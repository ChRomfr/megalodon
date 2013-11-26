<?php

class importController extends Controller{

	public function indexAction(){

		if( isset($_POST) && !empty($_POST) ):

			$dir = ROOT_PATH . 'web' . DS . 'upload' . DS . 'csv' . DS;
		
			require_once ROOT_PATH . 'kernel' . DS . 'lib' . DS . 'upload' . DS . 'class.upload.php';
			
			if( !is_dir($dir) ):
				@mkdir($dir);
			endif;
			
	        $fichier = new Upload($_FILES['file_import']);
	        $name = uniqid();
	        if($fichier->uploaded):
	            $fichier->file_overwrite 		= true;
	            $fichier->file_new_name_body  	= $name;
				$fichier->file_new_name_ext		= 'csv';
	            $fichier->process($dir);

	            return $this->registry->Helper->redirect($this->registry->Helper->getLink('import/step2/' . $name));
			endif;

			
		endif;

		return $this->registry->smarty->fetch(VIEW_PATH . 'import' . DS . 'index.tpl');
	}

	public function step2Action($file){
		
		$dir = ROOT_PATH . 'web' . DS . 'upload' . DS . 'csv' . DS;

		// Verification fichier présent sur le serveur
		if( !is_file($dir . $file . '.csv') ){
			return $this->registry->Helper->redirect($this->registry->Helper->getLink('import'),5,'Le fichier demande n existe pas sur le serveur');
		} 

		// Verification dans la base si le fichier a deja ete importe
		$result = $this->registry->db->get_one('import_file_history', array('file =' => $file));
		if( !empty($result) ){
			return $this->registry->Helper->redirect($this->registry->Helper->getLink('import'),5,'Ce fichier a deja ete importe dans la base');
		}

		// Recuperation des entetes du fichier
		$lines = file(ROOT_PATH . 'web' . DS . 'upload' . DS . 'csv' . DS . $file .'.csv');

		// Verification fichier non vide
		if(count($lines) == 0){
			return $this->registry->Helper->redirect($this->registry->Helper->getLink('import'),5,'Le fichier envoye ne contient aucune ligne');
		}

		$data = str_getcsv($lines[0],';');

		$data_ets = array(
			'raison_social'	=> 	'Raison social', 
			'adresse1'		=> 	'Adresse 1', 
			'adresse2'		=> 	'Adresse 2', 
			'code_postal'	=>	'Code postal',
			'ville'			=>	'Ville',
			'telephone'		=>	'Telephone',
			'fax'			=>	'Fax',
			'email'			=>	'Email',
			'effectif'		=>	'Effectif',
			'siret'			=>	'Siret',
			'categorie'		=>	'Categorie',
			'ape'			=>	'APE',
			'client'		=>	'Client',
			'code_interne'	=>	'Code interne'
		);

		$data_ctc = array(
			'nom'			=>	'Nom',
			'prenom'		=>	'Prenom',
			'telephone'		=>	'Telephone',
			'portable'		=>	'Portable',
			'fax'			=>	'Fax',
			'email'			=>	'Email',
			'poste'			=>	'Poste',
			'service'		=>	'Service',
			'code_interne'	=>	'Code interne',
		);

		$this->registry->smarty->assign(array(
			'entetes'		=>	$data,
			'entreprise'	=>	$data_ets,
			'contact'		=>	$data_ctc,
			'file'			=>	$file,
		));

		return $this->registry->smarty->fetch(VIEW_PATH . 'import' . DS . 'step2.tpl');
	}

