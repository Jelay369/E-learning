<?php
class Formation
{
	const ALLOWED_EXTENSION_VIDEO = ["mp4", "avi", "mpg", "mkv", "webm"];
	const UPLOAD_FOLDER_VIDEO = "video";
	const ALLOWED_EXTENSION_IMAGE = ["jpg", "jpeg", "png"];
	const UPLOAD_FOLDER_IMAGE = "images";

	public function index($title)
	{
		$title = urldecode($title);
		$title = str_replace("-", " ", $title);

		$db = new Database();
		$res = $db->select("formation")
		->where("title", "=")
		->execute([$title]);

		if (count($res) == 0) {
			header("location:" . BASE_URL);
		} else {
            // on vérifie que l'id existe
			$contact = new FrontModel("contact");
			$formation = new FormationModel();
			$front = new FrontModel('title');
			$data['title'] = $front->getAllTitle()[0];
			$data['formations'] = $formation->allNotSeen($res[0]->id);
			$data['contact'] = $contact->getAll();
			$data['formation'] = $formation->getOne($res[0]->id);

			if (isset($_SESSION["matricule"])) {
				$fem = new FormationEtudiantModel();
				$all = $fem->getFormationEtudiant(htmlspecialchars($_SESSION['matricule']));
				$my_formationId = [];
				foreach ($all as $formation) {
				$my_formationId[] = $formation->idFormation;
				}
				$data['my_formationsId'] = $my_formationId;
			}
			Controllers::loadView("formations.php", $data);
		}
  	}

  	public function inscription($title)
	{

		$title = urldecode($title);
		$title = str_replace("-", " ", $title);
		$db = new Database();
		$res = $db->select("formation")
		          ->where("title", "=")
		          ->execute([$title]);
		$home = new GeneralesModel("home");
		$home_data = $home->getLastData();

		if ($home_data === null) {
			$home->createHome();
		}
		if (count($res) == 0) {
			header("location:" . BASE_URL);
		} 
		else {
			$data['name'] = $res[0]->title;
			$data['id'] = $res[0]->id;
			$partenaireParametreModel = new PartenaireParametreModel();
			$data['parametre'] = $partenaireParametreModel->getOne(1)[0];
			// eto
			$data['home'] = $home_data;
			
			Controllers::loadView("inscriptionFormation.php", $data);
		}
	}

	public function getFormation(int $id)
	{
		$formation = new FormationModel();
		$data = $formation->getOne($id);
		$data->program = htmlspecialchars_decode($data->program);
		echo json_encode($data);
	}

	public function createFormation()
	{
		/*-------Controle de droit--------*/
		if (isset($_SESSION['id'])) {
		if ($_SESSION['role'] !== ROLE_USER[2]) {
			Controllers::loadView("error.php");
			exit();
		}
		} else {
		header('Location:' . BASE_URL . '/connection');
		exit();
		}
		/*---------------------------------*/

		$errors = null;
		$validation = new FormValidation();
			//  $videoController = new FileController([$_FILES['video']],self::UPLOAD_FOLDER_VIDEO);
		$imageController = new FileController([$_FILES['image']], self::UPLOAD_FOLDER_IMAGE);


			// $validation->requiredAll($_POST);
		$validation->required("title", "Ce champs est requis");
		$validation->required("idCategory", "Ce champs est requis");
		$validation->required("difficulte", "Ce champs est requis");
		$validation->required("commission", "Ce champs est requis");
		$validation->required("target", "Ce champs est requis");
		$validation->required("prerequis", "Ce champs est requis");
		$validation->required("duration", "Ce champs est requis");
		$validation->required("description", "Ce champs est requis");
		$validation->required("image_alt", "Ce champs est requis");
		$validation->required("program", "Ce champs est requis");
		$validation->required("nbreInscrit", "Ce champs est requis");

		$validation->required("type", "Ce champs est requis");
		if($_POST['type'] == 'payant'){
			$validation->required("cost", "Ce champs est requis");
		}else{
			$_POST['cost'] = 0;
		}
		

		/*$videoController->required()
		->verifyExtension(self::ALLOWED_EXTENSION_VIDEO);*/
		$imageController->required()
		->verifyExtension(self::ALLOWED_EXTENSION_IMAGE);

		// dump($validation->getErrors());die;
		if (!empty($validation->getErrors())) {
			$errors['post'] = $validation->getErrors();
		}
		/*
		if( !empty($videoController->getErrors()) ){
			$errors['video'] = $videoController->getErrors();
		}
		*/
		if (!empty($imageController->getErrors())) {
			$errors['image'] = $imageController->getErrors();
		}

		if ($errors) {
			echo json_encode($errors);
			exit();
		}

		// $videoController->upload();
		$imageController->upload();
		$formation = new FormationModel();

		$fields = [];
		$values = [];
		foreach ($_POST as $key => $post) {
			$fields[] = $key;
		if ($key === 'program') {
			$values[] = $post;
		} else {
			$values[] = htmlspecialchars(trim($post));
		}
		}
		/*$fields[] = 'video';
		$values[] = $videoController->getFilename();*/
		$fields[] = 'image';
		$values[] = $imageController->getFilename();

		$formation->create($fields, $values);
		$_SESSION['id_formation_tmp'] = $formation->getMaxId();

		$success['success'] = true;
		if(!empty($_POST['iframe']))
		{
			$success['iframe'] = true;       
		}

		echo json_encode($success);
	}

