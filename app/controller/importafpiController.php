<?php
set_time_limit(0);


class importafpiController extends Controller{

	protected $logs;

	protected $nb_lines_noemail = 0;

	protected $nb_email_present = 0;

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
				
	            return $this->registry->Helper->redirect($this->registry->Helper->getLink('importafpi/step2/' . $name));
			endif;

			
		endif;

		return $this->registry->smarty->fetch(VIEW_PATH . 'importafpi' . DS . 'index.tpl');
	}

	public function step2Action($file){
		
		$dir = ROOT_PATH . 'web' . DS . 'upload' . DS . 'csv' . DS;
		
		// Verification fichier présent sur le serveur
		if( !is_file($dir . $file . '.csv') ){
			return $this->registry->Helper->redirect($this->registry->Helper->getLink('importafpi'),5,'Le fichier demande n existe pas sur le serveur');
		} 

		// Verification dans la base si le fichier a deja ete importe
		$result = $this->registry->db->get_one('import_file_history', array('file =' => $file));
		if( !empty($result) ){
			return $this->registry->Helper->redirect($this->registry->Helper->getLink('importafpi'),5,'Ce fichier a deja ete importe dans la base');
		}

		// Recuperation des entetes du fichier
		$lines = file(ROOT_PATH . 'web' . DS . 'upload' . DS . 'csv' . DS . $file .'.csv');

		// Verification fichier non vide
		if(count($lines) == 0){
			return $this->registry->Helper->redirect($this->registry->Helper->getLink('importafpi'),5,'Le fichier envoye ne contient aucune ligne');
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
			'secteur'		=>	'Secteur',
			'ape'			=>	'APE',
			'meta'			=>	'Meta',
			'aero'			=>	'Aero',
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

		return $this->registry->smarty->fetch(VIEW_PATH . 'importafpi' . DS . 'step2.tpl');
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
			return $this->registry->smarty->fetch(VIEW_PATH . 'importafpi' . DS . 'dejaimporter.tpl'); 
		}

		$dir = ROOT_PATH . 'web' . DS . 'upload' . DS . 'csv' . DS;

		$lines = file(ROOT_PATH . 'web' . DS . 'upload' . DS . 'csv' . DS . $file .'.csv');

		$centreprise 	= $_POST['liaison_entreprise'];
		$ccontact 		= $_POST['liaison_contact'];

		$i=0;
		$nb_ets = 0;
		$nb_ctc = 0;
		$nb_lines_noemail = 0;
		$nb_email_present = 0;
		$nb_tel_present = 0;
		$nb_fax_present = 0;
		$nb_siret_present = 0;
		$nb_new_contacts = 0;
		$nb_update_contacts = 0;
		$nb_errors_contacts_double = 0;
		$nb_rs_present = 0;

		$rs_double = 0;

		if( isset($_POST['use_rs_double']) ){
			$rs_double = 1;
		}

		$verbose_log = 0;

		if( isset($_POST['logs_verbose']) ){
			$verbose_log = 1;
		}

		$email_valid	= 0;

		if( isset($_POST['email_valid']) ){
			$email_valid = 1;
		}

		$simulation = 0;

		if( isset($_POST['simulation']) ){
			$simulation = 1;
		}

		// boucles pour parcourir le fichier
		foreach($lines as $k => $v){

			// On ignore la 1ere ligne
			if( $i==0 ){
				$log[$i] = "ligne :". $i . "aucun traitement entete";
			}

			if($i > 0){
				$logs[$i] = "<strong>ligne : ". $i ."</strong><br/>";

				$data = str_getcsv($v,';');
				
				/**
				*	Update 05/11/2013
				*	Import directe dans nouvelle structure
				*/
				
				// 	Cas contacts entreprise, definie par le biais que l'on est une correspondance pour raison social
				if(	!empty($data[$centreprise['raison_social']])	){

					/**
					 * Traitement du cas ou le nom est la raison social
					 */
					if( isset($data[$ccontact['nom']]) &&  ($data[$ccontact['nom']] == $data[$centreprise['raison_social']]) ){
						if( !empty($data[$ccontact['prenom']]) ){
							$logs[$i] .= "<br/><strong>/!\ AUNCUN TRAITEMENT CAR PRENOM PRESENT /!\</strong>";
							goto line_is_person;
						}
					}

					$row_in_db = null;

					$logs[$i] .= '<br/>== Contact <strong>societe</strong>==<br/>RS : '. $data[$centreprise['raison_social']];

					// constructions des objets contacts et societe
					$contact = $this->ContructObjectContacts($data, $centreprise);
					$societe = $this->ContructObjectSociete($data, $centreprise);
					$contact->ctype = 'societe';

					// Traitement de l option email valide
					if($email_valid == 1 && empty($contact->email) ){
						$logs[$i] .= '<br/><span style="color:red">Ligne ignore, adresse email vide !</span>';
						goto nextboucle;
					}
					
					// Telephone
					$tel = $this->ConstructObjectTelephone($data, $centreprise, 'telephone');

					// Fax
					$fax = $this->ConstructObjectTelephone($data, $centreprise, 'fax');

					// Recherche dans la base si le contact est present
					if(!empty($societe->siret)){
						$result = $this->registry->db->get('societe', array('siret =' => $societe->siret));
						if(empty($result)){
							$logs[$i] .= '<br/>SIRET non trouve dans la base';
						}else{
							$nb_siret_present ++;
							$logs[$i] .= '<br/><span style="color:red"><strong>Error : SIRET deja dans la base</strong></span>';
							$row_in_db = $this->registry->db->get( 'contacts', array('id =' => $result[0]['contact_id']) );
						}
					}

					// Verification par la raison social
					if( empty($row_in_db) && !empty($societe->raison_social) ){
						$result = $this->registry->db->get('societe', array('raison_social =' => $societe->raison_social));
						if(empty($result)){
							$logs[$i] .= '<br/>RAISON SOCIAL non trouve dans la base';
						}else{
							
							$nb_rs_present ++;
							$logs[$i] .= '<br/><span style="color:orange">Warning : RAISON SOCIAL deja dans la base</span>';

							if($rs_double == 1){
								$row_in_db = $this->registry->db->get( 'contacts', array('id =' => $result[0]['contact_id']) );
							}
						}
					}
					 
					if(empty($row_in_db) && !empty($contact->email)){
						$result = $this->registry->db->get('contacts', array('email =' => $contact->email));
						if(empty($result)){
							$logs[$i] .= '<br/>Email non trouve dans la base';
						}else{
							$nb_email_present ++;
							$logs[$i] .= '<br/>Email deja dans la base ...';
							$row_in_db = $result;
						}
					}else{
						$logs[$i] .= '<br/>Contacts sans email';
						$nb_lines_noemail ++;
					}

					if(empty($row_in_db) && !empty($tel->telephone)){
						$result = $this->registry->db->get('telephones', array('telephone =' => $tel->telephone));
						if(empty($result)){
							$logs[$i] .= '<br/>Telephone non trouve dans la base';
						}else{
							$nb_tel_present ++;
							$logs[$i] .= '<br/>Telephone deja dans la base ... Verification et recuperation des infos';

							if(count($result) == 1){
								$logs[$i] .= '<br/>Une correspondance a ete trouve. #ID : <strong>'. $result[0]['contact_id'] .'</strong>';
								$row_in_db = $this->registry->db->get('contacts', array('id =' => $result[0]['contact_id']));
							}else{
								$logs[$i] .= '<br/><strong>Error : plusieurs contacts avec le même numero de telephone</strong>';
							}							
						}
					}

					if(empty($row_in_db) && !empty($fax->telephone)){
						$result = $this->registry->db->get('telephones', array('telephone =' => $fax->telephone));
						if(empty($result)){
							$logs[$i] .= '<br/>Fax non trouve dans la base';
						}else{
							$nb_fax_present ++;
							$logs[$i] .= '<br/>Fax deja dans la base<br/>Verification et recuperation des informatiosn dans la base';
							if(count($result) == 1){
								$logs[$i] .= '<br/>Une correspondance a ete trouve. #ID : <strong>'. $result[0]['contact_id'] .'</strong>';
								$row_in_db = $this->registry->db->get('contacts', array('id =' => $result[0]['contact_id']));
							}else{
								$logs[$i] .= '<br/><strong>Error : plusieurs contacts avec le même numero de FAX</strong>';
							}
							//$row_in_db = $result;
						}
					}
					
					if(!empty($row_in_db)){
						if(count($row_in_db) == 1){
							$logs[$i] .= '<br/>Mise a jour du contact avec les informations du nouveau fichier.';

							$nb_update_contacts ++;
							$row = $row_in_db[0];

							if($verbose_log == 1){
								$logs[$i] .= '<br/><br/>Informations deja presentes : '. print_r($row, true) .'<br/>';
								$logs[$i] .= '<br/>Nouvelle informations : '. print_r($contact, true) .'<br/>';
							}

							$contact->update_fields($row);

							if($verbose_log == 1){
								$logs[$i] .= '<br/>Nouvel enregistrement : '. print_r($contact, true) .'<br/>';
							}

							$contact->id = $row['id'];

							if($simulation == 0){
								$contact->save();
								$clog =  new clog(array('date_log' => date("Y-m-d H:i:s"), 'contact_id' => $contact->id, 'user_id' => $_SESSION['utilisateur']['id'], 'log' => 'Mise a jour du contact par le systeme d\'import AFPI'));
								$clog->save();
								
								// Enregistre du telephone
								if( !empty($tel->telephone) && $this->registry->db->count('telephones', array('telephone =' => $tel->telephone)) == 0 ){
									$tel->contact_id = $contact->id;
									$tel->save();
								}
								
								// Enregistrement fax
								if( !empty($fax->telephone) && $this->registry->db->count('telephones', array('telephone =' => $fax->telephone)) == 0 ){
									$fax->contact_id = $contact->id;
									$fax->save();
								}
							}

						}else{
							$logs[$i] .= '<br/><strong>/!\ PLUSIEURS CORRESPONDANCES IMPORT ET MISE A JOUR DE CE CONTACT ANNULE /!\</strong>';
							$nb_errors_contacts_double ++;
						}
					}else{
						$logs[$i] .= '<br/>Nouvel enregistrement societe';
						$nb_new_contacts ++;

						// Enregistrement du nouvel enregistrement
						if($simulation == 0){
							$cid = $contact->save();
							$clog =  new clog(array('date_log' => date("Y-m-d H:i:s"), 'contact_id' => $cid, 'user_id' => $_SESSION['utilisateur']['id'], 'log' => 'Ajout du contact dans la base par import AFPI'));
							$clog->save();

							$logs[$i] .= '<br/>Contact enregistre #<strong>'.$cid.'</strong>'. print_r($societe, true);

							$societe->contact_id = $cid;

							$societe->save();
							$clog =  new clog(array('date_log' => date("Y-m-d H:i:s"), 'contact_id' => $cid, 'user_id' => $_SESSION['utilisateur']['id'], 'log' => 'Ajout des infos societes dans la base par import AFPI'));
							$clog->save();

							if(!empty($tel->telephone)){
								$tel->contact_id = $cid;
								$tel->save();
								$clog =  new clog(array('date_log' => date("Y-m-d H:i:s"), 'contact_id' => $cid, 'user_id' => $_SESSION['utilisateur']['id'], 'log' => 'Ajout du telephone par import AFPI'));
								$clog->save();
							}

							if(!empty($fax->telephone)){
								$fax->contact_id = $cid;
								$fax->save();
								$clog =  new clog(array('date_log' => date("Y-m-d H:i:s"), 'contact_id' => $cid, 'user_id' => $_SESSION['utilisateur']['id'], 'log' => 'Ajout du fax par import AFPI'));
								$clog->save();
							}
							
							//////////////////////////////////
							// Cas specifique AFPI			//
							//////////////////////////////////
							
							// champ META
							if( !empty($data[$centreprise['meta']]) ){
								$this->registry->db->insert('contacts_categorie', array('contact_id' => $cid, 'categorie_id' => 5) );
							}
							
							// Traitement categorie
							if( !empty($data[$centreprise['categorie']]) ){
								$cat_search = $data[$centreprise['categorie']];
								$cat = $this->registry->db->get_one('categorie', array('libelle =' => $cat_search));
								if( !empty($cat) ){
									$this->registry->db->insert('contacts_categorie', array('contact_id' => $cid, 'categorie_id' => $cat['id']) );
								}
							}
							
						} //end simulation == 0						
					} // end else
				}

				// Traitement cas personnes
				line_is_person:
				if(!empty($data[$ccontact['nom']])){
					$row_in_db = null;
					$logs[$i] .= "<br/>== Contact <strong>Particulier</strong>==";

					/**
					 * Traitement du cas ou le nom est la raison social
					 */
					if( !empty($centreprise['raison_social']) &&  $data[$ccontact['nom']] == $data[$centreprise['raison_social']] ){
						if( empty($data[$ccontact['prenom']]) ){
							$logs[$i] .= '<br/><span style="color:blue">/!\ AUNCUN TRAITEMENT CAR AUCUN PRENOM POUR LA PERSONNE /!\</span>';
							goto nextboucle;
						}
					}

					// Contruction des object
					$contact = $this->ContructObjectContacts($data, $ccontact);
					$personne = $this->ConstructObjectPersonne($data, $ccontact);
					
					// Traitement de l option email valide
					if($email_valid == 1 && empty($contact->email) ){
						goto nextboucle;
					}

					// On test pour voir si contact est particulier ou rattaché à une entreprise
					// Pour etre certain de lie le contact à la bonne societe on test si un siret est
					// present si la ligne
					if( !empty($data[$centreprise['siret']] ) ){
						$contact->ctype = 'societe_contact';
						$logs[$i] .= '<div class="">Type de contact : societe_contact</div>';
						// Recuperation information societe pour liaison
						$societe = $this->registry->db->get_one('societe', array('siret =' => $data[$centreprise['siret']]));
						if( !empty($societe) ){						
							$personne->societe_id = $societe['id'];
						}else{
							$logs[$i] .= '<div class="alert alert-danger">/!\ Traitement du contact annulé, lié à une societe qui n\'existe pas dans la base</div>';
							goto  nextboucle;
						}
					}else{
						$contact->ctype = 'particulier';
						$logs[$i] .= '<div class="">Type de contact : particulier</div>';
					}
					
					// Traitement des numeros de telephones
					
					// Telephone
					$tel = $this->ConstructObjectTelephone($data, $centreprise, 'telephone');

					// Fax
					$fax = $this->ConstructObjectTelephone($data, $centreprise, 'fax');
					
					// Mobile 
					$port = $this->ConstructObjectTelephone($data, $centreprise, 'portable');

					// On recherche les doublons dans la base, on se base sur email / portable / telephone
					// Le telephone n est pas bloquant si la personne a donnee le standard
					if(empty($row_in_db) && !empty($contact->email)){
						$result = $this->registry->db->get('contacts', array('email =' => $contact->email));
						if(empty($result)){
							$logs[$i] .= '<br/>Email non trouve dans la base';
						}else{
							$nb_email_present ++;
							$logs[$i] .= '<br/>Email deja dans la base ...';
							$row_in_db = $result;
						}
					}else{
						$logs[$i] .= '<br/>Contacts sans email';
						$nb_lines_noemail ++;
					}

					if(empty($row_in_db) && !empty($tel->telephone)){
						$result = $this->registry->db->get('telephones', array('telephone =' => $tel->telephone));
						if(empty($result)){
							$logs[$i] .= '<br/>Telephone non trouve dans la base';
						}else{
							$nb_tel_present ++;
							$logs[$i] .= '<br/>Telephone deja dans la base ... Verification et recuperation des infos';

							if(count($result) == 1){
								$logs[$i] .= '<br/>Une correspondance a ete trouve. #ID : <strong>'. $result[0]['contact_id'] .'</strong>';
								$row_in_db = $this->registry->db->get('contacts', array('id =' => $result[0]['contact_id']));
							}else{
								$logs[$i] .= '<br/><strong>Error : plusieurs contacts avec le même numero de telephone</strong>';
							}							
						}
					}

					if(empty($row_in_db) && !empty($fax->telephone)){
						$result = $this->registry->db->get('telephones', array('telephone =' => $fax->telephone));
						if(empty($result)){
							$logs[$i] .= '<br/>Fax non trouve dans la base';
						}else{
							$nb_fax_present ++;
							$logs[$i] .= '<br/>Fax deja dans la base<br/>Verification et recuperation des informatiosn dans la base';
							if(count($result) == 1){
								$logs[$i] .= '<br/>Une correspondance a ete trouve. #ID : <strong>'. $result[0]['contact_id'] .'</strong>';
								$row_in_db = $this->registry->db->get('contacts', array('id =' => $result[0]['contact_id']));
							}else{
								$logs[$i] .= '<br/><strong>Error : plusieurs contacts avec le même numero de FAX</strong>';
							}
						}
					}
					
					// Doublons par le portable
					if(empty($row_in_db) && !empty($port->telephone)){
						$result = $this->registry->db->get('telephones', array('telephone =' => $port->telephone));
						if(empty($result)){
							$logs[$i] .= '<br/>Portable non trouve dans la base';
						}else{
							$nb_fax_present ++;
							$logs[$i] .= '<br/>Portable deja dans la base<br/>Verification et recuperation des informatiosn dans la base';
							if(count($result) == 1){
								$logs[$i] .= '<br/>Une correspondance a ete trouve. #ID : <strong>'. $result[0]['contact_id'] .'</strong>';
								$row_in_db = $this->registry->db->get('contacts', array('id =' => $result[0]['contact_id']));
							}else{
								$logs[$i] .= '<br/><strong>Error : plusieurs contacts avec le même numero de Portable</strong>';
							}
						}
					}
					
					//
					// Traitement dans la base
					//
					if(!empty($row_in_db)){
						if(count($row_in_db) == 1){
							$logs[$i] .= '<br/>Mise a jour du contact avec les informations du nouveau fichier.';

							$nb_update_contacts ++;
							$row = $row_in_db[0];

							if($verbose_log == 1){
								$logs[$i] .= '<br/><br/>Informations deja presentes : '. print_r($row, true) .'<br/>';
								$logs[$i] .= '<br/>Nouvelle informations : '. print_r($contact, true) .'<br/>';
							}

							$contact->update_fields($row);

							if($verbose_log == 1){
								$logs[$i] .= '<br/>Nouvel enregistrement : '. print_r($contact, true) .'<br/>';
								$logs[$i] .= '<br/>Objet personne : <pre>'. print_r($personne, true) .'<br/>';
							}

							$contact->id = $row['id'];

							if($simulation == 0){
								$contact->save();
								$clog =  new clog(array('date_log' => date("Y-m-d H:i:s"), 'contact_id' => $contact->id, 'user_id' => $_SESSION['utilisateur']['id'], 'log' => 'Mise a jour du contact par le systeme d\'import AFPI'));
								$clog->save();
								
								if( !empty($tel->telephone) && $this->registry->db->count('telephones', array('telephone =' => $tel->telephone)) == 0 ){
									$tel->save();
								}
								
								if( !empty($fax->telephone) && $this->registry->db->count('telephones', array('telephone =' => $fax->telephone)) == 0 ){
									$fax->save();
								}
								
								if( !empty($port->telephone) && $this->registry->db->count('telephones', array('telephone =' => $port->telephone)) == 0 ){
									$port->save();
								}								
							}
						}else{
							$logs[$i] .= '<br/><strong>/!\ PLUSIEURS CORRESPONDANCES IMPORT ET MISE A JOUR DE CE CONTACT ANNULE /!\</strong>';
							$nb_errors_contacts_double ++;
						}
					}else{
						$logs[$i] .= '<br/>Nouvel enregistrement personne';
						$nb_new_contacts ++;

						// Enregistrement du nouvel enregistrement
						if($simulation == 0){
							$cid = $contact->save();
							$clog =  new clog(array('date_log' => date("Y-m-d H:i:s"), 'contact_id' => $cid, 'user_id' => $_SESSION['utilisateur']['id'], 'log' => 'Ajout du contact dans la base par import AFPI'));
							$clog->save();

							$logs[$i] .= '<br/>Contact enregistre #<strong>'.$cid.'</strong><pre>'. print_r($personne, true) .'</pre>';

							$personne->contact_id = $cid;

							$personne->save();
							$clog =  new clog(array('date_log' => date("Y-m-d H:i:s"), 'contact_id' => $cid, 'user_id' => $_SESSION['utilisateur']['id'], 'log' => 'Ajout des infos de la personne dans la base par import AFPI'));
							$clog->save();

							if(!empty($tel->telephone)){
								$tel->contact_id = $cid;
								$tel->save();
								$clog =  new clog(array('date_log' => date("Y-m-d H:i:s"), 'contact_id' => $cid, 'user_id' => $_SESSION['utilisateur']['id'], 'log' => 'Ajout du telephone par import AFPI'));
								$clog->save();
							}

							if(!empty($fax->telephone)){
								$fax->contact_id = $cid;
								$fax->save();
								$clog =  new clog(array('date_log' => date("Y-m-d H:i:s"), 'contact_id' => $cid, 'user_id' => $_SESSION['utilisateur']['id'], 'log' => 'Ajout du fax par import AFPI'));
								$clog->save();
							}
							
							if(!empty($port->telephone)){
								$port->contact_id = $cid;
								$port->save();
								$clog =  new clog(array('date_log' => date("Y-m-d H:i:s"), 'contact_id' => $cid, 'user_id' => $_SESSION['utilisateur']['id'], 'log' => 'Ajout du portable par import AFPI'));
								$clog->save();
							}
						} //end simulation == 0		
						
					} // end else

				}// fin personne				

			}// end line 0 ** entete **

			nextboucle:

			$i++;

		} // endforeach

		$this->registry->smarty->assign(array(
			'logs'						=>	$logs,
			'nb_lines_noemail'			=>	$nb_lines_noemail,
			'nb_email_present'			=>	$nb_email_present,
			'nb_lines'					=>	$i,
			'nb_tel_present'			=>	$nb_tel_present,
			'nb_fax_present'			=>	$nb_fax_present,
			'nb_siret_present'			=>	$nb_siret_present,
			'nb_errors_contacts_double'	=>	$nb_errors_contacts_double,
			'nb_new_contacts'			=>	$nb_new_contacts,
			'nb_update_contacts'		=>	$nb_update_contacts,
		));
		
		if($simulation == 0){
			$this->saveImport($file, $nb_ets, $nb_ctc, $logs);
		}

		return $this->registry->smarty->fetch(VIEW_PATH . 'importafpi' . DS . 'step3.tpl');
	}
	
	/**
	*	@param array $record : Tableau contenant les données
	*	@param array $correspondance : Table contenant les liens CSV to MYSQL
	*	@return object
	*/
	private function ContructObjectContacts($record, $correspondance){
		$row = new contacts();		
				
		if( isset($correspondance['client']) && !empty($record[$correspondance['client']]) ){
			$row->client = 1;
		}else{
			$row->client = 0;
		}
		
		if( isset($correspondance['adresse1']) && !empty($correspondance['adresse1']) && !empty($record[$correspondance['adresse1']]) ){
			$row->adresse1  = utf8_encode($record[$correspondance['adresse1']]);
		}
		
		if( isset($correspondance['adresse2']) && !empty($correspondance['adresse2']) && !empty($record[$correspondance['adresse2']]) ){
			$row->adresse2  = utf8_encode($record[$correspondance['adresse2']]);			
		}
		
		if( isset($correspondance['code_postal']) && !empty($correspondance['code_potal']) ){
			$row->code_postal  = utf8_encode($record[$correspondance['code_postal']]);
		}
		
		if( isset($correspondance['ville']) && !empty($correspondance['ville']) && !empty($record[$correspondance['ville']])  ){
			$row->ville  = utf8_encode($record[$correspondance['ville']]);
		}
		
		if( isset($correspondance['code_interne']) && !empty($correspondance['code_interne']) ){
			$row->code_interne = utf8_encode($record[$correspondance['code_interne']]);
		}

		// Verification email. Check de la synthaxe directe.
		if( isset($correspondance['email']) && !empty($correspondance['email']) && !empty($record[$correspondance['email']]) ){
			$row->email = trim($record[$correspondance['email']]);

			// Verification email valide
			if( VerifieAdresseMail($row->email) == false ){
				$row->email = '';
			}
		}

		//if( isset($correspondance['']) && !empty($correspondance['']) ){}
		
		$row->lat = '';
		$row->lng = '';
		$row->pays = 'France';
		$row->isDelete = 0;
		$row->valid = 1;
		$row->type = 1;

		return $row;
	}

	private function ContructObjectSociete($record, $correspondance){

		$row = new societe();
		
		if( isset($correspondance['raison_social']) /*&& !empty($correspondance['raison_social']) */){
			$row->raison_social = trim($record[$correspondance['raison_social']]);
			
			$row->raison_social = mb_check_encoding($row->raison_social, 'UTF-8') ? $row->raison_social : utf8_encode($row->raison_social);
			$row->raison_social = mb_convert_encoding($row->raison_social,'UTF-8','UTF-8');
			
			if(mb_detect_encoding($row->raison_social) == 'UTF-8'){
				$row->raison_social = utf8_decode($row->raison_social);
			}
		}	

		if( isset($correspondance['effectif']) && !empty($correspondance['effectif']) && !empty($record[$correspondance['effectif']]) ){
			$row->effectif = trim(utf8_decode($record[$correspondance['effectif']]));
		}

		// Traitement du code APE
		$row->ape_id = 0;
		if( isset($correspondance['ape']) && !empty($correspondance['ape']) && !empty($record[$correspondance['ape']]) ){
			$ape = $this->registry->db->get_one('ape', array('code =' => trim($record[$correspondance['ape']])));
			if(!empty($ape)){
				$row->ape_id = $ape['id'];
			}
		}

		// Traitement du siret
		if( isset($correspondance['siret']) && !empty($correspondance['siret']) && !empty($record[$correspondance['siret']]) ){
			$row->siret =	clearsiret(trim($record[$correspondance['siret']]));			
		}
		
		return $row;

	}

	private function ConstructObjectPersonne($record, $correspondance){
		$row = new personne();

		if( isset($correspondance['nom'])){
			$row->nom = utf8_encode($record[$correspondance['nom']]);
		}
			

		if( isset($correspondance['prenom']) && !empty($correspondance['prenom']) ){
			$row->prenom = utf8_encode($record[$correspondance['prenom']]);
		}
			
		return $row;
	}

	private function ConstructObjectTelephone($record, $correspondance, $type){
		$row = new telephone();

		if( isset($correspondance[$type]) && !empty($record[$correspondance[$type]]) ){
			$row->telephone = clearphonenumber($record[$correspondance[$type]]);

			switch ($type) {
				case 'telephone':
					$row->type = 2;
					break;
				
				case 'portable':
					$row->type = 4;
					break;

				case 'fax':
					$row->type = 5;
					break;
			}

		}

		return $row;
			
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
		$contact->migration		= 0;

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

	private function constructObjetEntreprise($record, $correspondance){
		$ets = new entreprise();

		$ets->raison_social 	=	utf8_encode($record[$correspondance['raison_social']]);
		$ets->siret 			=	clearsiret(trim($record[$correspondance['siret']]));

		if( isset($correspondance['adresse1']) && !empty($correspondance['adresse1']) )
			$ets->adresse1 = utf8_encode($record[$correspondance['adresse1']]);
		
		if( isset($correspondance['adresse2']) && !empty($correspondance['adresse2']) )
			$ets->adresse2 = utf8_encode($record[$correspondance['adresse2']]);
		
		$ets->code_postal		=	$record[$correspondance['code_postal']];
		$ets->ville 			=	utf8_encode($record[$correspondance['ville']]);	
		
		if( isset($correspondance['telephone']) && !empty($correspondance['telephone']) )
			$ets->telephone 		= 	clearphonenumber($record[$correspondance['telephone']]);

		if( isset($correspondance['fax']) && !empty($correspondance['fax']) )
			$ets->fax 				= 	clearphonenumber($record[$correspondance['fax']]);
		
		if( isset($correspondance['effectif']) && !empty($correspondance['effectif']) )
			$ets->effectif 			=	!is_numeric($record[$correspondance['effectif']]) ? 0 : $record[$correspondance['effectif']];
		
		$ets->client 			= 	0;
		$ets->isValid 			= 	1;
		$ets->migration 		= 	0;
		$ets->mother 			=	0;

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

	private function updateEntrepriseData($entreprise, $olddata){
		$entreprise->id = $olddata['id'];
		return $entreprise;
	}

	private function email_in_db(){

		if(empty($row_in_db) && !empty($contact->email)){
			$result = $this->registry->db->get('contacts', array('email =' => $contact->email));
			
			if(empty($result)){
				$this->logs[$i] .= '<br/>Email non trouve dans la base';
			}else{
				$this->nb_email_present ++;
				$this->logs[$i] .= '<br/>Email deja dans la base ...';
				$row_in_db = $result;
			}
			
		}else{
			$this->logs[$i] .= '<br/>Contacts sans email';
			$this->nb_lines_noemail ++;
		}

	}

	private function tel_in_db(){

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
			'user_import'	=>	$_SESSION['utilisateur']['identifiant'],
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