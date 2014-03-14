<?php
/*

                                                                               
                                     _,,                                   ,dW 
                                   ,iSMP                                  JIl; 
                                  sPT1Y'                                 JWS:' 
                                 sIl:l1                                 fWIl?  
                                dIi:Il;                                fW1"    
                               dIi:l:I'                               fWI:     
                              dIli:l:I;                              fWI:      
                            .dIli:I:S:S                     .       fWIl`      
                          ,sWSSIIIiISIIS w,_             .sMW     ,MWIl;       
                     _.,sWWW*"'*" , SWW' MWWMm mu,,._  .iSYISb, ,MM*SI!:       
                 _,s YMMWW'',sd,'MM WMMi "*MW* WWWMWMb MMS WWP`,MW' S1!`       
            _,os,'MMi YW' m,'WW; WWb`SWM Im,,  SIS ISW SISIP*  WSi  II!.       
         .osSMWMW,'WSi ',MMP SSb WSW ISII`SYYi III !Il lIi,ui:,*1:li:l1!       
      ,sSMMWWWSSSS,'SWbdWW*  *YSbiSS:'IlI 7llI il1: l! 'l:+'+l; `''+1i:1i      
   ,sYSMWMWY**"""'` 'WWSSIIiu,'**Y11';IIIb ?!li ?l:i,         `      `'l!:     
  sPITMWMW'`.M.wdWWb,'YIi `YT" ,u!1",ISIWWm,'+?+ `'+Ili                `'l:,   
  YIi1lTYfPSkyLinedI!i`I!" .,:!1"',iSWWMMMMMmm,                                
    "T1l1lI**"'`.2006? ',o?*'``  ```""**YSWMMMWMm,                             
         "*:iil1I!I!"` '                 ``"*YMMWWM,                           
               ii!                             '*YMWM,                         
               I'                                  "YM                         
                                                                               

@author : Romain Drouche
@email : w.shark@hotmail.fr
@url : http://www.sharkphp.com
@description : Fichier de fonction spécifique a l application
@package : require sharkphp
*/

define('MEG_VERSION', '1.0.20140130');
define('SHARK_VERSION', '1.0.0-beta');

define('LDAP_IGNORE', false);
define('IN_MAINTENANCE',false);


if(IN_MAINTENANCE){
	echo "En cour de maintenance ...";
	exit;
}

$ajax_query = false;

// Detection du type de requete
if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') $ajax_query = true;

// System de notification pour le bootstrap
$registry->load_web_lib('scrollup/css/themes/image.css','css');
$registry->load_web_lib('notifications/notifications.css','css');
$registry->load_web_lib('notifications/notifications.js','js','footer');
$registry->load_web_lib('pnotify/jquery.pnotify.min.js','js','footer');
$registry->load_web_lib('scrollup/jquery.scrollUp.min.js','js','footer');
$registry->load_web_lib('pnotify/jquery.pnotify.default.css','css');
$registry->load_web_lib('shark_tasks/shark_tasks.css','css');

/**
*	Traitement des parametres particulier de config
*/
$registry->config['ldap_server'] = unserialize($registry->config['ldap_server']);

require_once ROOT_PATH.'app'.DS.'local'.DS.'french.php';
$registry->smarty->assign('lang', $lang);
$registry->modules = getModules();
$registry->smarty->assign('modules', $registry->modules);


/**
 * Fonction autoload de l'application
 */
function AppAutoload(){
	$ClassList = array(
		// Controller

		// Manager

		// Model
		'ca'				=>	ROOT_PATH.'app'.DS.'model'.DS.'ca.php',
		'categorie'			=>	ROOT_PATH.'app'.DS.'model'.DS.'categorie.php',
		'campaign'			=>	ROOT_PATH.'app'.DS.'model'.DS.'campaign.php',
		'clog'				=>	ROOT_PATH.'app'.DS.'model'.DS.'contacts_log.php',
		'contacts'			=>	ROOT_PATH.'app'.DS.'model'.DS.'contacts.php',
		'contacts_suivi'	=>	ROOT_PATH.'app'.DS.'model'.DS.'contacts_suivi.php',
		'mailing'			=>	ROOT_PATH.'app'.DS.'model'.DS.'mailing.php',
		'mailing_actions'	=>	ROOT_PATH.'app'.DS.'model'.DS.'mailing_actions.php', 
		'mailing_type'		=>	ROOT_PATH.'app'.DS.'model'.DS.'mailing_type.php',
		'log'				=>	ROOT_PATH.'app'.DS.'model'.DS.'log.php', 
		'personne'			=>	ROOT_PATH.'app'.DS.'model'.DS.'personne.php',
        'rdv'               =>  ROOT_PATH.'app'.DS.'model'.DS.'rdv.php',
		'site'				=>	ROOT_PATH.'app'.DS.'model'.DS.'site.php',
		'societe'			=>	ROOT_PATH.'app'.DS.'model'.DS.'societe.php',
		'telephone'			=>	ROOT_PATH.'app'.DS.'model'.DS.'telephone.php',
		'tier'				=>	ROOT_PATH.'app'.DS.'model'.DS.'tier.php',
		'utilisateur'		=>	ROOT_PATH.'app'.DS.'model'.DS.'utilisateur.php',

	);

	foreach( $ClassList as $k => $v ){
		if( $class = $k ){
			require_once $v;
		}
	}	
}

