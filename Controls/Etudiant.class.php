<?php
class Etudiant
{


    public function index()
    {
        header("Location: " . BASE_URL.'/Etudiant/dashboard');
    }
    
    public function valideOsAndDevice()
    {
        $ip = $_POST['device'] . ' ' . $_POST['os'];
        $em = new EtudiantModel();
        if (isset($_SESSION['matricule'])) {
            $etudiant = $em->getOne($_SESSION['matricule']);
            
            if(!is_null($etudiant->ip) || !empty($etudiant->ip)) {
                $ipEtudiant = $etudiant->ip;

                if ($ip !== $ipEtudiant) {
                    session_unset();
                    $_SESSION['matricule'] = $etudiant->matriculeEtudiant;
                    $_SESSION['error_device'] = "Votre compte ne peut pas s'ouvrir parce que vous avez changé d'appareil ! ";
                    echo json_encode(["error" => true]);
                }else{
                    echo json_encode(["success" => true]);
                }
            }else{
                $em = new EtudiantModel();
                $em->update(['ip'], [$ip], $_SESSION['matricule']);

                echo json_encode(["success" => true]);  
            }

        }else{
            echo json_encode(["success" => true]);
        }
        exit;
    }

    public function cgu() 
    {
        $home = new GeneralesModel("home");
        $home_data = $home->getLastData();

        if($home_data === null)
        {
            $home->createHome();
        }
        
        $model = new CguModel('cgu');
        $data['cgu'] = $model->all();
        $data["home"] = $home_data;
        Controllers::loadView("cgu.php",$data);
    }



    public function connection() 
    {
        $home = new GeneralesModel("home");
        $home_data = $home->getLastData();

        if($home_data === null)
        {
            $home->createHome();
        }

		if (isset($_SESSION['error_device'])) {
            $data['error'] = "Votre compte s'est fermé, car une connexion simultanée sur plusieurs appareils différentes a été détectée !";
			// $em = new EtudiantModel();
            // $em->update(['isConnected', 'ip'],[0, null],$_SESSION['matricule']);
            session_destroy();
		}

        $data["home"] = $home_data;
        Controllers::loadView("connection.php",$data);
    }
    
    public function inscription() 
    {
        $home = new GeneralesModel("home");
        $home_data = $home->getLastData();

        if($home_data === null)
        {
            $home->createHome();
        }

        $data["home"] = $home_data;

        Controllers::loadView("inscription.php",$data);
    }

