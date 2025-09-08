<style>
	/* Overlay loading */
	#loadingOverlay {
		display: none;
		/* default hidden */
		position: fixed;
		top: 0;
		left: 0;
		width: 100%;
		height: 100%;
		background: rgba(0, 0, 0, 0.5);
		z-index: 9999;
		display: flex;
		justify-content: center;
		align-items: center;
		flex-direction: column;
	}

	/* Spinner */
	.spinner {
		border: 8px solid #f3f3f3;
		/* Light grey */
		border-top: 8px solid #3498db;
		/* Blue */
		border-radius: 50%;
		width: 60px;
		height: 60px;
		animation: spin 1s linear infinite;
		margin-bottom: 15px;
	}

	@keyframes spin {
		0% {
			transform: rotate(0deg);
		}

		100% {
			transform: rotate(360deg);
		}
	}

	.loading-text {
		color: white;
		font-size: 20px;
		font-family: Arial, sans-serif;
	}
</style>

<div class="container" style="margin-bottom: 100px;">
	<h3 class="page-header">Daftar Member Tanpa VA</h3>

	<?php if (!empty($members_non_va)) : ?>

		<form id="bulkVAForm" method="post" action="<?= site_url('Generate_va/update_bulk_va'); ?>">
			<button type="submit" class="btn btn-success" style="margin-bottom: 50px;">Generate VA Terpilih</button>
			<table class="table table-bordered table-hover mt-2">
				<thead>
					<tr>
						<th><input type="checkbox" id="selectAll"></th>
						<th>User ID</th>
						<th>Nama</th>
						<th>No KTA</th>
						<th>Wilayah</th>
						<th>Kode BK</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($members_non_va as $row): ?>
						<tr>
							<td>
								<input type="checkbox" name="user_ids[]" value="<?= $row->user_id_profile; ?>"
									data-wilayah="<?= $row->code_wilayah; ?>"
									data-bk="<?= $row->code_bk_hkk; ?>"
									data-kta="<?= $row->no_kta; ?>">
							</td>
							<td><?= $row->user_id_profile; ?></td>
							<td><?= $row->firstname . " " . $row->lastname; ?></td>
							<td><?= $row->no_kta; ?></td>
							<td><?= $row->code_wilayah; ?></td>
							<td><?= $row->code_bk_hkk; ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>


		</form>

		<div id="loadingOverlay">
			<div class="spinner"></div>
			<div class="loading-text">Loading Data...</div>
		</div>
		<div class="table-responsive">
			<table id="membersTable" class="table table-bordered table-striped table-hover">
				<thead>
					<tr>
						<th>#</th>
						<th>User ID Profile</th>
						<th>First Name</th>
						<th>Last Name</th>
						<th>Kode Wilayah</th>
						<th>Kode BK HKK</th>
						<th>No KTA</th>
						<th>VA</th>
						<th>Aksi</th>
					</tr>
				</thead>
				<tbody>
					<?php $no = 1;
					foreach ($members_non_va as $row) : ?>
						<tr>
							<td><?= $no++; ?></td>
							<td><?= $row->user_id_profile; ?></td>
							<td><?= $row->firstname; ?></td>
							<td><?= $row->lastname; ?></td>
							<td><?= $row->code_wilayah; ?></td>
							<td><?= $row->code_bk_hkk; ?></td>
							<td><?= $row->no_kta; ?></td>
							<td><?= $row->va; ?></td>
							<td>
								<a href="<?= site_url('Generate_va/edit_va/' . $row->user_id_profile); ?>"
									class="btn btn-xs btn-primary edit-va-link"
									data-nama="<?= $row->no_kta; ?>">
									<span class="glyphicon glyphicon-edit"></span> Set VA
								</a>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>

		<!-- Loading Overlay -->
		<div id="loadingOverlay" style="
    display: none;
    position: fixed;
    top: 0%;
    left: 0%;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 9999;
    justify-content: center;
    align-items: center;
	
">
			<div class="spinner-border" style="
        width: 80px;
        height: 80px;
        border: 10px solid #f3f3f3;
        border-top: 10px solid #3498db;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    "></div>
		</div>

		<style>
			@keyframes spin {
				0% {
					transform: rotate(0deg);
				}

				100% {
					transform: rotate(360deg);
				}
			}
		</style>
	<?php else : ?>
		<div class="alert alert-info">
			<strong>Info!</strong> Tidak ada data member yang belum memiliki VA.
		</div>
	<?php endif; ?>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
<!-- Bootstrap 3 -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap.min.css">
<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script type="text/javascript">
	$(document).ready(function() {
		// Init DataTable
		$('#membersTable').DataTable({
			"pageLength": 10,
			"ordering": true,
			"autoWidth": false,
			"language": {
				"search": "Cari:",
				"lengthMenu": "Tampilkan _MENU_ data",
				"zeroRecords": "Tidak ada data ditemukan",
				"info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
				"infoEmpty": "Tidak ada data tersedia",
				"infoFiltered": "(difilter dari _MAX_ total data)"
			}
		});

		// Konfirmasi sebelum redirect ke halaman edit
		$('.edit-va-link').on('click', function(e) {
			e.preventDefault();
			var href = $(this).attr('href');
			var noKTA = $(this).data('nama');

			Swal.fire({
				title: 'Konfirmasi',
				text: "Ingin mengisi VA untuk No KTA: " + noKTA + "?",
				icon: 'question',
				showCancelButton: true,
				confirmButtonText: 'Ya, Lanjutkan',
				cancelButtonText: 'Batal'
			}).then((result) => {
				if (result.isConfirmed) {
					window.location.href = href;
				}
			});
		});
	});
</script>

<script>
	//BULK VA
	$(document).ready(function() {
		$("#selectAll").on("click", function() {
			$("input[name='user_ids[]']").prop("checked", this.checked);
		});

		$("#bulkVAForm").on("submit", function(e) {
			e.preventDefault();

			let kodeBank = "89699";
			let data = [];

			$("input[name='user_ids[]']:checked").each(function() {
				let userId = $(this).val();
				let wilayah = $(this).data("wilayah");
				let bk = $(this).data("bk");
				let kta = $(this).data("kta");

				let va = kodeBank + wilayah + bk + kta;

				data.push({
					user_id: userId,
					va: va
				});
			});


			//Show loading
			$('#loadingOverlay').fadeIn(200);
			$.ajax({
				url: $(this).attr("action"),
				type: "POST",
				data: {
					members: data
				},
				success: function(res) {
					$('#loadingOverlay').fadeOut(200); //Hide loading
					try {
						let response = JSON.parse(res);
						if (response.status === "success") {
							Swal.fire("Berhasil", response.message, "success").then(() => {
								location.reload();
							});
						} else {
							Swal.fire("Error", response.message, "error");
						}
					} catch (e) {
						Swal.fire("Error", "Respon server tidak valid", "error");
					}
				},
				Error: function() {
					$('#loadingOverlay').fadeOut(200);
					Swal.fire("Error", "Terjadi kesalahan pada server", "error");
				}
			});
		});
	});
</script>

<script>
	// Tampilkan overlay saat halaman mulai load
	document.getElementById('loadingOverlay').style.display = 'flex';

	// Sembunyikan overlay setelah halaman selesai load
	window.addEventListener('load', function() {
		document.getElementById('loadingOverlay').style.display = 'none';
	});
</script>