<?php
/**
 * Gere les appel de page lié au CA
 */
class caController extends Controller{
	

	/**
	 * Permet d'ajouter un CA dans la base
	 * @param int $contact_id identifiant du client pour lié le CA
	 */
	public function addAction($contact_id){
		$ca = new ca($this->registry->Http->post('ca'));
		$ca->date_add = date('Y-m-d H:i:s');
		$ca->user_id = $_SESSION['utilisateur']['id'];
		$ca->contact_id = $contact_id;
		
		$ca->save();
		$this->registry->Helper->pnotify('Contacts','CA enregistré');
		$ccontacts = $this->load_controller('contacts');
		return $ccontacts->detailAction($contact_id);
	}

	public function editAction($contact_id){
		$ca = new ca($this->registry->Http->post('ca'));
		//$ca->date_add = date('Y-m-d H:i:s');
		//$ca->user_id = $_SESSION['utilisateur']['id'];
		//$ca->contact_id = $contact_id;
		
		$ca->save();
		$this->registry->Helper->pnotify('Contacts','CA modifié');
		$ccontacts = $this->load_controller('contacts');
		return $ccontacts->detailAction($contact_id);
	}

	public function get_modal_formAction($contact_id){
		
		if(!is_null($this->registry->Http->get('ca_id'))){
			$ca_id = $this->registry->Http->get('ca_id');

			// Traitement cas CA passe en parametre
			$ca = $this->registry->db->get_one('ca', array('id =' => $ca_id));

			// Envoie a smarty
			$this->registry->smarty->assign('ca',$ca);
		}

		$this->registry->smarty->assign('contact_id', $contact_id);
		return $this->registry->smarty->fetch(VIEW_PATH.'ca'.DS.'form.tpl');
	}


	public function get_ca_by_contact_idAction($contact_id){
		$result = $this->registry->db->select('ca.*')
					->from('ca ca')
					->left_join('user u','ca.user_id = u.id')
					->where(array('contact_id =' => $contact_id))
					->order('date_ca DESC')
					->get();

		$this->registry->smarty->assign('ca', $result);
	}

	public function stats_contactsAction($cid){
		// Recuperation date max et min
		$ecart_date = $this->registry->db->select('MIN(date_ca) AS date_min, MAX(date_ca) as date_max')->from('ca')->where(array('contact_id =' => $cid))->get_one();

		// Determination de la date max
		$tmp = explode('-', $ecart_date['date_max']);
		$year_max = $tmp[0];
		$current_year = $year_max;

		// Determination de la date min
		$tmp = explode('-', $ecart_date['date_min']);
		$year_min = $tmp[0];

		// Init var qui va recevoir les stats
		$stats = array();

		// Boucle pour récuperer toute les informations sur les années
		while ($current_year >= $year_min) {
			$stats[$current_year]['year'] = (int)$current_year;

			$result = $this->registry->db->select('SUM(montant) AS sum')->from('ca')->where(array('contact_id =' => $cid, 'date_ca >=' => $current_year.'-01-01', 'date_ca <=' => $current_year.'-12-31', 'statut =' => 2))->get_one();

			$stats[$current_year]['ca_realise'] = $result['sum'];

			$result = $this->registry->db->select('SUM(montant) AS sum')->from('ca')->where(array('contact_id =' => $cid, 'date_ca >=' => $current_year.'-01-01', 'date_ca <=' => $current_year.'-12-31', 'statut =' => 1))->get_one();

			$stats[$current_year]['ca_prevision'] = $result['sum'];

			$car_n1 = $this->registry->db->select('SUM(montant) AS sum')->from('ca')->where(array('contact_id =' => $cid, 'date_ca >=' => ($current_year-1).'-01-01', 'date_ca <=' => ($current_year-1).'-12-31', 'statut =' => 2))->get_one();

			if($car_n1['sum'] > 0){
				$stats[$current_year]['ca_evolution'] = (($stats[$current_year]['ca_realise']-$car_n1 ['sum']) * 100) / $car_n1 ['sum'];
			}else{
				$stats[$current_year]['ca_evolution'] = 0;
			}
			
			
			// On decrement pour l année n-1
			$current_year = $current_year - 1;
		}

		$this->registry->smarty->assign('stats', $stats);
		return $this->registry->smarty->fetch(VIEW_PATH.'ca'.DS.'stats_contacts.tpl');

		var_dump($stats);
		exit;
		$stats = array();
		// Recuperation des stats pour année en cours
		$year = date('Y');
		$stats[0]['year'] = $year;

		$result = $this->registry->db->select('SUM(montant) AS sum')->from('ca')->where(array('contact_id =' => $cid, 'date_ca >=' => $year.'-01-01', 'date_ca <=' => $year.'-12-31', 'statut =' => 2))->get_one();

		$stats[0]['ca_realise'] = $result['sum'];

		$result = $this->registry->db->select('SUM(montant) AS sum')->from('ca')->where(array('contact_id =' => $cid, 'date_ca >=' => $year.'-01-01', 'date_ca <=' => $year.'-12-31', 'statut =' => 1))->get_one();

		$stats[0]['ca_prevision'] = $result['sum'];

		$this->registry->smarty->assign('stats', $stats);
		return $this->registry->smarty->fetch(VIEW_PATH.'ca'.DS.'stats_contacts.tpl');
		var_dump($stats);


	}
}