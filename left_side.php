<?php

//Need to load helper since this view is not called by Controller but called by other view
get_instance()->load->helper('utility');

$segment = $this->uri->segment(3);

function activate($segment, $page)
{
  if ($page ==  $segment) {
    echo 'class="active"';
  };
}
?>
<aside class="left-side sidebar-offcanvas">
  <!-- sidebar: style can be found in sidebar.less -->
  <section class="sidebar">
    <!-- Sidebar user panel -->

    <ul class="sidebar-menu">
      <li <?php echo ($this->uri->segment(2) == "dashboard") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/dashboard'); ?>"><i class="fa fa-dashboard"></i> <span>Dashboard</span> </a> </li>

      <?php if ($this->session->userdata('is_approval') == "1" || $this->session->userdata('type') == "0") { ?>

        <li <?php echo ($this->uri->segment(3) == "approval") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/members/approval'); ?>"><i class="fa fa-angle-double-right"></i> <span>Approval</span> </a> </li>

      <?php } ?>

      <!-- ----------------------------------------------------------------------- User Type = 0		 
		 <?php if ($this->session->userdata('type') == "0") { ?>
      			<li <?php echo ($this->uri->segment(3) == "" && $this->uri->segment(2) == "members") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/members'); ?>"><i class="fa fa-angle-double-right"></i><span>All Members</span> </a> </li>
	  
	  		<li <?php echo ($this->uri->segment(3) == "non_kta") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/members/non_kta'); ?>"><i class="fa fa-angle-double-right"></i><span>REG Members</span> </a> </li>
	  
	  		<li <?php echo ($this->uri->segment(3) == "her_kta") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/members/her_kta'); ?>"><i class="fa fa-angle-double-right"></i><span>HER Members</span> </a> </li>
	  
	  		<li <?php echo ($this->uri->segment(3) == "report") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/members/report'); ?>"><i class="fa fa-angle-double-right"></i><span>Report Members</span> </a> </li>
	  
	  		<li <?php echo ($this->uri->segment(3) == "report_stri") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/members/report_stri'); ?>"><i class="fa fa-angle-double-right"></i><span>Report STRI</span> </a> </li>
	  
	  		<li <?php echo ($this->uri->segment(3) == "kolektif") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/members/kolektif'); ?>"><i class="fa fa-angle-double-right"></i><span>Kolektif</span> </a> </li>
	  
	  		<li <?php echo ($this->uri->segment(3) == "stri_member") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/members/stri_member'); ?>"><i class="fa fa-angle-double-right"></i><span>STRI Members</span> </a> </li>
	  
	  		<li <?php echo ($this->uri->segment(3) == "set_stri") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/members/set_stri'); ?>"><i class="fa fa-angle-double-right"></i><span>Set STRI</span> </a> </li>
	  
	  		<li <?php echo ($this->uri->segment(3) == "stri") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/members/stri'); ?>"><i class="fa fa-angle-double-right"></i><span>STRI </span> </a> </li>
	 		 <li <?php echo ($this->uri->segment(3) == "pi") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/members/pi'); ?>"><i class="fa fa-angle-double-right"></i><span>SKIP </span> </a> </li>
	    <!--<li <?php //echo ($this->uri->segment(3)=="majelis")?'class="active"':'';
              ?>> <a href="<?php //echo base_url('admin/members/majelis');
                            ?>"><i class="fa fa-angle-double-right"></i><span>Majelis </span> </a> </li>-->

      <li <?php echo ($this->uri->segment(3) == "faip") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/members/faip'); ?>"><i class="fa fa-angle-double-right"></i><span>FAIP </span> </a> </li>

      <?php

        if ($this->session->userdata('is_pkb') == "1") { ?>

        <li <?php echo ($this->uri->segment(3) == "pkb") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/members/pkb'); ?>"><i class="fa fa-angle-double-right"></i><span>PKB </span> </a> </li>

      <?php } ?>

      <li <?php echo ($this->uri->segment(3) == "bp") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/members/bp'); ?>"><i class="fa fa-angle-double-right"></i><span>Bakuan Penilaian </span> </a> </li>
      <li <?php echo ($this->uri->segment(3) == "m_bk_skip") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/members/m_bk_skip'); ?>"><i class="fa fa-angle-double-right"></i><span>Ketua BK </span> </a> </li>
      <li <?php echo ($this->uri->segment(3) == "finance") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/members/finance'); ?>"><i class="fa fa-angle-double-right"></i><span>Validasi</span> </a> </li>
      <li <?php echo ($this->uri->segment(3) == "va") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/members/va'); ?>"><i class="fa fa-angle-double-right"></i><span>VA </span> </a> </li>
    <?php } ?>
    <!-- --------------------------------------------------------------------------  User Type 9 ---------------------------------------------	-->
    <?php if ($this->session->userdata('type') == "9") { ?>
      <li <?php echo ($this->uri->segment(3) == "" && $this->uri->segment(2) == "members") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/members'); ?>"><i class="fa fa-angle-double-right"></i><span>All Members</span> </a> </li>

      <?php if ($this->session->userdata('admin_id') != "784" &&  $this->session->userdata('admin_id') != "781") { ?>
        <li <?php echo ($this->uri->segment(3) == "non_kta") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/members/non_kta'); ?>"><i class="fa fa-angle-double-right"></i><span>REG Members</span> </a> </li>
        <li <?php echo ($this->uri->segment(3) == "her_kta") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/members/her_kta'); ?>"><i class="fa fa-angle-double-right"></i><span>HER Members</span> </a> </li>
        <?php if ($this->session->userdata('admin_id') == "780" || $this->session->userdata('admin_id') == "783" || $this->session->userdata('admin_id') == "782") { ?>
          <li <?php echo ($this->uri->segment(3) == "va") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/members/va'); ?>"><i class="fa fa-angle-double-right"></i><span>VA </span> </a> </li>

        <?php } ?>
        <?php if ($this->session->userdata('admin_id') == "780" || $this->session->userdata('admin_id') == "782") { ?>
          <li <?php echo ($this->uri->segment(3) == "finance") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/members/finance'); ?>"><i class="fa fa-angle-double-right"></i><span>Validasi</span> </a> </li>
        <?php } ?>
        <?php if ($this->session->userdata('admin_id') == "731"  || $this->session->userdata('admin_id') == "782") { ?>
          <li <?php echo ($this->uri->segment(3) == "report_stri") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/members/report_stri'); ?>"><i class="fa fa-angle-double-right"></i><span>Report STRI</span> </a> </li>
          <!-- 		<li <?php echo ($this->uri->segment(3) == "lapkeu") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/lapkeu/index/1'); ?>"><i class="fa fa-angle-double-right"></i><span>Laporan V1</span> </a> </li> -->
          <li <?php echo ($this->uri->segment(3) == "lapkeu") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/lapkeu/index/2'); ?>"><i class="fa fa-angle-double-right"></i><span>Laporan V2</span> </a> </li>
          <li <?php echo ($this->uri->segment(3) == "lapkeu") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/lapkeu/index/4'); ?>"><i class="fa fa-angle-double-right"></i><span>Laporan Kirim</span> </a> </li>

    <?php }
      }
    }    ?>
    <!-- -------------------------------------------------------------------------------------------------------------------------- -->
    <?php if ($this->session->userdata('type') == "10" ||  $this->session->userdata('admin_id') == "673") { ?>
      <li <?php echo ($this->uri->segment(3) == "" && $this->uri->segment(2) == "members") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/members'); ?>"><i class="fa fa-angle-double-right"></i><span>All Members</span> </a> </li>
      <li <?php echo ($this->uri->segment(3) == "pi") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/members/pi'); ?>"><i class="fa fa-angle-double-right"></i><span>SKIP </span> </a> </li>
    <?php } ?>


    <?php if ($this->session->userdata('admin_id') == "675") { ?>
      <li <?php echo ($this->uri->segment(3) == "" && $this->uri->segment(2) == "members") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/members'); ?>"><i class="fa fa-angle-double-right"></i><span>All Members</span> </a> </li>
    <?php } ?>

    <!-- ------------------------------------------------------------------------- User TYpe 2 ------------------------------ -->
    <?php if ($this->session->userdata('type') == "2") { ?>
      <li <?php echo ($this->uri->segment(3) == "" && $this->uri->segment(2) == "members") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/members'); ?>"><i class="fa fa-angle-double-right"></i><span>All Members</span> </a> </li>
      <li <?php echo ($this->uri->segment(3) == "non_kta") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/members/non_kta'); ?>"><i class="fa fa-angle-double-right"></i><span>REG Members</span> </a> </li>

      <li <?php echo ($this->uri->segment(3) == "her_kta") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/members/her_kta'); ?>"><i class="fa fa-angle-double-right"></i><span>HER Members</span> </a> </li>

      <li <?php echo ($this->uri->segment(3) == "report") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/members/report'); ?>"><i class="fa fa-angle-double-right"></i><span>Report Members</span> </a> </li>

      <li <?php echo ($this->uri->segment(3) == "kolektif") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/members/kolektif'); ?>"><i class="fa fa-angle-double-right"></i><span>Kolektif</span> </a> </li>

      <li <?php echo ($this->uri->segment(3) == "stri_member") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/members/stri_member'); ?>"><i class="fa fa-angle-double-right"></i><span>STRI Members</span> </a> </li>

      <li <?php echo ($this->uri->segment(3) == "stri") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/members/stri'); ?>"><i class="fa fa-angle-double-right"></i><span>STRI </span> </a> </li>

      <li <?php echo ($this->uri->segment(3) == "va") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/members/va'); ?>"><i class="fa fa-angle-double-right"></i><span>VA </span> </a> </li>

    <?php } ?>
    <!-- ----------------------------------------------------------------------------------------- End Of User Type 2 ---------------------------------- -->
    <!-- ----------------------------------------------------------------- User Type = 1 ---------------------------------------------- -->

    <?php if ($this->session->userdata('type') == "1") { ?>
      <li <?php echo ($this->uri->segment(3) == "" && $this->uri->segment(2) == "members") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/members'); ?>"><i class="fa fa-angle-double-right"></i><span>All Members</span> </a> </li>

      <li <?php echo ($this->uri->segment(3) == "faip") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/members/faip'); ?>"><i class="fa fa-angle-double-right"></i><span>FAIP </span> </a> </li>
      <?php
      if ($this->session->userdata('admin_id') == "672" ||  $this->session->userdata('admin_id') == "731" ||  $this->session->userdata('admin_id') == "659" ||  $this->session->userdata('admin_id') == "707") {
      ?>
        <li <?php echo ($this->uri->segment(3) == "faip_return") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/members/faip_return'); ?>"><i class="fa fa-angle-double-right"></i><span>FAIP RETURNED </span> </a> </li>
      <?php  }
      ?>


      <?php if ($this->session->userdata('is_pkb') == "1") { ?>

        <li <?php echo ($this->uri->segment(3) == "pkb") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/members/pkb'); ?>"><i class="fa fa-angle-double-right"></i><span>PKB </span> </a> </li>

      <?php } ?>

      <?php /* ?>
		<li <?php echo ($this->uri->segment(3)=="bp")?'class="active"':'';?>> <a href="<?php echo base_url('admin/members/bp');?>"><i class="fa fa-angle-double-right"></i><span>Bakuan Penilaian </span> </a> </li>
		<li <?php echo ($this->uri->segment(3)=="majelis")?'class="active"':'';?>> <a href="<?php echo base_url('admin/members/majelis');?>"><i class="fa fa-angle-double-right"></i><span>Majelis </span> </a> </li>
		<?php */ ?>

      <li <?php echo ($this->uri->segment(3) == "pi") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/members/pi'); ?>"><i class="fa fa-angle-double-right"></i><span>SKIP </span> </a> </li>

      <?php
      if ($this->session->userdata('admin_id') == "670" || $this->session->userdata('admin_id') == "659" || $this->session->userdata('admin_id') == "731") {
      ?>
        <li <?php echo ($this->uri->segment(3) == "stri_member") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/members/stri_member'); ?>"><i class="fa fa-angle-double-right"></i><span>STRI Members</span> </a> </li>
        <li <?php echo ($this->uri->segment(3) == "stri") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/members/stri'); ?>"><i class="fa fa-angle-double-right"></i><span>STRI </span> </a> </li>

      <?php } ?>
      <?php
      if ($this->session->userdata('admin_id') == "670") { ?>
        <li <?php echo ($this->uri->segment(3) == "stri") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/members/stri'); ?>"><i class="fa fa-angle-double-right"></i><span>STRI </span> </a> </li>
      <?php } ?>
      <?php if ($this->session->userdata('admin_id') == "672" || $this->session->userdata('admin_id') == "782") { ?>
        <li <?php echo ($this->uri->segment(3) == "finance") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/members/finance'); ?>"><i class="fa fa-angle-double-right"></i><span>Validasi</span> </a> </li>
      <?php }   ?>

      <li <?php echo ($this->uri->segment(3) == "va") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/members/va'); ?>"><i class="fa fa-angle-double-right"></i><span>VA </span> </a> </li>

    <?php } ?>
    <!-- ----------------------------------------------------------------------------------------------------------------------------- End Of User Type 1 --------------------------- -->
    <!-- ------------------------------------------------------------- User Type = 8 --------------------------------------------- -->
    <?php if ($this->session->userdata('type') == "8") { ?>
      <li <?php echo ($this->uri->segment(3) == "finance") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/members/finance'); ?>"><i class="fa fa-angle-double-right"></i><span>Validasi</span> </a> </li>
      <li <?php echo ($this->uri->segment(3) == "report_stri") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/members/report_stri'); ?>"><i class="fa fa-angle-double-right"></i><span>Report STRI</span> </a> </li>
      <li <?php echo ($this->uri->segment(3) == "lapkeu") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/lapkeu/index/5'); ?>"><i class="fa fa-angle-double-right"></i><span>Proses Hitung Anggota</span> </a> </li>
      <!--	<li <?php echo ($this->uri->segment(3) == "lapkeu") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/lapkeu/index/1'); ?>"><i class="fa fa-angle-double-right"></i><span>Laporan V1</span> </a> </li> -->
      <li <?php echo ($this->uri->segment(3) == "lapkeu") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/lapkeu/index/2'); ?>"><i class="fa fa-angle-double-right"></i><span>Laporan V2</span> </a> </li>
      <li <?php echo ($this->uri->segment(3) == "lapkeu") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/lapkeu/index/4'); ?>"><i class="fa fa-angle-double-right"></i><span>Laporan Kirim</span> </a> </li>
      <li <?php echo ($this->uri->segment(3) == "lapkeu") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/lapkeu/index/6'); ?>"><i class="fa fa-angle-double-right"></i><span>Laporan Mutasi</span> </a> </li>

      <!-- Test userprovisioner Rizal -->
      <li <?php echo ($this->uri->segment(3) == "test_upload_ugm") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/Userprovisioner'); ?>"><i class="fa fa-angle-double-right"></i><span>Test Upload Ugm</span> </a> </li>



    <?php } ?>
    <!-- --------------------------------------------------------------------------------------------------------------------------- End Of User Type 8 --- -->

    <?php if ($this->session->userdata('admin_id') == "673" || $this->session->userdata('admin_id') == "731") { ?>
      <li <?php echo ($this->uri->segment(3) == "finance") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/members/finance'); ?>"><i class="fa fa-angle-double-right"></i><span>Validasi</span> </a> </li>
    <?php } ?>
    <!-- --------------------------------------------------------------- Menu Tambahan PSPPI , AER dan APEC --------------------------------------------------------------------- -->

    <?php if ($this->session->userdata('admin_id') == "729" || $this->session->userdata('admin_id') == "731"  || $this->session->userdata('admin_id') == "1") { ?>
      <li <?php echo ($this->uri->segment(3) == "psppi") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/psppi_baru'); ?>"><i class="fa fa-angle-double-right"></i><span>PSPPI</span> </a> </li>
    <?php } ?>

    <?php if ($this->session->userdata('admin_id') == "784" || $this->session->userdata('admin_id') == "782") { ?>
      <li <?php echo ($this->uri->segment(3) == "pi") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/members/pi'); ?>"><i class="fa fa-angle-double-right"></i><span>SKIP </span> </a> </li>
      <li <?php echo ($this->uri->segment(3) == "stri") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/members/stri'); ?>"><i class="fa fa-angle-double-right"></i><span>STRI </span> </a> </li>
    <?php } ?>

    <?php if (
      $this->session->userdata('admin_id') == "784" || $this->session->userdata('admin_id') == "729" || $this->session->userdata('admin_id') == "731"
      || $this->session->userdata('admin_id') == "1"  || $this->session->userdata('admin_id') == "782"  || $this->session->userdata('admin_id') == "672"
    ) { ?>
      <li <?php echo ($this->uri->segment(3) == "aer") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/aer'); ?>"><i class="fa fa-angle-double-right"></i><span>ASEAN Eng.</span> </a> </li>
    <?php } ?>

    <?php if (
      $this->session->userdata('admin_id') == "784" || $this->session->userdata('admin_id') == "729" || $this->session->userdata('admin_id') == "731"
      ||  $this->session->userdata('admin_id') == "782" || $this->session->userdata('admin_id') == "672"
    ) { ?>
      <li <?php echo ($this->uri->segment(3) == "apec") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/apec'); ?>"><i class="fa fa-angle-double-right"></i><span>A P E C</span> </a> </li>
    <?php } ?>

    <?php if (
      $this->session->userdata('admin_id') == "784"  || $this->session->userdata('admin_id') == "1" ||  $this->session->userdata('admin_id') == "731"
      || $this->session->userdata('admin_id') == "672" || $this->session->userdata('admin_id') == "782"
    ) { ?>
      <li <?php echo ($this->uri->segment(3) == "acpe") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/acpe'); ?>"><i class="fa fa-angle-double-right"></i><span>A C P E</span> </a> </li>
    <?php } ?>


    <!-- ----------------------------------------------------------------------------------------------------------------------------------- -->
    <?php if ($this->session->userdata('type') == "15") { ?>

      <li <?php echo ($this->uri->segment(3) == "" && $this->uri->segment(2) == "members") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/members'); ?>"><i class="fa fa-angle-double-right"></i><span>Members</span> </a> </li>

    <?php } ?>

    <?php if ($this->session->userdata('type') == "11" && $this->session->userdata('code_bk_hkk') == "") { ?>

      <li <?php echo ($this->uri->segment(3) == "" && $this->uri->segment(2) == "members") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/members'); ?>"><i class="fa fa-angle-double-right"></i><span>Members</span> </a> </li>

      <?php //----------------------------------------------------------------------------------------------------------------------- Khusus untuk Admin UGM ---------------
      if ($this->session->userdata('admin_id') == "682"  && $this->session->userdata('kode_kolektif') == "500") { ?>
        <li <?php echo ($this->uri->segment(3) == "faip") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/members/faip'); ?>"><i class="fa fa-angle-double-right"></i><span>FAIP </span> </a> </li>
      <?php     }
      //----------------------------------------------------------------------------------------------------------------------------------------------------------------------------
    } else if ($this->session->userdata('type') == "11" && $this->session->userdata('code_bk_hkk') != "") { ?>

      <li <?php echo ($this->uri->segment(3) == "" && $this->uri->segment(2) == "members") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/members'); ?>"><i class="fa fa-angle-double-right"></i><span>Members</span> </a> </li>

      <li <?php echo ($this->uri->segment(3) == "non_kta") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/members/non_kta'); ?>"><i class="fa fa-angle-double-right"></i><span>REG Members</span> </a> </li>

      <li <?php echo ($this->uri->segment(3) == "her_kta") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/members/her_kta'); ?>"><i class="fa fa-angle-double-right"></i><span>HER Members</span> </a> </li>

      <li <?php echo ($this->uri->segment(3) == "stri") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/members/stri'); ?>"><i class="fa fa-angle-double-right"></i><span>STRI </span> </a> </li>

      <li <?php echo ($this->uri->segment(3) == "faip") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/members/faip'); ?>"><i class="fa fa-angle-double-right"></i><span>FAIP </span> </a> </li>

      <li <?php echo ($this->uri->segment(3) == "pi") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/members/pi'); ?>"><i class="fa fa-angle-double-right"></i><span>SKIP </span> </a> </li>
      <?php if ($this->session->userdata('is_pkb') == "1") { ?>

        <li <?php echo ($this->uri->segment(3) == "pkb") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/members/pkb'); ?>"><i class="fa fa-angle-double-right"></i><span>PKB </span> </a> </li>

      <?php } ?>

      <!--<li <?php //echo ($this->uri->segment(3)=="majelis")?'class="active"':'';
              ?>> <a href="<?php //echo base_url('admin/members/majelis');
                            ?>"><i class="fa fa-angle-double-right"></i><span>Majelis </span> </a> </li>-->
    <?php } ?>

    <?php if ($this->session->userdata('type') == "7") { ?>

      <li <?php echo ($this->uri->segment(3) == "faip") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/members/faip'); ?>"><i class="fa fa-angle-double-right"></i><span>FAIP </span> </a> </li>

      <?php if ($this->session->userdata('is_pkb') == "1") { ?>

        <li <?php echo ($this->uri->segment(3) == "pkb") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/members/pkb'); ?>"><i class="fa fa-angle-double-right"></i><span>PKB </span> </a> </li>

      <?php } ?>

    <?php } ?>

    <?php if ($this->session->userdata('type') == "12") { ?>

      <li <?php echo ($this->uri->segment(3) == "stri_member") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/members/stri_member'); ?>"><i class="fa fa-angle-double-right"></i><span>STRI Members</span> </a> </li>

      <li <?php echo ($this->uri->segment(3) == "set_stri") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/members/set_stri'); ?>"><i class="fa fa-angle-double-right"></i><span>Set STRI</span> </a> </li>

      <li <?php echo ($this->uri->segment(3) == "stri") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/members/stri'); ?>"><i class="fa fa-angle-double-right"></i><span>STRI </span> </a> </li>

      <li <?php echo ($this->uri->segment(3) == "va") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/members/va'); ?>"><i class="fa fa-angle-double-right"></i><span>VA </span> </a> </li>
    <?php } ?>

    <?php if ($this->session->userdata('type') == "13") { ?>

      <li <?php echo ($this->uri->segment(3) == "stri_member") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/members/stri_member'); ?>"><i class="fa fa-angle-double-right"></i><span>STRI Members</span> </a> </li>

    <?php } ?>


    <?php if ($this->session->userdata('type') == "14") { ?>
      <li <?php echo ($this->uri->segment(3) == "" && $this->uri->segment(2) == "members") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/members'); ?>"><i class="fa fa-angle-double-right"></i><span>All Members</span> </a> </li>
      <li <?php echo ($this->uri->segment(3) == "non_kta") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/members/non_kta'); ?>"><i class="fa fa-angle-double-right"></i><span>REG Members</span> </a> </li>

      <li <?php echo ($this->uri->segment(3) == "her_kta") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/members/her_kta'); ?>"><i class="fa fa-angle-double-right"></i><span>HER Members</span> </a> </li>

      <?php if ($this->session->userdata('admin_id') == "684") { ?>
        <li <?php echo ($this->uri->segment(3) == "va") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/members/va'); ?>"><i class="fa fa-angle-double-right"></i><span>VA </span> </a> </li>

    <?php }
    } ?>

    <?php if ($this->session->userdata('type') == "16") { ?>

      <?php if ($this->session->userdata('is_pkb') == "1") { ?>

        <li <?php echo ($this->uri->segment(3) == "pkb") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/members/pkb'); ?>"><i class="fa fa-angle-double-right"></i><span>PKB </span> </a> </li>
        <li <?php echo ($this->uri->segment(3) == "va") ? 'class="active"' : ''; ?>> <a href="<?php echo base_url('admin/members/va'); ?>"><i class="fa fa-angle-double-right"></i><span>VA </span> </a> </li>
      <?php } ?>

    <?php } ?>

    <li> <a href="<?php echo base_url('admin/home/logout'); ?>"><i class="fa fa-angle-double-right"></i><span>Logout</span> </a> </li>
    </ul>
  </section>
  <!-- /.sidebar -->
</aside>