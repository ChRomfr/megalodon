<?php

class entrepriseController extends Controller{

	/**
	*	@desc : Affiche la liste des entreprises en fonction des parametres passer le formulaire
	*
	*/
	public function indexAction(){	

		if( isset($_GET['csv']) ){
			return $this->csvAction();
		}

		$param = $this->getFiltreParam();

		$tmp = $this->registry->db->select('COUNT(e.id) as qte')->from('entreprises e')->left_join('entreprise_organisme eo','e.id = eo.entreprise_id')->where_free($param)->get_one();
		$nbEts = $tmp['qte'];
	
		// Recuperation des entreprises avec paginations
		$Pagination = new Zebra_Pagination();
		$Pagination->records($nbEts);
		$Pagination->records_per_page($this->registry->config['per_page']);

		$Ets = 	$this->db->select('e.*')
				->from('entreprises e')
				->left_join('entreprise_organisme eo','e.id = eo.entreprise_id');

		if(isset($_GET['filtre']['categorie_condition']) && $_GET['filtre']['categorie_condition'] == 'AND'){

			$cats = $_GET['filtre']['categorie'];
			$nbcat = count($cats);
			$jointure = "(SELECT ec1.categorie_id, ec1.entreprise_id FROM entreprise_categorie ec1 WHERE ec1.categorie_id IN (";
			foreach($cats as $key => $value){
				$jointure .= $value .',';
			}

			// Suppression de la derniere virguel
			$jointure = substr($jointure, 0, -1);

			$jointure .= ") GROUP BY ec1.entreprise_id HAVING COUNT(ec1.entreprise_id)=".$nbcat." ) t1";
			
			$this->db->left_join($jointure, 'e.id = t1.entreprise_id');
			$param .= " AND e.id = t1.entreprise_id ";

			$Ets = 	$this->db->where_free($param)
					->order('raison_social')
					->get();

				
		}else{

			$Ets = 	$this->db->where_free($param)
				->order('raison_social')
				->limit($this->registry->config['per_page'])
				->offset(getOffset($this->registry->config['per_page']))
				->get();

			$this->registry->smarty->assign('Pagination',$Pagination);
		}

		$this->registry->smarty->assign(array(
			'Ets'			=>	$Ets,
		));

		return $this->registry->smarty->fetch(VIEW_PATH . 'entreprise' . DS . 'index.tpl');
	}

	public function addAction(){

		if( $this->registry->Http->post('ets') !== null ){
			$entreprise = new entreprise($this->registry->Http->post('ets'));

			$entreprise->telephone 	= clearphonenumber($entreprise->telephone);
			$entreprise->fax 		= clearphonenumber($entreprise->fax);
			$entreprise->email 		= trim($entreprise->email);
			$entreprise->siret 		= clearsiret($entreprise->siret);
			$entreprise->client 	= 0;
			$entreprise->isValid 	= 1;

			if($entreprise->isValid() === true){
				$entreprise->id = $entreprise->save();

				// Traitements des categories
				$this->registry->db->delete('entreprise_categorie',null,array('entreprise_id =' => $entreprise->id));
				$categories = $this->registry->Http->post('categories');
				if(is_array($categories) && !empty($categories)){
					foreach ($categories as $key => $value) {
						$this->registry->db->insert('entreprise_categorie',array('entreprise_id' => $entreprise->id, 'categorie_id' => $value));
					}
				}

				return $this->registry->Helper->redirect($this->registry->Helper->getLink("entreprise/fiche/". $entreprise->id),3,"Entreprise enregistree");
			}
		}

		printform:
		$this->getFormValidatorJs();

		$this->registry->smarty->assign(array(
			'apes'		=>	$this->registry->db->get('ape'),
		));

		return $this->registry->smarty->fetch(VIEW_PATH . 'entreprise' . DS . 'add.tpl');
	}

