  <?php
  
  public function get_list_acpe()
  {
  $request = $this->input->post();

    $draw   = intval($request['draw'] ?? 1);
    $start  = intval($request['start'] ?? 0);
    $length = intval($request['length'] ?? 10);
    $search = $request['search']['value'] ?? '';

  // Total semua data
  $recordsTotal = $this->db->count_all('acpe');

  // Query untuk filter
  $this->db->from('acpe');
  if (!empty($search)) {
  $this->db->group_start();
  $this->db->like('kta', $search);
  $this->db->or_like('nama', $search);
  $this->db->or_like('no_acpe', $search);
  $this->db->or_like('new_pe_no', $search);
  $this->db->group_end();
  }
  $recordsFiltered = $this->db->count_all_results('', false);

  // Batasin hasil (INI PENTING!)
  $this->db->limit($length, $start);
  $query = $this->db->get();

  $data = [];
  $no = $start + 1;
  foreach ($query->result() as $row) {
  $btnEdit = '<a href="#" onclick="load_edit_data_acpe(' . $row->id . ')" class="btn btn-primary btn-xs">
    <i class="fa fa-edit"></i>Edit
  </a>';
  $urlNama = '<a href="' . base_url('admin/acpe/detail_acpe/' . $row->kta) . '">
    <h5>' . $row->nama . '</h5>
  </a>';

  $data[] = [
  $no++,
  $row->no_acpe,
  $row->doi,
  $urlNama,
  $row->kta,
  $row->new_pe_no,
  $row->asosiasi_prof,
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
  //end get acpe json for datatables