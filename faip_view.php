<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <meta name="_ci_view" content="<?php echo $_ci_view; ?>">
  <title><?php echo $title; ?></title>
  <?php $this->load->view('member/common/meta_tags'); ?>
  <?php $this->load->view('member/common/before_head_close'); ?>
  <style type="text/css">
    .awesome_style {
      font-size: 100px;
    }

    .form-control {
      font-size: 13px;
    }
  </style>
  <link rel="shortcut icon" href="<?php echo base_url(); ?>/assets/images/favicon_16.png">



</head>

<body class="skin-blue">
  <?php $this->load->view('member/common/after_body_open'); ?>
  <?php $this->load->view('member/common/header'); ?>
  <div class="wrapper row-offcanvas row-offcanvas-left">
    <?php $this->load->view('member/common/left_side'); ?>
    <!-- Right side column. Contains the navbar and content of the page -->
    <aside class="right-side">
      <!-- Content Header (Page header) -->
      <section class="content-header">
        <h1> FAIP</h1>
      </section>


      <div class="m-content">
        <span style="color:red;"><?php echo validation_errors(); ?>
          <div class="errormsg"></div>
        </span>
        <form method="post" id="formfaip" data-parsley-validate>
          <input type="hidden" name="id_faip" id="id_faip" />
          <div id="example-manipulation">
            <h3>I.1</h3>
            <section>
              <div class="title">
                <h4>I. DATA PRIBADI</h4>
              </div>
              <label>I.1 Umum</label><br /><br />

              <label>
                Perioda :
                <select id="periodstart" name="periodstart" class="" required="required">
                  <option value="">--Choose--</option>
                  <?php
                  for ($i = 1950; $i <= date('Y') + 1; $i++) {
                    echo '<option value="' . $i . '" ' . ($i == date('Y') ? "selected" : "") . '>' . $i . '</option>'; //'.(($v->addresstype==1)?'selected':'').'
                  }
                  ?>
                </select>
                s/d
                <select id="periodend" name="periodend" class="" required="required">
                  <option value="">--Choose--</option>
                  <?php
                  for ($i = 1950; $i <= date('Y') + 1; $i++) {
                    echo '<option value="' . $i . '" ' . ($i == (date('Y') + 1) ? "selected" : "") . '>' . $i . '</option>'; //'.(($v->addresstype==1)?'selected':'').'
                  }
                  ?>
                </select>
              </label><br /><br />

              <table border="1" width="100%" cellpadding="7">
                <tr>
                  <td><label>Nama Lengkap</label></td>
                  <td><?php $name = trim(strtolower($row->firstname)) . " " . trim(strtolower($row->lastname));
                      echo ucwords($name); ?> </td>
                  <td rowspan="6">
                    <div style="text-align:center;">
                      <img class="img-fluid" height="100" src="<?php echo ($row->photo != '') ? base_url() . 'assets/uploads/' . $row->photo : "" ?>" title="">
                    </div>
                  </td>
                </tr>
                <tr>
                  <td><label>Tempat & Tgl. Lahir</label></td>
                  <td><?php echo ucwords(strtolower($row->birthplace)); ?> , <?php $t = strtotime($row->dob);
                                                                              echo ($row->dob != "0000-00-00") ? date('d F Y', $t) : ""; ?></td>
                </tr>
                <tr>
                  <td><label>No. KTA</label></td>
                  <td><?php echo str_pad($kta->no_kta, 6, '0', STR_PAD_LEFT); ?></td>
                </tr>
                <tr>
                  <td><label>Badan Keahlian</label></td>
                  <td><?php echo $kta->bk; ?> (<?php echo str_pad($kta->code_bk_hkk, 3, '0', STR_PAD_LEFT); ?>)</td>
                </tr>
                <tr>
                  <td><label>Wilayah/Cabang</label></td>
                  <td><?php echo $kta->wil; ?> (<?php echo str_pad($kta->code_wilayah, 4, '0', STR_PAD_LEFT); ?>)</td>
                </tr>
                <tr>
                  <td><label>No. HP</label></td>
                  <td><?php echo $row->mobilephone; ?></td>
                </tr>
                <tr>
                  <td><label>Email</label></td>
                  <td><?php echo $emailx; ?></td>
                </tr>
              </table>

              <?php /*?>
		<table border="1" width="100%" cellpadding="3">
            <tr><td rowspan="4"><label>Alamat</label></td>
            <td colspan="2"><div style="text-align:center;"><label>Rumah</label></div></td><td colspan="2"><div style="text-align:center;"><label>Lembaga (Instansi/Perusahaan)
			</label></div></td></tr>
			
			<tr><td rowspan="2" colspan="2"><label>
			<?php
			$alamat='';
			$city='';
			$zipcode='';
			$wcity='';
			$wzipcode='';
			if(count($user_address)>0){
				foreach($user_address as $v){
					if($v->addresstype==1) {
						$alamat=$v->address.' '.$v->province;
						$city=$v->city;
						$zipcode=$v->zipcode;
					}
					else if($v->addresstype==2) {
						$wcity=$v->city;
						$wzipcode=$v->zipcode;
					}
				}
			}
			?>
			<textarea name="alamat" id="alamat" rows="3" class="form-control"><?php echo $alamat;?></textarea></label></td>
            <td colspan="2"><label>Nama Lembaga : </label>&emsp;&emsp;&emsp;<input type="text" name="lembaga" id="lembaga" /></td></tr>
			
			<tr><td colspan="2"><label>Jabatan di Lembaga :	</label>&emsp;<input type="text" name="jabatan" id="jabatan" /></td></tr>
			
			<tr><td><label>Kota : </label>&emsp;<input type="text" value="<?php echo $city;?>" name="kota1" id="kota1" class="form-control "/></td>
            <td><label>Kode Pos : </label>&emsp;<input type="text" value="<?php echo $zipcode;?>" name="zip1" id="zip1"/></td>
			<td><label>Kota : </label>&emsp;<input type="text" value="<?php echo $wcity;?>" name="kota2" id="kota2" class="form-control "/></td>
			<td><label>Kode Pos : </label>&emsp;<input type="text" value="<?php echo $wzipcode;?>" name="zip2" id="zip2"/></td></tr>
        </table>
		<?php */ ?>

              <table border="1" width="100%" cellpadding="3" id="tala">
                <thead>
                  <tr>
                    <th colspan="5">
                      <div style="text-align:center;"><label>Alamat</label></div>
                    </th>
                  </tr>
                  <tr>
                    <th>
                      <div style="text-align:center;"><label>Tipe</label></div>
                    </th>
                    <th>
                      <div style="text-align:center;"><label>Alamat</label></div>
                    </th>
                    <th>
                      <div style="text-align:center;"><label>Kota</label></div>
                    </th>
                    <th>
                      <div style="text-align:center;"><label>Kode Pos</label></div>
                    </th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  if (count($user_address) > 0) {
                    $i = 0;
                    $tableID = 'ala';
                    foreach ($user_address as $v) {
                      $tipe = 'Other';
                      if ($v->addresstype == 1) {
                        $tipe = 'Home';
                      } else if ($v->addresstype == 2) {
                        $tipe = 'Work';
                      }
                      $alamat = $v->address; //.' '.$v->province;

                      echo '<tr class=" ' . $tableID . '-item" data-id="' . $i . '">';
                      echo '<td><select id="addr_type' . $i . '" name="addr_type[]" class="form-control input-md" required="">
							<option value="">--Choose--</option>
							<option value="1" ' . (($v->addresstype == 1) ? 'selected' : '') . '>Home</option>
							<option value="2" ' . (($v->addresstype == 2) ? 'selected' : '') . '>Work</option>
							<option value="3" ' . (($v->addresstype == 3) ? 'selected' : '') . '>Other</option>
							</select></td>';
                      echo '<td><input type="text" value="' . $alamat . '" name="addr_desc[]" id="addr_desc' . $i . '" class="form-control "/></td>';
                      echo '<td><input type="text" value="' . $v->city . '" name="addr_loc[]" id="addr_loc' . $i . '" class="form-control "/></td>';
                      echo '<td><input type="text" value="' . $v->zipcode . '" name="addr_zip[]" id="addr_zip' . $i . '" class="form-control "/></td>';

                      echo '<td class="td-action">'
                        . '<button type="button" class="btn btn-danger btn-xs ' . $tableID . '-item-remove-button" data-id="' . $i . '">'
                        . '<i class="fa fa-trash-o fa-fw"></i>X'
                        . '</button>'
                        . '</td>';

                      echo '</tr>';
                      $i++;
                    }
                  }
                  ?>

                </tbody>
              </table>

              <div class="col-md-12" style="padding-bottom:20px;">
                <button type="button" class="btn btn-primary" onclick="add111('ala')">+ Tambah Baru</button>
              </div>

              <table border="1" width="100%" cellpadding="3" id="twor">
                <thead>
                  <tr>
                    <th colspan="5">
                      <div style="text-align:center;"><label>Lembaga (Instansi/Perusahaan)</label></div>
                    </th>
                  </tr>
                  <tr>
                    <th>
                      <div style="text-align:center;"><label>Nama</label></div>
                    </th>
                    <th>
                      <div style="text-align:center;"><label>Jabatan</label></div>
                    </th>
                    <th>
                      <div style="text-align:center;"><label>Kota</label></div>
                    </th>
                    <th>
                      <div style="text-align:center;"><label>Kode Pos</label></div>
                    </th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  if (count($user_lembaga) > 0) {
                    $tableID = 'wor';
                    $i = 0;

                    foreach ($user_lembaga as $v) {
                      echo '<tr class=" ' . $tableID . '-item" data-id="' . $i . '">';
                      echo '<td><input type="text" value="' . $v->company . '" name="exp_name[]" id="exp_name' . $i . '" class="form-control "/></td>';
                      echo '<td><input type="text" value="' . $v->title . '" name="exp_desc[]" id="exp_desc' . $i . '" class="form-control "/></td>';
                      echo '<td><input type="text" value="' . $v->location . '" name="exp_loc[]" id="exp_loc' . $i . '" class="form-control "/></td>';
                      echo '<td><input type="text" value="" name="exp_zip[]" id="exp_zip' . $i . '" class="form-control "/></td>';

                      echo '<td class="td-action">'
                        . '<button type="button" class="btn btn-danger btn-xs ' . $tableID . '-item-remove-button" data-id="' . $i . '">'
                        . '<i class="fa fa-trash-o fa-fw"></i>X'
                        . '</button>'
                        . '</td>';

                      echo '</tr>';
                      $i++;
                    }
                  }
                  ?>
                </tbody>
              </table>

              <div class="col-md-12" style="padding-bottom:20px;">
                <button type="button" class="btn btn-primary" onclick="add112('wor')">+ Tambah Baru</button>
              </div>

              <?php
              /*
		$email1='';
		$email2='';
		foreach($user_email as $v){
			if($v->contact_value!=$emailx && $v->contact_value!=$email1) 
			{
				$email1 = $v->contact_value;
				break;
			}
		}
		foreach($user_email as $v){
			if($v->contact_value!=$emailx && $v->contact_value!=$email1) 
			{
				$email2 = $v->contact_value;
				break;
			}
		}
		$user_phone1='';
		$user_phone2='';
		foreach($user_phone as $v){
			if($v->contact_value!=$row->mobilephone && $v->contact_value!=$user_phone1) 
			{
				$user_phone1 = $v->contact_value;
				break;
			}
		}
		foreach($user_phone as $v){
			if($v->contact_value!=$row->mobilephone && $v->contact_value!=$user_phone1) 
			{
				$user_phone2 = $v->contact_value;
				break;
			}
		}
		?>
		
		<table border="1" width="100%" cellpadding="5">
            <tr><td rowspan="4"><label>Komunikasi</label></td>
            <td><label>Telepon : </label><input type="text" value="<?php echo $user_phone1;?>" name="telp1" id="telp1"/></td><td><label>Faksimili : </label><input type="text" name="fax1" id="fax1"/></td><td><label>Telepon : </label><input type="text" value="<?php echo $user_phone2;?>" name="telp2" id="telp2"/></td><td><label>Faksimili : </label><input type="text" name="fax2" id="fax2"/></td></tr>
			<tr>
            <td><label>Telex : </label>&emsp;<input type="text" name="telx1" id="telx1"/></td><td><label>E-Mail : </label>&emsp;<input type="text" value="<?php echo $email1;?>"name="email1" id="email1"/></td><td><label>Telex : </label>&emsp;<input type="text" name="telx2" id="telx2"/></td><td><label>E-Mail : </label>&emsp;<input type="text" value="<?php echo $email2;?>" name="email2" id="email2"/></td></tr>
			<tr>
            <td><label>No. HP </label></td><td><label> <?php echo $row->mobilephone;?></label></td><td><label>E-mail </label></td><td><label> <?php echo $emailx;?></label></td></tr>
        </table>
		<?php */ ?>

              <table border="1" width="50%" cellpadding="3" id="tpho">
                <thead>
                  <tr>
                    <th colspan="3">
                      <div style="text-align:center;"><label>Komunikasi</label></div>
                    </th>
                  </tr>
                  <tr>
                    <th>
                      <div style="text-align:center;"><label>Tipe</label></div>
                    </th>
                    <th>
                      <div style="text-align:center;"><label>Nomor</label></div>
                    </th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  if (count($user_phone) > 0) {
                    $i = 0;
                    $tableID = 'pho';
                    foreach ($user_phone as $v) {
                      echo '<tr class=" ' . $tableID . '-item" data-id="' . $i . '">';
                      echo '<td>
					<select id="phone_type' . $i . '" name="phone_type[]" class="form-control input-md">
					<option value="">--Choose--</option>
					<option value="mobile_phone" ' . (($v->contact_type == "mobile_phone") ? 'selected' : '') . '>Mobile</option>
					<option value="home_phone" ' . (($v->contact_type == "home_phone") ? 'selected' : '') . '>Home</option>
					<option value="office_phone" ' . (($v->contact_type == "office_phone") ? 'selected' : '') . '>Work</option>
					<option value="main_phone" ' . (($v->contact_type == "main_phone") ? 'selected' : '') . '>Main</option>
					<option value="workfax_phone" ' . (($v->contact_type == "workfax_phone") ? 'selected' : '') . '>Work Fax</option>
					<option value="homefax_phone" ' . (($v->contact_type == "homefax_phone") ? 'selected' : '') . '>Home Fax</option>
					<option value="other_phone" ' . (($v->contact_type == "other_phone") ? 'selected' : '') . '>Other</option>
					</select></td>';
                      echo '<td><input type="text" value="' . $v->contact_value . '" name="phone_value[]" id="phone_value' . $i . '" class="form-control "/></td>';
                      echo '<td class="td-action">'
                        . '<button type="button" class="btn btn-danger btn-xs ' . $tableID . '-item-remove-button" data-id="' . $i . '">'
                        . '<i class="fa fa-trash-o fa-fw"></i>X'
                        . '</button>'
                        . '</td>';
                      echo '</tr>';
                      $i++;
                    }
                  }
                  ?>
                </tbody>
              </table>

              <div class="col-md-12" style="padding-bottom:20px;">
                <button type="button" class="btn btn-primary" onclick="add113('pho')">+ Tambah Baru</button>
              </div>





              <div class="col-md-12" style="padding-bottom:20px;text-align:center;">
                <button type="button" name="savefaip" value="1" class="btn btn-next savefaip">Save & Continue</button>
              </div>

              <!--<p><a href="javascript:void(0);" onclick="$('#wizard-4').steps('add', { title: $('#title-3').val(), content: $('#text-3').val() });">Add</a></p>
        <p>(*) Mandatory</p>-->
            </section>

            <h3>I.2</h3>
            <section>
              <label>I.2 Pendidikan Formal <span class="comp">(W2)</span></label><br />

              <?php
              $c_school = array();
              //print_r($m_act);
              if (isset($user_edu[0])) {
                $i = 0;
                foreach ($user_edu as $val) {
                  //if($val->degree=='S1' || $val->degree=='S2' || $val->degree=='S3'){
                  /*$c_school[$val->degree]['school'] = $val->school;
					$c_school[$val->degree]['fieldofstudy'] = $val->fieldofstudy;
					$c_school[$val->degree]['enddate'] = $val->enddate;
					$c_school[$val->degree]['score'] = $val->score;
					$c_school[$val->degree]['title'] = $val->title;
					$c_school[$val->degree]['activities'] = $val->activities;
					$c_school[$val->degree]['description'] = $val->description;*/
                  $c_school[]['degree'] = $val->degree;
                  $c_school[]['school'] = $val->school;
                  $c_school[]['mayor'] = $val->mayor;
                  $c_school[]['fieldofstudy'] = $val->fieldofstudy;
                  $c_school[]['enddate'] = $val->enddate;
                  $c_school[]['score'] = $val->score;
                  $c_school[]['title'] = $val->title;
                  $c_school[]['title_prefix'] = $val->title_prefix;
                  $c_school[]['activities'] = $val->activities;
                  $c_school[]['description'] = $val->description;
                  $i++;
                  //}
                }
              }

              ?>

              <?php /* ?>
		<table border="1" width="100%">
            <tr><td><label></label></td>
			<td align="center"><label>S1</label></td>
			<td align="center"><label>S2</label></td>
			<td align="center"><label>S3</label></td>
			</tr>
			<tr><td><label> Nama Perguruan Tinggi</label></td>
			<td><label><input type="text" name="school1" id="school1" value="<?php echo isset($c_school['S1']['school'])?$c_school['S1']['school']:'';?>" /></label></td>
			<td><label><input type="text" name="school2" id="school2" value="<?php echo isset($c_school['S2']['school'])?$c_school['S2']['school']:'';?>"/></label></td>
			<td><label><input type="text" name="school3" id="school3" value="<?php echo isset($c_school['S3']['school'])?$c_school['S3']['school']:'';?>"/></label></td>
			</tr>
			<tr><td><label>  Fakultas</label></td>
			<td><label><input type="text" name="fak1" id="fak1" /></label></td>
			<td><label><input type="text" name="fak2" id="fak2"/></label></td>
			<td><label><input type="text" name="fak3" id="fak3"/></label></td>
			</tr>
			<tr><td><label>  Jurusan</label></td>
			<td><label><input type="text" name="jur1" id="jur1" value="<?php echo isset($c_school['S1']['fieldofstudy'])?$c_school['S1']['fieldofstudy']:'';?>"/></label></td>
			<td><label><input type="text" name="jur2" id="jur2" value="<?php echo isset($c_school['S2']['fieldofstudy'])?$c_school['S2']['fieldofstudy']:'';?>"/></label></td>
			<td><label><input type="text" name="jur3" id="jur3" value="<?php echo isset($c_school['S3']['fieldofstudy'])?$c_school['S3']['fieldofstudy']:'';?>"/></label></td>
			</tr>
			<tr><td><label> Kota</label></td>
			<td><label><input type="text" name="kota1" id="kota1" /></label></td>
			<td><label><input type="text" name="kota2" id="kota2"/></label></td>
			<td><label><input type="text" name="kota3" id="kota3"/></label></td>
			</tr>
			<tr><td><label>  Negara</label></td>
			<td><label><input type="text" name="negara1" id="negara1"/></label></td>
			<td><label><input type="text" name="negara2" id="negara2"/></label></td>
			<td><label><input type="text" name="negara3" id="negara3"/></label></td>
			</tr>
			<tr><td><label>  Tahun Lulus</label></td>
			<td><label><input type="text" name="lulus1" id="lulus1" value="<?php echo isset($c_school['S1']['enddate'])?$c_school['S1']['enddate']:'';?>"/></label></td>
			<td><label><input type="text" name="lulus2" id="lulus2" value="<?php echo isset($c_school['S2']['enddate'])?$c_school['S2']['enddate']:'';?>"/></label></td>
			<td><label><input type="text" name="lulus3" id="lulus3" value="<?php echo isset($c_school['S3']['enddate'])?$c_school['S3']['enddate']:'';?>"/></label></td>
			
			</tr>
			<tr><td><label>  Gelar</label></td>
			<td><label><input type="text" name="title1" id="title1" value="<?php echo isset($c_school['S1']['title'])?$c_school['S1']['title']:'';?>"/></label></td>
			<td><label><input type="text" name="title2" id="title2" value="<?php echo isset($c_school['S2']['title'])?$c_school['S2']['title']:'';?>"/></label></td>
			<td><label><input type="text" name="title3" id="title3" value="<?php echo isset($c_school['S3']['title'])?$c_school['S3']['title']:'';?>"/></label></td>
			
			</tr>
			<tr><td><label>  Judul Tugas Akhir/Skripsi/Tesis/Disertasi</label></td>
			<td><label><input type="text" name="judul1" id="judul1" /></label></td>
			<td><label><input type="text" name="judul2" id="judul2" /></label></td>
			<td><label><input type="text" name="judul3" id="judul3" /></label></td>
			</tr>
			<tr><td><label>  Uraian Singkat Tentang Materi Tugas Akhir/ Skripsi/Disertasi</label></td>
			<td><label><input type="text" name="uraian1" id="uraian1" /></label></td>
			<td><label><input type="text" name="uraian2" id="uraian2" /></label></td>
			<td><label><input type="text" name="uraian3" id="uraian3" /></label></td>
			</tr>
			<tr><td><label>   Nilai Akademik Rata-rata</label></td>
			<td><label><input type="text" name="score1" id="score1" value="<?php echo isset($c_school['S1']['score'])?$c_school['S1']['score']:'';?>"/></label></td>
			<td><label><input type="text" name="score2" id="score2" value="<?php echo isset($c_school['S2']['score'])?$c_school['S2']['score']:'';?>"/></label></td>
			<td><label><input type="text" name="score3" id="score3" value="<?php echo isset($c_school['S3']['score'])?$c_school['S3']['score']:'';?>"/></label></td>
			
			</tr>
			<tr><td><label>   Judicium</label></td>
			<td><label><input type="text" name="judi1" id="judi1" /></label></td>
			<td><label><input type="text" name="judi2" id="judi2" /></label></td>
			<td><label><input type="text" name="judi3" id="judi3" /></label></td>
			</tr>
           
        </table>
		<?php */ ?>


              <table width="100%" id="tedu" class="table">
                <thead>
                  <tr>
                    <th width="5%"><label>No</label></th>
                    <th width="8%"><label>Nama Perguruan Tinggi</label></th>
                    <th width="6%"><label>Tingkat Pendidikan</label></th>
                    <th width="8%"><label>Fakultas</label></th>
                    <th width="7%"><label>Jurusan</label></th>
                    <th width="7%"><label>Kota/ Kabupaten</label></th>
                    <th width="7%"><label>Provinsi</label></th>
                    <th width="7%"><label>Negara</label></th>
                    <th width="5%"><label>Tahun Lulus</label></th>
                    <th width="5%"><label>Gelar</label></th>
                    <th width="10%"><label>Judul Tugas Akhir/Skripsi/Tesis/Disertasi</label></th>
                    <th width="13%"><label>Uraian Singkat Tentang Materi Tugas Akhir/ Skripsi/Tesis/ Disertasi</label></th>
                    <th width="7%"><label>Nilai Akademik Rata-rata</label></th>
                    <!--<th width="7%"><label>Judicium</label></th>-->
                    <th width="5%"><label>Dokumen Pendukung<br /> <span class="red">(Max. 700KB, image atau PDF)</span></label></th>
                  </tr>
                </thead>
                <tbody>
                  <?php

                  //BP
                  $bp_12_p = array();
                  $bp_12_q = array();
                  $bp_12_r = array();
                  if (isset($bp_12[0])) {
                    foreach ($bp_12 as $val) {
                      if ($val->faip_type == "p")
                        $bp_12_p[] = $val;
                      else if ($val->faip_type == "q")
                        $bp_12_q[] = $val;
                      else if ($val->faip_type == "r")
                        $bp_12_r[] = $val;
                    }
                  }
                  //print_r($bp_12_p); echo '<br />';
                  //print_r($bp_12_q); echo '<br />';
                  //print_r($bp_12_r); echo '<br />';







                  if ($c_school != '') {
                    $i = 0;
                    $i = 1;
                    foreach ($user_edu as $val) {
                      //if($val->degree=='S1' || $val->degree=='S2' || $val->degree=='S3'){
                      $tahunlulus = (isset($val->enddate) ? $val->enddate : '');
                      $birthdate_ts = strtotime("$tahunlulus-1-1");
                      $birthdate_ts2 = strtotime(date("Y-m-d"));

                      $diff = abs($birthdate_ts2 - $birthdate_ts);
                      $tempidx = 0;
                      $years = floor($diff / (365 * 60 * 60 * 24));

                      $data['bp_12'] = $this->main_mod->msrwhere('m_bakuan_penilaian', array('faip_num' => '12', 'faip_type' => 'p', 'condition' => $val->degree), 'id', 'desc')->result();
                      //echo $years.'<br />';
                      foreach ($data['bp_12'] as $valbp) {
                        $condition = substr($valbp->formula, 0, 2);

                        if ($condition == "<=") {
                          if ($years <= substr($valbp->formula, 2, 5)) $tempidx = $valbp->value;
                        } else if ($condition == "<") {
                          if ($years < substr($valbp->formula, 2, 5)) $tempidx = $valbp->value;
                        } else if ($condition == ">") {
                          if ($years > substr($valbp->formula, 2, 5)) $tempidx = $valbp->value;
                        } else if ($condition == ">=") {
                          if ($years >= substr($valbp->formula, 2, 5)) $tempidx = $valbp->value;
                        } else if ($condition == "=") {
                          if ($years == substr($valbp->formula, 2, 5)) $tempidx = $valbp->value;
                        }
                        //echo substr($valbp->formula,2,5).'*<br />';
                      }

                      $tempqr = 0;
                      if ($val->score <= 3) $tempqr = 2;
                      else if ($val->score > 3) $tempqr = 3;

                      $p = $tempidx;
                      $q = $tempqr;
                      $r = $tempqr;
                      $t = $p * $q * $r;

                      $tableID = 'edu';

                      echo '
							<tr class=" ' . $tableID . '-item" data-id="' . $i . '" >'
                        . '<td class="">'
                        . $i
                        . '</td>'
                        . '<td class="col-md-2"><input id="12_t' . $i . '" name="12_t[]" value="' . $t . '" type="hidden">'
                        . '<input id="12_school' . $i . '" name="12_school[]" value="' . $val->school . '" type="text" placeholder="" class="form-control input-md " style="width:200px;"  required="">'
                        . '</td>'
                        . '<td class="col-md-2">'
                        . '<select id="12_degree' . $i . '" name="12_degree[]" class="form-control input-md" required="">'
                        . '<option value="">--Choose--</option>';



                      if (isset($m_degree)) {
                        foreach ($m_degree as $val2) {
                          if ($val->type == '1')
                            echo '<option value="' . $val2->EDUCATION_TYPE_ID . '" ' . (isset($val->degree) ? (($val->degree == $val2->EDUCATION_TYPE_ID) ? 'selected="true"' : "") : "") . '>' . $val2->DESCRIPTION . '</option>';
                          else if ($val->type == '2')
                            echo '<option value="' . $val2->EDUCATION_TYPE_ID . '" ' . (isset($val2->EDUCATION_TYPE_ID) ? (($val2->EDUCATION_TYPE_ID == "Profesi") ? 'selected="true"' : "") : "") . '>' . $val2->DESCRIPTION . '</option>';
                        }
                      }

                      echo '</select></td>'
                        . '<td class="col-md-2">'
                        . '<input id="12_fakultas' . $i . '" name="12_fakultas[]" value="' . $val->mayor . '" type="text" placeholder="" class="form-control input-md " style="width:150px;"  required="">'
                        . '</td>'
                        . '<td class="col-md-2">'
                        . '<input id="12_fieldofstudy' . $i . '" name="12_fieldofstudy[]" value="' . $val->fieldofstudy . '" type="text" placeholder="" class="form-control input-md " style="width:150px;"  required="">'
                        . '</td>'
                        . '<td class="col-md-2">'
                        . '<input id="12_kota' . $i . '" name="12_kota[]" value="" type="text" placeholder="" class="form-control input-md " style="width:100px;"  required="">'
                        . '</td>'
                        . '<td class="col-md-2">'
                        . '<input id="12_provinsi' . $i . '" name="12_provinsi[]" value="" type="text" placeholder="" class="form-control input-md " style="width:100px;"  required="">'
                        . '</td>'
                        . '<td class="col-md-2">'
                        . '<input id="12_negara' . $i . '" name="12_negara[]" value="" type="text" placeholder="" class="form-control input-md " style="width:100px;"  required="">'
                        . '</td>'
                        . '<td class="col-md-2">'
                        . '<input id="12_tahunlulus' . $i . '" name="12_tahunlulus[]" value="' . (isset($val->enddate) ? $val->enddate : '') . '" type="text" placeholder="" class="form-control input-md " style="width:70px;"  required="">'
                        . '</td>'
                        . '<td class="col-md-2">'
                        . '<input id="12_title' . $i . '" name="12_title[]" value="' . ($val->title != '' ? $val->title : $val->title_prefix) . '" type="text" placeholder="" class="form-control input-md " style="width:100px;"  required="">'
                        . '</td>'
                        . '<td class="col-md-2">'
                        . '<input id="12_activities' . $i . '" name="12_activities[]" value="' . $val->activities . '" type="text" placeholder="" class="form-control input-md " style="width:200px;"  required="">'
                        . '</td>'
                        . '<td class="col-md-2">'
                        . '<input id="12_description' . $i . '" name="12_description[]" value="' . $val->description . '" type="text" placeholder="" class="form-control input-md " style="width:200px;"  required="">'
                        . '</td>'
                        . '<td class="col-md-2">'

                        /*
								. '<select id="12_score' .$i.'" name="12_score[]" class="input-md" required="">'
								. '<option value="">--Choose--</option>';
									if(isset($bp_12_q)){
										foreach($bp_12_q as $val2){
											echo '<option value="'.$val2->value.'" '.(isset($val->degree)?(($val->degree==$val2->value)?'selected="true"':""):"").'>'.$val2->desc.'</option>';
										}
									}
								echo '</select>'
								*/

                        . '<input id="12_score' . $i . '" name="12_score[]" value="' . $val->score . '" type="text" placeholder="" class="form-control input-md " style="width:70px;"  required="">'


                        . '</td>'
                        //. '<td class="col-md-2">'
                        //. '<input id="12_judicium' .$i.'" name="12_judicium[]" value="" type="text" placeholder="" class="input-md "  required="">'
                        //. '</td>'

                        . '<td class="col-md-8">'
                        . '	<div class="form-group">'
                        . '		<div id="12_avatarattedu' . $i . '">'
                        . '		<input type="hidden" name="12_edu_image_url[]" value="' . ((isset($val->attachment)) ? $val->attachment : '') . '" style="display: inline-block;">'
                        . '		' . ((isset($val->attachment)) ? "<a href='" . base_url() . "/assets/uploads/" . $val->attachment . "' target='_blank' class='ava_discus'>" . $val->attachment . "</a>" : '') . ''
                        . '		</div>'
                        . '		<div id="12_errUploadattedu' . $i . '"></div>'
                        . '		<input type="file" name="12_attedu[]" id="12_attedu' . $i . '" class="form-control input-md" onchange="upload_edu(\'attedu' . $i . '\')">'
                        . '	</div>'
                        . '</td>'

                        . '<td class="td-action">'
                        . '<button type="button" class="btn btn-danger btn-xs ' . $tableID . '-item-remove-button" data-id="' . $i . '">'
                        . '<i class="fa fa-trash-o fa-fw"></i>X'
                        . '</button>'
                        . '</td>'
                        . '</tr>
							';

                      $i++;
                      //}
                    }
                  }
                  ?>
                </tbody>
              </table>
              <div class="col-md-12" style="padding-bottom:20px;">
                <button type="button" class="btn btn-primary" onclick="add12('edu')">+ Tambah Baru</button>
              </div>
              <p>
                Lampirkan transkrip bila ada, apabila perlu tuliskan pada lembar tambahan </p>
              <div class="col-md-12" style="padding-bottom:20px;text-align:center;">
                <button type="button" name="savefaip" value="1" class="btn btn-next savefaip">Save & Continue</button>
              </div>
            </section>

            <h3>I.3</h3>
            <section>
              <label>I.3 Organisasi Profesi & Organisasi Lainnya Yang Dimasuki <span class="comp">(W1)</span></label><br />
              <div style="width:3000px;">
                <table width="100%" id="torg" class="table">
                  <thead>
                    <tr>
                      <th width="4%"><label>No</label></th>
                      <th width="7%"><label>NAMA ORGANISASI</label></th>
                      <th width="7%"><label>JENIS</label></th>
                      <th width="5%"><label>KOTA/KABUPATEN</label></th>
                      <th width="5%"><label>PROVINSI</label></th>
                      <th width="5%"><label>NEGARA</label></th>
                      <th width="10%"><label>PERIODA</label></th>
                      <th width="7%"><label>JABATAN DALAM ORGANISASI</label></th>
                      <th width="7%"><label>TINGKATAN ORGANISASI</label></th>
                      <th width="16%"><label>LINGKUP KEGIATAN ORGANISASI</label></th>
                      <th width="30%"><label>Uraian Singkat Aktifitas</label></th>
                      <th width="5%"><label>Dokumen Pendukung<br /> <span class="red">(Max. 700KB, image atau PDF)</span></label></th>
                      <th width="30%"><label>KLAIM KOMPETENSI </label></th>
                      <!--<th width="5%"><label>KOMPETENSI *)</label></th>-->
                      <th width="5%"><label></label></th>
                    </tr>
                  </thead>
                  <tbody>

                    <?php

                    $bp_13_p = array();
                    $bp_13_q = array();
                    $bp_13_r = array();
                    if (isset($bp_13[0])) {
                      foreach ($bp_13 as $val) {
                        if ($val->faip_type == "p")
                          $bp_13_p[] = $val;
                        else if ($val->faip_type == "q")
                          $bp_13_q[] = $val;
                        else if ($val->faip_type == "r")
                          $bp_13_r[] = $val;
                      }
                    }

                    if (count($user_org) > 0) {
                      $i = 1;

                      foreach ($user_org as $val) {

                        $birthdate_ts = strtotime("$val->startyear-$val->startmonth-1");
                        $birthdate_ts2 = '';
                        if ($val->is_present == "1")
                          $birthdate_ts2 = strtotime(date("Y-m-d"));
                        else
                          $birthdate_ts2 = strtotime("$val->endyear-$val->endmonth-1");
                        $diff = abs($birthdate_ts2 - $birthdate_ts);
                        $tempidx = 0;
                        $years = floor($diff / (365 * 60 * 60 * 24));

                        $data['bp_13'] = $this->main_mod->msrwhere('m_bakuan_penilaian', array('faip_num' => '13', 'faip_type' => 'p', 'condition' => 'Non PII'), 'id', 'desc')->result();

                        foreach ($data['bp_13'] as $valbp) {
                          $condition = substr($valbp->formula, 0, 2);
                          if ($condition == "<=") {
                            if ($years <= substr($valbp->formula, 2, 5)) $tempidx = $valbp->value;
                          } else if ($condition == "<") {
                            if ($years < substr($valbp->formula, 2, 5)) $tempidx = $valbp->value;
                          } else if ($condition == ">") {
                            if ($years > substr($valbp->formula, 2, 5)) $tempidx = $valbp->value;
                          } else if ($condition == ">=") {
                            if ($years >= substr($valbp->formula, 2, 5)) $tempidx = $valbp->value;
                          } else if ($condition == "=") {
                            if ($years == substr($valbp->formula, 2, 5)) $tempidx = $valbp->value;
                          }
                          //echo substr($valbp->formula,2,5).'*<br />';
                        }
                        $p = $tempidx;
                        $q = 0;
                        $r = 0;
                        foreach ($bp_13_q as $val2) {
                          if (trim($val->position) == trim($val2->desc))
                            $q = $val2->value;
                        }

                        foreach ($bp_13_r as $val2) {
                          if (trim($val->tingkat) == trim($val2->desc))
                            $r = $val2->value;
                        }


                        $t = $p * $q * $r;
                        //echo $r.$val->tingkat.'<br />';
                        $tableID = 'org';

                        echo '
							<tr class=" ' . $tableID . '-item" data-id="' . $i . '" ><td class="">'
                          . $i
                          . '</td>'

                          . '<td class="col-md-2"><input id="13_t' . $i . '" name="13_t[]" value="' . $t . '" type="hidden">'
                          . '<input id="13_nama_org' . $i  . '" name="13_nama_org[]" value="' . $val->organization . '" type="text" placeholder="" class="form-control input-md "  required="">'
                          . '</td>'
                          //. '<td class="col-md-2">'
                          //. '<input id="13_occupation' . $i  . '" name="13_occupation[]" value="'.$val->occupation.'" type="text" placeholder="" class="form-control input-md "  required="">'
                          //. '</td>'

                          . '<td class="col-md-2">'
                          . '	<select id="13_jenis' . $i . '" name="13_jenis[]" class="form-control input-md" required="">'
                          . '		<option value="">---</option>'
                          . '		<option value="Organisasi PII" ' . (isset($val->jenis) ? (($val->jenis == "Organisasi PII") ? 'selected="true"' : "") : "") . ' >Organisasi PII</option>'
                          . '		<option value="Organisasi Keinsinyuran Non PII" ' . (isset($val->jenis) ? (($val->jenis == "Organisasi Keinsinyuran Non PII") ? 'selected="true"' : "") : "") . '>Organisasi Keinsinyuran Non PII</option>'
                          . '		<option value="Organisasi Non Keinsinyuran" ' . (isset($val->jenis) ? (($val->jenis == "Organisasi Non Keinsinyuran") ? 'selected="true"' : "") : "") . '>Organisasi Non Keinsinyuran</option>'
                          . '	</select>'
                          . '</td>'

                          . '<td class="col-md-2">'
                          . '<input id="13_tempat' . $i  . '" name="13_tempat[]" value="' . $val->occupation . '" type="text" placeholder="" class="form-control input-md "  required="">'
                          . '</td>'
                          . '<td class="col-md-2">'
                          . '<input id="13_provinsi' . $i  . '" name="13_provinsi[]" value="' . $val->provinsi . '" type="text" placeholder="" class="form-control input-md "  required="">'
                          . '</td>'
                          . '<td class="col-md-2">'
                          . '<input id="13_negara' . $i  . '" name="13_negara[]" value="' . $val->negara . '" type="text" placeholder="" class="form-control input-md "  required="">'
                          . '</td>'


                          . '<td class="col-md-2">'
                          . '	<div class="">'
                          . '	<select id="13_startdate' . $i  . '" name="13_startdate[]" class="form-control input-md" required="">'
                          . '		<option value="">---</option>'
                          . '		<option value="1" ' . (isset($val->startmonth) ? (($val->startmonth == "1") ? 'selected="true"' : "") : "") . ' >Januari</option>'
                          . '		<option value="2" ' . (isset($val->startmonth) ? (($val->startmonth == "2") ? 'selected="true"' : "") : "") . '>Pebruari</option>'
                          . '		<option value="3" ' . (isset($val->startmonth) ? (($val->startmonth == "3") ? 'selected="true"' : "") : "") . '>Maret</option>'
                          . '		<option value="4" ' . (isset($val->startmonth) ? (($val->startmonth == "4") ? 'selected="true"' : "") : "") . '>April</option>'
                          . '		<option value="5" ' . (isset($val->startmonth) ? (($val->startmonth == "5") ? 'selected="true"' : "") : "") . '>Mei</option>'
                          . '		<option value="6" ' . (isset($val->startmonth) ? (($val->startmonth == "6") ? 'selected="true"' : "") : "") . '>Juni</option>'
                          . '		<option value="7" ' . (isset($val->startmonth) ? (($val->startmonth == "7") ? 'selected="true"' : "") : "") . '>Juli</option>'
                          . '		<option value="8" ' . (isset($val->startmonth) ? (($val->startmonth == "8") ? 'selected="true"' : "") : "") . '>Agustus</option>'
                          . '		<option value="9" ' . (isset($val->startmonth) ? (($val->startmonth == "9") ? 'selected="true"' : "") : "") . '>September</option>'
                          . '		<option value="10" ' . (isset($val->startmonth) ? (($val->startmonth == "10") ? 'selected="true"' : "") : "") . '>Oktober</option>'
                          . '		<option value="11" ' . (isset($val->startmonth) ? (($val->startmonth == "11") ? 'selected="true"' : "") : "") . '>Nopember</option>'
                          . '		<option value="12" ' . (isset($val->startmonth) ? (($val->startmonth == "12") ? 'selected="true"' : "") : "") . '>Desember</option>'
                          . '	</select>'
                          . '	<input id="13_startyear' . $i . '" name="13_startyear[]" value="' . $val->startyear . '" type="text" placeholder="Year" class="form-control input-md number datepickeryear" required="">'
                          . '	</div>'
                          . '	<div class="" id="13_ispresent1' . $i . '" ' . (isset($val->is_present) ? (($val->is_present == "1") ? 'style="display:none;"' : "") : "") . '>-</div>'
                          . '	<div class="" id="13_ispresent2' . $i . '" ' . (isset($val->is_present) ? (($val->is_present == "1") ? 'style="display:none;"' : "") : "") . '>'
                          . '	<select id="13_enddate' . $i . '" name="13_enddate[]" class="form-control input-md">'
                          . '		<option value="">---</option>'
                          . '		<option value="1" ' . (isset($val->endmonth) ? (($val->endmonth == "1") ? 'selected="true"' : "") : "") . ' >Januari</option>'
                          . '		<option value="2" ' . (isset($val->endmonth) ? (($val->endmonth == "2") ? 'selected="true"' : "") : "") . '>Pebruari</option>'
                          . '		<option value="3" ' . (isset($val->endmonth) ? (($val->endmonth == "3") ? 'selected="true"' : "") : "") . '>Maret</option>'
                          . '		<option value="4" ' . (isset($val->endmonth) ? (($val->endmonth == "4") ? 'selected="true"' : "") : "") . '>April</option>'
                          . '		<option value="5" ' . (isset($val->endmonth) ? (($val->endmonth == "5") ? 'selected="true"' : "") : "") . '>Mei</option>'
                          . '		<option value="6" ' . (isset($val->endmonth) ? (($val->endmonth == "6") ? 'selected="true"' : "") : "") . '>Juni</option>'
                          . '		<option value="7" ' . (isset($val->endmonth) ? (($val->endmonth == "7") ? 'selected="true"' : "") : "") . '>Juli</option>'
                          . '		<option value="8" ' . (isset($val->endmonth) ? (($val->endmonth == "8") ? 'selected="true"' : "") : "") . '>Agustus</option>'
                          . '		<option value="9" ' . (isset($val->endmonth) ? (($val->endmonth == "9") ? 'selected="true"' : "") : "") . '>September</option>'
                          . '		<option value="10" ' . (isset($val->endmonth) ? (($val->endmonth == "10") ? 'selected="true"' : "") : "") . '>Oktober</option>'
                          . '		<option value="11" ' . (isset($val->endmonth) ? (($val->endmonth == "11") ? 'selected="true"' : "") : "") . '>Nopember</option>'
                          . '		<option value="12" ' . (isset($val->endmonth) ? (($val->endmonth == "12") ? 'selected="true"' : "") : "") . '>Desember</option>'
                          . '	</select>'
                          . '	<input id="13_endyear' . $i . '" name="13_endyear[]" value="' . $val->endyear . '" type="text" placeholder="Year" class="form-control input-md number datepickeryear" >'
                          . '	</div>'
                          . '	<label class="form-check-label"><input type="hidden" name="13_workx[]" value="' . (isset($val->is_present) ? (($val->is_present == "1") ? '1' : "0") : "0") . '"><input type="checkbox" id="13_work' . $i . '" name="13_work[]" class="form-check-input" value="1" data-id="' . $i . '" ' . (isset($val->is_present) ? (($val->is_present == "1") ? 'checked="true"' : "") : "") . '>Masih menjadi anggota</label>'
                          . '</td>'



                          . '<td class="col-md-2">'
                          //. '<input id="13_jabatan' . $i  . '" name="13_jabatan[]" value="'.$val->position.'" type="text" placeholder="" class="form-control input-md "  required="">'

                          . '<select id="13_jabatan' . $i . '" name="13_jabatan[]" style="height: 34px;" required="">'
                          . '<option value="">--Choose--</option>';
                        if (isset($bp_13_q)) {
                          foreach ($bp_13_q as $val2) {
                            echo '<option value="' . $val2->value . '" ' . (isset($val->position) ? ((trim($val->position) == trim($val2->desc)) ? 'selected="true"' : "") : "") . '>' . $val2->desc . '</option>'; //
                          }
                        }
                        echo '</select>'

                          . '</td>'

                          . '<td class="col-md-2">'

                          . '<select id="13_tingkat' . $i . '" name="13_tingkat[]" style="height: 34px;" required="">'
                          . '<option value="">--Choose--</option>';
                        if (isset($bp_13_r)) {
                          foreach ($bp_13_r as $val2) {
                            echo '<option value="' . $val2->value . '" ' . (isset($val->tingkat) ? ((trim($val->tingkat) == trim($val2->desc)) ? 'selected="true"' : "") : "") . '>' . $val2->desc . '</option>'; //
                          }
                        }
                        echo '</select>'

                          . '</td>'

                          . '<td class="col-md-2">'

                          . '<select id="13_lingkup' . $i . '" name="13_lingkup[]" style="height: 34px;" required="">'
                          . '<option value="">--Choose--</option>'
                          . '<option value="Asosiasi Profesi" ' . (isset($val->lingkup) ? (($val->lingkup == "Asosiasi Profesi") ? 'selected="true"' : "") : "") . '>Asosiasi Profesi</option>'
                          . '<option value="Lembaga Pemerintah" ' . (isset($val->lingkup) ? (($val->lingkup == "Lembaga Pemerintah") ? 'selected="true"' : "") : "") . '>Lembaga Pemerintah</option>'
                          . '<option value="Lembaga Pendidikan" ' . (isset($val->lingkup) ? (($val->lingkup == "Lembaga Pendidikan") ? 'selected="true"' : "") : "") . '>Lembaga Pendidikan</option>'
                          . '<option value="Badan Usaha Milik Negara" ' . (isset($val->lingkup) ? (($val->lingkup == "Badan Usaha Milik Negara") ? 'selected="true"' : "") : "") . '>Badan Usaha Milik Negara</option>'
                          . '<option value="Badan Usaha Swasta" ' . (isset($val->lingkup) ? (($val->lingkup == "Badan Usaha Swasta") ? 'selected="true"' : "") : "") . '>Badan Usaha Swasta</option>'
                          . '<option value="Organisasi Kemasyarakatan" ' . (isset($val->lingkup) ? (($val->lingkup == "Organisasi Kemasyarakatan") ? 'selected="true"' : "") : "") . '>Organisasi Kemasyarakatan</option>'
                          . '<option value="Lain-lain" ' . (isset($val->lingkup) ? (($val->lingkup == "Lain-lain") ? 'selected="true"' : "") : "") . '>Lain-lain</option>';
                        echo '</select>'

                          . '</td>'

                          . '<td class="col-md-2">'

                          . '<textarea id="13_aktifitas' . $i . '" name="13_aktifitas[]" class="form-control input-md " style="width:200px;" rows="8">' . $val->description . '</textarea>'

                          . '</td>'

                          . '<td class="col-md-8">'
                          . '	<div class="form-group">'
                          . '		<div id="13_avatarattorg' . $i . '">'
                          . '		<input type="hidden" name="13_org_image_url[]" value="' . ((isset($val->attachment)) ? $val->attachment : '') . '" style="display: inline-block;">'
                          . '		' . ((isset($val->attachment)) ? "<a href='" . base_url() . "/assets/uploads/" . $val->attachment . "' target='_blank' class='ava_discus'>" . $val->attachment . "</a>" : '') . ''
                          . '		</div>'
                          . '		<div id="13_errUploadattorg' . $i . '"></div>'
                          . '		<input type="file" name="13_attorg[]" id="13_attorg' . $i . '" class="form-control input-md" onchange="upload_org(\'attorg' . $i . '\')">'
                          . '	</div>'
                          . '</td>'

                          . '<td class="col-md-2">'

                          //. '<textarea id="13_aktifitas' .$i. '" name="13_aktifitas[]" class="form-control input-md " style="width:200px;" rows="8">'.$val->description.'</textarea>'

                          . '<select id="13_komp' . $i . '" name="13_komp[' . $i . '][]" class="input-md" style="height: 200px;" required="" multiple>'
                          . '<option value="">--Choose--</option>';


                        if (isset($m_act_13)) {
                          $temp = true;
                          foreach ($m_act_13 as $val) {
                            if (strlen($val->value) < 8) {
                              if (!$temp)
                                echo '</optgroup>';
                              echo  '<optgroup label="' . $val->value . ' - ' . $val->title . '">';
                              $temp = true;
                            } else {
                              $temp = false;
                              echo  '<option value="' . $val->value . '" >' . $val->value . ' - ' . $val->title . '</option>';
                            }
                          }
                        }


                        echo '</select>'

                          . '</td>'
                          /*
									. '<td class="col-md-4">'
					
									. '<table width="100%" id="'.$tableID.'komp_' . $i  . '" class="table">'
									. '</table>'
									. '<div class="col-md-12" style="padding-bottom:20px;">'
									. '<button type="button" class="btn btn-primary" onclick="addKomp(\''.$tableID.'komp_' . $i  . '\')">+ Tambah Kompetensi</button>'
									. '</div>'
									. '</td>'
									*/
                          . '<td class="td-action">'
                          . '<button type="button" class="btn btn-danger btn-xs ' . $tableID . '-item-remove-button" data-id="' . $i  . '">'
                          . '<i class="fa fa-trash-o fa-fw"></i>X'
                          . '</button>'
                          . '</td>'
                          . '</tr>
							';

                        $i++;
                      }
                    }
                    ?>

                  </tbody>
                </table>
              </div>
              <div class="col-md-12" style="padding-bottom:20px;">
                <button type="button" class="btn btn-primary" onclick="add13('org')">+ Tambah Baru</button>
              </div>
              <p>Apabila perlu tuliskan pada lembar tambahan</p>
              <p>*) KOMPETENSI: Isi dengan nomor Uraian Kegiatan Kompetensi yang Anda anggap persyaratannya telah terpenuhi dengan aktifitas Anda di sini</p>
              <div class="col-md-12" style="padding-bottom:20px;text-align:center;">
                <button type="button" name="savefaip" value="1" class="btn btn-next savefaip">Save & Continue</button>
              </div>
            </section>
            <h3>I.4</h3>
            <section>
              <label>I.4 Tanda Penghargaan Yang Diterima (kerja tanpa pamrih) <span class="comp">(W1)</span></label><br />
              <div style="width:3000px;">
                <table width="100%" id="tphg" class="table">
                  <thead>
                    <tr>
                      <th width="4%"><label>No</label></th>
                      <th width="7%"><label>NAMA TANDA PENGHARGAAN</label></th>
                      <th width="5%"><label>NAMA LEMBAGA YANG MEMBERIKAN</label></th>
                      <th width="5%"><label>KOTA/KABUPATEN</label></th>
                      <th width="5%"><label>PROVINSI</label></th>
                      <th width="5%"><label>NEGARA</label></th>
                      <th width="10%"><label>TANGGAL TERBIT</label></th>

                      <th width="8%"><label>Penghargaan yang diterima tingkat</label></th>
                      <th width="8%"><label>Penghargaan diberikan oleh lembaga</label></th>
                      <th width="8%" style="display:none;"><label>Berapa banyak Penghargaan yang telah Anda terima ?</label></th>
                      <th width="30%"><label>Uraian Singkat Aktifitas</label></th>
                      <th width="5%"><label>Dokumen Pendukung<br /> <span class="red">(Max. 700KB, image atau PDF)</span></label></th>
                      <th width="30%"><label>KLAIM KOMPETENSI </label></th>
                      <!--<th width="10%"><label>KOMPETENSI *)</label></th>-->
                      <th width="5%"><label></label></th>
                    </tr>
                  </thead>
                  <tbody>

                    <?php

                    $bp_14_p = array();
                    $bp_14_q = array();
                    $bp_14_r = array();
                    if (isset($bp_14[0])) {
                      foreach ($bp_14 as $val) {
                        if ($val->faip_type == "p")
                          $bp_14_p[] = $val;
                        else if ($val->faip_type == "q")
                          $bp_14_q[] = $val;
                        else if ($val->faip_type == "r")
                          $bp_14_r[] = $val;
                      }
                    }

                    if (count($user_award) > 0) {
                      $i = 1;
                      foreach ($user_award as $val) {

                        $years = count($user_award);

                        $data['bp_14'] = $this->main_mod->msrwhere('m_bakuan_penilaian', array('faip_num' => '14', 'faip_type' => 'p'), 'id', 'desc')->result();

                        foreach ($data['bp_14'] as $valbp) {
                          $condition = substr($valbp->formula, 0, 2);
                          if ($condition == "<=") {
                            if ($years <= substr($valbp->formula, 2, 5)) $tempidx = $valbp->value;
                          } else if ($condition == "<") {
                            if ($years < substr($valbp->formula, 2, 5)) $tempidx = $valbp->value;
                          } else if ($condition == ">") {
                            if ($years > substr($valbp->formula, 2, 5)) $tempidx = $valbp->value;
                          } else if ($condition == ">=") {
                            if ($years >= substr($valbp->formula, 2, 5)) $tempidx = $valbp->value;
                          } else if ($condition == "=") {
                            if ($years == substr($valbp->formula, 2, 5)) $tempidx = $valbp->value;
                          }
                          //echo substr($valbp->formula,2,5).'*<br />';
                        }
                        $p = $tempidx;
                        $q = 0;
                        $r = 0;
                        foreach ($bp_14_q as $val2) {
                          if (trim($val->tingkat) == trim($val2->desc))
                            $q = $val2->value;
                        }

                        foreach ($bp_14_r as $val2) {
                          if (trim($val->pemberi) == trim($val2->desc))
                            $r = $val2->value;
                        }


                        $t = $p * $q * $r;

                        $tableID = 'phg';

                        echo '
							<tr class=" ' . $tableID . '-item" data-id="' . $i . '" ><td class="">'
                          . $i
                          . '</td>'

                          . '<td class="col-md-2"><input id="14_t' . $i . '" name="14_t[]" value="' . $t . '" type="hidden">'
                          . '<input id="14_nama' . $i . '" name="14_nama[]" value="' . $val->name . '" type="text" placeholder="" class="form-control input-md "  required="">'
                          . '</td>'
                          . '<td class="col-md-2">'
                          . '<input id="14_lembaga' . $i . '" name="14_lembaga[]" value="' . $val->issue . '" type="text" placeholder="" class="form-control input-md "  required="">'
                          . '</td>'

                          . '<td class="col-md-2">'
                          . '<input id="14_location' . $i . '" name="14_location[]" value="' . $val->location . '" type="text" placeholder="" class="form-control input-md "  required="">'
                          . '</td>'
                          . '<td class="col-md-2">'
                          . '<input id="14_provinsi' . $i . '" name="14_provinsi[]" value="' . $val->provinsi . '" type="text" placeholder="" class="form-control input-md "  required="">'
                          . '</td>'
                          . '<td class="col-md-2">'
                          . '<input id="14_negara' . $i . '" name="14_negara[]" value="' . $val->negara . '" type="text" placeholder="" class="form-control input-md "  required="">'
                          . '</td>'

                          . '<td class="col-md-2">'
                          . '	<div class="">'
                          . '	<select id="14_startdate' . $i . '" name="14_startdate[]" class="form-control input-md" required="">'
                          . '		<option value="">---</option>'
                          . '		<option value="1" ' . (isset($val->startmonth) ? (($val->startmonth == "1") ? 'selected="true"' : "") : "") . ' >Januari</option>'
                          . '		<option value="2" ' . (isset($val->startmonth) ? (($val->startmonth == "2") ? 'selected="true"' : "") : "") . '>Pebruari</option>'
                          . '		<option value="3" ' . (isset($val->startmonth) ? (($val->startmonth == "3") ? 'selected="true"' : "") : "") . '>Maret</option>'
                          . '		<option value="4" ' . (isset($val->startmonth) ? (($val->startmonth == "4") ? 'selected="true"' : "") : "") . '>April</option>'
                          . '		<option value="5" ' . (isset($val->startmonth) ? (($val->startmonth == "5") ? 'selected="true"' : "") : "") . '>Mei</option>'
                          . '		<option value="6" ' . (isset($val->startmonth) ? (($val->startmonth == "6") ? 'selected="true"' : "") : "") . '>Juni</option>'
                          . '		<option value="7" ' . (isset($val->startmonth) ? (($val->startmonth == "7") ? 'selected="true"' : "") : "") . '>Juli</option>'
                          . '		<option value="8" ' . (isset($val->startmonth) ? (($val->startmonth == "8") ? 'selected="true"' : "") : "") . '>Agustus</option>'
                          . '		<option value="9" ' . (isset($val->startmonth) ? (($val->startmonth == "9") ? 'selected="true"' : "") : "") . '>September</option>'
                          . '		<option value="10" ' . (isset($val->startmonth) ? (($val->startmonth == "10") ? 'selected="true"' : "") : "") . '>Oktober</option>'
                          . '		<option value="11" ' . (isset($val->startmonth) ? (($val->startmonth == "11") ? 'selected="true"' : "") : "") . '>Nopember</option>'
                          . '		<option value="12" ' . (isset($val->startmonth) ? (($val->startmonth == "12") ? 'selected="true"' : "") : "") . '>Desember</option>'
                          . '	</select>'
                          . '	<input id="14_startyear' . $i . '" name="14_startyear[]" value="' . $val->startyear . '" type="text" placeholder="Year" class="form-control input-md number datepickeryear" required="">'
                          . '	</div>'
                          . '</td>'


                          . '<td class="col-md-2">'
                          . '<select id="14_tingkat' . $i . '" name="14_tingkat[]" style="height: 34px;" required="">'
                          . '<option value="">--Choose--</option>';
                        if (isset($bp_14_q)) {
                          foreach ($bp_14_q as $val2) {
                            echo '<option value="' . $val2->value . '" ' . (isset($val->tingkat) ? ((trim($val->tingkat) == trim($val2->desc)) ? 'selected="true"' : "") : "") . '>' . $val2->desc . '</option>'; //'.(isset($val->degree)?(($val->degree==$val2->value)?'selected="true"':""):"").'
                          }
                        }
                        echo '</select>'
                          . '</td>'
                          . '<td class="col-md-2">'
                          . '<select id="14_tingkatlembaga' . $i . '" name="14_tingkatlembaga[]" style="height: 34px;" required="">'
                          . '<option value="">--Choose--</option>';
                        if (isset($bp_14_r)) {
                          foreach ($bp_14_r as $val2) {
                            echo '<option value="' . $val2->value . '" ' . (isset($val->pemberi) ? ((trim($val->pemberi) == trim($val2->desc)) ? 'selected="true"' : "") : "") . '>' . $val2->desc . '</option>'; //'.(isset($val->degree)?(($val->degree==$val2->value)?'selected="true"':""):"").'
                          }
                        }
                        echo '</select>'
                          . '</td>'
                          . '<td class="col-md-2" style="display:none;">'
                          . '<select id="14_total' . $i . '" name="14_total[]" style="height: 34px;" >'
                          . '<option value="">--Choose--</option>';
                        if (isset($bp_14_p)) {
                          foreach ($bp_14_p as $val2) {
                            echo '<option value="' . $val2->value . '" >' . $val2->desc . '</option>'; //'.(isset($val->degree)?(($val->degree==$val2->value)?'selected="true"':""):"").'
                          }
                        }
                        echo '</select>'
                          . '</td>'

                          . '<td class="col-md-2">'

                          . '<textarea id="14_uraian' . $i . '" name="14_uraian[]" class="form-control input-md " style="width:200px;" rows="8">' . $val->description . '</textarea>'
                          . '</td>'

                          . '<td class="col-md-8">'
                          . '	<div class="form-group">'
                          . '		<div id="14_avatarattaward' . $i . '">'
                          . '		<input type="hidden" name="14_award_image_url[]" value="' . ((isset($val->attachment)) ? $val->attachment : '') . '" style="display: inline-block;">'

                          //		. '		'.((isset($val->attachment))?"<a href='".base_url()."/assets/uploads/".$val->attachment."' target='_blank' class='ava_discus'>".$val->attachment."</a>":'').'' // ==> sebelum perubahan

                          // --------------------------------------------------------------------------- Perubahan by Ipur For Req UGM Tgl 26/27 Mei 2025 -------------------------------------
                          . '		' . ((isset($val->attachment)) ? "<a href='" . (substr(trim($val->attachment), 0, 8) == 'https://' ? $val->attachment : (base_url() . "/assets/uploads/" . $val->attachment)) . "'
									target='_blank' class='ava_discus'>" . $val->attachment . "</a>" : '') . ''
                          //-----------------------------------------------------------------------------------------------------------------------------------------------		
                          . '		</div>'
                          . '		<div id="14_errUploadattaward' . $i . '"></div>'
                          . '		<input type="file" name="14_attaward[]" id="14_attaward' . $i . '" class="form-control input-md" onchange="upload_award(\'attaward' . $i . '\')">'
                          . '	</div>'
                          . '</td>'

                          . '<td class="col-md-2">'

                          //. '<textarea id="14_uraian' .$i. '" name="14_uraian[]" class="form-control input-md " style="width:200px;" rows="8">'.$val->description.'</textarea>'

                          . '<select id="14_komp' . $i . '" name="14_komp[' . $i . '][]" class="input-md" style="height: 200px;" required="" multiple>'
                          . '<option value="">--Choose--</option>';


                        if (isset($m_act_13)) {
                          $temp = true;
                          foreach ($m_act_13 as $val) {
                            if (strlen($val->value) < 8) {
                              if (!$temp)
                                echo '</optgroup>';
                              echo  '<optgroup label="' . $val->value . ' - ' . $val->title . '">';
                              $temp = true;
                            } else {
                              $temp = false;
                              echo  '<option value="' . $val->value . '" >' . $val->value . ' - ' . $val->title . '</option>';
                            }
                          }
                        }


                        echo '</select>'


                          . '</td>'

                          /*
									. '<td class="col-md-4">'
					
									. '<table width="100%" id="'.$tableID.'komp_' . $i  . '" class="table">'
									. '</table>'
									. '<div class="col-md-12" style="padding-bottom:20px;">'
									. '<button type="button" class="btn btn-primary" onclick="addKomp(\''.$tableID.'komp_' . $i  . '\')">+ Tambah Kompetensi</button>'
									. '</div>'
									
									. '</td>'
									*/

                          . '<td class="td-action">'
                          . '<button type="button" class="btn btn-danger btn-xs ' . $tableID . '-item-remove-button" data-id="' . $i  . '">'
                          . '<i class="fa fa-trash-o fa-fw"></i>X'
                          . '</button>'
                          . '</td>'
                          . '</tr>
							';

                        $i++;
                      }
                    }
                    ?>

                  </tbody>
                </table>
              </div>
              <div class="col-md-12" style="padding-bottom:20px;">
                <button type="button" class="btn btn-primary" onclick="add14('phg')">+ Tambah Baru</button>
              </div>
              <p>Apabila perlu tuliskan pada lembar tambahan</p>
              <p>*) KOMPETENSI: Isi dengan nomor Uraian Kegiatan Kompetensi yang Anda anggap persyaratannya telah terpenuhi dengan aktifitas Anda di sini</p>
              <div class="col-md-12" style="padding-bottom:20px;text-align:center;">
                <button type="button" name="savefaip" value="1" class="btn btn-next savefaip">Save & Continue</button>
              </div>
            </section>
            <h3>I.5</h3>
            <section>
              <label>I.5 Pendidikan/Pelatihan Teknik/Manajemen <span class="comp">(W2)</span></label><br />
              <div style="width:3000px;">
                <table width="100%" id="tpdd" class="table">
                  <thead>
                    <tr>
                      <th width="5%"><label>No</label></th>
                      <th width="10%"><label>NAMA PENDIDIKAN/PELATIHAN TEKNIK</label></th>
                      <th width="5%"><label>PENYELENGGARA</label></th>
                      <th width="5%"><label>KOTA/KABUPATEN</label></th>
                      <th width="5%"><label>PROVINSI</label></th>
                      <th width="5%"><label>NEGARA</label></th>
                      <th width="15%"><label>BULAN / TAHUN</label></th>

                      <th width="5%"><label>Pada tingkatan apa materi Pendidikan/Pelatihan Teknik yang Anda Ikuti</label></th>
                      <th width="5%"><label>Berapa jam Anda mengikuti Pendidikan/Pelatihan Teknik ?</label></th>
                      <th width="5%" style="display:none;"><label>Berapa aktifitas Pendidikan/Pelatihan Teknik yang sudah Anda ikuti?</label></th>
                      <th width="30%"><label>Uraian Singkat Aktifitas</label></th>
                      <th width="5%"><label>Dokumen Pendukung<br /> <span class="red">(Max. 700KB, image atau PDF)</span></label></th>
                      <th width="30%"><label>KLAIM KOMPETENSI </label></th>
                      <!--<th width="10%"><label>KOMPETENSI *)</label></th>-->
                      <th width="5%"><label></label></th>
                    </tr>
                  </thead>
                  <tbody>

                    <?php
                    $bp_15_p = array();
                    $bp_15_q = array();
                    $bp_15_r = array();
                    if (isset($bp_15[0])) {
                      foreach ($bp_15 as $val) {
                        if ($val->faip_type == "p")
                          $bp_15_p[] = $val;
                        else if ($val->faip_type == "q")
                          $bp_15_q[] = $val;
                        else if ($val->faip_type == "r")
                          $bp_15_r[] = $val;
                      }
                    }
                    if (count($user_course1) > 0) {
                      $i = 1;
                      //print_r($user_course1);
                      foreach ($user_course1 as $val) {

                        $years = count($user_course1);

                        $data['bp_15'] = $this->main_mod->msrwhere('m_bakuan_penilaian', array('faip_num' => '15', 'faip_type' => 'p'), 'id', 'desc')->result();

                        foreach ($data['bp_15'] as $valbp) {
                          $condition = substr($valbp->formula, 0, 2);
                          if ($condition == "<=") {
                            if ($years <= substr($valbp->formula, 2, 5)) $tempidx = $valbp->value;
                          } else if ($condition == "<") {
                            if ($years < substr($valbp->formula, 2, 5)) $tempidx = $valbp->value;
                          } else if ($condition == ">") {
                            if ($years > substr($valbp->formula, 2, 5)) $tempidx = $valbp->value;
                          } else if ($condition == ">=") {
                            if ($years >= substr($valbp->formula, 2, 5)) $tempidx = $valbp->value;
                          } else if ($condition == "=") {
                            if ($years == substr($valbp->formula, 2, 5)) $tempidx = $valbp->value;
                          }
                          //echo substr($valbp->formula,2,5).'*<br />';
                        }
                        $p = $tempidx;
                        $q = 0;
                        $r = 0;
                        foreach ($bp_15_q as $val2) {
                          if (trim($val->hour) == trim($val2->desc))
                            $q = $val2->value;
                        }

                        foreach ($bp_15_r as $val2) {
                          if (trim($val->tingkat) == trim($val2->desc))
                            $r = $val2->value;
                        }


                        $t = $p * $q * $r;

                        $tableID = 'pdd';

                        echo '
							<tr class=" ' . $tableID . '-item" data-id="' . $i . '" ><td class="">'
                          . $i
                          . '</td>'


                          . '<td class="col-md-2"><input id="15_t' . $i . '" name="15_t[]" value="' . $t . '" type="hidden">'
                          . '<input id="15_nama' . $i . '" name="15_nama[]" value="' . $val->coursename . '" type="text" placeholder="" class="form-control input-md "  required="">'
                          . '</td>'
                          . '<td class="col-md-2">'
                          . '<input id="15_lembaga' . $i . '" name="15_lembaga[]" value="' . $val->courseorg . '" type="text" placeholder="" class="form-control input-md "  required="">'
                          . '</td>'
                          . '<td class="col-md-2">'
                          . '<input id="15_location' . $i . '" name="15_location[]" value="' . $val->location . '" type="text" placeholder="" class="form-control input-md "  required="">'
                          . '</td>'
                          . '<td class="col-md-2">'
                          . '<input id="15_provinsi' . $i . '" name="15_provinsi[]" value="' . $val->provinsi . '" type="text" placeholder="" class="form-control input-md "  required="">'
                          . '</td>'
                          . '<td class="col-md-2">'
                          . '<input id="15_negara' . $i . '" name="15_negara[]" value="' . $val->negara . '" type="text" placeholder="" class="form-control input-md "  required="">'
                          . '</td>'

                          . '<td class="col-md-2">'
                          . '	<div class="">'
                          . '	<select id="15_startdate' . $i . '" name="15_startdate[]" class="form-control input-md" required="">'
                          . '		<option value="">---</option>'
                          . '		<option value="1" ' . (isset($val->startmonth) ? (($val->startmonth == "1") ? 'selected="true"' : "") : "") . ' >Januari</option>'
                          . '		<option value="2" ' . (isset($val->startmonth) ? (($val->startmonth == "2") ? 'selected="true"' : "") : "") . '>Pebruari</option>'
                          . '		<option value="3" ' . (isset($val->startmonth) ? (($val->startmonth == "3") ? 'selected="true"' : "") : "") . '>Maret</option>'
                          . '		<option value="4" ' . (isset($val->startmonth) ? (($val->startmonth == "4") ? 'selected="true"' : "") : "") . '>April</option>'
                          . '		<option value="5" ' . (isset($val->startmonth) ? (($val->startmonth == "5") ? 'selected="true"' : "") : "") . '>Mei</option>'
                          . '		<option value="6" ' . (isset($val->startmonth) ? (($val->startmonth == "6") ? 'selected="true"' : "") : "") . '>Juni</option>'
                          . '		<option value="7" ' . (isset($val->startmonth) ? (($val->startmonth == "7") ? 'selected="true"' : "") : "") . '>Juli</option>'
                          . '		<option value="8" ' . (isset($val->startmonth) ? (($val->startmonth == "8") ? 'selected="true"' : "") : "") . '>Agustus</option>'
                          . '		<option value="9" ' . (isset($val->startmonth) ? (($val->startmonth == "9") ? 'selected="true"' : "") : "") . '>September</option>'
                          . '		<option value="10" ' . (isset($val->startmonth) ? (($val->startmonth == "10") ? 'selected="true"' : "") : "") . '>Oktober</option>'
                          . '		<option value="11" ' . (isset($val->startmonth) ? (($val->startmonth == "11") ? 'selected="true"' : "") : "") . '>Nopember</option>'
                          . '		<option value="12" ' . (isset($val->startmonth) ? (($val->startmonth == "12") ? 'selected="true"' : "") : "") . '>Desember</option>'
                          . '	</select>'
                          . '	<input id="15_startyear' . $i . '" name="15_startyear[]" value="' . $val->startyear . '" type="text" placeholder="Year" class="form-control input-md number datepickeryear" required="">'
                          . '	</div>'
                          . '	<div class="" id="15_ispresent1' . $i . '" ' . (isset($val->is_present) ? (($val->is_present == "1") ? 'style="display:none;"' : "") : "") . '>-</div>'
                          . '	<div class="" id="15_ispresent2' . $i . '" ' . (isset($val->is_present) ? (($val->is_present == "1") ? 'style="display:none;"' : "") : "") . '>'
                          . '	<select id="15_enddate' . $i . '" name="15_enddate[]" class="form-control input-md">'
                          . '		<option value="">---</option>'
                          . '		<option value="1" ' . (isset($val->endmonth) ? (($val->endmonth == "1") ? 'selected="true"' : "") : "") . ' >Januari</option>'
                          . '		<option value="2" ' . (isset($val->endmonth) ? (($val->endmonth == "2") ? 'selected="true"' : "") : "") . '>Pebruari</option>'
                          . '		<option value="3" ' . (isset($val->endmonth) ? (($val->endmonth == "3") ? 'selected="true"' : "") : "") . '>Maret</option>'
                          . '		<option value="4" ' . (isset($val->endmonth) ? (($val->endmonth == "4") ? 'selected="true"' : "") : "") . '>April</option>'
                          . '		<option value="5" ' . (isset($val->endmonth) ? (($val->endmonth == "5") ? 'selected="true"' : "") : "") . '>Mei</option>'
                          . '		<option value="6" ' . (isset($val->endmonth) ? (($val->endmonth == "6") ? 'selected="true"' : "") : "") . '>Juni</option>'
                          . '		<option value="7" ' . (isset($val->endmonth) ? (($val->endmonth == "7") ? 'selected="true"' : "") : "") . '>Juli</option>'
                          . '		<option value="8" ' . (isset($val->endmonth) ? (($val->endmonth == "8") ? 'selected="true"' : "") : "") . '>Agustus</option>'
                          . '		<option value="9" ' . (isset($val->endmonth) ? (($val->endmonth == "9") ? 'selected="true"' : "") : "") . '>September</option>'
                          . '		<option value="10" ' . (isset($val->endmonth) ? (($val->endmonth == "10") ? 'selected="true"' : "") : "") . '>Oktober</option>'
                          . '		<option value="11" ' . (isset($val->endmonth) ? (($val->endmonth == "11") ? 'selected="true"' : "") : "") . '>Nopember</option>'
                          . '		<option value="12" ' . (isset($val->endmonth) ? (($val->endmonth == "12") ? 'selected="true"' : "") : "") . '>Desember</option>'
                          . '	</select>'
                          . '	<input id="15_endyear' . $i . '" name="15_endyear[]" value="' . $val->endyear . '" type="text" placeholder="Year" class="form-control input-md number datepickeryear" >'
                          . '	</div>'
                          . '	<label class="form-check-label"><input type="hidden" name="15_workx[]" value="' . (isset($val->is_present) ? (($val->is_present == "1") ? '1' : "0") : "0") . '"><input type="checkbox" id="15_work' . $i . '" name="15_work[]" class="form-check-input" value="1" data-id="' . $i . '" ' . (isset($val->is_present) ? (($val->is_present == "1") ? 'checked="true"' : "") : "") . '>Masih menjadi anggota</label>'
                          . '</td>'



                          /*
							. '<td class="col-md-2">'
							. '<input id="15_jam' .$i.'" name="15_jam[]" value="" type="text" placeholder="" class="form-control input-md "  required="">'
							. '</td>'
							. '<td class="col-md-2">'
							. '<input id="15_lic' .$i.'" name="15_lic[]" value="'.$val->lic_num.'" type="text" placeholder="" class="form-control input-md "  required="">'
							. '</td>'
							. '<td class="col-md-2">'
							. '<input id="15_url' .$i.'" name="15_url[]" value="'.$val->cert_url.'" type="text" placeholder="" class="form-control input-md "  required="">'
							. '</td>'
							*/



                          . '<td class="col-md-2">'
                          . '<select id="15_tingkat' . $i . '" name="15_tingkat[]" style="height: 34px;" required="">'
                          . '<option value="">--Choose--</option>';
                        if (isset($bp_15_r)) {
                          foreach ($bp_15_r as $val2) {
                            echo '<option value="' . $val2->value . '" ' . (isset($val->tingkat) ? ((trim($val->tingkat) == trim($val2->desc)) ? 'selected="true"' : "") : "") . '>' . $val2->desc . '</option>'; //'.(isset($val->degree)?(($val->degree==$val2->value)?'selected="true"':""):"").'
                          }
                        }
                        echo '</select>'
                          . '</td>'
                          . '<td class="col-md-2">'
                          . '<select id="15_jam' . $i . '" name="15_jam[]" style="height: 34px;" required="">'
                          . '<option value="">--Choose--</option>';
                        if (isset($bp_15_q)) {
                          foreach ($bp_15_q as $val2) {
                            echo '<option value="' . $val2->value . '" ' . (isset($val->hour) ? ((trim($val->hour) == trim($val2->desc)) ? 'selected="true"' : "") : "") . '>' . $val2->desc . '</option>'; //'.(isset($val->degree)?(($val->degree==$val2->value)?'selected="true"':""):"").'
                          }
                        }
                        echo '</select>'
                          . '</td>'
                          . '<td class="col-md-2" style="display:none;">'
                          . '<select id="15_total' . $i . '" name="15_total[]" style="height: 34px;">'
                          . '<option value="">--Choose--</option>';
                        if (isset($bp_15_p)) {
                          foreach ($bp_15_p as $val2) {
                            echo '<option value="' . $val2->value . '" >' . $val2->desc . '</option>'; //'.(isset($val->degree)?(($val->degree==$val2->value)?'selected="true"':""):"").'
                          }
                        }
                        echo '</select>'
                          . '</td>'



                          . '<td class="col-md-2">'

                          . '<textarea id="15_uraian' . $i . '" name="15_uraian[]" class="form-control input-md " style="width:200px;" rows="8"></textarea>'
                          . '</td>'

                          . '<td class="col-md-8">'
                          . '	<div class="form-group">'
                          . '		<div id="15_avatarattcourse' . $i . '">'
                          . '		<input type="hidden" name="15_course_image_url[]" value="' . ((isset($val->attachment)) ? $val->attachment : '') . '" style="display: inline-block;">'
                          . '		' . ((isset($val->attachment)) ? "<a href='" . base_url() . "/assets/uploads/" . $val->attachment . "' target='_blank' class='ava_discus'>" . $val->attachment . "</a>" : '') . ''
                          . '		</div>'
                          . '		<div id="15_errUploadattcourse' . $i . '"></div>'
                          . '		<input type="file" name="15_attcourse[]" id="15_attcourse' . $i . '" class="form-control input-md" onchange="upload_course(\'attcourse' . $i . '\')">'
                          . '	</div>'
                          . '</td>'

                          . '<td class="col-md-2">'

                          //. '<textarea id="15_uraian' .$i. '" name="15_uraian[]" class="form-control input-md " style="width:200px;" rows="8">'.$val->description.'</textarea>'


                          . '<select id="15_komp' . $i . '" name="15_komp[' . $i . '][]" class="input-md" style="height: 200px;" required="" multiple>'
                          . '<option value="">--Choose--</option>';


                        if (isset($m_act_15)) {
                          $temp = true;
                          foreach ($m_act_15 as $val) {
                            if (strlen($val->value) < 8) {
                              if (!$temp)
                                echo '</optgroup>';
                              echo  '<optgroup label="' . $val->value . ' - ' . $val->title . '">';
                              $temp = true;
                            } else {
                              $temp = false;
                              echo  '<option value="' . $val->value . '" >' . $val->value . ' - ' . $val->title . '</option>';
                            }
                          }
                        }


                        echo '</select>'


                          . '</td>'



                          /*
									. '<td class="col-md-4">'
					
									. '<table width="100%" id="'.$tableID.'komp_' . $i  . '" class="table">'
									. '</table>'
									. '<div class="col-md-12" style="padding-bottom:20px;">'
									. '<button type="button" class="btn btn-primary" onclick="addKomp(\''.$tableID.'komp_' . $i  . '\')">+ Tambah Kompetensi</button>'
									. '</div>'
									
									. '</td>'
									*/

                          . '<td class="td-action">'
                          . '<button type="button" class="btn btn-danger btn-xs ' . $tableID . '-item-remove-button" data-id="' . $i  . '">'
                          . '<i class="fa fa-trash-o fa-fw"></i>X'
                          . '</button>'
                          . '</td>'
                          . '</tr>
							';

                        $i++;
                      }
                    }
                    ?>

                  </tbody>
                </table>
              </div>
              <div class="col-md-12" style="padding-bottom:20px;">
                <button type="button" class="btn btn-primary" onclick="add15('pdd')">+ Tambah Baru</button>
              </div>
              <p>Apabila perlu tuliskan pada lembar tambahan</p>
              <p>*) KOMPETENSI: Isi dengan nomor Uraian Kegiatan Kompetensi yang Anda anggap persyaratannya telah terpenuhi dengan aktifitas Anda di sini</p>
              <div class="col-md-12" style="padding-bottom:20px;text-align:center;">
                <button type="button" name="savefaip" value="1" class="btn btn-next savefaip">Save & Continue</button>
              </div>
            </section>
            <h3>I.6</h3>
            <section>
              <label>I.6 Sertifikat Kompetensi dan Bidang Lainnya (yang Relevan) Yang Diikuti <span class="green">(#)</span> <span class="comp">(W1,W4)</span></label><br />
              <div style="width:3000px;">
                <table width="100%" id="tppm" class="table">
                  <thead>
                    <tr>
                      <th width="5%"><label>No</label></th>
                      <th width="10%"><label>NAMA PENDIDIKAN/PELATIHAN</label></th>
                      <th width="10%"><label>PENYELENGGARA</label></th>
                      <th width="5%"><label>KOTA/KABUPATEN</label></th>
                      <th width="5%"><label>PROVINSI</label></th>
                      <th width="5%"><label>NEGARA</label></th>
                      <th width="10%"><label>BULAN / TAHUN</label></th>

                      <th width="5%"><label>Pada tingkatan apa materi Pendidikan/Pelatihan Manajemen dan Bidang Lainnya (yang Relevan) yang Anda Ikuti</label></th>
                      <th width="5%"><label>Berapa jam Anda mengikuti Pendidikan/Pelatihan Manajemen dan Bidang Lainnya (yang Relevan)?</label></th>
                      <th width="5%" style="display:none;"><label>Berapa aktifitas Pendidikan/Pelatihan Manajemen dan Bidang Lainnya (yang Relevan) yang sudah Anda ikuti?</label></th>
                      <th width="30%"><label>Uraian Singkat Aktifitas</label></th>
                      <th width="5%"><label>Dokumen Pendukung<br /> <span class="red">(Max. 700KB, image atau PDF)</span></label></th>
                      <th width="30%"><label>KLAIM KOMPETENSI </label></th>
                      <!--<th width="10%"><label>KOMPETENSI *)</label></th>-->
                      <th width="5%"><label></label></th>
                    </tr>
                  </thead>
                  <tbody>

                    <?php
                    $bp_16_p = array();
                    $bp_16_q = array();
                    $bp_16_r = array();
                    if (isset($bp_16[0])) {
                      foreach ($bp_16 as $val) {
                        if ($val->faip_type == "p")
                          $bp_16_p[] = $val;
                        else if ($val->faip_type == "q")
                          $bp_16_q[] = $val;
                        else if ($val->faip_type == "r")
                          $bp_16_r[] = $val;
                      }
                    }
                    if (count($user_cert) > 0) {
                      $i = 1;
                      foreach ($user_cert as $val) {

                        $years = count($user_cert);

                        $data['bp_16'] = $this->main_mod->msrwhere('m_bakuan_penilaian', array('faip_num' => '16', 'faip_type' => 'p'), 'id', 'desc')->result();

                        foreach ($data['bp_16'] as $valbp) {
                          $condition = substr($valbp->formula, 0, 2);
                          if ($condition == "<=") {
                            if ($years <= substr($valbp->formula, 2, 5)) $tempidx = $valbp->value;
                          } else if ($condition == "<") {
                            if ($years < substr($valbp->formula, 2, 5)) $tempidx = $valbp->value;
                          } else if ($condition == ">") {
                            if ($years > substr($valbp->formula, 2, 5)) $tempidx = $valbp->value;
                          } else if ($condition == ">=") {
                            if ($years >= substr($valbp->formula, 2, 5)) $tempidx = $valbp->value;
                          } else if ($condition == "=") {
                            if ($years == substr($valbp->formula, 2, 5)) $tempidx = $valbp->value;
                          }
                          //echo substr($valbp->formula,2,5).'*<br />';
                        }
                        $p = $tempidx;
                        $q = 0;
                        $r = 0;
                        /*foreach($bp_16_q as $val2){
								if(trim($val->position)==trim($val2->desc))
									$q =$val2->value;
							}
							
							foreach($bp_16_r as $val2){
								if(trim($val->tingkat)==trim($val2->desc))
									$r =$val2->value;
							}*/


                        $t = $p * $q * $r;

                        $tableID = 'ppm';

                        echo '
							<tr class=" ' . $tableID . '-item" data-id="' . $i . '" ><td class="">'
                          . $i
                          . '</td>'


                          . '<td class="col-md-2"><input id="16_t' . $i . '" name="16_t[]" value="' . $t . '" type="hidden">'
                          . '<input id="16_nama' . $i . '" name="16_nama[]" value="' . $val->cert_name . '" type="text" placeholder="" class="form-control input-md "  required="">'
                          . '</td>'
                          . '<td class="col-md-2">'
                          . '<input id="16_lembaga' . $i . '" name="16_lembaga[]" value="' . $val->cert_auth . '" type="text" placeholder="" class="form-control input-md "  required="">'
                          . '</td>'
                          . '<td class="col-md-2">'
                          . '<input id="16_location' . $i . '" name="16_location[]" value="' . $val->location . '" type="text" placeholder="" class="form-control input-md "  required="">'
                          . '</td>'
                          . '<td class="col-md-2">'
                          . '<input id="16_provinsi' . $i . '" name="16_provinsi[]" value="' . $val->provinsi . '" type="text" placeholder="" class="form-control input-md "  required="">'
                          . '</td>'
                          . '<td class="col-md-2">'
                          . '<input id="16_negara' . $i . '" name="16_negara[]" value="' . $val->negara . '" type="text" placeholder="" class="form-control input-md "  required="">'
                          . '</td>'

                          . '<td class="col-md-2">'
                          . '	<div class="">'
                          . '	<select id="16_startdate' . $i . '" name="16_startdate[]" class="form-control input-md" required="">'
                          . '		<option value="">---</option>'
                          . '		<option value="1" ' . (isset($val->startmonth) ? (($val->startmonth == "1") ? 'selected="true"' : "") : "") . ' >Januari</option>'
                          . '		<option value="2" ' . (isset($val->startmonth) ? (($val->startmonth == "2") ? 'selected="true"' : "") : "") . '>Pebruari</option>'
                          . '		<option value="3" ' . (isset($val->startmonth) ? (($val->startmonth == "3") ? 'selected="true"' : "") : "") . '>Maret</option>'
                          . '		<option value="4" ' . (isset($val->startmonth) ? (($val->startmonth == "4") ? 'selected="true"' : "") : "") . '>April</option>'
                          . '		<option value="5" ' . (isset($val->startmonth) ? (($val->startmonth == "5") ? 'selected="true"' : "") : "") . '>Mei</option>'
                          . '		<option value="6" ' . (isset($val->startmonth) ? (($val->startmonth == "6") ? 'selected="true"' : "") : "") . '>Juni</option>'
                          . '		<option value="7" ' . (isset($val->startmonth) ? (($val->startmonth == "7") ? 'selected="true"' : "") : "") . '>Juli</option>'
                          . '		<option value="8" ' . (isset($val->startmonth) ? (($val->startmonth == "8") ? 'selected="true"' : "") : "") . '>Agustus</option>'
                          . '		<option value="9" ' . (isset($val->startmonth) ? (($val->startmonth == "9") ? 'selected="true"' : "") : "") . '>September</option>'
                          . '		<option value="10" ' . (isset($val->startmonth) ? (($val->startmonth == "10") ? 'selected="true"' : "") : "") . '>Oktober</option>'
                          . '		<option value="11" ' . (isset($val->startmonth) ? (($val->startmonth == "11") ? 'selected="true"' : "") : "") . '>Nopember</option>'
                          . '		<option value="12" ' . (isset($val->startmonth) ? (($val->startmonth == "12") ? 'selected="true"' : "") : "") . '>Desember</option>'
                          . '	</select>'
                          . '	<input id="16_startyear' . $i . '" name="16_startyear[]" value="' . $val->startyear . '" type="text" placeholder="Year" class="form-control input-md number datepickeryear" required="">'
                          . '	</div>'
                          . '	<div class="" id="16_ispresent1' . $i . '" ' . (isset($val->is_present) ? (($val->is_present == "1") ? 'style="display:none;"' : "") : "") . '>-</div>'
                          . '	<div class="" id="16_ispresent2' . $i . '" ' . (isset($val->is_present) ? (($val->is_present == "1") ? 'style="display:none;"' : "") : "") . '>'
                          . '	<select id="16_enddate' . $i . '" name="16_enddate[]" class="form-control input-md">'
                          . '		<option value="">---</option>'
                          . '		<option value="1" ' . (isset($val->endmonth) ? (($val->endmonth == "1") ? 'selected="true"' : "") : "") . ' >Januari</option>'
                          . '		<option value="2" ' . (isset($val->endmonth) ? (($val->endmonth == "2") ? 'selected="true"' : "") : "") . '>Pebruari</option>'
                          . '		<option value="3" ' . (isset($val->endmonth) ? (($val->endmonth == "3") ? 'selected="true"' : "") : "") . '>Maret</option>'
                          . '		<option value="4" ' . (isset($val->endmonth) ? (($val->endmonth == "4") ? 'selected="true"' : "") : "") . '>April</option>'
                          . '		<option value="5" ' . (isset($val->endmonth) ? (($val->endmonth == "5") ? 'selected="true"' : "") : "") . '>Mei</option>'
                          . '		<option value="6" ' . (isset($val->endmonth) ? (($val->endmonth == "6") ? 'selected="true"' : "") : "") . '>Juni</option>'
                          . '		<option value="7" ' . (isset($val->endmonth) ? (($val->endmonth == "7") ? 'selected="true"' : "") : "") . '>Juli</option>'
                          . '		<option value="8" ' . (isset($val->endmonth) ? (($val->endmonth == "8") ? 'selected="true"' : "") : "") . '>Agustus</option>'
                          . '		<option value="9" ' . (isset($val->endmonth) ? (($val->endmonth == "9") ? 'selected="true"' : "") : "") . '>September</option>'
                          . '		<option value="10" ' . (isset($val->endmonth) ? (($val->endmonth == "10") ? 'selected="true"' : "") : "") . '>Oktober</option>'
                          . '		<option value="11" ' . (isset($val->endmonth) ? (($val->endmonth == "11") ? 'selected="true"' : "") : "") . '>Nopember</option>'
                          . '		<option value="12" ' . (isset($val->endmonth) ? (($val->endmonth == "12") ? 'selected="true"' : "") : "") . '>Desember</option>'
                          . '	</select>'
                          . '	<input id="16_endyear' . $i . '" name="16_endyear[]" value="' . $val->endyear . '" type="text" placeholder="Year" class="form-control input-md number datepickeryear" >'
                          . '	</div>'
                          . '	<label class="form-check-label"><input type="hidden" name="16_workx[]" value="' . (isset($val->is_present) ? (($val->is_present == "1") ? '1' : "0") : "0") . '"><input type="checkbox" id="16_work' . $i . '" name="16_work[]" class="form-check-input" value="1" data-id="' . $i . '" ' . (isset($val->is_present) ? (($val->is_present == "1") ? 'checked="true"' : "") : "") . '>Masih menjadi anggota</label>'
                          . '</td>'

                          /*
							. '<td class="col-md-2">'
							. '<input id="16_jam' .$i.'" name="16_jam[]" value="'.$val->hour.'" type="text" placeholder="" class="form-control input-md "  required="">'
							. '</td>'
							. '<td class="col-md-2">'
							. '<input id="16_lic' .$i.'" name="16_lic[]" value="" type="text" placeholder="" class="form-control input-md "  required="">'
							. '</td>'
							. '<td class="col-md-2">'
							. '<input id="16_url' .$i.'" name="16_url[]" value="" type="text" placeholder="" class="form-control input-md "  required="">'
							. '</td>'
							*/

                          . '<td class="col-md-2">'
                          . '<select id="16_tingkat' . $i . '" name="16_tingkat[]" style="height: 34px;" required="">'
                          . '<option value="">--Choose--</option>';
                        if (isset($bp_16_r)) {
                          foreach ($bp_16_r as $val2) {
                            echo '<option value="' . $val2->value . '" ' . (isset($val->tingkat) ? ((trim($val->tingkat) == trim($val2->desc)) ? 'selected="true"' : "") : "") . '>' . $val2->desc . '</option>'; //'.(isset($val->degree)?(($val->degree==$val2->value)?'selected="true"':""):"").'
                          }
                        }
                        echo '</select>'
                          . '</td>'
                          . '<td class="col-md-2">'
                          . '<select id="16_jam' . $i . '" name="16_jam[]" style="height: 34px;" required="">'
                          . '<option value="">--Choose--</option>';
                        if (isset($bp_16_q)) {
                          foreach ($bp_16_q as $val2) {
                            echo '<option value="' . $val2->value . '" ' . (isset($val->hour) ? ((trim($val->hour) == trim($val2->desc)) ? 'selected="true"' : "") : "") . '>' . $val2->desc . '</option>'; //'.(isset($val->degree)?(($val->degree==$val2->value)?'selected="true"':""):"").'
                          }
                        }
                        echo '</select>'
                          . '</td>'
                          . '<td class="col-md-2" style="display:none;">'
                          . '<select id="16_total' . $i . '" name="16_total[]" style="height: 34px;" >'
                          . '<option value="">--Choose--</option>';
                        if (isset($bp_16_p)) {
                          foreach ($bp_16_p as $val2) {
                            echo '<option value="' . $val2->value . '" >' . $val2->desc . '</option>'; //'.(isset($val->degree)?(($val->degree==$val2->value)?'selected="true"':""):"").'
                          }
                        }
                        echo '</select>'
                          . '</td>'

                          . '<td class="col-md-2">'

                          . '<textarea id="16_uraian' . $i . '" name="16_uraian[]" class="form-control input-md " style="width:200px;" rows="8"></textarea>'
                          . '</td>'

                          . '<td class="col-md-8">'
                          . '	<div class="form-group">'
                          . '		<div id="16_avatarattcert' . $i . '">'
                          . '		<input type="hidden" name="16_cert_image_url[]" value="' . ((isset($val->attachment)) ? $val->attachment : '') . '" style="display: inline-block;">'
                          . '		' . ((isset($val->attachment)) ? "<a href='" . base_url() . "/assets/uploads/" . $val->attachment . "' target='_blank' class='ava_discus'>" . $val->attachment . "</a>" : '') . ''
                          . '		</div>'
                          . '		<div id="16_errUploadattcert' . $i . '"></div>'
                          . '		<input type="file" name="16_attcert[]" id="16_attcert' . $i . '" class="form-control input-md" onchange="upload_cert(\'attcert' . $i . '\')">'
                          . '	</div>'
                          . '</td>'

                          . '<td class="col-md-2">'

                          //. '<textarea id="16_uraian' .$i. '" name="16_uraian[]" class="form-control input-md " style="width:200px;" rows="8"></textarea>'


                          . '<select id="16_komp' . $i . '" name="16_komp[' . $i . '][]" class="input-md" style="height: 200px;" required="" multiple>'
                          . '<option value="">--Choose--</option>';


                        if (isset($m_act_16)) {
                          $temp = true;
                          foreach ($m_act_16 as $val) {
                            if (strlen($val->value) < 8) {
                              if (!$temp)
                                echo '</optgroup>';
                              echo  '<optgroup label="' . $val->value . ' - ' . $val->title . '">';
                              $temp = true;
                            } else {
                              $temp = false;
                              echo  '<option value="' . $val->value . '" >' . $val->value . ' - ' . $val->title . '</option>';
                            }
                          }
                        }


                        echo '</select>'

                          . '</td>'



                          /*
									. '<td class="col-md-4">'
					
									. '<table width="100%" id="'.$tableID.'komp_' . $i  . '" class="table">'
									. '</table>'
									. '<div class="col-md-12" style="padding-bottom:20px;">'
									. '<button type="button" class="btn btn-primary" onclick="addKomp(\''.$tableID.'komp_' . $i  . '\')">+ Tambah Kompetensi</button>'
									. '</div>'
									
									. '</td>'
									*/

                          . '<td class="td-action">'
                          . '<button type="button" class="btn btn-danger btn-xs ' . $tableID . '-item-remove-button" data-id="' . $i  . '">'
                          . '<i class="fa fa-trash-o fa-fw"></i>X'
                          . '</button>'
                          . '</td>'
                          . '</tr>
							';

                        $i++;
                      }
                    }
                    ?>

                  </tbody>
                </table>
              </div>
              <div class="col-md-12" style="padding-bottom:20px;">
                <button type="button" class="btn btn-primary" onclick="add16('ppm')">+ Tambah Baru</button>
              </div>
              <p>Apabila perlu tuliskan pada lembar tambahan</p>
              <p>Catatan : <span class="green">(#)</span>Termasuk penataran P4</p>
              <p>*) KOMPETENSI: Isi dengan nomor Uraian Kegiatan Kompetensi yang Anda anggap persyaratannya telah terpenuhi dengan aktifitas Anda di sini</p>
              <div class="col-md-12" style="padding-bottom:20px;text-align:center;">
                <button type="button" name="savefaip" value="1" class="btn btn-next savefaip">Save & Continue</button>
              </div>
            </section>
            <h3>II.1</h3>
            <section>
              <div class="title">
                <h4>II. KUALIFIKASI KODE ETIK INSINYUR INDONESIA dan ETIKA PROFESIONAL</h4>
              </div>
              <label>II.1 Referensi Kode Etik dan Etika Profesi <span class="green">(#)</span></label><br /><br />
              <div style="width:1500px;">
                <table width="100%" id="tref" class="table">
                  <thead>
                    <tr>
                      <th width="5%"><label>No</label></th>
                      <th width="10%"><label>NAMA</label></th>
                      <th width="10%"><label>LEMBAGA</label></th>
                      <th width="10%"><label>ALAMAT</label></th>
                      <th width="10%"><label>KOTA/KABUPATEN</label></th>
                      <th width="10%"><label>PROVINSI</label></th>
                      <th width="10%"><label>NEGARA</label></th>
                      <th width="10%"><label>NO. TELEPON</label></th>
                      <th width="10%"><label>EMAIL</label></th>
                      <th width="10%"><label>HUBUNGAN</label></th>
                      <th width="5%"><label></label></th>
                    </tr>
                  </thead>
                  <tbody>
                  </tbody>
                </table>
              </div>
              <div class="col-md-12" style="padding-bottom:20px;">
                <button type="button" class="btn btn-primary" onclick="add21('ref')">+ Tambah Baru</button>
              </div>
              <p>Catatan : <span class="green">(#)</span> Sekurang-kurangnya 2 (dua) orang, sebanyak-banyaknya 4 (empat) orang</p>

              <div class="col-md-12" style="padding-bottom:20px;text-align:center;">
                <button type="button" name="savefaip" value="1" class="btn btn-next savefaip">Save & Continue</button>
              </div>
            </section>



            <h3>II.2</h3>
            <section>
              <label>II.2 Pengertian, Pendapat dan Pengalaman Sendiri <span class="comp">(W1)</span></label><br />
              Tuliskan dengan kata-kata sendiri apa pengertian dan pendapat Anda tentang Kode Etik Insinyur serta pengalaman Anda tentang Etika Profesi<br />
              <table width="100%" id="teti" class="table">
                <thead>
                  <tr>
                    <th width="5%"><label>No</label></th>
                    <th width="70%"><label></label></th>
                    <th width="20%"><label>KLAIM KOMPETENSI </label></th>
                    <th width="5%"><label></label></th>
                  </tr>
                </thead>
                <tbody>
                </tbody>
              </table>
              <div class="col-md-12" style="padding-bottom:20px;">
                <button type="button" class="btn btn-primary" onclick="add22('eti')">+ Tambah Baru</button>
              </div>

              <div class="col-md-12" style="padding-bottom:20px;text-align:center;">
                <button type="button" name="savefaip" value="1" class="btn btn-next savefaip">Save & Continue</button>
              </div>
            </section>

            <?php /* ?>
	
	<h3>III.1</h3>
    <section>
        <div class="title"><h4>III. KUALIFIKASI PROFESIONAL</h4></div>
		<label>III.1 Pengalaman Dalam Perencanaan & Perancangan dan/atau Pengalaman Dalam Pengelolaan Tugas-tugas Keinsinyuran <span class="green">(2)</span> <span class="comp">(W2,W3,P7)</span></label><br />
		<div style="width:1200px;">
		<table width="100%" id="tkup" class="table">
			<thead>
			<tr><th width="5%"><label>No. <span class="one">(1)</span></label></th>
			<th width="10%"><label>NAMA INSTANSI / PERUSAHAAN</label></th>
			<th width="10%"><label>NAMA PROYEK</label></th>
			<th width="5%"><label>NAMA PEMBERI TUGAS</label></th>			
			<th width="5%"><label>KOTA/KABUPATEN</label></th>	
			<th width="5%"><label>PROVINSI</label></th>	
			<th width="5%"><label>NEGARA</label></th>	
			<th width="5%"><label>PERIODA</label></th>
			<th width="5%"><label>POSISI TUGAS, JABATAN <span class="three">(3)</span></label></th>
			<th width="10%"><label>NILAI PROYEK</label></th>
			<th width="30%"><label>KLAIM KOMPETENSI </label></th>
			<!--<th width="10%"><label>KOMPETENSI *)</label></th>-->
			<th width="5%"><label></label></th>
			</tr>
			</thead>
			<tbody>
			<?php
			$bp_31_p = array();
			$bp_31_q = array();
			$bp_31_r = array();
			if(isset($bp_31[0])){
				foreach($bp_31 as $val){
					if($val->faip_type=="p")
						$bp_31_p[] = $val;
					else if($val->faip_type=="q")
						$bp_31_q[] = $val;
					else if($val->faip_type=="r")
						$bp_31_r[] = $val;
				}
			}
			?>
			
			</tbody>
        </table>
		</div>
		<div class="col-md-12" style="padding-bottom:20px;">
		<button type="button" class="btn btn-primary" onclick="add31('kup')">+ Tambah Baru</button>
		</div>
		<p>*) KOMPETENSI: Isi dengan nomor Uraian Kegiatan Kompetensi yang Anda anggap persyaratannya telah terpenuhi dengan aktifitas Anda di sini</p>
		<p>CATATAN : 	<br />
<span class="one">(1)</span>	Untuk setiap aktifitas yang Anda anggap menonjol, tuliskan uraian singkat di Lembar Dokumentasi Pengalaman Kerja (LAMPIRAN 1) dengan penomoran yang sama<br />
<span class="green">(2)</span>	Pengalaman kerja meliputi bidang Perencanaan/Perancangan, Pengawasan/Supervisi<br />
<span class="three">(3)</span>	Untuk Jabatan yang Anda anggap menonjol, uraikan lebih rinci pada Lampiran 1<br />
<span class="four">(4)</span>	Tuliskan Jenis dan Besar Perusahaan/Instansi (dapat dinyatakan dengan kelas perusahaan dan jumlah tenaga profesional yang dibawahi)</p>

		
    </section>
	
	<h3>III.2</h3>
    <section>
        <label>III.2 Pengalaman Mengajar Pelajaran Keinsinyuran dan/atau Manajemen dan/atau Pengalaman Mengembangkan Pendidikan/Pelatihan Keinsinyuran dan/atau Manajemen <span class="comp">(P5)</span></label><br />
		<div style="width:2600px;">
		<table width="100%" id="tman" class="table">
			<thead>
			<tr><th width="5%"><label>No</label></th>
			<th width="10%"><label>NAMA PERGURUAN TINGGI atau LEMBAGA</label></th>
			<th width="10%"><label>NAMA MATA AJARAN</label></th>
			<th width="5%"><label>KOTA/KABUPATEN</label></th>	
			<th width="5%"><label>PROVINSI</label></th>	
			<th width="5%"><label>NEGARA</label></th>
			
			<th width="10%"><label>PERIODA</label></th>
			<th width="5%"><label>POSISI TUGAS, JABATAN <span class="three">(3)</span></label></th>
			<th width="10%"><label>JUMLAH JAM atau S.K.S</label></th>
			<th width="30%"><label>KLAIM KOMPETENSI </label></th>
			<!--<th width="10%"><label>KOMPETENSI *)</label></th>-->
			<th width="5%"><label></label></th>
			</tr>
			</thead>
			<tbody>
			<?php
			$bp_32_p = array();
			$bp_32_q = array();
			$bp_32_r = array();
			if(isset($bp_32[0])){
				foreach($bp_32 as $val){
					if($val->faip_type=="p")
						$bp_32_p[] = $val;
					else if($val->faip_type=="q")
						$bp_32_q[] = $val;
					else if($val->faip_type=="r")
						$bp_32_r[] = $val;
				}
			}
			?>
			</tbody>
        </table>
		</div>
		<div class="col-md-12" style="padding-bottom:20px;">
		<button type="button" class="btn btn-primary" onclick="add32('man')">+ Tambah Baru</button>
		</div>
		<p>Apabila perlu tuliskan pada lembar tambahan</p>
		<p>*) KOMPETENSI: Isi dengan nomor Uraian Kegiatan Kompetensi yang Anda anggap persyaratannya telah terpenuhi dengan aktifitas Anda di sini</p>
    </section>
	
	<h3>III.3</h3>
    <section>
        <label>III.3 Pengalaman Dalam Penelitian, Pengembangan dan Komersialisasi   dan/atau Pengalaman Manangani Bahan Material dan Komponen <span class="green">(2)</span> <span class="comp">(P6,P9)</span></label><br />
		<div style="width:2600px;">
		<table width="100%" id="tmak" class="table">
			<thead>
			<tr><th width="5%"><label>No <span class="one">(1)</span></label></th>
			<th width="10%"><label>INSTANSI / PERUSAHAAN</label></th>
			<th width="10%"><label>NAMA PROYEK/PRODUK</label></th>
			<th width="5%"><label>KOTA/KABUPATEN</label></th>	
			<th width="5%"><label>PROVINSI</label></th>	
			<th width="5%"><label>NEGARA</label></th>
			<th width="5%"><label>PERIODA</label></th>			
			<th width="10%"><label>POSISI TUGAS, JABATAN <span class="three">(3)</span></label></th>
			<th width="10%"><label>NILAI PROYEK</label></th>
			<th width="30%"><label>KLAIM KOMPETENSI </label></th>
			<!--<th width="10%"><label>KOMPETENSI *)</label></th>-->
			<th width="5%"><label></label></th>
			</tr>
			</thead>
			<tbody>
			<?php
			$bp_33_p = array();
			$bp_33_q = array();
			$bp_33_r = array();
			if(isset($bp_33[0])){
				foreach($bp_33 as $val){
					if($val->faip_type=="p")
						$bp_33_p[] = $val;
					else if($val->faip_type=="q")
						$bp_33_q[] = $val;
					else if($val->faip_type=="r")
						$bp_33_r[] = $val;
				}
			}
			?>
			</tbody>
        </table>
		</div>
		<div class="col-md-12" style="padding-bottom:20px;">
		<button type="button" class="btn btn-primary" onclick="add33('mak')">+ Tambah Baru</button>
		</div>
		<p>Apabila perlu tuliskan pada lembar tambahan</p>
		<p>*) KOMPETENSI: Isi dengan nomor Uraian Kegiatan Kompetensi yang Anda anggap persyaratannya telah terpenuhi dengan aktifitas Anda di sini</p>
		<p>CATATAN : <br />
<span class="one">(1)</span> Untuk aktifitas yang Anda anggap menonjol, tuliskan uraian singkat di Lembar Dokumentasi Pengalaman Kerja (LAMPIRAN 1) dengan penomoran yang sama<br />

<span class="green">(2)</span>Pengalaman kerja meliputi bidang Perencanaan/Perancangan, Pengawasan/Supervisi, Produksi/Pembuatan<br />

<span class="three">(3)</span>Untuk Jabatan yang Anda anggap menonjol, uraikan lebih rinci pada Lampiran 1<br />

<span class="four">(4)</span>Tuliskan Jenis dan Besar Perusahaan/Instansi (dapat dinyatakan dengan kelas perusahaan dan jumlah tenaga profesional yang dibawahi)
</p>


    </section>
	
	<h3>III.4</h3>
    <section>
        <label>III.4 Pengalaman Dalam Pekerjaan Manufaktur atau Produksi dan/atau Pengalaman Dalam Konsultasi Perekayasaan dan/atau Konstruksi/Instalasi <span class="green">(2)</span> <span class="comp">(P7,P8)</span></label><br />
		<div style="width:1200px;">
		<table width="100%" id="trek" class="table">
			<thead>
			<tr><th width="5%"><label>No <span class="one">(1)</span></label></th>
			<th width="10%"><label>INSTANSI / PERUSAHAAN</label></th>
			<th width="10%"><label>NAMA PROYEK</label></th>
			<th width="5%"><label>KOTA/KABUPATEN</label></th>	
			<th width="5%"><label>PROVINSI</label></th>	
			<th width="5%"><label>NEGARA</label></th>
			<th width="5%"><label>PERIODA</label></th>
			<th width="10%"><label>POSISI TUGAS, JABATAN <span class="three">(3)</span></label></th>
			<th width="10%"><label>NILAI PROYEK</label></th>
			<th width="30%"><label>KLAIM KOMPETENSI </label></th>
			<!--<th width="10%"><label>KOMPETENSI *)</label></th>-->
			<th width="5%"><label></label></th>
			</tr>
			</thead>
			<tbody>
			<?php
			$bp_34_p = array();
			$bp_34_q = array();
			$bp_34_r = array();
			if(isset($bp_34[0])){
				foreach($bp_34 as $val){
					if($val->faip_type=="p")
						$bp_34_p[] = $val;
					else if($val->faip_type=="q")
						$bp_34_q[] = $val;
					else if($val->faip_type=="r")
						$bp_34_r[] = $val;
				}
			}
			?>
			</tbody>
        </table>
		</div>
		<div class="col-md-12" style="padding-bottom:20px;">
		<button type="button" class="btn btn-primary" onclick="add34('rek')">+ Tambah Baru</button>
		</div>
		<p>Apabila perlu tuliskan pada lembar tambahan</p>
		<p>*) KOMPETENSI: Isi dengan nomor Uraian Kegiatan Kompetensi yang Anda anggap persyaratannya telah terpenuhi dengan aktifitas Anda di sini</p>
		<p>CATATAN : 	<br />
<span class="one">(1)</span>	Untuk setiap aktifitas yang Anda anggap menonjol, tuliskan uraian singkat di Lembar Dokumentasi Pengalaman Kerja (LAMPIRAN 1) dengan penomoran yang sama<br />
<span class="green">(2)</span>	Pengalaman kerja meliputi bidang Perencanaan/Perancangan, Pengawasan/Supervisi, Produksi/Pembuatan<br />
<span class="three">(3)</span>	Untuk Jabatan yang Anda anggap menonjol, uraikan lebih rinci pada Lampiran 1<br />
<span class="four">(4)</span>	Tuliskan Jenis dan Besar Perusahaan/Instansi (dapat dinyatakan dengan kelas perusahaan dan jumlah tenaga profesional yang dibawahi)
</p>
    </section>
	
	<h3>III.5</h3>
    <section>
        <label>III.5 Pengalaman Dalam Manajemen Usaha dan Pemasaran Teknik dan/atau Pengalaman Dalam Manajemen Pembangunan dan Pemeliharaan Aset <span class="green">(2)</span> <span class="comp">(P10,P11)</span></label><br />
		<div style="width:1200px;">
		<table width="100%" id="tase" class="table">
			<thead>
			<tr><th width="5%"><label>No <span class="one">(1)</span></label></th>
			<th width="10%"><label>INSTANSI / PERUSAHAAN</label></th>			
			<th width="10%"><label>NAMA PROYEK/UNIT</label></th>			
			<th width="5%"><label>KOTA/KABUPATEN</label></th>	
			<th width="5%"><label>PROVINSI</label></th>	
			<th width="5%"><label>NEGARA</label></th>
			<th width="5%"><label>PERIODA</label></th>			
			<th width="10%"><label>POSISI TUGAS, JABATAN <span class="three">(3)</span></label></th>
			<th width="10%"><label>NILAI PROYEK</label></th>
			<th width="30%"><label>KLAIM KOMPETENSI </label></th>
			<!--<th width="10%"><label>KOMPETENSI *)</label></th>-->
			<th width="5%"><label></label></th>
			</tr>
			</thead>
			<tbody>
			<?php
			$bp_35_p = array();
			$bp_35_q = array();
			$bp_35_r = array();
			if(isset($bp_35[0])){
				foreach($bp_35 as $val){
					if($val->faip_type=="p")
						$bp_35_p[] = $val;
					else if($val->faip_type=="q")
						$bp_35_q[] = $val;
					else if($val->faip_type=="r")
						$bp_35_r[] = $val;
				}
			}
			?>
			</tbody>
        </table>
		</div>
		<div class="col-md-12" style="padding-bottom:20px;">
		<button type="button" class="btn btn-primary" onclick="add35('ase')">+ Tambah Baru</button>
		</div>
<p>Apabila perlu tuliskan pada lembar tambahan</p>
<p>*) KOMPETENSI: Isi dengan nomor Uraian Kegiatan Kompetensi yang Anda anggap persyaratannya telah terpenuhi dengan aktifitas Anda di sini</p>		
<p>		CATATAN : 	<br />
<span class="one">(1)</span>	Untuk setiap aktifitas yang Anda anggap menonjol, tuliskan uraian singkat di Lembar Dokumentasi Pengalaman Kerja (LAMPIRAN 1) dengan penomoran yang sama<br />
<span class="green">(2)</span>	Pengalaman kerja meliputi bidang Perencanaan, Pengawasan/Supervisi, Manajemen Usaha<br />
<span class="three">(3)</span>	Untuk Jabatan yang Anda anggap menonjol, uraikan lebih rinci pada Lampiran 1<br />
<span class="four">(4)</span>	Tuliskan Jenis dan Besar Perusahaan/Instansi (dapat dinyatakan dengan kelas perusahaan dan jumlah tenaga profesional yang dibawahi)
</p>
		
    </section>
	
	<?php */ ?>


            <h3>III</h3>
            <section>
              <label>III. KUALIFIKASI PROFESIONAL <span class="comp">(W2,W3,W4,P6,P7,P8,P9,P10,P11)</span></label><br />

              <div style="width:3000px;">
                <table width="100%" id="tkup" class="table">
                  <thead>
                    <tr>
                      <th width="5%"><label>No. </label></th>
                      <th width="10%"><label>Periode</label></th>
                      <th width="10%"><label>Nama Instansi / Perusahaan dan Jabatan/tugas</label></th>
                      <th width="10%"><label>Nama Aktifitas/Kegiatan/Proyek</label></th>
                      <th width="5%"><label>Pemberi Tugas</label></th>
                      <th width="5%"><label>Lokasi</label></th>
                      <th width="5%"><label>Durasi</label></th>
                      <th width="5%"><label>POSISI TUGAS, JABATAN </label></th>
                      <th width="5%"><label>Nilai Proyek </label></th>
                      <th width="5%"><label>Nilai Tanggungjawab </label></th>
                      <th width="5%"><label>SDM yang terlibat </label></th>
                      <th width="5%"><label>Tingkat Kesulitan </label></th>
                      <th width="10%"><label>Skala Proyek</label></th>
                      <th width="5%"><label>Dokumen Pendukung<br /> <span class="red">(Max. 700KB, image atau PDF)</span></label></th>
                      <th width="30%"><label>Uraian Singkat Tugas dan Tanggung Jawab Profesional sesuai NSPK</label></th>
                      <th width="30%"><label>KLAIM KOMPETENSI </label></th>
                      <!--<th width="10%"><label>KOMPETENSI *)</label></th>-->
                      <th width="5%"><label></label></th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    $bp_3_p = array();
                    $bp_3_q = array();
                    $bp_3_r = array();
                    if (isset($bp_3[0])) {
                      foreach ($bp_3 as $val) {
                        if ($val->faip_type == "p")
                          $bp_3_p[] = $val;
                        else if ($val->faip_type == "q")
                          $bp_3_q[] = $val;
                        else if ($val->faip_type == "r")
                          $bp_3_r[] = $val;
                      }
                    }
                    if (count($user_exp) > 0) {
                      $i = 1;
                      foreach ($user_exp as $val) {

                        //$data['bp_3']=$this->main_mod->msrwhere('m_bakuan_penilaian',array('faip_num'=>'3','faip_type'=>'p'),'id','desc')->result();

                        $birthdate_ts = strtotime("$val->startyear-$val->startmonth-1");
                        $birthdate_ts2 = strtotime("$val->endyear-$val->endmonth-1");
                        $diff = abs($birthdate_ts2 - $birthdate_ts);
                        $tempid = "";
                        $years = floor($diff / (365 * 60 * 60 * 24));
                        if ($years < 4)
                          $tempid = '1';
                        else if ($years < 8)
                          $tempid = '2';
                        else if ($years <= 10)
                          $tempid = '3';
                        else if ($years > 10)
                          $tempid = '4';

                        $p = $tempid;
                        $q = 0;
                        $r = 0;

                        /*foreach($bp_3_q as $val2){
							if(trim($val->position)==trim($val2->desc))
								$q =$val2->value;
						}
						
						foreach($bp_3_r as $val2){
							if(trim($val->tingkat)==trim($val2->desc))
								$r =$val2->value;
						}*/


                        $t = $p * $q * $r;

                        $tableID = 'kup';

                        echo '
						<tr class=" ' . $tableID . '-item" data-id="' . $i . '" >'
                          . '<td class="">'
                          . $i
                          . '</td>'

                          . '<td class="col-md-2"><input id="3_t' . $i . '" name="3_t[]" value="' . $t . '" type="hidden">'
                          . '	<div class="">'
                          . '	<select id="3_startdate' . $i  . '" name="3_startdate[]" style="height: 34px;" required="">'
                          . '		<option value="">---</option>'
                          . '		<option value="1" ' . (isset($val->startmonth) ? (($val->startmonth == "1") ? 'selected="true"' : "") : "") . ' >Januari</option>'
                          . '		<option value="2" ' . (isset($val->startmonth) ? (($val->startmonth == "2") ? 'selected="true"' : "") : "") . '>Pebruari</option>'
                          . '		<option value="3" ' . (isset($val->startmonth) ? (($val->startmonth == "3") ? 'selected="true"' : "") : "") . '>Maret</option>'
                          . '		<option value="4" ' . (isset($val->startmonth) ? (($val->startmonth == "4") ? 'selected="true"' : "") : "") . '>April</option>'
                          . '		<option value="5" ' . (isset($val->startmonth) ? (($val->startmonth == "5") ? 'selected="true"' : "") : "") . '>Mei</option>'
                          . '		<option value="6" ' . (isset($val->startmonth) ? (($val->startmonth == "6") ? 'selected="true"' : "") : "") . '>Juni</option>'
                          . '		<option value="7" ' . (isset($val->startmonth) ? (($val->startmonth == "7") ? 'selected="true"' : "") : "") . '>Juli</option>'
                          . '		<option value="8" ' . (isset($val->startmonth) ? (($val->startmonth == "8") ? 'selected="true"' : "") : "") . '>Agustus</option>'
                          . '		<option value="9" ' . (isset($val->startmonth) ? (($val->startmonth == "9") ? 'selected="true"' : "") : "") . '>September</option>'
                          . '		<option value="10" ' . (isset($val->startmonth) ? (($val->startmonth == "10") ? 'selected="true"' : "") : "") . '>Oktober</option>'
                          . '		<option value="11" ' . (isset($val->startmonth) ? (($val->startmonth == "11") ? 'selected="true"' : "") : "") . '>Nopember</option>'
                          . '		<option value="12" ' . (isset($val->startmonth) ? (($val->startmonth == "12") ? 'selected="true"' : "") : "") . '>Desember</option>'
                          . '	</select>'
                          . '	<input id="3_startyear' . $i . '" name="3_startyear[]" value="' . $val->startyear . '" type="text" placeholder="Year" class="form-control input-md number datepickeryear" required="">'
                          . '	</div>'
                          . '	<div class="" id="3_ispresent1' . $i . '" ' . (isset($val->is_present) ? (($val->is_present == "1") ? 'style="display:none;"' : "") : "") . '>-</div>'
                          . '	<div class="" id="3_ispresent2' . $i . '" ' . (isset($val->is_present) ? (($val->is_present == "1") ? 'style="display:none;"' : "") : "") . '>'
                          . '	<select id="3_enddate' . $i . '" name="3_enddate[]" style="height: 34px;">'
                          . '		<option value="">---</option>'
                          . '		<option value="1" ' . (isset($val->endmonth) ? (($val->endmonth == "1") ? 'selected="true"' : "") : "") . ' >Januari</option>'
                          . '		<option value="2" ' . (isset($val->endmonth) ? (($val->endmonth == "2") ? 'selected="true"' : "") : "") . '>Pebruari</option>'
                          . '		<option value="3" ' . (isset($val->endmonth) ? (($val->endmonth == "3") ? 'selected="true"' : "") : "") . '>Maret</option>'
                          . '		<option value="4" ' . (isset($val->endmonth) ? (($val->endmonth == "4") ? 'selected="true"' : "") : "") . '>April</option>'
                          . '		<option value="5" ' . (isset($val->endmonth) ? (($val->endmonth == "5") ? 'selected="true"' : "") : "") . '>Mei</option>'
                          . '		<option value="6" ' . (isset($val->endmonth) ? (($val->endmonth == "6") ? 'selected="true"' : "") : "") . '>Juni</option>'
                          . '		<option value="7" ' . (isset($val->endmonth) ? (($val->endmonth == "7") ? 'selected="true"' : "") : "") . '>Juli</option>'
                          . '		<option value="8" ' . (isset($val->endmonth) ? (($val->endmonth == "8") ? 'selected="true"' : "") : "") . '>Agustus</option>'
                          . '		<option value="9" ' . (isset($val->endmonth) ? (($val->endmonth == "9") ? 'selected="true"' : "") : "") . '>September</option>'
                          . '		<option value="10" ' . (isset($val->endmonth) ? (($val->endmonth == "10") ? 'selected="true"' : "") : "") . '>Oktober</option>'
                          . '		<option value="11" ' . (isset($val->endmonth) ? (($val->endmonth == "11") ? 'selected="true"' : "") : "") . '>Nopember</option>'
                          . '		<option value="12" ' . (isset($val->endmonth) ? (($val->endmonth == "12") ? 'selected="true"' : "") : "") . '>Desember</option>'
                          . '	</select>'
                          . '	<input id="3_endyear' . $i . '" name="3_endyear[]" value="' . $val->endyear . '" type="text" placeholder="Year" class="form-control input-md number datepickeryear" >'
                          . '	</div>'
                          . '	<label class="form-check-label"><input type="hidden" name="3_workx[]" value="' . (isset($val->is_present) ? (($val->is_present == "1") ? '1' : "0") : "0") . '"><input type="checkbox" id="3_work' . $i . '" name="3_work[]" class="form-check-input" value="1" data-id="' . $i . '" ' . (isset($val->is_present) ? (($val->is_present == "1") ? 'checked="true"' : "") : "") . '>Sampai saat ini</label>'
                          . '</td>'


                          . '<td class="col-md-2">'
                          . '<input id="3_instansi' . $i . '" name="3_instansi[]" value="' . $val->company . '" type="text" placeholder="" class="form-control input-md " style="width:200px;"  required=""><input id="3_title' . $i . '" name="3_title[]" value="' . $val->title . '" type="text" placeholder="" class="form-control input-md " style="width:200px;"  required="">'
                          . '</td>'
                          . '<td class="col-md-2">'
                          . '<input id="3_namaproyek' . $i . '" name="3_namaproyek[]" onchange="generate_option()" value="' . $val->actv . '" type="text" placeholder="" class="form-control input-md "  required="">'
                          . '</td>'
                          . '<td class="col-md-2">'
                          . '<input id="3_pemberitugas' . $i . '" name="3_pemberitugas[]" value="' . $val->company . '" type="text" placeholder="" class="form-control input-md " style="width:200px;" required="">'
                          . '</td>'
                          . '<td class="col-md-2">'
                          . '<input id="3_location' . $i . '" name="3_location[]" value="' . $val->location . '" type="text" placeholder="" class="form-control input-md " style="width:200px;" required="">'
                          . '<input id="3_provinsi' . $i . '" name="3_provinsi[]" value="' . $val->provinsi . '" type="text" placeholder="" class="form-control input-md " style="width:200px;" required="">'
                          . '<input id="3_negara' . $i . '" name="3_negara[]" value="' . $val->negara . '" type="text" placeholder="" class="form-control input-md " style="width:200px;" required="">'
                          . '</td>'

                          . '<td class="col-md-2">'
                          . '<select id="3_periode' . $i . '" name="3_periode[]" style="height: 34px;" required="">'
                          . '<option value="">--Choose--</option>';
                        if (isset($bp_3_p)) {
                          foreach ($bp_3_p as $val2) {

                            $birthdate_ts = strtotime("$val->startyear-$val->startmonth-1");
                            $birthdate_ts2 = strtotime("$val->endyear-$val->endmonth-1");
                            $diff = abs($birthdate_ts2 - $birthdate_ts);
                            $tempid = "";
                            $years = floor($diff / (365 * 60 * 60 * 24));
                            if ($years < 4)
                              $tempid = '1';
                            else if ($years < 8)
                              $tempid = '2';
                            else if ($years <= 10)
                              $tempid = '3';
                            else if ($years > 10)
                              $tempid = '4';
                            //echo $tempid;
                            //$months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));
                            //$days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
                            //printf("%d years, %d months, %d days\n", $years, $months, $days);

                            echo '<option value="' . $val2->value . '" ' . (isset($tempid) ? ((trim($tempid) == trim($val2->value)) ? 'selected="true"' : "") : "") . '>' . $val2->desc . '</option>'; //'.(isset($val->degree)?(($val->degree==$val2->value)?'selected="true"':""):"").'
                          }
                        }
                        echo '</select>'
                          . '</td>'

                          /*
						. '<td class="col-md-2">'
						. '<input id="3_jam' .$i.'" name="3_jam[]" value="'.$val->hour.'" type="text" placeholder="" class="form-control input-md "  required="">'
						. '</td>'
						. '<td class="col-md-2">'
						. '<input id="3_lic' .$i.'" name="3_lic[]" value="" type="text" placeholder="" class="form-control input-md "  required="">'
						. '</td>'
						. '<td class="col-md-2">'
						. '<input id="3_url' .$i.'" name="3_url[]" value="" type="text" placeholder="" class="form-control input-md "  required="">'
						. '</td>'
						*/


                          . '<td class="col-md-2">'
                          . '<select id="3_posisi' . $i . '" name="3_posisi[]" style="height: 34px;" required="">'
                          . '<option value="">--Choose--</option>';
                        if (isset($bp_3_q)) {
                          foreach ($bp_3_q as $val2) {
                            echo '<option value="' . $val2->value . '" ' . (isset($val->position) ? ((trim($val->position) == trim($val2->desc)) ? 'selected="true"' : "") : "") . '>' . $val2->desc . '</option>'; //'.(isset($val->degree)?(($val->degree==$val2->value)?'selected="true"':""):"").'
                          }
                        }
                        echo '</select>'
                          . '</td>'

                          . '<td class="col-md-2">'
                          . '<input id="3_nilaipry' . $i . '" name="3_nilaipry[]" value="" type="text" placeholder="" class=" "  required="">'
                          . '</td>'
                          . '<td class="col-md-2">'
                          . '<input id="3_nilaijasa' . $i . '" name="3_nilaijasa[]" value="" type="text" placeholder="" class=" "  required="">'
                          . '</td>'
                          . '<td class="col-md-2">'
                          . '<select id="3_nilaisdm' . $i . '" name="3_nilaisdm[]" style="height: 34px;" required="">'
                          . '<option value="">--Choose--</option>
								<option value="1">Sedikit</option>
								<option value="2">Sedang</option>
								<option value="3">Banyak</option>
								<option value="4">Sangat Banyak</option>';
                        echo '</select>'
                          . '</td>'
                          . '<td class="col-md-2">'
                          . '<select id="3_nilaisulit' . $i . '" name="3_nilaisulit[]" style="height: 34px;" required="">'
                          . '<option value="">--Choose--</option>
								<option value="1">Rendah</option>
								<option value="2">Sedang</option>
								<option value="3">Tinggi</option>
								<option value="4">Sangat Tinggi</option>';
                        echo '</select>'
                          . '</td>'

                          . '<td class="col-md-2">'
                          . '<select id="3_nilaiproyek' . $i . '" name="3_nilaiproyek[]" style="height: 34px;" required="">'
                          . '<option value="">--Choose--</option>';
                        if (isset($bp_3_r)) {
                          foreach ($bp_3_r as $val2) {
                            echo '<option value="' . $val2->value . '" ' . (isset($val->nilaiproyek) ? ((trim($val->nilaiproyek) == trim($val2->desc)) ? 'selected="true"' : "") : "") . '>' . $val2->desc . '</option>'; //'.(isset($val->degree)?(($val->degree==$val2->value)?'selected="true"':""):"").'
                          }
                        }
                        echo '</select>'
                          . '</td>'

                          . '<td class="col-md-8">'
                          . '	<div class="form-group">'
                          . '		<div id="3_avatarattexp' . $i . '">'
                          . '		<input type="hidden" name="3_exp_image_url[]" value="' . ((isset($val->attachment)) ? $val->attachment : '') . '" style="display: inline-block;">'
                          . '		' . ((isset($val->attachment)) ? "<a href='" . base_url() . "/assets/uploads/" . $val->attachment . "' target='_blank' class='ava_discus'>" . $val->attachment . "</a>" : '') . ''
                          . '		</div>'
                          . '		<div id="3_errUploadattexp' . $i . '"></div>'
                          . '		<input type="file" name="3_attexp[]" id="3_attexp' . $i . '" class="form-control input-md" onchange="upload_exp(\'attexp' . $i . '\')">'
                          . '	</div>'
                          . '</td>'

                          . '<td class="col-md-2">'

                          . '<textarea id="3_uraian' . $i . '" name="3_uraian[]" class="form-control input-md " style="width:200px;" rows="8">' . $val->description . '</textarea>'
                          . '</td>'

                          . '<td class="col-md-2">'

                          //. '<textarea id="3_uraian' .$i. '" name="3_uraian[]" class="form-control input-md " style="width:200px;" rows="8"></textarea>'


                          . '<select id="3_komp' . $i . '" name="3_komp[' . $i . '][]" class="input-md" style="height: 200px;" required="" multiple>'
                          . '<option value="">--Choose--</option>';


                        if (isset($m_act_3)) {
                          $temp = true;
                          foreach ($m_act_3 as $val) {
                            if (strlen($val->value) < 8) {
                              if (!$temp)
                                echo '</optgroup>';
                              echo  '<optgroup label="' . $val->value . ' - ' . $val->title . '">';
                              $temp = true;
                            } else {
                              $temp = false;
                              echo  '<option value="' . $val->value . '" >' . $val->value . ' - ' . $val->title . '</option>';
                            }
                          }
                        }


                        echo '</select>'

                          . '</td>'



                          /*
								. '<td class="col-md-4">'
				
								. '<table width="100%" id="'.$tableID.'komp_' . $i  . '" class="table">'
								. '</table>'
								. '<div class="col-md-12" style="padding-bottom:20px;">'
								. '<button type="button" class="btn btn-primary" onclick="addKomp(\''.$tableID.'komp_' . $i  . '\')">+ Tambah Kompetensi</button>'
								. '</div>'
								
								. '</td>'
								*/

                          . '<td class="td-action">'
                          . '<button type="button" class="btn btn-danger btn-xs ' . $tableID . '-item-remove-button" data-id="' . $i  . '">'
                          . '<i class="fa fa-trash-o fa-fw"></i>X'
                          . '</button>'
                          . '</td>'
                          . '</tr>
						';

                        $i++;
                      }
                    }
                    ?>

                  </tbody>
                </table>
              </div>
              <div class="col-md-12" style="padding-bottom:20px;">
                <button type="button" class="btn btn-primary" onclick="add3('kup')">+ Tambah Baru</button>
              </div>
              <!--
		<p>*) KOMPETENSI: Isi dengan nomor Uraian Kegiatan Kompetensi yang Anda anggap persyaratannya telah terpenuhi dengan aktifitas Anda di sini</p>
		<p>CATATAN : 	<br />
<span class="one">(1)</span>	Untuk setiap aktifitas yang Anda anggap menonjol, tuliskan uraian singkat di Lembar Dokumentasi Pengalaman Kerja (LAMPIRAN 1) dengan penomoran yang sama<br />
<span class="green">(2)</span>	Pengalaman kerja meliputi bidang Perencanaan/Perancangan, Pengawasan/Supervisi<br />
<span class="three">(3)</span>	Untuk Jabatan yang Anda anggap menonjol, uraikan lebih rinci pada Lampiran 1<br />
<span class="four">(4)</span>	Tuliskan Jenis dan Besar Perusahaan/Instansi (dapat dinyatakan dengan kelas perusahaan dan jumlah tenaga profesional yang dibawahi)</p>
-->

              <div class="col-md-12" style="padding-bottom:20px;text-align:center;">
                <button type="button" name="savefaip" value="1" class="btn btn-next savefaip">Save & Continue</button>
              </div>
            </section>

            <h3>IV</h3>
            <section>

              <label>IV. Pengalaman Mengajar Pelajaran Keinsinyuran dan/atau Manajemen dan/atau
                Pengalaman Mengembangkan Pendidikan/Pelatihan Keinsinyuran dan/atau Manajemen
                <span class="comp">(W2,W3,W4,P5)</span></label><br />



              <br />
              <div style="width:3000px;">
                <table width="100%" id="tman" class="table">
                  <thead>
                    <tr>
                      <th width="5%"><label>No. </label></th>
                      <th width="10%"><label>Nama Perguruan Tinggi / Lembaga</label></th>
                      <th width="10%"><label>Nama mata ajaran dan uraian singkat yang diajarkan / dikembangkan</label></th>
                      <th width="5%"><label>KOTA/KABUPATEN</label></th>
                      <th width="5%"><label>PROVINSI</label></th>
                      <th width="5%"><label>NEGARA</label></th>
                      <th width="5%"><label>PERIODA</label></th>
                      <th width="5%"><label>Jabatan pada Perguruan Tinggi / Lembaga </label></th>
                      <th width="10%"><label>Jumlah Jam / SKS</label></th>
                      <th width="5%"><label>Dokumen Pendukung<br /> <span class="red">(Max. 700KB, image atau PDF)</span></label></th>
                      <th width="30%"><label>Uraian Singkat Aktifitas</label></th>
                      <th width="30%"><label>KLAIM KOMPETENSI </label></th>
                      <!--<th width="10%"><label>KOMPETENSI *)</label></th>-->
                      <th width="5%"><label></label></th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    $bp_4_p = array();
                    $bp_4_q = array();
                    $bp_4_r = array();
                    if (isset($bp_4[0])) {
                      foreach ($bp_4 as $val) {
                        if ($val->faip_type == "p")
                          $bp_4_p[] = $val;
                        else if ($val->faip_type == "q")
                          $bp_4_q[] = $val;
                        else if ($val->faip_type == "r")
                          $bp_4_r[] = $val;
                      }
                    }
                    ?>

                  </tbody>
                </table>
              </div>
              <div class="col-md-12" style="padding-bottom:20px;">
                <button type="button" class="btn btn-primary" onclick="add4('man')">+ Tambah Baru</button>
              </div>

              <div class="col-md-12" style="padding-bottom:20px;text-align:center;">
                <button type="button" name="savefaip" value="1" class="btn btn-next savefaip">Save & Continue</button>
              </div>
            </section>

            <h3>V.1</h3>
            <section>
              <div class="title">
                <h4>V. PUBLIKASI, KOMUNIKASI DAN TEMUAN/INOVASI DI BIDANG KEINSINYURAN</h4>
              </div>
              <label>V.1 Karya Tulis di Bidang Keinsinyuran yang Dipublikasikan <span class="comp">(W4)</span></label><br />
              <div style="width:2600px;">
                <table width="100%" id="tpub" class="table">
                  <thead>
                    <tr>
                      <th width="5%"><label>No</label></th>
                      <th width="10%"><label>JUDUL KARYA TULIS</label></th>
                      <th width="10%"><label>NAMA MEDIA PUBLIKASI </label></th>
                      <th width="5%"><label>KOTA/KABUPATEN</label></th>
                      <th width="5%"><label>PROVINSI</label></th>
                      <th width="5%"><label>NEGARA</label></th>
                      <th width="5%"><label>Kapan Karya Tulis dipublikasikan?</label></th>

                      <th width="5%"><label>Media Publikasi tingkat</label></th>
                      <th width="5%" style="display:none;"><label>Berapa Karya Tulis yang sudah dipublikasikan?</label></th>

                      <th width="20%"><label>URAIAN SINGKAT MATERI YANG DIPUBLIKASIKAN</label></th>

                      <th width="10%"><label>Tingkat kesulitan dan manfaatnya materi yang diplubikasian</label></th>

                      <th width="5%"><label>Dokumen Pendukung<br /> <span class="red">(Max. 700KB, image atau PDF)</span></label></th>
                      <th width="10%"><label>KLAIM KOMPETENSI </label></th>
                      <!--<th width="10%"><label>KOMPETENSI *)</label></th>-->
                      <th width="5%"><label></label></th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    $bp_51_p = array();
                    $bp_51_q = array();
                    $bp_51_r = array();
                    if (isset($bp_51[0])) {
                      foreach ($bp_51 as $val) {
                        if ($val->faip_type == "p")
                          $bp_51_p[] = $val;
                        else if ($val->faip_type == "q")
                          $bp_51_q[] = $val;
                        else if ($val->faip_type == "r")
                          $bp_51_r[] = $val;
                      }
                    }
                    if (count($user_publication1) > 0) {
                      $i = 1;
                      foreach ($user_publication1 as $val) {

                        $years = count($user_publication1);

                        $data['bp_51'] = $this->main_mod->msrwhere('m_bakuan_penilaian', array('faip_num' => '51', 'faip_type' => 'p'), 'id', 'desc')->result();

                        foreach ($data['bp_51'] as $valbp) {
                          $condition = substr($valbp->formula, 0, 2);
                          if ($condition == "<=") {
                            if ($years <= substr($valbp->formula, 2, 5)) $tempidx = $valbp->value;
                          } else if ($condition == "<") {
                            if ($years < substr($valbp->formula, 2, 5)) $tempidx = $valbp->value;
                          } else if ($condition == ">") {
                            if ($years > substr($valbp->formula, 2, 5)) $tempidx = $valbp->value;
                          } else if ($condition == ">=") {
                            if ($years >= substr($valbp->formula, 2, 5)) $tempidx = $valbp->value;
                          } else if ($condition == "=") {
                            if ($years == substr($valbp->formula, 2, 5)) $tempidx = $valbp->value;
                          }
                          //echo substr($valbp->formula,2,5).'*<br />';
                        }
                        $p = $tempidx;
                        $q = 0;
                        $r = 0;
                        foreach ($bp_51_q as $val2) {
                          if (trim($val->tingkatmedia) == trim($val2->desc))
                            $q = $val2->value;
                        }

                        foreach ($bp_51_r as $val2) {
                          if (trim($val->tingkat) == trim($val2->desc))
                            $r = $val2->value;
                        }


                        $t = $p * $q * $r;

                        $tableID = 'pub';

                        echo '
						<tr class=" ' . $tableID . '-item" data-id="' . $i . '" >'
                          . '<td class="">'
                          . $i
                          . '</td>'


                          . '<td class="col-md-2"><input id="51_t' . $i . '" name="51_t[]" value="' . $t . '" type="hidden">'
                          . '<input id="51_nama' . $i . '" name="51_nama[]" value="' . $val->topic . '" type="text" placeholder="" class="form-control input-md " style="width:200px;" required="">'
                          . '</td>'
                          . '<td class="col-md-2">'
                          . '<input id="51_media' . $i . '" name="51_media[]" value="' . $val->media . '" type="text" placeholder="" class="form-control input-md " style="width:200px;" required="">'
                          . '</td>'
                          . '<td class="col-md-2">'
                          . '<input id="51_location' . $i . '" name="51_location[]" value="' . $val->location . '" type="text" placeholder="" class="form-control input-md " style="width:200px;" required="">'
                          . '</td>'
                          . '<td class="col-md-2">'
                          . '<input id="51_provinsi' . $i . '" name="51_provinsi[]" value="' . $val->provinsi . '" type="text" placeholder="" class="form-control input-md " style="width:200px;" required="">'
                          . '</td>'
                          . '<td class="col-md-2">'
                          . '<input id="51_negara' . $i . '" name="51_negara[]" value="' . $val->negara . '" type="text" placeholder="" class="form-control input-md " style="width:200px;" required="">'
                          . '</td>'
                          /*
						. '<td class="col-md-2">'
								. '<select id="51_periode' .$i.'" name="51_periode[]" style="height: 34px;" required="">'
								. '<option value="">--Choose--</option>';
									if(isset($bp_51_p)){
										foreach($bp_51_p as $val2){
										
											$birthdate_ts=strtotime("$val->startyear-$val->startmonth-1");
											$birthdate_ts2=strtotime("$val->endyear-$val->endmonth-1");
											$diff = abs($birthdate_ts2 - $birthdate_ts);
											$tempid="";
											$years = floor($diff / (365*60*60*24));
											if($years<4)
												$tempid='1';
											else if($years<8)
												$tempid='2';
											else if($years<=10)
												$tempid='3';
											else if($years>10)
												$tempid='4';
											//echo $tempid;
											//$months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));
											//$days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
											//printf("%d years, %d months, %d days\n", $years, $months, $days);
										
											echo '<option value="'.$val2->value.'" '.(isset($tempid)?((trim($tempid)==trim($val2->value))?'selected="true"':""):"").'>'.$val2->desc.'</option>';//'.(isset($val->degree)?(($val->degree==$val2->value)?'selected="true"':""):"").'
										}
									}
								echo '</select>'									
						. '</td>'
						*/


                          . '<td class="col-md-2">'
                          . '	<div class="">'
                          . '	<select id="51_startdate' . $i . '" name="51_startdate[]" class="form-control input-md" required="">'
                          . '		<option value="">---</option>'
                          . '		<option value="1" ' . (isset($val->startmonth) ? (($val->startmonth == "1") ? 'selected="true"' : "") : "") . ' >Januari</option>'
                          . '		<option value="2" ' . (isset($val->startmonth) ? (($val->startmonth == "2") ? 'selected="true"' : "") : "") . '>Pebruari</option>'
                          . '		<option value="3" ' . (isset($val->startmonth) ? (($val->startmonth == "3") ? 'selected="true"' : "") : "") . '>Maret</option>'
                          . '		<option value="4" ' . (isset($val->startmonth) ? (($val->startmonth == "4") ? 'selected="true"' : "") : "") . '>April</option>'
                          . '		<option value="5" ' . (isset($val->startmonth) ? (($val->startmonth == "5") ? 'selected="true"' : "") : "") . '>Mei</option>'
                          . '		<option value="6" ' . (isset($val->startmonth) ? (($val->startmonth == "6") ? 'selected="true"' : "") : "") . '>Juni</option>'
                          . '		<option value="7" ' . (isset($val->startmonth) ? (($val->startmonth == "7") ? 'selected="true"' : "") : "") . '>Juli</option>'
                          . '		<option value="8" ' . (isset($val->startmonth) ? (($val->startmonth == "8") ? 'selected="true"' : "") : "") . '>Agustus</option>'
                          . '		<option value="9" ' . (isset($val->startmonth) ? (($val->startmonth == "9") ? 'selected="true"' : "") : "") . '>September</option>'
                          . '		<option value="10" ' . (isset($val->startmonth) ? (($val->startmonth == "10") ? 'selected="true"' : "") : "") . '>Oktober</option>'
                          . '		<option value="11" ' . (isset($val->startmonth) ? (($val->startmonth == "11") ? 'selected="true"' : "") : "") . '>Nopember</option>'
                          . '		<option value="12" ' . (isset($val->startmonth) ? (($val->startmonth == "12") ? 'selected="true"' : "") : "") . '>Desember</option>'
                          . '	</select>'
                          . '	<input id="51_startyear' . $i . '" name="51_startyear[]" value="' . $val->startyear . '" type="text" placeholder="Year" class="form-control input-md number datepickeryear" required="">'
                          . '	</div>'
                          . '</td>'



                          /*
						. '<td class="col-md-2">'
						. '<input id="51_jam' .$i.'" name="51_jam[]" value="'.$val->hour.'" type="text" placeholder="" class="form-control input-md "  required="">'
						. '</td>'
						. '<td class="col-md-2">'
						. '<input id="51_lic' .$i.'" name="51_lic[]" value="" type="text" placeholder="" class="form-control input-md "  required="">'
						. '</td>'
						. '<td class="col-md-2">'
						. '<input id="51_url' .$i.'" name="51_url[]" value="" type="text" placeholder="" class="form-control input-md "  required="">'
						. '</td>'
						*/


                          . '<td class="col-md-2">'
                          . '<select id="51_tingkatmedia' . $i . '" name="51_tingkatmedia[]" style="height: 34px;" required="">'
                          . '<option value="">--Choose--</option>';
                        if (isset($bp_51_q)) {
                          foreach ($bp_51_q as $val2) {
                            echo '<option value="' . $val2->value . '" ' . (isset($val->tingkat) ? ((strpos($val2->desc, $val->tingkat) !== false) ? 'selected="true"' : "") : "") . '>' . $val2->desc . '</option>'; //'.(isset($val->degree)?(($val->degree==$val2->value)?'selected="true"':""):"").'
                          }
                        }
                        echo '</select>'
                          . '</td>'

                          . '<td class="col-md-2" style="display:none;">'
                          . '<select id="51_jumlah' . $i . '" name="51_jumlah[]" style="height: 34px;" >'
                          . '<option value="">--Choose--</option>';
                        if (isset($bp_51_p)) {
                          foreach ($bp_51_p as $val2) {
                            echo '<option value="' . $val2->value . '" ' . (isset($val->position) ? ((trim($val->position) == trim($val2->desc)) ? 'selected="true"' : "") : "") . '>' . $val2->desc . '</option>'; //'.(isset($val->degree)?(($val->degree==$val2->value)?'selected="true"':""):"").'
                          }
                        }
                        echo '</select>'
                          . '</td>'

                          . '<td class="col-md-2">'
                          . '<textarea id="51_uraian' . $i . '" name="51_uraian[]" class="form-control input-md " style="width:200px;" rows="8">' . $val->description . '</textarea>'
                          . '</td>'

                          . '<td class="col-md-2">'
                          . '<select id="51_tingkat' . $i . '" name="51_tingkat[]" style="height: 34px;" required="">'
                          . '<option value="">--Choose--</option>';
                        if (isset($bp_51_r)) {
                          foreach ($bp_51_r as $val2) {
                            echo '<option value="' . $val2->value . '" ' . (isset($val->tingkatmedia) ? ((trim($val->tingkatmedia) == trim($val2->desc)) ? 'selected="true"' : "") : "") . '>' . $val2->desc . '</option>'; //'.(isset($val->degree)?(($val->degree==$val2->value)?'selected="true"':""):"").'
                          }
                        }
                        echo '</select>'
                          . '</td>'


                          . '<td class="col-md-8">'
                          . '	<div class="form-group">'
                          . '		<div id="51_avatarattpublication1' . $i . '">'
                          . '		<input type="hidden" name="51_publication1_image_url[]" value="' . ((isset($val->attachment)) ? $val->attachment : '') . '" style="display: inline-block;">'
                          . '		' . ((isset($val->attachment)) ? "<a href='" . base_url() . "/assets/uploads/" . $val->attachment . "' target='_blank' class='ava_discus'>" . $val->attachment . "</a>" : '') . ''
                          . '		</div>'
                          . '		<div id="51_errUploadattpublication1' . $i . '"></div>'
                          . '		<input type="file" name="51_attpublication1[]" id="51_attpublication1' . $i . '" class="form-control input-md" onchange="upload_publication1(\'attpublication1' . $i . '\')">'
                          . '	</div>'
                          . '</td>'

                          . '<td class="col-md-2">'

                          //. '<textarea id="51_uraian' .$i. '" name="51_uraian[]" class="form-control input-md " style="width:200px;" rows="8"></textarea>'


                          . '<select id="51_komp' . $i . '" name="51_komp[' . $i . '][]" class="input-md" style="height: 200px;" required="" multiple>'
                          . '<option value="">--Choose--</option>';


                        if (isset($m_act_51)) {
                          $temp = true;
                          foreach ($m_act_51 as $val) {
                            if (strlen($val->value) < 8) {
                              if (!$temp)
                                echo '</optgroup>';
                              echo  '<optgroup label="' . $val->value . ' - ' . $val->title . '">';
                              $temp = true;
                            } else {
                              $temp = false;
                              echo  '<option value="' . $val->value . '" >' . $val->value . ' - ' . $val->title . '</option>';
                            }
                          }
                        }


                        echo '</select>'

                          . '</td>'



                          /*
								. '<td class="col-md-4">'
				
								. '<table width="100%" id="'.$tableID.'komp_' . $i  . '" class="table">'
								. '</table>'
								. '<div class="col-md-12" style="padding-bottom:20px;">'
								. '<button type="button" class="btn btn-primary" onclick="addKomp(\''.$tableID.'komp_' . $i  . '\')">+ Tambah Kompetensi</button>'
								. '</div>'
								
								. '</td>'
								*/

                          . '<td class="td-action">'
                          . '<button type="button" class="btn btn-danger btn-xs ' . $tableID . '-item-remove-button" data-id="' . $i  . '">'
                          . '<i class="fa fa-trash-o fa-fw"></i>X'
                          . '</button>'
                          . '</td>'
                          . '</tr>
						';

                        $i++;
                      }
                    }
                    ?>
                  </tbody>
                </table>
              </div>
              <div class="col-md-12" style="padding-bottom:20px;">
                <button type="button" class="btn btn-primary" onclick="add51('pub')">+ Tambah Baru</button>
              </div>
              <p>Apabila perlu tuliskan pada lembar tambahan</p>
              <p>*) KOMPETENSI: Isi dengan nomor Uraian Kegiatan Kompetensi yang Anda anggap persyaratannya telah terpenuhi dengan aktifitas Anda di sini</p>

              <div class="col-md-12" style="padding-bottom:20px;text-align:center;">
                <button type="button" name="savefaip" value="1" class="btn btn-next savefaip">Save & Continue</button>
              </div>
            </section>

            <h3>V.2</h3>
            <section>
              <label>V.2 Makalah/Tulisan Yang Disajikan Dalam Seminar/Lokakarya Keinsinyuran <span class="comp">(W4)</span></label><br />
              <div style="width:2600px;">
                <table width="100%" id="tlok" class="table">
                  <thead>
                    <tr>
                      <th width="5%"><label>No</label></th>
                      <th width="10%"><label>JUDUL MAKALAH/TULISAN </label></th>
                      <th width="10%"><label>NAMA SEMINAR/LOKAKARYA</label></th>
                      <th width="10%"><label>PENYELENGGARA</label></th>
                      <th width="5%"><label>KOTA/KABUPATEN</label></th>
                      <th width="5%"><label>PROVINSI</label></th>
                      <th width="5%"><label>NEGARA</label></th>
                      <th width="5%"><label>Kapan Seminar/Lokakarya diselenggarakan?</label></th>

                      <th width="5%"><label>Seminar/Lokakarya tingkat</label></th>
                      <th width="5%" style="display:none;"><label>Berapa Tulisan / Makalah yang sudah disajikan pada Seminar/Lokakarya?</label></th>

                      <th width="10%"><label>Uraian singkat Tulisan / Makalah yang disajikan</label></th>

                      <th width="10%"><label>Tingkat kesulitan dan manfaatnya materi yang disajikan</label></th>

                      <th width="5%"><label>Dokumen Pendukung<br /> <span class="red">(Max. 700KB, image atau PDF)</span></label></th>
                      <th width="10%"><label>KLAIM KOMPETENSI </label></th>
                      <!--<th width="10%"><label>KOMPETENSI *)</label></th>-->
                      <th width="5%"><label></label></th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    $bp_52_p = array();
                    $bp_52_q = array();
                    $bp_52_r = array();
                    if (isset($bp_52[0])) {
                      foreach ($bp_52 as $val) {
                        if ($val->faip_type == "p")
                          $bp_52_p[] = $val;
                        else if ($val->faip_type == "q")
                          $bp_52_q[] = $val;
                        else if ($val->faip_type == "r")
                          $bp_52_r[] = $val;
                      }
                    }
                    if (count($user_publication2) > 0) {
                      $i = 1;
                      foreach ($user_publication2 as $val) {

                        $years = count($user_publication2);

                        $data['bp_52'] = $this->main_mod->msrwhere('m_bakuan_penilaian', array('faip_num' => '52', 'faip_type' => 'p'), 'id', 'desc')->result();

                        foreach ($data['bp_52'] as $valbp) {
                          $condition = substr($valbp->formula, 0, 2);
                          if ($condition == "<=") {
                            if ($years <= substr($valbp->formula, 2, 5)) $tempidx = $valbp->value;
                          } else if ($condition == "<") {
                            if ($years < substr($valbp->formula, 2, 5)) $tempidx = $valbp->value;
                          } else if ($condition == ">") {
                            if ($years > substr($valbp->formula, 2, 5)) $tempidx = $valbp->value;
                          } else if ($condition == ">=") {
                            if ($years >= substr($valbp->formula, 2, 5)) $tempidx = $valbp->value;
                          } else if ($condition == "=") {
                            if ($years == substr($valbp->formula, 2, 5)) $tempidx = $valbp->value;
                          }
                          //echo substr($valbp->formula,2,5).'*<br />';
                        }
                        $p = $tempidx;
                        $q = 0;
                        $r = 0;
                        foreach ($bp_52_q as $val2) {
                          if (trim($val->tingkatmedia) == trim($val2->desc))
                            $q = $val2->value;
                        }

                        foreach ($bp_52_r as $val2) {
                          if (trim($val->tingkat) == trim($val2->desc))
                            $r = $val2->value;
                        }


                        $t = $p * $q * $r;

                        $tableID = 'lok';

                        echo '
						<tr class=" ' . $tableID . '-item" data-id="' . $i . '" >'
                          . '<td class="">'
                          . $i
                          . '</td>'


                          . '<td class="col-md-2"><input id="52_t' . $i . '" name="52_t[]" value="' . $t . '" type="hidden">'
                          . '<input id="52_judul' . $i . '" name="52_judul[]" value="' . $val->topic . '" type="text" placeholder="" class="form-control input-md " style="width:200px;" required="">'
                          . '</td>'
                          . '<td class="col-md-2">'
                          . '<input id="52_nama' . $i . '" name="52_nama[]" value="' . $val->event . '" type="text" placeholder="" class="form-control input-md " style="width:200px;" required="">'
                          . '</td>'
                          . '<td class="col-md-2">'
                          . '<input id="52_penyelenggara' . $i . '" name="52_penyelenggara[]" value="' . $val->media . '" type="text" placeholder="" class="form-control input-md "  style="width:200px;" required="">'
                          . '</td>'
                          . '<td class="col-md-2">'
                          . '<input id="52_location' . $i . '" name="52_location[]" value="' . $val->location . '" type="text" placeholder="" class="form-control input-md " style="width:200px;" required="">'
                          . '</td>'
                          . '<td class="col-md-2">'
                          . '<input id="52_provinsi' . $i . '" name="52_provinsi[]" value="' . $val->provinsi . '" type="text" placeholder="" class="form-control input-md " style="width:200px;" required="">'
                          . '</td>'
                          . '<td class="col-md-2">'
                          . '<input id="52_negara' . $i . '" name="52_negara[]" value="' . $val->negara . '" type="text" placeholder="" class="form-control input-md " style="width:200px;" required="">'
                          . '</td>'
                          /*
						. '<td class="col-md-2">'
								. '<select id="52_periode' .$i.'" name="52_periode[]" style="height: 34px;" required="">'
								. '<option value="">--Choose--</option>';
									if(isset($bp_52_p)){
										foreach($bp_52_p as $val2){
										
											$birthdate_ts=strtotime("$val->startyear-$val->startmonth-1");
											$birthdate_ts2=strtotime("$val->endyear-$val->endmonth-1");
											$diff = abs($birthdate_ts2 - $birthdate_ts);
											$tempid="";
											$years = floor($diff / (365*60*60*24));
											if($years<4)
												$tempid='1';
											else if($years<8)
												$tempid='2';
											else if($years<=10)
												$tempid='3';
											else if($years>10)
												$tempid='4';
											//echo $tempid;
											//$months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));
											//$days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
											//printf("%d years, %d months, %d days\n", $years, $months, $days);
										
											echo '<option value="'.$val2->value.'" '.(isset($tempid)?((trim($tempid)==trim($val2->value))?'selected="true"':""):"").'>'.$val2->desc.'</option>';//'.(isset($val->degree)?(($val->degree==$val2->value)?'selected="true"':""):"").'
										}
									}
								echo '</select>'									
						. '</td>'
						*/


                          . '<td class="col-md-2">'
                          . '	<div class="">'
                          . '	<select id="52_startdate' . $i . '" name="52_startdate[]" class="form-control input-md" required="">'
                          . '		<option value="">---</option>'
                          . '		<option value="1" ' . (isset($val->startmonth) ? (($val->startmonth == "1") ? 'selected="true"' : "") : "") . ' >Januari</option>'
                          . '		<option value="2" ' . (isset($val->startmonth) ? (($val->startmonth == "2") ? 'selected="true"' : "") : "") . '>Pebruari</option>'
                          . '		<option value="3" ' . (isset($val->startmonth) ? (($val->startmonth == "3") ? 'selected="true"' : "") : "") . '>Maret</option>'
                          . '		<option value="4" ' . (isset($val->startmonth) ? (($val->startmonth == "4") ? 'selected="true"' : "") : "") . '>April</option>'
                          . '		<option value="5" ' . (isset($val->startmonth) ? (($val->startmonth == "5") ? 'selected="true"' : "") : "") . '>Mei</option>'
                          . '		<option value="6" ' . (isset($val->startmonth) ? (($val->startmonth == "6") ? 'selected="true"' : "") : "") . '>Juni</option>'
                          . '		<option value="7" ' . (isset($val->startmonth) ? (($val->startmonth == "7") ? 'selected="true"' : "") : "") . '>Juli</option>'
                          . '		<option value="8" ' . (isset($val->startmonth) ? (($val->startmonth == "8") ? 'selected="true"' : "") : "") . '>Agustus</option>'
                          . '		<option value="9" ' . (isset($val->startmonth) ? (($val->startmonth == "9") ? 'selected="true"' : "") : "") . '>September</option>'
                          . '		<option value="10" ' . (isset($val->startmonth) ? (($val->startmonth == "10") ? 'selected="true"' : "") : "") . '>Oktober</option>'
                          . '		<option value="11" ' . (isset($val->startmonth) ? (($val->startmonth == "11") ? 'selected="true"' : "") : "") . '>Nopember</option>'
                          . '		<option value="12" ' . (isset($val->startmonth) ? (($val->startmonth == "12") ? 'selected="true"' : "") : "") . '>Desember</option>'
                          . '	</select>'
                          . '	<input id="52_startyear' . $i . '" name="52_startyear[]" value="' . $val->startyear . '" type="text" placeholder="Year" class="form-control input-md number datepickeryear" required="">'
                          . '	</div>'
                          . '</td>'



                          /*
						. '<td class="col-md-2">'
						. '<input id="52_jam' .$i.'" name="52_jam[]" value="'.$val->hour.'" type="text" placeholder="" class="form-control input-md "  required="">'
						. '</td>'
						. '<td class="col-md-2">'
						. '<input id="52_lic' .$i.'" name="52_lic[]" value="" type="text" placeholder="" class="form-control input-md "  required="">'
						. '</td>'
						. '<td class="col-md-2">'
						. '<input id="52_url' .$i.'" name="52_url[]" value="" type="text" placeholder="" class="form-control input-md "  required="">'
						. '</td>'
						*/


                          . '<td class="col-md-2">'
                          . '<select id="52_tingkatseminar' . $i . '" name="52_tingkatseminar[]" style="height: 34px;" required="">'
                          . '<option value="">--Choose--</option>';
                        if (isset($bp_52_q)) {
                          foreach ($bp_52_q as $val2) {
                            echo '<option value="' . $val2->value . '" ' . (isset($val->tingkat) ? ((strpos($val2->desc, $val->tingkat) !== false) ? 'selected="true"' : "") : "") . '>' . $val2->desc . '</option>'; //'.(isset($val->degree)?(($val->degree==$val2->value)?'selected="true"':""):"").'
                          }
                        }
                        echo '</select>'
                          . '</td>'

                          . '<td class="col-md-2" style="display:none;">'
                          . '<select id="52_jumlah' . $i . '" name="52_jumlah[]" style="height: 34px;" >'
                          . '<option value="">--Choose--</option>';
                        if (isset($bp_52_p)) {
                          foreach ($bp_52_p as $val2) {
                            echo '<option value="' . $val2->value . '" ' . (isset($val->position) ? ((trim($val->position) == trim($val2->desc)) ? 'selected="true"' : "") : "") . '>' . $val2->desc . '</option>'; //'.(isset($val->degree)?(($val->degree==$val2->value)?'selected="true"':""):"").'
                          }
                        }
                        echo '</select>'
                          . '</td>'

                          . '<td class="col-md-2">'
                          . '<textarea id="52_uraian' . $i . '" name="52_uraian[]" class="form-control input-md " style="width:200px;" rows="8">' . $val->description . '</textarea>'
                          . '</td>'

                          . '<td class="col-md-2">'
                          . '<select id="52_tingkat' . $i . '" name="52_tingkat[]" style="height: 34px;" required="">'
                          . '<option value="">--Choose--</option>';
                        if (isset($bp_52_r)) {
                          foreach ($bp_52_r as $val2) {
                            echo '<option value="' . $val2->value . '" ' . (isset($val->tingkatmedia) ? ((trim($val->tingkatmedia) == trim($val2->desc)) ? 'selected="true"' : "") : "") . '>' . $val2->desc . '</option>'; //'.(isset($val->degree)?(($val->degree==$val2->value)?'selected="true"':""):"").'
                          }
                        }
                        echo '</select>'
                          . '</td>'


                          . '<td class="col-md-8">'
                          . '	<div class="form-group">'
                          . '		<div id="52_avatarattpublication2' . $i . '">'
                          . '		<input type="hidden" name="52_publication2_image_url[]" value="' . ((isset($val->attachment)) ? $val->attachment : '') . '" style="display: inline-block;">'
                          . '		' . ((isset($val->attachment)) ? "<a href='" . base_url() . "/assets/uploads/" . $val->attachment . "' target='_blank' class='ava_discus'>" . $val->attachment . "</a>" : '') . ''
                          . '		</div>'
                          . '		<div id="52_errUploadattpublication2' . $i . '"></div>'
                          . '		<input type="file" name="52_attpublication2[]" id="52_attpublication2' . $i . '" class="form-control input-md" onchange="upload_publication2(\'attpublication2' . $i . '\')">'
                          . '	</div>'
                          . '</td>'

                          . '<td class="col-md-2">'

                          //. '<textarea id="52_uraian' .$i. '" name="52_uraian[]" class="form-control input-md " style="width:200px;" rows="8"></textarea>'


                          . '<select id="52_komp' . $i . '" name="52_komp[' . $i . '][]" class="input-md" style="height: 200px;" required="" multiple>'
                          . '<option value="">--Choose--</option>';


                        if (isset($m_act_51)) {
                          $temp = true;
                          foreach ($m_act_51 as $val) {
                            if (strlen($val->value) < 8) {
                              if (!$temp)
                                echo '</optgroup>';
                              echo  '<optgroup label="' . $val->value . ' - ' . $val->title . '">';
                              $temp = true;
                            } else {
                              $temp = false;
                              echo  '<option value="' . $val->value . '" >' . $val->value . ' - ' . $val->title . '</option>';
                            }
                          }
                        }


                        echo '</select>'

                          . '</td>'



                          /*
								. '<td class="col-md-4">'
				
								. '<table width="100%" id="'.$tableID.'komp_' . $i  . '" class="table">'
								. '</table>'
								. '<div class="col-md-12" style="padding-bottom:20px;">'
								. '<button type="button" class="btn btn-primary" onclick="addKomp(\''.$tableID.'komp_' . $i  . '\')">+ Tambah Kompetensi</button>'
								. '</div>'
								
								. '</td>'
								*/

                          . '<td class="td-action">'
                          . '<button type="button" class="btn btn-danger btn-xs ' . $tableID . '-item-remove-button" data-id="' . $i  . '">'
                          . '<i class="fa fa-trash-o fa-fw"></i>X'
                          . '</button>'
                          . '</td>'
                          . '</tr>
						';

                        $i++;
                      }
                    }
                    ?>
                  </tbody>
                </table>
              </div>
              <div class="col-md-12" style="padding-bottom:20px;">
                <button type="button" class="btn btn-primary" onclick="add52('lok')">+ Tambah Baru</button>
              </div>
              <p>Apabila perlu tuliskan pada lembar tambahan</p>
              <p>*) KOMPETENSI: Isi dengan nomor Uraian Kegiatan Kompetensi yang Anda anggap persyaratannya telah terpenuhi dengan aktifitas Anda di sini</p>
              <div class="col-md-12" style="padding-bottom:20px;text-align:center;">
                <button type="button" name="savefaip" value="1" class="btn btn-next savefaip">Save & Continue</button>
              </div>
            </section>

            <h3>V.3</h3>
            <section>
              <label>V.3 Seminar/Lokakarya Keinsinyuran Yang Diikuti <span class="comp">(W2)</span></label><br />
              <div style="width:2600px;">
                <table width="100%" id="tsem" class="table">
                  <thead>
                    <tr>
                      <th width="5%"><label>No</label></th>
                      <th width="10%"><label>NAMA SEMINAR / LOKAKARYA</label></th>
                      <th width="10%"><label>NAMA PENYELENGGARA</label></th>
                      <th width="5%"><label>KOTA/KABUPATEN</label></th>
                      <th width="5%"><label>PROVINSI</label></th>
                      <th width="5%"><label>NEGARA</label></th>
                      <th width="5%"><label>Kapan Seminar/Lokakarya diselenggarakan?</label></th>

                      <th width="5%"><label>Seminar/Lokakarya tingkat</label></th>
                      <th width="5%" style="display:none;"><label>Berapa Seminar/Lokakarya yang sudah Anda ikuti?</label></th>

                      <th width="20%"><label>Uraian singkat materi Seminar/Lokakarya</label></th>

                      <th width="10%"><label>Tingkat kesulitan dan manfaatnya materi Seminar/Lokakarya</label></th>

                      <th width="5%"><label>Dokumen Pendukung<br /> <span class="red">(Max. 700KB, image atau PDF)</span></label></th>
                      <th width="10%"><label>KLAIM KOMPETENSI </label></th>
                      <!--<th width="10%"><label>KOMPETENSI *)</label></th>-->
                      <th width="5%"><label></label></th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    $bp_53_p = array();
                    $bp_53_q = array();
                    $bp_53_r = array();
                    if (isset($bp_53[0])) {
                      foreach ($bp_53 as $val) {
                        if ($val->faip_type == "p")
                          $bp_53_p[] = $val;
                        else if ($val->faip_type == "q")
                          $bp_53_q[] = $val;
                        else if ($val->faip_type == "r")
                          $bp_53_r[] = $val;
                      }
                    }
                    if (count($user_publication3) > 0) {
                      $i = 1;
                      foreach ($user_publication3 as $val) {

                        $years = count($user_publication3);

                        $data['bp_51'] = $this->main_mod->msrwhere('m_bakuan_penilaian', array('faip_num' => '51', 'faip_type' => 'p'), 'id', 'desc')->result();

                        foreach ($data['bp_51'] as $valbp) {
                          $condition = substr($valbp->formula, 0, 2);
                          if ($condition == "<=") {
                            if ($years <= substr($valbp->formula, 2, 5)) $tempidx = $valbp->value;
                          } else if ($condition == "<") {
                            if ($years < substr($valbp->formula, 2, 5)) $tempidx = $valbp->value;
                          } else if ($condition == ">") {
                            if ($years > substr($valbp->formula, 2, 5)) $tempidx = $valbp->value;
                          } else if ($condition == ">=") {
                            if ($years >= substr($valbp->formula, 2, 5)) $tempidx = $valbp->value;
                          } else if ($condition == "=") {
                            if ($years == substr($valbp->formula, 2, 5)) $tempidx = $valbp->value;
                          }
                          //echo substr($valbp->formula,2,5).'*<br />';
                        }
                        $p = $tempidx;
                        $q = 0;
                        $r = 0;
                        foreach ($bp_51_q as $val2) {
                          if (trim($val->tingkatmedia) == trim($val2->desc))
                            $q = $val2->value;
                        }

                        foreach ($bp_51_r as $val2) {
                          if (trim($val->tingkat) == trim($val2->desc))
                            $r = $val2->value;
                        }


                        $t = $p * $q * $r;
                        $tableID = 'sem';

                        echo '
						<tr class=" ' . $tableID . '-item" data-id="' . $i . '" >'
                          . '<td class="">'
                          . $i
                          . '</td>'


                          . '<td class="col-md-2"><input id="53_t' . $i . '" name="53_t[]" value="' . $t . '" type="hidden">'
                          . '<input id="53_nama' . $i . '" name="53_nama[]" value="' . $val->topic . '" type="text" placeholder="" class="form-control input-md " style="width:200px;" required="">'
                          . '</td>'
                          . '<td class="col-md-2">'
                          . '<input id="53_penyelenggara' . $i . '" name="53_penyelenggara[]" value="' . $val->media . '" type="text" placeholder="" class="form-control input-md "  style="width:200px;" required="">'
                          . '</td>'
                          . '<td class="col-md-2">'
                          . '<input id="53_location' . $i . '" name="53_location[]" value="' . $val->location . '" type="text" placeholder="" class="form-control input-md " style="width:200px;" required="">'
                          . '</td>'
                          . '<td class="col-md-2">'
                          . '<input id="53_provinsi' . $i . '" name="53_provinsi[]" value="' . $val->provinsi . '" type="text" placeholder="" class="form-control input-md " style="width:200px;" required="">'
                          . '</td>'
                          . '<td class="col-md-2">'
                          . '<input id="53_negara' . $i . '" name="53_negara[]" value="' . $val->negara . '" type="text" placeholder="" class="form-control input-md " style="width:200px;" required="">'
                          . '</td>'
                          /*
						. '<td class="col-md-2">'
								. '<select id="53_periode' .$i.'" name="53_periode[]" style="height: 34px;" required="">'
								. '<option value="">--Choose--</option>';
									if(isset($bp_53_p)){
										foreach($bp_53_p as $val2){
										
											$birthdate_ts=strtotime("$val->startyear-$val->startmonth-1");
											$birthdate_ts2=strtotime("$val->endyear-$val->endmonth-1");
											$diff = abs($birthdate_ts2 - $birthdate_ts);
											$tempid="";
											$years = floor($diff / (365*60*60*24));
											if($years<4)
												$tempid='1';
											else if($years<8)
												$tempid='2';
											else if($years<=10)
												$tempid='3';
											else if($years>10)
												$tempid='4';
											//echo $tempid;
											//$months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));
											//$days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
											//printf("%d years, %d months, %d days\n", $years, $months, $days);
										
											echo '<option value="'.$val2->value.'" '.(isset($tempid)?((trim($tempid)==trim($val2->value))?'selected="true"':""):"").'>'.$val2->desc.'</option>';//'.(isset($val->degree)?(($val->degree==$val2->value)?'selected="true"':""):"").'
										}
									}
								echo '</select>'									
						. '</td>'
						*/


                          . '<td class="col-md-2">'
                          . '	<div class="">'
                          . '	<select id="53_startdate' . $i . '" name="53_startdate[]" class="form-control input-md" required="">'
                          . '		<option value="">---</option>'
                          . '		<option value="1" ' . (isset($val->startmonth) ? (($val->startmonth == "1") ? 'selected="true"' : "") : "") . ' >Januari</option>'
                          . '		<option value="2" ' . (isset($val->startmonth) ? (($val->startmonth == "2") ? 'selected="true"' : "") : "") . '>Pebruari</option>'
                          . '		<option value="3" ' . (isset($val->startmonth) ? (($val->startmonth == "3") ? 'selected="true"' : "") : "") . '>Maret</option>'
                          . '		<option value="4" ' . (isset($val->startmonth) ? (($val->startmonth == "4") ? 'selected="true"' : "") : "") . '>April</option>'
                          . '		<option value="5" ' . (isset($val->startmonth) ? (($val->startmonth == "5") ? 'selected="true"' : "") : "") . '>Mei</option>'
                          . '		<option value="6" ' . (isset($val->startmonth) ? (($val->startmonth == "6") ? 'selected="true"' : "") : "") . '>Juni</option>'
                          . '		<option value="7" ' . (isset($val->startmonth) ? (($val->startmonth == "7") ? 'selected="true"' : "") : "") . '>Juli</option>'
                          . '		<option value="8" ' . (isset($val->startmonth) ? (($val->startmonth == "8") ? 'selected="true"' : "") : "") . '>Agustus</option>'
                          . '		<option value="9" ' . (isset($val->startmonth) ? (($val->startmonth == "9") ? 'selected="true"' : "") : "") . '>September</option>'
                          . '		<option value="10" ' . (isset($val->startmonth) ? (($val->startmonth == "10") ? 'selected="true"' : "") : "") . '>Oktober</option>'
                          . '		<option value="11" ' . (isset($val->startmonth) ? (($val->startmonth == "11") ? 'selected="true"' : "") : "") . '>Nopember</option>'
                          . '		<option value="12" ' . (isset($val->startmonth) ? (($val->startmonth == "12") ? 'selected="true"' : "") : "") . '>Desember</option>'
                          . '	</select>'
                          . '	<input id="53_startyear' . $i . '" name="53_startyear[]" value="' . $val->startyear . '" type="text" placeholder="Year" class="form-control input-md number datepickeryear" required="">'
                          . '	</div>'
                          . '</td>'



                          /*
						. '<td class="col-md-2">'
						. '<input id="53_jam' .$i.'" name="53_jam[]" value="'.$val->hour.'" type="text" placeholder="" class="form-control input-md "  required="">'
						. '</td>'
						. '<td class="col-md-2">'
						. '<input id="53_lic' .$i.'" name="53_lic[]" value="" type="text" placeholder="" class="form-control input-md "  required="">'
						. '</td>'
						. '<td class="col-md-2">'
						. '<input id="53_url' .$i.'" name="53_url[]" value="" type="text" placeholder="" class="form-control input-md "  required="">'
						. '</td>'
						*/


                          . '<td class="col-md-2">'
                          . '<select id="53_tingkatseminar' . $i . '" name="53_tingkatseminar[]" style="height: 34px;" required="">'
                          . '<option value="">--Choose--</option>';
                        if (isset($bp_53_q)) {
                          foreach ($bp_53_q as $val2) {
                            echo '<option value="' . $val2->value . '" ' . (isset($val->tingkat) ? ((strpos($val2->desc, $val->tingkat) !== false) ? 'selected="true"' : "") : "") . '>' . $val2->desc . '</option>'; //'.(isset($val->degree)?(($val->degree==$val2->value)?'selected="true"':""):"").'
                          }
                        }
                        echo '</select>'
                          . '</td>'

                          . '<td class="col-md-2" style="display:none;">'
                          . '<select id="53_jumlah' . $i . '" name="53_jumlah[]" style="height: 34px;">'
                          . '<option value="">--Choose--</option>';
                        if (isset($bp_53_p)) {
                          foreach ($bp_53_p as $val2) {
                            echo '<option value="' . $val2->value . '" ' . (isset($val->position) ? ((trim($val->position) == trim($val2->desc)) ? 'selected="true"' : "") : "") . '>' . $val2->desc . '</option>'; //'.(isset($val->degree)?(($val->degree==$val2->value)?'selected="true"':""):"").'
                          }
                        }
                        echo '</select>'
                          . '</td>'

                          . '<td class="col-md-2">'
                          . '<textarea id="53_uraian' . $i . '" name="53_uraian[]" class="form-control input-md " style="width:200px;" rows="8">' . $val->description . '</textarea>'
                          . '</td>'

                          . '<td class="col-md-2">'
                          . '<select id="53_tingkat' . $i . '" name="53_tingkat[]" style="height: 34px;" required="">'
                          . '<option value="">--Choose--</option>';
                        if (isset($bp_53_r)) {
                          foreach ($bp_53_r as $val2) {
                            echo '<option value="' . $val2->value . '" ' . (isset($val->tingkatmedia) ? ((trim($val->tingkatmedia) == trim($val2->desc)) ? 'selected="true"' : "") : "") . '>' . $val2->desc . '</option>'; //'.(isset($val->degree)?(($val->degree==$val2->value)?'selected="true"':""):"").'
                          }
                        }
                        echo '</select>'
                          . '</td>'

                          . '<td class="col-md-8">'
                          . '	<div class="form-group">'
                          . '		<div id="53_avatarattpublication3' . $i . '">'
                          . '		<input type="hidden" name="53_publication3_image_url[]" value="' . ((isset($val->attachment)) ? $val->attachment : '') . '" style="display: inline-block;">'
                          . '		' . ((isset($val->attachment)) ? "<a href='" . base_url() . "/assets/uploads/" . $val->attachment . "' target='_blank' class='ava_discus'>" . $val->attachment . "</a>" : '') . ''
                          . '		</div>'
                          . '		<div id="53_errUploadattpublication3' . $i . '"></div>'
                          . '		<input type="file" name="53_attpublication3[]" id="53_attpublication3' . $i . '" class="form-control input-md" onchange="upload_publication3(\'attpublication3' . $i . '\')">'
                          . '	</div>'
                          . '</td>'


                          . '<td class="col-md-2">'

                          //. '<textarea id="53_uraian' .$i. '" name="53_uraian[]" class="form-control input-md " style="width:200px;" rows="8"></textarea>'


                          . '<select id="53_komp' . $i . '" name="53_komp[' . $i . '][]" class="input-md" style="height: 200px;" required="" multiple>'
                          . '<option value="">--Choose--</option>';


                        if (isset($m_act_53)) {
                          $temp = true;
                          foreach ($m_act_53 as $val) {
                            if (strlen($val->value) < 8) {
                              if (!$temp)
                                echo '</optgroup>';
                              echo  '<optgroup label="' . $val->value . ' - ' . $val->title . '">';
                              $temp = true;
                            } else {
                              $temp = false;
                              echo  '<option value="' . $val->value . '" >' . $val->value . ' - ' . $val->title . '</option>';
                            }
                          }
                        }


                        echo '</select>'

                          . '</td>'



                          /*
								. '<td class="col-md-4">'
				
								. '<table width="100%" id="'.$tableID.'komp_' . $i  . '" class="table">'
								. '</table>'
								. '<div class="col-md-12" style="padding-bottom:20px;">'
								. '<button type="button" class="btn btn-primary" onclick="addKomp(\''.$tableID.'komp_' . $i  . '\')">+ Tambah Kompetensi</button>'
								. '</div>'
								
								. '</td>'
								*/

                          . '<td class="td-action">'
                          . '<button type="button" class="btn btn-danger btn-xs ' . $tableID . '-item-remove-button" data-id="' . $i  . '">'
                          . '<i class="fa fa-trash-o fa-fw"></i>X'
                          . '</button>'
                          . '</td>'
                          . '</tr>
						';

                        $i++;
                      }
                    }
                    ?>
                  </tbody>
                </table>
              </div>
              <div class="col-md-12" style="padding-bottom:20px;">
                <button type="button" class="btn btn-primary" onclick="add53('sem')">+ Tambah Baru</button>
              </div>
              <p>Apabila perlu tuliskan pada lembar tambahan</p>
              <p>*) KOMPETENSI: Isi dengan nomor Uraian Kegiatan Kompetensi yang Anda anggap persyaratannya telah terpenuhi dengan aktifitas Anda di sini</p>
              <div class="col-md-12" style="padding-bottom:20px;text-align:center;">
                <button type="button" name="savefaip" value="1" class="btn btn-next savefaip">Save & Continue</button>
              </div>
            </section>
            <h3>V.4</h3>
            <section>
              <label>V.4 Karya Temuan/Inovasi/Paten dan Implementasi Teknologi Baru <span class="comp">(P6)</span></label><br />
              <div style="width:2600px;">
                <table width="100%" id="tino" class="table">
                  <thead>
                    <tr>
                      <th width="5%"><label>No</label></th>
                      <th width="20%"><label>Judul / Nama Karya Temuan/Inovasi/Paten dan Implementasi Teknologi Baru</label></th>
                      <th width="10%"><label>BULAN-TAHUN</label></th>
                      <th width="10%"><label>MEDIA PUBLIKASI KARYA (KALAU ADA)</label></th>

                      <th width="5%"><label>Media Publikasi tingkat</label></th>
                      <th width="5%" style="display:none;"><label>Jumlah Temuan </label></th>

                      <th width="20%"><label>Uraian singkat Karya Temuan/Inovasi/Paten dan Implementasi Teknologi Baru</label></th>

                      <th width="10%"><label>Tingkat kesulitan dan manfaatnya Karya Temuan/Inovasi/Paten dan Implementasi Teknologi Baru</label></th>

                      <th width="5%"><label>Dokumen Pendukung<br /> <span class="red">(Max. 700KB, image atau PDF)</span></label></th>
                      <th width="10%"><label>KLAIM KOMPETENSI </label></th>
                      <!--<th width="10%"><label>KOMPETENSI *)</label></th>-->
                      <th width="5%"><label></label></th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    $bp_54_p = array();
                    $bp_54_q = array();
                    $bp_54_r = array();
                    if (isset($bp_54[0])) {
                      foreach ($bp_54 as $val) {
                        if ($val->faip_type == "p")
                          $bp_54_p[] = $val;
                        else if ($val->faip_type == "q")
                          $bp_54_q[] = $val;
                        else if ($val->faip_type == "r")
                          $bp_54_r[] = $val;
                      }
                    }
                    if (count($user_publication4) > 0) {
                      $i = 1;
                      foreach ($user_publication4 as $val) {

                        $years = count($user_publication4);

                        $data['bp_54'] = $this->main_mod->msrwhere('m_bakuan_penilaian', array('faip_num' => '54', 'faip_type' => 'p'), 'id', 'desc')->result();

                        foreach ($data['bp_54'] as $valbp) {
                          $condition = substr($valbp->formula, 0, 2);
                          if ($condition == "<=") {
                            if ($years <= substr($valbp->formula, 2, 5)) $tempidx = $valbp->value;
                          } else if ($condition == "<") {
                            if ($years < substr($valbp->formula, 2, 5)) $tempidx = $valbp->value;
                          } else if ($condition == ">") {
                            if ($years > substr($valbp->formula, 2, 5)) $tempidx = $valbp->value;
                          } else if ($condition == ">=") {
                            if ($years >= substr($valbp->formula, 2, 5)) $tempidx = $valbp->value;
                          } else if ($condition == "=") {
                            if ($years == substr($valbp->formula, 2, 5)) $tempidx = $valbp->value;
                          }
                          //echo substr($valbp->formula,2,5).'*<br />';
                        }
                        $p = $tempidx;
                        $q = 0;
                        $r = 0;
                        foreach ($bp_54_q as $val2) {
                          if (trim($val->tingkatmedia) == trim($val2->desc))
                            $q = $val2->value;
                        }

                        foreach ($bp_54_r as $val2) {
                          if (trim($val->tingkat) == trim($val2->desc))
                            $r = $val2->value;
                        }


                        $t = $p * $q * $r;
                        $tableID = 'ino';

                        echo '
						<tr class=" ' . $tableID . '-item" data-id="' . $i . '" >'
                          . '<td class="">'
                          . $i
                          . '</td>'


                          . '<td class="col-md-2"><input id="54_t' . $i . '" name="54_t[]" value="' . $t . '" type="hidden">'
                          . '<input id="54_nama' . $i . '" name="54_nama[]" value="' . $val->topic . '" type="text" placeholder="" class="form-control input-md " style="width:200px;" required="">'
                          . '</td>'

                          . '<td class="col-md-2">'
                          . '	<div class="">'
                          . '	<select id="54_startdate' . $i . '" name="54_startdate[]" class="form-control input-md" required="">'
                          . '		<option value="">---</option>'
                          . '		<option value="1" ' . (isset($val->startmonth) ? (($val->startmonth == "1") ? 'selected="true"' : "") : "") . ' >Januari</option>'
                          . '		<option value="2" ' . (isset($val->startmonth) ? (($val->startmonth == "2") ? 'selected="true"' : "") : "") . '>Pebruari</option>'
                          . '		<option value="3" ' . (isset($val->startmonth) ? (($val->startmonth == "3") ? 'selected="true"' : "") : "") . '>Maret</option>'
                          . '		<option value="4" ' . (isset($val->startmonth) ? (($val->startmonth == "4") ? 'selected="true"' : "") : "") . '>April</option>'
                          . '		<option value="5" ' . (isset($val->startmonth) ? (($val->startmonth == "5") ? 'selected="true"' : "") : "") . '>Mei</option>'
                          . '		<option value="6" ' . (isset($val->startmonth) ? (($val->startmonth == "6") ? 'selected="true"' : "") : "") . '>Juni</option>'
                          . '		<option value="7" ' . (isset($val->startmonth) ? (($val->startmonth == "7") ? 'selected="true"' : "") : "") . '>Juli</option>'
                          . '		<option value="8" ' . (isset($val->startmonth) ? (($val->startmonth == "8") ? 'selected="true"' : "") : "") . '>Agustus</option>'
                          . '		<option value="9" ' . (isset($val->startmonth) ? (($val->startmonth == "9") ? 'selected="true"' : "") : "") . '>September</option>'
                          . '		<option value="10" ' . (isset($val->startmonth) ? (($val->startmonth == "10") ? 'selected="true"' : "") : "") . '>Oktober</option>'
                          . '		<option value="11" ' . (isset($val->startmonth) ? (($val->startmonth == "11") ? 'selected="true"' : "") : "") . '>Nopember</option>'
                          . '		<option value="12" ' . (isset($val->startmonth) ? (($val->startmonth == "12") ? 'selected="true"' : "") : "") . '>Desember</option>'
                          . '	</select>'
                          . '	<input id="54_startyear' . $i . '" name="54_startyear[]" value="' . $val->startyear . '" type="text" placeholder="Year" class="form-control input-md number datepickeryear" required="">'
                          . '	</div>'
                          . '</td>'

                          . '<td class="col-md-2">'
                          . '<input id="54_media_publikasi' . $i . '" name="54_media_publikasi[]" value="' . $val->media . '" type="text" placeholder="" class="form-control input-md "  required="">'
                          . '</td>'

                          /*
						. '<td class="col-md-2">'
								. '<select id="54_periode' .$i.'" name="54_periode[]" style="height: 34px;" required="">'
								. '<option value="">--Choose--</option>';
									if(isset($bp_54_p)){
										foreach($bp_54_p as $val2){
										
											$birthdate_ts=strtotime("$val->startyear-$val->startmonth-1");
											$birthdate_ts2=strtotime("$val->endyear-$val->endmonth-1");
											$diff = abs($birthdate_ts2 - $birthdate_ts);
											$tempid="";
											$years = floor($diff / (365*60*60*24));
											if($years<4)
												$tempid='1';
											else if($years<8)
												$tempid='2';
											else if($years<=10)
												$tempid='3';
											else if($years>10)
												$tempid='4';
											//echo $tempid;
											//$months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));
											//$days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
											//printf("%d years, %d months, %d days\n", $years, $months, $days);
										
											echo '<option value="'.$val2->value.'" '.(isset($tempid)?((trim($tempid)==trim($val2->value))?'selected="true"':""):"").'>'.$val2->desc.'</option>';//'.(isset($val->degree)?(($val->degree==$val2->value)?'selected="true"':""):"").'
										}
									}
								echo '</select>'									
						. '</td>'
						*/






                          /*
						. '<td class="col-md-2">'
						. '<input id="54_jam' .$i.'" name="54_jam[]" value="'.$val->hour.'" type="text" placeholder="" class="form-control input-md "  required="">'
						. '</td>'
						. '<td class="col-md-2">'
						. '<input id="54_lic' .$i.'" name="54_lic[]" value="" type="text" placeholder="" class="form-control input-md "  required="">'
						. '</td>'
						. '<td class="col-md-2">'
						. '<input id="54_url' .$i.'" name="54_url[]" value="" type="text" placeholder="" class="form-control input-md "  required="">'
						. '</td>'
						*/


                          . '<td class="col-md-2">'
                          . '<select id="54_tingkatseminar' . $i . '" name="54_tingkatseminar[]" style="height: 34px;" required="">'
                          . '<option value="">--Choose--</option>';
                        if (isset($bp_54_q)) {
                          foreach ($bp_54_q as $val2) {
                            echo '<option value="' . $val2->value . '" ' . (isset($val->tingkat) ? ((strpos($val2->desc, $val->tingkat) !== false) ? 'selected="true"' : "") : "") . '>' . $val2->desc . '</option>'; //'.(isset($val->degree)?(($val->degree==$val2->value)?'selected="true"':""):"").'
                          }
                        }
                        echo '</select>'
                          . '</td>'

                          . '<td class="col-md-2" style="display:none;">'
                          . '<select id="54_jumlah' . $i . '" name="54_jumlah[]" style="height: 34px;">'
                          . '<option value="">--Choose--</option>';
                        if (isset($bp_54_p)) {
                          foreach ($bp_54_p as $val2) {
                            echo '<option value="' . $val2->value . '" ' . (isset($val->position) ? ((trim($val->position) == trim($val2->desc)) ? 'selected="true"' : "") : "") . '>' . $val2->desc . '</option>'; //'.(isset($val->degree)?(($val->degree==$val2->value)?'selected="true"':""):"").'
                          }
                        }
                        echo '</select>'
                          . '</td>'

                          . '<td class="col-md-2">'
                          . '<textarea id="54_uraian' . $i . '" name="54_uraian[]" class="form-control input-md " style="width:200px;" rows="8">' . $val->description . '</textarea>'
                          . '</td>'

                          . '<td class="col-md-2">'
                          . '<select id="54_tingkat' . $i . '" name="54_tingkat[]" style="height: 34px;" required="">'
                          . '<option value="">--Choose--</option>';
                        if (isset($bp_54_r)) {
                          foreach ($bp_54_r as $val2) {
                            echo '<option value="' . $val2->value . '" ' . (isset($val->tingkatmedia) ? ((trim($val->tingkatmedia) == trim($val2->desc)) ? 'selected="true"' : "") : "") . '>' . $val2->desc . '</option>'; //'.(isset($val->degree)?(($val->degree==$val2->value)?'selected="true"':""):"").'
                          }
                        }
                        echo '</select>'
                          . '</td>'


                          . '<td class="col-md-8">'
                          . '	<div class="form-group">'
                          . '		<div id="54_avatarattpublication4' . $i . '">'
                          . '		<input type="hidden" name="54_publication4_image_url[]" value="' . ((isset($val->attachment)) ? $val->attachment : '') . '" style="display: inline-block;">'
                          . '		' . ((isset($val->attachment)) ? "<a href='" . base_url() . "/assets/uploads/" . $val->attachment . "' target='_blank' class='ava_discus'>" . $val->attachment . "</a>" : '') . ''
                          . '		</div>'
                          . '		<div id="54_errUploadattpublication4' . $i . '"></div>'
                          . '		<input type="file" name="54_attpublication4[]" id="54_attpublication4' . $i . '" class="form-control input-md" onchange="upload_publication4(\'attpublication4' . $i . '\')">'
                          . '	</div>'
                          . '</td>'

                          . '<td class="col-md-2">'

                          //. '<textarea id="54_uraian' .$i. '" name="54_uraian[]" class="form-control input-md " style="width:200px;" rows="8"></textarea>'


                          . '<select id="54_komp' . $i . '" name="54_komp[' . $i . '][]" class="input-md" style="height: 200px;" required="" multiple>'
                          . '<option value="">--Choose--</option>';


                        if (isset($m_act_54)) {
                          $temp = true;
                          foreach ($m_act_54 as $val) {
                            if (strlen($val->value) < 8) {
                              if (!$temp)
                                echo '</optgroup>';
                              echo  '<optgroup label="' . $val->value . ' - ' . $val->title . '">';
                              $temp = true;
                            } else {
                              $temp = false;
                              echo  '<option value="' . $val->value . '" >' . $val->value . ' - ' . $val->title . '</option>';
                            }
                          }
                        }


                        echo '</select>'

                          . '</td>'



                          /*
								. '<td class="col-md-4">'
				
								. '<table width="100%" id="'.$tableID.'komp_' . $i  . '" class="table">'
								. '</table>'
								. '<div class="col-md-12" style="padding-bottom:20px;">'
								. '<button type="button" class="btn btn-primary" onclick="addKomp(\''.$tableID.'komp_' . $i  . '\')">+ Tambah Kompetensi</button>'
								. '</div>'
								
								. '</td>'
								*/

                          . '<td class="td-action">'
                          . '<button type="button" class="btn btn-danger btn-xs ' . $tableID . '-item-remove-button" data-id="' . $i  . '">'
                          . '<i class="fa fa-trash-o fa-fw"></i>X'
                          . '</button>'
                          . '</td>'
                          . '</tr>
						';

                        $i++;
                      }
                    }
                    ?>
                  </tbody>
                </table>
              </div>
              <div class="col-md-12" style="padding-bottom:20px;">
                <button type="button" class="btn btn-primary" onclick="add54('ino')">+ Tambah Baru</button>
              </div>
              <p>*) KOMPETENSI: Isi dengan nomor Uraian Kegiatan Kompetensi yang Anda anggap persyaratannya telah terpenuhi dengan aktifitas Anda di sini</p>

              <div class="col-md-12" style="padding-bottom:20px;text-align:center;">
                <button type="button" name="savefaip" value="1" class="btn btn-next savefaip">Save & Continue</button>
              </div>
            </section>
            <h3>VI</h3>
            <section>
              <label>VI. BAHASA YANG DIKUASAI <span class="comp">(W4)</span></label><br />
              <table width="100%" id="tbah" class="table">
                <thead>
                  <tr>
                    <th width="5%"><label>No</label></th>
                    <th width="20%"><label>NAMA BAHASA</label></th>
                    <th width="20%"><label>JENIS BAHASA</label></th>
                    <th width="10%"><label>KEMAMPUAN VERBAL AKTIF/PASIF</label></th>
                    <th width="10%"><label>JENIS TULISAN YANG MAMPU DISUSUN </label></th>
                    <th width="10%" style="display:none;"><label>Jumlah Bahasa yang dikuasai </label></th>

                    <th width="10%" style="display:none;"><label>NILAI TOEFL ATAU YANG SEJENISNYA</label></th>

                    <th width="5%"><label>Dokumen Pendukung<br /> <span class="red">(Max. 700KB, image atau PDF)</span></label></th>
                    <th width="10%"><label>KLAIM KOMPETENSI </label></th>
                    <!--<th width="10%"><label>KOMPETENSI *)</label></th>-->
                    <th width="5%"><label></label></th>
                  </tr>
                </thead>
                <tbody>

                  <?php

                  $bp_6_p = array();
                  $bp_6_q = array();
                  $bp_6_r = array();
                  if (isset($bp_6[0])) {
                    foreach ($bp_6 as $val) {
                      if ($val->faip_type == "p")
                        $bp_6_p[] = $val;
                      else if ($val->faip_type == "q")
                        $bp_6_q[] = $val;
                      else if ($val->faip_type == "r")
                        $bp_6_r[] = $val;
                    }
                  }


                  if (count($user_skill) > 0) {
                    $i = 1;
                    foreach ($user_skill as $val) {

                      $years = count($user_skill);

                      $data['bp_6'] = $this->main_mod->msrwhere('m_bakuan_penilaian', array('faip_num' => '6', 'faip_type' => 'p'), 'id', 'desc')->result();

                      foreach ($data['bp_6'] as $valbp) {
                        $condition = substr($valbp->formula, 0, 2);
                        if ($condition == "<=") {
                          if ($years <= substr($valbp->formula, 2, 5)) $tempidx = $valbp->value;
                        } else if ($condition == "<") {
                          if ($years < substr($valbp->formula, 2, 5)) $tempidx = $valbp->value;
                        } else if ($condition == ">") {
                          if ($years > substr($valbp->formula, 2, 5)) $tempidx = $valbp->value;
                        } else if ($condition == ">=") {
                          if ($years >= substr($valbp->formula, 2, 5)) $tempidx = $valbp->value;
                        } else if ($condition == "=") {
                          if ($years == substr($valbp->formula, 2, 5)) $tempidx = $valbp->value;
                        }
                        //echo substr($valbp->formula,2,5).'*<br />';
                      }
                      $p = $tempidx;
                      $q = 0;
                      $r = 0;
                      foreach ($bp_6_q as $val2) {
                        if (trim($val->jenisbahasa) == trim($val2->desc))
                          $q = $val2->value;
                      }

                      foreach ($bp_6_r as $val2) {
                        if (trim($val->proficiency) == trim($val2->desc))
                          $r = $val2->value;
                      }


                      $t = $p * $q * $r;
                      $tableID = 'bah';

                      echo '
							<tr class=" ' . $tableID . '-item" data-id="' . $i . '" >'
                        . '<td class="">'
                        . $i
                        . '</td>'


                        . '<td class="col-md-2"><input id="6_t' . $i . '" name="6_t[]" value="' . $t . '" type="hidden">'
                        . '<input id="6_nama' . $i . '" name="6_nama[]" value="' . $val->name . '" type="text" placeholder="" class="form-control input-md " style="width:200px;" required="">'
                        . '</td>'

                        . '<td class="col-md-2">'
                        . '<select id="6_jenisbahasa' . $i . '" name="6_jenisbahasa[]" style="height: 34px;" required="">'
                        . '<option value="">--Choose--</option>';
                      if (isset($bp_6_q)) {
                        foreach ($bp_6_q as $val2) {
                          echo '<option value="' . $val2->value . '" ' . (isset($val->jenisbahasa) ? ((strpos($val2->desc, $val->jenisbahasa) !== false) ? 'selected="true"' : "") : "") . '>' . $val2->desc . '</option>'; //'.(isset($val->degree)?(($val->degree==$val2->value)?'selected="true"':""):"").'
                        }
                      }
                      echo '</select>'
                        . '</td>'

                        . '<td class="col-md-2">'
                        . '<select id="6_verbal' . $i . '" name="6_verbal[]" style="height: 34px;" required="">'
                        . '<option value="">--Choose--</option>';
                      if (isset($bp_6_r)) {
                        foreach ($bp_6_r as $val2) {
                          echo '<option value="' . $val2->value . '" ' . (isset($val->proficiency) ? ((strpos($val2->desc, $val->proficiency) !== false) ? 'selected="true"' : "") : "") . '>' . $val2->desc . '</option>'; //'.(isset($val->degree)?(($val->degree==$val2->value)?'selected="true"':""):"").'
                        }
                      }
                      echo '</select>'
                        . '</td>'


                        . '<td class="col-md-2">'
                        . '	<select id="6_jenistulisan' . $i . '" name="6_jenistulisan[]" required="" style="height: 34px;">'
                        . '		<option value="">---</option>'
                        . '		<option value="Makalah" ' . (isset($val->jenistulisan) ? ((strpos("Makalah", $val->jenistulisan) !== false) ? 'selected="true"' : "") : "") . '>Makalah</option>'
                        . '		<option value="Jurnal" ' . (isset($val->jenistulisan) ? ((strpos("Jurnal", $val->jenistulisan) !== false) ? 'selected="true"' : "") : "") . '>Jurnal</option>'
                        . '		<option value="Laporan" ' . (isset($val->jenistulisan) ? ((strpos("Laporan", $val->jenistulisan) !== false) ? 'selected="true"' : "") : "") . '>Laporan</option>'
                        . '	</select>'

                        . '</td>'

                        . '<td class="col-md-8">'
                        . '	<div class="form-group">'
                        . '		<div id="6_avatarattskill' . $i . '">'
                        . '		<input type="hidden" name="6_skill_image_url[]" value="' . ((isset($val->attachment)) ? $val->attachment : '') . '" style="display: inline-block;">'
                        . '		' . ((isset($val->attachment)) ? "<a href='" . base_url() . "/assets/uploads/" . $val->attachment . "' target='_blank' class='ava_discus'>" . $val->attachment . "</a>" : '') . ''
                        . '		</div>'
                        . '		<div id="6_errUploadattskill' . $i . '"></div>'
                        . '		<input type="file" name="6_attskill[]" id="6_attskill' . $i . '" class="form-control input-md" onchange="upload_skill(\'attskill' . $i . '\')">'
                        . '	</div>'
                        . '</td>'


                        . '<td class="col-md-2">'

                        //. '<textarea id="54_uraian' .$i. '" name="54_uraian[]" class="form-control input-md " style="width:200px;" rows="8"></textarea>'


                        . '<select id="6_komp' . $i . '" name="6_komp[' . $i . '][]" class="input-md" style="height: 200px;" required="" multiple>'
                        . '<option value="">--Choose--</option>';


                      if (isset($m_act_51)) {
                        $temp = true;
                        foreach ($m_act_51 as $val) {
                          if (strlen($val->value) < 8) {
                            if (!$temp)
                              echo '</optgroup>';
                            echo  '<optgroup label="' . $val->value . ' - ' . $val->title . '">';
                            $temp = true;
                          } else {
                            $temp = false;
                            echo  '<option value="' . $val->value . '" >' . $val->value . ' - ' . $val->title . '</option>';
                          }
                        }
                      }


                      echo '</select>'

                        . '</td>'



                        . '<td class="td-action">'
                        . '<button type="button" class="btn btn-danger btn-xs ' . $tableID . '-item-remove-button" data-id="' . $i  . '">'
                        . '<i class="fa fa-trash-o fa-fw"></i>X'
                        . '</button>'
                        . '</td>'
                        . '</tr>
							';

                      $i++;
                    }
                  }

                  ?>

                </tbody>
              </table>
              <div class="col-md-12" style="padding-bottom:20px;">
                <button type="button" class="btn btn-primary" onclick="add6('bah')">+ Tambah Baru</button>
              </div>
              <p>Apabila perlu tuliskan pada lembar tambahan</p>
              <p>*) KOMPETENSI: Isi dengan nomor Uraian Kegiatan Kompetensi yang Anda anggap persyaratannya telah terpenuhi dengan aktifitas Anda di sini</p>
              <div class="col-md-12" style="padding-bottom:20px;text-align:center;">
                <button type="button" name="savefaip" value="1" class="btn btn-next savefaip">Save & Continue</button>
              </div>
            </section>
            <h3>VII</h3>
            <section>
              <label>VII. PERNYATAAN </label><br />
              <p><input type="checkbox" name="pernyataan" onclick="setTime()" value="1" required="true"> Dengan ini saya menyatakan bahwa seluruh keterangan yang diunggah pada <b><span id="lbl_pernyataan">tanggal , jam</span></b> (menurut waktu SIMPONI) adalah benar. Bersama ini saya lampirkan data atau dokumen pendukung.

                <input type="hidden" id="wkt_pernyataan" name="wkt_pernyataan" />
              </p>
              <div class="col-md-12" style="padding-bottom:20px;text-align:center;">
                <button type="button" name="savefaip" value="1" class="btn btn-next savefaip">Save & Continue</button>
                <button type="submit" id="submitfaip" name="submitfaip" value="1" class="btn btn-next">Submit</button>
              </div>
            </section>
            <h3>Lampiran I</h3>
            <section>
              <div class="title">
                <h4>LEMBAR DOKUMENTASI PENGALAMAN KERJA</h4>
              </div><br />
              <table width="100%" id="tlam" class="table">
                <thead>
                  <tr>
                    <th width="5%"><label>No</label></th>
                    <th width="7%"><label>Aktifitas</label></th>
                    <th width="7%"><label>Nama</label></th>
                    <th width="7%"><label>Nama Proyek </label></th>
                    <th width="7%"><label>Jangka Waktu Proyek</label></th>
                    <th width="7%"><label>Nama Atasan/Pengawas/Supervisor</label></th>
                    <th width="20%"><label>Uraian Proyek (Termasuk penghargaan yang Anda terima, kalau ada)</label></th>
                    <th width="20%"><label>Uraian tugas yang Anda laksanakan </label></th>
                    <th width="10%"><label>Bagan organisasi yang menunjukkan posisi dan pertanggungjawaban Anda (Bila perlu pergunakan lembar kertas baru)</label></th>
                    <th width="5%"><label>Dokumen Pendukung<br /> <span class="red">(Max. 700KB, image atau PDF)</span></label></th>
                    <th width="5%"><label></label></th>
                  </tr>
                </thead>
                <tbody>
                </tbody>
              </table>
              <div class="col-md-12" style="padding-bottom:20px;">
                <button type="button" class="btn btn-primary" onclick="addlam('lam')">+ Tambah Baru</button>
              </div>

              <div class="col-md-12" style="padding-bottom:20px;text-align:center;">
                <button type="button" name="savefaip" value="1" class="btn btn-next savefaip">Save & Continue</button>
              </div>
            </section>
            <h3>Lampiran II</h3>
            <section>
              <div class="title">
                <h4>LEMBAR DOKUMENTASI SEMUA LAMPIRAN</h4>
              </div><br />
              <table width="100%" id="tlam2" class="table">
                <thead>
                  <tr>
                    <th width="5%"><label>No</label></th>
                    <th width="7%"><label>Lembar Kerja</label></th>
                    <th width="7%"><label>Nama/Judul</label></th>
                    <th width="5%"><label>Dokumen Pendukung<br /> <span class="red">(Max. 700KB, image atau PDF)</span></label></th>
                    <th width="5%"><label></label></th>
                  </tr>
                </thead>
                <tbody>
                </tbody>
              </table>
              <!--<div class="col-md-12" style="padding-bottom:20px;">
		<button type="button" class="btn btn-primary" onclick="addlam2('lam2')">+ Tambah Baru</button>
		</div>-->

              <div class="col-md-12" style="padding-bottom:20px;text-align:center;">
                <button type="button" name="savefaip" value="1" class="btn btn-next savefaip">Save & Continue</button>
              </div>
            </section>
            <h3>Rekap</h3>
            <section>
              <div class="title">
                <h4>FORMULIR PERMOHONAN UJI SERTIFIKASI INSINYUR PROFESIONAL</h4>
              </div>
              <label></label><br /><br />
              <table width="50%" id="" class="">
                <tr>
                  <td width="50%"><label>Nama Pemohon</label></td>
                  <td width="50%"><?php $name = trim(strtolower($row->firstname)) . " " . trim(strtolower($row->lastname));
                                  echo ucwords($name); ?></td>
                </tr>
                <!--<tr><td width="50%"><label>Sub-Kejuruan</label></td>
			<td width="50%"><input type="text" name="subkejuruan" id="subkejuruan" /></td>
			</tr>-->
                <tr>
                  <td width="50%"><label>Badan Keahlian</label></td>
                  <td width="50%">
                    <select id="bidang" name="bidang" class="form-control input-md">
                      <option value="">--Choose--</option>
                      <?php
                      if (isset($m_bk)) {
                        foreach ($m_bk as $val2) {
                          echo '<option value="' . $val2->value . '">' . $val2->name . '</option>';
                        }
                      }
                      ?>
                    </select>
                  </td>
                </tr>
                <tr>
                  <td width="50%"><label>Jenis Permohonan</label></td>
                  <td width="50%">
                    <select name="faip_type" id="faip_type" onchange="cek_perdana()" class="form-control input-md">
                      <option value="">---</option>
                      <option value="00"> Perdana</option>
                      <option value="01"> Pemutakhiran</option>
                    </select>
                  </td>
                </tr>
                <tr id="trfaip_type" style="display:none;">
                  <td width="50%"><label>IP yang dimiliki?</label></td>
                  <td width="50%">
                    <select name="certificate_type" id="certificate_type" class="form-control input-md">
                      <option value="">---</option>
                      <option value="IPP"> IPP</option>
                      <option value="IPM"> IPM</option>
                      <option value="IPU"> IPU</option>
                    </select>
                  </td>
                </tr>
              </table>
              <br />
              <label>Rekapitulasi Nilai Kegiatan:</label><br /><br />

              <table width="100%" id="tbah" class="table">
                <thead>
                  <tr>
                    <th width="25%"><label>Unit Kompetensi</label></th>
                    <th width="25%" style="background-color:#CCFFCC"><label>Nilai yang Diperoleh</label></th>
                    <th width="25%"><label>Batas Nilai Minimum (IPP)</label></th>
                    <th width="25%"><label>Batas Nilai Minimum (IPM) </label></th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td width="25%"><label>Wajib 1</label></td>
                    <td width="25%" style="background-color:#CCFFCC"><label id="wb1"><?php //echo ($hwb1!="")?$hwb1:$user_faip->wajib1_score;
                                                                                      ?></label><input type="hidden" id="hwb1" name="hwb1" value="<?php //echo set_value('hwb1',$user_faip->wajib1_score);
                                                                                                                                                  ?>" /></td>
                    <td width="25%"><label>60</label></td>
                    <td width="25%"><label>300</label></td>
                  </tr>
                  <tr>
                    <td width="25%"><label>Wajib 2</label></td>
                    <td width="25%" style="background-color:#CCFFCC"><label id="wb2"><?php //echo ($hwb2!="")?$hwb2:$user_faip->wajib2_score;
                                                                                      ?></label><input type="hidden" id="hwb2" name="hwb2" value="<?php //echo set_value('hwb2',$user_faip->wajib2_score);
                                                                                                                                                  ?>" /></td>
                    <td width="25%"><label>180</label></td>
                    <td width="25%"><label>900</label></td>
                  </tr>
                  <tr>
                    <td width="25%"><label>Wajib 3</label></td>
                    <td width="25%" style="background-color:#CCFFCC"><label id="wb3"><?php //echo ($hwb3!="")?$hwb3:$user_faip->wajib3_score;
                                                                                      ?></label><input type="hidden" id="hwb3" name="hwb3" value="<?php //echo set_value('hwb3',$user_faip->wajib3_score);
                                                                                                                                                  ?>" /></td>
                    <td width="25%"><label>120</label></td>
                    <td width="25%"><label>600</label></td>
                  </tr>
                  <tr>
                    <td width="25%"><label>Wajib 4</label></td>
                    <td width="25%" style="background-color:#CCFFCC"><label id="wb4"><?php //echo ($hwb4!="")?$hwb4:$user_faip->wajib4_score;
                                                                                      ?></label><input type="hidden" id="hwb4" name="hwb4" value="<?php //echo set_value('hwb4',$user_faip->wajib4_score);
                                                                                                                                                  ?>" /></td>
                    <td width="25%"><label>60</label></td>
                    <td width="25%"><label>300</label></td>
                  </tr>
                  <tr>
                    <td width="25%"><label>Pilihan</label></td>
                    <td width="25%" style="background-color:#CCFFCC"><label id="pil"><?php //echo ($hpil!="")?$hpil:$user_faip->pilihan_score;
                                                                                      ?></label><input type="hidden" id="hpil" name="hpil" value="<?php //echo set_value('hpil',$user_faip->pilihan_score);
                                                                                                                                                  ?>" /></td>
                    <td width="25%"><label>180</label></td>
                    <td width="25%"><label>900</label></td>
                  </tr>
                  <tr>
                    <td width="25%"><label>Jumlah</label></td>
                    <td width="25%" style="background-color:#CCFFCC"><label id="jml"><?php //echo ($hjml!="")?$hjml:$user_faip->total_score;
                                                                                      ?></label><input type="hidden" id="hjml" name="hjml" value="<?php //echo set_value('hjml',$user_faip->total_score);
                                                                                                                                                  ?>" /></td>
                    <td width="25%"><label>600</label></td>
                    <td width="25%"><label>3000</label></td>
                  </tr>
                </tbody>
              </table>
              <br />
              <label>Estimasi:</label><br />
              <div style="background-color:#CCFFFF"><label id="keputusan"><?php //echo ($hkeputusan!="")?$hkeputusan:$user_faip->keputusan;
                                                                          ?></label></div>
              <br />
              <button type="button" name="savefaip" value="1" class="btn btn-next savefaip">Save & Continue</button>


            </section>
          </div>

        </form>

      </div>




      <?php /*/ ?>	
    <!-- Main content -->
    <section class="content">
     <table width="100%" border="0" align="left">
              <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td width="19%" align="center"><a href="<?php echo base_url('member/employers');?>"><i class="fa awesome_style fa-briefcase"></i><br>
                Employers</a></td>
                <td width="19%" align="center"><a href="<?php echo base_url('member/job_seekers');?>"><i class="fa awesome_style awesome_style fa-user"></i><br>
                  Jobseeker</a></td>
                <td width="19%" align="center"><a href="<?php echo base_url('member/posted_jobs');?>"><i class="fa awesome_style fa-upload"></i> <br>
                  Posted Jobs</a></td>
                <td width="19%" align="center"><a href="<?php echo base_url('member/posted_jobs');?>"><i class="fa fa-clipboard awesome_style"></i><br>
                  Featured Jobs</a></td>
                <td width="19%" align="center"><a href="<?php echo base_url('member/pages');?>"><i class="fa awesome_style fa-file-text"></i><br>
                  Content Management</a></td>
              </tr>
              <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td><a href="<?php echo base_url().'member/profiles_lists';?>"></a></td>
              </tr>
              <tr>
                <td align="center"><a href="<?php echo base_url('member/stories');?>"><i class="fa awesome_style fa-thumbs-up"></i><br>
Success Stories</a></td>
                <td align="center"><a href="<?php echo base_url('member/invite_employer');?>"><i class="fa awesome_style fa-envelope"></i><br>
                  Invite Employer</a></td>
                <td align="center"><a href="<?php echo base_url('member/invite_jobseeker');?>"><i class="fa awesome_style fa-users"></i> <br>
                  Invite Jobseeker</a></td>
                <td align="center"><a href="<?php echo base_url('member/email_template');?>"><i class="fa fa-envelope awesome_style"></i><br>
                  Email Templates</a></td>
                <td align="center"><a href="<?php echo base_url('member/ads');?>"><i class="fa awesome_style fa-bullhorn"></i><br>
Ads</a></td>
              </tr>
              <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td align="center"><a href="<?php echo base_url('member/industries');?>"><i class="fa fa-desktop awesome_style"></i><br>
                  Job Industries</a></td>
                <td align="center"><a href="<?php echo base_url('member/institute');?>"><i class="fa awesome_style fa-university"></i><br>
                  Institute</a></td>
                <td align="center"><a href="<?php echo base_url('member/salary');?>"><i class="fa awesome_style fa-money"></i> <br>
                  Salary</a></td>
                <td align="center"><a href="<?php echo base_url('member/qualification');?>"><i class="fa  awesome_style fa-graduation-cap">&nbsp;</i><br>
               Qualification</a></td>
                <td align="center"><a href="<?php echo base_url('member/prohibited_keyword');?>"><i class="fa awesome_style fa-tags"></i><br>
Manage Prohibited Keywords</a></td>
              </tr>
              <tr>
                <td align="center">&nbsp;</td>
                <td align="center">&nbsp;</td>
                <td align="center">&nbsp;</td>
                <td align="center">&nbsp;</td>
                <td align="center">&nbsp;</td>
              </tr>
              <tr>
                <td align="center"><a href="<?php echo base_url('member/skills');?>"><i class="fa awesome_style fa-tags"></i><br>
Manage Skills</a></td>
                <td align="center"><a href="<?php echo base_url('member/manage_newsletters');?>"><i class="fa fa-envelope awesome_style"></i><br>
                  Manage Newsletters</a></td>
                <td align="center"><a href="<?php echo base_url('member/job_alert_queue');?>"><i class="fa fa-envelope awesome_style"></i><br>
                  Job Alert Queue</a></td>
                <td align="center"></td>
                <td align="center"></td>
              </tr>
              <tr>
                <td align="center">&nbsp;</td>
                <td align="center">&nbsp;</td>
                <td align="center">&nbsp;</td>
                <td align="center">&nbsp;</td>
                <td align="center">&nbsp;</td>
              </tr>
            </table>
    </section>
    <!-- /.content -->
<?php */ ?>
    </aside>
    <!-- /.right-side -->
    <?php $this->load->view('member/common/footer'); ?>

    <link href="<?php echo base_url('assets/ada/css/jquery.steps.css'); ?>" rel="stylesheet" type="text/css" />
    <script src="<?php echo base_url('assets/ada/js/jquery.steps.js'); ?>"></script>



    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
  <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
<![endif]-->

    <link href="<?php echo base_url('assets/ada/parsley/src/parsley.css'); ?>" rel="stylesheet">
    <style class="example">
      h4 {
        margin-bottom: 10px;
      }

      p.parsley-success {
        color: #468847;
        background-color: #DFF0D8;
        border: 1px solid #D6E9C6;
      }

      p.parsley-error {
        color: #B94A48;
        background-color: #F2DEDE;
        border: 1px solid #EED3D7;
      }
    </style>



    <!--<script src="<?php //echo base_url('assets/ada/js/validate.min.js'); 
                      ?>"></script>
<script src="http://ajax.aspnetcdn.com/ajax/jquery.validate/1.9/jquery.validate.min.js"></script>-->

    <script src="<?php echo base_url('assets/ada/parsley/bower_components/bootstrap/js/affix.js'); ?>"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/7.3/highlight.min.js"></script>

    <script src="<?php echo base_url('assets/ada/parsley/dist/parsley.js'); ?>"></script>
    <script class="example">
      $(function() {
        $('#formfaip').parsley().on('field:validated', function() {
            var ok = $('.parsley-error').length === 0;
            $('.bs-callout-info').toggleClass('hidden', !ok);
            $('.bs-callout-warning').toggleClass('hidden', ok);
          })
          .on('form:submit', function() {
            // Don't submit form for this demo
          });
        var error = '';
        window.Parsley.on('field:error', function(fieldInstance) {
          var x = this.$element;
          //console.log('Validation failed for: ', x);

          var arrErrorMsg = fieldInstance.getErrorsMessages();
          var errorMsg = arrErrorMsg.join(';');
          //console.log(errorMsg);

          // get name of the input with error
          console.log(fieldInstance.$element.attr('name') + ' ' + errorMsg);
          error = error + fieldInstance.$element.attr('name') + ' ' + errorMsg + '<br />';
          $('.errormsg').html(error);
        });

        $("#submitfaip").click(function() {
          error = '';
        });

        $(".savefaip").click(function() {
          var form = $('form')[0];
          var formData = new FormData(form);
          $.ajax({
            url: "<?php echo site_url('faip/submit') ?>",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(data, textStatus, jqXHR) {
              //console.log(data);
              alert(textStatus);
              if (textStatus == 'success') window.location.href = "<?php echo base_url(); ?>faip/editfaip/" + data;
              else {
                $.ajax({
                  url: "<?php echo site_url('faip/log_faip') ?>",
                  type: 'POST',
                  data: {
                    error: jqXHR.responseText
                  }
                });
              }
              //$('#id_faip').val(data);
            },
            error: function(jqXHR, textStatus, errorThrown) {

              $.ajax({
                url: "<?php echo site_url('faip/log_faip') ?>",
                type: 'POST',
                data: {
                  error: jqXHR.responseText
                },
                processData: false,
                contentType: false,
                success: function(data, textStatus, jqXHR) {

                },
                error: function(jqXHR, textStatus, errorThrown) {

                }
              });

              alert(textStatus);
            }
          });
        });

      });
    </script>



    <script src="<?php echo base_url(); ?>assets/js/typeahead.bundle.min.js"></script>
    <script>
      $("#example-manipulation").steps({
        /*headerTag: "h3",
    bodyTag: "section",
    enableAllSteps: true,
    enablePagination: false
	*/
        headerTag: "h3",
        bodyTag: "section",
        transitionEffect: "none",
        enableFinishButton: false,
        enablePagination: false,
        enableAllSteps: true,
        titleTemplate: "#title#",
        cssClass: "tabcontrol"
      });

      function add111(tableID) {
        var currentNumber = 1;
        if ($('.' + tableID + '-item').length > 0) {
          currentNumber = $('.' + tableID + '-item').last().data('id') + 1
        }

        $('#t' + tableID).append(
          '<tr class=" ' + tableID + '-item" data-id="' + currentNumber + '" >'

          +
          '<td><select id="addr_type' + currentNumber + '" name="addr_type[]" class="form-control input-md" required="">' +
          '		<option value="">--Choose--</option>' +
          '		<option value="1" >Home</option>' +
          '		<option value="2" >Work</option>' +
          '		<option value="3" >Other</option>' +
          '		</select></td>' +
          '<td><input type="text" value="" name="addr_desc[]" id="addr_desc' + currentNumber + '" class="form-control "/></td>' +
          '<td><input type="text" value="" name="addr_loc[]" id="addr_loc' + currentNumber + '" class="form-control "/></td>' +
          '<td><input type="text" value="" name="addr_zip[]" id="addr_zip' + currentNumber + '" class="form-control "/></td>'

          +
          '<td class="td-action">' +
          '<button type="button" class="btn btn-danger btn-xs ' + tableID + '-item-remove-button" data-id="' + currentNumber + '">' +
          '<i class="fa fa-trash-o fa-fw"></i>X' +
          '</button>' +
          '</td>' +
          '</tr>'
        );

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

        $('#t' + tableID).find("input[id^='addr_loc']").typeahead(null, {
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

      function add112(tableID) {
        var currentNumber = 1;
        if ($('.' + tableID + '-item').length > 0) {
          currentNumber = $('.' + tableID + '-item').last().data('id') + 1
        }

        $('#t' + tableID).append(
          '<tr class=" ' + tableID + '-item" data-id="' + currentNumber + '" >'

          +
          '<td><input type="text" value="" name="exp_name[]" id="exp_name' + currentNumber + '" class="form-control "/></td>' +
          '<td><input type="text" value="" name="exp_desc[]" id="exp_desc' + currentNumber + '" class="form-control "/></td>' +
          '<td><input type="text" value="" name="exp_loc[]" id="exp_loc' + currentNumber + '" class="form-control "/></td>' +
          '<td><input type="text" value="" name="exp_zip[]" id="exp_zip' + currentNumber + '" class="form-control "/></td>'

          +
          '<td class="td-action">' +
          '<button type="button" class="btn btn-danger btn-xs ' + tableID + '-item-remove-button" data-id="' + currentNumber + '">' +
          '<i class="fa fa-trash-o fa-fw"></i>X' +
          '</button>' +
          '</td>' +
          '</tr>'
        );

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

        $('#t' + tableID).find("input[id^='exp_loc']").typeahead(null, {
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

      function add113(tableID) {
        var currentNumber = 1;
        if ($('.' + tableID + '-item').length > 0) {
          currentNumber = $('.' + tableID + '-item').last().data('id') + 1
        }

        $('#t' + tableID).append(
          '<tr class=" ' + tableID + '-item" data-id="' + currentNumber + '" >'

          +
          '<td>' +
          '<select id="phone_type' + currentNumber + '" name="phone_type[]" class="form-control input-md">' +
          '<option value="">--Choose--</option>' +
          '<option value="mobile_phone">Mobile</option>' +
          '<option value="home_phone">Home</option>' +
          '<option value="office_phone">Work</option>' +
          '<option value="main_phone">Main</option>' +
          '<option value="workfax_phone">Work Fax</option>' +
          '<option value="homefax_phone">Home Fax</option>' +
          '<option value="other_phone">Other</option>' +
          '</select></td>' +
          '<td><input type="text" value="" name="phone_value[]" id="phone_value' + currentNumber + '" class="form-control "/></td>'

          +
          '<td class="td-action">' +
          '<button type="button" class="btn btn-danger btn-xs ' + tableID + '-item-remove-button" data-id="' + currentNumber + '">' +
          '<i class="fa fa-trash-o fa-fw"></i>X' +
          '</button>' +
          '</td>' +
          '</tr>'
        );
      }

      function add12(tableID) {
        var currentNumber = 1;
        if ($('.' + tableID + '-item').length > 0) {
          //currentNumber = $('.'+tableID+'-item').last().data('id') + 1

          var i_seq = 1;
          $('.' + tableID + '-item td:first-child').each(function() {
            i_seq++;
          });
          currentNumber = i_seq;
        }




        $('#t' + tableID).append(
          '<tr class=" ' + tableID + '-item" data-id="' + currentNumber + '" >' +
          '<td class="">' +
          currentNumber +
          '</td>' +
          '<td class="col-md-2"><input id="12_t' + currentNumber + '" name="12_t[]" value=0 type="hidden">' +
          '<input id="12_school' + currentNumber + '" name="12_school[]" value="" type="text" placeholder="" class="form-control input-md "  required="">' +
          '</td>'


          +
          '<td class="col-md-2">' +
          '<select id="12_degree' + currentNumber + '" name="12_degree[]" class="form-control input-md" required="">' +
          '<option value="">--Choose--</option>'


          <?php
          if (isset($m_degree)) {
            foreach ($m_degree as $val2) {
              echo '+ \'<option value="' . $val2->EDUCATION_TYPE_ID . '">' . $val2->DESCRIPTION . '</option>\'';
            }
          }
          ?>

          +
          '</select></td>'


          +
          '<td class="col-md-2">' +
          '<input id="12_fakultas' + currentNumber + '" name="12_fakultas[]" value="" type="text" placeholder="" class="form-control input-md "  required="">' +
          '</td>' +
          '<td class="col-md-2">' +
          '<input id="12_fieldofstudy' + currentNumber + '" name="12_fieldofstudy[]" value="" type="text" placeholder="" class="form-control input-md "  required="">' +
          '</td>' +
          '<td class="col-md-2">' +
          '<input id="12_kota' + currentNumber + '" name="12_kota[]" value="" type="text" placeholder="" class="form-control input-md "  required="">' +
          '</td>' +
          '<td class="col-md-2">' +
          '<input id="12_provinsi' + currentNumber + '" name="12_provinsi[]" value="" type="text" placeholder="" class="form-control input-md "  required="">' +
          '</td>' +
          '<td class="col-md-2">' +
          '<input id="12_negara' + currentNumber + '" name="12_negara[]" value="" type="text" placeholder="" class="form-control input-md "  required="">' +
          '</td>' +
          '<td class="col-md-2">' +
          '<input id="12_tahunlulus' + currentNumber + '" name="12_tahunlulus[]" value="" type="text" placeholder="" class="form-control input-md "  required="">' +
          '</td>' +
          '<td class="col-md-2">' +
          '<input id="12_title' + currentNumber + '" name="12_title[]" value="" type="text" placeholder="" class="form-control input-md "  required="">' +
          '</td>' +
          '<td class="col-md-2">' +
          '<input id="12_activities' + currentNumber + '" name="12_activities[]" value="" type="text" placeholder="" class="form-control input-md "  required="">' +
          '</td>' +
          '<td class="col-md-2">' +
          '<input id="12_description' + currentNumber + '" name="12_description[]" value="" type="text" placeholder="" class="form-control input-md "  required="">' +
          '</td>' +
          '<td class="col-md-2">' +
          '<input id="12_score' + currentNumber + '" name="12_score[]" value="" type="text" placeholder="" class="form-control input-md "  required="">' +
          '</td>'
          //+ '<td class="col-md-2">'
          //+ '<input id="12_judicium' + currentNumber + '" name="12_judicium[]" value="" type="text" placeholder="" class="form-control input-md "  required="">'
          //+ '</td>'

          +
          '	<td class="col-md-8">' +
          '		<div class="form-group">' +
          '			<div id="12_avatarattedu' + currentNumber + '">' +
          '			<input type="hidden" name="12_edu_image_url[]" value="" style="display: inline-block;">'

          +
          '			</div>' +
          '			<div id="12_errUploadattedu' + currentNumber + '"></div>' +
          '			<input type="file" name="12_attedu' + currentNumber + '" class="form-control input-md" ' +
          '			id="12_attedu' + currentNumber + '" onchange="upload_edu(\'attedu' + currentNumber + '\')">' +
          '		</div>' +
          '	</td>'

          +
          '<td class="td-action">' +
          '<button type="button" class="btn btn-danger btn-xs ' + tableID + '-item-remove-button" data-id="' + currentNumber + '">' +
          '<i class="fa fa-trash-o fa-fw"></i>X' +
          '</button>' +
          '</td>' +
          '</tr>'
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

        $('#t' + tableID).find("input[id^='12_title']").typeahead(null, {
          name: 'titles',
          display: 'name',
          source: titles,
          hint: true,
          highlight: true,
          minLength: 2,
          limit: Infinity,
          templates: {}
        });
        $('#t' + tableID).find("input[id^='12_kota']").typeahead(null, {
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

      function add13(tableID) {
        var currentNumber = 1;
        if ($('.' + tableID + '-item').length > 0) {
          currentNumber = $('.' + tableID + '-item').last().data('id') + 1
        }
        var seq = $('.' + tableID + '-item').length;
        seq = seq + 1;

        $('#t' + tableID).append(
          '<tr class=" ' + tableID + '-item" data-id="' + currentNumber + '" >' +
          '<td class="">' +
          seq +
          '</td>' +
          '<td class="col-md-2"><input id="13_t' + currentNumber + '" name="13_t[]" value=0 type="hidden">' +
          '<input id="13_nama_org' + currentNumber + '" name="13_nama_org[]" type="text" placeholder="" class="form-control input-md "  required="">' +
          '</td>'

          +
          '<td class="col-md-2">' +
          '<input id="13_tempat' + currentNumber + '" name="13_tempat[]" type="text" placeholder="" class="form-control input-md "  required="">' +
          '</td>'

          +
          '<td class="col-md-2">' +
          '	<select id="13_jenis' + currentNumber + '" name="13_jenis[]" class="form-control input-md" required="">' +
          '		<option value="">---</option>' +
          '		<option value="Organisasi PII">Organisasi PII</option>' +
          '		<option value="Organisasi Keinsinyuran Non PII">Organisasi Keinsinyuran Non PII</option>' +
          '		<option value="Organisasi Non Keinsinyuran">Organisasi Non Keinsinyuran</option>' +
          '	</select>' +
          '</td>'

          +
          '<td class="col-md-2">' +
          '<input id="13_provinsi' + currentNumber + '" name="13_provinsi[]" type="text" placeholder="" class="form-control input-md "  required="">' +
          '</td>' +
          '<td class="col-md-2">' +
          '<input id="13_negara' + currentNumber + '" name="13_negara[]" type="text" placeholder="" class="form-control input-md "  required="">' +
          '</td>'



          +
          '<td class="col-md-2">' +
          '	<div class="">' +
          '	<select id="13_startdate' + currentNumber + '" name="13_startdate[]" class="form-control input-md" required="">' +
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
          '	<input id="13_startyear' + currentNumber + '" name="13_startyear[]" type="text" placeholder="Year" class="form-control input-md number datepickeryear" required="">' +
          '	</div>' +
          '	<div class="" id="13_ispresent1' + currentNumber + '">-</div>' +
          '	<div class="" id="13_ispresent2' + currentNumber + '">' +
          '	<select id="13_enddate' + currentNumber + '" name="13_enddate[]" class="form-control input-md">' +
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
          '	<input id="13_endyear' + currentNumber + '" name="13_endyear[]" type="text" placeholder="Year" class="form-control input-md number datepickeryear" >' +
          '	</div>' +
          '	<label class="form-check-label"><input type="hidden" name="13_workx[]" value="0"><input type="checkbox" id="13_work' + currentNumber + '" name="13_work[]" class="form-check-input" value="1" data-id="' + currentNumber + '">Masih menjadi anggota</label>' +
          '	</td>'

          +
          '<td class="col-md-2">'
          //+ '<input id="13_jabatan' + currentNumber + '" name="13_jabatan[]" type="text" placeholder="" class="form-control input-md "  required="">'

          +
          '<select id="13_jabatan' + currentNumber + '" name="13_jabatan[]" style="height: 34px;" required="">' +
          '<option value="">--Choose--</option>'
          <?php
          if (isset($bp_13_q)) {
            foreach ($bp_13_q as $val2) { ?> +
              '<option value="<?php echo $val2->value; ?>" ><?php echo $val2->desc; ?></option>'
          <?php
            }
          }
          ?> +
          '</select>'

          +
          '</td>'

          +
          '<td class="col-md-2">' +
          '<select id="13_tingkat' + currentNumber + '" name="13_tingkat[]" style="height: 34px;" required="">' +
          '<option value="">--Choose--</option>'
          <?php
          if (isset($bp_13_r)) {
            foreach ($bp_13_r as $val2) { ?> +
              '<option value="<?php echo $val2->value; ?>" ><?php echo $val2->desc; ?></option>'
          <?php
            }
          }
          ?> +
          '</select>' +
          '</td>'

          +
          '<td class="col-md-2">' +
          '<select id="13_lingkup' + currentNumber + '" name="13_lingkup[]" style="height: 34px;" required="">' +
          '<option value="">--Choose--</option>' +
          '<option value="Asosiasi Profesi">Asosiasi Profesi</option>' +
          '<option value="Lembaga Pemerintah">Lembaga Pemerintah</option>' +
          '<option value="Lembaga Pendidikan">Lembaga Pendidikan</option>' +
          '<option value="Badan Usaha Milik Negara">Badan Usaha Milik Negara</option>' +
          '<option value="Badan Usaha Swasta">Badan Usaha Swasta</option>' +
          '<option value="Organisasi Kemasyarakatan">Organisasi Kemasyarakatan</option>' +
          '<option value="Lain-lain">Lain-lain</option>' +
          '</select>' +
          '</td>'

          +
          '<td class="col-md-2">' +
          '<textarea id="13_aktifitas' + currentNumber + '" name="13_aktifitas[]" class="form-control input-md " style="width:200px;" rows="8"></textarea>' +
          '</td>'

          +
          '	<td class="col-md-8">' +
          '		<div class="form-group">' +
          '			<div id="13_avatarattorg' + currentNumber + '">' +
          '			<input type="hidden" name="13_org_image_url[]" value="" style="display: inline-block;">'

          +
          '			</div>' +
          '			<div id="13_errUploadattorg' + currentNumber + '"></div>' +
          '			<input type="file" name="13_attorg' + currentNumber + '" class="form-control input-md" ' +
          '			id="13_attorg' + currentNumber + '" onchange="upload_org(\'attorg' + currentNumber + '\')">' +
          '		</div>' +
          '	</td>'

          +
          '<td class="col-md-2">'
          //+ '<textarea id="13_aktifitas' + currentNumber + '" name="13_aktifitas[]" class="form-control input-md " style="width:200px;" rows="8"></textarea>'

          +
          '<select id="13_komp' + currentNumber + '" name="13_komp[' + currentNumber + '][]" class="input-md" style="height: 200px;" required="" multiple>' +
          '<option value="">--Choose--</option>'
          <?php
          if (isset($m_act_13)) {
            $temp = true;
            foreach ($m_act_13 as $val) {
              if (strlen($val->value) < 8) {
                if (!$temp) { ?> +
                  '</optgroup>'
                <?php }  ?> +
                '<optgroup label="<?php echo $val->value . ' - ' . $val->title; ?>">'
              <?php $temp = true;
              } else {
                $temp = false; ?> +
                '<option value="<?php echo $val->value; ?>" ><?php echo $val->value . ' - ' . $val->title ?></option>'
          <?php }
            }
          }
          ?> +
          '</select>' +
          '</td>'

          /*
          + '<td class="col-md-4">'
          	+ '<table width="100%" id="'+tableID+'komp_' + currentNumber + '" class="table">'
          	+ '</table>'
          	+ '<div class="col-md-12" style="padding-bottom:20px;">'
          	+ '<button type="button" class="btn btn-primary" onclick="addKomp(\''+tableID+'komp_' + currentNumber + '\')">+ Tambah Kompetensi</button>'
          	+ '</div>'				
          + '</td>'
          */
          +
          '<td class="td-action">' +
          '<button type="button" class="btn btn-danger btn-xs ' + tableID + '-item-remove-button" data-id="' + currentNumber + '">' +
          '<i class="fa fa-trash-o fa-fw"></i>X' +
          '</button>' +
          '</td>' +
          '</tr>'
        );
        /*
        var regions = new Bloodhound({
        	datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
        	queryTokenizer: Bloodhound.tokenizers.whitespace,
        	remote: {
        		cache: false,
        		url: '<?php echo base_url(); ?>/welcome/searchregion?q=%QUERY%',
        		wildcard: '%QUERY%',
        		filter: function(list) {
        			return $.map(list, function(company) {
        				return { name: company.name };
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
        				return { name: company.name };
        			});
        		}
        	}
        });
        $('#'+tableID).find("input[id^='addresscity']").typeahead(null, {
        	name: 'regions',
        	display: 'name',
        	source: regions,
        	hint: true,
        	highlight: true,
        	minLength: 2,
        	limit: Infinity,
        	templates: {
        	}
        });
        $('#'+tableID).find("input[id^='addressprovince']").typeahead(null, {
        	name: 'provinces',
        	display: 'name',
        	source: provinces,
        	hint: true,
        	highlight: true,
        	minLength: 2,
        	limit: Infinity,
        	templates: {
        	}
        });
        
        var input = document.querySelector("#addressphone"+ currentNumber);
        window.intlTelInput(input, {
           autoHideDialCode: true,
           nationalMode: false,
           preferredCountries: ['id'],
          utilsScript: "<?php echo base_url(); ?>assets/ada/phone/js/utils.js",
        });
        
        document.getElementById('addressphone'+ currentNumber).addEventListener('input', function() {
          let start = this.selectionStart;
          let end = this.selectionEnd;
          
          const current = this.value
          const corrected = current.replace(/[^-+\d]/g, '');
          this.value = corrected;
          
          if (corrected.length < current.length) --end;
          this.setSelectionRange(start, end);
        });*/

      }

      function add14(tableID) {
        var currentNumber = 1;
        if ($('.' + tableID + '-item').length > 0) {
          currentNumber = $('.' + tableID + '-item').last().data('id') + 1
        }
        var seq = $('.' + tableID + '-item').length;
        seq = seq + 1;

        $('#t' + tableID).append(
          '<tr class=" ' + tableID + '-item" data-id="' + currentNumber + '" >' +
          '<td class="">' +
          seq +
          '</td>'

          +
          '<td class="col-md-2"><input id="14_t' + currentNumber + '" name="14_t[]" value=0 type="hidden">' +
          '<input id="14_nama' + currentNumber + '" name="14_nama[]" type="text" placeholder="" class="form-control input-md "  required="">' +
          '</td>' +
          '<td class="col-md-2">' +
          '<input id="14_lembaga' + currentNumber + '" name="14_lembaga[]" type="text" placeholder="" class="form-control input-md "  required="">' +
          '</td>' +
          '<td class="col-md-2">' +
          '<input id="14_location' + currentNumber + '" name="14_location[]" type="text" placeholder="" class="form-control input-md "  required="">' +
          '</td>' +
          '<td class="col-md-2">' +
          '<input id="14_provinsi' + currentNumber + '" name="14_provinsi[]" type="text" placeholder="" class="form-control input-md "  required="">' +
          '</td>' +
          '<td class="col-md-2">' +
          '<input id="14_negara' + currentNumber + '" name="14_negara[]" type="text" placeholder="" class="form-control input-md "  required="">' +
          '</td>'

          +
          '<td class="col-md-2">' +
          '	<div class="">' +
          '	<select id="14_startdate' + currentNumber + '" name="14_startdate[]" class="form-control input-md" required="">' +
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
          '	<input id="14_startyear' + currentNumber + '" name="14_startyear[]" type="text" placeholder="Year" class="form-control input-md number datepickeryear" required="">' +
          '	</div>' +
          '	</td>'

          +
          '<td class="col-md-2">' +
          '<select id="14_tingkat' + currentNumber + '" name="14_tingkat[]" style="height: 34px;" required="">' +
          '<option value="">--Choose--</option>'
          <?php
          if (isset($bp_14_q)) {
            foreach ($bp_14_q as $val2) { ?> +
              '<option value="<?php echo $val2->value; ?>" ><?php echo $val2->desc; ?></option>'
          <?php
            }
          }
          ?> +
          '</select>' +
          '</td>' +
          '<td class="col-md-2">' +
          '<select id="14_tingkatlembaga' + currentNumber + '" name="14_tingkatlembaga[]" style="height: 34px;" required="">' +
          '<option value="">--Choose--</option>'
          <?php
          if (isset($bp_14_r)) {
            foreach ($bp_14_r as $val2) { ?> +
              '<option value="<?php echo $val2->value; ?>" ><?php echo $val2->desc; ?></option>'
          <?php
            }
          }
          ?> +
          '</select>' +
          '</td>' +
          '<td class="col-md-2" style="display:none;">' +
          '<select id="14_total' + currentNumber + '" name="14_total[]" style="height: 34px;" >' +
          '<option value="">--Choose--</option>'
          <?php
          if (isset($bp_14_p)) {
            foreach ($bp_14_p as $val2) { ?> +
              '<option value="<?php echo $val2->value; ?>" ><?php echo $val2->desc; ?></option>'
          <?php
            }
          }
          ?> +
          '</select>' +
          '</td>'

          +
          '<td class="col-md-2">' +
          '<textarea id="14_uraian' + currentNumber + '" name="14_uraian[]" class="form-control input-md " style="width:200px;" rows="8"></textarea>' +
          '</td>'

          +
          '	<td class="col-md-8">' +
          '		<div class="form-group">' +
          '			<div id="14_avatarattaward' + currentNumber + '">' +
          '			<input type="hidden" name="14_award_image_url[]" value="" style="display: inline-block;">'

          +
          '			</div>' +
          '			<div id="14_errUploadattaward' + currentNumber + '"></div>' +
          '			<input type="file" name="14_attaward' + currentNumber + '" class="form-control input-md" ' +
          '			id="14_attaward' + currentNumber + '" onchange="upload_award(\'attaward' + currentNumber + '\')">' +
          '		</div>' +
          '	</td>'

          +
          '<td class="col-md-2">'
          //+ '<textarea id="14_uraian' + currentNumber + '" name="14_uraian[]" class="form-control input-md " style="width:200px;" rows="8"></textarea>'

          +
          '<select id="14_komp' + currentNumber + '" name="14_komp[' + currentNumber + '][]" class="input-md" style="height: 200px;" required="" multiple>' +
          '<option value="">--Choose--</option>'
          <?php
          if (isset($m_act_13)) {
            $temp = true;
            foreach ($m_act_13 as $val) {
              if (strlen($val->value) < 8) {
                if (!$temp) { ?> +
                  '</optgroup>'
                <?php }  ?> +
                '<optgroup label="<?php echo $val->value . ' - ' . $val->title; ?>">'
              <?php $temp = true;
              } else {
                $temp = false; ?> +
                '<option value="<?php echo $val->value; ?>" ><?php echo $val->value . ' - ' . $val->title ?></option>'
          <?php }
            }
          }
          ?> +
          '</select>'

          +
          '</td>'

          /*
          + '<td class="col-md-4">'
          	
          	+ '<table width="100%" id="'+tableID+'komp_' + currentNumber + '" class="table">'
          	+ '</table>'
          	+ '<div class="col-md-12" style="padding-bottom:20px;">'
          	+ '<button type="button" class="btn btn-primary" onclick="addKomp(\''+tableID+'komp_' + currentNumber + '\')">+ Tambah Kompetensi</button>'
          	+ '</div>'
          	
          + '</td>'
          */

          +
          '<td class="td-action">' +
          '<button type="button" class="btn btn-danger btn-xs ' + tableID + '-item-remove-button" data-id="' + currentNumber + '">' +
          '<i class="fa fa-trash-o fa-fw"></i>X' +
          '</button>' +
          '</td>' +
          '</tr>'
        );

      }

      function add15(tableID) {
        var currentNumber = 1;
        if ($('.' + tableID + '-item').length > 0) {
          currentNumber = $('.' + tableID + '-item').last().data('id') + 1
        }
        var seq = $('.' + tableID + '-item').length;
        seq = seq + 1;
        $('#t' + tableID).append(
          '<tr class=" ' + tableID + '-item" data-id="' + currentNumber + '" >' +
          '<td class="">' +
          seq +
          '</td>' +
          '<td class="col-md-2"><input id="15_t' + currentNumber + '" name="15_t[]" value=0 type="hidden">' +
          '<input id="15_nama' + currentNumber + '" name="15_nama[]" type="text" placeholder="" class="form-control input-md "  required="">' +
          '</td>' +
          '<td class="col-md-2">' +
          '<input id="15_lembaga' + currentNumber + '" name="15_lembaga[]" type="text" placeholder="" class="form-control input-md "  required="">' +
          '</td>' +
          '<td class="col-md-2">' +
          '<input id="15_location' + currentNumber + '" name="15_location[]" type="text" placeholder="" class="form-control input-md "  required="">' +
          '</td>' +
          '<td class="col-md-2">' +
          '<input id="15_provinsi' + currentNumber + '" name="15_provinsi[]" type="text" placeholder="" class="form-control input-md "  required="">' +
          '</td>' +
          '<td class="col-md-2">' +
          '<input id="15_negara' + currentNumber + '" name="15_negara[]" type="text" placeholder="" class="form-control input-md "  required="">' +
          '</td>'


          +
          '<td class="col-md-2">' +
          '	<div class="">' +
          '	<select id="15_startdate' + currentNumber + '" name="15_startdate[]" class="form-control input-md" required="">' +
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
          '	<input id="15_startyear' + currentNumber + '" name="15_startyear[]" type="text" placeholder="Year" class="form-control input-md number datepickeryear" required="">' +
          '	</div>' +
          '	<div class="" id="15_ispresent1' + currentNumber + '">-</div>' +
          '	<div class="" id="15_ispresent2' + currentNumber + '">' +
          '	<select id="15_enddate' + currentNumber + '" name="15_enddate[]" class="form-control input-md">' +
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
          '	<input id="15_endyear' + currentNumber + '" name="15_endyear[]" type="text" placeholder="Year" class="form-control input-md number datepickeryear" >' +
          '	</div>' +
          '	<label class="form-check-label"><input type="hidden" name="15_workx[]" value="0"><input type="checkbox" id="15_work' + currentNumber + '" name="15_work[]" class="form-check-input" value="1" data-id="' + currentNumber + '">Kredensial ini tidak akan kedaluwarsa</label>' +
          '	</td>'


          /*
          + '<td class="col-md-2">'
          + '<input id="15_jam' + currentNumber + '" name="15_jam[]" type="text" placeholder="" class="form-control input-md "  required="">'
          + '</td>'
          + '<td class="col-md-2">'
          + '<input id="15_lic' + currentNumber + '" name="15_lic[]" type="text" placeholder="" class="form-control input-md "  required="">'
          + '</td>'
          + '<td class="col-md-2">'
          + '<input id="15_url' + currentNumber + '" name="15_url[]" type="text" placeholder="" class="form-control input-md "  required="">'
          + '</td>'
          */

          +
          '<td class="col-md-2">' +
          '<select id="15_tingkat' + currentNumber + '" name="15_tingkat[]" style="height: 34px;" required="">' +
          '<option value="">--Choose--</option>'
          <?php
          if (isset($bp_15_r)) {
            foreach ($bp_15_r as $val2) { ?> +
              '<option value="<?php echo $val2->value; ?>" ><?php echo $val2->desc; ?></option>'
          <?php
            }
          }
          ?> +
          '</select>' +
          '</td>' +
          '<td class="col-md-2">' +
          '<select id="15_jam' + currentNumber + '" name="15_jam[]" style="height: 34px;" required="">' +
          '<option value="">--Choose--</option>'
          <?php
          if (isset($bp_15_q)) {
            foreach ($bp_15_q as $val2) { ?> +
              '<option value="<?php echo $val2->value; ?>" ><?php echo $val2->desc; ?></option>'
          <?php
            }
          }
          ?> +
          '</select>' +
          '</td>' +
          '<td class="col-md-2" style="display:none;">' +
          '<select id="15_total' + currentNumber + '" name="15_total[]" style="height: 34px;">' +
          '<option value="">--Choose--</option>'
          <?php
          if (isset($bp_15_p)) {
            foreach ($bp_15_p as $val2) { ?> +
              '<option value="<?php echo $val2->value; ?>" ><?php echo $val2->desc; ?></option>'
          <?php
            }
          }
          ?> +
          '</select>' +
          '</td>'

          +
          '<td class="col-md-2">' +
          '<textarea id="15_uraian' + currentNumber + '" name="15_uraian[]" class="form-control input-md " style="width:200px;" rows="8"></textarea>' +
          '</td>'

          +
          '	<td class="col-md-8">' +
          '		<div class="form-group">' +
          '			<div id="15_avatarattcourse' + currentNumber + '">' +
          '			<input type="hidden" name="15_course_image_url[]" value="" style="display: inline-block;">'

          +
          '			</div>' +
          '			<div id="15_errUploadattcourse' + currentNumber + '"></div>' +
          '			<input type="file" name="15_attcourse' + currentNumber + '" class="form-control input-md" ' +
          '			id="15_attcourse' + currentNumber + '" onchange="upload_course(\'attcourse' + currentNumber + '\')">' +
          '		</div>' +
          '	</td>'

          +
          '<td class="col-md-2">'
          //+ '<textarea id="15_uraian' + currentNumber + '" name="15_uraian[]" class="form-control input-md " style="width:200px;" rows="8"></textarea>'

          +
          '<select id="15_komp' + currentNumber + '" name="15_komp[' + currentNumber + '][]" class="input-md" style="height: 200px;" required="" multiple>' +
          '<option value="">--Choose--</option>'
          <?php
          if (isset($m_act_15)) {
            $temp = true;
            foreach ($m_act_15 as $val) {
              if (strlen($val->value) < 8) {
                if (!$temp) { ?> +
                  '</optgroup>'
                <?php }  ?> +
                '<optgroup label="<?php echo $val->value . ' - ' . $val->title; ?>">'
              <?php $temp = true;
              } else {
                $temp = false; ?> +
                '<option value="<?php echo $val->value; ?>" ><?php echo $val->value . ' - ' . $val->title ?></option>'
          <?php }
            }
          }
          ?> +
          '</select>'

          +
          '</td>'
          /*
          + '<td class="col-md-4">'
          	
          	+ '<table width="100%" id="'+tableID+'komp_' + currentNumber + '" class="table">'
          	+ '</table>'
          	+ '<div class="col-md-12" style="padding-bottom:20px;">'
          	+ '<button type="button" class="btn btn-primary" onclick="addKomp(\''+tableID+'komp_' + currentNumber + '\')">+ Tambah Kompetensi</button>'
          	+ '</div>'
          	
          + '</td>'
          */
          +
          '<td class="td-action">' +
          '<button type="button" class="btn btn-danger btn-xs ' + tableID + '-item-remove-button" data-id="' + currentNumber + '">' +
          '<i class="fa fa-trash-o fa-fw"></i>X' +
          '</button>' +
          '</td>' +
          '</tr>'
        );

      }

      function add16(tableID) {
        var currentNumber = 1;
        if ($('.' + tableID + '-item').length > 0) {
          currentNumber = $('.' + tableID + '-item').last().data('id') + 1
        }
        var seq = $('.' + tableID + '-item').length;
        seq = seq + 1;
        $('#t' + tableID).append(
          '<tr class=" ' + tableID + '-item" data-id="' + currentNumber + '" >' +
          '<td class="">' +
          seq +
          '</td>' +
          '<td class="col-md-2"><input id="16_t' + currentNumber + '" name="16_t[]" value=0 type="hidden">' +
          '<input id="16_nama' + currentNumber + '" name="16_nama[]" type="text" placeholder="" class="form-control input-md "  required="">' +
          '</td>' +
          '<td class="col-md-2">' +
          '<input id="16_lembaga' + currentNumber + '" name="16_lembaga[]" type="text" placeholder="" class="form-control input-md "  required="">' +
          '</td>' +
          '<td class="col-md-2">' +
          '<input id="16_location' + currentNumber + '" name="16_location[]" type="text" placeholder="" class="form-control input-md "  required="">' +
          '</td>' +
          '<td class="col-md-2">' +
          '<input id="16_provinsi' + currentNumber + '" name="16_provinsi[]" type="text" placeholder="" class="form-control input-md "  required="">' +
          '</td>' +
          '<td class="col-md-2">' +
          '<input id="16_negara' + currentNumber + '" name="16_negara[]" type="text" placeholder="" class="form-control input-md "  required="">' +
          '</td>'


          +
          '<td class="col-md-2">' +
          '	<div class="">' +
          '	<select id="16_startdate' + currentNumber + '" name="16_startdate[]" class="form-control input-md" required="">' +
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
          '	<input id="16_startyear' + currentNumber + '" name="16_startyear[]" type="text" placeholder="Year" class="form-control input-md number datepickeryear" required="">' +
          '	</div>' +
          '	<div class="" id="16_ispresent1' + currentNumber + '">-</div>' +
          '	<div class="" id="16_ispresent2' + currentNumber + '">' +
          '	<select id="16_enddate' + currentNumber + '" name="16_enddate[]" class="form-control input-md">' +
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
          '	<input id="16_endyear' + currentNumber + '" name="16_endyear[]" type="text" placeholder="Year" class="form-control input-md number datepickeryear" >' +
          '	</div>' +
          '	<label class="form-check-label"><input type="hidden" name="16_workx[]" value="0"><input type="checkbox" id="16_work' + currentNumber + '" name="16_work[]" class="form-check-input" value="1" data-id="' + currentNumber + '">Kredensial ini tidak akan kedaluwarsa</label>' +
          '	</td>'


          /*
          + '<td class="col-md-2">'
          + '<input id="16_jam' + currentNumber + '" name="16_jam[]" type="text" placeholder="" class="form-control input-md "  required="">'
          + '</td>'
          + '<td class="col-md-2">'
          + '<input id="16_lic' + currentNumber + '" name="16_lic[]" type="text" placeholder="" class="form-control input-md "  required="">'
          + '</td>'
          + '<td class="col-md-2">'
          + '<input id="16_url' + currentNumber + '" name="16_url[]" type="text" placeholder="" class="form-control input-md "  required="">'
          + '</td>'
          */

          +
          '<td class="col-md-2">' +
          '<select id="16_tingkat' + currentNumber + '" name="16_tingkat[]" style="height: 34px;" required="">' +
          '<option value="">--Choose--</option>'
          <?php
          if (isset($bp_16_r)) {
            foreach ($bp_16_r as $val2) { ?> +
              '<option value="<?php echo $val2->value; ?>" ><?php echo $val2->desc; ?></option>'
          <?php
            }
          }
          ?> +
          '</select>' +
          '</td>' +
          '<td class="col-md-2">' +
          '<select id="16_jam' + currentNumber + '" name="16_jam[]" style="height: 34px;" required="">' +
          '<option value="">--Choose--</option>'
          <?php
          if (isset($bp_16_q)) {
            foreach ($bp_16_q as $val2) { ?> +
              '<option value="<?php echo $val2->value; ?>" ><?php echo $val2->desc; ?></option>'
          <?php
            }
          }
          ?> +
          '</select>' +
          '</td>' +
          '<td class="col-md-2" style="display:none;">' +
          '<select id="16_total' + currentNumber + '" name="16_total[]" style="height: 34px;" >' +
          '<option value="">--Choose--</option>'
          <?php
          if (isset($bp_16_p)) {
            foreach ($bp_16_p as $val2) { ?> +
              '<option value="<?php echo $val2->value; ?>" ><?php echo $val2->desc; ?></option>'
          <?php
            }
          }
          ?> +
          '</select>' +
          '</td>'

          +
          '<td class="col-md-2">' +
          '<textarea id="16_uraian' + currentNumber + '" name="16_uraian[]" class="form-control input-md " style="width:200px;" rows="8"></textarea>' +
          '</td>'

          +
          '	<td class="col-md-8">' +
          '		<div class="form-group">' +
          '			<div id="16_avatarattcert' + currentNumber + '">' +
          '			<input type="hidden" name="16_cert_image_url[]" value="" style="display: inline-block;">'

          +
          '			</div>' +
          '			<div id="16_errUploadattcert' + currentNumber + '"></div>' +
          '			<input type="file" name="16_attcert' + currentNumber + '" class="form-control input-md" ' +
          '			id="16_attcert' + currentNumber + '" onchange="upload_cert(\'attcert' + currentNumber + '\')">' +
          '		</div>' +
          '	</td>'

          +
          '<td class="col-md-2">'
          //+ '<textarea id="16_uraian' + currentNumber + '" name="16_uraian[]" class="form-control input-md " style="width:200px;" rows="8"></textarea>'

          +
          '<select id="16_komp' + currentNumber + '" name="16_komp[' + currentNumber + '][]" class="input-md" style="height: 200px;" required="" multiple>' +
          '<option value="">--Choose--</option>'
          <?php
          if (isset($m_act_16)) {
            $temp = true;
            foreach ($m_act_16 as $val) {
              if (strlen($val->value) < 8) {
                if (!$temp) { ?> +
                  '</optgroup>'
                <?php }  ?> +
                '<optgroup label="<?php echo $val->value . ' - ' . $val->title; ?>">'
              <?php $temp = true;
              } else {
                $temp = false; ?> +
                '<option value="<?php echo $val->value; ?>" ><?php echo $val->value . ' - ' . $val->title ?></option>'
          <?php }
            }
          }
          ?> +
          '</select>'

          +
          '</td>'

          /*
          + '<td class="col-md-4">'
          	+ '<table width="100%" id="'+tableID+'komp_' + currentNumber + '" class="table">'
          	+ '</table>'
          	+ '<div class="col-md-12" style="padding-bottom:20px;">'
          	+ '<button type="button" class="btn btn-primary" onclick="addKomp(\''+tableID+'komp_' + currentNumber + '\')">+ Tambah Kompetensi</button>'
          	+ '</div>'
          	
          + '</td>'
          */

          +
          '<td class="td-action">' +
          '<button type="button" class="btn btn-danger btn-xs ' + tableID + '-item-remove-button" data-id="' + currentNumber + '">' +
          '<i class="fa fa-trash-o fa-fw"></i>X' +
          '</button>' +
          '</td>' +
          '</tr>'
        );

      }

      function add21(tableID) {
        var currentNumber = 1;
        if ($('.' + tableID + '-item').length > 0) {
          currentNumber = $('.' + tableID + '-item').last().data('id') + 1
        }
        var seq = $('.' + tableID + '-item').length;
        seq = seq + 1;
        $('#t' + tableID).append(
          '<tr class=" ' + tableID + '-item" data-id="' + currentNumber + '" >' +
          '<td class="">' +
          seq +
          '</td>' +
          '<td class="col-md-2">' +
          '<input id="21_nama' + currentNumber + '" name="21_nama[]" type="text" placeholder="" class="form-control input-md "  required="">' +
          '</td>' +
          '<td class="col-md-2">' +
          '<input id="21_lembaga' + currentNumber + '" name="21_lembaga[]" type="text" placeholder="" class="form-control input-md "  required="">' +
          '</td>' +
          '<td class="col-md-2">' +
          '<input id="21_alamat' + currentNumber + '" name="21_alamat[]" type="text" placeholder="" class="form-control input-md "  required="">' +
          '</td>'

          +
          '<td class="col-md-2">' +
          '<input id="21_kota' + currentNumber + '" name="21_kota[]" type="text" placeholder="" class="form-control input-md "  required="">' +
          '</td>' +
          '<td class="col-md-2">' +
          '<input id="21_provinsi' + currentNumber + '" name="21_provinsi[]" type="text" placeholder="" class="form-control input-md "  required="">' +
          '</td>' +
          '<td class="col-md-2">' +
          '<input id="21_negara' + currentNumber + '" name="21_negara[]" type="text" placeholder="" class="form-control input-md "  required="">' +
          '</td>'

          +
          '<td class="col-md-2">' +
          '<input id="21_notelp' + currentNumber + '" name="21_notelp[]" type="text" placeholder="" style="height: 34px;"  required="">' +
          '</td>' +
          '<td class="col-md-2">' +
          '<input id="21_email' + currentNumber + '" name="21_email[]" type="text" placeholder="" style="height: 34px;"  required="">' +
          '</td>' +
          '<td class="col-md-2">'
          //+ '<input id="21_hubungan' + currentNumber + '" name="21_hubungan[]" type="text" placeholder="" style="height: 34px;"  required="">'
          +
          '	<select id="21_hubungan' + currentNumber + '" name="21_hubungan[]" style="height: 34px;">' +
          '		<option value="">---</option>' +
          '		<option value="Atasan">Atasan</option>' +
          '		<option value="Rekan Kerja">Rekan Kerja</option>' +
          '		<option value="Rekan seprofesi">Rekan seprofesi</option>' +
          '		<option value="Lain-lain">Lain-lain</option>' +
          '	</select>' +
          '</td>' +
          '<td class="td-action">' +
          '<button type="button" class="btn btn-danger btn-xs ' + tableID + '-item-remove-button" data-id="' + currentNumber + '">' +
          '<i class="fa fa-trash-o fa-fw"></i>X' +
          '</button>' +
          '</td>' +
          '</tr>'
        );

      }

      function add22(tableID) {
        var currentNumber = 1;
        if ($('.' + tableID + '-item').length > 0) {
          currentNumber = $('.' + tableID + '-item').last().data('id') + 1
        }
        var seq = $('.' + tableID + '-item').length;
        seq = seq + 1;
        $('#t' + tableID).append(
          '<tr class=" ' + tableID + '-item" data-id="' + currentNumber + '" >' +
          '<td class="">' +
          seq +
          '</td>' +
          '<td class="col-md-2">' +
          '<textarea id="22_uraian' + currentNumber + '" name="22_uraian[]" class="form-control input-md " style="width:200px;" rows="8"></textarea>' +
          '</td>' +
          '<td class="col-md-4">'
          /*
          	+ '<table width="100%" id="'+tableID+'komp_' + currentNumber + '" class="table">'
          	+ '</table>'
          	+ '<div class="col-md-12" style="padding-bottom:20px;">'
          	+ '<button type="button" class="btn btn-primary" onclick="addKomp(\''+tableID+'komp_' + currentNumber + '\')">+ Tambah Kompetensi</button>'
          	+ '</div>'
          */

          +
          '<select id="22_komp' + currentNumber + '" name="22_komp[' + currentNumber + '][]" class="input-md" style="height: 200px;" required="" multiple>' +
          '<option value="">--Choose--</option>'
          <?php
          if (isset($m_act_13)) {
            $temp = true;
            foreach ($m_act_13 as $val) {
              if (strlen($val->value) < 8) {
                if (!$temp) { ?> +
                  '</optgroup>'
                <?php }  ?> +
                '<optgroup label="<?php echo $val->value . ' - ' . $val->title; ?>">'
              <?php $temp = true;
              } else {
                $temp = false; ?> +
                '<option value="<?php echo $val->value; ?>" ><?php echo $val->value . ' - ' . $val->title ?></option>'
          <?php }
            }
          }
          ?> +
          '</select>'

          +
          '</td>' +
          '<td class="td-action">' +
          '<button type="button" class="btn btn-danger btn-xs ' + tableID + '-item-remove-button" data-id="' + currentNumber + '">' +
          '<i class="fa fa-trash-o fa-fw"></i>X' +
          '</button>' +
          '</td>' +
          '</tr>'
        );

      }

      function add3(tableID) {
        var currentNumber = 1;
        if ($('.' + tableID + '-item').length > 0) {
          currentNumber = $('.' + tableID + '-item').last().data('id') + 1
        }
        var seq = $('.' + tableID + '-item').length;
        seq = seq + 1;
        $('#t' + tableID).append(
          '<tr class=" ' + tableID + '-item" data-id="' + currentNumber + '" >' +
          '<td class="">' +
          seq +
          '</td>'


          +
          '<td class="col-md-2"><input id="3_t' + currentNumber + '" name="3_t[]" value=0 type="hidden">' +
          '	<div class="">' +
          '	<select id="3_startdate' + currentNumber + '" name="3_startdate[]" class="form-control input-md" required="">' +
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
          '	<input id="3_startyear' + currentNumber + '" name="3_startyear[]" type="text" placeholder="Year" class="form-control input-md number datepickeryear" required="">' +
          '	</div>' +
          '	<div class="" id="3_ispresent1' + currentNumber + '">-</div>' +
          '	<div class="" id="3_ispresent2' + currentNumber + '">' +
          '	<select id="3_enddate' + currentNumber + '" name="3_enddate[]" class="form-control input-md">' +
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
          '	<input id="3_endyear' + currentNumber + '" name="3_endyear[]" type="text" placeholder="Year" class="form-control input-md number datepickeryear" >' +
          '	</div>' +
          '	<label class="form-check-label"><input type="hidden" name="3_workx[]" value="0"><input type="checkbox" id="3_work' + currentNumber + '" name="3_work[]" class="form-check-input" value="1" data-id="' + currentNumber + '">Sampai saat ini</label>' +
          '	</td>'


          +
          '<td class="col-md-2">' +
          '<input id="3_instansi' + currentNumber + '" name="3_instansi[]" type="text" placeholder="Nama Instansi / Perusahaan" class="form-control input-md " style="width:200px;" required=""><input id="3_title' + currentNumber + '" name="3_title[]" type="text" placeholder="Jabatan/Tugas" class="form-control input-md " style="width:200px;" required="">' +
          '</td>' +
          '<td class="col-md-2">' +
          '<input id="3_namaproyek' + currentNumber + '" name="3_namaproyek[]" onchange="generate_option()" type="text" placeholder="Nama Aktifitas/Kegiatan/Proyek" class="form-control input-md " style="width:200px;" required="">' +
          '</td>' +
          '<td class="col-md-2">' +
          '<input id="3_pemberitugas' + currentNumber + '" name="3_pemberitugas[]" type="text" placeholder="Pemberi Tugas" class="form-control input-md " style="width:200px;" required="">' +
          '</td>'

          +
          '<td class="col-md-2">' +
          '<input id="3_location' + currentNumber + '" name="3_location[]" type="text" placeholder="Kabupaten/Kota" class="form-control input-md " style="width:200px;" required="">' +
          '<input id="3_provinsi' + currentNumber + '" name="3_provinsi[]" type="text" placeholder="Provinsi" class="form-control input-md " style="width:200px;" required="">' +
          '<input id="3_negara' + currentNumber + '" name="3_negara[]" type="text" placeholder="Negara" class="form-control input-md " style="width:200px;" required="">' +
          '</td>'



          +
          '<td class="col-md-2">' +
          '<select id="3_periode' + currentNumber + '" name="3_periode[]" style="height: 34px;" required="">' +
          '<option value="">--Choose--</option>'
          <?php
          if (isset($bp_3_p)) {
            foreach ($bp_3_p as $val2) { ?> +
              '<option value="<?php echo $val2->value; ?>" ><?php echo $val2->desc; ?></option>'
          <?php
            }
          }
          ?> +
          '</select>' +
          '</td>' +
          '<td class="col-md-2">' +
          '<select id="3_posisi' + currentNumber + '" name="3_posisi[]" style="height: 34px;" required="">' +
          '<option value="">--Choose--</option>'
          <?php
          if (isset($bp_3_q)) {
            foreach ($bp_3_q as $val2) { ?> +
              '<option value="<?php echo $val2->value; ?>" ><?php echo $val2->desc; ?></option>'
          <?php
            }
          }
          ?> +
          '</select>' +
          '</td>'

          +
          '<td class="col-md-2">' +
          '<input id="3_nilaipry' + currentNumber + '" name="3_nilaipry[]" type="text" placeholder="Nilai Proyek" class="form-control input-md " style="width:200px;" required="">' +
          '</td>' +
          '<td class="col-md-2">' +
          '<input id="3_nilaijasa' + currentNumber + '" name="3_nilaijasa[]" type="text" placeholder="Nilai Tanggungjawab" class="form-control input-md " style="width:200px;" required="">' +
          '</td>' +
          '<td class="col-md-2">' +
          '<select id="3_nilaisdm' + currentNumber + '" name="3_nilaisdm[]" style="height: 34px;" required="">' +
          '<option value="">--Choose--</option>' +
          '<option value="1">Sedikit</option>' +
          '<option value="2">Sedang</option>' +
          '<option value="3">Banyak</option>' +
          '<option value="4">Sangat Banyak</option>' +
          '</select>' +
          '</td>' +
          '<td class="col-md-2">' +
          '<select id="3_nilaisulit' + currentNumber + '" name="3_nilaisulit[]" style="height: 34px;" required="">' +
          '<option value="">--Choose--</option>' +
          '<option value="1">Rendah</option>' +
          '<option value="2">Sedang</option>' +
          '<option value="3">Tinggi</option>' +
          '<option value="4">Sangat Tinggi</option>' +
          '</select>' +
          '</td>'

          +
          '<td class="col-md-2">' +
          '<select id="3_nilaiproyek' + currentNumber + '" name="3_nilaiproyek[]" style="height: 34px;" required="">' +
          '<option value="">--Choose--</option>'
          <?php
          if (isset($bp_3_r)) {
            foreach ($bp_3_r as $val2) { ?> +
              '<option value="<?php echo $val2->value; ?>" ><?php echo $val2->desc; ?></option>'
          <?php
            }
          }
          ?> +
          '</select>' +
          '</td>'

          /*
          
          + '<td class="col-md-2">'
          + '<input id="3_posisi' + currentNumber + '" name="3_posisi[]" type="text" placeholder="" class="form-control input-md "  required="">'
          + '</td>'
          + '<td class="col-md-2">'
          + '<input id="3_nilaiproyek' + currentNumber + '" name="3_nilaiproyek[]" type="text" placeholder="" class="form-control input-md "  required="">'
          + '</td>'*/

          +
          '	<td class="col-md-8">' +
          '		<div class="form-group">' +
          '			<div id="3_avatarattexp' + currentNumber + '">' +
          '			<input type="hidden" name="3_exp_image_url[]" value="" style="display: inline-block;">'

          +
          '			</div>' +
          '			<div id="3_errUploadattexp' + currentNumber + '"></div>' +
          '			<input type="file" name="3_attexp' + currentNumber + '" class="form-control input-md" ' +
          '			id="3_attexp' + currentNumber + '" onchange="upload_exp(\'attexp' + currentNumber + '\')">' +
          '		</div>' +
          '	</td>'

          +
          '<td class="col-md-2">' +
          '<textarea id="3_uraian' + currentNumber + '" name="3_uraian[]" class="form-control input-md " style="width:200px;" rows="8"></textarea>' +
          '</td>'

          +
          '<td class="col-md-2">'
          //+ '<input id="3_uraian' + currentNumber + '" name="3_uraian[]" type="text" placeholder="" class="form-control input-md "  required="">'
          //+ '<textarea id="3_uraian' + currentNumber + '" name="3_uraian[]" class="form-control input-md " style="width:200px;" rows="8"></textarea>'


          +
          '<select id="3_komp' + currentNumber + '" name="3_komp[' + currentNumber + '][]" class="input-md" style="height: 200px;" required="" multiple>' +
          '<option value="">--Choose--</option>'
          <?php
          if (isset($m_act_3)) {
            $temp = true;
            foreach ($m_act_3 as $val) {
              if (strlen($val->value) < 8) {
                if (!$temp) { ?> +
                  '</optgroup>'
                <?php }  ?> +
                '<optgroup label="<?php echo $val->value . ' - ' . $val->title; ?>">'
              <?php $temp = true;
              } else {
                $temp = false; ?> +
                '<option value="<?php echo $val->value; ?>" ><?php echo $val->value . ' - ' . $val->title ?></option>'
          <?php }
            }
          }
          ?> +
          '</select>'

          +
          '</td>'
          /*
          + '<td class="col-md-4">'
          	
          	+ '<table width="100%" id="'+tableID+'komp_' + currentNumber + '" class="table">'
          	+ '</table>'
          	+ '<div class="col-md-12" style="padding-bottom:20px;">'
          	+ '<button type="button" class="btn btn-primary" onclick="addKomp(\''+tableID+'komp_' + currentNumber + '\')">+ Tambah Kompetensi</button>'
          	+ '</div>'
          	
          + '</td>'*/
          +
          '<td class="td-action">'

          +
          '<button type="button" class="btn btn-danger btn-xs ' + tableID + '-item-remove-button" data-id="' + currentNumber + '">' +
          '<i class="fa fa-trash-o fa-fw"></i>X' +
          '</button>' +
          '</td>' +
          '</tr>'
        );

      }

      function add4(tableID) {
        var currentNumber = 1;
        if ($('.' + tableID + '-item').length > 0) {
          currentNumber = $('.' + tableID + '-item').last().data('id') + 1
        }
        var seq = $('.' + tableID + '-item').length;
        seq = seq + 1;
        $('#t' + tableID).append(
          '<tr class=" ' + tableID + '-item" data-id="' + currentNumber + '" >' +
          '<td class="">' +
          seq +
          '</td>'

          +
          '<td class="col-md-2"><input id="4_t' + currentNumber + '" name="4_t[]" value=0 type="hidden">' +
          '<input id="4_instansi' + currentNumber + '" name="4_instansi[]" type="text" placeholder="" class="form-control input-md " style="width:200px;" required="">' +
          '</td>' +
          '<td class="col-md-2">' +
          '<input id="4_namaproyek' + currentNumber + '" name="4_namaproyek[]" type="text" placeholder="" class="form-control input-md " style="width:200px;" required="">' +
          '</td>'

          +
          '<td class="col-md-2">' +
          '<input id="4_location' + currentNumber + '" name="4_location[]" type="text" placeholder="" class="form-control input-md " style="width:200px;" required="">' +
          '</td>' +
          '<td class="col-md-2">' +
          '<input id="4_provinsi' + currentNumber + '" name="4_provinsi[]" type="text" placeholder="" class="form-control input-md " style="width:200px;" required="">' +
          '</td>' +
          '<td class="col-md-2">' +
          '<input id="4_negara' + currentNumber + '" name="4_negara[]" type="text" placeholder="" class="form-control input-md " style="width:200px;" required="">' +
          '</td>'


          +
          '<td class="col-md-2">' +
          '<select id="4_periode' + currentNumber + '" name="4_periode[]" style="height: 34px;" required="">' +
          '<option value="">--Choose--</option>'
          <?php
          if (isset($bp_4_p)) {
            foreach ($bp_4_p as $val2) { ?> +
              '<option value="<?php echo $val2->value; ?>" ><?php echo $val2->desc; ?></option>'
          <?php
            }
          }
          ?> +
          '</select>' +
          '</td>' +
          '<td class="col-md-2">' +
          '<select id="4_posisi' + currentNumber + '" name="4_posisi[]" style="height: 34px;" required="">' +
          '<option value="">--Choose--</option>'
          <?php
          if (isset($bp_4_q)) {
            foreach ($bp_4_q as $val2) { ?> +
              '<option value="<?php echo $val2->value; ?>" ><?php echo $val2->desc; ?></option>'
          <?php
            }
          }
          ?> +
          '</select>' +
          '</td>' +
          '<td class="col-md-2">' +
          '<select id="4_jumlahsks' + currentNumber + '" name="4_jumlahsks[]" style="height: 34px;" required="">' +
          '<option value="">--Choose--</option>'
          <?php
          if (isset($bp_4_r)) {
            foreach ($bp_4_r as $val2) { ?> +
              '<option value="<?php echo $val2->value; ?>" ><?php echo $val2->desc; ?></option>'
          <?php
            }
          }
          ?> +
          '</select>' +
          '</td>'

          +
          '	<td class="col-md-8">' +
          '		<div class="form-group">' +
          '			<div id="4_avatarattexp2' + currentNumber + '">' +
          '			<input type="hidden" name="4_exp2_image_url[]" value="" style="display: inline-block;">'

          +
          '			</div>' +
          '			<div id="4_errUploadattexp2' + currentNumber + '"></div>' +
          '			<input type="file" name="4_attexp2' + currentNumber + '" class="form-control input-md" ' +
          '			id="4_attexp2' + currentNumber + '" onchange="upload_exp2(\'attexp2' + currentNumber + '\')">' +
          '		</div>' +
          '	</td>'

          +
          '<td class="col-md-2">' +
          '<textarea id="4_uraian' + currentNumber + '" name="4_uraian[]" class="form-control input-md " style="width:200px;" rows="8"></textarea>' +
          '</td>'

          +
          '<td class="col-md-2">'


          +
          '<select id="4_komp' + currentNumber + '" name="4_komp[' + currentNumber + '][]" class="input-md" style="height: 200px;" required="" multiple>' +
          '<option value="">--Choose--</option>'
          <?php
          if (isset($m_act_4)) {
            $temp = true;
            foreach ($m_act_4 as $val) {
              if (strlen($val->value) < 8) {
                if (!$temp) { ?> +
                  '</optgroup>'
                <?php }  ?> +
                '<optgroup label="<?php echo $val->value . ' - ' . $val->title; ?>">'
              <?php $temp = true;
              } else {
                $temp = false; ?> +
                '<option value="<?php echo $val->value; ?>" ><?php echo $val->value . ' - ' . $val->title ?></option>'
          <?php }
            }
          }
          ?> +
          '</select>'

          +
          '</td>'

          +
          '<td class="td-action">'

          +
          '<button type="button" class="btn btn-danger btn-xs ' + tableID + '-item-remove-button" data-id="' + currentNumber + '">' +
          '<i class="fa fa-trash-o fa-fw"></i>X' +
          '</button>' +
          '</td>' +
          '</tr>'
        );

      }

      function add51(tableID) {
        var currentNumber = 1;
        if ($('.' + tableID + '-item').length > 0) {
          currentNumber = $('.' + tableID + '-item').last().data('id') + 1
        }
        var seq = $('.' + tableID + '-item').length;
        seq = seq + 1;
        $('#t' + tableID).append(
          '<tr class=" ' + tableID + '-item" data-id="' + currentNumber + '" >' +
          '<td class="">' +
          seq +
          '</td>'

          +
          '<td class="col-md-2"><input id="51_t' + currentNumber + '" name="51_t[]" value=0 type="hidden">' +
          '<input id="51_nama' + currentNumber + '" name="51_nama[]" type="text" placeholder="" class="form-control input-md "  required="">' +
          '</td>' +
          '<td class="col-md-2">' +
          '<input id="51_media' + currentNumber + '" name="51_media[]" type="text" placeholder="" class="form-control input-md "  required="">' +
          '</td>'

          +
          '<td class="col-md-2">' +
          '<input id="51_location' + currentNumber + '" name="51_location[]" type="text" placeholder="" class="form-control input-md "  required="">' +
          '</td>' +
          '<td class="col-md-2">' +
          '<input id="51_provinsi' + currentNumber + '" name="51_provinsi[]" type="text" placeholder="" class="form-control input-md "  required="">' +
          '</td>' +
          '<td class="col-md-2">' +
          '<input id="51_negara' + currentNumber + '" name="51_negara[]" type="text" placeholder="" class="form-control input-md "  required="">' +
          '</td>'

          +
          '<td class="col-md-2">' +
          '	<div class="">' +
          '	<select id="51_startdate' + currentNumber + '" name="51_startdate[]" class="form-control input-md" required="">' +
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
          '	<input id="51_startyear' + currentNumber + '" name="51_startyear[]" type="text" placeholder="Year" class="form-control input-md number datepickeryear" required="">' +
          '	</div>' +
          '	</td>'


          +
          '<td class="col-md-2">' +
          '<select id="51_tingkatmedia' + currentNumber + '" name="51_tingkatmedia[]" style="height: 34px;" required="">' +
          '<option value="">--Choose--</option>'
          <?php
          if (isset($bp_51_q)) {
            foreach ($bp_51_q as $val2) { ?> +
              '<option value="<?php echo $val2->value; ?>" ><?php echo $val2->desc; ?></option>'
          <?php
            }
          }
          ?> +
          '</select>' +
          '</td>'

          +
          '<td class="col-md-2" style="display:none;">' +
          '<select id="51_jumlah' + currentNumber + '" name="51_jumlah[]" style="height: 34px;">' +
          '<option value="">--Choose--</option>'
          <?php
          if (isset($bp_51_p)) {
            foreach ($bp_51_p as $val2) { ?> +
              '<option value="<?php echo $val2->value; ?>" ><?php echo $val2->desc; ?></option>'
          <?php
            }
          }
          ?> +
          '</select>' +
          '</td>'


          +
          '<td class="col-md-2">'
          //+ '<input id="51_uraian' + currentNumber + '" name="51_uraian[]" type="text" placeholder="" class="form-control input-md "  required="">'
          +
          '<textarea id="51_uraian' + currentNumber + '" name="51_uraian[]" class="form-control input-md " style="width:200px;" rows="8"></textarea>' +
          '</td>'

          +
          '<td class="col-md-2">' +
          '<select id="51_tingkat' + currentNumber + '" name="51_tingkat[]" style="height: 34px;" required="">' +
          '<option value="">--Choose--</option>'
          <?php
          if (isset($bp_51_r)) {
            foreach ($bp_51_r as $val2) { ?> +
              '<option value="<?php echo $val2->value; ?>" ><?php echo $val2->desc; ?></option>'
          <?php
            }
          }
          ?> +
          '</select>' +
          '</td>'




          /*+ '<td class="col-md-4">'
          	
          	+ '<table width="100%" id="'+tableID+'komp_' + currentNumber + '" class="table">'
          	+ '</table>'
          	+ '<div class="col-md-12" style="padding-bottom:20px;">'
          	+ '<button type="button" class="btn btn-primary" onclick="addKomp(\''+tableID+'komp_' + currentNumber + '\')">+ Tambah Kompetensi</button>'
          	+ '</div>'
          	
          + '</td>'*/

          +
          '	<td class="col-md-8">' +
          '		<div class="form-group">' +
          '			<div id="51_avatarattpublication1' + currentNumber + '">' +
          '			<input type="hidden" name="51_publication1_image_url[]" value="" style="display: inline-block;">'

          +
          '			</div>' +
          '			<div id="51_errUploadattpublication1' + currentNumber + '"></div>' +
          '			<input type="file" name="51_attpublication1' + currentNumber + '" class="form-control input-md" ' +
          '			id="51_attpublication1' + currentNumber + '" onchange="upload_publication1(\'attpublication1' + currentNumber + '\')">' +
          '		</div>' +
          '	</td>'

          +
          '<td class="col-md-2">'


          +
          '<select id="51_komp' + currentNumber + '" name="51_komp[' + currentNumber + '][]" class="input-md" style="height: 200px;" required="" multiple>' +
          '<option value="">--Choose--</option>'
          <?php
          if (isset($m_act_51)) {
            $temp = true;
            foreach ($m_act_51 as $val) {
              if (strlen($val->value) < 8) {
                if (!$temp) { ?> +
                  '</optgroup>'
                <?php }  ?> +
                '<optgroup label="<?php echo $val->value . ' - ' . $val->title; ?>">'
              <?php $temp = true;
              } else {
                $temp = false; ?> +
                '<option value="<?php echo $val->value; ?>" ><?php echo $val->value . ' - ' . $val->title ?></option>'
          <?php }
            }
          }
          ?> +
          '</select>'

          +
          '</td>'


          +
          '<td class="td-action">' +
          '<button type="button" class="btn btn-danger btn-xs ' + tableID + '-item-remove-button" data-id="' + currentNumber + '">' +
          '<i class="fa fa-trash-o fa-fw"></i>X' +
          '</button>' +
          '</td>' +
          '</tr>'
        );

      }

      function add52(tableID) {
        var currentNumber = 1;
        if ($('.' + tableID + '-item').length > 0) {
          currentNumber = $('.' + tableID + '-item').last().data('id') + 1
        }
        var seq = $('.' + tableID + '-item').length;
        seq = seq + 1;
        $('#t' + tableID).append(
          '<tr class=" ' + tableID + '-item" data-id="' + currentNumber + '" >' +
          '<td class="">' +
          seq +
          '</td>'

          +
          '<td class="col-md-2"><input id="52_t' + currentNumber + '" name="52_t[]" value=0 type="hidden">' +
          '<input id="52_judul' + currentNumber + '" name="52_judul[]" type="text" placeholder="" class="form-control input-md "  required="">' +
          '</td>' +
          '<td class="col-md-2">' +
          '<input id="52_nama' + currentNumber + '" name="52_nama[]" type="text" placeholder="" class="form-control input-md "  required="">' +
          '</td>' +
          '<td class="col-md-2">' +
          '<input id="52_penyelenggara' + currentNumber + '" name="52_penyelenggara[]" type="text" placeholder="" class="form-control input-md "  required="">' +
          '</td>' +
          '<td class="col-md-2">' +
          '<input id="52_location' + currentNumber + '" name="52_location[]" type="text" placeholder="" class="form-control input-md "  required="">' +
          '</td>' +
          '<td class="col-md-2">' +
          '<input id="52_provinsi' + currentNumber + '" name="52_provinsi[]" type="text" placeholder="" class="form-control input-md "  required="">' +
          '</td>' +
          '<td class="col-md-2">' +
          '<input id="52_negara' + currentNumber + '" name="52_negara[]" type="text" placeholder="" class="form-control input-md "  required="">' +
          '</td>'

          +
          '<td class="col-md-2">' +
          '	<div class="">' +
          '	<select id="52_startdate' + currentNumber + '" name="52_startdate[]" class="form-control input-md" required="">' +
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
          '	<input id="52_startyear' + currentNumber + '" name="52_startyear[]" type="text" placeholder="Year" class="form-control input-md number datepickeryear" required="">' +
          '	</div>' +
          '	</td>'



          +
          '<td class="col-md-2">' +
          '<select id="52_tingkatseminar' + currentNumber + '" name="52_tingkatseminar[]" style="height: 34px;" required="">' +
          '<option value="">--Choose--</option>'
          <?php
          if (isset($bp_52_q)) {
            foreach ($bp_52_q as $val2) { ?> +
              '<option value="<?php echo $val2->value; ?>" ><?php echo $val2->desc; ?></option>'
          <?php
            }
          }
          ?> +
          '</select>' +
          '</td>'

          +
          '<td class="col-md-2" style="display:none;">' +
          '<select id="52_jumlah' + currentNumber + '" name="52_jumlah[]" style="height: 34px;">' +
          '<option value="">--Choose--</option>'
          <?php
          if (isset($bp_52_p)) {
            foreach ($bp_52_p as $val2) { ?> +
              '<option value="<?php echo $val2->value; ?>" ><?php echo $val2->desc; ?></option>'
          <?php
            }
          }
          ?> +
          '</select>' +
          '</td>'


          +
          '<td class="col-md-2">'
          //+ '<input id="52_uraian' + currentNumber + '" name="52_uraian[]" type="text" placeholder="" class="form-control input-md "  required="">'
          +
          '<textarea id="52_uraian' + currentNumber + '" name="52_uraian[]" class="form-control input-md " style="width:200px;" rows="8"></textarea>' +
          '</td>'

          +
          '<td class="col-md-2">' +
          '<select id="52_tingkat' + currentNumber + '" name="52_tingkat[]" style="height: 34px;" required="">' +
          '<option value="">--Choose--</option>'
          <?php
          if (isset($bp_52_r)) {
            foreach ($bp_52_r as $val2) { ?> +
              '<option value="<?php echo $val2->value; ?>" ><?php echo $val2->desc; ?></option>'
          <?php
            }
          }
          ?> +
          '</select>' +
          '</td>'

          +
          '	<td class="col-md-8">' +
          '		<div class="form-group">' +
          '			<div id="52_avatarattpublication2' + currentNumber + '">' +
          '			<input type="hidden" name="52_publication2_image_url[]" value="" style="display: inline-block;">'

          +
          '			</div>' +
          '			<div id="52_errUploadattpublication2' + currentNumber + '"></div>' +
          '			<input type="file" name="52_attpublication2' + currentNumber + '" class="form-control input-md" ' +
          '			id="52_attpublication2' + currentNumber + '" onchange="upload_publication2(\'attpublication2' + currentNumber + '\')">' +
          '		</div>' +
          '	</td>'

          +
          '<td class="col-md-2">'


          +
          '<select id="52_komp' + currentNumber + '" name="52_komp[' + currentNumber + '][]" class="input-md" style="height: 200px;" required="" multiple>' +
          '<option value="">--Choose--</option>'
          <?php
          if (isset($m_act_51)) {
            $temp = true;
            foreach ($m_act_51 as $val) {
              if (strlen($val->value) < 8) {
                if (!$temp) { ?> +
                  '</optgroup>'
                <?php }  ?> +
                '<optgroup label="<?php echo $val->value . ' - ' . $val->title; ?>">'
              <?php $temp = true;
              } else {
                $temp = false; ?> +
                '<option value="<?php echo $val->value; ?>" ><?php echo $val->value . ' - ' . $val->title ?></option>'
          <?php }
            }
          }
          ?> +
          '</select>'

          +
          '</td>'


          /*+ '<td class="col-md-4">'
          	
          	+ '<table width="100%" id="'+tableID+'komp_' + currentNumber + '" class="table">'
          	+ '</table>'
          	+ '<div class="col-md-12" style="padding-bottom:20px;">'
          	+ '<button type="button" class="btn btn-primary" onclick="addKomp(\''+tableID+'komp_' + currentNumber + '\')">+ Tambah Kompetensi</button>'
          	+ '</div>'
          	
          + '</td>'
          */
          +
          '<td class="td-action">' +
          '<button type="button" class="btn btn-danger btn-xs ' + tableID + '-item-remove-button" data-id="' + currentNumber + '">' +
          '<i class="fa fa-trash-o fa-fw"></i>X' +
          '</button>' +
          '</td>' +
          '</tr>'
        );

      }

      function add53(tableID) {
        var currentNumber = 1;
        if ($('.' + tableID + '-item').length > 0) {
          currentNumber = $('.' + tableID + '-item').last().data('id') + 1
        }
        var seq = $('.' + tableID + '-item').length;
        seq = seq + 1;
        $('#t' + tableID).append(
          '<tr class=" ' + tableID + '-item" data-id="' + currentNumber + '" >' +
          '<td class="">' +
          seq +
          '</td>'

          +
          '<td class="col-md-2"><input id="53_t' + currentNumber + '" name="53_t[]" value=0 type="hidden">' +
          '<input id="53_nama' + currentNumber + '" name="53_nama[]" type="text" placeholder="" class="form-control input-md "  required="">' +
          '</td>' +
          '<td class="col-md-2">' +
          '<input id="53_penyelenggara' + currentNumber + '" name="53_penyelenggara[]" type="text" placeholder="" class="form-control input-md "  required="">' +
          '</td>' +
          '<td class="col-md-2">' +
          '<input id="53_location' + currentNumber + '" name="53_location[]" type="text" placeholder="" class="form-control input-md "  required="">' +
          '</td>' +
          '<td class="col-md-2">' +
          '<input id="53_provinsi' + currentNumber + '" name="53_provinsi[]" type="text" placeholder="" class="form-control input-md "  required="">' +
          '</td>' +
          '<td class="col-md-2">' +
          '<input id="53_negara' + currentNumber + '" name="53_negara[]" type="text" placeholder="" class="form-control input-md "  required="">' +
          '</td>'

          +
          '<td class="col-md-2">' +
          '	<div class="">' +
          '	<select id="53_startdate' + currentNumber + '" name="53_startdate[]" class="form-control input-md" required="">' +
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
          '	<input id="53_startyear' + currentNumber + '" name="53_startyear[]" type="text" placeholder="Year" class="form-control input-md number datepickeryear" required="">' +
          '	</div>' +
          '	</td>'




          +
          '<td class="col-md-2">' +
          '<select id="53_tingkatseminar' + currentNumber + '" name="53_tingkatseminar[]" style="height: 34px;" required="">' +
          '<option value="">--Choose--</option>'
          <?php
          if (isset($bp_53_q)) {
            foreach ($bp_53_q as $val2) { ?> +
              '<option value="<?php echo $val2->value; ?>" ><?php echo $val2->desc; ?></option>'
          <?php
            }
          }
          ?> +
          '</select>' +
          '</td>'

          +
          '<td class="col-md-2" style="display:none;">' +
          '<select id="53_jumlah' + currentNumber + '" name="53_jumlah[]" style="height: 34px;">' +
          '<option value="">--Choose--</option>'
          <?php
          if (isset($bp_53_p)) {
            foreach ($bp_53_p as $val2) { ?> +
              '<option value="<?php echo $val2->value; ?>" ><?php echo $val2->desc; ?></option>'
          <?php
            }
          }
          ?> +
          '</select>' +
          '</td>'


          +
          '<td class="col-md-2">'
          //+ '<input id="53_uraian' + currentNumber + '" name="53_uraian[]" type="text" placeholder="" class="form-control input-md "  required="">'
          +
          '<textarea id="53_uraian' + currentNumber + '" name="53_uraian[]" class="form-control input-md " style="width:200px;" rows="8"></textarea>' +
          '</td>'

          +
          '<td class="col-md-2">' +
          '<select id="53_tingkat' + currentNumber + '" name="53_tingkat[]" style="height: 34px;" required="">' +
          '<option value="">--Choose--</option>'
          <?php
          if (isset($bp_53_r)) {
            foreach ($bp_53_r as $val2) { ?> +
              '<option value="<?php echo $val2->value; ?>" ><?php echo $val2->desc; ?></option>'
          <?php
            }
          }
          ?> +
          '</select>' +
          '</td>'

          +
          '	<td class="col-md-8">' +
          '		<div class="form-group">' +
          '			<div id="53_avatarattpublication3' + currentNumber + '">' +
          '			<input type="hidden" name="53_publication3_image_url[]" value="" style="display: inline-block;">'

          +
          '			</div>' +
          '			<div id="53_errUploadattpublication3' + currentNumber + '"></div>' +
          '			<input type="file" name="53_attpublication3' + currentNumber + '" class="form-control input-md" ' +
          '			id="53_attpublication3' + currentNumber + '" onchange="upload_publication3(\'attpublication3' + currentNumber + '\')">' +
          '		</div>' +
          '	</td>'

          +
          '<td class="col-md-2">'


          +
          '<select id="53_komp' + currentNumber + '" name="53_komp[' + currentNumber + '][]" class="input-md" style="height: 200px;" required="" multiple>' +
          '<option value="">--Choose--</option>'
          <?php
          if (isset($m_act_53)) {
            $temp = true;
            foreach ($m_act_53 as $val) {
              if (strlen($val->value) < 8) {
                if (!$temp) { ?> +
                  '</optgroup>'
                <?php }  ?> +
                '<optgroup label="<?php echo $val->value . ' - ' . $val->title; ?>">'
              <?php $temp = true;
              } else {
                $temp = false; ?> +
                '<option value="<?php echo $val->value; ?>" ><?php echo $val->value . ' - ' . $val->title ?></option>'
          <?php }
            }
          }
          ?> +
          '</select>'

          +
          '</td>'



          /*+ '<td class="col-md-4">'
          	+ '<table width="100%" id="'+tableID+'komp_' + currentNumber + '" class="table">'
          	+ '</table>'
          	+ '<div class="col-md-12" style="padding-bottom:20px;">'
          	+ '<button type="button" class="btn btn-primary" onclick="addKomp(\''+tableID+'komp_' + currentNumber + '\')">+ Tambah Kompetensi</button>'
          	+ '</div>'
          	
          + '</td>'*/
          +
          '<td class="td-action">' +
          '<button type="button" class="btn btn-danger btn-xs ' + tableID + '-item-remove-button" data-id="' + currentNumber + '">' +
          '<i class="fa fa-trash-o fa-fw"></i>X' +
          '</button>' +
          '</td>' +
          '</tr>'
        );

      }

      function add54(tableID) {
        var currentNumber = 1;
        if ($('.' + tableID + '-item').length > 0) {
          currentNumber = $('.' + tableID + '-item').last().data('id') + 1
        }
        var seq = $('.' + tableID + '-item').length;
        seq = seq + 1;
        $('#t' + tableID).append(
          '<tr class=" ' + tableID + '-item" data-id="' + currentNumber + '" >' +
          '<td class="">' +
          seq +
          '</td>'

          +
          '<td class="col-md-2"><input id="54_t' + currentNumber + '" name="54_t[]" value=0 type="hidden">' +
          '<input id="54_nama' + currentNumber + '" name="54_nama[]" type="text" placeholder="" class="form-control input-md "  required="">' +
          '</td>'

          +
          '<td class="col-md-2">' +
          '	<div class="">' +
          '	<select id="54_startdate' + currentNumber + '" name="54_startdate[]" class="form-control input-md" required="">' +
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
          '	<input id="54_startyear' + currentNumber + '" name="54_startyear[]" type="text" placeholder="Year" class="form-control input-md number datepickeryear" required="">' +
          '	</div>' +
          '	</td>'

          +
          '<td class="col-md-2">' +
          '<input id="54_media_publikasi' + currentNumber + '" name="54_media_publikasi[]" type="text" placeholder="" class="form-control input-md "  required="">' +
          '</td>'






          +
          '<td class="col-md-2">' +
          '<select id="54_tingkatseminar' + currentNumber + '" name="54_tingkatseminar[]" style="height: 34px;" required="">' +
          '<option value="">--Choose--</option>'
          <?php
          if (isset($bp_54_q)) {
            foreach ($bp_54_q as $val2) { ?> +
              '<option value="<?php echo $val2->value; ?>" ><?php echo $val2->desc; ?></option>'
          <?php
            }
          }
          ?> +
          '</select>' +
          '</td>'

          +
          '<td class="col-md-2" style="display:none;">' +
          '<select id="54_jumlah' + currentNumber + '" name="54_jumlah[]" style="height: 34px;">' +
          '<option value="">--Choose--</option>'
          <?php
          if (isset($bp_54_p)) {
            foreach ($bp_54_p as $val2) { ?> +
              '<option value="<?php echo $val2->value; ?>" ><?php echo $val2->desc; ?></option>'
          <?php
            }
          }
          ?> +
          '</select>' +
          '</td>'


          +
          '<td class="col-md-2">'
          //+ '<input id="54_uraian' + currentNumber + '" name="54_uraian[]" type="text" placeholder="" class="form-control input-md "  required="">'
          +
          '<textarea id="54_uraian' + currentNumber + '" name="54_uraian[]" class="form-control input-md " style="width:200px;" rows="8"></textarea>' +
          '</td>'

          +
          '<td class="col-md-2">' +
          '<select id="54_tingkat' + currentNumber + '" name="54_tingkat[]" style="height: 34px;" required="">' +
          '<option value="">--Choose--</option>'
          <?php
          if (isset($bp_54_r)) {
            foreach ($bp_54_r as $val2) { ?> +
              '<option value="<?php echo $val2->value; ?>" ><?php echo $val2->desc; ?></option>'
          <?php
            }
          }
          ?> +
          '</select>' +
          '</td>'


          +
          '	<td class="col-md-8">' +
          '		<div class="form-group">' +
          '			<div id="54_avatarattpublication4' + currentNumber + '">' +
          '			<input type="hidden" name="54_publication4_image_url[]" value="" style="display: inline-block;">'

          +
          '			</div>' +
          '			<div id="54_errUploadattpublication4' + currentNumber + '"></div>' +
          '			<input type="file" name="54_attpublication4' + currentNumber + '" class="form-control input-md" ' +
          '			id="54_attpublication4' + currentNumber + '" onchange="upload_publication4(\'attpublication4' + currentNumber + '\')">' +
          '		</div>' +
          '	</td>'

          +
          '<td class="col-md-2">'


          +
          '<select id="54_komp' + currentNumber + '" name="54_komp[' + currentNumber + '][]" class="input-md" style="height: 200px;" required="" multiple>' +
          '<option value="">--Choose--</option>'
          <?php
          if (isset($m_act_54)) {
            $temp = true;
            foreach ($m_act_54 as $val) {
              if (strlen($val->value) < 8) {
                if (!$temp) { ?> +
                  '</optgroup>'
                <?php }  ?> +
                '<optgroup label="<?php echo $val->value . ' - ' . $val->title; ?>">'
              <?php $temp = true;
              } else {
                $temp = false; ?> +
                '<option value="<?php echo $val->value; ?>" ><?php echo $val->value . ' - ' . $val->title ?></option>'
          <?php }
            }
          }
          ?> +
          '</select>'

          +
          '</td>'





          /*+ '<td class="col-md-4">'
          	
          	+ '<table width="100%" id="'+tableID+'komp_' + currentNumber + '" class="table">'
          	+ '</table>'
          	+ '<div class="col-md-12" style="padding-bottom:20px;">'
          	+ '<button type="button" class="btn btn-primary" onclick="addKomp(\''+tableID+'komp_' + currentNumber + '\')">+ Tambah Kompetensi</button>'
          	+ '</div>'
          	
          + '</td>'*/

          +
          '<td class="td-action">' +
          '<button type="button" class="btn btn-danger btn-xs ' + tableID + '-item-remove-button" data-id="' + currentNumber + '">' +
          '<i class="fa fa-trash-o fa-fw"></i>X' +
          '</button>' +
          '</td>' +
          '</tr>'
        );

      }

      function add6(tableID) {
        var currentNumber = 1;
        if ($('.' + tableID + '-item').length > 0) {
          currentNumber = $('.' + tableID + '-item').last().data('id') + 1
        }
        var seq = $('.' + tableID + '-item').length;
        seq = seq + 1;
        $('#t' + tableID).append(
          '<tr class=" ' + tableID + '-item" data-id="' + currentNumber + '" >' +
          '<td class="">' +
          seq +
          '</td>' +
          '<td class="col-md-2"><input id="6_t' + currentNumber + '" name="6_t[]" value=0 type="hidden">' +
          '<input id="6_nama' + currentNumber + '" name="6_nama[]" type="text" placeholder="" class="form-control input-md "  required="">' +
          '</td>'

          /*+ '<td class="col-md-2">'
          + '<input id="6_uraian' + currentNumber + '" name="6_uraian[]" type="text" placeholder="" class="form-control input-md "  required="">'
          + '</td>'
          + '<td class="col-md-2">'
          + '<input id="6_nilai' + currentNumber + '" name="6_nilai[]" type="text" placeholder="" class="form-control input-md "  required="">'
          + '</td>'*/







          +
          '<td class="col-md-2">' +
          '<select id="6_jenisbahasa' + currentNumber + '" name="6_jenisbahasa[]" style="height: 34px;" required="">' +
          '<option value="">--Choose--</option>'
          <?php
          if (isset($bp_6_q)) {
            foreach ($bp_6_q as $val2) { ?> +
              '<option value="<?php echo $val2->value; ?>" ><?php echo $val2->desc; ?></option>'
          <?php
            }
          }
          ?> +
          '</select>' +
          '</td>'

          +
          '<td class="col-md-2">' +
          '<select id="6_verbal' + currentNumber + '" name="6_verbal[]" style="height: 34px;" required="">' +
          '<option value="">--Choose--</option>'
          <?php
          if (isset($bp_6_r)) {
            foreach ($bp_6_r as $val2) { ?> +
              '<option value="<?php echo $val2->value; ?>" ><?php echo $val2->desc; ?></option>'
          <?php
            }
          }
          ?> +
          '</select>' +
          '</td>'

          +
          '<td class="col-md-2">'
          //+ '<input id="6_verbal' + currentNumber + '" name="6_verbal[]" type="text" placeholder="" class="form-control input-md "  required="">'

          +
          '	<select id="6_jenistulisan' + currentNumber + '" name="6_jenistulisan[]" required="" style="height: 34px;">' +
          '		<option value="">---</option>' +
          '		<option value="Makalah">Makalah</option>' +
          '		<option value="Jurnal">Jurnal</option>' +
          '		<option value="Laporan">Laporan</option>' +
          '	</select>'

          +
          '</td>'

          +
          '<td class="col-md-2" style="display:none;">' +
          '<select id="6_jumlah' + currentNumber + '" name="6_jumlah[]" style="height: 34px;" required="">' +
          '<option value="">--Choose--</option>'
          <?php
          if (isset($bp_6_p)) {
            foreach ($bp_6_p as $val2) { ?> +
              '<option value="<?php echo $val2->value; ?>" ><?php echo $val2->desc; ?></option>'
          <?php
            }
          }
          ?> +
          '</select>' +
          '</td>'

          /*
          + '<td class="col-md-2">'
          //+ '<input id="6_uraian' + currentNumber + '" name="6_uraian[]" type="text" placeholder="" class="form-control input-md "  required="">'
          + '<textarea id="6_uraian' + currentNumber + '" name="6_uraian[]" class="form-control input-md " style="width:200px;" rows="8"></textarea>'
          + '</td>'*/

          +
          '	<td class="col-md-8">' +
          '		<div class="form-group">' +
          '			<div id="6_avatarattskill' + currentNumber + '">' +
          '			<input type="hidden" name="6_skill_image_url[]" value="" style="display: inline-block;">'

          +
          '			</div>' +
          '			<div id="6_errUploadattskill' + currentNumber + '"></div>' +
          '			<input type="file" name="6_attskill' + currentNumber + '" class="form-control input-md" ' +
          '			id="6_attskill' + currentNumber + '" onchange="upload_skill(\'attskill' + currentNumber + '\')">' +
          '		</div>' +
          '	</td>'


          +
          '<td class="col-md-2">'


          +
          '<select id="6_komp' + currentNumber + '" name="6_komp[' + currentNumber + '][]" class="input-md" style="height: 200px;" required="" multiple>' +
          '<option value="">--Choose--</option>'
          <?php
          if (isset($m_act_51)) {
            $temp = true;
            foreach ($m_act_51 as $val) {
              if (strlen($val->value) < 8) {
                if (!$temp) { ?> +
                  '</optgroup>'
                <?php }  ?> +
                '<optgroup label="<?php echo $val->value . ' - ' . $val->title; ?>">'
              <?php $temp = true;
              } else {
                $temp = false; ?> +
                '<option value="<?php echo $val->value; ?>" ><?php echo $val->value . ' - ' . $val->title ?></option>'
          <?php }
            }
          }
          ?> +
          '</select>'

          +
          '</td>'







          /*
          + '<td class="col-md-4">'
          	+ '<table width="100%" id="'+tableID+'komp_' + currentNumber + '" class="table">'
          	+ '</table>'
          	+ '<div class="col-md-12" style="padding-bottom:20px;">'
          	+ '<button type="button" class="btn btn-primary" onclick="addKomp(\''+tableID+'komp_' + currentNumber + '\')">+ Tambah Kompetensi</button>'
          	+ '</div>'
          	
          + '</td>'
          */


          +
          '<td class="td-action">' +
          '<button type="button" class="btn btn-danger btn-xs ' + tableID + '-item-remove-button" data-id="' + currentNumber + '">' +
          '<i class="fa fa-trash-o fa-fw"></i>X' +
          '</button>' +
          '</td>' +
          '</tr>'
        );

      }

      function addlam(tableID) {
        var currentNumber = 1;
        if ($('.' + tableID + '-item').length > 0) {
          currentNumber = $('.' + tableID + '-item').last().data('id') + 1
        }
        var seq = $('.' + tableID + '-item').length;
        seq = seq + 1;

        var arr = document.querySelectorAll("[name^='3_namaproyek']");
        var strOption = '<option>-- Pilih --</option>';
        $.each(arr, function(index, value) {
          var number = $(value).parent().parent().find('td:eq(0)').html();
          strOption = strOption + '<option value="' + number + '">' + number + ' - ' + $(value).val() + '</option>';
        });

        $('#t' + tableID).append(
          '<tr class=" ' + tableID + '-item" data-id="' + currentNumber + '" >' +
          '<td class="">' +
          seq +
          '</td>' + '<td class="col-md-2">'
          /*+ '	<select id="lam_verbal' + currentNumber + '" name="lam_verbal[]" class="form-control input-md" required="">'
          + '		<option value="">---</option>'
          + '		<option value="Aktif">Aktif</option>'
          + '		<option value="Pasif">Pasif</option>'
          + '	</select>'*/
          +
          '<select id="lam_aktifitas' + currentNumber + '" name="lam_aktifitas[]" style="height: 34px;" required="">' + strOption + '</select>' +
          '<td class="col-md-2">' +
          '<input id="lam_nama' + currentNumber + '" name="lam_nama[]" type="text" placeholder="" style="height: 34px;"  required="">' +
          '</td>' +
          '<td class="col-md-2">' +
          '<input id="lam_namaproyek' + currentNumber + '" name="lam_namaproyek[]" type="text" placeholder="" style="height: 34px;"  required="">' +
          '</td>' +
          '<td class="col-md-2">' +
          '<input id="lam_jangka' + currentNumber + '" name="lam_jangka[]" type="text" placeholder="" style="height: 34px;"  required="">' +
          '</td>' +
          '<td class="col-md-2">' +
          '<input id="lam_atasan' + currentNumber + '" name="lam_atasan[]" type="text" placeholder="" class="form-control input-md "  required="">' +
          '</td>'

          +
          '</td>' +
          '<td class="col-md-2">' +
          '<textarea id="lam_uraianproyek' + currentNumber + '" name="lam_uraianproyek[]" class="form-control input-md " style="width:200px;" rows="8"></textarea>' +
          '</td>' +
          '<td class="col-md-2">' +
          '<textarea id="lam_uraiantugas' + currentNumber + '" name="lam_uraiantugas[]" class="form-control input-md " style="width:200px;" rows="8"></textarea>' +
          '</td>' +
          '<td class="col-md-2">' +
          '<textarea id="lam_bagan' + currentNumber + '" name="lam_bagan[]" class="form-control input-md " style="width:200px;" rows="8"></textarea>' +
          '</td>'

          +
          '	<td class="col-md-8">' +
          '		<div class="form-group">' +
          '			<div id="lam_avatarattedu' + currentNumber + '">' +
          '			<input type="hidden" name="lam_edu_image_url[]" value="" style="display: inline-block;">'

          +
          '			</div>' +
          '			<div id="lam_errUploadattedu' + currentNumber + '"></div>' +
          '			<input type="file" name="lam_attedu' + currentNumber + '" class="form-control input-md" ' +
          '			id="lam_attedu' + currentNumber + '" onchange="upload_lam(\'attedu' + currentNumber + '\')">' +
          '		</div>' +
          '	</td>'

          +
          '<td class="td-action">' +
          '<button type="button" class="btn btn-danger btn-xs ' + tableID + '-item-remove-button" data-id="' + currentNumber + '">' +
          '<i class="fa fa-trash-o fa-fw"></i>X' +
          '</button>' +
          '</td>' +
          '</tr>'
        );

      }

      function addKomp(tableID) {
        var currentNumber = 1;
        if ($('.' + tableID + '-item').length > 0) {
          currentNumber = $('.' + tableID + '-item').last().data('id') + 1
        }

        var array = tableID.split("_");

        $('#' + tableID).append(
          '<tr class=" ' + tableID + '-item" data-id="' + currentNumber + '" >' +
          '<td class="col-md-4" id="orgkomp' + currentNumber + '">' +
          '<select id="' + tableID + currentNumber + '" name="' + array[0] + '[' + array[1] + '][]" class=" input-md" required="">' +
          '<option value="">--Choose--</option>'
          <?php
          if (isset($m_komp)) {
            foreach ($m_komp as $val) {
          ?> +
              '<option value="<?php echo $val->value; ?>" ><?php echo $val->value . ' - ' . $val->title; ?></option>'

          <?php
            }
          }
          ?> +
          '</select>'
          //<input type="hidden" name="addressid[]" id="addressid' + currentNumber + '" value="" />

          +
          '<button type="button" class="btn btn-danger btn-xs ' + tableID + '-item-remove-button" data-id="' + currentNumber + '" onclick="delKomp(\'' + currentNumber + '\',\'' + tableID + '\')">' +
          '<i class="fa fa-trash-o fa-fw"></i>X' +
          '</button>' +
          '</td>' +
          '</tr>'
        );

      }

      function delKomp(targetId, tableID) {
        var changeConfirmation = confirm("Really?");
        if (changeConfirmation) {
          //var targetId = $(this).data('id');
          //if(targetId!='1')
          //{
          $('.' + tableID + '-item[data-id="' + targetId + '"]').remove();
          //}


        } else {
          return false;
        }

      }

      function upload_edu(edu) {
        var formData = new FormData();
        formData.append('file', $('#12_' + edu)[0].files[0]);
        $.ajax({
          url: "<?php echo site_url('faip/edu_upload') ?>",
          type: 'POST',
          data: formData,
          processData: false,
          contentType: false,
          success: function(data, textStatus, jqXHR) {
            if (data.substring(0, 6) == '<input') {
              $('#12_avatar' + edu).html($(data).fadeIn('slow'));
              uploadFlag = 1;
              $('#12_errUpload' + edu).html($('').fadeIn('slow'));
            } else {
              $('#12_errUpload' + edu).html($(data).fadeIn('slow'));
              $('#12_avatar' + edu).html('');
              $('#12_' + edu).val('');
            }
          },
          error: function(jqXHR, textStatus, errorThrown) {
            $('#12_errUpload' + edu).html($(textStatus).fadeIn('slow'));
            $('#12_' + edu).val('');
          }
        });
        generate_lampiran();
      }

      function upload_org(org) {
        var formData = new FormData();
        formData.append('file', $('#13_' + org)[0].files[0]);
        $.ajax({
          url: "<?php echo site_url('faip/org_upload') ?>",
          type: 'POST',
          data: formData,
          processData: false,
          contentType: false,
          success: function(data, textStatus, jqXHR) {
            if (data.substring(0, 6) == '<input') {
              $('#13_avatar' + org).html($(data).fadeIn('slow'));
              uploadFlag = 1;
              $('#13_errUpload' + org).html($('').fadeIn('slow'));
            } else {
              $('#13_errUpload' + org).html($(data).fadeIn('slow'));
              $('#13_avatar' + org).html('');
              $('#13_' + org).val('');
            }
          },
          error: function(jqXHR, textStatus, errorThrown) {
            $('#13_errUpload' + org).html($(textStatus).fadeIn('slow'));
            $('#13_' + org).val('');
          }
        });
        generate_lampiran();
      }

      function upload_award(award) {
        var formData = new FormData();
        formData.append('file', $('#14_' + award)[0].files[0]);
        $.ajax({
          url: "<?php echo site_url('faip/award_upload') ?>",
          type: 'POST',
          data: formData,
          processData: false,
          contentType: false,
          success: function(data, textStatus, jqXHR) {
            if (data.substring(0, 6) == '<input') {
              $('#14_avatar' + award).html($(data).fadeIn('slow'));
              uploadFlag = 1;
              $('#14_errUpload' + award).html($('').fadeIn('slow'));
            } else {
              $('#14_errUpload' + award).html($(data).fadeIn('slow'));
              $('#14_avatar' + award).html('');
              $('#14_' + award).val('');
            }
          },
          error: function(jqXHR, textStatus, errorThrown) {
            $('#14_errUpload' + award).html($(textStatus).fadeIn('slow'));
            $('#14_' + award).val('');
          }
        });
        generate_lampiran();
      }

      function upload_course(course) {
        var formData = new FormData();
        formData.append('file', $('#15_' + course)[0].files[0]);
        $.ajax({
          url: "<?php echo site_url('faip/course_upload') ?>",
          type: 'POST',
          data: formData,
          processData: false,
          contentType: false,
          success: function(data, textStatus, jqXHR) {
            if (data.substring(0, 6) == '<input') {
              $('#15_avatar' + course).html($(data).fadeIn('slow'));
              uploadFlag = 1;
              $('#15_errUpload' + course).html($('').fadeIn('slow'));
            } else {
              $('#15_errUpload' + course).html($(data).fadeIn('slow'));
              $('#15_avatar' + course).html('');
              $('#15_' + course).val('');
            }
          },
          error: function(jqXHR, textStatus, errorThrown) {
            $('#15_errUpload' + course).html($(textStatus).fadeIn('slow'));
            $('#15_' + course).val('');
          }
        });
        generate_lampiran();
      }

      function upload_cert(cert) {
        var formData = new FormData();
        formData.append('file', $('#16_' + cert)[0].files[0]);
        $.ajax({
          url: "<?php echo site_url('faip/cert_upload') ?>",
          type: 'POST',
          data: formData,
          processData: false,
          contentType: false,
          success: function(data, textStatus, jqXHR) {
            if (data.substring(0, 6) == '<input') {
              $('#16_avatar' + cert).html($(data).fadeIn('slow'));
              uploadFlag = 1;
              $('#16_errUpload' + cert).html($('').fadeIn('slow'));
            } else {
              $('#16_errUpload' + cert).html($(data).fadeIn('slow'));
              $('#16_avatar' + cert).html('');
              $('#16_' + cert).val('');
            }
          },
          error: function(jqXHR, textStatus, errorThrown) {
            $('#16_errUpload' + cert).html($(textStatus).fadeIn('slow'));
            $('#16_' + cert).val('');
          }
        });
        generate_lampiran();
      }

      function upload_publication1(publication1) {
        var formData = new FormData();
        formData.append('file', $('#51_' + publication1)[0].files[0]);
        $.ajax({
          url: "<?php echo site_url('faip/publication1_upload') ?>",
          type: 'POST',
          data: formData,
          processData: false,
          contentType: false,
          success: function(data, textStatus, jqXHR) {
            if (data.substring(0, 6) == '<input') {
              $('#51_avatar' + publication1).html($(data).fadeIn('slow'));
              uploadFlag = 1;
              $('#51_errUpload' + publication1).html($('').fadeIn('slow'));
            } else {
              $('#51_errUpload' + publication1).html($(data).fadeIn('slow'));
              $('#51_avatar' + publication1).html('');
              $('#51_' + publication1).val('');
            }
          },
          error: function(jqXHR, textStatus, errorThrown) {
            $('#51_errUpload' + publication1).html($(textStatus).fadeIn('slow'));
            $('#51_' + publication1).val('');
          }
        });
        generate_lampiran();
      }

      function upload_publication2(publication2) {
        var formData = new FormData();
        formData.append('file', $('#52_' + publication2)[0].files[0]);
        $.ajax({
          url: "<?php echo site_url('faip/publication2_upload') ?>",
          type: 'POST',
          data: formData,
          processData: false,
          contentType: false,
          success: function(data, textStatus, jqXHR) {
            if (data.substring(0, 6) == '<input') {
              $('#52_avatar' + publication2).html($(data).fadeIn('slow'));
              uploadFlag = 1;
              $('#52_errUpload' + publication2).html($('').fadeIn('slow'));
            } else {
              $('#52_errUpload' + publication2).html($(data).fadeIn('slow'));
              $('#52_avatar' + publication2).html('');
              $('#52_' + publication2).val('');
            }
          },
          error: function(jqXHR, textStatus, errorThrown) {
            $('#52_errUpload' + publication2).html($(textStatus).fadeIn('slow'));
            $('#52_' + publication2).val('');
          }
        });
        generate_lampiran();
      }


      function upload_publication3(publication3) {
        var formData = new FormData();
        formData.append('file', $('#53_' + publication3)[0].files[0]);
        $.ajax({
          url: "<?php echo site_url('faip/publication3_upload') ?>",
          type: 'POST',
          data: formData,
          processData: false,
          contentType: false,
          success: function(data, textStatus, jqXHR) {
            if (data.substring(0, 6) == '<input') {
              $('#53_avatar' + publication3).html($(data).fadeIn('slow'));
              uploadFlag = 1;
              $('#53_errUpload' + publication3).html($('').fadeIn('slow'));
            } else {
              $('#53_errUpload' + publication3).html($(data).fadeIn('slow'));
              $('#53_avatar' + publication3).html('');
              $('#53_' + publication3).val('');
            }
          },
          error: function(jqXHR, textStatus, errorThrown) {
            $('#53_errUpload' + publication3).html($(textStatus).fadeIn('slow'));
            $('#53_' + publication3).val('');
          }
        });
        generate_lampiran();
      }

      function upload_publication4(publication4) {
        var formData = new FormData();
        formData.append('file', $('#54_' + publication4)[0].files[0]);
        $.ajax({
          url: "<?php echo site_url('faip/publication4_upload') ?>",
          type: 'POST',
          data: formData,
          processData: false,
          contentType: false,
          success: function(data, textStatus, jqXHR) {
            if (data.substring(0, 6) == '<input') {
              $('#54_avatar' + publication4).html($(data).fadeIn('slow'));
              uploadFlag = 1;
              $('#54_errUpload' + publication4).html($('').fadeIn('slow'));
            } else {
              $('#54_errUpload' + publication4).html($(data).fadeIn('slow'));
              $('#54_avatar' + publication4).html('');
              $('#54_' + publication4).val('');
            }
          },
          error: function(jqXHR, textStatus, errorThrown) {
            $('#54_errUpload' + publication4).html($(textStatus).fadeIn('slow'));
            $('#54_' + publication4).val('');
          }
        });
        generate_lampiran();
      }

      function upload_skill(skill) {
        var formData = new FormData();
        formData.append('file', $('#6_' + skill)[0].files[0]);
        $.ajax({
          url: "<?php echo site_url('faip/skill_upload') ?>",
          type: 'POST',
          data: formData,
          processData: false,
          contentType: false,
          success: function(data, textStatus, jqXHR) {
            if (data.substring(0, 6) == '<input') {
              $('#6_avatar' + skill).html($(data).fadeIn('slow'));
              uploadFlag = 1;
              $('#6_errUpload' + skill).html($('').fadeIn('slow'));
            } else {
              $('#6_errUpload' + skill).html($(data).fadeIn('slow'));
              $('#6_avatar' + skill).html('');
              $('#6_' + skill).val('');
            }
          },
          error: function(jqXHR, textStatus, errorThrown) {
            $('#6_errUpload' + skill).html($(textStatus).fadeIn('slow'));
            $('#6_' + skill).val('');
          }
        });
        generate_lampiran();
      }

      function upload_lam(edu) {
        var formData = new FormData();
        formData.append('file', $('#lam_' + edu)[0].files[0]);
        $.ajax({
          url: "<?php echo site_url('faip/lam_upload') ?>",
          type: 'POST',
          data: formData,
          processData: false,
          contentType: false,
          success: function(data, textStatus, jqXHR) {
            if (data.substring(0, 6) == '<input') {
              $('#lam_avatar' + edu).html($(data).fadeIn('slow'));
              uploadFlag = 1;
              $('#lam_errUpload' + edu).html($('').fadeIn('slow'));
            } else {
              $('#lam_errUpload' + edu).html($(data).fadeIn('slow'));
              $('#lam_avatar' + edu).html('');
              $('#lam_' + edu).val('');
            }
          },
          error: function(jqXHR, textStatus, errorThrown) {
            $('#lam_errUpload' + edu).html($(textStatus).fadeIn('slow'));
            $('#lam_' + edu).val('');
          }
        });
        generate_lampiran();
      }

      function upload_exp(exp) {
        var formData = new FormData();
        formData.append('file', $('#3_' + exp)[0].files[0]);
        $.ajax({
          url: "<?php echo site_url('faip/exp_upload') ?>",
          type: 'POST',
          data: formData,
          processData: false,
          contentType: false,
          success: function(data, textStatus, jqXHR) {
            if (data.substring(0, 6) == '<input') {
              $('#3_avatar' + exp).html($(data).fadeIn('slow'));
              uploadFlag = 1;
              $('#3_errUpload' + exp).html($('').fadeIn('slow'));
            } else {
              $('#3_errUpload' + exp).html($(data).fadeIn('slow'));
              $('#3_avatar' + exp).html('');
              $('#3_' + exp).val('');
            }
          },
          error: function(jqXHR, textStatus, errorThrown) {
            $('#3_errUpload' + exp).html($(textStatus).fadeIn('slow'));
            $('#3_' + exp).val('');
          }
        });
        generate_lampiran();
      }

      function upload_exp2(exp2) {
        var formData = new FormData();
        formData.append('file', $('#4_' + exp2)[0].files[0]);
        $.ajax({
          url: "<?php echo site_url('faip/exp2_upload') ?>",
          type: 'POST',
          data: formData,
          processData: false,
          contentType: false,
          success: function(data, textStatus, jqXHR) {
            if (data.substring(0, 6) == '<input') {
              $('#4_avatar' + exp2).html($(data).fadeIn('slow'));
              uploadFlag = 1;
              $('#4_errUpload' + exp2).html($('').fadeIn('slow'));
            } else {
              $('#4_errUpload' + exp2).html($(data).fadeIn('slow'));
              $('#4_avatar' + exp2).html('');
              $('#4_' + exp2).val('');
            }
          },
          error: function(jqXHR, textStatus, errorThrown) {
            $('#4_errUpload' + exp2).html($(textStatus).fadeIn('slow'));
            $('#4_' + exp2).val('');
          }
        });
        generate_lampiran();
      }

      function calc() {

        var w1 = 0;
        var w2 = 0;
        var w3 = 0;
        var w4 = 0;
        var p = 0;
        var total = 0;
        $("input[id*='_t']").each(function() {
          //var readonly = $(this).attr("readonly");
          //if(readonly && readonly.toLowerCase()!=='false') {
          if ($(this).attr('type') == 'hidden') {
            var input = $(this).val();
            if (input != 0) {
              var id = $(this).attr('id');
              console.log(id + ' : ' + input);
              //console.log(id.substr(0, 2));
              if (id.substr(0, 2) == "12") {
                w2 = w2 + parseInt(input);
              } else {
                id = id.replace("t", "komp");
                var comp = $('#' + id).val();
                //console.log($('#13_komp2').val());
                if (comp != null) {
                  $.each(comp, function(key, value) {
                    if (value != undefined) {
                      value = value.substr(0, 3);
                      if (value == "W.1")
                        w1 = w1 + parseInt(input);
                      else if (value == "W.2")
                        w2 = w2 + parseInt(input);
                      else if (value == "W.3")
                        w3 = w3 + parseInt(input);
                      else if (value == "W.4")
                        w4 = w4 + parseInt(input);
                      else
                        p = p + parseInt(input);
                    }
                  });
                }
              }
              //console.log(comp);
            }
          }
        });
        total = w1 + w2 + w3 + w4 + p;
        $('#wb1').html(w1);
        $('#wb2').html(w2);
        $('#wb3').html(w3);
        $('#wb4').html(w4);
        $('#pil').html(p);
        $('#jml').html(total);

        $('#hwb1').val(w1);
        $('#hwb2').val(w2);
        $('#hwb3').val(w3);
        $('#hwb4').val(w4);
        $('#hpil').val(p);
        $('#hjml').val(total);
        var kep = '';
        if (total >= 600 && total < 3000) {
          if (w1 >= 60 && w2 >= 180 && w3 >= 120 && w4 >= 60 && p >= 180)
            kep = "Memenuhi persyaratan untuk sertifikasi IPP";
          else kep = "Belum memenuhi persyaratan untuk sertifikasi IPP";
        } else if (total >= 3000 && total < 6000) {
          if (w1 >= 300 && w2 >= 900 && w3 >= 600 && w4 >= 300 && p >= 900)
            kep = "Memenuhi persyaratan untuk sertifikasi IPM";
          else if (w1 >= 60 && w2 >= 180 && w3 >= 120 && w4 >= 60 && p >= 180)
            kep = "Belum memenuhi persyaratan untuk sertifikasi IPM. Memenuhi persyaratan untuk sertifikasi IPP";
          else kep = "Belum memenuhi persyaratan untuk sertifikasi IPP";
        } else if (total >= 6000) {
          if (w1 >= 600 && w2 >= 1800 && w3 >= 1200 && w4 >= 600 && p >= 1800)
            kep = "Memenuhi persyaratan untuk IPU";
          else if (w1 >= 300 && w2 >= 900 && w3 >= 600 && w4 >= 300 && p >= 900)
            kep = "Belum memenuhi persyaratan untuk sertifikasi IPU. Memenuhi persyaratan untuk sertifikasi IPM";
          else if (w1 >= 60 && w2 >= 180 && w3 >= 120 && w4 >= 60 && p >= 180)
            kep = "Belum memenuhi persyaratan untuk sertifikasi IPU. Memenuhi persyaratan untuk sertifikasi IPP";
          else kep = "Belum memenuhi persyaratan untuk sertifikasi IPP";
        } else if (total < 600) {
          kep = "Belum memenuhi persyaratan untuk sertifikasi IPP";
        }

        $('#keputusan').html(kep);
        $('#hkeputusan').val(kep);
      }

      function setTime() {

        const monthNames = ["January", "February", "March", "April", "May", "June",
          "July", "August", "September", "October", "November", "December"
        ];
        let dateObj = new Date();
        let month = monthNames[dateObj.getMonth()];
        let day = String(dateObj.getDate()).padStart(2, '0');
        let year = dateObj.getFullYear();
        let output = day + '\n' + month + ' ' + year;

        //document.querySelector('.date').textContent = output;

        $('#lbl_pernyataan').html('tanggal ' + output + ' jam ' + time_format(dateObj));
        $('#wkt_pernyataan').val('tanggal ' + output + ' jam ' + time_format(dateObj));
      }

      function time_format(d) {
        hours = format_two_digits(d.getHours());
        minutes = format_two_digits(d.getMinutes());
        seconds = format_two_digits(d.getSeconds());
        return hours + ":" + minutes; //+ ":" + seconds;
      }

      function format_two_digits(n) {
        return n < 10 ? '0' + n : n;
      }

      function generate_lampiran() {

        $("#tlam2 > tbody").empty();
        var currentNumber = 0;

        var tmp_seq = 0;
        var tmp_name = '12';

        var tableID = 'lam2';
        $.each($("input[name*='_url[]']"), function(index, value) {
          console.log($(this).attr('name') + ": " + value.value);
          if (value.value != '') {
            var seq = currentNumber + 1;
            var name = $(this).attr('name');
            var res = name.split("_");
            var temp = '';
            var title = '';
            if (res[0] == '12') {
              temp = 'I.2';
              title = $(this).parent().parent().parent().parent().find('td:eq(1)').children().val();
              console.log(title);
            } else if (res[0] == '13') {
              temp = 'I.3';
              title = $(this).parent().parent().parent().parent().find('td:eq(1)').children().val();
              console.log(title);
            } else if (res[0] == '14') {
              temp = 'I.4';
              title = $(this).parent().parent().parent().parent().find('td:eq(1)').children().val();
              console.log(title);
            } else if (res[0] == '15') {
              temp = 'I.5';
              title = $(this).parent().parent().parent().parent().find('td:eq(1)').children().val();
              console.log(title);
            } else if (res[0] == '16') {
              temp = 'I.6';
              title = $(this).parent().parent().parent().parent().find('td:eq(1)').children().val();
              console.log(title);
            } else if (res[0] == '3') {
              temp = 'III';
              title = $(this).parent().parent().parent().parent().find('td:eq(2)').children().val();
            } else if (res[0] == '4') {
              temp = 'IV';
              title = $(this).parent().parent().parent().parent().find('td:eq(1)').children().val();
            } else if (res[0] == '51') {
              temp = 'V.1';
              title = $(this).parent().parent().parent().parent().find('td:eq(1)').children().val();
              console.log(title);
            } else if (res[0] == '52') {
              temp = 'V.2';
              title = $(this).parent().parent().parent().parent().find('td:eq(1)').children().val();
              console.log(title);
            } else if (res[0] == '53') {
              temp = 'V.3';
              title = $(this).parent().parent().parent().parent().find('td:eq(1)').children().val();
              console.log(title);
            } else if (res[0] == '54') {
              temp = 'V.4';
              title = $(this).parent().parent().parent().parent().find('td:eq(1)').children().val();
              console.log(title);
            } else if (res[0] == '6') {
              temp = 'VI';
              title = $(this).parent().parent().parent().parent().find('td:eq(1)').children().val();
              console.log(title);
            } else if (res[0] == 'lam') {
              temp = 'Lampiran I';
              title = $(this).parent().parent().parent().parent().find('td:eq(1)').children().val();
              console.log(title);
            }

            $("#tlam2 > tbody").append('<tr class=" ' + tableID + '-item" data-id="' + currentNumber + '" >' +
              '<td class="">' +
              seq +
              '</td>' + '<td class="col-md-2"><b>' +
              temp +
              '</b></td>' +
              '<td class="col-md-2">' +
              title +
              '</td>'


              +
              '	<td class="col-md-8">' +
              '<a href="<?php echo base_url(); ?>assets/uploads/' + value.value + '" target="_blank" class="ava_discus">' + value.value + '</a>' +
              '	</td>'

              +
              '</tr>');
            currentNumber++;
          }
        });
      }

      function generate_option() {
        var arrOption = document.querySelectorAll("[name^='3_namaproyek']");
        var arrSelect = document.querySelectorAll("[name^='lam_aktifitas']");

        $.each(arrSelect, function(index, value) {
          var temp = $(value).val();
          var strOption = '<option>-- Pilih --</option>';
          $.each(arrOption, function(index2, value2) {
            var number = $(value2).parent().parent().find('td:eq(0)').html();
            strOption = strOption + '<option value="' + number + '">' + number + ' - ' + $(value2).val() + '</option>';
          });
          $(value)
            .empty()
            .append(strOption);
          $(value).val(temp).change();
        });
      }

      function cek_perdana() {
        var faip_type = $('#faip_type').val();
        if (faip_type == '01') {
          $('#trfaip_type').show();
        } else
          $('#trfaip_type').hide();
      }


      $(document).ready(function() {
        calc();
        generate_lampiran();
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

        $("input[id^='exp_loc'],input[id^='addr_loc']").typeahead(null, {
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

        $("input[id^='12_title']").typeahead(null, {
          name: 'titles',
          display: 'name',
          source: titles,
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


        $("input[id^='12_kota'],input[id^='13_tempat'],input[id^='14_location'],input[id^='15_location'],input[id^='16_location'],input[id^='3_location'],input[id^='4_location'],input[id^='51_location'],input[id^='52_location'],input[id^='53_location']").typeahead(null, {
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
          id = id.replace("kota", "provinsi");
          id = id.replace("tempat", "provinsi");
          id = id.replace("location", "provinsi");
          var id2 = $(this).prop('id');
          id2 = id2.replace("kota", "negara");
          id2 = id2.replace("tempat", "negara");
          id2 = id2.replace("location", "negara");
          console.log(id);
          $.ajax({
            url: "<?php echo base_url(); ?>welcome/fxsearchprovince?q=" + text,
            success: function(result) {

              if (result != "false") {
                var x = JSON.parse(result);
                $('#' + id).val(x[0].name);
                $('#' + id2).val('Indonesia');
                //console.log($('#'+id));
              }
              //else updateTotalEstimatedChargeByFlag(text);
              //console.log(result);
            }
          });


        });

        $("input[id^='13_provinsi'],input[id^='14_provinsi'],input[id^='15_provinsi'],input[id^='16_provinsi'],input[id^='3_provinsi'],input[id^='4_provinsi'],input[id^='51_provinsi'],input[id^='52_provinsi'],input[id^='53_provinsi']").typeahead(null, {
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




        $(document).on('change', "input[id^='12_tahunlulus'],input[id^='12_score'],select[id^='12_degree']", function() {
          //alert($(this).attr('id'));

          var id = $(this).attr('id');
          id = id.replace("12_tahunlulus", "");
          id = id.replace("12_score", "");
          id = id.replace("12_degree", "");

          //console.log($('#12_tahunlulus'+id).val());
          //console.log($('#12_score'+id).val());
          var p = 0;
          if ($('#12_tahunlulus' + id).val() != '' && $('#12_score' + id).val() != '' && $('#12_degree' + id).val() != '') {
            var pp = 0;
            var qq = 0;
            var rr = 0;

            $.ajax({
              url: "<?php echo base_url(); ?>welcome/getbakuanpenilaian?q=12&t=" + $('#12_degree' + id).val(),
              success: function(result) {
                if (result != "false") {
                  var x = JSON.parse(result);

                  var d = new Date($('#12_tahunlulus' + id).val(), 01, 01);
                  var ageDifMs = Date.now() - d;
                  var ageDate = new Date(ageDifMs); // miliseconds from epoch

                  var years = Math.abs(ageDate.getUTCFullYear() - 1970);

                  //console.log( years );
                  $.each(x, function(key, value) {
                    var condition = value.formula.substr(0, 2);
                    if (condition == "<=") {
                      if (years <= value.formula.substr(2, 5)) pp = value.value;
                    } else if (condition == "<") {
                      if (years < value.formula.substr(2, 5)) pp = value.value;
                    } else if (condition == ">") {
                      if (years > value.formula.substr(2, 5)) pp = value.value;
                    } else if (condition == ">=") {
                      if (years >= value.formula.substr(2, 5)) pp = value.value;
                    } else if (condition == "=") {
                      if (years == value.formula.substr(2, 5)) pp = value.value;
                    }
                  });
                  //console.log( pp );

                  if ($('#12_score' + id).val() <= 3) {
                    qq = 2;
                    rr = 2;
                  }
                  if ($('#12_score' + id).val() > 3) {
                    qq = 3;
                    rr = 3;
                  }
                  //console.log( qq );
                  $('#12_t' + id).val(pp * qq * rr);
                  calc();
                } else {
                  $('#12_t' + id).val('0');
                  calc();
                }
              }
            });

          } else {
            $('#12_t' + id).val('0');
            calc();
          }


        });

        $(document).on('change', "input[id^='13_startyear'],input[id^='13_endyear'],select[id^='13_enddate'],select[id^='13_startdate'],select[id^='13_jabatan'],select[id^='13_tingkat']", function() {
          var id = $(this).attr('id');
          id = id.replace("13_enddate", "");
          id = id.replace("13_endyear", "");
          id = id.replace("13_jabatan", "");
          id = id.replace("13_tingkat", "");

          //console.log($('#12_tahunlulus'+id).val());
          //console.log($('#12_score'+id).val());
          var p = 0;
          if ($('#13_startyear' + id).val() != '' && $('#13_jabatan' + id).val() != '' && $('#13_tingkat' + id).val() != '') {
            var pp = 0;
            var qq = 0;
            var rr = 0;

            $.ajax({
              url: "<?php echo base_url(); ?>welcome/getbakuanpenilaian?q=13&t=Non PII",
              success: function(result) {
                if (result != "false") {
                  var x = JSON.parse(result);

                  var d = new Date($('#13_startyear' + id).val(), $('#13_startdate' + id).val(), 01);
                  var ageDifMs = Date.now() - d;
                  var ageDate = new Date(ageDifMs); // miliseconds from epoch

                  var years = Math.abs(ageDate.getUTCFullYear() - 1970);

                  //console.log( years );
                  $.each(x, function(key, value) {
                    var condition = value.formula.substr(0, 2);
                    if (condition == "<=") {
                      if (years <= value.formula.substr(2, 5)) pp = value.value;
                    } else if (condition == "<") {
                      if (years < value.formula.substr(2, 5)) pp = value.value;
                    } else if (condition == ">") {
                      if (years > value.formula.substr(2, 5)) pp = value.value;
                    } else if (condition == ">=") {
                      if (years >= value.formula.substr(2, 5)) pp = value.value;
                    } else if (condition == "=") {
                      if (years == value.formula.substr(2, 5)) pp = value.value;
                    }
                  });


                  if ($('#13_jabatan' + id).val() != 0) {
                    qq = $('#13_jabatan' + id).val();
                  }

                  if ($('#13_tingkat' + id).val() != 0) {
                    rr = $('#13_tingkat' + id).val();
                  }
                  /*console.log( id );
                  console.log( pp );
                  console.log( qq );
                  console.log( rr );*/
                  $('#13_t' + id).val(pp * qq * rr);
                  calc();
                } else {
                  $('#13_t' + id).val('0');
                  calc();
                }
              }
            });

          } else {
            $('#13_t' + id).val('0');
            calc();
          }

        });

        $(document).on('change', "select[id^='14_tingkat'],select[id^='14_tingkatlembaga']", function() {
          var id = $(this).attr('id');
          id = id.replace("14_tingkatlembaga", "");
          id = id.replace("14_tingkat", "");

          //console.log($('#12_tahunlulus'+id).val());
          //console.log($('#12_score'+id).val());
          var p = 0;
          if ($('#14_tingkatlembaga' + id).val() != '' && $('#14_tingkat' + id).val() != '') {
            var pp = 0;
            var qq = 0;
            var rr = 0;

            $.ajax({
              url: "<?php echo base_url(); ?>welcome/getbakuanpenilaian?q=14",
              success: function(result) {
                if (result != "false") {
                  var x = JSON.parse(result);

                  var years = $("select[id^='14_tingkat']");
                  years = years.length;

                  //console.log( years );
                  $.each(x, function(key, value) {
                    var condition = value.formula.substr(0, 2);
                    if (condition == "<=") {
                      if (years <= value.formula.substr(2, 5)) pp = value.value;
                    } else if (condition == "<") {
                      if (years < value.formula.substr(2, 5)) pp = value.value;
                    } else if (condition == ">") {
                      if (years > value.formula.substr(2, 5)) pp = value.value;
                    } else if (condition == ">=") {
                      if (years >= value.formula.substr(2, 5)) pp = value.value;
                    } else if (condition == "=") {
                      if (years == value.formula.substr(2, 5)) pp = value.value;
                    }
                  });
                  //console.log( pp );

                  if ($('#14_tingkat' + id).val() != 0) {
                    qq = $('#14_tingkat' + id).val();
                  }
                  if ($('#14_tingkatlembaga' + id).val() != 0) {
                    rr = $('#14_tingkatlembaga' + id).val();
                  }
                  //console.log( qq );
                  $('#14_t' + id).val(pp * qq * rr);
                  calc();
                } else {
                  $('#14_t' + id).val('0');
                  calc();
                }
              }
            });

          } else {
            $('#14_t' + id).val('0');
            calc();
          }

        });

        $(document).on('change', "select[id^='15_tingkat'],select[id^='15_jam']", function() {
          var id = $(this).attr('id');
          id = id.replace("15_jam", "");
          id = id.replace("15_tingkat", "");

          //console.log($('#12_tahunlulus'+id).val());
          //console.log($('#12_score'+id).val());
          var p = 0;
          if ($('#15_jam' + id).val() != '' && $('#15_tingkat' + id).val() != '') {
            var pp = 0;
            var qq = 0;
            var rr = 0;

            $.ajax({
              url: "<?php echo base_url(); ?>welcome/getbakuanpenilaian?q=15",
              success: function(result) {
                if (result != "false") {
                  var x = JSON.parse(result);

                  var years = $("select[id^='15_tingkat']");
                  years = years.length;

                  //console.log( years );
                  $.each(x, function(key, value) {
                    var condition = value.formula.substr(0, 2);
                    if (condition == "<=") {
                      if (years <= value.formula.substr(2, 5)) pp = value.value;
                    } else if (condition == "<") {
                      if (years < value.formula.substr(2, 5)) pp = value.value;
                    } else if (condition == ">") {
                      if (years > value.formula.substr(2, 5)) pp = value.value;
                    } else if (condition == ">=") {
                      if (years >= value.formula.substr(2, 5)) pp = value.value;
                    } else if (condition == "=") {
                      if (years == value.formula.substr(2, 5)) pp = value.value;
                    }
                  });
                  //console.log( pp );

                  if ($('#15_jam' + id).val() != 0) {
                    qq = $('#15_jam' + id).val();
                  }
                  if ($('#15_tingkat' + id).val() != 0) {
                    rr = $('#15_tingkat' + id).val();
                  }
                  //console.log( qq );
                  $('#15_t' + id).val(pp * qq * rr);
                  calc();
                } else {
                  $('#15_t' + id).val('0');
                  calc();
                }
              }
            });

          } else {
            $('#15_t' + id).val('0');
            calc();
          }

        });

        $(document).on('change', "select[id^='16_tingkat'],select[id^='16_jam']", function() {
          var id = $(this).attr('id');
          id = id.replace("16_jam", "");
          id = id.replace("16_tingkat", "");

          //console.log($('#12_tahunlulus'+id).val());
          //console.log($('#12_score'+id).val());
          var p = 0;
          if ($('#16_jam' + id).val() != '' && $('#16_tingkat' + id).val() != '') {
            var pp = 0;
            var qq = 0;
            var rr = 0;

            $.ajax({
              url: "<?php echo base_url(); ?>welcome/getbakuanpenilaian?q=16",
              success: function(result) {
                if (result != "false") {
                  var x = JSON.parse(result);

                  var years = $("select[id^='16_tingkat']");
                  years = years.length;

                  //console.log( years );
                  $.each(x, function(key, value) {
                    var condition = value.formula.substr(0, 2);
                    if (condition == "<=") {
                      if (years <= value.formula.substr(2, 5)) pp = value.value;
                    } else if (condition == "<") {
                      if (years < value.formula.substr(2, 5)) pp = value.value;
                    } else if (condition == ">") {
                      if (years > value.formula.substr(2, 5)) pp = value.value;
                    } else if (condition == ">=") {
                      if (years >= value.formula.substr(2, 5)) pp = value.value;
                    } else if (condition == "=") {
                      if (years == value.formula.substr(2, 5)) pp = value.value;
                    }
                  });
                  //console.log( pp );

                  if ($('#16_jam' + id).val() != 0) {
                    qq = $('#16_jam' + id).val();
                  }
                  if ($('#16_tingkat' + id).val() != 0) {
                    rr = $('#16_tingkat' + id).val();
                  }
                  //console.log( qq );
                  $('#16_t' + id).val(pp * qq * rr);
                  calc();
                } else {
                  $('#16_t' + id).val('0');
                  calc();
                }
              }
            });

          } else {
            $('#16_t' + id).val('0');
            calc();
          }

        });

        $(document).on('change', "select[id^='3_posisi'],select[id^='3_nilaiproyek'],select[id^='3_periode']", function() {
          var id = $(this).attr('id');
          id = id.replace("3_posisi", "");
          id = id.replace("3_nilaiproyek", "");
          id = id.replace("3_periode", "");

          //console.log($('#12_tahunlulus'+id).val());
          //console.log($('#12_score'+id).val());
          var p = 0;
          if ($('#3_posisi' + id).val() != '' && $('#3_nilaiproyek' + id).val() != '' && $('#3_periode' + id).val() != '') {
            var pp = 0;
            var qq = 0;
            var rr = 0;

            $.ajax({
              url: "<?php echo base_url(); ?>welcome/getbakuanpenilaian?q=3",
              success: function(result) {
                if (result != "false") {
                  var x = JSON.parse(result);

                  var years = $("select[id^='3_periode" + id + "']").val();
                  pp = years;
                  //years = years.length;

                  //console.log( years );
                  /*$.each( x, function( key, value ) {
                  	var condition = value.formula.substr(0,2);
                  	if(condition =="<=") {  
                  		if(years <= value.formula.substr(2,5)) pp=value.value;
                  	}
                  	else if(condition =="<") {  
                  		if(years < value.formula.substr(2,5)) pp=value.value;
                  	}
                  	else if(condition ==">") {  
                  		if(years > value.formula.substr(2,5)) pp=value.value;
                  	}
                  	else if(condition ==">=") {  
                  		if(years >= value.formula.substr(2,5)) pp=value.value;
                  	}
                  	else if(condition =="=") {  
                  		if(years == value.formula.substr(2,5)) pp=value.value;
                  	}
                  });*/

                  if ($('#3_posisi' + id).val() != 0) {
                    qq = $('#3_posisi' + id).val();
                  }
                  if ($('#3_nilaiproyek' + id).val() != 0) {
                    rr = $('#3_nilaiproyek' + id).val();
                  }
                  //console.log( qq );
                  $('#3_t' + id).val(pp * qq * rr);
                  calc();
                } else {
                  $('#3_t' + id).val('0');
                  calc();
                }
              }
            });

          } else {
            $('#3_t' + id).val('0');
            calc();
          }

        });

        $(document).on('change', "select[id^='4_posisi'],select[id^='4_jumlahsks'],select[id^='4_periode']", function() {
          var id = $(this).attr('id');
          id = id.replace("4_posisi", "");
          id = id.replace("4_jumlahsks", "");
          id = id.replace("4_periode", "");

          //console.log($('#12_tahunlulus'+id).val());
          //console.log($('#12_score'+id).val());
          var p = 0;
          if ($('#4_posisi' + id).val() != '' && $('#4_jumlahsks' + id).val() != '' && $('#4_periode' + id).val() != '') {
            var pp = 0;
            var qq = 0;
            var rr = 0;

            $.ajax({
              url: "<?php echo base_url(); ?>welcome/getbakuanpenilaian?q=4",
              success: function(result) {
                if (result != "false") {
                  var x = JSON.parse(result);

                  var years = $("select[id^='4_periode" + id + "']").val();
                  pp = years;
                  //years = years.length;

                  //console.log( years );
                  /*
                  $.each( x, function( key, value ) {
                  	var condition = value.formula.substr(0,2);
                  	if(condition =="<=") {  
                  		if(years <= value.formula.substr(2,5)) pp=value.value;
                  	}
                  	else if(condition =="<") {  
                  		if(years < value.formula.substr(2,5)) pp=value.value;
                  	}
                  	else if(condition ==">") {  
                  		if(years > value.formula.substr(2,5)) pp=value.value;
                  	}
                  	else if(condition ==">=") {  
                  		if(years >= value.formula.substr(2,5)) pp=value.value;
                  	}
                  	else if(condition =="=") {  
                  		if(years == value.formula.substr(2,5)) pp=value.value;
                  	}
                  });*/

                  if ($('#4_posisi' + id).val() != 0) {
                    qq = $('#4_posisi' + id).val();
                  }
                  if ($('#4_jumlahsks' + id).val() != 0) {
                    rr = $('#4_jumlahsks' + id).val();
                  }
                  //console.log( qq );
                  $('#4_t' + id).val(pp * qq * rr);
                  calc();
                } else {
                  $('#4_t' + id).val('0');
                  calc();
                }
              }
            });

          } else {
            $('#4_t' + id).val('0');
            calc();
          }

        });

        $(document).on('change', "select[id^='51_tingkatmedia'],select[id^='51_tingkat']", function() {
          var id = $(this).attr('id');
          id = id.replace("51_tingkatmedia", "");
          id = id.replace("51_tingkat", "");

          //console.log($('#12_tahunlulus'+id).val());
          //console.log($('#12_score'+id).val());
          var p = 0;
          if ($('#51_tingkatmedia' + id).val() != '' && $('#51_tingkat' + id).val() != '') {
            var pp = 0;
            var qq = 0;
            var rr = 0;

            $.ajax({
              url: "<?php echo base_url(); ?>welcome/getbakuanpenilaian?q=51",
              success: function(result) {
                if (result != "false") {
                  var x = JSON.parse(result);

                  var years = $("select[id^='51_tingkatmedia']");
                  years = years.length;

                  //console.log( years );
                  $.each(x, function(key, value) {
                    var condition = value.formula.substr(0, 2);
                    if (condition == "<=") {
                      if (years <= value.formula.substr(2, 5)) pp = value.value;
                    } else if (condition == "<") {
                      if (years < value.formula.substr(2, 5)) pp = value.value;
                    } else if (condition == ">") {
                      if (years > value.formula.substr(2, 5)) pp = value.value;
                    } else if (condition == ">=") {
                      if (years >= value.formula.substr(2, 5)) pp = value.value;
                    } else if (condition == "=") {
                      if (years == value.formula.substr(2, 5)) pp = value.value;
                    }
                  });

                  if ($('#51_tingkatmedia' + id).val() != 0) {
                    qq = $('#51_tingkatmedia' + id).val();
                  }
                  if ($('#51_tingkat' + id).val() != 0) {
                    rr = $('#51_tingkat' + id).val();
                  }
                  //console.log( qq );
                  $('#51_t' + id).val(pp * qq * rr);
                  calc();
                } else {
                  $('#51_t' + id).val('0');
                  calc();
                }
              }
            });

          } else {
            $('#51_t' + id).val('0');
            calc();
          }

        });

        $(document).on('change', "select[id^='52_tingkatseminar'],select[id^='52_tingkat']", function() {
          var id = $(this).attr('id');
          id = id.replace("52_tingkatseminar", "");
          id = id.replace("52_tingkat", "");

          //console.log($('#12_tahunlulus'+id).val());
          //console.log($('#12_score'+id).val());
          var p = 0;
          if ($('#52_tingkatseminar' + id).val() != '' && $('#52_tingkat' + id).val() != '') {
            var pp = 0;
            var qq = 0;
            var rr = 0;

            $.ajax({
              url: "<?php echo base_url(); ?>welcome/getbakuanpenilaian?q=52",
              success: function(result) {
                if (result != "false") {
                  var x = JSON.parse(result);

                  var years = $("select[id^='52_tingkatseminar']");
                  years = years.length;

                  //console.log( years );
                  $.each(x, function(key, value) {
                    var condition = value.formula.substr(0, 2);
                    if (condition == "<=") {
                      if (years <= value.formula.substr(2, 5)) pp = value.value;
                    } else if (condition == "<") {
                      if (years < value.formula.substr(2, 5)) pp = value.value;
                    } else if (condition == ">") {
                      if (years > value.formula.substr(2, 5)) pp = value.value;
                    } else if (condition == ">=") {
                      if (years >= value.formula.substr(2, 5)) pp = value.value;
                    } else if (condition == "=") {
                      if (years == value.formula.substr(2, 5)) pp = value.value;
                    }
                  });

                  if ($('#52_tingkatseminar' + id).val() != 0) {
                    qq = $('#52_tingkatseminar' + id).val();
                  }
                  if ($('#52_tingkat' + id).val() != 0) {
                    rr = $('#52_tingkat' + id).val();
                  }
                  //console.log( qq );
                  $('#52_t' + id).val(pp * qq * rr);
                  calc();
                } else {
                  $('#52_t' + id).val('0');
                  calc();
                }
              }
            });

          } else {
            $('#52_t' + id).val('0');
            calc();
          }

        });

        $(document).on('change', "select[id^='53_tingkatseminar'],select[id^='53_tingkat']", function() {
          var id = $(this).attr('id');
          id = id.replace("53_tingkatseminar", "");
          id = id.replace("53_tingkat", "");

          //console.log($('#12_tahunlulus'+id).val());
          //console.log($('#12_score'+id).val());
          var p = 0;
          if ($('#53_tingkatseminar' + id).val() != '' && $('#53_tingkat' + id).val() != '') {
            var pp = 0;
            var qq = 0;
            var rr = 0;

            $.ajax({
              url: "<?php echo base_url(); ?>welcome/getbakuanpenilaian?q=53",
              success: function(result) {
                if (result != "false") {
                  var x = JSON.parse(result);

                  var years = $("select[id^='53_tingkatseminar']");
                  years = years.length;

                  //console.log( years );
                  $.each(x, function(key, value) {
                    var condition = value.formula.substr(0, 2);
                    if (condition == "<=") {
                      if (years <= value.formula.substr(2, 5)) pp = value.value;
                    } else if (condition == "<") {
                      if (years < value.formula.substr(2, 5)) pp = value.value;
                    } else if (condition == ">") {
                      if (years > value.formula.substr(2, 5)) pp = value.value;
                    } else if (condition == ">=") {
                      if (years >= value.formula.substr(2, 5)) pp = value.value;
                    } else if (condition == "=") {
                      if (years == value.formula.substr(2, 5)) pp = value.value;
                    }
                  });

                  if ($('#53_tingkatseminar' + id).val() != 0) {
                    qq = $('#53_tingkatseminar' + id).val();
                  }
                  if ($('#53_tingkat' + id).val() != 0) {
                    rr = $('#53_tingkat' + id).val();
                  }
                  //console.log( qq );
                  $('#53_t' + id).val(pp * qq * rr);
                  calc();
                } else {
                  $('#53_t' + id).val('0');
                  calc();
                }
              }
            });

          } else {
            $('#53_t' + id).val('0');
            calc();
          }

        });

        $(document).on('change', "select[id^='54_tingkatseminar'],select[id^='54_tingkat']", function() {
          var id = $(this).attr('id');
          id = id.replace("54_tingkatseminar", "");
          id = id.replace("54_tingkat", "");

          //console.log($('#12_tahunlulus'+id).val());
          //console.log($('#12_score'+id).val());
          var p = 0;
          if ($('#54_tingkatseminar' + id).val() != '' && $('#54_tingkat' + id).val() != '') {
            var pp = 0;
            var qq = 0;
            var rr = 0;

            $.ajax({
              url: "<?php echo base_url(); ?>welcome/getbakuanpenilaian?q=54",
              success: function(result) {
                if (result != "false") {
                  var x = JSON.parse(result);

                  var years = $("select[id^='54_tingkatseminar']");
                  years = years.length;

                  //console.log( years );
                  $.each(x, function(key, value) {
                    var condition = value.formula.substr(0, 2);
                    if (condition == "<=") {
                      if (years <= value.formula.substr(2, 5)) pp = value.value;
                    } else if (condition == "<") {
                      if (years < value.formula.substr(2, 5)) pp = value.value;
                    } else if (condition == ">") {
                      if (years > value.formula.substr(2, 5)) pp = value.value;
                    } else if (condition == ">=") {
                      if (years >= value.formula.substr(2, 5)) pp = value.value;
                    } else if (condition == "=") {
                      if (years == value.formula.substr(2, 5)) pp = value.value;
                    }
                  });

                  if ($('#54_tingkatseminar' + id).val() != 0) {
                    qq = $('#54_tingkatseminar' + id).val();
                  }
                  if ($('#54_tingkat' + id).val() != 0) {
                    rr = $('#54_tingkat' + id).val();
                  }
                  //console.log( qq );
                  $('#54_t' + id).val(pp * qq * rr);
                  calc();
                } else {
                  $('#54_t' + id).val('0');
                  calc();
                }
              }
            });

          } else {
            $('#54_t' + id).val('0');
            calc();
          }

        });

        $(document).on('change', "select[id^='6_jenisbahasa'],select[id^='6_verbal']", function() {
          var id = $(this).attr('id');
          id = id.replace("6_jenisbahasa", "");
          id = id.replace("6_verbal", "");

          //console.log($('#12_tahunlulus'+id).val());
          //console.log($('#12_score'+id).val());
          var p = 0;
          if ($('#6_jenisbahasa' + id).val() != '' && $('#6_verbal' + id).val() != '') {
            var pp = 0;
            var qq = 0;
            var rr = 0;

            $.ajax({
              url: "<?php echo base_url(); ?>welcome/getbakuanpenilaian?q=6",
              success: function(result) {
                if (result != "false") {
                  var x = JSON.parse(result);

                  var years = $("select[id^='6_jenisbahasa']");
                  years = years.length;

                  //console.log( years );
                  $.each(x, function(key, value) {
                    var condition = value.formula.substr(0, 2);
                    if (condition == "<=") {
                      if (years <= value.formula.substr(2, 5)) pp = value.value;
                    } else if (condition == "<") {
                      if (years < value.formula.substr(2, 5)) pp = value.value;
                    } else if (condition == ">") {
                      if (years > value.formula.substr(2, 5)) pp = value.value;
                    } else if (condition == ">=") {
                      if (years >= value.formula.substr(2, 5)) pp = value.value;
                    } else if (condition == "=") {
                      if (years == value.formula.substr(2, 5)) pp = value.value;
                    }
                  });

                  if ($('#6_jenisbahasa' + id).val() != 0) {
                    qq = $('#6_jenisbahasa' + id).val();
                  }
                  if ($('#6_verbal' + id).val() != 0) {
                    rr = $('#6_verbal' + id).val();
                  }
                  //console.log( qq );
                  $('#6_t' + id).val(pp * qq * rr);
                  calc();
                } else {
                  $('#6_t' + id).val('0');
                  calc();
                }
              }
            });

          } else {
            $('#6_t' + id).val('0');
            calc();
          }

        });

        $(document).on('click', "select[id*='_komp']", function() {
          //alert('a');
          calc();
        });

        $(document).on('click', "input[id^='13_work']", function() {
          //alert($(this).attr('data-id'));
          //var a = document.getElementById("c_certwork").checked;

          var a = $(this).is(':checked');
          var targetId = $(this).attr('data-id');
          var temp = "";
          //if(targetId!=1)
          temp = targetId;

          if (a == true) {
            $('#13_ispresent1' + temp).css('display', 'none');
            $('#13_ispresent2' + temp).css('display', 'none');
          } else {
            $('#13_ispresent1' + temp).css('display', 'block');
            $('#13_ispresent2' + temp).css('display', 'block');
          }
        });

        $(document).on('click', "input[id^='3_work']", function() {
          //alert($(this).attr('data-id'));
          //var a = document.getElementById("c_certwork").checked;

          var a = $(this).is(':checked');
          var targetId = $(this).attr('data-id');
          var temp = "";
          //if(targetId!=1)
          temp = targetId;

          if (a == true) {
            $('#3_ispresent1' + temp).css('display', 'none');
            $('#3_ispresent2' + temp).css('display', 'none');
          } else {
            $('#3_ispresent1' + temp).css('display', 'block');
            $('#3_ispresent2' + temp).css('display', 'block');
          }
        });

        $(document).on('click', "input[id^='15_work']", function() {
          //alert($(this).attr('data-id'));
          //var a = document.getElementById("c_certwork").checked;

          var a = $(this).is(':checked');
          var targetId = $(this).attr('data-id');
          var temp = "";
          //if(targetId!=1)
          temp = targetId;

          if (a == true) {
            $('#15_ispresent1' + temp).css('display', 'none');
            $('#15_ispresent2' + temp).css('display', 'none');
          } else {
            $('#15_ispresent1' + temp).css('display', 'block');
            $('#15_ispresent2' + temp).css('display', 'block');
          }
        });
        $(document).on('click', "input[id^='16_work']", function() {
          //alert($(this).attr('data-id'));
          //var a = document.getElementById("c_certwork").checked;

          var a = $(this).is(':checked');
          var targetId = $(this).attr('data-id');
          var temp = "";
          //if(targetId!=1)
          temp = targetId;

          if (a == true) {
            $('#16_ispresent1' + temp).css('display', 'none');
            $('#16_ispresent2' + temp).css('display', 'none');
          } else {
            $('#16_ispresent1' + temp).css('display', 'block');
            $('#16_ispresent2' + temp).css('display', 'block');
          }
        });
        $(document).on('click', "input[id^='31_work']", function() {
          //alert($(this).attr('data-id'));
          //var a = document.getElementById("c_certwork").checked;

          var a = $(this).is(':checked');
          var targetId = $(this).attr('data-id');
          var temp = "";
          //if(targetId!=1)
          temp = targetId;

          if (a == true) {
            $('#31_ispresent1' + temp).css('display', 'none');
            $('#31_ispresent2' + temp).css('display', 'none');
          } else {
            $('#31_ispresent1' + temp).css('display', 'block');
            $('#31_ispresent2' + temp).css('display', 'block');
          }
        });
        $(document).on('click', "input[id^='32_work']", function() {
          //alert($(this).attr('data-id'));
          //var a = document.getElementById("c_certwork").checked;

          var a = $(this).is(':checked');
          var targetId = $(this).attr('data-id');
          var temp = "";
          //if(targetId!=1)
          temp = targetId;

          if (a == true) {
            $('#32_ispresent1' + temp).css('display', 'none');
            $('#32_ispresent2' + temp).css('display', 'none');
          } else {
            $('#32_ispresent1' + temp).css('display', 'block');
            $('#32_ispresent2' + temp).css('display', 'block');
          }
        });
        $(document).on('click', "input[id^='33_work']", function() {
          //alert($(this).attr('data-id'));
          //var a = document.getElementById("c_certwork").checked;

          var a = $(this).is(':checked');
          var targetId = $(this).attr('data-id');
          var temp = "";
          //if(targetId!=1)
          temp = targetId;

          if (a == true) {
            $('#33_ispresent1' + temp).css('display', 'none');
            $('#33_ispresent2' + temp).css('display', 'none');
          } else {
            $('#33_ispresent1' + temp).css('display', 'block');
            $('#33_ispresent2' + temp).css('display', 'block');
          }
        });
        $(document).on('click', "input[id^='34_work']", function() {
          //alert($(this).attr('data-id'));
          //var a = document.getElementById("c_certwork").checked;

          var a = $(this).is(':checked');
          var targetId = $(this).attr('data-id');
          var temp = "";
          //if(targetId!=1)
          temp = targetId;

          if (a == true) {
            $('#34_ispresent1' + temp).css('display', 'none');
            $('#34_ispresent2' + temp).css('display', 'none');
          } else {
            $('#34_ispresent1' + temp).css('display', 'block');
            $('#34_ispresent2' + temp).css('display', 'block');
          }
        });
        $(document).on('click', "input[id^='35_work']", function() {
          //alert($(this).attr('data-id'));
          //var a = document.getElementById("c_certwork").checked;

          var a = $(this).is(':checked');
          var targetId = $(this).attr('data-id');
          var temp = "";
          //if(targetId!=1)
          temp = targetId;

          if (a == true) {
            $('#35_ispresent1' + temp).css('display', 'none');
            $('#35_ispresent2' + temp).css('display', 'none');
          } else {
            $('#35_ispresent1' + temp).css('display', 'block');
            $('#35_ispresent2' + temp).css('display', 'block');
          }
        });


        $(document).on('click', '.org-item-remove-button', function() {
          var changeConfirmation = confirm("Really?");
          if (changeConfirmation) {
            var targetId = $(this).data('id');
            //if(targetId!='1')
            //{
            $('.org-item[data-id="' + targetId + '"]').remove();
            //}

            var i = 0;
            $('.org-item td:first-child').each(function() {
              $(this).text(i + 1);
              i++;
            });
            calc();
          } else {
            return false;
          }
        });
        $(document).on('click', '.phg-item-remove-button', function() {
          var changeConfirmation = confirm("Really?");
          if (changeConfirmation) {
            var targetId = $(this).data('id');
            //if(targetId!='1')
            //{
            $('.phg-item[data-id="' + targetId + '"]').remove();
            //}
            var i = 0;
            $('.phg-item td:first-child').each(function() {
              $(this).text(i + 1);
              i++;
            });
            calc();
          } else {
            return false;
          }
        });
        $(document).on('click', '.pdd-item-remove-button', function() {
          var changeConfirmation = confirm("Really?");
          if (changeConfirmation) {
            var targetId = $(this).data('id');
            //if(targetId!='1')
            //{
            $('.pdd-item[data-id="' + targetId + '"]').remove();
            //}
            var i = 0;
            $('.pdd-item td:first-child').each(function() {
              $(this).text(i + 1);
              i++;
            });
            calc();
          } else {
            return false;
          }
        });
        $(document).on('click', '.ppm-item-remove-button', function() {
          var changeConfirmation = confirm("Really?");
          if (changeConfirmation) {
            var targetId = $(this).data('id');
            //if(targetId!='1')
            //{
            $('.ppm-item[data-id="' + targetId + '"]').remove();
            //}
            var i = 0;
            $('.ppm-item td:first-child').each(function() {
              $(this).text(i + 1);
              i++;
            });
            calc();
          } else {
            return false;
          }
        });
        $(document).on('click', '.ref-item-remove-button', function() {
          var changeConfirmation = confirm("Really?");
          if (changeConfirmation) {
            var targetId = $(this).data('id');
            //if(targetId!='1')
            //{
            $('.ref-item[data-id="' + targetId + '"]').remove();
            //}
            var i = 0;
            $('.ref-item td:first-child').each(function() {
              $(this).text(i + 1);
              i++;
            });
            calc();
          } else {
            return false;
          }
        });
        $(document).on('click', '.eti-item-remove-button', function() {
          var changeConfirmation = confirm("Really?");
          if (changeConfirmation) {
            var targetId = $(this).data('id');
            //if(targetId!='1')
            //{
            $('.eti-item[data-id="' + targetId + '"]').remove();
            //}
            var i = 0;
            $('.eti-item td:first-child').each(function() {
              $(this).text(i + 1);
              i++;
            });
            calc();
          } else {
            return false;
          }
        });
        $(document).on('click', '.kup-item-remove-button', function() {
          var changeConfirmation = confirm("Really?");
          if (changeConfirmation) {
            var targetId = $(this).data('id');

            var seq = $('.kup-item[data-id="' + targetId + '"]').find('td:eq(0)').html();
            var arrSelect = document.querySelectorAll("[name^='lam_aktifitas']");
            $.each(arrSelect, function(index, value) {
              var temp = $(value).val();
              if (seq == temp) $(value).val('').change();
              else if (parseInt(temp) > parseInt(seq)) {
                $(value).val(temp - 1).change();
              }
            });

            //if(targetId!='1')
            //{
            $('.kup-item[data-id="' + targetId + '"]').remove();
            //}
            var i = 0;
            $('.kup-item td:first-child').each(function() {
              $(this).text(i + 1);
              i++;
            });
            calc();
            generate_option();
          } else {
            return false;
          }
        });
        $(document).on('click', '.man-item-remove-button', function() {
          var changeConfirmation = confirm("Really?");
          if (changeConfirmation) {
            var targetId = $(this).data('id');
            //if(targetId!='1')
            //{
            $('.man-item[data-id="' + targetId + '"]').remove();
            //}
            var i = 0;
            $('.man-item td:first-child').each(function() {
              $(this).text(i + 1);
              i++;
            });
            calc();
          } else {
            return false;
          }
        });
        $(document).on('click', '.mak-item-remove-button', function() {
          var changeConfirmation = confirm("Really?");
          if (changeConfirmation) {
            var targetId = $(this).data('id');
            //if(targetId!='1')
            //{
            $('.mak-item[data-id="' + targetId + '"]').remove();
            //}
            var i = 0;
            $('.mak-item td:first-child').each(function() {
              $(this).text(i + 1);
              i++;
            });
            calc();
          } else {
            return false;
          }
        });
        $(document).on('click', '.rek-item-remove-button', function() {
          var changeConfirmation = confirm("Really?");
          if (changeConfirmation) {
            var targetId = $(this).data('id');
            //if(targetId!='1')
            //{
            $('.rek-item[data-id="' + targetId + '"]').remove();
            //}
            var i = 0;
            $('.rek-item td:first-child').each(function() {
              $(this).text(i + 1);
              i++;
            });
            calc();
          } else {
            return false;
          }
        });
        $(document).on('click', '.ase-item-remove-button', function() {
          var changeConfirmation = confirm("Really?");
          if (changeConfirmation) {
            var targetId = $(this).data('id');
            //if(targetId!='1')
            //{
            $('.ase-item[data-id="' + targetId + '"]').remove();
            //}
            var i = 0;
            $('.ase-item td:first-child').each(function() {
              $(this).text(i + 1);
              i++;
            });
            calc();
          } else {
            return false;
          }
        });
        $(document).on('click', '.pub-item-remove-button', function() {
          var changeConfirmation = confirm("Really?");
          if (changeConfirmation) {
            var targetId = $(this).data('id');
            //if(targetId!='1')
            //{
            $('.pub-item[data-id="' + targetId + '"]').remove();
            //}
            var i = 0;
            $('.pub-item td:first-child').each(function() {
              $(this).text(i + 1);
              i++;
            });
            calc();
          } else {
            return false;
          }
        });
        $(document).on('click', '.lok-item-remove-button', function() {
          var changeConfirmation = confirm("Really?");
          if (changeConfirmation) {
            var targetId = $(this).data('id');
            //if(targetId!='1')
            //{
            $('.lok-item[data-id="' + targetId + '"]').remove();
            //}
            var i = 0;
            $('.lok-item td:first-child').each(function() {
              console.log(this);
              $(this).text(i + 1);
              i++;
            });
            calc();
          } else {
            return false;
          }
        });
        $(document).on('click', '.sem-item-remove-button', function() {
          var changeConfirmation = confirm("Really?");
          if (changeConfirmation) {
            var targetId = $(this).data('id');
            //if(targetId!='1')
            //{
            $('.sem-item[data-id="' + targetId + '"]').remove();
            //}
            var i = 0;
            $('.sem-item td:first-child').each(function() {
              $(this).text(i + 1);
              i++;
            });
            calc();
          } else {
            return false;
          }
        });
        $(document).on('click', '.ino-item-remove-button', function() {
          var changeConfirmation = confirm("Really?");
          if (changeConfirmation) {
            var targetId = $(this).data('id');
            //if(targetId!='1')
            //{
            $('.ino-item[data-id="' + targetId + '"]').remove();
            //}
            var i = 0;
            $('.ino-item td:first-child').each(function() {
              $(this).text(i + 1);
              i++;
            });
            calc();
          } else {
            return false;
          }
        });
        $(document).on('click', '.bah-item-remove-button', function() {
          var changeConfirmation = confirm("Really?");
          if (changeConfirmation) {
            var targetId = $(this).data('id');
            //if(targetId!='1')
            //{
            $('.bah-item[data-id="' + targetId + '"]').remove();
            //}
            var i = 0;
            $('.bah-item td:first-child').each(function() {
              $(this).text(i + 1);
              i++;
            });
            calc();
          } else {
            return false;
          }
        });
        $(document).on('click', '.edu-item-remove-button', function() {
          var changeConfirmation = confirm("Really?");
          if (changeConfirmation) {
            var targetId = $(this).data('id');
            //if(targetId!='1')
            //{
            $('.edu-item[data-id="' + targetId + '"]').remove();
            //}
            var i = 0;
            $('.edu-item td:first-child').each(function() {
              $(this).text(i + 1);
              i++;
            });
            calc();
          } else {
            return false;
          }
        });
        $(document).on('click', '.ala-item-remove-button', function() {
          var changeConfirmation = confirm("Really?");
          if (changeConfirmation) {
            var targetId = $(this).data('id');
            //if(targetId!='1')
            //{
            $('.ala-item[data-id="' + targetId + '"]').remove();
            //}
            var i = 0;
            $('.ala-item td:first-child').each(function() {
              //$(this).text(i+1);
              i++;
            });
            calc();
          } else {
            return false;
          }
        });
        $(document).on('click', '.wor-item-remove-button', function() {
          var changeConfirmation = confirm("Really?");
          if (changeConfirmation) {
            var targetId = $(this).data('id');
            //if(targetId!='1')
            //{
            $('.wor-item[data-id="' + targetId + '"]').remove();
            //}
            var i = 0;
            $('.wor-item td:first-child').each(function() {
              //$(this).text(i+1);
              i++;
            });
            calc();
          } else {
            return false;
          }
        });
        $(document).on('click', '.pho-item-remove-button', function() {
          var changeConfirmation = confirm("Really?");
          if (changeConfirmation) {
            var targetId = $(this).data('id');
            //if(targetId!='1')
            //{
            $('.pho-item[data-id="' + targetId + '"]').remove();
            //}
            var i = 0;
            $('.pho-item td:first-child').each(function() {
              //$(this).text(i+1);
              i++;
            });
            calc();
          } else {
            return false;
          }
        });
        $(document).on('click', '.lam-item-remove-button', function() {
          var changeConfirmation = confirm("Really?");
          if (changeConfirmation) {
            var targetId = $(this).data('id');
            //if(targetId!='1')
            //{
            $('.lam-item[data-id="' + targetId + '"]').remove();
            //}
            var i = 0;
            $('.lam-item td:first-child').each(function() {
              $(this).text(i + 1);
              i++;
            });
            calc();
          } else {
            return false;
          }
        });
        $(document).on('mousedown', 'select[multiple="multiple"]', function(e) {
          e.preventDefault();

          var select = this;
          var scroll = select.scrollTop;

          e.target.selected = !e.target.selected;

          setTimeout(function() {
            select.scrollTop = scroll;
          }, 0);

          $(select).focus();
        }).on('mousemove', 'select[multiple="multiple"]', function(e) {
          e.preventDefault();
        });

        /*$("select").mousedown(function(e){
        	e.preventDefault();

        	var select = this;
        	var scroll = select .scrollTop;

        	e.target.selected = !e.target.selected;

        	setTimeout(function(){select.scrollTop = scroll;}, 0);

        	$(select ).focus();
        }).mousemove(function(e){e.preventDefault()});
        
        $(document).on('mousedown', 'option', function(e){
        	e.preventDefault();
        	$(this).prop('selected', $(this).prop('selected') ? false : true);
        	return false;
        });
        */



      });
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

      .table {
        font-size: 13px;
      }

      .comp {
        color: blue;
      }

      .green {
        color: #099c29;
      }

      .one {
        color: #d8621c;
      }

      .two {
        color: #7e12cc;
      }

      .three {
        color: #cc1255;
      }

      .four {
        color: #0fa995;
      }
    </style>