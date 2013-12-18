<?php


class mailingController extends Controller{
	
	/**
	 * Surchage de la fonction pour verification ACL
	 * @param [type] $registry [description]
	 */
	public function __construct($registry){
		parent::__construct($registry);

		if( isAdmin() < 1 && !getAcl('mailing_access') ){
			header('HTTP/1.0 401 Unauthorized');
			header('Location: '. $this->registry->Helper->getLink("index"));			
		}
	}

	/**
	*	Affiche la liste des mailings
	*	@return string code html de la page
	*/
	public function indexAction(){

		$per_page = 30;
		$this->load_manager('mailing');	

		$nb = $this->manager->mailing->count();
		$mailings = $this->manager->mailing->get($per_page, getOffset($per_page));	

		$Pagination = new Zebra_Pagination();
		$Pagination->records($nb);
		$Pagination->records_per_page($per_page);
		$this->registry->smarty->assign('Pagination',$Pagination);	

		$this->registry->smarty->assign('mailings',	$mailings);

		return $this->registry->smarty->fetch(VIEW_PATH . 'mailing' . DS . 'index.tpl');
	}
	
	/**
	 * Retour le calendrier des mailings
	 * @return [type] [description]
	 */
	public function calendrierAction(){
		// Lib pour affichage calendrier
		$this->registry->load_web_lib('fullcalendar/fullcalendar.css','css');
        $this->registry->load_web_lib('fullcalendar/fullcalendar.min.js','js');
		return $this->registry->smarty->fetch(VIEW_PATH . 'mailing' . DS . 'calendar.tpl');
	}
	
	/**
	 * Recupere au format json les infos des mailigns
	 * @return [type] [description]
	 */
	public function getmailingforcalendarAction(){
		$mailings = array();
		
		$this->load_manager('mailing');

		$Datas = $this->registry->db->get('mailings', array('valid != ' => 2));
		
		foreach($Datas as $Data){
        	$mailings[] = array(
				'id' 			=> 	$Data['id'], 
				'title' 		=> 	$Data['libelle'], 
				'start' 		=> 	$Data['date_wish'],
				'url' 			=> 	$this->registry->Helper->getLink('mailing/fiche/'. $Data['id']),
				'backgroundColor'	=>	$Data['valid'] == 0 ? '#999999' : '#5cb85c',
			);
        }
		
		return json_encode($mailings);
	}
	
	/**
	*	Affiche les details du mailing
	*	@param int $id identifiant du mailing
	*	@return string code html de la page
	*/
	public function ficheAction($id){
		$this->load_manager('mailing');
		$mailing = new mailing($this->manager->mailing->getById($id));
		$mailing->cible = unserialize($mailing->cible);
		
		if(!empty($mailing->date_send))
			$this->registry->smarty->assign('nb_destinataires', $this->registry->db->count('contacts_mailing', array('mailing_id =' => $id)));			

		if( isset($mailing->cible['ctype']) ){
			foreach($mailing->cible['ctype'] as $k => $v){
				$mailing->cible[$v] = 1;
			}
			unset($mailing->cible['ctype']);
		}
		
		$link_csv = $this->getlinktocvs($mailing->cible);
		$link_view = str_replace('csv','',$link_csv);
		
		$this->registry->smarty->assign(array(
			'mailing'	=>	$mailing,
			'link_csv'	=>	$link_csv,
			'link_view'	=>	$link_view,
		));
		
		return $this->registry->smarty->fetch(VIEW_PATH . 'mailing' . DS . 'fiche.tpl');
	}
	
	/**
	*	Affiche et traite le formulaire de requete de mailing
	*	@return string code html de la page
	*/
	public function addAction(){

		if( isAdmin() < 1 && !getAcl('mailing_demand') ){
			header('HTTP/1.0 401 Unauthorized');
			header('Location: '. $this->registry->Helper->getLink("mailing"));
		}
	
		if(!is_null($this->registry->Http->post('mailing'))){
		
			// On traite le formulaire
			$mailing = new mailing($this->registry->Http->post('mailing'));
			
			$mailing->cible = serialize($mailing->cible);
			$mailing->demand_by = $_SESSION['utilisateur']['id'];
			$mailing->demand_on = date("Y-m-d H:i:s");
			$mailing->send = 0;
			$mailing->valid = 0;
			$mailing->date_wish = FormatDateToMySql($mailing->date_wish);
			
			$mid = $mailing->save();
			
			$this->registry->Helper->pnotify('Mailing','Votre demande a été enregistrée.');
			
			// Récuperation des gestionnaires de mailings pour notifications
			$users = $this->registry->db->get('acl', array('acl =' => 'mailing_valid'));

			// Notification
			$notification = array(
				'sender_id'			=>	0,
				'user_id'			=>	0,
				'is_read'			=>	0,
				'is_delete'			=>	0,
				'date_notification'	=>	date("Y-m-d H:i:s"),
				'message'			=>	'<a href="'.$this->registry->Helper->getLink('mailing/fiche/'. $mid).'" title=""><i class="fa fa-envelope"></i>&nbsp;Une nouvelle demande de mailing vient d\'être effectuée.</a>',
				'third_type'		=>	'mailings',
				'third_id'			=>	$mid
			);

			// Envoie des notifications
			foreach ($users as $user) {
				$notification['user_id'] = $user['user_id'];
				$this->registry->db->insert('notifications', $notification);
			}

			// Affichage de la page
			return $this->indexAction();
		}
		$types = new mailing_type();
		$this->getFormValidatorJs();
		$this->registry->smarty->assign('types', $types->get());
		return $this->registry->smarty->fetch(VIEW_PATH . 'mailing' . DS . 'add.tpl');
	}
	
