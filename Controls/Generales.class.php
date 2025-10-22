<?php
class Generales{
    const ALLOWED_EXTENSION = ["jpg","jpeg","png","gif"];
    const UPLOAD_FOLDER = "images";

    public function home()
    {

    }

    public function updateHome($field)
    {
        $validation = new FormValidation();
        $validation->requiredAll($_POST,'Ce champ ne peut pas être vide');
        if(!$validation->run())
        {
            echo json_encode($validation->getErrors());
            exit();
        }
        $field = trim(htmlspecialchars($field));
        $model = new GeneralesModel('home');

        switch ($field) {
            case 'title':
            $model->update(["title"],[htmlspecialchars(trim($_POST['title']))],1);
            break;
            case 'slogan':
            $model->update(["slogan"],[htmlspecialchars(trim($_POST['slogan']))],1);
            break;
            case 'title-article':
            $model->update(["title_article"],[htmlspecialchars(trim($_POST['title-article']))],1);
            break;
            case 'content-article':
            $model->update(["content_article"],[htmlspecialchars(trim($_POST['content-article']))],1);
            break;
            case 'ifram':
            $model->update(["ifram"],[htmlspecialchars(trim($_POST['ifram']))],1);
            break;
            default:
                # code...
            break;
        }
        echo json_encode(["success"=>true]);

    }
    public function updateBackground()
    {
        //var_dump($_FILES);
        $fileController = new FileController($_FILES,"images/home");
        $fileController->verifyExtension(self::ALLOWED_EXTENSION);
        if(!empty($fileController->getErrors()))
        {
            echo json_encode($fileController->getErrors);
            exit();
        }
        $fileController->upload();
        $model = new GeneralesModel('home');
        $model->update(["image"],[$fileController->getFilename()],1);
        echo json_encode(['success'=>true,"image"=>$fileController->getFilename()]);
    }

    public function section()
    {
       $errors = null;
        if(trim($_FILES['image']['name']) === '')
        {
            $errors['requiredImage'] = 'Vous devez choisir une image de fond';
        }
        else if(trim($_FILES['image']['name']) !== '' && !FileController::extension($_FILES,self::ALLOWED_EXTENSION))
        {
            $errors['extensionImage'] = 'Format non supporté';
        }
        if(empty(trim($_POST['title'])) || empty(trim($_POST['content'])) )
        {
            $errors['requiredPost'] = 'Tous les champs sont obligatoires';
        }
        if($errors)
        {
            echo json_encode($errors);
            exit();
        }
        $fc = new FileController($_FILES,self::UPLOAD_FOLDER);
        $generale = new GeneralesModel("section");

        $fc->upload();
        $generale->createSection($_POST,$fc->getFilename());

        $responses['success'] = true;
        $responses['post'] = $generale->getLastData();
        $responses['file'] = true;

        echo json_encode($responses);

    }
    
    public function updateSection()
    {
        $errors = null;
        $fields = [];
        $values = [];
        if(isset($_POST))
        {
            $validation = new FormValidation();
            $validation->requiredAll($_POST);
            if(!$validation->run())
            {
                echo json_encode(["error" => "Tous les champs sont requis"]);
                exit();
            }
        }
        if(!empty($_FILES))
        {
            if($_FILES['image']['name'] !== '')
            {
                if(!FileController::extension($_FILES,self::ALLOWED_EXTENSION))
                {
                    echo json_encode(["error" => 'Extension de fichier non autorisé']);
                    exit();
                }
            }
        }

        foreach($_POST as $key=>$post)
        {
            if($key !== 'id')
            {
                $fields[] = $key;
                $values[] = $post;
            }

        }

        if($_FILES['image']['name'] !== '')
        {
            $fc = new FileController($_FILES,"images/section");
            $fc->upload();
            $fields[] = 'image';
            $values[] = $fc->getFilename();
        }

        $generale = new GeneralesModel("section");

        $generale->update($fields,$values,(int)$_POST['id']);

        $responses['success'] = true;
        $responses['post'] = $_POST;
        $responses['file'] = true;

        echo json_encode($responses);
    }

    public function deleteSection($id)
    {
        $generale = new GeneralesModel("section");
        $generale->delete((int)$id);
        echo json_encode(["success","Elément supprimé"]);
    }

    public function addPaiement()
    {
        $validation = new FormValidation();
        $validation->requiredAll($_POST);
        if(!$validation->run())
        {
            echo json_encode($validation->getErrors());
            exit();
        }
        $pm = new PaiementModel();
        $result = $pm->getOne(1);
        if(empty($result)){
            $pm->create(["mvola","airtelMoney","orangeMoney"],[
                htmlspecialchars(trim($_POST["mvola"])),
                htmlspecialchars(trim($_POST["airtelMoney"])),
                htmlspecialchars(trim($_POST["orangeMoney"])),
            ]);
        }else{
            $pm->update(["mvola","airtelMoney","orangeMoney"],[
                htmlspecialchars(trim($_POST["mvola"])),
                htmlspecialchars(trim($_POST["airtelMoney"])),
                htmlspecialchars(trim($_POST["orangeMoney"])),
            ],1);
        }
        echo json_encode(["success" => true]);
    }

    public function updateVideo()
    {
        $this->isAdmin();

        $up = new UploadAjax("../Publics/Video/",'../Publics/Upload_Temp/') ;

        $unidid_form = $up->getUniqidForm();
        if(!(isset($unidid_form,$_SESSION['UploadAjaxABCI'][$unidid_form]))){
            $up->exitErreurFichier('Identifiant de formulaire non valide. Rafraîchissez la page');
        }

        $up->Upload() ;
        $up->Transfert();

        $responses = $up->getResponseAjax() ;

        if($up->getFichierOk()) {
            $model = new GeneralesModel("home");
            chmod(dirname(__DIR__)."/Publics/Video/".$up->getFichierNomNettoye(), 0777);
            $video = $up->getFichierNomNettoye() ;
            $model->update(['video_home'],[$video],1);
            $responses['filename'] = $video;
        }

        echo json_encode($responses);
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

}