spl_autoload_register('AppAutoload');

getGlobalDatas();
$registry->smarty->assign('modal_search', $registry->smarty->fetch(VIEW_PATH.'global'.DS.'modal_search.shark'));


/**
 * Traitement cas particulier ou TOKEN CRON EXISTE
 * Si le token cron existe on cree un session ADMIN pour l excution de la tache
 * qui sera detruit directement a la fin
 * On verifie egalement que le controller demander soit bien cronController
 */
if( isset($_GET['cron_token'])){
	$cron_token = $_GET['cron_token'];

	// Verification du token
	if($cron_token != $registry->config['cron_token']){
		exit('Error invalid REQUEST !');
	}

	// Creation d'une session admin
	$admin = $registry->db->get_one('user', array('identifiant =' => 'admin'));
	$user = new utilisateur($admin);
	$registry->session->create($user);
}

/**
 * Traitement cas particulier si TOKEN SSO EXISTE
 * Permet de créer un session directe depuis un lien envoye depuis un email
 * Cela evite a l utilisateur de s identifier a chaque fois.
 */
if(isset($_GET['sso_token']) && isset($_GET['uid'])){
	$token = $_GET['sso_token'];
	$uid = $_GET['uid'];

	$result = $registry->db->get_one('user', array('sso_link =' => '1', 'sso_link_token =' => $token, 'id =' => $uid));

	if(!empty($result)){
		$user = new utilisateur($result);
		$registry->session->create($user);
		$user->last_connexion = time();
		$user->save();

		$log = new log(array(
			'log' 		=> 	'Connexion au logiciel via SSO LINK.',
			'module'	=>	'connexion',
			'link_id'	=>	$user->id,
		));
	}else{
		$log = new log(array(
			'log' 		=> 	'Echec connexion SSO. Information du client : {token:'.$token.', uid:'.$uid.', ip:'.$_SERVER['REMOTE_ADDR'].'} ',
			'module'	=>	'connexion',
			'link_id'	=>	$uid,
		));
	}

	$log->save();
}

if($registry->config['ldap_use'] == 1 && LDAP_IGNORE == false){
	require ROOT_PATH . 'LibApp' . DS . 'adldap' . DS . 'adLDAP.php';
	$registry->adldap = new adLDAP(array(
		'base_dn'				=>	$registry->config['ldap_basedn'],		//'DC=domain,DC=local',
		'account_suffix'		=>	$registry->config['ldap_accsuffix'],	//@domain.local', 
		'domain_controllers' 	=>	$registry->config['ldap_server'],
		'admin_username'		=>	$registry->config['ldap_user'],
		'admin_password'		=>	$registry->config['ldap_password'],
	));	
}

//
// On traite l Auth SSO si activee
//
if( $registry->config['auth_sso_apache'] == 1 ){

	// Recuperation utilisateur
	$user_ldap = $registry->adldap->user()->info($_SERVER['REMOTE_USER'], array("*"));

	// Verification si l utilisateur est dans la base
	$result = $registry->db->count('user', array('identifiant =' => $user_ldap[0]['samaccountname'][0]));
	if($result == 0){
		$utilisateur = user_add_sso($user_ldap);
		$registry->session->create($utilisateur);
	}else{
		if($_SESSION['utilisateur']['id'] == 'Visiteur'){
			$user = $registry->db->get_one('user',array('identifiant =' => $user_ldap[0]['samaccountname'][0]));
			$utilisateur = new utilisateur($user);
			$registry->session->create($utilisateur);
		}
	}	
}

/**
 * Force l'affichage du formulaire de connexion si utilisateur est visiteur
 */
if($registry->session->check() == false || $_SESSION['utilisateur']['id'] == 'Visiteur') {
	$registry->router->controller = 'connexion';
}


// Code qui permet le suivi utilisateur dans l app
if( $_SESSION['utilisateur']['id'] != 'Visiteur' && $ajax_query === false){
	$session = array(
		'session_id' 	=> $_SESSION['session_id'], 
		'user_id' 		=> $_SESSION['utilisateur']['id'],
		'last_update' 	=> time(),
		'url'			=>	$_SERVER['REQUEST_URI'],
		'ip'			=>	$_SERVER['REMOTE_ADDR']
	);

	$registry->db->update('sessions', $session, array('session_id =' => $_SESSION['session_id']) );
}

/**
 * Si SSO est active, cette fonction va enregistrer les utilisateurs dans la base local du script
 * @param  array $user_ldap tableau contenant les infos LDAP
 * @return object $utilisateur entite utilisateur           
 */
