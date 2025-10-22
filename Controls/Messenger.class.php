<?php 
class Messenger{
	private $messenger;

	public function __construct()
	{
        if (!isset($_SESSION['connected']) || $_SESSION['role'] !== ROLE_USER[0]) {
            header('Location: ' . BASE_URL);
            exit();
        }
        $etu = new EtudiantModel();
        if(!$etu->hasConfirmInscription($_SESSION['matricule']))
        {
            echo "unauthorized";
            exit();
        }
        $this->messenger = new MessengerModel();
    }

    public function getAllChatsEtudiant()
    {
        $discussions = $this->messenger->getAllChatsEtudiant();
        for($i=0;$i<count($discussions)-1;$i++)
        {
            for($j=$i+1;$j<count($discussions);$j++)
            {
                if($discussions[$i]->dateMessage < $discussions[$j]->dateMessage)
                {
                    $tmp = $discussions[$i];
                    $discussions[$i] = $discussions[$j];
                    $discussions[$j] = $tmp;
                }
            }
        }
        $data['discussions'] = $discussions;
        Controllers::loadView('etudiant/messenger/list_discussion.php',$data);
    }

    public function getDiscussion()
    {
    	$formateur = new FormateurModel();
    	$idFormateur = htmlspecialchars(trim($_POST['idFormateur']));
    	$data['messages'] = $this->messenger->getDiscussionsWithOffset($idFormateur,$_SESSION['matricule'],5,0);
    	$data['formateur'] = $formateur->getOne($idFormateur);
        $data['totalMessageCount'] = $this->messenger->getCountMessage($idFormateur,$_SESSION['matricule']);
        $data['offset'] = 0;
        Controllers::loadView('etudiant/messenger/content_discussion.php',$data);
    }

    public function sendAttachement() 
    {
        $up = new UploadAjax("../Publics/upload/messenger/",'../Publics/Upload_Temp/') ;
        $unidid_form = $up->getUniqidForm();

        if(!(isset($unidid_form,$_SESSION['UploadAjaxABCI'][$unidid_form]))){
            $up->exitErreurFichier('Identifiant de formulaire non valide. Rafraîchissez la page');
        }  

        $up->Upload() ; 
        $up->Transfert();
    
        $responses = $up->getResponseAjax() ;
        if($up->getFichierOk()) {
            chmod(dirname(__DIR__)."/Publics/upload/messenger/".$up->getFichierNomNettoye(), 0777);
            $responses['filename'] = $up->getFichierNomNettoye() ;
            $matrEtudiant = htmlspecialchars(trim($_POST["matricule"])) ;
            $idFormateur = htmlspecialchars(trim($_POST["idFormateur"])) ;
            $pieceJointe = $up->getFichierNomNettoye() ;
            $message = null ;
            $idMessage = microtime() ;

            if($pieceJointe !== "" && !is_null($pieceJointe))
            {
                $this->messenger->create(
                    ["idMessage","matrEtudiant","idFormateur","sender","contentMessage","pieceJointe","isReadMessage"],
                    [$idMessage,$matrEtudiant,$idFormateur,$matrEtudiant,$message,$pieceJointe,"0"]
                );
            }
        }

        echo json_encode($responses);
    }

    public function sendMessage()
    {
        $matrEtudiant = htmlspecialchars(trim($_POST["matricule"])) ;
        $idFormateur = htmlspecialchars(trim($_POST["idFormateur"])) ;
        $pieceJointe = null ;
        $message = htmlspecialchars(trim($_POST['my_message'])) ;
        $idMessage = microtime() ;

        if($message === "")
        {
            exit();
        }

        $this->messenger->create(
            ["idMessage","matrEtudiant","idFormateur","sender","contentMessage","pieceJointe","isReadMessage"],
            [$idMessage,$matrEtudiant,$idFormateur,$matrEtudiant,$message,$pieceJointe,"0"]
        );
        echo json_encode(['success' => true]);
    }

    public function getPrevious($offset)
    {
        $formateur = new FormateurModel();
        $offset = (int)$offset * 5;
        $matricule = $_POST['matricule'];
        $idFormateur = $_POST['idFormateur'];

        $data['messages'] = $this->messenger->getDiscussionsWithOffset($idFormateur,$_SESSION['matricule'],5,$offset);
        $data['formateur'] = $formateur->getOne($idFormateur);

        $countMessage = $this->messenger->getCountMessage($idFormateur,$matricule);

        $data['totalMessageCount'] = $countMessage - $offset;
        $data['offset'] = $offset;
        Controllers::loadView('etudiant/messenger/list_message.php',$data);
    }

    public function getNext($offset)
    {
        $formateur = new FormateurModel();
        $offset = (int)$offset * 5;
        $matricule = $_POST['matricule'];
        $idFormateur = $_POST['idFormateur'];

        $data['messages'] = $this->messenger->getDiscussionsWithOffset($idFormateur,$_SESSION['matricule'],5,$offset);
        $data['formateur'] = $formateur->getOne($idFormateur);

        $countMessage = $this->messenger->getCountMessage($idFormateur,$matricule);

        $data['totalMessageCount'] = $countMessage - $offset;
        $data['offset'] = $offset;
        Controllers::loadView('etudiant/messenger/list_message.php',$data);
    }

    public function searchDiscussion()
    {
        $query = htmlspecialchars(trim($_POST['query']));
        $discussions = [];
        if($query === "")
        {
            $discussions = $this->messenger->getAllChatsEtudiant();
        }
        else
        {
            $discussions = $this->messenger->searchDiscussion($query);
        }
        for($i=0;$i<count($discussions)-1;$i++)
        {
            for($j=$i+1;$j<count($discussions);$j++)
            {
                if($discussions[$i]->dateMessage < $discussions[$j]->dateMessage)
                {
                    $tmp = $discussions[$i];
                    $discussions[$i] = $discussions[$j];
                    $discussions[$j] = $tmp;
                }
            }
        }
        $data['discussions'] = $discussions;
        Controllers::loadView('etudiant/messenger/list_discussion.php',$data);
    }

    public function searchFormateur()
    {
        $formateur = new FormateurModel();
        $query = htmlspecialchars(trim($_POST['query']));
        $formateurs = [];
        if($query === "")
        {
            $formateurs = $formateur->getAllForMessenger();
        }
        else
        {
            $formateurs = $formateur->searchFormateur($query);
        }
        $data['formateurs'] = $formateurs;
        Controllers::loadView('etudiant/messenger/list_formateur.php',$data);
    }

    public function updateUnreadMessage()
    {
        $idFormateur = htmlspecialchars(trim($_POST['idFormateur']));
        $matricule = $_SESSION['matricule'];
        $this->messenger->updateUnreadMessage($idFormateur,$matricule);
        $unread = $this->messenger->getUnreadMessage($matricule);
        echo json_encode(["success" => true,"unread"=>$unread]);
    }

    public function getUnreadMessage()
    {
        $matricule = $_SESSION['matricule'];   
        $unread = $this->messenger->getUnreadMessage($matricule);
        echo json_encode(["success" => true,"unread"=>$unread]);
    }
}