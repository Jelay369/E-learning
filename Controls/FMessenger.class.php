<?php
class FMessenger{
	private $messenger;
	public function __construct()
	{
        if (!isset($_SESSION["connected"]) || $_SESSION['role'] !== ROLE_USER[1]) {
            header('Location:' . BASE_URL);
            exit();
        }
		$this->messenger = new FMessengerModel();
	}
	public function index(){
		$fem = new FormationEtudiantModel();
		$data['etudiants'] = $fem->getAllConfirmedForMessengerFormateur();
		Controllers::loadView('formateur/messenger/index.php',$data);
	}

	public function getAllChatsFormateur()
	{
		$discussions = $this->messenger->getAllChatsFormateur();
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
        Controllers::loadView('formateur/messenger/list_discussion.php',$data);
	}
	public function getDiscussion()
    {
    	$etudiant = new EtudiantModel();
    	$matricule = htmlspecialchars(trim($_POST['matricule']));
    	$data['messages'] = $this->messenger->getDiscussionsWithOffset($matricule,$_SESSION['id'],5,0);
    	$data['etudiant'] = $etudiant->getOne($matricule);
        $data['totalMessageCount'] = $this->messenger->getCountMessage($matricule,$_SESSION['id']);
        $data['offset'] = 0;
    	Controllers::loadView('formateur/messenger/content_discussion.php',$data);
    }
    public function sendAttachement() {
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
                    [$idMessage,$matrEtudiant,$idFormateur,$idFormateur,$message,$pieceJointe,"0"]
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
            [$idMessage,$matrEtudiant,$idFormateur,$idFormateur,$message,$pieceJointe,"0"]
        );
        echo json_encode(['success' => true]);
    }
    public function getPrevious($offset)
    {
        $etudiant = new EtudiantModel();
        $offset = (int)$offset * 5;
        $matricule = $_POST['matricule'];
        $idFormateur = $_POST['idFormateur'];

        $data['messages'] = $this->messenger->getDiscussionsWithOffset($matricule,$_SESSION['id'],5,$offset);
        $data['etudiant'] = $etudiant->getOne($matricule);

        $countMessage = $this->messenger->getCountMessage($matricule,$idFormateur);

        $data['totalMessageCount'] = $countMessage - $offset;
        $data['offset'] = $offset;
        Controllers::loadView('formateur/messenger/list_message.php',$data);
    }
    public function getNext($offset)
    {
        $etudiant = new EtudiantModel();
        $offset = (int)$offset * 5;
        $matricule = $_POST['matricule'];
        $idFormateur = $_POST['idFormateur'];

        $data['messages'] = $this->messenger->getDiscussionsWithOffset($matricule,$_SESSION['id'],5,$offset);
        $data['etudiant'] = $etudiant->getOne($matricule);

        $countMessage = $this->messenger->getCountMessage($matricule,$idFormateur);

        $data['totalMessageCount'] = $countMessage - $offset;
        $data['offset'] = $offset;
        Controllers::loadView('formateur/messenger/list_message.php',$data);
    }
    public function searchDiscussion()
    {
        $query = htmlspecialchars(trim($_POST['query']));
        $discussions = [];
        if($query === "")
        {
            $discussions = $this->messenger->getAllChatsFormateur();
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
        Controllers::loadView('formateur/messenger/list_discussion.php',$data);
    }
    public function searchEtudiant()
    {
        $etudiant = new EtudiantModel();
        $query = htmlspecialchars(trim($_POST['query']));
        $etudiants = [];
        if($query === "")
        {
            $etudiants = $etudiant->getAllForMessengerFormateur();
        }
        else
        {
            $etudiants = $etudiant->searchEtudiantForMessengerFormateur($query);
        }
        $data['etudiants'] = $etudiants;
        Controllers::loadView('formateur/messenger/list_etudiant.php',$data);
    }
    public function updateUnreadMessage()
    {
        $idFormateur = $_SESSION['id'];
        $matricule = htmlspecialchars(trim($_POST['matricule']));
        $this->messenger->updateUnreadMessage($idFormateur,$matricule);
        $unread = $this->messenger->getUnreadMessage($matricule);
        echo json_encode(["success" => true,"unread"=>$unread]);
    }
    public function getUnreadMessage()
    {
        $idFormateur = $_SESSION['id'];
        $unread = $this->messenger->getUnreadMessage($idFormateur);
        echo json_encode(["success" => true,"unread"=>$unread]);
    }

}