	public function editAction($eid){

		if( $this->registry->Http->post('ets') !== null ){
			$entreprise = new entreprise($this->registry->Http->post('ets'));

			$entreprise->telephone 	= clearphonenumber($entreprise->telephone);
			$entreprise->fax 		= clearphonenumber($entreprise->fax);
			$entreprise->email 		= trim($entreprise->email);
			$entreprise->siret 		= clearsiret($entreprise->siret);
			$entreprise->client 	= 0;
			$entreprise->isValid 	= 1;

			if($entreprise->isValid() === true){
				$entreprise->save();

				// Traitements des categories
				$this->registry->db->delete('entreprise_categorie',null,array('entreprise_id =' => $entreprise->id));
				$categories = $this->registry->Http->post('categories');
				if(is_array($categories) && !empty($categories)){
					foreach ($categories as $key => $value) {
						$this->registry->db->insert('entreprise_categorie',array('entreprise_id' => $entreprise->id, 'categorie_id' => $value));
					}
				}
				return $this->registry->Helper->redirect($this->registry->Helper->getLink("entreprise/fiche/". $entreprise->id),3,"Entreprise modifie");
			}
		}

		printform:
		$entreprise = new entreprise();
		$entreprise->get($eid);
		$this->getFormValidatorJs();

		$this->registry->smarty->assign(array(
			'apes'			=>	$this->registry->db->get('ape'),
			'ets'			=>	$entreprise,
			'categories'	=>	$this->registry->db->get('entreprise_categorie',array('entreprise_id =' => $entreprise->id)),
		));

		return $this->registry->smarty->fetch(VIEW_PATH . 'entreprise' . DS . 'edit.tpl');
	}

	/**
	 * Marque une entreprise comme supprimee
	 * @param  [type] $eid [description]
	 * @return [type]      [description]
	 */
	public function deleteAction($eid){
		$this->registry->db->update('entreprises',array('isDelete' => 1), array('id =' => $eid));
		return $this->registry->Helper->redirect($this->registry->Helper->getLink("entreprise"),3,"Entreprise supprimee");
	}

	/**
	*	@desc : Affiche la fiche entreprise
	*	@return string : code HTML de la page
	*/
	public function ficheAction($id){
		$Markers = array();

		$entreprise = $this->db->select('e.*')
						->from('entreprises e')
						->where(array('e.id =' => $id))
						->get_one();
						
		$contacts = $this->db->select('c.*, s.libelle as service, p.libelle as poste')
					->from('contact c')
					->left_join('poste p','c.poste_id = p.id')
					->left_join('service s','c.service_id = s.id')
					->where(array('entreprise_id =' => $id, 'c.isDelete =' => 0))
					->get();

		$categories = 	$this->db->select('c.libelle')
						->from('categorie c')
						->left_join('entreprise_categorie ec','c.id = ec.categorie_id')
						->where(array('ec.entreprise_id =' => $id))
						->get();

		// Traitement googlemap
		if(empty($entreprise['lat']) && empty($entreprise['lng']) && !empty($entreprise['ville'])){
			// On essaie de recuperer les coordonnées de la societe
			require_once ROOT_PATH . 'kernel' . DS . 'lib' . DS . 'GoogleMapAPIv3.class.php';
			$gmap = new GoogleMapApi();
			$coord = $gmap->geocoding($entreprise['adresse1'] .' '. $entreprise['code_postal'] . ' '. $entreprise['ville'] . ' FRANCE');
			if(is_numeric($coord[2])){
				$Markers[] = array(
					'lat' 	=> $coord[2], 
					'lng' 	=> $coord[3], 
					'data' 	=> array(
						'rs'				=>	$entreprise['raison_social'],
						'adresse'			=>	$entreprise['adresse1'],
						'code_postal'		=>	$entreprise['code_postal'],
						'ville'				=>	$entreprise['ville']
						),
					'options'	=> array(
						'icon'	=>	!empty($entreprise['client']) ? 'http://meg.intranet.domaineb/web/images/markers/pin1.png' : 	'http://meg.intranet.domaineb/web/images/markers/pin2.png'
					),
				);
				//$this->registry->db->update('entreprises',array('lat' => $coord[2], 'lng' => $coord[3]), array('id =' => $entreprise['id']));
			}
		}else{
			$Markers[] = array(
					'lat' 	=> $entreprise['lat'], 
					'lng' 	=> $entreprise['lng'], 
					'data' 	=> array(
						'rs'				=>	$entreprise['raison_social'],
						'adresse'			=>	$entreprise['adresse1'],
						'code_postal'		=>	$entreprise['code_postal'],
						'ville'				=>	$entreprise['ville']
						),
					'options'	=> array(
						'icon'	=>	!empty($entreprise['client']) ? 'http://meg.intranet.domaineb/web/images/markers/pin1.png' : 	'http://meg.intranet.domaineb/web/images/markers/pin2.png'
					),
				);
		}

		$this->registry->load_web_lib('gmap3/gmap3.js','js');
		$this->registry->load_web_lib('gmap3/gmap3.css','css');
		$this->registry->load_web_lib('chosen/chosen.css','css');
        $this->registry->load_web_lib('chosen/chosen.jquery.min.js','js');
        $this->registry->load_web_lib('markitup/skins/meg/style.css','css');
        $this->registry->load_web_lib('markitup/sets/default/style.css','css');
        $this->registry->load_web_lib('markitup/jquery.markitup.js','js');
        $this->registry->load_web_lib('markitup/sets/default/set.js','js');

		$this->registry->smarty->assign(array(
			'entreprise'		=>	$entreprise,
			'contacts'			=>	$contacts,
			'categories'		=>	$categories,
			'markers'			=>	json_encode($Markers, JSON_NUMERIC_CHECK),
		));

		return $this->registry->smarty->fetch(VIEW_PATH . 'entreprise' . DS . 'fiche.tpl');
	}

