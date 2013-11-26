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

		$this->load_manager('mailing');

		$mailings = $this->manager->mailing->get();
		

		$this->registry->smarty->assign(array(
			'mailings'		=>	$mailings,
		));

		return $this->registry->smarty->fetch(VIEW_PATH . 'mailing' . DS . 'index.tpl');
	}
	
	public function calendrierAction(){
		// Lib pour affichage calendrier
		$this->registry->load_web_lib('fullcalendar/fullcalendar.css','css');
        $this->registry->load_web_lib('fullcalendar/fullcalendar.print.css','css');
        $this->registry->load_web_lib('fullcalendar/fullcalendar.min.js','js');
		return $this->registry->smarty->fetch(VIEW_PATH . 'mailing' . DS . 'calendar.tpl');
	}
	
	public function getmailingforcalendarAction(){
		$mailings = array();
		
		$this->load_manager('mailing');

		$Datas = $this->registry->db->get('mailings', array('valid =' => 1));
		
		foreach($Datas as $Data){
        	$mailings[] = array(
				'id' => $Data['id'], 
				'title' => $Data['libelle'], 
				'start' => $Data['date_wish'],
				'url' => $this->registry->Helper->getLink('mailing/fiche/'. $Data['id']));
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
			
			$mailing->save();
			
			$this->registry->smarty->assign('FlashMessage','Demande enregistrée');
			return $this->indexAction();
		}

		$this->getFormValidatorJs();
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
		
		$this->registry->smarty->assign('mailing', $mailing);
		$this->getFormValidatorJs();
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
		$where = $ccontacts->getWhere(unserialize($mailing->cible));

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
		
		$mailing = new mailing();
		$mailing->get($id);
		$mailing->valid = 1;
		$mailing->valid_on = date("Y-m-d H:i:s");
		$mailing->valid_by = $_SESSION['utilisateur']['id'];
		$mailing->save();		
		
		$this->registry->smarty->assign('FlashMessage','Mailing valide');
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