<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('UserModel','um');
		$this->um->set_table_name('user');
	}
	public function index()
	{
		$this->load->view('login');
		if(!empty($_POST))
		{
			
			//redirect(base_url('Home'));
		}
	}
	public function connection()
	{
		$user = $this->um->getOne('id_user',1);
		$post_username = $this->input->post('username');
		$post_password = $this->input->post('password');
		if($user->username !== $post_username || !password_verify($post_password,$user->password))
		{
			echo json_encode(['success' => false]);
			exit();
		}
		$session_data = [
			"id_user" => $user->id_user,
			"connected" => true
		];
		$this->session->set_userdata($session_data);
		echo json_encode(['success' => true]);
	}
	public function logout()
    {
    	$array_items = ['id_user','connected'];
    	$this->session->unset_userdata($array_items);
    	session_destroy();
    	redirect(base_url());
    }

}
