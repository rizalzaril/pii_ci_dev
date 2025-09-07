<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

class Main_mod extends CI_Model
{
	/**
	 * Constructor
	 */
	function __construct()
	{
		parent::__construct();
	}
	//-------------------------------------------Aktif record---------------------------------------------//
	/*keterangan variabel :
	$table : nama tabel
	$field : nama field
	$id    : isi dari sebuah field 
	$sb    : sort by (asc atau desc)
	$gb    : group by (nama fieldnya)
	$com   : bentuk array ('field'=>'key') untuk pencarian
	$set   : bentuk array ('field'=>'isi') untuk input,update
	*/

	public function select($table, $field, $where, $like, $join, $order, $group, $limit = 1, $offset = 0)
	{
		if ($field != '') {
			$this->field = $field;
		}

		$this->db->select($this->field);

		if (is_array($where)) {
			foreach ($where as $k => $v) {
				$this->db->where($k, $v);
			}
		}

		if (is_array($like)) {
			foreach ($like as $k => $v) {
				$this->db->like($k, $v);
			}
		}

		if (is_array($join)) {
			foreach ($join as $k => $v) {
				$this->db->join($k, $v[0], $v[1]);
			}
		}
		/*
		if(is_array($order)){
			$this->db->order_by($order[0], $order[1]);
		}
		*/
		if (is_array($order)) {
			foreach ($order as $k => $v) {
				$this->db->order_by($k, $v);
			}
		}

		if ($group != '') {
			$this->db->group_by($group);
		}

		if ($limit != '' || $offset != '') {
			$this->db->limit($limit, $offset);
		}

		$q = $this->db->get($table);
		if ($q->num_rows() > 0) {
			return $q->result();
		} else {
			return FALSE;
		}
	}

	function search($table, $field, $where, $like, $wherespc, $join, $order, $group, $limit = 1, $offset = 0)
	{
		if ($field != '') {
			$this->field = $field;
		}

		$this->db->select($this->field);

		if (is_array($where)) {
			foreach ($where as $k => $v) {
				$this->db->where($k, $v);
			}
		}

		if (is_array($like)) {
			foreach ($like as $k => $v) {
				$this->db->like($k, $v);
			}
		}

		if ($wherespc != "") {
			$this->db->where($wherespc);
		}

		if (is_array($join)) {
			foreach ($join as $k => $v) {
				$this->db->join($k, $v[0], $v[1]);
			}
		}

		if (is_array($order)) {
			$this->db->order_by($order[0], $order[1]);
		}

		if ($group != '') {
			$this->db->group_by($group);
		}

		if ($limit != '' || $offset != '') {
			$this->db->limit($limit, $offset);
		}

		$q = $this->db->get($table);
		return $q->result();
	}

	function insert($table, $data)
	{
		$this->db->insert($table, $data);
		return $this->db->insert_id();
	}

	function update($table, $where, $data)
	{
		if (is_array($where)) {
			foreach ($where as $k => $v) {
				$this->db->where($k, $v);
			}
		}

		$this->db->update($table, $data);
	}

	function update2($table, $where, $or_where, $data)
	{
		if (is_array($where)) {
			foreach ($where as $k => $v) {
				$this->db->where($k, $v);
			}
		}

		if (is_array($or_where)) {
			foreach ($or_where as $k => $v) {
				$this->db->or_where($k, $v);
			}
		}

		$this->db->update($table, $data);
	}
	/*
	function delete($table, $where){
		if(is_array($where)){
			foreach($where as $k=>$v){
				$this->db->where($k, $v);
			}
		}
		
		$this->db->delete($table);
	}*/

	//mencari semua record berdasarkan tabel aja
	public function msr($table, $field, $sb)
	{
		$this->db->order_by($field, $sb);
		return $this->db->get($table);
	}

	public function msrgp($table, $field, $sb, $gb)
	{
		$this->db->group_by($gb);
		$this->db->order_by($field, $sb);
		return $this->db->get($table);
	}

	public function msrpag($table, $field, $sb, $limit, $offset)
	{
		$this->db->order_by($field, $sb, $limit, $offset);
		return $this->db->get($table);
	}

