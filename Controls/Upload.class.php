<?php 

	class Upload {
		public function index() {
			$up = new UploadAjax("../Publics/upload/messenger/",'../Publics/Upload_Temp/') ;
			$unidid_form = $up->getUniqidForm();
            if(!(isset($unidid_form,$_SESSION['UploadAjaxABCI'][$unidid_form]))){
                $up->exitErreurFichier('Identifiant de formulaire non valide. Rafraîchissez la page');
            }  
            $up->Upload() ; 
            $up->Transfert();
            
            if($up->getFichierOk()) {
                chmod(dirname(__DIR__)."/Publics/upload/messenger/".$up->getFichierNomNettoye(), 0777);
            }

            $responses = $up->getResponseAjax() ;
            $responses['filename'] = $up->getFichierNomNettoye() ;
            $pieceJointeContent = $up->getFichierNomNettoye() ;
            $db = new Database() ;
            $matrEtudiant = $_SESSION["matricule"] ;
            $idFormateur = htmlspecialchars(trim($_POST["idFormateur"])) ;
            $pieceJointe = null ;
            $message = null ;
            $idMessage = microtime() ;


            if($_POST["my_message"] != "") {
            	$message = ($_POST["my_message"]) ;
            } 

            // echo $_POST["my_message"] ;

            if($pieceJointeContent != "") {
            	$pieceJointe = $pieceJointeContent ;
            }

            $responses['idFormateur'] = $idFormateur;
            $db->insert("messages")
               ->parametters(array("idMessage","matrEtudiant","idFormateur","sender","contentMessage","pieceJointe","isReadMessage")) 
               ->execute(array($idMessage,$matrEtudiant,$idFormateur,$matrEtudiant,utf8_encode($message),$pieceJointe,"0")) ;

            echo json_encode($responses);

		}
	}

	


 ?>


 