<!DOCTYPE html>
<html>

<head>
	<meta charset="UTF-8">
	<title><?php // echo $title;
		?></title>
	<?php $this->load->view('admin/common/meta_tags'); ?>
	<?php $this->load->view('admin/common/before_head_close'); ?>
	<style type="text/css">
		.awesome_style {
			font-size: 100px;
		}
	</style>


	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
	<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>

	<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
	<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

	</style>
	<style>
		body {
			font-family: 'Raleway', sans-serif;

		}

		#gp_head {
			text-align: center;
			background-color: #61CAFA;
			height: 66px;
			margin: 0 0 -29px 0;
			padding-top: 35px;
			border-radius: 8px 8px 0 0;
			color: rgb(000, 000, 000);
		}

		#gp_tabel {
			font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
			width: 100%;
			border-collapse: collapse;
		}

		#gp_tabel_1 {
			font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
			font-size: 20px;
			width: 100%;
			border: 0;
			border-collapse: collapse;
		}

		#gp_tabel td,
		#gp_tabel th {
			font-size: 1em;
			border: 1px solid #FFD700;
			padding: 3px 7px 2px 7px;
		}

		#gp_tabel th {
			font-size: 1.1em;
			text-align: center;
			padding-top: 5px;
			padding-bottom: 4px;
			background-color: #61CAFA;
			color: #ffffff;
		}

		#gp_tabel tr.alt td {
			color: #000000;
			background-color: #61CAFA;
		}

		#pagination {
			margin: 40 40 0;
		}

		ul.gp_pagination li a {
			border: solid 1px;
			border-radius: 3px;
			-moz-border-radius: 3px;
			-webkit-border-radius: 3px;
			padding: 6px 9px 6px 9px;
		}

		ul.gp_pagination li {
			padding-bottom: 1px;
		}

		ul.gp_pagination li a:hover,
		ul.gp_pagination li a.current {
			color: #FFFFFF;
			box-shadow: 0px 1px #EDEDED;
			-moz-box-shadow: 0px 1px #EDEDED;
			-webkit-box-shadow: 0px 1px #EDEDED;
		}

		ul.gp_pagination {
			margin: 4px 0;
			padding: 0px;
			height: 100%;
			overflow: hidden;
			font: 12px 'Tahoma';
			list-style-type: none;
		}

		ul.gp_pagination li {
			float: left;
			margin: 0px;
			padding: 0px;
			margin-left: 5px;
		}

		ul.gp_pagination li a {
			color: black;
			display: block;
			text-decoration: none;
			padding: 7px 10px 7px 10px;
		}

		ul.gp_pagination li a img {
			border: none;
		}

		ul.gp_pagination li a {
			color: #0A7EC5;
			border-color: #8DC5E6;
			background: #F8FCFF;
		}

		ul.gp_pagination li a:hover,
		ul.gp_pagination li a.current {
			text-shadow: 0px 1px #388DBE;
			border-color: #3390CA;
			background: #58B0E7;
			background: -moz-linear-gradient(top, #B4F6FF 1px, #63D0FE 1px, #58B0E7);
			background: -webkit-gradient(linear, 0 0, 0 100%, color-stop(0.02, #B4F6FF), color-stop(0.02, #63D0FE), color-stop(1, #58B0E7));
		}

		#container {
			margin: 10px;
			border: 1px solid #D0D0D0;
			box-shadow: 0 0 8px #D0D0D0;
		}

		#body {
			margin: 0 15px 0 15px;
		}

		h1 {
			color: #444;
			background-color: transparent;
			border-bottom: 1px solid #D0D0D0;
			font-size: 19px;
			font-weight: normal;
			margin: 0 0 14px 0;
			padding: 14px 15px 10px 15px;
		}
	</style>

	<link rel="stylesheet" type="text/css" href="/assets/css/style.css" />
	<script src="/assets/js/jquery.js" type="text/javascript"></script>

	<link rel="stylesheet" type="text/css" href="/assets/css/datatable.css" />
	<script src="/assets/js/jquery.dataTables.js" type="text/javascript"></script>



<body class="skin-blue">
	<?php $this->load->view('admin/common/after_body_open'); ?>
	<?php $this->load->view('admin/common/header'); ?>
	<?php $this->load->view('admin/common/left_side'); ?>

	<!-- Right side column. Contains the navbar and content of the page -->
	<aside class="right-side">
		<!-- Content Header (Page header) -->
		<section class="content-header">
			<h1> LAPORAN IURAN ANGGOTA
				<!--<small>advanced tables</small>-->
			</h1>
			<ol class="breadcrumb">
				<li><a href="<?php echo base_url('admin/dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
				<!--<li><a href="#">Examples</a></li>-->
				<li <h10>Iuran Anggota</h10>
				</li>
			</ol>
		</section>

		</head>
		<div class="wrapper row-offcanvas row-offcanvas-left">

			<main class="main">
				<div class="content">

					<?php // echo validation_errors(); 
					?>
					<?php echo form_open(base_url('admin/lapkeu/aksi')); ?>
					<table width="100%">
						<tr>
							<td align="center" width="30%">
								<table width="25%" border="1">
									<tr>
										<td>
											<table width="100%">
												<tr>
													<td>
														<label>Periode Awal </label>
													</td>
													<td align="center"> : </td>
													<td>
														<input type="text" name="awal" id="awal" placeholder="" title="Silahkan isi periode awal laporan....">
													</td>
												</tr>
												<tr>
													<td>
														<label>Periode Akhir </label>
													</td>
													<td align="center"> : </td>
													<td>
														<input type="text" name="akhir" id="akhir" placeholder="" title="Silahkan isi periode akhir laporan....">
													</td>
													<td> <br /> </td>
													<td>
														<input type="hidden" name="pilih" id="pilih" value=<?php echo $pilih; ?>">
														<input type="hidden" name="balik" id="balik" value="t">
													</td>
												</tr>
												<br />
												<tr>
													<td align="center" colspan="3">
														<input type="submit" value="Proses">
													</td>
												</tr>

											</table>
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
					</form>
					<?php echo validation_errors(); ?>
					<!-- /.box-body -->

				</div>
				<!-- /.modal-content -->
		</div>
		<!-- /.modal-dialog -->
		</div>

		<!-- /.right-side -->
		<?php $this->load->view('admin/common/footer'); ?>

		<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
		<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

		<script>
			$(document).ready(function() {
				$('#awal').datepicker({
					format: 'dd-mm-yyyy',
					autoclose: true,
					todayHighlight: true
				});
				$('#akhir').datepicker({
					format: 'dd-mm-yyyy',
					autoclose: true,
					todayHighlight: true
				});
			});
		</script>

</body>

</html>