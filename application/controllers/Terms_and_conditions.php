<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Terms_and_conditions extends CI_Controller {

	public function index()
	{
		$this->load->view('termcondition');
	}
}
