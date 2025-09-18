<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

include_once (dirname(__FILE__) . "/Members.php");

/**
 * TODO: Move VA (Payment) related functions from controllers/admin/Members.php to this class,
 *       to reduce the complexity/size of Members.php
 * @author Eryan
 * @property Main_model.php main_mod
 * @property Payment_model.php payment_mod
 */
class Payment extends Members {

	protected $PAYMENT_STATUS_CANCEL = 3;
	protected $payment_tablename = 'user_transfer';

	function __construct()
	{
		parent::__construct();

        $this->load->model('main_mod');

		// To make this Class able to instantiate from Members.php
		// to prevent Unable to locate the specified class: Session.php
		// 20240721: Addedd Admin Kolektif (type = 11) for readOnly to view payment detail info
		if (isset($this->session)) {
			$akses = array("0", "1", "2", "12", "16","14", "8", "11", "9");		
			if(!in_array($this->session->userdata('type'),$akses)){
				$this->session->set_flashdata('error',$this->access_deny_msg());
				redirect('admin/dashboard');
				exit;
			}
		}

    }

    function version() {
        $this->_rest_response(TRUE, '200000', '20240721');
    }

	/**
	 * Tampilkan halaman VA, tanpa loading data VA.
	 * Data VA yang tampil akan dipanggil kemudian via Ajax get_va()
	 */
	function index()
	{
		$data['title'] = ''; //SITE_NAME.': Manage Members';
		$data['msg'] = '';
		
		// Load lookup tables
		$data["m_cab"] = $this->members_model->get_all_cabang();
		$data["m_bk"] = $this->members_model->get_all_bk();

		$this->load->view('admin/va_view', $data);
		return;
	}	

	/**
	 * Mengambil semua data VA untuk ditampilkan di tabel pada halaman VA
	 */
	public function get_va(){	
		
		$filter['status'] = $_POST['filter_status']; 
		$filter['bk'] = $_POST['filter_bk']; 
		$filter['cab'] = $_POST['filter_cab']; 
		if($_POST['tgl_period']!='')
		$filter['tgl_period'] = $_POST['tgl_period']; 
		if($_POST['tgl_period2']!='')
		$filter['tgl_period2'] = $_POST['tgl_period2']; 
		
		$search = $_POST['search']['value']; 
		$limit = $_POST['length']; 
		$start = $_POST['start']; 
		$order_index = $_POST['order'][0]['column']; 
		$order_field = $_POST['columns'][$order_index]['data']; 
		
		$column = ($_POST['columns']);
		
		$order_ascdesc = $_POST['order'][0]['dir']; 
		$sql_total = $this->payment_mod->count_all_va(); 
		$sql_data = $this->payment_mod->filter_va($search, $limit, $start, $order_field, $order_ascdesc ,$column,$filter); 
		$sql_filter = $this->payment_mod->count_filter_va($search,$column,$filter); 
		
		//print_r($column);
		
		$callback = array(
			'draw'=>$_POST['draw'],
			'recordsTotal'=>$sql_total,
			'recordsFiltered'=>$sql_filter,
			'data'=>$sql_data
		);
		header('Content-Type: application/json');
		echo json_encode($callback); 
	}	

	/**
	 * Klik "Detail" di halaman VA (Payment)
	 * @param ut_id user_transfer id
	 */
	function ajax_detail($ut_id = null) {

		log_message('debug',   "[SIMPONI] ".__CLASS__.'@'.__FUNCTION__. " Http metod: " . $this->input->method() . ", accessedBy " . $this->session->userdata('user_id' . ', data: ' . $this->input->raw_input_stream));		

		$detail = null; // Debug message

		if (empty($ut_id)) {
			$this->_rest_response(FALSE, 'SIM403201', 'Payment (user_transfer) id is requred.', REST_Controller::HTTP_BAD_REQUEST); 
		}
		
		if( 
			( isAdminMembership() || isAdminLSKI() 
			 || isAdminVnVMembership() || isAdminPKB() 
			 || isAdminSTRI() || isAdminFinance()
			 || isAdminKolektif() || isAdminKolektifRO()
			) == FALSE
		){
			$this->_rest_response(FALSE, 'SIM401201',  'No access to this resource/operation.', REST_Controller::HTTP_FORBIDDEN);
		} 	
		
		if (isAdminKolektif() || isAdminKolektifRO()) {
			$user_id = $this->db->select('user_id')->where('id', $ut_id)->get('user_transfer')->row()->user_id;
			if (!empty($user_id) && is_access_userdata_allowed($user_id) == FALSE) {
				$this->_rest_response(FALSE, 'SIM401100', "You don't have access permission to the user data", REST_Controller::HTTP_UNAUTHORIZED);	
			} else if (empty($user_id)) {
				$this->_rest_response(FALSE, 'SIM401102', "Check access permission failed. FAIP owner is not found", REST_Controller::HTTP_UNAUTHORIZED);	
			}
		}

		$data = $this->db->where('id',$ut_id)->get('v_payment_detail')->result_array();
		if(is_debug()) { $detail[] = $this->db->last_query(); }

		if (empty($data)) {
			$this->_rest_response(FALSE, 'SIM404100', "FAILED: No payment found", REST_Controller::HTTP_NOT_FOUND, $detail, $data);	
		} 
		else {
			$this->_rest_response(TRUE, 'SIM200000', "SUCCESS", 200, $detail, $data);
		}
	}

