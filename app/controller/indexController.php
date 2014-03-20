<?php

class indexController extends Controller{

	public function indexAction(){	
		set_time_limit(0);
		
		// Lib googlemap
		if($_SESSION['utilisateur']['index_map_contacts'] == 1){
			$this->registry->load_web_lib('gmap3/gmap3.js','js','footer');
			$this->registry->load_web_lib('gmap3/gmap3.css','css');
		}
		
		// Lib pour affichage calendrier
		$this->registry->load_web_lib('fullcalendar/fullcalendar.css','css');
        $this->registry->load_web_lib('fullcalendar/fullcalendar.min.js','js','footer');

        // JS PAGE
        $this->registry->load_web_lib('meg/index_index.js','js','footer');

		$stats = array(
			'nb_ctcs'		=>	$this->registry->db->count('contacts', array('isDelete =' => 0)),
			'nb_sct'		=>	$this->registry->db->count('contacts', array('type =' => 1)),
			'nb_per'		=>	$this->registry->db->count('contacts', array('type !=' => 1)),
			'nb_email'		=>	$this->registry->db->count('contacts', array('email != ' => '')),
		);

		// Recuperation des campagnes
		$campaigns = $this->registry->db->select('cp.*')->from('campaign cp')->left_join('campaign_assign_to cat','cp.id = cat.campaign_id')->where(array('cat.assign_to =' => $_SESSION['utilisateur']['id'], 'cp.date_start <=' => date('Y-m-d'), 'cp.date_end >=' => date('Y-m-d')))->group_by('cat.campaign_id')->get();
		if(!empty($campaigns)) $this->registry->smarty->assign('current_campaigns', json_encode($campaigns));

		// Recuperation des rdv à venir
		$meets = $this->registry->db->get('rdv', array('user_id =' => $_SESSION['utilisateur']['id'], 'date_rdv >=' => date('Y-m-d') .' 00:00:00'), 'date_rdv',10);
		if(!empty($meets)){
			$i=0;
			// Parcours des rendez vous pour recuperer les tiers
			$this->load_manager('contacts');
			foreach($meets as $row){
				if($row['tier_type'] == 'contacts'){
					$tier = $this->manager->contacts->getResumeById($row['tier_id']);
					if(empty($tier['prenom'])){
						$meets[$i]['participant'] = $tier['nom'];
					}else{
						$meets[$i]['participant'] = $tier['prenom'] . ' ' . $tier['nom'];
					}
				}
				$i++;
			}
			$this->registry->smarty->assign('meets', json_encode($meets));
		}
		
		// Recuperation des coordonnées pour la carte google
		if($_SESSION['utilisateur']['index_map_contacts'] == 1){ $coord = $this->getcurrentcoordmap();	}

		// Envoie des variables a smarty
		$this->registry->smarty->assign(array(
			'stats'		=>	$stats,
			'Markers'	=>	$_SESSION['utilisateur']['index_map_contacts'] == 1 ?json_encode($coord, JSON_NUMERIC_CHECK) : '',
		));
		
		// Generationde la page
		return $this->registry->smarty->fetch(VIEW_PATH.'index'.DS.'index.tpl'); 
	}
	
	private function getcurrentcoordmap(){

		if( !$Markers = $this->registry->cache->get('markers_index') ){
			$Markers = array();

			$contacts =	$this->registry->db->select('DISTINCT(c.lat), c.*, date_last_geoloc, p.societe_id')
						->from('contacts c')
						->where_free('c.lng != "" AND c.lat != "" AND c.isDelete = 0')
						->get();

			foreach($contacts as $row){
				if( empty($row['societe_id'])){
					$Markers[] = array('lat' => $row['lat'], 'lng' => $row['lng'], 'data' => array(
						'rs'				=>	!empty($row['raison_social']) ? $row['raison_social'] : $row['prenom'] .' '. $row['nom'] ,
						'adresse'			=>	$row['adress'],
						'code_postal'		=>	$row['zip_code'],
						'ville'				=>	$row['city']
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