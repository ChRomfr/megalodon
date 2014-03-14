<?php

class contactsManager extends BaseModel{
	
	/**
	*	Retourne le nombre de ligne que va retourne la fonction get
	*	@param string $where parametre pour la requete
	*	@return int nombre de contact
	*/
	public function count($_where = null){
		$this->db->select(' COUNT(DISTINCT(c.id)) as nb')
			->from('contacts c')
			->left_join('personne p','c.id = p.contact_id')
			->left_join('societe s','c.id = s.contact_id')
			->left_join('telephones t','c.id = t.contact_id');
			
		// Traitement des filtres des categories et jointure
		if( isset($_GET['filtre']['categorie']) && isset($_GET['filtre']['categorie_condition']) && $_GET['filtre']['categorie_condition'] == 'AND' && count($_GET['filtre']['categorie']) > 0){

			$cats = $_GET['filtre']['categorie'];
			$nbcat = count($cats);
			
			$jointure = "(SELECT cc1.contact_id, cc1.categorie_id FROM contacts_categorie cc1 WHERE cc1.categorie_id IN (";
			foreach($cats as $key => $value){
				$jointure .= $value .',';
			}

			// Suppression de la derniere virguel
			$jointure = substr($jointure, 0, -1);

			$jointure .= ") GROUP BY cc1.contact_id HAVING COUNT(cc1.contact_id)=".$nbcat." ) t1";
			
			$this->db->left_join($jointure, 'c.id = t1.contact_id');
		}elseif(isset($_GET['filtre']['categorie']) && isset($_GET['filtre']['categorie_condition']) && $_GET['filtre']['categorie_condition'] == 'OR' && count($_GET['filtre']['categorie']) > 0){
			$cats = $_GET['filtre']['categorie'];
			$nbcat = count($cats);
			
			$this->db->left_join('contacts_categorie cc','c.id = cc.contact_id');
			
			$where = " ( ";
			
			foreach($cats as $key => $value){
				$where .= ' cc.categorie_id = '. $value . ' OR';
			}
			
			$where = substr($where, 0, -2) . ' ) ';
			
			$this->db->where_free($where);
		}
			
		$this->db->where_free(' c.isDelete = 0 ');
		
		if(isset($_GET['filtre']['categorie']) && isset($_GET['filtre']['categorie_condition']) && $_GET['filtre']['categorie_condition'] == 'AND' && count($_GET['filtre']['categorie']) > 0){
			$this->db->where_free( " c.id = t1.contact_id " );
		}
		
		if(!is_null($_where)){
			$this->db->where_free($_where);
		}
			
		$result = $this->db->get_one();
		
		return $result['nb'];
	}
	
	
	
	/**
	*	Gere la requete pour recuperer les contacts dans la base
	*	@return array Tableau contenant le jeu de resultat
	*/
	public function get($_where = null, $limit = null, $offset = null, $fields = 'c.*'){

		
		$this->db->select(' DISTINCT(c.id), '. $fields .', concat_ws("",s.raison_social, p.nom) as nom, p.prenom')
			->from('contacts c')
			->left_join('personne p','c.id = p.contact_id')
			->left_join('societe s','c.id = s.contact_id')
			->left_join('telephones t','c.id = t.contact_id');
			
			
		// Traitement des filtres des categories et jointure
		if( isset($_GET['filtre']['categorie']) && isset($_GET['filtre']['categorie_condition']) && $_GET['filtre']['categorie_condition'] == 'AND' && count($_GET['filtre']['categorie']) > 0){

			$cats = $_GET['filtre']['categorie'];
			$nbcat = count($cats);
			
			$jointure = "(SELECT cc1.contact_id, cc1.categorie_id FROM contacts_categorie cc1 WHERE cc1.categorie_id IN (";
			foreach($cats as $key => $value){
				$jointure .= $value .',';
			}

			// Suppression de la derniere virguel
			$jointure = substr($jointure, 0, -1);

			$jointure .= ") GROUP BY cc1.contact_id HAVING COUNT(cc1.contact_id)=".$nbcat." ) t1";
			
			$this->db->left_join($jointure, 'c.id = t1.contact_id');
		}elseif(isset($_GET['filtre']['categorie']) && isset($_GET['filtre']['categorie_condition']) && $_GET['filtre']['categorie_condition'] == 'OR' && count($_GET['filtre']['categorie']) > 0){
			$cats = $_GET['filtre']['categorie'];
			$nbcat = count($cats);
			
			$this->db->left_join('contacts_categorie cc','c.id = cc.contact_id');
			
			$where = " ( ";
			
			foreach($cats as $key => $value){
				$where .= ' cc.categorie_id = '. $value . ' OR';
			}
			
			$where = substr($where, 0, -2) . ' ) ';
			
			$this->db->where_free($where);
		}
			
		$this->db->where_free(' 1 = 1 ');
		
		if(isset($_GET['filtre']['categorie']) && isset($_GET['filtre']['categorie_condition']) && $_GET['filtre']['categorie_condition'] == 'AND' && count($_GET['filtre']['categorie']) > 0){
			$this->db->where_free( " c.id = t1.contact_id " );
		}
		

		if(!is_null($_where)){
			$this->db->where_free($_where);
		}
		
		$this->db->order('nom');

		if(!is_null($limit) && is_numeric($limit)){
			$this->db->limit($limit);
		}
		
		if(!is_null($offset) && is_numeric($offset)){
			$this->db->offset($offset);
		}	
		
		return $this->db->get();
	}
	
