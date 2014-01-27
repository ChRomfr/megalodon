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

		if( isset($_POST['logo_delete'])){
			unlink(ROOT_PATH.'web'.DS.'upload'.DS.'logo'.DS.$this->registry->config['logo_name']);
			$this->registry->db->update('config', array('valeur' => NULL), array('cle =' => 'logo_name'));
		}

		if(!is_null($this->registry->Http->post('config'))){
            
            $config = $this->registry->Http->post('config');
            
            foreach($config as $key => $value){
                
                if($key == 'ldap_server'){
                    $value = serialize(explode(',',$value));
                }
                
                $this->registry->db->update('config', array('valeur' => $value), array('cle =' => $key));
            }

            // Traitements des fichiers
            $path = ROOT_PATH . 'web' . DS . 'upload' . DS . 'logo' . DS;
		
			if( !is_dir($path) ) @mkdir($path);			
		
			// Recuperation lib
			require_once ROOT_PATH . 'kernel' . DS . 'lib' . DS . 'upload' . DS . 'class.upload.php';
		
			// Nouvelle instance
			$upload = new Upload($_FILES['file_logo']);
		
			$new_file_name = time();
		
			// Traitement de l'upload avec renommage
			if($upload->uploaded){
				$upload->file_overwrite = true;
				$upload->file_new_name_body   = time();
				$upload->process($path);
				
				// Enregistrement dans la table
				$this->registry->db->update('config', array('valeur' => $upload->file_dst_name), array('cle =' => 'logo_name'));
			}
            
            // Suppression du cache actuel
            $this->registry->cache->remove('config');
            
            $this->registry->Helper->pnotify('Configuration', 'Configuration enregistrée');
            goto printform;
        }
        
        printform:
            
        return $this->registry->smarty->fetch(VIEW_PATH . 'adm' . DS . 'configuration.shark');
	}

	/**
	 * Verifie la precence des lignes de configuration dans la table.
	 * Si ligne abs elle est automatiquement rajoutée.
	 * @return [type] [description]
	 */
	public function configuration_checkAction(){
		$config_check = array(
			'ape_multi_choice'	=>	0,
			'logo'				=>	'',
			'logo_name'			=>	'',
		);
		

		$results = array();

		foreach($config_check as $k => $v){
			$result = $this->registry->db->count('config', array('cle =' => $k));
			
			if($result == 0){
				// On ajoute la cle
				$this->registry->db->insert('config', array('cle' => $k, 'valeur' => $v));
				$results[] = 'KEY : <strong>'.$k.'</strong> is missing. KEY add in database !';
			}
		}

		$this->registry->smarty->assign('results', $results);

		return $this->registry->smarty->fetch(VIEW_PATH . 'adm' . DS . 'configuration_check.meg');
	}

    public function contactsAction(){
        
        return $this->registry->smarty->fetch(VIEW_PATH . 'adm' . DS . 'contacts.tpl');
    }

	public function contacts_maintenanceAction(){
		$this->registry->smarty->assign('contacts_corbeille', $this->registry->db->count('contacts', array('isDelete =' => 1)));

		return $this->registry->smarty->fetch(VIEW_PATH.'contacts'.DS.'maintenance.tpl');
	}

	public function contacts_postesAction(){
		$this->registry->smarty->assign('postes', $this->registry->db->get('poste', null, 'libelle'));

		return $this->registry->smarty->fetch(VIEW_PATH.'adm'.DS.'contacts_postes.tpl');
	}

	/**
	 * Traite la suppression d'un poste dans la base
	 * @param  [type] $pid [description]
	 * @return [type]      [description]
	 */
	public function contacts_postes_deleteAction($pid){
		// Suppression du poste dans la base
		$this->registry->db->delete('poste', $pid);

		// Mise a jour des contacts
		$this->registry->db->update('personne', array('poste_id' => NULL), array('poste_id =' => $pid));

		// Notification
		$this->registry->Helper->pnotify('Poste','Poste supprimé. Les contacts ont été mise a jour.');

		return $this->contacts_postesAction();
	}

	/**
	 * Traite le formulaire d ajout d'un poste dans la base
	 * @return [type] [description]
	 */
	public function contacts_postes_addAction(){
		if(!is_null($this->registry->Http->post('poste'))){
			$poste = $this->registry->Http->post('poste');
			$this->registry->db->insert('poste', $poste);
			$this->registry->Helper->pnotify('Postes', 'Poste ajouté à la base');
		}

		return $this->contacts_postesAction();
	}

	/**
	 * Traite le formulaire d'edition d'un poste
	 * @param  [type] $poste_id [description]
	 * @return [type]           [description]
	 */
	public function contacts_postes_editAction($poste_id){
		if(!is_null($this->registry->Http->post('poste'))){
			$poste = $this->registry->Http->post('poste');
			$this->registry->db->update('poste', $poste, array('id =' => $poste_id));
			$this->registry->Helper->pnotify('Postes', 'Poste modifié');
		}

		return $this->contacts_postesAction();
	}

	/**
	 * Retourne le formulaire pour ajouter/modifier un poste dans la base
	 * @param  [type] $poste_id [description]
	 * @return [type]           [description]
	 */
	public function contacts_postes_load_formAction($poste_id = null){

		if(!empty($poste_id)){
			$this->registry->smarty->assign('poste', $this->registry->db->get_one('poste', array('id =' => $poste_id)));
		}

		return $this->registry->smarty->fetch(VIEW_PATH.'adm'.DS.'form_contacts_postes.shark');
	}

	public function contacts_servicesAction(){
		$this->registry->smarty->assign('services', $this->registry->db->get('service', null, 'libelle'));

		return $this->registry->smarty->fetch(VIEW_PATH.'adm'.DS.'contacts_services.tpl');
	}

	/**
	 * Traite la suppression d'un service dans la base
	 * @param  [type] $pid [description]
	 * @return [type]      [description]
	 */
	public function contacts_services_deleteAction($sid){
		// Suppression du service dans la base
		$this->registry->db->delete('service', $sid);

		// Mise a jour des contacts
		$this->registry->db->update('personne', array('service_id' => NULL), array('service_id =' => $sid));

		// Notification
		$this->registry->Helper->pnotify('Services','Service supprimé.<br/>Les contacts ont été mise a jour.');

		return $this->contacts_servicesAction();
	}

	/**
	 * Traite le formulaire d ajout d'un poste dans la base
	 * @return [type] [description]
	 */
	public function contacts_services_addAction(){
		if(!is_null($this->registry->Http->post('service'))){
			$poste = $this->registry->Http->post('service');
			$this->registry->db->insert('service', $poste);
			$this->registry->Helper->pnotify('services', 'service ajouté à la base');
		}

		return $this->contacts_servicesAction();
	}

	/**
	 * Traite le formulaire d'edition d'un service
	 * @param  [type] $service_id [description]
	 * @return [type]           [description]
	 */
	public function contacts_services_editAction($service_id){
		if(!is_null($this->registry->Http->post('service'))){
			$service = $this->registry->Http->post('service');
			$this->registry->db->update('service', $service, array('id =' => $service_id));
			$this->registry->Helper->pnotify('Services', 'Service modifié');
		}

		return $this->contacts_servicesAction();
	}

	/**
	 * Retourne le formulaire pour ajouter/modifier un poste dans la base
	 * @param  [type] $poste_id [description]
	 * @return [type]           [description]
	 */
	public function contacts_services_load_formAction($service_id = null){

		if(!empty($service_id)){
			$this->registry->smarty->assign('service', $this->registry->db->get_one('service', array('id =' => $service_id)));
		}

		return $this->registry->smarty->fetch(VIEW_PATH.'adm'.DS.'form_contacts_services.shark');
	}


	public function users_indexAction(){
		$users = $this->registry->db->get('user');

		$this->registry->smarty->assign('users', $users);

		return $this->registry->smarty->fetch(VIEW_PATH.'adm'.DS.'users_index.shark');
	}
	
	public function users_addAction(){
		if(!is_null($this->registry->Http->post('user'))){
			$tmp = $this->registry->Http->post('user');
			
			if($tmp['password_1'] != $tmp['password_2']){
				$this->registry->Helper->pnotify('Utilisateur', 'Mot de passe different !');
				goto showform;
			}
			
			if($this->registry->db->count('user', array('identifiant =' => $tmp['identifiant']))){
				$this->registry->Helper->pnotify('Utilisateur', 'Identifiant deja utilisé !');
				goto showform;
			}
			
			if($this->registry->db->count('user', array('email =' => $tmp['email']))){
				$this->registry->Helper->pnotify('Utilisateur', 'E-mail deja utilisé !');
				goto showform;
			}
			
			$tmp['password'] = sha1( sha1(strtolower($tmp['identifiant'])) . $tmp['password_1'] );
			
			$user = new utilisateur($tmp);
			
			$user->save();
			
			$this->registry->Helper->pnotify('Utilisateur', 'utilisateur ajouté');
			return $this->users_indexAction();
		}
		
		showform:
		$this->getFormValidatorJs();
		return $this->registry->smarty->fetch(VIEW_PATH.'adm'.DS.'users_add.shark');
	}

	/**
	 * Affiche et traite le formulaire d edition utilisateur
	 * @param  [type] $uid [description]
	 * @return [type]      [description]
	 */
	public function users_editAction($uid){

		if(!is_null($this->registry->Http->post('user'))){
			$user = new utilisateur($this->registry->Http->post('user'));
			
			// Suppression propriete mot de passe pour eviter un RAZ
			unset($user->password);
			
			// Sauvegarde de l utilisateur
			$user->save();

			// Suppression de toute les acls utilisateurs
			$this->registry->db->delete('acl', null, array('user_id =' => $user->id));

			// On parcours les acls pour enregistrement
			$acls = $this->registry->Http->post('acl');
			if(!empty($acls)){
				foreach ($acls as $key => $value) {
					$this->registry->db->insert('acl', array('user_id' => $user->id, 'acl' => $key));
				}
			}

			$this->registry->Helper->pnotify('Utilisateur', 'modifications enregistrées', 'success');

			return $this->users_indexAction();
		}

		$user = new utilisateur();
		$sites = new site();

		$user->get($uid);
		$user->acl = getACLs($user->id, true);

		$this->registry->smarty->assign('user', $user);
		$this->registry->smarty->assign('sites', $sites->get());

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
							
			}else{
				$this->registry->smarty->assign('FlashMessage', 'Une erreur est survenu pendant le transfert du fichier');
				goto printform;
			}
		}// end post

		printform:
	
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

	public function modulesAction(){
		$modules = $this->registry->db->get('modules');

		$this->registry->smarty->assign('modules',$modules);
		$this->registry->smarty->assign('ctitre', 'Administration :: Modules');

		return $this->registry->smarty->fetch(VIEW_PATH . 'adm' . DS . 'modules.meg');
	}

	/* --- SITES ACTIONS --- */

	/**
	 * Affiche la liste des sites dans la base
	 * @return [type] [description]
	 */
	public function sitesAction(){
		$sites = new site();

		$this->registry->smarty->assign('sites', $sites->get());

		return $this->registry->smarty->fetch(VIEW_PATH . 'adm' . DS . 'sites.meg');
	}

	/**
	 * Charge le formulaire pour ajout/modifier un site
	 * @param  [type] $sid [description]
	 * @return [type]      [description]
	 */
	public function sitesloadformAction($sid = null){

		if(!is_null($sid)){
			$site = new site();
			$site->get($sid);
			$this->registry->smarty->assign('site', $site);
		}

		return $this->registry->smarty->fetch(VIEW_PATH . 'adm' . DS . 'form_sites.meg');
	}

	/**
	 * Traite l'ajout d'un site dans la base
	 * @return [type] [description]
	 */
	public function site_addAction(){
		if(!is_null($this->registry->Http->post('site'))){
			$site = new site($this->registry->Http->post('site'));
			$site->save();
			$this->registry->Helper->pnotify('Sites', 'Site enregistré');
		}

		return $this->sitesAction();
	}

	/**
	 * Traite l'edition d'un site dans la base
	 * @param  [type] $sid [description]
	 * @return [type]      [description]
	 */
	public function site_editAction($sid){
		if(!is_null($this->registry->Http->post('site'))){
			$site = new site($this->registry->Http->post('site'));

			// Verification des champs qui peuvent etre vide
			if(empty($site->telephone)) $site->telephone = NULL;
			if(empty($site->fax)) $site->fax = NULL;
			if(empty($site->email)) $site->email = NULL;

			$site->save();
			$this->registry->Helper->pnotify('Sites', 'Site modifié');
		}

		return $this->sitesAction();
	}

	/**
	 * Traite la suppression d'un site dans la base
	 * @param  [type] $sid [description]
	 * @return [type]      [description]
	 */
	public function site_deleteAction($sid){
		$this->registry->db->delete('sites', $sid);
		$users = $this->registry->db->get('user', array('site_id =' => $sid));
		foreach($users as $user){
			$user['site_id'] = NULL;
			$this->registry->db->update('user', $user);
		}
		$this->registry->Helper->pnotify('Site', 'Site supprimé de la base. Les utilisateurs ont été retirés du site.');
		return $this->sitesAction();
	}

	/*-- TIERS --*/


	/*-- PRODUCT --*/
}