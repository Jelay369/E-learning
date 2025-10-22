<?php
class Dashboard
{
    private $formation;
    private $cours;
    private $qcm;

    public function __construct()
    {
        $this->formation = new FormationModel();
        $this->cours = new CoursModel();
        $this->qcm = new QcmModel();
    }

    public function index()
    {
        $this->isAdmin();
        $formation = new FormationModel();
        $data['formations'] = $formation->all();
        $data['title'] = 'Liste des formations';
        Controllers::loadView("formationList.php", $data);
    }


    public function cgu()
    {
        $validation = new FormValidation();
        $validation->required('content');
        if (!$validation->run()) {
            echo json_encode($validation->getErrors());
            exit();
        }

        $model = new CguModel('cgu');
        $data['cgu'] = $model->all();

        $content = trim($_POST['content']);

        $model->update(["content"], [$content], 1);


        //$data['category'] = $model->getLast();
        $data['success'] = true;
        echo json_encode($data);
    }

    public function addFormation()
    {
        $this->isAdmin();
        $formation = new FormationModel();
        $category = new CategoryModel();
        $formateur = new FormateurModel();
        $data['categories'] = $category->all();
        $data['formateurs'] = $formateur->getAll();
        $data['lastFormation'] = $formation->getLastId();
        $data['title'] = 'Ajout d\'une formation';
        $data['add'] = true;

        Controllers::loadView("dashboard.php", $data);
    }

    public function editFormation(int $id)
    {
        $this->isAdmin();
        $formation = new FormationModel();
        $category = new CategoryModel();
        $data['categories'] = $category->all();
        $data['formation'] = $formation->getOne($id);
        $data['title'] = 'Editer une formation';
        $data['edit'] = true;
        Controllers::loadView("dashboard.php", $data);
    }

    public function generales(?string $link = null)
    {
        $this->isAdmin();
        switch (trim($link)) {
            case 'home':
                $gm = new GeneralesModel("home");
                $data['home'] = $gm->getLastData();
                Controllers::loadView("generalesHome.php", $data);
                break;
            case 'section':
                $gm = new GeneralesModel("section");
                $data['sections'] = $gm->getDataWithLimit(3);
                Controllers::loadView("generalesSection.php", $data);
                break;
            case 'newsletter':
                $front = new FrontModel("newsletter");
                $data['members'] = $front->getAll();
                Controllers::loadView("generalesNewsLetter.php", $data);
                break;
            case 'contact':
                $front = new FrontModel("contact");
                $data['contact'] = $front->getAll();
                Controllers::loadView("generalesContact.php", $data);
                break;
            case 'message':
                $front = new FrontModel("message");
                $data['messages'] = $front->getAll();
                Controllers::loadView("generalesMessage.php", $data);
                break;
            case 'paiement':
                $pm = new PaiementModel();
                $data["paiement"] = $pm->getAll();
                Controllers::loadView("paiement.php", $data);
                break;
            case 'blog':
                $blog = new BlogModel();
                $data["blogs"] = $blog->all();
                $category = new CategoryBlogModel();
                $data["categorys"] = $category->getAll();
                Controllers::loadView("blog.php", $data);
                break;
            case 'title':
                $front = new FrontModel('title');
                $data["title"] = $front->getAllTitle();
                Controllers::loadView("title.php", $data);
                break;
            case 'category':
                $model = new CategoryBlogModel('category_blog');
                $data['category_blog'] = $model->all();
                Controllers::loadView('addCategoryBlog.php', $data);
                break;
            case 'cgu':
                $model = new CguModel('cgu');
                $data['cgu'] = $model->all();
                Controllers::loadView('updateCgu.php', $data);
                break;
            default:
                # code...
                break;
        }
    }

    public function cours($idCours = null)
    {
        if (!isset($_SESSION["connected"]) || $_SESSION['role'] === ROLE_USER[0]) {
            //Controllers::loadView("login.php");
            header('Location:' . BASE_URL);
            exit();
        }

        $data['numeros'] = [];
        $data['formations'] = $this->formation->all();

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
        Controllers::loadView("dashboardCours.php", $data);
    }

    public function allcours()
    {
        if (!isset($_SESSION["connected"]) || $_SESSION['role'] === ROLE_USER[0]) {
            //Controllers::loadView("login.php");
            header('Location:' . BASE_URL);
            exit();
        }

        $data['formations'] = $this->formation->all();
        $data['allCours'] = $this->cours->all();

        Controllers::loadView("dashboardCoursList.php", $data);
    }

    public function edit($id)
    {
        if (!isset($_SESSION["connected"]) || $_SESSION['role'] === ROLE_USER[0]) {
            //Controllers::loadView("login.php");
            header('Location:' . BASE_URL . '/formateur');
            exit();
        }
        $cours = new CoursModel();
        $data['cours'] = $cours->getOne((int)$id);
        $formation = new FormationModel();
        $data['formations'] = $formation->getAll();
        $data['title'] = 'Editer un cours';
        $data['allCodes'] = $cours->getCodeChapitre((int)$data['cours']->idFormation);
        $data['isEdit'] = true;
        Controllers::loadView("dashboardCours.php", $data);
    }

    public function addEtudiant()
    {
        $this->isAdmin();
        $model = new EtudiantModel();
        $newMatricule = $model->generateMatricule();
        $data["etudiants"] = $model->all();
        $data["newMatricule"] = $newMatricule;
        Controllers::loadView("addEtudiant.php", $data);
    }