	/**
	*	Recupere tout les informations d un contact dans la base
	*	@param int $id
	*	@return array $result
	*/
	public function getById($id, $history = 1){
		
		// Recuperation du contact
		$this->db->select('c.*, s.*, p.*, po.libelle as poste, se.libelle as service, s.id as sid, c.id as contact_id, s.id as societe_id, p.id as personne_id, p.societe_id as societe_id, group_concat(u2.identifiant) as users, group_concat(u2.id) as users_id, group_concat(gr.name) as groups, group_concat(gr.id) as groups_id')
			->from('contacts c')
			->left_join('societe s','c.id = s.contact_id')
			->left_join('personne p','c.id = p.contact_id')
			->left_join('poste po','p.poste_id = po.id')
			->left_join('service se','p.service_id = se.id')
			->left_join('contacts_users cu','c.id = cu.contact_id')
			->left_join('user u2','cu.user_id = u2.id')
			->left_join('contacts_groups cg','c.id = cg.contact_id')
			->left_join('groupe gr','cg.group_id = gr.id')
			->where(array('c.id =' => $id));
			
		$result = $this->db->get_one();
		
		// Recuperation des categories
		$this->db->select('c.id, c.libelle')
			->from('contacts_categorie cc')
			->left_join('categorie c','cc.categorie_id = c.id')
			->where(array('cc.contact_id =' => $id));
			
		$result['categories'] = $this->db->get();

		
		// Recuperations des telephones
		$this->db->select('t.*')
			->from('telephones t')
			->where(array('contact_id =' => $id));
			
		$result['telephones'] = $this->db->get();
		
		// Recuperation societe si personne / pro
		if(!empty($result['societe_id'])){
			//$result['societe'] = $this->db->get_one('societe', array('id =' => $result['societe_id']));
			$result['societe'] = $this->db->get_one('societe', array('contact_id =' => $result['societe_id']));
		}
								
		// Recuperation des emails
		$result['emails'] = $this->db->select('ce.*, u.identifiant as email_user')
							->from('contacts_email ce')
							->left_join('user u','ce.user_id = u.id')
							->where(array('ce.entreprise_id =' => $id))
							->order('ce.date_send DESC')
							->get();

		// Recuperation des suivis
		$result['suivis'] 	= 	$this->db->select('s.*, u.identifiant')
								->from('contacts_suivi s')
								->left_join('user u','s.uid = u.id')
								->where(array('s.cid =' => $id))
								->order('s.date_suivi DESC')
								->get();
								
		// Formatage des données
		if(!empty($result['users'])){
			$data = explode(',',$result['users']);
			$data2 = explode(',',$result['users_id']);
			$result['users'] = array();
			$i=0;
			foreach($data as $k => $v){
				$result['users'][$data2[$i]] = $v;
				$i++;
			}
		}
		
		if(!empty($result['groups'])){
			$data = explode(',',$result['groups']);
			$data2 = explode(',',$result['groups_id']);
			$result['groups'] = array();
			$i=0;
			foreach($data as $k => $v){
				$result['groups'][$data2[$i]] = $v;
				$i++;
			}
		}			
		return $result;
	}

