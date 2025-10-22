<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Template extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('TemplateModel','tm');
		$this->tm->set_table_name('template');
		$this->load->library('form_validation');
	}

	public function index()
	{
		$this->load->view('components/add_template');
	}
	public function list()
	{
		$data['templates'] = $this->tm->getAll();
		$this->load->view('components/list_template',$data);
	}
	public function create()
	{
		$fields = [];
		$values = [];
		$this->form_validation->set_rules('name_template','name','required');
		if(!$this->form_validation->run())
		{
			echo json_encode(['error' => 'Le champ nom est obligatoire']);
			exit();
		}
		foreach( $_POST as $field=>$post )
		{
			if(!empty(htmlspecialchars(trim($post))) && $field !== 'id_template' )
			{
				$fields[] = $field;
				$values[] = htmlspecialchars(trim($post));
			}
		}
		/*----------------Traitement du fichier-----------------*/
		$file = $_FILES['logo_template'];
		if(!empty($file['name']) && $file['size'] > 0)
		{
			$extension = strtolower(pathinfo($file['name'],PATHINFO_EXTENSION));
			if(!in_array($extension,['jpg','png','jpeg','gif']))
			{
				echo json_encode(['error' => 'Fichier non autorisé']);
				exit();
			}
			$filename = time().'.'.$extension;
			if(move_uploaded_file($file['tmp_name'], 'public/img/'.$filename))
			{
				$fields[] = 'logo_template';
				$values[] = $filename;
			}
		}

		$this->tm->create($fields,$values);
		echo json_encode(['success'=>true]);
	}

	public function update()
	{
		$fields = [];
		$values = [];
		$this->form_validation->set_rules('name_template','name','required');
		if(!$this->form_validation->run())
		{
			echo json_encode(['error' => 'Le champ nom est obligatoire']);
			exit();
		}
		foreach( $_POST as $field=>$post )
		{
			if(!empty(htmlspecialchars(trim($post))) && $field !== 'id_template' )
			{
				$fields[] = $field;
				$values[] = htmlspecialchars(trim($post));
			}
		}
		/*----------------Traitement du fichier-----------------*/
		$file = $_FILES['logo_template'];
		if(!empty($file['name']) && $file['size'] > 0)
		{
			$extension = strtolower(pathinfo($file['name'],PATHINFO_EXTENSION));
			if(!in_array($extension,['jpg','png','jpeg','gif']))
			{
				echo json_encode(['error' => 'Fichier non autorisé']);
				exit();
			}
			$filename = time().'.'.$extension;
			if(move_uploaded_file($file['tmp_name'], 'public/img/'.$filename))
			{
				$fields[] = 'logo_template';
				$values[] = $filename;
			}
		}
		$this->tm->update($fields,$values,'id_template',(int)$this->input->post('id_template'));
		echo json_encode(['success'=>true]);
	}

	public function delete($id_template)
	{
		$this->tm->delete((int)$id_template);
		echo json_encode(['success'=>true]);
	}

	public function preview()
	{	
		$file = $_FILES['logo_template'];
		$data['objet'] = "Objet du message";
		$data['message'] = "Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,
quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse
cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non
proident, sunt in culpa qui officia deserunt mollit anim id est laborum.";

		$data['template'] = (object)[
			'couleur_fond_template' => $_POST['couleur_fond_template'],
			'couleur_header_template' => $_POST['couleur_header_template'],
			'telephone_template' => $_POST['telephone_template'],
			'facebook_template' => $_POST['facebook_template'],
			'twitter_template' => $_POST['twitter_template'],
			'linkedin_template' => $_POST['linkedin_template'],
			'youtube_template' => $_POST['youtube_template'],
			'site_web_template' => $_POST['site_web_template']
		];

		if(empty($file['name']) && $file['size'] === 0)
		{
			$data['template']->logo_template = 'logo.png';
			$data['name_logo_uploaded'] = 'null';
		}
		else
		{
			$extension = strtolower(pathinfo($file['name'],PATHINFO_EXTENSION));
			if(!in_array($extension,['jpg','png','jpeg','gif']))
			{
				echo json_encode(['error' => 'Fichier non autorisé']);
				exit();
			}
			if($file['size'] > 10485760)
			{
				echo json_encode(['error' => 'Fichier trop lourd']);
				exit();
			}
			$filename = time().".".$extension;
			if(move_uploaded_file($file['tmp_name'], 'public/img/'.$filename ))
			{
				$data['template']->logo_template = $filename;
				$data['name_logo_uploaded'] = $filename;
			}
    		
			
		}

		$this->load->view('template_mail',$data);
		
	}
	public function delete_logo_tmp($filename = "null")
	{
		if(file_exists('public/img/'. $filename))
		{
			unlink('public/img/'. $filename);
		}
	}
}