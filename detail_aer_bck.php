<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="_ci_view" content="<?php echo $_ci_view; ?>">
	<title>Asean Eng Detail</title>
	<?php $this->load->view('admin/common/meta_tags'); ?>
	<?php $this->load->view('admin/common/before_head_close'); ?>
</head>

<body class="skin-blue">
	<?php $this->load->view('admin/common/after_body_open'); ?>
	<?php $this->load->view('admin/common/header'); ?>

	<div class="wrapper row-offcanvas row-offcanvas-left">
		<?php $this->load->view('admin/common/left_side'); ?>

		<aside class="right-side">
			<!-- Content Header (Page header) -->
			<section class="content-header">
				<h1>ASEAN Eng Profile</h1>
				<ol class="breadcrumb">
					<li><a href="<?php echo base_url('admin/dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
					<li class="active">Manage Members</li>
				</ol>
			</section>

			<?php if (!empty($detail_aer)): ?>
				<div class="col-md-12 d-flex justify-content-center">
					<div class="">
						<!-- Header Detail -->
						<div class="panel panel-default">
							<div class="panel-heading">
								<div class="row">
									<div class="col-xs-6">
										<h3 class="panel-title"><?= $detail_aer['nama'] ?></h3>
									</div>
									<div class="col-xs-6 text-right">
										<p class="text-muted" style="margin:0;">
											Member Since: <?= date('d/m/Y', strtotime($detail_aer['created'])) ?>
										</p>
									</div>
								</div>
							</div>
							<div class="panel-body">

								<!-- PHOTO -->
								<div class="text-center" style="margin-bottom:15px;">
									<img class="img-thumbnail" width="250"
										src="https://updmember.pii.or.id/assets/uploads/<?= $detail_aer['photo'] ?>"
										alt="Foto Profil">
								</div>

								<!-- PROFILE DETAIL -->
								<div class="panel panel-default">
									<div class="panel-body">
										<?php
										$profileFields = [
											'First Name' => 'firstname',
											'Last Name'  => 'lastname',
											'Gender'     => 'gender',
											'Mobile Phone' => 'mobilephone',
											'ID Card' => function ($d) {
												if (!empty($d['idcard'])) {
													$idType = isset($d['idtype']) ? ' (' . $d['idtype'] . ')' : '';
													$downloadBtn = '<a href="' . base_url('assets/uploads/idcard/' . $d['idcard']) . '" class="btn btn-xs btn-danger" target="_blank" style="margin-left:5px;">
													<i class="fa fa-arrow-down"></i> Download</a>';
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
												return (isset($d['is_public']) && $d['is_public'] == '1')
													? 'Bersedia data pribadi diserahkan ke PII'
													: '';
											},
											'Description' => 'description'
										];
										?>

										<?php foreach ($profileFields as $label => $key): ?>
											<div class="row" style="margin-bottom:10px;">
												<div class="col-xs-4">
													<label><strong><?= $label ?></strong></label>
												</div>
												<div class="col-xs-8">
													<p class="form-control-static">
														<?= is_callable($key) ? $key($detail_aer) : ($detail_aer[$key] ?? '-') ?>
													</p>
												</div>
											</div>
										<?php endforeach; ?>
									</div>
								</div>

								<!-- CONTACT -->
								<div class="panel panel-default">
									<div class="panel-body">
										<h5>Phone</h5>
										<hr>
										<?php foreach ($detail_aer['addresses'] as $contact): ?>
											<p><?= $contact['phone'] ?></p>
										<?php endforeach; ?>

										<h5>Email</h5>
										<hr>
										<?php foreach ($detail_aer['addresses'] as $contact): ?>
											<p><?= $contact['email'] ?></p>
										<?php endforeach; ?>
									</div>
								</div>

								<!-- ADDRESSES -->
								<div class="panel panel-default">
									<div class="panel-body">
										<h5><strong>Address</strong></h5>
										<table class="table">
											<tbody>
												<?php if (!empty($detail_aer['addresses'])): ?>
													<?php foreach ($detail_aer['addresses'] as $addr): ?>
														<tr>
															<td style="width:200px;">
																<?= $addr['desc'] ?><br>
																<?php if (!empty($addr['is_mailing']) && $addr['is_mailing'] == 1): ?>
																	Mailing Address
																<?php else: ?>
																	-
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
								<h3 class="text-center"><strong>Pengalaman Kerja/Profesional</strong></h3>
								<table class="table table-bordered">
									<thead>
										<tr>
											<th>Perusahaan</th>
											<th>Jabatan/Tugas</th>
											<th>Lokasi</th>
											<th>Periode</th>
											<th>Nama Aktifitas/Kegiatan/Proyek</th>
											<th>Uraian Singkat Tugas dan Tanggung Jawab Profesional</th>
											<th>Dokumen pendukung</th>
										</tr>
									</thead>
									<tbody>
										<?php if (!empty($detail_aer['experiences'])): ?>
											<?php foreach ($detail_aer['experiences'] as $exp): ?>
												<tr class="text-dark">
													<td><?= $exp['company'] ?? '-' ?></td>
													<td><?= $exp['title'] ?? '' ?></td>
													<td><?= ($exp['location'] ?? '') . ', ' . ($exp['provinsi'] ?? '') . ', ' . ($exp['negara'] ?? '') ?></td>
													<?php
													$startmonth = isset($exp['startmonth']) ? (int)$exp['startmonth'] : 0;
													$startyear  = $exp['startyear'] ?? '';
													$endmonth   = isset($exp['endmonth']) ? (int)$exp['endmonth'] : 0;
													$endyear    = $exp['endyear'] ?? '';
													$startmonthName = $startmonth > 0 ? date('M', mktime(0, 0, 0, $startmonth, 1)) : '';
													$endmonthName   = $endmonth > 0 ? date('M', mktime(0, 0, 0, $endmonth, 1)) : '';
													?>
													<td><?= trim("$startmonthName $startyear - $endmonthName $endyear") ?></td>
													<td><?= $exp['actv'] ?? '' ?></td>
													<td>
														<?php
														$description = $exp['description'] ?? '';
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
													<td>
														<?php if (!empty($exp['attachment'])): ?>
															<a href="<?= base_url('uploads/attachment/' . $exp['attachment']) ?>"
																class="btn btn-xs btn-danger" target="_blank">
																<i class="fa fa-arrow-down"></i> PDF
															</a>
														<?php else: ?>
															-
														<?php endif; ?>
													</td>
												</tr>
											<?php endforeach; ?>
										<?php else: ?>
											<tr>
												<td colspan="7"><em>Tidak ada pengalaman</em></td>
											</tr>
										<?php endif; ?>
									</tbody>
								</table>


								<!-- PENDIDIKAN -->
								<h3 class="text-center"><strong>Pendidikan</strong></h3>
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
										<?php $hasEducation = false; ?>
										<?php if (!empty($detail_aer['educations'])): ?>
											<?php foreach ($detail_aer['educations'] as $edu): ?>
												<?php if (isset($edu['status']) && $edu['status'] == 1): ?>
													<?php $hasEducation = true; ?>
													<tr class="text-dark">
														<td>
															<?php $typeMap = ['1' => 'Akademis', '2' => 'Profesi']; ?>
															<?= $typeMap[$edu['type']] ?? '-' ?>
														</td>
														<td><?= $edu['school'] ?? '' ?></td>
														<td><?= ($edu['startdate'] ?? '') . ' ' . ($edu['enddate'] ?? '') ?></td>
														<td><?= $edu['degree'] ?? '' ?></td>
														<td><?= $edu['mayor'] ?? '' ?></td>
														<td><?= $edu['fieldofstudy'] ?? '' ?></td>
														<td><?= $edu['score'] ?? '' ?></td>
														<td><?= $edu['title'] ?? '' ?></td>
														<td><?= $edu['activities'] ?? '' ?></td>
														<td>
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
														<td>
															<?php if (!empty($edu['attachment'])): ?>
																<a href="<?= base_url('uploads/attachment/' . $edu['attachment']) ?>"
																	class="btn btn-xs btn-danger" target="_blank">
																	<i class="fa fa-arrow-down"></i> PDF
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
												<td colspan="11"><em>Tidak ada pendidikan dengan status aktif</em></td>
											</tr>
										<?php endif; ?>
									</tbody>
								</table>


								<!-- CERTIFICATIONS -->

								<h3 class="text-center"><strong>Sertifikasi Profesional</strong></h3>
								<table class="table table-bordered text text-dark">
									<thead>
										<tr>
											<th>Nama Sertifikasi</th>
											<th>Otoritas Sertifikasi</th>
											<th>Nomor lisensi</th>
											<th>URL sertifikasi</th>
											<th>Kualifikasi</th>
											<th>Tanggal</th>
											<th>Deskripsi</th>
											<th>Dokumen pendukung</th>
										</tr>
									</thead>
									<tbody>
										<?php $hasCert = false; ?>
										<?php if (!empty($detail_aer['certifications'])): ?>
											<?php foreach ($detail_aer['certifications'] as $cert): ?>
												<?php if (isset($cert['status']) && $cert['status'] == 1): ?>
													<?php $hasCert = true; ?>
													<tr class="text-dark">
														<td><?= $cert['cert_name'] ?? '-' ?></td>
														<td><?= $cert['cert_auth'] ?? '' ?></td>
														<td><?= $cert['lic_num'] ?? '' ?></td>
														<td><?= $cert['cert_url'] ?? '' ?></td>
														<td><?= $cert['cert_title'] ?? '' ?></td>
														<td>
															<?php
															$startYear  = $cert['startyear'] ?? '';
															$startMonth = !empty($cert['startmonth']) ? date('M', mktime(0, 0, 0, (int)$cert['startmonth'], 1)) : '';
															echo trim($startMonth . ' ' . $startYear);
															echo ' - ';
															if (!empty($cert['is_present']) && $cert['is_present'] == '1') {
																echo 'Present';
															} else {
																$endYear  = $cert['endyear'] ?? '';
																$endMonth = !empty($cert['endmonth']) ? date('M', mktime(0, 0, 0, (int)$cert['endmonth'], 1)) : '';
																echo trim($endMonth . ' ' . $endYear);
															}
															?>
														</td>
														<td>
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
														<td>
															<?php if (!empty($cert['attachment'])): ?>
																<a href="<?= base_url('uploads/attachment/' . $cert['attachment']) ?>"
																	class="btn btn-xs btn-danger" target="_blank">
																	<i class="fa fa-arrow-down"></i> PDF
																</a>
															<?php else: ?>
																-
															<?php endif; ?>
														</td>
													</tr>
												<?php endif; ?>
											<?php endforeach; ?>
										<?php endif; ?>

										<?php if (!$hasCert): ?>
											<tr>
												<td colspan="8"><em>Tidak ada sertifikasi dengan status aktif</em></td>
											</tr>
										<?php endif; ?>
									</tbody>
								</table>


							</div><!-- panel-body -->
						</div><!-- panel -->
					</div><!-- container -->
				</div>
			<?php else: ?>
				<div class="alert alert-warning">Data tidak ditemukan.</div>
			<?php endif; ?>
		</aside>
	</div>

	<?php $this->load->view('admin/common/footer'); ?>
</body>

</html>