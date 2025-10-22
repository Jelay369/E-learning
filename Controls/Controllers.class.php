<?php 
class Controllers{

	public static function loadView(string $page, ?array $data = null){
		if($data){
			foreach($data as $key=>$value){
				$$key = $value;
			}
		}
		$page = htmlspecialchars(trim($page)) ;
		include("Views/".$page) ;
	}

	// pour inclure les portions de page
	public static function includeTemplate(string $template,?array $data=null){
		if($data){
			foreach($data as $key=>$value){
				$$key = $value;
			}
		}
		$template = htmlspecialchars(trim($template)) ;
		include("Views/template-part/".$template);
	}

	// pour inclure du JavaScript
	public static function includeScript($tab) {
		$script="" ;
		for ($i=0; $i <count($tab) ; $i++) { 
			$code =htmlspecialchars(trim($tab[$i])) ;
			$script .= "<script src='".BASE_URL."/Publics/js/".$code."'></script>" ;

		}
		echo $script ;
	}

	public static function formatDate(string $date)
	{
		$dateTime = new DateTime($date);
		return date('d/m/Y',$dateTime->getTimestamp());
	}
}

?>

