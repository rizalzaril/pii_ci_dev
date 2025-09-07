<?php
require_once(APPPATH.'/models/Payment_model.php');

// Cache TTL dari output lookup table dalam detik (second). 1 day = 86400s
define('CACHE_TTL_LOOKUP_TABLES', 86400); 

class Members_Model extends CI_Model {

	var $SPECIAL_ADMIN_1 = "673"; // ER: Siapa ini? tugiman.as@yahoo.com
	var $SPECIAL_ADMIN_2 = "675"; // ER: Siapa lagi ini? dhaninugroho92@gmail.com
	var $SPECIAL_ADMIN_3 = "706"; // ER: Siapa lagi lagi ini, hardcoded di finance_view.php?  wafiqta.dzi15@gmail.com, admin type = 8 (FINANCE)
	var $SPECIAL_ADMIN_4 = "780"; // ER: Siapa ini? Teguh
	var $SPECIAL_ADMIN_5 = "782"; // ER: Siapa ini? Admin user

	var $payment_mod;

    public function __construct() {

	   $this->payment_mod = new Payment_model();
	   $this->load->database();
	   $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }
    
	public function add_member($data){
  
            $return = $this->db->insert('pp_members', $data);
            if ((bool) $return === TRUE) {
                return $this->db->insert_id();
            } else {
                return $return;
            }       
			
	}	
	
	public function update_member($id, $data){
		$this->db->where('ID', $id);
		$return=$this->db->update('pp_members', $data);
		return $return;
	}
	
	public function update($id, $data){
		$this->db->where('ID', $id);
		$return=$this->db->update('pp_members', $data);
		return $return;
	}
	
	public function delete_member($id){
		$this->db->where('ID', $id);
		$this->db->delete('pp_members');
	}
	
	public function authenticate_member($user_name, $password) {
        $this->db->select('pp_members.*, pp_companies.company_slug');
        $this->db->from('pp_members');
		$this->db->join('pp_companies', 'pp_members.company_ID = pp_companies.ID', 'inner');
        $this->db->where('email', $user_name);
		$this->db->where('pass_code', $password);
		$this->db->limit(1);
        $Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->row();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
    }
	
	public function authenticate_member_by_email($user_name) {
        $this->db->select('pp_members.*');
        $this->db->from('pp_members');
        $this->db->where('email', $user_name);
		$this->db->limit(1);
        $Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->row();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
    }
	
	public function authenticate_member_by_password($ID, $password) {
        $this->db->select('*');
        $this->db->from('pp_members');
        $this->db->where('ID', $ID);
		$this->db->where('pass_code', $password);
		$this->db->limit(1);
        $Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->row();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
    }
	
	public function is_email_already_exists($ID, $email) {
        $this->db->select('ID');
        $this->db->from('pp_members');
        $this->db->where('ID !=', $ID);
		$this->db->where('email', $email);
		$this->db->limit(1);
        $Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->row('ID');
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
    }
	
 //---------------------------------------------------- Tambahan by IP --------------------   
   public function  get_all_per_wilayah($wil) {
       $this->db->from('m_cab');
       $this->db->where('wil_id', $wil ); 
        $Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->result();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
   }
//-------------------------------------------------------------------------------------------   

	/**
	 * Ambil semua cabang & wilayah. Wilayah yang punya kode lebih dari 2 digit bisa di-exclude 
	 */
	public function get_all_cabang_wilayah($wilayah_included = TRUE) {
		$output = 0;

		//Create caching key
		$uniq_string = __CLASS__.'@'. __FUNCTION__;
		$hash_cache  = crc32($uniq_string);

		if ( ! $output = $this->cache->get($hash_cache))
		{
				log_message('debug','[SIMPONI] '.__FUNCTION__.' - Saving to the cache! '.$uniq_string);
				
				$this->db->from('m_cab');
				if ( ! $wilayah_included ) $this->db->where('CHAR_LENGTH(value)<>2'); 
				$this->db->order_by("value", "asc"); 
				$Q = $this->db->get();
				if ($Q->num_rows() > 0) {
					$output = $Q->result();
				} else {
					$output = 0;
				}
				$Q->free_result();

				// Save into the cache 
				$this->cache->save($hash_cache, $output, CACHE_TTL_LOOKUP_TABLES);
		} else {
			log_message('debug','[SIMPONI] '.__FUNCTION__.' - Serving data from the cache!');
		}
        return $output;
    }
	
	/**
	 * Ambil semua cabang, tidak termasuk wilayah/provinsi
	 */
	public function get_all_cabang() {
		return $this->get_all_cabang_wilayah(false);
    }
	
	/**
	 * Ambil semua data badan kejuruan
	 */
	public function get_all_bk() {
		$output = 0;

		//Create caching key
		$uniq_string =  __CLASS__.'@'. __FUNCTION__;
		$hash_cache  = crc32($uniq_string);

		if ( ! $output = $this->cache->get($hash_cache))
		{
				log_message('debug','[SIMPONI] '.__FUNCTION__.' - Saving to the cache! '.$uniq_string);
				
				$this->db->from('m_bk');		
				$this->db->where('faip','1');
				$this->db->order_by("value", "asc");  
				$Q = $this->db->get();
				if ($Q->num_rows() > 0) {
					$output = $Q->result();
				} else {
					$output = 0;
				}
				$Q->free_result();

				// Save into the cache 
				$this->cache->save($hash_cache, $output, CACHE_TTL_LOOKUP_TABLES);
		} else {
			log_message('debug','[SIMPONI] '.__FUNCTION__.' - Serving data from the cache!');
		}
        return $output;
    }
	
	public function get_bk() {
		// TODO: move $this->session->userdata('code_bk_hkk') as function's parameter
        $this->db->from('m_bk');		
		$this->db->where('faip','1');
		$this->db->where("TRIM(LEADING '0' FROM value) = ".ltrim($this->session->userdata('code_bk_hkk'), '0'),null);
		$this->db->order_by("value", "asc");  
        $Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->result();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
    }
	
	public function get_all_hkk() {
		$output = 0;

		//Create caching key
		$uniq_string =  __CLASS__.'@'. __FUNCTION__;
		$hash_cache  = crc32($uniq_string);

		if ( ! $output = $this->cache->get($hash_cache))
		{
				log_message('debug','[SIMPONI] '.__FUNCTION__.' - Saving to the cache! '.$uniq_string);
				
				$this->db->from('m_hkk');		
				//$this->db->where('faip','1');
				$this->db->order_by("value", "asc");  
				$Q = $this->db->get();
				if ($Q->num_rows() > 0) {
					$output = $Q->result();
				} else {
					$output = 0;
				}
				$Q->free_result();

				// Save into the cache 
				$this->cache->save($hash_cache, $output, CACHE_TTL_LOOKUP_TABLES);
		} else {
			log_message('debug','[SIMPONI] '.__FUNCTION__.' - Serving data from the cache!');
		}
        return $output;		
    }
	
	public function get_all_typemajelis() {
        $this->db->where('id>2');
		$this->db->where('id<>8');
		$this->db->from('m_title');
        $Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->result();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
    }
	
	public function get_majelis($id) {
        $this->db->select('*');
        $this->db->from('admin');
		$this->db->where('id', $id);
        $Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->row();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
    }
	
	public function get_all_majelis_bk() {
        $this->db->select('*');
        $this->db->from('admin');
		
		$this->db->where('type',7);		
		if($this->session->userdata('type')!="0" && $this->session->userdata('type')!="1")
			$this->db->where("TRIM(LEADING '0' FROM code_bk_hkk) = ".ltrim($this->session->userdata('code_bk_hkk'), '0'),null);
		
        $Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->result();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
    }
	
	public function get_all_user_bk() {
        $this->db->select('users.id,firstname,lastname,(select members.no_kta from members where person_id=users.id) as no_kta');
        $this->db->from('users');
		//$this->db->join('members', 'users.id = members.person_id', 'inner');
		$this->db->join('user_profiles', 'users.id = user_profiles.user_id', 'inner');
		
		//$this->db->where('type',7);		
		if(!isAdminLSKI() && !isAdminPKB() && !isAdminKolektif() & !isAdminKolektifRO())
		{
			$this->db->group_start();
			$this->db->where("(mp_bk='' and users.id in (select person_id from members where TRIM(LEADING '0' FROM code_bk_hkk) = ".ltrim($this->session->userdata('code_bk_hkk'), '0').'))',null);
			$this->db->or_where("TRIM(LEADING '0' FROM user_profiles.mp_bk) = ".ltrim($this->session->userdata('code_bk_hkk'), '0'),null);
			//$this->db->or_where("users.id=7529",null); // WAHYU HENDRASTOMO
			$this->db->group_end();
		}
		$this->db->where('is_mp',1);
		$this->db->order_by("firstname,lastname", "asc"); 
		
        $Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->result();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
    }
	
	public function get_bp($id) {
        $this->db->select('*');
        $this->db->from('m_bakuan_penilaian');
		$this->db->where('id', $id);
        $Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->row();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
    }
	
	public function get_all_kolektif() {
		$output = 0;

		//Create caching key
		$uniq_string =  __CLASS__.'@'. __FUNCTION__;
		$hash_cache  = crc32($uniq_string);

		if ( ! $output = $this->cache->get($hash_cache))
		{
				log_message('debug','[SIMPONI] '.__CLASS__.'@'.__FUNCTION__.' - Saving to the cache! '.$uniq_string);
				
				$this->db->select('*');
				$this->db->from('admin');
				$this->db->where('type',11);		
				$this->db->where('code_bk_hkk','');
				$this->db->where('code_wilayah','');
				$this->db->order_by("name", "asc"); 
				$Q = $this->db->get();
				if ($Q->num_rows() > 0) {
					$output = $Q->result();
				} else {
					$output = 0;
				}
				$Q->free_result();

				// Save into the cache 
				$this->cache->save($hash_cache, $output, CACHE_TTL_LOOKUP_TABLES);
		} else {
			log_message('debug','[SIMPONI] '.__CLASS__.'@'.__FUNCTION__.' - Serving data from the cache!');
		}
        return $output;	
    }
	
	/**
	 * Get semua kode kolektif_name_id yang ada di user_profiles
	 * Ada nama di m_kolektif yang tidak akan muncul di list JIKA tidak ada users yang menggunakan kolektif_name_id tersebut
	 */
	public function get_all_kode_kolektif() {
        $this->db->select('distinct(`kolektif_name_id`) as kolektif_name_id, m_kolektif.name');
        $this->db->from('user_profiles');
		
		if($this->session->userdata('type')!="0" 
			&& $this->session->userdata('type')!="1" 
			&& $this->session->userdata('type')!="2" 
			&& $this->session->userdata('type')!="12" 
			&& $this->session->userdata('type')!="13") {
			
			$this->db->like('kolektif_ids',$this->session->userdata('admin_id'));	
		}	
	
		$this->db->join('m_kolektif', 'm_kolektif.id = user_profiles.kolektif_name_id', 'inner');
		
		$this->db->order_by("m_kolektif.id", "desc"); //CONCAT(COALESCE(firstname,''),COALESCE(lastname,''))
		
        $Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->result();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
    }
	
	/**
	 * Lookup tables for filtering by Kode Kolektif di search bar atas
	 */
	public function get_all_kode_kolektif2() {
        $this->db->select('*, (select name from m_kolektif_cat where id=category) as cat');
        $this->db->from('m_kolektif');
		
		//$this->db->order_by("name", "asc"); //CONCAT(COALESCE(firstname,''),COALESCE(lastname,''))
		$this->db->order_by("id", "desc"); //CONCAT(COALESCE(firstname,''),COALESCE(lastname,''))
		
        $Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->result();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
    }
	
	
	function last_kta(){
		//$this->db->select('count(id) as jml', FALSE);
		$this->db->select('id_kta as jml', FALSE);
		//$this->db->where('tahun=', date("Y"));
		$query = $this->db->get('id_management');
		return $query->row();
	}
		
	function insert_kta($table, $data, $insert_kta){
		$last = $this->last_kta();
		if ($this->db->insert($table, $data)) {
			$user_id = $this->db->insert_id();
						
			$this->db->set('id_kta', ($last->jml+1));
			//$this->db->where('tahun=', date("Y"));
			$this->db->update('id_management');
			
			
			//REMOVE BECAUSE VA
			if($insert_kta){
				$this->db->set('username', $data['no_kta']);
				$this->db->where('id', $data['person_id']);
				$this->db->update('users');
			}
			return true;
		}
		return false;
		
		
	}
	
	/*function last_stri(){
		//$this->db->select('count(id) as jml', FALSE);
		$this->db->select('id_stri as jml', FALSE);
		//$this->db->where('tahun=', date("Y"));
		$query = $this->db->get('id_management');
		return $query->row();
	}
		
	function insert_stri($table, $data){
		$last = $this->last_stri();
		if ($this->db->insert($table, $data)) {
			$user_id = $this->db->insert_id();
						
			$this->db->set('id_stri', ($last->jml+1));
			//$this->db->where('tahun=', date("Y"));
			$this->db->update('id_management');
			
			return true;
		}
		return false;
		
	}*/
		
	function last_ip(){
		//$this->db->select('count(id) as jml', FALSE);
		$this->db->select('id_ip as jml', FALSE);
		//$this->db->where('tahun=', date("Y"));
		$query = $this->db->get('id_management');
		return $query->row();
	}
		
	function insert_ip($table, $data){
		$last = $this->last_ip();
		if ($this->db->insert($table, $data)) {
			$user_id = $this->db->insert_id();
						
			$this->db->set('id_ip', ($last->jml+1));
			//$this->db->where('tahun=', date("Y"));
			$this->db->update('id_management');
			
			return true;
		}
		return false;
		
	}
	
	function last_ipm() {
		//$this->db->select('count(id) as jml', FALSE);
		$this->db->select('id_ipm as jml', FALSE);
		//$this->db->where('tahun=', date("Y"));
		$query = $this->db->get('id_management');
		return $query->row();
	}
		
	function insert_ipm($table, $data){
		$last = $this->last_ipm();
		if ($this->db->insert($table, $data)) {
			$user_id = $this->db->insert_id();
						
			$this->db->set('id_ipm', ($last->jml+1));
			//$this->db->where('tahun=', date("Y"));
			$this->db->update('id_management');
			
			return true;
		}
		return false;
		
	}
	
	function last_ipu() {
		//$this->db->select('count(id) as jml', FALSE);
		$this->db->select('id_ipu as jml', FALSE);
		//$this->db->where('tahun=', date("Y"));
		$query = $this->db->get('id_management');
		return $query->row();
	}
		
	function insert_ipu($table, $data){
		$last = $this->last_ipu();
		if ($this->db->insert($table, $data)) {
			$user_id = $this->db->insert_id();
						
			$this->db->set('id_ipu', ($last->jml+1));
			//$this->db->where('tahun=', date("Y"));
			$this->db->update('id_management');
			
			return true;
		}
		return false;
		
	}
			
	function last_skip($certificate_type, $skip_code_bk_hkk, $skip_sub_code_bk_hkk){
		//$this->db->select('count(id) as jml', FALSE);
		$this->db->select('skip_id as jml', FALSE);
		$this->db->where('certificate_type', $certificate_type);
		$this->db->where('skip_code_bk_hkk', $skip_code_bk_hkk);
		$this->db->where('skip_sub_code_bk_hkk', $skip_sub_code_bk_hkk);
		$query = $this->db->get('members_certificate');
		return $query->row();
	}
	
	function last_stri_bc($certificate_type, $stri_code_bk_hkk, $stri_sub_code_bk_hkk){
		$this->db->select('COUNT(id) as jml', FALSE);
		//$this->db->where('certificate_type', $certificate_type);
		//$this->db->where('stri_code_bk_hkk', $stri_code_bk_hkk);
		//$this->db->where('stri_sub_code_bk_hkk', $stri_sub_code_bk_hkk);
		$this->db->where('status', '1');
		$query = $this->db->get('members_certificate');
		return $query->row();
	}
	
	function last_stri(){
		$this->db->select('id_stri as jml', FALSE);
		//$this->db->where('certificate_type', $certificate_type);
		//$this->db->where('stri_code_bk_hkk', $stri_code_bk_hkk);
		//$this->db->where('stri_sub_code_bk_hkk', $stri_sub_code_bk_hkk);
		$this->db->where('seq', '1');
		$query = $this->db->get('id_management');
		return $query->row();
	}
	
	function check_last_stri($id){
		$this->db->select('stri_id', FALSE);
		$this->db->from('members_certificate');
		//$this->db->where('certificate_type', $certificate_type);
		//$this->db->where('stri_code_bk_hkk', $stri_code_bk_hkk);
		//$this->db->where('stri_sub_code_bk_hkk', $stri_sub_code_bk_hkk);
		$this->db->where('stri_id', $id);
		$this->db->where('status', 1);
		$query = $this->db->get();
		return $query->row();
	}
	
	function insert_skip($table, $data){
		if ($this->db->insert($table, $data)) {
			return true;
		}
		return false;
		
	}
	
	function insert_stri($table, $data){
		if ($this->db->insert($table, $data)) {
			return true;
		}
		return false;
		
	}
	
	public function get_all_members($per_page, $page) {
        $this->db->select('users.id as ID, username as no_kta, users.email, user_profiles.firstname, user_profiles.lastname, user_profiles.gender, user_profiles.idcard, user_profiles.dob, users.user_status as sts, (select code_wilayah from members where person_id=users.id limit 1) as cab,(select code_bk_hkk from members where person_id=users.id limit 1) as bk,(select from_date from members where person_id=users.id limit 1) as from_date,(select thru_date from members where person_id=users.id limit 1) as thru_date,(select jenis_anggota from members where person_id=users.id limit 1) as jenis_anggota,(select id from user_cert where user_id=users.id and status=2 and endyear>curdate() limit 1) as ip');
        $this->db->from('user_profiles');
		$this->db->join('users', 'user_profiles.user_id = users.id', 'left');
		//$this->db->join('members', 'user_profiles.user_id = members.person_id', 'left');
		//$this->db->order_by("user_profiles.id", "DESC"); 
		$this->db->limit($per_page, $page);
        $Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->result();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
    }
	
	public function get_all_members_v2($per_page, $page,$bk,$wil,$is_kolektif) {
		$this->db->select('users.id as ID, (select no_kta from members where person_id=users.id limit 1) as no_kta, '.
		'users.email, user_profiles.firstname, user_profiles.lastname, user_profiles.gender, user_profiles.idcard, '.
		'user_profiles.dob, users.user_status as sts, (select code_wilayah from members '.
		'where person_id=users.id limit 1) as cab,(select code_bk_hkk from members where person_id=users.id limit 1) as bk, '.
		'(select code_hkk from members where person_id=users.id limit 1) as hkk, '.
		'(select from_date from members where person_id=users.id limit 1) as from_date, '.
		'(select thru_date from members where person_id=users.id limit 1) as thru_date, '.
		'(select jenis_anggota from members where person_id=users.id limit 1) as jenis_anggota, '.
		'(select id from user_cert where user_id=users.id and status=2 and endyear>curdate() limit 1) as ip, '.
		'user_profiles.kolektif_ids, (select name from m_kolektif where status=1 and id=user_profiles.kolektif_name_id) as kolektif_name, '.
		'warga_asing,username,kolektif_name_id');
        $this->db->from('user_profiles');
		$this->db->join('users', 'user_profiles.user_id = users.id', 'left');		
//-------------------------------------------------------------------------------------------------- Tambahan by IP -----		
	if (strlen(trim($bk)) != 0 ) {
		$this->db->join('members', 'user_profiles.user_id = members.person_id', 'left');
//		$this->db->where('members.code_bk_hkk', $bk) ; 
		$this->db->where('LPAD(members.code_bk_hkk,2,"0")', $bk) ; //LPAD( code_bk_hkk, 2, '0' )
	}	
	if (strlen(trim($wil)) != 0 ) {
		if (strlen(trim($wil)) != 2 ) {
			$this->db->join('members', 'user_profiles.user_id = members.person_id', 'left');
			$this->db->where('members.code_wilayah', $wil) ; 				// untuk perbaikan tampilan per cabang tambahan by Ip		
		}
		if (strlen(trim($wil)) == 2 ) {
			$this->db->join('members', 'user_profiles.user_id = members.person_id', 'left');
			$this->db->where('members.wil_id', $wil) ; 				// untuk perbaikan tampilan per cabang tambahan by Ip		
		}		
       }	
//---------------------------------------------------------------------------------------------------------------------		


		//$this->db->join('members', 'user_profiles.user_id = members.person_id', 'left');
		//$this->db->order_by("user_profiles.id", "DESC"); 
		
	if($is_kolektif)
	{	
		$str = '';
		$i = 0;
			
		if(is_array($bk)){
			foreach($bk as $val)
			{
				if($i==0) $str .= " LPAD( code_bk_hkk, 2, '0' ) like '".$val."%'";
				else $str .= " or LPAD( code_bk_hkk, 2, '0' ) like '".$val."%'";
				$i++;
			}
		}
		else if($bk!=""){
			if($i==0) $str .= " LPAD( code_bk_hkk, 2, '0' ) like '".$bk."%'";
			else $str .= " or LPAD( code_bk_hkk, 2, '0' ) like '".$bk."%'";
			$i++;		
		}		
		if(is_array($wil)){
			foreach($wil as $val)
			{
				if($i==0) $str .= " LPAD( code_wilayah, 4, '0' ) like '".$val."%'";
				else $str .= " or LPAD( code_wilayah, 4, '0' ) like '".$val."%'";
				$i++;
			}
		}
		else if($wil!=""){
			if($i==0) $str .= " LPAD( code_wilayah, 4, '0' ) like '".$wil."%'";
			else $str .= " or LPAD( code_wilayah, 4, '0' ) like '".$wil."%'";
			$i++;		
		}
		
		
		$this->db->group_start();
		$this->db->where('FIND_IN_SET('. $this->session->userdata('admin_id').',kolektif_ids) <>',"0");
		if($str!='') $this->db->or_where('user_profiles.user_id in (select person_id from members where '.$str.')',null);
		$this->db->group_end();
		
	}
		
		$this->db->limit($per_page, $page);
        $Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->result();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
    }
	
	public function get_all_cabang_members_v2($bk,$wil,$is_kolektif) {
        $this->db->select('LPAD(members.code_wilayah, 4, 0) as value,(select name from m_cab where value=LPAD(members.code_wilayah, 4, 0)) as name');
        $this->db->from('user_profiles');
/*        
		//$this->db->join('users', 'user_profiles.user_id = users.id', 'left');
		$this->db->join('members', 'user_profiles.user_id = members.person_id', 'left');
		//$this->db->order_by("user_profiles.id", "DESC"); 
*/	
		$this->db->join('users', 'user_profiles.user_id = users.id', 'left');
		$this->db->join('members', 'user_profiles.user_id = members.person_id', 'left'); /// untuk perbaikan tampilan per cabang tambahan by Ip	
		$this->db->where('members.wil_id', $wil) ; 				// untuk perbaikan tampilan per cabang tambahan by Ip
		
	if($is_kolektif)
	{	
		$str = '';
		$i = 0;
			
		if(is_array($bk)){
			foreach($bk as $val)
			{
				if($i==0) $str .= " LPAD( code_bk_hkk, 2, '0' ) like '".$val."%'";
				else $str .= " or LPAD( code_bk_hkk, 2, '0' ) like '".$val."%'";
				$i++;
			}
		}
		else if($bk!=""){
			if($i==0) $str .= " LPAD( code_bk_hkk, 2, '0' ) like '".$bk."%'";
			else $str .= " or LPAD( code_bk_hkk, 2, '0' ) like '".$bk."%'";
			$i++;		
		}		
		if(is_array($wil)){
			foreach($wil as $val)
			{
/*			
				if($i==0) $str .= " LPAD( code_wilayah, 4, '0' ) like '".$val."%'";
				else $str .= " or LPAD( code_wilayah, 4, '0' ) like '".$val."%'";
				$i++;
*/

				if($i==0) $str .= "  code_wilayah like '".$val."%'";
				else $str .= " or code_wilayah like '".$val."%'";
				$i++;
		
		
			}
		}
		else if($wil!=""){
/*		
			if($i==0) $str .= " LPAD( code_wilayah, 4, '0' ) like '".$wil."%'";
			else $str .= " or LPAD( code_wilayah, 4, '0' ) like '".$wil."%'";
			$i++;	
*/
	
			if($i==0) $str .= " code_wilayah like '".$wil."%'";
			else $str .= " or code_wilayah like '".$wil."%'";
			$i++;		

			
		}
		
		
		$this->db->group_start();
		$this->db->where('FIND_IN_SET('. $this->session->userdata('admin_id').',kolektif_ids) <>',"0");
		if($str!='') $this->db->or_where('user_profiles.user_id in (select person_id from members where '.$str.')',null);
		$this->db->group_end();
		
	}
		
		$this->db->group_by('code_wilayah');
		$this->db->order_by("code_wilayah", "asc"); 
		
		//$this->db->limit($per_page, $page);
        $Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->result();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
    }
	
