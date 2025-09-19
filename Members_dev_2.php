<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH . 'libraries/phpass-0.1/PasswordHash.php');

const __BYPASS_AUTH_FOR_TEST__ = FALSE;

const PAY_TYPES = array(
	'1' => 'Pendaftaran Anggota Baru PII',
	'2' => 'Perpanjangan Anggota PII',
	'3' => 'FAIP Assesment Fee',
	'4' => 'FAIP SIP Fee',
	'5' => 'STRI',
	'6' => 'PKB Assesment Fee',
	'7' => 'PKB SIP Fee'
);

// ER: Mencoba mengidentifikasi admin type constants - Duplicate di Dashboard.php
const ADMIN_SUPERADMIN = '0';
const ADMIN_LSKI = '1';
const ADMIN_VERIFIKATOR = '2';
const ADMIN_RESERVED_GROUP3 = '3';
const ADMIN_RESERVED_GROUP4 = '4';
const ADMIN_RESERVED_GROUP5 = '5';
const ADMIN_RESERVED_GROUP6 = '6';
const ADMIN_RESERVED_GROUP7 = '7';
const ADMIN_FINANCE = '8';
const ADMIN_MEMBER = '9';
const ADMIN_SKIP = '10';
const ADMIN_WILAYAH_BK_KOLEKTIF = '11';
const ADMIN_RESERVED_GROUP12 = '12'; //STRI
const ADMIN_RESERVED_GROUP13 = '13'; //STRI
const ADMIN_RESERVED_GROUP14 = '14';
const ADMIN_KOLEKTIF15 = '15';
const ADMIN_RESERVED_GROUP16 = '16'; //PKB
const ADMIN_TYPE_SUPERADMIN = '0';
const ADMIN_TYPE_LSKI = '1';


/**
 * @property Main_model.php main_mod
 * @property Members_model.php members_model
 * @property Payment_model.php payment_model
 * @property Pagination.php pagination
 * @property Ciqrcode.php ciqrcode
 * @property Upload.php upload
 * @property Main_model.php basic
 * @property Faip_model.php faip_model
 * @property Pkb_model.php pkb_model
 * @property Imagick.php imagick
 * @property Image_lib.php image_lib
 * @property Datatables.php datatables
 *
 */
class Members extends CI_Controller
{

	protected $direktur_lski_ids = array(
		'659', //Direktur LSKI, dir.lski@pii.or.id
		//	'670',  //bambang.priatmono@pii.or.id
		//	'707'  // Ade Irfan
	); //671 Ayet, lski.yma@gmail.com
	protected $direktur_lski_ids_2 = array('659', '670', '671');
	//	protected $special_admin_676 = "676";
	protected $special_admin_675 = "675";
	protected $special_admin_673 = "673";
	protected $special_admin_782 = "782";
	protected $special_admin_780 = "780";
	protected $special_admin_731 = "731";
	protected $special_admin_672 = "672";
	protected $special_admin_707 = "707";

	protected $special_admin_lski_direktur = array(
		'659',
		'707'
	);
	protected $special_admin_lski_admin = array(
		'672', //Ruli, rulyahmadj@yahoo.com
		'659'  //Direktur LSKI, dir.lski@pii.or.id
	);


	protected $VALID_FILE_FORMAT = array("jpg", "jpeg", "gif", "png", "pdf", "bmp");


	protected $debug_msg = 'DEBUG is ENABLED. JSON output includes `data`, `detail` and `input`. ' .
		'Please do not rely on those elements';

	function __construct()
	{
		parent::__construct();
		date_default_timezone_set('Asia/Jakarta');
		$this->load->library('form_validation');
		$this->load->library('user_agent');
		$this->load->helper(array('form', 'url', 'utility', 'file', 'security'));
		$this->load->model('members_model');
		$this->load->model('faip_model');
		$this->load->model('faip_return_model');
		$this->load->model('pkb_model');
		$this->load->model('payment_model');

		// TODO: FIXME: Ini bikin problem karena parameter `username` dipakai oleh filter search fragment
		// Sementara ubah `username` ke `secret_username`
		if (__BYPASS_AUTH_FOR_TEST__ && $this->input->get('secret_username')) {
			// Instruction: Put only secret_username in as url param for testing
			$username = $this->input->get('secret_username');
			$result = $this->db
				->where('LOWER(admin_username)=', strtolower($username))
				->where('status_admin', 1)->get('admin');
			if ($result->num_rows() == 1) {
				$user = $result->row();
				$admin_data = array(
					'admin_id'       => $user->id,
					'name'           => $user->admin_username,
					'type'           => $user->type,
					'code_wilayah'   => $user->code_wilayah,
					'code_bk_hkk'    => $user->code_bk_hkk,
					'kode_kolektif'  => $user->kode_kolektif,
					'is_admin_login' => TRUE
				);

				$this->session->set_userdata($admin_data);
			} else {
				if ($this->input->is_ajax_request()) {
					$this->_rest_response(FALSE, 'SIMX403000', 'Not an admin', REST_Controller::HTTP_UNAUTHORIZED);
				} else {
					$this->session->set_flashdata('error', 'Unauthorized. Username (admin) not found.');
					redirect('admin');
					exit;
				}
			}
		} elseif (!$this->session->userdata('is_admin_login')) {
			$this->session->set_flashdata('error', $this->access_deny_msg());
			redirect('admin');
			exit;
		}
	}

	function access_deny_msg()
	{
		return "You may not have the necessary permissions to view this page/perform this action.";
	}

	/**
	 * All members
	 */
	public function index()
	{
		$data['title'] = ''; //SITE_NAME.': Manage Members';
		$data['msg'] = '';

		$akses = array("0", "1", "2", "9", "10", "11", "14", "15");
		if (!in_array($this->session->userdata('type'), $akses)) {
			if (
				$this->session->userdata('admin_id') != $this->special_admin_676 && $this->session->userdata('admin_id') != $this->special_admin_675 && $this->session->userdata('admin_id') != $this->special_admin_782
				&& $this->session->userdata('admin_id') != $this->special_admin_780 && $this->session->userdata('admin_id') != $this->special_admin_731 && $this->session->userdata('admin_id') != $this->special_admin_672
				&& $this->session->userdata('admin_id') != $this->special_admin_707
			) {
				$this->session->set_flashdata('error', $this->access_deny_msg());
				redirect('admin/dashboard');
				exit;
			}
		}

		$this->load->model('main_mod');
		$admin = $this->main_mod->msrquery('select code_bk_hkk as bk, code_wilayah as wil from admin where id = ' . $this->session->userdata('admin_id'))->result();
		$bk  = (isset($admin[0]->bk) ? $admin[0]->bk : "");
		$wil = (isset($admin[0]->wil) ? $admin[0]->wil : "");
		if (strpos($bk, ',') !== false) {
			$bk = explode(",", $bk);
		}
		if (strpos($wil, ',') !== false) {
			$wil = explode(",", $wil);
		}
		$is_kolektif = isAdminKolektif() || isAdminKolektifRO();
		//print_r($wil);
		//exit();

		//Pagination starts
		//$total_rows = $this->members_model->record_count('users');
		$total_rows = $this->members_model->record_count_v2('users', $bk, $wil, $is_kolektif);
		$config = pagination_configuration(base_url("admin/members"), $total_rows, 10, 3, 5, true);

		$this->pagination->initialize($config);
		$page = ($this->uri->segment(2)) ? $this->uri->segment(3) : 0;
		$page_num = $page - 1;
		$page_num = ($page_num < 0) ? '0' : $page_num;
		$page = $page_num * $config["per_page"];
		$data["links"] = $this->pagination->create_links();
		//Pagination ends

		// Main Query to get the member list
		//$obj_result = $this->members_model->get_all_members($config["per_page"], $page);
		$obj_result = $this->members_model->get_all_members_v2($config["per_page"], $page, $bk, $wil, $is_kolektif);
		$data['result'] = $obj_result;
		$data["total_rows"] = $total_rows;

		//------------------------------------------------------------- Perubahan by IP
		if (isAdminBKWilayahKolektif()) {
			$data["m_filter_cab"] = $this->members_model->get_all_cabang_members_v2($bk, $wil, $is_kolektif);
			$data["m_filter_cab1"] = $this->members_model->get_all_cabang_wilayah();
			$data["m_filter_wil"] = $this->members_model->get_all_per_wilayah($wil); // print_r($wil); exit() ;

		} else {
			$data["m_filter_cab"] = $this->members_model->get_all_cabang_wilayah();
			$data["m_filter_wil"] = $this->members_model->get_all_cabang_wilayah();
		}
		//-----------------------------------------------------------------------------------------------------------------		

		//print_r($data["m_filter_cab"]);
		$data["m_cab"] = $this->members_model->get_all_cabang_wilayah();
		$data["m_bk"] = $this->members_model->get_all_bk();
		$data["m_hkk"] = $this->members_model->get_all_hkk();
		//------------------------------------------------------------------------------------------ Tambahan by IP 
		$data["m_wil"] = $this->members_model->get_all_per_wilayah($wil);
		$data["wilayah"] = $wil;
		$data["kode_bk"] = $bk;
		if ($bk != ""  || !empty(trim($bk))) {
			$nabk = $this->members_model->cari_nabk($bk);
			$data["nabk"] = $nabk->name;
			$data["nobk"] = $nabk->value;
		}
		if ($wil != "" || !empty(trim($wil))) {
			$nacab = $this->members_model->cari_nacab($wil);
			$data["nacab"] = $nacab->name;
		}
		//------------------------------------------------------------------------------------------------------	
		// Get all user admin dengan tipe kolektif (type=11)
		// Digunakan di dropdown list untuk edit mapping user ke  admin kolektif
		$data["m_kolektif"] = $this->members_model->get_all_kolektif();

		// Get kode kolektif anya menampilkan kolektif yang dipakai oleh member saja
		// Jika ada kode kolektif di table m_kolektif tapi tidak ada member yang menggunakannya maka
		// kodenya tidak akan muncul di dropdown list
		$data["kode_kolektif"] = $this->members_model->get_all_kode_kolektif();
		$data["kode_all_kolektif"] = $this->members_model->get_all_kode_kolektif2();
		$this->load->view('admin/members_view', $data);
		return;
	}

	public function search()
	{

		log_message('debug', "[SIMPONI] " . __CLASS__ . "@" . __FUNCTION__ . ", accessBy: " . $this->session->userdata('admin_id') . "input_stream: " . print_r($this->input->input_stream(), TRUE));

		$akses = array("0", "1", "2", "9", "10", "11", "12", "14", "15");
		if (!in_array($this->session->userdata('type'), $akses)) {
			if (
				$this->session->userdata('admin_id') != $this->special_admin_676 && $this->session->userdata('admin_id') != $this->special_admin_676 && $this->session->userdata('admin_id') != $this->special_admin_782
				&& $this->session->userdata('admin_id') != $this->special_admin_780 && $this->session->userdata('admin_id') != $this->special_admin_731 && $this->session->userdata('admin_id') != $this->special_admin_672
				&& $this->session->userdata('admin_id') != $this->special_admin_707
			) {
				$this->session->set_flashdata('error', $this->access_deny_msg());
				redirect('admin/dashboard');
				exit;
			}
		}

		$data['title'] = ''; //SITE_NAME.': Manage Employers';
		$data['msg'] = '';
		$search_name = '';
		$search_email = '';
		$search_kta = '';
		$search_inst = '';
		$search_status = '';
		$search_dob = '';                // Tambahan by Ipur
		$search_filter_cab = '';
		$search_filter_wil = '';
		$search_filter_bk = '';
		$search_filter_hkk = '';
		$search_jenis_anggota = '';
		$search_filter_kolektif = '';
		$search_filter_dob = '';	        // Tambahan by Ipur

		$this->form_validation->set_rules('firstname', 'firstname', 'trim');
		$this->form_validation->set_rules('email', 'email', 'trim');
		$this->form_validation->set_rules('username', 'username', 'trim');
		$this->form_validation->set_rules('inst', 'inst', 'trim');
		$this->form_validation->set_rules('status', 'status', 'trim');
		$this->form_validation->set_rules('filter_cab', 'status', 'trim');
		$this->form_validation->set_rules('filter_wil', 'status', 'trim');
		$this->form_validation->set_rules('filter_bk', 'status', 'trim');
		$this->form_validation->set_rules('filter_hkk', 'status', 'trim');
		$this->form_validation->set_rules('jenis_anggota', 'jenis anggota', 'trim');
		$this->form_validation->set_rules('filter_kolektif', 'filter kolektif', 'trim');
		$this->form_validation->set_rules('filter_dob', 'filter dob', 'trim');
		$this->form_validation->run();

		if ($_GET) {
			$search_name 			= 	$this->input->get('firstname');
			$search_email 			= 	$this->input->get('email');
			$search_kta 			= 	$this->input->get('username');
			$search_inst 			= 	$this->input->get('inst');
			$search_status 			= 	$this->input->get('status');
			$search_filter_cab 		= 	$this->input->get('filter_cab');
			$search_filter_wil 		= 	$this->input->get('filter_wil');
			$search_filter_bk 		= 	$this->input->get('filter_bk');
			$search_filter_hkk 		= 	$this->input->get('filter_hkk');
			$search_jenis_anggota 		= 	$this->input->get('jenis_anggota');
			$search_filter_kolektif 	= 	$this->input->get('filter_kolektif');

			$search_dob 			= 	$this->input->get('dob');		// Tambahan by Ipur
			$search_filter_dob		= 	$this->input->get('filter_dob');	// Tambahan by Ipur
		}
		if (
			$search_name == '' && $search_email == '' && $search_kta == '' && $search_inst == '' && $search_status == '' && $search_dob == '' && $search_filter_bk == '' && $search_filter_hkk == '' && $search_filter_cab == ''
			&& $search_filter_wil == '' && $search_jenis_anggota == '' && $search_filter_kolektif == ''  && $search_filter_dob == ''
		) {
			redirect(base_url('admin/members'));
			return;
		}
		$new_array = array();
		$search_data = array();
		$search_data2 = array();
		if (isset($search_name) && $search_name != '') {
			$search_data['firstname'] = $search_name;
			$search_data2['REPLACE(concat(lower(COALESCE(firstname,"")),lower(COALESCE(lastname,"")))," ","")'] = str_replace(' ', '', strtolower($search_name));
		}
		if (isset($search_email) && $search_email != '') {
			$search_data['email'] = $search_email;
			$search_data2['REPLACE(lower(email)," ","")'] = str_replace(' ', '', strtolower($search_email));
		}
		if (isset($search_kta) && $search_kta != '') {
			$search_data['username'] = $search_kta;
			$search_data2['username'] = ltrim($search_kta, '0');
		}
		if (isset($search_inst) && $search_inst != '') {
			$search_data['inst'] = $search_inst;
			//$search_data2['REPLACE(lower(COALESCE(user_exp.company,""))," ","")']=str_replace(' ','',strtolower($search_inst));
			$search_data2['inst'] = $search_inst;
		}
		if (isset($search_status) && $search_status != '') {
			$search_data['status'] = $search_status;
			$search_data2['status'] = $search_status;
		}
		if (isset($search_filter_cab) && $search_filter_cab != '') {
			$search_data['filter_cab'] = $search_filter_cab;
			$search_data2['filter_cab'] = $search_filter_cab;
		}
		//-----------------------------------------------------------------------------------------------	Tambahan by IP	
		if (isset($search_filter_wil) && $search_filter_wil != '') {
			$search_data['filter_wil'] = $search_filter_wil;
			$search_data2['filter_wil'] = $search_filter_wil;
		}
		if (isset($search_dob) && $search_dob != '') {
			$search_data['dob'] = $search_dob;
			$search_data2['dob'] = ltrim($search_dob);
		}
		//--------------------------------------------------------------------------------				
		if (isset($search_filter_bk) && $search_filter_bk != '') {
			$search_data['filter_bk'] = $search_filter_bk;
			$search_data2['filter_bk'] = $search_filter_bk;
		}
		if (isset($search_filter_hkk) && $search_filter_hkk != '') {
			$search_data['filter_hkk'] = $search_filter_hkk;
			$search_data2['filter_hkk'] = $search_filter_hkk;
		}
		if (isset($search_jenis_anggota) && $search_jenis_anggota != '') {
			$search_data['jenis_anggota'] = $search_jenis_anggota;
			$search_data2['jenis_anggota'] = $search_jenis_anggota;
		}
		if (isset($search_filter_kolektif) && $search_filter_kolektif != '') {
			$search_data['filter_kolektif'] = $search_filter_kolektif;
			$search_data2['kolektif_name_id'] = $search_filter_kolektif;
		}
		$wild_card = '';

		$url_params = implode(
			'&amp;',
			array_map(
				function ($key, $val) {
					return urlencode($key) . '=' . urlencode($val);
				},
				array_keys($search_data),
				$search_data
			)
		);

		log_message('debug', "[SIMPONI] " . __CLASS__ . "@" . __FUNCTION__ . "  start DB query");

		$this->load->model('main_mod');
		$admin = $this->main_mod->msrquery('select code_bk_hkk as bk,code_wilayah as wil from admin where id = ' . $this->session->userdata('admin_id'))->result();
		$bk  = (isset($admin[0]->bk) ? $admin[0]->bk : "");
		$wil = (isset($admin[0]->wil) ? $admin[0]->wil : "");
		if (strpos($bk, ',') !== false) {
			$bk = explode(",", $bk);
		}
		if (strpos($wil, ',') !== false) {
			$wil = explode(",", $wil);
		}

		$is_kolektif = (($this->session->userdata('type') == "11" || $this->session->userdata('type') == "15") ? true : false);

		//Pagination starts
		//$total_rows = $this->members_model->search_record_count('user_profiles',$search_data2);
		$total_rows = $this->members_model->search_record_count_v2('user_profiles', $search_data2, $bk, $wil, $is_kolektif);

		$config = pagination_configuration_search(base_url("admin/members/search/?" . $url_params), $total_rows, 10, 3, 5, true);
		//print_r($search_data);
		$this->pagination->initialize($config);
		$page = $this->input->get('per_page');
		$page_num = $page - 1;
		$page_num = ($page_num < 0) ? '0' : $page_num;
		$page = $page_num * $config["per_page"];
		$data["links"] = $this->pagination->create_links();
		$data["total_rows"] = $total_rows;
		//Pagination ends
		//$obj_result = $this->members_model->search_all_members($config["per_page"], $page, $search_data2, $wild_card);


		log_message('debug', "[SIMPONI] " . __CLASS__ . "@" . __FUNCTION__ . "  start Main query");
		//Main query
		$obj_result = $this->members_model->search_all_members_v2($config["per_page"], $page, $search_data2, $wild_card, $bk, $wil, $is_kolektif);
		$data['result'] = $obj_result;
		$data['search_data'] = $search_data;

		if ($this->session->userdata('type') == "11") {
			$data["m_filter_cab"] = $this->members_model->get_all_cabang_members_v2($bk, $wil, $is_kolektif);
		} else {
			$data["m_filter_cab"] = $this->members_model->get_all_cabang_wilayah();
			//print_r($data["m_filter_cab"]);
		}

		log_message('debug', "[SIMPONI] " . __CLASS__ . "@" . __FUNCTION__ . "  start Lookups query");
		$data["m_cab"] = $this->members_model->get_all_cabang_wilayah();
		// ------------------------------------------------------------------------------------------------------------- Tambahan by IP		
		$data["m_filter_wil"] = $this->members_model->get_all_per_wilayah($wil);
		$data["wilayah"] = $wil;
		$data["kode_bk"] = $bk;
		if ($bk != "") {
			$data["m_filter_cab1"] = $this->members_model->get_all_cabang_wilayah();
		}
		if ($wil != "") {
			$nacab = $this->members_model->cari_nacab($wil);
			$data["nacab"] = $nacab->name;
		}
		if ($bk != "") {
			$nabk = $this->members_model->cari_nabk($bk);
			$data["nabk"] = $nabk->name;
			$data["nobk"] = $nabk->value;
		}
		//-----------------------------------------------------------------------------------------------------------------------------		
		$data["m_bk"] = $this->members_model->get_all_bk();
		$data["m_hkk"] = $this->members_model->get_all_hkk();
		$data["m_kolektif"] = $this->members_model->get_all_kolektif();
		$data["kode_kolektif"] = $this->members_model->get_all_kode_kolektif();
		$data["kode_all_kolektif"] = $this->members_model->get_all_kode_kolektif2();
		$this->load->view('admin/members_view', $data);
		return;
	}

	public function details($id = '')
	{
		if ($this->session->userdata('type') == "7") {
			$this->session->set_flashdata('error', $this->access_deny_msg());
			redirect('admin/dashboard');
			exit;
		}

		$this->load->model('main_mod');
		if (empty($id)) {
			$this->session->set_flashdata('error', 'Input member id is required.');
			redirect('admin/members');
			exit;
		}
		$data['title'] = ''; //SITE_NAME.': Member Details';
		$data['msg'] = '';
		$obj_row = $this->members_model->get_member_by_id($id);
		if ($obj_row) {
			$data['row'] = $obj_row;

			/*$data['user_address']=$this->main_mod->msrwhere('user_address',array('user_id'=>$id),'id','desc')->result();
			//$data['user_award']=$this->main_mod->msrwhere('user_award',array('user_id'=>$id),'id','desc')->result();
			$data['user_cert']=$this->main_mod->msrwhere('user_cert',array('user_id'=>$id),'id','desc')->result();
			//$data['user_course']=$this->main_mod->msrwhere('user_course',array('user_id'=>$id),'id','desc')->result();
			$data['user_edu']=$this->main_mod->msrwhere('user_edu',array('user_id'=>$id),'id','desc')->result();
			$data['user_email']=$this->main_mod->msrwhere('user_email',array('user_id'=>$id),'id','desc')->result();
			$data['user_exp']=$this->main_mod->msrwhere('user_exp',array('user_id'=>$id),'id','desc')->result();
			$data['user_org']=$this->main_mod->msrwhere('user_org',array('user_id'=>$id),'id','desc')->result();
			$data['user_phone']=$this->main_mod->msrwhere('user_phone',array('user_id'=>$id),'id','desc')->result();
			$data['user_prof']=$this->main_mod->msrwhere('user_prof',array('user_id'=>$id),'id','desc')->result();
			//$data['user_publication']=$this->main_mod->msrwhere('user_publication',array('user_id'=>$id),'id','desc')->result();
			$data['user_skill']=$this->main_mod->msrwhere('user_skill',array('user_id'=>$id),'id','desc')->result();
			$data['user_reg']=$this->main_mod->msrwhere('user_reg',array('user_id'=>$id),'id','asc')->result();
			*/
			$data['user_address'] = $this->main_mod->msrwhere('user_address', array('user_id' => $id, 'status' => 1), 'id', 'asc')->result();
			$data['user_email'] = $this->main_mod->msrwhere('contacts', array('user_id' => $id, 'contact_type like "%_email%"' => null, 'status' => 1), 'id', 'asc')->result();
			$data['user_phone'] = $this->main_mod->msrwhere('contacts', array('user_id' => $id, 'contact_type like "%_phone%"' => null, 'status' => 1), 'id', 'asc')->result();
			$data['user_edu'] = $this->main_mod->msrwhere('user_edu', array('user_id' => $id, 'status' => 1), 'id', 'asc')->result();
			$data['user_cert'] = $this->main_mod->msrwhere('user_cert', array('user_id' => $id, 'status' => 1), 'id', 'asc')->result();
			$data['user_exp'] = $this->main_mod->msrwhere('user_exp', array('user_id' => $id, 'status' => 1), 'id', 'asc')->result();
			$data['emailx'] = $obj_row->email;
			/*
			$data['m_phone']=$this->main_mod->msr('m_phone','id','asc')->result();
			$data['m_email']=$this->main_mod->msr('m_email','id','asc')->result();
			$data['m_address']=$this->main_mod->msr('m_address','id','asc')->result();
			$data['m_company']=$this->main_mod->msr('m_company','id','asc')->result();
			$data['m_proftype']=$this->main_mod->msr('m_proftype','id','asc')->result();
			$data['m_publicjurnal']=$this->main_mod->msr('m_publicjurnal','id','asc')->result();
			$data['m_publictype']=$this->main_mod->msr('m_publictype','id','asc')->result();

			$data['m_fieldofexpert']=$this->main_mod->msr('m_fieldofexpert','id','asc')->result();
			$data['m_accauth']=$this->main_mod->msr('m_accauth','id','asc')->result();
			$data['m_subfield']=$this->main_mod->msr('m_subfield','id','asc')->result();
			*/
			$data['m_phone'] = $this->main_mod->msrwhere('m_param', array('code' => 'phone'), 'id', 'asc')->result();
			$data['m_email'] = $this->main_mod->msrwhere('m_param', array('code' => 'email'), 'id', 'asc')->result();
			$data['m_address'] = $this->main_mod->msrwhere('m_param', array('code' => 'address'), 'id', 'asc')->result();
			$data['m_degree'] = $this->main_mod->msrwhere('education_type', null, 'EDUCATION_TYPE_ID', 'asc')->result();

			$this->load->view('admin/members_details_view', $data);
			return;
		}
		redirect('admin/members');
		exit;
	}

	public function details_m($id = '')
	{
		$this->load->model('main_mod');
		if ($id == '') {
			redirect('admin/members');
			exit;
		}
		$data['title'] = ''; //SITE_NAME.': Member Details';
		$data['msg'] = '';
		$obj_row = $this->members_model->get_member_by_kta($id);
		$id = isset($obj_row->user_id) ? $obj_row->user_id : '';
		if ($obj_row) {
			$data['row'] = $obj_row;
			//-----------------------------------
			/*$data['user_address']=$this->main_mod->msrwhere('user_address',array('user_id'=>$id),'id','desc')->result();
			//$data['user_award']=$this->main_mod->msrwhere('user_award',array('user_id'=>$id),'id','desc')->result();
			$data['user_cert']=$this->main_mod->msrwhere('user_cert',array('user_id'=>$id),'id','desc')->result();
			//$data['user_course']=$this->main_mod->msrwhere('user_course',array('user_id'=>$id),'id','desc')->result();
			$data['user_edu']=$this->main_mod->msrwhere('user_edu',array('user_id'=>$id),'id','desc')->result();
			$data['user_email']=$this->main_mod->msrwhere('user_email',array('user_id'=>$id),'id','desc')->result();
			$data['user_exp']=$this->main_mod->msrwhere('user_exp',array('user_id'=>$id),'id','desc')->result();
			$data['user_org']=$this->main_mod->msrwhere('user_org',array('user_id'=>$id),'id','desc')->result();
			$data['user_phone']=$this->main_mod->msrwhere('user_phone',array('user_id'=>$id),'id','desc')->result();
			$data['user_prof']=$this->main_mod->msrwhere('user_prof',array('user_id'=>$id),'id','desc')->result();
			//$data['user_publication']=$this->main_mod->msrwhere('user_publication',array('user_id'=>$id),'id','desc')->result();
			$data['user_skill']=$this->main_mod->msrwhere('user_skill',array('user_id'=>$id),'id','desc')->result();
			$data['user_reg']=$this->main_mod->msrwhere('user_reg',array('user_id'=>$id),'id','asc')->result();
			*/
			//-------------------------------------------
			$data['user_address'] = $this->main_mod->msrwhere('user_address', array('user_id' => $id, 'status' => 1), 'id', 'asc')->result();
			$data['user_email'] = $this->main_mod->msrwhere('contacts', array('user_id' => $id, 'contact_type like "%_email%"' => null, 'status' => 1), 'id', 'asc')->result();
			$data['user_phone'] = $this->main_mod->msrwhere('contacts', array('user_id' => $id, 'contact_type like "%_phone%"' => null, 'status' => 1), 'id', 'asc')->result();
			$data['user_edu'] = $this->main_mod->msrwhere('user_edu', array('user_id' => $id, 'status' => 1), 'id', 'asc')->result();
			$data['user_cert'] = $this->main_mod->msrwhere('user_cert', array('user_id' => $id, 'status' => 1), 'id', 'asc')->result();
			$data['user_exp'] = $this->main_mod->msrwhere('user_exp', array('user_id' => $id, 'status' => 1), 'id', 'asc')->result();
			$data['emailx'] = $obj_row->email;
			//-----------------------------------------
			/*
			$data['m_phone']=$this->main_mod->msr('m_phone','id','asc')->result();
			$data['m_email']=$this->main_mod->msr('m_email','id','asc')->result();
			$data['m_address']=$this->main_mod->msr('m_address','id','asc')->result();
			$data['m_company']=$this->main_mod->msr('m_company','id','asc')->result();
			$data['m_proftype']=$this->main_mod->msr('m_proftype','id','asc')->result();
			$data['m_publicjurnal']=$this->main_mod->msr('m_publicjurnal','id','asc')->result();
			$data['m_publictype']=$this->main_mod->msr('m_publictype','id','asc')->result();

			$data['m_fieldofexpert']=$this->main_mod->msr('m_fieldofexpert','id','asc')->result();
			$data['m_accauth']=$this->main_mod->msr('m_accauth','id','asc')->result();
			$data['m_subfield']=$this->main_mod->msr('m_subfield','id','asc')->result();
			*/
			//--------------------------------------------
			$data['m_phone'] = $this->main_mod->msrwhere('m_param', array('code' => 'phone'), 'id', 'asc')->result();
			$data['m_email'] = $this->main_mod->msrwhere('m_param', array('code' => 'email'), 'id', 'asc')->result();
			$data['m_address'] = $this->main_mod->msrwhere('m_param', array('code' => 'address'), 'id', 'asc')->result();
			$data['m_degree'] = $this->main_mod->msrwhere('education_type', null, 'EDUCATION_TYPE_ID', 'asc')->result();

			$this->load->view('admin/members_details_view', $data);
			return;
		}
		redirect('admin/members');
		exit;
	}

	public function update($id = '')
	{
		$this->load->model('main_mod');

		if ($this->session->userdata('type') != "0" && $this->session->userdata('type') != "2" && $this->session->userdata('type') != "11") {
			redirect('admin/members');
			exit;
		}

		if ($id == '') {
			redirect('admin/members');
			exit;
		}

		$data['title'] = ''; //SITE_NAME.': Edit Employer Details';
		$data['msg'] = '';
		/*
		$this->form_validation->set_rules('full_name', 'full name', 'trim|required');
		$this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
		$this->form_validation->set_rules('password', 'Password', 'trim|required');
		$this->form_validation->set_rules('mobile_phone', 'mobile number', 'trim|required');
		$this->form_validation->set_rules('country', 'Country', 'trim|required');
		$this->form_validation->set_rules('city', 'City', 'trim|required');*/
		$this->form_validation->set_error_delimiters('<span class="err" style="padding-left:2px;">', '</span>');

		if ($this->form_validation->run() === FALSE) {

			$obj_row = $this->members_model->get_member_by_id($id);
			//$obj_cities = $this->cities_model->get_all_cities();
			//$obj_countries = $this->countries_model->get_all_countries();
			//$obj_industries = $this->industries_model->get_all_industries();

			$data['row'] = $obj_row;
			//$data['result_cities'] = $obj_cities;
			//$data['result_countries'] = $obj_countries;
			//$data['result_industries'] = $obj_industries;
			/*$data['user_address']=$this->main_mod->msrwhere('user_address',array('user_id'=>$id),'id','desc')->result();
			$data['user_award']=$this->main_mod->msrwhere('user_award',array('user_id'=>$id),'id','desc')->result();
			$data['user_cert']=$this->main_mod->msrwhere('user_cert',array('user_id'=>$id),'id','desc')->result();
			$data['user_course']=$this->main_mod->msrwhere('user_course',array('user_id'=>$id),'id','desc')->result();
			$data['user_edu']=$this->main_mod->msrwhere('user_edu',array('user_id'=>$id),'id','desc')->result();
			$data['user_email']=$this->main_mod->msrwhere('user_email',array('user_id'=>$id),'id','desc')->result();
			$data['user_exp']=$this->main_mod->msrwhere('user_exp',array('user_id'=>$id),'id','desc')->result();
			$data['user_org']=$this->main_mod->msrwhere('user_org',array('user_id'=>$id),'id','desc')->result();
			$data['user_phone']=$this->main_mod->msrwhere('user_phone',array('user_id'=>$id),'id','desc')->result();
			$data['user_prof']=$this->main_mod->msrwhere('user_prof',array('user_id'=>$id),'id','desc')->result();
			$data['user_publication']=$this->main_mod->msrwhere('user_publication',array('user_id'=>$id),'id','desc')->result();
			$data['user_skill']=$this->main_mod->msrwhere('user_skill',array('user_id'=>$id),'id','desc')->result();
			$data['user_reg']=$this->main_mod->msrwhere('user_reg',array('user_id'=>$id),'id','asc')->result();
			*/
			$data['user_address'] = $this->main_mod->msrwhere('user_address', array('user_id' => $id, 'status' => 1), 'id', 'asc')->result();
			$data['user_email'] = $this->main_mod->msrwhere('contacts', array('user_id' => $id, 'contact_type like "%_email%"' => null, 'status' => 1), 'id', 'asc')->result();
			$data['user_phone'] = $this->main_mod->msrwhere('contacts', array('user_id' => $id, 'contact_type like "%_phone%"' => null, 'status' => 1), 'id', 'asc')->result();
			$data['user_edu'] = $this->main_mod->msrwhere('user_edu', array('user_id' => $id, 'status' => 1), 'id', 'asc')->result();
			$data['user_cert'] = $this->main_mod->msrwhere('user_cert', array('user_id' => $id, 'status' => 1), 'id', 'asc')->result();
			$data['user_exp'] = $this->main_mod->msrwhere('user_exp', array('user_id' => $id, 'status' => 1), 'id', 'asc')->result();
			$data['emailx'] = $obj_row->email;
			/*$data['m_phone']=$this->main_mod->msr('m_phone','id','asc')->result();
			$data['m_email']=$this->main_mod->msr('m_email','id','asc')->result();
			$data['m_address']=$this->main_mod->msr('m_address','id','asc')->result();
			$data['m_company']=$this->main_mod->msr('m_company','id','asc')->result();
			$data['m_proftype']=$this->main_mod->msr('m_proftype','id','asc')->result();
			$data['m_publicjurnal']=$this->main_mod->msr('m_publicjurnal','id','asc')->result();
			$data['m_publictype']=$this->main_mod->msr('m_publictype','id','asc')->result();

			$data['m_fieldofexpert']=$this->main_mod->msr('m_fieldofexpert','id','asc')->result();
			$data['m_accauth']=$this->main_mod->msr('m_accauth','id','asc')->result();
			$data['m_subfield']=$this->main_mod->msr('m_subfield','id','asc')->result();
			*/
			$data['m_phone'] = $this->main_mod->msrwhere('m_param', array('code' => 'phone'), 'id', 'asc')->result();
			$data['m_email'] = $this->main_mod->msrwhere('m_param', array('code' => 'email'), 'id', 'asc')->result();
			$data['m_address'] = $this->main_mod->msrwhere('m_param', array('code' => 'address'), 'id', 'asc')->result();
			$data['m_degree'] = $this->main_mod->msrwhere('education_type', null, 'EDUCATION_TYPE_ID', 'asc')->result();

			$data['idmember'] = $id;

			$this->load->view('admin/members_edit_view', $data);
			return;
		}
		/*
		$employer_array = array(
								'first_name' => $this->input->post('full_name'),
								'email' => $this->input->post('email'),
								'pass_code' => $this->input->post('password'),
								'mobile_phone' => $this->input->post('mobile_phone'),
								'country' => $this->input->post('country'),
								'city' => $this->input->post('city')
		);*/
		//$this->employers_model->update_member($id, $employer_array);
		$this->session->set_flashdata('update_action', true);
		redirect(base_url('admin/members/update/' . $id));
		return;
	}

	public function non_kta()
	{

		$akses = array("0", "2", "9", "14", "11");
		if (!in_array($this->session->userdata('type'), $akses)) {
			$this->session->set_flashdata('error', $this->access_deny_msg());
			redirect('admin/dashboard');
			exit;
		}

		$data['title'] = ''; //SITE_NAME.': Manage Members';
		$data['msg'] = '';


		$this->load->model('main_mod');
		$admin = $this->main_mod->msrquery('select code_bk_hkk as bk,code_wilayah as wil from admin where id = ' . $this->session->userdata('admin_id'))->result();
		$bk  = (isset($admin[0]->bk) ? $admin[0]->bk : "");
		$wil = (isset($admin[0]->wil) ? $admin[0]->wil : "");
		if (strpos($bk, ',') !== false) {
			$bk = explode(",", $bk);
		}
		if (strpos($wil, ',') !== false) {
			$wil = explode(",", $wil);
		}
		$is_kolektif = (($this->session->userdata('type') == "11" || $this->session->userdata('type') == "15") ? true : false);


		//Pagination starts
		$total_rows = $this->members_model->record_count_non_kta('users', $bk, $wil, $is_kolektif);
		$config = pagination_configuration(base_url("admin/members/non_kta"), $total_rows, 10, 4, 5, true);

		$this->pagination->initialize($config);
		$page = ($this->uri->segment(3)) ? $this->uri->segment(4) : 0;
		$page_num = $page - 1;
		$page_num = ($page_num < 0) ? '0' : $page_num;
		$page = $page_num * $config["per_page"];
		$data["links"] = $this->pagination->create_links();
		//Pagination ends
		//print_r($data["links"]);

		$obj_result = $this->members_model->get_all_non_kta_2($config["per_page"], $page, $bk, $wil, $is_kolektif);
		$data['result'] = $obj_result;
		$data["total_rows"] = $total_rows;

		$data["m_cab"] = $this->members_model->get_all_cabang();
		$data["m_bk"] = $this->members_model->get_all_bk();


		$this->load->view('admin/non_kta_view', $data);
		return;
	}

	public function search_non_kta()
	{
		$akses = array("0", "2", "9", "14", "11");
		if (!in_array($this->session->userdata('type'), $akses)) {
			$this->session->set_flashdata('error', $this->access_deny_msg());
			redirect('admin/dashboard');
			exit;
		}

		$data['title'] = ''; //SITE_NAME.': Manage Employers';
		$data['msg'] = '';
		$search_name = '';
		$search_email = '';

		$this->form_validation->set_rules('firstname', 'firstname', 'trim');
		$this->form_validation->set_rules('email', 'email', 'trim');
		$this->form_validation->run();

		if ($_GET) {
			$search_name 	= 	$this->input->get('firstname');
			$search_email 	= 	$this->input->get('email');
		}
		if ($search_name == '' && $search_email == '') {
			redirect(base_url('admin/members/non_kta'));
			return;
		}
		$new_array = array();
		$search_data = array();
		$search_data2 = array();
		if (isset($search_name) && $search_name != '') {
			$search_data['firstname'] = $search_name;
			//$search_data2['concat(lower(firstname),lower(lastname))']=strtolower($search_name);
			$search_data2['REPLACE(concat(lower(COALESCE(firstname,"")),lower(COALESCE(lastname,"")))," ","")'] = str_replace(' ', '', strtolower($search_name));
		}
		if (isset($search_email) && $search_email != '') {
			$search_data['email'] = $search_email;
			$search_data2['REPLACE(lower(email)," ","")'] = str_replace(' ', '', strtolower($search_email));
		}
		$wild_card = '';

		$url_params = implode(
			'&amp;',
			array_map(
				function ($key, $val) {
					return urlencode($key) . '=' . urlencode($val);
				},
				array_keys($search_data),
				$search_data
			)
		);

		$this->load->model('main_mod');
		$admin = $this->main_mod->msrquery('select code_bk_hkk as bk,code_wilayah as wil from admin where id = ' . $this->session->userdata('admin_id'))->result();
		$bk  = (isset($admin[0]->bk) ? $admin[0]->bk : "");
		$wil = (isset($admin[0]->wil) ? $admin[0]->wil : "");
		if (strpos($bk, ',') !== false) {
			$bk = explode(",", $bk);
		}
		if (strpos($wil, ',') !== false) {
			$wil = explode(",", $wil);
		}
		$is_kolektif = (($this->session->userdata('type') == "11" || $this->session->userdata('type') == "15") ? true : false);

		//Pagination starts
		$total_rows = $this->members_model->search_record_count_non_kta('users', $search_data2, $bk, $wil, $is_kolektif);
		$config = pagination_configuration_search(base_url("admin/members/search_non_kta/?" . $url_params), $total_rows, 10, 3, 5, true);

		$this->pagination->initialize($config);
		$page = $this->input->get('per_page');
		$page_num = $page - 1;
		$page_num = ($page_num < 0) ? '0' : $page_num;
		$page = $page_num * $config["per_page"];
		$data["links"] = $this->pagination->create_links();
		$data["total_rows"] = $total_rows;
		//Pagination ends
		$obj_result = $this->members_model->search_all_non_kta_2($config["per_page"], $page, $search_data2, $wild_card, $bk, $wil, $is_kolektif);
		$data['result'] = $obj_result;
		$data['search_data'] = $search_data;

		$data["m_cab"] = $this->members_model->get_all_cabang();
		$data["m_bk"] = $this->members_model->get_all_bk();

		$this->load->view('admin/non_kta_view', $data);
		return;
	}

	/**
	 * Halaman utama HER Members
	 */
	public function her_kta()
	{
		$akses = array("0", "2", "9", "14", "11");
		if (!in_array($this->session->userdata('type'), $akses)) {
			$this->session->set_flashdata('error', $this->access_deny_msg());
			redirect('admin/dashboard');
			exit;
		}

		$data['title'] = ''; //SITE_NAME.': Manage Members';
		$data['msg'] = '';


		$this->load->model('main_mod');
		$admin = $this->main_mod->msrquery('select code_bk_hkk as bk,code_wilayah as wil from admin where id = ' . $this->session->userdata('admin_id'))->result();
		$bk  = (isset($admin[0]->bk) ? $admin[0]->bk : "");
		$wil = (isset($admin[0]->wil) ? $admin[0]->wil : "");
		if (strpos($bk, ',') !== false) {
			$bk = explode(",", $bk);
		}
		if (strpos($wil, ',') !== false) {
			$wil = explode(",", $wil);
		}
		$is_kolektif = (($this->session->userdata('type') == "11" || $this->session->userdata('type') == "15") ? true : false);


		//Pagination starts
		$total_rows = $this->members_model->record_count_her_kta('users', $bk, $wil, $is_kolektif);
		$config = pagination_configuration(base_url("admin/members/her_kta"), $total_rows, 10, 4, 5, true);

		$this->pagination->initialize($config);
		$page = ($this->uri->segment(3)) ? $this->uri->segment(4) : 0;
		$page_num = $page - 1;
		$page_num = ($page_num < 0) ? '0' : $page_num;
		$page = $page_num * $config["per_page"];
		$data["links"] = $this->pagination->create_links();
		//Pagination ends
		//print_r($data["links"]);

		// Main query
		$obj_result = $this->members_model->get_all_her_kta_orig($config["per_page"], $page, $bk, $wil, $is_kolektif);
		$data['result'] = $obj_result;
		$data["total_rows"] = $total_rows;

		$data["m_cab"] = $this->members_model->get_all_cabang();
		$data["m_bk"] = $this->members_model->get_all_bk();


		$this->load->view('admin/her_kta_view', $data);
		return;
	}

	/**
	 * Halaman utama HER Members
	 */
	public function her_kta_orig()
	{
		$akses = array("0", "2", "9", "14", "11");
		if (!in_array($this->session->userdata('type'), $akses)) {
			$this->session->set_flashdata('error', $this->access_deny_msg());
			redirect('admin/dashboard');
			exit;
		}

		$data['title'] = ''; //SITE_NAME.': Manage Members';
		$data['msg'] = '';


		$this->load->model('main_mod');
		$admin = $this->main_mod->msrquery('select code_bk_hkk as bk,code_wilayah as wil from admin where id = ' . $this->session->userdata('admin_id'))->result();
		$bk  = (isset($admin[0]->bk) ? $admin[0]->bk : "");
		$wil = (isset($admin[0]->wil) ? $admin[0]->wil : "");
		if (strpos($bk, ',') !== false) {
			$bk = explode(",", $bk);
		}
		if (strpos($wil, ',') !== false) {
			$wil = explode(",", $wil);
		}
		$is_kolektif = (($this->session->userdata('type') == "11" || $this->session->userdata('type') == "15") ? true : false);


		//Pagination starts
		$total_rows = $this->members_model->record_count_her_kta('users', $bk, $wil, $is_kolektif);
		$config = pagination_configuration(base_url("admin/members/her_kta"), $total_rows, 10, 4, 5, true);

		$this->pagination->initialize($config);
		$page = ($this->uri->segment(3)) ? $this->uri->segment(4) : 0;
		$page_num = $page - 1;
		$page_num = ($page_num < 0) ? '0' : $page_num;
		$page = $page_num * $config["per_page"];
		$data["links"] = $this->pagination->create_links();
		//Pagination ends
		//print_r($data["links"]);

		// Main query
		$obj_result = $this->members_model->get_all_her_kta_orig($config["per_page"], $page, $bk, $wil, $is_kolektif);
		$data['result'] = $obj_result;
		$data["total_rows"] = $total_rows;

		$data["m_cab"] = $this->members_model->get_all_cabang();
		$data["m_bk"] = $this->members_model->get_all_bk();


		$this->load->view('admin/her_kta_view', $data);
		return;
	}

	public function her_kta_2()
	{
		$data['title'] = ''; //SITE_NAME.': Manage Members';
		$data['msg'] = '';

		//Pagination starts
		$total_rows = $this->members_model->record_count_her_kta_2('users');
		$config = pagination_configuration(base_url("admin/members/her_kta_2"), $total_rows, 10, 4, 5, true);

		$this->pagination->initialize($config);
		$page = ($this->uri->segment(3)) ? $this->uri->segment(4) : 0;
		$page_num = $page - 1;
		$page_num = ($page_num < 0) ? '0' : $page_num;
		$page = $page_num * $config["per_page"];
		$data["links"] = $this->pagination->create_links();
		//Pagination ends
		//print_r($data["links"]);

		$obj_result = $this->members_model->get_all_her_kta_2($config["per_page"], $page);
		$data['result'] = $obj_result;
		$data["total_rows"] = $total_rows;

		$data["m_cab"] = $this->members_model->get_all_cabang();
		$data["m_bk"] = $this->members_model->get_all_bk();


		$this->load->view('admin/her_kta_view_2', $data);
		return;
	}

	/**
	 * Dipanggil di halaman "HER Members" saat searching dari top form diatas tabel
	 */
	public function search_her_kta()
	{
		$akses = array("0", "2", "9", "14", "11");
		if (!in_array($this->session->userdata('type'), $akses)) {
			$this->session->set_flashdata('error', $this->access_deny_msg());
			redirect('admin/dashboard');
			exit;
		}

		$data['title'] = ''; //SITE_NAME.': Manage Employers';
		$data['msg'] = '';
		$search_name = '';
		$search_email = '';

		$search_kta = '';
		$search_filter_cab = '';
		$search_filter_bk = '';


		$this->form_validation->set_rules('username', 'username', 'trim');
		$this->form_validation->set_rules('filter_cab', 'status', 'trim');
		$this->form_validation->set_rules('filter_bk', 'status', 'trim');
		$this->form_validation->set_rules('firstname', 'firstname', 'trim');
		$this->form_validation->set_rules('email', 'email', 'trim');
		$this->form_validation->run();

		if ($_GET) {
			$search_name 	= 	$this->input->get('firstname');
			$search_email 	= 	$this->input->get('email');

			$search_kta 			= 	$this->input->get('username');
			$search_filter_cab 		= 	$this->input->get('filter_cab');
			$search_filter_bk 		= 	$this->input->get('filter_bk');
		}
		if ($search_name == '' && $search_email == '' && $search_kta == '' && $search_filter_cab == '' && $search_filter_bk == '') {
			redirect(base_url('admin/members/her_kta'));
			return;
		}
		$new_array = array();
		$search_data = array();
		$search_data2 = array();
		if (isset($search_name) && $search_name != '') {
			$search_data['firstname'] = $search_name;
			//$search_data2['concat(lower(firstname),lower(lastname))']=strtolower($search_name);
			$search_data2['REPLACE(concat(lower(COALESCE(firstname,"")),lower(COALESCE(lastname,"")))," ","")'] = str_replace(' ', '', strtolower($search_name));
		}
		if (isset($search_email) && $search_email != '') {
			$search_data['email'] = $search_email;
			$search_data2['REPLACE(lower(email)," ","")'] = str_replace(' ', '', strtolower($search_email));
		}

		if (isset($search_kta) && $search_kta != '') {
			$search_data['username'] = $search_kta;
			$search_data2['username'] = ltrim($search_kta, '0');
		}
		if (isset($search_filter_cab) && $search_filter_cab != '') {
			$search_data['filter_cab'] = $search_filter_cab;
			$search_data2['filter_cab'] = $search_filter_cab;
		}
		if (isset($search_filter_bk) && $search_filter_bk != '') {
			$search_data['filter_bk'] = $search_filter_bk;
			$search_data2['filter_bk'] = $search_filter_bk;
		}

		$wild_card = '';

		$url_params = implode(
			'&amp;',
			array_map(
				function ($key, $val) {
					return urlencode($key) . '=' . urlencode($val);
				},
				array_keys($search_data),
				$search_data
			)
		);



		$this->load->model('main_mod');
		$admin = $this->main_mod->msrquery('select code_bk_hkk as bk,code_wilayah as wil from admin where id = ' . $this->session->userdata('admin_id'))->result();
		$bk  = (isset($admin[0]->bk) ? $admin[0]->bk : "");
		$wil = (isset($admin[0]->wil) ? $admin[0]->wil : "");
		if (strpos($bk, ',') !== false) {
			$bk = explode(",", $bk);
		}
		if (strpos($wil, ',') !== false) {
			$wil = explode(",", $wil);
		}
		$is_kolektif = (($this->session->userdata('type') == "11" || $this->session->userdata('type') == "15") ? true : false);


		//Pagination starts
		$total_rows = $this->members_model->search_record_count_her_kta('users', $search_data2, $bk, $wil, $is_kolektif);
		$config = pagination_configuration_search(base_url("admin/members/search_her_kta/?" . $url_params), $total_rows, 10, 3, 5, true);

		$this->pagination->initialize($config);
		$page = $this->input->get('per_page');
		$page_num = $page - 1;
		$page_num = ($page_num < 0) ? '0' : $page_num;
		$page = $page_num * $config["per_page"];
		$data["links"] = $this->pagination->create_links();
		$data["total_rows"] = $total_rows;
		//Pagination ends
		$obj_result = $this->members_model->search_all_her_kta($config["per_page"], $page, $search_data2, $wild_card, $bk, $wil, $is_kolektif);
		$data['result'] = $obj_result;
		$data['search_data'] = $search_data;

		// Lookup tables
		$data["m_cab"] = $this->members_model->get_all_cabang();
		$data["m_bk"] = $this->members_model->get_all_bk();

		$this->load->view('admin/her_kta_view', $data);
		return;
	}

	public function search_her_kta_2()
	{
		$data['title'] = ''; //SITE_NAME.': Manage Employers';
		$data['msg'] = '';
		$search_name = '';
		$search_email = '';

		$this->form_validation->set_rules('firstname', 'firstname', 'trim');
		$this->form_validation->set_rules('email', 'email', 'trim');
		$this->form_validation->run();

		if ($_GET) {
			$search_name 	= 	$this->input->get('firstname');
			$search_email 	= 	$this->input->get('email');
		}
		if ($search_name == '' && $search_email == '') {
			redirect(base_url('admin/members/her_kta_2'));
			return;
		}
		$new_array = array();
		$search_data = array();
		$search_data2 = array();
		if (isset($search_name) && $search_name != '') {
			$search_data['firstname'] = $search_name;
			//$search_data2['concat(lower(firstname),lower(lastname))']=strtolower($search_name);
			$search_data2['REPLACE(concat(lower(COALESCE(firstname,"")),lower(COALESCE(lastname,"")))," ","")'] = str_replace(' ', '', strtolower($search_name));
		}
		if (isset($search_email) && $search_email != '') {
			$search_data['email'] = $search_email;
			$search_data2['REPLACE(lower(email)," ","")'] = str_replace(' ', '', strtolower($search_email));
		}
		$wild_card = '';

		$url_params = implode(
			'&amp;',
			array_map(
				function ($key, $val) {
					return urlencode($key) . '=' . urlencode($val);
				},
				array_keys($search_data),
				$search_data
			)
		);
		//Pagination starts
		$total_rows = $this->members_model->search_record_count_her_kta_2('users', $search_data2);
		$config = pagination_configuration_search(base_url("admin/members/search_her_kta_2/?" . $url_params), $total_rows, 10, 3, 5, true);

		$this->pagination->initialize($config);
		$page = $this->input->get('per_page');
		$page_num = $page - 1;
		$page_num = ($page_num < 0) ? '0' : $page_num;
		$page = $page_num * $config["per_page"];
		$data["links"] = $this->pagination->create_links();
		$data["total_rows"] = $total_rows;
		//Pagination ends

		// ER: Main Query
		$obj_result = $this->members_model->search_all_her_kta_2($config["per_page"], $page, $search_data2, $wild_card);
		$data['result'] = $obj_result;
		$data['search_data'] = $search_data;

		$data["m_cab"] = $this->members_model->get_all_cabang();
		$data["m_bk"] = $this->members_model->get_all_bk();

		$this->load->view('admin/her_kta_view_2', $data);
		return;
	}

	function setherstatus()
	{
		$akses = array("0", "2", "14");
		if (!in_array($this->session->userdata('type'), $akses)) {
			$this->session->set_flashdata('error', $this->access_deny_msg());
			redirect('admin/dashboard');
			exit;
		}

		$idmember = $this->input->post('id') <> null ? $this->input->post('id') : "";
		$status = $this->input->post('status') <> null ? $this->input->post('status') : "";
		$remarks = $this->input->post('remarks') <> null ? $this->input->post('remarks') : "";

		if ($idmember == '') {
			redirect('admin/members/her_kta');
			exit;
		}
		$this->load->model('main_mod');
		if ($idmember != '') {
			try {

				$check = $this->main_mod->msrwhere('user_transfer', array('id' => $idmember), 'id', 'desc')->result();
				if (isset($check[0])) {
					$where = array(
						"id" => $idmember
					);
					$row = array(
						'vnv_status' => $status,
						'remark' => $remarks,
						'modifieddate' => date('Y-m-d H:i:s'),
						'modifiedby' => $this->session->userdata('admin_id'),
					);
					$update = $this->main_mod->update('user_transfer', $where, $row);

					$rowInsert = array(
						'pay_id' => $idmember,
						'old_status' => $check[0]->status,
						'new_status' => $status,
						'notes' => 'anggota',
						'remark' => $remarks,
						'createdby' => $this->session->userdata('admin_id'),
					);
					$this->main_mod->insert('log_status_kta', $rowInsert);
				}

				echo "valid";
			} catch (Exception $e) {
				echo "not valid";
			}
		} else
			echo "not valid";
	}

	function setregstatus()
	{
		$akses = array("0", "2", "14");
		if (!in_array($this->session->userdata('type'), $akses)) {
			$this->session->set_flashdata('error', $this->access_deny_msg());
			redirect('admin/dashboard');
			exit;
		}

		$idmember = $this->input->post('id') <> null ? $this->input->post('id') : "";
		$status = $this->input->post('status') <> null ? $this->input->post('status') : "";
		$remarks = $this->input->post('remarks') <> null ? $this->input->post('remarks') : "";

		if ($idmember == '') {
			redirect('admin/members/non_kta');
			exit;
		}
		$this->load->model('main_mod');
		if ($idmember != '') {
			try {

				$check = $this->main_mod->msrwhere('user_transfer', array('id' => $idmember), 'id', 'desc')->result();
				if (isset($check[0])) {
					$where = array(
						"id" => $idmember
					);
					$row = array(
						'vnv_status' => $status,
						'remark' => $remarks,
						'modifieddate' => date('Y-m-d H:i:s'),
						'modifiedby' => $this->session->userdata('admin_id'),
					);
					$update = $this->main_mod->update('user_transfer', $where, $row);

					$rowInsert = array(
						'pay_id' => $idmember,
						'old_status' => $check[0]->status,
						'new_status' => $status,
						'notes' => 'anggota',
						'remark' => $remarks,
						'createdby' => $this->session->userdata('admin_id'),
					);
					$this->main_mod->insert('log_status_kta', $rowInsert);
				}

				echo "valid";
			} catch (Exception $e) {
				echo "not valid";
			}
		} else
			echo "not valid";
	}


	function download_kta_2()
	{
		$akses = array("0", "2", "11", "1");
		if (!in_array($this->session->userdata('type'), $akses)) {
			$this->session->set_flashdata('error', $this->access_deny_msg());
			redirect('admin/dashboard');
			exit;
		}
		$this->load->model('main_mod');
		$id = $this->input->get('id');
		$members = $this->main_mod->msrwhere('members', array('person_id' => $id, 'status' => 1), 'id', 'desc')->result();
		$user_profiles = $this->main_mod->msrwhere('user_profiles', array('user_id' => $id), 'id', 'desc')->result();
		$users = $this->main_mod->msrwhere('users', array('id' => $id), 'id', 'desc')->row();

		if (isset($members[0]->no_kta) && isset($user_profiles[0]->firstname)) {
			if (((strtotime($members[0]->thru_date) >= strtotime(date('Y-m-d')) || $members[0]->thru_date == '0000-00-00' || substr($members[0]->thru_date, 0, 4) === '3000') && $users->username != '') || $this->session->userdata('type') == "0") {

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


					if (strpos(strtolower($photo_cir), '.jpg') !== false) {
						$filename = FCPATH . './assets/uploads/' . str_replace("_", "\\_", str_replace(" ", "\\ ", $photo_cir));
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



				$fontname = TCPDF_FONTS::addTTFfont(FCPATH . './assets/fonts/CREDC___.ttf', 'credc', '', 96);
				$fontname2 = TCPDF_FONTS::addTTFfont(FCPATH . './assets/fonts/Montserrat-Bold.ttf', 'mont', '', 96);
				$pdf->SetFont($fontname2, '', 14, '', false);

				$tmp = '3px';
				$len = strlen($name);

				if ($len <= 60) $tmp = '11px';

				$temp_jenis = '';

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

	function download_skip()
	{
		$akses = array("0", "2", "11", "1");
		if (!in_array($this->session->userdata('type'), $akses)) {
			$this->session->set_flashdata('error', $this->access_deny_msg());
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
			//strtotime( $members[0]->thru_date ) >= strtotime(date('Y-m-d')) &&
			if ($users->username != '') {

				$name = trim(strtoupper($user_profiles[0]->firstname)) . " " . trim(strtoupper($user_profiles[0]->lastname));
				$name = $user_cert->ip_name != '' ? $user_cert->ip_name : $name;
				$photo = (($user_profiles[0]->photo != '') ? base_url() . 'assets/uploads/' . $user_profiles[0]->photo : "");
				$photo_cir = $user_profiles[0]->photo;
				$no_kta = str_pad($members[0]->code_wilayah, 4, '0', STR_PAD_LEFT) . '.' . str_pad($members[0]->code_bk_hkk, 2, '0', STR_PAD_LEFT) . '.' . str_pad($members[0]->no_kta, 6, '0', STR_PAD_LEFT);


				$phpdate = strtotime($user_cert->startyear);
				$from_date = date('m/y', $phpdate);

				$phpdate = strtotime($user_cert->endyear);
				$thru_date = date('m/y', $phpdate);
				if ($thru_date == '01/70') $thru_date = "01/30";

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
				//	$ketua = 'Dr. -Ing. Ir. Ilham Akbar Habibie, MBA., IPU.';

				$nama_ketua = isset($m_bk_skip->nama_ketua) ? $m_bk_skip->nama_ketua : '';
				$ttd_ketum = FCPATH . './assets/images/tanda_tangan_ketum.jpg';
				//	$ttd_ketum = FCPATH.'./assets/images/Ketum-Ilham.jpg';
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
		  width="43%" style="font-weight:bold;font-size:12px;">Pengurus Badan Kejuruan $nama_bk<br /><span style="font-style: italic;font-size:11px;font-weight:normal;">Board of $nama_bk_en</span></td>
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
				$pdf->Output($no_cert . '.pdf', 'D');
			}
		}
	}


	function report()
	{
		$data['title'] = ''; //SITE_NAME.': Manage Members';
		$data['msg'] = '';

		$akses = array("0", "2");
		if (!in_array($this->session->userdata('type'), $akses)) {
			$this->session->set_flashdata('error', $this->access_deny_msg());
			redirect('admin/dashboard');
			exit;
		}

		$data["m_cab"] = $this->members_model->get_all_cabang();
		$data["m_bk"] = $this->members_model->get_all_bk();
		$this->load->view('admin/report_view', $data);
		return;
	}

	public function get_report()
	{

		$filter['status'] = $_POST['filter_status'];
		$filter['bk'] = $_POST['filter_bk'];
		$filter['cab'] = $_POST['filter_cab'];
		if ($_POST['tgl_period'] != '')
			$filter['tgl_period'] = $_POST['tgl_period'];
		if ($_POST['tgl_period2'] != '')
			$filter['tgl_period2'] = $_POST['tgl_period2'];

		$search = $_POST['search']['value'];
		$limit = $_POST['length'];
		$start = $_POST['start'];
		$order_index = $_POST['order'][0]['column'];
		$order_field = $_POST['columns'][$order_index]['data'];

		$column = ($_POST['columns']);

		$order_ascdesc = $_POST['order'][0]['dir'];
		$sql_total = $this->members_model->count_all_report();
		$sql_data = $this->members_model->filter_report($search, $limit, $start, $order_field, $order_ascdesc, $column, $filter);
		$sql_filter = $this->members_model->count_filter_report($search, $column, $filter);

		//print_r($column);

		$callback = array(
			'draw' => $_POST['draw'],
			'recordsTotal' => $sql_total,
			'recordsFiltered' => $sql_filter,
			'data' => $sql_data
		);
		header('Content-Type: application/json');
		echo json_encode($callback);
	}

	function report_stri()
	{
		$data['title'] = ''; //SITE_NAME.': Manage Members';
		$data['msg'] = '';

		$akses = array("0", "8");
		if (!in_array($this->session->userdata('type'), $akses)) {
			$this->session->set_flashdata('error', $this->access_deny_msg());
			redirect('admin/dashboard');
			exit;
		}

		$data["m_cab"] = $this->members_model->get_all_cabang();
		$data["m_bk"] = $this->members_model->get_all_bk();
		$this->load->view('admin/report_stri_view', $data);
		return;
	}

	public function get_report_stri()
	{

		$filter['status'] = $_POST['filter_status'];
		$filter['bk'] = $_POST['filter_bk'];
		$filter['cab'] = $_POST['filter_cab'];
		if ($_POST['tgl_period'] != '')
			$filter['tgl_period'] = $_POST['tgl_period'];
		if ($_POST['tgl_period2'] != '')
			$filter['tgl_period2'] = $_POST['tgl_period2'];

		$search = $_POST['search']['value'];
		$limit = $_POST['length'];
		$start = $_POST['start'];
		$order_index = $_POST['order'][0]['column'];
		$order_field = $_POST['columns'][$order_index]['data'];

		$column = ($_POST['columns']);

		$order_ascdesc = $_POST['order'][0]['dir'];
		$sql_total = $this->members_model->count_all_report_stri();
		$sql_data = $this->members_model->filter_report_stri($search, $limit, $start, $order_field, $order_ascdesc, $column, $filter);
		$sql_filter = $this->members_model->count_filter_report_stri($search, $column, $filter);

		//print_r($column);

		$callback = array(
			'draw' => $_POST['draw'],
			'recordsTotal' => $sql_total,
			'recordsFiltered' => $sql_filter,
			'data' => $sql_data
		);
		header('Content-Type: application/json');
		echo json_encode($callback);
	}

	/**
	 * @deprecated finance_old
	 */
	public function finance_old()
	{
		$data['title'] = ''; //SITE_NAME.': Manage Members';
		$data['msg'] = '';

		//Pagination starts
		$total_rows = $this->members_model->record_count_non_kta('users');
		$config = pagination_configuration(base_url("admin/members/finance"), $total_rows, 10, 4, 5, true);

		$this->pagination->initialize($config);
		$page = ($this->uri->segment(3)) ? $this->uri->segment(4) : 0;
		$page_num = $page - 1;
		$page_num = ($page_num < 0) ? '0' : $page_num;
		$page = $page_num * $config["per_page"];
		$data["links"] = $this->pagination->create_links();
		//Pagination ends
		//print_r($data["links"]);

		$obj_result = $this->members_model->get_all_non_kta($config["per_page"], $page);
		$data['result'] = $obj_result;
		$data["total_rows"] = $total_rows;

		$data["m_cab"] = $this->members_model->get_all_cabang();
		$data["m_bk"] = $this->members_model->get_all_bk();


		$this->load->view('admin/finance_view', $data);
		return;
	}

	/**
	 * Controller for finance_view
	 */
	public function finance()
	{
		//	$akses = array("0", "8");
		$akses = array("0", "8", "9", "1");
		if (!in_array($this->session->userdata('type'), $akses)) {
			//	if($this->session->userdata('admin_id')!=$this->special_admin_673  && $this->session->userdata('admin_id')!=$this->special_admin_675)
			if (
				$this->session->userdata('admin_id') != $this->special_admin_673  && $this->session->userdata('admin_id') != $this->special_admin_782
				&& $this->session->userdata('admin_id') != $this->special_admin_780 && $this->session->userdata('admin_id') != $this->special_admin_731
				&& $this->session->userdata('admin_id') != $this->special_admin_672
			) {
				redirect('admin/dashboard');
				$this->session->set_flashdata('error', $this->access_deny_msg());
				exit;
			}
		}

		$this->load->model('main_mod');
		$data['title'] = ''; //SITE_NAME.': Manage Members';
		$data['msg'] = '';

		//Pagination starts
		$total_rows = $this->members_model->record_count_finance_2('users');
		$config = pagination_configuration(base_url("admin/members/finance"), $total_rows, 10, 4, 5, true);

		$this->pagination->initialize($config);
		$page = ($this->uri->segment(3)) ? $this->uri->segment(4) : 0;
		$page_num = $page - 1;
		$page_num = ($page_num < 0) ? '0' : $page_num;
		$page = $page_num * $config["per_page"];
		$data["links"] = $this->pagination->create_links();
		//Pagination ends
		//print_r($data["links"]);

		$obj_result = $this->members_model->get_all_finance_2($config["per_page"], $page);
		$data['result'] = $obj_result;
		$data["total_rows"] = $total_rows;

		$data["m_cab"] = $this->members_model->get_all_cabang();
		$data["m_bk"] = $this->members_model->get_all_bk();

		$data["kode_all_kolektif"] = $this->members_model->get_all_kode_kolektif2();

		$this->load->view('admin/finance_view', $data);
		return;
	}

	public function finance_2()
	{
		$data['title'] = ''; //SITE_NAME.': Manage Members';
		$data['msg'] = '';

		//Pagination starts
		$total_rows = $this->members_model->record_count_finance_2('users');
		$config = pagination_configuration(base_url("admin/members/finance_2"), $total_rows, 10, 4, 5, true);

		$this->pagination->initialize($config);
		$page = ($this->uri->segment(3)) ? $this->uri->segment(4) : 0;
		$page_num = $page - 1;
		$page_num = ($page_num < 0) ? '0' : $page_num;
		$page = $page_num * $config["per_page"];
		$data["links"] = $this->pagination->create_links();
		//Pagination ends
		//print_r($data["links"]);

		$obj_result = $this->members_model->get_all_finance_2($config["per_page"], $page);
		$data['result'] = $obj_result;
		$data["total_rows"] = $total_rows;

		$data["m_cab"] = $this->members_model->get_all_cabang();
		$data["m_bk"] = $this->members_model->get_all_bk();


		$this->load->view('admin/finance_view_2', $data);
		return;
	}

	/**
	 * Diakses dari Menu "Validasi (Payment) Finance" saat user melakakukan filter terhadap tabel
	 */
	public function search_non_kta_finance()
	{
		//	$akses = array("0", "8");
		$akses = array("0", "8", "9", "1");
		if (!in_array($this->session->userdata('type'), $akses)) {
			if (
				$this->session->userdata('admin_id') != $this->special_admin_673  && $this->session->userdata('admin_id') != $this->special_admin_675  && $this->session->userdata('admin_id') != $this->special_admin_782
				&& $this->session->userdata('admin_id') != $this->special_admin_780 && $this->session->userdata('admin_id') != $this->special_admin_731 && $this->session->userdata('admin_id') != $this->special_admin_672
			) {
				$this->session->set_flashdata('error', $this->access_deny_msg());
				redirect('admin/dashboard');
				exit;
			}
		}

		$this->load->model('main_mod');
		$data['title'] = ''; //SITE_NAME.': Manage Employers';
		$data['msg'] = '';
		$search_name = '';
		$search_email = '';
		$search_filter_kolektif = '';

		$this->form_validation->set_rules('va', 'va', 'trim');
		$this->form_validation->set_rules('firstname', 'firstname', 'trim');
		$this->form_validation->set_rules('email', 'email', 'trim');
		$this->form_validation->set_rules('filter_type', 'Type', 'trim');
		$this->form_validation->set_rules('filter_status', 'Status', 'trim');
		$this->form_validation->set_rules('filter_kolektif', 'filter kolektif', 'trim');
		$this->form_validation->run();

		if ($_GET) {
			$search_name 	= 	$this->input->get('firstname');
			$search_email 	= 	$this->input->get('email');
			$search_type 	= 	$this->input->get('filter_type');
			$search_status 	= 	$this->input->get('filter_status');
			$search_va 		= 	$this->input->get('va');
			$search_filter_kolektif 	= 	$this->input->get('filter_kolektif');
		}
		if ($search_name == '' && $search_email == '' && $search_type == '' && $search_status == '' && $search_va == '' && $search_filter_kolektif == '') {
			redirect(base_url('admin/members/finance'));
			return;
		}
		$new_array = array();
		$search_data = array();
		$search_data2 = array();
		if (isset($search_name) && $search_name != '') {
			$search_data['firstname'] = $search_name;
			//$search_data2['concat(lower(firstname),lower(lastname))']=strtolower($search_name);
			$search_data2['REPLACE(concat(lower(COALESCE(firstname,"")),lower(COALESCE(lastname,"")))," ","")'] = str_replace(' ', '', strtolower($search_name));
		}
		if (isset($search_email) && $search_email != '') {
			$search_data['email'] = $search_email;
			$search_data2['REPLACE(lower(email)," ","")'] = str_replace(' ', '', strtolower($search_email));
		}
		if (isset($search_type) && $search_type != '') {
			$search_data['filter_type'] = $search_type;
			$search_data2['filter_type'] = $search_type;
		}
		if (isset($search_status) && $search_status != '') {
			$search_data['filter_status'] = $search_status;
			$search_data2['user_transfer.status'] = $search_status;
		}
		if (isset($search_va) && $search_va != '') {
			$search_data['va'] = $search_va;
			$search_data2['user_profiles.va'] = $search_va;
		}
		if (isset($search_filter_kolektif) && $search_filter_kolektif != '') {
			$search_data['filter_kolektif'] = $search_filter_kolektif;
			$search_data2['kolektif_name_id'] = $search_filter_kolektif;
		}
		$wild_card = '';

		$url_params = implode(
			'&amp;',
			array_map(
				function ($key, $val) {
					return urlencode($key) . '=' . urlencode($val);
				},
				array_keys($search_data),
				$search_data
			)
		);

		//Pagination starts
		$total_rows = $this->members_model->search_record_count_finance_2('users', $search_data2);
		$config = pagination_configuration_search(base_url("admin/members/search_non_kta_finance/?" . $url_params), $total_rows, 10, 3, 5, true);

		$this->pagination->initialize($config);
		$page = $this->input->get('per_page');
		$page_num = $page - 1;
		$page_num = ($page_num < 0) ? '0' : $page_num;
		$page = $page_num * $config["per_page"];
		$data["links"] = $this->pagination->create_links();
		$data["total_rows"] = $total_rows;
		//Pagination ends

		// Main query
		$obj_result = $this->members_model->search_all_finance_2($config["per_page"], $page, $search_data2, $wild_card);
		$data['result'] = $obj_result;
		$data['search_data'] = $search_data;
		$data["kode_all_kolektif"] = $this->members_model->get_all_kode_kolektif2();

		$this->load->view('admin/finance_view', $data);
		return;
	}

	public function search_non_kta_finance_2()
	{
		$akses = array("0", "8");
		if (!in_array($this->session->userdata('type'), $akses)) {
			$this->session->set_flashdata('error', $this->access_deny_msg());
			redirect('admin/dashboard');
			exit;
		}

		$data['title'] = ''; //SITE_NAME.': Manage Employers';
		$data['msg'] = '';
		$search_name = '';
		$search_email = '';

		$this->form_validation->set_rules('firstname', 'firstname', 'trim');
		$this->form_validation->set_rules('email', 'email', 'trim');
		$this->form_validation->run();

		if ($_GET) {
			$search_name 	= 	$this->input->get('firstname');
			$search_email 	= 	$this->input->get('email');
		}
		if ($search_name == '' && $search_email == '') {
			redirect(base_url('admin/members/finance_2'));
			return;
		}
		$new_array = array();
		$search_data = array();
		$search_data2 = array();
		if (isset($search_name) && $search_name != '') {
			$search_data['firstname'] = $search_name;
			//$search_data2['concat(lower(firstname),lower(lastname))']=strtolower($search_name);
			$search_data2['REPLACE(concat(lower(COALESCE(firstname,"")),lower(COALESCE(lastname,"")))," ","")'] = str_replace(' ', '', strtolower($search_name));
		}
		if (isset($search_email) && $search_email != '') {
			$search_data['email'] = $search_email;
			$search_data2['REPLACE(lower(email)," ","")'] = str_replace(' ', '', strtolower($search_email));
		}
		$wild_card = '';

		$url_params = implode(
			'&amp;',
			array_map(
				function ($key, $val) {
					return urlencode($key) . '=' . urlencode($val);
				},
				array_keys($search_data),
				$search_data
			)
		);
		//Pagination starts
		$total_rows = $this->members_model->search_record_count_finance_2('users', $search_data2);
		$config = pagination_configuration_search(base_url("admin/members/search_non_kta_finance_2/?" . $url_params), $total_rows, 10, 3, 5, true);

		$this->pagination->initialize($config);
		$page = $this->input->get('per_page');
		$page_num = $page - 1;
		$page_num = ($page_num < 0) ? '0' : $page_num;
		$page = $page_num * $config["per_page"];
		$data["links"] = $this->pagination->create_links();
		$data["total_rows"] = $total_rows;
		//Pagination ends
		$obj_result = $this->members_model->search_all_finance_2($config["per_page"], $page, $search_data2, $wild_card);
		$data['result'] = $obj_result;
		$data['search_data'] = $search_data;

		$this->load->view('admin/finance_view_2', $data);
		return;
	}

	/**
	 * ER:
	 * Dipanggil saat user Admin klik "Set Member" kemudian "Save" di halaman REG Member (aplikan)
	 * insert ke tabel members user_profiles
	 * update ke tabel user_transfer id_management
	 *
	 */
	function setmember()
	{
		$akses = array("0", "2", "14");
		if (!in_array($this->session->userdata('type'), $akses)) {
			$this->session->set_flashdata('error', $this->access_deny_msg());
			redirect('admin/dashboard');
			exit;
		}

		$idmember = $this->input->post('id') <> null ? $this->input->post('id') : "";
		$cabang = $this->input->post('cabang') <> null ? $this->input->post('cabang') : "";
		$bk = $this->input->post('bk') <> null ? $this->input->post('bk') : "";
		$from = $this->input->post('from') <> null ? $this->input->post('from') : "";
		$until = $this->input->post('until') <> null ? $this->input->post('until') : "";
		$nokta = $this->input->post('nokta') <> null ? $this->input->post('nokta') : "";
		$ang = $this->input->post('ang') <> null ? $this->input->post('ang') : "";
		$is_stri = $this->input->post('stri') <> null ? $this->input->post('stri') : "";
		if ($idmember == '') {
			redirect('admin/members');
			exit;
		}
		$this->load->model('main_mod');
		if ($idmember != '') {
			try {
				$idx = $this->members_model->last_kta();
				$idx =  str_pad(($idx->jml + 1), 9, '0', STR_PAD_LEFT);

				$rowInsert = array(
					'person_id' => $idmember,
					'code_wilayah' => str_pad($cabang, 4, '0', STR_PAD_LEFT),
					'code_mitra' => '01',
					'code_bk_hkk' => $bk,
					'years' => date("y"),
					'code_bs' => '',
					//'no_kta' => $idx,
					'no_kta' => $nokta,
					'from_date' => date('Y-m-d', strtotime($from)),
					'thru_date' => date('Y-m-d', strtotime($until)),

					'jenis_anggota' => ($ang == '' ? '01' : $ang),

					'created_by' => $this->session->userdata('admin_id'),
					'created_at' => date('Y-m-d H:i:s'),
				);

				// Cek apakah nomor KTA sudah terpakai - No KTA diinput manual oleh Admin
				$check1 = $this->main_mod->msrwhere('members', array("TRIM(LEADING '0' FROM no_kta)=" => ltrim($nokta, '0')), 'id', 'desc')->result();
				if (isset($check1[0])) {
					echo "not valid kta";
				} else {

					// Cek apakah sudah ada payment transfer dari aplikan
					$check_transfer = $this->main_mod->msrwhere('user_transfer', array('user_id' => $idmember, 'pay_type' => '1'), 'id', 'desc')->result();
					$is_transfer = 0;
					if (isset($check_transfer[0])) {
						if ($check_transfer[0]->status == '1') $is_transfer = 1;

						// Kalau sudah ada update pay_type =1 dan vnv_status =1
						$this->main_mod->update(
							'user_transfer',
							array('user_id' => $idmember, 'pay_type' => '1'),
							array(
								'vnv_status' => 1,
								'modifieddate' => date('Y-m-d H:i:s'),
								'modifiedby' => $this->session->userdata('admin_id')
								//,'is_upload_mandiri'=>1
							)
						);
					}

					$check = $this->main_mod->msrwhere('members', array('person_id' => $idmember), 'id', 'desc')->result();
					if (isset($check[0])) {
						$where = array(
							"person_id" => $idmember
						);
						$row = array(
							'status' => 0,
							'updated_at' => date('Y-m-d H:i:s'),
							'updated_by' => $this->session->userdata('admin_id'),
						);
						$update = $this->main_mod->update('members', $where, $row);

						// The main QUERY to Execute!! Create member data
						$this->members_model->insert_kta('members', $rowInsert, $is_transfer);

						//UPDATE NOMOR VA
						$where = array(
							"user_id" => $idmember
						);

						$row = array(
							'va' => generate_va($cabang, $bk, $nokta),
							'modifieddate' => date('Y-m-d H:i:s'),
							'modifiedby' => $this->session->userdata('admin_id'),
						);
						$update = $this->main_mod->update('user_profiles', $where, $row);
					} else {
						$this->members_model->insert_kta('members', $rowInsert, $is_transfer);

						//UPDATE NOMOR VA
						$where = array(
							"user_id" => $idmember
						);


						$row = array(
							'va' => generate_va($cabang, $bk, $nokta),
							'modifieddate' => date('Y-m-d H:i:s'),
							'modifiedby' => $this->session->userdata('admin_id'),
						);
						$update = $this->main_mod->update('user_profiles', $where, $row);
					}

					if ($is_stri == '1') {
						$check_kta = $this->main_mod->msrwhere('members', array('person_id' => $idmember), 'id', 'desc')->result();
						//REMOVE BECAUSE VA
						if (isset($check_kta[0])) {
							$this->db->set('username', $check_kta[0]->no_kta);
							$this->db->where('id', $idmember);
							$this->db->update('users');
						}
					}

					//if(isset($check_transfer[0])){
					//SEND Mailer
					//$this->send_mail_va($check_transfer[0]->user_id, $check_transfer[0]->pay_type, $check_transfer[0]->sukarelatotal);
					//}

					echo "valid";
				}
			} catch (Exception $e) {
				//print_r($e);
				echo "not valid";
			}
		} else
			echo "not valid";
	}

	function setpayment()
	{
		$akses = array("0", "8", "9");
		if (!in_array($this->session->userdata('type'), $akses)) {
			if (
				$this->session->userdata('admin_id') != $this->special_admin_673 && $this->session->userdata('admin_id') != $this->special_admin_675
				&& $this->session->userdata('admin_id') != $this->special_admin_780 && $this->session->userdata('admin_id') != $this->special_admin_782
			) {
				$this->session->set_flashdata('error', $this->access_deny_msg());
				redirect('admin/dashboard');
				exit;
			}
		}

		$idmember = $this->input->post('id') <> null ? $this->input->post('id') : "";
		$payment = $this->input->post('payment') <> null ? $this->input->post('payment') : "";
		$this->load->model('main_mod');
		//if($idmember!=''){
		try {

			$checkStatus = $this->main_mod->msrwhere('user_transfer', array('id' => $idmember), 'id', 'desc')->result();

			if (!isset($checkStatus[0])) {
				echo "not valid";
			} else {
				$where = array(
					"id" => $idmember
				);
				$row = array();
				$row = array(
					'status' => $payment,
					'modifieddate' => date('Y-m-d H:i:s'),
					'modifiedby' => $this->session->userdata('admin_id'),
				);
				$update = $this->main_mod->update('user_transfer', $where, $row);

				if ($checkStatus[0]->pay_type == "3" && $payment == "1") {

					$check = $this->main_mod->msrwhere('user_faip', array('id' => $checkStatus[0]->rel_id), 'id', 'desc')->result();
					$rowInsert = array(
						'faip_id' => $checkStatus[0]->rel_id,
						'old_status' => $check[0]->status_faip,
						'new_status' => 5,
						'notes' => 'finance',
						'createdby' => $this->session->userdata('admin_id'),
					);
					$this->main_mod->insert('log_status_faip', $rowInsert);

					if ($check[0]->status_faip <= 5) {
						$where = array(
							"id" => $checkStatus[0]->rel_id
						);
						$row = array(
							'status_faip' => 5,
							//'remarks' => $remarks,
							'modifieddate' => date('Y-m-d H:i:s'),
							'modifiedby' => $this->session->userdata('admin_id'),
						);
						$update = $this->main_mod->update('user_faip', $where, $row);
					}
				} else if ($checkStatus[0]->pay_type == "4" && $payment == "1") {
					$check = $this->main_mod->msrwhere('user_faip', array('id' => $checkStatus[0]->rel_id), 'id', 'desc')->result();
					$rowInsert = array(
						'faip_id' => $checkStatus[0]->rel_id,
						'old_status' => $check[0]->status_faip,
						'new_status' => 11,
						'notes' => 'finance',
						'createdby' => $this->session->userdata('admin_id'),
					);
					$this->main_mod->insert('log_status_faip', $rowInsert);

					if ($check[0]->status_faip <= 11) {
						$where = array(
							"id" => $checkStatus[0]->rel_id
						);
						$row = array(
							'status_faip' => 11,
							//'remarks' => $remarks,
							'modifieddate' => date('Y-m-d H:i:s'),
							'modifiedby' => $this->session->userdata('admin_id'),
						);
						$update = $this->main_mod->update('user_faip', $where, $row);
					}
				} else if ($checkStatus[0]->pay_type == "1" || $checkStatus[0]->pay_type == "2") {
					$rowInsert = array(
						'pay_id' => $idmember,
						'old_status' => $checkStatus[0]->status,
						'new_status' => $payment,
						'notes' => 'finance',
						'createdby' => $this->session->userdata('admin_id'),
					);
					$this->main_mod->insert('log_status_kta', $rowInsert);

					if ($checkStatus[0]->pay_type == "1" && $payment == "1") {
						$check_kta = $this->main_mod->msrwhere('members', array('person_id' => $checkStatus[0]->user_id), 'id', 'desc')->result();
						//REMOVE BECAUSE VA
						if (isset($check_kta[0])) {
							$this->db->set('username', $check_kta[0]->no_kta);
							$this->db->where('id', $checkStatus[0]->user_id);
							$this->db->update('users');
						}
					} else if ($checkStatus[0]->pay_type == "2" && $payment == "1") {
						$check_log = $this->main_mod->msrwhere('log_her_kta', array('id_pay' => $idmember), 'id', 'desc')->result();
						//REMOVE BECAUSE VA
						if (isset($check_log[0])) {
							$where = array(
								"person_id" => $checkStatus[0]->user_id
							);
							$row = array(
								'from_date' => date('Y-m-d', strtotime($check_log[0]->from_date)),
								'thru_date' => date('Y-m-d', strtotime($check_log[0]->thru_date)),
								'updated_at' => date('Y-m-d H:i:s'),
								'updated_by' => $this->session->userdata('admin_id'),
							);
							$update = $this->main_mod->update('members', $where, $row);
						}
					}
				} else if ($checkStatus[0]->pay_type == "6" && $payment == "1") {

					$check = $this->main_mod->msrwhere('user_pkb', array('id' => $checkStatus[0]->rel_id), 'id', 'desc')->result();
					$rowInsert = array(
						'pkb_id' => $checkStatus[0]->rel_id,
						'old_status' => $check[0]->status_pkb,
						'new_status' => 5,
						'notes' => 'finance',
						'createdby' => $this->session->userdata('admin_id'),
					);
					$this->main_mod->insert('log_status_pkb', $rowInsert);


					$where = array(
						"id" => $checkStatus[0]->rel_id
					);
					$row = array(
						'status_pkb' => 5,
						//'remarks' => $remarks,
						'modifieddate' => date('Y-m-d H:i:s'),
						'modifiedby' => $this->session->userdata('admin_id'),
					);
					$update = $this->main_mod->update('user_pkb', $where, $row);
				} else if ($checkStatus[0]->pay_type == "7" && $payment == "1") {
					$where = array(
						"id" => $checkStatus[0]->rel_id
					);
					$row = array(
						'status_pkb' => 11,
						//'remarks' => $remarks,
						'modifieddate' => date('Y-m-d H:i:s'),
						'modifiedby' => $this->session->userdata('admin_id'),
					);
					$update = $this->main_mod->update('user_pkb', $where, $row);
				}

				echo "valid";
			}
		} catch (Exception $e) {
			//print_r($e);
			echo "not valid";
		}
	}

	/**
	 * Tampilkan halaman VA, tanpa loading data VA.
	 * Data VA yang tampil akan dipanggil kemudian via Ajax get_va()
	 */
	function va()
	{
		$data['title'] = ''; //SITE_NAME.': Manage Members';
		$data['msg'] = '';

		$akses = array("0", "1", "2", "12", "16", "14", "9");
		if (!in_array($this->session->userdata('type'), $akses)) {
			$this->session->set_flashdata('error', $this->access_deny_msg());
			redirect('admin/dashboard');
			exit;
		}

		// Load lookup tables
		$data["m_cab"] = $this->members_model->get_all_cabang();
		$data["m_bk"] = $this->members_model->get_all_bk();

		$this->load->view('admin/va_view', $data);
		return;
	}

	/**
	 * Mengambil semua data VA untuk ditampilkan di tabel pada halaman VA
	 */
	public function get_va()
	{

		$filter['status'] = $_POST['filter_status'];
		$filter['bk'] = $_POST['filter_bk'];
		$filter['cab'] = $_POST['filter_cab'];
		if ($_POST['tgl_period'] != '')
			$filter['tgl_period'] = $_POST['tgl_period'];
		if ($_POST['tgl_period2'] != '')
			$filter['tgl_period2'] = $_POST['tgl_period2'];

		$search = $_POST['search']['value'];
		$limit = $_POST['length'];
		$start = $_POST['start'];
		$order_index = $_POST['order'][0]['column'];
		$order_field = $_POST['columns'][$order_index]['data'];

		$column = ($_POST['columns']);

		$order_ascdesc = $_POST['order'][0]['dir'];

		// 20240707 - Moved from members_model to payment_model
		$sql_total = $this->payment_model->count_all_va();
		$sql_data = $this->payment_model->filter_va($search, $limit, $start, $order_field, $order_ascdesc, $column, $filter);
		$sql_filter = $this->payment_model->count_filter_va($search, $column, $filter);

		//print_r($column);

		$callback = array(
			'draw' => $_POST['draw'],
			'recordsTotal' => $sql_total,
			'recordsFiltered' => $sql_filter,
			'data' => $sql_data
		);
		header('Content-Type: application/json');
		echo json_encode($callback);
	}

	/**
	 * Check if user have permission to access page/action
	 * ER:
	 */
	function is_action_allowed($arrAccessList, $funcName = "", $redirectURL = "admin/dashboard")
	{
		if (!in_array($this->session->userdata('type'), $arrAccessList)) {
			//TODO: Add CI logging with $funcName
			log_message('info', '[SIMPONI] Access denied: ' . $funcName);
			$this->session->set_flashdata('error', $this->access_deny_msg());
			redirect($redirectURL);
			return FALSE;
		} else {
			return TRUE;
		}
	}

	function debug()
	{
		return; // DISABLE - TODO: fixme
		if (strpos($_SERVER['REQUEST_URI'], 'debug') !== false) {
			$userSession = json_encode($this->session);
			$dumpvar = array_merge(
				json_decode($userSession, true),
				array("request" => $this->input->request_headers()),
				array("body" => $this->input->raw_input_stream)
			);

			$this->output
				->set_content_type('application/json')
				->set_output(json_encode($dumpvar))
				->display();
		}
	}

	/**
	 * Show "SET STRI" main page
	 */
	function set_stri()
	{
		$data['title'] = ''; //SITE_NAME.': Manage Members';
		$data['msg'] = '';

		$this->debug();
		log_message('info', '[SIMPONI] set_stri');

		$akses = array("0", "12");
		if (! $this->is_action_allowed($akses, __FUNCTION__, "admin/dashboard")) {
			exit;
		}

		$data["m_cab"] = $this->members_model->get_all_cabang();
		$data["m_bk"] = $this->members_model->get_all_bk();
		$this->load->view('admin/setstri_view', $data);
		return;
	}

	/**
	 * Fill in the "SET STRI" table view with data
	 * Called after "SET STRI" main page (`set_stri()`) is loaded in browser
	 *
	 * return JSON data
	 */
	public function get_setstri()
	{

		$akses = array("0", "12");
		if (! $this->is_action_allowed($akses, __FUNCTION__, "admin/dashboard")) {
			exit;
		}

		$filter['status'] = $_POST['filter_status'];
		$filter['bk'] = $_POST['filter_bk'];
		$filter['cab'] = $_POST['filter_cab'];
		if ($_POST['tgl_period'] != '')
			$filter['tgl_period'] = $_POST['tgl_period'];
		if ($_POST['tgl_period2'] != '')
			$filter['tgl_period2'] = $_POST['tgl_period2'];

		$search = $_POST['search']['value'];
		$limit = $_POST['length'];
		$start = $_POST['start'];
		$order_index = $_POST['order'][0]['column'];
		$order_field = $_POST['columns'][$order_index]['data'];

		$column = ($_POST['columns']);

		$order_ascdesc = $_POST['order'][0]['dir'];
		$sql_total = $this->members_model->count_all_setstri();
		$sql_data = $this->members_model->filter_setstri($search, $limit, $start, $order_field, $order_ascdesc, $column, $filter);
		$sql_filter = $this->members_model->count_filter_setstri($search, $column, $filter);

		//print_r($column);

		$callback = array(
			'draw' => $_POST['draw'],
			'recordsTotal' => $sql_total,
			'recordsFiltered' => $sql_filter,
			'data' => $sql_data
		);
		header('Content-Type: application/json');
		echo json_encode($callback);
	}

	/**
	 * Called when a user (admin) selects item(s) in "Set STRI" main page's table, then click button 'Set STRI'
	 */
	function set_stri_kolektif()
	{
		// ER: Check if user role has access to this
		$accessCode = array("0", "12");
		if (!in_array($this->session->userdata('type'), $accessCode)) {
			redirect("admin/dashboard?msg=" . urlencode($this->access_deny_msg()));
			exit;
		}

		$id_total = $this->input->post('id') <> null ? $this->input->post('id') : "";
		if ($id_total == '') {
			//redirect('admin/members'); // ER: Commented. I prefer to stay in the same page
			redirect("admin/members/set_stri?msg=" . urlencode("STRI Id is not specified in the request param"));
			exit;
		}
		$this->load->model('main_mod');

		try {
			$idx = $this->members_model->last_stri();

			$skip = $this->members_model->get_stri_member_by_id($id_total);
			if (isset($skip[0]))
				$skip = $skip[0];


			$ck = $this->members_model->check_last_stri($idx->jml + 1);
			$tm = $idx->jml + 1;
			if (isset($ck->stri_id)) {
				$x = 1;
				$tm++;
				do {
					$ck = $this->members_model->check_last_stri($tm);
					if (!isset($ck->stri_id)) {
						$x = 2;
					}
				} while ($x <= 1);
			}

			$num = ltrim($tm, '0');
			$num =  str_pad(($num), 7, '0', STR_PAD_LEFT);

			//$num = ltrim($idx->jml, '0');
			//$num =  str_pad(($num+1), 7, '0', STR_PAD_LEFT);


			$stri_kp = isset($skip->ip_rev) ? $skip->ip_rev : '0';

			$check = $this->main_mod->msrwhere('members_certificate', array("LPAD(stri_pm, 2, '0') = '" . str_pad($stri_kp, 2, '0', STR_PAD_LEFT) . "'" => null, "LPAD(stri_id, 7, '0') = '" . str_pad($num, 7, '0', STR_PAD_LEFT) . "'" => null, 'status' => 1), 'id', 'desc')->result();

			$tgl_sk = '';

			if (!isset($check[0])) {
				$trf = $this->main_mod->msrwhere('user_transfer', array("id" => $id_total), 'id', 'desc')->row();
				$idmember = $trf->user_id;
				$kta = $this->main_mod->msrwhere('members', array("person_id" => $idmember), 'id', 'desc')->row();
				//$kta = (isset($kta->no_kta)?$kta->no_kta:"");
				$prof = $this->main_mod->msrwhere('user_profiles', array("user_id" => $idmember), 'id', 'desc')->row();
				$stri_tipe = $this->main_mod->msrquery('select id from user_edu where type=2 and user_id = ' . $idmember . ' and (fieldofstudy like "%profesi insinyur%" or title_prefix like "%ir%")')->row();
				$th = date('y');

				$rowInsert = array();

				if (isset($skip->ip_seq)) {

					$th = substr($skip->startyear, 0, 4);
					$th = substr($th, -2);

					$rowInsert = array(
						'person_id' => $idmember,

						'no_kta' => $kta->no_kta,
						'certificate_type' => $skip->ip_tipe,

						'skip_id' => str_pad($skip->ip_seq, 6, '0', STR_PAD_LEFT),
						'skip_code_wilayah' => str_pad($skip->ip_kta_wilcab, 4, '0', STR_PAD_LEFT),
						'skip_code_bk_hkk' => str_pad($skip->ip_bk, 2, '0', STR_PAD_LEFT),
						'skip_pm' => str_pad($skip->ip_rev, 2, '0', STR_PAD_LEFT),
						//'skip_sub_code_bk_hkk' => $stri_subbk,
						'skip_sk' => $skip->startyear,
						'skip_from_date' => $skip->startyear,
						'skip_thru_date' => $skip->endyear,


						'stri_id' => str_pad($num, 7, '0', STR_PAD_LEFT),
						'stri_code_wilayah' => str_pad($kta->code_wilayah, 4, '0', STR_PAD_LEFT),
						'stri_code_bk_hkk' => str_pad($skip->ip_bk, 2, '0', STR_PAD_LEFT),
						'stri_pm' => $skip->ip_rev,
						'stri_tipe' => isset($stri_tipe->id) ? 2 : 1,
						'warga' => $prof->warga_asing == 1 ? 2 : 1,
						'add_name' => trim(strtoupper($prof->firstname)) . " " . trim(strtoupper($prof->lastname)),
						'th'	=> $th,
						'stri_sub_code_bk_hkk' => '',
						'stri_sk' => date('Y-m-d', strtotime($skip->startyear)),
						'stri_from_date' => date('Y-m-d', strtotime($skip->startyear)),
						'stri_thru_date' => date('Y-m-d', strtotime("+5 years", strtotime($skip->startyear))),
						'status' => 1,

						'createdby' => $this->session->userdata('admin_id')

					);
					$tgl_sk = $skip->startyear;
					$stri_type = $skip->ip_tipe;
				} else {

					//$th = substr($from,0,4);
					//$th = substr($th,-2);

					$rowInsert = array(
						'person_id' => $idmember,

						'no_kta' => $kta->no_kta,
						'certificate_type' => 0,
						'stri_id' => str_pad($num, 7, '0', STR_PAD_LEFT),
						'stri_code_wilayah' => str_pad($kta->code_wilayah, 4, '0', STR_PAD_LEFT),
						'stri_code_bk_hkk' => str_pad($kta->code_bk_hkk, 2, '0', STR_PAD_LEFT),
						'stri_pm' => '00',
						'stri_tipe' => isset($stri_tipe->id) ? 2 : 1,
						'warga' => $prof->warga_asing == 1 ? 2 : 1,
						'add_name' => trim(strtoupper($prof->firstname)) . " " . trim(strtoupper($prof->lastname)),
						'th'	=> $th,
						'stri_sub_code_bk_hkk' => '',
						'stri_sk' => date('Y-m-d', strtotime('2023-02-03')),
						'stri_from_date' => date('Y-m-d', strtotime('2023-02-03')),
						'stri_thru_date' => date('Y-m-d', strtotime("+3 years", strtotime('2023-02-03'))),
						'status' => 1,

						'createdby' => $this->session->userdata('admin_id')

					);
					$tgl_sk = '2023-02-03';
					$stri_type = 0;
				}

				$where = array(
					"LPAD(no_kta, 6, '0') = '" . str_pad($kta->no_kta, 6, '0', STR_PAD_LEFT) . "'" => null
				);
				$row = array(
					'status' => 0,
					'modifieddate' => date('Y-m-d H:i:s'),
					'modifiedby' => $this->session->userdata('admin_id'),
				);
				$update = $this->main_mod->update('members_certificate', $where, $row);

				/*$check = $this->main_mod->msrwhere('members_certificate',array('person_id'=>$idmember,'status'=>1),'id','desc')->result();
				if(isset($check[0]))
				{
					$where = array(
						"person_id" => $idmember,
					);
					$row=array(
						'status' => 1,
						//'updated_at' => date('Y-m-d H:i:s'),
						//'updated_by' => $this->session->userdata('user_id'),
					);
					$update = $this->main_mod->update('members_certificate',$where,$row);


					$this->members_model->insert_stri('members_certificate',$rowInsert);
				}
				else{*/
				$this->members_model->insert_stri('members_certificate', $rowInsert);
				//}

				$rowInsert = array(
					'user_id' => $idmember,
					'id_pay' => $id_total,
					'stri_id' => $num,
					'tgl_sk' => date('Y-m-d', strtotime($tgl_sk)),
					'stri_type' => $stri_type,
					'createdby' => $this->session->userdata('admin_id'),
				);
				$this->main_mod->insert('log_stri', $rowInsert);


				//UPDATE JENIS ANGGOTA
				$kta_data = $this->members_model->get_member_by_kta($kta->no_kta);
				if ($kta_data->jenis_anggota == '01' || $kta_data->jenis_anggota == '1') {
					$where = array(
						"person_id" => $kta_data->user_id,
					);
					//$rowInsert['updated_by'] = $this->session->userdata('admin_id');
					//$rowInsert['updated_at'] = date('Y-m-d H:i:s');
					$rowInsertx['jenis_anggota'] = '02';

					$update = $this->main_mod->update('members', $where, $rowInsertx);
				}

				$where = array(
					"seq" => 1
				);
				$row = array(
					'id_stri' => $num,
				);
				$update = $this->main_mod->update('id_management', $where, $row);

				echo "valid";
			} else echo "nomor stri sudah ada";
		} catch (Exception $e) {
			//print_r($e);
			echo "not valid";
		}
	}

	function settotal()
	{
		$akses = array("0", "1", "2", "12");
		if (!in_array($this->session->userdata('type'), $akses)) {
			redirect('admin/dashboard');
			exit;
		}

		$id_total = $this->input->post('id') <> null ? $this->input->post('id') : "";
		$total = $this->input->post('total') <> null ? $this->input->post('total') : "";
		$total_anggota = $this->input->post('total_anggota') <> null ? $this->input->post('total_anggota') : "";
		$total_tahunan = $this->input->post('total_tahunan') <> null ? $this->input->post('total_tahunan') : "";
		$total_pangkal = $this->input->post('total_pangkal') <> null ? $this->input->post('total_pangkal') : "";
		$total_gedung = $this->input->post('total_gedung') <> null ? $this->input->post('total_gedung') : "";
		$total_perpus = $this->input->post('total_perpus') <> null ? $this->input->post('total_perpus') : "";
		$total_ceps = $this->input->post('total_ceps') <> null ? $this->input->post('total_ceps') : "";
		if ($id_total == '') {
			redirect('admin/members');
			exit;
		}
		$this->load->model('main_mod');
		if ($id_total != '') {
			try {
				$checkx = $this->main_mod->msrwhere('user_transfer', array('id' => $id_total), 'id', 'desc')->result();

				if (isset($checkx[0])) {

					$this->load->model("main_mod", "basic");
					$sql = "INSERT INTO user_transfer_log (id_user_transfer, `user_id`, `pay_type`, `order_id`, `rel_id`, `bukti`, `atasnama`, `tgl`, `status`, `description`, `iuranpangkal`, `iurantahunan`, `sukarelaanggota`, `sukarelagedung`, `sukarelaperpus`, `sukarelaceps`, `sukarelatotal`, `vnv_status`, `is_upload_mandiri`, `remark`, `add_doc1`, `add_doc2`, `createddate`, `createdby`, `modifieddate`, `modifiedby`)
						SELECT id, `user_id`, `pay_type`, `order_id`, `rel_id`, `bukti`, `atasnama`, `tgl`, `status`, `description`, `iuranpangkal`, `iurantahunan`, `sukarelaanggota`, `sukarelagedung`, `sukarelaperpus`, `sukarelaceps`, `sukarelatotal`, `vnv_status`, `is_upload_mandiri`, `remark`, `add_doc1`, `add_doc2`, `createddate`, `createdby`, `modifieddate`, `modifiedby`
						FROM user_transfer
						WHERE id=" . $id_total;

					$this->basic->msrquery($sql);


					$where = array(
						"id" => $id_total
					);
					$row = array(
						'iuranpangkal' => $total_pangkal,
						'iurantahunan' => $total_tahunan,
						'sukarelaanggota' => $total_anggota,
						'sukarelagedung' => $total_gedung,
						'sukarelaperpus' => $total_perpus,
						'sukarelaceps' => $total_ceps,

						'sukarelatotal' => $total,
						'modifieddate' => date('Y-m-d H:i:s'),
						'modifiedby' => $this->session->userdata('admin_id'),
					);
					$update = $this->main_mod->update('user_transfer', $where, $row);

					echo "valid";
				} else echo "not valid";
			} catch (Exception $e) {
				//print_r($e);
				echo "not valid";
			}
		} else
			echo "not valid";
	}

	/**
	 * ER:
	 * Pemanggilan Oddo untuk membuat sale order dilakukan disini.
	 * Dipanggil saat Admin di halaman VA mengklik button "Set Active".
	 * Pemanggilan function ini dari JS Ajax/UI akan dilakukan berkali-kali (satu per satu)
	 *   sesuai jumlah VA di tabel yang dipilih (selected).
	 */
	function set_active()
	{
		$akses = array("0", "1", "2", "12", "16");
		if (!in_array($this->session->userdata('type'), $akses)) {
			redirect('admin/dashboard');
			exit;
		}

		log_message('debug',   "[SIMPONI] " . __CLASS__ . '@' . __FUNCTION__ . " accessedBy " . $this->session->userdata('user_id') . " ");

		// ER: Kenapa nama variablenya id_total ya? kenapa "total"?
		// Mungkin awalnya id VA yang dipilih (selected) di UI akan mau dikirimkan sekaligus (bukan satu per satu)
		$id_total = $this->input->post('id') <> null ? $this->input->post('id') : "";

		if ($id_total == '') {
			redirect('admin/members');
			exit;
		}

		log_message('debug',   "[SIMPONI] " . __FUNCTION__ . " accessedBy " . $this->session->userdata('user_id')
			. ". VA id is " .  $id_total);

		$this->load->model('main_mod');
		if ($id_total != '') {
			try {

				// Check kalau `id` yang dikirim ada datanya di tabel `user_transfer`
				$checkx = $this->main_mod->msrwhere('user_transfer', array('id' => $id_total), 'id', 'desc')->result();

				if (isset($checkx[0])) {

					// ER: Jika status VA adalah Not Active (bener gak nih?) atau Belum kirim data ke Oddo
					if ($checkx[0]->is_upload_mandiri == '0') {

						//SEND ODOO
						$sukarela = $checkx[0]->sukarelatotal - $checkx[0]->iurantahunan - $checkx[0]->iuranpangkal;

						// User's FAIP current type (IPU/IPM/IPP)
						$tipe = '';

						log_message('debug',   "[SIMPONI] " . __FUNCTION__ . " - Found is_upload_mandiri=0. Begin process by checking payment type: " . $checkx[0]->pay_type);


						if ($checkx[0]->pay_type == '4') {

							// Menentukan tipe FAIP berdasarkan keputusan
							//TODO: Make this as a function

							$check_faip = $this->main_mod->msrwhere('asesor_faip', array('faip_id' => $checkx[0]->rel_id), 'id', 'desc')->row();

							// ER: Lihat table m_faip_type_keputusan untuk referensi - 20240530
							if (isset($check_faip->keputusan)) {
								if (
									$check_faip->keputusan == "IPM" ||
									$check_faip->keputusan == "Memenuhi persyaratan untuk sertifikasi IPM" ||
									$check_faip->keputusan == "Belum memenuhi persyaratan untuk sertifikasi IPU. Memenuhi persyaratan untuk sertifikasi IPM"
								) {
									$tipe = 'IPM';
								} else if (
									$check_faip->keputusan == "IPU" ||
									$check_faip->keputusan == "Memenuhi persyaratan untuk IPU"
								) {
									$tipe = 'IPU';
								} else {
									$tipe = 'IPP';
								}
							} else {
								// Jika FAIP type ditentukan secara manual
								$uf_row = $this->main_mod->msrwhere('user_faip', array('id' => $checkx[0]->rel_id), 'id', 'desc')->row();

								if ($uf_row->keputusan == "2" && $uf_row->is_manual == "1")
									$tipe = 'IPM';
								else if ($uf_row->keputusan == "3" && $uf_row->is_manual == "1")
									$tipe = 'IPU';
								else $tipe = 'IPP'; //$uf_row->keputusan=="1"
							}
						} else if ($checkx[0]->pay_type == '7') {

							// Menentukan tipe PKB berdasarkan keputusan
							//TODO: Make this as a function

							$check_faip = $this->main_mod->msrwhere('asesor_pkb', array('pkb_id' => $checkx[0]->rel_id), 'id', 'desc')->row();

							if (isset($check_faip->keputusan)) {
								if (
									$check_faip->keputusan == "Dapat ditingkatkan IPM"
									|| $check_faip->keputusan == "Dapat diperpanjang IPM"
								)
								//|| $check_faip->keputusan == "Belum dapat diperpanjang IPU. Dapat diperpanjang IPM" )
								{
									$tipe = 'IPM';
								} else if (
									$check_faip->keputusan == "Dapat ditingkatkan IPU"
									|| $check_faip->keputusan == "Dapat diperpanjang IPU"
								) {
									$tipe = 'IPU';
								} else {
									$tipe = 'IPP';
								}
							}
						}

						//CHECK PERIODE
						$check_periode = $this->main_mod->msrwhere('members', array('person_id' => $checkx[0]->user_id), 'id', 'desc')->row();
						$tahun = 1;
						if (isset($check_periode->thru_date)) {
							$tm = substr($check_periode->thru_date, 0, 4);
							$tahun = date('Y') - $tm;
						}

						log_message('debug',   "[SIMPONI] " . __FUNCTION__ . " - Checking period. [this_year - member_expired_year] = " . $tahun);

						// ER: Payment perpanjangan (HER)
						// Jika tipe pembayaran perpanjangan dan diperuntukan untuk tahun > 1
						if ($checkx[0]->pay_type == 2 && $tahun > 1) {


							//$check_wna = $this->main_mod->msrwhere('user_profiles',array('user_id'=>$checkx[0]->user_id),'id','desc')->row();
							$check_is_send = $this->main_mod->msrwhere('user_transfer_detail', array('pay_id' => $checkx[0]->id), 'id', 'desc')->row();

							if (isset($check_is_send->id)) {
								$year = $this->main_mod->msrquery('SELECT year(from_date) as year_from,year(thru_date) as year_to,from_date FROM `log_her_kta` WHERE id_pay=' . $checkx[0]->id)->row();
								$all_v = $this->main_mod->msrquery('SELECT count(id) as total FROM `user_transfer_detail` WHERE pay_id=' . $checkx[0]->id)->row();
								$year_from = $this->main_mod->msrquery('SELECT tahun as year,total FROM `user_transfer_detail` WHERE pay_id=' . $checkx[0]->id . ' order by id desc')->row();

								if (isset($year->from_date)) {
									$year_fromx = $year->year_from - 1;
									if (substr($year->from_date, -5) == '12-31') {
										$year_fromx = $year->year_from;
									}

									$sel = $year->year_to - $year_fromx;
									$kelipatan = $sel - $all_v->total;
									$price = $year_from->total;

									if ($kelipatan > 0) {
										$o_id = 0;
										$is_error = 0;
										$xo = 1;

										// ER: TODO: Perlu penjelasan disini
										for ($x = $kelipatan; $x > 0; $x--) {

											// Call Odoo
											$endpoint = 'erp/reg_member?user_id=' . $checkx[0]->user_id . '&sukarela=0&pangkal=' . $checkx[0]->iuranpangkal . '&tahunan=' . $price . '&type=' . $checkx[0]->pay_type . '&total=' . $checkx[0]->sukarelatotal . '&tipe_faip=' . $tipe . '&id=' . $id_total . '&tahun_her=' . ($year_from->year + $xo);
											log_message("debug", "[SIMPONI] " . __FUNCTION__ . " - X1 Start calling " . $endpoint);
											$result = file_get_contents(site_url($endpoint));

											$tmp = json_decode($result);
											//print_r($tmp);
											if (isset($tmp->msg)) {
												if ($tmp->msg == 'OK') {

													$rowInsert = array(
														'pay_id' => $id_total, // user_transfer id
														'order_id' => $tmp->data, // Odoo SO id
														'tahun' => ($year_from->year + $xo),
														'total' => $price,
														'status' => 0,
														'createdby' => $this->session->userdata('admin_id'),
													);
													$this->main_mod->insert('user_transfer_detail', $rowInsert);

													$o_id = $tmp->data;
												} else {
													log_message("debug", "[SIMPONI] " . __FUNCTION__ . " - Call Oddo multiple times for HER payment failed. Please check my next debug message and the code print this message!");
													break;
													$is_error = 1; //ER: Q: Gak salah nih statement setelah break?
													log_message("debug", "[SIMPONI] " . __FUNCTION__ . " - Call Oddo multiple times for HER payment is set to error.");
												}
											} else {
												log_message("debug", "[SIMPONI] " . __FUNCTION__ . " - Call Oddo multiple times for HER payment failed. Please check my next debug message and the code print this message!");
												break;
												$is_error = 1; //ER: Gak salah nih statement setelah break?
												log_message("debug", "[SIMPONI] " . __FUNCTION__ . " - Call Oddo multiple times for HER payment is set to error.");
											}
											$xo++;
										}

										// ER: WARNING!!!!!!!!!!!!!!!!!!!!!!! Sepertinya $is_error akan selalu "0"
										//


										// ER: Kalau semua proses di Odoo berhasil SEMUA maka update is_upload_mandiri=1 dan isi kode SO
										// Jika ada satu saja yang gagal proses di Oddo maka status di user_transfer tidak berubah
										if ($is_error == 0) {
											$where = array(
												"id" => $id_total
											);
											$row = array(
												'is_upload_mandiri' => 1,
												'order_id' => $o_id, // -> ER: Process diatas bisa membuat banyak SO id,
												//    tapi disini diupdate pakai SO id yang sukses dieksekusi terakhir??
												'modifieddate' => date('Y-m-d H:i:s'),
												'modifiedby' => $this->session->userdata('admin_id'),
											);
											$update = $this->main_mod->update('user_transfer', $where, $row);
											echo "valid"; // ER: Are you sure.. mau sending output sebelum kirim email berhasil?

											//SEND Mailer
											$this->send_mail_va($checkx[0]->user_id, $checkx[0]->pay_type, $checkx[0]->sukarelatotal);
											log_message("debug", "[SIMPONI] " . __FUNCTION__ . " - Call Oddo multiple times for HER payment valid. Email sent to member!");
										} else {
											// ER: TODO: Fixme!
											log_message("debug", "[SIMPONI] " . __FUNCTION__ . " - Call Oddo multiple times for HER payment failed.");
											echo "resend";
										}
									} else {
										log_message("debug", "[SIMPONI] " . __FUNCTION__ . " - Call Oddo multiple times for HER payment is not valid. Kelipatan nol");
										echo "not valid";
									}
								} else {
									log_message("debug", "[SIMPONI] " . __FUNCTION__ . " - Call Oddo multiple times for HER payment is not valid. from_date is null");
									echo "not valid";
								}
							} else {
								$year = $this->main_mod->msrquery('SELECT year(from_date) as year_from,year(thru_date) as year_to,from_date FROM `log_her_kta` WHERE id_pay=' . $checkx[0]->id)->row();

								if (isset($year->year_from)) {
									$year_fromx = $year->year_from - 1;
									$year_fromxx = $year->year_from;
									if (substr($year->from_date, -5) == '12-31') {
										$year_fromx = $year->year_from;
										$year_fromxx = $year->year_from + 1;
									}

									$kelipatan = $year->year_to - $year_fromx;
									$price = $checkx[0]->iurantahunan / $kelipatan;

									if ($kelipatan > 0) {
										$o_id = 0;
										$is_error = 0;
										$xo = 0;
										for ($x = $kelipatan; $x > 0; $x--) {
											$endpoint = 'erp/reg_member?user_id=' . $checkx[0]->user_id . '&sukarela=0&pangkal=' . $checkx[0]->iuranpangkal . '&tahunan=' . $price . '&type=' . $checkx[0]->pay_type . '&total=' . $checkx[0]->sukarelatotal . '&tipe_faip=' . $tipe . '&id=' . $id_total . '&tahun_her=' . ($year_fromxx + $xo);
											log_message("debug", "[SIMPONI] " . __FUNCTION__ . " - X2 Start calling " . $endpoint);
											$result = file_get_contents(site_url($endpoint));


											$tmp = json_decode($result);
											//print_r($tmp);
											if (isset($tmp->msg)) {
												if ($tmp->msg == 'OK') {

													$rowInsert = array(
														'pay_id' => $id_total,
														'order_id' => $tmp->data,
														'tahun' => ($year_fromxx + $xo),
														'total' => $price,
														'status' => 0,
														'createdby' => $this->session->userdata('admin_id'),
													);
													$this->main_mod->insert('user_transfer_detail', $rowInsert);

													$o_id = $tmp->data;
												} else {
													break;
													$is_error = 1;
												}
											} else {
												break;
												$is_error = 1;
											}
											$xo++;
										}

										if ($is_error == 0) {
											$where = array(
												"id" => $id_total
											);
											$row = array(
												'is_upload_mandiri' => 1,
												'order_id' => $o_id,
												'modifieddate' => date('Y-m-d H:i:s'),
												'modifiedby' => $this->session->userdata('admin_id'),
											);
											$update = $this->main_mod->update('user_transfer', $where, $row);
											echo "valid";

											//SEND Mailer
											$this->send_mail_va($checkx[0]->user_id, $checkx[0]->pay_type, $checkx[0]->sukarelatotal);
										} else echo "resend";
									} else echo "not valid";
								}
							}
						} // End of Pembayaran HER/Perpanjangan dengan tahun > 1

						// ER: Payment PKB Assessment fee
						else if ($checkx[0]->pay_type == 6) {
							$check_wna = $this->main_mod->msrwhere('user_profiles', array('user_id' => $checkx[0]->user_id), 'id', 'desc')->row();
							if ($check_wna->warga_asing == 0) {
								$pkb = $this->main_mod->msrwhere('user_pkb', array('id' => $checkx[0]->rel_id), 'id', 'desc')->row();
								$sip = $this->main_mod->msrwhere('user_cert', array('id' => $pkb->sip_id), 'id', 'desc')->row();
								$check_double = $this->main_mod->msrwhere('user_pkb', array('sip_id' => $pkb->sip_id, 'user_id' => $checkx[0]->user_id, 'id<>' . $pkb->id => null), 'id', 'desc')->row();

								$run_num = 1;
								$period = $pkb->periodend - $pkb->periodstart;

								if (isset($check_double->user_id)) {
									$period = $pkb->periodend - $check_double->periodend;
									$run_num = $check_double->periodend - $check_double->periodstart + 1;
								}

								// ER: Bener nih? Tipe FAIP nilainya ...
								$tipe = $run_num;

								$endpoint = 'erp/reg_member?user_id=' . $checkx[0]->user_id . '&sukarela=' . $sukarela . '&pangkal=' . $checkx[0]->iuranpangkal . '&tahunan=' . $checkx[0]->iurantahunan . '&type=' . $checkx[0]->pay_type . '&total=' . $checkx[0]->sukarelatotal . '&tipe_faip=' . $tipe . '&id=' . $id_total . '&tahun_her=' . date('Y');
								log_message("debug", "[SIMPONI] " . __FUNCTION__ . " - X3 Start calling " . $endpoint);
								$result = file_get_contents(site_url($endpoint));
								//print_r($result);

								$tmp = json_decode($result);
								//print_r($tmp);
								if (isset($tmp->msg)) {
									if ($tmp->msg == 'OK') {
										$where = array(
											"id" => $id_total
										);
										$row = array(
											//'is_upload_mandiri' => 1,
											'is_upload_mandiri' => 1,
											'order_id' => $tmp->data,
											'modifieddate' => date('Y-m-d H:i:s'),
											'modifiedby' => $this->session->userdata('admin_id'),
										);
										$update = $this->main_mod->update('user_transfer', $where, $row);
										echo "valid";

										//SEND Mailer
										$this->send_mail_va($checkx[0]->user_id, $checkx[0]->pay_type, $checkx[0]->sukarelatotal);
									} else {
										// Return from Odoo is not OK
										echo site_url('erp/reg_member?user_id=' . $checkx[0]->user_id . '&sukarela=' . $sukarela . '&pangkal=' . $checkx[0]->iuranpangkal . '&tahunan=' . $checkx[0]->iurantahunan . '&type=' . $checkx[0]->pay_type . '&total=' . $checkx[0]->sukarelatotal . '&tipe_faip=' . $tipe . '&id=' . $id_total . '&tahun_her=' . date('Y')); //print_r($tmp);//
									}
								} else {
									// Return from Odoo have no message field (`msg`)
									echo site_url('erp/reg_member?user_id=' . $checkx[0]->user_id . '&sukarela=' . $sukarela . '&pangkal=' . $checkx[0]->iuranpangkal . '&tahunan=' . $checkx[0]->iurantahunan . '&type=' . $checkx[0]->pay_type . '&total=' . $checkx[0]->sukarelatotal . '&tipe_faip=' . $tipe . '&id=' . $id_total . '&tahun_her=' . date('Y')); //echo "not valid";
								}
							} else {
								// Anggota adalah WNA
								echo "not valid";
							}
						}

						// ER: Payment selain PKB Asessment fee ATAU Pembayaran HER/Perpanjangan
						else {
							$th = date('Y');
							if ($checkx[0]->pay_type == 2) {
								$check_th = $this->main_mod->msrwhere('log_her_kta', array('id_pay' => $id_total), 'id', 'desc')->row();
								if (isset($check_th->thru_date))
									$th = substr($check_th->thru_date, 0, 4);
							}

							// Call Odoo
							$endpoint = 'erp/reg_member?user_id=' . $checkx[0]->user_id . '&sukarela=' . $sukarela . '&pangkal=' . $checkx[0]->iuranpangkal . '&tahunan=' . $checkx[0]->iurantahunan . '&type=' . $checkx[0]->pay_type . '&total=' . $checkx[0]->sukarelatotal . '&tipe_faip=' . $tipe . '&id=' . $id_total . '&tahun_her=' . $th;
							log_message("debug", "[SIMPONI] " . __FUNCTION__ . " - X4 Start calling " . $endpoint);
							$result = file_get_contents(site_url($endpoint));
							//print_r($result);

							$tmp = json_decode($result);
							//print_r($tmp);

							if (isset($tmp->msg)) {
								if ($tmp->msg == 'OK') {
									$where = array(
										"id" => $id_total
									);
									$row = array(
										'is_upload_mandiri' => 1,
										'order_id' => $tmp->data, // Update order_id value from Odoo
										'modifieddate' => date('Y-m-d H:i:s'),
										'modifiedby' => $this->session->userdata('admin_id'),
									);
									$update = $this->main_mod->update('user_transfer', $where, $row);

									echo "valid";

									//SEND Mailer
									$this->send_mail_va($checkx[0]->user_id, $checkx[0]->pay_type, $checkx[0]->sukarelatotal);
									log_message('debug', "[SIMPONI] " . __FUNCTION__ . " - Email sent to member!");
								} else {
									log_message(
										'error',
										"[SIMPONI] " . __FUNCTION__ . " Calling Odoo to register member is successful but not OK. \n" .
											"Set upload_mandiri=1 and sending email are NOT executed. \n" .
											"Response msg:" . @print_r($tmp, TRUE) . "\n"
									);
									echo "not valid";
									//print_r($tmp);
								}
							} else {
								log_message('debug', "[SIMPONI] " . __FUNCTION__ . " Calling Odoo to register member is failed. Response msg:" . @print_r($tmp, TRUE));
								print_r($tmp);
								//echo site_url('erp/reg_member?user_id='.$checkx[0]->user_id.'&sukarela='.$sukarela.'&pangkal='.$checkx[0]->iuranpangkal.'&tahunan='.$checkx[0]->iurantahunan.'&type='.$checkx[0]->pay_type.'&total='.$checkx[0]->sukarelatotal.'&tipe_faip='.$tipe.'&id='.$id_total);//echo "not valid";
							}
							//SEND ODOO

						}
					}
					// Jika status VA adalah Active maka do nothing
					else if ($checkx[0]->is_upload_mandiri == '1') {
						log_message('debug',  "[SIMPONI] " . __FUNCTION__ . " return 'valid'. VA status is alrady Active (is_upload_mandiri = 1)");
					} else {
						log_message('debug',  "[SIMPONI] " . __FUNCTION__ . " return 'not valid'. VA status is either not 'Active' or 'Not Active'");
					}
				} else {
					log_message('debug',   "[SIMPONI] " . __FUNCTION__ . " return 'not valid'. Transfer data is not found in user_transfer table with id=" . $id_total);
				}
			} catch (Exception $e) {
				//print_r($e);
				log_message('error', "[SIMPONI] " . __FUNCTION__ . " Exception on controllers/admin/Members@set_active(). \n" . $e);
				echo "not valid";
			}
		} else {
			// VA id is not found in the HTTP POST parameter
			echo "not valid";
		}
	}

	function set_active_manual()
	{
		// ER: Hardcoded VA id yang mau di set_active secara manual
		$id_total = 88246;

		$this->load->model('main_mod');
		if ($id_total != '') {
			try {
				$checkx = $this->main_mod->msrwhere('user_transfer', array('id' => $id_total), 'id', 'desc')->result();

				if (isset($checkx[0])) {
					if ($checkx[0]->is_upload_mandiri == '0') {

						//SEND ODOO
						$sukarela = $checkx[0]->sukarelatotal - $checkx[0]->iurantahunan - $checkx[0]->iuranpangkal;
						$tipe = '';

						//CHECK PERIODE
						$check_periode = $this->main_mod->msrwhere('members', array('person_id' => $checkx[0]->user_id), 'id', 'desc')->row();
						$tahun = 1;
						if (isset($check_periode->thru_date)) {
							$tm = substr($check_periode->thru_date, 0, 4);
							$tahun = date('Y') - $tm;
						}

						// Jika tipe pembayaran perpanjangan dan diperuntukan untuk tahun > 1
						if ($checkx[0]->pay_type == 2 && $tahun > 1) {
							$check_wna = $this->main_mod->msrwhere('user_profiles', array('user_id' => $checkx[0]->user_id), 'id', 'desc')->row();
							if ($check_wna->warga_asing == 0) {
								$tmp_setengah = $checkx[0]->iurantahunan / 2;

								// ER: Call Odoo
								$result2 = file_get_contents(site_url('erp/reg_member?user_id=' . $checkx[0]->user_id . '&sukarela=' . $sukarela . '&pangkal=' . $checkx[0]->iuranpangkal . '&tahunan=' . $tmp_setengah . '&type=' . $checkx[0]->pay_type . '&total=' . $checkx[0]->sukarelatotal . '&tipe_faip=' . $tipe . '&id=' . $id_total . '&tahun_her=' . date('Y')));
								//print_r($result);

								$return_so_odoo = json_decode($result2);
								//print_r($tmp);
								if (isset($return_so_odoo->msg)) {

									$rowInsert = array(
										'pay_id' => $id_total,
										'order_id' => $return_so_odoo->data,
										'tahun' => date('Y'),
										'total' => $tmp_setengah,
										'status' => 0,
										'createdby' => $this->session->userdata('admin_id'),
									);
									$this->main_mod->insert('user_transfer_detail', $rowInsert);

									$where = array(
										"id" => $id_total
									);


									$row = array(
										//'is_upload_mandiri' => 1,
										'is_upload_mandiri' => 1,
										'order_id' => $return_so_odoo->data,
										'modifieddate' => date('Y-m-d H:i:s'),
										'modifiedby' => $this->session->userdata('admin_id'),
									);
									$update = $this->main_mod->update('user_transfer', $where, $row);

									/* ER: Sepertinya ini tergantung kasus per kasus
											$row=array(
												'is_upload_mandiri' => 1,
												'modifieddate' => date('Y-m-d H:i:s'),
												'modifiedby' => $this->session->userdata('admin_id'),
											);
											$update = $this->main_mod->update('user_transfer',$where,$row);*/
									echo "valid";

									//SEND Mailer
									$this->send_mail_va($checkx[0]->user_id, $checkx[0]->pay_type, $checkx[0]->sukarelatotal);
								}
							}
						}
					} else if ($checkx[0]->is_upload_mandiri == '1') {
						echo "valid";
					} else echo "not valid";
				} else echo "not valid";
			} catch (Exception $e) {
				//print_r($e);
				echo "not valid";
			}
		} else
			echo "not valid";
	}

	function set_active_second()
	{
		$arr = array(
			82539,
			66244,
			83494,
			83425,
			83192,
			83190,
			83189,
			72960,
			75839,
			83806,
			83863,
			83881,
			83864
		);

		$this->load->model('main_mod');
		try {
			foreach ($arr as $val) {
				$checkx = $this->main_mod->msrwhere('user_transfer', array('id' => $val), 'id', 'desc')->result();

				$result2 = file_get_contents(site_url('erp/reg_member?user_id=' . $checkx[0]->user_id . '&sukarela=0&pangkal=0&tahunan=300000&type=2&total=' . $checkx[0]->sukarelatotal . '&tipe_faip=&id=' . $val . '&tahun_her=' . date('Y')));
				//print_r($result);

				$tmp2 = json_decode($result2);
				//print_r($tmp);
				if (isset($tmp2->msg)) {

					$rowInsert = array(
						'pay_id' => $val,
						'order_id' => $tmp2->data,
						'tahun' => date('Y'),
						'total' => 300000,
						'status' => 0,
						'createdby' => $this->session->userdata('admin_id'),
					);
					$this->main_mod->insert('user_transfer_detail', $rowInsert);

					/*$where = array(
						"id" => $val
					);
					$row=array(
						//'is_upload_mandiri' => 1,
						'is_upload_mandiri' => 1,
						'order_id' => $tmp2->data,
						'modifieddate' => date('Y-m-d H:i:s'),
						'modifiedby' => $this->session->userdata('admin_id'),
					);
					$update = $this->main_mod->update('user_transfer',$where,$row);*/

					echo $val . " = valid <br />";

					//SEND Mailer
					//$this->send_mail_va($checkx[0]->user_id, $checkx[0]->pay_type, $checkx[0]->sukarelatotal);
				}
			}
		} catch (Exception $e) {
			//print_r($e);
			echo "not valid";
		}
	}

	function send_mail_va($user_id, $pay_type, $sukarelatotal)
	{
		$this->load->model('main_mod');
		$users = $this->main_mod->msrwhere('users', array('id' => $user_id), 'id', 'desc')->result();
		$user_profiles = $this->main_mod->msrwhere('user_profiles', array('user_id' => $user_id), 'id', 'desc')->result();
		//print_r($bk);
		if (isset($users[0])) {
			if ($users[0]->email != '') {
				$to_email = array();
				$cc_email = array();
				$to_email[] = $users[0]->email;
				//$cc_email[] = 'blank.anonim4@gmail.com';

				$subject = "Informasi Pembayaran Untuk " . PAY_TYPES[$pay_type];

				$data['masa_berlaku'] = format_hari_tanggal(
					date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . ' + 1 days'))
				);
				$data['total']        = $sukarelatotal;
				$data['va']           = $user_profiles[0]->va;
				$data['nama']         = $user_profiles[0]->firstname . ' ' . $user_profiles[0]->lastname;
				$data['site_name']    = $this->config->item('website_name', 'tank_auth');
				if ($user_profiles[0]->kolektif_ids == '682') {
					return; // Dont send email.
				} else {
					$this->_send_email_va('va', $subject, $to_email, $cc_email, $data);
				}
			}
		}
	}

	function format_hari_tanggal($waktu)
	{
		$hari_array = array(
			'Minggu',
			'Senin',
			'Selasa',
			'Rabu',
			'Kamis',
			'Jumat',
			'Sabtu'
		);
		$hr = date('w', strtotime($waktu));
		$hari = $hari_array[$hr];
		$tanggal = date('j', strtotime($waktu));
		$bulan_array = array(
			1 => 'Januari',
			2 => 'Februari',
			3 => 'Maret',
			4 => 'April',
			5 => 'Mei',
			6 => 'Juni',
			7 => 'Juli',
			8 => 'Agustus',
			9 => 'September',
			10 => 'Oktober',
			11 => 'November',
			12 => 'Desember',
		);
		$bl = date('n', strtotime($waktu));
		$bulan = $bulan_array[$bl];
		$tahun = date('Y', strtotime($waktu));
		$jam = date('H:i:s', strtotime($waktu));

		return "$hari, $tanggal $bulan $tahun, $jam";
	}

	/**
	 * Refactor: Moved this function to separate Controller (Payment)
	 * This is keep it here to prevent error
	 */
	function export_va_all()
	{
		redirect('admin/payment/export_va_all?' . http_build_query($this->input->get()) . '&' . http_build_query($this->input->post()));
	}
	/**
	 * Refactor: Moved this function to separate Controller (Payment)
	 * This is keep it here to prevent error
	 */
	function export_va_select()
	{
		redirect('admin/payment/export_va_select?' . http_build_query($this->input->get()) . '&' . http_build_query($this->input->post()));
	}


	function export_skip_all()
	{
		$akses = array("0", "1");
		if (!in_array($this->session->userdata('type'), $akses)) {
			redirect('admin/dashboard');
			exit;
		}


		$this->load->library('Libexcel', 'excel');
		$this->load->model('main_mod');
		$rsl = $this->main_mod->msrquery("SELECT user_cert.*,firstname,lastname,m_bk.name as bk_name, email FROM user_cert left join users on user_cert.user_id=users.id left join user_profiles on user_cert.user_id = user_profiles.user_id left join m_bk on TRIM(LEADING '0' FROM user_cert.ip_bk) = TRIM(LEADING '0' FROM m_bk.value) where user_cert.user_id <> 0 and status=2 order by user_cert.id")->result();

		$objPHPExcel = new PHPExcel();

		$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A1', 'NAMA')
			->setCellValue('B1', 'Type')
			->setCellValue('C1', 'BK FAIP')
			->setCellValue('D1', 'No. IP')
			->setCellValue('E1', 'NO. KTA')
			->setCellValue('F1', 'SK FROM')
			->setCellValue('G1', 'SK END')
			->setCellValue('H1', 'STATUS')
			->setCellValue('I1', 'EMAIL');

		$objPHPExcel->getActiveSheet()->getStyle('A1:I1')->getFont()->setBold(true);

		$rowCount = 2;
		foreach ($rsl as $val) {

			$objPHPExcel->getActiveSheet()->SetCellValue('A' . $rowCount, $val->firstname . ' ' . $val->lastname);

			$tipe = '';
			if ($val->ip_tipe == '1')
				$tipe = 'IPP.';
			else if ($val->ip_tipe == '2')
				$tipe = 'IPM.';
			else if ($val->ip_tipe == '3')
				$tipe = 'IPU.';

			$objPHPExcel->getActiveSheet()->SetCellValue('B' . $rowCount, $tipe);
			$objPHPExcel->getActiveSheet()->SetCellValue('C' . $rowCount, $val->ip_bk . '-' . $val->bk_name);
			$objPHPExcel->getActiveSheet()->SetCellValue('D' . $rowCount, $val->lic_num);
			$objPHPExcel->getActiveSheet()->SetCellValue('E' . $rowCount, str_pad($val->ip_kta, 6, '0', STR_PAD_LEFT));
			$objPHPExcel->getActiveSheet()->SetCellValue('F' . $rowCount, $val->startyear);
			$objPHPExcel->getActiveSheet()->SetCellValue('G' . $rowCount, $val->endyear);

			$mysqltime = date('Y-m-d', strtotime($val->endyear));
			$active = 'Active';
			if (date('Y-m-d') >= $mysqltime) $active = 'Not Active';

			$objPHPExcel->getActiveSheet()->setCellValueExplicit('H' . $rowCount, $active, PHPExcel_Cell_DataType::TYPE_STRING);

			$objPHPExcel->getActiveSheet()->SetCellValue('I' . $rowCount, $val->email);

			//$objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, $val->sukarelatotal);

			$rowCount++;
		}

		$sheet = $objPHPExcel->getActiveSheet();
		$cellIterator = $sheet->getRowIterator()->current()->getCellIterator();
		$cellIterator->setIterateOnlyExistingCells(true);

		foreach ($cellIterator as $cell) {
			$sheet->getColumnDimension($cell->getColumn())->setAutoSize(true);
		}

		$filename = date('Y-m-d') . '.xls';
		header('Content-Type: application/vnd.ms-excel'); //mime type
		header('Content-Disposition: attachment;filename="IP_ALL_' . $filename . '"');
		header('Cache-Control: max-age=0'); //no cache
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
	}

	function export_skip_select()
	{
		$akses = array("0", "1");
		if (!in_array($this->session->userdata('type'), $akses)) {
			redirect('admin/dashboard');
			exit;
		}
		$id_total = $this->input->get('id') <> null ? $this->input->get('id') : "";

		$this->load->library('Libexcel', 'excel');
		$this->load->model('main_mod');
		$rsl = $this->main_mod->msrquery("SELECT user_cert.*,firstname,lastname,m_bk.name as bk_name FROM user_cert left join user_profiles on user_cert.user_id = user_profiles.user_id left join m_bk on TRIM(LEADING '0' FROM user_cert.ip_bk) = TRIM(LEADING '0' FROM m_bk.value) where user_cert.user_id <> 0 and status=2 and user_cert.id in (" . $id_total . ")")->result();

		$objPHPExcel = new PHPExcel();

		$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A1', 'NAMA')
			->setCellValue('B1', 'Type')
			->setCellValue('C1', 'BK FAIP')
			->setCellValue('D1', 'No. IP')
			->setCellValue('E1', 'NO. KTA')
			->setCellValue('F1', 'SK FROM')
			->setCellValue('G1', 'SK END')
			->setCellValue('H1', 'STATUS');

		$objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getFont()->setBold(true);

		$rowCount = 2;
		foreach ($rsl as $val) {

			$objPHPExcel->getActiveSheet()->SetCellValue('A' . $rowCount, $val->firstname . ' ' . $val->lastname);

			$tipe = '';
			if ($val->ip_tipe == '1')
				$tipe = 'IPP.';
			else if ($val->ip_tipe == '2')
				$tipe = 'IPM.';
			else if ($val->ip_tipe == '3')
				$tipe = 'IPU.';

			$objPHPExcel->getActiveSheet()->SetCellValue('B' . $rowCount, $tipe);
			$objPHPExcel->getActiveSheet()->SetCellValue('C' . $rowCount, $val->ip_bk . '-' . $val->bk_name);
			$objPHPExcel->getActiveSheet()->SetCellValue('D' . $rowCount, $val->lic_num);
			$objPHPExcel->getActiveSheet()->SetCellValue('E' . $rowCount, str_pad($val->ip_kta, 6, '0', STR_PAD_LEFT));
			$objPHPExcel->getActiveSheet()->SetCellValue('F' . $rowCount, $val->startyear);
			$objPHPExcel->getActiveSheet()->SetCellValue('G' . $rowCount, $val->endyear);

			$mysqltime = date('Y-m-d', strtotime($val->endyear));
			$active = 'Active';
			if (date('Y-m-d') >= $mysqltime) $active = 'Not Active';

			$objPHPExcel->getActiveSheet()->setCellValueExplicit('H' . $rowCount, $active, PHPExcel_Cell_DataType::TYPE_STRING);
			//$objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, $val->sukarelatotal);

			$rowCount++;
		}

		$sheet = $objPHPExcel->getActiveSheet();
		$cellIterator = $sheet->getRowIterator()->current()->getCellIterator();
		$cellIterator->setIterateOnlyExistingCells(true);

		foreach ($cellIterator as $cell) {
			$sheet->getColumnDimension($cell->getColumn())->setAutoSize(true);
		}

		$filename = date('Y-m-d') . '.xls';
		header('Content-Type: application/vnd.ms-excel'); //mime type
		header('Content-Disposition: attachment;filename="IP_ALL_' . $filename . '"');
		header('Cache-Control: max-age=0'); //no cache
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
	}

	/**
	 * Diakses dari main left menu "Approval"
	 */
	function approval()
	{
		$data['title'] = ''; //SITE_NAME.': Manage Members';
		$data['msg'] = '';

		//$akses = array("0", "1", "2", "12");
		//if(!in_array($this->session->userdata('type'),$akses)){
		//	redirect('admin/dashboard');
		//	exit;
		//}

		$data["m_cab"] = $this->members_model->get_all_cabang();
		$data["m_bk"] = $this->members_model->get_all_bk();
		$this->load->view('admin/approval_view', $data);
		return;
	}
	/**
	 * Populate table di halaman utama "Approval"
	 */
	public function get_approval()
	{

		$filter['status'] = $_POST['filter_status'];
		$filter['bk'] = $_POST['filter_bk'];
		$filter['cab'] = $_POST['filter_cab'];
		if ($_POST['tgl_period'] != '')
			$filter['tgl_period'] = $_POST['tgl_period'];
		if ($_POST['tgl_period2'] != '')
			$filter['tgl_period2'] = $_POST['tgl_period2'];

		$search = $_POST['search']['value'];
		$limit = $_POST['length'];
		$start = $_POST['start'];
		$order_index = $_POST['order'][0]['column'];
		$order_field = $_POST['columns'][$order_index]['data'];

		$column = ($_POST['columns']);

		$order_ascdesc = $_POST['order'][0]['dir'];
		$sql_total = $this->members_model->count_all_approval();
		$sql_data = $this->members_model->filter_approval($search, $limit, $start, $order_field, $order_ascdesc, $column, $filter);
		$sql_filter = $this->members_model->count_filter_approval($search, $column, $filter);

		//print_r($column);

		$callback = array(
			'draw' => $_POST['draw'],
			'recordsTotal' => $sql_total,
			'recordsFiltered' => $sql_filter,
			'data' => $sql_data
		);
		header('Content-Type: application/json');
		echo json_encode($callback);
	}

	function approved()
	{
		//$akses = array("0", "1", "2", "12");
		//if(!in_array($this->session->userdata('type'),$akses)){
		//	redirect('admin/dashboard');
		//	exit;
		//}

		$id_total = $this->input->post('id') <> null ? $this->input->post('id') : "";
		if ($id_total == '') {
			redirect('admin/members');
			exit;
		}
		$this->load->model('main_mod');
		if ($id_total != '') {
			try {
				$checkx = $this->main_mod->msrwhere('user_approval', array('id' => $id_total), 'id', 'desc')->result();

				if (isset($checkx[0])) {
					//if($checkx[0]->status == 'Waiting for Approval'){
					$where = array(
						"id" => $id_total
					);
					$row = array(
						'status' => 'Approved',
						'status_date' => date('Y-m-d H:i:s'),
						'modifieddate' => date('Y-m-d H:i:s'),
						'modifiedby' => $this->session->userdata('admin_id'),
					);
					$update = $this->main_mod->update('user_approval', $where, $row);

					//update cert temp

					$next_app = $this->main_mod->msrwhere('user_approval', array('faip_id' => $checkx[0]->faip_id, 'seq>' . $checkx[0]->seq => null), 'id', 'asc')->row();

					if (isset($next_app->app_id)) {
						$where = array(
							"faip_id" => $checkx[0]->faip_id
						);
						$row = array(
							'next_approval' => $next_app->app_id,
							'last_date_approval' => date('Y-m-d H:i:s'),
							'modifieddate' => date('Y-m-d H:i:s'),
							'modifiedby' => $this->session->userdata('admin_id'),
						);
						$update = $this->main_mod->update('user_cert_temp', $where, $row);
					} else {
						//APPROVED ALL
						$where = array(
							"faip_id" => $checkx[0]->faip_id
						);
						$row = array(
							'status_approval' => 'Approved',
							'status' => 1,
							'next_approval' => '',
							'last_date_approval' => date('Y-m-d H:i:s'),
							'modifieddate' => date('Y-m-d H:i:s'),
							'modifiedby' => $this->session->userdata('admin_id'),
						);
						$update = $this->main_mod->update('user_cert_temp', $where, $row);

						if ($checkx[0]->type_app == '1')
							$this->setipapproved($checkx[0]->faip_id);
						else if ($checkx[0]->type_app == '2')
							$this->setstriapproved($checkx[0]->faip_id);
					}
					//SEND Mailer
					//$this->send_mail_va($checkx[0]->user_id, $checkx[0]->pay_type, $checkx[0]->sukarelatotal);

					echo "valid";
					//}
					//else echo "not valid";
				} else echo "not valid";
			} catch (Exception $e) {
				//print_r($e);
				echo "not valid";
			}
		} else
			echo "not valid";
	}

	function rejected()
	{
		//$akses = array("0", "1", "2", "12");
		//if(!in_array($this->session->userdata('type'),$akses)){
		//	redirect('admin/dashboard');
		//	exit;
		//}

		$id_total = $this->input->post('id') <> null ? $this->input->post('id') : "";
		$remark = $this->input->post('remark') <> null ? $this->input->post('remark') : "";
		if ($id_total == '') {
			redirect('admin/members');
			exit;
		}
		$this->load->model('main_mod');
		if ($id_total != '') {
			try {
				$checkx = $this->main_mod->msrwhere('user_approval', array('id' => $id_total), 'id', 'desc')->result();

				if (isset($checkx[0])) {
					//if($checkx[0]->status == 'Waiting for Approval'){
					$where = array(
						"id" => $checkx[0]->faip_id
					);
					$row = array(
						'is_publish' => 2,
						'modifieddate' => date('Y-m-d H:i:s'),
						'modifiedby' => $this->session->userdata('admin_id'),
					);
					$update = $this->main_mod->update('members_certificate', $where, $row);

					$where = array(
						"id" => $id_total
					);
					$row = array(
						'status' => 'Rejected',
						'remark' => $remark,
						'status_date' => date('Y-m-d H:i:s'),
						'modifieddate' => date('Y-m-d H:i:s'),
						'modifiedby' => $this->session->userdata('admin_id'),
					);
					$update = $this->main_mod->update('user_approval', $where, $row);


					//SEND Mailer
					//$this->send_mail_va($checkx[0]->user_id, $checkx[0]->pay_type, $checkx[0]->sukarelatotal);

					echo "valid";
					//}
					//else echo "not valid";
				} else echo "not valid";
			} catch (Exception $e) {
				//print_r($e);
				echo "not valid";
			}
		} else
			echo "not valid";
	}


	//KOLEKTIF
	function setkolektif()
	{
		$akses = array("0", "2");
		if (!in_array($this->session->userdata('type'), $akses)) {
			redirect('admin/dashboard');
			exit;
		}

		$id = $this->input->post('id') <> null ? $this->input->post('id') : "";
		$admin = $this->input->post('admin') <> null ? $this->input->post('admin') : "";

		if ($id == '') {
			redirect('admin/members');
			exit;
		}
		$this->load->model('main_mod');
		if ($id != '') {
			try {

				$check = $this->main_mod->msrwhere('user_profiles', array('user_id' => $id), 'id', 'desc')->result(); //,'FIND_IN_SET("'.$admin.'", kolektif_ids)'=>null
				if (isset($check[0])) {
					if ($check[0]->kolektif_ids == "") {

						$where = array(
							"id" => $id
						);
						$row = array(
							'kolektif_ids' => $admin,
							'modifieddate' => date('Y-m-d H:i:s'),
							'modifiedby' => $this->session->userdata('admin_id'),
						);
						$update = $this->main_mod->update('user_profiles', $where, $row);
						echo "valid";
					} else if ($check[0]->kolektif_ids != "") {
						$tmp = explode(',', $check[0]->kolektif_ids);
						$arr = array();
						foreach ($tmp as $v) {
							if ($v != $admin)
								$arr[] = $v;
						}
						$arr[] = $admin;

						$where = array(
							"id" => $id
						);
						$row = array(
							'kolektif_ids' => implode(',', $arr),
							'modifieddate' => date('Y-m-d H:i:s'),
							'modifiedby' => $this->session->userdata('admin_id'),
						);
						$update = $this->main_mod->update('user_profiles', $where, $row);
						echo "valid";
					} else echo "not valid";
				} else echo "not valid";
			} catch (Exception $e) {
				//print_r($e);
				echo "not valid";
			}
		} else
			echo "not valid";
	}

	function setkolektifname()
	{
		$akses = array("0", "2");
		if (!in_array($this->session->userdata('type'), $akses)) {
			redirect('admin/dashboard');
			exit;
		}

		$id = $this->input->post('id') <> null ? $this->input->post('id') : "";
		$admin = $this->input->post('admin') <> null ? $this->input->post('admin') : "";

		if ($id == '') {
			redirect('admin/members');
			exit;
		}
		$this->load->model('main_mod');
		if ($id != '') {
			try {

				$check = $this->main_mod->msrwhere('user_profiles', array('user_id' => $id), 'id', 'desc')->result(); //,'FIND_IN_SET("'.$admin.'", kolektif_ids)'=>null
				if (isset($check[0])) {
					//if($check[0]->kolektif_name_id==""){

					$where = array(
						"id" => $id
					);
					$row = array(
						'kolektif_name_id' => $admin,
						'modifieddate' => date('Y-m-d H:i:s'),
						'modifiedby' => $this->session->userdata('admin_id'),
					);
					$update = $this->main_mod->update('user_profiles', $where, $row);
					echo "valid";
					/*}
					else if($check[0]->kolektif_name_id!=""){
						$tmp = explode(',',$check[0]->kolektif_name_id);
						$arr = array();
						foreach($tmp as $v){
							if($v!=$admin)
								$arr[] = $v;
						}
						$arr[] = $admin;

						$where = array(
							"id" => $id
						);
						$row=array(
							'kolektif_name_id' => implode(',',$arr),
							'modifieddate' => date('Y-m-d H:i:s'),
							'modifiedby' => $this->session->userdata('admin_id'),
						);
						$update = $this->main_mod->update('user_profiles',$where,$row);
						echo "valid";
					}
					else echo "not valid";*/
				} else echo "not valid";
			} catch (Exception $e) {
				//print_r($e);
				echo "not valid";
			}
		} else
			echo "not valid";
	}

	function deletekolektif()
	{
		$akses = array("0", "2");
		if (!in_array($this->session->userdata('type'), $akses)) {
			redirect('admin/dashboard');
			exit;
		}

		$idmember = $this->input->post('id') <> null ? $this->input->post('id') : "";
		$admin = $this->input->post('admin') <> null ? $this->input->post('admin') : "";

		if ($idmember == '') {
			redirect('admin/members');
			exit;
		}
		$this->load->model('main_mod');

		try {
			$check = $this->main_mod->msrwhere('user_profiles', array('user_id' => $idmember), 'id', 'desc')->result();

			if (isset($check[0])) {
				if ($check[0]->kolektif_ids != "") {
					$tmp = explode(',', $check[0]->kolektif_ids);
					$arr = array();
					foreach ($tmp as $v) {
						if ($v != $admin)
							$arr[] = $v;
					}

					$where = array(
						"id" => $idmember
					);
					$row = array(
						'kolektif_ids' => implode(',', $arr),
						'modifieddate' => date('Y-m-d H:i:s'),
						'modifiedby' => $this->session->userdata('admin_id'),
					);
					$update = $this->main_mod->update('user_profiles', $where, $row);
					echo "valid";
				} else echo "not valid";
			} else echo "not valid";
		} catch (Exception $e) {
			echo "not valid";
		}
	}

	function deletekolektifname()
	{
		$akses = array("0", "2");
		if (!in_array($this->session->userdata('type'), $akses)) {
			redirect('admin/dashboard');
			exit;
		}

		$idmember = $this->input->post('id') <> null ? $this->input->post('id') : "";
		$admin = $this->input->post('admin') <> null ? $this->input->post('admin') : "";

		if ($idmember == '') {
			redirect('admin/members');
			exit;
		}
		$this->load->model('main_mod');

		try {
			$check = $this->main_mod->msrwhere('user_profiles', array('user_id' => $idmember), 'id', 'desc')->result();

			if (isset($check[0])) {
				if ($check[0]->kolektif_name_id != "") {
					/*$tmp = explode(',',$check[0]->kolektif_name_id);
					$arr = array();
					foreach($tmp as $v){
						if($v!=$admin)
							$arr[] = $v;
					}*/

					$where = array(
						"id" => $idmember
					);
					$row = array(
						//'kolektif_name_id' => implode(',',$arr),
						'kolektif_name_id' => $admin,
						'modifieddate' => date('Y-m-d H:i:s'),
						'modifiedby' => $this->session->userdata('admin_id'),
					);
					$update = $this->main_mod->update('user_profiles', $where, $row);
					echo "valid";
				} else echo "not valid";
			} else echo "not valid";
		} catch (Exception $e) {
			echo "not valid";
		}
	}

	public function is_ip()
	{
		$id = $_GET['id'];
		$data = array();
		$this->load->model('main_mod');
		$data['is_ip'] = $this->main_mod->msrwhere('user_cert', array("user_id" => $id, "status" => 2), 'id', 'desc')->result();


		print_r(json_encode($data));
	}

	function bukti_upload()
	{
		$user_id = $this->input->post('user_id') <> null ? $this->input->post('user_id') : "";

		if ($user_id == '') {
			redirect('admin/dashboard');
			exit;
		}

		if (isset($_FILES['bukti']['name'])) {
			$valid_formats_img = array("jpg", "jpeg", "gif", "png", "pdf", "bmp");
			$name = $_FILES['bukti']['name'];
			$size = $_FILES['bukti']['size'];

			$extx = pathinfo($name, PATHINFO_EXTENSION);

			if (strlen($name)) {
				if (in_array(strtolower($extx), $valid_formats_img)) {
					if ($size < (710000)) {
						$actual_image_name = time() . "_BKT_" . $user_id . "." . $extx;

						$config['upload_path'] = './assets/uploads/pay/';
						$config['allowed_types'] = '*';
						$config['max_size']	= '710';
						$config['file_name'] = $actual_image_name;

						$this->load->library('upload', $config);

						if ($this->upload->do_upload('bukti')) {
							echo "<input type='hidden' id='bukti_image_url' value='" . $actual_image_name . "'>";
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
		$user_id = $this->input->post('user_id') <> null ? $this->input->post('user_id') : "";

		if ($user_id == '') {
			redirect('admin/dashboard');
			exit;
		}

		if (isset($_FILES['bukti']['name'])) {
			$valid_formats_img = array("jpg", "jpeg", "gif", "png", "pdf", "bmp");
			$name = $_FILES['bukti']['name'];
			$size = $_FILES['bukti']['size'];

			$extx = pathinfo($name, PATHINFO_EXTENSION);

			if (strlen($name)) {
				if (in_array(strtolower($extx), $valid_formats_img)) {
					if ($size < (710000)) {
						$actual_image_name = time() . "_BKT_" . $user_id . "." . $extx;

						$config['upload_path'] = './assets/uploads/pay/';
						$config['allowed_types'] = '*';
						$config['max_size']	= '710';
						$config['file_name'] = $actual_image_name;

						$this->load->library('upload', $config);

						if ($this->upload->do_upload('bukti')) {
							echo "<input type='hidden' id='bukti_image_url_' value='" . $actual_image_name . "'>";
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
		$user_id = $this->input->post('user_id') <> null ? $this->input->post('user_id') : "";

		if ($user_id == '') {
			redirect('admin/dashboard');
			exit;
		}

		if (isset($_FILES['bukti']['name'])) {
			$valid_formats_img = array("jpg", "jpeg", "gif", "png", "pdf", "bmp");
			$name = $_FILES['bukti']['name'];
			$size = $_FILES['bukti']['size'];

			$extx = pathinfo($name, PATHINFO_EXTENSION);

			if (strlen($name)) {
				if (in_array(strtolower($extx), $valid_formats_img)) {
					if ($size < (710000)) {
						$actual_image_name = time() . "_SIP_" . $user_id . "." . $extx;

						$config['upload_path'] = './assets/uploads/sip/';
						$config['allowed_types'] = '*';
						$config['max_size']	= '710';
						$config['file_name'] = $actual_image_name;

						$this->load->library('upload', $config);

						if ($this->upload->do_upload('bukti')) {
							echo "<input type='hidden' id='bukti_image_url_sip' value='" . $actual_image_name . "'>";
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

	function bukti_upload_kebenaran()
	{
		$user_id = $this->input->post('user_id') <> null ? $this->input->post('user_id') : "";

		if ($user_id == '') {
			redirect('admin/dashboard');
			exit;
		}

		if (isset($_FILES['kebenaran']['name'])) {
			$valid_formats_img = array("jpg", "jpeg", "gif", "png", "pdf", "bmp");
			$name = $_FILES['kebenaran']['name'];
			$size = $_FILES['kebenaran']['size'];

			$extx = pathinfo($name, PATHINFO_EXTENSION);

			if (strlen($name)) {
				if (in_array(strtolower($extx), $valid_formats_img)) {
					if ($size < (710000)) {
						$actual_image_name = time() . "_STR1_" . $user_id . "." . $extx;

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
		$user_id = $this->input->post('user_id') <> null ? $this->input->post('user_id') : "";

		if ($user_id == '') {
			redirect('admin/dashboard');
			exit;
		}

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
						$actual_image_name = time() . "_STR2_" . $user_id . "." . $extx;

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

	function update_bukti_upload()
	{
		$this->ajax_update_bukti_upload();
	}

	function ajax_update_bukti_upload()
	{
		$id = $this->input->post('id') <> null ? $this->input->post('id') : "";
		$user_id = $this->input->post('user_id') <> null ? $this->input->post('user_id') : "";

		if ($user_id == '') {
			redirect('admin/dashboard');
			exit;
		}

		if (isset($_FILES['bukti']['name'])) {
			$valid_formats_img = array("jpg", "jpeg", "gif", "png", "pdf", "bmp");
			$name = $_FILES['bukti']['name'];
			$size = $_FILES['bukti']['size'];

			$extx = pathinfo($name, PATHINFO_EXTENSION);

			if (strlen($name)) {
				if (in_array(strtolower($extx), $valid_formats_img)) {
					if ($size < (710000)) {
						$actual_image_name = time() . "_BKT_" . $user_id . "." . $extx;

						$config['upload_path'] = './assets/uploads/pay/';
						$config['allowed_types'] = '*';
						$config['max_size']	= '710';
						$config['file_name'] = $actual_image_name;

						$this->load->library('upload', $config);

						if ($this->upload->do_upload('bukti')) {
							$this->load->model('main_mod');
							$user_profiles = $this->main_mod->msrwhere('user_transfer', array('user_id' => $user_id, 'id' => $id), 'id', 'asc')->row();
							$temp = (isset($user_profiles->bukti) ? $user_profiles->bukti : '');

							$where = array(
								'user_id' => $user_id,
								'id' => $id
							);
							$row = array(
								'bukti' => $actual_image_name,
								'modifieddate' => date('Y-m-d H:i:s'),
								'modifiedby' => $this->session->userdata('admin_id'),
							);
							$update = $this->main_mod->update('user_transfer', $where, $row);

							echo 1;

							//echo "<input type='hidden' id='bukti_image_url' value='".$actual_image_name."'>";
							//echo "<a href='".base_url()."/assets/uploads/pay/".$actual_image_name."' target='_blank' class='ava_discus'>".$actual_image_name."</a>";
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

	function pay()
	{
		$user_id = $this->input->post('id') <> null ? $this->input->post('id') : "";

		if ($user_id == '') {
			redirect('admin/dashboard');
			exit;
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

			$check_transfer = $this->main_mod->msrwhere('members', array('person_id' => $user_id), 'id', 'desc')->result();
			$vnv_status = 0;
			if (isset($check_transfer[0]) && $pay_type == 1) {
				$vnv_status = 1;
			}

			$row = array(
				'user_id' => $user_id,
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

				'createdby' => $this->session->userdata('admin_id'),
			);

			if ($vnv_status == 1 && $pay_type == 1) {
				$row['modifieddate'] = date('Y-m-d H:i:s');
				$row['modifiedby'] = $this->session->userdata('user_id');
			}

			$insert = $this->main_mod->insert('user_transfer', $row);
			echo $insert;
		} catch (Exception $e) {
			print_r($e);
		}
		//}
		//else
		//	echo "not valid";

	}

	function pay_stri()
	{
		$user_id = $this->input->post('id') <> null ? $this->input->post('id') : "";

		if ($user_id == '') {
			redirect('admin/dashboard');
			exit;
		}
		$this->load->model('main_mod');
		//ORGANIZATIONS
		//$id = $this->input->post('id')<>null?$this->input->post('id'):"";
		$pay_type = $this->input->post('pay_type') <> null ? $this->input->post('pay_type') : "";
		$atasnama = $this->input->post('atasnama') <> null ? $this->input->post('atasnama') : "";
		$tgl = $this->input->post('tgl') <> null ? $this->input->post('tgl') : "";
		$bukti = $this->input->post('bukti') <> null ? $this->input->post('bukti') : "";
		$bukti_2 = $this->input->post('bukti_2') <> null ? $this->input->post('bukti_2') : "";
		$bukti_3 = $this->input->post('bukti_3') <> null ? $this->input->post('bukti_3') : "";
		$desc = $this->input->post('desc') <> null ? $this->input->post('desc') : "";
		//$sukarelatotal = 500000;
		$sukarelatotal = $this->input->post('sukarelatotal') <> null ? $this->input->post('sukarelatotal') : 0;
		$pay_type = 5;

		//if($bukti!=''){
		try {
			$row = array(
				'user_id' => $user_id,
				'pay_type' => $pay_type,
				//'rel_id' => $id,
				'atasnama' => $atasnama,
				'tgl' => $tgl,
				'bukti' => $bukti,
				'add_doc1' => $bukti_2,
				'add_doc2' => $bukti_3,
				'description' => $desc,

				'sukarelatotal' => $sukarelatotal,

				'createdby' => $this->session->userdata('admin_id'),
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

	function pay_stri_normal_va()
	{
		$user_id = $this->input->post('id') <> null ? $this->input->post('id') : "";
		$rel_id = $this->input->post('rel_id') <> null ? $this->input->post('rel_id') : "";

		if ($user_id == '' || $rel_id == '') {
			redirect('admin/dashboard');
			exit;
		}
		$this->load->model('main_mod');
		//ORGANIZATIONS
		//$id = $this->input->post('id')<>null?$this->input->post('id'):"";
		$pay_type = $this->input->post('pay_type') <> null ? $this->input->post('pay_type') : "";
		//$atasnama = $this->input->post('atasnama')<>null?$this->input->post('atasnama'):"";
		//$tgl = $this->input->post('tgl')<>null?$this->input->post('tgl'):"";
		//$bukti = $this->input->post('bukti')<>null?$this->input->post('bukti'):"";
		//$bukti_2 = $this->input->post('bukti_2')<>null?$this->input->post('bukti_2'):"";
		//$bukti_3 = $this->input->post('bukti_3')<>null?$this->input->post('bukti_3'):"";
		$desc = $this->input->post('desc') <> null ? $this->input->post('desc') : "";
		//$sukarelatotal = 500000;
		$sukarelatotal = $this->input->post('sukarelatotal') <> null ? $this->input->post('sukarelatotal') : 500000;
		$pay_type = 5;

		//if($bukti!=''){
		try {
			$row = array(
				'user_id' => $user_id,
				'pay_type' => $pay_type,
				'rel_id' => $rel_id,
				//'atasnama' => $atasnama,
				//'tgl' => $tgl,
				//'bukti' => $bukti,
				//'add_doc1' => $bukti_2,
				//'add_doc2' => $bukti_3,
				'description' => $desc,
				'vnv_status' => 1,

				'sukarelatotal' => $sukarelatotal,

				'createdby' => $this->session->userdata('admin_id'),

				'modifiedby' => $this->session->userdata('admin_id'),
				'modifieddate' => date('Y-m-d H:i:s'),

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

	function pay_stri_normal()
	{
		$user_id = $this->input->post('id') <> null ? $this->input->post('id') : "";
		$rel_id = $this->input->post('rel_id') <> null ? $this->input->post('rel_id') : "";

		if ($user_id == '' || $rel_id == '') {
			redirect('admin/dashboard');
			exit;
		}
		$this->load->model('main_mod');
		//ORGANIZATIONS
		//$id = $this->input->post('id')<>null?$this->input->post('id'):"";
		$pay_type = $this->input->post('pay_type') <> null ? $this->input->post('pay_type') : "";
		$atasnama = $this->input->post('atasnama') <> null ? $this->input->post('atasnama') : "";
		$tgl = $this->input->post('tgl') <> null ? $this->input->post('tgl') : "";
		$bukti = $this->input->post('bukti') <> null ? $this->input->post('bukti') : "";
		$sip = $this->input->post('sip') <> null ? $this->input->post('sip') : "";
		//$bukti_2 = $this->input->post('bukti_2')<>null?$this->input->post('bukti_2'):"";
		//$bukti_3 = $this->input->post('bukti_3')<>null?$this->input->post('bukti_3'):"";
		$desc = $this->input->post('desc') <> null ? $this->input->post('desc') : "";
		//$sukarelatotal = 500000;
		$sukarelatotal = $this->input->post('sukarelatotal') <> null ? $this->input->post('sukarelatotal') : 0;
		$pay_type = 5;

		//if($bukti!=''){
		try {
			$row = array(
				'user_id' => $user_id,
				'pay_type' => $pay_type,
				'rel_id' => $rel_id,
				'atasnama' => $atasnama,
				'tgl' => $tgl,
				'bukti' => ($bukti != '' ? $bukti : '-'),
				'sip' => $sip,
				//'add_doc1' => $bukti_2,
				//'add_doc2' => $bukti_3,
				'description' => $desc,
				//'vnv_status' => 1,

				'sukarelatotal' => $sukarelatotal,

				'createdby' => $this->session->userdata('admin_id'),

				//'modifiedby' => $this->session->userdata('admin_id'),
				//'modifieddate' => date('Y-m-d H:i:s'),

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

	function ktp_upload()
	{
		$user_id = $this->input->post('id') <> null ? $this->input->post('id') : "";

		if ($user_id == '') {
			redirect('admin/dashboard');
			exit;
		}

		if (isset($_FILES['ktp']['name'])) {
			$this->load->model('main_mod');
			$valid_formats_img = array("jpg", "jpeg", "gif", "png", "pdf", "bmp");
			$name = $_FILES['ktp']['name'];
			$size = $_FILES['ktp']['size'];

			$extx = pathinfo($name, PATHINFO_EXTENSION);

			if (strlen($name)) {
				//list($txt, $ext) = explode(".", $name);
				if (in_array(strtolower($extx), $valid_formats_img)) {
					if ($size < (710000)) {
						//$actual_image_name = time().substr(str_replace(" ", "_", $txt), 5).".".$ext;
						$actual_image_name = time() . "_KTP_" . $user_id . "." . $extx;

						$config['upload_path'] = './assets/uploads/';
						$config['allowed_types'] = '*';
						$config['max_size']	= '710';
						$config['file_name'] = $actual_image_name;

						$this->load->library('upload', $config);

						if ($this->upload->do_upload('ktp')) {

							$where = array(
								"user_id" => $user_id
							);
							$row = array(
								'id_file' => $actual_image_name,
								'modifieddate' => date('Y-m-d H:i:s'),
								'modifiedby' => $this->session->userdata('admin_id'),
							);
							$update = $this->main_mod->update('user_profiles', $where, $row);

							echo "<input type='hidden' id='ktp_image_url' value='" . $actual_image_name . "'>";
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

	// ------------------------------------------------------------ Tambahan untuk Upload E_SKIP dan E-STRI by Ipur ----------------------------------------------------- -->
	public function fungsiUploadGambar()
	{
		$no_kta = $_POST['id_f'];

		$this->load->model('main_mod');
		$id_faip = $this->uri->segment(4);
		$this->load->model('faip_model');
		$faip = $this->faip_model->ambil_user_faip_idnya($no_kta);
		$id_userid = $faip->user_id;
		$id_table = $faip->user_id;
		$nama = $faip->nama;
		$faip_idd = $faip->id;

		$file_name1 = $_FILES['gambar']['name'];
		$file_name = trim(str_replace(' ', '_', $file_name1));
		$nama_file = 'KTA-' . $no_kta . '-' . $file_name;

		$config['upload_path']          = FCPATH . '/assets/SKIP/';
		$config['allowed_types']        = 'gif|jpg|jpeg|png|pdf';
		$config['file_name']            = $nama_file;
		$config['overwrite']            = true; // tindih file dengan file baru
		$config['max_size']             = 1024; // batas ukuran file: 1MB
		$config['max_width']            = 1080; // batas lebar gambar dalam piksel
		$config['max_height']           = 1080; // batas tinggi gambar dalam piksel
		$this->load->library('upload', $config);
		$this->upload->do_upload('gambar');

		$isi_skip = $nama;

		$data_user_skip = [
			'skip'    => $nama_file,
			'user_id' => $id_table,
			'kta'     => $no_kta,
			'faip_id' => $faip_idd,
			'nama'    => $nama
		];

		$golek = $this->faip_model->get_user_skip_kta($no_kta);
		if ($golek == null) {
			$this->faip_model->save_user_skip($data_user_skip);
		} else {
			$this->faip_model->update_user_skip($no_kta, $data_user_skip);
		}

		redirect(base_url('/admin/members/faip'), 'refresh');
	}

	public function fungsiUpload_estri()
	{
		$no_kta = $_POST['id_f'];

		$this->load->model('main_mod');
		$id_faip = $this->uri->segment(4);
		$this->load->model('faip_model');
		$faip = $this->faip_model->get_members_certificate_kta($no_kta);
		$userid = $faip->person_id;
		$nama = $faip->add_name;

		$file_name1 = $_FILES['gambar']['name'];
		$file_name = trim(str_replace(' ', '_', $file_name1));
		$nama_file = 'KTA-' . $no_kta . '-' . $file_name;

		$config['upload_path']          = FCPATH . '/assets/ESTRI/';
		//     $config['upload_path']          = FCPATH.'/assets/SKIP/';
		$config['allowed_types']        = 'gif|jpg|jpeg|png|pdf';
		$config['file_name']            = $nama_file;
		$config['overwrite']            = true; // tindih file dengan file baru
		$config['max_size']             = 2048; // batas ukuran file: 2MB
		$config['max_width']            = 1080; // batas lebar gambar dalam piksel
		$config['max_height']           = 1080; // batas tinggi gambar dalam piksel
		$this->load->library('upload', $config);
		$this->upload->do_upload('gambar');

		$isi_stri = $nama;

		$data_user_estri = [
			'estri'    => $nama_file,
			'user_id' => $userid,
			'kta'     => $no_kta,
			'nama'    => $nama
		];

		$golek = $this->faip_model->get_user_estri_kta($no_kta);
		if ($golek == null) {
			$this->faip_model->save_user_estri($data_user_estri);
		} else {
			$this->faip_model->update_user_estri($no_kta, $data_user_estri);
		}

		redirect(base_url('/admin/members/stri'), 'refresh');
	}

	// -------------------------------------------------------------------------------------------------------------------------------------------------

	public function fungsiUploadGambarr()
	{
		$no_kta = $_POST['id_f'];

		$this->load->model('main_mod');
		$id_faip = $this->uri->segment(4);
		$this->load->model('pkb_model');
		$faip = $this->pkb_model->ambil_user_pkb_idnya($no_kta);
		$id_userid = $faip->user_id;
		$id_table = $faip->user_id;
		$nama = $faip->nama;
		$faip_idd = $faip->id;

		$file_name1 = $_FILES['gambar']['name'];
		$file_name = trim(str_replace(' ', '_', $file_name1));
		$nama_file = 'KTA-' . $no_kta . '-' . $file_name;

		$config['upload_path']          = FCPATH . '/assets/SKIP/';
		$config['allowed_types']        = 'gif|jpg|jpeg|png|pdf';
		$config['file_name']            = $nama_file;
		$config['overwrite']            = true; // tindih file dengan file baru
		$config['max_size']             = 1024; // batas ukuran file: 1MB
		$config['max_width']            = 1080; // batas lebar gambar dalam piksel
		$config['max_height']           = 1080; // batas tinggi gambar dalam piksel
		$this->load->library('upload', $config);
		$this->upload->do_upload('gambar');

		$isi_skip = $nama;

		$data_user_skip = [
			'skip'    => $nama_file,
			'user_id' => $id_table,
			'kta'     => $no_kta,
			'faip_id' => $faip_idd,
			'nama'    => $nama
		];

		$golek = $this->faip_model->get_user_skip_kta($no_kta);
		if ($golek == null) {
			$this->faip_model->save_user_skip($data_user_skip);
		} else {
			$this->faip_model->update_user_skip($no_kta, $data_user_skip);
		}

		redirect(base_url('/admin/members/pkb'), 'refresh');
	}


	function photo_upload()
	{
		$user_id = $this->input->post('id') <> null ? $this->input->post('id') : "";

		if ($user_id == '') {
			redirect('admin/dashboard');
			exit;
		}

		if (isset($_FILES['photo']['name'])) {
			$this->load->model('main_mod');
			$valid_formats_img = array("jpg", "jpeg", "png"); //, "gif","pdf","bmp"
			$name = $_FILES['photo']['name'];
			$size = $_FILES['photo']['size'];

			$extx = pathinfo($name, PATHINFO_EXTENSION);

			if (strlen($name)) {
				//list($txt, $ext) = explode(".", $name);
				if (in_array(strtolower($extx), $valid_formats_img)) {
					if ($size < (710000)) {
						//$actual_image_name = time().substr(str_replace(" ", "_", $txt), 5).".".$ext;

						$actual_image_name = time() . "_PHOTO_" . $user_id . "." . $extx;

						$config['upload_path'] = './assets/uploads/';
						$config['allowed_types'] = '*';
						$config['max_size']	= '710';
						$config['file_name'] = $actual_image_name;

						$this->load->library('upload', $config);

						if ($this->upload->do_upload('photo')) {


							$where = array(
								"user_id" => $user_id
							);
							$row = array(
								'photo' => $actual_image_name,
								'modifieddate' => date('Y-m-d H:i:s'),
								'modifiedby' => $this->session->userdata('admin_id'),
							);
							$update = $this->main_mod->update('user_profiles', $where, $row);

							echo "<input type='hidden' id='photo_image_url' value='" . $actual_image_name . "'>";

							echo "<a href='" . base_url() . "/assets/uploads/" . $actual_image_name . "' target='_blank' class='ava_discus'>" . $actual_image_name . "</a>";
						} else
							echo "<span style:'color:red'>Format file yang diijinkan (jpg|png|jpeg).</span>"; //|gif|pdf|bmp
					} else
						echo "<span style:'color:red'>Sorry, maximum file size should be 700 KB</span>";
				} else
					echo "<span style:'color:red'>Format file yang diijinkan (jpg|png|jpeg).</span>";
			} else
				echo "<span style:'color:red'>Please select an image (jpg|png|jpeg).</span>";
		} else
			echo "<span style:'color:red'>Please select an image (jpg|png|jpeg).</span>";
	}



	public function stri_member()
	{
		if ($this->session->userdata('type') != "0" && $this->session->userdata('type') != "2" && $this->session->userdata('type') != "12" && $this->session->userdata('type') != "13" && $this->session->userdata('type') != "1") {
			redirect('admin/dashboard');
			exit;
		}
		if ($this->session->userdata('type') == "1" && ($this->session->userdata('admin_id') != "670" && $this->session->userdata('admin_id') != "659")) {
			redirect('admin/dashboard');
			exit;
		}

		$data['title'] = ''; //SITE_NAME.': Manage Members';
		$data['msg'] = '';

		//Pagination starts
		$total_rows = $this->members_model->record_count_stri_member('users');
		$config = pagination_configuration(base_url("admin/members/stri_member"), $total_rows, 10, 4, 5, true);

		$this->pagination->initialize($config);
		$page = ($this->uri->segment(3)) ? $this->uri->segment(4) : 0;
		$page_num = $page - 1;
		$page_num = ($page_num < 0) ? '0' : $page_num;
		$page = $page_num * $config["per_page"];
		$data["links"] = $this->pagination->create_links();
		//Pagination ends
		//print_r($data["links"]);

		$obj_result = $this->members_model->get_all_stri_member($config["per_page"], $page);
		$data['result'] = $obj_result;
		$data["total_rows"] = $total_rows;

		$data["m_cab"] = $this->members_model->get_all_cabang();
		$data["m_bk"] = $this->members_model->get_all_bk();
		$data["m_hkk"] = $this->members_model->get_all_hkk();
		$data["kode_all_kolektif"] = $this->members_model->get_all_kode_kolektif2();

		$this->load->view('admin/stri_member_view', $data);
		return;
	}

	public function search_stri_member()
	{
		if ($this->session->userdata('type') != "0" && $this->session->userdata('type') != "2" && $this->session->userdata('type') != "12" && $this->session->userdata('type') != "13" && $this->session->userdata('type') != "1") {
			redirect('admin/dashboard');
			exit;
		}

		if ($this->session->userdata('type') == "1" && ($this->session->userdata('admin_id') != "670" && $this->session->userdata('admin_id') != "659" && $this->session->userdata('admin_id') != "756")) {
			redirect('admin/dashboard');
			exit;
		}

		$data['title'] = ''; //SITE_NAME.': Manage Employers';
		$data['msg'] = '';
		$search_name = '';
		$search_kta = '';

		$this->form_validation->set_rules('firstname', 'firstname', 'trim');
		$this->form_validation->set_rules('kta', 'kta', 'trim');
		$this->form_validation->set_rules('filter_type', 'Type', 'trim');
		$this->form_validation->set_rules('filter_status', 'Status', 'trim');
		$this->form_validation->set_rules('filter_cab', 'Wilayah/Cabang', 'trim');
		$this->form_validation->set_rules('filter_bk', 'BK', 'trim');
		$this->form_validation->set_rules('filter_hkk', 'HKK', 'trim');
		$this->form_validation->set_rules('stri_period_start', 'Start', 'trim');
		$this->form_validation->set_rules('stri_period_end', 'End', 'trim');
		$this->form_validation->set_rules('filter_kolektif', 'filter kolektif', 'trim');
		$this->form_validation->run();

		if ($_GET) {
			$search_name 	= 	$this->input->get('firstname');
			$search_kta 	= 	$this->input->get('kta');

			$search_type 	= 	$this->input->get('filter_type');
			$search_status 	= 	$this->input->get('filter_status');
			$search_cab 	= 	$this->input->get('filter_cab');
			$search_bk	 	= 	$this->input->get('filter_bk');
			$search_hkk	 	= 	$this->input->get('filter_hkk');

			$search_stri_period_start	 = 	$this->input->get('stri_period_start');
			$search_stri_period_end	 	= 	$this->input->get('stri_period_end');
			$search_filter_kolektif 	= 	$this->input->get('filter_kolektif');
		}
		if ($search_name == ''  && $search_kta == '' && $search_type == '' && $search_status == '' && $search_cab == '' && $search_bk == '' && $search_hkk == '' && $search_stri_period_start == '' && $search_stri_period_end == '' && $search_filter_kolektif == '') {
			redirect(base_url('admin/members/stri_member'));
			return;
		}
		$new_array = array();
		$search_data = array();
		$search_data2 = array();
		if (isset($search_name) && $search_name != '') {
			$search_data['firstname'] = $search_name;
			//$search_data2['lower(add_name)']=strtolower($search_name);
			//$search_data2['REPLACE(lower(add_name)," ","")']=str_replace(' ','',strtolower($search_name));
			$search_data2['REPLACE(concat(lower(COALESCE(firstname,"")),lower(COALESCE(lastname,"")))," ","")'] = str_replace(' ', '', strtolower($search_name));
		}
		if (isset($search_kta) && $search_kta != '') {
			$search_data['kta'] = $search_kta;
			$search_data2['no_kta'] = ltrim($search_kta, '0');
		}

		if (isset($search_status) && $search_status != '') {
			$search_data['filter_status'] = $search_status;
			$search_data2['filter_status'] = $search_status;
		}

		if (isset($search_type) && $search_type != '') {
			$search_data['filter_type'] = $search_type;
			$search_data2['filter_type'] = $search_type;
		}

		if (isset($search_cab) && $search_cab != '') {
			$search_data['filter_cab'] = $search_cab;
			$search_data2['filter_cab'] = $search_cab;
		}

		if (isset($search_bk) && $search_bk != '') {
			$search_data['filter_bk'] = $search_bk;
			$search_data2['filter_bk'] = $search_bk;
		}

		if (isset($search_hkk) && $search_hkk != '') {
			$search_data['filter_hkk'] = $search_hkk;
			$search_data2['filter_hkk'] = $search_hkk;
		}

		if (isset($search_stri_period_start) && $search_stri_period_start != '' && isset($search_stri_period_end) && $search_stri_period_end != '') {
			$time1 = strtotime($search_stri_period_start);
			$time2 = strtotime($search_stri_period_end);

			if ($time2 >= $time1) {
				$search_data2['stri_period'] = 'date(user_transfer.createddate) between "' . $search_stri_period_start . '" and "' . $search_stri_period_end . '"';
				$search_data['stri_period_start'] = $search_stri_period_start;
				$search_data['stri_period_end'] = $search_stri_period_end;
			} else {
				$search_data2['stri_period'] = 'date(user_transfer.createddate) between "' . date('Y-m-d') . '" and "' . date('Y-m-d') . '"';
				$search_data['stri_period_start'] = date('Y-m-d');
				$search_data['stri_period_end'] = date('Y-m-d');
			}
		}

		if (isset($search_filter_kolektif) && $search_filter_kolektif != '') {
			$search_data['filter_kolektif'] = $search_filter_kolektif;
			$search_data2['kolektif_name_id'] = $search_filter_kolektif;
		}

		$wild_card = '';

		$url_params = implode(
			'&amp;',
			array_map(
				function ($key, $val) {
					return urlencode($key) . '=' . urlencode($val);
				},
				array_keys($search_data),
				$search_data
			)
		);
		//Pagination starts
		$total_rows = $this->members_model->search_record_count_stri_member('users', $search_data2);
		$config = pagination_configuration_search(base_url("admin/members/search_stri_member/?" . $url_params), $total_rows, 10, 3, 5, true);

		$this->pagination->initialize($config);
		$page = $this->input->get('per_page');
		$page_num = $page - 1;
		$page_num = ($page_num < 0) ? '0' : $page_num;
		$page = $page_num * $config["per_page"];
		$data["links"] = $this->pagination->create_links();
		$data["total_rows"] = $total_rows;
		//Pagination ends
		$obj_result = $this->members_model->search_all_stri_member($config["per_page"], $page, $search_data2, $wild_card);
		$data['result'] = $obj_result;
		$data['search_data'] = $search_data;

		$data["m_cab"] = $this->members_model->get_all_cabang();
		$data["m_bk"] = $this->members_model->get_all_bk();
		$data["m_hkk"] = $this->members_model->get_all_hkk();
		$data["kode_all_kolektif"] = $this->members_model->get_all_kode_kolektif2();
		$this->load->view('admin/stri_member_view', $data);
		return;
	}

	private function _is_user_kolektif()
	{
		return ($this->session->userdata('type') == "11" || $this->session->userdata('type') == "15");
	}

	public function stri()
	{
		$access_types = array("0", "2", "12", "13", "11", "1", "9");
		if (
			!in_array($this->session->userdata('type'), $access_types) ||
			($this->session->userdata('type') == '1' && in_array($this->session->userdata('admin_id'), $this->direktur_lski_ids) === FALSE)
		) {
			redirect('admin/dashboard');
			exit;
		}

		$data['title'] = ''; //SITE_NAME.': Manage Members';
		$data['msg'] = '';

		$this->load->model('main_mod');
		$admin = $this->main_mod->msrquery('select code_bk_hkk as bk ,code_wilayah as wil from admin where id = ' . $this->session->userdata('admin_id'))->result();
		$bk  = (isset($admin[0]->bk) ? $admin[0]->bk : "");
		$wil = (isset($admin[0]->wil) ? $admin[0]->wil : "");
		if (strpos($bk, ',') !== false) {
			$bk = explode(",", $bk);
		}
		if (strpos($wil, ',') !== false) {
			$wil = explode(",", $wil);
		}
		$is_kolektif = $this->_is_user_kolektif();

		//Pagination starts
		$total_rows = $this->members_model->record_count_stri('users', $bk, $wil, $is_kolektif);
		$config = pagination_configuration(base_url("admin/members/stri"), $total_rows, 10, 4, 5, true);

		$this->pagination->initialize($config);
		$page = ($this->uri->segment(3)) ? $this->uri->segment(4) : 0;
		$page_num = $page - 1;
		$page_num = ($page_num < 0) ? '0' : $page_num;
		$page = $page_num * $config["per_page"];
		$data["links"] = $this->pagination->create_links();
		//Pagination ends
		//print_r($data["links"]);

		$obj_result = $this->members_model->get_all_stri($config["per_page"], $page, $bk, $wil, $is_kolektif);
		$data['result'] = $obj_result;
		$data["total_rows"] = $total_rows;

		//$data["m_cab"] = $this->members_model->get_all_cabang();
		//$data["m_bk"] = $this->members_model->get_all_bk();

		$data["m_cab"] = $this->members_model->get_all_cabang_wilayah();
		$data["m_bk"] = $this->members_model->get_all_bk();
		$data["m_hkk"] = $this->members_model->get_all_hkk();

		//$data["members"] = $this->members_model->get_all_member_sip();
		//$data["sip"] = $this->members_model->get_all_sip();
		//print_r($data["members"]);
		$this->load->view('admin/stri_view', $data);
		//return;

	}

	public function search_stri()
	{
		if (
			$this->session->userdata('type') != "0" && $this->session->userdata('type') != "2" && $this->session->userdata('type') != "12" && $this->session->userdata('type') != "13"
			&& $this->session->userdata('type') != "11" && $this->session->userdata('type') != "1" && $this->session->userdata('type') != "9"
		) {
			redirect('admin/dashboard');
			exit;
		}

		if ($this->session->userdata('type') == "1" && ($this->session->userdata('admin_id') != "670" && $this->session->userdata('admin_id') != "659"
			&& $this->session->userdata('admin_id') != "784" && $this->session->userdata('admin_id') != "782")) {
			redirect('admin/dashboard');
			exit;
		}

		$data['title'] = ''; //SITE_NAME.': Manage Employers';
		$data['msg'] = '';
		$search_name = '';
		$search_kta = '';

		$this->form_validation->set_rules('firstname', 'firstname', 'trim');
		$this->form_validation->set_rules('kta', 'kta', 'trim');

		$this->form_validation->set_rules('nomor', 'nomor', 'trim');
		$this->form_validation->set_rules('filter_type', 'Type', 'trim');
		$this->form_validation->set_rules('filter_status', 'Status', 'trim');
		$this->form_validation->set_rules('filter_cab', 'wilayah/cabang', 'trim');
		$this->form_validation->set_rules('filter_bk', 'BK', 'trim');
		$this->form_validation->set_rules('filter_hkk', 'BK', 'trim');
		$this->form_validation->set_rules('filter_program', 'Program Profesi', 'trim');
		$this->form_validation->set_rules('stri_period_start', 'Start', 'trim');
		$this->form_validation->set_rules('stri_period_end', 'End', 'trim');
		$this->form_validation->run();

		if ($_GET) {
			$search_name 	= 	$this->input->get('firstname');
			$search_kta 	= 	$this->input->get('kta');

			$search_nomor 	= 	$this->input->get('nomor');
			$search_type 	= 	$this->input->get('filter_type');
			$search_status 	= 	$this->input->get('filter_status');
			$search_cab 	= 	$this->input->get('filter_cab');
			$search_bk 		= 	$this->input->get('filter_bk');
			$search_hkk 	= 	$this->input->get('filter_hkk');
			$search_program = 	$this->input->get('filter_program');
			$search_stri_period_start	 = 	$this->input->get('stri_period_start');
			$search_stri_period_end	 	= 	$this->input->get('stri_period_end');
		}
		if ($search_name == ''  && $search_kta == '' && $search_cab == '' && $search_bk == '' && $search_hkk == '' && $search_nomor == '' && $search_type == '' && $search_status == '' && $search_program == '' && $search_stri_period_start == '' && $search_stri_period_end == '') {
			redirect(base_url('admin/members/stri'));
			return;
		}
		$new_array = array();
		$search_data = array();
		$search_data2 = array();
		if (isset($search_name) && $search_name != '') {
			$search_data['firstname'] = $search_name;
			//$search_data2['lower(add_name)']=strtolower($search_name);
			$search_data2['REPLACE(lower(add_name)," ","")'] = str_replace(' ', '', strtolower($search_name));
			//$search_data2['REPLACE(concat(lower(COALESCE(firstname,"")),lower(COALESCE(lastname,"")))," ","")']=str_replace(' ','',strtolower($search_name));
		}
		if (isset($search_kta) && $search_kta != '') {
			$search_data['kta'] = $search_kta;
			$search_data2['members_certificate.no_kta'] = ltrim($search_kta, '0');
		}

		if (isset($search_nomor) && $search_nomor != '') {
			$search_data['nomor'] = $search_nomor;
			$search_data2['nomor'] = $search_nomor;
		}

		if (isset($search_type) && $search_type != '') {
			$search_data['filter_type'] = $search_type;
			$search_data2['filter_type'] = $search_type;
		}

		if (isset($search_status) && $search_status != '') {
			$search_data['filter_status'] = $search_status;
			$search_data2['filter_status'] = $search_status;
		}

		if (isset($search_cab) && $search_cab != '') {
			$search_data['filter_cab'] = $search_cab;
			$search_data2['filter_cab'] = $search_cab;
		}

		if (isset($search_bk) && $search_bk != '') {
			$search_data['filter_bk'] = $search_bk;
			$search_data2['filter_bk'] = $search_bk;
		}

		if (isset($search_hkk) && $search_hkk != '') {
			$search_data['filter_hkk'] = $search_hkk;
			$search_data2['filter_hkk'] = $search_hkk;
		}

		if (isset($search_program) && $search_program != '') {
			$search_data['filter_program'] = $search_program;
			$search_data2['stri_tipe'] = $search_program;
		}

		if (isset($search_stri_period_start) && $search_stri_period_start != '' && isset($search_stri_period_end) && $search_stri_period_end != '') {
			$time1 = strtotime($search_stri_period_start);
			$time2 = strtotime($search_stri_period_end);

			if ($time2 >= $time1) {
				$search_data2['stri_period'] = 'date(e.createddate) between "' . $search_stri_period_start . '" and "' . $search_stri_period_end . '"';
				$search_data['stri_period_start'] = $search_stri_period_start;
				$search_data['stri_period_end'] = $search_stri_period_end;
			} else {
				$search_data2['stri_period'] = 'date(e.createddate) between "' . date('Y-m-d') . '" and "' . date('Y-m-d') . '"';
				$search_data['stri_period_start'] = date('Y-m-d');
				$search_data['stri_period_end'] = date('Y-m-d');
			}
		}

		$wild_card = '';

		$url_params = implode(
			'&amp;',
			array_map(
				function ($key, $val) {
					return urlencode($key) . '=' . urlencode($val);
				},
				array_keys($search_data),
				$search_data
			)
		);

		$this->load->model('main_mod');
		$admin = $this->main_mod->msrquery('select code_bk_hkk as bk,code_wilayah as wil from admin where id = ' . $this->session->userdata('admin_id'))->result();
		$bk  = (isset($admin[0]->bk) ? $admin[0]->bk : "");
		$wil = (isset($admin[0]->wil) ? $admin[0]->wil : "");
		if (strpos($bk, ',') !== false) {
			$bk = explode(",", $bk);
		}
		if (strpos($wil, ',') !== false) {
			$wil = explode(",", $wil);
		}
		$is_kolektif = (($this->session->userdata('type') == "11" || $this->session->userdata('type') == "15") ? true : false);


		//Pagination starts
		$total_rows = $this->members_model->search_record_count_stri('users', $search_data2, $bk, $wil, $is_kolektif);
		$config = pagination_configuration_search(base_url("admin/members/search_stri/?" . $url_params), $total_rows, 10, 3, 5, true);

		$this->pagination->initialize($config);
		$page = $this->input->get('per_page');
		$page_num = $page - 1;
		$page_num = ($page_num < 0) ? '0' : $page_num;
		$page = $page_num * $config["per_page"];
		$data["links"] = $this->pagination->create_links();
		$data["total_rows"] = $total_rows;
		//Pagination ends
		$obj_result = $this->members_model->search_all_stri($config["per_page"], $page, $search_data2, $wild_card, $bk, $wil, $is_kolektif);
		$data['result'] = $obj_result;
		$data['search_data'] = $search_data;

		//$data["m_cab"] = $this->members_model->get_all_cabang();
		//$data["m_bk"] = $this->members_model->get_all_bk();

		$data["m_cab"] = $this->members_model->get_all_cabang_wilayah();
		$data["m_bk"] = $this->members_model->get_all_bk();
		$data["m_hkk"] = $this->members_model->get_all_hkk();

		$this->load->view('admin/stri_view', $data);
		return;
	}

	public function ajax_member_search()
	{
		$id = $_GET['id'];
		$page = $_GET['page'];
		$data = array();
		if ($id == 'undefined') $id = null;

		$data = $this->members_model->get_all_member_sip($id, $page);
		print_r(json_encode($data));
	}

	function setstri()
	{
		if ($this->session->userdata('type') != "0" && $this->session->userdata('type') != "2" && $this->session->userdata('type') != "12" && $this->session->userdata('type') != "13") {
			redirect('admin/dashboard');
			exit;
		}

		$idmember = $this->input->post('id') <> null ? $this->input->post('id') : "";

		if ($idmember == '') {
			echo json_encode(array("code" => "failed", "msg" => "ID member is not specified in the request param"));
			//redirect('admin/members'); // _ER: Commented. I prefer to stay in the current page
			exit;
		}

		$stri_cabang = $this->input->post('stri_cabang') <> null ? $this->input->post('stri_cabang') : "";
		$stri_kp = $this->input->post('stri_kp') <> null ? $this->input->post('stri_kp') : "";
		$stri_bk = $this->input->post('stri_bk') <> null ? $this->input->post('stri_bk') : "";
		$stri_subbk = $this->input->post('stri_subbk') <> null ? $this->input->post('stri_subbk') : "";
		$stri_type = $this->input->post('stri_type') <> null ? $this->input->post('stri_type') : "";
		$stri_tipe = $this->input->post('stri_tipe') <> null ? $this->input->post('stri_tipe') : "";
		$warga = $this->input->post('warga') <> null ? $this->input->post('warga') : "";
		$nama_stri = $this->input->post('nama_stri') <> null ? $this->input->post('nama_stri') : "";

		$instansi = $this->input->post('instansi') <> null ? $this->input->post('instansi') : "";
		$tgl_sk = $this->input->post('tgl_sk') <> null ? $this->input->post('tgl_sk') : "";
		$from = $this->input->post('from') <> null ? $this->input->post('from') : "";
		$until = $this->input->post('until') <> null ? $this->input->post('until') : "";
		$id_pay = $this->input->post('id_pay') <> null ? $this->input->post('id_pay') : "";

		$num = $this->input->post('num') <> null ? $this->input->post('num') : "";
		$stri_ttd = $this->input->post('stri_ttd') <> null ? $this->input->post('stri_ttd') : "";

		$this->load->model('main_mod');
		try {
			$idx = $this->members_model->last_stri(); //$stri_type, $stri_bk, $stri_subbk

			$skip = $this->members_model->get_stri_member_by_id($id_pay);
			if (isset($skip[0]))
				$skip = $skip[0];

			if ($num == "") {
				$ck = $this->members_model->check_last_stri($idx->jml + 1);
				$tm = $idx->jml + 1;
				if (isset($ck->stri_id)) {
					$x = 1;
					$tm++;
					do {
						$ck = $this->members_model->check_last_stri($tm);
						if (!isset($ck->stri_id)) {
							$x = 2;
						}
					} while ($x <= 1);
				}

				$num = ltrim($tm, '0');
				$num =  str_pad(($num), 7, '0', STR_PAD_LEFT);
				//$num = ltrim($idx->jml, '0');
				//$num =  str_pad(($num+1), 7, '0', STR_PAD_LEFT);
			}

			$check = $this->main_mod->msrwhere('members_certificate', array("LPAD(stri_pm, 2, '0') = '" . str_pad($stri_kp, 2, '0', STR_PAD_LEFT) . "'" => null, "LPAD(stri_id, 7, '0') = '" . str_pad($num, 7, '0', STR_PAD_LEFT) . "'" => null, 'status' => 1), 'id', 'desc')->result();
			if (!isset($check[0])) {
				$kta = $this->members_model->get_kta_by_personid($idmember);
				$kta = (isset($kta->no_kta) ? $kta->no_kta : "");

				$th = date('y');

				$rowInsert = array();

				if (isset($skip->ip_seq)) {

					$th = substr($skip->startyear, 0, 4);
					$th = substr($th, -2);

					$rowInsert = array(
						'person_id' => $idmember,

						'no_kta' => $kta,
						'certificate_type' => $stri_type,

						'skip_id' => str_pad($skip->ip_seq, 6, '0', STR_PAD_LEFT),
						'skip_code_wilayah' => str_pad($skip->ip_kta_wilcab, 4, '0', STR_PAD_LEFT),
						'skip_code_bk_hkk' => str_pad($skip->ip_bk, 2, '0', STR_PAD_LEFT),
						'skip_pm' => str_pad($skip->ip_rev, 2, '0', STR_PAD_LEFT),
						//'skip_sub_code_bk_hkk' => $stri_subbk,
						'skip_sk' => $skip->startyear,
						'skip_from_date' => $skip->startyear,
						'skip_thru_date' => $skip->endyear,


						'stri_id' => str_pad($num, 7, '0', STR_PAD_LEFT),
						'stri_code_wilayah' => $stri_cabang,
						'stri_code_bk_hkk' => str_pad($skip->ip_bk, 2, '0', STR_PAD_LEFT),
						'stri_pm' => $stri_kp,
						'stri_tipe' => $stri_tipe,
						'warga' => $warga,
						'add_name' => strtoupper($nama_stri),
						'th'	=> $th,
						'stri_sub_code_bk_hkk' => $stri_subbk,
						'stri_sk' => date('Y-m-d', strtotime($tgl_sk)),
						'stri_from_date' => date('Y-m-d', strtotime($from)),
						'stri_thru_date' => date('Y-m-d', strtotime("+5 years", strtotime($from))),
						'status' => 1,

						'createdby' => $this->session->userdata('admin_id'),
						'stri_ttd' => $stri_ttd
					);
				} else {

					//$th = substr($from,0,4);
					//$th = substr($th,-2);

					$rowInsert = array(
						'person_id' => $idmember,

						'no_kta' => $kta,
						'certificate_type' => $stri_type,
						'stri_id' => str_pad($num, 7, '0', STR_PAD_LEFT),
						'stri_code_wilayah' => $stri_cabang,
						'stri_code_bk_hkk' => $stri_bk,
						'stri_pm' => $stri_kp,
						'stri_tipe' => $stri_tipe,
						'warga' => $warga,
						'add_name' => strtoupper($nama_stri),
						'th'	=> $th,
						'stri_sub_code_bk_hkk' => $stri_subbk,
						'stri_sk' => date('Y-m-d', strtotime($tgl_sk)),
						'stri_from_date' => date('Y-m-d', strtotime($from)),
						'stri_thru_date' => date('Y-m-d', strtotime("+3 years", strtotime($from))),
						'status' => 1,

						'createdby' => $this->session->userdata('admin_id'),
						'stri_ttd' => $stri_ttd

					);
				}

				$where = array(
					"LPAD(no_kta, 6, '0') = '" . str_pad($kta, 6, '0', STR_PAD_LEFT) . "'" => null
				);
				$row = array(
					'status' => 0,
					'modifieddate' => date('Y-m-d H:i:s'),
					'modifiedby' => $this->session->userdata('admin_id'),
					'stri_ttd' => $stri_ttd
				);
				$update = $this->main_mod->update('members_certificate', $where, $row);
				//--------------------------------------------------------------------------------------------------------- Tak digunakan
				/*$check = $this->main_mod->msrwhere('members_certificate',array('person_id'=>$idmember,'status'=>1),'id','desc')->result();
				if(isset($check[0]))
				{
					$where = array(
						"person_id" => $idmember,
					);
					$row=array(
						'status' => 1,
						//'updated_at' => date('Y-m-d H:i:s'),
						//'updated_by' => $this->session->userdata('user_id'),
					);
					$update = $this->main_mod->update('members_certificate',$where,$row);


					$this->members_model->insert_stri('members_certificate',$rowInsert);
				}
				else{*/
				//----------------------------------------------------------------------------------------------------------------------------------
				$this->members_model->insert_stri('members_certificate', $rowInsert);
				//}

				$rowInsert = array(
					'user_id' => $idmember,
					'id_pay' => $id_pay,
					'stri_id' => $num,
					'tgl_sk' => date('Y-m-d', strtotime($tgl_sk)),
					'stri_type' => $stri_type,
					'createdby' => $this->session->userdata('admin_id'),
				);
				$this->main_mod->insert('log_stri', $rowInsert);


				//UPDATE JENIS ANGGOTA
				$kta_data = $this->members_model->get_member_by_kta($kta);
				if ($kta_data->jenis_anggota == '01' || $kta_data->jenis_anggota == '1') {
					$where = array(
						"person_id" => $kta_data->user_id,
					);
					//$rowInsert['updated_by'] = $this->session->userdata('admin_id');
					//$rowInsert['updated_at'] = date('Y-m-d H:i:s');
					$rowInsertx['jenis_anggota'] = '02';

					$update = $this->main_mod->update('members', $where, $rowInsertx);
				}

				$where = array(
					'seq' => 1
				);
				$row = array(
					'id_stri' => $num
				);
				$update = $this->main_mod->update('id_management', $where, $row);

				echo json_encode(array("code" => "success", "msg" => "Certificate valid"));;
			} else echo json_encode(array("code" => "failed", "msg" => "Member certificate is not valid"));
		} catch (Exception $e) {
			//print_r($e);
			echo json_encode(array("code" => "failed", "msg" => "Code exception: " . $e));
		}
	} //Endof setstri

	//---------------------------------------------------------------------------------------------------------------- Tambahan by Ipur 08-06-2025

	public function update_acpe()
	{

		$id       = $_POST['id_id'];
		$kta      = $_POST['id_kta'];
		$nama     = $_POST['id_nama'];
		$noacpe   = $_POST['id_noacpe'];
		$doi      = $_POST['id_doi'];
		$newpeno  = $_POST['id_newpeno'];
		$bk_acpe  = $_POST['id_bk'];

		$data_update_acpe = [
			'no_acpe'   => $noacpe,
			'doi'       => $doi,
			'nama'      => $nama,
			'kta'       => $kta,
			'new_pe_no' => $newpeno,
			'bk_acpe'   => $bk_acpe

		];

		$this->load->model('simpan_model');

		$this->simpan_model->update_data_acpe($id, $data_update_acpe);
		$data['selesai'] = 'y';
		$data['list_acpe'] = $this->simpan_model->ambil_data_acpe();
		$this->load->view('admin/list_acpe', $data);
	}

	//-------------------------------------------------------------------------------------------------	
	function setstriedit_2()
	{
		// && $this->session->userdata('type')!="2" && $this->session->userdata('type')!="12"
		if ($this->session->userdata('type') != "0" && $this->session->userdata('type') != "12") {
			redirect('admin/dashboard');
			exit;
		}

		//              $id_stri = $_POST['id_id'] ; $nama_stri = $_POST['nama_id'] ; $stri_tipe = $_POST['stri_tipe'] ; $warga = $_POST['warga'] ;

		$id_id = $this->input->post('id') <> null ? $this->input->post('id') : "";
		$nama_stri = $this->input->post('add_name') <> null ? $this->input->post('add_name') : "";
		$no_kta = $this->input->post('no_kta') <> null ? $this->input->post('no_kta') : "";
		//		$warga = $this->input->post('warga')<>null?$this->input->post('warga'):"";

		$this->load->model('members_model');

		$rowInsert = [
			'add_name'     => $nama_stri,
			'status'       => 1,
			'modifiedby'   => $this->session->userdata('admin_id'),
			'modifieddate' => date('Y-m-d H:i:s')

		];

		/*						
						$rowInsert['add_name'] = $nama_stri; // Tambahan by Ipur Tgl 05-6-2025	
						$rowInsert['modifiedby'] = $this->session->userdata('admin_id');
						$rowInsert['modifieddate'] = date('Y-m-d H:i:s');
*/
		$update = $this->members_model->update_member_sert($id_id, $rowInsert);
		//	$this->members_model->update_member_sert($id_stri, $rowInsert);
		//	$update = $this->main_mod->update('members_certificate',$where,$rowInsert);

	}
	//}
	//------------------------------------------------------------------------------------------------------------------------------------

	function setstriedit()
	{
		// && $this->session->userdata('type')!="2" && $this->session->userdata('type')!="12"
		if ($this->session->userdata('type') != "0" && $this->session->userdata('type') != "12") {
			redirect('admin/dashboard');
			exit;
		}

		//$idmember = $this->input->post('id')<>null?$this->input->post('id'):"";

		$stri_cabang = $this->input->post('stri_cabang') <> null ? $this->input->post('stri_cabang') : "";
		$stri_kp = $this->input->post('stri_kp') <> null ? $this->input->post('stri_kp') : "";
		$stri_bk = $this->input->post('stri_bk') <> null ? $this->input->post('stri_bk') : "";
		$stri_subbk = $this->input->post('stri_subbk') <> null ? $this->input->post('stri_subbk') : "";
		$stri_type = $this->input->post('stri_type') <> null ? $this->input->post('stri_type') : "";
		$stri_tipe = $this->input->post('stri_tipe') <> null ? $this->input->post('stri_tipe') : "";
		$warga = $this->input->post('warga') <> null ? $this->input->post('warga') : "";
		$nama_stri = $this->input->post('nama_stri') <> null ? $this->input->post('nama_stri') : "";

		$instansi = $this->input->post('instansi') <> null ? $this->input->post('instansi') : "";
		$tgl_sk = $this->input->post('tgl_sk') <> null ? $this->input->post('tgl_sk') : "";
		$from = $this->input->post('from') <> null ? $this->input->post('from') : "";
		$until = $this->input->post('until') <> null ? $this->input->post('until') : "";
		$id_stri = $this->input->post('id') <> null ? $this->input->post('id') : "";
		$kta = $this->input->post('no_kta') <> null ? $this->input->post('no_kta') : "";
		$skip_id = $this->input->post('skip_id') <> null ? $this->input->post('skip_id') : "";

		$num = $this->input->post('num') <> null ? $this->input->post('num') : "";

		//if($idmember==''){
		//redirect('admin/members');
		//exit;
		//}
		$this->load->model('main_mod');
		//if($idmember!=''){
		try {
			$idmember = $this->members_model->get_member_by_kta($kta);
			if (isset($idmember->user_id)) {
				$idmember = $idmember->user_id;

				//$kta = $this->members_model->get_kta_by_personid($idmember);
				//$kta = $kta->no_kta;

				//$th = date('y');

				$rowInsert = array();

				$th = substr(date('Y-m-d', strtotime($tgl_sk)), 0, 4);
				$th = substr($th, -2);

				$rowInsert = array(
					/*'person_id' => $idmember,

						'no_kta' => $kta,
						'certificate_type' => $stri_type,

						'skip_id' => str_pad($skip_id, 6, '0', STR_PAD_LEFT),
						'skip_code_wilayah' => str_pad($stri_cabang, 4, '0', STR_PAD_LEFT),
						'skip_code_bk_hkk' => str_pad($stri_bk, 2, '0', STR_PAD_LEFT),
						'skip_pm' => str_pad($stri_kp, 2, '0', STR_PAD_LEFT),
						//'skip_sub_code_bk_hkk' => $stri_subbk,
						'skip_sk' => date('Y-m-d' , strtotime($tgl_sk)),
						'skip_from_date' => date('Y-m-d' , strtotime($from)),
						'skip_thru_date' => date('Y-m-d' , strtotime("+5 years",strtotime($from))),
						*/

					//'stri_id' => str_pad($num, 7, '0', STR_PAD_LEFT),
					//'stri_code_wilayah' => str_pad($stri_cabang, 4, '0', STR_PAD_LEFT),
					//'stri_code_bk_hkk' => str_pad($stri_bk, 2, '0', STR_PAD_LEFT),
					//'stri_pm' => $stri_kp,
					'stri_tipe' => $stri_tipe,
					'warga' => $warga,
					'add_name' => $nama_stri,
					//'th'	=> $th,
					//'stri_sub_code_bk_hkk' => $stri_subbk,
					//'stri_sk' => date('Y-m-d' , strtotime($tgl_sk)),
					//'stri_from_date' => date('Y-m-d' , strtotime($from)),
					//'stri_thru_date' => date('Y-m-d' , strtotime("+5 years",strtotime($from))),
					'status' => 1,

					'createdby_n' => $this->session->userdata('admin_id'),
				);

				/*if($skip_id == "" || $skip_id == "0")
					{
						$rowInsert['skip_id'] = null;
						$rowInsert['skip_code_wilayah'] = null;
						$rowInsert['skip_code_bk_hkk'] = null;
						$rowInsert['skip_pm'] = null;
						$rowInsert['skip_sk'] = null;
						$rowInsert['skip_from_date'] = null;
						$rowInsert['skip_thru_date'] = null;
					}*/


				$check = $this->main_mod->msrwhere('members_certificate', array('id' => $id_stri, 'status' => 1), 'id', 'desc')->result();

				if (isset($check[0])) {
					unset($check[0]->id);
					//unset($check[0]->createddate);
					//unset($check[0]->modifiedby);
					//unset($check[0]->modifieddate);
					$check[0]->fk_id = $id_stri;
					$check[0]->log_type = 'edit';
					$this->main_mod->insert('log_members_certificate', $check[0]);


					$where = array(
						"id" => $id_stri,
					);
					unset($rowInsert['createdby_n']);
					$rowInsert['add_name'] = $nama_stri; // Tambahan by Ipur Tgl 05-6-2025	
					$rowInsert['modifiedby'] = $this->session->userdata('admin_id');
					$rowInsert['modifieddate'] = date('Y-m-d H:i:s');

					$update = $this->main_mod->update('members_certificate', $where, $rowInsert);
				}

				/* FOR ADD
					else{
						$id_stri = $this->members_model->insert_stri('members_certificate',$rowInsert);

						$rowInsert['fk_id'] = $id_stri;
						$rowInsert['log_type'] = 'add';
						$this->main_mod->insert('log_members_certificate',$rowInsert);

					}*/

				echo "valid";
			} else echo "not valid";
		} catch (Exception $e) {
			//print_r($e);
			echo "not valid";
		}
		//}
		//else
		//	echo "not valid";

	}

	function getstri_2()
	{
		$this->load->model('members_model');
		$id = $_POST['id'];
		$sql_data = $this->members_model->get_stri_by_id_2($id);
		header('Content-Type: application/json');
		echo json_encode($sql_data);
	}

	function getstri()
	{
		$id = $_POST['id'];
		$sql_data = $this->members_model->get_stri_by_id($id);
		$callback = array(
			'data' => $sql_data
		);
		header('Content-Type: application/json');
		echo json_encode($sql_data);
	}

	function deletestri()
	{
		// && $this->session->userdata('type')!="2" && $this->session->userdata('type')!="12"
		if ($this->session->userdata('type') != "0") {
			redirect('admin/dashboard');
			exit;
		}

		$idmember = $this->input->post('id') <> null ? $this->input->post('id') : "";

		$num = $this->input->post('num') <> null ? $this->input->post('num') : "";

		if ($idmember == '') {
			redirect('admin/members');
			exit;
		}
		$this->load->model('main_mod');

		try {
			$check = $this->main_mod->msrwhere('members_certificate', array('id' => $idmember, 'status' => 1), 'id', 'desc')->result();

			if (isset($check[0])) {
				if (isset($check[0])) {
					unset($check[0]->id);
					unset($check[0]->createddate);
					unset($check[0]->modifiedby);
					unset($check[0]->modifieddate);
					$check[0]->fk_id = $idmember;
					$check[0]->log_type = 'delete';
					$this->main_mod->insert('log_members_certificate', $check[0]);


					$where = array(
						"id" => $idmember,
					);

					$rowInsert['status'] = 0;
					$rowInsert['modifiedby'] = $this->session->userdata('admin_id');
					$rowInsert['modifieddate'] = date('Y-m-d H:i:s');

					$update = $this->main_mod->update('members_certificate', $where, $rowInsert);
				}

				echo "valid";
			} else echo "not valid";
		} catch (Exception $e) {
			echo "not valid";
		}
	}

	/**
	 * ER: Sepertinya tidak digunakan di view manapun. Mungkin untuk `debuging`
	 */
	function get_last_id_stri()
	{
		$sql_data = $this->main_mod->msrquery('select id_stri from id_management ')->row();
		header('Content-Type: application/json');
		echo json_encode($sql_data->id_stri);
	}

	function __setip()
	{
		$idmember = $this->input->post('id') <> null ? $this->input->post('id') : "";
		$ip_cabang = $this->input->post('ip_cabang') <> null ? $this->input->post('ip_cabang') : "";
		$ip_kp = $this->input->post('ip_kp') <> null ? $this->input->post('ip_kp') : "";
		$ip_bk = $this->input->post('ip_bk') <> null ? $this->input->post('ip_bk') : "";
		$ip_subbk = $this->input->post('ip_subbk') <> null ? $this->input->post('ip_subbk') : "";
		$ip_type = $this->input->post('ip_type') <> null ? $this->input->post('ip_type') : "";
		$tgl_sk = $this->input->post('tgl_sk') <> null ? $this->input->post('tgl_sk') : "";
		$from = $this->input->post('from') <> null ? $this->input->post('from') : "";
		$until = $this->input->post('until') <> null ? $this->input->post('until') : "";
		if ($idmember == '') {
			redirect('admin/members');
			exit;
		}
		$this->load->model('main_mod');
		if ($idmember != '') {
			try {
				$idx = $this->members_model->last_skip($ip_type, $ip_bk, $ip_subbk);
				$num = ltrim($idx->jml, '0');
				$num =  str_pad(($num + 1), 6, '0', STR_PAD_LEFT);

				$kta = $this->members_model->get_kta_by_personid($idmember);
				$kta = $kta->no_kta;

				$rowInsert = array(
					'person_id' => $idmember,
					'no_kta' => $kta,
					'certificate_type' => $ip_type,

					'skip_id' => $num,
					'skip_code_wilayah' => $ip_cabang,
					'skip_code_bk_hkk' => $ip_bk,
					'skip_pm' => $ip_kp,
					'skip_sub_code_bk_hkk' => $ip_subbk,
					'skip_sk' => date('Y-m-d', strtotime($tgl_sk)),
					'skip_from_date' => date('Y-m-d', strtotime($from)),
					'skip_thru_date' => date('Y-m-d', strtotime($until)),

					'stri_id' => $num,
					'stri_code_wilayah' => $ip_cabang,
					'stri_code_bk_hkk' => $ip_bk,
					'stri_pm' => $ip_kp,
					'stri_sub_code_bk_hkk' => $ip_subbk,
					'stri_sk' => date('Y-m-d', strtotime($tgl_sk)),
					'stri_from_date' => date('Y-m-d', strtotime($from)),
					'stri_thru_date' => date('Y-m-d', strtotime($until)),
				);

				$check = $this->main_mod->msrwhere('members_certificate', array('person_id' => $idmember), 'id', 'desc')->result();
				$t = 'insert_skip';
				if (isset($check[0])) {
					$where = array(
						"person_id" => $idmember
					);
					$row = array(
						'status' => 0,
						//'updated_at' => date('Y-m-d H:i:s'),
						//'updated_by' => $this->session->userdata('user_id'),
					);
					$update = $this->main_mod->update('members_certificate', $where, $row);


					$this->members_model->$t('members_certificate', $rowInsert);
				} else {
					$this->members_model->$t('members_certificate', $rowInsert);
				}

				echo "valid";
			} catch (Exception $e) {
				//print_r($e);
				echo "not valid";
			}
		} else
			echo "not valid";
	}

	function setdob()
	{
		$akses = array("0", "2");
		if (!in_array($this->session->userdata('type'), $akses)) {
			redirect('admin/dashboard');
			exit;
		}

		$idmember = $this->input->post('id') <> null ? $this->input->post('id') : "";
		$tgl_sk = $this->input->post('tgl_dob') <> null ? $this->input->post('tgl_dob') : "";
		if ($idmember == '') {
			redirect('admin/members');
			exit;
		}
		$this->load->model('main_mod');
		if ($idmember != '') {
			try {

				$check = $this->main_mod->msrwhere('user_profiles', array('user_id' => $idmember), 'id', 'desc')->result();
				if (isset($check[0])) {
					$where = array(
						"user_id" => $idmember
					);
					$row = array(
						'dob' => date('Y-m-d', strtotime($tgl_sk)),
						//'updated_at' => date('Y-m-d H:i:s'),
						//'updated_by' => $this->session->userdata('user_id'),
					);
					$update = $this->main_mod->update('user_profiles', $where, $row);

					$rowInsert = array(
						'user_id' => $idmember,
						'old_status' => $check[0]->dob,
						'new_status' => date('Y-m-d', strtotime($tgl_sk)),
						'notes' => 'ganti dob',
						'remarks' => '',
						'createdby' => $this->session->userdata('admin_id'),
					);
					$this->main_mod->insert('log_status_keanggotaan', $rowInsert);
				}

				echo "valid";
			} catch (Exception $e) {
				//print_r($e);
				echo "not valid";
			}
		} else
			echo "not valid";
	}

	//Wrapper function
	function member_setperiod()
	{
		log_message('debug', '[SIMPONI] ' . __CLASS__ . '@' . __FUNCTION__ . ' body: ' . @file_get_contents('php://input'));
		$this->ajax_setperiod();
	}


	//Wrapper function
	function stri_setperiod()
	{
		log_message('debug', '[SIMPONI] ' . __CLASS__ . '@' . __FUNCTION__ . ' body: ' . @file_get_contents('php://input'));
		$this->ajax_setperiod();
	}

	//Wrapper function
	function her_setperiod()
	{
		log_message('debug', '[SIMPONI] ' . __CLASS__ . '@' . __FUNCTION__ . ' body: ' . @file_get_contents('php://input'));
		$this->ajax_setperiod();
	}

	protected function _rest_response($status = TRUE, $code = 'SIM20000', $message = 'success', $http_code = 200, $detail = null, $data = null)
	{
		$returnval = [
			'status' => $status,
			'code' => $code,
			'message' => $message
		];
		if (! empty($data)) {
			$returnval['data'] = $data;
		}
		if (! empty($detail)) {
			$returnval['detail'] = $detail;
		}
		// Consider to use ->set_output(json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
		$this->output
			->set_content_type('application/json')
			->set_status_header($http_code)
			->set_output(json_encode($returnval))
			->_display();
		exit();
	}

	public function date_check($str)
	{
		if (!DateTime::createFromFormat('d-m-Y', $str)) {
			$this->form_validation->set_message('date_check', 'The {field} has not a valid date format (DD-MM-YYYY)');
			return FALSE;
		} else {
			return TRUE;
		}
	}

	/**
	 * Called by Ajax using POST method
	 *
	 * #1. Dipanggil saat Admin melakukan klik "Set Period" di halaman HER Member.
	 * #2. Dipanggil juga saat Admin melakukan klik period di row anggota yg bersangkutan dari table di halaman "All Members",
	 *     di case #2 ini  tidak ada paramter `id_pay`
	 * #3. Dipanggil juga di STRI Members tp JS untuk menampilkan modal window (load_quick_period()) tidak ditampilkan
	 * insert to log_her_kta log_status_keanggotaan
	 * update to user_transfer (vnv_status=1), members (from_date & thru_date)
	 */
	function ajax_setperiod()
	{

		log_message('debug', '[SIMPONI] ' . __CLASS__ . '@' . __FUNCTION__);
		try {
			$actor_userid = $this->session->userdata('admin_id');

			$akses = array("0", "2", "14");
			if (!in_array($this->session->userdata('type'), $akses, true)) {
				$this->_rest_response(FALSE, 'SIM403001', 'No access to this resource/operation.', REST_Controller::HTTP_FORBIDDEN);
			}

			$data = null;

			// Accept both JSON or urlencoded format
			if (preg_match('/application\/json/', $this->input->get_request_header('Content-Type'))) {
				$stream_clean = $this->input->raw_input_stream;
				$data = json_decode($stream_clean, TRUE);
				$this->form_validation->set_data($data);
			} else if (preg_match('/application\/x-www-form-urlencoded/', $this->input->get_request_header('Content-Type'))) {
				$data = $this->input->post();
			} else {
				$this->_rest_response(FALSE, 'SIM401202', 'Content type is not recognized.', REST_Controller::HTTP_NOT_ACCEPTABLE);
			}

			log_message('debug', '[SIMPONI] ' . __CLASS__ . '@' . __FUNCTION__ . ' - data: ' . print_r($data, TRUE));

			$this->form_validation->set_rules('id', 'id', 'numeric|required');
			$this->form_validation->set_rules('tgl_period', 'tgl_period', 'required|callback_date_check');
			$this->form_validation->set_rules('tgl_period2', 'tgl_period2', 'required|callback_date_check');

			// id_pay (id dari user_transfer) hanya dikirim untuk case #1 HER  dan case #3 STRI jadi NOT required
			$this->form_validation->set_rules('id_pay', 'id_pay', 'numeric');

			if (!$this->form_validation->run()) {
				$this->_rest_response(FALSE, 'SIM403101', validation_errors(), REST_Controller::HTTP_BAD_REQUEST);
			}

			$thru_date = DateTime::createFromFormat('d-m-Y', $data['tgl_period2']);
			$from_date = DateTime::createFromFormat('d-m-Y', $data['tgl_period']);
			$date_now = new DateTime();

			if ($from_date > $thru_date) {
				$this->_rest_response(FALSE, 'SIM403101', 'From_date should be older than Thru_date', REST_Controller::HTTP_BAD_REQUEST);
			}

			log_message('debug', '[SIMPONI] ' . __CLASS__ . '@' . __FUNCTION__ . ' - ' . $this->input->raw_input_stream);

			$this->load->model('main_mod');

			// Ambil yang data user_transfer yg paling latest atau sesuai id ($id_pay)
			$this->db->where('user_id', $data['id']);
			if (! empty($data['id_pay'])) {
				$this->db->where('id', $data['id_pay']);
			}
			$ut_row = $this->db
				->order_by('id', 'desc')
				->limit(1)
				->get('user_transfer')->row();

			// Ambil data period saat ini
			$members_row = $this->main_mod->msrwhere('members', array('person_id' => $data['id']), 'id', 'desc')->result()[0];

			$additional_msg = null;

			if ($ut_row) {

				// Untuk kasus HER/Perpanjangan (pay_type=2)
				if ($ut_row->pay_type == 2) {

					// 20240704 - Bisa jadi perubahan  period dari page All Members (bukan HER) tapi
					// sebenernya adalah perpanjangan untuk mengreksi (tanggal yang salah)
					if (empty(@$data['id_pay'])) {
						$data['id_pay'] = $ut_row->id;
					}

					// Update vnv (verification status)
					$this->main_mod->update(
						'user_transfer',
						array('user_id' => $data['id'], 'pay_type' => '2'), // where statement
						array(
							'vnv_status' => 1,
							'modifieddate' => date('Y-m-d H:i:s'),
							'modifiedby' => $actor_userid
						)
						//,'is_upload_mandiri'=>1
					);

					// Log perubahan ini
					if (isset($members_row->no_kta)) {
						$rowInsert = array(
							'user_id'       => $data['id'],
							'id_pay'        => $data['id_pay'],
							'no_kta'        => $members_row->no_kta,
							'old_from_date' => $members_row->from_date,
							'old_thru_date' => $members_row->thru_date,
							'from_date'     => date('Y-m-d', strtotime($data['tgl_period'])),
							'thru_date'     => date('Y-m-d', strtotime($data['tgl_period2'])),
							'createdby'     => $actor_userid,
						);
						$this->main_mod->insert('log_her_kta', $rowInsert);
					}
				}

				// anggota sudah bayar
				if ($ut_row->status == '1') { // ER: TODO: Apakah perlu ditambahkan: `&& $ut_row->pay_type != 2` ? - 31 May 2024
					$where = array(
						"person_id" => $data['id']
					);
					$row = array(
						'from_date' => date('Y-m-d', strtotime($data['tgl_period'])),
						'thru_date' => date('Y-m-d', strtotime($data['tgl_period2'])),
						'updated_at' => date('Y-m-d H:i:s'),
						'updated_by' => $actor_userid,
					);
					$update = $this->main_mod->update('members', $where, $row);
					log_message('debug', '[SIMPONI] ' . __CLASS__ . '@' . __FUNCTION__ . ' - Period in the table members updated');
				}
				// Anggota belum bayar throw error? No
				else {

					if ($date_now > $thru_date) {
						$additional_msg = "No related payment, modify thru_date to an old date";
						$where = array(
							"person_id" => $data['id']
						);
						$row = array(
							'from_date' => date('Y-m-d', strtotime($data['tgl_period'])),
							'thru_date' => date('Y-m-d', strtotime($data['tgl_period2'])),
							'updated_at' => date('Y-m-d H:i:s'),
							'updated_by' => $actor_userid,
						);
						$update = $this->main_mod->update('members', $where, $row);
						log_message('debug', '[SIMPONI] ' . __CLASS__ . '@' . __FUNCTION__ . ' - Period in the table members updated');
					} else {
						$additional_msg = 'No payment yet, skip updating member period (from_date  and thru_date)';
						log_message('debug', '[SIMPONI] ' . __CLASS__ . '@' . __FUNCTION__ . ' - SKIP updating period in the table members, because payment status is not 1');
					}
				}

				$rowInsert = array(
					'user_id' => $data['id'],
					'old_status' => $members_row->from_date . ' - ' . $members_row->thru_date,
					'new_status' => date('Y-m-d', strtotime($data['tgl_period'])) . ' - ' . date('Y-m-d', strtotime($data['tgl_period2'])),
					'notes' => 'ganti period' . ", {$additional_msg}",
					'remarks' => '',
					'createdby' => $actor_userid
				);
				$this->main_mod->insert('log_status_keanggotaan', $rowInsert);
			}

			// No data in user_transfer
			else {

				// Note: Kalau thru_date diupdate ke tanggal sebelumnya maka proses aja - 4Jul2024
				// Kasus ada member yang thru_date sampai tahun 3000, ini harus bisa diupade
				// walaupun tanpa ada data pembayaran di user_transfer

				if ($date_now > $thru_date) {
					$where = array(
						"person_id" => $data['id']
					);
					$row = array(
						'from_date' => date('Y-m-d', strtotime($data['tgl_period'])),
						'thru_date' => date('Y-m-d', strtotime($data['tgl_period2'])),
						'updated_at' => date('Y-m-d H:i:s'),
						'updated_by' => $actor_userid,
					);
					$update = $this->main_mod->update('members', $where, $row);
					log_message('debug', '[SIMPONI] ' . __CLASS__ . '@' . __FUNCTION__ . ' - Period in the table members updated');

					$rowInsert = array(
						'user_id' => $data['id'],
						'old_status' => $members_row->from_date . ' - ' . $members_row->thru_date,
						'new_status' => date('Y-m-d', strtotime($data['tgl_period'])) . ' - ' . date('Y-m-d', strtotime($data['tgl_period2'])),
						'notes' => 'ganti period, no related payment data',
						'remarks' => '',
						'createdby' => $actor_userid
					);
					$this->main_mod->insert('log_status_keanggotaan', $rowInsert);
				}
				// Memperpanjang tapi no payment -> throw error
				else {
					//Member does not have any user_transfer entry
					$this->_rest_response(FALSE, 'SIM400101', "No related payment (user_transfer) found", REST_Controller::HTTP_BAD_REQUEST);
				}
			}

			$this->_rest_response(TRUE, 'SIM200000', 'Success ' . $additional_msg);
		} catch (\Throwable $e) {
			log_message('debug', '[SIMPONI] ' . __CLASS__ . '@' . __FUNCTION__ . ' - Exception ' . $e->getMessage());
			$this->_rest_response(FALSE, 'SIM503101', $e->getMessage(), REST_Controller::HTTP_SERVICE_UNAVAILABLE);
		}
	}

	/**
	 * TOBE DELETED - Sudah diperbarui dengan new function diatas - 30 May 2024
	 * FIXME: Bug: Untuk kasus #2, tabel `members` tidak akan terupdate
	 *
	 * #1. Dipanggil saat Admin melakukan klik "Set Period" di halaman HER Member.
	 * #2. Dipanggil juga saat Admin melakukan klik period di row anggota yg bersangkutan dari table di halaman "All Members"
	 * insert to log_her_kta log_status_keanggotaan
	 * update to user_transfer (vnv_status=1), members (from_date & thru_date)
	 */
	function setperiod_orig()
	{

		$akses = array("0", "2", "14");
		if (!in_array($this->session->userdata('type'), $akses)) {
			//$this->session->flashdata('msg', $this->access_deny_msg());
			redirect('admin/dashboard');
			exit;
		}

		$idmember = $this->input->post('id') <> null ? $this->input->post('id') : "";
		$id_pay = $this->input->post('id_pay') <> null ? $this->input->post('id_pay') : "0";
		$tgl_period = $this->input->post('tgl_period') <> null ? $this->input->post('tgl_period') : "";
		$tgl_period2 = $this->input->post('tgl_period2') <> null ? $this->input->post('tgl_period2') : "";
		if ($idmember == '') {
			redirect('admin/members');
			exit;
		}

		log_message('debug', '[SIMPONI] ' . __FUNCTION__ . ' - awal_periode: ' . $tgl_period . ', akhir_periode: ' . $tgl_period2);
		$this->load->model('main_mod');

		try {

			$check = $this->main_mod->msrwhere('user_profiles', array('user_id' => $idmember), 'id', 'desc')->result();
			if (isset($check[0])) {
				// Untuk kasus HER/Perpanjangan (pay_type=2)
				$check_transfer = $this->main_mod->msrwhere('user_transfer', array('user_id' => $idmember, 'pay_type' => '2'), 'id', 'desc')->result();
				$is_paid = 0;
				if ($check_transfer[0] != '') {
					if ($check_transfer[0]->status == '1') $is_paid = 1;

					// Update vnv (verification status)
					$this->main_mod->update(
						'user_transfer',
						array('user_id' => $idmember, 'pay_type' => '2'), // where statement
						array(
							'vnv_status' => 1,
							'modifieddate' => date('Y-m-d H:i:s'),
							'modifiedby' => $this->session->userdata('admin_id')
						)
						//,'is_upload_mandiri'=>1
					);

					//SEND Mailer
					//$this->send_mail_va($check_transfer[0]->user_id, $check_transfer[0]->pay_type, $check_transfer[0]->sukarelatotal);
				}

				// Log perubahan ini
				$checkx = $this->main_mod->msrwhere('members', array('person_id' => $idmember), 'id', 'desc')->result();
				if (isset($checkx[0]->no_kta)) {
					$rowInsert = array(
						'user_id' => $idmember,
						'id_pay' => $id_pay,
						'no_kta' => $checkx[0]->no_kta,
						'old_from_date' => $checkx[0]->from_date,
						'old_thru_date' => $checkx[0]->thru_date,
						'from_date' => date('Y-m-d', strtotime($tgl_period)),
						'thru_date' => date('Y-m-d', strtotime($tgl_period2)),
						'createdby' => $this->session->userdata('admin_id'),
					);
					$this->main_mod->insert('log_her_kta', $rowInsert);
				}


				//REMOVE BECAUSE VA
				if ($is_paid) {

					// ER: FIXME - Bug: Untuk kasus #2, tabel `members` tidak akan terupdate
					$where = array(
						"person_id" => $idmember
					);
					$row = array(
						'from_date' => date('Y-m-d', strtotime($tgl_period)),
						'thru_date' => date('Y-m-d', strtotime($tgl_period2)),
						'updated_at' => date('Y-m-d H:i:s'),
						'updated_by' => $this->session->userdata('admin_id'),
					);
					$update = $this->main_mod->update('members', $where, $row);
					log_message('debug', '[SIMPONI] ' . __FUNCTION__ . ' - Update period in the table members');
				} else {
					log_message('debug', '[SIMPONI] ' . __FUNCTION__ . ' - SKIP updating deriod in the table members, because payment status is not 1');
				}

				$rowInsert = array(
					'user_id' => $idmember,
					'old_status' => $checkx[0]->from_date . ' - ' . $checkx[0]->thru_date,
					'new_status' => date('Y-m-d', strtotime($tgl_period)) . ' - ' . date('Y-m-d', strtotime($tgl_period2)),
					'notes' => 'ganti period',
					'remarks' => '',
					'createdby' => $this->session->userdata('admin_id'),
				);
				$this->main_mod->insert('log_status_keanggotaan', $rowInsert);
			}

			echo "valid";
		} catch (Exception $e) {
			//print_r($e);
			echo "not valid";
		}
	}

	function setcab()
	{
		$akses = array("0", "2");
		if (!in_array($this->session->userdata('type'), $akses)) {
			redirect('admin/dashboard');
			exit;
		}

		$idmember = $this->input->post('id') <> null ? $this->input->post('id') : "";
		$cab = $this->input->post('cab') <> null ? $this->input->post('cab') : "";
		if ($idmember == '') {
			redirect('admin/members');
			exit;
		}
		$this->load->model('main_mod');
		if ($idmember != '') {
			try {

				$check = $this->main_mod->msrwhere('members', array('person_id' => $idmember), 'id', 'desc')->result();
				if (isset($check[0])) {
					$where = array(
						"person_id" => $idmember
					);
					$row = array(
						'code_wilayah' => str_pad($cab, 4, '0', STR_PAD_LEFT),
						'wil_id'       => substr($cab, 1, 2), 					// Tambahan by IP
						'updated_at'   => date('Y-m-d H:i:s'),
						'updated_by'   => $this->session->userdata('admin_id')
					);
					$update = $this->main_mod->update('members', $where, $row);

					//UPDATE NOMOR VA
					$where = array(
						"user_id" => $idmember
					);

					$row = array(
						'va' => generate_va($cab, $check[0]->code_bk_hkk, $check[0]->no_kta),
						'modifieddate' => date('Y-m-d H:i:s'),
						'modifiedby'   => $this->session->userdata('admin_id'),
					);
					$update = $this->main_mod->update('user_profiles', $where, $row);

					$rowInsert = array(
						'user_id'    => $idmember,
						'old_status' => $check[0]->code_wilayah,
						'new_status' => str_pad($cab, 4, '0', STR_PAD_LEFT),
						'notes'      => 'pindah cabang',
						'remarks'    => 'dari ' . $check[0]->code_wilayah . ' ke ' . str_pad($cab, 4, '0', STR_PAD_LEFT),
						'createdby'  => $this->session->userdata('admin_id'),
					);
					$this->main_mod->insert('log_status_keanggotaan', $rowInsert);
				}

				echo "valid";
			} catch (Exception $e) {
				//print_r($e);
				echo "not valid";
			}
		} else
			echo "not valid";
	}

	function setbk()
	{
		$this->ajax_update_bk();
	}

	/**
	 * Dipanggil dari halaman All Members
	 * - Akan gagal kalau ada FAIP yg masih aktif dan BK yang lama, FAIP harus diubah dahulu ke BK baru
	 */
	function ajax_update_bk()
	{

		log_message(
			'debug',
			"[SIMPONI] " . __CLASS__ . '@' . __FUNCTION__ . " Http metod: " . $this->input->method() . ", accessedBy " . $this->session->userdata('user_id' .
				', data: ' . $this->input->raw_input_stream)
		);

		$akses = array("0", "2");
		if (!in_array($this->session->userdata('type'), $akses)) {
			$this->_rest_response(FALSE, 'SIM401201',  'No access to this resource/operation.', REST_Controller::HTTP_FORBIDDEN);
		}

		$status = array();
		if (is_debug()) {
			$status[] = $this->debug_msg;
		}

		$this->load->model('main_mod');

		try {

			$user_id = $this->input->post('id') ?: "";
			$bk      = $this->input->post('bk') ?: "";

			// Validation
			$data = array(
				'user_id' => $user_id,
				'bk_id' => $bk
			);

			$this->form_validation->set_data($data);
			$this->form_validation->set_rules('user_id', 'User/Member id', 'required|integer');
			$this->form_validation->set_rules('bk_id', 'BK id', 'required|integer');

			if ($this->form_validation->run() == FALSE) {
				$this->_rest_response(FALSE, 'SIM401204', strip_tags(validation_errors()), REST_Controller::HTTP_BAD_REQUEST);
			}
			if (is_debug()) {
				$status[] = "SUCCESS: Params validation";
			}

			// Check member is exist - User bisa jadi belum member (tidak ada data di tabel members jadi members_id bisa NULL)
			$result = $this->main_mod->msrwhere('v_account', array('profile_id' => $user_id, 'members_id IS NOT NULL' => null), 'id', 'desc')->result();
			if (isset($result[0]) == FALSE) {
				$this->_rest_response(FALSE, '401020', "User not found or might not a member yet, user_id: {$user_id}", REST_Controller::HTTP_NOT_FOUND);
			}
			if (is_debug()) {
				$status[] = "SUCCESS: Get existing user based on user_id: {$user_id}";
			}

			$user = $result[0];

			//Check if BK already set/Not new member (Change BK)
			if (empty($user->code_bk_hkk) == FALSE || ltrim($user->code_bk_hkk, '0') !== '') {

				//Check if BK is the same as previous one
				if (ltrim($user->code_bk_hkk, '0') == $bk) {
					$this->_rest_response(
						FALSE,
						'401021',
						"Current user's BK is the same as target BK, bk_id: {$user->code_bk_hkk}",
						REST_Controller::HTTP_BAD_REQUEST
					);
				}
				if (is_debug()) {
					$status[] = "SUCCESS: Checked, target BK is different with current BK, bk_id: {$user->code_bk_hkk}";
				}

				//Check if user still have active FAIP (user_faip) with previous BK
				$FAIP_STATUS_TO_SCORE_MUK = 6;
				$result = $this->db
					->where('user_id', $user_id)
					->where('bidang', $user->code_bk_hkk)
					->where('status', 1)
					->where('status_faip >=', $FAIP_STATUS_TO_SCORE_MUK)
					->get('user_faip');

				if ($result->num_rows() > 0) {
					if (is_debug()) {
						$status[] = "FAILED: Still has active FAIP in the previous BK";
					}
					$this->_rest_response(
						FALSE,
						'401021',
						"FAILED: User still have active FAIP with BK (bidang): {$result->row()->bidang}, " .
							"faip_id: {$result->row()->id}, faip_status: {$result->row()->status_faip}",
						REST_Controller::HTTP_NOT_ACCEPTABLE
					);
				}
				if (is_debug()) {
					$status[] = "SUCCESS: Checked, no active FAIP with the existing BK";
				}



				//Check if user still have pending payment (user_transfer)
				$result = $this->db
					->where('user_id', $user_id)
					->where('status', 0)
					->where('order_id IS NOT NULL')
					->get('user_transfer');

				if ($result->num_rows() > 0) {
					if (is_debug()) {
						$status[] = "WARNING: Still has pending payment/VA (in user_transfer)";
					}
					// 20240702 - VA tetap dibiarkan akan dilakukan proses manual (info ke bagian Finance)
					//            walaupun ada VA yang masih aktif
					// $this->_rest_response(FALSE, '401022',
					// 	"User still have pending payment (VA), user_transfer id: {$result->row()->id}",
					// 	REST_Controller::HTTP_NOT_ACCEPTABLE
					// );
				}
				if (is_debug()) {
					$status[] = "SUCCESS: Checked, no pending payment/VA (in user_transfer)";
				}
			}

			// Finish checking -> Execute perubahan di database

			$data = array(
				'code_bk_hkk' => $bk,
				'updated_at'  => date('Y-m-d H:i:s'),
				'updated_by'  => $this->session->userdata('admin_id'),
			);
			$update = $this->main_mod->update('members', array("person_id" => $user_id), $data);

			if (is_debug()) {
				$status[] = "SUCCESS: Update table: members, person_id: {$user_id} ";
			}

			//Updata VA number

			//-------------------------------------------------------------------------------- Script Aslinya ------------------------------------
			/*				$up_data = array(
					'va' => generate_va($user->code_wilayah, $bk, $user->nokta),
					'modifieddate' => date('Y-m-d H:i:s'),
					'modifiedby' => $this->session->userdata('admin_id'),
				);
*/
			//----------------------------------------------------------------------------------------------------------------------------------------
			//--------------------------------------------------------------------------------------------------------------------------------------------------- Perubahan by Ipur Tgl.19-06-2025 ----
			$up_data = array(
				'va' => generate_va($user->code_wilayah, $bk, $user->no_kta),
				'modifieddate' => date('Y-m-d H:i:s'),
				'modifiedby' => $this->session->userdata('admin_id'),
			);
			//------------------------------------------------------------------------------------------------------------------------------------------

			$update = $this->main_mod->update('user_profiles', array("user_id" => $user_id), $up_data);
			if (is_debug()) {
				$status[] = "SUCCESS: Update table: user_profiles, user_id: {$user_id}";
			}

			$log_data = array(
				'user_id' => $user_id,
				'old_status' => ($user->code_bk_hkk ?: '0'),
				'new_status' => $bk,
				'notes' => 'pindah BK',
				'remarks' => 'dari ' . $user->code_bk_hkk . ' ke ' . $bk,
				'createdby' => $this->session->userdata('admin_id'),
			);
			$log_id = $this->main_mod->insert('log_status_keanggotaan', $log_data);
			if (is_debug()) {
				$status[] = "SUCCESS: Insert into log_status_keanggotaan, id: {$log_id}";
			}

			$this->_rest_response(
				TRUE,
				'200101',
				"Success/update member's BK",
				REST_Controller::HTTP_OK
				//$status
			);
		} catch (Throwable $t) {
			log_message('debug', '[SIMPONI] ' . __CLASS__ . '@' . __FUNCTION__ . ' - Exception ' . $t->getMessage());
			$this->_rest_response(FALSE, 'SIM401207', $t->getMessage(), REST_Controller::HTTP_SERVICE_UNAVAILABLE, $status);
		}
	}

	function sethkk()
	{
		$akses = array("0", "2");
		if (!in_array($this->session->userdata('type'), $akses)) {
			redirect('admin/dashboard');
			exit;
		}

		$idmember = $this->input->post('id') <> null ? $this->input->post('id') : "";
		$hkk = $this->input->post('hkk') <> null ? $this->input->post('hkk') : "";
		if ($idmember == '') {
			redirect('admin/members');
			exit;
		}
		$this->load->model('main_mod');
		if ($idmember != '') {
			try {

				$check = $this->main_mod->msrwhere('members', array('person_id' => $idmember), 'id', 'desc')->result();
				if (isset($check[0])) {
					$where = array(
						"person_id" => $idmember
					);
					$row = array(
						'code_hkk' => $hkk,
						'updated_at' => date('Y-m-d H:i:s'),
						'updated_by' => $this->session->userdata('admin_id'),
					);
					$update = $this->main_mod->update('members', $where, $row);
				}

				echo "valid";
			} catch (Exception $e) {
				//print_r($e);
				echo "not valid";
			}
		} else
			echo "not valid";
	}

	function setang()
	{
		$akses = array("0", "2");
		if (!in_array($this->session->userdata('type'), $akses)) {
			redirect('admin/dashboard');
			exit;
		}

		$idmember = $this->input->post('id') <> null ? $this->input->post('id') : "";
		$ang = $this->input->post('ang') <> null ? $this->input->post('ang') : "";
		if ($idmember == '') {
			redirect('admin/members');
			exit;
		}
		$this->load->model('main_mod');
		if ($idmember != '') {
			try {

				$check = $this->main_mod->msrwhere('members', array('person_id' => $idmember), 'id', 'desc')->result();
				if (isset($check[0])) {
					$where = array(
						"person_id" => $idmember
					);
					$row = array(
						'jenis_anggota' => $ang,
						'updated_at' => date('Y-m-d H:i:s'),
						'updated_by' => $this->session->userdata('admin_id'),
					);
					$update = $this->main_mod->update('members', $where, $row);

					$rowInsert = array(
						'user_id' => $idmember,
						'old_status' => $check[0]->jenis_anggota,
						'new_status' => $ang,
						'notes' => 'pindah jenis anggota',
						'remarks' => 'dari ' . $check[0]->jenis_anggota . ' ke ' . $ang,
						'createdby' => $this->session->userdata('admin_id'),
					);
					$this->main_mod->insert('log_status_keanggotaan', $rowInsert);
				}

				echo "valid";
			} catch (Exception $e) {
				//print_r($e);
				echo "not valid";
			}
		} else
			echo "not valid";
	}

	function setwarga()
	{
		$akses = array("0", "2");
		if (!in_array($this->session->userdata('type'), $akses)) {
			redirect('admin/dashboard');
			exit;
		}

		$idmember = $this->input->post('id') <> null ? $this->input->post('id') : "";
		$ang = $this->input->post('warga') <> null ? $this->input->post('warga') : "";
		if ($idmember == '') {
			redirect('admin/members');
			exit;
		}
		$this->load->model('main_mod');
		if ($idmember != '') {
			try {

				$check = $this->main_mod->msrwhere('user_profiles', array('user_id' => $idmember), 'id', 'desc')->result();
				if (isset($check[0])) {
					$where = array(
						"user_id" => $idmember
					);
					$row = array(
						'warga_asing' => $ang,
						'modifieddate' => date('Y-m-d H:i:s'),
						'modifiedby' => $this->session->userdata('admin_id'),
					);
					$update = $this->main_mod->update('user_profiles', $where, $row);

					$rowInsert = array(
						'user_id' => $idmember,
						'old_status' => $check[0]->warga_asing,
						'new_status' => $ang,
						'notes' => 'pindah warga',
						'remarks' => 'dari ' . $check[0]->warga_asing . ' ke ' . $ang,
						'createdby' => $this->session->userdata('admin_id'),
					);
					$this->main_mod->insert('log_status_keanggotaan', $rowInsert);
				}

				echo "valid";
			} catch (Exception $e) {
				//print_r($e);
				echo "not valid";
			}
		} else
			echo "not valid";
	}

	function _setstri()
	{
		$idmember = $this->input->post('id') <> null ? $this->input->post('id') : "";

		$stri_cabang = $this->input->post('stri_cabang') <> null ? $this->input->post('stri_cabang') : "";
		$stri_kp = $this->input->post('stri_kp') <> null ? $this->input->post('stri_kp') : "";
		$stri_bk = $this->input->post('stri_bk') <> null ? $this->input->post('stri_bk') : "";
		$stri_subbk = $this->input->post('stri_subbk') <> null ? $this->input->post('stri_subbk') : "";
		$stri_type = $this->input->post('stri_type') <> null ? $this->input->post('stri_type') : "";

		$instansi = $this->input->post('instansi') <> null ? $this->input->post('instansi') : "";
		$tgl_sk = $this->input->post('tgl_sk') <> null ? $this->input->post('tgl_sk') : "";
		$from = $this->input->post('from') <> null ? $this->input->post('from') : "";
		$until = $this->input->post('until') <> null ? $this->input->post('until') : "";
		if ($idmember == '') {
			redirect('admin/members');
			exit;
		}
		$this->load->model('main_mod');
		if ($idmember != '') {
			try {
				$idx = $this->members_model->last_stri();
				$idx =  str_pad(($idx->jml + 1), 6, '0', STR_PAD_LEFT);

				$kta = $this->members_model->get_kta_by_personid($idmember);
				$kta = $kta->no_kta;

				$rowInsert = array(
					'person_id' => $idmember,

					'stri_id' => $idx,
					'code_wilayah' => $stri_cabang,
					'code_bk_hkk' => $stri_bk,
					'pm' => $stri_kp,
					'sub_code_bk_hkk' => $stri_subbk,
					'certificate_type' => $stri_type,
					'no_kta' => $kta,
					'instansi' => $instansi,
					'sk' => date('Y-m-d', strtotime($tgl_sk)),
					'from_date' => date('Y-m-d', strtotime($from)),
					'thru_date' => date('Y-m-d', strtotime($until)),
					//'created_by' => $this->session->userdata('user_id'),

				);

				$check = $this->main_mod->msrwhere('members_stri', array('person_id' => $idmember), 'id', 'desc')->result();
				if (isset($check[0])) {
					$where = array(
						"person_id" => $idmember
					);
					$row = array(
						'status' => 0,
						//'updated_at' => date('Y-m-d H:i:s'),
						//'updated_by' => $this->session->userdata('user_id'),
					);
					$update = $this->main_mod->update('members_stri', $where, $row);


					$this->members_model->insert_stri('members_stri', $rowInsert);
				} else {
					$this->members_model->insert_stri('members_stri', $rowInsert);
				}

				echo "valid";
			} catch (Exception $e) {
				//print_r($e);
				echo "not valid";
			}
		} else
			echo "not valid";
	}

	function _setip()
	{
		$idmember = $this->input->post('id') <> null ? $this->input->post('id') : "";
		$ip_cabang = $this->input->post('ip_cabang') <> null ? $this->input->post('ip_cabang') : "";
		$ip_kp = $this->input->post('ip_kp') <> null ? $this->input->post('ip_kp') : "";
		$ip_bk = $this->input->post('ip_bk') <> null ? $this->input->post('ip_bk') : "";
		$ip_subbk = $this->input->post('ip_subbk') <> null ? $this->input->post('ip_subbk') : "";
		$ip_type = $this->input->post('ip_type') <> null ? $this->input->post('ip_type') : "";
		$tgl_sk = $this->input->post('tgl_sk') <> null ? $this->input->post('tgl_sk') : "";
		$from = $this->input->post('from') <> null ? $this->input->post('from') : "";
		$until = $this->input->post('until') <> null ? $this->input->post('until') : "";
		if ($idmember == '') {
			redirect('admin/members');
			exit;
		}
		$this->load->model('main_mod');
		if ($idmember != '') {
			try {
				$temp = '';
				if ($ip_type == 1)
					$temp = 'ip';
				else if ($ip_type == 2)
					$temp = 'ipm';
				else if ($ip_type == 3)
					$temp = 'ipu';

				$t = 'last_' . $temp;

				$idx = $this->members_model->$t();
				$idx =  str_pad(($idx->jml + 1), 6, '0', STR_PAD_LEFT);

				$kta = $this->members_model->get_kta_by_personid($idmember);
				$kta = $kta->no_kta;

				$rowInsert = array(
					'person_id' => $idmember,
					'ip_id' => $idx,
					'code_wilayah' => $ip_cabang,
					'code_bk_hkk' => $ip_bk,
					'pm' => $ip_kp,
					'sub_code_bk_hkk' => $ip_subbk,
					'certificate_type' => $ip_type,
					'no_kta' => $kta,
					'sk' => date('Y-m-d', strtotime($tgl_sk)),
					'from_date' => date('Y-m-d', strtotime($from)),
					'thru_date' => date('Y-m-d', strtotime($until)),
					//'created_by' => $this->session->userdata('user_id'),

				);

				$check = $this->main_mod->msrwhere('members_ip', array('person_id' => $idmember), 'id', 'desc')->result();
				$t = 'insert_' . $temp;
				if (isset($check[0])) {
					$where = array(
						"person_id" => $idmember
					);
					$row = array(
						'status' => 0,
						//'updated_at' => date('Y-m-d H:i:s'),
						//'updated_by' => $this->session->userdata('user_id'),
					);
					$update = $this->main_mod->update('members_ip', $where, $row);


					$this->members_model->$t('members_ip', $rowInsert);
				} else {
					$this->members_model->$t('members_ip', $rowInsert);
				}

				echo "valid";
			} catch (Exception $e) {
				//print_r($e);
				echo "not valid";
			}
		} else
			echo "not valid";
	}

	public function skip()
	{
		$akses = array("0", "1", "10");
		if (!in_array($this->session->userdata('type'), $akses)) {
			redirect('admin/dashboard');
			exit;
		}

		$data['title'] = ''; //SITE_NAME.': Manage Members';
		$data['msg'] = '';

		//Pagination starts
		$total_rows = $this->members_model->record_count_skip('users');
		$config = pagination_configuration(base_url("admin/members/skip"), $total_rows, 10, 4, 5, true);

		$this->pagination->initialize($config);
		$page = ($this->uri->segment(3)) ? $this->uri->segment(4) : 0;
		$page_num = $page - 1;
		$page_num = ($page_num < 0) ? '0' : $page_num;
		$page = $page_num * $config["per_page"];
		$data["links"] = $this->pagination->create_links();
		//Pagination ends
		//print_r($data["links"]);

		$obj_result = $this->members_model->get_all_skip($config["per_page"], $page);
		$data['result'] = $obj_result;
		$data["total_rows"] = $total_rows;

		$data["m_cab"] = $this->members_model->get_all_cabang();
		$data["m_bk"] = $this->members_model->get_all_bk();


		$this->load->view('admin/skip_view', $data);
		return;
	}

	public function search_skip()
	{
		$akses = array("0", "1", "10");
		if (!in_array($this->session->userdata('type'), $akses)) {
			redirect('admin/dashboard');
			exit;
		}

		$data['title'] = ''; //SITE_NAME.': Manage Employers';
		$data['msg'] = '';
		$search_name = '';
		$search_kta = '';

		$this->form_validation->set_rules('firstname', 'firstname', 'trim');
		$this->form_validation->set_rules('kta', 'kta', 'trim');
		$this->form_validation->run();

		if ($_GET) {
			$search_name 	= 	$this->input->get('firstname');
			$search_kta 	= 	$this->input->get('kta');
		}
		if ($search_name == '' && $search_kta == '') {
			redirect(base_url('admin/members/skip'));
			return;
		}
		$new_array = array();
		$search_data = array();
		$search_data2 = array();
		if (isset($search_name) && $search_name != '') {
			$search_data['firstname'] = $search_name;
			//$search_data2['lower(add_name)']=strtolower($search_name);
			$search_data2['REPLACE(lower(nama)," ","")'] = str_replace(' ', '', strtolower($search_name));
		}
		if (isset($search_kta) && $search_kta != '') {
			$search_data['kta'] = $search_kta;
			$search_data2['kta'] = ltrim($search_kta, '0');
		}

		$wild_card = '';

		$url_params = implode(
			'&amp;',
			array_map(
				function ($key, $val) {
					return urlencode($key) . '=' . urlencode($val);
				},
				array_keys($search_data),
				$search_data
			)
		);
		//Pagination starts
		$total_rows = $this->members_model->search_record_count_skip('users', $search_data2);
		$config = pagination_configuration_search(base_url("admin/members/search_skip/?" . $url_params), $total_rows, 10, 3, 5, true);

		$this->pagination->initialize($config);
		$page = $this->input->get('per_page');
		$page_num = $page - 1;
		$page_num = ($page_num < 0) ? '0' : $page_num;
		$page = $page_num * $config["per_page"];
		$data["links"] = $this->pagination->create_links();
		$data["total_rows"] = $total_rows;
		//Pagination ends
		$obj_result = $this->members_model->search_all_skip($config["per_page"], $page, $search_data2, $wild_card);
		$data['result'] = $obj_result;
		$data['search_data'] = $search_data;

		$this->load->view('admin/skip_view', $data);
		return;
	}

	public function pi()
	{
		$this->skip_mainpage();
	}


	public function skip_mainpage()
	{
		$akses = array("0", "1", "10", "11", "9");
		if (!in_array($this->session->userdata('type'), $akses)) {
			//			if($this->session->userdata('admin_id')!=$this->special_admin_676)
			if ($this->session->userdata('admin_id') != $this->special_admin_731 || $this->session->userdata('admin_id') != $this->special_admin_672) {
				redirect('admin/dashboard');
				exit;
			}
		}

		$data['title'] = ''; //SITE_NAME.': Manage Members';
		$data['msg'] = '';

		//Pagination starts
		/*$total_rows = $this->members_model->record_count_pi('users');
		$config = pagination_configuration(base_url("admin/members/pi"), $total_rows, 10, 4, 5, true);

		$this->pagination->initialize($config);
        $page = ($this->uri->segment(3)) ? $this->uri->segment(4) : 0;
		$page_num = $page-1;
		$page_num = ($page_num<0)?'0':$page_num;
		$page = $page_num*$config["per_page"];
		$data["links"] = $this->pagination->create_links();
		//Pagination ends
		//print_r($data["links"]);

		$obj_result = $this->members_model->get_all_pi($config["per_page"], $page);
		$data['result'] = $obj_result;
		$data["total_rows"] = $total_rows;*/

		$data["m_cab"] = $this->members_model->get_all_cabang_wilayah();
		//$data["m_cab"] = $this->members_model->get_all_cabang();
		if ($this->session->userdata('code_bk_hkk') != '') {
			$data["m_bk"] = $this->members_model->get_bk();
		} else {
			$data["m_bk"] = $this->members_model->get_all_bk();
		}
		$data["m_hkk"] = $this->members_model->get_all_hkk();

		$data['showAddSKIP_button'] = isRoot()
			|| (isAdminLSKI() && $this->session->userdata('name') == 'rulyahmadj@yahoo.com');
		$data['showNilaiIP_column'] = isRoot() || $this->session->userdata('admin_id') == "715"  || $this->session->userdata('admin_id') == "784" || $this->session->userdata('admin_id') == "782"  || $this->session->userdata('admin_id') == "731";
		$data['showExportAll_button'] = isAdminLSKI();

		//$this->load->view('admin/pi_view', $data);
		$this->load->view('admin/ip_view', $data);
		return;
	}

	public function search_pi()
	{
		$akses = array("0", "1", "10", "11");
		if (!in_array($this->session->userdata('type'), $akses)) {
			//	if($this->session->userdata('admin_id')!=$this->special_admin_676)
			if (
				$this->session->userdata('admin_id') != $this->special_admin_782 ||
				$this->session->userdata('admin_id') != $this->special_admin_731 || $this->session->userdata('admin_id') != $this->special_admin_672
			) {
				redirect('admin/dashboard');
				exit;
			}
		}

		$data['title'] = ''; //SITE_NAME.': Manage Employers';
		$data['msg'] = '';
		$search_name = '';
		$search_kta = '';

		$this->form_validation->set_rules('firstname', 'firstname', 'trim');
		$this->form_validation->set_rules('kta', 'kta', 'trim');
		$this->form_validation->run();

		if ($_GET) {
			$search_name 	= 	$this->input->get('firstname');
			$search_kta 	= 	$this->input->get('kta');
		}
		if ($search_name == '' && $search_kta == '') {
			redirect(base_url('admin/members/pi'));
			return;
		}
		$new_array = array();
		$search_data = array();
		$search_data2 = array();
		if (isset($search_name) && $search_name != '') {
			$search_data['firstname'] = $search_name;
			//$search_data2['lower(add_name)']=strtolower($search_name);
			$search_data2['REPLACE(lower(add_name)," ","")'] = str_replace(' ', '', strtolower($search_name));
		}
		if (isset($search_kta) && $search_kta != '') {
			$search_data['kta'] = $search_kta;
			$search_data2['no_kta'] = ltrim($search_kta, '0');
		}

		$wild_card = '';

		$url_params = implode(
			'&amp;',
			array_map(
				function ($key, $val) {
					return urlencode($key) . '=' . urlencode($val);
				},
				array_keys($search_data),
				$search_data
			)
		);
		//Pagination starts
		$total_rows = $this->members_model->search_record_count_pi('users', $search_data2);
		$config = pagination_configuration_search(base_url("admin/members/search_pi/?" . $url_params), $total_rows, 10, 3, 5, true);

		$this->pagination->initialize($config);
		$page = $this->input->get('per_page');
		$page_num = $page - 1;
		$page_num = ($page_num < 0) ? '0' : $page_num;
		$page = $page_num * $config["per_page"];
		$data["links"] = $this->pagination->create_links();
		$data["total_rows"] = $total_rows;
		//Pagination ends

		// Main Query
		$obj_result = $this->members_model->search_all_pi($config["per_page"], $page, $search_data2, $wild_card);
		$data['result'] = $obj_result;
		$data['search_data'] = $search_data;

		$this->load->view('admin/pi_view', $data);
		return;
	}

	function old_setip()
	{
		$akses = array("0", "1");
		if (!in_array($this->session->userdata('type'), $akses)) {
			redirect('admin/dashboard');
			exit;
		}

		$idmember = $this->input->post('id') <> null ? $this->input->post('id') : "";
		//$ip_cabang = $this->input->post('ip_cabang')<>null?$this->input->post('ip_cabang'):"";
		//$ip_kp = $this->input->post('ip_kp')<>null?$this->input->post('ip_kp'):"";
		$ip_bk = $this->input->post('ip_bk') <> null ? $this->input->post('ip_bk') : "";
		//$ip_subbk = $this->input->post('ip_subbk')<>null?$this->input->post('ip_subbk'):"";
		$ip_type = $this->input->post('ip_type') <> null ? $this->input->post('ip_type') : "";
		//$tgl_sk = $this->input->post('tgl_sk')<>null?$this->input->post('tgl_sk'):"";
		$from = $this->input->post('from') <> null ? $this->input->post('from') : "";
		$stri_kp = $this->input->post('stri_kp') <> null ? $this->input->post('stri_kp') : "";
		//$until = $this->input->post('until')<>null?$this->input->post('until'):"";
		$no_ip = $this->input->post('no_ip') <> null ? $this->input->post('no_ip') : "";
		$no_seri = $this->input->post('no_seri') <> null ? $this->input->post('no_seri') : "";
		if ($idmember == '') {
			redirect('admin/members');
			exit;
		}
		$this->load->model('main_mod');
		if ($idmember != '') {
			try {
				$faip = $this->faip_model->get_faip_by_id($idmember);
				$no_kta = $faip->no_kta;
				$userid = $faip->user_id;
				$kta = $this->members_model->get_kta_data_by_personid($userid);
				//print_r($kta);
				$checkx = $this->main_mod->msrwhere('user_profiles', array('user_id' => $userid), 'id', 'desc')->result();
				$nama = (isset($checkx[0]->firstname) ? $checkx[0]->firstname : '') . (isset($checkx[0]->firstname) ? ' ' . $checkx[0]->lastname : '');

				$title = '';
				if ($ip_type == "1") $title = 'IPP';
				else if ($ip_type == "2") $title = 'IPM';
				else if ($ip_type == "3") $title = 'IPU';

				$lic_num = $ip_type . '-' . ($ip_bk != '' ? $ip_bk : str_pad($faip->bidang, 2, '0', STR_PAD_LEFT)) . '-00-' . str_pad($no_ip, 6, '0', STR_PAD_LEFT) . '-' . str_pad($stri_kp, 2, '0', STR_PAD_LEFT);

				$rowInsert = array(
					'user_id' => $userid,
					'cert_name' => 'SERTIFIKAT KOMPETENSI INSINYUR PROFESIONAL',
					'cert_auth' => 'LSKI - PERSATUAN INSINYUR INDONESIA',
					'lic_num' => $lic_num,
					'cert_title' => $title,
					'cert_url' => $idmember,
					'location' => str_pad($kta->code_wilayah, 4, '0', STR_PAD_LEFT),
					'negara' => 'Indonesia',
					'startyear' => date('Y-m-d', strtotime($from)),
					'endyear' => date('Y-m-d', strtotime("+5 years", strtotime($from))),


					'ip_kta_wilcab' => str_pad($kta->code_wilayah, 4, '0', STR_PAD_LEFT),
					'ip_kta_bk' => str_pad($kta->code_bk_hkk, 2, '0', STR_PAD_LEFT),
					'ip_kta' => str_pad($no_kta, 6, '0', STR_PAD_LEFT),
					'ip_tipe' => $ip_type,
					'ip_bk' => ($ip_bk != '' ? $ip_bk : str_pad($faip->bidang, 2, '0', STR_PAD_LEFT)),
					'ip_c' => '00',
					'ip_seq' => str_pad($no_ip, 6, '0', STR_PAD_LEFT),

					'description' => str_pad($no_seri, 6, '0', STR_PAD_LEFT),

					'ip_rev' => ($stri_kp != '' ? $stri_kp : '00'),
					'ip_name' => $nama,
					'status' => 2,

					'createdby' => $this->session->userdata('admin_id')
				);

				$this->main_mod->insert('user_cert', $rowInsert);

				//UBAH STATUS

				$rowInsert = array(
					'faip_id' => $idmember,
					'old_status' => 11,
					'new_status' => 12,
					'notes' => 'lski',
					'createdby' => $this->session->userdata('admin_id'),
				);
				$this->main_mod->insert('log_status_faip', $rowInsert);



				$where = array(
					"id" => $idmember
				);
				$row = array(
					'status_faip' => 12,
					//'remarks' => $remarks,
					'modifieddate' => date('Y-m-d H:i:s'),
					'modifiedby' => $this->session->userdata('admin_id'),
				);
				$update = $this->main_mod->update('user_faip', $where, $row);


				echo "valid";
			} catch (Exception $e) {
				//print_r($e);
				echo "not valid";
			}
		} else
			echo "not valid";
	}

	function ajax_faip_setip()
	{
		$akses = array("0", "1");
		if (!in_array($this->session->userdata('type'), $akses)) {
			redirect('admin/dashboard');
			exit;
		}

		$idmember = $this->input->post('id') <> null ? $this->input->post('id') : "";
		//$ip_cabang = $this->input->post('ip_cabang')<>null?$this->input->post('ip_cabang'):"";
		//$ip_kp = $this->input->post('ip_kp')<>null?$this->input->post('ip_kp'):"";
		$ip_bk = $this->input->post('ip_bk') <> null ? $this->input->post('ip_bk') : "";
		$ip_hkk = $this->input->post('ip_hkk') <> null ? $this->input->post('ip_hkk') : "";
		//$ip_subbk = $this->input->post('ip_subbk')<>null?$this->input->post('ip_subbk'):"";
		$ip_type = $this->input->post('ip_type') <> null ? $this->input->post('ip_type') : "";
		//$tgl_sk = $this->input->post('tgl_sk')<>null?$this->input->post('tgl_sk'):"";
		$from = $this->input->post('from') <> null ? $this->input->post('from') : "";
		$starty = date('Y-m-d', strtotime($from));
		$stri_kp = $this->input->post('stri_kp') <> null ? $this->input->post('stri_kp') : "";
		//$until = $this->input->post('until')<>null?$this->input->post('until'):"";
		$no_ip = $this->input->post('no_ip') <> null ? $this->input->post('no_ip') : "";
		$no_seri = $this->input->post('no_seri') <> null ? $this->input->post('no_seri') : "";
		$nama = $this->input->post('nama_ip') <> null ? $this->input->post('nama_ip') : "";


		if ($idmember == '') {
			redirect('admin/members');
			exit;
		}
		$this->load->model('main_mod');
		if ($idmember != '') {
			try {
				//APPROVAL
				$approval = $this->main_mod->msrwhere('approval', array('app_type' => 'faip', 'is_active' => '1'), 'app_seq', 'asc')->result();
				if (isset($approval[0])) {
					$faip = $this->faip_model->get_faip_by_id($idmember);
					$no_kta = $faip->no_kta;
					$userid = $faip->user_id;
					$kta = $this->members_model->get_kta_data_by_personid($userid);
					//print_r($kta);
					$checkx = $this->main_mod->msrwhere('user_profiles', array('user_id' => $userid), 'id', 'desc')->result();
					//$nama = (isset($checkx[0]->firstname)?$checkx[0]->firstname:'').(isset($checkx[0]->firstname)?' '.$checkx[0]->lastname:'');

					$title = '';
					if ($ip_type == "1") $title = 'IPP';
					else if ($ip_type == "2") $title = 'IPM';
					else if ($ip_type == "3") $title = 'IPU';

					$lic_num = $ip_type . '-' . ($ip_bk != '' ? $ip_bk : str_pad($faip->bidang, 2, '0', STR_PAD_LEFT)) . '-' . str_pad($ip_hkk, 2, '0', STR_PAD_LEFT) . '-' . str_pad($no_ip, 6, '0', STR_PAD_LEFT) . '-' . str_pad($stri_kp, 2, '0', STR_PAD_LEFT);

					$rowInsert = array(
						'user_id' => $userid,
						'type_app' => 1,
						'faip_id' => $idmember,
						'cert_name' => 'SERTIFIKAT KOMPETENSI INSINYUR PROFESIONAL',
						'cert_auth' => 'LSKI - PERSATUAN INSINYUR INDONESIA',
						'lic_num' => $lic_num,
						'cert_title' => $title,
						'cert_url' => $idmember,
						'location' => str_pad($kta->code_wilayah, 4, '0', STR_PAD_LEFT),
						'negara' => 'Indonesia',
						'startyear' => date('Y-m-d', strtotime($from)),
						'endyear' => date('Y-m-d', strtotime("+5 years", strtotime($from))),


						'ip_kta_wilcab' => str_pad($kta->code_wilayah, 4, '0', STR_PAD_LEFT),
						'ip_kta_bk' => str_pad($kta->code_bk_hkk, 2, '0', STR_PAD_LEFT),
						'ip_kta' => str_pad($no_kta, 6, '0', STR_PAD_LEFT),
						'ip_tipe' => $ip_type,
						'ip_bk' => ($ip_bk != '' ? $ip_bk : str_pad($faip->bidang, 2, '0', STR_PAD_LEFT)),
						'ip_c' => ($ip_hkk != '' ? $ip_hkk : '00'),
						'ip_seq' => str_pad($no_ip, 6, '0', STR_PAD_LEFT),

						'description' => str_pad($no_seri, 6, '0', STR_PAD_LEFT),

						'ip_rev' => ($stri_kp != '' ? $stri_kp : '00'),
						'ip_name' => $nama,
						'status' => 0,



						'createdby' => $this->session->userdata('admin_id'),
						'modifiedby' => $this->session->userdata('admin_id'),
						'modifieddate' => date('Y-m-d H:i:s')
					);

					$idx = $this->main_mod->insert('user_cert_temp', $rowInsert);



					$get_bk = '';
					if ($approval[0]->app_cond == 'bk') {
						$get_bk = $this->main_mod->msrwhere('admin', array('code_bk_hkk' => ($ip_bk != '' ? $ip_bk : str_pad($faip->bidang, 2, '0', STR_PAD_LEFT))), 'id', 'asc')->row();
					}

					$where = array(
						"id" => $idx
					);
					$row = array(
						'approval_level' => count($approval),
						'next_approval' => (isset($get_bk->id)) ? $get_bk->id : $approval[0]->app_id,
					);
					$update = $this->main_mod->update('user_cert_temp', $where, $row);

					//UBAH STATUS

					$rowInsert = array(
						'faip_id' => $idmember,
						'old_status' => 11,
						'new_status' => 13,
						'notes' => 'lski',
						'createdby' => $this->session->userdata('admin_id'),
					);
					$this->main_mod->insert('log_status_faip', $rowInsert);



					$where = array(
						"id" => $idmember
					);
					$row = array(
						'status_faip' => 13,
						//'remarks' => $remarks,
						'modifieddate' => date('Y-m-d H:i:s'),
						'modifiedby' => $this->session->userdata('admin_id'),
					);
					$update = $this->main_mod->update('user_faip', $where, $row);

					foreach ($approval as $val) {
						$get_bk2 = '';
						if ($val->app_cond == 'bk') {
							$get_bk2 = $this->main_mod->msrwhere('admin', array('code_bk_hkk' => ($ip_bk != '' ? $ip_bk : str_pad($faip->bidang, 2, '0', STR_PAD_LEFT))), 'id', 'asc')->row();
						}

						$rowInsert = array(
							'type_app' => 1,
							'faip_id' => $idmember,
							'seq' => $val->app_seq,
							'app_title' => $val->app_title,
							'app_name' => $val->app_name,
							'app_id' => (isset($get_bk2->id)) ? $get_bk2->id : $val->app_id,
							'app_cond' => $val->app_cond_value,
							'status' => 'Waiting for Approval',
							'createdby' => $this->session->userdata('admin_id')
						);

						$this->main_mod->insert('user_approval', $rowInsert);
					}
				} else {
					$faip = $this->faip_model->get_faip_by_id($idmember);
					$no_kta = $faip->no_kta;
					$userid = $faip->user_id;
					$kta = $this->members_model->get_kta_data_by_personid($userid);
					//print_r($kta);
					$checkx = $this->main_mod->msrwhere('user_profiles', array('user_id' => $userid), 'id', 'desc')->result();
					$nama = (isset($checkx[0]->firstname) ? $checkx[0]->firstname : '') . (isset($checkx[0]->firstname) ? ' ' . $checkx[0]->lastname : '');

					$title = '';
					if ($ip_type == "1") $title = 'IPP';
					else if ($ip_type == "2") $title = 'IPM';
					else if ($ip_type == "3") $title = 'IPU';

					$lic_num = $ip_type . '-' . ($ip_bk != '' ? $ip_bk : str_pad($faip->bidang, 2, '0', STR_PAD_LEFT)) . '-' . str_pad($ip_hkk, 2, '0', STR_PAD_LEFT) . '-' . str_pad($no_ip, 6, '0', STR_PAD_LEFT) . '-' . str_pad($stri_kp, 2, '0', STR_PAD_LEFT);

					$rowInsert = array(
						'user_id' => $userid,
						'cert_name' => 'SERTIFIKAT KOMPETENSI INSINYUR PROFESIONAL',
						'cert_auth' => 'LSKI - PERSATUAN INSINYUR INDONESIA',
						'lic_num' => $lic_num,
						'cert_title' => $title,
						'cert_url' => $idmember,
						'location' => str_pad($kta->code_wilayah, 4, '0', STR_PAD_LEFT),
						'negara' => 'Indonesia',
						'startyear' => date('Y-m-d', strtotime($from)),
						'endyear' => date('Y-m-d', strtotime("+5 years", strtotime($from))),


						'ip_kta_wilcab' => str_pad($kta->code_wilayah, 4, '0', STR_PAD_LEFT),
						'ip_kta_bk' => str_pad($kta->code_bk_hkk, 2, '0', STR_PAD_LEFT),
						'ip_kta' => str_pad($no_kta, 6, '0', STR_PAD_LEFT),
						'ip_tipe' => $ip_type,
						'ip_bk' => ($ip_bk != '' ? $ip_bk : str_pad($faip->bidang, 2, '0', STR_PAD_LEFT)),
						'ip_c' => ($ip_hkk != '' ? $ip_hkk : '00'),
						'ip_seq' => str_pad($no_ip, 6, '0', STR_PAD_LEFT),

						'description' => str_pad($no_seri, 6, '0', STR_PAD_LEFT),

						'ip_rev' => ($stri_kp != '' ? $stri_kp : '00'),
						'ip_name' => $nama,
						'status' => 2,

						'createdby' => $this->session->userdata('admin_id')
					);

					$this->main_mod->insert('user_cert', $rowInsert);

					/*$rowInsert=$this->main_mod->msrquery('select * from user_cert_temp where faip_id = '.$idmember)->row();

					unset($rowInsert['faip_id']);
					unset($rowInsert['status']);
					unset($rowInsert['createdby']);
					unset($rowInsert['createddate']);
					unset($rowInsert['modifiedby']);
					unset($rowInsert['modifieddate']);

					$rowInsert['status'] = 2;
					$rowInsert['createdby'] = $this->session->userdata('admin_id');

					$this->main_mod->insert('user_cert',$rowInsert);*/

					//UBAH STATUS

					$rowInsert = array(
						'faip_id' => $idmember,
						'old_status' => 11,
						'new_status' => 12,
						'notes' => 'lski',
						'createdby' => $this->session->userdata('admin_id'),
					);
					$this->main_mod->insert('log_status_faip', $rowInsert);



					$where = array(
						"id" => $idmember
					);
					$row = array(
						'status_faip' => 12,
						//'remarks' => $remarks,
						'modifieddate' => date('Y-m-d H:i:s'),
						'modifiedby' => $this->session->userdata('admin_id'),
					);
					$update = $this->main_mod->update('user_faip', $where, $row);

					//UPDATE JENIS ANGGOTA
					if ($kta->jenis_anggota == '01' || $kta->jenis_anggota == '1') {
						$where = array(
							"person_id" => $kta->person_id,
						);
						//$rowInsert['updated_by'] = $this->session->userdata('admin_id');
						//$rowInsert['updated_at'] = date('Y-m-d H:i:s');
						$rowInsertx['jenis_anggota'] = '02';

						$update = $this->main_mod->update('members', $where, $rowInsertx);
					}

					//------------------------------------------------ Dibawah ini tambahan untuk STRI Otomatis -by IPur ----------------------------------------


					$insert_user_transfer = array(
						'user_id'           => $userid,
						'pay_type'          => 5,
						'order_id'          => 0,
						'bukti'             => '',
						'atasnama'          => '',
						'tgl'               => '',
						'status'            => 1,
						'description'       => 'Daftar STRI',
						'iuranpangkal'      => 0,
						'iurantahunan'      => 0,
						'sukarelaanggota'   => 0,
						'sukarelagedung'    => 0,
						'sukarelaperpus'    => 0,
						'sukarelaceps'      => 0,
						'sukarelatotal'     => 0,
						'vnv_status'        => 1,
						'is_upload_mandiri' => 0,
						'createddate'       => date('Y-m-d H:i:s'),
						'createdby'         => $userid,
						'modifieddate'      => date('Y-m-d H:i:s'),
						'modifiedby'        => 0

					);

					$this->main_mod->insert_utrf($insert_user_transfer);
					$get_user_cert_by_id = $this->main_mod->cari_id_user_cert($userid, $lic_num, $ip_type, $starty);

					$rel_id = $get_user_cert_by_id->id;
					$data_users = array(
						'rel_id' => $rel_id
					);

					$this->main_mod->update_user_transfer($userid, $data_users);

					//---------------------------------------------------------------------------------------------------------------------------------------
				}

				echo "valid";
			} catch (Exception $e) {
				//print_r($e);
				echo "not valid";
			}
		} else
			echo "not valid";
	}

	function setipapproved($idx)
	{
		//$akses = array("0", "1");
		//if(!in_array($this->session->userdata('type'),$akses)){
		//	redirect('admin/dashboard');
		//	exit;
		//}

		$idmember = $idx; //$this->input->post('id')<>null?$this->input->post('id'):"";
		if ($idmember == '') {
			redirect('admin/members');
			exit;
		}
		$this->load->model('main_mod');
		if ($idmember != '') {
			try {
				$rowInsert = $this->main_mod->msrquery('select * from user_cert_temp where faip_id = ' . $idmember)->row();

				unset($rowInsert->id);
				unset($rowInsert->faip_id);
				unset($rowInsert->status);
				unset($rowInsert->createdby);
				unset($rowInsert->createddate);
				unset($rowInsert->modifiedby);
				unset($rowInsert->modifieddate);
				unset($rowInsert->approval_level);
				unset($rowInsert->next_approval);
				unset($rowInsert->status_approval);
				unset($rowInsert->last_date_approval);

				$rowInsert->status = 2;
				$rowInsert->createdby = $this->session->userdata('admin_id');

				$this->main_mod->insert('user_cert', $rowInsert);

				//UBAH STATUS

				$rowInsert = array(
					'faip_id' => $idmember,
					'old_status' => 11,
					'new_status' => 12,
					'notes' => 'lski',
					'createdby' => $this->session->userdata('admin_id'),
				);
				$this->main_mod->insert('log_status_faip', $rowInsert);



				$where = array(
					"id" => $idmember
				);
				$row = array(
					'status_faip' => 12,
					//'remarks' => $remarks,
					'modifieddate' => date('Y-m-d H:i:s'),
					'modifiedby' => $this->session->userdata('admin_id'),
				);
				$update = $this->main_mod->update('user_faip', $where, $row);


				//echo "valid";
			} catch (Exception $e) {
				//print_r($e);
				//echo "not valid";
			}
		}
		//else
		//	echo "not valid";

	}

	function setstriapproved($idx)
	{
		//$akses = array("0", "1");
		//if(!in_array($this->session->userdata('type'),$akses)){
		//	redirect('admin/dashboard');
		//	exit;
		//}

		$idmember = $idx; //$this->input->post('id')<>null?$this->input->post('id'):"";
		if ($idmember == '') {
			redirect('admin/members');
			exit;
		}
		$this->load->model('main_mod');
		if ($idmember != '') {
			try {
				$where = array(
					"id" => $idmember
				);
				$row = array(
					'is_publish' => 1,
					'modifieddate' => date('Y-m-d H:i:s'),
					'modifiedby' => $this->session->userdata('admin_id'),
				);
				$update = $this->main_mod->update('members_certificate', $where, $row);


				//echo "valid";
			} catch (Exception $e) {
				//print_r($e);
				//echo "not valid";
			}
		}
		//else
		//	echo "not valid";

	}

	function setip_old()
	{
		$akses = array("0", "1");
		if (!in_array($this->session->userdata('type'), $akses)) {
			redirect('admin/dashboard');
			exit;
		}

		$idmember = $this->input->post('id') <> null ? $this->input->post('id') : "";
		$ip_cabang = $this->input->post('ip_cabang') <> null ? $this->input->post('ip_cabang') : "";
		$ip_kp = $this->input->post('ip_kp') <> null ? $this->input->post('ip_kp') : "";
		$ip_bk = $this->input->post('ip_bk') <> null ? $this->input->post('ip_bk') : "";
		$ip_subbk = $this->input->post('ip_subbk') <> null ? $this->input->post('ip_subbk') : "";
		$ip_type = $this->input->post('ip_type') <> null ? $this->input->post('ip_type') : "";
		$tgl_sk = $this->input->post('tgl_sk') <> null ? $this->input->post('tgl_sk') : "";
		$from = $this->input->post('from') <> null ? $this->input->post('from') : "";
		$until = $this->input->post('until') <> null ? $this->input->post('until') : "";
		$no_ip = $this->input->post('no_ip') <> null ? $this->input->post('no_ip') : "";
		if ($idmember == '') {
			redirect('admin/members');
			exit;
		}
		$this->load->model('main_mod');
		if ($idmember != '') {
			try {
				$kta = $this->members_model->get_kta_by_personid($idmember);
				$kta = $kta->no_kta;

				$checkx = $this->main_mod->msrwhere('user_profiles', array('user_id' => $idmember), 'id', 'desc')->result();
				$nama = (isset($checkx[0]->firstname) ? $checkx[0]->firstname : '') . (isset($checkx[0]->firstname) ? ' ' . $checkx[0]->lastname : '');

				$rowInsert = array(
					'wilayah' => $ip_cabang,
					'bk' => $ip_bk,
					'kta' => $kta,
					'sertid' => $ip_type,
					'sk_from' => date('Y-m-d', strtotime($from)),
					'sk_end' => date('Y-m-d', strtotime($until)),
					'noip' => $no_ip,

					'ponsel' => $idmember,
					'email' => $ip_kp,
					'email2' => $ip_subbk,
					'nama' => $nama,
					'nama_only' => $nama,
					'company' => date('Y-m-d', strtotime($tgl_sk)),
					'createdby' => $this->session->userdata('admin_id')
				);

				$this->main_mod->insert('skip45', $rowInsert);

				echo "valid";
			} catch (Exception $e) {
				//print_r($e);
				echo "not valid";
			}
		} else
			echo "not valid";
	}


	function set_stri_approval()
	{
		if ($this->session->userdata('type') != "0" && $this->session->userdata('type') != "2" && $this->session->userdata('type') != "12" && $this->session->userdata('type') != "13") {
			redirect('admin/dashboard');
			exit;
		}

		$idx = $this->uri->segment(4);
		//APPROVAL
		$this->load->model('main_mod');

		$check = $this->main_mod->msrwhere('user_approval', array('type_app' => 2, 'faip_id' => $idx), 'id', 'asc')->num_rows();
		if ($check == 0) {
			$approval = $this->main_mod->msrwhere('approval', array('app_type' => 'stri', 'is_active' => '1'), 'app_seq', 'asc')->result();
			if (isset($approval[0])) {
				/*$where = array(
					"id" => $idx
				);
				$row=array(
					'approval_level' => count($approval),
					'next_approval' => $approval[0]->app_id,
				);
				$update = $this->main_mod->update('members_certificate',$where,$row);	*/


				$members_certificate = $this->main_mod->msrwhere('members_certificate', array('status' => 1, 'id' => $idx), 'id', 'asc')->row();

				$type = '';
				if ($members_certificate->certificate_type == '3') $type = 'IPU';
				else if ($members_certificate->certificate_type == '2') $type = 'IPM';
				else if ($members_certificate->certificate_type == '1') $type = 'IPP';

				$rowInsert = array(
					'user_id' => $members_certificate->person_id,
					'type_app' => 2,
					'faip_id' => $idx,
					//'cert_name' => 'SERTIFIKAT KOMPETENSI INSINYUR PROFESIONAL',
					//'cert_auth' => 'LSKI - PERSATUAN INSINYUR INDONESIA',
					'lic_num' => $members_certificate->stri_id,
					'cert_title' => $type,
					'cert_url' => $idx,
					'location' => str_pad($members_certificate->code_wilayah, 4, '0', STR_PAD_LEFT),
					'negara' => 'Indonesia',
					//'startyear' => date('Y-m-d' , strtotime($from)),
					//'endyear' => date('Y-m-d' , strtotime("+5 years",strtotime($from))),


					'ip_kta_wilcab' => str_pad($members_certificate->code_wilayah, 4, '0', STR_PAD_LEFT),
					'ip_kta_bk' => str_pad($members_certificate->stri_code_bk_hkk, 2, '0', STR_PAD_LEFT),
					'ip_kta' => str_pad($members_certificate->no_kta, 6, '0', STR_PAD_LEFT),
					'ip_tipe' => $members_certificate->certificate_type,
					'ip_bk' => (str_pad($members_certificate->stri_code_bk_hkk, 2, '0', STR_PAD_LEFT)),
					//'ip_c' => ($ip_hkk!=''?$ip_hkk:'00'),
					'ip_seq' => str_pad($members_certificate->stri_id, 6, '0', STR_PAD_LEFT),

					//'description' => str_pad($no_seri, 6, '0', STR_PAD_LEFT),

					'ip_rev' => ($members_certificate->stri_pm != '' ? $members_certificate->stri_pm : '00'),
					'ip_name' => $members_certificate->add_name,
					'status' => 0,



					'createdby' => $this->session->userdata('admin_id'),
					'modifiedby' => $this->session->userdata('admin_id'),
					'modifieddate' => date('Y-m-d H:i:s')
				);

				$idxx = $this->main_mod->insert('user_cert_temp', $rowInsert);

				$where = array(
					"id" => $idxx
				);
				$row = array(
					'approval_level' => count($approval),
					'next_approval' => $approval[0]->app_id,
				);
				$update = $this->main_mod->update('user_cert_temp', $where, $row);

				foreach ($approval as $val) {
					$rowInsert = array(
						'type_app' => 2,
						'faip_id' => $idx,
						'seq' => $val->app_seq,
						'app_title' => $val->app_title,
						'app_name' => $val->app_name,
						'app_id' => $val->app_id,
						'app_cond' => $val->app_cond_value,
						'status' => 'Waiting for Approval',
						'createdby' => $this->session->userdata('admin_id')
					);

					$this->main_mod->insert('user_approval', $rowInsert);
				}
			}
		}
		redirect(base_url('admin/members/stri'));
	}

	public function get_pi()
	{
		$search = $_POST['search']['value'];
		$limit = $_POST['length'];
		$start = $_POST['start'];
		$order_index = $_POST['order'][0]['column'];
		$order_field = $_POST['columns'][$order_index]['data'];
		$order_ascdesc = $_POST['order'][0]['dir'];
		$sql_total = $this->members_model->count_all_pi();
		$sql_data = $this->members_model->filter_pi($search, $limit, $start, $order_field, $order_ascdesc);
		$sql_filter = $this->members_model->count_filter_pi($search);
		$callback = array(
			'draw' => $_POST['draw'],
			'recordsTotal' => $sql_total,
			'recordsFiltered' => $sql_filter,
			'data' => $sql_data
		);
		header('Content-Type: application/json');
		echo json_encode($callback);
	}

	public function get_pi_by_no_kta()
	{
		$no_kta = $_POST['no_kta'];
		$certid = $_POST['certid'];
		$sql_data = $this->members_model->get_pi_by_no_kta($no_kta, $certid);
		$callback = array(
			'data' => $sql_data
		);
		header('Content-Type: application/json');
		echo json_encode($sql_data);
	}

	public function get_pi_by_id()
	{
		$id = $_POST['id'];
		$sql_data = $this->members_model->get_pi_by_id($id);
		$callback = array(
			'data' => $sql_data
		);
		header('Content-Type: application/json');
		echo json_encode($sql_data);
	}

	function deleteskip()
	{
		$this->ajax_skip_deleteskip();
	}

	/**
	 * @deprecated - Move to Skip.php
	 */
	function ajax_skip_deleteskip($id = null)
	{
		//
		if (
			$this->session->userdata('type') != ADMIN_TYPE_SUPERADMIN
			|| $this->session->userdata('type') != ADMIN_TYPE_LSKI
			|| in_array($this->session->userdata('admin_id'), $this->special_admin_lski_admin)
		) {

			$this->_rest_response(FALSE, 'SIM401201',  'No access to this resource/operation.', REST_Controller::HTTP_FORBIDDEN);
		}

		$ucert_id = $id ?: $this->input->post('id');

		if (empty($ucert_id)) {
			$this->_rest_response(FALSE, 'SIM401201',  'Parameter user_cert id is required', REST_Controller::HTTP_NOT_ACCEPTABLE);
		}


		try {
			$this->load->model('main_mod');
			$check = $this->main_mod->msrwhere('user_cert', array('id' => $ucert_id, 'status' => 2), 'id', 'desc')->result();

			if (! isset($check[0])) {
				$this->_rest_response(FALSE, 'SIM401201',  "No user_cert resouce found, id: {$ucert_id}", REST_Controller::HTTP_NOT_FOUND);
			} else {

				if (isset($check[0])) {

					// Copy data to log_user_cert
					unset($check[0]->id);
					unset($check[0]->createddate);
					unset($check[0]->modifiedby);
					unset($check[0]->modifieddate);
					$check[0]->fk_id = $ucert_id;
					$check[0]->log_type = 'delete';
					$this->main_mod->insert('log_user_cert', $check[0]);

					// Delete entry
					$this->main_mod->delete_where('user_cert', array("id" => $ucert_id, "status" => 2));

					$this->_rest_response(TRUE, 'SIM401201',  "Delete successful, id: {$ucert_id}", REST_Controller::HTTP_OK);
				}
			}
		} catch (Throwable $t) {
			log_message('debug', '[SIMPONI] ' . __CLASS__ . '@' . __FUNCTION__ . ' - Exception ' . $t->getMessage());
			$this->_rest_response(FALSE, 'SIM401207', $t->getMessage(), REST_Controller::HTTP_SERVICE_UNAVAILABLE);
		}
	}



	public function get_ip()
	{


		$filter['status'] = $_POST['filter_status'];
		$filter['bk'] = $_POST['filter_bk'];
		$filter['hkk'] = $_POST['filter_hkk'];
		$filter['cab'] = $_POST['filter_cab'];

		if ($_POST['sk_start_date'] != '')
		$filter['sk_start_date'] = $_POST['sk_start_date'];
	if ($_POST['sk_end_date'] != '')
		$filter['sk_end_date'] = $_POST['sk_end_date'];





		$search = $_POST['search']['value'];
		$limit = $_POST['length'];
		$start = $_POST['start'];
		$order_index = $_POST['order'][0]['column'];
		$order_field = $_POST['columns'][$order_index]['data'];

		$column = ($_POST['columns']);

		$order_ascdesc = $_POST['order'][0]['dir'];
		$sql_total = $this->members_model->count_all_ip();
		$sql_data = $this->members_model->filter_ip($search, $limit, $start, $order_field, $order_ascdesc, $column, $filter);
		$sql_filter = $this->members_model->count_filter_ip($search, $column, $filter);

		//print_r($column);

		$callback = array(
			'draw' => $_POST['draw'],
			'recordsTotal' => $sql_total,
			'recordsFiltered' => $sql_filter,
			'data' => $sql_data
		);
		header('Content-Type: application/json');
		echo json_encode($callback);
	}

	public function get_faip_by_id()
	{
		$id = $_POST['id'];
		$sql_data = $this->faip_model->get_faip_by_id($id);
		$callback = array(
			'data' => $sql_data
		);
		header('Content-Type: application/json');
		echo json_encode($sql_data);
	}

	public function get_member_serti_by_id()
	{
		$id = $_POST['id'];
		$sql_data = $this->members_model->get_member_certi_by_id($id);
		$callback = array(
			'data' => $sql_data
		);
		header('Content-Type: application/json');
		echo json_encode($sql_data);
	}

	public function get_member_certi_by_id()
	{
		$id = $_POST['id'];
		$sql_data = $this->members_model->get_member_certii_by_id($id);

		$callback = array(
			'data' => $sql_data
		);

		header('Content-Type: application/json');
		echo json_encode($sql_data);
	}

	public function get_history_by_faipid()
	{
		$id = $_POST['id'];
		$sql_data = $this->faip_model->get_faip_by_id($id);
		$result = array();
		$last_status = 1;
		if (is_object($sql_data)) {
			if ($sql_data->wkt_pernyataan != '') {
				$tgl = str_replace("tanggal ", "", $sql_data->wkt_pernyataan);
				$tmp = explode(" jam ", $tgl);
				//$tgl = str_replace(" jam","",$tgl);
				$tgl = str_replace("\r\n", " ", isset($tmp[0]) ? $tmp[0] : '');

				$result[] = array('value' => '1', 'status' => 'TO V&V (LSKI)', 'tgl' => $tgl, 'jam' => isset($tmp[1]) ? $tmp[1] : '');
			}
			$temp = $this->faip_model->get_faip_status_by_id($id);

			if (is_array($temp)) {
				//print_r($temp);
				$i = 0;
				foreach ($temp as $val) {
					if ($val->new_status == 1 && $i == 0) {
						$tmp = explode(" ", $val->createddate);
						$tgl = strtotime($tmp[0]);
						$tgl = date("d F Y", $tgl);
						$result[0] = array('value' => '1', 'status' => 'TO V&V (LSKI)', 'tgl' => $tgl, 'jam' => substr($tmp[1], 0, 5));
					} else {
						$tmp = explode(" ", $val->createddate);
						$tgl = strtotime($tmp[0]);
						$tgl = date("d F Y", $tgl);
						$result[] = array('value' => $val->new_status, 'status' => $val->status, 'tgl' => $tgl, 'jam' => substr($tmp[1], 0, 5));
						$last_status = $val->new_status;
						$i++;
					}
				}
			}

			$temp = $this->faip_model->get_all_faipstatus_by_lastid($last_status);
			if (is_array($temp)) {
				foreach ($temp as $val) {
					$result[] = array('value' => $val->value, 'status' => $val->name, 'tgl' => '', 'jam' => '');
				}
			}
		}
		header('Content-Type: application/json');
		echo json_encode($result);
	}

	/**
	 * Adding flag for showing or not showing the buttons for some actions based on user access
	 */
	protected function _add_show_flag_faip($data = array())
	{
		$flag = array();
		$flag['showSetSKIP']  = (
			isRoot() ||
			in_array($this->session->userdata('admin_id'), $this->special_admin_lski_admin)
		);
		$flag['showAddFaipManual'] = (
			isRoot()
			|| in_array($this->session->userdata('admin_id'), array('670', '671'))
			|| $this->session->userdata('name') == 'rulyahmadj@yahoo.com'
		);
		$flag['showTglSIPPrint'] = (
			isRoot() ||
			$this->session->userdata('type') == "1"
		);
		// 20240721: Added to make troubleshooting easier - added by Eryan
		$flag['showPaymentInfo'] = (
			isAdminLSKI() ||
			isAdminKolektif() ||
			in_array($this->session->userdata('admin_id'), $this->special_admin_lski_admin)

		);
		// Show button Revisi and its note (catatan)
		$flag['showRevisi'] = (
			isAdminBK() ||
			in_array($this->session->userdata('admin_id'), $this->direktur_lski_ids)
		);
		$flag['showEditFaipManual'] = (
			isRoot() ||
			in_array($this->session->userdata('admin_id'), $this->special_admin_lski_admin)
		);
		$flag['showChangeBK'] = (
			isRoot() ||
			in_array($this->session->userdata('admin_id'), $this->special_admin_lski_admin)
		);
		$flag['showDownloadBAP'] = TRUE;

		return $data + $flag;
	}

	protected function _add_lookup_data_faip($data = array())
	{
		$lookups = array();
		$lookups["m_cab"]          = $this->members_model->get_all_cabang_wilayah();
		$lookups["m_bk"]           = $this->members_model->get_all_bk();
		$lookups["m_hkk"]          = $this->members_model->get_all_hkk();
		$lookups["m_majelis"]      = $this->members_model->get_all_majelis_bk();
		$lookups["m_user_majelis"] = $this->members_model->get_all_user_bk();
		$this->load->model('main_mod');
		$lookups['m_faip_status']  = $this->main_mod->msrwhere('m_faip_status', array('is_active' => 1), 'seq_number', 'asc')->result();

		if (isAdminKolektif() || isAdminKolektifRO()) {
			$lookups["m_kolektif"] = $this->db
				->select('m_kolektif.id, m_kolektif.name')
				->from('admin_kolektif_map')
				->join('m_kolektif', 'm_kolektif.id = admin_kolektif_map.kolektif_id')
				->where('admin_kolektif_map.admin_id', $this->session->userdata('admin_id'))
				->order_by('id', 'DESC')
				->get()->result();
		} else {
			$lookups["m_kolektif"] = $this->db
				->select('m_kolektif.id, m_kolektif.name')
				->from('m_kolektif')
				->order_by('id', 'DESC')
				->get()->result();
		}

		return $data + $lookups;
	}

	/**
	 * @TODO: Will be moved to Faip.php
	 */
	public function faip()
	{

		// 20240712: Kolektif admin (type=11) tidak dikasih akses (menu di dashboard),
		// setelah dicoba akses, SQL error karena di WHERE clause tidak ada value `code_bk_hkk` (fixed)
		// TODO: Jika diakses admin kolektif hanya menampilkan FAIP yg dimiliki users yang berasosiasi
		// dengan kolektif admin
		$akses = array("0", "1", "7", "10", "11");
		if (!in_array($this->session->userdata('type'), $akses)) {
			$this->session->set_flashdata('error', $this->access_deny_msg());
			redirect('admin/dashboard');
			exit;
		}

		$data['title'] = '';
		$data['msg'] = '';

		//Main query
		if (isAdminKolektif() || isAdminKolektifRO()) {
			//Pagination starts
			$total_rows = $this->faip_model->record_count_faip_new('user_faip');

			$config = pagination_configuration(base_url("admin/members/faip"), $total_rows, 10, 4, 5, true);
			$this->pagination->initialize($config);
			$page     = ($this->uri->segment(3)) ? $this->uri->segment(4) : 0;
			$page_num = $page - 1;
			$page_num = ($page_num < 0) ? '0' : $page_num;
			$page     = $page_num * $config["per_page"];
			$data["links"] = $this->pagination->create_links();

			// New query support for Admin kolektif (filtering FAIP based on users that managed by Admin kolektif)
			// It also support other addmin but need to be tested
			$faips = $this->faip_model->get_all_faip_new($config["per_page"], $page);
		} else {

			//Pagination starts
			$total_rows = $this->members_model->record_count_faip('user_faip');

			$config = pagination_configuration(base_url("admin/members/faip"), $total_rows, 10, 4, 5, true);
			$this->pagination->initialize($config);
			$page     = ($this->uri->segment(3)) ? $this->uri->segment(4) : 0;
			$page_num = $page - 1;
			$page_num = ($page_num < 0) ? '0' : $page_num;
			$page     = $page_num * $config["per_page"];
			$data["links"] = $this->pagination->create_links();

			// Use this legacy function at this momentto avoid any bug in the new function above
			// TODO: change this ti new function
			$faips = $this->members_model->get_all_faip($config["per_page"], $page);
		}

		$data['result'] = $faips;
		$data["total_rows"] = $total_rows;

		// Load lookup tables
		$data = $this->_add_lookup_data_faip($data);

		// Flag to show buttons/links
		$data = $this->_add_show_flag_faip($data);

		$this->load->view('admin/faip_view', $data);
	}

	//------------------------------------------------------- Tambahan by IP
	public function faip_return()
	{

		// Untuk menampilkan FAIP dengan 
		// 1. Status 3 ( Return to APL )
		// 2. Status 6 ( To Score MUK )
		// 3. Status 9 ( Final Score MUK )
		// di Dashboard LSKI

		//    $akses = array("0", "1", "7", "10", "11");
		$akses = array("0", "1");
		if (!in_array($this->session->userdata('type'), $akses)) {
			$this->session->set_flashdata('error', $this->access_deny_msg());
			redirect('admin/dashboard');
			exit;
		}

		$data['title'] = '';
		$data['msg'] = '';

		$search_data2['user_faip.nama)'] = ''; //str_replace(' ','',strtolower($search_name));
		$search_data2['user_faip.no_kta'] = ''; // ltrim($search_kta, '0');
		$search_data2['user_faip.status_faip'] = ''; //ltrim($search_status, '0');
		$search_data2['filter_cab'] = ''; // $search_cab;
		$search_data2['filter_bk'] = ''; //$search_bk;
		$search_data2['is_manual'] = ''; //$search_manual;

		$search_data['user_faip.nama)'] = ''; //str_replace(' ','',strtolower($search_name));
		$search_data['user_faip.no_kta'] = ''; // ltrim($search_kta, '0');
		$search_data['user_faip.status_faip'] = ''; //ltrim($search_status, '0');
		$search_data['filter_cab'] = ''; // $search_cab;
		$search_data['filter_bk'] = ''; //$search_bk;
		$search_data['is_manual'] = ''; //$search_manual;
		$wild_card = '';

		$url_params = implode(
			'&amp;',
			array_map(
				function ($key, $val) {
					return urlencode($key) . '=' . urlencode($val);
				},
				array_keys($search_data),
				$search_data
			)
		);

		//Main query

		if (isAdminKolektif() || isAdminKolektifRO()) {
			//Pagination starts

			//	$total_rows = $this->faip_return_model->record_count_faip_new_return('user_faip');
			$total_rows = $this->faip_return_model->record_count_faip_return('user_faip');

			//	$total_rows = $this->faip_model->record_count_faip_new('user_faip');


			$config = pagination_configuration(base_url("admin/members/faip_return"), $total_rows, 10, 4, 5, true);
			$this->pagination->initialize($config);
			$page     = ($this->uri->segment(3)) ? $this->uri->segment(4) : 0;
			$page_num = $page - 1;
			$page_num = ($page_num < 0) ? '0' : $page_num;
			$page     = $page_num * $config["per_page"];
			$data["links"] = $this->pagination->create_links();

			// New query support for Admin kolektif (filtering FAIP based on users that managed by Admin kolektif)
			// It also support other addmin but need to be tested
			//	$faips = $this->faip_return_model->get_all_faip_new($config["per_page"], $page);
			$faips = $this->faip_return_model->get_all_faip_return($config["per_page"], $page);
		} else {

			//Pagination starts
			//			$total_rows = $this->members_model->record_count_faip('user_faip');
			$total_rows = $this->faip_return_model->record_count_faip_return('user_faip');

			$config = pagination_configuration(base_url("admin/members/faip_return"), $total_rows, 10, 4, 5, true);
			$this->pagination->initialize($config);
			$page     = ($this->uri->segment(3)) ? $this->uri->segment(4) : 0;
			$page_num = $page - 1;
			$page_num = ($page_num < 0) ? '0' : $page_num;
			$page     = $page_num * $config["per_page"];
			$data["links"] = $this->pagination->create_links();

			// Use this legacy function at this momentto avoid any bug in the new function above
			// TODO: change this ti new function
			$faips = $this->faip_return_model->get_all_faip_return($config["per_page"], $page);
		}

		$data['result'] = $faips;
		$data["total_rows"] = $total_rows;

		// Load lookup tables
		$data = $this->_add_lookup_data_faip($data);

		// Flag to show buttons/links
		$data = $this->_add_show_flag_faip($data);

		$this->load->view('admin/faip_return_view', $data);
	}

	public function search_faip()
	{
		$akses = array("0", "1", "7", "10", "11");
		if (!in_array($this->session->userdata('type'), $akses)) {
			redirect('admin/dashboard');
			exit;
		}

		$data['title'] = ''; //SITE_NAME.': Manage Employers';
		$data['msg'] = '';
		$search_name = '';
		$search_kta = '';

		$this->form_validation->set_rules('firstname', 'firstname', 'trim');
		$this->form_validation->set_rules('kta', 'kta', 'trim');
		$this->form_validation->set_rules('filter_status', 'status', 'trim');
		$this->form_validation->set_rules('filter_cab', 'wilayah/cabang', 'trim');
		$this->form_validation->set_rules('filter_bk', 'BK', 'trim');
		$this->form_validation->set_rules('filter_hkk', 'BK', 'trim');
		$this->form_validation->set_rules('is_manual', 'is_manual', 'trim|integer');
		$this->form_validation->run();

		if ($_GET) {
			$search_name 	= 	$this->input->get('firstname');
			$search_kta 	= 	$this->input->get('kta');
			$search_status 	= 	$this->input->get('filter_status');
			$search_cab 	= 	$this->input->get('filter_cab');
			$search_bk 	= 	$this->input->get('filter_bk');
			$search_hkk 	= 	$this->input->get('filter_hkk');
			$search_manual 	= 	$this->input->get('is_manual');
		}
		if ($search_name == '' && $search_kta == '' && $search_status == '' && $search_cab == '' && $search_bk == '' && $search_hkk == '') {
			//redirect(base_url('admin/members/faip'));
			//return;
			$this->session->set_flashdata('message', 'No search filter is found');
		}
		$new_array = array();
		$search_data = array();
		$search_data2 = array();
		if (isset($search_name) && $search_name != '') {
			$search_data['firstname'] = $search_name;
			//$search_data2['lower(add_name)']=strtolower($search_name);
			$search_data2['REPLACE(lower(nama)," ","")'] = str_replace(' ', '', strtolower($search_name));
		}
		if (isset($search_kta) && $search_kta != '') {
			$search_data['kta'] = $search_kta;
			$search_data2['user_faip.no_kta'] = ltrim($search_kta, '0');
		}

		if (isset($search_status) && $search_status != '') {
			$search_data['filter_status'] = $search_status;
			$search_data2['user_faip.status_faip'] = ltrim($search_status, '0');
		}

		if (isset($search_cab) && $search_cab != '') {
			$search_data['filter_cab'] = $search_cab;
			$search_data2['filter_cab'] = $search_cab;
		}

		if (isset($search_bk) && $search_bk != '') {
			$search_data['filter_bk'] = $search_bk;
			$search_data2['filter_bk'] = $search_bk;
		}

		if (isset($search_hkk) && $search_hkk != '') {
			$search_data['filter_hkk'] = $search_hkk;
			$search_data2['filter_hkk'] = $search_hkk;
		}

		if (isset($search_manual) && $search_manual != '') {
			$search_data['is_manual'] = $search_manual;
			$search_data2['is_manual'] = $search_manual;
		}
		$wild_card = '';


		$url_params = implode(
			'&amp;',
			array_map(
				function ($key, $val) {
					return urlencode($key) . '=' . urlencode($val);
				},
				array_keys($search_data),
				$search_data
			)
		);

		//Pagination starts

		// Query for total record
		$total_rows = $this->members_model->search_record_count_faip('user_faip', $search_data2);

		$config = pagination_configuration_search(base_url("admin/members/search_faip/?" . $url_params), $total_rows, 10, 3, 5, true);
		$this->pagination->initialize($config);
		$page = $this->input->get('per_page');
		$page_num = $page - 1;
		$page_num = ($page_num < 0) ? '0' : $page_num;
		$page = $page_num * $config["per_page"];
		$data["links"] = $this->pagination->create_links();
		$data["total_rows"] = $total_rows;
		//Pagination ends

		// Main query
		$LEGACY = FALSE;
		if ($LEGACY) {
			$obj_result = $this->members_model->search_all_faip($config["per_page"], $page, $search_data2, $wild_card);
		} else {
			$obj_result = $this->faip_model->search_all_faip_new($config["per_page"], $page, $search_data2, $wild_card);
		}


		$data['result'] = $obj_result;
		$data['search_data'] = $search_data;

		// Construct lookup tables
		$data = $this->_add_lookup_data_faip($data);

		// Adding flag for showing or bot showing the buttons/links
		$data = $this->_add_show_flag_faip($data);

		$this->load->view('admin/faip_view', $data);
		return;
	}

	public function search_faip_return()
	{
		$akses = array("0", "1", "7", "10", "11");
		if (!in_array($this->session->userdata('type'), $akses)) {
			redirect('admin/dashboard');
			exit;
		}

		$data['title'] = ''; //SITE_NAME.': Manage Employers';
		$data['msg'] = '';
		$search_name = '';
		$search_kta = '';

		$this->form_validation->set_rules('firstname', 'firstname', 'trim');
		$this->form_validation->set_rules('kta', 'kta', 'trim');
		$this->form_validation->set_rules('filter_status', 'status', 'trim');
		$this->form_validation->set_rules('filter_cab', 'wilayah/cabang', 'trim');
		$this->form_validation->set_rules('filter_bk', 'BK', 'trim');
		$this->form_validation->set_rules('filter_hkk', 'BK', 'trim');
		$this->form_validation->set_rules('is_manual', 'is_manual', 'trim|integer');
		$this->form_validation->run();

		if ($_GET) {
			$search_name 	= 	$this->input->get('firstname');
			$search_kta 	= 	$this->input->get('kta');
			$search_status 	= 	$this->input->get('filter_status');
			$search_cab 	= 	$this->input->get('filter_cab');
			$search_bk 	= 	$this->input->get('filter_bk');
			$search_hkk 	= 	$this->input->get('filter_hkk');
			$search_manual 	= 	$this->input->get('is_manual');
		}
		if ($search_name == '' && $search_kta == '' && $search_status == '' && $search_cab == '' && $search_bk == '' && $search_hkk == '') {

			$this->session->set_flashdata('message', 'No search filter is found');
		}
		$new_array = array();
		$search_data = array();
		$search_data2 = array();
		if (isset($search_name) && $search_name != '') {
			$search_data['firstname'] = $search_name;
			$search_data2['REPLACE(lower(nama)," ","")'] = str_replace(' ', '', strtolower($search_name));
		}
		if (isset($search_kta) && $search_kta != '') {
			$search_data['kta'] = $search_kta;
			$search_data2['user_faip.no_kta'] = ltrim($search_kta, '0');
		}

		if (isset($search_status) && $search_status != '') {
			$search_data['filter_status'] = $search_status;
			$search_data2['user_faip.status_faip'] = ltrim($search_status, '0');
		}

		if (isset($search_cab) && $search_cab != '') {
			$search_data['filter_cab'] = $search_cab;
			$search_data2['filter_cab'] = $search_cab;
		}

		if (isset($search_bk) && $search_bk != '') {
			$search_data['filter_bk'] = $search_bk;
			$search_data2['filter_bk'] = $search_bk;
		}

		if (isset($search_hkk) && $search_hkk != '') {
			$search_data['filter_hkk'] = $search_hkk;
			$search_data2['filter_hkk'] = $search_hkk;
		}

		if (isset($search_manual) && $search_manual != '') {
			$search_data['is_manual'] = $search_manual;
			$search_data2['is_manual'] = $search_manual;
		}
		$wild_card = '';


		$url_params = implode(
			'&amp;',
			array_map(
				function ($key, $val) {
					return urlencode($key) . '=' . urlencode($val);
				},
				array_keys($search_data),
				$search_data
			)
		);

		//Pagination starts

		// Query for total record
		$total_rowss = $this->faip_return_model->search_record_count_faip('user_faip', $search_data2);

		$config = pagination_configuration_search(base_url("admin/members/search_faip_return/?" . $url_params), $total_rowss, 10, 3, 5, true);
		$this->pagination->initialize($config);
		$page = $this->input->get('per_page');
		$page_num = $page - 1;
		$page_num = ($page_num < 0) ? '0' : $page_num;
		$page = $page_num * $config["per_page"];
		$data["links"] = $this->pagination->create_links();
		$data["total_rows"] = $total_rowss;
		//Pagination ends

		// Main query
		$LEGACY = FALSE;
		//		if ($LEGACY) {
		//			$obj_result = $this->members_model->search_all_faip($config["per_page"], $page, $search_data2, $wild_card);
		//		} else {
		$obj_result = $this->faip_return_model->search_all_faip_return($config["per_page"], $page, $search_data2, $wild_card);

		//		}


		$data['result'] = $obj_result;
		$data['search_data'] = $search_data;

		// Construct lookup tables
		$data = $this->_add_lookup_data_faip($data);

		// Adding flag for showing or bot showing the buttons/links
		$data = $this->_add_show_flag_faip($data);

		$this->load->view('admin/faip_return_view', $data);
		return;
	}


	/**
	 * Load dropdown list items to be showen in the Set Status modal-window
	 * Lihat di https://docs.google.com/spreadsheets/d/12-0pE_B7hqHieX7xVdGr1Oby-YMv1Nk7hT-Q_sDtzK0/edit?gid=1869207821#gid=1869207821
	 * Tab FAIP Status Perpindahan
	 */
	public function ajax_show_status_faip()
	{
		$status_faip = $this->input->get('status') <> null ? $this->input->get('status') : "";
		$this->load->model('main_mod');

		$category = $this->main_mod->msrwhere('m_faip_status', array('id<>8' => null), 'seq_number', 'asc')->result();
		//$category=$this->main_mod->msrwhere('m_faip_status',null,'id','asc')->result();

		//$data = "<option value=''>-- Choose--</option>";
		$data = "";

		// Note: RETURNED_TO_APL = '3';
		// 20240718 remove RETURNED_TO_APL if faip > 2
		foreach ($category as $val) {
			// TO V&V (LSKI)
			if ($status_faip == "1" && in_array(trim($val->value), array('2', '3')) !== FALSE) {
				$data .= "<option value='" . $val->value . "'>" . trim($val->name) . "</option>";
			}
			// FAIP Verified
			elseif ($status_faip == "2" && in_array($val->value, array('2')) !== FALSE) {
				$data .= "<option value='" . $val->value . "'>" . trim($val->name) . "</option>";
			}
			// RETURNED_TO_APL
			elseif ($status_faip == "3" && in_array($val->value, array('5', '6')) !== FALSE) {
				$data .= "<option value='" . $val->value . "'>" . trim($val->name) . "</option>";
			}
			// TO CHECK UM (FIN)
			elseif ($status_faip == "4" && in_array($val->value, array('6', '1')) !== FALSE) {
				$data .= "<option value='" . $val->value . "'>" . trim($val->name) . "</option>";
			}
			// 	Assessment paid valid
			elseif ($status_faip == "5" && in_array($val->value, array('6')) !== FALSE) {
				$data .= "<option value='" . $val->value . "'>" . trim($val->name) . "</option>";
			}
			// TO SCORE (MUK)
			elseif ($status_faip == "6" && in_array($val->value, array('8', '9')) !== FALSE) {
				$data .= "<option value='" . $val->value . "'>" . trim($val->name) . "</option>";
			}
			// TO INTERVIEW (MUK)
			elseif ($status_faip == "8" && in_array($val->value, array('6', '9')) !== FALSE) {
				$data .= "<option value='" . $val->value . "'>" . trim($val->name) . "</option>";
			}
			// FINAL SCORE (MUK)
			elseif ($status_faip == "9" && in_array($val->value, array('6', '8')) !== FALSE) {
				$data .= "<option value='" . $val->value . "'>" . trim($val->name) . "</option>";
			}
			// TO CHECK SIP (FIN)
			elseif ($status_faip == "10" && in_array($val->value, array('6', '8')) !== FALSE) {
				$data .= "<option value='" . $val->value . "'>" . trim($val->name) . "</option>";
			}
			// SIP TO PRINT (LSKI)
			elseif ($status_faip == "11" && in_array($val->value, array('6', '8')) !== FALSE) {
				$data .= "<option value='" . $val->value . "'>" . trim($val->name) . "</option>";
			}
		}
		echo $data;
	}

	/**
	 * Show list of status FAIP
	 * Sepertinya sudah gak dipakai
	 */
	public function ajax_show_status_faip_2()
	{
		$status_faip = $this->input->get('status') <> null ? $this->input->get('status') : "";
		$this->load->model('main_mod');
		$category = $this->main_mod->msrwhere('m_faip_status', null, 'id', 'asc')->result();
		//$data = "<option value=''>-- Choose--</option>";
		$data = "";
		foreach ($category as $val) {
			if ($status_faip == "6" && $val->value == "3")
				$data .= "<option value='" . $val->value . "'>" . $val->name . "</option>";
		}
		echo $data;
	}

	/**
	 * Change/Set BK (Badan Kejuruan) di FAIP main
	 * Note:
	 * 20240724 Perubahan BK bisa terjadi setelah SIP aktif, [TODO] jadi score Asesor sebelumnya tidak boleh dihapus
	 * @deprecated Digabung ke ajax_update_bk()
	 */
	function ajax_update_faip_bk()
	{

		log_message(
			'debug',
			"[SIMPONI] " . __CLASS__ . '@' . __FUNCTION__ . " Http metod: " . $this->input->method() .
				", accessedBy " . $this->session->userdata('user_id' . ', data: ' . $this->input->raw_input_stream)
		);

		$akses = array("0", "1");
		if (!in_array($this->session->userdata('type'), $akses)) {
			$this->_rest_response(FALSE, 'SIM401201',  'No access to this resource/operation.', REST_Controller::HTTP_FORBIDDEN);
		}

		$status = array();
		if (is_debug()) {
			$status[] = $this->debug_msg;
		}

		try {

			$faip_id = $this->input->post('id') <> null ? $this->input->post('id') : "";
			$bk      = $this->input->post('bk') <> null ? $this->input->post('bk') : "";

			// Validation
			$data = array(
				'faip_id' => $faip_id,
				'bk_id' => $bk
			);
			$this->form_validation->set_data($data);
			$this->form_validation->set_rules('faip_id', 'FAIP id', 'required|integer');
			$this->form_validation->set_rules('bk_id', 'BK id', 'required|integer');
			if ($this->form_validation->run() == FALSE) {
				$this->_rest_response(FALSE, 'SIM401204', strip_tags(validation_errors()), REST_Controller::HTTP_BAD_REQUEST);
			}
			if (is_debug()) {
				$status[] = "SUCCESS: Params validation";
			}

			// Check user_faip
			$this->load->model('main_mod');
			$result = $this->main_mod->msrwhere('user_faip', array('id' => $faip_id), 'id', 'desc')->result();
			if (isset($result[0]) == FALSE) {
				$this->_rest_response(FALSE, '401020', "FAIP not found, faip_id: {$faip_id}", REST_Controller::HTTP_NOT_FOUND);
			}
			if (is_debug()) {
				$status[] = "SUCCESS: Get existing faip based on faip_id: {$faip_id}";
			}

			// Copy data for history
			$faip_old = $result[0];
			$majelis = array(
				'faip_id' => $faip_old->id,
				'bidang' => $faip_old->bidang,
				'majelis1' => $faip_old->majelis1,
				'majelis2' => $faip_old->majelis1,
				'majelis13' => $faip_old->majelis1
			);
			log_message('debug',  "[SIMPONI] " . __CLASS__ . '@' . __FUNCTION__ . " Change FAIP BK, previous faip data: " . print_r($majelis, TRUE));

			// Dump data asesor
			$result = $this->main_mod->msrwhere('asesor_faip', array('faip_id' => $faip_id), 'id', 'desc')->result();
			log_message('debug',  "[SIMPONI] " . __CLASS__ . '@' . __FUNCTION__ . " Change FAIP BK, previous asesor data: " . print_r($result, TRUE));
			if (is_debug()) {
				$status[] = "SUCCESS: Dump data asesor: " . print_r($result, TRUE);
			}

			//Delete data asesor
			$this->db->where('faip_id', $faip_id)->delete('asesor_faip');
			if (is_debug()) {
				$status[] = "SUCCESS: Delete asesor_faip, faip_id: {$faip_id}";
			}

			// Update FAIP
			$faip_data = array(
				'majelis1' => '',
				'majelis2' => '',
				'majelis3' => '',
				'bidang' => $bk,
				'modifieddate' => date('Y-m-d H:i:s'),
				'modifiedby' => $this->session->userdata('admin_id'),
			);
			$update = $this->main_mod->update('user_faip', array("id" => $faip_id), $faip_data);
			if (is_debug()) {
				$status[] = "SUCCESS: Update user_faip, faip_id: {$faip_id}";
			}

			// Log perubahan FAIP
			$logfaip_data = array(
				'faip_id' => $faip_id,
				'old_status' => $faip_old->status_faip,
				'new_status' => $faip_old->status_faip,
				'notes' => 'pindah BK',
				'remarks' => 'dari ' . $faip_old->bidang . ' ke ' . $bk,
				'createdby' => $this->session->userdata('admin_id'),
			);
			$logfaip_id = $this->main_mod->insert('log_status_faip', $logfaip_data);
			if (is_debug()) {
				$status[] = "SUCCESS: Insert log_status_faip, id: {$logfaip_id}";
			}

			$this->_rest_response(
				TRUE,
				'200101',
				"Success/update member's BK",
				REST_Controller::HTTP_OK
				//$status
			);
		} catch (Throwable $t) {
			log_message('debug', '[SIMPONI] ' . __CLASS__ . '@' . __FUNCTION__ . ' - Exception ' . $t->getMessage());
			$this->_rest_response(FALSE, 'SIM401211', $t->getMessage(), REST_Controller::HTTP_SERVICE_UNAVAILABLE, $status);
		}
	}

	function savesetmajelis_old()
	{
		$id_faip = $this->input->post('id_faip') <> null ? $this->input->post('id_faip') : "";
		$majelis = $this->input->post('majelis') <> null ? $this->input->post('majelis') : "";
		$tipe_faip = $this->input->post('tipe_faip') <> null ? $this->input->post('tipe_faip') : "";

		if ($id_faip == '') {
			redirect('admin/members/faip');
			exit;
		}
		$this->load->model('main_mod');
		if ($id_faip != '') {
			try {

				$check = $this->main_mod->msrwhere('user_faip', array('id' => $id_faip), 'id', 'desc')->result();
				if (isset($check[0])) {
					$where = array(
						"id" => $id_faip
					);
					$row = array(
						'majelis' . $tipe_faip => $majelis,
						'modifieddate' => date('Y-m-d H:i:s'),
						'modifiedby' => $this->session->userdata('admin_id'),
					);
					$update = $this->main_mod->update('user_faip', $where, $row);
				}

				echo "valid";
			} catch (Exception $e) {
				//print_r($e);
				echo "not valid";
			}
		} else
			echo "not valid";
	}

	function savesetmajelis()
	{
		$akses = array("0", "11");
		if (!in_array($this->session->userdata('type'), $akses)) {
			redirect('admin/dashboard');
			exit;
		}

		$id_faip = $this->input->post('id_faip') <> null ? $this->input->post('id_faip') : "";
		$majelis = $this->input->post('majelis') <> null ? $this->input->post('majelis') : "";
		$tipe_faip = $this->input->post('tipe_faip') <> null ? $this->input->post('tipe_faip') : "";

		if ($id_faip == '') {
			redirect('admin/members/faip');
			exit;
		}
		$this->load->model('main_mod');
		if ($id_faip != '') {
			try {

				$check = $this->main_mod->msrwhere('user_faip', array('id' => $id_faip), 'id', 'desc')->result();
				if (isset($check[0])) {
					$flag = true;
					if ($check[0]->user_id == $majelis)
						$flag = false;
					if ($check[0]->majelis1 == $majelis)
						$flag = false;
					if ($check[0]->majelis2 == $majelis)
						$flag = false;
					if ($check[0]->majelis3 == $majelis)
						$flag = false;

					if ($flag) {
						$where = array(
							"id" => $id_faip
						);
						$row = array(
							'majelis' . $tipe_faip => $majelis,
							'modifieddate' => date('Y-m-d H:i:s'),
							'modifiedby' => $this->session->userdata('admin_id'),
						);
						$update = $this->main_mod->update('user_faip', $where, $row);
						echo "valid";
					} else echo "not valid";
				} else echo "not valid";
			} catch (Exception $e) {
				//print_r($e);
				echo "not valid";
			}
		} else
			echo "not valid";
	}

	function setfaipstatus()
	{
		$akses = array("0", "1", "11");
		if (!in_array($this->session->userdata('type'), $akses)) {
			redirect('admin/dashboard');
			exit;
		}

		$faip_id = $this->input->post('id') <> null ? $this->input->post('id') : "";
		$status = $this->input->post('status') <> null ? $this->input->post('status') : "";
		$remarks = $this->input->post('remarks') <> null ? $this->input->post('remarks') : "";

		$score = $this->input->post('score') <> null ? $this->input->post('score') : "";
		$keputusan = $this->input->post('keputusan') <> null ? $this->input->post('keputusan') : "";

		$interview_date = $this->input->post('tgl') <> null ? $this->input->post('tgl') : "";
		$interview_start_hour = $this->input->post('jam_awal') <> null ? $this->input->post('jam_awal') : "";
		$interview_end_hour = $this->input->post('jam_akhir') <> null ? $this->input->post('jam_akhir') : "";
		$interview_loc = $this->input->post('lokasi') <> null ? $this->input->post('lokasi') : "";
		if (empty($faip_id)) {
			$this->session->set_flashdata('error', "Input id (faip id) is required");
			redirect('admin/members/faip');
			exit;
		}

		$this->load->model('main_mod');
		try {
			// Check FAIP yang dimaksud exist
			$check = $this->main_mod->msrwhere('user_faip', array('id' => $faip_id), 'id', 'desc')->result();
			if (isset($check[0])) {

				// Log perubahan status
				$rowInsert = array(
					'faip_id' => $faip_id,
					'old_status' => $check[0]->status_faip,
					'new_status' => $status,
					'notes' => 'lski',
					'remarks' => $remarks,
					'createdby' => $this->session->userdata('admin_id'),
				);
				$this->main_mod->insert('log_status_faip', $rowInsert);

				// Prepare data untuk update status FAIP
				$row = array(
					'status_faip' => $status,
					'remarks' => $remarks,
					'modifieddate' => date('Y-m-d H:i:s'),
					'modifiedby' => $this->session->userdata('admin_id'),
				);

				if ($status == "8") {
					$row['interview_date'] = date('Y-m-d', strtotime($interview_date));
					$row['interview_start_hour'] = $interview_start_hour;
					$row['interview_end_hour'] = $interview_end_hour;
					$row['interview_loc'] = $interview_loc;
				}

				// Update Status FAIP
				$update = $this->main_mod->update('user_faip', array("id" => $faip_id), $row);

				// NOTIF
				/*$to_email = array();
				$cc_email = array();
				foreach($bk_user as $val){
					$to_email [] = $val->admin_username;

				}
				$cc_email[] =$this->session->userdata('name');
				$subject = "FAIP ".$nama."(".$kta.") pembayaran sudah valid dan siap diuji";

				$data = (array)$check[0];
				$data["m_bk"] = $this->members_model->get_all_bk();
				$data['site_name'] = $this->config->item('website_name', 'tank_auth');
				$this->_send_email('faip_pre_score',$subject, $to_email, $cc_email, $data);*/
				// NOTIF

				// TO SCORE (MUK)
				if ($status == "6") {
					$bk = $check[0]->bidang;
					$nama = $check[0]->nama;
					$kta = str_pad($check[0]->no_kta, 6, '0', STR_PAD_LEFT);
					$bk_user = $this->main_mod->msrwhere('admin', array('type' => 11, 'code_bk_hkk' => str_pad($bk, 2, '0', STR_PAD_LEFT)), 'id', 'desc')->result();


					// Kirim email ke user BK
					if (is_array($bk_user)) {
						$to_email = array();
						$cc_email = array();
						foreach ($bk_user as $val) {
							$to_email[] = $val->admin_username;
						}
						//$cc_email[] =$this->session->userdata('name');
						$cc_email[] = '';
						$subject = "[SIMPONI] FAIP " . $nama . "(" . $kta . ") pembayaran sudah valid dan siap diuji";

						$data = (array)$check[0];
						$data["m_bk"] = $this->members_model->get_all_bk();
						$data['site_name'] = $this->config->item('website_name', 'tank_auth');
						$this->_send_email('faip_pre_score', $subject, $to_email, $cc_email, $data);
					}
				}

				// TO INTERVIEW (MUK)
				else if ($status == "8") {
					$users = $this->main_mod->msrwhere('users', array('id' => $check[0]->user_id), 'id', 'desc')->result();
					$bk = $check[0]->bidang;
					$nama = $check[0]->nama;
					$kta = str_pad($check[0]->no_kta, 6, '0', STR_PAD_LEFT);
					$bk_user = $this->main_mod->msrwhere('admin', array('type' => 11, 'code_bk_hkk' => str_pad($bk, 2, '0', STR_PAD_LEFT)), 'id', 'desc')->result();
					//print_r($bk);
					$to_email = array();
					$cc_email = array();

					$to_email[] = $users[0]->email;

					if (is_array($bk_user)) {
						foreach ($bk_user as $val) {
							$cc_email[] = $val->admin_username;
						}
					}

					//ER: TODO: Admin name belum tentu email
					$cc_email[] = $this->session->userdata('name');

					$subject = "[SIMPONI] Wawancara Calon IP";

					$data = (array)$check[0];
					$data["m_bk"] = $this->members_model->get_all_bk();
					$data['site_name'] = $this->config->item('website_name', 'tank_auth');

					$data['interview_date'] = date('Y-m-d', strtotime($interview_date));
					$data['interview_start_hour'] = $interview_start_hour;
					$data['interview_end_hour'] = $interview_end_hour;
					$data['interview_loc'] = $interview_loc;
					$data['interview_note'] = $remarks;

					if ($interview_date != '')
						$this->_send_email('faip_wawancara', $subject, $to_email, $cc_email, $data);
				}

				// FINAL SCORE (MUK)
				else if ($status == "9") {
					//UPDATE MASTER
					try {
						$row = array(
							'faip_id' => $faip_id,
							//'wajib1_score' => $this->input->post('hwb1')<>null?$this->input->post('hwb1'):"",
							//'wajib2_score' => $this->input->post('hwb2')<>null?$this->input->post('hwb2'):"",
							//'wajib3_score' => $this->input->post('hwb3')<>null?$this->input->post('hwb3'):"",
							//'wajib4_score' => $this->input->post('hwb4')<>null?$this->input->post('hwb4'):"",
							//'pilihan_score' => $this->input->post('hpil')<>null?$this->input->post('hpil'):"",
							'total_score' => $score,
							'keputusan' => $keputusan,
							'tgl_keputusan' => date("Y-m-d"),
							'createdby' => $this->session->userdata('admin_id'),
							'modifiedby' => $this->session->userdata('admin_id'),
							'modifieddate' => date("Y-m-d H:i:s"),
							'status' => "2"
						);
						$where = array(
							'faip_id' => $faip_id,
							'createdby' => $this->session->userdata('admin_id')
						);

						$check = $this->main_mod->msrwhere('asesor_faip', $where, 'id', 'desc')->result();
						if (isset($check[0])) {
							$update = $this->main_mod->update('asesor_faip', $where, $row);
							$asesor_faip_id = $check[0]->id;
						} else {
							$where2 = array(
								'faip_id' => $faip_id,
								'status' => 2,
								'createdby <> ' . $this->session->userdata('admin_id')  => null
							);
							$check2 = $this->main_mod->msrwhere('asesor_faip', $where2, 'id', 'desc')->result();

							$wajib1_score = 0;
							$wajib2_score = 0;
							$wajib3_score = 0;
							$wajib4_score = 0;
							$pilihan_score = 0;
							$count = 0;
							if (isset($check2[0])) {
								foreach ($check2 as $val2) {
									$wajib1_score = $wajib1_score + $val2->wajib1_score;
									$wajib2_score = $wajib2_score + $val2->wajib2_score;
									$wajib3_score = $wajib3_score + $val2->wajib3_score;
									$wajib4_score = $wajib4_score + $val2->wajib4_score;
									$pilihan_score = $pilihan_score + $val2->pilihan_score;
									$count++;
								}
							}

							$row['wajib1_score'] = $wajib1_score / $count;
							$row['wajib2_score'] = $wajib2_score / $count;
							$row['wajib3_score'] = $wajib3_score / $count;
							$row['wajib4_score'] = $wajib4_score / $count;
							$row['pilihan_score'] = $pilihan_score / $count;

							$asesor_faip_id = $this->main_mod->insert('asesor_faip', $row);
						}

						if (
							$keputusan == "Belum memenuhi persyaratan untuk sertifikasi IPP" ||
							$keputusan == "Belum Memenuhi IPP"
						) {

							/*$check = $this->main_mod->msrwhere('user_faip',array('id'=>$faip_id),'id','desc')->result();
							$rowInsert=array(
								'faip_id' => $faip_id,
								'old_status' => $check[0]->status_faip,
								'new_status' => 9,
								'notes' => 'bk',
								'createdby' => $this->session->userdata('admin_id'),
							);
							$this->main_mod->insert('log_status_faip',$rowInsert);

							$where = array(
								"id" => $faip_id
							);
							$row=array(
								'status_faip' => 9,
								'modifieddate' => date('Y-m-d H:i:s'),
								'modifiedby' => $this->session->userdata('admin_id'),
							);
							$update = $this->main_mod->update('user_faip',$where,$row);*/
						} else if (
							$keputusan == "IPP" ||
							$keputusan == "Memenuhi persyaratan untuk sertifikasi IPP" ||
							$keputusan == "Belum memenuhi persyaratan untuk sertifikasi IPM. Memenuhi persyaratan untuk sertifikasi IPP" ||
							$keputusan == "Belum memenuhi persyaratan untuk sertifikasi IPU. Memenuhi persyaratan untuk sertifikasi IPP"
						) {

							$check = $this->main_mod->msrwhere('user_faip', array('id' => $faip_id), 'id', 'desc')->result();
							$rowInsert = array(
								'faip_id' => $faip_id,
								'old_status' => $check[0]->status_faip,
								'new_status' => 11,
								'notes' => 'bk',
								'createdby' => $this->session->userdata('admin_id'),
							);
							$this->main_mod->insert('log_status_faip', $rowInsert);

							$where = array(
								"id" => $faip_id
							);
							$row = array(
								'status_faip' => 11,
								'modifieddate' => date('Y-m-d H:i:s'),
								'modifiedby' => $this->session->userdata('admin_id'),
							);
							$update = $this->main_mod->update('user_faip', $where, $row);
						}

						// Memennuhi IPM/IPU
						else {
							//OTOMATIS PENGAJUAN VA
							$sukarelatotal = 0;
							$status_faip = 10; // TO CHECK SIP (FIN)
							$check = $this->main_mod->msrwhere('user_faip', array('id' => $faip_id), 'id', 'desc')->result();

							if ($keputusan == "IPM" || $keputusan == "Memenuhi persyaratan untuk sertifikasi IPM" || $keputusan == "Belum memenuhi persyaratan untuk sertifikasi IPU. Memenuhi persyaratan untuk sertifikasi IPM") {
								$sukarelatotal = 550000;
							} else if ($keputusan == "IPU" || $keputusan == "Memenuhi persyaratan untuk IPU") {
								$sukarelatotal = 1100000;
							}

							//CEK IS PKB PENINGKATAN
							$check_pkb = $this->main_mod->msrwhere('user_pkb', array('user_id' => $check[0]->user_id, 'upgrade' => 'Peningkatan', 'status_pkb' => 13, 'is_upgrade_paid' => 0), 'id', 'desc')->row();
							if (isset($check_pkb->upgrade)) {

								$sukarelatotal = $sukarelatotal + 100000;
								$row = array(
									'is_upgrade_paid' => 1,
									'modifieddate' => date('Y-m-d H:i:s'),
									'modifiedby' => $this->session->userdata('admin_id'),
								);
								$update = $this->main_mod->update('user_pkb', array('user_id' => $check[0]->user_id, 'id' => $check_pkb->id), $row);
							}

							$row = array(
								'user_id' => $check[0]->user_id,
								'pay_type' =>  4, // FAIP SIP Fee,
								'rel_id' => $faip_id,
								'tgl' => date('Y-m-d'),
								'vnv_status' => 1,
								//'is_upload_mandiri'=>1,
								'sukarelatotal' => $sukarelatotal,
								'createdby' => $this->session->userdata('admin_id'),
								'modifiedby' => $this->session->userdata('admin_id'),
								'modifieddate' => date('Y-m-d H:i:s'),

							);
							$insert = $this->main_mod->insert('user_transfer', $row);
							$rowInsert = array(
								'faip_id' => $faip_id,
								'old_status' => $check[0]->status_faip,
								'new_status' => $status_faip,
								'notes' => 'anggota',
								'createdby' => $this->session->userdata('admin_id'),
							);
							$this->main_mod->insert('log_status_faip', $rowInsert);
							$where = array(
								"id" => $faip_id
							);
							$row = array(
								'status_faip' => $status_faip,
								//'remarks' => $remarks,
								'modifieddate' => date('Y-m-d H:i:s'),
								'modifiedby' => $this->session->userdata('admin_id'),
							);
							$update = $this->main_mod->update('user_faip', $where, $row);
						}
					} catch (Exception $e) {
						log_message('debug', '[SIMPONI] ' . __CLASS__ . '@' . __FUNCTION__ . " Exception: " . $e->getMessage());
						print_r($e);
						return false;
					}
				}

				// FAIP Verified
				else if ($status == "2") {
					$check = $this->main_mod->msrwhere('user_faip', array('id' => $faip_id), 'id', 'desc')->result();
					$pay_type = 3;
					$sukarelatotal = 1100000;
					$status_faip = 4;


					//CEK IS PKB PENINGKATAN
					$check_pkb = $this->main_mod->msrwhere('user_pkb', array('user_id' => $check[0]->user_id, 'upgrade' => 'Peningkatan', 'status_pkb' => 13, 'is_upgrade_paid' => 0), 'id', 'desc')->row();
					if (isset($check_pkb->upgrade)) {
						$status_faip = 5;
						$rowInsert = array(
							'faip_id' => $faip_id,
							'old_status' => $check[0]->status_faip,
							'new_status' => $status_faip,
							'notes' => 'anggota',
							'createdby' => $this->session->userdata('admin_id'),
						);
						$this->main_mod->insert('log_status_faip', $rowInsert);
						$where = array(
							"id" => $faip_id
						);
						$row = array(
							'status_faip' => $status_faip,
							//'remarks' => $remarks,
							'modifieddate' => date('Y-m-d H:i:s'),
							'modifiedby' => $this->session->userdata('admin_id'),
						);
						$update = $this->main_mod->update('user_faip', $where, $row);
					} else {
						//OTOMATIS PENGAJUAN VA

						$ut_data = array(
							'user_id'    => $check[0]->user_id,
							'pay_type'   => $pay_type,
							'rel_id'     => $faip_id,
							'vnv_status' => 1,
							'tgl'        => date('Y-m-d'),
							//'atasnama' => $atasnama,
							//'bukti'    => $bukti,
							//'description' => $desc,
							//'is_upload_mandiri' => 1,
							'sukarelatotal' => $sukarelatotal,
							'createdby'     => $this->session->userdata('admin_id'),
							'modifiedby'    => $this->session->userdata('admin_id'),
							'modifieddate'  => date('Y-m-d H:i:s'),

						);
						$insert = $this->main_mod->insert('user_transfer', $ut_data);
						$rowInsert = array(
							'faip_id'    => $faip_id,
							'old_status' => $check[0]->status_faip,
							'new_status' => $status_faip,
							'notes'      => 'anggota',
							'createdby'  => $this->session->userdata('admin_id'),
						);
						$this->main_mod->insert('log_status_faip', $rowInsert);
						$where = array(
							"id" => $faip_id
						);
						$row = array(
							'status_faip'  => $status_faip,
							//'remarks'    => $remarks,
							'modifieddate' => date('Y-m-d H:i:s'),
							'modifiedby'   => $this->session->userdata('admin_id'),
						);
						$update = $this->main_mod->update('user_faip', $where, $row);
					}
				}
			}

			echo "valid";
		} catch (Exception $e) {
			log_message('denig', '[SIMPONI] ' . __CLASS__ . '@' . __FUNCTION__ . " Exception: " . $e->getMessage());
			//print_r($e);
			echo "not valid";
		}
	}

	function ajax_setfaiprevisi()
	{
		$akses = array("0", "1", "11");
		if (!in_array($this->session->userdata('type'), $akses)) {
			redirect('admin/dashboard');
			exit;
		}

		$faip_id = $this->input->post('id') <> null ? $this->input->post('id') : "";
		$remarks = $this->input->post('remarks') <> null ? $this->input->post('remarks') : "";

		if (empty($faip_id)) {
			$this->session->set_flashdata('error', 'Input FAIP id is required');
			redirect('admin/members/faip');
			exit;
		}
		$this->load->model('main_mod');

		try {

			$check = $this->main_mod->msrwhere('user_faip', array('id' => $faip_id), 'id', 'desc')->result();
			if (isset($check[0])) {
				// Log step revisi FAIP
				$rowInsert = array(
					'faip_id' => $faip_id,
					'old_status' => $check[0]->status_faip,
					'new_status' => $check[0]->status_faip,
					'notes' => 'lski, revisi',
					'remarks' => $remarks,
					'createdby' => $this->session->userdata('admin_id'),
				);
				$this->main_mod->insert('log_status_faip', $rowInsert);


				// Flag FAIP to "Need Revisi"
				$data = array(
					'need_revisi' => 1,
					'revisi_note' => $remarks,
					'modifieddate' => date('Y-m-d H:i:s'),
					'modifiedby' => $this->session->userdata('admin_id'),
				);
				$update = $this->main_mod->update('user_faip', array("id" => $faip_id), $data);

				// Need Send NOTIF?

			}

			echo "valid";
		} catch (Exception $e) {
			log_message('denig', '[SIMPONI] ' . __CLASS__ . '@' . __FUNCTION__ . " Exception: " . $e->getMessage());
			//print_r($e);
			echo "not valid";
		}
	}

	function setstristatus()
	{
		$akses = array("0", "13", "12");
		if (!in_array($this->session->userdata('type'), $akses)) {
			redirect('admin/dashboard');
			exit;
		}

		$faip_id = $this->input->post('id') <> null ? $this->input->post('id') : "";
		$status = $this->input->post('status') <> null ? $this->input->post('status') : "";
		$remarks = $this->input->post('remarks') <> null ? $this->input->post('remarks') : "";

		if ($faip_id == '') {
			redirect('admin/members/stri_member');
			exit;
		}
		$this->load->model('main_mod');
		if ($faip_id != '') {
			try {

				$check = $this->main_mod->msrwhere('user_transfer', array('id' => $faip_id), 'id', 'desc')->result();
				if (isset($check[0])) {
					/*
					$rowInsert=array(
						'faip_id' => $faip_id,
						'old_status' => $check[0]->status_faip,
						'new_status' => $status,
						'notes' => 'lski',
						'createdby' => $this->session->userdata('admin_id'),
					);
					$this->main_mod->insert('log_status_faip',$rowInsert);
					*/

					$member = $this->main_mod->msrwhere('users', array('id' => $check[0]->user_id), 'id', 'desc')->row();

					$where = array(
						"id" => $faip_id
					);
					$row = array(
						'status' => ($status == 1 && $member->username != '' ? 1 : 0),
						'vnv_status' => $status,
						'remark' => $remarks,
						'modifieddate' => date('Y-m-d H:i:s'),
						'modifiedby' => $this->session->userdata('admin_id'),
					);
					$update = $this->main_mod->update('user_transfer', $where, $row);
				}

				echo "valid";
			} catch (Exception $e) {
				//print_r($e);
				echo "not valid";
			}
		} else
			echo "not valid";
	}

	public function faipview()
	{
		$akses = array("0", "1", "7", "10", "11");
		if (!in_array($this->session->userdata('type'), $akses)) {
			redirect('admin/dashboard');
			exit;
		}

		$data['title'] = ''; //SITE_NAME.': Manage Members';
		$data['msg'] = '';
		/*
		//Pagination starts
		$total_rows = $this->members_model->record_count_faip('user_faip');
		$config = pagination_configuration(base_url("admin/members/faip"), $total_rows, 10, 4, 5, true);

		$this->pagination->initialize($config);
        $page = ($this->uri->segment(3)) ? $this->uri->segment(4) : 0;
		$page_num = $page-1;
		$page_num = ($page_num<0)?'0':$page_num;
		$page = $page_num*$config["per_page"];
		$data["links"] = $this->pagination->create_links();
		//Pagination ends
		//print_r($data["links"]);

		$obj_result = $this->members_model->get_all_faip($config["per_page"], $page);
		$data['result'] = $obj_result;
		$data["total_rows"] = $total_rows;
		*/
		//$data["m_cab"] = $this->members_model->get_all_cabang();
		//$data["m_bk"] = $this->members_model->get_all_bk();






		$id_faip = $this->uri->segment(4);
		$faip = $this->faip_model->get_faip_by_id($id_faip);
		$id = isset($faip->user_id) ? $faip->user_id : "";

		if ($id != '') {

			$this->load->model('main_mod');
			$obj_row = $this->members_model->get_member_by_id($id);
			$data['id_faip'] = $id_faip;
			$data['row'] = $obj_row;
			$data['kta'] = $this->members_model->get_kta_data_by_personid($id);;
			$data['emailx'] = $obj_row->email;
			//$data['m_komp']=$this->main_mod->msrwhere('no_kompetensi',array('status'=>'1'),'id','asc')->result();

			$data['m_bk'] = $this->main_mod->msrwhere('m_bk', null, 'id', 'asc')->result();
			//$data['majelis_p'] = $this->members_model->get_majelis_p_by_bk($faip->bidang);
			//$data['majelis_q'] = $this->members_model->get_majelis_q_by_bk($faip->bidang);
			//$data['majelis_r'] = $this->members_model->get_majelis_r_by_bk($faip->bidang);


			$data['m_komp'] = $this->main_mod->msrquery('select value,title from no_kompetensi a join m_kompetensi b on a.value=b.code')->result();
			$data['m_act_13'] = $this->main_mod->msrquery('select code as value,title from m_kompetensi where code like "W.1%" order by id asc')->result();
			$data['m_act_15'] = $this->main_mod->msrquery('select code as value,title from m_kompetensi where code like "W.2%" or code like "W.4%" or code like "P.10%" order by id asc')->result();
			$data['m_act_16'] = $this->main_mod->msrquery('select code as value,title from m_kompetensi where code like "W.1%" or code like "W.4%" or code like "P.10%" order by id asc')->result();

			$data['m_act_3'] = $this->main_mod->msrquery('select code as value,title from m_kompetensi where
			code like "W.2%" or
			code like "W.3%" or
			code like "W.4%" or
			code like "P.6%" or
			code like "P.7%" or
			code like "P.8%" or
			code like "P.9%" or
			code like "P.10%" or
			code like "P.11%"
			order by id asc')->result();
			$data['m_act_4'] = $this->main_mod->msrquery('select code as value,title from m_kompetensi where code like "P.5%" order by id asc')->result();
			$data['m_act_51'] = $this->main_mod->msrquery('select code as value,title from m_kompetensi where code like "W.4%" order by id asc')->result();
			$data['m_act_53'] = $this->main_mod->msrquery('select code as value,title from m_kompetensi where code like "W.2%" order by id asc')->result();
			$data['m_act_54'] = $this->main_mod->msrquery('select code as value,title from m_kompetensi where code like "P.6%" order by id asc')->result();




			$data['m_degree'] = $this->main_mod->msrwhere('education_type', null, 'SEQ', 'asc')->result();
			//$data['user_address']=$this->main_mod->msrwhere('user_address',array('user_id'=>$id,'status'=>1),'id','asc')->result();
			$data['user_email'] = $this->main_mod->msrwhere('contacts', array('user_id' => $id, 'contact_type like "%_email%"' => null, 'status' => 1), 'id', 'asc')->result();
			//$data['user_phone']=$this->main_mod->msrwhere('contacts',array('user_id'=>$id,'contact_type like "%_phone%"'=>null,'status'=>1),'id','asc')->result();
			//$data['user_edu']=$this->main_mod->msrwhere('user_edu',array('user_id'=>$id,'status'=>1),'id','asc')->result();
			//$data['user_cert']=$this->main_mod->msrwhere('user_cert',array('user_id'=>$id,'status'=>1),'id','asc')->result();
			//$data['user_exp']=$this->main_mod->msrwhere('user_exp',array('user_id'=>$id,'status'=>1),'id','asc')->result();

			$data['user_faip'] = $faip;
			$data['user_faip_11'] = $this->main_mod->msrwhere('user_faip_11', array('faip_id' => $id_faip, 'status' => 1), 'id', 'asc')->result();
			$data['user_faip_12'] = $this->main_mod->msrwhere('user_faip_12', array('faip_id' => $id_faip, 'status' => 1), 'id', 'asc')->result();
			$data['user_faip_111'] = $this->main_mod->msrwhere('user_faip_111', array('faip_id' => $id_faip, 'status' => 1), 'id', 'asc')->result();
			$data['user_faip_112'] = $this->main_mod->msrwhere('user_faip_112', array('faip_id' => $id_faip, 'status' => 1), 'id', 'asc')->result();
			$data['user_faip_113'] = $this->main_mod->msrwhere('user_faip_113', array('faip_id' => $id_faip, 'status' => 1), 'id', 'asc')->result();
			$data['user_faip_13'] = $this->faip_model->get_all_faip_comp($id_faip, 'user_faip_13');
			$data['user_faip_14'] = $this->faip_model->get_all_faip_comp($id_faip, 'user_faip_14');
			$data['user_faip_15'] = $this->faip_model->get_all_faip_comp($id_faip, 'user_faip_15');
			$data['user_faip_16'] = $this->faip_model->get_all_faip_comp($id_faip, 'user_faip_16');

			$data['user_faip_21'] = $this->faip_model->get_all_faip_comp($id_faip, 'user_faip_21');
			$data['user_faip_22'] = $this->faip_model->get_all_faip_comp($id_faip, 'user_faip_22');
			$data['user_faip_3'] = $this->faip_model->get_all_faip_comp($id_faip, 'user_faip_3');
			$data['user_faip_4'] = $this->faip_model->get_all_faip_comp($id_faip, 'user_faip_4');
			$data['user_faip_51'] = $this->faip_model->get_all_faip_comp($id_faip, 'user_faip_51');
			$data['user_faip_52'] = $this->faip_model->get_all_faip_comp($id_faip, 'user_faip_52');
			$data['user_faip_53'] = $this->faip_model->get_all_faip_comp($id_faip, 'user_faip_53');
			$data['user_faip_54'] = $this->faip_model->get_all_faip_comp($id_faip, 'user_faip_54');
			$data['user_faip_6'] = $this->faip_model->get_all_faip_comp($id_faip, 'user_faip_6');
			$data['user_faip_lam'] = $this->main_mod->msrwhere('user_faip_lam', array('faip_id' => $id_faip, 'status' => 1), 'id', 'asc')->result();


			$faipNumWithPenilaian_list = array('12', '13', '14', '15', '16', '3', '4', '51', '52', '53', '54', '6');
			foreach ($faipNumWithPenilaian_list as $faip_num) {
				$data['bp_' . $faip_num] = $this->main_mod->msrwhere('m_bakuan_penilaian', array('faip_num' => $faip_num), 'id', 'asc')->result();
			}

			/*
			if($this->session->userdata('type')=="1")
			{

				$this->form_validation->set_rules('mp1', 'Majelis Penilai 1', 'trim|xss_clean|required');
				$this->form_validation->set_rules('mp2', 'Majelis Penilai 2', 'trim|xss_clean|required');
				$this->form_validation->set_rules('mp3', 'Majelis Penilai 3', 'trim|xss_clean|required');
			}

			if($this->session->userdata('type')=="3" || $this->session->userdata('type')=="7")
			{
				$this->form_validation->set_rules('13_p[]', 'Form 1.3 P', 'trim|xss_clean|required');
				$this->form_validation->set_rules('14_p[]', 'Form 1.4 P', 'trim|xss_clean|required');
				$this->form_validation->set_rules('15_p[]', 'Form 1.5 P', 'trim|xss_clean|required');
				$this->form_validation->set_rules('16_p[]', 'Form 1.6 P', 'trim|xss_clean|required');
				//$this->form_validation->set_rules('22_p[]', 'Form 2.2 P', 'trim|xss_clean|required');
				$this->form_validation->set_rules('3_p[]', 'Form 3 P', 'trim|xss_clean|required');
				$this->form_validation->set_rules('4_p[]', 'Form 4 P', 'trim|xss_clean|required');
				$this->form_validation->set_rules('51_p[]', 'Form 5.1 P', 'trim|xss_clean|required');
				$this->form_validation->set_rules('52_p[]', 'Form 5.2 P', 'trim|xss_clean|required');
				$this->form_validation->set_rules('53_p[]', 'Form 5.3 P', 'trim|xss_clean|required');
				$this->form_validation->set_rules('54_p[]', 'Form 5.4 P', 'trim|xss_clean|required');
				$this->form_validation->set_rules('6_p[]', 'Form 6 P', 'trim|xss_clean|required');
			}

			if($this->session->userdata('type')=="4" || $this->session->userdata('type')=="7")
			{
				$this->form_validation->set_rules('13_q[]', 'Form 1.3 Q', 'trim|xss_clean|required');
				$this->form_validation->set_rules('14_q[]', 'Form 1.4 Q', 'trim|xss_clean|required');
				$this->form_validation->set_rules('15_q[]', 'Form 1.5 Q', 'trim|xss_clean|required');
				$this->form_validation->set_rules('16_q[]', 'Form 1.6 Q', 'trim|xss_clean|required');
				//$this->form_validation->set_rules('22_q[]', 'Form 2.2 Q', 'trim|xss_clean|required');
				$this->form_validation->set_rules('3_q[]', 'Form 3 Q', 'trim|xss_clean|required');
				$this->form_validation->set_rules('4_q[]', 'Form 4 Q', 'trim|xss_clean|required');
				$this->form_validation->set_rules('51_q[]', 'Form 5.1 Q', 'trim|xss_clean|required');
				$this->form_validation->set_rules('52_q[]', 'Form 5.2 Q', 'trim|xss_clean|required');
				$this->form_validation->set_rules('53_q[]', 'Form 5.3 Q', 'trim|xss_clean|required');
				$this->form_validation->set_rules('54_q[]', 'Form 5.4 Q', 'trim|xss_clean|required');
				$this->form_validation->set_rules('6_q[]', 'Form 6 Q', 'trim|xss_clean|required');
			}
			if($this->session->userdata('type')=="5" || $this->session->userdata('type')=="7")
			{
				$this->form_validation->set_rules('13_r[]', 'Form 1.3 R', 'trim|xss_clean|required');
				$this->form_validation->set_rules('14_r[]', 'Form 1.4 R', 'trim|xss_clean|required');
				$this->form_validation->set_rules('15_r[]', 'Form 1.5 R', 'trim|xss_clean|required');
				$this->form_validation->set_rules('16_r[]', 'Form 1.6 R', 'trim|xss_clean|required');
				//$this->form_validation->set_rules('22_r[]', 'Form 2.2 R', 'trim|xss_clean|required');
				$this->form_validation->set_rules('3_r[]', 'Form 3 R', 'trim|xss_clean|required');
				$this->form_validation->set_rules('4_r[]', 'Form 4 R', 'trim|xss_clean|required');
				$this->form_validation->set_rules('51_r[]', 'Form 5.1 R', 'trim|xss_clean|required');
				$this->form_validation->set_rules('52_r[]', 'Form 5.2 R', 'trim|xss_clean|required');
				$this->form_validation->set_rules('53_r[]', 'Form 5.3 R', 'trim|xss_clean|required');
				$this->form_validation->set_rules('54_r[]', 'Form 5.4 R', 'trim|xss_clean|required');
				$this->form_validation->set_rules('6_r[]', 'Form 6 R', 'trim|xss_clean|required');
			}
			if($this->session->userdata('type')=="6" || $this->session->userdata('type')=="7")
			{
				$this->form_validation->set_rules('13_t[]', 'Form 1.3 T', 'trim|xss_clean|required');
				$this->form_validation->set_rules('14_t[]', 'Form 1.4 T', 'trim|xss_clean|required');
				$this->form_validation->set_rules('15_t[]', 'Form 1.5 T', 'trim|xss_clean|required');
				$this->form_validation->set_rules('16_t[]', 'Form 1.6 T', 'trim|xss_clean|required');
				//$this->form_validation->set_rules('22_t[]', 'Form 2.2 T', 'trim|xss_clean|required');
				$this->form_validation->set_rules('3_t[]', 'Form 3 T', 'trim|xss_clean|required');
				$this->form_validation->set_rules('4_t[]', 'Form 4 T', 'trim|xss_clean|required');
				$this->form_validation->set_rules('51_t[]', 'Form 5.1 T', 'trim|xss_clean|required');
				$this->form_validation->set_rules('52_t[]', 'Form 5.2 T', 'trim|xss_clean|required');
				$this->form_validation->set_rules('53_t[]', 'Form 5.3 T', 'trim|xss_clean|required');
				$this->form_validation->set_rules('54_t[]', 'Form 5.4 T', 'trim|xss_clean|required');
				$this->form_validation->set_rules('6_t[]', 'Form 6 T', 'trim|xss_clean|required');

				$this->form_validation->set_rules('tempat', 'Tempat', 'trim|xss_clean|required');
				$this->form_validation->set_rules('hwb1', 'Majelis Penilai 3', 'trim|xss_clean');
				$this->form_validation->set_rules('hwb2', 'Majelis Penilai 3', 'trim|xss_clean');
				$this->form_validation->set_rules('hwb3', 'Majelis Penilai 3', 'trim|xss_clean');
				$this->form_validation->set_rules('hwb4', 'Majelis Penilai 3', 'trim|xss_clean');
				$this->form_validation->set_rules('hpil', 'Majelis Penilai 3', 'trim|xss_clean');
				$this->form_validation->set_rules('hjml', 'Majelis Penilai 3', 'trim|xss_clean');
				$this->form_validation->set_rules('hkeputusan', 'Majelis Penilai 3', 'trim|xss_clean');
			}
			*/


			$data['hwb1'] = $this->input->post('hwb1') <> null ? $this->input->post('hwb1') : "";
			$data['hwb2'] = $this->input->post('hwb2') <> null ? $this->input->post('hwb2') : "";
			$data['hwb3'] = $this->input->post('hwb3') <> null ? $this->input->post('hwb3') : "";
			$data['hwb4'] = $this->input->post('hwb4') <> null ? $this->input->post('hwb4') : "";
			$data['hpil'] = $this->input->post('hpil') <> null ? $this->input->post('hpil') : "";
			$data['hjml'] = $this->input->post('hjml') <> null ? $this->input->post('hjml') : "";
			$data['hkeputusan'] = $this->input->post('hkeputusan') <> null ? $this->input->post('hkeputusan') : "";

			$data['asesor_12'] = $this->faip_model->get_all_faip_comp_asesor($id_faip, $this->session->userdata('admin_id'), '12');
			$data['asesor_13'] = $this->faip_model->get_all_faip_comp_asesor($id_faip, $this->session->userdata('admin_id'), '13');
			$data['asesor_14'] = $this->faip_model->get_all_faip_comp_asesor($id_faip, $this->session->userdata('admin_id'), '14');
			$data['asesor_15'] = $this->faip_model->get_all_faip_comp_asesor($id_faip, $this->session->userdata('admin_id'), '15');
			$data['asesor_16'] = $this->faip_model->get_all_faip_comp_asesor($id_faip, $this->session->userdata('admin_id'), '16');
			$data['asesor_21'] = $this->faip_model->get_all_faip_comp_asesor($id_faip, $this->session->userdata('admin_id'), '21');
			$data['asesor_22'] = $this->faip_model->get_all_faip_comp_asesor($id_faip, $this->session->userdata('admin_id'), '22');
			$data['asesor_3'] = $this->faip_model->get_all_faip_comp_asesor($id_faip, $this->session->userdata('admin_id'), '3');
			$data['asesor_4'] = $this->faip_model->get_all_faip_comp_asesor($id_faip, $this->session->userdata('admin_id'), '4');
			$data['asesor_51'] = $this->faip_model->get_all_faip_comp_asesor($id_faip, $this->session->userdata('admin_id'), '51');
			$data['asesor_52'] = $this->faip_model->get_all_faip_comp_asesor($id_faip, $this->session->userdata('admin_id'), '52');
			$data['asesor_53'] = $this->faip_model->get_all_faip_comp_asesor($id_faip, $this->session->userdata('admin_id'), '53');
			$data['asesor_54'] = $this->faip_model->get_all_faip_comp_asesor($id_faip, $this->session->userdata('admin_id'), '54');
			$data['asesor_6'] = $this->faip_model->get_all_faip_comp_asesor($id_faip, $this->session->userdata('admin_id'), '6');
			$data['asesor_faip'] = $this->main_mod->msrwhere('asesor_faip', array('faip_id' => $id_faip, 'modifiedby' => $this->session->userdata('admin_id')), 'id', 'asc')->result();

			$this->form_validation->set_rules('hwb1', 'hwb1', 'trim|xss_clean');

			$is_submit = $this->input->post('submitfaip') <> null ? $this->input->post('submitfaip') : "";
			$submit = $this->input->post('submit') <> null ? $this->input->post('submit') : "";

			if ($this->form_validation->run()) {
				$save_partial = $this->input->post('save_partial') <> null ? $this->input->post('save_partial') : "";
				$id = $id_faip;
				$asesor_faip_id = 0;
				//UPDATE MASTER
				try {
					$row = array(
						'faip_id' => $id_faip,
						'wajib1_score' => $this->input->post('hwb1') <> null ? $this->input->post('hwb1') : "",
						'wajib2_score' => $this->input->post('hwb2') <> null ? $this->input->post('hwb2') : "",
						'wajib3_score' => $this->input->post('hwb3') <> null ? $this->input->post('hwb3') : "",
						'wajib4_score' => $this->input->post('hwb4') <> null ? $this->input->post('hwb4') : "",
						'pilihan_score' => $this->input->post('hpil') <> null ? $this->input->post('hpil') : "",
						'total_score' => $this->input->post('hjml') <> null ? $this->input->post('hjml') : "",
						'keputusan' => $this->input->post('hkeputusan') <> null ? $this->input->post('hkeputusan') : "",
						'tgl_keputusan' => date("Y-m-d"),
						'createdby' => $this->session->userdata('admin_id'),
						'modifiedby' => $this->session->userdata('admin_id'),
						'modifieddate' => date("Y-m-d H:i:s"),
						'status' => ($submit == "1") ? "2" : "1"
					);
					$where = array(
						'faip_id' => $id_faip,
						'createdby' => $this->session->userdata('admin_id')
					);

					$check = $this->main_mod->msrwhere('asesor_faip', $where, 'id', 'desc')->result();
					if (isset($check[0])) {
						$update = $this->main_mod->update('asesor_faip', $where, $row);
						$asesor_faip_id = $check[0]->id;
					} else
						$asesor_faip_id = $this->main_mod->insert('asesor_faip', $row);
				} catch (Exception $e) {
					print_r($e);
					return false;
				}

				$p12 = $this->input->post('12_p') <> null ? $this->input->post('12_p') : "";
				$q12 = $this->input->post('12_q') <> null ? $this->input->post('12_q') : "";
				$r12 = $this->input->post('12_r') <> null ? $this->input->post('12_r') : "";
				$t12 = $this->input->post('12_t') <> null ? $this->input->post('12_t') : "";
				$c12 = $this->input->post('12_c') <> null ? $this->input->post('12_c') : "";

				$p13 = $this->input->post('13_p') <> null ? $this->input->post('13_p') : "";
				$q13 = $this->input->post('13_q') <> null ? $this->input->post('13_q') : "";
				$r13 = $this->input->post('13_r') <> null ? $this->input->post('13_r') : "";
				$t13 = $this->input->post('13_t') <> null ? $this->input->post('13_t') : "";
				$c13 = $this->input->post('13_c') <> null ? $this->input->post('13_c') : "";
				$is_m13 = $this->input->post('13_is_m') <> null ? $this->input->post('13_is_m') : "";

				$p14 = $this->input->post('14_p') <> null ? $this->input->post('14_p') : "";
				$q14 = $this->input->post('14_q') <> null ? $this->input->post('14_q') : "";
				$r14 = $this->input->post('14_r') <> null ? $this->input->post('14_r') : "";
				$t14 = $this->input->post('14_t') <> null ? $this->input->post('14_t') : "";
				$c14 = $this->input->post('14_c') <> null ? $this->input->post('14_c') : "";
				$is_m14 = $this->input->post('14_is_m') <> null ? $this->input->post('14_is_m') : "";

				$p15 = $this->input->post('15_p') <> null ? $this->input->post('15_p') : "";
				$q15 = $this->input->post('15_q') <> null ? $this->input->post('15_q') : "";
				$r15 = $this->input->post('15_r') <> null ? $this->input->post('15_r') : "";
				$t15 = $this->input->post('15_t') <> null ? $this->input->post('15_t') : "";
				$c15 = $this->input->post('15_c') <> null ? $this->input->post('15_c') : "";
				$is_m15 = $this->input->post('15_is_m') <> null ? $this->input->post('15_is_m') : "";

				$p16 = $this->input->post('16_p') <> null ? $this->input->post('16_p') : "";
				$q16 = $this->input->post('16_q') <> null ? $this->input->post('16_q') : "";
				$r16 = $this->input->post('16_r') <> null ? $this->input->post('16_r') : "";
				$t16 = $this->input->post('16_t') <> null ? $this->input->post('16_t') : "";
				$c16 = $this->input->post('16_c') <> null ? $this->input->post('16_c') : "";
				$is_m16 = $this->input->post('16_is_m') <> null ? $this->input->post('16_is_m') : "";

				$t21 = $this->input->post('21_t') <> null ? $this->input->post('21_t') : "";

				$p22 = $this->input->post('22_p') <> null ? $this->input->post('22_p') : "";
				$q22 = $this->input->post('22_q') <> null ? $this->input->post('22_q') : "";
				$r22 = $this->input->post('22_r') <> null ? $this->input->post('22_r') : "";
				$t22 = $this->input->post('22_t') <> null ? $this->input->post('22_t') : "";
				$c22 = $this->input->post('22_c') <> null ? $this->input->post('22_c') : "";

				$p3 = $this->input->post('3_p') <> null ? $this->input->post('3_p') : "";
				$q3 = $this->input->post('3_q') <> null ? $this->input->post('3_q') : "";
				$r3 = $this->input->post('3_r') <> null ? $this->input->post('3_r') : "";
				$t3 = $this->input->post('3_t') <> null ? $this->input->post('3_t') : "";
				$c3 = $this->input->post('3_c') <> null ? $this->input->post('3_c') : "";
				$is_m3 = $this->input->post('3_is_m') <> null ? $this->input->post('3_is_m') : "";

				$p4 = $this->input->post('4_p') <> null ? $this->input->post('4_p') : "";
				$q4 = $this->input->post('4_q') <> null ? $this->input->post('4_q') : "";
				$r4 = $this->input->post('4_r') <> null ? $this->input->post('4_r') : "";
				$t4 = $this->input->post('4_t') <> null ? $this->input->post('4_t') : "";
				$c4 = $this->input->post('4_c') <> null ? $this->input->post('4_c') : "";
				$is_m4 = $this->input->post('4_is_m') <> null ? $this->input->post('4_is_m') : "";

				$p51 = $this->input->post('51_p') <> null ? $this->input->post('51_p') : "";
				$q51 = $this->input->post('51_q') <> null ? $this->input->post('51_q') : "";
				$r51 = $this->input->post('51_r') <> null ? $this->input->post('51_r') : "";
				$t51 = $this->input->post('51_t') <> null ? $this->input->post('51_t') : "";
				$c51 = $this->input->post('51_c') <> null ? $this->input->post('51_c') : "";
				$is_m51 = $this->input->post('51_is_m') <> null ? $this->input->post('51_is_m') : "";

				$p52 = $this->input->post('52_p') <> null ? $this->input->post('52_p') : "";
				$q52 = $this->input->post('52_q') <> null ? $this->input->post('52_q') : "";
				$r52 = $this->input->post('52_r') <> null ? $this->input->post('52_r') : "";
				$t52 = $this->input->post('52_t') <> null ? $this->input->post('52_t') : "";
				$c52 = $this->input->post('52_c') <> null ? $this->input->post('52_c') : "";
				$is_m52 = $this->input->post('52_is_m') <> null ? $this->input->post('52_is_m') : "";

				$p53 = $this->input->post('53_p') <> null ? $this->input->post('53_p') : "";
				$q53 = $this->input->post('53_q') <> null ? $this->input->post('53_q') : "";
				$r53 = $this->input->post('53_r') <> null ? $this->input->post('53_r') : "";
				$t53 = $this->input->post('53_t') <> null ? $this->input->post('53_t') : "";
				$c53 = $this->input->post('53_c') <> null ? $this->input->post('53_c') : "";
				$is_m53 = $this->input->post('53_is_m') <> null ? $this->input->post('53_is_m') : "";

				$p54 = $this->input->post('54_p') <> null ? $this->input->post('54_p') : "";
				$q54 = $this->input->post('54_q') <> null ? $this->input->post('54_q') : "";
				$r54 = $this->input->post('54_r') <> null ? $this->input->post('54_r') : "";
				$t54 = $this->input->post('54_t') <> null ? $this->input->post('54_t') : "";
				$c54 = $this->input->post('54_c') <> null ? $this->input->post('54_c') : "";
				$is_m54 = $this->input->post('54_is_m') <> null ? $this->input->post('54_is_m') : "";

				$p6 = $this->input->post('6_p') <> null ? $this->input->post('6_p') : "";
				$q6 = $this->input->post('6_q') <> null ? $this->input->post('6_q') : "";
				$r6 = $this->input->post('6_r') <> null ? $this->input->post('6_r') : "";
				$t6 = $this->input->post('6_t') <> null ? $this->input->post('6_t') : "";
				$c6 = $this->input->post('6_c') <> null ? $this->input->post('6_c') : "";
				$is_m6 = $this->input->post('6_is_m') <> null ? $this->input->post('6_is_m') : "";

				$user_faip_12 = $this->main_mod->msrwhere('user_faip_12', array('faip_id' => $id_faip, 'status' => 1), 'id', 'asc')->result();
				$user_faip_13 = $this->faip_model->get_all_faip_comp_noparent($id_faip, 'user_faip_13');
				$user_faip_14 = $this->faip_model->get_all_faip_comp_noparent($id_faip, 'user_faip_14');
				$user_faip_15 = $this->faip_model->get_all_faip_comp_noparent($id_faip, 'user_faip_15');
				$user_faip_16 = $this->faip_model->get_all_faip_comp_noparent($id_faip, 'user_faip_16');
				$user_faip_21 = $this->faip_model->get_all_faip_comp_noparent($id_faip, 'user_faip_21');
				$user_faip_22 = $this->faip_model->get_all_faip_comp_noparent($id_faip, 'user_faip_22');

				$user_faip_3 = $this->faip_model->get_all_faip_comp_noparent($id_faip, 'user_faip_3');
				$user_faip_4 = $this->faip_model->get_all_faip_comp_noparent($id_faip, 'user_faip_4');
				$user_faip_51 = $this->faip_model->get_all_faip_comp_noparent($id_faip, 'user_faip_51');
				$user_faip_52 = $this->faip_model->get_all_faip_comp_noparent($id_faip, 'user_faip_52');
				$user_faip_53 = $this->faip_model->get_all_faip_comp_noparent($id_faip, 'user_faip_53');
				$user_faip_54 = $this->faip_model->get_all_faip_comp_noparent($id_faip, 'user_faip_54');
				$user_faip_6 = $this->faip_model->get_all_faip_comp_noparent($id_faip, 'user_faip_6');

				// COMP 12
				if ($is_submit == "1" || $save_partial == '12') {
					if ($is_submit == "1") $temp = '';
					else $temp = $this->main_mod->delete_where('asesor_score', array("faip_id" => $id_faip, "asesor_faip_id" => $asesor_faip_id, "faip_num" => "12"));

					if (is_array($user_faip_12)) {
						$j = 0;
						foreach ($user_faip_12 as $val) {
							try {
								$row = array(
									'asesor_faip_id' => $asesor_faip_id,
									'faip_id' => $id_faip,
									'faip_num' => "12",
									'user_faip_id' => $val->id,
									'kompetensi' => "W.2",

									'p' => $p12[$j],
									'q' => $q12[$j],
									'r' => $r12[$j],
									't' => $t12[$j],
									'notes' => "",
									'createdby' => $this->session->userdata('admin_id')
								);
								$update = $this->main_mod->insert('asesor_score', $row);
							} catch (Exception $e) {
								print_r($e);
								break;
							}
							$j++;
						}
					}
				}
				// COMP 13
				if ($is_submit == "1" || $save_partial == '13') {
					if ($is_submit == "1") $temp = '';
					else $temp = $this->main_mod->delete_where('asesor_score', array("faip_id" => $id_faip, "asesor_faip_id" => $asesor_faip_id, "faip_num" => "13"));

					if (is_array($c13)) {
						$j = 0;
						foreach ($c13 as $key => $val) {
							try {
								$row = array(
									'asesor_faip_id' => $asesor_faip_id,
									'faip_id' => $id_faip,
									'faip_num' => "13",
									'user_faip_id' => $key,
									'kompetensi' => $val,

									'is_add_by_majelis' => $is_m13[$j],

									'p' => $p13[$j],
									'q' => $q13[$j],
									'r' => $r13[$j],
									't' => $t13[$j],
									'notes' => "",

									'createdby' => $this->session->userdata('admin_id')

								);
								$update = $this->main_mod->insert('asesor_score', $row);
							} catch (Exception $e) {
								print_r($e);
								break;
							}
							$j++;
						}
					}
				}
				// COMP 14
				if ($is_submit == "1" || $save_partial == '14') {
					if ($is_submit == "1") $temp = '';
					else $temp = $this->main_mod->delete_where('asesor_score', array("faip_id" => $id_faip, "asesor_faip_id" => $asesor_faip_id, "faip_num" => "14"));

					if (is_array($c14)) {
						$j = 0;
						foreach ($c14 as $key => $val) {
							try {
								$row = array(
									'asesor_faip_id' => $asesor_faip_id,
									'faip_id' => $id_faip,
									'faip_num' => "14",
									'user_faip_id' => $key,
									'kompetensi' => $val,

									'is_add_by_majelis' => $is_m14[$j],

									'p' => $p14[$j],
									'q' => $q14[$j],
									'r' => $r14[$j],
									't' => $t14[$j],
									'notes' => "",

									'createdby' => $this->session->userdata('admin_id')
								);
								$update = $this->main_mod->insert('asesor_score', $row);
							} catch (Exception $e) {
								print_r($e);
								break;
							}
							$j++;
						}
					}
				}
				// COMP 15
				if ($is_submit == "1" || $save_partial == '15') {
					if ($is_submit == "1") $temp = '';
					else $temp = $this->main_mod->delete_where('asesor_score', array("faip_id" => $id_faip, "asesor_faip_id" => $asesor_faip_id, "faip_num" => "15"));

					if (is_array($c15)) {
						$j = 0;
						foreach ($c15 as $key => $val) {
							try {
								$row = array(
									'asesor_faip_id' => $asesor_faip_id,
									'faip_id' => $id_faip,
									'faip_num' => "15",
									'user_faip_id' => $key,
									'kompetensi' => $val,

									'is_add_by_majelis' => $is_m15[$j],

									'p' => $p15[$j],
									'q' => $q15[$j],
									'r' => $r15[$j],
									't' => $t15[$j],
									'notes' => "",

									'createdby' => $this->session->userdata('admin_id')
								);
								$update = $this->main_mod->insert('asesor_score', $row);
							} catch (Exception $e) {
								print_r($e);
								break;
							}
							$j++;
						}
					}
				}
				// COMP 16
				if ($is_submit == "1" || $save_partial == '16') {
					if ($is_submit == "1") $temp = '';
					else $temp = $this->main_mod->delete_where('asesor_score', array("faip_id" => $id_faip, "asesor_faip_id" => $asesor_faip_id, "faip_num" => "16"));

					if (is_array($c16)) {
						$j = 0;
						foreach ($c16 as $key => $val) {
							try {
								$row = array(
									'asesor_faip_id' => $asesor_faip_id,
									'faip_id' => $id_faip,
									'faip_num' => "16",
									'user_faip_id' => $key,
									'kompetensi' => $val,

									'is_add_by_majelis' => $is_m16[$j],

									'p' => $p16[$j],
									'q' => $q16[$j],
									'r' => $r16[$j],
									't' => $t16[$j],
									'notes' => "",

									'createdby' => $this->session->userdata('admin_id')
								);
								$update = $this->main_mod->insert('asesor_score', $row);
							} catch (Exception $e) {
								print_r($e);
								break;
							}
							$j++;
						}
					}
				}
				// COMP 21
				if ($is_submit == "1" || $save_partial == '21') {
					if ($is_submit == "1") $temp = '';
					else $temp = $this->main_mod->delete_where('asesor_score', array("faip_id" => $id_faip, "asesor_faip_id" => $asesor_faip_id, "faip_num" => "21"));

					//if(is_array($c22)){
					//$j=0;
					//foreach($c22 as $key=>$val){
					try {
						$row = array(
							'asesor_faip_id' => $asesor_faip_id,
							'faip_id' => $id_faip,
							'faip_num' => "21",
							'user_faip_id' => '',
							'kompetensi' => '',

							'p' => 0,
							'q' => 0,
							'r' => 0,
							't' => (isset($t21) ? $t21 : 0),
							'notes' => "",

							'createdby' => $this->session->userdata('admin_id')
						);
						$update = $this->main_mod->insert('asesor_score', $row);
					} catch (Exception $e) {
						//print_r($e);//break;
					}
					//$j++;
					//}
					//}
				}
				// COMP 22
				if ($is_submit == "1" || $save_partial == '22') {
					if ($is_submit == "1") $temp = '';
					else $temp = $this->main_mod->delete_where('asesor_score', array("faip_id" => $id_faip, "asesor_faip_id" => $asesor_faip_id, "faip_num" => "22"));

					if (is_array($t22)) {
						$j = 0;
						foreach ($t22 as $key => $val) {
							try {
								$row = array(
									'asesor_faip_id' => $asesor_faip_id,
									'faip_id' => $id_faip,
									'faip_num' => "22",
									'user_faip_id' => $key,
									//'kompetensi' => $val,

									//'p' => $p22[$j],
									//'q' => $q22[$j],
									//'r' => $r22[$j],
									't' => $val,
									'notes' => "",

									'createdby' => $this->session->userdata('admin_id')
								);
								$update = $this->main_mod->insert('asesor_score', $row);
							} catch (Exception $e) {
								print_r($e);
								break;
							}
							$j++;
						}
					}
				}
				// COMP 3
				if ($is_submit == "1" || $save_partial == '3') {
					if ($is_submit == "1") $temp = '';
					else $temp = $this->main_mod->delete_where('asesor_score', array("faip_id" => $id_faip, "asesor_faip_id" => $asesor_faip_id, "faip_num" => "3"));

					if (is_array($c3)) {
						$j = 0;
						foreach ($c3 as $key => $val) {
							try {
								$row = array(
									'asesor_faip_id' => $asesor_faip_id,
									'faip_id' => $id_faip,
									'faip_num' => "3",
									'user_faip_id' => $key,
									'kompetensi' => $val,

									'is_add_by_majelis' => $is_m3[$j],

									'p' => $p3[$j],
									'q' => $q3[$j],
									'r' => $r3[$j],
									't' => $t3[$j],
									'notes' => "",

									'createdby' => $this->session->userdata('admin_id')
								);
								$update = $this->main_mod->insert('asesor_score', $row);
							} catch (Exception $e) {
								print_r($e);
								break;
							}
							$j++;
						}
					}
				}
				// COMP 4
				if ($is_submit == "1" || $save_partial == '4') {
					if ($is_submit == "1") $temp = '';
					else $temp = $this->main_mod->delete_where('asesor_score', array("faip_id" => $id_faip, "asesor_faip_id" => $asesor_faip_id, "faip_num" => "4"));

					if (is_array($c4)) {
						$j = 0;
						foreach ($c4 as $key => $val) {
							try {
								$row = array(
									'asesor_faip_id' => $asesor_faip_id,
									'faip_id' => $id_faip,
									'faip_num' => "4",
									'user_faip_id' => $key,
									'kompetensi' => $val,

									'is_add_by_majelis' => $is_m4[$j],

									'p' => $p4[$j],
									'q' => $q4[$j],
									'r' => $r4[$j],
									't' => $t4[$j],
									'notes' => "",

									'createdby' => $this->session->userdata('admin_id')
								);
								$update = $this->main_mod->insert('asesor_score', $row);
							} catch (Exception $e) {
								print_r($e);
								break;
							}
							$j++;
						}
					}
				}

				// COMP 51
				if ($is_submit == "1" || $save_partial == '51') {
					if ($is_submit == "1") $temp = '';
					else $temp = $this->main_mod->delete_where('asesor_score', array("faip_id" => $id_faip, "asesor_faip_id" => $asesor_faip_id, "faip_num" => "51"));

					if (is_array($c51)) {
						$j = 0;
						foreach ($c51 as $key => $val) {
							try {
								$row = array(
									'asesor_faip_id' => $asesor_faip_id,
									'faip_id' => $id_faip,
									'faip_num' => "51",
									'user_faip_id' => $key,
									'kompetensi' => $val,

									'is_add_by_majelis' => $is_m51[$j],

									'p' => $p51[$j],
									'q' => $q51[$j],
									'r' => $r51[$j],
									't' => $t51[$j],
									'notes' => "",

									'createdby' => $this->session->userdata('admin_id')
								);
								$update = $this->main_mod->insert('asesor_score', $row);
							} catch (Exception $e) {
								print_r($e);
								break;
							}
							$j++;
						}
					}
				}
				// COMP 52
				if ($is_submit == "1" || $save_partial == '52') {
					if ($is_submit == "1") $temp = '';
					else $temp = $this->main_mod->delete_where('asesor_score', array("faip_id" => $id_faip, "asesor_faip_id" => $asesor_faip_id, "faip_num" => "52"));

					if (is_array($c52)) {
						$j = 0;
						foreach ($c52 as $key => $val) {
							try {
								$row = array(
									'asesor_faip_id' => $asesor_faip_id,
									'faip_id' => $id_faip,
									'faip_num' => "52",
									'user_faip_id' => $key,
									'kompetensi' => $val,

									'is_add_by_majelis' => $is_m52[$j],

									'p' => $p52[$j],
									'q' => $q52[$j],
									'r' => $r52[$j],
									't' => $t52[$j],
									'notes' => "",

									'createdby' => $this->session->userdata('admin_id')
								);
								$update = $this->main_mod->insert('asesor_score', $row);
							} catch (Exception $e) {
								print_r($e);
								break;
							}
							$j++;
						}
					}
				}
				// COMP 53
				if ($is_submit == "1" || $save_partial == '53') {
					if ($is_submit == "1") $temp = '';
					else $temp = $this->main_mod->delete_where('asesor_score', array("faip_id" => $id_faip, "asesor_faip_id" => $asesor_faip_id, "faip_num" => "53"));

					if (is_array($c53)) {
						$j = 0;
						foreach ($c53 as $key => $val) {
							try {
								$row = array(
									'asesor_faip_id' => $asesor_faip_id,
									'faip_id' => $id_faip,
									'faip_num' => "53",
									'user_faip_id' => $key,
									'kompetensi' => $val,

									'is_add_by_majelis' => $is_m53[$j],

									'p' => $p53[$j],
									'q' => $q53[$j],
									'r' => $r53[$j],
									't' => $t53[$j],
									'notes' => "",

									'createdby' => $this->session->userdata('admin_id')
								);
								$update = $this->main_mod->insert('asesor_score', $row);
							} catch (Exception $e) {
								print_r($e);
								break;
							}
							$j++;
						}
					}
				}
				// COMP 54
				if ($is_submit == "1" || $save_partial == '54') {
					if ($is_submit == "1") $temp = '';
					else $temp = $this->main_mod->delete_where('asesor_score', array("faip_id" => $id_faip, "asesor_faip_id" => $asesor_faip_id, "faip_num" => "54"));

					if (is_array($c54)) {
						$j = 0;
						foreach ($c54 as $key => $val) {
							try {
								$row = array(
									'asesor_faip_id' => $asesor_faip_id,
									'faip_id' => $id_faip,
									'faip_num' => "54",
									'user_faip_id' => $key,
									'kompetensi' => $val,

									'is_add_by_majelis' => $is_m54[$j],

									'p' => $p54[$j],
									'q' => $q54[$j],
									'r' => $r54[$j],
									't' => $t54[$j],
									'notes' => "",

									'createdby' => $this->session->userdata('admin_id')
								);
								$update = $this->main_mod->insert('asesor_score', $row);
							} catch (Exception $e) {
								print_r($e);
								break;
							}
							$j++;
						}
					}
				}
				// COMP 6
				if ($is_submit == "1" || $save_partial == '6') {
					if ($is_submit == "1") $temp = '';
					else $temp = $this->main_mod->delete_where('asesor_score', array("faip_id" => $id_faip, "asesor_faip_id" => $asesor_faip_id, "faip_num" => "6"));

					if (is_array($c6)) {
						$j = 0;
						foreach ($c6 as $key => $val) {
							try {
								$row = array(
									'asesor_faip_id' => $asesor_faip_id,
									'faip_id' => $id_faip,
									'faip_num' => "6",
									'user_faip_id' => $key,
									'kompetensi' => $val,

									'is_add_by_majelis' => $is_m6[$j],

									'p' => $p6[$j],
									'q' => $q6[$j],
									'r' => $r6[$j],
									't' => $t6[$j],
									'notes' => "",

									'createdby' => $this->session->userdata('admin_id')
								);
								$update = $this->main_mod->insert('asesor_score', $row);
							} catch (Exception $e) {
								print_r($e);
								break;
							}
							$j++;
						}
					}
				}





				echo $id;

				if ($is_submit == "1") redirect('admin/members/faip');
			} else
				$this->load->view('admin/faip_edit', $data);
			return;
		}
	}

	/**
	 * Dipanggil dari button di kolom skor majelis.
	 * View FAIP dengan skor penilaian Asesor
	 */
	public function faipview2()
	{
		// Refactor to new function name
		$this->faipview_assessor_result();
	}


	/**
	 * Dipanggil dari button di kolom skor majelis.
	 * View FAIP dengan skor penilaian Asesor
	 */
	public function faipview_assessor_result()
	{
		$akses = array(ADMIN_SUPERADMIN, ADMIN_LSKI, ADMIN_RESERVED_GROUP7, ADMIN_SKIP, ADMIN_WILAYAH_BK_KOLEKTIF);
		if (!in_array($this->session->userdata('type'), $akses)) {
			redirect('admin/dashboard');
			exit;
		}

		$id_faip   = $this->uri->segment(4);
		$asesor_id = $this->uri->segment(5);

		$faip = $this->faip_model->get_faip_by_id($id_faip);
		$id = isset($faip->user_id) ? $faip->user_id : "";

		if (empty($id)) $this->_rest_response(FALSE, '400209', 'Id user is required');

		$this->load->model('main_mod');
		$obj_row = $this->members_model->get_member_by_id($id);
		$data['id_faip'] = $id_faip;
		$data['row'] = $obj_row;
		$data['kta'] = $this->members_model->get_kta_data_by_personid($id);;
		$data['emailx'] = $obj_row->email;

		$data['m_bk'] = $this->main_mod->msrwhere('m_bk', null, 'id', 'asc')->result();


		$data['m_komp'] = $this->main_mod->msrquery('select value,title from no_kompetensi a join m_kompetensi b on a.value=b.code')->result();
		$data['m_act_13'] = $this->main_mod->msrquery('select code as value,title from m_kompetensi where code like "W.1%" order by id asc')->result();
		$data['m_act_15'] = $this->main_mod->msrquery('select code as value,title from m_kompetensi where code like "W.2%" or code like "W.4%" or code like "P.10%" order by id asc')->result();
		$data['m_act_16'] = $this->main_mod->msrquery('select code as value,title from m_kompetensi where code like "W.1%" or code like "W.4%" or code like "P.10%" order by id asc')->result();

		$data['m_act_3'] = $this->main_mod->msrquery('select code as value,title from m_kompetensi where
				code like "W.2%" or
				code like "W.3%" or
				code like "W.4%" or
				code like "P.6%" or
				code like "P.7%" or
				code like "P.8%" or
				code like "P.9%" or
				code like "P.10%" or
				code like "P.11%"
				order by id asc')->result();
		$data['m_act_4'] = $this->main_mod->msrquery('select code as value,title from m_kompetensi where code like "P.5%" order by id asc')->result();
		$data['m_act_51'] = $this->main_mod->msrquery('select code as value,title from m_kompetensi where code like "W.4%" order by id asc')->result();
		$data['m_act_53'] = $this->main_mod->msrquery('select code as value,title from m_kompetensi where code like "W.2%" order by id asc')->result();
		$data['m_act_54'] = $this->main_mod->msrquery('select code as value,title from m_kompetensi where code like "P.6%" order by id asc')->result();


		$data['m_degree']     = $this->main_mod->msrwhere('education_type', null, 'SEQ', 'asc')->result();
		$data['user_email']   = $this->main_mod->msrwhere('contacts', array('user_id' => $id, 'contact_type like "%_email%"' => null, 'status' => 1), 'id', 'asc')->result();

		$data['user_faip'] = $faip;

		// Get data FAIP utama
		$faipMain_list = array('user_faip_11', 'user_faip_12', 'user_faip_111', 'user_faip_112', 'user_faip_113');
		foreach ($faipMain_list as $tblname) {
			$data[$tblname] = $this->main_mod->msrwhere(
				$tblname,
				array('faip_id' => $id_faip, 'status' => 1),
				'id',
				'asc'
			)->result();
		}

		// FAIP Kompetensi
		$faipWithCompetency_list = array(
			'user_faip_13',
			'user_faip_14',
			'user_faip_15',
			'user_faip_16',
			'user_faip_21',
			'user_faip_22',
			'user_faip_3',
			'user_faip_4',
			'user_faip_51',
			'user_faip_52',
			'user_faip_53',
			'user_faip_54',
			'user_faip_6'
		);
		foreach ($faipWithCompetency_list as $tblname) {
			$data[$tblname] = $this->faip_model->get_all_faip_comp($id_faip, $tblname);
		}

		// FAIP Lampiran
		$data['user_faip_lam'] = $this->main_mod->msrwhere('user_faip_lam', array('faip_id' => $id_faip, 'status' => 1), 'id', 'asc')->result();

		// Data Bakuan Penilaian
		$faipNumWithPenilaian_list = array('12', '13', '14', '15', '16', '3', '4', '51', '52', '53', '54', '6');
		foreach ($faipNumWithPenilaian_list as $faip_num) {
			$data['bp_' . $faip_num] = $this->main_mod->msrwhere('m_bakuan_penilaian', array('faip_num' => $faip_num), 'id', 'asc')->result();
		}

		$data['hwb1']       = $this->input->post('hwb1') <> null ? $this->input->post('hwb1') : "";
		$data['hwb2']       = $this->input->post('hwb2') <> null ? $this->input->post('hwb2') : "";
		$data['hwb3']       = $this->input->post('hwb3') <> null ? $this->input->post('hwb3') : "";
		$data['hwb4']       = $this->input->post('hwb4') <> null ? $this->input->post('hwb4') : "";
		$data['hpil']       = $this->input->post('hpil') <> null ? $this->input->post('hpil') : "";
		$data['hjml']       = $this->input->post('hjml') <> null ? $this->input->post('hjml') : "";
		$data['hkeputusan'] = $this->input->post('hkeputusan') <> null ? $this->input->post('hkeputusan') : "";

		foreach ($faipNumWithPenilaian_list as $faip_num) {
			$data['asesor_' . $faip_num] = $this->faip_model->get_all_faip_comp_asesor($id_faip, $asesor_id, $faip_num);
		}

		$this->form_validation->set_rules('hwb1', 'hwb1', 'trim|xss_clean');

		// Dua variable ini untuk apa? Gak dipake
		$is_submit = $this->input->post('submitfaip') <> null ? $this->input->post('submitfaip') : "";
		$submit = $this->input->post('submit') <> null ? $this->input->post('submit') : "";

		$data['asesor'] = $this->main_mod->msrwhere('user_profiles', array('user_id' => $asesor_id), 'id', 'desc')->result();

		$this->load->view('admin/faip_edit2', $data);
		return;
	}

	public function edit_score()
	{
		$id_faip = $this->input->post('id') <> null ? $this->input->post('id') : "";

		if ($id_faip != "") {
			$this->load->model('main_mod');
			try {
				$row = array(
					'faip_id' => $id_faip,
					'wajib1_score' => $this->input->post('hwb1') <> null ? $this->input->post('hwb1') : "",
					'wajib2_score' => $this->input->post('hwb2') <> null ? $this->input->post('hwb2') : "",
					'wajib3_score' => $this->input->post('hwb3') <> null ? $this->input->post('hwb3') : "",
					'wajib4_score' => $this->input->post('hwb4') <> null ? $this->input->post('hwb4') : "",
					'pilihan_score' => $this->input->post('hpil') <> null ? $this->input->post('hpil') : "",
					'total_score' => $this->input->post('hjml') <> null ? $this->input->post('hjml') : "",
					'keputusan' => $this->input->post('hkeputusan') <> null ? $this->input->post('hkeputusan') : "",
					'tgl_keputusan' => date("Y-m-d"),
					'createdby' => $this->session->userdata('admin_id'),
					'modifiedby' => $this->session->userdata('admin_id'),
					'modifieddate' => date("Y-m-d H:i:s"),
					'status' => "2"
				);
				$where = array(
					'faip_id' => $id_faip,
					'createdby' => $this->session->userdata('admin_id')
				);

				$check = $this->main_mod->msrwhere('asesor_faip', $where, 'id', 'desc')->result();
				if (isset($check[0])) {
					$update = $this->main_mod->update('asesor_faip', $where, $row);
					$asesor_faip_id = $check[0]->id;
				} else
					$asesor_faip_id = $this->main_mod->insert('asesor_faip', $row);

				echo 'valid';
			} catch (Exception $e) {
				//print_r($e);
				//return false;
				echo 'not valid';
			}
		}
	}

	public function copyfaip()
	{
		$this->copyfaip_orig();
	}

	/**
	 * Copy FAIP score by Asesor/Majelis
	 */
	public function copyfaip_orig()
	{
		$id_faip = $this->input->post('faip_id') <> null ? $this->input->post('faip_id') : "";
		$majelis = $this->input->post('majelis') <> null ? $this->input->post('majelis') : "";

		// Asesor yang melakukan action ini
		$asesor_user_id = $this->session->userdata('admin_id');

		if ($id_faip != "") {
			$this->load->model('main_mod');
			try {

				// Check data FAIP existing memiliki asesor yang dimaksud (user login)
				$where = array(
					'id' => $id_faip,
					'(majelis1 = ' . $asesor_user_id . ' or majelis2 = ' . $asesor_user_id . ' or majelis3 = ' . $asesor_user_id . ')' => null,
					'(status_faip>=6 and status_faip<=8)' => null
				);
				$check = $this->main_mod->msrwhere('user_faip', $where, 'id', 'desc')->result();


				if (isset($check[0])) {
					$where2 = array(
						'createdby' => $majelis,
						'faip_id' => $id_faip
					);
					$asesor_faip = $this->main_mod->msrwhere('asesor_faip', $where2, 'id', 'desc')->result();

					if (isset($check[0])) {

						//INSERT ASESOR FAIP
						$where4 = array(
							'faip_id' => $id_faip,
							'createdby' => $this->session->userdata('admin_id')
						);
						$check4 = $this->main_mod->msrwhere('asesor_faip', $where4, 'id', 'desc')->result();

						$asesor_faip_id = '';
						$row = array(
							'faip_id' => $id_faip,
							'wajib1_score' => $asesor_faip[0]->wajib1_score,
							'wajib2_score' => $asesor_faip[0]->wajib2_score,
							'wajib3_score' => $asesor_faip[0]->wajib3_score,
							'wajib4_score' => $asesor_faip[0]->wajib4_score,
							'pilihan_score' => $asesor_faip[0]->pilihan_score,
							'total_score' => $asesor_faip[0]->total_score,
							'keputusan' => $asesor_faip[0]->keputusan,
							'tgl_keputusan' => date("Y-m-d"),
							'createdby' => $this->session->userdata('admin_id'),
							'modifiedby' => $this->session->userdata('admin_id'),
							'modifieddate' => date("Y-m-d H:i:s"),
							'status' => "2"
						);

						if (isset($check4[0])) {
							$update = $this->main_mod->update('asesor_faip', $where4, $row);
							$asesor_faip_id = $check4[0]->id;
						} else
							$asesor_faip_id = $this->main_mod->insert('asesor_faip', $row);



						$where3 = array(
							'asesor_faip_id' => $asesor_faip[0]->id,
							'faip_id' => $id_faip
						);
						$asesor_score = $this->main_mod->msrwhere('asesor_score', $where3, 'id', 'desc')->result();
						//print_r($asesor_faip_id);
						//INSERT ASESOR SCORE
						$this->main_mod->delete_where('asesor_score', array("asesor_faip_id" => $asesor_faip_id));
						foreach ($asesor_score as $val) {
							$row = array(
								'asesor_faip_id' => $asesor_faip_id,
								'faip_id' => $val->faip_id,
								'faip_num' => $val->faip_num,
								'user_faip_id' => $val->user_faip_id,
								'kompetensi' => $val->kompetensi,
								'p' => $val->p,
								'q' => $val->q,
								'r' => $val->r,
								't' => $val->t,
								'notes' => $val->notes,
								'status' => $val->status,
								'is_add_by_majelis' => $val->is_add_by_majelis,
								'createdby' => $this->session->userdata('admin_id'),
							);
							$this->main_mod->insert('asesor_score', $row);
						}

						echo 'valid';
					}
				} else
					echo 'not valid';
			} catch (Throwable $t) {
				//print_r($e);
				//return false;
				echo 'not valid' + $t->getMessage();
			}
		}
	}

	/**
	 * ER: Sepertinya function ini gak dipakai
	 */
	public function copyfaip_user()
	{
		$akses = array("0");
		if (!in_array($this->session->userdata('type'), $akses)) {
			redirect('admin/dashboard');
			exit;
		}

		$id_faip = $this->input->get('faip_id') <> null ? $this->input->get('faip_id') : "";

		if ($id_faip != "") {
			$this->load->model('main_mod');
			try {
				$where = array(
					'id' => $id_faip,
				);
				$check = $this->main_mod->msrwhere('user_faip', $where, 'id', 'desc')->result();
				if (isset($check[0])) {
					$new_faip_id = 0;
					$select = $this->main_mod->msrquery('select * from user_faip where id = ' . $id_faip);
					if ($select->num_rows()) {
						//FAIP
						foreach ($select->result_array() as $row) {
							unset($row['id']);
							unset($row['status_faip']);
							unset($row['interview_date']);
							unset($row['interview_start_hour']);
							unset($row['interview_end_hour']);
							unset($row['interview_loc']);
							unset($row['majelis1']);
							unset($row['majelis2']);
							unset($row['majelis3']);
							unset($row['remarks']);
							unset($row['need_revisi']);

							$row['status_faip'] = 0;
							$row['createdby'] = $this->session->userdata('admin_id');
							$row['createddate'] = date('Y-m-d H:i:s');
							$row['modifieddate'] = date('Y-m-d H:i:s');
							$row['modifiedby'] = $this->session->userdata('admin_id');

							$this->db->insert('user_faip', $row);
							$new_faip_id = $this->db->insert_id();
							echo $new_faip_id;
						}

						$select = $this->main_mod->msrquery('select * from user_faip_11 where faip_id = ' . $id_faip);
						if ($select->num_rows()) {
							//FAIP 11
							foreach ($select->result_array() as $row) {
								unset($row['id']);
								unset($row['faip_id']);

								$row['faip_id'] = $new_faip_id;
								$row['createdby'] = $this->session->userdata('admin_id');
								$row['createddate'] = date('Y-m-d H:i:s');
								$row['modifieddate'] = date('Y-m-d H:i:s');
								$row['modifiedby'] = $this->session->userdata('admin_id');

								$this->db->insert('user_faip_11', $row);
							}
						}

						$select = $this->main_mod->msrquery('select * from user_faip_111 where faip_id = ' . $id_faip);
						if ($select->num_rows()) {
							//FAIP 111
							foreach ($select->result_array() as $row) {
								unset($row['id']);
								unset($row['faip_id']);

								$row['faip_id'] = $new_faip_id;
								$row['createdby'] = $this->session->userdata('admin_id');
								$row['createddate'] = date('Y-m-d H:i:s');
								$row['modifieddate'] = date('Y-m-d H:i:s');
								$row['modifiedby'] = $this->session->userdata('admin_id');

								$this->db->insert('user_faip_111', $row);
							}
						}

						$select = $this->main_mod->msrquery('select * from user_faip_112 where faip_id = ' . $id_faip);
						if ($select->num_rows()) {
							//FAIP 112
							foreach ($select->result_array() as $row) {
								unset($row['id']);
								unset($row['faip_id']);

								$row['faip_id'] = $new_faip_id;
								$row['createdby'] = $this->session->userdata('admin_id');
								$row['createddate'] = date('Y-m-d H:i:s');
								$row['modifieddate'] = date('Y-m-d H:i:s');
								$row['modifiedby'] = $this->session->userdata('admin_id');

								$this->db->insert('user_faip_112', $row);
							}
						}

						$select = $this->main_mod->msrquery('select * from user_faip_113 where faip_id = ' . $id_faip);
						if ($select->num_rows()) {
							//FAIP 113
							foreach ($select->result_array() as $row) {
								unset($row['id']);
								unset($row['faip_id']);

								$row['faip_id'] = $new_faip_id;
								$row['createdby'] = $this->session->userdata('admin_id');
								$row['createddate'] = date('Y-m-d H:i:s');
								$row['modifieddate'] = date('Y-m-d H:i:s');
								$row['modifiedby'] = $this->session->userdata('admin_id');

								$this->db->insert('user_faip_113', $row);
							}
						}

						$select = $this->main_mod->msrquery('select * from user_faip_lam where faip_id = ' . $id_faip);
						if ($select->num_rows()) {
							//FAIP LAm
							foreach ($select->result_array() as $row) {
								unset($row['id']);
								unset($row['faip_id']);

								$row['faip_id'] = $new_faip_id;
								$row['createdby'] = $this->session->userdata('admin_id');
								$row['createddate'] = date('Y-m-d H:i:s');
								$row['modifieddate'] = date('Y-m-d H:i:s');
								$row['modifiedby'] = $this->session->userdata('admin_id');

								$this->db->insert('user_faip_lam', $row);
							}
						}

						$select = $this->main_mod->msrquery('select * from user_faip_12 where faip_id = ' . $id_faip);
						if ($select->num_rows()) {
							//FAIP 12
							foreach ($select->result_array() as $row) {
								unset($row['id']);
								unset($row['faip_id']);

								$row['faip_id'] = $new_faip_id;
								$row['createdby'] = $this->session->userdata('admin_id');
								$row['createddate'] = date('Y-m-d H:i:s');
								$row['modifieddate'] = date('Y-m-d H:i:s');
								$row['modifiedby'] = $this->session->userdata('admin_id');

								$this->db->insert('user_faip_12', $row);
							}
						}

						$select = $this->main_mod->msrquery('select * from user_faip_21 where faip_id = ' . $id_faip);
						if ($select->num_rows()) {
							//FAIP 21
							foreach ($select->result_array() as $row) {
								unset($row['id']);
								unset($row['faip_id']);

								$row['faip_id'] = $new_faip_id;
								$row['createdby'] = $this->session->userdata('admin_id');
								$row['createddate'] = date('Y-m-d H:i:s');
								$row['modifieddate'] = date('Y-m-d H:i:s');
								$row['modifiedby'] = $this->session->userdata('admin_id');

								$this->db->insert('user_faip_21', $row);
							}
						}

						$select = $this->main_mod->msrquery('select * from user_faip_22 where faip_id = ' . $id_faip);
						if ($select->num_rows()) {
							//FAIP 22
							foreach ($select->result_array() as $row) {
								unset($row['id']);
								unset($row['faip_id']);

								$row['faip_id'] = $new_faip_id;
								$row['createdby'] = $this->session->userdata('admin_id');
								$row['createddate'] = date('Y-m-d H:i:s');
								$row['modifieddate'] = date('Y-m-d H:i:s');
								$row['modifiedby'] = $this->session->userdata('admin_id');

								$this->db->insert('user_faip_22', $row);
							}
						}

						$order_id = 0;
						$parent = 0;

						$select = $this->main_mod->msrquery('select * from user_faip_13 where faip_id = ' . $id_faip . ' order by id asc');
						if ($select->num_rows()) {
							//FAIP 13
							foreach ($select->result_array() as $row) {
								$is_parent = false;
								if ($order_id == $row['parent']) {
									$parent = 0;
									$is_parent = true;
								}

								unset($row['id']);
								unset($row['faip_id']);

								$row['faip_id'] = $new_faip_id;
								$row['parent'] = $parent;

								$row['createdby'] = $this->session->userdata('admin_id');
								$row['createddate'] = date('Y-m-d H:i:s');
								$row['modifieddate'] = date('Y-m-d H:i:s');
								$row['modifiedby'] = $this->session->userdata('admin_id');

								$this->db->insert('user_faip_13', $row);
								$temp_id = $this->db->insert_id();
								if ($is_parent)
									$parent = $temp_id;
							}
						}

						$order_id = 0;
						$parent = 0;

						$select = $this->main_mod->msrquery('select * from user_faip_14 where faip_id = ' . $id_faip . ' order by id asc');
						if ($select->num_rows()) {
							//FAIP 14
							foreach ($select->result_array() as $row) {
								$is_parent = false;
								if ($order_id == $row['parent']) {
									$parent = 0;
									$is_parent = true;
								}

								unset($row['id']);
								unset($row['faip_id']);

								$row['faip_id'] = $new_faip_id;
								$row['parent'] = $parent;

								$row['createdby'] = $this->session->userdata('admin_id');
								$row['createddate'] = date('Y-m-d H:i:s');
								$row['modifieddate'] = date('Y-m-d H:i:s');
								$row['modifiedby'] = $this->session->userdata('admin_id');

								$this->db->insert('user_faip_14', $row);
								$temp_id = $this->db->insert_id();
								if ($is_parent)
									$parent = $temp_id;
							}
						}

						$order_id = 0;
						$parent = 0;

						$select = $this->main_mod->msrquery('select * from user_faip_15 where faip_id = ' . $id_faip . ' order by id asc');
						if ($select->num_rows()) {
							//FAIP 15
							foreach ($select->result_array() as $row) {
								$is_parent = false;
								if ($order_id == $row['parent']) {
									$parent = 0;
									$is_parent = true;
								}

								unset($row['id']);
								unset($row['faip_id']);

								$row['faip_id'] = $new_faip_id;
								$row['parent'] = $parent;

								$row['createdby'] = $this->session->userdata('admin_id');
								$row['createddate'] = date('Y-m-d H:i:s');
								$row['modifieddate'] = date('Y-m-d H:i:s');
								$row['modifiedby'] = $this->session->userdata('admin_id');

								$this->db->insert('user_faip_15', $row);
								$temp_id = $this->db->insert_id();
								if ($is_parent)
									$parent = $temp_id;
							}
						}

						$order_id = 0;
						$parent = 0;

						$select = $this->main_mod->msrquery('select * from user_faip_16 where faip_id = ' . $id_faip . ' order by id asc');
						if ($select->num_rows()) {
							//FAIP 16
							foreach ($select->result_array() as $row) {
								$is_parent = false;
								if ($order_id == $row['parent']) {
									$parent = 0;
									$is_parent = true;
								}

								unset($row['id']);
								unset($row['faip_id']);

								$row['faip_id'] = $new_faip_id;
								$row['parent'] = $parent;

								$row['createdby'] = $this->session->userdata('admin_id');
								$row['createddate'] = date('Y-m-d H:i:s');
								$row['modifieddate'] = date('Y-m-d H:i:s');
								$row['modifiedby'] = $this->session->userdata('admin_id');

								$this->db->insert('user_faip_16', $row);
								$temp_id = $this->db->insert_id();
								if ($is_parent)
									$parent = $temp_id;
							}
						}

						$order_id = 0;
						$parent = 0;

						$select = $this->main_mod->msrquery('select * from user_faip_3 where faip_id = ' . $id_faip . ' order by id asc');
						if ($select->num_rows()) {
							//FAIP 3
							foreach ($select->result_array() as $row) {
								$is_parent = false;
								if ($order_id == $row['parent']) {
									$parent = 0;
									$is_parent = true;
								}

								unset($row['id']);
								unset($row['faip_id']);

								$row['faip_id'] = $new_faip_id;
								$row['parent'] = $parent;

								$row['createdby'] = $this->session->userdata('admin_id');
								$row['createddate'] = date('Y-m-d H:i:s');
								$row['modifieddate'] = date('Y-m-d H:i:s');
								$row['modifiedby'] = $this->session->userdata('admin_id');

								$this->db->insert('user_faip_3', $row);
								$temp_id = $this->db->insert_id();
								if ($is_parent)
									$parent = $temp_id;
							}
						}

						$order_id = 0;
						$parent = 0;

						$select = $this->main_mod->msrquery('select * from user_faip_4 where faip_id = ' . $id_faip . ' order by id asc');
						if ($select->num_rows()) {
							//FAIP 4
							foreach ($select->result_array() as $row) {
								$is_parent = false;
								if ($order_id == $row['parent']) {
									$parent = 0;
									$is_parent = true;
								}

								unset($row['id']);
								unset($row['faip_id']);

								$row['faip_id'] = $new_faip_id;
								$row['parent'] = $parent;

								$row['createdby'] = $this->session->userdata('admin_id');
								$row['createddate'] = date('Y-m-d H:i:s');
								$row['modifieddate'] = date('Y-m-d H:i:s');
								$row['modifiedby'] = $this->session->userdata('admin_id');

								$this->db->insert('user_faip_4', $row);
								$temp_id = $this->db->insert_id();
								if ($is_parent)
									$parent = $temp_id;
							}
						}

						$order_id = 0;
						$parent = 0;

						$select = $this->main_mod->msrquery('select * from user_faip_51 where faip_id = ' . $id_faip . ' order by id asc');
						if ($select->num_rows()) {
							//FAIP 51
							foreach ($select->result_array() as $row) {
								$is_parent = false;
								if ($order_id == $row['parent']) {
									$parent = 0;
									$is_parent = true;
								}

								unset($row['id']);
								unset($row['faip_id']);

								$row['faip_id'] = $new_faip_id;
								$row['parent'] = $parent;

								$row['createdby'] = $this->session->userdata('admin_id');
								$row['createddate'] = date('Y-m-d H:i:s');
								$row['modifieddate'] = date('Y-m-d H:i:s');
								$row['modifiedby'] = $this->session->userdata('admin_id');

								$this->db->insert('user_faip_51', $row);
								$temp_id = $this->db->insert_id();
								if ($is_parent)
									$parent = $temp_id;
							}
						}

						$order_id = 0;
						$parent = 0;

						$select = $this->main_mod->msrquery('select * from user_faip_52 where faip_id = ' . $id_faip . ' order by id asc');
						if ($select->num_rows()) {
							//FAIP 52
							foreach ($select->result_array() as $row) {
								$is_parent = false;
								if ($order_id == $row['parent']) {
									$parent = 0;
									$is_parent = true;
								}

								unset($row['id']);
								unset($row['faip_id']);

								$row['faip_id'] = $new_faip_id;
								$row['parent'] = $parent;

								$row['createdby'] = $this->session->userdata('admin_id');
								$row['createddate'] = date('Y-m-d H:i:s');
								$row['modifieddate'] = date('Y-m-d H:i:s');
								$row['modifiedby'] = $this->session->userdata('admin_id');

								$this->db->insert('user_faip_52', $row);
								$temp_id = $this->db->insert_id();
								if ($is_parent)
									$parent = $temp_id;
							}
						}

						$order_id = 0;
						$parent = 0;

						$select = $this->main_mod->msrquery('select * from user_faip_53 where faip_id = ' . $id_faip . ' order by id asc');
						if ($select->num_rows()) {
							//FAIP 53
							foreach ($select->result_array() as $row) {
								$is_parent = false;
								if ($order_id == $row['parent']) {
									$parent = 0;
									$is_parent = true;
								}

								unset($row['id']);
								unset($row['faip_id']);

								$row['faip_id'] = $new_faip_id;
								$row['parent'] = $parent;

								$row['createdby'] = $this->session->userdata('admin_id');
								$row['createddate'] = date('Y-m-d H:i:s');
								$row['modifieddate'] = date('Y-m-d H:i:s');
								$row['modifiedby'] = $this->session->userdata('admin_id');

								$this->db->insert('user_faip_53', $row);
								$temp_id = $this->db->insert_id();
								if ($is_parent)
									$parent = $temp_id;
							}
						}

						$order_id = 0;
						$parent = 0;

						$select = $this->main_mod->msrquery('select * from user_faip_54 where faip_id = ' . $id_faip . ' order by id asc');
						if ($select->num_rows()) {
							//FAIP 54
							foreach ($select->result_array() as $row) {
								$is_parent = false;
								if ($order_id == $row['parent']) {
									$parent = 0;
									$is_parent = true;
								}

								unset($row['id']);
								unset($row['faip_id']);

								$row['faip_id'] = $new_faip_id;
								$row['parent'] = $parent;

								$row['createdby'] = $this->session->userdata('admin_id');
								$row['createddate'] = date('Y-m-d H:i:s');
								$row['modifieddate'] = date('Y-m-d H:i:s');
								$row['modifiedby'] = $this->session->userdata('admin_id');

								$this->db->insert('user_faip_54', $row);
								$temp_id = $this->db->insert_id();
								if ($is_parent)
									$parent = $temp_id;
							}
						}

						$order_id = 0;
						$parent = 0;

						$select = $this->main_mod->msrquery('select * from user_faip_6 where faip_id = ' . $id_faip . ' order by id asc');
						if ($select->num_rows()) {
							//FAIP 6
							foreach ($select->result_array() as $row) {
								$is_parent = false;
								if ($order_id == $row['parent']) {
									$parent = 0;
									$is_parent = true;
								}

								unset($row['id']);
								unset($row['faip_id']);

								$row['faip_id'] = $new_faip_id;
								$row['parent'] = $parent;

								$row['createdby'] = $this->session->userdata('admin_id');
								$row['createddate'] = date('Y-m-d H:i:s');
								$row['modifieddate'] = date('Y-m-d H:i:s');
								$row['modifiedby'] = $this->session->userdata('admin_id');

								$this->db->insert('user_faip_6', $row);
								$temp_id = $this->db->insert_id();
								if ($is_parent)
									$parent = $temp_id;
							}
						}
					}
				} else
					echo 'not valid';
			} catch (Exception $e) {
				echo 'not valid';
			}
		}
	}

	public function majelis()
	{
		$akses = array("0", "1", "11");
		if (!in_array($this->session->userdata('type'), $akses)) {
			redirect('admin/dashboard');
			exit;
		}

		$data['title'] = ''; //SITE_NAME.': Manage Members';
		$data['msg'] = '';

		$data["m_cab"] = $this->members_model->get_all_cabang();
		$data["m_bk"] = $this->members_model->get_all_bk();
		$data["m_title"] = $this->members_model->get_all_typemajelis();



		//Pagination starts
		$total_rows = $this->members_model->record_count_majelis('admin');
		$config = pagination_configuration(base_url("admin/members/majelis"), $total_rows, 10, 4, 5, true);

		$this->pagination->initialize($config);
		$page = ($this->uri->segment(3)) ? $this->uri->segment(4) : 0;
		$page_num = $page - 1;
		$page_num = ($page_num < 0) ? '0' : $page_num;
		$page = $page_num * $config["per_page"];
		$data["links"] = $this->pagination->create_links();
		//Pagination ends
		//print_r($data["links"]);

		$obj_result = $this->members_model->get_all_majelis($config["per_page"], $page);
		$data['result'] = $obj_result;
		$data["total_rows"] = $total_rows;

		//$data["m_cab"] = $this->members_model->get_all_cabang();
		//$data["m_bk"] = $this->members_model->get_all_bk();


		$this->load->view('admin/majelis_view', $data);
		return;
	}

	public function search_majelis()
	{
		$akses = array("0", "1", "11");
		if (!in_array($this->session->userdata('type'), $akses)) {
			redirect('admin/dashboard');
			exit;
		}

		$data['title'] = ''; //SITE_NAME.': Manage Employers';
		$data['msg'] = '';
		$search_name = '';
		$search_majelis = '';

		$this->form_validation->set_rules('firstname', 'firstname', 'trim');
		$this->form_validation->set_rules('type', 'type', 'trim');
		$this->form_validation->run();

		if ($_GET) {
			$search_name 	= 	$this->input->get('firstname');
			$search_majelis 	= 	$this->input->get('type');
		}
		if ($search_name == '' && $search_majelis == '') {
			redirect(base_url('admin/members/majelis'));
			return;
		}
		$new_array = array();
		$search_data = array();
		$search_data2 = array();
		if (isset($search_name) && $search_name != '') {
			$search_data['firstname'] = $search_name;
			//$search_data2['lower(add_name)']=strtolower($search_name);
			$search_data2['REPLACE(lower(name)," ","")'] = str_replace(' ', '', strtolower($search_name));
		}
		if (isset($search_majelis) && $search_majelis != '') {
			$search_data['type'] = $search_majelis;
			$search_data2['m_title.desc'] = ltrim($search_majelis, '0');
		}

		$wild_card = '';

		$url_params = implode(
			'&amp;',
			array_map(
				function ($key, $val) {
					return urlencode($key) . '=' . urlencode($val);
				},
				array_keys($search_data),
				$search_data
			)
		);
		//Pagination starts
		$total_rows = $this->members_model->search_record_count_majelis('users', $search_data2);
		$config = pagination_configuration_search(base_url("admin/members/search_majelis/?" . $url_params), $total_rows, 10, 3, 5, true);

		$this->pagination->initialize($config);
		$page = $this->input->get('per_page');
		$page_num = $page - 1;
		$page_num = ($page_num < 0) ? '0' : $page_num;
		$page = $page_num * $config["per_page"];
		$data["links"] = $this->pagination->create_links();
		$data["total_rows"] = $total_rows;
		//Pagination ends
		$obj_result = $this->members_model->search_all_majelis($config["per_page"], $page, $search_data2, $wild_card);
		$data['result'] = $obj_result;
		$data['search_data'] = $search_data;

		$this->load->view('admin/majelis_view', $data);
		return;
	}

	function setmajelis()
	{
		$akses = array("0", "11");
		if (!in_array($this->session->userdata('type'), $akses)) {
			redirect('admin/dashboard');
			exit;
		}

		$faip_id = $this->input->post('id') <> null ? $this->input->post('id') : "";
		//$cabang = $this->input->post('cabang')<>null?$this->input->post('cabang'):"";
		//$bk = $this->input->post('bk')<>null?$this->input->post('bk'):"";
		//$type = $this->input->post('type')<>null?$this->input->post('type'):"";
		$username = $this->input->post('username') <> null ? $this->input->post('username') : "";
		$password = $this->input->post('password') <> null ? $this->input->post('password') : "";
		$name = $this->input->post('name') <> null ? $this->input->post('name') : "";

		$type = 7;

		/*if($faip_id==''){
			redirect('admin/members');
			exit;
		}*/
		$this->load->model('main_mod');
		//if($faip_id!=''){
		try {

			$checkEmail = $this->main_mod->msrwhere('admin', array('id<>' => $faip_id, 'admin_username' => $username), 'id', 'desc')->result();

			if (isset($checkEmail[0])) {
				echo "not valid email";
			} else {

				$this->ci = &get_instance();
				$this->ci->load->config('tank_auth', TRUE);


				$hasher = new PasswordHash(
					$this->ci->config->item('phpass_hash_strength', 'tank_auth'),
					$this->ci->config->item('phpass_hash_portable', 'tank_auth')
				);
				$hashed_password = $hasher->HashPassword($password);

				$rowInsert = array(
					'id' => $faip_id,
					//'code_wilayah' => $cabang,
					'code_bk_hkk' => $this->session->userdata('code_bk_hkk'),
					'type' => $type,
					'name' => $name,
					'admin_username' => $username,
					'admin_password' => $hashed_password,
					'createdby' => $this->session->userdata('admin_id'),
				);

				$check = $this->main_mod->msrwhere('admin', array('id' => $faip_id), 'id', 'desc')->result();
				if (isset($check[0])) {
					$where = array(
						"id" => $faip_id
					);
					$row = array();
					if ($password != "") {
						$row = array(
							//'code_wilayah' => $cabang,
							'code_bk_hkk' => $this->session->userdata('code_bk_hkk'),
							'type' => $type,
							'name' => $name,
							'admin_username' => $username,
							'admin_password' => $hashed_password,
							'modifieddate' => date('Y-m-d H:i:s'),
							'modifiedby' => $this->session->userdata('admin_id'),
						);
					} else {
						$row = array(
							//'code_wilayah' => $cabang,
							'code_bk_hkk' => $this->session->userdata('code_bk_hkk'),
							'type' => $type,
							'name' => $name,
							'admin_username' => $username,
							'modifieddate' => date('Y-m-d H:i:s'),
							'modifiedby' => $this->session->userdata('admin_id'),
						);
					}
					$update = $this->main_mod->update('admin', $where, $row);
				} else {
					$this->main_mod->insert('admin', $rowInsert);
				}

				echo "valid";
			}
		} catch (Exception $e) {
			//print_r($e);
			echo "not valid";
		}
		/*}
		else
			echo "not valid";*/
	}

	function getmajelis()
	{
		$id = $this->input->get('id') <> null ? $this->input->get('id') : "";

		$majelis = $this->members_model->get_majelis($id);
		$array =  (array) $majelis;
		print_r(json_encode($array));
	}


	function download_faip()
	{
		$akses = array("0", "1", "7", "10", "11");
		if (!in_array($this->session->userdata('type'), $akses)) {
			redirect('admin/dashboard');
			exit;
		}

		$this->load->model('main_mod');
		$id_faip = $this->uri->segment(4);
		$this->load->model('faip_model');
		$faip = $this->faip_model->get_faip_by_id($id_faip);
		$data['id_faip'] = $id_faip;
		$id = isset($faip->user_id) ? $faip->user_id : "";

		$obj_row = $this->members_model->get_member_by_id($id);
		$data['row'] = $obj_row;
		$data['kta'] = $this->members_model->get_kta_data_by_personid($id);

		$data['user_faip'] = $faip;
		$data['user_faip_11'] = $this->main_mod->msrwhere('user_faip_11', array('faip_id' => $id_faip, 'status' => 1), 'id', 'asc')->result();
		$data['user_faip_12'] = $this->main_mod->msrwhere('user_faip_12', array('faip_id' => $id_faip, 'status' => 1), 'id', 'asc')->result();
		$data['user_faip_111'] = $this->main_mod->msrwhere('user_faip_111', array('faip_id' => $id_faip, 'status' => 1), 'id', 'asc')->result();
		$data['user_faip_112'] = $this->main_mod->msrwhere('user_faip_112', array('faip_id' => $id_faip, 'status' => 1), 'id', 'asc')->result();
		$data['user_faip_113'] = $this->main_mod->msrwhere('user_faip_113', array('faip_id' => $id_faip, 'status' => 1), 'id', 'asc')->result();
		$data['user_faip_13'] = $this->faip_model->get_all_faip_comp($id_faip, 'user_faip_13');
		$data['user_faip_14'] = $this->faip_model->get_all_faip_comp($id_faip, 'user_faip_14');
		$data['user_faip_15'] = $this->faip_model->get_all_faip_comp($id_faip, 'user_faip_15');
		$data['user_faip_16'] = $this->faip_model->get_all_faip_comp($id_faip, 'user_faip_16');
		$data['user_faip_21'] = $this->faip_model->get_all_faip_comp($id_faip, 'user_faip_21');
		$data['user_faip_22'] = $this->faip_model->get_all_faip_comp($id_faip, 'user_faip_22');
		$data['user_faip_3'] = $this->faip_model->get_all_faip_comp($id_faip, 'user_faip_3');
		$data['user_faip_4'] = $this->faip_model->get_all_faip_comp($id_faip, 'user_faip_4');
		$data['user_faip_51'] = $this->faip_model->get_all_faip_comp($id_faip, 'user_faip_51');
		$data['user_faip_52'] = $this->faip_model->get_all_faip_comp($id_faip, 'user_faip_52');
		$data['user_faip_53'] = $this->faip_model->get_all_faip_comp($id_faip, 'user_faip_53');
		$data['user_faip_54'] = $this->faip_model->get_all_faip_comp($id_faip, 'user_faip_54');
		$data['user_faip_6'] = $this->faip_model->get_all_faip_comp($id_faip, 'user_faip_6');
		$data['user_faip_lam'] = $this->main_mod->msrwhere('user_faip_lam', array('faip_id' => $id_faip, 'status' => 1), 'id', 'asc')->result();

		//print_r($data['user_faip_13']);

		$data['m_komp'] = $this->main_mod->msrquery('select value,title from no_kompetensi a join m_kompetensi b on a.value=b.code')->result();
		$data['m_act_13'] = $this->main_mod->msrquery('select code as value,title from m_kompetensi where code like "W.1%" order by id asc')->result();
		$data['m_act_15'] = $this->main_mod->msrquery('select code as value,title from m_kompetensi where code like "W.2%" or code like "W.4%" or code like "P.10%" order by id asc')->result();
		$data['m_act_16'] = $this->main_mod->msrquery('select code as value,title from m_kompetensi where code like "W.1%" or code like "W.4%" or code like "P.10%" order by id asc')->result();

		$data['m_act_3'] = $this->main_mod->msrquery('select code as value,title from m_kompetensi where
		code like "W.2%" or
		code like "W.3%" or
		code like "W.4%" or
		code like "P.6%" or
		code like "P.7%" or
		code like "P.8%" or
		code like "P.9%" or
		code like "P.10%" or
		code like "P.11%"
		order by id asc')->result();
		$data['m_act_4'] = $this->main_mod->msrquery('select code as value,title from m_kompetensi where code like "P.5%" order by id asc')->result();
		$data['m_act_51'] = $this->main_mod->msrquery('select code as value,title from m_kompetensi where code like "W.4%" order by id asc')->result();
		$data['m_act_53'] = $this->main_mod->msrquery('select code as value,title from m_kompetensi where code like "W.2%" order by id asc')->result();
		$data['m_act_54'] = $this->main_mod->msrquery('select code as value,title from m_kompetensi where code like "P.6%" order by id asc')->result();


		//$data['m_degree']=$this->main_mod->msrwhere('education_type',array('HAS_TABLE'=>'Y'),'SEQ','asc')->result();
		$data['m_degree'] = $this->main_mod->msrquery('select * from education_type where HAS_TABLE="Y" or seq=9 order by SEQ asc')->result();

		$data['m_bk'] = $this->main_mod->msrwhere('m_bk', array('faip' => 1), 'id', 'asc')->result();

		$data['user_address'] = $this->main_mod->msrwhere('user_address', array('user_id' => $id, 'status' => 1), 'id', 'asc')->result();
		$data['user_email'] = $this->main_mod->msrwhere('contacts', array('user_id' => $id, 'contact_type like "%_email%"' => null, 'status' => 1), 'id', 'asc')->result();
		$data['user_phone'] = $this->main_mod->msrwhere('contacts', array('user_id' => $id, 'contact_type like "%_phone%"' => null, 'status' => 1), 'id', 'asc')->result();
		$data['user_edu'] = $this->main_mod->msrwhere('user_edu', array('user_id' => $id, 'status' => 1), 'enddate', 'asc')->result();
		//$data['user_cert']=$this->main_mod->msrwhere('user_cert',array('user_id'=>$id,'status'=>1),'id','asc')->result();
		$data['user_cert'] = $this->main_mod->msrwhere('user_cert', array('user_id' => $id, 'status <> 0' => null), 'id', 'asc')->result();
		$data['user_exp'] = $this->main_mod->msrwhere('user_exp', array('user_id' => $id, 'status' => 1), 'startyear', 'asc')->result();
		$data['user_lembaga'] = $this->main_mod->msrwhere('user_exp', array('user_id' => $id, 'status' => 1, 'is_present' => 1), 'id', 'asc')->result();
		//$data['emailx'] = $this->session->userdata('email');
		$users = $this->main_mod->msrwhere('users', array('id' => $id), 'id', 'asc')->row();
		$data['emailx'] = $users->email;
		//FAIP
		$data['user_org'] = $this->main_mod->msrwhere('user_org', array('user_id' => $id, 'status' => 1), 'id', 'asc')->result();
		$data['user_award'] = $this->main_mod->msrwhere('user_award', array('user_id' => $id, 'status' => 1), 'id', 'asc')->result();

		$data['user_course1'] = $this->main_mod->msrwhere('user_course', array('user_id' => $id, 'status' => 1), 'id', 'asc')->result();

		$data['user_prof'] = $this->main_mod->msrwhere('user_prof', array('user_id' => $id, 'status' => 1), 'id', 'asc')->result();

		$data['user_publication1'] = $this->main_mod->msrwhere('user_publication', array('user_id' => $id, 'status' => 1, "type" => "1"), 'id', 'asc')->result();
		$data['user_publication2'] = $this->main_mod->msrwhere('user_publication', array('user_id' => $id, 'status' => 1, "type" => "2"), 'id', 'asc')->result();
		$data['user_publication3'] = $this->main_mod->msrwhere('user_publication', array('user_id' => $id, 'status' => 1, "type" => "3"), 'id', 'asc')->result();
		$data['user_publication4'] = $this->main_mod->msrwhere('user_publication', array('user_id' => $id, 'status' => 1, "type" => "4"), 'id', 'asc')->result();
		$data['user_skill'] = $this->main_mod->msrwhere('user_skill', array('user_id' => $id, 'status' => 1), 'id', 'asc')->result();;

		// Data Bakuan Penilaian
		$faipNumWithPenilaian_list = array('12', '13', '14', '15', '16', '3', '4', '51', '52', '53', '54', '6');
		foreach ($faipNumWithPenilaian_list as $faip_num) {
			$data['bp_' . $faip_num] = $this->main_mod->msrwhere('m_bakuan_penilaian', array('faip_num' => $faip_num), 'id', 'asc')->result();
		}


		$this->load->view('admin/download_faip', $data);

		//print_r($data);

		/*
		//print_r($user_profiles[0]->photo);
		$this->load->library('Pdf');
		$pdf = new Pdf('L', 'mm', 'A4', true, 'UTF-8', false);

		$pdf->SetProtection(array('copy','modify','annot-forms','fill-forms','extract','assemble','owner'), "", "masterpassword123", 0, null);

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
		//$img_file = FCPATH.'./assets/images/background.jpg';
		//$pdf->Image($img_file, 0, 0, 297, 210, '', '', '', false, 300, '', false, false, 0);

		//

		$html = $this->load->view('admin/download_faip', $data,true);

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
		$pdf->Output($nim.'.pdf', 'D');

		*/
	}

	//------------------------------------------------------------------ Tambahan Request P' Rully --------------------
	function catatan_bk()
	{
		$akses = array("0", "1");
		if (!in_array($this->session->userdata('type'), $akses)) {
			redirect('admin/dashboard');
			exit;
		}
		$this->load->model('main_mod');
		$id_faip = $this->uri->segment(4);
		$this->load->model('faip_model');
		$faip = $this->faip_model->ambil_user_faip_revisi($id_faip);
		$data['datanya_asesor_faip'] = $faip;
		$this->load->view('admin/catatan_bk', $data);
	}
	/*
	function remarksnya()
	{
		$akses = array("0", "1");
		if(!in_array($this->session->userdata('type'),$akses)){
			redirect('admin/dashboard');
			exit;
		}
		$this->load->model('main_mod');
		$id_faip = $this->uri->segment(4);
		$this->load->model('faip_model');
		$faip = $this->faip_model->ambil_user_faip_remarks($id_faip);
		$data['datanya_asesor_faip']= $faip;
		$this->load->view('admin/view_remarks', $data);

	}
*/
	//---------------------------------------------------------------------------------------

	function download_faip_2()
	{
		$akses = array("0", "1", "7", "10", "11");
		if (!in_array($this->session->userdata('type'), $akses)) {
			redirect('admin/dashboard');
			exit;
		}

		$this->load->model('main_mod');
		$id_faip = $this->uri->segment(4);
		$this->load->model('faip_model');
		$faip = $this->faip_model->get_faip_by_id($id_faip);
		$data['id_faip'] = $id_faip;
		$id = isset($faip->user_id) ? $faip->user_id : "";

		$obj_row = $this->members_model->get_member_by_id($id);
		$data['row'] = $obj_row;
		$data['kta'] = $this->members_model->get_kta_data_by_personid($id);

		$data['user_faip'] = $faip;
		$data['user_faip_11'] = $this->main_mod->msrwhere('user_faip_11', array('faip_id' => $id_faip, 'status' => 1), 'id', 'asc')->result();
		$data['user_faip_12'] = $this->main_mod->msrwhere('user_faip_12', array('faip_id' => $id_faip, 'status' => 1), 'id', 'asc')->result();
		$data['user_faip_111'] = $this->main_mod->msrwhere('user_faip_111', array('faip_id' => $id_faip, 'status' => 1), 'id', 'asc')->result();
		$data['user_faip_112'] = $this->main_mod->msrwhere('user_faip_112', array('faip_id' => $id_faip, 'status' => 1), 'id', 'asc')->result();
		$data['user_faip_113'] = $this->main_mod->msrwhere('user_faip_113', array('faip_id' => $id_faip, 'status' => 1), 'id', 'asc')->result();
		$data['user_faip_13'] = $this->faip_model->get_all_faip_comp($id_faip, 'user_faip_13');
		$data['user_faip_14'] = $this->faip_model->get_all_faip_comp($id_faip, 'user_faip_14');
		$data['user_faip_15'] = $this->faip_model->get_all_faip_comp($id_faip, 'user_faip_15');
		$data['user_faip_16'] = $this->faip_model->get_all_faip_comp($id_faip, 'user_faip_16');
		$data['user_faip_21'] = $this->faip_model->get_all_faip_comp($id_faip, 'user_faip_21');
		$data['user_faip_22'] = $this->faip_model->get_all_faip_comp($id_faip, 'user_faip_22');
		$data['user_faip_3'] = $this->faip_model->get_all_faip_comp($id_faip, 'user_faip_3');
		$data['user_faip_4'] = $this->faip_model->get_all_faip_comp($id_faip, 'user_faip_4');
		$data['user_faip_51'] = $this->faip_model->get_all_faip_comp($id_faip, 'user_faip_51');
		$data['user_faip_52'] = $this->faip_model->get_all_faip_comp($id_faip, 'user_faip_52');
		$data['user_faip_53'] = $this->faip_model->get_all_faip_comp($id_faip, 'user_faip_53');
		$data['user_faip_54'] = $this->faip_model->get_all_faip_comp($id_faip, 'user_faip_54');
		$data['user_faip_6'] = $this->faip_model->get_all_faip_comp($id_faip, 'user_faip_6');
		$data['user_faip_lam'] = $this->main_mod->msrwhere('user_faip_lam', array('faip_id' => $id_faip, 'status' => 1), 'id', 'asc')->result();

		//print_r($data['user_faip_13']);

		$data['m_komp'] = $this->main_mod->msrquery('select value,title from no_kompetensi a join m_kompetensi b on a.value=b.code')->result();
		$data['m_act_13'] = $this->main_mod->msrquery('select code as value,title from m_kompetensi where code like "W.1%" order by id asc')->result();
		$data['m_act_15'] = $this->main_mod->msrquery('select code as value,title from m_kompetensi where code like "W.2%" or code like "W.4%" or code like "P.10%" order by id asc')->result();
		$data['m_act_16'] = $this->main_mod->msrquery('select code as value,title from m_kompetensi where code like "W.1%" or code like "W.4%" or code like "P.10%" order by id asc')->result();

		$data['m_act_3'] = $this->main_mod->msrquery('select code as value,title from m_kompetensi where
		code like "W.2%" or
		code like "W.3%" or
		code like "W.4%" or
		code like "P.6%" or
		code like "P.7%" or
		code like "P.8%" or
		code like "P.9%" or
		code like "P.10%" or
		code like "P.11%"
		order by id asc')->result();
		$data['m_act_4'] = $this->main_mod->msrquery('select code as value,title from m_kompetensi where code like "P.5%" order by id asc')->result();
		$data['m_act_51'] = $this->main_mod->msrquery('select code as value,title from m_kompetensi where code like "W.4%" order by id asc')->result();
		$data['m_act_53'] = $this->main_mod->msrquery('select code as value,title from m_kompetensi where code like "W.2%" order by id asc')->result();
		$data['m_act_54'] = $this->main_mod->msrquery('select code as value,title from m_kompetensi where code like "P.6%" order by id asc')->result();


		//$data['m_degree']=$this->main_mod->msrwhere('education_type',array('HAS_TABLE'=>'Y'),'SEQ','asc')->result();
		$data['m_degree'] = $this->main_mod->msrquery('select * from education_type where HAS_TABLE="Y" or seq=9 order by SEQ asc')->result();
		$data['m_bk'] = $this->main_mod->msrwhere('m_bk', array('faip' => 1), 'id', 'asc')->result();

		$data['user_address'] = $this->main_mod->msrwhere('user_address', array('user_id' => $id, 'status' => 1), 'id', 'asc')->result();
		$data['user_email'] = $this->main_mod->msrwhere('contacts', array('user_id' => $id, 'contact_type like "%_email%"' => null, 'status' => 1), 'id', 'asc')->result();
		$data['user_phone'] = $this->main_mod->msrwhere('contacts', array('user_id' => $id, 'contact_type like "%_phone%"' => null, 'status' => 1), 'id', 'asc')->result();
		$data['user_edu'] = $this->main_mod->msrwhere('user_edu', array('user_id' => $id, 'status' => 1), 'enddate', 'asc')->result();
		//$data['user_cert']=$this->main_mod->msrwhere('user_cert',array('user_id'=>$id,'status'=>1),'id','asc')->result();
		$data['user_cert'] = $this->main_mod->msrwhere('user_cert', array('user_id' => $id, 'status <> 0' => null), 'id', 'asc')->result();
		$data['user_exp'] = $this->main_mod->msrwhere('user_exp', array('user_id' => $id, 'status' => 1), 'startyear', 'asc')->result();
		$data['user_lembaga'] = $this->main_mod->msrwhere('user_exp', array('user_id' => $id, 'status' => 1, 'is_present' => 1), 'id', 'asc')->result();
		//$data['emailx'] = $this->session->userdata('email');
		$users = $this->main_mod->msrwhere('users', array('id' => $id), 'id', 'asc')->row();
		$data['emailx'] = $users->email;
		//FAIP
		$data['user_org'] = $this->main_mod->msrwhere('user_org', array('user_id' => $id, 'status' => 1), 'id', 'asc')->result();
		$data['user_award'] = $this->main_mod->msrwhere('user_award', array('user_id' => $id, 'status' => 1), 'id', 'asc')->result();

		$data['user_course1'] = $this->main_mod->msrwhere('user_course', array('user_id' => $id, 'status' => 1), 'id', 'asc')->result();

		$data['user_prof'] = $this->main_mod->msrwhere('user_prof', array('user_id' => $id, 'status' => 1), 'id', 'asc')->result();

		$data['user_publication1'] = $this->main_mod->msrwhere('user_publication', array('user_id' => $id, 'status' => 1, "type" => "1"), 'id', 'asc')->result();
		$data['user_publication2'] = $this->main_mod->msrwhere('user_publication', array('user_id' => $id, 'status' => 1, "type" => "2"), 'id', 'asc')->result();
		$data['user_publication3'] = $this->main_mod->msrwhere('user_publication', array('user_id' => $id, 'status' => 1, "type" => "3"), 'id', 'asc')->result();
		$data['user_publication4'] = $this->main_mod->msrwhere('user_publication', array('user_id' => $id, 'status' => 1, "type" => "4"), 'id', 'asc')->result();
		$data['user_skill'] = $this->main_mod->msrwhere('user_skill', array('user_id' => $id, 'status' => 1), 'id', 'asc')->result();;

		//Bakuan Penilaian
		$data['bp_12'] = $this->main_mod->msrwhere('m_bakuan_penilaian', array('faip_num' => '12'), 'id', 'asc')->result(); //,'faip_type in ("q","r")'=>null
		$data['bp_13'] = $this->main_mod->msrwhere('m_bakuan_penilaian', array('faip_num' => '13'), 'id', 'asc')->result();
		$data['bp_14'] = $this->main_mod->msrwhere('m_bakuan_penilaian', array('faip_num' => '14'), 'id', 'asc')->result();
		$data['bp_15'] = $this->main_mod->msrwhere('m_bakuan_penilaian', array('faip_num' => '15'), 'id', 'asc')->result();
		$data['bp_16'] = $this->main_mod->msrwhere('m_bakuan_penilaian', array('faip_num' => '16'), 'id', 'asc')->result();

		$data['bp_3'] = $this->main_mod->msrwhere('m_bakuan_penilaian', array('faip_num' => '3'), 'id', 'asc')->result();
		$data['bp_4'] = $this->main_mod->msrwhere('m_bakuan_penilaian', array('faip_num' => '4'), 'id', 'asc')->result();
		$data['bp_51'] = $this->main_mod->msrwhere('m_bakuan_penilaian', array('faip_num' => '51'), 'id', 'asc')->result();
		$data['bp_52'] = $this->main_mod->msrwhere('m_bakuan_penilaian', array('faip_num' => '52'), 'id', 'asc')->result();
		$data['bp_53'] = $this->main_mod->msrwhere('m_bakuan_penilaian', array('faip_num' => '53'), 'id', 'asc')->result();
		$data['bp_54'] = $this->main_mod->msrwhere('m_bakuan_penilaian', array('faip_num' => '54'), 'id', 'asc')->result();
		$data['bp_6'] = $this->main_mod->msrwhere('m_bakuan_penilaian', array('faip_num' => '6'), 'id', 'asc')->result();


		$this->load->view('admin/download_faip_2', $data);
	}

	function download_stri_old_bc_urgent()
	{
		if ($this->session->userdata('admin_id') == '') {
			redirect('auth/login');
		}

		$akses = array("0", "2", "12", "13");
		if (!in_array($this->session->userdata('type'), $akses)) {
			redirect('admin/dashboard');
			exit;
		}

		$this->load->model('main_mod');
		$id = $this->uri->segment(4);
		//$id = $this->session->userdata('user_id');
		//$members=$this->main_mod->msrwhere('members',array('person_id'=>$id,'status'=>1),'id','desc')->result();
		$user_profiles = $this->main_mod->msrwhere('user_profiles', array('user_id' => $id), 'id', 'desc')->result();
		$stri = $this->main_mod->msrwhere('members_certificate', array('person_id' => $id), 'id', 'desc')->result();

		if (isset($user_profiles[0]->firstname) && isset($stri[0])) {

			$name = trim(strtoupper($user_profiles[0]->firstname)) . " " . trim(strtoupper($user_profiles[0]->lastname));
			if (isset($stri[0]->add_name)) $name = $stri[0]->add_name;
			$tgl_sk = $stri[0]->stri_sk;
			$tgl_sk = strtoupper($this->tgl_indo($tgl_sk));
			$no_seri = str_pad($stri[0]->stri_id, 7, '0', STR_PAD_LEFT);
			$no_stri = ($stri[0]->certificate_type != "" ? $stri[0]->certificate_type : "0") . '.' . ($stri[0]->stri_code_bk_hkk == "" ? "000" : str_pad($stri[0]->stri_code_bk_hkk, 3, '0', STR_PAD_LEFT)) . '.' . str_pad($stri[0]->th, 2, '0', STR_PAD_LEFT) . '.' . $stri[0]->warga . '.' . $stri[0]->stri_tipe . '.' . str_pad($stri[0]->stri_id, 8, '0', STR_PAD_LEFT);

			$photo = (($user_profiles[0]->photo != '') ? base_url() . 'assets/uploads/' . $user_profiles[0]->photo : "");
			$photo_cir = $user_profiles[0]->photo;
			//$no_kta = str_pad($members[0]->code_wilayah,4,'0',STR_PAD_LEFT).'.'.str_pad($members[0]->code_bk_hkk,2,'0',STR_PAD_LEFT).'.'.str_pad($members[0]->no_kta,6,'0',STR_PAD_LEFT);

			$kualifikasi = '';

			if ($stri[0]->certificate_type != "") {
				if ($stri[0]->certificate_type == "3")
					$kualifikasi = 'INSINYUR PROFESIONAL UTAMA';
				else if ($stri[0]->certificate_type == "2")
					$kualifikasi = 'INSINYUR PROFESIONAL MADYA';
				else if ($stri[0]->certificate_type == "1")
					$kualifikasi = 'INSINYUR PROFESIONAL PRATAMA';
			}

			//		$ketua = 'Dr. Ir. Danis Hidayat Sumadilaga, ST., M.Eng.Sc., IPU., ACPE.';
			$ketua = 'Dr. -Ing. Ir. Ilham Akbar Habibie, MBA., IPUE.';

			$tgl_penomoran = $stri[0]->stri_from_date;
			if (strtotime($tgl_penomoran) <= strtotime('2021-12-18'))
				//		$ketua = 'Dr. Ir. HERU DEWANTO, ST., M.Sc.(Eng.), IPU., ASEAN Eng., ACPE.';
				$ketua = 'Dr. Ir. Danis Hidayat Sumadilaga, ST., M.Eng.Sc., IPU., ACPE.';
			//$nim = str_replace(".","-",$no_stri.'_'.str_pad($members[0]->no_kta,6,'0',STR_PAD_LEFT).'_'.$name.'_'.(isset($stri[0]->skip_sk)?$stri[0]->skip_sk:""));

			$nim = str_replace(".", "-", $no_stri . '_000000_' . $name . '_' . (isset($stri[0]->stri_sk) ? $stri[0]->stri_sk : "") . '_' . (isset($stri[0]->stri_thru_date) ? $stri[0]->stri_thru_date : ""));

			//print_r($nim);



			$this->load->library('ciqrcode'); //pemanggilan library QR CODE

			$config['cacheable']    = true; //boolean, the default is true
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
			$img_file = FCPATH . './assets/images/blanko_stri22.jpg';
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
			}

			if ($photo_cir == '' || $photo_cir == ' ') {
				$photo = '<img class="img-fluid" style="text-align:right;padding:200;" height="80" width="70" src="' . base_url() . 'assets/images/nophoto.jpg" title="">';
			} else {
				list($width, $height) = getimagesize(FCPATH . './assets/uploads/' . $photo_cir);
				if ($width > $height) {
					$filename = FCPATH . './assets/uploads/' . $photo_cir;
					// Load the image
					$source = imagecreatefromjpeg($filename);
					// Rotate
					$rotate = imagerotate($source, 90, 0);
					//and save it on your server...
					imagejpeg($rotate, $filename);
					//echo 'Landscape';
				} else {
					//echo 'Portrait';// Portrait or Square
				}
				$photo = '<img class="img-fluid" style="text-align:right;padding:200;" height="80" width="70" src="' . $photo . '" title="">';
			}
			/*$fontname = TCPDF_FONTS::addTTFfont(FCPATH.'./assets/fonts/CREDC___.ttf', 'credc', '', 96);
		$fontname2 = TCPDF_FONTS::addTTFfont(FCPATH.'./assets/fonts/Montserrat-Bold.ttf', 'mont', '', 96);
		$pdf->SetFont($fontname2, '', 14, '', false);
		font-family:$fontname2*/

			$tmp = '3px';
			$len = strlen($name);
			//$kualifikasi
			if ($len <= 60) $tmp = '11px';

			$html = <<<EOD
<p style="font-size:13px;"> </p>
<table width="100%" cellspacing="0" border="1" cellpadding="55%"><tr valign="bottom">
<td class="header1" align="center" valign="middle" width="70%"> </td>
<td class="header1" align="center" valign="middle" width="30%" style="font-weight:bold;"> $no_seri</td>
</tr></table>

<p style="font-size:44px;"> </p>

<table width="100%" cellspacing="0" border="1" cellpadding="55%">
<tr valign="bottom">
	<td class="header1" align="center" valign="bottom"
		  width="24%"></td>
	<td class="header1" align="center" valign="middle"
		  width="50%">

	<p style="font-size:12px;font-weight:bold;text-align:center;">$name <br /></p>
	<p style="font-size:49px;font-weight:bold;">  <br /></p>
	<p style="font-size:12px;font-weight:bold;text-align:center;margin-top:10px;">$no_stri</p>

	</td>
	<td class="header1" align="center" valign="middle"
	width="19%"> </td>
</tr>
</table>
<p style="font-size:10px;"> </p>
<table width="100%" cellspacing="0" border="0" cellpadding="55%">
<tr valign="bottom">
	<td class="header1" align="left" valign="bottom"
		  width="15%"></td>
	<td class="header1" align="left" valign="bottom"
		  width="28.5%">$photo</td>
	<td class="header1" align="center" valign="middle"
		  width="30%">
	<p style="font-size:11px;text-align:left;font-weight:bold;line-height: 1.7;">JAKARTA </p>
	<p style="font-size:11px;text-align:left;font-weight:bold;line-height: 1;">$tgl_sk</p>

	</td>
	<td class="header1" align="left" valign="middle"
	width="32%"> <img class="img-fluid" style="text-align:left;" height="80" src="$barcode" title=""></td>
</tr>
</table>

<p style="font-size:16px;"> </p>
<table width="100%" cellspacing="0" border="0" cellpadding="55%">
<tr valign="bottom">
	<td class="header1" align="center" valign="middle"
	width="8%"> </td>
	<td class="header1" align="center" valign="middle"
		  width="85%" style="font-weight:bold;">$ketua</td>
	<td class="header1" align="center" valign="middle"
		  width="7%"> </td>
</tr>
</table>

EOD;
			$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
			//Close and output PDF document
			//print_r($html);
			$pdf->Output($nim . '.pdf', 'D');
		}
	}

	function download_stri_old()
	{
		if ($this->session->userdata('admin_id') == '') {
			redirect('auth/login');
		}

		$akses = array("0", "2", "12", "13");
		if (!in_array($this->session->userdata('type'), $akses)) {
			redirect('admin/dashboard');
			exit;
		}

		$this->load->model('main_mod');
		$id = $this->uri->segment(4);
		//$id = $this->session->userdata('user_id');
		$members = $this->main_mod->msrwhere('members', array('person_id' => $id, 'status' => 1), 'id', 'desc')->result();
		$user_profiles = $this->main_mod->msrwhere('user_profiles', array('user_id' => $id), 'id', 'desc')->result();
		$stri = $this->main_mod->msrwhere('members_certificate', array('person_id' => $id), 'id', 'desc')->result();

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

			if ($stri[0]->certificate_type != "") {
				if ($stri[0]->certificate_type == "3")
					$kualifikasi = 'INSINYUR PROFESIONAL UTAMA';
				else if ($stri[0]->certificate_type == "2")
					$kualifikasi = 'INSINYUR PROFESIONAL MADYA';
				else if ($stri[0]->certificate_type == "1")
					$kualifikasi = 'INSINYUR PROFESIONAL PRATAMA';
			}

			//		$ketua = 'Dr. Ir. Danis Hidayat Sumadilaga, ST., M.Eng.Sc., IPU., ACPE.';
			$ketua = 'Dr. -Ing. Ir. Ilham Akbar Habibie, MBA., IPU.';

			$tgl_penomoran = $stri[0]->stri_from_date;
			if (strtotime($tgl_penomoran) <= strtotime('2021-12-18'))
				//	$ketua = 'Dr. Ir. HERU DEWANTO, ST., M.Sc.(Eng.), IPU., ASEAN Eng., ACPE.';
				//	$ketua = 'Dr. Ir. Danis Hidayat Sumadilaga, ST., M.Eng.Sc., IPU., ACPE.';
				$ketua = 'Dr. -Ing. Ir. Ilham Akbar Habibie, MBA., IPU.';

			//$nim = str_replace(".","-",$no_stri.'_'.str_pad($members[0]->no_kta,6,'0',STR_PAD_LEFT).'_'.$name.'_'.(isset($stri[0]->skip_sk)?$stri[0]->skip_sk:""));

			$nim = str_replace(".", "-", $no_stri . '_' . str_pad($members[0]->no_kta, 6, '0', STR_PAD_LEFT) . '_' . $name . '_' . (isset($stri[0]->stri_sk) ? $stri[0]->stri_sk : "") . '_' . (isset($stri[0]->stri_thru_date) ? $stri[0]->stri_thru_date : ""));

			//print_r($nim);



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
			//



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
			$img_file = FCPATH . './assets/images/blanko_stri22.jpg';
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
				$photo = '<img class="img-fluid" style="text-align:right;padding:200;" height="80" width="70" src="' . base_url() . 'assets/images/nophoto.jpg" title="">';
			} else {
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
				$photo = '<img class="img-fluid" style="text-align:right;padding:200;" height="80" width="70" src="' . $photo . '" title="">';
			}
			/*$fontname = TCPDF_FONTS::addTTFfont(FCPATH.'./assets/fonts/CREDC___.ttf', 'credc', '', 96);
		$fontname2 = TCPDF_FONTS::addTTFfont(FCPATH.'./assets/fonts/Montserrat-Bold.ttf', 'mont', '', 96);
		$pdf->SetFont($fontname2, '', 14, '', false);
		font-family:$fontname2*/

			$tmp = '3px';
			$len = strlen($name);
			//$kualifikasi
			if ($len <= 60) $tmp = '11px';

			$html = <<<EOD
<p style="font-size:13px;"> </p>
<table width="100%" cellspacing="0" border="1" cellpadding="55%"><tr valign="bottom">
<td class="header1" align="center" valign="middle" width="70%"> </td>
<td class="header1" align="center" valign="middle" width="30%" style="font-weight:bold;"> $no_seri</td>
</tr></table>

<p style="font-size:44px;"> </p>

<table width="100%" cellspacing="0" border="1" cellpadding="55%">
<tr valign="bottom">
	<td class="header1" align="center" valign="bottom"
		  width="24%"></td>
	<td class="header1" align="center" valign="middle"
		  width="50%">

	<p style="font-size:12px;font-weight:bold;text-align:center;">$name <br /></p>
	<p style="font-size:49px;font-weight:bold;">  <br /></p>
	<p style="font-size:12px;font-weight:bold;text-align:center;margin-top:10px;">$no_stri</p>

	</td>
	<td class="header1" align="center" valign="middle"
	width="19%"> </td>
</tr>
</table>
<p style="font-size:10px;"> </p>
<table width="100%" cellspacing="0" border="0" cellpadding="55%">
<tr valign="bottom">
	<td class="header1" align="left" valign="bottom"
		  width="15%"></td>
	<td class="header1" align="left" valign="bottom"
		  width="28.5%">$photo</td>
	<td class="header1" align="center" valign="middle"
		  width="30%">
	<p style="font-size:11px;text-align:left;font-weight:bold;line-height: 1.7;">JAKARTA </p>
	<p style="font-size:11px;text-align:left;font-weight:bold;line-height: 1;">$tgl_sk</p>

	</td>
	<td class="header1" align="left" valign="middle"
	width="32%"> <img class="img-fluid" style="text-align:left;" height="80" src="$barcode" title=""></td>
</tr>
</table>

<p style="font-size:16px;"> </p>
<table width="100%" cellspacing="0" border="0" cellpadding="55%">
<tr valign="bottom">
	<td class="header1" align="center" valign="middle"
	width="8%"> </td>
	<td class="header1" align="center" valign="middle"
		  width="85%" style="font-weight:bold;">$ketua</td>
	<td class="header1" align="center" valign="middle"
		  width="7%"> </td>
</tr>
</table>

EOD;
			$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
			//Close and output PDF document
			//print_r($html);
			$pdf->Output($nim . '.pdf', 'D');
		}
	}
	function download_stri_old2()
	{
		if ($this->session->userdata('admin_id') == '') {
			redirect('auth/login');
		}

		$akses = array("0", "2", "12", "13");
		if (!in_array($this->session->userdata('type'), $akses)) {
			redirect('admin/dashboard');
			exit;
		}

		$this->load->model('main_mod');
		$id = $this->uri->segment(4);
		//$id = $this->session->userdata('user_id');
		$members = $this->main_mod->msrwhere('members', array('person_id' => $id, 'status' => 1), 'id', 'desc')->result();
		$user_profiles = $this->main_mod->msrwhere('user_profiles', array('user_id' => $id), 'id', 'desc')->result();
		$stri = $this->main_mod->msrwhere('members_certificate', array('person_id' => $id), 'id', 'desc')->result();

		if (isset($members[0]->no_kta) && isset($user_profiles[0]->firstname) && isset($stri[0])) {

			$name = trim(strtoupper($user_profiles[0]->firstname)) . " " . trim(strtoupper($user_profiles[0]->lastname));
			if (isset($stri[0]->add_name)) $name = $stri[0]->add_name;
			$tgl_sk = $stri[0]->skip_sk;
			$tgl_sk = $this->tgl_indo($tgl_sk);
			$no_seri = str_pad($stri[0]->stri_id, 8, '0', STR_PAD_LEFT);
			$no_stri = ($stri[0]->certificate_type != "" ? $stri[0]->certificate_type : "0") . '-' . ($stri[0]->stri_code_bk_hkk == "" ? "00" : $stri[0]->stri_code_bk_hkk) . '-' . str_pad($stri[0]->th, 2, '0', STR_PAD_LEFT) . '-' . $stri[0]->warga . '-' . $stri[0]->stri_tipe . '-' . str_pad($stri[0]->stri_id, 8, '0', STR_PAD_LEFT);

			$photo = (($user_profiles[0]->photo != '') ? base_url() . 'assets/uploads/' . $user_profiles[0]->photo : "");
			$photo_cir = $user_profiles[0]->photo;
			$no_kta = str_pad($members[0]->code_wilayah, 4, '0', STR_PAD_LEFT) . '.' . str_pad($members[0]->code_bk_hkk, 2, '0', STR_PAD_LEFT) . '.' . str_pad($members[0]->no_kta, 6, '0', STR_PAD_LEFT);

			$kualifikasi = '';

			if ($stri[0]->certificate_type != "") {
				if ($stri[0]->certificate_type == "3")
					$kualifikasi = 'INSINYUR PROFESIONAL UTAMA';
				else if ($stri[0]->certificate_type == "2")
					$kualifikasi = 'INSINYUR PROFESIONAL MADYA';
				else if ($stri[0]->certificate_type == "1")
					$kualifikasi = 'INSINYUR PROFESIONAL PRATAMA';
			}

			//		$ketua = 'Dr. Ir. Danis Hidayat Sumadilaga, ST., M.Eng.Sc., IPU., ACPE.';
			$ketua = 'Dr. -Ing. Ir. Ilham Akbar Habibie, MBA., IPU.';

			$tgl_penomoran = $stri[0]->stri_from_date;
			if (strtotime($tgl_penomoran) <= strtotime('2021-12-18'))
				//	$ketua = 'Dr. Ir. HERU DEWANTO, ST., M.Sc.(Eng.), IPU., ASEAN Eng., ACPE.';
				$ketua = 'Dr. Ir. Danis Hidayat Sumadilaga, ST., M.Eng.Sc., IPU., ACPE.';
			$nim = str_replace(".", "-", $no_stri . '_' . str_pad($members[0]->no_kta, 6, '0', STR_PAD_LEFT) . '_' . $name . '_' . (isset($stri[0]->skip_sk) ? $stri[0]->skip_sk : ""));
			//print_r($nim);


			$this->load->library('ciqrcode'); //pemanggilan library QR CODE

			$config['cacheable']    = true; //boolean, the default is true
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
			//$img_file = FCPATH.'./assets/images/blankosrti.jpg';
			//$pdf->Image($img_file, 0, 0, 354, 216, '', '', '', false, 300, '', false, false, 0);

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


			/*$fontname = TCPDF_FONTS::addTTFfont(FCPATH.'./assets/fonts/CREDC___.ttf', 'credc', '', 96);
		$fontname2 = TCPDF_FONTS::addTTFfont(FCPATH.'./assets/fonts/Montserrat-Bold.ttf', 'mont', '', 96);
		$pdf->SetFont($fontname2, '', 14, '', false);
		font-family:$fontname2*/

			$tmp = '3px';
			$len = strlen($name);

			if ($len <= 60) $tmp = '11px';

			$html = <<<EOD
<p style="font-size:18px;"> </p>
<table width="100%" cellspacing="0" border="0" cellpadding="55%"><tr valign="bottom">
<td class="header1" align="center" valign="middle" width="65%"> </td>
<td class="header1" align="center" valign="middle" width="35%"> $no_seri</td>
</tr></table>

<p style="font-size:37px;"> </p>

<table width="100%" cellspacing="0" border="0" cellpadding="55%">
<tr valign="bottom">
	<td class="header1" align="center" valign="bottom"
		  width="23%"></td>
	<td class="header1" align="center" valign="middle"
		  width="50%">

	<p style="font-size:15px;font-weight:bold;text-align:center;">$name <br /></p>
	<p style="font-size:17px;font-weight:bold;"> $kualifikasi <br /></p>
	<p style="font-size:15px;font-weight:bold;text-align:center;margin-top:10px;">$no_stri</p>

	</td>
	<td class="header1" align="center" valign="middle"
	width="20%"> </td>
</tr>
</table>
<p style="font-size:7px;"> </p>
<table width="100%" cellspacing="0" border="0" cellpadding="55%">
<tr valign="bottom">
	<td class="header1" align="left" valign="bottom"
		  width="18%"></td>
	<td class="header1" align="left" valign="bottom"
		  width="28%"><img class="img-fluid" style="text-align:left;" width="70" height="80" src="$photo" title=""></td>
	<td class="header1" align="center" valign="middle"
		  width="28%">
	<p style="font-size:15px;text-align:left;font-weight:bold;line-height: 1.7;">JAKARTA <br />$tgl_sk</p>


	</td>
	<td class="header1" align="left" valign="middle"
	width="30%"> <img class="img-fluid" style="text-align:left;" height="80" src="$barcode" title=""></td>
</tr>
</table>

<p style="font-size:20px;"> </p>
<table width="100%" cellspacing="0" border="0" cellpadding="55%">
<tr valign="bottom">
	<td class="header1" align="center" valign="middle"
	width="8%"> </td>
	<td class="header1" align="center" valign="middle"
		  width="85%" style="font-weight:bold;">$ketua</td>
	<td class="header1" align="center" valign="middle"
		  width="7%"> </td>
</tr>
</table>

<table width="100%" cellspacing="0" border="0" cellpadding="55%">
EOD;
			$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
			//Close and output PDF document
			//print_r($html);
			$pdf->Output($nim . '.pdf', 'D');
		}
	}
	function download_stri()
	{
		if ($this->session->userdata('admin_id') == '') {
			redirect('auth/login');
		}

		$akses = array("0", "2", "12", "13");
		if (!in_array($this->session->userdata('type'), $akses)) {
			redirect('admin/dashboard');
			exit;
		}

		$this->load->model('main_mod');
		$id = $this->uri->segment(4);
		//$id = $this->session->userdata('user_id');
		$members = $this->main_mod->msrwhere('members', array('person_id' => $id, 'status' => 1), 'id', 'desc')->result();
		$user_profiles = $this->main_mod->msrwhere('user_profiles', array('user_id' => $id), 'id', 'desc')->result();
		$stri = $this->main_mod->msrwhere('members_certificate', array('person_id' => $id), 'id', 'desc')->result();

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


			$tgl_penomoran = '';

			if ($stri[0]->certificate_type != "") {
				if ($stri[0]->certificate_type == "3")
					$kualifikasi = 'INSINYUR PROFESIONAL UTAMA';
				else if ($stri[0]->certificate_type == "2")
					$kualifikasi = 'INSINYUR PROFESIONAL MADYA';
				else if ($stri[0]->certificate_type == "1")
					$kualifikasi = 'INSINYUR PROFESIONAL PRATAMA';

				if ($stri[0]->certificate_type == "3" || $stri[0]->certificate_type == "2" || $stri[0]->certificate_type == "1") {
					$cek_ketum = $this->main_mod->msrquery('select ut.createddate from user_transfer ut join user_cert uc on ut.rel_id=uc.id join members_certificate mc on TRIM(LEADING "0" FROM mc.skip_id)=TRIM(LEADING "0" FROM uc.ip_seq) where mc.id=' . $stri[0]->id . ' and ut.user_id = ' . $id . ' order by ut.createddate desc limit 1')->row();
					if (isset($cek_ketum->createddate)) {
						if (strtotime($cek_ketum->createddate) <= strtotime('2021-12-18'))
							$tgl_penomoran = '2021-12-17';
						else
							$tgl_penomoran = $cek_ketum->createddate;
					} else $tgl_penomoran = '2021-12-17';
				}
			}

			//		$ketua = 'Dr. Ir. Danis Hidayat Sumadilaga, ST., M.Eng.Sc., IPU., ACPE.';
			$ketua = 'Dr. -Ing. Ir. Ilham Akbar Habibie, MBA., IPU.';

			$tgl_penomoran = $tgl_penomoran == '' ? $stri[0]->stri_from_date : $tgl_penomoran;
			if (strtotime($tgl_penomoran) <= strtotime('2021-12-18'))
				//	$ketua = 'Dr. Ir. HERU DEWANTO, ST., M.Sc.(Eng.), IPU., ASEAN Eng., ACPE.';
				$ketua = 'Dr. Ir. Danis Hidayat Sumadilaga, ST., M.Eng.Sc., IPU., ACPE.';

			//$nim = str_replace(".","-",$no_stri.'_'.str_pad($members[0]->no_kta,6,'0',STR_PAD_LEFT).'_'.$name.'_'.(isset($stri[0]->skip_sk)?$stri[0]->skip_sk:""));

			$nim = str_replace(" ", "", str_replace(".", "-", $no_stri . '_' . str_pad($members[0]->no_kta, 6, '0', STR_PAD_LEFT) . '_' . $name . '_' . (isset($stri[0]->stri_sk) ? $stri[0]->stri_sk : "") . '_' . (isset($stri[0]->stri_thru_date) ? $stri[0]->stri_thru_date : "")));

			//print_r($nim);


			$this->load->library('ciqrcode'); //pemanggilan library QR CODE

			$config['cacheable']    = true; //boolean, the default is true
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
			//$img_file = FCPATH.'./assets/images/blankosrti.jpg';
			//$pdf->Image($img_file, 0, 0, 354, 216, '', '', '', false, 300, '', false, false, 0);

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


			/*$fontname = TCPDF_FONTS::addTTFfont(FCPATH.'./assets/fonts/CREDC___.ttf', 'credc', '', 96);
		$fontname2 = TCPDF_FONTS::addTTFfont(FCPATH.'./assets/fonts/Montserrat-Bold.ttf', 'mont', '', 96);
		$pdf->SetFont($fontname2, '', 14, '', false);
		font-family:$fontname2*/

			$tmp = '3px';
			$len = strlen($name);

			if ($len <= 60) $tmp = '11px';

			$html = <<<EOD
<p style="font-size:14px;"> </p>
<table width="100%" cellspacing="0" border="0" cellpadding="55%"><tr valign="bottom">
<td class="header1" align="center" valign="middle" width="72%"> </td>
<td class="header1" align="center" valign="middle" width="28%"  style="font-size:11px;font-weight:bold;"> $no_seri</td>
</tr></table>

<p style="font-size:38px;"> </p>

<table width="100%" cellspacing="0" border="0" cellpadding="55%">
<tr valign="bottom">
	<td class="header1" align="center" valign="bottom"
		  width="25%"></td>
	<td class="header1" align="center" valign="middle"
		  width="50%">

	<p style="font-size:11px;font-weight:bold;text-align:center;">$name <br /><br /></p>
	<p style="font-size:11px;font-weight:bold;"> $kualifikasi <br /><br /></p>
	<p style="font-size:11px;font-weight:bold;text-align:center;margin-top:10px;">$no_stri</p>

	</td>
	<td class="header1" align="center" valign="middle"
	width="20%"> </td>
</tr>
</table>
<p style="font-size:9px;"> </p>
<table width="100%" cellspacing="0" border="0" cellpadding="55%">
<tr valign="bottom">
	<td class="header1" align="left" valign="bottom"
		  width="18%"></td>
	<td class="header1" align="left" valign="bottom"
		  width="29.5%"></td>
	<td class="header1" align="center" valign="middle"
		  width="28%">
	<p style="font-size:11px;text-align:left;font-weight:bold;line-height: 1.7;">JAKARTA </p>
	<p style="font-size:11px;text-align:left;font-weight:bold;line-height: 1;">$tgl_sk</p>

	</td>
	<td class="header1" align="left" valign="middle"
	width="30%"> <img class="img-fluid" style="text-align:left;" height="80" src="$barcode" title=""></td>
</tr>
</table>

<p style="font-size:21px;"> </p>
<table width="100%" cellspacing="0" border="0" cellpadding="55%">
<tr valign="bottom">
	<td class="header1" align="center" valign="middle"
	width="8%"> </td>
	<td class="header1" align="center" valign="middle"
		  width="85%" style="font-weight:bold;">$ketua</td>
	<td class="header1" align="center" valign="middle"
		  width="7%"> </td>
</tr>
</table>

<table width="100%" cellspacing="0" border="0" cellpadding="55%">
EOD;
			$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
			//Close and output PDF document
			//print_r($html);
			$pdf->Output($nim . '.pdf', 'D');
		}
	}


	function download_stri_ttd_bc()
	{
		if ($this->session->userdata('admin_id') == '') {
			redirect('auth/login');
		}

		$akses = array("0", "2", "12", "13");
		if (!in_array($this->session->userdata('type'), $akses)) {
			redirect('admin/dashboard');
			exit;
		}

		$this->load->model('main_mod');
		$id = $this->uri->segment(4);
		//$id = $this->session->userdata('user_id');
		$members = $this->main_mod->msrwhere('members', array('person_id' => $id, 'status' => 1), 'id', 'desc')->result();
		$user_profiles = $this->main_mod->msrwhere('user_profiles', array('user_id' => $id), 'id', 'desc')->result();
		$stri = $this->main_mod->msrwhere('members_certificate', array('person_id' => $id), 'id', 'desc')->result();

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
			}

			//	$ketua = 'Dr. Ir. Danis Hidayat Sumadilaga, ST., M.Eng.Sc., IPU., ACPE.';
			$ketua = 'Dr. -Ing. Ir. Ilham Akbar Habibie, MBA., IPU.';

			$tgl_penomoran = $stri[0]->stri_from_date;
			if (strtotime($tgl_penomoran) <= strtotime('2021-12-18'))
				//	$ketua = 'Dr. Ir. HERU DEWANTO, ST., M.Sc.(Eng.), IPU., ASEAN Eng., ACPE.';
				$ketua = 'Dr. Ir. Danis Hidayat Sumadilaga, ST., M.Eng.Sc., IPU., ACPE.';

			//$nim = str_replace(".","-",$no_stri.'_'.str_pad($members[0]->no_kta,6,'0',STR_PAD_LEFT).'_'.$name.'_'.(isset($stri[0]->skip_sk)?$stri[0]->skip_sk:""));

			$nim = str_replace(".", "-", $no_stri . '_' . str_pad($members[0]->no_kta, 6, '0', STR_PAD_LEFT) . '_' . $name . '_' . (isset($stri[0]->stri_sk) ? $stri[0]->stri_sk : "") . '_' . (isset($stri[0]->stri_thru_date) ? $stri[0]->stri_thru_date : ""));

			//print_r($nim);



			$this->load->library('ciqrcode'); //pemanggilan library QR CODE

			$config['cacheable']    = true; //boolean, the default is true
			$config['imagedir']     = './assets/uploads/qr/'; //direktori penyimpanan qr code
			$config['quality']      = true; //boolean, the default is true
			$config['size']         = '1024'; //interger, the default is 1024
			$config['black']        = array(224, 255, 255); // array, default is array(255,255,255)
			$config['white']        = array(70, 130, 180); // array, default is array(0,0,0)
			$this->ciqrcode->initialize($config);

			//$image_name=$nim.'.jpg'; //buat name dari qr code sesuai dengan nim
			$image_name = str_pad($members[0]->no_kta, 6, '0', STR_PAD_LEFT) . '.jpg'; //buat name dari qr code sesuai dengan nim

			$params['data'] = $nim; //data yang akan di jadikan QR CODE
			$params['level'] = 'H'; //H=High
			$params['size'] = 10;
			$params['savename'] = FCPATH . $config['imagedir'] . $image_name; //simpan image QR CODE ke folder assets/images/
			$this->ciqrcode->generate($params); // fungsi untuk generate QR CODE
			$barcode = $params['savename'];
			//



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

				/*$img = new Imagick($photo);
			$img->stripImage();
			$img->writeImage($photo);
			$img->clear();
			$img->destroy();*/


				$photo = '<img class="img-fluid" style="text-align:right;padding:200;" height="70" width="70" src="' . $photo . '" title="">';
			}
			/*$fontname = TCPDF_FONTS::addTTFfont(FCPATH.'./assets/fonts/CREDC___.ttf', 'credc', '', 96);
		$fontname2 = TCPDF_FONTS::addTTFfont(FCPATH.'./assets/fonts/Montserrat-Bold.ttf', 'mont', '', 96);
		$pdf->SetFont($fontname2, '', 14, '', false);
		font-family:$fontname2*/

			//	$ttd_ketum = FCPATH.'./assets/images/tanda_tangan_ketum_baru.png';
			$ttd_ketum = FCPATH . './assets/images/Ketum-Ilham_1.png';

			$flag_1 = '';
			$flag_2 = '60';

			//	echo $tgl_penomoran ; exit() ;

			/*
		if(strtotime($tgl_penomoran)<= strtotime('2024-12-05'))
		{
	//		$ttd_ketum = FCPATH.'./assets/images/tanda_tangan_ketum.png';
			$ttd_ketum = FCPATH.'./assets/images/tanda_tangan_ketum_baru.png';
			$flag_1 = '<br />';
			$flag_2 = '50';

		}

		if(strtotime($tgl_penomoran) >= strtotime('2024-12-06') && strtotime($tgl_penomoran) <= strtotime('2025-05-29'))
		{ 

			$ttd_ketum = FCPATH.'./assets/images/Ketum-Ilham_1.png';
			$flag_1 = '<br />';
			$flag_2 = '50';

		}
		if(strtotime($tgl_penomoran) >= strtotime('2025-05-30'))
		{ 

			$ttd_ketum = FCPATH.'./assets/images/Ketum-ttd.png'; 
			$flag_1 = '<br />';
			$flag_2 = '50';
			$barcode ;

		}	
		
*/
			$tmp = '3px';
			$len = strlen($name);
			//$kualifikasi
			if ($len <= 60) $tmp = '11px';

			$html = <<<EOD
<p style="font-size:18px;"> </p>
<table width="100%" cellspacing="0" border="1" cellpadding="55%"><tr valign="bottom">
<td class="header1" align="center" valign="middle" width="70%"> </td>
<td class="header1" align="center" valign="middle" width="30%" style="font-weight:bold;"> $no_seri</td>
</tr></table>

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

	<br /><br />
	<img class="img-fluid" height="50" src="$ttd_ketum" title=""></p>
	</td></tr>

	</table>


	</td>
	<td class="header1" align="right" valign="middle"
	width="15%"> <img class="img-fluid" style="text-align:right;" height="70" width="70" src="$barcode" title=""></td>
	<td class="header1" align="left" valign="bottom"
		  width="15%"></td>
</tr>
</table>
<table width="100%" cellspacing="0" border="0" cellpadding="55%">
<tr valign="bottom">
	<td class="header1" align="center" valign="middle"
	width="8%"> </td>
	<td class="header1" align="center" valign="middle"
		  width="85%" style="font-weight:bold;font-size:11px;">$ketua<br />
		  <span style="font-size:11px;text-align:center;font-weight:normal;">Ketua Umum</span><br />
		  <i style="font-size:10px;text-align:center;font-weight:normal;">President</i>
		  </td>
	<td class="header1" align="center" valign="middle"
		  width="7%"> </td>
</tr>
</table>

EOD;
			$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
			//Close and output PDF document
			//print_r($html);
			$pdf->Output($nim . '.pdf', 'D');
		}
	}


	function download_stri_ttd()
	{
		if ($this->session->userdata('admin_id') == '') {
			redirect('auth/login');
		}

		$akses = array("0", "2", "11", "12", "13");
		if (!in_array($this->session->userdata('type'), $akses)) {
			redirect('admin/dashboard');
			exit;
		}

		$this->load->model('main_mod');
		$id = $this->uri->segment(4);
		//$id = $this->session->userdata('user_id');
		$members = $this->main_mod->msrwhere('members', array('person_id' => $id, 'status' => 1), 'id', 'desc')->result();
		$user_profiles = $this->main_mod->msrwhere('user_profiles', array('user_id' => $id), 'id', 'desc')->result();
		$stri = $this->main_mod->msrwhere('members_certificate', array('person_id' => $id), 'id', 'desc')->result();
		//---------------------------------------------------------------------------------------------------------------------------- Tambahan by Ipur Tgl 03-06-2025		
		//		$this->load->model('members_model');
		$dibuat = $this->members_model->ambil_create_date($id);
		$dibuatnya = $dibuat->createddate;
		//                $status_faip = $this->members_model->get_status_faip($id) ; $stat_faip = $status_faip->status_faip ;
		$strii_ttd = $this->members_model->get_ttd_stri($id);
		//		print_r($strii_ttd);

		if ($strii_ttd->stri_ttd == 0) {
			$nama_ttd = "";
		}

		if ($strii_ttd->stri_ttd == 1) {
			$nama_ttd = "Kosong";
		}
		if ($strii_ttd->stri_ttd == 2) {
			$nama_ttd = ""; //Dr. -Ing. Ir. Ilham Akbar Habibie, MBA., IPU." ;
		}
		if ($strii_ttd->stri_ttd == 3) {
			$nama_ttd = ""; //"Dr. Ir. Danis Hidayat Sumadilaga, ST., M.Eng.Sc., IPU., ACPE." ;
		}
		//----------------------------------------------------------------------------------------------------------------------------------------------------------		
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


				if ($stri[0]->certificate_type == "3" || $stri[0]->certificate_type == "2" || $stri[0]->certificate_type == "1") {
					$cek_ketum = $this->main_mod->msrquery('select ut.createddate from user_transfer ut join user_cert uc on ut.rel_id=uc.id join members_certificate mc on TRIM(LEADING "0" FROM mc.skip_id)=TRIM(LEADING "0" FROM uc.ip_seq) where mc.id=' . $stri[0]->id . ' and ut.user_id = ' . $id . ' order by ut.createddate desc limit 1')->row();

					if (isset($cek_ketum->createddate)) {
						if (strtotime($cek_ketum->createddate) <= strtotime('2021-11-23'))
							// $tgl_penomoran = '2024-12-05';
							$tgl_penomoran = $stri[0]->stri_sk;
						else
							//$tgl_penomoran = $cek_ketum->createddate;
							$tgl_penomoran = $stri[0]->stri_sk;
					}
					// else $tgl_penomoran = '2024-12-05';
					else $tgl_penomoran = $stri[0]->stri_sk;
				}
			}

			$ketua = '';

			$tgl_penomoran = $stri[0]->stri_sk;
			/*
			if(strtotime($tgl_penomoran)<= strtotime('2024-12-05')) {

		        $ketua = 'Dr. Ir. Danis Hidayat Sumadilaga, ST., M.Eng.Sc., IPU., ACPE.';
			//	$ketua = 'Dr. Ir. HERU DEWANTO, ST., M.Sc.(Eng.), IPU., ASEAN Eng., ACPE.';
			//        $ketua = 'Dr. -Ing. Ir. Ilham Akbar Habibie, MBA., IPU.';
			}
*/
			if (strtotime($dibuatnya) >= strtotime('2025-05-30')) {
				$ketua = '';
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
			} // Eof if(strtotime($dibuatnya) <= strtotime('2025-05-30'))

			//print_r($user_profiles[0]->photo);

			$this->load->library('Pdf');

			$your_width = 296.8;
			$your_height = 250; // 210.1;
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
				}
			}

			if ($photo_cir == '' || $photo_cir == ' ') {
				$photo = '<img class="img-fluid" style="text-align:right;padding:200;" height="70" width="70" src="' . base_url() . 'assets/images/nophoto.jpg" title="">';
			} else {

				$photo = '<img class="img-fluid" style="text-align:right;padding:200;" height="70" width="70" src="' . $photo . '" title="">';
			}

			$flag_1 = '';
			$flag_2 = '60';

			$ttd_ketum = FCPATH . './assets/images/Ketum-Ilham_1.jpg';
			$flag_1 = '<br />';
			$flag_2 = '90';


			if (strtotime($dibuatnya) >= strtotime('2025-05-30')) {
				$ttd_ketum = FCPATH . './assets/images/Ketum-Ilham_1A.jpg';
				$flag_1 = '<br />';
				$flag_2 = '90';
				$barcode = '';
			}

			if (strtotime($dibuatnya) < strtotime('2025-05-30')) {
				if (strtotime($dibuatnya) > strtotime('2024-12-04')) {
					$ttd_ketum = FCPATH . './assets/images/Ketum-Ilham_1.jpg';
					$flag_1 = '<br />';
					$flag_2 = '90';
					//	$barcode = '' ;
				}
			}

			if (strtotime($dibuatnya) < strtotime('2024-12-05')) {
				if (strtotime($dibuatnya) > strtotime('2021-12-05')) {
					$ttd_ketum = FCPATH . './assets/images/tanda_tangan_ketum_DANIS.jpg';
					$flag_1 = '<br />';
					$flag_2 = '90';
					//	$barcode = '' ;
				}
			}

			if (strtotime($dibuatnya) < strtotime('2021-12-05')) {
				$ttd_ketum = FCPATH . './assets/images/tanda_tangan_ketum_1.png';
				$flag_1 = '<br />';
				$flag_2 = '90';
			}
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
			</td>

	</tr>
	
	</table>
	
	</td>

<?php			
		if(strtotime($dibuatnya) <= strtotime('2025-05-29')) {
		  {  ?>
	<td class="header1" align="right" valign="middle" rowspan="3"
	width="15%"> <img class="img-fluid" style="text-align:right;" height="70" width="70" src="$barcode" title=""></td>
	<td class="header1" align="left" valign="bottom"
		  width="15%"></td>	
<?php } ?>	
	
        </tr>
</table>

<table width="100%" cellspacing="0" border="0" cellpadding="0%">

			<tr valign="bottom">
				
				
				<td class="header1" align="center" valign="middle" width="100%" style="font-weight:bold;font-size:11px;">
			        	
					  <span style="font-size:11px;text-align:center;font-weight:normal;">Ketua Umum</span><br />
					  <i style="font-size:10px;text-align:center;font-weight:normal;">President</i>
			         </td>
				
</tr>
</table>

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

	public function bp()
	{
		$akses = array("0");
		if (!in_array($this->session->userdata('type'), $akses)) {
			redirect('admin/dashboard');
			exit;
		}

		$this->load->library('grocery_CRUD');
		try {
			$crud = new grocery_CRUD();

			//$crud->set_theme('datatables');
			$crud->set_table('m_bakuan_penilaian');
			$crud->set_subject('Bakuan Penilaian');
			$crud->required_fields('faip_num,faip_type');
			//$crud->columns('desc');

			$output = $crud->render();

			$this->load->view('admin/bp_view2', (array)$output);
		} catch (Exception $e) {
			show_error($e->getMessage() . ' --- ' . $e->getTraceAsString());
		}
	}

	public function bp_old()
	{
		$data['title'] = ''; //SITE_NAME.': Manage Members';
		$data['msg'] = '';


		//Pagination starts
		$total_rows = $this->members_model->record_count_bp('admin');
		$config = pagination_configuration(base_url("admin/members/bp"), $total_rows, 10, 4, 5, true);

		$this->pagination->initialize($config);
		$page = ($this->uri->segment(3)) ? $this->uri->segment(4) : 0;
		$page_num = $page - 1;
		$page_num = ($page_num < 0) ? '0' : $page_num;
		$page = $page_num * $config["per_page"];
		$data["links"] = $this->pagination->create_links();
		//Pagination ends
		//print_r($data["links"]);

		$obj_result = $this->members_model->get_all_bp($config["per_page"], $page);
		$data['result'] = $obj_result;
		$data["total_rows"] = $total_rows;


		$this->load->view('admin/bp_view', $data);
		return;
	}

	function setbp()
	{
		$akses = array("0");
		if (!in_array($this->session->userdata('type'), $akses)) {
			redirect('admin/dashboard');
			exit;
		}

		$faip_id = $this->input->post('id') <> null ? $this->input->post('id') : "";
		$faip_num = $this->input->post('faip_num') <> null ? $this->input->post('faip_num') : "";
		$bak_komp = $this->input->post('bak_komp') <> null ? $this->input->post('bak_komp') : "";
		$faip_type = $this->input->post('faip_type') <> null ? $this->input->post('faip_type') : "";
		$formula = $this->input->post('formula') <> null ? $this->input->post('formula') : "";
		$condition = $this->input->post('condition') <> null ? $this->input->post('condition') : "";
		$value = $this->input->post('value') <> null ? $this->input->post('value') : "";
		$desc = $this->input->post('desc') <> null ? $this->input->post('desc') : "";
		/*if($faip_id==''){
			redirect('admin/members');
			exit;
		}*/
		$this->load->model('main_mod');
		//if($faip_id!=''){
		try {


			$rowInsert = array(
				'id' => $faip_id,
				'faip_num' => $faip_num,
				'bak_komp' => $bak_komp,
				'faip_type' => $faip_type,
				'formula' => $formula,
				'condition' => $condition,
				'value' => $value,
				'desc' => $desc,
				'createdby' => $this->session->userdata('admin_id'),
			);

			$check = $this->main_mod->msrwhere('m_bakuan_penilaian', array('id' => $faip_id), 'id', 'desc')->result();
			if (isset($check[0])) {
				$where = array(
					"id" => $faip_id
				);
				$row = array(
					'faip_num' => $faip_num,
					'bak_komp' => $bak_komp,
					'faip_type' => $faip_type,
					'formula' => $formula,
					'condition' => $condition,
					'value' => $value,
					'desc' => $desc,
					'modifieddate' => date('Y-m-d H:i:s'),
					'modifiedby' => $this->session->userdata('admin_id'),
				);

				$update = $this->main_mod->update('m_bakuan_penilaian', $where, $row);
			} else {
				$this->main_mod->insert('m_bakuan_penilaian', $rowInsert);
			}

			echo "valid";
		} catch (Exception $e) {
			//print_r($e);
			echo "not valid";
		}
		/*}
		else
			echo "not valid";*/
	}

	function getbp()
	{
		$id = $this->input->get('id') <> null ? $this->input->get('id') : "";

		$bp = $this->members_model->get_bp($id);
		$array =  (array) $bp;
		print_r(json_encode($array));
	}

	public function kolektif()
	{
		$akses = array("0", "2");
		if (!in_array($this->session->userdata('type'), $akses)) {
			redirect('admin/dashboard');
			exit;
		}

		$this->load->library('grocery_CRUD');
		try {
			$crud = new grocery_CRUD();

			//$crud->set_theme('datatables');
			$crud->set_table('m_kolektif');
			$crud->set_subject('Kolektif');
			$crud->set_relation('category', 'm_kolektif_cat', 'name');
			$crud->required_fields('name,category,status');
			$crud->unset_columns(array('status'));
			//$crud->columns('desc');
			$crud->unset_delete();
			$output = $crud->render();

			$this->load->view('admin/kolektif_view', (array)$output);
		} catch (Exception $e) {
			show_error($e->getMessage() . ' --- ' . $e->getTraceAsString());
		}
	}


	public function m_bk_skip()
	{
		$akses = array("0", "1");
		if (!in_array($this->session->userdata('type'), $akses)) {
			redirect('admin/dashboard');
			exit;
		}

		$this->load->library('grocery_CRUD');
		try {
			$crud = new grocery_CRUD();

			//$crud->set_theme('datatables');
			$crud->set_table('m_bk_skip');
			$crud->set_subject('Ketua BK');
			$crud->set_relation('value', 'm_bk', '{value} - {name}');
			$crud->required_fields('value,nama_ketua,ttd,startdate,enddate');

			$crud->callback_before_insert(array($this, 'is_duplicate'));
			$crud->callback_before_update(array($this, 'is_duplicate_edit'));

			//$crud->unset_columns(array('status'));

			$crud->set_field_upload('ttd', 'assets/uploads/ttd');

			//$crud->columns('desc');
			$crud->unset_delete();
			$output = $crud->render();

			$this->load->view('admin/m_bk_skip_view', (array)$output);
		} catch (Exception $e) {
			show_error($e->getMessage() . ' --- ' . $e->getTraceAsString());
		}
	}

	public function is_duplicate($post_array)
	{
		$this->load->model('main_mod');

		$data = $this->main_mod->msrquery('select id from m_bk_skip where value = ' . $post_array['value'] . ' and ("' . date("Y-m-d", strtotime(str_replace('/', '-', $post_array['startdate']))) . '" <= enddate and "' . date("Y-m-d", strtotime(str_replace('/', '-', $post_array['enddate']))) . '" >= startdate)')->row();


		if (isset($data->id)) {


			$this->form_validation->set_message('m_bk_skip', 'Data ketua BK sudah ada pada periode ini.');


			return false;
		} else {


			return true;
		}
	}

	public function is_duplicate_edit($post_array, $primary_key)
	{
		$this->load->model('main_mod');

		$data = $this->main_mod->msrquery('select id from m_bk_skip where id <> ' . $primary_key . ' and value = ' . $post_array['value'] . ' and ("' . date("Y-m-d", strtotime(str_replace('/', '-', $post_array['startdate']))) . '" <= enddate and "' . date("Y-m-d", strtotime(str_replace('/', '-', $post_array['enddate']))) . '" >= startdate)')->row();


		if (isset($data->id)) {


			$this->form_validation->set_message('m_bk_skip', 'Data ketua BK sudah ada pada periode ini.');


			return false;
		} else {


			return true;
		}
	}



	function json()
	{
		$this->load->library('datatables');
		$this->datatables->select('id,code_wilayah,code_bk_hkk,no_kta,(select concat(firstname," ",lastname) from user_profiles where user_id=person_id) as name,(select email from users where id=person_id) as email,(select dob from user_profiles where user_id=person_id) as dob');
		$this->datatables->from('members');

		/*$this->datatables->select('members.person_id as id,code_wilayah,code_bk_hkk,no_kta,(concat(firstname," ",lastname)) as name,email, dob');
        $this->datatables->from('members');
		$this->datatables->join('user_profiles', 'person_id=user_id');
		$this->datatables->join('users', 'users.id=person_id');*/
		return print_r($this->datatables->generate());
	}

	function export_user()
	{
		$idmember = $this->input->get('id') <> null ? $this->input->get('id') : "";
		$this->load->library('Libexcel', 'excel');
		/*$arrCol[] = array('urutan'=>1, 'nilai'=>'No.','fontsize'=> '12', 'bold'=>true, 'namanya'=>'nomor', 'format'=>'string');
		$arrCol[] = array('urutan'=>1, 'nilai'=>'Firstname.','fontsize'=> '12', 'bold'=>true,'namanya'=>'firstname','format'=>'string');
		$arrCol[] = array('urutan'=>1, 'nilai'=>'Lastname.','fontsize'=> '12', 'bold'=>true, 'namanya'=>'lastname','format'=>'string');
		$arrCol[] = array('urutan'=>1, 'nilai'=>'no_kta.','fontsize'=> '12', 'bold'=>true,'halign'=>'right', 'namanya'=>'no_kta','format'=>'string');
		$arrCol[] = array('urutan'=>1, 'nilai'=>'code_bk_hkk.','fontsize'=> '12', 'bold'=>true,'halign'=>'right', 'namanya'=>'code_bk_hkk','format'=>'string');
		$arrCol[] = array('urutan'=>1, 'nilai'=>'code_wilayah.','fontsize'=> '12', 'bold'=>true,'halign'=>'right', 'namanya'=>'code_wilayah','format'=>'string');
		$arrCol[] = array('urutan'=>1, 'nilai'=>'from_date.','fontsize'=> '12', 'bold'=>true,'halign'=>'right', 'namanya'=>'from_date','format'=>'datetime');
		$arrCol[] = array('urutan'=>1, 'nilai'=>'thru_date.','fontsize'=> '12', 'bold'=>true,'halign'=>'right', 'namanya'=>'thru_date','format'=>'datetime');
		$arrCol[] = array('urutan'=>1, 'nilai'=>'photo.','fontsize'=> '12', 'bold'=>true,'halign'=>'right', 'namanya'=>'photo','format'=>'string');
		$arrCol[] = array('urutan'=>1, 'nilai'=>'adddress.','fontsize'=> '12', 'bold'=>true,'halign'=>'right', 'namanya'=>'adddress','format'=>'string');
		$rsl = $this->members_model->get_export_by_id($idmember);
		//print_r($rsl);
		$arrExcel = array('sNAMESS'=>'detanto', 'sFILNAM'=>'Export','col'=>$arrCol, 'rsl'=>$rsl);
		$this->libexcel->bangunexcel($arrExcel);*/

		$rsl = $this->members_model->get_export_by_id($idmember);
		$objPHPExcel = new PHPExcel();
		//$objPHPExcel->getActiveSheet()->fromArray($rsl);

		$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A1', 'FIRSTNAME')
			->setCellValue('B1', 'LASTNAME')
			->setCellValue('C1', 'NO. KTA')
			->setCellValue('D1', 'BK')
			->setCellValue('E1', 'CABANG')
			->setCellValue('F1', 'FROM DATE')
			->setCellValue('G1', 'THRU DATE')
			->setCellValue('H1', 'PHOTO LINK')
			->setCellValue('I1', 'ADDRESS')
			->setCellValue('J1', 'TITLE');

		$rowCount = 2;

		$objPHPExcel->getActiveSheet()->SetCellValue('A' . $rowCount, $rsl['firstname']);
		$objPHPExcel->getActiveSheet()->SetCellValue('B' . $rowCount, $rsl['lastname']);
		$objPHPExcel->getActiveSheet()->SetCellValue('C' . $rowCount, str_pad($rsl['no_kta'], 6, '0', STR_PAD_LEFT));
		$objPHPExcel->getActiveSheet()->SetCellValue('D' . $rowCount, str_pad($rsl['code_bk_hkk'], 3, '0', STR_PAD_LEFT));
		$objPHPExcel->getActiveSheet()->SetCellValue('E' . $rowCount, str_pad($rsl['code_wilayah'], 4, '0', STR_PAD_LEFT));
		$objPHPExcel->getActiveSheet()->SetCellValue('F' . $rowCount, $rsl['from_date']);
		$objPHPExcel->getActiveSheet()->SetCellValue('G' . $rowCount, $rsl['thru_date']);
		$objPHPExcel->getActiveSheet()->SetCellValue('H' . $rowCount, base_url() . 'assets/uploads/' . $rsl['photo']);
		$objPHPExcel->getActiveSheet()->SetCellValue('I' . $rowCount, $rsl['address']);
		$objPHPExcel->getActiveSheet()->SetCellValue('J' . $rowCount, $rsl['title']);

		$sheet = $objPHPExcel->getActiveSheet();
		$cellIterator = $sheet->getRowIterator()->current()->getCellIterator();
		$cellIterator->setIterateOnlyExistingCells(true);
		/** @var PHPExcel_Cell $cell */
		foreach ($cellIterator as $cell) {
			$sheet->getColumnDimension($cell->getColumn())->setAutoSize(true);
		}

		$filename = str_pad($rsl['no_kta'], 6, '0', STR_PAD_LEFT) . '.xls';
		header('Content-Type: application/vnd.ms-excel'); //mime type
		header('Content-Disposition: attachment;filename="' . $filename . '"');
		header('Cache-Control: max-age=0'); //no cache
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
	}

	function export_member()
	{

		$akses = array("0", "2", "11");
		if (!in_array($this->session->userdata('type'), $akses)) {
			redirect('admin/dashboard');
			exit;
		}

		if ($_GET) {
			$search_name 			= 	$this->input->get('firstname');
			$search_email 			= 	$this->input->get('email');
			$search_kta 			= 	$this->input->get('username');
			$search_inst 			= 	$this->input->get('inst');
			$search_status 			= 	$this->input->get('status');
			$search_filter_cab 		= 	$this->input->get('filter_cab');
			//		$search_filter_bk 		= 	$this->input->get('filter_bk');
			$search_filter_hkk 		= 	$this->input->get('filter_hkk');
			$search_jenis_anggota 		= 	$this->input->get('jenis_anggota');
			$search_filter_kolektif 	= 	$this->input->get('filter_kolektif');
		}

		$search_data2 = array();
		if (isset($search_name) && $search_name != '') {
			$search_data2['REPLACE(concat(lower(COALESCE(firstname,"")),lower(COALESCE(lastname,"")))," ","") like "%' . str_replace(' ', '', strtolower($search_name)) . '%"'] = null;
		}
		if (isset($search_email) && $search_email != '') {
			$search_data2['REPLACE(lower(email)," ","")'] = str_replace(' ', '', strtolower($search_email));
		}
		if (isset($search_kta) && $search_kta != '') {
			$search_data2['username'] = ltrim($search_kta, '0');
		}
		if (isset($search_inst) && $search_inst != '') {
			$search_data2['inst'] = $search_inst;
		}
		if (isset($search_status) && $search_status != '') {
			$search_data2['status'] = $search_status;
		}
		if (isset($search_filter_cab) && $search_filter_cab != '') {
			$search_data2['filter_cab'] = $search_filter_cab;
		}
		/*		
		if(isset($search_filter_bk) && $search_filter_bk!=''){
			$search_data2['filter_bk'] = $search_filter_bk;
		}
*/
		if (isset($search_filter_hkk) && $search_filter_hkk != '') {
			$search_data2['filter_hkk'] = $search_filter_hkk;
		}
		if (isset($search_jenis_anggota) && $search_jenis_anggota != '') {
			$search_data2['jenis_anggota'] = $search_jenis_anggota;
		}
		if (isset($search_filter_kolektif) && $search_filter_kolektif != '') {
			$search_data2['kolektif_name_id'] = $search_filter_kolektif;
		}

		$this->load->library('Libexcel', 'excel');
		/*$arrCol[] = array('urutan'=>1, 'nilai'=>'No.','fontsize'=> '12', 'bold'=>true, 'namanya'=>'nomor', 'format'=>'string');
		$arrCol[] = array('urutan'=>1, 'nilai'=>'Firstname.','fontsize'=> '12', 'bold'=>true,'namanya'=>'firstname','format'=>'string');
		$arrCol[] = array('urutan'=>1, 'nilai'=>'Lastname.','fontsize'=> '12', 'bold'=>true, 'namanya'=>'lastname','format'=>'string');
		$arrCol[] = array('urutan'=>1, 'nilai'=>'no_kta.','fontsize'=> '12', 'bold'=>true,'halign'=>'right', 'namanya'=>'no_kta','format'=>'string');
		$arrCol[] = array('urutan'=>1, 'nilai'=>'code_bk_hkk.','fontsize'=> '12', 'bold'=>true,'halign'=>'right', 'namanya'=>'code_bk_hkk','format'=>'string');
		$arrCol[] = array('urutan'=>1, 'nilai'=>'code_wilayah.','fontsize'=> '12', 'bold'=>true,'halign'=>'right', 'namanya'=>'code_wilayah','format'=>'string');
		$arrCol[] = array('urutan'=>1, 'nilai'=>'from_date.','fontsize'=> '12', 'bold'=>true,'halign'=>'right', 'namanya'=>'from_date','format'=>'datetime');
		$arrCol[] = array('urutan'=>1, 'nilai'=>'thru_date.','fontsize'=> '12', 'bold'=>true,'halign'=>'right', 'namanya'=>'thru_date','format'=>'datetime');
		$arrCol[] = array('urutan'=>1, 'nilai'=>'photo.','fontsize'=> '12', 'bold'=>true,'halign'=>'right', 'namanya'=>'photo','format'=>'string');
		$arrCol[] = array('urutan'=>1, 'nilai'=>'adddress.','fontsize'=> '12', 'bold'=>true,'halign'=>'right', 'namanya'=>'adddress','format'=>'string');
		$rsl = $this->members_model->get_export_by_id($idmember);
		//print_r($rsl);
		$arrExcel = array('sNAMESS'=>'detanto', 'sFILNAM'=>'Export','col'=>$arrCol, 'rsl'=>$rsl);
		$this->libexcel->bangunexcel($arrExcel);*/

		$this->load->model('main_mod');
		$admin = $this->main_mod->msrquery('select code_bk_hkk as bk,code_wilayah as wil from admin where id = ' . $this->session->userdata('admin_id'))->result();
		$bk  = (isset($admin[0]->bk) ? $admin[0]->bk : "");
		$wil = (isset($admin[0]->wil) ? $admin[0]->wil : "");
		if (strpos($bk, ',') !== false) {
			$bk = explode(",", $bk);
		}
		if (strpos($wil, ',') !== false) {
			$wil = explode(",", $wil);
		}
		//$is_kolektif = (($this->session->userdata('type')=="11" || $this->session->userdata('type')=="15")?true:false);
		$is_kolektif = (($this->session->userdata('type') == "11") ? true : false); //$this->session->userdata('admin_id')=='682' || $this->session->userdata('admin_id')=='679' || $this->session->userdata('admin_id')=='681'

		$rslx = $this->members_model->get_export_member($search_data2, $bk, $wil, $is_kolektif);
		//	$data["rslx"] = $rslx ;
		//	$this->load->view('admin/Coba', $data); 
		$objPHPExcel = new PHPExcel();
		//$objPHPExcel->getActiveSheet()->fromArray($rsl);

		$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A1', 'FIRSTNAME')
			->setCellValue('B1', 'LASTNAME')
			->setCellValue('C1', 'NO. VA')
			->setCellValue('D1', 'NO. KTA')
			->setCellValue('E1', 'FROM DATE')
			->setCellValue('F1', 'THRU DATE')
			->setCellValue('G1', 'BK')
			->setCellValue('H1', 'NAMA BK')
			->setCellValue('I1', 'CABANG')
			->setCellValue('J1', 'NAMA CABANG')
			->setCellValue('K1', 'EMAIL')
			->setCellValue('L1', 'MOBILEPHONE')
			->setCellValue('M1', 'ADDRESS')
			->setCellValue('N1', 'CITY')
			->setCellValue('O1', 'PROVINCE')
			->setCellValue('P1', 'ZIPCODE')
			->setCellValue('Q1', 'JENIS ANGGOTA');

		$rowCount = 2;

		foreach ($rslx as $rsl) {
			$objPHPExcel->getActiveSheet()->SetCellValue('A' . $rowCount, $rsl->firstname);
			$objPHPExcel->getActiveSheet()->SetCellValue('B' . $rowCount, $rsl->lastname);
			//$objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $rsl->va);
			$objPHPExcel->getActiveSheet()->setCellValueExplicit('C' . $rowCount, $rsl->va, PHPExcel_Cell_DataType::TYPE_STRING);
			$objPHPExcel->getActiveSheet()->SetCellValue('D' . $rowCount, str_pad($rsl->no_kta, 6, '0', STR_PAD_LEFT));
			$objPHPExcel->getActiveSheet()->SetCellValue('E' . $rowCount, $rsl->from_date);
			$objPHPExcel->getActiveSheet()->SetCellValue('F' . $rowCount, $rsl->thru_date);
			$objPHPExcel->getActiveSheet()->SetCellValue('G' . $rowCount, str_pad($rsl->code_bk_hkk, 3, '0', STR_PAD_LEFT));
			$objPHPExcel->getActiveSheet()->SetCellValue('H' . $rowCount, $rsl->nama_bk);
			$objPHPExcel->getActiveSheet()->SetCellValue('I' . $rowCount, str_pad($rsl->code_wilayah, 4, '0', STR_PAD_LEFT));
			$objPHPExcel->getActiveSheet()->SetCellValue('J' . $rowCount, $rsl->nama_wilayah);
			$objPHPExcel->getActiveSheet()->SetCellValue('K' . $rowCount, $rsl->email);
			$objPHPExcel->getActiveSheet()->SetCellValue('L' . $rowCount, $rsl->mobilephone);
			$objPHPExcel->getActiveSheet()->SetCellValue('M' . $rowCount, $rsl->address);
			$objPHPExcel->getActiveSheet()->SetCellValue('N' . $rowCount, $rsl->city);
			$objPHPExcel->getActiveSheet()->SetCellValue('O' . $rowCount, $rsl->province);
			$objPHPExcel->getActiveSheet()->SetCellValue('P' . $rowCount, $rsl->zipcode);

			$jns = '01 Anggota Muda';
			if ($rsl->jenis_anggota == '01' || $rsl->jenis_anggota == '1') $jns = '01 Anggota Muda';
			else if ($rsl->jenis_anggota == '02' || $rsl->jenis_anggota == '2') $jns = '02 Anggota Biasa';
			else if ($rsl->jenis_anggota == '03' || $rsl->jenis_anggota == '3') $jns = '03 Anggota Luar Biasa';
			else if ($rsl->jenis_anggota == '04' || $rsl->jenis_anggota == '4') $jns = '04 Anggota kehormatan';

			$objPHPExcel->getActiveSheet()->SetCellValue('Q' . $rowCount, $jns);
			$rowCount++;
		}
		$sheet = $objPHPExcel->getActiveSheet();
		$cellIterator = $sheet->getRowIterator()->current()->getCellIterator();
		$cellIterator->setIterateOnlyExistingCells(true);
		/** @var PHPExcel_Cell $cell */
		foreach ($cellIterator as $cell) {
			$sheet->getColumnDimension($cell->getColumn())->setAutoSize(true);
		}


		$filename = date('Y-m-d') . '.xls';
		header('Content-Type: application/vnd.ms-excel'); //mime type
		header('Content-Disposition: attachment;filename="' . $filename . '"');
		header('Cache-Control: max-age=0'); //no cache
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
	}

	function export_finance()
	{

		$akses = array("0", "8");
		if (!in_array($this->session->userdata('type'), $akses)) {
			redirect('admin/dashboard');
			exit;
		}

		if ($_GET) {
			$search_name 	= 	$this->input->get('firstname');
			$search_email 	= 	$this->input->get('email');
			$search_type 	= 	$this->input->get('filter_type');
			$search_status 	= 	$this->input->get('filter_status');
			$search_va 		= 	$this->input->get('va');
			$search_filter_kolektif 	= 	$this->input->get('filter_kolektif');
		}

		$search_data2 = array();
		if (isset($search_name) && $search_name != '') {
			$search_data2['REPLACE(concat(lower(COALESCE(firstname,"")),lower(COALESCE(lastname,"")))," ","")'] = str_replace(' ', '', strtolower($search_name));
		}
		if (isset($search_email) && $search_email != '') {
			$search_data2['REPLACE(lower(email)," ","")'] = str_replace(' ', '', strtolower($search_email));
		}
		if (isset($search_type) && $search_type != '') {
			$search_data2['filter_type'] = $search_type;
		}
		if (isset($search_status) && $search_status != '') {
			$search_data2['user_transfer.status'] = $search_status;
		}
		if (isset($search_va) && $search_va != '') {
			$search_data2['user_profiles.va'] = $search_va;
		}

		if (isset($search_filter_kolektif) && $search_filter_kolektif != '') {
			$search_data2['kolektif_name_id'] = $search_filter_kolektif;
		}

		$this->load->library('Libexcel', 'excel');

		$this->load->model('main_mod');
		$count = $this->members_model->get_count_export_finance($search_data2);
		if ($count < 5000) {

			$rslx = $this->members_model->get_export_finance($search_data2);
			$objPHPExcel = new PHPExcel();

			$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('A1', 'Tipe')
				->setCellValue('B1', 'VA')
				->setCellValue('C1', 'is upload to mandiri?')
				->setCellValue('D1', 'Nama')
				->setCellValue('E1', 'Email')
				->setCellValue('F1', 'Jenis kelamin')
				->setCellValue('G1', 'Tanggal Lahir')
				->setCellValue('H1', 'SIP')
				->setCellValue('I1', 'Period')
				->setCellValue('J1', 'Kolektif')
				->setCellValue('K1', 'Deskripsi')
				->setCellValue('L1', 'Total Transfer')
				//->setCellValue('M1', 'Bukti Transfer')
				->setCellValue('M1', 'Status');

			$rowCount = 2;

			foreach ($rslx as $rsl) {
				$paytype = '';
				if ($rsl->paytype == '0' || $rsl->paytype == '1')
					$paytype = 'REG';
				else if ($rsl->paytype == '2')
					$paytype = 'HER';
				else if ($rsl->paytype == '3')
					$paytype = 'FAIP';
				else if ($rsl->paytype == '4')
					$paytype = 'FAIP';
				else if ($rsl->paytype == '5')
					$paytype = 'STRI';

				$objPHPExcel->getActiveSheet()->SetCellValue('A' . $rowCount, $paytype);
				$objPHPExcel->getActiveSheet()->setCellValueExplicit('B' . $rowCount, $rsl->va, PHPExcel_Cell_DataType::TYPE_STRING);
				$objPHPExcel->getActiveSheet()->SetCellValue('C' . $rowCount, (($rsl->is_upload_mandiri == '1') ? 'Yes' : 'No'));
				$objPHPExcel->getActiveSheet()->SetCellValue('D' . $rowCount, $rsl->firstname . ' ' . $rsl->lastname);
				$objPHPExcel->getActiveSheet()->SetCellValue('E' . $rowCount, $rsl->email);
				$objPHPExcel->getActiveSheet()->SetCellValue('F' . $rowCount, $rsl->gender);
				$objPHPExcel->getActiveSheet()->SetCellValue('G' . $rowCount, $rsl->dob);
				$objPHPExcel->getActiveSheet()->SetCellValue('H' . $rowCount, (isset($rsl->sip_lic_num) ? $rsl->sip_lic_num . ' (' . $rsl->sip_startyear . ' sampai ' . $rsl->sip_endyear . ')' : ""));
				$objPHPExcel->getActiveSheet()->SetCellValue('I' . $rowCount, $rsl->from_date . ' - ' . $rsl->thru_date);
				$objPHPExcel->getActiveSheet()->SetCellValue('J' . $rowCount, $rsl->kolektif_name);
				$objPHPExcel->getActiveSheet()->SetCellValue('K' . $rowCount, $rsl->paydesc);
				$objPHPExcel->getActiveSheet()->SetCellValue('L' . $rowCount, $rsl->paysukarelatotal);

				$status = '';
				if ($rsl->paystatus == '1') {
					$status = 'Valid';
				} elseif ($rsl->paystatus == '0') {
					$status = 'Please Confirm';
				} elseif ($rsl->paystatus == '2') {
					$status = 'Not Valid';
				}

				$objPHPExcel->getActiveSheet()->SetCellValue('M' . $rowCount, $status);
				$rowCount++;
			}
			$sheet = $objPHPExcel->getActiveSheet();
			$cellIterator = $sheet->getRowIterator()->current()->getCellIterator();
			$cellIterator->setIterateOnlyExistingCells(true);

			foreach ($cellIterator as $cell) {
				$sheet->getColumnDimension($cell->getColumn())->setAutoSize(true);
			}

			$filename = date('Y-m-d') . '.xls';
			header('Content-Type: application/vnd.ms-excel'); //mime type
			header('Content-Disposition: attachment;filename="' . $filename . '"');
			header('Cache-Control: max-age=0'); //no cache
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
			$objWriter->save('php://output');
		} else {
			echo $count . '<script>alert("data terlalu banyak, silahkan filter terlebih dahulu");</script>';
		}
	}

	public function export_bk()
	{
		$this->load->library('Libexcel', 'excel');
		$this->load->model('main_mod');
		$rsl = $this->main_mod->msrquery("SELECT a.value,a.name,
(select count(x.id) from members x join users y on x.person_id=y.id where username<>'' and TRIM(LEADING '0' FROM a.value)=TRIM(LEADING '0' FROM code_bk_hkk) and thru_date>=curdate() ) as aktif ,
(select count(x.id) from members x join users y on x.person_id=y.id where username<>'' and TRIM(LEADING '0' FROM a.value)=TRIM(LEADING '0' FROM code_bk_hkk) and thru_date<curdate() ) as tidakaktif ,
(select count(x.id) from members x join users y on x.person_id=y.id where username<>'' and TRIM(LEADING '0' FROM a.value)=TRIM(LEADING '0' FROM code_bk_hkk) ) as total
FROM `m_bk` a WHERE 1")->result();

		$objPHPExcel = new PHPExcel();

		$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A1', 'KODE BK')
			->setCellValue('B1', 'NAMA BK')
			->setCellValue('C1', 'AKTIF')
			->setCellValue('D1', 'TIDAK AKTIF')
			->setCellValue('E1', 'TOTAL');

		$objPHPExcel->getActiveSheet()->getStyle('A1:E1')->getFont()->setBold(true);

		$rowCount = 2;
		$aktif = 0;
		$tidakaktif = 0;
		$total = 0;
		foreach ($rsl as $val) {
			$objPHPExcel->getActiveSheet()->SetCellValue('A' . $rowCount, $val->value);
			$objPHPExcel->getActiveSheet()->SetCellValue('B' . $rowCount, $val->name);
			$objPHPExcel->getActiveSheet()->SetCellValue('C' . $rowCount, $val->aktif);
			$objPHPExcel->getActiveSheet()->SetCellValue('D' . $rowCount, $val->tidakaktif);
			$objPHPExcel->getActiveSheet()->SetCellValue('E' . $rowCount, $val->total);

			$aktif += $val->aktif;
			$tidakaktif += $val->tidakaktif;
			$total += $val->total;

			$rowCount++;
		}

		$objPHPExcel->getActiveSheet()->SetCellValue('A' . $rowCount, 'TOTAL');
		$objPHPExcel->setActiveSheetIndex(0)->mergeCells('A' . $rowCount . ':B' . $rowCount);
		$objPHPExcel->getActiveSheet()->SetCellValue('C' . $rowCount, $aktif);
		$objPHPExcel->getActiveSheet()->SetCellValue('D' . $rowCount, $tidakaktif);
		$objPHPExcel->getActiveSheet()->SetCellValue('E' . $rowCount, $total);
		$objPHPExcel->getActiveSheet()->getStyle('A' . $rowCount . ':E' . $rowCount)->getFont()->setBold(true);


		/*$objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, str_pad($rsl['no_kta'], 6, '0', STR_PAD_LEFT));
		$objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, str_pad($rsl['code_bk_hkk'], 3, '0', STR_PAD_LEFT));
		$objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, str_pad($rsl['code_wilayah'], 4, '0', STR_PAD_LEFT));
		$objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, $rsl['from_date']);
		$objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, $rsl['thru_date']);
		$objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, base_url().'assets/uploads/'.$rsl['photo']);
		$objPHPExcel->getActiveSheet()->SetCellValue('I'.$rowCount, $rsl['address']);
		$objPHPExcel->getActiveSheet()->SetCellValue('J'.$rowCount, $rsl['title']);*/

		$sheet = $objPHPExcel->getActiveSheet();
		$cellIterator = $sheet->getRowIterator()->current()->getCellIterator();
		$cellIterator->setIterateOnlyExistingCells(true);

		foreach ($cellIterator as $cell) {
			$sheet->getColumnDimension($cell->getColumn())->setAutoSize(true);
		}

		$filename = date('Y-m-d') . '.xls';
		header('Content-Type: application/vnd.ms-excel'); //mime type
		header('Content-Disposition: attachment;filename="BK_' . $filename . '"');
		header('Cache-Control: max-age=0'); //no cache
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
	}

	public function export_bk_jenis()
	{
		$this->load->library('Libexcel', 'excel');
		$this->load->model('main_mod');
		$rsl = $this->main_mod->msrquery("SELECT a.value,a.name,
(select count(x.id) from members x join users y on x.person_id=y.id where username<>'' and TRIM(LEADING '0' FROM a.value)=TRIM(LEADING '0' FROM code_bk_hkk) and thru_date>=curdate()  and LPAD( jenis_anggota, 2, '0' )='01') as muda_aktif ,
(select count(x.id) from members x join users y on x.person_id=y.id where username<>'' and TRIM(LEADING '0' FROM a.value)=TRIM(LEADING '0' FROM code_bk_hkk) and thru_date>=curdate()  and LPAD( jenis_anggota, 2, '0' )='02') as biasa_aktif ,
(select count(x.id) from members x join users y on x.person_id=y.id where username<>'' and TRIM(LEADING '0' FROM a.value)=TRIM(LEADING '0' FROM code_bk_hkk) and thru_date>=curdate()  and LPAD( jenis_anggota, 2, '0' )='03') as luar_biasa_aktif ,
(select count(x.id) from members x join users y on x.person_id=y.id where username<>'' and TRIM(LEADING '0' FROM a.value)=TRIM(LEADING '0' FROM code_bk_hkk) and thru_date>=curdate() and LPAD( jenis_anggota, 2, '0' )='04') as kehormatan_aktif ,
(select count(x.id) from members x join users y on x.person_id=y.id where username<>'' and TRIM(LEADING '0' FROM a.value)=TRIM(LEADING '0' FROM code_bk_hkk) and thru_date<curdate() and LPAD( jenis_anggota, 2, '0' )='01') as muda_tidak_aktif ,
(select count(x.id) from members x join users y on x.person_id=y.id where username<>'' and TRIM(LEADING '0' FROM a.value)=TRIM(LEADING '0' FROM code_bk_hkk) and thru_date<curdate() and LPAD( jenis_anggota, 2, '0' )='02') as biasa_tidak_aktif ,
(select count(x.id) from members x join users y on x.person_id=y.id where username<>'' and TRIM(LEADING '0' FROM a.value)=TRIM(LEADING '0' FROM code_bk_hkk) and thru_date<curdate() and LPAD( jenis_anggota, 2, '0' )='03') as luar_biasa_tidak_aktif ,
(select count(x.id) from members x join users y on x.person_id=y.id where username<>'' and TRIM(LEADING '0' FROM a.value)=TRIM(LEADING '0' FROM code_bk_hkk) and thru_date<curdate() and LPAD( jenis_anggota, 2, '0' )='04') as kehormatan_tidak_aktif ,
(select count(x.id) from members x join users y on x.person_id=y.id where username<>'' and TRIM(LEADING '0' FROM a.value)=TRIM(LEADING '0' FROM code_bk_hkk)) as total
FROM `m_bk` a WHERE 1")->result();

		$objPHPExcel = new PHPExcel();

		$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A1', 'KODE BK')
			->setCellValue('B1', 'NAMA BK')
			->setCellValue('C1', 'MUDA AKTIF')
			->setCellValue('D1', 'BIASA AKTIF')
			->setCellValue('E1', 'LUARBIASA AKTIF')
			->setCellValue('F1', 'KEHORMATAN AKTIF')
			->setCellValue('G1', 'MUDA TIDAK AKTIF')
			->setCellValue('H1', 'BIASA TIDAK AKTIF')
			->setCellValue('I1', 'LUARBIASA TIDAK AKTIF')
			->setCellValue('J1', 'KEHORMATAN TIDAK AKTIF')
			->setCellValue('K1', 'TOTAL');

		$objPHPExcel->getActiveSheet()->getStyle('A1:K1')->getFont()->setBold(true);

		$rowCount = 2;
		$muda_aktif = 0;
		$biasa_aktif = 0;
		$luar_biasa_aktif = 0;
		$kehormatan_aktif = 0;
		$muda_tidak_aktif = 0;
		$biasa_tidak_aktif = 0;
		$luar_biasa_tidak_aktif = 0;
		$kehormatan_tidak_aktif = 0;
		$total = 0;
		foreach ($rsl as $val) {
			$objPHPExcel->getActiveSheet()->SetCellValue('A' . $rowCount, $val->value);
			$objPHPExcel->getActiveSheet()->SetCellValue('B' . $rowCount, $val->name);
			$objPHPExcel->getActiveSheet()->SetCellValue('C' . $rowCount, $val->muda_aktif);
			$objPHPExcel->getActiveSheet()->SetCellValue('D' . $rowCount, $val->biasa_aktif);
			$objPHPExcel->getActiveSheet()->SetCellValue('E' . $rowCount, $val->luar_biasa_aktif);
			$objPHPExcel->getActiveSheet()->SetCellValue('F' . $rowCount, $val->kehormatan_aktif);
			$objPHPExcel->getActiveSheet()->SetCellValue('G' . $rowCount, $val->muda_tidak_aktif);
			$objPHPExcel->getActiveSheet()->SetCellValue('H' . $rowCount, $val->biasa_tidak_aktif);
			$objPHPExcel->getActiveSheet()->SetCellValue('I' . $rowCount, $val->luar_biasa_tidak_aktif);
			$objPHPExcel->getActiveSheet()->SetCellValue('J' . $rowCount, $val->kehormatan_tidak_aktif);
			$objPHPExcel->getActiveSheet()->SetCellValue('K' . $rowCount, $val->total);

			$muda_aktif += $val->muda_aktif;
			$biasa_aktif += $val->biasa_aktif;
			$luar_biasa_aktif += $val->luar_biasa_aktif;
			$kehormatan_aktif += $val->kehormatan_aktif;
			$muda_tidak_aktif += $val->muda_tidak_aktif;
			$biasa_tidak_aktif += $val->biasa_tidak_aktif;
			$luar_biasa_tidak_aktif += $val->luar_biasa_tidak_aktif;
			$kehormatan_tidak_aktif += $val->kehormatan_tidak_aktif;
			$total += $val->total;

			$rowCount++;
		}

		$objPHPExcel->getActiveSheet()->SetCellValue('A' . $rowCount, 'TOTAL');
		$objPHPExcel->setActiveSheetIndex(0)->mergeCells('A' . $rowCount . ':B' . $rowCount);
		$objPHPExcel->getActiveSheet()->SetCellValue('C' . $rowCount, $muda_aktif);
		$objPHPExcel->getActiveSheet()->SetCellValue('D' . $rowCount, $biasa_aktif);
		$objPHPExcel->getActiveSheet()->SetCellValue('E' . $rowCount, $luar_biasa_aktif);
		$objPHPExcel->getActiveSheet()->SetCellValue('F' . $rowCount, $kehormatan_aktif);
		$objPHPExcel->getActiveSheet()->SetCellValue('G' . $rowCount, $muda_tidak_aktif);
		$objPHPExcel->getActiveSheet()->SetCellValue('H' . $rowCount, $biasa_tidak_aktif);
		$objPHPExcel->getActiveSheet()->SetCellValue('I' . $rowCount, $luar_biasa_tidak_aktif);
		$objPHPExcel->getActiveSheet()->SetCellValue('J' . $rowCount, $kehormatan_tidak_aktif);
		$objPHPExcel->getActiveSheet()->SetCellValue('K' . $rowCount, $total);
		$objPHPExcel->getActiveSheet()->getStyle('A' . $rowCount . ':K' . $rowCount)->getFont()->setBold(true);


		/*$objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, str_pad($rsl['no_kta'], 6, '0', STR_PAD_LEFT));
		$objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, str_pad($rsl['code_bk_hkk'], 3, '0', STR_PAD_LEFT));
		$objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, str_pad($rsl['code_wilayah'], 4, '0', STR_PAD_LEFT));
		$objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, $rsl['from_date']);
		$objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, $rsl['thru_date']);
		$objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, base_url().'assets/uploads/'.$rsl['photo']);
		$objPHPExcel->getActiveSheet()->SetCellValue('I'.$rowCount, $rsl['address']);
		$objPHPExcel->getActiveSheet()->SetCellValue('J'.$rowCount, $rsl['title']);*/

		$sheet = $objPHPExcel->getActiveSheet();
		$cellIterator = $sheet->getRowIterator()->current()->getCellIterator();
		$cellIterator->setIterateOnlyExistingCells(true);

		foreach ($cellIterator as $cell) {
			$sheet->getColumnDimension($cell->getColumn())->setAutoSize(true);
		}

		$filename = date('Y-m-d') . '.xls';
		header('Content-Type: application/vnd.ms-excel'); //mime type
		header('Content-Disposition: attachment;filename="BK_' . $filename . '"');
		header('Cache-Control: max-age=0'); //no cache
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
	}

	public function export_bk_2()
	{
		$this->load->library('Libexcel', 'excel');
		$this->load->model('main_mod');
		$rsl = $this->main_mod->msrquery("SELECT a.value,a.name,

(select count(members.id) from members where TRIM(LEADING '0' FROM a.value)=TRIM(LEADING '0' FROM code_bk_hkk) and
date(case when created_at<IFNULL((select modifieddate from user_transfer b where members.person_id=b.user_id and pay_type=1 and b.status=1 order by modifieddate desc limit 1),'1970-01-01') then (select modifieddate from user_transfer b where members.person_id=b.user_id and pay_type=1 and b.status=1 order by modifieddate desc limit 1) else created_at end) between '" . date('Y') . "-01-01' and '" . date('Y') . "-01-31')
as reg1,

(select count(members.id) from members where TRIM(LEADING '0' FROM a.value)=TRIM(LEADING '0' FROM code_bk_hkk) and
date(case when (select createddate from log_her_kta b where members.person_id=b.user_id order by b.id desc limit 1)<(select modifieddate from user_transfer b where members.person_id=b.user_id and pay_type=2 and b.status=1 order by modifieddate desc limit 1) then (select modifieddate from user_transfer b where members.person_id=b.user_id and pay_type=2 and b.status=1 order by modifieddate desc limit 1) else (select createddate from log_her_kta b where members.person_id=b.user_id order by b.id desc limit 1) end) between '" . date('Y') . "-01-01' and '" . date('Y') . "-01-31')
as her1,

(select count(members.id) from members where TRIM(LEADING '0' FROM a.value)=TRIM(LEADING '0' FROM code_bk_hkk) and
date(case when created_at<IFNULL((select modifieddate from user_transfer b where members.person_id=b.user_id and pay_type=1 and b.status=1 order by modifieddate desc limit 1),'1970-01-01') then (select modifieddate from user_transfer b where members.person_id=b.user_id and pay_type=1 and b.status=1 order by modifieddate desc limit 1) else created_at end) between '" . date('Y') . "-02-01' and '" . date('Y') . "-02-31')
as reg2,

(select count(members.id) from members where TRIM(LEADING '0' FROM a.value)=TRIM(LEADING '0' FROM code_bk_hkk) and
date(case when (select createddate from log_her_kta b where members.person_id=b.user_id order by b.id desc limit 1)<(select modifieddate from user_transfer b where members.person_id=b.user_id and pay_type=2 and b.status=1 order by modifieddate desc limit 1) then (select modifieddate from user_transfer b where members.person_id=b.user_id and pay_type=2 and b.status=1 order by modifieddate desc limit 1) else (select createddate from log_her_kta b where members.person_id=b.user_id order by b.id desc limit 1) end) between '" . date('Y') . "-02-01' and '" . date('Y') . "-02-31')
as her2,

(select count(members.id) from members where TRIM(LEADING '0' FROM a.value)=TRIM(LEADING '0' FROM code_bk_hkk) and
date(case when created_at<IFNULL((select modifieddate from user_transfer b where members.person_id=b.user_id and pay_type=1 and b.status=1 order by modifieddate desc limit 1),'1970-01-01') then (select modifieddate from user_transfer b where members.person_id=b.user_id and pay_type=1 and b.status=1 order by modifieddate desc limit 1) else created_at end) between '" . date('Y') . "-03-01' and '" . date('Y') . "-03-31')
as reg3,

(select count(members.id) from members where TRIM(LEADING '0' FROM a.value)=TRIM(LEADING '0' FROM code_bk_hkk) and
date(case when (select createddate from log_her_kta b where members.person_id=b.user_id order by b.id desc limit 1)<(select modifieddate from user_transfer b where members.person_id=b.user_id and pay_type=2 and b.status=1 order by modifieddate desc limit 1) then (select modifieddate from user_transfer b where members.person_id=b.user_id and pay_type=2 and b.status=1 order by modifieddate desc limit 1) else (select createddate from log_her_kta b where members.person_id=b.user_id order by b.id desc limit 1) end) between '" . date('Y') . "-03-01' and '" . date('Y') . "-03-31')
as her3

FROM `m_bk` a WHERE 1")->result();

		$objPHPExcel = new PHPExcel();

		$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('F1', '1')
			->setCellValue('H1', '2')
			->setCellValue('J1', '3')
			->setCellValue('L1', '4')
			->setCellValue('N1', '5')
			->setCellValue('P1', '6')
			->setCellValue('R1', '7')
			->setCellValue('T1', '8')
			->setCellValue('V1', '9')
			->setCellValue('X1', '10')
			->setCellValue('Z1', '11')
			->setCellValue('AB1', '12')
		;
		$objPHPExcel->setActiveSheetIndex(0)->mergeCells('F1:G1');
		$objPHPExcel->setActiveSheetIndex(0)->mergeCells('H1:I1');
		$objPHPExcel->setActiveSheetIndex(0)->mergeCells('J1:K1');
		$objPHPExcel->setActiveSheetIndex(0)->mergeCells('L1:M1');
		$objPHPExcel->setActiveSheetIndex(0)->mergeCells('N1:O1');
		$objPHPExcel->setActiveSheetIndex(0)->mergeCells('P1:Q1');
		$objPHPExcel->setActiveSheetIndex(0)->mergeCells('R1:S1');
		$objPHPExcel->setActiveSheetIndex(0)->mergeCells('T1:U1');
		$objPHPExcel->setActiveSheetIndex(0)->mergeCells('V1:W1');
		$objPHPExcel->setActiveSheetIndex(0)->mergeCells('X1:Y1');
		$objPHPExcel->setActiveSheetIndex(0)->mergeCells('Z1:AA1');
		$objPHPExcel->setActiveSheetIndex(0)->mergeCells('AB1:AC1');

		$style = array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			)
		);
		$objPHPExcel->getActiveSheet()->getStyle('F1:AC2')->applyFromArray($style);
		$objPHPExcel->getActiveSheet()->getStyle('F1:AC2')->getFont()->setBold(true);


		$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A2', 'KODE BK')
			->setCellValue('B2', 'NAMA BK')
			//->setCellValue('C2', 'AKTIF')
			//->setCellValue('D2', 'TIDAK AKTIF')
			//->setCellValue('E2', 'TOTAL')

			->setCellValue('F2', 'REG')
			->setCellValue('G2', 'HER')
			->setCellValue('H2', 'REG')
			->setCellValue('I2', 'HER')
			->setCellValue('J2', 'REG')
			->setCellValue('K2', 'HER')
			->setCellValue('L2', 'REG')
			->setCellValue('M2', 'HER')
			->setCellValue('N2', 'REG')
			->setCellValue('O2', 'HER')
			->setCellValue('P2', 'REG')
			->setCellValue('Q2', 'HER')
			->setCellValue('R2', 'REG')
			->setCellValue('S2', 'HER')
			->setCellValue('T2', 'REG')
			->setCellValue('U2', 'HER')
			->setCellValue('V2', 'REG')
			->setCellValue('W2', 'HER')
			->setCellValue('X2', 'REG')
			->setCellValue('Y2', 'HER')
			->setCellValue('Z2', 'REG')
			->setCellValue('AA2', 'HER')
			->setCellValue('AB2', 'REG')
			->setCellValue('AC2', 'HER')
		;

		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setVisible(false);
		$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setVisible(false);
		$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setVisible(false);

		$objPHPExcel->getActiveSheet()->getStyle('A2:E2')->getFont()->setBold(true);

		$rowCount = 3;
		$aktif = 0;
		$tidakaktif = 0;
		$total = 0;
		foreach ($rsl as $val) {
			$objPHPExcel->getActiveSheet()->SetCellValue('A' . $rowCount, $val->value);
			$objPHPExcel->getActiveSheet()->SetCellValue('B' . $rowCount, $val->name);
			//$objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $val->aktif);
			//$objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $val->tidakaktif);
			//$objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $val->total);

			$objPHPExcel->getActiveSheet()->SetCellValue('F' . $rowCount, $val->reg1);
			$objPHPExcel->getActiveSheet()->SetCellValue('G' . $rowCount, $val->her1);
			$objPHPExcel->getActiveSheet()->SetCellValue('H' . $rowCount, $val->reg2);
			$objPHPExcel->getActiveSheet()->SetCellValue('I' . $rowCount, $val->her2);
			$objPHPExcel->getActiveSheet()->SetCellValue('J' . $rowCount, $val->reg3);
			$objPHPExcel->getActiveSheet()->SetCellValue('K' . $rowCount, $val->her3);

			//$aktif += $val->aktif;
			//$tidakaktif += $val->tidakaktif;
			//$total += $val->total;

			$rowCount++;
		}

		$objPHPExcel->getActiveSheet()->SetCellValue('A' . $rowCount, 'TOTAL');
		$objPHPExcel->setActiveSheetIndex(0)->mergeCells('A' . $rowCount . ':B' . $rowCount);
		//$objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $aktif);
		//$objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $tidakaktif);
		//$objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $total);
		$objPHPExcel->getActiveSheet()->getStyle('A' . $rowCount . ':E' . $rowCount)->getFont()->setBold(true);


		/*$objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, str_pad($rsl['no_kta'], 6, '0', STR_PAD_LEFT));
		$objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, str_pad($rsl['code_bk_hkk'], 3, '0', STR_PAD_LEFT));
		$objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, str_pad($rsl['code_wilayah'], 4, '0', STR_PAD_LEFT));
		$objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, $rsl['from_date']);
		$objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, $rsl['thru_date']);
		$objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, base_url().'assets/uploads/'.$rsl['photo']);
		$objPHPExcel->getActiveSheet()->SetCellValue('I'.$rowCount, $rsl['address']);
		$objPHPExcel->getActiveSheet()->SetCellValue('J'.$rowCount, $rsl['title']);*/

		$sheet = $objPHPExcel->getActiveSheet();
		$cellIterator = $sheet->getRowIterator()->current()->getCellIterator();
		$cellIterator->setIterateOnlyExistingCells(true);

		foreach ($cellIterator as $cell) {
			$sheet->getColumnDimension($cell->getColumn())->setAutoSize(true);
		}

		$filename = date('Y-m-d') . '.xls';
		header('Content-Type: application/vnd.ms-excel'); //mime type
		header('Content-Disposition: attachment;filename="BK_' . $filename . '"');
		header('Cache-Control: max-age=0'); //no cache
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
	}

	public function export_lap_cab()
	{
		$this->load->library('Libexcel', 'excel');
		$list_lap = $this->members_model->ambil_data_wilayah_all();
		$data["list_lap"] = $list_lap;
		$this->load->view('admin/list_lap_cetak', $data);
	}

	public function export_lap_cab_1()
	{
		$kowil = (trim($this->input->post('id', true))) ? trim($this->input->post('id', true)) : '';
		$nacab = (trim($this->input->post('nama', true))) ? trim($this->input->post('nama', true)) : '';
		$jml = (trim($this->input->post('jml', true))) ? trim($this->input->post('jml', true)) : '';
		$data_wil = $this->members_model->ambil_data_cabang_per_wilayah($kowil);
		$data["data_wil"] = $data_wil;
		$data["kowil"] = $kowil;
		$data["nawil"] = $nacab;
		$data["jml"] = $jml;
		$this->load->view('admin/list_lap_wil_cetak', $data);
	}

	public function export_lap_all()
	{
		$list_lap = $this->members_model->ambil_data_wilayah_all_view();
		$data["list_lap_all"] = $list_lap;
		$this->load->view('admin/list_lap_all_cetak', $data);
	}

	public function export_cab()
	{
		$this->load->library('Libexcel', 'excel');
		$this->load->model('main_mod');
		$rsl = $this->main_mod->msrquery("SELECT a.value,a.name,
(select count(x.id) from members x join users y on x.person_id=y.id where username<>'' and a.value=code_wilayah and thru_date>=curdate() ) as aktif ,
(select count(x.id) from members x join users y on x.person_id=y.id where username<>'' and a.value=code_wilayah and thru_date<curdate() ) as tidakaktif ,
(select count(x.id) from members x join users y on x.person_id=y.id where username<>'' and a.value=code_wilayah) as total
FROM `m_cab` a WHERE length(value)>2")->result(); //and (person_id<>1 and person_id<>35448)

		$objPHPExcel = new PHPExcel();

		$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A1', 'KODE CABANG')
			->setCellValue('B1', 'NAMA CABANG')
			->setCellValue('C1', 'AKTIF')
			->setCellValue('D1', 'TIDAK AKTIF')
			->setCellValue('E1', 'TOTAL');

		$objPHPExcel->getActiveSheet()->getStyle('A1:E1')->getFont()->setBold(true);

		$rowCount = 2;
		$aktif = 0;
		$tidakaktif = 0;
		$total = 0;
		foreach ($rsl as $val) {
			$objPHPExcel->getActiveSheet()->SetCellValue('A' . $rowCount, $val->value);
			$objPHPExcel->getActiveSheet()->SetCellValue('B' . $rowCount, $val->name);
			$objPHPExcel->getActiveSheet()->SetCellValue('C' . $rowCount, $val->aktif);
			$objPHPExcel->getActiveSheet()->SetCellValue('D' . $rowCount, $val->tidakaktif);
			$objPHPExcel->getActiveSheet()->SetCellValue('E' . $rowCount, $val->total);

			$aktif += $val->aktif;
			$tidakaktif += $val->tidakaktif;
			$total += $val->total;

			$rowCount++;
		}

		$objPHPExcel->getActiveSheet()->SetCellValue('A' . $rowCount, 'TOTAL');
		$objPHPExcel->setActiveSheetIndex(0)->mergeCells('A' . $rowCount . ':B' . $rowCount);
		$objPHPExcel->getActiveSheet()->SetCellValue('C' . $rowCount, $aktif);
		$objPHPExcel->getActiveSheet()->SetCellValue('D' . $rowCount, $tidakaktif);
		$objPHPExcel->getActiveSheet()->SetCellValue('E' . $rowCount, $total);
		$objPHPExcel->getActiveSheet()->getStyle('A' . $rowCount . ':E' . $rowCount)->getFont()->setBold(true);


		/*$objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, str_pad($rsl['no_kta'], 6, '0', STR_PAD_LEFT));
		$objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, str_pad($rsl['code_bk_hkk'], 3, '0', STR_PAD_LEFT));
		$objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, str_pad($rsl['code_wilayah'], 4, '0', STR_PAD_LEFT));
		$objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, $rsl['from_date']);
		$objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, $rsl['thru_date']);
		$objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, base_url().'assets/uploads/'.$rsl['photo']);
		$objPHPExcel->getActiveSheet()->SetCellValue('I'.$rowCount, $rsl['address']);
		$objPHPExcel->getActiveSheet()->SetCellValue('J'.$rowCount, $rsl['title']);*/

		$sheet = $objPHPExcel->getActiveSheet();
		$cellIterator = $sheet->getRowIterator()->current()->getCellIterator();
		$cellIterator->setIterateOnlyExistingCells(true);

		foreach ($cellIterator as $cell) {
			$sheet->getColumnDimension($cell->getColumn())->setAutoSize(true);
		}

		$filename = date('Y-m-d') . '.xls';
		header('Content-Type: application/vnd.ms-excel'); //mime type
		header('Content-Disposition: attachment;filename="Cabang_' . $filename . '"');
		header('Cache-Control: max-age=0'); //no cache
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
	}

	public function view_all()
	{
		$list_lap = $this->members_model->ambil_data_wilayah_all_view();
		$data["list_lap_all"] = $list_lap;
		$this->load->view('admin/list_lap_all_view', $data);
	}

	public function lap_cab_1()
	{

		$kowil = (trim($this->input->post('id', true))) ? trim($this->input->post('id', true)) : '';
		$nacab = (trim($this->input->post('nama', true))) ? trim($this->input->post('nama', true)) : '';
		$jml = (trim($this->input->post('jml', true))) ? trim($this->input->post('jml', true)) : '';
		$data_wil = $this->members_model->ambil_data_cabang_per_wilayah($kowil);
		$data["data_wil"] = $data_wil;
		$data["kowil"] = $kowil;
		$data["nacab"] = $nacab;
		$data["jml"] = $jml;
		$this->load->view('admin/list_lap_wil', $data);
		//    echo $kowil ; exit() ;			

	}


	public function lap_cab_2()
	{
		$data["list_lap"] = $jumlah = $this->members_model->ambil_data_wilayah_all();
		$this->load->view('admin/list_lap', $data);
	}

	public function lap_cab()
	{
		$jumlah = $this->members_model->ambil_data_wilayah_all();
		foreach ($jumlah as $row) {
			$id = $row->id;
			$fiel = 'wil_id';
			$jcabang = $this->members_model->hitung_jumlah_cabangnya($row->kowil);
			$jcabang = $jcabang - 1;
			$janggota_aktif = $this->members_model->janggota_aktif($row->kowil, $fiel);
			$janggota_nonaktif = $this->members_model->janggota_nonaktif($row->kowil, $fiel);
			$jumlah =  $janggota_aktif +  $janggota_nonaktif;

			//echo $row->kowil.' = '.$jcabang.' - '.$janggota_aktif.' - '.$janggota_nonaktif.'<br>' ; 

			$data_update_lap = [
				'jum_cab'   => $jcabang,
				'aktif'     => $janggota_aktif,
				'non_aktif' => $janggota_nonaktif,
				'jumlah'    => $jumlah

			];
			$this->members_model->update_data_lap_wilayah($id, $data_update_lap);

			$cab_wil = $this->members_model->ambil_data_cabang_per_wilayah($row->kowil);
			foreach ($cab_wil as $roww) {
				$id = $roww->id;
				$fiel = 'code_wilayah';
				if (strlen($roww->kocab) != 2) {
					$janggota_aktif_cab = $this->members_model->janggota_aktif($roww->kocab, $fiel);
					$janggota_nonaktif_cab = $this->members_model->janggota_nonaktif($roww->kocab, $fiel);
					$jumlahh =  $janggota_aktif_cab +  $janggota_nonaktif_cab;

					$data_update_lap_cab = [

						'aktif'     => $janggota_aktif_cab,
						'non_aktif' => $janggota_nonaktif_cab,
						'jumlah'    => $jumlahh

					];
					$this->members_model->update_data_lap_cab($id, $data_update_lap_cab);
				}
			}
		}
		$data["list_lap"] = $jumlah = $this->members_model->ambil_data_wilayah_all(); // $this->members_model->ambil_data_cabang_all() ;
		$this->load->view('admin/list_lap', $data);

		//		exit() ;   	
	}

	public function export_cab_jenis()
	{
		$this->load->library('Libexcel', 'excel');
		$this->load->model('main_mod');
		$rsl = $this->main_mod->msrquery("SELECT a.value,a.name,
(select count(x.id) from members x join users y on x.person_id=y.id where username<>'' and a.value=code_wilayah and thru_date>=curdate() and LPAD( jenis_anggota, 2, '0' )='01' ) as muda_aktif ,
(select count(x.id) from members x join users y on x.person_id=y.id where username<>'' and a.value=code_wilayah and thru_date>=curdate() and LPAD( jenis_anggota, 2, '0' )='02' ) as biasa_aktif ,
(select count(x.id) from members x join users  y on x.person_id=y.id where username<>'' and a.value=code_wilayah and thru_date>=curdate() and LPAD( jenis_anggota, 2, '0' )='03' ) as luar_biasa_aktif ,
(select count(x.id) from members x join users y on x.person_id=y.id where username<>'' and a.value=code_wilayah and thru_date>=curdate() and LPAD( jenis_anggota, 2, '0' )='04' ) as kehormatan_aktif ,
(select count(x.id) from members x join users y on x.person_id=y.id where username<>'' and a.value=code_wilayah and thru_date<curdate() and LPAD( jenis_anggota, 2, '0' )='01' ) as muda_tidak_aktif ,
(select count(x.id) from members x join users y on x.person_id=y.id where username<>'' and a.value=code_wilayah and thru_date<curdate() and LPAD( jenis_anggota, 2, '0' )='02' ) as biasa_tidak_aktif ,
(select count(x.id) from members x join users y on x.person_id=y.id where username<>'' and a.value=code_wilayah and thru_date<curdate() and LPAD( jenis_anggota, 2, '0' )='03' ) as luar_biasa_tidak_aktif ,
(select count(x.id) from members x join users y on x.person_id=y.id where username<>'' and a.value=code_wilayah and thru_date<curdate() and LPAD( jenis_anggota, 2, '0' )='04' ) as kehormatan_tidak_aktif ,
(select count(x.id) from members x join users y on x.person_id=y.id where username<>'' and a.value=code_wilayah) as total
FROM `m_cab` a WHERE length(value)>2")->result(); //and (person_id<>1 and person_id<>35448)

		$objPHPExcel = new PHPExcel();

		$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A1', 'KODE CABANG')
			->setCellValue('B1', 'NAMA CABANG')
			->setCellValue('C1', 'MUDA AKTIF')
			->setCellValue('D1', 'BIASA AKTIF')
			->setCellValue('E1', 'LUARBIASA AKTIF')
			->setCellValue('F1', 'KEHORMATAN AKTIF')
			->setCellValue('G1', 'MUDA TIDAK AKTIF')
			->setCellValue('H1', 'BIASA TIDAK AKTIF')
			->setCellValue('I1', 'LUARBIASA TIDAK AKTIF')
			->setCellValue('J1', 'KEHORMATAN TIDAK AKTIF')
			->setCellValue('K1', 'TOTAL');

		$objPHPExcel->getActiveSheet()->getStyle('A1:K1')->getFont()->setBold(true);

		$rowCount = 2;
		$muda_aktif = 0;
		$biasa_aktif = 0;
		$luar_biasa_aktif = 0;
		$kehormatan_aktif = 0;
		$muda_tidak_aktif = 0;
		$biasa_tidak_aktif = 0;
		$luar_biasa_tidak_aktif = 0;
		$kehormatan_tidak_aktif = 0;
		$total = 0;
		foreach ($rsl as $val) {
			$objPHPExcel->getActiveSheet()->SetCellValue('A' . $rowCount, $val->value);
			$objPHPExcel->getActiveSheet()->SetCellValue('B' . $rowCount, $val->name);
			$objPHPExcel->getActiveSheet()->SetCellValue('C' . $rowCount, $val->muda_aktif);
			$objPHPExcel->getActiveSheet()->SetCellValue('D' . $rowCount, $val->biasa_aktif);
			$objPHPExcel->getActiveSheet()->SetCellValue('E' . $rowCount, $val->luar_biasa_aktif);
			$objPHPExcel->getActiveSheet()->SetCellValue('F' . $rowCount, $val->kehormatan_aktif);
			$objPHPExcel->getActiveSheet()->SetCellValue('G' . $rowCount, $val->muda_tidak_aktif);
			$objPHPExcel->getActiveSheet()->SetCellValue('H' . $rowCount, $val->biasa_tidak_aktif);
			$objPHPExcel->getActiveSheet()->SetCellValue('I' . $rowCount, $val->luar_biasa_tidak_aktif);
			$objPHPExcel->getActiveSheet()->SetCellValue('J' . $rowCount, $val->kehormatan_tidak_aktif);
			$objPHPExcel->getActiveSheet()->SetCellValue('K' . $rowCount, $val->total);

			$muda_aktif += $val->muda_aktif;
			$biasa_aktif += $val->biasa_aktif;
			$luar_biasa_aktif += $val->luar_biasa_aktif;
			$kehormatan_aktif += $val->kehormatan_aktif;
			$muda_tidak_aktif += $val->muda_tidak_aktif;
			$biasa_tidak_aktif += $val->biasa_tidak_aktif;
			$luar_biasa_tidak_aktif += $val->luar_biasa_tidak_aktif;
			$kehormatan_tidak_aktif += $val->kehormatan_tidak_aktif;
			$total += $val->total;

			$rowCount++;
		}

		$objPHPExcel->getActiveSheet()->SetCellValue('A' . $rowCount, 'TOTAL');
		$objPHPExcel->setActiveSheetIndex(0)->mergeCells('A' . $rowCount . ':B' . $rowCount);
		$objPHPExcel->getActiveSheet()->SetCellValue('C' . $rowCount, $muda_aktif);
		$objPHPExcel->getActiveSheet()->SetCellValue('D' . $rowCount, $biasa_aktif);
		$objPHPExcel->getActiveSheet()->SetCellValue('E' . $rowCount, $luar_biasa_aktif);
		$objPHPExcel->getActiveSheet()->SetCellValue('F' . $rowCount, $kehormatan_aktif);
		$objPHPExcel->getActiveSheet()->SetCellValue('G' . $rowCount, $muda_tidak_aktif);
		$objPHPExcel->getActiveSheet()->SetCellValue('H' . $rowCount, $biasa_tidak_aktif);
		$objPHPExcel->getActiveSheet()->SetCellValue('I' . $rowCount, $luar_biasa_tidak_aktif);
		$objPHPExcel->getActiveSheet()->SetCellValue('J' . $rowCount, $kehormatan_tidak_aktif);
		$objPHPExcel->getActiveSheet()->SetCellValue('K' . $rowCount, $total);
		$objPHPExcel->getActiveSheet()->getStyle('A' . $rowCount . ':K' . $rowCount)->getFont()->setBold(true);


		/*$objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, str_pad($rsl['no_kta'], 6, '0', STR_PAD_LEFT));
		$objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, str_pad($rsl['code_bk_hkk'], 3, '0', STR_PAD_LEFT));
		$objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, str_pad($rsl['code_wilayah'], 4, '0', STR_PAD_LEFT));
		$objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, $rsl['from_date']);
		$objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, $rsl['thru_date']);
		$objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, base_url().'assets/uploads/'.$rsl['photo']);
		$objPHPExcel->getActiveSheet()->SetCellValue('I'.$rowCount, $rsl['address']);
		$objPHPExcel->getActiveSheet()->SetCellValue('J'.$rowCount, $rsl['title']);*/

		$sheet = $objPHPExcel->getActiveSheet();
		$cellIterator = $sheet->getRowIterator()->current()->getCellIterator();
		$cellIterator->setIterateOnlyExistingCells(true);

		foreach ($cellIterator as $cell) {
			$sheet->getColumnDimension($cell->getColumn())->setAutoSize(true);
		}

		$filename = date('Y-m-d') . '.xls';
		header('Content-Type: application/vnd.ms-excel'); //mime type
		header('Content-Disposition: attachment;filename="Cabang_' . $filename . '"');
		header('Cache-Control: max-age=0'); //no cache
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
	}

	public function export_cab_2()
	{
		$this->load->library('Libexcel', 'excel');
		$this->load->model('main_mod');
		$month = date('m');
		$rsl = $this->main_mod->msrquery("SELECT a.value,a.name,

(select count(members.id) from members where TRIM(LEADING '0' FROM a.value)=TRIM(LEADING '0' FROM code_wilayah) and
date(case when created_at<IFNULL((select modifieddate from user_transfer b where members.person_id=b.user_id and pay_type=1 and b.status=1 order by modifieddate desc limit 1),'1970-01-01') then (select modifieddate from user_transfer b where members.person_id=b.user_id and pay_type=1 and b.status=1 order by modifieddate desc limit 1) else created_at end) between '" . date('Y') . "-" . $month . "-01' and '" . date('Y') . "-" . $month . "-31')
as reg,

(select count(members.id) from members where TRIM(LEADING '0' FROM a.value)=TRIM(LEADING '0' FROM code_wilayah) and
date(case when (select createddate from log_her_kta b where members.person_id=b.user_id order by b.id desc limit 1)<(select modifieddate from user_transfer b where members.person_id=b.user_id and pay_type=2 and b.status=1 order by modifieddate desc limit 1) then (select modifieddate from user_transfer b where members.person_id=b.user_id and pay_type=2 and b.status=1 order by modifieddate desc limit 1) else (select createddate from log_her_kta b where members.person_id=b.user_id order by b.id desc limit 1) end) between '" . date('Y') . "-" . $month . "-01' and '" . date('Y') . "-" . $month . "-31')
as her

FROM `m_cab` a WHERE length(value)>2")->result();

		$objPHPExcel = new PHPExcel();

		$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('F1', $month)
		;
		$objPHPExcel->setActiveSheetIndex(0)->mergeCells('F1:G1');

		$style = array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			)
		);
		$objPHPExcel->getActiveSheet()->getStyle('F1:G2')->applyFromArray($style);
		$objPHPExcel->getActiveSheet()->getStyle('F1:G2')->getFont()->setBold(true);

		$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A2', 'KODE CABANG')
			->setCellValue('B2', 'NAMA CABANG')
			//->setCellValue('C2', 'AKTIF')
			//->setCellValue('D2', 'TIDAK AKTIF')
			//->setCellValue('E2', 'TOTAL')
			->setCellValue('F2', 'REG')
			->setCellValue('G2', 'HER')
		;

		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setVisible(false);
		$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setVisible(false);
		$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setVisible(false);

		$objPHPExcel->getActiveSheet()->getStyle('A2:E2')->getFont()->setBold(true);

		$rowCount = 3;
		$aktif = 0;
		$tidakaktif = 0;
		$total = 0;
		foreach ($rsl as $val) {
			$objPHPExcel->getActiveSheet()->SetCellValue('A' . $rowCount, $val->value);
			$objPHPExcel->getActiveSheet()->SetCellValue('B' . $rowCount, $val->name);
			//$objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $val->aktif);
			//$objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $val->tidakaktif);
			//$objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $val->total);

			$objPHPExcel->getActiveSheet()->SetCellValue('F' . $rowCount, $val->reg);
			$objPHPExcel->getActiveSheet()->SetCellValue('G' . $rowCount, $val->her);

			//$aktif += $val->aktif;
			//$tidakaktif += $val->tidakaktif;
			//$total += $val->total;

			$rowCount++;
		}

		$objPHPExcel->getActiveSheet()->SetCellValue('A' . $rowCount, 'TOTAL');
		$objPHPExcel->setActiveSheetIndex(0)->mergeCells('A' . $rowCount . ':B' . $rowCount);
		//$objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $aktif);
		//$objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $tidakaktif);
		//$objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $total);
		$objPHPExcel->getActiveSheet()->getStyle('A' . $rowCount . ':E' . $rowCount)->getFont()->setBold(true);


		/*$objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, str_pad($rsl['no_kta'], 6, '0', STR_PAD_LEFT));
		$objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, str_pad($rsl['code_bk_hkk'], 3, '0', STR_PAD_LEFT));
		$objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, str_pad($rsl['code_wilayah'], 4, '0', STR_PAD_LEFT));
		$objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, $rsl['from_date']);
		$objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, $rsl['thru_date']);
		$objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, base_url().'assets/uploads/'.$rsl['photo']);
		$objPHPExcel->getActiveSheet()->SetCellValue('I'.$rowCount, $rsl['address']);
		$objPHPExcel->getActiveSheet()->SetCellValue('J'.$rowCount, $rsl['title']);*/

		$sheet = $objPHPExcel->getActiveSheet();
		$cellIterator = $sheet->getRowIterator()->current()->getCellIterator();
		$cellIterator->setIterateOnlyExistingCells(true);

		foreach ($cellIterator as $cell) {
			$sheet->getColumnDimension($cell->getColumn())->setAutoSize(true);
		}

		$filename = date('Y-m-d') . '.xls';
		header('Content-Type: application/vnd.ms-excel'); //mime type
		header('Content-Disposition: attachment;filename="Cabang_' . $filename . '"');
		header('Cache-Control: max-age=0'); //no cache
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
	}

	public function export_faip()
	{
		$this->load->library('Libexcel', 'excel');
		$this->load->model('main_mod');

		$search_cab 	= 	$this->input->get('filter_cab');
		$search_bk 		= 	$this->input->get('filter_bk');

		$f_bk = "and 1=1";

		$search_bk = explode(',', $search_bk);

		if (isset($search_bk[0])) {
			if ($search_bk[0] != '')
				$f_bk = 'and a.value in (' . implode(",", $search_bk) . ')';
		}

		$f_cab = "and 1=1";

		$search_cab = explode(',', $search_cab);

		if (isset($search_cab[0])) {
			if ($search_cab[0] != '') {
				$txt = '';
				foreach ($search_cab as $val) {
					if ($txt == '')
						$txt = $txt . 'LPAD(code_wilayah, 4, "0") like "' . $val . '%"';
					else
						$txt = $txt . ' or LPAD(code_wilayah, 4, "0") like "' . $val . '%"';
				}
				$f_cab = 'and x.user_id in (select person_id from members where ' . $txt . ')';
			}
		}

		$rsl = $this->main_mod->msrquery("SELECT
a.value,a.name,

(select count(x.id) from user_faip x where TRIM(LEADING '0' FROM a.value)=TRIM(LEADING '0' FROM x.bidang) and x.status_faip>0 $f_cab) as all_status,
(select count(x.id) from user_faip x where TRIM(LEADING '0' FROM a.value)=TRIM(LEADING '0' FROM x.bidang) and x.status_faip=1 $f_cab) as submit ,
(select count(x.id) from user_faip x where TRIM(LEADING '0' FROM a.value)=TRIM(LEADING '0' FROM x.bidang) and x.status_faip=2 $f_cab) as verif,
(select count(x.id) from user_faip x where TRIM(LEADING '0' FROM a.value)=TRIM(LEADING '0' FROM x.bidang) and x.status_faip=3 $f_cab) as reject,
(select count(x.id) from user_faip x where TRIM(LEADING '0' FROM a.value)=TRIM(LEADING '0' FROM x.bidang) and (x.status_faip=4 or x.status_faip=5) $f_cab) as fee,
(select count(x.id) from user_faip x where TRIM(LEADING '0' FROM a.value)=TRIM(LEADING '0' FROM x.bidang) and x.status_faip=6 $f_cab) as score,
(select count(x.id) from user_faip x where TRIM(LEADING '0' FROM a.value)=TRIM(LEADING '0' FROM x.bidang) and x.status_faip=7 $f_cab) as preview,
(select count(x.id) from user_faip x where TRIM(LEADING '0' FROM a.value)=TRIM(LEADING '0' FROM x.bidang) and x.status_faip=8 $f_cab) as interview,
(select count(x.id) from user_faip x where TRIM(LEADING '0' FROM a.value)=TRIM(LEADING '0' FROM x.bidang) and x.status_faip=9 $f_cab) as final,
(select count(x.id) from user_faip x where TRIM(LEADING '0' FROM a.value)=TRIM(LEADING '0' FROM x.bidang) and x.status_faip=10 $f_cab) as fee2,
(select count(x.id) from user_faip x where TRIM(LEADING '0' FROM a.value)=TRIM(LEADING '0' FROM x.bidang) and x.status_faip=11 $f_cab) as paid,
(select count(x.id) from user_faip x where TRIM(LEADING '0' FROM a.value)=TRIM(LEADING '0' FROM x.bidang) and x.status_faip=12 $f_cab) as aktif

FROM `m_bk` a WHERE 1=1 $f_bk")->result();

		$objPHPExcel = new PHPExcel();

		$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A1', 'KODE BK')
			->setCellValue('B1', 'NAMA BK')
			->setCellValue('C1', 'ALL STATUS')
			->setCellValue('D1', 'TO V&V (LSKI)')
			->setCellValue('E1', 'RETURNED TO APL')
			->setCellValue('F1', 'TO CHECK UM (FIN)')
			->setCellValue('G1', 'TO SCORE (MUK)')
			->setCellValue('H1', 'TO INTERVIEW (MUK)')
			->setCellValue('I1', 'FINAL SCORE (MUK)')
			->setCellValue('J1', 'TO CHECK SIP (FIN)')
			->setCellValue('K1', 'SIP TO PRINT (LSKI)')
			->setCellValue('L1', 'SIP TO ACTIVATE (LSKI)');

		//$objPHPExcel->getActiveSheet()->getStyle('A1:S1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()->getStyle('A1:L1')->getFont()->setBold(true);

		$rowCount = 2;
		$total_jml_bk = 0;
		$total_jml_fin = 0;
		$total_jml_lski = 0;
		$total[0] = 0;
		$total[1] = 0;
		$total[2] = 0;
		$total[3] = 0;
		$total[4] = 0;
		$total[5] = 0;
		$total[6] = 0;
		$total[7] = 0;
		$total[8] = 0;
		$total[9] = 0;
		$total[10] = 0;
		$total[11] = 0;
		$total[12] = 0;
		$total[13] = 0;
		$total[14] = 0;
		$total[15] = 0;
		$total[16] = 0;
		foreach ($rsl as $val) {

			$jml_bk = $val->score + $val->preview + $val->interview;
			$jml_fin = $val->fee + $val->fee2;
			$jml_lski = ($val->submit - $val->verif - $val->reject) + $val->paid;

			$total_jml_bk += $val->score + $val->preview + $val->interview;
			$total_jml_fin += $val->fee + $val->fee2;
			$total_jml_lski += ($val->submit - $val->verif - $val->reject) + $val->paid;

			$total[0] += $val->all_status;
			$total[1] += $val->submit;
			$total[2] += $val->verif;
			$total[3] += $val->reject;
			$total[4] += $val->fee;
			$total[5] += $val->score;
			$total[6] += $val->preview;
			$total[7] += $val->interview;
			$total[8] += $val->final;
			$total[9] += $val->fee2;
			$total[10] += $val->paid;
			$total[11] += $val->aktif;
			$total[12] += $val->submit;
			$total[13] += $jml_bk;
			$total[14] += $jml_fin;
			$total[15] += $val->paid;
			$total[16] += $jml_lski;

			$objPHPExcel->getActiveSheet()->SetCellValue('A' . $rowCount, $val->value);
			$objPHPExcel->getActiveSheet()->SetCellValue('B' . $rowCount, $val->name);
			$objPHPExcel->getActiveSheet()->SetCellValue('C' . $rowCount, $val->all_status);
			$objPHPExcel->getActiveSheet()->SetCellValue('D' . $rowCount, $val->submit);
			//$objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $val->verif);
			$objPHPExcel->getActiveSheet()->SetCellValue('E' . $rowCount, $val->reject);
			$objPHPExcel->getActiveSheet()->SetCellValue('F' . $rowCount, $val->fee);
			$objPHPExcel->getActiveSheet()->SetCellValue('G' . $rowCount, $val->score);
			//$objPHPExcel->getActiveSheet()->SetCellValue('I'.$rowCount, $val->preview);
			$objPHPExcel->getActiveSheet()->SetCellValue('H' . $rowCount, $val->interview);
			$objPHPExcel->getActiveSheet()->SetCellValue('I' . $rowCount, $val->final);
			$objPHPExcel->getActiveSheet()->SetCellValue('J' . $rowCount, $val->fee2);
			$objPHPExcel->getActiveSheet()->SetCellValue('K' . $rowCount, $val->paid);
			$objPHPExcel->getActiveSheet()->SetCellValue('L' . $rowCount, $val->aktif);



			$rowCount++;
		}

		$objPHPExcel->getActiveSheet()->SetCellValue('A' . $rowCount, 'TOTAL');
		$objPHPExcel->setActiveSheetIndex(0)->mergeCells('A' . $rowCount . ':B' . $rowCount);
		$objPHPExcel->getActiveSheet()->SetCellValue('C' . $rowCount, $total[0]);
		$objPHPExcel->getActiveSheet()->SetCellValue('D' . $rowCount, $total[1]);
		//$objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $total[2]);
		$objPHPExcel->getActiveSheet()->SetCellValue('E' . $rowCount, $total[3]);
		$objPHPExcel->getActiveSheet()->SetCellValue('F' . $rowCount, $total[4]);
		$objPHPExcel->getActiveSheet()->SetCellValue('G' . $rowCount, $total[5]);
		//$objPHPExcel->getActiveSheet()->SetCellValue('I'.$rowCount, $total[6]);
		$objPHPExcel->getActiveSheet()->SetCellValue('H' . $rowCount, $total[7]);
		$objPHPExcel->getActiveSheet()->SetCellValue('I' . $rowCount, $total[8]);
		$objPHPExcel->getActiveSheet()->SetCellValue('J' . $rowCount, $total[9]);
		$objPHPExcel->getActiveSheet()->SetCellValue('K' . $rowCount, $total[10]);
		$objPHPExcel->getActiveSheet()->SetCellValue('L' . $rowCount, $total[11]);

		/*$objPHPExcel->getActiveSheet()->SetCellValue('O'.$rowCount, $total[12]);
		$objPHPExcel->getActiveSheet()->SetCellValue('P'.$rowCount, $total[13]);
		$objPHPExcel->getActiveSheet()->SetCellValue('Q'.$rowCount, $total[14]);
		$objPHPExcel->getActiveSheet()->SetCellValue('R'.$rowCount, $total[15]);
		$objPHPExcel->getActiveSheet()->SetCellValue('S'.$rowCount, $total[16]);*/

		//$objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount.':S'.$rowCount)->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()->getStyle('A' . $rowCount . ':N' . $rowCount)->getFont()->setBold(true);


		/*$objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, str_pad($rsl['no_kta'], 6, '0', STR_PAD_LEFT));
		$objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, str_pad($rsl['code_bk_hkk'], 3, '0', STR_PAD_LEFT));
		$objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, str_pad($rsl['code_wilayah'], 4, '0', STR_PAD_LEFT));
		$objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, $rsl['from_date']);
		$objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, $rsl['thru_date']);
		$objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, base_url().'assets/uploads/'.$rsl['photo']);
		$objPHPExcel->getActiveSheet()->SetCellValue('I'.$rowCount, $rsl['address']);
		$objPHPExcel->getActiveSheet()->SetCellValue('J'.$rowCount, $rsl['title']);*/

		$sheet = $objPHPExcel->getActiveSheet();
		$cellIterator = $sheet->getRowIterator()->current()->getCellIterator();
		$cellIterator->setIterateOnlyExistingCells(true);

		foreach ($cellIterator as $cell) {
			$sheet->getColumnDimension($cell->getColumn())->setAutoSize(true);
		}

		$filename = date('Y-m-d') . '.xls';
		header('Content-Type: application/vnd.ms-excel'); //mime type
		header('Content-Disposition: attachment;filename="FAIP_' . $filename . '"');
		header('Cache-Control: max-age=0'); //no cache
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
	}

	public function export_pkb()
	{
		$this->load->library('Libexcel', 'excel');
		$this->load->model('main_mod');

		$search_cab 	= 	$this->input->get('filter_cab');
		$search_bk 		= 	$this->input->get('filter_bk');

		$f_bk = "and 1=1";

		$search_bk = explode(',', $search_bk);

		if (isset($search_bk[0])) {
			if ($search_bk[0] != '')
				$f_bk = 'and a.value in (' . implode(",", $search_bk) . ')';
		}

		$f_cab = "and 1=1";

		$search_cab = explode(',', $search_cab);

		if (isset($search_cab[0])) {
			if ($search_cab[0] != '') {
				$txt = '';
				foreach ($search_cab as $val) {
					if ($txt == '')
						$txt = $txt . 'LPAD(code_wilayah, 4, "0") like "' . $val . '%"';
					else
						$txt = $txt . ' or LPAD(code_wilayah, 4, "0") like "' . $val . '%"';
				}
				$f_cab = 'and x.user_id in (select person_id from members where ' . $txt . ')';
			}
		}

		$rsl = $this->main_mod->msrquery("SELECT
a.value,a.name,

(select count(x.id) from user_pkb x where TRIM(LEADING '0' FROM a.value)=TRIM(LEADING '0' FROM x.bidang) and x.status_pkb>0 $f_cab) as all_status,
(select count(x.id) from user_pkb x where TRIM(LEADING '0' FROM a.value)=TRIM(LEADING '0' FROM x.bidang) and x.status_pkb=1 $f_cab) as submit ,
(select count(x.id) from user_pkb x where TRIM(LEADING '0' FROM a.value)=TRIM(LEADING '0' FROM x.bidang) and x.status_pkb=2 $f_cab) as verif,
(select count(x.id) from user_pkb x where TRIM(LEADING '0' FROM a.value)=TRIM(LEADING '0' FROM x.bidang) and x.status_pkb=3 $f_cab) as reject,
(select count(x.id) from user_pkb x where TRIM(LEADING '0' FROM a.value)=TRIM(LEADING '0' FROM x.bidang) and (x.status_pkb=4 or x.status_pkb=5) $f_cab) as fee,
(select count(x.id) from user_pkb x where TRIM(LEADING '0' FROM a.value)=TRIM(LEADING '0' FROM x.bidang) and x.status_pkb=6 $f_cab) as score,
(select count(x.id) from user_pkb x where TRIM(LEADING '0' FROM a.value)=TRIM(LEADING '0' FROM x.bidang) and x.status_pkb=7 $f_cab) as preview,
(select count(x.id) from user_pkb x where TRIM(LEADING '0' FROM a.value)=TRIM(LEADING '0' FROM x.bidang) and x.status_pkb=8 $f_cab) as interview,
(select count(x.id) from user_pkb x where TRIM(LEADING '0' FROM a.value)=TRIM(LEADING '0' FROM x.bidang) and x.status_pkb=9 $f_cab) as final,
(select count(x.id) from user_pkb x where TRIM(LEADING '0' FROM a.value)=TRIM(LEADING '0' FROM x.bidang) and x.status_pkb=10 $f_cab) as fee2,
(select count(x.id) from user_pkb x where TRIM(LEADING '0' FROM a.value)=TRIM(LEADING '0' FROM x.bidang) and x.status_pkb=11 $f_cab) as paid,
(select count(x.id) from user_pkb x where TRIM(LEADING '0' FROM a.value)=TRIM(LEADING '0' FROM x.bidang) and x.status_pkb=12 $f_cab) as aktif,
(select count(x.id) from user_pkb x where TRIM(LEADING '0' FROM a.value)=TRIM(LEADING '0' FROM x.bidang) and x.status_pkb=13 $f_cab) as done

FROM `m_bk` a WHERE 1=1 $f_bk")->result();

		$objPHPExcel = new PHPExcel();

		$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A1', 'KODE BK')
			->setCellValue('B1', 'NAMA BK')
			->setCellValue('C1', 'ALL STATUS')
			->setCellValue('D1', 'TO V&V (LSKI)')
			->setCellValue('E1', 'RETURNED TO APL')
			->setCellValue('F1', 'TO CHECK UM (FIN)')
			->setCellValue('G1', 'TO SCORE (MUK)')
			->setCellValue('H1', 'TO INTERVIEW (MUK)')
			->setCellValue('I1', 'FINAL SCORE (MUK)')
			->setCellValue('J1', 'TO CHECK SIP (FIN)')
			->setCellValue('K1', 'SIP TO PRINT (LSKI)')
			->setCellValue('L1', 'SIP TO ACTIVATE (LSKI)')
			->setCellValue('M1', 'DONE');

		//$objPHPExcel->getActiveSheet()->getStyle('A1:S1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()->getStyle('A1:M1')->getFont()->setBold(true);

		$rowCount = 2;
		$total_jml_bk = 0;
		$total_jml_fin = 0;
		$total_jml_lski = 0;
		$total[0] = 0;
		$total[1] = 0;
		$total[2] = 0;
		$total[3] = 0;
		$total[4] = 0;
		$total[5] = 0;
		$total[6] = 0;
		$total[7] = 0;
		$total[8] = 0;
		$total[9] = 0;
		$total[10] = 0;
		$total[11] = 0;
		$total[12] = 0;
		$total[13] = 0;
		$total[14] = 0;
		$total[15] = 0;
		$total[16] = 0;
		$total[17] = 0;
		foreach ($rsl as $val) {

			$jml_bk = $val->score + $val->preview + $val->interview;
			$jml_fin = $val->fee + $val->fee2;
			$jml_lski = ($val->submit - $val->verif - $val->reject) + $val->paid;

			$total_jml_bk += $val->score + $val->preview + $val->interview;
			$total_jml_fin += $val->fee + $val->fee2;
			$total_jml_lski += ($val->submit - $val->verif - $val->reject) + $val->paid;

			$total[0] += $val->all_status;
			$total[1] += $val->submit;
			$total[2] += $val->verif;
			$total[3] += $val->reject;
			$total[4] += $val->fee;
			$total[5] += $val->score;
			$total[6] += $val->preview;
			$total[7] += $val->interview;
			$total[8] += $val->final;
			$total[9] += $val->fee2;
			$total[10] += $val->paid;
			$total[11] += $val->aktif;
			$total[12] += $val->submit;
			$total[17] += $val->done;
			$total[13] += $jml_bk;
			$total[14] += $jml_fin;
			$total[15] += $val->paid;
			$total[16] += $jml_lski;

			$objPHPExcel->getActiveSheet()->SetCellValue('A' . $rowCount, $val->value);
			$objPHPExcel->getActiveSheet()->SetCellValue('B' . $rowCount, $val->name);
			$objPHPExcel->getActiveSheet()->SetCellValue('C' . $rowCount, $val->all_status);
			$objPHPExcel->getActiveSheet()->SetCellValue('D' . $rowCount, $val->submit);
			//$objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $val->verif);
			$objPHPExcel->getActiveSheet()->SetCellValue('E' . $rowCount, $val->reject);
			$objPHPExcel->getActiveSheet()->SetCellValue('F' . $rowCount, $val->fee);
			$objPHPExcel->getActiveSheet()->SetCellValue('G' . $rowCount, $val->score);
			//$objPHPExcel->getActiveSheet()->SetCellValue('I'.$rowCount, $val->preview);
			$objPHPExcel->getActiveSheet()->SetCellValue('H' . $rowCount, $val->interview);
			$objPHPExcel->getActiveSheet()->SetCellValue('I' . $rowCount, $val->final);
			$objPHPExcel->getActiveSheet()->SetCellValue('J' . $rowCount, $val->fee2);
			$objPHPExcel->getActiveSheet()->SetCellValue('K' . $rowCount, $val->paid);
			$objPHPExcel->getActiveSheet()->SetCellValue('L' . $rowCount, $val->aktif);
			$objPHPExcel->getActiveSheet()->SetCellValue('M' . $rowCount, $val->done);



			$rowCount++;
		}

		$objPHPExcel->getActiveSheet()->SetCellValue('A' . $rowCount, 'TOTAL');
		$objPHPExcel->setActiveSheetIndex(0)->mergeCells('A' . $rowCount . ':B' . $rowCount);
		$objPHPExcel->getActiveSheet()->SetCellValue('C' . $rowCount, $total[0]);
		$objPHPExcel->getActiveSheet()->SetCellValue('D' . $rowCount, $total[1]);
		//$objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $total[2]);
		$objPHPExcel->getActiveSheet()->SetCellValue('E' . $rowCount, $total[3]);
		$objPHPExcel->getActiveSheet()->SetCellValue('F' . $rowCount, $total[4]);
		$objPHPExcel->getActiveSheet()->SetCellValue('G' . $rowCount, $total[5]);
		//$objPHPExcel->getActiveSheet()->SetCellValue('I'.$rowCount, $total[6]);
		$objPHPExcel->getActiveSheet()->SetCellValue('H' . $rowCount, $total[7]);
		$objPHPExcel->getActiveSheet()->SetCellValue('I' . $rowCount, $total[8]);
		$objPHPExcel->getActiveSheet()->SetCellValue('J' . $rowCount, $total[9]);
		$objPHPExcel->getActiveSheet()->SetCellValue('K' . $rowCount, $total[10]);
		$objPHPExcel->getActiveSheet()->SetCellValue('L' . $rowCount, $total[11]);
		$objPHPExcel->getActiveSheet()->SetCellValue('M' . $rowCount, $total[17]);

		/*$objPHPExcel->getActiveSheet()->SetCellValue('O'.$rowCount, $total[12]);
		$objPHPExcel->getActiveSheet()->SetCellValue('P'.$rowCount, $total[13]);
		$objPHPExcel->getActiveSheet()->SetCellValue('Q'.$rowCount, $total[14]);
		$objPHPExcel->getActiveSheet()->SetCellValue('R'.$rowCount, $total[15]);
		$objPHPExcel->getActiveSheet()->SetCellValue('S'.$rowCount, $total[16]);*/

		//$objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount.':S'.$rowCount)->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()->getStyle('A' . $rowCount . ':N' . $rowCount)->getFont()->setBold(true);


		/*$objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, str_pad($rsl['no_kta'], 6, '0', STR_PAD_LEFT));
		$objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, str_pad($rsl['code_bk_hkk'], 3, '0', STR_PAD_LEFT));
		$objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, str_pad($rsl['code_wilayah'], 4, '0', STR_PAD_LEFT));
		$objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, $rsl['from_date']);
		$objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, $rsl['thru_date']);
		$objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, base_url().'assets/uploads/'.$rsl['photo']);
		$objPHPExcel->getActiveSheet()->SetCellValue('I'.$rowCount, $rsl['address']);
		$objPHPExcel->getActiveSheet()->SetCellValue('J'.$rowCount, $rsl['title']);*/

		$sheet = $objPHPExcel->getActiveSheet();
		$cellIterator = $sheet->getRowIterator()->current()->getCellIterator();
		$cellIterator->setIterateOnlyExistingCells(true);

		foreach ($cellIterator as $cell) {
			$sheet->getColumnDimension($cell->getColumn())->setAutoSize(true);
		}

		$filename = date('Y-m-d') . '.xls';
		header('Content-Type: application/vnd.ms-excel'); //mime type
		header('Content-Disposition: attachment;filename="PKB_' . $filename . '"');
		header('Cache-Control: max-age=0'); //no cache
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
	}

	public function export_faip_detail()
	{
		$this->load->library('Libexcel', 'excel');
		$this->load->model('main_mod');

		$search_cab 	= 	$this->input->get('filter_cab');
		$search_bk 		= 	$this->input->get('filter_bk');
		$search_status 	= 	$this->input->get('filter_status');

		$f_status = "and 1=1";

		if ($search_status != '')
			$f_status = "and a.status_faip = '" . $search_status . "'";

		$f_bk = "and 1=1";

		if ($search_bk != '')
			$f_bk = "and LPAD( a.bidang, 2, '0' ) like '" . $search_bk . "%'";

		$f_cab = "and 1=1";

		$search_cab = explode(',', $search_cab);

		if (isset($search_cab[0])) {
			if ($search_cab[0] != '') {
				$txt = '';
				foreach ($search_cab as $val) {
					if ($txt == '')
						$txt = $txt . 'LPAD(code_wilayah, 4, "0") like "' . $val . '%"';
					else
						$txt = $txt . ' or LPAD(code_wilayah, 4, "0") like "' . $val . '%"';
				}
				$f_cab = 'and a.user_id in (select person_id from members where ' . $txt . ')';
			}
		}

		$rsl = $this->main_mod->msrquery("select a.no_kta,a.nama,

			(select GROUP_CONCAT(DISTINCT DATE(x.createddate) SEPARATOR ',') from log_status_faip x where x.faip_id=a.id and x.new_status=1) as submit ,
			(select GROUP_CONCAT(DISTINCT DATE(x.createddate) SEPARATOR ',') from log_status_faip x where x.faip_id=a.id and x.new_status=3) as reject,
			(select GROUP_CONCAT(DISTINCT DATE(x.createddate) SEPARATOR ',') from log_status_faip x where x.faip_id=a.id and x.new_status=4) as fee,
			(select GROUP_CONCAT(DISTINCT DATE(x.createddate) SEPARATOR ',') from log_status_faip x where x.faip_id=a.id and x.new_status=6) as score,
			(select GROUP_CONCAT(DISTINCT DATE(x.createddate) SEPARATOR ',') from log_status_faip x where x.faip_id=a.id and x.new_status=8) as interview,
			(select GROUP_CONCAT(DISTINCT DATE(x.createddate) SEPARATOR ',') from log_status_faip x where x.faip_id=a.id and x.new_status=9) as final,
			(select GROUP_CONCAT(DISTINCT DATE(x.createddate) SEPARATOR ',') from log_status_faip x where x.faip_id=a.id and x.new_status=10) as fee2,
			(select GROUP_CONCAT(DISTINCT DATE(x.createddate) SEPARATOR ',') from log_status_faip x where x.faip_id=a.id and x.new_status=11) as paid,
			(select GROUP_CONCAT(DISTINCT DATE(x.createddate) SEPARATOR ',') from log_status_faip x where x.faip_id=a.id and x.new_status=12) as aktif

			FROM user_faip a WHERE a.status_faip>0 $f_bk $f_cab $f_status")->result();

		$objPHPExcel = new PHPExcel();

		$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A4', 'No.KTA')
			->setCellValue('B4', 'NAMA')
			->setCellValue('C4', 'TO V&V (LSKI)')
			->setCellValue('D4', 'RETURNED TO APL')
			->setCellValue('E4', 'TO CHECK UM (FIN)')
			->setCellValue('F4', 'TO SCORE (MUK)')
			->setCellValue('G4', 'TO INTERVIEW (MUK)')
			->setCellValue('H4', 'FINAL SCORE (MUK)')
			->setCellValue('I4', 'TO CHECK SIP (FIN)')
			->setCellValue('J4', 'SIP TO PRINT (LSKI)')
			->setCellValue('K4', 'SIP TO ACTIVATE (LSKI)');

		//$objPHPExcel->getActiveSheet()->getStyle('A4:S4')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()->getStyle('A4:K4')->getFont()->setBold(true);


		if ($search_bk != '') {
			$tmp = $this->main_mod->msrquery('select name from m_bk where value = "' . $search_bk . '"')->row();
			$nama_bk = isset($tmp->name) ? $tmp->name : '';
			$objPHPExcel->getActiveSheet()->SetCellValue('A1', $nama_bk);
			$objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:L1');
		}
		if (isset($search_cab[0])) {
			$tmp = $this->main_mod->msrquery('select GROUP_CONCAT(name) as name from m_cab where value in (' . sprintf("'%s'", implode("','", $search_cab)) . ')')->row();
			$nama_cabang = isset($tmp->name) ? $tmp->name : '';
			$objPHPExcel->getActiveSheet()->SetCellValue('A2', $nama_cabang);
			$objPHPExcel->setActiveSheetIndex(0)->mergeCells('A2:L2');
		}
		if ($search_status != '') {
			$tmp = $this->main_mod->msrquery('select name from m_faip_status where value = "' . $search_status . '"')->row();
			$nama_status = isset($tmp->name) ? $tmp->name : '';
			$objPHPExcel->getActiveSheet()->SetCellValue('A3', $nama_status);
			$objPHPExcel->setActiveSheetIndex(0)->mergeCells('A3:L3');
		}




		$rowCount = 5;
		$total_jml_bk = 0;
		$total_jml_fin = 0;
		$total_jml_lski = 0;
		$total[0] = 0;
		$total[1] = 0;
		$total[2] = 0;
		$total[3] = 0;
		$total[4] = 0;
		$total[5] = 0;
		$total[6] = 0;
		$total[7] = 0;
		$total[8] = 0;
		$total[9] = 0;
		$total[10] = 0;
		$total[11] = 0;
		$total[12] = 0;
		$total[13] = 0;
		$total[14] = 0;
		$total[15] = 0;
		$total[16] = 0;
		$totalx = 0;
		foreach ($rsl as $val) {

			/*$jml_bk = $val->score+$val->preview+$val->interview;
			$jml_fin = $val->fee+$val->fee2;
			$jml_lski = ($val->submit-$val->verif-$val->reject)+$val->paid;

			$total_jml_bk += $val->score+$val->preview+$val->interview;
			$total_jml_fin += $val->fee+$val->fee2;
			$total_jml_lski += ($val->submit-$val->verif-$val->reject)+$val->paid;
			*/
			//$total[0] += $val->all_status;
			/*$total[1] += $val->submit;
			//$total[2] += $val->verif;
			$total[3] += $val->reject;
			$total[4] += $val->fee;
			$total[5] += $val->score;
			//$total[6] += $val->preview;
			$total[7] += $val->interview;
			$total[8] += $val->final;
			$total[9] += $val->fee2;
			$total[10] += $val->paid;
			$total[11] += $val->aktif;
			$total[12] += $val->submit;*/
			//$total[13] += $jml_bk;
			//$total[14] += $jml_fin;
			//$total[15] += $val->paid;
			//$total[16] += $jml_lski;
			$totalx++;

			$objPHPExcel->getActiveSheet()->SetCellValue('A' . $rowCount, str_pad($val->no_kta, 6, '0', STR_PAD_LEFT));
			$objPHPExcel->getActiveSheet()->SetCellValue('B' . $rowCount, $val->nama);
			//$objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $val->all_status);
			$objPHPExcel->getActiveSheet()->SetCellValue('C' . $rowCount, $val->submit);
			//$objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $val->verif);
			$objPHPExcel->getActiveSheet()->SetCellValue('D' . $rowCount, $val->reject);
			$objPHPExcel->getActiveSheet()->SetCellValue('E' . $rowCount, $val->fee);
			$objPHPExcel->getActiveSheet()->SetCellValue('F' . $rowCount, $val->score);
			//$objPHPExcel->getActiveSheet()->SetCellValue('I'.$rowCount, $val->preview);
			$objPHPExcel->getActiveSheet()->SetCellValue('G' . $rowCount, $val->interview);
			$objPHPExcel->getActiveSheet()->SetCellValue('H' . $rowCount, $val->final);
			$objPHPExcel->getActiveSheet()->SetCellValue('I' . $rowCount, $val->fee2);
			$objPHPExcel->getActiveSheet()->SetCellValue('J' . $rowCount, $val->paid);
			$objPHPExcel->getActiveSheet()->SetCellValue('K' . $rowCount, $val->aktif);



			$rowCount++;
		}

		$objPHPExcel->getActiveSheet()->SetCellValue('A' . $rowCount, 'TOTAL');
		//$objPHPExcel->setActiveSheetIndex(0)->mergeCells('A'.$rowCount.':B'.$rowCount);
		$objPHPExcel->getActiveSheet()->SetCellValue('B' . $rowCount, $totalx);
		/*$objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $total[1]);
		//$objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $total[2]);
		$objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $total[3]);
		$objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, $total[4]);
		$objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, $total[5]);
		//$objPHPExcel->getActiveSheet()->SetCellValue('I'.$rowCount, $total[6]);
		$objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, $total[7]);
		$objPHPExcel->getActiveSheet()->SetCellValue('I'.$rowCount, $total[8]);
		$objPHPExcel->getActiveSheet()->SetCellValue('J'.$rowCount, $total[9]);
		$objPHPExcel->getActiveSheet()->SetCellValue('K'.$rowCount, $total[10]);
		$objPHPExcel->getActiveSheet()->SetCellValue('L'.$rowCount, $total[11]);*/

		$objPHPExcel->getActiveSheet()->getStyle('A' . $rowCount . ':B' . $rowCount)->getFont()->setBold(true);


		$sheet = $objPHPExcel->getActiveSheet();
		$cellIterator = $sheet->getRowIterator()->current()->getCellIterator();
		$cellIterator->setIterateOnlyExistingCells(true);

		foreach ($cellIterator as $cell) {
			$sheet->getColumnDimension($cell->getColumn())->setAutoSize(true);
		}

		$filename = date('Y-m-d') . '.xls';
		header('Content-Type: application/vnd.ms-excel'); //mime type
		header('Content-Disposition: attachment;filename="FAIP_DETAIL_' . $filename . '"');
		header('Cache-Control: max-age=0'); //no cache
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
	}

	public function export_pkb_detail()
	{
		$this->load->library('Libexcel', 'excel');
		$this->load->model('main_mod');

		$search_cab 	= 	$this->input->get('filter_cab');
		$search_bk 		= 	$this->input->get('filter_bk');
		$search_status 	= 	$this->input->get('filter_status');

		$f_status = "and 1=1";

		if ($search_status != '')
			$f_status = "and a.status_pkb = '" . $search_status . "'";

		$f_bk = "and 1=1";

		if ($search_bk != '')
			$f_bk = "and LPAD( a.bidang, 2, '0' ) like '" . $search_bk . "%'";

		$f_cab = "and 1=1";

		$search_cab = explode(',', $search_cab);

		if (isset($search_cab[0])) {
			if ($search_cab[0] != '') {
				$txt = '';
				foreach ($search_cab as $val) {
					if ($txt == '')
						$txt = $txt . 'LPAD(code_wilayah, 4, "0") like "' . $val . '%"';
					else
						$txt = $txt . ' or LPAD(code_wilayah, 4, "0") like "' . $val . '%"';
				}
				$f_cab = 'and a.user_id in (select person_id from members where ' . $txt . ')';
			}
		}

		$rsl = $this->main_mod->msrquery("select a.no_kta,a.nama,

			(select GROUP_CONCAT(DISTINCT DATE(x.createddate) SEPARATOR ',') from log_status_pkb x where x.pkb_id=a.id and x.new_status=1) as submit ,
			(select GROUP_CONCAT(DISTINCT DATE(x.createddate) SEPARATOR ',') from log_status_pkb x where x.pkb_id=a.id and x.new_status=3) as reject,
			(select GROUP_CONCAT(DISTINCT DATE(x.createddate) SEPARATOR ',') from log_status_pkb x where x.pkb_id=a.id and x.new_status=4) as fee,
			(select GROUP_CONCAT(DISTINCT DATE(x.createddate) SEPARATOR ',') from log_status_pkb x where x.pkb_id=a.id and x.new_status=6) as score,
			(select GROUP_CONCAT(DISTINCT DATE(x.createddate) SEPARATOR ',') from log_status_pkb x where x.pkb_id=a.id and x.new_status=8) as interview,
			(select GROUP_CONCAT(DISTINCT DATE(x.createddate) SEPARATOR ',') from log_status_pkb x where x.pkb_id=a.id and x.new_status=9) as final,
			(select GROUP_CONCAT(DISTINCT DATE(x.createddate) SEPARATOR ',') from log_status_pkb x where x.pkb_id=a.id and x.new_status=10) as fee2,
			(select GROUP_CONCAT(DISTINCT DATE(x.createddate) SEPARATOR ',') from log_status_pkb x where x.pkb_id=a.id and x.new_status=11) as paid,
			(select GROUP_CONCAT(DISTINCT DATE(x.createddate) SEPARATOR ',') from log_status_pkb x where x.pkb_id=a.id and x.new_status=12) as aktif,
			(select GROUP_CONCAT(DISTINCT DATE(x.createddate) SEPARATOR ',') from log_status_pkb x where x.pkb_id=a.id and x.new_status=13) as done

			FROM user_pkb a WHERE a.status_pkb>0 $f_bk $f_cab $f_status")->result();

		$objPHPExcel = new PHPExcel();

		$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A4', 'No.KTA')
			->setCellValue('B4', 'NAMA')
			->setCellValue('C4', 'TO V&V (LSKI)')
			->setCellValue('D4', 'RETURNED TO APL')
			->setCellValue('E4', 'TO CHECK UM (FIN)')
			->setCellValue('F4', 'TO SCORE (MUK)')
			->setCellValue('G4', 'TO INTERVIEW (MUK)')
			->setCellValue('H4', 'FINAL SCORE (MUK)')
			->setCellValue('I4', 'TO CHECK SIP (FIN)')
			->setCellValue('J4', 'SIP TO PRINT (LSKI)')
			->setCellValue('K4', 'SIP TO ACTIVATE (LSKI)')
			->setCellValue('L4', 'DONE');

		//$objPHPExcel->getActiveSheet()->getStyle('A4:S4')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()->getStyle('A4:L4')->getFont()->setBold(true);


		if ($search_bk != '') {
			$tmp = $this->main_mod->msrquery('select name from m_bk where value = "' . $search_bk . '"')->row();
			$nama_bk = isset($tmp->name) ? $tmp->name : '';
			$objPHPExcel->getActiveSheet()->SetCellValue('A1', $nama_bk);
			$objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:L1');
		}
		if (isset($search_cab[0])) {
			$tmp = $this->main_mod->msrquery('select GROUP_CONCAT(name) as name from m_cab where value in (' . sprintf("'%s'", implode("','", $search_cab)) . ')')->row();
			$nama_cabang = isset($tmp->name) ? $tmp->name : '';
			$objPHPExcel->getActiveSheet()->SetCellValue('A2', $nama_cabang);
			$objPHPExcel->setActiveSheetIndex(0)->mergeCells('A2:L2');
		}
		if ($search_status != '') {
			$tmp = $this->main_mod->msrquery('select name from m_pkb_status where value = "' . $search_status . '"')->row();
			$nama_status = isset($tmp->name) ? $tmp->name : '';
			$objPHPExcel->getActiveSheet()->SetCellValue('A3', $nama_status);
			$objPHPExcel->setActiveSheetIndex(0)->mergeCells('A3:L3');
		}




		$rowCount = 5;
		$total_jml_bk = 0;
		$total_jml_fin = 0;
		$total_jml_lski = 0;
		$total[0] = 0;
		$total[1] = 0;
		$total[2] = 0;
		$total[3] = 0;
		$total[4] = 0;
		$total[5] = 0;
		$total[6] = 0;
		$total[7] = 0;
		$total[8] = 0;
		$total[9] = 0;
		$total[10] = 0;
		$total[11] = 0;
		$total[12] = 0;
		$total[13] = 0;
		$total[14] = 0;
		$total[15] = 0;
		$total[16] = 0;
		$total[17] = 0;
		$totalx = 0;
		foreach ($rsl as $val) {

			/*$jml_bk = $val->score+$val->preview+$val->interview;
			$jml_fin = $val->fee+$val->fee2;
			$jml_lski = ($val->submit-$val->verif-$val->reject)+$val->paid;

			$total_jml_bk += $val->score+$val->preview+$val->interview;
			$total_jml_fin += $val->fee+$val->fee2;
			$total_jml_lski += ($val->submit-$val->verif-$val->reject)+$val->paid;
			*/
			//$total[0] += $val->all_status;
			/*$total[1] += $val->submit;
			//$total[2] += $val->verif;
			$total[3] += $val->reject;
			$total[4] += $val->fee;
			$total[5] += $val->score;
			//$total[6] += $val->preview;
			$total[7] += $val->interview;
			$total[8] += $val->final;
			$total[9] += $val->fee2;
			$total[10] += $val->paid;
			$total[11] += $val->aktif;
			$total[12] += $val->submit;*/
			//$total[13] += $jml_bk;
			//$total[14] += $jml_fin;
			//$total[15] += $val->paid;
			//$total[16] += $jml_lski;
			$totalx++;

			$objPHPExcel->getActiveSheet()->SetCellValue('A' . $rowCount, str_pad($val->no_kta, 6, '0', STR_PAD_LEFT));
			$objPHPExcel->getActiveSheet()->SetCellValue('B' . $rowCount, $val->nama);
			//$objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $val->all_status);
			$objPHPExcel->getActiveSheet()->SetCellValue('C' . $rowCount, $val->submit);
			//$objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $val->verif);
			$objPHPExcel->getActiveSheet()->SetCellValue('D' . $rowCount, $val->reject);
			$objPHPExcel->getActiveSheet()->SetCellValue('E' . $rowCount, $val->fee);
			$objPHPExcel->getActiveSheet()->SetCellValue('F' . $rowCount, $val->score);
			//$objPHPExcel->getActiveSheet()->SetCellValue('I'.$rowCount, $val->preview);
			$objPHPExcel->getActiveSheet()->SetCellValue('G' . $rowCount, $val->interview);
			$objPHPExcel->getActiveSheet()->SetCellValue('H' . $rowCount, $val->final);
			$objPHPExcel->getActiveSheet()->SetCellValue('I' . $rowCount, $val->fee2);
			$objPHPExcel->getActiveSheet()->SetCellValue('J' . $rowCount, $val->paid);
			$objPHPExcel->getActiveSheet()->SetCellValue('K' . $rowCount, $val->aktif);
			$objPHPExcel->getActiveSheet()->SetCellValue('L' . $rowCount, $val->done);



			$rowCount++;
		}

		$objPHPExcel->getActiveSheet()->SetCellValue('A' . $rowCount, 'TOTAL');
		//$objPHPExcel->setActiveSheetIndex(0)->mergeCells('A'.$rowCount.':B'.$rowCount);
		$objPHPExcel->getActiveSheet()->SetCellValue('B' . $rowCount, $totalx);
		/*$objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $total[1]);
		//$objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $total[2]);
		$objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $total[3]);
		$objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, $total[4]);
		$objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, $total[5]);
		//$objPHPExcel->getActiveSheet()->SetCellValue('I'.$rowCount, $total[6]);
		$objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, $total[7]);
		$objPHPExcel->getActiveSheet()->SetCellValue('I'.$rowCount, $total[8]);
		$objPHPExcel->getActiveSheet()->SetCellValue('J'.$rowCount, $total[9]);
		$objPHPExcel->getActiveSheet()->SetCellValue('K'.$rowCount, $total[10]);
		$objPHPExcel->getActiveSheet()->SetCellValue('L'.$rowCount, $total[11]);*/

		$objPHPExcel->getActiveSheet()->getStyle('A' . $rowCount . ':B' . $rowCount)->getFont()->setBold(true);


		$sheet = $objPHPExcel->getActiveSheet();
		$cellIterator = $sheet->getRowIterator()->current()->getCellIterator();
		$cellIterator->setIterateOnlyExistingCells(true);

		foreach ($cellIterator as $cell) {
			$sheet->getColumnDimension($cell->getColumn())->setAutoSize(true);
		}

		$filename = date('Y-m-d') . '.xls';
		header('Content-Type: application/vnd.ms-excel'); //mime type
		header('Content-Disposition: attachment;filename="PKB_DETAIL_' . $filename . '"');
		header('Cache-Control: max-age=0'); //no cache
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
	}

	public function export_skip()
	{
		$this->load->library('Libexcel', 'excel');
		$this->load->model('main_mod');

		$search_cab 	= 	$this->input->get('filter_cab');
		$search_bk 		= 	$this->input->get('filter_bk');
		$search_type 	= 	$this->input->get('filter_type');

		$f_type = "and 1=1";

		$search_type = explode(',', $search_type);

		if (isset($search_type[0])) {
			if ($search_type[0] != '') {
				$txt = '(';
				foreach ($search_type as $val) {
					if ($txt == '(')
						$txt = $txt . 'x.cert_title like "' . $val . '%"';
					else
						$txt = $txt . ' or x.cert_title like "' . $val . '%"';
				}
				$txt = $txt . ')';
				$f_type = 'and ' . $txt;
			}
		}

		$f_bk = "and 1=1";

		$search_bk = explode(',', $search_bk);

		if (isset($search_bk[0])) {
			if ($search_bk[0] != '')
				$f_bk = 'and a.value in (' . implode(",", $search_bk) . ')';
		}

		$f_cab = "and 1=1";

		$search_cab = explode(',', $search_cab);

		if (isset($search_cab[0])) {
			if ($search_cab[0] != '') {
				$txt = '(';
				foreach ($search_cab as $val) {
					if ($txt == '(')
						$txt = $txt . 'LPAD(x.ip_kta_wilcab, 4, "0") like "' . $val . '%"';
					else
						$txt = $txt . ' or LPAD(x.ip_kta_wilcab, 4, "0") like "' . $val . '%"';
				}
				$txt = $txt . ')';
				$f_cab = 'and ' . $txt;
			}
		}

		$rsl = $this->main_mod->msrquery("SELECT
a.value,a.name,
(select count(x.id) from user_cert x left join user_profiles y on x.user_id=y.user_id where TRIM(LEADING '0' FROM a.value)=TRIM(LEADING '0' FROM x.ip_bk) and x.status=2 and x.user_id <> 0 $f_cab $f_type) as total,
(select count(x.id) from user_cert x left join user_profiles y on x.user_id=y.user_id where TRIM(LEADING '0' FROM a.value)=TRIM(LEADING '0' FROM x.ip_bk) and x.status=2 and endyear>=now() and x.user_id <> 0 $f_cab $f_type) as aktif ,
(select count(x.id) from user_cert x left join user_profiles y on x.user_id=y.user_id where TRIM(LEADING '0' FROM a.value)=TRIM(LEADING '0' FROM x.ip_bk) and x.status=2 and endyear<now() and x.user_id <> 0 $f_cab $f_type) as tidakaktif
FROM `m_bk` a WHERE 1=1 $f_bk")->result();

		$objPHPExcel = new PHPExcel();

		$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A1', 'KODE BK')
			->setCellValue('B1', 'NAMA BK')
			->setCellValue('C1', 'AKTIF')
			->setCellValue('D1', 'TIDAK AKTIF')
			->setCellValue('E1', 'TOTAL');

		$objPHPExcel->getActiveSheet()->getStyle('A1:E1')->getFont()->setBold(true);

		$rowCount = 2;
		$aktif = 0;
		$tidakaktif = 0;
		$total = 0;
		foreach ($rsl as $val) {
			$objPHPExcel->getActiveSheet()->SetCellValue('A' . $rowCount, $val->value);
			$objPHPExcel->getActiveSheet()->SetCellValue('B' . $rowCount, $val->name);
			$objPHPExcel->getActiveSheet()->SetCellValue('C' . $rowCount, $val->aktif);
			$objPHPExcel->getActiveSheet()->SetCellValue('D' . $rowCount, $val->tidakaktif);
			$objPHPExcel->getActiveSheet()->SetCellValue('E' . $rowCount, $val->total);

			$aktif += $val->aktif;
			$tidakaktif += $val->tidakaktif;
			$total += $val->total;

			$rowCount++;
		}

		$objPHPExcel->getActiveSheet()->SetCellValue('A' . $rowCount, 'TOTAL');
		$objPHPExcel->setActiveSheetIndex(0)->mergeCells('A' . $rowCount . ':B' . $rowCount);
		$objPHPExcel->getActiveSheet()->SetCellValue('C' . $rowCount, $aktif);
		$objPHPExcel->getActiveSheet()->SetCellValue('D' . $rowCount, $tidakaktif);
		$objPHPExcel->getActiveSheet()->SetCellValue('E' . $rowCount, $total);
		$objPHPExcel->getActiveSheet()->getStyle('A' . $rowCount . ':E' . $rowCount)->getFont()->setBold(true);



		$sheet = $objPHPExcel->getActiveSheet();
		$cellIterator = $sheet->getRowIterator()->current()->getCellIterator();
		$cellIterator->setIterateOnlyExistingCells(true);

		foreach ($cellIterator as $cell) {
			$sheet->getColumnDimension($cell->getColumn())->setAutoSize(true);
		}

		$filename = date('Y-m-d') . '.xls';
		header('Content-Type: application/vnd.ms-excel'); //mime type
		header('Content-Disposition: attachment;filename="SKIP_' . $filename . '"');
		header('Cache-Control: max-age=0'); //no cache
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
	}

	public function export_stri()
	{
		$this->load->library('Libexcel', 'excel');
		$this->load->model('main_mod');

		$search_cab 	= 	$this->input->get('filter_cab');
		$search_bk 		= 	$this->input->get('filter_bk');
		$search_type 	= 	$this->input->get('filter_type');

		$f_type = "and 1=1";

		$search_type = explode(',', $search_type);

		if (isset($search_type[0])) {
			if ($search_type[0] != '') {
				$txt = '(';
				foreach ($search_type as $val) {
					if ($txt == '(')
						$txt = $txt . 'x.certificate_type like "' . $val . '%"';
					else
						$txt = $txt . ' or x.certificate_type like "' . $val . '%"';
				}
				$txt = $txt . ')';
				$f_type = 'and ' . $txt;
			}
		}

		$f_bk = "and 1=1";

		$search_bk = explode(',', $search_bk);

		if (isset($search_bk[0])) {
			if ($search_bk[0] != '')
				$f_bk = 'and a.value in (' . implode(",", $search_bk) . ')';
		}

		$f_cab = "and 1=1";

		$search_cab = explode(',', $search_cab);

		if (isset($search_cab[0])) {
			if ($search_cab[0] != '') {
				$txt = '(';
				foreach ($search_cab as $val) {
					if ($txt == '(')
						$txt = $txt . 'LPAD(x.stri_code_wilayah, 4, "0") like "' . $val . '%"';
					else
						$txt = $txt . ' or LPAD(x.stri_code_wilayah, 4, "0") like "' . $val . '%"';
				}
				$txt = $txt . ')';
				$f_cab = 'and ' . $txt;
			}
		}

		$rsl = $this->main_mod->msrquery("SELECT
			a.value,a.name,

			(select count(x.id) from members_certificate x join user_profiles y on x.person_id=y.user_id where TRIM(LEADING '0' FROM a.value)=TRIM(LEADING '0' FROM x.stri_code_bk_hkk) and x.status=1 and x.certificate_type like '1%'  $f_cab $f_type) as IPP,
            (select count(x.id) from members_certificate x join user_profiles y on x.person_id=y.user_id where TRIM(LEADING '0' FROM a.value)=TRIM(LEADING '0' FROM x.stri_code_bk_hkk) and x.status=1 and x.certificate_type like '2%'  $f_cab $f_type) as IPM,
            (select count(x.id) from members_certificate x join user_profiles y on x.person_id=y.user_id where TRIM(LEADING '0' FROM a.value)=TRIM(LEADING '0' FROM x.stri_code_bk_hkk) and x.status=1 and x.certificate_type like '3%'  $f_cab $f_type) as IPU,
            (select count(x.id) from members_certificate x join user_profiles y on x.person_id=y.user_id where TRIM(LEADING '0' FROM a.value)=TRIM(LEADING '0' FROM x.stri_code_bk_hkk) and x.status=1 and x.certificate_type like '0%'  $f_cab $f_type) as PER,

			(select count(x.id) from members_certificate x join user_profiles y on x.person_id=y.user_id where TRIM(LEADING '0' FROM a.value)=TRIM(LEADING '0' FROM x.stri_code_bk_hkk) and x.status=1 $f_cab $f_type) as total,
			(select count(x.id) from members_certificate x join user_profiles y on x.person_id=y.user_id where TRIM(LEADING '0' FROM a.value)=TRIM(LEADING '0' FROM x.stri_code_bk_hkk) and x.status=1 and stri_thru_date>=now() $f_cab $f_type) as aktif ,
			(select count(x.id) from members_certificate x join user_profiles y on x.person_id=y.user_id where TRIM(LEADING '0' FROM a.value)=TRIM(LEADING '0' FROM x.stri_code_bk_hkk) and x.status=1 and stri_thru_date<now() $f_cab $f_type) as tidakaktif

			FROM `m_bk` a WHERE 1=1 $f_bk")->result();

		$objPHPExcel = new PHPExcel();

		$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A1', 'KODE BK')
			->setCellValue('B1', 'NAMA BK')

			->setCellValue('C1', 'IPP')
			->setCellValue('D1', 'IPM')
			->setCellValue('E1', 'IPU')
			->setCellValue('F1', 'PER')

			->setCellValue('G1', 'AKTIF')
			->setCellValue('H1', 'TIDAK AKTIF')
			->setCellValue('I1', 'TOTAL');

		$objPHPExcel->getActiveSheet()->getStyle('A1:I1')->getFont()->setBold(true);

		$rowCount = 2;
		$aktif = 0;
		$tidakaktif = 0;
		$total = 0;
		$ipp = 0;
		$ipm = 0;
		$ipu = 0;
		$per = 0;
		foreach ($rsl as $val) {
			$objPHPExcel->getActiveSheet()->SetCellValue('A' . $rowCount, $val->value);
			$objPHPExcel->getActiveSheet()->SetCellValue('B' . $rowCount, $val->name);

			$objPHPExcel->getActiveSheet()->SetCellValue('C' . $rowCount, $val->IPP);
			$objPHPExcel->getActiveSheet()->SetCellValue('D' . $rowCount, $val->IPM);
			$objPHPExcel->getActiveSheet()->SetCellValue('E' . $rowCount, $val->IPU);
			$objPHPExcel->getActiveSheet()->SetCellValue('F' . $rowCount, $val->PER);


			$objPHPExcel->getActiveSheet()->SetCellValue('G' . $rowCount, $val->aktif);
			$objPHPExcel->getActiveSheet()->SetCellValue('H' . $rowCount, $val->tidakaktif);
			$objPHPExcel->getActiveSheet()->SetCellValue('I' . $rowCount, $val->total);

			$aktif += $val->aktif;
			$tidakaktif += $val->tidakaktif;
			$total += $val->total;

			$ipp += $val->IPP;
			$ipm += $val->IPM;
			$ipu += $val->IPU;
			$per += $val->PER;

			$rowCount++;
		}

		$objPHPExcel->getActiveSheet()->SetCellValue('A' . $rowCount, 'TOTAL');
		$objPHPExcel->setActiveSheetIndex(0)->mergeCells('A' . $rowCount . ':B' . $rowCount);

		$objPHPExcel->getActiveSheet()->SetCellValue('C' . $rowCount, $ipp);
		$objPHPExcel->getActiveSheet()->SetCellValue('D' . $rowCount, $ipm);
		$objPHPExcel->getActiveSheet()->SetCellValue('E' . $rowCount, $ipu);
		$objPHPExcel->getActiveSheet()->SetCellValue('F' . $rowCount, $per);

		$objPHPExcel->getActiveSheet()->SetCellValue('G' . $rowCount, $aktif);
		$objPHPExcel->getActiveSheet()->SetCellValue('H' . $rowCount, $tidakaktif);
		$objPHPExcel->getActiveSheet()->SetCellValue('I' . $rowCount, $total);
		$objPHPExcel->getActiveSheet()->getStyle('A' . $rowCount . ':I' . $rowCount)->getFont()->setBold(true);



		$sheet = $objPHPExcel->getActiveSheet();
		$cellIterator = $sheet->getRowIterator()->current()->getCellIterator();
		$cellIterator->setIterateOnlyExistingCells(true);

		foreach ($cellIterator as $cell) {
			$sheet->getColumnDimension($cell->getColumn())->setAutoSize(true);
		}

		$filename = date('Y-m-d') . '.xls';
		header('Content-Type: application/vnd.ms-excel'); //mime type
		header('Content-Disposition: attachment;filename="STRI_' . $filename . '"');
		header('Cache-Control: max-age=0'); //no cache
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
	}

	function export_stri_2()
	{

		if ($this->session->userdata('type') != "0" && $this->session->userdata('type') != "2" && $this->session->userdata('type') != "12" && $this->session->userdata('type') != "13" && $this->session->userdata('type') != "11" && $this->session->userdata('type') != "1") {
			redirect('admin/dashboard');
			exit;
		}

		if (
			$this->session->userdata('type') == ADMIN_LSKI
			&& ($this->session->userdata('admin_id') != "670" && $this->session->userdata('admin_id') != "659")
		) {

			redirect('admin/dashboard');
			exit;
		}

		if ($_GET) {
			$search_name 	= 	$this->input->get('firstname');
			$search_kta 	= 	$this->input->get('kta');

			$search_nomor 	= 	$this->input->get('nomor');
			$search_type 	= 	$this->input->get('filter_type');
			$search_status 	= 	$this->input->get('filter_status');
			$search_cab 	= 	$this->input->get('filter_cab');
			$search_bk 		= 	$this->input->get('filter_bk');
			$search_hkk 	= 	$this->input->get('filter_hkk');
			$search_program = 	$this->input->get('filter_program');
			$search_stri_period_start	 = 	$this->input->get('stri_period_start');
			$search_stri_period_end	 	= 	$this->input->get('stri_period_end');
		}

		$search_data2 = array();
		if (isset($search_name) && $search_name != '') {
			$search_data2['REPLACE(lower(add_name)," ","")'] = str_replace(' ', '', strtolower($search_name));
		}
		if (isset($search_kta) && $search_kta != '') {
			$search_data2['members_certificate.no_kta'] = ltrim($search_kta, '0');
		}

		if (isset($search_nomor) && $search_nomor != '') {
			$search_data2['nomor'] = $search_nomor;
		}

		if (isset($search_type) && $search_type != '') {
			$search_data2['filter_type'] = $search_type;
		}

		if (isset($search_status) && $search_status != '') {
			$search_data2['filter_status'] = $search_status;
		}

		if (isset($search_cab) && $search_cab != '') {
			$search_data2['filter_cab'] = $search_cab;
		}

		if (isset($search_bk) && $search_bk != '') {
			$search_data2['filter_bk'] = $search_bk;
		}

		if (isset($search_hkk) && $search_hkk != '') {
			$search_data2['filter_hkk'] = $search_hkk;
		}

		if (isset($search_program) && $search_program != '') {
			$search_data2['stri_tipe'] = $search_program;
		}

		if (isset($search_stri_period_start) && $search_stri_period_start != '' && isset($search_stri_period_end) && $search_stri_period_end != '') {
			$time1 = strtotime($search_stri_period_start);
			$time2 = strtotime($search_stri_period_end);

			if ($time2 >= $time1) {
				$search_data2['stri_period'] = 'date(e.createddate) between "' . $search_stri_period_start . '" and "' . $search_stri_period_end . '"';
			} else {
				$search_data2['stri_period'] = 'date(e.createddate) between "' . date('Y-m-d') . '" and "' . date('Y-m-d') . '"';
			}
		}

		$this->load->library('Libexcel', 'excel');

		$this->load->model('main_mod');
		$count = $this->members_model->get_count_export_stri($search_data2);
		if ($count < 5000) {

			$rslx = $this->members_model->get_export_stri($search_data2);
			$objPHPExcel = new PHPExcel();

			$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('A1', 'Tanggal')
				->setCellValue('B1', 'Name')
				->setCellValue('C1', 'Type')
				->setCellValue('D1', 'Nomor')
				->setCellValue('E1', 'No. STRI')
				->setCellValue('F1', 'No. KTA')
				->setCellValue('G1', 'SK');

			$rowCount = 2;

			foreach ($rslx as $rsl) {
				$paytype = '';
				if ($rsl->certificate_type == '1')
					$paytype = 'IPP';
				else if ($rsl->certificate_type == '2')
					$paytype = 'IPM';
				else if ($rsl->certificate_type == '3')
					$paytype = 'IPU';

				$tmp = ($rsl->certificate_type != "" ? $rsl->certificate_type : "0") . '.' . ($rsl->stri_code_bk_hkk == "" ? "000" : str_pad($rsl->stri_code_bk_hkk, 3, '0', STR_PAD_LEFT)) . '.' . str_pad($rsl->th, 2, '0', STR_PAD_LEFT) . '.' . $rsl->warga . '.' . $rsl->stri_tipe . '.' . str_pad($rsl->stri_id, 8, '0', STR_PAD_LEFT);

				$objPHPExcel->getActiveSheet()->SetCellValue('A' . $rowCount, $rsl->tgl);
				$objPHPExcel->getActiveSheet()->SetCellValue('B' . $rowCount, $rsl->add_name);
				$objPHPExcel->getActiveSheet()->SetCellValue('C' . $rowCount, $paytype);
				$objPHPExcel->getActiveSheet()->setCellValueExplicit('D' . $rowCount, str_pad($rsl->stri_id, 7, '0', STR_PAD_LEFT), PHPExcel_Cell_DataType::TYPE_STRING);
				$objPHPExcel->getActiveSheet()->setCellValueExplicit('E' . $rowCount, $tmp, PHPExcel_Cell_DataType::TYPE_STRING);
				$objPHPExcel->getActiveSheet()->setCellValueExplicit('F' . $rowCount, str_pad($rsl->no_kta, 6, '0', STR_PAD_LEFT), PHPExcel_Cell_DataType::TYPE_STRING);
				$objPHPExcel->getActiveSheet()->SetCellValue('G' . $rowCount, $rsl->stri_sk);

				$status = '';

				if ($rsl->stri_thru_date >= date('Y-m-d')) {
					$class_label = 'success';
					$status = 'Active';
				} else {
					$class_label = 'danger';
					$status = 'Not Active';
				}

				$objPHPExcel->getActiveSheet()->SetCellValue('H' . $rowCount, $status);
				$rowCount++;
			}
			$sheet = $objPHPExcel->getActiveSheet();
			$cellIterator = $sheet->getRowIterator()->current()->getCellIterator();
			$cellIterator->setIterateOnlyExistingCells(true);

			foreach ($cellIterator as $cell) {
				$sheet->getColumnDimension($cell->getColumn())->setAutoSize(true);
			}

			$filename = date('Y-m-d') . '.xls';
			header('Content-Type: application/vnd.ms-excel'); //mime type
			header('Content-Disposition: attachment;filename="' . $filename . '"');
			header('Cache-Control: max-age=0'); //no cache
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
			$objWriter->save('php://output');
		} else {
			echo $count . '<script>alert("data terlalu banyak, silahkan filter terlebih dahulu");</script>';
		}
	}

	function edit_profile()
	{
		$idmember = $this->input->post('idmember') <> null ? $this->input->post('idmember') : "";
		if ($idmember == '') {
			redirect('admin/members');
			exit;
		}
		$this->load->model('main_mod');
		//PROFILE

		$this->form_validation->set_rules('fn', 'Firstname', 'trim|xss_clean');
		$this->form_validation->set_rules('va', 'VA', 'trim|xss_clean');
		$this->form_validation->set_rules('ln', 'Lastname', 'trim|xss_clean');
		$this->form_validation->set_rules('gender', 'Gender', 'trim|xss_clean');
		$this->form_validation->set_rules('phone', 'Mobile Phone', 'trim|xss_clean');
		$this->form_validation->set_rules('birthplace', 'Birthplace', 'trim|xss_clean');
		$this->form_validation->set_rules('typeid', 'Citizen', 'trim|xss_clean');
		$this->form_validation->set_rules('idnumber', 'ID', 'trim|xss_clean');
		$this->form_validation->set_rules('dob', 'Date of Birth', 'trim|xss_clean');
		$this->form_validation->set_rules('website', 'Website', 'trim|xss_clean');
		$this->form_validation->set_rules('is_public', 'is_public', 'trim|xss_clean');
		$this->form_validation->set_rules('is_datasend', 'is_datasend', 'trim|xss_clean');
		$this->form_validation->set_rules('desc', 'Description', 'trim|xss_clean');
		$this->form_validation->set_rules('mailingaddr', 'mailing addr', 'trim|xss_clean');
		$this->form_validation->set_rules('addressid', 'id', 'trim|xss_clean');

		if ($this->form_validation->run()) {
			$va = $this->input->post('va') <> null ? $this->input->post('va') : "";
			$fn = $this->input->post('fn') <> null ? $this->input->post('fn') : "";
			$ln = $this->input->post('ln') <> null ? $this->input->post('ln') : "";
			$dob = $this->input->post('dob') <> null ? $this->input->post('dob') : "";
			$phone = $this->input->post('phone') <> null ? $this->input->post('phone') : "";
			$birthplace = $this->input->post('birthplace') <> null ? $this->input->post('birthplace') : "";
			$typeid = $this->input->post('typeid') <> null ? $this->input->post('typeid') : "";
			$website = $this->input->post('website') <> null ? $this->input->post('website') : "";
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

				//print_r($data['user_phone']);


				$where = array(
					"user_id" => $idmember
				);

				$row = array(
					'firstname' => $fn,
					'lastname' => $ln,
					'gender' => $gender,
					'idtype' => $typeid,
					'idcard' => $idnumber,
					'mobilephone' => str_replace("-", "", str_replace(" ", "", $phone)),
					'birthplace' => $birthplace,
					'dob' => date('Y-m-d', strtotime($dob)),
					'website' => $website,
					'is_public' => ($is_public == "true" ? "1" : "0"),
					'is_datasend' => ($is_datasend == "true" ? "1" : "0"),
					'description' => $desc,
					'modifieddate' => date('Y-m-d H:i:s'),
					'modifiedby' => $this->session->userdata('admin_id'),
				);

				if ($this->session->userdata('type') == ADMIN_SUPERADMIN) {
					$row['va'] = $va;
				}

				$update = $this->main_mod->update('user_profiles', $where, $row);

				//ADDRESS

				$where = array(
					"user_id" => $idmember
				);
				$row = array(
					'status' => 0,
					'modifieddate' => date('Y-m-d H:i:s'),
					'modifiedby' => $this->session->userdata('admin_id'),
				);
				$update = $this->main_mod->update('user_address', $where, $row);


				$i = 0;
				$mailing = $mailing - 1;
				foreach ($typeaddress as $val) {
					$temp = 0;
					if ($mailing == $i)
						$temp = 1;
					$row = array(
						'user_id' => $idmember,
						'addresstype' => $typeaddress[$i],
						'address' => $address[$i],
						'phone' => $addressphone[$i],
						'email' => $email[$i],
						'city' => $addresscity[$i],
						'province' => $addressprovince[$i],
						'zipcode' => $addresszip[$i],
						'is_mailing' => $temp,
						'createdby' => $this->session->userdata('admin_id'),
					);
					//$insert = $this->main_mod->insert('user_address',$row);


					if ($addressid[$i] == '' || $addressid[$i] == '0')
						$catch = $this->main_mod->insert('user_address', $row);
					else {
						$row = array(
							'user_id' => $idmember,
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
							'modifiedby' => $this->session->userdata('admin_id'),
						);
						$where = array(
							"user_id" => $idmember,
							'id' => $addressid[$i],
						);
						$catch = $this->main_mod->update('user_address', $where, $row);
					}

					$i++;
				}


				//Email

				$where = array(
					"user_id" => $idmember
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
						'user_id' => $idmember,
						'contact_type' => $typeemail[$i],
						'contact_value' => $emailm[$i],
						//'created_at' => $this->session->userdata('user_id'),
					);
					//$insert = $this->main_mod->insert('user_address',$row);


					if ($emailid[$i] == '' || $emailid[$i] == '0')
						$catch = $this->main_mod->insert('contacts', $row);
					else {
						$row = array(
							'user_id' => $idmember,
							'contact_type' => $typeemail[$i],
							'contact_value' => $emailm[$i],
							'status' => 1,
							'updated_at' => date('Y-m-d H:i:s'),
							//'modifiedby' => $this->session->userdata('user_id'),
						);
						$where = array(
							"user_id" => $idmember,
							'id' => $emailid[$i],
						);
						$catch = $this->main_mod->update('contacts', $where, $row);
					}

					$i++;
				}

				//Phone

				/*$where = array(
					"user_id" => $idmember
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
						'user_id' => $idmember,
						'contact_type' => $typephone[$i],
						'contact_value' => $phonem[$i],
						//'createdby' => $this->session->userdata('user_id'),
					);
					//$insert = $this->main_mod->insert('user_address',$row);


					if ($phoneid[$i] == '' || $phoneid[$i] == '0')
						$catch = $this->main_mod->insert('contacts', $row);
					else {
						$row = array(
							'user_id' => $idmember,
							'contact_type' => $typephone[$i],
							'contact_value' => $phonem[$i],
							'status' => 1,
							'updated_at' => date('Y-m-d H:i:s'),
							//'modifiedby' => $this->session->userdata('user_id'),
						);
						$where = array(
							"user_id" => $idmember,
							'id' => $phoneid[$i],
						);
						$catch = $this->main_mod->update('contacts', $where, $row);
					}

					$i++;
				}

				$data['m_phone'] = $this->main_mod->msrwhere('m_param', array('code' => 'phone'), 'id', 'asc')->result();
				$data['m_email'] = $this->main_mod->msrwhere('m_param', array('code' => 'email'), 'id', 'asc')->result();
				$data['m_address'] = $this->main_mod->msrwhere('m_param', array('code' => 'address'), 'id', 'asc')->result();
				$data['m_degree'] = $this->main_mod->msrwhere('education_type', null, 'EDUCATION_TYPE_ID', 'asc')->result();
				$id = $idmember;
				$this->load->model('members_model');
				$obj_row = $this->members_model->get_member_by_id($id);
				$data['row'] = $obj_row;

				$data['user_address'] = $this->main_mod->msrwhere('user_address', array('user_id' => $id, 'status' => 1), 'id', 'asc')->result();
				$data['user_email'] = $this->main_mod->msrwhere('contacts', array('user_id' => $id, 'contact_type like "%_email%"' => null, 'status' => 1), 'id', 'asc')->result();
				$data['user_phone'] = $this->main_mod->msrwhere('contacts', array('user_id' => $id, 'contact_type like "%_phone%"' => null, 'status' => 1), 'id', 'asc')->result();
				$data['emailx'] = $obj_row->email;

				echo "valid";
			} catch (Exception $e) {
				//print_r($e);
				echo "not valid";
			}
		} else
			echo "not valid" . validation_errors('<div class="error">', '</div>');
	}

	function add_exp()
	{
		$idmember = $this->input->post('idmember') <> null ? $this->input->post('idmember') : "";
		if ($idmember == '') {
			redirect('admin/members');
			exit;
		}
		$this->load->model('main_mod');
		//EXPERIENCE
		$title = $this->input->post('title') <> null ? $this->input->post('title') : "";
		$company = $this->input->post('company') <> null ? $this->input->post('company') : "";
		$loc = $this->input->post('location') <> null ? $this->input->post('location') : "";
		$provinsi = $this->input->post('provinsi') <> null ? $this->input->post('provinsi') : "";
		$negara = $this->input->post('negara') <> null ? $this->input->post('negara') : "";
		$year = $this->input->post('startyear') <> null ? $this->input->post('startyear') : "";
		$year2 = $this->input->post('endyear') <> null ? $this->input->post('endyear') : "";
		$typetimeperiod = $this->input->post('startmonth') <> null ? $this->input->post('startmonth') : "";
		$typetimeperiod2 = $this->input->post('endmonth') <> null ? $this->input->post('endmonth') : "";
		$work = $this->input->post('is_present') <> null ? $this->input->post('is_present') : "";
		$actv = $this->input->post('actv') <> null ? $this->input->post('actv') : "";
		$desc = $this->input->post('description') <> null ? $this->input->post('description') : "";

		//$competency = $this->input->post('competency')<>null?$this->input->post('competency'):"";
		//$p_value = $this->input->post('p_value')<>null?$this->input->post('p_value'):"";
		//$q_value = $this->input->post('q_value')<>null?$this->input->post('q_value'):"";
		//$r_value = $this->input->post('r_value')<>null?$this->input->post('r_value'):"";
		//$t_value = $this->input->post('t_value')<>null?$this->input->post('t_value'):"";

		if ($title != '') {
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
								$actual_image_name = time() . "_EXP_" . $idmember . "." . $extx;
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


				$check = $this->main_mod->msrwhere('m_company', array('desc' => $company), 'id', 'desc')->result();
				if (!isset($check[0])) {
					$row = array(
						'desc' => $company,
					);
					$insert = $this->main_mod->insert('m_company', $row);
				}

				$row = array(
					'user_id' => $idmember,
					'company' => strtoupper($company),
					'title' => strtoupper($title),
					'location' => strtoupper($loc),
					'provinsi' => strtoupper($provinsi),
					'negara' => strtoupper($negara),
					'startyear' => $year,
					'startmonth' => $typetimeperiod,
					'endyear' => $year2,
					'endmonth' => $typetimeperiod2,
					'is_present' => ($work == "true" ? "1" : "0"),
					'actv' => $actv,
					'description' => $desc,
					'attachment' => $url_image,
					'createdby' => $this->session->userdata('admin_id')
					/*'competency' => $competency,
				'p_value' => $p_value,
				'q_value' => $q_value,
				'r_value' => $r_value,
				't_value' => $t_value*/
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
		$idmember = $this->input->post('idmember') <> null ? $this->input->post('idmember') : "";
		if ($idmember == '') {
			redirect('admin/members');
			exit;
		}
		$this->load->model('main_mod');
		//EXPERIENCE
		$title = $this->input->post('title') <> null ? $this->input->post('title') : "";
		$company = $this->input->post('company') <> null ? $this->input->post('company') : "";
		$loc = $this->input->post('location') <> null ? $this->input->post('location') : "";
		$provinsi = $this->input->post('provinsi') <> null ? $this->input->post('provinsi') : "";
		$negara = $this->input->post('negara') <> null ? $this->input->post('negara') : "";
		$year = $this->input->post('startyear') <> null ? $this->input->post('startyear') : "";
		$year2 = $this->input->post('endyear') <> null ? $this->input->post('endyear') : "";
		$typetimeperiod = $this->input->post('startmonth') <> null ? $this->input->post('startmonth') : "";
		$typetimeperiod2 = $this->input->post('endmonth') <> null ? $this->input->post('endmonth') : "";
		$work = $this->input->post('is_present') <> null ? $this->input->post('is_present') : "";
		$actv = $this->input->post('actv') <> null ? $this->input->post('actv') : "";
		$desc = $this->input->post('description') <> null ? $this->input->post('description') : "";
		$id = $this->input->post('id') <> null ? $this->input->post('id') : "";

		//$competency = $this->input->post('competency')<>null?$this->input->post('competency'):"";
		//$p_value = $this->input->post('p_value')<>null?$this->input->post('p_value'):"";
		//$q_value = $this->input->post('q_value')<>null?$this->input->post('q_value'):"";
		//$r_value = $this->input->post('r_value')<>null?$this->input->post('r_value'):"";
		//$t_value = $this->input->post('t_value')<>null?$this->input->post('t_value'):"";

		if ($title != '') {
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
								$actual_image_name = time() . "_EXP_" . $idmember . "." . $extx;
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



				$check = $this->main_mod->msrwhere('m_company', array('desc' => $company), 'id', 'desc')->result();
				if (!isset($check[0])) {
					$row = array(
						'desc' => $company,
					);
					$insert = $this->main_mod->insert('m_company', $row);
				}

				$row = array(
					//'user_id' => $idmember,
					'company' => strtoupper($company),
					'title' => strtoupper($title),
					'location' => strtoupper($loc),
					'provinsi' => strtoupper($provinsi),
					'negara' => strtoupper($negara),
					'startyear' => $year,
					'startmonth' => $typetimeperiod,
					'endyear' => $year2,
					'endmonth' => $typetimeperiod2,
					'is_present' => ($work == "true" ? "1" : "0"),
					'actv' => $actv,
					'description' => $desc,
					'attachment' => $url_image,
					'modifiedby' => $this->session->userdata('admin_id'),
					'modifieddate' => date('Y-m-d')
					/*'competency' => $competency,
				'p_value' => $p_value,
				'q_value' => $q_value,
				'r_value' => $r_value,
				't_value' => $t_value*/
				);

				if ($url_image == '') unset($row['attachment']);

				$where = array(
					"user_id" => $idmember,
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
		$idmember = $this->input->post('idmember') <> null ? $this->input->post('idmember') : "";
		if ($idmember == '') {
			redirect('admin/members');
			exit;
		}
		$this->load->model('main_mod');
		//EXPERIENCE
		$id = $this->input->post('id') <> null ? $this->input->post('id') : "";
		if ($id != '') {
			try {
				$check = $this->main_mod->msrwhere('user_exp', array('user_id' => $idmember, 'id' => $id), 'id', 'desc')->result();
				if (isset($check[0])) {
					$update = $this->main_mod->delete('user_exp', 'id', $id);
					echo 1;
				}
			} catch (Exception $e) {
				print_r($e);
			}
		} else
			echo "not valid";
	}

	function add_edu()
	{
		$idmember = $this->input->post('idmember') <> null ? $this->input->post('idmember') : "";
		if ($idmember == '') {
			redirect('admin/members');
			exit;
		}
		$this->load->model('main_mod');
		//EDUCATION
		$type = $this->input->post('school_type') <> null ? $this->input->post('school_type') : "";
		$school = $this->input->post('school') <> null ? $this->input->post('school') : "";
		$startdate = $this->input->post('dateattend') <> null ? $this->input->post('dateattend') : "";
		$enddate = $this->input->post('dateattend2') <> null ? $this->input->post('dateattend2') : "";
		$degree = $this->input->post('degree') <> null ? $this->input->post('degree') : "";
		$mayor = $this->input->post('mayor') <> null ? $this->input->post('mayor') : "";
		$fieldofstudy = $this->input->post('fos') <> null ? $this->input->post('fos') : "";
		$title_prefix = $this->input->post('title_prefix') <> null ? $this->input->post('title_prefix') : "";
		$title = $this->input->post('title') <> null ? $this->input->post('title') : "";
		$score = $this->input->post('score') <> null ? $this->input->post('score') : "";
		$activities = $this->input->post('actv') <> null ? $this->input->post('actv') : "";
		$description = $this->input->post('descedu') <> null ? $this->input->post('descedu') : "";


		//$competency = $this->input->post('competency')<>null?$this->input->post('competency'):"";
		//$p_value = $this->input->post('p_value')<>null?$this->input->post('p_value'):"";
		//$q_value = $this->input->post('q_value')<>null?$this->input->post('q_value'):"";
		//$r_value = $this->input->post('r_value')<>null?$this->input->post('r_value'):"";
		//$t_value = $this->input->post('t_value')<>null?$this->input->post('t_value'):"";

		if ($school != '') {

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
							$actual_image_name = time() . "_EDU_" . $idmember . "." . $extx;
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
					'user_id' => $idmember,
					'type' => $type,
					'school' => strtoupper($school),
					'startdate' => $startdate,
					'enddate' => $enddate,
					'degree' => $degree,
					'mayor' => strtoupper($mayor),
					'fieldofstudy' => strtoupper($fieldofstudy),
					'title_prefix' => $title_prefix,
					'title' => $title,
					'score' => $score,
					'activities' => strtoupper($activities),
					'description' => $description,
					'attachment' => $url_image,
					'createdby' => $this->session->userdata('admin_id')
					/*'competency' => $competency,
				'p_value' => $p_value,
				'q_value' => $q_value,
				'r_value' => $r_value,
				't_value' => $t_value*/
				);
				$insert = $this->main_mod->insert('user_edu', $row);
				echo $insert;
			} catch (Exception $e) {
				print_r($e);
			}
		} else
			echo "not valid";
	}

	function edit_edu()
	{
		$idmember = $this->input->post('idmember') <> null ? $this->input->post('idmember') : "";
		if ($idmember == '') {
			redirect('admin/members');
			exit;
		}
		$this->load->model('main_mod');
		//EDUCATION
		$type = $this->input->post('school_type') <> null ? $this->input->post('school_type') : "";
		$school = $this->input->post('school') <> null ? $this->input->post('school') : "";
		$startdate = $this->input->post('dateattend') <> null ? $this->input->post('dateattend') : "";
		$enddate = $this->input->post('dateattend2') <> null ? $this->input->post('dateattend2') : "";
		$degree = $this->input->post('degree') <> null ? $this->input->post('degree') : "";
		$mayor = $this->input->post('mayor') <> null ? $this->input->post('mayor') : "";
		$fieldofstudy = $this->input->post('fos') <> null ? $this->input->post('fos') : "";
		$title_prefix = $this->input->post('title_prefix') <> null ? $this->input->post('title_prefix') : "";
		$title = $this->input->post('title') <> null ? $this->input->post('title') : "";
		$score = $this->input->post('score') <> null ? $this->input->post('score') : "";
		$activities = $this->input->post('actv') <> null ? $this->input->post('actv') : "";
		$description = $this->input->post('descedu') <> null ? $this->input->post('descedu') : "";
		$id = $this->input->post('id') <> null ? $this->input->post('id') : "";




		if ($school != '') {

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
							$actual_image_name = time() . "_EDU_" . $idmember . "." . $extx;
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
					'type' => $type,
					'school' => strtoupper($school),
					'startdate' => $startdate,
					'enddate' => $enddate,
					'degree' => $degree,
					'mayor' => strtoupper($mayor),
					'fieldofstudy' => strtoupper($fieldofstudy),
					'title_prefix' => $title_prefix,
					'title' => $title,
					'score' => $score,
					'activities' => strtoupper($activities),
					'description' => $description,
					'attachment' => $url_image,
					'modifiedby' => $this->session->userdata('admin_id'),
					'modifieddate' => date('Y-m-d')
				);

				if ($url_image == '') unset($row['attachment']);

				$where = array(
					"user_id" => $idmember,
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

	function del_edu()
	{
		$idmember = $this->input->post('idmember') <> null ? $this->input->post('idmember') : "";
		if ($idmember == '') {
			redirect('admin/members');
			exit;
		}
		$this->load->model('main_mod');
		//EDUCATION
		$id = $this->input->post('id') <> null ? $this->input->post('id') : "";
		if ($id != '') {
			try {
				$check = $this->main_mod->msrwhere('user_edu', array('user_id' => $idmember, 'id' => $id), 'id', 'desc')->result();
				if (isset($check[0])) {
					//$update = $this->main_mod->delete('user_edu','id',$id);

					$row = array(
						'status' => 0,
					);

					$where = array(
						"id" => $id,
					);
					$update = $this->main_mod->update('user_edu', $where, $row);

					echo 1;
				}
			} catch (Exception $e) {
				print_r($e);
			}
		} else
			echo "not valid";
	}

	function add_cert()
	{
		$idmember = $this->input->post('idmember') <> null ? $this->input->post('idmember') : "";
		if ($idmember == '') {
			redirect('admin/members');
			exit;
		}
		$this->load->model('main_mod');
		//CERTIFICATIONS
		$cert_name = $this->input->post('certname') <> null ? $this->input->post('certname') : "";
		$cert_auth = $this->input->post('certauth') <> null ? $this->input->post('certauth') : "";
		$lic_num = $this->input->post('lic') <> null ? $this->input->post('lic') : "";
		$cert_title = $this->input->post('title') <> null ? $this->input->post('title') : "";
		$cert_url = $this->input->post('url') <> null ? $this->input->post('url') : "";
		$startmonth = $this->input->post('certdate') <> null ? $this->input->post('certdate') : "";
		$startyear = $this->input->post('certyear') <> null ? $this->input->post('certyear') : "";
		$endmonth = $this->input->post('certdate2') <> null ? $this->input->post('certdate2') : "";
		$endyear = $this->input->post('certyear2') <> null ? $this->input->post('certyear2') : "";
		$is_present = $this->input->post('certwork') <> null ? $this->input->post('certwork') : "";
		$description = $this->input->post('certdesc') <> null ? $this->input->post('certdesc') : "";

		//$competency = $this->input->post('competency')<>null?$this->input->post('competency'):"";
		//$p_value = $this->input->post('p_value')<>null?$this->input->post('p_value'):"";
		//$q_value = $this->input->post('q_value')<>null?$this->input->post('q_value'):"";
		//$r_value = $this->input->post('r_value')<>null?$this->input->post('r_value'):"";
		//$t_value = $this->input->post('t_value')<>null?$this->input->post('t_value'):"";
		if ($cert_name != '') {
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
								$actual_image_name = time() . "_CERT_" . $idmember . "." . $extx;
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
					'user_id' => $idmember,
					'cert_name' => strtoupper($cert_name),
					'cert_auth' => strtoupper($cert_auth),
					'cert_title' => $cert_title,
					'lic_num' => $lic_num,
					'cert_url' => $cert_url,
					'startmonth' => $startmonth,
					'startyear' => $startyear,
					'endmonth' => $endmonth,
					'endyear' => $endyear,
					'is_present' => ($is_present == "true" ? "1" : "0"),
					'description' => $description,
					'attachment' => $url_image,
					'createdby' => $this->session->userdata('admin_id')
					/*'competency' => $competency,
				'p_value' => $p_value,
				'q_value' => $q_value,
				'r_value' => $r_value,
				't_value' => $t_value*/
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
		$idmember = $this->input->post('idmember') <> null ? $this->input->post('idmember') : "";
		if ($idmember == '') {
			redirect('admin/members');
			exit;
		}
		$this->load->model('main_mod');
		//CERTIFICATIONS
		$cert_name = $this->input->post('certname') <> null ? $this->input->post('certname') : "";
		$cert_auth = $this->input->post('certauth') <> null ? $this->input->post('certauth') : "";
		$lic_num = $this->input->post('lic') <> null ? $this->input->post('lic') : "";
		$cert_url = $this->input->post('url') <> null ? $this->input->post('url') : "";
		$cert_title = $this->input->post('title') <> null ? $this->input->post('title') : "";
		$startmonth = $this->input->post('certdate') <> null ? $this->input->post('certdate') : "";
		$startyear = $this->input->post('certyear') <> null ? $this->input->post('certyear') : "";
		$endmonth = $this->input->post('certdate2') <> null ? $this->input->post('certdate2') : "";
		$endyear = $this->input->post('certyear2') <> null ? $this->input->post('certyear2') : "";
		$is_present = $this->input->post('certwork') <> null ? $this->input->post('certwork') : "";
		$description = $this->input->post('certdesc') <> null ? $this->input->post('certdesc') : "";
		$id = $this->input->post('id') <> null ? $this->input->post('id') : "";

		//$competency = $this->input->post('competency')<>null?$this->input->post('competency'):"";
		//$p_value = $this->input->post('p_value')<>null?$this->input->post('p_value'):"";
		//$q_value = $this->input->post('q_value')<>null?$this->input->post('q_value'):"";
		//$r_value = $this->input->post('r_value')<>null?$this->input->post('r_value'):"";
		//$t_value = $this->input->post('t_value')<>null?$this->input->post('t_value'):"";
		if ($cert_name != '') {
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
								$actual_image_name = time() . "_CERT_" . $idmember . "." . $extx;
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
					'cert_name' => strtoupper($cert_name),
					'cert_auth' => strtoupper($cert_auth),
					'lic_num' => $lic_num,
					'cert_url' => $cert_url,
					'cert_title' => $cert_title,
					'startmonth' => $startmonth,
					'startyear' => $startyear,
					'endmonth' => $endmonth,
					'endyear' => $endyear,
					'is_present' => ($is_present == "true" ? "1" : "0"),
					'description' => $description,
					'attachment' => $url_image,
					'modifiedby' => $this->session->userdata('admin_id'),
					'modifieddate' => date('Y-m-d')
					/*'competency' => $competency,
				'p_value' => $p_value,
				'q_value' => $q_value,
				'r_value' => $r_value,
				't_value' => $t_value*/
				);

				if ($url_image == '') unset($row['attachment']);

				$where = array(
					"user_id" => $idmember,
					"id" => $id,
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
		$idmember = $this->input->post('idmember') <> null ? $this->input->post('idmember') : "";
		if ($idmember == '') {
			redirect('admin/members');
			exit;
		}
		$this->load->model('main_mod');
		//CERTIFICATIONS
		$id = $this->input->post('id') <> null ? $this->input->post('id') : "";
		if ($id != '') {
			try {
				$check = $this->main_mod->msrwhere('user_cert', array('user_id' => $idmember, 'id' => $id, 'status' => 1), 'id', 'desc')->result();
				if (isset($check[0])) {
					$update = $this->main_mod->delete('user_cert', 'id', $id);
					echo 1;
				}
			} catch (Exception $e) {
				print_r($e);
			}
		} else
			echo "not valid";
	}

	function add_org()
	{
		$idmember = $this->input->post('idmember') <> null ? $this->input->post('idmember') : "";
		if ($idmember == '') {
			redirect('admin/members');
			exit;
		}
		$this->load->model('main_mod');
		//ORGANIZATIONS
		$organization = $this->input->post('org') <> null ? $this->input->post('org') : "";
		$position = $this->input->post('posit') <> null ? $this->input->post('posit') : "";
		$occupation = $this->input->post('occ') <> null ? $this->input->post('occ') : "";
		$startmonth = $this->input->post('orgdate') <> null ? $this->input->post('orgdate') : "";
		$startyear = $this->input->post('orgyear') <> null ? $this->input->post('orgyear') : "";
		$endmonth = $this->input->post('orgdate2') <> null ? $this->input->post('orgdate2') : "";
		$endyear = $this->input->post('orgyear2') <> null ? $this->input->post('orgyear2') : "";
		$is_present = $this->input->post('orgwork') <> null ? $this->input->post('orgwork') : "";
		$description = $this->input->post('orgdesc') <> null ? $this->input->post('orgdesc') : "";

		$competency = $this->input->post('competency') <> null ? $this->input->post('competency') : "";
		$p_value = $this->input->post('p_value') <> null ? $this->input->post('p_value') : "";
		$q_value = $this->input->post('q_value') <> null ? $this->input->post('q_value') : "";
		$r_value = $this->input->post('r_value') <> null ? $this->input->post('r_value') : "";
		$t_value = $this->input->post('t_value') <> null ? $this->input->post('t_value') : "";
		if ($organization != '') {
			try {
				$row = array(
					'user_id' => $idmember,
					'organization' => $organization,
					'position' => $position,
					'occupation' => $occupation,
					'startmonth' => $startmonth,
					'startyear' => $startyear,
					'endmonth' => $endmonth,
					'endyear' => $endyear,
					'is_present' => ($is_present == "true" ? "1" : "0"),
					'description' => $description,

					'competency' => $competency,
					'p_value' => $p_value,
					'q_value' => $q_value,
					'r_value' => $r_value,
					't_value' => $t_value
				);
				$insert = $this->main_mod->insert('user_org', $row);
				echo $insert;
			} catch (Exception $e) {
				print_r($e);
			}
		} else
			echo "not valid";
	}

	function edit_org()
	{
		$idmember = $this->input->post('idmember') <> null ? $this->input->post('idmember') : "";
		if ($idmember == '') {
			redirect('admin/members');
			exit;
		}
		$this->load->model('main_mod');
		//ORGANIZATIONS
		$organization = $this->input->post('org') <> null ? $this->input->post('org') : "";
		$position = $this->input->post('posit') <> null ? $this->input->post('posit') : "";
		$occupation = $this->input->post('occ') <> null ? $this->input->post('occ') : "";
		$startmonth = $this->input->post('orgdate') <> null ? $this->input->post('orgdate') : "";
		$startyear = $this->input->post('orgyear') <> null ? $this->input->post('orgyear') : "";
		$endmonth = $this->input->post('orgdate2') <> null ? $this->input->post('orgdate2') : "";
		$endyear = $this->input->post('orgyear2') <> null ? $this->input->post('orgyear2') : "";
		$is_present = $this->input->post('orgwork') <> null ? $this->input->post('orgwork') : "";
		$description = $this->input->post('orgdesc') <> null ? $this->input->post('orgdesc') : "";
		$id = $this->input->post('id') <> null ? $this->input->post('id') : "";

		$competency = $this->input->post('competency') <> null ? $this->input->post('competency') : "";
		$p_value = $this->input->post('p_value') <> null ? $this->input->post('p_value') : "";
		$q_value = $this->input->post('q_value') <> null ? $this->input->post('q_value') : "";
		$r_value = $this->input->post('r_value') <> null ? $this->input->post('r_value') : "";
		$t_value = $this->input->post('t_value') <> null ? $this->input->post('t_value') : "";
		if ($organization != '') {
			try {
				$row = array(
					'organization' => $organization,
					'position' => $position,
					'occupation' => $occupation,
					'startmonth' => $startmonth,
					'startyear' => $startyear,
					'endmonth' => $endmonth,
					'endyear' => $endyear,
					'is_present' => ($is_present == "true" ? "1" : "0"),
					'description' => $description,

					'competency' => $competency,
					'p_value' => $p_value,
					'q_value' => $q_value,
					'r_value' => $r_value,
					't_value' => $t_value
				);
				$where = array(
					"user_id" => $idmember,
					"id" => $id,
				);
				$update = $this->main_mod->update('user_org', $where, $row);

				echo 1;
			} catch (Exception $e) {
				print_r($e);
			}
		} else
			echo "not valid";
	}

	function del_org()
	{
		$idmember = $this->input->post('idmember') <> null ? $this->input->post('idmember') : "";
		if ($idmember == '') {
			redirect('admin/members');
			exit;
		}
		$this->load->model('main_mod');
		//ORGANIZATIONS
		$id = $this->input->post('id') <> null ? $this->input->post('id') : "";
		if ($id != '') {
			try {
				$check = $this->main_mod->msrwhere('user_org', array('user_id' => $idmember, 'id' => $id), 'id', 'desc')->result();
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

	function add_award()
	{
		$idmember = $this->input->post('idmember') <> null ? $this->input->post('idmember') : "";
		if ($idmember == '') {
			redirect('admin/members');
			exit;
		}
		$this->load->model('main_mod');
		//AWARD
		$name = $this->input->post('awardname') <> null ? $this->input->post('awardname') : "";
		$issue = $this->input->post('issue') <> null ? $this->input->post('issue') : "";
		$description = $this->input->post('awarddesc') <> null ? $this->input->post('awarddesc') : "";

		$competency = $this->input->post('competency') <> null ? $this->input->post('competency') : "";
		$p_value = $this->input->post('p_value') <> null ? $this->input->post('p_value') : "";
		$q_value = $this->input->post('q_value') <> null ? $this->input->post('q_value') : "";
		$r_value = $this->input->post('r_value') <> null ? $this->input->post('r_value') : "";
		$t_value = $this->input->post('t_value') <> null ? $this->input->post('t_value') : "";
		if ($name != '') {
			try {
				$row = array(
					'user_id' => $idmember,
					'name' => $name,
					'issue' => $issue,
					'description' => $description,

					'competency' => $competency,
					'p_value' => $p_value,
					'q_value' => $q_value,
					'r_value' => $r_value,
					't_value' => $t_value
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
		$idmember = $this->input->post('idmember') <> null ? $this->input->post('idmember') : "";
		if ($idmember == '') {
			redirect('admin/members');
			exit;
		}
		$this->load->model('main_mod');
		//AWARD
		$name = $this->input->post('awardname') <> null ? $this->input->post('awardname') : "";
		$issue = $this->input->post('issue') <> null ? $this->input->post('issue') : "";
		$description = $this->input->post('awarddesc') <> null ? $this->input->post('awarddesc') : "";
		$id = $this->input->post('id') <> null ? $this->input->post('id') : "";

		$competency = $this->input->post('competency') <> null ? $this->input->post('competency') : "";
		$p_value = $this->input->post('p_value') <> null ? $this->input->post('p_value') : "";
		$q_value = $this->input->post('q_value') <> null ? $this->input->post('q_value') : "";
		$r_value = $this->input->post('r_value') <> null ? $this->input->post('r_value') : "";
		$t_value = $this->input->post('t_value') <> null ? $this->input->post('t_value') : "";
		if ($name != '') {
			try {
				$row = array(
					'name' => $name,
					'issue' => $issue,
					'description' => $description,

					'competency' => $competency,
					'p_value' => $p_value,
					'q_value' => $q_value,
					'r_value' => $r_value,
					't_value' => $t_value
				);
				$where = array(
					"user_id" => $idmember,
					"id" => $id,
				);
				$update = $this->main_mod->update('user_award', $where, $row);

				echo 1;
			} catch (Exception $e) {
				print_r($e);
			}
		} else
			echo "not valid";
	}

	function del_award()
	{
		$idmember = $this->input->post('idmember') <> null ? $this->input->post('idmember') : "";
		if ($idmember == '') {
			redirect('admin/members');
			exit;
		}
		$this->load->model('main_mod');
		//AWARD
		$id = $this->input->post('id') <> null ? $this->input->post('id') : "";
		if ($id != '') {
			try {
				$check = $this->main_mod->msrwhere('user_award', array('user_id' => $idmember, 'id' => $id), 'id', 'desc')->result();
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

	function add_course()
	{
		$idmember = $this->input->post('idmember') <> null ? $this->input->post('idmember') : "";
		if ($idmember == '') {
			redirect('admin/members');
			exit;
		}
		$this->load->model('main_mod');
		//COURSE
		$coursename = $this->input->post('coursename') <> null ? $this->input->post('coursename') : "";
		$hour = $this->input->post('hour') <> null ? $this->input->post('hour') : "";
		$courseorg = $this->input->post('courseorg') <> null ? $this->input->post('courseorg') : "";
		$startmonth = $this->input->post('courseperiod') <> null ? $this->input->post('courseperiod') : "";
		$startyear = $this->input->post('courseyear') <> null ? $this->input->post('courseyear') : "";
		$endmonth = $this->input->post('courseperiod2') <> null ? $this->input->post('courseperiod2') : "";
		$endyear = $this->input->post('courseyear2') <> null ? $this->input->post('courseyear2') : "";

		$competency = $this->input->post('competency') <> null ? $this->input->post('competency') : "";
		$p_value = $this->input->post('p_value') <> null ? $this->input->post('p_value') : "";
		$q_value = $this->input->post('q_value') <> null ? $this->input->post('q_value') : "";
		$r_value = $this->input->post('r_value') <> null ? $this->input->post('r_value') : "";
		$t_value = $this->input->post('t_value') <> null ? $this->input->post('t_value') : "";
		if ($coursename != '') {
			try {
				$row = array(
					'user_id' => $idmember,
					'coursename' => $coursename,
					'hour' => $hour,
					'courseorg' => $courseorg,
					'startmonth' => $startmonth,
					'startyear' => $startyear,
					'endmonth' => $endmonth,
					'endyear' => $endyear,

					'competency' => $competency,
					'p_value' => $p_value,
					'q_value' => $q_value,
					'r_value' => $r_value,
					't_value' => $t_value
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
		$idmember = $this->input->post('idmember') <> null ? $this->input->post('idmember') : "";
		if ($idmember == '') {
			redirect('admin/members');
			exit;
		}
		$this->load->model('main_mod');
		//COURSE
		$coursename = $this->input->post('coursename') <> null ? $this->input->post('coursename') : "";
		$hour = $this->input->post('hour') <> null ? $this->input->post('hour') : "";
		$courseorg = $this->input->post('courseorg') <> null ? $this->input->post('courseorg') : "";
		$startmonth = $this->input->post('courseperiod') <> null ? $this->input->post('courseperiod') : "";
		$startyear = $this->input->post('courseyear') <> null ? $this->input->post('courseyear') : "";
		$endmonth = $this->input->post('courseperiod2') <> null ? $this->input->post('courseperiod2') : "";
		$endyear = $this->input->post('courseyear2') <> null ? $this->input->post('courseyear2') : "";

		$competency = $this->input->post('competency') <> null ? $this->input->post('competency') : "";
		$p_value = $this->input->post('p_value') <> null ? $this->input->post('p_value') : "";
		$q_value = $this->input->post('q_value') <> null ? $this->input->post('q_value') : "";
		$r_value = $this->input->post('r_value') <> null ? $this->input->post('r_value') : "";
		$t_value = $this->input->post('t_value') <> null ? $this->input->post('t_value') : "";

		$id = $this->input->post('id') <> null ? $this->input->post('id') : "";
		if ($coursename != '') {
			try {
				$row = array(
					'coursename' => $coursename,
					'hour' => $hour,
					'courseorg' => $courseorg,
					'startmonth' => $startmonth,
					'startyear' => $startyear,
					'endmonth' => $endmonth,
					'endyear' => $endyear,

					'competency' => $competency,
					'p_value' => $p_value,
					'q_value' => $q_value,
					'r_value' => $r_value,
					't_value' => $t_value
				);
				$where = array(
					"user_id" => $idmember,
					"id" => $id,
				);
				$update = $this->main_mod->update('user_course', $where, $row);

				echo 1;
			} catch (Exception $e) {
				print_r($e);
			}
		} else
			echo "not valid";
	}

	function del_course()
	{
		$idmember = $this->input->post('idmember') <> null ? $this->input->post('idmember') : "";
		if ($idmember == '') {
			redirect('admin/members');
			exit;
		}
		$this->load->model('main_mod');
		//COURSE
		$id = $this->input->post('id') <> null ? $this->input->post('id') : "";
		if ($id != '') {
			try {
				$check = $this->main_mod->msrwhere('user_course', array('user_id' => $idmember, 'id' => $id), 'id', 'desc')->result();
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

	function add_prof()
	{
		$idmember = $this->input->post('idmember') <> null ? $this->input->post('idmember') : "";
		if ($idmember == '') {
			redirect('admin/members');
			exit;
		}
		$this->load->model('main_mod');
		//PROFESIONAL
		$organization = $this->input->post('proforg') <> null ? $this->input->post('proforg') : "";
		$type = $this->input->post('proftype') <> null ? $this->input->post('proftype') : "";
		$position = $this->input->post('profposit') <> null ? $this->input->post('profposit') : "";
		$startmonth = $this->input->post('profperiod') <> null ? $this->input->post('profperiod') : "";
		$startyear = $this->input->post('profyear') <> null ? $this->input->post('profyear') : "";
		$endmonth = $this->input->post('profperiod2') <> null ? $this->input->post('profperiod2') : "";
		$endyear = $this->input->post('profyear2') <> null ? $this->input->post('profyear2') : "";
		$subject = $this->input->post('profsubject') <> null ? $this->input->post('profsubject') : "";
		$description = $this->input->post('profdesc') <> null ? $this->input->post('profdesc') : "";

		$competency = $this->input->post('competency') <> null ? $this->input->post('competency') : "";
		$p_value = $this->input->post('p_value') <> null ? $this->input->post('p_value') : "";
		$q_value = $this->input->post('q_value') <> null ? $this->input->post('q_value') : "";
		$r_value = $this->input->post('r_value') <> null ? $this->input->post('r_value') : "";
		$t_value = $this->input->post('t_value') <> null ? $this->input->post('t_value') : "";
		if ($organization != '') {
			try {
				$row = array(
					'user_id' => $idmember,
					'organization' => $organization,
					'type' => $type,
					'position' => $position,
					'subject' => $subject,
					'description' => $description,
					'startmonth' => $startmonth,
					'startyear' => $startyear,
					'endmonth' => $endmonth,
					'endyear' => $endyear,

					'competency' => $competency,
					'p_value' => $p_value,
					'q_value' => $q_value,
					'r_value' => $r_value,
					't_value' => $t_value
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
		$idmember = $this->input->post('idmember') <> null ? $this->input->post('idmember') : "";
		if ($idmember == '') {
			redirect('admin/members');
			exit;
		}
		$this->load->model('main_mod');
		//PROFESIONAL
		$organization = $this->input->post('proforg') <> null ? $this->input->post('proforg') : "";
		$type = $this->input->post('proftype') <> null ? $this->input->post('proftype') : "";
		$position = $this->input->post('profposit') <> null ? $this->input->post('profposit') : "";
		$startmonth = $this->input->post('profperiod') <> null ? $this->input->post('profperiod') : "";
		$startyear = $this->input->post('profyear') <> null ? $this->input->post('profyear') : "";
		$endmonth = $this->input->post('profperiod2') <> null ? $this->input->post('profperiod2') : "";
		$endyear = $this->input->post('profyear2') <> null ? $this->input->post('profyear2') : "";
		$subject = $this->input->post('profsubject') <> null ? $this->input->post('profsubject') : "";
		$description = $this->input->post('profdesc') <> null ? $this->input->post('profdesc') : "";


		$competency = $this->input->post('competency') <> null ? $this->input->post('competency') : "";
		$p_value = $this->input->post('p_value') <> null ? $this->input->post('p_value') : "";
		$q_value = $this->input->post('q_value') <> null ? $this->input->post('q_value') : "";
		$r_value = $this->input->post('r_value') <> null ? $this->input->post('r_value') : "";
		$t_value = $this->input->post('t_value') <> null ? $this->input->post('t_value') : "";

		$id = $this->input->post('id') <> null ? $this->input->post('id') : "";
		if ($organization != '') {
			try {
				$row = array(
					'organization' => $organization,
					'type' => $type,
					'position' => $position,
					'subject' => $subject,
					'description' => $description,
					'startmonth' => $startmonth,
					'startyear' => $startyear,
					'endmonth' => $endmonth,
					'endyear' => $endyear,

					'competency' => $competency,
					'p_value' => $p_value,
					'q_value' => $q_value,
					'r_value' => $r_value,
					't_value' => $t_value
				);
				$where = array(
					"user_id" => $idmember,
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
		$idmember = $this->input->post('idmember') <> null ? $this->input->post('idmember') : "";
		if ($idmember == '') {
			redirect('admin/members');
			exit;
		}
		$this->load->model('main_mod');
		//PROFESIONAL
		$id = $this->input->post('id') <> null ? $this->input->post('id') : "";
		if ($id != '') {
			try {
				$check = $this->main_mod->msrwhere('user_prof', array('user_id' => $idmember, 'id' => $id), 'id', 'desc')->result();
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

	function add_publication()
	{
		$idmember = $this->input->post('idmember') <> null ? $this->input->post('idmember') : "";
		if ($idmember == '') {
			redirect('admin/members');
			exit;
		}
		$this->load->model('main_mod');
		//PUBLICATION
		$topic = $this->input->post('publicationtopic') <> null ? $this->input->post('publicationtopic') : "";
		$type = $this->input->post('publicationtype') <> null ? $this->input->post('publicationtype') : "";
		$media = $this->input->post('publicationmedia') <> null ? $this->input->post('publicationmedia') : "";
		$startmonth = $this->input->post('publicationperiod') <> null ? $this->input->post('publicationperiod') : "";
		$startyear = $this->input->post('publicationyear') <> null ? $this->input->post('publicationyear') : "";
		$endmonth = $this->input->post('publicationperiod2') <> null ? $this->input->post('publicationperiod2') : "";
		$endyear = $this->input->post('publicationyear2') <> null ? $this->input->post('publicationyear2') : "";
		$journal = $this->input->post('publicationjournal') <> null ? $this->input->post('publicationjournal') : "";
		$event = $this->input->post('publicationevent') <> null ? $this->input->post('publicationevent') : "";
		$description = $this->input->post('publicationdesc') <> null ? $this->input->post('publicationdesc') : "";

		$competency = $this->input->post('competency') <> null ? $this->input->post('competency') : "";
		$p_value = $this->input->post('p_value') <> null ? $this->input->post('p_value') : "";
		$q_value = $this->input->post('q_value') <> null ? $this->input->post('q_value') : "";
		$r_value = $this->input->post('r_value') <> null ? $this->input->post('r_value') : "";
		$t_value = $this->input->post('t_value') <> null ? $this->input->post('t_value') : "";
		if ($topic != '') {
			try {
				$row = array(
					'user_id' => $idmember,
					'topic' => $topic,
					'media' => $media,
					'type' => $type,
					'journal' => $journal,
					'event' => $event,
					'description' => $description,
					'startmonth' => $startmonth,
					'startyear' => $startyear,
					'endmonth' => $endmonth,
					'endyear' => $endyear,

					'competency' => $competency,
					'p_value' => $p_value,
					'q_value' => $q_value,
					'r_value' => $r_value,
					't_value' => $t_value
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
		$idmember = $this->input->post('idmember') <> null ? $this->input->post('idmember') : "";
		if ($idmember == '') {
			redirect('admin/members');
			exit;
		}
		$this->load->model('main_mod');
		//PUBLICATION
		$topic = $this->input->post('publicationtopic') <> null ? $this->input->post('publicationtopic') : "";
		$type = $this->input->post('publicationtype') <> null ? $this->input->post('publicationtype') : "";
		$media = $this->input->post('publicationmedia') <> null ? $this->input->post('publicationmedia') : "";
		$startmonth = $this->input->post('publicationperiod') <> null ? $this->input->post('publicationperiod') : "";
		$startyear = $this->input->post('publicationyear') <> null ? $this->input->post('publicationyear') : "";
		$endmonth = $this->input->post('publicationperiod2') <> null ? $this->input->post('publicationperiod2') : "";
		$endyear = $this->input->post('publicationyear2') <> null ? $this->input->post('publicationyear2') : "";
		$journal = $this->input->post('publicationjournal') <> null ? $this->input->post('publicationjournal') : "";
		$event = $this->input->post('publicationevent') <> null ? $this->input->post('publicationevent') : "";
		$description = $this->input->post('publicationdesc') <> null ? $this->input->post('publicationdesc') : "";


		$competency = $this->input->post('competency') <> null ? $this->input->post('competency') : "";
		$p_value = $this->input->post('p_value') <> null ? $this->input->post('p_value') : "";
		$q_value = $this->input->post('q_value') <> null ? $this->input->post('q_value') : "";
		$r_value = $this->input->post('r_value') <> null ? $this->input->post('r_value') : "";
		$t_value = $this->input->post('t_value') <> null ? $this->input->post('t_value') : "";
		$id = $this->input->post('id') <> null ? $this->input->post('id') : "";
		if ($topic != '') {
			try {
				$row = array(
					'topic' => $topic,
					'media' => $media,
					'type' => $type,
					'journal' => $journal,
					'event' => $event,
					'description' => $description,
					'startmonth' => $startmonth,
					'startyear' => $startyear,
					'endmonth' => $endmonth,
					'endyear' => $endyear,

					'competency' => $competency,
					'p_value' => $p_value,
					'q_value' => $q_value,
					'r_value' => $r_value,
					't_value' => $t_value
				);
				$where = array(
					"user_id" => $idmember,
					"id" => $id,
				);
				$update = $this->main_mod->update('user_publication', $where, $row);

				echo 1;
			} catch (Exception $e) {
				print_r($e);
			}
		} else
			echo "not valid";
	}

	function del_publication()
	{
		$idmember = $this->input->post('idmember') <> null ? $this->input->post('idmember') : "";
		if ($idmember == '') {
			redirect('admin/members');
			exit;
		}
		$this->load->model('main_mod');
		//PUBLICATION
		$id = $this->input->post('id') <> null ? $this->input->post('id') : "";
		if ($id != '') {
			try {
				$check = $this->main_mod->msrwhere('user_publication', array('user_id' => $idmember, 'id' => $id), 'id', 'desc')->result();
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

	function add_skill()
	{
		$idmember = $this->input->post('idmember') <> null ? $this->input->post('idmember') : "";
		if ($idmember == '') {
			redirect('admin/members');
			exit;
		}
		$this->load->model('main_mod');
		//SKILL
		$name = $this->input->post('skillname') <> null ? $this->input->post('skillname') : "";
		$proficiency = $this->input->post('proficiency') <> null ? $this->input->post('proficiency') : "";
		$description = $this->input->post('skilldesc') <> null ? $this->input->post('skilldesc') : "";

		$competency = $this->input->post('competency') <> null ? $this->input->post('competency') : "";
		$p_value = $this->input->post('p_value') <> null ? $this->input->post('p_value') : "";
		$q_value = $this->input->post('q_value') <> null ? $this->input->post('q_value') : "";
		$r_value = $this->input->post('r_value') <> null ? $this->input->post('r_value') : "";
		$t_value = $this->input->post('t_value') <> null ? $this->input->post('t_value') : "";
		if ($name != '') {
			try {
				$row = array(
					'user_id' => $idmember,
					'name' => $name,
					'proficiency' => $proficiency,
					'description' => $description,

					'competency' => $competency,
					'p_value' => $p_value,
					'q_value' => $q_value,
					'r_value' => $r_value,
					't_value' => $t_value
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
		$idmember = $this->input->post('idmember') <> null ? $this->input->post('idmember') : "";
		if ($idmember == '') {
			redirect('admin/members');
			exit;
		}
		$this->load->model('main_mod');
		//SKILL
		$name = $this->input->post('skillname') <> null ? $this->input->post('skillname') : "";
		$proficiency = $this->input->post('proficiency') <> null ? $this->input->post('proficiency') : "";
		$description = $this->input->post('skilldesc') <> null ? $this->input->post('skilldesc') : "";

		$competency = $this->input->post('competency') <> null ? $this->input->post('competency') : "";
		$p_value = $this->input->post('p_value') <> null ? $this->input->post('p_value') : "";
		$q_value = $this->input->post('q_value') <> null ? $this->input->post('q_value') : "";
		$r_value = $this->input->post('r_value') <> null ? $this->input->post('r_value') : "";
		$t_value = $this->input->post('t_value') <> null ? $this->input->post('t_value') : "";

		$id = $this->input->post('id') <> null ? $this->input->post('id') : "";
		if ($name != '') {
			try {
				$row = array(
					'name' => $name,
					'proficiency' => $proficiency,
					'description' => $description,

					'competency' => $competency,
					'p_value' => $p_value,
					'q_value' => $q_value,
					'r_value' => $r_value,
					't_value' => $t_value
				);
				$where = array(
					"user_id" => $idmember,
					"id" => $id,
				);
				$update = $this->main_mod->update('user_skill', $where, $row);

				echo 1;
			} catch (Exception $e) {
				print_r($e);
			}
		} else
			echo "not valid";
	}

	function del_skill()
	{
		$idmember = $this->input->post('idmember') <> null ? $this->input->post('idmember') : "";
		if ($idmember == '') {
			redirect('admin/members');
			exit;
		}
		$this->load->model('main_mod');
		//SKILL
		$id = $this->input->post('id') <> null ? $this->input->post('id') : "";
		if ($id != '') {
			try {
				$check = $this->main_mod->msrwhere('user_skill', array('user_id' => $idmember, 'id' => $id), 'id', 'desc')->result();
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
		$idmember = $this->input->post('idmember') <> null ? $this->input->post('idmember') : "";
		if ($idmember == '') {
			redirect('admin/members');
			exit;
		}
		$this->load->model('main_mod');
		//SKILL
		$fieldofexpert = $this->input->post('fieldofexpert') <> null ? $this->input->post('fieldofexpert') : "";
		$accauth = $this->input->post('accauth') <> null ? $this->input->post('accauth') : "";
		$subfield = $this->input->post('subfield') <> null ? $this->input->post('subfield') : "";
		$filename = $this->input->post('filename') <> null ? $this->input->post('filename') : "";
		$desc2 = $this->input->post('desc2') <> null ? $this->input->post('desc2') : "";
		$competency = $this->input->post('competency') <> null ? $this->input->post('competency') : "";
		$p_value = $this->input->post('p_value') <> null ? $this->input->post('p_value') : "";
		$q_value = $this->input->post('q_value') <> null ? $this->input->post('q_value') : "";
		$r_value = $this->input->post('r_value') <> null ? $this->input->post('r_value') : "";
		$t_value = $this->input->post('t_value') <> null ? $this->input->post('t_value') : "";
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
					'user_id' => $idmember,
					'fieldofexpert' => $fieldofexpert,
					'accauth' => $accauth,
					'subfield' => $subfield,
					'document' => $nameDoc,
					'description' => $desc2,
					'competency' => $competency,
					'p_value' => $p_value,
					'q_value' => $q_value,
					'r_value' => $r_value,
					't_value' => $t_value
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
		$idmember = $this->input->post('idmember') <> null ? $this->input->post('idmember') : "";
		if ($idmember == '') {
			redirect('admin/members');
			exit;
		}
		$this->load->model('main_mod');
		//SKILL
		$fieldofexpert = $this->input->post('fieldofexpert') <> null ? $this->input->post('fieldofexpert') : "";
		$accauth = $this->input->post('accauth') <> null ? $this->input->post('accauth') : "";
		$subfield = $this->input->post('subfield') <> null ? $this->input->post('subfield') : "";
		$filename = $this->input->post('filename') <> null ? $this->input->post('filename') : "";
		$desc2 = $this->input->post('desc2') <> null ? $this->input->post('desc2') : "";
		$competency = $this->input->post('competency') <> null ? $this->input->post('competency') : "";
		$p_value = $this->input->post('p_value') <> null ? $this->input->post('p_value') : "";
		$q_value = $this->input->post('q_value') <> null ? $this->input->post('q_value') : "";
		$r_value = $this->input->post('r_value') <> null ? $this->input->post('r_value') : "";
		$t_value = $this->input->post('t_value') <> null ? $this->input->post('t_value') : "";

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
						'description' => $desc2,
						'competency' => $competency,
						'p_value' => $p_value,
						'q_value' => $q_value,
						'r_value' => $r_value,
						't_value' => $t_value
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
					"user_id" => $idmember,
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
		$idmember = $this->input->post('idmember') <> null ? $this->input->post('idmember') : "";
		if ($idmember == '') {
			redirect('admin/members');
			exit;
		}
		$this->load->model('main_mod');
		//SKILL
		$id = $this->input->post('id') <> null ? $this->input->post('id') : "";
		if ($id != '') {
			try {
				$check = $this->main_mod->msrwhere('user_reg', array('user_id' => $idmember, 'id' => $id), 'id', 'desc')->result();
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
		$idmember = $this->input->post('idmember') <> null ? $this->input->post('idmember') : "";
		if ($idmember == '') {
			redirect('admin/members');
			exit;
		}

		if (isset($_FILES['userfilegroup']['name'])) {
			//$valid_formats_img = array("jpg", "jpeg", "gif","png");
			$name = $_FILES['userfilegroup']['name'];
			$size = $_FILES['userfilegroup']['size'];



			if (strlen($name)) {
				list($txt, $ext) = explode(".", $name);
				//if(in_array($ext, $valid_formats_img))
				//{
				if ($size < (50024 * 50024)) {
					$actual_image_name = time() . substr(str_replace(" ", "_", $txt), 5) . "." . $ext;
					$config['upload_path'] = './assets/uploads/';
					$config['allowed_types'] = 'gif|jpg|png|jpeg|docx|doc|pdf';
					$config['max_size']	= '50024';
					$config['file_name'] = $actual_image_name;

					$this->load->library('upload', $config);

					if ($this->upload->do_upload('userfilegroup')) {

						$config['image_library'] = 'gd2';
						$config['source_image'] = './assets/uploads/' . $actual_image_name;
						$config['maintain_ratio'] = TRUE;
						$config['width']    = 300;
						$config['height']   = 300;

						$this->load->library('image_lib', $config);

						if (!$this->image_lib->resize()) {
							//echo $this->image_lib->display_errors();
						}


						echo "<input type='hidden' id='ajax_image_url' value='" . $actual_image_name . "'>";
						//echo "<img src='".base_url()."/assets/uploads/".$actual_image_name."'  class='ava_discus' width='150'>";
						echo "<a href='" . base_url() . "/assets/uploads/" . $actual_image_name . "'  class='ava_discus'>" . $actual_image_name . "</a>";
					} else
						echo "<span style:'color:red'>Please try again.</span>";
				} else
					echo "<span style:'color:red'>Sorry, maximum file size should be 10MB</span>";

				//}
				//else
				//echo "<span style:'color:red'>Invalid format, try again</span>";
			} else
				echo "<span style:'color:red'>Please select an image.</span>";
		} else
			echo "<span style:'color:red'>Please select an image.</span>";
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
			if ($size < (50024 * 50024)) {
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
				$data = array("status" => "error", "message" => "Sorry, maximum file size should be 10MB.");
			}
		} else {
			$data = array("status" => "error", "message" => "Error uploading document. Please try again.");
		}
		return $data;
	}

	function get_file()
	{
		if ($this->session->userdata('admin_id') == '') {
			redirect('auth/login');
		}
		$this->load->model('main_mod');
		//ORGANIZATIONS
		$id = $this->input->get('id') <> null ? $this->input->get('id') : "";
		$type = $this->input->get('type') <> null ? $this->input->get('type') : "";
		$user_id = $this->input->get('user_id') <> null ? $this->input->get('user_id') : "";
		$user_org = $this->main_mod->msrwhere($type, array('user_id' => $user_id, 'id' => $id, 'status' => 1), 'id', 'asc')->result();

		if (isset($user_org[0]->attachment)) {
			$this->load->helper('download');
			force_download($user_org[0]->attachment, file_get_contents(base_url() . "assets/uploads/" . $user_org[0]->attachment));
		}
	}

	function penyebut($nilai)
	{
		$nilai = abs($nilai);
		$huruf = array("", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
		$temp = "";
		if ($nilai < 12) {
			$temp = " " . $huruf[$nilai];
		} else if ($nilai < 20) {
			$temp = $this->penyebut($nilai - 10) . " belas";
		} else if ($nilai < 100) {
			$temp = $this->penyebut($nilai / 10) . " puluh" . $this->penyebut($nilai % 10);
		} else if ($nilai < 200) {
			$temp = " seratus" . $this->penyebut($nilai - 100);
		} else if ($nilai < 1000) {
			$temp = $this->penyebut($nilai / 100) . " ratus" . $this->penyebut($nilai % 100);
		} else if ($nilai < 2000) {
			$temp = " seribu" . $this->penyebut($nilai - 1000);
		} else if ($nilai < 1000000) {
			$temp = $this->penyebut($nilai / 1000) . " ribu" . $this->penyebut($nilai % 1000);
		} else if ($nilai < 1000000000) {
			$temp = $this->penyebut($nilai / 1000000) . " juta" . $this->penyebut($nilai % 1000000);
		} else if ($nilai < 1000000000000) {
			$temp = $this->penyebut($nilai / 1000000000) . " milyar" . $this->penyebut(fmod($nilai, 1000000000));
		} else if ($nilai < 1000000000000000) {
			$temp = $this->penyebut($nilai / 1000000000000) . " trilyun" . $this->penyebut(fmod($nilai, 1000000000000));
		}
		return $temp;
	}

	function terbilang($nilai)
	{
		if ($nilai < 0) {
			$hasil = "minus " . trim($this->penyebut($nilai));
		} else {
			$hasil = trim($this->penyebut($nilai));
		}
		return ucwords($hasil);
	}

	function download_invoice()
	{
		$akses = array("0", "11");
		if (!in_array($this->session->userdata('type'), $akses)) {
			redirect('admin/dashboard');
			exit;
		}
		$this->load->model('main_mod');
		$id = $this->input->get('id') <> null ? $this->input->get('id') : "";
		$tra = $this->main_mod->msrwhere('user_transfer', array('id' => $id), 'id', 'asc')->row();
		if (isset($tra->user_id)) {
			$user_p = $this->main_mod->msrwhere('user_profiles', array('id' => $tra->user_id), 'id', 'asc')->row();
			//print_r($user_p);
			$this->load->library('Pdf');
			$your_width = 210;
			$your_height = 297;
			$custom_layout = array($your_width, $your_height);

			$pdf = new Pdf('P', 'mm', $custom_layout, true, 'UTF-8', false);

			$pdf->SetProtection(array('copy', 'modify', 'annot-forms', 'fill-forms', 'extract', 'assemble', 'owner'), "", "masterpassword123", 0, null);

			$pdf->setPrintHeader(false);
			$pdf->setPrintFooter(false);

			// set margins
			$pdf->SetMargins(20, 20, 20, true);
			$pdf->SetAutoPageBreak(false, 0);

			$pdf->AddPage('P', $custom_layout);

			$img_file = FCPATH . './assets/images/headerpdf.png';
			$pdf->Image($img_file, 0, 0, 210, 297, '', '', '', false, 300, '', false, false, 0);

			$va = $user_p->va;
			$rp = number_format($tra->sukarelatotal);
			$date = date("d F Y", strtotime($tra->modifieddate));
			$terbilang = $this->terbilang($tra->sukarelatotal);
			$pay_for = 'Iuran Keanggotaan PII';
			if ($tra->pay_type == '5') $pay_for = 'STRI PII';
			else if ($tra->pay_type == '3') $pay_for = 'FAIP ASSESSMENT FEE PII';
			else if ($tra->pay_type == '4') $pay_for = 'FAIP SIP FEE PII';
			$nama = $user_p->firstname . ' ' . $user_p->lastname;

			//$photo = '<img class="img-fluid" style="text-align:right;padding:200;" height="300" width="250" src="'.base_url().'assets/images/headerpdf.png'.'" title="">';
			$html = <<<EOD
<br /><br /><br /><br /><br />
<h2>Informasi Pembayaran</h2>
NO: VA $va
<p style="float:right;text-align:right;">Jakarta, $date</p>
<p>
Kepada Yth,<br />
PS PPI – UNIVERSITAS GADJAH MADA<br />
Di <br />
Tempat
</p>
<br />
<br />

Dengan Hormat,<br /><br />

Bersama ini kami sampaikan Informasi Pembayaran <b>$pay_for</b> peserta PSPPI – Universitas Gadjah Mada, atas nama : $nama sebesar : <b>Rp. $rp,-</b> (Terbilang : $terbilang ) <br /><br />

Dengan detail pembayaran :<br /><br />

Total Bayar 			: Rp. $rp<br />
Metode Pembayaran	: Mandiri Virtual Account<br />
Kode Virtual Account	: $va<br /><br />

Demikian kami sampaikan, atas perhatian dan kerjasamanya kami ucapkan terimakasih.<br /><br />
<p style="text-align:center;">Hormat,<br /><br />

Keanggotaan dan Registrasi PII</p>
EOD;

			$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

			$makeSpace = preg_replace("/[^a-zA-Z0-9\s]/", "_", $nama);

			$pdf->Output('Informasi_Pembayaran_' . $makeSpace . '.pdf', 'D');
		} else echo '<script>alert("tidak valid");</script>';
	}

	function download_invoice_faip()
	{
		$akses = array("0", "11");
		if (!in_array($this->session->userdata('type'), $akses)) {
			redirect('admin/dashboard');
			exit;
		}
		$this->load->model('main_mod');
		$id = $this->input->get('id') <> null ? $this->input->get('id') : "";
		$tra = $this->main_mod->msrwhere('user_transfer', array('id' => $id), 'id', 'asc')->row();
		if (isset($tra->user_id)) {
			$user_p = $this->main_mod->msrwhere('user_profiles', array('id' => $tra->user_id), 'id', 'asc')->row();
			//print_r($user_p);
			$this->load->library('Pdf');
			$your_width = 210;
			$your_height = 297;
			$custom_layout = array($your_width, $your_height);

			$pdf = new Pdf('P', 'mm', $custom_layout, true, 'UTF-8', false);

			$pdf->SetProtection(array('copy', 'modify', 'annot-forms', 'fill-forms', 'extract', 'assemble', 'owner'), "", "masterpassword123", 0, null);

			$pdf->setPrintHeader(false);
			$pdf->setPrintFooter(false);

			// set margins
			$pdf->SetMargins(20, 20, 20, true);
			$pdf->SetAutoPageBreak(false, 0);

			$pdf->AddPage('P', $custom_layout);

			$img_file = FCPATH . './assets/images/headerpdflski.PNG';
			$pdf->Image($img_file, 0, 0, 210, 297, '', '', '', false, 300, '', false, false, 0);

			$va = $user_p->va;

			$total = 123200000;

			$rp = number_format($total);
			$date = date("d F Y", strtotime(date('Y-m-d')));
			$terbilang = $this->terbilang($total);
			$pay_for = 'Iuran Keanggotaan PII';
			if ($tra->pay_type == '5') $pay_for = 'STRI PII';
			else if ($tra->pay_type == '3') $pay_for = 'FAIP ASSESSMENT FEE PII';
			else if ($tra->pay_type == '4') $pay_for = 'FAIP SIP FEE PII';
			$nama = $user_p->firstname . ' ' . $user_p->lastname;

			//$photo = '<img class="img-fluid" style="text-align:right;padding:200;" height="300" width="250" src="'.base_url().'assets/images/headerpdf.png'.'" title="">';
			$html = <<<EOD
<br /><br /><br /><br /><br />
<h2>Pemberitahuan Kewajiban Pembayaran FAIP</h2>

<p style="float:right;text-align:right;">Jakarta, $date</p>
<p>
Kepada Yth,<br />
PS PPI – UNIVERSITAS GADJAH MADA<br />
Di <br />
Tempat
</p>
<br />
<br />

Dengan Hormat,<br /><br />

Bersama ini kami sampaikan pemberitahuan kewajiban pembayaran <b>$pay_for</b> peserta PSPPI – Universitas Gadjah Mada sebesar : <b>Rp. $rp,-</b> (<i>$terbilang</i>).<br /><br />

Dengan detail pembayaran :<br /><br />

Total Bayar 			: Rp. $rp<br />
Metode Pembayaran	: Mandiri Virtual Account<br /><br />

Demikian kami sampaikan, atas perhatian dan kerjasamanya kami ucapkan terimakasih.<br /><br />
<p style="text-align:left;">Hormat,<br /><br /><br /><br />

Ir. R. Bambang P. Soediro, ST, MT, MKN, IPU, AER, ACPE<br />
Sekretaris Lembaga</p>
EOD;

			$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

			$makeSpace = preg_replace("/[^a-zA-Z0-9\s]/", "_", $nama);

			$pdf->Output('Informasi_Pembayaran_Faip_' . date('Y-m-d') . '.pdf', 'D');
		} else echo '<script>alert("tidak valid");</script>';
	}


	//PKB



	public function pkb()
	{
		$akses = array("0", "1", "7", "10", "11", "16");
		if (!in_array($this->session->userdata('type'), $akses)) {
			redirect('admin/dashboard');
			exit;
		}

		$data['title'] = ''; //SITE_NAME.': Manage Members';
		$data['msg'] = '';

		//Pagination starts
		$total_rows = $this->members_model->record_count_pkb('user_pkb');
		$config = pagination_configuration(base_url("admin/members/pkb"), $total_rows, 10, 4, 5, true);

		$this->pagination->initialize($config);
		$page = ($this->uri->segment(3)) ? $this->uri->segment(4) : 0;
		$page_num = $page - 1;
		$page_num = ($page_num < 0) ? '0' : $page_num;
		$page = $page_num * $config["per_page"];
		$data["links"] = $this->pagination->create_links();
		//Pagination ends
		//print_r($data["links"]);

		$obj_result = $this->members_model->get_all_pkb($config["per_page"], $page);
		$data['result'] = $obj_result;
		$data["total_rows"] = $total_rows;

		//$data["m_cab"] = $this->members_model->get_all_cabang();
		//$data["m_bk"] = $this->members_model->get_all_bk();

		$data["m_cab"] = $this->members_model->get_all_cabang_wilayah();
		$data["m_bk"] = $this->members_model->get_all_bk();
		$data["m_majelis"] = $this->members_model->get_all_majelis_bk();
		$data["m_user_majelis"] = $this->members_model->get_all_user_bk();

		$this->load->model('main_mod');
		$data['m_pkb_status'] = $this->main_mod->msrwhere('m_pkb_status', null, 'id', 'asc')->result();

		//print_r($data["m_user_majelis"]);
		$this->load->view('admin/pkb_view', $data);
		//return;

	}

	public function search_pkb()
	{
		$akses = array("0", "1", "7", "10", "11", "16");
		if (!in_array($this->session->userdata('type'), $akses)) {
			redirect('admin/dashboard');
			exit;
		}

		$data['title'] = ''; //SITE_NAME.': Manage Employers';
		$data['msg'] = '';
		$search_name = '';
		$search_kta = '';

		$this->form_validation->set_rules('firstname', 'firstname', 'trim');
		$this->form_validation->set_rules('kta', 'kta', 'trim');
		$this->form_validation->set_rules('filter_status', 'status', 'trim');
		$this->form_validation->set_rules('filter_cab', 'wilayah/cabang', 'trim');
		$this->form_validation->set_rules('filter_bk', 'BK', 'trim');
		$this->form_validation->run();

		if ($_GET) {
			$search_name 	= 	$this->input->get('firstname');
			$search_kta 	= 	$this->input->get('kta');
			$search_status 	= 	$this->input->get('filter_status');
			$search_cab 	= 	$this->input->get('filter_cab');
			$search_bk 		= 	$this->input->get('filter_bk');
		}
		if ($search_name == '' && $search_kta == '' && $search_status == '' && $search_cab == '' && $search_bk == '') {
			redirect(base_url('admin/members/pkb'));
			return;
		}
		$new_array = array();
		$search_data = array();
		$search_data2 = array();
		if (isset($search_name) && $search_name != '') {
			$search_data['firstname'] = $search_name;
			//$search_data2['lower(add_name)']=strtolower($search_name);
			$search_data2['REPLACE(lower(nama)," ","")'] = str_replace(' ', '', strtolower($search_name));
		}
		if (isset($search_kta) && $search_kta != '') {
			$search_data['kta'] = $search_kta;
			$search_data2['user_pkb.no_kta'] = ltrim($search_kta, '0');
		}

		if (isset($search_status) && $search_status != '') {
			$search_data['filter_status'] = $search_status;
			$search_data2['user_pkb.status_pkb'] = ltrim($search_status, '0');
		}

		if (isset($search_cab) && $search_cab != '') {
			$search_data['filter_cab'] = $search_cab;
			$search_data2['filter_cab'] = $search_cab;
		}

		if (isset($search_bk) && $search_bk != '') {
			$search_data['filter_bk'] = $search_bk;
			$search_data2['filter_bk'] = $search_bk;
		}

		$wild_card = '';

		$url_params = implode(
			'&amp;',
			array_map(
				function ($key, $val) {
					return urlencode($key) . '=' . urlencode($val);
				},
				array_keys($search_data),
				$search_data
			)
		);
		//Pagination starts
		$total_rows = $this->members_model->search_record_count_pkb('user_pkb', $search_data2);
		$config = pagination_configuration_search(base_url("admin/members/search_pkb/?" . $url_params), $total_rows, 10, 3, 5, true);

		$this->pagination->initialize($config);
		$page = $this->input->get('per_page');
		$page_num = $page - 1;
		$page_num = ($page_num < 0) ? '0' : $page_num;
		$page = $page_num * $config["per_page"];
		$data["links"] = $this->pagination->create_links();
		$data["total_rows"] = $total_rows;
		//Pagination ends
		$obj_result = $this->members_model->search_all_pkb($config["per_page"], $page, $search_data2, $wild_card);
		$data['result'] = $obj_result;
		$data['search_data'] = $search_data;

		$data["m_cab"] = $this->members_model->get_all_cabang_wilayah();
		$data["m_bk"] = $this->members_model->get_all_bk();
		$data["m_majelis"] = $this->members_model->get_all_majelis_bk();
		$data["m_user_majelis"] = $this->members_model->get_all_user_bk();

		$this->load->model('main_mod');
		$data['m_pkb_status'] = $this->main_mod->msrwhere('m_pkb_status', null, 'id', 'asc')->result();

		$this->load->view('admin/pkb_view', $data);
		return;
	}

	function setpkbstatus()
	{
		$akses = array("0", "1", "11", "16");
		if (!in_array($this->session->userdata('type'), $akses)) {
			redirect('admin/dashboard');
			exit;
		}

		$idmember = $this->input->post('id') <> null ? $this->input->post('id') : "";
		$status = $this->input->post('status') <> null ? $this->input->post('status') : "";
		$remarks = $this->input->post('remarks') <> null ? $this->input->post('remarks') : "";
		$jenis_pkb = $this->input->post('jenis_pkb') <> null ? $this->input->post('jenis_pkb') : "";
		$score = $this->input->post('score') <> null ? $this->input->post('score') : "";
		$keputusan = $this->input->post('keputusan') <> null ? $this->input->post('keputusan') : "";


		$interview_date = $this->input->post('tgl') <> null ? $this->input->post('tgl') : "";
		$interview_start_hour = $this->input->post('jam_awal') <> null ? $this->input->post('jam_awal') : "";
		$interview_end_hour = $this->input->post('jam_akhir') <> null ? $this->input->post('jam_akhir') : "";
		$interview_loc = $this->input->post('lokasi') <> null ? $this->input->post('lokasi') : "";


		if ($idmember == '') {
			redirect('admin/members/pkb');
			exit;
		}
		$this->load->model('main_mod');
		if ($idmember != '') {
			try {

				$check = $this->main_mod->msrwhere('user_pkb', array('id' => $idmember), 'id', 'desc')->result();
				if (isset($check[0])) {

					$rowInsert = array(
						'pkb_id' => $idmember,
						'old_status' => $check[0]->status_pkb,
						'new_status' => $status,
						'notes' => 'lski',
						'remarks' => $remarks,
						'createdby' => $this->session->userdata('admin_id'),
					);
					$this->main_mod->insert('log_status_pkb', $rowInsert);



					$where = array(
						"id" => $idmember
					);
					$row = array(
						'status_pkb' => $status,
						'remarks' => $remarks,
						'modifieddate' => date('Y-m-d H:i:s'),
						'modifiedby' => $this->session->userdata('admin_id'),
					);

					if ($status == "8") {
						$row['interview_date'] = date('Y-m-d', strtotime($interview_date));
						$row['interview_start_hour'] = $interview_start_hour;
						$row['interview_end_hour'] = $interview_end_hour;
						$row['interview_loc'] = $interview_loc;
					}

					$update = $this->main_mod->update('user_pkb', $where, $row);

					// NOTIF
					/*$to_email = array();
					$cc_email = array();
					foreach($bk_user as $val){
						$to_email [] = $val->admin_username;

					}
					$cc_email[] =$this->session->userdata('name');
					$subject = "FAIP ".$nama."(".$kta.") pembayaran sudah valid dan siap diuji";

					$data = (array)$check[0];
					$data["m_bk"] = $this->members_model->get_all_bk();
					$data['site_name'] = $this->config->item('website_name', 'tank_auth');
					$this->_send_email('pkb_pre_score',$subject, $to_email, $cc_email, $data);*/
					// NOTIF

					if ($status == "6") {
						$bk = $check[0]->bidang;
						$nama = $check[0]->nama;
						$kta = str_pad($check[0]->no_kta, 6, '0', STR_PAD_LEFT);
						$bk_user = $this->main_mod->msrwhere('admin', array('type' => 11, 'code_bk_hkk' => str_pad($bk, 2, '0', STR_PAD_LEFT)), 'id', 'desc')->result();
						//print_r($bk);
						if (is_array($bk_user)) {
							$to_email = array();
							$cc_email = array();
							foreach ($bk_user as $val) {
								//print_r($val);
								$to_email[] = $val->admin_username;
							}
							$cc_email[] = $this->session->userdata('name');
							$subject = "PKB " . $nama . "(" . $kta . ") pembayaran sudah valid dan siap diuji";

							$data = (array)$check[0];
							$sip = $this->main_mod->msrwhere('user_cert', array('status' => 2, 'id' => $check[0]->sip_id), 'id', 'desc')->row();
							$data['lic_num'] = $sip->lic_num;
							$data['cert_title'] = $sip->cert_title;
							$data["m_bk"] = $this->members_model->get_all_bk();
							$data['site_name'] = $this->config->item('website_name', 'tank_auth');
							$this->_send_email('pkb_pre_score', $subject, $to_email, $cc_email, $data);
						}
					} else if ($status == "9") {
						//UPDATE MASTER
						try {
							$row = array(
								'pkb_id' => $idmember,
								//'wajib1_score' => $this->input->post('hwb1')<>null?$this->input->post('hwb1'):"",
								//'wajib2_score' => $this->input->post('hwb2')<>null?$this->input->post('hwb2'):"",
								//'wajib3_score' => $this->input->post('hwb3')<>null?$this->input->post('hwb3'):"",
								//'wajib4_score' => $this->input->post('hwb4')<>null?$this->input->post('hwb4'):"",
								//'pilihan_score' => $this->input->post('hpil')<>null?$this->input->post('hpil'):"",
								'total_score' => $score,
								'keputusan' => $keputusan,
								'tgl_keputusan' => date("Y-m-d"),
								'createdby' => $this->session->userdata('admin_id'),
								'modifiedby' => $this->session->userdata('admin_id'),
								'modifieddate' => date("Y-m-d H:i:s"),
								'status' => "2"
							);
							$where = array(
								'pkb_id' => $idmember,
								'createdby' => $this->session->userdata('admin_id')
							);

							$check = $this->main_mod->msrwhere('asesor_pkb', $where, 'id', 'desc')->result();
							if (isset($check[0])) {
								$update = $this->main_mod->update('asesor_pkb', $where, $row);
								$asesor_pkb_id = $check[0]->id;
							} else {
								$where2 = array(
									'pkb_id' => $idmember,
									'status' => 2,
									'createdby <> ' . $this->session->userdata('admin_id')  => null
								);
								$check2 = $this->main_mod->msrwhere('asesor_pkb', $where2, 'id', 'desc')->result();

								$wajib1_score = 0;
								$wajib2_score = 0;
								$wajib3_score = 0;
								$wajib4_score = 0;
								$wajib5_score = 0;
								$count = 0;
								foreach ($check2 as $val2) {
									$wajib1_score = $wajib1_score + $val2->wajib1_score;
									$wajib2_score = $wajib2_score + $val2->wajib2_score;
									$wajib3_score = $wajib3_score + $val2->wajib3_score;
									$wajib4_score = $wajib4_score + $val2->wajib4_score;
									$wajib5_score = $wajib5_score + $val2->wajib5_score;
									$count++;
								}

								$row['wajib1_score'] = $wajib1_score / $count;
								$row['wajib2_score'] = $wajib2_score / $count;
								$row['wajib3_score'] = $wajib3_score / $count;
								$row['wajib4_score'] = $wajib4_score / $count;
								$row['wajib5_score'] = $wajib5_score / $count;

								$asesor_pkb_id = $this->main_mod->insert('asesor_pkb', $row);
							}

							$id_cert = 0;
							/*if($keputusan=="Dapat ditingkatkan IPU") $id_cert = 3;
							else if($keputusan=="Dapat ditingkatkan IPM") $id_cert = 2;
							else if($keputusan=="Dapat diperpanjang IPU") $id_cert = 3;
							else if($keputusan=="Dapat diperpanjang IPM") $id_cert = 2;
							else if($keputusan=="Dapat diperpanjang IPP") $id_cert = 1;*/
							//else if($keputusan=="Belum dapat diperpanjang IPU. Dapat diperpanjang IPM") $id_cert = 2;
							//else if($keputusan=="Belum dapat diperpanjang IPU. Dapat diperpanjang IPP") $id_cert = 1;
							//else if($keputusan=="Belum dapat diperpanjang IPM. Dapat diperpanjang IPP") $id_cert = 1;

							if ($keputusan == "Dapat diperpanjang IPU") $id_cert = 3;
							else if ($keputusan == "Dapat diperpanjang IPM") $id_cert = 2;
							else if ($keputusan == "Dapat diperpanjang IPP") $id_cert = 1;

							if ($id_cert == 0) {
								if ($keputusan == "Dapat ditingkatkan IPU") $id_cert = 3;
								else if ($keputusan == "Dapat ditingkatkan IPM") $id_cert = 2;

								$where = array(
									"id" => $idmember
								);
								$row = array(
									'status_pkb' => 13,
									'hasil_keputusan' => $keputusan,
									'hasil_tipe' => $id_cert,
									'modifieddate' => date('Y-m-d H:i:s'),
									'modifiedby' => $this->session->userdata('admin_id'),
								);
								$update = $this->main_mod->update('user_pkb', $where, $row);
							} else {
								//OTOMATIS PENGAJUAN VA
								$sukarelatotal = 0;

								//if($keputusan=="Dapat ditingkatkan IPU") $sukarelatotal = 1200000;
								//else if($keputusan=="Dapat ditingkatkan IPM") $sukarelatotal = 650000;
								//else
								if ($keputusan == "Dapat diperpanjang IPU") $sukarelatotal = 1200000;
								else if ($keputusan == "Dapat diperpanjang IPM") $sukarelatotal = 650000;
								else if ($keputusan == "Dapat diperpanjang IPP") $sukarelatotal = 100000;
								//else if($keputusan=="Belum dapat diperpanjang IPU. Dapat diperpanjang IPM") $sukarelatotal = 650000;
								//else if($keputusan=="Belum dapat diperpanjang IPU. Dapat diperpanjang IPP") $sukarelatotal = 100000;
								//else if($keputusan=="Belum dapat diperpanjang IPM. Dapat diperpanjang IPP") $sukarelatotal = 100000;

								//CHECK TOTAL BEFORE
								$total_pkb = 0;
								$check_pk = $this->main_mod->msrwhere('user_pkb', array('id' => $idmember), 'id', 'desc')->row();
								$check_pkb = $this->main_mod->msrwhere('user_pkb', array('sip_id' => $check_pk->sip_id), 'id', 'desc')->result();
								//print_r($check_pkb);
								foreach ($check_pkb as $val) {
									$check_sippkb = $this->main_mod->msrwhere('user_transfer', array('pay_type' => 6, 'rel_id' => $val->id, 'status' => 1), 'id', 'desc')->row();
									if (isset($check_sippkb->sukarelatotal))
										$total_pkb = $total_pkb + $check_sippkb->sukarelatotal;
								}

								$tmpz = 1000000 - $total_pkb;
								$sukarelatotal = $sukarelatotal + $tmpz;
								//$sukarelatotal = $total_pkb;

								$pay_type = 7;
								$status_pkb = 10;
								$check = $this->main_mod->msrwhere('user_pkb', array('id' => $idmember), 'id', 'desc')->result();
								$row = array(
									'user_id' => $check[0]->user_id,
									'pay_type' => $pay_type,
									'rel_id' => $idmember,
									'tgl' => date('Y-m-d'),
									'vnv_status' => 1,
									//'is_upload_mandiri'=>1,

									'sukarelatotal' => $sukarelatotal,
									'createdby' => $this->session->userdata('admin_id'),

									'modifiedby' => $this->session->userdata('admin_id'),
									'modifieddate' => date('Y-m-d H:i:s'),

								);
								$insert = $this->main_mod->insert('user_transfer', $row);
								$rowInsert = array(
									'pkb_id' => $idmember,
									'old_status' => $check[0]->status_pkb,
									'new_status' => $status_pkb,
									'notes' => 'anggota',
									'createdby' => $this->session->userdata('admin_id'),
								);
								$this->main_mod->insert('log_status_pkb', $rowInsert);
								$where = array(
									"id" => $idmember
								);
								$row = array(
									'status_pkb' => $status_pkb,
									'upgrade' => 'Perpanjang',
									//'remarks' => $remarks,
									'modifieddate' => date('Y-m-d H:i:s'),
									'modifiedby' => $this->session->userdata('admin_id'),
								);
								$update = $this->main_mod->update('user_pkb', $where, $row);
							}
						} catch (Exception $e) {
							print_r($e);
							return false;
						}
					} else if ($status == "2") {
						$check = $this->main_mod->msrwhere('user_pkb', array('id' => $idmember), 'id', 'desc')->result();
						$check2 = $this->main_mod->msrwhere('user_pkb', array('user_id' => $check[0]->user_id, 'sip_id' => $check[0]->sip_id, 'id<>' . $check[0]->id => null, 'status_pkb<>0' => null), 'id', 'desc')->result();

						$period = $check[0]->periodend - $check[0]->periodstart;

						if (isset($check2[0]->user_id)) {
							$period = $check[0]->periodend - $check2[0]->periodend;
						}
						//print_r($period);
						//return;

						//OTOMATIS PENGAJUAN VA
						$pay_type = 6;
						$sukarelatotal = $period * 200000;
						//if($check[0]->certificate_type=='IPM') $sukarelatotal = 1650000;
						//else if($check[0]->certificate_type=='IPU') $sukarelatotal = 2200000;
						$status_pkb = 4;

						$row = array(
							'user_id' => $check[0]->user_id,
							'pay_type' => $pay_type,
							'rel_id' => $idmember,
							//'atasnama' => $atasnama,
							'tgl' => date('Y-m-d'),
							//'bukti' => $bukti,
							//'description' => $desc,

							'vnv_status' => 1,
							//'is_upload_mandiri'=>1,

							'sukarelatotal' => $sukarelatotal,
							'createdby' => $this->session->userdata('admin_id'),

							'modifiedby' => $this->session->userdata('admin_id'),
							'modifieddate' => date('Y-m-d H:i:s'),

						);
						$insert = $this->main_mod->insert('user_transfer', $row);
						$rowInsert = array(
							'pkb_id' => $idmember,
							'old_status' => $check[0]->status_pkb,
							'new_status' => $status_pkb,
							'notes' => 'anggota',
							'createdby' => $this->session->userdata('admin_id'),
						);
						$this->main_mod->insert('log_status_pkb', $rowInsert);
						$where = array(
							"id" => $idmember
						);
						$row = array(
							'status_pkb' => $status_pkb,
							'jenis_pkb' => $jenis_pkb,
							//'remarks' => $remarks,
							'modifieddate' => date('Y-m-d H:i:s'),
							'modifiedby' => $this->session->userdata('admin_id'),
						);
						$update = $this->main_mod->update('user_pkb', $where, $row);
					} else if ($status == "8") {
						$users = $this->main_mod->msrwhere('users', array('id' => $check[0]->user_id), 'id', 'desc')->result();
						$bk = $check[0]->bidang;
						$nama = $check[0]->nama;
						$kta = str_pad($check[0]->no_kta, 6, '0', STR_PAD_LEFT);
						$bk_user = $this->main_mod->msrwhere('admin', array('type' => 11, 'code_bk_hkk' => str_pad($bk, 2, '0', STR_PAD_LEFT)), 'id', 'desc')->result();
						//print_r($bk);
						$to_email = array();
						$cc_email = array();

						$to_email[] = $users[0]->email;

						if (is_array($bk_user)) {
							foreach ($bk_user as $val) {
								//print_r($val);
								$cc_email[] = $val->admin_username;
							}
						}
						$cc_email[] = $this->session->userdata('name');
						$subject = "Wawancara PKB";

						$data = (array)$check[0];
						$data["m_bk"] = $this->members_model->get_all_bk();
						$data['site_name'] = $this->config->item('website_name', 'tank_auth');

						$data['interview_date'] = date('Y-m-d', strtotime($interview_date));
						$data['interview_start_hour'] = $interview_start_hour;
						$data['interview_end_hour'] = $interview_end_hour;
						$data['interview_loc'] = $interview_loc;
						$data['interview_note'] = $remarks;

						if ($interview_date != '')
							$this->_send_email('pkb_wawancara', $subject, $to_email, $cc_email, $data);
					}
				}

				echo "valid";
			} catch (Exception $e) {
				//print_r($e);
				echo "not valid";
			}
		} else
			echo "not valid";
	}

	function setpkbrevisi()
	{
		$akses = array("0", "16", "11");
		if (!in_array($this->session->userdata('type'), $akses)) {
			redirect('admin/dashboard');
			exit;
		}

		$idmember = $this->input->post('id') <> null ? $this->input->post('id') : "";
		$remarks = $this->input->post('remarks') <> null ? $this->input->post('remarks') : "";

		if ($idmember == '') {
			redirect('admin/members/pkb');
			exit;
		}
		$this->load->model('main_mod');
		if ($idmember != '') {
			try {

				$check = $this->main_mod->msrwhere('user_pkb', array('id' => $idmember), 'id', 'desc')->result();
				if (isset($check[0])) {

					$rowInsert = array(
						'pkb_id' => $idmember,
						'old_status' => $check[0]->status_pkb,
						'new_status' => $check[0]->status_pkb,
						'notes' => 'lski',
						'remarks' => $remarks,
						'createdby' => $this->session->userdata('admin_id'),
					);
					$this->main_mod->insert('log_status_pkb', $rowInsert);



					$where = array(
						"id" => $idmember
					);
					$row = array(
						'need_revisi' => 1,
						'revisi_note' => $remarks,
						'modifieddate' => date('Y-m-d H:i:s'),
						'modifiedby' => $this->session->userdata('admin_id'),
					);
					$update = $this->main_mod->update('user_pkb', $where, $row);

					// NOTIF
					/*$to_email = array();
					$cc_email = array();
					foreach($bk_user as $val){
						$to_email [] = $val->admin_username;

					}
					$cc_email[] =$this->session->userdata('name');
					$subject = "FAIP ".$nama."(".$kta.") pembayaran sudah valid dan siap diuji";

					$data = (array)$check[0];
					$data["m_bk"] = $this->members_model->get_all_bk();
					$data['site_name'] = $this->config->item('website_name', 'tank_auth');
					$this->_send_email('pkb_pre_score',$subject, $to_email, $cc_email, $data);*/
					// NOTIF

				}

				echo "valid";
			} catch (Exception $e) {
				//print_r($e);
				echo "not valid";
			}
		} else
			echo "not valid";
	}

	public function ajax_show_status_pkb()
	{
		$status_pkb = $this->input->get('status') <> null ? $this->input->get('status') : "";
		$this->load->model('main_mod');
		$category = $this->main_mod->msrwhere('m_pkb_status', null, 'id', 'asc')->result();
		//$data = "<option value=''>-- Choose--</option>";
		$data = "";
		foreach ($category as $val) {
			if ($status_pkb == "1" && ($val->value == "2" || $val->value == "3"))
				$data .= "<option value='" . $val->value . "'>" . $val->name . "</option>";
			if ($status_pkb == "2" && ($val->value == "2" || $val->value == "3"))
				$data .= "<option value='" . $val->value . "'>" . $val->name . "</option>";
			if ($status_pkb == "5" && $val->value == "6")
				$data .= "<option value='" . $val->value . "'>" . $val->name . "</option>";
			if (($status_pkb > "5" && $status_pkb <= "9") && ($val->value > "6" && $val->value <= "9"))
				$data .= "<option value='" . $val->value . "'>" . $val->name . "</option>";
			if (($status_pkb == "12") && ($val->value == "13"))
				$data .= "<option value='" . $val->value . "'>" . $val->name . "</option>";
		}
		echo $data;
	}

	public function ajax_show_status_pkb_2()
	{
		$status_pkb = $this->input->get('status') <> null ? $this->input->get('status') : "";
		$this->load->model('main_mod');
		$category = $this->main_mod->msrwhere('m_pkb_status', null, 'id', 'asc')->result();
		//$data = "<option value=''>-- Choose--</option>";
		$data = "";
		foreach ($category as $val) {
			if ($status_pkb == "6" && $val->value == "3")
				$data .= "<option value='" . $val->value . "'>" . $val->name . "</option>";
		}
		echo $data;
	}

	function savesetmajelispkb()
	{
		$akses = array("0", "11");
		if (!in_array($this->session->userdata('type'), $akses)) {
			redirect('admin/dashboard');
			exit;
		}

		$id_pkb = $this->input->post('id_pkb') <> null ? $this->input->post('id_pkb') : "";
		$majelis = $this->input->post('majelis') <> null ? $this->input->post('majelis') : "";
		$tipe_pkb = $this->input->post('tipe_pkb') <> null ? $this->input->post('tipe_pkb') : "";

		if ($id_pkb == '') {
			redirect('admin/members/pkb');
			exit;
		}
		$this->load->model('main_mod');
		if ($id_pkb != '') {
			try {

				$check = $this->main_mod->msrwhere('user_pkb', array('id' => $id_pkb), 'id', 'desc')->result();
				if (isset($check[0])) {
					$flag = true;
					if ($check[0]->user_id == $majelis)
						$flag = false;
					if ($check[0]->majelis1 == $majelis)
						$flag = false;
					if ($check[0]->majelis2 == $majelis)
						$flag = false;
					if ($check[0]->majelis3 == $majelis)
						$flag = false;

					if ($flag) {
						$where = array(
							"id" => $id_pkb
						);
						$row = array(
							'majelis' . $tipe_pkb => $majelis,
							'modifieddate' => date('Y-m-d H:i:s'),
							'modifiedby' => $this->session->userdata('admin_id'),
						);
						$update = $this->main_mod->update('user_pkb', $where, $row);



						$check = $this->main_mod->msrwhere('user_pkb', array('id' => $id_pkb), 'id', 'desc')->result();
						if (isset($check[0])) {
							$bk = $check[0]->bidang;
							$nama = $check[0]->nama;
							$kta = str_pad($check[0]->no_kta, 6, '0', STR_PAD_LEFT);
							$bk_user = $this->main_mod->msrwhere('users', array('id' => $majelis), 'id', 'desc')->result();

							$to_email = array();
							$cc_email = array();
							foreach ($bk_user as $val) {
								//print_r($val);
								$to_email[] = $val->email;
							}
							//$cc_email[] =$this->session->userdata('name');
							$subject = "PKB " . $nama . "(" . $kta . ") pembayaran sudah valid dan siap diuji";

							$data = (array)$check[0];
							$sip = $this->main_mod->msrwhere('user_cert', array('status' => 2, 'id' => $check[0]->sip_id), 'id', 'desc')->row();
							$data['lic_num'] = $sip->lic_num;
							$data['cert_title'] = $sip->cert_title;
							$data["m_bk"] = $this->members_model->get_all_bk();
							$data['site_name'] = $this->config->item('website_name', 'tank_auth');
							$this->_send_email('pkb_pre_score', $subject, $to_email, $cc_email, $data);
						}
						echo "valid";
					} else echo "not valid";
				} else echo "not valid";
			} catch (Exception $e) {
				//print_r($e);
				echo "not valid";
			}
		} else
			echo "not valid";
	}

	public function pkbview()
	{
		$akses = array("0", "1", "7", "10", "11");
		if (!in_array($this->session->userdata('type'), $akses)) {
			redirect('admin/dashboard');
			exit;
		}

		$data['title'] = ''; //SITE_NAME.': Manage Members';
		$data['msg'] = '';


		$id_pkb = $this->uri->segment(4);
		$pkb = $this->pkb_model->get_pkb_by_id($id_pkb);
		$id = isset($pkb->user_id) ? $pkb->user_id : "";

		if ($id != '') {

			$this->load->model('main_mod');
			$obj_row = $this->members_model->get_member_by_id($id);
			$data['id_pkb'] = $id_pkb;
			$data['row'] = $obj_row;
			$data['kta'] = $this->members_model->get_kta_data_by_personid($id);
			//print_r($obj_row);
			$data['emailx'] = $obj_row->email;
			//$data['m_komp']=$this->main_mod->msrwhere('no_kompetensi',array('status'=>'1'),'id','asc')->result();

			$data['m_bk'] = $this->main_mod->msrwhere('m_bk', null, 'id', 'asc')->result();
			$pkb = $this->pkb_model->get_pkb_by_id($id_pkb);
			$data['sip'] = $this->main_mod->msrwhere('user_cert', array('id' => $pkb->sip_id), 'id', 'desc')->row();
			$data['stri'] = $this->main_mod->msrwhere('members_certificate', array('person_id' => $this->session->userdata('user_id'), "TRIM(LEADING '0' FROM skip_id)=" => (isset($data['sip']->ip_seq) ? ltrim($data['sip']->ip_seq, "0") : '')), 'id', 'desc')->row();
			/*$data['m_komp']=$this->main_mod->msrquery('select value,title from no_kompetensi a join m_kompetensi b on a.value=b.code')->result();
			$data['m_act_13']=$this->main_mod->msrquery('select code as value,title from m_kompetensi where code like "W.1%" order by id asc')->result();
			$data['m_act_15']=$this->main_mod->msrquery('select code as value,title from m_kompetensi where code like "W.2%" or code like "W.4%" or code like "P.10%" order by id asc')->result();
			$data['m_act_16']=$this->main_mod->msrquery('select code as value,title from m_kompetensi where code like "W.1%" or code like "W.4%" or code like "P.10%" order by id asc')->result();

			$data['m_act_3']=$this->main_mod->msrquery('select code as value,title from m_kompetensi where
			code like "W.2%" or
			code like "W.3%" or
			code like "W.4%" or
			code like "P.6%" or
			code like "P.7%" or
			code like "P.8%" or
			code like "P.9%" or
			code like "P.10%" or
			code like "P.11%"
			order by id asc')->result();
			$data['m_act_4']=$this->main_mod->msrquery('select code as value,title from m_kompetensi where code like "W.2%" or
			code like "W.3%" or
			code like "W.4%" or code like "P.5%" order by id asc')->result();
			$data['m_act_51']=$this->main_mod->msrquery('select code as value,title from m_kompetensi where code like "W.4%" order by id asc')->result();
			$data['m_act_53']=$this->main_mod->msrquery('select code as value,title from m_kompetensi where code like "W.2%" order by id asc')->result();
			$data['m_act_54']=$this->main_mod->msrquery('select code as value,title from m_kompetensi where code like "P.6%" order by id asc')->result();
			*/



			$data['m_degree'] = $this->main_mod->msrwhere('education_type', null, 'SEQ', 'asc')->result();
			//$data['user_address']=$this->main_mod->msrwhere('user_address',array('user_id'=>$id,'status'=>1),'id','asc')->result();
			$data['user_email'] = $this->main_mod->msrwhere('contacts', array('user_id' => $id, 'contact_type like "%_email%"' => null, 'status' => 1), 'id', 'asc')->result();

			$data['user_pkb'] = $pkb;
			$data['user_pkb_11'] = $this->main_mod->msrwhere('user_pkb_11', array('pkb_id' => $id_pkb, 'status' => 1), 'id', 'asc')->result();
			$data['user_pkb_12'] = $this->main_mod->msrwhere('user_pkb_12', array('pkb_id' => $id_pkb, 'status' => 1), 'id', 'asc')->result();
			$data['user_pkb_111'] = $this->main_mod->msrwhere('user_pkb_111', array('pkb_id' => $id_pkb, 'status' => 1), 'id', 'asc')->result();
			$data['user_pkb_112'] = $this->main_mod->msrwhere('user_pkb_112', array('pkb_id' => $id_pkb, 'status' => 1), 'id', 'asc')->result();
			$data['user_pkb_113'] = $this->main_mod->msrwhere('user_pkb_113', array('pkb_id' => $id_pkb, 'status' => 1), 'id', 'asc')->result();
			//$data['user_pkb_13']=$this->main_mod->msrwhere('user_pkb_13',array('pkb_id'=>$id_pkb,'status'=>1),'id','asc')->result();
			$data['user_pkb_21'] = $this->main_mod->msrwhere('user_pkb_21', array('pkb_id' => $id_pkb, 'status' => 1), 'id', 'asc')->result();
			$data['user_pkb_22'] = $this->main_mod->msrwhere('user_pkb_22', array('pkb_id' => $id_pkb, 'status' => 1), 'id', 'asc')->result();
			$data['user_pkb_31'] = $this->main_mod->msrwhere('user_pkb_31', array('pkb_id' => $id_pkb, 'status' => 1), 'id', 'asc')->result();
			$data['user_pkb_32'] = $this->main_mod->msrwhere('user_pkb_32', array('pkb_id' => $id_pkb, 'status' => 1), 'id', 'asc')->result();
			//$data['user_pkb_33']=$this->main_mod->msrwhere('user_pkb_33',array('pkb_id'=>$id_pkb,'status'=>1),'id','asc')->result();
			//$data['user_pkb_34']=$this->main_mod->msrwhere('user_pkb_34',array('pkb_id'=>$id_pkb,'status'=>1),'id','asc')->result();
			$data['user_pkb_41'] = $this->main_mod->msrwhere('user_pkb_41', array('pkb_id' => $id_pkb, 'status' => 1), 'id', 'asc')->result();
			//$data['user_pkb_42']=$this->main_mod->msrwhere('user_pkb_42',array('pkb_id'=>$id_pkb,'status'=>1),'id','asc')->result();
			//$data['user_pkb_43']=$this->main_mod->msrwhere('user_pkb_43',array('pkb_id'=>$id_pkb,'status'=>1),'id','asc')->result();
			//$data['user_pkb_44']=$this->main_mod->msrwhere('user_pkb_44',array('pkb_id'=>$id_pkb,'status'=>1),'id','asc')->result();
			//$data['user_pkb_45']=$this->main_mod->msrwhere('user_pkb_45',array('pkb_id'=>$id_pkb,'status'=>1),'id','asc')->result();
			//$data['user_pkb_46']=$this->main_mod->msrwhere('user_pkb_46',array('pkb_id'=>$id_pkb,'status'=>1),'id','asc')->result();
			$data['user_pkb_51'] = $this->main_mod->msrwhere('user_pkb_51', array('pkb_id' => $id_pkb, 'status' => 1), 'id', 'asc')->result();
			//$data['user_pkb_52']=$this->main_mod->msrwhere('user_pkb_52',array('pkb_id'=>$id_pkb,'status'=>1),'id','asc')->result();
			//$data['user_pkb_53']=$this->main_mod->msrwhere('user_pkb_53',array('pkb_id'=>$id_pkb,'status'=>1),'id','asc')->result();
			//$data['user_pkb_54']=$this->main_mod->msrwhere('user_pkb_54',array('pkb_id'=>$id_pkb,'status'=>1),'id','asc')->result();

			$data['bp_11'] = $this->main_mod->msrwhere('m_bakuan_penilaian_pkb', array('pkb_num' => '11'), 'id', 'asc')->result(); //,'faip_type in ("q","r")'=>null
			$data['bp_12'] = $this->main_mod->msrwhere('m_bakuan_penilaian_pkb', array('pkb_num' => '12'), 'id', 'asc')->result();
			//$data['bp_13']=$this->main_mod->msrwhere('m_bakuan_penilaian_pkb',array('pkb_num'=>'13'),'id','asc')->result();
			$data['bp_21'] = $this->main_mod->msrwhere('m_bakuan_penilaian_pkb', array('pkb_num' => '21'), 'id', 'asc')->result();
			$data['bp_22'] = $this->main_mod->msrwhere('m_bakuan_penilaian_pkb', array('pkb_num' => '22'), 'id', 'asc')->result();
			$data['bp_31'] = $this->main_mod->msrwhere('m_bakuan_penilaian_pkb', array('pkb_num' => '31'), 'id', 'asc')->result();
			//$data['bp_32']=$this->main_mod->msrwhere('m_bakuan_penilaian_pkb',array('pkb_num'=>'32'),'id','asc')->result();
			//$data['bp_33']=$this->main_mod->msrwhere('m_bakuan_penilaian_pkb',array('pkb_num'=>'31'),'id','asc')->result();
			//$data['bp_34']=$this->main_mod->msrwhere('m_bakuan_penilaian_pkb',array('pkb_num'=>'31'),'id','asc')->result();
			$data['bp_41'] = $this->main_mod->msrwhere('m_bakuan_penilaian_pkb', array('pkb_num' => '4'), 'id', 'asc')->result();
			//$data['bp_42']=$this->main_mod->msrwhere('m_bakuan_penilaian_pkb',array('pkb_num'=>'42'),'id','asc')->result();
			//$data['bp_43']=$this->main_mod->msrwhere('m_bakuan_penilaian_pkb',array('pkb_num'=>'43'),'id','asc')->result();
			//$data['bp_44']=$this->main_mod->msrwhere('m_bakuan_penilaian_pkb',array('pkb_num'=>'44'),'id','asc')->result();
			//$data['bp_45']=$this->main_mod->msrwhere('m_bakuan_penilaian_pkb',array('pkb_num'=>'45'),'id','asc')->result();
			//$data['bp_46']=$this->main_mod->msrwhere('m_bakuan_penilaian_pkb',array('pkb_num'=>'46'),'id','asc')->result();
			$data['bp_51'] = $this->main_mod->msrwhere('m_bakuan_penilaian_pkb', array('pkb_num' => '5'), 'id', 'asc')->result();



			$data['hwb1'] = $this->input->post('hwb1') <> null ? $this->input->post('hwb1') : "";
			$data['hwb2'] = $this->input->post('hwb2') <> null ? $this->input->post('hwb2') : "";
			$data['hwb3'] = $this->input->post('hwb3') <> null ? $this->input->post('hwb3') : "";
			$data['hwb4'] = $this->input->post('hwb4') <> null ? $this->input->post('hwb4') : "";
			$data['hwb5'] = $this->input->post('hwb5') <> null ? $this->input->post('hwb5') : "";
			$data['hjml'] = $this->input->post('hjml') <> null ? $this->input->post('hjml') : "";
			$data['hkeputusan'] = $this->input->post('hkeputusan') <> null ? $this->input->post('hkeputusan') : "";

			$data['asesor_11'] = $this->pkb_model->get_all_pkb_comp_asesor($id_pkb, $this->session->userdata('admin_id'), '11');
			$data['asesor_12'] = $this->pkb_model->get_all_pkb_comp_asesor($id_pkb, $this->session->userdata('admin_id'), '12');
			//$data['asesor_13']=$this->pkb_model->get_all_pkb_comp_asesor($id_pkb,$this->session->userdata('admin_id'),'13');
			$data['asesor_21'] = $this->pkb_model->get_all_pkb_comp_asesor($id_pkb, $this->session->userdata('admin_id'), '21');
			$data['asesor_22'] = $this->pkb_model->get_all_pkb_comp_asesor($id_pkb, $this->session->userdata('admin_id'), '22');
			$data['asesor_31'] = $this->pkb_model->get_all_pkb_comp_asesor($id_pkb, $this->session->userdata('admin_id'), '31');
			$data['asesor_32'] = $this->pkb_model->get_all_pkb_comp_asesor($id_pkb, $this->session->userdata('admin_id'), '32');
			//$data['asesor_33']=$this->pkb_model->get_all_pkb_comp_asesor($id_pkb,$this->session->userdata('admin_id'),'33');
			//$data['asesor_34']=$this->pkb_model->get_all_pkb_comp_asesor($id_pkb,$this->session->userdata('admin_id'),'34');
			$data['asesor_41'] = $this->pkb_model->get_all_pkb_comp_asesor($id_pkb, $this->session->userdata('admin_id'), '41');
			//$data['asesor_42']=$this->pkb_model->get_all_pkb_comp_asesor($id_pkb,$this->session->userdata('admin_id'),'42');
			//$data['asesor_43']=$this->pkb_model->get_all_pkb_comp_asesor($id_pkb,$this->session->userdata('admin_id'),'43');
			//$data['asesor_44']=$this->pkb_model->get_all_pkb_comp_asesor($id_pkb,$this->session->userdata('admin_id'),'44');
			//$data['asesor_45']=$this->pkb_model->get_all_pkb_comp_asesor($id_pkb,$this->session->userdata('admin_id'),'45');
			//$data['asesor_46']=$this->pkb_model->get_all_pkb_comp_asesor($id_pkb,$this->session->userdata('admin_id'),'46');
			$data['asesor_51'] = $this->pkb_model->get_all_pkb_comp_asesor($id_pkb, $this->session->userdata('admin_id'), '51');
			//$data['asesor_52']=$this->pkb_model->get_all_pkb_comp_asesor($id_pkb,$this->session->userdata('admin_id'),'52');
			//$data['asesor_53']=$this->pkb_model->get_all_pkb_comp_asesor($id_pkb,$this->session->userdata('admin_id'),'53');
			//$data['asesor_54']=$this->pkb_model->get_all_pkb_comp_asesor($id_pkb,$this->session->userdata('admin_id'),'54');
			$data['asesor_pkb'] = $this->main_mod->msrwhere('asesor_pkb', array('pkb_id' => $id_pkb, 'modifiedby' => $this->session->userdata('admin_id')), 'id', 'asc')->result();
			//print_r($this->session->userdata('admin_id'));
			$this->form_validation->set_rules('hwb1', 'hwb1', 'trim|xss_clean');

			$is_submit = $this->input->post('submitpkb') <> null ? $this->input->post('submitpkb') : "";
			$submit = $this->input->post('submit') <> null ? $this->input->post('submit') : "";

			if ($this->form_validation->run()) {
				$save_partial = $this->input->post('save_partial') <> null ? $this->input->post('save_partial') : "";
				$id = $id_pkb;
				$asesor_pkb_id = 0;
				//UPDATE MASTER
				try {
					$row = array(
						'pkb_id' => $id_pkb,
						'wajib1_score' => $this->input->post('hwb1') <> null ? $this->input->post('hwb1') : "",
						'wajib2_score' => $this->input->post('hwb2') <> null ? $this->input->post('hwb2') : "",
						'wajib3_score' => $this->input->post('hwb3') <> null ? $this->input->post('hwb3') : "",
						'wajib4_score' => $this->input->post('hwb4') <> null ? $this->input->post('hwb4') : "",
						'wajib5_score' => $this->input->post('hwb5') <> null ? $this->input->post('hwb5') : "",
						'total_score' => $this->input->post('hjml') <> null ? $this->input->post('hjml') : "",
						'keputusan' => $this->input->post('hkeputusan') <> null ? $this->input->post('hkeputusan') : "",
						'tgl_keputusan' => date("Y-m-d"),
						'createdby' => $this->session->userdata('admin_id'),
						'modifiedby' => $this->session->userdata('admin_id'),
						'modifieddate' => date("Y-m-d H:i:s"),
						'status' => ($submit == "1") ? "2" : "1"
					);
					$where = array(
						'pkb_id' => $id_pkb,
						'createdby' => $this->session->userdata('admin_id')
					);

					$check = $this->main_mod->msrwhere('asesor_pkb', $where, 'id', 'desc')->result();
					if (isset($check[0])) {
						$update = $this->main_mod->update('asesor_pkb', $where, $row);
						$asesor_pkb_id = $check[0]->id;
					} else
						$asesor_pkb_id = $this->main_mod->insert('asesor_pkb', $row);
				} catch (Exception $e) {
					print_r($e);
					return false;
				}

				$p11 = $this->input->post('11_a') <> null ? $this->input->post('11_a') : "";
				$q11 = $this->input->post('11_b') <> null ? $this->input->post('11_b') : "";
				$r11 = $this->input->post('11_c') <> null ? $this->input->post('11_c') : "";
				$t11 = $this->input->post('11_t') <> null ? $this->input->post('11_t') : "";
				$c11 = $this->input->post('11_c') <> null ? $this->input->post('11_c') : "";

				$p12 = $this->input->post('12_a') <> null ? $this->input->post('12_a') : "";
				$q12 = $this->input->post('12_b') <> null ? $this->input->post('12_b') : "";
				$r12 = $this->input->post('12_c') <> null ? $this->input->post('12_c') : "";
				$t12 = $this->input->post('12_t') <> null ? $this->input->post('12_t') : "";
				$c12 = $this->input->post('12_c') <> null ? $this->input->post('12_c') : "";

				/*$p13 = $this->input->post('13_a')<>null?$this->input->post('13_a'):"";
				$q13 = $this->input->post('13_b')<>null?$this->input->post('13_b'):"";
				$r13 = $this->input->post('13_c')<>null?$this->input->post('13_c'):"";
				$t13 = $this->input->post('13_t')<>null?$this->input->post('13_t'):"";
				$c13 = $this->input->post('13_c')<>null?$this->input->post('13_c'):"";
				*/
				$p21 = $this->input->post('21_a') <> null ? $this->input->post('21_a') : "";
				$q21 = $this->input->post('21_b') <> null ? $this->input->post('21_b') : "";
				$r21 = $this->input->post('21_c') <> null ? $this->input->post('21_c') : "";
				$t21 = $this->input->post('21_t') <> null ? $this->input->post('21_t') : "";
				$c21 = $this->input->post('21_c') <> null ? $this->input->post('21_c') : "";

				$p22 = $this->input->post('22_a') <> null ? $this->input->post('22_a') : "";
				$q22 = $this->input->post('22_b') <> null ? $this->input->post('22_b') : "";
				$r22 = $this->input->post('22_c') <> null ? $this->input->post('22_c') : "";
				$t22 = $this->input->post('22_t') <> null ? $this->input->post('22_t') : "";
				$c22 = $this->input->post('22_c') <> null ? $this->input->post('22_c') : "";

				$p31 = $this->input->post('31_a') <> null ? $this->input->post('31_a') : "";
				$q31 = $this->input->post('31_b') <> null ? $this->input->post('31_b') : "";
				$r31 = $this->input->post('31_c') <> null ? $this->input->post('31_c') : "";
				$t31 = $this->input->post('31_t') <> null ? $this->input->post('31_t') : "";
				$c31 = $this->input->post('31_c') <> null ? $this->input->post('31_c') : "";

				$p32 = $this->input->post('32_a') <> null ? $this->input->post('32_a') : "";
				$q32 = $this->input->post('32_b') <> null ? $this->input->post('32_b') : "";
				$r32 = $this->input->post('32_c') <> null ? $this->input->post('32_c') : "";
				$t32 = $this->input->post('32_t') <> null ? $this->input->post('32_t') : "";
				$c32 = $this->input->post('32_c') <> null ? $this->input->post('32_c') : "";

				/*$p33 = $this->input->post('33_a')<>null?$this->input->post('33_a'):"";
				$q33 = $this->input->post('33_b')<>null?$this->input->post('33_b'):"";
				$r33 = $this->input->post('33_c')<>null?$this->input->post('33_c'):"";
				$t33 = $this->input->post('33_t')<>null?$this->input->post('33_t'):"";
				$c33 = $this->input->post('33_c')<>null?$this->input->post('33_c'):"";

				$p34 = $this->input->post('34_a')<>null?$this->input->post('34_a'):"";
				$q34 = $this->input->post('34_b')<>null?$this->input->post('34_b'):"";
				$r34 = $this->input->post('34_c')<>null?$this->input->post('34_c'):"";
				$t34 = $this->input->post('34_t')<>null?$this->input->post('34_t'):"";
				$c34 = $this->input->post('34_c')<>null?$this->input->post('34_c'):"";
				*/
				$p41 = $this->input->post('41_a') <> null ? $this->input->post('41_a') : "";
				$q41 = $this->input->post('41_b') <> null ? $this->input->post('41_b') : "";
				$r41 = $this->input->post('41_c') <> null ? $this->input->post('41_c') : "";
				$t41 = $this->input->post('41_t') <> null ? $this->input->post('41_t') : "";
				$c41 = $this->input->post('41_c') <> null ? $this->input->post('41_c') : "";
				/*
				$p42 = $this->input->post('42_a')<>null?$this->input->post('42_a'):"";
				$q42 = $this->input->post('42_b')<>null?$this->input->post('42_b'):"";
				$r42 = $this->input->post('42_c')<>null?$this->input->post('42_c'):"";
				$t42 = $this->input->post('42_t')<>null?$this->input->post('42_t'):"";
				$c42 = $this->input->post('42_c')<>null?$this->input->post('42_c'):"";

				$p43 = $this->input->post('43_a')<>null?$this->input->post('43_a'):"";
				$q43 = $this->input->post('43_b')<>null?$this->input->post('43_b'):"";
				$r43 = $this->input->post('43_c')<>null?$this->input->post('43_c'):"";
				$t43 = $this->input->post('43_t')<>null?$this->input->post('43_t'):"";
				$c43 = $this->input->post('43_c')<>null?$this->input->post('43_c'):"";

				$p44 = $this->input->post('44_a')<>null?$this->input->post('44_a'):"";
				$q44 = $this->input->post('44_b')<>null?$this->input->post('44_b'):"";
				$r44 = $this->input->post('44_c')<>null?$this->input->post('44_c'):"";
				$t44 = $this->input->post('44_t')<>null?$this->input->post('44_t'):"";
				$c44 = $this->input->post('44_c')<>null?$this->input->post('44_c'):"";
				*/
				$p51 = $this->input->post('51_a') <> null ? $this->input->post('51_a') : "";
				$q51 = $this->input->post('51_b') <> null ? $this->input->post('51_b') : "";
				$r51 = $this->input->post('51_c') <> null ? $this->input->post('51_c') : "";
				$t51 = $this->input->post('51_t') <> null ? $this->input->post('51_t') : "";
				$c51 = $this->input->post('51_c') <> null ? $this->input->post('51_c') : "";
				/*
				$p52 = $this->input->post('52_a')<>null?$this->input->post('52_a'):"";
				$q52 = $this->input->post('52_b')<>null?$this->input->post('52_b'):"";
				$r52 = $this->input->post('52_c')<>null?$this->input->post('52_c'):"";
				$t52 = $this->input->post('52_t')<>null?$this->input->post('52_t'):"";
				$c52 = $this->input->post('52_c')<>null?$this->input->post('52_c'):"";

				$p53 = $this->input->post('53_a')<>null?$this->input->post('53_a'):"";
				$q53 = $this->input->post('53_b')<>null?$this->input->post('53_b'):"";
				$r53 = $this->input->post('53_c')<>null?$this->input->post('53_c'):"";
				$t53 = $this->input->post('53_t')<>null?$this->input->post('53_t'):"";
				$c53 = $this->input->post('53_c')<>null?$this->input->post('53_c'):"";

				$p54 = $this->input->post('54_a')<>null?$this->input->post('54_a'):"";
				$q54 = $this->input->post('54_b')<>null?$this->input->post('54_b'):"";
				$r54 = $this->input->post('54_c')<>null?$this->input->post('54_c'):"";
				$t54 = $this->input->post('54_t')<>null?$this->input->post('54_t'):"";
				$c54 = $this->input->post('54_c')<>null?$this->input->post('54_c'):"";
				*/
				$user_pkb_11 = $this->main_mod->msrwhere('user_pkb_11', array('pkb_id' => $id_pkb, 'status' => 1), 'id', 'asc')->result();
				$user_pkb_12 = $this->main_mod->msrwhere('user_pkb_12', array('pkb_id' => $id_pkb, 'status' => 1), 'id', 'asc')->result();
				//$user_pkb_13 = $this->main_mod->msrwhere('user_pkb_13',array('pkb_id'=>$id_pkb,'status'=>1),'id','asc')->result();
				$user_pkb_21 = $this->main_mod->msrwhere('user_pkb_21', array('pkb_id' => $id_pkb, 'status' => 1), 'id', 'asc')->result();
				$user_pkb_22 = $this->main_mod->msrwhere('user_pkb_22', array('pkb_id' => $id_pkb, 'status' => 1), 'id', 'asc')->result();
				$user_pkb_31 = $this->main_mod->msrwhere('user_pkb_31', array('pkb_id' => $id_pkb, 'status' => 1), 'id', 'asc')->result();
				$user_pkb_32 = $this->main_mod->msrwhere('user_pkb_32', array('pkb_id' => $id_pkb, 'status' => 1), 'id', 'asc')->result();
				//$user_pkb_33 = $this->main_mod->msrwhere('user_pkb_33',array('pkb_id'=>$id_pkb,'status'=>1),'id','asc')->result();
				//$user_pkb_34 = $this->main_mod->msrwhere('user_pkb_34',array('pkb_id'=>$id_pkb,'status'=>1),'id','asc')->result();
				$user_pkb_41 = $this->main_mod->msrwhere('user_pkb_41', array('pkb_id' => $id_pkb, 'status' => 1), 'id', 'asc')->result();
				//$user_pkb_42 = $this->main_mod->msrwhere('user_pkb_42',array('pkb_id'=>$id_pkb,'status'=>1),'id','asc')->result();
				//$user_pkb_43 = $this->main_mod->msrwhere('user_pkb_43',array('pkb_id'=>$id_pkb,'status'=>1),'id','asc')->result();
				//$user_pkb_44 = $this->main_mod->msrwhere('user_pkb_44',array('pkb_id'=>$id_pkb,'status'=>1),'id','asc')->result();
				//$user_pkb_45 = $this->main_mod->msrwhere('user_pkb_45',array('pkb_id'=>$id_pkb,'status'=>1),'id','asc')->result();
				//$user_pkb_46 = $this->main_mod->msrwhere('user_pkb_46',array('pkb_id'=>$id_pkb,'status'=>1),'id','asc')->result();
				$user_pkb_51 = $this->main_mod->msrwhere('user_pkb_51', array('pkb_id' => $id_pkb, 'status' => 1), 'id', 'asc')->result();
				//$user_pkb_52 = $this->main_mod->msrwhere('user_pkb_52',array('pkb_id'=>$id_pkb,'status'=>1),'id','asc')->result();
				//$user_pkb_53 = $this->main_mod->msrwhere('user_pkb_53',array('pkb_id'=>$id_pkb,'status'=>1),'id','asc')->result();
				//$user_pkb_54 = $this->main_mod->msrwhere('user_pkb_54',array('pkb_id'=>$id_pkb,'status'=>1),'id','asc')->result();

				// COMP 11
				if ($is_submit == "1" || $save_partial == '11') {
					if ($is_submit == "1") $temp = '';
					else $temp = $this->main_mod->delete_where('asesor_score_pkb', array("pkb_id" => $id_pkb, "asesor_pkb_id" => $asesor_pkb_id, "pkb_num" => "11"));

					if (is_array($user_pkb_11)) {
						$j = 0;
						foreach ($user_pkb_11 as $val) {
							try {
								$row = array(
									'asesor_pkb_id' => $asesor_pkb_id,
									'pkb_id' => $id_pkb,
									'pkb_num' => "11",
									'user_pkb_id' => $val->id,
									//'kompetensi' => "W.2",

									'a' => $p11[$j],
									'b' => $q11[$j],
									'c' => $r11[$j],
									't' => $t11[$j],
									'notes' => "",
									'createdby' => $this->session->userdata('admin_id')
								);
								$update = $this->main_mod->insert('asesor_score_pkb', $row);
							} catch (Exception $e) {
								print_r($e);
								break;
							}
							$j++;
						}
					}
				}

				// COMP 12
				if ($is_submit == "1" || $save_partial == '12') {
					if ($is_submit == "1") $temp = '';
					else $temp = $this->main_mod->delete_where('asesor_score_pkb', array("pkb_id" => $id_pkb, "asesor_pkb_id" => $asesor_pkb_id, "pkb_num" => "12"));

					if (is_array($user_pkb_12)) {
						$j = 0;
						foreach ($user_pkb_12 as $val) {
							try {
								$row = array(
									'asesor_pkb_id' => $asesor_pkb_id,
									'pkb_id' => $id_pkb,
									'pkb_num' => "12",
									'user_pkb_id' => $val->id,
									//'kompetensi' => "W.2",

									'a' => $p12[$j],
									'b' => $q12[$j],
									'c' => $r12[$j],
									't' => $t12[$j],
									'notes' => "",
									'createdby' => $this->session->userdata('admin_id')
								);
								$update = $this->main_mod->insert('asesor_score_pkb', $row);
							} catch (Exception $e) {
								print_r($e);
								break;
							}
							$j++;
						}
					}
				}

				// COMP 21
				if ($is_submit == "1" || $save_partial == '21') {
					if ($is_submit == "1") $temp = '';
					else $temp = $this->main_mod->delete_where('asesor_score_pkb', array("pkb_id" => $id_pkb, "asesor_pkb_id" => $asesor_pkb_id, "pkb_num" => "21"));

					if (is_array($user_pkb_21)) {
						$j = 0;
						foreach ($user_pkb_21 as $val) {
							try {
								$row = array(
									'asesor_pkb_id' => $asesor_pkb_id,
									'pkb_id' => $id_pkb,
									'pkb_num' => "21",
									'user_pkb_id' => $val->id,
									//'kompetensi' => "W.2",

									'a' => $p21[$j],
									'b' => $q21[$j],
									'c' => $r21[$j],
									't' => $t21[$j],
									'notes' => "",
									'createdby' => $this->session->userdata('admin_id')
								);
								$update = $this->main_mod->insert('asesor_score_pkb', $row);
							} catch (Exception $e) {
								print_r($e);
								break;
							}
							$j++;
						}
					}
				}

				// COMP 22
				if ($is_submit == "1" || $save_partial == '22') {
					if ($is_submit == "1") $temp = '';
					else $temp = $this->main_mod->delete_where('asesor_score_pkb', array("pkb_id" => $id_pkb, "asesor_pkb_id" => $asesor_pkb_id, "pkb_num" => "22"));

					if (is_array($user_pkb_22)) {
						$j = 0;
						foreach ($user_pkb_22 as $val) {
							try {
								$row = array(
									'asesor_pkb_id' => $asesor_pkb_id,
									'pkb_id' => $id_pkb,
									'pkb_num' => "22",
									'user_pkb_id' => $val->id,
									//'kompetensi' => "W.2",

									'a' => $p22[$j],
									'b' => $q22[$j],
									'c' => $r22[$j],
									't' => $t22[$j],
									'notes' => "",
									'createdby' => $this->session->userdata('admin_id')
								);
								$update = $this->main_mod->insert('asesor_score_pkb', $row);
							} catch (Exception $e) {
								print_r($e);
								break;
							}
							$j++;
						}
					}
				}

				// COMP 31
				if ($is_submit == "1" || $save_partial == '31') {
					if ($is_submit == "1") $temp = '';
					else $temp = $this->main_mod->delete_where('asesor_score_pkb', array("pkb_id" => $id_pkb, "asesor_pkb_id" => $asesor_pkb_id, "pkb_num" => "31"));

					if (is_array($user_pkb_31)) {
						$j = 0;
						foreach ($user_pkb_31 as $val) {
							try {
								$row = array(
									'asesor_pkb_id' => $asesor_pkb_id,
									'pkb_id' => $id_pkb,
									'pkb_num' => "31",
									'user_pkb_id' => $val->id,
									//'kompetensi' => "W.2",

									'a' => $p31[$j],
									'b' => $q31[$j],
									'c' => $r31[$j],
									't' => $t31[$j],
									'notes' => "",
									'createdby' => $this->session->userdata('admin_id')
								);
								$update = $this->main_mod->insert('asesor_score_pkb', $row);
							} catch (Exception $e) {
								print_r($e);
								break;
							}
							$j++;
						}
					}
				}

				// COMP 32
				if ($is_submit == "1" || $save_partial == '32') {
					if ($is_submit == "1") $temp = '';
					else $temp = $this->main_mod->delete_where('asesor_score_pkb', array("pkb_id" => $id_pkb, "asesor_pkb_id" => $asesor_pkb_id, "pkb_num" => "32"));

					if (is_array($user_pkb_32)) {
						$j = 0;
						foreach ($user_pkb_32 as $val) {
							try {
								$row = array(
									'asesor_pkb_id' => $asesor_pkb_id,
									'pkb_id' => $id_pkb,
									'pkb_num' => "32",
									'user_pkb_id' => $val->id,
									//'kompetensi' => "W.2",

									'a' => $p32[$j],
									'b' => $q32[$j],
									'c' => $r32[$j],
									't' => $t32[$j],
									'notes' => "",
									'createdby' => $this->session->userdata('admin_id')
								);
								$update = $this->main_mod->insert('asesor_score_pkb', $row);
							} catch (Exception $e) {
								print_r($e);
								break;
							}
							$j++;
						}
					}
				}

				// COMP 33
				/*if($is_submit=="1" || $save_partial=='33'){
					if($is_submit=="1") $temp ='';
					else $temp = $this->main_mod->delete_where('asesor_score_pkb',array("pkb_id"=>$id_pkb,"asesor_pkb_id"=>$asesor_pkb_id,"pkb_num"=>"33"));

					if(is_array($user_pkb_33)){
						$j=0;
						foreach($user_pkb_33 as $val){
							try{
								$row=array(
									'asesor_pkb_id' => $asesor_pkb_id,
									'pkb_id' => $id_pkb,
									'pkb_num' => "33",
									'user_pkb_id' => $val->id,
									//'kompetensi' => "W.2",

									'a' => $p33[$j],
									'b' => $q33[$j],
									'c' => $r33[$j],
									't' => $t33[$j],
									'notes' => "",
									'createdby' => $this->session->userdata('admin_id')
								);
								$update = $this->main_mod->insert('asesor_score_pkb',$row);
							}
							catch(Exception $e){
								print_r($e);break;
							}
							$j++;
						}
					}
				}

				// COMP 34
				if($is_submit=="1" || $save_partial=='34'){
					if($is_submit=="1") $temp ='';
					else $temp = $this->main_mod->delete_where('asesor_score_pkb',array("pkb_id"=>$id_pkb,"asesor_pkb_id"=>$asesor_pkb_id,"pkb_num"=>"34"));

					if(is_array($user_pkb_34)){
						$j=0;
						foreach($user_pkb_34 as $val){
							try{
								$row=array(
									'asesor_pkb_id' => $asesor_pkb_id,
									'pkb_id' => $id_pkb,
									'pkb_num' => "34",
									'user_pkb_id' => $val->id,
									//'kompetensi' => "W.2",

									'a' => $p34[$j],
									'b' => $q34[$j],
									'c' => $r34[$j],
									't' => $t34[$j],
									'notes' => "",
									'createdby' => $this->session->userdata('admin_id')
								);
								$update = $this->main_mod->insert('asesor_score_pkb',$row);
							}
							catch(Exception $e){
								print_r($e);break;
							}
							$j++;
						}
					}
				}
				*/
				// COMP 41
				if ($is_submit == "1" || $save_partial == '41') {
					if ($is_submit == "1") $temp = '';
					else $temp = $this->main_mod->delete_where('asesor_score_pkb', array("pkb_id" => $id_pkb, "asesor_pkb_id" => $asesor_pkb_id, "pkb_num" => "41"));

					if (is_array($user_pkb_41)) {
						$j = 0;
						foreach ($user_pkb_41 as $val) {
							try {
								$row = array(
									'asesor_pkb_id' => $asesor_pkb_id,
									'pkb_id' => $id_pkb,
									'pkb_num' => "41",
									'user_pkb_id' => $val->id,
									//'kompetensi' => "W.2",

									'a' => $p41[$j],
									'b' => $q41[$j],
									'c' => $r41[$j],
									't' => $t41[$j],
									'notes' => "",
									'createdby' => $this->session->userdata('admin_id')
								);
								$update = $this->main_mod->insert('asesor_score_pkb', $row);
							} catch (Exception $e) {
								print_r($e);
								break;
							}
							$j++;
						}
					}
				}
				/*
				// COMP 42
				if($is_submit=="1" || $save_partial=='42'){
					if($is_submit=="1") $temp ='';
					else $temp = $this->main_mod->delete_where('asesor_score_pkb',array("pkb_id"=>$id_pkb,"asesor_pkb_id"=>$asesor_pkb_id,"pkb_num"=>"42"));

					if(is_array($user_pkb_42)){
						$j=0;
						foreach($user_pkb_42 as $val){
							try{
								$row=array(
									'asesor_pkb_id' => $asesor_pkb_id,
									'pkb_id' => $id_pkb,
									'pkb_num' => "42",
									'user_pkb_id' => $val->id,
									//'kompetensi' => "W.2",

									'a' => $p42[$j],
									'b' => $q42[$j],
									'c' => $r42[$j],
									't' => $t42[$j],
									'notes' => "",
									'createdby' => $this->session->userdata('admin_id')
								);
								$update = $this->main_mod->insert('asesor_score_pkb',$row);
							}
							catch(Exception $e){
								print_r($e);break;
							}
							$j++;
						}
					}
				}

				// COMP 43
				if($is_submit=="1" || $save_partial=='43'){
					if($is_submit=="1") $temp ='';
					else $temp = $this->main_mod->delete_where('asesor_score_pkb',array("pkb_id"=>$id_pkb,"asesor_pkb_id"=>$asesor_pkb_id,"pkb_num"=>"43"));

					if(is_array($user_pkb_43)){
						$j=0;
						foreach($user_pkb_43 as $val){
							try{
								$row=array(
									'asesor_pkb_id' => $asesor_pkb_id,
									'pkb_id' => $id_pkb,
									'pkb_num' => "43",
									'user_pkb_id' => $val->id,
									//'kompetensi' => "W.2",

									'a' => $p43[$j],
									'b' => $q43[$j],
									'c' => $r43[$j],
									't' => $t43[$j],
									'notes' => "",
									'createdby' => $this->session->userdata('admin_id')
								);
								$update = $this->main_mod->insert('asesor_score_pkb',$row);
							}
							catch(Exception $e){
								print_r($e);break;
							}
							$j++;
						}
					}
				}

				// COMP 44
				if($is_submit=="1" || $save_partial=='44'){
					if($is_submit=="1") $temp ='';
					else $temp = $this->main_mod->delete_where('asesor_score_pkb',array("pkb_id"=>$id_pkb,"asesor_pkb_id"=>$asesor_pkb_id,"pkb_num"=>"44"));

					if(is_array($user_pkb_44)){
						$j=0;
						foreach($user_pkb_44 as $val){
							try{
								$row=array(
									'asesor_pkb_id' => $asesor_pkb_id,
									'pkb_id' => $id_pkb,
									'pkb_num' => "44",
									'user_pkb_id' => $val->id,
									//'kompetensi' => "W.2",

									'a' => $p44[$j],
									'b' => $q44[$j],
									'c' => $r44[$j],
									't' => $t44[$j],
									'notes' => "",
									'createdby' => $this->session->userdata('admin_id')
								);
								$update = $this->main_mod->insert('asesor_score_pkb',$row);
							}
							catch(Exception $e){
								print_r($e);break;
							}
							$j++;
						}
					}
				}
				*/
				// COMP 51
				if ($is_submit == "1" || $save_partial == '51') {
					if ($is_submit == "1") $temp = '';
					else $temp = $this->main_mod->delete_where('asesor_score_pkb', array("pkb_id" => $id_pkb, "asesor_pkb_id" => $asesor_pkb_id, "pkb_num" => "51"));

					if (is_array($user_pkb_51)) {
						$j = 0;
						foreach ($user_pkb_51 as $val) {
							try {
								$row = array(
									'asesor_pkb_id' => $asesor_pkb_id,
									'pkb_id' => $id_pkb,
									'pkb_num' => "51",
									'user_pkb_id' => $val->id,
									//'kompetensi' => "W.2",

									'a' => $p51[$j],
									'b' => $q51[$j],
									'c' => $r51[$j],
									't' => $t51[$j],
									'notes' => "",
									'createdby' => $this->session->userdata('admin_id')
								);
								$update = $this->main_mod->insert('asesor_score_pkb', $row);
							} catch (Exception $e) {
								print_r($e);
								break;
							}
							$j++;
						}
					}
				}
				/*
				// COMP 52
				if($is_submit=="1" || $save_partial=='52'){
					if($is_submit=="1") $temp ='';
					else $temp = $this->main_mod->delete_where('asesor_score_pkb',array("pkb_id"=>$id_pkb,"asesor_pkb_id"=>$asesor_pkb_id,"pkb_num"=>"52"));

					if(is_array($user_pkb_52)){
						$j=0;
						foreach($user_pkb_52 as $val){
							try{
								$row=array(
									'asesor_pkb_id' => $asesor_pkb_id,
									'pkb_id' => $id_pkb,
									'pkb_num' => "52",
									'user_pkb_id' => $val->id,
									//'kompetensi' => "W.2",

									'a' => $p52[$j],
									'b' => $q52[$j],
									'c' => $r52[$j],
									't' => $t52[$j],
									'notes' => "",
									'createdby' => $this->session->userdata('admin_id')
								);
								$update = $this->main_mod->insert('asesor_score_pkb',$row);
							}
							catch(Exception $e){
								print_r($e);break;
							}
							$j++;
						}
					}
				}

				// COMP 53
				if($is_submit=="1" || $save_partial=='53'){
					if($is_submit=="1") $temp ='';
					else $temp = $this->main_mod->delete_where('asesor_score_pkb',array("pkb_id"=>$id_pkb,"asesor_pkb_id"=>$asesor_pkb_id,"pkb_num"=>"53"));

					if(is_array($user_pkb_53)){
						$j=0;
						foreach($user_pkb_53 as $val){
							try{
								$row=array(
									'asesor_pkb_id' => $asesor_pkb_id,
									'pkb_id' => $id_pkb,
									'pkb_num' => "53",
									'user_pkb_id' => $val->id,
									//'kompetensi' => "W.2",

									'a' => $p53[$j],
									'b' => $q53[$j],
									'c' => $r53[$j],
									't' => $t53[$j],
									'notes' => "",
									'createdby' => $this->session->userdata('admin_id')
								);
								$update = $this->main_mod->insert('asesor_score_pkb',$row);
							}
							catch(Exception $e){
								print_r($e);break;
							}
							$j++;
						}
					}
				}

				// COMP 54
				if($is_submit=="1" || $save_partial=='54'){
					if($is_submit=="1") $temp ='';
					else $temp = $this->main_mod->delete_where('asesor_score_pkb',array("pkb_id"=>$id_pkb,"asesor_pkb_id"=>$asesor_pkb_id,"pkb_num"=>"54"));

					if(is_array($user_pkb_54)){
						$j=0;
						foreach($user_pkb_54 as $val){
							try{
								$row=array(
									'asesor_pkb_id' => $asesor_pkb_id,
									'pkb_id' => $id_pkb,
									'pkb_num' => "54",
									'user_pkb_id' => $val->id,
									//'kompetensi' => "W.2",

									'a' => $p54[$j],
									'b' => $q54[$j],
									'c' => $r54[$j],
									't' => $t54[$j],
									'notes' => "",
									'createdby' => $this->session->userdata('admin_id')
								);
								$update = $this->main_mod->insert('asesor_score_pkb',$row);
							}
							catch(Exception $e){
								print_r($e);break;
							}
							$j++;
						}
					}
				}

				*/



				echo $id;

				if ($is_submit == "1") redirect('admin/members/pkb');
			} else
				$this->load->view('admin/pkb_edit', $data);
			return;
		}
	}

	public function get_pkb_by_id()
	{
		$id = $_POST['id'];
		$sql_data = $this->pkb_model->get_pkb_by_id($id);
		$callback = array(
			'data' => $sql_data
		);
		header('Content-Type: application/json');
		echo json_encode($sql_data);
	}

	function setippkb()
	{
		$akses = array("0", "1");
		if (!in_array($this->session->userdata('type'), $akses)) {
			redirect('admin/dashboard');
			exit;
		}

		$idmember = $this->input->post('id') <> null ? $this->input->post('id') : "";
		//$ip_cabang = $this->input->post('ip_cabang')<>null?$this->input->post('ip_cabang'):"";
		//$ip_kp = $this->input->post('ip_kp')<>null?$this->input->post('ip_kp'):"";
		$ip_bk = $this->input->post('ip_bk') <> null ? $this->input->post('ip_bk') : "";
		//$ip_subbk = $this->input->post('ip_subbk')<>null?$this->input->post('ip_subbk'):"";
		$ip_type = $this->input->post('ip_type') <> null ? $this->input->post('ip_type') : "";
		//$tgl_sk = $this->input->post('tgl_sk')<>null?$this->input->post('tgl_sk'):"";
		$from = $this->input->post('from') <> null ? $this->input->post('from') : "";
		$stri_kp = $this->input->post('stri_kp') <> null ? $this->input->post('stri_kp') : "";
		//$until = $this->input->post('until')<>null?$this->input->post('until'):"";
		$no_ip = $this->input->post('no_ip') <> null ? $this->input->post('no_ip') : "";
		if ($idmember == '') {
			redirect('admin/members');
			exit;
		}
		$this->load->model('main_mod');
		if ($idmember != '') {
			try {
				$faip = $this->pkb_model->get_pkb_by_id($idmember);
				$no_kta = $faip->no_kta;
				$userid = $faip->user_id;
				$kta = $this->members_model->get_kta_data_by_personid($userid);
				//print_r($kta);
				$checkx = $this->main_mod->msrwhere('user_profiles', array('user_id' => $userid), 'id', 'desc')->result();
				$nama = (isset($checkx[0]->firstname) ? $checkx[0]->firstname : '') . (isset($checkx[0]->firstname) ? ' ' . $checkx[0]->lastname : '');

				$title = '';
				if ($ip_type == "1") $title = 'IPP';
				else if ($ip_type == "2") $title = 'IPM';
				else if ($ip_type == "3") $title = 'IPU';

				$lic_num = $ip_type . '-' . ($ip_bk != '' ? $ip_bk : str_pad($faip->bidang, 2, '0', STR_PAD_LEFT)) . '-00-' . str_pad($no_ip, 6, '0', STR_PAD_LEFT) . '-' . str_pad($stri_kp, 2, '0', STR_PAD_LEFT);

				$rowInsert = array(
					'user_id' => $userid,
					'cert_name' => 'SERTIFIKAT KOMPETENSI INSINYUR PROFESIONAL',
					'cert_auth' => 'LSKI - PERSATUAN INSINYUR INDONESIA',
					'lic_num' => $lic_num,
					'cert_title' => $title,
					'cert_url' => $idmember,
					'location' => str_pad($kta->code_wilayah, 4, '0', STR_PAD_LEFT),
					'provinsi' => 'PKB',
					'negara' => 'Indonesia',
					'startyear' => date('Y-m-d', strtotime($from)),
					'endyear' => date('Y-m-d', strtotime("+5 years", strtotime($from))),


					'ip_kta_wilcab' => str_pad($kta->code_wilayah, 4, '0', STR_PAD_LEFT),
					'ip_kta_bk' => str_pad($kta->code_bk_hkk, 2, '0', STR_PAD_LEFT),
					'ip_kta' => str_pad($no_kta, 6, '0', STR_PAD_LEFT),
					'ip_tipe' => $ip_type,
					'ip_bk' => ($ip_bk != '' ? $ip_bk : str_pad($faip->bidang, 2, '0', STR_PAD_LEFT)),
					'ip_c' => '00',
					'ip_seq' => str_pad($no_ip, 6, '0', STR_PAD_LEFT),

					'ip_rev' => ($stri_kp != '' ? $stri_kp : '00'),
					'ip_name' => $nama,
					'status' => 2,

					'createdby' => $this->session->userdata('admin_id')
				);

				$this->main_mod->insert('user_cert', $rowInsert);

				//UBAH STATUS

				$rowInsert = array(
					'pkb_id' => $idmember,
					'old_status' => 11,
					'new_status' => 12,
					'notes' => 'lski',
					'createdby' => $this->session->userdata('admin_id'),
				);
				$this->main_mod->insert('log_status_pkb', $rowInsert);



				$where = array(
					"id" => $idmember
				);
				$row = array(
					'status_pkb' => 12,
					//'remarks' => $remarks,
					'modifieddate' => date('Y-m-d H:i:s'),
					'modifiedby' => $this->session->userdata('admin_id'),
				);
				$update = $this->main_mod->update('user_pkb', $where, $row);


				echo "valid";
			} catch (Exception $e) {
				//print_r($e);
				echo "not valid";
			}
		} else
			echo "not valid";
	}

	public function get_history_by_pkbid()
	{
		$id = $_POST['id'];
		$sql_data = $this->pkb_model->get_pkb_by_id($id);
		$result = array();
		$last_status = 1;
		if (is_object($sql_data)) {
			if ($sql_data->wkt_pernyataan != '') {
				$tgl = str_replace("tanggal ", "", $sql_data->wkt_pernyataan);
				$tmp = explode(" jam ", $tgl);
				//$tgl = str_replace(" jam","",$tgl);
				$tgl = str_replace("\r\n", " ", $tmp[0]);

				$result[] = array('value' => '1', 'status' => 'PKB Submitted', 'tgl' => $tgl, 'jam' => $tmp[1]);
			}
			$temp = $this->pkb_model->get_pkb_status_by_id($id);

			if (is_array($temp)) {
				//print_r($temp);
				$i = 0;
				foreach ($temp as $val) {
					if ($val->new_status == 1 && $i == 0) {
						$tmp = explode(" ", $val->createddate);
						$tgl = strtotime($tmp[0]);
						$tgl = date("d F Y", $tgl);
						$result[0] = array('value' => '1', 'status' => 'TO V&V (PKB)', 'tgl' => $tgl, 'jam' => substr($tmp[1], 0, 5));
					} else {
						$tmp = explode(" ", $val->createddate);
						$tgl = strtotime($tmp[0]);
						$tgl = date("d F Y", $tgl);
						$result[] = array('value' => $val->new_status, 'status' => $val->status, 'tgl' => $tgl, 'jam' => substr($tmp[1], 0, 5));
						$last_status = $val->new_status;
						$i++;
					}
				}
			}

			$temp = $this->pkb_model->get_all_pkbstatus_by_lastid($last_status);
			if (is_array($temp)) {
				foreach ($temp as $val) {
					$result[] = array('value' => $val->value, 'status' => $val->name, 'tgl' => '', 'jam' => '');
				}
			}
		}
		header('Content-Type: application/json');
		echo json_encode($result);
	}

	function download_pkb()
	{
		$akses = array("0", "1", "7", "10", "11");
		if (!in_array($this->session->userdata('type'), $akses)) {
			redirect('admin/dashboard');
			exit;
		}

		$this->load->model('main_mod');
		$id_pkb = $this->uri->segment(4);
		$this->load->model('pkb_model');
		$pkb = $this->pkb_model->get_pkb_by_id($id_pkb);
		$data['id_pkb'] = $id_pkb;
		$id = isset($pkb->user_id) ? $pkb->user_id : "";

		$obj_row = $this->members_model->get_member_by_id($id);
		$data['row'] = $obj_row;
		$data['kta'] = $this->members_model->get_kta_data_by_personid($id);

		$data['sip'] = $this->main_mod->msrwhere('user_cert', array('id' => $pkb->sip_id), 'id', 'desc')->row();

		$data['user_pkb'] = $pkb;
		$data['user_pkb_11'] = $this->main_mod->msrwhere('user_pkb_11', array('pkb_id' => $id_pkb, 'status' => 1), 'id', 'asc')->result();
		$data['user_pkb_111'] = $this->main_mod->msrwhere('user_pkb_111', array('pkb_id' => $id_pkb, 'status' => 1), 'id', 'asc')->result();
		$data['user_pkb_112'] = $this->main_mod->msrwhere('user_pkb_112', array('pkb_id' => $id_pkb, 'status' => 1), 'id', 'asc')->result();
		$data['user_pkb_113'] = $this->main_mod->msrwhere('user_pkb_113', array('pkb_id' => $id_pkb, 'status' => 1), 'id', 'asc')->result();
		$data['user_pkb_12'] = $this->main_mod->msrwhere('user_pkb_12', array('pkb_id' => $id_pkb, 'status' => 1), 'id', 'asc')->result();
		// ER: 20240729 - Ini bikin error, di tempat lain user_pkb_13 ini sudah diremove semua (commented out)
		//$data['user_pkb_13']=$this->main_mod->msrwhere('user_pkb_13',array('pkb_id'=>$id_pkb,'status'=>1),'id','asc')->result();
		$data['user_pkb_21'] = $this->main_mod->msrwhere('user_pkb_21', array('pkb_id' => $id_pkb, 'status' => 1), 'id', 'asc')->result();
		$data['user_pkb_22'] = $this->main_mod->msrwhere('user_pkb_22', array('pkb_id' => $id_pkb, 'status' => 1), 'id', 'asc')->result();
		$data['user_pkb_31'] = $this->main_mod->msrwhere('user_pkb_31', array('pkb_id' => $id_pkb, 'status' => 1), 'id', 'asc')->result();
		$data['user_pkb_32'] = $this->main_mod->msrwhere('user_pkb_32', array('pkb_id' => $id_pkb, 'status' => 1), 'id', 'asc')->result();
		$data['user_pkb_41'] = $this->main_mod->msrwhere('user_pkb_41', array('pkb_id' => $id_pkb, 'status' => 1), 'id', 'asc')->result();
		//$data['user_pkb_42']=$this->main_mod->msrwhere('user_pkb_42',array('pkb_id'=>$id_pkb,'status'=>1),'id','asc')->result();
		//$data['user_pkb_43']=$this->main_mod->msrwhere('user_pkb_43',array('pkb_id'=>$id_pkb,'status'=>1),'id','asc')->result();
		//$data['user_pkb_44']=$this->main_mod->msrwhere('user_pkb_44',array('pkb_id'=>$id_pkb,'status'=>1),'id','asc')->result();
		//$data['user_pkb_45']=$this->main_mod->msrwhere('user_pkb_45',array('pkb_id'=>$id_pkb,'status'=>1),'id','asc')->result();
		//$data['user_pkb_46']=$this->main_mod->msrwhere('user_pkb_46',array('pkb_id'=>$id_pkb,'status'=>1),'id','asc')->result();
		$data['user_pkb_51'] = $this->main_mod->msrwhere('user_pkb_51', array('pkb_id' => $id_pkb, 'status' => 1), 'id', 'asc')->result();
		//$data['user_pkb_52']=$this->main_mod->msrwhere('user_pkb_52',array('pkb_id'=>$id_pkb,'status'=>1),'id','asc')->result();
		//$data['user_pkb_53']=$this->main_mod->msrwhere('user_pkb_53',array('pkb_id'=>$id_pkb,'status'=>1),'id','asc')->result();

		//print_r($data['user_pkb_13']);

		$data['m_degree'] = $this->main_mod->msrquery('select * from education_type where HAS_TABLE="Y" or seq=9 order by SEQ asc')->result();
		$data['m_bk'] = $this->main_mod->msrwhere('m_bk', array('faip' => 1), 'id', 'asc')->result();
		//$data['emailx'] = $this->session->userdata('email');
		$users = $this->main_mod->msrwhere('users', array('id' => $id), 'id', 'asc')->row();
		$data['emailx'] = $users->email;

		//FAIP
		$data['bp_11'] = $this->main_mod->msrwhere('m_bakuan_penilaian_pkb', array('pkb_num' => '11'), 'id', 'asc')->result(); //,'faip_type in ("q","r")'=>null
		$data['bp_12'] = $this->main_mod->msrwhere('m_bakuan_penilaian_pkb', array('pkb_num' => '12'), 'id', 'asc')->result();
		$data['bp_13'] = $this->main_mod->msrwhere('m_bakuan_penilaian_pkb', array('pkb_num' => '13'), 'id', 'asc')->result();
		$data['bp_21'] = $this->main_mod->msrwhere('m_bakuan_penilaian_pkb', array('pkb_num' => '21'), 'id', 'asc')->result();
		$data['bp_22'] = $this->main_mod->msrwhere('m_bakuan_penilaian_pkb', array('pkb_num' => '22'), 'id', 'asc')->result();
		$data['bp_31'] = $this->main_mod->msrwhere('m_bakuan_penilaian_pkb', array('pkb_num' => '31'), 'id', 'asc')->result();
		$data['bp_32'] = $this->main_mod->msrwhere('m_bakuan_penilaian_pkb', array('pkb_num' => '32'), 'id', 'asc')->result();
		$data['bp_41'] = $this->main_mod->msrwhere('m_bakuan_penilaian_pkb', array('pkb_num' => '4'), 'id', 'asc')->result();
		/*$data['bp_42']=$this->main_mod->msrwhere('m_bakuan_penilaian_pkb',array('pkb_num'=>'42'),'id','asc')->result();
		$data['bp_43']=$this->main_mod->msrwhere('m_bakuan_penilaian_pkb',array('pkb_num'=>'43'),'id','asc')->result();
		$data['bp_44']=$this->main_mod->msrwhere('m_bakuan_penilaian_pkb',array('pkb_num'=>'44'),'id','asc')->result();
		$data['bp_45']=$this->main_mod->msrwhere('m_bakuan_penilaian_pkb',array('pkb_num'=>'45'),'id','asc')->result();
		$data['bp_46']=$this->main_mod->msrwhere('m_bakuan_penilaian_pkb',array('pkb_num'=>'46'),'id','asc')->result();*/
		$data['bp_51'] = $this->main_mod->msrwhere('m_bakuan_penilaian_pkb', array('pkb_num' => '5'), 'id', 'asc')->result();
		//$data['bp_52']=$this->main_mod->msrwhere('m_bakuan_penilaian_pkb',array('pkb_num'=>'52'),'id','asc')->result();
		//$data['bp_53']=$this->main_mod->msrwhere('m_bakuan_penilaian_pkb',array('pkb_num'=>'53'),'id','asc')->result();


		$this->load->view('admin/download_pkb_2', $data);
	}

	function download_pkb_2()
	{
		$akses = array("0", "1", "7", "10", "11", "16");
		if (!in_array($this->session->userdata('type'), $akses)) {
			redirect('admin/dashboard');
			exit;
		}

		$this->load->model('main_mod');
		$id_pkb = $this->uri->segment(4);
		$this->load->model('pkb_model');
		$pkb = $this->pkb_model->get_pkb_by_id($id_pkb);
		$data['id_pkb'] = $id_pkb;
		$id = isset($pkb->user_id) ? $pkb->user_id : "";

		$obj_row = $this->members_model->get_member_by_id($id);
		$data['row'] = $obj_row;
		$data['kta'] = $this->members_model->get_kta_data_by_personid($id);

		$data['sip'] = $this->main_mod->msrwhere('user_cert', array('id' => $pkb->sip_id), 'id', 'desc')->row();
		$data['stri'] = $this->main_mod->msrwhere('members_certificate', array('person_id' => $this->session->userdata('user_id'), "TRIM(LEADING '0' FROM skip_id)=" => (isset($data['sip']->ip_seq) ? ltrim($data['sip']->ip_seq, "0") : '')), 'id', 'desc')->row();

		$data['user_pkb'] = $pkb;
		$data['user_pkb_11'] = $this->main_mod->msrwhere('user_pkb_11', array('pkb_id' => $id_pkb, 'status' => 1), 'id', 'asc')->result();
		$data['user_pkb_111'] = $this->main_mod->msrwhere('user_pkb_111', array('pkb_id' => $id_pkb, 'status' => 1), 'id', 'asc')->result();
		$data['user_pkb_112'] = $this->main_mod->msrwhere('user_pkb_112', array('pkb_id' => $id_pkb, 'status' => 1), 'id', 'asc')->result();
		$data['user_pkb_113'] = $this->main_mod->msrwhere('user_pkb_113', array('pkb_id' => $id_pkb, 'status' => 1), 'id', 'asc')->result();
		$data['user_pkb_12'] = $this->main_mod->msrwhere('user_pkb_12', array('pkb_id' => $id_pkb, 'status' => 1), 'id', 'asc')->result();
		//$data['user_pkb_13']=$this->main_mod->msrwhere('user_pkb_13',array('pkb_id'=>$id_pkb,'status'=>1),'id','asc')->result();
		$data['user_pkb_21'] = $this->main_mod->msrwhere('user_pkb_21', array('pkb_id' => $id_pkb, 'status' => 1), 'id', 'asc')->result();
		$data['user_pkb_22'] = $this->main_mod->msrwhere('user_pkb_22', array('pkb_id' => $id_pkb, 'status' => 1), 'id', 'asc')->result();
		$data['user_pkb_31'] = $this->main_mod->msrwhere('user_pkb_31', array('pkb_id' => $id_pkb, 'status' => 1), 'id', 'asc')->result();
		$data['user_pkb_32'] = $this->main_mod->msrwhere('user_pkb_32', array('pkb_id' => $id_pkb, 'status' => 1), 'id', 'asc')->result();
		//$data['user_pkb_33']=$this->main_mod->msrwhere('user_pkb_33',array('pkb_id'=>$id_pkb,'status'=>1),'id','asc')->result();
		//$data['user_pkb_34']=$this->main_mod->msrwhere('user_pkb_34',array('pkb_id'=>$id_pkb,'status'=>1),'id','asc')->result();
		$data['user_pkb_41'] = $this->main_mod->msrwhere('user_pkb_41', array('pkb_id' => $id_pkb, 'status' => 1), 'id', 'asc')->result();
		//$data['user_pkb_42']=$this->main_mod->msrwhere('user_pkb_42',array('pkb_id'=>$id_pkb,'status'=>1),'id','asc')->result();
		//$data['user_pkb_43']=$this->main_mod->msrwhere('user_pkb_43',array('pkb_id'=>$id_pkb,'status'=>1),'id','asc')->result();
		//$data['user_pkb_44']=$this->main_mod->msrwhere('user_pkb_44',array('pkb_id'=>$id_pkb,'status'=>1),'id','asc')->result();
		//$data['user_pkb_45']=$this->main_mod->msrwhere('user_pkb_45',array('pkb_id'=>$id_pkb,'status'=>1),'id','asc')->result();
		//$data['user_pkb_46']=$this->main_mod->msrwhere('user_pkb_46',array('pkb_id'=>$id_pkb,'status'=>1),'id','asc')->result();
		$data['user_pkb_51'] = $this->main_mod->msrwhere('user_pkb_51', array('pkb_id' => $id_pkb, 'status' => 1), 'id', 'asc')->result();
		//$data['user_pkb_52']=$this->main_mod->msrwhere('user_pkb_52',array('pkb_id'=>$id_pkb,'status'=>1),'id','asc')->result();
		//$data['user_pkb_53']=$this->main_mod->msrwhere('user_pkb_53',array('pkb_id'=>$id_pkb,'status'=>1),'id','asc')->result();
		//$data['user_pkb_54']=$this->main_mod->msrwhere('user_pkb_54',array('pkb_id'=>$id_pkb,'status'=>1),'id','asc')->result();

		//print_r($data['user_pkb_13']);

		$data['m_degree'] = $this->main_mod->msrquery('select * from education_type where HAS_TABLE="Y" or seq=9 order by SEQ asc')->result();
		$data['m_bk'] = $this->main_mod->msrwhere('m_bk', array('faip' => 1), 'id', 'asc')->result();
		//$data['emailx'] = $this->session->userdata('email');
		$users = $this->main_mod->msrwhere('users', array('id' => $id), 'id', 'asc')->row();
		$data['emailx'] = $users->email;

		//FAIP
		$data['bp_11'] = $this->main_mod->msrwhere('m_bakuan_penilaian_pkb', array('pkb_num' => '11'), 'id', 'asc')->result(); //,'faip_type in ("q","r")'=>null
		$data['bp_12'] = $this->main_mod->msrwhere('m_bakuan_penilaian_pkb', array('pkb_num' => '12'), 'id', 'asc')->result();
		//$data['bp_13']=$this->main_mod->msrwhere('m_bakuan_penilaian_pkb',array('pkb_num'=>'13'),'id','asc')->result();
		$data['bp_21'] = $this->main_mod->msrwhere('m_bakuan_penilaian_pkb', array('pkb_num' => '21'), 'id', 'asc')->result();
		$data['bp_22'] = $this->main_mod->msrwhere('m_bakuan_penilaian_pkb', array('pkb_num' => '22'), 'id', 'asc')->result();
		$data['bp_31'] = $this->main_mod->msrwhere('m_bakuan_penilaian_pkb', array('pkb_num' => '31'), 'id', 'asc')->result();
		//$data['bp_32']=$this->main_mod->msrwhere('m_bakuan_penilaian_pkb',array('pkb_num'=>'32'),'id','asc')->result();
		//$data['bp_33']=$this->main_mod->msrwhere('m_bakuan_penilaian_pkb',array('pkb_num'=>'31'),'id','asc')->result();
		//$data['bp_34']=$this->main_mod->msrwhere('m_bakuan_penilaian_pkb',array('pkb_num'=>'31'),'id','asc')->result();
		$data['bp_41'] = $this->main_mod->msrwhere('m_bakuan_penilaian_pkb', array('pkb_num' => '4'), 'id', 'asc')->result();
		//$data['bp_42']=$this->main_mod->msrwhere('m_bakuan_penilaian_pkb',array('pkb_num'=>'42'),'id','asc')->result();
		//$data['bp_43']=$this->main_mod->msrwhere('m_bakuan_penilaian_pkb',array('pkb_num'=>'43'),'id','asc')->result();
		//$data['bp_44']=$this->main_mod->msrwhere('m_bakuan_penilaian_pkb',array('pkb_num'=>'44'),'id','asc')->result();
		//$data['bp_45']=$this->main_mod->msrwhere('m_bakuan_penilaian_pkb',array('pkb_num'=>'45'),'id','asc')->result();
		//$data['bp_46']=$this->main_mod->msrwhere('m_bakuan_penilaian_pkb',array('pkb_num'=>'46'),'id','asc')->result();
		$data['bp_51'] = $this->main_mod->msrwhere('m_bakuan_penilaian_pkb', array('pkb_num' => '5'), 'id', 'asc')->result();


		$this->load->view('admin/download_pkb_2', $data);
	}

	public function pkbview2()
	{
		$akses = array("0", "1", "7", "10", "11");
		if (!in_array($this->session->userdata('type'), $akses)) {
			redirect('admin/dashboard');
			exit;
		}

		$data['title'] = ''; //SITE_NAME.': Manage Members';
		$data['msg'] = '';

		$id_pkb = $this->uri->segment(4);
		$asesor_id = $this->uri->segment(5);
		$pkb = $this->pkb_model->get_pkb_by_id($id_pkb);
		$id = isset($pkb->user_id) ? $pkb->user_id : "";
		//print_r($asesor_id);
		if (
			$this->session->userdata('type') == ADMIN_SUPERADMIN
			|| $this->session->userdata('type') == "1"
			|| $this->session->userdata('type') == "11"
		) {
			if ($id != '') {

				$this->load->model('main_mod');
				$obj_row = $this->members_model->get_member_by_id($id);
				$data['id_pkb'] = $id_pkb;
				$data['row'] = $obj_row;
				$data['kta'] = $this->members_model->get_kta_data_by_personid($id);;
				$data['emailx'] = $obj_row->email;

				$data['m_bk'] = $this->main_mod->msrwhere('m_bk', null, 'id', 'asc')->result();
				$data['sip'] = $this->main_mod->msrwhere('user_cert', array('id' => $pkb->sip_id), 'id', 'desc')->row();
				$data['stri'] = $this->main_mod->msrwhere('members_certificate', array('person_id' => $this->session->userdata('user_id'), "TRIM(LEADING '0' FROM skip_id)=" => (isset($data['sip']->ip_seq) ? ltrim($data['sip']->ip_seq, "0") : '')), 'id', 'desc')->row();

				$data['m_degree'] = $this->main_mod->msrwhere('education_type', null, 'SEQ', 'asc')->result();
				$data['user_email'] = $this->main_mod->msrwhere('contacts', array('user_id' => $id, 'contact_type like "%_email%"' => null, 'status' => 1), 'id', 'asc')->result();

				$data['user_pkb'] = $pkb;
				$data['user_pkb_11'] = $this->main_mod->msrwhere('user_pkb_11', array('pkb_id' => $id_pkb, 'status' => 1), 'id', 'asc')->result();
				$data['user_pkb_12'] = $this->main_mod->msrwhere('user_pkb_12', array('pkb_id' => $id_pkb, 'status' => 1), 'id', 'asc')->result();
				$data['user_pkb_111'] = $this->main_mod->msrwhere('user_pkb_111', array('pkb_id' => $id_pkb, 'status' => 1), 'id', 'asc')->result();
				$data['user_pkb_112'] = $this->main_mod->msrwhere('user_pkb_112', array('pkb_id' => $id_pkb, 'status' => 1), 'id', 'asc')->result();
				$data['user_pkb_113'] = $this->main_mod->msrwhere('user_pkb_113', array('pkb_id' => $id_pkb, 'status' => 1), 'id', 'asc')->result();
				//$data['user_pkb_13']=$this->main_mod->msrwhere('user_pkb_13',array('pkb_id'=>$id_pkb,'status'=>1),'id','asc')->result();
				$data['user_pkb_21'] = $this->main_mod->msrwhere('user_pkb_21', array('pkb_id' => $id_pkb, 'status' => 1), 'id', 'asc')->result();
				$data['user_pkb_22'] = $this->main_mod->msrwhere('user_pkb_22', array('pkb_id' => $id_pkb, 'status' => 1), 'id', 'asc')->result();
				$data['user_pkb_31'] = $this->main_mod->msrwhere('user_pkb_31', array('pkb_id' => $id_pkb, 'status' => 1), 'id', 'asc')->result();
				$data['user_pkb_32'] = $this->main_mod->msrwhere('user_pkb_32', array('pkb_id' => $id_pkb, 'status' => 1), 'id', 'asc')->result();
				//$data['user_pkb_33']=$this->main_mod->msrwhere('user_pkb_33',array('pkb_id'=>$id_pkb,'status'=>1),'id','asc')->result();
				//$data['user_pkb_34']=$this->main_mod->msrwhere('user_pkb_34',array('pkb_id'=>$id_pkb,'status'=>1),'id','asc')->result();
				$data['user_pkb_41'] = $this->main_mod->msrwhere('user_pkb_41', array('pkb_id' => $id_pkb, 'status' => 1), 'id', 'asc')->result();
				//$data['user_pkb_42']=$this->main_mod->msrwhere('user_pkb_42',array('pkb_id'=>$id_pkb,'status'=>1),'id','asc')->result();
				//$data['user_pkb_43']=$this->main_mod->msrwhere('user_pkb_43',array('pkb_id'=>$id_pkb,'status'=>1),'id','asc')->result();
				//$data['user_pkb_44']=$this->main_mod->msrwhere('user_pkb_44',array('pkb_id'=>$id_pkb,'status'=>1),'id','asc')->result();
				//$data['user_pkb_45']=$this->main_mod->msrwhere('user_pkb_45',array('pkb_id'=>$id_pkb,'status'=>1),'id','asc')->result();
				//$data['user_pkb_46']=$this->main_mod->msrwhere('user_pkb_46',array('pkb_id'=>$id_pkb,'status'=>1),'id','asc')->result();
				$data['user_pkb_51'] = $this->main_mod->msrwhere('user_pkb_51', array('pkb_id' => $id_pkb, 'status' => 1), 'id', 'asc')->result();
				//$data['user_pkb_52']=$this->main_mod->msrwhere('user_pkb_52',array('pkb_id'=>$id_pkb,'status'=>1),'id','asc')->result();
				//$data['user_pkb_53']=$this->main_mod->msrwhere('user_pkb_53',array('pkb_id'=>$id_pkb,'status'=>1),'id','asc')->result();
				//$data['user_pkb_54']=$this->main_mod->msrwhere('user_pkb_54',array('pkb_id'=>$id_pkb,'status'=>1),'id','asc')->result();

				$data['bp_11'] = $this->main_mod->msrwhere('m_bakuan_penilaian_pkb', array('pkb_num' => '11'), 'id', 'asc')->result(); //,'faip_type in ("q","r")'=>null
				$data['bp_12'] = $this->main_mod->msrwhere('m_bakuan_penilaian_pkb', array('pkb_num' => '12'), 'id', 'asc')->result();
				//$data['bp_13']=$this->main_mod->msrwhere('m_bakuan_penilaian_pkb',array('pkb_num'=>'13'),'id','asc')->result();
				$data['bp_21'] = $this->main_mod->msrwhere('m_bakuan_penilaian_pkb', array('pkb_num' => '21'), 'id', 'asc')->result();
				$data['bp_22'] = $this->main_mod->msrwhere('m_bakuan_penilaian_pkb', array('pkb_num' => '22'), 'id', 'asc')->result();
				$data['bp_31'] = $this->main_mod->msrwhere('m_bakuan_penilaian_pkb', array('pkb_num' => '31'), 'id', 'asc')->result();
				//$data['bp_32']=$this->main_mod->msrwhere('m_bakuan_penilaian_pkb',array('pkb_num'=>'32'),'id','asc')->result();
				//$data['bp_33']=$this->main_mod->msrwhere('m_bakuan_penilaian_pkb',array('pkb_num'=>'31'),'id','asc')->result();
				//$data['bp_34']=$this->main_mod->msrwhere('m_bakuan_penilaian_pkb',array('pkb_num'=>'31'),'id','asc')->result();
				$data['bp_41'] = $this->main_mod->msrwhere('m_bakuan_penilaian_pkb', array('pkb_num' => '4'), 'id', 'asc')->result();
				//$data['bp_42']=$this->main_mod->msrwhere('m_bakuan_penilaian_pkb',array('pkb_num'=>'42'),'id','asc')->result();
				//$data['bp_43']=$this->main_mod->msrwhere('m_bakuan_penilaian_pkb',array('pkb_num'=>'43'),'id','asc')->result();
				//$data['bp_44']=$this->main_mod->msrwhere('m_bakuan_penilaian_pkb',array('pkb_num'=>'44'),'id','asc')->result();
				//$data['bp_45']=$this->main_mod->msrwhere('m_bakuan_penilaian_pkb',array('pkb_num'=>'45'),'id','asc')->result();
				//$data['bp_46']=$this->main_mod->msrwhere('m_bakuan_penilaian_pkb',array('pkb_num'=>'46'),'id','asc')->result();
				$data['bp_51'] = $this->main_mod->msrwhere('m_bakuan_penilaian_pkb', array('pkb_num' => '5'), 'id', 'asc')->result();



				$data['hwb1'] = $this->input->post('hwb1') <> null ? $this->input->post('hwb1') : "";
				$data['hwb2'] = $this->input->post('hwb2') <> null ? $this->input->post('hwb2') : "";
				$data['hwb3'] = $this->input->post('hwb3') <> null ? $this->input->post('hwb3') : "";
				$data['hwb4'] = $this->input->post('hwb4') <> null ? $this->input->post('hwb4') : "";
				$data['hwb5'] = $this->input->post('hwb5') <> null ? $this->input->post('hwb5') : "";
				$data['hjml'] = $this->input->post('hjml') <> null ? $this->input->post('hjml') : "";
				$data['hkeputusan'] = $this->input->post('hkeputusan') <> null ? $this->input->post('hkeputusan') : "";

				$data['asesor_11'] = $this->pkb_model->get_all_pkb_comp_asesor($id_pkb, $asesor_id, '11');
				$data['asesor_12'] = $this->pkb_model->get_all_pkb_comp_asesor($id_pkb, $asesor_id, '12');
				//$data['asesor_13']=$this->pkb_model->get_all_pkb_comp_asesor($id_pkb,$asesor_id,'13');
				$data['asesor_21'] = $this->pkb_model->get_all_pkb_comp_asesor($id_pkb, $asesor_id, '21');
				$data['asesor_22'] = $this->pkb_model->get_all_pkb_comp_asesor($id_pkb, $asesor_id, '22');
				$data['asesor_31'] = $this->pkb_model->get_all_pkb_comp_asesor($id_pkb, $asesor_id, '31');
				$data['asesor_32'] = $this->pkb_model->get_all_pkb_comp_asesor($id_pkb, $asesor_id, '32');
				//$data['asesor_33']=$this->pkb_model->get_all_pkb_comp_asesor($id_pkb,$asesor_id,'33');
				//$data['asesor_34']=$this->pkb_model->get_all_pkb_comp_asesor($id_pkb,$asesor_id,'34');
				$data['asesor_41'] = $this->pkb_model->get_all_pkb_comp_asesor($id_pkb, $asesor_id, '41');
				//$data['asesor_42']=$this->pkb_model->get_all_pkb_comp_asesor($id_pkb,$asesor_id,'42');
				//$data['asesor_43']=$this->pkb_model->get_all_pkb_comp_asesor($id_pkb,$asesor_id,'43');
				//$data['asesor_44']=$this->pkb_model->get_all_pkb_comp_asesor($id_pkb,$asesor_id,'44');
				//$data['asesor_45']=$this->pkb_model->get_all_pkb_comp_asesor($id_pkb,$asesor_id,'45');
				//$data['asesor_46']=$this->pkb_model->get_all_pkb_comp_asesor($id_pkb,$asesor_id,'46');
				$data['asesor_51'] = $this->pkb_model->get_all_pkb_comp_asesor($id_pkb, $asesor_id, '51');
				//$data['asesor_52']=$this->pkb_model->get_all_pkb_comp_asesor($id_pkb,$asesor_id,'52');
				//$data['asesor_53']=$this->pkb_model->get_all_pkb_comp_asesor($id_pkb,$asesor_id,'53');
				//$data['asesor_54']=$this->pkb_model->get_all_pkb_comp_asesor($id_pkb,$asesor_id,'54');
				$data['asesor_pkb'] = $this->main_mod->msrwhere('asesor_pkb', array('pkb_id' => $id_pkb, 'modifiedby' => $asesor_id), 'id', 'asc')->result();


				$this->form_validation->set_rules('hwb1', 'hwb1', 'trim|xss_clean');

				$is_submit = $this->input->post('submitpkb') <> null ? $this->input->post('submitpkb') : "";
				$submit = $this->input->post('submit') <> null ? $this->input->post('submit') : "";

				$data['asesor'] = $this->main_mod->msrwhere('user_profiles', array('user_id' => $asesor_id), 'id', 'desc')->result();

				$this->load->view('admin/pkb_edit2', $data);
				return;
			}
		}
	}

	public function edit_score_pkb()
	{
		$id_pkb = $this->input->post('id') <> null ? $this->input->post('id') : "";

		if ($id_pkb != "") {
			$this->load->model('main_mod');
			try {
				$row = array(
					'pkb_id' => $id_pkb,
					'wajib1_score' => $this->input->post('hwb1') <> null ? $this->input->post('hwb1') : "",
					'wajib2_score' => $this->input->post('hwb2') <> null ? $this->input->post('hwb2') : "",
					'wajib3_score' => $this->input->post('hwb3') <> null ? $this->input->post('hwb3') : "",
					'wajib4_score' => $this->input->post('hwb4') <> null ? $this->input->post('hwb4') : "",
					'wajib5_score' => $this->input->post('hwb5') <> null ? $this->input->post('hwb5') : "",
					'total_score' => $this->input->post('hjml') <> null ? $this->input->post('hjml') : "",
					'keputusan' => $this->input->post('hkeputusan') <> null ? $this->input->post('hkeputusan') : "",
					'tgl_keputusan' => date("Y-m-d"),
					'createdby' => $this->session->userdata('admin_id'),
					'modifiedby' => $this->session->userdata('admin_id'),
					'modifieddate' => date("Y-m-d H:i:s"),
					'status' => "2"
				);
				$where = array(
					'pkb_id' => $id_pkb,
					'createdby' => $this->session->userdata('admin_id')
				);

				$check = $this->main_mod->msrwhere('asesor_pkb', $where, 'id', 'desc')->result();
				if (isset($check[0])) {
					$update = $this->main_mod->update('asesor_pkb', $where, $row);
					$asesor_pkb_id = $check[0]->id;
				} else
					$asesor_pkb_id = $this->main_mod->insert('asesor_pkb', $row);

				echo 'valid';
			} catch (Exception $e) {
				//print_r($e);
				//return false;
				echo 'not valid';
			}
		}
	}


	public function copypkb()
	{
		$id_faip = $this->input->post('pkb_id') <> null ? $this->input->post('pkb_id') : "";
		$majelis = $this->input->post('majelis') <> null ? $this->input->post('majelis') : "";

		if ($id_faip != "") {
			$this->load->model('main_mod');
			try {
				$where = array(
					'id' => $id_faip,
					'(majelis1 = ' . $this->session->userdata('admin_id') . ' or majelis2 = ' . $this->session->userdata('admin_id') . ' or majelis3 = ' . $this->session->userdata('admin_id') . ')' => null,
					'(status_pkb>=6 and status_pkb<=8)' => null
				);
				$check = $this->main_mod->msrwhere('user_pkb', $where, 'id', 'desc')->result();
				if (isset($check[0])) {
					$where2 = array(
						'createdby' => $majelis,
						'pkb_id' => $id_faip
					);
					$asesor_pkb = $this->main_mod->msrwhere('asesor_pkb', $where2, 'id', 'desc')->result();

					if (isset($check[0])) {

						//INSERT ASESOR FAIP
						$where4 = array(
							'pkb_id' => $id_faip,
							'createdby' => $this->session->userdata('admin_id')
						);
						$check4 = $this->main_mod->msrwhere('asesor_pkb', $where4, 'id', 'desc')->result();

						$asesor_pkb_id = '';
						$row = array(
							'pkb_id' => $id_faip,
							'wajib1_score' => $asesor_pkb[0]->wajib1_score,
							'wajib2_score' => $asesor_pkb[0]->wajib2_score,
							'wajib3_score' => $asesor_pkb[0]->wajib3_score,
							'wajib4_score' => $asesor_pkb[0]->wajib4_score,
							'wajib5_score' => $asesor_pkb[0]->wajib5_score,
							'total_score' => $asesor_pkb[0]->total_score,
							'keputusan' => $asesor_pkb[0]->keputusan,
							'tgl_keputusan' => date("Y-m-d"),
							'createdby' => $this->session->userdata('admin_id'),
							'modifiedby' => $this->session->userdata('admin_id'),
							'modifieddate' => date("Y-m-d H:i:s"),
							'status' => "2"
						);

						if (isset($check4[0])) {
							$update = $this->main_mod->update('asesor_pkb', $where4, $row);
							$asesor_pkb_id = $check4[0]->id;
						} else
							$asesor_pkb_id = $this->main_mod->insert('asesor_pkb', $row);



						$where3 = array(
							'asesor_pkb_id' => $asesor_pkb[0]->id,
							'pkb_id' => $id_faip
						);
						$asesor_score_pkb = $this->main_mod->msrwhere('asesor_score_pkb', $where3, 'id', 'desc')->result();
						//print_r($asesor_pkb_id);
						//INSERT ASESOR SCORE
						$this->main_mod->delete_where('asesor_score_pkb', array("asesor_pkb_id" => $asesor_pkb_id));
						foreach ($asesor_score_pkb as $val) {
							$row = array(
								'asesor_pkb_id' => $asesor_pkb_id,
								'pkb_id' => $val->pkb_id,
								'pkb_num' => $val->pkb_num,
								'user_pkb_id' => $val->user_pkb_id,
								'kompetensi' => $val->kompetensi,
								'a' => $val->a,
								'b' => $val->b,
								'c' => $val->c,
								't' => $val->t,
								'notes' => $val->notes,
								'status' => $val->status,
								'is_add_by_majelis' => $val->is_add_by_majelis,
								'createdby' => $this->session->userdata('admin_id'),
							);
							$this->main_mod->insert('asesor_score_pkb', $row);
						}

						echo 'valid';
					}
				} else
					echo 'not valid';
			} catch (Exception $e) {
				//print_r($e);
				//return false;
				echo 'not valid';
			}
		}
	}

	/* TEST
	public function unittest(){
		$this->load->model('main_mod');
		$check = $this->main_mod->msrwhere('members',array('person_id'=>1),'id','desc')->result();
		print_r($check[0]->from_date);
	}

	public function check_ktp(){
		$this->load->model('main_mod');
		$ktp=$this->main_mod->msrquery("SELECT * FROM user_profiles WHERE id_file like '%BKT%' limit 100")->result();

		foreach($ktp as $val){
			$compartment = "_KTP_".$val->id;

			$files = glob("/var/www/html/assets/uploads/*$compartment.*");
			if (count($files) == 1){
				foreach ($files as $file)
				{
					$info = pathinfo($file);
					echo $compartment." ada 1 : ".$info["basename"]."<br />";

					$where = array(
						"user_id" => $val->id
					);
					$row=array(
						'id_file' => $info["basename"],
					);
					$update = $this->main_mod->update('user_profiles',$where,$row);


				}
			}
			else if (count($files) > 1){
				$temp = '';
				foreach ($files as $file)
				{
					$info = pathinfo($file);
					if($info["basename"]>$temp)
						$temp = $info["basename"];
				}
				echo $compartment." ada banyak : ".$info["basename"]."<br />";

				$where = array(
					"user_id" => $val->id
				);
				$row=array(
					'id_file' => $temp,
				);
				$update = $this->main_mod->update('user_profiles',$where,$row);


			}
			else
			{
				echo $compartment." - No file.<br />";

				$where = array(
					"user_id" => $val->id
				);
				$row=array(
					'id_file' => null,
				);
				$update = $this->main_mod->update('user_profiles',$where,$row);
			}

		}
	}
	TEST */
	function _send_email($type, $subject, $to_email, $cc_email, &$data)
	{
		$this->load->library('MyPHPMailer');
		$mail = new PHPMailer();

		/*
		//$mail->IsSMTP(true); // we are going to use SMTP
        	$mail->SMTPAuth   = true; // enabled SMTP authentication

		$mail->SMTPSecure = "ssl";  // prefix for secure protocol to connect to the server
        	$mail->Host       = "smtp.gmail.com";      // setting GMail as our SMTP server
        	$mail->Port       = 465;                   // SMTP port to connect to GMail
        	//$mail->Username   = "updmember@gmail.com";  // user email address
        	//$mail->Password   = "serimpi37!1";            // password in GMail  serimpi37!1

		$mail->Username   = "simponi@pii.or.id";  // user email address
        	$mail->Password   = "S!mponi@PII";            // password in GMail  serimpi37!1
		 */

		$mail->IsSMTP(true);
		$mail->Host = 'smtp.office365.com';
		$mail->Port       = 587;
		$mail->SMTPSecure = 'tls';
		$mail->SMTPAuth   = true;
		$mail->Username = 'simponi@pii.or.id';
		$mail->Password = 'ndkgyyllfkzhbmzz';
		//$mail->SMTPDebug  = 2;
		//$mail->Debugoutput = function($str, $level) {echo "debug level $level; message: $str";};

		$mail->SetFrom('simponi@pii.or.id', 'simponi');  //Who is sending the email
		$mail->AddReplyTo('simponi@pii.or.id', 'simponi');

		/*
		$mail->SMTPSecure = "tls";  // prefix for secure protocol to connect to the server
        	$mail->Host       = "mail.pii.or.id";      // setting GMail as our SMTP server
        	$mail->Port       = 587;                   // SMTP port to connect to GMail
        	$mail->Username   = "updmember@pii.or.id";  // user email address
        	$mail->Password   = "123456789";            // password in GMail
        	$mail->SetFrom('updmember@pii.or.id', 'updmember');  //Who is sending the email
		*/

		/* 14 Apr 2024 - Commented out by Eryan
		$mail->SMTPOptions = array(
			'ssl' => array(
				'verify_peer' => false,
				'verify_peer_name' => false,
				'allow_self_signed' => true
			)
		);
		*/

		$mail->SetFrom('simponi@pii.or.id', 'simponi');  //Who is sending the email
		$mail->Subject    	= $subject;
		$mail->Body      	= $this->load->view('email/' . $type . '-html', $data, TRUE);
		$mail->AltBody    	= $this->load->view('email/' . $type . '-txt', $data, TRUE);

		foreach ($to_email as $v) {
			$mail->AddAddress($v);
		}

		foreach ($cc_email as $v) {
			$mail->AddCC($v);
		}

		//$mail->addBcc("d.angger@yahoo.com");
		//$mail->addBcc("blank.anonim4@gmail.com");

		//print_r($mail);


		try {
			if (!$mail->Send()) {
				echo "Error: " . $mail->ErrorInfo;
			}
		} catch (Exception $e) {
			echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
		}
	}

	function _send_email_va($type, $subject, $to_email, $cc_email, &$data)
	{
		$this->load->library('MyPHPMailer');
		$mail = new PHPMailer();

		/*
		// 14 April 2024 - Commented out by Eryan - migrating to MS O365
        //$mail->IsSMTP(true); // we are going to use SMTP
        $mail->SMTPAuth   = true; // enabled SMTP authentication

		$mail->SMTPSecure = "ssl";  // prefix for secure protocol to connect to the server
        $mail->Host       = "smtp.gmail.com";      // setting GMail as our SMTP server
        $mail->Port       = 465;                   // SMTP port to connect to GMail
        //$mail->Username   = "updmember@gmail.com";  // user email address
        //$mail->Password   = "serimpi37!1";            // password in GMail serimpi37!1  yapvnlsmzpvidjxl
		//$mail->SetFrom('updmember@pii.or.id', 'updmember');  //Who is sending the email
		//$mail->AddReplyTo('updmember@pii.or.id', 'updmember');

		$mail->Username   = "simponi@pii.or.id";  // user email address
        $mail->Password   = "S!mponi@PII";            // password in GMail  serimpi37!1
		 */

		$mail->IsSMTP(true);
		$mail->Host = 'smtp.office365.com';
		$mail->Port       = 587;
		$mail->SMTPSecure = 'tls';
		$mail->SMTPAuth   = true;
		$mail->Username = 'simponi@pii.or.id';
		$mail->Password = 'ndkgyyllfkzhbmzz';
		//$mail->SMTPDebug  = 2;
		//$mail->Debugoutput = function($str, $level) {echo "debug level $level; message: $str";};

		/*
		$mail->SMTPSecure = "tls";  // prefix for secure protocol to connect to the server
		$mail->Host       = "mail.pii.or.id";      // setting GMail as our SMTP server
		$mail->Port       = 587;                   // SMTP port to connect to GMail
		$mail->Username   = "updmember@pii.or.id";  // user email address
		$mail->Password   = "123456789";            // password in GMail
		$mail->SetFrom('updmember@pii.or.id', 'updmember');  //Who is sending the email
		*/

		/*
		$mail->SMTPOptions = array(
			'ssl' => array(
				'verify_peer' => false,
				'verify_peer_name' => false,
				'allow_self_signed' => true
			)
		);
		 */

		$mail->AddEmbeddedImage(FCPATH . 'assets/images/atm_lain_1.png', 'img1');
		$mail->AddEmbeddedImage(FCPATH . 'assets/images/atm_lain_2.png', 'img2');

		$mail->SetFrom('simponi+noreply@pii.or.id', 'simponi');  //Who is sending the email
		$mail->AddReplyTo('simponi@pii.or.id', 'simponi');

		$mail->Subject    	= $subject;
		$mail->Body      	= $this->load->view('email/' . $type . '-html', $data, TRUE);
		$mail->AltBody    	= $this->load->view('email/' . $type . '-txt', $data, TRUE);

		foreach ($to_email as $v) {
			$mail->AddAddress($v);
		}

		foreach ($cc_email as $v) {
			$mail->AddCC($v);
		}
		//print_r($mail);

		try {
			if (!$mail->Send()) {
				echo "Error: " . $mail->ErrorInfo;
			}
		} catch (Exception $e) {
			echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
		}
	}


	/**
	 * ER: Resend email
	 * @param id - `id` of `user_transfer`
	 */
	public function re_send()
	{
		$this->load->model('main_mod');
		$array = array($this->input->get('id'));

		foreach ($array as $val) {
			$checkx = $this->main_mod->msrwhere('user_transfer', array('id' => $val), 'id', 'desc')->result();

			if (isset($checkx[0])) {
				//if($checkx[0]->is_upload_mandiri == '0'){
				//SEND Mailer
				//$this->send_mail_va($checkx[0]->user_id, $checkx[0]->pay_type, $checkx[0]->sukarelatotal);
				//echo $checkx[0]->user_id.' - '.$checkx[0]->pay_type.' - '.$checkx[0]->sukarelatotal.'<br />';

				$user_id = $checkx[0]->user_id;
				$pay_type = $checkx[0]->pay_type;
				$sukarelatotal = $checkx[0]->sukarelatotal;


				$users = $this->main_mod->msrwhere('users', array('id' => $user_id), 'id', 'desc')->result();
				$user_profiles = $this->main_mod->msrwhere('user_profiles', array('user_id' => $user_id), 'id', 'desc')->result();
				//print_r($bk);
				if (isset($users[0])) {
					if ($users[0]->email != '') {
						$to_email = array();
						$cc_email = array();
						$to_email[] = $users[0]->email;

						$date_modif = $checkx[0]->modifieddate;

						$subject = "Informasi Pembayaran Untuk " . PAY_TYPES[$pay_type];

						$data['masa_berlaku'] = format_hari_tanggal(
							date('Y-m-d H:i:s', strtotime($date_modif . ' + 1 days'))
						);
						$data['total'] = $sukarelatotal;
						$data['va'] = $user_profiles[0]->va;
						$data['nama'] = $user_profiles[0]->firstname . ' ' . $user_profiles[0]->lastname;
						$data['site_name'] = $this->config->item('website_name', 'tank_auth');
						$this->_send_email_va('va', $subject, $to_email, $cc_email, $data);

						//echo $data['va'].' - '.$data['masa_berlaku'].' - '.$checkx[0]->sukarelatotal.'<br />';

					}
				}
				//}
			}
		}
	}

	public function test_email()
	{
		$this->load->library('MyPHPMailer');
		$mail = new PHPMailer();
		//$mail->IsSMTP(true); // we are going to use SMTP
		$mail->SMTPAuth   = true; // enabled SMTP authentication

		$mail->SMTPSecure = "ssl";  // prefix for secure protocol to connect to the server
		$mail->Host       = "smtp.gmail.com";      // setting GMail as our SMTP server
		$mail->Port       = 465;                   // SMTP port to connect to GMail
		$mail->Username   = "updmember@gmail.com";  // user email address
		$mail->Password   = "serimpi37!1";            // password in GMail
		$mail->SetFrom('updmember@pii.or.id', 'updmember');  //Who is sending the email
		$mail->AddReplyTo('updmember@pii.or.id', 'updmember');

		$mail->SMTPOptions = array(
			'ssl' => array(
				'verify_peer' => false,
				'verify_peer_name' => false,
				'allow_self_signed' => true
			)
		);

		$mail->SetFrom('updmember@pii.or.id', 'updmember');  //Who is sending the email
		$mail->Subject    	= 'test pii';
		$mail->Body      	= 'test';
		$mail->AltBody    	= 'test';

		//foreach($to_email as $v){
		$mail->AddAddress('d.angger@yahoo.com');
		//}

		//foreach($cc_email as $v){
		//$mail->AddCC($v);
		//}

		try {
			if (!$mail->Send()) {
				echo "Error: " . $mail->ErrorInfo;
			}
		} catch (Exception $e) {
			echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
		}
	}

	function download_kta_kolektif()
	{
		$akses = array("0");
		if (!in_array($this->session->userdata('type'), $akses)) {
			redirect('admin/dashboard');
			exit;
		}
		$this->load->model('main_mod');
		$tag = 'pln';
		//$tm = $this->main_mod->msrwhere('kta_download',array('tag'=>$tag,'is_download'=>'0','id<'=>'100'),'id','asc')->result();
		$tm = $this->main_mod->msrquery("SELECT * FROM kta_download WHERE tag='" . $tag . "' and is_download=0 order by id asc limit 500 ")->result();

		foreach ($tm as $v) {

			$id = $v->user_id;
			$members = $this->main_mod->msrwhere('members', array('person_id' => $id, 'status' => 1), 'id', 'desc')->result();
			$user_profiles = $this->main_mod->msrwhere('user_profiles', array('user_id' => $id), 'id', 'desc')->result();
			$users = $this->main_mod->msrwhere('users', array('id' => $id), 'id', 'desc')->row();

			if (isset($members[0]->no_kta) && isset($user_profiles[0]->firstname)) {
				if (((strtotime($members[0]->thru_date) >= strtotime(date('Y-m-d')) || $members[0]->thru_date == '0000-00-00' || substr($members[0]->thru_date, 0, 4) === '3000') && $users->username != '') || $this->session->userdata('type') == "0") {

					$name = trim(strtoupper($user_profiles[0]->firstname)) . " " . trim(strtoupper($user_profiles[0]->lastname));
					$photo = (($user_profiles[0]->photo != '') ? base_url() . 'assets/uploads/' . $user_profiles[0]->photo : "");
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


						if (strpos(strtolower($photo_cir), '.jpg') !== false) {
							$filename = FCPATH . './assets/uploads/' . str_replace("_", "\\_", str_replace(" ", "\\ ", $photo_cir));
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



					$fontname = TCPDF_FONTS::addTTFfont(FCPATH . './assets/fonts/CREDC___.ttf', 'credc', '', 96);
					$fontname2 = TCPDF_FONTS::addTTFfont(FCPATH . './assets/fonts/Montserrat-Bold.ttf', 'mont', '', 96);
					$pdf->SetFont($fontname2, '', 14, '', false);

					$tmp = '3px';
					$len = strlen($name);

					if ($len <= 60) $tmp = '11px';

					$temp_jenis = '';

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



					try {
						//Close and output PDF document
						$pdf->Output(FCPATH . 'assets/kolektif/' . $tag . '/' . $nim . '.pdf', 'F');
					} catch (Exception $e) {
						echo '</br> id:' . $id . ' error:' . $e->getMessage();
						$where = array(
							"tag" => $tag,
							"user_id" => $id
						);
						$row = array(
							'is_download' => 1,
							'remark' => $e->getMessage(),
						);
						$update = $this->main_mod->update('kta_download', $where, $row);
					} finally {
						//echo '</br> It is finally block, which always executes.';
						$where = array(
							"tag" => $tag,
							"user_id" => $id
						);
						$row = array(
							'is_download' => 1,
						);
						$update = $this->main_mod->update('kta_download', $where, $row);
					}
				}
			}
		}
	}

	function download_stri_kolektif()
	{
		if ($this->session->userdata('admin_id') == '') {
			redirect('auth/login');
		}

		$akses = array("0");
		if (!in_array($this->session->userdata('type'), $akses)) {
			$this->session->set_flashdata('error', $this->access_deny_msg());
			redirect('admin/dashboard');
			exit;
		}

		$this->load->model('main_mod');

		$tag = 'pln';
		//$tm = $this->main_mod->msrwhere('kta_download',array('tag'=>$tag,'is_download'=>'0','id<'=>'100'),'id','asc')->result();
		$tm = $this->main_mod->msrquery("SELECT * FROM stri_download WHERE tag='" . $tag . "' and is_download=0 order by id asc limit 300 ")->result();

		foreach ($tm as $v) {

			$id = $v->user_id;
			//$id = $this->uri->segment(4);
			//$id = $this->session->userdata('user_id');
			$members = $this->main_mod->msrwhere('members', array('person_id' => $id, 'status' => 1), 'id', 'desc')->result();
			$user_profiles = $this->main_mod->msrwhere('user_profiles', array('user_id' => $id), 'id', 'desc')->result();
			$stri = $this->main_mod->msrwhere('members_certificate', array('person_id' => $id, 'status' => 1), 'id', 'desc')->result();

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


					if ($stri[0]->certificate_type == "3" || $stri[0]->certificate_type == "2" || $stri[0]->certificate_type == "1") {
						$cek_ketum = $this->main_mod->msrquery('select ut.createddate from user_transfer ut join user_cert uc on ut.rel_id=uc.id join members_certificate mc on TRIM(LEADING "0" FROM mc.skip_id)=TRIM(LEADING "0" FROM uc.ip_seq) where mc.id=' . $stri[0]->id . ' and ut.user_id = ' . $id . ' order by ut.createddate desc limit 1')->row();

						if (isset($cek_ketum->createddate)) {
							if (strtotime($cek_ketum->createddate) <= strtotime('2021-12-18'))
								$tgl_penomoran = '2021-12-17';
							else
								$tgl_penomoran = $cek_ketum->createddate;
						} else $tgl_penomoran = '2021-12-17';
					}
				}

				$ketua = 'Dr. Ir. Danis Hidayat Sumadilaga, ST., M.Eng.Sc., IPU., ACPE.';
				$ketua = 'Dr. -Ing. Ir. Ilham Akbar Habibie, MBA., IPU.';

				$tgl_penomoran = $tgl_penomoran == '' ? $stri[0]->stri_from_date : $tgl_penomoran;
				if (strtotime($tgl_penomoran) <= strtotime('2021-12-18'))

					//	$ketua = 'Dr. Ir. HERU DEWANTO, ST., M.Sc.(Eng.), IPU., ASEAN Eng., ACPE.';
					$ketua = 'Dr. Ir. Danis Hidayat Sumadilaga, ST., M.Eng.Sc., IPU., ACPE.';

				//$nim = str_replace(".","-",$no_stri.'_'.str_pad($members[0]->no_kta,6,'0',STR_PAD_LEFT).'_'.$name.'_'.(isset($stri[0]->skip_sk)?$stri[0]->skip_sk:""));

				$nim = str_replace(".", "-", $no_stri . '_' . str_pad($members[0]->no_kta, 6, '0', STR_PAD_LEFT) . '_' . $name . '_' . (isset($stri[0]->stri_sk) ? $stri[0]->stri_sk : "") . '_' . (isset($stri[0]->stri_thru_date) ? $stri[0]->stri_thru_date : ""));

				//print_r($nim);



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
				//



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

					/*$img = new Imagick($photo);
			$img->stripImage();
			$img->writeImage($photo);
			$img->clear();
			$img->destroy();*/


					$photo = '<img class="img-fluid" style="text-align:right;padding:200;" height="70" width="70" src="' . $photo . '" title="">';
				}
				/*$fontname = TCPDF_FONTS::addTTFfont(FCPATH.'./assets/fonts/CREDC___.ttf', 'credc', '', 96);
		$fontname2 = TCPDF_FONTS::addTTFfont(FCPATH.'./assets/fonts/Montserrat-Bold.ttf', 'mont', '', 96);
		$pdf->SetFont($fontname2, '', 14, '', false);
		font-family:$fontname2*/

				//	$ttd_ketum = FCPATH.'./assets/images/tanda_tangan_ketum_baru.png';
				$ttd_ketum = FCPATH . './assets/images/Ketum-Ilham_1A.png';

				$flag_1 = '';
				$flag_2 = '60';

				if (strtotime($tgl_penomoran) <= strtotime('2021-12-18')) {
					//	$ttd_ketum = FCPATH.'./assets/images/tanda_tangan_ketum.png';
					$ttd_ketum = FCPATH . './assets/images/tanda_tangan_ketum_baru.png';
					$flag_1 = '<br />';
					$flag_2 = '50';
				}

				$tmp = '3px';
				$len = strlen($name);
				//$kualifikasi
				if ($len <= 60) $tmp = '11px';

				$html = <<<EOD
<p style="font-size:18px;"> </p>
<table width="100%" cellspacing="0" border="1" cellpadding="55%"><tr valign="bottom">
<td class="header1" align="center" valign="middle" width="70%"> </td>
<td class="header1" align="center" valign="middle" width="30%" style="font-weight:bold;"> $no_seri</td>
</tr></table>

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
	<tr><td width="30%">
		<span style="font-size:11px;">Ditetapkan di</span><br /><i style="font-size:9px;">Stated at</i></td><td width="3%">:</td><td width="67%"><p style="font-size:11px;text-align:left;font-weight:bold;">JAKARTA </p>
	</td></tr>
	<tr><td>
		<span style="font-size:11px;">Tanggal</span><br /><i style="font-size:9px;">Date</i></td><td>:</td><td><p style="font-size:11px;text-align:left;font-weight:bold;">$tgl_sk </p>
	</td></tr>

	<tr><td colspan="3"><p style="font-size:11px;text-align:center;font-weight:bold;">Persatuan Insinyur Indonesia<br />
		<span  style="font-size:8px;text-align:center;font-weight:bold;">THE INSTITUTION OF ENGINEERS INDONESIA</span>

		<br />$flag_1
		<img class="img-fluid" height="$flag_2" src="$ttd_ketum" title=""></p>
	</td></tr>

	</table>


	</td>
	<td class="header1" align="right" valign="middle"
	width="15%"> <img class="img-fluid" style="text-align:right;" height="70" width="70" src="$barcode" title=""></td>
	<td class="header1" align="left" valign="bottom"
		  width="15%"></td>
</tr>
</table>
<table width="100%" cellspacing="0" border="0" cellpadding="55%">
<tr valign="bottom">
	<td class="header1" align="center" valign="middle"
	width="8%"> </td>
	<td class="header1" align="center" valign="middle"
		  width="85%" style="font-weight:bold;font-size:11px;">$ketua<br />
		  <span style="font-size:11px;text-align:center;font-weight:normal;">Ketua Umum</span><br />
		  <i style="font-size:10px;text-align:center;font-weight:normal;">President</i>
		  </td>
	<td class="header1" align="center" valign="middle"
		  width="7%"> </td>
</tr>
</table>

EOD;
				$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
				//Close and output PDF document
				//print_r($html);
				//$pdf->Output($nim.'.pdf', 'D');



				try {
					//Close and output PDF document
					$pdf->Output(FCPATH . 'assets/kolektif/' . $tag . '/' . $nim . '.pdf', 'F');
				} catch (Exception $e) {
					echo '</br> id:' . $id . ' error:' . $e->getMessage();
					$where = array(
						"tag" => $tag,
						"user_id" => $id
					);
					$row = array(
						'is_download' => 1,
						'remark' => $e->getMessage(),
					);
					$update = $this->main_mod->update('stri_download', $where, $row);
				} finally {
					//echo '</br> It is finally block, which always executes.';
					$where = array(
						"tag" => $tag,
						"user_id" => $id
					);
					$row = array(
						'is_download' => 1,
					);
					$update = $this->main_mod->update('stri_download', $where, $row);
				}
			}
		}
	}

	function simpan_edit_estri()
	{

		$id       = $_POST['id_id'];
		$kta      = $_POST['id_kta'];
		$nama     = $_POST['id_nama']; //print_r($nama);  exit() ;

		$data_update_stri = [
			'add_name'   => $nama

		];

		$data['id_table'] = $id;
		$data['kta'] = $kta;
		$data['nama'] = $nama;
		$this->load->view('admin/Tes', $data);


		//	     $this->load_model('simpan_model') ;	
		//	      $this->simpan_model->update_data_stri($id, $data_update_stri);

	} // End Of Simpan_edit_stri 

	function lapkeu()
	{

		$this->load->model('Members_model');
		$data_lapkeu = $this->members_model->get_user_transfer();
		$data['data_lapkeu'] = $lapkeu;
		$this->load->view('admin/lapkeu_view', $data);
	} // end of lapkeu

}
