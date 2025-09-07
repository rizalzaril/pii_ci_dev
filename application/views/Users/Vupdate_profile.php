<div class="container mt-4">
  <div class="card">
    <div class="card-body bg-light">
      <h4 class="text text-center">Input Data</h4>
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