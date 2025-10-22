<?php
class Partenaire
{
    private $formation;
    private $cours;
    private $qcm;
    private $soldeNirinfo; 
    private $soldeTutoInfo; 

    public function __construct()
    {
        $this->formation = new FormationModel();
        $this->cours = new CoursModel();
        $this->qcm = new QcmModel();

        if (isset($_SESSION["id"])) {
            
            $partenaireModel = new PartenaireModel();
            $partenaire = $partenaireModel->getOneById($_SESSION["id"])[0];
            
            $model = new ServiceCodeModel();
            $this->soldeNirinfo = $model->getSoldePartenaire($partenaire->matricule);

            
            $parametres = new PartenaireParametreModel();
            $commission = $parametres->getOne(1)[0]->commission;

            $fem = new FormationEtudiantModel();
            $this->soldeTutoInfo = $fem->getSoldePartenaire($partenaire->matricule, $commission);

        }
        
        
    }

    
	public function index()
	{
        if(!isset($_SESSION['connected']) && $_SESSION["role"] !== ROLE_USER[3])
        {
            header("Location:".BASE_URL."/connection/partenaire");
            exit();
        }
        else{
            $this->isPartenaire();
            $partenaireModel = new PartenaireModel();
            $partenaire = $partenaireModel->getOneById($_SESSION["id"])[0];
            $data['partenaire'] = $partenaire;
            $data['soldeNirinfo'] = $this->soldeNirinfo;
            $data['soldeTutoInfo'] = $this->soldeTutoInfo;

            Controllers::loadView("partenaire/component/solde.php", $data);
        }
	}
    
	// public function index()
	// {
    //     if(!isset($_SESSION['connected']) && $_SESSION["role"] !== ROLE_USER[3])
    //     {
    //         header("Location:".BASE_URL."/connection/partenaire");
    //         exit();
    //     }
    //     else{
    //         $this->isPartenaire();
    //         $partenaireModel = new PartenaireModel();
    //         $partenaire = $partenaireModel->getOneById($_SESSION["id"])[0];
    //         $partenairePaiementModel = new PartenairePaiementModel();
    //         $data['historiqueList'] = $partenairePaiementModel->allForPatrenaire($partenaire);
    //         $data['partenaire'] = $partenaire;
    //         $data['soldeNirinfo'] = $this->soldeNirinfo;
    //         $data['soldeTutoInfo'] = $this->soldeTutoInfo;

    //         Controllers::loadView("partenaire/component/historiquePaiementPartenaire.php", $data);
    //     }
	// }
    
    // public function recherche_historique_paiement_partenaire()
    // {
    //     $this->isPartenaire();
    //     $partenaireModel = new PartenaireModel();
    //     $partenairePaiementModel = new PartenairePaiementModel();
    //     $partenaire = $partenaireModel->getOneById($_SESSION["id"])[0];
    //     $recherche = htmlspecialchars(trim($_POST["recherche"]));
    //     $data['historiqueList'] = $partenairePaiementModel->rechercheForPartenaire($recherche, $partenaire);
    //     $data['soldeNirinfo'] = $this->soldeNirinfo;
    //     $data['soldeTutoInfo'] = $this->soldeTutoInfo;

    //     Controllers::loadView("partenaire/component/tableHistoriquePaiementPartenaire.php", $data);
    // }

    // public function detailsPaiementPartenaire()
    // {
    //     $this->isPartenaire();
    //     $mois = htmlspecialchars(trim($_POST['mois']));
    //     $matricule = htmlspecialchars(trim($_POST['matricule']));
        
    //     $model = new PartenaireModel();
	// 	$data = $model->getOne($matricule);
    //     if (empty($data)) {
    //         echo json_encode(["error" => true]);
    //         exit;
    //     }
        
    //     $fem = new FormationEtudiantModel();
    //     $data['detailsList'] = $fem->getAllForPartenaire($mois, $matricule);
        
	// 	$date_debut = new DateTime(date($mois)) ; 
	// 	$date_debut = $date_debut->format('Y-m-d');
    //     $data['moisLettre'] = Utility::formatMois($date_debut);
    //     $parametres = new PartenaireParametreModel();
    //     $data['commission'] = $parametres->getOne(1)[0]->commission;
    //     $data['soldeNirinfo'] = $this->soldeNirinfo;
    //     $data['soldeTutoInfo'] = $this->soldeTutoInfo;

    //     Controllers::loadView("partenaire/component/detailsPaiementPartenaire.php", $data);
    // }




