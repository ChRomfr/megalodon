<?php
set_time_limit(0);


class liveController extends Controller {

	public function indexAction($site_id){
		// Recuperation informations site
		$site = $this->registry->db->get_one('serveur_proxy', array('id =' => $site_id) );
		
		// Assign a smarty
		$this->registry->smarty->assign('site',$site);
		
		// Generation de la page
		return $this->registry->smarty->fetch(VIEW_PATH . 'live' . DS . 'index.tpl');
	}
	
	public function getfileAction($site_id){
		
		$destination_file = ROOT_PATH . 'web' . DS . 'upload' . DS . 'tmp' . DS . 'access_log_tmp.log';
		
		// Verification si deja des logs
		if( is_file($destination_file)){
			@unlink($destination_file);
		}
		
		$site = $this->registry->db->get_one('serveur_proxy', array('id =' => $site_id) );
		
		//var_dump($site);

		$connection = ssh2_connect($site['serveur_ip'], $site['serveur_port']);
		ssh2_auth_password($connection, $site['serveur_user'], $site['serveur_password']);
		ssh2_exec($connection, "sudo chmod 0777 ". $site['dir_log_squid']);
		ssh2_scp_recv($connection, $site['dir_log_squid'], $destination_file);

		return "ok";
	}

	public function constructTableTmpAction(){

		$destination_file = ROOT_PATH . 'web' . DS . 'upload' . DS . 'tmp' . DS . 'access_log_tmp.log';

		$this->registry->db->query("TRUNCATE access_log_tmp");

		$handle = fopen($destination_file,"r");

		while (!feof($handle)) {
			$buffer=fgets($handle, 4096);
			
			$log = squid_log_parser($buffer);
			
			$this->registry->db->insert('access_log_tmp', $log);
		}

		return "ok";
	}
	
	public function statsAction(){
		
		$stats = $this->registry->db
					->select('SUM(bytes) as cumul, count(id) as hits, ip')
					->from('access_log_tmp')
					->group_by('ip')
					->order('cumul DESC')
					->get();
					
		// On boucles sur le cumul pour formater les donnees
		$i=0;
		foreach($stats as $row){
			$stats[$i]['cumul_format'] = formatBytes($row['cumul']);
			$i++;
		}
		
		// Envoie a smarty
		$this->registry->smarty->assign('stats', $stats);
		
		// Generation de l HTML
		$html = $this->registry->smarty->fetch(VIEW_PATH . 'live' . DS . 'stats.tpl');
		
		return $html;
	}

	public function detailbyipAction(){

		$ip = $this->registry->Http->get('ip');

		$datas =	$this->registry->db 
					->select('*')
					->from('access_log_tmp')
					->where(array('ip =' => $ip))
					->order('time DESC')
					->get();

		// Parcours des logs pour formatage
		foreach($datas as $row){
			$tmp_url = parse_url($row['url']);

			if( isset($logs[$tmp_url['host']]) ){
				$logs[$tmp_url['host']]['hits']++;
				$logs[$tmp_url['host']]['bytes'] = $logs[$tmp_url['host']]['bytes'] + $row['bytes'];
			}else{
				$logs[$tmp_url['host']]['url'] = $tmp_url['host'];
				$logs[$tmp_url['host']]['hits'] = 1;
				$logs[$tmp_url['host']]['bytes'] = $row['bytes'];
			}
		}
		

		// Envoie a smarty
		$this->registry->smarty->assign('logs', $logs);
		
		// Generation de l HTML
		$html = $this->registry->smarty->fetch(VIEW_PATH . 'live' . DS . 'detailbyip.tpl');
		
		return $html;
	}

}