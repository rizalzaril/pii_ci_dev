<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <title><?php echo $title; ?></title>
  <?php $this->load->view('member/common/meta_tags'); ?>
  <?php $this->load->view('member/common/before_head_close'); ?>
  <style type="text/css">
    .awesome_style {
      font-size: 100px;
    }

    .table>thead>tr>th,
    .table>tbody>tr>th,
    .table>tfoot>tr>th,
    .table>thead>tr>td,
    .table>tbody>tr>td,
    .table>tfoot>tr>td {
      border: none;
    }

    .table>thead>tr>th,
    .table>tbody>tr>th,
    .table>tfoot>tr>th,
    .table>thead>tr>td,
    .table>tbody>tr>td,
    .table>tfoot>tr>td {
      padding: 3px;
    }

    .form-wizard {
      cursor: pointer;
    }

    .iswna {
      display: <?php echo ($row->warga_asing == '0') ? "none" : "block"; ?>;
    }
  </style>
  <link rel="shortcut icon" href="<?php echo base_url(); ?>/assets/images/favicon_16.png">






  <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
  <style>
    .ui-autocomplete-loading {
      background: white url("<?php echo base_url(); ?>assets/images/ui-anim_basic_16x16.gif") right center no-repeat;
    }
  </style>
  <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>









</head>

