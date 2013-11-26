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

/**
*	Traitement des parametres particulier de config
*/
$registry->config['ldap_server'] = unserialize($registry->config['ldap_server']);


/**
 * Fonction autoload de l'application
 */
function AppAutoload(){
	$ClassList = array(
		// Controller

		// Manager

		// Model
		'categorie'		=>	ROOT_PATH.'app'.DS.'model'.DS.'categorie.php',
		'campaign'		=>	ROOT_PATH.'app'.DS.'model'.DS.'campaign.php',
		'clog'			=>	ROOT_PATH.'app'.DS.'model'.DS.'contacts_log.php',
		'contacts'		=>	ROOT_PATH.'app'.DS.'model'.DS.'contacts.php',
		'contacts_suivi'	=>	ROOT_PATH.'app'.DS.'model'.DS.'contacts_suivi.php',
		'mailing'		=>	ROOT_PATH . 'app' . DS . 'model' . DS . 'mailing.php',
		'mailing_type'	=>	ROOT_PATH . 'app' . DS . 'model' . DS . 'mailing_type.php', 
		'organisme'		=>	ROOT_PATH . 'app' . DS . 'model' . DS . 'organisme.php',
		'personne'		=>	ROOT_PATH.'app'.DS.'model'.DS.'personne.php',
		'societe'		=>	ROOT_PATH.'app'.DS.'model'.DS.'societe.php',
		'telephone'		=>	ROOT_PATH.'app'.DS.'model'.DS.'telephone.php',
		'utilisateur'	=>	ROOT_PATH . 'app' . DS . 'model' . DS . 'utilisateur.php',

	);

foreach( $ClassList as $k => $v ):
	if( $class = $k ):
		require_once $v;
	endif;
endforeach;
	
}
spl_autoload_register('AppAutoload');

getGlobalDatas();

if($registry->config['ldap_use'] == 1){
	require ROOT_PATH . 'LibApp' . DS . 'adldap' . DS . 'adLDAP.php';
	$registry->adldap = new adLDAP(array(
		'base_dn'				=>	$registry->config['ldap_basedn'],	//'DC=domain,DC=local',
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
	//require ROOT_PATH . 'LibApp' . DS . 'adldap' . DS . 'adLDAP.php';
	//$registry->adldap = new adLDAP();

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

if($registry->session->check() == false || $_SESSION['utilisateur']['id'] == 'Visiteur') {
	$registry->router->controller = 'connexion';
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

	//var_dump($registry->adldap->user()->inGroup($utilisateur->identifiant,$registry->config['group_ad_adm']));

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

		$result = $registry->db->select('DISTINCT(LEFT(e.code_postal,2)) as dpt')->from('contacts e')->having('dpt <>""')->order('dpt')->get();

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
			'organismes'	=>	$registry->db->get('organismes',null,'libelle'),
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
		'global_organismes'		=>	$global_data['organismes'],
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