	//mencari semua record berdasarkan kondisi 
	public function selwhere($table, $sel, $com, $field, $sb)
	{
		$this->db->select($sel);
		$this->db->order_by($field, $sb);
		return $this->db->get_where($table, $com);
	}

	public function msrwhere($table, $com, $field, $sb)
	{
		$this->db->order_by($field, $sb);
		return $this->db->get_where($table, $com);
	}

	public function msrwheregp($table, $com, $field, $sb, $gb)
	{
		$this->db->group_by($gb);
		$this->db->order_by($field, $sb);
		return $this->db->get_where($table, $com);
	}

	public function msrwherepag($table, $com, $field, $sb, $limit, $offset)
	{
		$this->db->order_by($field, $sb);
		return $this->db->get_where($table, $com, $limit, $offset);
	}

	public function msrwherepaggr($table, $com, $limit, $offset, $gb)
	{
		$this->db->group_by($gb);
		return $this->db->get_where($table, $com, $limit, $offset);
	}

	//save record berdasarkan tabel
	function save($table, $set)
	{
		$this->db->insert($table, $set);
	}

	//update record bebrdasarkan tabel,field dan key
	function edit($table, $set, $field, $id)
	{
		$this->db->where($field, $id);
		$this->db->update($table, $set);
	}

	//delete record bebrdasarkan tabel,field dan key
	function delete($table, $field, $id)
	{
		$this->db->delete($table, array($field => $id));
	}

	function delete_where($table, $where)
	{
		$this->db->delete($table, $where);
	}

	//mencari semua record dengan kondisi like
	function msrseek($keyword, $table, $field)
	{
		$this->db->select('*')->from($table);
		$this->db->like($field, strtoupper($keyword), 'after');
		$query = $this->db->get();
		return $query;
	}

	function msrseek2($keyword, $table, $field, $com)
	{
		$this->db->select('*')->from($table);
		$this->db->where($com);
		$this->db->like($field, $keyword, 'after');
		$query = $this->db->get();
		return $query;
	}

	//select field dengan id
	function select_id($field, $table, $keyword)
	{
		$this->db->select($field)->from($table);
		$query = $this->db->get();
		return $query;
	}

	function msrquery($sql)
	{
		$query = $this->db->query($sql);

		return $query;
	}

	//----------------------QUERY MODIF------------------------------//
	//----------------------------------------------------------- STRI --------------------------------------------------------

	public function get_stri_member_by_id($id)
	{
		$this->db->select('*');
		$this->db->from('user_cert');
		$this->db->join('user_transfer', 'user_cert.id = user_transfer.rel_id', 'inner');
		$this->db->where('user_cert.status', 2);
		$this->db->where('user_transfer.id', $id);

		$Q = $this->db->get();
		if ($Q->num_rows() > 0) {
			$return = $Q->result();
		} else {
			$return = 0;
		}
		$Q->free_result();
		return $return;
	}

	public function cari_id_user_cert($userid, $lic_num, $ip_type, $starty)
	{
		$this->db->select('*');
		$this->db->from('user_cert');
		$this->db->where('user_id', $userid);
		$this->db->where('lic_num', $lic_num);
		$this->db->where('ip_tipe', $ip_type);
		$this->db->where(trim('startyear'), $starty);
		$query = $this->db->get();
		return $query->row();
	}

	public function insert_utrf($insert_user_tranfer)
	{
		return $this->db->insert('user_transfer', $insert_user_tranfer);
	}

	public function update_user_transfer($userid, $data_users)
	{
		$this->db->where('user_id', $userid);
		$this->db->where('pay_type', 5);
		$this->db->where('order_id', 0);
		$this->db->where('status', 1);
		$this->db->where('description', 'Daftar STRI');
		$this->db->where('vnv_status', 1);
		$this->db->where('is_upload_mandiri', 0);
		$this->db->where('remark', '');
		$this->db->where('add_doc1', '');
		$return = $this->db->update('user_transfer', $data_users);
		return $return;
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */
