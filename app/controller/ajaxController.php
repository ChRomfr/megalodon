<?php

class ajaxController extends Controller{
	
	public function getVilleByCpAction(){
		$Cp = $this->app->HTTPRequest->getData('term');		
		$Datas = $this->app->db->select('DISTINCT(city) as label, zip_code as value')->from(PREFIX . 'contacts')->where_free( 'zip_code LIKE "'. $Cp .'%"' )->get();
		return json_encode($Datas);			
	}
	
	public function getNumSemaineAction(){
		$Date = $this->app->HTTPRequest->getData('date');
		$Tmp = explode('/', $Date);
		return date("W", mktime(13,0,0,$Tmp[1],$Tmp[0],$Tmp[2]));
	}

	public function getSavoirInutileAction(){
		$savoir = getSavoirInutile();

		$this->registry->smarty->assign('savoir_inutile', $savoir);

		return $this->registry->smarty->fetch(VIEW_PATH . 'ajax' . DS . 'savoir_inutile.shark');
	}

	public function get_tokenAction(){
		return getUniqueID();
	}

	public function add_taskAction(){

		$task = array(
			'third_type'	=>	$this->registry->Http->get('third_type'),
			'third_id'	=>	$this->registry->Http->get('third_id'),
			'task'	=>	$this->registry->Http->get('task'),
		);
		

		$task['date_add'] = date("Y-m-d H:i:s");
		$task['date_expire'] = NULL;
		$task['process'] = 0;
		$task['priority'] = 2;
		$task['user_id'] = $_SESSION['utilisateur']['id'];
		$task['creat_by'] = $_SESSION['utilisateur']['id'];

		if( empty($task['link']) ){
			switch ($task['third_type']) {
				case 'contacts':
					$task['link'] = $this->registry->Helper->getLink('contacts/detail/' . $task['third_id']);
					$this->load_manager('contacts');
					$contact = $this->manager->contacts->getById($task['third_id']);
					if($contact['ctype'] == 'societe'){
						$task['task'] = $contact['raison_social'] . ' - ' . $task['task'];
					}else{
						$task['task'] = $contact['nom'] . ' ' . $contact['prenom'] . ' - ' . $task['task'];
					}
					break;
				
				default:
					# code...
					break;
			}
		}

		$this->registry->db->insert('tasks', $task);

		return 'ok';
	}

	public function my_tasksAction(){
		$tasks = $this->registry->db->get('tasks', array('user_id =' => $_SESSION['utilisateur']['id'], 'process =' => 0));
		$this->registry->smarty->assign('mytasks', $tasks);
		return $this->registry->smarty->fetch(VIEW_PATH . 'ajax' . DS . 'my_tasks_menu_nav.shark');
	}
	
	/**
	*	Permet de recherche l id d un code ape dans la base
	*	@param string code ape a recherche
	*	@return json liste des codes APE et IP correspondant a la recherche
	*/
	public function search_apeAction(){
		$search = $this->registry->HTTPRequest->getData('term');
		$results = $this->registry->db->select('code as label, id as value')->from('ape')->where_free('code LIKE "%'. $search .'%"')->limit(10)->get();
		return json_encode($results);
	}

	public function search_userAction(){
		$search = $this->registry->HTTPRequest->getData('term');
		$results = $this->registry->db->select('identifiant as label, id as value')->from('user')->where_free('identifiant LIKE "%'. $search .'%"')->limit(10)->get();
		return json_encode($results);
	}
}