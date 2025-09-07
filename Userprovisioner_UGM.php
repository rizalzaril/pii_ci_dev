<?php
if (! defined('BASEPATH')) exit('No direct script access allowed');

const NUMBER_OF_KTA_DIGIT = 6;
const MEMBER_PHOTO_DIR = FCPATH . 'assets/uploads/';
const MEMBER_PHOTO_DUMMY_DIR = FCPATH . 'assets/uploads/';

/**
 * SETUP:
 * export CIDIR="/var/www/dev"
 * sudo mkdir -p $CIDIR/assets-temp/uploads $CIDIR/assets/uploads/userprovisioner
 * sudo chown -R www-data:www-data $CIDIR/assets-temp/uploads $CIDIR/assets/uploads/userprovisioner
 * sudo chmod -R 775 $CIDIR/assets-temp/uploads $CIDIR/assets/uploads/userprovisioner
 */
class Userprovisioner extends CI_Controller
{

  public function __construct()
  {
    parent::__construct();
    $this->load->helper(array('form', 'url'));
    $this->load->helper('file');

    // Allowed directories for upload
    $this->target_dirs = array(
      4 => 'assets/uploads/userprovisioner/',
      1 => 'assets/',
      2 => 'assets/uploads/',
      3 => 'assets/uploads/faip_manual/'
    );

    // The reason why user use this file upload, since this feature is meant to be an emergency tool,
    // not for day to day job
    $this->reasons = array(
      4 => 'Upload data user yang akan diprovisi',
      3 => 'Revisi/pembaruan penilaian FAIP',
      1 => 'Fitur tidak tersedia di Admin UI',
      2 => 'Fitur tidak tersedia di Member UI'
    );

    // Directory to store backup file (the old file that replaced by file upload)
    $this->BACKUP_DIR = '/var/www/assets-backup/';

    if (!$this->session->userdata('is_admin_login') && $this->session->userdata('admin_username') !== 'sp') {
      redirect(base_url() . 'admin');
      exit;
    }
  }

  public function index()
  {
    $this->load->view(
      'admin/userprovisioning_view',
      array(
        'error' => '',
        'target_dirs' => $this->target_dirs,
        'reasons' => $this->reasons
      )
    );
  }

  public function upload()
  {
    $config['upload_path']          = FCPATH . 'temp/uploads/';
    $config['allowed_types']        = 'csv';
    $config['max_size']             = 10000;

    $this->load->library('upload', $config);

    if (!$this->upload->do_upload('userfile')) {
      $error = array(
        'error' => $this->upload->display_errors() . ' ' . $config['upload_path'],
        'target_dirs' => $this->target_dirs,
        'reasons' => $this->reasons,
        'comment' => $this->input->post('comment')
      );

      $this->load->view('admin/userprovisioning_view', $error);
      return;
    } else {
      $data = array(
        'upload_data' => $this->upload->data(),
        'target_dirs' => $this->target_dirs,
        'reasons' => $this->reasons,
        'comment' => $this->input->post('comment')
      );

      $target_dir = FCPATH . $this->target_dirs[$this->input->post('target_dir')];
      if (!$this->folder_exist($target_dir)) {
        $error = array(
          'error' => 'Upload failed: Target directory does not exist! ' . $target_dir,
          'target_dirs' => $this->target_dirs,
          'reasons' => $this->reasons,
          'comment' => $this->input->post('comment')
        );
        $this->load->view('admin/userprovisioning_view', $error);
        return;
      }
      $file = $target_dir . $this->upload->data('client_name');

      if (file_exists($file)) {
        if ($this->input->post('status') == 1) {
          // User choose to not overwrite if the same file name exist
          $error = array(
            'error' => 'Upload failed: File with the same name is exist!',
            'target_dirs' => $this->target_dirs,
            'reasons' => $this->reasons,
            'comment' => $this->input->post('comment')
          );

          $this->load->view('admin/userprovisioning_view', $error);
          return;
        }

        // Move existing file to backup dir
        elseif ($this->input->post('status') == 2) {
          $date = new \DateTime();
          rename($file, $this->BACKUP_DIR . 'backup-' . $date->format('YmdHis') . '~' . $this->upload->data('client_name'));
        }
      }

      // Move upload file to expected dir
      if (rename($config['upload_path'] . $this->upload->data('file_name'), $file)) {
        // Final location of the file to be shown user
        $data['file_location'] = $file;
      }

      // Log the activity
      $log_data = array(
        'filename' => $this->upload->data('client_name'),
        'uploadedby' => ($this->session->user_id || 0),
        'target_dir' => $this->target_dirs[$this->input->post('target_dir')],
        'status' => $this->input->post('status'),
        'reason' => $this->input->post('reason'),
        'comment' => $this->input->post('comment')
      );

      $this->db->insert('log_upload_files', $log_data);
    }

    $this->load->view('admin/userprovisioning_view', $data);
  }

