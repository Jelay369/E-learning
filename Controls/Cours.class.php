<?php
class Cours
{
	const ALLOWED_EXTENSION = ["mp4", "webm"];

	public function index()
	{
        /* $model = new FormationModel();
        $data["all_formations"] = $model->all();
        Controllers::loadView("cours/index.php",$data);*/
    }
    
    
    public function view($titleFormation, $current = 1, $status = null)
    {
        if (!isset($_SESSION["connected"])) 
        {
            header('Location:' . BASE_URL);
            exit();
        } else {
            
            $titleFormation = urldecode($titleFormation);
            $titleFormation = str_replace("-", " ", $titleFormation);


            $fem = new FormationEtudiantModel();
            $listFE = $fem->getFormationEtudiant($_SESSION["matricule"]);
            $abonnements = [];

            foreach ($listFE as $list) {
                $listTitle = '';
                if ((int)$list->confirmInscription) {
                    $listTitle = Utility::formatUrl($list->title);
                    $listTitle = urldecode($listTitle);
                    $abonnements[] = str_replace("-", " ", $listTitle);
                }
            }

            if (!in_array($titleFormation, $abonnements)) {
                header('Location:' . BASE_URL . '/etudiant/dashboard');
                exit();
            }
        }
        
        $coursTermine = $fem->getChapitreTermine($_SESSION["matricule"], htmlspecialchars($titleFormation));
        $qcmTermine = $fem->getQcmTermine($_SESSION["matricule"], htmlspecialchars($titleFormation));
        $findFormationEtudiant = $fem->findFormationEtudiant($_SESSION["matricule"], htmlspecialchars($titleFormation));
        
        $diff = date_diff(new DateTime($findFormationEtudiant->dateInscription), new DateTime(date('Y-m-d H:i:s')))->days;
        if ($diff > 365){
            header('Location:' . BASE_URL . '/etudiant/dashboard');
            exit();
        }

        $notif = new NotificationModel();
        $data['notifications'] = $notif->allnotseen($_SESSION['matricule']);
        $mess = new MessengerModel();
        $data['unreadMessage'] = $mess->getUnreadMessage($_SESSION['matricule']);
        $data['currentChapitre'] = (int)$current;
        $index = (int)$current - 1; // Indice du tableau cours
        $model = new CoursModel();
        $cours = $model->getChapitreFormation(htmlspecialchars($titleFormation));
        $qm = new QcmModel();
        $data['totalQcm'] = $qm->getQcmFormation(htmlspecialchars($titleFormation));

        if (!$cours) {
        	Controllers::loadView("error.php");
        	exit();
        }

        if ($current > count($cours)) {
        	$index = count($cours) - 1;
        	$data['currentChapitre'] = count($cours);
        }
        if ($current <= 0) {
        	$index = 0;
        	$data['currentChapitre'] = 1;
        }
        $data["cours"] = $cours[$index];
        $data["allCours"] = $cours;


        if ($coursTermine->idChapitreTermine === null) {
        	$data["chapitreTermine"] = [];
        } else {
        	$data["chapitreTermine"] = explode("-", $coursTermine->idChapitreTermine);
        }
        if ($qcmTermine->idQcmTermine === null) {
        	$data["nbreQcmTermine"] = [];
        } else {
        	$data["nbreQcmTermine"] = explode("-", $qcmTermine->idQcmTermine);
        }
        //var_dump($coursTermine->idChapitreTermine) ;
        $idChapitreTermine = 0 ;
        if($coursTermine->idChapitreTermine!=null) {
            if (strpos($coursTermine->idChapitreTermine, "-") <= 0) {
                $idChapitreTermine = $coursTermine->idChapitreTermine;
            } else {
                $idChapitreTermine = explode("-", $coursTermine->idChapitreTermine);
                $idChapitreTermine = $this->getLastChapitreTermine($idChapitreTermine);
            }
             
        }
        $codechapitreterminer = $fem->getCodeChapitreTerminer($idChapitreTermine);

            if ($codechapitreterminer) {
                if ($codechapitreterminer[0]->code_chapitre <= 1) {
                    $data['codechapitreterminer'] = 2;
                } else {
                    $data['codechapitreterminer'] = (int) $codechapitreterminer[0]->code_chapitre + 1;
                }
            } else {
                $data['codechapitreterminer'] = 2;
            }
            if ($data['currentChapitre'] > $data['codechapitreterminer']) {
                header('Location:' . BASE_URL . '/Cours/view/' . Utility::formatUrl($titleFormation) . '/' . $data['codechapitreterminer']);
                exit;
            }   
       

        if ($cours[$index]->idQcm) {
        	$qlm = new QcmListModel();
        	$data["all_qcm"] = $qlm->getQcmListCours($cours[$index]->idQcm);
            
        	
            if($qcmTermine->idQcmTermine!=null) {
                $data['qcmTermine'] = explode("-", $qcmTermine->idQcmTermine);
                if (in_array($cours[$index]->idQcm, explode("-", $qcmTermine->idQcmTermine))) {
                    $qem = new QcmEtudiantModel();
                    $results = $qem->getQcmEtudiant($cours[$index]->idQcm);
                    $data['corrections'] = $results;
                 }
            }
        	
        }
        

        $etu = new EtudiantModel();
        $data['hasConfirmed'] = $etu->hasConfirmInscription($_SESSION['matricule']);
        $data['hasInscription'] = $etu->hasInscription($_SESSION['matricule']);
        $data['hasPhoto'] = $etu->hasPhoto($_SESSION['matricule']);
        $data['countNotConfirmed'] = $fem->getCountNotConfirmed($_SESSION['matricule']);

        $this->updateChapitreTermine($data["cours"]->idFormation,$data["cours"]->idChapitre,urldecode($titleFormation));

        if ($status && $status=="evaluation") {
            $qem = new QcmEtudiantModel();

            $data['reponseQcm'] = $qem->getReponse($data['all_qcm'][0]->idQcm,$_SESSION['matricule'] );
            Controllers::loadView("etudiant/coursEvaluation.php", $data);

        }elseif ($status && $status=="correction") {
            $qlm = new QcmListModel();
            $all_qcm = $qlm->getQcmListCours((int)$_POST['idQcm']);

            $results = null;
            $qem = new QcmEtudiantModel();
            foreach ($all_qcm as $qcm) {
                if(!isset($_POST[$qcm->idQcmListe])){
                    $data['post'] = $_POST;
                    $data['error'] = "Essayer de répondre à toutes les questions";
                    Controllers::loadView("etudiant/coursEvaluation.php", $data);
                    exit;
                }

                $res = null;
                $isValid = true;
                $correction = null;
                $reponseEtudiant = htmlspecialchars(trim($_POST[$qcm->idQcmListe]));
                if (strtolower($reponseEtudiant)  !== strtolower($qcm->reponse)) {
                    $isValid = false;
                    $correction = $qcm->reponse;
                }
                if(!$qem->isTerminate($qcm->idQcmListe,$_SESSION['matricule'])){
                    $qem->create(["idQcmEtudiant", "idQcm", "idQcmListe", "matriculeEtudiant", "reponseEtudiant", "isValid"], [uniqid(),$qcm->idQcm,(int)$qcm->idQcmListe,$_SESSION['matricule'],$reponseEtudiant,($isValid) ? 1 : 0]);
                }

            }

            $this->evaluationTermine($all_qcm[0]->idFormation, $all_qcm[0]->idFormation, $qcm->idQcm);
            header('Location:' . BASE_URL .'/Cours/view/'.Utility::formatUrl($titleFormation).'/'.((int)$current+1));
        } else {
            Controllers::loadView("etudiant/cours.php", $data);
        }
    }