function user_add_sso($user_ldap){
	global $registry;

	$utilisateur = new utilisateur();

	$utilisateur->identifiant = $user_ldap[0]['samaccountname'][0];

	if( isset($user_ldap[0]['mail'][0]) && !empty($user_ldap[0]['mail'][0]) )
		$utilisateur->email = $user_ldap[0]['mail'][0];

	$utilisateur->password = 'shark.123';
	$utilisateur->cryptPassword();
	$utilisateur->actif = 1;
	$utilisateur->register_on = time();
	$utilisateur->auth_type = 'sso';
	$utilisateur->save();


	if($registry->config['auth_by_group'] == 1 &&  !$registry->adldap->user()->inGroup($utilisateur->identifiant,$registry->config['group_ad'])){
		exit('Vous n\'avez les droits necessaire pour accèder à cette application');
	}

	if( $registry->adldap->user()->inGroup($utilisateur->identifiant,$registry->config['group_ad_adm'])){
		$utilisateur->isAdmin = '1';
	}else{
		$utilisateur->isAdmin = '0';
	}

	$utilisateur->save();

	return $utilisateur;
}

/**
 * Supprime les points ou les espaces dans les numeros de telephone
 * @param  string $number numero de telephone a nettoyer
 * @return string numero de telephone nettoye
 */
function clearphonenumber($number){
	return utf8_encode(str_replace(array('.','/',' '), '', $number));
}

/**
 * Supprime les points dans les numeros de SIRET
 * @param  string $siret chaine a nettoyer
 * @return string chaine nettoyee
 */
function clearsiret($siret){
	return str_replace('.', '', $siret);
}

/**
 * Recupere les donnees global de l application
 * et les envoies au moteur de template
 * @return void
 */
function getGlobalDatas(){
	global $registry;

	if(!$global_data = $registry->cache->get('global_data')){

		$result = $registry->db->select('DISTINCT(LEFT(e.zip_code,2)) as dpt')->from('contacts e')->having('dpt <>""')->order('dpt')->get();

		$dpt = array();

		foreach($result as $row){
			if(is_numeric($row['dpt'])){
				$dpt[]['dpt'] = $row['dpt'];
			}
		}

		$global_data = array(
			'categories'	=>	$registry->db->get('categorie', null, 'libelle'),
			'postes'		=>	$registry->db->get('poste', null, 'libelle'),
			'services'		=>	$registry->db->get('service', null, 'libelle'),
			'departements'	=>	$dpt,
			'ape'			=>	$registry->db->get('ape',null, 'code'),
		);
		$registry->cache->save(serialize($global_data));

	}else{
		$global_data = unserialize($global_data);
	}

	$registry->smarty->assign(array(
		'global_categories'		=>	$global_data['categories'],
		'global_postes'			=>	$global_data['postes'],
		'global_services'		=>	$global_data['services'],
		//'global_organismes'		=>	$global_data['organismes'],
		'global_departements'	=>	$global_data['departements'],
		'global_ape'			=>	$global_data['ape'],
	));

}

function DateMysqlToFR($date){
	$tmp = explode('-',$date);
	
	return $tmp[2].'/'.$tmp[1].'/'.$tmp[0];
}

function getACLs($uid = null, $return = false){
	global $registry;

	if(is_null($uid)){
		$uid = $_SESSION['utilisateur']['id'];
	}

	$acls = $registry->db->get('acl', array('user_id =' => $uid));

	if( !$return ){
		$_SESSION['acl'] = array();

		foreach($acls as $acl){
			$_SESSION['acl'][$acl['acl']] = 1;
		}
	}else{
		$result = array();
		foreach($acls as $acl){
			$result[$acl['acl']] = 1;
		}
		return $result;
	}
	
}

function getAcl($name){
	if( isset($_SESSION['acl'][$name]) ){
		return true;
	}

	return false;
}

function isAdmin(){
	return $_SESSION['utilisateur']['isAdmin'];
}

function getModules(){
	global $registry;
	$result = $registry->db->get('modules');
	$modules = array();

	foreach($result as $row){
		$modules[$row['name']] = $row;
	}

	return $modules;
}

function week_from_monday($date) {
    // Assuming $date is in format DD-MM-YYYY
   // list($day, $month, $year) = explode("-", $date);
   list($year, $month, $day) = explode('-', $date);

    // Get the weekday of the given date
    $wkday = date('l',mktime('0','0','0', $month, $day, $year));

    switch($wkday) {
        case 'Monday': $numDaysToMon = 0; break;
        case 'Tuesday': $numDaysToMon = 1; break;
        case 'Wednesday': $numDaysToMon = 2; break;
        case 'Thursday': $numDaysToMon = 3; break;
        case 'Friday': $numDaysToMon = 4; break;
        case 'Saturday': $numDaysToMon = 5; break;
        case 'Sunday': $numDaysToMon = 6; break;   
    }

    // Timestamp of the monday for that week
    $monday = mktime('0','0','0', $month, $day-$numDaysToMon, $year);

    $seconds_in_a_day = 86400;

    // Get date for 7 days from Monday (inclusive)
    for($i=0; $i<7; $i++)
    {
        $dates[$i] = date('Y-m-d',$monday+($seconds_in_a_day*$i));
    }

    return $dates;
}