<?php
class Notification
{
	public function __construct()
	{
		if (!isset($_SESSION['connected']) || $_SESSION['role'] !== ROLE_USER[0]) {
			header('Location: ' . BASE_URL);
			exit();
		}
	}
	
	// public function delete()
	// {
	// 	$notif = new NotificationModel();
	// 	$idNotif = (int)$_POST['idNotif'];
	// 	$notif->updatestatus($idNotif);
	// 	echo json_encode(["success" => true]);
	// }

	public function cours($codeChapitre, $title, $id)
	{
		$notif = new NotificationModel();
		$idNotif = (int)$id;
		$notif->updatestatus($idNotif);

		header("Location: " . BASE_URL.'/Cours/view/'.$title.'/'.$codeChapitre);
	}
}