    public function add()
    {
        if (!isset($_SESSION["connected"]) || $_SESSION['role'] === ROLE_USER[0]) {
            header('Location:' . BASE_URL);
            exit();
        }
        $up = new UploadAjax("../Publics/upload/video/cours/", '../Publics/Upload_Temp/');
        $unidid_form = $up->getUniqidForm();
        if (!(isset($unidid_form, $_SESSION['UploadAjaxABCI'][$unidid_form]))) {
            $up->exitErreurFichier('Identifiant de formulaire non valide. Rafraîchissez la page');
        }

        $up->Upload();
        $up->Transfert();

        $responses = $up->getResponseAjax();
        if ($up->getFichierOk()) {
            chmod(dirname(__DIR__) . "/Publics/upload/video/cours/" . $up->getFichierNomNettoye(), 0777);
            $video = $up->getFichierNomNettoye();
            $idFormation = (int)$_POST['idFormation'];
            $titre = htmlspecialchars(trim($_POST['titre']));
            $placement = (int)$_POST['placement'];
            $contenu = urldecode($_POST['contenu']);
            $codeChapitre = 1;

            $model = new CoursModel();
            if ($placement < 0) {
                $codeChapitre = (int)$model->getLastCodeChapitre($idFormation) + 1;
            } else {
                $codeChapitre = $placement + 1;
                $model->incrementCodeChapitre($idFormation, $placement);
            }

            $model->create(["titleChapitre", "descriptionChapitre", "idFormation", "videoChapitre", "code_chapitre", "contenuChapitre"],[$titre, "", $idFormation, $video, $codeChapitre, $contenu]);
            $this->notifyAllUser($idFormation);
            $responses["idFormation"] = $idFormation;
        }
        echo json_encode($responses);
    }

