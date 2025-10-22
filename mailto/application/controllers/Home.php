<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends CI_Controller {
	public function __construct()
	{
		parent::__construct();
		if(!$this->session->has_userdata('connected'))
		{
			redirect(base_url(),'auto',301);
			exit();
		}
	}
	public function index()
	{
		$this->load->view('index');
	}
}
