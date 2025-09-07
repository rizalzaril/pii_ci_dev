<div class="container mt-5">

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

  <a href="<?= base_url('/dashboard/add_data') ?>" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Add data </a>


  <!-- MODAL IMPORT DARI EXCEL/CSV -->

  <!-- Button trigger modal -->
  <!-- <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#exampleModal">
    <i class="fas fa-file-excel"></i> Import XLSX/CSV
  </button> -->

  <!-- Modal -->
  <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog ">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Import XLSX/CSV</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <?= form_open_multipart('import/import_proccess/', ['id' => 'formImport']) ?>

          <!-- Form file upload XLSX -->
          <div class="mb-3">
            <label for="" class="form-label fw-bold">Nama File XLSX/CSV*</label>
            <input type="file" name="excel_file" class="form-control shadow-sm" accept=".xls,.xlsx,.csv" required>
          </div>

          <div class="mb-3">
            <label for="kodkel" class="form-label fw-bold">Kode Kelompok*</label>
            <select name="kodkel" id="kodkel" class="form-control form-select shadow-sm">
              <?php foreach ($list_kelompok as $kodkel) : ?>
                <option value="<?= $kodkel->id ?>"><?= $kodkel->id ?>. <?= $kodkel->name ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label for="passwordImport" class="form-label fw-bold">Password*</label>
            <input type="password" class="form-control shadow-sm" name="password" placeholder="Masukkan Password Default untuk Aplikan" required>
          </div>

          <button type="submit" class="btn btn-success">Import</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>

          <?= form_close() ?>

          <!-- Overlay Loading -->
          <div id="loadingOverlay"
            style="display:none; position:fixed; top:0; left:0; width:100%; height:100%;
            background:rgba(0,0,0,0.5); z-index:9999; text-align:center; color:white; padding-top:20%;">
            <div class="spinner-border text-light" role="status" style="width:3rem; height:3rem;"></div>
            <p class="mt-3 fs-5">Sedang mengimport data, mohon tunggu...</p>
          </div>

          <script>
            document.getElementById('formImport').addEventListener('submit', function() {
              document.getElementById('loadingOverlay').style.display = 'block';
            });
          </script>

        </div>
        <div class="modal-footer">

        </div>
      </div>
    </div>
  </div>


  <h3 class="d-flex justify-content-center">Dummy Users</h3>

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


  <table id="table_users" class="table table-sm table-striped">
    <thead>
      <tr>
        <th>No</th>
        <th>Username</th>
        <th>Email</th>
        <th>Status</th>
        <th>Aksi</th>
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



<!-- Inisialisasi DataTable table users -->
<script>
  let table = new DataTable('#table_users', {
    processing: true,
    serverSide: true,
    ajax: {
      url: "<?= base_url('users/get_users') ?>",
      type: "GET",
      data: function(d) {
        let sort_val = $('#sort_by').val();
        if (sort_val) {
          let parts = sort_val.split('|');
          d.order_by = parts[0];
          d.order_dir = parts[1];
        }
      }
    }
  });

  // reload table saat dropdown berubah
  $('#sort_by').on('change', function() {
    table.ajax.reload();
  });
</script>