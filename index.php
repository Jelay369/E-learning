<?php
/*define('BASE_URL', 'https://tuto-info.com');*/
define('BASE_URL', 'http://localhost/tuto');
define('ROLE_USER', ["etudiant", "formateur", "admin", 'partenaire']);
define('MAX_FRAGMENT_UPLOAD', 6291456);
define('VERSION', "2023.08.29 08:32");
session_start();
define('PAGINATION', 10);



// autoload
// insertion autoload
include("Core/autoload.php");
require('vendor/autoload.php');



// ======== Version navigateur ======//
// $bd = new BrowserDetection();
// $b_name = $bd->getName();
// $b_version = $bd->getVersion();

// $old_browser = false;

// if (strtolower($b_name) === "internet explorer" && (int)$b_version <= 11) {
//     $old_browser = true;
// }
// ======== Version navigateur ======//


// model
if (isset($_GET["action"])) {
    if ($_GET["action"] != "") {
        // mbola esorina
        if ($_GET["action"] == "Formation" || $_GET["action"] == "formation" || $_GET["action"] == "formation/" || $_GET["action"] == "Formation/" || $_GET["action"] == "Formation/index/" || $_GET["action"] == "formation/index/" || $_GET["action"] == "formation/index" || $_GET["action"] == "Formation/index") 
        {
            header("location:" . BASE_URL);
        } else {
            Root::executer($_GET["action"], "error.php");
        }
    } else {

        $data = null;
        $front = new Front();
        $data = $front->getData();

        // ======== Version navigateur ======//
        // if ($old_browser) {
        //     $data['old_browser'] = true;
        // }
        // ======== Version navigateur ======//
        Controllers::loadView("index.php", $data);
    }
} else {
    $data = null;
    $front = new Front();
    $data = $front->getData();
    // ======== Version navigateur ======//
    // if ($old_browser) {
    //     $data['old_browser'] = true;
    // }
    // ======== Version navigateur ======//
    Controllers::loadView("index.php", $data);
}
