<?php

class campaignController extends Controller{

	/**
	 * Surchage de la fonction pour verification ACL
	 * @param [type] $registry [description]
	 */
	public function __construct($registry){
		parent::__construct($registry);

		if( isAdmin() < 1 && !getAcl('campaign_access') ){
			header('HTTP/1.0 401 Unauthorized');
			header('Location: '. $this->registry->Helper->getLink("index"));			
		}
	}
	
	public function indexAction(){
		$this->load_manager('campaign');
		
		$campaigns = $this->manager->campaign->get();
		
		$this->registry->smarty->assign('campaigns', $campaigns);
		
		return $this->registry->smarty->fetch(VIEW_PATH . 'campaign' . DS . 'index.shark');
	}
	
	public function viewAction($id){

		$this->load_manager('campaign');
		
		$campaign = $this->manager->campaign->getById($id);
		
		$this->registry->smarty->assign('campaign', $campaign);

		$this->registry->load_web_lib('moment-2.4.0.js','js','footer');
		$this->registry->load_web_lib('bt3_datapicker/css/bootstrap-datetimepicker.min.css','css');
		$this->registry->load_web_lib('bt3_datapicker/js/bootstrap-datetimepicker.min.js','js','footer');
		
		return $this->registry->smarty->fetch(VIEW_PATH . 'campaign' . DS . 'view.shark');

	}

	public function addAction(){
		
		if(!is_null($this->registry->Http->post('campaign'))){
			$data = $this->registry->Http->post('campaign');
			$campaign = new campaign($data);
			$result = $campaign->isValid();
			
			if($result !== true ){
				$this->registry->smarty->assign('Errors', $result);
				goto printform;
			}
			
			$campaign->target = serialize($campaign->target);
			
			$campaign->save();
			
			$this->registry->smarty->assign('FlashMessage','Campagne enregistrée');
			
			return $this->indexAction();
		}
		
		printform:
		$this->getFormValidatorJs();
		$this->registry->smarty->assign('users', $this->registry->db->get('user'));
		$this->registry->smarty->assign('campaign_type', $this->registry->db->get('campaign_type'));
		
		return $this->registry->smarty->fetch(VIEW_PATH . 'campaign' . DS . 'add.shark');
	
	}

	public function generat_cibleAction($id){

		$campaign = new campaign();
		$campaign->get($id);
		$tel_valid = 0;
		$cpt = 0;

		$campaign->target = unserialize($campaign->target);

		foreach($campaign->target['ctype'] as $k => $v){
			$campaign->target[$v] = 1;
		}

		if(isset($campaign->target['tel_valid'])){
			$tel_valid = 1;
		}

		unset($campaign->target['ctype']);
		
		$ccontacts = $this->load_controller('contacts');
		$where = $ccontacts->getWhere($campaign->target);
		
		$this->load_manager('contacts');

		$contacts = $this->manager->contacts->get($where);

		foreach($contacts as $row){

			if($tel_valid == 1){
				$result = $this->registry->db->count('telephones', array('contact_id =' => $row['id']));
				if($result == 0){
					goto nextboucle;
				}
			}

			$campaign_contacts = array('campaign_id'=>$id, 'contact_id'=>$row['id'], 'statut'=>0);
			$this->registry->db->insert('campaign_contacts', $campaign_contacts);
			$cpt++;

			$clog =  new clog(array('date_log' => date("Y-m-d H:i:s"), 'contact_id' => $row['id'], 'user_id' => $_SESSION['utilisateur']['id'], 'log' => 'Enregistrement dans la campagne #'. $id));
			$clog->save();

			nextboucle:
		}

		$this->registry->db->update('campaign', array('id' => $id, 'generated' => 1));

		return 'Campagne genere. Au total : '. $cpt;
	}

	public function deleteAction($id){

		if( isAdmin() < 1){
			return $this->indexAction();
		}

		$this->registry->db->delete('campaign', $id);
		$this->registry->db->delete('campaign_contacts', null, array('campaign_id =' => $id));
		$this->registry->db->delete('campaign_contacts_suivi', null, array('campaign_id =' => $id));
		$this->registry->db->delete('contacts_log', null, array('log =' => 'Enregistrement dans la campagne #'. $id));
		$this->registry->smarty->assign('FlashMessage','Campagne supprimée');

		return $this->indexAction();
	}