	public function editAction($id){		
		
		if( isAdmin() < 1 && !getAcl('mailing_valid') ){
			header('HTTP/1.0 401 Unauthorized');
			header('Location: '. $this->registry->Helper->getLink("mailing"));
		}

		if(!is_null($this->registry->Http->post('mailing'))){
		
			//Traitement du formulaire
			$mailing = new mailing($this->registry->Http->post('mailing'));
			
			$mailing->cible = serialize($mailing->cible);
			$mailing->date_wish = FormatDateToMySql($mailing->date_wish);
			
			$mailing->save();
			
			$this->registry->smarty->assign('FlashMessage','Mailing enregistré');
			
			return $this->ficheAction($id);
		}
		
		$mailing = new mailing();
		$mailing->get($id);
		
		$mailing->date_wish = DateMysqlToFR($mailing->date_wish);
		$mailing->cible = unserialize($mailing->cible);

		if( isset($mailing->cible['ctype']) ){
			foreach($mailing->cible['ctype'] as $k => $v){
				$mailing->cible[$v] = 1;
			}
		}
		
		$types = new mailing_type();
		$this->getFormValidatorJs();

		$this->registry->smarty->assign('types', $types->get());
		$this->registry->smarty->assign('mailing', $mailing);
		
		return $this->registry->smarty->fetch(VIEW_PATH . 'mailing' . DS . 'edit.shark');
	}
	
	/**
	*	Traite la suppression d'un mailing dans la base en verifiant les droits utilisateur
	*	@param int $id identifiant du mailing dans la base
	*	@return string code html de la page index
	*/
	public function deleteAction($id){
	
		if( isAdmin() < 1 && !getAcl('mailing_valid') ){
			header('HTTP/1.0 401 Unauthorized');
			header('Location: '. $this->registry->Helper->getLink("mailing"));
		}
		
		// suppression des lignes dans la bases
		$this->registry->db->delete('mailings',$id);
		$this->registry->db->delete('contacts_mailing', null, array('mailing_id =' => $id));
		
		// Notification a l utilisateur
		$this->registry->smarty->assign('FlashMessage','Mailing supprimé');
		
		// Affichage de la page
		return $this->indexAction();
	}
	
	public function marksendAction($id){

		if( isAdmin() < 1 && !getAcl('mailing_valid') ){
			header('HTTP/1.0 401 Unauthorized');
			header('Location: '. $this->registry->Helper->getLink("mailing"));
		}

		$mailing = new mailing();
		$mailing->get($id);

		if($mailing->send == 1){
			$this->registry->smarty->assign('FlashMessage','Mailing deja marqué comme envoyé');
			return $this->ficheAction($id);
		}
		
		$ccontacts = $this->load_controller('contacts');
		$cible = unserialize($mailing->cible);

		if( isset($cible['ctype']) ){
			foreach($cible['ctype'] as $k => $v){
				$cible[$v] = 1;
			}
			unset($cible['ctype']);
		}

		$where = $ccontacts->getWhere($cible);

		$this->load_manager('contacts');

		$contacts = $this->manager->contacts->get($where);

		foreach($contacts as $row){
			$contacts_mailing = array(
				'mailing_id'	=>	$id,
				'contact_id'	=>	$row['id'],
				'result'		=>	'',
			);

			$this->registry->db->insert('contacts_mailing', $contacts_mailing);

			$clog =  new clog(array('date_log' => date("Y-m-d H:i:s"), 'contact_id' => $row['id'], 'user_id' => $_SESSION['utilisateur']['id'], 'log' => 'Envoie mailing <a href="'.$this->registry->Helper->getLink('mailing/fiche/'.$id).'" title="">#'.$id.'</a>'));
			$clog->save();
		}

		$mailing->send = 1;
		$mailing->date_send = date("Y-m-d");
		$mailing->save();
		
		$this->registry->smarty->assign('FlashMessage','Mailing marque comme envoye');

		return $this->ficheAction($id);
	}
	
