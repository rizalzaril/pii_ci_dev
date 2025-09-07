<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Detail AER</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="container py-4">

  <h2 class="mb-3">Detail Apec Profile</h2>

  <?php if (!empty($detail_acpe)): ?>

    <div class="">

      <!-- Header Detail -->
      <div class="card mb-4 bg-light">
        <div class="card-header bg-light">
          <div class="d-flex justify-content-between align-items-center">
            <h3><?= $detail_acpe['nama'] ?></h3>
            <p class="text-muted">Member Since: <?= date('d/m/Y', strtotime($detail_acpe['created'])) ?></p>
          </div>
        </div>

        <div class="card-body">
          <!-- PHOTO -->
          <div class="d-flex justify-content-center mb-3">
            <img class="img-thumbnail" width="250"
              src="<?= base_url('assets/uploads/') . $detail_acpe['photo'] ?>"
              alt="">
          </div>

          <!-- PROFILE DETAIL -->
          <div class="card mb-3 shadow">
            <div class="card-body">
              <?php
              $profileFields = [
                'First Name' => 'firstname',
                'Last Name' => 'lastname',
                'Gender' => 'gender',
                'Mobile Phone' => 'mobilephone',
                'ID Card' => function ($d) {
                  if (!empty($d['idcard'])) {
                    // ambil type
                    $idType = isset($d['idtype']) ? ' (' . $d['idtype'] . ')' : '';

                    // buat tombol download PDF
                    $downloadBtn = '<a href="' . base_url('uploads/idcard/' . $d['idcard']) . '" 
										class="btn btn-sm btn-danger ms-2" target="_blank">
										<i class="fa-solid fa-arrow-down"></i>Download
										</a>';

                    // tampilkan nama file + idtype + tombol download
                    return $d['idcard'] . $idType . ' ' . $downloadBtn;
                  }
                  return '-';
                },
                'VA' => 'va',
                'Date of Birth' => function ($d) {
                  return $d['birthplace'] . ', ' . date('d-m-Y', strtotime($d['dob']));
                },
                'Website' => 'website',
                'Bersedia Menerima Bahan Publikasi' => function ($d) {
                  return (isset($d['is_public']) && $d['is_public'] == '1') ? 'Bersedia data pribadi diserahkan ke PII' : '';
                },
                'Description' => 'description'
              ];
              ?>

              <?php foreach ($profileFields as $label => $key): ?>
                <div class="row mb-2">
                  <div class="col-4">
                    <label class="fw-bold"><?= $label ?></label>
                  </div>
                  <div class="col-8">
                    <p class="mb-0">
                      <?= is_callable($key) ? $key($detail_acpe) : ($detail_acpe[$key] ?? '-') ?>
                    </p>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>



          <!-- CONTACT -->
          <div class="card mb-3 shadow">
            <div class="card-body">
              <h5>Phone</h5>
              <hr>
              <?php foreach ($detail_acpe['addresses'] as $contact): ?>
                <p><?= $contact['phone'] ?></p>
              <?php endforeach; ?>
              <h5>Email</h5>
              <hr>
              <?php foreach ($detail_acpe['addresses'] as $contact): ?>
                <p><?= $contact['email'] ?></p>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- ADDRESSES -->
          <div class="card mb-3 shadow">
            <div class="card-body">
              <h5 class="fw-bold">Address</h5>
              <table class="table table-borderless">
                <tbody>
                  <?php if (!empty($detail_acpe['addresses'])): ?>
                    <?php foreach ($detail_acpe['addresses'] as $addr): ?>
                      <tr>
                        <td style="width: 200px;">
                          <?= $addr['desc'] ?>
                          <br>
                          <?php if (isset($addr['is_mailing']) && $addr['is_mailing'] == 1): ?>
                            Mailing Address
                          <?php else: ?>
                            <?php
                            // cek ke tabel referensi address type
                            $label = '-';
                            if (!empty($user_address)) {

                              if ($ua->id == $addr['address_type']) {
                                $label = $addr['desc'];
                                break;
                              }
                            }
                            echo $label;
                            ?>
                          <?php endif; ?>
                        </td>
                        <td>
                          <?= $addr['address'] ?? '-' ?><br>
                          <?= $addr['city'] ?? '' ?><br>
                          <?= $addr['province'] ?? '' ?><br>
                          <?= $addr['zipcode'] ?? '' ?>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <tr>
                      <td colspan="2"><em>Tidak ada data alamat</em></td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>




          <!-- EXPERIENCE -->
          <div class="card mb-3">
            <div class="card-body">
              <h3 class="fw-bold text-center">Pengalaman Kerja/Profesional</h3>
              <table class="table table-bordered">
                <thead>
                  <tr>
                    <th>Perusahaan</th>
                    <th>Jabatan/Tugas</th>
                    <th>Lokasi</th>
                    <th>Periode</th>
                    <th>Nama Aktifitas/Kegiatan/Proyek </th>
                    <th>Uraian Singkat Tugas dan Tanggung Jawab Profesional </th>
                    <th>Dokumen pendukung</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (!empty($detail_acpe['experiences'])): ?>
                    <?php foreach ($detail_acpe['experiences'] as $exp): ?>
                      <tr class="fw-bold text-muted">

                        <td class="text-muted">
                          <?= $exp['company'] ?? '-' ?><br>
                        </td>
                        <td class="text-muted"><?= $exp['title'] ?? '' ?></td>
                        <td class="text-muted"><?= $exp['location'] . ', ' . $exp['provinsi'] . ', ' . $exp['negara'] ?? '' ?></td>

                        <?php
                        $startmonth = isset($exp['startmonth']) ? (int)$exp['startmonth'] : 0;
                        $startyear  = $exp['startyear'] ?? '';
                        $endmonth   = isset($exp['endmonth']) ? (int)$exp['endmonth'] : 0;
                        $endyear    = $exp['endyear'] ?? '';

                        $startmonthName = $startmonth > 0 ? date('M', mktime(0, 0, 0, $startmonth, 1)) : '';
                        $endmonthName   = $endmonth > 0 ? date('M', mktime(0, 0, 0, $endmonth, 1)) : '';
                        ?>
                        <td class="text-muted">
                          <?= trim("$startmonthName $startyear - $endmonthName $endyear") ?>
                        </td>



                        <td class="text-muted"><?= $exp['actv'] ?? '' ?></td>
                        <td class="text-muted">
                          <?php
                          $description = $exp['description'] ?? '';

                          if (!empty($description)) {
                            // Pisahkan teks berdasarkan nomor di awal (1. 2. 3. ...)
                            $items = preg_split('/\d+\.\s*/', $description, -1, PREG_SPLIT_NO_EMPTY);

                            if (!empty($items)) {
                              echo '<ol>'; // Daftar bernomor otomatis
                              foreach ($items as $item) {
                                echo '<li>' . trim($item) . '</li>';
                              }
                              echo '</ol>';
                            } else {
                              // Jika tidak ada nomor, tampilkan teks utuh
                              echo nl2br(htmlspecialchars($description));
                            }
                          } else {
                            echo '-'; // Default jika kosong
                          }
                          ?>
                        </td>

                        <td class="text-muted">
                          <?php if (!empty($exp['attachment'])): ?>
                            <a href="<?= base_url('uploads/attachment/' . $exp['attachment']) ?>"
                              class="btn btn-sm btn-danger"
                              target="_blank">
                              <i class="fa-solid fa-arrow-down"></i> PDF
                            </a>
                          <?php else: ?>
                            -
                          <?php endif; ?>
                        </td>

                      </tr>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <tr>
                      <td colspan="2"><em>Tidak ada pengalaman</em></td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>


          <!-- PENDIDIKAN -->
          <div class="card mb-3 shadow">
            <div class="card-body">
              <h3 class="fw-bold text-center">Pendidikan</h3>
              <table class="table table-bordered">
                <thead>
                  <tr>
                    <th>Tipe Pendidikan</th>
                    <th>Institusi / Universitas</th>
                    <th>Tahun</th>
                    <th>Tingkat Pendidikan</th>
                    <th>Fakultas</th>
                    <th>Jurusan/Kejuruan/ Nomor Sertifikat</th>
                    <th>IPK/Nilai</th>
                    <th>Gelar</th>
                    <th>Aktivitas dan kegiatan sosial</th>
                    <th>Deskripsi</th>
                    <th>Dokumen pendukung</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $hasEducation = false; // Untuk cek apakah ada data yang ditampilkan
                  if (!empty($detail_acpe['educations'])): ?>
                    <?php foreach ($detail_acpe['educations'] as $edu): ?>
                      <?php if (isset($edu['status']) && $edu['status'] == 1): ?>
                        <?php $hasEducation = true; ?>
                        <tr class="fw-bold text-muted">
                          <td class="text-muted">
                            <?php
                            $typeMap = [
                              '1' => 'Akademis',
                              '2' => 'Profesi'
                            ];
                            ?>
                            <?= $typeMap[$edu['type']] ?? '-' ?>
                          </td>

                          <td class="text-muted"><?= $edu['school'] ?? '' ?></td>
                          <td class="text-muted"><?= $edu['startdate'] . ' ' . $edu['enddate'] ?? '' ?></td>
                          <td class="text-muted"><?= $edu['degree'] ?? '' ?></td>
                          <td class="text-muted"><?= $edu['mayor'] ?? '' ?></td>
                          <td class="text-muted"><?= $edu['fieldofstudy'] ?? '' ?></td>
                          <td class="text-muted"><?= $edu['score'] ?? '' ?></td>
                          <td class="text-muted"><?= $edu['title'] ?? '' ?></td>
                          <td class="text-muted"><?= $edu['activities'] ?? '' ?></td>
                          <td class="text-muted">
                            <?php
                            $description = $edu['description'] ?? '';
                            if (!empty($description)) {
                              $items = preg_split('/\d+\.\s*/', $description, -1, PREG_SPLIT_NO_EMPTY);
                              if (!empty($items)) {
                                echo '<ol>';
                                foreach ($items as $item) {
                                  echo '<li>' . trim($item) . '</li>';
                                }
                                echo '</ol>';
                              } else {
                                echo nl2br(htmlspecialchars($description));
                              }
                            } else {
                              echo '-';
                            }
                            ?>
                          </td>
                          <td class="text-muted">
                            <?php if (!empty($edu['attachment'])): ?>
                              <a href="<?= base_url('uploads/attachment/' . $edu['attachment']) ?>"
                                class="btn btn-sm btn-danger"
                                target="_blank">
                                <i class="fa-solid fa-arrow-down"></i> PDF
                              </a>
                            <?php else: ?>
                              -
                            <?php endif; ?>
                          </td>

                        </tr>
                      <?php endif; ?>
                    <?php endforeach; ?>
                  <?php endif; ?>

                  <?php if (!$hasEducation): ?>
                    <tr>
                      <td colspan="10"><em>Tidak ada pendidikan dengan status aktif</em></td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>

          <!-- CERTIFICATIONS -->
          <div class="card mb-3 shadow">
            <div class="card-body">
              <h3 class="fw-bold text-center">Sertifikasi Profesional</h3>
              <table class="table table-bordered">
                <thead>
                  <tr>
                    <th>Nama Sertifikasi </th>
                    <th>Otoritas Sertifikasi </th>
                    <th>Nomor lisensi </th>
                    <th>URL sertifikasi </th>
                    <th>Kualifikasi </th>
                    <th>Tanggal</th>
                    <th>Deskripsi</th>
                    <th>Dokumen pendukung</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $hasCert = false; // Untuk cek apakah ada data yang ditampilkan
                  if (!empty($detail_acpe['certifications'])): ?>
                    <?php foreach ($detail_acpe['certifications'] as $cert): ?>
                      <?php if (isset($cert['status']) && $cert['status'] == 1): ?>
                        <?php $hasCert = true; ?>
                        <tr class="fw-bold text-muted">
                          <td class="text-muted"><?= $cert['cert_name'] ?? '-' ?></td>
                          <td class="text-muted"><?= $cert['cert_auth'] ?? '' ?></td>
                          <td class="text-muted"><?= $cert['lic_num'] ?? '' ?></td>
                          <td class="text-muted"><?= $cert['cert_url'] ?? '' ?></td>
                          <td class="text-muted"><?= $cert['cert_title'] ?? '' ?></td>
                          <td class="text-muted">
                            <?php
                            // Tampilkan tanggal mulai
                            $startYear  = isset($cert['startyear']) ? $cert['startyear'] : '';
                            $startMonth = isset($cert['startmonth']) ? date('M', mktime(0, 0, 0, (int)$cert['startmonth'], 1)) : '';

                            echo trim($startMonth . ' ' . $startYear);

                            echo ' - ';

                            // Tampilkan tanggal akhir atau "Present"
                            if (isset($cert['is_present']) && $cert['is_present'] == '1') {
                              echo 'Present';
                            } else {
                              $endYear  = isset($cert['endyear']) ? $cert['endyear'] : '';
                              $endMonth = isset($cert['endmonth']) ? date('M', mktime(0, 0, 0, (int)$cert['endmonth'], 1)) : '';
                              echo trim($endMonth . ' ' . $endYear);
                            }
                            ?>
                          </td>
                          <td class="text-muted">
                            <?php
                            $description = $cert['description'] ?? '';
                            if (!empty($description)) {
                              $items = preg_split('/\d+\.\s*/', $description, -1, PREG_SPLIT_NO_EMPTY);
                              if (!empty($items)) {
                                echo '<ol>';
                                foreach ($items as $item) {
                                  echo '<li>' . trim($item) . '</li>';
                                }
                                echo '</ol>';
                              } else {
                                echo nl2br(htmlspecialchars($description));
                              }
                            } else {
                              echo '-';
                            }
                            ?>
                          </td>



                          <td class="text-muted">
                            <?php if (!empty($cert['attachment'])): ?>
                              <a href="<?= base_url('uploads/attachment/' . $cert['attachment']) ?>"
                                class="btn btn-sm btn-danger"
                                target="_blank">
                                <i class="fa-solid fa-arrow-down"></i> PDF
                              </a>
                            <?php else: ?>
                              -
                            <?php endif; ?>
                          </td>
                        </tr>
                      <?php endif; ?>
                    <?php endforeach; ?>
                  <?php endif; ?>

                  <?php if (!$hasEducation): ?>
                    <tr>
                      <td colspan="10"><em>Tidak ada pendidikan dengan status aktif</em></td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>

        </div>
      </div>
    </div>

  <?php else: ?>
    <div class="alert alert-warning">Data tidak ditemukan.</div>
  <?php endif; ?>

</body>

</html>