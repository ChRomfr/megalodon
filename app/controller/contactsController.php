<?php

class contactsController extends Controller{	
	
	public function indexAction(){ return $this->index2Action(); }
	
	/**
	*	Affichage de contacts en les listants avev une requete ajax
	*	Reduit le délai d'affichage de la page
	*/
	public function index2Action(){	return $this->registry->smarty->fetch(VIEW_PATH . 'contacts' . DS . 'index2.shark'); }
	
	/**
	 * Affiche la liste des contacts
	 * @return [type] [description]
	 */
	public function ajax_load_contactsAction(){
		$per_page = $this->registry->config['per_page'];
		
		if( !empty($_SESSION['utilisateur']['contacts_per_page']) ){
			$per_page = $_SESSION['utilisateur']['contacts_per_page'];
		}
	
		$this->load_manager('contacts');
		
		$nb_contact = $this->manager->contacts->count($this->getWhere());

		$contacts = $this->manager->contacts->get($this->getWhere(),$per_page, getOffset($per_page), 'c.ctype, c.city, c.email');

		// Recuperation des entreprises avec paginations
		$base_url = $_SERVER['REQUEST_URI'];		
		$base_url = str_replace('ajax_load_contacts','index2', $base_url);	
		
		$Pagination = new Zebra_Pagination();
		$Pagination->base_url($base_url);
		$Pagination->records($nb_contact);
		$Pagination->records_per_page($per_page);
		$this->registry->smarty->assign('Pagination',$Pagination);
		
		$this->registry->smarty->assign('contacts',	$contacts);
		$this->registry->smarty->assign('nb_contacts', $nb_contact);
		
		echo $this->registry->smarty->fetch(VIEW_PATH . 'contacts' . DS . 'ajax_load_contacts.shark');
		
		exit;
	}
	
	public function csvAction(){
		$this->load_manager('contacts');
		
		$contacts = $this->manager->contacts->get($this->getWhere());
		
		// Contruction du fichier
		$Csv = '';
		$Csv = "Contact;Email\n";
		foreach ($contacts as $row) {
			if( !empty($row['raison_social']) ){
				$Csv .= $row['raison_social'] . ";" . $row['email'] ."\n";
			}else{
				$Csv .= $row['nom'] . " " . $row['prenom'] . ";" . $row['email'] ."\n";
			}			
		}

		// On affiche le fichier
		header('Content-Type: application/csv-tab-delimited-table');
		header('Content-disposition: filename=Export_Entreprise.csv');
		echo $Csv;
		exit;
	}
	
	public function addAction(){
		
		if( !is_null($this->registry->Http->post('contact'))){
			
			// Recuperation indormations formulaire
			$Data = $this->registry->Http->post('contact');

			// Enregistrement du contacts dans la base
			$contact = $this->contact_add($Data);

			if( isset($Data['per']) )
				$this->personne_add($Data['per'], $contact->id);
			elseif(isset($Data['ets']))
				$this->societe_add($Data['ets'], $contact->id);			
									
			// On traite les telephones
			foreach($Data['telephones'] as $row){
				$this->telephone_add($row, $contact->id);				
			}
			
			// Traitement des categories
			$categories = $this->registry->Http->post('categorie');
			if(is_array($categories) && !empty($categories)){
				foreach ($categories as $key => $value) {
					$this->registry->db->insert('contacts_categorie',array('contact_id' => $contact->id, 'categorie_id' => $value));
				}
			}
						
			$this->registry->Helper->pnotify('Contact', 'Contact enregistré');

			return $this->detailAction($contact->id);
		}

		if(!is_null($this->registry->Http->get('societe'))){
			$this->load_manager('contacts');
			$this->registry->smarty->assign('ets',$this->manager->contacts->getById($this->registry->Http->get('societe')));
		}
		

		$this->getFormValidatorJs();
		$this->registry->load_web_lib('meg/contacts_add.min.js','js','footer');
		//$this->registry->load_web_lib('meg/contacts_add.js','js','footer');

		return $this->registry->smarty->fetch(VIEW_PATH.'contacts'.DS.'add.tpl');
	}

	/**
	 * Traite l enregistrement dans la base d un nouveau contact
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	private function contact_add($data){
		$contact = new contacts($data);
		$contact->isValid();
		$contact->isDelete = 0;
		
		// Determination du ctype
		if(isset($data['ets'])){
			$contact->ctype = 'societe';
		}elseif(isset($data['per']['societe_id'])){
			$contact->ctype = 'societe_contact';
		}else{
			$contact->ctype = 'particulier';
		}

		$contact->id = $contact->save();

		// Enregistrement du log
		$clog =  new clog(array('date_log' => date("Y-m-d H:i:s"), 'contact_id' => $contact->id, 'user_id' => $_SESSION['utilisateur']['id'], 'log' => 'Ajout du contact dans la base'));
		$clog->save();

		return $contact;
	}

	private function personne_add($data, $cid){
		// Personne physique
		$personne = new personne($data);
		$personne->contact_id = $cid;
		$personne->save();

		return $personne;
	}

	private function societe_add($data, $cid){
		// Entreprise
		$societe = new societe($data);
		$societe->contact_id = $cid;
		$societe->save();

		return $societe;
	}

	private function telephone_add($data, $cid){
		$telephone = new telephone($data);
		if($telephone->isValid()){
			$telephone->contact_id = $cid;
			$telephone->save();
		}
	}

	/**
	 * Affiche et traite le formulaire d edition de contact
	 * @param  [type] $id [description]
	 * @return [type]     [description]
	 */
	public function editAction($id){

		// Traitement du formulaire
		if(!is_null($this->registry->Http->post('contact'))){
			$data = $this->registry->Http->post('contact');

			$contact = new contacts($data);
			$contact->id = $id;
			$this->registry->db->update('contacts', $contact, array('id =' => $contact->id));
			
			//$contact->save();

			if( isset($data['per']) ){
				// Personne physique
				$personne = new personne($data['per']);
				$personne->contact_id = $contact->id;
				$personne->save();
			}
			if(isset($data['ets'])){
				// Entreprise
				$societe = new societe($data['ets']);
				$societe->contact_id = $contact->id;
				$societe->save();
			}
			
			return $this->detailAction($id);
		}

		// Affichage du formulaire
		$this->getFormValidatorJs();

		$this->load_manager('contacts');
		
		$contact = $this->manager->contacts->getById($id);

		if(!empty($contact->societe_id)){
			$societe = $this->registry->db->get_one('societe', array('id =' => $contact->societe_id));
			$this->registry->smarty->assign('societe', $societe);
		}

		$this->registry->smarty->assign(array(
			'ctitre'		=>	'Editon contact',
			'contact'		=>	$contact,
			'apes'			=>	$this->registry->db->get('ape',null,'code'),
		));
		
		return $this->registry->smarty->fetch(VIEW_PATH.'contacts'.DS.'edit.shark');
	}
	