	public function suivi_addAction($cc_id){

		// Recuperation des informations du formulaire
		$suivi = $this->registry->Http->post('suivi');
		$cc_data = $this->registry->Http->post('campaign_suivi');

		// Recuperation des données dans la base
		$data = $this->registry->db->get_one('campaign_contacts', array('id =' => $cc_id));

		// Traitement suivi
		if( !empty($suivi['suivi'])){
			
			$suivi['campaign_id'] = $data['campaign_id'];
			$suivi['contact_id'] = $data['contact_id'];
			$suivi['cam_con_id'] = $cc_id;
			$suivi['add_by'] = $_SESSION['utilisateur']['id'];
			$suivi['add_on'] = date("Y-m-d H:i:s");

			$this->registry->db->insert('campaign_contacts_suivi', $suivi);
		}

		// Traitement informations
		$data['statut'] = $cc_data['statut'];
		$this->registry->db->update('campaign_contacts', $data);
		
		if(!is_null($this->registry->Http->post('filter'))){
			$this->registry->smarty->assign('filter',$this->registry->Http->post('filter'));	
		}

		$this->registry->smarty->assign('FlashMessage','Suivi enregistrée');

		return $this->viewAction($data['campaign_id']);
		
	}

	public function ajax_possible_contactsAction($id){

		$campaign = new campaign();
		$campaign->get($id);
		$tel_valid = 0;

		$campaign->target = unserialize($campaign->target);

		foreach($campaign->target['ctype'] as $k => $v){
			$campaign->target[$v] = 1;
		}

		if(isset($campaign->target['tel_valid'])){
			$tel_valid = 1;
		}

		unset($campaign->target['ctype']);
		
		$ccontacts = $this->load_controller('contacts');
		$where = $ccontacts->getWhere($campaign->target);
		$this->load_manager('contacts');

		if( $tel_valid == 0){			
			return $this->manager->contacts->count($where);
		}else{
			$cpt = 0;

			$contacts = $this->manager->contacts->get($where);

			foreach($contacts as $row){	

				$result = $this->registry->db->count('telephones', array('contact_id =' => $row['id']));
				if($result == 0){
					goto nextboucle;
				}

				$cpt++;
				nextboucle:
			}
			return $cpt;
		}
	}

	public function ajax_get_targetsAction($id){

		$filter = 'all';

		if( isset($_GET['filter']) ){
			$filter = $_GET['filter'];
		}

		$this->load_manager('campaign');

		// Recuperation des targets
		$targets = $this->manager->campaign->getTargetByCampaignId($id, $filter);

		// On bloucles dessus pour pour data
		$i=0;
		foreach($targets as $target){

			switch ($target['statut']) {
				case 1:
					$targets[$i]['statut'] = '<span class="label label-info">En attente</span>';
					break;

				case 2:
					$targets[$i]['statut'] = '<span class="label label-success">Succès</span>';
					break;

				case 3:
					$targets[$i]['statut'] = '<span class="label label-danger">Echec</span>';
					break;

				case 4:
					$targets[$i]['statut'] = '<span class="label label-warning">Annuler</span>';
					break;
				
				default:
					$targets[$i]['statut'] = '<span class="label label-default">A traiter</span>';
					break;
			}

			$i++;
		}

		return json_encode($targets);
		//var_dump($targets);

	}

	/**
	 * Retourne la progression de la campagne
	 * @param  [type] $id identifiant de la campagne dans la base
	 * @return [type]     [description]
	 */
	public function ajax_get_progressAction($id){
		$stats = array();

		$stats['total_cible'] = $this->registry->db->count('campaign_contacts', array('campaign_id =' => $id));
		$stats['succes'] = $this->registry->db->count('campaign_contacts', array('campaign_id =' => $id, 'statut =' => 2));
		$stats['echec'] = $this->registry->db->count('campaign_contacts', array('campaign_id =' => $id, 'statut =' => 3));
		$stats['cancel'] = $this->registry->db->count('campaign_contacts', array('campaign_id =' => $id, 'statut =' => 4));
		$stats['a_traiter'] = $this->registry->db->count('campaign_contacts', array('campaign_id =' => $id, 'statut <' => 2));

		$stats['succes_per_cent'] = ($stats['succes'] * 100) / $stats['total_cible'];
		$stats['traiter_per_cent'] = ( ($stats['total_cible']-$stats['a_traiter'])  * 100) / $stats['total_cible'];

		$this->registry->smarty->assign('stats', $stats);

		return $this->registry->smarty->fetch(VIEW_PATH . 'campaign' . DS . 'ajax_progress_campaign.shark');
	}