	/**
	*	@desc : Etape d enregistrement dans la base
	*	
	*/
	public function step3Action($file){

		// Verification si fichier deja importer
		$result = $this->dejaimporter($file);

		if( $result !== false ){
			$this->registry->smarty->assign('import',$result);
			return $this->registry->smarty->fetch(VIEW_PATH . 'import' . DS . 'dejaimporter.tpl'); 
		}

		$dir = ROOT_PATH . 'web' . DS . 'upload' . DS . 'csv' . DS;

		$lines = file(ROOT_PATH . 'web' . DS . 'upload' . DS . 'csv' . DS . $file .'.csv');

		$centreprise 	= $_POST['liaison_entreprise'];
		$ccontact 		= $_POST['liaison_contact'];
		$i=0;
		$nb_ets = 0;
		$nb_ctc = 0;

		// boucles pour parcourir le fichier
		foreach($lines as $k => $v){

			// On ignore la 1ere ligne
			if( $i==0 ){
				$log[$i] = "ligne :". $i . "aucun traitement entete";
			}

			if($i > 0){
				$logs[$i] = "ligne : ". $i ." ";

				$data = str_getcsv($v,';');

				// Si entreprise on verifie que la ligne raison social est remplie
				if( !empty($data[$centreprise['raison_social']]) ){

					// On construit l objet entreprise
					$ets = $this->constructObjetEntreprise($data, $centreprise);

					// On verifie si l ets est presente dans la base
					$indb = $this->etsindb($ets);

					// On traite le resultat indb
					if(count($indb) == 0 ){
						$logs[$i] .= " - Aucune correspondance trouve enregistrement dans l entreprise dans la base";
						$eid = $ets->save();
						$nb_ets++;
					}elseif( count($indb == 1) ){
						
						$logs[$i] .= " - Entreprise deja presente dans la base ID : ". $indb[0]['id'] . " - " . $indb['errors'];
						$logs[$i] .= " - Mise a jour des donnees ...";
						$ets = $this->updateEntrepriseData($ets, $indb[0]);
						// Enregistrement de la mise a jour
						$ets->save();
						$eid = $indb[0]['id'];
					}elseif( count($indb) > 1 ){
						$ets->isValid = 0;
						$eid = $ets->save();
						$logs[$i] .= " - Plusieurs correspondance possible. Entreprise enregistree mais non valide - ". $indb['errors'];
					}

				}// centreprise[raison_social]

				if(!empty($data[$ccontact['nom']])){

					// Traitement du contact
					$contact = $this->constructObjetContact($data, $ccontact);
					
					// On lui ajoute l id de l'entreprise
					$contact->entreprise_id = $eid;

					// On verifie s il est deja dans la base
					$indb = $this->ctcindb($contact);

					if($indb > 0){
						// Ajout d'une ligne de log
						$logs[$i] .= "\nContact deja existant dans la base";
					}else{
						// Ajout d'une ligne de log
						$logs[$i] .="\nAjout d'un contact";

						// Enregistrement dans la base
						$contact->save();
						$nb_ctc++;
					}	
				}else{
					$logs[$i] .= "\nPas de contact";
				}// endif !empty($row[9])

			}
			$i++;
		} // endforeach

		$this->registry->smarty->assign(array(
			'logs'		=>	$logs,
		));

		$this->saveImport($file, $nb_ets, $nb_ctc, $logs);

		return $this->registry->smarty->fetch(VIEW_PATH . 'import' . DS . 'step3.tpl');
	}

	private function constructObjetContact($record, $correspondance){
		$contact = new contact();

		if( isset($correspondance['nom']) && !empty($correspondance['nom']) )
			$contact->nom 			= utf8_encode($record[$correspondance['nom']]);

		if( isset($correspondance['prenom']) && !empty($correspondance['prenom']) )
			$contact->prenom 		= utf8_encode($record[$correspondance['prenom']]);

		if( isset($correspondance['telephone']) && !empty($record[$correspondance['telephone']]) )
			$contact->telephone 	= clearphonenumber($record[$correspondance['telephone']]);

		if( isset($correspondance['fax']) && !empty($correspondance['fax']) )
			$contact->fax 			= clearphonenumber($record[$correspondance['fax']]);

		if( isset($correspondance['mobile']) && !empty($correspondance['mobile']) )
			$contact->mobile		= clearphonenumber($record[$correspondance['mobile']]);

		if( isset($correspondance['email']) && !empty($correspondance['email']) )
			$contact->email 		= trim($record[$correspondance['email']]);

		$contact->date_add 		= date("Y-m-d H:i:s");
		$contact->date_edit 	= date("Y-m-d H:i:s");
		$contact->service_id 	= 0;
		$contact->poste_id 		= 0;

		// Recuperation du service
		if( isset($correspondance['service']) && !empty($correspondance['service']) ){
			$service = $this->registry->db->get_one('service', array('libelle =' => $record[$correspondance['service']] ));
			if(!empty($service)){
				$contact->service_id = $service['id'];
			}
		}
		

		// Recuperation du poste
		if( isset($correspondance['poste']) && !empty($correspondance['poste']) ){
			$poste = $this->registry->db->get_one('poste', array('libelle =' => $record[$correspondance['poste']] ));
			if(!empty($poste)){
				$contact->poste_id = $poste['id'];
			}
		}
		return $contact;

	}

	/**
	 * [updateEntrepriseData description]
	 * @param  [type] $entreprise [description]
	 * @param  [type] $olddata    [description]
	 * @return [type]             [description]
	 */
	private function updateEntrepriseData($entreprise, $olddata){
		$entreprise->id = $olddata['id'];
		return $entreprise;
	}