	public function detailAction($id){
		$this->registry->load_web_lib('gmap3/gmap3.js','js','footer');
		$this->registry->load_web_lib('gmap3/gmap3.css','css');
		$this->registry->load_web_lib('meg/contacts_fiche.js','js','footer');

		$this->load_manager('contacts');
		
		$contact = $this->manager->contacts->getById($id, $_SESSION['utilisateur']['historique_contact']);
		
		if( $_SESSION['utilisateur']['isAdmin'] > 0 && !empty($contact['raison_social']) ){
			// Matching rs similaire
			$matchings = $this->registry->db->select('raison_social, contact_id')->from('societe s')->where_free(' raison_social LIKE "%'. $contact['raison_social'] .'%" AND contact_id != '. $id .' ')->get();
			
			$this->registry->smarty->assign('matchings', $matchings);
		}
		
		// Recuperation des agences
		if( $contact['mother'] == 1 ){
			$agences = $this->manager->contacts->getAgences($contact['sid']);
			
			$this->registry->smarty->assign('agences', $agences);
		}
		
		// Recuperation siege social
		if( !empty($contact['parent_id']) ){
			$siege = $this->manager->contacts->getSiegeSocial($contact['parent_id']);
			
			$this->registry->smarty->assign('siege', $siege);
		}
		
		$this->registry->smarty->assign('contact', $contact);
		
		return $this->registry->smarty->fetch(VIEW_PATH.'contacts'.DS.'detail.tpl');
	}
	
	/**
	*	Marque comme supprime un contact, ce qui a pour effet de le masqué dans la liste
	*	mais ne le retire pas de la base. Pour supprime un contact definitivement, il faut passer
	*	par les fonction d'administration
	*	@param int $id Identifiant du contact a supprime
	*	@return string Code html
	*/
	public function deleteAction($id){

		if( $_SESSION['utilisateur']['isAdmin'] == 0){
			$this->registry->smarty->assign('FlashMessage','Vous n\'avez pas les droits pour effectuer cette action !');
			return $this->indexAction();
		}

		$contact = new contacts();
		$contact->get($id);
		$contact->setDelete();
		$contact->save();
		return $this->indexAction();
	}