    private function isPartenaire()
    {
        if (isset($_SESSION['id'])) {
            if ($_SESSION['role'] !== ROLE_USER[3]) {
                Controllers::loadView("error.php");
                exit();
            }
        } else {
            header('Location:' . BASE_URL . '/connection');
            exit();
        }
    }


    public function etudiants()
    {
        $this->isPartenaire();
        $partenaireModel = new PartenaireModel();
        $partenaire = $partenaireModel->getOneById($_SESSION["id"])[0];

        $fem = new FormationEtudiantModel();
        $data['etudiantsList'] = $fem->getAllEtudiantsForPartenaire($partenaire->matricule);
        $data['partenaire'] = $partenaire;
        $data['soldeNirinfo'] = $this->soldeNirinfo;
        $data['soldeTutoInfo'] = $this->soldeTutoInfo;
        
        $parametres = new PartenaireParametreModel();
        $data['commission'] = $parametres->getOne(1)[0]->commission;

        Controllers::loadView("partenaire/component/etudiantsPartenaire.php", $data);
    }
    
    public function recherche_etudiants()
    {
        $this->isPartenaire();
        $partenaireModel = new PartenaireModel();
        $partenaire = $partenaireModel->getOneById($_SESSION["id"])[0];
        $debut = $_POST['debut'];
        $fin = $_POST['fin'];
        $fem = new FormationEtudiantModel();
        if (empty($debut) && empty($fin)) {
            $data['etudiantsList'] = $fem->getAllEtudiantsForPartenaire($partenaire->matricule);
        }else{
            $data['etudiantsList'] = $fem->rechercheEtudiantsForPartenaire($partenaire->matricule, $debut, $fin);
        }

        $data['soldeNirinfo'] = $this->soldeNirinfo;
        $data['soldeTutoInfo'] = $this->soldeTutoInfo;
        
        $parametres = new PartenaireParametreModel();
        $data['commission'] = $parametres->getOne(1)[0]->commission;

        Controllers::loadView("partenaire/component/tableEtudiantPartenaire.php", $data);
    }





    // public function nirInfoPaiement()
    // {
    //     if(!isset($_SESSION['connected']) && $_SESSION["role"] !== ROLE_USER[3])
    //     {
    //         header("Location:".BASE_URL."/connection/partenaire");
    //         exit();
    //     }
    //     else{
    //         $this->isPartenaire();
    //         $partenaireModel = new PartenaireModel();
    //         $partenaire = $partenaireModel->getOneById($_SESSION["id"])[0];

    //         $model = new ServiceCodePaiementModel();
    //         $data['historiqueList'] = $model->allForPatrenaire($partenaire);
    //         $data['partenaire'] = $partenaire;
    //         $data['soldeNirinfo'] = $this->soldeNirinfo;
    //         $data['soldeTutoInfo'] = $this->soldeTutoInfo;

    //         Controllers::loadView("partenaire/component/historiquePaiementNirInfo.php", $data);
    //     } 
    // }
    
    // public function recherche_historique_paiement_nir_info()
    // {
    //     $this->isPartenaire();
    //     $partenaireModel = new PartenaireModel();
    //     $model = new ServiceCodePaiementModel();
    //     $partenaire = $partenaireModel->getOneById($_SESSION["id"])[0];
    //     $recherche = htmlspecialchars(trim($_POST["recherche"]));
    //     $data['historiqueList'] = $model->rechercheForPartenaire($recherche, $partenaire);
    //     $data['soldeNirinfo'] = $this->soldeNirinfo;
    //     $data['soldeTutoInfo'] = $this->soldeTutoInfo;

    //     Controllers::loadView("partenaire/component/tableHistoriquePaiementNirInfo.php", $data);
    // }
    
    // public function detailsPaiementNirInfo()
    // {
    //     $this->isPartenaire();
    //     $mois = htmlspecialchars(trim($_POST['mois']));
    //     $matricule = htmlspecialchars(trim($_POST['matricule']));
        
    //     $model = new PartenaireModel();
	// 	$data = $model->getOne($matricule);
    //     if (empty($data)) {
    //         echo json_encode(["error" => true]);
    //         exit;
    //     }

    //     $model = new ServiceCodeModel();
    //     $data['detailsList'] = $model->getAllForPartenaire($mois, $matricule);

    //     $data['montant'] = 0;
    //     foreach ($data['detailsList'] as $key => $detail) {
    //         $data['montant'] = $data['montant'] + $detail->commissionServiceCode;
    //     }
        
