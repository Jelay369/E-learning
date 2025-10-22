<?php
class Front
{

	public function newsLetter()
	{
		$validation = new FormValidation();
		$validation->requiredAll($_POST)
		->email($_POST['email']);
		if (!$validation->run()) {
			echo json_encode($validation->getErrors());
			exit();
		}

		$front = new FrontModel('newsletter');
		$username = htmlspecialchars(trim($_POST['username']));
		$email = htmlspecialchars(trim($_POST['email']));
		$front->createNewsLetter($username, $email);
		echo json_encode(['success' => true]);
	}
	
	public function updateContact()
	{
		$validation = new FormValidation();
		$validation->requiredAll($_POST);
		if (isset($_POST['email'])) {
			$validation->email($_POST['email']);
		}
		if (!$validation->run()) {
			echo json_encode($validation->getErrors());
			exit();
		}
		$front = new FrontModel('contact');
		$front->updateContact($_POST);
		echo json_encode(["success" => true]);
	}
	
	public function updateTitle()
	{
		$validation = new FormValidation();
		$validation->requiredAll($_POST);
		if (!$validation->run()) {
			echo json_encode($validation->getErrors());
			exit();
		}
		$front = new FrontModel('title');
		$front->updateTitle($_POST);
		echo json_encode(["success" => true]);
	}

	public function message()
	{
		$validation = new FormValidation();
		$validation->requiredAll($_POST);
		if (!$validation->run()) {
			echo json_encode("Veuillez remplir tous les champs !");
			exit();
		}

		$validation->email($_POST['email']);
		if (!$validation->run()) {
			echo json_encode("Mail invalide");
			exit();
		}
		$fields = [];
		$values = [];
		foreach ($_POST as $key => $post) {
			$fields[] = $key;
			$values[] = htmlspecialchars(trim($post));
		}
		$front = new FrontModel('message');
		$front->addMessage($fields, $values);
		echo json_encode(['success' => true]);
	}

	public function filter()
	{
		$formation = new FormationModel();
		$q = trim($_POST['q']);
		$qs = (int)trim($_POST['qs']);
		$data = null;
		if ($q === '') {
			if ($qs !== 0) {
				$data = $formation->getByCategory($qs);
			} else {
				$data = $formation->all();
			}
		} else {
			if ($qs === 0) {
				$data = $formation->getByQ($q);
			} else {
				$data = $formation->filterByCategory($qs, $q);
			}
		}
		echo json_encode($data);
	}

	public function getData()
	{
		$data = null;
		$home = new GeneralesModel("home");
		$section = new GeneralesModel("section");
		$contact = new FrontModel("contact");
		$category = new CategoryModel();
		$formations = new FormationModel();
		$formations_gratuit = new FormationModel();
		$formations_payant = new FormationModel();
		$blog = new BlogModel();
		$etudiant = new EtudiantModel();
		$front = new FrontModel('title');
		$data['title'] = $front->getAllTitle()[0];

		$home_data = $home->getLastData();

		if ($home_data === null) {
			$home->createHome();
		}

		$data['home'] = $home_data;
		$data['sections'] = $section->getDataWithLimit(3);
		$data['contact'] = $contact->getAll();
		$data['categories'] = $category->all();

		$data['formations_gratuit'] = $formations_gratuit->allTypeWithOffsetFront('gratuit', 0);
		$data['formation_number_gratuit'] = ceil(count($formations_gratuit->all()) / 6) ;

		$data['formations_payant'] = $formations_payant->allTypeWithOffsetFront('payant', 0);
		$data['formation_number_payant'] = ceil(count($formations_payant->all()) / 6) ;

		$data['formation_page'] = 1 ;
		
		$data['blogs'] = $blog->allWithOffsetFront(0);
		$data['blog_number'] = ceil(count($blog->all()) / 6) ;
		$data['blog_page'] = 1 ;

		$data['nombre_formation'] = ceil(count($formations->all()));
		$data['nombre_etudiant'] = $this->get_nombre_totale_inscrits();
		
		return $data;
	}

	public static function get_nombre_totale_inscrits()
	{
		$formation = new FrontModel("formation");
		$totale = $formation->get_nombre_totale_inscrits();
		return $totale[0]->somme;
	}
}
