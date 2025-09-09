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

	<style>
		.th-yellow thead th {
			background-color: #ffae00 !important;
			color: #000 !important;
			font-weight: bold;
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
</head>

<body class="skin-blue">

	<?php $this->load->view('admin/common/after_body_open'); ?>
	<?php $this->load->view('admin/common/header'); ?>

	<div class="wrapper row-offcanvas row-offcanvas-left">
		<?php $this->load->view('admin/common/left_side'); ?>

		<?php if ($selesai == 'y') { ?>
			<div class="container">
				<div class="alert alert-success fade in">
					<a href="#" class="close" data-dismiss="alert">&times;</a>
					Data telah disimpan....
				</div>
			</div>
		<?php } ?>

		<!-- Loading Overlay -->
		<div id="loadingOverlay">
			<i class="fa fa-spinner"></i>
			Loading data, please wait...
		</div>

		<!-- Right side column -->
		<aside class="right-side">
			<section class="content-header">
				<h1>ASEAN Eng Management</h1>
				<ol class="breadcrumb">
					<li><a href="<?php echo base_url('admin/dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
					<li class="active">Manage Members</li>
				</ol>
			</section>

			<main class="main">
				<div class="content">
					<div class="text-right mb-2">
						<a href="#" onclick="load_tambah_data()" class="btn btn-primary btn-xs">
							<i class="fa fa-plus"></i> Tambah Data
						</a>
					</div>

					<table id="datatables" class="display th-yellow" width="100%">
						<thead>
							<tr>
								<th>#</th>
								<th>NO. AER</th>
								<th>NAMA</th>
								<th>GRADE</th>
								<th>NO KTA</th>
								<th>DOI</th>
								<th>URL FILE</th>
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
									echo '<td>' . $isinya->doi . '</td>';
									echo '<td>';
									if (!empty($isinya->url_aer)) {
										echo '<a href="' . $isinya->url_aer . '" target="_blank" class="btn btn-info btn-xs"><i class="fa fa-file"></i> Lihat File</a>';
									} else {
										echo '<span class="text-danger">File tidak tersedia</span>';
									}
									echo '</td>';
									echo '<td class="text-center">
                                        <a href="#" onclick="load_edit_data(' . $isinya->id . ')" class="btn btn-primary btn-xs">
                                            <i class="fa fa-edit"></i>Edit
                                        </a>
                                      </td>';
									echo '</tr>';
								}
							}
							?>
						</tbody>
					</table>
				</div>
			</main>
		</aside>
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
					<form id="form_add_aer" class="form-horizontal" action="<?= base_url('/admin/aer/tambah_aer') ?>" method="post">
						<div class="form-group">
							<label class="col-sm-3 control-label">Nomor</label>
							<div class="col-sm-9"><input type="text" name="add_noaer" id="add_noaer" class="form-control" placeholder="Masukkan Nomor"></div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">Nomor KTA</label>
							<div class="col-sm-9"><input type="text" name="add_kta" id="add_kta" class="form-control" placeholder="Masukkan Nomor KTA"></div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">Nama</label>
							<div class="col-sm-9"><input type="text" name="add_nama" id="add_nama" class="form-control" placeholder="Masukkan Nama"></div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">Grade</label>
							<div class="col-sm-9"><input type="text" name="add_grade" id="add_grade" class="form-control" placeholder="Masukkan Grade"></div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">DOI</label>
							<div class="col-sm-9"><input type="text" name="add_doi" id="add_doi" class="form-control" placeholder="Masukkan DOI"></div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">URL</label>
							<div class="col-sm-9"><input type="text" name="add_url" id="add_url" class="form-control" placeholder="Masukkan URL"></div>
						</div>
						<div class="modal-footer">
							<button type="submit" class="btn btn-success"><i class="glyphicon glyphicon-ok"></i> Simpan</button>
							<button type="button" class="btn btn-default" data-dismiss="modal"><i class="glyphicon glyphicon-remove"></i> Batal</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>

	<!-- Modal Edit Data -->
	<div class="modal fade" id="quick_upload_skip" tabindex="-1" role="dialog">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h3 class="modal-title">Ubah Data ASEAN Eng.</h3>
				</div>
				<div class="modal-body">
					<form id="form_edit_aer" class="form-horizontal" action="<?= base_url('/admin/aer/update_aer') ?>" method="post">
						<div class="form-group">
							<label class="col-sm-3 control-label">Nomor</label>
							<div class="col-sm-9"><input type="text" name="edit_noaer" id="edit_noaer" class="form-control"></div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">Nomor KTA</label>
							<div class="col-sm-9"><input type="text" name="edit_kta" id="edit_kta" class="form-control"></div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">Nama</label>
							<div class="col-sm-9"><input type="text" name="edit_nama" id="edit_nama" class="form-control"></div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">Grade</label>
							<div class="col-sm-9"><input type="text" name="edit_grade" id="edit_grade" class="form-control"></div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">DOI</label>
							<div class="col-sm-9"><input type="text" name="edit_doi" id="edit_doi" class="form-control"></div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">URL</label>
							<div class="col-sm-9"><input type="text" name="edit_url" id="edit_url" class="form-control"></div>
						</div>
						<input type="hidden" name="edit_id" id="edit_id">
						<div class="modal-footer">
							<button type="submit" class="btn btn-success"><i class="glyphicon glyphicon-ok"></i> Simpan</button>
							<button type="button" class="btn btn-default" data-dismiss="modal"><i class="glyphicon glyphicon-remove"></i> Batal</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>

	<script>
		$(document).ready(function() {
			var table = $('#datatables').DataTable({
				responsive: true,
				initComplete: function() {
					$('#loadingOverlay').fadeOut();
				}
			});
		});

		function load_tambah_data() {
			$('#add_noaer').val('');
			$('#add_kta').val('');
			$('#add_nama').val('');
			$('#add_grade').val('');
			$('#add_doi').val('');
			$('#add_url').val('');
			$('.modal-title').html('Tambah Data ASEAN Eng');
			$('#myModal').modal('show');
		}

		function load_edit_data(id) {
			$.ajax({
				url: '<?php echo base_url('admin/aer/get_aer_by_id'); ?>',
				type: 'POST',
				data: {
					id: id
				},
				dataType: 'json',
				success: function(x) {
					$('#edit_id').val(x.id);
					$('#edit_noaer').val(x.no_aer);
					$('#edit_kta').val(x.kta);
					$('#edit_nama').val(x.nama);
					$('#edit_grade').val(x.grade);
					$('#edit_doi').val(x.doi);
					$('#edit_url').val(x.url_aer);
					$('.modal-title').html('Ubah Data ASEAN Eng');
					$('#quick_upload_skip').modal('show');
				}
			});
		}
	</script>

</body>

</html>