	public function uploadVideo()
	{
		$up = new UploadAjax("../Publics/upload/video/", '../Publics/Upload_Temp/');
		$unidid_form = $up->getUniqidForm();
		if (!(isset($unidid_form, $_SESSION['UploadAjaxABCI'][$unidid_form]))) {
		$up->exitErreurFichier('Identifiant de formulaire non valide. Rafraîchissez la page');
		}

		$up->Upload();
		$up->Transfert();

		$responses = $up->getResponseAjax();
		if ($up->getFichierOk()) {
		chmod(dirname(__DIR__) . "/Publics/upload/video/" . $up->getFichierNomNettoye(), 0777);
		$formation = new FormationModel();
		$formation->updateFormation("video", $up->getFichierNomNettoye(), $_SESSION['id_formation_tmp']);
		unset($_SESSION['id_formation_tmp']);
		}
		echo json_encode($responses);
	}

	public function update()
	{
		/*-------Controle de droit--------*/
		if (isset($_SESSION['id'])) {
			if ($_SESSION['role'] !== ROLE_USER[2]) {
				Controllers::loadView("error.php");
				exit();
			}
		} else {
			header('Location:' . BASE_URL . '/connection');
			exit();
		}
		/*---------------------------------*/
		$errors = null;
		$field = null;
		$value = null;
		$validation = new FormValidation();
		$validation->requiredAll($_POST, "Ce champ ne peut pas être vide");

		if (!$validation->run()) {
			$errors['required'] = $validation->getErrors();
			echo json_encode($errors);
			exit();
		}

		$model = new FormationModel();
		foreach ($_POST as $key => $post) {
			if ($key !== 'id') {
				$field = $key;
				$value = $post;
			}
		}

		$model->updateFormation(htmlspecialchars(trim($field)), htmlspecialchars(trim($value)), (int)$_POST['id']);
		
		$response['label'] = '';
		$response['cost'] = '';
		if($field == 'type'){
			$response['label'] = 'type';
			if($value == 'gratuit'){
				$model->updateFormation('cost', 0, (int)$_POST['id']);
				$response['cost'] = 0;
			}else{
				$response['cost']= $model->getOne((int)$_POST['id'])->cost;
			}
		} 
		
		$response['success'] = true;
		$response['post'] = htmlspecialchars(trim($value));
		if ($field === 'idFormateur') {
			$model = new FormateurModel();
			$response['post'] = $model->getOne(htmlspecialchars(trim($_POST[$field])))->fullname;
		}

		if ($field === 'idCategory') {
			$model = new CategoryModel();
			$response['post'] = $model->getOne(htmlspecialchars(trim($_POST[$field])))->nameCategory;
		}

		if ($field === 'program') {
			$response['post'] = $_POST[$field];
		}
		else if($field === 'description')
		{
			$response['post'] = $_POST[$field];
		}

		echo json_encode($response);
	}