  function folder_exist($folder)
  {
    // Get canonicalized absolute pathname
    $path = realpath($folder);

    // If it exist, check if it's a directory
    if ($path !== false and is_dir($path)) {
      // Return canonicalized absolute pathname
      return $path;
    }

    // Path/folder does not exist
    return false;
  }

  function extract_name($fullname)
  {

    if (empty($fullname) || trim($fullname) === '') {
      throw new Exception('Fullname cannot be empty');
    }

    // replace multiple spaces become single space
    $fullname = preg_replace('!\s+!', ' ', $fullname);
    $fullname_array = explode(' ', $fullname);

    $firstname = '';
    $lastname = '';

    if (count($fullname_array) == 1) {
      $firstname = $fullname_array[0];
      $lastname = '';
    } else if (count($fullname_array) == 2 && strlen($fullname_array[0]) == 1) {
      $firstname = $fullname_array[0] . ' ' . $fullname_array[1];
      $lastname = '';
    } else if (count($fullname_array) > 2) {
      if (strlen($fullname_array[0]) == 1) {
        $firstname = array_shift($fullname_array) . ' ' . array_shift($fullname_array);
        $lastname = implode(' ', $fullname_array);
      } else {
        $firstname = array_shift($fullname_array);
        $lastname = implode(' ', $fullname_array);
      }
    }

    return array($firstname, $lastname);
  }

  private function gender($gender)
  {
    $retval = '';
    switch (strtoupper($gender)) {
      case 'M':
      case 'MALE':
      case 'L':
      case 'LAKI-LAKI':
      case 'LAKI':
        $retval = 'Male';
        break;
      case 'F':
      case 'FEMALE':
      case 'P':
      case 'PEREMPUAN':
      case 'WANITA':
        $retval = 'Female';
        break;
    }
    return $retval;
  }

  /**
   * @return An array of {user_id and no_kta} if user found in the database or FALSE if user not found
   */
  private function is_user_exist($fullname, $email, $idnty_number, $mobilephone = null, $birthdate = null)
  {
    $name = preg_replace('!\s+!', ' ', $fullname);
    $name = strtoupper($name);
    $this->db->select('u.id as user_id')
      ->from('users u', false)
      ->join('user_profiles up', 'up.id = u.id')
      ->where('1 = 2'); // make it false if no other additional where check
    if (! empty($fullname)) $this->db->or_where("UPPER(CONCAT(up.firstname,' ',up.lastname)) =", $name);
    if (! empty($email)) $this->db->or_where('u.email', $email);
    if (! empty($idnty_number)) $this->db->or_where('up.idcard', $idnty_number);
    if (! empty($mobilephone)) $this->db->or_where('up.mobilephone', $mobilephone);

    //$sql = $this->db->get_compiled_select();
    //echo $sql;


    if (($user_id = $this->db->get()->result()) == TRUE) {
      return $user_id[0]->user_id;
    } else {
      return FALSE;
    }
  }

  /**
   * Fot testing only, checking whether a user is exist
   */
  function check_user()
  {
    $ret = $this->is_user_exist(
      $this->input->get('fullname'),
      $this->input->get('email'),
      $this->input->get('idnumber'),
      $this->input->get('birthdate'),
      $this->input->get('mobilephone')
    );

    if ($ret) {
      return $this->output
        ->set_content_type('application/json')
        ->set_status_header(200)
        ->set_output(
          json_encode([
            'status' => TRUE,
            'message' => "Similar user found. user_id: " . print_r($ret, true)
          ])
        );
    } else {
      return $this->output
        ->set_content_type('application/json')
        ->set_status_header(404)
        ->set_output(
          json_encode([
            'status' => FALSE,
            'message' => "Similar user not found"
          ])
        );
    }
  }

