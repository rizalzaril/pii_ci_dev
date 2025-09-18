  <!DOCTYPE html>
  <html>

  <head>
  	<meta charset="UTF-8">
  	<!-- _ci_view: <?php echo $_ci_view; ?> -->
  	<title><?php echo $title; ?></title>
  	<?php
		$this->load->view('admin/common/meta_tags');
		$this->load->view('admin/common/before_head_close'); ?>
  	<link rel="stylesheet" href="<?php echo base_url('assets/css/jquery-ui.css'); ?>">
  	<script src="<?php echo base_url('assets/js/jquery-1.12.4.js'); ?>"></script>
  	<script src="<?php echo base_url('assets/js/jquery-ui.js'); ?>"></script>


  	<script>
  		$(function() {
  			$("#from_ip").datepicker({
  				dateFormat: 'dd-mm-yy',
  				changeMonth: true,
  				changeYear: true,
  				yearRange: "<?php echo date('Y') - 5; ?>:2050"
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


  		function savesetip() {
  			var ip_bk = $('#ip_bk').val();
  			var ip_hkk = $('#ip_hkk').val();
  			var no_ip = $('#no_ip').val();
  			var ip_type = $('#ip_type').val();
  			var no_kta = $('#no_kta').val();
  			var stri_kp = $('#stri_kp').val();
  			var from = $('#from_ip').val();
  			var id_ip = $('#id_ip').val();

  			if (no_kta != '' && no_ip != '' && ip_type != '' && ip_bk != '' && from != '') {
  				//alert(from_ip);
  				var dataHTML = 'not valid';
  				$.ajax({
  					url: '<?php echo site_url('admin/skip/ajax_skip_setipedit') ?>',
  					dataType: "html",
  					type: "POST",
  					async: true, //false
  					data: {
  						id: id_ip,
  						stri_kp: stri_kp,
  						no_kta: no_kta,
  						ip_bk: ip_bk,
  						from: from,
  						ip_type: ip_type,
  						no_ip: no_ip,
  						ip_hkk: ip_hkk
  					},
  					success: function(jsonObject, status) {
  						console.log(jsonObject);
  						if ((jsonObject != 'not valid')) {
  							dataHTML = jsonObject;
  						}

  						if (dataHTML == 'not valid')
  							alert('not valid');
  						else {
  							$jq('#quick_ip').modal('hide');
  							window.location.href = "<?php echo base_url(); ?>admin/members/pi";
  						}




  					}
  				});

  			} else {
  				alert('Silahkan lengkapi datanya');
  			}
  			//$('#quick_profile').modal('hide');
  			//$('#quick_ip').modal('toggle');
  			//location.reload();
  		}
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
  				<h1> SKIP
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
  				var table = '';
  				$(document).ready(function() {

  					// Inisialisasi datepicker (gunakan jQuery normal, bukan $jq)




  					$jq = $.noConflict();
  					table = $('#table_id').DataTable({
  						"processing": true,
  						"serverSide": true,
  						"ordering": true, // Set true agar bisa di sorting
  						"order": [
  							[0, 'desc']
  						], // Default sortingnya berdasarkan kolom / field ke 0 (paling pertama)
  						"ajax": {
  							"url": "<?php echo base_url('admin/members/get_ip') ?>", // URL file untuk proses select datanya
  							"type": "POST",
  							'data': function(data) {
  								var filter_status = $('#filter_status').val();
  								var filter_bk = $('#filter_bk').val();
  								var filter_hkk = $('#filter_hkk').val();
  								var filter_cab = $('#filter_cab').val();
  								data.filter_status = filter_status;
  								data.filter_bk = filter_bk;
  								data.filter_cab = filter_cab;
  								data.filter_hkk = filter_hkk;
  								data.sk_start_date = $('#sk_start_date').val();
  								data.sk_end_date = $('#sk_end_date').val();
  								console.log(data);
  								return data;
  							}
  						},
  						"deferRender": true,
  						"aLengthMenu": [
  							[15, 50, 100, 1000],
  							[15, 50, 100, 1000]
  						], // Combobox Limit
  						"columns": [
  							/*{
            "className":      'details-control',
            "orderable":      false,
            "data":           null,
            "defaultContent": ''
          },*/
  							{
  								"render": function(data, type, row) {
  									var html = '';
  									//if(row.lastname!=null)
  									//	html += ' '+ row.lastname;
  									//console.log(row);
  									html += '<a href="<?php echo base_url(); ?>admin/members/details_m/' + row.ip_kta + '">' + (row.firstname != null ? row.firstname : '') + ' ' + (row.lastname != null ? row.lastname : '') + '</a>';

  									return html;
  									nya
  								}
  							},
  							{
  								"render": function(data, type, row) {
  									var html = '';
  									if (row.ip_tipe == '1')
  										html = 'IPP.';
  									else if (row.ip_tipe == '2')
  										html = 'IPM.';
  									else if (row.ip_tipe == '3')
  										html = 'IPU.';
  									return html;
  									nya
  								}
  							},
  							{
  								"render": function(data, type, row) {
  									var html = row.ip_bk + '-' + row.bk_name;
  									return html;
  									nya
  								}
  							},
  							{
  								"data": "lic_num"
  							},
  							{
  								"render": function(data, type, row) {
  									return zeroPad(row.ip_kta, 6);
  									nya
  								}
  							},

  							{
  								"data": "startyear"
  							},
  							{
  								"data": "endyear"
  							},

  							<?php if ($showNilaiIP_column) { ?> {
  									"render": function(data, type, row) {
  										var html = '';
  										if (row.bap != '' && row.total_score > 0) {
  											html = row.total_score + '<br />W1 : ' + row.wajib1_score + '<br />W2 : ' + row.wajib2_score + '<br />W3 : ' + row.wajib3_score + '<br />W4 : ' + row.wajib4_score + '<br />P : ' + row.pilihan_score;
  										} else if (row.bap == '' && row.total_score_as > 0) {
  											html = row.total_score_as + '<br />W1 : ' + row.wajib1_score_as + '<br />W2 : ' + row.wajib2_score_as + '<br />W3 : ' + row.wajib3_score_as + '<br />W4 : ' + row.wajib4_score_as + '<br />P : ' + row.pilihan_score_as;
  										}
  										return html;
  										nya
  									}
  								},
  							<?php } ?> {
  								"render": function(data, type, row) {
  									var class_label = '';
  									var status = 'Not Active';
  									var d = new Date(row.endyear);
  									//console.log(d);
  									var today = new Date();
  									today.setHours(0, 0, 0, 0);
  									if (d >= today) {
  										class_label = 'success';
  										status = 'Active';
  									} else if (row.sts == '0') {
  										class_label = 'danger';
  										status = 'Not Active';
  									} else
  										class_label = 'warning';

  									return '<a onClick="update_status(' + row.id + ');" href="javascript:;" id="sts_' + row.id + '"> <span class="label label-' + class_label + '">' + status + '</span> </a>';
  								}
  							},
  							//{ "data": "no_kta" }, 
  							//{ "data": "skip_sk" }, 
  							{
  								"render": function(data, type, row) {
  									var html = '';

  									<?php if ($this->session->userdata('type') == "1" || $this->session->userdata('type') == "0"  || $this->session->userdata('type') != "9") { ?>
  										var html = '<a href="javascript:;" onclick="load_quick_ip_view(\'' + row.id + '\');" class="btn btn-primary btn-xs">EDIT</a>';
  									<?php } ?>
  									<?php if (($this->session->userdata('type') == "1" && ($this->session->userdata('admin_id') == '672')) || $this->session->userdata('type') == "0") { ?>
  										html = html + '<a href="javascript:;" onclick="load_quick_ip_view_del(\'' + row.id + '\');" class="btn btn-primary btn-xs">DELETE</a>';
  									<?php } ?>
  									return html
  								}
  							},
  						],
  					});


  					// $jq("#sk_start_date, #sk_end_date").datepicker({
  					// 	dateFormat: 'yy-mm-dd',
  					// 	changeMonth: true,
  					// 	changeYear: true,
  					// 	yearRange: "1940:<?php echo date('Y') + 5; ?>",
  					// 	onSelect: function() {
  					// 		$("#sk_start_date,#sk_end_date").trigger("change"); // paksa trigger
  					// 	}
  					// });

  					$jq("#sk_start_date,#sk_end_date").on('change', function() { //keyup 
  						table.draw();
  					});



  					$('#table_id tfoot th').each(function() {
  						var title = $(this).text();

  						if (title) {
  							$(this).html('<input type="text" placeholder="Search ' + title + '" />');
  						}

  						// if (title === 'SK From') {
  						// 	$(this).html('<input type="text" id="sk_start_date" name="sk_start_date" class="datepicker" placeholder="Search ' + title + '" />');
  						// }

  						// if (title === 'SK End') {
  						// 	$(this).html('<input type="text" id="sk_end_date" name="sk_end_date" class="datepicker" placeholder="Search ' + title + '" />');
  						// }
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

  					$('#filter_hkk').change(function() {
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
  						<p>
  							<?php
								if ($showAddSKIP_button) { ?>
  								<a class="btn btn-primary" href="#" onClick="add_ip();"><i class="glyphicon glyphicon-file"></i>&nbsp;Add SKIP</a>
  							<?php } ?>
  						</p>
  						<br />
  						<div class="row" style="background-color:#3C8DBC; padding:10px; margin:0;">
  							<div class="col-md-2 margin-bottom-special">
  								<select id="filter_status" name="filter_status" class="form-control input-md">
  									<option value="" <?php echo (isset($search_data["filter_status"])) ? $search_data["filter_status"] == "" ? "selected" : '' : ''; ?>>All Status</option>
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


  							<div class="col-md-2 margin-bottom-special">
  								<select id="filter_hkk" name="filter_hkk" class="form-control input-md">
  									<option value="" <?php echo (isset($search_data["filter_hkk"])) ? $search_data["filter_hkk"] == "" ? "selected" : '' : ''; ?>>All HKK</option>
  									<?php
										if (isset($m_hkk)) {
											foreach ($m_hkk as $val) {
										?>
  											<option value="<?php echo $val->value; ?>" <?php echo (isset($search_data["filter_hkk"]) ? (($search_data["filter_hkk"] == $val->value) ? 'selected="true"' : "") : ""); ?>><?php echo $val->value; ?> - <?php echo $val->name; ?></option>
  									<?php
											}
										}
										?>
  								</select>
  							</div>

  							<div class="col-md-2 margin-bottom-special">
  								<input type="text" name="sk_start_date" id="sk_start_date" value="<?php //echo set_value('period',(isset($row->period)?date('d-m-Y',strtotime($row->period)):$row->period));
																																										?>" class="form-control datepicker" placeholder="Start Date" />
  							</div>

  							<div class="col-md-2 margin-bottom-special">
  								<input type="text" name="sk_end_date" id="sk_end_date" value="<?php //echo set_value('period',(isset($row->period)?date('d-m-Y',strtotime($row->period)):$row->period));
																																								?>" class="form-control datepicker" placeholder="End Date" />
  							</div>



  							<?php if ($showExportAll_button) { ?>
  								<div class="col-md-1 margin-bottom-special">
  									<input class="btn" name="button" value="Export All" type="button" onClick="export_all();">
  								</div>

  								<div class="col-md-2 margin-bottom-special">
  									<input class="btn" name="button" value="Export Selection" type="button" onClick="export_select();">
  								</div>
  							<?php } ?>
  						</div>
  						<br />
  						<table id="table_id" class="display" style="width:100%">
  							<thead>
  								<tr>
  									<!--<th></th>-->
  									<th>Name</th>
  									<th>Type</th>
  									<th>BK FAIP</th>
  									<th>No. IP</th>
  									<th align="center">No. KTA</th>
  									<th>SK From</th>
  									<th>SK End</th>
  									<?php if ($showNilaiIP_column) { ?>
  										<th>Nilai IP</th>
  									<?php } ?>
  									<th>Status</th>
  									<th>Action</th>
  								</tr>
  							</thead>
  							<tbody>
  							</tbody>
  							<tfoot>
  								<tr>
  									<th>Name</th>
  									<th>Type</th>
  									<th>BK</th>
  									<th>No. IP</th>
  									<th align="center">No. KTA</th>
  									<th>SK From</th>
  									<th>SK End</th>
  									<?php if ($showNilaiIP_column) { ?>
  										<th></th>
  									<?php } ?>
  									<th></th>
  									<th></th>
  								</tr>
  							</tfoot>
  						</table>
  					</div>
  				</div>
  			</section>
  			<!-- /.content -->
  		</aside>


  		<div class="modal fade" id="quick_ip">
  			<div class="modal-dialog">
  				<div class="modal-content">
  					<div class="modal-header">
  						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
  						<h4 class="modal-title">Set SKIP <span id="j_comp_name_add" style="font-weight:bold;"></span></h4>
  					</div>
  					<div class="modal-body">
  						<!-- /.box-header -->
  						<!-- form start -->
  						<div id="errbox_add" style="display:none;"></div>
  						<div class="box-body" id="j_box_add">
  							<form class="m-login__form m-form" method="post" id="ktaform">
  								<table width="95%" border="0">

  									<tr>
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

  									<tr>
  										<td width="25%"><strong><span class="form-group">Kualifikasi Profesional</span></strong><span class="red">*</span></td>
  										<td id="">
  											<select id="ip_type" name="ip_type" class="form-control input-md" required="">
  												<option value="">--Choose--</option>
  												<option value="1">Pratama</option>
  												<option value="2">Madya</option>
  												<option value="3">Utama</option>
  											</select>
  										</td>
  									</tr>

  									<tr>
  										<td><strong><span class="form-group">Nomor Urut IP</span></strong><span class="red">*</span></td>
  										<td id="">
  											<input type="text" name="no_ip" id="no_ip" value="<?php //echo set_value('dob',(isset($row->dob)?date('d-m-Y',strtotime($row->dob)):$row->dob));
																																					?>" class="form-control" placeholder="Nomor Urut IP" required="required" />
  										</td>
  									</tr>

  									<?php /* ?>
        
        <tr>
                <td width="25%"><strong><span class="form-group">Masukan Cabang</span></strong></td>
                <td id="">
          <select id="ip_cabang" name="ip_cabang" class="form-control input-md" required="">
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
                <td><strong><span class="form-group">Masukan Sub Bidang Kejuruan</span></strong></td>
                <td id="">
          <select id="ip_subbk" name="ip_subbk" class="form-control input-md" required="">
          <option value="">--Choose--</option>
          </select>
          </td>
              </tr>
              
        <tr>
                <td><strong><span class="form-group">Kode Pemutakhiran Sertifikat</span></strong></td>
                <td id="">
          <input type="text" name="ip_kp" id="ip_kp" value="<?php //echo set_value('dob',(isset($row->dob)?date('d-m-Y',strtotime($row->dob)):$row->dob));?>" class="form-control" placeholder="Kode Pemutakhiran Sertifikat" required="required" />
          </td>
              </tr>
        
        <?php */ ?>


  									<tr>
  										<td><strong><span class="form-group">Masukan BK yang tertera pada SIP</span></strong><span class="red">*</span></td>
  										<td id="">
  											<select id="ip_bk" name="ip_bk" class="form-control input-md" required="">
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
  										<td><strong><span class="form-group">Masukan HKK </span></strong></td>
  										<td id="">
  											<select id="ip_hkk" name="ip_hkk" class="form-control input-md" required="">
  												<option value="00">00</option>
  												<?php
													if (isset($m_hkk)) {
														foreach ($m_hkk as $val) {
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
  										<td><strong><span class="form-group">Kode Pemutakhiran Sertifikat</span></strong></td>
  										<td id="">
  											<input type="text" name="stri_kp" id="stri_kp" value="00" class="form-control" placeholder="Kode Pemutakhiran Sertifikat" required="required" />
  										</td>
  									</tr>

  									<tr>
  										<td><strong><span class="form-group">From Date</span></strong><span class="red">*</span></td>
  										<td id="">
  											<input type="text" name="from_ip" id="from_ip" value="<?php //echo set_value('dob',(isset($row->dob)?date('d-m-Y',strtotime($row->dob)):$row->dob));
																																							?>" class="form-control datepicker" placeholder="From Date" required="required" />
  										</td>
  									</tr>
  									<?php /* ?>
              <tr>
                <td><strong><span class="form-group">To Date</span></strong></td>
                <td id="">
          <input type="text" name="until_ip" id="until_ip" value="<?php //echo set_value('dob',(isset($row->dob)?date('d-m-Y',strtotime($row->dob)):$row->dob));?>" class="form-control datepicker" placeholder="To Date" required="required" />
          </td>
              </tr>
        
        <tr>
                <td><strong><span class="form-group">Tanggal SK</span></strong></td>
                <td id="">
          <input type="text" name="tgl_sk_ip" id="tgl_sk_ip" value="<?php //echo set_value('dob',(isset($row->dob)?date('d-m-Y',strtotime($row->dob)):$row->dob));?>" class="form-control datepicker" placeholder="SK Date" required="required" />
          </td>
              </tr>
        <?php */ ?>
  									<tr>
  										<td><strong><span class="form-group"></span></strong></td>
  										<td id=""><button type="button" class="btn btn-default" onclick="savesetip()">Save</button><input type="hidden" name="id_ip" id="id_ip" value="" /></td>
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

  		<!-- /.modal-content -->
  	</div>
  	<!-- /.modal-dialog -->
  	</div>
  	<!-- /.right-side -->


  	<?php $this->load->view('admin/common/footer'); ?>


  	<script>
  		function reformatDate(dateStr) {
  			dArr = dateStr.split("-"); // ex input "2010-01-18"
  			return dArr[2] + "-" + dArr[1] + "-" + dArr[0]; //ex out: "18/01/10"
  		}

  		function pad(num, size) {
  			var s = num + "";
  			while (s.length < size) s = "0" + s;
  			return s;
  		}

  		function load_quick_ip_view(id) {
  			$("#no_kta").val('').trigger('change');
  			$("#ktaform")[0].reset();


  			var id_ip = id;
  			$.ajax({
  				url: '<?php echo site_url('admin/members/get_pi_by_id') ?>',
  				dataType: "html",
  				type: "POST",
  				async: true, //false
  				data: {
  					id: id_ip
  				},
  				success: function(jsonObject, status) {
  					var x = JSON.parse(jsonObject);
  					//console.log(x);
  					x = x[0];

  					//$('#kta').val(x.ip_kta);

  					var newOption = new Option(pad(x.ip_kta, 6), pad(x.ip_kta, 6), false, false);
  					$('#no_kta').append(newOption).trigger('change');
  					$("#no_kta").val(pad(x.ip_kta, 6)).trigger('change');

  					$('#no_ip').val(x.ip_seq);
  					$('#ip_type').val(x.ip_tipe);
  					$('#from_ip').val(reformatDate(x.startyear));
  					$('#stri_kp').val(pad(x.ip_rev, 2));

  					$jq('#quick_ip').modal('show');
  					$jq("#id_ip").val(id);
  				}
  			});


  		}

  		function load_quick_ip_view_del(id) {
  			if (confirm('Apakah anda yakin akan menghapus (archive) SKIP ?')) {
  				$.ajax({
  					url: '<?php echo site_url('admin/skip/ajax_skip_deleteskip') ?>',
  					dataType: "html",
  					type: "POST",
  					async: true, //false
  					data: {
  						id: id
  					},
  					success: function(response, status) {
  						console.log(response);
  						response = JSON.parse(response);
  						if (response.status) {
  							// $('#errbox').text(response.message);
  							// $('#errbox').show();
  							location.reload();
  						} else {
  							// $('#errbox').text('Error:' + response.message);
  							// $('#errbox').show();
  						}
  						alert(response.message);
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
  						alert(error_msg);
  						//$('#errbox').html('<strong>Failed: </strong>' + error_msg);
  						//$('#errbox').show();
  					}
  				});
  			}


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

  		function export_all() {
  			window.open('<?php echo base_url(); ?>admin/members/export_skip_all');
  		}

  		// function export_select() {
  		// 	//console.log( table.rows().data());
  		// 	if (table.rows().data().length > 0) {
  		// 		var arr = [];
  		// 		var i = 0;
  		// 		table.rows().data().each(function() {
  		// 			var obj = $(this);
  		// 			obj = obj.toArray();
  		// 			//console.log( obj[i]);
  		// 			arr.push(obj[i].id);
  		// 			i++;
  		// 		});

  		// 		//console.log( arr);
  		// 		if (arr.length > 0)
  		// 			window.open('<?php echo base_url(); ?>admin/members/export_skip_select?id=' + arr);
  		// 	} else {
  		// 		alert('Tidak ada data');
  		// 	}
  		// }

  		function export_select() {
  			if (table.rows().data().length > 0) {
  				var arr = [];
  				var i = 0;
  				table.rows().data().each(function() {
  					var obj = $(this);
  					obj = obj.toArray();
  					arr.push(obj[i].id);
  					i++;
  				});

  				var sk_start_date = $('#sk_start_date').val();
  				var sk_end_date = $('#sk_end_date').val();

  				if (arr.length > 0) {
  					window.open(
  						'<?php echo base_url(); ?>admin/members/export_skip_select?id=' + arr.join(",") +
  						'&sk_start_date=' + sk_start_date +
  						'&sk_end_date=' + sk_end_date
  					);
  				}
  			} else {
  				alert('Tidak ada data');
  			}
  		}
  	</script>


  	<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />
  	<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>

  	<script>
  		$(document).ready(function() {


  			// Inisialisasi datepicker
  			$("#sk_start_date,#sk_end_date").datepicker({
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