	public function updateTarget()
	{
		/*-------Controle de droit--------*/
		if (isset($_SESSION['id'])) {
			if ($_SESSION['role'] !== ROLE_USER[2]) {
			Controllers::loadView("error.php");
			exit();
		}
		} else {
		header('Location:' . BASE_URL . '/connection');
		exit();
		}
		/*---------------------------------*/
		$validation = new FormValidation();
		$validation->requiredAll($_POST, 'Ce champ est requis');
		if (!$validation->run()) {
		echo json_encode(['error' => $validation->getErrors()]);
		exit();
		}
		$model = new FormationModel();
		$target = $model->getOne((int)$_POST['id'])->target;
		if (empty($target)) {
		$target = htmlspecialchars(trim($_POST['target']));
		} else {
		$target .= "-" . htmlspecialchars(trim($_POST['target']));
		}


		$model->updateFormation('target', $target, (int)$_POST['id']);
		echo json_encode(['success' => true]);
	}

	public function updatePrerequis()
	{
		/*-------Controle de droit--------*/
		if (isset($_SESSION['id'])) {
			if ($_SESSION['role'] !== ROLE_USER[2]) {
			Controllers::loadView("error.php");
			exit();
			}
		} else {
			header('Location:' . BASE_URL . '/connection');
			exit();
		}

		/*---------------------------------*/
		$validation = new FormValidation();
		$validation->requiredAll($_POST, 'Ce champ est requis');
		if (!$validation->run()) {
			echo json_encode(['error' => $validation->getErrors()]);
			exit();
		}

		$model = new FormationModel();
		$prerequis = $model->getOne((int)$_POST['id'])->prerequis;
		if (empty($prerequis)) {
			$prerequis = htmlspecialchars(trim($_POST['prerequis']));
		} else {
			$prerequis .= "-" . htmlspecialchars(trim($_POST['prerequis']));
		}


		$model->updateFormation('prerequis', $prerequis, (int)$_POST['id']);
		echo json_encode(['success' => true]);
	}

	public function updateImage()
	{
		/*-------Controle de droit--------*/
		if (isset($_SESSION['id'])) {
			if ($_SESSION['role'] !== ROLE_USER[2]) {
				Controllers::loadView("error.php");
				exit();
			}
		} 
		else {
			header('Location:' . BASE_URL . '/connection');
			exit();
		}

		/*---------------------------------*/
		$fileController = new FileController($_FILES, self::UPLOAD_FOLDER_IMAGE);
		$fileController->verifyExtension(self::ALLOWED_EXTENSION_IMAGE);
		if (!empty($fileController->getErrors())) {
			$errors['image'] = $fileController->getErrors();
			exit();
		}
		
		$fileController->upload();
		$model = new FormationModel();
		$model->updateFormation("image", $fileController->getFilename(), (int)$_POST['id']);
		echo json_encode(["success" => true]);
	}

	public function updateVideo()
	{
		/*-------Controle de droit--------*/
		if (isset($_SESSION['id'])) {
			if ($_SESSION['role'] !== ROLE_USER[2]) {
				Controllers::loadView("error.php");
				exit();
			}
		} else {
			header('Location:' . BASE_URL . '/connection');
			exit();
		}

		$up = new UploadAjax("../Publics/upload/video/", '../Publics/Upload_Temp/');
		$unidid_form = $up->getUniqidForm();

		if (!(isset($unidid_form, $_SESSION['UploadAjaxABCI'][$unidid_form]))) {
			$up->exitErreurFichier('Identifiant de formulaire non valide. Rafraîchissez la page');
		}

		$up->Upload();
		$up->Transfert();

		$responses = $up->getResponseAjax();
		if ($up->getFichierOk()) {
			chmod(dirname(__DIR__) . "/Publics/upload/video/" . $up->getFichierNomNettoye(), 0777);
			$formation = new FormationModel();
			$idFormation = (int)$_POST['idFormation-video'];
			$old = $formation->getOne($idFormation);

			$formation->updateFormation("video", $up->getFichierNomNettoye(), $idFormation);

			if (!is_dir(dirname(__DIR__) . "/Publics/upload/video/" . $old->video) && 
				file_exists(dirname(__DIR__) . "/Publics/upload/video/" . $old->video)) 
			{
				unlink(dirname(__DIR__) . "/Publics/upload/video/" . $old->video);
			}
			$responses["video"] = $up->getFichierNomNettoye();
		}
		echo json_encode($responses);
	}

