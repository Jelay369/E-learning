<?php
class DashboardEtudiant
{
	public function __construct()
	{
		if (!isset($_SESSION['connected']) || $_SESSION['role'] !== ROLE_USER[0]) {
			header('Location: ' . BASE_URL);
			exit();
		}
	}




	// public function paginationFormation($numero)
	// {
	// 	$numero = (int)$numero;
	// 	$offset = ($numero - 1) * 6;
	// 	$formations = new FormationModel();
	// 	$fem = new FormationEtudiantModel();
	// 	$data['my_formations'] = $fem->getFormationEtudiant(htmlspecialchars($_SESSION['matricule']));

	// 	$data['formations'] = $formations->allWithOffset($offset);
	// 	Controllers::loadView("etudiant/components/pagination_formation.php", $data);
	// }



	public function evaluation()
	{
		$fem = new FormationEtudiantModel();
		$etu = new EtudiantModel();
		if (!$etu->hasConfirmInscription($_SESSION['matricule'])) {
			exit();
		}
		$data['my_formations'] = $fem->getFormationEtudiant(htmlspecialchars($_SESSION['matricule']));
		Controllers::loadView("etudiant/components/evaluation.php", $data);
	}

	
	function getCountNotConfirmed()
	{
		$fem = new FormationEtudiantModel();
		$data['count'] = $fem->getCountNotConfirmed($_SESSION['matricule']);
		if ($data['count'] > 0) {
			echo "ok";
		} else {
			echo "empty";
		}
	}

}
