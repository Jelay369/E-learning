<?php
class Connection
{

	public function index()
	{
		Controllers::loadView("login.php");
	}

	public function login()
	{

		session_unset();
		if(empty(trim($_POST["username"])) || empty($_POST["password"]) )
		{
			echo json_encode(["success" => false,"required" => "Tous les champs sont obligatoires"]);
			exit();
		}
		$cm = new ConnectionModel();
		$user = $cm->verifyUser( trim($_POST['username']) , $_POST['password'] );
		if($user){
			$_SESSION["connected"] = true;
			$_SESSION['id'] = $user->id;
			$_SESSION['role'] = ROLE_USER[2];
			echo json_encode(["success" => true]);
		}
		else{
			echo json_encode(["success" => false]);
		}
	}



	public function formateur()
	{
		Controllers::loadView("formateurLogin.php");
	}

	public function formateurLogin()
	{
		session_unset();
		$formateurModel = new FormateurModel();
		$formateur = $formateurModel->getOne(htmlspecialchars(trim($_POST["username"])));
		if($formateur)
		{
			if(password_verify(htmlspecialchars($_POST["password"]),$formateur->passwordFormateur))
			{
				$_SESSION["connected"] = true;
				$_SESSION["id"] = $formateur->idFormateur;
				$_SESSION["role"] = ROLE_USER[1];
				$fm = new FormateurModel();
				$fm->update(['isConnected'],[1],$formateur->idFormateur);

				$messenger = new MessengerModel();
				$pieces = $messenger->getFilsToDeleteFormateur($formateur->idFormateur);
				$destination= dirname(__DIR__).DIRECTORY_SEPARATOR."Publics".DIRECTORY_SEPARATOR."upload".DIRECTORY_SEPARATOR."messenger".DIRECTORY_SEPARATOR;
				foreach ($pieces as $key => $piece) {
					$to_delete = $piece->pieceJointe;
					FileManager::remove_file($destination . $to_delete);
					$messenger->deletePiecesJoint($piece->idMessage);
				}
				echo json_encode(["success"=>true]);
			}
			else{
				echo json_encode(["error"=>true]);
			}
		}else{
			echo json_encode(["error"=>true]);
		}
	}

	public function logout()
	{
		
		if($_SESSION['role'] === ROLE_USER[1]){
			$fm = new FormateurModel();
			$fm->update(['isConnected'],[0],$_SESSION['id']);
		}
		if($_SESSION['role'] === ROLE_USER[0]){
    		$em = new EtudiantModel();
    		$em->update(['isConnected', 'ip'],[0, null],$_SESSION['matricule']);
		}

		session_destroy();

		header('Location:'.BASE_URL);
	}



	public function partenaire()
	{
		Controllers::loadView("partenaire/partenaireLogin.php");
	}
	
	public function partenaireLogin()
	{
		session_unset();
		$partenaireModel = new PartenaireModel();
		$partenaire = $partenaireModel->getOneForConnection(htmlspecialchars(trim($_POST["username"])));

		if($partenaire)
		{
			$partenaire = $partenaire[0];
			if(password_verify($_POST["password"], $partenaire->password))
			{
				$_SESSION["connected"] = true;
				$_SESSION["id"] = $partenaire->idPartenaire;
				$_SESSION["role"] = ROLE_USER[3];

				echo json_encode(["success"=>true]);
			}
			else{
				echo json_encode(["error"=>true]);
			}
		}
		else{
			echo json_encode(["error"=>true]);
		}

	}
	
}