<body class="skin-blue">
  <?php $this->load->view('member/common/after_body_open'); ?>
  <?php $this->load->view('member/common/header'); ?>

  <?php //$this->load->view('member/common/datepicker'); 
  ?>

  <div class="wrapper row-offcanvas row-offcanvas-left">
    <?php $this->load->view('member/common/left_side'); ?>
    <!-- Right side column. Contains the navbar and content of the page -->
    <aside class="right-side">
      <!-- Content Header (Page header) -->
      <section class="content-header">
        <h1> Pemutakhiran Data Anggota</h1>
      </section>

      <!--<link href="<?php //echo base_url();
                      ?>assets/ada/wizard/css/bootstrap.min.css" rel="stylesheet"/>-->
      <link href="<?php echo base_url(); ?>assets/ada/wizard/css/font-awesome.min.css" rel="stylesheet" />
      <link href="<?php echo base_url(); ?>assets/ada/wizard/style.css" rel="stylesheet" />

      <link rel="stylesheet" href="<?php echo base_url(); ?>assets/ada/phone/css/intlTelInput.css">

      <div class="container">
        <form method="post">
          <div class="wizards">
            <div class="progressbar">
              <div class="progress-line" data-now-value="12.11" data-number-of-steps="5" style="width: 12.11%;"></div> <!-- 19.66% -->
            </div>
            <div class="form-wizard active" id="wi_1">
              <div class="wizard-icon"><i class="fa fa-file-text-o"></i></div>
              <p>1. Data Pribadi</p>
            </div>
            <div class="form-wizard" id="wi_2">
              <div class="wizard-icon"><i class="fa fa-graduation-cap"></i></div>
              <p>2. Pendidikan & Sertifikasi Profesional</p>
            </div>
            <div class="form-wizard" id="wi_3">
              <div class="wizard-icon"><i class="fa fa-briefcase"></i></div>
              <p>3. Pengalaman Kerja/Profesional</p>
            </div>
            <div class="form-wizard" id="wi_4">
              <div class="wizard-icon"><i class="fa fa-upload"></i></div>
              <p>4. Unggah KTP/Paspor & Foto Profil</p>
            </div>
            <div class="form-wizard" id="wi_5">
              <div class="wizard-icon"><i class="fa fa-flag-checkered"></i></div>
              <p>5. Selesai</p>
            </div>
          </div>
          <fieldset id="fi_1">
            <h4 style="padding:30px;"><b>Data Pribadi</b></h4>
            <div class="col-md-12" style="background-color: #fff;">
              <div class="col-md-4 form-group">
                <label>Name Depan<span class="red"> *</span></label>
                <input type="text" name="fn" id="fn" class="form-control" value="<?php echo set_value('fn', $row->firstname); ?>" placeholder="Name Depan" required="required" />
              </div>
              <div class="col-md-8 form-group">
                <label>Nama Belakang</label>
                <input type="text" name="ln" id="ln" class="form-control" value="<?php echo set_value('ln', $row->lastname); ?>" placeholder="Nama Belakang" />
              </div>
              <div class="col-md-4 form-group">
                <label class="form-radio-label" for="gender">Jenis Kelamin <span class="red"> *</span></label><br />
                <input type="radio" name="gender" value="Male" <?php echo set_value('gender', $row->gender) == "Male" ? "checked" : ""; ?>>Pria
                <input type="radio" name="gender" value="Female" <?php echo set_value('gender', $row->gender) == "Female" ? "checked" : ""; ?>>Wanita

              </div>
              <div class="col-md-8 form-group">
                <label>Nomor Ponsel <span class="red"> *</span></label><br />
                <input type="tel" name="phone" id="phone" value="<?php echo set_value('phone', $row->mobilephone); ?>" class="form-control w400" placeholder="Contoh +627911123456" required="required" />

                <script src="<?php echo base_url(); ?>assets/ada/phone/js/intlTelInput.js"></script>
                <script>
                  var input = document.querySelector("#phone");
                  window.intlTelInput(input, {
                    // allowDropdown: false,
                    autoHideDialCode: true,
                    // autoPlaceholder: "off",
                    // dropdownContainer: document.body,
                    // excludeCountries: ["us"],
                    formatOnDisplay: false,
                    // geoIpLookup: function(callback) {
                    //   $.get("http://ipinfo.io", function() {}, "jsonp").always(function(resp) {
                    //     var countryCode = (resp && resp.country) ? resp.country : "";
                    //     callback(countryCode);
                    //   });
                    // },
                    // hiddenInput: "full_number",
                    // initialCountry: "auto",
                    // localizedCountries: { 'de': 'Deutschland' },
                    nationalMode: false,
                    // onlyCountries: ['us', 'gb', 'ch', 'ca', 'do'],
                    // placeholderNumberType: "MOBILE",
                    preferredCountries: ['id'],
                    // separateDialCode: true,
                    utilsScript: "<?php echo base_url(); ?>assets/ada/phone/js/utils.js",
                  });


                  const tel = document.getElementById('phone');

                  tel.addEventListener('input', function() {
                    let start = this.selectionStart;
                    let end = this.selectionEnd;

                    const current = this.value
                    const corrected = current.replace(/[^-+\d]/g, '');
                    this.value = corrected;

                    if (corrected.length < current.length) --end;
                    this.setSelectionRange(start, end);
                  });
                </script>
              </div>
              <div class="col-md-4 form-group">
                <label>Tempat Lahir <span class="red"> *</span></label><br />
                <input type="text" name="birthplace" id="birthplace" value="<?php echo set_value('birthplace', $row->birthplace); ?>" class="form-control w341" placeholder="Tempat Lahir" required="required" />
              </div>
              <div class="col-md-8 form-group">
                <label>Tanggal Lahir <span class="red"> *</span></label>

                <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
                <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
                <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
                <script>
                  $(function() {
                    $("#dob").datepicker({
                      dateFormat: 'dd-mm-yy',
                      changeMonth: true,
                      changeYear: true,
                      yearRange: "1930:<?php echo date('Y'); ?>"
                    });
                  });
                </script>

                <script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery-1.4.2.min.js"></script>
                <script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery.maskedinput-1.2.2-co.min.js"></script>

                <input type="text" name="dob" id="dob" value="<?php echo set_value('dob', (isset($row->dob) ? date('d-m-Y', strtotime($row->dob)) : $row->dob)); ?>" class="form-control datepicker" placeholder="Tanggal Lahir" required="required" />

                <script type="text/javascript">
                  $(function($) {
                    $("#dob").mask("99-99-9999"), {
                      placeholder: 'dd-mm-yyyy'
                    };
                  });
                </script>

              </div>

              <div class="col-md-12 form-group">
                <label class="form-radio-label" for="typeid">Warga Negara <span class="red"> *</span></label><br />
                <input type="radio" name="warga_asing" onchange="cek_warga()" value="0" <?php echo set_value('warga_asing', $row->warga_asing) == "0" ? "checked" : ""; ?>>Indonesia
                <input type="radio" name="warga_asing" onchange="cek_warga()" value="1" <?php echo set_value('warga_asing', $row->warga_asing) == "1" ? "checked" : ""; ?>>Asing
              </div>

              <div class="col-md-4 form-group">
                <label class="form-radio-label" for="typeid">Tipe ID <span class="red"> *</span></label><br />
                <input type="radio" name="typeid" id="valid_warga" value="Citizen" <?php echo set_value('typeid', $row->idtype) == "Citizen" ? "checked" : ""; ?>>KTP
                <input type="radio" name="typeid" value="Passport" <?php echo set_value('typeid', $row->idtype) == "Passport" ? "checked" : ""; ?>>Passport
              </div>
              <div class="col-md-8 form-group">
                <label>Nomor ID <span class="red"> *</span></label>
                <input type="text" name="idnumber" id="idnumber" value="<?php echo set_value('idnumber', $row->idcard); ?>" class="form-control" placeholder="ID Number" required="required" />
              </div>

              <div class="col-md-12 form-group iswna" style="border-color:red;border-style: solid;border-width: thin;">
                <br />
                <div class="col-md-6 form-group iswna">
                  <p><b>Upload Sertifikasi Insinyur dari negara asal yang sudah dilegalisir di Indonesia sesuai dengan ketentuan peraturan perundang-undangan, bagi yang belum bersertifikat kompetensi insinyur. <span class="red"> *</span></b></p> <span class="red">(Max. 700KB, image atau PDF)</span>
                  <div class="form-group">
                    <div id="avatar_sertifikat_legal">

                      <?php echo ($row->sertifikat_legal != '') ? "<a href='" . base_url() . "/assets/uploads/" . $row->sertifikat_legal . "' target='_blank' class='ava_discus'>" . $row->sertifikat_legal . "</a>" : ''; ?>

                    </div>
                    <br /><br />
                    <div id="errUpload_sertifikat_legal" class="red"></div>
                    <input type="file" name="sertifikat_legal" id="sertifikat_legal" onchange="upload_pernyataan('sertifikat_legal')">
                  </div>
                </div>

                <div class="col-md-6 form-group iswna">
                  <p><b>Upload Tanda bukti terdaftar pada register Perjanjian Pengakuan Timbal-balik (MRA = Mutual Recognation Agreement) sesuai dengan ketentuan peraturan perundang-undangan, bagi yang telah bersertifikat kompetensi insinyur. <span class="red"> *</span></b></p> <span class="red">(Max. 700KB, image atau PDF)</span>
                  <div class="form-group">
                    <div id="avatar_tanda_bukti">

                      <?php echo ($row->tanda_bukti != '') ? "<a href='" . base_url() . "/assets/uploads/" . $row->tanda_bukti . "' target='_blank' class='ava_discus'>" . $row->tanda_bukti . "</a>" : ''; ?>

                    </div>
                    <br /><br />
                    <div id="errUpload_tanda_bukti" class="red"></div>
                    <input type="file" name="tanda_bukti" id="tanda_bukti" onchange="upload_pernyataan('tanda_bukti')">
                  </div>
                </div>

                <div class="col-md-6 form-group iswna">
                  <p><b>Upload Surat dukungan (referensi) dari sekurang-kurangnya 2 (dua) Anggota Biasa. <span class="red"> *</span></b></p> <span class="red">(Max. 700KB, image atau PDF)</span>
                  <div class="form-group">
                    <div id="avatar_surat_dukungan">

                      <?php echo ($row->surat_dukungan != '') ? "<a href='" . base_url() . "/assets/uploads/" . $row->surat_dukungan . "' target='_blank' class='ava_discus'>" . $row->surat_dukungan . "</a>" : ''; ?>

                    </div>
                    <br /><br />
                    <div id="errUpload_surat_dukungan" class="red"></div>
                    <input type="file" name="surat_dukungan" id="surat_dukungan" onchange="upload_pernyataan('surat_dukungan')">
                  </div>
                </div>

                <!--<div class="col-md-6 form-group ">
						<p><b>Upload Surat pernyataan sanggup mematuhi ketentuan dalam Anggaran Dasar dan Anggaran Rumah Tangga, dan Kode Etik Insinyur serta ketentuan peraturan perundang-undangan. <span class="red"> *</span></b> <a href='<?php //echo base_url()
                                                                                                                                                                                                                                    ?>assets/Patuh KEI.docx' target="_blank" style="color:blue;">Download format pernyataan mematuhi Kode Etik</a></p>  <span class="red">(Max. 700KB, image atau PDF)</span>
						
						
						
						<div class="form-group">
							<div id="avatar_surat_pernyataan">
							
							<?php //echo ($row->surat_pernyataan!='')?"<a href='".base_url()."/assets/uploads/".$row->surat_pernyataan."' target='_blank' class='ava_discus'>".$row->surat_pernyataan."</a>":''; 
              ?>
							
							</div>
							<br /><br />
							<div id="errUpload_surat_pernyataan" class="red"></div>
							<input type="file" name="surat_pernyataan" id="surat_pernyataan" onchange="upload_pernyataan('surat_pernyataan')">
						</div>
					</div>-->

                <div class="col-md-6 form-group iswna">
                  <p><b>Upload Surat izin domisili di Indonesia. <span class="red"> *</span></b></p> <span class="red">(Max. 700KB, image atau PDF)</span>
                  <div class="form-group">
                    <div id="avatar_surat_ijin_domisili">

                      <?php echo ($row->surat_ijin_domisili != '') ? "<a href='" . base_url() . "/assets/uploads/" . $row->surat_ijin_domisili . "' target='_blank' class='ava_discus'>" . $row->surat_ijin_domisili . "</a>" : ''; ?>

                    </div>
                    <br /><br />
                    <div id="errUpload_surat_ijin_domisili" class="red"></div>
                    <input type="file" name="surat_ijin_domisili" id="surat_ijin_domisili" onchange="upload_pernyataan('surat_ijin_domisili')">
                  </div>
                </div>
              </div>


              <div class="col-md-12 form-group">
                <label>Website</label><br />
                <input id="website" name="website" type="url" placeholder="http://" class="form-control input-md w340" value="<?php echo set_value('website', (isset($row->website) ? $row->website : '')); ?>">
              </div>
              <div class="col-md-12 form-group">
                <input type="checkbox" name="is_public" id="is_public" class="form-check-label" value="1" <?php echo set_checkbox('is_public', '1', (isset($row->is_public) ? ($row->is_public == "1") ? TRUE : FALSE : FALSE)); ?>>
                <label class="form-check-label" for="is_public">Bersedia menerima bahan-bahan publikasi/promosi teknik</label>
                <br /><input type="checkbox" name="is_datasend" id="is_datasend" class="form-check-label" value="1" <?php echo set_checkbox('is_datasend', '1', (isset($row->is_datasend) ? ($row->is_datasend == "1") ? TRUE : FALSE : FALSE)); ?>>
                <label class="form-check-label" for="is_datasend">Kesediaan apabila data pribadi & keahlian/profesional di serahkan kepada para pihak yang terkait dengan keinsinyuran</label>
              </div>
              <div class="col-md-12 form-group">
                <label>Deskripsi</label><br />
                <textarea class="form-control" rows="5" id="desc" name="desc"><?php echo set_value('desc', (isset($row->description) ? $row->description : '')); ?></textarea>
              </div>

            </div>
            <h4 style="padding:30px;"><b>Kontak Address</b></h4>

            <div class="col-md-12">
              <table id="taddress" class="table" style="margin-left:5px;width:85%;" border="0">
                <tr class="row address-item noBorder" data-id="1">
                  <td>
                    <table class="table" style="margin-left:5px;">
                      <tr class="row">
                        <td class="col-md-4">
                          <select id="typeaddress" name="typeaddress[]" class="form-control input-md" required="">
                            <option value="">--Choose--</option>
                            <?php
                            if (isset($m_address)) {
                              foreach ($m_address as $val) {
                                if (isset($typeaddress[0])) {
                            ?>
                                  <option value="<?php echo $val->id; ?>" <?php echo (isset($typeaddress[0]) ? (($typeaddress[0] == $val->id) ? 'selected="true"' : "") : ""); ?>><?php echo $val->desc; ?></option>

                                <?php
                                } else {
                                ?>
                                  <option value="<?php echo $val->id; ?>" <?php echo (isset($user_address[0]->addresstype) ? (($user_address[0]->addresstype == $val->id) ? 'selected="true"' : "") : ""); ?>><?php echo $val->desc; ?></option>

                            <?php
                                }
                              }
                            }
                            ?>
                          </select>
                          <input type="hidden" name="addressid[]" id="addressid" value="<?php echo set_value('addressid[0]', (isset($user_address[0]->id) ? $user_address[0]->id : '0')); ?>" />
                        </td>
                        <td class="col-md-8">
                          <input id="address" name="address[]" type="text" placeholder="Address" class="form-control input-md" required="" value="<?php echo set_value('address[0]', (isset($user_address[0]->address) ? $user_address[0]->address : '')); ?>">
                        </td>
                        <td>
                          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        </td>
                      </tr>
                      <tr class="row">
                        <td class="col-md-4">
                          <label class="form-check-label" for="mailingaddr">
                            <input type="radio" name="mailingaddr" value="1" <?php echo set_radio('mailingaddr', '1', (isset($user_address[0]->is_mailing) ? ($user_address[0]->is_mailing == "1") ? TRUE : TRUE : TRUE)); ?>> <!--checked="checked"-->
                            Alamat Surat</label>
                        </td>
                        <td class="col-md-8">
                          <input id="addresscity" name="addresscity[]" type="text" placeholder="City" class="form-control input-md w400" value="<?php echo set_value('addresscity[0]', (isset($user_address[0]->city) ? $user_address[0]->city : '')); ?>" required="">
                        </td>
                      </tr>
                      <tr class="row">
                        <td class="col-md-4">
                        </td>
                        <td class="col-md-8">
                          <input id="addressprovince" name="addressprovince[]" type="text" placeholder="Province" class="form-control input-md w400" value="<?php echo set_value('addressprovince[0]', (isset($user_address[0]->province) ? $user_address[0]->province : '')); ?>" required="">
                        </td>
                      </tr>
                      <tr class="row">
                        <td class="col-md-4">
                        </td>
                        <td class="col-md-8">
                          <input id="addresszip" name="addresszip[]" type="text" placeholder="Zip Code" class="form-control input-md number w400" value="<?php echo set_value('addresszip[0]', (isset($user_address[0]->zipcode) ? $user_address[0]->zipcode : '')); ?>" required="">
                        </td>
                      </tr>
                      <tr class="row">
                        <td class="col-md-4">
                        </td>
                        <td class="col-md-8">
                          <input id="email" name="email[]" type="email" placeholder="Email" class="form-control input-md" value="<?php echo set_value('email[0]', (isset($user_address[0]->email) ? $user_address[0]->email : '')); ?>">
                        </td>
                      </tr>
                      <tr class="row">
                        <td class="col-md-4">
                        </td>
                        <td class="col-md-8">
                          <input id="addressphone" name="addressphone[]" type="text" placeholder="Contoh +627911123456" class="form-control input-md number w400" value="<?php echo set_value('addressphone[0]', (isset($user_address[0]->phone) ? $user_address[0]->phone : '')); ?>">

                          <script>
                            var input = document.querySelector("#addressphone");
                            window.intlTelInput(input, {
                              // allowDropdown: false,
                              autoHideDialCode: true,
                              // autoPlaceholder: "off",
                              // dropdownContainer: document.body,
                              // excludeCountries: ["us"],
                              formatOnDisplay: false,
                              // geoIpLookup: function(callback) {
                              //   $.get("http://ipinfo.io", function() {}, "jsonp").always(function(resp) {
                              //     var countryCode = (resp && resp.country) ? resp.country : "";
                              //     callback(countryCode);
                              //   });
                              // },
                              // hiddenInput: "full_number",
                              // initialCountry: "auto",
                              // localizedCountries: { 'de': 'Deutschland' },
                              nationalMode: false,
                              // onlyCountries: ['us', 'gb', 'ch', 'ca', 'do'],
                              // placeholderNumberType: "MOBILE",
                              preferredCountries: ['id'],
                              // separateDialCode: true,
                              utilsScript: "<?php echo base_url(); ?>assets/ada/phone/js/utils.js",
                            });

                            document.getElementById('addressphone').addEventListener('input', function() {
                              let start = this.selectionStart;
                              let end = this.selectionEnd;

                              const current = this.value
                              const corrected = current.replace(/[^-+\d]/g, '');
                              this.value = corrected;

                              if (corrected.length < current.length) --end;
                              this.setSelectionRange(start, end);
                            });
                          </script>

                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>

                <?php
                $addressmail = '';
                // print_r($user_address);
                if (!isset($typeaddress[1])) {
                  $i = 0;
                  foreach ($user_address as $val) {
                    $typeaddress[$i] = $val->addresstype;
                    $address[$i] = $val->address;
                    $addressphone[$i] = $val->phone;
                    $addresszip[$i] = $val->zipcode;
                    $addresscity[$i] = $val->city;
                    $addressprovince[$i] = $val->province;
                    $email[$i] = $val->email;
                    $addressmail[$i] = $val->is_mailing;
                    $addressid[$i] = $val->id;
                    $i++;
                  }
                }

                if (isset($typeaddress[1])) {
                  $i = 0;
                  foreach ($typeaddress as $val) {
                    if ($i > 0) {
                      $typeaddressx = isset($typeaddress[$i]) ? $typeaddress[$i] : "";
                      $addressx = isset($address[$i]) ? $address[$i] : "";
                      $addressphonex = isset($addressphone[$i]) ? $addressphone[$i] : "";
                      $addresszipx = isset($addresszip[$i]) ? $addresszip[$i] : "";
                      $addresscityx = isset($addresscity[$i]) ? $addresscity[$i] : "";
                      $addressprovincex = isset($addressprovince[$i]) ? $addressprovince[$i] : "";
                      $emailx = isset($email[$i]) ? $email[$i] : "";
                      $addressmailx = isset($addressmail[$i]) ? $addressmail[$i] : "";
                      $addressidx = isset($addressid[$i]) ? $addressid[$i] : "";
                ?>
                      <tr class="row address-item noBorder" data-id="<?php echo $i + 1; ?>">
                        <td>
                          <table class="table" style="margin-left:5px;">
                            <tr class="row">
                              <td class="col-md-4">
                                <select id="typeaddress<?php echo $i + 1; ?>" name="typeaddress[]" class="form-control input-md" required="">
                                  <option value="">--Choose--</option>
                                  <?php
                                  if (isset($m_address)) {
                                    foreach ($m_address as $val) {
                                  ?>
                                      <option value="<?php echo $val->id; ?>" <?php echo ($val->id == $typeaddressx) ? 'selected="true"' : ''; ?>><?php echo $val->desc; ?></option>

                                  <?php
                                    }
                                  }
                                  ?>
                                </select>
                                <input type="hidden" name="addressid[]" id="addressid<?php echo $i + 1; ?>" value="<?php echo $addressidx; ?>" />
                              </td>
                              <td class="col-md-8">
                                <input id="address<?php echo $i + 1; ?>" name="address[]" type="text" placeholder="Address" class="form-control input-md" required="" value="<?php echo $addressx; ?>">
                              </td>
                              <td class="td-action"><button type="button" class="btn btn-danger btn-xs address-item-remove-button" data-id="<?php echo $i + 1; ?>"><i class="fa fa-trash-o fa-fw"></i>X</button></td>
                            </tr>
                            <tr class="row">
                              <td class="col-md-4">
                                <label class="form-check-label">
                                  <input type="radio" name="mailingaddr" value="<?php echo $i + 1; ?>" <?php echo set_radio('mailingaddr', ($i + 1), ($addressmailx != '' ? ($addressmailx == "1") ? TRUE : FALSE : FALSE)); ?>> <!--checked="checked"-->
                                  Alamat Surat</label>
                              </td>
                              <td class="col-md-8">
                                <input id="addresscity<?php echo $i + 1; ?>" name="addresscity[]" type="text" placeholder="City" class="form-control input-md w400" value="<?php echo $addresscityx; ?>" required="">
                              </td>
                            </tr>
                            <tr class="row">
                              <td class="col-md-4">
                              </td>
                              <td class="col-md-8">
                                <input id="addressprovince<?php echo $i + 1; ?>" name="addressprovince[]" type="text" placeholder="Province" class="form-control input-md w400" value="<?php echo $addressprovincex; ?>" required="">
                              </td>
                            </tr>
                            <tr class="row">
                              <td class="col-md-4">
                              </td>
                              <td class="col-md-8">
                                <input id="addresszip<?php echo $i + 1; ?>" name="addresszip[]" type="text" placeholder="Zip Code" class="form-control input-md number w400" value="<?php echo $addresszipx; ?>" required="">
                              </td>
                            </tr>
                            <tr class="row">
                              <td class="col-md-4">
                              </td>
                              <td class="col-md-8">
                                <input id="email<?php echo $i + 1; ?>" name="email[]" type="email" placeholder="Email" class="form-control input-md number" value="<?php echo $emailx; ?>">
                              </td>
                            </tr>
                            <tr class="row">
                              <td class="col-md-4">
                              </td>
                              <td class="col-md-8">
                                <input id="addressphone<?php echo $i + 1; ?>" name="addressphone[]" type="text" placeholder="Contoh +627911123456" class="form-control input-md number w400" value="<?php echo $addressphonex; ?>">

                                <script>
                                  var input = document.querySelector("#addressphone<?php echo $i + 1; ?>");
                                  window.intlTelInput(input, {
                                    // allowDropdown: false,
                                    autoHideDialCode: true,
                                    // autoPlaceholder: "off",
                                    // dropdownContainer: document.body,
                                    // excludeCountries: ["us"],
                                    formatOnDisplay: false,
                                    // geoIpLookup: function(callback) {
                                    //   $.get("http://ipinfo.io", function() {}, "jsonp").always(function(resp) {
                                    //     var countryCode = (resp && resp.country) ? resp.country : "";
                                    //     callback(countryCode);
                                    //   });
                                    // },
                                    // hiddenInput: "full_number",
                                    // initialCountry: "auto",
                                    // localizedCountries: { 'de': 'Deutschland' },
                                    nationalMode: false,
                                    // onlyCountries: ['us', 'gb', 'ch', 'ca', 'do'],
                                    // placeholderNumberType: "MOBILE",
                                    preferredCountries: ['id'],
                                    // separateDialCode: true,
                                    utilsScript: "<?php echo base_url(); ?>assets/ada/phone/js/utils.js",
                                  });

                                  document.getElementById('addressphone<?php echo $i + 1; ?>').addEventListener('input', function() {
                                    let start = this.selectionStart;
                                    let end = this.selectionEnd;

                                    const current = this.value
                                    const corrected = current.replace(/[^-+\d]/g, '');
                                    this.value = corrected;

                                    if (corrected.length < current.length) --end;
                                    this.setSelectionRange(start, end);
                                  });
                                </script>

                              </td>
                            </tr>
                          </table>
                        </td>
                      </tr>
                <?php
                    }
                    $i++;
                  }
                }
                ?>

              </table>
              <div class="col-md-12" style="padding-bottom:20px;">
                <button type="button" class="btn btn-primary" onclick="addAddress('taddress')">Tambah</button>
              </div>
              <label class="col-md-12 control-label"></label>
            </div>

            <h4 style="padding:30px;"><b>Email Tambahan</b></h4>

            <div class="col-md-12">
              <table id="temail" class="table" style="margin-left:5px;5px;width:85%;" border="0">
                <tr class="row email-item noBorder" data-id="1">
                  <td>
                    <table class="table" style="margin-left:5px;">
                      <tr class="row">
                        <td class="col-md-4">
                          <select id="typeemail" name="typeemail[]" class="form-control input-md">
                            <option value="">--Choose--</option>
                            <?php
                            if (isset($m_email)) {
                              foreach ($m_email as $val) {
                                if (isset($typeemail[0])) {
                            ?>
                                  <option value="<?php echo $val->type; ?>" <?php echo (isset($typeemail[0]) ? (($typeemail[0] == $val->type) ? 'selected="true"' : "") : ""); ?>><?php echo $val->desc; ?></option>

                                <?php
                                } else {
                                ?>
                                  <option value="<?php echo $val->type; ?>" <?php echo (isset($user_email[0]->contact_type) ? (($user_email[0]->contact_type == $val->type) ? 'selected="true"' : "") : ""); ?>><?php echo $val->desc; ?></option>

                            <?php
                                }
                              }
                            }
                            ?>
                          </select>
                          <input type="hidden" name="emailid[]" id="emailid" value="<?php echo set_value('emailid[0]', (isset($user_email[0]->id) ? $user_email[0]->id : '0')); ?>" />
                        </td>
                        <td class="col-md-8">
                          <input id="emailm" name="emailm[]" type="text" placeholder="Email" class="form-control input-md" value="<?php echo set_value('emailm[0]', (isset($user_email[0]->contact_value) ? $user_email[0]->contact_value : '')); ?>">
                        </td>
                        <td>
                          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>

                <?php
                $addressmail = '';
                //print_r($user_email);
                if (!isset($typeemail[1])) {
                  $i = 0;
                  foreach ($user_email as $val) {
                    $typeemail[$i] = $val->contact_type;
                    $email[$i] = $val->contact_value;
                    $emailid[$i] = $val->id;
                    $i++;
                  }
                }

                if (isset($typeemail[1])) {
                  $i = 0;
                  foreach ($typeemail as $val) {
                    if ($i > 0) {
                      $typeemailx = isset($typeemail[$i]) ? $typeemail[$i] : "";
                      $emailx = isset($email[$i]) ? $email[$i] : "";
                      $emailidx = isset($emailid[$i]) ? $emailid[$i] : "";
                ?>
                      <tr class="row email-item noBorder" data-id="<?php echo $i + 1; ?>">
                        <td>
                          <table class="table" style="margin-left:5px;">
                            <tr class="row">
                              <td class="col-md-4">
                                <select id="typeemail<?php echo $i + 1; ?>" name="typeemail[]" class="form-control input-md">
                                  <option value="">--Choose--</option>
                                  <?php
                                  if (isset($m_email)) {
                                    foreach ($m_email as $val) {
                                  ?>
                                      <option value="<?php echo $val->type; ?>" <?php echo ($val->type == $typeemailx) ? 'selected="true"' : ''; ?>><?php echo $val->desc; ?></option>

                                  <?php
                                    }
                                  }
                                  ?>
                                </select>
                                <input type="hidden" name="emailid[]" id="emailid<?php echo $i + 1; ?>" value="<?php echo $emailidx; ?>" />
                              </td>
                              <td class="col-md-8">
                                <input id="emailm<?php echo $i + 1; ?>" name="emailm[]" type="text" placeholder="Email" class="form-control input-md" value="<?php echo $emailx; ?>">
                              </td>
                              <td class="td-action"><button type="button" class="btn btn-danger btn-xs email-item-remove-button" data-id="<?php echo $i + 1; ?>"><i class="fa fa-trash-o fa-fw"></i>X</button></td>
                            </tr>
                          </table>
                        </td>
                      </tr>
                <?php
                    }
                    $i++;
                  }
                }
                ?>

              </table>
              <div class="col-md-12" style="padding-bottom:20px;">
                <button type="button" class="btn btn-primary" onclick="addEmail('temail')">Tambah</button>
              </div>
            </div>

            <h4 style="padding:30px;"><b>Telepon Tambahan</b></h4>

            <div class="col-md-12">
              <table id="tphone" class="table" style="margin-left:5px;5px;width:85%;" border="0">
                <tr class="row phone-item noBorder" data-id="1">
                  <td>
                    <table class="table" style="margin-left:5px;">
                      <tr class="row">
                        <td class="col-md-4">
                          <select id="typephone" name="typephone[]" class="form-control input-md">
                            <option value="">--Choose--</option>
                            <?php
                            if (isset($m_phone)) {
                              foreach ($m_phone as $val) {
                                if (isset($typephone[0])) {
                            ?>
                                  <option value="<?php echo $val->type; ?>" <?php echo (isset($typephone[0]) ? (($typephone[0] == $val->type) ? 'selected="true"' : "") : ""); ?>><?php echo $val->desc; ?></option>

                                <?php
                                } else {
                                ?>
                                  <option value="<?php echo $val->type; ?>" <?php echo (isset($user_phone[0]->contact_type) ? (($user_phone[0]->contact_type == $val->type) ? 'selected="true"' : "") : ""); ?>><?php echo $val->desc; ?></option>

                            <?php
                                }
                              }
                            }
                            ?>
                          </select>
                          <input type="hidden" name="phoneid[]" id="phoneid" value="<?php echo set_value('phoneid[0]', (isset($user_phone[0]->id) ? $user_phone[0]->id : '0')); ?>" />
                        </td>
                        <td class="col-md-8">
                          <input id="phonem" name="phonem[]" type="text" placeholder="Contoh +627911123456" class="form-control input-md w400" value="<?php echo set_value('phonem[0]', (isset($user_phone[0]->contact_value) ? $user_phone[0]->contact_value : '')); ?>">

                          <script>
                            var input = document.querySelector("#phonem");
                            window.intlTelInput(input, {
                              // allowDropdown: false,
                              autoHideDialCode: true,
                              // autoPlaceholder: "off",
                              // dropdownContainer: document.body,
                              // excludeCountries: ["us"],
                              formatOnDisplay: false,
                              // geoIpLookup: function(callback) {
                              //   $.get("http://ipinfo.io", function() {}, "jsonp").always(function(resp) {
                              //     var countryCode = (resp && resp.country) ? resp.country : "";
                              //     callback(countryCode);
                              //   });
                              // },
                              // hiddenInput: "full_number",
                              // initialCountry: "auto",
                              // localizedCountries: { 'de': 'Deutschland' },
                              nationalMode: false,
                              // onlyCountries: ['us', 'gb', 'ch', 'ca', 'do'],
                              // placeholderNumberType: "MOBILE",
                              preferredCountries: ['id'],
                              // separateDialCode: true,
                              utilsScript: "<?php echo base_url(); ?>assets/ada/phone/js/utils.js",
                            });

                            document.getElementById('phonem').addEventListener('input', function() {
                              let start = this.selectionStart;
                              let end = this.selectionEnd;

                              const current = this.value
                              const corrected = current.replace(/[^-+\d]/g, '');
                              this.value = corrected;

                              if (corrected.length < current.length) --end;
                              this.setSelectionRange(start, end);
                            });
                          </script>

                        </td>
                        <td>
                          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>

                <?php
                $addressmail = '';
                // print_r($user_address);
                if (!isset($typephone[1])) {
                  $i = 0;
                  foreach ($user_phone as $val) {
                    $typephone[$i] = $val->contact_type;
                    $phone[$i] = $val->contact_value;
                    $phoneid[$i] = $val->id;
                    $i++;
                  }
                }

                if (isset($typephone[1])) {
                  $i = 0;
                  foreach ($typephone as $val) {
                    if ($i > 0) {
                      $typephonex = isset($typephone[$i]) ? $typephone[$i] : "";
                      $phonex = isset($phone[$i]) ? $phone[$i] : "";
                      $phoneidx = isset($phoneid[$i]) ? $phoneid[$i] : "";
                ?>
                      <tr class="row phone-item noBorder" data-id="<?php echo $i + 1; ?>">
                        <td>
                          <table class="table" style="margin-left:5px;">
                            <tr class="row">
                              <td class="col-md-4">
                                <select id="typephone<?php echo $i + 1; ?>" name="typephone[]" class="form-control input-md">
                                  <option value="">--Choose--</option>
                                  <?php
                                  if (isset($m_phone)) {
                                    foreach ($m_phone as $val) {
                                  ?>
                                      <option value="<?php echo $val->type; ?>" <?php echo ($val->type == $typephonex) ? 'selected="true"' : ''; ?>><?php echo $val->desc; ?></option>

                                  <?php
                                    }
                                  }
                                  ?>
                                </select>
                                <input type="hidden" name="phoneid[]" id="phoneid<?php echo $i + 1; ?>" value="<?php echo $phoneidx; ?>" />
                              </td>
                              <td class="col-md-8">
                                <input id="phonem<?php echo $i + 1; ?>" name="phonem[]" type="text" placeholder="Contoh +627911123456" class="form-control input-md w400" value="<?php echo $phonex; ?>">
                                <script>
                                  var input = document.querySelector("#phonem<?php echo $i + 1; ?>");
                                  window.intlTelInput(input, {
                                    // allowDropdown: false,
                                    autoHideDialCode: true,
                                    // autoPlaceholder: "off",
                                    // dropdownContainer: document.body,
                                    // excludeCountries: ["us"],
                                    formatOnDisplay: false,
                                    // geoIpLookup: function(callback) {
                                    //   $.get("http://ipinfo.io", function() {}, "jsonp").always(function(resp) {
                                    //     var countryCode = (resp && resp.country) ? resp.country : "";
                                    //     callback(countryCode);
                                    //   });
                                    // },
                                    // hiddenInput: "full_number",
                                    // initialCountry: "auto",
                                    // localizedCountries: { 'de': 'Deutschland' },
                                    nationalMode: false,
                                    // onlyCountries: ['us', 'gb', 'ch', 'ca', 'do'],
                                    // placeholderNumberType: "MOBILE",
                                    preferredCountries: ['id'],
                                    // separateDialCode: true,
                                    utilsScript: "<?php echo base_url(); ?>assets/ada/phone/js/utils.js",
                                  });

                                  document.getElementById('phonem<?php echo $i + 1; ?>').addEventListener('input', function() {
                                    let start = this.selectionStart;
                                    let end = this.selectionEnd;

                                    const current = this.value
                                    const corrected = current.replace(/[^-+\d]/g, '');
                                    this.value = corrected;

                                    if (corrected.length < current.length) --end;
                                    this.setSelectionRange(start, end);
                                  });
                                </script>
                              </td>
                              <td class="td-action"><button type="button" class="btn btn-danger btn-xs phone-item-remove-button" data-id="<?php echo $i + 1; ?>"><i class="fa fa-trash-o fa-fw"></i>X</button></td>
                            </tr>
                          </table>
                        </td>
                      </tr>
                <?php
                    }
                    $i++;
                  }
                }
                ?>

              </table>
              <div class="col-md-12" style="padding-bottom:20px;">
                <button type="button" class="btn btn-primary" onclick="addPhone('tphone')">Tambah</button>
              </div>
            </div>

            <div class="wizard-buttons" style="text-align:center;">
              <button type="button" id="editprofile" class="btn btn-next">Simpan & Lanjut</button>
            </div>

          </fieldset>
          <fieldset id="fi_2">
            <h4 style="padding:15px;"><b>Pendidikan</b></h4>

            <h4 style="padding:15px;color:red;font-size:13px;"><b>Harap mencantumkan pendidikan S1 dan upload ijazah S1 wajib untuk proses keanggotaan.</b></h4>

            <div class="col-md-12">
              <table id="tschool" class="table" style="margin-left:5px;width:90%;">
                <tr class="row school-item noBorder" data-id="1">
                  <td>
                    <table class="table" border="1" style="margin-left:5px;">
                      <tr class="row">
                        <td class="col-md-4">
                          <label class="col-md-6 control-label">Tipe Pendidikan<span class="red"> *</span></label>
                        </td>
                        <td class="col-md-8">
                          <select id="c_school_type" name="c_school_type[]" class="form-control input-md" required="" onchange="tipe_school(1)">
                            <option value="">--Choose--</option>
                            <?php
                            if (isset($m_school_type)) {
                              foreach ($m_school_type as $val) {
                                if (isset($c_school_type[0])) {
                            ?>
                                  <option value="<?php echo $val->id; ?>" <?php echo (isset($c_school_type[0]) ? (($c_school_type[0] == $val->id) ? 'selected="true"' : "") : ""); ?>><?php echo $val->desc; ?></option>

                                <?php
                                } else {
                                ?>
                                  <option value="<?php echo $val->id; ?>" <?php echo (isset($user_edu[0]->type) ? (($user_edu[0]->type == $val->id) ? 'selected="true"' : "") : ""); ?>><?php echo $val->desc; ?></option>

                            <?php
                                }
                              }
                            }
                            ?>
                          </select>
                        </td>
                      </tr>
                      <tr class="row">
                        <td class="col-md-4">
                          <label class="col-md-8 control-label">Institusi / Universitas<span class="red"> *</span></label>
                        </td>
                        <td class="col-md-6">
                          <input id="c_school" name="c_school[]" type="text" placeholder="School" class="form-control input-md" required="" value="<?php echo set_value('c_school[0]', (isset($user_edu[0]->school) ? $user_edu[0]->school : '')); ?>">
                          <input type="hidden" name="schoolid[]" id="schoolid" value="<?php echo set_value('schoolid[0]', (isset($user_edu[0]->id) ? $user_edu[0]->id : '0')); ?>" />
                        </td>
                      </tr>
                      <tr class="row">
                        <td class="col-md-4">
                          <label class="col-md-6 control-label" id="label_c_tahun"><?php echo isset($user_edu[0]->type) ? (($user_edu[0]->type == "1") ? "Tahun" : "Tahun Lulus") : "Tahun"; ?><span class="red"> *</span></label>
                        </td>
                        <td class="col-md-6">
                          <input style="width:28%;float:left;" id="c_dateattendstart" name="c_dateattendstart[]" type="text" placeholder="Year" class="form-control input-md datepickeryear" required="" value="<?php echo set_value('c_dateattendstart[0]', (isset($user_edu[0]->startdate) ? $user_edu[0]->startdate : '')); ?>">
                          <div style="float:left;padding-left:2%;padding-right:2%;">-</div>
                          <input style="width:28.5%;float:left;" id="c_dateattendend" name="c_dateattendend[]" type="text" placeholder="Year" class="form-control input-md datepickeryear" required="" value="<?php echo set_value('c_dateattendend[0]', (isset($user_edu[0]->enddate) ? $user_edu[0]->enddate : '')); ?>">
                        </td>
                      </tr>
                      <tr class="row">
                        <td class="col-md-4">
                          <label class="col-md-8 control-label" id="label_c_tingkat" <?php echo isset($user_edu[0]->type) ? (($user_edu[0]->type == "1") ? "" : "style='display:none;'") : ""; ?>>Tingkat Pendidikan<span class="red"> *</span></label>
                        </td>
                        <td class="col-md-8">
                          <select id="c_degree" name="c_degree[]" class="form-control input-md" required="" <?php echo isset($user_edu[0]->type) ? (($user_edu[0]->type == "1") ? "" : "style='display:none;'") : ""; ?>>
                            <option value="">--Choose--</option>
                            <?php
                            if (isset($m_degree)) {

                              $temp_type = (isset($user_edu[0]->type) ? ($user_edu[0]->type == '1') ? "Y" : "N" : "N");

                              foreach ($m_degree as $val) {

                                if ($val->HAS_TABLE == $temp_type) {

                                  if (isset($c_degree[0])) {
                            ?>
                                    <option value="<?php echo $val->EDUCATION_TYPE_ID; ?>" <?php echo (isset($c_degree[0]) ? (($c_degree[0] == $val->EDUCATION_TYPE_ID) ? 'selected="true"' : "") : ""); ?>><?php echo $val->DESCRIPTION; ?></option>

                                  <?php
                                  } else {
                                  ?>
                                    <option value="<?php echo $val->EDUCATION_TYPE_ID; ?>" <?php echo (isset($user_edu[0]->degree) ? (($user_edu[0]->degree == $val->EDUCATION_TYPE_ID) ? 'selected="true"' : "") : ""); ?>><?php echo $val->DESCRIPTION; ?></option>

                            <?php
                                  }
                                }
                              }
                            }
                            ?>

                          </select>

                        </td>
                      </tr>
                      <!--<tr class="row">
								<td class="col-md-4">
									<label class="col-md-6 control-label">Jurusan<span class="red"> *</span></label> 
								</td>
								<td class="col-md-8">
								<input id="c_mayor" name="c_mayor[]" type="text" placeholder="Jurusan" class="form-control input-md" required="" value="<?php //echo set_value('c_mayor[0]',(isset($user_edu[0]->mayor)?$user_edu[0]->mayor:'')); 
                                                                                                                                        ?>">
								</td>
							</tr>-->
                      <tr class="row">
                        <td class="col-md-4">
                          <label class="col-md-8 control-label" id="label_c_mayor" <?php echo isset($user_edu[0]->type) ? (($user_edu[0]->type == "1") ? "" : "style='display:none;'") : ""; ?>>Fakultas<span class="red"> *</span></label>
                        </td>
                        <td class="col-md-8">
                          <input id="c_mayor" name="c_mayor[]" type="text" placeholder="Fakultas" class="form-control input-md" value="<?php echo set_value('c_mayor[0]', (isset($user_edu[0]->mayor) ? $user_edu[0]->mayor : '')); ?>" <?php echo isset($user_edu[0]->type) ? (($user_edu[0]->type == "1") ? "required=''" : "style='display:none;'") : "required=''"; ?>>
                        </td>
                      </tr>
                      <tr class="row">
                        <td class="col-md-4">
                          <label class="col-md-6 control-label" id="label_c_fos"><?php echo isset($user_edu[0]->type) ? (($user_edu[0]->type == "1") ? "Jurusan/Kejuruan" : "Nomor Sertifikat") : "Jurusan/Kejuruan"; ?><span class="red"> *</span></label>
                        </td>
                        <td class="col-md-8">
                          <input id="c_fos" name="c_fos[]" type="text" placeholder="Jurusan/Kejuruan" class="form-control input-md" required="" value="<?php echo set_value('c_fos[0]', (isset($user_edu[0]->fieldofstudy) ? $user_edu[0]->fieldofstudy : '')); ?>">
                        </td>
                      </tr>
                      <tr class="row">
                        <td class="col-md-4">
                          <label class="col-md-3 control-label" id="label_c_score"><?php echo isset($user_edu[0]->type) ? (($user_edu[0]->type == "1") ? "IPK" : "NIlai") : "IPK"; ?><span class="red"> *</span></label>
                        </td>
                        <td class="col-md-8">
                          <input id="c_score" name="c_score[]" type="text" placeholder="IPK" class="form-control input-md" required="" value="<?php echo set_value('c_score[0]', (isset($user_edu[0]->score) ? $user_edu[0]->score : '')); ?>">
                        </td>
                      </tr>
                      <tr class="row">
                        <td class="col-md-4">
                          <label class="col-md-3 control-label">Gelar<span class="red"> *</span></label>
                        </td>
                        <td class="col-md-8">
                          <div style="width:25%;float:left;">
                            <b>Gelar depan</b> (contoh: Drs.)<br />
                            <input id="c_prefix_title" name="c_prefix_title[]" type="text" placeholder="Gelar Depan" class="form-control input-md w400" value="<?php echo set_value('c_prefix_title[0]', (isset($user_edu[0]->title_prefix) ? $user_edu[0]->title_prefix : '')); ?>" style="width:100%">
                          </div>
                          <div style="width:3%;float:left;text-align:center;">
                            /
                          </div>
                          <div style="width:33%;float:left;">
                            <b>Gelar belakang</b> (contoh: M.M.)<br />
                            <input id="c_title" name="c_title[]" type="text" placeholder="Gelar Belakang" class="form-control input-md w400" value="<?php echo set_value('c_title[0]', (isset($user_edu[0]->title) ? $user_edu[0]->title : '')); ?>" style="width:100%">
                          </div>
                        </td>
                      </tr>
                      <tr class="row">
                        <td class="col-md-4">
                          <label class="col-md-8 control-label">Judul Tugas Akhir/Skripsi/Tesis/Disertasi</label>
                        </td>
                        <td class="col-md-8">
                          <input id="c_actv" name="c_actv[]" type="text" placeholder="Judul Tugas Akhir/Skripsi/Tesis/Disertasi" class="form-control input-md" value="<?php echo set_value('c_actv[0]', (isset($user_edu[0]->activities) ? $user_edu[0]->activities : '')); ?>">
                        </td>
                      </tr>
                      <tr class="row">
                        <td class="col-md-4">
                          <label class="col-md-8 control-label">Uraian Singkat Tentang Materi Tugas Akhir/ Skripsi/Tesis/ Disertasi</label>
                        </td>
                        <td class="col-md-8">
                          <textarea class="form-control" rows="5" id="c_descedu" name="c_descedu[]"><?php echo set_value('c_descedu[0]', (isset($user_edu[0]->description) ? $user_edu[0]->description : '')); ?></textarea>
                        </td>
                      </tr>
                      <tr class="row">
                        <td class="col-md-4">
                          <label class="col-md-8 control-label">Dokumen pendukung <br /> <span class="red">(Max. 700KB, image atau PDF)</span></label>
                        </td>
                        <td class="col-md-8">
                          <div class="form-group">
                            <div id="avatarattedu">
                              <input type="hidden" name="edu_image_url[]" value="<?php echo (isset($user_edu[0]->attachment)) ? $user_edu[0]->attachment : ''; ?>" style="display: inline-block;">
                              <?php echo (isset($user_edu[0]->attachment)) ? "<a href='" . base_url() . "/assets/uploads/" . $user_edu[0]->attachment . "' target='_blank' class='ava_discus'>" . $user_edu[0]->attachment . "</a>" : ''; ?>
                            </div>
                            <div id="errUploadattedu" class="red"></div>
                            <input type="file" name="attedu" id="attedu" class="form-control input-md" onchange="upload_edu('attedu')">
                          </div>
                        </td>
                      </tr>
                    </table>


                  </td>
                </tr>

                <?php
                $addressmail = '';
                // print_r($user_address);
                if (!isset($c_school[1])) {
                  $i = 0;
                  foreach ($user_edu as $val) {
                    $c_school[$i] = $val->school;
                    $c_dateattendstart[$i] = $val->startdate;
                    $c_dateattendend[$i] = $val->enddate;
                    $c_degree[$i] = $val->degree;
                    $c_fos[$i] = $val->fieldofstudy;
                    $c_mayor[$i] = $val->mayor;
                    $c_type[$i] = $val->type;
                    $c_score[$i] = $val->score;
                    $c_title[$i] = $val->title;
                    $c_title_prefix[$i] = $val->title_prefix;
                    $c_actv[$i] = $val->activities;
                    $c_descedu[$i] = $val->description;
                    $c_eduattachment[$i] = $val->attachment;
                    $c_id[$i] = $val->id;
                    $i++;
                  }
                }

                if (isset($c_school[1])) {
                  $i = 0;
                  foreach ($c_school as $val) {
                    if ($i > 0) {
                      $c_schoolx = isset($c_school[$i]) ? $c_school[$i] : "";
                      $c_dateattendstartx = isset($c_dateattendstart[$i]) ? $c_dateattendstart[$i] : "";
                      $c_dateattendendx = isset($c_dateattendend[$i]) ? $c_dateattendend[$i] : "";
                      $c_degreex = isset($c_degree[$i]) ? $c_degree[$i] : "";
                      $c_fosx = isset($c_fos[$i]) ? $c_fos[$i] : "";
                      $c_mayorx = isset($c_mayor[$i]) ? $c_mayor[$i] : "";
                      $c_typex = isset($c_type[$i]) ? $c_type[$i] : "";
                      $c_scorex = isset($c_score[$i]) ? $c_score[$i] : "";
                      $c_titlex = isset($c_title[$i]) ? $c_title[$i] : "";
                      $c_title_prefixx = isset($c_title_prefix[$i]) ? $c_title_prefix[$i] : "";
                      $c_actvx = isset($c_actv[$i]) ? $c_actv[$i] : "";
                      $c_descedux = isset($c_descedu[$i]) ? $c_descedu[$i] : "";
                      $c_eduattachmentx = isset($c_eduattachment[$i]) ? $c_eduattachment[$i] : "";
                      $c_idx = isset($c_id[$i]) ? $c_id[$i] : "";
                ?>
                      <tr class="row school-item noBorder" data-id="<?php echo $i + 1; ?>">
                        <td>
                          <table class="table" border="1" style="margin-left:5px;">
                            <tr class="row">
                              <td class="col-md-4">
                                <label class="col-md-6 control-label">Tipe Pendidikan<span class="red"> *</span></label>
                              </td>
                              <td class="col-md-8">
                                <select id="c_school_type<?php echo $i + 1; ?>" name="c_school_type[]" class="form-control input-md" required="" onchange="tipe_school(<?php echo $i + 1; ?>)">
                                  <option value="">--Choose--</option>
                                  <?php
                                  if (isset($m_school_type)) {
                                    foreach ($m_school_type as $val) {

                                  ?>

                                      <option value="<?php echo $val->id; ?>" <?php echo ($val->id == $c_typex) ? 'selected="true"' : ''; ?>><?php echo $val->desc; ?></option>

                                  <?php
                                    }
                                  }
                                  ?>

                                </select>

                              </td>


                              <td class="td-action"><button type="button" class="btn btn-danger btn-xs school-item-remove-button" data-id="<?php echo $i + 1; ?>"><i class="fa fa-trash-o fa-fw"></i>X</button></td>
                            </tr>
                            <tr class="row">
                              <td class="col-md-4">
                                <label class="col-md-8 control-label">Institusi / Universitas<span class="red"> *</span></label>
                              </td>
                              <td class="col-md-6" colspan="2">
                                <input id="c_school<?php echo $i + 1; ?>" name="c_school[]" type="text" placeholder="School" class="form-control input-md" required="" value="<?php echo $c_schoolx; ?>">
                                <input type="hidden" name="schoolid[]" id="schoolid<?php echo $i + 1; ?>" value="<?php echo $c_idx; ?>" />

                              </td>
                            </tr>
                            <tr class="row">
                              <td class="col-md-4">
                                <label class="col-md-6 control-label" id="label_c_tahun<?php echo $i + 1; ?>"><?php echo ($c_typex == "1") ? "Tahun" : "Tahun Lulus" ?><span class="red"> *</span></label>
                              </td>
                              <td class="col-md-6" colspan="2">
                                <input style="width:28%;float:left;" id="c_dateattendstart<?php echo $i + 1; ?>" name="c_dateattendstart[]" type="text" placeholder="Year" class="form-control input-md datepickeryear" required="" value="<?php echo $c_dateattendstartx; ?>">
                                <div style="float:left;padding-left:2%;padding-right:2%;">-</div>
                                <input style="width:27.5%;float:left;" id="c_dateattendend<?php echo $i + 1; ?>" name="c_dateattendend[]" type="text" placeholder="Year" class="form-control input-md datepickeryear" required="" value="<?php echo $c_dateattendendx; ?>">
                              </td>
                            </tr>
                            <tr class="row">
                              <td class="col-md-4">
                                <label class="col-md-8 control-label" id="label_c_tingkat<?php echo $i + 1; ?>" <?php echo ($c_typex == "1") ? "" : "style='display:none;'" ?>>Tingkat Pendidikan<span class="red"> *</span></label>
                              </td>
                              <td class="col-md-8" colspan="2">
                                <select id="c_degree<?php echo $i + 1; ?>" name="c_degree[]" class="form-control input-md" <?php echo ($c_typex == "1") ? "required=''" : ""; ?> <?php echo ($c_typex == "1") ? "" : "style='display:none;'"; ?>>
                                  <option value="">--Choose--</option>
                                  <?php
                                  if (isset($m_degree)) {

                                    $temp_type = ($c_typex == '1') ? "Y" : "N";

                                    foreach ($m_degree as $val) {
                                      if ($val->HAS_TABLE == $temp_type) {
                                  ?>

                                        <option value="<?php echo $val->EDUCATION_TYPE_ID; ?>" <?php echo ($val->EDUCATION_TYPE_ID == $c_degreex) ? 'selected="true"' : ''; ?>><?php echo $val->DESCRIPTION; ?></option>

                                  <?php
                                      }
                                    }
                                  }
                                  ?>

                                </select>

                              </td>
                            </tr>
                            <tr class="row">
                              <td class="col-md-4">
                                <label class="col-md-6 control-label" id="label_c_mayor<?php echo $i + 1; ?>" <?php echo ($c_typex == "1") ? "" : "style='display:none;'" ?>>Fakultas<span class="red"> *</span></label>
                              </td>
                              <td class="col-md-8" colspan="2">
                                <input id="c_mayor<?php echo $i + 1; ?>" name="c_mayor[]" type="text" placeholder="Fakultas" class="form-control input-md" value="<?php echo $c_mayorx; ?>" <?php echo ($c_typex == "1") ? "required=''" : "style='display:none;'" ?>>
                              </td>
                            </tr>
                            <tr class="row">
                              <td class="col-md-4">
                                <label class="col-md-6 control-label" id="label_c_fos<?php echo $i + 1; ?>"><?php echo ($c_typex == "1") ? "Jurusan/Kejuruan" : "Nomor Sertifikat" ?><span class="red"> *</span></label>
                              </td>
                              <td class="col-md-8" colspan="2">
                                <input id="c_fos<?php echo $i + 1; ?>" name="c_fos[]" type="text" placeholder="Jurusan/Kejuruan" class="form-control input-md" required="" value="<?php echo $c_fosx; ?>">
                              </td>
                            </tr>
                            <tr class="row">
                              <td class="col-md-4">
                                <label class="col-md-3 control-label" id="label_c_score<?php echo $i + 1; ?>"><?php echo ($c_typex == "1") ? 'IPK<span class="red"> *</span>' : "Nilai" ?></label>
                              </td>
                              <td class="col-md-8" colspan="2">
                                <input id="c_score<?php echo $i + 1; ?>" name="c_score[]" type="text" placeholder="IPK" class="form-control input-md" <?php echo ($c_typex == "1") ? 'required=""' : "" ?> value="<?php echo $c_scorex; ?>">
                              </td>
                            </tr>
                            <tr class="row">
                              <td class="col-md-4">
                                <label class="col-md-3 control-label">Gelar<span class="red"> *</span></label>
                              </td>
                              <td class="col-md-8" colspan="2">
                                <?php /* ?>
										<input id="c_title<?php echo $i+1;?>" name="c_title[]" type="text" placeholder="Gelar" class="form-control input-md w400" required=""  value="<?php echo $c_titlex; ?>">
										<?php */ ?>

                                <div style="width:25%;float:left;">
                                  <b>Gelar depan</b> (contoh: Drs.)<br />
                                  <input id="c_prefix_title<?php echo $i + 1; ?>" name="c_prefix_title[]" type="text" placeholder="Gelar Depan" class="form-control input-md w400" value="<?php echo $c_title_prefixx; ?>" style="width:100%">
                                </div>
                                <div style="width:3%;float:left;text-align:center;">
                                  /
                                </div>
                                <div style="width:32%;float:left;">
                                  <b>Gelar belakang</b> (contoh: M.M.)<br />
                                  <input id="c_title<?php echo $i + 1; ?>" name="c_title[]" type="text" placeholder="Gelar Belakang" class="form-control input-md w400" value="<?php echo $c_titlex; ?>" style="width:100%">
                                </div>

                              </td>
                            </tr>
                            <tr class="row">
                              <td class="col-md-4">
                                <label class="col-md-8 control-label">Judul Tugas Akhir/Skripsi/Tesis/Disertasi</label>
                              </td>
                              <td class="col-md-8" colspan="2">
                                <input id="c_actv<?php echo $i + 1; ?>" name="c_actv[]" type="text" placeholder="Judul Tugas Akhir/Skripsi/Tesis/Disertasi" class="form-control input-md" value="<?php echo $c_actvx; ?>">
                              </td>
                            </tr>
                            <tr class="row">
                              <td class="col-md-4">
                                <label class="col-md-8 control-label">Uraian Singkat Tentang Materi Tugas Akhir/ Skripsi/Tesis/ Disertasi</label>
                              </td>
                              <td class="col-md-8" colspan="2">
                                <textarea class="form-control" rows="5" id="c_descedu<?php echo $i + 1; ?>" name="c_descedu[]"><?php echo $c_descedux; ?></textarea>
                              </td>
                            </tr>
                            <tr class="row">
                              <td class="col-md-4">
                                <label class="col-md-8 control-label">Dokumen pendukung <br /> <span class="red">(Max. 700KB, image atau PDF)</span></label>
                              </td>
                              <td class="col-md-8" colspan="2">
                                <div class="form-group">
                                  <div id="avatarattedu<?php echo $i + 1; ?>">
                                    <?php echo ($c_eduattachmentx != '') ? "<a href='" . base_url() . "/assets/uploads/" . $c_eduattachmentx . "' target='_blank' class='ava_discus'>" . $c_eduattachmentx . "</a>" : ''; ?>
                                    <input type="hidden" name="edu_image_url[]" value="<?php echo $c_eduattachmentx; ?>" style="display: inline-block;">
                                  </div>
                                  <div id="errUploadattedu<?php echo $i + 1; ?>" class="red"></div>
                                  <input type="file" name="attedu<?php echo $i + 1; ?>" class="form-control input-md" id="attedu<?php echo $i + 1; ?>" onchange="upload_edu('attedu<?php echo $i + 1; ?>')">
                                </div>
                              </td>
                            </tr>


                          </table>
                        </td>
                      </tr>
                <?php
                    }
                    $i++;
                  }
                }
                ?>


              </table>
              <div class="col-md-12" style="padding-bottom:20px;">
                <button type="button" class="btn btn-primary" onclick="addSchool('tschool')">Tambah</button>
              </div>
              <label class="col-md-12 control-label"></label>
            </div>


            <h4 style="padding:15px;"><b>Sertifikasi Profesional</b></h4>

            <div class="col-md-12">

              <?php
              /*if(isset($user_cert_pii[0])){
					?>
					<h4 style="padding:15px;"><b>Sertifikasi Profesional PII</b></h4>
					<?php
					}*/
              ?>
              <?php
              //print_r($user_cert_pii);
              if (isset($user_cert_pii[0])) {
                $i = 0;
                echo '<table class="table" border="1" style="margin-left:5px;width:90%;">';
                foreach ($user_cert_pii as $val) {
              ?>
                  <tr class="row">
                    <td class="col-md-4">
                      <label class="col-md-6 control-label">Nama Sertifikasi</label>
                    </td>
                    <td class="col-md-6">
                      <?php echo (isset($user_cert_pii[$i]->cert_name) ? $user_cert_pii[$i]->cert_name : ''); ?>
                    </td>
                  </tr>
                  <tr class="row">
                    <td class="col-md-4">
                      <label class="col-md-6 control-label">Otoritas Sertifikasi</label>
                    </td>
                    <td class="col-md-8">
                      <?php echo (isset($user_cert_pii[$i]->cert_auth) ? $user_cert_pii[$i]->cert_auth : ''); ?>
                    </td>
                  </tr>
                  <tr class="row">
                    <td class="col-md-4">
                      <label class="col-md-6 control-label">Nomor lisensi</label>
                    </td>
                    <td class="col-md-8">
                      <?php echo (isset($user_cert_pii[$i]->lic_num) ? $user_cert_pii[$i]->lic_num : ''); ?>
                    </td>
                  </tr>
                  <tr class="row">
                    <td class="col-md-4">
                      <label class="col-md-3 control-label">Kualifikasi</label>
                    </td>
                    <td class="col-md-8">
                      <?php echo (isset($user_cert_pii[$i]->cert_title) ? $user_cert_pii[$i]->cert_title : ''); ?>
                    </td>
                  </tr>
                  <tr class="row">
                    <td class="col-md-4" style="border-bottom:1pt solid black;">
                      <label class="col-md-3 control-label">Tanggal</label>
                    </td>
                    <td class="col-md-8" style="border-bottom:1pt solid black;">
                      <?php echo (isset($user_cert_pii[$i]->startyear) ? $user_cert_pii[$i]->startyear : ''); ?> -
                      <?php echo (isset($user_cert_pii[$i]->endyear) ? $user_cert_pii[$i]->endyear : ''); ?>
                    </td>
                  </tr>
              <?php
                  $i++;
                }
                echo '</table>';
              }
              ?>

              <h4 style="padding:15px;"><b>Sertifikasi Profesional diluar Sertifikat Kompetensi Insinyur Profesional (IPP, IPM, IPU)</b></h4>

              <table id="tcert" class="table" style="margin-left:5px;width:90%;">
                <tr class="row cert-item noBorder" data-id="1">
                  <td>
                    <table class="table" border="1" style="margin-left:5px;">
                      <tr class="row">
                        <td class="col-md-4">
                          <label class="col-md-6 control-label">Nama Sertifikasi<span class="red"> *</span></label>
                        </td>
                        <td class="col-md-6">
                          <input id="c_certname" name="c_certname[]" type="text" placeholder="Nama Sertifikasi" class="form-control input-md" value="<?php echo set_value('c_certname[0]', (isset($user_cert[0]->cert_name) ? $user_cert[0]->cert_name : '')); ?>">
                          <input type="hidden" name="certid[]" id="certid" value="<?php echo set_value('certid[0]', (isset($user_cert[0]->id) ? $user_cert[0]->id : '0')); ?>" />
                        </td>
                      </tr>
                      <tr class="row">
                        <td class="col-md-4">
                          <label class="col-md-6 control-label">Otoritas Sertifikasi<span class="red"> *</span></label>
                        </td>
                        <td class="col-md-8">
                          <input id="c_certauth" name="c_certauth[]" type="text" placeholder="Otoritas Sertifikasi" class="form-control input-md" value="<?php echo set_value('c_certauth[0]', (isset($user_cert[0]->cert_auth) ? $user_cert[0]->cert_auth : '')); ?>">
                        </td>
                      </tr>
                      <tr class="row">
                        <td class="col-md-4">
                          <label class="col-md-6 control-label">Nomor lisensi<span class="red"> *</span></label>
                        </td>
                        <td class="col-md-8">
                          <input id="c_lic" name="c_lic[]" type="text" placeholder="Nomor lisensi" class="form-control input-md" value="<?php echo set_value('c_lic[0]', (isset($user_cert[0]->lic_num) ? $user_cert[0]->lic_num : '')); ?>">
                        </td>
                      </tr>
                      <tr class="row">
                        <td class="col-md-4">
                          <label class="col-md-6 control-label">URL sertifikasi</label>
                        </td>
                        <td class="col-md-8">
                          <input id="c_url" name="c_url[]" type="text" placeholder="URL sertifikasi" class="form-control input-md" value="<?php echo set_value('c_url[0]', (isset($user_cert[0]->cert_url) ? $user_cert[0]->cert_url : '')); ?>">
                        </td>
                      </tr>


                      <tr class="row">
                        <td class="col-md-4">
                          <label class="col-md-3 control-label">Kualifikasi<span class="red"> *</span></label>
                        </td>
                        <td class="col-md-8">
                          <!--<b>Kualifikasi</b> (contoh: IPM.)<br />-->
                          <input id="c_cert_title" name="c_cert_title[]" type="text" placeholder="Kualifikasi" class="form-control input-md w400" value="<?php echo set_value('c_cert_title[0]', (isset($user_cert[0]->cert_title) ? $user_cert[0]->cert_title : '')); ?>">
                        </td>
                      </tr>

                      <tr class="row">
                        <td class="col-md-4">
                          <label class="col-md-6 control-label">Tanggal<span class="red"> *</span></label>
                        </td>
                        <td class="col-md-6">

                          <div class="col-md-3" style="width: 180px;padding-left: 0px;">
                            <select id="c_certdate" name="c_certdate[]" class="form-control input-md">
                              <option value="">---</option>
                              <option value="1" <?php echo (isset($user_cert[0]->startmonth) ? (($user_cert[0]->startmonth == "1") ? 'selected="true"' : "") : ""); ?>>Januari</option>
                              <option value="2" <?php echo (isset($user_cert[0]->startmonth) ? (($user_cert[0]->startmonth == "2") ? 'selected="true"' : "") : ""); ?>>Pebruari</option>
                              <option value="3" <?php echo (isset($user_cert[0]->startmonth) ? (($user_cert[0]->startmonth == "3") ? 'selected="true"' : "") : ""); ?>>Maret</option>
                              <option value="4" <?php echo (isset($user_cert[0]->startmonth) ? (($user_cert[0]->startmonth == "4") ? 'selected="true"' : "") : ""); ?>>April</option>
                              <option value="5" <?php echo (isset($user_cert[0]->startmonth) ? (($user_cert[0]->startmonth == "5") ? 'selected="true"' : "") : ""); ?>>Mei</option>
                              <option value="6" <?php echo (isset($user_cert[0]->startmonth) ? (($user_cert[0]->startmonth == "6") ? 'selected="true"' : "") : ""); ?>>Juni</option>
                              <option value="7" <?php echo (isset($user_cert[0]->startmonth) ? (($user_cert[0]->startmonth == "7") ? 'selected="true"' : "") : ""); ?>>Juli</option>
                              <option value="8" <?php echo (isset($user_cert[0]->startmonth) ? (($user_cert[0]->startmonth == "8") ? 'selected="true"' : "") : ""); ?>>Agustus</option>
                              <option value="9" <?php echo (isset($user_cert[0]->startmonth) ? (($user_cert[0]->startmonth == "9") ? 'selected="true"' : "") : ""); ?>>September</option>
                              <option value="10" <?php echo (isset($user_cert[0]->startmonth) ? (($user_cert[0]->startmonth == "10") ? 'selected="true"' : "") : ""); ?>>Oktober</option>
                              <option value="11" <?php echo (isset($user_cert[0]->startmonth) ? (($user_cert[0]->startmonth == "11") ? 'selected="true"' : "") : ""); ?>>Nopember</option>
                              <option value="12" <?php echo (isset($user_cert[0]->startmonth) ? (($user_cert[0]->startmonth == "12") ? 'selected="true"' : "") : ""); ?>>Desember</option>
                            </select>
                            <input id="c_certyear" name="c_certyear[]" type="text" placeholder="Year" class="form-control input-md number datepickeryear" value="<?php echo set_value('c_certyear[0]', (isset($user_cert[0]->startyear) ? $user_cert[0]->startyear : '')); ?>">
                          </div>
                          <div class="col-md-1" id="is_presentcert1" <?php echo (isset($user_cert[0]->is_present) ? ($user_cert[0]->is_present == "1") ? 'style="display: none;"' : "" : ""); ?>>-</div>
                          <div class="col-md-3" id="is_presentcert2" <?php echo (isset($user_cert[0]->is_present) ? ($user_cert[0]->is_present == "1") ? 'style="display: none;width: 180px;padding-left: 0px;"' : 'style="width: 180px;padding-left: 0px;"' : 'style="width: 180px;padding-left: 0px;"'); ?>>
                            <select id="c_certdate2" name="c_certdate2[]" class="form-control input-md">
                              <option value="">---</option>
                              <option value="1" <?php echo (isset($user_cert[0]->endmonth) ? (($user_cert[0]->endmonth == "1") ? 'selected="true"' : "") : ""); ?>>Januari</option>
                              <option value="2" <?php echo (isset($user_cert[0]->endmonth) ? (($user_cert[0]->endmonth == "2") ? 'selected="true"' : "") : ""); ?>>Pebruari</option>
                              <option value="3" <?php echo (isset($user_cert[0]->endmonth) ? (($user_cert[0]->endmonth == "3") ? 'selected="true"' : "") : ""); ?>>Maret</option>
                              <option value="4" <?php echo (isset($user_cert[0]->endmonth) ? (($user_cert[0]->endmonth == "4") ? 'selected="true"' : "") : ""); ?>>April</option>
                              <option value="5" <?php echo (isset($user_cert[0]->endmonth) ? (($user_cert[0]->endmonth == "5") ? 'selected="true"' : "") : ""); ?>>Mei</option>
                              <option value="6" <?php echo (isset($user_cert[0]->endmonth) ? (($user_cert[0]->endmonth == "6") ? 'selected="true"' : "") : ""); ?>>Juni</option>
                              <option value="7" <?php echo (isset($user_cert[0]->endmonth) ? (($user_cert[0]->endmonth == "7") ? 'selected="true"' : "") : ""); ?>>Juli</option>
                              <option value="8" <?php echo (isset($user_cert[0]->endmonth) ? (($user_cert[0]->endmonth == "8") ? 'selected="true"' : "") : ""); ?>>Agustus</option>
                              <option value="9" <?php echo (isset($user_cert[0]->endmonth) ? (($user_cert[0]->endmonth == "9") ? 'selected="true"' : "") : ""); ?>>September</option>
                              <option value="10" <?php echo (isset($user_cert[0]->endmonth) ? (($user_cert[0]->endmonth == "10") ? 'selected="true"' : "") : ""); ?>>Oktober</option>
                              <option value="11" <?php echo (isset($user_cert[0]->endmonth) ? (($user_cert[0]->endmonth == "11") ? 'selected="true"' : "") : ""); ?>>Nopember</option>
                              <option value="12" <?php echo (isset($user_cert[0]->endmonth) ? (($user_cert[0]->endmonth == "12") ? 'selected="true"' : "") : ""); ?>>Desember</option>
                            </select>
                            <input id="c_certyear2" name="c_certyear2[]" type="text" placeholder="Year" class="form-control input-md number datepickeryear" value="<?php echo set_value('c_certyear2[0]', (isset($user_cert[0]->endyear) ? $user_cert[0]->endyear : '')); ?>">
                          </div>
                        </td>
                      </tr>

                      <tr class="row">
                        <td class="col-md-4">
                          <label class="col-md-6 control-label"></label>
                        </td>
                        <td class="col-md-8">
                          <label class="form-check-label"><input type="checkbox" id="c_certwork" name="c_certwork[]" data-id="1" class="form-check-input" value="1" <?php echo set_checkbox('c_certwork[0]', '1', (isset($user_cert[0]->is_present) ? ($user_cert[0]->is_present == "1") ? TRUE : FALSE : FALSE)); ?>>Sampai saat ini</label>
                        </td>
                      </tr>

                      <tr class="row">
                        <td class="col-md-4">
                          <label class="col-md-6 control-label">Deskripsi</label>
                        </td>
                        <td class="col-md-8">
                          <textarea class="form-control" rows="5" id="c_certdesc" name="c_certdesc[]"><?php echo set_value('c_certdesc[0]', (isset($user_cert[0]->description) ? $user_cert[0]->description : '')); ?></textarea>
                        </td>
                      </tr>

                      <tr class="row">
                        <td class="col-md-4">
                          <label class="col-md-8 control-label">Dokumen pendukung <br /> <span class="red">(Max. 700KB, image atau PDF)</span></label>
                        </td>
                        <td class="col-md-8">
                          <div class="form-group">
                            <div id="avatarattcert">
                              <input type="hidden" name="cert_image_url[]" value="<?php echo (isset($user_cert[0]->attachment)) ? $user_cert[0]->attachment : ''; ?>" style="display: inline-block;">
                              <?php echo (isset($user_cert[0]->attachment)) ? "<a href='" . base_url() . "/assets/uploads/" . $user_cert[0]->attachment . "' target='_blank' class='ava_discus'>" . $user_cert[0]->attachment . "</a>" : ''; ?>
                            </div>
                            <div id="errUploadattcert"></div>
                            <input type="file" name="attcert" id="attcert" class="form-control input-md" onchange="upload_cert('attcert')">
                          </div>
                        </td>
                      </tr>

                    </table>


                  </td>
                </tr>

                <?php
                //$addressmail='';
                // print_r($user_address);
                if (!isset($c_certname[1])) {
                  $i = 0;
                  foreach ($user_cert as $val) {
                    $c_certname[$i] = $val->cert_name;
                    $c_certauth[$i] = $val->cert_auth;
                    $c_lic[$i] = $val->lic_num;
                    $c_url[$i] = $val->cert_url;
                    $c_title[$i] = $val->cert_title;
                    $c_certdate[$i] = $val->startmonth;
                    $c_certyear[$i] = $val->startyear;
                    $c_certdate2[$i] = $val->endmonth;
                    $c_certyear2[$i] = $val->endyear;
                    $c_certwork[$i] = $val->is_present;
                    $c_certdesc[$i] = $val->description;
                    $c_certattach[$i] = $val->attachment;
                    $c_certid[$i] = $val->id;
                    $i++;
                  }
                }

                if (isset($c_certname[1])) {
                  $i = 0;
                  foreach ($c_certname as $val) {
                    if ($i > 0) {
                      $c_certnamex = isset($c_certname[$i]) ? $c_certname[$i] : "";
                      $c_certauthx = isset($c_certauth[$i]) ? $c_certauth[$i] : "";
                      $c_licx = isset($c_lic[$i]) ? $c_lic[$i] : "";
                      $c_urlx = isset($c_url[$i]) ? $c_url[$i] : "";
                      $c_titlex = isset($c_title[$i]) ? $c_title[$i] : "";
                      $c_certdatex = isset($c_certdate[$i]) ? $c_certdate[$i] : "";
                      $c_certyearx = isset($c_certyear[$i]) ? $c_certyear[$i] : "";
                      $c_certdate2x = isset($c_certdate2[$i]) ? $c_certdate2[$i] : "";
                      $c_certyear2x = isset($c_certyear2[$i]) ? $c_certyear2[$i] : "";
                      $c_certworkx = isset($c_certwork[$i]) ? $c_certwork[$i] : "";
                      $c_certdescx = isset($c_certdesc[$i]) ? $c_certdesc[$i] : "";
                      $c_certattachx = isset($c_certattach[$i]) ? $c_certattach[$i] : "";
                      $c_certidx = isset($c_certid[$i]) ? $c_certid[$i] : "";
                ?>
                      <tr class="row cert-item noBorder" data-id="<?php echo $i + 1; ?>">
                        <td>
                          <table class="table" border="1" style="margin-left:5px;">

                            <tr class="row">
                              <td class="col-md-4">
                                <label class="col-md-6 control-label">Nama Sertifikasi<span class="red"> *</span></label>
                              </td>
                              <td class="col-md-6">

                                <input id="c_certname<?php echo $i + 1; ?>" name="c_certname[]" type="text" placeholder="Nama Sertifikasi" class="form-control input-md" required="" value="<?php echo $c_certnamex; ?>">
                                <input type="hidden" name="certid[]" id="certid<?php echo $i + 1; ?>" value="<?php echo $c_certidx; ?>" />
                              </td>

                              <td class="td-action"><button type="button" class="btn btn-danger btn-xs cert-item-remove-button" data-id="<?php echo $i + 1; ?>"><i class="fa fa-trash-o fa-fw"></i>X</button></td>
                            </tr>

                            <tr class="row">
                              <td class="col-md-4">
                                <label class="col-md-6 control-label">Otoritas Sertifikasi<span class="red"> *</span></label>
                              </td>
                              <td class="col-md-8" colspan="2">
                                <input id="c_certauth<?php echo $i + 1; ?>" name="c_certauth[]" type="text" placeholder="Otoritas Sertifikasi" class="form-control input-md" value="<?php echo $c_certauthx; ?>" required="">
                              </td>
                            </tr>
                            <tr class="row">
                              <td class="col-md-4">
                                <label class="col-md-6 control-label">Nomor lisensi<span class="red"> *</span></label>
                              </td>
                              <td class="col-md-8" colspan="2">
                                <input id="c_lic<?php echo $i + 1; ?>" name="c_lic[]" type="text" placeholder="Nomor lisensi" class="form-control input-md" value="<?php echo $c_licx; ?>" required="">
                              </td>
                            </tr>
                            <tr class="row">
                              <td class="col-md-4">
                                <label class="col-md-6 control-label">URL sertifikasi</label>
                              </td>
                              <td class="col-md-8" colspan="2">
                                <input id="c_url<?php echo $i + 1; ?>" name="c_url[]" type="text" placeholder="URL sertifikasi" class="form-control input-md" value="<?php echo $c_urlx; ?>">
                              </td>
                            </tr>

                            <tr class="row">
                              <td class="col-md-4">
                                <label class="col-md-3 control-label">Kualifikasi<span class="red"> *</span></label>
                              </td>
                              <td class="col-md-8" colspan="2">
                                <!--<b>Kualifikasi</b> (contoh: IPM.)<br />-->
                                <input id="c_cert_title<?php echo $i + 1; ?>" name="c_cert_title[]" type="text" placeholder="Kualifikasi" class="form-control input-md w400" required="" value="<?php echo $c_titlex; ?>">
                              </td>
                            </tr>

                            <tr class="row">
                              <td class="col-md-4">
                                <label class="col-md-6 control-label">Tanggal<span class="red"> *</span></label>
                              </td>
                              <td class="col-md-6" colspan="2">

                                <div class="col-md-3" style="width: 180px;padding-left: 0px;">
                                  <select id="c_certdate<?php echo $i + 1; ?>" required="" name="c_certdate[]" class="form-control input-md">
                                    <option value="">---</option>
                                    <option value="1" <?php echo ("1" == $c_certdatex) ? 'selected="true"' : ''; ?>>Januari</option>
                                    <option value="2" <?php echo ("2" == $c_certdatex) ? 'selected="true"' : ''; ?>>Pebruari</option>
                                    <option value="3" <?php echo ("3" == $c_certdatex) ? 'selected="true"' : ''; ?>>Maret</option>
                                    <option value="4" <?php echo ("4" == $c_certdatex) ? 'selected="true"' : ''; ?>>April</option>
                                    <option value="5" <?php echo ("5" == $c_certdatex) ? 'selected="true"' : ''; ?>>Mei</option>
                                    <option value="6" <?php echo ("6" == $c_certdatex) ? 'selected="true"' : ''; ?>>Juni</option>
                                    <option value="7" <?php echo ("7" == $c_certdatex) ? 'selected="true"' : ''; ?>>Juli</option>
                                    <option value="8" <?php echo ("8" == $c_certdatex) ? 'selected="true"' : ''; ?>>Agustus</option>
                                    <option value="9" <?php echo ("9" == $c_certdatex) ? 'selected="true"' : ''; ?>>September</option>
                                    <option value="10" <?php echo ("10" == $c_certdatex) ? 'selected="true"' : ''; ?>>Oktober</option>
                                    <option value="11" <?php echo ("11" == $c_certdatex) ? 'selected="true"' : ''; ?>>Nopember</option>
                                    <option value="12" <?php echo ("12" == $c_certdatex) ? 'selected="true"' : ''; ?>>Desember</option>
                                  </select>
                                  <input id="c_certyear<?php echo $i + 1; ?>" required="" name="c_certyear[]" type="text" placeholder="Year" class="form-control input-md number datepickeryear" value="<?php echo $c_certyearx; ?>">
                                </div>
                                <div class="col-md-1" id="is_presentcert1<?php echo $i + 1; ?>" <?php echo (isset($c_certworkx) ? ($c_certworkx == "1") ? 'style="display: none;"' : "" : ""); ?>>-</div>
                                <div class="col-md-3" id="is_presentcert2<?php echo $i + 1; ?>" <?php echo (isset($c_certworkx) ? ($c_certworkx == "1") ? 'style="display: none;width: 180px;padding-left: 0px;"' : 'style="width: 180px;padding-left: 0px;"' : 'style="width: 180px;padding-left: 0px;"'); ?>>
                                  <select id="c_certdate2<?php echo $i + 1; ?>" name="c_certdate2[]" class="form-control input-md">
                                    <option value="">---</option>
                                    <option value="1" <?php echo ("1" == $c_certdate2x) ? 'selected="true"' : ''; ?>>Januari</option>
                                    <option value="2" <?php echo ("2" == $c_certdate2x) ? 'selected="true"' : ''; ?>>Pebruari</option>
                                    <option value="3" <?php echo ("3" == $c_certdate2x) ? 'selected="true"' : ''; ?>>Maret</option>
                                    <option value="4" <?php echo ("4" == $c_certdate2x) ? 'selected="true"' : ''; ?>>April</option>
                                    <option value="5" <?php echo ("5" == $c_certdate2x) ? 'selected="true"' : ''; ?>>Mei</option>
                                    <option value="6" <?php echo ("6" == $c_certdate2x) ? 'selected="true"' : ''; ?>>Juni</option>
                                    <option value="7" <?php echo ("7" == $c_certdate2x) ? 'selected="true"' : ''; ?>>Juli</option>
                                    <option value="8" <?php echo ("8" == $c_certdate2x) ? 'selected="true"' : ''; ?>>Agustus</option>
                                    <option value="9" <?php echo ("9" == $c_certdate2x) ? 'selected="true"' : ''; ?>>September</option>
                                    <option value="10" <?php echo ("10" == $c_certdate2x) ? 'selected="true"' : ''; ?>>Oktober</option>
                                    <option value="11" <?php echo ("11" == $c_certdate2x) ? 'selected="true"' : ''; ?>>Nopember</option>
                                    <option value="12" <?php echo ("12" == $c_certdate2x) ? 'selected="true"' : ''; ?>>Desember</option>
                                  </select>
                                  <input id="c_certyear2<?php echo $i + 1; ?>" name="c_certyear2[]" type="text" placeholder="Year" class="form-control input-md number datepickeryear" value="<?php echo $c_certyear2x; ?>">
                                </div>
                              </td>
                            </tr>

                            <tr class="row">
                              <td class="col-md-4">
                                <label class="col-md-6 control-label"></label>
                              </td>
                              <td class="col-md-8" colspan="2">
                                <label class="form-check-label"><input type="checkbox" id="c_certwork<?php echo $i + 1; ?>" name="c_certwork[]" data-id="<?php echo $i + 1; ?>" class="form-check-input" value="1" <?php echo set_checkbox('c_certwork[0]', '1', (isset($c_certworkx) ? ($c_certworkx == "1") ? TRUE : FALSE : FALSE)); ?>>Sampai saat ini</label>
                              </td>
                            </tr>



                            <tr class="row">
                              <td class="col-md-4">
                                <label class="col-md-3 control-label">Deskripsi</label>
                              </td>
                              <td class="col-md-8" colspan="2">
                                <textarea class="form-control" rows="5" id="c_certdesc<?php echo $i + 1; ?>" name="c_certdesc[]"><?php echo $c_certdescx; ?></textarea>
                              </td>
                            </tr>

                            <tr class="row">
                              <td class="col-md-4">
                                <label class="col-md-8 control-label">Dokumen pendukung <br /> <span class="red">(Max. 700KB, image atau PDF)</span></label>
                              </td>
                              <td class="col-md-8" colspan="2">
                                <div class="form-group">
                                  <div id="avatarattcert<?php echo $i + 1; ?>">
                                    <?php echo ($c_certattachx != '') ? "<a href='" . base_url() . "/assets/uploads/" . $c_certattachx . "' target='_blank' class='ava_discus'>" . $c_certattachx . "</a>" : ''; ?>
                                    <input type="hidden" name="cert_image_url[]" value="<?php echo $c_certattachx; ?>" style="display: inline-block;">
                                  </div>
                                  <div id="errUploadattcert<?php echo $i + 1; ?>"></div>
                                  <input type="file" name="attcert<?php echo $i + 1; ?>" class="form-control input-md" id="attcert<?php echo $i + 1; ?>" onchange="upload_cert('attcert<?php echo $i + 1; ?>')">
                                </div>
                              </td>
                            </tr>


                          </table>
                        </td>
                      </tr>
                <?php
                    }
                    $i++;
                  }
                }
                ?>


              </table>
              <div class="col-md-12" style="padding-bottom:20px;">
                <button type="button" class="btn btn-primary" onclick="addCert('tcert')">Tambah</button>
              </div>

              <label class="col-md-12 control-label"></label>
            </div>

            <div class="wizard-buttons" style="text-align:center;">
              <button type="button" class="btn btn-previous">Kembali</button>
              <button type="button" class="btn btn-next" id="editschool">Simpan & Lanjut</button>
            </div>
          </fieldset>
          <fieldset id="fi_3">
            <h4 style="padding:15px;"><b>Pengalaman Kerja/Profesional</b></h4>

            <div class="col-md-12">
              <table id="texp" class="table" style="margin-left:5px;width:90%;">
                <tr class="row exp-item noBorder" data-id="1">
                  <td>
                    <table class="table" border="1" style="margin-left:5px;">

                      <tr class="row">
                        <td class="col-md-4">
                          <label class="col-md-5 control-label" for="c_company">Perusahaan<span class="red"> *</span></label>
                        </td>
                        <td class="col-md-6">
                          <input id="c_company" name="c_company[]" type="text" placeholder="Perusahaan" class="form-control input-md" required="" value="<?php echo set_value('c_company[0]', (isset($user_exp[0]->company) ? $user_exp[0]->company : '')); ?>">
                          <input type="hidden" name="expid[]" id="expid" value="<?php echo set_value('expid[0]', (isset($user_exp[0]->id) ? $user_exp[0]->id : '0')); ?>" />
                        </td>
                      </tr>
                      <tr class="row">
                        <td class="col-md-4">
                          <label class="col-md-5 control-label" for="c_title">Jabatan/Tugas<span class="red"> *</span></label>
                        </td>
                        <td class="col-md-6">
                          <input id="c_exptitle" name="c_exptitle[]" type="text" placeholder="Jabatan/Tugas" class="form-control input-md" required="" value="<?php echo set_value('c_exptitle[0]', (isset($user_exp[0]->title) ? $user_exp[0]->title : '')); ?>">

                        </td>
                      </tr>
                      <tr class="row">
                        <td class="col-md-4">
                          <label class="col-md-8 control-label" for="c_loc">Kabupaten/Kota<span class="red"> *</span></label>
                        </td>
                        <td class="col-md-6">
                          <input id="c_loc" name="c_loc[]" type="text" placeholder="Lokasi" class="form-control input-md w400" required="" value="<?php echo set_value('c_loc[0]', (isset($user_exp[0]->location) ? $user_exp[0]->location : '')); ?>">

                        </td>
                      </tr>
                      <tr class="row">
                        <td class="col-md-4">
                          <label class="col-md-8 control-label" for="c_provinsi">Provinsi<span class="red"> *</span></label>
                        </td>
                        <td class="col-md-6">
                          <input id="c_provinsi" name="c_provinsi[]" type="text" placeholder="Provinsi" class="form-control input-md w400" required="" value="<?php echo set_value('c_provinsi[0]', (isset($user_exp[0]->provinsi) ? $user_exp[0]->provinsi : '')); ?>">

                        </td>
                      </tr>
                      <tr class="row">
                        <td class="col-md-4">
                          <label class="col-md-8 control-label" for="c_negara">Negara<span class="red"> *</span></label>
                        </td>
                        <td class="col-md-6">
                          <input id="c_negara" name="c_negara[]" type="text" placeholder="Negara" class="form-control input-md w400" required="" value="<?php echo set_value('c_negara[0]', (isset($user_exp[0]->negara) ? $user_exp[0]->negara : '')); ?>">

                        </td>
                      </tr>
                      <tr class="row">
                        <td class="col-md-4">
                          <label class="col-md-6 control-label">Periode<span class="red"> *</span></label>
                        </td>
                        <td class="col-md-6">

                          <div class="col-md-3" style="width: 180px;padding-left: 0px;">
                            <select id="c_typetimeperiod" name="c_typetimeperiod[]" required="" class="form-control input-md">
                              <option value="">---</option>
                              <option value="1" <?php echo (isset($user_exp[0]->startmonth) ? (($user_exp[0]->startmonth == "1") ? 'selected="true"' : "") : ""); ?>>Januari</option>
                              <option value="2" <?php echo (isset($user_exp[0]->startmonth) ? (($user_exp[0]->startmonth == "2") ? 'selected="true"' : "") : ""); ?>>Pebruari</option>
                              <option value="3" <?php echo (isset($user_exp[0]->startmonth) ? (($user_exp[0]->startmonth == "3") ? 'selected="true"' : "") : ""); ?>>Maret</option>
                              <option value="4" <?php echo (isset($user_exp[0]->startmonth) ? (($user_exp[0]->startmonth == "4") ? 'selected="true"' : "") : ""); ?>>April</option>
                              <option value="5" <?php echo (isset($user_exp[0]->startmonth) ? (($user_exp[0]->startmonth == "5") ? 'selected="true"' : "") : ""); ?>>Mei</option>
                              <option value="6" <?php echo (isset($user_exp[0]->startmonth) ? (($user_exp[0]->startmonth == "6") ? 'selected="true"' : "") : ""); ?>>Juni</option>
                              <option value="7" <?php echo (isset($user_exp[0]->startmonth) ? (($user_exp[0]->startmonth == "7") ? 'selected="true"' : "") : ""); ?>>Juli</option>
                              <option value="8" <?php echo (isset($user_exp[0]->startmonth) ? (($user_exp[0]->startmonth == "8") ? 'selected="true"' : "") : ""); ?>>Agustus</option>
                              <option value="9" <?php echo (isset($user_exp[0]->startmonth) ? (($user_exp[0]->startmonth == "9") ? 'selected="true"' : "") : ""); ?>>September</option>
                              <option value="10" <?php echo (isset($user_exp[0]->startmonth) ? (($user_exp[0]->startmonth == "10") ? 'selected="true"' : "") : ""); ?>>Oktober</option>
                              <option value="11" <?php echo (isset($user_exp[0]->startmonth) ? (($user_exp[0]->startmonth == "11") ? 'selected="true"' : "") : ""); ?>>Nopember</option>
                              <option value="12" <?php echo (isset($user_exp[0]->startmonth) ? (($user_exp[0]->startmonth == "12") ? 'selected="true"' : "") : ""); ?>>Desember</option>
                            </select>
                            <input id="c_year" name="c_year[]" required="" type="text" placeholder="Year" class="form-control input-md number datepickeryear" value="<?php echo set_value('c_year[0]', (isset($user_exp[0]->startyear) ? $user_exp[0]->startyear : '')); ?>">
                          </div>
                          <div class="col-md-1" id="is_presentwork1" <?php echo (isset($user_exp[0]->is_present) ? ($user_exp[0]->is_present == "1") ? 'style="display: none;"' : "" : ""); ?>>-</div>
                          <div class="col-md-3" id="is_presentwork2" <?php echo (isset($user_exp[0]->is_present) ? ($user_exp[0]->is_present == "1") ? 'style="display: none;padding-left: 0px;width: 180px;"' : 'style="width: 180px;padding-left: 0px;"' : 'style="width: 180px;padding-left: 0px;"'); ?>>
                            <select id="c_typetimeperiod2" name="c_typetimeperiod2[]" class="form-control input-md">
                              <option value="">---</option>
                              <option value="1" <?php echo (isset($user_exp[0]->endmonth) ? (($user_exp[0]->endmonth == "1") ? 'selected="true"' : "") : ""); ?>>Januari</option>
                              <option value="2" <?php echo (isset($user_exp[0]->endmonth) ? (($user_exp[0]->endmonth == "2") ? 'selected="true"' : "") : ""); ?>>Pebruari</option>
                              <option value="3" <?php echo (isset($user_exp[0]->endmonth) ? (($user_exp[0]->endmonth == "3") ? 'selected="true"' : "") : ""); ?>>Maret</option>
                              <option value="4" <?php echo (isset($user_exp[0]->endmonth) ? (($user_exp[0]->endmonth == "4") ? 'selected="true"' : "") : ""); ?>>April</option>
                              <option value="5" <?php echo (isset($user_exp[0]->endmonth) ? (($user_exp[0]->endmonth == "5") ? 'selected="true"' : "") : ""); ?>>Mei</option>
                              <option value="6" <?php echo (isset($user_exp[0]->endmonth) ? (($user_exp[0]->endmonth == "6") ? 'selected="true"' : "") : ""); ?>>Juni</option>
                              <option value="7" <?php echo (isset($user_exp[0]->endmonth) ? (($user_exp[0]->endmonth == "7") ? 'selected="true"' : "") : ""); ?>>Juli</option>
                              <option value="8" <?php echo (isset($user_exp[0]->endmonth) ? (($user_exp[0]->endmonth == "8") ? 'selected="true"' : "") : ""); ?>>Agustus</option>
                              <option value="9" <?php echo (isset($user_exp[0]->endmonth) ? (($user_exp[0]->endmonth == "9") ? 'selected="true"' : "") : ""); ?>>September</option>
                              <option value="10" <?php echo (isset($user_exp[0]->endmonth) ? (($user_exp[0]->endmonth == "10") ? 'selected="true"' : "") : ""); ?>>Oktober</option>
                              <option value="11" <?php echo (isset($user_exp[0]->endmonth) ? (($user_exp[0]->endmonth == "11") ? 'selected="true"' : "") : ""); ?>>Nopember</option>
                              <option value="12" <?php echo (isset($user_exp[0]->endmonth) ? (($user_exp[0]->endmonth == "12") ? 'selected="true"' : "") : ""); ?>>Desember</option>
                            </select>
                            <input id="c_year2" name="c_year2[]" type="text" placeholder="Year" class="form-control input-md number datepickeryear" value="<?php echo set_value('c_year2[0]', (isset($user_exp[0]->endyear) ? $user_exp[0]->endyear : '')); ?>">
                          </div>
                        </td>
                      </tr>

                      <tr class="row">
                        <td class="col-md-4">
                          <label class="col-md-6 control-label"></label>
                        </td>
                        <td class="col-md-8">
                          <label class="form-check-label"><input type="checkbox" id="c_work" name="c_work[]" data-id="1" class="form-check-input" value="1" <?php echo set_checkbox('c_work[0]', '1', (isset($user_exp[0]->is_present) ? ($user_exp[0]->is_present == "1") ? TRUE : FALSE : FALSE)); ?>>Sampai saat ini</label>
                        </td>
                      </tr>

                      <tr class="row">
                        <td class="col-md-4">
                          <label class="col-md-8 control-label" for="c_actv">Nama Aktifitas/Kegiatan/Proyek</label>
                        </td>
                        <td class="col-md-6">
                          <textarea class="form-control" rows="5" id="c_actv" name="c_actv[]"><?php echo set_value('c_actv[0]', (isset($user_exp[0]->actv) ? $user_exp[0]->actv : '')); ?></textarea>

                        </td>
                      </tr>

                      <tr class="row">
                        <td class="col-md-4">
                          <label class="col-md-8 control-label" for="c_desc">Uraian Singkat Tugas dan Tanggung Jawab Profesional</label>
                        </td>
                        <td class="col-md-6">
                          <textarea class="form-control" rows="5" id="c_desc" name="c_desc[]"><?php echo set_value('c_desc[0]', (isset($user_exp[0]->description) ? $user_exp[0]->description : '')); ?></textarea>

                        </td>
                      </tr>

                      <tr class="row">
                        <td class="col-md-4">
                          <label class="col-md-8 control-label">Dokumen pendukung <br /> <span class="red">(Max. 700KB, image atau PDF)</span></label>
                        </td>
                        <td class="col-md-8">
                          <div class="form-group">
                            <div id="avatarattexp">
                              <input type="hidden" name="exp_image_url[]" value="<?php echo (isset($user_exp[0]->attachment)) ? $user_exp[0]->attachment : ''; ?>" style="display: inline-block;">
                              <?php echo (isset($user_exp[0]->attachment)) ? "<a href='" . base_url() . "/assets/uploads/" . $user_exp[0]->attachment . "' target='_blank' class='ava_discus'>" . $user_exp[0]->attachment . "</a>" : ''; ?>
                            </div>
                            <div id="errUploadattexp" class="red"></div>
                            <input type="file" name="attexp" id="attexp" class="form-control input-md" onchange="upload_exp('attexp')">
                          </div>
                        </td>
                      </tr>

                    </table>


                  </td>
                </tr>

                <?php
                $addressmail = '';
                // print_r($user_address);
                if (!isset($c_company[1])) {
                  $i = 0;
                  foreach ($user_exp as $val) {
                    $c_company[$i] = $val->company;
                    $c_exptitle[$i] = $val->title;
                    $c_loc[$i] = $val->location;
                    $c_provinsi[$i] = $val->provinsi;
                    $c_negara[$i] = $val->negara;
                    $c_year[$i] = $val->startyear;
                    $c_typetimeperiod[$i] = $val->startmonth;
                    $c_year2[$i] = $val->endyear;
                    $c_typetimeperiod2[$i] = $val->endmonth;
                    $c_work[$i] = $val->is_present;
                    $c_desc[$i] = $val->description;
                    $c_actv[$i] = $val->actv;
                    $c_id[$i] = $val->id;
                    $c_expattachment[$i] = $val->attachment;
                    $i++;
                  }
                }

                if (isset($c_company[1])) {
                  $i = 0;
                  foreach ($c_company as $val) {
                    if ($i > 0) {
                      $c_companyx = isset($c_company[$i]) ? $c_company[$i] : "";
                      $c_exptitlex = isset($c_exptitle[$i]) ? $c_exptitle[$i] : "";
                      $c_locx = isset($c_loc[$i]) ? $c_loc[$i] : "";
                      $c_provinsix = isset($c_provinsi[$i]) ? $c_provinsi[$i] : "";
                      $c_negarax = isset($c_negara[$i]) ? $c_negara[$i] : "";
                      $c_yearx = isset($c_year[$i]) ? $c_year[$i] : "";
                      $c_typetimeperiodx = isset($c_typetimeperiod[$i]) ? $c_typetimeperiod[$i] : "";
                      $c_year2x = isset($c_year2[$i]) ? $c_year2[$i] : "";
                      $c_typetimeperiod2x = isset($c_typetimeperiod2[$i]) ? $c_typetimeperiod2[$i] : "";
                      $c_workx = isset($c_work[$i]) ? $c_work[$i] : "";
                      $c_descx = isset($c_desc[$i]) ? $c_desc[$i] : "";
                      $c_actvx = isset($c_actv[$i]) ? $c_actv[$i] : "";
                      $c_idx = isset($c_id[$i]) ? $c_id[$i] : "";
                      $c_expattachmentx = isset($c_expattachment[$i]) ? $c_expattachment[$i] : "";
                ?>
                      <tr class="row exp-item noBorder" data-id="<?php echo $i + 1; ?>">
                        <td>
                          <table class="table" border="1" style="margin-left:5px;">


                            <tr class="row">
                              <td class="col-md-4">
                                <label class="col-md-5 control-label">Perusahaan<span class="red"> *</span></label>
                              </td>
                              <td class="col-md-6">

                                <input id="c_company<?php echo $i + 1; ?>" name="c_company[]" type="text" placeholder="Perusahaan" class="form-control input-md" required="" value="<?php echo $c_companyx; ?>">

                                <input type="hidden" name="expid[]" id="expid<?php echo $i + 1; ?>" value="<?php echo $c_idx; ?>" />
                              </td>

                              <td class="td-action"><button type="button" class="btn btn-danger btn-xs exp-item-remove-button" data-id="<?php echo $i + 1; ?>"><i class="fa fa-trash-o fa-fw"></i>X</button></td>
                            </tr>

                            <tr class="row">
                              <td class="col-md-4">
                                <label class="col-md-6 control-label">Jabatan/Tugas<span class="red"> *</span></label>
                              </td>
                              <td class="col-md-8" colspan="2">
                                <input id="c_exptitle<?php echo $i + 1; ?>" name="c_exptitle[]" type="text" placeholder="Jabatan/Tugas" class="form-control input-md" required="" value="<?php echo $c_exptitlex; ?>">
                              </td>
                            </tr>
                            <tr class="row">
                              <td class="col-md-4">
                                <label class="col-md-6 control-label">Kabupaten/Kota<span class="red"> *</span></label>
                              </td>
                              <td class="col-md-8" colspan="2">
                                <input id="c_loc<?php echo $i + 1; ?>" name="c_loc[]" type="text" placeholder="Lokasi" class="form-control input-md w400" required="" value="<?php echo $c_locx; ?>">
                              </td>
                            </tr>
                            <tr class="row">
                              <td class="col-md-4">
                                <label class="col-md-6 control-label">Provinsi<span class="red"> *</span></label>
                              </td>
                              <td class="col-md-8" colspan="2">
                                <input id="c_provinsi<?php echo $i + 1; ?>" name="c_provinsi[]" type="text" placeholder="Provinsi" class="form-control input-md w400" required="" value="<?php echo $c_provinsix; ?>">
                              </td>
                            </tr>
                            <tr class="row">
                              <td class="col-md-4">
                                <label class="col-md-6 control-label">Negara<span class="red"> *</span></label>
                              </td>
                              <td class="col-md-8" colspan="2">
                                <input id="c_negara<?php echo $i + 1; ?>" name="c_negara[]" type="text" placeholder="Negara" class="form-control input-md w400" required="" value="<?php echo $c_negarax; ?>">
                              </td>
                            </tr>

                            <tr class="row">
                              <td class="col-md-4">
                                <label class="col-md-6 control-label">Periode<span class="red"> *</span></label>
                              </td>
                              <td class="col-md-6" colspan="2">

                                <div class="col-md-3" style="width: 180px;padding-left: 0px;">
                                  <select id="c_typetimeperiod<?php echo $i + 1; ?>" required="" name="c_typetimeperiod[]" class="form-control input-md">
                                    <option value="">---</option>
                                    <option value="1" <?php echo ("1" == $c_typetimeperiodx) ? 'selected="true"' : ''; ?>>Januari</option>
                                    <option value="2" <?php echo ("2" == $c_typetimeperiodx) ? 'selected="true"' : ''; ?>>Pebruari</option>
                                    <option value="3" <?php echo ("3" == $c_typetimeperiodx) ? 'selected="true"' : ''; ?>>Maret</option>
                                    <option value="4" <?php echo ("4" == $c_typetimeperiodx) ? 'selected="true"' : ''; ?>>April</option>
                                    <option value="5" <?php echo ("5" == $c_typetimeperiodx) ? 'selected="true"' : ''; ?>>Mei</option>
                                    <option value="6" <?php echo ("6" == $c_typetimeperiodx) ? 'selected="true"' : ''; ?>>Juni</option>
                                    <option value="7" <?php echo ("7" == $c_typetimeperiodx) ? 'selected="true"' : ''; ?>>Juli</option>
                                    <option value="8" <?php echo ("8" == $c_typetimeperiodx) ? 'selected="true"' : ''; ?>>Agustus</option>
                                    <option value="9" <?php echo ("9" == $c_typetimeperiodx) ? 'selected="true"' : ''; ?>>September</option>
                                    <option value="10" <?php echo ("10" == $c_typetimeperiodx) ? 'selected="true"' : ''; ?>>Oktober</option>
                                    <option value="11" <?php echo ("11" == $c_typetimeperiodx) ? 'selected="true"' : ''; ?>>Nopember</option>
                                    <option value="12" <?php echo ("12" == $c_typetimeperiodx) ? 'selected="true"' : ''; ?>>Desember</option>
                                  </select>
                                  <input id="c_year<?php echo $i + 1; ?>" required="" name="c_year[]" type="text" placeholder="Year" class="form-control input-md number datepickeryear" value="<?php echo $c_yearx; ?>">
                                </div>
                                <div class="col-md-1" id="is_presentwork1<?php echo $i + 1; ?>" <?php echo (isset($c_workx) ? ($c_workx == "1") ? 'style="display: none;"' : "" : ""); ?>>-</div>
                                <div class="col-md-3" id="is_presentwork2<?php echo $i + 1; ?>" <?php echo (isset($c_workx) ? ($c_workx == "1") ? 'style="display: none;padding-left: 0px;width: 180px;"' : 'style="width: 180px;padding-left: 0px;"' : 'style="width: 180px;padding-left: 0px;"'); ?>>
                                  <select id="c_typetimeperiod2<?php echo $i + 1; ?>" name="c_typetimeperiod2[]" class="form-control input-md">
                                    <option value="">---</option>
                                    <option value="1" <?php echo ("1" == $c_typetimeperiod2x) ? 'selected="true"' : ''; ?>>Januari</option>
                                    <option value="2" <?php echo ("2" == $c_typetimeperiod2x) ? 'selected="true"' : ''; ?>>Pebruari</option>
                                    <option value="3" <?php echo ("3" == $c_typetimeperiod2x) ? 'selected="true"' : ''; ?>>Maret</option>
                                    <option value="4" <?php echo ("4" == $c_typetimeperiod2x) ? 'selected="true"' : ''; ?>>April</option>
                                    <option value="5" <?php echo ("5" == $c_typetimeperiod2x) ? 'selected="true"' : ''; ?>>Mei</option>
                                    <option value="6" <?php echo ("6" == $c_typetimeperiod2x) ? 'selected="true"' : ''; ?>>Juni</option>
                                    <option value="7" <?php echo ("7" == $c_typetimeperiod2x) ? 'selected="true"' : ''; ?>>Juli</option>
                                    <option value="8" <?php echo ("8" == $c_typetimeperiod2x) ? 'selected="true"' : ''; ?>>Agustus</option>
                                    <option value="9" <?php echo ("9" == $c_typetimeperiod2x) ? 'selected="true"' : ''; ?>>September</option>
                                    <option value="10" <?php echo ("10" == $c_typetimeperiod2x) ? 'selected="true"' : ''; ?>>Oktober</option>
                                    <option value="11" <?php echo ("11" == $c_typetimeperiod2x) ? 'selected="true"' : ''; ?>>Nopember</option>
                                    <option value="12" <?php echo ("12" == $c_typetimeperiod2x) ? 'selected="true"' : ''; ?>>Desember</option>
                                  </select>
                                  <input id="c_year2<?php echo $i + 1; ?>" name="c_year2[]" type="text" placeholder="Year" class="form-control input-md number datepickeryear" value="<?php echo $c_year2x; ?>">
                                </div>
                              </td>
                            </tr>

                            <tr class="row">
                              <td class="col-md-4">
                                <label class="col-md-6 control-label"></label>
                              </td>
                              <td class="col-md-8" colspan="2">
                                <label class="form-check-label"><input type="checkbox" id="c_work<?php echo $i + 1; ?>" name="c_work[]" data-id="<?php echo $i + 1; ?>" class="form-check-input" value="1" <?php echo set_checkbox('c_work[0]', '1', (isset($c_workx) ? ($c_workx == "1") ? TRUE : FALSE : FALSE)); ?>>Sampai saat ini</label>
                              </td>
                            </tr>


                            <tr class="row">
                              <td class="col-md-4">
                                <label class="col-md-8 control-label">Nama Aktifitas/Kegiatan/Proyek</label>
                              </td>
                              <td class="col-md-8" colspan="2">
                                <textarea class="form-control" rows="5" id="c_actv<?php echo $i + 1; ?>" name="c_actv[]"><?php echo $c_actvx; ?></textarea>
                              </td>
                            </tr>

                            <tr class="row">
                              <td class="col-md-4">
                                <label class="col-md-8 control-label">Uraian Singkat Tugas dan Tanggung Jawab Profesional</label>
                              </td>
                              <td class="col-md-8" colspan="2">
                                <textarea class="form-control" rows="5" id="c_desc<?php echo $i + 1; ?>" name="c_desc[]"><?php echo $c_descx; ?></textarea>
                              </td>
                            </tr>

                            <tr class="row">
                              <td class="col-md-4">
                                <label class="col-md-8 control-label">Dokumen pendukung <br /> <span class="red">(Max. 700KB, image atau PDF)</span></label>
                              </td>
                              <td class="col-md-8" colspan="2">
                                <div class="form-group">
                                  <div id="avatarattexp<?php echo $i + 1; ?>">
                                    <?php echo ($c_expattachmentx != '') ? "<a href='" . base_url() . "/assets/uploads/" . $c_expattachmentx . "' target='_blank' class='ava_discus'>" . $c_expattachmentx . "</a>" : ''; ?>
                                    <input type="hidden" name="exp_image_url[]" value="<?php echo $c_expattachmentx; ?>" style="display: inline-block;">
                                  </div>
                                  <div id="errUploadattexp<?php echo $i + 1; ?>" class="red"></div>
                                  <input type="file" name="attexp<?php echo $i + 1; ?>" class="form-control input-md" id="attexp<?php echo $i + 1; ?>" onchange="upload_exp('attexp<?php echo $i + 1; ?>')">
                                </div>
                              </td>
                            </tr>


                          </table>
                        </td>
                      </tr>
                <?php
                    }
                    $i++;
                  }
                }
                ?>


              </table>
              <div class="col-md-12">
                <button type="button" class="btn btn-primary" onclick="addExp('texp')">Tambah</button>
              </div>
              <label class="col-md-12 control-label"></label>
            </div>


            <div class="wizard-buttons" style="text-align:center;">
              <button type="button" class="btn btn-previous">Kembali</button>
              <button type="button" class="btn btn-next" id="editExp">Simpan & Lanjut</button>
            </div>
          </fieldset>
          <fieldset id="fi_4">
            <!-- <h4 style="padding:15px;"><b>Unggah</b></h4> -->

            <!-- UPLOAD KTP FORM -->
            <!-- <div class="col-md-12">
						<div class="col-sm-4 col-md-offset-4">
						<h4><b>Upload KTP (or PASSPORT)</b></h4>  <span class="red">(Ukuran Max. 700KB, Format file: gambar atau PDF)</span>
								<div class="form-group">
									<div id="avatar">
									<?php echo ($row->id_file != '') ? "<a href='" . base_url() . "/assets/uploads/" . $row->id_file . "' target='_blank' class='ava_discus'>" . $row->id_file . "</a>" : ''; ?>
									</div>
									<br /><br />
									<div id="errUpload" class="red"></div>
									<input type="file" name="ktp" id="ktp" onchange="upload_ktp()">
								</div>
						</div>
						
						<div class="col-sm-4 col-md-offset-4">
						<h4><b>Upload Photo</b></h4>  <span class="red">(Ukuran Max. 700KB, Format file: PNG atau JPG/JPEG)</span>
								<div class="form-group">
									<div id="avatar2">
									<?php //echo ($row->photo!='')?"<img src='".base_url()."/assets/uploads/".$row->photo."'  class='ava_discus' width='150'>":''; 
                  ?>
									
									<?php echo ($row->photo != '') ? "<a href='" . base_url() . "/assets/uploads/" . $row->photo . "' target='_blank' class='ava_discus'>" . $row->photo . "</a>" : ''; ?>
									
									</div>
									<br /><br />
									<div id="errUpload2" class="red"></div>
									<input type="file" name="photo" id="photo" onchange="upload_photo()">
								</div>
						</div>
						
					</div> -->

            <!-- NEW UI UPLOAD KTP & PHOTO -->
            <!-- <div class="">
              <div class="row">
                <!-- Upload KTP -->
            <div class="col-md-6 col-md-offset-3">
              <div class="panel panel-default">
                <div class="panel-heading text-center">
                  <h4><b>Upload KTP / Passport</b></h4>
                  <small class="text-danger">(Ukuran Max. 700KB, Format: Gambar atau PDF)</small>
                </div>
                <div class="panel-body text-center">
                  <div id="avatar" class="mb-10">
                    <?php echo ($row->id_file != '')
                      ? "<a href='" . base_url() . "/assets/uploads/" . $row->id_file . "' target='_blank' class='btn btn-link'>" . $row->id_file . "</a>"
                      : "<p class='text-muted'>Belum ada file diupload</p>"; ?>
                  </div>
                  <input type="file" class="form-control" name="ktp" id="ktp" onchange="previewFile(this, 'avatarPreview')">
                  <div id="errUpload" class="text-danger small mt-5"></div>

                  <!-- Preview KTP -->
                  <div id="avatarPreview" class="mt-10"></div>
                </div>
              </div>
            </div>

            <!-- Upload Photo -->
            <div class="col-md-6 col-md-offset-3">
              <div class="panel panel-default">
                <div class="panel-heading text-center">
                  <h4><b>Upload Photo</b></h4>
                  <small class="text-danger">(Ukuran Max. 700KB, Format: PNG / JPG / JPEG)</small>
                </div>
                <div class="panel-body text-center">
                  <div id="avatar2" class="mb-10">
                    <?php echo ($row->photo != '')
                      ? "<a href='" . base_url() . "/assets/uploads/" . $row->photo . "' target='_blank' class='btn btn-link'>" . $row->photo . "</a>"
                      : "<p class='text-muted'>Belum ada foto diupload</p>"; ?>
                  </div>
                  <input type="file" class="form-control" name="photo" id="photo" onchange="previewFile(this, 'photoPreview')">
                  <div id="errUpload2" class="text-danger small mt-5"></div>

                  <!-- Preview Photo -->
                  <div id="photoPreview" class="mt-10"></div>
                </div>
              </div>
            </div>
      </div>
  </div> -->

  <!-- SCRIPT ASLINYA -->
  <div class="col-md-12">
    <div class="col-sm-4 col-md-offset-4">
      <h4><b>Upload KTP (or PASSPORT)</b></h4> <span class="red">(Ukuran Max. 700KB, Format file: gambar atau PDF)</span>
      <div class="form-group">
        <div id="avatar"> <?php echo ($row->id_file != '') ? "<a href='" . base_url() . "/assets/uploads/" . $row->id_file . "' target='_blank' class='ava_discus'>" . $row->id_file . "</a>" : ''; ?> </div> <br /><br />
        <div id="errUpload" class="red"></div> <input type="file" name="ktp" id="ktp" onchange="upload_ktp()">
      </div>
    </div>
    <div class="col-sm-4 col-md-offset-4">
      <h4><b>Upload Photo</b></h4> <span class="red">(Ukuran Max. 700KB, Format file: PNG atau JPG/JPEG)</span>
      <div class="form-group">
        <div id="avatar2"> <?php //echo ($row->photo!='')?"<img src='".base_url()."/assets/uploads/".$row->photo."' class='ava_discus' width='150'>":''; 
                            ?> <?php echo ($row->photo != '') ? "<a href='" . base_url() . "/assets/uploads/" . $row->photo . "' target='_blank' class='ava_discus'>" . $row->photo . "</a>" : ''; ?> </div> <br /><br />
        <div id="errUpload2" class="red"></div> <input type="file" name="photo" id="photo" onchange="upload_photo()">
      </div>
    </div>
  </div>


  <!-- SCRIPT ASLI UI MODIFICATION -->
  <div class="container">
    <div class="row">
      <!-- Upload KTP -->
      <div class="col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
        <div class="panel panel-default">
          <div class="panel-heading text-center">
            <h4><b>Upload KTP (or PASSPORT)</b></h4>
            <small class="text-danger">(Ukuran Max. 700KB, Format file: gambar atau PDF)</small>
          </div>
          <div class="panel-body text-center">
            <div id="avatar">
              <?php echo ($row->id_file != '')
                ? "<a href='" . base_url() . "/assets/uploads/" . $row->id_file . "' target='_blank' class='btn btn-link'><i class='glyphicon glyphicon-file'></i> " . $row->id_file . "</a>"
                : '<p class="text-muted">Belum ada file diupload</p>'; ?>
            </div>
            <br />
            <div id="errUpload" class="text-danger"></div>
            <label class="btn btn-primary btn-file">
              <i class="glyphicon glyphicon-upload"></i> Pilih File
              <input type="file" name="ktp" id="ktp" onchange="upload_ktp()" style="display: none;">
            </label>
          </div>
        </div>
      </div>

      <!-- Upload Photo -->
      <div class="col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
        <div class="panel panel-default">
          <div class="panel-heading text-center">
            <h4><b>Upload Foto</b></h4>
            <small class="text-danger">(Ukuran Max. 700KB, Format file: PNG atau JPG/JPEG)</small>
          </div>
          <div class="panel-body text-center">
            <div id="avatar2">
              <?php echo ($row->photo != '')
                ? "<a href='" . base_url() . "/assets/uploads/" . $row->photo . "' target='_blank' class='btn btn-link'><i class='glyphicon glyphicon-picture'></i> " . $row->photo . "</a>"
                : '<p class="text-muted">Belum ada foto diupload</p>'; ?>
            </div>
            <br />
            <div id="errUpload2" class="text-danger"></div>
            <label class="btn btn-success btn-file">
              <i class="glyphicon glyphicon-camera"></i> Pilih Foto
              <input type="file" name="photo" id="photo" onchange="upload_photo()" style="display: none;">
            </label>
          </div>
        </div>
      </div>
    </div>
  </div>



  <div class="wizard-buttons" style="text-align:center;">
    <button type="button" class="btn btn-previous">Kembali</button>
    <button type="button" class="btn btn-next">Simpan & Lanjut</button>
  </div>
  </fieldset>
  <fieldset id="fi_5">
    <div class="jumbotron text-center">
      <h1>Terima kasih!</h1>
      <h4>Proses pemutakhiran data telah selesai. Kapan saja silakan login kembali dan update data anda.</h4>

      <!--Ada Kesulitan? Contact us-->
    </div>
    <div class="wizard-buttons" style="text-align:center;">
      <button type="button" class="btn btn-previous">Kembali</button>
      <button type="button" name="home" onclick="return tohome();" class="btn btn-primary">Display Profile</button>
    </div>
  </fieldset>

  </form>
  </div>

  <script src="<?php echo base_url(); ?>assets/ada/wizard/js/jquery.min.js"></script>
  <script src="<?php echo base_url(); ?>assets/ada/wizard/js/popper.min.js"></script>
  <script src="<?php echo base_url(); ?>assets/ada/wizard/js/bootstrap.min.js"></script>
  <script src="<?php echo base_url(); ?>assets/ada/wizard/script.js"></script>



  </aside>
  <!-- /.right-side -->
  <?php $this->load->view('member/common/footer'); ?>




  <script src="<?php echo base_url(); ?>assets/js/typeahead.bundle.min.js"></script>
  <script>
    function addAddress(tableID) {
      var currentNumber = 1;
      if ($('.address-item').length > 0) {
        currentNumber = $('.address-item').last().data('id') + 1
      }

      $('#' + tableID).append(
        '<tr class="row address-item" data-id="' + currentNumber + '" >' +
        '<td><table class="table" style="margin-left:5px;">' +
        '<tr class="row">' +
        '<td class="col-md-4">' +
        '<select id="typeaddress' + currentNumber + '" name="typeaddress[]" class="form-control input-md" required="">' +
        '<option value="">--Choose--</option>'
        <?php
        if (isset($m_address)) {
          foreach ($m_address as $val) {
        ?> +
            '<option value="<?php echo $val->id; ?>" ><?php echo $val->desc; ?></option>'

        <?php
          }
        }
        ?> +
        '</select><input type="hidden" name="addressid[]" id="addressid' + currentNumber + '" value="" />' +
        '</td>' +
        '<td class="col-md-8">' +
        '<input id="address' + currentNumber + '" name="address[]" type="text" placeholder="Address" class="form-control input-md" required="">' +
        '</td>' +
        '<td class="td-action">' +
        '<button type="button" class="btn btn-danger btn-xs address-item-remove-button" data-id="' + currentNumber + '">' +
        '<i class="fa fa-trash-o fa-fw"></i>X' +
        '</button>' +
        '</td>' +
        '</tr>' +
        '<tr class="row">' +
        '<td class="col-md-4">' +
        '<label class="form-check-label"><input type="radio" name="mailingaddr" value="' + currentNumber + '">Alamat Surat</label>' +
        '</td>' +
        '<td class="col-md-8">' +
        '<input id="addresscity' + currentNumber + '" name="addresscity[]" type="text" placeholder="City" class="form-control input-md w400"  required="">' +
        '</td>' +
        '</tr>' +
        '<tr class="row">' +
        '<td class="col-md-4">' +
        '</td>' +
        '<td class="col-md-8">' +
        '<input id="addressprovince' + currentNumber + '" name="addressprovince[]" type="text" placeholder="Province" class="form-control input-md w400"  required="">' +
        '</td>' +
        '</tr>' +
        '<tr class="row">' +
        '<td class="col-md-4">' +
        '</td>' +
        '<td class="col-md-8">' +
        '<input id="addresszip' + currentNumber + '" name="addresszip[]" type="text" placeholder="Zip Code" class="form-control input-md number w400" required="" >' +
        '</td>' +
        '</tr>' +
        '<tr class="row">' +
        '<td class="col-md-4">' +
        '</td>' +
        '<td class="col-md-8">' +
        '<input id="email' + currentNumber + '" name="email[]" type="email" placeholder="Email" class="form-control input-md">' +
        '</td>' +
        '</tr>' +
        '<tr class="row">' +
        '<td class="col-md-4">' +
        '</td>' +
        '<td class="col-md-8">' +
        '<input id="addressphone' + currentNumber + '" name="addressphone[]" type="text" placeholder="Contoh +627911123456" class="form-control input-md number w400" >' +
        '</td>' +
        '</tr>' +
        '</table></td></tr>'
      );

      var regions = new Bloodhound({
        datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        remote: {
          cache: false,
          url: '<?php echo base_url(); ?>/welcome/searchregion?q=%QUERY%',
          wildcard: '%QUERY%',
          filter: function(list) {
            return $.map(list, function(company) {
              return {
                name: company.name
              };
            });
          }
        }
      });
      var provinces = new Bloodhound({
        datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        remote: {
          cache: false,
          url: '<?php echo base_url(); ?>/welcome/searchprovince?q=%QUERY%',
          wildcard: '%QUERY%',
          filter: function(list) {
            return $.map(list, function(company) {
              return {
                name: company.name
              };
            });
          }
        }
      });
      $('#' + tableID).find("input[id^='addresscity']").typeahead(null, {
        name: 'regions',
        display: 'name',
        source: regions,
        hint: true,
        highlight: true,
        minLength: 2,
        limit: Infinity,
        templates: {}
      });
      $('#' + tableID).find("input[id^='addressprovince']").typeahead(null, {
        name: 'provinces',
        display: 'name',
        source: provinces,
        hint: true,
        highlight: true,
        minLength: 2,
        limit: Infinity,
        templates: {}
      });

      var input = document.querySelector("#addressphone" + currentNumber);
      window.intlTelInput(input, {
        autoHideDialCode: true,
        nationalMode: false,
        preferredCountries: ['id'],
        utilsScript: "<?php echo base_url(); ?>assets/ada/phone/js/utils.js",
      });

      document.getElementById('addressphone' + currentNumber).addEventListener('input', function() {
        let start = this.selectionStart;
        let end = this.selectionEnd;

        const current = this.value
        const corrected = current.replace(/[^-+\d]/g, '');
        this.value = corrected;

        if (corrected.length < current.length) --end;
        this.setSelectionRange(start, end);
      });

    }

    function addSchool(tableID) {
      var currentNumber = 1;
      if ($('.school-item').length > 0) {
        currentNumber = $('.school-item').last().data('id') + 1
      }

      $('#' + tableID).append(
        '<tr class="row school-item" data-id="' + currentNumber + '" >' +
        '<td><table class="table" border="1" style="margin-left:5px;">'


        +
        '<tr class="row">' +
        '	<td class="col-md-4">' +
        '		<label class="col-md-6 control-label">Tipe Pendidikan<span class="red"> *</span></label>  ' +
        '	</td>' +
        '	<td class="col-md-8">' +
        '	<select id="c_school_type' + currentNumber + '" name="c_school_type[]" class="form-control input-md" required="" onchange="tipe_school(' + currentNumber + ')">' +
        '<option value="">--Choose--</option>'
        <?php
        if (isset($m_school_type)) {
          foreach ($m_school_type as $val) {
        ?> +
            '<option value="<?php echo $val->id; ?>" ><?php echo $val->desc; ?></option>'

        <?php
          }
        }
        ?> +
        '	</select>' +
        '	</td>' +
        '<td class="td-action">' +
        '<button type="button" class="btn btn-danger btn-xs school-item-remove-button" data-id="' + currentNumber + '">' +
        '<i class="fa fa-trash-o fa-fw"></i>X' +
        '</button>' +
        '</td>' +
        '</tr>' +
        '<tr class="row">' +
        '	<td class="col-md-4">' +
        '		<label class="col-md-8 control-label">Institusi / Universitas<span class="red"> *</span></label> ' +
        '	</td>' +
        '	<td class="col-md-6" colspan="2">' +
        '	<input id="c_school' + currentNumber + '" name="c_school[]" type="text" placeholder="School" class="form-control input-md" required="">' +
        ' <input type="hidden" name="schoolid[]" id="schoolid' + currentNumber + '" value="" />' +
        '	</td>' +
        '</tr>' +
        '<tr class="row">' +
        '	<td class="col-md-4">' +
        '		<label class="col-md-6 control-label" id="label_c_tahun' + currentNumber + '">Tahun<span class="red"> *</span></label>  ' +
        '	</td>' +
        '	<td class="col-md-6" colspan="2">'

        +
        '<input style="width:28%;float:left;" id="c_dateattendstart' + currentNumber + '" name="c_dateattendstart[]" type="text" placeholder="Year" class="form-control input-md datepickeryear" required="">' +
        '<div style="float:left;padding-left:2%;padding-right:2%;">-</div>' +
        '<input style="width:27.5%;float:left;" id="c_dateattendend' + currentNumber + '" name="c_dateattendend[]" type="text" placeholder="Year" class="form-control input-md datepickeryear" required="">'

        +
        '	</td>' +
        '</tr>' +
        '<tr class="row">' +
        '	<td class="col-md-4">' +
        '		<label class="col-md-8 control-label" id="label_c_tingkat' + currentNumber + '">Tingkat Pendidikan<span class="red"> *</span></label>  ' +
        '	</td>' +
        '	<td class="col-md-8" colspan="2">' +
        '	<select id="c_degree' + currentNumber + '" name="c_degree[]" class="form-control input-md" required="">' +
        '<option value="">--Choose--</option>'
        /*<?php
          if (isset($m_degree)) {
            foreach ($m_degree as $val) {
          ?>
        + '<option value="<?php echo $val->EDUCATION_TYPE_ID; ?>" ><?php echo $val->DESCRIPTION; ?></option>'
        
        <?php
            }
          }
        ?>*/
        +
        '	</select>' +
        '	</td>' +
        '</tr>'
        /*+ '<tr class="row">'
        + '	<td class="col-md-4">'
        + '		<label class="col-md-6 control-label">Jurusan<span class="red"> *</span></label> '
        + '	</td>'
        + '	<td class="col-md-8">'
        + '	<input id="c_mayor' + currentNumber + '" name="c_mayor[]" type="text" placeholder="Jurusan" class="form-control input-md" required="">'
        + '	</td>'
        + '</tr>'*/
        +
        '<tr class="row">' +
        '	<td class="col-md-4">' +
        '		<label class="col-md-6 control-label" id="label_c_fos' + currentNumber + '">Jurusan/Kejuruan<span class="red"> *</span></label> ' +
        '	</td>' +
        '	<td class="col-md-8" colspan="2">' +
        '	<input id="c_fos' + currentNumber + '" name="c_fos[]" type="text" placeholder="" class="form-control input-md" required="">' +
        '	</td>' +
        '</tr>' +
        '<tr class="row">' +
        '	<td class="col-md-4">' +
        '		<label class="col-md-3 control-label" id="label_c_score' + currentNumber + '">IPK<span class="red"> *</span></label>' +
        '	</td>' +
        '	<td class="col-md-8" colspan="2">' +
        '	<input id="c_score' + currentNumber + '" name="c_score[]" type="text" placeholder="" class="form-control input-md" required="">' +
        '	</td>' +
        '</tr>' +
        '<tr class="row">' +
        '	<td class="col-md-4">' +
        '		<label class="col-md-3 control-label">Gelar<span class="red"> *</span></label>  ' +
        '	</td>' +
        '	<td class="col-md-8" colspan="2">'
        //+ '	<input id="c_title' + currentNumber + '" name="c_title[]" type="text" placeholder="Gelar" class="form-control input-md w400" required="" >'

        +
        '<div style="width:25%;float:left;">' +
        '	<b>Gelar depan</b> (contoh: Drs.)<br />' +
        '	<input id="c_prefix_title' + currentNumber + '" name="c_prefix_title[]" type="text" placeholder="Gelar Depan" ' +
        ' class="form-control input-md w400" value="" style="width:100%">' +
        '</div>' +
        '<div style="width:3%;float:left;text-align:center;">' +
        '	/ ' +
        '</div>' +
        '<div style="width:32%;float:left;">' +
        '	<b>Gelar belakang</b> (contoh:  M.M.)<br />' +
        '	<input id="c_title' + currentNumber + '" name="c_title[]" type="text" placeholder="Gelar Belakang" class="form-control input-md w400" value="" style="width:100%">' +
        '</div>'


        +
        '	</td>' +
        '</tr>' +
        '<tr class="row">' +
        '	<td class="col-md-4">' +
        '		<label class="col-md-8 control-label">Judul Tugas Akhir/Skripsi/Tesis/Disertasi</label>    ' +
        '	</td>' +
        '	<td class="col-md-8" colspan="2">' +
        '	<input id="c_actv' + currentNumber + '" name="c_actv[]" type="text" placeholder="Judul Tugas Akhir/Skripsi/Tesis/Disertasi" class="form-control input-md" >' +
        '	</td>' +
        '</tr>' +
        '<tr class="row">' +
        '	<td class="col-md-4">' +
        '		<label class="col-md-8 control-label">Uraian Singkat Tentang Materi Tugas Akhir/ Skripsi/Tesis/ Disertasi</label>  ' +
        '	</td>' +
        '	<td class="col-md-8" colspan="2">' +
        '		<textarea class="form-control" rows="5" id="c_descedu' + currentNumber + '" name="c_descedu[]"></textarea>' +
        '	</td>' +
        '</tr>'

        +
        '<tr class="row">' +
        '	<td class="col-md-4">' +
        '		<label class="col-md-8 control-label">Dokumen pendukung <br /> <span class="red">(Max. 700KB, image atau PDF)</span></label>  ' +
        '	</td>' +
        '	<td class="col-md-8" colspan="2">' +
        '		<div class="form-group">' +
        '			<div id="avatarattedu' + currentNumber + '">' +
        '			<input type="hidden" name="edu_image_url[]" value="" style="display: inline-block;">'

        +
        '			</div>' +
        '			<div id="errUploadattedu' + currentNumber + '" class="red"></div>' +
        '			<input type="file" name="attedu' + currentNumber + '" class="form-control input-md" id="attedu' + currentNumber + '" onchange="upload_edu(\'attedu' + currentNumber + '\')">' +
        '		</div>' +
        '	</td>' +
        '</tr>'


        +
        '</table></td></tr>'
      );

      /*var titles = new Bloodhound({
			datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
			queryTokenizer: Bloodhound.tokenizers.whitespace,
			remote: {
				cache: false,
				url: '<?php echo base_url(); ?>/welcome/searchtitle?q=%QUERY%',
				wildcard: '%QUERY%',
				filter: function(list) {
					return $.map(list, function(company) {
						return { name: company.name };
					});
				}
			}
		});
		
		
		$('#'+tableID).find("input[id^='c_title']").typeahead(null, {
            name: 'titles',
            display: 'name',
            source: titles,
            hint: true,
			highlight: true,
			minLength: 2,
			limit: Infinity,
            templates: {
            }
        });*/


      $('#' + tableID).find("input[id^='c_title']").autocomplete({
        source: function(request, response) {
          var x = $(this.element).prop("id");
          x = x.replace("c_title", "");
          $.ajax({
            url: '<?php echo base_url(); ?>/welcome/searchtitlev2_s',
            dataType: "JSON",
            data: {
              term: request.term,
              edu: $("#c_degree" + x).val()
            },
            success: function(data) {
              response(data);
            }
          });
        },
        minLength: 0,
        select: function(event, ui) {
          //log( "Selected: " + ui.item.value + " aka " + ui.item.id );
        }
      });

      $('#' + tableID).find("input[id^='c_prefix_title']").autocomplete({
        source: function(request, response) {
          var x = $(this.element).prop("id");
          x = x.replace("c_prefix_title", "");
          $.ajax({
            url: '<?php echo base_url(); ?>/welcome/searchtitlev2_p',
            dataType: "JSON",
            data: {
              term: request.term,
              edu: $("#c_degree" + x).val()
            },
            success: function(data) {
              response(data);
            }
          });
        },
        minLength: 0,
        select: function(event, ui) {
          //log( "Selected: " + ui.item.value + " aka " + ui.item.id );
        }
      });

      $('#' + tableID).find("select[id^='c_degree']").change(function(e) {
        var c = ($(this).attr('id'));
        c = c.replace("c_degree", "");

        $("#c_title" + c).val('');
        $("#c_prefix_title" + c).val('');
      });

    }

    function addCert(tableID) {
      var currentNumber = 1;
      if ($('.cert-item').length > 0) {
        currentNumber = $('.cert-item').last().data('id') + 1
      }

      $('#' + tableID).append(
        '<tr class="row cert-item" data-id="' + currentNumber + '" >' +
        '<td><table class="table" border="1" style="margin-left:5px;">'



        +
        '<tr class="row">' +
        '	<td class="col-md-4">' +
        '		<label class="col-md-6 control-label">Nama Sertifikasi<span class="red"> *</span></label> ' +
        '	</td>' +
        '	<td class="col-md-6">' +
        '	<input id="c_certname' + currentNumber + '" name="c_certname[]" type="text" placeholder="Nama Sertifikasi" class="form-control input-md" required="">' +
        '<input type="hidden" name="certid[]" id="certid' + currentNumber + '" value="" />' +
        '	</td>' +
        '<td class="td-action">' +
        '<button type="button" class="btn btn-danger btn-xs cert-item-remove-button" data-id="' + currentNumber + '">' +
        '<i class="fa fa-trash-o fa-fw"></i>X' +
        '</button>' +
        '</td>' +
        '</tr>' +
        '<tr class="row">' +
        '	<td class="col-md-4">' +
        '		<label class="col-md-6 control-label">Otoritas Sertifikasi<span class="red"> *</span></label> ' +
        '	</td>' +
        '	<td class="col-md-8" colspan="2">' +
        '	<input id="c_certauth' + currentNumber + '" name="c_certauth[]" type="text" placeholder="Otoritas Sertifikasi" class="form-control input-md" required="">' +
        '	</td>' +
        '</tr>' +
        '<tr class="row">' +
        '	<td class="col-md-4">' +
        '		<label class="col-md-6 control-label">Nomor lisensi<span class="red"> *</span></label>' +
        '	</td>' +
        '	<td class="col-md-8" colspan="2">' +
        '	<input id="c_lic' + currentNumber + '" name="c_lic[]" type="text" placeholder="Nomor lisensi" class="form-control input-md" required="">' +
        '	</td>' +
        '</tr>' +
        '<tr class="row">' +
        '	<td class="col-md-4">' +
        '		<label class="col-md-6 control-label">URL sertifikasi</label>  ' +
        '	</td>' +
        '	<td class="col-md-8" colspan="2">' +
        '	<input id="c_url' + currentNumber + '" name="c_url[]" type="text" placeholder="URL sertifikasi" class="form-control input-md" >' +
        '	</td>' +
        '</tr>'

        +
        '<tr class="row">' +
        '	<td class="col-md-4">' +
        '		<label class="col-md-3 control-label">Kualifikasi<span class="red"> *</span></label>  ' +
        '	</td>' +
        '	<td class="col-md-8" colspan="2">' +
        '	<!--<b>Kualifikasi</b> (contoh: IPM.)<br />-->' +
        '	<input id="c_cert_title' + currentNumber + '" name="c_cert_title[]" type="text" placeholder="Kualifikasi" class="form-control input-md w400" required=""  value="">' +
        '	</td>' +
        '</tr>'

        +
        '<tr class="row">' +
        '	<td class="col-md-4">' +
        '		<label class="col-md-6 control-label">Tanggal<span class="red"> *</span></label>  ' +
        '	</td>' +
        '	<td class="col-md-6" colspan="2">' +
        '	<div class="col-md-3" style="width: 180px;padding-left: 0px;">' +
        '	<select id="c_certdate' + currentNumber + '" name="c_certdate[]" class="form-control input-md" required="">' +
        '		<option value="">---</option>' +
        '		<option value="1">Januari</option>' +
        '		<option value="2">Pebruari</option>' +
        '		<option value="3">Maret</option>' +
        '		<option value="4">April</option>' +
        '		<option value="5">Mei</option>' +
        '		<option value="6">Juni</option>' +
        '		<option value="7">Juli</option>' +
        '		<option value="8">Agustus</option>' +
        '		<option value="9">September</option>' +
        '		<option value="10">Oktober</option>' +
        '		<option value="11">Nopember</option>' +
        '		<option value="12">Desember</option>' +
        '	</select>' +
        '	<input id="c_certyear' + currentNumber + '" name="c_certyear[]" type="text" placeholder="Year" class="form-control input-md number datepickeryear" required="">' +
        '	</div>' +
        '	<div class="col-md-1" id="is_presentcert1' + currentNumber + '">-</div>' +
        '	<div class="col-md-3" id="is_presentcert2' + currentNumber + '" style="width: 180px;padding-left: 0px;">' +
        '	<select id="c_certdate2' + currentNumber + '" name="c_certdate2[]" class="form-control input-md">' +
        '		<option value="">---</option>' +
        '		<option value="1">Januari</option>' +
        '		<option value="2">Pebruari</option>' +
        '		<option value="3">Maret</option>' +
        '		<option value="4">April</option>' +
        '		<option value="5">Mei</option>' +
        '		<option value="6">Juni</option>' +
        '		<option value="7">Juli</option>' +
        '		<option value="8">Agustus</option>' +
        '		<option value="9">September</option>' +
        '		<option value="10">Oktober</option>' +
        '		<option value="11">Nopember</option>' +
        '		<option value="12">Desember</option>' +
        '	</select>' +
        '	<input id="c_certyear2' + currentNumber + '" name="c_certyear2[]" type="text" placeholder="Year" class="form-control input-md number datepickeryear" >' +
        '	</div>' +
        '	</td>' +
        '</tr>' +
        '<tr class="row">' +
        '	<td class="col-md-4">' +
        '		<label class="col-md-6 control-label"></label>  ' +
        '	</td>' +
        '	<td class="col-md-8" colspan="2">' +
        '	<label class="form-check-label"><input type="checkbox" id="c_certwork' + currentNumber + '" name="c_certwork[]" class="form-check-input" value="1" data-id="' + currentNumber + '">Sampai saat ini</label>' +
        '	</td>' +
        '</tr>' +
        '<tr class="row">' +
        '	<td class="col-md-4">' +
        '		<label class="col-md-6 control-label">Deskripsi</label>  ' +
        '	</td>' +
        '	<td class="col-md-8" colspan="2">' +
        '		<textarea class="form-control" rows="5" id="c_certdesc' + currentNumber + '" name="c_certdesc[]"></textarea>' +
        '	</td>' +
        '</tr>'

        +
        '<tr class="row">' +
        '	<td class="col-md-4">' +
        '		<label class="col-md-8 control-label">Dokumen pendukung <br /> <span class="red">(Max. 700KB, image atau PDF)</span></label>  ' +
        '	</td>' +
        '	<td class="col-md-8" colspan="2">' +
        '		<div class="form-group">' +
        '			<div id="avatarattcert' + currentNumber + '">' +
        '			<input type="hidden" name="cert_image_url[]" value="" style="display: inline-block;">'

        +
        '			</div>' +
        '			<div id="errUploadattcert' + currentNumber + '" class="red"></div>' +
        '			<input type="file" name="attcert' + currentNumber + '" class="form-control input-md" id="attcert' + currentNumber + '" onchange="upload_cert(\'attcert' + currentNumber + '\')">' +
        '		</div>' +
        '	</td>' +
        '</tr>'

        +
        '</table></td></tr>'
      );

      var titles = new Bloodhound({
        datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        remote: {
          cache: false,
          url: '<?php echo base_url(); ?>/welcome/searchtitle?q=%QUERY%',
          wildcard: '%QUERY%',
          filter: function(list) {
            return $.map(list, function(company) {
              return {
                name: company.name
              };
            });
          }
        }
      });


      $('#' + tableID).find("input[id^='c_title']").typeahead(null, {
        name: 'titles',
        display: 'name',
        source: titles,
        hint: true,
        highlight: true,
        minLength: 2,
        limit: Infinity,
        templates: {}
      });


      /*$('#'+tableID).find("input[id^='c_cert_title']").autocomplete({
      	source: function( request, response ) {
      	var x = $(this.element).prop("id");
      	x = x.replace("c_cert_title", "");
      	$.ajax( {
      	  url: '<?php echo base_url(); ?>/welcome/searchtitlev3',
      	  dataType: "JSON",
      	  data: {
      		term: request.term
      	  },
      	  success: function( data ) {
      	    response( data );
      	  }
      	} );},
      	minLength: 0,
      	select: function( event, ui ) {
      		//log( "Selected: " + ui.item.value + " aka " + ui.item.id );
      	}
      });*/

    }

    function addExp(tableID) {
      var currentNumber = 1;
      if ($('.exp-item').length > 0) {
        currentNumber = $('.exp-item').last().data('id') + 1
      }

      $('#' + tableID).append(
        '<tr class="row exp-item" data-id="' + currentNumber + '" >' +
        '<td><table class="table" border="1" style="margin-left:5px;">'



        +
        '<tr class="row">' +
        '	<td class="col-md-4">' +
        '		<label class="col-md-6 control-label">Perusahaan<span class="red"> *</span></label> ' +
        '	</td>' +
        '	<td class="col-md-6">' +
        '	<input id="c_company' + currentNumber + '" name="c_company[]" type="text" placeholder="Perusahaan" class="form-control input-md" required="">' +
        ' <input type="hidden" name="expid[]" id="expid' + currentNumber + '" value="" />' +
        '	</td>' +
        '<td class="td-action">' +
        '<button type="button" class="btn btn-danger btn-xs exp-item-remove-button" data-id="' + currentNumber + '">' +
        '<i class="fa fa-trash-o fa-fw"></i>X' +
        '</button>' +
        '</td>' +
        '</tr>' +
        '<tr class="row">' +
        '	<td class="col-md-4">' +
        '		<label class="col-md-6 control-label">Jabatan/Tugas<span class="red"> *</span></label> ' +
        '	</td>' +
        '	<td class="col-md-8" colspan="2">' +
        '	<input id="c_exptitle' + currentNumber + '" name="c_exptitle[]" type="text" placeholder="Jabatan/Tugas" class="form-control input-md" required="">' +
        '	</td>' +
        '</tr>' +
        '<tr class="row">' +
        '	<td class="col-md-4">' +
        '		<label class="col-md-6 control-label">Kabupaten/Kota<span class="red"> *</span></label>' +
        '	</td>' +
        '	<td class="col-md-8" colspan="2">' +
        '	<input id="c_loc' + currentNumber + '" name="c_loc[]" type="text" placeholder="Lokasi" class="form-control input-md w400" required="" >' +
        '	</td>' +
        '</tr>' +
        '<tr class="row">' +
        '	<td class="col-md-4">' +
        '		<label class="col-md-6 control-label">Provinsi<span class="red"> *</span></label>' +
        '	</td>' +
        '	<td class="col-md-8" colspan="2">' +
        '	<input id="c_provinsi' + currentNumber + '" name="c_provinsi[]" type="text" placeholder="Provinsi" class="form-control input-md w400" required="" >' +
        '	</td>' +
        '</tr>' +
        '<tr class="row">' +
        '	<td class="col-md-4">' +
        '		<label class="col-md-6 control-label">Negara<span class="red"> *</span></label>' +
        '	</td>' +
        '	<td class="col-md-8" colspan="2">' +
        '	<input id="c_negara' + currentNumber + '" name="c_negara[]" type="text" placeholder="Negara" class="form-control input-md w400" required="" >' +
        '	</td>' +
        '</tr>' +
        '<tr class="row">' +
        '	<td class="col-md-4">' +
        '		<label class="col-md-6 control-label">Periode<span class="red"> *</span></label>  ' +
        '	</td>' +
        '	<td class="col-md-6" colspan="2">' +
        '	<div class="col-md-3"  style="width: 180px;padding-left: 0px;">' +
        '	<select id="c_typetimeperiod' + currentNumber + '" name="c_typetimeperiod[]" class="form-control input-md" required="">' +
        '		<option value="">---</option>' +
        '		<option value="1">Januari</option>' +
        '		<option value="2">Pebruari</option>' +
        '		<option value="3">Maret</option>' +
        '		<option value="4">April</option>' +
        '		<option value="5">Mei</option>' +
        '		<option value="6">Juni</option>' +
        '		<option value="7">Juli</option>' +
        '		<option value="8">Agustus</option>' +
        '		<option value="9">September</option>' +
        '		<option value="10">Oktober</option>' +
        '		<option value="11">Nopember</option>' +
        '		<option value="12">Desember</option>' +
        '	</select>' +
        '	<input id="c_year' + currentNumber + '" name="c_year[]" type="text" placeholder="Year" class="form-control input-md number datepickeryear" required="">' +
        '	</div>' +
        '	<div class="col-md-1" id="is_presentwork1' + currentNumber + '">-</div>' +
        '	<div class="col-md-3"  style="width: 180px;padding-left: 0px;" id="is_presentwork2' + currentNumber + '">' +
        '	<select id="c_typetimeperiod2' + currentNumber + '" name="c_typetimeperiod2[]" class="form-control input-md">' +
        '		<option value="">---</option>' +
        '		<option value="1">Januari</option>' +
        '		<option value="2">Pebruari</option>' +
        '		<option value="3">Maret</option>' +
        '		<option value="4">April</option>' +
        '		<option value="5">Mei</option>' +
        '		<option value="6">Juni</option>' +
        '		<option value="7">Juli</option>' +
        '		<option value="8">Agustus</option>' +
        '		<option value="9">September</option>' +
        '		<option value="10">Oktober</option>' +
        '		<option value="11">Nopember</option>' +
        '		<option value="12">Desember</option>' +
        '	</select>' +
        '	<input id="c_year2' + currentNumber + '" name="c_year2[]" type="text" placeholder="Year" class="form-control input-md number datepickeryear" >' +
        '	</div>' +
        '	</td>' +
        '</tr>' +
        '<tr class="row">' +
        '	<td class="col-md-4">' +
        '		<label class="col-md-6 control-label"></label>  ' +
        '	</td>' +
        '	<td class="col-md-8" colspan="2">' +
        '	<label class="form-check-label"><input type="checkbox" id="c_work' + currentNumber + '" name="c_work[]" class="form-check-input" value="1" data-id="' + currentNumber + '">Sampai saat ini</label>' +
        '	</td>' +
        '</tr>' +
        '<tr class="row">' +
        '	<td class="col-md-4">' +
        '		<label class="col-md-8 control-label">Nama Aktifitas/Kegiatan/Proyek</label>  ' +
        '	</td>' +
        '	<td class="col-md-8" colspan="2">' +
        '		<textarea class="form-control" rows="5" id="c_actv' + currentNumber + '" name="c_actv[]"></textarea>' +
        '	</td>' +
        '</tr>' +
        '<tr class="row">' +
        '	<td class="col-md-4">' +
        '		<label class="col-md-8 control-label">Uraian Singkat Tugas dan Tanggung Jawab Profesional</label>  ' +
        '	</td>' +
        '	<td class="col-md-8" colspan="2">' +
        '		<textarea class="form-control" rows="5" id="c_desc' + currentNumber + '" name="c_desc[]"></textarea>' +
        '	</td>' +
        '</tr>'

        +
        '<tr class="row">' +
        '	<td class="col-md-4">' +
        '		<label class="col-md-8 control-label">Dokumen pendukung <br /> <span class="red">(Max. 700KB, image atau PDF)</span></label>  ' +
        '	</td>' +
        '	<td class="col-md-8" colspan="2">' +
        '		<div class="form-group">' +
        '			<div id="avatarattexp' + currentNumber + '">' +
        '			<input type="hidden" name="exp_image_url[]" value="" style="display: inline-block;">'

        +
        '			</div>' +
        '			<div id="errUploadattexp' + currentNumber + '" class="red"></div>' +
        '			<input type="file" name="attexp' + currentNumber + '" class="form-control input-md" id="attexp' + currentNumber + '" onchange="upload_exp(\'attexp' + currentNumber + '\')">' +
        '		</div>' +
        '	</td>' +
        '</tr>'

        +
        '</table></td></tr>'
      );

      var titles = new Bloodhound({
        datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        remote: {
          cache: false,
          url: '<?php echo base_url(); ?>/welcome/searchtitle?q=%QUERY%',
          wildcard: '%QUERY%',
          filter: function(list) {
            return $.map(list, function(company) {
              return {
                name: company.name
              };
            });
          }
        }
      });


      $('#' + tableID).find("input[id^='c_title']").typeahead(null, {
        name: 'titles',
        display: 'name',
        source: titles,
        hint: true,
        highlight: true,
        minLength: 2,
        limit: Infinity,
        templates: {}
      });







      var regions = new Bloodhound({
        datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        remote: {
          cache: false,
          url: '<?php echo base_url(); ?>/welcome/searchregion?q=%QUERY%',
          wildcard: '%QUERY%',
          filter: function(list) {
            return $.map(list, function(company) {
              return {
                name: company.name
              };
            });
          }
        }
      });
      $('#' + tableID).find("input[id^='c_loc']").typeahead(null, {
        name: 'regions',
        display: 'name',
        source: regions,
        hint: true,
        highlight: true,
        minLength: 2,
        limit: Infinity,
        templates: {}
      });




    }

    function addEmail(tableID) {
      var currentNumber = 1;
      if ($('.email-item').length > 0) {
        currentNumber = $('.email-item').last().data('id') + 1
      }

      $('#' + tableID).append(
        '<tr class="row email-item" data-id="' + currentNumber + '" >' +
        '<td><table class="table" style="margin-left:5px;">' +
        '<tr class="row">' +
        '<td class="col-md-4">' +
        '<select id="typeemail' + currentNumber + '" name="typeemail[]" class="form-control input-md">' +
        '<option value="">--Choose--</option>'
        <?php
        if (isset($m_email)) {
          foreach ($m_email as $val) {
        ?> +
            '<option value="<?php echo $val->type; ?>" ><?php echo $val->desc; ?></option>'

        <?php
          }
        }
        ?> +
        '</select><input type="hidden" name="emailid[]" id="emailid' + currentNumber + '" value="" />' +
        '</td>' +
        '<td class="col-md-8">' +
        '<input id="emailm' + currentNumber + '" name="emailm[]" type="text" placeholder="Email" class="form-control input-md">' +
        '</td>' +
        '<td class="td-action">' +
        '<button type="button" class="btn btn-danger btn-xs email-item-remove-button" data-id="' + currentNumber + '">' +
        '<i class="fa fa-trash-o fa-fw"></i>X' +
        '</button>' +
        '</td>' +
        '</tr>' +
        '</table></td></tr>'
      );



    }

    function addPhone(tableID) {
      var currentNumber = 1;
      if ($('.phone-item').length > 0) {
        currentNumber = $('.phone-item').last().data('id') + 1
      }

      $('#' + tableID).append(
        '<tr class="row phone-item" data-id="' + currentNumber + '" >' +
        '<td><table class="table" style="margin-left:5px;">' +
        '<tr class="row">' +
        '<td class="col-md-4">' +
        '<select id="typephone' + currentNumber + '" name="typephone[]" class="form-control input-md">' +
        '<option value="">--Choose--</option>'
        <?php
        if (isset($m_phone)) {
          foreach ($m_phone as $val) {
        ?> +
            '<option value="<?php echo $val->type; ?>" ><?php echo $val->desc; ?></option>'

        <?php
          }
        }
        ?> +
        '</select><input type="hidden" name="phoneid[]" id="phoneid' + currentNumber + '" value="" />' +
        '</td>' +
        '<td class="col-md-8">' +
        '<input id="phonem' + currentNumber + '" name="phonem[]" type="text" placeholder="Contoh +627911123456" class="form-control input-md w400" >' +
        '</td>' +
        '<td class="td-action">' +
        '<button type="button" class="btn btn-danger btn-xs phone-item-remove-button" data-id="' + currentNumber + '">' +
        '<i class="fa fa-trash-o fa-fw"></i>X' +
        '</button>' +
        '</td>' +
        '</tr>' +
        '</table></td></tr>'
      );

      var input = document.querySelector("#phonem" + currentNumber);
      window.intlTelInput(input, {
        autoHideDialCode: true,
        nationalMode: false,
        preferredCountries: ['id'],
        utilsScript: "<?php echo base_url(); ?>assets/ada/phone/js/utils.js",
      });

      document.getElementById('phonem' + currentNumber).addEventListener('input', function() {
        let start = this.selectionStart;
        let end = this.selectionEnd;

        const current = this.value
        const corrected = current.replace(/[^-+\d]/g, '');
        this.value = corrected;

        if (corrected.length < current.length) --end;
        this.setSelectionRange(start, end);
      });

    }

    function tohome() {
      window.location.href = '<?php echo base_url(); ?>/member/profile';
    }

    $(document).ready(function() {
      var regions = new Bloodhound({
        datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        remote: {
          url: '<?php echo base_url(); ?>/welcome/searchregion?q=%QUERY%',
          wildcard: '%QUERY%',
          filter: function(list) {
            return $.map(list, function(company) {
              return {
                name: company.name
              };
            });
          }
        }
      });
      var provinces = new Bloodhound({
        datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        remote: {
          cache: false,
          url: '<?php echo base_url(); ?>/welcome/searchprovince?q=%QUERY%',
          wildcard: '%QUERY%',
          filter: function(list) {
            return $.map(list, function(company) {
              return {
                name: company.name
              };
            });
          }
        }
      });
      /*var titles = new Bloodhound({
      	datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
      	queryTokenizer: Bloodhound.tokenizers.whitespace,
      	remote: {
      		cache: false,
      		url: '<?php echo base_url(); ?>/welcome/searchtitle?q=%QUERY%',
      		wildcard: '%QUERY%',
      		filter: function(list) {
      			return $.map(list, function(company) {
      				return { name: company.name };
      			});
      		}
      	}
      });*/
      var kodepos = new Bloodhound({
        datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        remote: {
          cache: false,
          url: '<?php echo base_url(); ?>/welcome/searchzipcode?q=%QUERY%',
          wildcard: '%QUERY%',
          filter: function(list) {
            return $.map(list, function(company) {
              return {
                name: company.name
              };
            });
          }
        }
      });

      $('#birthplace').typeahead(null, {
        name: 'regions',
        display: 'name',
        source: regions,
        hint: true,
        highlight: true,
        minLength: 2,
        limit: Infinity,
        templates: {
          /*empty: [
              '<div class="tt-empty-message">',
                  'No Companies matched your input',
              '</div>'
          ].join('\n'),*/
        }
      });

      /*$("input[id^='c_title']").typeahead(null, {
            name: 'titles',
            display: 'name',
            source: titles,
            hint: true,
			highlight: true,
			minLength: 2,
			limit: Infinity,
            templates: {
                
            }
        });*/

      $("input[id^='c_title']").autocomplete({
        source: function(request, response) {
          var x = $(this.element).prop("id");
          x = x.replace("c_title", "");
          $.ajax({
            url: '<?php echo base_url(); ?>/welcome/searchtitlev2_s',
            dataType: "JSON",
            data: {
              term: request.term,
              edu: $("#c_degree" + x).val()
            },
            success: function(data) {
              response(data);
            }
          });
        },
        minLength: 0,
        select: function(event, ui) {
          //log( "Selected: " + ui.item.value + " aka " + ui.item.id );
        }
      });

      $("input[id^='c_prefix_title']").autocomplete({
        source: function(request, response) {
          var x = $(this.element).prop("id");
          x = x.replace("c_prefix_title", "");
          $.ajax({
            url: '<?php echo base_url(); ?>/welcome/searchtitlev2_p',
            dataType: "JSON",
            data: {
              term: request.term,
              edu: $("#c_degree" + x).val()
            },
            success: function(data) {
              response(data);
            }
          });
        },
        minLength: 0,
        select: function(event, ui) {
          //log( "Selected: " + ui.item.value + " aka " + ui.item.id );
        }
      });

      $("input[id^='c_loc']").typeahead(null, {
        name: 'regions',
        display: 'name',
        source: regions,
        hint: true,
        highlight: true,
        minLength: 2,
        limit: Infinity,
        templates: {
          /*empty: [
              '<div class="tt-empty-message">',
                  'No Companies matched your input',
              '</div>'
          ].join('\n'),*/
        }
      });


      /*$( "input[id^='c_cert_title']" ).autocomplete({
      	source: function( request, response ) {
      	var x = $(this.element).prop("id");
      	x = x.replace("c_cert_title", "");
      	$.ajax( {
      	  url: '<?php echo base_url(); ?>/welcome/searchtitlev3',
      	  dataType: "JSON",
      	  data: {
      		term: request.term
      	  },
      	  success: function( data ) {
      	    response( data );
      	  }
      	} );},
      	minLength: 0,
      	select: function( event, ui ) {
      		//log( "Selected: " + ui.item.value + " aka " + ui.item.id );
      	}
      });*/


      //Address

      $(document).on('click', '.address-item-remove-button', function() {
        var changeConfirmation = confirm("Apakah anda yakin ingin menghapus?");
        if (changeConfirmation) {
          var targetId = $(this).data('id');
          if (targetId != '1') {
            var addressid = $('#addressid' + targetId).val();
            if (addressid != '') {
              $.ajax({
                url: '<?php echo site_url('member/del_address') ?>',
                dataType: "html",
                type: "POST",
                async: true, //false
                data: {
                  id: addressid
                },
                success: function(jsonObject, status) {
                  console.log(jsonObject);
                  if ((jsonObject != 'not valid')) {
                    dataHTML = jsonObject;
                  }

                  if (dataHTML == 'not valid')
                    alert('not valid');
                }
              });

            }
            $('.address-item[data-id="' + targetId + '"]').remove();
          }


        } else {
          return false;
        }
      });

      $(document).on('click', '.school-item-remove-button', function() {
        var changeConfirmation = confirm("Apakah anda yakin ingin menghapus?");
        if (changeConfirmation) {
          var targetId = $(this).data('id');
          if (targetId != '1') {
            var schoolid = $('#schoolid' + targetId).val();
            if (schoolid != '') {
              $.ajax({
                url: '<?php echo site_url('member/del_edu') ?>',
                dataType: "html",
                type: "POST",
                async: true, //false
                data: {
                  id: schoolid
                },
                success: function(jsonObject, status) {
                  console.log(jsonObject);
                  if ((jsonObject != 'not valid')) {
                    dataHTML = jsonObject;
                  }

                  if (dataHTML == 'not valid')
                    alert('not valid');
                }
              });

            }
            $('.school-item[data-id="' + targetId + '"]').remove();
          }
        } else {
          return false;
        }
      });

      $(document).on('click', '.cert-item-remove-button', function() {
        var changeConfirmation = confirm("Apakah anda yakin ingin menghapus?");
        if (changeConfirmation) {
          var targetId = $(this).data('id');
          if (targetId != '1') {
            var certid = $('#certid' + targetId).val();
            if (certid != '') {
              $.ajax({
                url: '<?php echo site_url('member/del_cert') ?>',
                dataType: "html",
                type: "POST",
                async: true, //false
                data: {
                  id: certid
                },
                success: function(jsonObject, status) {
                  console.log(jsonObject);
                  if ((jsonObject != 'not valid')) {
                    dataHTML = jsonObject;
                  }

                  if (dataHTML == 'not valid')
                    alert('not valid');
                }
              });

            }
            $('.cert-item[data-id="' + targetId + '"]').remove();
          }

        } else {
          return false;
        }


      });

      $(document).on('click', '.exp-item-remove-button', function() {
        var changeConfirmation = confirm("Apakah anda yakin ingin menghapus?");
        if (changeConfirmation) {
          var targetId = $(this).data('id');
          if (targetId != '1') {
            var expid = $('#expid' + targetId).val();
            if (expid != '') {
              $.ajax({
                url: '<?php echo site_url('member/del_exp') ?>',
                dataType: "html",
                type: "POST",
                async: true, //false
                data: {
                  id: expid
                },
                success: function(jsonObject, status) {
                  console.log(jsonObject);
                  if ((jsonObject != 'not valid')) {
                    dataHTML = jsonObject;
                  }

                  if (dataHTML == 'not valid')
                    alert('not valid');
                }
              });

            }
            $('.exp-item[data-id="' + targetId + '"]').remove();
          }
        } else {
          return false;
        }

      });


      $(document).on('click', '.email-item-remove-button', function() {
        var changeConfirmation = confirm("Apakah anda yakin ingin menghapus?");
        if (changeConfirmation) {
          var targetId = $(this).data('id');
          if (targetId != '1') {
            var emailid = $('#emailid' + targetId).val();
            if (emailid != '') {
              $.ajax({
                url: '<?php echo site_url('member/del_email') ?>',
                dataType: "html",
                type: "POST",
                async: true, //false
                data: {
                  id: emailid
                },
                success: function(jsonObject, status) {
                  console.log(jsonObject);
                  if ((jsonObject != 'not valid')) {
                    dataHTML = jsonObject;
                  }

                  if (dataHTML == 'not valid')
                    alert('not valid');
                }
              });

            }
            $('.email-item[data-id="' + targetId + '"]').remove();
          }


        } else {
          return false;
        }
      });

      $(document).on('click', '.phone-item-remove-button', function() {
        var changeConfirmation = confirm("Apakah anda yakin ingin menghapus?");
        if (changeConfirmation) {
          var targetId = $(this).data('id');
          if (targetId != '1') {
            var phoneid = $('#phoneid' + targetId).val();
            if (phoneid != '') {
              $.ajax({
                url: '<?php echo site_url('member/del_phone') ?>',
                dataType: "html",
                type: "POST",
                async: true, //false
                data: {
                  id: phoneid
                },
                success: function(jsonObject, status) {
                  console.log(jsonObject);
                  if ((jsonObject != 'not valid')) {
                    dataHTML = jsonObject;
                  }

                  if (dataHTML == 'not valid')
                    alert('not valid');
                }
              });

            }
            $('.phone-item[data-id="' + targetId + '"]').remove();
          }


        } else {
          return false;
        }
      });


      $(document).on('click', "input[id^='c_certwork']", function() {
        //alert('a');
        //var a = document.getElementById("c_certwork").checked;

        var a = $(this).is(':checked');
        var targetId = $(this).attr('data-id');
        var temp = "";
        if (targetId != 1)
          temp = targetId;

        if (a == true) {
          $('#is_presentcert1' + temp).css('display', 'none');
          $('#is_presentcert2' + temp).css('display', 'none');
        } else {
          $('#is_presentcert1' + temp).css('display', 'block');
          $('#is_presentcert2' + temp).css('display', 'block');
        }
      });

      $(document).on('click', "input[id^='c_work']", function() {
        //alert('a');
        //var a = document.getElementById("c_certwork").checked;

        var a = $(this).is(':checked');
        var targetId = $(this).attr('data-id');
        var temp = "";
        if (targetId != 1)
          temp = targetId;

        if (a == true) {
          $('#is_presentwork1' + temp).css('display', 'none');
          $('#is_presentwork2' + temp).css('display', 'none');
        } else {
          $('#is_presentwork1' + temp).css('display', 'block');
          $('#is_presentwork2' + temp).css('display', 'block');
        }
      });

      $("input[id^='addresscity']").typeahead(null, {
        name: 'regions',
        display: 'name',
        source: regions,
        limit: 10,
        minLength: 0,
        templates: {
          /*empty: [
              '<div class="tt-empty-message">',
                  'No Companies matched your input',
              '</div>'
          ].join('\n'),*/
        }
      }).bind('change blur', function() {
        var text = $(this).val();
        var id = $(this).prop('id');
        id = id.replace("city", "province");

        $.ajax({
          url: "<?php echo base_url(); ?>welcome/fxsearchprovince?q=" + text,
          success: function(result) {

            if (result != "false") {
              var x = JSON.parse(result);
              $('#' + id).val(x[0].name);
              //console.log($('#'+id));
            }
            //else updateTotalEstimatedChargeByFlag(text);
            //console.log(result);
          }
        });


      });

      $("input[id^='addressprovince']").typeahead(null, {
        name: 'provinces',
        display: 'name',
        source: provinces,
        limit: 10,
        minLength: 0,
        templates: {
          /*empty: [
              '<div class="tt-empty-message">',
                  'No Companies matched your input',
              '</div>'
          ].join('\n'),*/
        }
      });

      $("input[id^='addresszip']").typeahead(null, {
        name: 'kodepos',
        display: 'name',
        source: kodepos,
        limit: 10,
        minLength: 0,
        templates: {
          /*empty: [
              '<div class="tt-empty-message">',
                  'No Companies matched your input',
              '</div>'
          ].join('\n'),*/
        }
      }).bind('change blur', function() {
        var text = $(this).val();
        var id = $(this).prop('id');
        var city = id.replace("zip", "city");
        var province = id.replace("zip", "province");

        $.ajax({
          url: "<?php echo base_url(); ?>welcome/fxsearchcityprovincebyzip?q=" + text,
          success: function(result) {

            if (result != "false") {
              var x = JSON.parse(result);
              $('#' + city).val(x[0].city);
              $('#' + province).val(x[0].province);
              //console.log(x);
            }
          }
        });


      });

      $("select[id^='c_degree']").change(function(e) {
        var c = ($(this).attr('id'));
        c = c.replace("c_degree", "");

        $("#c_title" + c).val('');
        $("#c_prefix_title" + c).val('');
      });

      $('#editprofile').click(function(e) {
        var valid = true;
        var email = "";

        var fn = $('#fn');
        var ln = $('#ln');
        var gender = $('input[name="gender"]:checked').val(); // $('#c_work');
        var warga_asing = $('input[name="warga_asing"]:checked').val();
        var phone = $('#phone');
        var birthplace = $('#birthplace');
        var dob = $('#dob');
        var typeid = $('input[name="typeid"]:checked').val();
        var idnumber = $('#idnumber');
        var website = $('#website');
        var desc = $('#desc');
        var is_public = document.getElementById("is_public").checked;
        var is_datasend = document.getElementById("is_datasend").checked;

        var email = document.getElementsByName('email[]');
        var typeaddress = document.getElementsByName('typeaddress[]');
        var address = document.getElementsByName('address[]');
        var addressphone = document.getElementsByName('addressphone[]');
        var addresscity = document.getElementsByName('addresscity[]');
        var addressprovince = document.getElementsByName('addressprovince[]');
        var addresszip = document.getElementsByName('addresszip[]');
        var addressid = document.getElementsByName('addressid[]');
        var mailingaddr = $('input[name="mailingaddr"]:checked').val();

        var typephone = document.getElementsByName('typephone[]');
        var phonem = document.getElementsByName('phonem[]');
        var phoneid = document.getElementsByName('phoneid[]');

        var typeemail = document.getElementsByName('typeemail[]');
        var emailm = document.getElementsByName('emailm[]');
        var emailid = document.getElementsByName('emailid[]');

        /*for(key=0; key < addressphone.length; key++)  {
        	if(addressphone[key].value == '')
        	valid = false;
        }
        for(key=0; key < email.length; key++)  {
        	if(email[key].value == '')
        	valid = false;
        }*/
        for (key = 0; key < typeaddress.length; key++) {
          if (typeaddress[key].value == '')
            valid = false;
        }
        for (key = 0; key < address.length; key++) {
          if (address[key].value == '')
            valid = false;
        }
        for (key = 0; key < addressprovince.length; key++) {
          if (addressprovince[key].value == '')
            valid = false;
        }
        for (key = 0; key < addresscity.length; key++) {
          if (addresscity[key].value == '')
            valid = false;
        }
        for (key = 0; key < addresszip.length; key++) {
          if (addresszip[key].value == '')
            valid = false;
        }
        //custom
        for (key = 0; key < email.length; key++) {
          if (email[key].value != '' && isEmail(email[key].value) == false) {
            alert('email not valid');
            return false;
          }
        }
        for (key = 0; key < addresszip.length; key++) {
          if (addresszip[key].value != '' && isNumeric(addresszip[key].value) == false) {
            alert('zip code not valid');
            return false;
          }
        }

        /*for(key=0; key < typephone.length; key++)  {
        	if(typephone[key].value == '')
        	valid = false;
        }
        for(key=0; key < phonem.length; key++)  {
        	if(phonem[key].value == '')
        	valid = false;
        }
        for(key=0; key < typeemail.length; key++)  {
        	if(typeemail[key].value == '')
        	valid = false;
        }
        for(key=0; key < emailm.length; key++)  {
        	if(emailm[key].value == '')
        	valid = false;
        }*/

        if (fn.val() != '' && phone.val() != '' && (idnumber.val()).trim() != '' && typeid != '' && dob.val() != '' && gender != '' && birthplace.val() != '' && mailingaddr != '' && valid == true) {
          e.preventDefault();

          var addressphonex = $('input[name="addressphone[]"]').map(function() {
            return this.value;
          }).get();
          var emailx = $('input[name="email[]"]').map(function() {
            return this.value;
          }).get();
          var typeaddressx = $('select[name="typeaddress[]"]').map(function() {
            return this.value;
          }).get();
          var addressx = $('input[name="address[]"]').map(function() {
            return this.value;
          }).get();
          var addresscityx = $('input[name="addresscity[]"]').map(function() {
            return this.value;
          }).get();
          var addressprovincex = $('input[name="addressprovince[]"]').map(function() {
            return this.value;
          }).get();
          var addresszipx = $('input[name="addresszip[]"]').map(function() {
            return this.value;
          }).get();
          var addressidx = $('input[name="addressid[]"]').map(function() {
            return this.value;
          }).get();

          var typephonex = $('select[name="typephone[]"]').map(function() {
            return this.value;
          }).get();
          var phonemx = $('input[name="phonem[]"]').map(function() {
            return this.value;
          }).get();
          var phoneidx = $('input[name="phoneid[]"]').map(function() {
            return this.value;
          }).get();

          var typeemailx = $('select[name="typeemail[]"]').map(function() {
            return this.value;
          }).get();
          var emailmx = $('input[name="emailm[]"]').map(function() {
            return this.value;
          }).get();
          var emailidx = $('input[name="emailid[]"]').map(function() {
            return this.value;
          }).get();

          var dataHTML = 'not valid';

          $.ajax({
            url: '<?php echo site_url('member/edit_profile') ?>',
            dataType: "html",
            type: "POST",
            async: true, //false
            data: {
              fn: fn.val(),
              ln: ln.val(),
              phone: phone.val(),
              dob: dob.val(),
              website: website.val(),
              desc: desc.val(),
              is_public: is_public,
              is_datasend: is_datasend,
              gender: gender,
              warga_asing: warga_asing,
              birthplace: birthplace.val(),
              typeid: typeid,
              idnumber: idnumber.val(),
              mailingaddr: mailingaddr,
              'addressphone[]': addressphonex,
              'email[]': emailx,
              'typeaddress[]': typeaddressx,
              'address[]': addressx,
              'addresscity[]': addresscityx,
              'addressprovince[]': addressprovincex,
              'addresszip[]': addresszipx,
              'addressid[]': addressidx,
              'typeemail[]': typeemailx,
              'typephone[]': typephonex,
              'emailm[]': emailmx,
              'phonem[]': phonemx,
              'emailid[]': emailidx,
              'phoneid[]': phoneidx
            },
            success: function(jsonObject, status) {
              console.log(jsonObject);
              if ((jsonObject != 'not valid')) {
                dataHTML = jsonObject;
              }

              if (dataHTML == 'not valid')
                alert('Please filled required field');
            }
          });


        } else {
          alert('Please filled required field');
        }
      });

      $('#editschool').click(function(e) {
        var valid = true;

        var c_school_type = document.getElementsByName('c_school_type[]');
        var c_school = document.getElementsByName('c_school[]');
        var c_dateattendstart = document.getElementsByName('c_dateattendstart[]');
        var c_dateattendend = document.getElementsByName('c_dateattendend[]');
        var c_degree = document.getElementsByName('c_degree[]');
        var c_mayor = document.getElementsByName('c_mayor[]');
        var c_fos = document.getElementsByName('c_fos[]');
        var c_score = document.getElementsByName('c_score[]');
        var c_title = document.getElementsByName('c_title[]');
        var c_prefix_title = document.getElementsByName('c_prefix_title[]');
        var c_actv = document.getElementsByName('c_actv[]');
        var c_descedu = document.getElementsByName('c_descedu[]');
        var schoolid = document.getElementsByName('schoolid[]');
        var edu_image_url = document.getElementsByName('edu_image_url[]');

        //Cert
        var c_certname = document.getElementsByName('c_certname[]');
        var c_certauth = document.getElementsByName('c_certauth[]');
        var c_lic = document.getElementsByName('c_lic[]');
        var c_url = document.getElementsByName('c_url[]');
        var c_cert_title = document.getElementsByName('c_cert_title[]');
        var c_certdate = document.getElementsByName('c_certdate[]');
        var c_certyear = document.getElementsByName('c_certyear[]');
        var c_certdate2 = document.getElementsByName('c_certdate2[]');
        var c_certyear2 = document.getElementsByName('c_certyear2[]');
        var c_certwork = document.getElementsByName('c_certwork[]');
        var c_certdesc = document.getElementsByName('c_certdesc[]');
        var certid = document.getElementsByName('certid[]');
        var cert_image_url = document.getElementsByName('cert_image_url[]');

        //console.log(addressphone);
        for (key = 0; key < c_school_type.length; key++) {
          if (c_school_type[key].value == '')
            valid = false;
        }
        for (key = 0; key < c_school.length; key++) {
          if (c_school[key].value == '')
            valid = false;
        }
        for (key = 0; key < c_dateattendstart.length; key++) {
          if (c_dateattendstart[key].value == '')
            valid = false;
        }
        for (key = 0; key < c_dateattendend.length; key++) {
          if (c_dateattendend[key].value == '')
            valid = false;
        }
        /*for(key=0; key < c_degree.length; key++)  {
        	if(c_degree[key].value == '')
        	valid = false;
        }
        for(key=0; key < c_mayor.length; key++)  {
        	if(c_mayor[key].value == '')
        	valid = false;
        }*/
        for (key = 0; key < c_fos.length; key++) {
          if (c_fos[key].value == '')
            valid = false;
        }


        for (key = 0; key < c_score.length; key++) {
          if (c_score[key].value == '' && c_school_type[key].value == '1')
            valid = false;
        }

        /*for(key=0; key < c_title.length; key++)  {
        	if(c_title[key].value == '')
        	valid = false;
        }*/


        //custom
        for (key = 0; key < c_dateattendstart.length; key++) {
          if (c_dateattendstart[key].value != '' && isNumeric(c_dateattendstart[key].value) == false) {
            alert('Tanggal tidak valid');
            return false;
          }
        }
        for (key = 0; key < c_dateattendend.length; key++) {
          if (c_dateattendend[key].value != '' && isNumeric(c_dateattendend[key].value) == false) {
            alert('Tanggal tidak valid');
            return false;
          }
        }
        for (key = 0; key < c_dateattendstart.length; key++) {
          if (c_dateattendstart[key].value != '' && isNumeric(c_dateattendstart[key].value) == true && c_dateattendend[key].value != '' && isNumeric(c_dateattendend[key].value) == true) {
            if (parseInt(c_dateattendstart[key].value) > parseInt(c_dateattendend[key].value)) {
              alert('Tanggal tidak valid');
              return false;
            }
          }
        }

        for (key = 0; key < c_score.length; key++) {
          if (c_score[key].value != '' && (isNumeric(c_score[key].value) == false && isFloat(c_score[key].value) == false)) {
            alert('Nilai tidak valid');
            return false;
          }
        }


        /*
        for(key=0; key < c_actv.length; key++)  {
        	if(c_actv[key].value == '')
        	valid = false;
        }
        for(key=0; key < c_descedu.length; key++)  {
        	if(c_descedu[key].value == '')
        	valid = false;
        }*/




        for (key = 0; key < c_certname.length; key++) {
          if (c_certname[key].value != '' || c_certauth[key].value != '' || c_lic[key].value != '') {
            if (c_certname[key].value == '')
              valid = false;
            if (c_certauth[key].value == '')
              valid = false;
            if (c_lic[key].value == '')
              valid = false;
            if (c_certdate[key].value == '')
              valid = false;
            if (c_certyear[key].value == '')
              valid = false;
            if ($(c_certwork[key]).is(':checked') == false && (c_certdate2[key].value == '' || c_certyear2[key].value == '')) {
              valid = false;
            }
            /*if(cert_image_url[key].value == '')
            	valid = false;*/
          }
        }

        //custom
        for (key = 0; key < c_certyear.length; key++) {
          if (c_certyear[key].value != '' && isNumeric(c_certyear[key].value) == false) {
            alert('Tanggal tidak valid');
            return false;
          }
        }
        for (key = 0; key < c_certyear2.length; key++) {
          if (c_certyear2[key].value != '' && isNumeric(c_certyear2[key].value) == false) {
            alert('Tanggal tidak valid');
            return false;
          }
        }

        for (key = 0; key < c_certyear.length; key++) {
          if (c_certyear[key].value != '' && isNumeric(c_certyear[key].value) == true && c_certyear2[key].value != '' && isNumeric(c_certyear2[key].value) == true && c_certdate[key].value != '' && c_certdate2[key].value != '' && $(c_certwork[key]).is(':checked') == false) {
            var s = new Date(c_certyear[key].value, c_certdate[key].value, 01);
            var e = new Date(c_certyear2[key].value, c_certdate2[key].value, 01);
            if (s > e) {
              alert('Tanggal tidak valid');
              return false;
            }
          }
        }



        if (valid == true) {
          //e.preventDefault();

          var c_certnamex = $('input[name="c_certname[]"]').map(function() {
            return this.value;
          }).get();
          var c_certauthx = $('input[name="c_certauth[]"]').map(function() {
            return this.value;
          }).get();
          var c_licx = $('input[name="c_lic[]"]').map(function() {
            return this.value;
          }).get();
          var c_urlx = $('input[name="c_url[]"]').map(function() {
            return this.value;
          }).get();
          var c_cert_titlex = $('input[name="c_cert_title[]"]').map(function() {
            return this.value;
          }).get();
          var c_certdatex = $('select[name="c_certdate[]"]').map(function() {
            return this.value;
          }).get();
          var c_certyearx = $('input[name="c_certyear[]"]').map(function() {
            return this.value;
          }).get();
          var c_certdate2x = $('select[name="c_certdate2[]"]').map(function() {
            return this.value;
          }).get();
          var c_certyear2x = $('input[name="c_certyear2[]"]').map(function() {
            return this.value;
          }).get();
          var c_certworkx = $('input[name="c_certwork[]"]').map(function() {


            return $(this).is(':checked');
          }).get();
          var c_certdescx = $('textarea[name="c_certdesc[]"]').map(function() {
            return this.value;
          }).get();
          var schoolidx = $('input[name="schoolid[]"]').map(function() {
            return this.value;
          }).get();
          var edu_image_urlx = $('input[name="edu_image_url[]"]').map(function() {
            return this.value;
          }).get();

          var c_school_typex = $('select[name="c_school_type[]"]').map(function() {
            return this.value;
          }).get();

          var c_schoolx = $('input[name="c_school[]"]').map(function() {
            return this.value;
          }).get();
          var c_dateattendstartx = $('input[name="c_dateattendstart[]"]').map(function() {
            return this.value;
          }).get();
          var c_dateattendendx = $('input[name="c_dateattendend[]"]').map(function() {
            return this.value;
          }).get();
          var c_degreex = $('select[name="c_degree[]"]').map(function() {
            return this.value;
          }).get();
          var c_mayorx = $('input[name="c_mayor[]"]').map(function() {
            return this.value;
          }).get();
          var c_fosx = $('input[name="c_fos[]"]').map(function() {
            return this.value;
          }).get();
          var c_scorex = $('input[name="c_score[]"]').map(function() {
            return this.value;
          }).get();
          var c_titlex = $('input[name="c_title[]"]').map(function() {
            return this.value;
          }).get();
          var c_prefix_titlex = $('input[name="c_prefix_title[]"]').map(function() {
            return this.value;
          }).get();
          var c_actvx = $('input[name="c_actv[]"]').map(function() {
            return this.value;
          }).get();
          var c_descedux = $('textarea[name="c_descedu[]"]').map(function() {
            return this.value;
          }).get();

          var certidx = $('input[name="certid[]"]').map(function() {
            return this.value;
          }).get();

          var cert_image_urlx = $('input[name="cert_image_url[]"]').map(function() {
            return this.value;
          }).get();

          var dataHTML = 'not valid';

          $.ajax({
            url: '<?php echo site_url('member/edit_edu') ?>',
            dataType: "html",
            type: "POST",
            async: true, //false ,
            data: {
              'type[]': c_school_typex,
              'school[]': c_schoolx,
              'dateattendstart[]': c_dateattendstartx,
              'dateattendend[]': c_dateattendendx,
              'degree[]': c_degreex,
              'fos[]': c_fosx,
              'score[]': c_scorex,
              'title[]': c_titlex,
              'title_prefix[]': c_prefix_titlex,
              'actv[]': c_actvx,
              'descedu[]': c_descedux,
              'certname[]': c_certnamex,
              'certauth[]': c_certauthx,
              'lic[]': c_licx,
              'url[]': c_urlx,
              'cert_title[]': c_cert_titlex,
              'certdate[]': c_certdatex,
              'certyear[]': c_certyearx,
              'certdate2[]': c_certdate2x,
              'certyear2[]': c_certyear2x,
              'certwork[]': c_certworkx,
              'certdesc[]': c_certdescx,
              'schoolid[]': schoolidx,
              'certid[]': certidx,
              'edu_image_url[]': edu_image_urlx,
              'cert_image_url[]': cert_image_urlx,
              'mayor[]': c_mayorx
            },
            success: function(jsonObject, status) {
              console.log(jsonObject);
              if ((jsonObject != 'not valid')) {
                dataHTML = jsonObject;
              }

              if (dataHTML == 'not valid')
                alert('Please filled required field');
            }
          });


        } else {
          alert('Please filled required field');
        }
      });

      $('#editExp').click(function(e) {
        var valid = true;

        var c_company = document.getElementsByName('c_company[]');
        var c_title = document.getElementsByName('c_exptitle[]');
        var c_loc = document.getElementsByName('c_loc[]');
        var c_provinsi = document.getElementsByName('c_provinsi[]');
        var c_negara = document.getElementsByName('c_negara[]');
        var c_typetimeperiod = document.getElementsByName('c_typetimeperiod[]');
        var c_year = document.getElementsByName('c_year[]');
        var c_typetimeperiod2 = document.getElementsByName('c_typetimeperiod2[]');
        var c_year2 = document.getElementsByName('c_year2[]');
        var c_work = document.getElementsByName('c_work[]');
        var c_actv = document.getElementsByName('c_actv[]');
        var c_desc = document.getElementsByName('c_desc[]');
        var expid = document.getElementsByName('expid[]');
        var exp_image_url = document.getElementsByName('exp_image_url[]');

        for (key = 0; key < c_company.length; key++) {
          if (c_company[key].value == '')
            valid = false;
        }
        for (key = 0; key < c_title.length; key++) {
          if (c_title[key].value == '')
            valid = false;
        }
        for (key = 0; key < c_loc.length; key++) {
          if (c_loc[key].value == '')
            valid = false;
        }
        for (key = 0; key < c_provinsi.length; key++) {
          if (c_provinsi[key].value == '')
            valid = false;
        }
        for (key = 0; key < c_negara.length; key++) {
          if (c_negara[key].value == '')
            valid = false;
        }
        for (key = 0; key < c_typetimeperiod.length; key++) {
          if (c_typetimeperiod[key].value == '')
            valid = false;
        }
        for (key = 0; key < c_year.length; key++) {
          if (c_year[key].value == '')
            valid = false;
        }

        for (key = 0; key < c_work.length; key++) {
          if ($(c_work[key]).is(':checked') == false && (c_typetimeperiod2[key].value == '' || c_year2[key].value == '')) {
            valid = false;
          }
          //valid = false;
        }
        /*for(key=0; key < c_desc.length; key++)  {
        	if(c_desc[key].value == '')
        	valid = false;
        }*/


        for (key = 0; key < c_year.length; key++) {
          if (c_year[key].value != '' && isNumeric(c_year[key].value) == true && c_year2[key].value != '' && isNumeric(c_year2[key].value) == true && c_typetimeperiod[key].value != '' && c_typetimeperiod2[key].value != '' && $(c_work[key]).is(':checked') == false) {
            var s = new Date(c_year[key].value, c_typetimeperiod[key].value, 01);
            var e = new Date(c_year2[key].value, c_typetimeperiod2[key].value, 01);
            if (s > e) {
              alert('Tanggal tidak valid');
              return false;
            }
          }
        }

        if (valid == true) {
          //e.preventDefault();

          var c_companyx = $('input[name="c_company[]"]').map(function() {
            return this.value;
          }).get();
          var c_titlex = $('input[name="c_exptitle[]"]').map(function() {
            return this.value;
          }).get();
          var c_locx = $('input[name="c_loc[]"]').map(function() {
            return this.value;
          }).get();
          var c_negarax = $('input[name="c_negara[]"]').map(function() {
            return this.value;
          }).get();
          var c_provinsix = $('input[name="c_provinsi[]"]').map(function() {
            return this.value;
          }).get();
          var c_typetimeperiodx = $('select[name="c_typetimeperiod[]"]').map(function() {
            return this.value;
          }).get();
          var c_yearx = $('input[name="c_year[]"]').map(function() {
            return this.value;
          }).get();
          var c_typetimeperiod2x = $('select[name="c_typetimeperiod2[]"]').map(function() {
            return this.value;
          }).get();
          var c_year2x = $('input[name="c_year2[]"]').map(function() {
            return this.value;
          }).get();
          var c_workx = $('input[name="c_work[]"]').map(function() {


            return $(this).is(':checked');
          }).get();
          var c_descx = $('textarea[name="c_desc[]"]').map(function() {
            return this.value;
          }).get();
          var c_actvx = $('textarea[name="c_actv[]"]').map(function() {
            return this.value;
          }).get();
          var expidx = $('input[name="expid[]"]').map(function() {
            return this.value;
          }).get();
          var exp_image_urlx = $('input[name="exp_image_url[]"]').map(function() {
            return this.value;
          }).get();
          var dataHTML = 'not valid';

          $.ajax({
            url: '<?php echo site_url('member/edit_exp') ?>',
            dataType: "html",
            type: "POST",
            async: true, //false
            data: {
              'company[]': c_companyx,
              'title[]': c_titlex,
              'loc[]': c_locx,
              'provinsi[]': c_provinsix,
              'negara[]': c_negarax,
              'typetimeperiod[]': c_typetimeperiodx,
              'year[]': c_yearx,
              'typetimeperiod2[]': c_typetimeperiod2x,
              'year2[]': c_year2x,
              'work[]': c_workx,
              'actv[]': c_actvx,
              'desc[]': c_descx,
              'expid[]': expidx,
              'exp_image_url[]': exp_image_urlx
            },
            success: function(jsonObject, status) {
              console.log(jsonObject);
              if ((jsonObject != 'not valid')) {
                dataHTML = jsonObject;
              }

              if (dataHTML == 'not valid')
                alert('Please filled required field');
            }
          });


        } else {
          alert('Please filled required field');
        }
      });

    });

    function isEmail(email) {
      var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
      return regex.test(email);
    }

    function isNumeric(n) {
      return !isNaN(parseFloat(n)) && isFinite(n);
    }

    function isFloat(val) {
      var floatRegex = /^-?\d+(?:[.,]\d*?)?$/;
      if (!floatRegex.test(val))
        return false;

      val = parseFloat(val);
      if (isNaN(val))
        return false;
      return true;
    }

    function upload_ktp() {
      var formData = new FormData();
      formData.append('ktp', $('#ktp')[0].files[0]);

      $("#errUpload").html("");
      $("#errUpload").addClass("loader");
      $("#ktp").addClass("hide");

      $.ajax({
        url: "<?php echo site_url('member/ktp_upload') ?>",
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(data, textStatus, jqXHR) {
          if (data.substring(0, 6) == '<input') {
            $('#avatar').html($(data).fadeIn('slow'));
            uploadFlag = 1;
            $('#errUpload').html($('').fadeIn('slow'));
          } else {
            $('#errUpload').html($(data).fadeIn('slow'));
            $('#avatar').html('');
            $('#ktp').val('');
          }

          $("#errUpload").removeClass("loader");
          $("#ktp").removeClass("hide");
          $('#ktp').val('');
        },
        error: function(jqXHR, textStatus, errorThrown) {
          $('#errUpload').html($(textStatus).fadeIn('slow'));

          $("#errUpload").removeClass("loader");
          $("#ktp").removeClass("hide");
          $('#ktp').val('');

        }
      });
    }



    function upload_edu(edu) {
      var formData = new FormData();
      formData.append('file', $('#' + edu)[0].files[0]);

      $('#errUpload' + edu).html("");
      $('#errUpload' + edu).addClass("loader");
      $('#' + edu).addClass("hide");

      $.ajax({
        url: "<?php echo site_url('member/edu_upload') ?>",
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(data, textStatus, jqXHR) {
          if (data.substring(0, 6) == '<input') {
            $('#avatar' + edu).html($(data).fadeIn('slow'));
            uploadFlag = 1;
            $('#errUpload' + edu).html($('').fadeIn('slow'));
          } else {
            $('#errUpload' + edu).html($(data).fadeIn('slow'));
            $('#avatar' + edu).html('');
            $('#' + edu).val('');
          }

          $('#errUpload' + edu).removeClass("loader");
          $('#' + edu).removeClass("hide");
          $('#' + edu).val('');

        },
        error: function(jqXHR, textStatus, errorThrown) {
          $('#errUpload' + edu).html($(textStatus).fadeIn('slow'));
          $('#' + edu).val('');

          $('#errUpload' + edu).removeClass("loader");
          $('#' + edu).removeClass("hide");
          $('#' + edu).val('');

        }
      });
    }

    function upload_cert(cert) {
      var formData = new FormData();
      formData.append('file', $('#' + cert)[0].files[0]);

      $('#errUpload' + cert).html("");
      $('#errUpload' + cert).addClass("loader");
      $('#' + cert).addClass("hide");

      $.ajax({
        url: "<?php echo site_url('member/cert_upload') ?>",
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(data, textStatus, jqXHR) {
          if (data.substring(0, 6) == '<input') {
            $('#avatar' + cert).html($(data).fadeIn('slow'));
            uploadFlag = 1;
            $('#errUpload' + cert).html($('').fadeIn('slow'));
          } else {
            $('#errUpload' + cert).html($(data).fadeIn('slow'));
            $('#avatar' + cert).html('');
            $('#' + cert).val('');
          }

          $('#errUpload' + cert).removeClass("loader");
          $('#' + cert).removeClass("hide");
          $('#' + cert).val('');
        },
        error: function(jqXHR, textStatus, errorThrown) {
          $('#errUpload' + cert).html($(textStatus).fadeIn('slow'));
          $('#' + cert).val('');

          $('#errUpload' + cert).removeClass("loader");
          $('#' + cert).removeClass("hide");
          $('#' + cert).val('');
        }
      });
    }

    function upload_exp(exp) {
      var formData = new FormData();
      formData.append('file', $('#' + exp)[0].files[0]);

      $('#errUpload' + exp).html("");
      $('#errUpload' + exp).addClass("loader");
      $('#' + exp).addClass("hide");

      $.ajax({
        url: "<?php echo site_url('member/exp_upload') ?>",
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(data, textStatus, jqXHR) {
          if (data.substring(0, 6) == '<input') {
            $('#avatar' + exp).html($(data).fadeIn('slow'));
            uploadFlag = 1;
            $('#errUpload' + exp).html($('').fadeIn('slow'));
          } else {
            $('#errUpload' + exp).html($(data).fadeIn('slow'));
            $('#avatar' + exp).html('');
            $('#' + exp).val('');
          }

          $('#errUpload' + exp).removeClass("loader");
          $('#' + exp).removeClass("hide");
          $('#' + exp).val('');
        },
        error: function(jqXHR, textStatus, errorThrown) {
          $('#errUpload' + exp).html($(textStatus).fadeIn('slow'));
          $('#' + exp).val('');

          $('#errUpload' + exp).removeClass("loader");
          $('#' + exp).removeClass("hide");
          $('#' + exp).val('');
        }
      });
    }

    function upload_pernyataan(textid) {
      var formData = new FormData();
      formData.append(textid, $('#' + textid)[0].files[0]);

      $("#errUpload_" + textid).html("");
      $("#errUpload_" + textid).addClass("loader");
      $("#" + textid).addClass("hide");

      $.ajax({
        url: "<?php echo site_url('member/"+textid+"_upload') ?>",
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(data, textStatus, jqXHR) {
          if (data.substring(0, 6) == '<input') {
            $('#avatar_' + textid).html($(data).fadeIn('slow'));
            uploadFlag = 1;
            $("#errUpload_" + textid).html($('').fadeIn('slow'));
          } else {
            $("#errUpload_" + textid).html($(data).fadeIn('slow'));
            $('#avatar_' + textid).html('');
            $('#' + textid).val('');
          }

          $("#errUpload_" + textid).removeClass("loader");
          $("#" + textid).removeClass("hide");
          $('#' + textid).val('');
        },
        error: function(jqXHR, textStatus, errorThrown) {
          $("#errUpload_" + textid).html($(textStatus).fadeIn('slow'));

          $("#errUpload_" + textid).removeClass("loader");
          $("#" + textid).removeClass("hide");
          $('#' + textid).val('');

        }
      });
    }

    function tipe_school(id) {
      var temp = '';
      if (id != 1) temp = id;

      $('#c_prefix_title' + temp).val('');
      $('#c_title' + temp).val('');

      var c_school_type = $('#c_school_type' + temp).val();
      var label_c_tahun = $('#label_c_tahun' + temp);
      var label_c_fos = $('#label_c_fos' + temp);
      var label_c_score = $('#label_c_score' + temp);
      var targetId = $('#c_degree' + temp).val();

      if (c_school_type == 1) {
        $('#label_c_tahun' + temp).html('Tahun<span class="red"> *</span>');
        $('#label_c_fos' + temp).html('Jurusan/Kejuruan<span class="red"> *</span>');
        $('#label_c_score' + temp).html('IPK<span class="red"> *</span>');
        $('#c_score' + temp).prop('required', true);

        $('#c_degree' + temp).show();
        $('#c_degree' + temp).prop('required', true);

        $('#c_mayor' + temp).show();
        $('#c_mayor' + temp).prop('required', true);

        $('#label_c_tingkat' + temp).show();
        $('#label_c_mayor' + temp).show();

        $('#c_degree' + temp).find('option').remove();
        $.fn.populate = function() {
          $(this)
            .append('<option value="D3">D3</option>')
            .append('<option value="D4">D4</option>')
            .append('<option value="S1">S1</option>')
            .append('<option value="S2">S2</option>')
            .append('<option value="S3">S3</option>')
        }

        $('#c_degree' + temp).populate();
      } else {
        $('#label_c_tahun' + temp).html('Tahun Lulus<span class="red"> *</span>');
        $('#label_c_fos' + temp).html('Nomor Sertifikat<span class="red"> *</span>');
        $('#label_c_score' + temp).html('Nilai');
        $('#c_score' + temp).prop('required', false);

        $('#c_degree' + temp).find('option').remove();

        $('#c_degree' + temp).hide();
        $('#c_degree' + temp).prop('required', false);

        $('#c_mayor' + temp).hide();
        $('#c_mayor' + temp).prop('required', false);

        $('#label_c_tingkat' + temp).hide();
        $('#label_c_mayor' + temp).hide();

        /*$.fn.populate = function() {
          $(this)
        	.append('<option value="IPP">IPP</option>')
        	.append('<option value="IPM">IPM</option>')
        	.append('<option value="IPU">IPU</option>')
        }

        $('#c_degree'+temp).populate();*/
      }

      //alert(label_c_tahun.innerHTML);
    }

    function cek_warga() {
      var warga = $('input[name="warga_asing"]:checked').val();
      if (warga == 1) {
        $('#valid_warga[value="Citizen"]').attr('disabled', 'disabled');
        $('input[name="typeid"][value="Passport"]').prop("checked", true);
        $('.iswna').css('display', 'block');
      } else {
        $('#valid_warga[value="Citizen"]').attr('disabled', false);
        $('.iswna').css('display', 'none');

      }
    }
  </script>
  <style>
    .tt-query {
      -webkit-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075);
      -moz-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075);
      box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075);
    }

    .tt-hint {
      color: #999
    }

    .tt-menu {
      /* used to be tt-dropdown-menu in older versions */
      width: 422px;
      margin-top: 4px;
      padding: 4px 0;
      background-color: #fff;
      border: 1px solid #ccc;
      border: 1px solid rgba(0, 0, 0, 0.2);
      -webkit-border-radius: 4px;
      -moz-border-radius: 4px;
      border-radius: 4px;
      -webkit-box-shadow: 0 5px 10px rgba(0, 0, 0, .2);
      -moz-box-shadow: 0 5px 10px rgba(0, 0, 0, .2);
      box-shadow: 0 5px 10px rgba(0, 0, 0, .2);
    }

    .tt-suggestion {
      padding: 3px 20px;
      line-height: 24px;
    }

    .tt-suggestion.tt-cursor,
    .tt-suggestion:hover {
      color: #fff;
      background-color: #0097cf;

    }

    .tt-suggestion p {
      margin: 0;
    }

    .red {
      color: red;
    }

    .loader {
      border: 16px solid #f3f3f3;
      /* Light grey */
      border-top: 16px solid #3498db;
      /* Blue */
      border-radius: 50%;
      width: 120px;
      height: 120px;
      animation: spin 2s linear infinite;
    }

    .hide {
      display: none;
    }

    @keyframes spin {
      0% {
        transform: rotate(0deg);
      }

      100% {
        transform: rotate(360deg);
      }
    }
  </style>

  <script>
    function upload_photo() {
      var formData = new FormData();
      formData.append('photo', $('#photo')[0].files[0]);

      $("#errUpload2").html("");
      $("#errUpload2").addClass("loader");
      $("#photo").addClass("hide");

      $.ajax({
        url: "<?php echo site_url('member/photo_upload') ?>",
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(data, textStatus, jqXHR) {
          if (data.substring(0, 6) == '<input') {
            $('#avatar2').html($(data).fadeIn('slow'));
            uploadFlag = 1;
            $('#errUpload2').html($('').fadeIn('slow'));
          } else {
            $('#errUpload2').html($(data).fadeIn('slow'));
            $('#avatar2').html('');
            //$('#photo').val('');
          }

          $("#errUpload2").removeClass("loader");
          $("#photo").removeClass("hide");
          $('#photo').val('');
        },
        error: function(jqXHR, textStatus, errorThrown) {
          $('#errUpload2').html($(textStatus).fadeIn('slow'));


          $("#errUpload2").removeClass("loader");
          $("#photo").removeClass("hide");
          $('#photo').val('');
        }
      });
    }
  </script>

  <script>
    function previewFile(input, previewId) {
      var file = input.files[0];
      var preview = document.getElementById(previewId);
      preview.innerHTML = ""; // reset

      if (file) {
        var fileType = file.type;

        if (fileType.match('image.*')) {
          var reader = new FileReader();
          reader.onload = function(e) {
            preview.innerHTML = "<img src='" + e.target.result + "' class='img-thumbnail' width='200'>";
          };
          reader.readAsDataURL(file);
        } else if (fileType === "application/pdf") {
          preview.innerHTML = "<embed src='" + URL.createObjectURL(file) + "' type='application/pdf' width='100%' height='400px'>";
        } else {
          preview.innerHTML = "<p class='text-danger'>Format tidak didukung.</p>";
        }
      }
    }
  </script>