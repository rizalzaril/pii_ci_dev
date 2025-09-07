<?php


defined('BASEPATH') or exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;

class Acpe extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();

		$this->load->model('Acpe_model');
		$this->load->library(['session', 'form_validation']);
	}


	public function index()
	{
		// var_dump($this->get_aer());
		// exit;
		$this->load->view('header');
		$this->load->view('Vacpe');
		$this->load->view('footer');
	}



	// public function get_acpe()
	// {
	// 	$draw       = intval($this->input->get("draw"));
	// 	$start      = intval($this->input->get("start"));
	// 	$length     = intval($this->input->get("length"));
	// 	$order_col  = $this->input->get("order_by");
	// 	$order_dir  = $this->input->get("order_dir");

	// 	// $search     = $this->input->get("search")['value'];
	// 	$start_date = $this->input->get("start_date");
	// 	$end_date   = $this->input->get("end_date");

	// 	$data_acpe = $this->Acpe_model->get_acpe(
	// 		$start,
	// 		$length,
	// 		// $search,
	// 		$order_col,
	// 		$order_dir,
	// 		$start_date,
	// 		$end_date
	// 	);

	// 	$total    = $this->Acpe_model->count_all();
	// 	$filtered = $this->Acpe_model->count_filtered($start_date, $end_date);

	// 	$data = [];
	// 	$no = $start + 1;

	// 	foreach ($data_acpe as $acpe) {
	// 		$actionButtons = '
	//           <a href="' . base_url('users/get_user_detail/' . $acpe->id) . '" class="btn btn-sm btn-dark">
	//               <i class="fa fa-eye"></i>
	//           </a>
	//           <a href="' . base_url('users/edit/' . $acpe->id) . '" class="btn btn-sm btn-warning">
	//               <i class="fa fa-edit"></i>
	//           </a>
	//       ';



	// 		$data[] = [
	// 			// '<input type="checkbox" class="row_checkbox" value="' . $aer->id . '">',
	// 			$no++,
	// 			$acpe->no_acpe,
	// 			$acpe->doi,
	// 			$acpe->nama,
	// 			$acpe->kta,
	// 			$acpe->new_pe_no,
	// 			$acpe->bk_acpe,
	// 			$acpe->asosiasi_prof,
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

	public function get_acpe()
	{
		$draw       = intval($this->input->get("draw"));
		$start      = intval($this->input->get("start"));
		$length     = intval($this->input->get("length"));
		$order_col  = $this->input->get("order_by") ?? 'id';
		$order_dir  = $this->input->get("order_dir") ?? 'DESC';

		$start_date = $this->input->get("start_date");
		$end_date   = $this->input->get("end_date");

		$data_acpe = $this->Acpe_model->get_acpe(
			$start,
			$length,
			$order_col,
			$order_dir,
			$start_date,
			$end_date
		);

		$total    = $this->Acpe_model->count_all();
		$filtered = $this->Acpe_model->count_filtered($start_date, $end_date);

		$data = [];
		$no = $start + 1;

		if ($data_acpe) {
			foreach ($data_acpe as $acpe) {
				$actionButtons = '
                <a href="' . base_url('users/get_user_detail/' . $acpe->Id) . '" class="btn btn-sm btn-dark">
                    <i class="fa fa-eye"></i>
                </a>
                <a href="' . base_url('users/edit/' . $acpe->Id) . '" class="btn btn-sm btn-warning">
                    <i class="fa fa-edit"></i>
                </a>
            ';

				$data[] = [
					$no++,
					$acpe->no_acpe,
					$acpe->doi,
					$acpe->nama,
					$acpe->kta,
					$acpe->new_pe_no,
					$acpe->bk_acpe,
					$acpe->asosiasi_prof,
					$actionButtons
				];
			}
		}

		header('Content-Type: application/json');
		echo json_encode([
			"draw" => $draw,
			"recordsTotal" => $total,
			"recordsFiltered" => $filtered,
			"data" => $data
		]);
		exit;
	}
}