    public function addWithoutVideo()
    {
        if (!isset($_SESSION["connected"]) || $_SESSION['role'] === ROLE_USER[0]) {
            header('Location:' . BASE_URL);
            exit();
        }
        $idFormation = (int)$_POST['idFormation'];
        $titre = htmlspecialchars(trim($_POST['titre']));
        $placement = (int)$_POST['placement'];
        $contenu = urldecode($_POST['contenu']);
        $lienVideo = htmlspecialchars(trim($_POST['lienVideo']));
        $codeChapitre = 1;

        $model = new CoursModel();
        if ($placement < 0) {
            $codeChapitre = (int)$model->getLastCodeChapitre($idFormation) + 1;
        } else {
            $codeChapitre = $placement + 1;
            $model->incrementCodeChapitre($idFormation, $placement);
        }

        $model->create(
          ["titleChapitre", "descriptionChapitre", "idFormation", "code_chapitre", "contenuChapitre", "lienVideo"],
          [$titre, "", $idFormation, $codeChapitre, $contenu, $lienVideo]);
        $this->notifyAllUser($idFormation);
        $responses["idFormation"] = $idFormation;
        $responses['success'] = true;
        echo json_encode($responses);
    }

    public function update()
    {
        if (!isset($_SESSION["connected"]) || $_SESSION['role'] === ROLE_USER[0]) {
            header('Location:' . BASE_URL);
            exit();
        }
        $idFormation = (int)$_POST['idFormation'];
        $titre = htmlspecialchars(trim($_POST['titre']));
        $placement = (int)$_POST['placement'];
        $contenu = urldecode($_POST['contenu']);
        $idChapitre = (int)$_POST['idChapitre'];
        $lienVideo = htmlspecialchars(trim($_POST['lienVideo']));

        $model = new CoursModel();
        $edited = $model->getOne($idChapitre);
        $codeChapitre = $edited->code_chapitre;

        if ($placement >= 0) {
            if ($codeChapitre !== ($placement + 1)) {
                if (($placement + 1) < $codeChapitre) {
                    $model->incrementCodeChapitreWithLimit($placement, $codeChapitre, $idFormation);
                    $codeChapitre = $placement + 1;
                } else {
                    $model->decrementCodeChapitreWithLimit($placement, $codeChapitre, $idFormation);
                    $codeChapitre = $placement + 1;
                }
            }
        }
        $model->update(
            ["titleChapitre", "descriptionChapitre", "idFormation", "code_chapitre", "contenuChapitre", "lienVideo", "videoChapitre"],
            [$titre, "", $idFormation, $codeChapitre, $contenu, $lienVideo, NULL],
            $idChapitre
        );
    }