	public function massdeleteAction(){

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
				$contact->setDelete();
				$contact->save();
			}
			
		}

		// Message a l utilisateur
		$this->registry->smarty->assign('FlashMessage','Contacts supprimé');

		// On lui affiche de nouveau la liste des contacts
		return $this->indexAction();
	}
	
	/**
	*	Affiche le specifique du formulaire
	*
	*/
	public function ajaxloadaddtypeformAction(){
		$type = $this->registry->Http->get('type');
		$type_socio = $this->registry->Http->get('typesocio');
		$societe = $this->registry->Http->get('societe');
		
		if($type == 'entreprise'){
			$this->registry->smarty->assign('apes',$this->registry->db->get('ape',null,'code'));
			
			return $this->registry->smarty->fetch(VIEW_PATH.'contacts'.DS.'form_add_pro.tpl');
		}elseif($type == 'personne'){
			if($type_socio == 1){
				if(!empty($societe)){				
					$this->load_manager('contacts');
					$this->registry->smarty->assign('ets',$this->manager->contacts->getById($this->registry->Http->get('societe')));			
				}
			}
			
			return $this->registry->smarty->fetch(VIEW_PATH.'contacts'.DS.'form_add_par.tpl');
		}
	}
	
	/**
	*	Verifie si l email est valide et present dans la base
	*	Cette fonction peut etre appelle comme page pour une verification via ajax
	*	@return string resultat bool en toute lettre
	*/
	public function checkemailAction(){
		$email = $_GET['contact']['email'];
		$email = trim($email);

		if( VerifieAdresseMail($email) == false ):
			return "false";
		endif;

		$Result = $this->app->db->count('contacts', array('email =' => $email) );

		if( $Result > 0):
			return "false";
		else:
			return "true";
		endif;
	}
	
	public function sendemailAction($eid){
		
		if( !is_null($this->registry->Http->post('mail')) ){
			$mail = $this->registry->Http->post('mail');

			if( !VerifieAdresseMail($mail['de']) || !VerifieAdresseMail($mail['a'])){
				return $this->detailAction($eid);
			}

			$email = array(
				'entreprise_id'		=>	$eid,
				'user_id'			=>	$_SESSION['utilisateur']['id'],
				'a'					=>	$mail['a'],
				'de'				=>	$mail['de'],
				'sujet'				=>	trim($mail['sujet']),
				'body'				=>	$mail['body'],
				'result'			=>	'none',
				'date_send'			=>	date("Y-m-d H:i:s"),
				'pj'				=>	null,
			);

			// Envoie de l email			
			if(is_file($_FILES['pj']['tmp_name'])){
				// Traitement email avec pj
				$extension_upload = strtolower(  substr(  strrchr($_FILES['pj']['name'], '.')  ,1)  );
				$name_file = md5(uniqid(rand(), true));
				
				$dest_file = ROOT_PATH.'web'.DS.'upload'.DS.'tmp'.DS.$name_file.'.'.$extension_upload;

				move_uploaded_file($_FILES['pj']['tmp_name'],$dest_file);

				$email['pj'] = $name_file.'.'.$extension_upload;

				// Envoie de l email et recuperation du resultat
				$result = sendEmail($mail['a'],$mail['de'],$mail['sujet'],'',$mail['body'], $dest_file);
			}else{
				$result = sendEmail($mail['a'],$mail['de'],$mail['sujet'],'',$mail['body']);
			}

			$email['result'] = $result;

			// Sauvegarde dans la base du mail
			$email_id = $this->registry->db->insert('contacts_email', $email);			

			if( $result === true){
				$this->registry->db->update('entreprise_email', $email, array('id =' => $email_id));
				sendEmail($mail['de'],$mail['de'], 'Copie message - '. $mail['sujet'], '',$mail['body']);
				$this->registry->Helper->pnotify('Email','Email envoye. Une copie a ete envoye a l\\\'adresse suivante : '. $mail['de']);
				// Enregistrement du log
				$clog =  new clog(array('date_log' => date("Y-m-d H:i:s"), 'contact_id' => $eid, 'user_id' => $_SESSION['utilisateur']['id'], 'log' => 'Envoie email depuis la fiche #'. $email_id));
				$clog->save();
			}else{
				$this->registry->db->update('entreprise_email', $email, array('id =' => $email_id));
				$this->registry->Helper->pnotify('FlashMessage','Une erreur est survenue durant l\\\'envoie : '. $result);
				// Enregistrement du log
				$clog =  new clog(array('date_log' => date("Y-m-d H:i:s"), 'contact_id' => $eid, 'user_id' => $_SESSION['utilisateur']['id'], 'log' => 'Echec envoie email depuis la fiche #'. $email_id));
				$clog->save();
			}
			
			return $this->detailAction($eid);
		}else{
			return $this->detailAction($eid);
		}
	}

	/**
	 * Recupere et retourne les emails envoye depuis la fiche
	 * @param  [type] $eid [description]
	 * @return [type]      [description]
	 */
	public function ajax_get_email_detailAction($eid){
		$email = $this->registry->db->get_one('contacts_email', array('id =' =>$eid));

		if(!empty($email['pj'])){
			// Verification PJ toujours sur le serveur
			if(!is_file(ROOT_PATH.'web'.DS.'upload'.DS.'tmp'.DS.$email['pj'])){
				$email['pj'] = 'Error file not found !';
			}
		}
		
		$this->registry->smarty->assign('email', $email);

		return $this->registry->smarty->fetch(VIEW_PATH.'contacts'.DS.'get_email_detail.meg');
	}
	
	/**
	 * [getWhere description]
	 * @return [type] [description]
	 */
	public function getWhere($data_filtre = null){

		if(is_null($data_filtre) && isset($_GET['filtre'])){
			$filtres = $_GET['filtre'];
		}else{
			$filtres = $data_filtre;
		}
		
		// On recupere que les valides
		$param = " c.valid = 1 ";
		
		// Gere l'affichage ou non des elements a la corbeille
		if( isset($filtres['deleted']) ){
			$param .= " AND c.isDelete = 1 ";
		}else{
			$param .= " AND c.isDelete != 1 "; 
		}
		
		// Traitement des filtres
		if( !empty($filtres) ){

			if( isset($filtres['departement']) && !empty($filtres['departement']) && !is_array($filtres['departement']) ){
				$param .= " AND c.code_postal LIKE '". $filtres['departement'] ."%' ";
			}elseif( isset($filtres['departement']) && !empty($filtres['departement']) && is_array($filtres['departement']) ){
				$param .= " AND ( ";
					$i=0;
					foreach($filtres['departement'] as $k => $v){
						if($i!=0){
							$param .= " OR c.code_postal LIKE '". $v ."%' ";
						}else{
							$param .= " c.code_postal LIKE '". $v ."%' ";;
						}
						$i++;
					}
				$param .= " ) ";
			}

			if( isset($filtres['email_is_valid']) ){
				$param .= " AND c.email != '' ";
			}
			
			// APE
			if( isset($filtres['ape_id']) && !empty($filtres['ape_id']) ){
				$param .= " AND s.ape_id =  '". $filtres['ape_id'] ."'";
			}elseif(isset($filtres['ape']) && !empty($filtres['ape']) ){
				$ape = $filtres['ape'];
				
				if(!empty($ape)){
					
					$param .= " AND ( ";
					$i=0;
					foreach($ape as $k => $v){
						if($i!=0){
							$param .= " OR s.ape_id = ". $v ." ";
						}else{
							$param .= " s.ape_id = ". $v ." ";
						}
						$i++;
					}
					$param .= " ) ";
				}
			}

			// Effectif
			if( isset($filtres['effectif_mini']) && !empty($filtres['effectif_mini']) && isset($filtres['effectif_max']) && !empty($filtres['effectif_max']) ){
				$param .= " AND s.effectif >= ". $filtres['effectif_mini'] ." AND s.effectif <= ". $filtres['effectif_max'] ." ";
			}
			
			// Client
			if( isset($filtres['is_client']) ){
				$param .= " AND c.client = 1 ";
			}
			
			// Query
			if( isset($filtres['query']) && !empty($filtres['query']) ){
				$s = trim($filtres['query']);
				if( is_numeric($s) ){
					// Recherche sur siret
					$param .= " AND ( s.siret = '". $s ."' OR t.telephone = '". $s ."' ) ";
				}elseif(VerifieAdresseMail($s) == true){
					$param .= " AND c.email = '". $s ."' ";
				}else{
					// Recherche sur la raison social
					$param .= " AND (s.raison_social LIKE '%".$s."%' OR p.nom LIKE '%".$s."%' ) ";
				}
			}

			if( isset($filtres['poste']) && !empty($filtres['poste'])){
				$param .= ' AND p.poste_id = '. $filtres['poste'] .' ';
			}

			if( isset($filtres['service']) && !empty($filtres['service'])){
				$param .= ' AND p.service_id = '. $filtres['service'] .' ';
			}

			// Traitement du ctype
			$param .= " AND (";
			$param_ctype = 0;

			if( isset($filtres['societes']) ){
				$param .= 'c.ctype = "societe" ';
				$param_ctype++;
			}	

			if( isset($filtres['particulier']) ){
				if($param_ctype > 0){
					$param .= ' OR ';
				}
				$param .= 'c.ctype = "particulier" ';
				$param_ctype++;
			}

			if( isset($filtres['societe_contact']) ){
				if($param_ctype > 0){
					$param .= ' OR ';
				}
				$param .= 'c.ctype = "societe_contact" ';
				$param_ctype++;
			}

			$param .= ' ) ';

		}
		
		return $param;
	}

	public function updaterowsAction(){
			$entreprises = $this->registry->db->get('entreprises', array('migration =' => 0, 'new_id !=' => ''));

			foreach($entreprises as $row){
				$contact = new contacts();

				$contact->id = $row['new_id'];
				$contact->client = $row['client'];
				$contact->adresse1 = $row['adresse1'];
				$contact->adresse2 = $row['adresse2'];
				$contact->code_postal = $row['code_postal'];
				$contact->ville = $row['ville'];
				$contact->pays = 'France';
				$contact->code_interne = $row['code_interne'];
				$contact->type = 1;
				$contact->ctype = 'societe';
				$contact->email = $row['email'];
				$contact->lat = $row['lat'];
				$contact->lng = $row['lng'];
				$contact->date_last_geoloc = $row['date_last_geoloc'];
				$contact->valid = 1;
				$contact->isDelete = 0;

				// Sauvegarde
				$contact->save();

				// Enregistrement du log
				$clog =  new clog(array('date_log' => date("Y-m-d H:i:s"), 'contact_id' => $contact->id, 'user_id' => $_SESSION['utilisateur']['id'], 'log' => 'Update contact'));
				$clog->save();

				$societe = new societe();
				$societe->contact_id = $contact->id;
				$societe->ape_id = $row['ape_id'];
				$societe->effectif = $row['effectif'];
				$societe->raison_social = $row['raison_social'];
				$societe->siret = $row['siret'];

				// Sauvegarde
				$societe->save();

				// Enregistrement du log
				$clog =  new clog(array('date_log' => date("Y-m-d H:i:s"), 'contact_id' => $contact->id, 'user_id' => $_SESSION['utilisateur']['id'], 'log' => 'Update'));
				$clog->save();

				// Mise a jour de l ancien contact
				$row['migration'] = 1;

				$this->registry->db->update('entreprises', $row);
			}


				$this->updatecontact();

				return 'ok';
		}

	private function updatecontact(){
		
		// Recuperation des contacts
		$contact = $this->registry->db->get('contact', array('migration =' => 0, 'new_id !=' => '""'));

		foreach($contact as $row){
			// Recuperation des informations entreprises
			$infos_ets = $this->registry->db->get_one('entreprises', array('id =' => $row['entreprise_id']));

			$contact = new contacts();

			$contact->id = $row['new_id'];
			$contact->client = 0;
			$contact->adresse1 = $infos_ets['adresse1'];
			$contact->adresse2 = $infos_ets['adresse2'];
			$contact->code_postal = $infos_ets['code_postal'];
			$contact->ville = $infos_ets['ville'];
			$contact->pays = 'France';
			$contact->code_interne = $row['code_interne'];
			$contact->type = 1;
			$contact->ctype = 'societe_contact';
			$contact->email = $row['email'];
			$contact->lat = $infos_ets['lat'];
			$contact->lng = $infos_ets['lng'];
			$contact->date_last_geoloc = $infos_ets['date_last_geoloc'];
			$contact->valid = 1;
			$contact->isDelete = 0;

			// Sauvegarde
			$contact->save();

			// Enregistrement du log
			$clog =  new clog(array('date_log' => date("Y-m-d H:i:s"), 'contact_id' => $contact->id, 'user_id' => $_SESSION['utilisateur']['id'], 'log' => 'Maj enregistrement'));
			$clog->save();

			// Creation de la personne
			$personne = new personne();
			$personne->contact_id = $contact->id;
			$personne->nom = $row['nom'];
			$personne->prenom = $row['prenom'];
			$personne->societe_id = $infos_ets['new_id'];
			$personne->poste_id = $row['poste_id'];
			$personne->service_id = $row['service_id'];


			$personne->save();

			// Enregistrement du log
			$clog =  new clog(array('date_log' => date("Y-m-d H:i:s"), 'contact_id' => $contact->id, 'user_id' => $_SESSION['utilisateur']['id'], 'log' => 'Maj enregistrement'));
			$clog->save();


			if( empty($personne->nom)){
				$contact->isDelete = 1;
				$contact->save();
				$clog =  new clog(array('date_log' => date("Y-m-d H:i:s"), 'contact_id' => $contact->id, 'user_id' => $_SESSION['utilisateur']['id'], 'log' => 'Migration - Contact marque comme supprime car information imcomplete !'));
				$clog->save();
			}

			// Mise a jour de l ancien contact
			$row['migration'] = 1;

			$this->registry->db->update('contact', $row);
		}
	}

	
	public function maintenanceAction(){		
		$adm = $this->load_controller('adm');
		return $adm->contacts_maintenanceAction();
	}

	/**
	 * Permet la suppression des contacts sans nom et raison social
	 * @return [type] [description]
	 */
	public function maintenance_massdelete_nonameAction(){
		set_time_limit(0);

		$qte = 0;

		$this->load_manager('contacts');

		$contacts = $this->manager->contacts->get($this->getWhere());

		foreach($contacts as $row){
			if(empty($row['nom'])){
				$qte++;
				$contact = new contacts();
				$contact->get($row['id']);
				$contact->setDelete();
				$contact->save();
			}
		}

		return $qte;
	}

	public function ajaxgetformeditemailmaintenanceAction($id){

		// Traitement du formulaire
		if( !is_null($this->registry->Http->post('contact')) ){
			$contact = $this->registry->Http->post('contact');

			// On retourne le tableau
			$this->registry->db->update('contacts', $contact);

			// FlashMessage
			$this->registry->smarty->assign('FlashMessage','E-mail modifié');

			return $this->maintenanceAction();
		}		

		// Recuperartion et envoie a smarty du contact
		$this->registry->smarty->assign('contact', $this->registry->db->get_one('contacts', array('id =' => $id)));

		// Envoie du code HTML pour traitement AJAX
		return $this->registry->smarty->fetch(VIEW_PATH . 'contacts' . DS . 'ajaxgetformeditemailmaintenance.shark');
	}

	public function maintenancegetcorbeilleAction(){
		$this->load_manager('contacts');

		$this->registry->smarty->assign(array(
			'contacts'		=>	$this->manager->contacts->getInCorbeille(),
		));

		return $this->registry->smarty->fetch(VIEW_PATH.'contacts'.DS.'maintenancegetcorbeille.tpl');
	}

	/**
	*	Permet la restauration d un contact depuis la corbeille
	*	@param int $cid identifiant dans la base de données du contact
	*	@return string code html
	*/
	public function ajaxrestoreAction($cid){

		$contact = new contacts();
		$contact->get($cid);
		$contact->setDelete('0');
		$contact->save();
		
		return $this->maintenancegetcorbeilleAction();
	}

	public function maitenancecleantrashAction(){
		$this->load_manager('contacts');
		$result = $this->manager->contacts->cleantrash();
		return $result;
	}

	public function geolocajaxAction(){
		set_time_limit(0);
		
		$result_ok = 0;
		$result_fail = 0;

		require_once ROOT_PATH . 'kernel' . DS . 'lib' . DS . 'GoogleMapAPIv3.class.php';
		$gmap = new GoogleMapApi();
		$contacts = $this->registry->db->get('contacts');

		foreach($contacts as $row){
			$coord = $gmap->geocoding($row['adress'] .' '. $row['zip_code'] . ' '. $row['city'] . ' FRANCE');
			if(is_numeric($coord[2])){
				// Geoloc OK
				$data = array(
					'lat'				=>	$coord[2],
					'lng' 				=> 	$coord[3],
					'date_last_geoloc'	=>	date("Y-m-d H:i:s"),
					'id'				=>	$row['id']
				);

				$clog =  new clog(array('date_log' => date("Y-m-d H:i:s"), 'contact_id' => $row['id'], 'user_id' => $_SESSION['utilisateur']['id'], 'log' => 'Geoloc - Recuperation des coordonnées GPS'));
				$clog->save();

				$result_ok++;
			}else{
				$data = array(
					'date_last_geoloc'	=>	date("Y-m-d H:i:s"),
					'id'				=>	$row['id'],
				);
				$clog =  new clog(array('date_log' => date("Y-m-d H:i:s"), 'contact_id' => $row['id'], 'user_id' => $_SESSION['utilisateur']['id'], 'log' => 'Geoloc - Echec de la recuperation des coordonnées GPS'));
				$clog->save();
				$result_fail++;
			}

			// Sauvegarde
			$this->registry->db->update('contacts',$data);
		}// endforeach

		$this->registry->smarty->assign(array(
			'tot_contacts'		=>	count($contacts),
			'result_fail'		=>	$result_fail,
			'result_ok'			=>	$result_ok,
		));

		return $this->registry->smarty->fetch(VIEW_PATH.'contacts'.DS.'geolocajax.tpl');	
	}

	/**
	 * Geolocalise les contacts sans coordonnees
	 * @return [type] [description]
	 */
	public function geolocemptycoordajaxAction(){
		set_time_limit(0);
		$result_ok = 0;
		$result_fail = 0;
		$this->load_manager('contacts');
		$contacts =	$this->manager->contacts->getByEmptyCoords(1000);

		foreach($contacts as $row){
			$row = $this->getCoord($row);
			
			if(!empty($row['lat'])){
				$clog =  new clog(array('date_log' => date("Y-m-d H:i:s"), 'contact_id' => $row['id'], 'user_id' => $_SESSION['utilisateur']['id'], 'log' => 'Geoloc - Recuperation des coordonnées GPS'));
				$clog->save();				
				$result_ok++;
			}else{
				$clog =  new clog(array('date_log' => date("Y-m-d H:i:s"), 'contact_id' => $row['id'], 'user_id' => $_SESSION['utilisateur']['id'], 'log' => 'Geoloc - Echec de la recuperation des coordonnées GPS'));
				$clog->save();
				$result_fail++;
			}
			
			// Sauvegarde du lasr geoloc
			
			
			$this->registry->db->update('contacts', $row);
		}

		$this->registry->smarty->assign(array(
			'tot_contacts'		=>	$result_ok + $result_fail,
			'result_fail'		=>	$result_fail,
			'result_ok'			=>	$result_ok,
		));

		return $this->registry->smarty->fetch(VIEW_PATH.'contacts'.DS.'geolocemptycoordajax.tpl');	
	}
	
	/**
	*	Permet de fusionner deux contacts
	*
	*/
	public function fusion_contactAction(){
	
		$contact_1 = $this->registry->Http->get('c1');
		$contact_2 = $this->registry->Http->get('c2');
		
		$c1 = new contacts();
		$c2 = new contacts();
		
		$c1->get($contact_1);
		$c2->get($contact_2);
		
		$c1->update_fields($c2);
		var_dump($c1);
		
		$s1 = new societe($this->registry->db->get_one('societe',array('contact_id =' => $c1->id)));
		$s2 = new societe($this->registry->db->get_one('societe',array('contact_id =' => $c2->id)));
		
		$s1->update_fields($s2);
		
		var_dump($s1);
		
		// Migration de tous les anciens contacts
		$this->registry->db->update('personne', array('societe_id' => $s1->id), array('societe_id =' => $s2->id) );
		$this->registry->db->update('telephones', array('contact_id' => $c1->id), array('contact_id =' => $c2->id) );
		
		// Suppression du doublons.
		$c2->delete($c2->id);
		$s2->delete($s2->id);
		$this->registry->db->delete('contacts_log', null, array('contact_id =' => $c2->id));
		$this->registry->db->delete('contacts_mailing', null, array('contact_id =' => $c2->id));
		$this->registry->db->delete('contacts_suivi', null, array('cid =' => $c2->id));
		$this->registry->db->delete('contacts_files', null, array('contact_id =' => $c2->id));
		$this->registry->db->delete('contacts_email', null, array('entreprise_id =' => $c2->id));
		
		// Sauvegarde des nouveaux object
		$c1->save();
		$s1->save();
		
		return $this->detailAction($c1->id);
	}

	private function getCoord($entreprise){
		require_once ROOT_PATH . 'kernel' . DS . 'lib' . DS . 'GoogleMapAPIv3.class.php';
		$gmap = new GoogleMapApi();

		$coord = $gmap->geocoding($entreprise['adresse1'] .' '. $entreprise['code_postal'] . ' '. $entreprise['ville'] . ' FRANCE');

		if(is_numeric($coord[2])){
			// Geoloc OK
			return array_merge($entreprise, array(
				'lat'				=>	$coord[2],
				'lng' 				=> 	$coord[3],
				'date_last_geoloc'	=>	date("Y-m-d H:i:s"),
			));
		}else{
			return array_merge($entreprise, array(
				'date_last_geoloc'	=>	date("Y-m-d H:i:s"),
			));
		}
	}

	public function suiviaddAction($cid){

		if(!is_null($this->registry->Http->post('suivi'))){

			$suivi = new contacts_suivi($this->registry->Http->post('suivi'));
			$suivi->cid = $cid;
			$suivi->uid = $_SESSION['utilisateur']['id'];
			$suivi->date_suivi = TimeToDATETIME();
			$suivi->save();

			$clog =  new clog(array('date_log' => date("Y-m-d H:i:s"), 'contact_id' => $suivi->cid, 'user_id' => $suivi->uid, 'log' => 'Suivi - Nouveau suivi ajoute'));
			$clog->save();

			$this->registry->smarty->assign('FlashMessage','Suivi enregistré');
		}

		return $this->detailAction($cid);
	}

	public function suivideleteAction($sid){
		
		$suivi = new contacts_suivi();
		$suivi->get($sid);

		$suivi->delete($sid);

		$clog =  new clog(array('date_log' => date("Y-m-d H:i:s"), 'contact_id' => $suivi->cid, 'user_id' => $suivi->uid, 'log' => 'Suivi - suppression du suivi #'. $suivi->id));
		$clog->save();

		$this->registry->smarty->assign('FlashMessage','Suivi supprimé');

		return $this->detailAction($suivi->cid);
	}
	
	public function file_addAction($cid){
		
		$path = ROOT_PATH . 'web' . DS . 'upload' . DS . 'contacts' . DS . $cid . DS;
		
		if( !is_dir($path) ){
			@mkdir($path);
		}
		
		// Recuperation lib
		require_once ROOT_PATH . 'kernel' . DS . 'lib' . DS . 'upload' . DS . 'class.upload.php';
		
		// Nouvelle instance
		$upload = new Upload($_FILES['file_contact']);
		
		$new_file_name = time();
		
		// Traitement de l'upload avec renommage
		if($upload->uploaded){
			$upload->file_overwrite = true;
			$upload->file_new_name_body   = time();
			$upload->process($path);
		};

		// Creation du tableau avec les donnees pour
		// enregistrement dans la base
		$file = array(
			'name'			=>	$upload->file_src_name ,
			'description'	=>	'',
			'disk_name'		=>	$upload->file_dst_name,
			'contact_id'	=>	$cid,
			'user_id'		=>	$_SESSION['utilisateur']['id'],
			'date_add'		=>	date("Y-m-d H:i:s"),
			'downloaded'	=>	0,
		);
		var_dump($file);
		$fid = $this->registry->db->insert('contacts_files', $file);
		
		$clog =  new clog(array('date_log' => date("Y-m-d H:i:s"), 'contact_id' => $cid, 'user_id' => $_SESSION['utilisateur']['id'], 'log' => 'Fichier - ajout du fichier '. $upload->file_src_name .' #'. $fid));
		$clog->save();
		
		return "<script>window.top.window.endUpload('success');</script>";
	}
	
	/**
	*	Gere la suppression du fichier de la base et du disque
	*	@param int $fid id du fichier dans la base
	*	@return mixed code html
	*/
	public function file_deleteAction($fid){
	
		$file = $this->registry->db->get_one('contacts_files',array('id =' => $fid));
		
		$path = ROOT_PATH . 'web' . DS . 'upload' . DS . 'contacts' . DS . $file['contact_id'] . DS;
		@unlink($path . $file['disk_name']);
		
		$this->registry->db->delete('contacts_files', $fid);
		
		$clog =  new clog(array('date_log' => date("Y-m-d H:i:s"), 'contact_id' => $file['contact_id'], 'user_id' => $_SESSION['utilisateur']['id'], 'log' => 'Fichier - suppression du fichier #'. $fid));
		$clog->save();
		
		$this->registry->smarty->assign('FlashMessage', 'Fichier supprimé');
		
		return $this->detailAction($file['contact_id']);
	}

	/**
	 * Recupere la liste des fichier dans la base d'un contact
	 * Si param JSON passé en GET retourne le resultat de la requete dans ce format
	 * @param  [type] $cid [description]
	 * @return [type]      [description]
	 */
	function get_filesAction($cid){

		// Recuperation des fichiers
		$files 	=	$this->registry->db
						->select('cf.*, u.identifiant as identifiant')
						->from('contacts_files cf')
						->left_join('user u','cf.user_id = u.id')
						->where(array('cf.contact_id =' => $cid))
						->get();
						
		// Si apl en JSON
		if(!is_null($this->registry->Http->get('json'))){
			$i=0;
			foreach($files as $file){
				$files[$i]['url_download'] = $this->registry->config['url'] . 'web/upload/contacts/'. $cid . '/'. $file['disk_name'];
				$i++;
			}

			return json_encode($files);
		}
	}

	public function ajaxmaintenanceoptdbAction(){		

		return "go";
	}

	public function ajaxoptcontactsAction(){
		// On supprime la limite de temps
		set_time_limit(0);

		// Recuperations des contacts
		$contacts = $this->registry->db->get('contacts');

		foreach($contacts as $row){
			$contact = new contacts($row);
			$contact->adresse1 = trim($contact->adresse1);
			$contact->adresse2 = trim($contact->adresse2);
			$contact->code_postal = trim($contact->code_postal);
			$contact->ville = trim($contact->ville);
			$contact->pays = trim($contact->pays);
			$contact->email = trim($contact->email);
			$contact->code_interne = trim($contact->code_interne);
			$contact->save();
		}

		return "contacts_ok";
	}

	public function ajaxoptsocietesAction(){
		// On supprime la limite de temps
		set_time_limit(0);

		// Traitement des societes
		$societes = $this->registry->db->get('societe');

		foreach ($societes as $row) {
			$societe = new societe($row);
			$societe->raison_social = trim($societe->raison_social);
			$societe->siret = trim($societe->siret);
			$societe->save();
		}

		return "societes_ok";
	}

	public function ajaxoptpersonnesAction(){
		// On supprime la limite de temps
		set_time_limit(0);

		// Traitement des personnes
		$personnes = $this->registry->db->get('personne');

		foreach($personnes as $row){
			$personne = new personne($row);
			$personne->nom = trim($personne->nom);
			$personne->prenom = trim($personne->prenom);
			$personne->save();
		}

		return "personnes_ok";
	}

	public function ajaxcheckemailcontactsAction(){

		$result = array();

		// Recuperation des contacts
		$contacts = $this->registry->db->get('contacts', array('email <>' => ''));

		foreach($contacts as $row){
			if( VerifieAdresseMail($row['email']) == false ){
				$result[] = $row;
			}
		}

		return json_encode($result);

	}

	public function AjaxAddPhoneAction($cid){

		if( !is_null($this->registry->Http->post('phone')) ){
			$phone = new telephone($this->registry->Http->post('phone'));
			$phone->clearnumber();
			$phone->save();

			$this->registry->smarty->assign('FlashMessage','Téléphone enregistré');

			return $this->detailAction($phone->contact_id);
		}

		showform:
		$this->registry->smarty->assign('cid',$cid);
		return $this->registry->smarty->fetch(VIEW_PATH.'contacts'.DS.'ajax-form-phone.shark');

	}

	public function ajax_search_societeAction(){
		$search = $this->registry->HTTPRequest->getData('term');
		$results = $this->registry->db->select('s.raison_social as label, s.contact_id as value')->from('societe s')->where_free('s.raison_social LIKE "%'. $search .'%"')->limit(10)->get();
		return json_encode($results);
	}

	/**
	 * Recherche dans la base par rapport a la var GET term passe en parametre et retourne le resultat
	 * en JSON pour etre traite en JS
	 * @return json ARRAY du matching
	 */
	public function ajax_search_globalAction(){
		$search = $this->registry->HTTPRequest->getData('term');
		$results =	$this->registry->db->select('concat_ws(" ",s.raison_social, p.prenom, p.nom)as label, c.id as value')
					->from('contacts c')
					->left_join('societe s', 's.contact_id = c.id')
					->left_join('personne p', 'p.contact_id = c.id')
					->where_free('s.raison_social LIKE "%'. $search .'%" OR p.nom LIKE "%'.$search.'%" OR c.email LIKE "%'.$search.'%"')
					->limit(10)
					->get();
		return json_encode($results);
	}

	/**
	 * Retourne le formulaire pour ajouter un agence
	 * @param  [type] $mother_id [description]
	 * @return [type]            [description]
	 */
	public function ajax_form_add_agenceAction($mother_id){
		$this->registry->smarty->assign('contact_id', $this->registry->Http->get('contact'));
		$this->registry->smarty->assign('mother_id', $mother_id);
		return $this->registry->smarty->fetch(VIEW_PATH . 'contacts' . DS . 'ajax-form-agence-add.meg');
	}

	/**
	 * Retourne le formulaire pour ajouter un fichier
	 * depuis la fiche du client
	 * @param  [type] $cid [description]
	 * @return [type]      [description]
	 */
	public function ajax_form_add_fileAction($cid){
		$this->registry->smarty->assign('cid', $cid);
		return $this->registry->smarty->fetch(VIEW_PATH . 'contacts' . DS . 'ajax-form-add-file.meg');
	}

	public function phone_deleteAction($pid){

		$phone =  new telephone();
		$phone->get($pid);
		$phone->delete($phone->id);

		$clog =  new clog(array('date_log' => date("Y-m-d H:i:s"), 'contact_id' => $phone->contact_id, 'user_id' => $_SESSION['utilisateur']['id'], 'log' => 'Telephone - suppression du telephone #'. $phone->id . ' - '. $phone->telephone));
		$clog->save();

		$this->registry->Helper->pnotify('Téléphone', 'Téléphone retiré du contact');		

		return $this->detailAction($phone->contact_id);
	}
	
	/**
	*	Ajout une agence à un siege social
	*
	*/
	public function agence_addAction(){
		$agence_id = $this->registry->Http->get('agence_id');
		$mother = $this->registry->Http->get('mother');
		$contact = $this->registry->Http->get('contact');
		$this->registry->db->update('societe', array('parent_id' => $mother), array('id =' => $agence_id));
		
		return $this->detailAction($contact);
	}
	
	/**
	*	Supprime une agence d un siege social
	*
	*/
	public function agence_removeAction(){
		$agence_id = $this->registry->Http->get('agence_id');
		$mother = $this->registry->Http->get('mother');
		$this->registry->db->update('societe', array('parent_id' => '0'), array('id =' => $agence_id));
			
		return $this->detailAction($mother);
	}


	public function ajax_geoloc_contactAction($contact_id){
		$contact = new contacts();
		$contact->get($contact_id);

		if(empty($contact))
			return 'Error return data of contact';

		if(!empty($contact->date_last_geoloc) && (date("Y-m-d H:i:s") - $contact->date_last_geoloc) > 15 ){
			require_once ROOT_PATH . 'kernel' . DS . 'lib' . DS . 'GoogleMapAPIv3.class.php';
			$gmap = new GoogleMapApi();
			
			$coord = $gmap->geocoding($contact->adresse1 .' '. $contact->code_postal . ' '. $contact->ville);

			if(is_numeric($coord[2])){
				$contact->lat = $coord[2];
				$contact->lng = $coord[3];
				$contact->date_last_geoloc = date("Y-m-d H:i:s");
				$contact->save();

				$clog =  new clog(array('date_log' => date("Y-m-d H:i:s"), 'contact_id' => $contact_id, 'user_id' => $_SESSION['utilisateur']['id'], 'log' => 'Geoloc - Recuperation des coordonnées GPS'));
				$clog->save();

				return 'Geoloc ok';
			}else{
				return 'Error geoloc : ' . print_r($coord, true);
			}
		}
			
		return 'Error : Contact geoloc in short time';	
	}

	public function ajax_form_suiviAction($contact_id){

		$this->registry->smarty->assign('contact_id', $contact_id);

		return $this->registry->smarty->fetch(VIEW_PATH . 'contacts' . DS . 'ajax-form-suivi.shark');
	}

	public function ajax_form_sendemailAction($contact_id){
		$contact = new contacts();
		$contact->get($contact_id);

		$this->registry->smarty->assign('contact', $contact);
		$this->registry->smarty->assign('contact_id', $contact_id);

		return $this->registry->smarty->fetch(VIEW_PATH . 'contacts' . DS . 'ajax-form-send-email.shark');
	}

	/**
	 * [get_contacts_societeAction description]
	 * @param  [type] $contact_id [description]
	 * @return [type]             [description]
	 */
	public function get_contacts_societeAction($contact_id){

		$this->load_manager('contacts');
		$contacts = $this->manager->contacts->getContactsOfSociete($contact_id);

		return json_encode($contacts);
	}

	/**
	 * [get_logs_of_contactsAction description]
	 * @param  [type] $contact_id [description]
	 * @return [type]             [description]
	 */
	public function get_logs_of_contactAction($contact_id){
		$this->load_manager('contacts');
		$logs = $this->manager->contacts->getLogs($contact_id);

		return json_encode($logs);
	}

	/**
	 * [get_mailings_of_contactAction description]
	 * @param  [type] $contact_id [description]
	 * @return [type]             [description]
	 */
	public function get_mailings_of_contactAction($contact_id){
		$this->load_manager('mailing');
		$mailings = $this->manager->mailing->getByContactId($contact_id);
		$i=0;
		
		foreach ($mailings as $row) {
			if($row['open'] == 1)
				$mailings[$i]['open'] = '<span class="label label-success">Oui</span>';
			else
				$mailings[$i]['open'] = '<span class="label label-default">Non</span>';
			
			$i++;
		}

		return json_encode($mailings);
	}
}