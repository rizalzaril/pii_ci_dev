<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Member extends CI_Controller
{
	function __construct()
	{
		parent::__construct();
		date_default_timezone_set('Asia/Jakarta');
		$this->load->helper(array('form', 'url'));
		$this->load->library('form_validation');
		//$this->load->library('security');
		$this->load->helper('security');
		$this->load->library('tank_auth');
		$this->lang->load('tank_auth');
		$this->load->model('main_mod');
		$this->load->model('members_model');
		$this->load->model('faip_model');
		$this->load->model('simpan_model');
	}

	function index()
	{
		if ($this->session->userdata('user_id') == '') {
			redirect('auth/login');
		}
		//$data = '';
		$data['title'] = 'PII | Dashboard';
		$data['email'] = $this->session->userdata('email');
		//$this->load->view('member/beranda', $data);

		$this->load->view('member/dashboard_view', $data);
	}

	function kta()
	{
		if ($this->session->userdata('user_id') == '') {
			redirect('auth/login');
		}
		//$data = '';
		$data['title'] = 'PII | KEANGGOTAAN';
		$data['email'] = $this->session->userdata('email');
		//$this->load->view('member/beranda', $data);
		$id = $this->session->userdata('user_id');
		$data['members'] = $this->main_mod->msrwhere('members', array('person_id' => $id, 'status' => 1), 'id', 'desc')->result();
		$username = $this->main_mod->selwhere('users', 'username', array('id' => $id), 'id', 'desc')->row();
		if ($username->username == '') $data['members'] = array();

		$user_profiles = $this->main_mod->msrwhere('user_profiles', array('user_id' => $id), 'id', 'desc')->result();
		$user_edu = $this->main_mod->msrwhere('user_edu', array('user_id' => $id, 'status' => 1), 'id', 'desc')->result();

		$data['iswna'] = isset($user_profiles[0]->warga_asing) ? $user_profiles[0]->warga_asing : 0;

		$isvalid = 0;

		if (isset($user_profiles[0]) && isset($user_edu[0])) {
			if ($user_profiles[0]->photo != "" && $user_profiles[0]->id_file != "") {
				$isvalid = 1;

				$temp_flag = 0;
				foreach ($user_edu as $v) {
					if ($v->attachment != "")
						$temp_flag = 1;
				}
				if ($temp_flag == 1 && $isvalid == 1)
					$isvalid = 1;
				else $isvalid = 0;
			}

			$data['va'] = $user_profiles[0]->va;
		}
		$data['isvalid'] = $isvalid;
		$kta = isset($data['members'][0]->no_kta) ? $data['members'][0]->no_kta : 'DD';
		//if($kta!='DD') $data['is_ip']=$this->main_mod->msrwhere('skip45',array("kta"=>ltrim($kta, '0')),'id','desc')->result();		
		if ($kta != 'DD') $data['is_ip'] = $this->main_mod->msrwhere('user_cert', array("user_id" => $id, "status" => 2), 'id', 'desc')->result();

		$data['m_bk'] = $this->main_mod->msrwhere('m_bk', null, 'id', 'asc')->result();
		$data['m_cab'] = $this->main_mod->msrwhere('m_cab', null, 'id', 'asc')->result();

		$is_ajukan_reg = $this->main_mod->msrwhere('user_transfer', array('user_id' => $id, 'pay_type' => 5, 'rel_id' => 0), 'id', 'desc')->row();
		$data['is_ajukan_reg'] = isset($is_ajukan_reg->id) ? 1 : 0;

		//print_r($data['is_ip']);
		//$this->load->view('member/kta_dashboard', $data);
		$this->load->view('member/kta_dashboard - NOT VA', $data);
	}

	function stri()
	{
		if ($this->session->userdata('user_id') == '') {
			redirect('auth/login');
		}

		$tmp = $this->main_mod->msrquery('select * from parameter ')->result();

		if (isset($tmp[2]->value)) {
			if ($tmp[2]->value == '1')
				$this->session->set_userdata('is_stri_approval', '1');
		}

		$is_access = $this->main_mod->msrwhere('members', array('person_id' => $this->session->userdata('user_id'), 'thru_date >= curdate()' => null), 'id', 'desc')->result();
		if (!isset($is_access[0])) {
			echo '<script>alert("Harap proses aktifasi keanggotaan terlebih dahulu");window.location.href="' . base_url() . 'member/kta";</script>';
		} else {
			//$data = '';
			$data['title'] = 'PII | STRI';
			$data['email'] = $this->session->userdata('email');
			//$this->load->view('member/beranda', $data);
			$id = $this->session->userdata('user_id');
			$data['members'] = $this->main_mod->msrwhere('members', array('person_id' => $id, 'status' => 1), 'id', 'desc')->result();
			$users = $this->main_mod->msrwhere('users', array('id' => $id), 'id', 'desc')->row();
			$data['username'] = $users->username;
			$is_ajukan_reg = $this->main_mod->msrwhere('user_transfer', array('user_id' => $id, 'pay_type' => 1, 'status' => 0), 'id', 'desc')->row();
			$data['is_ajukan_reg'] = isset($is_ajukan_reg->id) ? 1 : 0;

			$user_profiles = $this->main_mod->msrwhere('user_profiles', array('user_id' => $id), 'id', 'desc')->result();
			$user_edu = $this->main_mod->msrwhere('user_edu', array('user_id' => $id, 'status' => 1), 'id', 'desc')->result();
			$user_exp = $this->main_mod->msrwhere('user_exp', array('user_id' => $id, 'status' => 1), 'id', 'desc')->result();

			//$data['user_ip']=$this->main_mod->msrwhere('user_cert',array('user_id'=>$id,'status'=>2),'id','desc')->result();	

			$data['user_ip'] = $this->main_mod->msrquery('
		(
		select id,lic_num, startyear,endyear,cert_title, ip_kta,			
		(select status from user_transfer where user_id=' . $id . ' and pay_type=5 and rel_id = user_cert.id order by createddate desc limit 1) as status,
		(select members_certificate.id from members_certificate where person_id=' . $id . ' and stri_id = (select stri_id from log_stri join user_transfer on log_stri.id_pay=user_transfer.id where user_transfer.rel_id=user_cert.id order by log_stri.id desc limit 1) and status=1) as members_certificate_id,
		(select is_publish from members_certificate where person_id=' . $id . ' and stri_id = (select stri_id from log_stri join user_transfer on log_stri.id_pay=user_transfer.id where user_transfer.rel_id=user_cert.id order by log_stri.id desc limit 1) and status=1) as is_publish,
		(select stri_id from members_certificate where person_id=' . $id . ' and stri_id = (select stri_id from log_stri join user_transfer on log_stri.id_pay=user_transfer.id where user_transfer.rel_id=user_cert.id order by log_stri.id desc limit 1) and status=1) as stri_id,
		(select certificate_type from members_certificate where person_id=' . $id . ' and stri_id = (select stri_id from log_stri join user_transfer on log_stri.id_pay=user_transfer.id where user_transfer.rel_id=user_cert.id order by log_stri.id desc limit 1) and status=1) as certificate_type,
		(select stri_code_bk_hkk from members_certificate where person_id=' . $id . ' and stri_id = (select stri_id from log_stri join user_transfer on log_stri.id_pay=user_transfer.id where user_transfer.rel_id=user_cert.id order by log_stri.id desc limit 1) and status=1) as stri_code_bk_hkk,
		(select stri_from_date from members_certificate where person_id=' . $id . ' and stri_id = (select stri_id from log_stri join user_transfer on log_stri.id_pay=user_transfer.id where user_transfer.rel_id=user_cert.id order by log_stri.id desc limit 1) and status=1) as stri_from_date,
		(select stri_thru_date from members_certificate where person_id=' . $id . ' and stri_id = (select stri_id from log_stri join user_transfer on log_stri.id_pay=user_transfer.id where user_transfer.rel_id=user_cert.id order by log_stri.id desc limit 1) and status=1) as stri_thru_date,
		(select stri_pm from members_certificate where person_id=' . $id . ' and stri_id = (select stri_id from log_stri join user_transfer on log_stri.id_pay=user_transfer.id where user_transfer.rel_id=user_cert.id order by log_stri.id desc limit 1) and status=1) as stri_pm,
		(select warga from members_certificate where person_id=' . $id . ' and stri_id = (select stri_id from log_stri join user_transfer on log_stri.id_pay=user_transfer.id where user_transfer.rel_id=user_cert.id order by log_stri.id desc limit 1) and status=1) as warga,
		(select th from members_certificate where person_id=' . $id . ' and stri_id = (select stri_id from log_stri join user_transfer on log_stri.id_pay=user_transfer.id where user_transfer.rel_id=user_cert.id order by log_stri.id desc limit 1) and status=1) as th,
		(select stri_tipe from members_certificate where person_id=' . $id . ' and stri_id = (select stri_id from log_stri join user_transfer on log_stri.id_pay=user_transfer.id where user_transfer.rel_id=user_cert.id order by log_stri.id desc limit 1) and status=1) as stri_tipe, (select vnv_status from user_transfer where rel_id=user_cert.id and pay_type=5 order by id desc limit 1) as vnv, (select remark from user_transfer where rel_id=user_cert.id and pay_type=5 order by id desc limit 1) as notesvnv, ip_seq, ip_rev,
		(select is_upload_mandiri from user_transfer where rel_id=user_cert.id and pay_type=5 order by id desc limit 1) as is_upload_mandiri
		from user_cert where user_id = ' . $id . ' and status=2 order by endyear desc,ip_tipe desc limit 1
		)
		Union
		(
		select null as id, null as lic_num,null as startyear,null as endyear,null as cert_title, null as ip_kta,
		status,
		(select members_certificate.id from members_certificate where person_id=' . $id . ' and stri_id = (select stri_id from log_stri where user_transfer.id=log_stri.id_pay order by log_stri.id desc limit 1) and certificate_type=0 order by members_certificate.id desc limit 1) as members_certificate_id,
		(select is_publish from members_certificate where person_id=' . $id . ' and stri_id = (select stri_id from log_stri where user_transfer.id=log_stri.id_pay order by log_stri.id desc limit 1) and certificate_type=0 order by members_certificate.id desc limit 1) as is_publish,
		(select stri_id from members_certificate where person_id=' . $id . ' and stri_id = (select stri_id from log_stri where user_transfer.id=log_stri.id_pay order by log_stri.id desc limit 1) and certificate_type=0 order by members_certificate.id desc limit 1) as stri_id,
		(select certificate_type from members_certificate where person_id=' . $id . ' and stri_id = (select stri_id from log_stri where user_transfer.id=log_stri.id_pay order by log_stri.id desc limit 1) and certificate_type=0 order by members_certificate.id desc limit 1) as certificate_type,
		(select stri_code_bk_hkk from members_certificate where person_id=' . $id . ' and stri_id = (select stri_id from log_stri where user_transfer.id=log_stri.id_pay order by log_stri.id desc limit 1) and certificate_type=0 order by members_certificate.id desc limit 1) as stri_code_bk_hkk,
		(select stri_from_date from members_certificate where person_id=' . $id . ' and stri_id = (select stri_id from log_stri where user_transfer.id=log_stri.id_pay order by log_stri.id desc limit 1) and certificate_type=0 order by members_certificate.id desc limit 1) as stri_from_date,
		(select stri_thru_date from members_certificate where person_id=' . $id . ' and stri_id = (select stri_id from log_stri where user_transfer.id=log_stri.id_pay order by log_stri.id desc limit 1) and certificate_type=0 order by members_certificate.id desc limit 1) as stri_thru_date,
		(select stri_pm from members_certificate where person_id=' . $id . ' and stri_id = (select stri_id from log_stri where user_transfer.id=log_stri.id_pay order by log_stri.id desc limit 1) and certificate_type=0 order by members_certificate.id desc limit 1) as stri_pm,
		(select warga from members_certificate where person_id=' . $id . ' and stri_id = (select stri_id from log_stri where user_transfer.id=log_stri.id_pay order by log_stri.id desc limit 1) and certificate_type=0 order by members_certificate.id desc limit 1) as warga,
		(select th from members_certificate where person_id=' . $id . ' and stri_id = (select stri_id from log_stri where user_transfer.id=log_stri.id_pay order by log_stri.id desc limit 1) and certificate_type=0 order by members_certificate.id desc limit 1) as th,
		(select stri_tipe from members_certificate where person_id=' . $id . ' and stri_id = (select stri_id from log_stri where user_transfer.id=log_stri.id_pay order by log_stri.id desc limit 1) and certificate_type=0 order by members_certificate.id desc limit 1) as stri_tipe, vnv_status as vnv, remark as notesvnv , null as ip_seq, null as ip_rev,
		is_upload_mandiri
		from user_transfer where user_id = ' . $id . ' and pay_type=5 and rel_id=0 order by user_transfer.createddate desc limit 1
		)		
		
		')->result();

			$enddate = isset($data['members'][0]->thru_date) ? strtotime($data['members'][0]->thru_date) : '';
			$enddate3000 = isset($data['members'][0]->thru_date) ? $data['members'][0]->thru_date : '';
			$datenow = strtotime(date('Y-m-d')); //, strtotime('+2 months')

			$isvalid = 0;
			if ($enddate != '') {
				if ($enddate >= $datenow) $isvalid = 1;
			}
			if (substr($enddate3000, 0, 4) == '3000') $isvalid = 1;

			if (isset($user_profiles[0])) {
				$data['va'] = $user_profiles[0]->va;
			}
			$data['isvalid'] = $isvalid;


			//CEK STRI PERALIHAN
			$isvalid_stri_per = 0;

			if (isset($user_profiles[0]) && isset($user_edu[0]) && isset($user_exp[0])) {
				if ($user_profiles[0]->id_file != "") {
					$isvalid_stri_per = 1;

					$temp_flag = 0;
					foreach ($user_edu as $v) {
						if ($v->attachment != "")
							$temp_flag = 1;
					}
					$temp_flag_exp = 0;
					foreach ($user_exp as $v) {
						if ($v->attachment != "")
							$temp_flag_exp = 1;
					}
					if ($temp_flag == 1 && $isvalid_stri_per == 1 && $temp_flag_exp == 1)
						$isvalid_stri_per = 1;
					else $isvalid_stri_per = 0;
				}
			}
			$data['isvalid_stri_per'] = $isvalid_stri_per;

			//CEK STRI NORMAL IJASAH IR/SUKET
			$isvalid_stri_normal = 1;
			//---------------------------------------------------------------------- Tidak digunakan 		
			/*$isvalid_stri_normal = 0;
		
		if(isset($user_profiles[0]) && isset($user_edu[0])){
			if($user_profiles[0]->id_file!="")
			{
				$isvalid_stri_normal = 1;
			
				$temp_flag = 0;
				foreach($user_edu as $v){
					if($v->attachment!="" && $v->type=="2")
						$temp_flag = 1;
				}				
				if($temp_flag == 1 && $isvalid_stri_normal == 1)
					$isvalid_stri_normal = 1;
				else $isvalid_stri_normal = 0;
			}
		}*/
			//-------------------------------------------------------------------------------------------

			$data['isvalid_stri_normal'] = $isvalid_stri_normal;


			//$kta = isset($data['members'][0]->no_kta)?$data['members'][0]->no_kta:'DD';
			//if($kta!='DD') $data['is_ip']=$this->main_mod->msrwhere('user_cert',array("user_id"=>$id,"status"=>2),'id','desc')->result();		

			$data['m_bk'] = $this->main_mod->msrwhere('m_bk', null, 'id', 'asc')->result();
			$data['m_cab'] = $this->main_mod->msrwhere('m_cab', null, 'id', 'asc')->result();

			//--------------------------------------------- Tambahan by Ipur Tgl 05-06-2025 ------------------------------------------------------------
			$nokta = $nokta ?? '';

			$dibuat = $this->members_model->ambil_create_date($id);
			if (isset($dibuat)) {
				$dibuatnya = $dibuat->createddate; // Tambahan by Ipur 
				$data['dibuatnya'] = $dibuatnya;
				$this->load->model('faip_model');
				$nokta = $this->faip_model->golek_user_id_sertifikat($id);
				$data['nokta'] = $nokta->no_kta; // Tambahan by Ipur Tgl.2025-06-16		 	
			} else {
				$data['dibuatnya'] = '';
				$data['nokta'] = ''; // Tambahan by Ipur Tgl.2025-06-16
			}


			//-----------------------------------------------------------------------------------------------------------------------------------------

			//print_r($data['isvalid']);
			//$this->load->view('member/kta_dashboard', $data);
			$this->load->view('member/stri_dashboard - NOT VA', $data);
		}
	}

	function pay()
	{
		if ($this->session->userdata('user_id') == '') {
			redirect('auth/login');
		}
		$this->load->model('main_mod');
		//ORGANIZATIONS
		$pay_type = $this->input->post('pay_type') <> null ? $this->input->post('pay_type') : "";
		$atasnama = $this->input->post('atasnama') <> null ? $this->input->post('atasnama') : "";
		$tgl = $this->input->post('tgl') <> null ? $this->input->post('tgl') : "";
		$bukti = $this->input->post('bukti') <> null ? $this->input->post('bukti') : "";
		$desc = $this->input->post('desc') <> null ? $this->input->post('desc') : "";



		$iuranpangkal = $this->input->post('iuranpangkal') <> null ? $this->input->post('iuranpangkal') : "";
		$iurantahunan = $this->input->post('iurantahunan') <> null ? $this->input->post('iurantahunan') : "";
		$sukarelaanggota = $this->input->post('sukarelaanggota') <> null ? $this->input->post('sukarelaanggota') : "";
		$sukarelagedung = $this->input->post('sukarelagedung') <> null ? $this->input->post('sukarelagedung') : "";
		$sukarelaperpus = $this->input->post('sukarelaperpus') <> null ? $this->input->post('sukarelaperpus') : "";
		$sukarelaceps = $this->input->post('sukarelaceps') <> null ? $this->input->post('sukarelaceps') : "";
		$sukarelatotal = $this->input->post('sukarelatotal') <> null ? $this->input->post('sukarelatotal') : "";

		//if($bukti!=''){
		try {
			$check_double_transfer = $this->main_mod->msrwhere('user_transfer', array(
				'user_id' => $this->session->userdata('user_id'),
				'is_upload_mandiri' => 1,
				'status' => 0,
				'bukti' => '',
				' NOW() BETWEEN modifieddate AND DATE_ADD( modifieddate, INTERVAL 7 DAY )' => null
			), 'id', 'desc')->result();

			$check_double_transfer2 = $this->main_mod->msrwhere('user_transfer', array(
				'user_id' => $this->session->userdata('user_id'),
				'pay_type' => $pay_type,
				'status' => 0,
				//' NOW() BETWEEN modifieddate AND DATE_ADD( modifieddate, INTERVAL 7 DAY )' => null
			), 'id', 'desc')->result();

			if (isset($check_double_transfer[0]) || isset($check_double_transfer2[0])) {
				echo 0;
			} else {

				$check_transfer = $this->main_mod->msrwhere('members', array('person_id' => $this->session->userdata('user_id')), 'id', 'desc')->result();
				$vnv_status = 0;
				if (isset($check_transfer[0]) && $pay_type == 1) {
					$vnv_status = 1;
				}
				$row = array(
					'user_id' => $this->session->userdata('user_id'),
					'pay_type' => $pay_type,
					'atasnama' => $atasnama,
					'tgl' => $tgl,
					'bukti' => $bukti,
					'description' => $desc,

					'vnv_status' => $vnv_status,

					'iuranpangkal' => $iuranpangkal,
					'iurantahunan' => $iurantahunan,
					'sukarelaanggota' => $sukarelaanggota,
					'sukarelagedung' => $sukarelagedung,
					'sukarelaperpus' => $sukarelaperpus,
					'sukarelaceps' => $sukarelaceps,
					'sukarelatotal' => $sukarelatotal,
					'createddate' => date('Y-m-d H:i:s'),
					'createdby' => $this->session->userdata('user_id'),
				);

				if ($vnv_status == 1 && $pay_type == 1) {
					$row['modifieddate'] = date('Y-m-d H:i:s');
					$row['modifiedby'] = $this->session->userdata('user_id');
				}

				$insert = $this->main_mod->insert('user_transfer', $row);

				$rowInsert = array(
					'pay_id' => $insert,
					'old_status' => 0,
					'new_status' => 1,
					'notes' => 'member',
					'createdby' => $this->session->userdata('user_id'),
				);
				$this->main_mod->insert('log_status_kta', $rowInsert);

				echo $insert;
			}
		} catch (Exception $e) {
			print_r($e);
		}
		//}
		//else
		//	echo "not valid";

	}

	function pay_faip()
	{
		if ($this->session->userdata('user_id') == '') {
			redirect('auth/login');
		}
		$this->load->model('main_mod');
		//ORGANIZATIONS
		$id = $this->input->post('id') <> null ? $this->input->post('id') : "";
		$pay_type = $this->input->post('pay_type') <> null ? $this->input->post('pay_type') : "";
		$atasnama = $this->input->post('atasnama') <> null ? $this->input->post('atasnama') : "";
		$tgl = $this->input->post('tgl') <> null ? $this->input->post('tgl') : "";
		$bukti = $this->input->post('bukti') <> null ? $this->input->post('bukti') : "";
		$desc = $this->input->post('desc') <> null ? $this->input->post('desc') : "";

		$total = $this->input->post('total') <> null ? $this->input->post('total') : "";

		$sukarelatotal = 1100000;
		$status_faip = 4;
		if ($pay_type == 3) {
			$sukarelatotal = 1100000;
			$status_faip = 4;
		} else if ($pay_type == 4) {
			$sukarelatotal = $total;
			$status_faip = 10;
		}

		//if($bukti!=''){
		try {
			$check_double_transfer = $this->main_mod->msrwhere('user_transfer', array(
				'user_id' => $this->session->userdata('user_id'),
				'is_upload_mandiri' => 1,
				'status' => 0,
				'bukti' => '',
				' NOW() BETWEEN modifieddate AND DATE_ADD( modifieddate, INTERVAL 7 DAY )' => null
			), 'id', 'desc')->result();

			$check_double_transfer2 = $this->main_mod->msrwhere('user_transfer', array(
				'user_id' => $this->session->userdata('user_id'),
				'pay_type' => $pay_type,
				'status' => 0,
				//' NOW() BETWEEN modifieddate AND DATE_ADD( modifieddate, INTERVAL 7 DAY )' => null
			), 'id', 'desc')->result();

			if (isset($check_double_transfer[0]) || isset($check_double_transfer2[0])) {
				echo 0;
			} else {
				$row = array(
					'user_id' => $this->session->userdata('user_id'),
					'pay_type' => $pay_type,
					'rel_id' => $id,
					'atasnama' => $atasnama,
					'tgl' => $tgl,
					'bukti' => $bukti,
					'description' => $desc,

					'vnv_status' => 1,
					//'is_upload_mandiri'=>1,

					'sukarelatotal' => $sukarelatotal,
					'createdby' => $this->session->userdata('user_id'),

					'modifiedby' => $this->session->userdata('user_id'),
					'modifieddate' => date('Y-m-d H:i:s'),

				);
				$insert = $this->main_mod->insert('user_transfer', $row);


				$check = $this->main_mod->msrwhere('user_faip', array('id' => $id), 'id', 'desc')->result();
				$rowInsert = array(
					'faip_id' => $id,
					'old_status' => $check[0]->status_faip,
					'new_status' => $status_faip,
					'notes' => 'anggota',
					'createdby' => $this->session->userdata('user_id'),
				);
				$this->main_mod->insert('log_status_faip', $rowInsert);



				$where = array(
					"id" => $id
				);
				$row = array(
					'status_faip' => $status_faip,
					//'remarks' => $remarks,
					'modifieddate' => date('Y-m-d H:i:s'),
					'modifiedby' => $this->session->userdata('user_id'),
				);
				$update = $this->main_mod->update('user_faip', $where, $row);


				echo $insert;
			}
		} catch (Exception $e) {
			print_r($e);
		}
		//}
		//else
		//	echo "not valid";

	}

	function pay_stri()
	{
		if ($this->session->userdata('user_id') == '') {
			redirect('auth/login');
		}
		$this->load->model('main_mod');
		//ORGANIZATIONS
		$id = $this->input->post('id') <> null ? $this->input->post('id') : "";
		$pay_type = $this->input->post('pay_type') <> null ? $this->input->post('pay_type') : "";
		$atasnama = $this->input->post('atasnama') <> null ? $this->input->post('atasnama') : "";
		$tgl = $this->input->post('tgl') <> null ? $this->input->post('tgl') : "";
		$bukti = $this->input->post('bukti') <> null ? $this->input->post('bukti') : "";
		$bukti_2 = $this->input->post('bukti_2') <> null ? $this->input->post('bukti_2') : "";
		$bukti_3 = $this->input->post('bukti_3') <> null ? $this->input->post('bukti_3') : "";
		$desc = $this->input->post('desc') <> null ? $this->input->post('desc') : "";
		$sip = $this->input->post('sip') <> null ? $this->input->post('sip') : "";

		//$sukarelatotal = $this->input->post('sukarelatotal')<>null?$this->input->post('sukarelatotal'):500000;
		$sukarelatotal = $this->input->post('sukarelatotal') <> null ? $this->input->post('sukarelatotal') : 0;

		//$sukarelatotal = 500000;
		$pay_type = 5;

		$check_double_transfer = $this->main_mod->msrwhere('user_transfer', array(
			'user_id' => $this->session->userdata('user_id'),
			'rel_id' => $id == '' ? 0 : $id,
			'status' => 0,
			'vnv_status' => 0,
			'pay_type' => 5
		), 'id', 'desc')->result();

		if (isset($check_double_transfer[0])) {
			echo 'double pengajuan';
		} else {
			if ($id != '' && $id != '0') {
				try {
					/*$check_double_transfer = $this->main_mod->msrwhere('user_transfer',array(
						'user_id'=>$this->session->userdata('user_id'),
						'is_upload_mandiri'=>1,
						'status'=>0,
						'bukti' => '',
						' NOW() BETWEEN modifieddate AND DATE_ADD( modifieddate, INTERVAL 7 DAY )' => null
					),'id','desc')->result();
					
					if(isset($check_double_transfer[0])){
						echo 0;
					}
					else{*/
					$vnv_status = 0;
					if ($id != '' && $id != '0') $vnv_status = 1;

					$row = array(
						'user_id' => $this->session->userdata('user_id'),
						'pay_type' => $pay_type,
						'rel_id' => $id,
						'atasnama' => $atasnama,
						'tgl' => $tgl,
						'bukti' => ($bukti != '' ? $bukti : '-'),

						'sip' => $sip,

						'add_doc1' => $bukti_2,
						'add_doc2' => $bukti_3,
						'description' => $desc,
						//'vnv_status' => $vnv_status,

						'sukarelatotal' => $sukarelatotal,
						'createdby' => $this->session->userdata('user_id'),

						//'modifiedby' => $this->session->userdata('user_id'),
						//'modifieddate' => date('Y-m-d H:i:s'),

					);
					$insert = $this->main_mod->insert('user_transfer', $row);


					echo $insert;
					//}
				} catch (Exception $e) {
					print_r($e);
				}
			} else {
				//if($bukti!=''){
				try {
					$row = array(
						'user_id' => $this->session->userdata('user_id'),
						'pay_type' => $pay_type,
						'rel_id' => $id,
						'atasnama' => $atasnama,
						'tgl' => $tgl,
						'bukti' => ($bukti != '' ? $bukti : ''),
						'add_doc1' => $bukti_2,
						'add_doc2' => $bukti_3,
						'description' => $desc,

						'sukarelatotal' => $sukarelatotal,

						'createdby' => $this->session->userdata('user_id'),
					);
					$insert = $this->main_mod->insert('user_transfer', $row);


					echo $insert;
				} catch (Exception $e) {
					print_r($e);
				}
				//}
				//else
				//	echo "not valid";
			}
		}
	}

	function pay_v2()
	{
		if ($this->session->userdata('user_id') == '') {
			redirect('auth/login');
		}
		$this->load->model('main_mod');
		//ORGANIZATIONS
		$pay_type = $this->input->post('pay_type') <> null ? $this->input->post('pay_type') : "";
		$atasnama = $this->input->post('atasnama') <> null ? $this->input->post('atasnama') : "";
		$tgl = $this->input->post('tgl') <> null ? $this->input->post('tgl') : "";
		$bukti = $this->input->post('bukti') <> null ? $this->input->post('bukti') : "";
		$desc = $this->input->post('desc') <> null ? $this->input->post('desc') : "";
		$order_id = $this->input->post('order_id') <> null ? $this->input->post('order_id') : "";



		$iuranpangkal = $this->input->post('iuranpangkal') <> null ? $this->input->post('iuranpangkal') : "";
		$iurantahunan = $this->input->post('iurantahunan') <> null ? $this->input->post('iurantahunan') : "";
		$sukarelaanggota = $this->input->post('sukarelaanggota') <> null ? $this->input->post('sukarelaanggota') : "";
		$sukarelagedung = $this->input->post('sukarelagedung') <> null ? $this->input->post('sukarelagedung') : "";
		$sukarelaperpus = $this->input->post('sukarelaperpus') <> null ? $this->input->post('sukarelaperpus') : "";
		$sukarelaceps = $this->input->post('sukarelaceps') <> null ? $this->input->post('sukarelaceps') : "";
		$sukarelatotal = $this->input->post('sukarelatotal') <> null ? $this->input->post('sukarelatotal') : "";

		//if($bukti!=''){
		try {
			$row = array(
				'user_id' => $this->session->userdata('user_id'),
				'pay_type' => $pay_type,
				'atasnama' => $atasnama,
				'tgl' => $tgl,
				'bukti' => $bukti,
				'description' => $desc,
				'order_id' => $order_id,

				'iuranpangkal' => $iuranpangkal,
				'iurantahunan' => $iurantahunan,
				'sukarelaanggota' => $sukarelaanggota,
				'sukarelagedung' => $sukarelagedung,
				'sukarelaperpus' => $sukarelaperpus,
				'sukarelaceps' => $sukarelaceps,
				'sukarelatotal' => $sukarelatotal,

				'createdby' => $this->session->userdata('user_id'),
			);
			$insert = $this->main_mod->insert('user_transfer', $row);
			echo $insert;
		} catch (Exception $e) {
			print_r($e);
		}
		//}
		//else
		//	echo "not valid";

	}

	function pay_faip_v2()
	{
		if ($this->session->userdata('user_id') == '') {
			redirect('auth/login');
		}
		$this->load->model('main_mod');
		//ORGANIZATIONS
		$id = $this->input->post('id') <> null ? $this->input->post('id') : "";
		$pay_type = $this->input->post('pay_type') <> null ? $this->input->post('pay_type') : "";
		$atasnama = $this->input->post('atasnama') <> null ? $this->input->post('atasnama') : "";
		$tgl = $this->input->post('tgl') <> null ? $this->input->post('tgl') : "";
		$bukti = $this->input->post('bukti') <> null ? $this->input->post('bukti') : "";
		$desc = $this->input->post('desc') <> null ? $this->input->post('desc') : "";
		$order_id = $this->input->post('order_id') <> null ? $this->input->post('order_id') : "";

		$sukarelatotal = 1100000;
		$status_faip = 4;
		if ($pay_type == 3) {
			$sukarelatotal = 1100000;
			$status_faip = 4;
		} else if ($pay_type == 4) {
			$sukarelatotal = 1;
			$status_faip = 11;
		}

		//if($bukti!=''){
		try {
			$row = array(
				'user_id' => $this->session->userdata('user_id'),
				'pay_type' => $pay_type,
				'rel_id' => $id,
				'atasnama' => $atasnama,
				'tgl' => $tgl,
				'bukti' => $bukti,
				'description' => $desc,
				'order_id' => $order_id,

				'sukarelatotal' => $sukarelatotal,

				'createdby' => $this->session->userdata('user_id'),
			);
			$insert = $this->main_mod->insert('user_transfer', $row);

			$where = array(
				"id" => $id
			);
			$row = array(
				'status_faip' => $status_faip,
				//'remarks' => $remarks,
				'modifieddate' => date('Y-m-d H:i:s'),
				'modifiedby' => $this->session->userdata('user_id'),
			);
			$update = $this->main_mod->update('user_faip', $where, $row);


			echo $insert;
		} catch (Exception $e) {
			print_r($e);
		}
		//}
		//else
		//	echo "not valid";

	}

	function lanjutkan()
	{
		if ($this->session->userdata('user_id') == '') {
			redirect('auth/login');
		}

		$idmember = $this->input->post('id') <> null ? $this->input->post('id') : "";
		$this->load->model('main_mod');
		if ($idmember != '') {
			try {

				$check = $this->main_mod->msrwhere('user_transfer', array('id' => $idmember, 'user_id' => $this->session->userdata('user_id')), 'id', 'desc')->result();
				if (isset($check[0])) {
					$where = array(
						"id" => $idmember
					);
					$row = array(
						'vnv_status' => 0,
						'modifieddate' => date('Y-m-d H:i:s'),
						'modifiedby' => $this->session->userdata('user_id'),
					);
					$update = $this->main_mod->update('user_transfer', $where, $row);
				}

				echo "valid";
			} catch (Exception $e) {
				echo "not valid";
			}
		} else
			echo "not valid";
	}

	function bukti_upload()
	{
		if (! $this->session->userdata('user_id')) redirect('login');

		if (isset($_FILES['bukti']['name'])) {
			$valid_formats_img = array("jpg", "jpeg", "gif", "png", "pdf", "bmp");
			$name = $_FILES['bukti']['name'];
			$size = $_FILES['bukti']['size'];

			$extx = pathinfo($name, PATHINFO_EXTENSION);

			if (strlen($name)) {
				//list($txt, $ext) = explode(".", $name);
				if (in_array(strtolower($extx), $valid_formats_img)) {
					if ($size < (710000)) {
						//$actual_image_name = time().substr(str_replace(" ", "_", $txt), 5).".".$ext;
						$actual_image_name = time() . "_BKT_" . $this->session->userdata('user_id') . "." . $extx;

						$config['upload_path'] = './assets/uploads/pay/';
						$config['allowed_types'] = '*';
						$config['max_size']	= '710';
						$config['file_name'] = $actual_image_name;

						$this->load->library('upload', $config);

						if ($this->upload->do_upload('bukti')) {

							/*$config['image_library'] = 'gd2';
							$config['source_image'] = './assets/uploads/pay/'.$actual_image_name;
							$config['maintain_ratio'] = TRUE;
							//$config['width']    = 300;
							//$config['height']   = 300;

							$this->load->library('image_lib', $config); 

							if (!$this->image_lib->resize()) {
								//echo $this->image_lib->display_errors();
							}*/
							/*
							$where = array(
								"user_id" => $this->session->userdata('user_id')
							);
							$row=array(
								'id_file' => $actual_image_name,
								'modifieddate' => date('Y-m-d H:i:s'),
								'modifiedby' => $this->session->userdata('user_id'),	
							);
							$update = $this->main_mod->update('user_profiles',$where,$row);
							*/
							echo "<input type='hidden' id='bukti_image_url' value='" . $actual_image_name . "'>";
							//echo "<img src='".base_url()."/assets/uploads/".$actual_image_name."'  class='ava_discus' width='150'>";
							echo "<a href='" . base_url() . "/assets/uploads/pay/" . $actual_image_name . "' target='_blank' class='ava_discus'>" . $actual_image_name . "</a>";
						} else
							echo "<span style:'color:red'>Format file yang diijinkan (gif|jpg|png|jpeg|pdf|bmp).</span>";
					} else
						echo "<span style:'color:red'>Sorry, maximum file size should be 700 KB</span>";
				} else
					echo "<span style:'color:red'>Format file yang diijinkan (gif|jpg|png|jpeg|pdf|bmp).</span>";
			} else
				echo "<span style:'color:red'>Please select an image (jpg, gif, png, bmp file) or PDF file.</span>";
		} else
			echo "<span style:'color:red'>Please select an image (jpg, gif, png, bmp file) or PDF file.</span>";
	}

	function bukti_upload_()
	{
		if (! $this->session->userdata('user_id')) redirect('login');

		if (isset($_FILES['bukti']['name'])) {
			$valid_formats_img = array("jpg", "jpeg", "gif", "png", "pdf", "bmp");
			$name = $_FILES['bukti']['name'];
			$size = $_FILES['bukti']['size'];

			$extx = pathinfo($name, PATHINFO_EXTENSION);

			if (strlen($name)) {
				//list($txt, $ext) = explode(".", $name);
				if (in_array(strtolower($extx), $valid_formats_img)) {
					if ($size < (710000)) {
						//$actual_image_name = time().substr(str_replace(" ", "_", $txt), 5).".".$ext;
						$actual_image_name = time() . "_BKT_" . $this->session->userdata('user_id') . "." . $extx;

						$config['upload_path'] = './assets/uploads/pay/';
						$config['allowed_types'] = '*';
						$config['max_size']	= '710';
						$config['file_name'] = $actual_image_name;

						$this->load->library('upload', $config);

						if ($this->upload->do_upload('bukti')) {

							/*$config['image_library'] = 'gd2';
							$config['source_image'] = './assets/uploads/pay/'.$actual_image_name;
							$config['maintain_ratio'] = TRUE;
							//$config['width']    = 300;
							//$config['height']   = 300;

							$this->load->library('image_lib', $config); 

							if (!$this->image_lib->resize()) {
								//echo $this->image_lib->display_errors();
							}*/
							/*
							$where = array(
								"user_id" => $this->session->userdata('user_id')
							);
							$row=array(
								'id_file' => $actual_image_name,
								'modifieddate' => date('Y-m-d H:i:s'),
								'modifiedby' => $this->session->userdata('user_id'),	
							);
							$update = $this->main_mod->update('user_profiles',$where,$row);
							*/
							echo "<input type='hidden' id='bukti_image_url_' value='" . $actual_image_name . "'>";
							//echo "<img src='".base_url()."/assets/uploads/".$actual_image_name."'  class='ava_discus' width='150'>";
							echo "<a href='" . base_url() . "/assets/uploads/pay/" . $actual_image_name . "' target='_blank' class='ava_discus'>" . $actual_image_name . "</a>";
						} else
							echo "<span style:'color:red'>Format file yang diijinkan (gif|jpg|png|jpeg|pdf|bmp).</span>";
					} else
						echo "<span style:'color:red'>Sorry, maximum file size should be 700 KB</span>";
				} else
					echo "<span style:'color:red'>Format file yang diijinkan (gif|jpg|png|jpeg|pdf|bmp).</span>";
			} else
				echo "<span style:'color:red'>Please select an image (jpg, gif, png, bmp file) or PDF file.</span>";
		} else
			echo "<span style:'color:red'>Please select an image (jpg, gif, png, bmp file) or PDF file.</span>";
	}

	function sip_upload_()
	{
		if (! $this->session->userdata('user_id')) redirect('login');

		if (isset($_FILES['bukti']['name'])) {
			$valid_formats_img = array("jpg", "jpeg", "gif", "png", "pdf", "bmp");
			$name = $_FILES['bukti']['name'];
			$size = $_FILES['bukti']['size'];

			$extx = pathinfo($name, PATHINFO_EXTENSION);

			if (strlen($name)) {
				//list($txt, $ext) = explode(".", $name);
				if (in_array(strtolower($extx), $valid_formats_img)) {
					if ($size < (710000)) {
						//$actual_image_name = time().substr(str_replace(" ", "_", $txt), 5).".".$ext;
						$actual_image_name = time() . "_SIP_" . $this->session->userdata('user_id') . "." . $extx;

						$config['upload_path'] = './assets/uploads/sip/';
						$config['allowed_types'] = '*';
						$config['max_size']	= '710';
						$config['file_name'] = $actual_image_name;

						$this->load->library('upload', $config);

						if ($this->upload->do_upload('bukti')) {

							/*$config['image_library'] = 'gd2';
							$config['source_image'] = './assets/uploads/pay/'.$actual_image_name;
							$config['maintain_ratio'] = TRUE;
							//$config['width']    = 300;
							//$config['height']   = 300;

							$this->load->library('image_lib', $config); 

							if (!$this->image_lib->resize()) {
								//echo $this->image_lib->display_errors();
							}*/
							/*
							$where = array(
								"user_id" => $this->session->userdata('user_id')
							);
							$row=array(
								'id_file' => $actual_image_name,
								'modifieddate' => date('Y-m-d H:i:s'),
								'modifiedby' => $this->session->userdata('user_id'),	
							);
							$update = $this->main_mod->update('user_profiles',$where,$row);
							*/
							echo "<input type='hidden' id='bukti_image_url_sip' value='" . $actual_image_name . "'>";
							//echo "<img src='".base_url()."/assets/uploads/".$actual_image_name."'  class='ava_discus' width='150'>";
							echo "<a href='" . base_url() . "/assets/uploads/sip/" . $actual_image_name . "' target='_blank' class='ava_discus'>" . $actual_image_name . "</a>";
						} else
							echo "<span style:'color:red'>Format file yang diijinkan (gif|jpg|png|jpeg|pdf|bmp).</span>";
					} else
						echo "<span style:'color:red'>Sorry, maximum file size should be 700 KB</span>";
				} else
					echo "<span style:'color:red'>Format file yang diijinkan (gif|jpg|png|jpeg|pdf|bmp).</span>";
			} else
				echo "<span style:'color:red'>Please select an image (jpg, gif, png, bmp file) or PDF file.</span>";
		} else
			echo "<span style:'color:red'>Please select an image (jpg, gif, png, bmp file) or PDF file.</span>";
	}

	function bukti_upload_faip()
	{
		if (! $this->session->userdata('user_id')) redirect('login');

		if (isset($_FILES['bukti']['name'])) {
			$valid_formats_img = array("jpg", "jpeg", "gif", "png", "pdf", "bmp");
			$name = $_FILES['bukti']['name'];
			$size = $_FILES['bukti']['size'];

			$extx = pathinfo($name, PATHINFO_EXTENSION);

			if (strlen($name)) {
				//list($txt, $ext) = explode(".", $name);
				if (in_array(strtolower($extx), $valid_formats_img)) {
					if ($size < (710000)) {
						//$actual_image_name = time().substr(str_replace(" ", "_", $txt), 5).".".$ext;
						$actual_image_name = time() . "_BKT_" . $this->session->userdata('user_id') . "." . $extx;

						$config['upload_path'] = './assets/uploads/pay/';
						$config['allowed_types'] = '*';
						$config['max_size']	= '710';
						$config['file_name'] = $actual_image_name;

						$this->load->library('upload', $config);

						if ($this->upload->do_upload('bukti')) {

							/*$config['image_library'] = 'gd2';
							$config['source_image'] = './assets/uploads/pay/'.$actual_image_name;
							$config['maintain_ratio'] = TRUE;
							//$config['width']    = 300;
							//$config['height']   = 300;

							$this->load->library('image_lib', $config); 

							if (!$this->image_lib->resize()) {
								//echo $this->image_lib->display_errors();
							}*/
							/*
							$where = array(
								"user_id" => $this->session->userdata('user_id')
							);
							$row=array(
								'id_file' => $actual_image_name,
								'modifieddate' => date('Y-m-d H:i:s'),
								'modifiedby' => $this->session->userdata('user_id'),	
							);
							$update = $this->main_mod->update('user_profiles',$where,$row);
							*/
							echo "<input type='hidden' id='bukti_image_url' value='" . $actual_image_name . "'>";
							//echo "<img src='".base_url()."/assets/uploads/".$actual_image_name."'  class='ava_discus' width='150'>";
							echo "<a href='" . base_url() . "/assets/uploads/pay/" . $actual_image_name . "' target='_blank' class='ava_discus'>" . $actual_image_name . "</a>";
						} else
							echo "<span style:'color:red'>Format file yang diijinkan (gif|jpg|png|jpeg|pdf|bmp).</span>";
					} else
						echo "<span style:'color:red'>Sorry, maximum file size should be 700 KB</span>";
				} else
					echo "<span style:'color:red'>Format file yang diijinkan (gif|jpg|png|jpeg|pdf|bmp).</span>";
			} else
				echo "<span style:'color:red'>Please select an image (jpg, gif, png, bmp file) or PDF file.</span>";
		} else
			echo "<span style:'color:red'>Please select an image (jpg, gif, png, bmp file) or PDF file.</span>";
	}

	function bukti_upload_kebenaran()
	{
		if (! $this->session->userdata('user_id')) redirect('login');

		if (isset($_FILES['kebenaran']['name'])) {
			$valid_formats_img = array("jpg", "jpeg", "gif", "png", "pdf", "bmp");
			$name = $_FILES['kebenaran']['name'];
			$size = $_FILES['kebenaran']['size'];

			$extx = pathinfo($name, PATHINFO_EXTENSION);

			if (strlen($name)) {
				//list($txt, $ext) = explode(".", $name);
				if (in_array(strtolower($extx), $valid_formats_img)) {
					if ($size < (710000)) {
						//$actual_image_name = time().substr(str_replace(" ", "_", $txt), 5).".".$ext;
						$actual_image_name = time() . "_STR1_" . $this->session->userdata('user_id') . "." . $extx;

						$config['upload_path'] = './assets/uploads/kebenaran/';
						$config['allowed_types'] = '*';
						$config['max_size']	= '710';
						$config['file_name'] = $actual_image_name;

						$this->load->library('upload', $config);

						if ($this->upload->do_upload('kebenaran')) {


							echo "<input type='hidden' id='bukti_kebenaran_image_url' value='" . $actual_image_name . "'>";
							echo "<a href='" . base_url() . "/assets/uploads/kebenaran/" . $actual_image_name . "' target='_blank' class='ava_discus'>" . $actual_image_name . "</a>";
						} else
							echo "<span style:'color:red'>Format file yang diijinkan (gif|jpg|png|jpeg|pdf|bmp).</span>";
					} else
						echo "<span style:'color:red'>Sorry, maximum file size should be 700 KB</span>";
				} else
					echo "<span style:'color:red'>Format file yang diijinkan (gif|jpg|png|jpeg|pdf|bmp).</span>";
			} else
				echo "<span style:'color:red'>Please select an image (jpg, gif, png, bmp file) or PDF file.</span>";
		} else
			echo "<span style:'color:red'>Please select an image (jpg, gif, png, bmp file) or PDF file.</span>";
	}

	function bukti_upload_kodeetik()
	{
		if (! $this->session->userdata('user_id')) redirect('login');

		if (isset($_FILES['kode_etik']['name'])) {
			$valid_formats_img = array("jpg", "jpeg", "gif", "png", "pdf", "bmp");
			$name = $_FILES['kode_etik']['name'];
			$size = $_FILES['kode_etik']['size'];

			$extx = pathinfo($name, PATHINFO_EXTENSION);

			if (strlen($name)) {
				//list($txt, $ext) = explode(".", $name);
				if (in_array(strtolower($extx), $valid_formats_img)) {
					if ($size < (710000)) {
						//$actual_image_name = time().substr(str_replace(" ", "_", $txt), 5).".".$ext;
						$actual_image_name = time() . "_STR2_" . $this->session->userdata('user_id') . "." . $extx;

						$config['upload_path'] = './assets/uploads/kode_etik/';
						$config['allowed_types'] = '*';
						$config['max_size']	= '710';
						$config['file_name'] = $actual_image_name;

						$this->load->library('upload', $config);

						if ($this->upload->do_upload('kode_etik')) {


							echo "<input type='hidden' id='bukti_kode_etik_image_url' value='" . $actual_image_name . "'>";
							echo "<a href='" . base_url() . "/assets/uploads/kode_etik/" . $actual_image_name . "' target='_blank' class='ava_discus'>" . $actual_image_name . "</a>";
						} else
							echo "<span style:'color:red'>Format file yang diijinkan (gif|jpg|png|jpeg|pdf|bmp).</span>";
					} else
						echo "<span style:'color:red'>Sorry, maximum file size should be 700 KB</span>";
				} else
					echo "<span style:'color:red'>Format file yang diijinkan (gif|jpg|png|jpeg|pdf|bmp).</span>";
			} else
				echo "<span style:'color:red'>Please select an image (jpg, gif, png, bmp file) or PDF file.</span>";
		} else
			echo "<span style:'color:red'>Please select an image (jpg, gif, png, bmp file) or PDF file.</span>";
	}

	function updatewizard()
	{
		if ($this->session->userdata('user_id') == '') {
			redirect('auth/login');
		}

		$data['title'] = 'PII | Pemutakhiran Data Anggota';




		$data['m_phone'] = $this->main_mod->msrwhere('m_param', array('code' => 'phone'), 'id', 'asc')->result();
		$data['m_email'] = $this->main_mod->msrwhere('m_param', array('code' => 'email'), 'id', 'asc')->result();
		$data['m_address'] = $this->main_mod->msrwhere('m_param', array('code' => 'address'), 'id', 'asc')->result();
		$data['m_degree'] = $this->main_mod->msrwhere('education_type', null, 'SEQ', 'asc')->result();

		$data['m_school_type'] = $this->main_mod->msrwhere('m_school_type', null, 'id', 'asc')->result();

		//print_r($data['m_degree']);
		/*$data['m_company']=$this->main_mod->msr('m_company','id','asc')->result();
		$data['m_proftype']=$this->main_mod->msr('m_proftype','id','asc')->result();
		$data['m_publicjurnal']=$this->main_mod->msr('m_publicjurnal','id','asc')->result();
		$data['m_publictype']=$this->main_mod->msr('m_publictype','id','asc')->result();
		
		$data['m_fieldofexpert']=$this->main_mod->msr('m_fieldofexpert','id','asc')->result();
		$data['m_accauth']=$this->main_mod->msr('m_accauth','id','asc')->result();
		$data['m_subfield']=$this->main_mod->msr('m_subfield','id','asc')->result();
		*/
		$id = $this->session->userdata('user_id');

		$obj_row = $this->members_model->get_member_by_id($id);
		$data['row'] = $obj_row;

		//print_r($data['row']);

		$data['user_address'] = $this->main_mod->msrwhere('user_address', array('user_id' => $id, 'status' => 1), 'id', 'asc')->result();
		$data['user_email'] = $this->main_mod->msrwhere('contacts', array('user_id' => $id, 'contact_type like "%_email%"' => null, 'status' => 1), 'id', 'asc')->result();
		$data['user_phone'] = $this->main_mod->msrwhere('contacts', array('user_id' => $id, 'contact_type like "%_phone%"' => null, 'status' => 1), 'id', 'asc')->result();
		$data['user_edu'] = $this->main_mod->msrwhere('user_edu', array('user_id' => $id, 'status' => 1), 'id', 'asc')->result();
		$data['user_cert'] = $this->main_mod->msrwhere('user_cert', array('user_id' => $id, 'status' => 1), 'id', 'asc')->result();
		$data['user_cert_pii'] = $this->main_mod->msrwhere('user_cert', array('user_id' => $id, 'status' => 2, 'endyear >= CURDATE()' => null), 'id', 'desc')->result();
		$data['user_exp'] = $this->main_mod->msrwhere('user_exp', array('user_id' => $id, 'status' => 1), 'id', 'asc')->result();

		//$data['user_email']=$this->main_mod->msrwhere('user_email',array('user_id'=>$id),'id','asc')->result();
		//$data['user_phone']=$this->main_mod->msrwhere('user_phone',array('user_id'=>$id),'id','asc')->result();
		/*
		$data['user_award']=$this->main_mod->msrwhere('user_award',array('user_id'=>$id),'id','asc')->result();
		
		$data['user_course']=$this->main_mod->msrwhere('user_course',array('user_id'=>$id),'id','asc')->result();
		
		
		$data['user_org']=$this->main_mod->msrwhere('user_org',array('user_id'=>$id),'id','asc')->result();		
		$data['user_prof']=$this->main_mod->msrwhere('user_prof',array('user_id'=>$id),'id','asc')->result();
		$data['user_publication']=$this->main_mod->msrwhere('user_publication',array('user_id'=>$id),'id','asc')->result();
		$data['user_skill']=$this->main_mod->msrwhere('user_skill',array('user_id'=>$id),'id','asc')->result();
		$data['user_reg']=$this->main_mod->msrwhere('user_reg',array('user_id'=>$id),'id','asc')->result();
		*/
		$data['emailx'] = $this->session->userdata('email');


		$this->load->view('member/updatewizard_view', $data);
	}

	function profile()
	{
		if ($this->session->userdata('user_id') == '') {
			redirect('auth/login');
		}
		$this->load->model('main_mod');
		$this->load->model('members_model');
		$data['title'] = 'PII | Profile';
		$id = $this->session->userdata('user_id');

		$obj_row = $this->members_model->get_member_by_id($id);
		$data['row'] = $obj_row;
		$data['user_address'] = $this->main_mod->msrwhere('user_address', array('user_id' => $id, 'status' => 1), 'id', 'asc')->result();
		$data['user_email'] = $this->main_mod->msrwhere('contacts', array('user_id' => $id, 'contact_type like "%_email%"' => null, 'status' => 1), 'id', 'asc')->result();
		$data['user_phone'] = $this->main_mod->msrwhere('contacts', array('user_id' => $id, 'contact_type like "%_phone%"' => null, 'status' => 1), 'id', 'asc')->result();
		$data['user_edu'] = $this->main_mod->msrwhere('user_edu', array('user_id' => $id, 'status' => 1), 'id', 'asc')->result();
		$data['user_cert'] = $this->main_mod->msrwhere('user_cert', array('user_id' => $id, 'status<>0' => null), 'id', 'asc')->result();
		$data['user_exp'] = $this->main_mod->msrwhere('user_exp', array('user_id' => $id, 'status' => 1), 'id', 'asc')->result();

		$data['gelar1'] = $this->main_mod->msrwhere('user_edu', array('user_id' => $id, 'status' => 1, 'type' => 1), 'degree', 'desc')->result();
		$data['gelar2'] = $this->main_mod->msrwhere('user_edu', array('user_id' => $id, 'status' => 1, 'type' => 2), 'startdate', 'desc')->result();

		$data['m_phone'] = $this->main_mod->msrwhere('m_param', array('code' => 'phone'), 'id', 'asc')->result();
		$data['m_email'] = $this->main_mod->msrwhere('m_param', array('code' => 'email'), 'id', 'asc')->result();
		$data['m_address'] = $this->main_mod->msrwhere('m_param', array('code' => 'address'), 'id', 'asc')->result();
		$data['m_degree'] = $this->main_mod->msrwhere('education_type', null, 'SEQ', 'asc')->result();
		$data['m_school_type'] = $this->main_mod->msrwhere('m_school_type', null, 'id', 'asc')->result();

		$data['emailx'] = $this->session->userdata('email');
		$this->load->view('member/profile_view', $data);
	}


	//Secure File Access BY Rizal
	public function idcard_file($filename = '')
	{
		// Pastikan sudah login
		if (!$this->session->userdata('user_id')) {
			redirect('auth/login');
		}

		// Cegah path traversal
		$filename = basename($filename);

		$user_id = $this->session->userdata('user_id');

		// --- Validasi file terhadap user_profiles ---
		$this->db->where('user_id', $user_id);
		$this->db->where('id_file', $filename);
		$row = $this->db->get('user_profiles')->row();

		if (!$row) {
			// Kalau bukan file miliknya
			show_error('Anda tidak berhak mengakses file ini', 403);
			return;
		}

		// Path file di server
		$filepath = FCPATH . 'assets/uploads/' . $filename;

		if (file_exists($filepath)) {
			$mime = mime_content_type($filepath);
			header('Content-Type: ' . $mime);
			header('Content-Length: ' . filesize($filepath));
			header('Cache-Control: private');
			header('Content-Disposition: inline; filename="' . $filename . '"');
			readfile($filepath);
			exit;
		} else {
			show_404();
		}
	}




	function profile_old()
	{
		if ($this->session->userdata('user_id') == '') {
			redirect('auth/login');
		}
		$this->load->model('main_mod');
		$this->load->model('members_model');
		//Profile
		$this->form_validation->set_rules('fn', 'Firstname', 'trim|required|xss_clean');
		$this->form_validation->set_rules('ln', 'Lastname', 'trim|required|xss_clean');
		$this->form_validation->set_rules('gender', 'Gender', 'trim|required|xss_clean');
		$this->form_validation->set_rules('ktp', 'ID Card', 'trim|required|xss_clean|numeric');
		$this->form_validation->set_rules('dob', 'Date of Birth', 'trim|required|xss_clean');
		$this->form_validation->set_rules('website', 'Website', 'trim|xss_clean');
		$this->form_validation->set_rules('is_public', 'is_public', 'trim|xss_clean');
		$this->form_validation->set_rules('is_datasend', 'is_datasend', 'trim|xss_clean');
		$this->form_validation->set_rules('desc', 'Description', 'trim|xss_clean');
		$this->form_validation->set_rules('mailingaddr', 'mailing addr', 'trim|xss_clean');

		$this->form_validation->set_rules('fieldofexpert', 'fieldofexpert', 'trim|xss_clean');
		$this->form_validation->set_rules('subfield', 'subfield', 'trim|xss_clean');
		$this->form_validation->set_rules('accauth', 'accauth', 'trim|xss_clean');
		$this->form_validation->set_rules('filename', 'filename', 'trim|xss_clean');
		$this->form_validation->set_rules('desc2', 'desc2', 'trim|xss_clean');

		$this->form_validation->set_rules('email[]', 'Email', 'trim|required|xss_clean|valid_email');



		$data['typephone'] = $this->input->post('typephone[]') <> null ? $this->input->post('typephone[]') : "";
		$data['phone'] = $this->input->post('phone[]') <> null ? $this->input->post('phone[]') : "";

		$data['typeemail'] = $this->input->post('typeemail[]') <> null ? $this->input->post('typeemail[]') : "";
		$data['email'] = $this->input->post('email[]') <> null ? $this->input->post('email[]') : "";

		$data['typeaddress'] = $this->input->post('typeaddress[]') <> null ? $this->input->post('typeaddress[]') : "";
		$data['address'] = $this->input->post('address[]') <> null ? $this->input->post('address[]') : "";
		$data['addressphone'] = $this->input->post('addressphone[]') <> null ? $this->input->post('addressphone[]') : "";
		$data['addresszip'] = $this->input->post('addresszip[]') <> null ? $this->input->post('addresszip[]') : "";

		//print_r($this->form_validation);


		if ($this->form_validation->run() === FALSE) {
			$data['m_phone'] = $this->main_mod->msr('m_phone', 'id', 'asc')->result();
			$data['m_email'] = $this->main_mod->msr('m_email', 'id', 'asc')->result();
			$data['m_address'] = $this->main_mod->msr('m_address', 'id', 'asc')->result();
			$data['m_company'] = $this->main_mod->msr('m_company', 'id', 'asc')->result();
			$data['m_proftype'] = $this->main_mod->msr('m_proftype', 'id', 'asc')->result();
			$data['m_publicjurnal'] = $this->main_mod->msr('m_publicjurnal', 'id', 'asc')->result();
			$data['m_publictype'] = $this->main_mod->msr('m_publictype', 'id', 'asc')->result();

			$data['m_fieldofexpert'] = $this->main_mod->msr('m_fieldofexpert', 'id', 'asc')->result();
			$data['m_accauth'] = $this->main_mod->msr('m_accauth', 'id', 'asc')->result();
			$data['m_subfield'] = $this->main_mod->msr('m_subfield', 'id', 'asc')->result();

			$id = $this->session->userdata('user_id');

			$obj_row = $this->members_model->get_member_by_id($id);
			$data['row'] = $obj_row;
			$data['user_address'] = $this->main_mod->msrwhere('user_address', array('user_id' => $id), 'id', 'asc')->result();
			$data['user_award'] = $this->main_mod->msrwhere('user_award', array('user_id' => $id), 'id', 'asc')->result();
			$data['user_cert'] = $this->main_mod->msrwhere('user_cert', array('user_id' => $id), 'id', 'asc')->result();
			$data['user_course'] = $this->main_mod->msrwhere('user_course', array('user_id' => $id), 'id', 'asc')->result();
			$data['user_edu'] = $this->main_mod->msrwhere('user_edu', array('user_id' => $id), 'id', 'asc')->result();
			$data['user_email'] = $this->main_mod->msrwhere('user_email', array('user_id' => $id), 'id', 'asc')->result();
			$data['user_exp'] = $this->main_mod->msrwhere('user_exp', array('user_id' => $id), 'id', 'asc')->result();
			$data['user_org'] = $this->main_mod->msrwhere('user_org', array('user_id' => $id), 'id', 'asc')->result();
			$data['user_phone'] = $this->main_mod->msrwhere('user_phone', array('user_id' => $id), 'id', 'asc')->result();
			$data['user_prof'] = $this->main_mod->msrwhere('user_prof', array('user_id' => $id), 'id', 'asc')->result();
			$data['user_publication'] = $this->main_mod->msrwhere('user_publication', array('user_id' => $id), 'id', 'asc')->result();
			$data['user_skill'] = $this->main_mod->msrwhere('user_skill', array('user_id' => $id), 'id', 'asc')->result();
			$data['user_reg'] = $this->main_mod->msrwhere('user_reg', array('user_id' => $id), 'id', 'asc')->result();

			$data['emailx'] = $this->session->userdata('email');
			$this->load->view('member/profile', $data);
		} else {

			$data['typephone'] = $this->input->post('typephone[]') <> null ? $this->input->post('typephone[]') : "";
			$data['phone'] = $this->input->post('phone[]') <> null ? $this->input->post('phone[]') : "";

			$data['typeemail'] = $this->input->post('typeemail[]') <> null ? $this->input->post('typeemail[]') : "";
			$data['email'] = $this->input->post('email[]') <> null ? $this->input->post('email[]') : "";

			$data['typeaddress'] = $this->input->post('typeaddress[]') <> null ? $this->input->post('typeaddress[]') : "";
			$data['address'] = $this->input->post('address[]') <> null ? $this->input->post('address[]') : "";
			$data['addressphone'] = $this->input->post('addressphone[]') <> null ? $this->input->post('addressphone[]') : "";
			$data['addresszip'] = $this->input->post('addresszip[]') <> null ? $this->input->post('addresszip[]') : "";

			//UPLOAD
			$document = $this->upload();
			$nameDoc = "";
			if (isset($document['status'])) {
				$nameDoc = $document['message'];
			}

			//PROFILE
			$where = array(
				"user_id" => $this->session->userdata('user_id')
			);
			$row = array(
				'firstname' => $this->form_validation->set_value('fn'),
				'lastname' => $this->form_validation->set_value('ln'),
				'gender' => $this->form_validation->set_value('gender'),
				'idcard' => $this->form_validation->set_value('ktp'),
				'dob' => $this->form_validation->set_value('dob'),
				'website' => $this->form_validation->set_value('website'),
				'is_public' => $this->form_validation->set_value('is_public'),
				'is_datasend' => $this->form_validation->set_value('is_datasend'),
				'description' => $this->form_validation->set_value('desc'),

				'fieldofexpert' => $this->form_validation->set_value('fieldofexpert'),
				'accauth' => $this->form_validation->set_value('accauth'),
				'subfield' => $this->form_validation->set_value('subfield'),
				'document' => $nameDoc,
				'description2' => $this->form_validation->set_value('desc2')
			);
			$update = $this->main_mod->update('user_profiles', $where, $row);

			//PHONE
			$delete = $this->main_mod->delete('user_phone', 'user_id', $this->session->userdata('user_id'));
			$i = 0;
			foreach ($data['typephone'] as $val) {
				$row = array(
					'user_id' => $this->session->userdata('user_id'),
					'phonetype' => $data['typephone'][$i],
					'phonenumber' => $data['phone'][$i]
				);
				$insert = $this->main_mod->insert('user_phone', $row);
				$i++;
			}
			//EMAIL
			$delete = $this->main_mod->delete('user_email', 'user_id', $this->session->userdata('user_id'));
			$i = 0;
			foreach ($data['typeemail'] as $val) {
				$row = array(
					'user_id' => $this->session->userdata('user_id'),
					'emailtype' => $data['typeemail'][$i],
					'email' => $data['email'][$i]
				);
				$insert = $this->main_mod->insert('user_email', $row);
				$i++;
			}
			//ADDRESS
			$delete = $this->main_mod->delete('user_address', 'user_id', $this->session->userdata('user_id'));
			$i = 0;
			$mailing = $this->form_validation->set_value('mailingaddr');
			$mailing = $mailing - 1;
			foreach ($data['typeaddress'] as $val) {
				$temp = 0;
				if ($mailing == $i)
					$temp = 1;
				$row = array(
					'user_id' => $this->session->userdata('user_id'),
					'addresstype' => $data['typeaddress'][$i],
					'address' => $data['address'][$i],
					'notelp' => $data['addressphone'][$i],
					'zipcode' => $data['addresszip'][$i],
					'is_mailing' => $temp
				);
				$insert = $this->main_mod->insert('user_address', $row);
				$i++;
			}
			redirect('member/profile');
		}
	}

	function edit_profile()
	{
		if ($this->session->userdata('user_id') == '') {
			redirect('auth/login');
		}
		$this->load->model('main_mod');
		//PROFILE

		$this->form_validation->set_rules('fn', 'Firstname', 'trim|required|xss_clean');
		$this->form_validation->set_rules('ln', 'Lastname', 'trim|xss_clean');
		$this->form_validation->set_rules('gender', 'Gender', 'trim|required|xss_clean');
		$this->form_validation->set_rules('phone', 'Mobile Phone', 'trim|required|xss_clean');
		$this->form_validation->set_rules('birthplace', 'Birthplace', 'trim|required|xss_clean');
		$this->form_validation->set_rules('typeid', 'Citizen', 'trim|required|xss_clean');
		$this->form_validation->set_rules('idnumber', 'ID', 'trim|required|xss_clean');
		$this->form_validation->set_rules('dob', 'Date of Birth', 'trim|required|xss_clean');
		$this->form_validation->set_rules('website', 'Website', 'trim|xss_clean');
		$this->form_validation->set_rules('is_public', 'is_public', 'trim|xss_clean');
		$this->form_validation->set_rules('is_datasend', 'is_datasend', 'trim|xss_clean');
		$this->form_validation->set_rules('desc', 'Description', 'trim|xss_clean');
		$this->form_validation->set_rules('mailingaddr', 'mailing addr', 'required|trim|xss_clean');
		$this->form_validation->set_rules('addressid', 'id', 'trim|xss_clean');
		$this->form_validation->set_rules('warga_asing', 'Warga Negara', 'required|trim|xss_clean');

		if ($this->form_validation->run()) {

			$fn = $this->input->post('fn') <> null ? $this->input->post('fn') : "";
			$ln = $this->input->post('ln') <> null ? $this->input->post('ln') : "";
			$dob = $this->input->post('dob') <> null ? $this->input->post('dob') : "";
			$phone = $this->input->post('phone') <> null ? $this->input->post('phone') : "";
			$birthplace = $this->input->post('birthplace') <> null ? $this->input->post('birthplace') : "";
			$typeid = $this->input->post('typeid') <> null ? $this->input->post('typeid') : "";
			$website = $this->input->post('website') <> null ? $this->input->post('website') : "";
			$warga_asing = $this->input->post('warga_asing') <> null ? $this->input->post('warga_asing') : "";
			$desc = $this->input->post('desc') <> null ? $this->input->post('desc') : "";
			$gender = $this->input->post('gender') <> null ? $this->input->post('gender') : "";
			$idnumber = $this->input->post('idnumber') <> null ? $this->input->post('idnumber') : "";
			$is_public = $this->input->post('is_public') <> null ? $this->input->post('is_public') : "";
			$is_datasend = $this->input->post('is_datasend') <> null ? $this->input->post('is_datasend') : "";
			//$mailing = $this->input->post('mailingaddr')<>null?$this->input->post('mailingaddr'):"";

			$email = $this->input->post('email[]') <> null ? $this->input->post('email[]') : "";
			$typeaddress = $this->input->post('typeaddress[]') <> null ? $this->input->post('typeaddress[]') : "";
			$address = $this->input->post('address[]') <> null ? $this->input->post('address[]') : "";
			$addressphone = $this->input->post('addressphone[]') <> null ? $this->input->post('addressphone[]') : "";
			$addresscity = $this->input->post('addresscity[]') <> null ? $this->input->post('addresscity[]') : "";
			$addressprovince = $this->input->post('addressprovince[]') <> null ? $this->input->post('addressprovince[]') : "";
			$addresszip = $this->input->post('addresszip[]') <> null ? $this->input->post('addresszip[]') : "";
			$mailing = $this->input->post('mailingaddr') <> null ? $this->input->post('mailingaddr') : "";
			$addressid = $this->input->post('addressid[]') <> null ? $this->input->post('addressid[]') : "";

			$emailm = $this->input->post('emailm[]') <> null ? $this->input->post('emailm[]') : "";
			$typeemail = $this->input->post('typeemail[]') <> null ? $this->input->post('typeemail[]') : "";
			$emailid = $this->input->post('emailid[]') <> null ? $this->input->post('emailid[]') : "";

			$phonem = $this->input->post('phonem[]') <> null ? $this->input->post('phonem[]') : "";
			$typephone = $this->input->post('typephone[]') <> null ? $this->input->post('typephone[]') : "";
			$phoneid = $this->input->post('phoneid[]') <> null ? $this->input->post('phoneid[]') : "";
			try {
				$where = array(
					"user_id" => $this->session->userdata('user_id')
				);
				$row = array(
					'firstname' => $fn,
					'lastname' => $ln,
					'gender' => $gender,
					'idtype' => $typeid,
					'idcard' => $idnumber,
					'mobilephone' => str_replace("-", "", str_replace(" ", "", $phone)),
					'birthplace' => strtoupper($birthplace),
					'dob' => date('Y-m-d', strtotime($dob)),
					'website' => $website,
					'warga_asing' => $warga_asing,
					'is_public' => ($is_public == "true" ? "1" : "0"),
					'is_datasend' => ($is_datasend == "true" ? "1" : "0"),
					'description' => $desc,
					'modifieddate' => date('Y-m-d H:i:s'),
					'modifiedby' => $this->session->userdata('user_id'),
				);
				$update = $this->main_mod->update('user_profiles', $where, $row);

				//ADDRESS

				$where = array(
					"user_id" => $this->session->userdata('user_id')
				);
				$row = array(
					'status' => 0,
					'modifieddate' => date('Y-m-d H:i:s'),
					'modifiedby' => $this->session->userdata('user_id'),
				);
				$update = $this->main_mod->update('user_address', $where, $row);


				$i = 0;
				$mailing = $mailing - 1;
				foreach ($typeaddress as $val) {
					$temp = 0;
					if ($mailing == $i)
						$temp = 1;
					$row = array(
						'user_id' => $this->session->userdata('user_id'),
						'addresstype' => $typeaddress[$i],
						'address' => $address[$i],
						'phone' => $addressphone[$i],
						'email' => $email[$i],
						'city' => $addresscity[$i],
						'province' => $addressprovince[$i],
						'zipcode' => $addresszip[$i],
						'is_mailing' => $temp,
						'createdby' => $this->session->userdata('user_id'),
					);
					//$insert = $this->main_mod->insert('user_address',$row);


					if ($addressid[$i] == '' || $addressid[$i] == '0')
						$catch = $this->main_mod->insert('user_address', $row);
					else {
						$row = array(
							'user_id' => $this->session->userdata('user_id'),
							'addresstype' => $typeaddress[$i],
							'address' => $address[$i],
							'phone' => $addressphone[$i],
							'email' => $email[$i],
							'city' => $addresscity[$i],
							'province' => $addressprovince[$i],
							'zipcode' => $addresszip[$i],
							'is_mailing' => $temp,
							'status' => 1,
							'modifieddate' => date('Y-m-d H:i:s'),
							'modifiedby' => $this->session->userdata('user_id'),
						);
						$where = array(
							"user_id" => $this->session->userdata('user_id'),
							'id' => $addressid[$i],
						);
						$catch = $this->main_mod->update('user_address', $where, $row);
					}

					$i++;
				}


				//Email

				$where = array(
					"user_id" => $this->session->userdata('user_id')
				);
				$row = array(
					'status' => 0,
					'updated_at' => date('Y-m-d H:i:s'),
					//'modifiedby' => $this->session->userdata('user_id'),	
				);
				$update = $this->main_mod->update('contacts', $where, $row);


				$i = 0;
				foreach ($typeemail as $val) {
					$row = array(
						'user_id' => $this->session->userdata('user_id'),
						'contact_type' => $typeemail[$i],
						'contact_value' => $emailm[$i],
						//'created_at' => $this->session->userdata('user_id'),
					);
					//$insert = $this->main_mod->insert('user_address',$row);


					if ($emailid[$i] == '' || $emailid[$i] == '0')
						$catch = $this->main_mod->insert('contacts', $row);
					else {
						$row = array(
							'user_id' => $this->session->userdata('user_id'),
							'contact_type' => $typeemail[$i],
							'contact_value' => $emailm[$i],
							'status' => 1,
							'updated_at' => date('Y-m-d H:i:s'),
							//'modifiedby' => $this->session->userdata('user_id'),
						);
						$where = array(
							"user_id" => $this->session->userdata('user_id'),
							'id' => $emailid[$i],
						);
						$catch = $this->main_mod->update('contacts', $where, $row);
					}

					$i++;
				}

				//Phone

				/*$where = array(
					"user_id" => $this->session->userdata('user_id')
				);
				$row=array(
					'status' => 0,
					'updated_at' => date('Y-m-d H:i:s'),
					//'modifiedby' => $this->session->userdata('user_id'),	
				);
				$update = $this->main_mod->update('contacts',$where,$row);
				*/

				$i = 0;
				foreach ($typephone as $val) {
					$row = array(
						'user_id' => $this->session->userdata('user_id'),
						'contact_type' => $typephone[$i],
						'contact_value' => $phonem[$i],
						//'createdby' => $this->session->userdata('user_id'),
					);
					//$insert = $this->main_mod->insert('user_address',$row);


					if ($phoneid[$i] == '' || $phoneid[$i] == '0')
						$catch = $this->main_mod->insert('contacts', $row);
					else {
						$row = array(
							'user_id' => $this->session->userdata('user_id'),
							'contact_type' => $typephone[$i],
							'contact_value' => $phonem[$i],
							'status' => 1,
							'updated_at' => date('Y-m-d H:i:s'),
							//'modifiedby' => $this->session->userdata('user_id'),
						);
						$where = array(
							"user_id" => $this->session->userdata('user_id'),
							'id' => $phoneid[$i],
						);
						$catch = $this->main_mod->update('contacts', $where, $row);
					}

					$i++;
				}

				echo "valid";
			} catch (Exception $e) {
				//print_r($e);
				echo "not valid";
			}
		} else
			echo "not valid";
	}

	function edit_address_old()
	{
		if ($this->session->userdata('user_id') == '') {
			redirect('auth/login');
		}
		$this->load->model('main_mod');
		//Address

		$this->form_validation->set_rules('mailingaddr', 'mailing addr', 'required|trim|xss_clean');

		if ($this->form_validation->run()) {

			$email = $this->input->post('email[]') <> null ? $this->input->post('email[]') : "";
			$typeaddress = $this->input->post('typeaddress[]') <> null ? $this->input->post('typeaddress[]') : "";
			$address = $this->input->post('address[]') <> null ? $this->input->post('address[]') : "";
			$addressphone = $this->input->post('addressphone[]') <> null ? $this->input->post('addressphone[]') : "";
			$addresscity = $this->input->post('addresscity[]') <> null ? $this->input->post('addresscity[]') : "";
			$addressprovince = $this->input->post('addressprovince[]') <> null ? $this->input->post('addressprovince[]') : "";
			$addresszip = $this->input->post('addresszip[]') <> null ? $this->input->post('addresszip[]') : "";
			$mailing = $this->input->post('mailingaddr') <> null ? $this->input->post('mailingaddr') : "";


			try {

				$where = array(
					"user_id" => $this->session->userdata('user_id')
				);
				$row = array(
					'status' => 0,
					'modifieddate' => date('Y-m-d H:i:s'),
					'modifiedby' => $this->session->userdata('user_id'),
				);
				$update = $this->main_mod->update('user_address', $where, $row);


				$i = 0;
				$mailing = $mailing - 1;
				foreach ($typeaddress as $val) {
					$temp = 0;
					if ($mailing == $i)
						$temp = 1;
					$row = array(
						'user_id' => $this->session->userdata('user_id'),
						'addresstype' => $typeaddress[$i],
						'address' => $address[$i],
						'phone' => $addressphone[$i],
						'email' => $email[$i],
						'city' => $addresscity[$i],
						'province' => $addressprovince[$i],
						'zipcode' => $addresszip[$i],
						'is_mailing' => $temp,
						'createdby' => $this->session->userdata('user_id'),
					);
					$insert = $this->main_mod->insert('user_address', $row);
					$i++;
				}


				echo "valid";
				/*
				$id = $this->session->userdata('user_id');
				$this->load->model('members_model');
				$obj_row = $this->members_model->get_member_by_id($id);			
				$data['row'] = $obj_row;
				$data['emailx'] = $this->session->userdata('email');
				$this->load->view('member/edit_profile', $data);*/
			} catch (Exception $e) {
				print_r($e);
			}
		} else
			echo "not valid";
	}

	function del_address()
	{
		if ($this->session->userdata('user_id') == '') {
			redirect('auth/login');
		}
		$this->load->model('main_mod');
		//EXPERIENCE
		$id = $this->input->post('id') <> null ? $this->input->post('id') : "";
		if ($id != '') {
			try {
				$check = $this->main_mod->msrwhere('user_address', array('user_id' => $this->session->userdata('user_id'), 'id' => $id), 'id', 'desc')->result();
				if (isset($check[0])) {
					$update = $this->main_mod->delete('user_address', 'id', $id);
					//echo 1;
				}
				echo "valid";
			} catch (Exception $e) {
				//print_r($e);
				echo "not valid";
			}
		} else
			echo "not valid";
	}

	function edit_profile_old()
	{
		if ($this->session->userdata('user_id') == '') {
			redirect('auth/login');
		}
		$this->load->model('main_mod');
		//PROFILE

		$this->form_validation->set_rules('fn', 'Firstname', 'trim|required|xss_clean');
		$this->form_validation->set_rules('ln', 'Lastname', 'trim|required|xss_clean');
		$this->form_validation->set_rules('gender', 'Gender', 'trim|required|xss_clean');
		$this->form_validation->set_rules('ktp', 'ID', 'trim|required|xss_clean');
		$this->form_validation->set_rules('dob', 'Date of Birth', 'trim|required|xss_clean');
		$this->form_validation->set_rules('website', 'Website', 'trim|xss_clean');
		$this->form_validation->set_rules('is_public', 'is_public', 'trim|xss_clean');
		$this->form_validation->set_rules('is_datasend', 'is_datasend', 'trim|xss_clean');
		$this->form_validation->set_rules('desc', 'Description', 'trim|xss_clean');
		$this->form_validation->set_rules('mailingaddr', 'mailing addr', 'trim|xss_clean');

		if ($this->form_validation->run()) {
			$typephone = $this->input->post('typephone[]') <> null ? $this->input->post('typephone[]') : "";
			$phone = $this->input->post('phone[]') <> null ? $this->input->post('phone[]') : "";
			$typeemail = $this->input->post('typeemail[]') <> null ? $this->input->post('typeemail[]') : "";
			$email = $this->input->post('email[]') <> null ? $this->input->post('email[]') : "";
			$typeaddress = $this->input->post('typeaddress[]') <> null ? $this->input->post('typeaddress[]') : "";
			$address = $this->input->post('address[]') <> null ? $this->input->post('address[]') : "";
			//$addressphone = $this->input->post('addressphone[]')<>null?$this->input->post('addressphone[]'):"";
			$addresscity = $this->input->post('addresscity[]') <> null ? $this->input->post('addresscity[]') : "";
			$addressprovince = $this->input->post('addressprovince[]') <> null ? $this->input->post('addressprovince[]') : "";
			$addresszip = $this->input->post('addresszip[]') <> null ? $this->input->post('addresszip[]') : "";

			$fn = $this->input->post('fn') <> null ? $this->input->post('fn') : "";
			$ln = $this->input->post('ln') <> null ? $this->input->post('ln') : "";
			$dob = $this->input->post('dob') <> null ? $this->input->post('dob') : "";
			$website = $this->input->post('website') <> null ? $this->input->post('website') : "";
			$desc = $this->input->post('desc') <> null ? $this->input->post('desc') : "";
			$gender = $this->input->post('gender') <> null ? $this->input->post('gender') : "";
			$ktp = $this->input->post('ktp') <> null ? $this->input->post('ktp') : "";
			$is_public = $this->input->post('is_public') <> null ? $this->input->post('is_public') : "";
			$is_datasend = $this->input->post('is_datasend') <> null ? $this->input->post('is_datasend') : "";
			$mailing = $this->input->post('mailingaddr') <> null ? $this->input->post('mailingaddr') : "";


			try {
				$where = array(
					"user_id" => $this->session->userdata('user_id')
				);
				$row = array(
					'firstname' => $fn,
					'lastname' => $ln,
					'gender' => $gender,
					'idcard' => $ktp,
					'dob' => $dob,
					'website' => $website,
					'is_public' => ($is_public == "true" ? "1" : "0"),
					'is_datasend' => ($is_datasend == "true" ? "1" : "0"),
					'description' => $desc,
				);
				$update = $this->main_mod->update('user_profiles', $where, $row);

				$this->main_mod->delete('user_phone', 'user_id', $this->session->userdata('user_id'));
				$this->main_mod->delete('user_email', 'user_id', $this->session->userdata('user_id'));
				$this->main_mod->delete('user_address', 'user_id', $this->session->userdata('user_id'));

				//PHONE
				$i = 0;
				foreach ($typephone as $val) {
					$row = array(
						'user_id' => $this->session->userdata('user_id'),
						'phonetype' => $typephone[$i],
						'phonenumber' => $phone[$i]
					);
					$insert = $this->main_mod->insert('user_phone', $row);
					$i++;
				}
				//EMAIL
				$i = 0;
				foreach ($typeemail as $val) {
					$row = array(
						'user_id' => $this->session->userdata('user_id'),
						'emailtype' => $typeemail[$i],
						'email' => $email[$i]
					);
					$insert = $this->main_mod->insert('user_email', $row);
					$i++;
				}
				//ADDRESS
				$i = 0;
				$mailing = $mailing - 1;
				foreach ($typeaddress as $val) {
					$temp = 0;
					if ($mailing == $i)
						$temp = 1;
					$row = array(
						'user_id' => $this->session->userdata('user_id'),
						'addresstype' => $typeaddress[$i],
						'address' => $address[$i],
						//'notelp' => $addressphone[$i],
						'city' => $addresscity[$i],
						'province' => $addressprovince[$i],
						'zipcode' => $addresszip[$i],
						'is_mailing' => $temp
					);
					$insert = $this->main_mod->insert('user_address', $row);
					$i++;
				}


				$data['m_phone'] = $this->main_mod->msr('m_phone', 'id', 'asc')->result();
				$data['m_email'] = $this->main_mod->msr('m_email', 'id', 'asc')->result();
				$data['m_address'] = $this->main_mod->msr('m_address', 'id', 'asc')->result();
				$id = $this->session->userdata('user_id');
				$this->load->model('members_model');
				$obj_row = $this->members_model->get_member_by_id($id);
				$data['row'] = $obj_row;
				$data['user_address'] = $this->main_mod->msrwhere('user_address', array('user_id' => $id), 'id', 'asc')->result();
				$data['user_email'] = $this->main_mod->msrwhere('user_email', array('user_id' => $id), 'id', 'asc')->result();
				$data['user_phone'] = $this->main_mod->msrwhere('user_phone', array('user_id' => $id), 'id', 'asc')->result();
				$data['emailx'] = $this->session->userdata('email');
				$this->load->view('member/edit_profile', $data);
			} catch (Exception $e) {
				print_r($e);
			}
		} else
			echo "not valid";
	}

	function download_kta()
	{
		if ($this->session->userdata('user_id') == '') {
			redirect('auth/login');
		}
		$id = $this->session->userdata('user_id');
		$members = $this->main_mod->msrwhere('members', array('person_id' => $id, 'status' => 1), 'id', 'desc')->result();
		$user_profiles = $this->main_mod->msrwhere('user_profiles', array('user_id' => $id), 'id', 'desc')->result();

		if (isset($members[0]->no_kta) && isset($user_profiles[0]->firstname)) {

			$name = trim(strtoupper($user_profiles[0]->firstname)) . " " . trim(strtoupper($user_profiles[0]->lastname));
			$photo = (($user_profiles[0]->photo != '') ? base_url() . 'assets/uploads/' . $user_profiles[0]->photo : "");
			$no_kta = str_pad($members[0]->code_wilayah, 4, '0', STR_PAD_LEFT) . '.' . str_pad($members[0]->code_bk_hkk, 2, '0', STR_PAD_LEFT) . '.' . str_pad($members[0]->no_kta, 6, '0', STR_PAD_LEFT);
			$nim = str_pad($members[0]->no_kta, 6, '0', STR_PAD_LEFT);

			$phpdate = strtotime($members[0]->from_date);
			$from_date = date('m/Y', $phpdate);

			$phpdate = strtotime($members[0]->thru_date);
			$thru_date = date('m/Y', $phpdate);


			//BARCODE
			$this->load->library('zend');
			//load yang ada di folder Zend
			$this->zend->load('Zend/Barcode');
			//generate barcodenya
			//$kode = 12345abc;
			$img = Zend_Barcode::draw('code128', 'image', array('text' => $nim, 'drawText' => false, 'barThinWidth' => 2, 'backgroundColor' => '#FE6601'), array());
			$code = $nim;
			$store_image = imagepng($img, FCPATH . "./assets/uploads/barcode/{$code}.png");
			//return $code.'.png';
			$barcode = base_url() . 'assets/uploads/barcode/' . $code . '.png';
			//BARCODE

			//QR
			/*
		$this->load->library('ciqrcode'); //pemanggilan library QR CODE
 
        $config['cacheable']    = true; //boolean, the default is true
        $config['cachedir']     = './assets/uploads/qr/'; //string, the default is application/cache/
        $config['errorlog']     = './assets/uploads/qr/'; //string, the default is application/logs/
        $config['imagedir']     = './assets/uploads/qr/'; //direktori penyimpanan qr code
        $config['quality']      = true; //boolean, the default is true
        $config['size']         = '1024'; //interger, the default is 1024
        $config['black']        = array(224,255,255); // array, default is array(255,255,255)
        $config['white']        = array(70,130,180); // array, default is array(0,0,0)
        $this->ciqrcode->initialize($config);
 
        $image_name=$nim.'.png'; //buat name dari qr code sesuai dengan nim
 
        $params['data'] = $nim; //data yang akan di jadikan QR CODE
        $params['level'] = 'H'; //H=High
        $params['size'] = 10;
        $params['savename'] = FCPATH.$config['imagedir'].$image_name; //simpan image QR CODE ke folder assets/images/
        $this->ciqrcode->generate($params); // fungsi untuk generate QR CODE
		*/
			//



			//print_r($user_profiles[0]->photo);
			$this->load->library('Pdf');
			$pdf = new Pdf('L', 'mm', 'A4', true, 'UTF-8', false);

			$pdf->SetProtection(array('modify'));

			$pdf->setPrintHeader(false);
			$pdf->setPrintFooter(false);

			// set margins
			$pdf->SetMargins(0, 0, 0, true);

			// set auto page breaks false
			$pdf->SetAutoPageBreak(false, 0);

			// add a page
			$pdf->AddPage('L', 'A4');

			// Display image on full page
			//$pdf->Image('background.jpg', 0, 0, 210, 297, 'JPG', '', '', true, 200, '', false, false, 0, false, false, true);
			$img_file = FCPATH . './assets/images/background.jpg';
			$pdf->Image($img_file, 0, 0, 297, 210, '', '', '', false, 300, '', false, false, 0);

			/*
<h1>Welcome to <a href="http://www.tcpdf.org" style="text-decoration:none;background-color:#CC0000;color:black;">&nbsp;<span style="color:black;">TC</span><span style="color:white;">PDF</span>&nbsp;</a>!</h1>
<i>This is the first example of TCPDF library.</i>
<p>This text is printed using the <i>writeHTMLCell()</i> method but you can also use: <i>Multicell(), writeHTML(), Write(), Cell() and Text()</i>.</p>
<p>Please check the source code documentation and other examples for further information.</p>
<p style="color:#CC0000;">TO IMPROVE AND EXPAND TCPDF I NEED YOUR SUPPORT, PLEASE <a href="http://sourceforge.net/donate/index.php?group_id=128076">MAKE A DONATION!</a></p>
<p style="font-size:34px;">Please check the source code documentation and other examples for further information.</p>*/

			$html = <<<EOD
<p style="font-size:34px;"> </p>
<table width="100%" cellspacing="0" border="1" cellpadding="55%" style="padding:200;">
<tr valign="bottom">
	<td class="header1" align="center" valign="middle"
		  width="75%"></td>
	<td class="header1" align="center" valign="middle"
		  width="20%"><img class="img-fluid" style="text-align:right;padding:200;" height="300" width="250" src="$photo" title=""></td>
	<td class="header1" align="center" valign="middle"
	width="5%"> </td>
</tr>
<tr valign="bottom">
	<td class="header1" align="center" valign="middle"
		  width="50%"></td>
	<td class="header1" align="center" valign="middle"
		  width="45%"><p style="font-size:26px;font-weight:bold;text-align:right;">$name</p></td>
	<td class="header1" align="center" valign="middle"
	width="5%"> </td>
</tr>
<tr valign="bottom">
	<td class="header1" align="center" valign="middle"
		  width="50%"></td>
	<td class="header1" align="center" valign="middle"
		  width="45%"><p style="font-size:26px;font-weight:bold;text-align:right;color:white;">$no_kta</p></td>
	<td class="header1" align="center" valign="middle"
	width="5%"> </td>
</tr>
<tr valign="bottom">
	<td class="header1" align="center" valign="middle"
		  width="70%"></td>
	<td class="header1" align="center" valign="middle"
		  width="28%"><img class="img-fluid" style="text-align:right;" height="80" width="250" src="$barcode" title=""></td>
	<td class="header1" align="center" valign="middle"
	width="2%"> </td>
</tr>
</table>
<p style="font-size:15px;"> </p>
<table width="100%" cellspacing="0" border="1" cellpadding="55%" style="padding:200;">
<tr valign="bottom">
	<td class="header1" align="center" valign="middle"
	width="53%"> </td>
	<td class="header1" align="center" valign="middle"
		  width="22%"><p style="font-size:26px;font-weight:bold;text-align:right;color:white;">$from_date</p></td>
	<td class="header1" align="center" valign="middle"
		  width="23%"><p style="font-size:26px;font-weight:bold;text-align:right;color:white;">$thru_date</p></td>
	
</tr>
</table>
EOD;

			// Print text using writeHTMLCell()
			$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
			$pdf->SetAlpha(0.3);

			$html2 = <<<EOD
<table width="100%" cellspacing="0" border="1" cellpadding="55%" style="padding:200;">
<tr valign="middle">
	<td class="header1" align="center" valign="middle"
	width="5%"> </td>
	
	<td class="header1" align="center" valign="middle"
		  width="60%"><p style="font-size:96px;font-weight:bold;text-align:right;color:white;">$thru_date</p></td>
	
</tr>
</table>
EOD;

			$pdf->writeHTMLCell(0, 0, 0, 80, $html2, 0, 1, 0, true, '', true);

			//Close and output PDF document
			$pdf->Output('page.pdf', 'I');
		}
	}

	function download_kta_qr_old()
	{
		if ($this->session->userdata('user_id') == '') {
			redirect('auth/login');
		}
		$id = $this->session->userdata('user_id');
		$members = $this->main_mod->msrwhere('members', array('person_id' => $id, 'status' => 1), 'id', 'desc')->result();
		$user_profiles = $this->main_mod->msrwhere('user_profiles', array('user_id' => $id), 'id', 'desc')->result();

		if (isset($members[0]->no_kta) && isset($user_profiles[0]->firstname)) {

			$name = trim(strtoupper($user_profiles[0]->firstname)) . " " . trim(strtoupper($user_profiles[0]->lastname));
			$photo = (($user_profiles[0]->photo != '') ? base_url() . 'assets/uploads/' . $user_profiles[0]->photo : "");
			$no_kta = str_pad($members[0]->code_wilayah, 4, '0', STR_PAD_LEFT) . '.' . str_pad($members[0]->code_bk_hkk, 2, '0', STR_PAD_LEFT) . '.' . str_pad($members[0]->no_kta, 6, '0', STR_PAD_LEFT);


			$phpdate = strtotime($members[0]->from_date);
			$from_date = date('m/Y', $phpdate);

			$phpdate = strtotime($members[0]->thru_date);
			$thru_date = date('m/Y', $phpdate);

			$id_name = preg_replace("/[^a-zA-Z0-9]+/", "", $name);
			$nim = str_pad($members[0]->no_kta, 6, '0', STR_PAD_LEFT) . '_' . $id_name . '_' . date('mY', $phpdate);

			//BARCODE
			/*$this->load->library('zend');		 
		//load yang ada di folder Zend
		$this->zend->load('Zend/Barcode');		 
		//generate barcodenya
		//$kode = 12345abc;
		$img = Zend_Barcode::draw('code128', 'image', array('text'=>$nim, 'drawText' => false,'barThinWidth'=>2,'backgroundColor'=>'#FE6601'), array());
		$code = $nim;
		$store_image = imagepng($img,FCPATH."./assets/uploads/barcode/{$code}.png");
		//return $code.'.png';
		$barcode = base_url().'assets/uploads/barcode/'.$code.'.png';*/
			//BARCODE
			//height="80" width="250"
			//QR

			$this->load->library('ciqrcode'); //pemanggilan library QR CODE

			$config['cacheable']    = true; //boolean, the default is true
			//$config['cachedir']     = './assets/uploads/qr/'; //string, the default is application/cache/
			//$config['errorlog']     = './assets/uploads/qr/'; //string, the default is application/logs/
			$config['imagedir']     = './assets/uploads/qr/'; //direktori penyimpanan qr code
			$config['quality']      = true; //boolean, the default is true
			$config['size']         = '1024'; //interger, the default is 1024
			$config['black']        = array(224, 255, 255); // array, default is array(255,255,255)
			$config['white']        = array(70, 130, 180); // array, default is array(0,0,0)
			$this->ciqrcode->initialize($config);

			$image_name = $nim . '.jpg'; //buat name dari qr code sesuai dengan nim

			$params['data'] = $nim; //data yang akan di jadikan QR CODE
			$params['level'] = 'H'; //H=High
			$params['size'] = 10;
			$params['savename'] = FCPATH . $config['imagedir'] . $image_name; //simpan image QR CODE ke folder assets/images/
			$this->ciqrcode->generate($params); // fungsi untuk generate QR CODE
			$barcode = $params['savename'];
			//



			//print_r($user_profiles[0]->photo);
			$this->load->library('Pdf');
			$pdf = new Pdf('L', 'mm', 'A4', true, 'UTF-8', false);

			$pdf->SetProtection(array('copy', 'modify', 'annot-forms', 'fill-forms', 'extract', 'assemble', 'owner'), "", "masterpassword123", 0, null);

			$pdf->setPrintHeader(false);
			$pdf->setPrintFooter(false);

			// set margins
			$pdf->SetMargins(0, 0, 0, true);

			// set auto page breaks false
			$pdf->SetAutoPageBreak(false, 0);

			// add a page
			$pdf->AddPage('L', 'A4');

			// Display image on full page
			//$pdf->Image('background.jpg', 0, 0, 210, 297, 'JPG', '', '', true, 200, '', false, false, 0, false, false, true);
			$img_file = FCPATH . './assets/images/background.jpg';
			$pdf->Image($img_file, 0, 0, 297, 210, '', '', '', false, 300, '', false, false, 0);

			/*
<h1>Welcome to <a href="http://www.tcpdf.org" style="text-decoration:none;background-color:#CC0000;color:black;">&nbsp;<span style="color:black;">TC</span><span style="color:white;">PDF</span>&nbsp;</a>!</h1>
<i>This is the first example of TCPDF library.</i>
<p>This text is printed using the <i>writeHTMLCell()</i> method but you can also use: <i>Multicell(), writeHTML(), Write(), Cell() and Text()</i>.</p>
<p>Please check the source code documentation and other examples for further information.</p>
<p style="color:#CC0000;">TO IMPROVE AND EXPAND TCPDF I NEED YOUR SUPPORT, PLEASE <a href="http://sourceforge.net/donate/index.php?group_id=128076">MAKE A DONATION!</a></p>
<p style="font-size:34px;">Please check the source code documentation and other examples for further information.</p>*/

			$html = <<<EOD
<p style="font-size:34px;"> </p>
<table width="100%" cellspacing="0" border="1" cellpadding="55%" style="padding:200;">
<tr valign="bottom">
	<td class="header1" align="center" valign="middle"
		  width="75%"></td>
	<td class="header1" align="center" valign="middle"
		  width="20%"><img class="img-fluid" style="text-align:right;padding:200;" height="300" width="250" src="$photo" title=""></td>
	<td class="header1" align="center" valign="middle"
	width="5%"> </td>
</tr>
<tr valign="bottom">
	<td class="header1" align="center" valign="middle"
		  width="10%"></td>
	<td class="header1" align="center" valign="middle"
		  width="85%"><p style="font-size:24px;font-weight:bold;text-align:right;">$name</p></td>
	<td class="header1" align="center" valign="middle"
	width="5%"> </td>
</tr>
<tr valign="bottom">
	<td class="header1" align="center" valign="middle"
		  width="50%"></td>
	<td class="header1" align="center" valign="middle"
		  width="45%"><p style="font-size:26px;font-weight:bold;text-align:right;color:white;">$no_kta</p></td>
	<td class="header1" align="center" valign="middle"
	width="5%"> </td>
</tr>
<tr valign="bottom">
	<td class="header1" align="center" valign="middle"
		  width="70%"></td>
	<td class="header1" align="center" valign="middle"
		  width="28%"><img class="img-fluid" style="text-align:right;" height="130" src="$barcode" title=""></td>
	<td class="header1" align="center" valign="middle"
	width="2%"> </td>
</tr>
</table>

<table width="100%" cellspacing="0" border="1" cellpadding="55%" style="padding:200;">
<tr valign="bottom">
	<td class="header1" align="center" valign="middle"
	width="53%"> </td>
	<td class="header1" align="center" valign="middle"
		  width="22%"><p style="font-size:26px;font-weight:bold;text-align:right;color:white;">$from_date</p></td>
	<td class="header1" align="center" valign="middle"
		  width="23%"><p style="font-size:26px;font-weight:bold;text-align:right;color:white;">$thru_date</p></td>
	
</tr>
</table>
EOD;

			// Print text using writeHTMLCell()
			$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
			$pdf->SetAlpha(0.3);

			$html2 = <<<EOD
<table width="100%" cellspacing="0" border="1" cellpadding="55%" style="padding:200;">
<tr valign="middle">
	<td class="header1" align="center" valign="middle"
	width="5%"> </td>
	
	<td class="header1" align="center" valign="middle"
		  width="60%"><p style="font-size:96px;font-weight:bold;text-align:right;color:white;">$thru_date</p></td>
	
</tr>
</table>
EOD;

			$pdf->writeHTMLCell(0, 0, 0, 80, $html2, 0, 1, 0, true, '', true);

			//Close and output PDF document
			$pdf->Output($nim . '.pdf', 'D');
		}
	}

	function download_kta_qr()
	{
		if ($this->session->userdata('user_id') == '') {
			redirect('auth/login');
		}
		$id = $this->session->userdata('user_id');
		$members = $this->main_mod->msrwhere('members', array('person_id' => $id, 'status' => 1), 'id', 'desc')->result();
		$user_profiles = $this->main_mod->msrwhere('user_profiles', array('user_id' => $id), 'id', 'desc')->result();

		if (isset($members[0]->no_kta) && isset($user_profiles[0]->firstname)) {

			$name = trim(strtoupper($user_profiles[0]->firstname)) . " " . trim(strtoupper($user_profiles[0]->lastname));
			$photo = (($user_profiles[0]->photo != '') ? base_url() . 'assets/uploads/' . $user_profiles[0]->photo : "");
			$no_kta = str_pad($members[0]->code_wilayah, 4, '0', STR_PAD_LEFT) . '.' . str_pad($members[0]->code_bk_hkk, 2, '0', STR_PAD_LEFT) . '.' . str_pad($members[0]->no_kta, 6, '0', STR_PAD_LEFT);


			$phpdate = strtotime($members[0]->from_date);
			$from_date = date('m/Y', $phpdate);

			$phpdate = strtotime($members[0]->thru_date);
			$thru_date = date('m/Y', $phpdate);

			$id_name = preg_replace("/[^a-zA-Z0-9]+/", "", $name);
			$nim = str_pad($members[0]->no_kta, 6, '0', STR_PAD_LEFT) . '_' . $id_name . '_' . date('mY', $phpdate);

			//BARCODE
			/*$this->load->library('zend');		 
		//load yang ada di folder Zend
		$this->zend->load('Zend/Barcode');		 
		//generate barcodenya
		//$kode = 12345abc;
		$img = Zend_Barcode::draw('code128', 'image', array('text'=>$nim, 'drawText' => false,'barThinWidth'=>2,'backgroundColor'=>'#FE6601'), array());
		$code = $nim;
		$store_image = imagepng($img,FCPATH."./assets/uploads/barcode/{$code}.png");
		//return $code.'.png';
		$barcode = base_url().'assets/uploads/barcode/'.$code.'.png';*/
			//BARCODE
			//height="80" width="250"
			//QR

			$this->load->library('ciqrcode'); //pemanggilan library QR CODE

			$config['cacheable']    = true; //boolean, the default is true
			//$config['cachedir']     = './assets/uploads/qr/'; //string, the default is application/cache/
			//$config['errorlog']     = './assets/uploads/qr/'; //string, the default is application/logs/
			$config['imagedir']     = './assets/uploads/qr/'; //direktori penyimpanan qr code
			$config['quality']      = true; //boolean, the default is true
			$config['size']         = '1024'; //interger, the default is 1024
			$config['black']        = array(224, 255, 255); // array, default is array(255,255,255)
			$config['white']        = array(70, 130, 180); // array, default is array(0,0,0)
			$this->ciqrcode->initialize($config);

			$image_name = $nim . '.jpg'; //buat name dari qr code sesuai dengan nim

			$params['data'] = $nim; //data yang akan di jadikan QR CODE
			$params['level'] = 'H'; //H=High
			$params['size'] = 10;
			$params['savename'] = FCPATH . $config['imagedir'] . $image_name; //simpan image QR CODE ke folder assets/images/
			$this->ciqrcode->generate($params); // fungsi untuk generate QR CODE
			$barcode = $params['savename'];
			//



			//print_r($user_profiles[0]->photo);
			$this->load->library('Pdf');
			$pdf = new Pdf('L', 'mm', 'A4', true, 'UTF-8', false);

			$pdf->SetProtection(array('copy', 'modify', 'annot-forms', 'fill-forms', 'extract', 'assemble', 'owner'), "", "masterpassword123", 0, null);

			$pdf->setPrintHeader(false);
			$pdf->setPrintFooter(false);

			// set margins
			$pdf->SetMargins(0, 0, 0, true);

			// set auto page breaks false
			$pdf->SetAutoPageBreak(false, 0);

			// add a page
			$pdf->AddPage('L', 'A4');

			// Display image on full page
			//$pdf->Image('background.jpg', 0, 0, 210, 297, 'JPG', '', '', true, 200, '', false, false, 0, false, false, true);
			$img_file = FCPATH . './assets/images/background.jpg';
			$pdf->Image($img_file, 0, 0, 297, 210, '', '', '', false, 300, '', false, false, 0);

			if (strpos(strtolower($photo), '.pdf') !== false) {
				$im = new imagick($photo);
				$im->setImageFormat('jpg');
				$file = base_url() . 'assets/uploads/' . str_replace("pdf", "jpg", $user_profiles[0]->photo);
				$im->writeImage(FCPATH . './assets/uploads/' . (str_replace("pdf", "jpg", $user_profiles[0]->photo)));
				//header('Content-Type: image/jpeg');
				//$imgtemp = 'data:image/jpg;base64,'.base64_encode($im->getImageBlob());
				$photo = '<img class="img-fluid" style="text-align:right;padding:200;" height="300" width="250" src="' . $file . '" title="">';
				//echo $img;
			}

			/*
<h1>Welcome to <a href="http://www.tcpdf.org" style="text-decoration:none;background-color:#CC0000;color:black;">&nbsp;<span style="color:black;">TC</span><span style="color:white;">PDF</span>&nbsp;</a>!</h1>
<i>This is the first example of TCPDF library.</i>
<p>This text is printed using the <i>writeHTMLCell()</i> method but you can also use: <i>Multicell(), writeHTML(), Write(), Cell() and Text()</i>.</p>
<p>Please check the source code documentation and other examples for further information.</p>
<p style="color:#CC0000;">TO IMPROVE AND EXPAND TCPDF I NEED YOUR SUPPORT, PLEASE <a href="http://sourceforge.net/donate/index.php?group_id=128076">MAKE A DONATION!</a></p>
<p style="font-size:34px;">Please check the source code documentation and other examples for further information.</p>*/

			$html = <<<EOD
<p style="font-size:34px;"> </p>
<table width="100%" cellspacing="0" border="1" cellpadding="55%" style="padding:200;">
<tr valign="bottom">
	<td class="header1" align="center" valign="middle"
		  width="75%"></td>
	<td class="header1" align="center" valign="middle"
		  width="20%"><img class="img-fluid" style="text-align:right;padding:200;" height="300" width="250" src="$photo" title=""></td>
	<td class="header1" align="center" valign="middle"
	width="5%"> </td>
</tr>
<tr valign="bottom">
	<td class="header1" align="center" valign="middle"
		  width="10%"></td>
	<td class="header1" align="center" valign="middle"
		  width="85%"><p style="font-size:24px;font-weight:bold;text-align:right;">$name</p></td>
	<td class="header1" align="center" valign="middle"
	width="5%"> </td>
</tr>
<tr valign="bottom">
	<td class="header1" align="center" valign="middle"
		  width="50%"></td>
	<td class="header1" align="center" valign="middle"
		  width="45%"><p style="font-size:26px;font-weight:bold;text-align:right;color:white;">$no_kta</p></td>
	<td class="header1" align="center" valign="middle"
	width="5%"> </td>
</tr>
<tr valign="bottom">
	<td class="header1" align="center" valign="middle"
		  width="70%"></td>
	<td class="header1" align="center" valign="middle"
		  width="28%"><img class="img-fluid" style="text-align:right;" height="130" src="$barcode" title=""></td>
	<td class="header1" align="center" valign="middle"
	width="2%"> </td>
</tr>
</table>

<table width="100%" cellspacing="0" border="1" cellpadding="55%" style="padding:200;">
<tr valign="bottom">
	<td class="header1" align="center" valign="middle"
	width="53%"> </td>
	<td class="header1" align="center" valign="middle"
		  width="22%"><p style="font-size:26px;font-weight:bold;text-align:right;color:white;">$from_date</p></td>
	<td class="header1" align="center" valign="middle"
		  width="23%"><p style="font-size:26px;font-weight:bold;text-align:right;color:white;">$thru_date</p></td>
	
</tr>
</table>
EOD;

			// Print text using writeHTMLCell()
			$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
			$pdf->SetAlpha(0.3);

			$html2 = <<<EOD
<table width="100%" cellspacing="0" border="1" cellpadding="55%" style="padding:200;">
<tr valign="middle">
	<td class="header1" align="center" valign="middle"
	width="5%"> </td>
	
	<td class="header1" align="center" valign="middle"
		  width="60%"><p style="font-size:96px;font-weight:bold;text-align:right;color:white;">$thru_date</p></td>
	
</tr>
</table>
EOD;

			$pdf->writeHTMLCell(0, 0, 0, 80, $html2, 0, 1, 0, true, '', true);

			//Close and output PDF document
			$pdf->Output($nim . '.pdf', 'D');
		}
	}

	function download_kta_old()
	{
		if ($this->session->userdata('user_id') == '') {
			redirect('auth/login');
		}
		$id = $this->session->userdata('user_id');
		$members = $this->main_mod->msrwhere('members', array('person_id' => $id, 'status' => 1), 'id', 'desc')->result();
		$user_profiles = $this->main_mod->msrwhere('user_profiles', array('user_id' => $id), 'id', 'desc')->result();

		if (isset($members[0]->no_kta) && isset($user_profiles[0]->firstname)) {

			$name = trim(strtoupper($user_profiles[0]->firstname)) . " " . trim(strtoupper($user_profiles[0]->lastname));
			$photo = (($user_profiles[0]->photo != '') ? base_url() . 'assets/uploads/' . $user_profiles[0]->photo : "");
			$photo_cir = $user_profiles[0]->photo;
			$no_kta = str_pad($members[0]->code_wilayah, 4, '0', STR_PAD_LEFT) . '.' . str_pad($members[0]->code_bk_hkk, 2, '0', STR_PAD_LEFT) . '.' . str_pad($members[0]->no_kta, 6, '0', STR_PAD_LEFT);


			$phpdate = strtotime($members[0]->from_date);
			$from_date = date('m/y', $phpdate);

			$phpdate = strtotime($members[0]->thru_date);
			$thru_date = date('m/y', $phpdate);

			$id_name = preg_replace("/[^a-zA-Z0-9]+/", "", $name);
			$nim = str_pad($members[0]->no_kta, 6, '0', STR_PAD_LEFT) . '_' . $id_name . '_' . date('mY', $phpdate);

			//BARCODE
			/*$this->load->library('zend');		 
		//load yang ada di folder Zend
		$this->zend->load('Zend/Barcode');		 
		//generate barcodenya
		//$kode = 12345abc;
		$img = Zend_Barcode::draw('code128', 'image', array('text'=>$nim, 'drawText' => false,'barThinWidth'=>2,'backgroundColor'=>'#FE6601'), array());
		$code = $nim;
		$store_image = imagepng($img,FCPATH."./assets/uploads/barcode/{$code}.png");
		//return $code.'.png';
		$barcode = base_url().'assets/uploads/barcode/'.$code.'.png';*/
			//BARCODE
			//height="80" width="250"
			//QR

			$this->load->library('ciqrcode'); //pemanggilan library QR CODE

			$config['cacheable']    = true; //boolean, the default is true
			//$config['cachedir']     = './assets/uploads/qr/'; //string, the default is application/cache/
			//$config['errorlog']     = './assets/uploads/qr/'; //string, the default is application/logs/
			$config['imagedir']     = './assets/uploads/qr/'; //direktori penyimpanan qr code
			$config['quality']      = true; //boolean, the default is true
			$config['size']         = '1024'; //interger, the default is 1024
			$config['black']        = array(224, 255, 255); // array, default is array(255,255,255)
			$config['white']        = array(70, 130, 180); // array, default is array(0,0,0)
			$this->ciqrcode->initialize($config);

			$image_name = $nim . '.jpg'; //buat name dari qr code sesuai dengan nim

			$params['data'] = $nim; //data yang akan di jadikan QR CODE
			$params['level'] = 'H'; //H=High
			$params['size'] = 10;
			$params['savename'] = FCPATH . $config['imagedir'] . $image_name; //simpan image QR CODE ke folder assets/images/
			$this->ciqrcode->generate($params); // fungsi untuk generate QR CODE
			$barcode = $params['savename'];
			//



			//print_r($user_profiles[0]->photo);
			$this->load->library('Pdf');
			$pdf = new Pdf('L', 'mm', 'A4', true, 'UTF-8', false);

			$pdf->SetProtection(array('copy', 'modify', 'annot-forms', 'fill-forms', 'extract', 'assemble', 'owner'), "", "masterpassword123", 0, null);

			$pdf->setPrintHeader(false);
			$pdf->setPrintFooter(false);

			// set margins
			$pdf->SetMargins(0, 0, 0, true);

			// set auto page breaks false
			$pdf->SetAutoPageBreak(false, 0);

			// add a page
			$pdf->AddPage('L', 'A4');

			// Display image on full page
			//$pdf->Image('background.jpg', 0, 0, 210, 297, 'JPG', '', '', true, 200, '', false, false, 0, false, false, true);
			$img_file = FCPATH . './assets/images/background_new.jpg';
			$pdf->Image($img_file, 0, 0, 297, 210, '', '', '', false, 300, '', false, false, 0);

			if (strpos(strtolower($photo), '.pdf') !== false) {
				$im = new imagick($photo);
				$im->setImageFormat('jpg');
				$file = base_url() . 'assets/uploads/' . str_replace("pdf", "jpg", $user_profiles[0]->photo);
				$im->writeImage(FCPATH . './assets/uploads/' . (str_replace("pdf", "jpg", $user_profiles[0]->photo)));
				$photo_cir = (str_replace("pdf", "jpg", $user_profiles[0]->photo));
				//header('Content-Type: image/jpeg');
				//$imgtemp = 'data:image/jpg;base64,'.base64_encode($im->getImageBlob());
				$im->destroy();
				$photo = '<img class="img-fluid" style="text-align:right;padding:200;" height="300" width="250" src="' . $file . '" title="">';
				//echo $img;
			}

			//CIRCULAR IMAGE	
			//$outfile = FCPATH.'./assets/uploads/'.(str_replace(".","_.",$photo_cir));
			//$photo = base_url().'assets/uploads/'.str_replace(".","_.",$photo_cir);
			/*
		$circle = new Imagick();
		$circle->newImage(185.5, 185.5, 'none');
		$circle->setimageformat('png');
		//$circle->setimagematte(true);
		$draw = new ImagickDraw();
		$draw->setfillcolor('#ffffff');
		$draw->circle(185.5/2, 185.5/2, 185.5/2, 185.5);
		$circle->drawimage($draw);

		$imagick = new Imagick();
		$imagick->readImage(FCPATH.'./assets/uploads/'.$photo_cir);
		$imagick->setImageFormat( "png" );
		//$imagick->setimagematte(true);
		$imagick->cropimage(185.5, 185.5, 253, 0);
		$imagick->compositeimage($circle, Imagick::COMPOSITE_DSTIN, 0, 0);
		$imagick->writeImage($outfile);
		$imagick->destroy();*/

			//exec("convert ".FCPATH.'./assets/uploads/'.$photo_cir.' ( -clone 0 -fill black -colorize 100 -fill white -draw "translate %[fx:w/2],%[fx:h/2] circle 0,0 0,%[fx:min(w/2,h/2)]" ) -alpha off -compose copy_opacity -composite -trim '.$outfile);		


			//exec('convert -size 200x200 xc:none -fill '.FCPATH.'./assets/uploads/'.$photo_cir.' -draw "circle 100,100 100,1" '.$outfile);

			//exec('convert '.FCPATH.'./assets/uploads/'.$photo_cir.'  -resize x800 -resize "800x<"   -resize 50% -gravity center  -crop 400x400+0+0 +repage ( +clone -threshold -1 -negate -fill white -draw "circle 200,200 200,0" ) -compose DstIn -composite -auto-orient '.$outfile);

			/*
<h1>Welcome to <a href="http://www.tcpdf.org" style="text-decoration:none;background-color:#CC0000;color:black;">&nbsp;<span style="color:black;">TC</span><span style="color:white;">PDF</span>&nbsp;</a>!</h1>
<i>This is the first example of TCPDF library.</i>
<p>This text is printed using the <i>writeHTMLCell()</i> method but you can also use: <i>Multicell(), writeHTML(), Write(), Cell() and Text()</i>.</p>
<p>Please check the source code documentation and other examples for further information.</p>
<p style="color:#CC0000;">TO IMPROVE AND EXPAND TCPDF I NEED YOUR SUPPORT, PLEASE <a href="http://sourceforge.net/donate/index.php?group_id=128076">MAKE A DONATION!</a></p>
<p style="font-size:34px;">Please check the source code documentation and other examples for further information.</p>*/

			$fontname = TCPDF_FONTS::addTTFfont(FCPATH . './assets/fonts/CREDC___.ttf', 'credc', '', 96);
			$fontname2 = TCPDF_FONTS::addTTFfont(FCPATH . './assets/fonts/Montserrat-Bold.ttf', 'mont', '', 96);
			$pdf->SetFont($fontname2, '', 14, '', false);

			$html = <<<EOD
<p style="font-size:34px;"> </p>
<table width="100%" cellspacing="0" border="1" cellpadding="55%" style="padding:200;">
<tr valign="bottom">
	<td class="header1" align="center" valign="middle"
		  width="75%"></td>
	<td class="header1" align="center" valign="middle"
		  width="20%"><img class="img-fluid" style="text-align:right;padding:200;" width="250" height="300" src="$photo" title=""></td>
	<td class="header1" align="center" valign="middle"
	width="5%"> </td>
</tr>
</table>
<p style="font-size:35px;"> </p>
<table width="100%" cellspacing="0" border="1" cellpadding="55%" style="padding:200;">
<tr valign="bottom">
	<td class="header1" align="center" valign="middle"
	width="8%"> </td>
	<td class="header1" align="center" valign="middle"
		  width="85%"><p style="font-size:24px;font-weight:bold;text-align:left;color:white;font-family:$fontname2">$name</p></td>
	<td class="header1" align="center" valign="middle"
		  width="7%"> </td>
</tr>
</table>
<p style="font-size:3px;"> </p>
<table width="100%" cellspacing="0" border="1" cellpadding="55%" style="padding:200;">
EOD;
			$pdf->SetFont($fontname, '', 14, '', false);
			$html .= <<<EOD
<tr valign="bottom">
	<td class="header1" align="center" valign="middle"
	width="8%"> </td>
	<td class="header1" align="center" valign="middle"
		  width="85%"><p style="font-size:46px;font-weight:bold;text-align:left;color:#ff7700;">$no_kta</p></td>
	<td class="header1" align="center" valign="middle"
		  width="7%"></td>
</tr>
</table>

<table width="100%" cellspacing="0" border="1" cellpadding="55%" style="padding:200;">
<tr valign="bottom">
	<td class="header1" align="center" valign="middle"
	width="22%"> </td>
	<td class="header1" align="center" valign="middle"
		  width="30%"><p style="font-size:26px;font-weight:bold;text-align:left;color:white;">$from_date</p></td>
	<td class="header1" align="center" valign="middle"
		  width="23%"><p style="font-size:26px;font-weight:bold;text-align:left;color:white;">$thru_date</p></td>
	
	
</tr>
</table>
EOD;

			// Print text using writeHTMLCell()
			$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
			//$pdf->SetAlpha(0.8);

			$html2 = <<<EOD
<p style="font-size:37px;"> </p>
<table width="100%" cellspacing="0" border="1" cellpadding="55%" style="padding:200;">
<tr valign="middle">
	<td class="header1" align="center" valign="middle"
	width="70%"> </td>
	
	<td class="header1" align="center" valign="middle"
		  width="30%"><img class="img-fluid" style="text-align:right;" height="130" src="$barcode" title=""></td>
	
</tr>
</table>
EOD;

			$pdf->writeHTMLCell(0, 0, 0, 120, $html2, 0, 1, 0, true, '', true);

			//Close and output PDF document
			$pdf->Output($nim . '.pdf', 'D');
		}
	}

	function download_kta_2()
	{
		if ($this->session->userdata('user_id') == '') {
			redirect('auth/login');
		}
		$id = $this->session->userdata('user_id');
		$members = $this->main_mod->msrwhere('members', array('person_id' => $id, 'status' => 1), 'id', 'desc')->result();
		$user_profiles = $this->main_mod->msrwhere('user_profiles', array('user_id' => $id), 'id', 'desc')->result();
		$users = $this->main_mod->msrwhere('users', array('id' => $id), 'id', 'desc')->row();

		if (isset($members[0]->no_kta) && isset($user_profiles[0]->firstname)) {

			//if(strtotime( $members[0]->thru_date ) >= strtotime(date('Y-m-d')) && $users->username!=''){
			if ((strtotime($members[0]->thru_date) >= strtotime(date('Y-m-d')) || $members[0]->thru_date == '0000-00-00') && $users->username != '') {

				$name = trim(strtoupper($user_profiles[0]->firstname)) . " " . trim(strtoupper($user_profiles[0]->lastname));
				$photo = (($user_profiles[0]->photo != '') ? base_url() . 'assets/uploads/' . rawurlencode($user_profiles[0]->photo) : "");
				$photo_cir = $user_profiles[0]->photo;
				$no_kta = str_pad($members[0]->code_wilayah, 4, '0', STR_PAD_LEFT) . '.' . str_pad($members[0]->code_bk_hkk, 2, '0', STR_PAD_LEFT) . '.' . str_pad($members[0]->no_kta, 6, '0', STR_PAD_LEFT);


				$phpdate = strtotime($members[0]->from_date);
				$from_date = date('m/y', $phpdate);

				$phpdate = strtotime($members[0]->thru_date);
				$thru_date = date('m/y', $phpdate);
				if ($thru_date == '01/70') $thru_date = "01/30";
				else if ($members[0]->thru_date == '0000-00-00') $thru_date = "-";

				$id_name = preg_replace("/[^a-zA-Z0-9]+/", "", $name);
				$nim = str_pad($members[0]->no_kta, 6, '0', STR_PAD_LEFT) . '_' . $id_name . '_' . date('mY', $phpdate);

				$jenis_anggota = $members[0]->jenis_anggota;
				log_message('debug', '[SIMPONI] ' . __CLASS__ . '@' . __FUNCTION__ . " - no_kta: ${no_kta}, jenis_anggota: ${jenis_anggota} from_date: ${from_date}, thru_date: ${thru_date}, photo: ${photo}");



				//BARCODE
				/*$this->load->library('zend');		 
		//load yang ada di folder Zend
		$this->zend->load('Zend/Barcode');		 
		//generate barcodenya
		//$kode = 12345abc;
		$img = Zend_Barcode::draw('code128', 'image', array('text'=>$nim, 'drawText' => false,'barThinWidth'=>2,'backgroundColor'=>'#FE6601'), array());
		$code = $nim;
		$store_image = imagepng($img,FCPATH."./assets/uploads/barcode/{$code}.png");
		//return $code.'.png';
		$barcode = base_url().'assets/uploads/barcode/'.$code.'.png';*/
				//BARCODE
				//height="80" width="250"
				//QR

				$this->load->library('ciqrcode'); //pemanggilan library QR CODE

				$config['cacheable']    = true; //boolean, the default is true
				//$config['cachedir']     = './assets/uploads/qr/'; //string, the default is application/cache/
				//$config['errorlog']     = './assets/uploads/qr/'; //string, the default is application/logs/
				$config['imagedir']     = './assets/uploads/qr/'; //direktori penyimpanan qr code
				$config['quality']      = true; //boolean, the default is true
				$config['size']         = '1024'; //interger, the default is 1024
				$config['black']        = array(224, 255, 255); // array, default is array(255,255,255)
				$config['white']        = array(70, 130, 180); // array, default is array(0,0,0)
				$this->ciqrcode->initialize($config);

				$image_name = $nim . '.jpg'; //buat name dari qr code sesuai dengan nim

				$params['data'] = $nim; //data yang akan di jadikan QR CODE
				$params['level'] = 'H'; //H=High
				$params['size'] = 10;
				$params['savename'] = FCPATH . $config['imagedir'] . $image_name; //simpan image QR CODE ke folder assets/images/
				$this->ciqrcode->generate($params); // fungsi untuk generate QR CODE
				$barcode = $params['savename'];
				//



				//print_r($user_profiles[0]->photo);

				$this->load->library('Pdf');

				$your_width = 354;
				$your_height = 216;
				$custom_layout = array($your_width, $your_height);

				$pdf = new Pdf('L', 'mm', $custom_layout, true, 'UTF-8', false);

				$pdf->SetProtection(array('copy', 'modify', 'annot-forms', 'fill-forms', 'extract', 'assemble', 'owner'), "", "masterpassword123", 0, null);

				$pdf->setPrintHeader(false);
				$pdf->setPrintFooter(false);

				// set margins
				$pdf->SetMargins(0, 0, 0, true);

				// set auto page breaks false
				$pdf->SetAutoPageBreak(false, 0);

				// add a page


				$pdf->AddPage('L', $custom_layout);

				// Display image on full page
				//$pdf->Image('background.jpg', 0, 0, 210, 297, 'JPG', '', '', true, 200, '', false, false, 0, false, false, true);
				$img_file = FCPATH . './assets/images/background_new.jpg';
				$pdf->Image($img_file, 0, 0, 354, 216, '', '', '', false, 300, '', false, false, 0);

				if (strpos(strtolower($photo), '.pdf') !== false) {
					$im = new imagick($photo);
					$im->setImageFormat('jpg');
					$file = base_url() . 'assets/uploads/' . str_replace("pdf", "jpg", rawurlencode($user_profiles[0]->photo));
					$im->writeImage(FCPATH . './assets/uploads/' . (str_replace("pdf", "jpg", $user_profiles[0]->photo)));
					$photo_cir = (str_replace("pdf", "jpg", $user_profiles[0]->photo));
					//header('Content-Type: image/jpeg');
					//$imgtemp = 'data:image/jpg;base64,'.base64_encode($im->getImageBlob());
					$im->destroy();
					$photo = '<img class="img-fluid" style="text-align:right;padding:200;" height="300" width="250" src="' . $file . '" title="">';
					//echo $img;
				}

				if ($photo_cir != '' && $photo_cir != ' ') {
					/*list($width, $height) = getimagesize(FCPATH.'./assets/uploads/'.$photo_cir);
			if ($width > $height) {
				$filename=FCPATH.'./assets/uploads/'.$photo_cir;
				// Load the image
				$source = imagecreatefromjpeg($filename);
				// Rotate
				$rotate = imagerotate($source, 90, 0);
				//and save it on your server...
				imagejpeg($rotate, $filename);			
				//echo 'Landscape';
			} else {
				//echo 'Portrait';// Portrait or Square
			}*/

					if (strpos(strtolower($photo_cir), '.jpg') !== false) {
						$filename = FCPATH . './assets/uploads/' . $photo_cir;
						/*$image = imagecreatefromstring(file_get_contents(FCPATH.'./assets/uploads/'.$photo_cir));//imagecreatefromjpeg($filename);
				$exif = exif_read_data(FCPATH.'./assets/uploads/'.$photo_cir);
				if(!empty($exif['Orientation'])) {
					switch($exif['Orientation']) {
						case 8:
							$image = imagerotate($image,90,0);
							break;
						case 3:
							$image = imagerotate($image,180,0);
							break;
						case 6:
							$image = imagerotate($image,-90,0);
							break;
					}
				}
				imagejpeg($image, $filename);*/
					}
				} else {
					$photo = '<img class="img-fluid" style="text-align:right;padding:200;" height="300" width="250" src="' . base_url() . 'assets/images/nophoto.jpg' . '" title="">';
				}

				//CIRCULAR IMAGE	
				//$outfile = FCPATH.'./assets/uploads/'.(str_replace(".","_.",$photo_cir));
				//$photo = base_url().'assets/uploads/'.str_replace(".","_.",$photo_cir);
				/*
		$circle = new Imagick();
		$circle->newImage(185.5, 185.5, 'none');
		$circle->setimageformat('png');
		//$circle->setimagematte(true);
		$draw = new ImagickDraw();
		$draw->setfillcolor('#ffffff');
		$draw->circle(185.5/2, 185.5/2, 185.5/2, 185.5);
		$circle->drawimage($draw);

		$imagick = new Imagick();
		$imagick->readImage(FCPATH.'./assets/uploads/'.$photo_cir);
		$imagick->setImageFormat( "png" );
		//$imagick->setimagematte(true);
		$imagick->cropimage(185.5, 185.5, 253, 0);
		$imagick->compositeimage($circle, Imagick::COMPOSITE_DSTIN, 0, 0);
		$imagick->writeImage($outfile);
		$imagick->destroy();*/

				//exec("convert ".FCPATH.'./assets/uploads/'.$photo_cir.' ( -clone 0 -fill black -colorize 100 -fill white -draw "translate %[fx:w/2],%[fx:h/2] circle 0,0 0,%[fx:min(w/2,h/2)]" ) -alpha off -compose copy_opacity -composite -trim '.$outfile);		


				//exec('convert -size 200x200 xc:none -fill '.FCPATH.'./assets/uploads/'.$photo_cir.' -draw "circle 100,100 100,1" '.$outfile);

				//exec('convert '.FCPATH.'./assets/uploads/'.$photo_cir.'  -resize x800 -resize "800x<"   -resize 50% -gravity center  -crop 400x400+0+0 +repage ( +clone -threshold -1 -negate -fill white -draw "circle 200,200 200,0" ) -compose DstIn -composite -auto-orient '.$outfile);

				/*
<h1>Welcome to <a href="http://www.tcpdf.org" style="text-decoration:none;background-color:#CC0000;color:black;">&nbsp;<span style="color:black;">TC</span><span style="color:white;">PDF</span>&nbsp;</a>!</h1>
<i>This is the first example of TCPDF library.</i>
<p>This text is printed using the <i>writeHTMLCell()</i> method but you can also use: <i>Multicell(), writeHTML(), Write(), Cell() and Text()</i>.</p>
<p>Please check the source code documentation and other examples for further information.</p>
<p style="color:#CC0000;">TO IMPROVE AND EXPAND TCPDF I NEED YOUR SUPPORT, PLEASE <a href="http://sourceforge.net/donate/index.php?group_id=128076">MAKE A DONATION!</a></p>
<p style="font-size:34px;">Please check the source code documentation and other examples for further information.</p>*/

				$fontname = TCPDF_FONTS::addTTFfont(FCPATH . './assets/fonts/CREDC___.ttf', 'credc', '', 96);
				$fontname2 = TCPDF_FONTS::addTTFfont(FCPATH . './assets/fonts/Montserrat-Bold.ttf', 'mont', '', 96);
				$pdf->SetFont($fontname2, '', 14, '', false);

				$tmp = '3px';
				$len = strlen($name);

				if ($len <= 60) $tmp = '11px';

				$temp_jenis = '';
				log_message('debug', '[SIMPONI] ' . __CLASS__ . '@' . __FUNCTION__ . " Crate KTA card header based on jenis anggota");

				if ($members[0]->jenis_anggota == '01' || $members[0]->jenis_anggota == '03' || $members[0]->jenis_anggota == '04') {
					//$t = ($members[0]->jenis_anggota=='01')?'ANGGOTA MUDA':($members[0]->jenis_anggota=='04')?'ANGGOTA KEHORMATAN':'ANGGOTA LUAR BIASA';
					$t = 'ANGGOTA MUDA';
					if ($members[0]->jenis_anggota == 4)
						$t = 'ANGGOTA KEHORMATAN';
					else if ($members[0]->jenis_anggota == 3)
						$t = 'ANGGOTA LUAR BIASA';

					$temp_jenis = '<p style="font-size:20px;"> </p>
<table width="100%" cellspacing="0" border="1" cellpadding="55%" style="padding:200;">
<tr valign="bottom">
	<td class="header1" align="center" valign="middle"
	width="8%"> </td>
	<td class="header1" align="center" valign="middle"
		  width="85%"><p style="font-size:24px;font-weight:bold;text-align:left;color:white;font-family:' . $fontname2 . '">' . $name . '</p></td>
	<td class="header1" align="center" valign="middle"
		  width="7%"> </td>
</tr>
</table>
<p style="font-size:5px;"> </p>
<table width="100%" cellspacing="0" border="1" cellpadding="55%" style="padding:200;">
<tr valign="bottom">
	<td class="header1" align="center" valign="middle"
	width="8%"> </td>
	<td class="header1" align="center" valign="middle"
		  width="85%"><p style="font-size:24px;font-weight:bold;text-align:left;color:white;font-family:' . $fontname2 . '">' . $t . '</p></td>
	<td class="header1" align="center" valign="middle"
		  width="7%"> </td>
</tr>
</table>';
					log_message('debug', '[SIMPONI] ' . __CLASS__ . '@' . __FUNCTION__ . " - no_kta: ${no_kta}, jenis_anggota: ${jenis_anggota} , temp_jenis: \n" . $temp_jenis);
				} else {
					$temp_jenis = '<p style="font-size:35px;"> </p>
<table width="100%" cellspacing="0" border="1" cellpadding="55%" style="padding:200;">
<tr valign="bottom">
	<td class="header1" align="center" valign="middle"
	width="8%"> </td>
	<td class="header1" align="center" valign="middle"
		  width="85%"><p style="font-size:24px;font-weight:bold;text-align:left;color:white;font-family:' . $fontname2 . '">' . $name . '</p></td>
	<td class="header1" align="center" valign="middle"
		  width="7%"> </td>
</tr>
</table>';
					log_message('debug', '[SIMPONI] ' . __CLASS__ . '@' . __FUNCTION__ . " - no_kta: ${no_kta}, jenis_anggota: ${jenis_anggota} , temp_jenis: \n" . $temp_jenis);
				}


				$html = <<<EOD
<p style="font-size:20px;"> </p>
<table width="100%" cellspacing="0" border="1" cellpadding="55%" style="padding:200;">
<tr valign="bottom">
	<td class="header1" align="center" valign="middle"
		  width="72%"></td>
	<td class="header1" align="center" valign="middle"
		  width="19%"><img class="img-fluid" style="text-align:right;padding:200;" width="350" height="400" src="$photo" title=""></td>
	<td class="header1" align="center" valign="middle"
	width="9%"> </td>
</tr>
</table>$temp_jenis<p style="font-size:$tmp;"> </p>
<table width="100%" cellspacing="0" border="1" cellpadding="55%" style="padding:200;">
EOD;
				$pdf->SetFont($fontname, '', 14, '', false);
				$html .= <<<EOD
<tr valign="bottom">
	<td class="header1" align="center" valign="middle"
	width="8%"> </td>
	<td class="header1" align="center" valign="middle"
		  width="85%"><p style="font-size:46px;font-weight:bold;text-align:left;color:#ff7700;">$no_kta</p></td>
	<td class="header1" align="center" valign="middle"
		  width="7%"></td>
</tr>
</table>

<table width="100%" cellspacing="0" border="1" cellpadding="55%" style="padding:200;">
<tr valign="bottom">
	<td class="header1" align="center" valign="middle"
	width="22%"> </td>
	<td class="header1" align="center" valign="middle"
		  width="30%"><p style="font-size:26px;font-weight:bold;text-align:left;color:white;">$from_date</p></td>
	<td class="header1" align="center" valign="middle"
		  width="23%"><p style="font-size:26px;font-weight:bold;text-align:left;color:white;">$thru_date</p></td>
	
	
</tr>
</table>
EOD;

				log_message('debug', '[SIMPONI] ' . __CLASS__ . '@' . __FUNCTION__ . " - no_kta: ${no_kta}, jenis_anggota: ${jenis_anggota} , temp_jenis: \n" . $html);

				// Print text using writeHTMLCell()
				$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
				//$pdf->SetAlpha(0.8);

				$html2 = <<<EOD
<p style="font-size:48px;"> </p>
<table width="100%" cellspacing="0" border="1" cellpadding="55%" style="padding:200;">
<tr valign="middle">
	<td class="header1" align="center" valign="middle"
	width="72%"> </td>
	
	<td class="header1" align="center" valign="middle"
		  width="19%"><img class="img-fluid" style="text-align:right;" height="130" src="$barcode" title=""></td>
	<td class="header1" align="center" valign="middle"
	width="9%"> </td>
</tr>
</table>
EOD;

				$pdf->writeHTMLCell(0, 0, 0, 120, $html2, 0, 1, 0, true, '', true);

				//Close and output PDF document
				$pdf->Output($nim . '.pdf', 'D');
			}
		}
	}

	function download_kta_2_test()
	{
		if ($this->session->userdata('user_id') == '') {
			redirect('auth/login');
		}
		$id = 38274;
		$members = $this->main_mod->msrwhere('members', array('person_id' => $id, 'status' => 1), 'id', 'desc')->result();
		$user_profiles = $this->main_mod->msrwhere('user_profiles', array('user_id' => $id), 'id', 'desc')->result();

		if (isset($members[0]->no_kta) && isset($user_profiles[0]->firstname)) {

			$name = trim(strtoupper($user_profiles[0]->firstname)) . " " . trim(strtoupper($user_profiles[0]->lastname));
			$photo = (($user_profiles[0]->photo != '') ? base_url() . 'assets/uploads/' . $user_profiles[0]->photo : "");
			$photo_cir = $user_profiles[0]->photo;
			$no_kta = str_pad($members[0]->code_wilayah, 4, '0', STR_PAD_LEFT) . '.' . str_pad($members[0]->code_bk_hkk, 2, '0', STR_PAD_LEFT) . '.' . str_pad($members[0]->no_kta, 6, '0', STR_PAD_LEFT);


			$phpdate = strtotime($members[0]->from_date);
			$from_date = date('m/y', $phpdate);

			$phpdate = strtotime($members[0]->thru_date);
			$thru_date = date('m/y', $phpdate);

			$id_name = preg_replace("/[^a-zA-Z0-9]+/", "", $name);
			$nim = str_pad($members[0]->no_kta, 6, '0', STR_PAD_LEFT) . '_' . $id_name . '_' . date('mY', $phpdate);

			//BARCODE
			/*$this->load->library('zend');		 
		//load yang ada di folder Zend
		$this->zend->load('Zend/Barcode');		 
		//generate barcodenya
		//$kode = 12345abc;
		$img = Zend_Barcode::draw('code128', 'image', array('text'=>$nim, 'drawText' => false,'barThinWidth'=>2,'backgroundColor'=>'#FE6601'), array());
		$code = $nim;
		$store_image = imagepng($img,FCPATH."./assets/uploads/barcode/{$code}.png");
		//return $code.'.png';
		$barcode = base_url().'assets/uploads/barcode/'.$code.'.png';*/
			//BARCODE
			//height="80" width="250"
			//QR

			$this->load->library('ciqrcode'); //pemanggilan library QR CODE

			$config['cacheable']    = true; //boolean, the default is true
			//$config['cachedir']     = './assets/uploads/qr/'; //string, the default is application/cache/
			//$config['errorlog']     = './assets/uploads/qr/'; //string, the default is application/logs/
			$config['imagedir']     = './assets/uploads/qr/'; //direktori penyimpanan qr code
			$config['quality']      = true; //boolean, the default is true
			$config['size']         = '1024'; //interger, the default is 1024
			$config['black']        = array(224, 255, 255); // array, default is array(255,255,255)
			$config['white']        = array(70, 130, 180); // array, default is array(0,0,0)
			$this->ciqrcode->initialize($config);

			$image_name = $nim . '.jpg'; //buat name dari qr code sesuai dengan nim

			$params['data'] = $nim; //data yang akan di jadikan QR CODE
			$params['level'] = 'H'; //H=High
			$params['size'] = 10;
			$params['savename'] = FCPATH . $config['imagedir'] . $image_name; //simpan image QR CODE ke folder assets/images/
			$this->ciqrcode->generate($params); // fungsi untuk generate QR CODE
			$barcode = $params['savename'];
			//



			//print_r($user_profiles[0]->photo);

			$this->load->library('Pdf');

			$your_width = 354;
			$your_height = 216;
			$custom_layout = array($your_width, $your_height);

			$pdf = new Pdf('L', 'mm', $custom_layout, true, 'UTF-8', false);

			$pdf->SetProtection(array('copy', 'modify', 'annot-forms', 'fill-forms', 'extract', 'assemble', 'owner'), "", "masterpassword123", 0, null);

			$pdf->setPrintHeader(false);
			$pdf->setPrintFooter(false);

			// set margins
			$pdf->SetMargins(0, 0, 0, true);

			// set auto page breaks false
			$pdf->SetAutoPageBreak(false, 0);

			// add a page


			$pdf->AddPage('L', $custom_layout);

			// Display image on full page
			//$pdf->Image('background.jpg', 0, 0, 210, 297, 'JPG', '', '', true, 200, '', false, false, 0, false, false, true);
			$img_file = FCPATH . './assets/images/background_new.jpg';
			$pdf->Image($img_file, 0, 0, 354, 216, '', '', '', false, 300, '', false, false, 0);

			if (strpos(strtolower($photo), '.pdf') !== false) {
				$im = new imagick($photo);
				$im->setImageFormat('jpg');
				$file = base_url() . 'assets/uploads/' . str_replace("pdf", "jpg", $user_profiles[0]->photo);
				$im->writeImage(FCPATH . './assets/uploads/' . (str_replace("pdf", "jpg", $user_profiles[0]->photo)));
				$photo_cir = (str_replace("pdf", "jpg", $user_profiles[0]->photo));
				//header('Content-Type: image/jpeg');
				//$imgtemp = 'data:image/jpg;base64,'.base64_encode($im->getImageBlob());
				$im->destroy();
				$photo = '<img class="img-fluid" style="text-align:right;padding:200;" height="300" width="250" src="' . $file . '" title="">';
				//echo $img;
			}

			if ($photo_cir != '' && $photo_cir != ' ') {
				/*list($width, $height) = getimagesize(FCPATH.'./assets/uploads/'.$photo_cir);
			if ($width > $height) {
				$filename=FCPATH.'./assets/uploads/'.$photo_cir;
				// Load the image
				$source = imagecreatefromjpeg($filename);
				// Rotate
				$rotate = imagerotate($source, 90, 0);
				//and save it on your server...
				imagejpeg($rotate, $filename);			
				//echo 'Landscape';
			} else {
				//echo 'Portrait';// Portrait or Square
			}*/

				if (strpos(strtolower($photo_cir), '.jpg') !== false) {
					$filename = FCPATH . './assets/uploads/' . $photo_cir;
					/*$image = imagecreatefromstring(file_get_contents(FCPATH.'./assets/uploads/'.$photo_cir));//imagecreatefromjpeg($filename);
				$exif = exif_read_data(FCPATH.'./assets/uploads/'.$photo_cir);
				if(!empty($exif['Orientation'])) {
					switch($exif['Orientation']) {
						case 8:
							$image = imagerotate($image,90,0);
							break;
						case 3:
							$image = imagerotate($image,180,0);
							break;
						case 6:
							$image = imagerotate($image,-90,0);
							break;
					}
				}
				imagejpeg($image, $filename);*/
				}
			} else {
				$photo = '<img class="img-fluid" style="text-align:right;padding:200;" height="300" width="250" src="' . base_url() . 'assets/images/nophoto.jpg' . '" title="">';
			}

			//CIRCULAR IMAGE	
			//$outfile = FCPATH.'./assets/uploads/'.(str_replace(".","_.",$photo_cir));
			//$photo = base_url().'assets/uploads/'.str_replace(".","_.",$photo_cir);
			/*
		$circle = new Imagick();
		$circle->newImage(185.5, 185.5, 'none');
		$circle->setimageformat('png');
		//$circle->setimagematte(true);
		$draw = new ImagickDraw();
		$draw->setfillcolor('#ffffff');
		$draw->circle(185.5/2, 185.5/2, 185.5/2, 185.5);
		$circle->drawimage($draw);

		$imagick = new Imagick();
		$imagick->readImage(FCPATH.'./assets/uploads/'.$photo_cir);
		$imagick->setImageFormat( "png" );
		//$imagick->setimagematte(true);
		$imagick->cropimage(185.5, 185.5, 253, 0);
		$imagick->compositeimage($circle, Imagick::COMPOSITE_DSTIN, 0, 0);
		$imagick->writeImage($outfile);
		$imagick->destroy();*/

			//exec("convert ".FCPATH.'./assets/uploads/'.$photo_cir.' ( -clone 0 -fill black -colorize 100 -fill white -draw "translate %[fx:w/2],%[fx:h/2] circle 0,0 0,%[fx:min(w/2,h/2)]" ) -alpha off -compose copy_opacity -composite -trim '.$outfile);		


			//exec('convert -size 200x200 xc:none -fill '.FCPATH.'./assets/uploads/'.$photo_cir.' -draw "circle 100,100 100,1" '.$outfile);

			//exec('convert '.FCPATH.'./assets/uploads/'.$photo_cir.'  -resize x800 -resize "800x<"   -resize 50% -gravity center  -crop 400x400+0+0 +repage ( +clone -threshold -1 -negate -fill white -draw "circle 200,200 200,0" ) -compose DstIn -composite -auto-orient '.$outfile);

			/*
<h1>Welcome to <a href="http://www.tcpdf.org" style="text-decoration:none;background-color:#CC0000;color:black;">&nbsp;<span style="color:black;">TC</span><span style="color:white;">PDF</span>&nbsp;</a>!</h1>
<i>This is the first example of TCPDF library.</i>
<p>This text is printed using the <i>writeHTMLCell()</i> method but you can also use: <i>Multicell(), writeHTML(), Write(), Cell() and Text()</i>.</p>
<p>Please check the source code documentation and other examples for further information.</p>
<p style="color:#CC0000;">TO IMPROVE AND EXPAND TCPDF I NEED YOUR SUPPORT, PLEASE <a href="http://sourceforge.net/donate/index.php?group_id=128076">MAKE A DONATION!</a></p>
<p style="font-size:34px;">Please check the source code documentation and other examples for further information.</p>*/

			$fontname = TCPDF_FONTS::addTTFfont(FCPATH . './assets/fonts/CREDC___.ttf', 'credc', '', 96);
			$fontname2 = TCPDF_FONTS::addTTFfont(FCPATH . './assets/fonts/Montserrat-Bold.ttf', 'mont', '', 96);
			$pdf->SetFont($fontname2, '', 14, '', false);

			$tmp = '3px';
			$len = strlen($name);

			if ($len <= 60) $tmp = '11px';

			$html = <<<EOD
<p style="font-size:20px;"> </p>
<table width="100%" cellspacing="0" border="1" cellpadding="55%" style="padding:200;">
<tr valign="bottom">
	<td class="header1" align="center" valign="middle"
		  width="72%"></td>
	<td class="header1" align="center" valign="middle"
		  width="19%"><img class="img-fluid" style="text-align:right;padding:200;" width="350" height="400" src="$photo" title=""></td>
	<td class="header1" align="center" valign="middle"
	width="9%"> </td>
</tr>
</table>
<p style="font-size:35px;"> </p>
<table width="100%" cellspacing="0" border="1" cellpadding="55%" style="padding:200;">
<tr valign="bottom">
	<td class="header1" align="center" valign="middle"
	width="8%"> </td>
	<td class="header1" align="center" valign="middle"
		  width="85%"><p style="font-size:24px;font-weight:bold;text-align:left;color:white;font-family:$fontname2">$name</p></td>
	<td class="header1" align="center" valign="middle"
		  width="7%"> </td>
</tr>
</table>
<p style="font-size:$tmp;"> </p>
<table width="100%" cellspacing="0" border="1" cellpadding="55%" style="padding:200;">
EOD;
			$pdf->SetFont($fontname, '', 14, '', false);
			$html .= <<<EOD
<tr valign="bottom">
	<td class="header1" align="center" valign="middle"
	width="8%"> </td>
	<td class="header1" align="center" valign="middle"
		  width="85%"><p style="font-size:46px;font-weight:bold;text-align:left;color:#ff7700;">$no_kta</p></td>
	<td class="header1" align="center" valign="middle"
		  width="7%"></td>
</tr>
</table>

<table width="100%" cellspacing="0" border="1" cellpadding="55%" style="padding:200;">
<tr valign="bottom">
	<td class="header1" align="center" valign="middle"
	width="22%"> </td>
	<td class="header1" align="center" valign="middle"
		  width="30%"><p style="font-size:26px;font-weight:bold;text-align:left;color:white;">$from_date</p></td>
	<td class="header1" align="center" valign="middle"
		  width="23%"><p style="font-size:26px;font-weight:bold;text-align:left;color:white;">$thru_date</p></td>
	
	
</tr>
</table>
EOD;

			// Print text using writeHTMLCell()
			$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
			//$pdf->SetAlpha(0.8);

			$html2 = <<<EOD
<p style="font-size:48px;"> </p>
<table width="100%" cellspacing="0" border="1" cellpadding="55%" style="padding:200;">
<tr valign="middle">
	<td class="header1" align="center" valign="middle"
	width="72%"> </td>
	
	<td class="header1" align="center" valign="middle"
		  width="19%"><img class="img-fluid" style="text-align:right;" height="130" src="$barcode" title=""></td>
	<td class="header1" align="center" valign="middle"
	width="9%"> </td>
</tr>
</table>
EOD;

			$pdf->writeHTMLCell(0, 0, 0, 120, $html2, 0, 1, 0, true, '', true);

			//Close and output PDF document
			$pdf->Output($nim . '.pdf', 'D');
		}
	}

	function download_skip()
	{
		$akses = array("0", "2", "11", "1");
		if (!in_array($this->session->userdata('type'), $akses)) {
			//redirect('admin/dashboard');
			//exit;
		}
		$this->load->model('main_mod');
		$idx = $this->input->get('id');
		$user_cert = $this->main_mod->msrwhere('user_cert', array('id' => $idx, 'status' => 2), 'id', 'desc')->row();

		if (isset($user_cert->user_id) && isset($user_cert->startyear)) {

			$m_bk = $this->main_mod->msrwhere('m_bk', array('trim(leading "0" from value) = ' => ltrim($user_cert->ip_bk, '0')), 'id', 'desc')->row();

			$nama_bk = $m_bk->nama_id;
			$nama_bk_en = $m_bk->nama_en;

			$m_bk_skip = $this->main_mod->msrquery('select * from m_bk_skip where value = ' . $m_bk->id . ' and "' . $user_cert->startyear . '" between startdate and enddate order by id desc')->row();

			$id = $user_cert->user_id;
			$members = $this->main_mod->msrwhere('members', array('person_id' => $id, 'status' => 1), 'id', 'desc')->result();
			$user_profiles = $this->main_mod->msrwhere('user_profiles', array('user_id' => $id), 'id', 'desc')->result();
			$users = $this->main_mod->msrwhere('users', array('id' => $id), 'id', 'desc')->row();

			//print_r($m_bk_skip);
			//exit;

			if (strtotime($members[0]->thru_date) >= strtotime(date('Y-m-d')) && $users->username != '') {

				$name = $user_cert->ip_name != '' ? $user_cert->ip_name : $name;
				$photo = (($user_profiles[0]->photo != '') ? base_url() . 'assets/uploads/' . $user_profiles[0]->photo : "");
				$photo_cir = $user_profiles[0]->photo;
				$no_kta = str_pad($members[0]->code_wilayah, 4, '0', STR_PAD_LEFT) . '.' . str_pad($members[0]->code_bk_hkk, 2, '0', STR_PAD_LEFT) . '.' . str_pad($members[0]->no_kta, 6, '0', STR_PAD_LEFT);


				$phpdate = strtotime($user_cert->startyear);
				$from_date = date('m/y', $phpdate);

				$phpdate = strtotime($user_cert->endyear);
				$thru_date = date('m/y', $phpdate);
				if ($thru_date == '01/70') $thru_date = "01/30";
				else if ($members[0]->thru_date == '0000-00-00') $thru_date = "-";

				$id_name = preg_replace("/[^a-zA-Z0-9]+/", "", $name);
				$nim = preg_replace("/[^a-zA-Z0-9]+/", "_", $user_cert->lic_num) . '_' . str_pad($members[0]->no_kta, 6, '0', STR_PAD_LEFT) . '_' . $id_name . '_' . (isset($user_cert->startyear) ? $user_cert->startyear : "") . '_' . (isset($user_cert->endyear) ? $user_cert->endyear : "");



				$this->load->library('ciqrcode'); //pemanggilan library QR CODE

				$config['cacheable']    = true; //boolean, the default is true
				//$config['cachedir']     = './assets/uploads/qr/'; //string, the default is application/cache/
				//$config['errorlog']     = './assets/uploads/qr/'; //string, the default is application/logs/
				$config['imagedir']     = './assets/uploads/qr/'; //direktori penyimpanan qr code
				$config['quality']      = true; //boolean, the default is true
				$config['size']         = '1024'; //interger, the default is 1024
				$config['black']        = array(224, 255, 255); // array, default is array(255,255,255)
				$config['white']        = array(70, 130, 180); // array, default is array(0,0,0)
				$this->ciqrcode->initialize($config);

				$image_name = $nim . '.jpg'; //buat name dari qr code sesuai dengan nim

				$params['data'] = $nim; //data yang akan di jadikan QR CODE
				$params['level'] = 'H'; //H=High
				$params['size'] = 10;
				$params['savename'] = FCPATH . $config['imagedir'] . $image_name; //simpan image QR CODE ke folder assets/images/
				$this->ciqrcode->generate($params); // fungsi untuk generate QR CODE
				$barcode = $params['savename'];



				//print_r($user_profiles[0]->photo);

				$this->load->library('Pdf');

				$your_width = 296.8;
				$your_height = 210.1;
				$custom_layout = array($your_width, $your_height);

				$pdf = new Pdf('L', 'mm', $custom_layout, true, 'UTF-8', false);

				$pdf->SetProtection(array('copy', 'modify', 'annot-forms', 'fill-forms', 'extract', 'assemble', 'owner'), "", "masterpassword123", 0, null);

				$pdf->setPrintHeader(false);
				$pdf->setPrintFooter(false);

				// set margins
				$pdf->SetMargins(0, 0, 0, true);

				// set auto page breaks false
				$pdf->SetAutoPageBreak(false, 0);

				// add a page


				$pdf->AddPage('L', $custom_layout);

				// Display image on full page
				//$pdf->Image('background.jpg', 0, 0, 210, 297, 'JPG', '', '', true, 200, '', false, false, 0, false, false, true);
				$img_file = FCPATH . './assets/images/background_sip.png';
				$pdf->Image($img_file, 0, 0, 296.8, 210.1, '', '', '', false, 300, '', false, false, 0);

				/*if (strpos(strtolower($photo), '.pdf') !== false){
			$im = new imagick( $photo);
			$im->setImageFormat('jpg');		
			$file = base_url().'assets/uploads/'.str_replace("pdf","jpg",$user_profiles[0]->photo);
			$im->writeImage(FCPATH.'./assets/uploads/'.(str_replace("pdf","jpg",$user_profiles[0]->photo)));
			$photo_cir = (str_replace("pdf","jpg",$user_profiles[0]->photo));
			$im->destroy();
			$photo = '<img class="img-fluid" style="text-align:right;padding:200;" height="300" width="250" src="'.$file.'" title="">';
		}
		
		if($photo_cir!='' && $photo_cir!=' '){
			
			
			if (strpos(strtolower($photo_cir), '.jpg') !== false){
				$filename=FCPATH.'./assets/uploads/'.str_replace("_","\\_",str_replace(" ","\\ ",$photo_cir));
			}
		}
		else{
			$photo = '<img class="img-fluid" style="text-align:right;padding:200;" height="300" width="250" src="'.base_url().'assets/images/nophoto.jpg'.'" title="">';
		}*/



				//$fontname = TCPDF_FONTS::addTTFfont(FCPATH.'./assets/fonts/CREDC___.ttf', 'credc', '', 96);
				//$fontname2 = TCPDF_FONTS::addTTFfont(FCPATH.'./assets/fonts/Montserrat-Bold.ttf', 'mont', '', 96);
				//$fontname2 = TCPDF_FONTS::addTTFfont(FCPATH.'./assets/fonts/Calibri-Regular.ttf', 'cal', '', 96);
				//$fontname2_italic = TCPDF_FONTS::addTTFfont(FCPATH.'./assets/fonts/Calibri-Italic.ttf', 'cal_italic', '', 96);
				//$pdf->SetFont($fontname2, '', 14, '', false);
				$pdf->setFont('calibri', '', 13, '', false);
				$pdf->setCellHeightRatio(1);
				$tmp = '3px';
				$len = strlen($name);
				$no_seri = isset($user_cert->description) ? $user_cert->description : '000000';

				$name = $user_cert->ip_name != '' ? $user_cert->ip_name : $name;
				$tgl_sk = $user_cert->startyear;
				$tgl_sk = ucwords($this->tgl_indo($tgl_sk));
				$no_stri = str_pad($members[0]->code_wilayah, 4, '0', STR_PAD_LEFT) . '-' . str_pad($user_cert->ip_bk, 2, '0', STR_PAD_LEFT) . '-' . str_pad($members[0]->no_kta, 6, '0', STR_PAD_LEFT);
				$stri_type = '';
				$stri_type2 = '';

				if ($user_cert->cert_title != "") {
					if ($user_cert->cert_title == "IPU") {
						$stri_type = 'Insinyur Profesional Utama';
						$stri_type2 = 'Executive Professional Engineer';
					} else if ($user_cert->cert_title == "IPM") {
						$stri_type = 'Insinyur Profesional Madya';
						$stri_type2 = 'Senior Professional Engineer';
					} else if ($user_cert->cert_title == "IPP") {
						$stri_type = 'Insinyur Profesional Pratama';
						$stri_type2 = 'Junior Professional Engineer';
					}
				}

				$no_cert = $user_cert->lic_num;
				$ketua = 'Dr. Ir. HERU DEWANTO, ST., M.Sc.(Eng.), IPU., ASEAN Eng., ACPE.';
				$nama_ketua = isset($m_bk_skip->nama_ketua) ? $m_bk_skip->nama_ketua : '';
				$ttd_ketum = FCPATH . './assets/images/tanda_tangan_ketum.jpg';
				$ttd_ketua = isset($m_bk_skip->ttd) ? FCPATH . './assets/uploads/ttd/' . $m_bk_skip->ttd : '';

				if ($len <= 60) $tmp = '11px';

				$html = <<<EOD
<p style="font-size:13px;"> </p>
<table width="100%" cellspacing="0" border="0" cellpadding=""><tr valign="bottom">
<td class="header1" align="center" valign="middle" width="88%"> </td>
<td class="header1" align="left" valign="middle" width="7%" style="font-weight:bold;font-size:9px;">No. Sertifikat <br /><span style="font-family: 'calibri', Times, serif;font-style: italic;font-weight:normal;">Certificate No.</span></td>
<td class="header1" align="left" valign="middle" width="5%" style="font-weight:bold;font-size:9px;">$no_seri </td>
</tr></table>

<p style="font-size:45px;"> </p>

<table width="100%" cellspacing="0" border="0" cellpadding="">
<tr valign="bottom">
	<td class="header1" align="center" valign="bottom"
		  width="9%"></td>
	<td class="header1" align="center" valign="middle"
		  width="80%">
	<p>Persatuan Insinyur Indonesia dengan ini menetapkan<br /><span style="font-size:12px;font-style: italic;">The Institution of Engineers Indonesia, hereby certify</span></p>
	<span style="font-size:20px;font-family: 'calibrib', Times, serif;text-align:center;">$name </span><br />
	<p style="font-size:11px;font-family: 'calibri', Times, serif;"> Nomor Keanggotaan | <span style="font-style: italic;">Membership Number</span> <br />
	<span style="font-size:14px;font-weight:bold;text-align:center;">$no_stri</span></p> 
	<p style="font-size:12px;font-family: 'calibri', Times, serif;"> sebagai | <span style="font-style: italic;">as</span><br />
	<span style="font-size:18px;font-family: 'calibrib', Times, serif;text-align:center;">$stri_type</span><br />
	<span style="font-size:16px;font-family: 'calibrib', Times, serif;text-align:center;font-style: italic;">$stri_type2</span></p> 
	<p style="font-size:11px;font-family: 'calibri', Times, serif;">Nomor Sertifikasi  |  <span style="font-style: italic;">Certification Number</span> <br />
	<span style="font-size:14px;font-weight:bold;text-align:center;">$no_cert</span> </p>
	<p style="font-size:11px;font-family: 'calibri', Times, serif;">berdasarkan Bakuan Kompetensi Insinyur Profesional yang ditentukan oleh Persatuan Insinyur Indonesia dan berlaku masa 5 (lima) tahun <br />  <span style="font-size:10px;font-style: italic;">In accordance with the Competency Standard for Professional Engineer as defined by the Instituion of Engineers Indonesia, and is valid for 5 (five) years</span></p> 
	<p style="font-size:11px;font-family: 'calibri', Times, serif;">Ditetapkan di, pada tanggal | <span style="font-style: italic;">Issued at, on the date of</span> <br />
	<span style="font-size:11px;font-family: 'calibri', Times, serif;">Jakarta, Indonesia, $tgl_sk </span> </p>
	
	</td>
	<td class="header1" align="center" valign="middle"
	width="4%"> </td>
</tr>
</table>
<table width="100%" cellspacing="0" border="0" cellpadding="">
<tr valign="bottom">
	<td class="header1" align="center" valign="middle"
	width="3%"> </td>
	<td class="header1" align="center" valign="middle"
		  width="43%" style="font-weight:bold;font-size:12px;">Pengurus Badan Keahlian $nama_bk<br /><span style="font-style: italic;font-size:11px;font-weight:normal;">Board of $nama_bk_en</span></td>
	<td class="header1" align="center" valign="middle"
	width="7%"> </td>
	<td class="header1" align="center" valign="middle"
		  width="40%" style="font-weight:bold;font-size:12px;">Pengurus Pusat Persatuan Insinyur Indonesia<br /><span style="font-style: italic;font-size:11px;font-weight:normal;">National Board of the Institution of Engineers Indonesia</span></td>
	<td class="header1" align="center" valign="middle"
		  width="4%"> </td>
</tr>
<tr valign="bottom">
	<td class="header1" align="center" valign="middle"
	width="3%"> </td>
	<td class="header1" align="center" valign="middle"
		  width="43%" style="font-weight:bold;font-size:11px;"><img class="img-fluid" style="text-align:left;" height="50" src="$ttd_ketua" title=""></td>
	<td class="header1" align="center" valign="middle"
	width="7%"> <img class="" height="120" src="$barcode" title=""></td>
	<td class="header1" align="center" valign="middle"
		  width="43%" style="font-weight:bold;font-size:11px;"><img class="img-fluid" style="text-align:left;" height="50" src="$ttd_ketum" title=""></td>
	<td class="header1" align="center" valign="middle"
		  width="4%"> </td>
</tr>
<tr valign="bottom">
	<td class="header1" align="center" valign="middle"
	width="3%"> </td>
	<td class="header1" align="center" valign="middle"
		  width="43%" style="font-weight:bold;font-size:11px;">$nama_ketua<br /><span style="font-size:11px;font-weight:normal;">Ketua | </span><span style="font-style: italic;font-size:11px;font-weight:normal;">Chairman</span></td>
	<td class="header1" align="center" valign="middle"
	width="7%"> </td>
	<td class="header1" align="center" valign="middle"
		  width="43%" style="font-weight:bold;font-size:11px;">$ketua<br /><span style="font-size:11px;font-weight:normal;">Ketua Umum | </span><span style="font-style: italic;font-size:11px;font-weight:normal;">President</span></td>
	<td class="header1" align="center" valign="middle"
		  width="4%"> </td>
</tr>
</table>
EOD;

				// Print text using writeHTMLCell()
				$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

				//Close and output PDF document
				$pdf->Output($nim . '.pdf', 'D');
			}
		}
	}

	function download_stri_ttd()
	{
		if ($this->session->userdata('user_id') == '') {
			redirect('auth/login');
		}

		$this->load->model('main_mod');
		$cert_id = $this->uri->segment(3);
		$id = $this->session->userdata('user_id');
		$members = $this->main_mod->msrwhere('members', array('person_id' => $id, 'status' => 1), 'id', 'desc')->result();
		$user_profiles = $this->main_mod->msrwhere('user_profiles', array('user_id' => $id), 'id', 'desc')->result();
		$stri = $this->main_mod->msrwhere('members_certificate', array('person_id' => $id, 'id' => $cert_id), 'id', 'desc')->result();
		$dibuat = $this->members_model->ambil_create_date($id);
		$dibuatnya = $dibuat->createddate; // Tambahanby Ipur 

		if (isset($members[0]->no_kta) && isset($user_profiles[0]->firstname) && isset($stri[0])) {

			$name = trim(strtoupper($user_profiles[0]->firstname)) . " " . trim(strtoupper($user_profiles[0]->lastname));
			if (isset($stri[0]->add_name)) $name = $stri[0]->add_name;
			$tgl_sk = $stri[0]->stri_sk;
			$tgl_sk = strtoupper($this->tgl_indo($tgl_sk));
			$no_seri = str_pad($stri[0]->stri_id, 7, '0', STR_PAD_LEFT);
			$no_stri = ($stri[0]->certificate_type != "" ? $stri[0]->certificate_type : "0") . '.' . ($stri[0]->stri_code_bk_hkk == "" ? "000" : str_pad($stri[0]->stri_code_bk_hkk, 3, '0', STR_PAD_LEFT)) . '.' . str_pad($stri[0]->th, 2, '0', STR_PAD_LEFT) . '.' . $stri[0]->warga . '.' . $stri[0]->stri_tipe . '.' . str_pad($stri[0]->stri_id, 8, '0', STR_PAD_LEFT);

			$photo = (($user_profiles[0]->photo != '') ? base_url() . 'assets/uploads/' . $user_profiles[0]->photo : "");
			$photo_cir = $user_profiles[0]->photo;
			$no_kta = str_pad($members[0]->code_wilayah, 4, '0', STR_PAD_LEFT) . '.' . str_pad($members[0]->code_bk_hkk, 2, '0', STR_PAD_LEFT) . '.' . str_pad($members[0]->no_kta, 6, '0', STR_PAD_LEFT);

			$kualifikasi = '';
			$txt_kualifiaksi_id = 'dinyatakan memiliki kompetensi sebagai :';
			$txt_kualifiaksi_en = 'is recognized to have competency as';
			$year_kualifiaksi_id = '5 (lima)';
			$year_kualifiaksi_en = '5 ( five )';

			$tgl_penomoran = '';



			if ($stri[0]->certificate_type != "") {
				if ($stri[0]->certificate_type == "3")
					$kualifikasi = 'INSINYUR PROFESIONAL UTAMA';
				else if ($stri[0]->certificate_type == "2")
					$kualifikasi = 'INSINYUR PROFESIONAL MADYA';
				else if ($stri[0]->certificate_type == "1")
					$kualifikasi = 'INSINYUR PROFESIONAL PRATAMA';
				else if ($stri[0]->certificate_type == "0") {
					$kualifikasi = 'INSINYUR PROFESIONAL';
					$txt_kualifiaksi_id = 'dinyatakan sebagai :';
					$txt_kualifiaksi_en = 'is recognized as :';
					$year_kualifiaksi_id = '3 (tiga)';
					$year_kualifiaksi_en = '3 ( three )';
				}

				//--------------------------------------------- SCRIPT ASLI -------------------------------------------------------------------------------
				/*
				if($stri[0]->certificate_type=="3" || $stri[0]->certificate_type=="2" || $stri[0]->certificate_type=="1"){
					$cek_ketum=$this->main_mod->msrquery('select ut.createddate from user_transfer ut join user_cert uc on ut.rel_id=uc.id join members_certificate mc on TRIM(LEADING "0" FROM mc.skip_id)=TRIM(LEADING "0" FROM uc.ip_seq) where mc.id='.$stri[0]->id.' and ut.user_id = '.$id.' order by ut.createddate desc limit 1')->row();		
					if(isset($cek_ketum->createddate)){
						if(strtotime($cek_ketum->createddate)<= strtotime('2021-12-18'))
							$tgl_penomoran = '2021-12-17';
						else
							$tgl_penomoran = $cek_ketum->createddate;
					}
					else $tgl_penomoran = '2021-12-17';
				}
*/
				//----------------------------------------------------------------------------------------------------------------------------------------------	
				//----------------------- PERUBAHAN ----------------------------------------------------------------------------------------------------------

				if ($stri[0]->certificate_type == "3" || $stri[0]->certificate_type == "2" || $stri[0]->certificate_type == "1") {
					$cek_ketum = $this->main_mod->msrquery('select ut.createddate from user_transfer ut join user_cert uc on ut.rel_id=uc.id join members_certificate mc on TRIM(LEADING "0" FROM mc.skip_id)=TRIM(LEADING "0" FROM uc.ip_seq) where mc.id=' . $stri[0]->id . ' and ut.user_id = ' . $id . ' order by ut.createddate desc limit 1')->row();

					if (isset($cek_ketum->createddate)) {
						if (strtotime($cek_ketum->createddate) <= strtotime('2021-11-23'))

							$tgl_penomoran = $stri[0]->stri_sk;
						else

							$tgl_penomoran = $stri[0]->stri_sk;
					} else $tgl_penomoran = $stri[0]->stri_sk;
				}
				//---------------------------------------------------------------------------------------------------------------------------------------																																																																																																																			 											 
			}

			$ketua = '';

			$tgl_penomoran = $stri[0]->stri_sk;

			if (strtotime($dibuatnya) >= strtotime('2025-05-30')) {
				$ketua = '';
				$barcode = '';
			}

			//$nim = str_replace(".","-",$no_stri.'_'.str_pad($members[0]->no_kta,6,'0',STR_PAD_LEFT).'_'.$name.'_'.(isset($stri[0]->skip_sk)?$stri[0]->skip_sk:""));	

			$nim = str_replace(".", "-", $no_stri . '_' . str_pad($members[0]->no_kta, 6, '0', STR_PAD_LEFT) . '_' . $name . '_' . (isset($stri[0]->stri_sk) ? $stri[0]->stri_sk : "") . '_' . (isset($stri[0]->stri_thru_date) ? $stri[0]->stri_thru_date : ""));

			//print_r($nim);

			if (strtotime($dibuatnya) <= strtotime('2025-05-29')) {

				$this->load->library('ciqrcode'); //pemanggilan library QR CODE

				$config['cacheable']    = true; //boolean, the default is true
				$config['imagedir']     = './assets/uploads/qr/'; //direktori penyimpanan qr code
				$config['quality']      = true; //boolean, the default is true
				$config['size']         = '1024'; //interger, the default is 1024
				$config['black']        = array(224, 255, 255); // array, default is array(255,255,255)
				$config['white']        = array(70, 130, 180); // array, default is array(0,0,0)
				$this->ciqrcode->initialize($config);

				//$image_name=$nim.'.jpg'; //buat name dari qr code sesuai dengan nim
				$image_name = str_pad($members[0]->no_kta, 6, '0', STR_PAD_LEFT) . '.png'; //buat name dari qr code sesuai dengan nim

				$params['data'] = $nim; //data yang akan di jadikan QR CODE
				$params['level'] = 'H'; //H=High
				$params['size'] = 5;
				$params['savename'] = FCPATH . $config['imagedir'] . $image_name; //simpan image QR CODE ke folder assets/images/
				$this->ciqrcode->generate($params); // fungsi untuk generate QR CODE
				$barcode = $params['savename'];
			} // Eof if(strtotime($dibuatnya) <= strtotime('2025-05-29'))


			//print_r($user_profiles[0]->photo);

			$this->load->library('Pdf');

			$your_width = 296.8;
			$your_height = 210.1;
			$custom_layout = array($your_width, $your_height);

			$pdf = new Pdf('L', 'mm', $custom_layout, true, 'UTF-8', false);

			//$pdf->SetProtection(array('copy','modify','annot-forms','fill-forms','extract','assemble','owner'), "", "masterpassword123", 0, null);

			$pdf->setPrintHeader(false);
			$pdf->setPrintFooter(false);

			// set margins
			$pdf->SetMargins(0, 0, 0, true);

			// set auto page breaks false
			$pdf->SetAutoPageBreak(false, 0);

			// add a page


			$pdf->AddPage('L', $custom_layout);

			// Display image on full page
			//$pdf->Image('background.jpg', 0, 0, 210, 297, 'JPG', '', '', true, 200, '', false, false, 0, false, false, true);
			$img_file = FCPATH . './assets/images/STRI_.png';
			$pdf->Image($img_file, 0, 0, 296.8, 210.1, '', '', '', false, 300, '', false, false, 0);

			if (strpos(strtolower($photo), '.pdf') !== false) {
				$im = new imagick($photo);
				$im->setImageFormat('jpg');
				$file = base_url() . 'assets/uploads/' . str_replace("pdf", "jpg", $user_profiles[0]->photo);
				$im->writeImage(FCPATH . './assets/uploads/' . (str_replace("pdf", "jpg", $user_profiles[0]->photo)));
				$photo_cir = (str_replace("pdf", "jpg", $user_profiles[0]->photo));
				//header('Content-Type: image/jpeg');
				//$imgtemp = 'data:image/jpg;base64,'.base64_encode($im->getImageBlob());
				$im->destroy();
				$photo = $file;
				//echo $img;
			} else {
				if ($photo_cir != '' && $photo_cir != ' ') {
					list($width, $height) = getimagesize(FCPATH . './assets/uploads/' . $photo_cir);
					if ($width > 300) {
						/*$img = new Imagick($photo);
					$img->setImageFormat('jpg');	
					$img->stripImage();
					$img->writeImage(FCPATH.'./assets/uploads/'.(str_replace("png","jpg",$user_profiles[0]->photo)));
					$img->clear();
					$img->destroy();
					$photo = str_replace("png","jpg",$photo);*/
					}
				}
			}

			if ($photo_cir == '' || $photo_cir == ' ') {
				$photo = '<img class="img-fluid" style="text-align:right;padding:200;" height="70" width="70" src="' . base_url() . 'assets/images/nophoto.jpg" title="">';
			} else {

				$photo = '<img class="img-fluid" style="text-align:right;padding:200;" height="70" width="70" src="' . $photo . '" title="">';
			}

			// ------------------------------------------------------------------------------------------------------------------------------------ Tambahan by Ipur Tgl 05-06-2025 ----

			$ttd_ketum = FCPATH . './assets/images/Ketum-Ilham_1.jpg';
			$flag_1 = '';
			$flag_2 = '75';

			if (strtotime($dibuatnya) >= strtotime('2025-05-30')) {
				$ttd_ketum = FCPATH . './assets/images/Ketum-Ilham_1A.jpg';
				$flag_1 = '<br />';
				$flag_2 = '75';
				$barcode = '';
			}

			if (strtotime($dibuatnya) <= strtotime('2024-12-05')) {
				$ttd_ketum = FCPATH . './assets/images/tanda_tangan_ketum_DANIS.png';
				$flag_1 = '<br />';
				$flag_2 = '75';
			}
			//------------------------------------------------------------------------------------------------------------------------------------------------------		
			$tmp = '3px';
			$len = strlen($name);
			//$kualifikasi
			if ($len <= 60) $tmp = '11px';

			$html = <<<EOD
<p style="font-size:18px;"> </p>
<table width="100%" cellspacing="0" border="1" cellpadding="55%">
	<tr valign="bottom">
		<td class="header1" align="center" valign="middle" width="70%"> </td>
		<td class="header1" align="center" valign="middle" width="30%" style="font-weight:bold;"> $no_seri</td>
	</tr>
</table>

<p style="font-size:25px;"> </p>

<table width="100%" cellspacing="0" border="1" cellpadding="55%">
<tr valign="bottom">
	<td class="header1" align="center" valign="bottom"
		  width="9%"></td>
	<td class="header1" align="center" valign="middle"
		  width="82%">
	
	<p style="font-size:9px;text-align:center;">Sesuai dengan Undang-Undang No.11 tahun 2014 tentang Keinsinyuran dan Peraturan Pemerintah Nomor 25 Tahun 2019,<br />
	dengan ini Persatuan Insinyur Indonesia menetapkan bahwa :
	<p style="font-size:1px;"> </p>
	<i  style="font-size:8px;text-align:center;">Based on Law No. 11 of 2014 and Government Regulation No. 25 of 2019, The Institution of Engineers Indonesia Certifies that:</i></p>
	
	<p style="font-size:11px;font-weight:bold;text-align:center;">$name 
	<p style="font-size:2px;"> </p>
	<span style="font-size:10px;font-weight:normal;">  $txt_kualifiaksi_id<br /><i style="font-size:9px;text-align:center;font-weight:normal;">$txt_kualifiaksi_en</i></span>
	<p style="font-size:2px;"> </p>
	<span style="font-size:11px;font-weight:bold;text-align:center;margin-top:10px;">$kualifikasi</span>
	<p style="font-size:2px;"> </p>
	<span style="font-size:10px;font-weight:normal;">  Nomor Registrasi<br /><i style="font-size:9px;text-align:center;font-weight:normal;">Registration Number</i><br /></span><br />
	
	<span style="font-size:11px;font-weight:bold;text-align:center;">$no_stri</span></p>
	
	<p style="font-size:11px;text-align:center;">Surat Tanda Registrasi Insinyur berlaku $year_kualifiaksi_id tahun sejak ditetapkan:<br />
	<i style="font-size:8.5px;text-align:center;">This Certificate of Registration is valid for $year_kualifiaksi_en years since it is stated</i></p>
	
	</td>
	<td class="header1" align="center" valign="middle"
	width="9%"> </td>
</tr>
</table>

<table width="100%" cellspacing="0" border="0" cellpadding="55%">
<tr valign="bottom">
	<td class="header1" align="left" valign="bottom"
		  width="17%"></td>
	<td class="header1" align="left" valign="bottom"
		  width="15%">$photo</td>
	<td class="header1" align="left" valign="middle"
		  width="35%">
	<table>
	<tr><td width="30%"><span style="font-size:11px;">Ditetapkan di</span><br /><i style="font-size:9px;">Stated at</i></td><td width="3%">:</td><td width="67%"><p style="font-size:11px;text-align:left;font-weight:bold;">JAKARTA </p></td></tr>
	<tr><td><span style="font-size:11px;">Tanggal</span><br /><i style="font-size:9px;">Date</i></td><td>:</td><td><p style="font-size:11px;text-align:left;font-weight:bold;">$tgl_sk </p></td></tr>
	
	<tr><td colspan="3"><p style="font-size:11px;text-align:center;font-weight:bold;">Persatuan Insinyur Indonesia<br />
	<span  style="font-size:8px;text-align:center;font-weight:bold;">THE INSTITUTION OF ENGINEERS INDONESIA</span>
	
	<br />$flag_1
	<img class="img-fluid" height="$flag_2" src="$ttd_ketum" title=""></p>
	</td></tr>
	
	</table>
	
	
	</td>

<?php	
// ----------------------------------------------------------------------- Perubahan by Ipur Tgl 05-06-2025
		if(strtotime($dibuatnya) <= strtotime('2025-05-29')) {
		  {  ?>	
	<td class="header1" align="right" valign="middle"  rowspan="3"
	width="15%"> <img class="img-fluid" style="text-align:right;" height="70" width="70" src="$barcode" title=""></td>
	<td class="header1" align="left" valign="bottom"
		  width="15%"></td>
		  
<?php } ?> 
</tr>
</table>
<table width="100%" cellspacing="0" border="0" cellpadding="0%">
<tr valign="bottom">
	<td class="header1" align="center" valign="middle"
	width="8%"> </td>
	<td class="header1" align="center" valign="middle" width="85%" style="font-weight:bold;font-size:11px;">
		  <span style="font-size:11px;text-align:center;font-weight:normal;">Ketua Umum</span><br />
		  <i style="font-size:10px;text-align:center;font-weight:normal;">President</i>
		  </td>

</tr>
</table>
<!-- ---------------------------------------------------------------------------------------------------------------- -->
EOD;
			$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
			//Close and output PDF document
			//print_r($html);
			$pdf->Output($nim . '.pdf', 'D');
		}
	}

	function tgl_indo($tanggal)
	{
		$bulan = array(
			1 =>   'Januari',
			'Februari',
			'Maret',
			'April',
			'Mei',
			'Juni',
			'Juli',
			'Agustus',
			'September',
			'Oktober',
			'November',
			'Desember'
		);
		$pecahkan = explode('-', $tanggal);

		// variabel pecahkan 0 = tanggal
		// variabel pecahkan 1 = bulan
		// variabel pecahkan 2 = tahun

		return ltrim($pecahkan[2], '0') . ' ' . $bulan[(int)$pecahkan[1]] . ' ' . $pecahkan[0];
	}

	function add_exp()
	{
		if ($this->session->userdata('user_id') == '') {
			redirect('auth/login');
		}
		$this->load->model('main_mod');
		//EXPERIENCE
		$title = $this->input->post('title') <> null ? $this->input->post('title') : "";
		$company = $this->input->post('company') <> null ? $this->input->post('company') : "";
		$loc = $this->input->post('location') <> null ? $this->input->post('location') : "";
		$year = $this->input->post('startyear') <> null ? $this->input->post('startyear') : "";
		$year2 = $this->input->post('endyear') <> null ? $this->input->post('endyear') : "";
		$typetimeperiod = $this->input->post('startmonth') <> null ? $this->input->post('startmonth') : "";
		$typetimeperiod2 = $this->input->post('endmonth') <> null ? $this->input->post('endmonth') : "";
		$work = $this->input->post('is_present') <> null ? $this->input->post('is_present') : "";
		$desc = $this->input->post('description') <> null ? $this->input->post('description') : "";
		if ($title != '') {
			try {
				$check = $this->main_mod->msrwhere('m_company', array('desc' => $company), 'id', 'desc')->result();
				if (!isset($check[0])) {
					$row = array(
						'desc' => $company,
					);
					$insert = $this->main_mod->insert('m_company', $row);
				}

				$row = array(
					'user_id' => $this->session->userdata('user_id'),
					'company' => $company,
					'title' => $title,
					'location' => $loc,
					'startyear' => $year,
					'startmonth' => $typetimeperiod,
					'endyear' => $year2,
					'endmonth' => $typetimeperiod2,
					'is_present' => ($work == "true" ? "1" : "0"),
					'description' => $desc
				);
				$insert = $this->main_mod->insert('user_exp', $row);
				echo $insert;
			} catch (Exception $e) {
				print_r($e);
			}
		} else
			echo "not valid";
	}

	function edit_exp()
	{
		if ($this->session->userdata('user_id') == '') {
			redirect('auth/login');
		}
		$this->load->model('main_mod');
		//EXPERIENCE
		$title = $this->input->post('title[]') <> null ? $this->input->post('title[]') : "";
		$company = $this->input->post('company[]') <> null ? $this->input->post('company[]') : "";
		$loc = $this->input->post('loc[]') <> null ? $this->input->post('loc[]') : "";
		$provinsi = $this->input->post('provinsi[]') <> null ? $this->input->post('provinsi[]') : "";
		$negara = $this->input->post('negara[]') <> null ? $this->input->post('negara[]') : "";
		$year = $this->input->post('year[]') <> null ? $this->input->post('year[]') : "";
		$year2 = $this->input->post('year2[]') <> null ? $this->input->post('year2[]') : "";
		$typetimeperiod = $this->input->post('typetimeperiod[]') <> null ? $this->input->post('typetimeperiod[]') : "";
		$typetimeperiod2 = $this->input->post('typetimeperiod2[]') <> null ? $this->input->post('typetimeperiod2[]') : "";
		$work = $this->input->post('work[]') <> null ? $this->input->post('work[]') : "";
		$desc = $this->input->post('desc[]') <> null ? $this->input->post('desc[]') : "";
		$actv = $this->input->post('actv[]') <> null ? $this->input->post('actv[]') : "";
		$expid = $this->input->post('expid[]') <> null ? $this->input->post('expid[]') : "";
		$exp_image_url = $this->input->post('exp_image_url[]') <> null ? $this->input->post('exp_image_url[]') : "";
		//if($company!=''){
		try {
			/*$check = $this->main_mod->msrwhere('m_company',array('desc'=>$company),'id','desc')->result();
				if(!isset($check[0]))
				{
					$row=array(
						'desc' => $company,
					);
					$insert = $this->main_mod->insert('m_company',$row);
				}	*/






			$where = array(
				"user_id" => $this->session->userdata('user_id')
			);
			$row = array(
				'status' => 0,
				'modifieddate' => date('Y-m-d H:i:s'),
				'modifiedby' => $this->session->userdata('user_id'),
			);
			$update = $this->main_mod->update('user_exp', $where, $row);


			$i = 0;
			foreach ($company as $val) {
				$row = array(
					'user_id' => $this->session->userdata('user_id'),
					'company' => strtoupper($company[$i]),
					'title' => strtoupper($title[$i]),
					'location' => strtoupper($loc[$i]),
					'provinsi' => strtoupper($provinsi[$i]),
					'negara' => strtoupper($negara[$i]),
					'startyear' => $year[$i],
					'startmonth' => $typetimeperiod[$i],
					'endyear' => $year2[$i],
					'endmonth' => $typetimeperiod2[$i],
					'is_present' => ($work[$i] == "true" ? "1" : "0"),
					'description' => $desc[$i],
					'actv' => $actv[$i],
					'createdby' => $this->session->userdata('user_id'),
					'attachment' => (isset($exp_image_url[$i]) ? $exp_image_url[$i] : ''),
				);

				if ($expid[$i] == '' || $expid[$i] == '0')
					$catch = $this->main_mod->insert('user_exp', $row);
				else {
					$row = array(
						'user_id' => $this->session->userdata('user_id'),
						'company' => strtoupper($company[$i]),
						'title' => strtoupper($title[$i]),
						'location' => strtoupper($loc[$i]),
						'provinsi' => strtoupper($provinsi[$i]),
						'negara' => strtoupper($negara[$i]),
						'startyear' => $year[$i],
						'startmonth' => $typetimeperiod[$i],
						'endyear' => $year2[$i],
						'endmonth' => $typetimeperiod2[$i],
						'is_present' => ($work[$i] == "true" ? "1" : "0"),
						'description' => $desc[$i],
						'actv' => $actv[$i],
						'status' => 1,
						'modifieddate' => date('Y-m-d H:i:s'),
						'modifiedby' => $this->session->userdata('user_id'),
						'attachment' => (isset($exp_image_url[$i]) ? $exp_image_url[$i] : ''),
					);

					$where = array(
						"user_id" => $this->session->userdata('user_id'),
						'id' => $expid[$i],
					);
					$catch = $this->main_mod->update('user_exp', $where, $row);
				}
				$i++;
			}

			echo "valid";
		} catch (Exception $e) {
			//print_r($e);
			echo "not valid";
		}
		//}
		//else
		//	echo "not valid";

	}

	function edit_exp_old()
	{
		if ($this->session->userdata('user_id') == '') {
			redirect('auth/login');
		}
		$this->load->model('main_mod');
		//EXPERIENCE
		$title = $this->input->post('title') <> null ? $this->input->post('title') : "";
		$company = $this->input->post('company') <> null ? $this->input->post('company') : "";
		$loc = $this->input->post('location') <> null ? $this->input->post('location') : "";
		$year = $this->input->post('startyear') <> null ? $this->input->post('startyear') : "";
		$year2 = $this->input->post('endyear') <> null ? $this->input->post('endyear') : "";
		$typetimeperiod = $this->input->post('startmonth') <> null ? $this->input->post('startmonth') : "";
		$typetimeperiod2 = $this->input->post('endmonth') <> null ? $this->input->post('endmonth') : "";
		$work = $this->input->post('is_present') <> null ? $this->input->post('is_present') : "";
		$desc = $this->input->post('description') <> null ? $this->input->post('description') : "";
		$id = $this->input->post('id') <> null ? $this->input->post('id') : "";
		if ($title != '') {
			try {
				$check = $this->main_mod->msrwhere('m_company', array('desc' => $company), 'id', 'desc')->result();
				if (!isset($check[0])) {
					$row = array(
						'desc' => $company,
					);
					$insert = $this->main_mod->insert('m_company', $row);
				}

				$row = array(
					//'user_id' => $this->session->userdata('user_id'),
					'company' => $company,
					'title' => $title,
					'location' => $loc,
					'startyear' => $year,
					'startmonth' => $typetimeperiod,
					'endyear' => $year2,
					'endmonth' => $typetimeperiod2,
					'is_present' => ($work == "true" ? "1" : "0"),
					'description' => $desc
				);
				$where = array(
					"user_id" => $this->session->userdata('user_id'),
					"id" => $id,
				);
				$update = $this->main_mod->update('user_exp', $where, $row);

				echo 1;
			} catch (Exception $e) {
				print_r($e);
			}
		} else
			echo "not valid";
	}

	function del_exp()
	{
		if ($this->session->userdata('user_id') == '') {
			redirect('auth/login');
		}
		$this->load->model('main_mod');
		//EXPERIENCE
		$id = $this->input->post('id') <> null ? $this->input->post('id') : "";
		if ($id != '') {
			try {
				$check = $this->main_mod->msrwhere('user_exp', array('user_id' => $this->session->userdata('user_id'), 'id' => $id), 'id', 'desc')->result();
				if (isset($check[0])) {
					$update = $this->main_mod->delete('user_exp', 'id', $id);
					//echo 1;
				}
				echo "valid";
			} catch (Exception $e) {
				//print_r($e);
				echo "not valid";
			}
		} else
			echo "not valid";
	}

	function del_phone()
	{
		if ($this->session->userdata('user_id') == '') {
			redirect('auth/login');
		}
		$this->load->model('main_mod');
		//PHONE
		$id = $this->input->post('id') <> null ? $this->input->post('id') : "";
		if ($id != '') {
			try {
				$check = $this->main_mod->msrwhere('contacts', array('user_id' => $this->session->userdata('user_id'), 'id' => $id), 'id', 'desc')->result();
				if (isset($check[0])) {
					$update = $this->main_mod->delete('contacts', 'id', $id);
					//echo 1;
				}
				echo "valid";
			} catch (Exception $e) {
				//print_r($e);
				echo "not valid";
			}
		} else
			echo "not valid";
	}

	function del_email()
	{
		if ($this->session->userdata('user_id') == '') {
			redirect('auth/login');
		}
		$this->load->model('main_mod');
		//PHONE
		$id = $this->input->post('id') <> null ? $this->input->post('id') : "";
		if ($id != '') {
			try {
				$check = $this->main_mod->msrwhere('contacts', array('user_id' => $this->session->userdata('user_id'), 'id' => $id), 'id', 'desc')->result();
				if (isset($check[0])) {
					$update = $this->main_mod->delete('contacts', 'id', $id);
					//echo 1;
				}
				echo "valid";
			} catch (Exception $e) {
				//print_r($e);
				echo "not valid";
			}
		} else
			echo "not valid";
	}

	function add_edu()
	{
		if ($this->session->userdata('user_id') == '') {
			redirect('auth/login');
		}
		$this->load->model('main_mod');
		//EDUCATION
		$school = $this->input->post('school') <> null ? $this->input->post('school') : "";
		$startdate = $this->input->post('dateattend') <> null ? $this->input->post('dateattend') : "";
		$enddate = $this->input->post('dateattend2') <> null ? $this->input->post('dateattend2') : "";
		$degree = $this->input->post('degree') <> null ? $this->input->post('degree') : "";
		$mayor = $this->input->post('mayor') <> null ? $this->input->post('mayor') : "";
		$fieldofstudy = $this->input->post('fos') <> null ? $this->input->post('fos') : "";
		$grade = $this->input->post('grade') <> null ? $this->input->post('grade') : "";
		$score = $this->input->post('score') <> null ? $this->input->post('score') : "";
		$activities = $this->input->post('actv') <> null ? $this->input->post('actv') : "";
		$description = $this->input->post('descedu') <> null ? $this->input->post('descedu') : "";
		if ($school != '') {
			try {
				$row = array(
					'user_id' => $this->session->userdata('user_id'),
					'school' => $school,
					'startdate' => $startdate,
					'enddate' => $enddate,
					'degree' => $degree,
					'mayor' => $mayor,
					'fieldofstudy' => $fieldofstudy,
					'grade' => $grade,
					'score' => $score,
					'activities' => $activities,
					'description' => $description
				);
				$insert = $this->main_mod->insert('user_edu', $row);
				echo $insert;
			} catch (Exception $e) {
				print_r($e);
			}
		} else
			echo "not valid";
	}

	function edit_edu_old()
	{
		if ($this->session->userdata('user_id') == '') {
			redirect('auth/login');
		}
		$this->load->model('main_mod');
		//EDUCATION
		$school = $this->input->post('school') <> null ? $this->input->post('school') : "";
		$startdate = $this->input->post('dateattend') <> null ? $this->input->post('dateattend') : "";
		$enddate = $this->input->post('dateattend2') <> null ? $this->input->post('dateattend2') : "";
		$degree = $this->input->post('degree') <> null ? $this->input->post('degree') : "";
		$fieldofstudy = $this->input->post('fos') <> null ? $this->input->post('fos') : "";
		$grade = $this->input->post('grade') <> null ? $this->input->post('grade') : "";
		$score = $this->input->post('score') <> null ? $this->input->post('score') : "";
		$activities = $this->input->post('actv') <> null ? $this->input->post('actv') : "";
		$description = $this->input->post('descedu') <> null ? $this->input->post('descedu') : "";
		$id = $this->input->post('id') <> null ? $this->input->post('id') : "";
		if ($school != '') {
			try {
				$row = array(
					'school' => $school,
					'startdate' => $startdate,
					'enddate' => $enddate,
					'degree' => $degree,
					'fieldofstudy' => $fieldofstudy,
					'grade' => $grade,
					'score' => $score,
					'activities' => $activities,
					'description' => $description
				);
				$where = array(
					"user_id" => $this->session->userdata('user_id'),
					"id" => $id,
				);
				$update = $this->main_mod->update('user_edu', $where, $row);

				echo 1;
			} catch (Exception $e) {
				print_r($e);
			}
		} else
			echo "not valid";
	}

	function edit_edu()
	{
		if ($this->session->userdata('user_id') == '') {
			redirect('auth/login');
		}
		$this->load->model('main_mod');

		//if ($this->form_validation->run()) {

		$type = $this->input->post('type[]') <> null ? $this->input->post('type[]') : "";
		$school = $this->input->post('school[]') <> null ? $this->input->post('school[]') : "";
		$startdate = $this->input->post('dateattendstart[]') <> null ? $this->input->post('dateattendstart[]') : "";
		$enddate = $this->input->post('dateattendend[]') <> null ? $this->input->post('dateattendend[]') : "";
		$degree = $this->input->post('degree[]') <> null ? $this->input->post('degree[]') : "";
		$mayor = $this->input->post('mayor[]') <> null ? $this->input->post('mayor[]') : "";
		$fieldofstudy = $this->input->post('fos[]') <> null ? $this->input->post('fos[]') : "";
		$title = $this->input->post('title[]') <> null ? $this->input->post('title[]') : "";
		$title_prefix = $this->input->post('title_prefix[]') <> null ? $this->input->post('title_prefix[]') : "";
		$score = $this->input->post('score[]') <> null ? $this->input->post('score[]') : "";
		$activities = $this->input->post('actv[]') <> null ? $this->input->post('actv[]') : "";
		$description_edu = $this->input->post('descedu[]') <> null ? $this->input->post('descedu[]') : "";
		$schoolid = $this->input->post('schoolid[]') <> null ? $this->input->post('schoolid[]') : "";
		$edu_image_url = $this->input->post('edu_image_url[]') <> null ? $this->input->post('edu_image_url[]') : "";

		$cert_name = $this->input->post('certname[]') <> null ? $this->input->post('certname[]') : "";
		$cert_auth = $this->input->post('certauth[]') <> null ? $this->input->post('certauth[]') : "";
		$lic_num = $this->input->post('lic[]') <> null ? $this->input->post('lic[]') : "";
		$cert_url = $this->input->post('url[]') <> null ? $this->input->post('url[]') : "";
		$cert_title = $this->input->post('cert_title[]') <> null ? $this->input->post('cert_title[]') : "";
		$startmonth = $this->input->post('certdate[]') <> null ? $this->input->post('certdate[]') : "";
		$startyear = $this->input->post('certyear[]') <> null ? $this->input->post('certyear[]') : "";
		$endmonth = $this->input->post('certdate2[]') <> null ? $this->input->post('certdate2[]') : "";
		$endyear = $this->input->post('certyear2[]') <> null ? $this->input->post('certyear2[]') : "";
		$is_present = $this->input->post('certwork[]') <> null ? $this->input->post('certwork[]') : "";
		$description = $this->input->post('certdesc[]') <> null ? $this->input->post('certdesc[]') : "";
		$certid = $this->input->post('certid[]') <> null ? $this->input->post('certid[]') : "";
		$cert_image_url = $this->input->post('cert_image_url[]') <> null ? $this->input->post('cert_image_url[]') : "";

		//$id = $this->input->post('id')<>null?$this->input->post('id'):"";
		try {

			$where = array(
				"user_id" => $this->session->userdata('user_id')
			);
			$row = array(
				'status' => 0,
				'modifieddate' => date('Y-m-d H:i:s'),
				'modifiedby' => $this->session->userdata('user_id'),
			);
			$update = $this->main_mod->update('user_edu', $where, $row);


			$i = 0;
			foreach ($school as $val) {
				$row = array(
					'user_id' => $this->session->userdata('user_id'),
					'type' => $type[$i],
					'school' => strtoupper($school[$i]),
					'startdate' => $startdate[$i],
					'enddate' => $enddate[$i],
					'degree' => $degree[$i],
					'mayor' => (isset($mayor[$i]) ? strtoupper($mayor[$i]) : ""),
					'fieldofstudy' => strtoupper($fieldofstudy[$i]),
					'title' => $title[$i],
					'title_prefix' => $title_prefix[$i],
					'score' => $score[$i],
					'activities' => (isset($activities[$i]) ? strtoupper($activities[$i]) : ''),
					'description' => (isset($description_edu[$i]) ? $description_edu[$i] : ''),
					'attachment' => (isset($edu_image_url[$i]) ? $edu_image_url[$i] : ''),
					'createdby' => $this->session->userdata('user_id'),
				);


				if ($schoolid[$i] == '' || $schoolid[$i] == '0')
					$catch = $this->main_mod->insert('user_edu', $row);
				else {
					$row = array(
						'user_id' => $this->session->userdata('user_id'),
						'type' => $type[$i],
						'school' => strtoupper($school[$i]),
						'startdate' => $startdate[$i],
						'enddate' => $enddate[$i],
						'degree' => $degree[$i],
						'mayor' => (isset($mayor[$i]) ? strtoupper($mayor[$i]) : ""),
						'fieldofstudy' => strtoupper($fieldofstudy[$i]),
						'title' => $title[$i],
						'title_prefix' => $title_prefix[$i],
						'score' => $score[$i],
						'activities' => (isset($activities[$i]) ? strtoupper($activities[$i]) : ''),
						'description' => (isset($description_edu[$i]) ? $description_edu[$i] : ''),
						'attachment' => (isset($edu_image_url[$i]) ? $edu_image_url[$i] : ''),
						'status' => 1,
						'modifieddate' => date('Y-m-d H:i:s'),
						'modifiedby' => $this->session->userdata('user_id'),
					);
					$where = array(
						"user_id" => $this->session->userdata('user_id'),
						'id' => $schoolid[$i],
					);
					$catch = $this->main_mod->update('user_edu', $where, $row);
				}

				//$insert = $this->main_mod->insert('user_edu',$row);
				$i++;
			}




			/*
				$row=array(
				'cert_name' => $cert_name,
				'cert_auth' => $cert_auth,
				'lic_num' => $lic_num,
				'cert_url' => $cert_url,
				'startmonth' => $startmonth,
				'startyear' => $startyear,
				'endmonth' => $endmonth,
				'endyear' => $endyear,
				'is_present' => ($is_present=="true"?"1":"0"),
				'description' => $description
				);
				$where = array(
					"user_id" => $this->session->userdata('user_id'),
					"id" => $id,
				);
				$update = $this->main_mod->update('user_cert',$where,$row);
				*/



			$where = array(
				"user_id" => $this->session->userdata('user_id'),
				'status' => 1,
			);
			$row = array(
				'status' => 0,
				'modifieddate' => date('Y-m-d H:i:s'),
				'modifiedby' => $this->session->userdata('user_id'),
			);
			$update = $this->main_mod->update('user_cert', $where, $row);


			$i = 0;
			foreach ($cert_name as $val) {
				$row = array(
					'user_id' => $this->session->userdata('user_id'),
					'cert_name' => strtoupper($cert_name[$i]),
					'cert_auth' => strtoupper($cert_auth[$i]),
					'lic_num' => $lic_num[$i],
					'cert_url' => (isset($cert_url[$i]) ? $cert_url[$i] : ''),
					'cert_title' => (isset($cert_title[$i]) ? $cert_title[$i] : ''),
					'startmonth' => $startmonth[$i],
					'startyear' => $startyear[$i],
					'endmonth' => (isset($endmonth[$i]) ? $endmonth[$i] : ''),
					'endyear' => (isset($endyear[$i]) ? $endyear[$i] : ''),
					'is_present' => ($is_present[$i] == "true" ? "1" : "0"),
					'description' => $description[$i],
					'attachment' => (isset($cert_image_url[$i]) ? $cert_image_url[$i] : ''),
					'createdby' => $this->session->userdata('user_id'),
					'status' => 1,
				);


				if ($certid[$i] == '' || $certid[$i] == '0')
					$catch = $this->main_mod->insert('user_cert', $row);
				else {
					$row = array(
						'user_id' => $this->session->userdata('user_id'),

						'cert_name' => strtoupper($cert_name[$i]),
						'cert_auth' => strtoupper($cert_auth[$i]),
						'lic_num' => $lic_num[$i],
						'cert_url' => (isset($cert_url[$i]) ? $cert_url[$i] : ''),
						'cert_title' => (isset($cert_title[$i]) ? $cert_title[$i] : ''),
						'startmonth' => $startmonth[$i],
						'startyear' => $startyear[$i],
						'endmonth' => (isset($endmonth[$i]) ? $endmonth[$i] : ''),
						'endyear' => (isset($endyear[$i]) ? $endyear[$i] : ''),
						'is_present' => ($is_present[$i] == "true" ? "1" : "0"),
						'description' => $description[$i],
						'attachment' => (isset($cert_image_url[$i]) ? $cert_image_url[$i] : ''),
						'status' => 1,
						'modifieddate' => date('Y-m-d H:i:s'),
						'modifiedby' => $this->session->userdata('user_id'),
					);
					$where = array(
						"user_id" => $this->session->userdata('user_id'),
						'status' => 0,
						'id' => $certid[$i],
					);
					$catch = $this->main_mod->update('user_cert', $where, $row);
				}

				//$insert = $this->main_mod->insert('user_cert',$row);
				$i++;
			}






			echo "valid";
		} catch (Exception $e) {
			//print_r($e);
			echo "not valid";
		}
		//}
		//else







	}

	function del_edu()
	{
		if ($this->session->userdata('user_id') == '') {
			redirect('auth/login');
		}
		$this->load->model('main_mod');
		//EDUCATION
		$id = $this->input->post('id') <> null ? $this->input->post('id') : "";
		if ($id != '') {
			try {
				$check = $this->main_mod->msrwhere('user_edu', array('user_id' => $this->session->userdata('user_id'), 'id' => $id), 'id', 'desc')->result();
				if (isset($check[0])) {
					$update = $this->main_mod->delete('user_edu', 'id', $id);
					//echo 1;
				}
				echo "valid";
			} catch (Exception $e) {
				//print_r($e);
				echo "not valid";
			}
		} else
			echo "not valid";
	}

	function add_cert()
	{
		if ($this->session->userdata('user_id') == '') {
			redirect('auth/login');
		}
		$this->load->model('main_mod');
		//CERTIFICATIONS
		$cert_name = $this->input->post('certname') <> null ? $this->input->post('certname') : "";
		$cert_auth = $this->input->post('certauth') <> null ? $this->input->post('certauth') : "";
		$lic_num = $this->input->post('lic') <> null ? $this->input->post('lic') : "";
		$cert_url = $this->input->post('url') <> null ? $this->input->post('url') : "";
		$startmonth = $this->input->post('certdate') <> null ? $this->input->post('certdate') : "";
		$startyear = $this->input->post('certyear') <> null ? $this->input->post('certyear') : "";
		$endmonth = $this->input->post('certdate2') <> null ? $this->input->post('certdate2') : "";
		$endyear = $this->input->post('certyear2') <> null ? $this->input->post('certyear2') : "";
		$is_present = $this->input->post('certwork') <> null ? $this->input->post('certwork') : "";
		$description = $this->input->post('certdesc') <> null ? $this->input->post('certdesc') : "";
		if ($cert_name != '') {
			try {
				$row = array(
					'user_id' => $this->session->userdata('user_id'),
					'cert_name' => $cert_name,
					'cert_auth' => $cert_auth,
					'lic_num' => $lic_num,
					'cert_url' => $cert_url,
					'startmonth' => $startmonth,
					'startyear' => $startyear,
					'endmonth' => $endmonth,
					'endyear' => $endyear,
					'is_present' => ($is_present == "true" ? "1" : "0"),
					'description' => $description
				);
				$insert = $this->main_mod->insert('user_cert', $row);
				echo $insert;
			} catch (Exception $e) {
				print_r($e);
			}
		} else
			echo "not valid";
	}

	function edit_cert()
	{
		if ($this->session->userdata('user_id') == '') {
			redirect('auth/login');
		}
		$this->load->model('main_mod');
		//CERTIFICATIONS
		$cert_name = $this->input->post('certname') <> null ? $this->input->post('certname') : "";
		$cert_auth = $this->input->post('certauth') <> null ? $this->input->post('certauth') : "";
		$lic_num = $this->input->post('lic') <> null ? $this->input->post('lic') : "";
		$cert_url = $this->input->post('url') <> null ? $this->input->post('url') : "";
		$startmonth = $this->input->post('certdate') <> null ? $this->input->post('certdate') : "";
		$startyear = $this->input->post('certyear') <> null ? $this->input->post('certyear') : "";
		$endmonth = $this->input->post('certdate2') <> null ? $this->input->post('certdate2') : "";
		$endyear = $this->input->post('certyear2') <> null ? $this->input->post('certyear2') : "";
		$is_present = $this->input->post('certwork') <> null ? $this->input->post('certwork') : "";
		$description = $this->input->post('certdesc') <> null ? $this->input->post('certdesc') : "";
		$id = $this->input->post('id') <> null ? $this->input->post('id') : "";
		if ($cert_name != '') {
			try {
				$row = array(
					'cert_name' => $cert_name,
					'cert_auth' => $cert_auth,
					'lic_num' => $lic_num,
					'cert_url' => $cert_url,
					'startmonth' => $startmonth,
					'startyear' => $startyear,
					'endmonth' => $endmonth,
					'endyear' => $endyear,
					'is_present' => ($is_present == "true" ? "1" : "0"),
					'description' => $description
				);
				$where = array(
					"user_id" => $this->session->userdata('user_id'),
					"id" => $id,
					"status" => 1,
				);
				$update = $this->main_mod->update('user_cert', $where, $row);

				echo 1;
			} catch (Exception $e) {
				print_r($e);
			}
		} else
			echo "not valid";
	}

	function del_cert()
	{
		if ($this->session->userdata('user_id') == '') {
			redirect('auth/login');
		}
		$this->load->model('main_mod');
		//CERTIFICATIONS
		$id = $this->input->post('id') <> null ? $this->input->post('id') : "";
		if ($id != '') {
			try {
				$check = $this->main_mod->msrwhere('user_cert', array('user_id' => $this->session->userdata('user_id'), 'id' => $id, 'status' => 1), 'id', 'desc')->result();
				if (isset($check[0])) {
					$update = $this->main_mod->delete('user_cert', 'id', $id);
					//echo 1;
				}
				echo "valid";
			} catch (Exception $e) {
				//print_r($e);
				echo "not valid";
			}
		} else
			echo "not valid";
	}

	function add_org()
	{
		if ($this->session->userdata('user_id') == '') {
			redirect('auth/login');
		}
		$this->load->model('main_mod');
		//ORGANIZATIONS
		$organization = $this->input->post('org') <> null ? $this->input->post('org') : "";
		$jenis = $this->input->post('jenis') <> null ? $this->input->post('jenis') : "";
		$position = $this->input->post('posit') <> null ? $this->input->post('posit') : "";
		$tingkat = $this->input->post('tingkat') <> null ? $this->input->post('tingkat') : "";
		$lingkup = $this->input->post('lingkup') <> null ? $this->input->post('lingkup') : "";
		$occupation = $this->input->post('occ') <> null ? $this->input->post('occ') : "";
		$neg = $this->input->post('neg') <> null ? $this->input->post('neg') : "";
		$pro = $this->input->post('pro') <> null ? $this->input->post('pro') : "";
		$startmonth = $this->input->post('orgdate') <> null ? $this->input->post('orgdate') : "";
		$startyear = $this->input->post('orgyear') <> null ? $this->input->post('orgyear') : "";
		$endmonth = $this->input->post('orgdate2') <> null ? $this->input->post('orgdate2') : "";
		$endyear = $this->input->post('orgyear2') <> null ? $this->input->post('orgyear2') : "";
		$is_present = $this->input->post('orgwork') <> null ? $this->input->post('orgwork') : "";
		$description = $this->input->post('orgdesc') <> null ? $this->input->post('orgdesc') : "";
		if ($organization != '') {
			try {

				$url_image = '';

				if (isset($_FILES['attachment']['name'])) {
					$valid_formats_img = array("jpg", "jpeg", "gif", "png", "pdf", "bmp");
					$name = $_FILES['attachment']['name'];
					$size = $_FILES['attachment']['size'];

					$extx = pathinfo($name, PATHINFO_EXTENSION);

					if (strlen($name)) {
						//list($txt, $ext) = explode(".", $name);
						if (in_array(strtolower($extx), $valid_formats_img)) {
							if ($size < (710000)) {
								//$actual_image_name = time().substr(str_replace(" ", "_", $txt), 5).".".$ext;
								$actual_image_name = time() . "_ORG_" . $this->session->userdata('user_id') . "." . $extx;

								$config['upload_path'] = './assets/uploads/';
								$config['allowed_types'] = '*';
								$config['max_size']	= '710';
								$config['file_name'] = $actual_image_name;

								$this->load->library('upload', $config);

								if ($this->upload->do_upload('attachment')) {
									$url_image = $actual_image_name;
								} else
									echo "<span style:'color:red'>Format file yang diijinkan (gif|jpg|png|jpeg|pdf|bmp).</span>";
							} else
								echo "<span style:'color:red'>Sorry, maximum file size should be 700 KB</span>";
						} else
							echo "<span style:'color:red'>Format file yang diijinkan (gif|jpg|png|jpeg|pdf|bmp).</span>";
					} else
						echo "<span style:'color:red'>Please select an image (jpg, gif, png, bmp file) or PDF file.</span>";
				}



				$row = array(
					'user_id' => $this->session->userdata('user_id'),
					'organization' => $organization,
					'jenis' => $jenis,
					'position' => $position,
					'tingkat' => $tingkat,
					'lingkup' => $lingkup,
					'occupation' => $occupation,
					'negara' => $neg,
					'provinsi' => $pro,
					'startmonth' => $startmonth,
					'startyear' => $startyear,
					'endmonth' => $endmonth,
					'endyear' => $endyear,
					'is_present' => ($is_present == "true" ? "1" : "0"),
					'description' => $description,
					'attachment' => $url_image,
					'createdby' => $this->session->userdata('user_id')
				);
				$insert = $this->main_mod->insert('user_org', $row);
				echo $insert;
			} catch (Exception $e) {
				print_r($e);
			}
		} else
			echo "not valid";
	}

	function org()
	{
		if ($this->session->userdata('user_id') == '') {
			redirect('auth/login');
		}
		$this->load->model('main_mod');
		$this->load->model('members_model');
		$data['title'] = 'PII | Organisasi';
		$id = $this->session->userdata('user_id');

		$obj_row = $this->members_model->get_member_by_id($id);
		$data['row'] = $obj_row;
		$data['idmember'] = $id;
		$data['user_org'] = $this->main_mod->msrwhere('user_org', array('user_id' => $id, 'status' => 1), 'id', 'asc')->result();



		/*$data['user_email']=$this->main_mod->msrwhere('contacts',array('user_id'=>$id,'contact_type like "%_email%"'=>null,'status'=>1),'id','asc')->result();		
		$data['user_phone']=$this->main_mod->msrwhere('contacts',array('user_id'=>$id,'contact_type like "%_phone%"'=>null,'status'=>1),'id','asc')->result();		
		$data['user_edu']=$this->main_mod->msrwhere('user_edu',array('user_id'=>$id,'status'=>1),'id','asc')->result();
		$data['user_cert']=$this->main_mod->msrwhere('user_cert',array('user_id'=>$id,'status'=>1),'id','asc')->result();
		$data['user_exp']=$this->main_mod->msrwhere('user_exp',array('user_id'=>$id,'status'=>1),'id','asc')->result();
		
		$data['m_phone']=$this->main_mod->msrwhere('m_param',array('code'=>'phone'),'id','asc')->result();
		$data['m_email']=$this->main_mod->msrwhere('m_param',array('code'=>'email'),'id','asc')->result();
		$data['m_address']=$this->main_mod->msrwhere('m_param',array('code'=>'address'),'id','asc')->result();
		$data['m_degree']=$this->main_mod->msrwhere('education_type',null,'SEQ','asc')->result();
		*/
		$data['emailx'] = $this->session->userdata('email');
		$this->load->view('member/org_view', $data);
	}

	function edit_org()
	{
		if ($this->session->userdata('user_id') == '') {
			redirect('auth/login');
		}
		$this->load->model('main_mod');
		//ORGANIZATIONS
		$organization = $this->input->post('org') <> null ? $this->input->post('org') : "";
		$jenis = $this->input->post('jenis') <> null ? $this->input->post('jenis') : "";
		$position = $this->input->post('posit') <> null ? $this->input->post('posit') : "";
		$tingkat = $this->input->post('tingkat') <> null ? $this->input->post('tingkat') : "";
		$lingkup = $this->input->post('lingkup') <> null ? $this->input->post('lingkup') : "";
		$occupation = $this->input->post('occ') <> null ? $this->input->post('occ') : "";
		$neg = $this->input->post('neg') <> null ? $this->input->post('neg') : "";
		$pro = $this->input->post('pro') <> null ? $this->input->post('pro') : "";
		$startmonth = $this->input->post('orgdate') <> null ? $this->input->post('orgdate') : "";
		$startyear = $this->input->post('orgyear') <> null ? $this->input->post('orgyear') : "";
		$endmonth = $this->input->post('orgdate2') <> null ? $this->input->post('orgdate2') : "";
		$endyear = $this->input->post('orgyear2') <> null ? $this->input->post('orgyear2') : "";
		$is_present = $this->input->post('orgwork') <> null ? $this->input->post('orgwork') : "";
		$description = $this->input->post('orgdesc') <> null ? $this->input->post('orgdesc') : "";
		$id = $this->input->post('id') <> null ? $this->input->post('id') : "";
		if ($organization != '') {

			$url_image = '';

			if (isset($_FILES['attachment']['name'])) {
				$valid_formats_img = array("jpg", "jpeg", "gif", "png", "pdf", "bmp");
				$name = $_FILES['attachment']['name'];
				$size = $_FILES['attachment']['size'];

				$extx = pathinfo($name, PATHINFO_EXTENSION);

				if (strlen($name)) {
					//list($txt, $ext) = explode(".", $name);
					if (in_array(strtolower($extx), $valid_formats_img)) {
						if ($size < (710000)) {
							//$actual_image_name = time().substr(str_replace(" ", "_", $txt), 5).".".$ext;
							$actual_image_name = time() . "_ORG_" . $this->session->userdata('user_id') . "." . $extx;

							$config['upload_path'] = './assets/uploads/';
							$config['allowed_types'] = '*';
							$config['max_size']	= '710';
							$config['file_name'] = $actual_image_name;

							$this->load->library('upload', $config);

							if ($this->upload->do_upload('attachment')) {
								$url_image = $actual_image_name;
							} else
								echo "<span style:'color:red'>Format file yang diijinkan (gif|jpg|png|jpeg|pdf|bmp).</span>";
						} else
							echo "<span style:'color:red'>Sorry, maximum file size should be 700 KB</span>";
					} else
						echo "<span style:'color:red'>Format file yang diijinkan (gif|jpg|png|jpeg|pdf|bmp).</span>";
				} else
					echo "<span style:'color:red'>Please select an image (jpg, gif, png, bmp file) or PDF file.</span>";
			}

			try {
				if (isset($_FILES['attachment']['name'])) {
					$row = array(
						'organization' => $organization,
						'jenis' => $jenis,
						'position' => $position,
						'tingkat' => $tingkat,
						'lingkup' => $lingkup,
						'occupation' => $occupation,
						'negara' => $neg,
						'provinsi' => $pro,
						'startmonth' => $startmonth,
						'startyear' => $startyear,
						'endmonth' => $endmonth,
						'endyear' => $endyear,
						'is_present' => ($is_present == "true" ? "1" : "0"),
						'description' => $description,
						'attachment' => $url_image,
						'modifiedby' => $this->session->userdata('user_id'),
						'modifieddate' => date('Y-m-d h:i:s')
					);
					$where = array(
						"user_id" => $this->session->userdata('user_id'),
						"id" => $id,
					);
					$update = $this->main_mod->update('user_org', $where, $row);
				} else {
					$row = array(
						'organization' => $organization,
						'jenis' => $jenis,
						'position' => $position,
						'tingkat' => $tingkat,
						'lingkup' => $lingkup,
						'occupation' => $occupation,
						'negara' => $neg,
						'provinsi' => $pro,
						'startmonth' => $startmonth,
						'startyear' => $startyear,
						'endmonth' => $endmonth,
						'endyear' => $endyear,
						'is_present' => ($is_present == "true" ? "1" : "0"),
						'description' => $description,
						'modifiedby' => $this->session->userdata('user_id'),
						'modifieddate' => date('Y-m-d h:i:s')
					);
					$where = array(
						"user_id" => $this->session->userdata('user_id'),
						"id" => $id,
					);
					$update = $this->main_mod->update('user_org', $where, $row);
				}
				echo 1;
			} catch (Exception $e) {
				print_r($e);
			}
		} else
			echo "not valid";
	}

	function del_org()
	{
		if ($this->session->userdata('user_id') == '') {
			redirect('auth/login');
		}
		$this->load->model('main_mod');
		//ORGANIZATIONS
		$id = $this->input->post('id') <> null ? $this->input->post('id') : "";
		if ($id != '') {
			try {
				$check = $this->main_mod->msrwhere('user_org', array('user_id' => $this->session->userdata('user_id'), 'id' => $id), 'id', 'desc')->result();
				if (isset($check[0])) {
					$update = $this->main_mod->delete('user_org', 'id', $id);
					echo 1;
				}
			} catch (Exception $e) {
				print_r($e);
			}
		} else
			echo "not valid";
	}

	function get_file()
	{
		if ($this->session->userdata('user_id') == '') {
			redirect('auth/login');
		}
		$this->load->model('main_mod');
		//ORGANIZATIONS
		$id = $this->input->get('id') <> null ? $this->input->get('id') : "";
		$type = $this->input->get('type') <> null ? $this->input->get('type') : "";
		$user_org = $this->main_mod->msrwhere($type, array('user_id' => $this->session->userdata('user_id'), 'id' => $id, 'status' => 1), 'id', 'asc')->result();

		if (isset($user_org[0]->attachment)) {
			$this->load->helper('download');
			force_download($user_org[0]->attachment, file_get_contents(base_url() . "assets/uploads/" . $user_org[0]->attachment));
		}
	}

	function award()
	{
		if ($this->session->userdata('user_id') == '') {
			redirect('auth/login');
		}
		$this->load->model('main_mod');
		$this->load->model('members_model');
		$data['title'] = 'PII | Penghargaan';
		$id = $this->session->userdata('user_id');

		$obj_row = $this->members_model->get_member_by_id($id);
		$data['row'] = $obj_row;
		$data['idmember'] = $id;
		$data['user_award'] = $this->main_mod->msrwhere('user_award', array('user_id' => $id, 'status' => 1), 'id', 'asc')->result();

		$data['emailx'] = $this->session->userdata('email');
		$this->load->view('member/award_view', $data);
	}

	function add_award()
	{
		if ($this->session->userdata('user_id') == '') {
			redirect('auth/login');
		}
		$this->load->model('main_mod');
		//AWARD
		$name = $this->input->post('awardname') <> null ? $this->input->post('awardname') : "";
		$issue = $this->input->post('issue') <> null ? $this->input->post('issue') : "";
		$occupation = $this->input->post('occ') <> null ? $this->input->post('occ') : "";
		$neg = $this->input->post('neg') <> null ? $this->input->post('neg') : "";
		$pro = $this->input->post('pro') <> null ? $this->input->post('pro') : "";
		$tingkat = $this->input->post('tingkat') <> null ? $this->input->post('tingkat') : "";
		$pemberi = $this->input->post('pemberi') <> null ? $this->input->post('pemberi') : "";
		$startmonth = $this->input->post('startmonth') <> null ? $this->input->post('startmonth') : "";
		$startyear = $this->input->post('startyear') <> null ? $this->input->post('startyear') : "";
		$description = $this->input->post('awarddesc') <> null ? $this->input->post('awarddesc') : "";
		if ($name != '') {
			try {

				$url_image = '';

				if (isset($_FILES['attachment']['name'])) {
					$valid_formats_img = array("jpg", "jpeg", "gif", "png", "pdf", "bmp");
					$namex = $_FILES['attachment']['name'];
					$size = $_FILES['attachment']['size'];

					$extx = pathinfo($namex, PATHINFO_EXTENSION);

					if (strlen($namex)) {
						//list($txt, $ext) = explode(".", $name);
						if (in_array(strtolower($extx), $valid_formats_img)) {
							if ($size < (710000)) {
								//$actual_image_name = time().substr(str_replace(" ", "_", $txt), 5).".".$ext;
								$actual_image_name = time() . "_AWD_" . $this->session->userdata('user_id') . "." . $extx;

								$config['upload_path'] = './assets/uploads/';
								$config['allowed_types'] = '*';
								$config['max_size']	= '710';
								$config['file_name'] = $actual_image_name;

								$this->load->library('upload', $config);

								if ($this->upload->do_upload('attachment')) {
									$url_image = $actual_image_name;
								} else
									echo "<span style:'color:red'>Format file yang diijinkan (gif|jpg|png|jpeg|pdf|bmp).</span>";
							} else
								echo "<span style:'color:red'>Sorry, maximum file size should be 700 KB</span>";
						} else
							echo "<span style:'color:red'>Format file yang diijinkan (gif|jpg|png|jpeg|pdf|bmp).</span>";
					} else
						echo "<span style:'color:red'>Please select an image (jpg, gif, png, bmp file) or PDF file.</span>";
				}



				$row = array(
					'user_id' => $this->session->userdata('user_id'),
					'name' => $name,
					'issue' => $issue,

					'location' => $occupation,
					'provinsi' => $pro,
					'negara' => $neg,
					'pemberi' => $pemberi,
					'tingkat' => $tingkat,

					'startmonth' => $startmonth,
					'startyear' => $startyear,
					'description' => $description,
					'attachment' => $url_image,
					'createdby' => $this->session->userdata('user_id')
				);
				$insert = $this->main_mod->insert('user_award', $row);
				echo $insert;
			} catch (Exception $e) {
				print_r($e);
			}
		} else
			echo "not valid";
	}

	function edit_award()
	{
		if ($this->session->userdata('user_id') == '') {
			redirect('auth/login');
		}
		$this->load->model('main_mod');
		//AWARD
		$name = $this->input->post('awardname') <> null ? $this->input->post('awardname') : "";
		$issue = $this->input->post('issue') <> null ? $this->input->post('issue') : "";
		$occupation = $this->input->post('occ') <> null ? $this->input->post('occ') : "";
		$neg = $this->input->post('neg') <> null ? $this->input->post('neg') : "";
		$pro = $this->input->post('pro') <> null ? $this->input->post('pro') : "";
		$tingkat = $this->input->post('tingkat') <> null ? $this->input->post('tingkat') : "";
		$pemberi = $this->input->post('pemberi') <> null ? $this->input->post('pemberi') : "";
		$startmonth = $this->input->post('startmonth') <> null ? $this->input->post('startmonth') : "";
		$startyear = $this->input->post('startyear') <> null ? $this->input->post('startyear') : "";
		$description = $this->input->post('awarddesc') <> null ? $this->input->post('awarddesc') : "";
		$id = $this->input->post('id') <> null ? $this->input->post('id') : "";
		if ($name != '') {


			$url_image = '';

			if (isset($_FILES['attachment']['name'])) {
				$valid_formats_img = array("jpg", "jpeg", "gif", "png", "pdf", "bmp");
				$namex = $_FILES['attachment']['name'];
				$size = $_FILES['attachment']['size'];

				$extx = pathinfo($namex, PATHINFO_EXTENSION);

				if (strlen($namex)) {
					//list($txt, $ext) = explode(".", $name);
					if (in_array(strtolower($extx), $valid_formats_img)) {
						if ($size < (710000)) {
							//$actual_image_name = time().substr(str_replace(" ", "_", $txt), 5).".".$ext;
							$actual_image_name = time() . "_AWD_" . $this->session->userdata('user_id') . "." . $extx;

							$config['upload_path'] = './assets/uploads/';
							$config['allowed_types'] = '*';
							$config['max_size']	= '710';
							$config['file_name'] = $actual_image_name;

							$this->load->library('upload', $config);

							if ($this->upload->do_upload('attachment')) {
								$url_image = $actual_image_name;
							} else
								echo "<span style:'color:red'>Format file yang diijinkan (gif|jpg|png|jpeg|pdf|bmp).</span>";
						} else
							echo "<span style:'color:red'>Sorry, maximum file size should be 700 KB</span>";
					} else
						echo "<span style:'color:red'>Format file yang diijinkan (gif|jpg|png|jpeg|pdf|bmp).</span>";
				} else
					echo "<span style:'color:red'>Please select an image (jpg, gif, png, bmp file) or PDF file.</span>";
			}


			try {
				if (isset($_FILES['attachment']['name'])) {
					$row = array(
						'name' => $name,
						'issue' => $issue,
						'location' => $occupation,
						'provinsi' => $pro,
						'negara' => $neg,
						'pemberi' => $pemberi,
						'tingkat' => $tingkat,
						'startmonth' => $startmonth,
						'startyear' => $startyear,
						'description' => $description,
						'attachment' => $url_image,
						'modifiedby' => $this->session->userdata('user_id'),
						'modifieddate' => date('Y-m-d h:i:s')
					);
					$where = array(
						"user_id" => $this->session->userdata('user_id'),
						"id" => $id,
					);
					$update = $this->main_mod->update('user_award', $where, $row);
				} else {
					$row = array(
						'name' => $name,
						'issue' => $issue,
						'location' => $occupation,
						'provinsi' => $pro,
						'negara' => $neg,
						'pemberi' => $pemberi,
						'tingkat' => $tingkat,
						'startmonth' => $startmonth,
						'startyear' => $startyear,
						'description' => $description,
						'modifiedby' => $this->session->userdata('user_id'),
						'modifieddate' => date('Y-m-d h:i:s')
					);
					$where = array(
						"user_id" => $this->session->userdata('user_id'),
						"id" => $id,
					);
					$update = $this->main_mod->update('user_award', $where, $row);
				}


				echo 1;
			} catch (Exception $e) {
				print_r($e);
			}
		} else
			echo "not valid";
	}

	function del_award()
	{
		if ($this->session->userdata('user_id') == '') {
			redirect('auth/login');
		}
		$this->load->model('main_mod');
		//AWARD
		$id = $this->input->post('id') <> null ? $this->input->post('id') : "";
		if ($id != '') {
			try {
				$check = $this->main_mod->msrwhere('user_award', array('user_id' => $this->session->userdata('user_id'), 'id' => $id), 'id', 'desc')->result();
				if (isset($check[0])) {
					$update = $this->main_mod->delete('user_award', 'id', $id);
					echo 1;
				}
			} catch (Exception $e) {
				print_r($e);
			}
		} else
			echo "not valid";
	}

	function course()
	{
		if ($this->session->userdata('user_id') == '') {
			redirect('auth/login');
		}
		$this->load->model('main_mod');
		$this->load->model('members_model');
		$data['title'] = 'PII | Kursus';
		$id = $this->session->userdata('user_id');

		$obj_row = $this->members_model->get_member_by_id($id);
		$data['row'] = $obj_row;
		$data['idmember'] = $id;
		$data['user_course'] = $this->main_mod->msrwhere('user_course', array('user_id' => $id, 'status' => 1), 'id', 'asc')->result();

		$data['emailx'] = $this->session->userdata('email');
		$this->load->view('member/course_view', $data);
	}

	function add_course()
	{
		if ($this->session->userdata('user_id') == '') {
			redirect('auth/login');
		}
		$this->load->model('main_mod');
		//COURSE
		$coursename = $this->input->post('coursename') <> null ? $this->input->post('coursename') : "";
		$hour = $this->input->post('hour') <> null ? $this->input->post('hour') : "";
		$occupation = $this->input->post('occ') <> null ? $this->input->post('occ') : "";
		$neg = $this->input->post('neg') <> null ? $this->input->post('neg') : "";
		$pro = $this->input->post('pro') <> null ? $this->input->post('pro') : "";
		$tingkat = $this->input->post('tingkat') <> null ? $this->input->post('tingkat') : "";
		$type = $this->input->post('type') <> null ? $this->input->post('type') : "";
		$courseorg = $this->input->post('courseorg') <> null ? $this->input->post('courseorg') : "";
		$startmonth = $this->input->post('courseperiod') <> null ? $this->input->post('courseperiod') : "";
		$startyear = $this->input->post('courseyear') <> null ? $this->input->post('courseyear') : "";
		$endmonth = $this->input->post('courseperiod2') <> null ? $this->input->post('courseperiod2') : "";
		$endyear = $this->input->post('courseyear2') <> null ? $this->input->post('courseyear2') : "";
		if ($coursename != '') {

			$url_image = '';

			if (isset($_FILES['attachment']['name'])) {
				$valid_formats_img = array("jpg", "jpeg", "gif", "png", "pdf", "bmp");
				$namex = $_FILES['attachment']['name'];
				$size = $_FILES['attachment']['size'];

				$extx = pathinfo($namex, PATHINFO_EXTENSION);

				if (strlen($namex)) {
					//list($txt, $ext) = explode(".", $name);
					if (in_array(strtolower($extx), $valid_formats_img)) {
						if ($size < (710000)) {
							//$actual_image_name = time().substr(str_replace(" ", "_", $txt), 5).".".$ext;
							$actual_image_name = time() . "_CRS_" . $this->session->userdata('user_id') . "." . $extx;

							$config['upload_path'] = './assets/uploads/';
							$config['allowed_types'] = '*';
							$config['max_size']	= '710';
							$config['file_name'] = $actual_image_name;

							$this->load->library('upload', $config);

							if ($this->upload->do_upload('attachment')) {
								$url_image = $actual_image_name;
							} else
								echo "<span style:'color:red'>Format file yang diijinkan (gif|jpg|png|jpeg|pdf|bmp).</span>";
						} else
							echo "<span style:'color:red'>Sorry, maximum file size should be 700 KB</span>";
					} else
						echo "<span style:'color:red'>Format file yang diijinkan (gif|jpg|png|jpeg|pdf|bmp).</span>";
				} else
					echo "<span style:'color:red'>Please select an image (jpg, gif, png, bmp file) or PDF file.</span>";
			}


			try {
				$row = array(
					'user_id' => $this->session->userdata('user_id'),
					'coursename' => $coursename,
					'hour' => $hour,
					'location' => $occupation,
					'provinsi' => $pro,
					'negara' => $neg,
					'type' => $type,
					'tingkat' => $tingkat,
					'courseorg' => $courseorg,
					'startmonth' => $startmonth,
					'startyear' => $startyear,
					'endmonth' => $endmonth,
					'endyear' => $endyear,
					'attachment' => $url_image,
					'createdby' => $this->session->userdata('user_id')
				);
				$insert = $this->main_mod->insert('user_course', $row);
				echo $insert;
			} catch (Exception $e) {
				print_r($e);
			}
		} else
			echo "not valid";
	}

	function edit_course()
	{
		if ($this->session->userdata('user_id') == '') {
			redirect('auth/login');
		}
		$this->load->model('main_mod');
		//COURSE
		$coursename = $this->input->post('coursename') <> null ? $this->input->post('coursename') : "";
		$hour = $this->input->post('hour') <> null ? $this->input->post('hour') : "";
		$occupation = $this->input->post('occ') <> null ? $this->input->post('occ') : "";
		$neg = $this->input->post('neg') <> null ? $this->input->post('neg') : "";
		$pro = $this->input->post('pro') <> null ? $this->input->post('pro') : "";
		$tingkat = $this->input->post('tingkat') <> null ? $this->input->post('tingkat') : "";
		$type = $this->input->post('type') <> null ? $this->input->post('type') : "";
		$courseorg = $this->input->post('courseorg') <> null ? $this->input->post('courseorg') : "";
		$startmonth = $this->input->post('courseperiod') <> null ? $this->input->post('courseperiod') : "";
		$startyear = $this->input->post('courseyear') <> null ? $this->input->post('courseyear') : "";
		$endmonth = $this->input->post('courseperiod2') <> null ? $this->input->post('courseperiod2') : "";
		$endyear = $this->input->post('courseyear2') <> null ? $this->input->post('courseyear2') : "";

		$id = $this->input->post('id') <> null ? $this->input->post('id') : "";
		if ($coursename != '') {

			$url_image = '';

			if (isset($_FILES['attachment']['name'])) {
				$valid_formats_img = array("jpg", "jpeg", "gif", "png", "pdf", "bmp");
				$namex = $_FILES['attachment']['name'];
				$size = $_FILES['attachment']['size'];

				$extx = pathinfo($namex, PATHINFO_EXTENSION);

				if (strlen($namex)) {
					//list($txt, $ext) = explode(".", $name);
					if (in_array(strtolower($extx), $valid_formats_img)) {
						if ($size < (710000)) {
							//$actual_image_name = time().substr(str_replace(" ", "_", $txt), 5).".".$ext;
							$actual_image_name = time() . "_CRS_" . $this->session->userdata('user_id') . "." . $extx;

							$config['upload_path'] = './assets/uploads/';
							$config['allowed_types'] = '*';
							$config['max_size']	= '710';
							$config['file_name'] = $actual_image_name;

							$this->load->library('upload', $config);

							if ($this->upload->do_upload('attachment')) {
								$url_image = $actual_image_name;
							} else
								echo "<span style:'color:red'>Format file yang diijinkan (gif|jpg|png|jpeg|pdf|bmp).</span>";
						} else
							echo "<span style:'color:red'>Sorry, maximum file size should be 700 KB</span>";
					} else
						echo "<span style:'color:red'>Format file yang diijinkan (gif|jpg|png|jpeg|pdf|bmp).</span>";
				} else
					echo "<span style:'color:red'>Please select an image (jpg, gif, png, bmp file) or PDF file.</span>";
			}


			try {
				if (isset($_FILES['attachment']['name'])) {
					$row = array(
						'coursename' => $coursename,
						'hour' => $hour,
						'location' => $occupation,
						'provinsi' => $pro,
						'negara' => $neg,
						'type' => $type,
						'tingkat' => $tingkat,
						'courseorg' => $courseorg,
						'startmonth' => $startmonth,
						'startyear' => $startyear,
						'endmonth' => $endmonth,
						'endyear' => $endyear,
						'attachment' => $url_image,
						'modifiedby' => $this->session->userdata('user_id'),
						'modifieddate' => date('Y-m-d h:i:s')
					);
					$where = array(
						"user_id" => $this->session->userdata('user_id'),
						"id" => $id,
					);
					$update = $this->main_mod->update('user_course', $where, $row);
				} else {
					$row = array(
						'coursename' => $coursename,
						'hour' => $hour,
						'location' => $occupation,
						'provinsi' => $pro,
						'negara' => $neg,
						'type' => $type,
						'tingkat' => $tingkat,
						'courseorg' => $courseorg,
						'startmonth' => $startmonth,
						'startyear' => $startyear,
						'endmonth' => $endmonth,
						'endyear' => $endyear,
						'modifiedby' => $this->session->userdata('user_id'),
						'modifieddate' => date('Y-m-d h:i:s')
					);
					$where = array(
						"user_id" => $this->session->userdata('user_id'),
						"id" => $id,
					);
					$update = $this->main_mod->update('user_course', $where, $row);
				}


				echo 1;
			} catch (Exception $e) {
				print_r($e);
			}
		} else
			echo "not valid";
	}

	function del_course()
	{
		if ($this->session->userdata('user_id') == '') {
			redirect('auth/login');
		}
		$this->load->model('main_mod');
		//COURSE
		$id = $this->input->post('id') <> null ? $this->input->post('id') : "";
		if ($id != '') {
			try {
				$check = $this->main_mod->msrwhere('user_course', array('user_id' => $this->session->userdata('user_id'), 'id' => $id), 'id', 'desc')->result();
				if (isset($check[0])) {
					$update = $this->main_mod->delete('user_course', 'id', $id);
					echo 1;
				}
			} catch (Exception $e) {
				print_r($e);
			}
		} else
			echo "not valid";
	}

	function prof()
	{
		if ($this->session->userdata('user_id') == '') {
			redirect('auth/login');
		}
		$this->load->model('main_mod');
		$this->load->model('members_model');
		$data['title'] = 'PII | Kualifikasi Profesional';
		$id = $this->session->userdata('user_id');

		$obj_row = $this->members_model->get_member_by_id($id);
		$data['row'] = $obj_row;
		$data['idmember'] = $id;
		$data['user_prof'] = $this->main_mod->msrwhere('user_prof', array('user_id' => $id, 'status' => 1), 'id', 'asc')->result();

		$data['m_proftype'] = $this->main_mod->msr('m_proftype', 'id', 'asc')->result();

		$data['emailx'] = $this->session->userdata('email');
		$this->load->view('member/prof_view', $data);
	}

	function add_prof()
	{
		if ($this->session->userdata('user_id') == '') {
			redirect('auth/login');
		}
		$this->load->model('main_mod');
		//PROFESIONAL
		$organization = $this->input->post('proforg') <> null ? $this->input->post('proforg') : "";
		//$type = $this->input->post('proftype')<>null?$this->input->post('proftype'):"";

		$occupation = $this->input->post('occ') <> null ? $this->input->post('occ') : "";
		$neg = $this->input->post('neg') <> null ? $this->input->post('neg') : "";
		$pro = $this->input->post('pro') <> null ? $this->input->post('pro') : "";
		$tingkat = $this->input->post('tingkat') <> null ? $this->input->post('tingkat') : "";
		$pemberi = $this->input->post('pemberi') <> null ? $this->input->post('pemberi') : "";

		$position = $this->input->post('profposit') <> null ? $this->input->post('profposit') : "";
		$startmonth = $this->input->post('profperiod') <> null ? $this->input->post('profperiod') : "";
		$startyear = $this->input->post('profyear') <> null ? $this->input->post('profyear') : "";
		$endmonth = $this->input->post('profperiod2') <> null ? $this->input->post('profperiod2') : "";
		$endyear = $this->input->post('profyear2') <> null ? $this->input->post('profyear2') : "";
		$subject = $this->input->post('profsubject') <> null ? $this->input->post('profsubject') : "";
		$description = $this->input->post('profdesc') <> null ? $this->input->post('profdesc') : "";
		if ($organization != '') {
			try {
				$row = array(
					'user_id' => $this->session->userdata('user_id'),
					'organization' => $organization,
					//'type' => $type,

					'location' => $occupation,
					'provinsi' => $pro,
					'negara' => $neg,
					'pemberi' => $pemberi,
					'nilaiproyek' => $tingkat,

					'position' => $position,
					'subject' => $subject,
					'description' => $description,
					'startmonth' => $startmonth,
					'startyear' => $startyear,
					'endmonth' => $endmonth,
					'endyear' => $endyear
				);
				$insert = $this->main_mod->insert('user_prof', $row);
				echo $insert;
			} catch (Exception $e) {
				print_r($e);
			}
		} else
			echo "not valid";
	}

	function edit_prof()
	{
		if ($this->session->userdata('user_id') == '') {
			redirect('auth/login');
		}
		$this->load->model('main_mod');
		//PROFESIONAL
		$organization = $this->input->post('proforg') <> null ? $this->input->post('proforg') : "";
		//$type = $this->input->post('proftype')<>null?$this->input->post('proftype'):"";

		$occupation = $this->input->post('occ') <> null ? $this->input->post('occ') : "";
		$neg = $this->input->post('neg') <> null ? $this->input->post('neg') : "";
		$pro = $this->input->post('pro') <> null ? $this->input->post('pro') : "";
		$tingkat = $this->input->post('tingkat') <> null ? $this->input->post('tingkat') : "";
		$pemberi = $this->input->post('pemberi') <> null ? $this->input->post('pemberi') : "";

		$position = $this->input->post('profposit') <> null ? $this->input->post('profposit') : "";
		$startmonth = $this->input->post('profperiod') <> null ? $this->input->post('profperiod') : "";
		$startyear = $this->input->post('profyear') <> null ? $this->input->post('profyear') : "";
		$endmonth = $this->input->post('profperiod2') <> null ? $this->input->post('profperiod2') : "";
		$endyear = $this->input->post('profyear2') <> null ? $this->input->post('profyear2') : "";
		$subject = $this->input->post('profsubject') <> null ? $this->input->post('profsubject') : "";
		$description = $this->input->post('profdesc') <> null ? $this->input->post('profdesc') : "";

		$id = $this->input->post('id') <> null ? $this->input->post('id') : "";
		if ($organization != '') {
			try {
				$row = array(
					'organization' => $organization,
					//'type' => $type,

					'location' => $occupation,
					'provinsi' => $pro,
					'negara' => $neg,
					'pemberi' => $pemberi,
					'nilaiproyek' => $tingkat,

					'position' => $position,
					'subject' => $subject,
					'description' => $description,
					'startmonth' => $startmonth,
					'startyear' => $startyear,
					'endmonth' => $endmonth,
					'endyear' => $endyear
				);
				$where = array(
					"user_id" => $this->session->userdata('user_id'),
					"id" => $id,
				);
				$update = $this->main_mod->update('user_prof', $where, $row);

				echo 1;
			} catch (Exception $e) {
				print_r($e);
			}
		} else
			echo "not valid";
	}

	function del_prof()
	{
		if ($this->session->userdata('user_id') == '') {
			redirect('auth/login');
		}
		$this->load->model('main_mod');
		//PROFESIONAL
		$id = $this->input->post('id') <> null ? $this->input->post('id') : "";
		if ($id != '') {
			try {
				$check = $this->main_mod->msrwhere('user_prof', array('user_id' => $this->session->userdata('user_id'), 'id' => $id), 'id', 'desc')->result();
				if (isset($check[0])) {
					$update = $this->main_mod->delete('user_prof', 'id', $id);
					echo 1;
				}
			} catch (Exception $e) {
				print_r($e);
			}
		} else
			echo "not valid";
	}

	function publication()
	{
		if ($this->session->userdata('user_id') == '') {
			redirect('auth/login');
		}
		$this->load->model('main_mod');
		$this->load->model('members_model');
		$data['title'] = 'PII | Publikasi';
		$id = $this->session->userdata('user_id');

		$obj_row = $this->members_model->get_member_by_id($id);
		$data['row'] = $obj_row;
		$data['idmember'] = $id;
		$data['user_publication'] = $this->main_mod->msrwhere('user_publication', array('user_id' => $id, 'status' => 1), 'id', 'asc')->result();

		$data['m_publicjurnal'] = $this->main_mod->msr('m_publicjurnal', 'id', 'asc')->result();
		$data['m_publictype'] = $this->main_mod->msr('m_publictype', 'id', 'asc')->result();

		$data['emailx'] = $this->session->userdata('email');
		$this->load->view('member/publication_view', $data);
	}

	function add_publication()
	{
		if ($this->session->userdata('user_id') == '') {
			redirect('auth/login');
		}
		$this->load->model('main_mod');
		//PUBLICATION
		$topic = $this->input->post('publicationtopic') <> null ? $this->input->post('publicationtopic') : "";

		$occupation = $this->input->post('occ') <> null ? $this->input->post('occ') : "";
		$neg = $this->input->post('neg') <> null ? $this->input->post('neg') : "";
		$pro = $this->input->post('pro') <> null ? $this->input->post('pro') : "";
		$tingkat = $this->input->post('tingkat') <> null ? $this->input->post('tingkat') : "";
		$tingkatmedia = $this->input->post('tingkatmedia') <> null ? $this->input->post('tingkatmedia') : "";

		$type = $this->input->post('publicationtype') <> null ? $this->input->post('publicationtype') : "";
		$media = $this->input->post('publicationmedia') <> null ? $this->input->post('publicationmedia') : "";
		$startmonth = $this->input->post('publicationperiod') <> null ? $this->input->post('publicationperiod') : "";
		$startyear = $this->input->post('publicationyear') <> null ? $this->input->post('publicationyear') : "";
		$endmonth = $this->input->post('publicationperiod2') <> null ? $this->input->post('publicationperiod2') : "";
		$endyear = $this->input->post('publicationyear2') <> null ? $this->input->post('publicationyear2') : "";
		$journal = $this->input->post('publicationjournal') <> null ? $this->input->post('publicationjournal') : "";
		$event = $this->input->post('publicationevent') <> null ? $this->input->post('publicationevent') : "";
		$description = $this->input->post('publicationdesc') <> null ? $this->input->post('publicationdesc') : "";
		if ($topic != '') {

			$url_image = '';

			if (isset($_FILES['attachment']['name'])) {
				$valid_formats_img = array("jpg", "jpeg", "gif", "png", "pdf", "bmp");
				$namex = $_FILES['attachment']['name'];
				$size = $_FILES['attachment']['size'];

				$extx = pathinfo($namex, PATHINFO_EXTENSION);

				if (strlen($namex)) {
					//list($txt, $ext) = explode(".", $name);
					if (in_array(strtolower($extx), $valid_formats_img)) {
						if ($size < (710000)) {
							//$actual_image_name = time().substr(str_replace(" ", "_", $txt), 5).".".$ext;
							$actual_image_name = time() . "_PBC_" . $this->session->userdata('user_id') . "." . $extx;

							$config['upload_path'] = './assets/uploads/';
							$config['allowed_types'] = '*';
							$config['max_size']	= '710';
							$config['file_name'] = $actual_image_name;

							$this->load->library('upload', $config);

							if ($this->upload->do_upload('attachment')) {
								$url_image = $actual_image_name;
							} else
								echo "<span style:'color:red'>Format file yang diijinkan (gif|jpg|png|jpeg|pdf|bmp).</span>";
						} else
							echo "<span style:'color:red'>Sorry, maximum file size should be 700 KB</span>";
					} else
						echo "<span style:'color:red'>Format file yang diijinkan (gif|jpg|png|jpeg|pdf|bmp).</span>";
				} else
					echo "<span style:'color:red'>Please select an image (jpg, gif, png, bmp file) or PDF file.</span>";
			}


			try {
				$row = array(
					'user_id' => $this->session->userdata('user_id'),
					'topic' => $topic,
					'media' => $media,
					'type' => $type,

					'location' => $occupation,
					'provinsi' => $pro,
					'negara' => $neg,
					'tingkat' => $tingkat,
					'tingkatmedia' => $tingkatmedia,

					'journal' => $journal,
					'event' => $event,
					'description' => $description,
					'startmonth' => $startmonth,
					'startyear' => $startyear,
					'endmonth' => $endmonth,
					'endyear' => $endyear,
					'attachment' => $url_image,
					'createdby' => $this->session->userdata('user_id')
				);
				$insert = $this->main_mod->insert('user_publication', $row);
				echo $insert;
			} catch (Exception $e) {
				print_r($e);
			}
		} else
			echo "not valid";
	}

	function edit_publication()
	{
		if ($this->session->userdata('user_id') == '') {
			redirect('auth/login');
		}
		$this->load->model('main_mod');
		//PUBLICATION
		$topic = $this->input->post('publicationtopic') <> null ? $this->input->post('publicationtopic') : "";


		$occupation = $this->input->post('occ') <> null ? $this->input->post('occ') : "";
		$neg = $this->input->post('neg') <> null ? $this->input->post('neg') : "";
		$pro = $this->input->post('pro') <> null ? $this->input->post('pro') : "";
		$tingkat = $this->input->post('tingkat') <> null ? $this->input->post('tingkat') : "";
		$tingkatmedia = $this->input->post('tingkatmedia') <> null ? $this->input->post('tingkatmedia') : "";

		$type = $this->input->post('publicationtype') <> null ? $this->input->post('publicationtype') : "";
		$media = $this->input->post('publicationmedia') <> null ? $this->input->post('publicationmedia') : "";
		$startmonth = $this->input->post('publicationperiod') <> null ? $this->input->post('publicationperiod') : "";
		$startyear = $this->input->post('publicationyear') <> null ? $this->input->post('publicationyear') : "";
		$endmonth = $this->input->post('publicationperiod2') <> null ? $this->input->post('publicationperiod2') : "";
		$endyear = $this->input->post('publicationyear2') <> null ? $this->input->post('publicationyear2') : "";
		$journal = $this->input->post('publicationjournal') <> null ? $this->input->post('publicationjournal') : "";
		$event = $this->input->post('publicationevent') <> null ? $this->input->post('publicationevent') : "";
		$description = $this->input->post('publicationdesc') <> null ? $this->input->post('publicationdesc') : "";


		$id = $this->input->post('id') <> null ? $this->input->post('id') : "";
		if ($topic != '') {


			$url_image = '';

			if (isset($_FILES['attachment']['name'])) {
				$valid_formats_img = array("jpg", "jpeg", "gif", "png", "pdf", "bmp");
				$namex = $_FILES['attachment']['name'];
				$size = $_FILES['attachment']['size'];

				$extx = pathinfo($namex, PATHINFO_EXTENSION);

				if (strlen($namex)) {
					//list($txt, $ext) = explode(".", $name);
					if (in_array(strtolower($extx), $valid_formats_img)) {
						if ($size < (710000)) {
							//$actual_image_name = time().substr(str_replace(" ", "_", $txt), 5).".".$ext;
							$actual_image_name = time() . "_PBC_" . $this->session->userdata('user_id') . "." . $extx;

							$config['upload_path'] = './assets/uploads/';
							$config['allowed_types'] = '*';
							$config['max_size']	= '710';
							$config['file_name'] = $actual_image_name;

							$this->load->library('upload', $config);

							if ($this->upload->do_upload('attachment')) {
								$url_image = $actual_image_name;
							} else
								echo "<span style:'color:red'>Format file yang diijinkan (gif|jpg|png|jpeg|pdf|bmp).</span>";
						} else
							echo "<span style:'color:red'>Sorry, maximum file size should be 700 KB</span>";
					} else
						echo "<span style:'color:red'>Format file yang diijinkan (gif|jpg|png|jpeg|pdf|bmp).</span>";
				} else
					echo "<span style:'color:red'>Please select an image (jpg, gif, png, bmp file) or PDF file.</span>";
			}


			try {
				if (isset($_FILES['attachment']['name'])) {
					$row = array(
						'topic' => $topic,
						'media' => $media,
						'type' => $type,

						'location' => $occupation,
						'provinsi' => $pro,
						'negara' => $neg,
						'tingkat' => $tingkat,
						'tingkatmedia' => $tingkatmedia,

						'journal' => $journal,
						'event' => $event,
						'description' => $description,
						'startmonth' => $startmonth,
						'startyear' => $startyear,
						'endmonth' => $endmonth,
						'endyear' => $endyear,
						'attachment' => $url_image,
						'modifiedby' => $this->session->userdata('user_id'),
						'modifieddate' => date('Y-m-d h:i:s')
					);
					$where = array(
						"user_id" => $this->session->userdata('user_id'),
						"id" => $id,
					);
					$update = $this->main_mod->update('user_publication', $where, $row);
				} else {
					$row = array(
						'topic' => $topic,
						'media' => $media,
						'type' => $type,

						'location' => $occupation,
						'provinsi' => $pro,
						'negara' => $neg,
						'tingkat' => $tingkat,
						'tingkatmedia' => $tingkatmedia,

						'journal' => $journal,
						'event' => $event,
						'description' => $description,
						'startmonth' => $startmonth,
						'startyear' => $startyear,
						'endmonth' => $endmonth,
						'endyear' => $endyear,
						'modifiedby' => $this->session->userdata('user_id'),
						'modifieddate' => date('Y-m-d h:i:s')
					);
					$where = array(
						"user_id" => $this->session->userdata('user_id'),
						"id" => $id,
					);
					$update = $this->main_mod->update('user_publication', $where, $row);
				}



				echo 1;
			} catch (Exception $e) {
				print_r($e);
			}
		} else
			echo "not valid";
	}

	function del_publication()
	{
		if ($this->session->userdata('user_id') == '') {
			redirect('auth/login');
		}
		$this->load->model('main_mod');
		//PUBLICATION
		$id = $this->input->post('id') <> null ? $this->input->post('id') : "";
		if ($id != '') {
			try {
				$check = $this->main_mod->msrwhere('user_publication', array('user_id' => $this->session->userdata('user_id'), 'id' => $id), 'id', 'desc')->result();
				if (isset($check[0])) {
					$update = $this->main_mod->delete('user_publication', 'id', $id);
					echo 1;
				}
			} catch (Exception $e) {
				print_r($e);
			}
		} else
			echo "not valid";
	}

	public function member_serti()
	{
		if (!$this->session->userdata('user_id')) {
			redirect('auth/login');
		}

		// defaults
		$data = [];
		$data['title'] = 'PII | Sertifikat';
		$id = $this->session->userdata('user_id');

		$nokta = '';
		$nama = '';

		// ambil no_kta dari member berdasarkan user_id
		$member = $this->simpan_model->cari_di_member($id);
		if ($member && !empty($member->no_kta)) {
			$id = trim($member->no_kta);
			$nokta = $id;
		}

		// buat varian nokta: padded ke 6 digit + beberapa alternatif
		$id = $nokta !== '' ? str_pad($nokta, 6, '0', STR_PAD_LEFT) : '';
		$variants = [];
		if ($id !== '') {
			$variants[] = $id;
			$variants[] = ltrim($id, '0');               // tanpa leading zeros
			if (strlen($id) >= 5) $variants[] = substr($id, 1, 5);
			if (strlen($id) >= 4) $variants[] = substr($id, 2, 4);
		}
		$variants = array_values(array_unique(array_filter($variants)));

		// ambil nama
		$cari_nama = null;
		if ($id !== '') $cari_nama = $this->simpan_model->ambil_nama($id);
		if (!$cari_nama && $nokta !== '') $cari_nama = $this->simpan_model->ambil_nama($nokta);
		if ($cari_nama) {
			$namad = $cari_nama->namad ?? '';
			$namab = $cari_nama->namab ?? '';
			$nama = trim($namad . ' ' . $namab);
		}

		// inisialisasi data default
		$data = array_merge($data, [
			'nama' => $nama,
			'nokta' => $nokta,
			'kode_apec' => '',
			'nosip' => '',
			'noreg' => '',
			'kode_acpe' => '',
			'doi' => '',
			'new_pe' => '',
			'bk_acpe' => '',
			'no_aer' => '',
			'grade' => '',
			'url_aer' => '',
			// skip & stri default
			'sk_skip' => null,
			'skip_from' => null,
			'skip_thru' => null,
			'skip_id' => null,
			'cert_type' => null,
			'cert_ket' => null,
			'stri_id' => null,
			'stri_sk' => null,
			'stri_tipe' => null,
			'stri_from' => null,
			'stri_thru' => null
		]);

		// helper cari data
		$try_find = function ($methodName) use ($variants) {
			foreach ($variants as $v) {
				$c = $this->simpan_model->{$methodName}($v);
				if ($c != null) return $c;
			}
			return null;
		};

		// APEC
		$c = $try_find('cari_data_apec');
		if ($c) {
			$data['kode_apec'] = $c->kode ?? '';
			$data['nosip'] = $c->nosip ?? '';
			$data['noreg'] = $c->reg_urut ?? '';
		}

		// ACPE
		$c = $try_find('cari_data_acpe');
		if ($c) {
			$data['kode_acpe'] = $c->no_acpe ?? '';
			$data['doi'] = $c->doi ?? '';
			$data['new_pe'] = $c->new_pe_no ?? '';
			$data['bk_acpe'] = $c->bk_acpe ?? '';
		}

		// AER
		$c = $try_find('cari_data_aer');
		if ($c) {
			$data['no_aer'] = $c->no_aer ?? '';
			$data['grade'] = $c->grade ?? '';
			$data['url_aer'] = $c->url_aer ?? '';
			if (!empty($c->nama)) $data['nama'] = $c->nama;
		}

		// SKIP & STRI ? hanya kalau ada sertifikat
		if ($this->simpan_model->cek_sertifikat_member($id)) {
			$c = $try_find('cari_di_member_serti_akhir');
			if ($c && !empty($c->skip_id)) {
				$data['sk_skip']   = $c->skip_sk ?? null;
				$data['skip_from'] = $c->skip_from_date ?? null;
				$data['skip_thru'] = $c->skip_thru_date ?? null;
				$data['skip_id']   = $c->skip_id ?? null;
				$data['cert_type'] = $c->certificate_type ?? null;

				$ct = $data['cert_type'];
				$data['cert_ket'] = ($ct == 1) ? 'IPP' : (($ct == 2) ? 'IPM' : (($ct == 3) ? 'IPU' : null));

				$data['stri_id']   = $c->stri_id ?? null;
				$data['stri_sk']   = $c->stri_sk ?? null;
				$data['stri_tipe'] = $c->stri_tipe ?? null;
				$data['stri_from'] = $c->stri_from_date ?? null;
				$data['stri_thru'] = $c->stri_thru_date ?? null;
			}
		}



		$this->load->view('member/sertifikat_aplikan', $data);
	}




	function skill()
	{
		if ($this->session->userdata('user_id') == '') {
			redirect('auth/login');
		}
		$this->load->model('main_mod');
		$this->load->model('members_model');
		$data['title'] = 'PII | Keahlian';
		$id = $this->session->userdata('user_id');

		$obj_row = $this->members_model->get_member_by_id($id);
		$data['row'] = $obj_row;
		$data['idmember'] = $id;
		$data['user_skill'] = $this->main_mod->msrwhere('user_skill', array('user_id' => $id, 'status' => 1), 'id', 'asc')->result();

		$data['emailx'] = $this->session->userdata('email');
		$this->load->view('member/skill_view', $data);
	}

	function add_skill()
	{
		if ($this->session->userdata('user_id') == '') {
			redirect('auth/login');
		}
		$this->load->model('main_mod');
		//SKILL
		$name = $this->input->post('skillname') <> null ? $this->input->post('skillname') : "";
		$proficiency = $this->input->post('proficiency') <> null ? $this->input->post('proficiency') : "";
		$description = $this->input->post('skilldesc') <> null ? $this->input->post('skilldesc') : "";
		$jenisbahasa = $this->input->post('jenisbahasa') <> null ? $this->input->post('jenisbahasa') : "";
		$jenistulisan = $this->input->post('jenistulisan') <> null ? $this->input->post('jenistulisan') : "";

		if ($name != '') {

			$url_image = '';

			if (isset($_FILES['attachment']['name'])) {
				$valid_formats_img = array("jpg", "jpeg", "gif", "png", "pdf", "bmp");
				$namex = $_FILES['attachment']['name'];
				$size = $_FILES['attachment']['size'];

				$extx = pathinfo($namex, PATHINFO_EXTENSION);

				if (strlen($namex)) {
					//list($txt, $ext) = explode(".", $name);
					if (in_array(strtolower($extx), $valid_formats_img)) {
						if ($size < (710000)) {
							//$actual_image_name = time().substr(str_replace(" ", "_", $txt), 5).".".$ext;
							$actual_image_name = time() . "_SKL_" . $this->session->userdata('user_id') . "." . $extx;

							$config['upload_path'] = './assets/uploads/';
							$config['allowed_types'] = '*';
							$config['max_size']	= '710';
							$config['file_name'] = $actual_image_name;

							$this->load->library('upload', $config);

							if ($this->upload->do_upload('attachment')) {
								$url_image = $actual_image_name;
							} else
								echo "<span style:'color:red'>Format file yang diijinkan (gif|jpg|png|jpeg|pdf|bmp).</span>";
						} else
							echo "<span style:'color:red'>Sorry, maximum file size should be 700 KB</span>";
					} else
						echo "<span style:'color:red'>Format file yang diijinkan (gif|jpg|png|jpeg|pdf|bmp).</span>";
				} else
					echo "<span style:'color:red'>Please select an image (jpg, gif, png, bmp file) or PDF file.</span>";
			}

			try {
				$row = array(
					'user_id' => $this->session->userdata('user_id'),
					'name' => $name,

					'jenisbahasa' => $jenisbahasa,
					'jenistulisan' => $jenistulisan,

					'proficiency' => $proficiency,
					'description' => $description,
					'attachment' => $url_image,
					'createdby' => $this->session->userdata('user_id')
				);
				$insert = $this->main_mod->insert('user_skill', $row);
				echo $insert;
			} catch (Exception $e) {
				print_r($e);
			}
		} else
			echo "not valid";
	}

	function edit_skill()
	{
		if ($this->session->userdata('user_id') == '') {
			redirect('auth/login');
		}
		$this->load->model('main_mod');
		//SKILL
		$name = $this->input->post('skillname') <> null ? $this->input->post('skillname') : "";
		$proficiency = $this->input->post('proficiency') <> null ? $this->input->post('proficiency') : "";
		$description = $this->input->post('skilldesc') <> null ? $this->input->post('skilldesc') : "";
		$jenisbahasa = $this->input->post('jenisbahasa') <> null ? $this->input->post('jenisbahasa') : "";
		$jenistulisan = $this->input->post('jenistulisan') <> null ? $this->input->post('jenistulisan') : "";


		$id = $this->input->post('id') <> null ? $this->input->post('id') : "";
		if ($name != '') {



			$url_image = '';

			if (isset($_FILES['attachment']['name'])) {
				$valid_formats_img = array("jpg", "jpeg", "gif", "png", "pdf", "bmp");
				$namex = $_FILES['attachment']['name'];
				$size = $_FILES['attachment']['size'];

				$extx = pathinfo($namex, PATHINFO_EXTENSION);

				if (strlen($namex)) {
					//list($txt, $ext) = explode(".", $name);
					if (in_array(strtolower($extx), $valid_formats_img)) {
						if ($size < (710000)) {
							//$actual_image_name = time().substr(str_replace(" ", "_", $txt), 5).".".$ext;
							$actual_image_name = time() . "_SKL_" . $this->session->userdata('user_id') . "." . $extx;

							$config['upload_path'] = './assets/uploads/';
							$config['allowed_types'] = '*';
							$config['max_size']	= '710';
							$config['file_name'] = $actual_image_name;

							$this->load->library('upload', $config);

							if ($this->upload->do_upload('attachment')) {
								$url_image = $actual_image_name;
							} else
								echo "<span style:'color:red'>Format file yang diijinkan (gif|jpg|png|jpeg|pdf|bmp).</span>";
						} else
							echo "<span style:'color:red'>Sorry, maximum file size should be 700 KB</span>";
					} else
						echo "<span style:'color:red'>Format file yang diijinkan (gif|jpg|png|jpeg|pdf|bmp).</span>";
				} else
					echo "<span style:'color:red'>Please select an image (jpg, gif, png, bmp file) or PDF file.</span>";
			}


			try {
				if (isset($_FILES['attachment']['name'])) {
					$row = array(
						'name' => $name,

						'jenisbahasa' => $jenisbahasa,
						'jenistulisan' => $jenistulisan,


						'proficiency' => $proficiency,
						'description' => $description,
						'attachment' => $url_image,
						'modifiedby' => $this->session->userdata('user_id'),
						'modifieddate' => date('Y-m-d h:i:s')
					);
					$where = array(
						"user_id" => $this->session->userdata('user_id'),
						"id" => $id,
					);
					$update = $this->main_mod->update('user_skill', $where, $row);
				} else {
					$row = array(
						'name' => $name,

						'jenisbahasa' => $jenisbahasa,
						'jenistulisan' => $jenistulisan,


						'proficiency' => $proficiency,
						'description' => $description,
						'modifiedby' => $this->session->userdata('user_id'),
						'modifieddate' => date('Y-m-d h:i:s')
					);
					$where = array(
						"user_id" => $this->session->userdata('user_id'),
						"id" => $id,
					);
					$update = $this->main_mod->update('user_skill', $where, $row);
				}



				echo 1;
			} catch (Exception $e) {
				print_r($e);
			}
		} else
			echo "not valid";
	}

	function del_skill()
	{
		if ($this->session->userdata('user_id') == '') {
			redirect('auth/login');
		}
		$this->load->model('main_mod');
		//SKILL
		$id = $this->input->post('id') <> null ? $this->input->post('id') : "";
		if ($id != '') {
			try {
				$check = $this->main_mod->msrwhere('user_skill', array('user_id' => $this->session->userdata('user_id'), 'id' => $id), 'id', 'desc')->result();
				if (isset($check[0])) {
					$update = $this->main_mod->delete('user_skill', 'id', $id);
					echo 1;
				}
			} catch (Exception $e) {
				print_r($e);
			}
		} else
			echo "not valid";
	}

	function add_reg()
	{
		if ($this->session->userdata('user_id') == '') {
			redirect('auth/login');
		}
		$this->load->model('main_mod');
		//SKILL
		$fieldofexpert = $this->input->post('fieldofexpert') <> null ? $this->input->post('fieldofexpert') : "";
		$accauth = $this->input->post('accauth') <> null ? $this->input->post('accauth') : "";
		$subfield = $this->input->post('subfield') <> null ? $this->input->post('subfield') : "";
		$filename = $this->input->post('filename') <> null ? $this->input->post('filename') : "";
		$desc2 = $this->input->post('desc2') <> null ? $this->input->post('desc2') : "";

		if ($fieldofexpert != '') {
			try {
				$nameDoc = $filename;
				//UPLOAD
				/*$document = $this->upload();
				$nameDoc = "";
				if (isset($document['status'])) {
					$nameDoc = $document['message'];
				}*/

				$row = array(
					'user_id' => $this->session->userdata('user_id'),
					'fieldofexpert' => $fieldofexpert,
					'accauth' => $accauth,
					'subfield' => $subfield,
					'document' => $nameDoc,
					'description' => $desc2
				);
				$insert = $this->main_mod->insert('user_reg', $row);
				echo $insert;
			} catch (Exception $e) {
				print_r($e);
			}
		} else
			echo "not valid";
	}

	function edit_reg()
	{
		if ($this->session->userdata('user_id') == '') {
			redirect('auth/login');
		}
		$this->load->model('main_mod');
		//SKILL
		$fieldofexpert = $this->input->post('fieldofexpert') <> null ? $this->input->post('fieldofexpert') : "";
		$accauth = $this->input->post('accauth') <> null ? $this->input->post('accauth') : "";
		$subfield = $this->input->post('subfield') <> null ? $this->input->post('subfield') : "";
		$filename = $this->input->post('filename') <> null ? $this->input->post('filename') : "";
		$desc2 = $this->input->post('desc2') <> null ? $this->input->post('desc2') : "";


		$id = $this->input->post('id') <> null ? $this->input->post('id') : "";
		if ($fieldofexpert != '') {
			try {
				$row = array();
				//UPLOAD
				/*$document = $this->upload();
				$nameDoc = "";
				if (isset($document['status'])) {
					$nameDoc = $document['message'];
					$row=array(
					'fieldofexpert' => $fieldofexpert,
					'accauth' => $accauth,
					'subfield' => $subfield,
					'document' => $nameDoc,
					'description' => $desc2
					);
				}*/

				if ($filename != '') {
					$row = array(
						'fieldofexpert' => $fieldofexpert,
						'accauth' => $accauth,
						'subfield' => $subfield,
						'document' => $filename,
						'description' => $desc2
					);
				} else {
					$row = array(
						'fieldofexpert' => $fieldofexpert,
						'accauth' => $accauth,
						'subfield' => $subfield,
						//'document' => $filename,
						'description' => $desc2
					);
				}
				$where = array(
					"user_id" => $this->session->userdata('user_id'),
					"id" => $id,
				);
				$update = $this->main_mod->update('user_reg', $where, $row);

				echo 1;
			} catch (Exception $e) {
				print_r($e);
			}
		} else
			echo "not valid";
	}

	function del_reg()
	{
		if ($this->session->userdata('user_id') == '') {
			redirect('auth/login');
		}
		$this->load->model('main_mod');
		//SKILL
		$id = $this->input->post('id') <> null ? $this->input->post('id') : "";
		if ($id != '') {
			try {
				$check = $this->main_mod->msrwhere('user_reg', array('user_id' => $this->session->userdata('user_id'), 'id' => $id), 'id', 'desc')->result();
				if (isset($check[0])) {
					$update = $this->main_mod->delete('user_reg', 'id', $id);
					echo 1;
				}
			} catch (Exception $e) {
				print_r($e);
			}
		} else
			echo "not valid";
	}

	function file_upload()
	{
		if (! $this->session->userdata('user_id')) redirect('login');

		if (isset($_FILES['userfilegroup']['name'])) {
			$valid_formats_img = array("jpg", "jpeg", "gif", "png", "pdf", "bmp");
			$name = $_FILES['userfilegroup']['name'];
			$size = $_FILES['userfilegroup']['size'];

			$extx = pathinfo($name, PATHINFO_EXTENSION);

			if (strlen($name)) {
				//list($txt, $ext) = explode(".", $name);
				if (in_array(strtolower($extx), $valid_formats_img)) {
					if ($size < (710000)) {
						//$actual_image_name = time().substr(str_replace(" ", "_", $txt), 5).".".$ext;

						$actual_image_name = time() . "_FILE_" . $this->session->userdata('user_id') . "." . $extx;

						$config['upload_path'] = './assets/uploads/';
						$config['allowed_types'] = '*';
						$config['max_size']	= '710';
						$config['file_name'] = $actual_image_name;

						$this->load->library('upload', $config);

						if ($this->upload->do_upload('userfilegroup')) {

							/*$config['image_library'] = 'gd2';
							$config['source_image'] = './assets/uploads/'.$actual_image_name;
							$config['maintain_ratio'] = TRUE;
							//$config['width']    = 300;
							//$config['height']   = 300;

							$this->load->library('image_lib', $config); 

							if (!$this->image_lib->resize()) {
								//echo $this->image_lib->display_errors();
							}*/


							echo "<input type='hidden' id='ajax_image_url' value='" . $actual_image_name . "'>";
							//echo "<img src='".base_url()."/assets/uploads/".$actual_image_name."'  class='ava_discus' width='150'>";
							echo "<a href='" . base_url() . "/assets/uploads/" . $actual_image_name . "'  class='ava_discus'>" . $actual_image_name . "</a>";
						} else
							echo "<span style:'color:red'>Format file yang diijinkan (gif|jpg|png|jpeg|pdf|bmp).</span>";
					} else
						echo "<span style:'color:red'>Sorry, maximum file size should be 700 KB</span>";
				} else
					echo "<span style:'color:red'>Format file yang diijinkan (gif|jpg|png|jpeg|pdf|bmp).</span>";
			} else
				echo "<span style:'color:red'>Please select an image (jpg, gif, png, bmp file) or PDF file.</span>";
		} else
			echo "<span style:'color:red'>Please select an image (jpg, gif, png, bmp file) or PDF file.</span>";
	}

	function ktp_upload()
	{
		if (! $this->session->userdata('user_id')) redirect('login');

		if (isset($_FILES['ktp']['name'])) {
			$valid_formats_img = array("jpg", "jpeg", "gif", "png", "pdf", "bmp");
			$name = $_FILES['ktp']['name'];
			$size = $_FILES['ktp']['size'];

			$extx = pathinfo($name, PATHINFO_EXTENSION);

			if (strlen($name)) {
				//list($txt, $ext) = explode(".", $name);
				if (in_array(strtolower($extx), $valid_formats_img)) {
					if ($size < (710000)) {
						//$actual_image_name = time().substr(str_replace(" ", "_", $txt), 5).".".$ext;
						$actual_image_name = time() . "_KTP_" . $this->session->userdata('user_id') . "." . $extx;

						$config['upload_path'] = './assets/uploads/';
						$config['allowed_types'] = '*';
						$config['max_size']	= '710';
						$config['file_name'] = $actual_image_name;

						$this->load->library('upload', $config);

						if ($this->upload->do_upload('ktp')) {

							/*$config['image_library'] = 'gd2';
							$config['source_image'] = './assets/uploads/'.$actual_image_name;
							$config['maintain_ratio'] = TRUE;
							//$config['width']    = 300;
							//$config['height']   = 300;

							$this->load->library('image_lib', $config); 

							if (!$this->image_lib->resize()) {
								//echo $this->image_lib->display_errors();
							}*/

							$user_profiles = $this->main_mod->msrwhere('user_profiles', array('user_id' => $this->session->userdata('user_id')), 'id', 'asc')->row();
							$temp = (isset($user_profiles->id_file) ? $user_profiles->id_file : '');

							$where = array(
								"user_id" => $this->session->userdata('user_id')
							);
							$row = array(
								'id_file' => $actual_image_name,
								'modifieddate' => date('Y-m-d H:i:s'),
								'modifiedby' => $this->session->userdata('user_id'),
							);
							$update = $this->main_mod->update('user_profiles', $where, $row);

							//if (file_exists("./assets/uploads/".$temp) && $temp!='') 
							//		unlink("./assets/uploads/".$temp);

							echo "<input type='hidden' id='ktp_image_url' value='" . $actual_image_name . "'>";
							//echo "<img src='".base_url()."/assets/uploads/".$actual_image_name."'  class='ava_discus' width='150'>";
							echo "<a href='" . base_url() . "/assets/uploads/" . $actual_image_name . "' target='_blank' class='ava_discus'>" . $actual_image_name . "</a>";
						} else
							echo "<span style:'color:red'>Format file yang diijinkan (gif|jpg|png|jpeg|pdf|bmp).</span>";
					} else
						echo "<span style:'color:red'>Sorry, maximum file size should be 700 KB</span>";
				} else
					echo "<span style:'color:red'>Format file yang diijinkan (gif|jpg|png|jpeg|pdf|bmp).</span>";
			} else
				echo "<span style:'color:red'>Please select an image (jpg, gif, png, bmp file) or PDF file.</span>";
		} else
			echo "<span style:'color:red'>Please select an image (jpg, gif, png, bmp file) or PDF file.</span>";
	}

	function photo_upload()
	{
		if (! $this->session->userdata('user_id')) redirect('login');

		if (isset($_FILES['photo']['name'])) {
			$valid_formats_img = array("jpg", "jpeg", "png"); //,"gif","pdf","bmp"
			$name = $_FILES['photo']['name'];
			$size = $_FILES['photo']['size'];

			$extx = pathinfo($name, PATHINFO_EXTENSION);

			if (strlen($name)) {
				//list($txt, $ext) = explode(".", $name);
				if (in_array(strtolower($extx), $valid_formats_img)) {
					if ($size < (710000)) {
						//$actual_image_name = time().substr(str_replace(" ", "_", $txt), 5).".".$ext;

						$actual_image_name = time() . "_PHOTO_" . $this->session->userdata('user_id') . "." . $extx;

						$config['upload_path'] = './assets/uploads/';
						$config['allowed_types'] = '*';
						$config['max_size']	= '710';
						$config['file_name'] = $actual_image_name;

						$this->load->library('upload', $config);

						if ($this->upload->do_upload('photo')) {

							/*$config['image_library'] = 'gd2';
							$config['source_image'] = './assets/uploads/'.$actual_image_name;
							$config['maintain_ratio'] = TRUE;
							//$config['width']    = 300;
							//$config['height']   = 300;

							$this->load->library('image_lib', $config); 

							if (!$this->image_lib->resize()) {
								//echo $this->image_lib->display_errors();
							}*/

							$user_profiles = $this->main_mod->msrwhere('user_profiles', array('user_id' => $this->session->userdata('user_id')), 'id', 'asc')->row();
							$temp = (isset($user_profiles->id_file) ? $user_profiles->id_file : '');

							$where = array(
								"user_id" => $this->session->userdata('user_id')
							);
							$row = array(
								'photo' => $actual_image_name,
								'modifieddate' => date('Y-m-d H:i:s'),
								'modifiedby' => $this->session->userdata('user_id'),
							);
							$update = $this->main_mod->update('user_profiles', $where, $row);

							//if (file_exists("./assets/uploads/".$temp) && $temp!='') 
							//		unlink("./assets/uploads/".$temp);

							echo "<input type='hidden' id='photo_image_url' value='" . $actual_image_name . "'>";
							//echo "<img src='".base_url()."/assets/uploads/".$actual_image_name."'  class='ava_discus' width='150'>";

							echo "<a href='" . base_url() . "/assets/uploads/" . $actual_image_name . "' target='_blank' class='ava_discus'>" . $actual_image_name . "</a>";

							//echo "<a href='".base_url()."/assets/uploads/".$actual_image_name."'  class='ava_discus'>".$actual_image_name."</a>";
						} else
							echo "<span style:'color:red'>Format file yang diijinkan (jpg|png|jpeg).</span>";
					} else
						echo "<span style:'color:red'>Sorry, maximum file size should be 700 KB</span>";
				} else
					echo "<span style:'color:red'>Format file yang diijinkan (jpg|png|jpeg).</span>";
			} else
				echo "<span style:'color:red'>Please select an image (jpg|png|jpeg).</span>";
		} else
			echo "<span style:'color:red'>Please select an image (jpg|png|jpeg).</span>";
	}

	function cert_upload()
	{
		if (! $this->session->userdata('user_id')) redirect('login');

		$id = $this->input->post('file') <> null ? $this->input->post('file') : "";

		if (isset($_FILES['file']['name'])) {
			$valid_formats_img = array("jpg", "jpeg", "gif", "png", "pdf", "bmp");
			$name = $_FILES['file']['name'];
			$size = $_FILES['file']['size'];

			$extx = pathinfo($name, PATHINFO_EXTENSION);

			if (strlen($name)) {
				//list($txt, $ext) = explode(".", $name);
				if (in_array(strtolower($extx), $valid_formats_img)) {
					if ($size < (710000)) {
						//$actual_image_name = time().substr(str_replace(" ", "_", $txt), 5).".".$ext;
						$actual_image_name = time() . "_CERT_" . $this->session->userdata('user_id') . "." . $extx;
						$config['upload_path'] = './assets/uploads/';
						$config['allowed_types'] = '*';
						$config['max_size']	= '710';
						$config['file_name'] = $actual_image_name;

						$this->load->library('upload', $config);

						if ($this->upload->do_upload('file')) {

							/*$config['image_library'] = 'gd2';
							$config['source_image'] = './assets/uploads/'.$actual_image_name;
							$config['maintain_ratio'] = TRUE;
							//$config['width']    = 300;
							//$config['height']   = 300;

							$this->load->library('image_lib', $config); 

							if (!$this->image_lib->resize()) {
								//echo $this->image_lib->display_errors();
							}*/


							echo "<input type='hidden' name='cert_image_url[]' value='" . $actual_image_name . "'>";
							//echo "<img src='".base_url()."/assets/uploads/".$actual_image_name."'  class='ava_discus' width='150'>";
							echo "<a href='" . base_url() . "/assets/uploads/" . $actual_image_name . "' target='_blank' class='ava_discus'>" . $actual_image_name . "</a>";
							//echo "<a href='".base_url()."/assets/uploads/".$actual_image_name."'  class='ava_discus'>".$actual_image_name."</a>";
						} else
							echo "<span style:'color:red'>Format file yang diijinkan (gif|jpg|png|jpeg|pdf|bmp).</span>";
					} else
						echo "<span style:'color:red'>Sorry, maximum file size should be 700 KB</span>";
				} else
					echo "<span style:'color:red'>Format file yang diijinkan (gif|jpg|png|jpeg|pdf|bmp).</span>";
			} else
				echo "<span style:'color:red'>Please select an image (jpg, gif, png, bmp file) or PDF file.</span>";
		} else
			echo "<span style:'color:red'>Please select an image (jpg, gif, png, bmp file) or PDF file.</span>";
	}

	function edu_upload()
	{
		if (! $this->session->userdata('user_id')) redirect('login');

		$id = $this->input->post('file') <> null ? $this->input->post('file') : "";

		if (isset($_FILES['file']['name'])) {
			$valid_formats_img = array("jpg", "jpeg", "gif", "png", "pdf", "bmp");
			$name = $_FILES['file']['name'];
			$size = $_FILES['file']['size'];

			$extx = pathinfo($name, PATHINFO_EXTENSION);
			//print_r($size);
			if (strlen($name)) {
				//list($txt, $ext) = explode(".", $name);
				if (in_array(strtolower($extx), $valid_formats_img)) {
					if ($size < (710000)) {
						//$actual_image_name = time().substr(str_replace(" ", "_", $txt), 5).".".$ext;
						$actual_image_name = time() . "_EDU_" . $this->session->userdata('user_id') . "." . $extx;
						$config['upload_path'] = './assets/uploads/';
						$config['allowed_types'] = '*';
						$config['max_size']	= '710';
						$config['file_name'] = $actual_image_name;

						$this->load->library('upload', $config);

						if ($this->upload->do_upload('file')) {

							/*$config['image_library'] = 'gd2';
							$config['source_image'] = './assets/uploads/'.$actual_image_name;
							$config['maintain_ratio'] = TRUE;
							//$config['width']    = 300;
							//$config['height']   = 300;

							$this->load->library('image_lib', $config); 

							if (!$this->image_lib->resize()) {
								//echo $this->image_lib->display_errors();
							}*/


							echo "<input type='hidden' name='edu_image_url[]' value='" . $actual_image_name . "'>";
							//echo "<img src='".base_url()."/assets/uploads/".$actual_image_name."'  class='ava_discus' width='150'>";
							echo "<a href='" . base_url() . "/assets/uploads/" . $actual_image_name . "' target='_blank' class='ava_discus'>" . $actual_image_name . "</a>";
							//echo "<a href='".base_url()."/assets/uploads/".$actual_image_name."'  class='ava_discus'>".$actual_image_name."</a>";
						} else
							echo "<span style:'color:red'>Format file yang diijinkan (gif|jpg|png|jpeg|pdf|bmp).</span>";
					} else
						echo "<span style:'color:red'>Sorry, maximum file size should be 700 KB</span>";
				} else
					echo "<span style:'color:red'>Format file yang diijinkan (gif|jpg|png|jpeg|pdf|bmp).</span>";
			} else
				echo "<span style:'color:red'>Please select an image (jpg, gif, png, bmp file) or PDF file.</span>";
		} else
			echo "<span style:'color:red'>Please select an image (jpg, gif, png, bmp file) or PDF file.</span>";
	}

	function exp_upload()
	{
		if (! $this->session->userdata('user_id')) redirect('login');

		$id = $this->input->post('file') <> null ? $this->input->post('file') : "";

		if (isset($_FILES['file']['name'])) {
			$valid_formats_img = array("jpg", "jpeg", "gif", "png", "pdf", "bmp");
			$name = $_FILES['file']['name'];
			$size = $_FILES['file']['size'];

			$extx = pathinfo($name, PATHINFO_EXTENSION);
			//print_r($size);
			if (strlen($name)) {
				//list($txt, $ext) = explode(".", $name);
				if (in_array(strtolower($extx), $valid_formats_img)) {
					if ($size < (710000)) {
						//$actual_image_name = time().substr(str_replace(" ", "_", $txt), 5).".".$ext;
						$actual_image_name = time() . "_EXP_" . $this->session->userdata('user_id') . "." . $extx;
						$config['upload_path'] = './assets/uploads/';
						$config['allowed_types'] = '*';
						$config['max_size']	= '710';
						$config['file_name'] = $actual_image_name;

						$this->load->library('upload', $config);

						if ($this->upload->do_upload('file')) {

							/*$config['image_library'] = 'gd2';
							$config['source_image'] = './assets/uploads/'.$actual_image_name;
							$config['maintain_ratio'] = TRUE;
							//$config['width']    = 300;
							//$config['height']   = 300;

							$this->load->library('image_lib', $config); 

							if (!$this->image_lib->resize()) {
								//echo $this->image_lib->display_errors();
							}*/


							echo "<input type='hidden' name='exp_image_url[]' value='" . $actual_image_name . "'>";
							//echo "<img src='".base_url()."/assets/uploads/".$actual_image_name."'  class='ava_discus' width='150'>";
							echo "<a href='" . base_url() . "/assets/uploads/" . $actual_image_name . "' target='_blank' class='ava_discus'>" . $actual_image_name . "</a>";
							//echo "<a href='".base_url()."/assets/uploads/".$actual_image_name."'  class='ava_discus'>".$actual_image_name."</a>";
						} else
							echo "<span style:'color:red'>Format file yang diijinkan (gif|jpg|png|jpeg|pdf|bmp).</span>";
					} else
						echo "<span style:'color:red'>Sorry, maximum file size should be 700 KB</span>";
				} else
					echo "<span style:'color:red'>Format file yang diijinkan (gif|jpg|png|jpeg|pdf|bmp).</span>";
			} else
				echo "<span style:'color:red'>Please select an image (jpg, gif, png, bmp file) or PDF file.</span>";
		} else
			echo "<span style:'color:red'>Please select an image (jpg, gif, png, bmp file) or PDF file.</span>";
	}

	function surat_pernyataan_upload()
	{
		if (! $this->session->userdata('user_id')) redirect('login');

		if (isset($_FILES['surat_pernyataan']['name'])) {
			$valid_formats_img = array("jpg", "jpeg", "gif", "png", "pdf", "bmp");
			$name = $_FILES['surat_pernyataan']['name'];
			$size = $_FILES['surat_pernyataan']['size'];

			$extx = pathinfo($name, PATHINFO_EXTENSION);

			if (strlen($name)) {
				if (in_array(strtolower($extx), $valid_formats_img)) {
					if ($size < (710000)) {
						$actual_image_name = time() . "_SP_" . $this->session->userdata('user_id') . "." . $extx;

						$config['upload_path'] = './assets/uploads/';
						$config['allowed_types'] = '*';
						$config['max_size']	= '710';
						$config['file_name'] = $actual_image_name;

						$this->load->library('upload', $config);

						if ($this->upload->do_upload('surat_pernyataan')) {


							//$user_profiles = $this->main_mod->msrwhere('user_profiles',array('user_id'=>$this->session->userdata('user_id')),'id','asc')->row();	
							//$temp = (isset($user_profiles->surat_pernyataan)?$user_profiles->surat_pernyataan:'');

							$where = array(
								"user_id" => $this->session->userdata('user_id')
							);
							$row = array(
								'surat_pernyataan' => $actual_image_name,
								'modifieddate' => date('Y-m-d H:i:s'),
								'modifiedby' => $this->session->userdata('user_id'),
							);
							$update = $this->main_mod->update('user_profiles', $where, $row);

							echo "<input type='hidden' id='surat_pernyataan_image_url' value='" . $actual_image_name . "'>";
							echo "<a href='" . base_url() . "/assets/uploads/" . $actual_image_name . "' target='_blank' class='ava_discus'>" . $actual_image_name . "</a>";
						} else
							echo "<span style:'color:red'>Format file yang diijinkan (gif|jpg|png|jpeg|pdf|bmp).</span>";
					} else
						echo "<span style:'color:red'>Sorry, maximum file size should be 700 KB</span>";
				} else
					echo "<span style:'color:red'>Format file yang diijinkan (gif|jpg|png|jpeg|pdf|bmp).</span>";
			} else
				echo "<span style:'color:red'>Please select an image (jpg, gif, png, bmp file) or PDF file.</span>";
		} else
			echo "<span style:'color:red'>Please select an image (jpg, gif, png, bmp file) or PDF file.</span>";
	}

	function sertifikat_legal_upload()
	{
		if (! $this->session->userdata('user_id')) redirect('login');

		if (isset($_FILES['sertifikat_legal']['name'])) {
			$valid_formats_img = array("jpg", "jpeg", "gif", "png", "pdf", "bmp");
			$name = $_FILES['sertifikat_legal']['name'];
			$size = $_FILES['sertifikat_legal']['size'];

			$extx = pathinfo($name, PATHINFO_EXTENSION);

			if (strlen($name)) {
				if (in_array(strtolower($extx), $valid_formats_img)) {
					if ($size < (710000)) {
						$actual_image_name = time() . "_SL_" . $this->session->userdata('user_id') . "." . $extx;

						$config['upload_path'] = './assets/uploads/';
						$config['allowed_types'] = '*';
						$config['max_size']	= '710';
						$config['file_name'] = $actual_image_name;

						$this->load->library('upload', $config);

						if ($this->upload->do_upload('sertifikat_legal')) {


							//$user_profiles = $this->main_mod->msrwhere('user_profiles',array('user_id'=>$this->session->userdata('user_id')),'id','asc')->row();	
							//$temp = (isset($user_profiles->sertifikat_legal)?$user_profiles->sertifikat_legal:'');

							$where = array(
								"user_id" => $this->session->userdata('user_id')
							);
							$row = array(
								'sertifikat_legal' => $actual_image_name,
								'modifieddate' => date('Y-m-d H:i:s'),
								'modifiedby' => $this->session->userdata('user_id'),
							);
							$update = $this->main_mod->update('user_profiles', $where, $row);

							echo "<input type='hidden' id='sertifikat_legal_image_url' value='" . $actual_image_name . "'>";
							echo "<a href='" . base_url() . "/assets/uploads/" . $actual_image_name . "' target='_blank' class='ava_discus'>" . $actual_image_name . "</a>";
						} else
							echo "<span style:'color:red'>Format file yang diijinkan (gif|jpg|png|jpeg|pdf|bmp).</span>";
					} else
						echo "<span style:'color:red'>Sorry, maximum file size should be 700 KB</span>";
				} else
					echo "<span style:'color:red'>Format file yang diijinkan (gif|jpg|png|jpeg|pdf|bmp).</span>";
			} else
				echo "<span style:'color:red'>Please select an image (jpg, gif, png, bmp file) or PDF file.</span>";
		} else
			echo "<span style:'color:red'>Please select an image (jpg, gif, png, bmp file) or PDF file.</span>";
	}

	function tanda_bukti_upload()
	{
		if (! $this->session->userdata('user_id')) redirect('login');

		if (isset($_FILES['tanda_bukti']['name'])) {
			$valid_formats_img = array("jpg", "jpeg", "gif", "png", "pdf", "bmp");
			$name = $_FILES['tanda_bukti']['name'];
			$size = $_FILES['tanda_bukti']['size'];

			$extx = pathinfo($name, PATHINFO_EXTENSION);

			if (strlen($name)) {
				if (in_array(strtolower($extx), $valid_formats_img)) {
					if ($size < (710000)) {
						$actual_image_name = time() . "_TB_" . $this->session->userdata('user_id') . "." . $extx;

						$config['upload_path'] = './assets/uploads/';
						$config['allowed_types'] = '*';
						$config['max_size']	= '710';
						$config['file_name'] = $actual_image_name;

						$this->load->library('upload', $config);

						if ($this->upload->do_upload('tanda_bukti')) {


							//$user_profiles = $this->main_mod->msrwhere('user_profiles',array('user_id'=>$this->session->userdata('user_id')),'id','asc')->row();	
							//$temp = (isset($user_profiles->tanda_bukti)?$user_profiles->tanda_bukti:'');

							$where = array(
								"user_id" => $this->session->userdata('user_id')
							);
							$row = array(
								'tanda_bukti' => $actual_image_name,
								'modifieddate' => date('Y-m-d H:i:s'),
								'modifiedby' => $this->session->userdata('user_id'),
							);
							$update = $this->main_mod->update('user_profiles', $where, $row);

							echo "<input type='hidden' id='tanda_bukti_image_url' value='" . $actual_image_name . "'>";
							echo "<a href='" . base_url() . "/assets/uploads/" . $actual_image_name . "' target='_blank' class='ava_discus'>" . $actual_image_name . "</a>";
						} else
							echo "<span style:'color:red'>Format file yang diijinkan (gif|jpg|png|jpeg|pdf|bmp).</span>";
					} else
						echo "<span style:'color:red'>Sorry, maximum file size should be 700 KB</span>";
				} else
					echo "<span style:'color:red'>Format file yang diijinkan (gif|jpg|png|jpeg|pdf|bmp).</span>";
			} else
				echo "<span style:'color:red'>Please select an image (jpg, gif, png, bmp file) or PDF file.</span>";
		} else
			echo "<span style:'color:red'>Please select an image (jpg, gif, png, bmp file) or PDF file.</span>";
	}

	function surat_dukungan_upload()
	{
		if (! $this->session->userdata('user_id')) redirect('login');

		if (isset($_FILES['surat_dukungan']['name'])) {
			$valid_formats_img = array("jpg", "jpeg", "gif", "png", "pdf", "bmp");
			$name = $_FILES['surat_dukungan']['name'];
			$size = $_FILES['surat_dukungan']['size'];

			$extx = pathinfo($name, PATHINFO_EXTENSION);

			if (strlen($name)) {
				if (in_array(strtolower($extx), $valid_formats_img)) {
					if ($size < (710000)) {
						$actual_image_name = time() . "_SDU_" . $this->session->userdata('user_id') . "." . $extx;

						$config['upload_path'] = './assets/uploads/';
						$config['allowed_types'] = '*';
						$config['max_size']	= '710';
						$config['file_name'] = $actual_image_name;

						$this->load->library('upload', $config);

						if ($this->upload->do_upload('surat_dukungan')) {


							//$user_profiles = $this->main_mod->msrwhere('user_profiles',array('user_id'=>$this->session->userdata('user_id')),'id','asc')->row();	
							//$temp = (isset($user_profiles->surat_dukungan)?$user_profiles->surat_dukungan:'');

							$where = array(
								"user_id" => $this->session->userdata('user_id')
							);
							$row = array(
								'surat_dukungan' => $actual_image_name,
								'modifieddate' => date('Y-m-d H:i:s'),
								'modifiedby' => $this->session->userdata('user_id'),
							);
							$update = $this->main_mod->update('user_profiles', $where, $row);

							echo "<input type='hidden' id='surat_dukungan_image_url' value='" . $actual_image_name . "'>";
							echo "<a href='" . base_url() . "/assets/uploads/" . $actual_image_name . "' target='_blank' class='ava_discus'>" . $actual_image_name . "</a>";
						} else
							echo "<span style:'color:red'>Format file yang diijinkan (gif|jpg|png|jpeg|pdf|bmp).</span>";
					} else
						echo "<span style:'color:red'>Sorry, maximum file size should be 700 KB</span>";
				} else
					echo "<span style:'color:red'>Format file yang diijinkan (gif|jpg|png|jpeg|pdf|bmp).</span>";
			} else
				echo "<span style:'color:red'>Please select an image (jpg, gif, png, bmp file) or PDF file.</span>";
		} else
			echo "<span style:'color:red'>Please select an image (jpg, gif, png, bmp file) or PDF file.</span>";
	}

	function surat_ijin_domisili_upload()
	{
		if (! $this->session->userdata('user_id')) redirect('login');

		if (isset($_FILES['surat_ijin_domisili']['name'])) {
			$valid_formats_img = array("jpg", "jpeg", "gif", "png", "pdf", "bmp");
			$name = $_FILES['surat_ijin_domisili']['name'];
			$size = $_FILES['surat_ijin_domisili']['size'];

			$extx = pathinfo($name, PATHINFO_EXTENSION);

			if (strlen($name)) {
				if (in_array(strtolower($extx), $valid_formats_img)) {
					if ($size < (710000)) {
						$actual_image_name = time() . "_SID_" . $this->session->userdata('user_id') . "." . $extx;

						$config['upload_path'] = './assets/uploads/';
						$config['allowed_types'] = '*';
						$config['max_size']	= '710';
						$config['file_name'] = $actual_image_name;

						$this->load->library('upload', $config);

						if ($this->upload->do_upload('surat_ijin_domisili')) {


							//$user_profiles = $this->main_mod->msrwhere('user_profiles',array('user_id'=>$this->session->userdata('user_id')),'id','asc')->row();	
							//$temp = (isset($user_profiles->surat_ijin_domisili)?$user_profiles->surat_ijin_domisili:'');

							$where = array(
								"user_id" => $this->session->userdata('user_id')
							);
							$row = array(
								'surat_ijin_domisili' => $actual_image_name,
								'modifieddate' => date('Y-m-d H:i:s'),
								'modifiedby' => $this->session->userdata('user_id'),
							);
							$update = $this->main_mod->update('user_profiles', $where, $row);

							echo "<input type='hidden' id='surat_ijin_domisili_image_url' value='" . $actual_image_name . "'>";
							echo "<a href='" . base_url() . "/assets/uploads/" . $actual_image_name . "' target='_blank' class='ava_discus'>" . $actual_image_name . "</a>";
						} else
							echo "<span style:'color:red'>Format file yang diijinkan (gif|jpg|png|jpeg|pdf|bmp).</span>";
					} else
						echo "<span style:'color:red'>Sorry, maximum file size should be 700 KB</span>";
				} else
					echo "<span style:'color:red'>Format file yang diijinkan (gif|jpg|png|jpeg|pdf|bmp).</span>";
			} else
				echo "<span style:'color:red'>Please select an image (jpg, gif, png, bmp file) or PDF file.</span>";
		} else
			echo "<span style:'color:red'>Please select an image (jpg, gif, png, bmp file) or PDF file.</span>";
	}



	/**
	 * Login user on the site
	 *
	 * @return void
	 */

	function login()
	{
		if ($this->tank_auth->is_logged_in()) {									// logged in
			redirect('');
		} elseif ($this->tank_auth->is_logged_in(FALSE)) {						// logged in, not activated
			redirect('/auth/send_again/');
		} else {
			$data['login_by_username'] = ($this->config->item('login_by_username', 'tank_auth') and
				$this->config->item('use_username', 'tank_auth'));
			$data['login_by_email'] = $this->config->item('login_by_email', 'tank_auth');

			$this->form_validation->set_rules('login', 'Login', 'trim|required|xss_clean');
			$this->form_validation->set_rules('password', 'Password', 'trim|required|xss_clean');
			$this->form_validation->set_rules('remember', 'Remember me', 'integer');

			// Get login for counting attempts to login
			if (
				$this->config->item('login_count_attempts', 'tank_auth') and
				($login = $this->input->post('login'))
			) {
				$login = $this->security->xss_clean($login);
			} else {
				$login = '';
			}

			$data['use_recaptcha'] = $this->config->item('use_recaptcha', 'tank_auth');
			if ($this->tank_auth->is_max_login_attempts_exceeded($login)) {
				if ($data['use_recaptcha'])
					$this->form_validation->set_rules('recaptcha_response_field', 'Confirmation Code', 'trim|xss_clean|required|callback__check_recaptcha');
				else
					$this->form_validation->set_rules('captcha', 'Confirmation Code', 'trim|xss_clean|required|callback__check_captcha');
			}
			$data['errors'] = array();

			if ($this->form_validation->run()) {								// validation ok
				if ($this->tank_auth->login(
					$this->form_validation->set_value('login'),
					$this->form_validation->set_value('password'),
					$this->form_validation->set_value('remember'),
					$data['login_by_username'],
					$data['login_by_email']
				)) {								// success
					redirect('');
				} else {
					$errors = $this->tank_auth->get_error_message();
					if (isset($errors['banned'])) {								// banned user
						$this->_show_message($this->lang->line('auth_message_banned') . ' ' . $errors['banned']);
					} elseif (isset($errors['not_activated'])) {				// not activated user
						redirect('/auth/send_again/');
					} else {													// fail
						foreach ($errors as $k => $v)	$data['errors'][$k] = $this->lang->line($v);
					}
				}
			}
			$data['show_captcha'] = FALSE;
			if ($this->tank_auth->is_max_login_attempts_exceeded($login)) {
				$data['show_captcha'] = TRUE;
				if ($data['use_recaptcha']) {
					$data['recaptcha_html'] = $this->_create_recaptcha();
				} else {
					$data['captcha_html'] = $this->_create_captcha();
				}
			}
			$this->load->view('auth/login_form', $data);
		}
	}

	/**
	 * Logout user
	 *
	 * @return void
	 */
	function logout()
	{
		$this->tank_auth->logout();

		$this->_show_message($this->lang->line('auth_message_logged_out'));
	}

	/**
	 * Register user on the site
	 *
	 * @return void
	 */
	function register()
	{
		if ($this->tank_auth->is_logged_in()) {									// logged in
			redirect('');
		} elseif ($this->tank_auth->is_logged_in(FALSE)) {						// logged in, not activated
			redirect('/auth/send_again/');
		} elseif (!$this->config->item('allow_registration', 'tank_auth')) {	// registration is off
			$this->_show_message($this->lang->line('auth_message_registration_disabled'));
		} else {
			//Profile
			$this->form_validation->set_rules('firstname', 'First Name', 'trim|required|xss_clean');
			$this->form_validation->set_rules('lastname', 'Last Name', 'trim|required|xss_clean');

			$use_username = $this->config->item('use_username', 'tank_auth');
			if ($use_username) {
				$this->form_validation->set_rules('username', 'Username', 'trim|required|xss_clean|min_length[' . $this->config->item('username_min_length', 'tank_auth') . ']|max_length[' . $this->config->item('username_max_length', 'tank_auth') . ']|alpha_dash');
			}

			$this->form_validation->set_rules('email', 'Email', 'trim|required|xss_clean|valid_email|callback_email_check');
			$this->form_validation->set_rules('password', 'Password', 'trim|required|xss_clean|min_length[' . $this->config->item('password_min_length', 'tank_auth') . ']|max_length[' . $this->config->item('password_max_length', 'tank_auth') . ']|alpha_dash');
			$this->form_validation->set_rules('confirm_password', 'Confirm Password', 'trim|required|xss_clean|matches[password]');

			$captcha_registration	= $this->config->item('captcha_registration', 'tank_auth');
			$use_recaptcha			= $this->config->item('use_recaptcha', 'tank_auth');
			if ($captcha_registration) {
				if ($use_recaptcha) {
					$this->form_validation->set_rules('recaptcha_response_field', 'Confirmation Code', 'trim|xss_clean|required|callback__check_recaptcha');
				} else {
					$this->form_validation->set_rules('captcha', 'Confirmation Code', 'trim|xss_clean|required|callback__check_captcha');
				}
			}
			$data['errors'] = array();

			$email_activation = $this->config->item('email_activation', 'tank_auth');

			$this->load->model('main_mod');

			if ($this->form_validation->run()) {								// validation ok
				if (!is_null($data = $this->tank_auth->create_user(
					$use_username ? $this->form_validation->set_value('username') : '',
					$this->form_validation->set_value('email'),
					$this->form_validation->set_value('password'),
					$email_activation
				))) {									// success

					$data['site_name'] = $this->config->item('website_name', 'tank_auth');

					if ($email_activation) {									// send "activate" email
						$data['activation_period'] = $this->config->item('email_activation_expire', 'tank_auth') / 3600;

						$this->_send_email('activate', $data['email'], $data);

						unset($data['password']); // Clear password (just for any case)

						$this->_show_message($this->lang->line('auth_message_registration_completed_1'));
					} else {
						if ($this->config->item('email_account_details', 'tank_auth')) {	// send "welcome" email

							$this->_send_email('welcome', $data['email'], $data);
						}
						unset($data['password']); // Clear password (just for any case)

						//PROFILE
						$where = array(
							"user_id" => $data['user_id']
						);
						$row = array(
							'firstname' => $this->form_validation->set_value('firstname'),
							'lastname' => $this->form_validation->set_value('lastname'),
						);
						$update = $this->main_mod->update('user_profiles', $where, $row);

						//print_r($row);
						$this->_show_message($this->lang->line('auth_message_registration_completed_2') . ' ' . anchor('/auth/login/', 'Login'));
					}
				} else {
					$errors = $this->tank_auth->get_error_message();
					foreach ($errors as $k => $v)	$data['errors'][$k] = $this->lang->line($v);
				}
			}
			if ($captcha_registration) {
				if ($use_recaptcha) {
					$data['recaptcha_html'] = $this->_create_recaptcha();
				} else {
					$data['captcha_html'] = $this->_create_captcha();
				}
			}


			$data['use_username'] = $use_username;
			$data['captcha_registration'] = $captcha_registration;
			$data['use_recaptcha'] = $use_recaptcha;

			//print_r($this->form_validation);
			$this->load->view('auth/register_form', $data);
		}
	}

	public function email_check($post_email)
	{

		$this->db->where('email', $post_email);

		$query = $this->db->get('users');

		$count_row = $query->num_rows();

		if ($count_row > 0) {
			$this->form_validation->set_message('email_check', 'The email address you have entered is already registered,');
			return FALSE;
		} else {
			return TRUE;
		}
	}

	function register_bc()
	{
		if ($this->tank_auth->is_logged_in()) {									// logged in
			redirect('');
		} elseif ($this->tank_auth->is_logged_in(FALSE)) {						// logged in, not activated
			redirect('/auth/send_again/');
		} elseif (!$this->config->item('allow_registration', 'tank_auth')) {	// registration is off
			$this->_show_message($this->lang->line('auth_message_registration_disabled'));
		} else {
			//Profile
			$this->form_validation->set_rules('fn', 'Firstname', 'trim|required|xss_clean');
			$this->form_validation->set_rules('ln', 'Lastname', 'trim|required|xss_clean');
			$this->form_validation->set_rules('gender', 'Gender', 'trim|required|xss_clean');
			$this->form_validation->set_rules('ktp', 'ID', 'trim|required|xss_clean');
			$this->form_validation->set_rules('dob', 'Date of Birth', 'trim|required|xss_clean');
			$this->form_validation->set_rules('website', 'Website', 'trim|xss_clean');
			$this->form_validation->set_rules('is_public', 'is_public', 'trim|xss_clean');
			$this->form_validation->set_rules('is_datasend', 'is_datasend', 'trim|xss_clean');
			$this->form_validation->set_rules('desc', 'Description', 'trim|xss_clean');
			$this->form_validation->set_rules('mailingaddr', 'mailing addr', 'trim|xss_clean');

			$this->form_validation->set_rules('fieldofexpert', 'fieldofexpert', 'trim|xss_clean');
			$this->form_validation->set_rules('subfield', 'subfield', 'trim|xss_clean');
			$this->form_validation->set_rules('accauth', 'accauth', 'trim|xss_clean');
			$this->form_validation->set_rules('filename', 'filename', 'trim|xss_clean');
			$this->form_validation->set_rules('desc2', 'desc2', 'trim|xss_clean');


			$this->form_validation->set_rules('email[]', 'Email', 'trim|required|xss_clean|valid_email');


			$use_username = $this->config->item('use_username', 'tank_auth');
			if ($use_username) {
				$this->form_validation->set_rules('username', 'Username', 'trim|required|xss_clean|min_length[' . $this->config->item('username_min_length', 'tank_auth') . ']|max_length[' . $this->config->item('username_max_length', 'tank_auth') . ']|alpha_dash');
			}
			/*			
			$this->form_validation->set_rules('email', 'Email', 'trim|required|xss_clean|valid_email');
			$this->form_validation->set_rules('password', 'Password', 'trim|required|xss_clean|min_length['.$this->config->item('password_min_length', 'tank_auth').']|max_length['.$this->config->item('password_max_length', 'tank_auth').']|alpha_dash');
			$this->form_validation->set_rules('confirm_password', 'Confirm Password', 'trim|required|xss_clean|matches[password]');
*/
			$captcha_registration	= $this->config->item('captcha_registration', 'tank_auth');
			$use_recaptcha			= $this->config->item('use_recaptcha', 'tank_auth');
			if ($captcha_registration) {
				if ($use_recaptcha) {
					$this->form_validation->set_rules('recaptcha_response_field', 'Confirmation Code', 'trim|xss_clean|required|callback__check_recaptcha');
				} else {
					$this->form_validation->set_rules('captcha', 'Confirmation Code', 'trim|xss_clean|required|callback__check_captcha');
				}
			}
			$data['errors'] = array();

			$email_activation = $this->config->item('email_activation', 'tank_auth');

			$this->load->model('main_mod');

			$data['typephone'] = $this->input->post('typephone[]') <> null ? $this->input->post('typephone[]') : "";
			$data['phone'] = $this->input->post('phone[]') <> null ? $this->input->post('phone[]') : "";

			$data['typeemail'] = $this->input->post('typeemail[]') <> null ? $this->input->post('typeemail[]') : "";
			$data['email'] = $this->input->post('email[]') <> null ? $this->input->post('email[]') : "";

			$data['typeaddress'] = $this->input->post('typeaddress[]') <> null ? $this->input->post('typeaddress[]') : "";
			$data['address'] = $this->input->post('address[]') <> null ? $this->input->post('address[]') : "";
			$data['addressphone'] = $this->input->post('addressphone[]') <> null ? $this->input->post('addressphone[]') : "";
			$data['addresszip'] = $this->input->post('addresszip[]') <> null ? $this->input->post('addresszip[]') : "";



			if ($this->form_validation->run()) {								// validation ok
				/*if (!is_null($data = $this->tank_auth->create_user(
						$use_username ? $this->form_validation->set_value('username') : '',
						$this->form_validation->set_value('email'),
						$this->form_validation->set_value('password'),
						$email_activation))) {									// success*/

				if (!is_null($data = $this->tank_auth->create_user(
					$use_username ? $this->form_validation->set_value('username') : '',
					$data['email'][0],
					'',
					$email_activation
				))) {									// success

					$data['site_name'] = $this->config->item('website_name', 'tank_auth');

					if ($email_activation) {									// send "activate" email
						$data['activation_period'] = $this->config->item('email_activation_expire', 'tank_auth') / 3600;

						$this->_send_email('activate', $data['email'], $data);

						unset($data['password']); // Clear password (just for any case)

						$this->_show_message($this->lang->line('auth_message_registration_completed_1'));
					} else {
						if ($this->config->item('email_account_details', 'tank_auth')) {	// send "welcome" email

							$this->_send_email('welcome', $data['email'], $data);
						}
						unset($data['password']); // Clear password (just for any case)

						$data['typephone'] = $this->input->post('typephone[]') <> null ? $this->input->post('typephone[]') : "";
						$data['phone'] = $this->input->post('phone[]') <> null ? $this->input->post('phone[]') : "";

						$data['typeemail'] = $this->input->post('typeemail[]') <> null ? $this->input->post('typeemail[]') : "";
						$data['email'] = $this->input->post('email[]') <> null ? $this->input->post('email[]') : "";

						$data['typeaddress'] = $this->input->post('typeaddress[]') <> null ? $this->input->post('typeaddress[]') : "";
						$data['address'] = $this->input->post('address[]') <> null ? $this->input->post('address[]') : "";
						$data['addressphone'] = $this->input->post('addressphone[]') <> null ? $this->input->post('addressphone[]') : "";
						$data['addresszip'] = $this->input->post('addresszip[]') <> null ? $this->input->post('addresszip[]') : "";


						//UPLOAD
						$document = $this->upload();
						$nameDoc = "";
						if (isset($document['status'])) {
							$nameDoc = $document['message'];
						}

						//PROFILE
						$where = array(
							"user_id" => $data['user_id']
						);
						$row = array(
							'firstname' => $this->form_validation->set_value('fn'),
							'lastname' => $this->form_validation->set_value('ln'),
							'gender' => $this->form_validation->set_value('gender'),
							'idcard' => $this->form_validation->set_value('ktp'),
							'dob' => $this->form_validation->set_value('dob'),
							'website' => $this->form_validation->set_value('website'),
							'is_public' => $this->form_validation->set_value('is_public'),
							'is_datasend' => $this->form_validation->set_value('is_datasend'),
							'description' => $this->form_validation->set_value('desc'),

							'fieldofexpert' => $this->form_validation->set_value('fieldofexpert'),
							'accauth' => $this->form_validation->set_value('accauth'),
							'subfield' => $this->form_validation->set_value('subfield'),
							'document' => $nameDoc,
							'description2' => $this->form_validation->set_value('desc2')
						);
						$update = $this->main_mod->update('user_profiles', $where, $row);
						//PHONE
						$i = 0;
						foreach ($data['typephone'] as $val) {
							$row = array(
								'user_id' => $data['user_id'],
								'phonetype' => $data['typephone'][$i],
								'phonenumber' => $data['phone'][$i]
							);
							$insert = $this->main_mod->insert('user_phone', $row);
							$i++;
						}
						//EMAIL
						$i = 0;
						foreach ($data['typeemail'] as $val) {
							$row = array(
								'user_id' => $data['user_id'],
								'emailtype' => $data['typeemail'][$i],
								'email' => $data['email'][$i]
							);
							$insert = $this->main_mod->insert('user_email', $row);
							$i++;
						}
						//ADDRESS
						$i = 0;
						$mailing = $this->form_validation->set_value('mailingaddr');
						$mailing = $mailing - 1;
						foreach ($data['typeaddress'] as $val) {
							$temp = 0;
							if ($mailing == $i)
								$temp = 1;
							$row = array(
								'user_id' => $data['user_id'],
								'addresstype' => $data['typeaddress'][$i],
								'address' => $data['address'][$i],
								'notelp' => $data['addressphone'][$i],
								'zipcode' => $data['addresszip'][$i],
								'is_mailing' => $temp
							);
							$insert = $this->main_mod->insert('user_address', $row);
							$i++;
						}

						//EXPERIENCE
						$title = $this->input->post('title[]') <> null ? $this->input->post('title[]') : "";
						$company = $this->input->post('company[]') <> null ? $this->input->post('company[]') : "";
						$loc = $this->input->post('loc[]') <> null ? $this->input->post('loc[]') : "";
						$year = $this->input->post('year[]') <> null ? $this->input->post('year[]') : "";
						$year2 = $this->input->post('year2[]') <> null ? $this->input->post('year2[]') : "";
						$typetimeperiod = $this->input->post('typetimeperiod[]') <> null ? $this->input->post('typetimeperiod[]') : "";
						$typetimeperiod2 = $this->input->post('typetimeperiod2[]') <> null ? $this->input->post('typetimeperiod2[]') : "";
						$work = $this->input->post('work[]') <> null ? $this->input->post('work[]') : "";
						$desc = $this->input->post('desc[]') <> null ? $this->input->post('desc[]') : "";
						$i = 1;
						if (isset($title[1])) {
							foreach ($title as $val) {
								$check = $this->main_mod->msrwhere('m_company', array('desc' => $company[$i]), 'id', 'desc')->result();
								if (!isset($check[0])) {
									$row = array(
										'desc' => $company[$i],
									);
									$insert = $this->main_mod->insert('m_company', $row);
								}

								$row = array(
									'user_id' => $data['user_id'],
									'company' => $company[$i],
									'title' => $title[$i],
									'location' => $loc[$i],
									'startyear' => $year[$i],
									'startmonth' => $typetimeperiod[$i],
									'endyear' => $year2[$i],
									'endmonth' => $typetimeperiod2[$i],
									'is_present' => ($work[$i] == "true" ? "1" : "0"),
									'description' => $desc[$i]
								);
								$insert = $this->main_mod->insert('user_exp', $row);
								$i++;
							}
						}
						//EDUCATION
						$school = $this->input->post('school[]') <> null ? $this->input->post('school[]') : "";
						$startdate = $this->input->post('dateattend[]') <> null ? $this->input->post('dateattend[]') : "";
						$enddate = $this->input->post('dateattend2[]') <> null ? $this->input->post('dateattend2[]') : "";
						$degree = $this->input->post('degree[]') <> null ? $this->input->post('degree[]') : "";
						$fieldofstudy = $this->input->post('fos[]') <> null ? $this->input->post('fos[]') : "";
						$grade = $this->input->post('grade[]') <> null ? $this->input->post('grade[]') : "";
						$score = $this->input->post('score[]') <> null ? $this->input->post('score[]') : "";
						$activities = $this->input->post('actv[]') <> null ? $this->input->post('actv[]') : "";
						$description = $this->input->post('descedu[]') <> null ? $this->input->post('descedu[]') : "";
						$i = 1;
						if (isset($school[1])) {
							foreach ($school as $val) {
								$row = array(
									'user_id' => $data['user_id'],
									'school' => $school[$i],
									'startdate' => $startdate[$i],
									'enddate' => $enddate[$i],
									'degree' => $degree[$i],
									'fieldofstudy' => $fieldofstudy[$i],
									'grade' => $grade[$i],
									'score' => $score[$i],
									'activities' => $activities[$i],
									'description' => $description[$i]
								);
								$insert = $this->main_mod->insert('user_edu', $row);
								$i++;
							}
						}
						//CERTIFICATIONS
						$cert_name = $this->input->post('certname[]') <> null ? $this->input->post('certname[]') : "";
						$cert_auth = $this->input->post('certauth[]') <> null ? $this->input->post('certauth[]') : "";
						$lic_num = $this->input->post('lic[]') <> null ? $this->input->post('lic[]') : "";
						$cert_url = $this->input->post('url[]') <> null ? $this->input->post('url[]') : "";
						$startmonth = $this->input->post('certdate[]') <> null ? $this->input->post('certdate[]') : "";
						$startyear = $this->input->post('certyear[]') <> null ? $this->input->post('certyear[]') : "";
						$endmonth = $this->input->post('certdate2[]') <> null ? $this->input->post('certdate2[]') : "";
						$endyear = $this->input->post('certyear2[]') <> null ? $this->input->post('certyear2[]') : "";
						$is_present = $this->input->post('certwork[]') <> null ? $this->input->post('certwork[]') : "";
						$description = $this->input->post('certdesc[]') <> null ? $this->input->post('certdesc[]') : "";
						$i = 1;
						if (isset($cert_name[1])) {
							foreach ($cert_name as $val) {
								$row = array(
									'user_id' => $data['user_id'],
									'cert_name' => $cert_name[$i],
									'cert_auth' => $cert_auth[$i],
									'lic_num' => $lic_num[$i],
									'cert_url' => $cert_url[$i],
									'startmonth' => $startmonth[$i],
									'startyear' => $startyear[$i],
									'endmonth' => $endmonth[$i],
									'endyear' => $endyear[$i],
									'is_present' => ($is_present[$i] == "true" ? "1" : "0"),
									'description' => $description[$i]
								);
								$insert = $this->main_mod->insert('user_cert', $row);
								$i++;
							}
						}
						//ORGANIZATIONS
						$organization = $this->input->post('org[]') <> null ? $this->input->post('org[]') : "";
						$position = $this->input->post('posit[]') <> null ? $this->input->post('posit[]') : "";
						$occupation = $this->input->post('occ[]') <> null ? $this->input->post('occ[]') : "";
						$startmonth = $this->input->post('orgdate[]') <> null ? $this->input->post('orgdate[]') : "";
						$startyear = $this->input->post('orgyear[]') <> null ? $this->input->post('orgyear[]') : "";
						$endmonth = $this->input->post('orgdate2[]') <> null ? $this->input->post('orgdate2[]') : "";
						$endyear = $this->input->post('orgyear2[]') <> null ? $this->input->post('orgyear2[]') : "";
						$is_present = $this->input->post('orgwork[]') <> null ? $this->input->post('orgwork[]') : "";
						$description = $this->input->post('orgdesc[]') <> null ? $this->input->post('orgdesc[]') : "";
						$i = 1;
						if (isset($organization[1])) {
							foreach ($organization as $val) {
								$row = array(
									'user_id' => $data['user_id'],
									'organization' => $organization[$i],
									'position' => $position[$i],
									'occupation' => $occupation[$i],
									'startmonth' => $startmonth[$i],
									'startyear' => $startyear[$i],
									'endmonth' => $endmonth[$i],
									'endyear' => $endyear[$i],
									'is_present' => ($is_present[$i] == "true" ? "1" : "0"),
									'description' => $description[$i]
								);
								$insert = $this->main_mod->insert('user_org', $row);
								$i++;
							}
						}
						//AWARD
						$name = $this->input->post('awardname[]') <> null ? $this->input->post('awardname[]') : "";
						$issue = $this->input->post('issue[]') <> null ? $this->input->post('issue[]') : "";
						$description = $this->input->post('awarddesc[]') <> null ? $this->input->post('awarddesc[]') : "";
						$i = 1;
						if (isset($name[1])) {
							foreach ($name as $val) {
								$row = array(
									'user_id' => $data['user_id'],
									'name' => $name[$i],
									'issue' => $issue[$i],
									'description' => $description[$i]
								);
								$insert = $this->main_mod->insert('user_award', $row);
								$i++;
							}
						}
						//COURSES
						$coursename = $this->input->post('coursename[]') <> null ? $this->input->post('coursename[]') : "";
						$hour = $this->input->post('hour[]') <> null ? $this->input->post('hour[]') : "";
						$courseorg = $this->input->post('courseorg[]') <> null ? $this->input->post('courseorg[]') : "";
						$startmonth = $this->input->post('courseperiod[]') <> null ? $this->input->post('courseperiod[]') : "";
						$startyear = $this->input->post('courseyear[]') <> null ? $this->input->post('courseyear[]') : "";
						$endmonth = $this->input->post('courseperiod2[]') <> null ? $this->input->post('courseperiod2[]') : "";
						$endyear = $this->input->post('courseyear2[]') <> null ? $this->input->post('courseyear2[]') : "";
						$i = 1;
						if (isset($coursename[1])) {
							foreach ($coursename as $val) {
								$row = array(
									'user_id' => $data['user_id'],
									'coursename' => $coursename[$i],
									'hour' => $hour[$i],
									'courseorg' => $courseorg[$i],
									'startmonth' => $startmonth[$i],
									'startyear' => $startyear[$i],
									'endmonth' => $endmonth[$i],
									'endyear' => $endyear[$i]
								);
								$insert = $this->main_mod->insert('user_course', $row);
								$i++;
							}
						}
						//PROFESIONAL
						$organization = $this->input->post('proforg[]') <> null ? $this->input->post('proforg[]') : "";
						$type = $this->input->post('proftype[]') <> null ? $this->input->post('proftype[]') : "";
						$position = $this->input->post('profposit[]') <> null ? $this->input->post('profposit[]') : "";
						$startmonth = $this->input->post('profperiod[]') <> null ? $this->input->post('profperiod[]') : "";
						$startyear = $this->input->post('profyear[]') <> null ? $this->input->post('profyear[]') : "";
						$endmonth = $this->input->post('profperiod2[]') <> null ? $this->input->post('profperiod2[]') : "";
						$endyear = $this->input->post('profyear2[]') <> null ? $this->input->post('profyear2[]') : "";
						$subject = $this->input->post('profsubject[]') <> null ? $this->input->post('profsubject[]') : "";
						$description = $this->input->post('profdesc[]') <> null ? $this->input->post('profdesc[]') : "";
						$i = 1;
						if (isset($organization[1])) {
							foreach ($organization as $val) {
								$row = array(
									'user_id' => $data['user_id'],
									'organization' => $organization[$i],
									'type' => $type[$i],
									'position' => $position[$i],
									'subject' => $subject[$i],
									'description' => $description[$i],
									'startmonth' => $startmonth[$i],
									'startyear' => $startyear[$i],
									'endmonth' => $endmonth[$i],
									'endyear' => $endyear[$i]
								);
								$insert = $this->main_mod->insert('user_prof', $row);
								$i++;
							}
						}
						//PUBLICATION
						$topic = $this->input->post('publicationtopic[]') <> null ? $this->input->post('publicationtopic[]') : "";
						$type = $this->input->post('publicationtype[]') <> null ? $this->input->post('publicationtype[]') : "";
						$media = $this->input->post('publicationmedia[]') <> null ? $this->input->post('publicationmedia[]') : "";
						$startmonth = $this->input->post('publicationperiod[]') <> null ? $this->input->post('publicationperiod[]') : "";
						$startyear = $this->input->post('publicationyear[]') <> null ? $this->input->post('publicationyear[]') : "";
						$endmonth = $this->input->post('publicationperiod2[]') <> null ? $this->input->post('publicationperiod2[]') : "";
						$endyear = $this->input->post('publicationyear2[]') <> null ? $this->input->post('publicationyear2[]') : "";
						$journal = $this->input->post('publicationjournal[]') <> null ? $this->input->post('publicationjournal[]') : "";
						$event = $this->input->post('publicationevent[]') <> null ? $this->input->post('publicationevent[]') : "";
						$description = $this->input->post('publicationdesc[]') <> null ? $this->input->post('publicationdesc[]') : "";
						$i = 1;
						if (isset($topic[1])) {
							foreach ($topic as $val) {
								$row = array(
									'user_id' => $data['user_id'],
									'topic' => $topic[$i],
									'media' => $media[$i],
									'type' => $type[$i],
									'journal' => $journal[$i],
									'event' => $event[$i],
									'description' => $description[$i],
									'startmonth' => $startmonth[$i],
									'startyear' => $startyear[$i],
									'endmonth' => $endmonth[$i],
									'endyear' => $endyear[$i]
								);
								$insert = $this->main_mod->insert('user_publication', $row);
								$i++;
							}
						}
						//SKILL
						$name = $this->input->post('skillname[]') <> null ? $this->input->post('skillname[]') : "";
						$proficiency = $this->input->post('proficiency[]') <> null ? $this->input->post('proficiency[]') : "";
						$description = $this->input->post('skilldesc[]') <> null ? $this->input->post('skilldesc[]') : "";
						$i = 1;
						if (isset($name[1])) {
							foreach ($name as $val) {
								$row = array(
									'user_id' => $data['user_id'],
									'name' => $name[$i],
									'proficiency' => $proficiency[$i],
									'description' => $description[$i]
								);
								$insert = $this->main_mod->insert('user_skill', $row);
								$i++;
							}
						}
						//print_r($row);
						$this->_show_message($this->lang->line('auth_message_registration_completed_2') . ' ' . anchor('/auth/login/', 'Login'));
					}
				} else {
					$errors = $this->tank_auth->get_error_message();
					foreach ($errors as $k => $v)	$data['errors'][$k] = $this->lang->line($v);
				}
			}
			if ($captcha_registration) {
				if ($use_recaptcha) {
					$data['recaptcha_html'] = $this->_create_recaptcha();
				} else {
					$data['captcha_html'] = $this->_create_captcha();
				}
			}


			$data['use_username'] = $use_username;
			$data['captcha_registration'] = $captcha_registration;
			$data['use_recaptcha'] = $use_recaptcha;

			$data['m_phone'] = $this->main_mod->msr('m_phone', 'id', 'asc')->result();
			$data['m_address'] = $this->main_mod->msr('m_address', 'id', 'asc')->result();
			$data['m_company'] = $this->main_mod->msr('m_company', 'id', 'asc')->result();
			$data['m_proftype'] = $this->main_mod->msr('m_proftype', 'id', 'asc')->result();
			$data['m_publicjurnal'] = $this->main_mod->msr('m_publicjurnal', 'id', 'asc')->result();
			$data['m_publictype'] = $this->main_mod->msr('m_publictype', 'id', 'asc')->result();

			$data['m_fieldofexpert'] = $this->main_mod->msr('m_fieldofexpert', 'id', 'asc')->result();
			$data['m_accauth'] = $this->main_mod->msr('m_accauth', 'id', 'asc')->result();
			$data['m_subfield'] = $this->main_mod->msr('m_subfield', 'id', 'asc')->result();




			//print_r($this->form_validation);
			$this->load->view('auth/register_form', $data);
		}
	}

	/**
	 * Send activation email again, to the same or new email address
	 *
	 * @return void
	 */
	function send_again()
	{
		if (!$this->tank_auth->is_logged_in(FALSE)) {							// not logged in or activated
			redirect('/auth/login/');
		} else {
			$this->form_validation->set_rules('email', 'Email', 'trim|required|xss_clean|valid_email');

			$data['errors'] = array();

			if ($this->form_validation->run()) {								// validation ok
				if (!is_null($data = $this->tank_auth->change_email(
					$this->form_validation->set_value('email')
				))) {			// success

					$data['site_name']	= $this->config->item('website_name', 'tank_auth');
					$data['activation_period'] = $this->config->item('email_activation_expire', 'tank_auth') / 3600;

					$this->_send_email('activate', $data['email'], $data);

					$this->_show_message(sprintf($this->lang->line('auth_message_activation_email_sent'), $data['email']));
				} else {
					$errors = $this->tank_auth->get_error_message();
					foreach ($errors as $k => $v)	$data['errors'][$k] = $this->lang->line($v);
				}
			}
			$this->load->view('auth/send_again_form', $data);
		}
	}

	/**
	 * Activate user account.
	 * User is verified by user_id and authentication code in the URL.
	 * Can be called by clicking on link in mail.
	 *
	 * @return void
	 */
	function activate()
	{
		$user_id		= $this->uri->segment(3);
		$new_email_key	= $this->uri->segment(4);

		// Activate user
		if ($this->tank_auth->activate_user($user_id, $new_email_key)) {		// success
			$this->tank_auth->logout();
			$this->_show_message($this->lang->line('auth_message_activation_completed') . ' ' . anchor('/auth/login/', 'Login'));
		} else {																// fail
			$this->_show_message($this->lang->line('auth_message_activation_failed'));
		}
	}

	/**
	 * Generate reset code (to change password) and send it to user
	 *
	 * @return void
	 */
	function forgot_password()
	{
		if ($this->tank_auth->is_logged_in()) {									// logged in
			redirect('');
		} elseif ($this->tank_auth->is_logged_in(FALSE)) {						// logged in, not activated
			redirect('/auth/send_again/');
		} else {
			$this->form_validation->set_rules('login', 'Email or login', 'trim|required|xss_clean');

			$data['errors'] = array();

			if ($this->form_validation->run()) {								// validation ok
				if (!is_null($data = $this->tank_auth->forgot_password(
					$this->form_validation->set_value('login')
				))) {

					$data['site_name'] = $this->config->item('website_name', 'tank_auth');

					// Send email with password activation link
					$this->_send_email('forgot_password', $data['email'], $data);

					$this->_show_message($this->lang->line('auth_message_new_password_sent'));
				} else {
					$errors = $this->tank_auth->get_error_message();
					foreach ($errors as $k => $v)	$data['errors'][$k] = $this->lang->line($v);
				}
			}
			$this->load->view('auth/forgot_password_form', $data);
		}
	}

	/**
	 * Replace user password (forgotten) with a new one (set by user).
	 * User is verified by user_id and authentication code in the URL.
	 * Can be called by clicking on link in mail.
	 *
	 * @return void
	 */
	function reset_password()
	{
		$user_id		= $this->uri->segment(3);
		$new_pass_key	= $this->uri->segment(4);

		$this->form_validation->set_rules('new_password', 'New Password', 'trim|required|xss_clean|min_length[' . $this->config->item('password_min_length', 'tank_auth') . ']|max_length[' . $this->config->item('password_max_length', 'tank_auth') . ']|alpha_dash');
		$this->form_validation->set_rules('confirm_new_password', 'Confirm new Password', 'trim|required|xss_clean|matches[new_password]');

		$data['errors'] = array();

		if ($this->form_validation->run()) {								// validation ok
			if (!is_null($data = $this->tank_auth->reset_password(
				$user_id,
				$new_pass_key,
				$this->form_validation->set_value('new_password')
			))) {	// success

				$data['site_name'] = $this->config->item('website_name', 'tank_auth');

				// Send email with new password
				$this->_send_email('reset_password', $data['email'], $data);

				$this->_show_message($this->lang->line('auth_message_new_password_activated') . ' ' . anchor('/auth/login/', 'Login'));
			} else {														// fail
				$this->_show_message($this->lang->line('auth_message_new_password_failed'));
			}
		} else {
			// Try to activate user by password key (if not activated yet)
			if ($this->config->item('email_activation', 'tank_auth')) {
				$this->tank_auth->activate_user($user_id, $new_pass_key, FALSE);
			}

			if (!$this->tank_auth->can_reset_password($user_id, $new_pass_key)) {
				$this->_show_message($this->lang->line('auth_message_new_password_failed'));
			}
		}
		$this->load->view('auth/reset_password_form', $data);
	}

	/**
	 * Change user password
	 *
	 * @return void
	 */
	function change_password()
	{
		if (!$this->tank_auth->is_logged_in()) {								// not logged in or not activated
			redirect('/auth/login/');
		} else {
			$data['title'] = 'PII | Change Password';
			$this->form_validation->set_rules('old_password', 'Old Password', 'trim|required|xss_clean');
			$this->form_validation->set_rules('new_password', 'New Password', 'trim|required|xss_clean|min_length[' . $this->config->item('password_min_length', 'tank_auth') . ']|max_length[' . $this->config->item('password_max_length', 'tank_auth') . ']');
			$this->form_validation->set_rules('confirm_new_password', 'Confirm new Password', 'trim|required|xss_clean|matches[new_password]');

			$data['errors'] = array();

			if ($this->form_validation->run()) {								// validation ok
				if ($this->tank_auth->change_password(
					$this->form_validation->set_value('old_password'),
					$this->form_validation->set_value('new_password')
				)) {	// success
					$this->_show_message($this->lang->line('auth_message_password_changed'));
				} else {														// fail
					$errors = $this->tank_auth->get_error_message();
					foreach ($errors as $k => $v)	$data['errors'][$k] = $this->lang->line($v);
				}
			}
			$this->load->view('auth/change_password_form', $data);
		}
	}

	/**
	 * Change user email
	 *
	 * @return void
	 */
	function change_email()
	{
		if (!$this->tank_auth->is_logged_in()) {								// not logged in or not activated
			redirect('/auth/login/');
		} else {
			$this->form_validation->set_rules('password', 'Password', 'trim|required|xss_clean');
			$this->form_validation->set_rules('email', 'Email', 'trim|required|xss_clean|valid_email');

			$data['errors'] = array();

			if ($this->form_validation->run()) {								// validation ok
				if (!is_null($data = $this->tank_auth->set_new_email(
					$this->form_validation->set_value('email'),
					$this->form_validation->set_value('password')
				))) {			// success

					$data['site_name'] = $this->config->item('website_name', 'tank_auth');

					// Send email with new email address and its activation link
					$this->_send_email('change_email', $data['new_email'], $data);

					$this->_show_message(sprintf($this->lang->line('auth_message_new_email_sent'), $data['new_email']));
				} else {
					$errors = $this->tank_auth->get_error_message();
					foreach ($errors as $k => $v)	$data['errors'][$k] = $this->lang->line($v);
				}
			}
			$this->load->view('auth/change_email_form', $data);
		}
	}

	/**
	 * Replace user email with a new one.
	 * User is verified by user_id and authentication code in the URL.
	 * Can be called by clicking on link in mail.
	 *
	 * @return void
	 */
	function reset_email()
	{
		$user_id		= $this->uri->segment(3);
		$new_email_key	= $this->uri->segment(4);

		// Reset email
		if ($this->tank_auth->activate_new_email($user_id, $new_email_key)) {	// success
			$this->tank_auth->logout();
			$this->_show_message($this->lang->line('auth_message_new_email_activated') . ' ' . anchor('/auth/login/', 'Login'));
		} else {																// fail
			$this->_show_message($this->lang->line('auth_message_new_email_failed'));
		}
	}

	/**
	 * Delete user from the site (only when user is logged in)
	 *
	 * @return void
	 */
	function unregister()
	{
		if (!$this->tank_auth->is_logged_in()) {								// not logged in or not activated
			redirect('/auth/login/');
		} else {
			$this->form_validation->set_rules('password', 'Password', 'trim|required|xss_clean');

			$data['errors'] = array();

			if ($this->form_validation->run()) {								// validation ok
				if ($this->tank_auth->delete_user(
					$this->form_validation->set_value('password')
				)) {		// success
					$this->_show_message($this->lang->line('auth_message_unregistered'));
				} else {														// fail
					$errors = $this->tank_auth->get_error_message();
					foreach ($errors as $k => $v)	$data['errors'][$k] = $this->lang->line($v);
				}
			}
			$this->load->view('auth/unregister_form', $data);
		}
	}

	/**
	 * Show info message
	 *
	 * @param	string
	 * @return	void
	 */
	function _show_message($message)
	{
		$this->session->set_flashdata('message', $message);
		redirect('/auth/');
	}

	/**
	 * Send email message of given type (activate, forgot_password, etc.)
	 *
	 * @param	string
	 * @param	string
	 * @param	array
	 * @return	void
	 */
	function _send_email($type, $email, &$data)
	{
		$this->load->library('email');
		$this->email->from($this->config->item('webmaster_email', 'tank_auth'), $this->config->item('website_name', 'tank_auth'));
		$this->email->reply_to($this->config->item('webmaster_email', 'tank_auth'), $this->config->item('website_name', 'tank_auth'));
		$this->email->to($email);
		$this->email->subject(sprintf($this->lang->line('auth_subject_' . $type), $this->config->item('website_name', 'tank_auth')));
		$this->email->message($this->load->view('email/' . $type . '-html', $data, TRUE));
		$this->email->set_alt_message($this->load->view('email/' . $type . '-txt', $data, TRUE));
		$this->email->send();
	}

	function upload()
	{
		//set preferences
		/*$config['upload_path'] = './uploads/';
        $config['allowed_types'] = 'txt|pdf|zip';
        $config['max_size']    = '100';

        //load upload class library
        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('filename'))
        {
            // case - failure
            $upload_error = array('error' => $this->upload->display_errors());
            $this->load->view('upload_file_view', $upload_error);
        }
        else
        {
            // case - success
            $upload_data = $this->upload->data();
            $data['success_msg'] = '<div class="alert alert-success text-center">Your file <strong>' . $upload_data['file_name'] . '</strong> was successfully uploaded!</div>';
            $this->load->view('upload_file_view', $data);
        }
		*/


		$valid_formats_img = array("zip", "pdf", "rar");
		$name = $_FILES['filename']['name'];
		$temp = $name;
		$size = $_FILES['filename']['size'];

		$lastDot = strrpos($name, ".");
		$name = str_replace(".", "", substr($name, 0, $lastDot)) . substr($name, $lastDot);

		$data = array();

		if (strlen($name)) {
			list($txt, $ext) = explode(".", $name);
			if ($size < (710000)) {
				//load upload class library
				$actual_image_name = time() . substr(str_replace(" ", "_", $txt), 5) . "." . $ext;
				$config['upload_path'] = './assets/uploads/';
				$config['allowed_types'] = '*';
				$config['max_size']	= '10024';
				$config['file_name'] = $actual_image_name;
				$config['remove_spaces'] = FALSE;

				$this->load->library('upload', $config);

				if ($this->upload->do_upload('filename')) {
					$data = array("status" => "success", "message" => $actual_image_name, "file" => $temp);
				} else {
					$te = "Please try again. " . $this->upload->display_errors();
					$data = array("status" => "error", "message" => $te);
				}
			} else {
				$data = array("status" => "error", "message" => "Sorry, maximum file size should be 700 KB.");
			}
		} else {
			$data = array("status" => "error", "message" => "Error uploading document. Please try again.");
		}
		return $data;
	}
}

/* End of file auth.php */
/* Location: ./application/controllers/auth.php */