    public function validerInscriptionEtudiant()
    {
        $this->isAdmin();
        $validation = new FormValidation();
        $validation->required("matriculeEtudiant", "Un etudiant doit avoir un numero matricule");
        $validation->required("fullnameEtudiant", "Un etudiant doit avoir un nom");
        $validation->required("contactEtudiant", "Le champ telephone ne doit pas être vide");
        $validation->uniq("etudiant", "contactEtudiant", "Ce numero de telephone est dejà utilisé");
        if (!empty($_POST['emailEtudiant'])) {
            $validation->email(htmlspecialchars(trim($_POST['emailEtudiant'])), "Adresse email non valide");
        }

        if (!$validation->run()) {
            echo json_encode($validation->getErrors());
            exit();
        }
        $etudiantModel = new EtudiantModel();
        $etudiantModel->create(
            ["matriculeEtudiant", "passwordEtudiant", "fullnameEtudiant", "contactEtudiant", "emailEtudiant", "confirmEtudiant", "adresseEtudiant"],
            [
                htmlspecialchars(trim($_POST['matriculeEtudiant'])),
                password_hash("password", PASSWORD_DEFAULT),
                htmlspecialchars(trim($_POST['fullnameEtudiant'])),
                htmlspecialchars(trim($_POST['contactEtudiant'])),
                htmlspecialchars(trim($_POST['emailEtudiant'])),
                1,
                htmlspecialchars(trim($_POST['adresseEtudiant'])),
            ]
        );
        if (isset($_POST['idFormation'])) {
            $date = new DateTime();
            $formationEtudiant = new FormationEtudiantModel();
            $formationEtudiant->create(
                ["idFormationEtudiant", "matriculeEtudiant", "idFormation", "confirmInscription"],
                [
                    $date->getTimestamp(),
                    htmlspecialchars(trim($_POST['matriculeEtudiant'])),
                    (int)$_POST['idFormation'],
                    1
                ]
            );
        }

        echo json_encode(["success" => true]);
    }

    public function listEtudiant(?array $listEtudiant = null, ?string $query = null)
    {
        $this->isAdmin();
        $data = null;
        if ($listEtudiant === null) {
            $model = new FormationEtudiantModel();
            $data["etudiants"] = $model->all();
        } else {
            $data["etudiants"] = $listEtudiant;
            $data["query"] = $query;
        }
        Controllers::loadView("listEtudiant.php", $data);
    }

    public function confirmEtudiant()
    {
        $this->isAdmin();
        $model = new FormationEtudiantModel();
        $fm = new FormationModel();
        $facture = new FactureModel();

        $nbreInscrit = $fm->getNbreInscrit((int)$_POST['idFormation']);
        // $fm->update(["nbreInscrit"], [$nbreInscrit + 1], (int)$_POST['idFormation']);

        $formation = $model->getOne((int)$_POST['idFormationEtudiant']);

        $montant = $fm->getMontant($formation->idFormation);

        if (!is_null($formation->codePromo)) {
            $montant = $montant - $formation->reduction;
        }

        $facture->create(
            ["montant_facture", "date_facture", "date_paiement"],
            [$montant, date("Y-m-d H:i:s"), date("Y-m-d H:i:s")]
        );
        $id_facture = $facture->getLastInserted();

        $model->update(["confirmInscription", "id_facture"], [1, $id_facture], (int)$_POST['idFormationEtudiant']);


        echo json_encode(["success" => true]);
    }

    public function reinitialiseFormationEtudiant()
    {
        $this->isAdmin();
        $model = new FormationEtudiantModel();
        $fm = new FormationModel();
        $facture = new FactureModel();

        $nbreInscrit = $fm->getNbreInscrit((int)$_POST['idFormation']);
        // $fm->update(["nbreInscrit"], [$nbreInscrit + 1], (int)$_POST['idFormation']);

        $formation = $model->getOne((int)$_POST['idFormationEtudiant']);

        $montant = $fm->getMontant($formation->idFormation);

        if (!is_null($formation->codePromo)) {
            $montant = $montant - $formation->reduction;
        }

        $facture->create(
            ["montant_facture", "date_facture", "date_paiement"],
            [$montant, date("Y-m-d H:i:s"), date("Y-m-d H:i:s")]
        );
        $id_facture = $facture->getLastInserted();

        $model->update(["confirmInscription", "id_facture", "dateInscription"], [1, $id_facture, date("Y-m-d H:i:s")], (int)$_POST['idFormationEtudiant']);


        echo json_encode(["success" => true]);
    }

    public function payerCommission()
    {
        $this->isAdmin();
        $model = new FormationEtudiantModel();
        $fm = new FormationModel();
        $facture = new FactureModel();

        $nbreInscrit = $fm->getNbreInscrit((int)$_POST['idFormation']);
        $fm->update(["nbreInscrit"], [$nbreInscrit + 1], (int)$_POST['idFormation']);

        $formation = $model->getOne((int)$_POST['idFormationEtudiant']);

        $montant = $fm->getMontant($formation->idFormation);


        $model->update(["payer"], [1], (int)$_POST['idFormationEtudiant']);
        echo json_encode(["success" => true]);
    }

    public function deleteEtudiant(string $matricule)
    {
        $this->isAdmin();
        $mess = new MessengerModel();
        $model = new EtudiantModel();
        $fem = new FormationEtudiantModel();

        $model->delete($matricule);
        $mess->delete($matricule);
        $fem->deleteEtudiant($matricule);
        echo json_encode(["success" => true]);
    }

    public function deleteFormationEtudiant()
    {
        $this->isAdmin();
        $fem = new FormationEtudiantModel();
        $fem->delete((int)$_POST["idFormationEtudiant"]);
        echo json_encode(["success" => true]);
    }

    public function filterFormationEtudiant()
    {
        if (isset($_POST)) {
            $model = new FormationEtudiantModel();
            $data = null;
            $q = htmlspecialchars(trim($_POST["q"]));
            if (empty($q)) {
                $data = $model->all();
            } else {

                $data = $model->filter($q);
            }
            $this->listEtudiant($data, $q);
        }
    }

    public function qcm()
    {
        if (!isset($_SESSION["connected"]) || $_SESSION['role'] === ROLE_USER[0]) {
            //Controllers::loadView("login.php");
            header('Location:' . BASE_URL);
            exit();
        }
        $qm = new QcmModel();
        $data['questions'] = $qm->getAll();
        $data['formations'] = $this->formation->all();
        Controllers::loadView("qcm.php", $data);
    }