    public function updateWithoutVideo()
    {
        if (!isset($_SESSION["connected"]) || $_SESSION['role'] === ROLE_USER[0]) {
            header('Location:' . BASE_URL);
            exit();
        }
        foreach ($_POST as $key => $post) {
            if ($key !== "contenu" && $key !== "lienVideo") {
                if (trim($post) === "") {
                    echo json_encode(["error" => "Certain champs sont obligatoires"]);
                    exit();
                }
            }
        }

        $idFormation = (int)$_POST['idFormation'];
        $titre = htmlspecialchars(trim($_POST['titre']));
        $idChapitre = (int)$_POST['idChapitre'];
        $model = new CoursModel();

        $edited = $model->getOne($idChapitre);

        if ($titre !== $edited->titleChapitre) {
            $cours = $model->getByFormationAndTitle($idFormation, $titre);
            if (!empty($cours)) {
                echo json_encode(["error" => "Le titre doit être unique"]);
                exit();
            }
        }
        $this->update();
        echo json_encode(['success' => true]);
    }

    public function updateWithVideo()
    {
        if (!isset($_SESSION["connected"]) || $_SESSION['role'] === ROLE_USER[0]) {
            header('Location:' . BASE_URL);
            exit();
        }
        $up = new UploadAjax("../Publics/upload/video/cours/", '../Publics/Upload_Temp/');
        $unidid_form = $up->getUniqidForm();
        if (!(isset($unidid_form, $_SESSION['UploadAjaxABCI'][$unidid_form]))) {
            $up->exitErreurFichier('Identifiant de formulaire non valide. Rafraîchissez la page');
        }

        $up->Upload();
        $up->Transfert();

        $responses = $up->getResponseAjax();
        if ($up->getFichierOk()) {
            chmod(dirname(__DIR__) . "/Publics/upload/video/cours/" . $up->getFichierNomNettoye(), 0777);
            $video = $up->getFichierNomNettoye();
            $this->update();

            $idChapitre = (int)$_POST['idChapitre'];
            $model = new CoursModel();

            $old = $model->getOne($idChapitre);
            $filename = dirname(__DIR__) . "/Publics/upload/video/cours/" . $old->videoChapitre;

            $model->update(["videoChapitre", "lienVideo"], [$video, NULL], $idChapitre);
            if (!is_dir($filename) && file_exists($filename)) {
                unlink($filename);
            }
        }

        echo json_encode($responses);
    }

    public function updateVideo()
    {
        if (!isset($_SESSION["connected"]) || $_SESSION['role'] === ROLE_USER[0]) {
            header('Location:' . BASE_URL);
            exit();
        }
        $fc = new FileController($_FILES, "video/cours");
        if ($_FILES['videoChapitre']['name'] !== '') {
            $fc->verifyExtension(self::ALLOWED_EXTENSION);
            if (!empty($fc->getErrors())) {
                echo json_encode($fc->getErrors());
                exit();
            }
        }
        $fc->upload();
        $model = new CoursModel();
        $model->update(["videoChapitre"], [$fc->getFilename()], (int)$_POST['idChapitre']);
        echo json_encode(["success" => true, "filename" => $fc->getFilename()]);
    }

    public function delete()
    {
        if (!isset($_SESSION["connected"]) || $_SESSION['role'] === ROLE_USER[0]) {
            header('Location:' . BASE_URL);
            exit();
        }
        $id = (int)$_POST['idCours'];
        $model = new CoursModel();
        $old = $model->getOne($id);

        $filename = dirname(__DIR__) . "/Publics/upload/video/cours/" . $old->videoChapitre;
        $numero = $old->code_chapitre;
        $idFormation = $old->idFormation;

        $model->delete((int)$id);
        $model->decrementCodeChapitre($numero, $idFormation);
        if (!is_dir($filename) && file_exists($filename)) {
            unlink($filename);
        }
        echo json_encode(["success" => true]);
    }

    public function deleteMultiple()
    {
        if (!isset($_SESSION["connected"]) || $_SESSION['role'] === ROLE_USER[0]) {
            header('Location:' . BASE_URL);
            exit();
        }
        $model = new CoursModel();
        $lists_id = "(";
        foreach ($_POST['idCours'] as $id) {
            $lists_id .= (int)$id . ",";
        }
        $lists_id = trim($lists_id, ",") . ")";
        $to_deletes = $model->getMore($lists_id);
        foreach ($_POST['idCours'] as $id) {
            $model->delete((int)$id);
        }
        foreach ($to_deletes as $old) {
            $filename = dirname(__DIR__) . "/Publics/upload/video/cours/" . $old->videoChapitre;
            if (!is_dir($filename) && file_exists($filename)) {
                unlink($filename);
            }
        }
        $idFormation = $to_deletes[0]->idFormation;
        $model->reorganizeCodeChapitre($idFormation);
        echo json_encode(["success" => true]);
    }

