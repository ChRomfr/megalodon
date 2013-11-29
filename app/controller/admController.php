<?php

class admController extends Controller{
	
	/**
	 * Surchage de la fonction pour verification ACL
	 * @param [type] $registry [description]
	 */
	public function __construct($registry){
		parent::__construct($registry);

		if( isAdmin() < 1 ){
			$i = $this->load_controller('index');
			return $i->indexAction();
		}
	}

	public function indexAction(){
		$this->registry->load_web_lib('flot/jquery.flot.js','js');
        $this->registry->load_web_lib('flot/jquery.flot.pie.js','js');

		return $this->registry->smarty->fetch(VIEW_PATH . 'adm' . DS . 'index.shark');
	}

	public function maintenanceAction(){
		// Recuperations des utilisateurs en lignes
		$sessions = $this->registry->db->select('s.*, u.identifiant')->from('sessions s')->left_join('user u', 's.user_id = u.id')->where(array('last_update >' => time() - 300))->get();

		$this->registry->smarty->assign('sessions', $sessions);

		return $this->registry->smarty->fetch(VIEW_PATH . 'adm' . DS . 'maintenance.shark');
	}

	public function ajax_clean_cacheAction(){
		$files = getFilesInDir(ROOT_PATH . 'cache');
		foreach($files as $key => $value){
			@unlink(ROOT_PATH . 'cache' . DS . $value);
		}

		return 'Cache cleaned';
	}	

	public function ajax_clean_sessionsAction(){
		$files = getFilesInDir(ROOT_PATH . 'cache' . DS . '_sessions');
		foreach($files as $key => $value){
			@unlink(ROOT_PATH . 'cache' . DS . '_sessions' . DS . $value);
		}

		return 'Sessions cleaned';
	}

	public function configurationAction(){
		if(!is_null($this->registry->Http->post('config'))){
            
            $config = $this->registry->Http->post('config');
            
            foreach($config as $key => $value){
                
                if($key == 'ldap_server'){
                    $value = serialize(explode(',',$value));
                }
                
                $this->registry->db->update('config', array('valeur' => $value), array('cle =' => $key));
            }
            
            $this->registry->cache->remove('config');
            
            return $this->registry->Helper->redirect($this->registry->Helper->getLink('configuration'),3,'Configuration enregistrée');
        }
        
        printform:
            
        return $this->registry->smarty->fetch(VIEW_PATH . 'adm' . DS . 'configuration.shark');
	}


	public function contacts_maintenanceAction(){
		$this->registry->smarty->assign('contacts_corbeille', $this->registry->db->count('contacts', array('isDelete =' => 1)));

		return $this->registry->smarty->fetch(VIEW_PATH.'contacts'.DS.'maintenance.tpl');
	}

	public function users_indexAction(){
		$users = $this->registry->db->get('user');

		$this->registry->smarty->assign('users', $users);

		return $this->registry->smarty->fetch(VIEW_PATH.'adm'.DS.'users_index.shark');
	}

	/**
	 * Affiche et traite le formulaire d edition utilisateur
	 * @param  [type] $uid [description]
	 * @return [type]      [description]
	 */
	public function users_editAction($uid){

		if(!is_null($this->registry->Http->post('user'))){
			$user = new utilisateur($this->registry->Http->post('user'));
			// Sauvegarde de l utilisateur
			$user->save();

			// Suppression de toute les acls utilisateurs
			$this->registry->db->delete('acl', null, array('user_id =' => $user->id));

			// On parcours les acls pour enregistrement
			$acls = $this->registry->Http->post('acl');
			foreach ($acls as $key => $value) {
				$this->registry->db->insert('acl', array('user_id' => $user->id, 'acl' => $key));
			}

			$this->registry->smarty->assign('FlashMessage','Utilisateur modifié');

			return $this->users_indexAction();
		}

		$user = new utilisateur();
		$user->get($uid);

		$user->acl = getACLs($user->id, true);

		$this->registry->smarty->assign('user', $user);

		return $this->registry->smarty->fetch(VIEW_PATH.'adm'.DS.'users_edit.shark');
	}

	/**
	 * Retourne les stats pour affichage dans l administration
	 * @return [type] [description]
	 */
	public function ajax_statsAction(){
		$stats = array();

		// Recuperation des campagnes en cours
		$stats['nb_campaign'] = $this->registry->db->count('campaign', array('date_start <=' => date('Y-m-d'), 'date_end >=' =>  date('Y-m-d')));

		// Request mailing
		$stats['nb_mailing'] = $this->registry->db->count('mailings', array('date_wish >=' => date('Y-m-d')));

		$this->registry->smarty->assign('stats', $stats);

		return $this->registry->smarty->fetch(VIEW_PATH.'adm'.DS.'ajax_stats.shark');
	}

	/**
	 * Recuperation des données pour generation graphique
	 * @return [type] [description]
	 */
	public function ajax_dataforgraph_repart_constactsAction(){
		$data = array();

		$data[] = array('label' => 'Societe', 'data' => $this->registry->db->count('contacts', array('ctype =' => 'societe')));
		$data[] = array('label' => 'Particulier', 'data' => $this->registry->db->count('contacts', array('ctype =' => 'particulier')));
		$data[] = array('label' => 'Contact', 'data' => $this->registry->db->count('contacts', array('ctype =' => 'societe_contact')));

		return json_encode($data,JSON_NUMERIC_CHECK);
	}

}