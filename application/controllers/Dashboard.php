<?php
defined('BASEPATH') or exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;

class Dashboard extends CI_Controller
{
  public function __construct()
  {
    parent::__construct();

    $this->load->model('Pii_Model');
    $this->load->library(['session', 'form_validation']);
  }


  public function cek_db()
  {
    //cek koneksi ke db

    $query = $this->db->query('SELECT DATABASE() AS db');

    //cek hasil query
    if ($query->num_rows() > 0) {
      $row = $query->row();
      echo "Database anda berhasil terkoneksi";
      echo 'Database anda adalah ' . $row->db;
    } else {
      echo 'Database tidak terkoneksi, silahkan cek kembali!';
    }
  }


  public function list_data()
  {

    $data['list_data'] = $this->Pii_Model->find_all();

    $this->load->view('header');
    $this->load->view('Dashboard/list_persen_bagi', $data);
    $this->load->view('footer');
  }

  public function add_data()
  {
    $kode_terakhir = $this->Pii_Model->cek_kode(); // contoh hasil: B01-0001

    // Ambil angka urutan terakhir dari kode (misal: '0001')
    $no_urut = (int) substr($kode_terakhir, 2, 2); // mulai dari index 4, ambil 4 digit
    $new_urut = $no_urut + 1;

    // Format ulang dengan leading zero 4 digit (hasil: '0002')
    $format_urut = str_pad($new_urut, 0, '0', STR_PAD_LEFT);

    // Gabungkan dengan awalan kode (misal: 'B01-')
    $kode_baru = 'B0' . $format_urut;

    $data = array('kode' => $kode_baru);

    $this->load->view('header');
    $this->load->view('Dashboard/add_data', $data);
    $this->load->view('footer');
  }

  public function store_data()
  {

    $this->form_validation->set_rules('kode', 'Kode', 'required|trim');
    $this->form_validation->set_rules('keterangan', 'Keterangan', 'required|trim');
    $this->form_validation->set_rules('persen', 'Persen', 'required|trim');
    $this->form_validation->set_rules('nilai_awal', 'Nilai awal', 'required|trim');

    if ($this->form_validation->run() == false) {
      return redirect('/dashboard/add_data');
    } else {
      $kode = $this->input->post('kode', true);
      $keterangan = $this->input->post('keterangan', true);
      $persen = $this->input->post('persen', true);
      $nilai_awal = $this->input->post('nilai_awal', true);


      $data = [
        'kode' => htmlspecialchars($kode),
        'keter' => htmlspecialchars_decode($keterangan),
        'nilai_awal' => htmlspecialchars_decode($nilai_awal),
        'persen' => htmlspecialchars($persen),
      ];

      // var_dump($data);
      // exit;

      $this->Pii_Model->save_data($data);

      $this->session->set_flashdata('success_save', 'Data berhasil disimpan.');
      redirect('/dashboard/add_data');
    }
  }


  public function edit_data($id)
  {

    $data['row'] = $this->Pii_Model->get_by_id($id);

    $this->load->view('header');
    $this->load->view('Dashboard/edit_data', $data);
    $this->load->view('footer');
  }


  public function update_data($id)
  {

    $this->form_validation->set_rules('keterangan', 'Keterangan', 'required|trim');
    $this->form_validation->set_rules('persen', 'Persen', 'required|numeric');
    $this->form_validation->set_rules('nilai_awal', 'Persen', 'required|numeric');

    if ($this->form_validation->run() === FALSE) {
      // Jika validasi gagal, kembali ke form edit
      $this->session->set_flashdata('error', 'Input tidak valid!');
      return redirect('/dashboard/edit_data/' . $id);
    }

    // Ambil input dari form
    $keterangan = $this->input->post('keterangan', true);
    $persen = $this->input->post('persen', true);
    $nilai_awal = $this->input->post('nilai_awal', true);

    $data = [
      'keter' => htmlspecialchars_decode($keterangan),
      'nilai_awal' => htmlspecialchars($nilai_awal),
      'persen' => htmlspecialchars($persen),
    ];

    $this->Pii_Model->update($id, $data);


    $this->session->set_flashdata('success_update', 'Data berhasil diupdate!');
    redirect('/dashboard/list_data');
  }



