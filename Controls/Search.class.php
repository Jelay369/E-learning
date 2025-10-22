<?php
class Search
{

	public function index()
	{
		if (isset($_POST['search'])) {
			$offsetFormation = 0;
			$iFormation = 1;
			$offsetBlog = 0;
			$iBlog = 1;

			$query = htmlspecialchars(trim($_POST['search']));
			$data['search'] = $query;


			$formation = new FormationModel();
			$etudiant = new EtudiantModel();
			$data['formations'] = $formation->allWithOffsetFrontSearch($offsetFormation, $query);
			$data['formation_number'] = ceil(count($formation->allFrontSearch($query)) / 6);
			$data['formation_page'] = $iFormation;


			$categorie = new CategoryModel();
			$contact = new FrontModel("contact");
			$data['categories'] = $categorie->all();
			$data['contact'] = $contact->getAll();

			$home = new GeneralesModel("home");
			$home_data = $home->getLastData();
			if ($home_data === null) {
				$home->createHome();
			}
			$data['home'] = $home_data;


			$data['nombre_formation'] = ceil(count($formation->all()));
			$formation = new FrontModel("formation");
			$totale = $formation->get_nombre_totale_inscrits();
			$data['nombre_etudiant'] = $totale[0]->somme;
			Controllers::loadView("search.php", $data);
		} else {
			header('Location: ' . BASE_URL);
			exit();
		}
	}

	public function formation($text = null, $number = null)
	{

		/*//echo BASE_URL." " ;
		$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' 
              || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
		$host = $_SERVER['HTTP_HOST'];
		$request = $_SERVER['REQUEST_URI'];
		$url_complet = "".$protocol."".$host."".$request ;
		
		// remplacement 
		$url_actu = str_replace(BASE_URL,"",$url_complet) ;
		
		$nblink = count(explode("/",$url_actu)) ;*/
		
		//echo "".$protocol."".$host."".$request ;
		if ($text!=null) {

			//echo "OK" ;
			$offsetFormation = 0;
			$iFormation = 1;
			$offsetBlog = 0;
			$iBlog = 1;

			$query = htmlspecialchars(trim($text));
			$query = urldecode($query);
			$query = str_replace("-", " ", $query);
			if (!ctype_digit((string)$number) || (int)$number < 1) {
				Controllers::loadView("error.php");
				exit ;
			}
			
			$iFormation = $number;
			$offsetFormation = ($iFormation - 1) * 6;

			$data['search'] = $query;

			$formation = new FormationModel();
			$etudiant = new EtudiantModel();
			$data['formations'] = $formation->allWithOffsetFrontSearch($offsetFormation, $query);
			$data['formation_number'] = ceil(count($formation->allFrontSearch($query)) / 6);


			$data['formation_page'] = $iFormation;


			$categorie = new CategoryModel();
			$contact = new FrontModel("contact");
			$data['categories'] = $categorie->all();
			$data['contact'] = $contact->getAll();

			$home = new GeneralesModel("home");
			$home_data = $home->getLastData();
			if ($home_data === null) {
				$home->createHome();

			}
			$data['home'] = $home_data;


			$data['nombre_formation'] = ceil(count($formation->all()));
			$formation = new FrontModel("formation");
			$totale = $formation->get_nombre_totale_inscrits();
			$data['nombre_etudiant'] = $totale[0]->somme;
			Controllers::loadView("search.php", $data);
			

		} else {

			header('Location: ' . BASE_URL);
			exit();
		}

	}

}
