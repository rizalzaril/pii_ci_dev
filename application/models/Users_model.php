
<?php


defined('BASEPATH') or exit('No direct script access allowed');

class Users_model extends CI_Model
{


	public function find_all_users()
	{
		$this->db->select('*');
		$this->db->from('users');
		$this->db->order_by('id', 'DESC');
		$this->db->limit(100);
		return $this->db->get()->result();
	}

	public function get_user_detail($id)
	{
		return $this->db
			->select('users.*, user_profiles.*')
			->from('users')
			->join('user_profiles', 'user_profiles.user_id = users.id', 'left')
			->where('users.id', $id)
			->limit(10)
			->get()
			->row();
	}


	// public function get_detail_aer($kta)
	// {
	// 	$this->db->select('*');
	// 	$this->db->from('v_detail_aer_coba_6');
	// 	$this->db->where('no_kta', $kta);
	// 	return $this->db->get()->result();
	// }


	//GET DETAIL AER BY KTA
	public function get_detail_aer($kta)
	{
		// --- Ambil data utama (tanpa address, karena address bisa banyak) ---
		$this->db->select('
			v_aer_members_coba.*, 
			user_profiles.*, 
			users.*
		');
		$this->db->from('v_aer_members_coba');
		$this->db->join('user_profiles', 'user_profiles.user_id = v_aer_members_coba.person_id', 'left');
		$this->db->join('users', 'users.id = v_aer_members_coba.person_id', 'left');
		$this->db->join('m_param', 'm_param.id = v_aer_members_coba.person_id', 'left');
		$this->db->where('v_aer_members_coba.kta', $kta);
		$detail = $this->db->get()->row_array(); // ambil satu baris data utama

		if ($detail) {
			$person_id = $detail['person_id'];

			// // --- Address (bisa banyak) ---
			// $detail['addresses'] = $this->db
			// 	->where('user_id', $person_id)
			// 	->get('user_address')
			// 	->result_array();

			$detail['addresses'] = $this->db
				->select('user_address.*, m_param.*')       // Ambil semua field alamat + field dari m_param
				->from('user_address')
				->join('m_param', 'm_param.id = user_address.addresstype', 'left') // Join m_param ke alamat
				->where('user_address.user_id', $person_id)
				->get()
				->result_array();


			// --- Experiences ---
			$detail['experiences'] = $this->db
				->where('user_id', $person_id)
				->get('user_exp')
				->result_array();

			// --- Educations ---
			$detail['educations'] = $this->db
				->where('user_id', $person_id)
				->get('user_edu')
				->result_array();

			// --- Certifications ---
			$detail['certifications'] = $this->db
				->where('user_id', $person_id)
				->get('user_cert')
				->result_array();
		}

		return $detail;
	}



	//GET DETAIL APEC BY KTA
	public function get_detail_apec($kta)
	{
		// --- Ambil data utama (tanpa address, karena address bisa banyak) ---
		$this->db->select('
			v_apec_members.*, 
			user_profiles.*, 
			users.*
		');
		$this->db->from('v_apec_members');
		$this->db->join('user_profiles', 'user_profiles.user_id = v_apec_members.person_id', 'left');
		$this->db->join('users', 'users.id = v_apec_members.person_id', 'left');
		// $this->db->join('m_param', 'm_param.id = v_apec_members.person_id', 'left');
		$this->db->where('v_apec_members.nokta', $kta);
		$detail = $this->db->get()->row_array(); // ambil satu baris data utama

		if ($detail) {
			$person_id = $detail['person_id'];

			// // --- Address (bisa banyak) ---
			// $detail['addresses'] = $this->db
			// 	->where('user_id', $person_id)
			// 	->get('user_address')
			// 	->result_array();

			$detail['addresses'] = $this->db
				->select('user_address.*, m_param.*')       // Ambil semua field alamat + field dari m_param
				->from('user_address')
				->join('m_param', 'm_param.id = user_address.addresstype', 'left') // Join m_param ke alamat
				->where('user_address.user_id', $person_id)
				->get()
				->result_array();


			// --- Experiences ---
			$detail['experiences'] = $this->db
				->where('user_id', $person_id)
				->get('user_exp')
				->result_array();

			// --- Educations ---
			$detail['educations'] = $this->db
				->where('user_id', $person_id)
				->get('user_edu')
				->result_array();

			// --- Certifications ---
			$detail['certifications'] = $this->db
				->where('user_id', $person_id)
				->get('user_cert')
				->result_array();
		}

		return $detail;
	}


	//GET DETAIL ACPE BY KTA
	public function get_detail_acpe($kta)
	{
		// --- Ambil data utama (tanpa address, karena address bisa banyak) ---
		$this->db->select('
			v_acpe_members.*, 
			user_profiles.*, 
			users.*
		');
		$this->db->from('v_acpe_members');
		$this->db->join('user_profiles', 'user_profiles.user_id = v_acpe_members.person_id', 'left');
		$this->db->join('users', 'users.id = v_acpe_members.person_id', 'left');
		// $this->db->join('m_param', 'm_param.id = v_apec_members.person_id', 'left');
		$this->db->where('v_acpe_members.kta', $kta);
		$detail = $this->db->get()->row_array(); // ambil satu baris data utama

		if ($detail) {
			$person_id = $detail['person_id'];

			// // --- Address (bisa banyak) ---
			// $detail['addresses'] = $this->db
			// 	->where('user_id', $person_id)
			// 	->get('user_address')
			// 	->result_array();

			$detail['addresses'] = $this->db
				->select('user_address.*, m_param.*')       // Ambil semua field alamat + field dari m_param
				->from('user_address')
				->join('m_param', 'm_param.id = user_address.addresstype', 'left') // Join m_param ke alamat
				->where('user_address.user_id', $person_id)
				->get()
				->result_array();


			// --- Experiences ---
			$detail['experiences'] = $this->db
				->where('user_id', $person_id)
				->get('user_exp')
				->result_array();

			// --- Educations ---
			$detail['educations'] = $this->db
				->where('user_id', $person_id)
				->get('user_edu')
				->result_array();

			// --- Certifications ---
			$detail['certifications'] = $this->db
				->where('user_id', $person_id)
				->get('user_cert')
				->result_array();
		}

		return $detail;
	}






	public function msrwhere($table, $com, $field, $sb)
	{
		$this->db->order_by($field, $sb);
		return $this->db->get_where($table, $com);
	}


	public function get_aer_by_kta($kta)
	{
		$this->db->select('*');
		$this->db->from('aer');
		$this->db->where('kta', $kta);

		$query = $this->db->get();
		return $query->row(); // return 1 row object
	}


	// public function get_users($start, $length, $search = null)
	// {
	//   $this->db->select('id, username, email, activated');
	//   $this->db->from('users');

	//   // 🔍 Proses pencarian jika ada input
	//   if (!empty($search)) {
	//     $this->db->group_start();
	//     $this->db->like('username', $search);
	//     $this->db->or_like('email', $search);
	//     $this->db->group_end();
	//   }

	//   $this->db->order_by('id', 'DESC');
	//   $this->db->limit($length, $start);

	//   return $this->db->get()->result();
	// }

	public function get_users($start, $length, $search = null, $order_col = null, $order_dir = null, $is_duplicate = null)
	{
		$this->db->select('*');
		$this->db->from('users');

		// 🔹 Filter duplicate berdasarkan tabel users
		if ($is_duplicate !== null && $is_duplicate !== '') {
			if ($is_duplicate == 1) {
				// Duplicate Only → email sudah ada di users
				$this->db->where("email IN (SELECT email FROM users)", NULL, FALSE);
			} elseif ($is_duplicate == 0) {
				// Non Duplicate Only → email belum ada di users
				$this->db->where("email NOT IN (SELECT email FROM users)", NULL, FALSE);
			}
		}

		if (!empty($search)) {
			$this->db->group_start();
			$this->db->like('username', $search);
			$this->db->or_like('email', $search);
			$this->db->group_end();
		}

		if ($order_col && $order_dir) {
			$this->db->order_by($order_col, $order_dir);
		} else {
			$this->db->order_by('id', 'DESC');
		}

		$this->db->limit($length, $start);
		return $this->db->get()->result();
	}

	public function count_filtered($search = null, $is_duplicate = null)
	{
		$this->db->from('users');

		if ($is_duplicate !== null && $is_duplicate !== '') {
			if ($is_duplicate == 1) {
				$this->db->where("email IN (SELECT email FROM users)", NULL, FALSE);
			} elseif ($is_duplicate == 0) {
				$this->db->where("email NOT IN (SELECT email FROM users)", NULL, FALSE);
			}
		}

		if (!empty($search)) {
			$this->db->group_start();
			$this->db->like('username', $search);
			$this->db->or_like('email', $search);
			$this->db->group_end();
		}

		return $this->db->count_all_results();
	}



	public function count_all()
	{
		return $this->db->count_all('users');
	}
}