	public function deleteTarget()
	{
		/*-------Controle de droit--------*/
		if (isset($_SESSION['id'])) {
			if ($_SESSION['role'] !== ROLE_USER[2]) {
				Controllers::loadView("error.php");
				exit();
			}
		} else {
			header('Location:' . BASE_URL . '/connection');
			exit();
		}
		/*---------------------------------*/
		$model = new FormationModel();
		$target = $model->getOne((int)$_POST['id'])->target;
		$parts = explode("-", $target);
		$newValue = '';
		foreach ($parts as $key => $part) {
			if ($part !== htmlspecialchars(trim($_POST['value']))) {
				if ($key === 0) {
					$newValue .= $part;
				} else {
					$newValue .= '-' . $part;
				}
			}
		}
		$newValue = trim($newValue, "-");
		$model->updateFormation('target', $newValue, (int)$_POST['id']);
		echo json_encode(['success' => true]);
	}

	public function deletePrerequis()
	{
		/*-------Controle de droit--------*/
		if (isset($_SESSION['id'])) {
			if ($_SESSION['role'] !== ROLE_USER[2]) {
				Controllers::loadView("error.php");
				exit();
			}
		} else {
			header('Location:' . BASE_URL . '/connection');
			exit();
		}
		/*---------------------------------*/
		$model = new FormationModel();
		$prerequis = $model->getOne((int)$_POST['id'])->prerequis;
		$parts = explode("-", $prerequis);
		$newValue = '';

		foreach ($parts as $key => $part) {
			if ($part !== htmlspecialchars(trim($_POST['value']))) {
				if ($key === 0) {
					$newValue .= $part;
				} else {
					$newValue .= '-' . $part;
				}
			}
		}
		$newValue = trim($newValue, "-");
		$model->updateFormation('prerequis', $newValue, (int)$_POST['id']);
		echo json_encode(['success' => true]);
	}

	public function deleteFormation($id)
	{
		/*-------Controle de droit--------*/
		if (isset($_SESSION['id'])) {
			if ($_SESSION['role'] !== ROLE_USER[2]) {
				Controllers::loadView("error.php");
				exit();
			}
		} else {
			header('Location:' . BASE_URL . '/connection');
			exit();
		}
		/*---------------------------------*/
		$formation = new FormationModel();
		$formation->delete((int)$id);
	}

	public function deleteMultiple()
	{
		/*-------Controle de droit--------*/
		if (isset($_SESSION['id'])) {
			if ($_SESSION['role'] !== ROLE_USER[2]) {
				Controllers::loadView("error.php");
				exit();
			}
		} else {
			header('Location:' . BASE_URL . '/connection');
			exit();
		}
		/*---------------------------------*/
		$formation = new FormationModel();
		$formation->deleteMultiple($_POST['id'], "id");
		echo json_encode(["success" => count($_POST['id']) . "elements a été supprimer"]);
	}

	public function search()
	{
		$formation = new FormationModel();
		$data['formations'] = $formation->search($_POST['q'], 'title');
		$data['title'] = 'Resultat de la recherche';
		$data['search'] = true;
		Controllers::loadView("formationList.php", $data);
		//var_dump($data);
	}

	public function getAll()
	{
		$model = new FormationModel();
		$fem = new FormationEtudiantModel();
		$allInscris = null;
		$formations = $model->all();
		foreach ($formations as $formation) {
			$nbreInscrit = $fem->getEtudiantInscrisCount((int)$formation->id);
			$tmp = [
				"idFormation" => (int)$formation->id,
				"nbre" => $nbreInscrit
			];
			$allInscris[] = $tmp;
		}
		echo json_encode(["formations" => $formations, "inscris" => $allInscris]);
	}

