<?php
defined('BASEPATH') or exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;


class User_profiles extends CI_Controller
{

  public function __construct()
  {
    parent::__construct();

    $this->load->model('User_profiles_model');
    $this->load->library(['session', 'form_validation']);
  }

  public function index()
  {
    // $data['users'] = $this->Users_model->find_all_users();

    $id = $this->session->userdata('id');

    $data['profile_data'] = $this->User_profiles_model->get_profile_by_id($id);

    // echo '<pre>';
    // print_r($data);
    // echo '</pre>';
    // exit;
    $this->load->view('header');
    $this->load->view('/Dashboard/Vprofile', $data);
    $this->load->view('footer');
  }


  public function get_user_profiles()
  {
    $draw = intval($this->input->get("draw"));
    $start = intval($this->input->get("start"));
    $length = intval($this->input->get("length"));

    $users = $this->Users_model->get_user_profiles($start, $length);
    $total = $this->Users_model->count_all();

    $data = [];
    $no = $start + 1;
    foreach ($users as $user) {
      $data[] = [
        $no++,
        $user->username,
        $user->email,
        $user->activated,
        '<a href="' . base_url('user/edit/' . $user->id) . '" class="btn btn-sm btn-primary"><i class="fa fa-edit"></i></a>
       <a href="#" class="btn btn-sm btn-danger btn-delete" data-id="' . $user->id . '"><i class="fa fa-trash"></i></a>'
      ];
    }

    $output = [
      "draw" => $draw,
      "recordsTotal" => $total,
      "recordsFiltered" => $total,
      "data" => $data
    ];

    // Untuk debug JSON
    header('Content-Type: application/json');
    echo json_encode($output, JSON_PRETTY_PRINT);
    exit;
  }


  /////////////////////////////////// PROFILE SETTINGS \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\

  public function update_info_pribadi($id)
  {
    $firstname = $this->input->post('firstname', true);
    $lastname = $this->input->post('lastname', true);
    $gender = $this->input->post('gender', true);
    $birthplace = $this->input->post('birthplace', true);
    $dob = $this->input->post('dob', true);
    $website = $this->input->post('website', true);
    $idtype = $this->input->post('idtype', true);

    $data = [
      'firstname'  => htmlspecialchars_decode($firstname),
      'lastname'   => htmlspecialchars($lastname),
      'gender'     => htmlspecialchars($gender),
      'birthplace' => htmlspecialchars($birthplace),
      'dob'        => htmlspecialchars($dob),
      'website'    => htmlspecialchars($website),
      'idtype'    => htmlspecialchars($idtype),
    ];

    // var_dump($data);
    // exit;

    $this->User_profiles_model->update_profiles($id, $data);
    $this->session->set_flashdata('success', 'Data berhasil disimpan!');
    redirect('/User_profiles/');
  }

  public function update_informasi_kontak($id)
  {
    $mobilephone = $this->input->post('mobilephone', true);

    $data = [
      'mobilephone' => htmlspecialchars($mobilephone),
    ];

    // var_dump($data);
    // exit;

    $this->User_profiles_model->update_profiles($id, $data);
    $this->session->set_flashdata('success', 'Data berhasil disimpan!');
    redirect('/User_profiles');
  }

  public function update_informasi_persyaratan($id)
  {
    $this->load->library('upload');

    $upload_path = './uploads/';
    $allowed_types = 'pdf|jpg|jpeg|png|doc|docx';
    $max_size = 2048; // 2mb

    $fields = [
      'id_file',
      'sertifikat_legal',
      'tanda_bukti',
      'surat_dukungan',
      'surat_pernyataan',
      'surat_ijin_domisili'
    ];

    $data = [];

    // Loop untuk menangani semua file
    foreach ($fields as $field_name) {
      if (!empty($_FILES[$field_name]['name'])) {
        $config['upload_path'] = $upload_path;
        $config['allowed_types'] = $allowed_types;
        $config['max_size'] = $max_size;
        $config['file_name'] = time() . '_' . $_FILES[$field_name]['name']; // Rename file
        $this->upload->initialize($config);

        if ($this->upload->do_upload($field_name)) {
          $uploaded = $this->upload->data();
          $data[$field_name] = $uploaded['file_name'];
        } else {
          $this->session->set_flashdata('error', "Gagal mengupload file " . $field_name . ": " . $this->upload->display_errors('', ''));
          redirect('user_profiles');
          return;
        }
      }
    }

    // Simpan data ke database
    $this->User_profiles_model->update_profiles($id, $data);
    $this->session->set_flashdata('success', 'Data berhasil disimpan!');
    redirect('user_profiles');
  }


  public function import_excel($user_id)
  {
    $config['upload_path']   = './uploads/';
    $config['allowed_types'] = 'xlsx|xls|csv';
    $config['max_size']      = 2048;
    $config['file_name']     = 'excel_import_' . time();

    $this->load->library('upload', $config);

    if (!$this->upload->do_upload('excel_file')) {
      $this->session->set_flashdata('error', $this->upload->display_errors());
      redirect('user_profiles');
    }

    $uploadedFile = $this->upload->data();
    $spreadsheet = IOFactory::load($uploadedFile['full_path']);
    $sheet = $spreadsheet->getActiveSheet()->toArray();

    // Ambil baris ke-2 (indeks 1)s
    $row = $sheet[1];

    $data = [
      'firstname'   => $row[0] ?? '',
      'lastname'    => $row[1] ?? '',
      'gender'      => $row[2] ?? '',
      'birthplace'  => $row[3] ?? '',
      'dob'         => $row[4] ?? '',
      'idcard'      => $row[5] ?? '',
      'website'     => $row[6] ?? '',
      'mobilephone' => $row[7] ?? '',
    ];

    $this->User_profiles_model->update_profile_from_excel($user_id, $data);

    $this->session->set_flashdata('success', '✅ Data berhasil diimpor dari Excel.');
    redirect('user_profiles');
  }
}
