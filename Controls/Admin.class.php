<?php
class Admin{

    public function index()
    {
        if(isset($_SESSION['connected']) && $_SESSION["role"]===ROLE_USER[2])
        {
            header('Location: '. BASE_URL .'/dashboard');
        }
        else
        {
            header('Location: '. BASE_URL .'/connection');
            exit();
        }
    }

    public function edit()
    {
        if( ! isset($_SESSION['connected']) || $_SESSION["role"] !== ROLE_USER[2] )
        {
            header('Location: '. BASE_URL .'/connection');
            exit();
        }
        $model = new AdminModel();
        $data['admin'] = $model->getOne((int)$_SESSION['id']);
        Controllers::loadView('editPassword.php',$data);
    }
    
    public function updateAccount()
    {
        if( ! isset($_SESSION['connected']) || $_SESSION["role"] !== ROLE_USER[2] )
        {
            header('Location: '. BASE_URL .'/connection');
            exit();
        }
      $errors = null;
       $validation = new FormValidation();
       $validation->requiredAll($_POST)
                ->passwordMin($_POST['new-password'],8);
       if(!$validation->run())
       {
           $errors['form'] = $validation->getErrors();
       }
        $model = new AdminModel();
        $admin = $model->getOne((int)$_SESSION['id']);

       if( !password_verify($_POST['password'],$admin->password) )
       {
           $errors['incorrect'] = 'Mot de passe incorrect';
       }
       if( $_POST['new-password'] !== $_POST['new-password-confirm'] )
       {
           $errors['confirm'] = 'Les deux mots de passe ne correspondent pas';
       }
       if($errors)
       {
           echo json_encode($errors);
           exit();
       }

       $fields = ['username','password'];
       $values = [htmlspecialchars(trim($_POST['username'])) , password_hash($_POST['new-password'],PASSWORD_DEFAULT)];
       $model->update($fields,$values,(int)$_SESSION['id']);
       echo json_encode(['success'=>true]);
    }
}