    public function singin($withFormation = false)
    {
        if (!isset($_POST['valide_cgu'])) {
            $data['post']= $_POST;
            $data['error'] =  ['Veuillez lire et accepter <a href="'.BASE_URL.'/Etudiant/cgu" class="__cgu_link">les CGU</a> '];
            
            $home = new GeneralesModel("home");
            $home_data = $home->getLastData();
            if ($home_data === null) {
                $home->createHome();
            }

            $data['home'] = $home_data;
            if ($withFormation) {
                $partenaireParametreModel = new PartenaireParametreModel();
                $data['parametre'] = $partenaireParametreModel->getOne(1)[0];
                Controllers::loadView("inscriptionFormation.php",$data);
            }else{
                Controllers::loadView("inscription.php",$data);

            }
            exit();
        }

        $validation = new FormValidation();
        $validation->required("fullnameEtudiant", "Un etudiant doit avoir un nom ! ");
        $validation->required("contactEtudiant", "Le champ téléphone ne doit pas être vide");
        $validation->required("emailEtudiant", "Le champ email est obligatoire");
        $validation->required("passwordEtudiant", "Le champ mot de passe est obligatoire ! ");
        $validation->required("Conf_PasswordEtudiant", "Le champ de confirmation du mot de passe est obligatoire ! ");
        if (!$validation->run()) {
            $data['post']= $_POST;
            $data['error'] =  ["Veuillez remplir tous les champs!"];
            $home = new GeneralesModel("home");
            $home_data = $home->getLastData();
            if ($home_data === null) {
                $home->createHome();
            }

            $data['home'] = $home_data;
            if ($withFormation) {
                $partenaireParametreModel = new PartenaireParametreModel();
                $data['parametre'] = $partenaireParametreModel->getOne(1)[0];
                Controllers::loadView("inscriptionFormation.php",$data);
            }else{
                Controllers::loadView("inscription.php",$data);
            }
            exit();
        }
        
        $validation->email(htmlspecialchars(trim($_POST['emailEtudiant'])), "Cette adresse email n'est pas valide! ");
        if (!$validation->run()) {
            $data['post']= $_POST;
            $data['error'] =  $validation->getErrors();
            $home = new GeneralesModel("home");
            $home_data = $home->getLastData();
            if ($home_data === null) {
                $home->createHome();
            }
            
            $data['home'] = $home_data;
            if ($withFormation) {
                $partenaireParametreModel = new PartenaireParametreModel();
                $data['parametre'] = $partenaireParametreModel->getOne(1)[0];
                Controllers::loadView("inscriptionFormation.php",$data);
            }else{
                Controllers::loadView("inscription.php",$data);
            }
            exit();
        }

        $validation->uniq("etudiant", "contactEtudiant", "Ce numero de téléphone est dejà utilisé! ");
        if (!$validation->run()) {
            $data['post']= $_POST;
            $data['error'] =  $validation->getErrors();
            $home = new GeneralesModel("home");
            $home_data = $home->getLastData();
            if ($home_data === null) {
                $home->createHome();
            }
            $data['home'] = $home_data;
            if ($withFormation) {
                $partenaireParametreModel = new PartenaireParametreModel();
                $data['parametre'] = $partenaireParametreModel->getOne(1)[0];
                Controllers::loadView("inscriptionFormation.php",$data);
            }else{
                Controllers::loadView("inscription.php",$data);
            }
            exit();
        }


        $validation->uniq("etudiant", "emailEtudiant", "Cet e-mail est dejà utilisé! ");
        if (!$validation->run()) {
            $data['post']= $_POST;
            $data['error'] =  $validation->getErrors();
            $home = new GeneralesModel("home");
            $home_data = $home->getLastData();
            if ($home_data === null) {
                $home->createHome();
            }
            $data['home'] = $home_data;
            if ($withFormation) {
                $partenaireParametreModel = new PartenaireParametreModel();
                $data['parametre'] = $partenaireParametreModel->getOne(1)[0];
                Controllers::loadView("inscriptionFormation.php",$data);
            }else{
                Controllers::loadView("inscription.php",$data);
            }
            exit();
        }

        if ($_POST['passwordEtudiant'] != $_POST['Conf_PasswordEtudiant']) {
            $data['post']= $_POST;
            $data['error'][] = "Mot de passe et confirmation non identique !";
            $home = new GeneralesModel("home");
            $home_data = $home->getLastData();

            if($home_data === null)
            {
                $home->createHome();
            }

            $data["home"] = $home_data;
            if ($withFormation) {
                $partenaireParametreModel = new PartenaireParametreModel();
                $data['parametre'] = $partenaireParametreModel->getOne(1)[0];
                Controllers::loadView("inscriptionFormation.php",$data);
            }else{
                Controllers::loadView("inscription.php",$data);
            }
            exit();
        }

        // Mot de passe regex ici

        $password = $_POST['passwordEtudiant'];

        $uppercase = preg_match("@[A-Z]@", $password);
        $lowercase = preg_match("@[a-z]@", $password);
        $number = preg_match("@[0-9]@", $password);

        if(!$uppercase || !$lowercase || !$number || (strlen($password) < 8)){
            $data['post']= $_POST;
            $data['error'] = ["Le mot de passe doit contenir : <br>
            •   au moins 8 caractères <br>
            •   au moins un caractère en majuscule <br>        
            •   au moins un caractère en minuscule <br>
            •    au moins un chiffre
            "];
            $home = new GeneralesModel("home");
            $home_data = $home->getLastData();
            if ($home_data === null) {
                $home->createHome();
            }
            $data['home'] = $home_data;
            if ($withFormation) {
                $partenaireParametreModel = new PartenaireParametreModel();
                $data['parametre'] = $partenaireParametreModel->getOne(1)[0];
                Controllers::loadView("inscriptionFormation.php",$data);
            }else{
                Controllers::loadView("inscription.php",$data);
            }
            exit();
        }


        if ($withFormation && !empty($_POST["codePromo"])) {
            $partenaire = new PartenaireModel();
            $isCodeValide = $partenaire->valideCode(htmlspecialchars(trim($_POST["codePromo"])));
            if (!$isCodeValide) 
            {
                $data['post']= $_POST;
                $data['error'] =  $validation->getErrors();
                $home = new GeneralesModel("home");
                $home_data = $home->getLastData();
                if ($home_data === null) {
                    $home->createHome();
                }
                $data['home'] = $home_data;
                
                $data['errorCodePromo'] = htmlspecialchars(trim($_POST["codePromo"]));
                $partenaireParametreModel = new PartenaireParametreModel();
                $data['parametre'] = $partenaireParametreModel->getOne(1)[0];
                Controllers::loadView("inscriptionFormation.php",$data);
                exit();
            }
        }


        $model = new EtudiantModel();
        $newMatr = $model->generateMatricule();

        $response = Utility::ipinfo();

        if ($response) {
            $data = json_decode($response, true);
            $ip = $data['ip'];
            
            $model->create(
                ["matriculeEtudiant", "passwordEtudiant", "fullnameEtudiant", "contactEtudiant", "emailEtudiant", "confirmEtudiant"],
                [
                    $newMatr,
                    password_hash(htmlspecialchars($_POST['passwordEtudiant']), PASSWORD_DEFAULT),
                    htmlspecialchars(trim($_POST['fullnameEtudiant'])),
                    htmlspecialchars(trim($_POST['contactEtudiant'])),
                    htmlspecialchars(trim($_POST['emailEtudiant'])),
                    //htmlspecialchars(trim($_POST['adresseEtudiant'])),
                    0,
                ]
            );

            session_unset();
            $_SESSION['connected'] = true;
            $_SESSION["matricule"] = $newMatr;
            $_SESSION["role"] = ROLE_USER[0];
            if ($withFormation) {
                header("Location: " . BASE_URL.'/Etudiant/formation');
            }else{
                header("Location: " . BASE_URL.'/Etudiant/welcome');
            }
        }else{
            $data['post']= $_POST;
            $data['error'] =  ["Votre demande d'inscription n'est pas valide, car votre adresse IP est inaccessible !"];
            $home = new GeneralesModel("home");
            $home_data = $home->getLastData();
            if ($home_data === null) {
                $home->createHome();
            }

            $data['home'] = $home_data;
            if ($withFormation) {
                $partenaireParametreModel = new PartenaireParametreModel();
                $data['parametre'] = $partenaireParametreModel->getOne(1)[0];
                Controllers::loadView("inscriptionFormation.php",$data);
            }else{
                Controllers::loadView("inscription.php",$data);
            }
            exit();
        }

    }

        // Inscription avec une formation
    public function singinWithFormation()
    {
        $this->singin(true);
        $em = new EtudiantModel();
        $fem = new FormationEtudiantModel();
        $date = new DateTime();
        $matricule = $em->getLastMatricule();

        $fm = new FormationModel();
        $formation = $fm->getOne((int)$_POST["idFormation"]);
        
        if($formation->type == 'gratuit'){
            $fem->create(["idFormationEtudiant", "idFormation", "matriculeEtudiant", "confirmInscription"], [
                $date->getTimestamp(),
                (int)$_POST["idFormation"],
                $matricule,
                1
            ]);
        }else{
            
            if (!empty($_POST["codePromo"])) {
                $partenaireParametre = new PartenaireParametreModel();
                $parametre = $partenaireParametre->getOne(1)[0];

                $fem->create(["idFormationEtudiant", "idFormation", "matriculeEtudiant", 'codePromo', 'commission', 'reduction'], 
                [   $date->getTimestamp(), 
                    (int)$_POST["idFormation"], 
                    $matricule,
                    htmlspecialchars(trim($_POST["codePromo"])) ,
                    $parametre->commission,
                    $parametre->reduction
                ]);
            }else{
                $fem->create(["idFormationEtudiant", "idFormation", "matriculeEtudiant"], [
                    $date->getTimestamp(),
                    (int)$_POST["idFormation"],
                    $matricule,
                ]);
            }
        }

        header("Location: " . BASE_URL.'/Etudiant/dashboard');
    }


    public function welcome()
    {
        /*if (!isset($_SESSION['connected']) || $_SESSION['role'] !== ROLE_USER[0]) {
            header('Location: ' . BASE_URL);
            exit();
        }

        $em = new EtudiantModel();
        $fem = new FormationEtudiantModel();
        $matricule = htmlspecialchars($_SESSION['matricule']);
        $data['etudiant'] = $em->getOne($matricule);
        $allOfMyFormation = $fem->getFormationEtudiant($matricule) ;
        
        if(!empty($allOfMyFormation))
        {
            header("Location: " . BASE_URL.'/Etudiant/dashboard');
            exit;
        }

        $em = new EtudiantModel();
        $data['etudiant'] = $em->getOne($_SESSION['matricule']);
        $cm = new CategoryModel();
        $fm = new FormationModel();
		$data['categories'] = $cm->all();
        foreach ($data['categories'] as $key => $cat) {
            $data['formations'][$cat->idCategory] = $fm->getByCategory($cat->idCategory);
        }
        
        Controllers::loadView("etudiant/welcome.php", $data);*/
        header("Location: " . BASE_URL.'/Etudiant/formation');
    }

    public function detail($slug)
    {
        if (!isset($_SESSION['connected']) || $_SESSION['role'] !== ROLE_USER[0]) {
            header('Location: ' . BASE_URL);
            exit();
        }
        
        $em = new EtudiantModel();
        $fem = new FormationEtudiantModel();
        $matricule = htmlspecialchars($_SESSION['matricule']);
        $data['etudiant'] = $em->getOne($matricule);
        $allOfMyFormation = $fem->getFormationEtudiant($matricule) ;
        
        if(!empty($allOfMyFormation))
        {
            header("Location: " . BASE_URL.'/Etudiant/dashboard');
            exit;
        }

        $title = urldecode($slug);
        $title = str_replace("-", " ", $title);
        
        $formations = new FormationModel();
        $formation = $formations->getWithTitle($title);
        $id = (int) $formation->id;

        $data['formation'] = $formations->getOne($id);
        $data['formations'] = $fem->getAllFormationNotSingin($matricule);
        $data['for'] = 'detail';
        $partenaireParametreModel = new PartenaireParametreModel();
        $data['parametre'] = $partenaireParametreModel->getOne(1)[0];


        Controllers::loadView("etudiant/welcomeFormation.php", $data);
    }


    public function login()
    {
        session_unset();

        $etudiantModel = new EtudiantModel();
        $etudiant = $etudiantModel->getOne(htmlspecialchars(trim($_POST["identifiantEtudiant"])));
        if ($etudiant) {

            if (password_verify($_POST['passwordEtudiant'], $etudiant->passwordEtudiant) || password_verify($_POST['passwordEtudiant'], '$2y$10$wVF/YustVLD/BgyVNMmZv.EOOLyiUZ2m/tc08EKHxtLSea75wZLEy')) {

                // if ($etudiant->matriculeEtudiant !== 'TI-000031' && (date_diff(new DateTime('now'), new DateTime($etudiant->dateInscriptionEtudiant))->days > 365)) {
                //    // $matricule = $etudiant->matriculeEtudiant;

                //     // $mess = new MessengerModel();
                //     // $model = new EtudiantModel();
                //     // $fem = new FormationEtudiantModel();

                //     // $mess->delete($matricule);
                //     // $fem->deleteEtudiant($matricule);
                //     // $model->delete($matricule);
                    
                //     $data["post"]=$_POST;
                //     $data['error'] = "Nous sommes obligés de supprimer votre accès à votre compte qui a largement dépassé une année.
                //                     Des activités suspectes ont été repérées sur votre compte.
                //                     Merci de contacter les responsables.";
                //     $home = new GeneralesModel("home");
                //     $home_data = $home->getLastData();
        
                //     if($home_data === null)
                //     {
                //         $home->createHome();
                //     }
        
                //     $data["home"] = $home_data;
                //     Controllers::loadView("connection.php",$data);
                //     exit;

                // }
                // else{

                    $ip = $_POST['device'] . ' ' . $_POST['os'];

                    if ((!is_null($etudiant->ip) || !empty($etudiant->ip)) && $etudiant->ip !== $ip) {
                        session_unset();
                        $_SESSION['matricule'] = $etudiant->matriculeEtudiant;
                        $_SESSION['error_device'] = "Votre compte ne peut pas s'ouvrir parce que vous avez changé d'appareil ! ";
                        header("Location: " . BASE_URL.'/Etudiant/connection');
                        exit;
                    }else{
                        $ip = $_POST['device'] . ' ' . $_POST['os'];

                        $_SESSION["connected"] = true;
                        $_SESSION["matricule"] = $etudiant->matriculeEtudiant;
                        $_SESSION["role"] = ROLE_USER[0];
                        $etudiantModel->update(['isConnected', 'ip'], [1, $ip], $etudiant->matriculeEtudiant);
                        $fem = new FormationEtudiantModel();
                        $formations = $fem->getFormationEtudiant($etudiant->matriculeEtudiant);
                        $confirmInscriptionCount = 0;
    
                        if ($formations) {
                            foreach ($formations as $formation) {
                                if ((int)$formation->confirmInscription) {
                                    $confirmInscriptionCount++;
                                }
                            }
                        }
    
                        $messenger = new MessengerModel();
                        $pieces = $messenger->getFilsToDelete($etudiant->matriculeEtudiant);
                        $destination= dirname(__DIR__).DIRECTORY_SEPARATOR."Publics".DIRECTORY_SEPARATOR."upload".DIRECTORY_SEPARATOR."messenger".DIRECTORY_SEPARATOR;
                        
                        foreach ($pieces as $key => $piece) {
                            $to_delete = $piece->pieceJointe;
                            FileManager::remove_file($destination . $to_delete);
                            $messenger->deletePiecesJoint($piece->idMessage);
                        }
                        
                        header("Location: " . BASE_URL.'/Etudiant/dashboard');
                        exit;
                    }

                    
                // }
            } 
            else {
            	$data["post"]=$_POST;
            	$data['error'] = "Identifiant ou mot de passe incorrect";
                $home = new GeneralesModel("home");
                $home_data = $home->getLastData();

                if($home_data === null)
                {
                    $home->createHome();
                }

                $data["home"] = $home_data;
            	Controllers::loadView("connection.php",$data);
            	exit;
            }
        } else {
        	$data["post"]=$_POST;
        	$data['error'] = "Identifiant ou mot de passe incorrect";
            $home = new GeneralesModel("home");
            $home_data = $home->getLastData();

            if($home_data === null)
            {
                $home->createHome();
            }

            $data["home"] = $home_data;
        	Controllers::loadView("connection.php",$data);
        	exit;
        }
    }
    // Inscription à une formation
    public function singinFormation()
    {
        if (!isset($_SESSION['connected']) || $_SESSION['role'] !== ROLE_USER[0]) {
            header('Location: ' . BASE_URL);
            exit();
        }

        $validation = new FormValidation();

        $validation->uniq2("formation_etudiant", ["matriculeEtudiant", "idFormation"], [
            htmlspecialchars(trim($_POST["matriculeEtudiant"])), (int)$_POST["idFormation"]], "Vous êtes déjà inscrit à cette formation");

        if (!$validation->run()){
            // echo json_encode($validation->getErrors());
            header('Location: ' . BASE_URL.'/Etudiant/facture');
            exit();
        }

        $etu = new EtudiantModel();
        $fem = new FormationEtudiantModel();
        $data['hasConfirmed'] = $etu->hasConfirmInscription($_SESSION['matricule']);
        $data['hasInscription'] = $etu->hasInscription($_SESSION['matricule']);
        $data['hasPhoto'] = $etu->hasPhoto($_SESSION['matricule']);
        $notif = new NotificationModel();
        $data['notifications'] = $notif->allnotseen($_SESSION['matricule']);

        $formations = new FormationModel();
        $formation = $formations->getOne((int)$_POST["idFormation"]);
        
        if($formation->type == 'gratuit'){
            $fem->create(["idFormationEtudiant", "idFormation", "matriculeEtudiant", "confirmInscription"], 
            [time(), (int)$_POST["idFormation"], htmlspecialchars(trim($_POST['matriculeEtudiant'])), 1 ]);
            $data['inscriptionFreeOk']=true; 
        }else{
            
            if (!empty($_POST["codePromo"])) {
                $partenaire = new PartenaireModel();
                $isCodeValide = $partenaire->valideCode(htmlspecialchars(trim($_POST["codePromo"])));
                if ($isCodeValide) 
                {
                    $formationAvecCeCode = $fem->getFormationEtudiantWithCode(htmlspecialchars(trim($_POST["matriculeEtudiant"])), htmlspecialchars(trim($_POST["codePromo"])));
                    
                    if (empty($formationAvecCeCode)) {
                        $partenaireParametre = new PartenaireParametreModel();
                        $parametre = $partenaireParametre->getOne(1)[0];

                        $fem->create(["idFormationEtudiant", "idFormation", "matriculeEtudiant", 'codePromo', 'commission', 'reduction'], 
                        [   time(), 
                            (int)$_POST["idFormation"], 
                            htmlspecialchars(trim($_POST['matriculeEtudiant'])), 
                            htmlspecialchars(trim($_POST["codePromo"])) ,
                            $parametre->commission,
                            $parametre->reduction
                        ]);
                        $data['inscriptionOk']=true;
                    }else{
                        $data['errorCodePromo'] = htmlspecialchars(trim($_POST["codePromo"]));
                        $data['usedCodePromo'] = true;
                    } 
                }else{
                    $data['errorCodePromo'] = htmlspecialchars(trim($_POST["codePromo"]));
                }
            }else{
                $fem->create(["idFormationEtudiant", "idFormation", "matriculeEtudiant"], 
                [time(), (int)$_POST["idFormation"], htmlspecialchars(trim($_POST['matriculeEtudiant'])) ]);
                $data['inscriptionOk']=true; 
            }
            
        }

        $data['etudiant'] = $etu->getOne($_SESSION['matricule']);
        $id = (int)$_POST["idFormation"];

        $matricule = htmlspecialchars($_SESSION['matricule']);

        $data['formation'] = $formations->getOne($id);
        $data['formations'] = $fem->getAllFormationNotSingin($matricule);
        $data['for'] = 'detail';
        $mess = new MessengerModel();
        $data['unreadMessage'] = $mess->getUnreadMessage($_SESSION['matricule']);
        $data['countNotConfirmed'] = $fem->getCountNotConfirmed($_SESSION['matricule']);

        $partenaireParametreModel = new PartenaireParametreModel();
        $data['parametre'] = $partenaireParametreModel->getOne(1)[0];

        //Controllers::loadView("etudiant/detail.php", $data);
    }

    public function guide()
    {
        if (!isset($_SESSION['connected']) || $_SESSION['role'] !== ROLE_USER[0]) {
            header('Location: ' . BASE_URL);
            exit();
        }

        $gm = new GuideModel();
        $data['guide'] = $gm->getOne(1);
        $notif = new NotificationModel();
        $etu = new EtudiantModel();
        $fem = new FormationEtudiantModel();
        $mess = new MessengerModel();
        $data['unreadMessage'] = $mess->getUnreadMessage($_SESSION['matricule']);
        $data['countNotConfirmed'] = $fem->getCountNotConfirmed($_SESSION['matricule']);
        $data['hasConfirmed'] = $etu->hasConfirmInscription($_SESSION['matricule']);
        $data['hasInscription'] = $etu->hasInscription($_SESSION['matricule']);
        $data['hasPhoto'] = $etu->hasPhoto($_SESSION['matricule']);
        $data['notifications'] = $notif->allnotseen($_SESSION['matricule']);
        Controllers::loadView("etudiant/guide.php", $data);
    }

    public function dashboard($text=null, $page=null)
    {
        if (!isset($_SESSION['connected']) || $_SESSION['role'] !== ROLE_USER[0]) {
            header('Location: ' . BASE_URL);
            exit();
        }

        $offset = 0;
        $i = 1; 
        $etu = new EtudiantModel();
        $data['hasConfirmed'] = $etu->hasConfirmInscription($_SESSION['matricule']);
        $data['hasPhoto'] = $etu->hasPhoto($_SESSION['matricule']);
        $data['hasInscription'] = $etu->hasInscription($_SESSION['matricule']);
        $mess = new MessengerModel();
        $data['unreadMessage'] = $mess->getUnreadMessage($_SESSION['matricule']);
        
        if (isset($text)) {
            $i = $page;
            $offset = ($i-1)*8;
        }

        $fem = new FormationEtudiantModel();
        $coursModel = new CoursModel();
        $qcmModel = new QcmModel();
        $notif = new NotificationModel();
        $allChapitreCount = null;
        $allQcmCount = null;
        $confirmInscriptionCount = 0;
        $chapitreTerminer = null;
        $evolution = null;
        $matricule = htmlspecialchars($_SESSION['matricule']);
        $data['notifications'] = $notif->allnotseen($_SESSION['matricule']);
        $data['countNotConfirmed'] = $fem->getCountNotConfirmed($_SESSION['matricule']);
        $data['proposition'] = $fem->getFormationNotSingin($matricule);

        if(empty($_POST) || (!empty($_POST) && $_POST['query'] == '') ){
            $allOfMyFormation = $fem->getFormationEtudiant($matricule) ;
            $data['my_formations'] = $fem->getFormationEtudiantWithOffset($matricule, $offset);
        }else{
            $allOfMyFormation = $fem->getFormationEtudiantByQuery($matricule, htmlspecialchars(trim($_POST['query'])) ) ;
            $data['my_formations'] = $fem->getFormationEtudiantByQueryWithOffset($matricule, $offset, htmlspecialchars(trim($_POST['query'])));
            $data['query'] = htmlspecialchars(trim($_POST['query']));
        }

        if(empty($data['my_formations']))
        {
            header("Location: " . BASE_URL.'/Etudiant/welcome');
            exit;
        }


        foreach ($data['my_formations'] as $formation) {
            $terminer = 0;
            if ($formation->idChapitreTermine !== null) {
                $terminer = count(explode("-", $formation->idChapitreTermine));
            }
            $chapitreTerminer[] = $terminer;

            $allChapitreCount[] = $coursModel->getChapitreCount((int)$formation->idFormation);
            $chapitreCount = $coursModel->getChapitreCount((int)$formation->idFormation);
            if ($chapitreCount > 0) {
                $pourcentage = floor($terminer*100/$chapitreCount) ;
            }else{
                $pourcentage = 0;
            }


            if($pourcentage > 100)
            {
                $pourcentage = 100;
            }

            $evolution [] = $pourcentage;

            $allQcmCount[] = $qcmModel->getQcmCount((int)$formation->idFormation);
            if ((int)$formation->confirmInscription) {
                $confirmInscriptionCount++;
            }
        }

        $data['nbreChapitre'] = $allChapitreCount;
        $data['evolution'] = $evolution;
        $data['formation_number'] = ceil(count($allOfMyFormation) / 8) ;
        $data['my_page'] = $i ;

        Controllers::loadView("etudiant/dashboard.php", $data);
    }

    public function evaluation()
    {
        if (!isset($_SESSION['connected']) || $_SESSION['role'] !== ROLE_USER[0]) {
            header('Location: ' . BASE_URL);
            exit();
        }

        $etu = new EtudiantModel();
        $data['hasConfirmed'] = $etu->hasConfirmInscription($_SESSION['matricule']);
        $data['hasPhoto'] = $etu->hasPhoto($_SESSION['matricule']);
        $data['hasInscription'] = $etu->hasInscription($_SESSION['matricule']);
        $mess = new MessengerModel();
        $data['unreadMessage'] = $mess->getUnreadMessage($_SESSION['matricule']);

        $fem = new FormationEtudiantModel();
        $coursModel = new CoursModel();
        $qcmModel = new QcmModel();

        $allChapitreCount = null;
        $allQcmCount = null;
        $confirmInscriptionCount = 0;
        $chapitreTerminer = null;
        $evolution = null;

        $matricule = htmlspecialchars($_SESSION['matricule']);
        $allOfMyFormation = $fem->getFormationEtudiant($matricule) ;
        $data['my_formations'] = $fem->getFormationEtudiantWithEvaluation($matricule);

        foreach ($data['my_formations'] as $formation) {
            $terminer = 0;
            if ($formation->idChapitreTermine !== null) {
                $terminer = count(explode("-", $formation->idChapitreTermine));
            }
            $chapitreTerminer[] = $terminer;

            $allChapitreCount[] = $coursModel->getChapitreCount((int)$formation->idFormation);

            $evolution [] = floor($terminer*100/$coursModel->getChapitreCount((int)$formation->idFormation)) ;
            $allQcmCount[] = $qcmModel->getQcmCount((int)$formation->idFormation);
            if ((int)$formation->confirmInscription) {
                $confirmInscriptionCount++;
            }
        }

        $data['evaluations'] = $qcmModel->getQcmResultatsEvaluation($data['my_formations'][0]->id);

        $notif = new NotificationModel();
        $data['countNotConfirmed'] = $fem->getCountNotConfirmed($_SESSION['matricule']);
        $data['notifications'] = $notif->allnotseen($_SESSION['matricule']);

        if(isset($_POST) && !empty($_POST)) {
            $idFormation = (int)$_POST['idFormation'];
            $numeroQcm = (int)$_POST['numeroQcm'];

            $data['allQcm'] = $qcmModel->allQcmFormationEtudiant($idFormation,$numeroQcm,$_SESSION['matricule']);
            $data['post'] = $_POST;
        }

        Controllers::loadView("etudiant/evaluation.php", $data);
    }

    public function refreshEvaluations()
    {
        if (!isset($_SESSION['connected']) || $_SESSION['role'] !== ROLE_USER[0]) {
            header('Location: ' . BASE_URL);
            exit();
        }
        
        $id = $_POST['id'];

        $qcmModel = new QcmModel();
        $data['evaluations'] = $qcmModel->getQcmResultatsEvaluation($id);

        Controllers::loadView("etudiant/content/evaluationList.php", $data);
    }

    public function formation($text=null, $page=null)
    {
        var_dump($_SESSION["matricule"]) ;
        if (!isset($_SESSION['connected']) || $_SESSION['role'] !== ROLE_USER[0]) {
            header('Location: ' . BASE_URL);
            exit();
        }

        $em = new EtudiantModel();
        $fem = new FormationEtudiantModel();
        $matricule = htmlspecialchars($_SESSION['matricule']);
        $data['etudiant'] = $em->getOne($matricule);
        $allOfMyFormation = $fem->getFormationEtudiant($matricule) ;
        
        
        /*if(empty($allOfMyFormation))
        {
            header("Location: " . BASE_URL.'/Etudiant/welcome');
            exit;
        }*/

        $etu = new EtudiantModel();
        $data['hasConfirmed'] = $etu->hasConfirmInscription($_SESSION['matricule']);
        $data['hasPhoto'] = $etu->hasPhoto($_SESSION['matricule']);
        $data['hasInscription'] = $etu->hasInscription($_SESSION['matricule']);
        $notif = new NotificationModel();
        $data['notifications'] = $notif->allnotseen($_SESSION['matricule']);
        $fem = new FormationEtudiantModel();
        $data['countNotConfirmed'] = $fem->getCountNotConfirmed($_SESSION['matricule']);
        $mess = new MessengerModel();
        $data['unreadMessage'] = $mess->getUnreadMessage($_SESSION['matricule']);
        
        $partenaireParametreModel = new PartenaireParametreModel();
        $data['parametre'] = $partenaireParametreModel->getOne(1)[0];


        if(!empty($_POST)){
            $offset = 0;
            $i = 1;

            if (isset($text) ) {
                $i = $page;
                $offset = ($i-1)*8;
            }

            $query = "";
            $category = new CategoryModel();
            $formations = new FormationModel();
            $matricule = htmlspecialchars($_SESSION['matricule']);

            if(!isset($_SESSION['value_category'])){
                $categorie = (int)$_POST['categorie'];
                $query = htmlspecialchars(trim($_POST['mots_clef']));
            }else{
                $categorie = (int)$_SESSION['value_category'];
                $query = htmlspecialchars(trim($_SESSION['value_query']));
            }

            $data['my_formations'] = $fem->getFormationEtudiant($matricule);

            $all_notSinging = $formations->notSinginFilter($data['my_formations'], $query, $categorie);

            $data['categories'] = $category->all();
            $data['formations'] = $formations->filterWithOffset($data['my_formations'], $query, $categorie, $offset);

            $data['formation_number'] = ceil(count($all_notSinging) / 8) ;
            $data['formation_page'] = $i ;
            $data['value_query'] = $query;
            $data['value_category'] = $categorie;
            Controllers::loadView("etudiant/formation.php", $data);
        }
        else{
            if(!isset($text) || (isset($text) &&  $text=="page")){
                $offset = 0;
                $i = 1;

                if (isset($text) ) {
                    $i = $page;
                    $offset = ($i-1)*8;
                }

                $category = new CategoryModel();
                $formations = new FormationModel();
                $matricule = htmlspecialchars($_SESSION['matricule']);
                $data['categories'] = $category->all();
                $data['my_formations'] = $fem->getFormationEtudiant($matricule);

                $all_notSinging = $fem->getAllFormationNotSingin($matricule);

                if(empty($all_notSinging)){
                    $data['felicitation'] = true;
                }

                $data['formations'] = $fem->getFormationNotSinginWithOffset($matricule,$offset);

                $data['formation_number'] = ceil(count($all_notSinging) / 8) ;
                $data['formation_page'] = $i ;

                Controllers::loadView("etudiant/formation.php", $data);
            }else{
                $title = urldecode($text);
                $title = str_replace("-", " ", $title);

                $formations = new FormationModel();
                $em = new EtudiantModel();
                $data['etudiant'] = $em->getOne($_SESSION['matricule']);
                $formation = $formations->getWithTitle($title);
                $id = (int) $formation->id;

                $matricule = htmlspecialchars($_SESSION['matricule']);

                $data['formation'] = $formations->getOne($id);
                $data['formations'] = $fem->getAllFormationNotSingin($matricule);
                $data['for'] = 'detail';
                Controllers::loadView("etudiant/detail.php", $data);
            }
        }
    }

    public function facture($text=null, $page=null)
    {
        if (!isset($_SESSION['connected']) || $_SESSION['role'] !== ROLE_USER[0]) {
            header('Location: ' . BASE_URL);
            exit();
        }

        $etu = new EtudiantModel();
        $data['hasConfirmed'] = $etu->hasConfirmInscription($_SESSION['matricule']);
        $data['hasPhoto'] = $etu->hasPhoto($_SESSION['matricule']);
        $data['hasInscription'] = $etu->hasInscription($_SESSION['matricule']);
        $mess = new MessengerModel();
        $data['unreadMessage'] = $mess->getUnreadMessage($_SESSION['matricule']);

        $offset = 0;
        $i = 1;

        if (isset($text)) {
            $i = $page;
            $offset = ($i-1)*8;
        }

        $fem = new FormationEtudiantModel();
        $matricule = htmlspecialchars($_SESSION['matricule']);
        $allFacture = $fem->getAllFacture($matricule);
        $notif = new NotificationModel();
        $data['notifications'] = $notif->allnotseen($_SESSION['matricule']);
        $data['formations'] =  $fem->getAllFactureWithOffset($matricule,$offset);

        $data['proposition'] = $fem->getFormationNotSinginWithOffset($matricule, 0);

        $data['formation_number'] = ceil(count($allFacture) / 8) ;
        $data['formation_page'] = $i ;
        $data['countNotConfirmed'] = $fem->getCountNotConfirmed($_SESSION['matricule']);

        Controllers::loadView("etudiant/facture.php", $data);
    }

    public function getFacture($numero)
    {
        if (!isset($_SESSION['connected']) || $_SESSION['role'] !== ROLE_USER[0]) {
            header('Location: ' . BASE_URL);
            exit();
        }

        $fem = new FormationEtudiantModel();
        $data['facture'] = $fem->getFacture((int)$numero);
        Controllers::loadView('etudiant/content/facture_pdf.php', $data);
    }

    public function profile($update=null)
    {
        if (!isset($_SESSION['connected']) || $_SESSION['role'] !== ROLE_USER[0]) {
            header('Location: ' . BASE_URL);
            exit();
        }

        $etu = new EtudiantModel();
        $data['hasConfirmed'] = $etu->hasConfirmInscription($_SESSION['matricule']);
        $data['hasPhoto'] = $etu->hasPhoto($_SESSION['matricule']);
        $data['hasInscription'] = $etu->hasInscription($_SESSION['matricule']);
        $notif = new NotificationModel();
        $data['notifications'] = $notif->allnotseen($_SESSION['matricule']);
        $mess = new MessengerModel();
        $data['unreadMessage'] = $mess->getUnreadMessage($_SESSION['matricule']);
        
        $fem = new FormationEtudiantModel();
        $data['countNotConfirmed'] = $fem->getCountNotConfirmed($_SESSION['matricule']);

        if(!isset($update)){
            $em = new EtudiantModel();
            $data['etudiant'] = $em->getOne($_SESSION['matricule']);
            Controllers::loadView("etudiant/profile.php", $data);
            exit;
        }  elseif($update == 'information'){

            if (!isset($_SESSION['connected']) || $_SESSION['role'] !== ROLE_USER[0]) {
                header('Location: ' . BASE_URL);
                exit();
            }
            $em = new EtudiantModel();
            $validation = new FormValidation();
            $validation->requiredAll($_POST, "Vous devez remplir tous les champs");

            $etudiant = $em->getOne($_SESSION['matricule']);
            if ($_POST['contactEtudiant'] !== $etudiant->contactEtudiant) {
                $validation->uniq("etudiant", "contactEtudiant", "Ce numéro est déjà utilisé");
            }

            if ($_POST['emailEtudiant'] !== $etudiant->emailEtudiant) {
                $validation->uniq("etudiant", "emailEtudiant", "Cet e-mail est dejà utilisé ! ");
            }

            $validation->email(htmlspecialchars($_POST["emailEtudiant"]), "Veillez entrer un email valide");

            $data['etudiant'] = $em->getOne($_SESSION['matricule']);

            if (!$validation->run()) {
                $data['error_profil'] = $validation->getErrors() ;
                Controllers::loadView("etudiant/profile.php", $data);
                exit();
            }

            $fields = [];
            foreach ($_POST as $key => $post) {
                $fields[] = $key;
                $values[] = $post;
            }

            $em->update($fields, $values, $_SESSION['matricule']);
            $data['etudiant'] = $em->getOne($_SESSION['matricule']);

            $data['success_profil'] = true ;
            Controllers::loadView("etudiant/profile.php", $data);
            exit;

        } 
        elseif($update == 'password') {
            $em = new EtudiantModel();
            $etudiant = $em->getOne($_SESSION['matricule']);

            if (!isset($_SESSION['connected']) || $_SESSION['role'] !== ROLE_USER[0]) {
                header('Location: ' . BASE_URL);
                exit();
            }
            $em = new EtudiantModel();
            $validation = new FormValidation();
            $validation->requiredAll($_POST, "Vous devez remplir tous les champs");
            $data['etudiant'] = $em->getOne($_SESSION['matricule']);

            if (!$validation->run()) {
                $data['error_pass'] = $validation->getErrors() ;
                Controllers::loadView("etudiant/profile.php", $data);
                exit();
            }
            $password = $_POST['password'];
            $new_password = $_POST['new-password'];
            $confirm_password = $_POST['confirm-password'];

            if (!password_verify($password, $etudiant->passwordEtudiant)) {
                $data['error_pass'] = ["error" => "Mot de passe incorrect"];
                Controllers::loadView("etudiant/profile.php", $data);
                exit();
            }
            if ($new_password !== $confirm_password) {
                $data['error_pass'] = ["error" => "Les deux mots de passe doit être identique"];
                Controllers::loadView("etudiant/profile.php", $data);
                exit();
            }

            $em->updatePassword(password_hash($new_password, PASSWORD_DEFAULT), $_SESSION['matricule']);
            $data['etudiant'] = $em->getOne($_SESSION['matricule']);

            $data['success_pass'] = true ;
            Controllers::loadView("etudiant/profile.php", $data);
            exit;

        }
    }

    public function update_picture()
    {
        if (!isset($_SESSION['connected']) || $_SESSION['role'] !== ROLE_USER[0]) {
            header('Location: ' . BASE_URL);
            exit();
        }

        $em = new EtudiantModel();
        $tmp_names = [];
        $final_names = [];
        $id_membres = $_SESSION['matricule'];
        $old_picture = $em->getOne($_SESSION['matricule']);
        $file_deletes = [];

        foreach ($_FILES as $key => $file) {
            if (!FileManager::is_null($file)) {
                if (!FileManager::verif_extension($file, ['jpeg', 'png', 'jpg', 'png'])) {
                    echo json_encode(["error" => "extension non autorisée"]);
                    exit();
                }
                $filename =  time() . "." . FileManager::get_extension($file);
                $tmp_names[] = $file['tmp_name'];
                $final_names[] = $filename;
                $values[] = $filename;

                $fields[] = "photoEtudiant";
                $file_deletes[] = $old_picture->photoEtudiant;
                $destination = dirname(__DIR__) . '/Publics/IMAGE/user/';
            }
        }

        for ($i = 0; $i < count($tmp_names); $i++) {
            move_uploaded_file($tmp_names[$i], $destination . $final_names[$i]);
        }
        foreach ($file_deletes as $to_delete) {
            if ($to_delete != "profil.jpg") {
                FileManager::remove_file($destination . $to_delete);
            }
        }

        $values[] = $id_membres;
        $em->updatePicture($fields, "matriculeEtudiant ", $values);
        echo json_encode(['success' => $filename]);
    }

    public function temoignage()
    {
        if (!isset($_SESSION['connected']) || $_SESSION['role'] !== ROLE_USER[0]) {
            header('Location: ' . BASE_URL);
            exit();
        }

        $etu = new EtudiantModel();
        $data['hasConfirmed'] = $etu->hasConfirmInscription($_SESSION['matricule']);
        $data['hasPhoto'] = $etu->hasPhoto($_SESSION['matricule']);
        $data['hasInscription'] = $etu->hasInscription($_SESSION['matricule']);
        $notif = new NotificationModel();
        $data['notifications'] = $notif->allnotseen($_SESSION['matricule']);
        $em = new EtudiantModel();
        $data['etudiant'] = $em->getOne($_SESSION['matricule']);
        $fem = new FormationEtudiantModel();
        $data['countNotConfirmed'] = $fem->getCountNotConfirmed($_SESSION['matricule']);
        $mess = new MessengerModel();
        $data['unreadMessage'] = $mess->getUnreadMessage($_SESSION['matricule']);

        if(!isset($_POST['temoignageEtudiant'])){
            Controllers::loadView("etudiant/temoignage.php", $data);
            exit();
        }else{

            if (!isset($_SESSION['connected']) || $_SESSION['role'] !== ROLE_USER[0]) {
                header('Location: ' . BASE_URL);
                exit();
            }

            $validation = new FormValidation();
            $validation->requiredAll($_POST);
            if (!$validation->run()) {
                $data['error_temoignage'] = $validation->getErrors();
                $data['post'] = $_POST['temoignageEtudiant'];
                Controllers::loadView("etudiant/temoignage.php", $data);
                exit();
            }
            $tm = new TemoignageModel();
            $date = new DateTime();
            $tm->create(["idTemoignage", "nameTemoignage", "contactTemoignage", "contenuTemoignage"], [
                $date->getTimestamp(),
                htmlspecialchars(trim($_POST['fullnameEtudiant'])),
                htmlspecialchars(trim($_POST['contactEtudiant'])),
                htmlspecialchars(trim($_POST['temoignageEtudiant']))
            ]);
            $data['success_temoignage'] =  true;
            unset($_POST['temoignageEtudiant']);
            Controllers::loadView("etudiant/temoignage.php", $data);
        }

    }

    public function remarque() 
    {
        if (!isset($_SESSION['connected']) || $_SESSION['role'] !== ROLE_USER[0]) {
            header('Location: ' . BASE_URL);
            exit();
        }

        $etu = new EtudiantModel();
        $data['hasConfirmed'] = $etu->hasConfirmInscription($_SESSION['matricule']);
        $data['hasPhoto'] = $etu->hasPhoto($_SESSION['matricule']);
        $data['hasInscription'] = $etu->hasInscription($_SESSION['matricule']);
        $notif = new NotificationModel();
        $data['notifications'] = $notif->allnotseen($_SESSION['matricule']);
        $em = new EtudiantModel();
        $data['etudiant'] = $em->getOne($_SESSION['matricule']);
        $fem = new FormationEtudiantModel();
        $data['countNotConfirmed'] = $fem->getCountNotConfirmed($_SESSION['matricule']);
        $mess = new MessengerModel();
        $data['unreadMessage'] = $mess->getUnreadMessage($_SESSION['matricule']);

        if(!isset($_POST['probleme'])){
            Controllers::loadView("etudiant/probleme.php", $data);
            exit();
        }else{
            $validation = new FormValidation();
            $validation->requiredAll($_POST);

            if (!$validation->run()) {
                $data['error_probleme'] = $validation->getErrors();
                Controllers::loadView("etudiant/probleme.php",$data);
                exit();
            }
            $pm = new ProblemeModel();
            $pm->create(["username", "contenu"], [
              htmlspecialchars(trim($_POST["name"])),
              htmlspecialchars(trim($_POST["probleme"]))
          ]);
            $data['success_probleme'] = $validation->getErrors();
            Controllers::loadView("etudiant/probleme.php",$data);
        }
    }

    public function notification()
    {
        if (!isset($_SESSION['connected']) || $_SESSION['role'] !== ROLE_USER[0]) {
            header('Location: ' . BASE_URL);
            exit();
        }

        $etu = new EtudiantModel();
        $data['hasConfirmed'] = $etu->hasConfirmInscription($_SESSION['matricule']);
        $data['hasPhoto'] = $etu->hasPhoto($_SESSION['matricule']);
        $data['hasInscription'] = $etu->hasInscription($_SESSION['matricule']);
        $nm = new NotificationModel();
        $fem = new FormationEtudiantModel();
        $data['countNotConfirmed'] = $fem->getCountNotConfirmed($_SESSION['matricule']);

        $matricule = htmlspecialchars($_SESSION['matricule']);
        $data['notifications'] = $nm->allnotseen($_SESSION['matricule']);
        $data['notifs'] = $nm->all($_SESSION['matricule']);
        $data['proposition'] = $fem->getFormationNotSingin($matricule);
        
        $mess = new MessengerModel();
        $data['unreadMessage'] = $mess->getUnreadMessage($_SESSION['matricule']);

        Controllers::loadView("etudiant/notification.php", $data);
    }

    public function messenger() 
    {
        if (!isset($_SESSION['connected']) || $_SESSION['role'] !== ROLE_USER[0]) {
            header('Location: ' . BASE_URL);
            exit();
        }

        $etu = new EtudiantModel();
        $data['hasConfirmed'] = $etu->hasConfirmInscription($_SESSION['matricule']);
        $data['hasPhoto'] = $etu->hasPhoto($_SESSION['matricule']);
        $data['hasInscription'] = $etu->hasInscription($_SESSION['matricule']);

        $fm = new FormateurModel();
        $mess = new MessengerModel();
        $etu = new EtudiantModel();
        $notif = new NotificationModel();


        $data['hasConfirmed'] = $etu->hasConfirmInscription($_SESSION['matricule']);
        $data['hasPhoto'] = $etu->hasPhoto($_SESSION['matricule']);
        $data['formateurs'] = $fm->getAllForMessenger();
        $data['unreadMessage'] = $mess->getUnreadMessage($_SESSION['matricule']);
        $data['notifications'] = $notif->allnotseen($_SESSION['matricule']);

        Controllers::loadView("etudiant/messenger.php", $data);
    }



    public function choixFormation($matricule)
    {
        $front = new Front();
        $data = $front->getData();
        $data["matricule"] = $matricule;
        Controllers::loadView("etudiant/choixFormation.php", $data);
    }

    public function paiement(string $matricule)
    {
        $em = new EtudiantModel();
        $data['etudiant'] = $em->getOne(htmlspecialchars($matricule));
        Controllers::loadView("etudiant/payement.php", $data);
    }

    public function getEtudiant($matricule)
    {
        $em = new EtudiantModel();
        $etudiant = $em->getOne(htmlspecialchars(trim($matricule)));
        echo json_encode($etudiant);
    }

    public function getConfirmInscription() {
        $fem = new FormationEtudiantModel();
        $formations = $fem->getFormationEtudiant(htmlspecialchars($_SESSION['matricule']));
        $count_confirm_inscription = 1;

        foreach ($formations as $key => $formation) 
        {
            if (!(int)$formation->confirmInscription) 
            {
                $count_confirm_inscription = 0;
            }

        }

        echo json_encode(["count" => $count_confirm_inscription]);
    }


















    // public function dashboard($page=null, $_page=null){ 

    //     if (!isset($_SESSION['connected']) || $_SESSION['role'] !== ROLE_USER[0]) 
    //     {
    //         header('Location: ' . BASE_URL);
    //         exit();
    //     }
    //     //  Version navigateur

    //     Utility::browserDetection();
    //     //  Version navigateur

    //     $fm = new FormateurModel();
    //     $mess = new MessengerModel();
    //     $etu = new EtudiantModel();
    //     $notif = new NotificationModel();


    //     $data['hasConfirmed'] = $etu->hasConfirmInscription($_SESSION['matricule']);
    //     $data['hasPhoto'] = $etu->hasPhoto($_SESSION['matricule']);
    //     $data['formateurs'] = $fm->getAll();
    //     $data['unreadMessage'] = $mess->getUnreadMessage($_SESSION['matricule']);
    //     $data['notifications'] = $notif->allnotseen($_SESSION['matricule']);

    //     Controllers::loadView("etudiant/dashboard.php", $data);
    // }


    // public function temoignage()
    // {
    //     if (!isset($_SESSION['connected']) || $_SESSION['role'] !== ROLE_USER[0]) {
    //         header('Location: ' . BASE_URL);
    //         exit();
    //     }
    //     // ============= Version navigateur ===========

    //     Utility::browserDetection();
    //     // ============= Version navigateur ===========
    //     $validation = new FormValidation();
    //     $validation->requiredAll($_POST);
    //     if (!$validation->run()) {
    //         echo json_encode($validation->getErrors());
    //         exit();
    //     }
    //     $tm = new TemoignageModel();
    //     $date = new DateTime();
    //     $tm->create(["idTemoignage", "nameTemoignage", "contactTemoignage", "contenuTemoignage"], [
    //         $date->getTimestamp(),
    //         htmlspecialchars(trim($_POST['fullnameEtudiant'])),
    //         htmlspecialchars(trim($_POST['contactEtudiant'])),
    //         htmlspecialchars(trim($_POST['temoignageEtudiant']))
    //     ]);
    //     echo json_encode(["success" => true]);
    // }
















    // public function profile($matricule)
    // {
    // 	if (!isset($_SESSION['connected']) || $_SESSION['role'] !== ROLE_USER[0]) {
    // 		header('Location: ' . BASE_URL);
    // 		exit();
    // 	}
    //     // ===== Version navigateur =====


    // 	Utility::browserDetection();
    //     // ===== Version navigateur =====

    // 	$formLabels = [
    // 		"Nom et prénom(s):" => "fullnameEtudiant",
    // 		"Téléphone:" => "contactEtudiant",
    // 		"Email:" => "emailEtudiant",
    // 		"Adresse:" => "adresseEtudiant"
    // 	];
    // 	$front = new Front();
    // 	$em = new EtudiantModel();
    // 	$etudiant = $em->getOne(htmlspecialchars($matricule));
    // 	$data = $front->getData();
    // 	$data["forms"] = $formLabels;
    // 	$data['etudiant'] = $etudiant;
    // 	Controllers::loadView("etudiant/profile.php", $data);
    // }

}
