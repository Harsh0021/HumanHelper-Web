<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Refund_policy extends CI_Controller {
	
	public function index()
	{
		$this->load->view('refund_policy');
	}
}
?>
