  <?php $this->load->view('header'); ?>

  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-8">

        <div class="card shadow-sm">
          <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Profil Pengguna</h4>

          </div>

          <div class="card-body">

            <!-- Informasi pribadi -->
            <div class="card">
              <div class="card-header">
                <h5>Informasi pribadi</h5>
              </div>
              <div class="card-body">

                <?= form_open('user_profiles/update_info_pribadi/' . ($profile_data->user_id ?? '')) ?>

                <div class="row mb-3">
                  <label for="firstname" class="col-sm-4 col-form-label">Nama depan</label>
                  <div class="col-sm-8">
                    <input type="text" class="form-control"
                      id="firstname"
                      name="firstname"
                      value="<?= $profile_data->firstname ?? '' ?>"
                      placeholder="<?= empty($profile_data->firstname) ? 'Belum diisi' : '' ?>">
                  </div>
                </div>

                <div class="row mb-3">
                  <label for="lastname" class="col-sm-4 col-form-label">Nama belakang</label>
                  <div class="col-sm-8">
                    <input type="text" class="form-control"
                      id="lastname"
                      name="lastname"
                      value="<?= $profile_data->lastname ?? '' ?>"
                      placeholder="<?= empty($profile_data->lastname) ? 'Belum diisi' : '' ?>">
                  </div>
                </div>

                <div class="row mb-3">
                  <label for="gender" class="col-sm-4 col-form-label">Gender</label>
                  <div class="col-sm-8">
                    <select name="gender" id="gender" class="form-control">
                      <option value="">-- Pilih Gender --</option>
                      <option value="Male" <?= (!empty($profile_data->gender) && $profile_data->gender === 'Male') ? 'selected' : '' ?>>Male</option>
                      <option value="Female" <?= (!empty($profile_data->gender) && $profile_data->gender === 'Female') ? 'selected' : '' ?>>Female</option>
                    </select>
                  </div>
                </div>


                <div class="row mb-3">
                  <label for="birthplace" class="col-sm-4 col-form-label">Tempat lahir</label>
                  <div class="col-sm-8">
                    <input type="text"
                      class="form-control <?= empty($profile_data->birthplace) ? 'text-muted' : '' ?>"
                      id="birthplace"
                      name="birthplace"
                      value="<?= $profile_data->birthplace ?? '' ?>"
                      placeholder="<?= empty($profile_data->birthplace) ? 'Belum diisi' : '' ?>">
                  </div>
                </div>

                <div class="row mb-3">
                  <label for="dob" class="col-sm-4 col-form-label">Tanggal lahir</label>
                  <div class="col-sm-8">
                    <input type="date"
                      class="form-control <?= empty($profile_data->dob) ? 'text-muted' : '' ?>"
                      id="dob"
                      name="dob"
                      value="<?= $profile_data->dob ?? '' ?>">
                  </div>
                </div>

                <input type="hidden" name="idtype" value="Citizen">

                <div class="row mb-3">
                  <label for="idcard" class="col-sm-4 col-form-label">No Id Card</label>
                  <div class="col-sm-8">
                    <input type="text"
                      class="form-control"
                      id="idcard"
                      name="idcard"
                      value="<?= $profile_data->idcard ?? '' ?>"
                      placeholder="<?= empty($profile_data->idcard) ? 'Belum diisi' : '' ?>"
                      maxlength="16"
                      pattern="\d{16}"
                      title="ID Card harus 16 digit angka"
                      required
                      oninput="validateIdCard(this)">

                    <small id="idcardFeedback" class="text-danger d-none">ID Card tidak valid</small>
                  </div>
                </div>



                <div class="row mb-3">
                  <label for="website" class="col-sm-4 col-form-label">Website</label>
                  <div class="col-sm-8">
                    <input type="text"
                      class="form-control <?= empty($profile_data->website) ? 'text-muted' : '' ?>"
                      id="website"
                      name="website"
                      value="<?= $profile_data->website ?? '' ?>"
                      placeholder="<?= empty($profile_data->website) ? 'Belum diisi' : '' ?>">
                  </div>
                </div>

                <div class="row mb-3">
                  <label class="col-sm-4 col-form-label">Warga Asing</label>
                  <div class="col-sm-8 pt-1">
                    <?php if (isset($profile_data->warga_asing)) : ?>
                      <?php if ($profile_data->warga_asing == 0): ?>
                        <div class="badge bg-info text-dark">Tidak</div>
                      <?php else: ?>
                        <div class="badge bg-success">Ya</div>
                      <?php endif; ?>
                    <?php else: ?>
                      <span class="badge bg-dark">Tidak diketahui</span>
                    <?php endif; ?>
                  </div>
                </div>

                <button type="submit" class="btn btn-primary float-end">Simpan</button>

                <?= form_close() ?>



              </div>
            </div>



            <!-- Informasi kontak -->
            <div class="card shadow-sm mt-4">
              <div class="card-header">
                <h5>Informasi kontak</h5>
              </div>
              <div class="card-body">

                <?= form_open('user_profiles/update_informasi_kontak/' . ($profile_data->user_id ?? '')) ?>

                <div class="row mb-3">
                  <label class="col-sm-4 col-form-label">Email</label>
                  <div class="col-sm-8">
                    <?= $this->session->userdata('email') ?>
                  </div>
                </div>

                <div class="row mb-3">
                  <label for="lastname" class="col-sm-4 col-form-label">Mobile phone</label>
                  <div class="col-sm-8">
                    <input type="text" class="form-control"
                      id="mobilephone"
                      name="mobilephone"
                      value="<?= $profile_data->mobilephone ?? '' ?>"
                      placeholder="<?= empty($profile_data->mobilephone) ? 'Belum diisi' : '' ?>">
                  </div>
                </div>

                <button type="submit" class="btn btn-primary float-end">Simpan</button>

                <?= form_close() ?>

              </div>
            </div>





            <!-- Informasi persyaratan -->

            <div class="card shadow-sm mt-4">
              <div class="card-header">
                <h5>Informasi persyaratan</h5>
              </div>
              <div class="card-body">

                <?= form_open_multipart('user_profiles/update_informasi_persyaratan/' . ($profile_data->user_id ?? '')) ?>

                <div class="row mb-3">
                  <label for="surat_pernyataan" class="col-sm-4 col-form-label">Id card/KTP</label>
                  <div class="col-sm-8">
                    <input type="file" class="form-control" id="surat_pernyataan" name="id_file">
                    <?php if (!empty($profile_data->id_file)) : ?>
                      <div class="row">
                        <div class="col-8">
                          <small class="text-muted d-block text-truncate" style="max-width: 100%;">
                            File saat ini: <?= $profile_data->id_file ?>
                          </small>
                        </div>

                        <div class="col-4 text-end">
                          <small class="text-muted">
                            <a href="<?= base_url('uploads/' . $profile_data->id_file) ?>" target="_blank">Lihat file</a>
                          </small>
                        </div>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>

                <div class="row mb-3">
                  <label for="sertifikat_legal" class="col-sm-4 col-form-label">Sertifikat Legal</label>
                  <div class="col-sm-8">
                    <input type="file" class="form-control" id="sertifikat_legal" name="sertifikat_legal">
                    <?php if (!empty($profile_data->sertifikat_legal)) : ?>
                      <div class="row">
                        <div class="col-8">
                          <small class="text-muted d-block text-truncate" style="max-width: 100%;">
                            File saat ini: <?= $profile_data->sertifikat_legal ?>
                          </small>
                        </div>

                        <div class="col-4 text-end">
                          <small class="text-muted">
                            <a href="<?= base_url('uploads/' . $profile_data->sertifikat_legal) ?>" target="_blank">Lihat file</a>
                          </small>
                        </div>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>

                <div class="row mb-3">
                  <label for="tanda_bukti" class="col-sm-4 col-form-label">Tanda Bukti</label>
                  <div class="col-sm-8">
                    <input type="file" class="form-control" id="tanda_bukti" name="tanda_bukti">
                    <?php if (!empty($profile_data->tanda_bukti)) : ?>
                      <div class="row">
                        <div class="col-8">
                          <small class="text-muted d-block text-truncate" style="max-width: 100%;">
                            File saat ini: <?= $profile_data->tanda_bukti ?>
                          </small>
                        </div>

                        <div class="col-4 text-end">
                          <small class="text-muted">
                            <a href="<?= base_url('uploads/' . $profile_data->tanda_bukti) ?>" target="_blank">Lihat file</a>
                          </small>
                        </div>
                      </div>

                    <?php endif; ?>
                  </div>
                </div>

                <div class="row mb-3">
                  <label for="surat_dukungan" class="col-sm-4 col-form-label">Surat Dukungan</label>
                  <div class="col-sm-8">
                    <input type="file" class="form-control" id="surat_dukungan" name="surat_dukungan">
                    <?php if (!empty($profile_data->surat_dukungan)) : ?>
                      <div class="row">
                        <div class="col-8">
                          <small class="text-muted">File saat ini: <?= $profile_data->surat_dukungan ?></small>
                        </div>

                        <div class="col-4">
                          <small class="text-muted">
                            <a href="<?= base_url('uploads/' . $profile_data->surat_dukungan) ?>" target="_blank">Lihat file</a>
                          </small>
                        </div>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>

                <div class="row mb-3">
                  <label for="surat_pernyataan" class="col-sm-4 col-form-label">Surat Pernyataan</label>
                  <div class="col-sm-8">
                    <input type="file" class="form-control" id="surat_pernyataan" name="surat_pernyataan">
                    <?php if (!empty($profile_data->surat_pernyataan)) : ?>
                      <div class="row">
                        <div class="col-8">
                          <small class="text-muted">File saat ini: <?= $profile_data->surat_pernyataan ?></small>
                        </div>

                        <div class="col-4">
                          <small class="text-muted">
                            <a href="<?= base_url('uploads/' . $profile_data->surat_pernyataan) ?>" target="_blank">Lihat file</a>
                          </small>
                        </div>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>

                <div class="row mb-3">
                  <label for="surat_ijin_domisili" class="col-sm-4 col-form-label">Surat Ijin Domisili</label>
                  <div class="col-sm-8">
                    <input type="file" class="form-control" id="surat_ijin_domisili" name="surat_ijin_domisili">
                    <?php if (!empty($profile_data->surat_ijin_domisili)) : ?>

                      <div class="row">
                        <div class="col-8">
                          <small class="text-muted d-block text-truncate" style="max-width: 100%;">
                            File saat ini: <?= $profile_data->surat_ijin_domisili ?>
                          </small>
                        </div>

                        <div class="col-4 text-end">
                          <small class="text-muted">
                            <a href="<?= base_url('uploads/' . $profile_data->surat_ijin_domisili) ?>" target="_blank">Lihat file</a>
                          </small>
                        </div>
                      </div>

                    <?php endif; ?>
                  </div>
                </div>

                <button type="submit" class="btn btn-primary float-end">Simpan</button>

                <?= form_close() ?>

              </div>
            </div>


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