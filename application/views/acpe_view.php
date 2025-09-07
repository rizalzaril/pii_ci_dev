<div class="container mt-5">

  <?php if ($this->session->flashdata('success') || $this->session->flashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <strong>Sukses!</strong> <?= $this->session->flashdata('success'); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>

  <!-- error validation -->
  <?php if ($this->session->flashdata('error') || $this->session->flashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <strong>Gagal!</strong> <?= $this->session->flashdata('error'); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>

  <a href="<?= base_url('/dashboard/add_data') ?>" class="btn btn-primary mb-3"><i class="fa-solid fa-plus"></i> Add data </a>

  <!-- button import csv/xlxs -->
  <button type="button" class="btn btn-dark mb-3" data-bs-toggle="modal" data-bs-target="#exampleModal">
    <i class="fas fa-file-excel"></i> Import
  </button>


  <!-- Modal import csv/xlxs -->

  <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Import data dari CSV/XLSX</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="card my-4">
            <div class="card-header bg-success text-white">Import dari Excel</div>
            <div class="card-body">
              <?= form_open_multipart('dashboard/import_acpe/') ?>
              <div class="mb-3">
                <input type="file" name="excel_file" class="form-control" accept=".xls,.xlsx,.csv," required>
              </div>

            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-success">Import</button>
          <?= form_close() ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Table acpe -->

  <table id="table_acpe" class="table table-sm table-striped mt-3">
    <thead>
      <tr>
        <th scope="col">#</th>
        <th scope="col">No Acpe</th>
        <th scope="col">Doi</th>
        <th scope="col">nama</th>
        <th scope="col">KTA</th>
        <th scope="col">New Po No</th>
        <th scope="col">Bk Acpe</th>
        <th scope="col">Asosiasi Prof</th>
        <th scope="col">Aksi</th>
      </tr>
    </thead>

    <tbody>
      <?php
      $no = 1;
      foreach ($list_acpe as $acpe) : ?>
        <tr>
          <th scope="row"><?= $no++ ?></th>
          <td><?= $acpe->no_acpe ?></td>
          <td><?= $acpe->doi ?></td>
          <td><?= $acpe->nama ?></td>
          <td><?= $acpe->kta ?></td>
          <td><?= $acpe->new_po_no ?></td>
          <td><?= $acpe->bk_acpe ?></td>
          <td><?= $acpe->asosiasi_prof ?></td>

          <td>
            <a class="btn btn-primary btn-sm" href="<?= base_url('/dashboard/edit_data/' . $acpe->id) ?>"><i class="fas fa-edit"></i></a>
            <a class="btn btn-danger btn-sm btn-delete" data-id="<?= $acpe->id ?>" data-kode="<?= $acpe->id ?>"><i class="fas fa-trash"></i></a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

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


<?php $this->load->view('footer'); ?>

<!-- Inisialisasi DataTable table users -->
<script>
  new DataTable('#table_acpe', {
    processing: true,
    // serverSide: true,
    // ajax: {
    //   url: "<?= base_url('users/get_users') ?>",
    //   type: "GET"
    // }
  });
</script>