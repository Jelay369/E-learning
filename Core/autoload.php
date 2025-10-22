<?php 
	function chargerClasse($classe){
  			
  			if (file_exists("Controls/".$classe.'.class.php')) {
  				require "Controls/".$classe.'.class.php'; 
  			}
  			else if (file_exists("Models/".$classe.'.class.php'))  {
  				require "Models/".$classe.'.class.php'; 
  			}
        else if(file_exists("Models/".$classe.'.php')) {
          require "Models/".$classe.'.php'; 
        }
  			else if (file_exists("Core/".$classe.'.class.php')) {
  				require "Core/".$classe.'.class.php'; 
  			}
}

	spl_autoload_register('chargerClasse'); // On enregistre la fonction en autoload pour qu'elle soit appelée dès qu'on instanciera une classe non déclarée.

// UploadAjaxABCIServeur.php
 ?>