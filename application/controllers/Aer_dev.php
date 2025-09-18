<?php defined('BASEPATH') or exit('No direct script access allowed');

class Aer extends CI_Controller
{

  public function index()
  {
    $akses = array("0", "1", "2", "9", "10", "11", "14", "15");

    // Cek apakah user type ada di daftar akses
    if (!in_array($this->session->userdata('type'), $akses)) {
      // Jika tidak punya akses, redirect ke halaman home
      redirect('admin/home');
      return; // hentikan eksekusi lebih lanjut
    }

    // Load model
    $this->load->model('simpan_model');

    // Ambil data dari model
    $data['list_aer'] = $this->simpan_model->ambil_data_aer();
    $data['selesai']  = 't';

    // Load view
    $this->load->view('admin/list_aer', $data);
  }

  public function tampilkan_edit()
  {
    $proses   = (trim($this->input->post('proses', true))) ? trim($this->input->post('proses', true)) : '';
    $data['proses'] = $proses;
    if ($proses = "Proses Edit") {
      $data['judul']  = "Update Data ASEAN Eng";
      $data['keter']  = "Proses Edit";
      $data['keter1'] = "Batal";
      $data['selesai'] = 't';
      $data['id']     = (trim($this->input->post('id', true))) ? trim($this->input->post('id', true)) : '';
      $data['kta']    = (trim($this->input->post('nokta', true))) ? trim($this->input->post('nokta', true)) : '';
      $data['nama']   = (trim($this->input->post('nama', true))) ? trim($this->input->post('nama', true)) : '';
      $data['kode']   = (trim($this->input->post('kode', true))) ? trim($this->input->post('kode', true)) : '';
      $data['grade']  = (trim($this->input->post('grade', true))) ? trim($this->input->post('grade', true)) : '';
    }
    $this->load->view('admin/edit_v_aer', $data);
  }

  public function tampilkan_tambah()
  {

    $proses   = (trim($this->input->post('proses', true))) ? trim($this->input->post('proses', true)) : '';
    $data['proses'] = $proses;

    if ($proses = "Tambah") {
      $data['judul']  = "Tambah Data ASEAN Eng";
      $data['id']     = '';
      $data['keter']  = "Proses Tambah";
      $data['keter1'] = "Batal";
      $data['selesai'] = 't';
      $data['kta']    = '';
      $data['nama']   = '';
      $data['kode']   = '';
      $data['grade']  = '';
    }

    $this->load->view('admin/edit_v_aer', $data);
  }

  public function proses()
  {
    $proses   = (trim($this->input->post('proses', true))) ? trim($this->input->post('proses', true)) : '';
    $id       = (trim($this->input->post('id', true))) ? trim($this->input->post('id', true)) : '';
    $kta      = (trim($this->input->post('nokta', true))) ? trim($this->input->post('nokta', true)) : '';
    $nama     = (trim($this->input->post('nama', true))) ? trim($this->input->post('nama', true)) : '';
    $kode     = (trim($this->input->post('kode', true))) ? trim($this->input->post('kode', true)) : '';
    $grade    = (trim($this->input->post('grade', true))) ? trim($this->input->post('grade', true)) : '';

    if ($proses == 'Batal') {
      $data['selesai'] = 't';
    } else {
      $data['selesai'] = 'y';
    }
    $data_update_aer = [
      'no_aer'     => $kode,
      'nama'     => $nama,
      'grade'    => $grade,
      'kta'   => $kta

    ];

    $this->load->model('simpan_model');
    if ($proses == 'Proses Edit') {
      $this->simpan_model->update_data_aer($id, $data_update_aer);
    }
    if ($proses == 'Proses Tambah') {
      $this->simpan_model->save_data_aer($data_update_aer);
    }

    $data['list_aer'] = $this->simpan_model->ambil_data_aer();
    $this->load->view('admin/list_aer', $data);
  }

  public function tambah_aer()
  {

    $id       = $_POST['id_id'];
    $kta      = $_POST['id_kta'];
    $nama     = $_POST['id_nama'];
    $kode     = $_POST['id_noaer'];
    $grade    = $_POST['id_grade'];
    $doi      = $_POST['id_doi'];
    $url      = $_POST['id_url'];

    $data_update_aer = [
      'no_aer'       => $kode,
      'nama'       => $nama,
      'grade'      => $grade,
      'kta'     => $kta,
      'doi'     => $doi,
      'url'     => $url

    ];



    $this->load->model('simpan_model');

    $this->simpan_model->save_data_aer($data_update_aer);

    $data['list_aer'] = $this->simpan_model->ambil_data_aer();
    $data['selesai'] = 'y';
    $this->load->view('admin/list_aer', $data);
  }

