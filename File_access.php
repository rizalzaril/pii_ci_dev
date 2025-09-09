<?php
defined('BASEPATH') or exit('No direct script access allowed');

class File_access extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
	}

	private function access_deny_msg()
	{
		return "Anda tidak memiliki akses untuk file ini.";
	}

	public function download_idcard($filename = '')
	{
		// Daftar role/akses yang diizinkan
		$akses = array("0", "1", "2", "9", "10", "11", "14", "15");

		$admin_id = $this->session->userdata('admin_id');
		$type     = $this->session->userdata('type');

		// Jika bukan type yang diizinkan
		if (!in_array($type, $akses)) {

			redirect('https://www.google.com');
			exit;
		} else {


			// --- Proses download hanya sampai sini kalau lolos validasi akses ---

			// Cegah path traversal
			$filename = basename($filename);

			// Path file di server
			$filepath = FCPATH . 'assets/uploads/' . $filename;

			if (file_exists($filepath)) {
				// Tentukan MIME type
				$mime = mime_content_type($filepath);

				header('Content-Type: ' . $mime);
				header('Content-Length: ' . filesize($filepath));
				header('Cache-Control: private');
				header('Content-Disposition: inline; filename="' . $filename . '"');

				readfile($filepath);
				exit;
			} else {
				show_404();
			}
		}
	}
}