	/**
	 * Klik Payment Info in FAIP dashboard
	 * This function is similar to `ajax_detail(ut_id)` but it <b>support multiple payment records</b> to be returned
	 */
	function ajax_detail_by_faipid($faip_id = null) {
		log_message('debug',   "[SIMPONI] ".__CLASS__.'@'.__FUNCTION__. " Http metod: " . $this->input->method() . ", accessedBy " . $this->session->userdata('user_id' . ', data: ' . $this->input->raw_input_stream));		

		$detail = array();

		// Check if admin kolektif has access to user data
		if (isAdminKolektif() ||  isAdminKolektifRO()) {
			$user_id = $this->db->select('user_id')->where('id', $faip_id)->get('user_faip')->row()->user_id;

			if (!empty($user_id) && is_access_userdata_allowed($user_id) == FALSE) {
				$this->_rest_response(FALSE, 'SIM401100', "You don't have access permission to the user data. FAIP id: {$faip_id}, user_id: {$user_id}", REST_Controller::HTTP_UNAUTHORIZED);	
			} else if (empty($user_id)) {
				log_message('debug',   "[SIMPONI] ".__CLASS__.'@'.__FUNCTION__. " SQL: \n" . $this->db->last_query());		
				$this->_rest_response(FALSE, 'SIM401102', "Check access permission failed. FAIP owner is not found, user_id {$user_id}", REST_Controller::HTTP_UNAUTHORIZED);	
			}
		}

		$ut_result = $this->db->select('id')->where("rel_id", $faip_id)
		->where("status<", '3') // status is not canceled/deleted
		->order_by('id', "DESC")
		->get('user_transfer')
		->result();


		if (!isset($ut_result) || sizeof($ut_result) < 1) {
			$this->_rest_response(FALSE, 'SIM404100', "FAILED: No payment found", REST_Controller::HTTP_NOT_FOUND);	
		}  else {
			if (sizeof($ut_result) == 1) { 
				$data = $this->db->where('id',$ut_result[0]->id)->get('v_payment_detail')->result_array();
			} 
			else if (sizeof($ut_result) > 1) {
				$ut_ids = array();
				foreach ($ut_result as $ut) {
					$ut_ids[] = $ut->id;
				}
				$data = $this->db->where_in('id',$ut_ids)->get('v_payment_detail')->result_array();
				if(is_debug()) { $detail[] = $this->db->last_query(); }				
			} 
		}
		$this->_rest_response(TRUE, 'SIM200000', "SUCCESS", 200, $detail, $data);
	}

