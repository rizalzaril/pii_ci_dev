<?php
$this->load->view('header');
$this->load->view('footer');
?>

<!-- Tambahkan di bagian <head> -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">


<div class="container mt-5 mb-5">
  <div class="row justify-content-center">

    <div class="col-md-6">
      <h2 class="text-center mb-4">Daftar Anggota</h2>

      <!-- Flash message -->
      <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success">
          <?= html_escape($this->session->flashdata('success')) ?>
        </div>
      <?php endif; ?>

      <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <strong>Maaf!</strong> <?= $this->session->flashdata('error'); ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>

      <!-- Tampilkan error validasi -->
      <?= validation_errors('<div class="alert alert-danger">', '</div>'); ?>

      <?= form_open('auth/register') ?>

      <!-- CSRF Token -->
      <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>"
        value="<?= $this->security->get_csrf_hash(); ?>" />

      <div class="mb-3">
        <div class="row">
          <div class="col-6">
            <label for="first_name" class="form-label">First Name</label>
            <input type="text" class="form-control" id="first_name" name="first_name" required
              value="<?= html_escape(set_value('firstname')) ?>">
          </div>
          <div class="col-6">
            <label for="last_name" class="form-label">Last Name</label>
            <input type="text" class="form-control" id="last_name" name="last_name" required
              value="<?= html_escape(set_value('lastname')) ?>">
          </div>
        </div>
      </div>

      <div class="mb-3">
        <div class="row">
          <div class="col-6">
            <label for="birth_place" class="form-label">Birth Place</label>
            <input type="text" class="form-control" id="birth_place" name="birth_place" required
              value="<?= html_escape(set_value('birthplace')) ?>">
          </div>
          <div class="col-6">
            <label for="dob" class="form-label">Date of Birth</label>
            <input type="date" class="form-control" id="dob" name="dob" required
              value="<?= html_escape(set_value('dob')) ?>">
          </div>
        </div>
      </div>

      <!-- Gender -->
      <div class="mb-3">
        <label class="form-label">Gender</label>
        <div class="row">
          <div class="col-3">
            <div class="form-check">
              <input class="form-check-input" type="radio" name="gender" id="gender_male" value="Male" required>
              <label class="form-check-label" for="gender_male">Pria</label>
            </div>
          </div>
          <div class="col-3">
            <div class="form-check">
              <input class="form-check-input" type="radio" name="gender" id="gender_female" value="Female">
              <label class="form-check-label" for="gender_female">Wanita</label>
            </div>
          </div>
        </div>
      </div>




      <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" name="email" required
          value="<?= html_escape(set_value('email')) ?>">
      </div>

      <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control" id="password" name="password" required>
      </div>

      <div class="mb-3">
        <label for="passconf" class="form-label">Konfirmasi Password</label>
        <input type="password" class="form-control" id="passconf" name="passconf" required>
      </div>

      <div class="mb-3">
        <!-- Tampilkan CAPTCHA -->
        <?php if (isset($captcha['image'])): ?>
          <div class="mb-3">
            <label class="form-label">Kode Keamanan</label>
            <div class="d-flex align-items-center">
              <p id="captcha-image" class="me-2 mb-0"><?= $captcha['image']; ?></p>
              <!-- <button type="button" class="btn btn-sm btn-outline-secondary" onclick="refreshCaptcha()" title="Refresh CAPTCHA">⟳</button> -->

            </div>
          </div>
        <?php else: ?>
          <p class="text-danger">Captcha gagal dimuat. Coba reload halaman.</p>
        <?php endif; ?>


        <input type="text" name="captcha" style="text-transform: uppercase;" class="form-control mb-3" placeholder="Masukkan kode di atas" required>
      </div>

      <button type="submit" class="btn btn-primary w-100">Daftar</button>
      <?= form_close() ?>
    </div>
  </div>
</div>