<?php


defined('BASEPATH') or exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;

class Aer extends CI_Controller
{

  public function __construct()
  {
    parent::__construct();

    $this->load->model('Aer_model');
    $this->load->library(['session', 'form_validation']);
  }


  public function index()
  {
    // var_dump($this->get_aer());
    // exit;
    $this->load->view('header');
    $this->load->view('Vaer');
    $this->load->view('footer');
  }


  //////////////////////////////////IMPORT UNTUK DATA AER //////////////////////////////////////
  public function import_aer_proccess()
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


  public function get_aer()
  {
    $draw       = intval($this->input->get("draw"));
    $start      = intval($this->input->get("start"));
    $length     = intval($this->input->get("length"));
    $order_col  = $this->input->get("order_by");
    $order_dir  = $this->input->get("order_dir");

    $search     = $this->input->get("search")['value'];
    $start_date = $this->input->get("start_date");
    $end_date   = $this->input->get("end_date");

    $data_aer = $this->Aer_model->get_aer(
      $start,
      $length,
      $search,
      $order_col,
      $order_dir,
      $start_date,
      $end_date
    );

    $total    = $this->Aer_model->count_all();
    $filtered = $this->Aer_model->count_filtered($search, $start_date, $end_date);

    $data = [];
    $no = $start + 1;

    foreach ($data_aer as $aer) {
      $actionButtons = '
            <a href="' . base_url('users/get_user_detail/' . $aer->id) . '" class="btn btn-sm btn-dark">
                <i class="fa fa-eye"></i>
            </a>
            <a href="' . base_url('users/edit/' . $aer->id) . '" class="btn btn-sm btn-warning">
                <i class="fa fa-edit"></i>
            </a>
        ';

      // cek apakah url_aer ada isinya
      if (!empty($aer->url_aer)) {
        $url_aer = '<a href="' . $aer->url_aer . '" target="_blank" class="btn btn-sm btn-info">
                        <i class="fa fa-file"></i> Lihat File
                    </a>';
      } else {
        $url_aer = '<span class="text-danger">File tidak tersedia</span>';
      }

      $data[] = [
        '<input type="checkbox" class="row_checkbox" value="' . $aer->id . '">',
        $no++,
        $aer->no_aer,
        $aer->nama,
        $aer->grade,
        $aer->kta,
        // $aer->doi,
        // $url_aer,
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
}
