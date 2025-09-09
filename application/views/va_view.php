<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <!-- _ci_view: <?php echo $_ci_view; ?> -->
  <meta name="_ci_view" content="<?php echo $_ci_view; ?>">
  <meta file="">
  <title><?php echo $title; ?></title>
  <?php $this->load->view('admin/common/meta_tags'); ?>
  <?php $this->load->view('admin/common/before_head_close'); ?>


  <link rel="stylesheet" href="<?php echo base_url('assets/css/jquery-ui.css'); ?>">
  <script src="<?php echo base_url('assets/js/jquery-1.12.4.js'); ?>"></script>
  <script src="<?php echo base_url('assets/js/jquery-ui.js'); ?>"></script>


  <script>
    var table = '';
    $(function() {
      $("#from_ip").datepicker({
        dateFormat: 'dd-mm-yy',
        changeMonth: true,
        changeYear: true,
        yearRange: "<?php echo date('Y'); ?>:2050"
      });

      $("#from,#until").change(function() {
        var from = $("#from").val();
        var to = $("#until").val();

        var parts = from.split("-");
        var st = new Date(parseInt(parts[2], 10),
          parseInt(parts[1], 10) - 1,
          parseInt(parts[0], 10));
        var parts2 = to.split("-");
        var et = new Date(parseInt(parts2[2], 10),
          parseInt(parts2[1], 10) - 1,
          parseInt(parts2[0], 10));

        //alert(st +' '+ et);
        if (from != '' && to != '') {
          if (st > et) {
            alert("Invalid Date Range");
            $(this).val('');
          }
        }
      });

    });

    function savesettotal() {
      var total = $('#total').val();
      var id_total = $('#id_total').val();
      var total_anggota = $('#total_anggota').val();
      var total_tahunan = $('#total_tahunan').val();
      var total_pangkal = $('#total_pangkal').val();
      var total_gedung = $('#total_gedung').val();
      var total_perpus = $('#total_perpus').val();
      var total_ceps = $('#total_ceps').val();


      var dataHTML = 'not valid';
      $.ajax({
        url: '<?php echo site_url('admin/members/settotal') ?>',
        dataType: "html",
        type: "POST",
        async: true, //false
        data: {
          id: id_total,
          total: total,
          total_anggota: total_anggota,
          total_tahunan: total_tahunan,
          total_pangkal: total_pangkal,
          total_gedung: total_gedung,
          total_perpus: total_perpus,
          total_ceps: total_ceps
        },
        success: function(jsonObject, status) {
          console.log(jsonObject);
          if ((jsonObject != 'not valid')) {
            dataHTML = jsonObject;
          }

          if (dataHTML == 'not valid')
            alert('not valid');
          else
            window.location.href = "<?php echo base_url(); ?>admin/members/va";
        }
      });

      //$('#quick_profile').modal('hide');
      //$('#quick_ip').modal('toggle');
      //location.reload();
    }

    function set_active() {
      if ($("[name='id[]']:checked").length > 0) {
        // ER: Call backend one by one
        $("[name='id[]']:checked").each(function() {
          var idx = $(this).val();
          var idx_el = $(this);
          $.ajax({
            url: '<?php echo site_url('admin/members/set_active') ?>',
            dataType: "html",
            type: "POST",
            async: false, //true
            data: {
              id: idx
            },
            success: function(jsonObject, status) {
              if (jsonObject.substring(0, 5) == 'valid') {
                $(idx_el).parent().parent().find("td:nth-child(7)").html($(idx_el).parent().parent().find("td:nth-child(7) a").html());
                $(idx_el).parent().parent().find("td:nth-child(8)").html('<a href="javascript:;" > <span class="label label-success">Active</span> </a>');
              }
            }
          });
        });
      } else {
        alert('Silahkan pilih terlebih dahulu');
      }
    }

    function export_all() {

      var filter_status = $('#filter_status').val();
      var filter_bk = $('#filter_bk').val();
      var filter_cab = $('#filter_cab').val();
      var tgl_period = $('#tgl_period').val();
      var tgl_period2 = $('#tgl_period2').val();

      var tipe = $('#table_id tfoot tr th:eq(4) input').val();
      //console.log(query);
      //window.open( '<?php echo base_url(); ?>admin/members/export_va_all' );
      window.open('<?php echo base_url(); ?>admin/members/export_va_all?status=' + filter_status + '&bk=' + filter_bk + '&cab=' + filter_cab + '&tgl_period=' + tgl_period + '&tgl_period2=' + tgl_period2 + '&tipe=' + tipe);
    }

    function export_select() {
      if ($("[name='id[]']:checked").length > 0) {
        var arr = [];
        var is_valid = true;
        $("[name='id[]']:checked").each(function() {
          var x = $(this).parent().parent().children('td').eq(7).children('a').children('span');
          var xx = $(x).html();
          if (xx == 'Not Active') is_valid = false;
          else arr.push($(this).val());
        });


        if (is_valid) window.open('<?php echo base_url(); ?>admin/members/export_va_select?id=' + arr);
        else alert('Terdapat data yg belum di set aktif, silahkan di set active terlebih dahulu.');
      } else {
        alert('Silahkan pilih terlebih dahulu');
      }
    }
  </script>

  <!-- Overlay Loading styles -->
  <style>
    #loadingOverlay {
      display: none;
      /* awalnya disembunyikan */
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(255, 255, 255, 0.7);
      z-index: 9999;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    #loadingOverlay i {
      font-size: 50px;
      color: #3498db;
    }
  </style>