	private function constructObjetEntreprise($record, $correspondance){
		$ets = new entreprise();

		$ets->raison_social 	=	utf8_encode($record[$correspondance['raison_social']]);
		$ets->siret 			=	clearsiret(trim($record[$correspondance['siret']]));

		if( isset($correspondance['adresse1']) && !empty($correspondance['adresse1']) )
			$ets->adresse1 = utf8_encode($record[$correspondance['adresse1']]);

		if( isset($correspondance['adresse2']) && !empty($correspondance['adresse2']) )
			$ets->adresse2 = utf8_encode($record[$correspondance['adresse2']]);

		if( isset($correspondance['code_postal']) && !empty($correspondance['code_postal']) )
			$ets->code_postal =	$record[$correspondance['code_postal']];

		if( isset($correspondance['ville']) && !empty($correspondance['ville']) )
			$ets->ville =	utf8_encode($record[$correspondance['ville']]);	

		if( isset($correspondance['telephone']) && !empty($correspondance['telephone']) )
		$ets->telephone 		= 	clearphonenumber($record[$correspondance['telephone']]);

		if( isset($correspondance['fax']) && !empty($correspondance['fax']) )
			$ets->fax 				= 	clearphonenumber($record[$correspondance['fax']]);

		$ets->effectif 			=	!is_numeric($record[$correspondance['effectif']]) ? 0 : $record[$correspondance['effectif']];
		$ets->client 			= 	0;
		$ets->isValid 			= 	1;

		if( isset($correspondance['code_interne']) && !empty($correspondance['code_interne']) )
			$ets->code_interne 		=	$record[$correspondance['code_interne']];

		if(!empty($record[$correspondance['email']])){
			$ets->email = trim($record[$correspondance['email']]);
		}

		// APE
		$ets->ape_id = 0;
		if(isset($correspondance['ape']) && !empty($correspondance['ape'])){
			$ape = $this->registry->db->get_one('ape', array('code =' => trim($record[$correspondance['ape']])));
			if(!empty($ape)){
				$ets->ape_id = $ape['id'];
			}
		}
		
		return $ets;
	}

	/**
	*	Fonction que va verifier si l entreprise existe deja dans la base
	*
	*/
	private function etsindb($entreprise){
		
		// Verification par email
		if( !empty($entreprise->email) ){
			// On tente de recupere une entreprise avec la meme adresse email
			$tmp = $this->registry->db->get('entreprises', array('email =' => $entreprise->email));
			
			if(!empty($tmp)){
				$tmp['errors'] = 'Email identique : '. $entreprise->email;
				return $tmp;
			}
		}

		// Code siret
		if( !empty($entreprise->siret) ){
			// On tente de recupere une entreprise avec la meme adresse email
			$tmp = $this->registry->db->get('entreprises', array('siret =' => $entreprise->siret));
			
			if(!empty($tmp)){
				$tmp['errors'] = 'Siret identique : '. $entreprise->siret;
				return $tmp;
			}else{
				return;
			}
		}

		// Verification par code interne
		if( !empty($entreprise->code_interne) ){
			$result = $entreprise->getByCodeInterne($entreprise->code_interne);
			if(!empty($result)){
				$tmp['errors'] = 'Find by internal code';
				return $tmp;
			}
			return;
		}

		// Recherche sur la raison social de facon strict
		if( !empty($entreprise->raison_social) ){
			// On tente de recupere une entreprise avec la meme adresse email
			$tmp = $this->registry->db->get('entreprises', array('raison_social =' => $entreprise->raison_social));
			
			if(!empty($tmp)){
				$tmp['errors'] = 'Raison social identique : '. $entreprise->raison_social;
				return $tmp;
			}
		}

	}

	private function ctcindb($contact){

		// Si email vide on force l enregistrement
		if(empty($contact->email)){
			// Verification si contact existe deja par son nom/prenom/ets_id
			$result = $this->registry->db->count('contact',array('nom =' => $contact->nom, 'prenom =' => $contact->prenom, 'entreprise_id =' => $contact->entreprise_id));
			if($result > 0){
				return $result;
			}

			return;
		}

		$test = $this->registry->db->count('contact', array('email =' => $contact->email));

		if($test > 0){
			return $test;
		}

		return;
	} // end function ctcindb

	private function saveImport($file, $nb_ets, $nb_ctc, $logs){
		$data = array(
			'file'			=>	$file,
			'user_import'	=>	$_SERVER['REMOTE_USER'],
			'date_import'	=>	date("Y-m-d H:i:s"),
			'nb_ligne_ets'	=>	$nb_ets,
			'nb_ligne_ctc'	=>	$nb_ctc,
		);

		$this->registry->db->insert('import_file_history', $data);

		// Sauvegarde logs dans fichiers
		$handle = fopen(ROOT_PATH . 'log' . DS . 'import' . DS . $file . '.log', 'w+');
		foreach($logs as $k => $v){
			fwrite($handle, $v."\n");
		}
	}

	private function dejaimporter($fichier){
		$result = $this->registry->db->get_one('import_file_history', array('file =' => $fichier));
		if( !empty($result) ){
			return $result;
		}
		return false;
	}

} // end class