	/**
	*	Exporte la liste des entreprises en fonctions des filtres
	*	@return void
	*/
	private function csvAction(){

		// Recuperation des entreprises

		$param = $this->getFiltreParam();
		$this->db->select('e.raison_social, e.email')
			->from('entreprises e')
			->left_join('entreprise_organisme eo','e.id = eo.entreprise_id');

		if(isset($_GET['filtre']) && $_GET['filtre']['categorie_condition'] == 'AND'){

			$cats = $_GET['filtre']['categorie'];
			$nbcat = count($cats);
			$jointure = "(SELECT ec1.categorie_id, ec1.entreprise_id FROM entreprise_categorie ec1 WHERE ec1.categorie_id IN (";
			foreach($cats as $key => $value){
				$jointure .= $value .',';
			}

			// Suppression de la derniere virguel
			$jointure = substr($jointure, 0, -1);

			$jointure .= ") GROUP BY ec1.entreprise_id HAVING COUNT(ec1.entreprise_id)=".$nbcat." ) t1";
			
			$this->db->left_join($jointure, 'e.id = t1.entreprise_id');
			$param .= " AND e.id = t1.entreprise_id ";

			$entreprises = 	$this->db->where_free($param)
							->order('raison_social')
							->get();

				
		}else{

			$entreprises =	$this->db->where_free($param)
							->order('raison_social')
							->get();
		}

		// Creation du CSV
		$Csv = '';
		$Csv = "Entreprise;Email\n";
		foreach ($entreprises as $row) {
			$Csv .= $row['raison_social'] . ";" . $row['email'] ."\n";
		}

		// On affiche le fichier
		header('Content-Type: application/csv-tab-delimited-table');
		header('Content-disposition: filename=Export_Entreprise.csv');
		echo $Csv;
		exit;
	}

