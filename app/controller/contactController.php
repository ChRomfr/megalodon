<?php

class contactController extends Controller{
	
	/**
	 * Affiche la liste des contacts en fonction des parametres
	 * de filtre.
	 * @return string code html
	 */
	public function indexAction(){

		if( isset($_GET['csv']) ){
			return $this->csvAction();
		}

		$param = $this->getFiltreParam();

		$tmp = $this->db->select('count(c.id) as nb')->from('contact c')->left_join('entreprises e','c.entreprise_id = e.id')->where($param)->get_one();

		$nbContact = $tmp['nb'];

		// Recuperation des entreprises avec paginations
		$Pagination = new Zebra_Pagination();
		$Pagination->records($nbContact);
		$Pagination->records_per_page($this->registry->config['per_page']);

		$this->db->select('c.*, e.*, c.email as email')
			->from('contact c')
			->left_join('entreprises e','c.entreprise_id = e.id');

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

			$contacts	=	$this->db->where_free($param)
							->order('raison_social')
							->get();

		}else{
			$contacts	=	$this->registry->db->where($param)
							->order('e.raison_social')
							->limit($this->registry->config['per_page'])
							->offset(getOffset($this->registry->config['per_page']))
							->get();

			$this->registry->smarty->assign('Pagination',$Pagination);
		}				

		$this->registry->smarty->assign(array(
			'contacts'			=>	$contacts,
		));

		return $this->registry->smarty->fetch(VIEW_PATH . 'contact' . DS . 'index.tpl');
	}

	/**
	 * [addAction description]
	 * @param $eid Identifiant de l entreprise
	 */
	public function addAction($eid){

		if($this->registry->Http->post('contact') !== null){

			$contact = new contact($this->registry->Http->post('contact'));
				
			if($contact->isValid() === true){

				$contact->date_add 		= date("Y-m-d H:i:s");
				$contact->date_edit 	= date("Y-m-d H:i:s");
				$contact->telephone 	= clearphonenumber($contact->telephone);
				$contact->mobile 		= clearphonenumber($contact->mobile);
				$contact->fax 			= clearphonenumber($contact->fax);

				$contact->save();

				return $this->registry->Helper->redirect($this->registry->Helper->getLink('entreprise/fiche/'. $contact->entreprise_id),3,'Contact enregistre');
			}
		}

		printform:
		$this->getFormValidatorJs();
		$this->registry->smarty->assign(array(
			'entreprise'		=>	$this->registry->db->get_one('entreprises',array('id =' => $eid)),
		));
		return $this->registry->smarty->fetch(VIEW_PATH . 'contact' . DS . 'add.tpl');
	}

	public function editAction($cid){

		if($this->registry->Http->post('contact') !== null){

			$contact = new contact($this->registry->Http->post('contact'));
				
			if($contact->isValid() === true){

				$contact->date_add 		= date("Y-m-d H:i:s");
				$contact->date_edit 	= date("Y-m-d H:i:s");
				$contact->telephone 	= clearphonenumber($contact->telephone);
				$contact->mobile 		= clearphonenumber($contact->mobile);
				$contact->fax 			= clearphonenumber($contact->fax);

				$contact->save();

				return $this->registry->Helper->redirect($this->registry->Helper->getLink('entreprise/fiche/'. $contact->entreprise_id),3,'Contact enregistre');
			}
		}

		printform:
		$contact = new contact();
		$contact->get($cid);
		$this->getFormValidatorJs();
		$this->registry->smarty->assign(array(
			'contact'			=>	$contact,
			'entreprise'		=>	$this->registry->db->get_one('entreprises',array('id =' => $contact->entreprise_id)),
		));
		return $this->registry->smarty->fetch(VIEW_PATH . 'contact' . DS . 'edit.tpl');
	}

	public function deleteAction($cid){
		$contact = new contact;
		$contact->get($cid);
		$this->registry->db->update('contact',array('isDelete' => 1),array('id =' => $cid));
		return $this->registry->Helper->redirect($this->registry->Helper->getLink('entreprise/fiche/'. $contact->entreprise_id),3,'Contact supprime');
	}

	public function checkemailAction(){
		$email = $_GET['contact']['email'];
		$email = trim($email);

		if( VerifieAdresseMail($email) == false ):
			return "false";
		endif;

		$Result = $this->app->db->count('contact', array('email =' => $email) );

		if( $Result > 0):
			return "false";
		else:
			return "true";
		endif;
	}

	/**
	*	Construit les parametre du WHERE pour les requetes
	*	@return string
	*/
	private function getFiltreParam(){
		$param = " isValid = 1 AND c.isDelete = 0 AND e.isDelete = 0 ";

		// Traitement des filtres
		if( isset($_GET['filtre']) ){
			$filtres = $_GET['filtre'];

			if( isset($filtres['departement']) && !empty($filtres['departement']) ){
				$param .= " AND e.code_postal LIKE '". $filtres['departement'] ."%' ";
			}

			if( isset($filtres['email_is_valid']) ){
				$param .= " AND c.email != '' ";
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

			//
			//PARAM SPECIFIQUE AU CONTACT
			//
			if(isset($filtres['poste']) && !empty($filtres['poste'])){
				$param .= ' AND c.poste_id = '.$filtres['poste'].' ';
			}

			if(isset($filtres['service']) && !empty($filtres['service'])){
				$param .= ' AND c.service_id = '.$filtres['service'] .' ';
			}

			// Query
			if( isset($filtres['query']) && !empty($filtres['query']) ){
				$s = trim($filtres['query']);
				if( is_numeric($s) ){
					// Recherche sur telephone et portable
					$param .= " AND ( c.telephone = '". $s ."' OR c.portable = '". $s ."' ) ";
				}elseif(VerifieAdresseMail($s) == true){
					$param .= " AND c.email = '". $s ."' ";
				}else{
					// Recherche sur la raison social
					$param .= " AND c.nom LIKE '%".$s."%' ";
				}
			}
		}

		return $param;
	}

	private function csvAction(){

		// Recuperation des entreprises

		$param = $this->getFiltreParam();
		
		$this->db->select('c.nom, c.prenom, c.email')
			->from('contact c')
			->left_join('entreprises e','c.entreprise_id = e.id');


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

			$contacts	=	$this->db->where_free($param)
							->order('raison_social')
							->get();

				
		}else{

			$contacts	=	$this->registry->db->where($param)
							->order('e.raison_social')
							->get();
		}
		
		if( isset($_GET['ets']) ){
			// Recuperation des entreprises
			$param = $this->getFiltreParamEntreprise();
			
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
		}
		

		// Creation du CSV
		$Csv = '';
		$Csv = "Contact;Email\n";
		foreach ($contacts as $row) {
			$Csv .= $row['nom'] . " " . $row['prenom'] . ";" . $row['email'] ."\n";
		}

		if( isset($_GET['ets']) ){
			foreach($entreprises as $row){
				$Csv .= $row['raison_social'] .";". $row['email'] ."\n";
			}
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
	private function getFiltreParamEntreprise(){
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

}