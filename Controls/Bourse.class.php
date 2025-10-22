<?php
class Bourse
{
    public function index()
    {
        $home = new GeneralesModel("home");
        $home_data = $home->getLastData();

        if ($home_data === null) {
            $home->createHome();
        }

        if(isset($_SESSION['bourse_error']) && $_SESSION['bourse_error'] == true)
        {
            $data['error'] =  ['Vous êtes déja inscrit !'];
        }
        unset($_SESSION['bourse_error']);

        $data["home"] = $home_data;
        Controllers::loadView("action/head.php", $data);
        Controllers::loadView("accueil/loading.php", $data);

        Controllers::loadView("action/header.php", $data);

        Controllers::loadView("action/formulaire/bourse.php", $data);

        Controllers::loadView("action/footer.php", $data);
    }

    public function validation()
    {
        $bourse = new BousrseModel();

        $nom = strip_tags(trim($_POST['name']));
        $prenom = strip_tags(trim($_POST['lastname']));
        $date = strip_tags(trim($_POST['date']));
        $lieuNaiss = strip_tags(trim($_POST['lieu']));
        $address = strip_tags(trim($_POST['address']));
        $num = strip_tags(trim($_POST['num']));
        $mail = strip_tags(trim($_POST['mail']));
        $facebook = strip_tags(trim($_POST['facebook']));

        $formation = strip_tags(trim($_POST['formation']));

        if (isset($_POST['ordinateur']) &&  isset($_POST['info'])) {
            $ordinateur = strip_tags(trim($_POST['ordinateur']));
            $info = strip_tags(trim($_POST['info']));

            $filds = ['nom', 'prenom', 'date_naissance', 'lieu_naissance', 'address', 'telephone', 'email', 'facebook', 'formation', 'a_ordi', 'niveau_info'];
            $values = [$nom, $prenom, $date, $lieuNaiss, $address, $num, $mail, $facebook, $formation, $ordinateur, $info];
        } else {
            $filds = ['nom', 'prenom', 'date_naissance', 'lieu_naissance', 'address', 'telephone', 'email', 'facebook', 'formation'];
            $values = [$nom, $prenom, $date, $lieuNaiss, $address, $num, $mail, $facebook, $formation];
        }



        $test = $bourse->getBourse([$num, $mail]);

        if ($test == '') {

            $bourse->register($filds, $values);

            //* renomer les fichiers 
            $post = ['cin', 'c_residence',  'Cbonne_c', 'Ctravail', 'releve', 'bulletin'];
            $champs_file = ['cin', 'c_residence',  'c_bonne_c', 'c_travail', 'releve_bacc  ', 'bulletin_terminale'];

            $id = $bourse->getBourse([$num, $mail])->idPersonne;
            $values = [];

            for ($i = 0; $i < count($post); $i++) {

                $name = $_FILES['cin']['name'];
                $name = explode('.', $name);

                $ext = $name[count($name) - 1];

                $new_name =  $nom . '-' . $post[$i] . '-' . $id . '.' . $ext;

                $values[] = 'Publics/upload/bourse/' . $post[$i] . '/' . $new_name;


                $go = move_uploaded_file($_FILES[$post[$i]]['tmp_name'], 'Publics/upload/bourse/' . $post[$i] . '/' . $new_name);
            }
            $values[] = $id;

            $bourse->inserrt_file($champs_file, $values);
            $_SESSION['bourse_success'] = true; 
            header("Location: " . BASE_URL.'/Bourse/enregitre');

        } else {
            $_SESSION['bourse_error'] = true; 
            header("Location: " . BASE_URL.'/Bourse');
        }
        
        header("Location: " . BASE_URL.'/Bourse/enregitre');
    }

    public function enregitre()
    {
        $home = new GeneralesModel("home");
        $home_data = $home->getLastData();

        if ($home_data === null) {
            $home->createHome();
        }

        
        $data["home"] = $home_data;
        Controllers::loadView("action/head.php", $data);
        Controllers::loadView("accueil/loading.php", $data);

        Controllers::loadView("action/header.php", $data);

        Controllers::loadView("action/formulaire/enregistre.php", $data);

        Controllers::loadView("action/footer.php", $data);
    }

    public function condition()
    { 
        $home = new GeneralesModel("home");
        $home_data = $home->getLastData();

        if ($home_data === null) {
            $home->createHome();
        }

        
        $data["home"] = $home_data;
        Controllers::loadView("action/head.php", $data);
        Controllers::loadView("accueil/loading.php", $data);

        Controllers::loadView("action/header.php", $data);

        Controllers::loadView("action/formulaire/politique.php", $data);

        Controllers::loadView("action/footer.php", $data);
    }
}