	/**
	*	Construit les parametre du WHERE pour les requetes
	*	@return string
	*/
	private function getFiltreParam(){
		$param = " isValid = 1 ";

		if( isset($_GET['filtre']['deleted']) ){
			$param .= " AND e.isDelete = 1 ";
		}else{
			$param .= " AND e.isDelete = 0 "; 
		}

		// Traitement des filtres
		if( isset($_GET['filtre']) ){
			$filtres = $_GET['filtre'];

			if( isset($filtres['departement']) && !empty($filtres['departement']) ){
				$param .= " AND e.code_postal LIKE '". $filtres['departement'] ."%' ";
			}

			if( isset($filtres['email_is_valid']) ){
				$param .= " AND e.email != '' ";
			}

			// Organismes
			if(isset($filtres['organisme']) && !empty($filtres['organisme'])){
				$organismes = $filtres['organisme'];
				
				if(!empty($organismes)){
					
					$param .= " AND ( ";
					$i=0;
					foreach($organismes as $k => $v){
						if($i!=0){
							$param .= " OR eo.organisme_id = ". $v ." ";
						}else{
							$param .= " eo.organisme_id = ". $v ." ";
						}
						$i++;
					}
					$param .= " ) ";
				}

			}
			
			// Effectif
			if( isset($filtres['effectif_mini']) && !empty($filtres['effectif_mini']) && isset($filtres['effectif_max']) && !empty($filtres['effectif_max']) ){
				$param .= " AND e.effectif >= ". $filtres['effectif_mini'] ." AND e.effectif <= ". $filtres['effectif_max'] ." ";
			}
			
			// Client
			if( isset($filtres['is_client']) ){
				$param .= " AND e.client = 1 ";
			}
			
			// Query
			if( isset($filtres['query']) && !empty($filtres['query']) ){
				$s = trim($filtres['query']);
				if( is_numeric($s) ){
					// Recherche sur siret
					$param .= " AND ( e.siret = '". $s ."' OR e.telephone = '". $s ."' OR fax = '". $s ."' ) ";
				}elseif(VerifieAdresseMail($s) == true){
					$param .= " AND e.email = '". $s ."' ";
				}else{
					// Recherche sur la raison social
					$param .= " AND e.raison_social LIKE '%".$s."%' ";
				}
			}	
		}

		return $param;
	}

	public function checkemailAction(){
		$email = $_GET['ets']['email'];
		$email = trim($email);

		if( VerifieAdresseMail($email) == false ):
			return "false";
		endif;

		$Result = $this->app->db->count('entreprises', array('email =' => $email) );

		if( $Result > 0):
			return "false";
		else:
			return "true";
		endif;
	}

	public function checksiretAction(){
		$siret = $_GET['ets']['siret'];
		$siret = clearsiret($siret);


		$Result = $this->app->db->count('entreprises', array('siret =' => $siret) );

		if( $Result > 0):
			return "false";
		else:
			return "true";
		endif;
	}

	public function geolocAction(){

		return $this->registry->smarty->fetch(VIEW_PATH.'entreprise'.DS.'geoloc.tpl');
	}

	public function geolocajaxAction(){
		set_time_limit(0);
		
		$result_ok = 0;
		$result_fail = 0;

		require_once ROOT_PATH . 'kernel' . DS . 'lib' . DS . 'GoogleMapAPIv3.class.php';
		$gmap = new GoogleMapApi();
		$entreprises = $this->registry->db->get('entreprises');

		foreach($entreprises as $row){
			$coord = $gmap->geocoding($row['adresse1'] .' '. $row['code_postal'] . ' '. $row['ville'] . ' FRANCE');
			if(is_numeric($coord[2])){
				// Geoloc OK
				$data = array(
					'lat'				=>	$coord[2],
					'lng' 				=> 	$coord[3],
					'date_last_geoloc'	=>	date("Y-m-d H:i:s"),
					'id'				=>	$row['id']
				);
				$result_ok++;
			}else{
				$data = array(
					'date_last_geoloc'	=>	date("Y-m-d H:i:s"),
					'id'				=>	$row['id'],
				);
				$result_fail++;
			}

			// Sauvegarde
			$this->registry->db->update('entreprises',$data);
		}// endforeach

		$this->registry->smarty->assign(array(
			'tot_ets'		=>	count($entreprises),
			'result_fail'	=>	$result_fail,
			'result_ok'		=>	$result_ok,
		));

		return $this->registry->smarty->fetch(VIEW_PATH.'entreprise'.DS.'geolocajax.tpl');	
	}
	