	/**
	 * [ajax_get_detailAction description]
	 * @param  int $id identifiant compaign_contacts dans la bdd
	 * @return [type]     [description]
	 */
	public function ajax_get_detailAction($id){

		$this->load_manager('campaign_contacts_suivi');
		// Recuperation des infos (campaign_id et contact_id)
		$data = $this->registry->db->get_one('campaign_contacts', array('id =' => $id));
		$suivis = $this->manager->campaign_contacts_suivi->getByCCID($id);
		$campaign = $this->registry->db->get_one('campaign', array('id =' => $data['campaign_id']));

		$this->load_manager('contacts');
		$contact = $this->manager->contacts->getById($data['contact_id']);

		if( !is_null($this->registry->Http->get('filter')) ){
			$this->registry->smarty->assign('filter', $this->registry->Http->get('filter'));
		}

		$this->registry->smarty->assign('contact', $contact);
		$this->registry->smarty->assign('campaign_data', $data);
		$this->registry->smarty->assign('suivis', $suivis);
		$this->registry->smarty->assign('campaign', $campaign);

		return $this->registry->smarty->fetch(VIEW_PATH . 'campaign' . DS . 'ajax_detail_cible.shark');
	}

	/**
	 * Affiche et traite le prise de rendez vous pour le module CAMPAIGN
	 * @return [type] [description]
	 */
	public function take_rdvAction(){
		if($this->registry->modules['rdv']['actif'] != 1){
			exit('This module is unactive, please contact your administrator');
		}

		if(!is_null($this->registry->Http->post('rdv'))){

			$rdv = new rdv($this->registry->Http->post('rdv'));
			$rdv->add_by = $_SESSION['utilisateur']['id'];
			$rdv->add_on = date('Y-m-d H:i:s');
			$rdv->statut = 0;
			$rid = $rdv->save();

			$this->registry->smarty->assign('rdv',$rdv);

			if($rdv->source_type == 'campaign'){
				$cc_data = $this->registry->db->get_one('campaign_contacts', array('campaign_id =' => $rdv->source_id, 'contact_id =' => $rdv->tier_id));

				// Ajout d un suivi a la campagne
				$campaign_suivi = array(
					'campaign_id'	=>	$rdv->source_id,
					'contact_id'	=>	$rdv->tier_id,
					'cam_con_id'	=>	$cc_data['id'],
					'suivi'			=>	'Nouveau rendez vous pris',
					'add_by'		=>	$_SESSION['utilisateur']['id'],
					'add_on'		=>	date('Y-m-d H:i:s'),
				);

				$this->registry->db->insert('campaign_contacts_suivi', $campaign_suivi);

				// Recuperation info tier
				$this->load_manager('contacts');

				$contact = $this->manager->contacts->getById($rdv->tier_id);

				$this->registry->smarty->assign('contact',$contact);

				// Envoie d'un email 
				$user = new utilisateur();
				$user->get($rdv->user_id);
				if(!empty($user->email)){
					$email_corp = $this->registry->smarty->fetch(VIEW_PATH.'rdv'.DS.'email_notification.meg');
					sendEmail($user->email, $this->registry->config['email_sender'], 'Nouveau rendez vous le '. $rdv->date_rdv, strip_tags($email_corp), $email_corp);
				}

				// Ajout des log utilisateur
				$log = new log(array(
					'log' 		=> 	'Prise d un rendez vous dans la campagne #'. $rdv->source_id .' pour l utilisateur : '. $user->identifiant . ' #'. $user->id .' rdv #'. $rid ,
					'module'	=>	'rdv',
					'link_id'	=>	$rid,
				));
				$log->save();
				
				$log = new log(array(
					'log' 		=> 	'Nouveau rendez vous pris  dans la campagne #'. $rdv->source_id .' par l utilisateur : '. $_SESSION['utilisateur']['identifiant'] . ' #'. $_SESSION['utilisateur']['id'] .' rdv #'. $rid ,
					'module'	=>	'rdv',
					'link_id'	=>	$rid,
					'user_id'	=>	$user->id,
				));
				$log->save();

				// PNOTIFY
				$this->registry->Helper->pnotify('Rendez vous', 'Rendez vous ajouté !', 'success');

				if($this->registry->config['campaign_rdv_success'] == 1){
					$this->registry->db->update('campaign_contacts', array('statut' => 2), array('id =' => $cc_data['id']));
				}

				$notification = array(
					'sender_id'			=>	0,
					'user_id'			=>	$rdv->user_id,
					'is_read'			=>	0,
					'is_delete'			=>	0,
					'date_notification'	=>	date("Y-m-d H:i:s"),
					'message'			=>	'Un nouveau rendez vous vient d etre pris',
					'third_type'		=>	'rdv',
					'third_id'			=>	$rid,
				);
				$this->registry->db->insert('notifications', $notification);

				// Retourne la campagne a l utilisateur
				return $this->viewAction($rdv->source_id);
			}
		}

		showform:
		$row = new contacts();
	   	$row->get($_GET['tier_id']);
	   	$this->registry->smarty->assign('tier', $row);
		$this->registry->smarty->assign('submit_url', 'index.php/campaign/take_rdv');
		return $this->registry->smarty->fetch(VIEW_PATH.'rdv'.DS.'form.meg');
	}

}// end class