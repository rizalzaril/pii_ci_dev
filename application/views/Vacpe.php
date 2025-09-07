<div class="container mt-3">

	<?php if ($this->session->flashdata('success_update') || $this->session->flashdata('success_delete') || $this->session->flashdata('success_import')) : ?>
		<div class="alert alert-success alert-dismissible fade show" role="alert">
			<strong>Sukses!</strong> <?= $this->session->flashdata('success_import'); ?>
			<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
		</div>
	<?php endif; ?>


	<?php if ($this->session->flashdata('error_import')): ?>
		<div class="alert alert-danger alert-dismissible fade show" role="alert">
			<strong>Gagal Import!</strong> <?= $this->session->flashdata('error_import'); ?>
			<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
		</div>
	<?php endif; ?>

	<h3 class="text-center"><i class="fa-solid fa-user"></i> List Acpe</h3>
	<hr>
	<a href="<?= base_url('/dashboard/add_data') ?>" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Add data </a>

	<!-- Button trigger modal Import ACPE -->
	<button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#exampleModal"> <i class="fas fa-file-excel"></i> Import XLSX/CSV </button>

	<!-- Modal Import data AER -->
	<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
		<div class="modal-dialog ">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="exampleModalLabel">Import XLSX/CSV</h5> <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body"> <?= form_open_multipart('import/import_proccess_acpe/', ['id' => 'formImport']) ?> <!-- Form file upload XLSX -->
					<div class="mb-3"> <label for="" class="form-label fw-bold">Nama File XLSX/CSV*</label> <input type="file" name="excel_file" class="form-control shadow-sm" accept=".xls,.xlsx,.csv" required> </div>

					<button type="submit" class="btn btn-success">Import</button> <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button> <?= form_close() ?> <!-- Overlay Loading -->

					<div id="loadingOverlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; text-align:center; color:white; padding-top:20%;">
						<div class="spinner-border text-light" role="status" style="width:3rem; height:3rem;"></div>
						<p class="mt-3 fs-5">Sedang mengimport data, mohon tunggu...</p>
					</div>
					<script>
						document.getElementById('formImport').addEventListener('submit', function() {
							document.getElementById('loadingOverlay').style.display = 'block';
						});
					</script>
				</div>
				<div class="modal-footer"> </div>
			</div>
		</div>

	</div>


	<!-- FILTER / SORT  -->

	<div class="d-flex justify-content-between mt-3">
		<div class="mb-3">
			<label>Sort by key:</label>
			<select id="sort_by" class="form-select" style="width:auto; display:inline-block;">
				<option value="id|ASC">PRIMARY (ASC)</option>
				<option value="id|DESC" selected>PRIMARY (DESC)</option>
				<option value="username|ASC">username (ASC)</option>
				<option value="username|DESC">username (DESC)</option>
				<option value="email|ASC">email (ASC)</option>
				<option value="email|DESC">email (DESC)</option>
				<option value="">Tidak ada</option>
			</select>
		</div>

		<div class="mb-3">
			<label>Filter by Duplicate:</label>
			<select id="filter_duplicate" class="form-select" style="width:auto; display:inline-block;">
				<option value="">All</option>
				<option value="1">Duplicate Only</option>
				<option value="0">Non Duplicate Only</option>
			</select>
		</div>

		<!-- ✅ Filter by Date (Tambahan) -->
		<div class="mb-3">
			<label>Filter by Date:</label>
			<input type="date" id="start_date" class="form-control d-inline-block" style="width:auto; display:inline-block;">
			<span> s/d </span>
			<input type="date" id="end_date" class="form-control d-inline-block" style="width:auto; display:inline-block;">
			<button id="btn_filter_date" class="btn btn-primary btn-sm">Filter</button>
			<button id="btn_reset_date" class="btn btn-secondary btn-sm">Reset</button>
		</div>
		<!-- ✅ END Filter by Date -->
	</div>



	<!-- Delete form -->
	<div class="mb-3">
		<button id="btn_delete_selected" class="btn btn-danger btn-sm">
			<i class="fa fa-trash"></i> Delete Selected
		</button>
		<button id="btn_delete_all" class="btn btn-warning btn-sm">
			<i class="fa fa-trash"></i> Delete All
		</button>
	</div>

	<!-- END FILTER / SORT  -->


	<!-- TABLE AER SECTION -->
	<table id="acpe_table" class="table table-sm table-striped">
		<thead>
			<tr class="center">
				<!-- <th><input type="checkbox" id="select_all"></th> -->
				<th>#</th>
				<th>No. ACPE</th>
				<th>DOI</th>
				<th>NAMA</th>
				<th>KTA</th>
				<th>NEW PE NO</th>
				<th>BK_ACPE</th>
				<th>ASOSIASI</th>
				<th>#</th>
			</tr>
		</thead>
	</table>


</div>
<?php $this->load->view('footer'); ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>

