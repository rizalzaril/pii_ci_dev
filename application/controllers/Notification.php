<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Notification extends CI_Controller
{
  public function __construct()
  {
    parent::__construct();
    //$this->load->model('Notification_model');
    $this->load->library('form_validation');
    // if ($this->session->userdata('role_id') != '1') {
    //   redirect('auth/block');
    // }
  }

  public function index()
  {
    $data['title'] = 'Notification List';
    //$data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
    //$data['notifications'] = $this->Notification_model->getAllNotifications();
    $this->load->view('/header', $data);
    $this->load->view('/notification', $data);
    $this->load->view('/footer');
  }

  public function get_notification()
  {
    $query = $this->db->order_by('id', 'DESC')
      // ->where('is_read', 0)
      ->get('notifications'); // batasi hanya 10 notifikasi terbaru
    echo json_encode($query->result());
  }


  public function add_notification_view()
  {
    $data['title'] = 'Add Notification';
    //$data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
    $this->load->view('/header', $data);
    $this->load->view('/add_notification', $data);
    $this->load->view('/footer');
  }


  public function insert_notification()
  {

    $user_id = $this->input->post('user_id');
    $title_notif = $this->input->post('title');
    $message = $this->input->post('message');
    $data = [
      'user_id' => $user_id,
      'title' => $title_notif,
      'message' => $message,
      'created_at' => date('Y-m-d H:i:s'),
      'is_read' => 0
    ];

    $this->db->insert('notifications', $data);
    echo "Notification pushed.";
  }


  public function mark_as_read($id = null)
  {

    $id = $this->input->post('id') ?? $id;

    if (!$id) {
      echo json_encode(['status' => 'error', 'message' => 'No notification ID provided.']);
      return;
    }



    if ($id) {
      $this->db->where('id', $id);
      $this->db->update('notifications', ['is_read' => 1]);

      echo json_encode(['status' => 'success', 'message' => 'Notification marked as read.']);
    } else {
      echo json_encode(['status' => 'error', 'message' => 'No notification ID provided.']);
    }


    if ($this->db->affected_rows() > 0) {
      echo json_encode([
        "status" => "success",
        "message" => "Notification marked as read."
      ]);
    } else {
      echo json_encode([
        "status" => "error",
        "message" => "Notification not found or already read."
      ]);
    }
  }
}
