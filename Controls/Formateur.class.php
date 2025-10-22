<?php
class Formateur{
    const UPLOAD_FOLDER = 'images/formateur';
    const ALLOWED_EXTENSION = ['jpeg','jpg','png'];
    public function __construct()
    {
        if(isset($_SESSION["connected"]))
        {
         if($_SESSION["role"] === ROLE_USER[0])
         {
           Controllers::loadView("403.php");
           exit();
         }
        }
        else{
            header("Location:".BASE_URL."/connection/formateur");
            exit();
        }
    }
    public function index()
    {
        if($_SESSION["role"] !== ROLE_USER[2])
        {
            Controllers::loadView("403.php");
            exit();
        }
        $model = new FormateurModel();
        $id = $model->generateID();
        $data['formateurs'] = $model->getAll();
        $data['newId'] = $id;
        Controllers::loadView('addFormateur.php',$data);
    }
    public function add()
    {
        if($_SESSION["role"] !== ROLE_USER[2])
        {
            Controllers::loadView("403.php");
            exit();
        }
       $validation = new FormValidation();
       $fileController = new FileController($_FILES,self::UPLOAD_FOLDER);
       $validation->requiredAll($_POST);
       $validation->uniq('formateur','idFormateur','Identifiant dejà utilisé');

       if(!$validation->run())
       {
           echo json_encode($validation->getErrors());
           exit();
       }
       if($_FILES['photoFormateur']['name'] !== '')
       {
            $fileController->verifyExtension(self::ALLOWED_EXTENSION);
            if(!empty($fileController->getErrors()))
            {
                echo json_encode($fileController->getErrors());
                exit();
            }
            $fileController->upload();
       }

       $model = new FormateurModel();
      // $user = new User();
       $fields = [
           "idFormateur","passwordFormateur","fullname",
           "lastname","specialite","fonction",
           "facebook",'email','telephone',
           "photoFormateur"];
       $values = [
            htmlspecialchars( trim($_POST['idFormateur']) ),
            password_hash("password",PASSWORD_DEFAULT),
            htmlspecialchars( trim($_POST['fullname']) ),
            htmlspecialchars( trim($_POST['lastname']) ),
            htmlspecialchars( trim($_POST['specialite']) ),
            htmlspecialchars( trim($_POST['fonction']) ),
            htmlspecialchars( trim($_POST['facebook']) ),
            htmlspecialchars( trim($_POST['email']) ),
            htmlspecialchars( trim($_POST['telephone']) ),
            $fileController->getFilename()
       ];
       $model->create($fields,$values);
    //   $user->add(htmlspecialchars( trim($_POST['idFormateur']) ),ROLE_USER[1]);
       $data['success'] = true;
       echo json_encode($data);
    }
    public function getAll(){
        if($_SESSION["role"] !== ROLE_USER[2])
        {
            Controllers::loadView("403.php");
            exit();
        }
        $model = new FormateurModel();
        $data = $model->getAll();
        echo json_encode($data);
    }
    public function get($id)
    {
        $model = new FormateurModel();
        $data = $model->getOne($id);
        echo json_encode($data);
    }
    public function update()
    {
        $validation = new FormValidation();
        $fileController = new FileController($_FILES,self::UPLOAD_FOLDER);
        $fields = ['fullname','lastname','fonction','specialite','facebook','email','telephone'];
        $validation->requiredAll($_POST);
        if(!$validation->run())
       {
           echo json_encode($validation->getErrors());
           exit();
       }
       $values = [
           htmlspecialchars(trim( $_POST['fullname'] )),
           htmlspecialchars(trim( $_POST['lastname'] )),
           htmlspecialchars(trim( $_POST['fonction'] )),
           htmlspecialchars(trim( $_POST['specialite'] )),
           htmlspecialchars( trim($_POST['facebook']) ),
           htmlspecialchars( trim($_POST['email']) ),
           htmlspecialchars( trim($_POST['telephone']) )
       ];
       if($_FILES['photoFormateur']['name'] !== '')
       {
            $fileController->verifyExtension(self::ALLOWED_EXTENSION);
            if(!empty($fileController->getErrors()))
            {
                echo json_encode($fileController->getErrors());
                exit();
            }
            $fileController->upload();
            $fields[] = 'photoFormateur';
            $values[] = $fileController->getFilename();
       }
       $model = new FormateurModel();

       $model->update($fields,$values,htmlspecialchars(trim( $_POST['idFormateur'] )));

       $data['success'] = true;
       $data['formateur'] = $values;
       echo json_encode($data);
    }
    public function updateProfil()
    {
        $validation = new FormValidation();
        $validation->requiredAll($_POST,"Vous devez renseigner le champ");
        if(!$validation->run())
        {
            echo json_encode(["error" => "Tous les champs sont requis"]);
            exit();
        }
        $fm = new FormateurModel();
        $nom = htmlspecialchars(trim($_POST['nom']));
        $prenom = htmlspecialchars(trim($_POST['prenom']));
        $facebook = htmlspecialchars(trim($_POST['facebook']));
        $email = htmlspecialchars(trim($_POST['email']));
        $telephone = htmlspecialchars(trim($_POST['telephone']));

        $fm->update(["fullname","lastname","facebook","email","telephone"],
                    [$nom,$prenom,$facebook,$email,$telephone],$_SESSION['id']);
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
        $fm = new FormateurModel();
        $formateur = $fm->getOne($_SESSION['id']);
        if(! password_verify($password, $formateur->passwordFormateur))
        {
            echo json_encode(["error" => "Mot de passe incorrect"]);
            exit();
        }
        if($npassword !== $cpassword)
        {
            echo json_encode(["error" => "Les 2 mots de passe doit être identique"]);
            exit();
        }
        $fm->update(["passwordFormateur"],[password_hash($npassword, PASSWORD_DEFAULT)],$_SESSION['id']);
        echo json_encode(["success" => true]);
    }
    public function updatePhoto()
    {
        $fc = new FileController($_FILES,self::UPLOAD_FOLDER);
        $fc->verifyExtension(self::ALLOWED_EXTENSION);
        if(!$fc->valideFile()){
            echo json_encode($fc->getErrors());
            exit();
        }
        $fc->upload();
        $fm = new FormateurModel();
        $fm->update(["photoFormateur"],[$fc->getFilename()],$_SESSION['id']);
        echo json_encode(["success"=>true,"photo" => $fc->getFilename()]);
    }
    public function delete($id)
    {
        if($_SESSION["role"] !== ROLE_USER[2])
        {
            Controllers::loadView("403.php");
            exit();
        }
        $model = new FormateurModel();
        $mess = new FMessengerModel();
        $model->delete($id);
        $mess->delete($id);
        echo json_encode(['success'=>true]);
    }

}