	public function getResumeById($id){
		// Recuperation du contact
		$this->db->select('c.*, s.*, p.*, po.libelle as poste, se.libelle as service, s.id as sid, c.id as contact_id, s.id as societe_id, p.id as personne_id, p.societe_id as societe_id')
			->from('contacts c')
			->left_join('societe s','c.id = s.contact_id')
			->left_join('personne p','c.id = p.contact_id')
			->left_join('poste po','p.poste_id = po.id')
			->left_join('service se','p.service_id = se.id')
			->where(array('c.id =' => $id));
			
		return $this->db->get_one();
	}

	public function getContactsOfSociete($contact_id){
		return 	$this->db->select('c.*, p.*, po.libelle as poste, se.libelle as service, c.id as cid')
					->from('contacts c')
					->left_join('personne p','c.id = p.contact_id')
					->left_join('poste po','p.poste_id = po.id')
					->left_join('service se','p.service_id = se.id')
					//->where(array('p.societe_id =' => $result['sid'], 'c.isDelete !=' => 1))
					->where(array('p.societe_id =' => $contact_id, 'c.isDelete !=' => 1))
					->get();
	}


	public function getLogs($contact_id){
		return $this->db->select('cl.*,u.identifiant as log_user')
					->from('contacts_log cl')
					->left_join('user u','cl.user_id = u.id')
					->where(array('cl.contact_id =' => $contact_id))
					->order('cl.date_log DESC')
					->get();
	}

	public function getByEmail($email, $history = 1){
		
		// Recuperation du contact
		$this->db->select('c.*, s.*, p.*, po.libelle as poste, se.libelle as service, s.id as sid, c.id as contact_id, s.id as societe_id, p.id as personne_id, p.societe_id as societe_id')
			->from('contacts c')
			->left_join('societe s','c.id = s.contact_id')
			->left_join('personne p','c.id = p.contact_id')
			->left_join('poste po','p.poste_id = po.id')
			->left_join('service se','p.service_id = se.id')
			->where_free('c.email LIKE "%' . $email .'%"');
			
		$result = $this->db->get_one();
		/*
		// Recuperation des categories
		$this->db->select('c.id, c.libelle')
			->from('contacts_categorie cc')
			->left_join('categorie c','cc.categorie_id = c.id')
			->where(array('cc.contact_id =' => $id));
			
		$result['categories'] = $this->db->get();

		
		// Recuperations des telephones
		$this->db->select('t.*')
			->from('telephones t')
			->where(array('contact_id =' => $id));
			
		$result['telephones'] = $this->db->get();
		
		// Recuperation societe si personne / pro
		if(!empty($result['societe_id'])){
			$result['societe'] = $this->db->get_one('societe', array('id =' => $result['societe_id']));
		}
		
		// Recuperation personne si societe
		if(!empty($result['raison_social'])){
			$result['contacts']	=	$this->db->select('c.*, p.*, po.libelle as poste, se.libelle as service, c.id as cid')
									->from('contacts c')
									->left_join('personne p','c.id = p.contact_id')
									->left_join('poste po','p.poste_id = po.id')
									->left_join('service se','p.service_id = se.id')
									->where(array('p.societe_id =' => $result['sid'], 'c.isDelete !=' => 1))
									->get();
		}
		
		// Recuperation des logs
		if( $history == 1){
			$result['logs']	=	$this->db->select('cl.*,u.identifiant as log_user')
									->from('contacts_log cl')
									->left_join('user u','cl.user_id = u.id')
									->where(array('cl.contact_id =' => $id))
									->order('cl.date_log DESC')
									->get();
		}
								
		// Recuperation des emails
		$result['emails'] = $this->db->select('ce.*, u.identifiant as email_user')
							->from('contacts_email ce')
							->left_join('user u','ce.user_id = u.id')
							->where(array('ce.entreprise_id =' => $id))
							->order('ce.date_send DESC')
							->get();

		// Recuperation des mailings
		$result['mailings'] = 	$this->db->select('m.id, m.libelle')
								->from('mailings m')
								->left_join('contacts_mailing cm','cm.mailing_id = m.id')
								->where(array('cm.contact_id =' => $id))
								->get();

		// Recuperation des suivis
		$result['suivis'] 	= 	$this->db->select('s.*, u.identifiant')
								->from('contacts_suivi s')
								->left_join('user u','s.uid = u.id')
								->where(array('s.cid =' => $id))
								->order('s.date_suivi DESC')
								->get();
								
		// Recuperation des fichiers
		$result['files']	=	$this->db->select('cf.*, u.identifiant')
								->from('contacts_files cf')
								->left_join('user u','cf.user_id = u.id')
								->where(array('cf.contact_id =' => $id))
								->order('cf.name')
								->get();
		*/
		return $result;
	}

