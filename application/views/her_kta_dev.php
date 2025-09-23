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
			$("#tgl_period").datepicker({
				dateFormat: 'dd-mm-yy',
				changeMonth: true,
				changeYear: true,
				yearRange: "1940:<?php echo date('Y') + 5; ?>"
			});
			$("#tgl_period2").datepicker({
				dateFormat: 'dd-mm-yy',
				changeMonth: true,
				changeYear: true,
				yearRange: "1940:<?php echo date('Y') + 5; ?>"
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

		function load_quick_period(id, tgl, tgl2, id_pay) {
			$('#quick_period').modal('show');
			$('#errboxPeriod').text('')
			$('#errboxPeriod').hide()
			$("#id_period").val(id);
			$("#id_pay").val(id_pay);

			if (tgl != '0000-00-00' && tgl2 != '0000-00-00') {
				var today = new Date(tgl);
				var dd = today.getDate();
				var mm = today.getMonth() + 1;
				var yyyy = today.getFullYear();
				if (dd < 10) {
					dd = '0' + dd;
				}
				if (mm < 10) {
					mm = '0' + mm;
				}
				var today = dd + '-' + mm + '-' + yyyy;

				$("#tgl_period").val(today);


				var today2 = new Date(tgl2);
				var dd2 = today2.getDate();
				var mm2 = today2.getMonth() + 1;
				var yyyy2 = today2.getFullYear();
				if (dd2 < 10) {
					dd2 = '0' + dd2;
				}
				if (mm2 < 10) {
					mm2 = '0' + mm2;
				}
				var today2 = dd2 + '-' + mm2 + '-' + yyyy2;

				$("#tgl_period2").val(today2);
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

		function savesetperiod() {
			var tgl_period = $('#tgl_period').val();
			var tgl_period2 = $('#tgl_period2').val();
			var id_period = $('#id_period').val();
			var id_pay = $('#id_pay').val();
			$.ajax({
				type: "POST",
				url: '<?php echo site_url('admin/members/her_setperiod') ?>',
				contentType: "application/json; charset=utf-8",
				dataType: "json",
				async: true,
				data: JSON.stringify({
					id: id_period,
					id_pay: id_pay,
					tgl_period: tgl_period,
					tgl_period2: tgl_period2
				}),
				success: function(response, status) {
					console.log(response);
					//response = JSON.parse(response);
					if (response.status) {
						$('#errboxPeriod').text(response.message);
						$('#errboxPeriod').show();
						location.reload();
					} else {
						$('#errboxPeriod').text('Error:' + response.message);
						$('#errboxPeriod').show();
					}
				},
				error: function(jqXHR, exception) {
					console.log(jqXHR);
					var error_msg = '';
					if (jqXHR.status === 0) {
						error_msg = 'Not connect.\n Verify Network.';
					} else if (jqXHR.status == 403) {
						error_msg = 'Not authorized. [403]';
					} else if (jqXHR.status == 404) {
						error_msg = 'Requested page/resource not found. [404]';
					} else if (jqXHR.status == 500) {
						error_msg = 'Internal Server Error [500].';
					} else if (exception === 'parsererror') {
						error_msg = 'Requested JSON parse failed.';
					} else if (exception === 'timeout') {
						error_msg = 'Time out error.';
					} else if (exception === 'abort') {
						error_msg = 'Ajax request aborted.';
					} else {
						error_msg = '<br/>\n' + jqXHR.responseText;
					}
					$('#errboxPeriod').html('<strong>Failed: </strong>' + error_msg);
					$('#errboxPeriod').show();
				}
			});

			//$('#quick_period').modal('toggle');
			//window.location.href = "<?php echo base_url(); ?>admin/members";
		}

		function load_quick_status(id) {
			$('#quick_status').modal('show');
			$("#id_status").val(id);
		}

		function savesetstatus() {
			var status = $('#status').val();
			var remarks = $('#remarks').val();
			var id_status = $('#id_status').val();

			var dataHTML = 'not valid';
			$.ajax({
				url: '<?php echo site_url('admin/members/setherstatus') ?>',
				dataType: "html",
				type: "POST",
				async: true, //false
				data: {
					id: id_status,
					status: status,
					remarks: remarks
				},
				success: function(jsonObject, status) {
					console.log(jsonObject);
					if ((jsonObject != 'not valid')) {
						dataHTML = jsonObject;
					}

					if (dataHTML == 'not valid')
						alert('not valid');
					location.reload();
				}
			});

			//$('#quick_profile').modal('hide');
			$('#quick_status').modal('toggle');

			//window.location.href = "<?php echo base_url(); ?>admin/members";
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
								<h3 class="box-title">HER Members / Perpanjangan Anggota PII</h3>

							</div>

							<!-- /.box-header -->
							<div class="box-body table-responsive" style="overflow:scroll;">
								<?php $this->load->view('admin/common/member_quick_search_bar_her_kta'); ?>
								<div class="clearfix text-right" style="padding:10px;">
									<?php
									//if(!isset($_GET['industry_ID'])):
									?>


									<?php //endif;
									?>
									</strong> </div>

								<style>
									.table-sm td,
									.table-sm th {
										padding: .2rem;
										/* kecilkan padding */
									}
								</style>

								<div id="tableOverlay" style="
											display:none;
											position:absolute;
											top:0;
											left:0;
											width:100%;
											height:100%;
											background:rgba(255,255,255,0.8);
											z-index:999;
											text-align:center;
											padding-top:100px;
									">
									<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i>
									<span style="font-size:18px;">Loading data...</span>
								</div>

								<div class="table-responsive" style="position:relative;">
									<table id="example2" style="font-size: 12px;" class="table table-sm table-bordered table-hover">
										<thead>
											<tr>
												<th>Tanggal Pengajuan</th>
												<th>Wilayah | Cabang</th>
												<th>BK / HKK</th>
												<th>No KTA</th>
												<th>Period</th>
												<th>Name/Email Address</th>
												<th>Date of Birth</th>
												<th>SIP</th>
												<th>Warga</th>
												<th>Dokumen</th>
												<th>Payment Status</th>
												<th>Deskripsi</th>
												<th>Total Transfer</th>
												<th>Bukti Transfer</th>
												<th>Action</th>
											</tr>
										</thead>
										<tbody></tbody>
									</table>
								</div>


							</div>



							<!-- /.box-body -->
						</div>
						<!-- /.box -->

						<!-- /.box -->
					</div>
				</div>
			</section>
			<!-- /.content -->
		</aside>
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

		<div class="modal fade" id="quick_period">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
						<h4 class="modal-title">Set Period <span id="j_comp_name" style="font-weight:bold;"></span></h4>
					</div>
					<div class="modal-body">
						<!-- /.box-header -->
						<!-- form start -->
						<div id="errboxPeriod" style="display:none;" class="alert alert-warning" role="alert"></div>
						<div class="box-body" id="j_box">
							<table width="95%" border="0">
								<tr>
									<td id="">
										<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery-1.4.2.min.js"></script>
										<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery.maskedinput-1.2.2-co.min.js"></script>
										<strong><span class="form-group">From date:</span></strong>
									</td>
									<td>
										<input type="text" name="tgl_period" id="tgl_period" value="<?php //echo set_value('period',(isset($row->period)?date('d-m-Y',strtotime($row->period)):$row->period));
																																								?>" class="form-control datepicker" placeholder="Start Date" required="required" />
									</td>
								</tr>
								<tr>
									<td><strong><span class="form-group">To date:</span></strong></td>
									<td><input type="text" name="tgl_period2" id="tgl_period2" value="<?php //echo set_value('period',(isset($row->period)?date('d-m-Y',strtotime($row->period)):$row->period));
																																										?>" class="form-control datepicker" placeholder="End Date" required="required" /></td>
									<script type="text/javascript">
										$(function($) {
											$("#tgl_period").mask("99-99-9999"), {
												placeholder: 'dd-mm-yyyy'
											};
											$("#tgl_period2").mask("99-99-9999"), {
												placeholder: 'dd-mm-yyyy'
											};
										});
									</script>
								</tr>

								<tr>
									<td></td>
									<td id=""><button type="button" class="btn btn-default" onclick="savesetperiod()">Save</button><input type="hidden" name="id_period" id="id_period" value="" /><input type="hidden" name="id_pay" id="id_pay" value="" /></td>
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

		<div class="modal fade" id="quick_status">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
						<h4 class="modal-title">Set Status (Reject)<span id="j_comp_name_" style="font-weight:bold;"></span></h4>
					</div>
					<div class="modal-body">
						<!-- /.box-header -->
						<!-- form start -->
						<div id="errbox" style="display:none;" class="alert alert-warning" role="alert"></div>
						<div class="box-body" id="j_box_">
							<table width="95%" border="0">

								<tr>
									<td><strong><span class="form-group">Status</span></strong></td>
									<td id="">
										<select class="form-control" name="status" id="status" required="">
											<option value="2">Not Valid
											</option>
										</select>
									</td>
								</tr>


								<tr>
									<td><strong><span class="form-group">Note</span></strong></td>
									<td>
										<textarea name="remarks" id="remarks" rows="4" cols="60"></textarea>
									</td>
								</tr>

								<tr>
									<td><strong><span class="form-group"></span></strong></td>
									<td id=""><button type="button" class="btn btn-default" onclick="savesetstatus()" data-dismiss="modal">Save</button><input type="hidden" name="id_status" id="id_status" value="" /></td>
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

		<!-- /.modal-content -->
	</div>
	<!-- /.modal-dialog -->
	</div>
	<!-- /.right-side -->

	<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
	<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
	<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
	<style>
		/* kasih jarak bawah untuk area top (length, pagination, filter) */
		div.dataTables_wrapper div.top {
			margin-bottom: 10px;
			/* bisa kamu atur misalnya 15px */
		}
	</style>

	<script>
		// aktifkan noConflict agar $ tidak bentrok
		var jqDT = jQuery.noConflict(true);

		jqDT(document).ready(function() {
			var table = jqDT('#example2').DataTable({
				"processing": true,
				"serverSide": true,
				"ajax": {
					"url": "<?php echo base_url('admin/members/get_her'); ?>",
					"type": "POST"
				},
				"columns": [{
						"data": "tgl_pengajuan"
					},
					{
						"data": "cab"
					},
					{
						"data": "bk"
					},
					{
						"data": "no_kta"
					},
					{
						"data": "period"
					},
					{
						"data": "name_email"
					},
					{
						"data": "dob"
					},
					{
						"data": "sip"
					},
					{
						"data": "warga_asing"
					},
					{
						"data": "dokumen"
					},
					{
						"data": "paystatus"
					},
					{
						"data": "paydesc"
					},
					{
						"data": "total_transfer"
					},
					{
						"data": "bukti_transfer",
						"render": function(data, type, row) {
							return data; // biar tag <a> muncul sebagai link
						}
					},
					{
						"data": "action"
					}
				],

				"dom": '<"top"lpf>rt<"bottom"ip><"clear">'
				// "l" = length menu, "p" = pagination, "f" = filter (search box)
				// "t" = table, "i" = information
			});

			// event: sebelum Ajax jalankan → tampilkan overlay
			table.on('preXhr.dt', function() {
				jqDT('#tableOverlay').show();
			});

			// event: sesudah Ajax selesai → sembunyikan overlay
			table.on('xhr.dt', function() {
				jqDT('#tableOverlay').hide();
			});
		});
	</script>



	<?php $this->load->view('admin/common/footer'); ?>