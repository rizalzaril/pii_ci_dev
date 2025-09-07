<?php
if (! defined('BASEPATH')) exit('No direct script access allowed');

require_once(APPPATH . '/libraries/FileDownloader.php');

const NUMBER_OF_KTA_DIGIT = 6;
const MEMBER_PHOTO_DIR = FCPATH . 'assets/uploads/';
const MEMBER_PHOTO_DUMMY_DIR = FCPATH . 'assets-temp/uploads/';
const MEMBER_IDCARD_DIR = FCPATH . 'assets/uploads/';
const MEMBER_IDCARD_DUMMY_DIR = FCPATH . 'assets-temp/uploads/';
const MEMBER_IJAZAH_DIR = FCPATH . 'assets/uploads/';
const MEMBER_IJASAH_DUMMY_DIR = FCPATH . 'assets-temp/uploads/';

const CSV_DATE_FORMAT = 'd/m/Y';
//const CSV_DATE_FORMAT = 'Y-m-d';


/**
 * SETUP:
 * export CIDIR="/var/www/dev"
 * sudo mkdir -p $CIDIR/assets-temp/uploads $CIDIR/assets/uploads/userprovisioner
 * sudo chown -R www-data:www-data $CIDIR/assets-temp/uploads $CIDIR/assets/uploads/userprovisioner
 * sudo chmod -R 775 $CIDIR/assets-temp/uploads $CIDIR/assets/uploads/userprovisioner
 */

/**
 * Convert Excel column letter to zero-based index
 * A -> 0, B -> 1, ..., Z -> 25, AA -> 26, AB -> 27, ...
 */
function excelColumnToIndex($column)
{
	$column = strtoupper($column);
	$length = strlen($column);
	$index = 0;

	for ($i = 0; $i < $length; $i++) {
		$index *= 26;
		$index += ord($column[$i]) - ord('A') + 1;
	}

	return $index - 1; // zero-based index
}