	public function getNotSingin()
	{
		$model = new FormationModel();
		$data['formations'] = $model->notSingin();
		echo json_encode($data);
	}

	public function getByFormateur()
	{
		$model = new FormationModel();
		$data = NULL;
		if ($_SESSION['role'] === ROLE_USER[2]) {
			$data['formations'] = $model->all();
		} else {
			$data['formations'] = $model->getFormationByFormateur($_SESSION['id']);
		}
		echo json_encode($data);
	}

    // 	public function page($i) 
    // 	{
    // 		$offset = ($i - 1)*6;
    //         $front = new Front();
    //         $data = $front->getData();
    
    // 		$home = new GeneralesModel("home");
    // 		$section = new GeneralesModel("section");
    // 		$contact = new FrontModel("contact");
    // 		$category = new CategoryModel();
    // 		$formations = new FormationModel();
    // 		$blog = new BlogModel();
    
    // 		$home_data = $home->getLastData();
    
    // 		if ($home_data === null) {
    // 			$home->createHome();
    // 		}
    
    // 		$front = new FrontModel('title');
    // 		$data['title'] = $front->getAllTitle()[0];
    // 		$data['home'] = $home_data;
    // 		$data['sections'] = $section->getDataWithLimit(3);
    // 		$data['contact'] = $contact->getAll();
    // 		$data['categories'] = $category->all();
    // 		$data['formations'] = $formations->allWithOffsetFront($offset);
    // 		$data['formation_number'] = ceil(count($formations->all()) / 6) ;
    // 		$data['formation_page'] = $i ;
    		
    // 		$allFormation = $formations->all();
    // 		$data['issetGratuit'] = false;
    
    // 		foreach($allFormation as $key => $formation){
    // 			if($formation->type == 'gratuit'){
    // 				$data['issetGratuit'] = true;
    // 			}
    // 		}
    		
    
    // 		$data['blogs'] = $blog->allWithOffsetFront(0);
    // 		$data['blog_number'] = ceil(count($blog->all()) / 6) ;
    // 		$data['blog_page'] = 1 ;
    // 		Controllers::loadView("index.php", $data);
    // 	}
    
    	public function pagegratuit($i) 
	{
		$offset = ($i - 1)*6;
        $front = new Front();
        $data = $front->getData();

		$home = new GeneralesModel("home");
		$section = new GeneralesModel("section");
		$contact = new FrontModel("contact");
		$category = new CategoryModel();
		$formations_gratuit = new FormationModel();
		$blog = new BlogModel();

		$home_data = $home->getLastData();

		if ($home_data === null) {
			$home->createHome();
		}

		$front = new FrontModel('title');
		$data['title'] = $front->getAllTitle()[0];
		$data['home'] = $home_data;
		$data['sections'] = $section->getDataWithLimit(3);
		$data['contact'] = $contact->getAll();
		$data['categories'] = $category->all();
		$data['formations_gratuit'] = $formations_gratuit->allTypeWithOffsetFront('gratuit', $offset);
		$data['formation_number'] = ceil(count($formations_gratuit->all()) / 6) ;
		$data['formation_page'] = $i ;
		
		$allFormation = $formations_gratuit->all();
		$data['issetGratuit'] = true;

		foreach($allFormation as $key => $formation){
			if($formation->type == 'gratuit'){
				$data['issetGratuit'] = true;
			}
		}
		

		$data['blogs'] = $blog->allWithOffsetFront(0);
		$data['blog_number'] = ceil(count($blog->all()) / 6) ;
		$data['blog_page'] = 1 ;
		Controllers::loadView("index.php", $data);
	}

