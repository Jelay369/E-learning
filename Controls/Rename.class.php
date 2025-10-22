<?php 

class Rename {
    public function index() {
        $scandir = scandir("./Publics/upload/video/cours");
        foreach($scandir as $fichier){

            if($fichier == '.' || $fichier == '..'){
            }else{
                $separator = ' ';
                if (strpos($fichier, $separator) !== false){
                   
                    $nomFichier = str_replace($separator,"_",$fichier);
                    echo($nomFichier.'<br/>');
                    
                    $nomfichierinitial= './Publics/upload/video/cours/'.$fichier;
                    $nomfichierfinal='./Publics/upload/video/cours/'.$nomFichier;
    
                    rename ($nomfichierinitial, $nomfichierfinal);
                }
            }
        }
        echo('ok');
    }
}

// UPDATE chapitre SET videoChapitre = replace(videoChapitre, '-','_')


?>