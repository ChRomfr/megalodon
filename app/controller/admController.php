<?php
/**
 * Contient toute les actions accessible dans l'administration
 * @last_update : 20140305
 */
class admController extends Controller{
	
	/**
	 * Surchage de la fonction pour verification ACL
	 * @param [type] $registry [description]
	 */
	public function __construct($registry){
		parent::__construct($registry);

		if( isAdmin() < 1 ){
			header('location'. $this->registry->config['url']);
			exit('Error not allow');
		}

		  $this->registry->load_web_lib('meg/adm_global.js','js', 'footer');
	}

	public function indexAction(){
		$this->registry->load_web_lib('flot/jquery.flot.js','js', 'footer');
        $this->registry->load_web_lib('flot/jquery.flot.pie.js','js', 'footer');
        $this->registry->load_web_lib('meg/adm_index.js','js', 'footer');
		return $this->registry->smarty->fetch(VIEW_PATH . 'adm' . DS . 'index.shark');
	}

	public function maintenanceAction(){
		// Recuperations des utilisateurs en lignes
		$sessions = $this->registry->db->select('s.*, u.identifiant')->from('sessions s')->left_join('user u', 's.user_id = u.id')->where(array('last_update >' => time() - 300))->get();

		$this->registry->smarty->assign('sessions', $sessions);

		return $this->registry->smarty->fetch(VIEW_PATH . 'adm' . DS . 'maintenance.shark');
	}

