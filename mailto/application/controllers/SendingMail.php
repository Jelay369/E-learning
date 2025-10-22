<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class SendingMail extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->library('form_validation');
		$this->load->library('email');

		$config = [
            'mailtype' => 'html',
            'charset'  => 'utf-8',
            'priority' => '1',
            'protocol' => 'sendmail',
            'mailpath' => '/usr/sbin/sendmail',
            'wordwrap' => TRUE
        ];

		$this->email->initialize($config);

		$this->load->model('EmailModel','em');
		$this->em->set_table_name('mail_envoye');

		$this->load->model('CampagneModel','cm');
		$this->cm->set_table_name('campagne');
		$this->load->model('TemplateModel','tm');
		$this->tm->set_table_name('template');
	}

	public function index()
	{
		$data['campagnes'] = $this->cm->getAll();
		$data['templates'] = $this->tm->getAll();
		$this->load->view('components/send_mail.php',$data);
	}
	public function list()
	{
		$this->load->model('EmailModel','em');
		$this->em->set_table_name('mail_envoye');
		$data['emails_envoye'] = $this->em->allDesc();
		$this->load->view('components/list_email_envoye',$data);
	}
	public function getContact()
	{
		$this->load->model('ContactModel','cm');
		$this->cm->set_table_name('contact');
		$contacts = $this->cm->getAll();
		echo json_encode($contacts);
	}
	public function send_first()
	{
		$type_message = htmlspecialchars(trim($this->input->post('type-message')));

		//$this->form_validation->set_rules("objet","objet","required");
		$this->form_validation->set_rules("message","message","required");

		$type_message = htmlspecialchars(trim($_POST['type-message']));
		$is_template = false;
		if($type_message === 'template'){
			$this->form_validation->set_rules("type-message","type message","required");
			$is_template = true;
		}

		if(!$this->form_validation->run())
		{
			echo json_encode(['success' => false]);
			exit();
		}


		$objet = htmlspecialchars(trim($this->input->post('objet')));
		$message = trim(nl2br($this->input->post('message')));
		//$message = htmlspecialchars(trim($this->input->post('message')));
		$email_destinataire = htmlspecialchars(trim($this->input->post('destinataire')));
		$campagne = (int)$this->input->post('campagne');

		$expediteur = htmlspecialchars(trim($this->input->post('expediteur')));

		/*------------------Pièce jointe---------------------*/
		$piece_file = $_FILES['piece-jointe'];
		$name_file = null;
		if(($piece_file['name'] !== "" && $piece_file['size'] > 0))
		{
			$extension = strtolower(pathinfo($piece_file['name'],PATHINFO_EXTENSION));
			$name_file = time().'.'.$extension;
			if(move_uploaded_file($piece_file['tmp_name'], 'public/tmp/'.$name_file))
			{
				if($this->startSending($type_message,$expediteur,$email_destinataire,$objet,$message,$name_file,$is_template))
				{
					$_SESSION['piece-jointe'] = $name_file;
					$this->saveMail([microtime(),$email_destinataire,$objet,$message,$name_file,$campagne]);
					echo json_encode(['success' => true]);
				}
				else
				{
					echo json_encode(['success' => false]);
				}
			}
		}else{
			if($this->startSending($type_message,$expediteur,$email_destinataire,$objet,$message,$name_file,$is_template))
			{
				$this->saveMail([microtime(),$email_destinataire,$objet,$message,$name_file,$campagne]);
				echo json_encode(['success' => true]);
			}
			else
			{
				echo json_encode(['success' => false]);
			}
		}
	}
	public function send_rest()
	{

		$type_message = htmlspecialchars(trim($this->input->post('type-message')));

		//$this->form_validation->set_rules("objet","objet","required");
		$this->form_validation->set_rules("message","message","required");
		$is_template = false;
		if($type_message === 'template'){
			$this->form_validation->set_rules("type-message","type message","required");
			$is_template = true;
		}

		if(!$this->form_validation->run())
		{
			echo json_encode(['error' => 'Le champs message ne doit pas être vide']);
			exit();
		}

		$objet = htmlspecialchars(trim($this->input->post('objet')));
		$message = trim(nl2br($this->input->post('message')));//htmlspecialchars(trim($this->input->post('message')));
		$email_destinataire = htmlspecialchars(trim($this->input->post('destinataire')));
		$index_email = (int)$this->input->post('index-email');
		$nbre_contact = (int)$this->input->post('nbre-contact');
		$campagne = (int)$this->input->post('campagne');

		$expediteur = htmlspecialchars(trim($this->input->post('expediteur')));

		/*------------------Pièce jointe---------------------*/
		$name_file = null;
		if(isset($_SESSION['piece-jointe']))
		{
			$name_file = $_SESSION['piece-jointe'];
		}

		if($this->startSending($type_message,$expediteur,$email_destinataire,$objet,$message,$name_file,$is_template))
		{
			if($index_email === $nbre_contact){
				unset($_SESSION['piece-jointe']);
			}
			$this->saveMail([microtime(),$email_destinataire,$objet,$message,$name_file,$campagne]);
			echo json_encode(['success' => true,'index' => $index_email]);
		}
		else
		{
			echo json_encode(['success' => false]);
		}

	}

	public function getContentText()
	{
		$file = $_FILES['destinataire-text'];
		$file_extension = strtolower(pathinfo($file['name'],PATHINFO_EXTENSION));
		if($file_extension !== 'txt'){
			echo json_encode(['error' => 'Fichier non autorisé']);
			exit();
		}

		$this->load->model('ContactModel','ctm');
		$this->ctm->set_table_name('contact');
		$all_contacts_email = $this->ctm->getAllEmail();

		$final_name = time().".".$file_extension;
		if(move_uploaded_file($file['tmp_name'], 'public/tmp/'.$final_name ))
		{
			if($txt_string = file_get_contents('public/tmp/'.$final_name)){

				unlink('public/tmp/'.$final_name);
				$contacts = explode("\n", $txt_string);
				foreach($contacts as $contact)
				{
					if(!in_array($contact,$all_contacts_email))
					{
						$this->ctm->create(['email_contact'],[trim($contact)]);
					}
				}

				echo json_encode(['success' => true,'contacts' => $contacts]);
			}
		}
	}

	public function delete($id_email)
	{
		$this->em->delete(urldecode($id_email));
		echo json_encode(["success" => true]);
	}
	public function search()
	{
		$query = htmlspecialchars(trim($this->input->post('query')));
		$data['emails_envoye'] = $this->em->search($query);
		$this->load->view('components/list_email_envoye',$data);
	}
	public function tuto_info()
	{

		$objet = htmlspecialchars(trim($this->input->post('objet')));
		$message = $this->input->post('message');
		$destinataire = htmlspecialchars($_POST['destinataire']);

		$data['template'] = $this->tm->getOne('id_template',4);
		$data['objet'] = $objet;
		$data['message'] = $message;
		$data['name_logo_uploaded'] = '';
		$body = $this->load->view('template_mail',$data,TRUE);


        $this->email->to($destinataire);
        $this->email->from('tutoinfo@tuto-info.mg');
        $this->email->subject($objet);
        $this->email->message($body);

		$this->email->send();

	}
	private function startSending($type_message,$expediteur,string $destinataire,$objet,$message,$piece_jointe = null,$template = false)
	{
		$body = null;
		if($expediteur === "")
		{
			$expediteur = "contact@tuto-info.com";
		}
		if($template)
		{
			$id_template = (int)$this->input->post('id_template');
			$data['template'] = $this->tm->getOne('id_template',$id_template);
			$data['objet'] = $objet;
			$data['message'] = $message;
			$data['name_logo_uploaded'] = '';
			$body = $this->load->view('template_mail',$data,TRUE);
		}
		else
		{
			$body = $message;
		}
		$this->email->clear();

        $this->email->to($destinataire);
        $this->email->from($expediteur);
        $this->email->subject($objet);
        $this->email->message($body);
        if($piece_jointe)
        {
        	$this->email->attach('public/tmp/' . $piece_jointe);
        }
        if(!$this->email->send())
        {
        	return false;
        }
        return true;
	}
	private function saveMail(array $values)
	{
		$this->em->create(["id_mail_envoye","destinataire_mail","objet_mail","message_mail","piece_jointe_mail","campagne_mail"],$values);
	}
}
