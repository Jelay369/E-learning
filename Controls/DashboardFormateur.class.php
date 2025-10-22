<?php
class DashboardFormateur
{

	private $cours;
	private $formation;
	private $fem;
    private $qcm;

	public function __construct()
	{
		if (!isset($_SESSION["connected"]) || $_SESSION['role'] !== ROLE_USER[1]) {
			header('Location:' . BASE_URL);
			exit();
		}

		$this->cours = new CoursModel();
		$this->formation = new FormationModel();
		$this->fem = new FormationEtudiantModel();
        $this->qcm = new QcmModel();
	}
	
	
	public function index()
	{
		$formateur = $_SESSION['id'];
		$data['allCours'] = $this->cours->getCoursByFormateur($formateur);
		$data['formations'] = $this->formation->getFormationByFormateur($formateur);
		Controllers::loadView("formateur/dashboard.php", $data);
	}
	public function listCours()
	{
		$formateur = $_SESSION['id'];
		$data['formations'] = $this->formation->getFormationByFormateur($formateur);
		$data['allCours'] = $this->cours->getCoursByFormateur($formateur);
		Controllers::loadView("formateur/components/list_cours.php", $data);
	}
	public function getAllCours($idFormation)
	{
		$data["numeros"] = $this->cours->getCodeChapitre((int)$idFormation);
		Controllers::loadView("formateur/components/option_cours.php", $data);
	}
	public function getTable($idFormation)
	{
		$idFormation = (int)$idFormation;
		$formateur = $_SESSION['id'];
		$data = null;

		if ($idFormation <= 0) {
			$data['allCours'] = $this->cours->getCoursByFormateur($formateur);
		} else {
			$data['allCours'] = $this->cours->getByFormationAndFormateur($idFormation, $formateur);
		}
		Controllers::loadView("formateur/components/table_cours.php", $data);
	}
	public function getTableEleve()
	{
		$idFormation = (int)$_POST["formation"];
		$formateur = $_SESSION['id'];
		$query = htmlspecialchars(trim($_POST["query"]));
		$data = null;

		if ($idFormation <= 0 && $query === "") {
			$data['eleves'] = $this->fem->getEleves($formateur);
		} else {
			if ($query === "") {
				$data['eleves'] = $this->fem->getElevesByFormation($formateur, $idFormation);
			} elseif ($idFormation <= 0) {
				$data['eleves'] = $this->fem->getElevesByNameOrMatricule($formateur, "%" . $query . "%");
			} else {
				$data['eleves'] = $this->fem->getSearchEleves($formateur, $idFormation, "%" . $query . "%");
			}
		}
		Controllers::loadView("formateur/components/table_eleve.php", $data);
	}

	public function getTableCommission()
	{
		$idFormation = (int)$_POST["formation"];
		$formateur = $_SESSION['id'];
		$query = htmlspecialchars(trim($_POST["query"]));
		$data = null;

		if ($idFormation <= 0 && $query === "") {
			$data['eleves'] = $this->fem->getEleves($formateur);
		} else {
			if ($query === "") {
				$data['eleves'] = $this->fem->getElevesByFormation($formateur, $idFormation);
			} elseif ($idFormation <= 0) {
				$data['eleves'] = $this->fem->getElevesByNameOrMatricule($formateur, "%" . $query . "%");
			} else {
				$data['eleves'] = $this->fem->getSearchEleves($formateur, $idFormation, "%" . $query . "%");
			}
		}
		Controllers::loadView("formateur/components/table_commission.php", $data);
	}

	public function addCours($idCours = null)
	{
		$formateur = $_SESSION['id'];
		$data['numeros'] = [];
		$data['formations'] = $this->formation->getFormationByFormateur($formateur);

		if (is_null($idCours)) {
			if (!empty($data['formations'])) {
				$first = $data['formations'][0]->id;
				$data['numeros'] = $this->cours->getCodeChapitre($first);
				$data["num"] = ((int)$this->cours->getLastCodeChapitre($first)) + 1;
			}
			$data['cours'] = false;
		} else {
			$course = $this->cours->getOne($idCours);
			$data['cours'] = $course;
			$data['numeros'] = $this->cours->getCodeChapitre($course->idFormation);
			$data["num"] = (int)$course->code_chapitre;
		}
		Controllers::loadView("formateur/components/add_cours.php", $data);
	}
	public function eleve()
	{
		$formateur = $_SESSION['id'];
		$data['formations'] = $this->formation->getFormationByFormateur($formateur);
		$data['eleves'] = $this->fem->getEleves($formateur);
		Controllers::loadView("formateur/components/eleves.php", $data);
	}
	public function commission() 
	{
		$formateur = $_SESSION['id'];
		$data['formations'] = $this->formation->getFormationByFormateur($formateur);
		$data['eleves'] = $this->fem->getEleves($formateur);
		Controllers::loadView("formateur/components/commission.php", $data);
	}
	public function profil()
	{
		$formateur = new FormateurModel();
		$data["profil"] = $formateur->getOne($_SESSION['id']);
		Controllers::loadView("formateur/components/profil.php", $data);
	}
	public function addQCM()
	{
		$formateur = $_SESSION['id'];
		$data['formations'] = $this->formation->getFormationByFormateur($formateur);
		Controllers::loadView("formateur/components/qcm.php", $data);
	}

	public function see_student_profil()
	{
		$formateur = $_SESSION['id'];
		$profil = $this->fem->see_student_profil($formateur, $_POST['matricule']);
		echo json_encode(['success' => $profil]);
	}
	
	
	

    public function allqcm()
    {
        if (!isset($_SESSION["connected"]) || $_SESSION['role'] === ROLE_USER[0]) {
                //Controllers::loadView("login.php");
            header('Location:' . BASE_URL);
            exit();
        }

		$formateur = $_SESSION['id'];
        $data['formations'] = $this->formation->all();
        $data['allQCM'] = $this->qcm->allForFormateur($formateur);
        Controllers::loadView("formateur/components/qcmList.php", $data);
    }
}
