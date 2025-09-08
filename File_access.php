<?php
defined('BASEPATH') or exit('No direct script access allowed');

class File_access extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();
		$this->load->library('session');
	}

	/**
	 * Download file ID Card aman
	 * @param string $filename Nama file lengkap (misal: 1649068985_KTP_4400.jpeg)
	 */
	public function download_idcard($filename = '')
	{
		// Cek user login
		
		

		// Cegah path traversal
		$filename = basename($filename);

		// Path file di server
		$filepath = FCPATH . '/assets/uploads/' . $filename;

		if (file_exists($filepath)) {
			// Tentukan MIME type
			$mime = mime_content_type($filepath);

			// Header download
			header('Content-Description: File Transfer');
			header('Content-Type: ' . $mime);
			header('Content-Disposition: attachment; filename="' . $filename . '"');
			header('Content-Length: ' . filesize($filepath));
			header('Cache-Control: private');

			readfile($filepath);
			exit;
		} else {
			show_404();
		}
	}
}