	function ajax_cancel($ut_id = null, $status = 3) {

		log_message('debug',   "[SIMPONI] ".__CLASS__.'@'.__FUNCTION__. " Http metod: " . $this->input->method() . ", accessedBy " . $this->session->userdata('user_id' . ', data: ' . $this->input->raw_input_stream));
		
		$detail = null; // Debug message
		$status = $this->PAYMENT_STATUS_CANCEL; // 3 = Cancel

		if (empty($ut_id)) {
			$this->_rest_response(FALSE, 'SIM403201', 'Payment (user_transfer) id is requred.', REST_Controller::HTTP_BAD_REQUEST); 
		}
		
		//TODO: Authorization check - Masih perlu dicek lagi
		$akses = array("0", "1"); 		
		if( in_array($this->session->userdata('type'), $akses) === FALSE )
		{
			$this->_rest_response(FALSE, 'SIM401201',  'No access to this resource/operation.', REST_Controller::HTTP_FORBIDDEN);
		}		

		try {
			$data = $this->db->where('id',$ut_id)->get('v_payment')->result_array();
			if(is_debug()) { $detail[] = $this->db->last_query(); }	
			if(is_debug()) { $detail[] = $data; }	
			
			$payment = $data[0];

			// LKSI admin hanya bisa cancel payment terkait Keanggotaan
			// 3=FAIP Assesment Fee, 4=FAIP SIP Fee
			if ( isAdminLSKI() == TRUE && ! ($payment["pay_type"] !== '3' || $payment['pay_type'] !== '4') ) {
				$this->_rest_response(FALSE, 'SIM401201',  "You can only cancel the payment which related to FAIP.\nuser_transfer id: {$ut_id}, payment type: ".$payment["pay_type"], REST_Controller::HTTP_FORBIDDEN);
			}

			// Admin Keanggotaan hanya bisa cancel payment ini:
			// 1=Anggota Baru (REG) Fee 2=Anggota Perpanjangan(HER) Fee
			else if ( isAdminMembership() == TRUE && ! ($payment['pay_type'] != 1 || $payment['pay_type'] != 2) ) {
				$this->_rest_response(FALSE, 'SIM401201',  'You can only cancel the payment which related to Keanggotaan', REST_Controller::HTTP_FORBIDDEN);
			}

			$newdata['status'] = $status; 
			$result = $this->db->where('id',$ut_id)->update($this->payment_tablename, $newdata);
			if(is_debug()) { $detail[] = $this->db->last_query(); }

			if ($result) {
				$this->_rest_response(TRUE, 'SIM200000', "SUCCESS. Please delete Odoo SaleOrder manually if any.\nOddo SaleOrder id:" . @$data['odoo_so'], 200, $detail); //, $data);
			} {
				$this->_rest_response(TRUE, 'SIM401202', "Update failed", REST_Controller::HTTP_NOT_MODIFIED, $detail, $data);
			}

		} catch (Throwable $t) {
			$this->_rest_response(TRUE, 'SIM401202', "Update failed", REST_Controller::HTTP_INTERNAL_SERVER_ERROR, $detail);
		}
		
	}	