	public function pagepayant($i) 
	{
		$offset = ($i - 1)*6;
        $front = new Front();
        $data = $front->getData();

		$home = new GeneralesModel("home");
		$section = new GeneralesModel("section");
		$contact = new FrontModel("contact");
		$category = new CategoryModel();
		$formations_payant = new FormationModel();
		$blog = new BlogModel();

		$home_data = $home->getLastData();

		if ($home_data === null) {
			$home->createHome();
		}

		$front = new FrontModel('title');
		$data['title'] = $front->getAllTitle()[0];
		$data['home'] = $home_data;
		$data['sections'] = $section->getDataWithLimit(3);
		$data['contact'] = $contact->getAll();
		$data['categories'] = $category->all();
		$data['formations_payant'] = $formations_payant->allTypeWithOffsetFront('payant', $offset);
		$data['formation_number'] = ceil(count($formations_payant->all()) / 6) ;
		$data['formation_page'] = $i ;
		
		$allFormation = $formations_payant->all();
		$data['issetGratuit'] = false;

		foreach($allFormation as $key => $formation){
			if($formation->type == 'gratuit'){
				$data['issetGratuit'] = true;
			}
		}
		

		$data['blogs'] = $blog->allWithOffsetFront(0);
		$data['blog_number'] = ceil(count($blog->all()) / 6) ;
		$data['blog_page'] = 1 ;
		Controllers::loadView("index.php", $data);
	}

	public function gratuit($i = 0)
	{
		if(empty($i)){
			$i = 1;
		}
		$offset = ($i - 1)*6;
        $front = new Front();
        $data = $front->getData();

		$home = new GeneralesModel("home");
		$section = new GeneralesModel("section");
		$contact = new FrontModel("contact");
		$category = new CategoryModel();
		$formations = new FormationModel();
		$blog = new BlogModel();

		$home_data = $home->getLastData();

		if ($home_data === null) {
			$home->createHome();
		}

		$front = new FrontModel('title');
		$data['title'] = $front->getAllTitle()[0];
		$data['home'] = $home_data;
		$data['sections'] = $section->getDataWithLimit(3);
		$data['contact'] = $contact->getAll();
		$data['categories'] = $category->all();
		$data['formations'] = $formations->allTypeWithOffsetFront('gratuit', $offset);
		$data['formation_number'] = ceil(count($formations->allType('gratuit')) / 6) ;
		$data['formation_page'] = $i ;

		$data['typeFormation'] = 'gratuit';
		
		$allFormation = $formations->all();
		$data['issetGratuit'] = false;

		foreach($allFormation as $key => $formation){
			if($formation->type == 'gratuit'){
				$data['issetGratuit'] = true;
			}
		}

		$data['blogs'] = $blog->allWithOffsetFront(0);
		$data['blog_number'] = ceil(count($blog->all()) / 6) ;
		$data['blog_page'] = 1 ;
		Controllers::loadView("index.php", $data);
	}

	public function payant($i = 0)
	{
		if(empty($i)){
			$i = 1;
		}
		$offset = ($i - 1)*6;
        $front = new Front();
        $data = $front->getData();

		$home = new GeneralesModel("home");
		$section = new GeneralesModel("section");
		$contact = new FrontModel("contact");
		$category = new CategoryModel();
		$formations = new FormationModel();
		$blog = new BlogModel();

		$home_data = $home->getLastData();

		if ($home_data === null) {
			$home->createHome();
		}

		$front = new FrontModel('title');
		$data['title'] = $front->getAllTitle()[0];
		$data['home'] = $home_data;
		$data['sections'] = $section->getDataWithLimit(3);
		$data['contact'] = $contact->getAll();
		$data['categories'] = $category->all();
		$data['formations'] = $formations->allTypeWithOffsetFront('payant', $offset);
		$data['formation_number'] = ceil(count($formations->allType('payant')) / 6) ;
		$data['formation_page'] = $i ;

		$data['typeFormation'] = 'payant';
		
		$allFormation = $formations->all();
		$data['issetGratuit'] = false;

		foreach($allFormation as $key => $formation){
			if($formation->type == 'gratuit'){
				$data['issetGratuit'] = true;
			}
		}

		$data['blogs'] = $blog->allWithOffsetFront(0);
		$data['blog_number'] = ceil(count($blog->all()) / 6) ;
		$data['blog_page'] = 1 ;
		Controllers::loadView("index.php", $data);
	}
}