class Userprovisioner extends CI_Controller
{
	// List of directory to store upload file
	protected $target_dirs;
	protected $reasons;
	protected $BACKUP_DIR;

	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('form', 'url', 'utility'));
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
		$this->BACKUP_DIR = '/var/www/assets-temp/';

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
		$config['upload_path']   = FCPATH . 'temp/uploads/';
		$config['allowed_types'] = 'csv';
		$config['max_size']      = 10000;

		$this->load->library('upload', $config);

		if (!$this->upload->do_upload('userfile')) {
			$error = array(
				'error'       => $this->upload->display_errors() . ' ' . $config['upload_path'],
				'target_dirs' => $this->target_dirs,
				'reasons'     => $this->reasons,
				'comment'     => $this->input->post('comment')
			);

			$this->load->view('admin/userprovisioning_view', $error);
			return;
		}

		$data = array(
			'upload_data' => $this->upload->data(),
			'target_dirs' => $this->target_dirs,
			'reasons'     => $this->reasons,
			'comment'     => $this->input->post('comment')
		);

		$target_dir = FCPATH . $this->target_dirs[$this->input->post('target_dir')];

		if (!$this->folder_exist($target_dir)) {
			$error = array(
				'error'       => 'Upload failed: Target directory does not exist! ' . $target_dir,
				'target_dirs' => $this->target_dirs,
				'reasons'     => $this->reasons,
				'comment'     => $this->input->post('comment')
			);
			$this->load->view('admin/userprovisioning_view', $error);
			return;
		}

		$file = $target_dir . $this->upload->data('client_name');

		// Move upload file to expected dir
		if (!rename($config['upload_path'] . $this->upload->data('file_name'), $file)) {
			$error = array(
				'error'       => 'Failed to move uploaded file to target directory!',
				'target_dirs' => $this->target_dirs,
				'reasons'     => $this->reasons,
				'comment'     => $this->input->post('comment')
			);
			$this->load->view('admin/userprovisioning_view', $error);
			return;
		}

		$data['file_location'] = $file;

		// Log the activity
		$log_data = array(
			'filename'   => $this->upload->data('client_name'),
			'uploadedby' => isset($this->session->user_id) ? $this->session->user_id : 0,
			'target_dir' => $this->target_dirs[$this->input->post('target_dir')],
			'status'     => $this->input->post('status'),
			'reason'     => $this->input->post('reason'),
			'comment'    => $this->input->post('comment')
		);

		$this->db->insert('log_upload_files', $log_data);

		// Proses CSV
		$this->process_csv($data['upload_data']['client_name']);

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
		} else if (count($fullname_array) >= 2) {
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

	protected function gender($gender)
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
	 * @return An array of {user} from `v_account` if user found in the database or FALSE if user not found
	 */
	protected function is_user_exist($fullname, $email, $idnty_number, $mobilephone = null, $birthdate = null, $min_match = 2)
	{
		$name = preg_replace('!\s+!', ' ', $fullname);
		$name = strtoupper($name);

		// Format date for MySQL
		$dob = $birthdate->format('Y-m-d');

		$fullname     = mysql_escape_char($fullname);
		$email        = mysql_escape_char($email);
		$idnty_number = mysql_escape_char($idnty_number);
		$mobilephone  = mysql_escape_char($mobilephone);

		$user = $this->db
			->where(
				"(UPPER(CONCAT(firstname,' ',lastname)) = '${fullname}') + (LOWER(email) = LOWER('${email}')) + " .
					"(IFNULL(idcard, 'randomxyz') = '${idnty_number}') + (IFNULL(mobilephone,'randomxyz') = '${mobilephone}') + " .
					"(dob = '${dob}') >= ${min_match}"
			)
			->get('v_account')
			->result_array();


		//$sql = $this->db->get_compiled_select();
		//echo $sql;

		if ($user) {
			return $user[0];
		} else {
			return FALSE;
		}
	}

	/**
	 * @return An array of {user_id} if user found in the database or FALSE if user not found
	 */
	protected function is_user_exist_old1($fullname, $email, $idnty_number, $mobilephone = null, $birthdate = null)
	{
		$name = preg_replace('!\s+!', ' ', $fullname);
		$name = strtoupper($name);

		// TODO: FIX using matching mechanism: match at lest 2 out of 4
		// WHERE (FirstName = ?) + (LastName = ?) + (... = ?) > 2
		// this https://stackoverflow.com/questions/7109375/mysql-matching-2-out-of-5-fields
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
						'message' => "Similar user found. user: " . print_r($ret, true)
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

	protected function indentity_type($type)
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
	//  protected function check_birthdate($date_string, $format = 'd/m/Y', $maxage = 90) {
	protected function check_birthdate($date_string, $format = 'Y-m-d', $maxage = 90)
	{

		/*
        if ( ($date = DateTime::createFromFormat($format, $date_string)) === FALSE ) {
            throw new Exception('Birth date error. Format is not match: '.$format.', date_string: '.$date_string);
        }

        $now = new DateTime();
        $interval = $now->diff($date);
        if ( $interval->y > $maxage) {
            throw new Exception('Birth date error. Age is more than '.$maxage);
        }
*/
		//	$now = new DateTime();
		//	$date = DateTime::createFromFormat($format, $date_string) ;
		//       $birthday  =  $date->format(CSV_DATE_FORMAT);
		$birthday  =  '0000-00-00';
		return $birthday;
	}


	protected function format_mobilephone($no, $countrycode = '62', $withplus = false)
	{

		//--------------------------------
		/*
        $no = preg_replace('/(?!^\+)[^\d]/x', "", $no); //remove non numeric except + in the begining
        switch (true) {
            case (preg_match('#^8\d{4,11}$#', $no)):
                $no = $countrycode . $no;
                break;
            case (preg_match('#^08\d{5,13}$#', $no)):
                $no = $countrycode . substr($no, 1);
                break;
            case (preg_match('#^'.$countrycode.'\d{5,13}$#', $no)):
                $no = $no;
                break;
            case (preg_match('#^\+'.$countrycode.'\d{5,13}$#', $no)):
                $no = substr($no, 1);
                break;
            default:
                throw new Exception('Invalid mobile phone number format');
                break;
        }

*/
		//-----------------------------------------------------------------------------

		if ($withplus) $no = '+' . $no;
		return $no;
	}

	/**
	 * @return user_id/person_id who own the KTA number
	 */
	protected function is_kta_exist($kta)
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

	/**
	 * Format KTA. Remove non numeric, ussually like: dot, space, dash
	 * Get only 6 digit (NUMBER_OF_KTA_DIGIT) from the input
	 */
	protected function format_kta($kta)
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

		// Feature flag - the old method of download copy() does not work when download a file from google drive
		$NEW_DOWNLOAD_METHOD = true;
		if ($NEW_DOWNLOAD_METHOD) {
			try {
				//Download file using Curl library
				$dl = new Filedownloader($url, "cookies.txt");
				$content = $dl->download();

				if (empty($content)) {
					throw new Exception('Cannot copy idcard/image from url: ' . $url . ' as file: ' . $filename . '. Reponse 0 content from the url');
				}

				// Fix extension file name if it empty, use extension file name from the source
				$saveName = 'nofilename.ext';
				if ($header = $dl->getHeader('Content-disposition')) {
					if (preg_match('/filename="?(.*)"?/', $header, $matches)) {
						$saveName = str_replace('"', '', $matches[1]);
					}
				}
				if (empty($ext)) {
					$ext      = pathinfo($saveName, PATHINFO_EXTENSION);
					$fileloc  = $fileloc . $ext;
				}
				file_put_contents($fileloc, $content);

				$new_ext = is_file_image($fileloc);
				if ($ext == 'ext' && $new_ext !== FALSE) {
					rename($fileloc, pathinfo($fileloc, PATHINFO_FILENAME) . $new_ext);
				}

				if (file_exists($fileloc)) {
					$this->db
						->set('photo', basename($fileloc))
						->set('modifiedby', $user_modifier)
						->set('modifieddate', date('Y-m-d H:i:s')) // Should be autoupdated by MySQL)
						->where('user_id', $user_id)
						->update($prefix . 'user_profiles');

					$retval = basename($fileloc);
				}
			} catch (Throwable $t) {
				throw new Exception('Cannot copy photo/image from url: ' . $url . ' as file: ' . $filename . '. Message: ' . $t->getMessage());
			}

			// Old method - does works for UGM case (file not in Google Drive)
		} else {

			if (! @copy($url, $fileloc)) {
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
		}

		log_message('debug', '[SIMPONI] ' . __FUNCTION__ . ' - user_id: ' . $user_id . ', filename: ' . $retval);
		return $retval;
	}

	function update_idcard($user_id, $url, $user_modifier = 0, $prefix = '')
	{
		$retval = FALSE;
		if (empty($user_id) || empty($url)) {
			throw new InvalidArgumentException('Cannot update idcard. user_id and url cannot be empty');
		}

		$url_path = parse_url($url)['path'];
		$ext      = pathinfo($url_path, PATHINFO_EXTENSION);
		$filename = time() . "_KTP_" . $user_id . "." . $ext;
		$fileloc  = MEMBER_IDCARD_DIR . $filename;
		if (! empty($prefix)) {
			$fileloc  = MEMBER_IDCARD_DUMMY_DIR . $filename;
		}


		// Feature flag - the old method of download copy() does not work when download a file from google drive
		$NEW_DOWNLOAD_METHOD = true;
		if ($NEW_DOWNLOAD_METHOD) {
			try {
				//Download file using Curl library
				$dl = new Filedownloader($url, "cookies.txt");
				$content = $dl->download();

				if (empty($content)) {
					throw new Exception('Cannot copy idcard/image from url: ' . $url . ' as file: ' . $filename . '. Reponse 0 content from the url');
				}

				// Fix extension file name if it empty, use extension file name from the source
				$saveName = 'nofilename.ext';
				if ($header = $dl->getHeader('Content-disposition')) {
					if (preg_match('/filename="?(.*)"?/', $header, $matches)) {
						$saveName = str_replace('"', '', $matches[1]);
					}
				}
				if (empty($ext)) {
					$ext      = pathinfo($saveName, PATHINFO_EXTENSION);
					$fileloc  = $fileloc . $ext;
				}
				file_put_contents($fileloc, $content);

				$new_ext = @is_file_image($fileloc);
				if ($ext == 'ext' && $new_ext !== FALSE) {
					rename($fileloc, pathinfo($fileloc, PATHINFO_FILENAME) . $new_ext);
				}

				if (file_exists($fileloc)) {
					$this->db
						->set('id_file', basename($fileloc))
						->set('modifiedby', $user_modifier)
						->set('modifieddate', date('Y-m-d H:i:s')) // Should be autoupdated by MySQL)
						->where('user_id', $user_id)
						->update($prefix . 'user_profiles');

					$retval = basename($fileloc);
				}
			} catch (Throwable $t) {
				throw new Exception('Cannot copy idcard/image from url: ' . $url . ' as file: ' . $filename . '. Message: ' . $t->getMessage());
			}

			// Old method - does works for UGM case (file not in Google Drive)
		} else {

			if (! @copy($url, $fileloc)) {
				$errors = error_get_last();
				throw new Exception('Cannot copy idcard/image from url: ' . $url . ' as file: ' . $filename . '. Message: ' . $errors['message']);
			} else {
				$this->db
					->set('id_file', $filename)
					->set('modifiedby', $user_modifier)
					->set('modifieddate', date('Y-m-d H:i:s')) // Should be autoupdated by MySQL)
					->where('user_id', $user_id)
					->update($prefix . 'user_profiles');

				$retval = $filename;
			}
		}
		log_message('debug', '[SIMPONI] ' . __FUNCTION__ . ' - user_id: ' . $user_id . ', filename: ' . $retval);
		return $retval;
	}

	function update_ijazah($user_id, $user_edu_id, $url, $user_modifier = 0, $prefix = '')
	{
		$retval = FALSE;
		if (empty($user_id) || empty($url) || empty($user_edu_id)) {
			throw new InvalidArgumentException('Cannot update ijasah. user_id and url cannot be empty');
		}

		$url_path = parse_url($url)['path'];
		$ext      = pathinfo($url_path, PATHINFO_EXTENSION);
		$filename = time() . "_EDU_" . $user_id . "." . $ext;
		$fileloc  = MEMBER_IJAZAH_DIR . $filename;
		if (! empty($prefix)) {
			$fileloc  = MEMBER_IDCARD_DUMMY_DIR . $filename;
		}



		// Feature flag - the old method of download copy() does not work when download a file from google drive
		$NEW_DOWNLOAD_METHOD = true;
		if ($NEW_DOWNLOAD_METHOD) {
			try {
				//Download file using Curl library
				$dl = new Filedownloader($url, "cookies.txt");
				$content = $dl->download();

				if (empty($content)) {
					throw new Exception('Cannot copy idcard/image from url: ' . $url . ' as file: ' . $filename . '. Reponse 0 content from the url');
				}

				// Fix extension file name if it empty, use extension file name from the source
				$saveName = 'nofilename.ext';
				if ($header = $dl->getHeader('Content-disposition')) {
					if (preg_match('/filename="?(.*)"?/', $header, $matches)) {
						$saveName = str_replace('"', '', $matches[1]);
					}
				}
				if (empty($ext)) {
					$ext      = pathinfo($saveName, PATHINFO_EXTENSION);
					$fileloc  = $fileloc . $ext;
				}
				file_put_contents($fileloc, $content);

				$new_ext = @is_file_image($fileloc);
				if ($ext == 'ext' && $new_ext !== FALSE) {
					rename($fileloc, pathinfo($fileloc, PATHINFO_FILENAME) . $new_ext);
				}

				if (file_exists($fileloc)) {
					$this->db
						->set('attachment', basename($fileloc))
						->set('modifiedby', $user_modifier)
						->set('modifieddate', date('Y-m-d H:i:s')) // Should be autoupdated by MySQL)
						->where('user_id', $user_id)
						->where('id', $user_edu_id)
						->update($prefix . 'user_edu');

					$retval = basename($fileloc);
				}
			} catch (Throwable $t) {
				throw new Exception('Cannot copy ijasah/image from url: ' . $url . ' as file: ' . $filename . '. Message: ' . $t->getMessage());
			}

			// Old method - does works for UGM case (file not in Google Drive)
		} else {

			if (! @copy($url, $fileloc)) {
				$errors = error_get_last();
				throw new Exception('Cannot copy ijasah/image from url: ' . $url . ' as file: ' . $filename . '. Message: ' . $errors['message']);
			} else {
				$this->db
					->set('attachment', $filename)
					->set('modifiedby', $user_modifier)
					->set('modifieddate', date('Y-m-d H:i:s')) // Should be autoupdated by MySQL)
					->where('user_id', $user_id)
					->where('id', $user_edu_id)
					->update($prefix . 'user_edu');

				$retval = $filename;
			}
		}

		log_message('debug', '[SIMPONI] ' . __FUNCTION__ . ' - user_id: ' . $user_id . ', filename: ' . $retval);
		return $retval;
	}


	protected function generate_password()
	{
		$new_pwd = generate_random_password();
		//$encypt_pwd =
		return $new_pwd;
	}

	/**
	 * Trim right and left, make first letter uppercase and remove unwanted chars e.g. numeric
	 */
	protected function fortmat_name($name)
	{
		return ucwords(preg_replace('/\s*(?:[\d_]|[^\w\s])+/', '', strtolower(rtrim(ltrim($name)))));
	}


	// Fungsi bantu untuk membaca CSV aman
	private function csv_get($row, $index, $default = null)
	{
		return isset($row[$index]) ? $row[$index] : $default;
	}





	/**
	 * Main function to start processing CSV file
	 * For test:
	 * Folder: cd /var/www/dev/assets/uploads/userprovisioner/
	 * https://simponi-dev.pii.or.id/index.php/admin/userprovisioner/process_csv/pengajuan_kta_feb2024_csvforexcel_enter_char_removed_test1row.csv
	 * https://simponi-dev.pii.or.id/index.php/admin/userprovisioner/process_csv/pengajuan_kta_feb2024_batch13_ugm.csv
	 * Pendaftaran_Grup_Kolektif_Keanggotaan_PII_Angkatan_1.csv
	 */
	public function process_csv($filename)
	{
		if (empty($filename)) {
			$filename = $this->input->get('file');
		}

		$KOLEKTIF_IDS = [690, 745, 682, 500, 749];
		$CREATOR_MODIFICATOR = $this->session->userdata('admin_id');
		$KOLEKTIF_BATCH_INSERT_ID = '512';
		$KOLEKTIF_BATCH_INSERT_NAME = 'UGM ANGKATAN 15A JUNI 2025';
		$SCHOOL_NAME = 'UNIVERSITAS GAJAH MADA';
		$SCHOOL_DEGREE = 'S1';
		$__USE_DEFAULT_PASSWORD__ = TRUE;
		$DEFAULT_PASSWORD = '$2y$10$v6zVno3AVAdMJ3Bg1r.Mc.9zDyfkDnqAGRxXXBNZzmMKXGgAiw2YS';
		$DEFAULT_COUNTRY_NAME = 'Indonesia';
		$__USE_TRANSACTION__ = FALSE;
		$CSV_DIR = FCPATH . $this->target_dirs['4'];

		if (!file_exists($CSV_DIR . $filename)) {
			return $this->output
				->set_content_type('application/json')
				->set_status_header(404)
				->set_output(json_encode([
					'status' => FALSE,
					'message' => 'File does not exist ' . $filename,
					'result' => []
				]));
		}

		$fhandle = fopen($CSV_DIR . $filename, "r");
		$first_line = fgets($fhandle);
		$CSV_SEPARATOR = (substr_count($first_line, ';') > substr_count($first_line, ',')) ? ';' : ',';
		$CSV_MAX_CHARS = null;
		$CSV_READ_MAX_LINES = 220;
		$CSV_START_DATA_ROW = 0;
		$CSV_MIN_COLUMN_COUNT = 18;
		$CSV_DATE_FORMAT = CSV_DATE_FORMAT;

		$__USE_DUMMY_TABLES__ = FALSE;
		$TABLE_PREFIX_FOR_DUMMY = 'dummy_';
		$prefix = $__USE_DUMMY_TABLES__ ? $TABLE_PREFIX_FOR_DUMMY : '';

		$TABLE_USERS        = $prefix . 'users';
		$TABLE_USER_PROFILE = $prefix . 'user_profiles';
		$TABLE_USER_ADDRESS = $prefix . 'user_address';
		$TABLE_USER_EXP     = $prefix . 'user_exp';
		$TABLE_USER_EDU     = $prefix . 'user_edu';
		$TABLE_MEMBERS      = $prefix . 'members';
		$TABLE_USER_TRANSFER = $prefix . 'user_transfer';
		$table_list = [$TABLE_USERS, $TABLE_USER_PROFILE, $TABLE_USER_ADDRESS, $TABLE_USER_EXP, $TABLE_USER_EDU, $TABLE_MEMBERS, $TABLE_USER_TRANSFER];

		$rownum = 0;
		$rowrum_processed = 0;
		$result_list = [];
		$success_count = 0;
		$rownum_messages = [];

		// Create dummy tables if needed
		if ($__USE_DUMMY_TABLES__) {
			foreach ($table_list as $tablename) {
				$tablename_orig = preg_replace('/^' . $TABLE_PREFIX_FOR_DUMMY . '/', '', $tablename);
				$this->db->query('DROP TABLE IF EXISTS `' . $tablename . '`');
				$this->db->query('CREATE TABLE `' . $tablename . '` LIKE `' . $tablename_orig . '`;');
			}
			@array_map('unlink', array_filter((array) glob(MEMBER_PHOTO_DUMMY_DIR . "*")));
		}

		while (($getData = fgetcsv($fhandle, $CSV_MAX_CHARS, $CSV_SEPARATOR)) !== FALSE) {
			$rownum++;
			$rowrum_processed++;

			$user_id = '';
			$ext_user_id = '';
			$email = '';
			// $no_kta = '';
			$status_db_education = 0;
			$status_db_transfer = 0;
			$status_db_users = 0;
			$status_db_profile = 0;
			$status_db_kolektif = 0;
			$status_db_address = 0;
			$status_db_members = 0;
			$status_db_job = 0;
			$status_db_commit = 0;
			$status_upload_photo = 0;
			$stasus_message = '-';

			// Skip header
			if ($rownum <= $CSV_START_DATA_ROW) continue;
			if ($getData === array(null) || trim(implode($getData)) === '') continue;
			if ($rownum > $CSV_READ_MAX_LINES) break;
			if (count($getData) < ($CSV_MIN_COLUMN_COUNT + 1)) continue;

			try {
				$username = $getData[7] ?? '';
				$password = ($__USE_DEFAULT_PASSWORD__) ? $DEFAULT_PASSWORD : $this->generate_password();
				$email = $getData[excelColumnToIndex('H')] ?? '';
				$ext_user_id = $getData[excelColumnToIndex('A')] ?? '';
				$kode_wil = $getData[6] ?? '';
				$kowil = substr($kode_wil, 0, 2);
				$kode_bk = $getData[excelColumnToIndex('M')] ?? '';
				$kode_kta = $getData[excelColumnToIndex('N')] ?? '';
				$from_date = $getData[43] ?? '';
				$thru_date = $getData[44] ?? '';
				$jenis_ang = "1";
				$status = 1;

				$fullname = $getData[5] ?? '';
				[$firstname, $lastname] = $this->extract_name($fullname);
				$gender = $this->gender($getData[excelColumnToIndex('I')] ?? '');
				$idnty_type = $this->indentity_type($getData[excelColumnToIndex('X')] ?? '');
				$idnty_number = $getData[excelColumnToIndex('Y')] ?? '';
				$birthplace = $getData[excelColumnToIndex('J')] ?? '';
				$birthdate = $getData[excelColumnToIndex('K')] ?? '';
				$mobilephone = $this->format_mobilephone($getData[excelColumnToIndex('Z')] ?? '');
				$va = '89699' . ($getData[6] ?? '') . ($getData[7] ?? '') . ($getData[8] ?? '');
				$kolektif_batch = $KOLEKTIF_BATCH_INSERT_ID;
				$kolektif_ids = implode(',', $KOLEKTIF_IDS);
				$createdby = $CREATOR_MODIFICATOR;
				$modifiedby = $CREATOR_MODIFICATOR;

				$addresstype = 1;
				$address = $getData[25] ?? '';
				$city = $getData[26] ?? '';
				$province = '';
				$zipcode = $getData[27] ?? '';
				$homephone = $getData[28] ?? '';

				$lembaga_nama = $getData[31] ?? '';
				$lembaga_jabatan = $getData[32] ?? '';
				$present_job = 1;
				$lembaga_alamat = $getData[34] ?? '';
				$lembaga_prov = '';
				$lembaga_negara = $DEFAULT_COUNTRY_NAME;

				$photo_link = $getData[40] ?? '';
				$idcard_link = $getData[41] ?? '';
				$ijazah_link = $getData[42] ?? '';

				$no_kta = $getData[excelColumnToIndex('N')] ?? '';

				if ($__USE_TRANSACTION__) $this->db->trans_start();

				// Insert users
				$user_data = ['username' => $no_kta, 'password' => $password, 'email' => $email];
				if ($this->db->insert($TABLE_USERS, $user_data)) $user_id = $this->db->insert_id();

				// Insert profile
				$uprofile_data = [
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
					'modifiedby' => $modifiedby
				];
				$this->db->insert($TABLE_USER_PROFILE, $uprofile_data);

				// Insert address
				$addr_data = [
					'user_id' => $user_id,
					'addresstype' => $addresstype,
					'address' => $address,
					'city' => $city,
					'province' => $province,
					'zipcode' => $zipcode,
					'phone' => $homephone,
					'createdby' => $createdby,
					'modifiedby' => $modifiedby
				];
				$this->db->insert($TABLE_USER_ADDRESS, $addr_data);

				// Insert members
				$members_data = [
					'person_id' => $user_id,
					'code_wilayah' => $kode_wil,
					'code_mitra' => 1,
					'code_bk_hkk' => $kode_bk,
					'years' => "25",
					'no_kta' => $no_kta,
					'from_date' => $from_date,
					'thru_date' => $thru_date,
					'jenis_anggota' => $jenis_ang,
					'status' => 1,
					'created_at' => date('Y-m-d H:i:s'),
					'created_by' => $createdby,
					'updated_by' => $modifiedby,
					'wil_id' => $kowil
				];
				$this->db->insert($TABLE_MEMBERS, $members_data);

				// Insert user transfer
				$user_transfer_data = [
					'user_id' => $user_id,
					'pay_type' => 1,
					'order_id' => 0,
					'rel_id' => 0,
					'bukti' => "INV046-PSPPI-UGM Angkatan 15 Bach 1",
					'atasnama' => $fullname,
					'tgl' => "2025-06-02",
					'status' => 1,
					'description' => 'Pembayaran kolektif UGM-15',
					'iuranpangkal' => 100000,
					'iurantahunan' => 225000,
					'sukarelatotal' => 325000,
					'vnv_status' => 1,
					'remark' => '[SIMPONI] Bulk insert 2025-06-12',
					'createddate' => date('Y-m-d H:i:s'),
					'createdby' => $createdby,
					'modifieddate' => date('Y-m-d H:i:s'),
					'modifiedby' => $createdby
				];
				$this->db->insert($TABLE_USER_TRANSFER, $user_transfer_data);

				// Experience
				$experince_data = [
					'user_id' => $user_id,
					'company' => $lembaga_nama,
					'title' => $lembaga_jabatan,
					'location' => $lembaga_alamat,
					'provinsi' => $lembaga_prov,
					'negara' => $lembaga_negara,
					'is_present' => $present_job,
					'createdby' => $createdby
				];
				$this->db->insert($TABLE_USER_EXP, $experince_data);

				// Education (Ijazah)
				$education_data = [
					'user_id' => $user_id,
					'type' => '1',
					'school' => $SCHOOL_NAME,
					'degree' => $SCHOOL_DEGREE,
					'description' => '[Data is automatically inserted]'
				];
				$this->db->insert($TABLE_USER_EDU, $education_data);

				if ($__USE_TRANSACTION__) $this->db->trans_complete();

				$success_count++;
				$result_list[] = [
					"row_num" => $rownum,
					"message" => "SUCCESS - Inserted user_id: $user_id, email: $email",
					"details" => []
				];
			} catch (Exception $e) {
				$result_list[] = [
					"row_num" => $rownum,
					"message" => "ERROR - " . $e->getMessage(),
					"details" => []
				];
			}
		}

		fclose($fhandle);

		return $this->output
			->set_content_type('application/json')
			->set_status_header($success_count > 0 ? 200 : 404)
			->set_output(json_encode([
				'status' => $success_count > 0,
				'message' => $success_count > 0 ? "Successfully processed {$success_count} rows" : "Failed processing all rows",
				'result' => $result_list
			]));
	}
}