	/**
	 * Geolocalise les entreprises sans coordonnees
	 * @return [type] [description]
	 */
	public function geolocemptycoordajaxAction(){
		set_time_limit(0);
		$result_ok = 0;
		$result_fail = 0;
		$this->load_manager('entreprise');
		$entreprises =	$this->manager->entreprise->getByEmptyCoords();

		foreach($entreprises as $entreprise){
			$entreprise = $this->getCoord($entreprise);

			if(!empty($entreprise['lat'])){				
				$result_ok++;
			}else{
				$result_fail++;
			}
			
			$this->registry->db->update('entreprises', $entreprise);
		}

		$this->registry->smarty->assign(array(
			'tot_ets'		=>	count($entreprises),
			'result_fail'	=>	$result_fail,
			'result_ok'		=>	$result_ok,
		));

		return $this->registry->smarty->fetch(VIEW_PATH.'entreprise'.DS.'geolocemptycoordajax.tpl');	
	}
	
	public function geolocnewajaxAction(){
		
	}

	public function cleandataAction(){
		return $this->registry->smarty->fetch(VIEW_PATH.'entreprise'.DS.'cleandata.tpl');
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

	public function cleandataajaxAction(){
		$lcd = 0;
		// Recuperation de tous les liens dans categories
		$links_categorie = $this->registry->db->get('entreprise_categorie');

		// On boucle sur le resultat pour verifie les liens
		foreach ($links_categorie as $row) {
			$result = $this->registry->db->count('entreprises', array('id =' => $row['entreprise_id']));
			if($result == 0){
				$this->registry->db->delete('entreprise_categorie', null, array('entreprise_id =' => $row['entreprise_id']));
				$lcd++;
			}
		}

		$this->registry->smarty->assign(array(
			'lcd'		=>	$lcd,
		));

		return $this->registry->smarty->fetch(VIEW_PATH.'entreprise'.DS.'cleandataajax.tpl');
	}

	public function checkadremailajaxAction(){
		$email_invalide = 0;
		$emails = array();

		$entreprises = $this->registry->db->get('entreprises', array('email != ' => '""'));
		
		foreach($entreprises as $row){
			if(!empty($row['email'])){
				if(VerifieAdresseMail($row['email']) == false){
					$email_invalide++;
					$emails[] = $row;
				}
			}			
		}

		$this->registry->smarty->assign(array(
			'email_invalide'		=>	$email_invalide,
			'emails'				=>	$emails,
		));

		return $this->registry->smarty->fetch(VIEW_PATH.'entreprise'.DS.'checkadremail.tpl');
	}

	public function removeemailajaxAction($email){
		$this->registry->db->update('entreprises', array('email' => ''), array('email =' => $email));
		return $this->checkadremailajaxAction();
	}

	public function sendemailAction($eid){
		
		if( !is_null($this->registry->Http->post('mail')) ){
			$mail = $this->registry->Http->post('mail');

			if( !VerifieAdresseMail($mail['de']) || !VerifieAdresseMail($mail['a'])){
				return $this->ficheAction($eid);
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
			);

			// Sauvegarde dans la base du mail
			$email_id = $this->registry->db->insert('entreprise_email', $email);

			// Envoie de l email et recuperation du resultat
			$result = sendEmail($mail['a'],$mail['de'],$mail['sujet'],'',$mail['body']);

			if( $result === true){
				$email['result'] = 'Succes';
				$this->registry->db->update('entreprise_email', $email, array('id =' => $email_id));
				sendEmail($mail['de'],$mail['de'], 'Copie message - '. $mail['sujet'], '',$mail['body']);
				$this->smarty->assign('FlashMessage','Email envoye. Une copie a ete envoye a l\\\'adresse suivante : '. $mail['de']);
			}else{
				$email['result'] = $result;
				$this->registry->db->update('entreprise_email', $email, array('id =' => $email_id));
				$this->smarty->assign('FlashMessage','Une erreur est survenue durant l\\\'envoie : '. $result);
			}
			
			return $this->ficheAction($eid);
		}else{
			return $this->ficheAction($eid);
		}
	}

}