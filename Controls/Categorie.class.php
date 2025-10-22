<?php
class Categorie
{
	
	public function index($name = null, $text = null, $page = null)
	{
		if(is_null($name)){
			header("location:" . BASE_URL);
		}

		$offset = 0;
		$i = 1;

		
		if (isset($text)) {
			if(is_null($page)){
				$offset = 0; 
			}else{
				$i = $page;
				$offset = ($i - 1) * 6;
			}
		}

		$title = urldecode($name);
		$title = str_replace("-", " ", $title);

		$categorie = new CategoryModel();
		$category = $categorie->getWithName($title);
		$idCategory = $category->idCategory;

		$contact = new FrontModel("contact");
		$formation = new FormationModel();
		$front = new FrontModel('title');
		$etudiant = new EtudiantModel();
		$data['title'] = $front->getAllTitle()[0];
		$data['categorie'] = $category;
		$data['categories'] = $categorie->getAllNotSeen($idCategory);
		$data['contact'] = $contact->getAll();
		
		
		
		if(isset($text) && $text != 'page')
		{
			$formations = $formation->geTypeByCategoryWithOffset($text, $idCategory, $offset);
			$data['formations'] = $formations;
			$data['formation_number'] = ceil(count($formation->getTypeByCategory($text, $idCategory)) / 6);
			$data['formation_page'] = $i;
			$data['typeFormation'] = $text;
		}else{
			$formations = $formation->getByCategoryWithOffset($idCategory, $offset);
			$data['formations'] = $formations;
			$data['formation_number'] = ceil(count($formation->getByCategory($idCategory)) / 6);
			$data['formation_page'] = $i;
		}
		$home = new GeneralesModel("home");
		$home_data = $home->getLastData();
		if ($home_data === null) {
			$home->createHome();
		}
		$data['home'] = $home_data;

		$data['nombre_formation'] = ceil(count($formation->all()));

		$front = new FrontModel("formation");
		$totale = $front->get_nombre_totale_inscrits();
		$data['nombre_etudiant'] = $totale[0]->somme;
		
		
		$allFormation = $formation->getByCategory($idCategory);
		$data['issetGratuit'] = false;
		foreach($allFormation as $key => $formation){
			if($formation->type == 'gratuit'){
				$data['issetGratuit'] = true;
			}
		}

		Controllers::loadView("categorie.php", $data);
	}
	
	
    private function isAdmin()
    {
        if (isset($_SESSION['id'])) {
            if ($_SESSION['role'] !== ROLE_USER[2]) {
                Controllers::loadView("error.php");
                exit();
            }
        } else {
            header('Location:' . BASE_URL . '/connection');
            exit();
        }
    }

	
    public function getAll()
    {
        $this->isAdmin();
        $category = new CategoryModel();
        $data = $category->all();
        echo json_encode($data);
    }
}