	/**
	 * Verifie l'existance et les droits sur les dossiers MEG
	 * @return string HTML
	 */
	public function check_dirAction(){
		$check_dir = array(
			'/cache'		=>	array(
					'name'	=>	'/cache',
					'dir'	=>	ROOT_PATH.'cache',
				),
			'/cache/_sessions' => array(
					'name'	=>	'/cache/_sessions',
					'dir'	=>	ROOT_PATH.'cache'.DS.'_sessions',
				),
			'/web/upload'	=>	array(
					'name'	=>	'/web/upload',
					'dir'	=>	ROOT_PATH .'web'.DS.'upload',
				),
			'/web/upload/contacts'	=>	array(
					'name'	=>	'/web/upload/contacts',
					'dir'	=>	ROOT_PATH . 'web'.DS.'upload'.DS.'contacts',
				),
			'/web/upload/csv'	=>	array(
					'name'	=>	'/web/upload/csv',
					'dir'	=>	ROOT_PATH . 'web'.DS.'upload'.DS.'csv',
				),
			'/web/upload/logo'	=>	array(
					'name'	=>	'/web/upload/logo',
					'dir'	=>	ROOT_PATH . 'web'.DS.'upload'.DS.'logo',
				),
			'/web/upload/tmp'	=>	array(
					'name'	=>	'/web/upload/tmp',
					'dir'	=>	ROOT_PATH . 'web'.DS.'upload'.DS.'tmp',
				),
			'/log'	=>	array(
					'name'	=>	'/log',
					'dir'	=>	ROOT_PATH . 'log',
				),
			'/log/error'	=>	array(
					'name'	=>	'/log/error',
					'dir'	=>	ROOT_PATH . 'log'.DS.'error',
				),
			'/log/import'	=>	array(
					'name'	=>	'/log/import',
					'dir'	=>	ROOT_PATH . 'log'.DS.'import',
				),
		);

		foreach ($check_dir as $dir) {
			if(!is_dir($dir['dir'])){
				$check_dir[$dir['name']]['result']= 'Dossier absent';

				if(@mkdir($dir['dir'], '0777'))
					$check_dir[$dir['name']]['result'] = '<br/>OK';
				else
					$check_dir[$dir['name']]['result'] = '<br/>Erreur';			
				
			}else{
				$check_dir[$dir['name']]['result']= '<span class="label label-success"><strong>OK</strong></span>';
			}
		}
		
		$this->registry->smarty->assign('check_dir', $check_dir);

		return $this->registry->smarty->fetch(VIEW_PATH.'adm'.DS.'check_dir.meg');
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

		// Suppression du logo existant
		if( isset($_POST['logo_delete'])){
			unlink(ROOT_PATH.'web'.DS.'upload'.DS.'logo'.DS.$this->registry->config['logo_name']);
			$this->registry->db->update('config', array('valeur' => NULL), array('cle =' => 'logo_name'));
		}

		// Traitement du formulaire
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
			'ape_multi_choice'				=>	0,
			'cron_token'					=>	getUniqueID(),
			'logo'							=>	'',
			'logo_name'						=>	'',
			'mailing_group_receive_resume'	=>	'',
			'register_open'					=>	0,
			'version_installed'				=>	'1.0.20140130',
			'email_sender'					=>	'',
			'campaign_rdv_success'			=>	0,
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
	 * Recupere et affiche tout les contacts de type societe_contact sans email
	 * @return mixed resultat au format HTML ou JSON
	 */
	public function contacts_no_emailAction(){

		// Mise a la corbeille des contacts directements
		if( !is_null($this->registry->Http->get('go_to_trash')) ){
			$query = "UPDATE contacts SET isDelete = '1' WHERE type = '3' AND (email IS NULL OR email = '') ";
			$result = $this->registry->db->query($query);
			return 'Contacts supprimés ('. $result .')';
		}
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

	public function users_view_log_contactsAction($uid){
		$logs = $this->registry->db->select('*')
					->from('logs')
					->where(array('user_id = ' => $uid))
					->order('date_log DESC')
					->limit(100)
					->get();

		$this->registry->smarty->assign('logs',$logs);

		return $this->registry->smarty->fetch(VIEW_PATH.'adm'.DS.'users_view_log_contacts.meg');
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

			$log = new log(array(
				'log' 		=> 	'Edition du l utilisateur : <a href="'.$this->registry->Helper->getLink("adm/users_edit/".$user->id) .'" title="">'. $user->identifiant .'</a>',
				'module'	=>	'user',
				'link_id'	=>	$user->id,
			));
			$log->save();

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

	public function users_deleteAction($uid){

		$user = new utilisateur();
		$user->get($uid);

		if($user->identifiant == 'admin'){
			$this->registry->Helper->pnotify('Utilisateur', '<strong>Il n est pas possible cet utilisateur.</strong>');
			return $this->users_indexAction();	
		}

		$this->registry->db->delete('user', $uid);

		$this->registry->Helper->pnotify('Utilisateur', 'Utilisateur supprimé de la base.');

		return $this->users_indexAction();
	}

	/**
	 * Supprime l utilisateur du groupe
	 * @param  [type] $uid [description]
	 * @return [type]      [description]
	 */
	public function users_remove_groupAction($uid){
		$gid = $this->registry->Http->get('gid');
		$this->remove_users_group($uid, $gid);
		$this->registry->Helper->pnotify('Utilisateur', 'Utilisateur retiré du groupe');
		return $this->users_editAction($uid);
	}

	public function users_add_in_groupAction($uid){
		if(!is_null($this->registry->Http->post('group'))){
			$group = $this->registry->Http->post('group');
			$group['user_id'] = $uid;
			$this->registry->db->insert('user_groupe', $group);
			$this->registry->Helper->pnotify('Groupe', 'Utilisateur ajouté au groupe.');
			return $this->users_editAction($uid);
		}

		// Recuperation des groupes ou l utilisateur est deja inscrit
		$users_in_group = $this->registry->db->get('user_groupe', array('user_id =' => $uid));

		$not_in = '';

		if(count($users_in_group) > 0){
			$not_in = 'id NOT IN (';
			foreach ($users_in_group as $row) {
				$not_in .= $row['groupe_id'].',';
			}

			$not_in = substr($not_in,0, -1);
			$not_in .= ')';
		}

		// Recuperation des groupes
		$this->registry->db->select('g.*')->from('groupe g');
		if(!empty($not_in)) $this->registry->db->where_free($not_in);
		$groups = $this->registry->db->get();

		$this->registry->smarty->assign('groups', $groups);
		$this->registry->smarty->assign('uid', $uid);

		return $this->registry->smarty->fetch(VIEW_PATH.'adm'.DS.'users_add_in_group.meg');
	}

	public function users_sync_ldapAction(){

		if(!is_null($this->registry->Http->post('usync'))){
			$users_to_sync = $this->registry->Http->post('usync');

			foreach($users_to_sync as $k => $v){
				// Verification si l utilisateur est dans la base
				$result = $this->registry->db->count('user', array('identifiant =' => $v));

				if($result == 0){

					// Recuperation utilisateur
					$user_ldap = $this->registry->adldap->user()->info($v, array("*"));

					$utilisateur = user_add_sso($user_ldap);
				}
			}

			$this->registry->Helper->pnotify('Utilisateurs', 'utilisateur importé', 'success');

			return $this->users_indexAction();
		}

		$lists = $this->registry->adldap->user()->all();

		$this->registry->smarty->assign('users_ldap', $lists);
		return $this->registry->smarty->fetch(VIEW_PATH.'adm'.DS.'users_sync_ldap_list.meg');
	}

	/**
	 * Affioche la liste des groupes utilisateur dans la base
	 * @return [type] [description]
	 */
	public function groupsAction(){
		// Appel du JS pour le form
		$this->getFormValidatorJs();

		$this->registry->smarty->assign('groups', $this->registry->db->get('groupe'));
		return $this->registry->smarty->fetch(VIEW_PATH.'adm'.DS.'users_groups.meg');
	}

	/**
	 * Retourne le formulaire de gestion de groupe
	 * @param  int $gid id du groupe
	 * @return mixed      code HTML du formulaire
	 */
	public function groups_get_formAction($gid = null){

		if(!empty($gid)){
			$this->registry->smarty->assign('group', $this->registry->db->get_one('groupe', array('id =' => $gid)));
		}
		return $this->registry->smarty->fetch(VIEW_PATH.'adm'.DS.'groups_form.meg');
	}

	/**
	 * Traite le formulaire pour enregistre un group dans la base
	 * @return groupsAction()
	 */
	public function groups_addAction(){
		if(!is_null($this->registry->Http->post('group'))){
			$group = new Basegroupe($this->registry->Http->post('group'));
			$group->save();
			$this->registry->Helper->pnotify('Groupe', 'Groupe  enregistré dans la base','success');
		}

		return $this->groupsAction();
	}

	/**
	 * Traite le formulaire pour enregistre un group dans la base
	 * @return groupsAction()
	 */
	public function groups_editAction($gid){
		if(!is_null($this->registry->Http->post('group'))){
			$group = new Basegroupe($this->registry->Http->post('group'));
			$group->save();
			$this->registry->Helper->pnotify('Groupe', 'Groupe  enregistré dans la base','success');
		}

		return $this->groupsAction();
	}

	/**
	 * Gere la suppression d un groupe
	 * @param  [type] $gid [description]
	 * @return [type]      [description]
	 */
	public function groups_deleteAction($gid){
		$this->registry->db->delete('groupe', $gid);
		$this->registry->Helper->pnotify('Groupe', 'Groupe supprimé','success');
		return $this->groupsAction();
	}

	public function groups_detailAction($gid){
		
		$group = new Basegroupe();
		$group->get($gid);
		
		// Utilisateurs dans le groupe
		$users = $this->registry->db->select('u.id, u.identifiant, ug.role')
					->from('user_groupe ug')
					->left_join('user u', 'ug.user_id = u.id')
					->where(array('ug.groupe_id =' => $gid))
					->get();

		$this->registry->smarty->assign('group', $group);
		$this->registry->smarty->assign('users', $users);

		return $this->registry->smarty->fetch(VIEW_PATH.'adm'.DS.'groups_detail.meg');
	}

	public function groups_form_add_inAction($gid){

		if(!is_null($this->registry->Http->post('group'))){
			$group = $this->registry->Http->post('group');
			$group['groupe_id'] = $gid;
			$this->registry->db->insert('user_groupe', $group);
			$this->registry->Helper->pnotify('Groupe', 'Utilisateur ajouté au groupe.');
			return $this->groups_detailAction($gid);
		}

		// Recuperation des utilisateuts deja dans la groupe pour exclure
		$users_in_group = $this->registry->db->get('user_groupe', array('groupe_id =' => $gid));

		$not_in = '';

		if(count($users_in_group) > 0){
			$not_in = 'id NOT IN (';
			foreach ($users_in_group as $row) {
				$not_in .= $row['user_id'].',';
			}

			$not_in = substr($not_in,0, -1);
			$not_in .= ')';
		}

		// Recuperation des utilisateurs
		$this->registry->db->select('u.*')->from('user u');
		if(!empty($not_in)) $this->registry->db->where_free($not_in);
		$users = $this->registry->db->get();

		$this->registry->smarty->assign('users', $users);
		$this->registry->smarty->assign('gid', $gid);

		return $this->registry->smarty->fetch(VIEW_PATH.'adm'.DS.'groups_form_add_in.meg');					
	}

	public function groups_remove_userAction($gid){
		$uid = $this->registry->Http->get('uid');
		$this->remove_users_group($uid, $gid);
		$this->registry->Helper->pnotify('Groupe', 'Utilisateur retiré du groupe.');
		return $this->groups_detailAction($gid);
	}

	/**
	 * Supprime la liaison user <-> group
	 * @param  int $uid [description]
	 * @param  int $gid [description]
	 * @return void
	 */
	private function remove_users_group($uid, $gid){
		$this->registry->db->delete('user_groupe', null, array('groupe_id =' => $gid, 'user_id =' => $uid));
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
	
	/**
	*	Verifie les societes cliente et passe les contact societe en client
	*/
	public function contacts_synchro_clt_societe_proAction(){
		$in_error = 0;
		$societes = $this->registry->db->get('contacts', array('client =' => 1, 'ctype =' => 'societe'));
		
		foreach($societes as $societe){
			// Recuperation des contacts
			$personnes = $this->registry->db->get('personne', array('societe_id =' => $societe['id']));
			
			if(!empty($personnes)){
				foreach($personnes as $personne){
					$data = $this->registry->db->get_one('contacts', array('id =' => $personne['contact_id']));
					if($data['client'] == 0){
						$this->registry->db->update('contacts', array('client' => 1), array('id =' => $data['id']));
						$in_error++;
					}
				}
			}
		}
		
		return $in_error .' personnes ont été modifiés';
	}

	public function contacts_migAction(){
		set_time_limit(0);
		$tot_societe = 0;
		$tot_personne = 0;
		$mig_societe = 0;
		$mig_personne = 0;

		// On commence par les societes
		$societes = $this->registry->db->get('societe');
		$tot_societe = count($societes);
		$html = null;
		foreach($societes as $row){
			// Recuperation du contact
			$data = $this->registry->db->get_one('contacts', array('id =' => $row['contact_id']));
			if(is_array($data)){
				$contact = new myObject($data);
				$contact->nom = $row['raison_social'];
				$contact->siret = $row['siret'];
				$contact->effectif = $row['effectif'];
				$contact->ape_id = $row['ape_id'];
				$contact->mother = $row['mother'];
				$contact->parent_id = $row['parent_id'];
				$contact->type = 1;
				
				// Sauvegarde de l'objet
				$this->registry->db->update('contacts', $contact, array('id =' => $row['contact_id']));

				// Enregistrement de la date de migration
				$this->registry->db->update('personne', array('date_mig' => date('Y-m-d H:i:s')), array('id =' => $row['id']));

				$mig_societe++;
			}			
		}
		
		// Personnes
		$personnes = $this->registry->db->get('personne');
		$tot_personne = count($personnes);

		foreach($personnes as $row){
			if(empty($row['nom']) && empty($row['prenom'])) goto nextboucle;

			// Recuperation du contact
			$data = $this->registry->db->get_one('contacts', array('id =' => $row['contact_id']));
			if(is_array($data)){
				$data = $this->registry->db->get_one('contacts', array('id =' => $row['contact_id']));
				$contact = new myObject($data);
				$contact->nom = $row['nom'];
				$contact->prenom = $row['prenom'];
				$contact->poste_id = $row['poste_id'];
				$contact->service_id = $row['service_id'];
				
				if(!empty($row['societe_id'])){
					$contact->parent_id = $row['societe_id'];
					$contact->ctype = 'societe_contact';
					$contact->type = 2;
				}else{
					$contact->type = 3;
					$contact->parent_id = null;
				}

				// Sauvegarde de l'objet
				$this->registry->db->update('contacts', $contact, array('id =' => $row['contact_id']));

				// Enregistrement de la date de migration
				$this->registry->db->update('personne', array('date_mig' => date('Y-m-d H:i:s')), array('id =' => $row['id']));

				$mig_personne++;
			}
			nextboucle:
		}
		
		return 'Total societe :'. $tot_societe .'<br/> Societe migree :'. $mig_societe .'<br/>Total personne :'. $tot_personne .'<br/>Personne migree :'. $mig_personne;
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
			}			
		}

		// Message a l utilisateur
		$this->registry->smarty->assign('FlashMessage','Emails supprimes');

		// On lui affiche de nouveau la liste des contacts
		return $this->contacts_maintenanceAction();
	}

	public function mailingsAction(){
		return $this->registry->smarty->fetch(VIEW_PATH . 'adm' . DS . 'mailings.meg');
	}

	public function mailingtypeAction(){
		$types = new mailing_type();
		
		$this->registry->smarty->assign('types', $types->get());
		
		return $this->registry->smarty->fetch(VIEW_PATH . 'adm' . DS . 'mailingtype.shark');
	}

	/**
	 * Affiche et traite les preferences du modules mailings
	 * @return [type] [description]
	 */
	public function mailings_settingsAction(){

		// Traitement du formulaire
		if(!is_null($this->registry->Http->post('config'))){
            
            $config = $this->registry->Http->post('config');
            
            foreach($config as $key => $value){                
                $this->registry->db->update('config', array('valeur' => $value), array('cle =' => $key));
            }
            
            // Suppression du cache actuel
            $this->registry->cache->remove('config');
            
            $this->registry->Helper->pnotify('Configuration', 'Configuration enregistrée');
          	
          	return $this->mailingsAction();
        }
        
        printform:

		$this->registry->smarty->assign('groups', $this->registry->db->get('groupe'));
		return $this->registry->smarty->fetch(VIEW_PATH . 'adm' . DS . 'mailings_settings.meg');
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

	public function mailings_resumeAction($type="hebdo"){

		if(empty($type)){
			$type = 'hebdo';
		}

		// Recuperation des mailins entre deux dates
		$this->load_manager('mailing');

		if($type=='hebdo'){
			// Tache à effectue le dimanche
			$date =  date("Y-m-d");

			if( !is_null($this->registry->Http->get('date')) ){
				$date = $this->registry->Http->get('date');	
			}	

			// Recuperation des jours de le semaine
			$days_of_week = week_from_monday($date);

			// Mailing a venir
			$mailings = $this->manager->mailing->get(1000,0, array('date_send >=' => $days_of_week[0], 'date_send <=' => $days_of_week[6], 'send =' => 1, 'valid =' => 1));
			
			// Mailing passe
			list($year_n, $month_n, $day_n) = explode('-', $days_of_week['6']);
			$date_next_week = date('Y-m-d', (mktime(12,0,0,$month_n, $day_n, $year_n) + 86400)); 
			$days_of_next_week = week_from_monday($date_next_week);

			$next_mailings = $this->manager->mailing->get(1000,0, array('date_wish >=' => $days_of_next_week[0], 'date_wish <=' => $days_of_next_week[6]));

			$date_feature_mailing = array($days_of_next_week[0], $days_of_next_week[6]);
			$date_previous_mailing = array($days_of_week[0], $days_of_week[6]); 

		}elseif($type=="week_pair"){
			// Tache a effectue le lundi
			
			// Recuperation du numéro de semaine
			$num_week = date('W');

			// On teste le numero de semaine
			if($num_week%2==1){
				return 'invalid week';
			}

			// Recuperation des mailings a venir sur la semaine pair et impair a venir
			$week_1 = week_from_monday(date("Y-m-d"));

			list($year_n, $month_n, $day_n) = explode('-', $week_1['6']);
			$date_next_week = date('Y-m-d', (mktime(12,0,0,$month_n, $day_n, $year_n) + 86400)); 
			$week_2 = week_from_monday($date_next_week);

			$next_mailings = $this->manager->mailing->get(1000,0, array('date_wish >=' => $week_1[0], 'date_wish <=' => $week_2[6]));

			// Recuperation des mailings envoye sur la semaines impair et pair passés
			list($year_n, $month_n, $day_n) = explode('-', $week_1['0']);
			$date_p_week = date('Y-m-d', (mktime(12,0,0,$month_n, $day_n, $year_n) - 86400)); 
			$previous_week_1 = week_from_monday($date_p_week);

			list($year_n, $month_n, $day_n) = explode('-', $previous_week_1['0']);
			$date_p_week = date('Y-m-d', (mktime(12,0,0,$month_n, $day_n, $year_n) - 86400)); 
			$previous_week_2 = week_from_monday($date_p_week);
			
			$mailings = $this->manager->mailing->get(1000,0, array('date_send >=' => $previous_week_2[0], 'date_send <=' => $previous_week_1[6], 'send =' => 1, 'valid =' => 1));

			$date_feature_mailing = array($week_1[0], $week_2[6]);
			$date_previous_mailing =  array($previous_week_2[0], $previous_week_1[6]);
		}

		$this->registry->smarty->assign(array(
			'date_feature_mailing'	=>	$date_feature_mailing,
			'date_previous_mailing' => 	$date_previous_mailing,
			'type'					=>	$type,
		));

		$this->registry->smarty->assign('mailings', $mailings);		
		$this->registry->smarty->assign('next_mailings', $next_mailings);

		$mail_sujet = "Recap hebdomadaire des mailings";
		$mail_body =  $this->registry->smarty->fetch(VIEW_PATH . 'adm' . DS . 'mailings_mail_body_resume.meg');

		// Recuperation des utilisateur
		$users = $this->registry->db->select('u.*')
				->from('groupe g')
				->left_join('user_groupe ug','g.id = ug.groupe_id')
				->left_join('user u','ug.user_id = u.id')
				->where(array('ug.groupe_id =' => $this->registry->config['mailing_group_receive_resume']))
				->get();
		var_dump($users);
		$i=0;
		foreach($users as $row){
			if(!empty($row['email'])){
				$i++;
				sendEmail($row['email'], 'nepasrepondre@afpi-centre-valdeloire', $mail_sujet, '', $mail_body);
			}
		}		

		return 'ok - email envoyé '. $i .' fois';
	}

	public function modulesAction(){
		$modules = $this->registry->db->get('modules');

		$this->registry->smarty->assign('modules',$modules);
		$this->registry->smarty->assign('ctitre', 'Administration :: Modules');

		return $this->registry->smarty->fetch(VIEW_PATH . 'adm' . DS . 'modules.meg');
	}

	public function modules_unactiveAction($mid){
		$this->registry->db->update('modules', array('actif' => 0, 'id' => $mid));
		$this->registry->Helper->pnotify('Modules', 'Module desactivé');
		return $this->modulesAction();
	}

	public function modules_activeAction($mid){
		$this->registry->db->update('modules', array('actif' => 1, 'id' => $mid));
		$this->registry->Helper->pnotify('Modules', 'Module activé');
		return $this->modulesAction();
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

	/**
	 * Affiche la liste des tiers avec les differentes actions.
	 * @return [type] [description]
	 */
	public function tiersAction(){

		// Verification modules actifs
		if($this->registry->modules['tiers']['actif'] != 1){
			$this->registry->Helper->pnotify('Erreur', 'Module desactive');
			return $this->indexAction();
		}

		$this->getFormValidatorJs();

		$this->load_manager('tier');

		$tiers = $this->manager->tier->getAll();

		$this->registry->smarty->assign('tiers', $tiers);

		return $this->registry->smarty->fetch(VIEW_PATH . 'adm' . DS . 'tiers.meg');
	}

	/**
	 * Retourne le formulaire de gestion des tiers
	 * @param  [type] $tid [description]
	 * @return [type]      [description]
	 */
	public function tiers_formAction($tid = null){
		
		if(!empty($tid)){
			$tier = new tier();
			$tier->get($tid);

			// Verification que le tier demandé est valide
			if(empty($tier->id)){
				$this->registry->Helper->pnotify('Tiers', 'Erreur tier introuvable dans la base','danger');
				return $this->tiersAction();
			}

			$this->registry->smarty->assign('tier', $tier);
		}
		
		$this->registry->smarty->assign('types', $this->registry->db->get('tiers_type'));
		return $this->registry->smarty->fetch(VIEW_PATH.'adm'.DS.'tier_form.meg');
	}

	/**
	 * Traite le formulaire d ajout de tier
	 * @return [type] [description]
	 */
	public function tier_addAction(){
		if( !is_null($this->registry->Http->post('tier'))){
			$tier = new tier($this->registry->Http->post('tier'));
			$tier->save();
			$this->registry->Helper->pnotify('Tiers', 'Informations enregistrées dans la base','success');
		}

		return $this->tiersAction();
	}

	/**
	 * Traite le formulaire d edition
	 * @return [type] [description]
	 */
	public function tier_editAction($tid){
		if( !is_null($this->registry->Http->post('tier'))){
			$tier = new tier($this->registry->Http->post('tier'));
			$tier->save();
			$this->registry->Helper->pnotify('Tiers', 'Informations enregistrées dans la base','success');
		}

		return $this->tiersAction();
	}

	/**
	 * Supprime un tier de la base
	 * @param  [type] $tid [description]
	 * @return [type]      [description]
	 */
	public function tier_deleteAction($tid){
		$tier = new tier();
		$tier->get($tid);

		// Verification que le tier demandé est valide
		if(empty($tier)){
			$this->registry->Helper->pnotify('Tiers', 'Erreur tier introuvable dans la base','danger');
			return $this->tiersAction();
		}

		$this->registry->db->delete('tiers', $tid);
		$this->registry->Helper->pnotify('Tiers', 'Informations supprimées dans la base','success');
		return $this->tiersAction();
	}


	/*-- PRODUCT --*/

	/*-- LOGS --*/
	public function logsAction(){
		$per_page = 50;

		$nb_rows = $this->registry->db->count('logs');

		// Recuperation des logs dans la base
		$logs = $this->registry->db->get('logs', null, 'date_log DESC', $per_page, getOffset($per_page));

		$this->registry->smarty->assign('logs', $logs);
		
		return $this->registry->smarty->fetch(VIEW_PATH.'adm'.DS.'logs.meg');
	}
	
	public function logs_import_form_clogAction(){
		set_time_limit(0);
		ini_set("memory_limit","1024M");
		
		// Recuperation de tout les clogs
		$clogs = $this->registry->db->get('contacts_log');
		
		// On boucles pour les inseres et supprimé les clogs
		foreach($clogs as $row){
			$data = array(
				'date_log' 	=> 	$row['date_log'],
				'log'		=>	$row['log'],
				'user_id'	=>	$row['user_id'],
				'module'	=>	'contacts',
				'link_id'	=>	$row['contact_id'],
			);
			$log = new log($data);
			$log->save();
			$this->registry->db->delete('contacts_log', $row['id']);
		}
	}

	public function rdvAction(){
		return $this->rdv_categoriesAction();
	}

	/**
	 * Affiche la liste des categories
	 * @return string HTML
	 */
	public function rdv_categoriesAction(){
		$categories = $this->registry->db->get('rdv_categories', null, 'libelle');
		$this->registry->smarty->assign('categories', $categories);
		return $this->registry->smarty->fetch(VIEW_PATH.'adm'.DS.'rdv_categories.meg');

	}

	/**
	 * Affiche le formulaire pour l edition d'une categorie
	 * @param  [type] $cid [description]
	 * @return [type]      [description]
	 */
	public function rdv_categories_getformAction($cid = null){
		if(!empty($cid)){
			$this->registry->smarty->assign('categorie', $this->registry->db->get_one('rdv_categories', array('id =' => $cid)));
		}
		return $this->registry->smarty->fetch(VIEW_PATH.'adm'.DS.'rdv_categories_form.meg');
	}

	/**
	 * Traite le formulaire d ajout de categorie
	 * @return [type] [description]
	 */
	public function rdv_categories_addAction(){
		if(!is_null($this->registry->Http->post('categorie'))){
			$categorie = $this->registry->Http->post('categorie');
			$this->registry->db->insert('rdv_categories', $categorie);
			$this->registry->Helper->pnotify('Rendez vous','Catégorie ajoutée','success');
		}
		return $this->rdv_categoriesAction();
	}

	/**
	 * Traite le formulaire d editon des categories
	 * @param  [type] $cid [description]
	 * @return [type]      [description]
	 */
	public function rdv_categories_editAction($cid){
		if(!is_null($this->registry->Http->post('categorie'))){
			$categorie = $this->registry->Http->post('categorie');
			$this->registry->db->update('rdv_categories', $categorie);
			$this->registry->Helper->pnotify('Rendez vous','Catégorie modifiée','success');
		}
		return $this->rdv_categoriesAction();
	}

	public function rdv_categories_deleteAction($cid){
		$this->registry->db->delete('rdv_categories', $cid);
		$this->registry->db->update('rdv', array('categorie_id' => null), array('categorie_id =' => $cid));
		$this->registry->Helper->pnotify('Rendez vous','Catégorie supprimée','success');
		return $this->rdv_categoriesAction();
	}
}