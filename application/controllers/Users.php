<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Users extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();

		$this->load->model('Users_model');
		$this->load->model('Pii_Model');
		$this->load->library(['session', 'form_validation']);
	}

	public function index()
	{
		// $data['users'] = $this->Users_model->find_all_users();

		$data_kelompok['list_kelompok'] = $this->Pii_Model->get_data_kelompok();

		//Jika data kelompok 0 maka tampilkan pesan
		if (!$data_kelompok) {
			echo 'Data Kelompok kosong';
		}

		//cek last_id dari table users
		// $last_id = $this->Pii_Model->cek_next_id_users();
		// echo '<pre>';
		// var_dump('NEXT ID USERS:' . $last_id);
		// echo '</pre>';
		// exit;

		// echo '<pre>';
		// var_dump($data_kelompok);
		// echo '</pre>';
		// exit;

		$this->load->view('header');
		$this->load->view('/Users/Vusers', $data_kelompok);
		$this->load->view('footer');
	}



	// Detail Aer berdasarkan KTA
	public function detail_aer($kta)
	{
		$data['detail_aer'] = $this->Users_model->get_detail_aer($kta);

		// echo '<pre>';
		// var_dump($data);
		// echo '</pre>';
		// exit;

		if (!$data['detail_aer']) {
			show_404(); // Jika data tidak ditemukan
		}

		$this->load->view('header'); // opsional
		$this->load->view('aer_details_view', $data);
		$this->load->view('footer'); // opsional
	}


	// Detail APEC berdasarkan KTA
	public function detail_apec($kta)
	{
		$data['detail_apec'] = $this->Users_model->get_detail_apec($kta);

		// echo '<pre>';
		// var_dump($data);
		// echo '</pre>';
		// exit;

		if (!$data['detail_apec']) {
			show_404(); // Jika data tidak ditemukan
		}

		$this->load->view('header'); // opsional
		$this->load->view('apec_details_view', $data);
		$this->load->view('footer'); // opsional
	}


	// Detail ACPE berdasarkan KTA
	public function detail_acpe($kta)
	{
		$data['detail_acpe'] = $this->Users_model->get_detail_acpe($kta);

		// echo '<pre>';
		// var_dump($data);
		// echo '</pre>';
		// exit;

		if (!$data['detail_acpe']) {
			show_404(); // Jika data tidak ditemukan
		}

		$this->load->view('header'); // 

		$this->load->view('acpe_details_view', $data);
		$this->load->view('footer'); // opsional
	}


	// public function details($kta = '')
	// {
	// 	// if ($this->session->userdata('type') == "7") {
	// 	// 	$this->session->set_flashdata('error', $this->access_deny_msg());
	// 	// 	redirect('admin/dashboard');
	// 	// 	exit;
	// 	// }

	// 	$this->load->model('main_mod');
	// 	if (empty($kta)) {
	// 		$this->session->set_flashdata('error', 'Input member id is required.');
	// 		redirect('admin/members');
	// 		exit;
	// 	}
	// 	$data['title'] = ''; //SITE_NAME.': Member Details';
	// 	$data['msg'] = '';
	// 	$obj_row = $this->Users_model->get_aer_by_kta($kta);
	// 	if ($obj_row) {
	// 		$data['row'] = $obj_row;


	// 		$data['user_address'] = $this->main_mod->msrwhere('user_address', array('user_id' => $kta, 'status' => 1), 'id', 'asc')->result();
	// 		$data['user_email'] = $this->main_mod->msrwhere('contacts', array('user_id' => $kta, 'contact_type like "%_email%"' => null, 'status' => 1), 'id', 'asc')->result();
	// 		$data['user_phone'] = $this->main_mod->msrwhere('contacts', array('user_id' => $kta, 'contact_type like "%_phone%"' => null, 'status' => 1), 'id', 'asc')->result();
	// 		$data['user_edu'] = $this->main_mod->msrwhere('user_edu', array('user_id' => $kta, 'status' => 1), 'id', 'asc')->result();
	// 		$data['user_cert'] = $this->main_mod->msrwhere('user_cert', array('user_id' => $kta, 'status' => 1), 'id', 'asc')->result();
	// 		$data['user_exp'] = $this->main_mod->msrwhere('user_exp', array('user_id' => $kta, 'status' => 1), 'id', 'asc')->result();
	// 		$data['emailx'] = $obj_row->email;

	// 		$data['m_phone'] = $this->main_mod->msrwhere('m_param', array('code' => 'phone'), 'id', 'asc')->result();
	// 		$data['m_email'] = $this->main_mod->msrwhere('m_param', array('code' => 'email'), 'id', 'asc')->result();
	// 		$data['m_address'] = $this->main_mod->msrwhere('m_param', array('code' => 'address'), 'id', 'asc')->result();
	// 		$data['m_degree'] = $this->main_mod->msrwhere('education_type', null, 'EDUCATION_TYPE_ID', 'asc')->result();

	// 		echo '<pre>';
	// 		var_dump($data);
	// 		echo '</pre>';
	// 		exit;

	// 		$this->load->view('aer_details_view', $data);
	// 		return;
	// 	}
	// 	redirect('aer_details_view');
	// 	exit;
	// }






	//get users with ajax
	//get users with ajax
	// public function get_users()
	// {
	// 	$draw         = intval($this->input->get("draw"));
	// 	$start        = intval($this->input->get("start"));
	// 	$length       = intval($this->input->get("length"));
	// 	$search       = $this->input->get("search")['value'];
	// 	$order_col    = $this->input->get("order_by");
	// 	$order_dir    = $this->input->get("order_dir");
	// 	$is_duplicate = $this->input->get("is_duplicate"); // tambahan filter

	// 	// Ambil data dari model dengan filter duplicate
	// 	$users    = $this->Users_model->get_users($start, $length, $search, $order_col, $order_dir, $is_duplicate);
	// 	$total    = $this->Users_model->count_all();
	// 	$filtered = $this->Users_model->count_filtered($search, $is_duplicate);

	// 	$data = [];
	// 	$no = $start + 1;

	// 	foreach ($users as $user) {
	// 		$existsInUsers = $this->db
	// 			->get_where('users', ['email' => $user->email])
	// 			->num_rows() > 0;

	// 		$emailDisplay = $existsInUsers
	// 			? '<span class="text text-danger fw-bold">' . $user->email . '</span>'
	// 			: $user->email;

	// 		$duplicateBadge = $existsInUsers
	// 			? '<span class="badge bg-danger">Cannot Import <i class="fa fa-times"></i></span>'
	// 			: '<span class="badge bg-success">Ready to Import <i class="fa fa-check"></i></span>';

	// 		$actionButtons = '
	//           <a href="' . base_url('users/get_user_detail/' . $user->id) . '" class="btn btn-sm btn-dark">
	//               <i class="fa fa-eye"></i>
	//           </a>
	//           <a href="' . base_url('users/edit/' . $user->id) . '" class="btn btn-sm btn-warning">
	//               <i class="fa fa-edit"></i>
	//           </a>
	//       ';

	// 		// ✅ Jika filter duplicate diaktifkan, tampilkan hanya yang sesuai
	// 		if ($is_duplicate !== null && $is_duplicate !== '') {
	// 			if ($is_duplicate == '1' && !$existsInUsers) {
	// 				continue; // hanya duplicate yang ditampilkan
	// 			}
	// 			if ($is_duplicate == '0' && $existsInUsers) {
	// 				continue; // hanya yang tidak duplicate yang ditampilkan
	// 			}
	// 		}

	// 		$data[] = [
	// 			'<input type="checkbox" class="row_checkbox" value="' . $user->id . '">',
	// 			$no++,
	// 			$user->id,
	// 			$user->username,
	// 			$emailDisplay,
	// 			$duplicateBadge,
	// 			$user->created,
	// 			$actionButtons
	// 		];
	// 	}

	// 	echo json_encode([
	// 		"draw" => $draw,
	// 		"recordsTotal" => $total,
	// 		"recordsFiltered" => $filtered,
	// 		"data" => $data
	// 	]);
	// }

	public function get_users()
	{
		$draw         = intval($this->input->get("draw"));
		$start        = intval($this->input->get("start"));
		$length       = intval($this->input->get("length"));
		$search       = $this->input->get("search")['value'];
		$order_col    = $this->input->get("order_by");
		$order_dir    = $this->input->get("order_dir");
		$is_duplicate = $this->input->get("is_duplicate");

		// ✅ Ambil filter date dari request
		$start_date   = $this->input->get("start_date");
		$end_date     = $this->input->get("end_date");

		// Ambil data dari model dengan filter duplicate + date
		$users    = $this->Users_model->get_users($start, $length, $search, $order_col, $order_dir, $is_duplicate, $start_date, $end_date);
		$total    = $this->Users_model->count_all();
		$filtered = $this->Users_model->count_filtered($search, $is_duplicate, $start_date, $end_date);

		$data = [];
		$no = $start + 1;

		foreach ($users as $user) {
			$existsInUsers = $this->db
				->get_where('users', ['email' => $user->email])
				->num_rows() > 0;

			$emailDisplay = $existsInUsers
				? '<span class="text text-danger fw-bold">' . $user->email . '</span>'
				: $user->email;

			$duplicateBadge = $existsInUsers
				? '<span class="badge bg-danger">Cannot Import <i class="fa fa-times"></i></span>'
				: '<span class="badge bg-success">Ready to Import <i class="fa fa-check"></i></span>';

			$actionButtons = '
            <a href="' . base_url('users/get_user_detail/' . $user->id) . '" class="btn btn-sm btn-dark">
                <i class="fa fa-eye"></i>
            </a>
            <a href="' . base_url('users/edit/' . $user->id) . '" class="btn btn-sm btn-warning">
                <i class="fa fa-edit"></i>
            </a>
        ';

			if ($is_duplicate !== null && $is_duplicate !== '') {
				if ($is_duplicate == '1' && !$existsInUsers) {
					continue;
				}
				if ($is_duplicate == '0' && $existsInUsers) {
					continue;
				}
			}

			$data[] = [
				'<input type="checkbox" class="row_checkbox" value="' . $user->id . '">',
				$no++,
				$user->id,
				$user->username,
				$emailDisplay,
				$duplicateBadge,
				$user->created,
				$actionButtons
			];
		}

		echo json_encode([
			"draw" => $draw,
			"recordsTotal" => $total,
			"recordsFiltered" => $filtered,
			"data" => $data
		]);
	}




	public function delete_selected_dummy_users()
	{
		$ids = $this->input->post('ids');

		if (!empty($ids)) {
			// Hapus dari tabel dummy_users
			$this->db->where_in('id', $ids)->delete('users');

			//Hapus dari tabel dummy_user_profiles
			$this->db->where_in('user_id', $ids)->delete('user_profiles');

			//Hapus dari tabel dummy_user_address
			$this->db->where_in('user_id', $ids)->delete('user_address');
		}

		echo json_encode(['Status' => 'Delete selected success!']);
	}


	public function delete_all_dummy_users()
	{
		// Kosongkan tabel dummy_users
		// $data = $this->db->empty_table('users');
		// var_dump($data);
		// exit;

		// Kosongkan tabel dummy_user_profiles
		// $this->db->empty_table('user_profiles');

		// Kosongkan tabel dummy_user_address
		// $this->db->empty_table('user_address');

		echo json_encode(['Status' => 'Delete success!']);
	}





	public function get_user_detail($id)
	{
		$data['user_detail'] = $this->Users_model->get_user_detail($id);
		// echo '<pre>';
		// print_r($data);
		// echo '</pre>';

		$this->load->view('Users/Vuser_profiles', $data);
	}
}
