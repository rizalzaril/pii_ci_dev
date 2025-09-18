<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">

  <?php $this->load->view('admin/common/meta_tags'); ?>
  <?php $this->load->view('admin/common/before_head_close'); ?>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css"
    integrity="sha512-DxV+EoADOkOygM4IR9yXP8Sb2qwgidEmeqAEmDKIOfPRQZOWbXCzLC6vjbZyy0vPisbH2SyW27+ddLVCN+OMzQ=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />

  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />
  <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css" />
  <link rel="stylesheet" type="text/css" href="<?php echo base_url('assets/css/bootstrap.css') ?>" />
  <link rel="stylesheet" type="text/css" href="/assets/css/style.css" />

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script type="text/javascript" src="<?php echo base_url('assets/js/bootstrap.js') ?>"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

  <style type="text/css">
    .awesome_styleee {
      font-size: 24px;

    }


    .awesome_stylee {
      font-size: 18px;
      background-color: #ffae00;
    }

    .awesome_style {
      font-size: 18px;

    }

    /* Overlay Loading Fullscreen */
    #loadingOverlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(255, 255, 255, 0.8);
      z-index: 9999;
      display: flex;
      justify-content: center;
      align-items: center;
      flex-direction: column;
      font-size: 20px;
      color: #333;
    }

    #loadingOverlay i {
      font-size: 50px;
      margin-bottom: 10px;
      animation: spin 1s linear infinite;
    }

    @keyframes spin {
      0% {
        transform: rotate(0deg);
      }

      100% {
        transform: rotate(360deg);
      }
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

    <!-- Right side column. Contains the navbar and content of the page -->
    <aside class="right-side">
      <!-- Content Header (Page header) -->
      <section class="content-header">
        <h1> ACPE Management
          <!--<small>advanced tables</small>-->
        </h1>
        <ol class="breadcrumb">
          <li><a href="<?php echo base_url('admin/dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
          <!--<li><a href="#">Examples</a></li>-->
          <li>
            <h10>Manage Members</h10>
          </li>
        </ol>

      </section>

      <!-- ----------------------------------------------------------------------------------------------- -->

      <link rel="stylesheet" type="text/css" href="/assets/css/style.css" />
      <script src="/assets/js/jquery.js" type="text/javascript"></script>

      <link rel="stylesheet" type="text/css" href="/assets/css/datatable.css" />
      <script src="/assets/js/jquery.dataTables.js" type="text/javascript"></script>

      <script type="text/javascript" charset="utf-8">
        $(document).ready(function() {
          $('#datatables').dataTable({
            "sScrollY": "500px",
            "sScrollX": "100%",
            "bScrollCollapse": true,
            "bPaginate": true,
            "bJQueryUI": true
          });
        });

        function load_edit_data_acpe(id) {

          var idd = id;
          var id_proses = "Simpan";

          $.ajax({
            url: '<?php echo base_url('admin/acpe/get_acpe_by_id'); ?>',
            dataType: "html",
            type: "POST",
            async: true, //false
            data: {
              id: idd
            },
            success: function(jsonObject, status) {
              var x = JSON.parse(jsonObject);
              console.log(x);

              $('#id_id').val(x.Id);
              $('#id_kta').val(x.kta);
              $('#id_noacpe').val(x.no_acpe);
              $('#id_nama').val(x.nama);
              $('#id_doi').val(x.doi);
              $('#id_newpeno').val(x.new_pe_no);
              $('#id_bk').val(x.bk_acpe);

            }

          });

          $('#id_proses').val(id_proses);
          $('#id_batal').val(id_batal);
          $('.modal-title').html('Ubah Data ACPE');
          $('#quick_upload_acpe').modal('show');

        }

        function load_tambah_data_acpe() {
          var id_id = '';
          var id_kta = '';
          var id_noacpe = '';
          var id_nama = '';
          var id_doi = '';
          var id_newpeno = '';
          var id_bk = '';
          var id_proses = "Simpan";

          $('#id_id').val(id_id);
          $('#id_kta').val(id_kta);
          $('#id_noacpe').val(id_noacpe);
          $('#id_nama').val(id_nama);
          $('#id_doi').val(id_doi);
          $('#id_newpeno').val(id_newpeno);
          $('#id_bk').val(id_bk);

          $('#id_pros').val(id_proses);
          $('.modal-title').html('Tambah Data ACPE');
          $('#myModal_acpe').modal('show');

        }
      </script>

</head>

<body class="skin-blue">

  <!-- Loading Overlay -->
  <div id="loadingOverlay">
    <i class="fa fa-spinner"></i>
    Loading data, please wait...
  </div>


  <div class="wrapper row-offcanvas row-offcanvas-left">

    <main class="main">
      <div class="content">

        <table>
          <tr>
            <td colspan='15' align="right">
              <a onclick="load_tambah_data_acpe()" href="" data-target="#myModal_acpe" class="btn btn-primary btn-xs" data-toggle='modal'>Tambah Data</a>
            </td>
          </tr>
          <tr>
            <td colspan='15' align="center">
              <table id="acpeTables" class="display" width="100%" align="center">

                <thead>
                  <tr class="awesome_stylee">
                    <th align="center" class="awesome_stylee"><b>#</th>
                    <th align="center" class="awesome_stylee"><b>NO. ACPE</th>
                    <th align="center" class="awesome_stylee"><b>DATE OF ISSUE</th>
                    <th align="center" class="awesome_stylee"><b>N A M A</th>
                    <th align="center" class="awesome_stylee"><b>NO KTA</th>
                    <th align="center" class="awesome_stylee"><b>NEW PE NO.</th>
                    <th align="center" class="awesome_stylee"><b>ACPE Dicipline</th>

                    <th align="center" class="awesome_stylee"><b>ACTION</th>

                  </tr>

                </thead>
                <tbody>



                </tbody>
            </td>
          </tr>
        </table>
        </td>
        </tr>
        </table>

      </div>
  </div>

  <!-- Modal Edit  Data -->

  <!-- Modal Update  Data -->

  <div class="modal fade" id="quick_upload_acpe" tabindex="-1" role="dialog" aria-labelledby="quick_upload_acpe" aria-hidden="true">

    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h3 class="modal-title">Ubah Data ACPE. </h3>
        </div>
        <div class="modal-body">
          <!-- /.box-header -->
          <!-- form start -->
          <div id="errboxUloadSkip" style="display:none;" class="alert alert-warning" role="alert"></div>
          <div class="box-body" id="j_box">

            <table width="95%" style="border: 0;">
              <form id="form_acpe" name="form_acpe" action="<?= base_url('admin/acpe/update_acpe') ?>" method="post">

                <tr>
                  <td><strong><span class="form-group">Nomor KTA</span></strong></td>
                  <td><input type="text" name="id_kta" id="id_kta" /> </td>
                </tr>
                <tr>
                  <td><strong><span class="form-group">N A M A</span></strong></td>
                  <td><input type="text" size='50' name='id_nama' id='id_nama' /> </td>
                </tr>
                <tr>
                  <td>
                    <label><strong><span class="form-group">Nomor</span></strong></label>
                  </td>
                  <td> <input type="text" name='id_noacpe' id='id_noacpe' /> </td>
                  </td>
                </tr>
                <tr>
                  <td><strong><span class="form-group">ACPE Dicipline</span></strong></td>
                  <td><input type="text" name="id_bk" id="id_bk" /></td>
                </tr>
                <tr>
                  <td><strong><span class="form-group">Date Of Issue</span></strong></td>
                  <td><input type="text" name="id_doi" id="id_doi" /></td>
                </tr>
                <tr>
                  <td><strong><span class="form-group">New_Pe_No</span></strong></td>
                  <td><input type="text" name="id_newpeno" id="id_newpeno" /></td>
                </tr>
                <tr>
                  <td> <input type="hidden" name='id_id' id='id_id' /></td>
                </tr>


            </table>

          </div>
          <!-- /.box-body -->

          <div class="modal-footer">
            <div class="form-group">
              <input type="submit" class="btn btn-default" name="id_proses" id="id_proses" /> <button type="button" class='btn btn-primary btn-small' data-dismiss="modal">Batal</button>
            </div>
          </div>
          </form>
        </div>
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
  </div>

  <!-- End of Modal Edit Data -->

  <!-- Modal Tambah Data -->

  <div class="modal fade" id="myModal_acpe" tabindex="-1" role="dialog" aria-labelledby="myModal_acpe" aria-hidden="true">

    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h3 class="modal-title">Ubah Data ACPE. </h3>
        </div>
        <div class="modal-body">
          <!-- /.box-header -->
          <!-- form start -->
          <div id="errboxUloadSkip" style="display:none;" class="alert alert-warning" role="alert"></div>
          <div class="box-body" id="j_box">

            <table width="95%" style="border: 0;">
              <form id="form_aer" name="form_aer" action="<?= base_url('/admin/acpe/tambah_acpe') ?>" method="post">
                <tr>
                  <td><strong><span class="form-group">Nomor KTA</span></strong></td>
                  <td><input type="text" name="id_kta" id="id_kta" /> </td>
                </tr>
                <tr>
                  <td><strong><span class="form-group">N A M A</span></strong></td>
                  <td><input type="text" size='50' name='id_nama' id='id_nama' /> </td>
                </tr>
                <tr>
                  <td>
                    <label><strong><span class="form-group">Nomor</span></strong></label>
                  </td>
                  <td> <input type="text" name='id_noacpe' id='id_noacpe' /> </td>
                  </td>
                </tr>
                <tr>
                  <td><strong><span class="form-group">ACPE Dicipline</span></strong></td>
                  <td><input type="text" name="id_bk" id="id_bk" /></td>
                </tr>
                <tr>
                  <td><strong><span class="form-group">Date Of Issue</span></strong></td>
                  <td><input type="text" name="id_doi" id="id_doi" /></td>
                </tr>
                <tr>
                  <td><strong><span class="form-group">New_Pe_No</span></strong></td>
                  <td><input type="text" name="id_newpeno" id="id_newpeno" /></td>
                </tr>
                <tr>
                  <td> <input type="hidden" name='id_id' id='id_id' /></td>
                </tr>
            </table>
          </div>
          <!-- /.box-body -->

          <div class="modal-footer">
            <div class="form-group">
              <input type="submit" class="btn btn-default" name="id_pros" id="id_pros" /> <button type="button" class='btn btn-primary btn-small' data-dismiss="modal">Batal</button>
            </div>
          </div>
          </form>
        </div>
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
  </div>

  <!-- End of Modal Input Data -->

  <script>
    //SCRIPT DATATABLES WITH SERVER SIDE
    $(document).ready(function() {
      var table = $('#acpeTables').DataTable({
        responsive: true,
        processing: true,
        serverSide: true,
        ajax: {
          url: '<?php echo base_url('admin/acpe/get_list_acpe'); ?>',
          type: 'POST'
        },
        initComplete: function() {
          $('#loadingOverlay').fadeOut();
        },
        columnDefs: [{
            targets: 0,
            orderable: false
          }, // kolom nomor tidak bisa sort
          {
            targets: -1,
            orderable: false
          } // kolom action tidak bisa sort
        ]
      });
    });
    // END SCRIPT DATATABLES
  </script>

</body>

</html>