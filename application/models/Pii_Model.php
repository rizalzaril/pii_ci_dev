<?php


defined('BASEPATH') or exit('No direct script access allowed');

class Pii_Model extends CI_Model
{

	public function find_all()
	{
		$this->db->select('*');
		$this->db->from('persen_bagi');
		$this->db->order_by('id', 'DESC');
		return $this->db->get()->result();
	}

	public function save_data($data)
	{
		$this->db->insert('persen_bagi', $data);
		return $this->db->insert_id();
	}

	public function cek_kode()
	{
		$query = $this->db->query('SELECT MAX(kode) as kode_ from persen_bagi');
		$result = $query->row();
		return $result->kode_;
	}

	public function get_by_id($id)
	{
		return $this->db->get_where('persen_bagi', ['id' => $id])->result();
	}

	public function update($id, $data)
	{
		return $this->db->update('persen_bagi', $data, ['id' => $id]);
	}

	public function delete_by_id($id)
	{
		return $this->db->delete('persen_bagi', ['id' => $id]);
	}

	// ACPE \\
	public function get_acpe()
	{
		$this->db->select('*');
		$this->db->from('acpe');
		$this->db->order_by('id', 'DESC');
		return $this->db->get()->result();
	}

	public function insert_from_import($data)
	{
		return $this->db->insert('users', $data);
	}

	public function insert_from_import_aer($data)
	{
		return $this->db->insert('aer', $data);
	}

	public function insert_from_import_acpe($data)
	{
		return $this->db->insert('acpe', $data);
	}

	public function insert_data_profiles($data_profiles)
	{
		return $this->db->insert('user_profiles', $data_profiles);
	}

	public function insert_user_address($data_address)
	{
		return $this->db->insert('user_address', $data_address);
	}


	/// ITS \\\

	public function get_users($start, $length, $search = null, $order_col = 'id', $order_dir = 'DESC')
	{
		$this->db->select('*')->from('users');

		if (!empty($search)) {
			$this->db->group_start()
				->like('username', $search)
				->or_like('email', $search)
				->group_end();
		}

		// mapping nama kolom yang valid
		$allowed_cols = ['id', 'username', 'email'];
		if (!in_array($order_col, $allowed_cols)) {
			$order_col = 'id';
		}

		$order_dir = strtoupper($order_dir) === 'ASC' ? 'ASC' : 'DESC';

		$this->db->order_by($order_col, $order_dir);
		$this->db->limit($length, $start);

		return $this->db->get()->result();
	}



	public function count_filtered($search = null)
	{
		$this->db->from('users');

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


	//Get DATA KOLEKTIF

	public function get_data_kelompok()
	{
		return $this->db->get('m_kolektif')->result();
	}

	//CEK NEXT_ID BERDASARKAN LAST_ID DARI TB USERS
	public function cek_next_id_users()
	{
		$this->db->select('id');
		$this->db->order_by('id', 'DESC');
		$query = $this->db->get('users');
		$row = $query->row();

		return $row ? $row->id + 70 : 1;
	}
}
