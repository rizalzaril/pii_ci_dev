<?php


defined('BASEPATH') or exit('No direct script access allowed');


class Generate_va_model extends CI_Model
{

	public function get_members_non_va($limit = 700)
	{
		$this->db->select('
				up.user_id as user_id_profile, 
				m.code_wilayah,
				m.code_bk_hkk,
				m.no_kta,
				up.va,
				up.firstname,
				up.lastname
		');
		$this->db->from('members m');
		$this->db->join('user_profiles up', 'm.person_id = up.user_id', 'left');
		$this->db->where("(up.va IS NULL OR up.va = '')", NULL, FALSE);
		$this->db->order_by('m.id', 'DESC');
		$this->db->limit($limit);

		$query = $this->db->get();
		return $query->result(); // return dalam bentuk array of object
	}


	// ✅ Ambil 1 member berdasarkan ID
	public function get_member_by_id($user_id)
	{
		$this->db->select('m.person_id as user_id_profile, m.code_wilayah, m.code_bk_hkk, m.no_kta, up.va');
		$this->db->from('members m');
		$this->db->join('user_profiles up', 'up.user_id = m.person_id', 'left');
		$this->db->where('m.person_id', $user_id);
		$query = $this->db->get();
		return $query->row();
	}


	// ✅ Update VA
	public function update_va($user_id, $va)
	{
		$this->db->where('user_id', $user_id);
		return $this->db->update('user_profiles', ['va' => $va]);
	}
}
