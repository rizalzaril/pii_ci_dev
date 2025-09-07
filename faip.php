<?php if (!defined('BASEPATH')) exit('No direct script access allowed');


/**
 * 
 * @property Faip_model.php faip_model
 * @property Members_model.php members_model
 * @property Upload.php upload
 * @property Image_lib.php image_lib
 * @property Tank_auth tank_auth
 * @property Main_mod main_mod
 */
class Faip extends CI_Controller
{
  function __construct()
  {
    parent::__construct();

    date_default_timezone_set('Asia/Jakarta');
    $this->load->library('form_validation');
    //$this->load->library('security');
    $this->load->helper(array('form', 'url', 'utility', 'file', 'security'));
    $this->load->library('tank_auth');
    $this->lang->load('tank_auth');
    $this->load->model('main_mod');
    $this->load->model('members_model');
    $this->load->model('faip_model');

    $user_id = $this->session->userdata('user_id');
    if (isRoot() == FALSE) {

      if (empty($user_id)) {
        $this->session->set_flashdata('message', "You're not logged in or your session is expired");
        redirect('auth/login');
      }

      // Cek apakah user (login) masih anggota (akhir peride anggota > tanggal hari ini )
      $is_access = $this->main_mod->msrquery(
        'select a.id from members a join users b on a.person_id=b.id where username <> "" and person_id="' .
          $user_id . '" and thru_date >= curdate()'
      )->result();

      if (!isset($is_access[0])) {
        $is_access2 = $this->main_mod->msrquery('select id from users where id="' . $user_id . '" and by_pass = 1')->num_rows();
        if ($is_access2 == 0) {
          echo '<script>alert("Harap proses aktifasi keanggotaan terlebih dahulu");window.location.href="' . base_url() . 'member/kta";</script>';

          // 20240729 - exit() ditambahkan. Not tested yet. 
          // Ini sangat penting!! agar index() tidak tereksekusi.
          exit();
        }
      }
    }
  }

  /**
   * Member's FAIP Dashboard
   * Memperlihatkan semua FAIP yg dimiliki oleh user pengakses
   * Diakses oleh anggota dari menu kiri (navbar)
   */
  function index()
  {

    //$data = '';
    $data['title'] = 'PII | FAIP';
    $data['email'] = $this->session->userdata('email');
    //$this->load->view('member/beranda', $data);
    $id = $this->session->userdata('user_id');
    $data['user_faip'] = $this->main_mod->msrwhere('user_faip', array('user_id' => $id, 'status' => 1), 'id', 'desc')->result();
    $data['m_faip_status'] = $this->main_mod->msrwhere('m_faip_status', array('is_active' => 1), 'seq_number', 'asc')->result();

    $data["m_bk"] = $this->members_model->get_all_bk();

    $user_profiles = $this->main_mod->msrwhere('user_profiles', array('user_id' => $id), 'id', 'desc')->result();
    $data['va'] = $user_profiles[0]->va;


    //$this->load->view('member/faip_dashboard', $data);
    $this->load->view('member/faip_dashboard - NOT VA', $data);
  }

  /**
   * Save FAIP 
   * Called by from FAIP editor by clicking "Save & Continue" button. JS: savefaip()
   * 
   * Create new FAIP
   */
  function submit()
  {

    $user_id      = $this->session->userdata('user_id');
    $createdby_id = $this->session->userdata('user_id');

    log_message('debug', "[SIMPONI] " . __CLASS__ . "@" . __FUNCTION__ . " accessedby: {$user_id}");


    // TODO: Consider to user $this->_check_faip_status_owner()? but it's include "status_faip<>3"
    //$is_access = $this->main_mod->msrquery('select id from user_faip where user_id="'.$user_id
    //	.'" and (status_faip>0 and status_faip<12) and status_faip<>9')->result();		
    //if(isset($is_access[0])){
    //	redirect('member');
    //}

    //error_reporting(0);
    $data['email'] = $this->session->userdata('email');

    $obj_member = $this->members_model->get_member_by_id($user_id);
    $data['row'] = $obj_member;
    $data['kta'] = $this->members_model->get_kta_data_by_personid($user_id);;


    $data['m_degree'] = $this->main_mod->msrquery('select * from education_type where HAS_TABLE="Y" or seq=9 order by seq asc')->result();
    $data['m_bk'] = $this->main_mod->msrwhere('m_bk', array('faip' => 1), 'id', 'asc')->result();

    //Get data from table user_* e.g. address, email, edu, cert, exp
    $data = $data + $this->_get_user_details($user_id);

    $data['emailx'] =  $this->session->userdata('email');

    // Data Bakuan Penilaian
    $faipNumWithPenilaian_list = array('12', '13', '14', '15', '16', '3', '4', '51', '52', '53', '54', '6');
    foreach ($faipNumWithPenilaian_list as $faip_num) {
      $data['bp_' . $faip_num] = $this->main_mod->msrwhere('m_bakuan_penilaian', array('faip_num' => $faip_num), 'id', 'asc')->result();
    }

    $is_submit = "0";

    $periodstart      = "";
    $periodend        = "";
    $id_faip          = '';
    $subkejuruan      = "";
    $bidang           = "";
    $faip_type        = "";
    $certificate_type = "";
    $pernyataan       = "";
    $wkt_pernyataan   = "";

    $addr_type = $this->input->post('addr_type') <> null ? $this->input->post('addr_type') : "";
    $addr_desc = $this->input->post('addr_desc') <> null ? $this->input->post('addr_desc') : "";
    $addr_loc = $this->input->post('addr_loc') <> null ? $this->input->post('addr_loc') : "";
    $addr_zip = $this->input->post('addr_zip') <> null ? $this->input->post('addr_zip') : "";


    $exp_name = $this->input->post('exp_name') <> null ? $this->input->post('exp_name') : "";
    $exp_desc = $this->input->post('exp_desc') <> null ? $this->input->post('exp_desc') : "";
    $exp_loc = $this->input->post('exp_loc') <> null ? $this->input->post('exp_loc') : "";
    $exp_zip = $this->input->post('exp_zip') <> null ? $this->input->post('exp_zip') : "";

    $phone_type = $this->input->post('phone_type') <> null ? $this->input->post('phone_type') : "";
    $phone_value = $this->input->post('phone_value') <> null ? $this->input->post('phone_value') : "";

    $school = $this->input->post('12_school') <> null ? $this->input->post('12_school') : "";
    $degree = $this->input->post('12_degree') <> null ? $this->input->post('12_degree') : "";
    $fakultas = $this->input->post('12_fakultas') <> null ? $this->input->post('12_fakultas') : "";
    $fieldofstudy = $this->input->post('12_fieldofstudy') <> null ? $this->input->post('12_fieldofstudy') : "";

    $lam_aktifitas = $this->input->post('lam_aktifitas') <> null ? $this->input->post('lam_aktifitas') : "";

    $nama21 = $this->input->post('21_nama') <> null ? $this->input->post('21_nama') : "";

    //$user_id='';
    $faip_id = '';
    $id_faip = '';


    //INSERT MASTER
    try {
      if ($id_faip == "") {
        $row = array(
          'user_id'          => $user_id,
          'no_kta'           => isset($data['kta']->no_kta) ? str_pad($data['kta']->no_kta, 6, '0', STR_PAD_LEFT) : '',
          'nama'             => ucwords(trim(strtolower($data['row']->firstname)) . " " . trim(strtolower($data['row']->lastname))),
          'periodstart'      => $periodstart,
          'periodend'        => $periodend,
          'subkejuruan'      => $subkejuruan,
          'bidang'           => isset($data['kta']->code_bk_hkk) ? $data['kta']->code_bk_hkk : '',
          'faip_type'        => $faip_type,
          'certificate_type' => $certificate_type,
          'pernyataan'       => $pernyataan,
          'wkt_pernyataan'   => $wkt_pernyataan,
          'createdby'        => $createdby_id,
          'status_faip'      => $is_submit
        );
        $insert = $this->main_mod->insert('user_faip', $row);
        $faip_id = $insert;
      }
    } catch (Throwable $e) {
      log_message('debug', '[SIMPONI] ' . __CLASS__ . '@' . __FUNCTION__ . " Exception: Failed to insert data user_faip " . $e->getMessage());
      $this->session->set_flashdata('error', 'Exception: Failed to insert data user_faip ' . $e->getMessage());
      print_r($e);
      return false;
    }
    //print_r($data['row']);
    //INSERT 11
    $t = strtotime($data['row']->dob);
    try {
      if ($id_faip == "") {
        $row = array(
          'faip_id' => $faip_id,
          'nama' => ucwords(trim(strtolower($data['row']->firstname)) . " " . trim(strtolower($data['row']->lastname))),
          'birthplace' => ucwords(strtolower($data['row']->birthplace)),
          'dob' => (($data['row']->dob != "0000-00-00") ? $data['row']->dob : ""), //date('d F Y',$t)
          'no_kta' => isset($data['kta']->no_kta) ? str_pad($data['kta']->no_kta, 6, '0', STR_PAD_LEFT) : '',
          'subkejuruan' => isset($data['kta']->code_bk_hkk) ? $data['kta']->code_bk_hkk : '',
          'bidang' => isset($data['kta']->code_bk_hkk) ? $data['kta']->code_bk_hkk : '',
          'photo' => (($data['row']->photo != '') ? base_url() . 'assets/uploads/' . $data['row']->photo : ""),
          'mobilephone' => $data['row']->mobilephone,
          'email' => $this->session->userdata('email'),
          'createdby' => $createdby_id,
        );
        $insert = $this->main_mod->insert('user_faip_11', $row);
      }
    } catch (Exception $e) {
      log_message('debug', '[SIMPONI] ' . __CLASS__ . '@' . __FUNCTION__ . " Exception: Failed to insert data user_faip_11 " . $e->getMessage());
      $this->session->set_flashdata('error', 'Exception: Failed to insert data user_faip_11 ' . $e->getMessage());
      print_r($e);
      return false;
    }

    //INSERT 111			
    if (is_array($data['user_address'])) {
      $j = 0;
      foreach ($data['user_address'] as $v) {
        try {
          $row = array(
            'faip_id' => $faip_id,
            'addr_type' => (isset($v->addresstype) ? $v->addresstype : ''),
            'addr_desc' => (isset($v->address) ? $v->address : ''),
            'addr_loc' => (isset($v->city) ? $v->city : ''),
            'addr_zip' => (isset($v->zipcode) ? $v->zipcode : ''),
            'createdby' => $createdby_id,
          );
          $insert = $this->main_mod->insert('user_faip_111', $row);
        } catch (Exception $e) {
          log_message('debug', '[SIMPONI] ' . __CLASS__ . '@' . __FUNCTION__ . " Exception: Failed to insert data user_faip_111 " . $e->getMessage());
          $this->session->set_flashdata('error', 'Exception: Failed to insert data user_faip_111 ' . $e->getMessage());
          print_r($e);
        }
        $j++;
      }
    }

    //INSERT 112
    if (is_array($data['user_lembaga'])) {
      $j = 0;
      foreach ($data['user_lembaga'] as $v) {
        $tempid = '';
        $exp_zip = "";
        try {
          $row = array(
            'faip_id' => $faip_id,
            'exp_name' => (isset($v->company) ? $v->company : ''),
            'exp_desc' => (isset($v->title) ? $v->title : ''),
            'exp_loc' => (isset($v->location) ? $v->location : ''),
            'exp_zip' => $exp_zip,
            'createdby' => $createdby_id,
          );
          $insert = $this->main_mod->insert('user_faip_112', $row);
        } catch (Exception $e) {
          log_message('debug', '[SIMPONI] ' . __CLASS__ . '@' . __FUNCTION__ . " Exception: Failed to insert data user_faip_112 " . $e->getMessage());
          $this->session->set_flashdata('error', 'Exception: Failed to insert data user_faip_112 ' . $e->getMessage());
          print_r($e);
        }
        $j++;
      }
    }

    //INSERT 113
    if (is_array($data['user_phone'])) {
      $j = 0;
      foreach ($data['user_phone'] as $v) {
        try {
          $row = array(
            'faip_id' => $faip_id,
            'phone_type' => (isset($v->contact_type) ? $v->contact_type : ''),
            'phone_value' => (isset($v->contact_value) ? $v->contact_value : ''),
            'createdby' => $createdby_id,
          );
          $insert = $this->main_mod->insert('user_faip_113', $row);
        } catch (Exception $e) {
          log_message('debug', '[SIMPONI] ' . __CLASS__ . '@' . __FUNCTION__ . " Exception: Failed to insert data user_faip_113 " . $e->getMessage());
          $this->session->set_flashdata('error', 'Exception: Failed to insert data user_faip_113 ' . $e->getMessage());
          print_r($e);
        }
        $j++;
      }
    }

    //INSERT 12
    if (is_array($data['user_edu'])) {
      $j = 0;
      foreach ($data['user_edu'] as $v) {
        try {
          $row = array(
            'faip_id' => $faip_id,
            'school' => (isset($v->school) ? $v->school : ''),
            'school_type' => (isset($v->degree) ? $v->degree : ''),
            'fakultas' => (isset($v->mayor) ? $v->mayor : ''),
            'jurusan' => (isset($v->fieldofstudy) ? $v->fieldofstudy : ''),
            'kota' => '',
            'provinsi' => '',
            'negara' => '',
            'tahun_lulus' => (isset($v->enddate) ? $v->enddate : ''),
            'title' => (isset($v->title) ? $v->title : ''),
            'judul' => (isset($v->activities) ? $v->activities : ''),
            'uraian' => (isset($v->description) ? $v->description : ''),
            'score' => (isset($v->score) ? $v->score : ''),


            'kompetensi' => 'W.2',

            'p' => 0,
            'q' => 0,
            'r' => 0,
            't' => 0,
            'attachment' => (isset($v->attachment) ? $v->attachment : ''),
            'createdby' => $createdby_id,
          );
          $insert = $this->main_mod->insert('user_faip_12', $row);
        } catch (Exception $e) {
          log_message('debug', '[SIMPONI] ' . __CLASS__ . '@' . __FUNCTION__ . " Exception: Failed to insert data user_faip_12 " . $e->getMessage());
          $this->session->set_flashdata('error', 'Exception: Failed to insert data user_faip_12 ' . $e->getMessage());
          print_r($e);
        }
        $j++;
      }
    }

    //INSERT 13_
    if (is_array($data['user_org'])) {

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

      $j = 0;
      //print_r($a13_komp);
      foreach ($data['user_org'] as $v) {
        $tempid = '';
        $i = 0;

        $jabatan = '';
        if (isset($bp_13_q)) {
          foreach ($bp_13_q as $val2) {
            if (trim($v->position) == trim($val2->desc))
              $jabatan = $val2->value;
          }
        }
        $tingkat = '';
        if (isset($bp_13_r)) {
          foreach ($bp_13_r as $val2) {
            if (trim($v->tingkat) == trim($val2->desc))
              $tingkat = $val2->value;
          }
        }

        //$perioda = $this->input->post('13_perioda')<>null?$this->input->post('13_perioda'):"";
        //$aktifitas = $this->input->post('13_aktifitas')<>null?$this->input->post('13_aktifitas'):"";						
        try {
          $row = array(
            'faip_id' => $faip_id,
            'parent' => ($i == 0) ? 0 : $tempid,
            'nama_org' => (isset($v->organization) ? $v->organization : ''),
            'jenis' => (isset($v->jenis) ? $v->jenis : ''),
            'lingkup' => (isset($v->lingkup) ? $v->lingkup : ''),
            'jabatan' =>  $jabatan,
            'tempat' => (isset($v->occupation) ? $v->occupation : ''),
            'provinsi' => (isset($v->provinsi) ? $v->provinsi : ''),
            'negara' => (isset($v->negara) ? $v->negara : ''),
            'aktifitas' => (isset($v->description) ? $v->description : ''),
            'startdate' => (isset($v->startmonth) ? $v->startmonth : ''),
            'startyear' => (isset($v->startyear) ? $v->startyear : ''),
            'enddate' => (isset($v->endmonth) ? $v->endmonth : ''),
            'endyear' => (isset($v->endyear) ? $v->endyear : ''),
            'is_present' => (isset($v->is_present) ? $v->is_present : ''),
            'tingkat' =>  $tingkat,
            'kompetensi' => '',

            'p' => 0,
            'q' => 0,
            'r' => 0,
            't' => 0,

            'attachment' => (isset($v->attachment) ? $v->attachment : ''),
            'createdby' => $createdby_id,
          );
          $insert = $this->main_mod->insert('user_faip_13', $row);

          if ($i == 0) $tempid = $insert;
        } catch (Exception $e) {
          log_message('debug', '[SIMPONI] ' . __CLASS__ . '@' . __FUNCTION__ . " Exception: Failed to insert data user_faip_13 " . $e->getMessage());
          $this->session->set_flashdata('error', 'Exception: Failed to insert data user_faip_13 ' . $e->getMessage());
          print_r($e);
        }
        $i++;

        $j++;
      }
    }

    //INSERT 14_
    if (is_array($data['user_award'])) {

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

      $j = 0;
      foreach ($data['user_award'] as $v) {
        $tempid = '';
        $i = 0;

        $tingkat = '';
        if (isset($bp_14_q)) {
          foreach ($bp_14_q as $val2) {
            if (trim($v->tingkat) == trim($val2->desc))
              $tingkat = $val2->value;
          }
        }

        $tingkatlembaga = '';
        if (isset($bp_14_r)) {
          foreach ($bp_14_r as $val2) {
            if (trim($v->pemberi) == trim($val2->desc))
              $tingkatlembaga = $val2->value;
          }
        }



        try {
          $row = array(
            'faip_id' => $faip_id,
            'parent' => ($i == 0) ? 0 : $tempid,

            'startdate' => (isset($v->startmonth) ? $v->startmonth : ''),
            'startyear' => (isset($v->startyear) ? $v->startyear : ''),
            'nama' => (isset($v->name) ? $v->name : ''),
            'lembaga' => (isset($v->issue) ? $v->issue : ''),
            'tingkat' => $tingkat,
            'tingkatlembaga' => $tingkatlembaga,
            'location' => (isset($v->location) ? $v->location : ''),
            'provinsi' => (isset($v->provinsi) ? $v->provinsi : ''),
            'negara' => (isset($v->negara) ? $v->negara : ''),
            'uraian' => (isset($v->description) ? $v->description : ''),

            'p' => 0,
            'q' => 0,
            'r' => 0,
            't' => 0,

            'attachment' => (isset($v->attachment) ? $v->attachment : ''),
            //'kompetensi' => $kompetensi,
            'createdby' => $createdby_id,
          );
          $insert = $this->main_mod->insert('user_faip_14', $row);



          if ($i == 0) $tempid = $insert;
        } catch (Exception $e) {
          log_message('debug', '[SIMPONI] ' . __CLASS__ . '@' . __FUNCTION__ . " Exception: Failed to insert data user_faip_14 " . $e->getMessage());
          $this->session->set_flashdata('error', 'Exception: Failed to insert data user_faip_14 ' . $e->getMessage());
          print_r($e);
        }
        $i++;

        $j++;
      }
    }

    //INSERT 15_
    if (is_array($data['user_course1'])) {

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

      $j = 0; //$tempid='';$i=0;$tempkomp='';
      foreach ($data['user_course1'] as $v) {
        $tempid = '';
        $i = 0;

        $tingkat = '';
        if (isset($bp_15_r)) {
          foreach ($bp_15_r as $val2) {
            if (trim($v->tingkat) == trim($val2->desc))
              $tingkat = $val2->value;
          }
        }

        $hour = '';
        if (isset($bp_15_q)) {
          foreach ($bp_15_q as $val2) {
            if (trim($v->hour) == trim($val2->desc))
              $hour = $val2->value;
          }
        }


        try {
          $row = array(
            'faip_id' => $faip_id,
            'parent' => ($i == 0) ? 0 : $tempid,

            'nama' => (isset($v->coursename) ? $v->coursename : ''),
            'lembaga' => (isset($v->courseorg) ? $v->courseorg : ''),
            'jam' => $hour,
            'tingkat' => $tingkat,
            'location' => (isset($v->location) ? $v->location : ''),
            'provinsi' => (isset($v->provinsi) ? $v->provinsi : ''),
            'negara' => (isset($v->negara) ? $v->negara : ''),
            'uraian' => (isset($v->description) ? $v->description : ''),

            'startdate' => (isset($v->startmonth) ? $v->startmonth : ''),
            'startyear' => (isset($v->startyear) ? $v->startyear : ''),
            'enddate' => (isset($v->endmonth) ? $v->endmonth : ''),
            'endyear' => (isset($v->endyear) ? $v->endyear : ''),
            'is_present' => (isset($v->is_present) ? $v->is_present : ''),

            'p' => 0,
            'q' => 0,
            'r' => 0,
            't' => 0,

            'attachment' => (isset($v->attachment) ? $v->attachment : ''),


            //'kompetensi' => $kompetensi,
            'createdby' => $createdby_id,
          );
          $insert = $this->main_mod->insert('user_faip_15', $row);
          if ($i == 0) $tempid = $insert;
        } catch (Exception $e) {
          log_message('debug', '[SIMPONI] ' . __CLASS__ . '@' . __FUNCTION__ . " Exception: Failed to insert data user_faip_15 " . $e->getMessage());
          $this->session->set_flashdata('error', 'Exception: Failed to insert data user_faip_15 ' . $e->getMessage());
          print_r($e);
        }
        $i++;

        $j++;
      }
    }

    //INSERT 16_
    if (is_array($data['user_cert'])) {

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

      $j = 0; //$tempid='';$i=0;$tempkomp='';
      foreach ($data['user_cert'] as $v) {
        $tempid = '';
        $i = 0;

        $tingkat = '';
        /*if(isset($bp_16_r)){
						foreach($bp_16_r as $val2){
							if(trim($v->tingkat)==trim($val2->desc))
								$tingkat = $val2->value;
						}
					}*/

        $hour = '';
        /*if(isset($bp_16_q)){
						foreach($bp_16_q as $val2){
							if(trim($v->hour)==trim($val2->desc))
								$hour = $val2->value;
						}
					}*/


        try {
          $row = array(
            'faip_id' => $faip_id,
            'parent' => ($i == 0) ? 0 : $tempid,

            'nama' => (isset($v->cert_name) ? $v->cert_name : ''),
            'lembaga' => (isset($v->cert_auth) ? $v->cert_auth : ''),
            'jam' => $hour,
            'tingkat' => $tingkat,
            'location' => (isset($v->location) ? $v->location : ''),
            'provinsi' => (isset($v->provinsi) ? $v->provinsi : ''),
            'negara' => (isset($v->negara) ? $v->negara : ''),
            'uraian' => (isset($v->description) ? $v->description : ''),

            'startdate' => (isset($v->startmonth) ? $v->startmonth : ''),
            'startyear' => (isset($v->startyear) ? $v->startyear : ''),
            'enddate' => (isset($v->endmonth) ? $v->endmonth : ''),
            'endyear' => (isset($v->endyear) ? $v->endyear : ''),

            'is_present' => (isset($v->is_present) ? $v->is_present : ''),

            'p' => 0,
            'q' => 0,
            'r' => 0,
            't' => 0,

            'attachment' => (isset($v->attachment) ? $v->attachment : ''),
            'createdby' => $createdby_id,
          );
          $insert = $this->main_mod->insert('user_faip_16', $row);


          if ($i == 0) $tempid = $insert;
        } catch (Exception $e) {
          log_message('debug', '[SIMPONI] ' . __CLASS__ . '@' . __FUNCTION__ . " Exception: Failed to insert data user_faip_16 " . $e->getMessage());
          $this->session->set_flashdata('error', 'Exception: Failed to insert data user_faip_16 ' . $e->getMessage());
          print_r($e);
        }
        $i++;

        $j++;
      }
    }

    //INSERT 21_

    //INSERT 22_

    //INSERT 3_
    if (is_array($data['user_exp'])) {

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

      $j = 0; //$tempid='';$i=0;$tempkomp='';
      foreach ($data['user_exp'] as $v) {
        $tempid = '';
        $i = 0;

        $periode = '';
        if (isset($bp_3_p)) {
          foreach ($bp_3_p as $val2) {

            $birthdate_ts = strtotime("$v->startyear-$v->startmonth-1");
            $birthdate_ts2 = strtotime("$v->endyear-$v->endmonth-1");
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

            if (trim($tempid) == trim($val2->value))
              $periode = $val2->value;
          }
        }

        $position = '';
        /*if(isset($bp_3_q)){
						foreach($bp_3_q as $val2){
							if(trim($v->position)==trim($val2->desc))
								$position = $val2->value;
						}
					}*/
        $nilaiproyek = '';
        /*if(isset($bp_3_r)){
						foreach($bp_3_r as $val2){
							if(trim($v->nilaiproyek)==trim($val2->desc))
								$nilaiproyek = $val2->value;
						}
					}*/

        try {
          $row = array(
            'faip_id' => $faip_id,
            'parent' => ($i == 0) ? 0 : $tempid,

            'instansi' => (isset($v->company) ? $v->company : ''),
            'startdate' => (isset($v->startmonth) ? $v->startmonth : ''),
            'startyear' => (isset($v->startyear) ? $v->startyear : ''),
            'enddate' => (isset($v->endmonth) ? $v->endmonth : ''),
            'endyear' => (isset($v->endyear) ? $v->endyear : ''),
            'is_present' => (isset($v->is_present) ? $v->is_present : ''),
            'title' => (isset($v->title) ? $v->title : ''),
            'nilaipry' => '',
            'nilaijasa' => '',
            'nilaisdm' => '',
            'nilaisulit' => '',
            'location' => (isset($v->location) ? $v->location : ''),
            'provinsi' => (isset($v->provinsi) ? $v->provinsi : ''),
            'negara' => (isset($v->negara) ? $v->negara : ''),
            'namaproyek' => (isset($v->actv) ? $v->actv : ''),
            'posisi' => $position,
            'periode' => $periode,
            'nilaiproyek' => $nilaiproyek,
            'pemberitugas' => (isset($v->company) ? $v->company : ''),
            'uraian' => (isset($v->description) ? $v->description : ''),

            'p' => 0,
            'q' => 0,
            'r' => 0,
            't' => 0,

            'attachment' => (isset($v->attachment) ? $v->attachment : ''),
            'createdby' => $createdby_id,
          );
          $insert = $this->main_mod->insert('user_faip_3', $row);

          if ($i == 0) $tempid = $insert;
        } catch (Exception $e) {
          log_message('debug', '[SIMPONI] ' . __CLASS__ . '@' . __FUNCTION__ . " Exception: Failed to insert data user_faip_3 " . $e->getMessage());
          $this->session->set_flashdata('error', 'Exception: Failed to insert data user_faip_3 ' . $e->getMessage());
          print_r($e);
        }

        $i++;

        $j++;
      }
    }

    //INSERT 4_


    //INSERT 51_
    if (is_array($data['user_publication1'])) {

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

      $j = 0; //$tempid='';$i=0;$tempkomp='';
      foreach ($data['user_publication1'] as $v) {
        $tempid = '';
        $i = 0;

        $tingkatmedia = '';
        if (isset($bp_51_q)) {
          foreach ($bp_51_q as $val2) {
            if (trim($v->tingkat) == trim($val2->desc))
              $tingkatmedia = $val2->value;
          }
        }

        $tingkat = '';
        if (isset($bp_51_r)) {
          foreach ($bp_51_r as $val2) {
            if (trim($v->tingkatmedia) == trim($val2->desc))
              $tingkat = $val2->value;
          }
        }

        try {
          $row = array(
            'faip_id' => $faip_id,
            'parent' => ($i == 0) ? 0 : $tempid,

            'startdate' => (isset($v->startmonth) ? $v->startmonth : ''),
            'startyear' => (isset($v->startyear) ? $v->startyear : ''),
            'nama' => (isset($v->topic) ? $v->topic : ''),
            'location' => (isset($v->location) ? $v->location : ''),
            'provinsi' => (isset($v->provinsi) ? $v->provinsi : ''),
            'negara' => (isset($v->negara) ? $v->negara : ''),
            'media' => (isset($v->media) ? $v->media : ''),
            'tingkatmedia' => $tingkatmedia,
            'tingkat' => $tingkat,
            'uraian' => (isset($v->description) ? $v->description : ''),

            'p' => 0,
            'q' => 0,
            'r' => 0,
            't' => 0,

            'attachment' => (isset($v->attachment) ? $v->attachment : ''),
            'createdby' => $createdby_id,
          );
          $insert = $this->main_mod->insert('user_faip_51', $row);


          if ($i == 0) $tempid = $insert;
        } catch (Exception $e) {
          log_message('debug', '[SIMPONI] ' . __CLASS__ . '@' . __FUNCTION__ . " Exception: Failed to insert data user_faip_51 " . $e->getMessage());
          $this->session->set_flashdata('error', 'Exception: Failed to insert data user_faip_51 ' . $e->getMessage());
          print_r($e);
        }
        $i++;

        $j++;
      }
    }

    //INSERT 52_
    if (is_array($data['user_publication2'])) {

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

      $j = 0; //$tempid='';$i=0;$tempkomp='';
      foreach ($data['user_publication2'] as $v) {
        $tempid = '';
        $i = 0;

        $tingkatseminar = '';
        if (isset($bp_52_q)) {
          foreach ($bp_52_q as $val2) {
            if (trim($v->tingkat) == trim($val2->desc))
              $tingkatseminar = $val2->value;
          }
        }

        $tingkat = '';
        if (isset($bp_52_r)) {
          foreach ($bp_52_r as $val2) {
            if (trim($v->tingkatmedia) == trim($val2->desc))
              $tingkat = $val2->value;
          }
        }


        try {
          $row = array(
            'faip_id' => $faip_id,
            'parent' => ($i == 0) ? 0 : $tempid,

            'startdate' => (isset($v->startmonth) ? $v->startmonth : ''),
            'startyear' => (isset($v->startyear) ? $v->startyear : ''),
            'nama' => (isset($v->event) ? $v->event : ''),
            'penyelenggara' => (isset($v->media) ? $v->media : ''),
            'judul' => (isset($v->topic) ? $v->topic : ''),
            'location' => (isset($v->location) ? $v->location : ''),
            'provinsi' => (isset($v->provinsi) ? $v->provinsi : ''),
            'negara' => (isset($v->negara) ? $v->negara : ''),
            'tingkatseminar' => $tingkatseminar,
            'tingkat' => $tingkat,
            'uraian' => (isset($v->description) ? $v->description : ''),


            'p' => 0,
            'q' => 0,
            'r' => 0,
            't' => 0,

            'attachment' => (isset($v->attachment) ? $v->attachment : ''),
            'createdby' => $createdby_id,
          );
          $insert = $this->main_mod->insert('user_faip_52', $row);


          if ($i == 0) $tempid = $insert;
        } catch (Exception $e) {
          log_message('debug', '[SIMPONI] ' . __CLASS__ . '@' . __FUNCTION__ . " Exception: Failed to insert data user_faip_52 " . $e->getMessage());
          $this->session->set_flashdata('error', 'Exception: Failed to insert data user_faip_52 ' . $e->getMessage());
          print_r($e);
        }
        $i++;

        $j++;
      }
    }

    //INSERT 53_
    if (is_array($data['user_publication3'])) {

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

      $j = 0; //$tempid='';$i=0;$tempkomp='';
      foreach ($data['user_publication3'] as $v) {
        $tempid = '';
        $i = 0;

        $tingkatseminar = '';
        if (isset($bp_53_q)) {
          foreach ($bp_53_q as $val2) {
            if (trim($v->tingkat) == trim($val2->desc))
              $tingkatseminar = $val2->value;
          }
        }

        $tingkat = '';
        if (isset($bp_53_r)) {
          foreach ($bp_53_r as $val2) {
            if (trim($v->tingkatmedia) == trim($val2->desc))
              $tingkat = $val2->value;
          }
        }


        try {
          $row = array(
            'faip_id' => $faip_id,
            'parent' => ($i == 0) ? 0 : $tempid,

            'startdate' => (isset($v->startmonth) ? $v->startmonth : ''),
            'startyear' => (isset($v->startyear) ? $v->startyear : ''),
            'nama' => (isset($v->topic) ? $v->topic : ''),
            'location' => (isset($v->location) ? $v->location : ''),
            'provinsi' => (isset($v->provinsi) ? $v->provinsi : ''),
            'negara' => (isset($v->negara) ? $v->negara : ''),
            'penyelenggara' => (isset($v->media) ? $v->media : ''),
            'tingkatseminar' => $tingkatseminar,
            'tingkat' => $tingkat,
            'uraian' => (isset($v->description) ? $v->description : ''),


            'p' => 0,
            'q' => 0,
            'r' => 0,
            't' => 0,

            'attachment' => (isset($v->attachment) ? $v->attachment : ''),
            'createdby' => $createdby_id,
          );
          $insert = $this->main_mod->insert('user_faip_53', $row);


          if ($i == 0) $tempid = $insert;
        } catch (Exception $e) {
          log_message('debug', '[SIMPONI] ' . __CLASS__ . '@' . __FUNCTION__ . " Exception: Failed to insert data user_faip_53 " . $e->getMessage());
          $this->session->set_flashdata('error', 'Exception: Failed to insert data user_faip_53 ' . $e->getMessage());
          print_r($e);
        }
        $i++;

        $j++;
      }
    }

    //INSERT 54_
    if (is_array($data['user_publication4'])) {

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

      $j = 0; //$tempid='';$i=0;$tempkomp='';
      foreach ($data['user_publication4'] as $v) {
        $tempid = '';
        $i = 0;

        $tingkatseminar = '';
        if (isset($bp_54_q)) {
          foreach ($bp_54_q as $val2) {
            if (trim($v->tingkat) == trim($val2->desc))
              $tingkatseminar = $val2->value;
          }
        }

        $tingkat = '';
        if (isset($bp_54_r)) {
          foreach ($bp_54_r as $val2) {
            if (trim($v->tingkatmedia) == trim($val2->desc))
              $tingkat = $val2->value;
          }
        }
        try {
          $row = array(
            'faip_id' => $faip_id,
            'parent' => ($i == 0) ? 0 : $tempid,

            'startdate' => (isset($v->startmonth) ? $v->startmonth : ''),
            'startyear' => (isset($v->startyear) ? $v->startyear : ''),
            'nama' => (isset($v->topic) ? $v->topic : ''),
            //'location' => (isset($v->location)?$v->location:''),
            //'provinsi' => (isset($v->provinsi)?$v->provinsi:''),
            //'negara' => (isset($v->negara)?$v->negara:''),
            'media_publikasi' => (isset($v->media) ? $v->media : ''),
            'tingkatseminar' => $tingkatseminar,
            'tingkat' => $tingkat,
            'uraian' => (isset($v->description) ? $v->description : ''),


            'p' => 0,
            'q' => 0,
            'r' => 0,
            't' => 0,

            'attachment' => (isset($v->attachment) ? $v->attachment : ''),
            'createdby' => $createdby_id,
          );
          $insert = $this->main_mod->insert('user_faip_54', $row);



          if ($i == 0) $tempid = $insert;
        } catch (Exception $e) {
          log_message('debug', '[SIMPONI] ' . __CLASS__ . '@' . __FUNCTION__ . " Exception: Failed to insert data user_faip_54 " . $e->getMessage());
          $this->session->set_flashdata('error', 'Exception: Failed to insert data user_faip_54 ' . $e->getMessage());
          print_r($e);
        }
        $i++;

        $j++;
      }
    }

    //INSERT 6_
    if (is_array($data['user_skill'])) {

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

      $j = 0; //$tempid='';$i=0;$tempkomp='';
      foreach ($data['user_skill'] as $v) {
        $tempid = '';
        $i = 0;

        $jenisbahasa = '';
        if (isset($bp_6_q)) {
          foreach ($bp_6_q as $val2) {
            if (strpos($val2->desc, $v->jenisbahasa) !== false)
              $jenisbahasa = $val2->value;
          }
        }

        $verbal = '';
        if (isset($bp_6_r)) {
          foreach ($bp_6_r as $val2) {
            if (strpos($val2->desc, $v->proficiency) !== false)
              $verbal = $val2->value;
          }
        }

        $jenistulisan = '';
        if (isset($v->jenistulisan)) {
          if ($v->jenistulisan != '') {
            if (strpos("Makalah", $v->jenistulisan) !== false)
              $jenistulisan = "Makalah";
            else if (strpos("Jurnal", $v->jenistulisan) !== false)
              $jenistulisan = "Jurnal";
            else if (strpos("Laporan", $v->jenistulisan) !== false)
              $jenistulisan = "Laporan";
          }
        }
        try {
          $row = array(
            'faip_id' => $faip_id,
            'parent' => ($i == 0) ? 0 : $tempid,
            'jenisbahasa' => $jenisbahasa,
            'verbal' => $verbal,
            'jenistulisan' => $jenistulisan,
            'nama' => (isset($v->name) ? $v->name : ''),
            //'kompetensi' => $kompetensi,

            'p' => 0,
            'q' => 0,
            'r' => 0,
            't' => 0,

            'attachment' => (isset($v->attachment) ? $v->attachment : ''),

            'createdby' => $createdby_id,
          );
          $insert = $this->main_mod->insert('user_faip_6', $row);


          if ($i == 0) $tempid = $insert;
        } catch (Exception $e) {
          log_message('debug', '[SIMPONI] ' . __CLASS__ . '@' . __FUNCTION__ . " Exception: Failed to insert data user_faip_6 " . $e->getMessage());
          $this->session->set_flashdata('error', 'Exception: Failed to insert data user_faip_6 ' . $e->getMessage());
          print_r($e);
        }
        $i++;

        $j++;
      }
    }

    //INSERT LAMPIRAN


    //echo $faip_id;
    redirect('faip/editfaip/' . $faip_id);
  }

  private function _check_faip_status_owner($user_id, $id_faip)
  {
    // Does this faip belong to the user who access it?
    // Faip status is either 1, 2, 4, 5, 6, 7, 8, 10, 11 (not 3 or 9)
    // $is_access = $this->main_mod->msrquery(
    // 	'select id from user_faip where user_id="' . $user_id . 
    // 	'" and id <> "' . $id_faip . '" and (status_faip>0 and status_faip<12) and status_faip<>9 and status_faip<>3'
    // )->result();
    // if (isset($is_access[0])) {
    // 	// ER: see m_faip_status
    // 	$this->session->set_flashdata('message', "FAIP is not accessible to you because the status is in-progress or it's not belong to you.");
    // 	redirect('member'); // No access
    // }
  }

  private function _get_user_details($user_id)
  {
    $data = array();
    $data['user_address'] = $this->main_mod->msrwhere('user_address', array('user_id' => $user_id, 'status' => 1), 'id', 'asc')->result();
    $data['user_email']   = $this->main_mod->msrwhere('contacts', array('user_id' => $user_id, 'contact_type like "%_email%"' => null, 'status' => 1), 'id', 'asc')->result();
    $data['user_phone']   = $this->main_mod->msrwhere('contacts', array('user_id' => $user_id, 'contact_type like "%_phone%"' => null, 'status' => 1), 'id', 'asc')->result();
    $data['user_edu']     = $this->main_mod->msrwhere('user_edu', array('user_id' => $user_id, 'status' => 1), 'enddate', 'asc')->result();
    $data['user_cert']    = $this->main_mod->msrwhere('user_cert', array('user_id' => $user_id, 'status <> 0' => null), 'id', 'asc')->result();
    $data['user_exp']     = $this->main_mod->msrwhere('user_exp', array('user_id' => $user_id, 'status' => 1), 'startyear', 'asc')->result();
    $data['user_lembaga'] = $this->main_mod->msrwhere('user_exp', array('user_id' => $user_id, 'status' => 1, 'is_present' => 1), 'id', 'asc')->result();
    $data['user_org'] = $this->main_mod->msrwhere('user_org', array('user_id' => $user_id, 'status' => 1), 'id', 'asc')->result();
    $data['user_award'] = $this->main_mod->msrwhere('user_award', array('user_id' => $user_id, 'status' => 1), 'id', 'asc')->result();
    $data['user_course1'] = $this->main_mod->msrwhere('user_course', array('user_id' => $user_id, 'status' => 1), 'id', 'asc')->result();
    $data['user_prof'] = $this->main_mod->msrwhere('user_prof', array('user_id' => $user_id, 'status' => 1), 'id', 'asc')->result();
    $data['user_publication1'] = $this->main_mod->msrwhere('user_publication', array('user_id' => $user_id, 'status' => 1, "type" => "1"), 'id', 'asc')->result();
    $data['user_publication2'] = $this->main_mod->msrwhere('user_publication', array('user_id' => $user_id, 'status' => 1, "type" => "2"), 'id', 'asc')->result();
    $data['user_publication3'] = $this->main_mod->msrwhere('user_publication', array('user_id' => $user_id, 'status' => 1, "type" => "3"), 'id', 'asc')->result();
    $data['user_publication4'] = $this->main_mod->msrwhere('user_publication', array('user_id' => $user_id, 'status' => 1, "type" => "4"), 'id', 'asc')->result();
    $data['user_skill'] = $this->main_mod->msrwhere('user_skill', array('user_id' => $user_id, 'status' => 1), 'id', 'asc')->result();;

    return $data;
  }

  /**
   * Dipanggil saat user klik Edit FAIP di halaman FAIP Dashbord (member)
   */
  function editfaip($id_faip = null)
  {

    $user_id       = $this->session->userdata('user_id') ?: $this->input->get('user_id');
    $createdby_id  = $this->session->userdata('user_id') ?: $user_id;
    $modifiedby_id = $this->session->userdata('user_id') ?: $user_id;
    $id_faip       = $this->uri->segment(3) ?: $this->input->get('faip_id');

    log_message('debug', "[SIMPONI] " . __CLASS__ . "@" . __FUNCTION__ . " faip_id: {$id_faip}, accessedby: {$user_id}");

    if (empty($id_faip)) {
      $this->session->set_flashdata('message', "Input FAIP id is required");
    }

    // Does this faip belong to the user who access it?
    // Faip status is either 1, 2, 4, 5, 6, 7, 8, 10, 11 (not 3 or 9)
    $this->_check_faip_status_owner($user_id, $id_faip);

    $data['title'] = 'PII | FAIP';
    $data['email'] = $this->session->userdata('email');

    $this->load->model('faip_model');

    // Get FAIP data 
    $faip = $this->faip_model->get_faip_by_id($id_faip);
    $data['id_faip'] = $id_faip;
    $faip_user_id = isset($faip->user_id) ? $faip->user_id : "";

    $status = isset($faip->status_faip) ? $faip->status_faip : "";
    if ($status != '') {
      if ($status >= 4 && $faip->need_revisi == '0')
        redirect('faip');
    }

    // User loged in sama dengan FAIP owner
    if ($faip_user_id !== $user_id) {
      //TODO: Change this
      //echo "Access forbidden. You are not the owner of the FAIP";
      //exit();
      //TODO
    }

    $obj_member = $this->members_model->get_member_by_id($user_id);
    $data['row'] = $obj_member;
    $data['kta'] = $this->members_model->get_kta_data_by_personid($user_id);

    $data['user_faip'] = $faip;

    $data['user_faip_11'] = $this->main_mod->msrwhere('user_faip_11', array('faip_id' => $id_faip, 'status' => 1), 'id', 'asc')->result();
    $data['user_faip_12'] = $this->main_mod->msrwhere('user_faip_12', array('faip_id' => $id_faip, 'status' => 1), 'id', 'asc')->result();
    $data['user_faip_111'] = $this->main_mod->msrwhere('user_faip_111', array('faip_id' => $id_faip, 'status' => 1), 'id', 'asc')->result();
    $data['user_faip_112'] = $this->main_mod->msrwhere('user_faip_112', array('faip_id' => $id_faip, 'status' => 1), 'id', 'asc')->result();
    $data['user_faip_113'] = $this->main_mod->msrwhere('user_faip_113', array('faip_id' => $id_faip, 'status' => 1), 'id', 'asc')->result();

    $data['user_faip_3'] = $this->faip_model->get_all_faip_without_comp($id_faip, 'user_faip_3');
    if (is_array($data['user_faip_3'])) {
      $save_partial = $this->input->post('save_partial') <> null ? $this->input->post('save_partial') : "";
      if ($save_partial == '') {
        $i = 0;
        foreach ($data['user_faip_3'] as $v) {
          $temp = $this->main_mod->msrquery('select 
						GROUP_CONCAT(user_faip_3.kompetensi) as comp,
						GROUP_CONCAT(user_faip_3.id) as compid,
						GROUP_CONCAT(user_faip_3.p) as pp,
						GROUP_CONCAT(user_faip_3.q) as qq,
						GROUP_CONCAT(user_faip_3.r) as rr,
						GROUP_CONCAT(user_faip_3.t) as tt
						from user_faip_3 where user_faip_3.faip_id=' . $v->faip_id . ' and (user_faip_3.id=' . $v->id . ' or user_faip_3.parent=' . $v->id . ') and status=1')->row();
          $data['user_faip_3'][$i]->comp = $temp->comp;
          $data['user_faip_3'][$i]->compid = $temp->compid;
          $data['user_faip_3'][$i]->pp = $temp->pp;
          $data['user_faip_3'][$i]->qq = $temp->qq;
          $data['user_faip_3'][$i]->rr = $temp->rr;
          $data['user_faip_3'][$i]->tt = $temp->tt;
          $i++;
        }
      }
    }

    // FAIP Kompetensi
    $faipWithCompetency_list = array(
      'user_faip_13',
      'user_faip_14',
      'user_faip_15',
      'user_faip_16',
      'user_faip_21',
      'user_faip_22',
      'user_faip_3',
      'user_faip_4',
      'user_faip_51',
      'user_faip_52',
      'user_faip_53',
      'user_faip_54',
      'user_faip_6'
    );
    foreach ($faipWithCompetency_list as $tblname) {
      $data[$tblname] = $this->faip_model->get_all_faip_comp($id_faip, $tblname);
    }


    $data['user_faip_lam'] = $this->main_mod->msrwhere('user_faip_lam', array('faip_id' => $id_faip, 'status' => 1), 'id', 'asc')->result();

    //print_r($data['user_faip']);

    $data['m_komp'] = $this->main_mod->msrquery('select value,title from no_kompetensi a join m_kompetensi b on a.value=b.code')->result();
    $data['m_act_13'] = $this->main_mod->msrquery('select code as value,title from m_kompetensi where code like "W.1%" order by id asc')->result();
    $data['m_act_15'] = $this->main_mod->msrquery('select code as value,title from m_kompetensi where code like "W.2%" or code like "W.4%" or code like "P.10%" order by id asc')->result();
    $data['m_act_16'] = $this->main_mod->msrquery('select code as value,title from m_kompetensi where code like "W.1%" or code like "W.4%" or code like "P.10%" order by id asc')->result();

    $data['m_act_3'] = $this->main_mod->msrquery('select code as value,title from m_kompetensi where 
			code like "W.2%" or 
			code like "W.3%" or 
			code like "W.4%" or 
			code like "P.6%" or 
			code like "P.7%" or 
			code like "P.8%" or 
			code like "P.9%" or 
			code like "P.10%" or 
			code like "P.11%"  
			order by id asc')->result();
    $data['m_act_4'] = $this->main_mod->msrquery('select code as value,title from m_kompetensi where code like "P.5%" order by id asc')->result();
    $data['m_act_51'] = $this->main_mod->msrquery('select code as value,title from m_kompetensi where code like "W.4%" order by id asc')->result();
    $data['m_act_53'] = $this->main_mod->msrquery('select code as value,title from m_kompetensi where code like "W.2%" order by id asc')->result();
    $data['m_act_54'] = $this->main_mod->msrquery('select code as value,title from m_kompetensi where code like "P.6%" order by id asc')->result();


    $data['m_degree'] = $this->main_mod->msrquery('select * from education_type where HAS_TABLE="Y" or seq=9 order by SEQ asc')->result();
    $data['m_bk'] = $this->main_mod->msrwhere('m_bk', array('faip' => 1), 'id', 'asc')->result();

    //Get data from table user_* e.g. address, email, edu, cert, exp
    $data = $data + $this->_get_user_details($user_id);

    // Data Bakuan Penilaian
    $faipNumWithPenilaian_list = array('12', '13', '14', '15', '16', '3', '4', '51', '52', '53', '54', '6');
    foreach ($faipNumWithPenilaian_list as $faip_num) {
      $data['bp_' . $faip_num] = $this->main_mod->msrwhere('m_bakuan_penilaian', array('faip_num' => $faip_num), 'id', 'asc')->result();
    }

    $data['m_matriks_klaim'] = $this->main_mod->msrwhere('m_matriks_klaim', array('condition' => '', 'notes' => ''), 'faip_num', 'asc')->result();

    $this->form_validation->set_rules('firstname', 'First Name', 'trim|xss_clean');

    // FAIP submitted?
    $is_submit = $this->input->post('submitfaip') <> null ? $this->input->post('submitfaip') : "";

    if ($is_submit == "1") {
      /*
				$this->form_validation->set_rules('bidang', 'Rekap - Bidang Pekerjaan', 'required|trim|xss_clean');
				$this->form_validation->set_rules('faip_type', 'Rekap - Jenis Permohonan', 'required|trim|xss_clean');
				$this->form_validation->set_rules('certificate_type', 'Rekap - Jenis Permohonan', 'required|trim|xss_clean');
				$this->form_validation->set_rules('periodstart', 'I.1 - Perioda', 'required|trim|xss_clean');
				$this->form_validation->set_rules('periodend', 'I.1 - Perioda', 'required|trim|xss_clean');
				
				$orgkomp = $this->input->post('orgkomp')<>null?$this->input->post('orgkomp'):"";
				$phgkomp = $this->input->post('phgkomp')<>null?$this->input->post('phgkomp'):"";
				$pddkomp = $this->input->post('pddkomp')<>null?$this->input->post('pddkomp'):"";
				$ppmkomp = $this->input->post('ppmkomp')<>null?$this->input->post('ppmkomp'):"";
				$etikomp = $this->input->post('etikomp')<>null?$this->input->post('etikomp'):"";
				$kupkomp = $this->input->post('kupkomp')<>null?$this->input->post('kupkomp'):"";
				$mankomp = $this->input->post('mankomp')<>null?$this->input->post('mankomp'):"";
				$makkomp = $this->input->post('makkomp')<>null?$this->input->post('makkomp'):"";
				$rekkomp = $this->input->post('rekkomp')<>null?$this->input->post('rekkomp'):"";
				$asekomp = $this->input->post('asekomp')<>null?$this->input->post('asekomp'):"";
				$pubkomp = $this->input->post('pubkomp')<>null?$this->input->post('pubkomp'):"";
				$lokkomp = $this->input->post('lokkomp')<>null?$this->input->post('lokkomp'):"";
				$semkomp = $this->input->post('semkomp')<>null?$this->input->post('semkomp'):"";
				$inokomp = $this->input->post('inokomp')<>null?$this->input->post('inokomp'):"";
				$bahkomp = $this->input->post('bahkomp')<>null?$this->input->post('bahkomp'):"";
				
				//$id13 = $this->input->post('13_id')<>null?$this->input->post('13_id'):"";
				
				if(!empty($orgkomp)){foreach($orgkomp as $id => $datax){
					$this->form_validation->set_rules('orgkomp[' . $id . '][]', 'Form 1.3 Kompetensi', 'required|trim|xss_clean');}}
				if(!empty($phgkomp)){foreach($phgkomp as $id => $datax){
					$this->form_validation->set_rules('phgkomp[' . $id . '][]', 'Form 1.4 Kompetensi', 'required|trim|xss_clean');}}
				if(!empty($pddkomp)){foreach($pddkomp as $id => $datax){
					$this->form_validation->set_rules('pddkomp[' . $id . '][]', 'Form 1.5 Kompetensi', 'required|trim|xss_clean');}}
				if(!empty($ppmkomp)){foreach($ppmkomp as $id => $datax){
					$this->form_validation->set_rules('ppmkomp[' . $id . '][]', 'Form 1.6 Kompetensi', 'required|trim|xss_clean');}}
				if(!empty($etikomp)){foreach($etikomp as $id => $datax){
					$this->form_validation->set_rules('etikomp[' . $id . '][]', 'Form 2.2 Kompetensi', 'required|trim|xss_clean');}}
				if(!empty($kupkomp)){foreach($kupkomp as $id => $datax){
					$this->form_validation->set_rules('kupkomp[' . $id . '][]', 'Form 3.1 Kompetensi', 'required|trim|xss_clean');}}
				if(!empty($mankomp)){foreach($mankomp as $id => $datax){
					$this->form_validation->set_rules('mankomp[' . $id . '][]', 'Form 3.2 Kompetensi', 'required|trim|xss_clean');}}
				if(!empty($makkomp)){foreach($makkomp as $id => $datax){
					$this->form_validation->set_rules('makkomp[' . $id . '][]', 'Form 3.3 Kompetensi', 'required|trim|xss_clean');}}
				if(!empty($rekkomp)){foreach($rekkomp as $id => $datax){
					$this->form_validation->set_rules('rekkomp[' . $id . '][]', 'Form 3.4 Kompetensi', 'required|trim|xss_clean');}}
				if(!empty($asekomp)){foreach($asekomp as $id => $datax){
					$this->form_validation->set_rules('asekomp[' . $id . '][]', 'Form 3.5 Kompetensi', 'required|trim|xss_clean');}}
				if(!empty($pubkomp)){foreach($pubkomp as $id => $datax){
					$this->form_validation->set_rules('pubkomp[' . $id . '][]', 'Form 4.1 Kompetensi', 'required|trim|xss_clean');}}		
				if(!empty($lokkomp)){foreach($lokkomp as $id => $datax){
					$this->form_validation->set_rules('lokkomp[' . $id . '][]', 'Form 4.2 Kompetensi', 'required|trim|xss_clean');}}
				if(!empty($semkomp)){foreach($semkomp as $id => $datax){
					$this->form_validation->set_rules('semkomp[' . $id . '][]', 'Form 4.3 Kompetensi', 'required|trim|xss_clean');}}
				if(!empty($inokomp)){foreach($inokomp as $id => $datax){
					$this->form_validation->set_rules('inokomp[' . $id . '][]', 'Form 4.4 Kompetensi', 'required|trim|xss_clean');}}
				if(!empty($bahkomp)){foreach($bahkomp as $id => $datax){
					$this->form_validation->set_rules('bahkomp[' . $id . '][]', 'Form 5 Kompetensi', 'required|trim|xss_clean');}}
				*/

      $this->form_validation->set_rules('bidang', 'Rekap - Badan Keahlian', 'trim|xss_clean');
      $this->form_validation->set_rules('faip_type', 'Rekap - Jenis Permohonan', 'trim|xss_clean');
      $this->form_validation->set_rules('certificate_type', 'Rekap - Jenis Permohonan', 'trim|xss_clean');
      //$this->form_validation->set_rules('periodstart', 'I.1 - Perioda', 'required|trim|xss_clean');
      //$this->form_validation->set_rules('periodend', 'I.1 - Perioda', 'required|trim|xss_clean');

      $a13_komp = $this->input->post('13_komp') <> null ? $this->input->post('13_komp') : "";
      $a14_komp = $this->input->post('14_komp') <> null ? $this->input->post('14_komp') : "";
      $a15_komp = $this->input->post('15_komp') <> null ? $this->input->post('15_komp') : "";
      $a16_komp = $this->input->post('16_komp') <> null ? $this->input->post('16_komp') : "";
      $a3_komp = $this->input->post('3_komp') <> null ? $this->input->post('3_komp') : "";
      $a4_komp = $this->input->post('4_komp') <> null ? $this->input->post('4_komp') : "";
      $a51_komp = $this->input->post('51_komp') <> null ? $this->input->post('51_komp') : "";
      $a52_komp = $this->input->post('52_komp') <> null ? $this->input->post('52_komp') : "";
      $a53_komp = $this->input->post('53_komp') <> null ? $this->input->post('53_komp') : "";
      $a54_komp = $this->input->post('54_komp') <> null ? $this->input->post('54_komp') : "";
      $a6_komp = $this->input->post('6_komp') <> null ? $this->input->post('6_komp') : "";

      //$id13 = $this->input->post('13_id')<>null?$this->input->post('13_id'):"";

      if (!empty($a13_komp)) {
        foreach ($a13_komp as $id => $datax) {
          $this->form_validation->set_rules('13_komp[' . $id . '][]', 'Form 1.3 Kompetensi', 'required|trim|xss_clean');
        }
      }
      if (!empty($a14_komp)) {
        foreach ($a14_komp as $id => $datax) {
          $this->form_validation->set_rules('14_komp[' . $id . '][]', 'Form 1.4 Kompetensi', 'required|trim|xss_clean');
        }
      }
      if (!empty($a15_komp)) {
        foreach ($a15_komp as $id => $datax) {
          $this->form_validation->set_rules('15_komp[' . $id . '][]', 'Form 1.5 Kompetensi', 'required|trim|xss_clean');
        }
      }
      if (!empty($a16_komp)) {
        foreach ($a16_komp as $id => $datax) {
          $this->form_validation->set_rules('16_komp[' . $id . '][]', 'Form 1.6 Kompetensi', 'required|trim|xss_clean');
        }
      }
      if (!empty($a3_komp)) {
        foreach ($a3_komp as $id => $datax) {
          $this->form_validation->set_rules('3_komp[' . $id . '][]', 'Form III Kompetensi', 'required|trim|xss_clean');
        }
      }
      if (!empty($a4_komp)) {
        foreach ($a4_komp as $id => $datax) {
          $this->form_validation->set_rules('4_komp[' . $id . '][]', 'Form IV Kompetensi', 'required|trim|xss_clean');
        }
      }
      if (!empty($a51_komp)) {
        foreach ($a51_komp as $id => $datax) {
          $this->form_validation->set_rules('51_komp[' . $id . '][]', 'Form V.1 Kompetensi', 'required|trim|xss_clean');
        }
      }
      if (!empty($a52_komp)) {
        foreach ($a52_komp as $id => $datax) {
          $this->form_validation->set_rules('52_komp[' . $id . '][]', 'Form V.2 Kompetensi', 'required|trim|xss_clean');
        }
      }
      if (!empty($a53_komp)) {
        foreach ($a53_komp as $id => $datax) {
          $this->form_validation->set_rules('53_komp[' . $id . '][]', 'Form V.3 Kompetensi', 'required|trim|xss_clean');
        }
      }
      if (!empty($a54_komp)) {
        foreach ($a54_komp as $id => $datax) {
          $this->form_validation->set_rules('54_komp[' . $id . '][]', 'Form V.4 Kompetensi', 'required|trim|xss_clean');
        }
      }
      if (!empty($a6_komp)) {
        foreach ($a6_komp as $id => $datax) {
          $this->form_validation->set_rules('6_komp[' . $id . '][]', 'Form 6 Kompetensi', 'required|trim|xss_clean');
        }
      }
    }


    if ($this->form_validation->run()) {
      $save_partial = $this->input->post('save_partial') <> null ? $this->input->post('save_partial') : "";

      $subkejuruan = $this->input->post('subkejuruan') <> null ? $this->input->post('subkejuruan') : "";
      $bidang = $this->input->post('bidang_tujuan') <> null ? $this->input->post('bidang_tujuan') : "";
      $faip_type = $this->input->post('faip_type') <> null ? $this->input->post('faip_type') : "";
      $certificate_type = $this->input->post('certificate_type') <> null ? $this->input->post('certificate_type') : "";
      $pernyataan = $this->input->post('pernyataan') <> null ? $this->input->post('pernyataan') : "";
      $wkt_pernyataan = $this->input->post('wkt_pernyataan') <> null ? $this->input->post('wkt_pernyataan') : "";
      $periodstart = $this->input->post('periodstart') <> null ? $this->input->post('periodstart') : "";
      $periodend = $this->input->post('periodend') <> null ? $this->input->post('periodend') : "";

      $hwb1 = $this->input->post('hwb1') <> null ? $this->input->post('hwb1') : "";
      $hwb2 = $this->input->post('hwb2') <> null ? $this->input->post('hwb2') : "";
      $hwb3 = $this->input->post('hwb3') <> null ? $this->input->post('hwb3') : "";
      $hwb4 = $this->input->post('hwb4') <> null ? $this->input->post('hwb4') : "";
      $hpil = $this->input->post('hpil') <> null ? $this->input->post('hpil') : "";
      $hjml = $this->input->post('hjml') <> null ? $this->input->post('hjml') : "";


      $addr_type = $this->input->post('addr_type') <> null ? $this->input->post('addr_type') : "";
      $exp_name = $this->input->post('exp_name') <> null ? $this->input->post('exp_name') : "";
      $phone_type = $this->input->post('phone_type') <> null ? $this->input->post('phone_type') : "";
      $school = $this->input->post('12_school') <> null ? $this->input->post('12_school') : "";

      $lam_aktifitas = $this->input->post('lam_aktifitas') <> null ? $this->input->post('lam_aktifitas') : "";

      $nama21 = $this->input->post('21_nama') <> null ? $this->input->post('21_nama') : "";

      $a13_komp = $this->input->post('13_komp') <> null ? $this->input->post('13_komp') : "";
      $a14_komp = $this->input->post('14_komp') <> null ? $this->input->post('14_komp') : "";
      $a15_komp = $this->input->post('15_komp') <> null ? $this->input->post('15_komp') : "";
      $a16_komp = $this->input->post('16_komp') <> null ? $this->input->post('16_komp') : "";
      $a22_komp = $this->input->post('22_komp') <> null ? $this->input->post('22_komp') : "";
      $a3_komp = $this->input->post('3_komp') <> null ? $this->input->post('3_komp') : "";
      $a4_komp = $this->input->post('4_komp') <> null ? $this->input->post('4_komp') : "";
      $a51_komp = $this->input->post('51_komp') <> null ? $this->input->post('51_komp') : "";
      $a52_komp = $this->input->post('52_komp') <> null ? $this->input->post('52_komp') : "";
      $a53_komp = $this->input->post('53_komp') <> null ? $this->input->post('53_komp') : "";
      $a54_komp = $this->input->post('54_komp') <> null ? $this->input->post('54_komp') : "";
      $a6_komp = $this->input->post('6_komp') <> null ? $this->input->post('6_komp') : "";



      $id111 = $this->input->post('111_id') <> null ? $this->input->post('111_id') : "";
      $id112 = $this->input->post('112_id') <> null ? $this->input->post('112_id') : "";
      $id113 = $this->input->post('113_id') <> null ? $this->input->post('113_id') : "";
      $id12 = $this->input->post('12_id') <> null ? $this->input->post('12_id') : "";
      $id13 = $this->input->post('13_id') <> null ? $this->input->post('13_id') : "";
      $id14 = $this->input->post('14_id') <> null ? $this->input->post('14_id') : "";
      $id15 = $this->input->post('15_id') <> null ? $this->input->post('15_id') : "";
      $id16 = $this->input->post('16_id') <> null ? $this->input->post('16_id') : "";
      $id21 = $this->input->post('21_id') <> null ? $this->input->post('21_id') : "";
      $id22 = $this->input->post('22_id') <> null ? $this->input->post('22_id') : "";
      $id3 = $this->input->post('3_id') <> null ? $this->input->post('3_id') : "";
      $id4 = $this->input->post('4_id') <> null ? $this->input->post('4_id') : "";
      $id51 = $this->input->post('51_id') <> null ? $this->input->post('51_id') : "";
      $id52 = $this->input->post('52_id') <> null ? $this->input->post('52_id') : "";
      $id53 = $this->input->post('53_id') <> null ? $this->input->post('53_id') : "";
      $id54 = $this->input->post('54_id') <> null ? $this->input->post('54_id') : "";
      $id6 = $this->input->post('6_id') <> null ? $this->input->post('6_id') : "";

      $idlam = $this->input->post('lam_id') <> null ? $this->input->post('lam_id') : "";
      //print_r($id12);
      $id = $id_faip;
      //UPDATE MASTER
      try {

        if ($is_submit == "1" || $save_partial == "1") {
          $check = $this->main_mod->msrwhere('user_faip', array('id' => $id_faip), 'id', 'desc')->result();
          $rowInsert = array(
            'faip_id' => $id_faip,
            'old_status' => $check[0]->status_faip,
            'new_status' => $check[0]->need_revisi == '1' ? $check[0]->status_faip : 1,
            'notes' => 'anggota',
            'createdby' => $createdby_id,
          );
          $this->main_mod->insert('log_status_faip', $rowInsert);
        }

        $check_fp = $this->main_mod->msrwhere('user_faip', array('id' => $id_faip), 'id', 'desc')->result();

        $row = array(
          //'user_id' => $this->session->userdata('user_id'),
          //'no_kta' => str_pad($data['kta']->no_kta,6,'0',STR_PAD_LEFT),
          //'nama' => ucwords(trim(strtolower($data['row']->firstname))." ".trim(strtolower($data['row']->lastname))),
          'periodstart' => $periodstart,
          'periodend' => $periodend,
          'subkejuruan' => $subkejuruan,
          'bidang' => isset($data['kta']->code_bk_hkk) ? $data['kta']->code_bk_hkk : '',
          'bidang_tujuan' => $bidang,
          'faip_type' => $faip_type,
          'certificate_type' => $certificate_type,

          // Pre-Score
          'wajib1_score' => $hwb1,
          'wajib2_score' => $hwb2,
          'wajib3_score' => $hwb3,
          'wajib4_score' => $hwb4,
          'pilihan_score' => $hpil,
          'total_score' => $hjml,

          'pernyataan' => (($is_submit == "1" || $save_partial == "1") ? $pernyataan : ""),
          'wkt_pernyataan' => (($is_submit == "1" || $save_partial == "1") ? $wkt_pernyataan : ""),
          'modifiedby' => $modifiedby_id,
          'modifieddate' => date('Y-m-d H:i:s'),
          'status_faip' => (($is_submit == "1" || $save_partial == "1") ? "1" : "0")
        );
        $where = array(
          "id" => $id_faip
        );

        if ($check_fp[0]->need_revisi == '1') {
          unset($row['status_faip']);
          unset($row['wkt_pernyataan']);
          if ($is_submit == "1" || $save_partial == "1") {
            $row['need_revisi'] = 0;
          }
        }

        $update = $this->main_mod->update('user_faip', $where, $row);
      } catch (Exception $e) {
        print_r($e);
        log_message('debug', '[SIMPONI] ' . __CLASS__ . '@' . __FUNCTION__ . "Error updating user_faip. " . $e->getMessage());
        return false;
      }

      //NO UPDATE 11
      /*$t = strtotime($data['row']->dob); 
				try{
					$row=array(
						'faip_id' => $faip_id,
						'nama' => ucwords(trim(strtolower($data['row']->firstname))." ".trim(strtolower($data['row']->lastname))),
						'birthplace' => ucwords(strtolower($data['row']->birthplace)),
						'dob' => (($data['row']->dob!="0000-00-00")?date('d F Y',$t):""),
						'no_kta' => str_pad($data['kta']->no_kta,6,'0',STR_PAD_LEFT),					
						'subkejuruan' => $data['kta']->code_bk_hkk,
						'bidang' => $data['kta']->bk,
						'photo' => (($data['row']->photo!='')?base_url().'assets/uploads/'.$data['row']->photo:""),
						'mobilephone' => $data['row']->mobilephone,
						'email' => $this->session->userdata('email'),
						'createdby' => $createdby_id,
					);
					$insert = $this->main_mod->insert('user_faip_11',$row);
					$id=$insert;
				}
				catch(Exception $e){
					print_r($e);
					return false;
				}*/

      //UPDATE 111
      if ($is_submit == "1" || $save_partial == '11') {
        if ($is_submit == "1") $temp = ''; //$temp = $this->main_mod->update('user_faip_111',array("faip_id" => $id),array("status" => 0));	
        else $temp = $this->main_mod->delete('user_faip_111', "faip_id", $id);


        if (is_array($id111)) {
          $j = 0;
          foreach ($id111 as $val) {
            $addr_type = $this->input->post('addr_type') <> null ? $this->input->post('addr_type') : "";
            $addr_desc = $this->input->post('addr_desc') <> null ? $this->input->post('addr_desc') : "";
            $addr_loc = $this->input->post('addr_loc') <> null ? $this->input->post('addr_loc') : "";
            $addr_zip = $this->input->post('addr_zip') <> null ? $this->input->post('addr_zip') : "";
            try {
              $row = array(
                'faip_id' => $id,
                'addr_type' => (isset($addr_type[$j]) ? $addr_type[$j] : ''),
                'addr_desc' => (isset($addr_desc[$j]) ? $addr_desc[$j] : ''),
                'addr_loc' => (isset($addr_loc[$j]) ? $addr_loc[$j] : ''),
                'addr_zip' => (isset($addr_zip[$j]) ? $addr_zip[$j] : ''),
                'createdby' => $createdby_id,
                'modifiedby' => $modifiedby_id,
                'modifieddate' => date('Y-m-d H:i:s'),
                'status' => 1,
              );
              if ($val != "" && $is_submit == "1") {
                $where = array(
                  "id" => $val
                );
                //$update = $this->main_mod->update('user_faip_111',$where,$row);		
              } else $insert = $this->main_mod->insert('user_faip_111', $row);

              //SYNC user_address
              if ($is_submit == "1") {
                $o_data = $this->main_mod->msrwhere('user_address', array('user_id' => $user_id, 'status' => 1, 'addresstype' => (isset($addr_type[$j]) ? $addr_type[$j] : ''), 'address' => (isset($addr_desc[$j]) ? $addr_desc[$j] : '')), 'id', 'asc')->result();
                if (count($o_data) > 0) {
                  $temp = $this->main_mod->update(
                    'user_address',
                    array('user_id' => $user_id, 'status' => 1, 'addresstype' => (isset($addr_type[$j]) ? $addr_type[$j] : ''), 'address' => (isset($addr_desc[$j]) ? $addr_desc[$j] : '')),
                    array(
                      'addresstype' => (isset($addr_type[$j]) ? $addr_type[$j] : ''),
                      'address' => (isset($addr_desc[$j]) ? $addr_desc[$j] : ''),
                      'city' => (isset($addr_loc[$j]) ? $addr_loc[$j] : ''),
                      'zipcode' => (isset($addr_zip[$j]) ? $addr_zip[$j] : '')
                    )
                  );
                } else {
                  $this->main_mod->insert('user_address', array(
                    'user_id' => $user_id,
                    'status' => 1,
                    'addresstype' => (isset($addr_type[$j]) ? $addr_type[$j] : ''),
                    'address' => (isset($addr_desc[$j]) ? $addr_desc[$j] : ''),
                    'city' => (isset($addr_loc[$j]) ? $addr_loc[$j] : ''),
                    'zipcode' => (isset($addr_zip[$j]) ? $addr_zip[$j] : '')
                  ));
                }
              }
              //SYNC	
            } catch (Exception $e) {
              print_r($e);
            }
            $j++;
          }
        }

        //UPDATE 112
        if ($is_submit == "1") $temp = ''; //$temp = $this->main_mod->update('user_faip_112',array("faip_id" => $id),array("status" => 0));	
        else $temp = $this->main_mod->delete('user_faip_112', "faip_id", $id);


        if (is_array($id112)) {
          $j = 0;
          foreach ($id112 as $val) {
            $exp_name = $this->input->post('exp_name') <> null ? $this->input->post('exp_name') : "";
            $exp_desc = $this->input->post('exp_desc') <> null ? $this->input->post('exp_desc') : "";
            $exp_loc = $this->input->post('exp_loc') <> null ? $this->input->post('exp_loc') : "";
            $exp_zip = $this->input->post('exp_zip') <> null ? $this->input->post('exp_zip') : "";
            try {
              $row = array(
                'faip_id' => $id,
                'exp_name' => $exp_name[$j],
                'exp_desc' => $exp_desc[$j],
                'exp_loc' => $exp_loc[$j],
                'exp_zip' => $exp_zip[$j],
                'createdby' => $createdby_id,
                'modifiedby' => $modifiedby_id,
                'modifieddate' => date('Y-m-d H:i:s'),
                'status' => 1,

              );
              if ($val != "" && $is_submit == "1") {
                $where = array(
                  "id" => $val
                );
                //$update = $this->main_mod->update('user_faip_112',$where,$row);		
              } else $insert = $this->main_mod->insert('user_faip_112', $row);
            } catch (Exception $e) {
              print_r($e);
            }
            $j++;
          }
        }

        //UPDATE 113
        if ($is_submit == "1") $temp = ''; //$temp = $this->main_mod->update('user_faip_113',array("faip_id" => $id),array("status" => 0));	
        else $temp = $this->main_mod->delete('user_faip_113', "faip_id", $id);


        if (is_array($id113)) {
          $j = 0;
          foreach ($id113 as $val) {
            $phone_type = $this->input->post('phone_type') <> null ? $this->input->post('phone_type') : "";
            $phone_value = $this->input->post('phone_value') <> null ? $this->input->post('phone_value') : "";
            try {
              $row = array(
                'faip_id' => $id,
                'phone_type' => $phone_type[$j],
                'phone_value' => $phone_value[$j],
                'createdby' => $createdby_id,
                'modifiedby' => $modifiedby_id,
                'modifieddate' => date('Y-m-d H:i:s'),
                'status' => 1,

              );
              if ($val != "" && $is_submit == "1") {
                $where = array(
                  "id" => $val
                );
                //$update = $this->main_mod->update('user_faip_113',$where,$row);		
              } else $insert = $this->main_mod->insert('user_faip_113', $row);

              //SYNC contacts
              if ($is_submit == "1") {
                $o_data = $this->main_mod->msrwhere('contacts', array('user_id' => $user_id, 'status' => 1, 'contact_value' => $phone_value[$j]), 'id', 'asc')->result();
                if (count($o_data) > 0) {
                  $temp = $this->main_mod->update(
                    'contacts',
                    array('user_id' => $user_id, 'status' => 1, 'contact_value' => $phone_value[$j]),
                    array(
                      'contact_type' => $phone_type[$j],
                      'contact_value' => $phone_value[$j]
                    )
                  );
                } else {
                  $this->main_mod->insert('contacts', array(
                    'user_id' => $user_id,
                    'status' => 1,
                    'contact_type' => $phone_type[$j],
                    'contact_value' => $phone_value[$j]
                  ));
                }
              }
              //SYNC
            } catch (Exception $e) {
              print_r($e);
            }
            $j++;
          }
        }
      }

      //UPDATE 12
      if ($is_submit == "1" || $save_partial == '12') {
        if ($is_submit == "1") $temp = ''; //$temp = $this->main_mod->update('user_faip_12',array("faip_id" => $id),array("status" => 0));	
        else $temp = $this->main_mod->delete('user_faip_12', "faip_id", $id);

        if (is_array($id12)) {
          $j = 0;
          foreach ($id12 as $val) {
            $school = $this->input->post('12_school') <> null ? $this->input->post('12_school') : "";
            $degree = $this->input->post('12_degree') <> null ? $this->input->post('12_degree') : "";
            $fakultas = $this->input->post('12_fakultas') <> null ? $this->input->post('12_fakultas') : "";
            $fieldofstudy = $this->input->post('12_fieldofstudy') <> null ? $this->input->post('12_fieldofstudy') : "";
            $kota = $this->input->post('12_kota') <> null ? $this->input->post('12_kota') : "";
            $negara = $this->input->post('12_negara') <> null ? $this->input->post('12_negara') : "";
            $provinsi = $this->input->post('12_provinsi') <> null ? $this->input->post('12_provinsi') : "";
            $tahunlulus = $this->input->post('12_tahunlulus') <> null ? $this->input->post('12_tahunlulus') : "";
            $title = $this->input->post('12_title') <> null ? $this->input->post('12_title') : "";
            $activities = $this->input->post('12_activities') <> null ? $this->input->post('12_activities') : "";
            $description = $this->input->post('12_description') <> null ? $this->input->post('12_description') : "";
            $score = $this->input->post('12_score') <> null ? $this->input->post('12_score') : "";
            $judicium = $this->input->post('12_judicium') <> null ? $this->input->post('12_judicium') : "";
            $edu_image_url = $this->input->post('12_edu_image_url') <> null ? $this->input->post('12_edu_image_url') : "";

            $birthdate_ts = strtotime("$tahunlulus[$j]-1-1");
            $birthdate_ts2 = strtotime(date("Y-m-d"));

            $diff = abs($birthdate_ts2 - $birthdate_ts);
            $tempidx = 0;
            $years = floor($diff / (365 * 60 * 60 * 24));

            $data['bp_12'] = $this->main_mod->msrwhere('m_bakuan_penilaian', array('faip_num' => '12', 'faip_type' => 'p', 'condition' => $degree[$j]), 'id', 'desc')->result();
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
            if ($score[$j] <= 3) $tempqr = 2;

            if ($score[$j] > 3) $tempqr = 3;
            $p = $tempidx;
            $q = $tempqr;
            $r = $tempqr;
            $t = $p * $q * $r;


            try {
              $row = array(
                'faip_id' => $id,
                'school' => $school[$j],
                'school_type' => $degree[$j],
                'fakultas' => $fakultas[$j],
                'jurusan' => $fieldofstudy[$j],
                'kota' => $kota[$j],
                'provinsi' => $provinsi[$j],
                'negara' => $negara[$j],
                'tahun_lulus' => $tahunlulus[$j],
                'title' => $title[$j],
                'judul' => $activities[$j],
                'uraian' => $description[$j],
                'score' => $score[$j],

                'kompetensi' => 'W.2',

                'p' => $p,
                'q' => $q,
                'r' => $r,
                't' => $t,

                'attachment' => (isset($edu_image_url[$j]) ? $edu_image_url[$j] : ''),
                'createdby' => $createdby_id,
                'modifiedby' => $modifiedby_id,
                'modifieddate' => date('Y-m-d H:i:s'),
                'status' => 1,

              );
              if ($val != "" && $is_submit == "1") {
                $where = array(
                  "id" => $val
                );
                //$update = $this->main_mod->update('user_faip_12',$where,$row);		
              } else $insert = $this->main_mod->insert('user_faip_12', $row);

              //SYNC user_edu
              if ($is_submit == "1") {
                $o_data = $this->main_mod->msrwhere('user_edu', array('user_id' => $user_id, 'status' => 1, 'school' => $school[$j], 'fieldofstudy' => $fieldofstudy[$j]), 'id', 'asc')->result();
                if (count($o_data) > 0) {
                  $temp = $this->main_mod->update(
                    'user_edu',
                    array('user_id' => $user_id, 'status' => 1, 'school' => $school[$j], 'fieldofstudy' => $fieldofstudy[$j]),
                    array(
                      'school' => $school[$j],
                      'degree' => $degree[$j],
                      'fieldofstudy' => $fieldofstudy[$j],
                      'mayor' => $fakultas[$j],
                      'enddate' => $tahunlulus[$j],
                      'title' => $title[$j],
                      'score' => $score[$j],
                      'activities' => $activities[$j],
                      'description' => $description[$j],
                      'attachment' => (isset($edu_image_url[$j]) ? $edu_image_url[$j] : '')
                    )
                  );
                } else {
                  $this->main_mod->insert('user_edu', array(
                    'user_id' => $user_id,
                    'status' => 1,
                    'school' => $school[$j],
                    'degree' => $degree[$j],
                    'fieldofstudy' => $fieldofstudy[$j],
                    'mayor' => $fakultas[$j],
                    'enddate' => $tahunlulus[$j],
                    'title' => $title[$j],
                    'score' => $score[$j],
                    'activities' => $activities[$j],
                    'description' => $description[$j],
                    'attachment' => (isset($edu_image_url[$j]) ? $edu_image_url[$j] : ''),
                    'createdby' => $createdby_id,
                  ));
                }
              }
              //SYNC	
            } catch (Exception $e) {
              print_r($e);
            }
            $j++;
          }
        }
      }

      //UPDATE 13_
      //print_r($orgkomp);
      if ($is_submit == "1" || $save_partial == '13') {
        if ($faip->need_revisi == '1') {
          $temp = $this->main_mod->update('user_faip_13', array("faip_id" => $id), array("status" => 0));
        } else {
          if ($is_submit == "1") $temp = ''; //$temp = $this->main_mod->update('user_faip_13',array("faip_id" => $id),array("status" => 0));	
          else $temp = $this->main_mod->delete('user_faip_13', "faip_id", $id);
        }
        $nama_org_m = $this->input->post('13_nama_org') <> null ? $this->input->post('13_nama_org') : "";
        if (is_array($nama_org_m)) {
          $j = 0;
          foreach ($nama_org_m as $val) {
            $tempid = '';
            $i = 0;
            //$a13_komp = $this->input->post('13_komp['.($j+1).']')<>null?$this->input->post('13_komp['.($j+1).']'):"";
            $a13_komp = $this->input->post('13_komp') <> null ? $this->input->post('13_komp') : "";
            if (is_array($a13_komp)) {
              $a13_komp = array_merge($a13_komp);
              $a13_komp = $a13_komp[$j];
            }

            if ($is_submit != "1" && !is_array($a13_komp[0]))
              $a13_komp = explode(",", $a13_komp[0]);

            if (!is_array($a13_komp)) {
              $a13_komp[] = null;
            }

            if (is_array($a13_komp)) {
              foreach ($a13_komp as $kompetensi) {
                $nama_org = $this->input->post('13_nama_org') <> null ? $this->input->post('13_nama_org') : "";
                $jenis = $this->input->post('13_jenis') <> null ? $this->input->post('13_jenis') : "";
                $tingkat = $this->input->post('13_tingkat') <> null ? $this->input->post('13_tingkat') : "";
                $lingkup = $this->input->post('13_lingkup') <> null ? $this->input->post('13_lingkup') : "";
                $jabatan = $this->input->post('13_jabatan') <> null ? $this->input->post('13_jabatan') : "";
                $tempat = $this->input->post('13_tempat') <> null ? $this->input->post('13_tempat') : "";
                $provinsi = $this->input->post('13_provinsi') <> null ? $this->input->post('13_provinsi') : "";
                $negara = $this->input->post('13_negara') <> null ? $this->input->post('13_negara') : "";
                $aktifitas = $this->input->post('13_aktifitas') <> null ? $this->input->post('13_aktifitas') : "";
                $startdate = $this->input->post('13_startdate') <> null ? $this->input->post('13_startdate') : "";
                $startyear = $this->input->post('13_startyear') <> null ? $this->input->post('13_startyear') : "";
                $enddate = $this->input->post('13_enddate') <> null ? $this->input->post('13_enddate') : "";
                $endyear = $this->input->post('13_endyear') <> null ? $this->input->post('13_endyear') : "";
                $is_present = $this->input->post('13_workx') <> null ? $this->input->post('13_workx') : "";
                $org_image_url = $this->input->post('13_org_image_url') <> null ? $this->input->post('13_org_image_url') : "";

                $birthdate_ts = strtotime("$startyear[$j]-$startdate[$j]-1");
                $birthdate_ts2 = '';
                if ($is_present[$j] == "1")
                  $birthdate_ts2 = strtotime(date("Y-m-d"));
                else
                  $birthdate_ts2 = strtotime("$endyear[$j]-$enddate[$j]-1");
                $diff = abs($birthdate_ts2 - $birthdate_ts);
                $tempidx = 0;
                $years = floor($diff / (365 * 60 * 60 * 24));

                $data['bp_13'] = $this->main_mod->msrwhere('m_bakuan_penilaian', array('faip_num' => '13', 'faip_type' => 'p', 'condition' => 'Non PII'), 'id', 'desc')->result();
                //echo $years.'<br />';
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

                if ($jenis[$j] == 'Organisasi PII')
                  $tempidx = 4;

                $p = $tempidx;
                $q = (isset($jabatan[$j]) ? ($jabatan[$j] != '' ? $jabatan[$j] : 0) : 0);
                $r = (isset($tingkat[$j]) ? ($tingkat[$j] != '' ? $tingkat[$j] : 0) : 0);
                $t = $p * $q * $r;

                try {
                  $row = array(
                    'faip_id' => $id,
                    'parent' => ($i == 0) ? 0 : $tempid,
                    'nama_org' => ($i == 0) ? $nama_org[$j] : '',
                    'jenis' => ($i == 0) ? $jenis[$j] : '',
                    'lingkup' => ($i == 0) ? $lingkup[$j] : '',
                    'jabatan' => ($i == 0) ? $jabatan[$j] : '',
                    'tempat' => ($i == 0) ? $tempat[$j] : '',
                    'provinsi' => ($i == 0) ? $provinsi[$j] : '',
                    'negara' => ($i == 0) ? $negara[$j] : '',
                    'aktifitas' => ($i == 0) ? $aktifitas[$j] : '',
                    'startdate' => ($i == 0) ? $startdate[$j] : '',
                    'startyear' => ($i == 0) ? $startyear[$j] : '',
                    'enddate' => ($i == 0) ? $enddate[$j] : '',
                    'endyear' => ($i == 0) ? $endyear[$j] : '',
                    'is_present' => ($i == 0) ? ($is_present[$j] == "1" ? "1" : "0") : '',
                    'tingkat' => ($i == 0) ? $tingkat[$j] : '',
                    'kompetensi' => $kompetensi,

                    'p' => $p,
                    'q' => $q,
                    'r' => $r,
                    't' => $t,
                    'attachment' => (($i == 0) ? (isset($org_image_url[$j]) ? $org_image_url[$j] : '') : ''),
                    'createdby' => $createdby_id,

                    'modifiedby' => $modifiedby_id,
                    'modifieddate' => date('Y-m-d H:i:s'),
                    'status' => 1,
                  );

                  $insert = '';
                  if ($i == 0 && $id13[$j] != "") {

                    $temp_ = $this->main_mod->msrwhere('user_faip_13', array('id' => $id13[$j], 'status' => '0'), 'id', 'desc')->result();
                    if (isset($temp_[0])) {
                      $where = array(
                        "id" => $id13[$j],
                        'status' => '0'
                      );
                      $update = $this->main_mod->update('user_faip_13', $where, $row);
                      $insert = $id13[$j];
                    } else
                      $insert = $this->main_mod->insert('user_faip_13', $row);
                  } else {
                    if ($id13[$j] != "") {
                      $temp__ = $this->main_mod->msrwhere('user_faip_13', array('parent' => $id13[$j], 'status' => '0', 'kompetensi' => $kompetensi), 'id', 'desc')->result();

                      if (isset($temp__[0])) {
                        $where = array(
                          "parent" => $id13[$j],
                          "status" => '0',
                          'kompetensi' => $kompetensi
                        );
                        $update = $this->main_mod->update('user_faip_13', $where, $row);
                        $insert = $id13[$j];
                      } else $insert = $this->main_mod->insert('user_faip_13', $row);
                    } else $insert = $this->main_mod->insert('user_faip_13', $row);
                  }

                  $te = '';
                  if ($jabatan[$j] == '2')
                    $te = 'Anggota biasa';
                  else if ($jabatan[$j] == '3')
                    $te = 'Anggota pengurus';
                  else if ($jabatan[$j] == '4')
                    $te = 'Pimpinan';
                  $se = '';
                  if ($tingkat[$j] == '1')
                    $se = 'Organisasi lokal (bukan Nasional)';
                  else if ($tingkat[$j] == '2')
                    $se = 'Organisasi Nasional';
                  else if ($tingkat[$j] == '3')
                    $se = 'Organisasi Regional';
                  else if ($tingkat[$j] == '4')
                    $se = 'Organisasi Internasional';

                  //SYNC user_org
                  if ($is_submit == "1") {
                    $o_data = $this->main_mod->msrwhere('user_org', array('user_id' => $user_id, 'status' => 1, 'organization' => $nama_org[$j], 'startyear' => $startyear[$j]), 'id', 'asc')->result();
                    if (count($o_data) > 0) {
                      $temp = $this->main_mod->update(
                        'user_org',
                        array('user_id' => $user_id, 'status' => 1, 'organization' => $nama_org[$j], 'startyear' => $startyear[$j]),
                        array(
                          'organization' => $nama_org[$j],
                          'jenis' => $jenis[$j],
                          'position' => $te,
                          'tingkat' => $se,
                          'lingkup' => $lingkup[$j],
                          'occupation' => $tempat[$j],
                          'negara' => $negara[$j],
                          'provinsi' => $provinsi[$j],
                          'startmonth' => $startdate[$j],
                          'startyear' => $startyear[$j],
                          'endmonth' => $enddate[$j],
                          'endyear' => $endyear[$j],
                          'is_present' => ($is_present[$j] == "1" ? "1" : "0"),
                          'description' => $aktifitas[$j],
                          'attachment' => (isset($org_image_url[$j]) ? $org_image_url[$j] : '')
                        )
                      );
                    } else {
                      $this->main_mod->insert('user_org', array(
                        'user_id' => $user_id,
                        'status' => 1,
                        'organization' => $nama_org[$j],
                        'jenis' => $jenis[$j],
                        'position' => $te,
                        'tingkat' => $se,
                        'lingkup' => $lingkup[$j],
                        'occupation' => $tempat[$j],
                        'negara' => $negara[$j],
                        'provinsi' => $provinsi[$j],
                        'startmonth' => $startdate[$j],
                        'startyear' => $startyear[$j],
                        'endmonth' => $enddate[$j],
                        'endyear' => $endyear[$j],
                        'is_present' => ($is_present[$j] == "1" ? "1" : "0"),
                        'description' => $aktifitas[$j],
                        'attachment' => (isset($org_image_url[$j]) ? $org_image_url[$j] : ''),
                        'createdby' => $createdby_id,
                      ));
                    }
                  }
                  //SYNC

                  if ($i == 0) $tempid = $insert;
                } catch (Exception $e) {
                  print_r($e);
                }
                $i++;
              }

              if ($faip->need_revisi == '1') {
                $temp = $this->main_mod->delete_where('user_faip_13', array("faip_id" => $id, 'status' => '0'));
              }
            }

            $j++;
          }
        }
      }

      //UPDATE 14_
      //print_r($orgkomp);
      if ($is_submit == "1" || $save_partial == '14') {
        if ($faip->need_revisi == '1') {
          $temp = $this->main_mod->update('user_faip_14', array("faip_id" => $id), array("status" => 0));
        } else {
          if ($is_submit == "1") $temp = ''; //$temp = $this->main_mod->update('user_faip_13',array("faip_id" => $id),array("status" => 0));	
          else $temp = $this->main_mod->delete('user_faip_14', "faip_id", $id);
        }

        $nama_14_m = $this->input->post('14_nama') <> null ? $this->input->post('14_nama') : "";
        if (is_array($nama_14_m)) {
          $j = 0;
          foreach ($nama_14_m as $val) {
            $tempid = '';
            $i = 0;
            //$a14_komp = $this->input->post('14_komp['.($j+1).']')<>null?$this->input->post('14_komp['.($j+1).']'):"";
            $a14_komp = $this->input->post('14_komp') <> null ? $this->input->post('14_komp') : "";
            if (is_array($a14_komp)) {
              $a14_komp = array_merge($a14_komp);
              $a14_komp = $a14_komp[$j];
            }
            if ($is_submit != "1" && !is_array($a14_komp[0]))
              $a14_komp = explode(",", $a14_komp[0]);

            if (!is_array($a14_komp)) {
              $a14_komp[] = null;
            }

            if (is_array($a14_komp)) {
              foreach ($a14_komp as $kompetensi) {
                $startdate = $this->input->post('14_startdate') <> null ? $this->input->post('14_startdate') : "";
                $startyear = $this->input->post('14_startyear') <> null ? $this->input->post('14_startyear') : "";
                $nama = $this->input->post('14_nama') <> null ? $this->input->post('14_nama') : "";
                $lembaga = $this->input->post('14_lembaga') <> null ? $this->input->post('14_lembaga') : "";
                $location = $this->input->post('14_location') <> null ? $this->input->post('14_location') : "";
                $provinsi = $this->input->post('14_provinsi') <> null ? $this->input->post('14_provinsi') : "";
                $negara = $this->input->post('14_negara') <> null ? $this->input->post('14_negara') : "";
                $tingkat = $this->input->post('14_tingkat') <> null ? $this->input->post('14_tingkat') : "";
                $tingkatlembaga = $this->input->post('14_tingkatlembaga') <> null ? $this->input->post('14_tingkatlembaga') : "";
                $award_image_url = $this->input->post('14_award_image_url') <> null ? $this->input->post('14_award_image_url') : "";

                $uraian = $this->input->post('14_uraian') <> null ? $this->input->post('14_uraian') : "";
                $tempidx = 0;
                $years = count($a14_komp);
                $data['bp_14'] = $this->main_mod->msrwhere('m_bakuan_penilaian', array('faip_num' => '14', 'faip_type' => 'p'), 'id', 'desc')->result();
                //echo $years.'<br />';
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
                $q = (isset($tingkat[$j]) ? ($tingkat[$j] != '' ? $tingkat[$j] : 0) : 0);
                $r = (isset($tingkatlembaga[$j]) ? ($tingkatlembaga[$j] != '' ? $tingkatlembaga[$j] : 0) : 0);
                $t = $p * $q * $r;
                try {
                  $row = array(
                    'faip_id' => $id,
                    'parent' => ($i == 0) ? 0 : $tempid,
                    'startdate' => ($i == 0) ? $startdate[$j] : '',
                    'startyear' => ($i == 0) ? $startyear[$j] : '',
                    'nama' => ($i == 0) ? $nama[$j] : '',
                    'lembaga' => ($i == 0) ? $lembaga[$j] : '',
                    'tingkat' => ($i == 0) ? $tingkat[$j] : '',
                    'tingkatlembaga' => ($i == 0) ? $tingkatlembaga[$j] : '',
                    'location' => ($i == 0) ? $location[$j] : '',
                    'provinsi' => ($i == 0) ? $provinsi[$j] : '',
                    'negara' => ($i == 0) ? $negara[$j] : '',
                    'uraian' => ($i == 0) ? $uraian[$j] : '',

                    'p' => $p,
                    'q' => $q,
                    'r' => $r,
                    't' => $t,

                    'attachment' => (($i == 0) ? (isset($award_image_url[$j]) ? $award_image_url[$j] : '') : ''),
                    'kompetensi' => $kompetensi,
                    'createdby' => $createdby_id,

                    'modifiedby' => $modifiedby_id,
                    'modifieddate' => date('Y-m-d H:i:s'),
                    'status' => 1,
                  );
                  $insert = '';
                  /*if($i==0 && $id14[$j]!="" && $is_submit=="1"){
											$where = array(
												"id" => $id14[$j]
											);
											//$update = $this->main_mod->update('user_faip_14',$where,$row);	
											$insert = $id14[$j];								
										}		
										else $insert = $this->main_mod->insert('user_faip_14',$row);	
										*/

                  if ($i == 0 && $id14[$j] != "") {

                    $temp_ = $this->main_mod->msrwhere('user_faip_14', array('id' => $id14[$j], 'status' => '0'), 'id', 'desc')->result();
                    if (isset($temp_[0])) {
                      $where = array(
                        "id" => $id14[$j],
                        'status' => '0'
                      );
                      $update = $this->main_mod->update('user_faip_14', $where, $row);
                      $insert = $id14[$j];
                    } else
                      $insert = $this->main_mod->insert('user_faip_14', $row);
                  } else {
                    if ($id14[$j] != "") {
                      $temp__ = $this->main_mod->msrwhere('user_faip_14', array('parent' => $id14[$j], 'status' => '0', 'kompetensi' => $kompetensi), 'id', 'desc')->result();

                      if (isset($temp__[0])) {
                        $where = array(
                          "parent" => $id14[$j],
                          "status" => '0',
                          'kompetensi' => $kompetensi
                        );
                        $update = $this->main_mod->update('user_faip_14', $where, $row);
                        $insert = $id14[$j];
                      } else $insert = $this->main_mod->insert('user_faip_14', $row);
                    } else $insert = $this->main_mod->insert('user_faip_14', $row);
                  }


                  $te = '';
                  if ($tingkat[$j] == '2')
                    $te = 'Tingkatan Muda/pemula';
                  else if ($tingkat[$j] == '3')
                    $te = 'Tingkatan Madya';
                  else if ($tingkat[$j] == '4')
                    $te = 'Tingkatan Utama';
                  $se = '';
                  if ($tingkatlembaga[$j] == '1')
                    $se = 'Penghargaan Lokal';
                  else if ($tingkatlembaga[$j] == '2')
                    $se = 'Penghargaan Nasional';
                  else if ($tingkatlembaga[$j] == '3')
                    $se = 'Penghargaan Regional';
                  else if ($tingkatlembaga[$j] == '4')
                    $se = 'Penghargaan Internasional';

                  //SYNC user_award
                  if ($is_submit == "1") {
                    $o_data = $this->main_mod->msrwhere('user_award', array('user_id' => $user_id, 'status' => 1, 'name' => $nama[$j], 'startyear' => $startyear[$j]), 'id', 'asc')->result();
                    if (count($o_data) > 0) {
                      $temp = $this->main_mod->update(
                        'user_award',
                        array('user_id' => $user_id, 'status' => 1, 'name' => $nama[$j], 'startyear' => $startyear[$j]),
                        array(
                          'name' => $nama[$j],
                          'issue' => $lembaga[$j],
                          'pemberi' => $se,
                          'tingkat' => $te,

                          'location' => $location[$j],
                          'negara' => $negara[$j],
                          'provinsi' => $provinsi[$j],

                          'startmonth' => $startdate[$j],
                          'startyear' => $startyear[$j],
                          'description' => $uraian[$j],
                          'attachment' => (isset($award_image_url[$j]) ? $award_image_url[$j] : '')
                        )
                      );
                    } else {
                      $this->main_mod->insert('user_award', array(
                        'user_id' => $user_id,
                        'status' => 1,
                        'name' => $nama[$j],
                        'issue' => $lembaga[$j],
                        'pemberi' => $se,
                        'tingkat' => $te,

                        'location' => $location[$j],
                        'negara' => $negara[$j],
                        'provinsi' => $provinsi[$j],

                        'startmonth' => $startdate[$j],
                        'startyear' => $startyear[$j],
                        'description' => $uraian[$j],
                        'attachment' => (isset($award_image_url[$j]) ? $award_image_url[$j] : ''),
                        'createdby' => $createdby_id,
                      ));
                    }
                  }
                  //SYNC


                  if ($i == 0) $tempid = $insert;
                } catch (Exception $e) {
                  print_r($e);
                }
                $i++;
              }

              if ($faip->need_revisi == '1') {
                $temp = $this->main_mod->delete_where('user_faip_14', array("faip_id" => $id, 'status' => '0'));
              }
            }

            $j++;
          }
        }
      }

      //UPDATE 15_
      //print_r($orgkomp);
      if ($is_submit == "1" || $save_partial == '15') {
        if ($faip->need_revisi == '1') {
          $temp = $this->main_mod->update('user_faip_15', array("faip_id" => $id), array("status" => 0));
        } else {
          if ($is_submit == "1") $temp = ''; //$temp = $this->main_mod->update('user_faip_13',array("faip_id" => $id),array("status" => 0));	
          else $temp = $this->main_mod->delete('user_faip_15', "faip_id", $id);
        }

        $nama_15_m = $this->input->post('15_nama') <> null ? $this->input->post('15_nama') : "";
        if (is_array($nama_15_m)) {
          $j = 0;
          foreach ($nama_15_m as $val) {
            $tempid = '';
            $i = 0;
            //$a15_komp = $this->input->post('15_komp['.($j+1).']')<>null?$this->input->post('15_komp['.($j+1).']'):"";
            $a15_komp = $this->input->post('15_komp') <> null ? $this->input->post('15_komp') : "";
            if (is_array($a15_komp)) {
              $a15_komp = array_merge($a15_komp);
              $a15_komp = $a15_komp[$j];
            }
            if ($is_submit != "1" && !is_array($a15_komp[0]))
              $a15_komp = explode(",", $a15_komp[0]);

            if (!is_array($a15_komp)) {
              $a15_komp[] = null;
            }

            if (is_array($a15_komp)) {
              foreach ($a15_komp as $kompetensi) {
                //$perioda = $this->input->post('15_perioda')<>null?$this->input->post('15_perioda'):"";
                $nama = $this->input->post('15_nama') <> null ? $this->input->post('15_nama') : "";
                $lembaga = $this->input->post('15_lembaga') <> null ? $this->input->post('15_lembaga') : "";
                $location = $this->input->post('15_location') <> null ? $this->input->post('15_location') : "";
                $provinsi = $this->input->post('15_provinsi') <> null ? $this->input->post('15_provinsi') : "";
                $negara = $this->input->post('15_negara') <> null ? $this->input->post('15_negara') : "";
                $tingkat = $this->input->post('15_tingkat') <> null ? $this->input->post('15_tingkat') : "";
                $jam = $this->input->post('15_jam') <> null ? $this->input->post('15_jam') : "";
                $startdate = $this->input->post('15_startdate') <> null ? $this->input->post('15_startdate') : "";
                $startyear = $this->input->post('15_startyear') <> null ? $this->input->post('15_startyear') : "";
                $enddate = $this->input->post('15_enddate') <> null ? $this->input->post('15_enddate') : "";
                $endyear = $this->input->post('15_endyear') <> null ? $this->input->post('15_endyear') : "";
                $is_present = $this->input->post('15_workx') <> null ? $this->input->post('15_workx') : "";
                $jam = $this->input->post('15_jam') <> null ? $this->input->post('15_jam') : "";
                $course_image_url = $this->input->post('15_course_image_url') <> null ? $this->input->post('15_course_image_url') : "";
                //$lic = $this->input->post('15_lic')<>null?$this->input->post('15_lic'):"";	
                //$url = $this->input->post('15_url')<>null?$this->input->post('15_url'):"";							
                $uraian = $this->input->post('15_uraian') <> null ? $this->input->post('15_uraian') : "";

                $tempidx = 0;
                $years = count((array)$val);
                $data['bp_15'] = $this->main_mod->msrwhere('m_bakuan_penilaian', array('faip_num' => '15', 'faip_type' => 'p'), 'id', 'desc')->result();
                //echo $years.'<br />';
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
                $q = (isset($jam[$j]) ? ($jam[$j] != '' ? $jam[$j] : 0) : 0);
                $r = (isset($tingkat[$j]) ? ($tingkat[$j] != '' ? $tingkat[$j] : 0) : 0);
                $t = $p * $q * $r;
                try {
                  $row = array(
                    'faip_id' => $id,
                    'parent' => ($i == 0) ? 0 : $tempid,
                    //'perioda' => ($i==0)?$perioda[$j]:'',
                    'nama' => ($i == 0) ? $nama[$j] : '',
                    'lembaga' => ($i == 0) ? $lembaga[$j] : '',
                    'jam' => ($i == 0) ? $jam[$j] : '',
                    'tingkat' => ($i == 0) ? $tingkat[$j] : '',
                    'location' => ($i == 0) ? $location[$j] : '',
                    'provinsi' => ($i == 0) ? $provinsi[$j] : '',
                    'negara' => ($i == 0) ? $negara[$j] : '',
                    'uraian' => ($i == 0) ? $uraian[$j] : '',

                    'startdate' => ($i == 0) ? $startdate[$j] : '',
                    'startyear' => ($i == 0) ? $startyear[$j] : '',
                    'enddate' => ($i == 0) ? $enddate[$j] : '',
                    'endyear' => ($i == 0) ? $endyear[$j] : '',
                    'is_present' => ($i == 0) ? ($is_present[$j] == "1" ? "1" : "0") : '',
                    //'lic' => ($i==0)?$lic[$j]:'',
                    //'url' => ($i==0)?$url[$j]:'',
                    //'jam' => ($i==0)?$jam[$j]:'',


                    'p' => $p,
                    'q' => $q,
                    'r' => $r,
                    't' => $t,

                    'attachment' => (($i == 0) ? (isset($course_image_url[$j]) ? $course_image_url[$j] : '') : ''),
                    'kompetensi' => $kompetensi,
                    'createdby' => $this->session->userdata('user_id'),

                    'modifiedby' => $this->session->userdata('user_id'),
                    'modifieddate' => date('Y-m-d H:i:s'),
                    'status' => 1,
                  );
                  $insert = '';
                  /*if($i==0 && $id15[$j]!="" && $is_submit=="1"){
											$where = array(
												"id" => $id15[$j]
											);
											//$update = $this->main_mod->update('user_faip_15',$where,$row);	
											$insert = $id15[$j];								
										}		
										else $insert = $this->main_mod->insert('user_faip_15',$row);	*/

                  if ($i == 0 && $id15[$j] != "") {

                    $temp_ = $this->main_mod->msrwhere('user_faip_15', array('id' => $id15[$j], 'status' => '0'), 'id', 'desc')->result();
                    if (isset($temp_[0])) {
                      $where = array(
                        "id" => $id15[$j],
                        'status' => '0'
                      );
                      $update = $this->main_mod->update('user_faip_15', $where, $row);
                      $insert = $id15[$j];
                    } else
                      $insert = $this->main_mod->insert('user_faip_15', $row);
                  } else {
                    if ($id15[$j] != "") {
                      $temp__ = $this->main_mod->msrwhere('user_faip_15', array('parent' => $id15[$j], 'status' => '0', 'kompetensi' => $kompetensi), 'id', 'desc')->result();

                      if (isset($temp__[0])) {
                        $where = array(
                          "parent" => $id15[$j],
                          "status" => '0',
                          'kompetensi' => $kompetensi
                        );
                        $update = $this->main_mod->update('user_faip_15', $where, $row);
                        $insert = $id15[$j];
                      } else $insert = $this->main_mod->insert('user_faip_15', $row);
                    } else $insert = $this->main_mod->insert('user_faip_15', $row);
                  }

                  $te = '';
                  if ($tingkat[$j] == '2')
                    $te = 'Tingkat Dasar (Fundamental)';
                  else if ($tingkat[$j] == '3')
                    $te = 'Tingkat Lanjutan (Advanced)';
                  $se = '';
                  if ($jam[$j] == '1')
                    $se = 'Lama pendidikan s/d 36 Jam';
                  else if ($jam[$j] == '2')
                    $se = 'Lama pendidikan 36 – 100 Jam';
                  else if ($jam[$j] == '3')
                    $se = 'Lama pendidikan 100 – 240 Jam';
                  else if ($jam[$j] == '4')
                    $se = '> dari 240 Jam';

                  //SYNC user_course
                  if ($is_submit == "1") {
                    $o_data = $this->main_mod->msrwhere('user_course', array('user_id' => $this->session->userdata('user_id'), 'status' => 1, 'coursename' => $nama[$j], 'startyear' => $startyear[$j]), 'id', 'asc')->result();
                    if (count($o_data) > 0) {
                      $temp = $this->main_mod->update(
                        'user_course',
                        array('user_id' => $this->session->userdata('user_id'), 'status' => 1, 'coursename' => $nama[$j], 'startyear' => $startyear[$j]),
                        array(
                          'coursename' => $nama[$j],
                          'hour' => $se,
                          'location' => $location[$j],
                          'provinsi' => $provinsi[$j],
                          'negara' => $negara[$j],
                          'type' => 'Teknik dan Profesi Keinsinyuran',
                          'tingkat' => $te,
                          'courseorg' => $lembaga[$j],
                          'startmonth' => $startdate[$j],
                          'startyear' => $startyear[$j],
                          'endmonth' => $enddate[$j],
                          'endyear' => $endyear[$j],
                          'attachment' => (isset($course_image_url[$j]) ? $course_image_url[$j] : '')
                        )
                      );
                    } else {
                      $this->main_mod->insert('user_course', array(
                        'user_id' => $this->session->userdata('user_id'),
                        'status' => 1,
                        'coursename' => $nama[$j],
                        'hour' => $se,
                        'location' => $location[$j],
                        'provinsi' => $provinsi[$j],
                        'negara' => $negara[$j],
                        'type' => 'Teknik dan Profesi Keinsinyuran',
                        'tingkat' => $te,
                        'courseorg' => $lembaga[$j],
                        'startmonth' => $startdate[$j],
                        'startyear' => $startyear[$j],
                        'endmonth' => $enddate[$j],
                        'endyear' => $endyear[$j],
                        'attachment' => (isset($course_image_url[$j]) ? $course_image_url[$j] : ''),
                        'createdby' => $this->session->userdata('user_id'),
                      ));
                    }
                  }
                  //SYNC

                  if ($i == 0) $tempid = $insert;
                } catch (Exception $e) {
                  print_r($e);
                }
                $i++;
              }

              if ($faip->need_revisi == '1') {
                $temp = $this->main_mod->delete_where('user_faip_15', array("faip_id" => $id, 'status' => '0'));
              }
            }

            $j++;
          }
        }
      }

      //UPDATE 16_
      //print_r($orgkomp);
      if ($is_submit == "1" || $save_partial == '16') {
        if ($faip->need_revisi == '1') {
          $temp = $this->main_mod->update('user_faip_16', array("faip_id" => $id), array("status" => 0));
        } else {
          if ($is_submit == "1") $temp = ''; //$temp = $this->main_mod->update('user_faip_13',array("faip_id" => $id),array("status" => 0));	
          else $temp = $this->main_mod->delete('user_faip_16', "faip_id", $id);
        }

        $nama_16_m = $this->input->post('16_nama') <> null ? $this->input->post('16_nama') : "";
        if (is_array($nama_16_m)) {
          $j = 0;
          foreach ($nama_16_m as $val) {
            $tempid = '';
            $i = 0;
            //$a16_komp = $this->input->post('16_komp['.($j+1).']')<>null?$this->input->post('16_komp['.($j+1).']'):"";
            $a16_komp = $this->input->post('16_komp') <> null ? $this->input->post('16_komp') : "";
            if (is_array($a16_komp)) {
              $a16_komp = array_merge($a16_komp);
              $a16_komp = $a16_komp[$j];
            }
            if ($is_submit != "1" && !is_array($a16_komp[0]))
              $a16_komp = explode(",", $a16_komp[0]);

            if (!is_array($a16_komp)) {
              $a16_komp[] = null;
            }

            if (is_array($a16_komp)) {
              foreach ($a16_komp as $kompetensi) {
                $nama = $this->input->post('16_nama') <> null ? $this->input->post('16_nama') : "";
                $lembaga = $this->input->post('16_lembaga') <> null ? $this->input->post('16_lembaga') : "";
                $location = $this->input->post('16_location') <> null ? $this->input->post('16_location') : "";
                $provinsi = $this->input->post('16_provinsi') <> null ? $this->input->post('16_provinsi') : "";
                $negara = $this->input->post('16_negara') <> null ? $this->input->post('16_negara') : "";
                $tingkat = $this->input->post('16_tingkat') <> null ? $this->input->post('16_tingkat') : "";
                $jam = $this->input->post('16_jam') <> null ? $this->input->post('16_jam') : "";
                $startdate = $this->input->post('16_startdate') <> null ? $this->input->post('16_startdate') : "";
                $startyear = $this->input->post('16_startyear') <> null ? $this->input->post('16_startyear') : "";
                $enddate = $this->input->post('16_enddate') <> null ? $this->input->post('16_enddate') : "";
                $endyear = $this->input->post('16_endyear') <> null ? $this->input->post('16_endyear') : "";
                $is_present = $this->input->post('16_workx') <> null ? $this->input->post('16_workx') : "";
                $jam = $this->input->post('16_jam') <> null ? $this->input->post('16_jam') : "";
                $lic = $this->input->post('16_lic') <> null ? $this->input->post('16_lic') : "";
                $url = $this->input->post('16_url') <> null ? $this->input->post('16_url') : "";
                $uraian = $this->input->post('16_uraian') <> null ? $this->input->post('16_uraian') : "";
                $cert_image_url = $this->input->post('16_cert_image_url') <> null ? $this->input->post('16_cert_image_url') : "";

                $tempidx = 0;
                $years = count((array)$val);
                $data['bp_16'] = $this->main_mod->msrwhere('m_bakuan_penilaian', array('faip_num' => '16', 'faip_type' => 'p'), 'id', 'desc')->result();
                //echo $years.'<br />';
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
                $q = (isset($jam[$j]) ? ($jam[$j] != '' ? $jam[$j] : 0) : 0);
                $r = (isset($tingkat[$j]) ? ($tingkat[$j] != '' ? $tingkat[$j] : 0) : 0);
                $t = $p * $q * $r;
                try {
                  $row = array(
                    'faip_id' => $id,
                    'parent' => ($i == 0) ? 0 : $tempid,
                    //'perioda' => ($i==0)?$perioda[$j]:'',
                    'nama' => ($i == 0) ? $nama[$j] : '',
                    'lembaga' => ($i == 0) ? $lembaga[$j] : '',
                    'jam' => ($i == 0) ? $jam[$j] : '',
                    'tingkat' => ($i == 0) ? $tingkat[$j] : '',
                    'location' => ($i == 0) ? $location[$j] : '',
                    'provinsi' => ($i == 0) ? $provinsi[$j] : '',
                    'negara' => ($i == 0) ? $negara[$j] : '',
                    'uraian' => ($i == 0) ? $uraian[$j] : '',

                    'startdate' => ($i == 0) ? $startdate[$j] : '',
                    'startyear' => ($i == 0) ? $startyear[$j] : '',
                    'enddate' => ($i == 0) ? $enddate[$j] : '',
                    'endyear' => ($i == 0) ? $endyear[$j] : '',
                    'is_present' => ($i == 0) ? ($is_present[$j] == "1" ? "1" : "0") : '',
                    //'lic' => ($i==0)?$lic[$j]:'',
                    //'url' => ($i==0)?$url[$j]:'',
                    //'jam' => ($i==0)?$jam[$j]:'',

                    'p' => $p,
                    'q' => $q,
                    'r' => $r,
                    't' => $t,

                    'kompetensi' => $kompetensi,
                    'createdby' => $this->session->userdata('user_id'),
                    'attachment' => (($i == 0) ? (isset($cert_image_url[$j]) ? $cert_image_url[$j] : '') : ''),
                    'modifiedby' => $this->session->userdata('user_id'),
                    'modifieddate' => date('Y-m-d H:i:s'),
                    'status' => 1,
                  );
                  $insert = '';
                  /*if($i==0 && $id16[$j]!="" && $is_submit=="1"){
											$where = array(
												"id" => $id16[$j]
											);
											//$update = $this->main_mod->update('user_faip_16',$where,$row);	
											$insert = $id16[$j];								
										}		
										else $insert = $this->main_mod->insert('user_faip_16',$row);	*/

                  if ($i == 0 && $id16[$j] != "") {

                    $temp_ = $this->main_mod->msrwhere('user_faip_16', array('id' => $id16[$j], 'status' => '0'), 'id', 'desc')->result();
                    if (isset($temp_[0])) {
                      $where = array(
                        "id" => $id16[$j],
                        'status' => '0'
                      );
                      $update = $this->main_mod->update('user_faip_16', $where, $row);
                      $insert = $id16[$j];
                    } else
                      $insert = $this->main_mod->insert('user_faip_16', $row);
                  } else {
                    if ($id16[$j] != "") {
                      $temp__ = $this->main_mod->msrwhere('user_faip_16', array('parent' => $id16[$j], 'status' => '0', 'kompetensi' => $kompetensi), 'id', 'desc')->result();

                      if (isset($temp__[0])) {
                        $where = array(
                          "parent" => $id16[$j],
                          "status" => '0',
                          'kompetensi' => $kompetensi
                        );
                        $update = $this->main_mod->update('user_faip_16', $where, $row);
                        $insert = $id16[$j];
                      } else $insert = $this->main_mod->insert('user_faip_16', $row);
                    } else $insert = $this->main_mod->insert('user_faip_16', $row);
                  }

                  //SYNC user_cert
                  /*$o_data=$this->main_mod->msrwhere('user_cert',array('user_id'=>$this->session->userdata('user_id'),'status'=>1,'cert_name'=>$nama[$j],'startyear'=>$startyear[$j]),'id','asc')->result();
										if(count($o_data)>0) 
										{
											$temp = $this->main_mod->update('user_cert',array('user_id'=>$this->session->userdata('user_id'),'status'=>1,'cert_name'=>$nama[$j],'startyear'=>$startyear[$j]),
											array(	'cert_name' => $nama[$j],
													'cert_auth' => $lembaga[$j],
													'startmonth' => $startdate[$j],
													'startyear' => $startyear[$j],
													'endmonth' => $enddate[$j],
													'endyear' => $endyear[$j],
													'attachment' => $cert_image_url[$j]
													));
										}
										else{
											$this->main_mod->insert('user_cert',array(
													'user_id'=>$this->session->userdata('user_id'),
													'status'=>1,
													'cert_name' => $nama[$j],
													'cert_auth' => $lembaga[$j],
													'startmonth' => $startdate[$j],
													'startyear' => $startyear[$j],
													'endmonth' => $enddate[$j],
													'endyear' => $endyear[$j],
													'attachment' => $cert_image_url[$j],
													'createdby' => $this->session->userdata('user_id'),
													));	
										}*/
                  //SYNC

                  if ($i == 0) $tempid = $insert;
                } catch (Exception $e) {
                  print_r($e);
                }
                $i++;
              }

              if ($faip->need_revisi == '1') {
                $temp = $this->main_mod->delete_where('user_faip_16', array("faip_id" => $id, 'status' => '0'));
              }
            }

            $j++;
          }
        }
      }

      //UPDATE 21_
      //print_r($orgkomp);

      if ($is_submit == "1" || $save_partial == '21') {
        if ($is_submit == "1") $temp = ''; //$temp =$this->main_mod->update('user_faip_21',array("faip_id" => $id),array("status" => 0));	
        else $temp = $this->main_mod->delete('user_faip_21', "faip_id", $id);

        if (is_array($id21)) {

          $t = count($id21);

          if ($t == 1) $t = 35;
          else if ($t == 2) $t = 40;
          else if ($t == 3) $t = 45;
          else if ($t > 3) $t = 50;

          $j = 0;
          foreach ($id21 as $val) {
            //$tempid='';$i=0;
            //foreach($val as $kompetensi){
            $alamat = $this->input->post('21_alamat') <> null ? $this->input->post('21_alamat') : "";
            $lembaga = $this->input->post('21_lembaga') <> null ? $this->input->post('21_lembaga') : "";
            $kota = $this->input->post('21_kota') <> null ? $this->input->post('21_kota') : "";
            $provinsi = $this->input->post('21_provinsi') <> null ? $this->input->post('21_provinsi') : "";
            $negara = $this->input->post('21_negara') <> null ? $this->input->post('21_negara') : "";
            $notelp = $this->input->post('21_notelp') <> null ? $this->input->post('21_notelp') : "";
            $email = $this->input->post('21_email') <> null ? $this->input->post('21_email') : "";
            $nama = $this->input->post('21_nama') <> null ? $this->input->post('21_nama') : "";
            $hubungan = $this->input->post('21_hubungan') <> null ? $this->input->post('21_hubungan') : "";
            try {
              $row = array(
                'faip_id' => $id,
                //'parent' => 0:$tempid,
                'alamat' => $alamat[$j],
                'notelp' => $notelp[$j],
                'nama' => $nama[$j],
                'lembaga' => $lembaga[$j],
                'kota' => $kota[$j],
                'provinsi' => $provinsi[$j],
                'negara' => $negara[$j],
                'email' => $email[$j],
                'hubungan' => $hubungan[$j],
                //'kompetensi' => $kompetensi,

                'kompetensi' => 'W.1',
                't' => $t,

                'createdby' => $this->session->userdata('user_id'),
                'modifiedby' => $this->session->userdata('user_id'),
                'modifieddate' => date('Y-m-d H:i:s'),
                'status' => 1,
              );
              if ($val != "" && $is_submit == "1") {
                $where = array(
                  "id" => $val
                );
                //$update = $this->main_mod->update('user_faip_21',$where,$row);		
              } else $insert = $this->main_mod->insert('user_faip_21', $row);
              //if($i==0) $tempid = $insert;							
            } catch (Exception $e) {
              print_r($e);
            }
            //$i++;
            //}
            $j++;
          }
        }
      }

      //UPDATE 22_
      //print_r($orgkomp);

      if ($is_submit == "1" || $save_partial == '22') {
        if ($is_submit == "1") $temp = ''; //$temp =$this->main_mod->update('user_faip_22',array("faip_id" => $id),array("status" => 0));	
        else $temp = $this->main_mod->delete('user_faip_22', "faip_id", $id);

        $nama_22_uraian = $this->input->post('22_uraian') <> null ? $this->input->post('22_uraian') : "";
        if (is_array($nama_22_uraian)) {
          $j = 0;
          foreach ($nama_22_uraian as $val) {
            $tempid = '';
            $i = 0;
            $uraian = $this->input->post('22_uraian') <> null ? $this->input->post('22_uraian') : "";
            try {
              $row = array(
                'faip_id' => $id,
                'parent' => ($i == 0) ? 0 : $tempid,
                'uraian' => ($i == 0) ? $uraian[$j] : '',
                'kompetensi' => 'W.1',
                't' => ($i == 0) ? ($uraian[$j] != '' ? '30' : '0') : '0',
                'createdby' => $this->session->userdata('user_id'),
                'modifiedby' => $this->session->userdata('user_id'),
                'modifieddate' => date('Y-m-d H:i:s'),
                'status' => 1,
              );
              $insert = '';
              if ($i == 0 && $id22[$j] != "" && $is_submit == "1") {
                $where = array(
                  "id" => $id22[$j]
                );
                //$update = $this->main_mod->update('user_faip_22',$where,$row);	
                $insert = $id22[$j];
              } else $insert = $this->main_mod->insert('user_faip_22', $row);
              if ($i == 0) $tempid = $insert;
            } catch (Exception $e) {
              print_r($e);
            }


            //$a22_komp = $this->input->post('22_komp['.($j+1).']')<>null?$this->input->post('22_komp['.($j+1).']'):"";

            /*$a22_komp = $this->input->post('22_komp')<>null?$this->input->post('22_komp'):"";							
							if(is_array($a22_komp)){
							$a22_komp = array_merge($a22_komp); 
							$a22_komp = $a22_komp[$j];
							}
							if($is_submit!="1" && !is_array($a22_komp[0]))
								$a22_komp = explode(",", $a22_komp[0]);
							
							if(is_array($a22_komp)){
								foreach($a22_komp as $kompetensi){
									$uraian = $this->input->post('22_uraian')<>null?$this->input->post('22_uraian'):"";					
									try{
										$row=array(
											'faip_id' => $id,
											'parent' => ($i==0)?0:$tempid,
											'uraian' => ($i==0)?$uraian[$j]:'',
											'kompetensi' => $kompetensi,
											't' => ($i==0)?($uraian[$j]!=''?'30':'0'):'0',
											'createdby' => $this->session->userdata('user_id'),
											'modifiedby' => $this->session->userdata('user_id'),
											'modifieddate' => date('Y-m-d H:i:s'),
											'status' => 1,
										);
										$insert='';
										if($i==0 && $id22[$j]!="" && $is_submit=="1"){
											$where = array(
												"id" => $id22[$j]
											);
											//$update = $this->main_mod->update('user_faip_22',$where,$row);	
											$insert = $id22[$j];								
										}		
										else $insert = $this->main_mod->insert('user_faip_22',$row);	
										if($i==0) $tempid = $insert;							
									}
									catch(Exception $e){
										print_r($e);
									}
									$i++;
								}
							}
							else{
								$uraian = $this->input->post('22_uraian')<>null?$this->input->post('22_uraian'):"";					
								try{
									$row=array(
										'faip_id' => $id,
										'parent' => ($i==0)?0:$tempid,
										'uraian' => ($i==0)?$uraian[$j]:'',
										//'kompetensi' => $kompetensi,
										't' => ($i==0)?($uraian[$j]!=''?'30':'0'):'0',
										'createdby' => $this->session->userdata('user_id'),
										'modifiedby' => $this->session->userdata('user_id'),
										'modifieddate' => date('Y-m-d H:i:s'),
										'status' => 1,
									);
									$insert='';
									if($i==0 && $id22[$j]!="" && $is_submit=="1"){
										$where = array(
											"id" => $id22[$j]
										);
										//$update = $this->main_mod->update('user_faip_22',$where,$row);	
										$insert = $id22[$j];								
									}		
									else $insert = $this->main_mod->insert('user_faip_22',$row);	
									if($i==0) $tempid = $insert;							
								}
								catch(Exception $e){
									print_r($e);
								}
								$i++;
							}*/
            $j++;
          }
        }
      }

      //UPDATE 3_
      //print_r($orgkomp);

      if ($is_submit == "1" || $save_partial == '3') {
        if ($faip->need_revisi == '1') {
          $temp = $this->main_mod->update('user_faip_3', array("faip_id" => $id), array("status" => 0));
        } else {
          if ($is_submit == "1") $temp = ''; //$temp = $this->main_mod->update('user_faip_13',array("faip_id" => $id),array("status" => 0));	
          else $temp = $this->main_mod->delete('user_faip_3', "faip_id", $id);
        }

        $nama_3_instansi = $this->input->post('3_instansi') <> null ? $this->input->post('3_instansi') : "";
        if (is_array($nama_3_instansi)) {
          $j = 0;
          foreach ($nama_3_instansi as $val) {
            $tempid = '';
            $i = 0;
            //$a3_komp = $this->input->post('3_komp['.($j+1).']')<>null?$this->input->post('3_komp['.($j+1).']'):"";
            $a3_komp = $this->input->post('3_komp') <> null ? $this->input->post('3_komp') : "";
            if (is_array($a3_komp)) {
              $a3_komp = array_merge($a3_komp);
              $a3_komp = $a3_komp[$j];
            }
            if ($is_submit != "1" && !is_array($a3_komp[0]))
              $a3_komp = explode(",", $a3_komp[0]);

            if (!is_array($a3_komp)) {
              $a3_komp[] = null;
            }


            if (is_array($a3_komp)) {
              foreach ($a3_komp as $kompetensi) {

                $startdate = $this->input->post('3_startdate') <> null ? $this->input->post('3_startdate') : "";
                $startyear = $this->input->post('3_startyear') <> null ? $this->input->post('3_startyear') : "";
                $enddate = $this->input->post('3_enddate') <> null ? $this->input->post('3_enddate') : "";
                $endyear = $this->input->post('3_endyear') <> null ? $this->input->post('3_endyear') : "";
                $is_present = $this->input->post('3_workx') <> null ? $this->input->post('3_workx') : "";

                $instansi = $this->input->post('3_instansi') <> null ? $this->input->post('3_instansi') : "";
                $location = $this->input->post('3_location') <> null ? $this->input->post('3_location') : "";
                $provinsi = $this->input->post('3_provinsi') <> null ? $this->input->post('3_provinsi') : "";
                $negara = $this->input->post('3_negara') <> null ? $this->input->post('3_negara') : "";
                $namaproyek = $this->input->post('3_namaproyek') <> null ? $this->input->post('3_namaproyek') : "";
                $posisi = $this->input->post('3_posisi') <> null ? $this->input->post('3_posisi') : "";

                $uraian = $this->input->post('3_uraian') <> null ? $this->input->post('3_uraian') : "";

                $periode = $this->input->post('3_periode') <> null ? $this->input->post('3_periode') : "";
                $nilaiproyek = $this->input->post('3_nilaiproyek') <> null ? $this->input->post('3_nilaiproyek') : "";
                $pemberitugas = $this->input->post('3_pemberitugas') <> null ? $this->input->post('3_pemberitugas') : "";
                $exp_image_url = $this->input->post('3_exp_image_url') <> null ? $this->input->post('3_exp_image_url') : "";

                $title = $this->input->post('3_title') <> null ? $this->input->post('3_title') : "";
                $nilaipry = $this->input->post('3_nilaipry') <> null ? $this->input->post('3_nilaipry') : "";
                $nilaijasa = $this->input->post('3_nilaijasa') <> null ? $this->input->post('3_nilaijasa') : "";
                $nilaisdm = $this->input->post('3_nilaisdm') <> null ? $this->input->post('3_nilaisdm') : "";
                $nilaisulit = $this->input->post('3_nilaisulit') <> null ? $this->input->post('3_nilaisulit') : "";

                $p = (isset($periode[$j]) ? ($periode[$j] != '' ? $periode[$j] : 0) : 0);
                $q = (isset($posisi[$j]) ? ($posisi[$j] != '' ? $posisi[$j] : 0) : 0);
                $r = (isset($nilaiproyek[$j]) ? ($nilaiproyek[$j] != '' ? $nilaiproyek[$j] : 0) : 0);
                $t = $p * $q * $r;
                try {
                  $row = array(
                    'faip_id' => $id,
                    'parent' => ($i == 0) ? 0 : $tempid,
                    'instansi' => ($i == 0) ? $instansi[$j] : '',
                    'location' => ($i == 0) ? $location[$j] : '',
                    'provinsi' => ($i == 0) ? $provinsi[$j] : '',
                    'negara' => ($i == 0) ? $negara[$j] : '',
                    'namaproyek' => ($i == 0) ? $namaproyek[$j] : '',
                    'posisi' => ($i == 0) ? $posisi[$j] : '',
                    'periode' => ($i == 0) ? $periode[$j] : '',
                    'nilaiproyek' => ($i == 0) ? $nilaiproyek[$j] : '',
                    'pemberitugas' => ($i == 0) ? $pemberitugas[$j] : '',

                    'startdate' => ($i == 0) ? $startdate[$j] : '',
                    'startyear' => ($i == 0) ? $startyear[$j] : '',
                    'enddate' => ($i == 0) ? $enddate[$j] : '',
                    'endyear' => ($i == 0) ? $endyear[$j] : '',
                    'is_present' => ($i == 0) ? ($is_present[$j] == "1" ? "1" : "0") : '',
                    'title' => ($i == 0) ? $title[$j] : '',
                    'nilaipry' => ($i == 0) ? $nilaipry[$j] : '',
                    'nilaijasa' => ($i == 0) ? $nilaijasa[$j] : '',
                    'nilaisdm' => ($i == 0) ? $nilaisdm[$j] : '',
                    'nilaisulit' => ($i == 0) ? $nilaisulit[$j] : '',

                    'uraian' => ($i == 0) ? $uraian[$j] : '',

                    'kompetensi' => $kompetensi,

                    'p' => $p,
                    'q' => $q,
                    'r' => $r,
                    't' => $t,

                    'attachment' => (($i == 0) ? (isset($exp_image_url[$j]) ? $exp_image_url[$j] : '') : ''),
                    'createdby' => $this->session->userdata('user_id'),
                    'modifiedby' => $this->session->userdata('user_id'),
                    'modifieddate' => date('Y-m-d H:i:s'),
                    'status' => 1,
                  );
                  $insert = '';
                  /*if($i==0 && $id3[$j]!="" && $is_submit=="1"){
											$where = array(
												"id" => $id3[$j]
											);
											//$update = $this->main_mod->update('user_faip_3',$where,$row);	
											$insert = $id3[$j];																							
										}		
										else 
											$insert = $this->main_mod->insert('user_faip_3',$row);	*/

                  if ($i == 0 && $id3[$j] != "") {

                    $temp_ = $this->main_mod->msrwhere('user_faip_3', array('id' => $id3[$j], 'status' => '0'), 'id', 'desc')->result();
                    if (isset($temp_[0])) {
                      $where = array(
                        "id" => $id3[$j],
                        'status' => '0'
                      );
                      $update = $this->main_mod->update('user_faip_3', $where, $row);
                      $insert = $id3[$j];
                    } else
                      $insert = $this->main_mod->insert('user_faip_3', $row);
                  } else {
                    if ($id3[$j] != "") {
                      $temp__ = $this->main_mod->msrwhere('user_faip_3', array('parent' => $id3[$j], 'status' => '0', 'kompetensi' => $kompetensi), 'id', 'desc')->result();

                      if (isset($temp__[0])) {
                        $where = array(
                          "parent" => $id3[$j],
                          "status" => '0',
                          'kompetensi' => $kompetensi
                        );
                        $update = $this->main_mod->update('user_faip_3', $where, $row);
                        $insert = $id3[$j];
                      } else $insert = $this->main_mod->insert('user_faip_3', $row);
                    } else $insert = $this->main_mod->insert('user_faip_3', $row);
                  }

                  //SYNC user_exp
                  if ($is_submit == "1") {
                    $o_data = $this->main_mod->msrwhere('user_exp', array('user_id' => $this->session->userdata('user_id'), 'status' => 1, 'company' => $instansi[$j], 'title' => $title[$j], 'startyear' => $startyear[$j]), 'id', 'asc')->result();
                    if (count($o_data) > 0) {
                      $temp = $this->main_mod->update(
                        'user_exp',
                        array('user_id' => $this->session->userdata('user_id'), 'status' => 1, 'company' => $instansi[$j], 'title' => $title[$j], 'startyear' => $startyear[$j]),
                        array(
                          'attachment' => (isset($exp_image_url[$j]) ? $exp_image_url[$j] : ''),
                          'company' => strtoupper($instansi[$j]),
                          'title' => strtoupper($title[$j]),
                          'location' => $location[$j],
                          'provinsi' => $provinsi[$j],
                          'negara' => $negara[$j],
                          'startmonth' => $startdate[$j],
                          'startyear' => $startyear[$j],
                          'endmonth' => $enddate[$j],
                          'endyear' => $endyear[$j],
                          'is_present' => ($is_present[$j] == "1" ? "1" : "0"),
                          'description' => $uraian[$j],
                          'actv' => $namaproyek[$j]

                        )
                      );
                    } else {
                      $this->main_mod->insert('user_exp', array(
                        'user_id' => $this->session->userdata('user_id'),
                        'status' => 1,
                        'attachment' => (isset($exp_image_url[$j]) ? $exp_image_url[$j] : ''),
                        'company' => strtoupper($instansi[$j]),
                        'title' => strtoupper($title[$j]),
                        'location' => $location[$j],
                        'provinsi' => $provinsi[$j],
                        'negara' => $negara[$j],
                        'startmonth' => $startdate[$j],
                        'startyear' => $startyear[$j],
                        'endmonth' => $enddate[$j],
                        'endyear' => $endyear[$j],
                        'is_present' => ($is_present[$j] == "1" ? "1" : "0"),
                        'description' => $uraian[$j],
                        'actv' => $namaproyek[$j],
                        'createdby' => $this->session->userdata('user_id'),
                      ));
                    }
                  }
                  //SYNC

                  if ($i == 0) $tempid = $insert;
                } catch (Exception $e) {
                  print_r($e);
                }
                $i++;
              }

              if ($faip->need_revisi == '1') {
                $temp = $this->main_mod->delete_where('user_faip_3', array("faip_id" => $id, 'status' => '0'));
              }
            }

            $j++;
          }
        }
      }

      //UPDATE 4_
      //print_r($orgkomp);

      if ($is_submit == "1" || $save_partial == '4') {
        if ($faip->need_revisi == '1') {
          $temp = $this->main_mod->update('user_faip_4', array("faip_id" => $id), array("status" => 0));
        } else {
          if ($is_submit == "1") $temp = ''; //$temp = $this->main_mod->update('user_faip_13',array("faip_id" => $id),array("status" => 0));	
          else $temp = $this->main_mod->delete('user_faip_4', "faip_id", $id);
        }

        $nama_4_instansi = $this->input->post('4_instansi') <> null ? $this->input->post('4_instansi') : "";
        if (is_array($nama_4_instansi)) {
          $j = 0;
          foreach ($nama_4_instansi as $val) {
            $tempid = '';
            $i = 0;
            //$a4_komp = $this->input->post('4_komp['.($j+1).']')<>null?$this->input->post('4_komp['.($j+1).']'):"";
            $a4_komp = $this->input->post('4_komp') <> null ? $this->input->post('4_komp') : "";
            if (is_array($a4_komp)) {
              $a4_komp = array_merge($a4_komp);
              $a4_komp = $a4_komp[$j];
            }
            if ($is_submit != "1" && !is_array($a4_komp[0]))
              $a4_komp = explode(",", $a4_komp[0]);

            if (!is_array($a4_komp)) {
              $a4_komp[] = null;
            }

            if (is_array($a4_komp)) {
              foreach ($a4_komp as $kompetensi) {
                $startdate = $this->input->post('4_startdate') <> null ? $this->input->post('4_startdate') : "";
                $startyear = $this->input->post('4_startyear') <> null ? $this->input->post('4_startyear') : "";
                $enddate = $this->input->post('4_enddate') <> null ? $this->input->post('4_enddate') : "";
                $endyear = $this->input->post('4_endyear') <> null ? $this->input->post('4_endyear') : "";
                $is_present = $this->input->post('4_workx') <> null ? $this->input->post('4_workx') : "";

                $instansi = $this->input->post('4_instansi') <> null ? $this->input->post('4_instansi') : "";
                $location = $this->input->post('4_location') <> null ? $this->input->post('4_location') : "";
                $provinsi = $this->input->post('4_provinsi') <> null ? $this->input->post('4_provinsi') : "";
                $negara = $this->input->post('4_negara') <> null ? $this->input->post('4_negara') : "";
                $namaproyek = $this->input->post('4_namaproyek') <> null ? $this->input->post('4_namaproyek') : "";

                $uraian = $this->input->post('4_uraian') <> null ? $this->input->post('4_uraian') : "";

                $posisi = $this->input->post('4_posisi') <> null ? $this->input->post('4_posisi') : "";
                $periode = $this->input->post('4_periode') <> null ? $this->input->post('4_periode') : "";
                $jumlahsks = $this->input->post('4_jumlahsks') <> null ? $this->input->post('4_jumlahsks') : "";
                $exp2_image_url = $this->input->post('4_exp2_image_url') <> null ? $this->input->post('4_exp2_image_url') : "";

                $p = (isset($periode[$j]) ? ($periode[$j] != '' ? $periode[$j] : 0) : 0);
                $q = (isset($posisi[$j]) ? ($posisi[$j] != '' ? $posisi[$j] : 0) : 0);
                $r = (isset($jumlahsks[$j]) ? ($jumlahsks[$j] != '' ? $jumlahsks[$j] : 0) : 0);
                $t = $p * $q * $r;
                try {
                  $row = array(
                    'faip_id' => $id,
                    'parent' => ($i == 0) ? 0 : $tempid,

                    'instansi' => ($i == 0) ? $instansi[$j] : '',
                    'location' => ($i == 0) ? $location[$j] : '',
                    'provinsi' => ($i == 0) ? $provinsi[$j] : '',
                    'negara' => ($i == 0) ? $negara[$j] : '',
                    'namaproyek' => ($i == 0) ? $namaproyek[$j] : '',
                    'posisi' => ($i == 0) ? $posisi[$j] : '',
                    'periode' => ($i == 0) ? $periode[$j] : '',
                    'jumlahsks' => ($i == 0) ? $jumlahsks[$j] : '',

                    'uraian' => ($i == 0) ? $uraian[$j] : '',

                    'kompetensi' => $kompetensi,

                    'p' => $p,
                    'q' => $q,
                    'r' => $r,
                    't' => $t,

                    'attachment' => (($i == 0) ? (isset($exp2_image_url[$j]) ? $exp2_image_url[$j] : '') : ''),
                    'createdby' => $this->session->userdata('user_id'),
                    'modifiedby' => $this->session->userdata('user_id'),
                    'modifieddate' => date('Y-m-d H:i:s'),
                    'status' => 1,
                  );
                  $insert = '';
                  /*if($i==0 && $id4[$j]!="" && $is_submit=="1"){
											$where = array(
												"id" => $id4[$j]
											);
											//$update = $this->main_mod->update('user_faip_4',$where,$row);	
											$insert = $id4[$j];								
										}		
										else $insert = $this->main_mod->insert('user_faip_4',$row);	*/

                  if ($i == 0 && $id4[$j] != "") {

                    $temp_ = $this->main_mod->msrwhere('user_faip_4', array('id' => $id4[$j], 'status' => '0'), 'id', 'desc')->result();
                    if (isset($temp_[0])) {
                      $where = array(
                        "id" => $id4[$j],
                        'status' => '0'
                      );
                      $update = $this->main_mod->update('user_faip_4', $where, $row);
                      $insert = $id4[$j];
                    } else
                      $insert = $this->main_mod->insert('user_faip_4', $row);
                  } else {
                    if ($id4[$j] != "") {
                      $temp__ = $this->main_mod->msrwhere('user_faip_4', array('parent' => $id4[$j], 'status' => '0', 'kompetensi' => $kompetensi), 'id', 'desc')->result();

                      if (isset($temp__[0])) {
                        $where = array(
                          "parent" => $id4[$j],
                          "status" => '0',
                          'kompetensi' => $kompetensi
                        );
                        $update = $this->main_mod->update('user_faip_4', $where, $row);
                        $insert = $id4[$j];
                      } else $insert = $this->main_mod->insert('user_faip_4', $row);
                    } else $insert = $this->main_mod->insert('user_faip_4', $row);
                  }

                  if ($i == 0) $tempid = $insert;
                } catch (Exception $e) {
                  print_r($e);
                }
                $i++;
              }

              if ($faip->need_revisi == '1') {
                $temp = $this->main_mod->delete_where('user_faip_4', array("faip_id" => $id, 'status' => '0'));
              }
            }
            $j++;
          }
        }
      }

      //INSERT 51_
      //print_r($orgkomp);

      if ($is_submit == "1" || $save_partial == '51') {

        if ($faip->need_revisi == '1') {
          $temp = $this->main_mod->update('user_faip_51', array("faip_id" => $id), array("status" => 0));
        } else {
          if ($is_submit == "1") $temp = ''; //$temp = $this->main_mod->update('user_faip_13',array("faip_id" => $id),array("status" => 0));	
          else $temp = $this->main_mod->delete('user_faip_51', "faip_id", $id);
        }


        $nama_51_nama = $this->input->post('51_nama') <> null ? $this->input->post('51_nama') : "";
        if (is_array($nama_51_nama)) {
          $j = 0;
          foreach ($nama_51_nama as $val) {
            $tempid = '';
            $i = 0;
            //$a51_komp = $this->input->post('51_komp['.($j+1).']')<>null?$this->input->post('51_komp['.($j+1).']'):"";
            $a51_komp = $this->input->post('51_komp') <> null ? $this->input->post('51_komp') : "";
            if (is_array($a51_komp)) {
              $a51_komp = array_merge($a51_komp);
              $a51_komp = $a51_komp[$j];
            }
            if ($is_submit != "1" && !is_array($a51_komp[0]))
              $a51_komp = explode(",", $a51_komp[0]);

            if (!is_array($a51_komp)) {
              $a51_komp[] = null;
            }

            if (is_array($a51_komp)) {
              foreach ($a51_komp as $kompetensi) {
                $startdate = $this->input->post('51_startdate') <> null ? $this->input->post('51_startdate') : "";
                $startyear = $this->input->post('51_startyear') <> null ? $this->input->post('51_startyear') : "";

                $nama = $this->input->post('51_nama') <> null ? $this->input->post('51_nama') : "";
                $location = $this->input->post('51_location') <> null ? $this->input->post('51_location') : "";
                $provinsi = $this->input->post('51_provinsi') <> null ? $this->input->post('51_provinsi') : "";
                $negara = $this->input->post('51_negara') <> null ? $this->input->post('51_negara') : "";
                $media = $this->input->post('51_media') <> null ? $this->input->post('51_media') : "";
                $tingkatmedia = $this->input->post('51_tingkatmedia') <> null ? $this->input->post('51_tingkatmedia') : "";
                $tingkat = $this->input->post('51_tingkat') <> null ? $this->input->post('51_tingkat') : "";
                $uraian = $this->input->post('51_uraian') <> null ? $this->input->post('51_uraian') : "";
                $publication1_image_url = $this->input->post('51_publication1_image_url') <> null ? $this->input->post('51_publication1_image_url') : "";

                $tempidx = 0;
                $years = count($a51_komp);
                $data['bp_51'] = $this->main_mod->msrwhere('m_bakuan_penilaian', array('faip_num' => '51', 'faip_type' => 'p'), 'id', 'desc')->result();
                //echo $years.'<br />';
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
                $q = (isset($tingkatmedia[$j]) ? ($tingkatmedia[$j] != '' ? $tingkatmedia[$j] : 0) : 0);
                $r = (isset($tingkat[$j]) ? ($tingkat[$j] != '' ? $tingkat[$j] : 0) : 0);
                $t = $p * $q * $r;

                try {
                  $row = array(
                    'faip_id' => $id,
                    'parent' => ($i == 0) ? 0 : $tempid,
                    'startdate' => ($i == 0) ? $startdate[$j] : '',
                    'startyear' => ($i == 0) ? $startyear[$j] : '',
                    'nama' => ($i == 0) ? $nama[$j] : '',
                    'location' => ($i == 0) ? $location[$j] : '',
                    'provinsi' => ($i == 0) ? $provinsi[$j] : '',
                    'negara' => ($i == 0) ? $negara[$j] : '',
                    'media' => ($i == 0) ? $media[$j] : '',
                    'tingkatmedia' => ($i == 0) ? $tingkatmedia[$j] : '',
                    'tingkat' => ($i == 0) ? $tingkat[$j] : '',
                    'uraian' => ($i == 0) ? $uraian[$j] : '',
                    'kompetensi' => $kompetensi,

                    'p' => $p,
                    'q' => $q,
                    'r' => $r,
                    't' => $t,

                    'attachment' => ($i == 0) ? (isset($publication1_image_url[$j]) ? $publication1_image_url[$j] : '') : '',
                    'createdby' => $this->session->userdata('user_id'),
                    'modifiedby' => $this->session->userdata('user_id'),
                    'modifieddate' => date('Y-m-d H:i:s'),
                    'status' => 1,
                  );
                  $insert = '';
                  /*if($i==0 && $id51[$j]!="" && $is_submit=="1"){
											$where = array(
												"id" => $id51[$j]
											);
											//$update = $this->main_mod->update('user_faip_51',$where,$row);	
											$insert = $id51[$j];								
										}		
										else $insert = $this->main_mod->insert('user_faip_51',$row);	*/

                  if ($i == 0 && $id51[$j] != "") {

                    $temp_ = $this->main_mod->msrwhere('user_faip_51', array('id' => $id51[$j], 'status' => '0'), 'id', 'desc')->result();
                    if (isset($temp_[0])) {
                      $where = array(
                        "id" => $id51[$j],
                        'status' => '0'
                      );
                      $update = $this->main_mod->update('user_faip_51', $where, $row);
                      $insert = $id51[$j];
                    } else
                      $insert = $this->main_mod->insert('user_faip_51', $row);
                  } else {
                    if ($id51[$j] != "") {
                      $temp__ = $this->main_mod->msrwhere('user_faip_51', array('parent' => $id51[$j], 'status' => '0', 'kompetensi' => $kompetensi), 'id', 'desc')->result();

                      if (isset($temp__[0])) {
                        $where = array(
                          "parent" => $id51[$j],
                          "status" => '0',
                          'kompetensi' => $kompetensi
                        );
                        $update = $this->main_mod->update('user_faip_51', $where, $row);
                        $insert = $id51[$j];
                      } else $insert = $this->main_mod->insert('user_faip_51', $row);
                    } else $insert = $this->main_mod->insert('user_faip_51', $row);
                  }

                  $te = '';
                  if ($tingkatmedia[$j] == '1')
                    $te = 'Lokal';
                  else if ($tingkatmedia[$j] == '2')
                    $te = 'Nasional';
                  else if ($tingkatmedia[$j] == '3')
                    $te = 'Internasional';
                  $se = '';
                  if ($tingkat[$j] == '1')
                    $se = 'Komplikasi masalah, kreatifitas & inovasi rendah, nilai manfaat dan dampak nilai teknologi rendah';
                  else if ($tingkat[$j] == '2')
                    $se = 'Komplikasi masalah, kreatifitas & inovasi sedang, nilai manfaat dan dampak nilai teknologi sedang';
                  else if ($tingkat[$j] == '3')
                    $se = 'Komplikasi masalah, kreatifitas & inovasi tinggi, nilai manfaat dan dampak nilai teknologi luas';
                  else if ($tingkat[$j] == '4')
                    $se = 'Komplikasi masalah, kreatifitas & inovasi sangat tinggi, nilai manfaat dan dampak nilai teknologi sangat luas';


                  //SYNC user_publication
                  if ($is_submit == "1") {
                    $o_data = $this->main_mod->msrwhere('user_publication', array('user_id' => $this->session->userdata('user_id'), 'status' => 1, 'type' => "1", 'topic' => $nama[$j], 'startyear' => $startyear[$j]), 'id', 'asc')->result();
                    if (count($o_data) > 0) {
                      $temp = $this->main_mod->update(
                        'user_publication',
                        array('user_id' => $this->session->userdata('user_id'), 'status' => 1, 'type' => "1", 'topic' => $nama[$j], 'startyear' => $startyear[$j]),
                        array(
                          'topic' => $nama[$j],
                          'media' => $media[$j],
                          'type' => "1",
                          'location' => $location[$j],
                          'provinsi' => $provinsi[$j],
                          'negara' => $negara[$j],
                          'tingkat' => $te, //$tingkatmedia[$j],		
                          'tingkatmedia' => $se, //$tingkat[$j],														
                          //'journal' => $journal,
                          //'event' => $event,
                          'description' => $uraian[$j],
                          'startmonth' => $startdate[$j],
                          'startyear' => $startyear[$j],
                          'attachment' => (isset($publication1_image_url[$j]) ? $publication1_image_url[$j] : '')
                        )
                      );
                    } else {
                      $this->main_mod->insert('user_publication', array(
                        'user_id' => $this->session->userdata('user_id'),
                        'status' => 1,
                        'topic' => $nama[$j],
                        'media' => $media[$j],
                        'type' => "1",
                        'location' => $location[$j],
                        'provinsi' => $provinsi[$j],
                        'negara' => $negara[$j],
                        'tingkat' => $te, //$tingkatmedia[$j],		
                        'tingkatmedia' => $se, //$tingkat[$j],													
                        //'journal' => $journal,
                        //'event' => $event,
                        'description' => $uraian[$j],
                        'startmonth' => $startdate[$j],
                        'startyear' => $startyear[$j],
                        'attachment' => (isset($publication1_image_url[$j]) ? $publication1_image_url[$j] : ''),
                        'createdby' => $this->session->userdata('user_id'),
                      ));
                    }
                  }
                  //SYNC

                  if ($i == 0) $tempid = $insert;
                } catch (Exception $e) {
                  print_r($e);
                }
                $i++;
              }

              if ($faip->need_revisi == '1') {
                $temp = $this->main_mod->delete_where('user_faip_51', array("faip_id" => $id, 'status' => '0'));
              }
            }
            $j++;
          }
        }
      }


      //INSERT 52_
      //print_r($orgkomp);

      if ($is_submit == "1" || $save_partial == '52') {
        if ($faip->need_revisi == '1') {
          $temp = $this->main_mod->update('user_faip_52', array("faip_id" => $id), array("status" => 0));
        } else {
          if ($is_submit == "1") $temp = ''; //$temp = $this->main_mod->update('user_faip_13',array("faip_id" => $id),array("status" => 0));	
          else $temp = $this->main_mod->delete('user_faip_52', "faip_id", $id);
        }


        $nama_52_nama = $this->input->post('52_nama') <> null ? $this->input->post('52_nama') : "";
        if (is_array($nama_52_nama)) {
          $j = 0;
          foreach ($nama_52_nama as $val) {
            $tempid = '';
            $i = 0;
            //$a52_komp = $this->input->post('52_komp['.($j+1).']')<>null?$this->input->post('52_komp['.($j+1).']'):"";
            $a52_komp = $this->input->post('52_komp') <> null ? $this->input->post('52_komp') : "";
            if (is_array($a52_komp)) {
              $a52_komp = array_merge($a52_komp);
              $a52_komp = $a52_komp[$j];
            }
            if ($is_submit != "1" && !is_array($a52_komp[0]))
              $a52_komp = explode(",", $a52_komp[0]);

            if (!is_array($a52_komp)) {
              $a52_komp[] = null;
            }

            if (is_array($a52_komp)) {
              foreach ($a52_komp as $kompetensi) {
                $startdate = $this->input->post('52_startdate') <> null ? $this->input->post('52_startdate') : "";
                $startyear = $this->input->post('52_startyear') <> null ? $this->input->post('52_startyear') : "";

                $nama = $this->input->post('52_nama') <> null ? $this->input->post('52_nama') : "";
                $judul = $this->input->post('52_judul') <> null ? $this->input->post('52_judul') : "";
                $penyelenggara = $this->input->post('52_penyelenggara') <> null ? $this->input->post('52_penyelenggara') : "";
                $location = $this->input->post('52_location') <> null ? $this->input->post('52_location') : "";
                $provinsi = $this->input->post('52_provinsi') <> null ? $this->input->post('52_provinsi') : "";
                $negara = $this->input->post('52_negara') <> null ? $this->input->post('52_negara') : "";
                $tingkatseminar = $this->input->post('52_tingkatseminar') <> null ? $this->input->post('52_tingkatseminar') : "";
                $tingkat = $this->input->post('52_tingkat') <> null ? $this->input->post('52_tingkat') : "";
                $uraian = $this->input->post('52_uraian') <> null ? $this->input->post('52_uraian') : "";
                $publication2_image_url = $this->input->post('52_publication2_image_url') <> null ? $this->input->post('52_publication2_image_url') : "";
                $tempidx = 0;
                $years = count($a52_komp);
                $data['bp_52'] = $this->main_mod->msrwhere('m_bakuan_penilaian', array('faip_num' => '52', 'faip_type' => 'p'), 'id', 'desc')->result();
                //echo $years.'<br />';
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
                $q = (isset($tingkatseminar[$j]) ? ($tingkatseminar[$j] != '' ? $tingkatseminar[$j] : 0) : 0);
                $r = (isset($tingkat[$j]) ? ($tingkat[$j] != '' ? $tingkat[$j] : 0) : 0);
                $t = $p * $q * $r;
                try {
                  $row = array(
                    'faip_id' => $id,
                    'parent' => ($i == 0) ? 0 : $tempid,
                    'startdate' => ($i == 0) ? $startdate[$j] : '',
                    'startyear' => ($i == 0) ? $startyear[$j] : '',
                    'nama' => ($i == 0) ? $nama[$j] : '',
                    'judul' => ($i == 0) ? $judul[$j] : '',
                    'penyelenggara' => ($i == 0) ? $penyelenggara[$j] : '',
                    'location' => ($i == 0) ? $location[$j] : '',
                    'provinsi' => ($i == 0) ? $provinsi[$j] : '',
                    'negara' => ($i == 0) ? $negara[$j] : '',
                    'tingkatseminar' => ($i == 0) ? $tingkatseminar[$j] : '',
                    'tingkat' => ($i == 0) ? $tingkat[$j] : '',
                    'uraian' => ($i == 0) ? $uraian[$j] : '',
                    'kompetensi' => $kompetensi,

                    'p' => $p,
                    'q' => $q,
                    'r' => $r,
                    't' => $t,
                    'attachment' => (($i == 0) ? (isset($publication2_image_url[$j]) ? $publication2_image_url[$j] : '') : ''),
                    'createdby' => $this->session->userdata('user_id'),
                    'modifiedby' => $this->session->userdata('user_id'),
                    'modifieddate' => date('Y-m-d H:i:s'),
                    'status' => 1,
                  );
                  $insert = '';
                  /*if($i==0 && $id52[$j]!="" && $is_submit=="1"){
											$where = array(
												"id" => $id52[$j]
											);
											//$update = $this->main_mod->update('user_faip_52',$where,$row);	
											$insert = $id52[$j];								
										}		
										else $insert = $this->main_mod->insert('user_faip_52',$row);	*/

                  if ($i == 0 && $id52[$j] != "") {

                    $temp_ = $this->main_mod->msrwhere('user_faip_52', array('id' => $id52[$j], 'status' => '0'), 'id', 'desc')->result();
                    if (isset($temp_[0])) {
                      $where = array(
                        "id" => $id52[$j],
                        'status' => '0'
                      );
                      $update = $this->main_mod->update('user_faip_52', $where, $row);
                      $insert = $id52[$j];
                    } else
                      $insert = $this->main_mod->insert('user_faip_52', $row);
                  } else {
                    if ($id52[$j] != "") {
                      $temp__ = $this->main_mod->msrwhere('user_faip_52', array('parent' => $id52[$j], 'status' => '0', 'kompetensi' => $kompetensi), 'id', 'desc')->result();

                      if (isset($temp__[0])) {
                        $where = array(
                          "parent" => $id52[$j],
                          "status" => '0',
                          'kompetensi' => $kompetensi
                        );
                        $update = $this->main_mod->update('user_faip_52', $where, $row);
                        $insert = $id52[$j];
                      } else $insert = $this->main_mod->insert('user_faip_52', $row);
                    } else $insert = $this->main_mod->insert('user_faip_52', $row);
                  }

                  $te = '';
                  if ($tingkatseminar[$j] == '1')
                    $te = 'Lokal';
                  else if ($tingkatseminar[$j] == '2')
                    $te = 'Nasional';
                  else if ($tingkatseminar[$j] == '3')
                    $te = 'Internasional';
                  $se = '';
                  if ($tingkat[$j] == '1')
                    $se = 'Komplikasi masalah, kreatifitas & inovasi rendah, nilai manfaat dan dampak nilai teknologi rendah';
                  else if ($tingkat[$j] == '2')
                    $se = 'Komplikasi masalah, kreatifitas & inovasi sedang, nilai manfaat dan dampak nilai teknologi sedang';
                  else if ($tingkat[$j] == '3')
                    $se = 'Komplikasi masalah, kreatifitas & inovasi tinggi, nilai manfaat dan dampak nilai teknologi luas';
                  else if ($tingkat[$j] == '4')
                    $se = 'Komplikasi masalah, kreatifitas & inovasi sangat tinggi, nilai manfaat dan dampak nilai teknologi sangat luas';


                  //SYNC user_publication
                  if ($is_submit == "1") {
                    $o_data = $this->main_mod->msrwhere('user_publication', array('user_id' => $this->session->userdata('user_id'), 'status' => 1, 'type' => "2", 'topic' => $judul[$j], 'startyear' => $startyear[$j]), 'id', 'asc')->result();
                    if (count($o_data) > 0) {
                      $temp = $this->main_mod->update(
                        'user_publication',
                        array('user_id' => $this->session->userdata('user_id'), 'status' => 1, 'type' => "2", 'topic' => $judul[$j], 'startyear' => $startyear[$j]),
                        array(
                          'topic' => $judul[$j],
                          'event' => $nama[$j],
                          'media' => $penyelenggara[$j],
                          'type' => "2",
                          'location' => $location[$j],
                          'provinsi' => $provinsi[$j],
                          'negara' => $negara[$j],
                          'tingkat' => $te, //$tingkatmedia[$j],		
                          'tingkatmedia' => $se, //$tingkat[$j],													

                          //'event' => $event,
                          'description' => $uraian[$j],
                          'startmonth' => $startdate[$j],
                          'startyear' => $startyear[$j],
                          'attachment' => (isset($publication2_image_url[$j]) ? $publication2_image_url[$j] : '')
                        )
                      );
                    } else {
                      $this->main_mod->insert('user_publication', array(
                        'user_id' => $this->session->userdata('user_id'),
                        'status' => 1,
                        'topic' => $judul[$j],
                        'event' => $nama[$j],
                        'media' => $penyelenggara[$j],
                        'type' => "2",
                        'location' => $location[$j],
                        'provinsi' => $provinsi[$j],
                        'negara' => $negara[$j],
                        'tingkat' => $te, //$tingkatmedia[$j],		
                        'tingkatmedia' => $se, //$tingkat[$j],													

                        //'event' => $event,
                        'description' => $uraian[$j],
                        'startmonth' => $startdate[$j],
                        'startyear' => $startyear[$j],
                        'attachment' => (isset($publication2_image_url[$j]) ? $publication2_image_url[$j] : ''),
                        'createdby' => $this->session->userdata('user_id'),
                      ));
                    }
                  }
                  //SYNC

                  if ($i == 0) $tempid = $insert;
                } catch (Exception $e) {
                  print_r($e);
                }
                $i++;
              }

              if ($faip->need_revisi == '1') {
                $temp = $this->main_mod->delete_where('user_faip_52', array("faip_id" => $id, 'status' => '0'));
              }
            }
            $j++;
          }
        }
      }

      //INSERT 53_
      //print_r($orgkomp);

      if ($is_submit == "1" || $save_partial == '53') {
        if ($faip->need_revisi == '1') {
          $temp = $this->main_mod->update('user_faip_53', array("faip_id" => $id), array("status" => 0));
        } else {
          if ($is_submit == "1") $temp = ''; //$temp = $this->main_mod->update('user_faip_13',array("faip_id" => $id),array("status" => 0));	
          else $temp = $this->main_mod->delete('user_faip_53', "faip_id", $id);
        }


        $nama_53_nama = $this->input->post('53_nama') <> null ? $this->input->post('53_nama') : "";
        if (is_array($nama_53_nama)) {
          $j = 0;
          foreach ($nama_53_nama as $val) {
            $tempid = '';
            $i = 0;
            //$a53_komp = $this->input->post('53_komp['.($j+1).']')<>null?$this->input->post('53_komp['.($j+1).']'):"";
            $a53_komp = $this->input->post('53_komp') <> null ? $this->input->post('53_komp') : "";
            if (is_array($a53_komp)) {
              $a53_komp = array_merge($a53_komp);
              $a53_komp = $a53_komp[$j];
            }
            if ($is_submit != "1" && !is_array($a53_komp[0]))
              $a53_komp = explode(",", $a53_komp[0]);

            if (!is_array($a53_komp)) {
              $a53_komp[] = null;
            }

            if (is_array($a53_komp)) {
              foreach ($a53_komp as $kompetensi) {
                $startdate = $this->input->post('53_startdate') <> null ? $this->input->post('53_startdate') : "";
                $startyear = $this->input->post('53_startyear') <> null ? $this->input->post('53_startyear') : "";

                $nama = $this->input->post('53_nama') <> null ? $this->input->post('53_nama') : "";
                $location = $this->input->post('53_location') <> null ? $this->input->post('53_location') : "";
                $provinsi = $this->input->post('53_provinsi') <> null ? $this->input->post('53_provinsi') : "";
                $negara = $this->input->post('53_negara') <> null ? $this->input->post('53_negara') : "";
                $penyelenggara = $this->input->post('53_penyelenggara') <> null ? $this->input->post('53_penyelenggara') : "";
                $tingkatseminar = $this->input->post('53_tingkatseminar') <> null ? $this->input->post('53_tingkatseminar') : "";
                $tingkat = $this->input->post('53_tingkat') <> null ? $this->input->post('53_tingkat') : "";
                $uraian = $this->input->post('53_uraian') <> null ? $this->input->post('53_uraian') : "";
                $publication3_image_url = $this->input->post('53_publication3_image_url') <> null ? $this->input->post('53_publication3_image_url') : "";
                $tempidx = 0;
                $years = count($a53_komp);
                $data['bp_53'] = $this->main_mod->msrwhere('m_bakuan_penilaian', array('faip_num' => '53', 'faip_type' => 'p'), 'id', 'desc')->result();
                //echo $years.'<br />';
                foreach ($data['bp_53'] as $valbp) {
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
                $q = (isset($tingkatseminar[$j]) ? ($tingkatseminar[$j] != '' ? $tingkatseminar[$j] : 0) : 0);
                $r = (isset($tingkat[$j]) ? ($tingkat[$j] != '' ? $tingkat[$j] : 0) : 0);
                $t = $p * $q * $r;
                try {
                  $row = array(
                    'faip_id' => $id,
                    'parent' => ($i == 0) ? 0 : $tempid,
                    'startdate' => ($i == 0) ? $startdate[$j] : '',
                    'startyear' => ($i == 0) ? $startyear[$j] : '',
                    'nama' => ($i == 0) ? $nama[$j] : '',
                    'location' => ($i == 0) ? $location[$j] : '',
                    'provinsi' => ($i == 0) ? $provinsi[$j] : '',
                    'negara' => ($i == 0) ? $negara[$j] : '',
                    'penyelenggara' => ($i == 0) ? $penyelenggara[$j] : '',
                    'tingkatseminar' => ($i == 0) ? $tingkatseminar[$j] : '',
                    'tingkat' => ($i == 0) ? $tingkat[$j] : '',
                    'uraian' => ($i == 0) ? $uraian[$j] : '',
                    'kompetensi' => $kompetensi,


                    'p' => $p,
                    'q' => $q,
                    'r' => $r,
                    't' => $t,

                    'attachment' => ($i == 0) ? (isset($publication3_image_url[$j]) ? $publication3_image_url[$j] : '') : '',
                    'createdby' => $this->session->userdata('user_id'),
                    'modifiedby' => $this->session->userdata('user_id'),
                    'modifieddate' => date('Y-m-d H:i:s'),
                    'status' => 1,
                  );
                  $insert = '';
                  /*if($i==0 && $id53[$j]!="" && $is_submit=="1"){
											$where = array(
												"id" => $id53[$j]
											);
											//$update = $this->main_mod->update('user_faip_53',$where,$row);	
											$insert = $id53[$j];								
										}		
										else $insert = $this->main_mod->insert('user_faip_53',$row);	*/

                  if ($i == 0 && $id53[$j] != "") {

                    $temp_ = $this->main_mod->msrwhere('user_faip_53', array('id' => $id53[$j], 'status' => '0'), 'id', 'desc')->result();
                    if (isset($temp_[0])) {
                      $where = array(
                        "id" => $id53[$j],
                        'status' => '0'
                      );
                      $update = $this->main_mod->update('user_faip_53', $where, $row);
                      $insert = $id53[$j];
                    } else
                      $insert = $this->main_mod->insert('user_faip_53', $row);
                  } else {
                    if ($id53[$j] != "") {
                      $temp__ = $this->main_mod->msrwhere('user_faip_53', array('parent' => $id53[$j], 'status' => '0', 'kompetensi' => $kompetensi), 'id', 'desc')->result();

                      if (isset($temp__[0])) {
                        $where = array(
                          "parent" => $id53[$j],
                          "status" => '0',
                          'kompetensi' => $kompetensi
                        );
                        $update = $this->main_mod->update('user_faip_53', $where, $row);
                        $insert = $id53[$j];
                      } else $insert = $this->main_mod->insert('user_faip_53', $row);
                    } else $insert = $this->main_mod->insert('user_faip_53', $row);
                  }

                  $te = '';
                  if ($tingkatseminar[$j] == '1')
                    $te = 'Lokal';
                  else if ($tingkatseminar[$j] == '2')
                    $te = 'Nasional';
                  else if ($tingkatseminar[$j] == '3')
                    $te = 'Internasional';
                  $se = '';
                  if ($tingkat[$j] == '1')
                    $se = 'Komplikasi masalah, kreatifitas & inovasi rendah, nilai manfaat dan dampak nilai teknologi rendah';
                  else if ($tingkat[$j] == '2')
                    $se = 'Komplikasi masalah, kreatifitas & inovasi sedang, nilai manfaat dan dampak nilai teknologi sedang';
                  else if ($tingkat[$j] == '3')
                    $se = 'Komplikasi masalah, kreatifitas & inovasi tinggi, nilai manfaat dan dampak nilai teknologi luas';
                  else if ($tingkat[$j] == '4')
                    $se = 'Komplikasi masalah, kreatifitas & inovasi sangat tinggi, nilai manfaat dan dampak nilai teknologi sangat luas';


                  //SYNC user_publication
                  if ($is_submit == "1") {
                    $o_data = $this->main_mod->msrwhere('user_publication', array('user_id' => $this->session->userdata('user_id'), 'status' => 1, 'type' => "3", 'topic' => $nama[$j], 'startyear' => $startyear[$j]), 'id', 'asc')->result();
                    if (count($o_data) > 0) {
                      $temp = $this->main_mod->update(
                        'user_publication',
                        array('user_id' => $this->session->userdata('user_id'), 'status' => 1, 'type' => "3", 'topic' => $nama[$j], 'startyear' => $startyear[$j]),
                        array(
                          'topic' => $nama[$j],
                          'media' => $penyelenggara[$j],
                          'type' => "3",
                          'location' => $location[$j],
                          'provinsi' => $provinsi[$j],
                          'negara' => $negara[$j],
                          'tingkat' => $te,
                          'tingkatmedia' => $se,

                          //'event' => $event,
                          'description' => $uraian[$j],
                          'startmonth' => $startdate[$j],
                          'startyear' => $startyear[$j],
                          'attachment' => (isset($publication3_image_url[$j]) ? $publication3_image_url[$j] : '')
                        )
                      );
                    } else {
                      $this->main_mod->insert('user_publication', array(
                        'user_id' => $this->session->userdata('user_id'),
                        'status' => 1,
                        'topic' => $nama[$j],
                        'media' => $penyelenggara[$j],
                        'type' => "3",
                        'location' => $location[$j],
                        'provinsi' => $provinsi[$j],
                        'negara' => $negara[$j],
                        'tingkat' => $te,
                        'tingkatmedia' => $se,

                        //'event' => $event,
                        'description' => $uraian[$j],
                        'startmonth' => $startdate[$j],
                        'startyear' => $startyear[$j],
                        'attachment' => (isset($publication3_image_url[$j]) ? $publication3_image_url[$j] : ''),
                        'createdby' => $this->session->userdata('user_id'),
                      ));
                    }
                  }
                  //SYNC

                  if ($i == 0) $tempid = $insert;
                } catch (Exception $e) {
                  print_r($e);
                }
                $i++;
              }

              if ($faip->need_revisi == '1') {
                $temp = $this->main_mod->delete_where('user_faip_53', array("faip_id" => $id, 'status' => '0'));
              }
            }
            $j++;
          }
        }
      }

      //INSERT 54_
      //print_r($orgkomp);

      if ($is_submit == "1" || $save_partial == '54') {
        if ($faip->need_revisi == '1') {
          $temp = $this->main_mod->update('user_faip_54', array("faip_id" => $id), array("status" => 0));
        } else {
          if ($is_submit == "1") $temp = ''; //$temp = $this->main_mod->update('user_faip_13',array("faip_id" => $id),array("status" => 0));	
          else $temp = $this->main_mod->delete('user_faip_54', "faip_id", $id);
        }


        $nama_54_nama = $this->input->post('54_nama') <> null ? $this->input->post('54_nama') : "";
        if (is_array($nama_54_nama)) {
          $j = 0;
          foreach ($nama_54_nama as $val) {
            $tempid = '';
            $i = 0;
            //$a54_komp = $this->input->post('54_komp['.($j+1).']')<>null?$this->input->post('54_komp['.($j+1).']'):"";
            $a54_komp = $this->input->post('54_komp') <> null ? $this->input->post('54_komp') : "";
            if (is_array($a54_komp)) {
              $a54_komp = array_merge($a54_komp);
              $a54_komp = $a54_komp[$j];
            }
            if ($is_submit != "1" && !is_array($a54_komp[0]))
              $a54_komp = explode(",", $a54_komp[0]);

            if (!is_array($a54_komp)) {
              $a54_komp[] = null;
            }

            if (is_array($a54_komp)) {
              foreach ($a54_komp as $kompetensi) {
                $startdate = $this->input->post('54_startdate') <> null ? $this->input->post('54_startdate') : "";
                $startyear = $this->input->post('54_startyear') <> null ? $this->input->post('54_startyear') : "";

                $nama = $this->input->post('54_nama') <> null ? $this->input->post('54_nama') : "";
                $media_publikasi = $this->input->post('54_media_publikasi') <> null ? $this->input->post('54_media_publikasi') : "";
                $tingkatseminar = $this->input->post('54_tingkatseminar') <> null ? $this->input->post('54_tingkatseminar') : "";
                $tingkat = $this->input->post('54_tingkat') <> null ? $this->input->post('54_tingkat') : "";
                $uraian = $this->input->post('54_uraian') <> null ? $this->input->post('54_uraian') : "";
                $publication4_image_url = $this->input->post('54_publication4_image_url') <> null ? $this->input->post('54_publication4_image_url') : "";
                $tempidx = 0;
                $years = count($a54_komp);
                $data['bp_54'] = $this->main_mod->msrwhere('m_bakuan_penilaian', array('faip_num' => '54', 'faip_type' => 'p'), 'id', 'desc')->result();
                //echo $years.'<br />';
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
                $q = (isset($tingkatseminar[$j]) ? ($tingkatseminar[$j] != '' ? $tingkatseminar[$j] : 0) : 0);
                $r = (isset($tingkat[$j]) ? ($tingkat[$j] != '' ? $tingkat[$j] : 0) : 0);
                $t = $p * $q * $r;
                try {
                  $row = array(
                    'faip_id' => $id,
                    'parent' => ($i == 0) ? 0 : $tempid,
                    'startdate' => ($i == 0) ? $startdate[$j] : '',
                    'startyear' => ($i == 0) ? $startyear[$j] : '',
                    'nama' => ($i == 0) ? $nama[$j] : '',
                    'uraian' => ($i == 0) ? $uraian[$j] : '',
                    'media_publikasi' => ($i == 0) ? $media_publikasi[$j] : '',
                    'tingkat' => ($i == 0) ? $tingkat[$j] : '',
                    'tingkatseminar' => ($i == 0) ? $tingkatseminar[$j] : '',
                    'kompetensi' => $kompetensi,

                    'p' => $p,
                    'q' => $q,
                    'r' => $r,
                    't' => $t,

                    'attachment' => (($i == 0) ? (isset($publication4_image_url[$j]) ? $publication4_image_url[$j] : '') : ''),
                    'createdby' => $this->session->userdata('user_id'),
                    'modifiedby' => $this->session->userdata('user_id'),
                    'modifieddate' => date('Y-m-d H:i:s'),
                    'status' => 1,
                  );
                  $insert = '';
                  /*if($i==0 && $id54[$j]!="" && $is_submit=="1"){
											$where = array(
												"id" => $id54[$j]
											);
											//$update = $this->main_mod->update('user_faip_54',$where,$row);	
											$insert = $id54[$j];								
										}		
										else $insert = $this->main_mod->insert('user_faip_54',$row);*/

                  if ($i == 0 && $id54[$j] != "") {

                    $temp_ = $this->main_mod->msrwhere('user_faip_54', array('id' => $id54[$j], 'status' => '0'), 'id', 'desc')->result();
                    if (isset($temp_[0])) {
                      $where = array(
                        "id" => $id54[$j],
                        'status' => '0'
                      );
                      $update = $this->main_mod->update('user_faip_54', $where, $row);
                      $insert = $id54[$j];
                    } else
                      $insert = $this->main_mod->insert('user_faip_54', $row);
                  } else {
                    if ($id54[$j] != "") {
                      $temp__ = $this->main_mod->msrwhere('user_faip_54', array('parent' => $id54[$j], 'status' => '0', 'kompetensi' => $kompetensi), 'id', 'desc')->result();

                      if (isset($temp__[0])) {
                        $where = array(
                          "parent" => $id54[$j],
                          "status" => '0',
                          'kompetensi' => $kompetensi
                        );
                        $update = $this->main_mod->update('user_faip_54', $where, $row);
                        $insert = $id54[$j];
                      } else $insert = $this->main_mod->insert('user_faip_54', $row);
                    } else $insert = $this->main_mod->insert('user_faip_54', $row);
                  }

                  $te = '';
                  if ($tingkatseminar[$j] == '1')
                    $te = 'Lokal';
                  else if ($tingkatseminar[$j] == '2')
                    $te = 'Nasional';
                  else if ($tingkatseminar[$j] == '3')
                    $te = 'Internasional';
                  $se = '';
                  if ($tingkat[$j] == '1')
                    $se = 'Komplikasi masalah, kreatifitas & inovasi rendah, nilai manfaat dan dampak nilai teknologi rendah';
                  else if ($tingkat[$j] == '2')
                    $se = 'Komplikasi masalah, kreatifitas & inovasi sedang, nilai manfaat dan dampak nilai teknologi sedang';
                  else if ($tingkat[$j] == '3')
                    $se = 'Komplikasi masalah, kreatifitas & inovasi tinggi, nilai manfaat dan dampak nilai teknologi luas';
                  else if ($tingkat[$j] == '4')
                    $se = 'Komplikasi masalah, kreatifitas & inovasi sangat tinggi, nilai manfaat dan dampak nilai teknologi sangat luas';


                  //SYNC user_publication
                  if ($is_submit == "1") {
                    $o_data = $this->main_mod->msrwhere('user_publication', array('user_id' => $this->session->userdata('user_id'), 'status' => 1, 'type' => "4", 'topic' => $nama[$j], 'startyear' => $startyear[$j]), 'id', 'asc')->result();
                    if (count($o_data) > 0) {
                      $temp = $this->main_mod->update(
                        'user_publication',
                        array('user_id' => $this->session->userdata('user_id'), 'status' => 1, 'type' => "4", 'topic' => $nama[$j], 'startyear' => $startyear[$j]),
                        array(
                          'topic' => $nama[$j],
                          'media' => $media_publikasi[$j],
                          'type' => "4",
                          'location' => $location[$j],
                          'provinsi' => $provinsi[$j],
                          'negara' => $negara[$j],
                          'tingkat' => $te,
                          'tingkatmedia' => $se,

                          //'event' => $event,
                          'description' => $uraian[$j],
                          'startmonth' => $startdate[$j],
                          'startyear' => $startyear[$j],
                          'attachment' => (isset($publication4_image_url[$j]) ? $publication4_image_url[$j] : '')
                        )
                      );
                    } else {
                      $this->main_mod->insert('user_publication', array(
                        'user_id' => $this->session->userdata('user_id'),
                        'status' => 1,
                        'topic' => $nama[$j],
                        'media' => $media_publikasi[$j],
                        'type' => "4",
                        'location' => $location[$j],
                        'provinsi' => $provinsi[$j],
                        'negara' => $negara[$j],
                        'tingkat' => $te,
                        'tingkatmedia' => $se,

                        //'event' => $event,
                        'description' => $uraian[$j],
                        'startmonth' => $startdate[$j],
                        'startyear' => $startyear[$j],
                        'attachment' => (isset($publication4_image_url[$j]) ? $publication4_image_url[$j] : ''),
                        'createdby' => $this->session->userdata('user_id'),
                      ));
                    }
                  }
                  //SYNC

                  if ($i == 0) $tempid = $insert;
                } catch (Exception $e) {
                  print_r($e);
                }
                $i++;
              }

              if ($faip->need_revisi == '1') {
                $temp = $this->main_mod->delete_where('user_faip_54', array("faip_id" => $id, 'status' => '0'));
              }
            }
            $j++;
          }
        }
      }

      //INSERT 6_
      //print_r($orgkomp);

      if ($is_submit == "1" || $save_partial == '6') {
        if ($faip->need_revisi == '1') {
          $temp = $this->main_mod->update('user_faip_6', array("faip_id" => $id), array("status" => 0));
        } else {
          if ($is_submit == "1") $temp = ''; //$temp = $this->main_mod->update('user_faip_13',array("faip_id" => $id),array("status" => 0));	
          else $temp = $this->main_mod->delete('user_faip_6', "faip_id", $id);
        }


        $nama_6_nama = $this->input->post('6_nama') <> null ? $this->input->post('6_nama') : "";
        if (is_array($nama_6_nama)) {
          $j = 0;
          foreach ($nama_6_nama as $val) {
            $tempid = '';
            $i = 0;
            //$a6_komp = $this->input->post('6_komp['.($j+1).']')<>null?$this->input->post('6_komp['.($j+1).']'):"";
            $a6_komp = $this->input->post('6_komp') <> null ? $this->input->post('6_komp') : "";
            if (is_array($a6_komp)) {
              $a6_komp = array_merge($a6_komp);
              $a6_komp = $a6_komp[$j];
            }

            if ($is_submit != "1" && !is_array($a6_komp[0]))
              $a6_komp = explode(",", $a6_komp[0]);

            if (!is_array($a6_komp)) {
              $a6_komp[] = null;
            }

            if (is_array($a6_komp)) {
              foreach ($a6_komp as $kompetensi) {
                $jenisbahasa = $this->input->post('6_jenisbahasa') <> null ? $this->input->post('6_jenisbahasa') : "";
                $jenistulisan = $this->input->post('6_jenistulisan') <> null ? $this->input->post('6_jenistulisan') : "";
                $verbal = $this->input->post('6_verbal') <> null ? $this->input->post('6_verbal') : "";
                $nama = $this->input->post('6_nama') <> null ? $this->input->post('6_nama') : "";
                $skill_image_url = $this->input->post('6_skill_image_url') <> null ? $this->input->post('6_skill_image_url') : "";
                $tempidx = 0;
                $years = count($a6_komp);
                $data['bp_6'] = $this->main_mod->msrwhere('m_bakuan_penilaian', array('faip_num' => '6', 'faip_type' => 'p'), 'id', 'desc')->result();
                //echo $years.'<br />';
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
                $q = (isset($jenisbahasa[$j]) ? ($jenisbahasa[$j] != '' ? $jenisbahasa[$j] : 0) : 0);
                $r = (isset($verbal[$j]) ? ($verbal[$j] != '' ? $verbal[$j] : 0) : 0);
                $t = $p * $q * $r;
                try {
                  $row = array(
                    'faip_id' => $id,
                    'parent' => ($i == 0) ? 0 : $tempid,
                    'jenisbahasa' => ($i == 0) ? $jenisbahasa[$j] : '',
                    'verbal' => ($i == 0) ? $verbal[$j] : '',
                    'jenistulisan' => ($i == 0) ? $jenistulisan[$j] : '',
                    'nama' => ($i == 0) ? $nama[$j] : '',
                    'kompetensi' => $kompetensi,

                    'p' => $p,
                    'q' => $q,
                    'r' => $r,
                    't' => $t,

                    'attachment' => (($i == 0) ? (isset($skill_image_url[$j]) ? $skill_image_url[$j] : '') : ''),
                    'createdby' => $this->session->userdata('user_id'),
                    'modifiedby' => $this->session->userdata('user_id'),
                    'modifieddate' => date('Y-m-d H:i:s'),
                    'status' => 1,
                  );
                  $insert = '';
                  /*if($i==0 && $id6[$j]!="" && $is_submit=="1"){
											$where = array(
												"id" => $id6[$j]
											);
											//$update = $this->main_mod->update('user_faip_6',$where,$row);	
											$insert = $id6[$j];								
										}		
										else $insert = $this->main_mod->insert('user_faip_6',$row);	*/

                  if ($i == 0 && $id6[$j] != "") {

                    $temp_ = $this->main_mod->msrwhere('user_faip_6', array('id' => $id6[$j], 'status' => '0'), 'id', 'desc')->result();
                    if (isset($temp_[0])) {
                      $where = array(
                        "id" => $id6[$j],
                        'status' => '0'
                      );
                      $update = $this->main_mod->update('user_faip_6', $where, $row);
                      $insert = $id6[$j];
                    } else
                      $insert = $this->main_mod->insert('user_faip_6', $row);
                  } else {
                    if ($id6[$j] != "") {
                      $temp__ = $this->main_mod->msrwhere('user_faip_6', array('parent' => $id6[$j], 'status' => '0', 'kompetensi' => $kompetensi), 'id', 'desc')->result();

                      if (isset($temp__[0])) {
                        $where = array(
                          "parent" => $id6[$j],
                          "status" => '0',
                          'kompetensi' => $kompetensi
                        );
                        $update = $this->main_mod->update('user_faip_6', $where, $row);
                        $insert = $id6[$j];
                      } else $insert = $this->main_mod->insert('user_faip_6', $row);
                    } else $insert = $this->main_mod->insert('user_faip_6', $row);
                  }

                  $te = '';
                  if ($jenisbahasa[$j] == '1')
                    $te = 'Daerah';
                  else if ($jenisbahasa[$j] == '2')
                    $te = 'Nasional';
                  else if ($jenisbahasa[$j] == '3')
                    $te = 'Asing / Internasional';
                  $se = '';
                  if ($verbal[$j] == '2')
                    $se = 'Pasif';
                  else if ($verbal[$j] == '3')
                    $se = 'Aktif';

                  //SYNC user_skill
                  if ($is_submit == "1") {
                    $o_data = $this->main_mod->msrwhere('user_skill', array('user_id' => $this->session->userdata('user_id'), 'status' => 1, 'name' => $nama[$j]), 'id', 'asc')->result();
                    if (count($o_data) > 0) {
                      $temp = $this->main_mod->update(
                        'user_skill',
                        array('user_id' => $this->session->userdata('user_id'), 'status' => 1, 'name' => $nama[$j]),
                        array(
                          'name' => $nama[$j],
                          'jenisbahasa' => $te,
                          'proficiency' => $se,
                          'jenistulisan' => $jenistulisan[$j],
                          'attachment' => (isset($skill_image_url[$j]) ? $skill_image_url[$j] : '')
                        )
                      );
                    } else {
                      $this->main_mod->insert('user_skill', array(
                        'user_id' => $this->session->userdata('user_id'),
                        'status' => 1,
                        'name' => $nama[$j],
                        'jenisbahasa' => $te,
                        'proficiency' => $se,
                        'jenistulisan' => $jenistulisan[$j],
                        'attachment' => (isset($skill_image_url[$j]) ? $skill_image_url[$j] : ''),
                        'createdby' => $this->session->userdata('user_id'),
                      ));
                    }
                  }
                  //SYNC

                  if ($i == 0) $tempid = $insert;
                } catch (Exception $e) {
                  print_r($e);
                }
                $i++;
              }

              if ($faip->need_revisi == '1') {
                $temp = $this->main_mod->delete_where('user_faip_6', array("faip_id" => $id, 'status' => '0'));
              }
            }
            $j++;
          }
        }
      }

      //INSERT LAMPIRAN

      if ($is_submit == "1" || $save_partial == 'lam' || $save_partial == '3') {
        if ($is_submit == "1") $temp = ''; //$temp =$this->main_mod->update('user_faip_lam',array("faip_id" => $id),array("status" => 0));	
        else $temp = $this->main_mod->delete('user_faip_lam', "faip_id", $id);


        if (is_array($idlam)) {
          $j = 0;
          foreach ($idlam as $val) {
            $lam_aktifitas = $this->input->post('lam_aktifitas') <> null ? $this->input->post('lam_aktifitas') : "";
            $lam_nama = $this->input->post('lam_nama') <> null ? $this->input->post('lam_nama') : "";
            $lam_namaproyek = $this->input->post('lam_namaproyek') <> null ? $this->input->post('lam_namaproyek') : "";
            $lam_jangka = $this->input->post('lam_jangka') <> null ? $this->input->post('lam_jangka') : "";
            $lam_atasan = $this->input->post('lam_atasan') <> null ? $this->input->post('lam_atasan') : "";
            $lam_uraianproyek = $this->input->post('lam_uraianproyek') <> null ? $this->input->post('lam_uraianproyek') : "";
            $lam_uraiantugas = $this->input->post('lam_uraiantugas') <> null ? $this->input->post('lam_uraiantugas') : "";
            $lam_bagan = $this->input->post('lam_bagan') <> null ? $this->input->post('lam_bagan') : "";
            $lam_edu_image_url = $this->input->post('lam_edu_image_url') <> null ? $this->input->post('lam_edu_image_url') : "";

            try {
              $row = array(
                'faip_id' => $id,
                'aktifitas' => isset($lam_aktifitas[$j]) ? $lam_aktifitas[$j] : "",
                'nama' => isset($lam_nama[$j]) ? $lam_nama[$j] : "",
                'namaproyek' => isset($lam_namaproyek[$j]) ? $lam_namaproyek[$j] : "",
                'jangka' => isset($lam_jangka[$j]) ? $lam_jangka[$j] : "",
                'atasan' => isset($lam_atasan[$j]) ? $lam_atasan[$j] : "",
                'uraianproyek' => isset($lam_uraianproyek[$j]) ? $lam_uraianproyek[$j] : "",
                'uraiantugas' => isset($lam_uraiantugas[$j]) ? $lam_uraiantugas[$j] : "",
                'bagan' => isset($lam_bagan[$j]) ? $lam_bagan[$j] : "",
                'attachment' => isset($lam_edu_image_url[$j]) ? $lam_edu_image_url[$j] : "",
                'createdby' => $this->session->userdata('user_id'),
                'modifiedby' => $this->session->userdata('user_id'),
                'modifieddate' => date('Y-m-d H:i:s'),
                'status' => 1,
              );
              if ($val != "" && $is_submit == "1") {
                $where = array(
                  "id" => $val
                );
                //$update = $this->main_mod->update('user_faip_lam',$where,$row);		
              } else $insert = $this->main_mod->insert('user_faip_lam', $row);
            } catch (Exception $e) {
              print_r($e);
            }
            $j++;
          }
        }
      }


      //else
      //redirect('faip/editfaip/'.$id);

      echo $id;

      if ($is_submit == "1")
        redirect('faip');

      //echo 'ok';

    }
    // Form validation vailed
    else {
      $this->load->view('member/faip_view2', $data);
    }
    return;
  }

  function download_faip()
  {

    $id_faip = $this->uri->segment(3);


    $is_access = $this->main_mod->msrquery('select a.id from members a join users b on a.person_id=b.id where username <> "" and person_id="' . $this->session->userdata('user_id') . '" and thru_date >= curdate()')->result();
    if (!isset($is_access[0])) {
      $is_access2 = $this->main_mod->msrquery('select id from users where id="' . $this->session->userdata('user_id') . '" and by_pass = 1')->num_rows();
      if ($is_access2 == 0)
        redirect('member');
    }

    $this->load->model('main_mod');
    $this->load->model('faip_model');

    $faip = $this->faip_model->get_faip_by_id($id_faip);
    $id   = isset($faip->user_id) ? $faip->user_id : "";

    $data['id_faip'] = $id_faip;


    if ($id == $this->session->userdata('user_id')) {

      $obj_member = $this->members_model->get_member_by_id($id);
      $data['row'] = $obj_member;
      $data['kta'] = $this->members_model->get_kta_data_by_personid($id);

      $data['user_faip'] = $faip;
      $data['user_faip_11'] = $this->main_mod->msrwhere('user_faip_11', array('faip_id' => $id_faip, 'status' => 1), 'id', 'asc')->result();
      $data['user_faip_12'] = $this->main_mod->msrwhere('user_faip_12', array('faip_id' => $id_faip, 'status' => 1), 'id', 'asc')->result();
      $data['user_faip_111'] = $this->main_mod->msrwhere('user_faip_111', array('faip_id' => $id_faip, 'status' => 1), 'id', 'asc')->result();
      $data['user_faip_112'] = $this->main_mod->msrwhere('user_faip_112', array('faip_id' => $id_faip, 'status' => 1), 'id', 'asc')->result();
      $data['user_faip_113'] = $this->main_mod->msrwhere('user_faip_113', array('faip_id' => $id_faip, 'status' => 1), 'id', 'asc')->result();
      $data['user_faip_13'] = $this->faip_model->get_all_faip_comp($id_faip, 'user_faip_13');
      $data['user_faip_14'] = $this->faip_model->get_all_faip_comp($id_faip, 'user_faip_14');
      $data['user_faip_15'] = $this->faip_model->get_all_faip_comp($id_faip, 'user_faip_15');
      $data['user_faip_16'] = $this->faip_model->get_all_faip_comp($id_faip, 'user_faip_16');
      $data['user_faip_21'] = $this->faip_model->get_all_faip_comp($id_faip, 'user_faip_21');
      $data['user_faip_22'] = $this->faip_model->get_all_faip_comp($id_faip, 'user_faip_22');
      $data['user_faip_3'] = $this->faip_model->get_all_faip_comp($id_faip, 'user_faip_3');
      $data['user_faip_4'] = $this->faip_model->get_all_faip_comp($id_faip, 'user_faip_4');
      $data['user_faip_51'] = $this->faip_model->get_all_faip_comp($id_faip, 'user_faip_51');
      $data['user_faip_52'] = $this->faip_model->get_all_faip_comp($id_faip, 'user_faip_52');
      $data['user_faip_53'] = $this->faip_model->get_all_faip_comp($id_faip, 'user_faip_53');
      $data['user_faip_54'] = $this->faip_model->get_all_faip_comp($id_faip, 'user_faip_54');
      $data['user_faip_6'] = $this->faip_model->get_all_faip_comp($id_faip, 'user_faip_6');
      $data['user_faip_lam'] = $this->main_mod->msrwhere('user_faip_lam', array('faip_id' => $id_faip, 'status' => 1), 'id', 'asc')->result();

      //print_r($data['user_faip_13']);

      $data['m_komp'] = $this->main_mod->msrquery('select value,title from no_kompetensi a join m_kompetensi b on a.value=b.code')->result();
      $data['m_act_13'] = $this->main_mod->msrquery('select code as value,title from m_kompetensi where code like "W.1%" order by id asc')->result();
      $data['m_act_15'] = $this->main_mod->msrquery('select code as value,title from m_kompetensi where code like "W.2%" or code like "W.4%" or code like "P.10%" order by id asc')->result();
      $data['m_act_16'] = $this->main_mod->msrquery('select code as value,title from m_kompetensi where code like "W.1%" or code like "W.4%" or code like "P.10%" order by id asc')->result();

      $data['m_act_3'] = $this->main_mod->msrquery('select code as value,title from m_kompetensi where 
			code like "W.2%" or 
			code like "W.3%" or 
			code like "W.4%" or 
			code like "P.6%" or 
			code like "P.7%" or 
			code like "P.8%" or 
			code like "P.9%" or 
			code like "P.10%" or 
			code like "P.11%"  
			order by id asc')->result();
      $data['m_act_4'] = $this->main_mod->msrquery('select code as value,title from m_kompetensi where code like "P.5%" order by id asc')->result();
      $data['m_act_51'] = $this->main_mod->msrquery('select code as value,title from m_kompetensi where code like "W.4%" order by id asc')->result();
      $data['m_act_53'] = $this->main_mod->msrquery('select code as value,title from m_kompetensi where code like "W.2%" order by id asc')->result();
      $data['m_act_54'] = $this->main_mod->msrquery('select code as value,title from m_kompetensi where code like "P.6%" order by id asc')->result();


      //$data['m_degree']=$this->main_mod->msrwhere('education_type',array('HAS_TABLE'=>'Y'),'SEQ','asc')->result();
      $data['m_degree'] = $this->main_mod->msrquery('select * from education_type where HAS_TABLE="Y" or seq=9 order by SEQ asc')->result();

      $data['m_bk'] = $this->main_mod->msrwhere('m_bk', array('faip' => 1), 'id', 'asc')->result();

      $data = $data + $this->_get_user_details($id);
      $data['emailx'] = $this->session->userdata('email');

      // Data Bakuan Penilaian
      $faipNumWithPenilaian_list = array('12', '13', '14', '15', '16', '3', '4', '51', '52', '53', '54', '6');
      foreach ($faipNumWithPenilaian_list as $faip_num) {
        $data['bp_' . $faip_num] = $this->main_mod->msrwhere('m_bakuan_penilaian', array('faip_num' => $faip_num), 'id', 'asc')->result();
      }

      //$this->load->view('admin/download_faip', $data);
      $this->load->view('member/download_faip_2', $data);
    }
  }

  function deletefaip()
  {

    $is_access = $this->main_mod->msrquery('select a.id from members a join users b on a.person_id=b.id where username <> "" and person_id="' . $this->session->userdata('user_id') . '" and thru_date >= curdate()')->result();
    if (!isset($is_access[0])) {
      $is_access2 = $this->main_mod->msrquery('select id from users where id="' . $this->session->userdata('user_id') . '" and by_pass = 1')->num_rows();
      if ($is_access2 == 0)
        redirect('member');
    }

    $id_faip = $this->uri->segment(3);

    $this->load->model('faip_model');
    $faip = $this->faip_model->get_faip_by_id($id_faip);
    $id = isset($faip->user_id) ? $faip->user_id : "";
    $status = isset($faip->status_faip) ? $faip->status_faip : "";
    //echo $id;
    if ($id == $this->session->userdata('user_id') && $status == 0) {
      $this->main_mod->delete('user_faip', 'id', $id_faip);
      /*$this->main_mod->delete('user_faip_3','faip_id',$id_faip);			
			$this->main_mod->delete('user_faip_4','faip_id',$id_faip);	
			$this->main_mod->delete('user_faip_6','faip_id',$id_faip);
			$this->main_mod->delete('user_faip_11','faip_id',$id_faip);				
			$this->main_mod->delete('user_faip_12','faip_id',$id_faip);	
			$this->main_mod->delete('user_faip_13','faip_id',$id_faip);
			$this->main_mod->delete('user_faip_14','faip_id',$id_faip);				
			$this->main_mod->delete('user_faip_15','faip_id',$id_faip);	
			$this->main_mod->delete('user_faip_16','faip_id',$id_faip);
			$this->main_mod->delete('user_faip_21','faip_id',$id_faip);				
			$this->main_mod->delete('user_faip_22','faip_id',$id_faip);	
			$this->main_mod->delete('user_faip_51','faip_id',$id_faip);
			$this->main_mod->delete('user_faip_52','faip_id',$id_faip);	
			$this->main_mod->delete('user_faip_53','faip_id',$id_faip);				
			$this->main_mod->delete('user_faip_54','faip_id',$id_faip);	
			$this->main_mod->delete('user_faip_111','faip_id',$id_faip);
			$this->main_mod->delete('user_faip_112','faip_id',$id_faip);	
			$this->main_mod->delete('user_faip_113','faip_id',$id_faip);
			$this->main_mod->delete('user_faip_lam','faip_id',$id_faip);	*/
    }
    redirect('faip');
  }

  function edu_upload()
  {
    if (! $this->session->userdata('user_id')) redirect('login');

    $id = $this->input->post('file') <> null ? $this->input->post('file') : "";

    if (isset($_FILES['file']['name'])) {
      $valid_formats_img = array("jpg", "jpeg", "gif", "png", "pdf", "bmp");
      $name = $_FILES['file']['name'];
      $size = $_FILES['file']['size'];

      $extx = pathinfo($name, PATHINFO_EXTENSION);

      if (strlen($name)) {
        //list($txt, $ext) = explode(".", $name);
        if (in_array(strtolower($extx), $valid_formats_img)) {
          if ($size < (710000)) {
            //$actual_image_name = time().substr(str_replace(" ", "_", $txt), 5).".".$ext;
            $actual_image_name = time() . "_EDU_" . $this->session->userdata('user_id') . "." . $extx;
            $config['upload_path'] = './assets/uploads/';
            $config['allowed_types'] = '*';
            $config['max_size']  = '50024';
            $config['file_name'] = $actual_image_name;

            $this->load->library('upload', $config);

            if ($this->upload->do_upload('file')) {

              /*$config['image_library'] = 'gd2';
							$config['source_image'] = './assets/uploads/'.$actual_image_name;
							$config['maintain_ratio'] = TRUE;
							$config['width']    = 300;
							$config['height']   = 300;

							$this->load->library('image_lib', $config); 

							if (!$this->image_lib->resize()) {
								//echo $this->image_lib->display_errors();
							}
							*/

              echo "<input type='hidden' name='12_edu_image_url[]' value='" . $actual_image_name . "'>";
              //echo "<img src='".base_url()."/assets/uploads/".$actual_image_name."'  class='ava_discus' width='150'>";
              echo "<a href='" . base_url() . "/assets/uploads/" . $actual_image_name . "' target='_blank' class='ava_discus'>" . $actual_image_name . "</a>";
              //echo "<a href='".base_url()."/assets/uploads/".$actual_image_name."'  class='ava_discus'>".$actual_image_name."</a>";
            } else
              echo "<span style:'color:red'>Please try again." . $extx . "</span>";
          } else
            echo "<span style:'color:red'>Sorry, maximum file size should be 700 KB</span>";
        } else
          echo "<span style:'color:red'>Format file yang diijinkan (gif|jpg|png|jpeg|pdf|bmp).</span>";
      } else
        echo "<span style:'color:red'>Please select an image (jpg, gif, png, bmp file) or PDF file.</span>";
    } else
      echo "<span style:'color:red'>Please select an image (jpg, gif, png, bmp file) or PDF file.</span>";
  }

  function org_upload()
  {
    if (! $this->session->userdata('user_id')) redirect('login');

    $id = $this->input->post('file') <> null ? $this->input->post('file') : "";

    if (isset($_FILES['file']['name'])) {
      $valid_formats_img = array("jpg", "jpeg", "gif", "png", "pdf", "bmp");
      $name = $_FILES['file']['name'];
      $size = $_FILES['file']['size'];

      $extx = pathinfo($name, PATHINFO_EXTENSION);

      if (strlen($name)) {
        if (in_array(strtolower($extx), $valid_formats_img)) {
          if ($size < (710000)) {

            $actual_image_name = time() . "_ORG_" . $this->session->userdata('user_id') . "." . $extx;
            $config['upload_path'] = './assets/uploads/';
            $config['allowed_types'] = '*';
            $config['max_size']  = '50024';
            $config['file_name'] = $actual_image_name;

            $this->load->library('upload', $config);

            if ($this->upload->do_upload('file')) {



              echo "<input type='hidden' name='13_org_image_url[]' value='" . $actual_image_name . "'>";

              echo "<a href='" . base_url() . "/assets/uploads/" . $actual_image_name . "' target='_blank' class='ava_discus'>" . $actual_image_name . "</a>";
            } else
              echo "<span style:'color:red'>Please try again." . $extx . "</span>";
          } else
            echo "<span style:'color:red'>Sorry, maximum file size should be 700 KB</span>";
        } else
          echo "<span style:'color:red'>Format file yang diijinkan (gif|jpg|png|jpeg|pdf|bmp).</span>";
      } else
        echo "<span style:'color:red'>Please select an image (jpg, gif, png, bmp file) or PDF file.</span>";
    } else
      echo "<span style:'color:red'>Please select an image (jpg, gif, png, bmp file) or PDF file.</span>";
  }

  function award_upload()
  {
    if (! $this->session->userdata('user_id')) redirect('login');

    $id = $this->input->post('file') <> null ? $this->input->post('file') : "";

    if (isset($_FILES['file']['name'])) {
      $valid_formats_img = array("jpg", "jpeg", "gif", "png", "pdf", "bmp");
      $name = $_FILES['file']['name'];
      $size = $_FILES['file']['size'];

      $extx = pathinfo($name, PATHINFO_EXTENSION);

      if (strlen($name)) {
        if (in_array(strtolower($extx), $valid_formats_img)) {
          if ($size < (710000)) {

            $actual_image_name = time() . "_AWD_" . $this->session->userdata('user_id') . "." . $extx;
            $config['upload_path'] = './assets/uploads/';
            $config['allowed_types'] = '*';
            $config['max_size']  = '50024';
            $config['file_name'] = $actual_image_name;

            $this->load->library('upload', $config);

            if ($this->upload->do_upload('file')) {



              echo "<input type='hidden' name='14_award_image_url[]' value='" . $actual_image_name . "'>";

              echo "<a href='" . base_url() . "/assets/uploads/" . $actual_image_name . "' target='_blank' class='ava_discus'>" . $actual_image_name . "</a>";
            } else
              echo "<span style:'color:red'>Please try again." . $extx . "</span>";
          } else
            echo "<span style:'color:red'>Sorry, maximum file size should be 700 KB</span>";
        } else
          echo "<span style:'color:red'>Format file yang diijinkan (gif|jpg|png|jpeg|pdf|bmp).</span>";
      } else
        echo "<span style:'color:red'>Please select an image (jpg, gif, png, bmp file) or PDF file.</span>";
    } else
      echo "<span style:'color:red'>Please select an image (jpg, gif, png, bmp file) or PDF file.</span>";
  }

  function course_upload()
  {
    if (! $this->session->userdata('user_id')) redirect('login');

    $id = $this->input->post('file') <> null ? $this->input->post('file') : "";

    if (isset($_FILES['file']['name'])) {
      $valid_formats_img = array("jpg", "jpeg", "gif", "png", "pdf", "bmp");
      $name = $_FILES['file']['name'];
      $size = $_FILES['file']['size'];

      $extx = pathinfo($name, PATHINFO_EXTENSION);

      if (strlen($name)) {
        if (in_array(strtolower($extx), $valid_formats_img)) {
          if ($size < (710000)) {

            $actual_image_name = time() . "_CRS_" . $this->session->userdata('user_id') . "." . $extx;
            $config['upload_path'] = './assets/uploads/';
            $config['allowed_types'] = '*';
            $config['max_size']  = '50024';
            $config['file_name'] = $actual_image_name;

            $this->load->library('upload', $config);

            if ($this->upload->do_upload('file')) {



              echo "<input type='hidden' name='15_course_image_url[]' value='" . $actual_image_name . "'>";

              echo "<a href='" . base_url() . "/assets/uploads/" . $actual_image_name . "' target='_blank' class='ava_discus'>" . $actual_image_name . "</a>";
            } else
              echo "<span style:'color:red'>Please try again." . $extx . "</span>";
          } else
            echo "<span style:'color:red'>Sorry, maximum file size should be 700 KB</span>";
        } else
          echo "<span style:'color:red'>Format file yang diijinkan (gif|jpg|png|jpeg|pdf|bmp).</span>";
      } else
        echo "<span style:'color:red'>Please select an image (jpg, gif, png, bmp file) or PDF file.</span>";
    } else
      echo "<span style:'color:red'>Please select an image (jpg, gif, png, bmp file) or PDF file.</span>";
  }

  function cert_upload()
  {
    if (! $this->session->userdata('user_id')) redirect('login');

    $id = $this->input->post('file') <> null ? $this->input->post('file') : "";

    if (isset($_FILES['file']['name'])) {
      $valid_formats_img = array("jpg", "jpeg", "gif", "png", "pdf", "bmp");
      $name = $_FILES['file']['name'];
      $size = $_FILES['file']['size'];

      $extx = pathinfo($name, PATHINFO_EXTENSION);

      if (strlen($name)) {
        if (in_array(strtolower($extx), $valid_formats_img)) {
          if ($size < (710000)) {

            $actual_image_name = time() . "_CERT_" . $this->session->userdata('user_id') . "." . $extx;
            $config['upload_path'] = './assets/uploads/';
            $config['allowed_types'] = '*';
            $config['max_size']  = '50024';
            $config['file_name'] = $actual_image_name;

            $this->load->library('upload', $config);

            if ($this->upload->do_upload('file')) {



              echo "<input type='hidden' name='16_cert_image_url[]' value='" . $actual_image_name . "'>";

              echo "<a href='" . base_url() . "/assets/uploads/" . $actual_image_name . "' target='_blank' class='ava_discus'>" . $actual_image_name . "</a>";
            } else
              echo "<span style:'color:red'>Please try again." . $extx . "</span>";
          } else
            echo "<span style:'color:red'>Sorry, maximum file size should be 700 KB</span>";
        } else
          echo "<span style:'color:red'>Format file yang diijinkan (gif|jpg|png|jpeg|pdf|bmp).</span>";
      } else
        echo "<span style:'color:red'>Please select an image (jpg, gif, png, bmp file) or PDF file.</span>";
    } else
      echo "<span style:'color:red'>Please select an image (jpg, gif, png, bmp file) or PDF file.</span>";
  }

  function publication1_upload()
  {
    if (! $this->session->userdata('user_id')) redirect('login');

    $id = $this->input->post('file') <> null ? $this->input->post('file') : "";

    if (isset($_FILES['file']['name'])) {
      $valid_formats_img = array("jpg", "jpeg", "gif", "png", "pdf", "bmp");
      $name = $_FILES['file']['name'];
      $size = $_FILES['file']['size'];

      $extx = pathinfo($name, PATHINFO_EXTENSION);

      if (strlen($name)) {
        if (in_array(strtolower($extx), $valid_formats_img)) {
          if ($size < (710000)) {

            $actual_image_name = time() . "_PBC_" . $this->session->userdata('user_id') . "." . $extx;
            $config['upload_path'] = './assets/uploads/';
            $config['allowed_types'] = '*';
            $config['max_size']  = '50024';
            $config['file_name'] = $actual_image_name;

            $this->load->library('upload', $config);

            if ($this->upload->do_upload('file')) {



              echo "<input type='hidden' name='51_publication1_image_url[]' value='" . $actual_image_name . "'>";

              echo "<a href='" . base_url() . "/assets/uploads/" . $actual_image_name . "' target='_blank' class='ava_discus'>" . $actual_image_name . "</a>";
            } else
              echo "<span style:'color:red'>Please try again." . $extx . "</span>";
          } else
            echo "<span style:'color:red'>Sorry, maximum file size should be 700 KB</span>";
        } else
          echo "<span style:'color:red'>Format file yang diijinkan (gif|jpg|png|jpeg|pdf|bmp).</span>";
      } else
        echo "<span style:'color:red'>Please select an image (jpg, gif, png, bmp file) or PDF file.</span>";
    } else
      echo "<span style:'color:red'>Please select an image (jpg, gif, png, bmp file) or PDF file.</span>";
  }

  function publication2_upload()
  {
    if (! $this->session->userdata('user_id')) redirect('login');

    $id = $this->input->post('file') <> null ? $this->input->post('file') : "";

    if (isset($_FILES['file']['name'])) {
      $valid_formats_img = array("jpg", "jpeg", "gif", "png", "pdf", "bmp");
      $name = $_FILES['file']['name'];
      $size = $_FILES['file']['size'];

      $extx = pathinfo($name, PATHINFO_EXTENSION);

      if (strlen($name)) {
        if (in_array(strtolower($extx), $valid_formats_img)) {
          if ($size < (710000)) {

            $actual_image_name = time() . "_PBC_" . $this->session->userdata('user_id') . "." . $extx;
            $config['upload_path'] = './assets/uploads/';
            $config['allowed_types'] = '*';
            $config['max_size']  = '50024';
            $config['file_name'] = $actual_image_name;

            $this->load->library('upload', $config);

            if ($this->upload->do_upload('file')) {



              echo "<input type='hidden' name='52_publication2_image_url[]' value='" . $actual_image_name . "'>";

              echo "<a href='" . base_url() . "/assets/uploads/" . $actual_image_name . "' target='_blank' class='ava_discus'>" . $actual_image_name . "</a>";
            } else
              echo "<span style:'color:red'>Please try again." . $extx . "</span>";
          } else
            echo "<span style:'color:red'>Sorry, maximum file size should be 700 KB</span>";
        } else
          echo "<span style:'color:red'>Format file yang diijinkan (gif|jpg|png|jpeg|pdf|bmp).</span>";
      } else
        echo "<span style:'color:red'>Please select an image (jpg, gif, png, bmp file) or PDF file.</span>";
    } else
      echo "<span style:'color:red'>Please select an image (jpg, gif, png, bmp file) or PDF file.</span>";
  }

  function publication3_upload()
  {
    if (! $this->session->userdata('user_id')) redirect('login');

    $id = $this->input->post('file') <> null ? $this->input->post('file') : "";

    if (isset($_FILES['file']['name'])) {
      $valid_formats_img = array("jpg", "jpeg", "gif", "png", "pdf", "bmp");
      $name = $_FILES['file']['name'];
      $size = $_FILES['file']['size'];

      $extx = pathinfo($name, PATHINFO_EXTENSION);

      if (strlen($name)) {
        if (in_array(strtolower($extx), $valid_formats_img)) {
          if ($size < (710000)) {

            $actual_image_name = time() . "_PBC_" . $this->session->userdata('user_id') . "." . $extx;
            $config['upload_path'] = './assets/uploads/';
            $config['allowed_types'] = '*';
            $config['max_size']  = '50024';
            $config['file_name'] = $actual_image_name;

            $this->load->library('upload', $config);

            if ($this->upload->do_upload('file')) {



              echo "<input type='hidden' name='53_publication3_image_url[]' value='" . $actual_image_name . "'>";

              echo "<a href='" . base_url() . "/assets/uploads/" . $actual_image_name . "' target='_blank' class='ava_discus'>" . $actual_image_name . "</a>";
            } else
              echo "<span style:'color:red'>Please try again." . $extx . "</span>";
          } else
            echo "<span style:'color:red'>Sorry, maximum file size should be 700 KB</span>";
        } else
          echo "<span style:'color:red'>Format file yang diijinkan (gif|jpg|png|jpeg|pdf|bmp).</span>";
      } else
        echo "<span style:'color:red'>Please select an image (jpg, gif, png, bmp file) or PDF file.</span>";
    } else
      echo "<span style:'color:red'>Please select an image (jpg, gif, png, bmp file) or PDF file.</span>";
  }

  function publication4_upload()
  {
    if (! $this->session->userdata('user_id')) redirect('login');

    $id = $this->input->post('file') <> null ? $this->input->post('file') : "";

    if (isset($_FILES['file']['name'])) {
      $valid_formats_img = array("jpg", "jpeg", "gif", "png", "pdf", "bmp");
      $name = $_FILES['file']['name'];
      $size = $_FILES['file']['size'];

      $extx = pathinfo($name, PATHINFO_EXTENSION);

      if (strlen($name)) {
        if (in_array(strtolower($extx), $valid_formats_img)) {
          if ($size < (710000)) {

            $actual_image_name = time() . "_PBC_" . $this->session->userdata('user_id') . "." . $extx;
            $config['upload_path'] = './assets/uploads/';
            $config['allowed_types'] = '*';
            $config['max_size']  = '50024';
            $config['file_name'] = $actual_image_name;

            $this->load->library('upload', $config);

            if ($this->upload->do_upload('file')) {



              echo "<input type='hidden' name='54_publication4_image_url[]' value='" . $actual_image_name . "'>";

              echo "<a href='" . base_url() . "/assets/uploads/" . $actual_image_name . "' target='_blank' class='ava_discus'>" . $actual_image_name . "</a>";
            } else
              echo "<span style:'color:red'>Please try again." . $extx . "</span>";
          } else
            echo "<span style:'color:red'>Sorry, maximum file size should be 700 KB</span>";
        } else
          echo "<span style:'color:red'>Format file yang diijinkan (gif|jpg|png|jpeg|pdf|bmp).</span>";
      } else
        echo "<span style:'color:red'>Please select an image (jpg, gif, png, bmp file) or PDF file.</span>";
    } else
      echo "<span style:'color:red'>Please select an image (jpg, gif, png, bmp file) or PDF file.</span>";
  }

  function skill_upload()
  {
    if (! $this->session->userdata('user_id')) redirect('login');

    $id = $this->input->post('file') <> null ? $this->input->post('file') : "";

    if (isset($_FILES['file']['name'])) {
      $valid_formats_img = array("jpg", "jpeg", "gif", "png", "pdf", "bmp");
      $name = $_FILES['file']['name'];
      $size = $_FILES['file']['size'];

      $extx = pathinfo($name, PATHINFO_EXTENSION);

      if (strlen($name)) {
        if (in_array(strtolower($extx), $valid_formats_img)) {
          if ($size < (710000)) {

            $actual_image_name = time() . "_SKL_" . $this->session->userdata('user_id') . "." . $extx;
            $config['upload_path'] = './assets/uploads/';
            $config['allowed_types'] = '*';
            $config['max_size']  = '50024';
            $config['file_name'] = $actual_image_name;

            $this->load->library('upload', $config);

            if ($this->upload->do_upload('file')) {



              echo "<input type='hidden' name='6_skill_image_url[]' value='" . $actual_image_name . "'>";

              echo "<a href='" . base_url() . "/assets/uploads/" . $actual_image_name . "' target='_blank' class='ava_discus'>" . $actual_image_name . "</a>";
            } else
              echo "<span style:'color:red'>Please try again." . $extx . "</span>";
          } else
            echo "<span style:'color:red'>Sorry, maximum file size should be 700 KB</span>";
        } else
          echo "<span style:'color:red'>Format file yang diijinkan (gif|jpg|png|jpeg|pdf|bmp).</span>";
      } else
        echo "<span style:'color:red'>Please select an image (jpg, gif, png, bmp file) or PDF file.</span>";
    } else
      echo "<span style:'color:red'>Please select an image (jpg, gif, png, bmp file) or PDF file.</span>";
  }

  function lam_upload()
  {
    if (! $this->session->userdata('user_id')) redirect('login');

    $id = $this->input->post('file') <> null ? $this->input->post('file') : "";

    if (isset($_FILES['file']['name'])) {
      $valid_formats_img = array("jpg", "jpeg", "gif", "png", "pdf", "bmp");
      $name = $_FILES['file']['name'];
      $size = $_FILES['file']['size'];

      $extx = pathinfo($name, PATHINFO_EXTENSION);

      if (strlen($name)) {
        //list($txt, $ext) = explode(".", $name);
        if (in_array(strtolower($extx), $valid_formats_img)) {
          if ($size < (710000)) {
            //$actual_image_name = time().substr(str_replace(" ", "_", $txt), 5).".".$ext;
            $actual_image_name = time() . "_LAM_" . $this->session->userdata('user_id') . "." . $extx;
            $config['upload_path'] = './assets/uploads/';
            $config['allowed_types'] = '*';
            $config['max_size']  = '50024';
            $config['file_name'] = $actual_image_name;

            $this->load->library('upload', $config);

            if ($this->upload->do_upload('file')) {

              $config['image_library'] = 'gd2';
              $config['source_image'] = './assets/uploads/' . $actual_image_name;
              $config['maintain_ratio'] = TRUE;
              $config['width']    = 300;
              $config['height']   = 300;

              $this->load->library('image_lib', $config);

              if (!$this->image_lib->resize()) {
                //echo $this->image_lib->display_errors();
              }


              echo "<input type='hidden' name='lam_edu_image_url[]' value='" . $actual_image_name . "'>";
              //echo "<img src='".base_url()."/assets/uploads/".$actual_image_name."'  class='ava_discus' width='150'>";
              echo "<a href='" . base_url() . "/assets/uploads/" . $actual_image_name . "' target='_blank' class='ava_discus'>" . $actual_image_name . "</a>";
              //echo "<a href='".base_url()."/assets/uploads/".$actual_image_name."'  class='ava_discus'>".$actual_image_name."</a>";
            } else
              echo "<span style:'color:red'>Please try again." . $extx . "</span>";
          } else
            echo "<span style:'color:red'>Sorry, maximum file size should be 700 KB</span>";
        } else
          echo "<span style:'color:red'>Format file yang diijinkan (gif|jpg|png|jpeg|pdf|bmp).</span>";
      } else
        echo "<span style:'color:red'>Please select an image (jpg, gif, png, bmp file) or PDF file.</span>";
    } else
      echo "<span style:'color:red'>Please select an image (jpg, gif, png, bmp file) or PDF file.</span>";
  }

  function exp_upload()
  {
    if (! $this->session->userdata('user_id')) redirect('login');

    $id = $this->input->post('file') <> null ? $this->input->post('file') : "";

    if (isset($_FILES['file']['name'])) {
      $valid_formats_img = array("jpg", "jpeg", "gif", "png", "pdf", "bmp");
      $name = $_FILES['file']['name'];
      $size = $_FILES['file']['size'];

      $extx = pathinfo($name, PATHINFO_EXTENSION);

      if (strlen($name)) {
        if (in_array(strtolower($extx), $valid_formats_img)) {
          if ($size < (710000)) {

            $actual_image_name = time() . "_EXP_" . $this->session->userdata('user_id') . "." . $extx;
            $config['upload_path'] = './assets/uploads/';
            $config['allowed_types'] = '*';
            $config['max_size']  = '50024';
            $config['file_name'] = $actual_image_name;

            $this->load->library('upload', $config);

            if ($this->upload->do_upload('file')) {



              echo "<input type='hidden' name='3_exp_image_url[]' value='" . $actual_image_name . "'>";

              echo "<a href='" . base_url() . "/assets/uploads/" . $actual_image_name . "' target='_blank' class='ava_discus'>" . $actual_image_name . "</a>";
            } else
              echo "<span style:'color:red'>Please try again." . $extx . "</span>";
          } else
            echo "<span style:'color:red'>Sorry, maximum file size should be 700 KB</span>";
        } else
          echo "<span style:'color:red'>Format file yang diijinkan (gif|jpg|png|jpeg|pdf|bmp).</span>";
      } else
        echo "<span style:'color:red'>Please select an image (jpg, gif, png, bmp file) or PDF file.</span>";
    } else
      echo "<span style:'color:red'>Please select an image (jpg, gif, png, bmp file) or PDF file.</span>";
  }

  function exp2_upload()
  {
    if (! $this->session->userdata('user_id')) redirect('login');

    $id = $this->input->post('file') <> null ? $this->input->post('file') : "";

    if (isset($_FILES['file']['name'])) {
      $valid_formats_img = array("jpg", "jpeg", "gif", "png", "pdf", "bmp");
      $name = $_FILES['file']['name'];
      $size = $_FILES['file']['size'];

      $extx = pathinfo($name, PATHINFO_EXTENSION);

      if (strlen($name)) {
        if (in_array(strtolower($extx), $valid_formats_img)) {
          if ($size < (710000)) {

            $actual_image_name = time() . "_EXP2_" . $this->session->userdata('user_id') . "." . $extx;
            $config['upload_path'] = './assets/uploads/';
            $config['allowed_types'] = '*';
            $config['max_size']  = '50024';
            $config['file_name'] = $actual_image_name;

            $this->load->library('upload', $config);

            if ($this->upload->do_upload('file')) {



              echo "<input type='hidden' name='4_exp2_image_url[]' value='" . $actual_image_name . "'>";

              echo "<a href='" . base_url() . "/assets/uploads/" . $actual_image_name . "' target='_blank' class='ava_discus'>" . $actual_image_name . "</a>";
            } else
              echo "<span style:'color:red'>Please try again." . $extx . "</span>";
          } else
            echo "<span style:'color:red'>Sorry, maximum file size should be 700 KB</span>";
        } else
          echo "<span style:'color:red'>Format file yang diijinkan (gif|jpg|png|jpeg|pdf|bmp).</span>";
      } else
        echo "<span style:'color:red'>Please select an image (jpg, gif, png, bmp file) or PDF file.</span>";
    } else
      echo "<span style:'color:red'>Please select an image (jpg, gif, png, bmp file) or PDF file.</span>";
  }

  /**
   * Called when submit FAIP data has failed by Ajax from frontend
   */
  function log_faip()
  {

    $id = $this->input->post('id') <> null ? $this->input->post('id') : "";
    $error = $this->input->post('error') <> null ? $this->input->post('error') : "";

    $rowInsert = array(
      //'user_id' => (isset($this->session->userdata('user_id'))?$this->session->userdata('user_id'):""),
      'faip_id' => $id,
      'log_error' => $error,
    );
    $this->main_mod->insert('log_faip', $rowInsert);
  }

  public function form_import()
  {

    $data = array();

    //$data = '';
    $data['title'] = 'PII | FAIP';
    $data['email'] = $this->session->userdata('email');
    //$this->load->view('member/beranda', $data);
    $id = $this->session->userdata('user_id');
    $data['user_faip'] = $this->main_mod->msrwhere('user_faip', array('user_id' => $id, 'status' => 1), 'id', 'desc')->result();
    $data['m_faip_status'] = $this->main_mod->msrwhere('m_faip_status', array('is_active' => 1), 'seq_number', 'asc')->result();

    $data["m_bk"] = $this->members_model->get_all_bk();

    $user_profiles = $this->main_mod->msrwhere('user_profiles', array('user_id' => $id), 'id', 'desc')->result();
    $data['va'] = $user_profiles[0]->va;

    if (isset($_POST['preview'])) {
      $upload = $this->faip_upload();

      if ($upload['result'] == "success") {
        //include APPPATH.'third_party/PHPExcel/PHPExcel.php';
        $this->load->library('Libexcel', 'excel');

        $excelreader = new PHPExcel_Reader_Excel2007();
        $excelreader->setReadDataOnly(true);
        $loadexcel = $excelreader->load('assets/excel/' . $this->session->userdata('user_id') . '.xlsx');
        //$sheet = $loadexcel->getActiveSheet()->toArray(null, true, true ,true);


        //TAB I.1
        $sheet['periode_start'] = $loadexcel->getActiveSheet()->getCell('D3')->getValue();
        $sheet['periode_end'] = $loadexcel->getActiveSheet()->getCell('F3')->getValue();
        $sheet['nama'] = $loadexcel->getActiveSheet()->getCell('B5')->getValue();
        $sheet['tempat'] = $loadexcel->getActiveSheet()->getCell('B6')->getValue();
        $sheet['kta'] = $loadexcel->getActiveSheet()->getCell('B7')->getValue();
        $sheet['bk'] = $loadexcel->getActiveSheet()->getCell('B8')->getValue();


        $data['sheet'] = $sheet;
      } else {
        $data['upload_error'] = $upload['error'];
      }
    }

    $this->load->view('member/faip_import', $data);
  }

  public function faip_upload()
  {
    $this->load->library('upload'); // Load librari upload

    $config['upload_path'] = './assets/excel/';
    $config['allowed_types'] = 'xlsx';
    $config['max_size']  = '10048';
    $config['overwrite'] = true;
    $config['file_name'] = $this->session->userdata('user_id') . '.xlsx';

    $this->upload->initialize($config); // Load konfigurasi uploadnya
    if ($this->upload->do_upload('file')) { // Lakukan upload dan Cek jika proses upload berhasil
      // Jika berhasil :
      $return = array('result' => 'success', 'file' => $this->upload->data(), 'error' => '');
      return $return;
    } else {
      // Jika gagal :
      $return = array('result' => 'failed', 'file' => '', 'error' => $this->upload->display_errors());
      return $return;
    }
  }

  public function form_import_test_bc()
  {

    $data = array();
    $id = $this->session->userdata('user_id');
    $data['m_faip_status'] = $this->main_mod->msrwhere('m_faip_status', array('is_active' => 1), 'seq_number', 'asc')->result();
    $data["m_bk"] = $this->members_model->get_all_bk();

    $user_profiles = $this->main_mod->msrwhere('user_profiles', array('user_id' => $id), 'id', 'desc')->result();
    $users = $this->main_mod->msrwhere('users', array('id' => $id), 'id', 'desc')->result();

    //include APPPATH.'third_party/PHPExcel/PHPExcel.php';
    $this->load->library('Libexcel', 'excel');

    $excelreader = new PHPExcel_Reader_Excel2007();
    $excelreader->setReadDataOnly(true);
    $loadexcel = $excelreader->load('assets/excel/' . $id . '.xlsx');
    //$sheet = $loadexcel->getActiveSheet()->toArray(null, true, true ,true);


    $obj_member = $this->members_model->get_member_by_id($id);
    $data['row'] = $obj_member;
    $data['kta'] = $this->members_model->get_kta_data_by_personid($id);

    $bulan = array(
      'Januari' => 1,
      'Februari' => 2,
      'Maret' => 3,
      'April' => 4,
      'Mei' => 5,
      'Juni' => 6,
      'Juli' => 7,
      'Agustus' => 8,
      'September' => 9,
      'Oktober' => 10,
      'November' => 11,
      'Desember' => 12,
      'Nopember' => 11,
      'Pebruari' => 2,

      'JANUARI' => 1,
      'FEBRUARI' => 2,
      'MARET' => 3,
      'APRIL' => 4,
      'MEI' => 5,
      'JUNI' => 6,
      'JULI' => 7,
      'AGUSTUS' => 8,
      'SEPTEMBER' => 9,
      'OKTOBER' => 10,
      'NOVEMBER' => 11,
      'DESEMBER' => 12,
      'NOPEMBER' => 11,
      'PEBRUARI' => 2,

      'Jan' => 1,
      'Feb' => 2,
      'Mar' => 3,
      'Apr' => 4,
      'Mei' => 5,
      'Jun' => 6,
      'Jul' => 7,
      'Ags' => 8,
      'Sep' => 9,
      'Okt' => 10,
      'Nov' => 11,
      'Des' => 12,

      'januari' => 1,
      'febuari' => 2,
      'maret' => 3,
      'april' => 4,
      'mei' => 5,
      'juni' => 6,
      'juli' => 7,
      'agustus' => 8,
      'september' => 9,
      'oktober' => 10,
      'november' => 11,
      'desember' => 12
    );

    $sheet['periode_start'] = $loadexcel->getSheetByName('I.1')->getCell('D3')->getValue();
    $sheet['periode_end'] = $loadexcel->getSheetByName('I.1')->getCell('F3')->getValue();
    $sheet['nama'] = $loadexcel->getSheetByName('I.1')->getCell('B5')->getValue();
    $sheet['tempat'] = $loadexcel->getSheetByName('I.1')->getCell('B6')->getValue();
    $sheet['kta'] = $loadexcel->getSheetByName('I.1')->getCell('B7')->getValue();
    $sheet['bk'] = $loadexcel->getSheetByName('I.1')->getCell('B8')->getValue();
    $sheet['bk'] = $loadexcel->getSheetByName('I.1')->getCell('B8')->getValue();
    //$sheet['mobilephone'] = $loadexcel->getSheetByName('I.1')->getCell('C21')->getValue();
    $sheet['mobilephone'] = $loadexcel->getSheetByName('I.1')->getCell('C20')->getValue();
    //$sheet['email'] = $loadexcel->getSheetByName('I.1')->getCell('E20')->getValue();
    $sheet['email'] = $loadexcel->getSheetByName('I.1')->getCell('E19')->getValue();
    $sheet['faip_type'] = $loadexcel->getSheetByName('rekapitulasi')->getCell('S9')->getValue();
    $sheet['certificate_type'] = $loadexcel->getSheetByName('rekapitulasi')->getCell('S10')->getValue();


    $faip_id = 0;
    try {
      if ($faip_id == 0) {
        $row = array(
          'user_id' => $id,
          'no_kta' => str_pad($data['kta']->no_kta, 6, '0', STR_PAD_LEFT),
          'nama' => $sheet['nama'] != '' ? $sheet['nama'] : ucwords(trim(strtolower($data['row']->firstname)) . " " . trim(strtolower($data['row']->lastname))),
          'periodstart' => 2014, //$sheet['periode_start'],
          'periodend' => 2021, //$sheet['periode_end'],
          'subkejuruan' => '',
          'bidang' => $data['kta']->code_bk_hkk,
          'faip_type' => $sheet['faip_type'] == 'Pemutakhiran' ? '01' : '00',
          'certificate_type' => isset($sheet['certificate_type']) ? $sheet['certificate_type'] : '',
          'pernyataan' => '',
          'wkt_pernyataan' => '',
          'createdby' => $id,
          'status_faip' => 0
        );
        $insert = $this->main_mod->insert('user_faip', $row);
        $faip_id = $insert;
      }
    } catch (Exception $e) {
      print_r($e);
      return false;
    }


    $data['user_faip'] = $this->main_mod->msrwhere('user_faip', array('id' => $faip_id, 'status' => 1), 'id', 'desc')->result();



    //TAB 1V
    $sheetObj = $loadexcel->getSheetByName('IV');
    $startFrom = 4;
    $limit = null;
    $i = 1;
    $is_parent = true;
    $parent_4 = array();
    $parent_temp = 0;
    foreach ($sheetObj->getRowIterator($startFrom, $limit) as $row) {
      $j = 0;
      foreach ($row->getCellIterator() as $cell) {
        $value = $cell->getCalculatedValue();
        if ($j == 0 && $value != '') {
          $is_parent = true;
          $parent_temp++;
        } else if ($j != 0 && $value != '') $is_parent = true;
        else $is_parent = false;

        if ($is_parent) {
          if ($j == 1) $parent_4[$parent_temp]['4_periode'] = $cell->getCalculatedValue();
          else if ($j == 2) $parent_4[$parent_temp]['4_instansi'] = $cell->getCalculatedValue();
          else if ($j == 3) $parent_4[$parent_temp]['4_namaproyek'] = $cell->getCalculatedValue();
          else if ($j == 4) $parent_4[$parent_temp]['4_location'] = $cell->getCalculatedValue();
          else if ($j == 5) $parent_4[$parent_temp]['4_durasi'] = $cell->getCalculatedValue();
          else if ($j == 6) $parent_4[$parent_temp]['4_posisi'] = $cell->getCalculatedValue();
          else if ($j == 7) $parent_4[$parent_temp]['4_jumlahsks'] = $cell->getCalculatedValue();
          else if ($j == 8) $parent_4[$parent_temp]['4_aktifitas'] = $cell->getCalculatedValue();

          /*if($j==1) $parent_4[$parent_temp]['4_periode'] = $cell->getCalculatedValue();
					else if($j==2) $parent_4[$parent_temp]['4_instansi'] = $cell->getCalculatedValue();
					else if($j==3) $parent_4[$parent_temp]['4_location'] = $cell->getCalculatedValue();
					else if($j==4) $parent_4[$parent_temp]['4_durasi'] = $cell->getCalculatedValue();
					else if($j==5) $parent_4[$parent_temp]['4_posisi'] = $cell->getCalculatedValue();
					else if($j==6) $parent_4[$parent_temp]['4_jumlahsks'] = $cell->getCalculatedValue();
					else if($j==7) $parent_4[$parent_temp]['4_namaproyek'] = $cell->getCalculatedValue();*/
        }

        if ($value != '' && (strpos($value, 'W.') !== false || strpos($value, 'P.') !== false)) {
          $parent_4[$parent_temp]['4_kompetensi'][] = $cell->getCalculatedValue();
        }
        $j++;
      }
      $i++;
    }
    $this->main_mod->delete('user_faip_4', "faip_id", $faip_id);
    if (is_array($parent_4)) {
      foreach ($parent_4 as $parent) {

        //$periode = explode(" s.d. ",$parent['4_periode']);
        $periode = explode("-", $parent['4_periode']);
        $startdate = '';
        $startyear = '';
        $enddate = '';
        $endyear = '';
        if (isset($periode[1])) {
          $temp = explode(" ", $periode[1]);

          if (isset($temp[2])) {
            //$enddate = $bulan[trim($temp[1])];
            //$endyear = $temp[2];
          }

          $temp = explode(" ", $periode[0]);
          //if(!isset($temp[1])) $startdate = $enddate;
          //else $startdate = $bulan[trim($temp[1])];

          $startyear = $endyear;
        } else {
          $temp = explode(" ", $parent['4_periode']);
          if (isset($temp[2])) {
            $startdate = $bulan[trim($temp[1])];
            $startyear = $temp[2];
          } else {
            $startdate = 1;
            $startyear = $temp[0];
          }
          $enddate = $startdate;
          $endyear = $startyear;
        }

        $is_present = 0;
        if ($endyear == 'Sekarang') {
          $endyear = '';
          $is_present = 1;
        }

        $p_master = $this->main_mod->msrwhere('m_bakuan_penilaian', array('faip_num' => '4', 'faip_type' => 'p', 'desc' => $parent['4_durasi']), 'id', 'desc')->row();
        $q_master = $this->main_mod->msrwhere('m_bakuan_penilaian', array('faip_num' => '4', 'faip_type' => 'q', 'desc' => $parent['4_posisi']), 'id', 'desc')->row();
        $r_master = $this->main_mod->msrwhere('m_bakuan_penilaian', array('faip_num' => '4', 'faip_type' => 'r', 'desc' => $parent['4_jumlahsks']), 'id', 'desc')->row();

        $p = isset($p_master->value) ? $p_master->value : 0;
        $q = isset($q_master->value) ? $q_master->value : 0;
        $r = isset($r_master->value) ? $r_master->value : 0;
        $t = $p * $q * $r;

        $row = array(
          'faip_id' => $faip_id,
          'parent' => 0,

          'instansi' => $parent['4_instansi'],
          'periode' => $p,
          'posisi' => $q,
          'jumlahsks' => $r,
          'location' => $parent['4_location'],
          'provinsi' => '-',
          'negara' => '-',
          'namaproyek' => $parent['4_namaproyek'],
          'uraian' => isset($parent['4_aktifitas']) ? $parent['4_aktifitas'] : '',
          'startdate' => $startdate,
          'startyear' => $startyear,
          'enddate' => $enddate,
          'endyear' => $endyear,
          'kompetensi' => $parent['4_kompetensi'][0],

          'p' => $p,
          'q' => $q,
          'r' => $r,
          't' => $t,
          'attachment' => '',
          'createdby' => $id,
          'modifiedby' => $id,
          'modifieddate' => date('Y-m-d H:i:s'),
          'status' => 1,
        );

        $insert_id = $this->main_mod->insert('user_faip_4', $row);

        $i = 0;
        foreach ($parent['4_kompetensi'] as $kompetensi) {
          if ($i > 0) {
            $row = array(
              'faip_id' => $faip_id,
              'parent' => $insert_id,
              'kompetensi' => $kompetensi,

              'p' => $p,
              'q' => $q,
              'r' => $r,
              't' => $t,
              'attachment' => '',
              'createdby' => $id,
              'modifiedby' => $id,
              'modifieddate' => date('Y-m-d H:i:s'),
              'status' => 1,
            );

            $insert = $this->main_mod->insert('user_faip_4', $row);
          }
          $i++;
        }
      }
    }



    //$this->load->view('member/faip_import', $data);
  }

  public function form_import_test()
  {

    $data = array();
    $id = $this->session->userdata('user_id');
    $data['m_faip_status'] = $this->main_mod->msrwhere('m_faip_status', array('is_active' => 1), 'seq_number', 'asc')->result();
    $data["m_bk"] = $this->members_model->get_all_bk();

    $user_profiles = $this->main_mod->msrwhere('user_profiles', array('user_id' => $id), 'id', 'desc')->result();
    $users = $this->main_mod->msrwhere('users', array('id' => $id), 'id', 'desc')->result();

    //include APPPATH.'third_party/PHPExcel/PHPExcel.php';
    $this->load->library('Libexcel', 'excel');

    $excelreader = new PHPExcel_Reader_Excel2007();
    $excelreader->setReadDataOnly(true);
    $loadexcel = $excelreader->load('assets/excel/' . $id . '.xlsx');
    //$sheet = $loadexcel->getActiveSheet()->toArray(null, true, true ,true);


    $obj_member = $this->members_model->get_member_by_id($id);
    $data['row'] = $obj_member;
    $data['kta'] = $this->members_model->get_kta_data_by_personid($id);

    $bulan = array(
      'Januari' => 1,
      'Februari' => 2,
      'Maret' => 3,
      'April' => 4,
      'Mei' => 5,
      'Juni' => 6,
      'Juli' => 7,
      'Agustus' => 8,
      'September' => 9,
      'Oktober' => 10,
      'November' => 11,
      'Desember' => 12,
      'Nopember' => 11,
      'Pebruari' => 2,

      'JANUARI' => 1,
      'FEBRUARI' => 2,
      'MARET' => 3,
      'APRIL' => 4,
      'MEI' => 5,
      'JUNI' => 6,
      'JULI' => 7,
      'AGUSTUS' => 8,
      'SEPTEMBER' => 9,
      'OKTOBER' => 10,
      'NOVEMBER' => 11,
      'DESEMBER' => 12,
      'NOPEMBER' => 11,
      'PEBRUARI' => 2,

      'Jan' => 1,
      'Feb' => 2,
      'Mar' => 3,
      'Apr' => 4,
      'Mei' => 5,
      'Jun' => 6,
      'Jul' => 7,
      'Ags' => 8,
      'Sep' => 9,
      'Okt' => 10,
      'Nov' => 11,
      'Des' => 12,

      'januari' => 1,
      'febuari' => 2,
      'maret' => 3,
      'april' => 4,
      'mei' => 5,
      'juni' => 6,
      'juli' => 7,
      'agustus' => 8,
      'september' => 9,
      'oktober' => 10,
      'november' => 11,
      'desember' => 12,

      'Jan' => 1,
      'Feb' => 2,
      'Mar' => 3,
      'Apr' => 4,
      'May' => 5,
      'Jun' => 6,
      'Jul' => 7,
      'Aug' => 8,
      'Sep' => 9,
      'Oct' => 10,
      'Nov' => 11,
      'Dec' => 12,

      'January' => 1,
      'October' => 10,
    );

    $sheet['periode_start'] = $loadexcel->getSheetByName('I.1')->getCell('D3')->getValue();
    $sheet['periode_end'] = $loadexcel->getSheetByName('I.1')->getCell('F3')->getValue();
    $sheet['nama'] = $loadexcel->getSheetByName('I.1')->getCell('B5')->getValue();
    $sheet['tempat'] = $loadexcel->getSheetByName('I.1')->getCell('B6')->getValue();
    $sheet['kta'] = $loadexcel->getSheetByName('I.1')->getCell('B7')->getValue();
    $sheet['bk'] = $loadexcel->getSheetByName('I.1')->getCell('B8')->getValue();
    $sheet['bk'] = $loadexcel->getSheetByName('I.1')->getCell('B8')->getValue();
    $sheet['mobilephone'] = $loadexcel->getSheetByName('I.1')->getCell('C21')->getValue();
    //$sheet['mobilephone'] = $loadexcel->getSheetByName('I.1')->getCell('C20')->getValue();
    //$sheet['email'] = $loadexcel->getSheetByName('I.1')->getCell('E20')->getValue();
    //$sheet['email'] = $loadexcel->getSheetByName('I.1')->getCell('E19')->getValue();
    $sheet['email'] = $loadexcel->getSheetByName('I.1')->getCell('G21')->getValue();
    $sheet['faip_type'] = $loadexcel->getSheetByName('rekapitulasi')->getCell('S9')->getValue();
    $sheet['certificate_type'] = $loadexcel->getSheetByName('rekapitulasi')->getCell('S10')->getValue();


    $faip_id = 13463;
    try {
      if ($faip_id == 0) {
        $row = array(
          'user_id' => $id,
          'no_kta' => str_pad($data['kta']->no_kta, 6, '0', STR_PAD_LEFT),
          'nama' => $sheet['nama'] != '' ? $sheet['nama'] : ucwords(trim(strtolower($data['row']->firstname)) . " " . trim(strtolower($data['row']->lastname))),
          'periodstart' => 2008, //$sheet['periode_start'],
          'periodend' => 2022, //$sheet['periode_end'],
          'subkejuruan' => '',
          'bidang' => $data['kta']->code_bk_hkk,
          'faip_type' => $sheet['faip_type'] == 'Pemutakhiran' ? '01' : '00',
          'certificate_type' => isset($sheet['certificate_type']) ? $sheet['certificate_type'] : '',
          'pernyataan' => '',
          'wkt_pernyataan' => '',
          'createdby' => $id,
          'status_faip' => 0
        );
        $insert = $this->main_mod->insert('user_faip', $row);
        $faip_id = $insert;
      }
    } catch (Exception $e) {
      print_r($e);
      return false;
    }


    $data['user_faip'] = $this->main_mod->msrwhere('user_faip', array('id' => $faip_id, 'status' => 1), 'id', 'desc')->result();

    //TAB I.1
    $this->main_mod->delete('user_faip_11', "faip_id", $faip_id);
    $t = strtotime($data['row']->dob);
    try {
      $row = array(
        'faip_id' => $faip_id,
        'nama' => $sheet['nama'] != '' ? $sheet['nama'] : ucwords(trim(strtolower($data['row']->firstname)) . " " . trim(strtolower($data['row']->lastname))),
        'birthplace' => ucwords(strtolower($data['row']->birthplace)),
        'dob' => (($data['row']->dob != "0000-00-00") ? $data['row']->dob : ""), //date('d F Y',$t)
        'no_kta' => str_pad($data['kta']->no_kta, 6, '0', STR_PAD_LEFT),
        'subkejuruan' => $data['kta']->code_bk_hkk,
        'bidang' => $data['kta']->code_bk_hkk,
        'photo' => (($data['row']->photo != '') ? base_url() . 'assets/uploads/' . $data['row']->photo : ""),
        'mobilephone' => $sheet['mobilephone'] != '' ? $sheet['mobilephone'] : $data['row']->mobilephone,
        'email' => $users[0]->email,
        'createdby' => $id,
      );
      $insert = $this->main_mod->insert('user_faip_11', $row);
    } catch (Exception $e) {
      print_r($e);
      return false;
    }


    //INSERT 111
    $this->main_mod->delete('user_faip_111', "faip_id", $faip_id);

    //$addr_loc = $loadexcel->getSheetByName('I.1')->getCell('C17')->getValue();
    //$addr_zip = $loadexcel->getSheetByName('I.1')->getCell('E17')->getValue();
    $addr_loc = $loadexcel->getSheetByName('I.1')->getCell('C19')->getValue();
    $addr_zip = $loadexcel->getSheetByName('I.1')->getCell('E19')->getValue();
    //$addr_loc = $loadexcel->getSheetByName('I.1')->getCell('C19')->getValue();
    //$addr_zip = $loadexcel->getSheetByName('I.1')->getCell('E19')->getValue();
    //$addr_loc = $loadexcel->getSheetByName('I.1')->getCell('C18')->getValue();
    //$addr_zip = $loadexcel->getSheetByName('I.1')->getCell('E18')->getValue();

    try {
      $row = array(
        'faip_id' => $faip_id,
        'addr_type' => 1,
        'addr_desc' => $loadexcel->getSheetByName('I.1')->getCell('B12')->getValue(),
        'addr_loc' => isset($addr_loc) ? $addr_loc : '',
        'addr_zip' => isset($addr_zip) ? $addr_zip : '',
        //'addr_loc' => $loadexcel->getSheetByName('I.1')->getCell('C18')->getValue(),								
        //'addr_zip' => $loadexcel->getSheetByName('I.1')->getCell('E18')->getValue(),
        'createdby' => $id,
      );
      $insert = $this->main_mod->insert('user_faip_111', $row);
    } catch (Exception $e) {
      print_r($e);
    }

    //INSERT 112
    $this->main_mod->delete('user_faip_112', "faip_id", $faip_id);

    //$exp_loc = $loadexcel->getSheetByName('I.1')->getCell('G17')->getValue();
    //$exp_zip = $loadexcel->getSheetByName('I.1')->getCell('I17')->getValue();
    $exp_loc = $loadexcel->getSheetByName('I.1')->getCell('G19')->getValue();
    $exp_zip = $loadexcel->getSheetByName('I.1')->getCell('I19')->getValue();
    //$exp_loc = $loadexcel->getSheetByName('I.1')->getCell('G19')->getValue();
    //$exp_zip = $loadexcel->getSheetByName('I.1')->getCell('I19')->getValue();
    //$exp_loc = $loadexcel->getSheetByName('I.1')->getCell('G18')->getValue();
    //$exp_zip = $loadexcel->getSheetByName('I.1')->getCell('I18')->getValue();

    try {
      $row = array(
        'faip_id' => $faip_id,
        'exp_name' => $loadexcel->getSheetByName('I.1')->getCell('F13')->getValue(),
        'exp_desc' => $loadexcel->getSheetByName('I.1')->getCell('F16')->getValue(),
        //'exp_desc' => $loadexcel->getSheetByName('I.1')->getCell('F15')->getValue(),
        'exp_loc' => isset($exp_loc) ? $exp_loc : '',
        'exp_zip' => isset($exp_zip) ? $exp_zip : '',
        //'exp_loc' => $loadexcel->getSheetByName('I.1')->getCell('G18')->getValue(),				
        //'exp_zip' => $loadexcel->getSheetByName('I.1')->getCell('I18')->getValue(),
        'createdby' => $id,
      );
      $insert = $this->main_mod->insert('user_faip_112', $row);
    } catch (Exception $e) {
      print_r($e);
    }

    //INSERT 113
    $this->main_mod->delete('user_faip_113', "faip_id", $faip_id);

    //$phone_value = $loadexcel->getSheetByName('I.1')->getCell('G18')->getValue();
    //$phone_value = $loadexcel->getSheetByName('I.1')->getCell('G20')->getValue();
    //$phone_value = $loadexcel->getSheetByName('I.1')->getCell('C21')->getValue();
    //$phone_value = $loadexcel->getSheetByName('I.1')->getCell('C20')->getValue();
    $phone_value = $loadexcel->getSheetByName('I.1')->getCell('C22')->getValue();
    //$phone_value = $loadexcel->getSheetByName('I.1')->getCell('C22')->getValue();

    try {
      $row = array(
        'faip_id' => $faip_id,
        'phone_type' => 'office_phone',
        //'phone_value' => $loadexcel->getSheetByName('I.1')->getCell('G19')->getValue(),
        'phone_value' => isset($phone_value) ? $phone_value : '',
        'createdby' => $id,
      );
      $insert = $this->main_mod->insert('user_faip_113', $row);
    } catch (Exception $e) {
      print_r($e);
    }


    //INSERT 12
    $this->main_mod->delete('user_faip_12', "faip_id", $faip_id);
    try {
      $tahun_lulus = $loadexcel->getSheetByName('I.2')->getCell('C8')->getValue();
      $score = $loadexcel->getSheetByName('I.2')->getCell('C12')->getValue();
      $birthdate_ts = strtotime("$tahun_lulus-1-1");
      $birthdate_ts2 = strtotime(date("Y-m-d"));

      $diff = abs($birthdate_ts2 - $birthdate_ts);
      $tempidx = 0;
      $years = floor($diff / (365 * 60 * 60 * 24));

      $data['bp_12'] = $this->main_mod->msrwhere('m_bakuan_penilaian', array('faip_num' => '12', 'faip_type' => 'p', 'condition' => 'S1'), 'id', 'desc')->result();
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
      if ($score <= 3) $tempqr = 2;

      if ($score > 3) $tempqr = 3;
      $p = $tempidx;
      $q = $tempqr;
      $r = $tempqr;
      $t = $p * $q * $r;

      $fakultas = $loadexcel->getSheetByName('I.2')->getCell('C4')->getValue();
      $judul = $loadexcel->getSheetByName('I.2')->getCell('C10')->getValue();
      $uraian = $loadexcel->getSheetByName('I.2')->getCell('C11')->getValue();

      $row = array(
        'faip_id' => $faip_id,
        'school' => $loadexcel->getSheetByName('I.2')->getCell('C3')->getValue(),
        'school_type' => 'S1',
        'fakultas' => isset($fakultas) ? $fakultas : '',
        'jurusan' => $loadexcel->getSheetByName('I.2')->getCell('C5')->getValue(),
        'kota' => $loadexcel->getSheetByName('I.2')->getCell('C6')->getValue(),
        'provinsi' => '-',
        'negara' => $loadexcel->getSheetByName('I.2')->getCell('C7')->getValue(),
        'tahun_lulus' => $loadexcel->getSheetByName('I.2')->getCell('C8')->getValue(),
        'title' => $loadexcel->getSheetByName('I.2')->getCell('C9')->getValue(),
        'judul' => isset($judul) ? $judul : '-',
        'uraian' => isset($uraian) ? $uraian : '',
        'score' => $loadexcel->getSheetByName('I.2')->getCell('C12')->getValue(),
        'judicium' => ($loadexcel->getSheetByName('I.2')->getCell('C13')->getValue() != '' ? $loadexcel->getSheetByName('I.2')->getCell('C13')->getValue() : '-'),


        'kompetensi' => 'W.2',

        'p' => $p,
        'q' => $q,
        'r' => $r,
        't' => $t,
        'attachment' => '',
        'createdby' => $id,
      );
      $insert = $this->main_mod->insert('user_faip_12', $row);
    } catch (Exception $e) {
      print_r($e);
    }



    try {
      $tahun_lulus = $loadexcel->getSheetByName('I.2')->getCell('D8')->getValue();
      $score = $loadexcel->getSheetByName('I.2')->getCell('D12')->getValue();
      $birthdate_ts = strtotime("$tahun_lulus-1-1");
      $birthdate_ts2 = strtotime(date("Y-m-d"));

      $school = $loadexcel->getSheetByName('I.2')->getCell('D3')->getValue();
      if ($school != '') {
        $diff = abs($birthdate_ts2 - $birthdate_ts);
        $tempidx = 0;
        $years = floor($diff / (365 * 60 * 60 * 24));

        $data['bp_12'] = $this->main_mod->msrwhere('m_bakuan_penilaian', array('faip_num' => '12', 'faip_type' => 'p', 'condition' => 'S2'), 'id', 'desc')->result();
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
        if ($score <= 3) $tempqr = 2;

        if ($score > 3) $tempqr = 3;
        $p = $tempidx;
        $q = $tempqr;
        $r = $tempqr;
        $t = $p * $q * $r;

        $fakultas = $loadexcel->getSheetByName('I.2')->getCell('D4')->getValue();

        $row = array(
          'faip_id' => $faip_id,
          'school' => $loadexcel->getSheetByName('I.2')->getCell('D3')->getValue(),
          'school_type' => 'S2',
          'fakultas' => isset($fakultas) ? $fakultas : '',
          'jurusan' => $loadexcel->getSheetByName('I.2')->getCell('D5')->getValue(),
          'kota' => $loadexcel->getSheetByName('I.2')->getCell('D6')->getValue(),
          'provinsi' => '-',
          'negara' => $loadexcel->getSheetByName('I.2')->getCell('D7')->getValue(),
          'tahun_lulus' => $loadexcel->getSheetByName('I.2')->getCell('D8')->getValue(),
          'title' => $loadexcel->getSheetByName('I.2')->getCell('D9')->getValue(),
          'judul' => $loadexcel->getSheetByName('I.2')->getCell('D10')->getValue(),
          'uraian' => $loadexcel->getSheetByName('I.2')->getCell('D11')->getValue(),
          'score' => $loadexcel->getSheetByName('I.2')->getCell('D12')->getValue(),
          'judicium' => $loadexcel->getSheetByName('I.2')->getCell('D13')->getValue() != '' ? $loadexcel->getSheetByName('I.2')->getCell('D13')->getValue() : '-',


          'kompetensi' => 'W.2',

          'p' => $p,
          'q' => $q,
          'r' => $r,
          't' => $t,
          'attachment' => '',
          'createdby' => $id,
        );
        $insert = $this->main_mod->insert('user_faip_12', $row);
      }
    } catch (Exception $e) {
      print_r($e);
    }

    try {
      $tahun_lulus = $loadexcel->getSheetByName('I.2')->getCell('E8')->getValue();
      $score = $loadexcel->getSheetByName('I.2')->getCell('E12')->getValue();
      $birthdate_ts = strtotime("$tahun_lulus-1-1");
      $birthdate_ts2 = strtotime(date("Y-m-d"));

      $school = $loadexcel->getSheetByName('I.2')->getCell('E3')->getValue();
      if ($school != '') {

        $diff = abs($birthdate_ts2 - $birthdate_ts);
        $tempidx = 0;
        $years = floor($diff / (365 * 60 * 60 * 24));

        $data['bp_12'] = $this->main_mod->msrwhere('m_bakuan_penilaian', array('faip_num' => '12', 'faip_type' => 'p', 'condition' => 'S3'), 'id', 'desc')->result();
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
        if ($score <= 3) $tempqr = 2;

        if ($score > 3) $tempqr = 3;
        $p = $tempidx;
        $q = $tempqr;
        $r = $tempqr;
        $t = $p * $q * $r;

        $fakultas = $loadexcel->getSheetByName('I.2')->getCell('E4')->getValue();

        $row = array(
          'faip_id' => $faip_id,
          'school' => $loadexcel->getSheetByName('I.2')->getCell('E3')->getValue(),
          //'school_type' => 'S3',
          'school_type' => 'Ir.',
          'fakultas' => isset($fakultas) ? $fakultas : '',
          'jurusan' => $loadexcel->getSheetByName('I.2')->getCell('E5')->getValue(),
          'kota' => $loadexcel->getSheetByName('I.2')->getCell('E6')->getValue(),
          'provinsi' => '-',
          'negara' => $loadexcel->getSheetByName('I.2')->getCell('E7')->getValue(),
          'tahun_lulus' => $loadexcel->getSheetByName('I.2')->getCell('E8')->getValue(),
          'title' => $loadexcel->getSheetByName('I.2')->getCell('E9')->getValue(),
          'judul' => $loadexcel->getSheetByName('I.2')->getCell('E10')->getValue(),
          'uraian' => $loadexcel->getSheetByName('I.2')->getCell('E11')->getValue(),
          'score' => $loadexcel->getSheetByName('I.2')->getCell('E12')->getValue(),
          'judicium' => $loadexcel->getSheetByName('I.2')->getCell('E13')->getValue() != '' ? $loadexcel->getSheetByName('I.2')->getCell('E13')->getValue() : '',


          'kompetensi' => 'W.2',

          'p' => $p,
          'q' => $q,
          'r' => $r,
          't' => $t,
          'attachment' => '',
          'createdby' => $id,
        );
        $insert = $this->main_mod->insert('user_faip_12', $row);
      }
    } catch (Exception $e) {
      print_r($e);
    }


    try {
      $tahun_lulus = $loadexcel->getSheetByName('I.2')->getCell('F8')->getValue();
      $score = $loadexcel->getSheetByName('I.2')->getCell('F12')->getValue();
      $birthdate_ts = strtotime("$tahun_lulus-1-1");
      $birthdate_ts2 = strtotime(date("Y-m-d"));

      $school = $loadexcel->getSheetByName('I.2')->getCell('F3')->getValue();
      if ($school != '') {

        $diff = abs($birthdate_ts2 - $birthdate_ts);
        $tempidx = 0;
        $years = floor($diff / (365 * 60 * 60 * 24));

        $data['bp_12'] = $this->main_mod->msrwhere('m_bakuan_penilaian', array('faip_num' => '12', 'faip_type' => 'p', 'condition' => 'S3'), 'id', 'desc')->result();
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
        if ($score <= 3) $tempqr = 2;

        if ($score > 3) $tempqr = 3;
        $p = $tempidx;
        $q = $tempqr;
        $r = $tempqr;
        $t = $p * $q * $r;

        $fakultas = $loadexcel->getSheetByName('I.2')->getCell('F4')->getValue();

        $row = array(
          'faip_id' => $faip_id,
          'school' => $loadexcel->getSheetByName('I.2')->getCell('F3')->getValue(),
          'school_type' => 'Ir.',
          'fakultas' => isset($fakultas) ? $fakultas : '',
          'jurusan' => $loadexcel->getSheetByName('I.2')->getCell('F5')->getValue(),
          'kota' => $loadexcel->getSheetByName('I.2')->getCell('F6')->getValue(),
          'provinsi' => '-',
          'negara' => $loadexcel->getSheetByName('I.2')->getCell('F7')->getValue(),
          'tahun_lulus' => $loadexcel->getSheetByName('I.2')->getCell('F8')->getValue(),
          'title' => $loadexcel->getSheetByName('I.2')->getCell('F9')->getValue(),
          'judul' => $loadexcel->getSheetByName('I.2')->getCell('F10')->getValue(),
          'uraian' => $loadexcel->getSheetByName('I.2')->getCell('F11')->getValue(),
          'score' => $loadexcel->getSheetByName('I.2')->getCell('F12')->getValue(),
          'judicium' => $loadexcel->getSheetByName('I.2')->getCell('F13')->getValue(),


          'kompetensi' => 'W.2',

          'p' => $p,
          'q' => $q,
          'r' => $r,
          't' => $t,
          'attachment' => '',
          'createdby' => $id,
        );
        $insert = $this->main_mod->insert('user_faip_12', $row);
      }
    } catch (Exception $e) {
      print_r($e);
    }


    //TAB 1.3
    $sheetObj = $loadexcel->getSheetByName('I.3');
    $startFrom = 4;
    $limit = null;
    $i = 1;
    $is_parent = true;
    $parent_13 = array();
    $parent_temp = 0;
    foreach ($sheetObj->getRowIterator($startFrom, $limit) as $row) {
      $j = 0;
      foreach ($row->getCellIterator() as $cell) {
        $value = $cell->getCalculatedValue();
        if ($j == 0 && $value != '') {
          $is_parent = true;
          $parent_temp++;
        } else if ($j != 0 && $value != '') $is_parent = true;
        else $is_parent = false;

        if ($is_parent) {
          if ($j == 1) $parent_13[$parent_temp]['13_nama_org'] = $cell->getCalculatedValue();
          else if ($j == 2) $parent_13[$parent_temp]['13_jenis'] = $cell->getCalculatedValue();
          else if ($j == 3) $parent_13[$parent_temp]['13_tempat'] = $cell->getCalculatedValue();
          else if ($j == 4) $parent_13[$parent_temp]['13_negara'] = $cell->getCalculatedValue();
          else if ($j == 5) $parent_13[$parent_temp]['13_periode'] = $cell->getCalculatedValue();
          else if ($j == 6) $parent_13[$parent_temp]['13_startyear'] = $cell->getCalculatedValue();
          else if ($j == 7) $parent_13[$parent_temp]['13_jabatan'] = $cell->getCalculatedValue();
          else if ($j == 8) $parent_13[$parent_temp]['13_tingkat'] = $cell->getCalculatedValue();
          else if ($j == 9) $parent_13[$parent_temp]['13_lingkup'] = $cell->getCalculatedValue();
          else if ($j == 10) $parent_13[$parent_temp]['13_aktifitas'] = $cell->getCalculatedValue();
        }

        if ($value != '' && (strpos($value, 'W.') !== false || strpos($value, 'P.') !== false)) {
          $parent_13[$parent_temp]['13_kompetensi'][] = $cell->getCalculatedValue();
        }
        $j++;
      }
      $i++;
    }
    $this->main_mod->delete('user_faip_13', "faip_id", $faip_id);
    if (is_array($parent_13)) {
      foreach ($parent_13 as $parent) {

        $periode = explode("-", $parent['13_periode']);
        //$periode = explode(" - ",$parent['13_periode']);
        if (!isset($periode[1])) $periode = explode("s/d", $parent['13_periode']);
        $startyear = trim($periode[0]);
        $endyear = (isset($periode[1])) ? trim($periode[1]) : '';
        $is_present = 0;
        if ($endyear == 'Sekarang' || $endyear == 'sekarang') {
          $endyear = '';
          $is_present = 1;
        }
        $p = 0;
        if ($parent['13_startyear'] == '1 – 5 Tahun') $p = 1;
        else if ($parent['13_startyear'] == '6 – 10 Tahun') $p = 2;
        else if ($parent['13_startyear'] == '11 – 15 Tahun') $p = 3;
        else if ($parent['13_startyear'] == 'd) > dari 15 Tahun') $p = 4;
        else if ($parent['13_startyear'] == '> dari 15 Tahun') $p = 4;

        if ($parent['13_jenis'] == 'Organisasi PII') $p = 4;

        $q_master = $this->main_mod->msrwhere('m_bakuan_penilaian', array('faip_num' => '13', 'faip_type' => 'q', 'desc' => $parent['13_jabatan']), 'id', 'desc')->row();
        $r_master = $this->main_mod->msrwhere('m_bakuan_penilaian', array('faip_num' => '13', 'faip_type' => 'r', 'desc' => $parent['13_tingkat']), 'id', 'desc')->row();

        $q = isset($q_master->value) ? $q_master->value : 0;
        $r = isset($r_master->value) ? $r_master->value : 0;
        $t = $p * $q * $r;

        $row = array(
          'faip_id' => $faip_id,
          'parent' => 0,
          'nama_org' => $parent['13_nama_org'],
          'jenis' => $parent['13_jenis'],
          'lingkup' => $parent['13_lingkup'],
          'jabatan' => $q,
          'tempat' => isset($parent['13_tempat']) ? $parent['13_tempat'] : '',
          'provinsi' => '-',
          'negara' => isset($parent['13_negara']) ? $parent['13_negara'] : '',
          'aktifitas' => isset($parent['13_aktifitas']) ? $parent['13_aktifitas'] : '',
          'startdate' => '1',
          'startyear' => $startyear,
          'enddate' => '1',
          'endyear' => $endyear != '' ? $endyear : $startyear,
          'is_present' => $is_present,
          'tingkat' => $r,
          'kompetensi' => isset($parent['13_kompetensi'][0]) ? $parent['13_kompetensi'][0] : 'null',

          'p' => $p,
          'q' => $q,
          'r' => $r,
          't' => $t,
          'attachment' => '',
          'createdby' => $id,
          'modifiedby' => $id,
          'modifieddate' => date('Y-m-d H:i:s'),
          'status' => 1,
        );

        $insert_id = $this->main_mod->insert('user_faip_13', $row);

        $i = 0;
        if (isset($parent['13_kompetensi'][0])) {
          foreach ($parent['13_kompetensi'] as $kompetensi) {
            if ($i > 0) {

              if ($kompetensi == 'W.1.4.1.') $kompetensi = 'W.1.4.4.';
              else if ($kompetensi == 'W.1.4.2.') $kompetensi = 'W.1.4.5.';
              else if ($kompetensi == 'W.1.4.3.') $kompetensi = 'W.1.4.6.';
              else if ($kompetensi == 'W.1.4.4.') $kompetensi = 'W.1.4.7.';
              else if ($kompetensi == 'W.1.4.5.') $kompetensi = 'W.1.4.8.';

              $row = array(
                'faip_id' => $faip_id,
                'parent' => $insert_id,
                'kompetensi' => $kompetensi,

                'p' => $p,
                'q' => $q,
                'r' => $r,
                't' => $t,
                'attachment' => '',
                'createdby' => $id,
                'modifiedby' => $id,
                'modifieddate' => date('Y-m-d H:i:s'),
                'status' => 1,
              );

              $insert = $this->main_mod->insert('user_faip_13', $row);
            }
            $i++;
          }
        }
      }
    }




    //TAB 1.4
    $sheetObj = $loadexcel->getSheetByName('I.4');
    $startFrom = 4;
    $limit = null;
    $i = 1;
    $is_parent = true;
    $parent_14 = array();
    $parent_temp = 0;
    foreach ($sheetObj->getRowIterator($startFrom, $limit) as $row) {
      $j = 0;
      foreach ($row->getCellIterator() as $cell) {
        $value = $cell->getCalculatedValue();
        if ($j == 0 && $value != '') {
          $is_parent = true;
          $parent_temp++;
        } else if ($j != 0 && $value != '') $is_parent = true;
        else $is_parent = false;

        if ($is_parent) {
          if ($j == 1) $parent_14[$parent_temp]['14_startyear'] = $cell->getCalculatedValue();
          else if ($j == 2) $parent_14[$parent_temp]['14_nama'] = $cell->getCalculatedValue();
          else if ($j == 3) $parent_14[$parent_temp]['14_lembaga'] = $cell->getCalculatedValue();
          else if ($j == 4) $parent_14[$parent_temp]['14_location'] = $cell->getCalculatedValue();
          else if ($j == 5) $parent_14[$parent_temp]['14_negara'] = $cell->getCalculatedValue();
          else if ($j == 6) $parent_14[$parent_temp]['14_tingkat'] = $cell->getCalculatedValue();
          else if ($j == 7) $parent_14[$parent_temp]['14_tingkatlembaga'] = $cell->getCalculatedValue();
          else if ($j == 8) $parent_14[$parent_temp]['14_aktifitas'] = $cell->getCalculatedValue();
        }

        if ($value != '' && (strpos($value, 'W.') !== false || strpos($value, 'P.') !== false)) {
          $parent_14[$parent_temp]['14_kompetensi'][] = $cell->getCalculatedValue();
        }
        $j++;
      }
      $i++;
    }
    $this->main_mod->delete('user_faip_14', "faip_id", $faip_id);
    if (is_array($parent_14)) {
      foreach ($parent_14 as $parent) {

        $periode = explode(" ", $parent['14_startyear']);

        $startyear = '';
        $endyear = '';
        $is_present = 0;

        if (isset($periode[2])) {
          $startyear = $bulan[trim($periode[1])];
          $endyear = trim($periode[2]);
          if ($endyear == 'Sekarang') {
            $endyear = '';
            $is_present = 1;
          }
        } else if (isset($periode[1])) {
          $startyear = $bulan[trim($periode[0])];
          $endyear = trim($periode[1]);
          if ($endyear == 'Sekarang') {
            $endyear = '';
            $is_present = 1;
          }
        } else {
          $startyear = 1;
          $endyear = $parent['14_startyear'];
        }

        $p = 0;
        $tempidx = 0;
        $years = count($parent_14);
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
        }
        $p = $tempidx;
        $q_master = isset($parent['14_tingkat']) ? $this->main_mod->msrwhere('m_bakuan_penilaian', array('faip_num' => '14', 'faip_type' => 'q', 'desc' => $parent['14_tingkat']), 'id', 'desc')->row() : 0;
        $r_master = $this->main_mod->msrwhere('m_bakuan_penilaian', array('faip_num' => '14', 'faip_type' => 'r', 'desc' => $parent['14_tingkatlembaga']), 'id', 'desc')->row();

        $q = isset($q_master->value) ? $q_master->value : 0;
        $r = isset($r_master->value) ? $r_master->value : 0;
        $t = $p * $q * $r;

        $row = array(
          'faip_id' => $faip_id,
          'parent' => 0,
          'nama' => $parent['14_nama'],
          'lembaga' => isset($parent['14_lembaga']) ? $parent['14_lembaga'] : '',
          'tingkat' => $q,
          'tingkatlembaga' => $r,
          'location' => isset($parent['14_location']) ? $parent['14_location'] : '',
          'provinsi' => '-',
          'negara' => isset($parent['14_negara']) ? $parent['14_negara'] : '',
          'uraian' => $parent['14_aktifitas'],
          'startdate' => $startyear,
          'startyear' => $endyear,
          'kompetensi' => $parent['14_kompetensi'][0],

          'p' => $p,
          'q' => $q,
          'r' => $r,
          't' => $t,
          'attachment' => '',
          'createdby' => $id,
          'modifiedby' => $id,
          'modifieddate' => date('Y-m-d H:i:s'),
          'status' => 1,
        );

        $insert_id = $this->main_mod->insert('user_faip_14', $row);

        $i = 0;
        foreach ($parent['14_kompetensi'] as $kompetensi) {
          if ($i > 0) {
            if ($kompetensi == 'W.1.4.1.') $kompetensi = 'W.1.4.4.';
            else if ($kompetensi == 'W.1.4.2.') $kompetensi = 'W.1.4.5.';
            else if ($kompetensi == 'W.1.4.3.') $kompetensi = 'W.1.4.6.';
            else if ($kompetensi == 'W.1.4.4.') $kompetensi = 'W.1.4.7.';
            else if ($kompetensi == 'W.1.4.5.') $kompetensi = 'W.1.4.8.';
            $row = array(
              'faip_id' => $faip_id,
              'parent' => $insert_id,
              'kompetensi' => $kompetensi,

              'p' => $p,
              'q' => $q,
              'r' => $r,
              't' => $t,
              'attachment' => '',
              'createdby' => $id,
              'modifiedby' => $id,
              'modifieddate' => date('Y-m-d H:i:s'),
              'status' => 1,
            );

            $insert = $this->main_mod->insert('user_faip_14', $row);
          }
          $i++;
        }
      }
    }



    //TAB 1.5
    $sheetObj = $loadexcel->getSheetByName('I.5');
    $startFrom = 4;
    $limit = null;
    $i = 1;
    $is_parent = true;
    $parent_15 = array();
    $parent_temp = 0;
    foreach ($sheetObj->getRowIterator($startFrom, $limit) as $row) {
      $j = 0;
      foreach ($row->getCellIterator() as $cell) {
        $value = $cell->getCalculatedValue();
        if ($j == 0 && $value != '') {
          $is_parent = true;
          $parent_temp++;
        } else if ($j != 0 && $value != '') $is_parent = true;
        else $is_parent = false;

        if ($is_parent) {
          if ($j == 1) $parent_15[$parent_temp]['15_nama'] = $cell->getCalculatedValue();
          else if ($j == 2) $parent_15[$parent_temp]['15_lembaga'] = $cell->getCalculatedValue();
          else if ($j == 3) $parent_15[$parent_temp]['15_location'] = $cell->getCalculatedValue();
          else if ($j == 4) $parent_15[$parent_temp]['15_negara'] = $cell->getCalculatedValue();
          else if ($j == 5) $parent_15[$parent_temp]['15_periode'] = $cell->getCalculatedValue();
          else if ($j == 6) $parent_15[$parent_temp]['15_tingkat'] = $cell->getCalculatedValue();
          else if ($j == 7) $parent_15[$parent_temp]['15_jam'] = $cell->getCalculatedValue();
          else if ($j == 8) $parent_15[$parent_temp]['15_aktifitas'] = $cell->getCalculatedValue();
        }

        if ($value != '' && (strpos($value, 'W.') !== false || strpos($value, 'P.') !== false)) {
          $parent_15[$parent_temp]['15_kompetensi'][] = $cell->getCalculatedValue();
        }
        $j++;
      }
      $i++;
    }
    $this->main_mod->delete('user_faip_15', "faip_id", $faip_id);
    if (is_array($parent_15)) {
      foreach ($parent_15 as $parent) {

        //$periode = explode(" s.d. ",$parent['15_periode']);
        $periode = explode(" - ", $parent['15_periode']);
        $startdate = '';
        $startyear = '';
        $enddate = '';
        $endyear = '';
        if (isset($periode[1])) {
          $temp = explode(" ", $periode[1]);
          if (isset($temp[2])) {
            $enddate = $bulan[trim($temp[1])];
            $endyear = $temp[2];
          } else if (isset($temp[1])) {
            $enddate = $bulan[trim($temp[0])];
            $endyear = $temp[1];
          } else {
            $endyear = $periode[1];
          }

          $temp = explode(" ", $periode[0]);
          if (isset($temp[1])) {
            $startdate = $bulan[trim($temp[0])];
            $startyear = $temp[1];
          } else if (!isset($temp[1])) {
            $startdate = $enddate;
            $startyear = $periode[0];
          } else $startdate = $bulan[trim($temp[1])];

          //$startyear = $endyear;
        } else {
          /*$temp = explode(" ",$parent['15_periode']);
					$startdate = $bulan[trim($temp[1])];
					$startyear = $temp[2];
					$enddate = $startdate;
					$endyear = $startyear;*/

          $temp = explode(" ", $parent['15_periode']);
          //$temp = explode(" / ",$parent['15_periode']);

          if (isset($temp[2])) {
            $startdate = $bulan[trim($temp[1])];
            $startyear = $temp[2];
            $enddate = $startdate;
            $endyear = $startyear;
          } else if (isset($temp[1])) {
            $startdate = $bulan[trim($temp[0])];
            $startyear = $temp[1];
            $enddate = $startdate;
            $endyear = $startyear;
          } else {
            $startdate = 1;
            $startyear = $temp[0];
            $enddate = $startdate;
            $endyear = $startyear;
          }
        }

        $is_present = 0;
        if (strtolower($endyear) == 'sekarang') {
          $endyear = '';
          $is_present = 1;
        }

        $p = 0;
        $tempidx = 0;
        $years = count($parent_15);
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
        }
        $p = $tempidx;
        $q_master = isset($parent['15_jam']) ? $this->main_mod->msrwhere('m_bakuan_penilaian', array('faip_num' => '15', 'faip_type' => 'q', 'desc' => $parent['15_jam']), 'id', 'desc')->row() : 0;
        $r_master = isset($parent['15_tingkat']) ? $this->main_mod->msrwhere('m_bakuan_penilaian', array('faip_num' => '15', 'faip_type' => 'r', 'desc' => $parent['15_tingkat']), 'id', 'desc')->row() : 0;

        $q = isset($q_master->value) ? $q_master->value : 0;
        $r = isset($r_master->value) ? $r_master->value : 0;
        $t = $p * $q * $r;

        $row = array(
          'faip_id' => $faip_id,
          'parent' => 0,
          'nama' => $parent['15_nama'],
          'lembaga' => $parent['15_lembaga'],
          'jam' => $q,
          'tingkat' => $r,
          'location' => $parent['15_location'],
          'provinsi' => '-',
          'negara' => $parent['15_negara'],
          'uraian' => $parent['15_aktifitas'],
          'startdate' => $startdate,
          'startyear' => $startyear,
          'enddate' => $enddate,
          'endyear' => $endyear,
          'is_present' => $is_present,
          'kompetensi' => isset($parent['15_kompetensi'][0]) ? $parent['15_kompetensi'][0] : '',

          'p' => $p,
          'q' => $q,
          'r' => $r,
          't' => $t,
          'attachment' => '',
          'createdby' => $id,
          'modifiedby' => $id,
          'modifieddate' => date('Y-m-d H:i:s'),
          'status' => 1,
        );

        $insert_id = $this->main_mod->insert('user_faip_15', $row);

        $i = 0;
        if (isset($parent['15_kompetensi'])) {
          foreach ($parent['15_kompetensi'] as $kompetensi) {
            if ($i > 0) {
              $row = array(
                'faip_id' => $faip_id,
                'parent' => $insert_id,
                'kompetensi' => $kompetensi,

                'p' => $p,
                'q' => $q,
                'r' => $r,
                't' => $t,
                'attachment' => '',
                'createdby' => $id,
                'modifiedby' => $id,
                'modifieddate' => date('Y-m-d H:i:s'),
                'status' => 1,
              );

              $insert = $this->main_mod->insert('user_faip_15', $row);
            }
            $i++;
          }
        }
      }
    }


    //TAB 1.6
    $sheetObj = $loadexcel->getSheetByName('I.6');
    $startFrom = 4;
    $limit = null;
    $i = 1;
    $is_parent = true;
    $parent_16 = array();
    $parent_temp = 0;
    foreach ($sheetObj->getRowIterator($startFrom, $limit) as $row) {
      $j = 0;
      foreach ($row->getCellIterator() as $cell) {
        $value = $cell->getCalculatedValue();
        if ($j == 0 && $value != '') {
          $is_parent = true;
          $parent_temp++;
        } else if ($j != 0 && $value != '') $is_parent = true;
        else $is_parent = false;

        if ($is_parent) {
          if ($j == 1) $parent_16[$parent_temp]['16_nama'] = $cell->getCalculatedValue();
          else if ($j == 2) $parent_16[$parent_temp]['16_lembaga'] = $cell->getCalculatedValue();
          else if ($j == 3) $parent_16[$parent_temp]['16_location'] = $cell->getCalculatedValue();
          else if ($j == 4) $parent_16[$parent_temp]['16_negara'] = $cell->getCalculatedValue();
          else if ($j == 5) $parent_16[$parent_temp]['16_periode'] = $cell->getCalculatedValue();
          else if ($j == 6) $parent_16[$parent_temp]['16_tingkat'] = $cell->getCalculatedValue();
          else if ($j == 7) $parent_16[$parent_temp]['16_jam'] = $cell->getCalculatedValue();
          else if ($j == 8) $parent_16[$parent_temp]['16_aktifitas'] = $cell->getCalculatedValue();
        }

        if ($value != '' && (strpos($value, 'W.') !== false || strpos($value, 'P.') !== false)) {
          $parent_16[$parent_temp]['16_kompetensi'][] = $cell->getCalculatedValue();
        }
        $j++;
      }
      $i++;
    }
    $this->main_mod->delete('user_faip_16', "faip_id", $faip_id);
    if (is_array($parent_16)) {
      foreach ($parent_16 as $parent) {

        //$periode = explode(" s.d. ",$parent['16_periode']);
        $periode = isset($parent['16_periode']) ? explode(" - ", $parent['16_periode']) : '';
        //$periode = isset($parent['16_periode'])?explode("/",$parent['16_periode']):'';
        //$periode = isset($parent['16_periode'])?explode(" / ",$parent['16_periode']):'';
        $startdate = '';
        $startyear = '';
        $enddate = '';
        $endyear = '';
        if ($periode != '') {
          if (isset($periode[1])) {
            $temp = explode(" ", $periode[1]);

            if (isset($temp[2])) {
              $enddate = $bulan[trim($temp[1])];
              $endyear = $temp[2];
            } else if (isset($temp[1])) {
              $enddate = $bulan[trim($temp[0])];
              $endyear = $temp[1];
            } else {
              $endyear = $periode[1];
            }

            //$enddate = $bulan[trim($temp[1])];
            //$endyear = $temp[2];

            $temp = explode(" ", $periode[0]);
            if (isset($temp[1])) {
              $startdate = $bulan[trim($temp[0])];
              $startyear = $temp[1];
            } else if (!isset($temp[1])) {
              $startdate = $enddate;
              $startyear = $periode[0];
            } else $startdate = $bulan[trim($temp[1])];


            //$temp = explode(" ",$periode[0]);
            //if(!isset($temp[1])) $startdate = $enddate;
            //else $startdate = isset($temp[0])?$bulan[trim($temp[0])]:'1';

            //$startyear = $endyear;
          } else {
            /*$temp = explode(" ",$parent['16_periode']);
					$startdate = $bulan[trim($temp[1])];
					$startyear = $temp[2];
					$enddate = $startdate;
					$endyear = $startyear;*/

            $temp = explode(" ", $parent['16_periode']);
            //$temp = explode(" / ",$parent['16_periode']);

            if (isset($temp[2])) {
              $startdate = $bulan[trim($temp[1])];
              $startyear = $temp[2];
              $enddate = $startdate;
              $endyear = $startyear;
            } else if (isset($temp[1])) {
              $startdate = $bulan[trim($temp[0])];
              $startyear = $temp[1];
              $enddate = $startdate;
              $endyear = $startyear;
            } else {
              $startdate = 1;
              $startyear = $temp[0];
              $enddate = $startdate;
              $endyear = $startyear;
            }
          }
        }

        $is_present = 0;
        if (strtolower($endyear) == 'sekarang') {
          $endyear = '';
          $is_present = 1;
        }

        $p = 0;
        $tempidx = 0;
        $years = count($parent_16);
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
        }
        $p = $tempidx;
        $q_master = isset($parent['16_jam']) ? $this->main_mod->msrwhere('m_bakuan_penilaian', array('faip_num' => '16', 'faip_type' => 'q', 'desc' => $parent['16_jam']), 'id', 'desc')->row() : 0;
        $r_master = $this->main_mod->msrwhere('m_bakuan_penilaian', array('faip_num' => '16', 'faip_type' => 'r', 'desc' => $parent['16_tingkat']), 'id', 'desc')->row();

        $q = isset($q_master->value) ? $q_master->value : 0;
        $r = isset($r_master->value) ? $r_master->value : 0;
        $t = $p * $q * $r;

        $row = array(
          'faip_id' => $faip_id,
          'parent' => 0,
          'nama' => $parent['16_nama'],
          'lembaga' => isset($parent['16_lembaga']) ? $parent['16_lembaga'] : '',
          'jam' => $q,
          'tingkat' => $r,
          'location' => isset($parent['16_location']) ? $parent['16_location'] : '',
          'provinsi' => '-',
          'negara' => isset($parent['16_negara']) ? $parent['16_negara'] : '',
          'uraian' => isset($parent['16_aktifitas']) ? $parent['16_aktifitas'] : '',
          'startdate' => $startdate,
          'startyear' => $startyear,
          'enddate' => $enddate,
          'endyear' => $endyear,
          'is_present' => $is_present,
          'kompetensi' => $parent['16_kompetensi'][0],

          'p' => $p,
          'q' => $q,
          'r' => $r,
          't' => $t,
          'attachment' => '',
          'createdby' => $id,
          'modifiedby' => $id,
          'modifieddate' => date('Y-m-d H:i:s'),
          'status' => 1,
        );

        $insert_id = $this->main_mod->insert('user_faip_16', $row);

        $i = 0;
        foreach ($parent['16_kompetensi'] as $kompetensi) {
          if ($i > 0) {
            $row = array(
              'faip_id' => $faip_id,
              'parent' => $insert_id,
              'kompetensi' => $kompetensi,

              'p' => $p,
              'q' => $q,
              'r' => $r,
              't' => $t,
              'attachment' => '',
              'createdby' => $id,
              'modifiedby' => $id,
              'modifieddate' => date('Y-m-d H:i:s'),
              'status' => 1,
            );

            $insert = $this->main_mod->insert('user_faip_16', $row);
          }
          $i++;
        }
      }
    }


    //TAB II.1
    $sheetObj = $loadexcel->getSheetByName('II.1');
    $startFrom = 4;
    $limit = null;
    $i = 1;
    $is_parent = true;
    $parent_21 = array();
    $parent_temp = 0;
    foreach ($sheetObj->getRowIterator($startFrom, $limit) as $row) {
      $j = 0;
      foreach ($row->getCellIterator() as $cell) {
        $value = $cell->getCalculatedValue();
        if ($j == 0 && $value != '') {
          $is_parent = true;
          $parent_temp++;
        } else if ($j != 0 && $value != '') $is_parent = true;
        else $is_parent = false;

        if ($is_parent) {
          if ($j == 1) $parent_21[$parent_temp]['21_nama'] = $cell->getCalculatedValue();
          else if ($j == 2) $parent_21[$parent_temp]['21_alamat'] = $cell->getCalculatedValue();
          else if ($j == 3) $parent_21[$parent_temp]['21_notelp'] = $cell->getCalculatedValue();
          else if ($j == 4) $parent_21[$parent_temp]['21_email'] = $cell->getCalculatedValue();
          else if ($j == 5) $parent_21[$parent_temp]['21_hubungan'] = $cell->getCalculatedValue();
        }

        //if($value!='' && (strpos($value, 'W.') !== false || strpos($value, 'P.') !== false)){
        //$parent_21[$parent_temp]['21_kompetensi'][] = $cell->getCalculatedValue();
        //}
        $j++;
      }
      $i++;
    }
    $this->main_mod->delete('user_faip_21', "faip_id", $faip_id);
    if (is_array($parent_21)) {
      $t = count($parent_21);

      if ($t == 1) $t = 35;
      else if ($t == 2) $t = 40;
      else if ($t == 3) $t = 45;
      else if ($t > 3) $t = 50;

      $i = 0;
      foreach ($parent_21 as $parent) {
        $row = array(
          'faip_id' => $faip_id,
          'parent' => 0,

          'alamat' => $parent['21_alamat'],
          'notelp' => $parent['21_notelp'],
          'nama' => $parent['21_nama'],
          'lembaga' => $parent['21_hubungan'],
          //'kota' => $parent['21_alamat'],
          'kota' => '-',
          'provinsi' => '-',
          'negara' => '-',
          'email' => isset($parent['21_email']) ? $parent['21_email'] : '-',
          'hubungan' => 'Lain-lain',
          't' => $i == 0 ? $t : 0,
          'kompetensi' => 'W.1',
          //'attachment' => '',	
          'createdby' => $id,
          'modifiedby' => $id,
          'modifieddate' => date('Y-m-d H:i:s'),
          'status' => 1,
        );

        $insert_id = $this->main_mod->insert('user_faip_21', $row);

        $i++;
      }
    }


    //TAB II.2
    $sheetObj = $loadexcel->getSheetByName('II.2');
    $startFrom = 4;
    //$startFrom = 3; 
    $limit = null;
    $i = 1;
    $is_parent = true;
    $parent_22 = array();
    $parent_temp = 0;
    foreach ($sheetObj->getRowIterator($startFrom, $limit) as $row) {
      $j = 0;
      foreach ($row->getCellIterator() as $cell) {
        $value = $cell->getCalculatedValue();
        if ($j == 0 && $value != '') {
          $is_parent = true;
          $parent_temp++;
        } else if ($j != 0 && $value != '') $is_parent = true;
        else $is_parent = false;

        if ($is_parent) {
          if ($j == 0) $parent_22[$parent_temp]['22_uraian'] = $cell->getCalculatedValue();
        }

        //if($value!='' && (strpos($value, 'W.') !== false || strpos($value, 'P.') !== false)){
        //$parent_22[$parent_temp]['22_kompetensi'][] = $cell->getCalculatedValue();
        //}
        $j++;
      }
      $i++;
    }
    $this->main_mod->delete('user_faip_22', "faip_id", $faip_id);
    if (is_array($parent_22)) {
      foreach ($parent_22 as $parent) {
        $row = array(
          'faip_id' => $faip_id,
          'parent' => 0,

          'uraian' => $parent['22_uraian'],
          'kompetensi' => 'W.1',
          't' => ($parent['22_uraian'] != '' ? '30' : '0'),

          //'attachment' => '',	
          'createdby' => $id,
          'modifiedby' => $id,
          'modifieddate' => date('Y-m-d H:i:s'),
          'status' => 1,
        );

        $insert_id = $this->main_mod->insert('user_faip_22', $row);
      }
    }




    //TAB III
    $sheetObj = $loadexcel->getSheetByName('III');
    $startFrom = 4;
    $limit = null;
    $i = 1;
    $is_parent = true;
    $parent_3 = array();
    $parent_temp = 0;
    foreach ($sheetObj->getRowIterator($startFrom, $limit) as $row) {
      $j = 0;
      foreach ($row->getCellIterator() as $cell) {
        $value = $cell->getCalculatedValue();
        if ($j == 0 && $value != '') {
          $is_parent = true;
          $parent_temp++;
        } else if ($j != 0 && $value != '') $is_parent = true;
        else $is_parent = false;

        if ($is_parent) {
          if ($j == 1) $parent_3[$parent_temp]['3_periode'] = $cell->getCalculatedValue();
          else if ($j == 2) $parent_3[$parent_temp]['3_instansi'] = $cell->getCalculatedValue();
          else if ($j == 3) $parent_3[$parent_temp]['3_title'] = $cell->getCalculatedValue();
          else if ($j == 4) $parent_3[$parent_temp]['3_namaproyek'] = $cell->getCalculatedValue();
          else if ($j == 5) $parent_3[$parent_temp]['3_pemberitugas'] = $cell->getCalculatedValue();
          else if ($j == 6) $parent_3[$parent_temp]['3_location'] = $cell->getCalculatedValue();
          else if ($j == 7) $parent_3[$parent_temp]['3_durasi'] = $cell->getCalculatedValue();
          else if ($j == 8) $parent_3[$parent_temp]['3_posisi'] = $cell->getCalculatedValue();
          else if ($j == 9) $parent_3[$parent_temp]['3_nilaipry'] = $cell->getCalculatedValue();
          else if ($j == 10) $parent_3[$parent_temp]['3_nilaijasa'] = $cell->getCalculatedValue();
          else if ($j == 11) $parent_3[$parent_temp]['3_nilaisdm'] = $cell->getCalculatedValue();
          else if ($j == 12) $parent_3[$parent_temp]['3_nilaisulit'] = $cell->getCalculatedValue();
          else if ($j == 13) $parent_3[$parent_temp]['3_nilaiproyek'] = $cell->getCalculatedValue();
          else if ($j == 14) $parent_3[$parent_temp]['3_aktifitas'] = $cell->getCalculatedValue();
        }

        if ($value != '' && (strpos($value, 'W.') !== false || strpos($value, 'P.') !== false)) {
          $parent_3[$parent_temp]['3_kompetensi'][] = $cell->getCalculatedValue();
        }
        $j++;
      }
      $i++;
    }
    $this->main_mod->delete('user_faip_3', "faip_id", $faip_id);
    if (is_array($parent_3)) {
      foreach ($parent_3 as $parent) {
        //$periode = explode(" s/d ",$parent['3_periode']);
        //$periode = explode(" s.d. ",$parent['3_periode']);
        //$periode = explode("-",$parent['3_periode']);


        $periode = (isset($parent['3_periode'])) ? explode(" - ", $parent['3_periode']) : '';
        $startdate = '';
        $startyear = '';
        $enddate = '';
        $endyear = '';
        if (isset($periode[1])) {
          if (trim(strtolower($periode[1])) != "sekarang") {
            $temp = explode(" ", $periode[1]);
            if (count($temp) == 2) {
              $enddate = $bulan[trim($temp[0])];
              $endyear = $temp[1];
            } else if (count($temp) > 2) {
              $enddate = $bulan[trim($temp[1])];
              $endyear = $temp[2];
            } else if (count($temp) == 1) {
              $enddate = 1;
              $endyear = $temp[0];
            }
          }

          $temp = explode(" ", $periode[0]);
          if (count($temp) == 2) {
            $startdate = $bulan[trim($temp[0])];
            $startyear = $temp[1];
          } else if (count($temp) > 2) {
            $startdate = $bulan[trim($temp[1])];
            $startyear = $temp[2];
          } else {
            $startdate = isset($temp[1]) ? $bulan[trim($temp[1])] : 1;
            $startyear = isset($temp[1]) ? $endyear : $temp[0];
          }
        } else {
          $temp = (isset($parent['3_periode'])) ? explode(" ", $parent['3_periode']) : '';
          //$startdate = isset($temp[0])?$temp[0]:1;
          //$startyear = isset($temp[1])?$temp[1]:(isset($temp[0])?$temp[0]:'');
          //$enddate = $startdate;
          //$endyear = $startyear;

          if (isset($temp[2])) {
            $startdate = $bulan[trim($temp[1])];
            $startyear = $temp[2];
            $enddate = $startdate;
            $endyear = $startyear;
          } else if (isset($temp[1])) {
            $startdate = $bulan[trim($temp[0])];
            $startyear = $temp[1];
            $enddate = $startdate;
            $endyear = $startyear;
          } else {
            $startdate = 1;
            $startyear = $temp[0];
            $enddate = $startdate;
            $endyear = $startyear;
          }

          //$temp = explode(" ",$parent['3_periode']);
          //$startdate = $bulan[trim($temp[1])];
          //$startyear = $temp[2];
          //$enddate = $startdate;
          //$endyear = $startyear;
        }

        $is_present = 0;
        if (isset($periode[1])) {
          if (trim(strtolower($periode[1])) == "sekarang") {
            $is_present = 1;
          }
        }

        $nilaisdm = 0;
        if (isset($parent['3_nilaisdm'])) {
          if ($parent['3_nilaisdm'] == 'Sedikit') $nilaisdm = 1;
          else if ($parent['3_nilaisdm'] == 'Sedang') $nilaisdm = 2;
          else if ($parent['3_nilaisdm'] == 'Banyak') $nilaisdm = 3;
          else if ($parent['3_nilaisdm'] == 'Sangat Banyak') $nilaisdm = 4;
        }
        $nilaisulit = 0;
        if (isset($parent['3_nilaisulit'])) {
          if ($parent['3_nilaisulit'] == 'Rendah') $nilaisulit = 1;
          else if ($parent['3_nilaisulit'] == 'Sedang') $nilaisulit = 2;
          else if ($parent['3_nilaisulit'] == 'Tinggi') $nilaisulit = 3;
          else if ($parent['3_nilaisulit'] == 'Sangat Tinggi') $nilaisulit = 4;
        }


        $p = 0;
        $p_master = isset($parent['3_durasi']) ? $this->main_mod->msrwhere('m_bakuan_penilaian', array('faip_num' => '3', 'faip_type' => 'p', 'desc' => $parent['3_durasi']), 'id', 'desc')->row() : 0;
        $q_master = 0;
        if (isset($parent['3_posisi']))
          $q_master = $this->main_mod->msrwhere('m_bakuan_penilaian', array('faip_num' => '3', 'faip_type' => 'q', 'desc' => trim($parent['3_posisi'])), 'id', 'desc')->row();

        $r_master = 0;
        if (isset($parent['3_nilaiproyek']))
          $r_master = $this->main_mod->msrwhere('m_bakuan_penilaian', array('faip_num' => '3', 'faip_type' => 'r', 'desc' => $parent['3_nilaiproyek']), 'id', 'desc')->row();

        $p = isset($p_master->value) ? $p_master->value : 0;
        $q = isset($q_master->value) ? $q_master->value : 0;
        $r = isset($r_master->value) ? $r_master->value : 0;
        $t = $p * $q * $r;

        $row = array(
          'faip_id' => $faip_id,
          'parent' => 0,

          'instansi' => isset($parent['3_instansi']) ? $parent['3_instansi'] : '',
          'location' => isset($parent['3_location']) ? $parent['3_location'] : '',
          'provinsi' => '-',
          'negara' => '-',
          'namaproyek' => isset($parent['3_namaproyek']) ? $parent['3_namaproyek'] : '-',
          'posisi' => $q,
          'periode' => $p,
          'nilaiproyek' => $r,
          'pemberitugas' => isset($parent['3_pemberitugas']) ? $parent['3_pemberitugas'] : '-',
          'startdate' => $startdate,
          'startyear' => $startyear,
          'enddate' => $enddate,
          'endyear' => $endyear,
          'is_present' => $is_present,
          'title' => isset($parent['3_title']) ? $parent['3_title'] : '-',
          'nilaipry' => isset($parent['3_nilaipry']) ? $parent['3_nilaipry'] : '-',
          'nilaijasa' => (isset($parent['3_nilaijasa']) ? $parent['3_nilaijasa'] : '-'),
          'nilaisdm' => $nilaisdm,
          'nilaisulit' => $nilaisulit,

          'uraian' => isset($parent['3_aktifitas']) ? $parent['3_aktifitas'] : '-',


          'kompetensi' => isset($parent['3_kompetensi'][0]) ? $parent['3_kompetensi'][0] : 'null',
          'p' => $p,
          'q' => $q,
          'r' => $r,
          't' => $t,
          'attachment' => '',
          'createdby' => $id,
          'modifiedby' => $id,
          'modifieddate' => date('Y-m-d H:i:s'),
          'status' => 1,
        );

        $insert_id = $this->main_mod->insert('user_faip_3', $row);

        $i = 0;
        if (isset($parent['3_kompetensi'][0])) {
          foreach ($parent['3_kompetensi'] as $kompetensi) {
            if ($i > 0) {
              $row = array(
                'faip_id' => $faip_id,
                'parent' => $insert_id,
                'kompetensi' => $kompetensi,

                'p' => $p,
                'q' => $q,
                'r' => $r,
                't' => $t,
                'attachment' => '',
                'createdby' => $id,
                'modifiedby' => $id,
                'modifieddate' => date('Y-m-d H:i:s'),
                'status' => 1,
              );

              $insert = $this->main_mod->insert('user_faip_3', $row);
            }
            $i++;
          }
        }
      }
    }


    //TAB 1V
    $sheetObj = $loadexcel->getSheetByName('IV');
    $startFrom = 4;
    $limit = null;
    $i = 1;
    $is_parent = true;
    $parent_4 = array();
    $parent_temp = 0;
    foreach ($sheetObj->getRowIterator($startFrom, $limit) as $row) {
      $j = 0;
      foreach ($row->getCellIterator() as $cell) {
        $value = $cell->getCalculatedValue();
        if ($j == 0 && $value != '') {
          $is_parent = true;
          $parent_temp++;
        } else if ($j != 0 && $value != '') $is_parent = true;
        else $is_parent = false;

        if ($is_parent) {
          if ($j == 1) $parent_4[$parent_temp]['4_periode'] = $cell->getCalculatedValue();
          else if ($j == 2) $parent_4[$parent_temp]['4_instansi'] = $cell->getCalculatedValue();
          else if ($j == 3) $parent_4[$parent_temp]['4_namaproyek'] = $cell->getCalculatedValue();
          else if ($j == 4) $parent_4[$parent_temp]['4_location'] = $cell->getCalculatedValue();
          else if ($j == 5) $parent_4[$parent_temp]['4_durasi'] = $cell->getCalculatedValue();
          else if ($j == 6) $parent_4[$parent_temp]['4_posisi'] = $cell->getCalculatedValue();
          else if ($j == 7) $parent_4[$parent_temp]['4_jumlahsks'] = $cell->getCalculatedValue();
          else if ($j == 8) $parent_4[$parent_temp]['4_aktifitas'] = $cell->getCalculatedValue();

          /*if($j==1) $parent_4[$parent_temp]['4_periode'] = $cell->getCalculatedValue();
					else if($j==2) $parent_4[$parent_temp]['4_instansi'] = $cell->getCalculatedValue();
					else if($j==3) $parent_4[$parent_temp]['4_location'] = $cell->getCalculatedValue();
					else if($j==4) $parent_4[$parent_temp]['4_durasi'] = $cell->getCalculatedValue();
					else if($j==5) $parent_4[$parent_temp]['4_posisi'] = $cell->getCalculatedValue();
					else if($j==6) $parent_4[$parent_temp]['4_jumlahsks'] = $cell->getCalculatedValue();
					else if($j==7) $parent_4[$parent_temp]['4_namaproyek'] = $cell->getCalculatedValue();*/
        }

        if ($value != '' && (strpos($value, 'W.') !== false || strpos($value, 'P.') !== false)) {
          $parent_4[$parent_temp]['4_kompetensi'][] = $cell->getCalculatedValue();
        }
        $j++;
      }
      $i++;
    }
    $this->main_mod->delete('user_faip_4', "faip_id", $faip_id);
    if (is_array($parent_4)) {
      foreach ($parent_4 as $parent) {

        //$periode = explode(" s.d. ",$parent['4_periode']);
        $periode = explode("-", $parent['4_periode']);
        $startdate = '';
        $startyear = '';
        $enddate = '';
        $endyear = '';
        if (isset($periode[1])) {
          $temp = explode(" ", $periode[1]);

          if (isset($temp[2])) {
            //$enddate = $bulan[trim($temp[1])];
            //$endyear = $temp[2];
          }

          $temp = explode(" ", $periode[0]);
          //if(!isset($temp[1])) $startdate = $enddate;
          //else $startdate = $bulan[trim($temp[1])];

          $startyear = $endyear;
        } else {
          $temp = explode(" ", $parent['4_periode']);
          if (isset($temp[2])) {
            $startdate = $bulan[trim($temp[1])];
            $startyear = $temp[2];
          } else {
            $startdate = 1;
            $startyear = $temp[0];
          }
          $enddate = $startdate;
          $endyear = $startyear;
        }

        $is_present = 0;
        if ($endyear == 'Sekarang') {
          $endyear = '';
          $is_present = 1;
        }

        $p_master = $this->main_mod->msrwhere('m_bakuan_penilaian', array('faip_num' => '4', 'faip_type' => 'p', 'desc' => $parent['4_durasi']), 'id', 'desc')->row();
        $q_master = $this->main_mod->msrwhere('m_bakuan_penilaian', array('faip_num' => '4', 'faip_type' => 'q', 'desc' => $parent['4_posisi']), 'id', 'desc')->row();
        $r_master = $this->main_mod->msrwhere('m_bakuan_penilaian', array('faip_num' => '4', 'faip_type' => 'r', 'desc' => $parent['4_jumlahsks']), 'id', 'desc')->row();

        $p = isset($p_master->value) ? $p_master->value : 0;
        $q = isset($q_master->value) ? $q_master->value : 0;
        $r = isset($r_master->value) ? $r_master->value : 0;
        $t = $p * $q * $r;

        $row = array(
          'faip_id' => $faip_id,
          'parent' => 0,

          'instansi' => $parent['4_instansi'],
          'periode' => $p,
          'posisi' => $q,
          'jumlahsks' => $r,
          'location' => isset($parent['4_location']) ? $parent['4_location'] : '',
          'provinsi' => '-',
          'negara' => '-',
          'namaproyek' => isset($parent['4_namaproyek']) ? $parent['4_namaproyek'] : '',
          'uraian' => isset($parent['4_aktifitas']) ? $parent['4_aktifitas'] : '',
          'startdate' => $startdate == '' ? 1 : $startdate,
          'startyear' => $startyear,
          'enddate' => $enddate,
          'endyear' => $endyear,
          'kompetensi' => $parent['4_kompetensi'][0],

          'p' => $p,
          'q' => $q,
          'r' => $r,
          't' => $t,
          'attachment' => '',
          'createdby' => $id,
          'modifiedby' => $id,
          'modifieddate' => date('Y-m-d H:i:s'),
          'status' => 1,
        );

        $insert_id = $this->main_mod->insert('user_faip_4', $row);

        $i = 0;
        foreach ($parent['4_kompetensi'] as $kompetensi) {
          if ($i > 0) {
            $row = array(
              'faip_id' => $faip_id,
              'parent' => $insert_id,
              'kompetensi' => $kompetensi,

              'p' => $p,
              'q' => $q,
              'r' => $r,
              't' => $t,
              'attachment' => '',
              'createdby' => $id,
              'modifiedby' => $id,
              'modifieddate' => date('Y-m-d H:i:s'),
              'status' => 1,
            );

            $insert = $this->main_mod->insert('user_faip_4', $row);
          }
          $i++;
        }
      }
    }



    //TAB V.1
    $sheetObj = $loadexcel->getSheetByName('V.1');
    $startFrom = 4;
    $limit = null;
    $i = 1;
    $is_parent = true;
    $parent_51 = array();
    $parent_temp = 0;
    foreach ($sheetObj->getRowIterator($startFrom, $limit) as $row) {
      $j = 0;
      foreach ($row->getCellIterator() as $cell) {
        $value = $cell->getCalculatedValue();
        if ($j == 0 && $value != '') {
          $is_parent = true;
          $parent_temp++;
        } else if ($j != 0 && $value != '') $is_parent = true;
        else $is_parent = false;

        if ($is_parent) {
          if ($j == 1) $parent_51[$parent_temp]['51_perioda'] = $cell->getCalculatedValue();
          else if ($j == 2) $parent_51[$parent_temp]['51_nama'] = $cell->getCalculatedValue();
          else if ($j == 3) $parent_51[$parent_temp]['51_media'] = $cell->getCalculatedValue();
          else if ($j == 4) $parent_51[$parent_temp]['51_location'] = $cell->getCalculatedValue();
          else if ($j == 5) $parent_51[$parent_temp]['51_tingkatmedia'] = $cell->getCalculatedValue();
          else if ($j == 6) $parent_51[$parent_temp]['51_tingkat'] = $cell->getCalculatedValue();
          else if ($j == 7) $parent_51[$parent_temp]['51_uraian'] = $cell->getCalculatedValue();
        }

        if ($value != '' && (strpos($value, 'W.') !== false || strpos($value, 'P.') !== false)) {
          $parent_51[$parent_temp]['51_kompetensi'][] = $cell->getCalculatedValue();
        }
        $j++;
      }
      $i++;
    }
    $this->main_mod->delete('user_faip_51', "faip_id", $faip_id);
    if (is_array($parent_51)) {
      foreach ($parent_51 as $parent) {

        $periode = explode(" ", $parent['51_perioda']);
        $startdate = '';
        $startyear = '';
        if (isset($periode[1])) {
          $startdate = $bulan[trim($periode[0])];
          $startyear = $periode[1];
        } else {
          $startdate = '1';
          $startyear = $periode[0];
        }

        $is_present = 0;
        if ($endyear == 'Sekarang') {
          $is_present = 1;
        }

        $p = 0;
        $tempidx = 0;
        $years = count($parent_51);
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
        }
        $p = $tempidx;
        $q_master = $this->main_mod->msrwhere('m_bakuan_penilaian', array('faip_num' => '51', 'faip_type' => 'q', 'desc' => $parent['51_tingkatmedia']), 'id', 'desc')->row();
        //$r_master=$this->main_mod->msrwhere('m_bakuan_penilaian',array('faip_num'=>'51','faip_type'=>'r','desc'=>$parent['51_tingkat']),'id','desc')->row();

        $r = 0;
        if ($parent['51_tingkat'] == 'Rendah') $r = 1;
        else if ($parent['51_tingkat'] == 'Sedang') $r = 2;
        else if ($parent['51_tingkat'] == 'Tinggi') $r = 3;
        else if ($parent['51_tingkat'] == 'Sangat Tinggi') $r = 4;


        $q = isset($q_master->value) ? $q_master->value : 0;
        //$r =isset($r_master->value)?$r_master->value:0;
        $t = $p * $q * $r;

        $row = array(
          'faip_id' => $faip_id,
          'parent' => 0,
          'startdate' => $startdate,
          'startyear' => $startyear,
          'nama' => $parent['51_nama'],
          'location' => isset($parent['51_location']) ? $parent['51_location'] : '-',
          'provinsi' => '-',
          'negara' => '-',
          'media' => isset($parent['51_media']) ? $parent['51_media'] : '',
          'tingkatmedia' => $q,
          'tingkat' => $r,
          'uraian' => isset($parent['51_uraian']) ? $parent['51_uraian'] : ' ',

          'kompetensi' => $parent['51_kompetensi'][0],

          'p' => $p,
          'q' => $q,
          'r' => $r,
          't' => $t,
          'attachment' => '',
          'createdby' => $id,
          'modifiedby' => $id,
          'modifieddate' => date('Y-m-d H:i:s'),
          'status' => 1,
        );

        $insert_id = $this->main_mod->insert('user_faip_51', $row);

        $i = 0;
        foreach ($parent['51_kompetensi'] as $kompetensi) {
          if ($i > 0) {
            $row = array(
              'faip_id' => $faip_id,
              'parent' => $insert_id,
              'kompetensi' => $kompetensi,

              'p' => $p,
              'q' => $q,
              'r' => $r,
              't' => $t,
              'attachment' => '',
              'createdby' => $id,
              'modifiedby' => $id,
              'modifieddate' => date('Y-m-d H:i:s'),
              'status' => 1,
            );

            $insert = $this->main_mod->insert('user_faip_51', $row);
          }
          $i++;
        }
      }
    }



    //TAB V.2
    $sheetObj = $loadexcel->getSheetByName('V.2');
    $startFrom = 4;
    $limit = null;
    $i = 1;
    $is_parent = true;
    $parent_52 = array();
    $parent_temp = 0;
    foreach ($sheetObj->getRowIterator($startFrom, $limit) as $row) {
      $j = 0;
      foreach ($row->getCellIterator() as $cell) {
        $value = $cell->getCalculatedValue();
        if ($j == 0 && $value != '') {
          $is_parent = true;
          $parent_temp++;
        } else if ($j != 0 && $value != '') $is_parent = true;
        else $is_parent = false;

        if ($is_parent) {
          if ($j == 1) $parent_52[$parent_temp]['52_perioda'] = $cell->getCalculatedValue();
          else if ($j == 2) $parent_52[$parent_temp]['52_judul'] = $cell->getCalculatedValue();
          else if ($j == 3) $parent_52[$parent_temp]['52_nama'] = $cell->getCalculatedValue();
          else if ($j == 4) $parent_52[$parent_temp]['52_penyelenggara'] = $cell->getCalculatedValue();
          else if ($j == 5) $parent_52[$parent_temp]['52_location'] = $cell->getCalculatedValue();
          else if ($j == 6) $parent_52[$parent_temp]['52_tingkatseminar'] = $cell->getCalculatedValue();
          else if ($j == 7) $parent_52[$parent_temp]['52_tingkat'] = $cell->getCalculatedValue();
          else if ($j == 8) $parent_52[$parent_temp]['52_uraian'] = $cell->getCalculatedValue();
        }

        if ($value != '' && (strpos($value, 'W.') !== false || strpos($value, 'P.') !== false)) {
          $parent_52[$parent_temp]['52_kompetensi'][] = $cell->getCalculatedValue();
        }
        $j++;
      }
      $i++;
    }
    $this->main_mod->delete('user_faip_52', "faip_id", $faip_id);
    if (is_array($parent_52)) {
      foreach ($parent_52 as $parent) {

        $periode = explode(" ", $parent['52_perioda']);
        $startdate = '';
        $startyear = '';
        if (isset($periode[2])) {
          $startdate = $bulan[trim($periode[1])];
          $startyear = $periode[2];
        } else if (isset($periode[1])) {
          $startdate = $bulan[trim($periode[0])];
          $startyear = $periode[1];
        } else {
          $startdate = 1;
          $startyear = $periode[0];
        }

        $is_present = 0;
        if ($endyear == 'Sekarang') {
          $is_present = 1;
        }

        $p = 0;
        $tempidx = 0;
        $years = count($parent_52);
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
        }
        $p = $tempidx;
        $q_master = $this->main_mod->msrwhere('m_bakuan_penilaian', array('faip_num' => '52', 'faip_type' => 'q', 'desc' => $parent['52_tingkatseminar']), 'id', 'desc')->row();
        //$r_master=$this->main_mod->msrwhere('m_bakuan_penilaian',array('faip_num'=>'52','faip_type'=>'r','desc'=>$parent['52_tingkat']),'id','desc')->row();

        $r = 0;
        if ($parent['52_tingkat'] == 'Rendah') $r = 1;
        else if ($parent['52_tingkat'] == 'Sedang') $r = 2;
        else if ($parent['52_tingkat'] == 'Tinggi') $r = 3;
        else if ($parent['52_tingkat'] == 'Sangat Tinggi') $r = 4;


        $q = isset($q_master->value) ? $q_master->value : 0;
        //$r =isset($r_master->value)?$r_master->value:0;
        $t = $p * $q * $r;

        $row = array(
          'faip_id' => $faip_id,
          'parent' => 0,
          'startdate' => $startdate,
          'startyear' => $startyear,
          'judul' => $parent['52_judul'],
          'nama' => $parent['52_nama'],
          'penyelenggara' => isset($parent['52_penyelenggara']) ? $parent['52_penyelenggara'] : '',
          'location' => $parent['52_location'],
          'provinsi' => '-',
          'negara' => '-',
          'tingkatseminar' => $q,
          'tingkat' => $r,
          'uraian' => $parent['52_uraian'],

          'kompetensi' => $parent['52_kompetensi'][0],

          'p' => $p,
          'q' => $q,
          'r' => $r,
          't' => $t,
          'attachment' => '',
          'createdby' => $id,
          'modifiedby' => $id,
          'modifieddate' => date('Y-m-d H:i:s'),
          'status' => 1,
        );

        $insert_id = $this->main_mod->insert('user_faip_52', $row);

        $i = 0;
        foreach ($parent['52_kompetensi'] as $kompetensi) {
          if ($i > 0) {
            $row = array(
              'faip_id' => $faip_id,
              'parent' => $insert_id,
              'kompetensi' => $kompetensi,

              'p' => $p,
              'q' => $q,
              'r' => $r,
              't' => $t,
              'attachment' => '',
              'createdby' => $id,
              'modifiedby' => $id,
              'modifieddate' => date('Y-m-d H:i:s'),
              'status' => 1,
            );

            $insert = $this->main_mod->insert('user_faip_52', $row);
          }
          $i++;
        }
      }
    }


    //TAB V.3
    $sheetObj = $loadexcel->getSheetByName('V.3');
    $startFrom = 4;
    $limit = null;
    $i = 1;
    $is_parent = true;
    $parent_53 = array();
    $parent_temp = 0;
    foreach ($sheetObj->getRowIterator($startFrom, $limit) as $row) {
      $j = 0;
      foreach ($row->getCellIterator() as $cell) {
        $value = $cell->getCalculatedValue();
        if ($j == 0 && $value != '') {
          $is_parent = true;
          $parent_temp++;
        } else if ($j != 0 && $value != '') $is_parent = true;
        else $is_parent = false;

        if ($is_parent) {
          if ($j == 1) $parent_53[$parent_temp]['53_perioda'] = $cell->getCalculatedValue();
          else if ($j == 2) $parent_53[$parent_temp]['53_nama'] = $cell->getCalculatedValue();
          else if ($j == 3) $parent_53[$parent_temp]['53_penyelenggara'] = $cell->getCalculatedValue();
          else if ($j == 4) $parent_53[$parent_temp]['53_location'] = $cell->getCalculatedValue();
          else if ($j == 5) $parent_53[$parent_temp]['53_tingkatseminar'] = $cell->getCalculatedValue();
          else if ($j == 6) $parent_53[$parent_temp]['53_tingkat'] = $cell->getCalculatedValue();
          else if ($j == 7) $parent_53[$parent_temp]['53_uraian'] = $cell->getCalculatedValue();
        }

        if ($value != '' && (strpos($value, 'W.') !== false || strpos($value, 'P.') !== false)) {
          $parent_53[$parent_temp]['53_kompetensi'][] = $cell->getCalculatedValue();
        }
        $j++;
      }
      $i++;
    }
    $this->main_mod->delete('user_faip_53', "faip_id", $faip_id);
    if (is_array($parent_53)) {
      foreach ($parent_53 as $parent) {

        $periode = explode(" ", $parent['53_perioda']);
        //$periode = explode(" / ",$parent['53_perioda']);
        $startdate = '';
        $startyear = '';
        if (isset($periode[2])) {
          $startdate = $bulan[trim($periode[1])];
          $startyear = $periode[2];
        } else if (isset($periode[1])) {
          $startdate = $bulan[trim($periode[0])];
          $startyear = $periode[1];
        } else {
          $startdate = 1;
          $startyear = $periode[0];
        }

        $is_present = 0;
        if ($endyear == 'Sekarang') {
          $is_present = 1;
        }

        $p = 0;
        $tempidx = 0;
        $years = count($parent_53);
        $data['bp_53'] = $this->main_mod->msrwhere('m_bakuan_penilaian', array('faip_num' => '53', 'faip_type' => 'p'), 'id', 'desc')->result();
        foreach ($data['bp_53'] as $valbp) {
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
        }
        $p = $tempidx;
        $q_master = $this->main_mod->msrwhere('m_bakuan_penilaian', array('faip_num' => '53', 'faip_type' => 'q', 'desc' => $parent['53_tingkatseminar']), 'id', 'desc')->row();
        //$r_master=$this->main_mod->msrwhere('m_bakuan_penilaian',array('faip_num'=>'53','faip_type'=>'r','desc'=>$parent['53_tingkat']),'id','desc')->row();

        $r = 0;
        if ($parent['53_tingkat'] == 'Rendah') $r = 1;
        else if ($parent['53_tingkat'] == 'Sedang') $r = 2;
        else if ($parent['53_tingkat'] == 'Tinggi') $r = 3;
        else if ($parent['53_tingkat'] == 'Sangat Tinggi') $r = 4;


        $q = isset($q_master->value) ? $q_master->value : 0;
        //$r =isset($r_master->value)?$r_master->value:0;
        $t = $p * $q * $r;

        $row = array(
          'faip_id' => $faip_id,
          'parent' => 0,
          'startdate' => $startdate,
          'startyear' => $startyear,
          'nama' => $parent['53_nama'],
          'penyelenggara' => (isset($parent['53_penyelenggara']) ? $parent['53_penyelenggara'] : '-'),
          'location' => (isset($parent['53_location']) ? $parent['53_location'] : '-'),
          'provinsi' => '-',
          'negara' => '-',
          'tingkatseminar' => $q,
          'tingkat' => $r,
          'uraian' => $parent['53_uraian'],

          'kompetensi' => (isset($parent['53_kompetensi'][0]) ? $parent['53_kompetensi'][0] : ''),

          'p' => $p,
          'q' => $q,
          'r' => $r,
          't' => $t,
          'attachment' => '',
          'createdby' => $id,
          'modifiedby' => $id,
          'modifieddate' => date('Y-m-d H:i:s'),
          'status' => 1,
        );

        $insert_id = $this->main_mod->insert('user_faip_53', $row);

        $i = 0;
        if (isset($parent['53_kompetensi'])) {
          foreach ($parent['53_kompetensi'] as $kompetensi) {
            if ($i > 0) {
              $row = array(
                'faip_id' => $faip_id,
                'parent' => $insert_id,
                'kompetensi' => $kompetensi,

                'p' => $p,
                'q' => $q,
                'r' => $r,
                't' => $t,
                'attachment' => '',
                'createdby' => $id,
                'modifiedby' => $id,
                'modifieddate' => date('Y-m-d H:i:s'),
                'status' => 1,
              );

              $insert = $this->main_mod->insert('user_faip_53', $row);
            }
            $i++;
          }
        }
      }
    }


    //TAB V.4
    $sheetObj = $loadexcel->getSheetByName('V.4');
    $startFrom = 4;
    $limit = null;
    $i = 1;
    $is_parent = true;
    $parent_54 = array();
    $parent_temp = 0;
    foreach ($sheetObj->getRowIterator($startFrom, $limit) as $row) {
      $j = 0;
      foreach ($row->getCellIterator() as $cell) {
        $value = $cell->getCalculatedValue();
        if ($j == 0 && $value != '') {
          $is_parent = true;
          $parent_temp++;
        } else if ($j != 0 && $value != '') $is_parent = true;
        else $is_parent = false;

        if ($is_parent) {
          if ($j == 1) $parent_54[$parent_temp]['54_perioda'] = $cell->getCalculatedValue();
          else if ($j == 2) $parent_54[$parent_temp]['54_nama'] = $cell->getCalculatedValue();
          else if ($j == 3) $parent_54[$parent_temp]['54_uraian'] = $cell->getCalculatedValue();
          else if ($j == 4) $parent_54[$parent_temp]['54_media_publikasi'] = $cell->getCalculatedValue();
          else if ($j == 5) $parent_54[$parent_temp]['54_tingkatseminar'] = $cell->getCalculatedValue();
          else if ($j == 6) $parent_54[$parent_temp]['54_tingkat'] = $cell->getCalculatedValue();
        }

        if ($value != '' && (strpos($value, 'W.') !== false || strpos($value, 'P.') !== false)) {
          $parent_54[$parent_temp]['54_kompetensi'][] = $cell->getCalculatedValue();
        }
        $j++;
      }
      $i++;
    }
    $this->main_mod->delete('user_faip_54', "faip_id", $faip_id);
    if (is_array($parent_54)) {
      foreach ($parent_54 as $parent) {

        $periode = explode(" ", $parent['54_perioda']);
        $startdate = '';
        $startyear = '';
        if (isset($periode[2])) {
          $startdate = $bulan[trim($periode[1])];
          $startyear = $periode[2];
        } else if (isset($periode[1])) {
          $startdate = $bulan[trim($periode[0])];
          $startyear = $periode[1];
        } else {
          $startdate = 1;
          $startyear = $periode[0];
        }

        $is_present = 0;
        if ($endyear == 'Sekarang') {
          $is_present = 1;
        }

        $p = 0;
        $tempidx = 0;
        $years = count($parent_54);
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
        }
        $p = $tempidx;
        $q_master = $this->main_mod->msrwhere('m_bakuan_penilaian', array('faip_num' => '54', 'faip_type' => 'q', 'desc' => $parent['54_tingkatseminar']), 'id', 'desc')->row();
        //$r_master=$this->main_mod->msrwhere('m_bakuan_penilaian',array('faip_num'=>'54','faip_type'=>'r','desc'=>$parent['54_tingkat']),'id','desc')->row();

        $r = 0;
        if ($parent['54_tingkat'] == 'Rendah') $r = 1;
        else if ($parent['54_tingkat'] == 'Sedang') $r = 2;
        else if ($parent['54_tingkat'] == 'Tinggi') $r = 3;
        else if ($parent['54_tingkat'] == 'Sangat Tinggi') $r = 4;


        $q = isset($q_master->value) ? $q_master->value : 0;
        //$r =isset($r_master->value)?$r_master->value:0;
        $t = $p * $q * $r;

        $row = array(
          'faip_id' => $faip_id,
          'parent' => 0,
          'startdate' => $startdate,
          'startyear' => $startyear,
          'nama' => $parent['54_nama'],
          'media_publikasi' => isset($parent['54_media_publikasi']) ? $parent['54_media_publikasi'] : '',
          //'location' => ($parent['54_location']!=''?$parent['54_location']:'-'),
          //'provinsi' => '-',
          //'negara' => '-',
          'tingkatseminar' => $q,
          'tingkat' => $r,
          'uraian' => $parent['54_uraian'],

          'kompetensi' => $parent['54_kompetensi'][0],

          'p' => $p,
          'q' => $q,
          'r' => $r,
          't' => $t,
          'attachment' => '',
          'createdby' => $id,
          'modifiedby' => $id,
          'modifieddate' => date('Y-m-d H:i:s'),
          'status' => 1,
        );

        $insert_id = $this->main_mod->insert('user_faip_54', $row);

        $i = 0;
        foreach ($parent['54_kompetensi'] as $kompetensi) {
          if ($i > 0) {
            $row = array(
              'faip_id' => $faip_id,
              'parent' => $insert_id,
              'kompetensi' => $kompetensi,

              'p' => $p,
              'q' => $q,
              'r' => $r,
              't' => $t,
              'attachment' => '',
              'createdby' => $id,
              'modifiedby' => $id,
              'modifieddate' => date('Y-m-d H:i:s'),
              'status' => 1,
            );

            $insert = $this->main_mod->insert('user_faip_54', $row);
          }
          $i++;
        }
      }
    }


    //TAB VI
    $sheetObj = $loadexcel->getSheetByName('VI');
    $startFrom = 4;
    $limit = null;
    $i = 1;
    $is_parent = true;
    $parent_6 = array();
    $parent_temp = 0;
    foreach ($sheetObj->getRowIterator($startFrom, $limit) as $row) {
      $j = 0;
      foreach ($row->getCellIterator() as $cell) {
        $value = $cell->getCalculatedValue();
        if ($j == 0 && $value != '') {
          $is_parent = true;
          $parent_temp++;
        } else if ($j != 0 && $value != '') $is_parent = true;
        else $is_parent = false;

        if ($is_parent) {
          if ($j == 1) $parent_6[$parent_temp]['6_nama'] = $cell->getCalculatedValue();
          else if ($j == 2) $parent_6[$parent_temp]['6_jenisbahasa'] = $cell->getCalculatedValue();
          else if ($j == 3) $parent_6[$parent_temp]['6_verbal'] = $cell->getCalculatedValue();
          else if ($j == 4) $parent_6[$parent_temp]['6_jenistulisan'] = $cell->getCalculatedValue();
          else if ($j == 5) $parent_6[$parent_temp]['6_nilai'] = $cell->getCalculatedValue();
        }

        if ($value != '' && (strpos($value, 'W.') !== false || strpos($value, 'P.') !== false)) {
          $parent_6[$parent_temp]['6_kompetensi'][] = $cell->getCalculatedValue();
        }
        $j++;
      }
      $i++;
    }
    $this->main_mod->delete('user_faip_6', "faip_id", $faip_id);
    if (is_array($parent_6)) {
      foreach ($parent_6 as $parent) {
        $p = 0;
        $tempidx = 0;
        $years = count($parent_6);
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
        }
        $p = $tempidx;
        $q_master = $this->main_mod->msrwhere('m_bakuan_penilaian', array('faip_num' => '6', 'faip_type' => 'q', 'desc' => $parent['6_jenisbahasa']), 'id', 'desc')->row();
        $r_master = $this->main_mod->msrwhere('m_bakuan_penilaian', array('faip_num' => '6', 'faip_type' => 'r', 'desc' => $parent['6_verbal']), 'id', 'desc')->row();

        $q = isset($q_master->value) ? $q_master->value : 0;
        $r = isset($r_master->value) ? $r_master->value : 0;
        $t = $p * $q * $r;

        $row = array(
          'faip_id' => $faip_id,
          'parent' => 0,
          'nama' => $parent['6_nama'],
          'jenisbahasa' => $q,
          'verbal' => $r,
          'jenistulisan' => 'Laporan', //$parent['6_jenistulisan'],
          'nilai' => (isset($parent['6_nilai']) ? $parent['6_nilai'] : '-'),

          'kompetensi' => $parent['6_kompetensi'][0],

          'p' => $p,
          'q' => $q,
          'r' => $r,
          't' => $t,
          'attachment' => '',
          'createdby' => $id,
          'modifiedby' => $id,
          'modifieddate' => date('Y-m-d H:i:s'),
          'status' => 1,
        );

        $insert_id = $this->main_mod->insert('user_faip_6', $row);

        $i = 0;
        foreach ($parent['6_kompetensi'] as $kompetensi) {
          if ($i > 0) {
            $row = array(
              'faip_id' => $faip_id,
              'parent' => $insert_id,
              'kompetensi' => $kompetensi,

              'p' => $p,
              'q' => $q,
              'r' => $r,
              't' => $t,
              'attachment' => '',
              'createdby' => $id,
              'modifiedby' => $id,
              'modifieddate' => date('Y-m-d H:i:s'),
              'status' => 1,
            );

            $insert = $this->main_mod->insert('user_faip_6', $row);
          }
          $i++;
        }
      }
    }



    //TAB Lampiran
    $sheetObj = $loadexcel->getSheetByName('Lampiran');
    $startFrom = 4;
    $limit = null;
    $i = 1;
    $is_parent = true;
    $parent_lamp = array();
    $parent_temp = 0;
    foreach ($sheetObj->getRowIterator($startFrom, $limit) as $row) {
      $j = 0;
      foreach ($row->getCellIterator() as $cell) {
        $value = $cell->getCalculatedValue();
        if ($j == 0 && $value != '') {
          $is_parent = true;
          $parent_temp++;
        } else if ($j != 0 && $value != '') $is_parent = true;
        else $is_parent = false;

        if ($is_parent) {
          if ($j == 1) $parent_lamp[$parent_temp]['lam_aktifitas'] = $cell->getCalculatedValue();
          else if ($j == 2) $parent_lamp[$parent_temp]['lam_nama'] = $cell->getCalculatedValue();
          else if ($j == 3) $parent_lamp[$parent_temp]['lam_namaproyek'] = $cell->getCalculatedValue();
          else if ($j == 4) $parent_lamp[$parent_temp]['lam_jangka'] = $cell->getCalculatedValue();
          else if ($j == 5) $parent_lamp[$parent_temp]['lam_atasan'] = $cell->getCalculatedValue();
          else if ($j == 6) $parent_lamp[$parent_temp]['lam_uraianproyek'] = $cell->getCalculatedValue();
          else if ($j == 7) $parent_lamp[$parent_temp]['lam_uraiantugas'] = $cell->getCalculatedValue();
          else if ($j == 8) $parent_lamp[$parent_temp]['lam_bagan'] = $cell->getCalculatedValue();
        }

        //if($value!='' && (strpos($value, 'W.') !== false || strpos($value, 'P.') !== false)){
        //$parent_lamp[$parent_temp]['21_kompetensi'][] = $cell->getCalculatedValue();
        //}
        $j++;
      }
      $i++;
    }
    $this->main_mod->delete('user_faip_lam', "faip_id", $faip_id);
    if (is_array($parent_lamp)) {

      $i = 0;
      foreach ($parent_lamp as $parent) {
        $row = array(
          'faip_id' => $faip_id,
          //'parent' => 0,

          'aktifitas' => $parent['lam_aktifitas'],
          'nama' => isset($parent['lam_nama']) ? ($parent['lam_nama'] != '' ? $parent['lam_nama'] : '-') : '-',
          'namaproyek' => $parent['lam_namaproyek'] == '' ? '-' : $parent['lam_namaproyek'],
          'jangka' => $parent['lam_jangka'],
          'atasan' => isset($parent['lam_atasan']) ? ($parent['lam_atasan'] != '' ? $parent['lam_atasan'] : '-') : '-',
          'uraianproyek' => $parent['lam_uraianproyek'],
          'uraiantugas' => $parent['lam_uraiantugas'],
          'bagan' => isset($parent['lam_bagan']) ? $parent['lam_bagan'] : '',
          'createdby' => $id,
          'modifiedby' => $id,
          'modifieddate' => date('Y-m-d H:i:s'),
          'status' => 1,
        );

        $insert_id = $this->main_mod->insert('user_faip_lam', $row);

        $i++;
      }
    }


    //$this->load->view('member/faip_import', $data);
  }




  // ER: Delete duplicates functions which already exist in Member.php 
  // Keliatannya banyak functions yang tadinya ada dibawah ini adalah hasil copy paste file Member.php 
  // Mulai dari function updatewizard() sampai function unregister()



}
