<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Detail Pengguna</title>

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

  <style>
    body {
      background-color: #f0f2f5;
    }

    .profile-card {
      border-radius: 10px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    }

    .profile-header {
      text-align: center;
    }

    .profile-icon {
      font-size: 80px;
      color: #0d6efd;
    }

    .label {
      font-weight: 600;
      color: #6c757d;
    }
  </style>
</head>

<body>

  <div class="container my-5">
    <div class="row justify-content-center">
      <div class="col-lg-8">

        <div class="card profile-card p-4">
          <div class="profile-header mb-4">
            <i class="fas fa-user-circle profile-icon"></i>
            <h4 class="mt-2">Profil Pengguna</h4>
          </div>

          <?php if ($user_detail): ?>
            <div class="row mb-3">
              <div class="col-md-4 label">Username</div>
              <div class="col-md-8"><?= htmlspecialchars($user_detail->username ?? '-') ?></div>
            </div>

            <div class="row mb-3">
              <div class="col-md-4 label">Email</div>
              <div class="col-md-8"><?= htmlspecialchars($user_detail->email ?? '-') ?></div>
            </div>

            <div class="row mb-3">
              <div class="col-md-4 label">Status Akun</div>
              <div class="col-md-8">
                <?php if ($user_detail->activated == 1): ?>
                  <span class="badge bg-success">Aktif</span>
                <?php else: ?>
                  <span class="badge bg-secondary">Nonaktif</span>
                <?php endif; ?>
              </div>
            </div>

            <?php if (!empty($user_detail->address)): ?>
              <div class="row mb-3">
                <div class="col-md-4 label">Alamat</div>
                <div class="col-md-8"><?= htmlspecialchars($user_detail->address) ?></div>
              </div>
            <?php endif; ?>

            <?php if (!empty($user_detail->phone)): ?>
              <div class="row mb-3">
                <div class="col-md-4 label">No. Telepon</div>
                <div class="col-md-8"><?= htmlspecialchars($user_detail->phone) ?></div>
              </div>
            <?php endif; ?>

            <?php if (!empty($user_detail->firstname)): ?>
              <div class="row mb-3">
                <div class="col-md-4 label">No. Telepon</div>
                <div class="col-md-8"><?= htmlspecialchars($user_detail->firstname) ?></div>
              </div>
            <?php endif; ?>

            <div class="row mb-3">
              <div class="col-md-4 label">First name</div>
              <div class="col-md-8">
                <?= !empty($user_detail->firstname) ? htmlspecialchars($user_detail->firstname) : '<span class="text-muted">Belum diisi</span>' ?>
              </div>
            </div>

            <div class="row mb-3">
              <div class="col-md-4 label">Last name</div>
              <div class="col-md-8">
                <?= !empty($user_detail->lastname) ? htmlspecialchars($user_detail->lastname) : '<span class="text-muted">Belum diisi</span>' ?>
              </div>
            </div>

            <!-- gender -->
            <div class="row mb-3">
              <div class="col-md-4 label">Gender</div>
              <div class="col-md-8">
                <?= !empty($user_detail->gender) ? htmlspecialchars($user_detail->gender) : '<span class="text-muted">Belum diisi</span>' ?>
              </div>
            </div>

            <!-- id type -->
            <div class="row mb-3">
              <div class="col-md-4 label">Id type</div>
              <div class="col-md-8">
                <?= !empty($user_detail->idtype) ? htmlspecialchars($user_detail->idtype) : '<span class="text-muted">Belum diisi</span>' ?>
              </div>
            </div>

            <!-- birthplace -->
            <div class="row mb-3">
              <div class="col-md-4 label">Birth place</div>
              <div class="col-md-8">
                <?= !empty($user_detail->birthplace) ? htmlspecialchars($user_detail->birthplace) : '<span class="text-muted">Belum diisi</span>' ?>
              </div>
            </div>

            <!-- tgl lahir -->
            <div class="row mb-3">
              <div class="col-md-4 label">Birth date</div>
              <div class="col-md-8">
                <?= !empty($user_detail->dob) ? htmlspecialchars($user_detail->dob) : '<span class="text-muted">Belum diisi</span>' ?>
              </div>
            </div>

            <!-- mobile phone -->
            <div class="row mb-3">
              <div class="col-md-4 label">Mobile phone</div>
              <div class="col-md-8">
                <?= !empty($user_detail->mobilephone) ? htmlspecialchars($user_detail->mobilephone) : '<span class="text-muted">Belum diisi</span>' ?>
              </div>
            </div>

            <!-- website -->
            <div class="row mb-3">
              <div class="col-md-4 label">Website</div>
              <div class="col-md-8">
                <?= !empty($user_detail->website) ? htmlspecialchars($user_detail->website) : '<span class="text-muted">Belum diisi</span>' ?>
              </div>
            </div>

            <!-- VA -->
            <div class="row mb-3">
              <div class="col-md-4 label">Virtual account</div>
              <div class="col-md-8">
                <?= !empty($user_detail->va) ? htmlspecialchars($user_detail->va) : '<span class="text-muted">Belum diisi</span>' ?>
              </div>
            </div>


          <?php else: ?>
            <div class="alert alert-warning text-center">
              <i class="fa fa-exclamation-triangle me-2"></i>Data pengguna tidak ditemukan.
            </div>
          <?php endif; ?>

          <div class="text-end mt-4">
            <a href="<?= base_url('users') ?>" class="btn btn-outline-primary">
              <i class="fa fa-arrow-left me-1"></i> Kembali ke Daftar
            </a>
          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>