	/**
	*	Traite la validation d un mailing
	*	@param int $id identifiant du mailing
	*	@return string code html de la page
	*/
	public function validedAction($id){
		
		if( isAdmin() < 1 && !getAcl('mailing_valid') ){
			header('HTTP/1.0 401 Unauthorized');
			header('Location: '. $this->registry->Helper->getLink("mailing"));
		}
		
		// Sauvegarde du mailing
		$mailing = new mailing();
		$mailing->get($id);
		$mailing->valid = 1;
		$mailing->valid_on = date("Y-m-d H:i:s");
		$mailing->valid_by = $_SESSION['utilisateur']['id'];
		$mailing->save();

		// Recuperation des utilisateurs validateur
		$users = $this->registry->db->get('acl', array('acl =' => 'mailing_valid'));

		// Ajout tache au mailing_valid
		$task = array(
			'creat_by'	=>	0,
			'user_id'	=>	'',
			'third_type'	=>	'mailings',
			'third_id'		=>	$id,
			'date_add'		=>	date("Y-m-d"),
			'date_expire'	=>	$mailing->date_wish,
			'priority'		=>	3,
			'process'		=>	0,
			'task'			=>	'Envoyé le mailing '. $mailing->libelle .' (#'.$id.')',
			'link'			=>	$this->registry->Helper->getLink('mailing/fiche/'. $id),
			'guid'			=>	uniqid(),
		);

		foreach($users as $user){
			$task['user_id'] = $user['user_id'];
			$this->registry->db->insert('tasks', $task);
		}
		
		// Ajout notification au demandeur	
		$notification = array(
			'sender_id'			=>	0,
			'user_id'			=>	$mailing->demand_by,
			'is_read'			=>	0,
			'is_delete'			=>	0,
			'date_notification'	=>	date("Y-m-d H:i:s"),
			'message'			=>	'Votre demande de <a href="'.$this->registry->Helper->getLink('mailing/fiche/'. $id).'" title="">mailing</a> a ete validé',
			'third_type'		=>	'mailings',
			'third_id'			=>	$id,
		);	

		$this->registry->db->insert('notifications', $notification);
		
		$this->registry->Helper->pnotify('Mailing', 'Mailing validé');

		return $this->ficheAction($id);		
	}
	
	/**
	*	Traite le refus d un mailing
	*	@param int $id identifiant du mailing
	*	@return string code html de la page
	*/
	public function refusedAction($id){

		if( isAdmin() < 1 && !getAcl('mailing_valid') ){
			header('HTTP/1.0 401 Unauthorized');
			header('Location: '. $this->registry->Helper->getLink("mailing"));
		}
		
		// Gestion du clic sur le bouton Annuler
		if( $this->registry->Http->get('raison') == 'null' ){
			return $this->ficheAction($id);
		}
		
		// Enregistrement du refus
		$mailing = new mailing();
		$mailing->get($id);
		$mailing->valid = 2;
		$mailing->valid_on = date("Y-m-d H:i:s");
		$mailing->valid_by = $_SESSION['utilisateur']['id'];
		$mailing->refus = $this->registry->Http->get('raison');
		$mailing->save();		
		
		$this->registry->smarty->assign('FlashMessage','Mailing refuse');
		return $this->ficheAction($id);		
	}
	
	/**
	*	Retourne et affiche les champs contacts pour le formulaire
	*	de requete mailing
	*	@return string code html a affiche
	*/
	public function getchampscontactsajaxAction(){
		return $this->registry->smarty->fetch(VIEW_PATH . 'mailing' . DS . 'getchampscontactsajax.tpl');
	}
	
	/**
	*	Verifie la valide de la date et le nombre de mailing le jour souhaite
	*	@return string code resultat
	*/
	public function checkdatewishAction(){
		$date = FormatDateToMySql($_GET['mailing']['date_wish']);
		
		$result = $this->registry->db->count('mailings', array('date_wish =' => $date, 'valid =' => 1));
		
		if($result < $this->registry->config['mailing_max_day']){
			return "true";
		}else{
			return "false";
		}
	}

	/**
	 * Recupere le nombre de contact correspondant a la cible
	 * @param  [type] $mailing_id [description]
	 * @return [type]             [description]
	 */
	public function ajax_get_nbcontactsAction($mailing_id){

		$mailing = new mailing();
		$mailing->get($mailing_id);

		$mailing->cible = unserialize($mailing->cible);

		if( isset($mailing->cible['ctype']) ){
			foreach($mailing->cible['ctype'] as $k => $v){
				$mailing->cible[$v] = 1;
			}
		}

		$ccontacts = $this->load_controller('contacts');
		$where = $ccontacts->getWhere($mailing->cible);
		$this->load_manager('contacts');
			
		return $this->manager->contacts->count($where);
	}