    public function filterByFormation()
    {
        $cours = new CoursModel();
        $results = NULL;
        $type = "Tous les cours";
        if (!isset($_POST['idFormation'])) {
            if ($_SESSION['role'] === ROLE_USER[2]) {
                $results = $cours->all();
            } else {
                $results = $cours->getCoursByFormateur($_SESSION['id']);
            }
        } else {
            if ($_SESSION['role'] === ROLE_USER[2]) {
                $results = $cours->getByFormation((int)$_POST['idFormation']);
            } else {
                $results = $cours->getByFormationAndFormateur((int)$_POST['idFormation'], $_SESSION['id']);
            }

            if (!empty($results)) {
                $type = $results[0]->title;
            } else {
                $model = new FormationModel();
                $result = $model->getOne((int)$_POST['idFormation']);
                $type = $result->title;
            }
        }


        $data["cours"] = $results;
        $data["type"] = $type;
        Controllers::loadView("dashboardCoursList.php", $data);
    }

    public function getByFormation($idFormation)
    {
        $coursModel = new CoursModel();
        $cours = $coursModel->getByFormation((int)$idFormation);
        echo json_encode($cours);
    }

    public function updateChapitreTermine($idFormation,$idChapitre,$title)
    {
        $fem = new FormationEtudiantModel();
        $coursModel = new CoursModel();
        $allChapitre = $coursModel->getChapitreFormation($title);
        $cours = $fem->getChapitreTermine($_SESSION["matricule"], $title);
        $coursTermine = $cours->idChapitreTermine;
        $termine = "";
        if ($coursTermine === null) {
            $termine .= $idChapitre;
        } else {
            if (!in_array($idChapitre, explode("-", $coursTermine))) {
                $termine = (int)$idChapitre . '-' . $coursTermine;
            } else {
                $termine = $coursTermine;
            }
        }
        $fem->updateChapitreTermine($termine, $_SESSION["matricule"], (int)$idFormation);
    }
    public function chapitreTermine()
    {
        $fem = new FormationEtudiantModel();
        $coursModel = new CoursModel();
        $allChapitre = $coursModel->getChapitreFormation(htmlspecialchars($_POST["titleFormation"]));
        $cours = $fem->getChapitreTermine($_SESSION["matricule"], htmlspecialchars($_POST["titleFormation"]));
        $coursTermine = $cours->idChapitreTermine;
        $termine = "";
        if ($coursTermine === null) {
            $termine .= (int)$_POST['idChapitre'];
        } else {
            if (!in_array($_POST['idChapitre'], explode("-", $coursTermine))) {
                $termine = (int)$_POST['idChapitre'] . '-' . $coursTermine;
            } else {
                $termine = $coursTermine;
            }
        }
        $fem->updateChapitreTermine($termine, $_SESSION["matricule"], (int)$_POST['idFormation']);
        echo json_encode(["success" => true]);
    }

