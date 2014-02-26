<?php
/**
 *	MEG
 *	@author Romain DROUCHE
 *	@link http://www.sharkphp.com
 */
# Debut chrono page
$chrono1 = microtime(true);

# Definition des constantes
define('IN_VA', TRUE);
define('ROOT_PATH', str_replace('index.php','',__FILE__));
define('DS', DIRECTORY_SEPARATOR); 
define('APP_PATH', ROOT_PATH . 'app' . DS);
define('CACHE_PATH', ROOT_PATH . 'cache' . DS);
define('VIEW_PATH', ROOT_PATH . 'app' . DS . 'view' . DS);
define('CONTROLLER_PATH', ROOT_PATH . 'app' . DS . 'controller' . DS);
define('MODEL_PATH', ROOT_PATH . 'app' . DS . 'model' . DS);
define('LOG_ACCESS', false);	# Permet de logge tout les requetes HTTP et de le enregistre dans un fichier
define('ADM_MODEL_PATH',ROOT_PATH . 'app' . DS . 'model');

//Chemin des sessions
session_save_path(ROOT_PATH . 'cache' . DS . '_sessions');

require_once ROOT_PATH . 'kernel' . DS . 'core'. DS . 'core.php';
# START CODE SPECIFIQUE APP
define('USE_TABLE_CONFIG',true);

require ROOT_PATH . 'app.php';
# END CODE SPECIFIQUE APP

if(ACL_ADMIN){
	$registry->constructConstAdm();	
}

# Envoie du JS & CSS
$jquery_theme = 'overcast';
$registry->addJS('jquery-last.min.js');								# Jquery
$registry->addJS('jquery-migrate-1.1.0.min.js'); 					# Jquery BC
$registry->addJS('jquery-ui-last.custom.min.js');					# Jquery ui
$registry->addCSS($jquery_theme . '/jquery-ui-last.custom.min.css');
$registry->addJS('jquery.maskedinput.min.js');
$registry->addJS('mustache.js');
$registry->load_web_lib('tablesorter/jquery.tablesorter.min.js','js');
$registry->load_web_lib('tablesorter/jquery.tablesorter.pager.js','js');
$registry->load_web_lib('chosen/chosen.jquery.min.js','js');
$registry->load_web_lib('chosen/chosen.css','css');

# Difinition des chemins des applications par ordre d appel
$registry->router->setPath(array(ROOT_PATH . 'modules' . DS . 'controller' .DS,  ROOT_PATH . 'app' . DS . 'controller' .DS) );

# Execution de la requete et recuperation du resultat
$Content = $registry->router->loader();

$registry->smarty->assign('App',$registry);

// Envoie du formulaire de recherche au layout
$registry->smarty->assign('global_form_search', $registry->smarty->fetch(VIEW_PATH.'global'.DS.'form_search.shark'));
$registry->smarty->assign('nav_menu_left',$registry->smarty->fetch(VIEW_PATH.'global'.DS.'nav_menu_left.shark'));
$registry->smarty->assign('modal_global',$registry->smarty->fetch(VIEW_PATH.'global'.DS.'modal_global.shark'));

if( !$registry->HTTPRequest->getExists('nohtml') && !$registry->HTTPRequest->getExists('print') && $ajax_query == false ):

	if( IN_PRODUCTION === false ){
		require_once 'dvlp_mod.php';
	}
    
    //$BlocGauche = $registry->getBlok('left');
    
	# Affichage du resultat dans le template
	$registry->smarty->assign(array(
		'blokTop'		=>	''/*$registry->getBlok('top')*/,
		'blokGauche'	=>	''/*$BlocGauche*/,
		'blokFoot'		=>	''/*$registry->getBlok('foot')*/,
		'css_add'		=>	registry::$css,
		'js_add'		=>	registry::$js,		
		'registry'		=>	$registry,
		'content'		=>	$Content,
	));


	if( !IN_PRODUCTION ){
		$registry->smarty->assign('dvlp_tps_generation', round( microtime(true) - $chrono1, 6));
		$registry->smarty->assign('dvlp_memory', round(memory_get_usage() / (1024*1024),2));
		$registry->smarty->assign('dvlp_nb_queries', $db->num_queries);
	}

	// Generation de la page avec le layout du theme
	echo $registry->smarty->display(ROOT_PATH . 'themes' . DS . $config->config['theme'] . DS . 'layout.tpl');

elseif( $registry->HTTPRequest->getExists('print') && !$registry->HTTPRequest->getExists('nohtml')):
	# Affichage specifique pour les impressions
	$registry->smarty->assign('css_add', registry::$css);
	$registry->smarty->assign('js_add', registry::$js);
	$registry->smarty->assign('content', $Content);
	echo $registry->smarty->display(ROOT_PATH . 'themes' . DS . $config->config['theme'] . DS . 'layout_print.tpl');	
else:
	# Affichage du resultat seul sans code HTML AJAX
	echo $Content;
endif;