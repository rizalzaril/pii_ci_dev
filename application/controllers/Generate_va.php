<?php
defined('BASEPATH') or exit('No direct script access allowed');


class Generate_va extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();

		$this->load->model('Generate_va_model');
		$this->load->library(['session', 'form_validation']);
	}

	public function index()
	{
		$data['members_non_va'] = $this->Generate_va_model->get_members_non_va();

		if (empty($data['members_non_va'])) {
			echo 'Data Member yg tidak memiliki VA tidak ada!';
			return; // hentikan proses agar tidak load view kosong
		}


		$this->load->view('members/Vmember_non_va', $data);
	}


	// ✅ Halaman Edit VA
	public function edit_va($user_id)
	{
		$data['member'] = $this->Generate_va_model->get_member_by_id($user_id);

		if (!$data['member']) {
			show_404();
		}

		$this->load->view('header');
		$this->load->view('members/Vedit_va', $data);
		$this->load->view('footer');
	}

	public function update_va()
	{
		$user_id = $this->input->post('user_id');
		$va      = $this->input->post('va');

		// Debug data yang diterima
		// var_dump([
		// 	'user_id' => $user_id,
		// 	'va'      => $va
		// ]);
		// // Hentikan proses supaya jelas output var_dump
		// die();

		if ($user_id && $va) {
			$this->db->where('user_id', $user_id);
			$this->db->update('user_profiles', ['va' => $va]);

			echo json_encode([
				'status'  => 'success',
				'message' => 'VA berhasil diperbarui.'
			]);
		} else {
			echo json_encode([
				'status'  => 'error',
				'message' => 'Data tidak valid.'
			]);
		}
	}


	//UPDATE VA SECARA MASSAL
	public function update_bulk_va()
	{
		$members = $this->input->post('members'); // array of user_id + va

		if (empty($members)) {
			echo json_encode([
				'status' => 'error',
				'message' => 'Tidak ada data dipilih.'
			]);
			return;
		}

		foreach ($members as $row) {
			$this->Generate_va_model->update_va($row['user_id'], $row['va']);
		}

		echo json_encode([
			'status'  => 'success',
			'message' => count($members) . ' VA berhasil diperbarui.'
		]);
	}
}