<?php if ($this->session->flashdata('success_delete') || $this->session->flashdata('success_update')): ?>
	<script>
		//alert delete
		Swal.fire({
			icon: 'success',
			title: 'Berhasil!',
			text: '<?= $this->session->flashdata("success_delete") ?>',
			showConfirmButton: false,
			timer: 2000
		});

		Swal.fire({
			icon: 'success',
			title: 'Berhasil!',
			text: '<?= $this->session->flashdata("success_update") ?>',
			showConfirmButton: false,
			timer: 2000
		});

		Swal.fire({
			icon: 'success',
			title: 'Berhasil!',
			text: '<?= $this->session->flashdata("success_import") ?>',
			showConfirmButton: false,
			timer: 2000
		});
	</script>
<?php endif; ?>


<!-- Confirm delete -->
<script>
	$(document).ready(function() {
		$('.btn-delete').on('click', function(e) {
			e.preventDefault();
			const id = $(this).data('id');
			const kode = $(this).data('kode');

			Swal.fire({
				title: 'Yakin hapus data ini?',
				text: "Data dengan kode " + '' +
					kode + " yang dihapus tidak bisa dikembalikan!",
				icon: 'warning',
				showCancelButton: true,
				confirmButtonText: 'Ya, hapus!',
				cancelButtonText: 'Batal',
				reverseButtons: true
			}).then((result) => {
				if (result.isConfirmed) {
					window.location.href = '<?= base_url("/dashboard/delete_data/") ?>' + id;
				}
			});
		});
	});
</script>


<!-- Inisialisasi DataTable table aer -->
<script>
	// $(document).ready(function() {
	// 	let table = $('#acpe_table').DataTable({
	// 		processing: true,
	// 		serverSide: true,
	// 		ajax: {
	// 			url: "<?= base_url('Acpe/get_acpe') ?>",
	// 			type: "GET",
	// 			data: function(d) {
	// 				let sort_val = $('#sort_by').val();
	// 				if (sort_val) {
	// 					let parts = sort_val.split('|');
	// 					d.order_by = parts[0];
	// 					d.order_dir = parts[1];
	// 				}
	// 				// d.is_duplicate = $('#filter_duplicate').val();

	// 				// ✅ Kirim filter date
	// 				// d.start_date = $('#start_date').val();
	// 				// d.end_date = $('#end_date').val();

	// 				return d; // pastikan dikembalikan
	// 			}
	// 		}
	// 	});

	// 	// $('#sort_by, #filter_duplicate').on('change', function() {
	// 	// 	table.ajax.reload();
	// 	// });

	// 	// $('#btn_filter_date').on('click', function() {
	// 	// 	table.ajax.reload();
	// 	// });

	// 	// $('#btn_reset_date').on('click', function() {
	// 	// 	$('#start_date').val('');
	// 	// 	$('#end_date').val('');
	// 	// 	table.ajax.reload();
	// 	// });

	// 	// $('#select_all').on('click', function() {
	// 	// 	$('.row_checkbox').prop('checked', this.checked);
	// 	// });

	// 	$('#btn_delete_selected').on('click', function() {
	// 		let ids = [];
	// 		$('.row_checkbox:checked').each(function() {
	// 			ids.push($(this).val());
	// 		});

	// 		if (ids.length === 0) {
	// 			Swal.fire('Warning', 'Pilih minimal 1 data!', 'warning');
	// 			return;
	// 		}

	// 		Swal.fire({
	// 			title: 'Yakin hapus data terpilih?',
	// 			icon: 'warning',
	// 			showCancelButton: true,
	// 			confirmButtonText: 'Ya, hapus!',
	// 			cancelButtonText: 'Batal'
	// 		}).then((result) => {
	// 			if (result.isConfirmed) {
	// 				$.post("<?= base_url('users/delete_selected_dummy_users') ?>", {
	// 					ids: ids
	// 				}, function() {
	// 					Swal.fire('Sukses', 'Data terpilih berhasil dihapus', 'success');
	// 					table.ajax.reload();
	// 				});
	// 			}
	// 		});
	// 	});

	// 	$('#btn_delete_all').on('click', function() {
	// 		Swal.fire({
	// 			title: 'Yakin hapus semua data?',
	// 			icon: 'warning',
	// 			showCancelButton: true,
	// 			confirmButtonText: 'Ya, hapus semua!',
	// 			cancelButtonText: 'Batal'
	// 		}).then((result) => {
	// 			if (result.isConfirmed) {
	// 				$.post("<?= base_url('users/delete_all_dummy_users') ?>", function() {
	// 					Swal.fire('Sukses', 'Semua data berhasil dihapus', 'success');
	// 					table.ajax.reload();
	// 				});
	// 			}
	// 		});
	// 	});
	// });

	$(document).ready(function() {
		let table = $('#acpe_table').DataTable({
			processing: true,
			serverSide: true,
			ajax: {
				url: "<?= base_url('Acpe/get_acpe') ?>",
				type: "GET",
				data: function(d) {
					let sort_val = $('#sort_by').val();
					if (sort_val) {
						let parts = sort_val.split('|');
						d.order_by = parts[0];
						d.order_dir = parts[1];
					}
					return d;
				},
				error: function(xhr, error, thrown) {
					console.log("🔴 DataTables AJAX Error:");
					console.log("Status:", xhr.status);
					console.log("Response Text:", xhr.responseText);
					console.log("Error:", error);
					console.log("Thrown:", thrown);
					alert("Server mengembalikan response yang tidak valid.\nCek console (F12 → Console/Network).");
				}
			}
		});
	});
</script>