  private function indentity_type($type)
  {
    $retval = '';
    switch (strtoupper($type)) {
      case 'KTP':
      case 'KARTU TANDA PENDUDUK':
      case 'CITIZEN':
        $retval = 'Citizen';
        break;
      case 'PASPOR':
      case 'PASSPORT':
      case 'P':
        $retval = 'Passport';
        break;
      case 'SIM':
      case 'SURAT IZIN MENGEMUDI':
      case 'SURAT IJIN MENGEMUDI':
        $retval = 'Passport';
        break;
    }
    return $retval;
  }

  /**
   * default format 'mm/dd/yy' e.g. '12/31/24'
   */
  private function check_birthdate($date_string, $format = 'd/m/y', $maxage = 90)
  {

    if (($date = DateTime::createFromFormat($format, $date_string)) === FALSE) {
      throw new Exception('Birth date error. Format is not match: ' . $format . ', date_string: ' . $date_string);
    }

    $now = new DateTime();
    $interval = $now->diff($date);
    if ($interval->y > $maxage) {
      throw new Exception('Birth date error. Age is more than ' . $maxage);
    }
    $birthday  =  $date->format('Y-m-d');
    return $birthday;
  }

  private function format_mobilephone($no, $countrycode = '62', $withplus = false)
  {
    $no = preg_replace('/(?!^\+)[^\d]/x', "", $no); //remove non numeric except + in the begining
    switch (true) {
      case (preg_match('#^8\d{4,11}$#', $no)):
        $no = $countrycode . $no;
        break;
      case (preg_match('#^08\d{5,13}$#', $no)):
        $no = $countrycode . substr($no, 1);
        break;
      case (preg_match('#^' . $countrycode . '\d{5,13}$#', $no)):
        $no = $no;
        break;
      case (preg_match('#^\+' . $countrycode . '\d{5,13}$#', $no)):
        $no = substr($no, 1);
        break;
      default:
        throw new Exception('Invalid mobile phone number format');
        break;
    }
    if ($withplus) $no = '+' . $no;
    return $no;
  }

  /**
   * @return user_id/person_id who own the KTA number
   */
  private function is_kta_exist($kta)
  {
    if (empty($kta)) {
      return FALSE;
    }

    $result = $this->db->select('person_id')->from('members')->where('no_kta', $kta);
    if (($user_id = $this->db->get()->result()) == TRUE) {
      return $user_id[0]->person_id;
    } else {
      return FALSE;
    }
  }

  // Tested! 
  private function format_kta($kta)
  {
    $no = preg_replace('/(?!^)[^\d]/x', "", $kta); //remove non numeric
    $no = substr($no, -NUMBER_OF_KTA_DIGIT);
    return $no;
  }

  /**
   * Fot testing only, checking a user is exist
   */
  function check_kta()
  {
    $kta = $this->format_kta($this->input->get('kta'));

    if ($this->is_kta_exist($kta)) {
      return $this->output
        ->set_content_type('application/json')
        ->set_status_header(200)
        ->set_output(
          json_encode([
            'status' => TRUE,
            'message' => "Same KTA is exist in the database. KTA: " . print_r($kta, true)
          ])
        );
    } else {
      return $this->output
        ->set_content_type('application/json')
        ->set_status_header(404)
        ->set_output(
          json_encode([
            'status' => FALSE,
            'message' => "Same KTA is not found. KTA has been search: " . print_r($kta, true)
          ])
        );
    }
  }

