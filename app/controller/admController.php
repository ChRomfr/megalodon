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
        
        // Recuperation du nombre de contacts
        $stats['nb_contacts'] = $this->registry->db->count('contacts', array('isDelete = ' => 0));
        $stats['nb_contacts_clients'] = $this->registry->db->count('contacts', array('isDelete =' => 0, 'client =' => 1));
        
        // Envoie des stats a smarty
		$this->registry->smarty->assign('stats', $stats);
        
        // Envoie du code HTML
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
	
    /**
    * Genere les stats pour graphique
    * @return json  
    */
	public function ajax_dataforgraph_repart_mailing_typeAction(){
        $data = array();
        
        // Recuperation des types
        $types = $this->registry->db->get('mailings_type');
        
        // Parcours les types
        foreach($types as $type){
            $data[] = array('label' => $type['libelle'], 'data' => $this->registry->db->count('mailings', array('type_id =' => $type['id'])));
        }
        
        // envoie du resultat au format JSON
        return json_encode($data, JSON_NUMERIC_CHECK);
	}

	public function contacts_delete_by_email_step1Action(){

		if(!is_null($this->registry->Http->post('dc'))){
			$dir = ROOT_PATH . 'web' . DS . 'upload' . DS . 'csv' . DS;
		
			require_once ROOT_PATH . 'kernel' . DS . 'lib' . DS . 'upload' . DS . 'class.upload.php';
			
			if(!is_dir($dir))
				@mkdir($dir);			
			
	        $fichier = new Upload($_FILES['file_dc']);
	        $name = uniqid();
	        if($fichier->uploaded){
	            $fichier->file_overwrite 		= true;
	            $fichier->file_new_name_body  	= $name;
				$fichier->file_new_name_ext		= 'csv';
	            $fichier->process($dir);

	            // On traite le fichier
	            $lines = file(ROOT_PATH . 'web' . DS . 'upload' . DS . 'csv' . DS . $name .'.csv');

	            // On verifie que le fichier comporte des lignes
	            if(count($lines) == 0){
	            	$this->registry->smarty->assign('FlashMessage', 'Le fichier envoye ne contient aucune ligne');
					goto printform;
				}

				$this->load_manager('contacts');
				$contacts = array();

				foreach($lines as $k => $v){
					$result = $this->manager->contacts->getByEmail(trim($v));

					if(!empty($result))
						$contacts[] = $result;
				}

				$this->registry->smarty->assign('contacts', $contacts);

				goto printform;
				echo"<pre>";
				print_r($contacts);
				echo"</pre>";
			
			}else{
				$this->registry->smarty->assign('FlashMessage', 'Une erreur est survenu pendant le transfert du fichier');
				goto printform;
			}
		}// end post

		printform:
		/*echo "<pre>";
		print_r(getSavoirInutile());
		echo "</pre>";*/
		$this->registry->smarty->assign('savoir_inutile', getSavoirInutile());
		return $this->registry->smarty->fetch(VIEW_PATH.'adm'.DS.'contacts_delete_by_email_step1.shark');

	}

	public function contacts_delete_by_email_step2Action(){

		// Verification droit utilisateur
		if( $_SESSION['utilisateur']['isAdmin'] == 0){
			$this->registry->smarty->assign('FlashMessage','Vous n\'avez pas les droits pour effectuer cette action !');
			return $this->indexAction();
		}

		// Verification que le formulaire est appellé de la bonne page avec un contenu
		if(is_null($this->registry->Http->post('contacts'))){
			$this->registry->smarty->assign('FlashMessage','Vous n\'avez pas selectionner de contact !');
			return $this->indexAction();
		}

		// Recuperation des contacts dans une variable
		$datas = $this->registry->Http->post('contacts');
		
		// On boucle sur les données pour les mettre a la corbeille
		foreach($datas as $data){
			foreach($data as $k => $v){
				$contact = new contacts();
				$contact->get($k);
				$old_email = $contact->email;
				$contact->email = NULL;
				$contact->save();

				$clog =  new clog(array('date_log' => date("Y-m-d H:i:s"), 'contact_id' => $k, 'user_id' => $_SESSION['utilisateur']['id'], 'log' => 'Suppression email par fichier. Ancien adresse email : '. $old_email));
				$clog->save();
				echo "<pre>"; print_r($contact); echo "</pre>";
			}
			
		}

		// Message a l utilisateur
		$this->registry->smarty->assign('FlashMessage','Emails supprimes');

		// On lui affiche de nouveau la liste des contacts
		return $this->contacts_maintenanceAction();
	}

	public function mailingtypeAction(){
		$types = new mailing_type();
		
		$this->registry->smarty->assign('types', $types->get());
		
		return $this->registry->smarty->fetch(VIEW_PATH . 'adm' . DS . 'mailingtype.shark');
	}

	/**
	 * Affiche et traite le formulaire pour ajouter un type de mailing
	 * @return [type] [description]
	 */
	public function mailingtype_addAction(){

		if(!is_null($this->registry->Http->post('type'))){
			$type = new mailing_type($this->registry->Http->post('type'));
			if(!empty($type->libelle)){
				$type->save();
				$this->registry->Helper->pnotify('Type ajouté', 'Nouveau type de mailing ajouté');
			}
			return $this->mailingtypeAction();
		}

		// envoie du formulaire
		return $this->registry->smarty->fetch(VIEW_PATH . 'adm' . DS . 'mailingtype_add.shark');
	}

	public function mailingtype_editAction(){

	}

	public function mailingtype_deleteAction($type_id){
		$type = new mailing_type();
		$type->get($type_id);
		$type->delete($type->id);
		$this->registry->Helper->pnotify('Type supprime', 'Le type a été supprimé');
		return $this->mailingtypeAction();
	}
}