    public function allqcm()
    {
        if (!isset($_SESSION["connected"]) || $_SESSION['role'] === ROLE_USER[0]) {
            //Controllers::loadView("login.php");
            header('Location:' . BASE_URL);
            exit();
        }

        $data['formations'] = $this->formation->all();
        $data['allQCM'] = $this->qcm->all();
        Controllers::loadView("qcmList.php", $data);
    }

    public function guide()
    {
        $this->isAdmin();
        $gm = new GuideModel();
        $data['guide'] = $gm->getOne(1);
        Controllers::loadView("guideEtudiant.php", $data);
    }

    public function temoignageEtudiant()
    {
        $this->isAdmin();
        $tm = new TemoignageModel();
        $data['temoignages'] = $tm->getAll();
        Controllers::loadView("temoignageEtudiant.php", $data);
    }

    public function problemeEtudiant()
    {
        $this->isAdmin();
        $pm = new ProblemeModel();
        $data['problemes'] = $pm->getAll();
        Controllers::loadView("problemeEtudiant.php", $data);
    }

    public function discution()
    {
        if (!isset($_SESSION["connected"]) || $_SESSION['role'] === ROLE_USER[0]) {
            //Controllers::loadView("login.php");
            header('Location:' . BASE_URL);
            exit();
        }
        Controllers::loadView("discution.php");
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

    public function recherche_etudiant()
    {
        $recherche = htmlspecialchars(trim($_POST["recherche"]));
        $this->isAdmin();
        $model = new EtudiantModel();
        $newMatricule = $model->generateMatricule();
        $data["newMatricule"] = $newMatricule;
        $data["etudiants"] = $model->recherche($recherche);
        Controllers::loadView("template-part/table_etudiant.php", $data);
    }

    public function reinitialiseEtudiant(string $matricule)
    {
        $this->isAdmin();
        $model = new EtudiantModel();
        $model->reinitialise($matricule);
        echo json_encode(["success" => true]);
    }

    public function deblocageEtudiant(string $matricule)
    {
        $this->isAdmin();
        $model = new EtudiantModel();
        $model->deblocage($matricule);
        echo json_encode(["success" => true]);
    }

    public function updateGuide()
    {
        $this->isAdmin();

        $up = new UploadAjax("../Publics/upload/guide/", '../Publics/Upload_Temp/');

        $unidid_form = $up->getUniqidForm();
        if (!(isset($unidid_form, $_SESSION['UploadAjaxABCI'][$unidid_form]))) {
            $up->exitErreurFichier('Identifiant de formulaire non valide. Rafraîchissez la page');
        }

        $up->Upload();
        $up->Transfert();

        $responses = $up->getResponseAjax();

        if ($up->getFichierOk()) {
            $model = new GuideModel();
            chmod(dirname(__DIR__) . "/Publics/upload/guide/" . $up->getFichierNomNettoye(), 0777);
            $video = $up->getFichierNomNettoye();
            $model->update(['video'], [$video], 1);
            $responses['filename'] = $video;
        }

        echo json_encode($responses);
    }

    public function getAllCours($idFormation)
    {
        $data["numeros"] = $this->cours->getCodeChapitre((int)$idFormation);
        Controllers::loadView("formateur/components/option_cours.php", $data);
    }

    public function addCours($idCours = null)
    {
        $data['numeros'] = [];
        $data['formations'] = $this->formation->all();

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
        Controllers::loadView("admin/components/addCours.php", $data);
    }

    public function getTable($idFormation)
    {
        $idFormation = (int)$idFormation;
        $data = null;

        if ($idFormation <= 0) {
            $data['allCours'] = $this->cours->all();
        } else {
            $data['allCours'] = $this->cours->getByFormation($idFormation);
        }

        Controllers::loadView("admin/components/table_cours.php", $data);
    }

    public function categorie()
    {
        $model = new CategoryModel('category');
        $data['categories'] = $model->all();
        Controllers::loadView('addCategory.php', $data);
    }

    public function addCategorie()
    {
        $validation = new FormValidation();
        $validation->required('nameCategory');
        $validation->required('iconCategory');
        $validation->uniq('category', 'nameCategory', 'Categorie existant');

        if (!$validation->run()) {
            echo json_encode($validation->getErrors());
            exit();
        }

        $model = new CategoryModel();

        $model->create(["nameCategory", "icon"], [htmlspecialchars(trim($_POST["nameCategory"])), htmlspecialchars(trim($_POST["iconCategory"]))]);

        //$data['category'] = $model->getLast();
        $data['success'] = true;
        echo json_encode($data);
    }

    //   public function getAllCategorie(){
    //     $model = new CategoryModel();
    //     $data = $model->all();
    //     echo json_encode($data);
    //   }

    public function deleteCategorie($id)
    {
        $model = new CategoryModel();
        $model->delete((int)$id);
        echo json_encode(['success' => true]);
    }

    public function updateCategorie($id)
    {
        $validation = new FormValidation();
        $category = new CategoryModel();
        $validation->required('iconCategory');
        $validation->required('nameCategory');

        $lastCategory = $category->getOne((int)$id);

        // if($lastCategory->nameCategory === htmlspecialchars( trim($_POST['nameCategory']) )){
        //   $validation->uniq('category','nameCategory','Categorie existant');
        // }
        if (!$validation->run()) {
            echo json_encode($validation->getErrors());
            exit();
        }
        $model = new CategoryModel();
        $model->update(['nameCategory', 'icon'], [htmlspecialchars(trim($_POST['nameCategory'])), htmlspecialchars(trim($_POST["iconCategory"]))], (int)$id);

        //$data['category'] = $model->getLast();
        $data['success'] = true;
        echo json_encode($data);
    }

    public function addCategory()
    {
        $validation = new FormValidation();
        $validation->required('category');
        $validation->uniq('category_blog', 'category', 'Categorie de blog existant');

        if (!$validation->run()) {
            echo json_encode($validation->getErrors());
            exit();
        }

        $model = new CategoryBlogModel();

        $model->create(["category"], [htmlspecialchars(trim($_POST["category"]))]);

        //$data['category'] = $model->getLast();
        $data['success'] = true;
        echo json_encode($data);
    }

    public function deleteCategory($id)
    {
        $model = new CategoryBlogModel();
        $model->delete((int)$id);
        echo json_encode(['success' => true]);
    }

    public function updateCategory($id)
    {
        $validation = new FormValidation();
        $category = new CategoryBlogModel();
        $validation->required('category');

        $lastCategory = $category->getOne((int)$id);

        // if($lastCategory->nameCategory === htmlspecialchars( trim($_POST['nameCategory']) )){
        //   $validation->uniq('category','nameCategory','Categorie existant');
        // }
        if (!$validation->run()) {
            echo json_encode($validation->getErrors());
            exit();
        }
        $model = new CategoryBlogModel();
        $model->update(['category'], [htmlspecialchars(trim($_POST['category']))], (int)$id);

        //$data['category'] = $model->getLast();
        $data['success'] = true;
        echo json_encode($data);
    }



























    public function partenaire()
    {
        $this->isAdmin();
        $model = new PartenaireModel();
        $newMatricule = $model->generateMatricule();
        $data["partenaires"] = $model->all();
        $data["newMatricule"] = $newMatricule;
        Controllers::loadView("partenaire/admin/addPartenaire.php", $data);
    }

    public function validerInscriptionPartenaire()
    {
        $this->isAdmin();
        $validation = new FormValidation();
        $validation->required("matricule", "Un partenaire doit avoir un numero matricule");
        $validation->required("nom", "Un partenaire doit avoir un nom");
        $validation->required("prenom", "Un partenaire doit avoir un prénom");
        $validation->required("tel", "Le champ téléphone ne doit pas être vide");
        $validation->required("codePromo", "Le champ Code Promo ne doit pas être vide");
        $validation->uniq("partenaire", "tel", "Ce numero de telephone est dejà utilisé");
        $validation->uniq("partenaire", "codePromo", "Ce Code Promo est dejà utilisé");

        if (!empty($_POST['mail'])) {
            $validation->uniq("partenaire", "mail", "Cet Email est dejà utilisé");
            $validation->email(htmlspecialchars(trim($_POST['mail'])), "Adresse email non valide");
        }

        if (!$validation->run()) {
            echo json_encode($validation->getErrors());
            exit();
        }

        $partenaireModel = new PartenaireModel();
        $partenaireModel->create(
            ["matricule", "password", "nom", "prenom", "tel", "mail", "codePromo"],
            [
                htmlspecialchars(trim($_POST['matricule'])),
                password_hash("nirinfo", PASSWORD_DEFAULT),
                htmlspecialchars(trim($_POST['nom'])),
                htmlspecialchars(trim($_POST['prenom'])),
                htmlspecialchars(trim($_POST['tel'])),
                htmlspecialchars(trim($_POST['mail'])),
                htmlspecialchars(trim($_POST['codePromo'])),
            ]
        );

        // $partenaireModel->insertIntoPIPIMP(
        //     htmlspecialchars(trim($_POST['matricule'])),
        //     password_hash("nirinfo", PASSWORD_DEFAULT),
        //     htmlspecialchars(trim($_POST['nom'])),
        //     htmlspecialchars(trim($_POST['prenom'])),
        //     htmlspecialchars(trim($_POST['tel'])),
        //     htmlspecialchars(trim($_POST['mail'])),
        //     htmlspecialchars(trim($_POST['codePromo']))
        // );





        echo json_encode(["success" => true]);
    }

    public function reinitialisePartenaire(string $matricule)
    {
        $this->isAdmin();
        $model = new PartenaireModel();
        $data = $model->getOne(htmlspecialchars(trim($matricule)));
        if (empty($data)) {
            echo json_encode(["error" => true]);
        } else {
            $model->reinitialise($matricule);
            echo json_encode(["success" => true]);
        }
    }

    public function deletePartenaire(string $matricule)
    {
        $this->isAdmin();
        $model = new PartenaireModel();
        $data = $model->getOne(htmlspecialchars(trim($matricule)));

        if (empty($data)) {
            echo json_encode(["error" => true]);
        } else {
            $model->delete($matricule);
            echo json_encode(["success" => true]);
        }
    }

    public function getPartenaire()
    {
        $this->isAdmin();
        $model = new PartenaireModel();
        $data = $model->getOne(htmlspecialchars(trim($_POST['id'])));
        if (empty($data)) {
            echo json_encode(["error" => true]);
        } else {
            echo json_encode($data);
        }
    }

    public function updatePartenaire()
    {
        $this->isAdmin();
        $validation = new FormValidation();
        $validation->required("matricule", "Un partenaire doit avoir un numero matricule");
        $validation->required("nom", "Un partenaire doit avoir un nom");
        $validation->required("prenom", "Un partenaire doit avoir un prénom");
        $validation->required("tel", "Le champ téléphone ne doit pas être vide");
        $validation->required("codePromo", "Le champ Code Promo ne doit pas être vide");

        if (!empty($_POST['mail'])) {
            $validation->email(htmlspecialchars(trim($_POST['mail'])), "Adresse email non valide");
        }

        if (!$validation->run()) {
            echo json_encode($validation->getErrors());
            exit();
        }

        $model = new PartenaireModel();
        $model->update(
            ["nom", "prenom", "tel", "mail", "codePromo"],
            [
                htmlspecialchars(trim($_POST['nom'])),
                htmlspecialchars(trim($_POST['prenom'])),
                htmlspecialchars(trim($_POST['tel'])),
                htmlspecialchars(trim($_POST['mail'])),
                htmlspecialchars(trim($_POST['codePromo']))
            ],
            htmlspecialchars(trim($_POST['matricule']))
        );

        $data['success'] = true;
        echo json_encode($data);
    }

    public function recherche_partenaire()
    {
        $this->isAdmin();
        $recherche = htmlspecialchars(trim($_POST["recherche"]));
        $model = new PartenaireModel();
        $newMatricule = $model->generateMatricule();
        $data["newMatricule"] = $newMatricule;
        $data["partenaires"] = $model->recherche($recherche);
        Controllers::loadView("partenaire/admin/tablePartenaire.php", $data);
    }

    public function partenaireParametrage()
    {
        $this->isAdmin();
        $model = new PartenaireParametreModel();
        $data['parametre'] = $model->getOne(1)[0];
        Controllers::loadView("partenaire/admin/partenaireParametrage.php", $data);
    }

    public function updatePartenaireParametrage()
    {
        $this->isAdmin();
        $validation = new FormValidation();
        $validation->required("commission", "La commission est requise ! ");
        $validation->required("reduction", "La réduction est requise ! ");

        if (!$validation->run()) {
            echo json_encode($validation->getErrors());
            exit();
        }

        $model = new PartenaireParametreModel();
        $model->update(
            ["commission", "reduction"],
            [
                (int)$_POST['commission'],
                (int)$_POST['reduction'],
            ],
            1
        );

        $data['success'] = true;
        echo json_encode($data);
    }





    public function partenairePaiement()
    {
        $this->isAdmin();

        $model = new PartenaireModel();
        $data["partenaires"] = $model->all();

        if (isset($_POST['partenaire'])) {
            $validation = new FormValidation();
            $validation->required("partenaire", "Le séléction d'un partenaire est requis");

            if (!$validation->run()) {
                echo json_encode(['errorP' => $validation->getErrors()]);
                exit();
            }

            $matricule = htmlspecialchars(trim($_POST['partenaire']));

            $fem = new FormationEtudiantModel();
            $data['paiementList'] = $fem->getAllForPaiementPartenaire($matricule);

            $data['partenaire'] = $model->getOne($matricule)[0];

            $parametres = new PartenaireParametreModel();
            $data['commission'] = $parametres->getOne(1)[0]->commission;
            Controllers::loadView("partenaire/admin/tablePaiementPartenaire.php", $data);
            exit();
        }

        Controllers::loadView("partenaire/admin/paiementPartenaire.php", $data);
    }

    public function detailsPaiementPartenaire()
    {
        $this->isAdmin();
        $mois = htmlspecialchars(trim($_POST['mois']));
        $matricule = htmlspecialchars(trim($_POST['matricule']));

        $model = new PartenaireModel();
        $data = $model->getOne($matricule);
        if (empty($data)) {
            echo json_encode(["error" => true]);
            exit;
        }

        $fem = new FormationEtudiantModel();
        $data['detailsList'] = $fem->getAllForPaiementPartenaire($mois, $matricule);

        $date_debut = new DateTime(date($mois));
        $date_debut = $date_debut->format('Y-m-d');
        $data['moisLettre'] = Utility::formatMois($date_debut);
        $parametres = new PartenaireParametreModel();
        $data['commission'] = $parametres->getOne(1)[0]->commission;

        Controllers::loadView("partenaire/admin/detailsPaiementPartenaire.php", $data);
    }



    public function partenaireListe()
    {
        $this->isAdmin();

        $fem = new FormationEtudiantModel();
        $data['detailsList'] = $fem->getAllForListePartenaire();

        $parametres = new PartenaireParametreModel();
        $data['commission'] = $parametres->getOne(1)[0]->commission;

        Controllers::loadView("partenaire/admin/listePartenaire.php", $data);
    }

    public function recherchePartenaireListe()
    {
        $this->isAdmin();

        $fem = new FormationEtudiantModel();
        $recherche = trim($_POST["q"]);
        $data['detailsList'] = $fem->rechercheAllForListePartenaire($recherche);

        $parametres = new PartenaireParametreModel();
        $data['commission'] = $parametres->getOne(1)[0]->commission;


        $data['query'] = trim($_POST["q"]);

        Controllers::loadView("partenaire/admin/listePartenaire.php", $data);
    }




    public function paiementHistorique()
    {
        $this->isAdmin();
        $partenairePaiementModel = new PartenairePaiementModel();
        $data['historiqueList'] = $partenairePaiementModel->all();

        Controllers::loadView("partenaire/admin/historiquePaiementPartenaire.php", $data);
    }

    public function recherche_historique_paiement_partenaire()
    {
        $this->isAdmin();
        $partenairePaiementModel = new PartenairePaiementModel();
        $recherche = htmlspecialchars(trim($_POST["recherche"]));
        $data['historiqueList'] = $partenairePaiementModel->recherche($recherche);

        Controllers::loadView("partenaire/admin/tableHistoriquePaiementPartenaire.php", $data);
    }




    public function status()
    {
        $this->isAdmin();
        $model = new StatusModel();
        $data["status"] = $model->all();
        Controllers::loadView("partenaire/admin/addStatus.php", $data);
    }

    public function createStatus()
    {
        $this->isAdmin();

        $validation = new FormValidation();
        $validation->required("nom", "Le champs nom du status est requis ! ");
        $validation->uniq("status", "nom", "Ce status est dejà ajouté ! ");

        if (!$validation->run()) {
            echo json_encode($validation->getErrors());
            exit();
        }

        $statusModel = new StatusModel();
        $statusModel->create(
            ["nom"],
            [
                trim($_POST['nom']),
            ]
        );

        echo json_encode(["success" => true]);
    }

    public function updateStatus()
    {
        $this->isAdmin();
        $validation = new FormValidation();
        $validation->required("nom", "Le champs nom du status est requis ! ");

        if (!$validation->run()) {
            echo json_encode($validation->getErrors());
            exit();
        }

        $model = new StatusModel();
        $model->update(
            ["nom"],
            [
                trim($_POST['nom'])
            ],
            trim($_POST['id'])
        );

        $data['success'] = true;
        echo json_encode($data);
    }

    public function getStatus()
    {
        $this->isAdmin();
        $model = new StatusModel();
        $data = $model->getOne(htmlspecialchars(trim($_POST['id'])));
        if (empty($data)) {
            echo json_encode(["error" => true]);
        } else {
            echo json_encode($data);
        }
    }

    public function recherche_status()
    {
        $this->isAdmin();
        $recherche = trim($_POST["recherche"]);
        $model = new StatusModel();
        $data["status"] = $model->recherche($recherche);
        Controllers::loadView("partenaire/admin/tableStatus.php", $data);
    }

    public function deleteStatus(string $id)
    {
        $this->isAdmin();
        $model = new StatusModel();
        $data = $model->getOne(htmlspecialchars(trim($id)));

        if (empty($data)) {
            echo json_encode(["error" => true]);
        } else {
            $model->delete($id);
            echo json_encode(["success" => true]);
        }
    }




    public function servicePartenarial()
    {
        $this->isAdmin();
        $statusModel = new StatusModel();
        $servicePartenarialModel = new ServicePartenarialModel();
        $data["status"] = $statusModel->all();
        $data["servicePartenarial"] = $servicePartenarialModel->all();
        Controllers::loadView("partenaire/admin/addServicePartenarial.php", $data);
    }


    public function createServicePartenarial()
    {
        $this->isAdmin();

        $validation = new FormValidation();
        $validation->required("nom_service", "Le champs nom du service est requis ! ");
        $validation->required("status", "Le champs status est requis ! ");
        $validation->uniq("service_partenarial", "nom_service", "Cette service partenarial est dejà ajouté ! ");

        if (!$validation->run()) {
            echo json_encode($validation->getErrors());
            exit();
        }

        $servicePartenarialModel = new ServicePartenarialModel();
        $servicePartenarialModel->create(
            ["nom_service", "commission", "status"],
            [
                trim($_POST['nom_service']),
                trim($_POST['commission']),
                (int)$_POST['status'],
            ]
        );

        echo json_encode(["success" => true]);
    }

    public function updateServicePartenarial()
    {
        $this->isAdmin();
        $validation = new FormValidation();
        $validation->required("nom_service", "Le champs nom du service partenarial est requis ! ");

        if (!$validation->run()) {
            echo json_encode($validation->getErrors());
            exit();
        }

        $model = new ServicePartenarialModel();
        $model->update(
            ["nom_service", "commission", "status"],
            [
                trim($_POST['nom_service']),
                trim($_POST['commission']),
                (int)$_POST['status'],
            ],
            trim($_POST['id'])
        );

        $data['success'] = true;
        echo json_encode($data);
    }

    public function deleteServicePartenarial(string $id)
    {
        $this->isAdmin();
        $model = new ServicePartenarialModel();
        $data = $model->getOne(htmlspecialchars(trim($id)));

        if (empty($data)) {
            echo json_encode(["error" => true]);
        } else {
            $model->delete($id);
            echo json_encode(["success" => true]);
        }
    }

    public function getServicePartenarial()
    {
        $this->isAdmin();
        $model = new ServicePartenarialModel();
        $data = $model->getOne(htmlspecialchars(trim($_POST['id'])));

        if (empty($data)) {
            echo json_encode(["error" => true]);
        } else {
            echo json_encode($data);
        }
    }

    public function recherche_service_partenarial()
    {
        $this->isAdmin();
        $recherche = trim($_POST["recherche"]);
        $model = new ServicePartenarialModel();
        $data["servicePartenarial"] = $model->recherche($recherche);
        Controllers::loadView("partenaire/admin/tableServicePartenarial.php", $data);
    }




    public function serviceCode()
    {
        $this->isAdmin();
        $statusModel = new StatusModel();
        $serviceCodeModel = new ServiceCodeModel();
        $servicePartenarialModel = new ServicePartenarialModel();
        $data["status"] = $statusModel->all();

        if (!empty($data["status"])) {
            $data["servicePartenarial"] = $servicePartenarialModel->getByStatus($data['status'][0]);
        }

        $data["serviceCode"] = $serviceCodeModel->all();

        Controllers::loadView("partenaire/admin/addServiceCode.php", $data);
    }

    public function changeStatus()
    {
        $this->isAdmin();
        $statusModel = new StatusModel();
        $servicePartenarialModel = new ServicePartenarialModel();

        $status = (int)$_POST["id"];
        $status = $statusModel->getOne($status);
        if (empty($status)) {
            echo json_encode(["error" => true]);
        } else {

            $data = $servicePartenarialModel->getByStatus($status[0]);
            echo json_encode($data);
        }
    }

    public function createServiceCode()
    {
        $this->isAdmin();

        $validation = new FormValidation();
        $validation->required("codePromo", "Le champs Code Promo est requis ! ");
        $validation->required("fullname", "Le champs nom complet est requis ! ");
        $validation->required("status", "Le champs Status est requis ! ");
        $validation->required("service_partenarial", "Le champs service partenarial est requis ! ");
        $validation->required("commissionServiceCode", "Le champs service partenarial est requis ! ");

        if (!$validation->run()) {
            echo json_encode($validation->getErrors());
            exit();
        }

        $partenaireModel = new PartenaireModel();
        $validePartenaire = $partenaireModel->valideCode(trim($_POST['codePromo']));
        if (!$validePartenaire) {
            echo json_encode(["error" => 'Code promo invalide ! ']);
            exit;
        }

        $servicePartenarialModel = new ServicePartenarialModel();
        $servicePartenarial = $servicePartenarialModel->getOne((int)$_POST['service_partenarial']);
        if (empty($servicePartenarial)) {
            echo json_encode(["error" => 'Service invalide ! ']);
            exit;
        }

        if (empty($servicePartenarial->commission) || is_null($servicePartenarial->commission) || $servicePartenarial->commission == '') {
            $commission = trim($_POST['commissionServiceCode']);
        } else {
            $commission = $servicePartenarial[0]->commission;
        }


        $serviceCodeModel = new ServiceCodeModel();
        $serviceCodeModel->create(
            ["matriculeEtudiantEnSalle", "fullname", "codePromo", "status", "service_partenarial", "commissionServiceCode", "partenairePaye", "date", "details"],
            [
                trim($_POST['matriculeEtudiantEnSalle']),
                trim($_POST['fullname']),
                trim($_POST['codePromo']),
                $servicePartenarial[0]->status,
                $servicePartenarial[0]->idServicePartenarial,
                $commission,
                0,
                date('Y-m-d'),
                trim($_POST['details']),
            ]
        );

        echo json_encode(["success" => true]);
    }

    public function getServiceCode()
    {
        $this->isAdmin();
        $model = new ServiceCodeModel();
        $data['serviceCode'] = $model->getOne(htmlspecialchars(trim($_POST['id'])))[0];


        if (empty($data['serviceCode'])) {
            echo json_encode(["error" => true]);
        } else {
            $statusModel = new StatusModel();
            $status = $data['serviceCode']->status;
            $status = $statusModel->getOne($status);
            $servicePartenarialModel = new ServicePartenarialModel();
            $data['service_partenarial'] = $servicePartenarialModel->getByStatus($status[0]);
            echo json_encode($data);
        }
    }

    public function updateServiceCode()
    {
        $this->isAdmin();
        $validation = new FormValidation();
        $validation->required("codePromo", "Le champs Code Promo est requis ! ");
        $validation->required("fullname", "Le champs nom complet est requis ! ");
        $validation->required("status", "Le champs Status est requis ! ");
        $validation->required("service_partenarial", "Le champs service partenarial est requis ! ");
        $validation->required("commissionServiceCode", "Le champs service partenarial est requis ! ");

        if (!$validation->run()) {
            echo json_encode($validation->getErrors());
            exit();
        }

        $partenaireModel = new PartenaireModel();
        $validePartenaire = $partenaireModel->valideCode(trim($_POST['codePromo']));
        if (!$validePartenaire) {
            echo json_encode(["error" => 'Code promo invalide ! ']);
            exit;
        }

        $servicePartenarialModel = new ServicePartenarialModel();
        $servicePartenarial = $servicePartenarialModel->getOne((int)$_POST['service_partenarial']);
        if (empty($servicePartenarial)) {
            echo json_encode(["error" => 'Service invalide ! ']);
            exit;
        }

        if (empty($servicePartenarial->commission) || is_null($servicePartenarial->commission) || $servicePartenarial->commission == '') {
            $commission = trim($_POST['commissionServiceCode']);
        } else {
            $commission = $servicePartenarial[0]->commission;
        }

        $serviceCodeModel = new ServiceCodeModel();
        $serviceCodeModel->update(
            ["matriculeEtudiantEnSalle", "fullname", "codePromo", "status", "service_partenarial", "commissionServiceCode", "partenairePaye", "date", "details"],
            [
                trim($_POST['matriculeEtudiantEnSalle']),
                trim($_POST['fullname']),
                trim($_POST['codePromo']),
                $servicePartenarial[0]->status,
                $servicePartenarial[0]->idServicePartenarial,
                $commission,
                0,
                date('Y-m-d'),
                trim($_POST['details']),
            ],
            (int)$_POST['id']
        );

        $data['success'] = true;
        echo json_encode($data);
    }

    public function deleteServiceCode(string $id)
    {
        $this->isAdmin();
        $serviceCodeModel = new ServiceCodeModel();
        $data = $serviceCodeModel->getOne(htmlspecialchars(trim($id)));

        if (empty($data)) {
            echo json_encode(["error" => true]);
        } else {
            $serviceCodeModel->delete($id);
            echo json_encode(["success" => true]);
        }
    }

    public function recherche_service_code()
    {
        $this->isAdmin();
        $recherche = trim($_POST["recherche"]);
        $model = new ServiceCodeModel();
        $data["serviceCode"] = $model->recherche($recherche);
        Controllers::loadView("partenaire/admin/tableServiceCode.php", $data);
    }





    public function servicePartenarialPaiement()
    {
        $this->isAdmin();


        $model = new PartenaireModel();
        $data["partenaires"] = $model->all();

        if (isset($_POST['partenaire'])) {
            $validation = new FormValidation();
            $validation->required("partenaire", "Le séléction d'un partenaire est requis");

            if (!$validation->run()) {
                echo json_encode(['errorP' => $validation->getErrors()]);
                exit();
            }

            $matricule = htmlspecialchars(trim($_POST['partenaire']));

            $data['partenaire'] = $model->getOne($matricule)[0];

            $model = new ServiceCodeModel();
            $data['paiementList'] = $model->getAllCollaborateurForPartenaire($matricule);

            $parametres = new PartenaireParametreModel();
            $data['commission'] = $parametres->getOne(1)[0]->commission;
            Controllers::loadView("partenaire/admin/tablePaiementServicePartenarial.php", $data);
            exit();
        }


        Controllers::loadView("partenaire/admin/paiementServicePartenarial.php", $data);
    }

    public function recherche_paiement_service_partenarial()
    {
        $this->isAdmin();
        $recherche = htmlspecialchars(trim($_POST["recherche"]));
        $mois = htmlspecialchars(trim($_POST["mois"]));

        $serviceCodeModel = new ServiceCodeModel();
        $data['paiementList'] = $serviceCodeModel->searchGroupForPaiementPartenaire($mois, $recherche);
        $data['mois'] = $mois;

        $data['recherche'] = $recherche;
        Controllers::loadView("partenaire/admin/contentTableServicePartenarialPaiement.php", $data);
    }

    public function detailsPaiementServicePartenarial()
    {
        $this->isAdmin();
        $mois = htmlspecialchars(trim($_POST['mois']));
        $matricule = htmlspecialchars(trim($_POST['matricule']));

        $model = new PartenaireModel();
        $data = $model->getOne($matricule);
        if (empty($data)) {
            echo json_encode(["error" => true]);
            exit;
        }

        $model = new ServiceCodeModel();
        $data['detailsList'] = $model->getAllForPartenaire($mois, $matricule);

        $data['montant'] = 0;
        foreach ($data['detailsList'] as $key => $detail) {
            $data['montant'] = $data['montant'] + $detail->commissionServiceCode;
        }

        $date_debut = new DateTime(date($mois));
        $date_debut = $date_debut->format('Y-m-d');
        $data['moisLettre'] = Utility::formatMois($date_debut);

        Controllers::loadView("partenaire/admin/detailsPaiementServicePartenarial.php", $data);
    }

    public function payerServicePartenarial()
    {
        $this->isAdmin();
        $id = (int)$_POST['id'];
        $matricule = htmlspecialchars(trim($_POST['matricule']));


        $model = new ServiceCodeModel();
        $aPayer =  $model->getOne($id);

        foreach ($aPayer as $key => $paiement) {
            $model->update(["partenairePaye"], [1], (int)$paiement->idServiceCode);
        }


        $model = new ServiceCodeModel();
        $data['paiementList'] = $model->getAllCollaborateurForPartenaire($matricule);

        $parametres = new PartenaireParametreModel();
        $data['commission'] = $parametres->getOne(1)[0]->commission;

        Controllers::loadView("partenaire/admin/tablePaiementServicePartenarial.php", $data);
    }




    public function servicePartenarialPaiementHistorique()
    {
        $this->isAdmin();
        $model = new ServiceCodePaiementModel();
        $data['historiqueList'] = $model->all();

        Controllers::loadView("partenaire/admin/historiquePaiementServicePertanarial.php", $data);
    }

    public function recherche_historique_paiement_service_partenarial()
    {
        $this->isAdmin();
        $model = new ServiceCodePaiementModel();
        $recherche = trim($_POST["recherche"]);
        $data['historiqueList'] = $model->recherche($recherche);

        Controllers::loadView("partenaire/admin/tableHistoriquePaiementServicePartenarial.php", $data);
    }




    public function payerPartenaire()
    {
        $this->isAdmin();
        $id = (int)($_POST['id']);
        $matricule =  trim($_POST['matricule']);

        $fem = new FormationEtudiantModel();
        $aPayer = $fem->getOneForPaiement($id);

        foreach ($aPayer as $key => $paiement) {
            $fem->update(["partenairePaye"], [1], (int)$paiement->idFormationEtudiant);
        }

        $fem = new FormationEtudiantModel();
        $data['paiementList'] = $fem->getAllForPaiementPartenaire($matricule);

        $parametres = new PartenaireParametreModel();
        $data['commission'] = $parametres->getOne(1)[0]->commission;
        Controllers::loadView("partenaire/admin/tablePaiementPartenaire.php", $data);
        exit();
    }

    public function bourse($page = '')
    {

        $bourse = new BousrseModel();
        $data = $bourse->getAll();

        // * ici **************************/

        if (PAGINATION < count($data)) {
            $champ = count($data) / PAGINATION;
            $champ = round($champ);
        } else {
            $champ = 0;
        }
     

        $page = ($page > 1) ? $page  : 1;

        if ((int)$page == 0) {
            $start = (int)$page *  PAGINATION;
        } else {
            $start = ((int)$page - 1) * PAGINATION;
        }
        
        
        if (PAGINATION < count($data)) {
            $data = $bourse->getAllwithLimite(PAGINATION, $start);
        }
        $data[0]->pagination = $champ;

        if ($page == 0) {
            $page = 1;
        }
        $data[0]->page = $page;
         
        $data[0]->function = 'bourse';
        
          

        // * ici **************************/


        Controllers::loadView("dashboardBourse.php", $data);
    }
    public function getBourseByid()
    {
        $id = strip_tags(trim($_POST['id']));
        $bourse = new BousrseModel();

        $data = $bourse->getBourseByid($id);

        echo json_encode($data);
    }

    public function rechercheBourse($page = '')
    {
        $query = '';
        if (isset($_POST['query'])) {
            $query = trim(strip_tags($_POST['query']));
        } else {
            if ($query == '') {
                $query = trim(strip_tags($_POST['recherche']));
            }
        }


        $search = $query;
        $query = '%' . $query . '%';
        $bourse = new BousrseModel();
        $data = $bourse->getBourseBycreatary($query, '', '');

        // * ici **************************/
        if (PAGINATION < count($data)) {
            $champ = count($data) / PAGINATION;
            $champ = ceil($champ);
        } else {
            $champ = 0;
        }
        $page = ($page > 1) ? $page  : 1;

        if ((int)$page == 0) {
            $start = (int)$page *  PAGINATION;
        } else {
            $start = ((int)$page - 1) * PAGINATION;
        }
        if (PAGINATION < count($data)) {
            $data = $bourse->getBourseBycreatary($query, PAGINATION, $start);
        }
        $data[0]->pagination = $champ;
        $data[0]->function = 'rechercheBourse';

        if ($page == 0) {
            $page = 1;
        }
        $data[0]->query = $search;
        $data[0]->page = $page;
        // * ici **************************/



        Controllers::loadView("dashboardBourse.php", $data);
    }
    public function deletebourse()
    {
        $id = trim(strip_tags($_POST['id']));
        $bourse = new BousrseModel();
        $bourse->deletebourse($id);

        echo json_encode([
            'success' => true
        ]);
    }
}
