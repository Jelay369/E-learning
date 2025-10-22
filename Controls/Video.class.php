<?php
class Video{
    public function __construct()
    {
        if(!isset($_SESSION["connected"])){
            header('Location:'.BASE_URL);
            exit();
        }
    }
    public function play($idChapitre,$title)
    {
        $fem = new FormationEtudiantModel();
        if($fem->is_singin((int)$idChapitre,$_SESSION['matricule'])){
            $player = new Player(dirname(__DIR__)."/Publics/upload/video/cours/" . urldecode($title));
            $player->start();
        }else{
            echo "Error 404 : not found";
        }

    }
}