	public function import_statsAction($mailing_id){

		$this->registry->smarty->assign('mailing_id', $mailing_id);

		if(!is_null($this->registry->Http->post('fs'))){
			// Traitement du fichier
			$dir = ROOT_PATH . 'web' . DS . 'upload' . DS . 'csv' . DS;
		
			require_once ROOT_PATH . 'kernel' . DS . 'lib' . DS . 'upload' . DS . 'class.upload.php';
			
			if(!is_dir($dir))
				@mkdir($dir);			
			
	        $fichier = new Upload($_FILES['file_stats']);
	        $name = uniqid();

	        if($fichier->uploaded){
	            $fichier->file_overwrite 		= true;
	            $fichier->file_new_name_body  	= $name;
				$fichier->file_new_name_ext		= 'csv';
	            $fichier->process($dir);

	            // On traite le fichier
	            $lines = file(ROOT_PATH . 'web' . DS . 'upload' . DS . 'csv' . DS . $name .'.csv');

	            // On verifie que le fichier comporte des lignes
	            if(count($lines) == 1){
	            	$this->registry->smarty->assign('FlashMessage', 'Le fichier envoye ne contient aucune ligne');
					goto printform;
				}

				$this->load_manager('contacts');
				$contacts = array();

				$i = 0;
				$stats_file = array(
					'not_in_db'		=>	0,
					'open'			=>	0,
					'not_open'		=>	0,
					'db_contact'	=>	$this->registry->db->count('contacts_mailing', array('mailing_id =' => $mailing_id)),
					'file_contact'	=>	count($lines),
				);
				
				foreach($lines as $row){
					// On traite pas la 1er ligne car entete
					if($i == 0)
						goto nextboucle;

					$data = str_getcsv($row,';');

					// Recherche du contact dans la base
					$result = $this->registry->db->get_one('contacts', array('email =' => $data[0]));
					
					if(!$result){
						$stats_file['not_in_db']++;
						goto nextboucle;
					}
						
					$result_mailing = array(
						'mailing_id'		=>	$mailing_id,
						'contact_id'		=>	$result['id'],
						'open'				=>	0,
						'date_open'			=>	NULL,
						'in_stat'			=>	1,
					);
					
					if($data['1'] == 'oui' || $data['1'] == 'o' || $data['1'] == 'yes' || $data['1'] == 'y'){
						$result_mailing['open'] = 1; 
						$stats_file['open']++;
					}else{
						$stats_file['not_open']++;
					}

					//$this->registry->db->update('contacts_mailing', $result_mailing, array('contact_id' => $result['id'], 'mailing_id =' => $mailing_id));

					nextboucle:
					$i++;
				}
			
				$this->registry->smarty->assign('stats', $stats_file);
			}
		}

		printform:
		$this->registry->smarty->assign('savoir_inutile', getSavoirInutile());
		return $this->registry->smarty->fetch(VIEW_PATH . 'mailing' . DS . 'import_stats.shark');
	}

	public function get_contacts_not_in_statsAction($mailing_id){
		$contacts =  $this->registry->db->select(' DISTINCT(c.id), concat_ws("",s.raison_social, p.nom) as nom, p.prenom, c.email')
						->from('contacts_mailing cm')
						->left_join('contacts c','c.id = cm.contact_id')
						->left_join('personne p','c.id = p.contact_id')
						->left_join('societe s','c.id = s.contact_id')						
						->where(array('cm.in_stat !=' => 1, 'cm.mailing_id =' => $mailing_id))
						->limit(50)
						->offset(getOffset(50))
						->get();

		return json_encode($contacts);
	}

	public function remove_invalid_emails($mailing_id){
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
		return '';
	}

	public function invalid_emailsAction($mailing_id){

	}
	
	/**
	*	Contruit le lien vers le fichier CSV pour exporter les contacts
	*	@param array $cible contient les parametres de recherche
	*	@return string $url contenant le lien vers la page de generationde CSV
	*/
	private function getlinktocvs($cible){
		$url = $this->registry->Helper->getLink("contacts/csv?export=1");
		
		foreach($cible as $key => $value){	
			if( is_array($value) ){				
				foreach($value as $k => $v){
					$url .= '&filtre['. $key .'][]='. $v;
				}				
			}else{
				$url .= '&filtre['. $key .']=' . $value;
			}
		}
		
		$url .= '&filtre[email_is_valid]=1';
		
		return $url;
	}
}