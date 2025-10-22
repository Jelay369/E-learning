<?php 

class AppMessenger{
	const TIMEOUT = 60;
	
	public function start()
	{
		session_write_close();
		ignore_user_abort(false);
		$mess = new MessengerModel();
		$formateur = new FormateurModel();

		$matricule = htmlspecialchars(trim($_POST['matricule']));
		$old_count_message = (int)$_POST['count'];

		$formateur_connected = $formateur->getConnected();

		if($old_count_message < 0)
		{
			$old_count_message = $mess->getCountAllMessage($matricule);
		}

		for($i = 0; $i < self::TIMEOUT; $i++)
		{
			$count_message = $mess->getCountAllMessage($matricule);
			$connected = $formateur->getConnected();
			if($count_message > $old_count_message)
			{
				echo json_encode(['count'=>$count_message,"updated" => true]);
				exit();
			}
			if($connected !== $formateur_connected)
			{
				echo json_encode(['count'=>$count_message,"updated" => false,'uconnected' => true]);
				exit();
			}
			sleep(1);
		}
		echo json_encode(['updated' => false,'count' => $old_count_message]);
	}

	public function messenger_start()
	{
		session_write_close();
		ignore_user_abort(false);
		$mess = new FMessengerModel();
		$etudiant = new EtudiantModel();

		$id = htmlspecialchars(trim($_POST['id']));
		$old_count_message = (int)$_POST['count'];

		$etudiant_connected = $etudiant->getConnected();

		if($old_count_message < 0)
		{
			$old_count_message = $mess->getCountAllMessage($id);
		}

		for($i = 0; $i < self::TIMEOUT; $i++)
		{
			$count_message = $mess->getCountAllMessage($id);
			$connected = $etudiant->getConnected();
			if($count_message > $old_count_message)
			{
				echo json_encode(['count'=>$count_message,"updated" => true]);
				exit();
			}
			if($connected !== $etudiant_connected)
			{
				echo json_encode(['count'=>$count_message,"updated" => false,'uconnected' => true]);
				exit();
			}
			sleep(1);
		}
		echo json_encode(['updated' => false,'count' => $old_count_message]);

	}
}