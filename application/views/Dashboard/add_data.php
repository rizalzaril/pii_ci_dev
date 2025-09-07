<div class="container mt-4">
	<div class="card">
		<div class="card-body bg-light">
			<h4 class="text text-center"></h4>
			<hr>

			<?php if ($this->session->flashdata('success_save')): ?>
				<div class="alert alert-success alert-dismissible fade show" role="alert">
					<strong>Sukses!</strong> <?= $this->session->flashdata('success_save'); ?>
					<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
				</div>
			<?php endif; ?>

			<?= form_open('/dashboard/store_data') ?>

			<!-- Input Kode otomatis -->
			<div class="mb-3 row">
				<label for="inputKode" class="col-sm-2 col-form-label">Kode</label>
				<div class="col-sm-10">
					<input type="text" class="form-control" id="inputKode" name="kode" value="<?php echo sprintf("%03s", $kode) ?>" readonly>
				</div>
			</div>

			<!-- Input keterangan -->
			<div class="mb-3 row">
				<label for="inputKeterangan" class="col-sm-2 col-form-label">Keterangan</label>
				<div class="col-sm-10">
					<input type="text" class="form-control" id="inputKeterangan" name="keterangan">
				</div>
			</div>

			<!-- Input grade -->
			<div class="mb-3 row">
				<label for="inputGrade" class="col-sm-2 col-form-label">angka awal (%)</label>
				<div class="col-sm-10">
					<input type="number" class="form-control" id="inputGrade" name="nilai_awal">
				</div>
			</div>

			<!-- Persen -->
			<div class="input-group mb-3">
				<label for="inputPersen" class="col-sm-2 col-form-label">Hasil akhir (%)</label>
				<input type="number" class="form-control" id="inputPersen" placeholder="" aria-label="" name="persen" aria-describedby="basic-addon2">
				<span class="input-group-text" id="basic-addon2">%</span>
			</div>

			<div class="float-end">
				<a href="<?= site_url('/dashboard/list_data') ?>" class="btn btn-dark">Kembali</a>
				<button type="submit" class="btn btn-primary"><i class="fa-solid fa-file"></i>Simpan</button>
			</div>

			<?= form_close() ?>
		</div>
	</div>
</div>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>

<?php if ($this->session->flashdata('success_save')): ?>
	<script>
		//alert delete

		Swal.fire({
			icon: 'success',
			title: 'Berhasil!',
			text: '<?= $this->session->flashdata("success_save") ?>',
			showConfirmButton: false,
			timer: 2000
		});
	</script>
<?php endif; ?>





<script>
	//script on input nilai awal to nilai akhir
	$(document).ready(function() {
		$('#inputGrade').on('input', function() {
			let keterangan = parseFloat($(this).val());
			let persen = '';

			if (keterangan >= 0 && keterangan < 10) {
				persen = 10;
			} else if (keterangan >= 10 && keterangan < 15) {
				persen = 15;
			} else if (keterangan >= 15 && keterangan < 20) {
				persen = 20;
			} else if (keterangan >= 20 && keterangan < 25) {
				persen = 25;
			} else if (keterangan >= 25 && keterangan < 35) {
				persen = 35;
			} else if (keterangan >= 35 && keterangan < 50) {
				persen = 50;
			} else if (keterangan >= 50 && keterangan < 75) {
				persen = 75;
			} else if (keterangan >= 75 && keterangan < 100) {
				persen = 100;
			} else if (keterangan >= 100 && keterangan < 101) {
				persen = 100;
			} else if (keterangan) {
				persen = 'Angka melebihi persentase';
			}


			$('#inputPersen').val(persen);
		});
	});
</script>