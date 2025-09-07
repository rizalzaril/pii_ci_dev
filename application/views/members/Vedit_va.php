<div class="container" style="margin-top:50px;">
	<div class="row">
		<div class="col-md-6 col-md-offset-3">
			<div class="panel panel-default shadow" style="border-radius:8px;">
				<div class="panel-heading text-center" style="background:#337ab7; color:#fff; border-radius:8px 8px 0 0;">
					<h3 class="panel-title" style="font-size:18px; font-weight:bold;">
						<span class="glyphicon glyphicon-edit"></span> Edit Virtual Account
					</h3>
				</div>

				<div class="panel-body" style="padding:30px;">
					<?php if (!empty($member)) : ?>
						<form action="<?= site_url('Generate_va/update_va'); ?>" method="post" class="form-horizontal" id="editVAForm">
							<input type="hidden" name="user_id" value="<?= $member->user_id_profile; ?>">
							<input type="hidden" id="noKTA" value="<?= $member->no_kta; ?>">
							<input type="hidden" id="codeWilayah" value="<?= $member->code_wilayah; ?>">
							<input type="hidden" id="codeBk" value="<?= $member->code_bk_hkk; ?>">

							<div class="form-group">
								<label class="col-sm-4 control-label">User ID Profile</label>
								<div class="col-sm-8">
									<input type="text" class="form-control input-lg" value="<?= $member->user_id_profile; ?>" readonly>
								</div>
							</div>

							<div class="form-group mt-2">
								<label class="col-sm-4 control-label">Kode Wilayah</label>
								<div class="col-sm-8">
									<input type="text" class="form-control input-lg" value="<?= $member->code_wilayah; ?>" readonly>
								</div>
							</div>

							<div class="form-group mt-2">
								<label class="col-sm-4 control-label">Kode BK HKK</label>
								<div class="col-sm-8">
									<input type="text" class="form-control input-lg" value="<?= $member->code_bk_hkk; ?>" readonly>
								</div>
							</div>

							<div class="form-group mt-2">
								<label class="col-sm-4 control-label">No KTA</label>
								<div class="col-sm-8">
									<input type="text" class="form-control input-lg" value="<?= $member->no_kta; ?>" readonly>
								</div>
							</div>

							<div class="form-group mt-2">
								<label for="va" class="col-sm-4 control-label">Virtual Account 17 Digit</label>
								<div class="col-sm-8">
									<input type="text" class="form-control input-lg text-center" name="va" id="va"
										value="" readonly required style="font-weight:bold; letter-spacing:2px;">
								</div>
							</div>

							<div class="form-group text-center" style="margin-top:30px;">
								<button type="submit" class="btn btn-success btn-lg" style="padding:10px 30px; border-radius:25px;">
									<span class="glyphicon glyphicon-floppy-disk"></span> Simpan
								</button>
								<a href="<?= site_url('members/list_members_non_va'); ?>" class="btn btn-default btn-lg" style="padding:10px 30px; border-radius:25px; margin-left:10px;">
									<span class="glyphicon glyphicon-chevron-left"></span> Kembali
								</a>
							</div>
						</form>
					<?php else : ?>
						<div class="alert alert-warning text-center">Data member tidak ditemukan.</div>
						<div class="text-center">
							<a href="<?= site_url('members/list_members_non_va'); ?>" class="btn btn-default btn-lg" style="border-radius:25px;">
								<span class="glyphicon glyphicon-chevron-left"></span> Kembali
							</a>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
<!-- Bootstrap 3 -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
	$(document).ready(function() {
		// Generate VA otomatis saat halaman load
		var kodeBank = "89699";
		var codeWilayah = $('#codeWilayah').val();
		var codeBk = $('#codeBk').val();
		var noKTA = $('#noKTA').val();

		var va = kodeBank + codeWilayah + codeBk + noKTA;
		$('#va').val(va);

		// Submit form pakai AJAX + SweetAlert
		$('#editVAForm').on('submit', function(e) {
			e.preventDefault();

			var form = $(this);
			var url = form.attr('action');
			var data = form.serialize();

			$.post(url, data, function(response) {
				try {
					var res = JSON.parse(response);

					if (res.status === 'success') {
						Swal.fire({
							icon: 'success',
							title: 'Berhasil',
							text: 'VA untuk No KTA ' + noKTA + ' berhasil diperbarui!',
							timer: 2500,
							showConfirmButton: false
						}).then(() => {
							window.location.href = "<?= site_url('Generate_va'); ?>";
						});
					} else {
						Swal.fire({
							icon: 'error',
							title: 'Gagal',
							text: res.message
						});
					}
				} catch (e) {
					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: 'Terjadi kesalahan server.'
					});
				}
			});
		});
	});
</script>