</head>

<body class="skin-blue">
  <?php $this->load->view('admin/common/after_body_open'); ?>
  <?php $this->load->view('admin/common/header'); ?>
  <div class="wrapper row-offcanvas row-offcanvas-left">
    <?php $this->load->view('admin/common/left_side'); ?>
    <!-- Right side column. Contains the navbar and content of the page -->
    <aside class="right-side">
      <!-- Content Header (Page header) -->
      <section class="content-header">
        <h1> VA
          <!--<small>advanced tables</small>-->
        </h1>
        <ol class="breadcrumb">
          <li><a href="<?php echo base_url('admin/dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
          <!--<li><a href="#">Examples</a></li>-->
          <li class="active">Manage Members</li>
        </ol>
      </section>



      <link rel="stylesheet" type="text/css" href="<?php echo base_url('assets/datatable/jquery.dataTables.css'); ?>">
      <script type="text/javascript" charset="utf8" src="<?php echo base_url('assets/datatable/jquery-3.3.1.js'); ?>"></script>
      <script type="text/javascript" charset="utf8" src="<?php echo base_url('assets/datatable/jquery.dataTables.min.js'); ?>"></script>

      <style>
        td.details-control {
          background: url('https://datatables.net/examples/resources/details_open.png') no-repeat center center;
          cursor: pointer;
        }

        tr.shown td.details-control {
          background: url('https://datatables.net/examples/resources/details_close.png') no-repeat center center;
        }

        tfoot input {
          width: 100%;
          padding: 3px;
          box-sizing: border-box;
        }
      </style>
      <script>
        function zeroPad(num, places) {
          return String(num).padStart(places, '0')
        }

        function format(d) {

          var html = '';
          var div = $('<div/>')
            .addClass('loading')
            .text('Loading...');
          //console.log(d);
          $.ajax({
            url: "<?php echo site_url('admin/members/get_pi_by_no_kta') ?>",
            dataType: "json",
            type: "POST",
            async: true, //false
            data: {
              no_kta: d.kta,
              certid: d.sertid
            },
            success: function(jsonObject, status) {
              html += '<table cellpadding="5" cellspacing="0" border="0" style="padding-left:50px;">';
              $.each(jsonObject, function(key, value) {

                var certificate_type = '';
                if (value.sertid == '1')
                  certificate_type = 'IPP';
                else if (value.sertid == '2')
                  certificate_type = 'IPM';
                else if (value.sertid == '3')
                  certificate_type = 'IPU';

                var ip = value.noip;
                /*
                '';
                if(value.sertid!=null)
                	ip += value.sertid;
                if(value.skip_code_bk_hkk!=null)
                	ip += '-'+ value.skip_code_bk_hkk+'-00-';
                if(value.skip_id!=null)
                	ip += value.skip_id+'-';
                if(value.skip_pm!=null)
                	ip += value.skip_pm;
                */

                //console.log(value.sertid+'-'+d.sertid +'..'+ value.sk_from+'-'+d.sk_from);
                if (value.sertid != d.sertid || value.sk_from != d.sk_from)
                  html += '<tr><td>' + certificate_type + '</td><td>' + ip + '</td><td>' + value.sk_from + '</td></tr>';
              });
              html += '</table>';
              div
                .html(html)
                .removeClass('loading');
            }
          });

          return div;
        }

        $(document).ready(function() {
          $jq = $.noConflict();
          table = $('#table_id').DataTable({
            "processing": true,
            "serverSide": true,
            "ordering": true, // Set true agar bisa di sorting
            "order": [
              [0, 'asc']
            ], // Default sortingnya berdasarkan kolom / field ke 0 (paling pertama)
            "ajax": {
              "url": "<?php echo base_url('admin/members/get_va') ?>", // URL file untuk proses select datanya
              "type": "POST",
              'data': function(data) {
                var filter_status = $('#filter_status').val();
                var filter_bk = $('#filter_bk').val();
                var filter_cab = $('#filter_cab').val();
                var tgl_period = $('#tgl_period').val();
                var tgl_period2 = $('#tgl_period2').val();
                data.filter_status = filter_status;
                data.filter_bk = filter_bk;
                data.filter_cab = filter_cab;
                data.tgl_period = tgl_period;
                data.tgl_period2 = tgl_period2;
              }
            },
            "deferRender": true,
            "aLengthMenu": [
              [15, 50, 100, 1000],
              [15, 50, 100, 1000]
            ], // Combobox Limit
            "columns": [{
                "data": "id"
              },
              {
                "data": "modifieddate"
              },
              {
                "render": function(data, type, row) {
                  var html = '';
                  html += '<a href="<?php echo base_url(); ?>admin/members/details_m/' + row.no_kta + '">' + (row.firstname != null ? row.firstname : '') + ' ' + (row.lastname != null ? row.lastname : '') + '</a>';

                  return html;
                }
              },
              {
                "render": function(data, type, row) {
                  return zeroPad(row.no_kta, 6);
                  //	return zeroPad(row.no_kta, 7);
                }
              },
              {
                "render": function(data, type, row) {
                  var html = '';
                  if (row.pay_type == '1')
                    html = 'REG';
                  else if (row.pay_type == '2')
                    html = 'HER';
                  else if (row.pay_type == '3')
                    html = 'FAIP Assessment Fee';
                  else if (row.pay_type == '4')
                    html = 'FAIP SIP Fee';
                  else if (row.pay_type == '5')
                    html = 'STRI';
                  else if (row.pay_type == '6')
                    html = 'PKB Assessment Fee';
                  else if (row.pay_type == '7')
                    html = 'PKB SIP Fee';
                  return html;
                }
              },
              {
                "data": "va"
              },


              {
                "render": function(data, type, row) {
                  var html = '';
                  if (row.is_upload_mandiri == '0') {
                    html = '<a href="javascript:;" class="btn btn-primary btn-xs" onClick="load_quick_total(' + row.id + ',' + row.warga_asing + ',' + row.sukarelatotal + ',' + row.iuranpangkal + ',' + row.iurantahunan + ',' + row.sukarelaanggota + ',' + row.sukarelagedung + ',' + row.sukarelaperpus + ',' + row.sukarelaceps + ');">' + row.sukarelatotal + '</a>';
                  } else if (row.is_upload_mandiri == '1') {
                    html = row.sukarelatotal;
                  } else
                    class_label = 'danger';
                  return html;
                }
              },


              {
                "render": function(data, type, row) {
                  if (row.status == '1') { //row.is_upload_mandiri=='1' && 
                    class_label = 'success';
                    status = 'Paid';
                  } else if (row.is_upload_mandiri == '0') {
                    class_label = 'warning';
                    status = 'Not Active';
                  } else if (row.is_upload_mandiri == '1' && row.status == '0') {
                    class_label = 'success';
                    status = 'Active';
                  } else if (row.is_upload_mandiri == '1' && row.status == '3') {
                    class_label = 'success';
                    status = 'Canceled';
                  } else {
                    class_label = 'danger';
                    status = 'Not Active';
                  }

                  return '<a onClick="update_status(' + row.id + ');" href="javascript:;" id="sts_' + row.id + '"> <span class="label label-' + class_label + '">' + status + '</span> </a>';
                }
              },
              {
                "render": function(data, type, row) {
                  var html = '<a href="javascript:;" class="btn btn-primary btn-xs" onclick="load_quick_PaymentDetail(\'' + row.id + '\');">Detail</a>';
                  <?php if ((
                      isAdminLSKI() &&
                      in_array($this->session->userdata('name'), array('rulyahmadj@yahoo.com', 'dir.lski@pii.or.id'))
                    ) ||
                    (
                      isAdminMembership() &&
                      in_array($this->session->userdata('name'), array('infiryus@gmail.com'))
                    )
                  ) { ?>
                    html = html + ' <a href="javascript:;" class="btn btn-primary btn-xs" onclick="load_quick_paymentCancel(\'' + row.id + '\');">Cancel</a>';
                  <?php } ?>
                  return html
                }
              },
            ],
            'columnDefs': [{
              'targets': 0,
              'searchable': false,
              'orderable': false,
              'className': 'dt-body-center',
              'render': function(data, type, full, meta) {
                return '<input type="checkbox" name="id[]" value="' + $('<div/>').text(data).html() + '">';
              }
            }],
            'order': [
              [1, 'asc']
            ]
          });


          // Handle click on "Select all" control
          $('#example-select-all').on('click', function() {
            // Get all rows with search applied
            var rows = table.rows({
              'search': 'applied'
            }).nodes();
            // Check/uncheck checkboxes for all rows in the table
            $('input[type="checkbox"]', rows).prop('checked', this.checked);
          });

          // Handle click on checkbox to set state of "Select all" control
          $('#table_id tbody').on('change', 'input[type="checkbox"]', function() {
            // If checkbox is not checked
            if (!this.checked) {
              var el = $('#example-select-all').get(0);
              // If "Select all" control is checked and has 'indeterminate' property
              if (el && el.checked && ('indeterminate' in el)) {
                // Set visual state of "Select all" control
                // as 'indeterminate'
                el.indeterminate = true;
              }
            }
          });

          $('#table_id tfoot th').each(function() {
            var title = $(this).text();
            $(this).html('<input type="text" placeholder="Search ' + title + '" />');
          });


          $("#table_id tfoot input").on('change', function() { //keyup 
            table
              .column($(this).parent().index() + ':visible')
              .search(this.value)
              .draw();
          });

          $('#filter_status').change(function() {
            table.draw();
          });

          $('#filter_cab').change(function() {
            table.draw();
          });

          $('#filter_bk').change(function() {
            table.draw();
          });

          $jq("#tgl_period,#tgl_period2").on('change', function() { //keyup 
            table.draw();
          });

          /*
	$('#table_id tbody').on('click', 'td.details-control', function () {
        var tr = $(this).closest('tr');
        var row = table.row( tr );
 
        if ( row.child.isShown() ) {
            // This row is already open - close it
            row.child.hide();
            tr.removeClass('shown');
        }
        else {
            // Open this row
            row.child( format(row.data()) ).show();
            tr.addClass('shown');
        }
    } );*/

        });
      </script>
      <section class="content">
        <div class="row">
          <div class="col-xs-12">
            <!--<p>
	  <input class="btn" name="button" value="Add" type="button" onClick="add_ip();">
	  </p>
	  <br />-->
            <div class="row" style="background-color:#3C8DBC; padding:10px; margin:0;">
              <div class="col-md-2 margin-bottom-special">
                <select id="filter_status" name="filter_status" class="form-control input-md">
                  <option value="" <?php echo (isset($search_data["filter_status"])) ? $search_data["filter_status"] == "" ? "selected" : '' : ''; ?>>All Status</option>
                  <option value="2" <?php echo (isset($search_data["filter_status"])) ? $search_data["filter_status"] == "2" ? "selected" : '' : ''; ?>>Paid</option>
                  <option value="1" <?php echo (isset($search_data["filter_status"])) ? $search_data["filter_status"] == "1" ? "selected" : '' : ''; ?>>Active</option>
                  <option value="0" <?php echo (isset($search_data["filter_status"])) ? $search_data["filter_status"] == "0" ? "selected" : '' : ''; ?>>Not Active</option>
                </select>
              </div>
              <div class="col-md-2 margin-bottom-special">
                <select id="filter_cab" name="filter_cab" class="form-control input-md">
                  <option value="" <?php echo (isset($search_data["filter_cab"])) ? $search_data["filter_cab"] == "" ? "selected" : '' : ''; ?>>All Wilayah / Cabang</option>
                  <?php
                  if (isset($m_cab)) {
                    foreach ($m_cab as $val) {
                  ?>
                      <option value="<?php echo $val->value; ?>" <?php echo (isset($search_data["filter_cab"]) ? (($search_data["filter_cab"] == $val->value) ? 'selected="true"' : "") : ""); ?>><?php echo $val->value; ?> - <?php echo $val->name; ?></option>
                  <?php
                    }
                  }
                  ?>
                </select>
              </div>

              <div class="col-md-2 margin-bottom-special">
                <select id="filter_bk" name="filter_bk" class="form-control input-md">
                  <option value="" <?php echo (isset($search_data["filter_bk"])) ? $search_data["filter_bk"] == "" ? "selected" : '' : ''; ?>>All BK</option>
                  <?php
                  if (isset($m_bk)) {
                    foreach ($m_bk as $val) {
                  ?>
                      <option value="<?php echo $val->value; ?>" <?php echo (isset($search_data["filter_bk"]) ? (($search_data["filter_bk"] == $val->value) ? 'selected="true"' : "") : ""); ?>><?php echo $val->value; ?> - <?php echo $val->name; ?></option>
                  <?php
                    }
                  }
                  ?>
                </select>
              </div>
              <?php
              $akses = array("780", "783", "782", "684");
              if (!in_array($this->session->userdata('admin_id'), $akses)) {
                //	if($this->session->userdata('type')!='14'){
              ?>
                <div class="col-md-1 margin-bottom-special">
                  <input class="btn" name="button" value="Set Active" type="button" onClick="set_active();">
                </div>

                <div class="col-md-1 margin-bottom-special">
                  <input class="btn" name="button" value="Export All" type="button" onClick="export_all();">
                </div>

                <div class="col-md-2 margin-bottom-special">
                  <input class="btn" name="button" value="Export Selection" type="button" onClick="export_select();">
                </div>
              <?php
              }
              ?>
            </div>

            <div class="row" style="background-color:#3C8DBC; padding:10px; margin:0;">

              <div class="col-md-2 margin-bottom-special">
                <input type="text" name="tgl_period" id="tgl_period" value="<?php //echo set_value('period',(isset($row->period)?date('d-m-Y',strtotime($row->period)):$row->period));
                                                                            ?>" class="form-control datepicker" placeholder="Start Date" />
              </div>

              <div class="col-md-2 margin-bottom-special">
                <input type="text" name="tgl_period2" id="tgl_period2" value="<?php //echo set_value('period',(isset($row->period)?date('d-m-Y',strtotime($row->period)):$row->period));
                                                                              ?>" class="form-control datepicker" placeholder="End Date" />
              </div>

            </div>

            <br />
            <table id="table_id" class="display" style="width:100%">
              <thead>
                <tr>
                  <th><input type="checkbox" name="select_all" value="1" id="example-select-all"></th>
                  <th>Tanggal Update Terakhir</th>
                  <th>Name</th>
                  <th align="center">No. KTA</th>
                  <th>Tipe</th>
                  <th align="center">No. VA</th>
                  <th>Total</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <!-- Body is filled by Ajax -->
              <tbody>
              </tbody>
              <tfoot>
                <tr>
                  <th></th>
                  <th>Tanggal Update Terakhir</th>
                  <th>Name</th>
                  <th align="center">No. KTA</th>
                  <th>Tipe</th>
                  <th align="center">No. VA</th>
                  <th>Total</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </section>





      <!-- /.content -->
    </aside>


    <div class="modal fade" id="quick_total">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">Set Total <span id="j_comp_name_p" style="font-weight:bold;"></span></h4>
          </div>
          <div class="modal-body">
            <!-- /.box-header -->
            <!-- form start -->
            <div id="errbo_px" style="display:none;"></div>
            <div class="box-body" id="j_box_p">
              <table width="95%" border="0">

                <tr class="is_wna" style="display:none;" width="500px">
                  <td width="200px"><strong><span class="form-group">Pangkal</span></strong></td>
                  <td width="200px">
                    <input type="text" name="total" id="total_pangkal" value="" class="form-control" onchange="calc_total()" placeholder="New Total" required="required" />


                  </td>
                </tr>
                <tr class="is_wna" style="display:none;">
                  <td width="200px"><strong><span class="form-group">Tahunan</span></strong></td>
                  <td width="200px">
                    <input type="text" name="total" id="total_tahunan" value="" class="form-control" onchange="calc_total()" placeholder="New Total" required="required" />


                  </td>
                </tr>
                <tr class="is_wna" style="display:none;">
                  <td width="200px"><strong><span class="form-group">Sumbangan Keanggotaan</span></strong></td>
                  <td width="200px">
                    <input type="text" name="total" id="total_anggota" value="" class="form-control" onchange="calc_total()" placeholder="New Total" required="required" />


                  </td>
                </tr>
                <tr class="is_wna" style="display:none;">
                  <td width="200px"><strong><span class="form-group">Sumbangan Gedung</span></strong></td>
                  <td width="200px">
                    <input type="text" name="total" id="total_gedung" value="" class="form-control" onchange="calc_total()" placeholder="New Total" required="required" />


                  </td>
                </tr>
                <tr class="is_wna" style="display:none;">
                  <td width="200px"><strong><span class="form-group">Sumbangan Perpustakaan</span></strong></td>
                  <td width="200px">
                    <input type="text" name="total" id="total_perpus" value="" class="form-control" onchange="calc_total()" placeholder="New Total" required="required" />


                  </td>
                </tr>
                <tr class="is_wna" style="display:none;">
                  <td width="200px"><strong><span class="form-group">Sumbangan CEPS</span></strong></td>
                  <td width="200px">
                    <input type="text" name="total" id="total_ceps" value="" class="form-control" onchange="calc_total()" placeholder="New Total" required="required" />


                  </td>
                </tr>

                <tr style="display:inline-flex;">
                  <td width="200px"><strong><span class="form-group">Total</span></strong></td>
                  <td width="200px">
                    <input type="text" name="total" id="total" value="" class="form-control" placeholder="New Total" required="required" />


                  </td>
                </tr>

                <tr>
                  <td><strong><span class="form-group"></span></strong></td>
                  <td id=""><button type="button" class="btn btn-default" onclick="savesettotal()" data-dismiss="modal">Save</button><input type="hidden" name="id_total" id="id_total" value="" /></td>
                </tr>
              </table>
            </div>
            <!-- /.box-body -->

          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          </div>
        </div>
        <!-- /.modal-content -->
      </div>
      <!-- /.modal-dialog -->
    </div>

    <div class="modal fade" id="quick_PaymentDetail">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">Payment Detail<span id="j_comp_name_p" style="font-weight:bold;"></span></h4>
          </div>
          <div class="modal-body">
            <!-- /.box-header -->
            <!-- form start -->
            <div id="errboxPaymentDetail" style="display:none;" class="alert alert-warning" role="alert"></div>
            <div class="box-body" id="j_box_p">
              <div id="paymentDetailText"></div>
            </div>
            <!-- /.box-body -->

          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          </div>
        </div>
        <!-- /.modal-content -->
      </div>
      <!-- /.modal-dialog -->
    </div>

    <!-- /.modal-content -->
  </div>
  <!-- /.modal-dialog -->
  </div>
  <!-- /.right-side -->

  <div id="loadingOverlay">
    <div>
      <i class="fa fa-spinner fa-spin"></i>
      <p>Loading, please wait...</p>
    </div>
  </div>


  <?php $this->load->view('admin/common/footer'); ?>

  <!-- Loading overlay -->
  <script>
    $(document).ready(function() {
      // Hide spinner setelah seluruh konten siap
      $("#loadingOverlay").fadeOut();
    });
  </script>

  <script>
    function calc_total() {
      var a = $("#total_pangkal").val();
      var b = $("#total_tahunan").val();
      var c = $("#total_anggota").val();
      var d = $("#total_gedung").val();
      var e = $("#total_perpus").val();
      var f = $("#total_ceps").val();
      $("#total").val(Number(a) + Number(b) + Number(c) + Number(d) + Number(e) + Number(f));
    }

    function reformatDate(dateStr) {
      dArr = dateStr.split("-"); // ex input "2010-01-18"
      return dArr[2] + "-" + dArr[1] + "-" + dArr[0]; //ex out: "18/01/10"
    }

    function pad(num, size) {
      var s = num + "";
      while (s.length < size) s = "0" + s;
      return s;
    }

    function load_quick_PaymentDetail(id) {
      $jq('#quick_PaymentDetail').modal('show');
      $('#errboxPaymentDetail').hide();

      var id_ip = id;
      $.ajax({
        url: '<?php echo site_url('admin/payment/ajax_detail') ?>/' + id,
        dataType: "json",
        type: "GET",
        async: true, //false
        data: {
          id: id_ip
        },
        success: function(jsonObject, status) {
          console.log(jsonObject);
          keyval = '';
          $.each(jsonObject.data[0], function(key, value) {
            keyval += '<div class="row"><div class="col-md-4"><b>' + key + '</b>:</div><div class="col-md-4" >' + value + '</div></div>';
          });
          $('#paymentDetailText').html(keyval);
        },
        error: function(jqXHR, exception) {
          console.log(jqXHR);
          var error_msg = '';
          if (jqXHR.status === 0) {
            error_msg = 'Not connect.\n Verify Network.';
          } else if (jqXHR.status == 403) {
            error_msg = 'Not authorized. [403]';
          } else if (jqXHR.status == 404) {
            error_msg = 'Requested page not found. [404]';
          } else if (jqXHR.status == 500) {
            error_msg = 'Internal Server Error [500].';
          } else if (exception === 'parsererror') {
            error_msg = 'Requested JSON parse failed.';
          } else if (exception === 'timeout') {
            error_msg = 'Time out error.';
          } else if (exception === 'abort') {
            error_msg = 'Ajax request aborted.';
          } else {
            var json = JSON.parse(jqXHR.responseText)
            if (json.message) {
              error_msg = json.message;
            } else {
              error_msg = '<br/>\n' + jqXHR.responseText;
            }
          }
          $('#errboxPaymentDetail').html('ERROR: ' + error_msg);
          $('#errboxPaymentDetail').show();
        }
      });


    }

    function load_quick_paymentCancel(id) {
      if (confirm('Apakah anda yakin akan cancel payment ini?\n\nPastikan lagi anda mengklik payment yang tepat')) {
        $.ajax({
          url: '<?php echo site_url('admin/payment/ajax_cancel/') ?>' + id,
          dataType: "json",
          type: "GET",
          async: true, //false
          data: {
            id: id
          },
          success: function(jsonObject, status) {
            console.log(jsonObject);
            alert(jsonObject.message);
            window.location.href = "<?php echo base_url(); ?>/admin/payment";
          },
          error: function(jqXHR, exception) {
            console.log(jqXHR);
            var error_msg = '';
            if (jqXHR.status === 0) {
              error_msg = 'Not connect.\n Verify Network.';
              //} else if (jqXHR.status == 403) {
              //	error_msg = 'Not authorized. [403]';
            } else if (jqXHR.status == 404) {
              error_msg = 'Requested page not found. [404]';
            } else if (jqXHR.status == 500) {
              error_msg = 'Internal Server Error [500].';
            } else if (exception === 'parsererror') {
              error_msg = 'Requested JSON parse failed.';
            } else if (exception === 'timeout') {
              error_msg = 'Time out error.';
            } else if (exception === 'abort') {
              error_msg = 'Ajax request aborted.';
            } else {
              var json = JSON.parse(jqXHR.responseText)
              if (json.message) {
                error_msg = json.message;
              } else {
                error_msg = '<br/>\n' + jqXHR.responseText;
              }
            }
            alert(error_msg);
          }
        });
      }


    }

    function load_quick_total(id, warga, total, pangkal, tahunan, anggota, gedung, perpus, ceps) {
      $jq('#quick_total').modal('show');
      $("#id_total").val(id);
      $("#total").val(total);
      $("#total_pangkal").val(pangkal);
      $("#total_tahunan").val(tahunan);
      $("#total_anggota").val(anggota);
      $("#total_gedung").val(gedung);
      $("#total_perpus").val(perpus);
      $("#total_ceps").val(ceps);
      if (warga == 1) {
        $('.is_wna').css('display', 'inline-flex');
      } else $('.is_wna').css('display', 'none');
    }

    function add_ip() {
      $("#no_kta").val('').trigger('change');
      $('#no_kta').empty().trigger("change");
      $("#ktaform")[0].reset();
      $jq('#quick_ip').modal('show');
      /*
      var id_ip  	=  id;
      $.ajax({
      	url: '<?php echo site_url('admin/members/get_pi_by_id') ?>',
      	dataType: "html",
      	type: "POST",
      	async: true,//false
      	data: {id:id_ip},
      	success: function(jsonObject,status) {
      		var x = JSON.parse(jsonObject);
      		console.log(x);
      		
      		$('#kta').val(x.kta);
      		$('#no_ip').val(x.noip);
      		$('#ip_type').val(x.sertid);
      		$('#from_ip').val(x.sk_from);
      		
      		$jq('#quick_ip').modal('show');
      		$jq("#id_ip").val(id);
      	}
      });*/


    }
  </script>


  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>

  <script>
    $(document).ready(function() {

      $("#tgl_period,#tgl_period2").datepicker({
        dateFormat: 'yy-mm-dd',
        changeMonth: true,
        changeYear: true,
        yearRange: "1940:<?php echo date('Y') + 5; ?>"
      });

      $('#no_kta').select2({
        width: '100%',
        ajax: {
          url: function(params) {
            return '<?php echo base_url(); ?>admin/members/ajax_member_search?id=' + params.term + '&page=' + params.page || 1;
          },
          processResults: function(data) {
            return {
              results: $.map(JSON.parse(data), function(item) {
                return {
                  text: item.no_kta,
                  id: item.no_kta
                }
              })
            };
          }
        }
      });
    });
  </script>

  <style>
    .red {
      color: red;
    }
  </style>