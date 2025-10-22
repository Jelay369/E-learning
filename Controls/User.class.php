<?php
class User{
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

    public function add(string $username,string $role)
    {
        $model = new UserModel();
        $model->create(["username","password","role"],[
            $username,
            password_hash("password",PASSWORD_DEFAULT),
            $role
        ]);
    }
    public function edit($idFormateur)
    {
        $fm = new FormateurModel();
        $data['formateur'] = $fm->getOne(htmlspecialchars($idFormateur));
        Controllers::loadView("editAccountFormateur.php",$data);
    }
}