<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Campagne extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		if(!$this->session->has_userdata('connected'))
		{
			redirect(base_url(),'auto',301);
			exit();
		}
		$this->load->library('form_validation');
		$this->load->model('CampagneModel',"cm");
		$this->cm->set_table_name('campagne');
	}
	public function index()
	{
		$this->load->view('components/campagne');
	}
	public function list()
	{
		$data['campagnes'] = $this->cm->getAll();
		$this->load->view('components/list_campagne',$data);
	}
	public function create()
	{
		$this->form_validation->set_rules('name_campagne','name','required',[
			'required' => 'Le champ nom de la campagne est obligatoire'
		]);
		$this->form_validation->set_rules('debut_campagne','debut','required',[
			'required' => 'Le champ début de la campagne est obligatoire'
		]);
		$this->form_validation->set_rules('fin_campagne','fin','required',[
			'required' => 'Le champ fin de la campagne est obligatoire'
		]);

		if(!$this->form_validation->run())
		{
			echo json_encode(['error'=>validation_errors()]);
			exit();
		}
		$fields = [];
		$values = [];
		foreach($_POST as $key=>$post)
		{
			if($key != 'id_campagne')
			{
				if(!empty($post))
				{
					$fields[] = $key;
					$values[] = htmlspecialchars(trim($post));
				}
			}
		}
		$this->cm->create($fields,$values);
		echo json_encode(['success'=>true,'message'=>'Campagne ajouté']);
	}
	public function update()
	{
		$this->form_validation->set_rules('name_campagne','name','required',[
			'required' => 'Le champ nom de la campagne est obligatoire'
		]);
		$this->form_validation->set_rules('debut_campagne','debut','required',[
			'required' => 'Le champ début de la campagne est obligatoire'
		]);
		$this->form_validation->set_rules('fin_campagne','fin','required',[
			'required' => 'Le champ fin de la campagne est obligatoire'
		]);

		if(!$this->form_validation->run())
		{
			echo json_encode(['error'=>validation_errors()]);
			exit();
		}
		$fields = [];
		$values = [];
		foreach($_POST as $key=>$post)
		{
			if($key != 'id_campagne')
			{
				if(!empty($post))
				{
					$fields[] = $key;
					$values[] = htmlspecialchars(trim($post));
				}
			}
		}
		$this->cm->update($fields,$values,'id_campagne',(int)$this->input->post('id_campagne'));
		echo json_encode(['success'=>true,'message'=>'Mise à jour effectué']);
	}
	public function delete($id_campagne)
	{
		$this->cm->delete((int)$id_campagne);
		echo json_encode(['success'=>true]);
	}
}
