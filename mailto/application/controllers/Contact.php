<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Contact extends CI_Controller {
	public function __construct()
	{
		parent::__construct();
		if(!$this->session->has_userdata('connected'))
		{
			redirect(base_url(),'auto',301);
			exit();
		}
		$this->load->model('ContactModel','cm');
		$this->cm->set_table_name('contact');
		$this->load->library('form_validation');
	}
	/**--------------GET----------------------*/
	public function add()
	{
		$this->load->view('components/add_contact');
	}
	public function list()
	{
		$data['contacts'] = $this->cm->allDesc();
		$this->load->view('components/list_contact.php',$data);
	}
	public function delete($id_contact)
	{
		$this->cm->delete((int)$id_contact);
		echo json_encode(['success'=>true]);
	}

	/**---------------POST----------------------*/
	public function create()
	{
		$this->form_validation->set_rules("email_contact","email","required|valid_email|is_unique[contact.email_contact]",[
			"required" => "L'adresse email est obligatoire",
			"valid_email" => "Adresse email invalide",
			"is_unique" => "Cet email existe déja"
		]);
		if(!$this->form_validation->run()){
			echo json_encode(['error'=>validation_errors()]);
			exit();
		}
		$fields = [];
		$values = [];
		foreach($_POST as $key=>$post)
		{
			if($key != 'id_contact')
			{
				if(!empty($post))
				{
					$fields[] = $key;
					$values[] = htmlspecialchars(trim($post));
				}
			}
		}
		$this->cm->create($fields,$values);
		echo json_encode(['success'=>true,'message'=>'Contact ajouté']);
	}
	public function update()
	{
		$this->form_validation->set_rules("email_contact","email","required|valid_email",[
			"required" => "L'adresse email est obligatoire",
			"valid_email" => "Adresse email invalide"
		]);
		if(!$this->form_validation->run()){
			echo json_encode(['error'=>validation_errors()]);
			exit();
		}
		$fields = [];
		$values = [];
		foreach($_POST as $key=>$post)
		{
			if($key !== 'id_contact'){
				if(!empty($post))
				{
					$fields[] = $key;
					$values[] = htmlspecialchars(trim($post));
				}
			}
		}
		$this->cm->update($fields,$values,"id_contact",(int)$_POST['id_contact']);
		echo json_encode(['success'=>true,'message'=>'Mise à jour effectué']);
	}
	public function create_by_CSV()
	{
		$file = $_FILES['csv'];
		$fields = "(firstname_contact,lastname_contact,email_contact,entreprise_contact,poste_contact,telephone_contact)";
		$values = "";

		if(empty($file['name']) && $file['size'] === 0 ){
			echo json_encode(['error' => 'Selectionner un fichier CSV']);
			exit();
		}
		$file_extension = strtolower(pathinfo($file['name'],PATHINFO_EXTENSION));
		if($file_extension !== 'csv'){
			echo json_encode(['error' => 'Extension de fichier non autorisé']);
			exit();
		}
		$all_contacts_email = $this->cm->getAllEmail();
		$final_name = time().".".$file_extension;
		if(move_uploaded_file($file['tmp_name'], 'public/tmp/'.$final_name ))
		{
			if($csv_string = file_get_contents('public/tmp/'.$final_name)){
				
				unlink('public/tmp/'.$final_name);
				
				$rows = explode("\n", $csv_string);
				$cols = [];

				foreach($rows as $r)
				{
					$tmp = explode(",",$r);
					$cols[] = $tmp;
				}
				foreach($cols as $key=>$col_values)
				{
					if($key > 0 && count($col_values) === count($cols[0]))
					{
						/*----Mety mbola hiova---------*/
						if(!in_array($col_values[3],$all_contacts_email))
						{
							$values .= '(';
							foreach($col_values as $k=>$value)
							{
								if($k > 0)
								{
									if($value === "")
									{
										$values .= 'NULL,';
									}
									else
									{
										$values .= '"' . trim($value) . '"' . ',';
									}
								}
							}
							$values = trim($values,',');
							$values .= '),';
						}
					}
				}
				$values = trim($values,",");
				$this->cm->createByCSV($fields,$values);
				echo json_encode(['success' => true]);
			}	
		}
	}
	public function search()
	{
		$query = htmlspecialchars(trim($this->input->post('query')));
		$contacts = $this->cm->search($query);
		echo json_encode($contacts);
	}
	
}
