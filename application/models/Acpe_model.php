
<?php


defined('BASEPATH') or exit('No direct script access allowed');

class Acpe_model extends CI_Model
{

	public function get_acpe($start, $length, $order_col = null, $order_dir = null, $start_date = null, $end_date = null)
	{
		$this->db->select('*');
		$this->db->from('acpe');

		if (!empty($start_date) && !empty($end_date)) {
			$this->db->where('DATE(created_at) >=', $start_date);
			$this->db->where('DATE(created_at) <=', $end_date);
		}

		if (!empty($order_col) && !empty($order_dir)) {
			$this->db->order_by($order_col, $order_dir);
		}

		// if (!empty($search)) {
		// 	$this->db->group_start();
		// 	$this->db->like('no_aer', $search);
		// 	$this->db->or_like('nama', $search);
		// 	$this->db->or_like('grade', $search);
		// 	$this->db->or_like('kta', $search);
		// 	$this->db->group_end();
		// }


		$this->db->limit($length, $start);

		return $this->db->get()->result();
	}

	public function count_filtered($start_date = null, $end_date = null)
	{
		$this->db->from('acpe');

		if (!empty($start_date) && !empty($end_date)) {
			$this->db->where('DATE(created_at) >=', $start_date);
			$this->db->where('DATE(created_at) <=', $end_date);
		}

		return $this->db->count_all_results();
	}



	public function count_all()
	{
		return $this->db->count_all('acpe');
	}
}