    public function addQCM()
    {
        if (!isset($_SESSION["connected"]) || $_SESSION['role'] === ROLE_USER[0]) {
            header('Location:' . BASE_URL);
            exit();
        }
        if (!isset($_POST['idFormation']) || !isset($_POST['codeChapitre'])) {
            echo json_encode(['requiredAll' => 'Tous les champs sont requis']);
            exit();
        }
        $validation = new FormValidation();
        $validation->requiredAll($_POST);
        if (!$validation->run()) {
            echo json_encode($validation->getErrors());
            exit();
        }
        $qm = new QcmModel();
        $cm = new CoursModel();
        $lastQcm = $qm->getLastQcmFormation((int)$_POST['idFormation']);

        $isRegistered = $qm->isRegistered((int)$_POST['idFormation'], (int)$_POST['codeChapitre']);
        if($isRegistered){
            $idQcm = $qm->getRegistered((int)$_POST['idFormation'], (int)$_POST['codeChapitre'])->idQcm;
        }
        else{
            $idQcm = time();
            $qm->create(["idQcm", "idFormation", "codeChapitre", "numeroQcm"], [
                $idQcm,
                (int)$_POST['idFormation'],
                (int)$_POST['codeChapitre'],
                $lastQcm + 1
            ]);
            $cm->setQcm($idQcm, (int)$_POST['idFormation'], (int)$_POST['codeChapitre']);
        }


        $qlm = new QcmListModel();
        $question = htmlspecialchars(trim($_POST['questions']));
        $reponse = htmlspecialchars(trim($_POST['reponses']));
        
        // $choixReponse = htmlspecialchars(trim($_POST['choixReponse1']))."-".htmlspecialchars(trim($_POST['choixReponse2']))."-".htmlspecialchars(trim($_POST['choixReponse3']));
        
        $choixReponse = '';
        foreach ($_POST['choixReponse'] as $key => $value) {
            if (!empty($value)) {
                $choixReponse .= $value.'-';
            }
        }
        $choixReponse = trim($choixReponse, '-');

        if($_FILES['audio']['name'] !== '')
        {
            $directory = 'audio';

            $fileController = new FileController($_FILES, $directory);
            $fileController->verifyExtension(["mp3","ogg"]);
            if(!empty($fileController->getErrors()))
            {
                echo json_encode($fileController->getErrors());
                exit();
            }
            $fileController->upload($_FILES['audio']['name']);
            $audio = $fileController->getFilename();

            $qlm->create(["question", "reponse", "choixReponse", "idQcm", "audio"], [
                $question,
                $reponse,
                $choixReponse,
                $idQcm,
                $audio
            ]);

        }else{
            $qlm->create(["question", "reponse", "choixReponse", "idQcm"], [
                $question,
                $reponse,
                $choixReponse,
                $idQcm
            ]);
        }


        echo json_encode(['success' => true]);
    }


    public function getQcmFormation()
    {
        $validation = new FormValidation();
        $validation->requiredAll($_POST);
        if (!$validation->run()) {
            echo json_encode(["error" => 'Veillez remplir tous les champs']);
            exit();
        }
        $qm = new QcmModel();
        $results = $qm->allQcmFormation((int)$_POST['idFormation'], (int)$_POST['numeroQcm']);
        echo json_encode(["success" => true, "data" => $results]);
    }

    public function generateCodeChapitre($idFormation)
    {
        $cm = new CoursModel();
        $lastCode = $cm->getLastCodeChapitre((int)$idFormation);
        $allCode = $cm->getCodeChapitre((int)$idFormation);
        if ($lastCode) {
            $lastCode++;
        } else {
            $lastCode = 1;
        }
        echo json_encode(["codeChapitre" => $lastCode, "allCodes" => $allCode]);
    }

    public function verifyData()
    {
        if (!isset($_SESSION["connected"]) || $_SESSION['role'] === ROLE_USER[0]) {
            header('Location:' . BASE_URL);
            exit();
        }
        foreach ($_POST as $key => $post) {
            if ($key !== "contenu" && $key !== "idChapitre" && $key !== "lienVideo") {
                if (trim($post) === "") {
                    echo json_encode(["error" => "Certain champs sont obligatoires"]);
                    exit();
                }
            }
        }

        $idFormation = (int)$_POST['idFormation'];
        $titre = htmlspecialchars(trim($_POST['titre']));
        $model = new CoursModel();
        $cours = $model->getByFormationAndTitle($idFormation, $titre);

        if (!empty($cours)) {
            echo json_encode(["error" => "Le titre doit être unique"]);
            exit();
        }
        echo json_encode(['success' => true]);
    }

