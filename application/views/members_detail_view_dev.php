<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <title><?php echo $title; ?></title>
  <?php $this->load->view('admin/common/meta_tags'); ?>
  <?php $this->load->view('admin/common/before_head_close'); ?>
</head>

<body class="skin-blue">
  <?php $this->load->view('admin/common/after_body_open'); ?>
  <?php $this->load->view('admin/common/header'); ?>
  <div class="wrapper row-offcanvas row-offcanvas-left">
    <?php $this->load->view('admin/common/left_side'); ?>
    <!-- Right side column. Contains the navbar and content of the page -->
    <aside class="right-side">
      <!-- Content Header (Page header) -->
      <section class="content-header">
        <h1> Member Management
          <!--<small>advanced tables</small>-->
        </h1>
        <ol class="breadcrumb">
          <li><a href="<?php echo base_url('admin/dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
          <li><a href="<?php echo base_url('admin/members'); ?>">Members</a></li>
          <li class="active"><?php echo $row->firstname . ' ' . $row->lastname; ?></li>
        </ol>
      </section>

      <!-- Main content -->
      <section class="content invoice">
        <!-- title row -->
        <div class="row">
          <div class="col-xs-12">
            <h2 class="page-header">
              <!--<i class="fa fa-globe"></i>--> <?php echo $row->firstname . ' ' . $row->lastname; ?>

              <?php if ($this->session->userdata('type') == "0" || $this->session->userdata('type') == "2" || ($this->session->userdata('type') == "11" && $this->session->userdata('code_wilayah') == "" && $this->session->userdata('code_bk_hkk') == "")) { ?>
                <a href="<?php echo base_url('admin/members/update/' . $row->user_id); ?>" style="font-size:12px;">Edit</a>
              <?php } ?>

              <small class="pull-right">Member Since: <?php echo date_formats($row->created, 'd/m/Y'); ?></small>
            </h2>
          </div>
          <!-- /.col -->
        </div>

        <?php /* ?>
	
	
    <!-- info row -->
    <div class="row invoice-info">
      <div class="col-sm-4 invoice-col"><b>Email Address:</b> <?php echo $row->email;?><br/>
        <b>Password:</b> <?php echo $row->pass_code;?><br/>
        <b>Account Status:</b> <?php echo $row->sts;?></div>
      <div class="col-sm-4 invoice-col"><b>Mobile Number:</b> <?php echo $row->mobile_phone;?><br/>
          <b>Gender:</b> <?php echo $row->gender;?><br/>
          <b>City:</b> <?php echo $row->city;?><br>
         <b>Country:</b> <?php echo $row->country;?>
      </div>
      <!-- /.col -->
      <div class="col-sm-4 invoice-col"> 
        <b>No. of jobs posted:</b> - </div>
      <!-- /.col --> 
    </div>
    <!-- /.row -->
	
	
	
	
	
	
    <div class="row">
      <div class="col-xs-12">
        <h2 class="page-header"> Company Information </h2>
      </div>
      <!-- /.col --> 
    </div>
	
    <div class="row invoice-info">
      <div class="col-sm-4 invoice-col"><b>Company Name:</b> <?php echo $row->company_name;?> <br>
        <b>Company Email:</b> <?php echo $row->company_email;?> <br>
        <strong>CEO: </strong><?php echo $row->company_ceo;?><br/>
        <b>Industry:</b> <?php echo $row->industry_name;?><br/>
        <b>Established In:</b> <?php echo $row->established_in;?>
      </div>
      <!-- /.col -->
      <div class="col-sm-4 invoice-col">
      	<b>Company Phone:</b> <?php echo $row->company_phone;?> <br>
        <b>Company Fax:</b> <?php echo $row->company_fax;?> <br>
      	<b>No. Of Offices:</b> <?php echo $row->no_of_offices;?><br/>
      	<b>No. Of Employees:</b> <?php echo $row->no_of_employees;?><br/>
        <b>Company Address:</b> <?php echo $row->company_location;?><br/>
        <b>Company Website: </b><?php echo $row->company_website;?>
      </div>
      <!-- /.col -->
      <?php $image_name = ($row->company_logo)?$row->company_logo:'no_logo.jpg';?>
      <div class="col-sm-4 invoice-col" style="text-align:center"><img src="<?php echo base_url('public/uploads/employer/'.$image_name);?>" style="max-width:150px;"></div>
      <!-- /.col --> 
    </div>
	
	
	
    <div>&nbsp;</div>
    <div class="row">
     <div class="col-sm-12 invoice-col"><b>Description: </b><?php echo $row->company_description;?></div>
    </div>
    <div>&nbsp;</div>
	
	
	<?php */ ?>










        <div class="row">
          <div class=""><!-- col-xs-12 col-sm-12 col-md-offset-1 col-md-10 col-lg-offset-2 col-lg-8 -->
            <div class="panel panel-default">
              <div class="panel-heading resume-heading">
                <div class="row">
                  <div class="col-lg-12">
                    <fieldset>

                      <!-- Form Name -->
                      <legend>Profile</legend>
                      <div class="row">
                        <div class="col-md-12 col-sm-12" style="text-align:center;">
                          <img class="img-fluid" width="200" src="<?php echo ($row->photo != '') ? base_url() . 'assets/uploads/' . $row->photo : "" ?>" title=""><br />

                          <?php
                          if (strpos(strtolower($row->photo), '.pdf') !== false) {
                            echo '<a  style="color:blue;" target="_blank" href="' . (($row->photo != '') ? base_url() . 'assets/uploads/' . $row->photo : "") . '"> Download Foto</a>';
                          }
                          ?>

                        </div>
                      </div>
                      <div class="row">
                        <div class="col-md-4">
                          <label class="control-label" for="fn">First name</label>
                        </div>
                        <div class="col-md-8">
                          <label class="control-label" for="fn"><?php echo isset($row->firstname) ? $row->firstname : ''; ?></label>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-md-4">
                          <label class="control-label" for="ln">Last name</label>
                        </div>
                        <div class="col-md-8">
                          <label class="control-label" for="ln"><?php echo isset($row->lastname) ? $row->lastname : ''; ?></label>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-md-4">
                          <label class="control-label" for="gender">Gender</label>
                        </div>
                        <div class="col-md-8">
                          <label class="control-label"><?php echo isset($row->gender) ? $row->gender : ''; ?></label>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-md-4">
                          <label class="control-label" for="mobilephone">Mobile Phone</label>
                        </div>
                        <div class="col-md-8">
                          <label class="control-label"><?php echo isset($row->mobilephone) ? $row->mobilephone : ''; ?></label>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-md-4">
                          <label class="control-label" for="ktp">ID Card</label>
                        </div>
                        <div class="col-md-8">
                          <label class="control-label">
                            <?php
                            //echo isset($row->idcard)?$row->idcard:''; 
                            ?>

                            <?php if ($this->session->userdata('type') == "0" || $this->session->userdata('type') == "2" || $this->session->userdata('type') == "9" || $this->session->userdata('type') == "14" || $this->session->userdata('type') == "13" || ($this->session->userdata('type') == "11" && $this->session->userdata('code_wilayah') == "" && $this->session->userdata('code_bk_hkk') == "")) {

                              //added by Rizal
                              $downloadIdcardUrl = base_url('admin/file_access/download_idcard/' . $row->id_file);

                              echo ($row->id_file != '')
                                ? $row->idcard . ' <a target="_blank" class="btn btn-danger btn-xs" href="' . $downloadIdcardUrl . '">Download</a>'
                                : $row->idcard;
                            } else {
                              echo ($row->id_file != '') ? $row->idcard : $row->idcard;
                            }
                            ?>
                            (<?php echo $row->idtype; ?>)
                          </label>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-4">
                          <label class="control-label" for="mobilephone">VA</label>
                        </div>
                        <div class="col-md-8">
                          <label class="control-label"><?php echo isset($row->va) ? $row->va : ''; ?></label>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-4">
                          <label class="control-label" for="dob">Date Of Birth</label>
                        </div>
                        <div class="col-md-8">
                          <label class="control-label"><?php echo isset($row->birthplace) ? $row->birthplace : ''; ?> , <?php echo (isset($row->dob) ? date('d-m-Y', strtotime($row->dob)) : $row->dob); ?></label>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-4">
                          <label class="control-label" for="website">Website</label>
                        </div>
                        <div class="col-md-8">
                          <label class="control-label"><?php echo isset($row->website) ? $row->website : ''; ?></label>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-6">

                          <label class="control-label"><?php echo isset($row->is_public) ? ($row->is_public == '1') ? 'Bersedia menerima bahan publikasi' : '' : ''; ?></label>
                        </div>
                        <div class="col-md-6">
                          <label class="control-label"><?php echo isset($row->is_datasend) ? ($row->is_datasend == '1') ? 'Bersedia data pribadi diserahkan ke PII' : '' : ''; ?></label>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-4">
                          <label class="control-label" for="desc">Description</label>
                        </div>
                        <div class="col-md-8">
                          <label class="control-label"><?php echo isset($row->description) ? $row->description : ''; ?></label>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-12">
                          <label class="control-label" for="phone">Phone</label>
                        </div>
                        <div class="col-md-12">
                          <table id="tphone" class="table" style="margin-left:5px;">
                            <tr class="row phone-item" data-id="1">
                              <td class="col-md-4">
                                <?php echo $row->mobilephone; ?>
                                <?php
                                /*if(isset($m_phone)){
							foreach($m_phone as $val){
								if(isset($user_phone[0]->phonetype)){
									if($user_phone[0]->phonetype==$val->id)
									echo $val->desc; 
								}
							}
						}*/
                                foreach ($user_phone as $v) {
                                  if ($v->contact_value != $row->mobilephone) echo ' , ' . $v->contact_value;
                                }
                                ?>
                              </td>
                              <td class="col-md-8">
                                <?php //echo (isset($user_phone[0]->phonenumber)?($user_phone[0]->phonenumber):""); 
                                ?>
                              </td>
                            </tr>
                            <?php
                            /*if(isset($user_phone[1])){
						$i = 0;
						foreach($user_phone as $val){
							if($i>0){
							$typephonex = isset($val->phonetype)?$val->phonetype:"";
							$phonex = isset($val->phonenumber)?$val->phonenumber:"";
							?>
							<tr class="row phone-item" data-id="<?php echo $i+1;?>" >
							<td class="col-md-4">
								<?php
								if(isset($m_phone)){
									foreach($m_phone as $val2){
									if($val2->id == $typephonex)
										echo $val2->desc; 
									}
								}
								?>
								
							</td>
							<td class="col-md-8">
								<?php echo $phonex; ?>
							</td>
							<td class="td-action">
							</td>
							</tr>
							<?php
							}
							$i++;
						}
					}*/
                            ?>

                          </table>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-12">
                          <label class="control-label" for="email">Email</label>
                        </div>
                        <div class="col-md-12">
                          <table id="temail" class="table" style="margin-left:5px;">
                            <tr class="row email-item" data-id="1">
                              <td class="col-md-4">
                                <?php echo $emailx; ?>
                                <?php
                                /*if(isset($m_email)){
								foreach($m_email as $val){
									if(isset($user_email[0]->emailtype)){
									if($user_email[0]->emailtype==$val->id)
										echo $val->desc; 
									}
								}
							}*/
                                foreach ($user_email as $v) {
                                  if ($v->contact_value != $emailx) echo ' , ' . $v->contact_value;
                                }
                                ?>
                              </td>
                              <td class="col-md-8">
                                <?php //echo (isset($user_email[0]->email)?($user_email[0]->email):""); 
                                ?>
                              </td>
                            </tr>
                            <?php
                            /*if(isset($user_email[1])){
						$i = 0;
						foreach($user_email as $val){
							if($i>0){
							$typeemailx = isset($val->emailtype)?$val->emailtype:"";
							$emailx = isset($val->email)?$val->email:"";
							?>
							<tr class="row email-item" data-id="<?php echo $i+1;?>" >
							<td class="col-md-4">
								<?php
								if(isset($m_email)){
									foreach($m_email as $val2){
										if($val2->id == $typeemailx){
											echo $val2->desc; 
										}
									}
								}
								?>
								</select>
							</td>
							<td class="col-md-8">
								<?php echo $emailx; ?>
							</td>
							<td class="td-action">
							</td>
							</tr>
							<?php
							}
							$i++;
						}
					}*/
                            ?>

                          </table>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-12">
                          <label class="control-label" for="address">Address</label>
                        </div>
                        <div class="col-md-12">
                          <table id="taddress" class="table" style="margin-left:5px;">
                            <tr class="row address-item" data-id="1">
                              <td>
                                <table class="table" style="margin-left:5px;background-color:unset;">
                                  <tr class="row">
                                    <td class="col-md-4">
                                      <?php
                                      if (isset($m_address)) {
                                        foreach ($m_address as $val) {
                                          if (isset($user_address[0]->addresstype)) {
                                            if ($user_address[0]->addresstype == $val->id)
                                              echo $val->desc;
                                          }
                                        }
                                      }
                                      ?>
                                    </td>
                                    <td class="col-md-8">
                                      <?php echo (isset($user_address[0]->address) ? ($user_address[0]->address) : ""); ?>
                                    </td>
                                  </tr>
                                  <tr class="row">
                                    <td class="col-md-4">
                                      <?php echo (isset($user_address[0]->is_mailing) ? ($user_address[0]->is_mailing == 1 ? "Mailing Address" : "") : ""); ?>
                                    </td>
                                    <td class="col-md-8">
                                      <?php echo (isset($user_address[0]->city) ? ($user_address[0]->city) : ""); ?>
                                    </td>
                                  </tr>
                                  <tr class="row">
                                    <td class="col-md-4">
                                    </td>
                                    <td class="col-md-8">
                                      <?php echo (isset($user_address[0]->province) ? ($user_address[0]->province) : ""); ?>
                                    </td>
                                  </tr>
                                  <tr class="row">
                                    <td class="col-md-4">
                                    </td>
                                    <td class="col-md-8">
                                      <?php echo (isset($user_address[0]->zipcode) ? ($user_address[0]->zipcode) : ""); ?>
                                    </td>
                                  </tr>
                                </table>
                              </td>
                            </tr>

                            <?php
                            if (isset($user_address[1])) {
                              $i = 0;
                              foreach ($user_address as $val) {
                                if ($i > 0) {
                                  $typeaddressx = isset($val->addresstype) ? $val->addresstype : "";
                                  $addressx = isset($val->address) ? $val->address : "";
                                  $addressphonex = isset($val->notelp) ? $val->notelp : "";
                                  $addresszipx = isset($val->zipcode) ? $val->zipcode : "";
                                  $provincex = isset($val->province) ? $val->province : "";
                                  $cityx = isset($val->city) ? $val->city : "";
                                  $mailingx = isset($val->is_mailing) ? $val->is_mailing : "";
                            ?>
                                  <tr class="row address-item" data-id="<?php echo $i + 1; ?>">
                                    <td>
                                      <table class="table" style="margin-left:5px;background-color:unset;">
                                        <tr class="row">
                                          <td class="col-md-4">
                                            <?php
                                            if (isset($m_address)) {
                                              foreach ($m_address as $val2) {
                                                if ($val2->id == $typeaddressx) {
                                                  echo $val2->desc;
                                                }
                                              }
                                            }
                                            ?>
                                          </td>
                                          <td class="col-md-8">
                                            <?php echo $addressx; ?>
                                          </td>
                                          <td class="td-action"></td>
                                        </tr>
                                        <tr class="row">
                                          <td class="col-md-4">
                                            <label class="form-check-label">
                                              <?php echo (isset($val->is_mailing) ? ($val->is_mailing == 1 ? "Mailing Address" : "") : ""); ?>
                                            </label>
                                          </td>
                                          <td class="col-md-8">
                                            <?php echo $cityx; ?>
                                          </td>
                                        </tr>
                                        <tr class="row">
                                          <td class="col-md-4">
                                          </td>
                                          <td class="col-md-8">
                                            <?php echo $provincex; ?>
                                          </td>
                                        </tr>
                                        <tr class="row">
                                          <td class="col-md-4">
                                          </td>
                                          <td class="col-md-8">
                                            <?php echo $addresszipx; ?>
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

                        </div>
                      </div>


                    </fieldset>
                  </div>

                </div>
              </div>



              <div class="bs-callout bs-callout-danger">
                <h4>Pengalaman Kerja/Profesional</h4>
                <table class="table" style="margin-left:5px;">
                  <?php
                  $i = 0;
                  foreach ($user_exp as $val) {
                    if ($i == 0) {
                  ?>
                      <tr class="row">
                        <td>Perusahaan </td>
                        <td>Jabatan/Tugas</td>
                        <td>Lokasi</td>
                        <td>Periode </td>
                        <td>Nama Aktifitas/Kegiatan/Proyek</td>
                        <td>Uraian Singkat Tugas dan Tanggung Jawab Profesional</td>
                        <th>Dokumen pendukung </th>
                      </tr>
                    <?php
                    }
                    ?>
                    <tr class="row">
                      <td><label class="control-label"><?php echo isset($val->company) ? $val->company : ''; ?></label> </td>
                      <td><label class="control-label"><?php echo isset($val->title) ? $val->title : ''; ?></label> </td>
                      <td><label class="control-label"><?php echo isset($val->location) ? $val->location : ''; ?> <?php echo isset($val->provinsi) ? $val->provinsi : ''; ?> <?php echo isset($val->negara) ? $val->negara : ''; ?></label> </td>
                      <td><label class="control-label">
                          <?php echo isset($val->startyear) ? $val->startyear : ''; ?>
                          <?php echo isset($val->startmonth) ? $val->startmonth : ''; ?> -
                          <?php echo isset($val->is_present) ? ($val->is_present == '1') ? 'present' : (isset($val->endyear) ? $val->endyear : '') . ' ' . (isset($val->endmonth) ? $val->endmonth : '') : ''; ?>
                        </label></td>
                      <td><label class="control-label"><?php echo isset($val->actv) ? $val->actv : ''; ?></label> </td>
                      <td><label class="control-label"><?php echo isset($val->description) ? $val->description : ''; ?></label> </td>

                      <!-- added by Rizal -->
                      <?php $downloadExpUrl = base_url('admin/file_access/exp_file/' . $val->attachment); ?>
                      <td>
                        <label class="control-label">
                          <?php
                          $type         = $this->session->userdata('type');
                          $code_wilayah = $this->session->userdata('code_wilayah');
                          $code_bk_hkk  = $this->session->userdata('code_bk_hkk');

                          // Daftar role yang boleh akses
                          $akses = in_array($type, ["0", "1", "2", "9", "12", "13", "14"])
                            || ($type == "11" && $code_wilayah == "" && $code_bk_hkk == "");

                          if ($akses && !empty($val->attachment)) : ?>
                            <a target="_blank" class="btn btn-danger" href="<?php echo $downloadExpUrl; ?>">
                              <?php echo $val->attachment; ?>
                            </a>
                          <?php endif; ?>
                        </label>
                      </td>

                    </tr>
                  <?php
                    $i++;
                  }
                  ?>
                </table>
              </div>

              <div class="bs-callout bs-callout-danger">
                <h4>Pendidikan</h4>
                <table class="table" style="margin-left:5px;">
                  <?php
                  $i = 0;
                  foreach ($user_edu as $val) {
                    if ($i == 0) {
                  ?>
                      <tr class="row">
                        <td>Tipe Pendidikan</td>
                        <td>Institusi / Universitas</td>
                        <td>Tahun</td>
                        <td>Tingkat Pendidikan</td>
                        <th>Fakultas</th>
                        <th>Jurusan/Kejuruan/<br />Nomor Sertifikat</th>
                        <th>IPK/Nilai</th>
                        <th>Gelar</th>
                        <th>Aktivitas dan kegiatan sosial</th>
                        <th>Deskripsi</th>
                        <th>Dokumen pendukung</th>
                      </tr>
                    <?php
                    }
                    ?>
                    <tr class="row">
                      <td><label class="control-label"><?php echo isset($val->type) ? ($val->type == "1" ? "Akademis" : "Profesi") : ''; ?></label> </td>
                      <td><label class="control-label"><?php echo isset($val->school) ? $val->school : ''; ?></label> </td>
                      <td><label class="control-label">
                          <?php echo isset($val->startdate) ? $val->startdate : ''; ?>
                          <?php echo isset($val->enddate) ? $val->enddate : ''; ?>
                        </label></td>
                      <td><label class="control-label"><?php echo isset($val->degree) ? $val->degree : ''; ?></label> </td>
                      <td><label class="control-label"><?php echo isset($val->mayor) ? $val->mayor : ''; ?></label> </td>
                      <td><label class="control-label"><?php echo isset($val->fieldofstudy) ? $val->fieldofstudy : ''; ?></label> </td>
                      <td><label class="control-label"><?php echo isset($val->score) ? $val->score : ''; ?></label> </td>
                      <td><label class="control-label">

                          <?php echo isset($val->title_prefix) ? $val->title_prefix : ''; ?>

                          <?php echo isset($val->title) ? $val->title : ''; ?>

                        </label> </td>
                      <td><label class="control-label"><?php echo isset($val->activities) ? $val->activities : ''; ?></label> </td>
                      <td><label class="control-label"><?php echo isset($val->description) ? $val->description : ''; ?></label> </td>

                      <!-- added by Rizal -->
                      <?php $downloadEduUrl = base_url('admin/file_access/edu_file/' . $val->attachment); ?>
                      <td>
                        <label class="control-label">
                          <?php
                          $type = $this->session->userdata('type');
                          $code_wilayah = $this->session->userdata('code_wilayah');
                          $code_bk_hkk  = $this->session->userdata('code_bk_hkk');

                          $akses = in_array($type, ["0", "1", "2", "9", "12", "13", "14"])
                            || ($type == "11" && $code_wilayah == "" && $code_bk_hkk == "");

                          if ($akses && !empty($val->attachment)) : ?>
                            <a target="_blank" href="<?php echo $downloadEduUrl; ?>" class="btn btn-danger btn-xs">
                              <?php echo $val->attachment; ?>
                            </a>
                          <?php endif; ?>
                        </label>
                      </td>

                    </tr>
                  <?php
                    $i++;
                  }
                  ?>
                </table>
              </div>

              <div class="bs-callout bs-callout-danger">
                <h4>Sertifikasi Profesional</h4>

                <table class="table" style="margin-left:5px;">
                  <?php
                  $i = 0;
                  foreach ($user_cert as $val) {
                    if ($i == 0) {
                  ?>
                      <tr class="row">
                        <td>Nama Sertifikasi</td>
                        <td>Otoritas Sertifikasi</td>
                        <td>Nomor lisensi</td>
                        <th>URL sertifikasi</th>
                        <th>Kualifikasi</th>
                        <th>Tanggal</th>
                        <th>Deskripsi</th>
                        <th>Dokumen pendukung</th>
                      </tr>
                    <?php
                    }
                    ?>
                    <tr class="row">
                      <td><label class="control-label"><?php echo isset($val->cert_name) ? $val->cert_name : ''; ?></label> </td>
                      <td><label class="control-label"><?php echo isset($val->cert_auth) ? $val->cert_auth : ''; ?></label> </td>
                      <td><label class="control-label"><?php echo isset($val->lic_num) ? $val->lic_num : ''; ?></label> </td>
                      <td><label class="control-label"><?php echo isset($val->cert_url) ? $val->cert_url : ''; ?></label> </td>
                      <td><label class="control-label"><?php echo isset($val->cert_title) ? $val->cert_title : ''; ?></label> </td>
                      <td><label class="control-label">
                          <?php echo isset($val->startyear) ? $val->startyear : ''; ?>
                          <?php echo isset($val->startmonth) ? $val->startmonth : ''; ?> -
                          <?php echo isset($val->is_present) ? ($val->is_present == '1') ? 'present' : (isset($val->endyear) ? $val->endyear : '') . ' ' . (isset($val->endmonth) ? $val->endmonth : '') : ''; ?>
                        </label></td>
                      <td><label class="control-label"><?php echo isset($val->description) ? $val->description : ''; ?></label> </td>

                      <!-- added by Rizal -->
                      <?php $downloadCertUrl = base_url('admin/file_access/cert_file/' . $val->attachment); ?>
                      <td>
                        <label class="control-label">
                          <?php if (!empty($val->attachment)) : ?>
                            <a target="_blank" class="btn btn-danger" href="<?php echo $downloadCertUrl; ?>">
                              <?php echo $val->attachment; ?>
                            </a>
                          <?php endif; ?>
                        </label>
                      </td>

                    </tr>
                  <?php
                    $i++;
                  }
                  ?>
                </table>

              </div>

              <?php /* ?>
	  
	  <div class="bs-callout bs-callout-danger">
        <h4>Organization</h4>
		
		<table class="table" style="margin-left:5px;">
		<?php 
		$i = 0;
		foreach($user_org as $val) {
		if($i == 0){
			?>
			<tr class="row">
			<td>Organization</td>
			<td>Position(s) Held</td>
			<td>Occupation</td>
			<th>Time Period</th>
			<th>Description</th>
			</tr>
			<?php
		}
		?>
			<tr class="row">
			<td><label class="control-label"><?php echo isset($val->organization)?$val->organization:''; ?></label> </td>
			<td><label class="control-label"><?php echo isset($val->position)?$val->position:''; ?></label> </td>
			<td><label class="control-label"><?php echo isset($val->occupation)?$val->occupation:''; ?></label> </td>	
			<td><label class="control-label">
			<?php echo isset($val->startyear)?$val->startyear:''; ?>
			<?php echo isset($val->startmonth)?$val->startmonth:''; ?> -
			<?php echo isset($val->is_present)?($val->is_present=='1')?'present':(isset($val->endyear)?$val->endyear:'').' '.(isset($val->endmonth)?$val->endmonth:''):''; ?>
			</label></td>
			<td><label class="control-label"><?php echo isset($val->description)?$val->description:''; ?></label> </td>
			</tr>
		<?php 
		$i++;
		} 
		?>
		</table>
				
		
	  </div>
	  
	  <div class="bs-callout bs-callout-danger">
        <h4>Award</h4>
		
		<table class="table" style="margin-left:5px;">
		<?php 
		$i = 0;
		foreach($user_award as $val) {
		if($i == 0){
			?>
			<tr class="row">
			<td>Name</td>
			<td>Issue / Filling Date</td>
			<th>Description</th>
			</tr>
			<?php
		}
		?>
			<tr class="row">
			<td><label class="control-label"><?php echo isset($val->name)?$val->name:''; ?></label> </td>
			<td><label class="control-label"><?php echo isset($val->issue)?$val->issue:''; ?></label> </td>
			<td><label class="control-label"><?php echo isset($val->description)?$val->description:''; ?></label> </td>
			</tr>
		<?php 
		$i++;
		} 
		?>
		</table>
		
	  </div>
	  
	  <div class="bs-callout bs-callout-danger">
        <h4>Course</h4>
		
		<table class="table" style="margin-left:5px;">
		<?php 
		$i = 0;
		foreach($user_course as $val) {
		if($i == 0){
			?>
			<tr class="row">
			<td>Name</td>
			<td>Period</td>
			<td>Hour</td>
			<th>Organization</th>
			</tr>
			<?php
		}
		?>
			<tr class="row">
			<td><label class="control-label"><?php echo isset($val->coursename)?$val->coursename:''; ?></label> </td>	
			<td><label class="control-label">
			<?php echo isset($val->startyear)?$val->startyear:''; ?>
			<?php echo isset($val->startmonth)?$val->startmonth:''; ?> 
			</label></td>
			<td><label class="control-label"><?php echo isset($val->hour)?$val->hour:''; ?></label> </td>
			<td><label class="control-label"><?php echo isset($val->courseorg)?$val->courseorg:''; ?></label> </td>
			</tr>
		<?php 
		$i++;
		} 
		?>
		</table>
		
	  </div>
	  
	  <div class="bs-callout bs-callout-danger">
        <h4>Profesional Qualification</h4>
		
		<table class="table" style="margin-left:5px;">
		<?php 
		$i = 0;
		foreach($user_prof as $val) {
		if($i == 0){
			?>
			<tr class="row">
			<td>Organization</td>
			<td>Type</td>
			<td>Period</td>
			<th>Position</th>
			<th>Subject</th>
			<th>Description</th>
			</tr>
			<?php
		}
		?>
			<tr class="row">
			<td><label class="control-label"><?php echo isset($val->organization)?$val->organization:''; ?></label> </td>
			<td><label class="control-label">
			
			<?php
			if(isset($m_proftype)){
				foreach($m_proftype as $val2){
				if($val2->id == $val->type){
				?>
				<?php echo isset($val2->desc)?$val2->desc:''; ?>
			
				<?php
				}
				}
			}
			?>
			
			
			</label> </td>
			<td><label class="control-label">
			<?php echo isset($val->startyear)?$val->startyear:''; ?>
			<?php echo isset($val->startmonth)?$val->startmonth:''; ?> 
			</label></td>
			<td><label class="control-label"><?php echo isset($val->position)?$val->position:''; ?></label> </td>	
			<td><label class="control-label"><?php echo isset($val->subject)?$val->subject:''; ?></label> </td>	
			<td><label class="control-label"><?php echo isset($val->description)?$val->description:''; ?></label> </td>
			</tr>
		<?php 
		$i++;
		} 
		?>
		</table>
		
	  </div>
	  
	  <div class="bs-callout bs-callout-danger">
        <h4>Publication</h4>
		
		<table class="table" style="margin-left:5px;">
		<?php 
		$i = 0;
		foreach($user_publication as $val) {
		if($i == 0){
			?>
			<tr class="row">
			<td>Period</td>
			<td>Topic</td>
			<td>Type</td>
			<td>Media</td>
			<th>Journals</th>
			<th>Event Name</th>
			<th>Description</th>
			</tr>
			<?php
		}
		?>
			<tr class="row">
			
			<td><label class="control-label">
			<?php echo isset($val->startyear)?$val->startyear:''; ?>
			<?php echo isset($val->startmonth)?$val->startmonth:''; ?> 
			</label></td>
			<td><label class="control-label"><?php echo isset($val->topic)?$val->topic:''; ?></label> </td>
			<td><label class="control-label">
			
			<?php
			if(isset($m_publictype)){
				foreach($m_publictype as $val2){
				if($val2->id == $val->type){
				?>
				<?php echo isset($val2->desc)?$val2->desc:''; ?>
			
				<?php
				}
				}
			}
			?>
			
			
			</label> </td>
			<td><label class="control-label"><?php echo isset($val->media)?$val->media:''; ?></label> </td>	
			<td><label class="control-label">
			<?php
			if(isset($m_publicjurnal)){
				foreach($m_publicjurnal as $val2){
				if($val2->id == $val->journal){
				?>
				<?php echo isset($val2->desc)?$val2->desc:''; ?>
			
				<?php
				}
				}
			}
			?>
			</label> </td>	
			<td><label class="control-label"><?php echo isset($val->event)?$val->event:''; ?></label> </td>	
			<td><label class="control-label"><?php echo isset($val->description)?$val->description:''; ?></label> </td>
			</tr>
		<?php 
		$i++;
		} 
		?>
		</table>
		
	  </div>
	  
	   <div class="bs-callout bs-callout-danger">
        <h4>Skill & Language</h4>
		
		<table class="table" style="margin-left:5px;">
		<?php 
		$i = 0;
		foreach($user_skill as $val) {
		if($i == 0){
			?>
			<tr class="row">
			<td>Name</td>
			<td>Proficiency</td>
			<td>Description</td>
			</tr>
			<?php
		}
		?>
			<tr class="row">
			<td><label class="control-label"><?php echo isset($val->name)?$val->name:''; ?></label> </td>	
			<td><label class="control-label"><?php echo isset($val->proficiency)?$val->proficiency:''; ?></label> </td>
			<td><label class="control-label"><?php echo isset($val->description)?$val->description:''; ?></label> </td>
			</tr>
		<?php 
		$i++;
		} 
		?>
		</table>
		
	  </div>
	  
	  <div class="bs-callout bs-callout-danger">
        <h4>Registration</h4>
		
		<?php 
		$i = 0;
		foreach($user_reg as $val) {
			$i = isset($val->id)?$val->id:'';
		?>
			<span class="list-group-item registration-item" data-id="<?php echo $i; ?>">
			<button type="button" style="float:right;text-align:right;" class="editreg" data-id="<?php echo $i; ?>"><img src="<?php echo base_url();?>assets/images/edit.png" /></button>
				
				<p class="list-group-item-text">
				
				<?php
				if(isset($m_fieldofexpert)){
					foreach($m_fieldofexpert as $val2){
					if($val2->id == $val->fieldofexpert){
					?>
					<?php echo isset($val2->desc)?$val2->desc:''; ?><input type="hidden" id="fieldofexpert<?php echo $i; ?>" name="fieldofexpert[<?php echo $i; ?>]" value="<?php echo (isset($val2->id)?$val2->id:''); ?>">
				
					<?php
					}
					}
				}
				?>
				
				</p>
				<p class="list-group-item-text">
				<?php
				if(isset($m_subfield)){
					foreach($m_subfield as $val2){
					if($val2->id == $val->subfield){
					?>
					<?php echo isset($val2->desc)?$val2->desc:''; ?><input type="hidden" id="subfield<?php echo $i; ?>" name="subfield[<?php echo $i; ?>]" value="<?php echo (isset($val2->id)?$val2->id:''); ?>">
				
					<?php
					}
					}
				}
				?>
				</p>
				<p class="list-group-item-text">
				<?php
				if(isset($m_accauth)){
					foreach($m_accauth as $val2){
					if($val2->id == $val->accauth){
					?>
					<?php echo isset($val2->desc)?$val2->desc:''; ?><input type="hidden" id="accauth<?php echo $i; ?>" name="accauth[<?php echo $i; ?>]" value="<?php echo (isset($val2->id)?$val2->id:''); ?>">
				
					<?php
					}
					}
				}
				?>
				</p>
				<p class="list-group-item-text">
				<?php echo isset($val->description)?$val->description:''; ?> <input type="hidden" id="desc2<?php echo $i; ?>" name="desc2[<?php echo $i; ?>]" value="<?php echo isset($val->description)?$val->description:''; ?>"><br />
				</p>
				<p class="list-group-item-text">
				<?php
				if(($val->document!='')){
				?>
				<a href="<?php echo base_url(); ?>/assets/uploads/<?php echo isset($val->document)?$val->document:''; ?>"  class="ava_discus" id="filedoc<?php echo $i; ?>"><?php echo isset($val->document)?$val->document:''; ?></a>	
				<?php
				}
				?>
				</p>
			</span>
			
		<?php 
		$i++;
		} 
		?>
		
	  </div>
	  
	  <?php */ ?>

            </div>

          </div>
        </div>



        <style>
          h4 {
            text-align: center;
            font-weight: bold;
          }
        </style>



        <!-- this row will not appear when printing -->
        <div class="row no-print">
          <div class="col-xs-12">

            <?php if ($this->session->userdata('type') == "0" || $this->session->userdata('type') == "2" || ($this->session->userdata('type') == "11" && $this->session->userdata('code_wilayah') == "" && $this->session->userdata('code_bk_hkk') == "")) { ?>
              <a href="<?php echo base_url('admin/members/update/' . $row->id); ?>"><button class="btn btn-default"><i class="fa fa-edit"></i> Edit This Record</button> </a>
            <?php } ?><!--&nbsp;&nbsp;
        <button class="btn btn-default" onclick="window.print();"><i class="fa fa-print"></i> Print</button>-->
          </div>
        </div>
      </section>
      <!-- /.content -->
    </aside>
    <!-- /.right-side -->
    <?php $this->load->view('admin/common/footer'); ?>