  /**
   * Update user's photo by copying from external URL
   * @param prefix Support to store photos in the temporary folder for testing, and update dummy `user_profile` table 
   */
  function update_photo($user_id, $url, $user_modifier = 0, $prefix = '')
  {
    $retval = FALSE;
    if (empty($user_id) || empty($url)) {
      throw new InvalidArgumentException('Cannot update photo. user_id and url cannot be empty');
    }

    $url_path = parse_url($url)['path'];
    $ext      = pathinfo($url_path, PATHINFO_EXTENSION);
    $filename = time() . "_PHOTO_" . $user_id . "." . $ext;
    $fileloc  = MEMBER_PHOTO_DIR . $filename;
    if (! empty($prefix)) {
      $fileloc  = MEMBER_PHOTO_DUMMY_DIR . $filename;
    }


    if (! copy($url, $fileloc)) {
      $errors = error_get_last();
      throw new Exception('Cannot copy photo/image from url: ' . $url . ' as file: ' . $filename . '. Message: ' . $errors['message']);
    } else {
      $this->db
        ->set('photo', $filename)
        ->set('modifiedby', $user_modifier)
        ->set('modifieddate', date('Y-m-d H:i:s')) // Should be autoupdated by MySQL)
        ->where('user_id', $user_id)
        ->update($prefix . 'user_profiles');

      $retval = $filename;
    }
    log_message('debug', '[SIMPONI] ' . __FUNCTION__ . ' - user_id: ' . $user_id . ', filename: ' . $retval);
    return $retval;
  }

  private function generate_password()
  {
    $new_pwd = generate_random_password();
    //$encypt_pwd = 
    return $new_pwd;
  }

