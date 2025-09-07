<?php


defined('BASEPATH') or exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;

class Import extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();

		$this->load->model('Pii_Model');
		$this->load->library(['session', 'form_validation']);
	}


	public function index()
	{
		$this->load->view('header');
		$this->load->view('import_view');
		$this->load->view('footer');
	}


	//////////////////////////////////IMPORT UNTUK DATA ITS //////////////////////////////////////
	public function import_proccess()
	{
		// Konfigurasi upload
		$config = [
			'upload_path'   => './uploads/excel_import/',
			'allowed_types' => 'xlsx|xls|csv',
			'max_size'      => 2048,
			'file_name'     => 'excel_import_' . time()
		];

		$this->load->library('upload', $config);

		if (!$this->upload->do_upload('excel_file')) {
			$this->session->set_flashdata('error', $this->upload->display_errors());
			redirect('/dashboard/acpe');
			return;
		}

		$uploadedFile = $this->upload->data();

		try {
			// Load spreadsheet
			$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($uploadedFile['full_path']);
			$sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

			$kodkel = $this->input->post('kodkel', true);
			$passwordDefault = $this->input->post('password', true) ?: '123';

			// Ambil semua email existing dari DB
			$existingEmails = $this->db->select('email')->get('users')->result_array();
			$existingEmails = array_column($existingEmails, 'email');

			$duplicateEmails = [];

			//cek last_id dari table users
			$last_id_users = $this->Pii_Model->cek_next_id_users();


			foreach ($sheetData as $rowIndex => $row) {
				if ($rowIndex === 1) continue; // Skip header
				if (empty(array_filter($row))) continue; // Skip baris kosong

				$username_excel = ''; // Kolom username di Excel
				$email = trim($row['D']);

				// Skip jika email kosong
				if (!$email) continue;

				// Cek email duplikat
				if (in_array($email, $existingEmails)) {
					// Update flag is_duplicate di DB
					$this->db->where('email', $email)->update('users', ['is_duplicate' => 1]);

					// Simpan email & baris untuk pesan error
					$duplicateEmails[] = "Baris {$rowIndex}: {$email}";
					continue;
				}

				// Jika username ada isinya, skip insert
				if (!empty($username_excel)) {
					continue;
				}

				// Mapping gender
				$gender_excel = strtolower(trim($row['H']));
				if ($gender_excel === 'laki-laki') {
					$gender_db = 'Male';
				} elseif ($gender_excel === 'perempuan') {
					$gender_db = 'Female';
				} else {
					$gender_db = null;
				}

				// Format DOB
				$dob_cell = trim($row['L']);
				$dob_db = null;
				if (!empty($dob_cell)) {
					if (is_numeric($dob_cell)) {
						$dob_db = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dob_cell)->format('Y-m-d');
					} else {
						$dob_db = date('Y-m-d', strtotime($dob_cell));
					}
				}

				//Kalau id yang dimasukkan <= last_id_users, skip
				if (!empty($row['id']) && $row['id'] <= $last_id_users) {
					continue; //skip
				}


				// ===================== INSERT USERS =====================
				$data_users = [
					'id'           => $last_id_users++,
					'username'     => '',
					'email'        => $email,
					'password'     => password_hash($passwordDefault, PASSWORD_DEFAULT),
				];

				//debug data_users insert
				// echo '<pre>';
				// var_dump($data_users);
				// echo '</pre>';
				// exit;
				$this->Pii_Model->insert_from_import($data_users);

				// Ambil ID user yang baru dibuat
				$user_id = $this->db->insert_id();
				$mobilephone = trim($row['M']);

				// ===================== INSERT USER PROFILE =====================
				$data_profiles = [
					'id'               => $user_id,
					'user_id'          => $user_id,
					'firstname'        => trim($row['B']),
					'lastname'         => trim($row['C']),
					'gender'           => $gender_db,
					'idtype'           => 'Citizen',
					'idcard'           => trim($row['J']),
					'birthplace'       => trim($row['K']),
					'dob'              => $dob_db,
					'mobilephone'      => $mobilephone,
					'kolektif_name_id' => htmlspecialchars($kodkel),
					// 'photo'      			 => trim($row['V']),
					// 'idfile'      			 => trim($row['T']),
					// 'idfile'      			 => trim($row['T']),
				];
				$this->Pii_Model->insert_data_profiles($data_profiles);

				// ===================== INSERT USER ADDRESS =====================
				$data_address = [
					'user_id'     => $user_id,
					'addresstype' => 'Rumah',
					'address'     => trim($row['N']),
					'city'        => trim($row['O']),
					'province'    => trim($row['P']),
					'phone'       => $mobilephone,
					'zipcode'     => trim($row['Q']),
					'email'       => $email,
					'createddate' => date('Y-m-d h:i:s'),
				];
				$this->Pii_Model->insert_user_address($data_address);

				// Tambahkan email ke existingEmails supaya tidak duplikat di file yang sama
				$existingEmails[] = $email;
			}

			// Hapus file upload
			if (file_exists($uploadedFile['full_path'])) {
				unlink($uploadedFile['full_path']);
			}

			// Feedback hasil import
			if (!empty($duplicateEmails)) {
				$message = "Email berikut sudah terdaftar sebagai user aplikan:<br>" . implode('<br>', $duplicateEmails);
				$this->session->set_flashdata('error_import', $message);
			} else {
				$this->session->set_flashdata('success_import', '✅ Semua data berhasil diimpor.');
			}
		} catch (\Exception $e) {
			$this->session->set_flashdata('error', 'Gagal memproses file: ' . $e->getMessage());
		}

		redirect('/users');
	}


	///////////////////////////////////IMPORT SET NEW PASSWORD\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
	public function import_update_password()
	{
		// Ambil password dari form admin
		$passwordNew = $this->input->post('password', true);
		if (empty($passwordNew)) {
			$this->session->set_flashdata('error', 'Password baru wajib diisi.');
			redirect('/users');
			return;
		}

		// Konfigurasi upload
		$config = [
			'upload_path'   => './uploads/excel_import/',
			'allowed_types' => 'xlsx|xls|csv',
			'max_size'      => 2048,
			'file_name'     => 'update_password_' . time()
		];

		$this->load->library('upload', $config);

		if (!$this->upload->do_upload('excel_file')) {
			$this->session->set_flashdata('error', $this->upload->display_errors());
			redirect('/users');
			return;
		}

		$uploadedFile = $this->upload->data();

		try {
			// Load spreadsheet
			$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($uploadedFile['full_path']);
			$sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

			$updatedUsers = [];
			$notFoundUsers = [];

			foreach ($sheetData as $rowIndex => $row) {
				if ($rowIndex === 1) continue; // Skip header
				if (empty(array_filter($row))) continue; // Skip baris kosong

				$email = trim($row['D']); // Kolom A = email

				if (empty($email)) continue;

				// Cari user berdasarkan email
				$existingUser = $this->db->where('email', $email)->get('users')->row_array();

				if ($existingUser) {
					// Update password ke yang diinput admin
					$this->db->where('id', $existingUser['id'])
						->update('users', [
							'password' => password_hash($passwordNew, PASSWORD_DEFAULT)
						]);

					$updatedUsers[] = "Email {$email}";
				} else {
					$notFoundUsers[] = "Baris {$rowIndex}: {$email}";
				}
			}

			// Hapus file upload
			if (file_exists($uploadedFile['full_path'])) {
				unlink($uploadedFile['full_path']);
			}

			// Pesan feedback
			$msg = '';
			if (!empty($updatedUsers)) {
				$msg .= "✅ Password berhasil diperbarui untuk:<br>" . implode('<br>', $updatedUsers) . "<br><br>";
			}
			if (!empty($notFoundUsers)) {
				$msg .= "⚠ Email tidak ditemukan:<br>" . implode('<br>', $notFoundUsers);
			}

			$this->session->set_flashdata('success_import', $msg);
		} catch (\Exception $e) {
			$this->session->set_flashdata('error_import', 'Gagal memproses file: ' . $e->getMessage());
		}

		redirect('/users');
	}


	/////////////////////////////// IMPORT UNTUK DATA UGM \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
	public function import_proccess_ugm()
	{
		// Konfigurasi upload
		$config = [
			'upload_path'   => './uploads/excel_import/',
			'allowed_types' => 'xlsx|xls|csv',
			'max_size'      => 2048,
			'file_name'     => 'excel_import_' . time()
		];

		$this->load->library('upload', $config);

		if (!$this->upload->do_upload('excel_file')) {
			$this->session->set_flashdata('error', $this->upload->display_errors());
			redirect('/dashboard/acpe');
			return;
		}

		$uploadedFile = $this->upload->data();

		try {
			// Load spreadsheet
			$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($uploadedFile['full_path']);
			$sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

			$kodkel = $this->input->post('kodkel', true);
			$passwordDefault = $this->input->post('password', true) ?: '123';

			// Ambil semua email existing dari DB
			$existingEmails = $this->db->select('email')->get('users')->result_array();
			$existingEmails = array_column($existingEmails, 'email');

			$duplicateEmails = [];

			//cek last_id dari table users
			$last_id_users = $this->Pii_Model->cek_next_id_users();


			foreach ($sheetData as $rowIndex => $row) {
				if ($rowIndex === 1) continue; // Skip header
				if (empty(array_filter($row))) continue; // Skip baris kosong

				$username_excel = ''; // Kolom username di Excel
				$email = trim($row['D']);

				// Skip jika email kosong
				if (!$email) continue;

				// Cek email duplikat
				if (in_array($email, $existingEmails)) {
					// Update flag is_duplicate di DB
					$this->db->where('email', $email)->update('users', ['is_duplicate' => 1]);

					// Simpan email & baris untuk pesan error
					$duplicateEmails[] = "Baris {$rowIndex}: {$email}";
					continue;
				}

				// Jika username ada isinya, skip insert
				if (!empty($username_excel)) {
					continue;
				}

				// Mapping gender
				$gender_excel = strtolower(trim($row['H']));
				if ($gender_excel === 'laki-laki') {
					$gender_db = 'Male';
				} elseif ($gender_excel === 'perempuan') {
					$gender_db = 'Female';
				} else {
					$gender_db = null;
				}

				// Format DOB
				$dob_cell = trim($row['L']);
				$dob_db = null;
				if (!empty($dob_cell)) {
					if (is_numeric($dob_cell)) {
						$dob_db = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dob_cell)->format('Y-m-d');
					} else {
						$dob_db = date('Y-m-d', strtotime($dob_cell));
					}
				}

				//Kalau id yang dimasukkan <= last_id_users, skip
				if (!empty($row['id']) && $row['id'] <= $last_id_users) {
					continue; //skip
				}


				// ===================== INSERT USERS =====================
				$data_users = [
					'id'           => $last_id_users++,
					'username'     => '',
					'email'        => $email,
					'password'     => password_hash($passwordDefault, PASSWORD_DEFAULT),
				];

				//debug data_users insert
				// echo '<pre>';
				// var_dump($data_users);
				// echo '</pre>';
				// exit;
				$this->Pii_Model->insert_from_import($data_users);

				// Ambil ID user yang baru dibuat
				$user_id = $this->db->insert_id();
				$mobilephone = trim($row['M']);

				// ===================== INSERT USER PROFILE =====================
				$data_profiles = [
					'id'               => $user_id,
					'user_id'          => $user_id,
					'firstname'        => trim($row['B']),
					'lastname'         => trim($row['C']),
					'gender'           => $gender_db,
					'idtype'           => 'Citizen',
					'idcard'           => trim($row['J']),
					'birthplace'       => trim($row['K']),
					'dob'              => $dob_db,
					'mobilephone'      => $mobilephone,
					'kolektif_name_id' => htmlspecialchars($kodkel),
					// 'photo'      			 => trim($row['V']),
					// 'idfile'      			 => trim($row['T']),
					// 'idfile'      			 => trim($row['T']),
				];
				$this->Pii_Model->insert_data_profiles($data_profiles);

				// ===================== INSERT USER ADDRESS =====================
				$data_address = [
					'user_id'     => $user_id,
					'addresstype' => 'Rumah',
					'address'     => trim($row['N']),
					'city'        => trim($row['O']),
					'province'    => trim($row['P']),
					'phone'       => $mobilephone,
					'zipcode'     => trim($row['Q']),
					'email'       => $email,
					'createddate' => date('Y-m-d h:i:s'),
				];
				$this->Pii_Model->insert_user_address($data_address);

				// Tambahkan email ke existingEmails supaya tidak duplikat di file yang sama
				$existingEmails[] = $email;
			}

			// Hapus file upload
			if (file_exists($uploadedFile['full_path'])) {
				unlink($uploadedFile['full_path']);
			}

			// Feedback hasil import
			if (!empty($duplicateEmails)) {
				$message = "Email berikut sudah terdaftar sebagai user aplikan:<br>" . implode('<br>', $duplicateEmails);
				$this->session->set_flashdata('error_import', $message);
			} else {
				$this->session->set_flashdata('success_import', '✅ Semua data berhasil diimpor.');
			}
		} catch (\Exception $e) {
			$this->session->set_flashdata('error', 'Gagal memproses file: ' . $e->getMessage());
		}

		redirect('/users');
	}


	/////////////////////////////// IMPORT UNTUK DATA AER \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
	public function import_proccess_aer()
	{
		// Konfigurasi upload
		$config = [
			'upload_path'   => './uploads/excel_import/',
			'allowed_types' => 'xlsx|xls|csv',
			'max_size'      => 2048,
			'file_name'     => 'excel_import_' . time()
		];

		$this->load->library('upload', $config);

		if (!$this->upload->do_upload('excel_file')) {
			$this->session->set_flashdata('error', $this->upload->display_errors());
			redirect('/aer');
			return;
		}

		$uploadedFile = $this->upload->data();

		try {
			// Load spreadsheet
			$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($uploadedFile['full_path']);
			$sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

			$duplicateData = []; // simpan duplikat kta/no_aer

			foreach ($sheetData as $rowIndex => $row) {
				if ($rowIndex === 1) continue; // Skip header
				if (empty(array_filter($row))) continue; // Skip baris kosong

				$no_aer 	= trim($row['A']);
				$nama   	= trim($row['B']);
				$grade    	= trim($row['D']);
				$kta  	= trim($row['C']);
				$doi  		= trim($row['E']);
				$url_aer  = trim($row['F']);

				if (!$no_aer && !$kta) continue; // skip kalau kosong semua

				// 🔎 cek apakah no_aer atau kta sudah ada di DB
				// $exists = $this->db->where('no_aer', $no_aer)
				// 	->or_where('kta', $kta)
				// 	->get('aer')
				// 	->row();

				// if ($exists) {
				// 	$duplicateData[] = "Baris {$rowIndex}: no_aer = {$no_aer}, kta = {$kta} sudah ada di DB";
				// 	continue; // skip insert
				// }

				// ===================== INSERT AER =====================
				$data_aer = [
					'no_aer' 			=> $no_aer,
					'nama'   			=> $nama,
					'grade'  			=> $grade,
					'kta'    			=> $kta,
					'doi'    		 	=> $doi,
					'url_aer'    	=> $url_aer,
				];

				// var_dump($data_aer);
				// exit;

				$this->Pii_Model->insert_from_import_aer($data_aer);
			}

			// Hapus file upload
			if (file_exists($uploadedFile['full_path'])) {
				unlink($uploadedFile['full_path']);
			}

			// Feedback hasil import
			if (!empty($duplicateData)) {
				$message = "Data berikut sudah ada di database:<br>" . implode('<br>', $duplicateData);
				$this->session->set_flashdata('error_import', $message);
			} else {
				$this->session->set_flashdata('success_import', '✅ Semua data berhasil diimpor.');
			}
		} catch (\Exception $e) {
			$this->session->set_flashdata('error', 'Gagal memproses file: ' . $e->getMessage());
		}

		redirect('/aer');
	}



	/////////////////////////////// IMPORT UNTUK DATA ACPE \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
	public function import_proccess_acpe()
	{
		// Konfigurasi upload
		$config = [
			'upload_path'   => './uploads/excel_import/',
			'allowed_types' => 'xlsx|xls|csv',
			'max_size'      => 2048,
			'file_name'     => 'excel_import_' . time()
		];

		$this->load->library('upload', $config);

		if (!$this->upload->do_upload('excel_file')) {
			$this->session->set_flashdata('error', $this->upload->display_errors());
			redirect('/aer');
			return;
		}

		$uploadedFile = $this->upload->data();

		try {
			// Load spreadsheet
			$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($uploadedFile['full_path']);
			$sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

			$duplicateData = []; // simpan duplikat kta/no_acpe

			foreach ($sheetData as $rowIndex => $row) {
				if ($rowIndex === 1) continue; // Skip header
				if (empty(array_filter($row))) continue; // Skip baris kosong

				$no_acpe 		= trim($row['A']);
				$doi   			= trim($row['B']);
				$nama    		= trim($row['C']);
				$kta  			= trim($row['D']);
				$new_pe_no  = trim($row['E']);
				$bk_acpe  	= trim($row['F']);

				// if (!$no_acpe && !$kta) continue; // skip kalau kosong semua

				// 🔎 cek apakah no_aer atau kta sudah ada di DB
				// $exists = $this->db->where('no_aer', $no_aer)
				// 	->or_where('kta', $kta)
				// 	->get('aer')
				// 	->row();

				// if ($exists) {
				// 	$duplicateData[] = "Baris {$rowIndex}: no_aer = {$no_aer}, kta = {$kta} sudah ada di DB";
				// 	continue; // skip insert
				// }

				// ===================== INSERT ACPE =====================
				$data_acpe = [
					'no_acpe' 					=> $no_acpe,
					'doi'   						=> $doi,
					'nama'  						=> $nama,
					'kta'  							=> $kta,
					'new_pe_no'    			=> $new_pe_no,
					'bk_acpe'    		 		=> $doi,
					'asosiasi_prof'    	=> 'PII',
				];

				// var_dump($data_acpe);
				// exit;

				$this->Pii_Model->insert_from_import_acpe($data_acpe);
			}

			// Hapus file upload
			if (file_exists($uploadedFile['full_path'])) {
				unlink($uploadedFile['full_path']);
			}

			// Feedback hasil import
			if (!empty($duplicateData)) {
				$message = "Data berikut sudah ada di database:<br>" . implode('<br>', $duplicateData);
				$this->session->set_flashdata('error_import', $message);
			} else {
				$this->session->set_flashdata('success_import', '✅ Semua data berhasil diimpor.');
			}
		} catch (\Exception $e) {
			$this->session->set_flashdata('error', 'Gagal memproses file: ' . $e->getMessage());
		}

		redirect('/acpe');
	}
}
