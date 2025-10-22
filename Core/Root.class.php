<?php
class Root{
	public static $test="" ;


	public static function executer($url,$page){
		$page = htmlspecialchars(trim($page)) ;
		$url = htmlspecialchars(trim($url)) ;
		$app_url ="" ;
			$controlName ="" ; // nom du controllers
			$methodeName ="" ; // nom de la méthode
			$param = array() ; // liste de paramettre
			// tester s'il y a un /
			if (strpos($url,"/")>-1) {
				$app_url = explode("/",$url) ;
				$controlName = $app_url[0] ;
				$methodeName = $app_url[1] ;
				if (count($app_url)>2) {
					$j = 0 ;
					for ($i=2; $i <count($app_url) ; $i++) {
						$param[$j]  = $app_url[$i] ;
						$j++ ;
					}
				}


			}
			else {
				$controlName = $url ;
			}
			$controlName = ucfirst($controlName) ;
			// vérifier si la classe (controller) exite
			if (file_exists("Controls/".$controlName.'.class.php') ) {

				if (method_exists ($controlName,$methodeName)) {
					if (count($param)==0) {
						// execution methode
						$reflect = new ReflectionMethod($controlName,$methodeName);
						$reflect->invoke(new $controlName);
					}
					else {
						$reflect = new ReflectionMethod($controlName,$methodeName);

						$reflect->invokeArgs(new $controlName,$param);
					}

				}

				else if(!method_exists($controlName,$methodeName) || $methodeName="") {
					$methodeName="index" ;
					if($methodeName === ""){
						$reflect = new ReflectionMethod($controlName,$methodeName);
						$reflect->invoke(new $controlName);
					}else{
						$parts = explode("/", trim($url, "/"));
						$params = [];
						foreach ($parts as $key => $value)
						{
							if($key > 0)
							{
								$params[] = $value;
							}
						}
						$reflect = new ReflectionMethod($controlName,$methodeName);
						$reflect->invokeArgs(new $controlName,$params);
					}

				}



			}
			else {
					// mbola soloina
				Controllers::loadView($page) ;

			}
		}
	}

	?>