  public function update_aer()
  {
    //	     $proses   = (trim($this->input->post('proses',true)))? trim($this->input->post('proses',true)) : '';
    $id       = $_POST['id_id'];
    $kta      = $_POST['id_kta'];
    $nama     = $_POST['id_nama'];
    $kode     = $_POST['id_noaer'];
    $grade    = $_POST['id_grade'];
    $doi      = $_POST['id_doi'];
    $url      = $_POST['id_url'];

    $data_update_aer = [
      'no_aer'     => $kode,
      'nama'     => $nama,
      'grade'    => $grade,
      'kta'   => $kta,
      'doi'   => $doi,
      'url_aer' => $url

    ];

    //var_dump($data_update_aer);
    //exit;

    $this->load->model('simpan_model');

    $this->simpan_model->update_data_aer($id, $data_update_aer);

    $data['list_aer'] = $this->simpan_model->ambil_data_aer();
    $data['selesai'] = 'y';
    $this->load->view('admin/list_aer', $data);
  }

  public function get_aer_by_id()
  {
    $this->load->model('simpan_model');
    $id = $_POST['id'];
    $sql_data = $this->simpan_model->cari_data_aer_id($id);
    //	        $this->load->view('admin/Edit_aer',$sql_data);

    header('Content-Type: application/json');
    echo json_encode($sql_data);
  }

  public function ubah_aer_modal()
  {
    $this->load->model('simpan_model');
    $id = $_POST['id'];
    $data['a'] = $$this->simpan_model->cari_data_aer_id($id);
    $this->load->view('admin/Edit_aer', $data);
  }


  // Detail Aer berdasarkan KTA Added By Rizal
  public function detail_aer($kta)
  {
    $this->load->model('Aer_detail_model');

    $akses = array("0", "1", "2", "9", "10", "11", "14", "15");

    if (!in_array($this->session->userdata('type'), $akses)) {
      // Hentikan proses, tampilkan pesan error HTML
      show_error('Akses Ditolak');
      return;
    }

    $data['detail_aer'] = $this->Aer_detail_model->get_detail_aer($kta);

    // echo '<pre>';
    // var_dump($data);
    // echo '</pre>';
    // exit;

    if (!$data['detail_aer']) {
      show_404(); // Jika data tidak ditemukan
    }

    //$this->load->view('admin/common/header'); 
    $this->load->view('admin/aer_details_view', $data);
    //$this->load->view('admin/common/footer'); 
  }


  //Get aer json for datatables By Rizal
  // Get aer json for datatables By Rizal
  public function get_list_aer()
  {
    // Bisa terima POST atau GET
    $request = $this->input->post();
    if (empty($request)) {
      $request = $this->input->get();
    }

    $draw   = intval($request['draw'] ?? 1);
    $start  = intval($request['start'] ?? 0);
    $length = intval($request['length'] ?? 10);
    $search = $request['search']['value'] ?? '';

    // Hitung total semua data
    $recordsTotal = $this->db->count_all('aer');

    // Query dengan filter
    $this->db->from('aer');
    if (!empty($search)) {
      $this->db->group_start();
      $this->db->like('kta', $search);
      $this->db->or_like('nama', $search);
      $this->db->or_like('no_aer', $search);
      $this->db->or_like('grade', $search);
      $this->db->group_end();
    }

    // Hitung setelah filter
    $recordsFiltered = $this->db->count_all_results('', false);

    // Limit dan ambil data
    $this->db->limit($length, $start);
    $query = $this->db->get();

    $data = [];
    $no = $start + 1;
    foreach ($query->result() as $row) {
      $btnEdit = '<a href="javascript:void(0)" onclick="load_edit_data(' . $row->id . ')" class="btn btn-primary btn-xs">
                        <i class="fa fa-edit"></i> Edit
                    </a>';

      $urlNama = '<a href="' . base_url('admin/aer/detail_aer/' . $row->kta) . '"><h5>' . $row->nama . '</h5></a>';

      $urlAerBtn = '<a href="' . $row->url_aer . '" target="_blank" class="btn btn-info btn-xs"><i class="fa fa-file"></i> Lihat File</a>';
      $urlAerEmpty = '<span class="text-danger">File tidak tersedia</span>';

      $data[] = [
        $no++,
        $row->no_aer,
        $urlNama,
        $row->grade,
        $row->kta,
        $row->doi,
        $row->url_aer ? $urlAerBtn : $urlAerEmpty,
        $btnEdit,
      ];
    }

    $output = [
      "draw" => $draw,
      "recordsTotal" => $recordsTotal,
      "recordsFiltered" => $recordsFiltered,
      "data" => $data
    ];

    $this->output
      ->set_content_type('application/json')
      ->set_output(json_encode($output));
  }

  //end get aer json for datatables
}
