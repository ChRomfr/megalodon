<?php

class indexController extends Controller{

	public function indexAction(){	
		set_time_limit(0);
		
		// Lib googlemap
		if($_SESSION['utilisateur']['index_map_contacts'] == 1){
			$this->registry->load_web_lib('gmap3/gmap3.js','js');
			$this->registry->load_web_lib('gmap3/gmap3.css','css');
		}
		
		// Lib pour affichage calendrier
		$this->registry->load_web_lib('fullcalendar/fullcalendar.css','css');
        $this->registry->load_web_lib('fullcalendar/fullcalendar.min.js','js');

		$stats = array(
			'nb_ctcs'		=>	$this->registry->db->count('contacts', array('isDelete =' => 0)),
			'nb_sct'		=>	$this->registry->db->count('societe'),
			'nb_per'		=>	$this->registry->db->count('personne'),
			'nb_email'		=>	$this->registry->db->count('contacts', array('email != ' => '')),
		);
		
		if($_SESSION['utilisateur']['index_map_contacts'] == 1){
			$coord = $this->getcurrentcoordmap();
		}

		$this->registry->smarty->assign(array(
			'stats'				=>	$stats,
			'Markers'			=>	$_SESSION['utilisateur']['index_map_contacts'] == 1 ?json_encode($coord, JSON_NUMERIC_CHECK) : '',
		));
		
		return $this->registry->smarty->fetch(VIEW_PATH.'index'.DS.'index.tpl'); 
	}

	public function getcoordmapajax(){
	
	}
	
	private function getcurrentcoordmap(){

		if( !$Markers = $this->registry->cache->get('markers_index') ){
			$Markers = array();

			// Recuperation des entreprises deja geolocalisees
			/*$entreprises =	$this->registry->db
							->select('id,raison_social,adresse1, ville, code_postal,lat,lng, client, date_last_geoloc')
							->from('entreprises')
							->where_free('lat != "" AND lng != ""')
							->get();

			foreach($entreprises as $entreprise){
				$Markers[] = array('lat' => $entreprise['lat'], 'lng' => $entreprise['lng'], 'data' => array(
						'rs'				=>	$entreprise['raison_social'],
						'adresse'			=>	$entreprise['adresse1'],
						'code_postal'		=>	$entreprise['code_postal'],
						'ville'				=>	$entreprise['ville']
						),
						'options'	=> array(
							'icon'	=>	!empty($entreprise['client']) ? $this->registry->config['url'] . 'web/images/markers/pin1.png' : 	$this->registry->config['url'] . 'web/images/markers/pin2.png'
						),
				);
			}	*/
			$contacts =	$this->registry->db->select('DISTINCT(c.lat), c.*, s.raison_social, p.nom, p.prenom, date_last_geoloc, p.societe_id')
						->from('contacts c')
						->left_join('personne p','c.id = p.contact_id')
						->left_join('societe s','c.id = s.contact_id')
						->where_free('c.lng != "" AND c.lat != "" AND c.isDelete = 0')
						->get();

			foreach($contacts as $row){
				if( empty($row['societe_id'])){
					$Markers[] = array('lat' => $row['lat'], 'lng' => $row['lng'], 'data' => array(
						'rs'				=>	!empty($row['raison_social']) ? $row['raison_social'] : $row['prenom'] .' '. $row['nom'] ,
						'adresse'			=>	$row['adresse1'],
						'code_postal'		=>	$row['code_postal'],
						'ville'				=>	$row['ville']
						),
						'options'	=> array(
							'icon'	=>	!empty($row['client']) ? $this->registry->config['url'] . 'web/images/markers/pin1.png' : 	$this->registry->config['url'] . 'web/images/markers/pin2.png'
						),
					);
				}				
			}		

			$this->registry->cache->save(serialize($Markers));

			return $Markers;
		}else{
			return unserialize($Markers);
		}
	}


	private function getallcoordmap(){

		if( !$Markers = $this->registry->cache->get('markers_index') ){
			$Markers = array();
			require_once ROOT_PATH . 'kernel' . DS . 'lib' . DS . 'GoogleMapAPIv3.class.php';

			// Recuperation des adresses
			$villes = $this->registry->db->select('id,raison_social,adresse1, ville, code_postal,lat,lng, client, date_last_geoloc')->from('entreprises')->where_free('ville != "" AND code_postal != "" ')->get();

			$gmap = new GoogleMapApi();

			$date_now = new DateTime(date("Y-m-d H:i:s"));

			foreach($villes as $ville){
				$date_geoloc = new DateTime($ville['date_last_geoloc']);
				$interval = $date_now->diff($date_geoloc);
				$diff = intval($interval->format('%a'));
				
				if( is_null($ville['date_last_geoloc']) || (!empty($ville['lat']) && 10 < $diff) || (empty($ville['lat']) && 3 < $diff) ){				
					$coord = $gmap->geocoding($ville['adresse1'] .' '. $ville['code_postal'] . ' '. $ville['ville'] . ' FRANCE');
					if(is_numeric($coord[2])){
						$Markers[] = array(
							'lat' 	=> $coord[2], 
							'lng' 	=> $coord[3], 
							'data' 	=> array(
								'rs'				=>	$ville['raison_social'],
								'adresse'			=>	$ville['adresse1'],
								'code_postal'		=>	$ville['code_postal'],
								'ville'				=>	$ville['ville']
								),
							'options'	=> array(
								'icon'	=>	!empty($ville['client']) ? 'http://meg.intranet.domaineb/web/images/markers/pin1.png' : 	'http://meg.intranet.domaineb/web/images/markers/pin2.png'
							),
						);
						
						$this->registry->db->update('entreprises',array('lat' => $coord[2], 'lng' => $coord[3], 'date_last_geoloc' => date("Y-m-d H:i:s")), array('id =' => $ville['id']));
					}else{
						// On met quand meme a jour la date de la derniere geoloc
						$this->registry->db->update('entreprises',array('id' => $ville['id'], 'date_last_geoloc' => date("Y-m-d H:i:s")));
					}
					
				}elseif(!is_null($ville['lat'])){
					$Markers[] = array('lat' => $ville['lat'], 'lng' => $ville['lng'], 'data' => array(
							'rs'				=>	$ville['raison_social'],
							'adresse'			=>	$ville['adresse1'],
							'code_postal'		=>	$ville['code_postal'],
							'ville'				=>	$ville['ville']
							),
							'options'	=> array(
								'icon'	=>	!empty($ville['client']) ? 'http://meg.intranet.domaineb/web/images/markers/pin1.png' : 	'http://meg.intranet.domaineb/web/images/markers/pin2.png'
							),
					);
				}
				
			}

			$this->registry->cache->save(serialize($Markers));

			return $Markers;
		}else{
			return unserialize($Markers);
		}
	}

	public function importnafAction(){

		if(!isset($_GET['import'])){
			exit;
		}

		$lines = file(ROOT_PATH . 'web' . DS . 'upload' . DS . 'csv' . DS . 'naf.csv');

		foreach( $lines as  $k => $v){

			$data = str_getcsv($v,';');
			$naf = array();

			if(is_array($data)){
				$naf['code'] = str_replace('.', '', $data[0]);
				$naf['description'] = utf8_encode($data[1]);
				$this->registry->db->insert('ape', $naf);
			}
		}
	}
  
}