	public function get_all_finance($per_page, $page) {

		//TODO: FIXME - Refactor this query using JOIN.
        $this->db->select('users.id as ID, username as no_kta, users.email, user_profiles.firstname, user_profiles.lastname, user_profiles.gender, user_profiles.idcard, user_profiles.dob, users.user_status as sts,
		(select pay_type from user_transfer where user_profiles.user_id=user_id order by createddate desc limit 1) as paytype,
		(select atasnama from user_transfer where user_profiles.user_id=user_id order by createddate desc limit 1) as payname,
		(select tgl from user_transfer where user_profiles.user_id=user_id order by createddate desc limit 1) as paydate,
		(select description from user_transfer where user_profiles.user_id=user_id order by createddate desc limit 1) as paydesc,
		(select bukti from user_transfer where user_profiles.user_id=user_id order by createddate desc limit 1) as payfile,
		(select status from user_transfer where user_profiles.user_id=user_id order by createddate desc limit 1) as paystatus,
		
		(select iuranpangkal from user_transfer where user_profiles.user_id=user_id order by createddate desc limit 1) as payiuranpangkal,
		(select iurantahunan from user_transfer where user_profiles.user_id=user_id order by createddate desc limit 1) as payiurantahunan,
		(select sukarelaanggota from user_transfer where user_profiles.user_id=user_id order by createddate desc limit 1) as paysukarelaanggota,
		(select sukarelagedung from user_transfer where user_profiles.user_id=user_id order by createddate desc limit 1) as paysukarelagedung,
		(select sukarelaperpus from user_transfer where user_profiles.user_id=user_id order by createddate desc limit 1) as paysukarelaperpus,
		(select sukarelaceps from user_transfer where user_profiles.user_id=user_id order by createddate desc limit 1) as paysukarelaceps,
		(select sukarelatotal from user_transfer where user_profiles.user_id=user_id order by createddate desc limit 1) as paysukarelatotal,
		
		(select lic_num from user_cert where user_id=users.id and status=2 order by endyear desc,ip_tipe desc limit 1) as sip_lic_num,
		(select startyear from user_cert where user_id=users.id and status=2 order by endyear desc,ip_tipe desc limit 1) as sip_startyear,
		(select endyear from user_cert where user_id=users.id and status=2 order by endyear desc,ip_tipe desc limit 1) as sip_endyear,
		
		(select id from user_transfer where user_profiles.user_id=user_id order by createddate desc limit 1) as payid,(select from_date from members where person_id=users.id limit 1) as from_date,(select thru_date from members where person_id=users.id limit 1) as thru_date');
        $this->db->from('user_profiles');
		$this->db->join('users', 'user_profiles.user_id = users.id', 'left');
		//$this->db->join('members', 'user_profiles.user_id = members.person_id', 'left');
		//$this->db->where('username =""', null,false);
		$this->db->where('users.id in (select user_id from user_transfer)', null,false);
		$this->db->order_by("paystatus", "asc"); 
		$this->db->order_by("user_profiles.createddate", "DESC"); 
		$this->db->limit($per_page, $page);
        $Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->result();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
    }
	
	public function get_all_finance_2($per_page, $page) {
        /*
		(select x.pay_type from user_transfer x where user_profiles.user_id=x.user_id and user_transfer.pay_type=x.pay_type order by x.createddate desc limit 1) as paytype,
		(select x.atasnama from user_transfer x where user_profiles.user_id=x.user_id and user_transfer.pay_type=x.pay_type order by x.createddate desc limit 1) as payname,
		(select x.tgl from user_transfer x where user_profiles.user_id=x.user_id and user_transfer.pay_type=x.pay_type order by x.createddate desc limit 1) as paydate,
		(select x.description from user_transfer x where user_profiles.user_id=x.user_id and user_transfer.pay_type=x.pay_type order by x.createddate desc limit 1) as paydesc,
		(select x.bukti from user_transfer x where user_profiles.user_id=x.user_id and user_transfer.pay_type=x.pay_type order by x.createddate desc limit 1) as payfile,
		(select x.status from user_transfer x where user_profiles.user_id=x.user_id and user_transfer.pay_type=x.pay_type order by x.createddate desc limit 1) as paystatus,
		(select x.iuranpangkal from user_transfer x where user_profiles.user_id=x.user_id and user_transfer.pay_type=x.pay_type order by x.createddate desc limit 1) as payiuranpangkal,
		(select x.iurantahunan from user_transfer x where user_profiles.user_id=x.user_id and user_transfer.pay_type=x.pay_type order by x.createddate desc limit 1) as payiurantahunan,
		(select x.sukarelaanggota from user_transfer x where user_profiles.user_id=x.user_id and user_transfer.pay_type=x.pay_type order by x.createddate desc limit 1) as paysukarelaanggota,
		(select x.sukarelagedung from user_transfer x where user_profiles.user_id=x.user_id and user_transfer.pay_type=x.pay_type order by x.createddate desc limit 1) as paysukarelagedung,
		(select x.sukarelaperpus from user_transfer x where user_profiles.user_id=x.user_id and user_transfer.pay_type=x.pay_type order by x.createddate desc limit 1) as paysukarelaperpus,
		(select x.sukarelaceps from user_transfer x where user_profiles.user_id=x.user_id and user_transfer.pay_type=x.pay_type order by x.createddate desc limit 1) as paysukarelaceps,
		(select x.sukarelatotal from user_transfer x where user_profiles.user_id=x.user_id and user_transfer.pay_type=x.pay_type order by x.createddate desc limit 1) as paysukarelatotal,
		
		(select lic_num from user_cert where user_id=users.id and status=2 order by endyear desc,ip_tipe desc limit 1) as sip_lic_num,
		(select startyear from user_cert where user_id=users.id and status=2 order by endyear desc,ip_tipe desc limit 1) as sip_startyear,
		(select endyear from user_cert where user_id=users.id and status=2 order by endyear desc,ip_tipe desc limit 1) as sip_endyear,
		
		(select x.id from user_transfer x where user_profiles.user_id=x.user_id and user_transfer.pay_type=x.pay_type order by x.createddate desc limit 1) as payid,
		
		(select from_date from members where person_id=users.id limit 1) as from_date,
		(select thru_date from members where person_id=users.id limit 1) as thru_date,
		*/
		
		$this->db->select('user_transfer.id as ut_id, users.id as ID, username as no_kta, users.email, user_profiles.firstname, user_profiles.lastname, user_profiles.gender, user_profiles.idcard, user_profiles.dob, users.user_status as sts,
		pay_type,
		from_date,
		thru_date,		
		user_profiles.va,is_upload_mandiri,(select name from m_kolektif where status=1 and id=user_profiles.kolektif_name_id) as kolektif_name
		');
        $this->db->from('user_transfer');
		$this->db->join('users', 'user_transfer.user_id = users.id');
		$this->db->join('user_profiles', 'user_profiles.user_id = user_transfer.user_id', 'left');
		$this->db->join('members', 'user_transfer.user_id = members.person_id', 'left');
		
		$this->db->where('(CASE WHEN pay_type=5 THEN vnv_status=1 ELSE 1=1 END)', null,false);//and rel_id=0 
		
//		if($this->session->userdata('admin_id')=="673" ){
		if($this->session->userdata('admin_id')=="673" || $this->session->userdata('admin_id')=="672"){
			$this->db->where('pay_type in (3,4)', null,false);
		}
//		else if($this->session->userdata('admin_id')=="675" ){
		else if($this->session->userdata('admin_id')=="782" || $this->session->userdata('admin_id')=="780"){
			$this->db->where('pay_type in (1,2)', null,false);
		}
		
		$this->db->group_by('user_transfer.user_id,user_transfer.pay_type');
		
		//$this->db->order_by("paystatus", "asc");
		//$this->db->order_by("user_transfer.modifieddate", "desc");		
		//$this->db->order_by("user_profiles.createddate", "DESC"); 
		
		$this->db->limit($per_page, $page);
        $Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->result();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
    }
	
	public function get_all_non_kta($per_page, $page) {
        $this->db->select('users.id as ID, username as no_kta, users.email, user_profiles.firstname, user_profiles.lastname, user_profiles.gender, user_profiles.idcard, user_profiles.dob, users.user_status as sts,
		warga_asing,
		sertifikat_legal,
		tanda_bukti,
		surat_dukungan,
		surat_pernyataan,
		surat_ijin_domisili,
		(select atasnama from user_transfer where user_profiles.user_id=user_id and (pay_type=0 or pay_type=1) order by createddate desc limit 1) as payname,
		(select tgl from user_transfer where user_profiles.user_id=user_id and (pay_type=0 or pay_type=1) order by createddate desc limit 1) as paydate,
		(select description from user_transfer where user_profiles.user_id=user_id and (pay_type=0 or pay_type=1) order by createddate desc limit 1) as paydesc,
		(select bukti from user_transfer where user_profiles.user_id=user_id and (pay_type=0 or pay_type=1) order by createddate desc limit 1) as payfile,
		(select status from user_transfer where user_profiles.user_id=user_id and (pay_type=0 or pay_type=1) order by createddate desc limit 1) as paystatus,
		(select id from user_transfer where user_profiles.user_id=user_id and (pay_type=0 or pay_type=1) order by createddate desc limit 1) as payid,
		(select vnv_status from user_transfer where user_profiles.user_id=user_id and (pay_type=0 or pay_type=1) order by createddate desc limit 1) as vnv_status,
		(select remark from user_transfer where user_profiles.user_id=user_id and (pay_type=0 or pay_type=1) order by createddate desc limit 1) as remark
		');
        $this->db->from('user_profiles');
		$this->db->join('users', 'user_profiles.user_id = users.id', 'left');
		//$this->db->join('members', 'user_profiles.user_id = members.person_id', 'left');
		$this->db->where('username =""', null,false);
		
		$this->db->where('user_id not in (select user_id from user_transfer where pay_type=5)', null,false);
		
		$this->db->order_by("paystatus", "DESC"); 
		$this->db->order_by("user_profiles.createddate", "DESC"); 
		$this->db->limit($per_page, $page);
        $Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->result();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
    }
	
	public function get_all_non_kta_2($per_page, $page,$bk,$wil,$is_kolektif) {
        $this->db->select('`users`.`id` as `ID`, `username` as `no_kta`, `users`.`email`, 
		`users`.`user_status` as `sts`, created, 
		`firstname`, 
		`lastname`, 
		`gender`, 
		`idcard`, 
		`dob`,  
		`warga_asing`, 
		`sertifikat_legal`, 
		`tanda_bukti`, 
		`surat_dukungan`, 
		`surat_pernyataan`, 
		`surat_ijin_domisili`,(select no_kta from members where person_id=user_profiles.user_id) as no_kta_temp');
        $this->db->from('user_profiles');
		$this->db->join('users', 'user_profiles.user_id = users.id', 'left');
		//$this->db->join('members', 'user_profiles.user_id = members.person_id', 'left');
		$this->db->where('username =""', null,false);
		//$this->db->where('user_id not in (select user_id from user_transfer where pay_type=5)', null,false);
		$this->db->where('user_id in (select user_id from user_transfer where pay_type=1)', null,false);
		
		
		if($is_kolektif)
		{
			$str = '';
			$str2 = '';
			$i = 0;
			$i2 = 0;
				
			if(is_array($bk)){
				foreach($bk as $val)
				{
					if($i==0) $str .= " LPAD( code_bk_hkk, 2, '0' ) like '".$val."%'";
					else $str .= " or LPAD( code_bk_hkk, 2, '0' ) like '".$val."%'";
					$i++;
				}
			}
			else if($bk!=""){
				if($i==0) $str .= " LPAD( code_bk_hkk, 2, '0' ) like '".$bk."%'";
				else $str .= " or LPAD( code_bk_hkk, 2, '0' ) like '".$bk."%'";
				$i++;		
			}		
			if(is_array($wil)){
				foreach($wil as $val)
				{
					if($i2==0) $str2 .= " LPAD( code_wilayah, 4, '0' ) like '".$val."%'";
					else $str2 .= " or LPAD( code_wilayah, 4, '0' ) like '".$val."%'";
					$i2++;
				}
			}
			else if($wil!=""){
				if($i2==0) $str2 .= " LPAD( code_wilayah, 4, '0' ) like '".$wil."%'";
				else $str2 .= " or LPAD( code_wilayah, 4, '0' ) like '".$wil."%'";
				$i2++;		
			}
			
			
			$this->db->group_start();
			//$this->db->where('FIND_IN_SET('. $this->session->userdata('admin_id').',kolektif_ids) <>',"0");
			if($str!='') $this->db->where('user_profiles.user_id in (select person_id from members where '.$str.')',null);
			if($str2!='') $this->db->or_where('user_profiles.user_id in (select person_id from members where '.$str2.')',null);
			$this->db->group_end();
		}
		
		
		
		$str = $this->db->get_compiled_select();
		
		
		$str2 =  $this->db
		->select("d.*, 
(select atasnama from user_transfer where d.ID=user_id and (pay_type=0 or pay_type=1) order by createddate desc limit 1) as payname, 
(select tgl from user_transfer where d.ID=user_id and (pay_type=0 or pay_type=1) order by createddate desc limit 1) as paydate, 
(select description from user_transfer where d.ID=user_id and (pay_type=0 or pay_type=1) order by createddate desc limit 1) as paydesc, 
(select bukti from user_transfer where d.ID=user_id and (pay_type=0 or pay_type=1) order by createddate desc limit 1) as payfile, 
(select status from user_transfer where d.ID=user_id and (pay_type=0 or pay_type=1) order by createddate desc limit 1) as paystatus, 
(select id from user_transfer where d.ID=user_id and (pay_type=0 or pay_type=1) order by createddate desc limit 1) as payid, 
(select vnv_status from user_transfer where d.ID=user_id and (pay_type=0 or pay_type=1) order by createddate desc limit 1) as vnv_status, 
(select remark from user_transfer where d.ID=user_id and (pay_type=0 or pay_type=1) order by createddate desc limit 1) as remark,
(select createddate from user_transfer where d.ID=user_id and (pay_type=0 or pay_type=1) order by createddate desc limit 1) as tgl_pengajuan", false)
		->from('('.$str.') as d')	
		->order_by("(CASE
			WHEN vnv_status = 1 THEN 2
			WHEN vnv_status = 2 THEN 1
			WHEN vnv_status = 0 THEN 0
			ELSE 1 END)", "ASC")		
		->order_by("paystatus", "asc")
		->order_by("tgl_pengajuan", "asc")
		//->order_by("d.created", "DESC")
		->limit($per_page, $page)		
		->get_compiled_select();
		
		//(case when (select status from user_transfer where d.ID=user_id and (pay_type=0 or pay_type=1) order by createddate desc limit 1)=0 then 3 else (select status from user_transfer where d.ID=user_id and (pay_type=0 or pay_type=1) order by createddate desc limit 1) end) as seq
		
		$str2 = str_replace("`", "", $str2);		
		$Q = $this->db->query($str2);
		
		
		//$this->db->order_by("paystatus", "DESC"); 
		//$this->db->order_by("user_profiles.createddate", "DESC"); 
		//$this->db->limit($per_page, $page);
		//$Q = $this->db->get();
		
        if ($Q->num_rows() > 0) {
            $return = $Q->result();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
    }

	/**
	 * Dipanggil dari halaman "HER Members" (New)
	 * New query 2 Jun 2024
	 * Lihat: https://docs.google.com/document/d/1x_4OOS3dG90PAlVUK8RRSb-Qdu5VOFfW0blBJIHVgd4/edit?usp=sharing
	 */
	public function get_all_her_kta($per_page, $page,$bk,$wil,$is_kolektif) {
        $this->db->select('
		  		max(user_transfer.id) as payid,
				user_transfer.id as payid,
				user_transfer.createddate as tgl_pengajuan,
				users.id as ID, 
				users.username as no_kta, 
				users.email, 
				user_profiles.firstname, 
				user_profiles.lastname, 
				user_profiles.gender, 
				user_profiles.idcard, 
				user_profiles.dob, 
				users.user_status as sts,		
				user_profiles.warga_asing,
				user_profiles.sertifikat_legal,
				user_profiles.tanda_bukti,
				user_profiles.surat_dukungan,
				user_profiles.surat_pernyataan,
				user_profiles.surat_ijin_domisili,
				user_transfer.atasnama as payname,
				user_transfer.tgl as paydate,
				user_transfer.description paydesc,
				user_transfer.bukti as payfile,
				user_transfer.status as paystatus,
				user_transfer.iuranpangkal as payiuranpangkal,
				user_transfer.iurantahunan as payiurantahunan,
				user_transfer.sukarelaanggota as paysukarelaanggota,
				user_transfer.sukarelagedung as paysukarelagedung,
				user_transfer.sukarelaperpus as paysukarelaperpus,
				user_transfer.sukarelaceps as paysukarelaceps,
				user_transfer.sukarelatotal as paysukarelatotal,
		
				user_cert1.lic_num as sip_lic_num,
				user_cert1.startyear as sip_startyear,
				user_cert1.endyear as sip_endyear,
				
				log_her_kta.id_pay  as id_pay, 
				log_her_kta.from_date as plan_from_date, 
				log_her_kta.thru_date as plan_thru_date, 
				
				user_transfer.id as id_pay_cek, 
				user_transfer.vnv_status as vnv_status,
				user_transfer.remark as remark,
				
				members.code_wilayah as cab,
				members.code_bk_hkk as bk,
				members.from_date  as from_date,
				members.thru_date as thru_date');
        $this->db->from('user_transfer')
			->join('log_her_kta', 'user_transfer.id = log_her_kta.id_pay', 'left')
			->join('user_profiles', 'user_transfer.user_id = user_profiles.user_id', 'left')
			->join('users', 'user_profiles.user_id = users.id', 'left')
			->join('members', 'members.person_id = users.id', 'left')
			->join('(SELECT DISTINCT user_id, lic_num, startyear, max(endyear) as endyear FROM user_cert WHERE status=2 GROUP BY user_id) user_cert1', 'user_transfer.user_id=user_cert1.user_id', 'left')
			->where('user_transfer.pay_type = 2')
			->where('(users.username <> \'\' OR users.username IS NOT NULL)')
			->group_by('user_transfer.user_id');
		
		
		if($is_kolektif)
		{
			$str = '';
			$str2 = '';
			$i = 0;
			$i2 = 0;
				
			if(is_array($bk)){
				foreach($bk as $val)
				{
					if($i==0) $str .= " LPAD( code_bk_hkk, 2, '0' ) like '".$val."%'";
					else $str .= " or LPAD( code_bk_hkk, 2, '0' ) like '".$val."%'";
					$i++;
				}
			}
			else if($bk!=""){
				if($i==0) $str .= " LPAD( code_bk_hkk, 2, '0' ) like '".$bk."%'";
				else $str .= " or LPAD( code_bk_hkk, 2, '0' ) like '".$bk."%'";
				$i++;		
			}		
			if(is_array($wil)){
				foreach($wil as $val)
				{
					if($i2==0) $str2 .= " LPAD( code_wilayah, 4, '0' ) like '".$val."%'";
					else $str2 .= " or LPAD( code_wilayah, 4, '0' ) like '".$val."%'";
					$i2++;
				}
			}
			else if($wil!=""){
				if($i2==0) $str2 .= " LPAD( code_wilayah, 4, '0' ) like '".$wil."%'";
				else $str2 .= " or LPAD( code_wilayah, 4, '0' ) like '".$wil."%'";
				$i2++;		
			}
			
			
			$this->db->group_start();
			//$this->db->where('FIND_IN_SET('. $this->session->userdata('admin_id').',kolektif_ids) <>',"0");
			if($str!='') $this->db->where('user_profiles.user_id in (select person_id from members where '.$str.')',null);
			if($str2!='') $this->db->or_where('user_profiles.user_id in (select person_id from members where '.$str2.')',null);
			$this->db->group_end();
		}
		
		$this->db->order_by("(CASE
			WHEN vnv_status = 1 THEN 2
			WHEN vnv_status = 2 THEN 1
			WHEN vnv_status = 0 THEN 0
			ELSE 1 END)", "ASC");
		$this->db->order_by("paystatus", "asc"); 
		$this->db->order_by("tgl_pengajuan", "asc"); 
		//$this->db->order_by("id_pay", "asc"); 
		//$this->db->order_by("paydate", "asc"); 
		$this->db->limit($per_page, $page);
        $Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->result();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
    }	
	
	/**
	 * HER Members (Original)
	 * TODO: FIXME: Perlu diubah query yang lebih baik 
	 * Direname dari `get_all_her_kta` menjadi `get_all_her_kta_orig` tanggal 2 Juni 2024
	 * karena diubah querynya. 
	 * Lihat: https://docs.google.com/document/d/1x_4OOS3dG90PAlVUK8RRSb-Qdu5VOFfW0blBJIHVgd4/edit?usp=sharing
	 */
	public function get_all_her_kta_orig($per_page, $page,$bk,$wil,$is_kolektif) {
		// ER: TODO Fixme! Informasi yang gak muncul diawal (payment info) sebaiknya gak dilakukan query
		// ER: TODO FIxme! Mungkin bisa menggunakan JOIN dgn log_her_kta daripada pakai nested query
        $this->db->select(
		'users.id as ID, username as no_kta, users.email, user_profiles.firstname, user_profiles.lastname, user_profiles.gender, user_profiles.idcard, user_profiles.dob, users.user_status as sts,		
		warga_asing,
		sertifikat_legal,
		tanda_bukti,
		surat_dukungan,
		surat_pernyataan,
		surat_ijin_domisili,
		(select atasnama from user_transfer where user_transfer.pay_type=2 and user_profiles.user_id=user_id order by createddate desc limit 1) as payname,
		(select tgl from user_transfer where user_transfer.pay_type=2 and user_profiles.user_id=user_id order by createddate desc limit 1) as paydate,
		(select description from user_transfer where user_transfer.pay_type=2 and user_profiles.user_id=user_id order by createddate desc limit 1) as paydesc,
		(select bukti from user_transfer where user_transfer.pay_type=2 and user_profiles.user_id=user_id order by createddate desc limit 1) as payfile,
		(select status from user_transfer where user_transfer.pay_type=2 and user_profiles.user_id=user_id order by createddate desc limit 1) as paystatus,
		
		(select iuranpangkal from user_transfer where user_transfer.pay_type=2 and user_profiles.user_id=user_id order by createddate desc limit 1) as payiuranpangkal,
		(select iurantahunan from user_transfer where user_transfer.pay_type=2 and user_profiles.user_id=user_id order by createddate desc limit 1) as payiurantahunan,
		(select sukarelaanggota from user_transfer where user_transfer.pay_type=2 and user_profiles.user_id=user_id order by createddate desc limit 1) as paysukarelaanggota,
		(select sukarelagedung from user_transfer where user_transfer.pay_type=2 and user_profiles.user_id=user_id order by createddate desc limit 1) as paysukarelagedung,
		(select sukarelaperpus from user_transfer where user_transfer.pay_type=2 and user_profiles.user_id=user_id order by createddate desc limit 1) as paysukarelaperpus,
		(select sukarelaceps from user_transfer where user_transfer.pay_type=2 and user_profiles.user_id=user_id order by createddate desc limit 1) as paysukarelaceps,
		(select sukarelatotal from user_transfer where user_transfer.pay_type=2 and user_profiles.user_id=user_id order by createddate desc limit 1) as paysukarelatotal,
		
		(select id from user_transfer where user_transfer.pay_type=2 and user_profiles.user_id=user_id order by createddate desc limit 1) as payid,
		
		(select lic_num from user_cert where user_id=users.id and status=2 order by endyear desc,ip_tipe desc limit 1) as sip_lic_num,
		(select startyear from user_cert where user_id=users.id and status=2 order by endyear desc,ip_tipe desc limit 1) as sip_startyear,
		(select endyear from user_cert where user_id=users.id and status=2 order by endyear desc,ip_tipe desc limit 1) as sip_endyear,
		
		(SELECT id_pay from user_transfer x join log_her_kta y on y.id_pay=x.id WHERE x.pay_type=2 and x.user_id =user_profiles.user_id order by x.createddate desc limit 1) as id_pay, 
		(SELECT x.id from user_transfer x WHERE x.pay_type=2 and x.user_id =user_profiles.user_id order by x.createddate desc limit 1) as id_pay_cek, 

		(SELECT from_date from user_transfer x join log_her_kta y on y.id_pay=x.id 
		WHERE x.pay_type=2 and x.user_id =user_profiles.user_id order by x.createddate desc limit 1) as plan_from_date, 

		(SELECT thru_date from user_transfer x join log_her_kta y on y.id_pay=x.id 
		WHERE x.pay_type=2 and x.user_id =user_profiles.user_id order by x.createddate desc limit 1) as plan_thru_date, 
		
		(select vnv_status from user_transfer where user_transfer.pay_type=2 and user_profiles.user_id=user_id order by createddate desc limit 1) as vnv_status,
		(select remark from user_transfer where user_transfer.pay_type=2 and user_profiles.user_id=user_id order by createddate desc limit 1) as remark,
		
		(select code_wilayah from members where person_id=users.id limit 1) as cab,(select code_bk_hkk from members where person_id=users.id limit 1) as bk,
		(select from_date from members where person_id=users.id limit 1) as from_date,
		(select thru_date from members where person_id=users.id limit 1) as thru_date,
		(select createddate from user_transfer where user_transfer.pay_type=2 and user_profiles.user_id=user_id order by createddate desc limit 1) as tgl_pengajuan
		');
        $this->db->from('user_profiles');
		$this->db->join('users', 'user_profiles.user_id = users.id', 'left');
		//$this->db->join('members', 'user_profiles.user_id = members.person_id', 'left');
		//$this->db->where('username =""', null,false);
		$this->db->where('users.id in (select user_id from user_transfer where pay_type =2)', null,false);
		
		
		if($is_kolektif)
		{
			$str = '';
			$str2 = '';
			$i = 0;
			$i2 = 0;
				
			if(is_array($bk)){
				foreach($bk as $val)
				{
					if($i==0) $str .= " LPAD( code_bk_hkk, 2, '0' ) like '".$val."%'";
					else $str .= " or LPAD( code_bk_hkk, 2, '0' ) like '".$val."%'";
					$i++;
				}
			}
			else if($bk!=""){
				if($i==0) $str .= " LPAD( code_bk_hkk, 2, '0' ) like '".$bk."%'";
				else $str .= " or LPAD( code_bk_hkk, 2, '0' ) like '".$bk."%'";
				$i++;		
			}		
			if(is_array($wil)){
				foreach($wil as $val)
				{
					if($i2==0) $str2 .= " LPAD( code_wilayah, 4, '0' ) like '".$val."%'";
					else $str2 .= " or LPAD( code_wilayah, 4, '0' ) like '".$val."%'";
					$i2++;
				}
			}
			else if($wil!=""){
				if($i2==0) $str2 .= " LPAD( code_wilayah, 4, '0' ) like '".$wil."%'";
				else $str2 .= " or LPAD( code_wilayah, 4, '0' ) like '".$wil."%'";
				$i2++;		
			}
			
			
			$this->db->group_start();
			//$this->db->where('FIND_IN_SET('. $this->session->userdata('admin_id').',kolektif_ids) <>',"0");
			if($str!='') $this->db->where('user_profiles.user_id in (select person_id from members where '.$str.')',null);
			if($str2!='') $this->db->or_where('user_profiles.user_id in (select person_id from members where '.$str2.')',null);
			$this->db->group_end();
		}
		
		$this->db->order_by("(CASE
			WHEN vnv_status = 1 THEN 2
			WHEN vnv_status = 2 THEN 1
			WHEN vnv_status = 0 THEN 0
			ELSE 1 END)", "ASC");
		$this->db->order_by("paystatus", "asc"); 
		$this->db->order_by("tgl_pengajuan", "asc"); 
		//$this->db->order_by("id_pay", "asc"); 
		//$this->db->order_by("paydate", "asc"); 
		$this->db->limit($per_page, $page);
        $Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->result();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
    }
	
	public function get_all_her_kta_2($per_page, $page) {
/*        
		$this->db->select('users.id as ID, username as no_kta, users.email, user_profiles.firstname, user_profiles.lastname, user_profiles.gender, user_profiles.idcard, user_profiles.dob, users.user_status as sts,
		(select atasnama from user_transfer where user_profiles.user_id=user_id order by createddate desc limit 1) as payname,
		(select tgl from user_transfer where user_profiles.user_id=user_id order by createddate desc limit 1) as paydate,
		(select description from user_transfer where user_profiles.user_id=user_id order by createddate desc limit 1) as paydesc,
		(select bukti from user_transfer where user_profiles.user_id=user_id order by createddate desc limit 1) as payfile,
		(select status from user_transfer where user_profiles.user_id=user_id order by createddate desc limit 1) as paystatus,
		
		(select iuranpangkal from user_transfer where user_profiles.user_id=user_id order by createddate desc limit 1) as payiuranpangkal,
		(select iurantahunan from user_transfer where user_profiles.user_id=user_id order by createddate desc limit 1) as payiurantahunan,
		(select sukarelaanggota from user_transfer where user_profiles.user_id=user_id order by createddate desc limit 1) as paysukarelaanggota,
		(select sukarelagedung from user_transfer where user_profiles.user_id=user_id order by createddate desc limit 1) as paysukarelagedung,
		(select sukarelaperpus from user_transfer where user_profiles.user_id=user_id order by createddate desc limit 1) as paysukarelaperpus,
		(select sukarelaceps from user_transfer where user_profiles.user_id=user_id order by createddate desc limit 1) as paysukarelaceps,
		(select sukarelatotal from user_transfer where user_profiles.user_id=user_id order by createddate desc limit 1) as paysukarelatotal,
		
		(select id from user_transfer where user_profiles.user_id=user_id order by createddate desc limit 1) as payid,
		
		(SELECT id_pay FROM log_her_kta JOIN user_transfer ON user_transfer.user_id = log_her_kta.user_id WHERE id_pay <>0 AND log_her_kta.user_id =user_profiles.user_id order by log_her_kta.createddate desc limit 1) as id_pay,
		
		(select code_wilayah from members where person_id=users.id limit 1) as cab,(select code_bk_hkk from members where person_id=users.id limit 1) as bk,(select from_date from members where person_id=users.id limit 1) as from_date,(select thru_date from members where person_id=users.id limit 1) as thru_date
		');
        $this->db->from('user_profiles');
		$this->db->join('users', 'user_profiles.user_id = users.id', 'left');
		//$this->db->join('members', 'user_profiles.user_id = members.person_id', 'left');
		//$this->db->where('username =""', null,false);
		$this->db->where('users.id in (select user_id from user_transfer where pay_type =2)', null,false);
		$this->db->order_by("paystatus", "DESC"); 
		$this->db->order_by("id_pay", "asc"); 
		$this->db->order_by("paydate", "asc");
*/



		$this->db->select('users.id as ID, username as no_kta, users.email, user_profiles.firstname, user_profiles.lastname, user_profiles.gender, user_profiles.idcard, user_profiles.dob, users.user_status as sts,
		
		user_transfer.atasnama as payname,
		user_transfer.tgl as paydate,
		user_transfer.description as paydesc,
		user_transfer.bukti as payfile,
		user_transfer.status as paystatus,
		user_transfer.iuranpangkal as payiuranpangkal,
		user_transfer.iurantahunan as payiurantahunan,
		user_transfer.sukarelaanggota as paysukarelaanggota,
		user_transfer.sukarelagedung as paysukarelagedung,
		user_transfer.sukarelaperpus as paysukarelaperpus,
		user_transfer.sukarelaceps as paysukarelaceps,
		user_transfer.sukarelatotal as paysukarelatotal,
		user_transfer.id as payid,
		
		(SELECT id_pay FROM log_her_kta WHERE id_pay =user_transfer.id order by log_her_kta.createddate desc limit 1) as id_pay,
		
		(select code_wilayah from members where person_id=users.id limit 1) as cab,(select code_bk_hkk from members where person_id=users.id limit 1) as bk,(select from_date from members where person_id=users.id limit 1) as from_date,(select thru_date from members where person_id=users.id limit 1) as thru_date');
        $this->db->from('user_transfer');
		$this->db->join('users', 'user_transfer.user_id = users.id', 'left');
		$this->db->join('user_profiles', 'user_profiles.user_id = user_transfer.user_id', 'left');
		$this->db->where('pay_type =2', null,false);
		$this->db->order_by("paystatus", "DESC"); 
		$this->db->order_by("id_pay", "asc"); 
		$this->db->order_by("paydate", "asc");
		
		$this->db->limit($per_page, $page);
        $Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->result();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
    }
	
	public function get_all_stri_member($per_page, $page) {
        $this->db->select('users.id as ID, username as no_kta, users.email,user_profiles.firstname, user_profiles.lastname, user_profiles.gender, user_profiles.idcard, user_profiles.dob, users.user_status as sts,
		
		user_transfer.atasnama as payname,
		user_transfer.tgl as paydate,
		user_transfer.description as paydesc,
		user_transfer.bukti as payfile,
		user_transfer.status as paystatus,
		
		user_transfer.iuranpangkal as payiuranpangkal,
		user_transfer.iurantahunan as payiurantahunan,
		user_transfer.sukarelaanggota as paysukarelaanggota,
		user_transfer.sukarelagedung as paysukarelagedung,
		user_transfer.sukarelaperpus as paysukarelaperpus,
		user_transfer.sukarelaceps as paysukarelaceps,
		user_transfer.sukarelatotal as paysukarelatotal,
		
		user_transfer.vnv_status as vnv_status,
		user_transfer.remark as remark,
		user_transfer.add_doc1 as add_doc1,
		user_transfer.add_doc2 as add_doc2,
		user_transfer.sip as sip,
		
		(select lic_num from user_cert where user_transfer.rel_id=id and status=2 order by createddate desc limit 1) as lic_num,
		(select cert_title from user_cert where user_transfer.rel_id=id and status=2 order by createddate desc limit 1) as cert_title,
		(select startyear from user_cert where user_transfer.rel_id=id and status=2 order by createddate desc limit 1) as startyear,
		(select endyear from user_cert where user_transfer.rel_id=id and status=2 order by createddate desc limit 1) as endyear,
		
		
		user_transfer.id as payid,
		user_transfer.createddate as createddate,
		
		(SELECT id_pay FROM log_stri WHERE id_pay = user_transfer.id order by log_stri.createddate desc limit 1) as id_pay,
		
		(select code_wilayah from members where person_id=users.id limit 1) as cab,(select code_bk_hkk from members where person_id=users.id limit 1) as bk,(select from_date from members where person_id=users.id limit 1) as from_date,(select thru_date from members where person_id=users.id limit 1) as thru_date,members.jenis_anggota
		,(select name from m_kolektif where status=1 and id=user_profiles.kolektif_name_id) as kolektif_name
		');
        $this->db->from('user_profiles');
		$this->db->join('users', 'user_profiles.user_id = users.id', 'left');
		$this->db->join('user_transfer', 'user_profiles.user_id = user_transfer.user_id', 'left');
		$this->db->join('members', 'user_profiles.user_id = members.person_id', 'left');
		
		//$this->db->where('username =""', null,false);
		
		//$this->db->where('users.id in (select user_id from user_transfer where pay_type =5)', null,false);
		$this->db->where('pay_type', "5");
		$this->db->where('user_transfer.id not in (select id_pay from log_stri)', null,false);
		
		
		$this->db->where('username <> ""', null,false);
		//$this->db->where('user_transfer.vnv_status in (0,1)', null,false);
		
		if($this->session->userdata('type')=="13")
		{
			$this->db->where('rel_id',"0");
		}
		/*else if($this->session->userdata('type')=="12")
		{
			$this->db->where('rel_id<>0',null);
		}*/
		
		//if($this->session->userdata('type')!="13") { $this->db->order_by("vnv_status", "DESC"); }
		//$this->db->order_by("paystatus", "DESC"); 
		//$this->db->order_by("id_pay", "asc"); 
		//$this->db->order_by("paydate", "asc"); 
		
		/*if($this->session->userdata('type')!="13") { 
			$this->db->order_by("(CASE
			WHEN paystatus = 1 THEN 2
			WHEN paystatus = 2 THEN 0
			WHEN paystatus = 0 THEN 1
			ELSE 1 END)", "DESC");
		}*/
		//$this->db->order_by("paystatus", "DESC"); 
				
		//$this->db->order_by("(CASE
		//WHEN id_pay = '' THEN 1
		//ELSE 0 END)", "DESC");
		//$this->db->order_by("id_pay", "asc");
		
		/*if($this->session->userdata('type')=="13") { 
			//$this->db->order_by("(CASE
			//WHEN vnv_status = 1 THEN 1
			//WHEN vnv_status = 2 THEN 0
			//WHEN vnv_status = 0 THEN 2
			//ELSE 2 END)", "DESC");
			
			$this->db->order_by("createddate", "DESC");
		}
		else if($this->session->userdata('type')<>"12" && $this->session->userdata('type')!="13"){
			$this->db->order_by("(CASE
			WHEN vnv_status = 1 THEN 2
			WHEN vnv_status = 2 THEN 0
			WHEN vnv_status = 0 THEN 1
			ELSE 1 END)", "DESC");
			//$this->db->order_by("vnv_status", "DESC");
		}*/
		
		/*if($this->session->userdata('type')=="2") { $this->db->order_by("(CASE
		WHEN username = '' THEN 1
		ELSE 0 END)", "desc"); }*/
		
		/*if($this->session->userdata('type')=="12") { 
			$this->db->order_by("(CASE
			WHEN id_pay <> '' THEN 0
			ELSE 1 END)", "DESC");
			
			$this->db->order_by("(CASE
			WHEN username <> '' THEN 1
			ELSE 0 END)", "desc");
			
			$this->db->order_by("(CASE
			WHEN rel_id = 0 THEN (CASE
			WHEN vnv_status = 1 THEN 2
			WHEN vnv_status = 2 THEN 0
			WHEN vnv_status = 0 THEN 1
			ELSE 1 END)
			ELSE 2 END)", "DESC");
			
			//$this->db->order_by("(CASE
			//WHEN rel_id <> 0 THEN (CASE WHEN vnv_status=1 THEN 1 ELSE 0 END)
			//ELSE 1 END)", "desc");
		}*/
		
		//$this->db->order_by("paydate", "asc"); 
		$this->db->order_by("user_transfer.createddate", "desc"); 
		$this->db->limit($per_page, $page);
        $Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->result();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
    }
	
	public function get_all_stri($per_page, $page,$bk,$wil,$is_kolektif) {
        $this->db->select('e.createddate as tgl,members_certificate.person_id as ID,add_name, members_certificate.status as sts,m_kolektif.category as ishkk,members_certificate.*');//(select name from m_bk where value= members_stri.code_bk_hkk) as kejuruan
        
		
		//$this->db->select('person_id as ID,user_profiles.firstname as firstname, user_profiles.lastname, status as sts,members_certificate.*');
		$this->db->from('members_certificate');//user_profiles.firstname
		$this->db->join('user_profiles', 'user_profiles.user_id = members_certificate.person_id');
		$this->db->join('m_kolektif', 'user_profiles.kolektif_name_id = m_kolektif.id', 'left');
		$this->db->join('members', 'members_certificate.person_id = members.person_id');
		$this->db->join('log_stri b', 'b.id = (
			SELECT
			  max(fa.id) a_id
			FROM log_stri fa 
			WHERE fa.stri_id = members_certificate.stri_id and fa.user_id=members_certificate.person_id
		  )', 'left',false);
		$this->db->join('user_transfer e', 'e.id = b.id_pay', 'left');
		//$this->db->where('stri_code_wilayah <> ""', null,false);
		//$this->db->order_by("members_stri.id", "DESC"); 
		
		$this->db->where('members_certificate.status', "1");
		
		if($is_kolektif)
		{
			$str = '';
			$str2 = '';
			$i = 0;
			$i2 = 0;
				
			if(is_array($bk)){
				foreach($bk as $val)
				{
					if($i==0) $str .= " LPAD( stri_code_bk_hkk, 2, '0' ) like '".$val."%'";
					else $str .= " or LPAD( stri_code_bk_hkk, 2, '0' ) like '".$val."%'";
					$i++;
				}
			}
			else if($bk!=""){
				if($i==0) $str .= " LPAD( stri_code_bk_hkk, 2, '0' ) like '".$bk."%'";
				else $str .= " or LPAD( stri_code_bk_hkk, 2, '0' ) like '".$bk."%'";
				$i++;		
			}		
			if(is_array($wil)){
				foreach($wil as $val)
				{
					if($i2==0) $str2 .= " LPAD( code_wilayah, 4, '0' ) like '".$val."%'";
					else $str2 .= " or LPAD( code_wilayah, 4, '0' ) like '".$val."%'";
					$i2++;
				}
			}
			else if($wil!=""){
				if($i2==0) $str2 .= " LPAD( code_wilayah, 4, '0' ) like '".$wil."%'";
				else $str2 .= " or LPAD( code_wilayah, 4, '0' ) like '".$wil."%'";
				$i2++;		
			}
			
			
			$this->db->group_start();
			//$this->db->where('FIND_IN_SET('. $this->session->userdata('admin_id').',kolektif_ids) <>',"0");
			if($str!='') $this->db->where($str,null);
			if($str2!='') $this->db->where('members_certificate.person_id in (select person_id from members where '.$str2.')',null);
			$this->db->group_end();
		}
		
		$this->db->limit($per_page, $page);
        $Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->result();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
    }
	
	/**
	 * ER: FIXME: Remove this function
	 * @deprecated table `skip3` is not exist
	 */
	public function get_all_skip($per_page, $page) {
        $this->db->select('id as ID,a.*');
		//$this->db->select('person_id as ID, add_name as firstname, user_profiles.lastname, status as sts,members_certificate.*');//,(select name from m_bk where value= members_ip.code_bk_hkk) as kejuruan user_profiles.firstname
        
		
		
		$this->db->from('skip3 a');
		$this->db->where('sk_end > now()', null,false);
		$this->db->where('noip<>""', null,false);
		$this->db->group_by('kta,sk_from,sertid');
		$this->db->having('sertid=(select max(sertid) from skip3 where kta=a.kta)');
		$this->db->having('sk_from=(select max(sk_from) from skip3 where kta=a.kta)');
		$this->db->order_by("sk_end", "asc"); 
		
		
		//$this->db->join('user_profiles', 'user_profiles.user_id = members_certificate.person_id', 'left');
		//$this->db->join('members', 'user_profiles.user_id = members.person_id', 'left');
		//$this->db->where('username =""', null,false);
		//$this->db->where('skip_id <> ""', null,false);
		//$this->db->order_by("members_pi.id", "DESC"); 
		$this->db->limit($per_page, $page);
        $Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->result();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
    }
	
	public function get_all_pi($per_page, $page) {
        $this->db->select('person_id as ID, add_name as firstname, user_profiles.lastname, status as sts,members_certificate.*');//,(select name from m_bk where value= members_ip.code_bk_hkk) as kejuruan user_profiles.firstname
        $this->db->from('members_certificate');
		$this->db->join('user_profiles', 'user_profiles.user_id = members_certificate.person_id', 'left');
		//$this->db->join('members', 'user_profiles.user_id = members.person_id', 'left');
		//$this->db->where('username =""', null,false);
		$this->db->where('skip_id <> ""', null,false);
		//$this->db->order_by("members_pi.id", "DESC"); 
		$this->db->limit($per_page, $page);
        $Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->result();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
    }

	public function record_count_faip($table_name) {
		$this->db->select('user_faip.id');
		$this->db->from('user_faip');
		$this->db->join('user_profiles', 'user_profiles.user_id = user_faip.user_id', 'left');
		$this->db->where('status', 1);
		$this->db->where('status_faip<>0');
		
		
		if(!isAdminLSKI() && !isAdminMajelisAll() && !isAdminBKWilayahKolektif() && !isAdminKolektifRO() 
			&& !empty($this->session->userdata('code_bk_hkk')) )
		{
			$this->db->where("TRIM(LEADING '0' FROM bidang) = ".ltrim($this->session->userdata('code_bk_hkk'), '0'),null);
			$this->db->where('status_faip>=5');
		}
		else if($this->session->userdata('type')=="7")
		{
			//$this->db->where("TRIM(LEADING '0' FROM bidang) = ".ltrim($this->session->userdata('code_bk_hkk'), '0'),null);
			
			$this->db->where('status_faip>=6');
			
			$this->db->group_start()->where('majelis1', $this->session->userdata('admin_id'))->or_where('majelis2', $this->session->userdata('admin_id'))->or_where('majelis3', $this->session->userdata('admin_id'))->group_end();
		}
		else if(isAdminBKWilayahKolektif() || isAdminKolektifRO())
		{
			// User BK
			if ( ! empty($this->session->userdata('code_bk_hkk')) ) {
				$this->db->where("TRIM(LEADING '0' FROM bidang) = ".ltrim($this->session->userdata('code_bk_hkk'), '0'),null);
			}
			else if (isAdminKolektif() || isAdminKolektifRO()) {
				$this->db
					->group_start()
						->where("FIND_IN_SET(".$this->session->userdata('admin_id').",user_profiles.kolektif_ids) = 1",null)
						->or_where("user_profiles.kolektif_name_id IN (select kolektif_id from admin_kolektif_map where admin_id = ".$this->session->userdata('admin_id').")",null)
						->or_where("user_profiles.kolektif_name_id",$this->session->userdata('kode_kolektif'))
					->group_end();
			}
			//$this->db->where('status_faip>=6');
		}
		
		$Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->num_rows();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
		
		//return $this->db->count_all();
    }

	public function get_all_faip($per_page, $page) {
        $this->db->select('(select CONCAT(COALESCE(firstname,""),COALESCE(lastname,"")) from user_profiles where user_id=user_faip.majelis1) as asesor1,(select CONCAT(COALESCE(firstname,""),COALESCE(lastname,"")) from user_profiles where user_id=user_faip.majelis2) as asesor2,(select CONCAT(COALESCE(firstname,""),COALESCE(lastname,"")) from user_profiles where user_id=user_faip.majelis3) as asesor3,user_faip.id as ID,(select name from m_cab where TRIM(LEADING "0" FROM value)=TRIM(LEADING "0" FROM members.code_wilayah)) as wil_name,(select name from m_bk where TRIM(LEADING "0" FROM value)=TRIM(LEADING "0" FROM members.code_bk_hkk)) as bk_name,(select name from m_bk where TRIM(LEADING "0" FROM value)=TRIM(LEADING "0" FROM user_faip.bidang)) as bk_faip,members.code_bk_hkk as bk,members.code_wilayah as wil,(select name from m_faip_status where value=user_faip.status_faip) as status_name, user_faip.*,
		(select GROUP_CONCAT(createdby) from asesor_faip where status=2 and faip_id=user_faip.id) as score_id,
		(select GROUP_CONCAT(wajib1_score) from asesor_faip where status=2 and faip_id=user_faip.id) as wajib1_score_,
		(select GROUP_CONCAT(wajib2_score) from asesor_faip where status=2 and faip_id=user_faip.id) as wajib2_score_,
		(select GROUP_CONCAT(wajib3_score) from asesor_faip where status=2 and faip_id=user_faip.id) as wajib3_score_,
		(select GROUP_CONCAT(wajib4_score) from asesor_faip where status=2 and faip_id=user_faip.id) as wajib4_score_,
		(select GROUP_CONCAT(pilihan_score) from asesor_faip where status=2 and faip_id=user_faip.id) as pilihan_score_,
		(select GROUP_CONCAT(total_score) from asesor_faip where status=2 and faip_id=user_faip.id) as score,
		(select GROUP_CONCAT(keputusan) from asesor_faip where status=2 and faip_id=user_faip.id) as keputusan,
		(select keputusan from asesor_faip join admin on asesor_faip.createdby=admin.id where type=11 and TRIM(LEADING "0" FROM code_bk_hkk) =TRIM(LEADING "0" FROM user_faip.bidang) and status=2 and faip_id=user_faip.id order by asesor_faip.id desc limit 1) as keputusan_bk,
		(select total_score from asesor_faip join admin on asesor_faip.createdby=admin.id where type=11 and TRIM(LEADING "0" FROM code_bk_hkk) =TRIM(LEADING "0" FROM user_faip.bidang) and status=2 and faip_id=user_faip.id order by asesor_faip.id desc limit 1) as score_bk,
		
		(select wajib1_score from asesor_faip join admin on asesor_faip.createdby=admin.id where type=11 and TRIM(LEADING "0" FROM code_bk_hkk) =TRIM(LEADING "0" FROM user_faip.bidang) and status=2 and faip_id=user_faip.id order by asesor_faip.id desc limit 1) as wajib1_score_bk,
		(select wajib2_score from asesor_faip join admin on asesor_faip.createdby=admin.id where type=11 and TRIM(LEADING "0" FROM code_bk_hkk) =TRIM(LEADING "0" FROM user_faip.bidang) and status=2 and faip_id=user_faip.id order by asesor_faip.id desc limit 1) as wajib2_score_bk,
		(select wajib3_score from asesor_faip join admin on asesor_faip.createdby=admin.id where type=11 and TRIM(LEADING "0" FROM code_bk_hkk) =TRIM(LEADING "0" FROM user_faip.bidang) and status=2 and faip_id=user_faip.id order by asesor_faip.id desc limit 1) as wajib3_score_bk,
		(select wajib4_score from asesor_faip join admin on asesor_faip.createdby=admin.id where type=11 and TRIM(LEADING "0" FROM code_bk_hkk) =TRIM(LEADING "0" FROM user_faip.bidang) and status=2 and faip_id=user_faip.id order by asesor_faip.id desc limit 1) as wajib4_score_bk,
		(select pilihan_score from asesor_faip join admin on asesor_faip.createdby=admin.id where type=11 and TRIM(LEADING "0" FROM code_bk_hkk) =TRIM(LEADING "0" FROM user_faip.bidang) and status=2 and faip_id=user_faip.id order by asesor_faip.id desc limit 1) as pilihan_score_bk,
		
		remarks,user_faip.keputusan as keputusan_manual,
		
		(select createddate from log_status_faip where faip_id=user_faip.id and new_status=11 order by createddate desc limit 1) as tgl_sip_to_print
		
		');//,(select name from m_bk where value= members_ip.code_bk_hkk) as kejuruan user_profiles.firstname
		//(select GROUP_CONCAT(keputusan) from asesor_faip where status=2 and faip_id=user_faip.id) as keputusan,
		//(select name from admin where id=user_faip.majelis1)
		
		//(select lic_num from user_cert where status=2 and cert_url=user_faip.id order by id desc limit 1) as lic_num,
		
        $this->db->from('user_faip');
		// 12Jul2024 ER: Tiba-tiba ini bikin error query yang di-generate oleh CI jadi dua kali join ke user_profiles
		// Sementara ditutup dan masih jalan dengan baik
		//$this->db->join('user_profiles', 'user_profiles.user_id = user_faip.user_id', 'left');
		$this->db->join('members', 'members.person_id = user_faip.user_id', 'left');
		$this->db->join('m_faip_status', 'm_faip_status.value = user_faip.status_faip', 'left');
		$this->db->where('user_faip.status', 1);
		$this->db->where('status_faip<>0');
		
		if($this->session->userdata('type')!="0" && $this->session->userdata('type')!="1" && $this->session->userdata('type')!="7" && $this->session->userdata('type')!="11" && $this->session->userdata('type')!="15")
		{
			$this->db->where("TRIM(LEADING '0' FROM bidang) = ".ltrim($this->session->userdata('code_bk_hkk'), '0'),null);
			$this->db->where('status_faip>=5');
		}
		else if($this->session->userdata('type')=="7")
		{
			//$this->db->where("TRIM(LEADING '0' FROM bidang) = ".ltrim($this->session->userdata('code_bk_hkk'), '0'),null);
			
			$this->db->where('status_faip>=6');
			
			$this->db->group_start()->where('majelis1', $this->session->userdata('admin_id'))->or_where('majelis2', $this->session->userdata('admin_id'))->or_where('majelis3', $this->session->userdata('admin_id'))->group_end();
		}
		else if($this->session->userdata('type')=="11" || $this->session->userdata('type')=="15")
		{
			$this->db->where("TRIM(LEADING '0' FROM bidang) = ".ltrim($this->session->userdata('code_bk_hkk'), '0'),null);
			//$this->db->where('status_faip>=6');
		}
		
		if($this->session->userdata('type')=="0" || $this->session->userdata('type')=="1")
		{
			$this->db->order_by("m_faip_status.seq_lski", "desc"); 
		}
		
		//$this->db->order_by("user_faip.status_faip", "asc"); 
		//$this->db->order_by("user_faip.createddate", "DESC"); 
		
		if($this->session->userdata('type')=="0" || $this->session->userdata('type')=="1")
			$this->db->order_by("(select createddate from log_status_faip where faip_id=user_faip.id and new_status=11 order by createddate desc limit 1)", "DESC"); 
		else
		{
			$this->db->order_by("(select createddate from log_status_faip where faip_id=user_faip.id and old_status=0 and new_status=1 order by createddate asc limit 1)", "desc"); 
			$this->db->order_by("user_faip.id", "desc"); 
		}
		$this->db->limit($per_page, $page);
        $Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->result();
        } else {
            $return = 0;
        }
        $Q->free_result();
		log_message('debug', '[SIMPONI] '.__CLASS__.'@'.__FUNCTION__." query: \n" . $this->db->last_query());
        return $return;
    }	
	
	public function get_all_majelis($per_page, $page) {
        $this->db->select('admin.*,m_title.desc as title');//,(select name from m_bk where value= members_ip.code_bk_hkk) as kejuruan user_profiles.firstname
		$this->db->join('m_title', 'm_title.id = admin.type', 'left');
        $this->db->from('admin');
		$this->db->where('type',7);		
		if($this->session->userdata('type')!="0" && $this->session->userdata('type')!="1")
			$this->db->where("TRIM(LEADING '0' FROM code_bk_hkk) = ".ltrim($this->session->userdata('code_bk_hkk'), '0'),null);
		
		//$this->db->order_by("user_faip.createddate", "DESC"); 
		$this->db->limit($per_page, $page);
        $Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->result();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
    }
	
	public function get_all_bp($per_page, $page) {
        $this->db->select('*');//,(select name from m_bk where value= members_ip.code_bk_hkk) as kejuruan user_profiles.firstname
		//$this->db->join('m_title', 'm_title.id = admin.type', 'left');
        $this->db->from('m_bakuan_penilaian');
		//$this->db->where('type>2');
		//$this->db->order_by("user_faip.createddate", "DESC"); 
		$this->db->limit($per_page, $page);
        $Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->result();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
    }
	
	public function get_majelis_p_by_bk($id) {
        $this->db->select('*');
        $this->db->from('admin');
		$this->db->where('type',3);
		$this->db->where('code_bk_hkk',$id);
		//$this->db->join('user_profiles', 'users.id = user_profiles.user_id', 'inner');
		//$this->db->join('pp_job_industries', 'pp_companies.industry_ID = pp_job_industries.ID', 'left');
		//$this->db->where('users.id', $id);
        $Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->result();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
    }
	
	public function get_majelis_q_by_bk($id) {
        $this->db->select('*');
        $this->db->from('admin');
		$this->db->where('type',4);
		$this->db->where('code_bk_hkk',$id);
		//$this->db->join('user_profiles', 'users.id = user_profiles.user_id', 'inner');
		//$this->db->join('pp_job_industries', 'pp_companies.industry_ID = pp_job_industries.ID', 'left');
		//$this->db->where('users.id', $id);
        $Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->result();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
    }
	
	public function get_majelis_r_by_bk($id) {
        $this->db->select('*');
        $this->db->from('admin');
		$this->db->where('type',5);
		$this->db->where('code_bk_hkk',$id);
		//$this->db->join('user_profiles', 'users.id = user_profiles.user_id', 'inner');
		//$this->db->join('pp_job_industries', 'pp_companies.industry_ID = pp_job_industries.ID', 'left');
		//$this->db->where('users.id', $id);
        $Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->result();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
    }
	
	public function get_stri_member_by_id($id) {
        $this->db->select('*');
        $this->db->from('user_cert');
		$this->db->join('user_transfer', 'user_cert.id = user_transfer.rel_id', 'inner');
		
		$this->db->where('user_cert.status',2);
		$this->db->where('user_transfer.id',$id);
		
		//$this->db->join('pp_job_industries', 'pp_companies.industry_ID = pp_job_industries.ID', 'left');
		//$this->db->where('users.id', $id);
        $Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->result();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
    }

	public function get_all_pkb($per_page, $page) {
        $this->db->select('(select CONCAT(COALESCE(firstname,""),COALESCE(lastname,"")) from user_profiles where user_id=user_pkb.majelis1) as asesor1,
		(select CONCAT(COALESCE(firstname,""),COALESCE(lastname,"")) from user_profiles where user_id=user_pkb.majelis2) as asesor2,
		(select CONCAT(COALESCE(firstname,""),COALESCE(lastname,"")) from user_profiles where user_id=user_pkb.majelis3) as asesor3,
		user_pkb.id as ID,
		(select name from m_cab where TRIM(LEADING "0" FROM value)=TRIM(LEADING "0" FROM members.code_wilayah)) as wil_name,
		(select name from m_bk where TRIM(LEADING "0" FROM value)=TRIM(LEADING "0" FROM members.code_bk_hkk)) as bk_name,
		(select name from m_bk where TRIM(LEADING "0" FROM value)=TRIM(LEADING "0" FROM user_pkb.bidang)) as bk_pkb,
		members.code_bk_hkk as bk,members.code_wilayah as wil,
(select name from m_pkb_status where value=v_status_pkb_terakhir.new_status) as status_name, 
v_status_pkb_terakhir.new_status as status_pkb,user_pkb.id,user_pkb.user_id, user_pkb.sip_id,user_pkb.remarks,user_pkb.jenis_pkb,user_pkb.no_kta,user_pkb.no_sip,
user_pkb.masa_sip,user_pkb.no_stri, user_pkb.masa_stri, user_pkb.nama,user_pkb.periodstart,user_pkb.periodend,
user_pkb.subkejuruan,user_pkb.bidang, user_pkb.bidang_tujuan, user_pkb.faip_type, user_pkb.certificate_type,
user_pkb.pernyataan,user_pkb.wkt_pernyataan,user_pkb.wajib1_score,user_pkb.wajib2_score,user_pkb.wajib3_score,
user_pkb.wajib4_score,user_pkb.wajib5_score,user_pkb.total_score,user_pkb.keputusan,user_pkb.tempat,user_pkb.tgl_keputusan,
user_pkb.majelis1,user_pkb.majelis2,user_pkb.majelis3,user_pkb.remarks,user_pkb.status,user_pkb.need_revisi,
user_pkb.revisi_note,user_pkb.interview_date,user_pkb.interview_start_hour,user_pkb.interview_end_hour,
user_pkb.interview_loc,user_pkb.hasil_keputusan,user_pkb.hasil_tipe,user_pkb.upgrade,user_pkb.upgrade_date,
user_pkb.is_upgrade_paid,user_pkb.createddate,user_pkb.createdby,user_pkb.modifieddate,user_pkb.modifiedby,
		(select GROUP_CONCAT(createdby) from asesor_pkb where status=2 and pkb_id=user_pkb.id) as score_id,
		(select GROUP_CONCAT(wajib1_score) from asesor_pkb where status=2 and pkb_id=user_pkb.id) as wajib1_score_,
		(select GROUP_CONCAT(wajib2_score) from asesor_pkb where status=2 and pkb_id=user_pkb.id) as wajib2_score_,
		(select GROUP_CONCAT(wajib3_score) from asesor_pkb where status=2 and pkb_id=user_pkb.id) as wajib3_score_,
		(select GROUP_CONCAT(wajib4_score) from asesor_pkb where status=2 and pkb_id=user_pkb.id) as wajib4_score_,
		(select GROUP_CONCAT(wajib5_score) from asesor_pkb where status=2 and pkb_id=user_pkb.id) as wajib5_score_,
		(select GROUP_CONCAT(total_score) from asesor_pkb where status=2 and pkb_id=user_pkb.id) as score,
		(select GROUP_CONCAT(keputusan) from asesor_pkb where status=2 and pkb_id=user_pkb.id) as keputusan,
		(select lic_num from user_cert where status=2 and cert_url=user_pkb.id order by id desc limit 1) as lic_num
		');//,(select name from m_bk where value= members_ip.code_bk_hkk) as kejuruan user_profiles.firstname
		
		//(select name from admin where id=user_pkb.majelis1)
		
        $this->db->from('user_pkb');
		$this->db->join('v_status_pkb_terakhir', 'user_pkb.id = v_status_pkb_terakhir.pkb_id', 'left');
		$this->db->join('user_profiles', 'user_profiles.user_id = user_pkb.user_id', 'left');
		$this->db->join('members', 'members.person_id = user_pkb.user_id', 'left');
		$this->db->join('m_pkb_status', 'm_pkb_status.value = user_pkb.status_pkb', 'left');
		$this->db->where('user_pkb.status', 1);
		$this->db->where('status_pkb<>0');
		
		if($this->session->userdata('type')!="0" && $this->session->userdata('type')!="1" && $this->session->userdata('type')!="7" && $this->session->userdata('type')!="11" && $this->session->userdata('type')!="15" && $this->session->userdata('type')!="16")
		{
			$this->db->where("TRIM(LEADING '0' FROM bidang) = ".ltrim($this->session->userdata('code_bk_hkk'), '0'),null);
			$this->db->where('status_pkb>=5');
		}
		else if($this->session->userdata('type')=="7")
		{
			//$this->db->where("TRIM(LEADING '0' FROM bidang) = ".ltrim($this->session->userdata('code_bk_hkk'), '0'),null);
			
			$this->db->where('status_pkb>=6');
			
			$this->db->group_start()->where('majelis1', $this->session->userdata('admin_id'))->or_where('majelis2', $this->session->userdata('admin_id'))->or_where('majelis3', $this->session->userdata('admin_id'))->group_end();
		}
		else if($this->session->userdata('type')=="11" || $this->session->userdata('type')=="15")
		{
			$this->db->where("TRIM(LEADING '0' FROM bidang) = ".ltrim($this->session->userdata('code_bk_hkk'), '0'),null);
			//$this->db->where('status_pkb>=6');
		}
		
		if($this->session->userdata('type')=="0" || $this->session->userdata('type')=="1" || $this->session->userdata('type')=="16")
		{
			$this->db->order_by("m_pkb_status.seq_lski", "desc"); 
		}
		
		$this->db->order_by("user_pkb.status_pkb", "asc"); 
		$this->db->order_by("user_pkb.createddate", "DESC"); 
		$this->db->limit($per_page, $page);
        $Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->result();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
    }
	
	
	/*public function get_all_stri($per_page, $page) {
        $this->db->select('person_id as ID, user_profiles.firstname, user_profiles.lastname, status as sts,members_stri.*');//(select name from m_bk where value= members_stri.code_bk_hkk) as kejuruan
        $this->db->from('members_stri');
		$this->db->join('user_profiles', 'user_profiles.user_id = members_stri.person_id', 'left');
		//$this->db->join('members', 'user_profiles.user_id = members.person_id', 'left');
		//$this->db->where('username =""', null,false);
		//$this->db->order_by("members_stri.id", "DESC"); 
		$this->db->limit($per_page, $page);
        $Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->result();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
    }
	
	public function get_all_pi($per_page, $page) {
        $this->db->select('person_id as ID, user_profiles.firstname, user_profiles.lastname, status as sts,members_ip.*');//,(select name from m_bk where value= members_ip.code_bk_hkk) as kejuruan
        $this->db->from('members_ip');
		$this->db->join('user_profiles', 'user_profiles.user_id = members_ip.person_id', 'left');
		//$this->db->join('members', 'user_profiles.user_id = members.person_id', 'left');
		//$this->db->where('username =""', null,false);
		//$this->db->order_by("members_pi.id", "DESC"); 
		$this->db->limit($per_page, $page);
        $Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->result();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
    }*/
	
	public function record_count($table_name) {
		$this->db->from('user_profiles');
		$this->db->join('users', 'user_profiles.user_id = users.id', 'left');
		return $this->db->count_all_results();
		//return $this->db->count_all($table_name);
    }
	
	public function record_count_v2($table_name,$bk,$wil,$is_kolektif) {
		$this->db->from('user_profiles');
		$this->db->join('users', 'user_profiles.user_id = users.id', 'left');
	 	$this->db->join('members', 'user_profiles.user_id = members.person_id', 'left');

//------------------------------------------------------------------------------------------- Tambahan by IP	
           if (strlen(trim($wil)) != 0 ) {
		if (strlen(trim($wil)) != 2 ) {
			$this->db->where('members.code_wilayah', $wil) ; 
		}

                if (strlen(trim($wil)) == 2 ) {
			$this->db->where('members.wil_id', $wil) ; 				
		}
	    }	
	    
	if (strlen(trim($bk)) != 0 ) {
	//	$this->db->where('members.code_bk_hkk', $bk) ; 
		$this->db->where('LPAD(members.code_bk_hkk,2,"0")', $bk) ;
	}		    
//--------------------------------------------------------------------

	if($is_kolektif)
	{
		$str = '';
		$i = 0;
			
		if(is_array($bk)){
			foreach($bk as $val)
			{
				if($i==0) $str .= " LPAD( code_bk_hkk, 2, '0' ) like '".$val."%'";
				else $str .= " or LPAD( code_bk_hkk, 2, '0' ) like '".$val."%'";
				$i++;
			}
		}
		else if($bk!=""){
			if($i==0) $str .= " LPAD( code_bk_hkk, 2, '0' ) like '".$bk."%'";
			else $str .= " or LPAD( code_bk_hkk, 2, '0' ) like '".$bk."%'";
			$i++;		
		}		
		if(is_array($wil)){
			foreach($wil as $val)
			{
				if($i==0) $str .= " LPAD( code_wilayah, 4, '0' ) like '".$val."%'";
				else $str .= " or LPAD( code_wilayah, 4, '0' ) like '".$val."%'";
				$i++;
			}
		}
		else if($wil!=""){
			if($i==0) $str .= " LPAD( code_wilayah, 4, '0' ) like '".$wil."%'";
			else $str .= " or LPAD( code_wilayah, 4, '0' ) like '".$wil."%'";
			$i++;		
		}
		
		
		$this->db->group_start();
		$this->db->where('FIND_IN_SET('. $this->session->userdata('admin_id').',kolektif_ids) <>',"0");
		if($str!='') $this->db->or_where('user_profiles.user_id in (select person_id from members where '.$str.')',null);
		$this->db->group_end();
	}	
		
		return $this->db->count_all_results();
		//return $this->db->count_all($table_name);
    }
	
	public function record_count_non_kta($table_name,$bk,$wil,$is_kolektif) {
		$this->db->select('user_profiles.id');
		$this->db->from('user_profiles');
		$this->db->join('users', 'user_profiles.user_id = users.id', 'left');
		$this->db->where('username = ""', null,false);
		//$this->db->where('user_id not in (select user_id from user_transfer where pay_type=5)', null,false);
		$this->db->where('user_id in (select user_id from user_transfer where pay_type=1)', null,false);
		
		if($is_kolektif)
		{
			$str = '';
			$str2 = '';
			$i = 0;
			$i2 = 0;
				
			if(is_array($bk)){
				foreach($bk as $val)
				{
					if($i==0) $str .= " LPAD( code_bk_hkk, 2, '0' ) like '".$val."%'";
					else $str .= " or LPAD( code_bk_hkk, 2, '0' ) like '".$val."%'";
					$i++;
				}
			}
			else if($bk!=""){
				if($i==0) $str .= " LPAD( code_bk_hkk, 2, '0' ) like '".$bk."%'";
				else $str .= " or LPAD( code_bk_hkk, 2, '0' ) like '".$bk."%'";
				$i++;		
			}		
			if(is_array($wil)){
				foreach($wil as $val)
				{
					if($i2==0) $str2 .= " LPAD( code_wilayah, 4, '0' ) like '".$val."%'";
					else $str2 .= " or LPAD( code_wilayah, 4, '0' ) like '".$val."%'";
					$i2++;
				}
			}
			else if($wil!=""){
				if($i2==0) $str2 .= " LPAD( code_wilayah, 4, '0' ) like '".$wil."%'";
				else $str2 .= " or LPAD( code_wilayah, 4, '0' ) like '".$wil."%'";
				$i2++;		
			}
			
			
			$this->db->group_start();
			//$this->db->where('FIND_IN_SET('. $this->session->userdata('admin_id').',kolektif_ids) <>',"0");
			if($str!='') $this->db->where('user_profiles.user_id in (select person_id from members where '.$str.')',null);
			if($str2!='') $this->db->or_where('user_profiles.user_id in (select person_id from members where '.$str2.')',null);
			$this->db->group_end();
		}
		
		$Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->num_rows();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
		
		//return $this->db->count_all();
    }
	
	public function record_count_her_kta($table_name,$bk,$wil,$is_kolektif) {
		$this->db->select('user_profiles.id');
		$this->db->from('user_profiles');
		$this->db->join('users', 'user_profiles.user_id = users.id', 'left');
		$this->db->where('users.id in (select user_id from user_transfer where pay_type =2)', null,false);
		
		if($is_kolektif)
		{
			$str = '';
			$str2 = '';
			$i = 0;
			$i2 = 0;
				
			if(is_array($bk)){
				foreach($bk as $val)
				{
					if($i==0) $str .= " LPAD( code_bk_hkk, 2, '0' ) like '".$val."%'";
					else $str .= " or LPAD( code_bk_hkk, 2, '0' ) like '".$val."%'";
					$i++;
				}
			}
			else if($bk!=""){
				if($i==0) $str .= " LPAD( code_bk_hkk, 2, '0' ) like '".$bk."%'";
				else $str .= " or LPAD( code_bk_hkk, 2, '0' ) like '".$bk."%'";
				$i++;		
			}		
			if(is_array($wil)){
				foreach($wil as $val)
				{
					if($i2==0) $str2 .= " LPAD( code_wilayah, 4, '0' ) like '".$val."%'";
					else $str2 .= " or LPAD( code_wilayah, 4, '0' ) like '".$val."%'";
					$i2++;
				}
			}
			else if($wil!=""){
				if($i2==0) $str2 .= " LPAD( code_wilayah, 4, '0' ) like '".$wil."%'";
				else $str2 .= " or LPAD( code_wilayah, 4, '0' ) like '".$wil."%'";
				$i2++;		
			}
			
			
			$this->db->group_start();
			//$this->db->where('FIND_IN_SET('. $this->session->userdata('admin_id').',kolektif_ids) <>',"0");
			if($str!='') $this->db->where('user_profiles.user_id in (select person_id from members where '.$str.')',null);
			if($str2!='') $this->db->or_where('user_profiles.user_id in (select person_id from members where '.$str2.')',null);
			$this->db->group_end();
		}
		
		
		$Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->num_rows();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
		
		//return $this->db->count_all();
    }
	
	public function record_count_her_kta_2($table_name) {
		$this->db->select('user_profiles.id');
		
		/*$this->db->from('user_profiles');
		$this->db->join('users', 'user_profiles.user_id = users.id', 'left');
		$this->db->where('users.id in (select user_id from user_transfer where pay_type =2)', null,false);
		*/
		
		$this->db->from('user_transfer');
		$this->db->join('users', 'user_transfer.user_id = users.id', 'left');
		$this->db->join('user_profiles', 'user_profiles.user_id = user_transfer.user_id', 'left');
		$this->db->where('pay_type =2', null,false);
		
		$Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->num_rows();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
		
		//return $this->db->count_all();
    }
	
	public function record_count_stri_member($table_name) {
		$this->db->select('user_profiles.id');
		$this->db->from('user_profiles');
		$this->db->join('users', 'user_profiles.user_id = users.id', 'left');
		$this->db->join('user_transfer', 'user_profiles.user_id = user_transfer.user_id', 'left');
		$this->db->join('members', 'user_profiles.user_id = members.person_id', 'left');
		
		//$this->db->where('username =""', null,false);
		
		//$this->db->where('users.id in (select user_id from user_transfer where pay_type =5)', null,false);
		$this->db->where('pay_type', "5");
		$this->db->where('user_transfer.id not in (select id_pay from log_stri)', null,false);
		
		$this->db->where('username <> ""', null,false);
		//$this->db->where('user_transfer.vnv_status in (0,1)', null,false);
		
		if($this->session->userdata('type')=="13")
		{
			$this->db->where('rel_id',"0");
		}
		/*else if($this->session->userdata('type')=="12")
		{
			$this->db->where('rel_id<>0',null);
		}*/
		
		$Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->num_rows();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
		
		//return $this->db->count_all();
    }
	
	public function record_count_finance($table_name) {
		$this->db->select('user_profiles.id');
		$this->db->from('user_profiles');
		$this->db->join('users', 'user_profiles.user_id = users.id', 'left');
		//$this->db->where('username = ""', null,false);
		$this->db->where('users.id in (select user_id from user_transfer)', null,false);
		
		$Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->num_rows();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
		
		//return $this->db->count_all();
    }
	
	public function record_count_finance_2($table_name) {
		$this->db->select('user_profiles.id');
		 $this->db->from('user_transfer');
		$this->db->join('users', 'user_transfer.user_id = users.id', 'left');
		$this->db->join('user_profiles', 'user_profiles.user_id = user_transfer.user_id', 'left');		
		$this->db->join('members', 'user_transfer.user_id = members.person_id', 'left');
		$this->db->where('(CASE WHEN pay_type=5 THEN vnv_status=1 ELSE 1=1 END)', null,false);//and rel_id=0 
		if($this->session->userdata('admin_id')=="673" ){
			$this->db->where('pay_type in (3,4)', null,false);
		}
		else if($this->session->userdata('admin_id')=="675" ){
			$this->db->where('pay_type in (1,2)', null,false);
		}
		$this->db->group_by('user_transfer.user_id,user_transfer.pay_type');
		
		$Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->num_rows();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
		
		//return $this->db->count_all();
    }
	
	public function record_count_stri($table_name,$bk,$wil,$is_kolektif) {
		$this->db->select('members_certificate.id');
		$this->db->from('members_certificate');
		//$this->db->where('username = ""', null,false);
		//$this->db->where('stri_code_wilayah <> ""', null,false);
		$this->db->join('user_profiles', 'user_profiles.user_id = members_certificate.person_id');
		$this->db->join('m_kolektif', 'user_profiles.kolektif_name_id = m_kolektif.id', 'left');
		$this->db->join('members', 'members_certificate.person_id = members.person_id');
		$this->db->join('log_stri b', 'b.id = (
			SELECT
			  max(fa.id) a_id
			FROM log_stri fa 
			WHERE fa.stri_id = members_certificate.stri_id and fa.user_id=members_certificate.person_id
		  )', 'left',false);
		$this->db->join('user_transfer e', 'e.id = b.id_pay', 'left');
		$this->db->where('members_certificate.status', "1");
		
		if($is_kolektif)
		{
			$str = '';
			$str2 = '';
			$i = 0;
			$i2 = 0;
				
			if(is_array($bk)){
				foreach($bk as $val)
				{
					if($i==0) $str .= " LPAD( stri_code_bk_hkk, 2, '0' ) like '".$val."%'";
					else $str .= " or LPAD( stri_code_bk_hkk, 2, '0' ) like '".$val."%'";
					$i++;
				}
			}
			else if($bk!=""){
				if($i==0) $str .= " LPAD( stri_code_bk_hkk, 2, '0' ) like '".$bk."%'";
				else $str .= " or LPAD( stri_code_bk_hkk, 2, '0' ) like '".$bk."%'";
				$i++;		
			}		
			if(is_array($wil)){
				foreach($wil as $val)
				{
					if($i2==0) $str2 .= " LPAD( code_wilayah, 4, '0' ) like '".$val."%'";
					else $str2 .= " or LPAD( code_wilayah, 4, '0' ) like '".$val."%'";
					$i2++;
				}
			}
			else if($wil!=""){
				if($i2==0) $str2 .= " LPAD( code_wilayah, 4, '0' ) like '".$wil."%'";
				else $str2 .= " or LPAD( code_wilayah, 4, '0' ) like '".$wil."%'";
				$i2++;		
			}
			
			
			$this->db->group_start();
			//$this->db->where('FIND_IN_SET('. $this->session->userdata('admin_id').',kolektif_ids) <>',"0");
			if($str!='') $this->db->where($str,null);
			if($str2!='') $this->db->where('members_certificate.person_id in (select person_id from members where '.$str2.')',null);
			$this->db->group_end();
		}
		
		
		
		$Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->num_rows();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
		
		//return $this->db->count_all();
    }
	
	/**
	 * ER: FIXME: Remove this function
	 * @deprecated table `skip3` is not exist
	 */
	public function record_count_skip($table_name) {
		$this->db->select('id');
		$this->db->from('skip3 a');
		//$this->db->where('username = ""', null,false);
		//$this->db->where('skip_id <> ""', null,false);
		
		$this->db->where('sk_end > now()', null,false);
		$this->db->where('noip<>""', null,false);
		$this->db->group_by('kta,sk_from,sertid');
		$this->db->having('sertid=(select max(sertid) from skip3 where kta=a.kta)');
		$this->db->having('sk_from=(select max(sk_from) from skip3 where kta=a.kta)');
		//$this->db->order_by("sk_from", "DESC"); 
		
		$Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->num_rows();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
		
		//return $this->db->count_all();
    }
	
	public function record_count_pi($table_name) {
		$this->db->select('id');
		$this->db->from('members_certificate');
		//$this->db->where('username = ""', null,false);
		$this->db->where('skip_id <> ""', null,false);
		
		$Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->num_rows();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
		
		//return $this->db->count_all();
    }
	
	public function record_count_pkb($table_name) {
		$this->db->select('id');
		$this->db->from('user_pkb');
		$this->db->where('status', 1);
		$this->db->where('status_pkb<>0');
		
		if($this->session->userdata('type')!="0" && $this->session->userdata('type')!="1" && $this->session->userdata('type')!="7" && $this->session->userdata('type')!="11" && $this->session->userdata('type')!="15" && $this->session->userdata('type')!="16")
		{
			$this->db->where("TRIM(LEADING '0' FROM bidang) = ".ltrim($this->session->userdata('code_bk_hkk'), '0'),null);
			$this->db->where('status_pkb>=5');
		}
		else if($this->session->userdata('type')=="7")
		{
			//$this->db->where("TRIM(LEADING '0' FROM bidang) = ".ltrim($this->session->userdata('code_bk_hkk'), '0'),null);
			
			$this->db->where('status_pkb>=6');
			
			$this->db->group_start()->where('majelis1', $this->session->userdata('admin_id'))->or_where('majelis2', $this->session->userdata('admin_id'))->or_where('majelis3', $this->session->userdata('admin_id'))->group_end();
		}
		else if($this->session->userdata('type')=="11" || $this->session->userdata('type')=="15")
		{
			$this->db->where("TRIM(LEADING '0' FROM bidang) = ".ltrim($this->session->userdata('code_bk_hkk'), '0'),null);
			//$this->db->where('status_pkb>=6');
		}
		
		$Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->num_rows();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
		
		//return $this->db->count_all();
    }
	
	public function record_count_majelis($table_name) {
		$this->db->select('id');
		$this->db->from('admin');
		//$this->db->where('status', 1);
		$this->db->where('type',7);		
		if($this->session->userdata('type')!="0" && $this->session->userdata('type')!="1")
			$this->db->where("TRIM(LEADING '0' FROM code_bk_hkk) = ".ltrim($this->session->userdata('code_bk_hkk'), '0'),null);
		
		$Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->num_rows();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
		
		//return $this->db->count_all();
    }
	
	public function record_count_bp($table_name) {
		$this->db->select('id');
		$this->db->from('m_bakuan_penilaian');
		//$this->db->where('status', 1);
		//$this->db->where('type>2');
		
		$Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->num_rows();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
		
		//return $this->db->count_all();
    }
	
	public function get_kta_by_personid($id) {
        $this->db->select('no_kta');
        $this->db->from('members');
		$this->db->where('person_id', $id);
        $Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->row();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
    }
	
	public function get_kta_data_by_personid($id) {
        $this->db->select('*,(select name from m_bk where TRIM(LEADING "0" from value)=TRIM(LEADING "0" from members.code_bk_hkk)) as bk,(select name from m_cab where TRIM(LEADING "0" from value)=TRIM(LEADING "0" from members.code_wilayah)) as wil');
        $this->db->from('members');
		$this->db->where('person_id', $id);
        $Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->row();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
    }
	
	public function get_skip_data_by_id($id) {
        $this->db->select('*');
        $this->db->from('user_cert');
		$this->db->where('status', 2);
		$this->db->where('id', $id);
        $Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->row();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
    }
	
	public function get_member_by_id($id) {
        $this->db->select('user_profiles.*,users.created,email');
        $this->db->from('users');
		$this->db->join('user_profiles', 'users.id = user_profiles.user_id', 'inner');
		//$this->db->join('pp_job_industries', 'pp_companies.industry_ID = pp_job_industries.ID', 'left');
		$this->db->where('users.id', $id);
        $Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->row();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
    }
	
	public function get_member_by_kta($id) {
        $this->db->select('user_profiles.*,users.created,email,code_bk_hkk,code_wilayah,no_kta,jenis_anggota');
        $this->db->from('users');
		$this->db->join('user_profiles', 'users.id = user_profiles.user_id', 'inner');
		$this->db->join('members', 'members.person_id = user_profiles.user_id', 'inner');
		//$this->db->join('pp_job_industries', 'pp_companies.industry_ID = pp_job_industries.ID', 'left');
		$this->db->where("TRIM(LEADING '0' FROM members.no_kta) = ", ltrim($id, '0'));
        $Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->row();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
    }
	
	public function get_export_by_id($id) {
        $this->db->select('user_profiles.firstname,user_profiles.lastname,no_kta,code_bk_hkk,code_wilayah,from_date,thru_date,photo,(select CONCAT(address," - ",city," - ",province," - ",phone," - ",zipcode) from user_address where user_id=user_profiles.user_id and is_mailing=1 and status=1 order by createddate desc limit 1) as address,(select GROUP_CONCAT(title_prefix," , ",title) from user_edu where user_id=user_profiles.user_id and status=1 order by createddate desc) as title');
        $this->db->from('user_profiles');
		$this->db->join('members', 'members.person_id = user_profiles.user_id', 'left');
		$this->db->where('user_profiles.user_id', $id);
        $Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = (array) $Q->row();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
    }

	public function get_export_member($search_parameters,$bk,$wil,$is_kolektif) {
        $this->db->select("
		(select code_bk_hkk from members where members.person_id=a.user_id) as code_bk_hkk,
		(SELECT name from m_bk join members on LPAD(members.code_bk_hkk, 2, '0')=value where person_id=a.user_id) as nama_bk,
		(select code_wilayah from members where members.person_id=a.user_id) as code_wilayah,
		(SELECT name from m_cab join members on LPAD(members.code_wilayah, 4, '0')=m_cab.value where members.person_id=a.user_id) as nama_wilayah,
		(select no_kta from members where members.person_id=a.user_id and b.username<>'') as no_kta,
		(select from_date from members where members.person_id=a.user_id and b.username<>'') as from_date,
		(select thru_date from members where members.person_id=a.user_id and b.username<>'') as thru_date,
		(select jenis_anggota from members where members.person_id=a.user_id and b.username<>'') as jenis_anggota,
		firstname,lastname,email,mobilephone,va,
		(select address from user_address where user_id=a.user_id and addresstype=1 order by id desc limit 1) as address,
		(select city from user_address where user_id=a.user_id and addresstype=1 order by id desc limit 1) as city,
		(select province from user_address where user_id=a.user_id and addresstype=1 order by id desc limit 1) as province,
		(select zipcode from user_address where user_id=a.user_id and addresstype=1 order by id desc limit 1) as zipcode");
        //$this->db->from('members a');
		$this->db->from('user_profiles a');
		$this->db->join('users b', 'a.user_id = b.id', 'left');
		//$this->db->where('user_profiles.user_id', $id);
		
		foreach($search_parameters as $v=>$w){
			if($v == 'inst'){
				
				$this->db->where(('a.user_id in (select user_id from user_exp where status=1 and REPLACE(lower(COALESCE(user_exp.company,""))," ","") like "%'.str_replace(' ','',strtolower($w)).'%" )'),null);
			}
			else if($v == 'status'){
				
				if($w=="1") $this->db->where(('(select count(person_id) from members join users on members.person_id=users.id where username<>"" and thru_date>=curdate() and person_id=a.user_id) > 0'),null,false);
				else $this->db->where(('(select count(person_id) from members join users on members.person_id=users.id where username<>"" and thru_date<curdate() and person_id=a.user_id) > 0'),null,false);
			}
			
			else if($v == 'filter_cab'){
				$this->db->where(('a.user_id in (select person_id from members where LPAD(members.code_wilayah, 4, "0") like "'.$w.'%")'),null);
			}
			
			else if($v == 'filter_bk'){
				$this->db->where(('a.user_id in (select person_id from members where TRIM(LEADING "0" FROM code_bk_hkk)='.ltrim($w, '0').')'),null);
			}	
/*			
			else if($v == 'filter_hkk'){
				$this->db->where(('a.user_id in (select person_id from members where TRIM(LEADING "0" FROM code_hkk)='.ltrim($w, '0').')'),null);
			}
*/			
			else if($v == 'jenis_anggota'){
				$this->db->where(('a.user_id = (select person_id from members where jenis_anggota='.$w.' and person_id=a.user_id)'),null);
			}
			else
				$this->db->where($v,$w);
		}
		
		
		
		if($is_kolektif)
		{	
			$str = '';
			$i = 0;
				
			if(is_array($bk)){
				foreach($bk as $val)
				{
					if($i==0) $str .= " LPAD( code_bk_hkk, 2, '0' ) like '".$val."%'";
					else $str .= " or LPAD( code_bk_hkk, 2, '0' ) like '".$val."%'";
					$i++;
				}
			}
			else if($bk!=""){
				if($i==0) $str .= " LPAD( code_bk_hkk, 2, '0' ) like '".$bk."%'";
				else $str .= " or LPAD( code_bk_hkk, 2, '0' ) like '".$bk."%'";
				$i++;		
			}	
			
			if(is_array($wil)){
				foreach($wil as $val)
				{
					if($i==0) $str .= " LPAD( code_wilayah, 4, '0' ) like '".$val."%'";
					else $str .= " or LPAD( code_wilayah, 4, '0' ) like '".$val."%'";
					$i++;
				}
			}
			else if($wil!=""){
				if($i==0) $str .= " LPAD( code_wilayah, 4, '0' ) like '".$wil."%'";
				else $str .= " or LPAD( code_wilayah, 4, '0' ) like '".$wil."%'";
				$i++;		
			}
			
			
			$this->db->group_start();
			$this->db->where('FIND_IN_SET('. $this->session->userdata('admin_id').',kolektif_ids) <>',"0");
			if($str!='') $this->db->or_where('a.user_id in (select person_id from members where '.$str.')',null);
			$this->db->group_end();
		}
		/*else{
			$this->db->where(('a.user_id in (select person_id from members where LPAD(members.code_wilayah, 4, "0") like "23%" or LPAD(members.code_wilayah, 4, "0") like "24%" or LPAD(members.code_wilayah, 4, "0") like "26%" or LPAD(members.code_wilayah, 4, "0") like "34%")'),null,false);
		}*/
		
        $Q = $this->db->get();
        if ($Q->num_rows() > 0) {
   //         $return = (array) $Q->result();
  		 $return = $Q->result();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
    }

	public function get_export_member_ori($search_parameters,$bk,$wil,$is_kolektif) {
        $this->db->select("
		(select code_bk_hkk from members where members.person_id=a.user_id) as code_bk_hkk,
		(SELECT name from m_bk join members on LPAD(members.code_bk_hkk, 2, '0')=value where person_id=a.user_id) as nama_bk,
		(select code_wilayah from members where members.person_id=a.user_id) as code_wilayah,
		(SELECT name from m_cab join members on LPAD(members.code_wilayah, 4, '0')=m_cab.value where members.person_id=a.user_id) as nama_wilayah,
		(select no_kta from members where members.person_id=a.user_id and b.username<>'') as no_kta,
		(select from_date from members where members.person_id=a.user_id and b.username<>'') as from_date,
		(select thru_date from members where members.person_id=a.user_id and b.username<>'') as thru_date,
		(select jenis_anggota from members where members.person_id=a.user_id and b.username<>'') as jenis_anggota,
		firstname,lastname,email,mobilephone,va,
		(select address from user_address where user_id=a.user_id and addresstype=1 order by id desc limit 1) as address,
		(select city from user_address where user_id=a.user_id and addresstype=1 order by id desc limit 1) as city,
		(select province from user_address where user_id=a.user_id and addresstype=1 order by id desc limit 1) as province,
		(select zipcode from user_address where user_id=a.user_id and addresstype=1 order by id desc limit 1) as zipcode");
        //$this->db->from('members a');
		$this->db->from('user_profiles a');
		$this->db->join('users b', 'a.user_id = b.id', 'left');
		//$this->db->where('user_profiles.user_id', $id);
		
		foreach($search_parameters as $v=>$w){
			if($v == 'inst'){
				
				$this->db->where(('a.user_id in (select user_id from user_exp where status=1 and REPLACE(lower(COALESCE(user_exp.company,""))," ","") like "%'.str_replace(' ','',strtolower($w)).'%" )'),null);
			}
			else if($v == 'status'){
				
				if($w=="1") $this->db->where(('(select count(person_id) from members join users on members.person_id=users.id where username<>"" and thru_date>=curdate() and person_id=a.user_id) > 0'),null,false);
				else $this->db->where(('(select count(person_id) from members join users on members.person_id=users.id where username<>"" and thru_date<curdate() and person_id=a.user_id) > 0'),null,false);
			}
			
			else if($v == 'filter_cab'){
				$this->db->where(('a.user_id in (select person_id from members where LPAD(members.code_wilayah, 4, "0") like "'.$w.'%")'),null);
			}
			
			else if($v == 'filter_bk'){
				$this->db->where(('a.user_id in (select person_id from members where TRIM(LEADING "0" FROM code_bk_hkk)='.ltrim($w, '0').')'),null);
			}		
			else if($v == 'filter_hkk'){
				$this->db->where(('a.user_id in (select person_id from members where TRIM(LEADING "0" FROM code_hkk)='.ltrim($w, '0').')'),null);
			}
			else if($v == 'jenis_anggota'){
				$this->db->where(('a.user_id = (select person_id from members where jenis_anggota='.$w.' and person_id=a.user_id)'),null);
			}
			else
				$this->db->where($v,$w);
		}
		
		
		
		if($is_kolektif)
		{	
			$str = '';
			$i = 0;
				
			if(is_array($bk)){
				foreach($bk as $val)
				{
					if($i==0) $str .= " LPAD( code_bk_hkk, 2, '0' ) like '".$val."%'";
					else $str .= " or LPAD( code_bk_hkk, 2, '0' ) like '".$val."%'";
					$i++;
				}
			}
			else if($bk!=""){
				if($i==0) $str .= " LPAD( code_bk_hkk, 2, '0' ) like '".$bk."%'";
				else $str .= " or LPAD( code_bk_hkk, 2, '0' ) like '".$bk."%'";
				$i++;		
			}	
			
			if(is_array($wil)){
				foreach($wil as $val)
				{
					if($i==0) $str .= " LPAD( code_wilayah, 4, '0' ) like '".$val."%'";
					else $str .= " or LPAD( code_wilayah, 4, '0' ) like '".$val."%'";
					$i++;
				}
			}
			else if($wil!=""){
				if($i==0) $str .= " LPAD( code_wilayah, 4, '0' ) like '".$wil."%'";
				else $str .= " or LPAD( code_wilayah, 4, '0' ) like '".$wil."%'";
				$i++;		
			}
			
			
			$this->db->group_start();
			$this->db->where('FIND_IN_SET('. $this->session->userdata('admin_id').',kolektif_ids) <>',"0");
			if($str!='') $this->db->or_where('a.user_id in (select person_id from members where '.$str.')',null);
			$this->db->group_end();
		}
		/*else{
			$this->db->where(('a.user_id in (select person_id from members where LPAD(members.code_wilayah, 4, "0") like "23%" or LPAD(members.code_wilayah, 4, "0") like "24%" or LPAD(members.code_wilayah, 4, "0") like "26%" or LPAD(members.code_wilayah, 4, "0") like "34%")'),null,false);
		}*/
		
        $Q = $this->db->get();
        if ($Q->num_rows() > 0) {
  //          $return = (array) $Q->result();
  		 $return = $Q->result();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
    }
		
	public function get_export_finance($search_parameters) {
        //print_r($search_parameters);
		$this->db->select('users.id as ID, username as no_kta, users.email, user_profiles.firstname, user_profiles.lastname, user_profiles.gender, user_profiles.idcard, user_profiles.dob, users.user_status as sts,
		user_transfer.pay_type,from_date,thru_date,
		user_profiles.va,user_transfer.is_upload_mandiri,(select x.status from user_transfer x where user_profiles.user_id=x.user_id and user_transfer.pay_type=x.pay_type order by x.createddate desc limit 1) as paystatus,(select name from m_kolektif where status=1 and id=user_profiles.kolektif_name_id) as kolektif_name,
		
		x.pay_type as paytype,x.atasnama as payname,x.tgl as paydate,x.description paydesc,x.bukti as payfile,x.status as paystatus,x.iuranpangkal as payiuranpangkal,x.iurantahunan as payiurantahunan,x.sukarelaanggota as paysukarelaanggota, x.sukarelagedung as paysukarelagedung,x.sukarelaperpus as paysukarelaperpus, x.sukarelaceps as paysukarelaceps, x.sukarelatotal as paysukarelatotal, x.id as payid,
		lic_num as sip_lic_num,startyear as sip_startyear, endyear as sip_endyear
		
		
		');
		//(select x.pay_type as paytype,x.atasnama as payname,x.tgl as paydate,x.description paydesc,x.bukti as payfile,x.status as paystatus,x.iuranpangkal as payiuranpangkal,x.iurantahunan as payiurantahunan,x.sukarelaanggota as paysukarelaanggota, x.sukarelagedung as paysukarelagedung,x.sukarelaperpus as paysukarelaperpus, x.sukarelaceps as paysukarelaceps, x.sukarelatotal as paysukarelatotal, x.id as payid from user_transfer x where '.$row->ID.'=x.user_id and '.$row->pay_type.'=x.pay_type order by x.createddate desc limit 1)
        $this->db->from('user_transfer');
		$this->db->join('users', 'user_transfer.user_id = users.id', 'left');
		$this->db->join('user_profiles', 'user_profiles.user_id = user_transfer.user_id', 'left');
		$this->db->join('members', 'user_transfer.user_id = members.person_id', 'left');
		$this->db->join('user_transfer x', 'x.id = (select id from user_transfer mm where mm.user_id=users.id and mm.pay_type=user_transfer.pay_type order by mm.createddate desc limit 1)', 'left');
		$this->db->join('user_cert', 'user_cert.id = (select id from user_cert nn where nn.user_id=users.id and status=2 order by endyear desc, ip_tipe desc limit 1)', 'left');
		$this->db->where('(CASE WHEN user_transfer.pay_type=5 THEN user_transfer.vnv_status=1 ELSE 1=1 END)', null,false);//and rel_id=0 
		
		
		$this->db->group_by('user_transfer.user_id,user_transfer.pay_type');
		
		//$this->db->order_by("user_transfer.modifieddate", "desc");		
		//$this->db->order_by("user_profiles.createddate", "DESC");
		
		
		foreach($search_parameters as $v=>$w){
			if($v == 'filter_type'){
				if($w=='1') $this->db->where(('(user_transfer.pay_type=0 or user_transfer.pay_type=1)'),null);
				else if($w=='2') $this->db->where(('(user_transfer.pay_type=2)'),null);
				else if($w=='3') $this->db->where(('(user_transfer.pay_type=3 or user_transfer.pay_type=4)'),null);
				else if($w=='4') $this->db->where(('(user_transfer.pay_type=5)'),null);
			}
			else if($v == 'user_transfer.status'){
				if($w=='0') $this->db->having('paystatus',  0);
				else if($w=='1') $this->db->having('paystatus',  1);
				else if($w=='2') $this->db->having('paystatus',  2);
			}
			else
				$this->db->like($v,$w);
		}
		
        $Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = (array) $Q->result();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
    }
	
	public function get_export_stri($search_parameters) {
        //print_r($search_parameters);
		$this->db->select('e.createddate as tgl,members_certificate.person_id as ID,add_name, members_certificate.status as sts,m_kolektif.category as ishkk,members_certificate.*');
        $this->db->from('members_certificate');//user_profiles.firstname
		$this->db->join('user_profiles', 'user_profiles.user_id = members_certificate.person_id');
		$this->db->join('m_kolektif', 'user_profiles.kolektif_name_id = m_kolektif.id', 'left');
		$this->db->join('members', 'members_certificate.person_id = members.person_id');
		$this->db->join('log_stri b', 'b.id = (
			SELECT
			  max(fa.id) a_id
			FROM log_stri fa 
			WHERE fa.stri_id = members_certificate.stri_id and fa.user_id=members_certificate.person_id
		  )', 'left',false);
		$this->db->join('user_transfer e', 'e.id = b.id_pay', 'left');
		//$this->db->where('stri_code_wilayah <> ""', null,false);
		
		$this->db->where('members_certificate.status', "1");
		
		foreach($search_parameters as $v=>$w){
			if($v == 'filter_cab'){
				$this->db->where(('LPAD(stri_code_wilayah, 4, "0") like "'.$w.'%"'),null);
			}
			else if($v == 'filter_bk'){
				$this->db->where(('LPAD(stri_code_bk_hkk, 2, "0") like "'.$w.'%"'),null);
			}
			else if($v == 'filter_hkk'){
				$this->db->where(('LPAD(members.code_hkk, 2, "0") like "'.$w.'%"'),null);
			}
			else if($v == 'filter_type'){
				$this->db->where('certificate_type',$w);
			}
			else if($v == 'filter_status'){
				if($w=='1') $this->db->where(('stri_thru_date >= now()'),null);
				else if($w=='0') $this->db->where(('stri_thru_date < now()'),null);
			}
			else if($v == 'nomor'){
				$this->db->where('TRIM(LEADING "0" FROM stri_id) = "'.ltrim($w, '0').'"',null);
			}
			else if($v == 'stri_period'){
				$this->db->where(($w),null);
			}
			else
				$this->db->where($v,$w);
		}
		//$this->db->$where($search_parameters);
		
		
        $Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = (array) $Q->result();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
    }
	
	public function get_count_export_finance($search_parameters) {
        $this->db->select('users.id as ID,x.status');
        $this->db->from('user_transfer');
		$this->db->join('users', 'user_transfer.user_id = users.id', 'left');
		$this->db->join('user_profiles', 'user_profiles.user_id = user_transfer.user_id', 'left');
		$this->db->join('members', 'user_transfer.user_id = members.person_id', 'left');
		//$this->db->join('user_transfer x', 'x.id = (select id from user_transfer mm where mm.user_id=users.id and mm.pay_type=user_transfer.pay_type order by mm.createddate desc limit 1)', 'left');
		$this->db->join('user_transfer x', 'x.id = (select id from user_transfer mm where mm.user_id=users.id and mm.pay_type=user_transfer.pay_type order by mm.createddate desc limit 1)', 'left');
		//$this->db->join('user_cert', 'user_cert.id = (select id from user_cert nn where nn.user_id=users.id and status=2 order by endyear desc, ip_tipe desc limit 1)', 'left');
		$this->db->where('(CASE WHEN user_transfer.pay_type=5 THEN user_transfer.vnv_status=1 ELSE 1=1 END)', null,false);//and rel_id=0 
		
		
		$this->db->group_by('user_transfer.user_id,user_transfer.pay_type');
		
		foreach($search_parameters as $v=>$w){
			if($v == 'filter_type'){
				if($w=='1') $this->db->where(('(user_transfer.pay_type=0 or user_transfer.pay_type=1)'),null);
				else if($w=='2') $this->db->where(('(user_transfer.pay_type=2)'),null);
				else if($w=='3') $this->db->where(('(user_transfer.pay_type=3 or user_transfer.pay_type=4)'),null);
				else if($w=='4') $this->db->where(('(user_transfer.pay_type=5)'),null);
			}
			else if($v == 'user_transfer.status'){
				if($w=='0') $this->db->having('x.status',  0);
				else if($w=='1') $this->db->having('x.status',  1);
				else if($w=='2') $this->db->having('x.status',  2);
			}
			else
				$this->db->like($v,$w);
		}
		
        $Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->num_rows();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
    }
	
	public function get_count_export_stri($search_parameters) {
        $this->db->select('members_certificate.id');
        $this->db->from('members_certificate');//user_profiles.firstname
		$this->db->join('user_profiles', 'user_profiles.user_id = members_certificate.person_id');
		$this->db->join('m_kolektif', 'user_profiles.kolektif_name_id = m_kolektif.id', 'left');
		$this->db->join('members', 'members_certificate.person_id = members.person_id');
		$this->db->join('log_stri b', 'b.id = (
			SELECT
			  max(fa.id) a_id
			FROM log_stri fa 
			WHERE fa.stri_id = members_certificate.stri_id and fa.user_id=members_certificate.person_id
		  )', 'left',false);
		$this->db->join('user_transfer e', 'e.id = b.id_pay', 'left');
		//$this->db->where('stri_code_wilayah <> ""', null,false);
		
		$this->db->where('members_certificate.status', "1");
		
		foreach($search_parameters as $v=>$w){
			if($v == 'filter_cab'){
				$this->db->where(('LPAD(stri_code_wilayah, 4, "0") like "'.$w.'%"'),null);
			}
			else if($v == 'filter_bk'){
				$this->db->where(('LPAD(stri_code_bk_hkk, 2, "0") like "'.$w.'%"'),null);
			}
			else if($v == 'filter_hkk'){
				$this->db->where(('LPAD(members.code_hkk, 2, "0") like "'.$w.'%"'),null);
			}
			else if($v == 'filter_type'){
				$this->db->where('certificate_type',$w);
			}
			else if($v == 'filter_status'){
				if($w=='1') $this->db->where(('stri_thru_date >= now()'),null);
				else if($w=='0') $this->db->where(('stri_thru_date < now()'),null);
			}
			else if($v == 'nomor'){
				$this->db->where('TRIM(LEADING "0" FROM stri_id) = "'.ltrim($w, '0').'"',null);
			}
			else if($v == 'stri_period'){
				$this->db->where(($w),null);
			}
			else
				$this->db->where($v,$w);
		}
		//$this->db->$where($search_parameters);
		
		
        $Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->num_rows();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
    }
	
	public function get_member_by_id_simple($id) {
        $this->db->select('pp_members.*');
        $this->db->from('pp_members');
		$this->db->where('pp_members.ID', $id);
        $Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->row();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
    }
	
	public function get_member_by_company_id($cid) {
        $this->db->select('pp_members.*, pp_companies.ID AS CID,pp_companies.company_name,pp_companies.company_email,pp_companies.company_ceo,pp_companies.industry_ID,pp_companies.ownership_type,pp_companies.company_description,pp_companies.company_location,pp_companies.no_of_offices,pp_companies.company_website,pp_companies.no_of_employees, pp_companies.established_in, pp_companies.company_logo, pp_companies.company_folder, pp_companies.company_type, pp_companies.company_fax, pp_companies.company_phone');
        $this->db->from('pp_members');
		$this->db->join('pp_companies', 'pp_members.company_ID = pp_companies.ID', 'left');
		$this->db->where('pp_members.company_ID', $cid);
        $Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->row();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
    }
	
//====== Searching Employers =======	
	public function search_all_members($per_page, $page, $search_parameters, $wild_card='') {
		
		$where = ($wild_card=='yes')?'where':'like';
        $this->db->select('users.id as ID, username as no_kta, users.email, user_profiles.firstname, user_profiles.lastname, user_profiles.gender, user_profiles.idcard, user_profiles.dob, users.user_status as sts, (select code_wilayah from members where person_id=users.id limit 1) as cab,(select code_bk_hkk from members where person_id=users.id limit 1) as bk,(select from_date from members where person_id=users.id limit 1) as from_date,(select thru_date from members where person_id=users.id limit 1) as thru_date,(select jenis_anggota from members where person_id=users.id limit 1) as jenis_anggota,(select id from user_cert where user_id=users.id and status=2 and endyear>curdate() limit 1) as ip');
        $this->db->from('user_profiles');
		$this->db->join('users', 'user_profiles.user_id = users.id', 'left');
		
		foreach($search_parameters as $v=>$w){
			if($v == 'inst'){
				
				$this->db->where(('user_profiles.user_id in (select user_id from user_exp where status=1 and REPLACE(lower(COALESCE(user_exp.company,""))," ","") like "%'.str_replace(' ','',strtolower($w)).'%" )'),null);
			}
			else
				$this->db->$where($v,$w);
		}
		
		//$this->db->$where($search_parameters);
		
		//$this->db->order_by("user_profiles.id", "DESC"); 
		$this->db->limit($per_page, $page);
        $Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->result();
        } else {
            $return = 0;
        }
        $Q->free_result();
		//echo $this->db->last_query(); exit;
        return $return;
    }
	
	public function search_all_members_v2($per_page, $page, $search_parameters, $wild_card='',$bk,$wil,$is_kolektif) {
		
		$where = ($wild_card=='yes')?'where':'like';
        $this->db->select('users.id as ID, (select no_kta from members where person_id=users.id limit 1) as no_kta, users.email, user_profiles.firstname, user_profiles.lastname, user_profiles.gender, user_profiles.idcard, user_profiles.dob, users.user_status as sts, (select code_wilayah from members where person_id=users.id limit 1) as cab,(select code_bk_hkk from members where person_id=users.id limit 1) as bk,(select code_bk_hkk from members where person_id=users.id limit 1) as bk,(select code_hkk from members where person_id=users.id limit 1) as hkk,(select from_date from members where person_id=users.id limit 1) as from_date,(select thru_date from members where person_id=users.id limit 1) as thru_date,(select jenis_anggota from members where person_id=users.id limit 1) as jenis_anggota,(select id from user_cert where user_id=users.id and status=2 and endyear>curdate() limit 1) as ip, user_profiles.kolektif_ids,(select name from m_kolektif where status=1 and id=user_profiles.kolektif_name_id) as kolektif_name,warga_asing,username,kolektif_name_id');
        $this->db->from('user_profiles');
		$this->db->join('users', 'user_profiles.user_id = users.id', 'left');

//-------------------------------------------------------------------------------------------------- Tambahan by IP -----		
	if (strlen(trim($bk)) != 0 ) {
		$this->db->join('members', 'user_profiles.user_id = members.person_id', 'left');
	//	$this->db->where('members.code_bk_hkk', $bk) ; 
		$this->db->where('LPAD(members.code_bk_hkk,2,"0")', $bk) ;
	}	
	if (strlen(trim($wil)) != 0 ) {
		if (strlen(trim($wil)) != 2 ) {
			$this->db->join('members', 'user_profiles.user_id = members.person_id', 'left');
			$this->db->where('members.code_wilayah', $wil) ; 				// untuk perbaikan tampilan per cabang tambahan by Ip		
		}
		if (strlen(trim($wil)) == 2 ) {
			$this->db->join('members', 'user_profiles.user_id = members.person_id', 'left');
			$this->db->where('members.wil_id', $wil) ; 				// untuk perbaikan tampilan per cabang tambahan by Ip		
		}		
       }	
//---------------------------------------------------------------------------------------------------------------------			

		foreach($search_parameters as $v=>$w){
			if($v == 'inst'){
				
				$this->db->where(('user_profiles.user_id in (select user_id from user_exp where status=1 and REPLACE(lower(COALESCE(user_exp.company,""))," ","") like "%'.str_replace(' ','',strtolower($w)).'%" )'),null);
			}
			else if($v == 'status'){
				
				if($w=="1") $this->db->where(('(select count(person_id) from members join users on members.person_id=users.id where username<>"" and thru_date>=curdate() and person_id=user_profiles.user_id) > 0'),null,false);
				else $this->db->where(('(select count(person_id) from members join users on members.person_id=users.id where username<>"" and thru_date<curdate() and person_id=user_profiles.user_id) > 0'),null,false);
			}
			else if($v == 'filter_cab'){
				$this->db->where(('user_profiles.user_id in (select person_id from members where LPAD(members.code_wilayah, 4, "0") like "'.$w.'%")'),null);
			}			
//----------------------------------------------------------------------------------------- Tambahan by IP
			else if($v == 'filter_wil'){
				$this->db->where(('user_profiles.user_id in (select person_id from members where LPAD(members.code_wilayah, 4, "0") like "'.$w.'%")'),null);
			}		
//--------------------------------------------------------------------------------		
			else if($v == 'filter_bk'){
				$this->db->where(('user_profiles.user_id in (select person_id from members where TRIM(LEADING "0" FROM code_bk_hkk)='.ltrim($w, '0').')'),null);
			}
			else if($v == 'filter_hkk'){
				$this->db->where(('user_profiles.user_id in (select person_id from members where TRIM(LEADING "0" FROM code_hkk)='.ltrim($w, '0').')'),null);
			}
			else if($v == 'jenis_anggota'){
		//		$this->db->where(('user_profiles.user_id = (select person_id from members where members.jenis_anggota='.$w.' and members.person_id=user_profiles.user_id)'),null);
		//		$this->db->where(('user_profiles.user_id = (select members.person_id from members where TRIM(members.jenis_anggota) ='.$w.' and members.person_id=user_profiles.user_id)'),null);
				$this->db->where(('user_profiles.user_id in (select person_id from members where LPAD(members.jenis_anggota, 2, "0") like "'.$w.'%")'),null);
			}			
			else if($v == 'username'){
				$this->db->where(('user_profiles.user_id in (select person_id from members where no_kta like "%'.$w.'%")'),null);
			}
			else
				$this->db->$where($v,$w);
		}
		
		//$this->db->$where($search_parameters);
		
		//$this->db->order_by("user_profiles.id", "DESC"); 
		
	if($is_kolektif)
	{	
		$str = '';
		$i = 0;
			
		if(is_array($bk)){
			foreach($bk as $val)
			{
				if($i==0) $str .= " LPAD( code_bk_hkk, 2, '0' ) like '".$val."%'";
				else $str .= " or LPAD( code_bk_hkk, 2, '0' ) like '".$val."%'";
				$i++;
			}
		}
		else if($bk!=""){
			if($i==0) $str .= " LPAD( code_bk_hkk, 2, '0' ) like '".$bk."%'";
			else $str .= " or LPAD( code_bk_hkk, 2, '0' ) like '".$bk."%'";
			$i++;		
		}		
		if(is_array($wil)){
			foreach($wil as $val)
			{
				if($i==0) $str .= " LPAD( code_wilayah, 4, '0' ) like '".$val."%'";
				else $str .= " or LPAD( code_wilayah, 4, '0' ) like '".$val."%'";
				$i++;
			}
		}
		else if($wil!=""){
			if($i==0) $str .= " LPAD( code_wilayah, 4, '0' ) like '".$wil."%'";
			else $str .= " or LPAD( code_wilayah, 4, '0' ) like '".$wil."%'";
			$i++;		
		}
		
		
		$this->db->group_start();
		$this->db->where('FIND_IN_SET('. $this->session->userdata('admin_id').',kolektif_ids) <>',"0");
		if($str!='') $this->db->or_where('user_profiles.user_id in (select person_id from members where '.$str.')',null);
		$this->db->group_end();
	}	
		$this->db->limit($per_page, $page);
        $Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->result();
        } else {
            $return = 0;
        }
        $Q->free_result();
		//echo $this->db->last_query(); exit;
        return $return;
    }
	
	public function search_record_count($table_name, $search_parameters) {
		//return $this->db->count_all($table_name);
		foreach($search_parameters as $v=>$w){
			if($v == 'inst'){
				$this->db->where(('user_profiles.user_id in (select user_id from user_exp where status=1 and REPLACE(lower(COALESCE(user_exp.company,""))," ","") like "%'.str_replace(' ','',strtolower($w)).'%" )'),null);
				
			}
			else
				$this->db->like($v,$w);
		}
		
		//$this->db->like($search_parameters);
		$this->db->from($table_name);
		$this->db->join('users', 'user_profiles.user_id = users.id', 'left');
		return $this->db->count_all_results();
		//exit;
    }
	
	public function search_record_count_v2($table_name, $search_parameters,$bk,$wil,$is_kolektif) {
		//return $this->db->count_all($table_name);
//-------------------------------------------------------------------------------------------------- Tambahan by IP -----		
	if (strlen(trim($bk)) != 0 ) {
		$this->db->join('members', 'user_profiles.user_id = members.person_id', 'left');
//		$this->db->where('members.code_bk_hkk', $bk) ; 
		$this->db->where('LPAD(members.code_bk_hkk,2,"0")', $bk) ;
	}	
	if (strlen(trim($wil)) != 0 ) {
		if (strlen(trim($wil)) != 2 ) {
			$this->db->join('members', 'user_profiles.user_id = members.person_id', 'left');
			$this->db->where('members.code_wilayah', $wil) ; 				// untuk perbaikan tampilan per cabang tambahan by Ip		
		}
		if (strlen(trim($wil)) == 2 ) {
			$this->db->join('members', 'user_profiles.user_id = members.person_id', 'left');
			$this->db->where('members.wil_id', $wil) ; 				// untuk perbaikan tampilan per cabang tambahan by Ip		
		}		
       }	
//---------------------------------------------------------------------------------------------------------------------				
		
		foreach($search_parameters as $v=>$w){
			if($v == 'inst'){
				$this->db->where(('user_profiles.user_id in (select user_id from user_exp where status=1 and REPLACE(lower(COALESCE(user_exp.company,""))," ","") like "%'.str_replace(' ','',strtolower($w)).'%" )'),null);
				
			}
			else if($v == 'status'){
				
				if($w=="1") $this->db->where(('(select count(person_id) from members join users on members.person_id=users.id where username<>"" and thru_date>=curdate() and person_id=user_profiles.user_id) > 0'),null,false);
				else $this->db->where(('(select count(person_id) from members join users on members.person_id=users.id where username<>"" and thru_date<curdate() and person_id=user_profiles.user_id) > 0'),null,false);
			}
			else if($v == 'filter_cab'){
				$this->db->where(('user_profiles.user_id in (select person_id from members where LPAD(members.code_wilayah, 4, "0") like "'.$w.'%")'),null);				
			}

//---------------------------------------------------------------------		Tambahan by IP	
			else if($v == 'filter_wil'){
				$this->db->where(('user_profiles.user_id in (select person_id from members where LPAD(members.wil_id, 2, "0") like "'.$w.'%")'),null);				
			}
//-------------------------------------------------------------------------			

			else if($v == 'filter_bk'){
				$this->db->where(('user_profiles.user_id in (select person_id from members where TRIM(LEADING "0" FROM code_bk_hkk)='.ltrim($w, '0').')'),null);
			}
			else if($v == 'filter_hkk'){
				$this->db->where(('user_profiles.user_id in (select person_id from members where TRIM(LEADING "0" FROM code_hkk)='.ltrim($w, '0').')'),null);
			}
			else if($v == 'jenis_anggota'){
			      
			//	$this->db->where(('user_profiles.user_id = (select person_id from members where LPAD(members.jenis_anggota,2,"0") ='.$w.' and person_id=user_profiles.user_id)'),null);
			//	$this->db->where(('user_profiles.user_id = (select members.person_id from members where TRIM(members.jenis_anggota) ='.$w.' and members.person_id=user_profiles.user_id)'),null);			
				$this->db->where(('user_profiles.user_id in (select person_id from members where LPAD(members.jenis_anggota, 2, "0") like "'.$w.'%")'),null);
			}
			else if($v == 'username'){
				$this->db->where(('user_profiles.user_id in (select person_id from members where no_kta like "%'.$w.'%")'),null);
			}
			else
				$this->db->like($v,$w);
		}
		
		//$this->db->like($search_parameters);
		$this->db->from($table_name);
		$this->db->join('users', 'user_profiles.user_id = users.id', 'left');
		
		
	if($is_kolektif)
	{
		$str = '';
		$i = 0;
			
		if(is_array($bk)){
			foreach($bk as $val)
			{
				if($i==0) $str .= " LPAD( code_bk_hkk, 2, '0' ) like '".$val."%'";
				else $str .= " or LPAD( code_bk_hkk, 2, '0' ) like '".$val."%'";
				$i++;
			}
		}
		else if($bk!=""){
			if($i==0) $str .= " LPAD( code_bk_hkk, 2, '0' ) like '".$bk."%'";
			else $str .= " or LPAD( code_bk_hkk, 2, '0' ) like '".$bk."%'";
			$i++;		
		}		
		if(is_array($wil)){
			foreach($wil as $val)
			{
				if($i==0) $str .= " LPAD( code_wilayah, 4, '0' ) like '".$val."%'";
				else $str .= " or LPAD( code_wilayah, 4, '0' ) like '".$val."%'";
				$i++;
			}
		}
		else if($wil!=""){
			if($i==0) $str .= " LPAD( code_wilayah, 4, '0' ) like '".$wil."%'";
			else $str .= " or LPAD( code_wilayah, 4, '0' ) like '".$wil."%'";
			$i++;		
		}
		
		
		$this->db->group_start();
		$this->db->where('FIND_IN_SET('. $this->session->userdata('admin_id').',kolektif_ids) <>',"0");
		if($str!='') $this->db->or_where('user_profiles.user_id in (select person_id from members where '.$str.')',null);
		$this->db->group_end();
	}	
		return $this->db->count_all_results();
		//exit;
    }
	
	public function search_all_non_kta($per_page, $page, $search_parameters, $wild_card='') {
		
		$where = ($wild_card=='yes')?'where':'like';
		$this->db->select('users.id as ID, username as no_kta, users.email, user_profiles.firstname, user_profiles.lastname, user_profiles.gender, user_profiles.idcard, user_profiles.dob, users.user_status as sts,		
		warga_asing,
		sertifikat_legal,
		tanda_bukti,
		surat_dukungan,
		surat_pernyataan,
		surat_ijin_domisili,
		(select atasnama from user_transfer where user_profiles.user_id=user_id and (pay_type=0 or pay_type=1) order by createddate desc limit 1) as payname,
		(select tgl from user_transfer where user_profiles.user_id=user_id and (pay_type=0 or pay_type=1) order by createddate desc limit 1) as paydate,
		(select description from user_transfer where user_profiles.user_id=user_id and (pay_type=0 or pay_type=1) order by createddate desc limit 1) as paydesc,
		(select bukti from user_transfer where user_profiles.user_id=user_id and (pay_type=0 or pay_type=1) order by createddate desc limit 1) as payfile,
		(select status from user_transfer where user_profiles.user_id=user_id and (pay_type=0 or pay_type=1) order by createddate desc limit 1) as paystatus,
		(select id from user_transfer where user_profiles.user_id=user_id and (pay_type=0 or pay_type=1) order by createddate desc limit 1) as payid,
		(select vnv_status from user_transfer where user_profiles.user_id=user_id and (pay_type=0 or pay_type=1) order by createddate desc limit 1) as vnv_status,
		(select remark from user_transfer where user_profiles.user_id=user_id and (pay_type=0 or pay_type=1) order by createddate desc limit 1) as remark');
        $this->db->from('user_profiles');
		$this->db->join('users', 'user_profiles.user_id = users.id', 'left');
		//$this->db->join('members', 'user_profiles.user_id = members.person_id', 'left');
		$this->db->where('username =""', null,false);
		//$this->db->where('user_id not in (select user_id from user_transfer where pay_type=5)', null,false);
		$this->db->where('user_id in (select user_id from user_transfer where pay_type=1)', null,false);
		
		$this->db->$where($search_parameters);
		//$this->db->order_by("user_profiles.id", "DESC"); 
		$this->db->limit($per_page, $page);
        $Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->result();
        } else {
            $return = 0;
        }
        $Q->free_result();
		//echo $this->db->last_query(); exit;
        return $return;
    }
	
	public function search_all_non_kta_2($per_page, $page, $search_parameters, $wild_card='',$bk,$wil,$is_kolektif) {
		
		$where = ($wild_card=='yes')?'where':'like';
		
		$this->db->select('`users`.`id` as `ID`, `username` as `no_kta`, `users`.`email`, 
		`users`.`user_status` as `sts`, created, 
		`firstname`, 
		`lastname`, 
		`gender`, 
		`idcard`, 
		`dob`,  
		`warga_asing`, 
		`sertifikat_legal`, 
		`tanda_bukti`, 
		`surat_dukungan`, 
		`surat_pernyataan`, 
		`surat_ijin_domisili`,(select no_kta from members where person_id=user_profiles.user_id) as no_kta_temp');
        $this->db->from('user_profiles');
		$this->db->join('users', 'user_profiles.user_id = users.id', 'left');
		//$this->db->join('members', 'user_profiles.user_id = members.person_id', 'left');
		$this->db->where('username =""', null,false);
		//$this->db->where('user_id not in (select user_id from user_transfer where pay_type=5)', null,false);
		$this->db->where('user_id in (select user_id from user_transfer where pay_type=1)', null,false);
		
		if($is_kolektif)
		{
			$str = '';
			$str2 = '';
			$i = 0;
			$i2 = 0;
				
			if(is_array($bk)){
				foreach($bk as $val)
				{
					if($i==0) $str .= " LPAD( code_bk_hkk, 2, '0' ) like '".$val."%'";
					else $str .= " or LPAD( code_bk_hkk, 2, '0' ) like '".$val."%'";
					$i++;
				}
			}
			else if($bk!=""){
				if($i==0) $str .= " LPAD( code_bk_hkk, 2, '0' ) like '".$bk."%'";
				else $str .= " or LPAD( code_bk_hkk, 2, '0' ) like '".$bk."%'";
				$i++;		
			}		
			if(is_array($wil)){
				foreach($wil as $val)
				{
					if($i2==0) $str2 .= " LPAD( code_wilayah, 4, '0' ) like '".$val."%'";
					else $str2 .= " or LPAD( code_wilayah, 4, '0' ) like '".$val."%'";
					$i2++;
				}
			}
			else if($wil!=""){
				if($i2==0) $str2 .= " LPAD( code_wilayah, 4, '0' ) like '".$wil."%'";
				else $str2 .= " or LPAD( code_wilayah, 4, '0' ) like '".$wil."%'";
				$i2++;		
			}
			
			
			$this->db->group_start();
			//$this->db->where('FIND_IN_SET('. $this->session->userdata('admin_id').',kolektif_ids) <>',"0");
			if($str!='') $this->db->where('user_profiles.user_id in (select person_id from members where '.$str.')',null);
			if($str2!='') $this->db->or_where('user_profiles.user_id in (select person_id from members where '.$str2.')',null);
			$this->db->group_end();
		}
		
		$this->db->$where($search_parameters);
		$str = $this->db->get_compiled_select();
		
		
		$str2 =  $this->db
		->select("d.*, 
(select atasnama from user_transfer where d.ID=user_id and (pay_type=0 or pay_type=1) order by createddate desc limit 1) as payname, 
(select tgl from user_transfer where d.ID=user_id and (pay_type=0 or pay_type=1) order by createddate desc limit 1) as paydate, 
(select description from user_transfer where d.ID=user_id and (pay_type=0 or pay_type=1) order by createddate desc limit 1) as paydesc, 
(select bukti from user_transfer where d.ID=user_id and (pay_type=0 or pay_type=1) order by createddate desc limit 1) as payfile, 
(select status from user_transfer where d.ID=user_id and (pay_type=0 or pay_type=1) order by createddate desc limit 1) as paystatus, 
(select id from user_transfer where d.ID=user_id and (pay_type=0 or pay_type=1) order by createddate desc limit 1) as payid, 
(select vnv_status from user_transfer where d.ID=user_id and (pay_type=0 or pay_type=1) order by createddate desc limit 1) as vnv_status, 
(select remark from user_transfer where d.ID=user_id and (pay_type=0 or pay_type=1) order by createddate desc limit 1) as remark,
(select createddate from user_transfer where d.ID=user_id and (pay_type=0 or pay_type=1) order by createddate desc limit 1) as tgl_pengajuan
", false)
		->from('('.$str.') as d')		
		->order_by("(CASE
			WHEN vnv_status = 1 THEN 2
			WHEN vnv_status = 2 THEN 1
			WHEN vnv_status = 0 THEN 0
			ELSE 1 END)", "ASC")
		->order_by("paystatus", "asc")
		->order_by("tgl_pengajuan", "asc")
		//->order_by("d.created", "DESC")
		->limit($per_page, $page)		
		->get_compiled_select();
		
		//(case when (select status from user_transfer where d.ID=user_id and (pay_type=0 or pay_type=1) order by createddate desc limit 1)=0 then 3 else (select status from user_transfer where d.ID=user_id and (pay_type=0 or pay_type=1) order by createddate desc limit 1) end) as seq
		
		
		$str2 = str_replace("`", "", $str2);		
		$Q = $this->db->query($str2);
		
        if ($Q->num_rows() > 0) {
            $return = $Q->result();
        } else {
            $return = 0;
        }
        $Q->free_result();
		//echo $this->db->last_query(); exit;
        return $return;
    }
	
	public function search_all_her_kta($per_page, $page, $search_parameters, $wild_card='',$bk,$wil,$is_kolektif) {
		
		$where = ($wild_card=='yes')?'where':'like';
		$this->db->select('users.id as ID, username as no_kta, users.email, user_profiles.firstname, user_profiles.lastname, user_profiles.gender, user_profiles.idcard, user_profiles.dob, users.user_status as sts,		
		warga_asing,
		sertifikat_legal,
		tanda_bukti,
		surat_dukungan,
		surat_pernyataan,
		surat_ijin_domisili,
		(select atasnama from user_transfer where user_transfer.pay_type=2 and user_profiles.user_id=user_id order by createddate desc limit 1) as payname,
		(select tgl from user_transfer where user_transfer.pay_type=2 and user_profiles.user_id=user_id order by createddate desc limit 1) as paydate,
		(select description from user_transfer where user_transfer.pay_type=2 and user_profiles.user_id=user_id order by createddate desc limit 1) as paydesc,
		(select bukti from user_transfer where user_transfer.pay_type=2 and user_profiles.user_id=user_id order by createddate desc limit 1) as payfile,
		(select status from user_transfer where user_transfer.pay_type=2 and user_profiles.user_id=user_id order by createddate desc limit 1) as paystatus,
		
		(select iuranpangkal from user_transfer where user_transfer.pay_type=2 and user_profiles.user_id=user_id order by createddate desc limit 1) as payiuranpangkal,
		(select iurantahunan from user_transfer where user_transfer.pay_type=2 and user_profiles.user_id=user_id order by createddate desc limit 1) as payiurantahunan,
		(select sukarelaanggota from user_transfer where user_transfer.pay_type=2 and user_profiles.user_id=user_id order by createddate desc limit 1) as paysukarelaanggota,
		(select sukarelagedung from user_transfer where user_transfer.pay_type=2 and user_profiles.user_id=user_id order by createddate desc limit 1) as paysukarelagedung,
		(select sukarelaperpus from user_transfer where user_transfer.pay_type=2 and user_profiles.user_id=user_id order by createddate desc limit 1) as paysukarelaperpus,
		(select sukarelaceps from user_transfer where user_transfer.pay_type=2 and user_profiles.user_id=user_id order by createddate desc limit 1) as paysukarelaceps,
		(select sukarelatotal from user_transfer where user_transfer.pay_type=2 and user_profiles.user_id=user_id order by createddate desc limit 1) as paysukarelatotal,
		
		(select id from user_transfer where user_transfer.pay_type=2 and user_profiles.user_id=user_id order by createddate desc limit 1) as payid,
		
		(select lic_num from user_cert where user_id=users.id and status=2 order by endyear desc,ip_tipe desc limit 1) as sip_lic_num,
		(select startyear from user_cert where user_id=users.id and status=2 order by endyear desc,ip_tipe desc limit 1) as sip_startyear,
		(select endyear from user_cert where user_id=users.id and status=2 order by endyear desc,ip_tipe desc limit 1) as sip_endyear,
		
		(SELECT id_pay from user_transfer x join log_her_kta y on y.id_pay=x.id WHERE x.pay_type=2 and x.user_id =user_profiles.user_id order by x.createddate desc limit 1) as id_pay, 
		(SELECT x.id from user_transfer x WHERE x.pay_type=2 and x.user_id =user_profiles.user_id order by x.createddate desc limit 1) as id_pay_cek, 

		(SELECT from_date from user_transfer x join log_her_kta y on y.id_pay=x.id WHERE x.pay_type=2 and x.user_id =user_profiles.user_id order by x.createddate desc limit 1) as plan_from_date, 

		(SELECT thru_date from user_transfer x join log_her_kta y on y.id_pay=x.id WHERE x.pay_type=2 and x.user_id =user_profiles.user_id order by x.createddate desc limit 1) as plan_thru_date, 
		
		(select vnv_status from user_transfer where user_transfer.pay_type=2 and user_profiles.user_id=user_id order by createddate desc limit 1) as vnv_status,
		(select remark from user_transfer where user_transfer.pay_type=2 and user_profiles.user_id=user_id order by createddate desc limit 1) as remark,
		
		(select code_wilayah from members where person_id=users.id limit 1) as cab,(select code_bk_hkk from members where person_id=users.id limit 1) as bk,(select from_date from members where person_id=users.id limit 1) as from_date,(select thru_date from members where person_id=users.id limit 1) as thru_date,
		(select createddate from user_transfer where user_transfer.pay_type=2 and user_profiles.user_id=user_id order by createddate desc limit 1) as tgl_pengajuan');
        $this->db->from('user_profiles');
		$this->db->join('users', 'user_profiles.user_id = users.id', 'left');
		//$this->db->join('members', 'user_profiles.user_id = members.person_id', 'left');
		$this->db->where('users.id in (select user_id from user_transfer where pay_type =2)', null,false);
		
		$this->db->order_by("(CASE
			WHEN vnv_status = 1 THEN 2
			WHEN vnv_status = 2 THEN 1
			WHEN vnv_status = 0 THEN 0
			ELSE 1 END)", "ASC");
		$this->db->order_by("paystatus", "asc"); 
		$this->db->order_by("tgl_pengajuan", "asc"); 
		
		//$this->db->$where($search_parameters);
		foreach($search_parameters as $v=>$w){
			if($v == 'filter_cab'){
				$this->db->where(('user_profiles.user_id in (select person_id from members where TRIM(LEADING "0" FROM code_wilayah)='.ltrim($w, '0').')'),null);
			}			
			else if($v == 'filter_bk'){
				$this->db->where(('user_profiles.user_id in (select person_id from members where TRIM(LEADING "0" FROM code_bk_hkk)='.ltrim($w, '0').')'),null);
			}
			else
				$this->db->$where($v,$w);
		}
		
		if($is_kolektif)
		{
			$str = '';
			$str2 = '';
			$i = 0;
			$i2 = 0;
				
			if(is_array($bk)){
				foreach($bk as $val)
				{
					if($i==0) $str .= " LPAD( code_bk_hkk, 2, '0' ) like '".$val."%'";
					else $str .= " or LPAD( code_bk_hkk, 2, '0' ) like '".$val."%'";
					$i++;
				}
			}
			else if($bk!=""){
				if($i==0) $str .= " LPAD( code_bk_hkk, 2, '0' ) like '".$bk."%'";
				else $str .= " or LPAD( code_bk_hkk, 2, '0' ) like '".$bk."%'";
				$i++;		
			}		
			if(is_array($wil)){
				foreach($wil as $val)
				{
					if($i2==0) $str2 .= " LPAD( code_wilayah, 4, '0' ) like '".$val."%'";
					else $str2 .= " or LPAD( code_wilayah, 4, '0' ) like '".$val."%'";
					$i2++;
				}
			}
			else if($wil!=""){
				if($i2==0) $str2 .= " LPAD( code_wilayah, 4, '0' ) like '".$wil."%'";
				else $str2 .= " or LPAD( code_wilayah, 4, '0' ) like '".$wil."%'";
				$i2++;		
			}
			
			
			$this->db->group_start();
			//$this->db->where('FIND_IN_SET('. $this->session->userdata('admin_id').',kolektif_ids) <>',"0");
			if($str!='') $this->db->where('user_profiles.user_id in (select person_id from members where '.$str.')',null);
			if($str2!='') $this->db->or_where('user_profiles.user_id in (select person_id from members where '.$str2.')',null);
			$this->db->group_end();
		}
		
		//$this->db->order_by("user_profiles.id", "DESC"); 
		$this->db->limit($per_page, $page);
        $Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->result();
        } else {
            $return = 0;
        }
        $Q->free_result();
		//echo $this->db->last_query(); exit;
        return $return;
    }
	
	public function search_all_her_kta_2($per_page, $page, $search_parameters, $wild_card='') {
		
		$where = ($wild_card=='yes')?'where':'like';
		/*$this->db->select('users.id as ID, username as no_kta, users.email, user_profiles.firstname, user_profiles.lastname, user_profiles.gender, user_profiles.idcard, user_profiles.dob, users.user_status as sts,
		(select atasnama from user_transfer where user_profiles.user_id=user_id order by createddate desc limit 1) as payname,
		(select tgl from user_transfer where user_profiles.user_id=user_id order by createddate desc limit 1) as paydate,
		(select description from user_transfer where user_profiles.user_id=user_id order by createddate desc limit 1) as paydesc,
		(select bukti from user_transfer where user_profiles.user_id=user_id order by createddate desc limit 1) as payfile,
		(select status from user_transfer where user_profiles.user_id=user_id order by createddate desc limit 1) as paystatus,
		
		(select iuranpangkal from user_transfer where user_profiles.user_id=user_id order by createddate desc limit 1) as payiuranpangkal,
		(select iurantahunan from user_transfer where user_profiles.user_id=user_id order by createddate desc limit 1) as payiurantahunan,
		(select sukarelaanggota from user_transfer where user_profiles.user_id=user_id order by createddate desc limit 1) as paysukarelaanggota,
		(select sukarelagedung from user_transfer where user_profiles.user_id=user_id order by createddate desc limit 1) as paysukarelagedung,
		(select sukarelaperpus from user_transfer where user_profiles.user_id=user_id order by createddate desc limit 1) as paysukarelaperpus,
		(select sukarelaceps from user_transfer where user_profiles.user_id=user_id order by createddate desc limit 1) as paysukarelaceps,
		(select sukarelatotal from user_transfer where user_profiles.user_id=user_id order by createddate desc limit 1) as paysukarelatotal,
		
		(select id from user_transfer where user_profiles.user_id=user_id order by createddate desc limit 1) as payid,
		(SELECT id_pay FROM log_her_kta JOIN user_transfer ON user_transfer.user_id = log_her_kta.user_id WHERE id_pay <>0 AND log_her_kta.user_id =user_profiles.user_id order by log_her_kta.createddate desc limit 1) as id_pay,
		
		(select code_wilayah from members where person_id=users.id limit 1) as cab,(select code_bk_hkk from members where person_id=users.id limit 1) as bk,(select from_date from members where person_id=users.id limit 1) as from_date,(select thru_date from members where person_id=users.id limit 1) as thru_date');
        $this->db->from('user_profiles');
		$this->db->join('users', 'user_profiles.user_id = users.id', 'left');
		//$this->db->join('members', 'user_profiles.user_id = members.person_id', 'left');
		$this->db->where('users.id in (select user_id from user_transfer where pay_type =2)', null,false);
		*/
		$this->db->select('users.id as ID, username as no_kta, users.email, user_profiles.firstname, user_profiles.lastname, user_profiles.gender, user_profiles.idcard, user_profiles.dob, users.user_status as sts,
		
		user_transfer.atasnama as payname,
		user_transfer.tgl as paydate,
		user_transfer.description as paydesc,
		user_transfer.bukti as payfile,
		user_transfer.status as paystatus,
		user_transfer.iuranpangkal as payiuranpangkal,
		user_transfer.iurantahunan as payiurantahunan,
		user_transfer.sukarelaanggota as paysukarelaanggota,
		user_transfer.sukarelagedung as paysukarelagedung,
		user_transfer.sukarelaperpus as paysukarelaperpus,
		user_transfer.sukarelaceps as paysukarelaceps,
		user_transfer.sukarelatotal as paysukarelatotal,
		user_transfer.id as payid,
		
		(SELECT id_pay FROM log_her_kta WHERE id_pay =user_transfer.id order by log_her_kta.createddate desc limit 1) as id_pay,
		
		(select code_wilayah from members where person_id=users.id limit 1) as cab,(select code_bk_hkk from members where person_id=users.id limit 1) as bk,(select from_date from members where person_id=users.id limit 1) as from_date,(select thru_date from members where person_id=users.id limit 1) as thru_date');
        $this->db->from('user_transfer');
		$this->db->join('users', 'user_transfer.user_id = users.id', 'left');
		$this->db->join('user_profiles', 'user_profiles.user_id = user_transfer.user_id', 'left');
		$this->db->where('pay_type =2', null,false);
		
		
		$this->db->$where($search_parameters);
		//$this->db->order_by("user_profiles.id", "DESC"); 
		$this->db->limit($per_page, $page);
        $Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->result();
        } else {
            $return = 0;
        }
        $Q->free_result();
		//echo $this->db->last_query(); exit;
        return $return;
    }
	
	public function search_all_stri_member($per_page, $page, $search_parameters, $wild_card='') {
		
		$where = ($wild_card=='yes')?'where':'like';
		$this->db->select('users.id as ID, username as no_kta, users.email,user_profiles.firstname, user_profiles.lastname, user_profiles.gender, user_profiles.idcard, user_profiles.dob, users.user_status as sts,
		
		user_transfer.atasnama as payname,
		user_transfer.tgl as paydate,
		user_transfer.description as paydesc,
		user_transfer.bukti as payfile,
		user_transfer.status as paystatus,
		
		user_transfer.iuranpangkal as payiuranpangkal,
		user_transfer.iurantahunan as payiurantahunan,
		user_transfer.sukarelaanggota as paysukarelaanggota,
		user_transfer.sukarelagedung as paysukarelagedung,
		user_transfer.sukarelaperpus as paysukarelaperpus,
		user_transfer.sukarelaceps as paysukarelaceps,
		user_transfer.sukarelatotal as paysukarelatotal,
		
		user_transfer.vnv_status as vnv_status,
		user_transfer.remark as remark,
		user_transfer.add_doc1 as add_doc1,
		user_transfer.add_doc2 as add_doc2,
		user_transfer.sip as sip,
		
		(select lic_num from user_cert where user_transfer.rel_id=id and status=2 order by createddate desc limit 1) as lic_num,
		(select cert_title from user_cert where user_transfer.rel_id=id and status=2 order by createddate desc limit 1) as cert_title,
		(select startyear from user_cert where user_transfer.rel_id=id and status=2 order by createddate desc limit 1) as startyear,
		(select endyear from user_cert where user_transfer.rel_id=id and status=2 order by createddate desc limit 1) as endyear,
		
		
		user_transfer.id as payid,
		user_transfer.createddate as createddate,
		
		(SELECT id_pay FROM log_stri WHERE id_pay = user_transfer.id order by log_stri.createddate desc limit 1) as id_pay,
		
		(select code_wilayah from members where person_id=users.id limit 1) as cab,(select code_bk_hkk from members where person_id=users.id limit 1) as bk,(select from_date from members where person_id=users.id limit 1) as from_date,(select thru_date from members where person_id=users.id limit 1) as thru_date,members.jenis_anggota
		,(select name from m_kolektif where status=1 and id=user_profiles.kolektif_name_id) as kolektif_name');
        $this->db->from('user_profiles');
		$this->db->join('users', 'user_profiles.user_id = users.id', 'left');
		$this->db->join('user_transfer', 'user_profiles.user_id = user_transfer.user_id', 'left');
		$this->db->join('members', 'user_profiles.user_id = members.person_id', 'left');
		
		//$this->db->where('username =""', null,false);
		
		//$this->db->where('users.id in (select user_id from user_transfer where pay_type =5)', null,false);
		$this->db->where('pay_type', "5");
		$this->db->where('user_transfer.id not in (select id_pay from log_stri)', null,false);
		$this->db->where('username <> ""', null,false);
		//$this->db->where('user_transfer.vnv_status in (0,1)', null,false);
		
		/*$this->db->group_start();
		$this->db->where(('user_transfer.vnv_status = 0 and user_transfer.status=0 and (select count(id) from users where id = user_transfer.user_id and username<>"")=1'),null);
		$this->db->or_where(('((select count(id) from log_stri where user_id = user_transfer.user_id and id_pay = user_transfer.id)=0 and (select count(id) from users where id = user_transfer.user_id and username<>"")=1 and user_transfer.status = 1 and (
				CASE WHEN user_transfer.rel_id=0 THEN user_transfer.vnv_status=1 ELSE 1=1 END)
				)'),null);
		$this->db->group_end();*/
		
		if($this->session->userdata('type')=="13")
		{
			$this->db->where('rel_id',"0");
			$this->db->order_by("createddate", "DESC");
		}
		/*else if($this->session->userdata('type')=="12")
		{
			$this->db->where('rel_id<>0',null);
		}*/
		
		foreach($search_parameters as $v=>$w){
			if($v == 'filter_cab'){
				$this->db->where(('LPAD(members.code_wilayah, 4, "0") like "'.$w.'%"'),null);
			}
			else if($v == 'filter_bk'){
				$this->db->where(('LPAD(members.code_bk_hkk, 2, "0") like "'.$w.'%"'),null);
			}
			else if($v == 'filter_hkk'){
				$this->db->where(('LPAD(members.code_hkk, 2, "0") like "'.$w.'%"'),null);
			}
			else if($v == 'filter_type'){
				if($w=='1') $this->db->where(('user_transfer.rel_id <> 0'),null);
				else if($w=='2') $this->db->where(('user_transfer.rel_id = 0'),null);
			}
			else if($v == 'filter_status'){
				/*if($w=='1') $this->db->where(('user_transfer.status = 0'),null);
				else if($w=='2') $this->db->where(('user_transfer.status = 1 and (
				CASE WHEN user_transfer.rel_id=0 THEN user_transfer.vnv_status = 0 WHEN user_transfer.rel_id<>0 THEN (select count(id) from log_stri where user_id = user_transfer.user_id and id_pay = user_transfer.id)=0 ELSE 1=1 END)'),null);
				else if($w=='3') $this->db->where(('user_transfer.status = 2'),null);
				else if($w=='4') $this->db->where(('user_transfer.status = 1 and user_transfer.vnv_status = 1 and (select count(person_id) from members where person_id = user_transfer.user_id)=0'),null);*/
				
				if($w=='4') $this->db->where(('user_transfer.vnv_status=0'),null);
				
				else if($w=='5') $this->db->where(('user_transfer.vnv_status = 2'),null);
				
				else if($w=='6') $this->db->where(('user_transfer.vnv_status=1'),null);
				
				//else if($w=='7') $this->db->where(('(select count(id) from log_stri where user_id = user_transfer.user_id and id_pay = user_transfer.id)>=1'),null);
			}		
			else if($v == 'stri_period'){
				$this->db->where(($w),null);
			}
			else
				$this->db->$where($v,$w);
		}
		//$this->db->$where($search_parameters);
		
		
		//$this->db->order_by("user_profiles.id", "DESC"); 
		$this->db->limit($per_page, $page);
        $Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->result();
        } else {
            $return = 0;
        }
        $Q->free_result();
		//echo $this->db->last_query(); exit;
        return $return;
    }
	
	/**
	 * ASK: Function ini masih dipakai? Sepertinya sudah diganti dengan search_all_finance_2()
	 * Nested SQL Statementnya di function ini tidak efektif
	 */
	public function search_all_finance($per_page, $page, $search_parameters, $wild_card='') {
		
		$where = ($wild_card=='yes')?'where':'like';

		// 
		$this->db->select('users.id as ID, username as no_kta, users.email, user_profiles.firstname, user_profiles.lastname, user_profiles.gender, user_profiles.idcard, user_profiles.dob, users.user_status as sts,
		(select pay_type from user_transfer where user_profiles.user_id=user_id order by createddate desc limit 1) as paytype,
		(select atasnama from user_transfer where user_profiles.user_id=user_id order by createddate desc limit 1) as payname,
		(select tgl from user_transfer where user_profiles.user_id=user_id order by createddate desc limit 1) as paydate,
		(select description from user_transfer where user_profiles.user_id=user_id order by createddate desc limit 1) as paydesc,
		(select bukti from user_transfer where user_profiles.user_id=user_id order by createddate desc limit 1) as payfile,
		(select status from user_transfer where user_profiles.user_id=user_id order by createddate desc limit 1) as paystatus,
		
		(select iuranpangkal from user_transfer where user_profiles.user_id=user_id order by createddate desc limit 1) as payiuranpangkal,
		(select iurantahunan from user_transfer where user_profiles.user_id=user_id order by createddate desc limit 1) as payiurantahunan,
		(select sukarelaanggota from user_transfer where user_profiles.user_id=user_id order by createddate desc limit 1) as paysukarelaanggota,
		(select sukarelagedung from user_transfer where user_profiles.user_id=user_id order by createddate desc limit 1) as paysukarelagedung,
		(select sukarelaperpus from user_transfer where user_profiles.user_id=user_id order by createddate desc limit 1) as paysukarelaperpus,
		(select sukarelaceps from user_transfer where user_profiles.user_id=user_id order by createddate desc limit 1) as paysukarelaceps,
		(select sukarelatotal from user_transfer where user_profiles.user_id=user_id order by createddate desc limit 1) as paysukarelatotal,
		
		(select lic_num from user_cert where user_id=users.id and status=2 order by endyear desc,ip_tipe desc limit 1) as sip_lic_num,
		(select startyear from user_cert where user_id=users.id and status=2 order by endyear desc,ip_tipe desc limit 1) as sip_startyear,
		(select endyear from user_cert where user_id=users.id and status=2 order by endyear desc,ip_tipe desc limit 1) as sip_endyear,
		
		(select id from user_transfer where user_profiles.user_id=user_id order by createddate desc limit 1) as payid,
		(select from_date from members where person_id=users.id limit 1) as from_date,
		(select thru_date from members where person_id=users.id limit 1) as thru_date');
        $this->db->from('user_profiles');
		$this->db->join('users', 'user_profiles.user_id = users.id', 'left');
		//$this->db->join('members', 'user_profiles.user_id = members.person_id', 'left');
		//$this->db->where('username =""', null,false);
		
		$this->db->where('users.id in (select user_id from user_transfer)', null,false);
		
		
		foreach($search_parameters as $v=>$w){
			if($v == 'filter_type'){
				if($w=='1') $this->db->where(('users.id in (select user_id from user_transfer where pay_type=0 or pay_type=1)'),null);
				else if($w=='2') $this->db->where(('users.id in (select user_id from user_transfer where pay_type=2)'),null);
				else if($w=='3') $this->db->where(('users.id in (select user_id from user_transfer where pay_type=3 or pay_type=4)'),null);
				else if($w=='4') $this->db->where(('users.id in (select user_id from user_transfer where pay_type=5)'),null);
			}
			else
				$this->db->$where($v,$w);
		}
		//$this->db->$where($search_parameters);
		//$this->db->order_by("user_profiles.id", "DESC"); 
		$this->db->limit($per_page, $page);
        $Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->result();
        } else {
            $return = 0;
        }
        $Q->free_result();
		//echo $this->db->last_query(); exit;
        return $return;
    }
	
	/**
	 * Tampilan di halaman utama "Validasi Finance"
	 */
	public function search_all_finance_2($per_page, $page, $search_parameters, $wild_card='') {
		

		// 202407007 - Join dengan log_her_kta karena from_date dan thru_date di tabel member tidak historical
		// sehingga untuk member yang sudah membayar HER beberapa kali makan from_date dan thru_date akan 
		// sama semua
		//ROLLBACK dulu
		$where = ($wild_card=='yes')?'where':'like';
		$this->db->select('user_transfer.id as ut_id, users.id as ID, username as no_kta, users.email, user_profiles.firstname, user_profiles.lastname, user_profiles.gender, user_profiles.idcard, user_profiles.dob, users.user_status as sts,
		pay_type,		
		log_her_kta.from_date, log_her_kta.thru_date,
		user_profiles.va, is_upload_mandiri,
		(select x.status from user_transfer x where user_profiles.user_id=x.user_id and user_transfer.pay_type=x.pay_type order by x.createddate desc limit 1) as paystatus,
		(select name from m_kolektif where status=1 and id=user_profiles.kolektif_name_id) as kolektif_name');
        $this->db->from('user_transfer');
		$this->db->join('log_her_kta', 'log_her_kta.id_pay = user_transfer.id', 'left');
		$this->db->join('users', 'user_transfer.user_id = users.id', 'left');
		$this->db->join('user_profiles', 'user_profiles.user_id = user_transfer.user_id', 'left');
		$this->db->join('members', 'user_transfer.user_id = members.person_id', 'left');
		$this->db->where('(CASE WHEN pay_type=5 THEN vnv_status=1 ELSE 1=1 END)', null, false);//and rel_id=0 
		/*
		user_transfer.pay_type as paytype,
		user_transfer.atasnama as payname,
		user_transfer.tgl as paydate,
		user_transfer.description as paydesc,
		user_transfer.bukti as payfile,
		user_transfer.status as paystatus,
		user_transfer.iuranpangkal as payiuranpangkal,
		user_transfer.iurantahunan as payiurantahunan,
		user_transfer.sukarelaanggota as paysukarelaanggota,
		user_transfer.sukarelagedung as paysukarelagedung,
		user_transfer.sukarelaperpus as paysukarelaperpus,
		user_transfer.sukarelaceps as paysukarelaceps,
		user_transfer.sukarelatotal as paysukarelatotal,
		user_transfer.id as payid,*/
		
		if($this->session->userdata('admin_id')=="673" ){
			$this->db->where('pay_type in (3,4)', null,false);
		}
		else if($this->session->userdata('admin_id')=="675" ){
			$this->db->where('pay_type in (1,2)', null,false);
		}
		
		// ER: Comment this 29 May 2024 SIMX52
		//$this->db->group_by('user_transfer.user_id, user_transfer.pay_type');
		
		//$this->db->order_by("user_transfer.modifieddate", "desc");		
		//$this->db->order_by("user_profiles.createddate", "DESC");
		
		
		foreach($search_parameters as $v=>$w){
			if($v == 'filter_type'){
				if($w=='1') $this->db->where(('(pay_type=0 or pay_type=1)'),null);
				else if($w=='2') $this->db->where(('(pay_type=2)'),null);
				else if($w=='3') $this->db->where(('(pay_type=3 or pay_type=4)'),null);
				else if($w=='4') $this->db->where(('(pay_type=5)'),null);
				else if($w=='5') $this->db->where(('(pay_type=6 or pay_type=7)'),null);
			}
			else if($v == 'user_transfer.status'){
				if($w=='0') $this->db->having('paystatus',  0);
				else if($w=='1') $this->db->having('paystatus',  1);
				else if($w=='2') $this->db->having('paystatus',  2);
			}
			else
				$this->db->$where($v,$w);
		}
		//$this->db->$where($search_parameters);
		//$this->db->order_by("user_profiles.id", "DESC"); 
		$this->db->limit($per_page, $page);
        $Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->result();
        } else {
            $return = 0;
        }
        $Q->free_result();
		//echo $this->db->last_query(); exit;
        return $return;
    }
	
	public function search_all_stri($per_page, $page, $search_parameters, $wild_card='',$bk,$wil,$is_kolektif) {
		
		$where = ($wild_card=='yes')?'where':'like';
		$this->db->select('e.createddate as tgl,members_certificate.person_id as ID,add_name, members_certificate.status as sts,m_kolektif.category as ishkk,members_certificate.*');
        $this->db->from('members_certificate');//user_profiles.firstname
		$this->db->join('user_profiles', 'user_profiles.user_id = members_certificate.person_id');
		$this->db->join('m_kolektif', 'user_profiles.kolektif_name_id = m_kolektif.id', 'left');
		$this->db->join('members', 'members_certificate.person_id = members.person_id');
		$this->db->join('log_stri b', 'b.id = (
			SELECT
			  max(fa.id) a_id
			FROM log_stri fa 
			WHERE fa.stri_id = members_certificate.stri_id and fa.user_id=members_certificate.person_id
		  )', 'left',false);
		$this->db->join('user_transfer e', 'e.id = b.id_pay', 'left');
		//$this->db->where('stri_code_wilayah <> ""', null,false);
		
		$this->db->where('members_certificate.status', "1");
		
		foreach($search_parameters as $v=>$w){
			if($v == 'filter_cab'){
				$this->db->where(('LPAD(stri_code_wilayah, 4, "0") like "'.$w.'%"'),null);
			}
			else if($v == 'filter_bk'){
				$this->db->where(('LPAD(stri_code_bk_hkk, 2, "0") like "'.$w.'%"'),null);
			}
			else if($v == 'filter_hkk'){
				$this->db->where(('LPAD(members.code_hkk, 2, "0") like "'.$w.'%"'),null);
			}
			else if($v == 'filter_type'){
				$this->db->where('certificate_type',$w);
			}
			else if($v == 'filter_status'){
				if($w=='1') $this->db->where(('stri_thru_date >= now()'),null);
				else if($w=='0') $this->db->where(('stri_thru_date < now()'),null);
			}
			else if($v == 'nomor'){
				$this->db->where('TRIM(LEADING "0" FROM members_certificate.stri_id) = "'.ltrim($w, '0').'"',null);
			}
			else if($v == 'stri_period'){
				$this->db->where(($w),null);
			}
			else
				$this->db->$where($v,$w);
		}
		//$this->db->$where($search_parameters);
		
		if($is_kolektif)
		{
			$str = '';
			$str2 = '';
			$i = 0;
			$i2 = 0;
				
			if(is_array($bk)){
				foreach($bk as $val)
				{
					if($i==0) $str .= " LPAD( stri_code_bk_hkk, 2, '0' ) like '".$val."%'";
					else $str .= " or LPAD( stri_code_bk_hkk, 2, '0' ) like '".$val."%'";
					$i++;
				}
			}
			else if($bk!=""){
				if($i==0) $str .= " LPAD( stri_code_bk_hkk, 2, '0' ) like '".$bk."%'";
				else $str .= " or LPAD( stri_code_bk_hkk, 2, '0' ) like '".$bk."%'";
				$i++;		
			}		
			if(is_array($wil)){
				foreach($wil as $val)
				{
					if($i2==0) $str2 .= " LPAD( code_wilayah, 4, '0' ) like '".$val."%'";
					else $str2 .= " or LPAD( code_wilayah, 4, '0' ) like '".$val."%'";
					$i2++;
				}
			}
			else if($wil!=""){
				if($i2==0) $str2 .= " LPAD( code_wilayah, 4, '0' ) like '".$wil."%'";
				else $str2 .= " or LPAD( code_wilayah, 4, '0' ) like '".$wil."%'";
				$i2++;		
			}
			
			
			$this->db->group_start();
			//$this->db->where('FIND_IN_SET('. $this->session->userdata('admin_id').',kolektif_ids) <>',"0");
			if($str!='') $this->db->where($str,null);
			if($str2!='') $this->db->where('members_certificate.person_id in (select person_id from members where '.$str2.')',null);
			$this->db->group_end();
		}
		
		//$this->db->order_by("user_profiles.id", "DESC"); 
		$this->db->limit($per_page, $page);
        $Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->result();
        } else {
            $return = 0;
        }
        $Q->free_result();
		//echo $this->db->last_query(); exit;
        return $return;
    }
	
	/**
	 * ER: FIXME: Remove this function
	 * @deprecated table `skip3` is not exist
	 */
	public function search_all_skip($per_page, $page, $search_parameters, $wild_card='') {
		
		$where = ($wild_card=='yes')?'where':'like';
		/*$this->db->select('person_id as ID,add_name as firstname, user_profiles.lastname, status as sts,members_certificate.*');
        $this->db->from('members_certificate');//user_profiles.firstname
		$this->db->join('user_profiles', 'user_profiles.user_id = members_certificate.person_id', 'left');
		//$this->db->join('members', 'user_profiles.user_id = members.person_id', 'left');
		$this->db->where('skip_id <> ""', null,false);
		*/
		
		$this->db->select('id as ID,a.*');
		$this->db->from('skip3 a');
		$this->db->where('sk_end > now()', null,false);
		$this->db->where('noip<>""', null,false);
		$this->db->group_by('kta,sk_from,sertid');
		$this->db->having('sertid=(select max(sertid) from skip3 where kta=a.kta)');
		$this->db->having('sk_from=(select max(sk_from) from skip3 where kta=a.kta)');
		//$this->db->order_by("sk_from", "DESC"); 
		
		
		$this->db->$where($search_parameters);
		//$this->db->order_by("user_profiles.id", "DESC"); 
		$this->db->limit($per_page, $page);
        $Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->result();
        } else {
            $return = 0;
        }
        $Q->free_result();
		//echo $this->db->last_query(); exit;
        return $return;
    }
	
	public function search_all_pi($per_page, $page, $search_parameters, $wild_card='') {
		
		$where = ($wild_card=='yes')?'where':'like';
		 $this->db->select('person_id as ID,add_name as firstname, user_profiles.lastname, status as sts,members_certificate.*');
        $this->db->from('members_certificate');//user_profiles.firstname
		$this->db->join('user_profiles', 'user_profiles.user_id = members_certificate.person_id', 'left');
		//$this->db->join('members', 'user_profiles.user_id = members.person_id', 'left');
		$this->db->where('skip_id <> ""', null,false);
		
		
		$this->db->$where($search_parameters);
		//$this->db->order_by("user_profiles.id", "DESC"); 
		$this->db->limit($per_page, $page);
        $Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->result();
        } else {
            $return = 0;
        }
        $Q->free_result();
		//echo $this->db->last_query(); exit;
        return $return;
    }

	public function search_all_faip($per_page, $page, $search_parameters, $wild_card='') {
		
		$where = ($wild_card=='yes')?'where':'like';
		 $this->db->select(
		'(select CONCAT(COALESCE(firstname,""),COALESCE(lastname,"")) from user_profiles where user_id=user_faip.majelis1) as asesor1,
		(select CONCAT(COALESCE(firstname,""),COALESCE(lastname,"")) from user_profiles where user_id=user_faip.majelis2) as asesor2,
		(select CONCAT(COALESCE(firstname,""),COALESCE(lastname,"")) from user_profiles where user_id=user_faip.majelis3) as asesor3,
		user_faip.id as ID,(select name from m_cab where TRIM(LEADING "0" FROM value)=TRIM(LEADING "0" FROM members.code_wilayah)) as wil_name,
		(select name from m_bk where TRIM(LEADING "0" FROM value)=TRIM(LEADING "0" FROM members.code_bk_hkk)) as bk_name,
		(select name from m_bk where TRIM(LEADING "0" FROM value)=TRIM(LEADING "0" FROM user_faip.bidang)) as bk_faip,
		members.code_bk_hkk as bk,
		members.code_wilayah as wil,
		(select name from m_faip_status where value=user_faip.status_faip) as status_name, 
		user_faip.*,
		(select GROUP_CONCAT(createdby) from asesor_faip where status=2 and faip_id=user_faip.id) as score_id,
		(select GROUP_CONCAT(wajib1_score) from asesor_faip where status=2 and faip_id=user_faip.id) as wajib1_score_,
		(select GROUP_CONCAT(wajib2_score) from asesor_faip where status=2 and faip_id=user_faip.id) as wajib2_score_,
		(select GROUP_CONCAT(wajib3_score) from asesor_faip where status=2 and faip_id=user_faip.id) as wajib3_score_,
		(select GROUP_CONCAT(wajib4_score) from asesor_faip where status=2 and faip_id=user_faip.id) as wajib4_score_,
		(select GROUP_CONCAT(pilihan_score) from asesor_faip where status=2 and faip_id=user_faip.id) as pilihan_score_,
		 (select GROUP_CONCAT(total_score) from asesor_faip where status=2 and faip_id=user_faip.id) as score,
		(select GROUP_CONCAT(keputusan) from asesor_faip where status=2 and faip_id=user_faip.id) as keputusan,
		(select keputusan from asesor_faip join admin on asesor_faip.createdby=admin.id where type=11 and TRIM(LEADING "0" FROM code_bk_hkk) =TRIM(LEADING "0" FROM user_faip.bidang) and status=2 and faip_id=user_faip.id order by asesor_faip.id desc limit 1) as keputusan_bk,
		(select total_score from asesor_faip join admin on asesor_faip.createdby=admin.id where type=11 and TRIM(LEADING "0" FROM code_bk_hkk) =TRIM(LEADING "0" FROM user_faip.bidang) and status=2 and faip_id=user_faip.id order by asesor_faip.id desc limit 1) as score_bk,
		
		(select wajib1_score from asesor_faip join admin on asesor_faip.createdby=admin.id where type=11 and TRIM(LEADING "0" FROM code_bk_hkk) =TRIM(LEADING "0" FROM user_faip.bidang) and status=2 and faip_id=user_faip.id order by asesor_faip.id desc limit 1) as wajib1_score_bk,
		(select wajib2_score from asesor_faip join admin on asesor_faip.createdby=admin.id where type=11 and TRIM(LEADING "0" FROM code_bk_hkk) =TRIM(LEADING "0" FROM user_faip.bidang) and status=2 and faip_id=user_faip.id order by asesor_faip.id desc limit 1) as wajib2_score_bk,
		(select wajib3_score from asesor_faip join admin on asesor_faip.createdby=admin.id where type=11 and TRIM(LEADING "0" FROM code_bk_hkk) =TRIM(LEADING "0" FROM user_faip.bidang) and status=2 and faip_id=user_faip.id order by asesor_faip.id desc limit 1) as wajib3_score_bk,
		(select wajib4_score from asesor_faip join admin on asesor_faip.createdby=admin.id where type=11 and TRIM(LEADING "0" FROM code_bk_hkk) =TRIM(LEADING "0" FROM user_faip.bidang) and status=2 and faip_id=user_faip.id order by asesor_faip.id desc limit 1) as wajib4_score_bk,
		(select pilihan_score from asesor_faip join admin on asesor_faip.createdby=admin.id where type=11 and TRIM(LEADING "0" FROM code_bk_hkk) =TRIM(LEADING "0" FROM user_faip.bidang) and status=2 and faip_id=user_faip.id order by asesor_faip.id desc limit 1) as pilihan_score_bk,
		
		remarks,user_faip.keputusan as keputusan_manual,
		
		(select createddate from log_status_faip where faip_id=user_faip.id and new_status=11 order by createddate desc limit 1) as tgl_sip_to_print
		
		 ');
        
		// (select lic_num from user_cert where status=2 and cert_url=user_faip.id order by id desc limit 1) as lic_num,
		
		$this->db->from('user_faip');
		$this->db->join('user_profiles', 'user_profiles.user_id = user_faip.user_id', 'left');
		$this->db->join('members', 'members.person_id = user_faip.user_id', 'left');
		$this->db->join('m_faip_status', 'm_faip_status.value = user_faip.status_faip', 'left');
		$this->db->where('user_faip.status', 1);
		$this->db->where('status_faip<>0');
		
		if($this->session->userdata('type')!="0" && $this->session->userdata('type')!="1" && $this->session->userdata('type')!="7" && $this->session->userdata('type')!="11" && $this->session->userdata('type')!="15")
		{
			$this->db->where("TRIM(LEADING '0' FROM bidang) = ".ltrim($this->session->userdata('code_bk_hkk'), '0'),null);
			$this->db->where('status_faip>=5');
		}
		else if($this->session->userdata('type')=="7")
		{
			//$this->db->where("TRIM(LEADING '0' FROM bidang) = ".ltrim($this->session->userdata('code_bk_hkk'), '0'),null);
			
			$this->db->where('status_faip>=6');
			
			$this->db->group_start()->where('majelis1', $this->session->userdata('admin_id'))->or_where('majelis2', $this->session->userdata('admin_id'))->or_where('majelis3', $this->session->userdata('admin_id'))->group_end();
		}
		else if($this->session->userdata('type')=="11" || $this->session->userdata('type')=="15")
		{
			$this->db->where("TRIM(LEADING '0' FROM bidang) = ".ltrim($this->session->userdata('code_bk_hkk'), '0'),null);
			//$this->db->where('status_faip>=6');
		}
		
		foreach($search_parameters as $v=>$w){
			if($v == 'filter_cab'){
				$this->db->where(('LPAD(members.code_wilayah, 4, "0") like "'.$w.'%"'),null);
			}
			else if($v == 'filter_bk'){
				$this->db->where(('LPAD(user_faip.bidang, 2, "0") like "'.$w.'%"'),null);
			}
			else if($v == 'filter_hkk'){
				$this->db->where(('LPAD(members.code_hkk, 2, "0") like "'.$w.'%"'),null);
			}
			else if($v == 'user_faip.status_faip'){
				$this->db->where('status_faip',$w);
			}
			else if($v == 'is_manual'){
				$this->db->where('is_manual',$w);
			}			
			else
				$this->db->$where($v,$w);
		}
		//$this->db->$where($search_parameters);
		//$this->db->order_by("wkt_pernyataan", "d"); 
		//$this->db->order_by("user_profiles.id", "DESC"); 
		if($this->session->userdata('type')=="0" || $this->session->userdata('type')=="1")
			$this->db->order_by("(select createddate from log_status_faip where faip_id=user_faip.id and new_status=11 order by createddate desc limit 1)", "DESC"); 
		else
		{
			$this->db->order_by("(select createddate from log_status_faip where faip_id=user_faip.id and old_status=0 and new_status=1 order by createddate asc limit 1)", "desc"); 
			$this->db->order_by("user_faip.id", "desc"); 
		}
		$this->db->limit($per_page, $page);
        $Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->result();
        } else {
            $return = 0;
        }
        $Q->free_result();
		//echo $this->db->last_query(); exit;
        return $return;
    }
	
	public function search_all_pkb($per_page, $page, $search_parameters, $wild_card='') {
		
		$where = ($wild_card=='yes')?'where':'like';
		 $this->db->select('(select CONCAT(COALESCE(firstname,""),COALESCE(lastname,"")) from user_profiles where user_id=user_pkb.majelis1) as asesor1,
		(select CONCAT(COALESCE(firstname,""),COALESCE(lastname,"")) from user_profiles where user_id=user_pkb.majelis2) as asesor2,
		(select CONCAT(COALESCE(firstname,""),COALESCE(lastname,"")) from user_profiles where user_id=user_pkb.majelis3) as asesor3,
		user_pkb.id as ID,
		(select name from m_cab where TRIM(LEADING "0" FROM value)=TRIM(LEADING "0" FROM members.code_wilayah)) as wil_name,
		(select name from m_bk where TRIM(LEADING "0" FROM value)=TRIM(LEADING "0" FROM members.code_bk_hkk)) as bk_name,
		(select name from m_bk where TRIM(LEADING "0" FROM value)=TRIM(LEADING "0" FROM user_pkb.bidang)) as bk_pkb,
		members.code_bk_hkk as bk,members.code_wilayah as wil,
(select name from m_pkb_status where value=v_status_pkb_terakhir.new_status) as status_name, 
v_status_pkb_terakhir.new_status as status_pkb,user_pkb.id,user_pkb.user_id, user_pkb.sip_id,user_pkb.remarks,user_pkb.jenis_pkb,user_pkb.no_kta,user_pkb.no_sip,
user_pkb.masa_sip,user_pkb.no_stri, user_pkb.masa_stri, user_pkb.nama,user_pkb.periodstart,user_pkb.periodend,
user_pkb.subkejuruan,user_pkb.bidang, user_pkb.bidang_tujuan, user_pkb.faip_type, user_pkb.certificate_type,
user_pkb.pernyataan,user_pkb.wkt_pernyataan,user_pkb.wajib1_score,user_pkb.wajib2_score,user_pkb.wajib3_score,
user_pkb.wajib4_score,user_pkb.wajib5_score,user_pkb.total_score,user_pkb.keputusan,user_pkb.tempat,user_pkb.tgl_keputusan,
user_pkb.majelis1,user_pkb.majelis2,user_pkb.majelis3,user_pkb.remarks,user_pkb.status,user_pkb.need_revisi,
user_pkb.revisi_note,user_pkb.interview_date,user_pkb.interview_start_hour,user_pkb.interview_end_hour,
user_pkb.interview_loc,user_pkb.hasil_keputusan,user_pkb.hasil_tipe,user_pkb.upgrade,user_pkb.upgrade_date,
user_pkb.is_upgrade_paid,user_pkb.createddate,user_pkb.createdby,user_pkb.modifieddate,user_pkb.modifiedby,
		(select GROUP_CONCAT(createdby) from asesor_pkb where status=2 and pkb_id=user_pkb.id) as score_id,
		(select GROUP_CONCAT(wajib1_score) from asesor_pkb where status=2 and pkb_id=user_pkb.id) as wajib1_score_,
		(select GROUP_CONCAT(wajib2_score) from asesor_pkb where status=2 and pkb_id=user_pkb.id) as wajib2_score_,
		(select GROUP_CONCAT(wajib3_score) from asesor_pkb where status=2 and pkb_id=user_pkb.id) as wajib3_score_,
		(select GROUP_CONCAT(wajib4_score) from asesor_pkb where status=2 and pkb_id=user_pkb.id) as wajib4_score_,
		(select GROUP_CONCAT(wajib5_score) from asesor_pkb where status=2 and pkb_id=user_pkb.id) as wajib5_score_,
		(select GROUP_CONCAT(total_score) from asesor_pkb where status=2 and pkb_id=user_pkb.id) as score,
		(select GROUP_CONCAT(keputusan) from asesor_pkb where status=2 and pkb_id=user_pkb.id) as keputusan,
		(select lic_num from user_cert where status=2 and cert_url=user_pkb.id order by id desc limit 1) as lic_num
		');
        $this->db->from('user_pkb');
		$this->db->join('v_status_pkb_terakhir', 'user_pkb.id = v_status_pkb_terakhir.pkb_id', 'left');
		$this->db->join('user_profiles', 'user_profiles.user_id = user_pkb.user_id', 'left');
		$this->db->join('members', 'members.person_id = user_pkb.user_id', 'left');
		$this->db->join('m_pkb_status', 'm_pkb_status.value = user_pkb.status_pkb', 'left');
		$this->db->where('user_pkb.status', 1);
		$this->db->where('status_pkb<>0');
		
		if($this->session->userdata('type')!="0" && $this->session->userdata('type')!="1" && $this->session->userdata('type')!="7" && $this->session->userdata('type')!="11" && $this->session->userdata('type')!="15" && $this->session->userdata('type')!="16")
		{
			$this->db->where("TRIM(LEADING '0' FROM bidang) = ".ltrim($this->session->userdata('code_bk_hkk'), '0'),null);
			$this->db->where('status_pkb>=5');
		}
		else if($this->session->userdata('type')=="7")
		{
			//$this->db->where("TRIM(LEADING '0' FROM bidang) = ".ltrim($this->session->userdata('code_bk_hkk'), '0'),null);
			
			$this->db->where('status_pkb>=6');
			
			$this->db->group_start()->where('majelis1', $this->session->userdata('admin_id'))->or_where('majelis2', $this->session->userdata('admin_id'))->or_where('majelis3', $this->session->userdata('admin_id'))->group_end();
		}
		else if($this->session->userdata('type')=="11" || $this->session->userdata('type')=="15")
		{
			$this->db->where("TRIM(LEADING '0' FROM bidang) = ".ltrim($this->session->userdata('code_bk_hkk'), '0'),null);
			//$this->db->where('status_pkb>=6');
		}
		
		foreach($search_parameters as $v=>$w){
			if($v == 'filter_cab'){
				$this->db->where(('LPAD(members.code_wilayah, 4, "0") like "'.$w.'%"'),null);
			}
			else if($v == 'filter_bk'){
				$this->db->where(('LPAD(members.code_bk_hkk, 2, "0") like "'.$w.'%"'),null);
			}
			else
				$this->db->$where($v,$w);
		}
		//$this->db->$where($search_parameters);
		
		//$this->db->order_by("user_profiles.id", "DESC"); 
		$this->db->limit($per_page, $page);
        $Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->result();
        } else {
            $return = 0;
        }
        $Q->free_result();
		//echo $this->db->last_query(); exit;
        return $return;
    }
	
	public function search_all_majelis($per_page, $page, $search_parameters, $wild_card='') {
		
		$where = ($wild_card=='yes')?'where':'like';
		 $this->db->select('*,m_title.desc as title');//,(select name from m_bk where value= members_ip.code_bk_hkk) as kejuruan user_profiles.firstname
		$this->db->join('m_title', 'm_title.id = admin.type', 'left');
		$this->db->from('admin');
		//$this->db->join('user_profiles', 'user_profiles.user_id = members_certificate.person_id', 'left');
		//$this->db->join('members', 'user_profiles.user_id = members.person_id', 'left');
		//$this->db->where('skip_id <> ""', null,false);
		$this->db->where('type',7);		
		if($this->session->userdata('type')!="0" && $this->session->userdata('type')!="1")
			$this->db->where("TRIM(LEADING '0' FROM code_bk_hkk) = ".ltrim($this->session->userdata('code_bk_hkk'), '0'),null);
		
		$this->db->$where($search_parameters);
		//$this->db->order_by("user_profiles.id", "DESC"); 
		$this->db->limit($per_page, $page);
        $Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->result();
        } else {
            $return = 0;
        }
        $Q->free_result();
		//echo $this->db->last_query(); exit;
        return $return;
    }
	
	public function search_all_bp($per_page, $page, $search_parameters, $wild_card='') {
		
		$where = ($wild_card=='yes')?'where':'like';
		 $this->db->select('*');//,(select name from m_bk where value= members_ip.code_bk_hkk) as kejuruan user_profiles.firstname
		//$this->db->join('m_title', 'm_title.id = admin.type', 'left');
		$this->db->from('m_bakuan_penilaian');
		//$this->db->join('user_profiles', 'user_profiles.user_id = members_certificate.person_id', 'left');
		//$this->db->join('members', 'user_profiles.user_id = members.person_id', 'left');
		//$this->db->where('skip_id <> ""', null,false);
		//$this->db->where('type>2');
		
		$this->db->$where($search_parameters);
		//$this->db->order_by("user_profiles.id", "DESC"); 
		$this->db->limit($per_page, $page);
        $Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->result();
        } else {
            $return = 0;
        }
        $Q->free_result();
		//echo $this->db->last_query(); exit;
        return $return;
    }
	
	public function search_record_count_non_kta($table_name, $search_parameters,$bk,$wil,$is_kolektif) {
		//return $this->db->count_all($table_name);
		$this->db->like($search_parameters);
		
		
		$this->db->select('users.id');
		$this->db->from('user_profiles');
		$this->db->join('users', 'user_profiles.user_id = users.id', 'left');
		$this->db->where('username = ""', null,false);
		//$this->db->where('user_id not in (select user_id from user_transfer where pay_type=5)', null,false);
		$this->db->where('user_id in (select user_id from user_transfer where pay_type=1)', null,false);
		
		
		if($is_kolektif)
		{
			$str = '';
			$str2 = '';
			$i = 0;
			$i2 = 0;
				
			if(is_array($bk)){
				foreach($bk as $val)
				{
					if($i==0) $str .= " LPAD( code_bk_hkk, 2, '0' ) like '".$val."%'";
					else $str .= " or LPAD( code_bk_hkk, 2, '0' ) like '".$val."%'";
					$i++;
				}
			}
			else if($bk!=""){
				if($i==0) $str .= " LPAD( code_bk_hkk, 2, '0' ) like '".$bk."%'";
				else $str .= " or LPAD( code_bk_hkk, 2, '0' ) like '".$bk."%'";
				$i++;		
			}		
			if(is_array($wil)){
				foreach($wil as $val)
				{
					if($i2==0) $str2 .= " LPAD( code_wilayah, 4, '0' ) like '".$val."%'";
					else $str2 .= " or LPAD( code_wilayah, 4, '0' ) like '".$val."%'";
					$i2++;
				}
			}
			else if($wil!=""){
				if($i2==0) $str2 .= " LPAD( code_wilayah, 4, '0' ) like '".$wil."%'";
				else $str2 .= " or LPAD( code_wilayah, 4, '0' ) like '".$wil."%'";
				$i2++;		
			}
			
			
			$this->db->group_start();
			//$this->db->where('FIND_IN_SET('. $this->session->userdata('admin_id').',kolektif_ids) <>',"0");
			if($str!='') $this->db->where('user_profiles.user_id in (select person_id from members where '.$str.')',null);
			if($str2!='') $this->db->or_where('user_profiles.user_id in (select person_id from members where '.$str2.')',null);
			$this->db->group_end();
		}
		
		$Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->num_rows();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
		
		
		//$this->db->from($table_name);
		//$this->db->join('users', 'user_profiles.user_id = users.id', 'left');
		//return $this->db->count_all_results();
		
		//exit;
    }
	
	public function search_record_count_her_kta($table_name, $search_parameters,$bk,$wil,$is_kolektif) {
		//return $this->db->count_all($table_name);
		
		
		//$this->db->like($search_parameters);
		foreach($search_parameters as $v=>$w){
			if($v == 'filter_cab'){
				$this->db->where(('user_profiles.user_id in (select person_id from members where TRIM(LEADING "0" FROM code_wilayah)='.ltrim($w, '0').')'),null);
			}			
			else if($v == 'filter_bk'){
				$this->db->where(('user_profiles.user_id in (select person_id from members where TRIM(LEADING "0" FROM code_bk_hkk)='.ltrim($w, '0').')'),null);
			}
			else
				$this->db->like($v,$w);
		}
		
		if($is_kolektif)
		{
			$str = '';
			$str2 = '';
			$i = 0;
			$i2 = 0;
				
			if(is_array($bk)){
				foreach($bk as $val)
				{
					if($i==0) $str .= " LPAD( code_bk_hkk, 2, '0' ) like '".$val."%'";
					else $str .= " or LPAD( code_bk_hkk, 2, '0' ) like '".$val."%'";
					$i++;
				}
			}
			else if($bk!=""){
				if($i==0) $str .= " LPAD( code_bk_hkk, 2, '0' ) like '".$bk."%'";
				else $str .= " or LPAD( code_bk_hkk, 2, '0' ) like '".$bk."%'";
				$i++;		
			}		
			if(is_array($wil)){
				foreach($wil as $val)
				{
					if($i2==0) $str2 .= " LPAD( code_wilayah, 4, '0' ) like '".$val."%'";
					else $str2 .= " or LPAD( code_wilayah, 4, '0' ) like '".$val."%'";
					$i2++;
				}
			}
			else if($wil!=""){
				if($i2==0) $str2 .= " LPAD( code_wilayah, 4, '0' ) like '".$wil."%'";
				else $str2 .= " or LPAD( code_wilayah, 4, '0' ) like '".$wil."%'";
				$i2++;		
			}
			
			
			$this->db->group_start();
			//$this->db->where('FIND_IN_SET('. $this->session->userdata('admin_id').',kolektif_ids) <>',"0");
			if($str!='') $this->db->where('user_profiles.user_id in (select person_id from members where '.$str.')',null);
			if($str2!='') $this->db->or_where('user_profiles.user_id in (select person_id from members where '.$str2.')',null);
			$this->db->group_end();
		}
		
		$this->db->select('users.id');
		$this->db->from('user_profiles');
		$this->db->join('users', 'user_profiles.user_id = users.id', 'left');
		$this->db->where('users.id in (select user_id from user_transfer where pay_type =2)', null,false);
		
		$Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->num_rows();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
		
		
		//$this->db->from($table_name);
		//$this->db->join('users', 'user_profiles.user_id = users.id', 'left');
		//return $this->db->count_all_results();
		
		//exit;
    }
	
	public function search_record_count_her_kta_2($table_name, $search_parameters) {
		//return $this->db->count_all($table_name);
		$this->db->like($search_parameters);
		
		
		$this->db->select('users.id');
		
		/*$this->db->from('user_profiles');
		$this->db->join('users', 'user_profiles.user_id = users.id', 'left');
		$this->db->where('users.id in (select user_id from user_transfer where pay_type =2)', null,false);
		*/
		
		$this->db->from('user_transfer');
		$this->db->join('users', 'user_transfer.user_id = users.id', 'left');
		$this->db->join('user_profiles', 'user_profiles.user_id = user_transfer.user_id', 'left');
		$this->db->where('pay_type =2', null,false);
		
		$Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->num_rows();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
		
		
		//$this->db->from($table_name);
		//$this->db->join('users', 'user_profiles.user_id = users.id', 'left');
		//return $this->db->count_all_results();
		
		//exit;
    }
	
	public function search_record_count_stri_member($table_name, $search_parameters) {
		//return $this->db->count_all($table_name);
		$where = 'like';
		foreach($search_parameters as $v=>$w){
			if($v == 'filter_cab'){
				$this->db->where(('LPAD(members.code_wilayah, 4, "0") like "'.$w.'%"'),null);
			}
			else if($v == 'filter_bk'){
				$this->db->where(('LPAD(members.code_bk_hkk, 2, "0") like "'.$w.'%"'),null);
			}
			else if($v == 'filter_hkk'){
				$this->db->where(('LPAD(members.code_hkk, 2, "0") like "'.$w.'%"'),null);
			}
			else if($v == 'filter_type'){
				if($w=='1') $this->db->where(('user_transfer.rel_id <> 0'),null);
				else if($w=='2') $this->db->where(('user_transfer.rel_id = 0'),null);
			}
			else if($v == 'filter_status'){
				/*if($w=='1') $this->db->where(('user_transfer.status = 0'),null);
				else if($w=='2') $this->db->where(('user_transfer.status = 1 and (
				CASE WHEN user_transfer.rel_id=0 THEN user_transfer.vnv_status = 0 WHEN user_transfer.rel_id<>0 THEN (select count(id) from log_stri where user_id = user_transfer.user_id and id_pay = user_transfer.id)=0 ELSE 1=1 END)'),null);
				else if($w=='3') $this->db->where(('user_transfer.status = 2'),null);
				else if($w=='4') $this->db->where(('user_transfer.status = 1 and user_transfer.vnv_status = 1 and (select count(person_id) from members where person_id = user_transfer.user_id)=0'),null);*/
				
				if($w=='4') $this->db->where(('user_transfer.vnv_status = 0'),null);
				
				else if($w=='5') $this->db->where(('user_transfer.vnv_status = 2'),null);
				
				else if($w=='6') $this->db->where(('user_transfer.vnv_status = 1'),null);
				//else if($w=='7') $this->db->where(('(select count(id) from log_stri where user_id = user_transfer.user_id and id_pay = user_transfer.id)>=1'),null);
			}	
			else if($v == 'stri_period'){
				$this->db->where(($w),null);
			}
			else
				$this->db->$where($v,$w);
		}
		//$this->db->like($search_parameters);
		
		
		$this->db->select('users.id');
		$this->db->from('user_profiles');
		$this->db->join('users', 'user_profiles.user_id = users.id', 'left');
		$this->db->join('user_transfer', 'user_profiles.user_id = user_transfer.user_id', 'left');
		$this->db->join('members', 'user_profiles.user_id = members.person_id', 'left');
		
		//$this->db->where('username =""', null,false);
		
		//$this->db->where('users.id in (select user_id from user_transfer where pay_type =5)', null,false);
		$this->db->where('pay_type', "5");
		$this->db->where('user_transfer.id not in (select id_pay from log_stri)', null,false);
		$this->db->where('username <> ""', null,false);
		//$this->db->where('user_transfer.vnv_status in (0,1)', null,false);
		
		if($this->session->userdata('type')=="13")
		{
			$this->db->where('rel_id',"0");
		}
		/*else if($this->session->userdata('type')=="12")
		{
			$this->db->where('rel_id<>0',null);
		}*/
		
		$Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->num_rows();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
		
		
		//$this->db->from($table_name);
		//$this->db->join('users', 'user_profiles.user_id = users.id', 'left');
		//return $this->db->count_all_results();
		
		//exit;
    }
	
	public function search_record_count_finance($table_name, $search_parameters) {
		//return $this->db->count_all($table_name);
		$where = 'like';
		foreach($search_parameters as $v=>$w){
			if($v == 'filter_type'){
				if($w=='1') $this->db->where(('users.id in (select user_id from user_transfer where pay_type=0 or pay_type=1)'),null);
				else if($w=='2') $this->db->where(('users.id in (select user_id from user_transfer where pay_type=2)'),null);
				else if($w=='3') $this->db->where(('users.id in (select user_id from user_transfer where pay_type=3 or pay_type=4)'),null);
				else if($w=='4') $this->db->where(('users.id in (select user_id from user_transfer where pay_type=5)'),null);
			}
			else
				$this->db->$where($v,$w);
		}
		//$this->db->like($search_parameters);
		
		
		$this->db->select('users.id');
		$this->db->from('user_profiles');
		$this->db->join('users', 'user_profiles.user_id = users.id', 'left');
		//$this->db->where('username = ""', null,false);
		$this->db->where('users.id in (select user_id from user_transfer)', null,false);
		
		$Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->num_rows();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
		
		
		//$this->db->from($table_name);
		//$this->db->join('users', 'user_profiles.user_id = users.id', 'left');
		//return $this->db->count_all_results();
		
		//exit;
    }
	
	public function search_record_count_finance_2($table_name, $search_parameters) {
		//return $this->db->count_all($table_name);
		$where = 'like';
		foreach($search_parameters as $v=>$w){
			if($v == 'filter_type'){
				if($w=='1') $this->db->where(('(pay_type=0 or pay_type=1)'),null);
				else if($w=='2') $this->db->where(('(pay_type=2)'),null);
				else if($w=='3') $this->db->where(('(pay_type=3 or pay_type=4)'),null);
				else if($w=='4') $this->db->where(('(pay_type=5)'),null);
				else if($w=='5') $this->db->where(('(pay_type=6 or pay_type=7)'),null);
			}
			else if($v == 'user_transfer.status'){
				if($w=='0') $this->db->having('paystatus',  0);
				else if($w=='1') $this->db->having('paystatus',  1);
				else if($w=='2') $this->db->having('paystatus',  2);
			}
			else
				$this->db->$where($v,$w);
		}
		//$this->db->like($search_parameters);
		
		
		$this->db->select('users.id,(select x.status from user_transfer x where user_profiles.user_id=x.user_id and user_transfer.pay_type=x.pay_type order by x.createddate desc limit 1) as paystatus');
		$this->db->from('user_transfer');
		$this->db->join('users', 'user_transfer.user_id = users.id', 'left');
		$this->db->join('user_profiles', 'user_profiles.user_id = user_transfer.user_id', 'left');
		$this->db->join('members', 'user_transfer.user_id = members.person_id', 'left');
		$this->db->where('(CASE WHEN pay_type=5 THEN vnv_status=1 ELSE 1=1 END)', null,false);//and rel_id=0 
		if($this->session->userdata('admin_id')=="673" ){
			$this->db->where('pay_type in (3,4)', null,false);
		}
		else if($this->session->userdata('admin_id')=="675" ){
			$this->db->where('pay_type in (1,2)', null,false);
		}
		$this->db->group_by('user_transfer.user_id,user_transfer.pay_type');
		
		$Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->num_rows();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
		
		
		//$this->db->from($table_name);
		//$this->db->join('users', 'user_profiles.user_id = users.id', 'left');
		//return $this->db->count_all_results();
		
		//exit;
    }
	
	public function search_record_count_stri($table_name, $search_parameters,$bk,$wil,$is_kolektif) {
		$where = 'like';
		//return $this->db->count_all($table_name);
		foreach($search_parameters as $v=>$w){
			if($v == 'filter_cab'){
				$this->db->where(('LPAD(stri_code_wilayah, 4, "0") like "'.$w.'%"'),null);
			}
			else if($v == 'filter_bk'){
				$this->db->where(('LPAD(stri_code_bk_hkk, 2, "0") like "'.$w.'%"'),null);
			}			
			else if($v == 'filter_hkk'){
				$this->db->where(('LPAD(members.code_hkk, 2, "0") like "'.$w.'%"'),null);
			}
			else if($v == 'filter_type'){
				$this->db->where('certificate_type',$w);
			}
			else if($v == 'filter_status'){
				if($w=='1') $this->db->where(('stri_thru_date >= now()'),null);
				else if($w=='0') $this->db->where(('stri_thru_date < now()'),null);
			}
			else if($v == 'nomor'){
				$this->db->where('TRIM(LEADING "0" FROM members_certificate.stri_id) = "'.ltrim($w, '0').'"',null);
			}
			else if($v == 'stri_period'){
				$this->db->where(($w),null);
			}
			else
				$this->db->$where($v,$w);
		}
		//$this->db->like($search_parameters);
		
		if($is_kolektif)
		{
			$str = '';
			$str2 = '';
			$i = 0;
			$i2 = 0;
				
			if(is_array($bk)){
				foreach($bk as $val)
				{
					if($i==0) $str .= " LPAD( stri_code_bk_hkk, 2, '0' ) like '".$val."%'";
					else $str .= " or LPAD( stri_code_bk_hkk, 2, '0' ) like '".$val."%'";
					$i++;
				}
			}
			else if($bk!=""){
				if($i==0) $str .= " LPAD( stri_code_bk_hkk, 2, '0' ) like '".$bk."%'";
				else $str .= " or LPAD( stri_code_bk_hkk, 2, '0' ) like '".$bk."%'";
				$i++;		
			}		
			if(is_array($wil)){
				foreach($wil as $val)
				{
					if($i2==0) $str2 .= " LPAD( code_wilayah, 4, '0' ) like '".$val."%'";
					else $str2 .= " or LPAD( code_wilayah, 4, '0' ) like '".$val."%'";
					$i2++;
				}
			}
			else if($wil!=""){
				if($i2==0) $str2 .= " LPAD( code_wilayah, 4, '0' ) like '".$wil."%'";
				else $str2 .= " or LPAD( code_wilayah, 4, '0' ) like '".$wil."%'";
				$i2++;		
			}
			
			
			$this->db->group_start();
			//$this->db->where('FIND_IN_SET('. $this->session->userdata('admin_id').',kolektif_ids) <>',"0");
			if($str!='') $this->db->where($str,null);
			if($str2!='') $this->db->where('members_certificate.person_id in (select person_id from members where '.$str2.')',null);
			$this->db->group_end();
		}
		
		$this->db->select('members_certificate.id');
		$this->db->from('members_certificate');//user_profiles.firstname
		$this->db->join('user_profiles', 'user_profiles.user_id = members_certificate.person_id');
		$this->db->join('members', 'members_certificate.person_id = members.person_id');
		$this->db->join('log_stri b', 'b.id = (
			SELECT
			  max(fa.id) a_id
			FROM log_stri fa 
			WHERE fa.stri_id = members_certificate.stri_id and fa.user_id=members_certificate.person_id
		  )', 'left',false);
		$this->db->join('user_transfer e', 'e.id = b.id_pay', 'left');
		
		$this->db->where('members_certificate.status', "1");
		
		$Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->num_rows();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
		
		
		//$this->db->from($table_name);
		//$this->db->join('users', 'user_profiles.user_id = users.id', 'left');
		//return $this->db->count_all_results();
		
		//exit;
    }
	
	/**
	 * ER: FIXME: Remove this function
	 * @deprecated table `skip3` is not exist
	 */
	public function search_record_count_skip($table_name, $search_parameters) {
		//return $this->db->count_all($table_name);
		$this->db->like($search_parameters);
		
		/*
		$this->db->select('members_certificate.id');
		$this->db->from('members_certificate');//user_profiles.firstname
		$this->db->join('user_profiles', 'user_profiles.user_id = members_certificate.person_id', 'left');
		$this->db->where('skip_id <> ""', null,false);
		*/
		$this->db->select('id');
		$this->db->from('skip3 a');
		$this->db->where('sk_end > now()', null,false);
		$this->db->where('noip<>""', null,false);
		$this->db->group_by('kta,sk_from,sertid');
		$this->db->having('sertid=(select max(sertid) from skip3 where kta=a.kta)');
		$this->db->having('sk_from=(select max(sk_from) from skip3 where kta=a.kta)');
		
		
		$Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->num_rows();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
		
		
		//$this->db->from($table_name);
		//$this->db->join('users', 'user_profiles.user_id = users.id', 'left');
		//return $this->db->count_all_results();
		
		//exit;
    }
	
	public function search_record_count_pi($table_name, $search_parameters) {
		//return $this->db->count_all($table_name);
		$this->db->like($search_parameters);
		
		
		$this->db->select('members_certificate.id');
		$this->db->from('members_certificate');//user_profiles.firstname
		$this->db->join('user_profiles', 'user_profiles.user_id = members_certificate.person_id', 'left');
		$this->db->where('skip_id <> ""', null,false);
		
		$Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->num_rows();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
		
		
		//$this->db->from($table_name);
		//$this->db->join('users', 'user_profiles.user_id = users.id', 'left');
		//return $this->db->count_all_results();
		
		//exit;
    }
	
	public function search_record_count_faip($table_name, $search_parameters) {
		//return $this->db->count_all($table_name);
		$where = 'like';
		foreach($search_parameters as $v=>$w){
			if($v == 'filter_cab'){
				$this->db->where(('LPAD(members.code_wilayah, 4, "0") like "'.$w.'%"'),null);
			}
			else if($v == 'filter_bk'){
				$this->db->where(('LPAD(user_faip.bidang, 2, "0") like "'.$w.'%"'),null);
			}	
			else if($v == 'filter_hkk'){
				$this->db->where(('LPAD(members.code_hkk, 2, "0") like "'.$w.'%"'),null);
			}			
			else if($v == 'user_faip.status_faip'){
				$this->db->where('status_faip',$w);
			} 
			else if($v == 'filter_kolektif'){
				$this->db->where('user_profiles.kolektif_name_id',$w);
			}			
			elseif ($v == 'is_manual'){
				$this->db->where('is_manual',$w);
			} 
			else
				$this->db->$where($v,$w);
		}
		//$this->db->like($search_parameters);
		
		
		$this->db->select('user_faip.id');
		$this->db->from('user_faip');
		$this->db->join('user_profiles', 'user_profiles.user_id = user_faip.user_id', 'left');
		$this->db->join('members', 'members.person_id = user_faip.user_id', 'left');
		$this->db->where('user_faip.status', 1);
		$this->db->where('user_faip.status_faip<>0');
		
		if($this->session->userdata('type')!="0" && $this->session->userdata('type')!="1" && $this->session->userdata('type')!="7" && $this->session->userdata('type')!="11" && $this->session->userdata('type')!="15")
		{
			$this->db->where("TRIM(LEADING '0' FROM bidang) = ".ltrim($this->session->userdata('code_bk_hkk'), '0'),null);
			$this->db->where('status_faip>=5');
		}
		else if($this->session->userdata('type')=="7")
		{
			//$this->db->where("TRIM(LEADING '0' FROM bidang) = ".ltrim($this->session->userdata('code_bk_hkk'), '0'),null);
			
			$this->db->where('status_faip>=6');
			
			$this->db->group_start()->where('majelis1', $this->session->userdata('admin_id'))->or_where('majelis2', $this->session->userdata('admin_id'))->or_where('majelis3', $this->session->userdata('admin_id'))->group_end();
		}
		else if($this->session->userdata('type')=="11" || $this->session->userdata('type')=="15")
		{
			$this->db->where("TRIM(LEADING '0' FROM bidang) = ".ltrim($this->session->userdata('code_bk_hkk'), '0'),null);
			//$this->db->where('status_faip>=6');
		}
		else if(isAdminBKWilayahKolektif() || isAdminKolektifRO())
		{
			// User BK
			if ( ! empty($this->session->userdata('code_bk_hkk')) ) {
				$this->db->where("TRIM(LEADING '0' FROM bidang) = ".ltrim($this->session->userdata('code_bk_hkk'), '0'),null);
			}
			else if (isAdminKolektifRO() || isAdminHasKolektifId()) {
				$this->db
					->group_start()
						->where("FIND_IN_SET(".$this->session->userdata('admin_id').",user_profiles.kolektif_ids) = 1",null)
						->or_where("user_profiles.kolektif_name_id IN (select kolektif_id from admin_kolektif_map where admin_id = ".$this->session->userdata('admin_id').")",null)
                        ->or_where("user_profiles.kolektif_name_id",$this->session->userdata('kode_kolektif'))
					->group_end();
			}
			//$this->db->where('status_faip>=6');
		}
		
		$Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->num_rows();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
		
		
		//$this->db->from($table_name);
		//$this->db->join('users', 'user_profiles.user_id = users.id', 'left');
		//return $this->db->count_all_results();
		
		//exit;
    }
	
	public function search_record_count_pkb($table_name, $search_parameters) {
		//return $this->db->count_all($table_name);
		$where = 'like';
		foreach($search_parameters as $v=>$w){
			if($v == 'filter_cab'){
				$this->db->where(('LPAD(members.code_wilayah, 4, "0") like "'.$w.'%"'),null);
			}
			else if($v == 'filter_bk'){
				$this->db->where(('LPAD(members.code_bk_hkk, 2, "0") like "'.$w.'%"'),null);
			}
			else
				$this->db->$where($v,$w);
		}
		//$this->db->like($search_parameters);
		
		
		$this->db->select('*');
		$this->db->from('user_pkb');
		$this->db->join('members', 'members.person_id = user_pkb.user_id', 'left');
		$this->db->where('user_pkb.status', 1);
		$this->db->where('user_pkb.status_pkb<>0');
		
		if($this->session->userdata('type')!="0" && $this->session->userdata('type')!="1" && $this->session->userdata('type')!="7" && $this->session->userdata('type')!="11" && $this->session->userdata('type')!="15" && $this->session->userdata('type')!="16")
		{
			$this->db->where("TRIM(LEADING '0' FROM bidang) = ".ltrim($this->session->userdata('code_bk_hkk'), '0'),null);
			$this->db->where('status_pkb>=5');
		}
		else if($this->session->userdata('type')=="7")
		{
			//$this->db->where("TRIM(LEADING '0' FROM bidang) = ".ltrim($this->session->userdata('code_bk_hkk'), '0'),null);
			
			$this->db->where('status_pkb>=6');
			
			$this->db->group_start()->where('majelis1', $this->session->userdata('admin_id'))->or_where('majelis2', $this->session->userdata('admin_id'))->or_where('majelis3', $this->session->userdata('admin_id'))->group_end();
		}
		else if($this->session->userdata('type')=="11" || $this->session->userdata('type')=="15")
		{
			$this->db->where("TRIM(LEADING '0' FROM bidang) = ".ltrim($this->session->userdata('code_bk_hkk'), '0'),null);
			//$this->db->where('status_pkb>=6');
		}
		
		$Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->num_rows();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
		
		
		//$this->db->from($table_name);
		//$this->db->join('users', 'user_profiles.user_id = users.id', 'left');
		//return $this->db->count_all_results();
		
		//exit;
    }
	
	public function search_record_count_majelis($table_name, $search_parameters) {
		//return $this->db->count_all($table_name);
		$this->db->like($search_parameters);
		
		
		 $this->db->select('*,m_title.desc as title');//,(select name from m_bk where value= members_ip.code_bk_hkk) as kejuruan user_profiles.firstname
		$this->db->join('m_title', 'm_title.id = admin.type', 'left');
		$this->db->from('admin');
		//$this->db->join('user_profiles', 'user_profiles.user_id = members_certificate.person_id', 'left');
		//$this->db->where('skip_id <> ""', null,false);
		$this->db->where('type',7);		
		if($this->session->userdata('type')!="0" && $this->session->userdata('type')!="1")
			$this->db->where("TRIM(LEADING '0' FROM code_bk_hkk) = ".ltrim($this->session->userdata('code_bk_hkk'), '0'),null);
		
		$Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->num_rows();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
		
		
		//$this->db->from($table_name);
		//$this->db->join('users', 'user_profiles.user_id = users.id', 'left');
		//return $this->db->count_all_results();
		
		//exit;
    }
	
//====== Specifically front end methods =======	
	public function get_all_active_members($per_page, $page) {
        $Q = $this->db->query("CALL get_all_active_members($page, $per_page)");
        if ($Q->num_rows() > 0) {
            $return = $Q->result();
        } else {
            $return = 0;
        }
		$Q->next_result();
        $Q->free_result();
        return $return;
    }	
	
	public function get_all_active_top_members($per_page, $page) {
        $Q = $this->db->query("CALL get_all_active_top_members($page, $per_page)");
        if ($Q->num_rows() > 0) {
            $return = $Q->result();
        } else {
            $return = 0;
        }
		$Q->next_result();
        $Q->free_result();
        return $return;
    }
	
	
	public function get_company_details_by_slug($slug) {
        $Q = $this->db->query('CALL get_company_by_slug("'.$slug.'")');
        if ($Q->num_rows() > 0) {
            $return = $Q->row();
        } else {
            $return = 0;
        }
		$Q->next_result();
        $Q->free_result();
        return $return;
    }	
	
	
	
	public function filter_pi($search, $limit, $start, $order_field, $order_ascdesc){
		$this->db->select('users.id as person_id,skip45.*,MAX(sk_from) as sk_from, MAX(sertid) as sertid, MAX(noip) as noip');
        $this->db->from('skip45');
		$this->db->join('users', 'users.username = skip45.kta', 'left');
		//$this->db->where('skip_id <> ""', null,false);
		$this->db->where('kta <> ""', null,false);
		$this->db->where('sertid <> ""', null,false);
		if($search!=''){
			$this->db->like('nama', $search);
			$this->db->or_like('kta', $search); 
		}
		$this->db->order_by($order_field, $order_ascdesc); 
		//$this->db->order_by("sk_end", "desc");
		//$this->db->order_by("sertid", "desc"); 
		//$this->db->order_by("code", "desc"); 
		
		$this->db->group_by('skip45.kta');
		
		
		$this->db->limit($limit, $start); 
		return $this->db->get()->result_array(); 
	}
	public function count_all_pi(){
		$this->db->select('users.id,kta');
		$this->db->from('skip45');
		$this->db->join('users', 'users.username = skip45.kta', 'left');
		//this->db->where('skip_id <> ""', null,false);
		$this->db->where('kta <> ""', null,false);
		$this->db->where('sertid <> ""', null,false);
		
		$this->db->group_by('skip45.kta');
		
		return $this->db->count_all_results(); // Untuk menghitung semua data siswa
	}
	public function count_filter_pi($search){
		$this->db->select('users.id as person_id,skip45.*');
        $this->db->from('skip45');
		$this->db->join('users', 'users.username = skip45.kta', 'left');
		//$this->db->where('skip_id <> ""', null,false);
		$this->db->where('kta <> ""', null,false);
		$this->db->where('sertid <> ""', null,false);
		if($search!=''){
			$this->db->like('nama', $search);
			$this->db->or_like('kta', $search); 
		}
		$this->db->group_by('skip45.kta');
		
		//$this->db->or_like('no_kta', $search); 
		return $this->db->get()->num_rows(); // Untuk menghitung jumlah data sesuai dengan filter pada textbox pencarian
	}
	public function get_pi_by_no_kta($no_kta,$certid){
		$this->db->select('users.id as person_id,skip45.*');//,MAX(sk_from) as sk_from, MAX(sertid) as sertid, MAX(noip) as noip
        $this->db->from('skip45');
		$this->db->join('users', 'users.username = skip45.kta', 'left');
		//$this->db->where('skip_id <> ""', null,false);
		$this->db->where('skip45.kta', ($no_kta));
		$this->db->where('skip45.id<>', $certid);
		$this->db->where('kta <> ""', null,false);
		$this->db->where('sertid <> ""', null,false);
		//$this->db->like('add_name', $search);
		//$this->db->or_like('user_profiles.lastname', $search); 
		//$this->db->or_like('no_kta', $search); 
		//$this->db->or_like('alamat', $search); 
		$this->db->order_by('sk_end', 'desc'); 
		$this->db->order_by('sertid', 'desc'); 
		$this->db->order_by('noip', 'desc'); 
		
		$this->db->group_by('sk_end');
		$this->db->group_by('sertid');
		
		//$this->db->group_by('no_kta');
		
		//$this->db->limit($limit, $start); 
		return $this->db->get()->result_array(); 
		//return $this->db->get('siswa')->result_array(); 
	}
	
	public function get_pi_by_id_old($id){
		$this->db->select('users.id as person_id,skip45.*');//,MAX(sk_from) as sk_from, MAX(sertid) as sertid, MAX(noip) as noip
        $this->db->from('skip45');
		$this->db->join('users', 'users.username = skip45.kta', 'left');
		$this->db->where('skip45.id', $id); 
		return $this->db->get()->result_array(); 
		//return $this->db->get('siswa')->result_array(); 
	}
	
	public function get_pi_by_id($id){
		$this->db->select('users.id as person_id,user_cert.*');//,MAX(sk_from) as sk_from, MAX(sertid) as sertid, MAX(noip) as noip
        $this->db->from('user_cert');
		$this->db->join('users', 'users.id = user_cert.user_id', 'left');
		$this->db->where('user_cert.id', $id); 
		$this->db->where('user_cert.status', 2); 
		return $this->db->get()->result_array(); 
		//return $this->db->get('siswa')->result_array(); 
	}

	public function get_stri_by_id_2($id){
		$this->db->select('*');
       		 $this->db->from('members_certificate');
		$this->db->where('id', $id); 
		$query = $this->db->get() ;
		return $query->row();
		
	}
    
	public function get_stri_by_id($id){
		$this->db->select('members_certificate.*');//,MAX(sk_from) as sk_from, MAX(sertid) as sertid, MAX(noip) as noip
        $this->db->from('members_certificate');
		//$this->db->join('users', 'users.id = members_certificate.person_id', 'left');
		//$this->db->join('members', 'users.id = members_certificate.person_id', 'left');
		$this->db->where('members_certificate.id', $id); 
		return $this->db->get()->row(); 
		//return $this->db->get('siswa')->result_array(); 
	}
	
	public function get_all_sip($id=null) {
        $this->db->select('user_cert.*');
        $this->db->from('user_cert');
		//$this->db->join('members_certificatex', "TRIM(LEADING '0' FROM user_cert.ip_kta) = TRIM(LEADING '0' FROM members_certificate.no_kta)");
		
		//$this->db->where('username =""', null,false);
		
		//$this->db->where('users.id in (select user_id from user_transfer where pay_type =5)', null,false);
		
		if($id!=null)
			$this->db->where('user_cert.user_id', $id);
		
		$this->db->where('user_cert.status', "2");
		$this->db->where('user_cert.user_id<>0', null);
		
		
		//$this->db->order_by("paystatus", "DESC"); 
		//$this->db->order_by("id_pay", "asc"); 
		$this->db->order_by("lic_num", "asc"); 
		//$this->db->limit($per_page, $page);
        $Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->result();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
    }
	
	public function get_all_member_sip($id=null,$page) {
        $this->db->select('members.no_kta,user_profiles.firstname,user_profiles.lastname,members.person_id');
        $this->db->from('members');
		$this->db->join('user_profiles', "members.person_id=user_profiles.user_id");
		
		//$this->db->where('username =""', null,false);
		
		//$this->db->where('users.id in (select user_id from user_transfer where pay_type =5)', null,false);
		if($id!=null)
			$this->db->like("members.no_kta", $id,'after');
		//$this->db->where('user_cert.user_id<>0', null);
		
		
		//$this->db->order_by("paystatus", "DESC"); 
		//$this->db->order_by("id_pay", "asc"); 
		$this->db->order_by("no_kta", "asc"); 
		$this->db->limit(200);
        $Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->result();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
    }
	
	
	public function count_all_ip(){
		$this->db->select('user_cert.user_id');
		$this->db->from('user_cert');
		$this->db->join('user_profiles', 'user_cert.user_id = user_profiles.user_id', 'left');
		$this->db->join('m_bk', 'TRIM(LEADING "0" FROM user_cert.ip_bk) = TRIM(LEADING "0" FROM m_bk.value)', 'left');
		
		$this->db->join('user_faip', 'user_faip.id = user_cert.cert_url', 'left');
		
		//this->db->where('skip_id <> ""', null,false);
		//$this->db->where('ip_kta <> ""', null,false);
		$this->db->where('user_cert.user_id <> 0', null,false);
		$this->db->where('user_cert.status', 2);
		if($this->session->userdata('code_bk_hkk')!='')
			$this->db->where("TRIM(LEADING '0' FROM value) = ".ltrim($this->session->userdata('code_bk_hkk'), '0'),null);
		//$this->db->group_by('user_cert.ip_kta');
		
		return $this->db->count_all_results(); // Untuk menghitung semua data siswa
	}
	public function filter_ip($search, $limit, $start, $order_field, $order_ascdesc,$column,$filter){
		$this->db->select('user_cert.*,firstname,lastname,m_bk.name as bk_name,
		user_faip.bap,
		wajib1_score,wajib2_score,wajib3_score,wajib4_score,pilihan_score,total_score,
		(select wajib1_score from asesor_faip where faip_id=user_cert.cert_url order by createddate desc limit 1) as wajib1_score_as,
		(select wajib2_score from asesor_faip where faip_id=user_cert.cert_url order by createddate desc limit 1) as wajib2_score_as,
		(select wajib3_score from asesor_faip where faip_id=user_cert.cert_url order by createddate desc limit 1) as wajib3_score_as,
		(select wajib4_score from asesor_faip where faip_id=user_cert.cert_url order by createddate desc limit 1) as wajib4_score_as,
		(select pilihan_score from asesor_faip where faip_id=user_cert.cert_url order by createddate desc limit 1) as pilihan_score_as,
		(select total_score from asesor_faip where faip_id=user_cert.cert_url order by createddate desc limit 1) as total_score_as
		');
		//$this->db->select('users.id as person_id,skip45.*,MAX(sk_from) as sk_from, MAX(sertid) as sertid, MAX(noip) as noip');
        $this->db->from('user_cert');
		$this->db->join('user_profiles', 'user_cert.user_id = user_profiles.user_id', 'left');
		$this->db->join('m_bk', 'TRIM(LEADING "0" FROM user_cert.ip_bk) = TRIM(LEADING "0" FROM m_bk.value)', 'left');
		
		$this->db->join('user_faip', 'user_faip.id = user_cert.cert_url', 'left');
		
		//$this->db->where('skip_id <> ""', null,false);
		//$this->db->where('ip_kta <> ""', null,false);
		$this->db->where('user_cert.user_id <> 0', null,false);
		$this->db->where('user_cert.status', 2);
		
		if($this->session->userdata('code_bk_hkk')!='')
			$this->db->where("TRIM(LEADING '0' FROM value) = ".ltrim($this->session->userdata('code_bk_hkk'), '0'),null);
		
		//$this->db->where('sertid <> ""', null,false);
		if($search!=''){
			$this->db->like('REPLACE(concat(lower(COALESCE(firstname,"")),lower(COALESCE(lastname,"")))," ","")', str_replace(' ','',strtolower($search)));
			$this->db->or_like("cert_title", $search); 
			$this->db->or_like("m_bk.name", $search); 
			$this->db->or_like("lic_num", $search); 
			$this->db->or_like("TRIM(LEADING '0' FROM ip_kta)", ltrim($search, '0')); 
			$this->db->or_like("startyear", $search); 
			$this->db->or_like("endyear", $search); 
		}
		
		foreach($column as $v){
			if($v['data']=="0" && $v['search']['value']!=''){
				$this->db->like('REPLACE(concat(lower(COALESCE(firstname,"")),lower(COALESCE(lastname,"")))," ","")', str_replace(' ','',strtolower($v['search']['value']))); 
			}
			if($v['data']=="1" && $v['search']['value']!=''){
				$this->db->like("cert_title", $v['search']['value']); 
			}	
			if($v['data']=="2" && $v['search']['value']!=''){
				$this->db->like("m_bk.name", $v['search']['value']); 
			}
			if($v['data']=="lic_num" && $v['search']['value']!=''){
				$this->db->like("lic_num", $v['search']['value']); 
			}
			if($v['data']=="4" && $v['search']['value']!=''){
				$this->db->like("TRIM(LEADING '0' FROM ip_kta)", ltrim($v['search']['value'], '0')); 
			}	
			if($v['data']=="startyear" && $v['search']['value']!=''){
				$this->db->like("startyear", $v['search']['value']); 
			}
			if($v['data']=="endyear" && $v['search']['value']!=''){
				$this->db->like("endyear", $v['search']['value']); 
			}
		}
		
		foreach($filter as $v=>$w){
			if($v == 'status'){
				if($w=='1') $this->db->where(('user_cert.endyear>=now()'),null);
				else if($w=='0') $this->db->where(('user_cert.endyear<now()'),null);
			}
			else if($v == 'cab'){
				$this->db->where(('LPAD(user_cert.ip_kta_wilcab, 4, "0") like "'.$w.'%"'),null);
			}
			else if($v == 'bk'){
				$this->db->where(('LPAD(user_cert.ip_bk, 2, "0") like "'.$w.'%"'),null);
			}
			else if($v == 'hkk'){
				$this->db->where(('LPAD(user_cert.ip_c, 2, "0") like "'.$w.'%"'),null);
			}
			
		}
		
		$this->db->order_by($order_field, $order_ascdesc); 
		//$this->db->order_by("sk_end", "desc");
		//$this->db->order_by("sertid", "desc"); 
		//$this->db->order_by("code", "desc"); 
		
		//$this->db->group_by('user_cert.ip_kta');
		
		
		$this->db->limit($limit, $start); 
		return $this->db->get()->result_array(); 
	}
	public function count_filter_ip($search,$column,$filter){
		$this->db->select('user_cert.user_id');
        $this->db->from('user_cert');
		$this->db->join('user_profiles', 'user_cert.user_id = user_profiles.user_id', 'left');
		$this->db->join('m_bk', 'TRIM(LEADING "0" FROM user_cert.ip_bk) = TRIM(LEADING "0" FROM m_bk.value)', 'left');
		
		$this->db->join('user_faip', 'user_faip.id = user_cert.cert_url', 'left');
		
		//$this->db->where('skip_id <> ""', null,false);
		//$this->db->where('ip_kta <> ""', null,false);
		$this->db->where('user_cert.user_id <> 0', null,false);
		$this->db->where('user_cert.status', 2);
		if($this->session->userdata('code_bk_hkk')!='')
			$this->db->where("TRIM(LEADING '0' FROM value) = ".ltrim($this->session->userdata('code_bk_hkk'), '0'),null);
		if($search!=''){
			$this->db->like('REPLACE(concat(lower(COALESCE(firstname,"")),lower(COALESCE(lastname,"")))," ","")', str_replace(' ','',strtolower($search)));
			$this->db->or_like("cert_title", $search); 
			$this->db->or_like("m_bk.name", $search); 
			$this->db->or_like("lic_num", $search); 
			$this->db->or_like("TRIM(LEADING '0' FROM ip_kta)", ltrim($search, '0')); 
			$this->db->or_like("startyear", $search); 
			$this->db->or_like("endyear", $search); 
		}
		
		foreach($column as $v){
			if($v['data']=="0" && $v['search']['value']!=''){
				$this->db->like('REPLACE(concat(lower(COALESCE(firstname,"")),lower(COALESCE(lastname,"")))," ","")', str_replace(' ','',strtolower($v['search']['value']))); 
			}
			if($v['data']=="1" && $v['search']['value']!=''){
				$this->db->like("cert_title", $v['search']['value']); 
			}	
			if($v['data']=="2" && $v['search']['value']!=''){
				$this->db->like("m_bk.name", $v['search']['value']); 
			}
			if($v['data']=="lic_num" && $v['search']['value']!=''){
				$this->db->like("lic_num", $v['search']['value']); 
			}
			if($v['data']=="4" && $v['search']['value']!=''){
				$this->db->like("TRIM(LEADING '0' FROM ip_kta)", ltrim($v['search']['value'], '0')); 
			}	
			if($v['data']=="startyear" && $v['search']['value']!=''){
				$this->db->like("startyear", $v['search']['value']); 
			}
			if($v['data']=="endyear" && $v['search']['value']!=''){
				$this->db->like("endyear", $v['search']['value']); 
			}
		}
		
		foreach($filter as $v=>$w){
			if($v == 'status'){
				if($w=='1') $this->db->where(('user_cert.endyear>=now()'),null);
				else if($w=='0') $this->db->where(('user_cert.endyear<now()'),null);
			}
			else if($v == 'cab'){
				$this->db->where(('LPAD(user_cert.ip_kta_wilcab, 4, "0") like "'.$w.'%"'),null);
			}
			else if($v == 'bk'){
				$this->db->where(('LPAD(user_cert.ip_bk, 2, "0") like "'.$w.'%"'),null);
			}
			else if($v == 'hkk'){
				$this->db->where(('LPAD(user_cert.ip_c, 2, "0") like "'.$w.'%"'),null);
			}
		}
		
		//$this->db->group_by('user_cert.ip_kta');
		
		//$this->db->or_like('no_kta', $search); 
		return $this->db->get()->num_rows(); // Untuk menghitung jumlah data sesuai dengan filter pada textbox pencarian
	}
	
	
	/**
	 * Move to Payment_Model, this function to be deprecated and deleted later
	 */
	public function count_all_va(){
		return $this->payment_mod->count_all_va();
	}

	/**
	 * Move to Payment_Model, this function to be deprecated and deleted later
	 */	
	public function filter_va($search, $limit, $start, $order_field, $order_ascdesc,$column,$filter){
		return $this->payment_mod->filter_va($search, $limit, $start, $order_field, $order_ascdesc,$column,$filter);
	}
	
	/**
	 * Move to Payment_Model, this function to be deprecated and deleted later
	 */	
	public function count_filter_va($search,$column,$filter){
		return $this->payment_mod->count_filter_va($search,$column,$filter);
	}
	
	public function count_all_setstri(){
		$this->db->select('user_transfer.id');
		$this->db->from('user_transfer');
		$this->db->join('user_profiles', 'user_transfer.user_id = user_profiles.user_id', 'left');
		$this->db->join('members', 'members.person_id = user_transfer.user_id', 'left');		
		$this->db->join('m_kolektif', 'm_kolektif.id = user_profiles.kolektif_name_id', 'left');
		$this->db->join('users', 'users.id = user_profiles.user_id', 'left');
		//$this->db->join('m_bk', 'TRIM(LEADING "0" FROM user_cert.ip_bk) = TRIM(LEADING "0" FROM m_bk.value)', 'left');
		
		//$this->db->where('user_transfer.createddate >= "2021-02-01 00:00:00"', null,false);
		$this->db->where('user_transfer.pay_type', 5);
		$this->db->where('user_transfer.vnv_status', 1);
		$this->db->where('username <> ""', null,false);
		//$this->db->where('((select count(id) from log_stri where user_id = user_transfer.user_id and id_pay = user_transfer.id)=0 and (select count(person_id) from members where person_id = user_transfer.user_id)=1 and user_transfer.status = 1 and (CASE WHEN user_transfer.rel_id=0 THEN user_transfer.vnv_status=1 ELSE 1=1 END))', null);
		$this->db->where('user_transfer.id not in (select id_pay from log_stri)', null,false);
		//$this->db->where('bukti', '');
		//$this->db->where('user_transfer.status', 0);
		
		return $this->db->count_all_results(); // Untuk menghitung semua data siswa
	}
	public function filter_setstri($search, $limit, $start, $order_field, $order_ascdesc,$column,$filter){
		$this->db->select('user_transfer.*,firstname,lastname,no_kta,from_date,thru_date,jenis_anggota,
		(select ip_tipe from user_cert where user_transfer.rel_id=id and status=2 order by createddate desc limit 1) as lic_id,
		(select lic_num from user_cert where user_transfer.rel_id=id and status=2 order by createddate desc limit 1) as lic_num,
		(select cert_title from user_cert where user_transfer.rel_id=id and status=2 order by createddate desc limit 1) as cert_title,
		(select startyear from user_cert where user_transfer.rel_id=id and status=2 order by createddate desc limit 1) as startyear,
		(select endyear from user_cert where user_transfer.rel_id=id and status=2 order by createddate desc limit 1) as endyear,
		(select name from m_kolektif where status=1 and id=user_profiles.kolektif_name_id) as kolektif_name'); //m_bk.name as bk_name
		//$this->db->select('users.id as person_id,skip45.*,MAX(sk_from) as sk_from, MAX(sertid) as sertid, MAX(noip) as noip');
		
        $this->db->from('user_transfer');
		$this->db->join('user_profiles', 'user_transfer.user_id = user_profiles.user_id', 'left');
		$this->db->join('members', 'members.person_id = user_transfer.user_id', 'left');
		$this->db->join('m_kolektif', 'm_kolektif.id = user_profiles.kolektif_name_id', 'left');
		$this->db->join('users', 'users.id = user_profiles.user_id', 'left');
		$this->db->where('user_transfer.pay_type', 5);
		$this->db->where('user_transfer.vnv_status', 1);
		$this->db->where('username <> ""', null,false);
		//$this->db->where('((select count(id) from log_stri where user_id = user_transfer.user_id and id_pay = user_transfer.id)=0 and (select count(person_id) from members where person_id = user_transfer.user_id)=1 and user_transfer.status = 1 and (CASE WHEN user_transfer.rel_id=0 THEN user_transfer.vnv_status=1 ELSE 1=1 END))', null);
		$this->db->where('user_transfer.id not in (select id_pay from log_stri)', null,false);
		
		
		if($search!=''){
			$this->db->group_start();
			$this->db->like('REPLACE(concat(lower(COALESCE(firstname,"")),lower(COALESCE(lastname,"")))," ","")', str_replace(' ','',strtolower($search)));
			$this->db->or_like("user_transfer.createddate", $search); 
			$this->db->or_like("user_transfer.description", $search); 
			//$this->db->or_like("(select code from m_pay_type where value=user_transfer.pay_type limit 1)", $search,false); 
			//$this->db->or_where("(select code from m_pay_type where value=user_transfer.pay_type limit 1) like '%".$search."%'", null, false); 
			$this->db->or_like("TRIM(LEADING '0' FROM no_kta)", ltrim($search, '0')); 
			$this->db->or_like("m_kolektif.name", $search); 
			$this->db->group_end();
			//$this->db->or_like("sukarelatotal", $search); 
		}
		//print_r($column);
		foreach($column as $v){
			if($v['data']=="0" && $v['search']['value']!=''){
				
			}
			if($v['data']=="1" && $v['search']['value']!=''){
				$this->db->like("user_transfer.createddate", $v['search']['value']); 
			}
			if($v['data']=="2" && $v['search']['value']!=''){
				if(preg_match("/".strtolower($v['search']['value'])."/i", 'peralihan')) 
					$this->db->where("user_transfer.rel_id", 0); 
				else 
					$this->db->where("(select cert_title from user_cert where id=user_transfer.rel_id) like '%".$v['search']['value']."%'", null, false); 
			}
			if($v['data']=="3" && $v['search']['value']!=''){
				$this->db->like('REPLACE(concat(lower(COALESCE(firstname,"")),lower(COALESCE(lastname,"")))," ","")', str_replace(' ','',strtolower($v['search']['value']))); 
			}	
			if($v['data']=="4" && $v['search']['value']!=''){
				$this->db->like("TRIM(LEADING '0' FROM no_kta)", $v['search']['value']); 
			}
			//if($v['data']=="4" && $v['search']['value']!=''){
			//	$this->db->where("(select code from m_pay_type where value=user_transfer.pay_type limit 1) like '%".$v['search']['value']."%'", null, false); 
			//}	
			if($v['data']=="5" && $v['search']['value']!=''){
				$this->db->group_start();
				$this->db->like("members.from_date", $v['search']['value']); 
				$this->db->or_like("members.thru_date", $v['search']['value']); 
				$this->db->group_end();
			}
			if($v['data']=="lic_num" && $v['search']['value']!=''){
				$this->db->where("(select lic_num from user_cert where user_transfer.rel_id=id and status=2 order by createddate desc limit 1) like '%".$v['search']['value']."%'", null, false); 
			}
			if($v['data']=="7" && $v['search']['value']!=''){
				$this->db->group_start();
				$this->db->where("(select startyear from user_cert where user_transfer.rel_id=id and status=2 order by createddate desc limit 1) like '%".$v['search']['value']."%'", null, false); 
				$this->db->or_where("(select endyear from user_cert where user_transfer.rel_id=id and status=2 order by createddate desc limit 1) like '%".$v['search']['value']."%'", null, false); 
				$this->db->group_end();
			}
			if($v['data']=="8" && $v['search']['value']!=''){
				if (strpos('anggota muda', strtolower($v['search']['value'])) !== false) {
					$this->db->like("jenis_anggota", 1); 
				}
				else if (strpos('anggota biasa', strtolower($v['search']['value'])) !== false) {
					$this->db->like("jenis_anggota", 2); 
				}
				else if (strpos('anggota luar biasa', strtolower($v['search']['value'])) !== false) {
					$this->db->like("jenis_anggota", 3); 
				}
				else if (strpos('anggota kehormatan', strtolower($v['search']['value'])) !== false) {
					$this->db->like("jenis_anggota", 4); 
				}
			}
			if($v['data']=="kolektif_name" && $v['search']['value']!=''){
				$this->db->like("m_kolektif.name", $v['search']['value']); 
			}
			if($v['data']=="description" && $v['search']['value']!=''){
				$this->db->like("user_transfer.description", $v['search']['value']); 
			}
		}
		
		$this->db->group_start();
		foreach($filter as $v=>$w){
			if($v == 'status'){
				if($w=='1') { $this->db->where('user_transfer.rel_id<>0',null); }
				else if($w=='2') { $this->db->where('user_transfer.rel_id',0); }
			}
			else if($v == 'cab'){
				$this->db->where(('LPAD(members.code_wilayah, 4, "0") like "'.$w.'%"'),null);
			}
			else if($v == 'bk'){
				$this->db->where(('LPAD(members.code_bk_hkk, 2, "0") like "'.$w.'%"'),null);
			}
			else if($v == 'tgl_period'){
				$this->db->where(("DATE(user_transfer.createddate)>='".$w."'"),null);
			}
			else if($v == 'tgl_period2'){
				$this->db->where(("DATE(user_transfer.createddate)<='".$w."'"),null);
			}
			
		}
		$this->db->group_end();
		
		$this->db->order_by($order_field, $order_ascdesc); 
		//$this->db->order_by("sk_end", "desc");
		//$this->db->order_by("sertid", "desc"); 
		//$this->db->order_by("code", "desc"); 
		
		//$this->db->group_by('user_cert.ip_kta');
		
		
		$this->db->limit($limit, $start); 
		return $this->db->get()->result_array(); 
	}
	public function count_filter_setstri($search,$column,$filter){
		$this->db->select('user_transfer.id');
		$this->db->from('user_transfer');
		$this->db->join('user_profiles', 'user_transfer.user_id = user_profiles.user_id', 'left');
		$this->db->join('members', 'members.person_id = user_transfer.user_id', 'left');
		$this->db->join('m_kolektif', 'm_kolektif.id = user_profiles.kolektif_name_id', 'left');
		$this->db->join('users', 'users.id = user_profiles.user_id', 'left');
		$this->db->where('user_transfer.pay_type', 5);
		$this->db->where('user_transfer.vnv_status', 1);
		$this->db->where('username <> ""', null,false);
		//$this->db->where('((select count(id) from log_stri where user_id = user_transfer.user_id and id_pay = user_transfer.id)=0 and (select count(person_id) from members where person_id = user_transfer.user_id)=1 and user_transfer.status = 1 and (CASE WHEN user_transfer.rel_id=0 THEN user_transfer.vnv_status=1 ELSE 1=1 END))', null);
		$this->db->where('user_transfer.id not in (select id_pay from log_stri)', null,false);
		if($search!=''){
			$this->db->group_start();
			$this->db->like('REPLACE(concat(lower(COALESCE(firstname,"")),lower(COALESCE(lastname,"")))," ","")', str_replace(' ','',strtolower($search)));
			$this->db->or_like("user_transfer.createddate", $search); 
			$this->db->or_like("user_transfer.description", $search); 
			//$this->db->or_like("(select code from m_pay_type where value=user_transfer.pay_type limit 1)", $search,false); 
			//$this->db->or_where("(select code from m_pay_type where value=user_transfer.pay_type limit 1) like '%".$search."%'", null, false); 
			$this->db->or_like("TRIM(LEADING '0' FROM no_kta)", ltrim($search, '0')); 
			$this->db->or_like("m_kolektif.name", $search); 
			$this->db->group_end();
			//$this->db->or_like("sukarelatotal", $search); 
		}
		
		foreach($column as $v){
			if($v['data']=="0" && $v['search']['value']!=''){
				
			}
			if($v['data']=="1" && $v['search']['value']!=''){
				$this->db->like("user_transfer.createddate", $v['search']['value']); 
			}
			if($v['data']=="2" && $v['search']['value']!=''){
				if(preg_match("/".strtolower($v['search']['value'])."/i", 'peralihan')) 
					$this->db->where("user_transfer.rel_id", 0); 
				else 
					$this->db->where("(select cert_title from user_cert where id=user_transfer.rel_id) like '%".$v['search']['value']."%'", null, false); 
			}
			if($v['data']=="3" && $v['search']['value']!=''){
				$this->db->like('REPLACE(concat(lower(COALESCE(firstname,"")),lower(COALESCE(lastname,"")))," ","")', str_replace(' ','',strtolower($v['search']['value']))); 
			}	
			if($v['data']=="4" && $v['search']['value']!=''){
				$this->db->like("TRIM(LEADING '0' FROM no_kta)", $v['search']['value']); 
			}
			//if($v['data']=="4" && $v['search']['value']!=''){
			//	$this->db->where("(select code from m_pay_type where value=user_transfer.pay_type limit 1) like '%".$v['search']['value']."%'", null, false); 
			//}	
			if($v['data']=="5" && $v['search']['value']!=''){
				$this->db->group_start();
				$this->db->like("members.from_date", $v['search']['value']); 
				$this->db->or_like("members.thru_date", $v['search']['value']); 
				$this->db->group_end();
			}
			if($v['data']=="lic_num" && $v['search']['value']!=''){
				$this->db->where("(select lic_num from user_cert where user_transfer.rel_id=id and status=2 order by createddate desc limit 1) like '%".$v['search']['value']."%'", null, false); 
			}
			if($v['data']=="7" && $v['search']['value']!=''){
				$this->db->group_start();
				$this->db->where("(select startyear from user_cert where user_transfer.rel_id=id and status=2 order by createddate desc limit 1) like '%".$v['search']['value']."%'", null, false); 
				$this->db->or_where("(select endyear from user_cert where user_transfer.rel_id=id and status=2 order by createddate desc limit 1) like '%".$v['search']['value']."%'", null, false); 
				$this->db->group_end();
			}
			if($v['data']=="8" && $v['search']['value']!=''){
				if (strpos('anggota muda', strtolower($v['search']['value'])) !== false) {
					$this->db->like("jenis_anggota", 1); 
				}
				else if (strpos('anggota biasa', strtolower($v['search']['value'])) !== false) {
					$this->db->like("jenis_anggota", 2); 
				}
				else if (strpos('anggota luar biasa', strtolower($v['search']['value'])) !== false) {
					$this->db->like("jenis_anggota", 3); 
				}
				else if (strpos('anggota kehormatan', strtolower($v['search']['value'])) !== false) {
					$this->db->like("jenis_anggota", 4); 
				}
			}
			if($v['data']=="kolektif_name" && $v['search']['value']!=''){
				$this->db->like("m_kolektif.name", $v['search']['value']); 
			}
			if($v['data']=="description" && $v['search']['value']!=''){
				$this->db->like("user_transfer.description", $v['search']['value']); 
			}
		}
				
		$this->db->group_start();
		foreach($filter as $v=>$w){
			if($v == 'status'){
				if($w=='1') { $this->db->where('user_transfer.rel_id<>0',null); }
				else if($w=='2') { $this->db->where('user_transfer.rel_id',0); }
			}
			else if($v == 'cab'){
				$this->db->where(('LPAD(members.code_wilayah, 4, "0") like "'.$w.'%"'),null);
			}
			else if($v == 'bk'){
				$this->db->where(('LPAD(members.code_bk_hkk, 2, "0") like "'.$w.'%"'),null);
			}
			else if($v == 'tgl_period'){
				$this->db->where(("DATE(user_transfer.createddate)>='".$w."'"),null);
			}
			else if($v == 'tgl_period2'){
				$this->db->where(("DATE(user_transfer.createddate)<='".$w."'"),null);
			}
			
		}
		$this->db->group_end();
		
		//$this->db->group_by('user_cert.ip_kta');
		
		//$this->db->or_like('no_kta', $search); 
		return $this->db->get()->num_rows(); // Untuk menghitung jumlah data sesuai dengan filter pada textbox pencarian
	}
	
	
	public function count_all_report(){
		$this->db->select('members.id');
		$this->db->from('members');
		$this->db->join('user_profiles', 'user_profiles.user_id = members.person_id', 'left');
		$this->db->join('users', 'users.id = members.person_id', 'left');
		$this->db->where('username<>""',null,false);
		$this->db->where('members.person_id in (select user_id from user_transfer where (pay_type=1 or pay_type=2) and status=1)',null,false);
		
		return $this->db->count_all_results(); // Untuk menghitung semua data siswa
	}
	public function filter_report($search, $limit, $start, $order_field, $order_ascdesc,$column,$filter){
		$this->db->select('(case when created_at<(select modifieddate from user_transfer b where members.person_id=b.user_id and pay_type=1 and b.status=1 order by modifieddate desc limit 1) then (select modifieddate from user_transfer b where members.person_id=b.user_id and pay_type=1 and b.status=1 order by modifieddate desc limit 1) else created_at end) as tgl_reg,(case when updated_at<(select modifieddate from user_transfer b where members.person_id=b.user_id and pay_type=2 and b.status=1 order by modifieddate desc limit 1) then (select modifieddate from user_transfer b where members.person_id=b.user_id and pay_type=2 and b.status=1 order by modifieddate desc limit 1) else updated_at end) as tgl_her,(select pay_type from user_transfer b where members.person_id=b.user_id and (pay_type=1 or pay_type=2) and b.status=1 order by modifieddate desc limit 1) as tipe,firstname,lastname,no_kta,code_bk_hkk,code_wilayah,members.person_id as id'); 
		
        $this->db->from('members');
		$this->db->join('user_profiles', 'user_profiles.user_id = members.person_id', 'left');
		$this->db->join('users', 'users.id = members.person_id', 'left');
		$this->db->where('username<>""',null,false);
		
		if($search!=''){
			$this->db->like('REPLACE(concat(lower(COALESCE(firstname,"")),lower(COALESCE(lastname,"")))," ","")', str_replace(' ','',strtolower($search)));
			$this->db->or_like("TRIM(LEADING '0' FROM code_bk_hkk)", ltrim($search, '0')); 
			$this->db->or_like("TRIM(LEADING '0' FROM code_wilayah)", ltrim($search, '0')); 
			$this->db->or_like("TRIM(LEADING '0' FROM no_kta)", ltrim($search, '0')); 
			
		}
		
		foreach($column as $v){
			if($v['data']=="0" && $v['search']['value']!=''){
				
			}
			//if($v['data']=="1" && $v['search']['value']!=''){
				//$this->db->like("(case when created_at<(select modifieddate from user_transfer b where members.person_id=b.user_id and pay_type=1 and b.status=1 order by modifieddate desc limit 1) then (select modifieddate from user_transfer b where members.person_id=b.user_id and pay_type=1 and b.status=1 order by modifieddate desc limit 1) else created_at end)", $v['search']['value']); 
			//}
			if($v['data']=="1" && $v['search']['value']!=''){
				$this->db->like('REPLACE(concat(lower(COALESCE(firstname,"")),lower(COALESCE(lastname,"")))," ","")', str_replace(' ','',strtolower($v['search']['value']))); 
			}	
			if($v['data']=="2" && $v['search']['value']!=''){
				$this->db->like("TRIM(LEADING '0' FROM no_kta)", ltrim($v['search']['value'], '0')); 
			}
			if($v['data']=="3" && $v['search']['value']!=''){
				$this->db->like("TRIM(LEADING '0' FROM code_bk_hkk)", ltrim($v['search']['value'], '0')); 
			}	
			if($v['data']=="4" && $v['search']['value']!=''){
				$this->db->like("TRIM(LEADING '0' FROM code_wilayah)", ltrim($v['search']['value'], '0')); 
			}
		}
		
		$status = '';
		$from_date = '';
		$to_date = '';
		foreach($filter as $v=>$w){
			if($v == 'status'){
				if($w=='1') { $status = 1; }
				else if($w=='2') { $status = 2; }
			}
			else if($v == 'cab'){
				if($w!='') $this->db->where(('LPAD(members.code_wilayah, 4, "0") like "'.$w.'%"'),null);
			}
			else if($v == 'bk'){
				if($w!='') $this->db->where(('LPAD(members.code_bk_hkk, 2, "0") like "'.$w.'%"'),null);
			}
			else if($v == 'tgl_period'){
				if($w!='') $from_date = $w;
			}
			else if($v == 'tgl_period2'){
				if($w!='') $to_date = $w;
			}
		}		
		
		
		if($status==1)
		{
			if($from_date!='' && $to_date!=''){
				$this->db->where(("date(case when created_at<IFNULL((select modifieddate from user_transfer b where members.person_id=b.user_id and pay_type=1 and b.status=1 order by modifieddate desc limit 1),'1970-01-01') then (select date(modifieddate) from user_transfer b where members.person_id=b.user_id and pay_type=1 and b.status=1 order by modifieddate desc limit 1) else date(created_at) end) between '".$from_date."' and '".$to_date."'"),null,false);
			}
			else{
				if($from_date!='') $this->db->where(("date(case when created_at<IFNULL((select modifieddate from user_transfer b where members.person_id=b.user_id and pay_type=1 and b.status=1 order by modifieddate desc limit 1),'1970-01-01') then (select modifieddate from user_transfer b where members.person_id=b.user_id and pay_type=1 and b.status=1 order by modifieddate desc limit 1) else created_at end) >='".$from_date."'"),null,false);
				
				if($to_date!='') $this->db->where(("date(case when created_at<IFNULL((select modifieddate from user_transfer b where members.person_id=b.user_id and pay_type=1 and b.status=1 order by modifieddate desc limit 1),'1970-01-01') then (select modifieddate from user_transfer b where members.person_id=b.user_id and pay_type=1 and b.status=1 order by modifieddate desc limit 1) else created_at end) <='".$to_date."'"),null,false);
			}
		}
		else if($status==2)
		{
			if($from_date!='' && $to_date!=''){
				//$this->db->where(("date(case when (select b.createddate from log_her_kta b join user_transfer x on b.id_pay=x.id where members.person_id=b.user_id and x.status=1 order by b.id desc limit 1)<(select modifieddate from user_transfer b where members.person_id=b.user_id and pay_type=2 and b.status=1 order by modifieddate desc limit 1) then (select modifieddate from user_transfer b where members.person_id=b.user_id and pay_type=2 and b.status=1 order by modifieddate desc limit 1) else (select b.createddate from log_her_kta b join user_transfer x on b.id_pay=x.id where members.person_id=b.user_id and x.status=1 order by b.id desc limit 1) end) between '".$from_date."' and '".$to_date."'"),null,false);
				
				$this->db->where(("person_id in (select x.user_id from user_transfer x join log_status_kta y on x.id=y.pay_id join log_her_kta z on z.id_pay=x.id where y.notes='finance' and y.new_status=1 and date(y.createddate) between '".$from_date."' and '".$to_date."')"),null,false);
				
			}
			else{
				//if($from_date!='') $this->db->where(("date(case when (select b.createddate from log_her_kta b join user_transfer x on b.id_pay=x.id where members.person_id=b.user_id and x.status=1 order by b.id desc limit 1)<(select modifieddate from user_transfer b where members.person_id=b.user_id and pay_type=2 and b.status=1 order by modifieddate desc limit 1) then (select modifieddate from user_transfer b where members.person_id=b.user_id and pay_type=2 and b.status=1 order by modifieddate desc limit 1) else (select b.createddate from log_her_kta b join user_transfer x on b.id_pay=x.id where members.person_id=b.user_id and x.status=1 order by b.id desc limit 1) end) >='".$from_date."'"),null,false);
				
				if($from_date!='') $this->db->where(("person_id in (select x.user_id from user_transfer x join log_status_kta y on x.id=y.pay_id join log_her_kta z on z.id_pay=x.id where y.notes='finance' and y.new_status=1 and y.createddate >='".$from_date."')"),null,false);
				
				//if($to_date!='') $this->db->where(("date(case when (select b.createddate from log_her_kta b join user_transfer x on b.id_pay=x.id where members.person_id=b.user_id and x.status=1 order by b.id desc limit 1)<(select modifieddate from user_transfer b where members.person_id=b.user_id and pay_type=2 and b.status=1 order by modifieddate desc limit 1) then (select modifieddate from user_transfer b where members.person_id=b.user_id and pay_type=2 and b.status=1 order by modifieddate desc limit 1) else (select b.createddate from log_her_kta b join user_transfer x on b.id_pay=x.id where members.person_id=b.user_id and x.status=1 order by b.id desc limit 1) end) <='".$to_date."'"),null,false);
				
				if($to_date!='') $this->db->where(("person_id in (select x.user_id from user_transfer x join log_status_kta y on x.id=y.pay_id join log_her_kta z on z.id_pay=x.id where y.notes='finance' and y.new_status=1 and y.createddate <='".$to_date."')"),null,false);
			}
		}	
		else{
			if($from_date!='' && $to_date!=''){
				$this->db->group_start();
				$this->db->where(("date(case when created_at<IFNULL((select modifieddate from user_transfer b where members.person_id=b.user_id and pay_type=1 and b.status=1 order by modifieddate desc limit 1),'1970-01-01') then (select date(modifieddate) from user_transfer b where members.person_id=b.user_id and pay_type=1 and b.status=1 order by modifieddate desc limit 1) else date(created_at) end) between '".$from_date."' and '".$to_date."'"),null,false);
				
				//$this->db->or_where(("date(case when (select b.createddate from log_her_kta b join user_transfer x on b.id_pay=x.id where members.person_id=b.user_id and x.status=1 order by b.id desc limit 1)<(select modifieddate from user_transfer b where members.person_id=b.user_id and pay_type=2 and b.status=1 order by modifieddate desc limit 1) then (select modifieddate from user_transfer b where members.person_id=b.user_id and pay_type=2 and b.status=1 order by modifieddate desc limit 1) else (select b.createddate from log_her_kta b join user_transfer x on b.id_pay=x.id where members.person_id=b.user_id and x.status=1 order by b.id desc limit 1) end) between '".$from_date."' and '".$to_date."'"),null,false);
				
				$this->db->or_where(("person_id in (select x.user_id from user_transfer x join log_status_kta y on x.id=y.pay_id join log_her_kta z on z.id_pay=x.id where y.notes='finance' and y.new_status=1 and date(y.createddate) between '".$from_date."' and '".$to_date."')"),null,false);
				
				$this->db->group_end();
			}
			else{
				$this->db->group_start();
				if($from_date!='') $this->db->or_where(("date(case when created_at<IFNULL((select modifieddate from user_transfer b where members.person_id=b.user_id and pay_type=1 and b.status=1 order by modifieddate desc limit 1),'1970-01-01') then (select modifieddate from user_transfer b where members.person_id=b.user_id and pay_type=1 and b.status=1 order by modifieddate desc limit 1) else created_at end) >='".$from_date."'"),null,false);
				
				if($to_date!='') $this->db->or_where(("date(case when created_at<IFNULL((select modifieddate from user_transfer b where members.person_id=b.user_id and pay_type=1 and b.status=1 order by modifieddate desc limit 1),'1970-01-01') then (select modifieddate from user_transfer b where members.person_id=b.user_id and pay_type=1 and b.status=1 order by modifieddate desc limit 1) else created_at end) <='".$to_date."'"),null,false);
				
				//if($from_date!='') $this->db->or_where(("date(case when (select b.createddate from log_her_kta b join user_transfer x on b.id_pay=x.id where members.person_id=b.user_id and x.status=1 order by b.id desc limit 1)<(select modifieddate from user_transfer b where members.person_id=b.user_id and pay_type=2 and b.status=1 order by modifieddate desc limit 1) then (select modifieddate from user_transfer b where members.person_id=b.user_id and pay_type=2 and b.status=1 order by modifieddate desc limit 1) else (select b.createddate from log_her_kta b join user_transfer x on b.id_pay=x.id where members.person_id=b.user_id and x.status=1 order by b.id desc limit 1) end) >='".$from_date."'"),null,false);
				
				//if($to_date!='') $this->db->or_where(("date(case when (select b.createddate from log_her_kta b join user_transfer x on b.id_pay=x.id where members.person_id=b.user_id and x.status=1 order by b.id desc limit 1)<(select modifieddate from user_transfer b where members.person_id=b.user_id and pay_type=2 and b.status=1 order by modifieddate desc limit 1) then (select modifieddate from user_transfer b where members.person_id=b.user_id and pay_type=2 and b.status=1 order by modifieddate desc limit 1) else (select b.createddate from log_her_kta b join user_transfer x on b.id_pay=x.id where members.person_id=b.user_id and x.status=1 order by b.id desc limit 1) end) <='".$to_date."'"),null,false);
				
				if($from_date!='') $this->db->or_where(("person_id in (select x.user_id from user_transfer x join log_status_kta y on x.id=y.pay_id join log_her_kta z on z.id_pay=x.id where y.notes='finance' and y.new_status=1 and y.createddate >='".$from_date."')"),null,false);
				
				if($to_date!='') $this->db->or_where(("person_id in (select x.user_id from user_transfer x join log_status_kta y on x.id=y.pay_id join log_her_kta z on z.id_pay=x.id where y.notes='finance' and y.new_status=1 and y.createddate <='".$to_date."')"),null,false);
				
				$this->db->group_end();
			}
		}
		

		$this->db->order_by($order_field, $order_ascdesc); 
		//$this->db->order_by("sk_end", "desc");
		//$this->db->order_by("sertid", "desc"); 
		//$this->db->order_by("code", "desc"); 
		
		//$this->db->group_by('user_cert.ip_kta');
		
		
		$this->db->limit($limit, $start); 
		return $this->db->get()->result_array(); 
	}
	public function count_filter_report($search,$column,$filter){
		$this->db->select('members.id');
		$this->db->from('members');
		$this->db->join('user_profiles', 'user_profiles.user_id = members.person_id', 'left');
		$this->db->join('users', 'users.id = members.person_id', 'left');
		$this->db->where('username<>""',null,false);
		
		if($search!=''){
			$this->db->like('REPLACE(concat(lower(COALESCE(firstname,"")),lower(COALESCE(lastname,"")))," ","")', str_replace(' ','',strtolower($search)));
			$this->db->or_like("TRIM(LEADING '0' FROM code_bk_hkk)", ltrim($search, '0')); 
			$this->db->or_like("TRIM(LEADING '0' FROM code_wilayah)", ltrim($search, '0')); 
			$this->db->or_like("TRIM(LEADING '0' FROM no_kta)", ltrim($search, '0')); 
			
		}
		
		foreach($column as $v){
			if($v['data']=="0" && $v['search']['value']!=''){
				
			}
			//if($v['data']=="1" && $v['search']['value']!=''){
				//$this->db->like("(case when created_at<(select modifieddate from user_transfer b where members.person_id=b.user_id and pay_type=1 and b.status=1 order by modifieddate desc limit 1) then (select modifieddate from user_transfer b where members.person_id=b.user_id and pay_type=1 and b.status=1 order by modifieddate desc limit 1) else created_at end)", $v['search']['value']); 
			//}
			if($v['data']=="1" && $v['search']['value']!=''){
				$this->db->like('REPLACE(concat(lower(COALESCE(firstname,"")),lower(COALESCE(lastname,"")))," ","")', str_replace(' ','',strtolower($v['search']['value']))); 
			}	
			if($v['data']=="2" && $v['search']['value']!=''){
				$this->db->like("TRIM(LEADING '0' FROM no_kta)", ltrim($v['search']['value'], '0')); 
			}
			if($v['data']=="3" && $v['search']['value']!=''){
				$this->db->like("TRIM(LEADING '0' FROM code_bk_hkk)", ltrim($v['search']['value'], '0')); 
			}	
			if($v['data']=="4" && $v['search']['value']!=''){
				$this->db->like("TRIM(LEADING '0' FROM code_wilayah)", ltrim($v['search']['value'], '0')); 
			}
		}
		
		$status = '';
		$from_date = '';
		$to_date = '';
		foreach($filter as $v=>$w){
			if($v == 'status'){
				if($w=='1') { $status = 1; }
				else if($w=='2') { $status = 2; }
			}
			else if($v == 'cab'){
				if($w!='') $this->db->where(('LPAD(members.code_wilayah, 4, "0") like "'.$w.'%"'),null);
			}
			else if($v == 'bk'){
				if($w!='') $this->db->where(('LPAD(members.code_bk_hkk, 2, "0") like "'.$w.'%"'),null);
			}
			else if($v == 'tgl_period'){
				if($w!='') $from_date = $w;
			}
			else if($v == 'tgl_period2'){
				if($w!='') $to_date = $w;
			}
		}		
		
		if($status==1)
		{
			if($from_date!='' && $to_date!=''){
				$this->db->where(("date(case when created_at<IFNULL((select modifieddate from user_transfer b where members.person_id=b.user_id and pay_type=1 and b.status=1 order by modifieddate desc limit 1),'1970-01-01') then (select date(modifieddate) from user_transfer b where members.person_id=b.user_id and pay_type=1 and b.status=1 order by modifieddate desc limit 1) else date(created_at) end) between '".$from_date."' and '".$to_date."'"),null,false);
			}
			else{
				if($from_date!='') $this->db->where(("date(case when created_at<IFNULL((select modifieddate from user_transfer b where members.person_id=b.user_id and pay_type=1 and b.status=1 order by modifieddate desc limit 1),'1970-01-01') then (select modifieddate from user_transfer b where members.person_id=b.user_id and pay_type=1 and b.status=1 order by modifieddate desc limit 1) else created_at end) >='".$from_date."'"),null,false);
				
				if($to_date!='') $this->db->where(("date(case when created_at<IFNULL((select modifieddate from user_transfer b where members.person_id=b.user_id and pay_type=1 and b.status=1 order by modifieddate desc limit 1),'1970-01-01') then (select modifieddate from user_transfer b where members.person_id=b.user_id and pay_type=1 and b.status=1 order by modifieddate desc limit 1) else created_at end) <='".$to_date."'"),null,false);
			}
		}
		else if($status==2)
		{
			if($from_date!='' && $to_date!=''){
				//$this->db->where(("date(case when (select b.createddate from log_her_kta b join user_transfer x on b.id_pay=x.id where members.person_id=b.user_id and x.status=1 order by b.id desc limit 1)<(select modifieddate from user_transfer b where members.person_id=b.user_id and pay_type=2 and b.status=1 order by modifieddate desc limit 1) then (select modifieddate from user_transfer b where members.person_id=b.user_id and pay_type=2 and b.status=1 order by modifieddate desc limit 1) else (select b.createddate from log_her_kta b join user_transfer x on b.id_pay=x.id where members.person_id=b.user_id and x.status=1 order by b.id desc limit 1) end) between '".$from_date."' and '".$to_date."'"),null,false);
				
				$this->db->where(("person_id in (select x.user_id from user_transfer x join log_status_kta y on x.id=y.pay_id join log_her_kta z on z.id_pay=x.id where y.notes='finance' and y.new_status=1 and date(y.createddate) between '".$from_date."' and '".$to_date."')"),null,false);
				
			}
			else{
				//if($from_date!='') $this->db->where(("date(case when (select b.createddate from log_her_kta b join user_transfer x on b.id_pay=x.id where members.person_id=b.user_id and x.status=1 order by b.id desc limit 1)<(select modifieddate from user_transfer b where members.person_id=b.user_id and pay_type=2 and b.status=1 order by modifieddate desc limit 1) then (select modifieddate from user_transfer b where members.person_id=b.user_id and pay_type=2 and b.status=1 order by modifieddate desc limit 1) else (select b.createddate from log_her_kta b join user_transfer x on b.id_pay=x.id where members.person_id=b.user_id and x.status=1 order by b.id desc limit 1) end) >='".$from_date."'"),null,false);
				
				if($from_date!='') $this->db->where(("person_id in (select x.user_id from user_transfer x join log_status_kta y on x.id=y.pay_id join log_her_kta z on z.id_pay=x.id where y.notes='finance' and y.new_status=1 and y.createddate >='".$from_date."')"),null,false);
				
				//if($to_date!='') $this->db->where(("date(case when (select b.createddate from log_her_kta b join user_transfer x on b.id_pay=x.id where members.person_id=b.user_id and x.status=1 order by b.id desc limit 1)<(select modifieddate from user_transfer b where members.person_id=b.user_id and pay_type=2 and b.status=1 order by modifieddate desc limit 1) then (select modifieddate from user_transfer b where members.person_id=b.user_id and pay_type=2 and b.status=1 order by modifieddate desc limit 1) else (select b.createddate from log_her_kta b join user_transfer x on b.id_pay=x.id where members.person_id=b.user_id and x.status=1 order by b.id desc limit 1) end) <='".$to_date."'"),null,false);
				
				if($to_date!='') $this->db->where(("person_id in (select x.user_id from user_transfer x join log_status_kta y on x.id=y.pay_id join log_her_kta z on z.id_pay=x.id where y.notes='finance' and y.new_status=1 and y.createddate <='".$to_date."')"),null,false);
			}
		}	
		else{
			if($from_date!='' && $to_date!=''){
				$this->db->group_start();
				$this->db->where(("date(case when created_at<IFNULL((select modifieddate from user_transfer b where members.person_id=b.user_id and pay_type=1 and b.status=1 order by modifieddate desc limit 1),'1970-01-01') then (select date(modifieddate) from user_transfer b where members.person_id=b.user_id and pay_type=1 and b.status=1 order by modifieddate desc limit 1) else date(created_at) end) between '".$from_date."' and '".$to_date."'"),null,false);
				
				//$this->db->or_where(("date(case when (select b.createddate from log_her_kta b join user_transfer x on b.id_pay=x.id where members.person_id=b.user_id and x.status=1 order by b.id desc limit 1)<(select modifieddate from user_transfer b where members.person_id=b.user_id and pay_type=2 and b.status=1 order by modifieddate desc limit 1) then (select modifieddate from user_transfer b where members.person_id=b.user_id and pay_type=2 and b.status=1 order by modifieddate desc limit 1) else (select b.createddate from log_her_kta b join user_transfer x on b.id_pay=x.id where members.person_id=b.user_id and x.status=1 order by b.id desc limit 1) end) between '".$from_date."' and '".$to_date."'"),null,false);
				
				$this->db->or_where(("person_id in (select x.user_id from user_transfer x join log_status_kta y on x.id=y.pay_id join log_her_kta z on z.id_pay=x.id where y.notes='finance' and y.new_status=1 and date(y.createddate) between '".$from_date."' and '".$to_date."')"),null,false);
				
				$this->db->group_end();
			}
			else{
				$this->db->group_start();
				if($from_date!='') $this->db->or_where(("date(case when created_at<IFNULL((select modifieddate from user_transfer b where members.person_id=b.user_id and pay_type=1 and b.status=1 order by modifieddate desc limit 1),'1970-01-01') then (select modifieddate from user_transfer b where members.person_id=b.user_id and pay_type=1 and b.status=1 order by modifieddate desc limit 1) else created_at end) >='".$from_date."'"),null,false);
				
				if($to_date!='') $this->db->or_where(("date(case when created_at<IFNULL((select modifieddate from user_transfer b where members.person_id=b.user_id and pay_type=1 and b.status=1 order by modifieddate desc limit 1),'1970-01-01') then (select modifieddate from user_transfer b where members.person_id=b.user_id and pay_type=1 and b.status=1 order by modifieddate desc limit 1) else created_at end) <='".$to_date."'"),null,false);
				
				//if($from_date!='') $this->db->or_where(("date(case when (select b.createddate from log_her_kta b join user_transfer x on b.id_pay=x.id where members.person_id=b.user_id and x.status=1 order by b.id desc limit 1)<(select modifieddate from user_transfer b where members.person_id=b.user_id and pay_type=2 and b.status=1 order by modifieddate desc limit 1) then (select modifieddate from user_transfer b where members.person_id=b.user_id and pay_type=2 and b.status=1 order by modifieddate desc limit 1) else (select b.createddate from log_her_kta b join user_transfer x on b.id_pay=x.id where members.person_id=b.user_id and x.status=1 order by b.id desc limit 1) end) >='".$from_date."'"),null,false);
				
				//if($to_date!='') $this->db->or_where(("date(case when (select b.createddate from log_her_kta b join user_transfer x on b.id_pay=x.id where members.person_id=b.user_id and x.status=1 order by b.id desc limit 1)<(select modifieddate from user_transfer b where members.person_id=b.user_id and pay_type=2 and b.status=1 order by modifieddate desc limit 1) then (select modifieddate from user_transfer b where members.person_id=b.user_id and pay_type=2 and b.status=1 order by modifieddate desc limit 1) else (select b.createddate from log_her_kta b join user_transfer x on b.id_pay=x.id where members.person_id=b.user_id and x.status=1 order by b.id desc limit 1) end) <='".$to_date."'"),null,false);
				
				if($from_date!='') $this->db->or_where(("person_id in (select x.user_id from user_transfer x join log_status_kta y on x.id=y.pay_id join log_her_kta z on z.id_pay=x.id where y.notes='finance' and y.new_status=1 and y.createddate >='".$from_date."')"),null,false);
				
				if($to_date!='') $this->db->or_where(("person_id in (select x.user_id from user_transfer x join log_status_kta y on x.id=y.pay_id join log_her_kta z on z.id_pay=x.id where y.notes='finance' and y.new_status=1 and y.createddate <='".$to_date."')"),null,false);
				
				$this->db->group_end();
			}
		}
		//$this->db->group_by('user_cert.ip_kta');
		
		//$this->db->or_like('no_kta', $search); 
		return $this->db->get()->num_rows(); // Untuk menghitung jumlah data sesuai dengan filter pada textbox pencarian
	}
	
	public function count_all_report_stri(){
		$this->db->select('members.id');
		$this->db->from('log_stri');
		$this->db->join('members', 'log_stri.user_id = members.person_id', 'left');
		$this->db->join('user_profiles', 'user_profiles.user_id = members.person_id', 'left');
		$this->db->join('members_certificate', "TRIM(LEADING '0' FROM members_certificate.stri_id) = TRIM(LEADING '0' FROM log_stri.stri_id)", 'left');
		
		return $this->db->count_all_results(); // Untuk menghitung semua data siswa
	}
	public function filter_report_stri($search, $limit, $start, $order_field, $order_ascdesc,$column,$filter){
		$this->db->select('members_certificate.certificate_type,members_certificate.stri_code_bk_hkk,members_certificate.th,members_certificate.warga,members_certificate.stri_tipe,
		members_certificate.stri_id,log_stri.createddate as tgl,stri_type as tipe,firstname,lastname,members.no_kta,code_bk_hkk,code_wilayah,log_stri.id as id,log_stri.user_id as user_id'); 
		
        $this->db->from('log_stri');
		$this->db->join('members', 'log_stri.user_id = members.person_id', 'left');
		$this->db->join('user_profiles', 'user_profiles.user_id = members.person_id', 'left');
		$this->db->join('members_certificate', "TRIM(LEADING '0' FROM members_certificate.stri_id) = TRIM(LEADING '0' FROM log_stri.stri_id)", 'left');
		//$this->db->where('username<>""',null,false);
		
		if($search!=''){
			$this->db->like('REPLACE(concat(lower(COALESCE(firstname,"")),lower(COALESCE(lastname,"")))," ","")', str_replace(' ','',strtolower($search)));
			$this->db->or_like("TRIM(LEADING '0' FROM code_bk_hkk)", ltrim($search, '0')); 
			$this->db->or_like("TRIM(LEADING '0' FROM code_wilayah)", ltrim($search, '0')); 
			$this->db->or_like("TRIM(LEADING '0' FROM members.no_kta)", ltrim($search, '0')); 
			$this->db->or_like("log_stri.createddate", $search); 
			$this->db->or_like('concat(COALESCE(certificate_type,""),COALESCE(LPAD(stri_code_bk_hkk, 3, "0"),""),COALESCE(th,""),COALESCE(warga,""),COALESCE(stri_tipe,""),COALESCE(LPAD(members_certificate.stri_id, 8, "0"),""))', str_replace('.','',($search))); 
		}
		
		foreach($column as $v){
			if($v['data']=="0" && $v['search']['value']!=''){
				$this->db->like('log_stri.createddate', $v['search']['value']);
			}
			if($v['data']=="1" && $v['search']['value']!=''){
				$this->db->like('REPLACE(concat(lower(COALESCE(firstname,"")),lower(COALESCE(lastname,"")))," ","")', str_replace(' ','',strtolower($v['search']['value']))); 
			}	
			if($v['data']=="2" && $v['search']['value']!=''){
				$this->db->like('concat(COALESCE(certificate_type,""),COALESCE(LPAD(stri_code_bk_hkk, 3, "0"),""),COALESCE(th,""),COALESCE(warga,""),COALESCE(stri_tipe,""),COALESCE(LPAD(members_certificate.stri_id, 8, "0"),""))', str_replace('.','',($v['search']['value']))); 
			}	
			if($v['data']=="3" && $v['search']['value']!=''){
				$this->db->like("TRIM(LEADING '0' FROM members.no_kta)", ltrim($v['search']['value'], '0')); 
			}
			if($v['data']=="4" && $v['search']['value']!=''){
				$this->db->like("TRIM(LEADING '0' FROM code_bk_hkk)", ltrim($v['search']['value'], '0')); 
			}	
			if($v['data']=="5" && $v['search']['value']!=''){
				$this->db->like("TRIM(LEADING '0' FROM code_wilayah)", ltrim($v['search']['value'], '0')); 
			}
		}
		
		$status = '';
		$from_date = '';
		$to_date = '';
		foreach($filter as $v=>$w){
			if($v == 'status'){
				if($w=='1') { $status = 1; }
				else if($w=='2') { $status = 2; }
			}
			else if($v == 'cab'){
				if($w!='') $this->db->where(('LPAD(members.code_wilayah, 4, "0") like "'.$w.'%"'),null);
			}
			else if($v == 'bk'){
				if($w!='') $this->db->where(('LPAD(members.code_bk_hkk, 2, "0") like "'.$w.'%"'),null);
			}
			else if($v == 'tgl_period'){
				if($w!='') $from_date = $w;
			}
			else if($v == 'tgl_period2'){
				if($w!='') $to_date = $w;
			}
		}		
		
		
		if($from_date!='' && $to_date!=''){
			$this->db->where(("log_stri.createddate between '".$from_date."' and '".$to_date."'"),null,false);
		}
		else{
			if($from_date!='') $this->db->where(("log_stri.createddate >='".$from_date."'"),null,false);
			
			if($to_date!='') $this->db->where(("log_stri.createddate <='".$to_date."'"),null,false);
		}
		
		if($status==1)
		{
			$this->db->where(("log_stri.stri_type<>0"),null,false);
		}
		else if($status==2)
		{
			$this->db->where(("log_stri.stri_type=0"),null,false);
		}	
		

		$this->db->order_by($order_field, $order_ascdesc); 
		//$this->db->order_by("sk_end", "desc");
		//$this->db->order_by("sertid", "desc"); 
		//$this->db->order_by("code", "desc"); 
		
		//$this->db->group_by('user_cert.ip_kta');
		
		
		$this->db->limit($limit, $start); 
		return $this->db->get()->result_array(); 
	}
	public function count_filter_report_stri($search,$column,$filter){
		$this->db->select('log_stri.id');
		$this->db->from('log_stri');
		$this->db->join('members', 'log_stri.user_id = members.person_id', 'left');
		$this->db->join('user_profiles', 'user_profiles.user_id = members.person_id', 'left');
		$this->db->join('members_certificate', "TRIM(LEADING '0' FROM members_certificate.stri_id) = TRIM(LEADING '0' FROM log_stri.stri_id)", 'left');
		//$this->db->where('username<>""',null,false);
		
		if($search!=''){
			$this->db->like('REPLACE(concat(lower(COALESCE(firstname,"")),lower(COALESCE(lastname,"")))," ","")', str_replace(' ','',strtolower($search)));
			$this->db->or_like("TRIM(LEADING '0' FROM code_bk_hkk)", ltrim($search, '0')); 
			$this->db->or_like("TRIM(LEADING '0' FROM code_wilayah)", ltrim($search, '0')); 
			$this->db->or_like("TRIM(LEADING '0' FROM members.no_kta)", ltrim($search, '0')); 
			$this->db->or_like("log_stri.createddate", $search); 
			$this->db->or_like('concat(COALESCE(certificate_type,""),COALESCE(LPAD(stri_code_bk_hkk, 3, "0"),""),COALESCE(th,""),COALESCE(warga,""),COALESCE(stri_tipe,""),COALESCE(LPAD(members_certificate.stri_id, 8, "0"),""))', str_replace('.','',($search))); 
		}
		
		foreach($column as $v){
			if($v['data']=="0" && $v['search']['value']!=''){
				$this->db->like('log_stri.createddate', $v['search']['value']);
			}
			if($v['data']=="1" && $v['search']['value']!=''){
				$this->db->like('REPLACE(concat(lower(COALESCE(firstname,"")),lower(COALESCE(lastname,"")))," ","")', str_replace(' ','',strtolower($v['search']['value']))); 
			}	
			if($v['data']=="2" && $v['search']['value']!=''){
				$this->db->like('concat(COALESCE(certificate_type,""),COALESCE(LPAD(stri_code_bk_hkk, 3, "0"),""),COALESCE(th,""),COALESCE(warga,""),COALESCE(stri_tipe,""),COALESCE(LPAD(members_certificate.stri_id, 8, "0"),""))', str_replace('.','',($v['search']['value']))); 
			}	
			if($v['data']=="3" && $v['search']['value']!=''){
				$this->db->like("TRIM(LEADING '0' FROM members.no_kta)", ltrim($v['search']['value'], '0')); 
			}
			if($v['data']=="4" && $v['search']['value']!=''){
				$this->db->like("TRIM(LEADING '0' FROM code_bk_hkk)", ltrim($v['search']['value'], '0')); 
			}	
			if($v['data']=="5" && $v['search']['value']!=''){
				$this->db->like("TRIM(LEADING '0' FROM code_wilayah)", ltrim($v['search']['value'], '0')); 
			}
		}
		
		$status = '';
		$from_date = '';
		$to_date = '';
		foreach($filter as $v=>$w){
			if($v == 'status'){
				if($w=='1') { $status = 1; }
				else if($w=='2') { $status = 2; }
			}
			else if($v == 'cab'){
				if($w!='') $this->db->where(('LPAD(members.code_wilayah, 4, "0") like "'.$w.'%"'),null);
			}
			else if($v == 'bk'){
				if($w!='') $this->db->where(('LPAD(members.code_bk_hkk, 2, "0") like "'.$w.'%"'),null);
			}
			else if($v == 'tgl_period'){
				if($w!='') $from_date = $w;
			}
			else if($v == 'tgl_period2'){
				if($w!='') $to_date = $w;
			}
		}		
		
		
		if($from_date!='' && $to_date!=''){
			$this->db->where(("log_stri.createddate between '".$from_date."' and '".$to_date."'"),null,false);
		}
		else{
			if($from_date!='') $this->db->where(("log_stri.createddate >='".$from_date."'"),null,false);
			
			if($to_date!='') $this->db->where(("log_stri.createddate <='".$to_date."'"),null,false);
		}
		
		if($status==1)
		{
			$this->db->where(("log_stri.stri_type<>0"),null,false);
		}
		else if($status==2)
		{
			$this->db->where(("log_stri.stri_type=0"),null,false);
		}	
		
		//$this->db->group_by('user_cert.ip_kta');
		
		//$this->db->or_like('no_kta', $search); 
		return $this->db->get()->num_rows(); // Untuk menghitung jumlah data sesuai dengan filter pada textbox pencarian
	}
	
	
	public function count_all_approval(){
		$this->db->select('user_approval.id');
		$this->db->from('user_cert_temp');
		$this->db->join('user_approval', 'user_cert_temp.faip_id = user_approval.faip_id', 'left');
		$this->db->where('app_id', $this->session->userdata('admin_id'));
		$this->db->where('(user_approval.status <> "Waiting for Approval" or (user_approval.status="Waiting for Approval" and next_approval="'.$this->session->userdata('admin_id').'"))', null);
		
		
		return $this->db->count_all_results(); // Untuk menghitung semua data siswa
	}
	public function filter_approval($search, $limit, $start, $order_field, $order_ascdesc,$column,$filter){
		$this->db->select('user_cert_temp.*,user_approval.status as status_app,user_approval.status_date,user_approval.id'); 
		
        $this->db->from('user_cert_temp');
		$this->db->join('user_approval', 'user_cert_temp.faip_id = user_approval.faip_id', 'left');
		$this->db->where('app_id', $this->session->userdata('admin_id'));
		$this->db->where('(user_approval.status <> "Waiting for Approval" or (user_approval.status="Waiting for Approval" and next_approval="'.$this->session->userdata('admin_id').'"))', null);
		
		if($search!=''){
			$this->db->like('REPLACE(lower(COALESCE(ip_name,""))," ","")', str_replace(' ','',strtolower($search)));
			$this->db->or_like("cert_title", $search); 
			$this->db->or_like("user_cert_temp.modifieddate", $search); 
			$this->db->or_like("TRIM(LEADING '0' FROM ip_kta)", ltrim($search, '0')); 
		}
		
		foreach($column as $v){
			if($v['data']=="0" && $v['search']['value']!=''){
				
			}
			if($v['data']=="1" && $v['search']['value']!=''){
				$this->db->like("user_cert_temp.modifieddate", $v['search']['value']); 
			}
			if($v['data']=="2" && $v['search']['value']!=''){
				$this->db->like('REPLACE(lower(COALESCE(ip_name,""))," ","")', str_replace(' ','',strtolower($v['search']['value']))); 
			}	
			if($v['data']=="3" && $v['search']['value']!=''){
				$this->db->like("TRIM(LEADING '0' FROM ip_kta)", ltrim($v['search']['value'], '0')); 
			}
			if($v['data']=="4" && $v['search']['value']!=''){
				$this->db->like("cert_title", $v['search']['value']); 
			}
			if($v['data']=="5" && $v['search']['value']!=''){
				$this->db->like("user_approval.status", $v['search']['value']); 
			}
		}
		
		foreach($filter as $v=>$w){
			if($v == 'status'){
				if($w=='1') { $this->db->where('user_approval.status','Approved'); }
				else if($w=='0') { $this->db->where('user_approval.status','Waiting for Approval'); }
				else if($w=='2') { $this->db->where('user_approval.status','Rejected'); }
			}
			else if($v == 'cab'){
				$this->db->where(('LPAD(location, 4, "0") like "'.$w.'%"'),null);
			}
			else if($v == 'bk'){
				$this->db->where(('LPAD(ip_bk, 2, "0") like "'.$w.'%"'),null);
			}
			else if($v == 'tgl_period'){
				$this->db->where(("DATE(user_cert_temp.modifieddate)>='".$w."'"),null);
			}
			else if($v == 'tgl_period2'){
				$this->db->where(("DATE(user_cert_temp.modifieddate)<='".$w."'"),null);
			}
			
		}
		
		$this->db->order_by($order_field, $order_ascdesc); 
		
		
		$this->db->limit($limit, $start); 
		return $this->db->get()->result_array(); 
	}
	public function count_filter_approval($search,$column,$filter){
		$this->db->select('user_approval.id');
		$this->db->from('user_cert_temp');
		$this->db->join('user_approval', 'user_cert_temp.faip_id = user_approval.faip_id', 'left');
		$this->db->where('app_id', $this->session->userdata('admin_id'));
		$this->db->where('(user_approval.status <> "Waiting for Approval" or (user_approval.status="Waiting for Approval" and next_approval="'.$this->session->userdata('admin_id').'"))', null);
		
		
		if($search!=''){
			$this->db->like('REPLACE(lower(COALESCE(ip_name,""))," ","")', str_replace(' ','',strtolower($search)));
			$this->db->or_like("cert_title", $search); 
			$this->db->or_like("user_cert_temp.modifieddate", $search); 
			$this->db->or_like("TRIM(LEADING '0' FROM ip_kta)", ltrim($search, '0')); 
		}
		
		foreach($column as $v){
			if($v['data']=="0" && $v['search']['value']!=''){
				
			}
			if($v['data']=="1" && $v['search']['value']!=''){
				$this->db->like("user_cert_temp.modifieddate", $v['search']['value']); 
			}
			if($v['data']=="2" && $v['search']['value']!=''){
				$this->db->like('REPLACE(lower(COALESCE(ip_name,""))," ","")', str_replace(' ','',strtolower($v['search']['value']))); 
			}	
			if($v['data']=="3" && $v['search']['value']!=''){
				$this->db->like("TRIM(LEADING '0' FROM ip_kta)", ltrim($v['search']['value'], '0')); 
			}
			if($v['data']=="4" && $v['search']['value']!=''){
				$this->db->like("cert_title", $v['search']['value']); 
			}
			if($v['data']=="5" && $v['search']['value']!=''){
				$this->db->like("user_approval.status", $v['search']['value']); 
			}
		}
		
		foreach($filter as $v=>$w){
			if($v == 'status'){
				if($w=='1') { $this->db->where('user_approval.status','Approved'); }
				else if($w=='0') { $this->db->where('user_approval.status','Waiting for Approval'); }
				else if($w=='2') { $this->db->where('user_approval.status','Rejected'); }
			}
			else if($v == 'cab'){
				$this->db->where(('LPAD(location, 4, "0") like "'.$w.'%"'),null);
			}
			else if($v == 'bk'){
				$this->db->where(('LPAD(ip_bk, 2, "0") like "'.$w.'%"'),null);
			}
			else if($v == 'tgl_period'){
				$this->db->where(("DATE(user_cert_temp.modifieddate)>='".$w."'"),null);
			}
			else if($v == 'tgl_period2'){
				$this->db->where(("DATE(user_cert_temp.modifieddate)<='".$w."'"),null);
			}
			
		}
		
		return $this->db->get()->num_rows(); // Untuk menghitung jumlah data sesuai dengan filter pada textbox pencarian
	}
	
	//API
	
	public function api_get_all_members() {
        $this->db->select('users.id as ID, username as no_kta, users.email, user_profiles.firstname, user_profiles.lastname, user_profiles.gender, user_profiles.idcard, user_profiles.dob, users.user_status as sts, (select code_wilayah from members where person_id=users.id limit 1) as cab,(select code_bk_hkk from members where person_id=users.id limit 1) as bk,(select from_date from members where person_id=users.id limit 1) as from_date,(select thru_date from members where person_id=users.id limit 1) as thru_date');
        $this->db->from('user_profiles');
		$this->db->join('users', 'user_profiles.user_id = users.id', 'left');
		//$this->db->join('members', 'user_profiles.user_id = members.person_id', 'left');
		//$this->db->order_by("user_profiles.id", "DESC"); 
		//$this->db->limit(100, 1);
        $Q = $this->db->get();
        if ($Q->num_rows() > 0) {
            $return = $Q->result();
        } else {
            $return = 0;
        }
        $Q->free_result();
        return $return;
    }
	
	
	
	
	
	
	
	
	/*
	public function filter_pi($search, $limit, $start, $order_field, $order_ascdesc){
		$this->db->select('person_id as ID, add_name as firstname, user_profiles.lastname, status as sts,members_certificate.id as certid,members_certificate.*');
        $this->db->from('members_certificate');
		$this->db->join('user_profiles', 'user_profiles.user_id = members_certificate.person_id', 'left');
		$this->db->where('skip_id <> ""', null,false);
		$this->db->like('add_name', $search);
		$this->db->or_like('user_profiles.lastname', $search); 
		//$this->db->or_like('no_kta', $search); 
		//$this->db->or_like('alamat', $search); 
		$this->db->order_by($order_field, $order_ascdesc); 
		
		$this->db->group_by('no_kta');
		
		$this->db->limit($limit, $start); 
		return $this->db->get()->result_array(); 
		//return $this->db->get('siswa')->result_array(); 
	}
	public function count_all_pi(){
		$this->db->select('no_kta');
		$this->db->from('members_certificate');
		$this->db->join('user_profiles', 'user_profiles.user_id = members_certificate.person_id', 'left');
		$this->db->where('skip_id <> ""', null,false);
		
		$this->db->group_by('no_kta');
		
		return $this->db->count_all_results(); // Untuk menghitung semua data siswa
	}
	public function count_filter_pi($search){
		$this->db->select('person_id as ID, add_name as firstname, user_profiles.lastname, status as sts,members_certificate.id as certid,members_certificate.*');
        $this->db->from('members_certificate');
		$this->db->join('user_profiles', 'user_profiles.user_id = members_certificate.person_id', 'left');
		$this->db->where('skip_id <> ""', null,false);
		$this->db->like('add_name', $search);
		$this->db->or_like('user_profiles.lastname', $search); 
		
		$this->db->group_by('no_kta');
		
		//$this->db->or_like('no_kta', $search); 
		return $this->db->get()->num_rows(); // Untuk menghitung jumlah data sesuai dengan filter pada textbox pencarian
	}
	public function get_pi_by_no_kta($no_kta,$certid){
		$this->db->select('person_id as ID, add_name as firstname, user_profiles.lastname, status as sts,members_certificate.id as certid,members_certificate.*');
        $this->db->from('members_certificate');
		$this->db->join('user_profiles', 'user_profiles.user_id = members_certificate.person_id', 'left');
		$this->db->where('skip_id <> ""', null,false);
		$this->db->where('no_kta', $no_kta);
		$this->db->where('members_certificate.id<>', $certid);
		//$this->db->like('add_name', $search);
		//$this->db->or_like('user_profiles.lastname', $search); 
		//$this->db->or_like('no_kta', $search); 
		//$this->db->or_like('alamat', $search); 
		$this->db->order_by('skip_sk', 'desc'); 
		
		//$this->db->group_by('no_kta');
		
		//$this->db->limit($limit, $start); 
		return $this->db->get()->result_array(); 
		//return $this->db->get('siswa')->result_array(); 
	}
	*/
//-------------------------------------------------------------------------------------------- Tambahan	by IP
  	 public function get_member_certii_by_id($id)
      	{
  		$this->db->select('*');
  		$this->db->from('members_certificate');
  		$this->db->where('person_id',$id);
  		$this->db->order_by('id', 'DESC'); $this->db->limit('1');
  		$query = $this->db->get() ;
  		return $query->row();
      
	}		

    	public function get_member_certi_by_id($id)
    	{
    	        
		$this->db->select('*');
		$this->db->from('user_cert');
		$this->db->where('user_id',$id);
		$this->db->where('ip_kta <> ','');
		$this->db->order_by('id', 'DESC'); $this->db->limit('1');
		$query = $this->db->get() ;
		return $query->row();
    
	}	
	
	public function cari_nacab($wil)
    	{
		$this->db->select('*');
		$this->db->from('m_cab');
		$this->db->where('value',$wil);
		$query = $this->db->get() ;
		return $query->row();
    
	}		
	
	public function cari_nabk($bk)
    	{
		$this->db->select('*');
		$this->db->from('m_bk');
		$this->db->where('value',$bk);
		$query = $this->db->get() ;
		return $query->row();
    
	}			

	public function ambil_data_cabang_all()
    	{
		$this->db->select('*');
		$this->db->from('laporan');
		
		        $Q = $this->db->get();
		        if ($Q->num_rows() > 0) {
		            $return = $Q->result();
		        } else {
		            $return = 0;
		        }
		        $Q->free_result();
        	return $return;
    
	}			  
	
	public function ambil_data_cabang_per_wilayah($kowil)
    	{
		$this->db->select('*');
		$this->db->from('laporan');
		$this->db->where('kowil',$kowil);
		        $Q = $this->db->get();
		        if ($Q->num_rows() > 0) {
		            $return = $Q->result();
		        } else {
		            $return = 0;
		        }
		        $Q->free_result();
        	return $return;
    
	}			  
	
	public function ambil_data_wilayah_all()
	{
		$this->db->select('*');
		$this->db->from('laporan');
		$this->db->group_by('kowil');
		        $Q = $this->db->get();
		        if ($Q->num_rows() > 0) {
		            $return = $Q->result();
		        } else {
		            $return = 0;
		        }
		        $Q->free_result();
        	return $return;		
		
//		return $this->db->count_all_results();		
	
	}
    	
	public function ambil_data_wilayah_all_view()
	{
		$this->db->select('*');
		$this->db->from('laporan');
		$this->db->order_by('kocab');
		        $Q = $this->db->get();
		        if ($Q->num_rows() > 0) {
		            $return = $Q->result();
		        } else {
		            $return = 0;
		        }
		        $Q->free_result();
        	return $return;		
	
	}    	
	
    	public function hitung_jumlah_cabangnya($kowil)
    	{
		$this->db->select('*');
		$this->db->from('laporan');
		$this->db->where('kowil',$kowil);
		return $this->db->count_all_results();		
    
	}			    	
    	
    	public function janggota_aktif($kowil, $fiel)
    	{
    		$tgl = date('Y-m-d') ; 
    		$this->db->select('*');
		$this->db->from('members');
		$this->db->join('users', 'users.id = members.person_id', 'left');
		$this->db->where('username <> ', '' );
		$this->db->where($fiel,$kowil);
		$this->db->where('thru_date >= ',$tgl );		
		return $this->db->count_all_results();	
		
	}	
    	
    	public function janggota_nonaktif($kowil, $fiel)
    	{
    		$tgl = date('Y-m-d') ; 
    		$this->db->select('*');
		$this->db->from('members');
		$this->db->join('users', 'users.id = members.person_id', 'left');
		$this->db->where('username <> ' , '' );
		$this->db->where($fiel,$kowil);
		$this->db->where('thru_date < ',$tgl );
		return $this->db->count_all_results();	
		
	}	    	
    	
    	public function update_data_lap_wilayah($id, $data_update_lap)
        {
        	$this->db->where('id', $id);
        	$return=$this->db->update('laporan', $data_update_lap);
        	return $return;
        }	          	
    	
    	public function update_data_lap_cab($id, $data_update_lap_cab)
        {
	        	$this->db->where('id', $id);
	        	$return=$this->db->update('laporan', $data_update_lap_cab);
	        	return $return;
        }
        
	public function get_ttd_stri($id)
    	{
		$this->db->select('*');
		$this->db->from('members_certificate');
		$this->db->where('person_id',$id);
		$query = $this->db->get() ;
		return $query->row();
    
	}	
	
	public function ambil_create_date($id)
    	{
		$this->db->select('*');
		$this->db->from('user_cert');
		$this->db->where('user_id',$id);
		$this->db->where('ip_kta <> ','');
		$this->db->order_by('id', 'DESC'); $this->db->limit('1');
		$query = $this->db->get() ;
		return $query->row();
    
	}			        	
	
	public function get_status_faip($id)
    	{
		$this->db->select('*');
		$this->db->from('user_faip');
		$this->db->where('user_id',$id);
		$query = $this->db->get() ;
		return $query->row();
    
	}	
        
	public function update_member_sert($id_id,$rowInsert)
	{
		$this->db->where('id', $id_id);
		$return=$this->db->update('members_certificate', $rowInsert);
	        return $return;
        }	
        
	public function cari_log_her_kta($pay_typee)
   	{
		$this->db->select('*');
		$this->db->from('log_her_kta');
		$this->db->where('id_pay',$pay_typee);
		$this->db->order_by('id', 'DESC'); $this->db->limit('1');
		$query = $this->db->get() ;
		return $query->row();
    
	}			
	
      public function save_data_lap_temp($data_insert_lap_temp)
      {
          return $this->db->insert('laporan_skip_temp',$data_insert_lap_temp);
      }	
}
?>