    public function verifyDataUpdate()
    {
        if (!isset($_SESSION["connected"]) || $_SESSION['role'] === ROLE_USER[0]) {
            header('Location:' . BASE_URL);
            exit();
        }
        foreach ($_POST as $key => $post) {
            if ($key !== "contenu" && $key !== "lienVideo") {
                if (trim($post) === "") {
                    echo json_encode(["error" => "Certain champs sont obligatoires"]);
                    exit();
                }
            }
        }


        $idFormation = (int)$_POST['idFormation'];
        $titre = htmlspecialchars(trim($_POST['titre']));
        $idChapitre = (int)$_POST['idChapitre'];
        $model = new CoursModel();

        $edited = $model->getOne($idChapitre);
        if ($titre !== $edited->titleChapitre) {
            $cours = $model->getByFormationAndTitle($idFormation, $titre);
            if (!empty($cours)) {
                echo json_encode(["error" => "Le titre doit être unique"]);
                exit();
            }
        }

        echo json_encode(['success' => true]);
    }

    public function prepareSendMail($idFormation)
    {
        $cours = new CoursModel();
        $last = $cours->getLast($idFormation);
        $etu = new EtudiantModel();

        $etudiants = $etu->getByFormation($last->idChapitre);
        echo json_encode($etudiants);
    }

    private function notifyAllUser($idFormation)
    {
        $cours = new CoursModel();
        $notif = new NotificationModel();
        $etu = new EtudiantModel();

        $last = $cours->getLast($idFormation);
        $etudiants = $etu->getByFormation($last->idChapitre);
        foreach ($etudiants as $etudiant) {
            $notif->create(["matriculeEtudiant", "idChapitre"], [$etudiant->matriculeEtudiant, $last->idChapitre]);
        }
    }

    private function evaluationTermine($titleFormation, $idFormation, $idQcm )
    {
        $fem = new FormationEtudiantModel();
        $qcm = $fem->getQcmTermineById($_SESSION["matricule"], (int)$idFormation);
        $qcmTermine = $qcm->idQcmTermine;
        $termine = "";
        if ($qcmTermine === null) {
            $termine .= (int)$idQcm;
            $fem->updateQcmTermine($termine, $_SESSION["matricule"], (int)$idFormation);
        } else {
            $termine = (int)$idQcm . '-' . $qcmTermine;
            if($qcmTermine != (int)$idQcm){
                $fem->updateQcmTermine($termine, $_SESSION["matricule"], (int)$idFormation);
            }
        }
    }

    private function getLastChapitreTermine($idChapitreTermine)
    {
        $cours = new CoursModel();
        $str = "(";
        foreach ($idChapitreTermine as $value) {
            $str .= $value . ",";
        }
        $str = trim($str, ',') . ")";
        $chapitres = $cours->getMore($str);

        $max = 1;
        $idChapitre = NULL;
        foreach ($chapitres as $ch) {
            if ((int)$ch->code_chapitre >= $max) {
                $max = (int)$ch->code_chapitre;
                $idChapitre = (int)$ch->idChapitre;
            }
        }
        return $idChapitre;
    }
    
    







    public function deleteQCM()
    {
        if (!isset($_SESSION["connected"]) || $_SESSION['role'] === ROLE_USER[0]) {
            header('Location:' . BASE_URL);
            exit();
        }

        $model = new QcmModel();
        $modelList = new QcmListModel();

        $id = (int)$_POST['idQCMList'];
        $oldQCMList = $modelList->getOne($id);

        $idQCM = $oldQCMList->idQcm;
        
        $old = $model->getOne($idQCM);
        $numero = $old->numeroQcm;
        $idFormation = $old->idFormation;

        $model->deleteAll((int)$id, $idFormation, $idQCM, $numero);
        
        echo json_encode(["success" => true]);
    }
    

    public function deleteQCMMultiple()
    {
        if (!isset($_SESSION["connected"]) || $_SESSION['role'] === ROLE_USER[0]) {
            header('Location:' . BASE_URL);
            exit();
        }
        
        
        $model = new QcmModel();
        $modelList = new QcmListModel();

        foreach ($_POST['idQCMList'] as $id) 
        {
            $id = (int)$id;
            $oldQCMList = $modelList->getOne($id);

            $idQCM = $oldQCMList->idQcm;
            
            $old = $model->getOne($idQCM);
            $numero = $old->numeroQcm;
            $idFormation = $old->idFormation;

            $model->deleteAll((int)$id, $idFormation, $idQCM, $numero);
        }

        echo json_encode(["success" => true]);
    }
}
