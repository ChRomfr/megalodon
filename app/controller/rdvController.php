<?php
define('RDV_PER_PAGE', 10);

class rdvController extends Controller{
	
    public function myAction(){
    	$uid = $_SESSION['utilisateur']['id'];
    	$rdv = new rdv();

        $nb_rdv = $rdv->countByUserId($uid);

        $Pagination = new Zebra_Pagination();
        $Pagination->records($nb_rdv);
        $Pagination->records_per_page(RDV_PER_PAGE);
        $this->registry->smarty->assign('Pagination',$Pagination);

    	$this->registry->smarty->assign('meet',$rdv->getByUserId($uid, RDV_PER_PAGE, getOffset(RDV_PER_PAGE)));

    	return $this->registry->smarty->fetch(VIEW_PATH.'rdv'.DS.'my.meg');
    }

    public function mycalendarAction(){

        // Lib pour affichage calendrier
        $this->registry->load_web_lib('fullcalendar/fullcalendar.css','css');
        $this->registry->load_web_lib('fullcalendar/fullcalendar_year.js','js','footer');

        return $this->registry->smarty->fetch(VIEW_PATH.'rdv'.DS.'mycalendar.meg');
    }

    /**
     * Utilisation du cache pour cette page pour eviter de faire trop de requete
     * 
     * @return [type] [description]
     */
    public function get_mycalendarAction(){
        $uid = $_SESSION['utilisateur']['id'];

        if(!$meets = $this->registry->cache->get('data_for_mycalendar_u_'. $uid)){       
            $rdv = new rdv();

            $meets = array();
            $results = $rdv->getByUserId($uid, 1000, 0);

            $i=0;
            foreach($results as $row){
                $meets[$i] = array(
                    'id'            =>  $row['id'], 
                    'title'         =>  $row['date_rdv'],  
                    'start'         =>  $row['date_rdv'],
                    'allday'        =>  false,
                );

                if($row['tier_type'] == 'contacts'){
                    $this->load_manager('contacts');
                    $tier = $this->manager->contacts->getById($row['tier_id']);
                    if(!empty($tier['raison_social'])){
                      $meets[$i]['title'] .= ' '. $tier['raison_social'];  
                    }else{
                        $meets[$i]['titel'] .= ' '. $tier['prenom'] . ' ' . $tier['nom'];
                    }

                    $meets[$i]['title'] .= ' '. $row['description'];
                }

                $i++;
            }
            $this->registry->cache->save(serialize($meets));
        }else{
            $meets = unserialize($meets);
        }

        return json_encode($meets);
    }

    public function get_detailAction($rid){
    	$rdv = new rdv();
    	$rdv->get($rid);

    	if($rdv->tier_type == 'contacts'){
    		$this->load_manager('contacts');
    		$tier = $this->manager->contacts->getById($rdv->tier_id);
    	}

        if($rdv->add_by != $_SESSION['utilisateur']['id']){
            $by = $this->registry->smarty->assign('by', $this->registry->db->get_one('user', array('id =' => $rdv->add_by)));
        }

    	$this->registry->smarty->assign(array(
    		'rdv'	=>	$rdv,
    		'tier'	=>	$tier,
    	));

    	return $this->registry->smarty->fetch(VIEW_PATH.'rdv'.DS.'get_detail.meg');
    }

    public function ajax_get_infos_tierAction($rid){
    	$rdv = new rdv();

    	$rdv->get($rid);

    	if($rdv->tier_type == 'contacts'){
    		$this->load_manager('contacts');
    		$result = $this->manager->contacts->getById($rdv->tier_id);
    		return json_encode($result);
    	}
    }

    public function ajax_save_statutAction($rid){

        // Recuperation du statut
        $statut = $this->registry->Http->get('rdv_statut');

        // Recuperation de l objet statut
        $rdv = new rdv();
        $rdv->get($rid);
        $rdv->setStatut($statut);

        // Enreistrement
        $rdv->save();

        return 'ok';        
    }

    public function ajax_save_rapportAction($rid){
        // Recuperation du statut
        $rapport = $this->registry->Http->get('rapport');

        // Recuperation de l objet statut
        $rdv = new rdv();
        $rdv->get($rid);
        $rdv->setRapport($rapport);

        // Enreistrement
        $rdv->save();

        return 'ok'; 
    }

	public function get_formAction(){

	   if( isset($_GET['tier_type']) && $_GET['tier_type'] == 'contacts'){
	   		$row = new contacts();
	   		$row->get($_GET['tier_id']);
	   		$this->registry->smarty->assign('tier', $row);
	   }

		return $this->registry->smarty->fetch(VIEW_PATH.'rdv'.DS.'form.meg');
	}
    
    
    public function addAction(){
       	if(!is_null($this->registry->Http->post('rdv'))){

			$rdv = new rdv($this->registry->Http->post('rdv'));
			$rdv->add_by = $_SESSION['utilisateur']['id'];
			$rdv->add_on = date('Y-m-d H:i:s');
			$rdv->statut = 0;
			$rid = $rdv->save();

			print_r($rdv);

			exit;
		} 
    }
}