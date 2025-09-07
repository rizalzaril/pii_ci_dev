<?php


defined('BASEPATH') or exit('No direct script access allowed');


class AuthModel extends CI_Model
{

  private $table = 'users';
  private $table_profiles = 'user_profiles';


  public function register($data)
  {
    $this->db->insert('users', $data);
    return $this->db->insert_id(); // Ambil id terakhir
  }

  public function register_profiles($data)
  {
    return $this->db->insert('user_profiles', $data);
  }


  public function getProfileByUserId($user_id)
  {
    return $this->db->get_where('profiles', ['user_id' => $user_id])->row_array();
  }

  public function update_last_login($id, $ip_public)
  {
    $this->db->where('id', $id);
    $this->db->update('users', [
      'last_login' => date('Y-m-d H:i:s'),
      'last_ip' => $ip_public
    ]);
  }



  public function updateProfile($user_id, $data)
  {
    $this->db->where('user_id', $user_id);
    return $this->db->update('profiles', $data);
  }

  public function insertProfile($data)
  {
    return $this->db->insert('profiles', $data);
  }


  public function getUsersEmail($email)
  {
    return $this->db->get_where($this->table, ['email' => $email])->row();
  }

  public function getUsersPassword($user_id)
  {
    $this->db->select('password');
    $this->db->from('users');
    $this->db->where('users.id', $user_id);
    return $this->db->get()->row();
  }



  public function getUsersProfile($user_id)
  {
    $this->db->select('users.id, users.name, users.email, users.kode_user, profiles.address, profiles.phone');
    $this->db->from('users');
    $this->db->join('profiles', 'profiles.user_id = users.id', 'left'); // pakai left join agar tidak error jika profile kosong
    $this->db->where('users.id', $user_id);
    return $this->db->get()->row();
  }


  public function ubahPassword($newPassword, $user_id)
  {
    $this->db->where('id', $user_id);
    $this->db->update('users', ['password' => $newPassword]);
  }


  public function updatePassword($email, $password)
  {
    $this->db->where('email', $email);
    $this->db->update('users', ['password' => $password]);
  }

  public function getUserToken($token)
  {
    return $this->db->get_where('user_token', ['token' => $token])->row();
  }

  public function insertToken($data)
  {
    return $this->db->insert('user_token', $data);
  }

  public function deleteToken($email)
  {
    return $this->db->delete('user_token', ['email' => $email]);
  }

  public function log_activity($user_id, $activity)
  {
    $ip_public = @file_get_contents('https://api.ipify.org'); // dapatkan IP publik asli

    $this->db->insert('log_activity', [
      'user_id'     => $user_id,
      'activity'    => $activity,
      'ip_address'  => $ip_public ?: $this->input->ip_address(), // fallback ke CI IP
      'created_at'  => date('Y-m-d H:i:s'),
    ]);
  }
}
