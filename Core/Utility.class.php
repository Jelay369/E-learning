<?php
use Cocur\Slugify\Slugify;

class Utility
{


	public static function ipinfo()
	{
        $apiKey = 'c5391ed54e2123';
        $apiUrl = "http://ipinfo.io?token=$apiKey";
        $response = file_get_contents($apiUrl);

		return $response;
	}
	
	public static function formatDate($date)
	{
		$days = ['', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
		$months = ['', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
		$dateTime = new DateTime($date);
		$day_name = (int)$dateTime->format('N');
		$day_value = (int)$dateTime->format('d');
		$month = (int)$dateTime->format('n');
		$years = (int)$dateTime->format('Y');

		return $days[$day_name] . " " . $day_value . " " . $months[$month] . " " . $years;
	}
	
	public static function formatMois($date)
	{
		$months = ['', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
		$dateTime = new DateTime($date);
		$month = (int)$dateTime->format('n');
		$years = (int)$dateTime->format('Y');

		return $months[$month] . " " . $years;
	}

	public static function formatShortDate($date)
	{
		$months = ['', 'Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sept', 'Oct', 'Nov', 'Déc'];
		$dateTime = new DateTime($date);
		$day_value = (int)$dateTime->format('d');
		$month = (int)$dateTime->format('n');
		$years = (int)$dateTime->format('Y');

		return $day_value . " " . $months[$month] . " " . $years;
	}
	public static function getTimestamp($datetime)
	{
		$dateTime = new DateTime($datetime);
		return $dateTime->getTimestamp();
	}
	public static function getHours($datetime)
	{
		$dateTime = new DateTime($datetime);
		return $dateTime->format('Y-m-d H');
	}
	public static function formatTime($datetime)
	{
		$dateTime = new DateTime($datetime);
		return $dateTime->format('H:i');
	}
	public static function isImage($data)
	{
		$exts = ['jpeg', 'jpg', 'png', 'gif'];
		$parts = explode(".", $data);
		$ext = end($parts);
		if (in_array(strtolower($ext), $exts)) {
			return true;
		}
		return false;
	}
	public static function number_to_letter($nombre)
	{
		$cel = new ChiffreEnLettre();
		return $cel->getString($nombre);
	}
	public static function truncate_text($text)
	{
		if (strlen($text) < 18)
			return $text;
		return substr($text, 0, 17) . "...";
	}

	public static function browserDetection()
	{
		//  Version navigateur

		$bd = new BrowserDetection();
		$b_name = strtolower($bd->getName());
		$b_version = (int) $bd->getVersion();

		if (
			($b_name === "internet explorer")
			|| ($b_name === "edge" && $b_version < 15)
			|| ($b_name === "firefox" && $b_version < 54)
			|| ($b_name === "chrome" && $b_version < 51)
			|| ($b_name === "safari" && $b_version < 10)
			|| ($b_name === "opera" && $b_version < 38)
			|| ($b_name === "opera mini")
			|| ($b_name === "android" && $b_version < 99)
			|| ($b_name === "opera mobile" && $b_version < 64)
			|| ($b_name === "samsung internet" && $b_version < 5)
		) {
			Controllers::loadView("obsolete.php");
			die();
		}
		//  Version navigateur
	}

	public static function formatUrl($str) {
		// $str = strtolower($str);
		// $cible = [
		// 	'À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ă', 'Ą',
		// 	'Ç', 'Ć', 'Č', 'Œ',
		// 	'Ď', 'Đ',
		// 	'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ă', 'ą',
		// 	'ç', 'ć', 'č', 'œ',
		// 	'ď', 'đ',
		// 	'È', 'É', 'Ê', 'Ë', 'Ę', 'Ě',
		// 	'Ğ',
		// 	'Ì', 'Í', 'Î', 'Ï', 'İ',
		// 	'Ĺ', 'Ľ', 'Ł',
		// 	'è', 'é', 'ê', 'ë', 'ę', 'ě',
		// 	'ğ',
		// 	'ì', 'í', 'î', 'ï', 'ı',
		// 	'ĺ', 'ľ', 'ł',
		// 	'Ñ', 'Ń', 'Ň',
		// 	'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ő',
		// 	'Ŕ', 'Ř',
		// 	'Ś', 'Ş', 'Š',
		// 	'ñ', 'ń', 'ň',
		// 	'ò', 'ó', 'ô', 'ö', 'ø', 'ő',
		// 	'ŕ', 'ř',
		// 	'ś', 'ş', 'š',
		// 	'Ţ', 'Ť',
		// 	'Ù', 'Ú', 'Û', 'Ų', 'Ü', 'Ů', 'Ű',
		// 	'Ý', 'ß',
		// 	'Ź', 'Ż', 'Ž',
		// 	'ţ', 'ť',
		// 	'ù', 'ú', 'û', 'ų', 'ü', 'ů', 'ű',
		// 	'ý', 'ÿ',
		// 	'ź', 'ż', 'ž',
		// 	'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р',
		// 	'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'р',
		// 	'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я',
		// 	'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я'
		// ];

		// $rempl = [
		// 	'A', 'A', 'A', 'A', 'A', 'A', 'AE', 'A', 'A',
		// 	'C', 'C', 'C', 'CE',
		// 	'D', 'D',
		// 	'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'a', 'a',
		// 	'c', 'c', 'c', 'ce',
		// 	'd', 'd',
		// 	'E', 'E', 'E', 'E', 'E', 'E',
		// 	'G',
		// 	'I', 'I', 'I', 'I', 'I',
		// 	'L', 'L', 'L',
		// 	'e', 'e', 'e', 'e', 'e', 'e',
		// 	'g',
		// 	'i', 'i', 'i', 'i', 'i',
		// 	'l', 'l', 'l',
		// 	'N', 'N', 'N',
		// 	'O', 'O', 'O', 'O', 'O', 'O', 'O',
		// 	'R', 'R',
		// 	'S', 'S', 'S',
		// 	'n', 'n', 'n',
		// 	'o', 'o', 'o', 'o', 'o', 'o',
		// 	'r', 'r',
		// 	's', 's', 's',
		// 	'T', 'T',
		// 	'U', 'U', 'U', 'U', 'U', 'U', 'U',
		// 	'Y', 'Y',
		// 	'Z', 'Z', 'Z',
		// 	't', 't',
		// 	'u', 'u', 'u', 'u', 'u', 'u', 'u',
		// 	'y', 'y',
		// 	'z', 'z', 'z',
		// 	'A', 'B', 'B', 'r', 'A', 'E', 'E', 'X', '3', 'N', 'N', 'K', 'N', 'M', 'H', 'O', 'N', 'P',
		// 	'a', 'b', 'b', 'r', 'a', 'e', 'e', 'x', '3', 'n', 'n', 'k', 'n', 'm', 'h', 'o', 'p',
		// 	'C', 'T', 'Y', 'O', 'X', 'U', 'u', 'W', 'W', 'b', 'b', 'b', 'E', 'O', 'R',
		// 	'c', 't', 'y', 'o', 'x', 'u', 'u', 'w', 'w', 'b', 'b', 'b', 'e', 'o', 'r'
		// ];

		// $clean = str_replace($cible, $rempl, $str); 
		// // préserve le maximum de caractères utiles


		// $parts = explode(" ", $clean);
		// $url = implode("-", $parts);
		// return $url;

		$slugify = new Slugify();
		return $slugify->slugify($str);
	}

	
	public static function format_date($date)
	{
		$date_ = new DateTime($date);
		return $date_->format('d/m/Y');
	}

	public static function years_interval ($date1, $date2=null)
	{
		if(is_null($date2)){
			$date2= new DateTime();
		}
		$interval = $date1->diff($date2);
		return $interval->format("%y");
	}

}
