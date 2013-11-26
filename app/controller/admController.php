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
		return $this->registry->smarty->fetch(VIEW_PATH . 'adm' . DS . 'index.shark');
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

}