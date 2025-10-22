<?php
class FormationEtudiant{

    public function add()
    {
        $etudiantModel = new EtudiantModel();
        $etudiant = $etudiantModel->getOne( htmlspecialchars(trim($_POST["matriculeEtudiant"])));
        if(!$etudiant)
        {
            echo json_encode(["notFound"=>true]);
            exit();
        }
        $validation = new FormValidation();
        $validation->requiredAll($_POST);
        $validation->uniq2("formation_etudiant",["matriculeEtudiant","idFormation"],[
            htmlspecialchars(trim($_POST["matriculeEtudiant"])),
            (int)$_POST["idFormation"]
        ],"Vous avez déjà inscris à cette formation");
        if(!$validation->run()){
            echo json_encode($validation->getErrors());
            exit();
        }
        if (!isset($_POST["idFormation"])){
            echo json_encode(["error"=>true]);
            exit();
        }
        $model = new FormationEtudiantModel();
        $date = new DateTime();

        $model->create(["idFormationEtudiant","matriculeEtudiant","idFormation","confirmInscription"],[
            $date->getTimestamp(),
            htmlspecialchars(trim($_POST["matriculeEtudiant"])),
            (int)$_POST["idFormation"],
            0
        ]);
        echo json_encode(["success"=>true]);
    }
}