	function export_va_all(){

		// Prevent error: Allowed memory size of XX bytes exhausted (tried to allocate XX bytes)
		// application/third_party/PHPExcel/Worksheet.php on line 1213
		// DONOT open this! Bikin server development hang dan perlu restart
		//ini_set('memory_limit', '-1');

		$akses = array("0", "1", "2", "12");		
		if(!in_array($this->session->userdata('type'),$akses)){
			redirect('admin/dashboard');
			exit;
		}
		
		$status = $this->input->get('status')<>null?$this->input->get('status'):"";
		$bk = $this->input->get('bk')<>null?$this->input->get('bk'):"";
		$cab = $this->input->get('cab')<>null?$this->input->get('cab'):"";
		$tgl_period = $this->input->get('tgl_period')<>null?$this->input->get('tgl_period'):"";
		$tgl_period2 = $this->input->get('tgl_period2')<>null?$this->input->get('tgl_period2'):"";
		$tipe = $this->input->get('tipe')<>null?$this->input->get('tipe'):"";
		
		$arr = '';
		
		if($status != ''){
			if($status=='1') { 
				$arr = $arr." and user_transfer.is_upload_mandiri=1 and user_transfer.status=0";
			}
			else if($status=='0') { 
				$arr = $arr." and user_transfer.is_upload_mandiri=0 and user_transfer.status=0";
			}
			else if($status=='2') { 
				$arr = $arr." and user_transfer.status=1";
			}
		}
		if($bk!='')
			$arr = $arr.'LPAD(members.code_bk_hkk, 2, "0") like "'.$bk.'%"';
		if($cab!='')
			$arr = $arr.'LPAD(members.code_wilayah, 4, "0") like "'.$cab.'%"';
		
		if($tgl_period != ''){
			$arr = $arr." and DATE(user_transfer.modifieddate)>='".$tgl_period."'";
		}
		if($tgl_period2 != ''){
			$arr = $arr." and DATE(user_transfer.modifieddate)<='".$tgl_period2."'";
		}
		if($tipe!='')
			$arr = $arr." and (select code from m_pay_type where value=user_transfer.pay_type limit 1) like '%".$tipe."%'";
		
		$this->load->library('Libexcel','excel');
		$this->load->model('main_mod');

//----------------------------------------------------------------------------------------------------------- Aslinya -----------------------------
/*		$sql = "SELECT user_transfer.*,firstname,lastname,no_kta,va,mobilephone FROM "
		."user_transfer join user_profiles on user_transfer.user_id = user_profiles.user_id "
		."join members on members.person_id = user_transfer.user_id where "
		."user_transfer.createddate >= '2021-02-01 00:00:00' and vnv_status=1".$arr;
*/
//------------------------------------------------------------------------------------------------------------ Perubahan by Ipur Tgl. 18-06-2025 --------------------------------

		$sql = "SELECT user_transfer.*,user_profiles.firstname,user_profiles.lastname,members.no_kta,user_profiles.va, user_profiles.mobilephone, "
		."members.from_date, members.thru_date FROM "
		."user_transfer join user_profiles on user_transfer.user_id = user_profiles.user_id "
		."join members on members.person_id = user_transfer.user_id where "
		."user_transfer.createddate >= '2021-02-01 00:00:00' and vnv_status=1".$arr ;
				
//-------------------------------------------------------------------------------------------------------------------------		
		$query = $this->db->query($sql);
		if( $this->db->count_all_results() > 500 ) {
			$sql .= " LIMIT 500";
		}
		$query->free_result();
			
		$rsl = $this->main_mod->msrquery($sql)->result();

		$objPHPExcel = new PHPExcel();
		
		if($tipe == 'REG' || $tipe == 'HER')	{	
			$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('A1', 'TANGGAL UPDATE TERAKHIR')
        			->setCellValue('B1', 'NAMA')
        			->setCellValue('C1', 'NO. KTA')
				->setCellValue('D1', 'TIPE')
				->setCellValue('E1', 'NO. VA')
				->setCellValue('F1', 'TOTAL')
				->setCellValue('G1', 'HP')
				->setCellValue('H1', 'PERIODE BAYAR')
			;
		}

		if($tipe == 'FAIP' || $tipe == 'STRI' )	{
			$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('A1', 'TANGGAL UPDATE TERAKHIR')
			        ->setCellValue('B1', 'NAMA')
			        ->setCellValue('C1', 'NO. KTA')
				->setCellValue('D1', 'TIPE')
				->setCellValue('E1', 'NO. VA')
				->setCellValue('F1', 'TOTAL')
				->setCellValue('G1', 'HP')		
			;		
		}
		
		$objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getFont()->setBold(true);
		
		$rowCount = 2;
		foreach($rsl as $val){
			
			$tipe = '';

			if($val->pay_type=='1') {
				$tipe = 'REG';
			}	
			if($val->pay_type=='2') {
				$tipe = 'HER';
			}	
			if($val->pay_type=='3') {
				$tipe = 'FAIP Assessment Fee';
			}	
			if($val->pay_type=='4') {
				$tipe = 'FAIP SIP Fee';
			}	
			if($val->pay_type=='5') {
				$tipe = 'STRI';
			}	
			
			if($val->pay_type=='2') {
			        $pay_typee = $val->id ; $this->load->model('members_model');
				$cari_tip = $this->members_model->cari_log_her_kta($pay_typee) ; 
				if (isset($cari_tip)) {
					$fromd = $cari_tip->from_date ; $thrud = $cari_tip->thru_date ;	
				}else{
				        $fromd = '' ; $thrud = '' ;
				}        
			}	
			
			if($val->pay_type !='2') {
				$fromd = '' ; $thrud = '' ;
				if ($val->pay_type == '1' ) {
				    $fromd = $val->from_date ; $thrud = $val->thru_date ;	
				}
			}	
	
			$objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, $val->modifieddate);
			$objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, $val->firstname.' '.$val->lastname);
			$objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, str_pad($val->no_kta, 6, '0', STR_PAD_LEFT));
			$objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $tipe);
			$objPHPExcel->getActiveSheet()->setCellValueExplicit('E'.$rowCount, $val->va, PHPExcel_Cell_DataType::TYPE_STRING);
			$objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, $val->sukarelatotal);
			$objPHPExcel->getActiveSheet()->setCellValueExplicit('G'.$rowCount, $val->mobilephone, PHPExcel_Cell_DataType::TYPE_STRING);
			$objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, $fromd.' - '.$thrud); // Tambahan by Ipur Tgl. 18-06-2025			
			
			$rowCount++;
		} 
		
		$sheet = $objPHPExcel->getActiveSheet();
		$cellIterator = $sheet->getRowIterator()->current()->getCellIterator();
		$cellIterator->setIterateOnlyExistingCells( true );
		
		foreach( $cellIterator as $cell ) {
				$sheet->getColumnDimension( $cell->getColumn() )->setAutoSize( true );
		}
		
		$filename=date('Y-m-d').'.xls'; 
		header('Content-Type: application/vnd.ms-excel'); //mime type
		header('Content-Disposition: attachment;filename="VA_ALL_'.$filename.'"'); 
		header('Cache-Control: max-age=0'); //no cache
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');  
		$objWriter->save('php://output');
	}
	
	function export_va_select(){
		$akses = array("0", "1", "2", "12");		
		if(!in_array($this->session->userdata('type'),$akses)){
			redirect('admin/dashboard');
			exit;
		}
		$id_total = $this->input->get('id')<>null?$this->input->get('id'):"";
		
		$this->load->library('Libexcel','excel');
		$this->load->model('main_mod');
//------------------------------------------------------------- Aslinya ------------------------------------------------------------

//		$rsl = $this->main_mod->msrquery("SELECT user_transfer.*,firstname,lastname,no_kta,va,mobilephone FROM user_transfer join user_profiles on user_transfer.user_id = user_profiles.user_id join members on members.person_id = user_transfer.user_id where user_transfer.createddate >= '2021-02-01 00:00:00' and vnv_status=1 and user_transfer.id in (".$id_total.")")->result();

//------------------------------------------------------------------------------------------------------------ Perubahan by Ipur Tgl. 18-06-2025 --------------------------------

		$rsl = $this->main_mod->msrquery("SELECT user_transfer.*,user_profiles.firstname, user_profiles.lastname, members.no_kta, user_profiles.va, user_profiles.mobilephone, 
		       members.from_date, members.thru_date  FROM user_transfer join user_profiles on user_transfer.user_id = user_profiles.user_id 
		       join members on members.person_id = user_transfer.user_id  where  
		       user_transfer.createddate >= '2021-02-01 00:00:00' and vnv_status=1 and user_transfer.id in (".$id_total.")")->result();
		       
//-------------------------------------------------------------------------------------------------------------------------------------------------------------		
		$objPHPExcel = new PHPExcel();
		
			$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('A1', 'TANGGAL UPDATE TERAKHIR')
        			->setCellValue('B1', 'NAMA')
        			->setCellValue('C1', 'NO. KTA')
				->setCellValue('D1', 'TIPE')
				->setCellValue('E1', 'NO. VA')
				->setCellValue('F1', 'TOTAL')
				->setCellValue('G1', 'HP')
				->setCellValue('H1', 'PERIODE BAYAR')
			;
		
		$objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getFont()->setBold(true);
		
		$rowCount = 2;
		foreach($rsl as $val){
			
			if($val->pay_type=='1') {
				$tipe = 'REG';
			}	
			if($val->pay_type=='2') {
				$tipe = 'HER';
			}	
			if($val->pay_type=='3') {
				$tipe = 'FAIP Assessment Fee';
			}	
			if($val->pay_type=='4') {
				$tipe = 'FAIP SIP Fee';
			}	
			if($val->pay_type=='5') {
				$tipe = 'STRI';
			}	
			
			if($val->pay_type=='2') {
			        $pay_typee = $val->id ; $this->load->model('members_model');
				$cari_tip = $this->members_model->cari_log_her_kta($pay_typee) ; 
				if (isset($cari_tip)) {
					$fromd = $cari_tip->from_date ; $thrud = $cari_tip->thru_date ;	
				}else{
				        $fromd = '' ; $thrud = '' ;
				}        
			}	
			
			if($val->pay_type !='2') {
				$fromd = '' ; $thrud = '' ;
				if ($val->pay_type == '1' ) {
				    $fromd = $val->from_date ; $thrud = $val->thru_date ;	
				}
			}	
			
			$objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, $val->modifieddate);
			$objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, $val->firstname.' '.$val->lastname);
			$objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, str_pad($val->no_kta, 6, '0', STR_PAD_LEFT));
			$objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $tipe);			
			$objPHPExcel->getActiveSheet()->setCellValueExplicit('E'.$rowCount, $val->va, PHPExcel_Cell_DataType::TYPE_STRING);
			$objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, $val->sukarelatotal);
			$objPHPExcel->getActiveSheet()->setCellValueExplicit('G'.$rowCount, $val->mobilephone, PHPExcel_Cell_DataType::TYPE_STRING);
			$objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, $fromd.' - '.$thrud); // Tambahan by Ipur Tgl. 18-06-2025
			$rowCount++;
		}
		
		$sheet = $objPHPExcel->getActiveSheet();
		$cellIterator = $sheet->getRowIterator()->current()->getCellIterator();
		$cellIterator->setIterateOnlyExistingCells( true );
		
		foreach( $cellIterator as $cell ) {
				$sheet->getColumnDimension( $cell->getColumn() )->setAutoSize( true );
		}
		
		$filename=date('Y-m-d').'.xls'; 
		header('Content-Type: application/vnd.ms-excel'); //mime type
		header('Content-Disposition: attachment;filename="VA_ALL_'.$filename.'"'); 
		header('Cache-Control: max-age=0'); //no cache
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');  
		$objWriter->save('php://output');
	}	

}
