
<?php


defined('BASEPATH') or exit('No direct script access allowed');

class User_profiles_model extends CI_Model
{

  public function get_profile_by_id($id)
  {

    return $this->db->get_where('user_profiles', ['user_id' => $id])->row();
  }


  public function find_all_users()
  {
    $this->db->select('*');
    $this->db->from('users');
    $this->db->order_by('id', 'DESC');
    $this->db->limit(100);
    return $this->db->get()->result();
  }


  public function get_user_profile($start, $limit)
  {
    return $this->db
      ->select('id, username, email, activated') // <--- Tambahkan kolom yang dibutuhkan
      ->from('user_profile')
      ->order_by('id', 'DESC')
      ->limit($limit, $start)
      ->get()
      ->result();
  }

  public function update_profiles($id, $data)
  {
    return $this->db->update('user_profiles', $data, ['user_id' => $id]);
  }


  public function count_all()
  {
    return $this->db->count_all('users');
  }

  public function update_profile_from_excel($user_id, $data)
  {
    return $this->db->update('user_profiles', $data, ['user_id' => $user_id]);
  }
}