  public function delete_data($id)
  {
    $test = $this->Pii_Model->delete_by_id($id);

    // debuging
    // var_dump($test);
    // exit;

    $this->session->set_flashdata('success_delete', 'Data berhasil dihapus');
    redirect('/dashboard/list_data');
  }

  public function acpe()
  {

    $data['list_acpe'] = $this->Pii_Model->get_acpe();

    $this->load->view('header');
    $this->load->view('acpe_view', $data);
    $this->load->view('footer');
  }

  public function import_acpe()
  {
    // Konfigurasi upload
    $config = [
      'upload_path'   => './uploads/',
      'allowed_types' => 'xlsx|xls|csv',
      'max_size'      => 2048,
      'file_name'     => 'excel_import_' . time()
    ];

    $this->load->library('upload', $config);

    // Upload file
    if (!$this->upload->do_upload('excel_file')) {
      $this->session->set_flashdata('error', $this->upload->display_errors());
      redirect('/dashboard/acpe');
      return;
    }

    $uploadedFile = $this->upload->data();

    try {
      // Load spreadsheet
      $spreadsheet = IOFactory::load($uploadedFile['full_path']);
      $sheetData   = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

      // Loop mulai dari baris kedua (skip header)
      foreach ($sheetData as $rowIndex => $row) {
        if ($rowIndex === 1) continue; // skip header

        // Skip baris kosong
        if (empty(array_filter($row))) continue;

        $data = [
          'no_acpe'       => trim($row['A']),
          'doi'           => trim($row['B']),
          'nama'          => trim($row['C']),
          'kta'           => trim($row['D']),
          'new_po_no'     => trim($row['E']),
          'bk_acpe'       => trim($row['F']),
          'asosiasi_prof' => trim($row['G']),
        ];

        // Validasi kolom wajib
        if (!empty($data['no_acpe']) && !empty($data['doi']) && !empty($data['nama'])) {
          $this->Pii_Model->insert_from_import($data);
        }
      }

      $this->session->set_flashdata('success', '✅ Data berhasil diimpor.');
    } catch (\Exception $e) {
      $this->session->set_flashdata('error', 'Gagal memproses file: ' . $e->getMessage());
    }

    // Hapus file upload untuk keamanan
    if (file_exists($uploadedFile['full_path'])) {
      unlink($uploadedFile['full_path']);
    }

    redirect('/dashboard/acpe');
  }



  // ITS

  public function its()
  {
    $this->load->view('header');
    $this->load->view('its_view');
    $this->load->view('footer');
  }


  public function get_data_its()
  {
    $draw   = intval($this->input->get("draw"));
    $start  = intval($this->input->get("start"));
    $length = intval($this->input->get("length"));
    $search = $this->input->get("search")['value'];

    $users = $this->Pii_Model->get_its($start, $length, $search);
    $total = $this->Pii_Model->count_all();
    $filtered = $this->Users_model->count_filtered($search);

    $data = [];
    $no = $start + 1;
    foreach ($users as $user) {
      $data[] = [
        $no++,
        $user->username,
        $user->email,
        $user->activated == 1
          ? '<span class="badge bg-success">Aktif</span>'
          : '<span class="badge bg-secondary">Nonaktif</span>',
        '<a href="' . base_url('users/get_user_detail/' . $user->id) . '" class="btn btn-sm btn-dark"><i class="fa fa-eye"></i></a>
        <a href="' . base_url('users/edit/' . $user->id) . '" class="btn btn-sm btn-primary"><i class="fa fa-edit"></i></a>
       <a href="#" class="btn btn-sm btn-danger btn-delete" data-id="' . $user->id . '"><i class="fa fa-trash"></i></a>'
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
