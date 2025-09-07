<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">

  <?php $this->load->view('admin/common/meta_tags'); ?>
  <?php $this->load->view('admin/common/before_head_close'); ?>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css" integrity="sha512-DxV+EoADOkOygM4IR9yXP8Sb2qwgidEmeqAEmDKIOfPRQZOWbXCzLC6vjbZyy0vPisbH2SyW27+ddLVCN+OMzQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />

  <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
  <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
  <script src="<?php echo base_url('assets/js/jquery-3.1.1.min.js') ?>" type="text/javascript"></script>

  <link rel="stylesheet" type="text/css" href="<?php echo base_url('assets/css/bootstrap.css') ?>" />
  <script type="text/javascript" src="<?php echo base_url('assets/js/jquery.js') ?>"></script>
  <script type="text/javascript" src="<?php echo base_url('assets/js/bootstrap.js') ?>"></script>

  <style type="text/css">
    .table thead th {
      background-color: #ffae00 !important;
      /* Orange */
      color: #000 !important;
      /* Teks hitam */
      font-weight: bold;
      /* Tebal */
      text-align: center;
      vertical-align: middle;
    }

    .awesome_style {
      font-size: 16px;
    }
  </style>

  <link rel="stylesheet" type="text/css" href="/assets/css/style.css" />
  <script src="/assets/js/jquery.js" type="text/javascript"></script>

  <link rel="stylesheet" type="text/css" href="/assets/css/datatable.css" />
  <script src="/assets/js/jquery.dataTables.js" type="text/javascript"></script>

  <?php $this->load->view('admin/common/after_body_open'); ?>
  <?php $this->load->view('admin/common/header'); ?>
  <div class="wrapper row-offcanvas row-offcanvas-left">
    <?php $this->load->view('admin/common/left_side'); ?>

    <?php if ($selesai == 'y') { ?>
      <div class="container">
        <div class="alert alert-success fade in">
          <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
          Data telah disimpan....
        </div>
      </div>
    <?php } ?>

    <!-- Right side column -->
    <aside class="right-side">
      <section class="content-header">
        <h1> ASEAN Eng Management </h1>
        <ol class="breadcrumb">
          <li><a href="<?php echo base_url('admin/dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
          <li class="active">Manage Members</li>
        </ol>
      </section>

      <script type="text/javascript">
        $(document).ready(function() {
          $('#datatables').dataTable({
            "sScrollY": "500",
            "sScrollX": "100%",
            "bScrollCollapse": true,
            "bPaginate": true,
            "bJQueryUI": true
          });
        });

        function load_upload_skip_view(id) {
          var idd = id;
          var id_proses = "Simpan";

          $.ajax({
            url: '<?php echo base_url('admin/aer/get_aer_by_id'); ?>',
            dataType: "html",
            type: "POST",
            data: {
              id: idd
            },
            success: function(jsonObject) {
              var x = JSON.parse(jsonObject);
              $('#id_id').val(x.id);
              $('#id_kta').val(x.kta);
              $('#id_noaer').val(x.no_aer);
              $('#id_nama').val(x.nama);
              $('#id_grade').val(x.grade);
            }
          });

          $('#id_proses').val(id_proses);
          $('.modal-title').html('Ubah Data ASEAN Eng');
          $('#quick_upload_skip').modal('show');
        }

        function load_tambah_data() {
          $('#id_id').val('');
          $('#id_kta').val('');
          $('#id_noaer').val('');
          $('#id_nama').val('');
          $('#id_grade').val('');
          $('#id_pros').val("Simpan");
          $('.modal-title').html('Tambah Data ASEAN Eng');
          $('#myModal').modal('show');
        }
      </script>

</head>