	// 	$date_debut = new DateTime(date($mois)) ; 
	// 	$date_debut = $date_debut->format('Y-m-d');
    //     $data['moisLettre'] = Utility::formatMois($date_debut);
    //     $data['soldeNirinfo'] = $this->soldeNirinfo;
    //     $data['soldeTutoInfo'] = $this->soldeTutoInfo;

    //     Controllers::loadView("partenaire/component/detailsPaiementNirInfo.php", $data);
    // }






    
    
    public function edit()
    {
        $fm = new PartenaireModel();
        $data['partenaire'] = $fm->getOneById($_SESSION["id"])[0];
        $data['soldeNirinfo'] = $this->soldeNirinfo;
        $data['soldeTutoInfo'] = $this->soldeTutoInfo;
        Controllers::loadView("partenaire/component/editPartenaire.php",$data);
    }

    public function updateProfil()
    {
        $validation = new FormValidation();
        
        $validation->required("nom", "Un partenaire doit avoir un nom");
        $validation->required("prenom", "Un partenaire doit avoir un prénom");
        $validation->required("tel", "Le champ téléphone ne doit pas etre vide");

        if (!empty($_POST['mail'])) {
            $validation->email(htmlspecialchars(trim($_POST['mail'])), "Adresse email non valide");
        }

        if(!$validation->run())
        {
            echo json_encode(["error" => "Certains champs sont requis"]);
            exit();
        }

        $nom = htmlspecialchars(trim($_POST['nom']));
        $prenom = htmlspecialchars(trim($_POST['prenom']));
        $mail = htmlspecialchars(trim($_POST['mail']));
        $tel = htmlspecialchars(trim($_POST['tel']));

        
        $partenaireModel = new PartenaireModel();
        $partenaire = $partenaireModel->getOneById($_SESSION["id"])[0];

        if (!empty($mail)) {
            $partenaireModel->update(["nom","prenom","tel","mail"],
            [$nom,$prenom,$tel,$mail], $partenaire->matricule);
        }else{
            $partenaireModel->update(["nom","prenom","tel","mail"],
            [$nom,$prenom,$tel,null], $partenaire->matricule);
        }

        echo json_encode(["success"=>true]);
    }

    public function updatePassword()
    {
        $validation = new FormValidation();
        $validation->requiredAll($_POST,"Vous devez renseigner le champ");
        if(!$validation->run())
        {
            echo json_encode(["error" => "Tous les champs sont requis"]);
            exit();
        }

        $password = $_POST['password'];
        $npassword = $_POST['new-password'];
        $cpassword = $_POST['confirm-password'];
        
        $partenaireModel = new PartenaireModel();
        $partenaire = $partenaireModel->getOneById($_SESSION["id"])[0];
        if(! password_verify($password, $partenaire->password))
        {
            echo json_encode(["error" => "Mot de passe incorrect"]);
            exit();
        }
        if($npassword !== $cpassword)
        {
            echo json_encode(["error" => "Les 2 mots de passe doit être identique"]);
            exit();
        }
        $partenaireModel->update(["password"],[password_hash($npassword, PASSWORD_DEFAULT)], $partenaire->matricule);
        echo json_encode(["success" => true]);
    }



    public function inscrits()
    {
        $this->isPartenaire();
        $partenaireModel = new PartenaireModel();
        $partenaire = $partenaireModel->getOneById($_SESSION["id"])[0];

        $model = new ServiceCodeModel();
        $data['etudiantsList'] = $model->getAllCollaborateurForPartenaire($partenaire->matricule);
        $data['partenaire'] = $partenaire;

        $data['soldeNirinfo'] = $this->soldeNirinfo;
        $data['soldeTutoInfo'] = $this->soldeTutoInfo;
        Controllers::loadView("partenaire/component/colaborateurPartenaire.php", $data);
    }
    
    public function recherche_inscrits()
    {
        $this->isPartenaire();
        $partenaireModel = new PartenaireModel();
        $partenaire = $partenaireModel->getOneById($_SESSION["id"])[0];
        $debut = $_POST['debut'];
        $fin = $_POST['fin'];

        
        $model = new ServiceCodeModel();
        if (empty($debut) && empty($fin)) {
            $data['etudiantsList'] = $model->getAllCollaborateurForPartenaire($partenaire->matricule);
        }else{
            $data['etudiantsList'] = $model->rechercheEtudiantsForPartenaire($partenaire->matricule, $debut, $fin);
        }


        $data['soldeNirinfo'] = $this->soldeNirinfo;
        $data['soldeTutoInfo'] = $this->soldeTutoInfo;
        Controllers::loadView("partenaire/component/tableColaborateurPartenaire.php", $data);
    }


}