  /**
   * Main function to start processing CSV file
   * For test: https://simponi-dev.pii.or.id/index.php/admin/userprovisioner/process_csv/pengajuan_kta_feb2024_csvforexcel_enter_char_removed_test1row.csv
   * https://simponi-dev.pii.or.id/index.php/admin/userprovisioner/process_csv/pengajuan_kta_feb2024_batch13_ugm.csv
   */
  function process_csv($filename)
  {

    //error_reporting(0);

    if (empty($filename)) {
      $filename = $this->input->get('file');
    }

    // Copy just for defined_vars() dump
    $MEMBER_PHOTO_DUMMY_DIR = MEMBER_PHOTO_DUMMY_DIR;
    $MEMBER_PHOTO_DIR = MEMBER_PHOTO_DIR;
    $NUMBER_OF_KTA_DIGIT = NUMBER_OF_KTA_DIGIT;


    $KOLEKTIF_IDS = array(
      //'744', //PSPPI Kehutanan UGM
      '628'
    );
    $CREATOR_MODIFICATOR = '123456';
    $KOLEKTIF_BATCH_INSERT_ID = '500';
    $KOLEKTIF_BATCH_INSERT_NAME = 'UGM ANGKATAN 15A JUNI 2025';

    $__USE_DEFAULT_PASSWORD__ = TRUE;
    $DEFAULT_PASSWORD = '$2a$08\$gHInWtYruHTiNCspkx6BDO0Lhf.x6Ak9nbUcV.0B6rueLG9.wJcHO' . '_DISABLED'; //Sembunyi11
    $DEFAULT_COUNTRY_NAME = 'Indonesia';

    // Set to FALSE since $this->db->insert_id() is always return 1 when using transaction.
    // Is it a bug in CI?
    $__USE_TRANSACTION__ = FALSE;

    $CSV_SEPARATOR        = ';';
    $CSV_ENCLOSURE        = '';
    $CSV_MAX_CHARS        = null; // Unlimited number of chars in a line
    $CSV_READ_MAX_LINES   = 220;
    $CSV_START_DATA_ROW   = 0;
    $CSV_MIN_COLUMN_COUNT = 18;

    $CSV_DATE_FORMAT = 'yyyy-mm-dd'; // 'Y-m-d';
    $CSV_DIR = FCPATH . $this->target_dirs['4'];

    $__USE_DUMMY_TABLES__ = TRUE;
    $TABLE_PREFIX_FOR_DUMMY = 'dummy_';
    $prefix = '';
    if ($__USE_DUMMY_TABLES__) {
      $prefix = $TABLE_PREFIX_FOR_DUMMY;
    }
    $TABLE_USERS        = $prefix . 'users';
    $TABLE_USER_PROFILE = $prefix . 'user_profiles';
    $TABLE_USER_ADDRESS = $prefix . 'user_address';
    $TABLE_USER_EXP     = $prefix . 'user_exp';
    $table_list = array($TABLE_USERS, $TABLE_USER_PROFILE, $TABLE_USER_ADDRESS, $TABLE_USER_EXP);


    $fhandle = fopen($CSV_DIR . $filename, "r");
    $rownum = 0;
    $rowrum_processed = 0;
    $result_list = array();
    $success_count = 0;
    $rownum_messages = array();

    // Check if the csv file exist, if no the return an error (404)
    if (! file_exists($CSV_DIR . $filename)) {
      return $this->output
        ->set_content_type('application/json')
        ->set_status_header(404)
        ->set_output(
          json_encode([
            'status' => FALSE,
            'message' => 'File does not exist ' . $filename,
            'result' =>  $result_list
          ])
        );
    }
    log_message('debug', '[SIMPONI] ' . __FUNCTION__ . ' - Processing file: ' . $CSV_DIR . $filename);

    // Log every batch process into a single file.
    $log_file = $CSV_DIR . $prefix . 'log_' . date('Ymd-His') . '.log';

    $defined_vars = print_r(get_defined_vars(), true);
    if (!write_file($log_file, $defined_vars . "\n", 'a+')) {
      throw new Exception('Unable to write the batch userprovisioning log to a file.');
    }

    // Create dummy tables for insert with the same structure from original tables
    if ($__USE_DUMMY_TABLES__) {
      log_message('debug', '[SIMPONI] ' . __FUNCTION__ . ' - Creating dummy/test tables.');

      foreach ($table_list as $tablename) {
        $tablename_orig = preg_replace('/^' . $TABLE_PREFIX_FOR_DUMMY . '/', '', $tablename);
        $this->db->query('DROP TABLE IF EXISTS `' . $tablename . '`');
        $this->db->query('CREATE TABLE `' . $tablename . '` LIKE `' . $tablename_orig . '`;');
      }

      // Clean up TEMPORARY photos directory
      log_message('debug', '[SIMPONI] ' . __FUNCTION__ . ' - Clean up dummy/test folder.');
      @array_map('unlink', array_filter((array) glob(MEMBER_PHOTO_DUMMY_DIR . "*")));
    }

    if ($fhandle !== FALSE) {

      while (($getData = fgetcsv($fhandle, $CSV_MAX_CHARS, $CSV_SEPARATOR, $CSV_ENCLOSURE)) !== FALSE) {

        $rownum++;

        // Ignore header if any
        if ($rownum <= $CSV_START_DATA_ROW) {
          continue;
        }

        //Ignore blank lines
        if ($getData === array(null)) {
          continue;
        }

        if ($rownum === $CSV_READ_MAX_LINES) {
          $rownum_messages[$rownum][] = 'ERROR - Stop processing the next line if any! Limit of lines has reached.';

          $result_list[] = array(
            "row_num" => $rownum,
            "message" => 'ERROR - Max number of lines that can be processed is exceeded: ' . $CSV_READ_MAX_LINES,
            "details" => $rownum_messages[$rownum]
          );
          break;
        }

        if (($col_num = count($getData)) < ($CSV_MIN_COLUMN_COUNT + 1)) {
          $rownum_messages[$rownum][] = 'ERROR - Stop processing the next line if any! Number of columns (array size) is ' . $col_num;

          $result_list[] = array(
            "row_num" => $rownum,
            "message" => 'ERROR - Failed to read all columns in a line, min_column: ' . $CSV_MIN_COLUMN_COUNT,
            "details" => $rownum_messages[$rownum]
          );
          break;
        }

        log_message('debug', '[SIMPONI] ' . __FUNCTION__ . ' - Processing row_num: ' . $rownum . ' ' . print_r($getData, true));

        try {
          $rowrum_processed++;

          $username = ''; // Default value for non member is empty
          $password = ($__USE_DEFAULT_PASSWORD__) ? $DEFAULT_PASSWORD : $this->generate_password();
          $email = $getData[6];

          $fullname       = $getData[4];
          $firstname      = $this->extract_name($fullname)[0];
          $lastname       = $this->extract_name($fullname)[1];
          $gender         = $this->gender($getData[7]);
          $idnty_type     = $this->indentity_type($getData[14]);
          $idnty_number   = $getData[15];
          $birthplace     = $getData[8];
          $birthdate      = $getData[9];  // $this->check_birthdate($getData[9], $CSV_DATE_FORMAT); // Perubahan by Ipur
          $mobilephone    = $this->format_mobilephone($getData[16]);
          $va             = '89699' . $getData[1] . $getData[2] . $getData[12]; // Will be generated later manually by admin using set_active() / Perubahan by Ipur
          $kolektif_batch = $KOLEKTIF_BATCH_INSERT_ID;
          $kolektif_ids   = implode(',', $KOLEKTIF_IDS);
          $createdby      = $CREATOR_MODIFICATOR;
          $modifiedby     = $CREATOR_MODIFICATOR;

          $addresstype = 1; // Home address
          $address     = $getData[17];
          $city        = $getData[18];
          $province    = $getData[2];
          $zipcode     = $getData[19];
          $homephone   = $getData[20];

          $lembaga_nama    = $getData[23];
          $lembaga_jabatan = $getData[24];
          $present_job     = 1;
          $lembaga_alamat  = $getData[26];
          $lembaga_kota    = $getData[27];
          $lembaga_prov    = '';
          $lembaga_negara  = $DEFAULT_COUNTRY_NAME;
          $lembaga_kodepos = $getData[28];
          $lembaga_phone   = $getData[29];

          $photo_link = $getData[32];


          $existing_user_id = $this->is_user_exist($fullname, $email, $idnty_number, $mobilephone);

          $no_kta = $getData[12];
          $no_kta = $this->format_kta($no_kta);

          // KTA is exist in the database
          if (! empty($no_kta) && ($user_id_with_kta = $this->is_kta_exist($no_kta)) !== FALSE) {

            // id with similar data and the id who own the KTA is match
            if ($existing_user_id === $user_id_with_kta) {

              // Update kolektif_ids
              foreach ($KOLEKTIF_IDS as $kolektif_id) {
                $str_sql = "update user_profiles set kolektif_ids = case "
                  . "when (id = ${existing_user_id} AND (kolektif_ids is null OR kolektif_ids = '')) THEN '${kolektif_id}' "
                  . "when (id = ${existing_user_id} AND (find_in_set(${kolektif_id},kolektif_ids) > 0)) THEN  kolektif_ids "
                  . "when (id = ${existing_user_id} AND (find_in_set(${kolektif_id},kolektif_ids) = 0)) then CONCAT(kolektif_ids,',','${kolektif_id}') "
                  . "ELSE kolektif_ids "
                  . "END";

                // No need code below. String substitution work with ${var} Cool!
                //$str_sql = strtr($str_sql, array('${existing_user_id}' => $existing_user_id, '${kolektif_id}' => $kolektif_id));

                $this->db->query($str_sql);
              }

              $rownum_messages[$rownum][] = 'SUCCESS - Update user to have kolektif_ids: ' . implode(',', $KOLEKTIF_IDS) . ', id: ' . $existing_user_id;
            } else {
              $rownum_messages[$rownum][] = 'WARNING - Person who has same KTA seems like different person. Please check manually.';
            }
            throw new Exception('KTA number is already exist. KTA: ' . $no_kta . ', user_id: ' . $user_id_with_kta);
          }

          if ($existing_user_id !== FALSE) {
            throw new Exception("Similar user already exist. existing_user_id: ${existing_user_id}, fullname: '${fullname}', email: ${email}, ktp: ${idnty_number}, mobile: ${mobilephone}");
          }

          // Start database insert
          if ($__USE_TRANSACTION__) {
            $this->db->trans_start();
          }

          $user_data = array(
            'username' => $no_kta,  // $username, Perubahan by Ipur
            'password' => $password,
            'email' => $email,
          );

          if ($this->db->insert($TABLE_USERS, $user_data)) {
            $user_id = $this->db->insert_id();
            $rownum_messages[$rownum][] = 'SUCCESS - Insert into table: ' . $TABLE_USERS . ', id: ' . $user_id;
          }

          $uprofile_data = array(
            'user_id' => $user_id,
            'va' => $va,
            'firstname' => $firstname,
            'lastname' => $lastname,
            'gender' => $gender,
            'idtype' => $idnty_type,
            'idcard' => $idnty_number,
            'birthplace' => $birthplace,
            'dob' => $birthdate,
            'mobilephone' => $mobilephone,
            'kolektif_ids' => $kolektif_ids,
            'kolektif_name_id' => $kolektif_batch,
            'createdby' => $createdby,
            'modifiedby' => $modifiedby,
          );

          if ($this->db->insert($TABLE_USER_PROFILE, $uprofile_data)) {
            $uprofile_id = $this->db->insert_id();
            $rownum_messages[$rownum][] = 'SUCCESS - Insert into table: ' . $TABLE_USER_PROFILE . ', id: ' . $uprofile_id;
          }


          $addr_data = array(
            'user_id' => $user_id,
            'addresstype' => $addresstype,
            'address' => $address,
            'city' => $city,
            'province' => $province,
            'zipcode' => $zipcode,
            'phone' => $homephone,
            'createdby' => $createdby,
            'modifiedby' => $modifiedby
          );

          if ($this->db->insert($TABLE_USER_ADDRESS, $addr_data)) {
            $addr_id = $this->db->insert_id();
            $rownum_messages[$rownum][] = 'SUCCESS - Insert into table: ' . $TABLE_USER_ADDRESS . ', id: ' . $addr_id;
          }

          $experince_data = array(
            'user_id' => $user_id,
            'company' => $lembaga_nama,
            'title' => $lembaga_jabatan,
            'location' => $lembaga_alamat,
            'provinsi' => $lembaga_prov,
            'negara' => $lembaga_negara,
            'is_present' => $present_job,
            'createdby' => $createdby,
          );

          if ($this->db->insert($TABLE_USER_EXP, $experince_data)) {
            $exprience_id = $this->db->insert_id();
            $rownum_messages[$rownum][] = 'SUCCESS - Insert into table: ' . $TABLE_USER_EXP . ', id: ' . $exprience_id;
          }

          // Commit the transaction
          if ($__USE_TRANSACTION__) {
            $this->db->trans_complete();
          }

          if ($__USE_TRANSACTION__ && $this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $error = $this->db->error();
            throw new Exception('[SIMPONI] ' . __FUNCTION__ . ' Failed to commit transaction while provision user: ' . $email . ', row_number: ' . $rownum . ' ' . $error);
          } else {
            $success_count++;

            $tbl_prefix = ($__USE_DUMMY_TABLES__) ? $TABLE_PREFIX_FOR_DUMMY : '';
            if (($photofile = $this->update_photo($user_id, $photo_link, $modifiedby, $tbl_prefix)) !== FALSE) {
              $rownum_messages[$rownum][] = 'SUCCESS - Copy & update the user\'s photo. Filename: ' . $photofile;
            }

            $result_list[] = array(
              "row_num" => $rownum,
              "message" => 'SUCCESS - All insert user_id: ' . $user_id . ', email: ' . $email . ', address_id: ' . $addr_id . ', experice_id: ' . $exprience_id,
              "details" => $rownum_messages[$rownum]
            );
          }
        } catch (Exception $t) {
          log_message('error', '[SIMPONI] ' . __FUNCTION__ . ' - ' .  $t->getMessage());
          $rownum_messages[$rownum][] = 'ERROR - ' .  $t->getMessage();
          $result_list[] = array(
            "row_no" => $rownum,
            "message" => 'ERROR - Last error message: ' . $t->getMessage(),
            "details" => $rownum_messages[$rownum]
          );
        }
      } // end while loop
    }

    fclose($fhandle);


    if (!write_file($log_file, json_encode($result_list, JSON_PRETTY_PRINT) . "\n", 'a+')) {
      throw new Exception('Unable to write the batch userprovisioning log to a file.');
    }

    if ($success_count == 0) {
      return $this->output
        ->set_content_type('application/json')
        ->set_status_header(404)
        ->set_output(
          json_encode([
            'status' => FALSE,
            'message' => 'Failed processing ALL {' . $rowrum_processed . '} rows in file ' . $filename,
            'result' =>  $result_list
          ])
        );
    } else {
      return $this->output
        ->set_content_type('application/json')
        ->set_status_header(200)
        ->set_output(
          json_encode([
            'status' => TRUE,
            'message' => 'Successfuly processing ALL {' . $success_count . '} rows in file ' . $filename,
            'result' =>  $result_list
          ])
        );
    }
  }
}
