<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <!-- _ci_view: <?php echo $_ci_view; ?> -->
  <title><?php echo $title; ?></title>
  <?php
  $this->load->view('admin/common/meta_tags');
  $this->load->view('admin/common/before_head_close');
  ?>
  <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
  <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

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






  <script>
    $(function() {
      $("#from,#until").datepicker({
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

    function load_quick_update_bukti(id, user_id) {
      $('#quick_update_bukti').modal('show');
      $("#id_payid").val(id);
      $("#id_user_id").val(user_id);
    }

    function saveupdatebukti() {
      var id = $('#id_payid').val();
      var user_id = $('#id_user_id').val();

      if (typeof $('#bukti_upload')[0].files[0] === "undefined" || id == '' || user_id == '')
        alert('data gagal tersimpan');
      else {
        var formData = new FormData();
        formData.append('bukti', $('#bukti_upload')[0].files[0]);
        formData.append('id', id);
        formData.append('user_id', user_id);

        var dataHTML = 'not valid';
        $.ajax({
          url: '<?php echo site_url('admin/members/ajax_update_bukti_upload') ?>',
          type: 'POST',
          data: formData,
          processData: false,
          contentType: false,
          success: function(jsonObject, status) {
            console.log(jsonObject);
            if ((jsonObject == '1')) {
              location.reload();
            } else alert(jsonObject);

          }
        });

        $('#quick_update_bukti').modal('toggle');
      }

    }

    function load_quick_payment_detail(p1, p2, p3, p4, p5, p6, p7) {
      $('#quick_detail_payment').modal('show');
      $("#p1").html(p1);
      $("#p2").html(p2);
      $("#p3").html(p3);
      $("#p4").html(p4);
      $("#p5").html(p5);
      $("#p6").html(p6);
      $("#p7").html(p7);
    }

    function load_quick_PaymentDetail(id) {
      $('#quick_PaymentDetail').modal('show');
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
            error_msg = 'Requested resource not found. [404]';
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


    // set payment function valid / not valid
    function savesetmember() {
      var payment = $('#payment').val();
      var id_c = $('#id_c').val();
      var dataHTML = 'not valid';
      $.ajax({
        url: '<?php echo site_url('admin/members/setpayment') ?>',
        dataType: "html",
        type: "POST",
        async: true, //false
        data: {
          id: id_c,
          payment: payment
        },
        success: function(jsonObject, status) {
          console.log(jsonObject);
          if ((jsonObject != 'not valid')) {
            dataHTML = jsonObject;
          }

          if (dataHTML == 'not valid')
            alert('not valid');
          else {
            alert('data berhasil di update');
            window.location.href = "<?php echo base_url(); ?>admin/members/finance";
          }
        }
      });

      //$('#quick_profile').modal('hide');
      //$('#quick_payment').modal('toggle');
      //location.reload();
    }


    function export_member() {
      var filter_status = $('#filter_status').val();
      var filter_type = $('#filter_type').val();
      var firstname = $('#firstname').val();
      var email = $('#email').val();
      var va = $('#va').val();
      var filter_kolektif = $('#filter_kolektif').val();
      var dataHTML = 'not valid';
      //console.log(filter_kolektif);
      if (filter_kolektif == undefined) filter_kolektif = '';

      window.open("<?php echo site_url('admin/members/export_finance') ?>?filter_status=" + filter_status + "&email=" + email + "&filter_type=" + filter_type + "&firstname=" + firstname + "&va=" + va + "&filter_kolektif=" + filter_kolektif, '_blank');



    }

    $(function() {
      $(document).tooltip();
    });
  </script>
  <style>
    label {
      display: inline-block;
      width: 5em;
    }
  </style>
</head>

<body class="skin-blue">
  <?php
  $this->load->view('admin/common/after_body_open');
  $this->load->view('admin/common/header');
  ?>
  <div class="wrapper row-offcanvas row-offcanvas-left">
    <?php $this->load->view('admin/common/left_side'); ?>
    <!-- Right side column. Contains the navbar and content of the page -->
    <aside class="right-side">
      <!-- Content Header (Page header) -->
      <section class="content-header">
        <h1> Members Management
          <!--<small>advanced tables</small>-->
        </h1>
        <ol class="breadcrumb">
          <li><a href="<?php echo base_url('admin/dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
          <!--<li><a href="#">Examples</a></li>-->
          <li class="active">Validasi (Finance)</li>
        </ol>
      </section>

      <!-- Main content -->
      <section class="content">



        <div class="row">
          <div class="col-xs-12">
            <div class="box">
              <div class="box-header">
                <h3 class="box-title">Validasi Pembayaran (Finance)</h3>
                <!--Pagination-->
                <div class="paginationWrap"> <?php echo ($result) ? $links : ''; ?></div>
              </div>

              <!-- /.box-header -->
              <div class="box-body table-responsive" style="overflow-x:scroll;">
                <?php $this->load->view('admin/common/member_quick_search_bar_non_kta_finance'); ?>
                <div class="clearfix text-right" style="padding:10px;">
                  <?php
                  //if(!isset($_GET['industry_ID'])):
                  ?>
                  Total Records: <strong><?php echo $total_rows; ?>

                    <?php //endif;
                    ?>
                  </strong> </div>
                <table id="example2" class="table table-bordered table-hover">
                  <thead>
                    <tr>
                      <th>Tipe</th>
                      <th>VA</th>
                      <th width="1">is upload to mandiri?</th>
                      <th>Nama/Email</th>
                      <th>SIP</th>
                      <th>Period</th>
                      <th>Kolektif</th>
                      <th align="center">Deskripsi</th>
                      <th>Total Transfer</th>
                      <th>Bukti Transfer</th>
                      <th>Status</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    if ($result):
                      foreach ($result as $row):
                        $json_row = array();
                        $total_posted_jobs = 0;

                        /*$json_row['email'] = $row->email;
						$json_row['email'] = $row->email;
						$json_row['email'] = $row->email;
						$json_row['email'] = $row->email;
						$json_row['email'] = $row->email;*/


                        $json_string1 = str_replace('"', "dquote", json_encode($row));
                        $json_string2 = str_replace("'", "squote", $json_string1);
                        $json_string = str_replace("/", "slash", $json_string2);

                        // TODO: FIXME - Wow! SQL execution didalam row iteration of a table rendering. 
                        // Dengan query yg hampir sama yg dilakukan sudah dilakukan di controller
                        $temp_user_transfer = $this->main_mod->msrquery(
                          'select x.pay_type as paytype,x.atasnama as payname,x.tgl as paydate,x.description paydesc,x.bukti as payfile, ' .
                            ' x.status as paystatus,x.iuranpangkal as payiuranpangkal,x.iurantahunan as payiurantahunan, ' .
                            ' x.sukarelaanggota as paysukarelaanggota, x.sukarelagedung as paysukarelagedung,x.sukarelaperpus as paysukarelaperpus, ' .
                            ' x.sukarelaceps as paysukarelaceps, x.sukarelatotal as paysukarelatotal, x.id as payid ' .
                            ' from user_transfer x where x.id=' . $row->ut_id
                        )->row();
                        // Again? :-(
                        $temp_user_cert = $this->main_mod->msrquery(
                          'select lic_num as sip_lic_num, startyear as sip_startyear, endyear as sip_endyear ' .
                            'from user_cert where user_id=' . $row->ID . ' and status=2  ' .
                            ' order by endyear desc, ip_tipe desc limit 1'
                        )->row();


                    ?>
                        <tr id="row_<?php echo $row->ID;  // ER: Ini untuk apa? ID disini adalah user_id 
                                    ?>" data-utid="<?php echo $row->ut_id; ?>" data-payid="<?php echo $temp_user_transfer->payid ?>">
                          <!--<td valign="middle"><?php //echo $row->no_kta;//date_formats($row->dated, 'd/m/Y');
                                                  ?><br />
                  </td>-->
                          <td valign="middle">
                            <?php
                            if ($temp_user_transfer->paytype == '0' || $temp_user_transfer->paytype == '1')
                              echo '<span class="label label-success">REG</span>';
                            else if ($temp_user_transfer->paytype == '2')
                              echo '<span class="label label-warning">HER</span>';
                            else if ($temp_user_transfer->paytype == '3')
                              echo '<span class="label label-primary">FAIP</span>';
                            else if ($temp_user_transfer->paytype == '4')
                              echo '<span class="label label-primary">FAIP</span>';
                            else if ($temp_user_transfer->paytype == '5')
                              echo '<span class="label label-danger">STRI</span>';
                            else if ($temp_user_transfer->paytype == '6')
                              echo '<span class="label label-danger">PKB</span>';
                            else if ($temp_user_transfer->paytype == '7')
                              echo '<span class="label label-danger">PKB</span>';
                            ?>

                          </td>

                          <td valign="middle"><?php echo $row->va; ?></td>
                          <td valign="middle"><?php echo ($row->is_upload_mandiri == '1') ? 'Yes' : 'No'; ?></td>

                          <td valign="middle"><strong><a href="<?php echo base_url('admin/members/details/' . $row->ID); ?>">
                                <?php echo $row->firstname . ' ' . $row->lastname; ?></a></strong><br />
                            <?php echo $row->email; ?><br />
                            Gender: <?php echo $row->gender; ?>, TL: <?php echo $row->dob; ?>
                          </td>
                          <td valign="middle"><?php echo (isset($temp_user_cert->sip_lic_num) ? $temp_user_cert->sip_lic_num . '<br />(' . $temp_user_cert->sip_startyear . ' sampai ' . $temp_user_cert->sip_endyear . ')' : ""); ?> </td>
                          <td valign="middle">

                            <?php echo $row->from_date; ?> - <?php echo $row->thru_date; ?>
                          </td>
                          <td valign="middle"><?php echo $row->kolektif_name; ?></td>
                          <td valign="middle">
                            <?php
                            if (! empty($temp_user_transfer->paydesc)) {
                              echo preg_replace('/\s+?(\S+)?$/', '', substr($temp_user_transfer->paydesc, 0, 61));
                            }
                            if (strlen($temp_user_transfer->paydesc) > 60) { ?>
                              <a id='paydesc_<?php echo $row->ID; ?>' href="javascript:alert($('#paydesc_<?php echo $row->ID; ?>').attr('title'));" title="<?php echo $temp_user_transfer->paydesc; ?>"><span class="label label-info">...</span></a>
                          </td>
                        <?php } ?>
                        <td valign="middle">

                          <a href="javascript:;" class="btn btn-primary btn-xs" onClick="load_quick_payment_detail('<?php echo $temp_user_transfer->payiuranpangkal; ?>','<?php echo $temp_user_transfer->payiurantahunan; ?>','<?php echo $temp_user_transfer->paysukarelaanggota; ?>','<?php echo $temp_user_transfer->paysukarelagedung; ?>','<?php echo $temp_user_transfer->paysukarelaperpus; ?>','<?php echo $temp_user_transfer->paysukarelaceps; ?>','<?php echo $temp_user_transfer->paysukarelatotal; ?>');"><?php echo $temp_user_transfer->paysukarelatotal; ?></a>


                        </td>

                        <?php /*?>
				  <td valign="middle"><a class="btn btn-primary btn-xs" href="<?php echo base_url('admin/posted_jobs/jobs_by_company/'.$row->ID);?>" target="_blank">View (<?php echo $total_posted_jobs;?>)</a></td>
                  <td align="center" valign="middle"><a href="<?php echo base_url('admin/members/details/'.$row->ID);?>" target="_blank">
                    <?php $image_name = ($row->company_logo)?$row->company_logo:'no_logo.jpg';?>
                    <img src="<?php echo base_url('public/uploads/employer/thumb/'.$image_name);?>" mar-height="60"/><br />
                    <?php echo ($row->company_name)?$row->company_name:' - ';?></a></td>
                  <td valign="middle"><?php
				  		if($row->top_employer=='yes')
							$top_class_label = 'success';
						else
							$top_class_label = 'warning';
				  ?>
                  
                    <a onClick="update_top_employer_status(<?php echo $row->ID;?>);" href="javascript:;" id="te_<?php echo $row->ID;?>"> <span class="label label-<?php echo $top_class_label;?>"><?php echo $row->top_employer;?></span> </a></td>
                    
                  <?php */ ?>
                        <td valign="middle" data-colname="bukti">
                          <?php if (! empty($temp_user_transfer->payfile) && trim($temp_user_transfer->payfile) !== '-') { ?>
                            <?php echo (! empty($temp_user_transfer->payname)) ? $temp_user_transfer->payname . '<br/>' : ''; ?> <br /> <?php echo (! empty($temp_user_transfer->paydate)) ? $temp_user_transfer->paydate . '<br />' : ''; ?>
                            <a target="_blank" class="btn btn-primary btn-xs" href="<?php echo base_url() . 'assets/uploads/pay/' . $temp_user_transfer->payfile; ?>">download</a> <br /> <?php //echo $row->paydesc;
                                                                                                                                                                                          ?>
                          <?php } ?>
                        </td>
                        <td valign="middle" data-paystatus="<?php echo $temp_user_transfer->paystatus; ?>">
                          <?php
                          $status = '';
                          if ($temp_user_transfer->paystatus == '1') {
                            $class_label = 'success';
                            $status = 'Valid';
                          } elseif ($temp_user_transfer->paystatus == '0') {
                            $class_label = 'warning';
                            $status = 'Please Confirm';
                          } elseif ($temp_user_transfer->paystatus == '2') {
                            $class_label = 'danger';
                            $status = 'Not Valid';
                          } else $class_label = 'warning';
                          ?>

                          <a onClick="update_status(<?php echo $row->ID; ?>);" href="javascript:;" id="sts_<?php echo $row->ID; ?>"> <span class="label label-<?php echo $class_label; ?>"><?php echo $status; ?></span> </a>
                        </td>


                        <td valign="middle" data-colname="Action">
                          <a href="javascript:;" class="btn btn-primary btn-xs" onclick="load_quick_PaymentDetail('<?php echo $row->ut_id; ?>');">Detail</a>
                          <?php
                          $arr_paystatuses = array("0", "1", "2", "9");
                          if (
                            in_array($temp_user_transfer->paystatus, $arr_paystatuses)
                            && $this->session->userdata('admin_id') == "780" || $this->session->userdata('admin_id') == "706" || $this->session->userdata('admin_id') == "672"
                            || $this->session->userdata('admin_id') == "731" || $this->session->userdata('admin_id') == "785" || $this->session->userdata('admin_id') == "782"
                            || $this->session->userdata('admin_id') == "673"
                          ) { ?>
                            <a href="javascript:;" class="btn btn-primary btn-xs" onClick="load_quick_profile_view9('<?php echo $temp_user_transfer->payid; ?>');">Set Status</a>
                          <?php } ?>

                          <?php if (isRoot()) { ?>
                            <br />
                            <a href="javascript:;" class="btn btn-primary btn-xs" onClick="load_quick_update_bukti('<?php echo $temp_user_transfer->payid; ?>','<?php echo $row->ID; ?>');">
                              Update Bukti</a>
                          <?php } ?>

                        </td>


                        <?php //--------------------------------------
                        /*
          <td valign="middle">
			<?php if ( $this->session->userdata('admin_id')!= "706" || $this->session->userdata('admin_id')!= "785" ) {  
			          // wafiqta.dzi15@gmail.com ;	?>	
				  <a href="javascript:;" class="btn btn-primary btn-xs" onClick="load_quick_profile_view9('<?php echo $temp_user_transfer->payid;?>');">Set Status</a>               
			<?php } ?>	  
				  <?php if(isRoot()){ ?>
				         <br />
				          <a href="javascript:;" class="btn btn-primary btn-xs" onClick="load_quick_update_bukti('<?php echo $temp_user_transfer->payid;?>','<?php echo $row->ID;?>');">
				            Update Bukti</a>
				   <?php } ?>
				  
                  </td>
 //---------------------------------------------------------------------------------                 
*/
                        ?>
                        </tr>
                      <?php endforeach;
                    else: ?>
                      <tr>
                        <td colspan="100%" align="center" class="text-red">No Record found!</td>
                      </tr>
                    <?php
                    endif;
                    ?>
                  </tbody>
                  <tfoot>
                  </tfoot>
                </table>
              </div>

              <!--Pagination-->
              <div class="paginationWrap"> <?php echo ($result) ? $links : ''; ?> </div>

              <!-- /.box-body -->
            </div>
            <!-- /.box -->

            <!-- /.box -->
          </div>
        </div>
      </section>
      <!-- /.content -->
    </aside>


    <div class="modal fade" id="quick_update_bukti">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">Update Bukti Transfer <span id="j_comp_name_email" style="font-weight:bold;"></span></h4>
          </div>
          <div class="modal-body">
            <!-- /.box-header -->
            <!-- form start -->
            <div id="errbo_email" style="display:none;"></div>
            <div class="box-body" id="j_box_email">
              <table width="95%" border="0">

                <tr>
                  <td><strong><span class="form-group">Bukti Transfer</span></strong></td>
                  <td id="">
                    <div class="col-sm-12">
                      <span style="color:red">(Max. 700KB, image atau PDF)</span>
                      <div class="form-group">
                        <div id="avatar">
                          <?php //echo ($row->id_file!='')?"<a href='".base_url()."/assets/uploads/".$row->id_file."' target='_blank' class='ava_discus'>".$row->id_file."</a>":''; 
                          ?>
                        </div>
                        <br /><br />
                        <div id="errUpload" class="red"></div>
                        <input type="file" name="bukti_upload" id="bukti_upload">
                      </div>
                    </div>


                  </td>
                </tr>

                <tr>
                  <td><strong><span class="form-group"></span></strong></td>
                  <td id=""><button type="button" class="btn btn-default" onclick="saveupdatebukti()" data-dismiss="modal">Save</button>
                    <input type="hidden" name="id_payid" id="id_payid" value="" />
                    <input type="hidden" name="id_user_id" id="id_user_id" value="" />
                  </td>
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


    <div class="modal fade" id="quick_payment">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">Set Status Pembayaran Test <span id="comp_name" style="font-weight:bold;"></span></h4>
          </div>
          <div class="modal-body">
            <!-- /.box-header -->
            <!-- form start -->
            <div class="box-body">
              <table width="95%" border="0">
                <tr>
                  <td width="25%"><strong><span class="form-group">Status Pembayaran</span></strong></td>
                  <td id="">
                    <select id="payment" name="payment" class="form-control input-md" required="">
                      <option value="">--Choose--</option>
                      <option value="1"> Valid</option>
                      <option value="2"> Tidak Valid</option>
                    </select>
                  </td>
                </tr>
                <tr>
                  <td><strong><span class="form-group"></span></strong></td>
                  <td id=""><button type="button" class="btn btn-default" onclick="savesetmember()" data-dismiss="modal">Save</button><input type="hidden" name="id_c" id="id_c" value="" /></td>
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

    <div class="modal fade" id="quick_detail_payment">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">Detail Transfer <span id="comp_name" style="font-weight:bold;"></span></h4>
          </div>
          <div class="modal-body">
            <!-- /.box-header -->
            <!-- form start -->
            <div class="box-body">
              <table class="table" style="font-size:14px;font-weight:bold;">
                <tr>
                  <td>1.</td>
                  <td>Iuran Pangkal</td>
                  <td>:</td>
                  <td>Rp. <span id="p1"></span></td>
                  <tr />
                <tr>
                  <td>2.</td>
                  <td>Iuran Tahunan</td>
                  <td>:</td>
                  <td>Rp. <span id="p2"></span></td>
                  <tr />
                <tr>
                  <td>3.</td>
                  <td>Sumbangan Sukarela</td>
                  <td></td>
                  <td></td>
                  <tr />
                <tr>
                  <td></td>
                  <td>a. Keanggotaan </td>
                  <td>: </td>
                  <td>Rp. <span id="p3"></span></td>
                  <tr />
                <tr>
                  <td></td>
                  <td>b. Gedung </td>
                  <td>: </td>
                  <td>Rp. <span id="p4"></span></td>
                  <tr />
                <tr>
                  <td></td>
                  <td>c. Perpustakaan </td>
                  <td>: </td>
                  <td>Rp. <span id="p5"></span></td>
                  <tr />
                <tr>
                  <td></td>
                  <td>d. CEPS </td>
                  <td>: </td>
                  <td>Rp. <span id="p6"></span></td>
                  <tr />
                <tr>
                  <td>4. </td>
                  <td>Total 1+2+3(a+b+c+d)</td>
                  <td>:</td>
                  <td>Rp. <span id="p7"></span></td>
                  <tr />
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

  <script>
    $(document).ready(function() {
      // Hide spinner setelah seluruh konten siap
      $("#loadingOverlay").fadeOut();
    });
  </script>
  <?php $this->load->view('admin/common/footer'); ?>