<body class="skin-blue">

  <div class="wrapper row-offcanvas row-offcanvas-left">
    <main class="main">
      <div class="content">

        <div class="table">
          <div class="text-right" style="margin-bottom:10px;">
            <a onclick="load_tambah_data()" href="#" data-target="#myModal" data-toggle="modal" class="btn btn-primary btn-xs">
              <i class="fa fa-plus"></i> Tambah Data
            </a>
          </div>

          <table id="datatables" class="table table-bordered table-striped table-hover">
            <thead>
              <tr>
                <th>#</th>
                <th>NO. AER</th>
                <th>NAMA</th>
                <th>GRADE</th>
                <th>NO KTA</th>
                <th>ACTION</th>
              </tr>
            </thead>
            <tbody>
              <?php
              if (isset($list_aer) && !empty($list_aer)) {
                $no = 1;
                foreach ($list_aer as $isinya) {

                  echo '<tr>';
                  echo '<td class="text-center">' . $no++ . '</td>';
                  echo '<td>' . $isinya->no_aer . '</td>';
                  echo '<td><a href="' . base_url('admin/aer/detail_aer/' . $isinya->kta) . '"><h5>' . $isinya->nama . '</h5></a></td>';
                  echo '<td>' . $isinya->grade . '</td>';
                  echo '<td>' . $isinya->kta . '</td>';



                  // Kolom ACTION
                  echo '<td class="text-center">
								<a onclick="load_upload_skip_view(' . $isinya->id . ')" 
								   href="#" 
								   data-target="#quick_upload_skip" 
								   data-toggle="modal" 
								   class="btn btn-primary btn-xs">
								   <i class="fa fa-edit"></i> Ubah Data
								</a>
							  </td>';
                  echo '</tr>';
                }
              }
              ?>
            </tbody>
          </table>
        </div>

      </div>
    </main>
  </div>

  <!-- Modal Update Data -->
  <div class="modal fade" id="quick_upload_skip" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h3 class="modal-title">Ubah Data ASEAN Eng.</h3>
        </div>
        <div class="modal-body">
          <form id="form_aer" action="<?= base_url('/admin/aer/update_aer') ?>" method="post">
            <table class="table-borderless">
              <tr>
                <td>Nomor</td>
                <td><input type="text" name='id_noaer' id='id_noaer' /></td>
              </tr>
              <tr>
                <td>Nomor KTA</td>
                <td><input type="text" name="id_kta" id="id_kta" /></td>
              </tr>
              <tr>
                <td>Grade</td>
                <td><input type="text" name="id_grade" id="id_grade" /></td>
              </tr>
              <tr>
                <td>Nama</td>
                <td><input type="text" name='id_nama' id='id_nama' size="50" /></td>
              </tr>
            </table>
            <input type="hidden" name='id_id' id='id_id' />
            <div class="modal-footer">
              <input type="submit" class="btn btn-default" name="id_proses" id="id_proses" />
              <button type="button" class="btn btn-primary" data-dismiss="modal">Batal</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Tambah Data -->
  <div class="modal fade" id="myModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h3 class="modal-title">Tambah Data ASEAN Eng.</h3>
        </div>
        <div class="modal-body">
          <form id="form_aer" action="<?= base_url('/admin/aer/tambah_aer') ?>" method="post">
            <table class="table-borderless">
              <tr>
                <td>Nomor</td>
                <td><input type="text" name='id_noaer' id='id_noaer' /></td>
              </tr>
              <tr>
                <td>Nomor KTA</td>
                <td><input type="text" name="id_kta" id="id_kta" /></td>
              </tr>
              <tr>
                <td>Grade</td>
                <td><input type="text" name="id_grade" id="id_grade" /></td>
              </tr>
              <tr>
                <td>Nama</td>
                <td><input type="text" name='id_nama' id='id_nama' size="50" /></td>
              </tr>
            </table>
            <input type="hidden" name='id_id' id='id_id' />
            <div class="modal-footer">
              <input type="submit" class="btn btn-default" name="id_pros" id="id_pros" />
              <button type="button" class="btn btn-primary" data-dismiss="modal">Batal</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

</body>

</html>