  <?php $this->load->view('header'); ?>

  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-8">

        <?php if ($this->session->flashdata('success_import')): ?>
          <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>Sukses!</strong> <?= $this->session->flashdata('success_import'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        <?php endif; ?>

        <?php if ($this->session->flashdata('error')): ?>
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Sukses!</strong> <?= $this->session->flashdata('error'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        <?php endif; ?>

        <div class="card my-4">
          <div class="card-header bg-success text-white">Import dari Excel</div>
          <div class="card-body">

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

          </div>
        </div>

      </div>
    </div>
  </div>

  <?php $this->load->view('footer'); ?>


  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>

  <!-- Alertify CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css" />
  <!-- Theme (Optional) -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/default.min.css" />

  <!-- Alertify JS -->
  <script src="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>


  <?php if ($this->session->flashdata('success')): ?>
    <script>
      alertify.set('notifier', 'position', 'top-right');
      alertify.success('<?= $this->session->flashdata("success") ?>');
    </script>
  <?php endif; ?>

  <!-- ID CARD 16 DIGIT VALIDATION  -->
  <script>
    let idCardTouched = false;

    function validateIdCard(input) {
      input.value = input.value.replace(/[^0-9]/g, '').slice(0, 16);
      const feedback = document.getElementById('idcardFeedback');

      if (!idCardTouched) return;

      if (input.value.length !== 16) {
        feedback.classList.remove('d-none');
      } else {
        feedback.classList.add('d-none');
      }
    }

    document.addEventListener('DOMContentLoaded', function() {
      const idcardInput = document.getElementById('idcard');

      if (idcardInput) {
        // Tandai bahwa user sudah menyentuh input
        idcardInput.addEventListener('focus', function() {
          idCardTouched = true;
        });

        // Jalankan validasi saat input berubah
        idcardInput.addEventListener('input', function() {
          validateIdCard(idcardInput);
        });
      }
    });
  </script>