	public function getInCorbeille(){

		return 	$this->db->select(' DISTINCT(c.id), c.*, s.raison_social, p.nom, p.prenom, p.societe_id')
				->from('contacts c')
				->left_join('personne p','c.id = p.contact_id')
				->left_join('societe s','c.id = s.contact_id')
				->left_join('telephones t','c.id = t.contact_id')
				->where(array('isDelete =' => 1))
				->order('s.raison_social, p.nom')
				->get();

	}

	public function cleantrash(){
		$contacts = $this->db->get('contacts', array('isDelete =' => 1));

		foreach($contacts as $row){
			$this->db->delete('contacts',$row['id']);
			$this->db->delete('personne', null, array('contact_id =' => $row['id']));
			$this->db->delete('societe', null, array('contact_id =' => $row['id']));
			$this->db->delete('contacts_categorie', null, array('contact_id =' => $row['id']));
			$this->db->delete('contacts_email', null, array('entreprise_id =' => $row['id']));
			$this->db->delete('contacts_log', null, array('contact_id =' => $row['id']));
			$this->db->delete('telephones', null, array('contact_id =' => $row['id']));
			$this->db->delete('campaign_contacts', null, array('contact_id =' => $row['id']));
			$this->db->delete('campaign_contacts_suivi', null, array('contact_id =' => $row['id']));
		}

		return count($contacts);
	}

	public function getByEmptyCoords($limit = null){
			$date_15_days = date("Y-m-d H:i:s", (time() - 1296000));

			$this->db
				->select('c.*')
				->from('contacts c')
				->where_free('(lat IS NULL OR lat = "") AND adress != "" AND zip_code != "" AND city != "" AND (date_last_geoloc = "" OR date_last_geoloc < "'. $date_15_days .'" OR date_last_geoloc IS NULL)');
				
		if( !is_null($limit) && is_numeric($limit) ){
			$this->db->limit($limit);
		}
		
		return $this->db->get();

		print_r($this->db->queries);
	}
	
	public function getAgences($parent_id){
		$this->db
			->select('s.raison_social, c.city, c.id as contacts_id')
			->from('societe s')
			->left_join('contacts c','s.contact_id = c.id')
			->where(array('s.parent_id =' => $parent_id, 'c.isDelete =' => 0));
			
		return $this->db->get();
	}
	
	public function getSiegeSocial($id){
		$this->db
			->select('s.raison_social, c.city, c.id as contacts_id')
			->from('societe s')
			->left_join('contacts c','s.contact_id = c.id')
			->where(array('s.id =' => $id, 'c.isDelete =' => 0));
			
		return $this->db->get_one();
	}

	/**
	 * Recupere tout les contacts sans email de type pro
	 * @return [type] [description]
	 */
	public function get_no_email(){
		$this->db->select('c.*, p.nom, p.prenom')
			->from('contacts c')
			->left_join('personne p', 'p.contact_id = c.id')
			->where_free('c.ctype = "societe_contact" AND (c.email IS NULL OR c.email = "")');

		return $this->db->get();
	}
}