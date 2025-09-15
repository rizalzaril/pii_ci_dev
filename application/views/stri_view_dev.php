<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <!-- _ci_view: <?php echo $_ci_view; ?> -->
  <title><?php echo $title; ?></title>
  <?php $this->load->view('admin/common/meta_tags'); ?>
  <?php $this->load->view('admin/common/before_head_close'); ?>

  <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
  <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

  <script>
    $(function() {
      $("#from,#until").datepicker({
        dateFormat: 'dd-mm-yy',
        changeMonth: true,
        changeYear: true,
        yearRange: "<?php echo date('Y'); ?>:2050"
      });
      $("#tgl_sk").datepicker({
        dateFormat: 'dd-mm-yy',
        changeMonth: true,
        changeYear: true,
        yearRange: "1990:2050"
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

    function pad(num, size) {
      var s = num + "";
      while (s.length < size) s = "0" + s;
      return s;
    }

    function reformatDate(dateStr) {
      dArr = dateStr.split("-"); // ex input "2010-01-18"
      return dArr[2] + "-" + dArr[1] + "-" + dArr[0]; //ex out: "18/01/10"
    }

    function add_stri() {
      $("#no_kta").val('').trigger('change');
      $("#ktaform")[0].reset();
      $('#quick_stri_edit').modal('show');
    }

    function edit_stri(id) {
      $("#no_kta").val('').trigger('change');
      $("#ktaform")[0].reset();

      $.ajax({
        url: '<?php echo site_url('admin/members/getstri') ?>',
        dataType: "html",
        type: "POST",
        async: true, //false
        data: {
          id: id
        },
        success: function(jsonObject, status) {
          //console.log(JSON.parse(jsonObject));

          var data = JSON.parse(jsonObject);


          var newOption = new Option(pad(data.no_kta, 6), pad(data.no_kta, 6), false, false);
          $('#no_kta').append(newOption).trigger('change');

          $("#id_stri").val(data.id);
          $("#no_kta").val(pad(data.no_kta, 6)).trigger('change');
          $("#skip_id").val(pad(data.skip_id, 6));
          console.log(data.id);
          $("#stri_cabang").val(pad(data.stri_code_wilayah, 4));
          $("#stri_bk").val(pad(data.stri_code_bk_hkk, 2));
          $("#nama_stri").val(data.add_name);
          $("#stri_type").val(data.certificate_type);
          $("#warga").val(data.warga);
          $("#stri_tipe").val(data.stri_tipe);
          $("#stri_kp").val(pad(data.stri_pm, 2));
          $("#tgl_sk").val(reformatDate(data.stri_from_date));
          $("#num").val(data.stri_id);
          $("#stri_type").attr('disabled', false);



          //$("#stri_type").css("pointer-events","none");
          //$("#id_pay").val(payid);

          /*var res = 0;
          if(type!='') res = type.charAt(0);
          let dateObj = new Date();
          let month = dateObj.getMonth()+1;
          let day = String(dateObj.getDate()).padStart(2, '0');
          let year = dateObj.getFullYear();
          let output = pad(day,2)+ '-'+ pad(month,2)  + '-'+  year;
          //console.log(tgl_sk);
          //$("#stri_kp").val('00');
          if(tgl_sk=="")
          	$("#tgl_sk").val(output);
          else
          	$("#tgl_sk").val(tgl_sk);*/

          $('#quick_stri_edit').modal('show');

        }
      });
    }

    function delete_stri(id) {
      if (confirm('Apakah anda yakin?')) {
        $.ajax({
          url: '<?php echo site_url('admin/members/deletestri') ?>',
          dataType: "html",
          type: "POST",
          async: true, //false
          data: {
            id: id
          },
          success: function(jsonObject, status) {
            console.log(jsonObject);
            if ((jsonObject != 'not valid')) {
              dataHTML = jsonObject;
            }

            if (dataHTML == 'not valid')
              alert('not valid');
            else {
              window.location.href = "<?php echo base_url(); ?>admin/members/stri";
            }

          }
        });
      }
    }

    function savesetmember() {
      var cabang = $('#cabang').val();
      var bk = $('#bk').val();
      var from = $('#from').val();
      var until = $('#until').val();
      var id_c = $('#id_c').val();
      var dataHTML = 'not valid';
      $.ajax({
        url: '<?php echo site_url('admin/members/setmember') ?>',
        dataType: "html",
        type: "POST",
        async: true, //false
        data: {
          id: id_c,
          cabang: cabang,
          bk: bk,
          from: from,
          until: until
        },
        success: function(jsonObject, status) {
          console.log(jsonObject);
          if ((jsonObject != 'not valid')) {
            dataHTML = jsonObject;
          }

          if (dataHTML == 'not valid')
            alert('not valid');
        }
      });

      //$('#quick_profile').modal('hide');
      $('#quick_profile').modal('toggle');
      location.reload();
    }

    function savesetstri_1() {
      var no_kta = $('#id_kta').val();
      var nama_stri = $('#id_nama').val();
      var id_stri = $('#id_id').val();

      var dataHTML = 'not valid';
      $.ajax({
        url: '<?php echo site_url('admin/members/setstriedit_2') ?>',
        dataType: "html",
        type: "POST",
        async: true, //false
        data: {
          id: id_stri,
          no_kta: no_kta,
          add_name: nama_stri
        },
        success: function(jsonObject, status) {
          console.log(jsonObject);
          if ((jsonObject != 'not valid')) {
            dataHTML = jsonObject;
          }

          if (dataHTML == 'not valid')
            alert('not valid');
          else {
            //$('#quick_stri').modal('toggle');
            window.location.href = "<?php echo base_url(); ?>admin/members/stri";
          }


        }
      });

    }

    function savesetstri() {
      var tgl_sk = $('#tgl_sk').val();

      var no_kta = $('#no_kta').val();
      var skip_id = $('#skip_id').val();
      var id_stri = $('#id_stri').val();

      var stri_cabang = $('#stri_cabang').val();
      var stri_bk = $('#stri_bk').val();
      var stri_type = $('#stri_type').val();
      var stri_kp = $('#stri_kp').val();
      var stri_subbk = $('#stri_subbk').val();
      var warga = $('#warga').val();
      var stri_tipe = $('#stri_tipe').val();
      var nama_stri = $('#nama_stri').val();

      var instansi = $('#instansi').val();
      var from = tgl_sk; // $('#from_stri').val();
      var until = $('#until_stri').val();
      var id_stri = $('#id_stri').val();
      var no_kta = $('#no_kta').val();
      var skip_id = $('#skip_id').val();

      var num = $('#num').val();

      var dataHTML = 'not valid';
      $.ajax({
        url: '<?php echo site_url('admin/members/setstriedit') ?>',
        dataType: "html",
        type: "POST",
        async: true, //false
        data: {
          id: id_stri,
          tgl_sk: tgl_sk,
          stri_type: stri_type,
          from: from,
          until: until,
          instansi: instansi,
          stri_bk: stri_bk,
          stri_kp: stri_kp,
          stri_subbk: stri_subbk,
          stri_cabang: stri_cabang,
          no_kta: no_kta,
          num: num,
          warga: warga,
          stri_tipe: stri_tipe,
          nama_stri: nama_stri,
          skip_id: skip_id
        },
        success: function(jsonObject, status) {
          console.log(jsonObject);
          if ((jsonObject != 'not valid')) {
            dataHTML = jsonObject;
          }

          if (dataHTML == 'not valid')
            alert('not valid');
          else {
            //$('#quick_stri').modal('toggle');
            window.location.href = "<?php echo base_url(); ?>admin/members/stri";
          }


        }
      });

      //$('#quick_profile').modal('hide');
      //$('#quick_stri').modal('toggle');
      //location.reload();

    }

    function export_member() {
      var filter_cab = $('#filter_cab').val();
      var filter_bk = $('#filter_bk').val();
      var filter_hkk = $('#filter_hkk').val();
      var filter_program = $('#filter_program').val();
      var stri_period_start = $('#stri_period_start').val();
      var stri_period_end = $('#stri_period_end').val();
      var filter_status = $('#filter_status').val();
      var filter_type = $('#filter_type').val();
      var firstname = $('#firstname').val();
      var nomor = $('#nomor').val();
      var kta = $('#kta').val();
      var dataHTML = 'not valid';
      //console.log(filter_kolektif);
      //if(filter_kolektif== undefined) filter_kolektif ='';

      window.open("<?php echo site_url('admin/members/export_stri_2') ?>?filter_cab=" + filter_cab + "&filter_bk=" + filter_bk + "&filter_hkk=" + filter_hkk + "&filter_program=" + filter_program + "&stri_period_start=" + stri_period_start + "&stri_period_end=" + stri_period_end + "&filter_status=" + filter_status + "&filter_type=" + filter_type + "&firstname=" + firstname + "&nomor=" + nomor + "&kta=" + kta, '_blank');



    }

    //---------------------------------------------------------- Tambahan by IP untuk upload E-STRI --------------------------------
    function load_upload_estri_view(no_kta) {
      var id_kta = no_kta;

      $('#id_f').val(id_kta);
      $('#id_ff').val(id_kta);
      $('#quick_upload_estri').modal('show');

    }

    function load_edit_estri(id, no_kta, add_name) {

      var idd = id;
      var nokta = no_kta;
      var nama = add_name;
      var id_proses = "Simpan";

      $('#id_proses').val(id_proses);
      $('#id_id').val(idd);
      $('#id_nama').val(nama);
      $('#id_kta').val(nokta);

      $('#quick_edit_estri').modal('show');
    }


    //------------------------------------------------------------------------------------------
  </script>


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
        <h1> Members Management
          <!--<small>advanced tables</small>-->
        </h1>
        <ol class="breadcrumb">
          <li><a href="<?php echo base_url('admin/dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
          <!--<li><a href="#">Examples</a></li>-->
          <li class="active">Manage Members</li>
        </ol>
      </section>

      <!-- Main content -->
      <section class="content">
        <div class="row">
          <div class="col-xs-12">
            <div class="box">
              <div class="box-header">
                <h3 class="box-title">STRI</h3>
                <!--Pagination-->
                <div class="paginationWrap"> <?php echo ($result) ? $links : ''; ?></div>
              </div>

              <!-- /.box-header -->
              <div class="box-body table-responsive">
                <?php $this->load->view('admin/common/member_quick_search_bar_stri'); ?>
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
                      <th>Tanggal</th>
                      <th>Name</th>
                      <th>Type</th>
                      <th>Nomor</th>
                      <th>No. STRI</th>
                      <th align="center">No. KTA</th>
                      <th>SK</th>

                      <th>Status</th>

                      <?php

                      if ($this->session->userdata('is_stri_approval') == "1") {

                      ?>

                        <th>Status Approval</th>

                      <?php

                      }

                      ?>

                      <!--<th>Quick Edit</th>-->
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    if ($result):
                      foreach ($result as $row):
                        $json_row = array();
                        $total_posted_jobs = 0; //$this->posted_jobs_model->count_records('pp_post_jobs','employer_id',$row->ID);

                        /*$json_row['email'] = $row->email;
						$json_row['email'] = $row->email;
						$json_row['email'] = $row->email;
						$json_row['email'] = $row->email;
						$json_row['email'] = $row->email;*/


                        $json_string1 = str_replace('"', "dquote", json_encode($row));
                        $json_string2 = str_replace("'", "squote", $json_string1);
                        $json_string = str_replace("/", "slash", $json_string2);
                    ?>
                        <tr id="row_<?php echo $row->ID; ?>">
                          <!--<td valign="middle"><?php //echo $row->no_kta;//date_formats($row->dated, 'd/m/Y');
                                                  ?><br />
                  <?php //echo ($row->ip_address)?'<a href="http://domaintools.com/'.$row->ip_address.'" target="_blank">'.$row->ip_address.'</a>':'';
                  ?>
                  </td>-->
                          <td valign="middle"><?php echo $row->tgl; ?></td>
                          <td valign="middle"><a href="<?php echo base_url('admin/members/details/' . $row->ID); ?>"><?php echo $row->add_name; ?></a></td>
                          <td valign="middle">
                            <?php
                            if ($row->certificate_type == 1)
                              echo 'IPP';
                            else if ($row->certificate_type == 2)
                              echo 'IPM';
                            else if ($row->certificate_type == 3)
                              echo 'IPU';
                            ?>
                          </td>
                          <td><?= str_pad($row->stri_id, 7, '0', STR_PAD_LEFT) ?></td>
                          <td valign="middle"><?php echo ($row->certificate_type != "" ? $row->certificate_type : "0") . '.' . ($row->stri_code_bk_hkk == "" ? "000" : str_pad($row->stri_code_bk_hkk, 3, '0', STR_PAD_LEFT)) . '.' . str_pad($row->th, 2, '0', STR_PAD_LEFT) . '.' . $row->warga . '.' . $row->stri_tipe . '.' . str_pad($row->stri_id, 8, '0', STR_PAD_LEFT); ?></td>
                          <td id="nomorkta" valign="middle"><?php echo str_pad($row->no_kta, 6, '0', STR_PAD_LEFT); ?></td>
                          <td valign="middle"><?php echo $row->stri_sk; ?></td>

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

                          <td valign="middle">

                            <?php
                            $status = '';

                            if ($row->stri_thru_date >= date('Y-m-d')) {
                              $class_label = 'success';
                              $status = 'Active';
                            } else {
                              $class_label = 'danger';
                              $status = 'Not Active';
                            }
                            ?>

                            <a onClick="update_status(<?php echo $row->ID; ?>);" href="javascript:;" id="sts_<?php echo $row->ID; ?>"> <span class="label label-<?php echo $class_label; ?>"><?php echo $status; ?></span> </a>

                            <?php
                            /*$status = '';
				  		if($row->sts=='1'){
							$class_label = 'success';
							 $status = 'Active';
						}
						elseif($row->sts=='0'){
							$class_label = 'danger';
							 $status = 'Not Active';
						}
						else
							$class_label = 'warning';*/
                            ?>

                            <!-- <a onClick="update_status(<?php //echo $row->ID;
                                                            ?>);" href="javascript:;" id="sts_<?php //echo $row->ID;
                                                                                              ?>"> <span class="label label-<?php //echo $class_label;
                                                                                                                                                  ?>"><?php //echo $status; 
                                                                                                                                                                            ?></span> </a>-->

                          </td>

                          <?php

                          if ($this->session->userdata('is_stri_approval') == "1") {

                          ?>

                            <td valign="middle">

                              <?php
                              $temp_2 = $this->main_mod->msrquery('select id from user_approval where type_app=2 and faip_id=' . $row->id)->num_rows();
                              if ($row->is_publish == '0' && $temp_2 == 0 && $status == 'Active') {
                                $class_label = 'success';
                              ?>
                                <a href="<?php echo base_url('admin/members/set_stri_approval/' . $row->id); ?>" onclick="return confirm('Apakah anda yakin?')"> <span class="btn btn-primary btn-xs">Approval E-STRI</span> </a>
                              <?php
                              } else if ($row->is_publish == '0' && $temp_2 > 0) {
                              ?>

                                <a href="javascript:;"> <span class="label label-warning">Waiting for Approval</span> </a>

                              <?php
                              } else if ($row->is_publish == '2' && $temp_2 > 0) {
                              ?>

                                <a href="javascript:;"> <span class="label label-danger">Rejected</span> </a>

                              <?php
                              } else if ($row->is_publish == '1' && $status == 'Active') {
                              ?>
                                <a href="<?php echo base_url('admin/members/download_stri_ttd/' . $row->ID); ?>" class="btn btn-primary btn-xs">Download E-STRI</a>
                              <?php
                              }

                              /*if(($this->session->userdata('admin_id')=="676" && $row->ishkk=='9') || $this->session->userdata('type')=="0"){
						?>
						<a href="<?php echo base_url('admin/members/download_stri_ttd/'.$row->ID);?>" class="btn btn-primary btn-xs">Download E-STRI</a>
						<?php
					}*/


                              ?>








                              <!--<a onClick="delete_stri(<?php //echo $row->id;
                                                          ?>);" href="javascript:;" class="btn btn-primary btn-xs">Delete</a>-->

                            </td>

                          <?php

                          }

                          ?>

                          <?php /*?>
                  <td valign="middle">
				  <a href="javascript:;" class="btn btn-primary btn-xs" onClick="load_quick_profile_view2('<?php echo $row->ID;?>');">Set Member</a> 
				  
				  <a href="javascript:;" onClick="load_quick_job_view('<?php echo $row->ID;?>', '<?php echo $row->company_name;?>');" class="btn btn-primary btn-xs">Job View</a>
                  
                  </td>
				  <?php */ ?>
                          <td valign="middle">
                            <?php /*?><a href="#<?php //echo base_url('admin/members/update/'.$row->ID);?>" class="btn btn-primary btn-xs">Edit Member</a><br />
				 
				  <a target="_blank" href="<?php echo base_url('admin/members/login/'.$row->ID);?>" class="btn btn-primary btn-xs" style="margin:1px;">Login as Member</a><br />
				  <a href="javascript:delete_employer(<?php echo camelize($row->ID);?>);" class="btn btn-danger btn-xs">Delete Member</a>
				   <?php */ ?>

                            <?php
                            if ($this->session->userdata('type') != "11") {
                              if ($this->session->userdata('admin_id') != "782") {
                                if ($row->certificate_type == 0) {
                            ?>
                                  <!-- 				   
				   <a href="<?php echo base_url('admin/members/download_stri_old/' . $row->ID); ?>" class="btn btn-primary btn-xs">Download</a> 
-->
                                <?php
                                } else {
                                ?>
                                  <!--				   
				   <a href="<?php echo base_url('admin/members/download_stri/' . $row->ID); ?>" class="btn btn-primary btn-xs">Download</a> 
-->
                            <?php
                                }
                              }
                            }
                            ?>

                            <?php
                            if ($this->session->userdata('is_stri_approval') != "1") {
                              if ($this->session->userdata('admin_id') == "676" || $this->session->userdata('admin_id') == "673" || $this->session->userdata('admin_id') == "756"  || $this->session->userdata('admin_id') == "777"  || $this->session->userdata('admin_id') == "775" || $this->session->userdata('type') == "0") {
                            ?>
                                <a href="<?php echo base_url('admin/members/download_stri_ttd/' . $row->ID); ?>" class="btn btn-primary btn-xs">Download E-STRI</a>
                              <?php

                              }
                            }

                            if ($this->session->userdata('type') == "0" || $this->session->userdata('admin_id') == "673" || $this->session->userdata('admin_id') == "676" || $this->session->userdata('admin_id') == "756"  || $this->session->userdata('admin_id') == "777"  || $this->session->userdata('admin_id') == "775") {
                              ?>
                              <a onClick="load_edit_estri('<?php echo $row->id; ?>', '<?php echo $row->no_kta; ?>', '<?php echo $row->add_name; ?>')" href="" class="btn btn-primary btn-xs" data-toggle='modal'>Edit</a>
                              &nbsp; <a onclick="load_upload_estri_view('<?php echo $row->no_kta; ?>')" href="#" class="btn btn-primary btn-xs">Upload E-STRI</a>

                              <?php
                              $golek = $this->faip_model->get_user_estri_kta($row->no_kta);
                              if ($golek != null) {
                                $skipnee = $golek->estri; // $skipnee ='KTA-'.$row->no_kta.'-'.$skipne ; 
                              } else {
                                $skipnee = 'Belum_punya_SKIP.png';
                              }
                              ?>


                              &nbsp;<a class="btn btn-primary btn-xs" href="<?php echo base_url() . 'assets/ESTRI/' . $skipnee ?>" target="_blank">Download E-STRI NEW</a>

                            <?php  } ?>


                          </td>

                        </tr>
                      <?php endforeach;
                    else: ?>
                      <tr>
                        <td colspan="10" align="center" class="text-red">No Record found!</td>
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
    <div class="modal fade" id="quick_profile">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">Add Majelis <span id="comp_name" style="font-weight:bold;"></span></h4>
          </div>
          <div class="modal-body">
            <!-- /.box-header -->
            <!-- form start -->
            <div class="box-body">
              <table width="95%" border="0">
                <tr>
                  <td width="25%"><strong><span class="form-group">Masukan Cabang</span></strong></td>
                  <td id="">
                    <select id="cabang" name="cabang" class="form-control input-md" required="">
                      <option value="">--Choose--</option>
                      <?php
                      if (isset($m_cab)) {
                        foreach ($m_cab as $val) {
                      ?>
                          <option value="<?php echo $val->value; ?>" <?php //echo (isset($user_address[0]->addresstype)?(($user_address[0]->addresstype==$val->id)?'selected="true"':""):""); 
                                                                      ?>><?php echo $val->value; ?> - <?php echo $val->name; ?></option>
                      <?php
                        }
                      }
                      ?>
                    </select>
                  </td>
                </tr>
                <tr>
                  <td><strong><span class="form-group">Masukan BK</span></strong></td>
                  <td id="">
                    <select id="bk" name="bk" class="form-control input-md" required="">
                      <option value="">--Choose--</option>
                      <?php
                      if (isset($m_bk)) {
                        foreach ($m_bk as $val) {
                      ?>
                          <option value="<?php echo $val->value; ?>" <?php //echo (isset($user_address[0]->addresstype)?(($user_address[0]->addresstype==$val->id)?'selected="true"':""):""); 
                                                                      ?>><?php echo $val->value; ?> - <?php echo $val->name; ?></option>
                      <?php
                        }
                      }
                      ?>
                    </select>
                  </td>
                </tr>
                <tr>
                  <td><strong><span class="form-group">From Date</span></strong></td>
                  <td id="">
                    <input type="text" name="from" id="from" value="<?php //echo set_value('dob',(isset($row->dob)?date('d-m-Y',strtotime($row->dob)):$row->dob));
                                                                    ?>" class="form-control datepicker" placeholder="From Date" required="required" />
                  </td>
                </tr>
                <tr>
                  <td><strong><span class="form-group">To Date</span></strong></td>
                  <td id="">
                    <input type="text" name="until" id="until" value="<?php //echo set_value('dob',(isset($row->dob)?date('d-m-Y',strtotime($row->dob)):$row->dob));
                                                                      ?>" class="form-control datepicker" placeholder="To Date" required="required" />
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
    <div class="modal fade" id="quick_job">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">Quick Preview of Latest Job Posted By <span id="j_comp_name" style="font-weight:bold;"></span></h4>
          </div>
          <div class="modal-body">
            <!-- /.box-header -->
            <!-- form start -->
            <div id="errbox" style="display:none;"></div>
            <div class="box-body" id="j_box">
              <table width="95%" border="0">
                <tr>
                  <td width="25%"><strong><span class="form-group">Job Title:</span></strong></td>
                  <td id="job_title"></td>
                </tr>
                <tr>
                  <td><strong><span class="form-group">Job Category:</span></strong></td>
                  <td id="job_cat"></span></td>
                </tr>
                <tr>
                  <td><strong><span class="form-group">Job Description:</span></strong></td>
                  <td id="job_desc"></span></td>
                </tr>
                <tr>
                  <td colspan="2"><strong>&nbsp;</strong></td>
                </tr>
                <tr>
                  <td><strong><span class="form-group">Contact Name:</span></strong></td>
                  <td id="contact_name"></td>
                </tr>
                <tr>
                  <td><strong><span class="form-group">Contact Phone:</span></strong></td>
                  <td id="contact_phone"></td>
                </tr>
                <tr>
                  <td><strong><span class="form-group">Contact Email:</span></strong></td>
                  <td id="contact_email"></td>
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
    <!-- /.right-side -->


    <!-- ------------------------------------------------------------- Tambahan by IP untuk upload E-STRI  ---------------------------------------- -->
    <div class="modal fade" id="quick_upload_estri">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">Upload E-STRI <span id="j_comp_name" style="font-weight:bold;"></span></h4>
          </div>
          <div class="modal-body">
            <!-- /.box-header -->
            <!-- form start -->
            <div id="errboxUloadSkip" style="display:none;" class="alert alert-warning" role="alert"></div>
            <div class="box-body" id="j_box">
              <table width="95%" style="border: 0;">
                <table width="95%" style="border: 0;">

                  <tr>
                    <td><strong><span class="form-group">File E-STRI</span></strong></td>
                    <td id="">
                      <form action="<?= base_url('/admin/members/fungsiUpload_estri') ?>" method="post" enctype="multipart/form-data">

                        <input type="file" name="gambar" id="gambar" accept="png, jpeg, jpg, gif, pdf" value='Pilih Filenya' />
                        <input type="text" name='id_ff' id='id_ff' disabled />
                        <input type="hidden" name='id_f' id='id_f' />

                        <button type="submit">Upload File</button>

                      </form>

                  </tr>


                </table>


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
    <!-- ---------------------------------------------------------------------------------------------------------------  Edit STRI -->
    <div class="modal fade" id="quick_edit_estri" tabindex="-1" role="dialog" aria-labelledby="quick_edit_estri" aria-hidden="true">
      <!-- <div class="modal fade" id="quick_edit_estri"> -->
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">Edit E-STRI <span id="j_comp_name" style="font-weight:bold;"></span></h4>
          </div>
          <div class="modal-body">
            <!-- /.box-header -->
            <!-- form start -->
            <div id="errboxUloadSkip" style="display:none;" class="alert alert-warning" role="alert"></div>
            <div class="box-body" id="j_box">
              <form action="" method="post">

                <table width="95%" border="0">
                  <tr style="display:none;">
                    <td><strong><strong><span class="form-group">ID TABEL STRI</span></strong></td>
                    <td>
                      <input type="hidden" name="id_id" id="id_id" />
                    </td>
                  </tr>
                  <tr>
                    <td><strong><strong><span class="form-group">Nomor KTA</span></strong></td>
                    <td>
                      <input type="text" name="id_kta" id="id_kta" />
                    </td>
                  </tr>
                  <tr>
                    <td><strong><span class="form-group">Nama cetak STRI</span></strong></td>
                    <td>
                      <input type="text" name="id_nama" id="id_nama" />
                    </td>
                  </tr>

                </table>
                <div class="modal-footer">
                  <button type="button" class="btn btn-default" data-dismiss="modal" onclick="savesetstri_1()">Simpan</button>
                  <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>

                </div>

              </form>
            </div>
            <!-- /.box-body -->

          </div>

        </div>

      </div>

      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
  </div>

  <!-- ---------------------------------------------------------------------------------------------------------------- -->

  <div class="modal fade" id="quick_stri_edit">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="modal-title">Set STRI <span id="comp_name_edit_stri" style="font-weight:bold;"></span></h4>
        </div>
        <div class="modal-body">
          <!-- /.box-header -->
          <!-- form start -->


          <div class="box-body">
            <form class="m-login__form m-form" method="post" id="ktaform">
              <table width="95%" border="0">
                <?php /* ?>
			<tr>
              <td width="25%"><strong><span class="form-group">Masukan Cabang</span></strong></td>
              <td id="">
			  <select id="cabang_stri" name="cabang_stri" class="form-control input-md" required="">
				<option value="">--Choose--</option>
				<?php
				if(isset($m_cab)){
					foreach($m_cab as $val){
						?>
						<option value="<?php echo $val->value; ?>" <?php //echo (isset($user_address[0]->addresstype)?(($user_address[0]->addresstype==$val->id)?'selected="true"':""):""); ?>><?php echo $val->value; ?> - <?php echo $val->name; ?></option>
						<?php
					}
				}
				?>
				</select>
			  </td>
            </tr>
            <tr>
              <td><strong><span class="form-group">Masukan BK</span></strong></td>
              <td id="">
			  <select id="bk_stri" name="bk_stri" class="form-control input-md" required="">
				<option value="">--Choose--</option>
				<?php
				if(isset($m_bk)){
					foreach($m_bk as $val){
						?>
						<option value="<?php echo $val->value; ?>" <?php //echo (isset($user_address[0]->addresstype)?(($user_address[0]->addresstype==$val->id)?'selected="true"':""):""); ?>><?php echo $val->value; ?> - <?php echo $val->name; ?></option>
						<?php
					}
				}
				?>
				</select>
			  </td>
            </tr>
			<?php */ ?>

                <tr style="display:none;">
                  <td><strong><span class="form-group">Nomor KTA</span></strong> <span class="red">*</span></td>
                  <td id="">
                    <select id="no_kta" name="no_kta" class="form-control input-md" required="">
                      <option value="">--Choose--</option>
                      <?php
                      if (isset($members)) {
                        foreach ($members as $val) {
                      ?>
                          <option value="<?php echo str_pad($val->no_kta, 6, '0', STR_PAD_LEFT); ?>" <?php //echo (isset($user_address[0]->addresstype)?(($user_address[0]->addresstype==$val->id)?'selected="true"':""):""); 
                                                                                                      ?>><?php echo str_pad($val->no_kta, 6, '0', STR_PAD_LEFT); ?> <?php //-echo $val->firstname; 
                                                                                                                                                                    ?> <?php //echo $val->lastname; 
                                                                                                                                                                                                                                                                                                ?></option>
                      <?php
                        }
                      }
                      ?>
                    </select>
                  </td>
                </tr>

                <tr style="display:none;">
                  <td><strong><span class="form-group">Nomor Urut SIP</span></strong></td>
                  <td id="">
                    <input type="text" name="skip_id" id="skip_id" value="" class="form-control" placeholder="Nomor Urut SIP" />
                  </td>
                </tr>

                <tr style="display:none;">
                  <td><strong><span class="form-group">Kualifikasi Profesional</span></strong></td>
                  <td id="">
                    <select id="stri_type" name="stri_type" class="form-control input-md" required="">
                      <option value="">--Choose--</option>
                      <option value="0">No IP</option>
                      <option value="1">Pratama</option>
                      <option value="2">Madya</option>
                      <option value="3">Utama</option>
                    </select>
                  </td>
                </tr>
                <tr style="display:none;">
                  <td width="25%"><strong><span class="form-group">Masukan Cabang</span></strong></td>
                  <td id="">
                    <select id="stri_cabang" name="stri_cabang" class="form-control input-md" required="">
                      <option value="">--Choose--</option>
                      <?php
                      if (isset($m_cab)) {
                        foreach ($m_cab as $val) {
                      ?>
                          <option value="<?php echo $val->value; ?>" <?php //echo (isset($user_address[0]->addresstype)?(($user_address[0]->addresstype==$val->id)?'selected="true"':""):""); 
                                                                      ?>><?php echo $val->value; ?> - <?php echo $val->name; ?></option>
                      <?php
                        }
                      }
                      ?>
                    </select>
                  </td>
                </tr>
                <tr style="display:none;">
                  <td><strong><span class="form-group">Masukan BK / HKK</span></strong></td>
                  <td id="">
                    <select id="stri_bk" name="stri_bk" class="form-control input-md" required="">
                      <option value="">--Choose--</option>
                      <?php
                      if (isset($m_bk)) {
                        foreach ($m_bk as $val) {
                      ?>
                          <option value="<?php echo $val->value; ?>" <?php //echo (isset($user_address[0]->addresstype)?(($user_address[0]->addresstype==$val->id)?'selected="true"':""):""); 
                                                                      ?>><?php echo $val->value; ?> - <?php echo $val->name; ?></option>
                      <?php
                        }
                      }
                      ?>
                    </select>
                  </td>
                </tr>

                <tr style="display:none;">
                  <td><strong><span class="form-group">Masukan Sub Bidang Kejuruan</span></strong></td>
                  <td id="">
                    <select id="stri_subbk" name="stri_subbk" class="form-control input-md" required="">
                      <option value="">--Choose--</option>
                    </select>
                  </td>
                </tr>

                <tr>
                  <td><strong><span class="form-group">Nama cetak STRI</span></strong><span class="red">*</span></td>
                  <td id="">
                    <input type="text" name="nama_stri" id="nama_stri" value="<?php //echo set_value('dob',(isset($row->dob)?date('d-m-Y',strtotime($row->dob)):$row->dob));
                                                                              ?>" class="form-control" placeholder="Nama cetak STRI" required="required" />
                  </td>
                </tr>

                <tr>
                  <td><strong><span class="form-group">Ns/As</span></strong><span class="red">*</span></td>
                  <td id="">
                    <select id="warga" name="warga" class="form-control input-md" required="">
                      <option value="1">1</option>
                      <option value="2">2</option>
                    </select>
                  </td>
                </tr>

                <tr>
                  <td><strong><span class="form-group">ros RI</span></strong><span class="red">*</span></td>
                  <td id="">
                    <select id="stri_tipe" name="stri_tipe" class="form-control input-md" required="">
                      <option value="1">1</option>
                      <option value="2">2</option>
                    </select>
                  </td>
                </tr>

                <tr style="display:none;">
                  <td><strong><span class="form-group">Kode Pemutakhiran Sertifikat</span></strong></td>
                  <td id="">
                    <input type="text" name="stri_kp" id="stri_kp" value="00" class="form-control" placeholder="Kode Pemutakhiran Sertifikat" required="required" />
                  </td>
                </tr>
                <tr style="display:none;">
                  <td><strong><span class="form-group">From Date</span></strong></td>
                  <td id="">
                    <input type="text" name="from_stri" id="from_stri" value="<?php //echo set_value('dob',(isset($row->dob)?date('d-m-Y',strtotime($row->dob)):$row->dob));
                                                                              ?>" class="form-control datepicker" placeholder="From Date" required="required" />
                  </td>
                </tr>
                <tr style="display:none;">
                  <td><strong><span class="form-group">To Date</span></strong></td>
                  <td id="">
                    <input type="text" name="until_stri" id="until_stri" value="<?php //echo set_value('dob',(isset($row->dob)?date('d-m-Y',strtotime($row->dob)):$row->dob));
                                                                                ?>" class="form-control datepicker" placeholder="To Date" required="required" />
                  </td>
                </tr>

                <tr style="display:none;">
                  <td><strong><span class="form-group">Tanggal SK</span></strong> <span class="red">*</span></td>
                  <td id="">
                    <input type="text" name="tgl_sk" id="tgl_sk" value="<?php //echo set_value('dob',(isset($row->dob)?date('d-m-Y',strtotime($row->dob)):$row->dob));
                                                                        ?>" class="form-control datepicker" placeholder="SK Date" required="required" />
                  </td>
                </tr>

                <tr style="display:none;">
                  <td><strong><span class="form-group">Nomor Urut STRI</span></strong> <span class="red">*</span></td>
                  <td id="">
                    <input type="text" name="num" id="num" value="" class="form-control" placeholder="Nomor Urut STRI" />
                  </td>
                </tr>


                <tr>
                  <td><strong><span class="form-group"></span></strong></td>
                  <td id=""><button type="button" class="btn btn-default" onclick="savesetstri()" data-dismiss="modal">Save</button>
                    <input type="hidden" name="id_stri" id="id_stri" value="" />
                  </td>
                </tr>
              </table>
            </form>
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



  <?php $this->load->view('admin/common/footer'); ?>


  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>

  <script>
    $(document).ready(function() {
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