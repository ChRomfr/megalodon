<!--
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
//-->
<!DOCTYPE html>
<html>
<head>
<title>Meg - Mailing Export Gl0b@L</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link rel="icon" type="image/png" href="{$config.url}themes/sharkphp/images/sharkphp.png" />
<!--[if IE]><link rel="shortcut icon" type="image/x-icon" href="{$config.url}themes/sharkphp/images/sharkphp.ico" /><![endif]-->
<link rel="stylesheet" href="{$config.url}themes/bootstrap/css/font-awesome.css" type="text/css" media="screen" />
<link rel="stylesheet" href="{$config.url}themes/bootstrap/css/bootstrap.css" type="text/css" media="screen" />
<link rel="stylesheet" href="{$config.url}themes/bootstrap/css/bootstrap-responsive.css" type="text/css" media="screen" />
<link rel="stylesheet" href="{$config.url}themes/dashboard/css/opa-icons.css" type="text/css" media="screen" />
<link rel="stylesheet" href="{$config.url}themes/dashboard/css/charisma-app.css" type="text/css" media="screen" />
<link rel="stylesheet" href="{$config.url}themes/dashboard/css/uniform.default.css" type="text/css" media="screen" />
<link rel="stylesheet" href="{$config.url}themes/dashboard/dashboard.css" type="text/css" media="screen" />
{if !empty($css_add)}
{foreach $css_add as $k => $v}
<link rel="stylesheet" href="{$config.url}web/css/{$v}" type="text/css" media="screen" />
{/foreach}
{foreach registry::$css_lib as $k => $v}
<link rel="stylesheet" href="{$config.url}web/lib/{$v}" type="text/css" media="screen" />
{/foreach}
{/if}
<script type="text/javascript" src="{$config.url}web/js/javascript.js"></script>
{if !empty($js_add)}
{foreach $js_add as $k => $v}
<script type="text/javascript" src="{$config.url}web/js/{$v}"></script>
{/foreach}
{/if}
{foreach registry::$js_lib as $k => $v}
<script type="text/javascript" src="{$config.url}web/lib/{$v}"></script>
{/foreach}
<script type="text/javascript" src="{$config.url}themes/bootstrap/js/bootstrap.min.js"></script>
<!--[if lt IE 9]>
<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
</head>
<body data-spy="scroll" data-target=".navbar">
{strip}
	{* Bar de navigation *}
	<div class="navbar navbar-inverse navbar-fixed-top">
		<div class="navbar-inner">
			<div class="container">
				<a class="btn btn-navbar" data-toggle="collapse" date-target=".nav-collapse">
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</a>
				<a class="brand" href="{$Helper->getLink("index")}" title="Retour au site">M&euro;g</a>
				<div class="nav-collapse">
					<ul class="nav">
					{*
						<li><a href="{$Helper->getLinkAdm("index")}"><i class="icon-home icon-white"></i></a></li>
					*}
					</ul>
					<ul class="nav pull-right">
						{if $smarty.session.utilisateur.id != 'Visiteur'}
						<li><a href="{$Helper->getLink("utilisateur")}" title=""><i class="icon-user icon-white"></i></a></li>
						<li><a href="{$Helper->getLink("connexion/logout")}" title=""><i class="icon-off icon-white"></i></a></li>
						{/if}
					</ul>
					
				</div>
			</div>
		</div>
	</div>{* /navbar *}
	{* START header *}
	<div id="header" style="padding-top:50px;"></div>
	{* END header *}

	{* START conteneur central *}
	<div class="container-fluid">
		<div class="row-fluid">
			<div class="span3 main-menu-span" style="padding-top:20px;">
				<div class="well nav-collapse sidebar-nav">
					<ul class="nav nav-tabs nav-stacked main-menu">
						<li class="nav-header hidden-tablet">Navigation</li>
						
						{*
						<li class="dropdown">
							<a class="dropdown-toggle" data-toggle="dropdown" href="#" title="">{$lang.Biens}&nbsp;<b class="caret"></b></a>
							<ul class="dropdown-menu">
                				<li><a href="{$Helper->getLinkAdm("bien")}" title="{$lang.Biens}">{$lang.Biens}</a></li>
                				<li><a href="{$Helper->getLinkAdm("bien/add")}" title="{$lang.Ajouter}">{$lang.Ajouter}</a></li>
                				<li><a href="{$Helper->getLinkAdm("categorie?c=bien")}" title="">{$lang.Categorie}</a></li>
                			</ul>
                		</li>
						*}
						<li><a href="{$Helper->getLink("contacts")}" title="Contacts">Contacts</a></li>
						{*<li><a href="{$Helper->getLink("entreprise")}" title="Liste des entreprises">Listes des entreprises</a></li>	
                		<li><a href="{$Helper->getLink("contact")}" title="Liste des contacts">Listes des contacts</a></li>	*}
						<li class="dropdown-submenu">
							<a href="#" data-toggle="dropdown">Mailing</a>
							<ul class="dropdown-menu">
								<li><a href="{$Helper->getLink("mailing")}" title="Liste des mailings">Liste</a></li>
								<li><a href="{$Helper->getLink("mailing/calendrier")}" title="Calendrier">Calendrier</a></li>
								<li><a href="{$Helper->getLink("mailing/add")}" title="Nouvelle demande">Demande</a></li>
							</ul>
						</li>
						
                		{if $smarty.session.utilisateur.isAdmin > 0}
						<li class="dropdown-submenu">
							<a href="#" data-toggle="dropdown">Gestion</a>
							<ul class="dropdown-menu">
								<li><a href="{$Helper->getLink("contacts/add")}" title="Ajouter un contact">Ajouter un contact</a></li>
                                                                <li><a href="{$Helper->getLink("configuration")}" title="Configuration">Configuration</a></li>
								{* <li><a href="{$Helper->getLink("entreprise/add")}" title="Ajouter une nouvelle entreprise">Ajouter une entreprise</a></li> *}
								<li><a href="{$Helper->getLink("categorie")}" title="Gestion des catégories">Catégories</a></li>
								<li><a href="{$Helper->getLink("organisme")}" title="">Organisme</a></li>
								<li><a href="{$Helper->getLink("import")}" title="Importer depuis un fichier CSV">Importer</a></li>
								<li><a href="{$Helper->getLink("importafpi")}" title="Importer depuis un fichier CSV">AFPI import</a></li>
								<li><a href="{$Helper->getLink("contacts/maintenance")}" title="Importer les entreprises et les contact dans contactS">Maintenance</a></li>
							</ul>
						</li>
						{/if}	

						{* Traitements des bundles *}
						{if isset($Bundle) && is_array($Bundle)}
							{foreach $Bundle as $Row}
								{if $Row.menu_admin == 1}
									{$Row.menu_admin_code}
								{/if}
							{/foreach}
						{/if}
						
						{$global_form_search}
						
										
					</ul>{* /nav *}
				</div>{* /well *}
			</div>{* /span3 *}
			<div class="span9">
				{$content}
			</div>{* /span9 *}
		</div>{* /row-fluid *}
	</div>{* /container-fluid *}
	{* END conteneur central *}
	
	{* START footer *}
	<footer class="footer_site">
		<div class="container">
			<div class="row-fluid">
				<div class="span8">
				</div><!-- /span8 -->
				<div class="span4">
				</div><!-- /span4 -->
			</div><!-- /row-fluid-->
		</div><!-- /container -->
		<div class="container">
			<div class="row-fluid">
				<div class="span8">	
				</div>
				<div class="span4">					
				</div>
			</div><!-- /row-fluid -->
			<hr/>
			<div class="fleft">
				
			</div>
			<div class="fright">
				Réaliser avec <a href="http://www.sharkphp.com" title="Another CMS/FRAMEWORK">Sharkphp <img src="{$config.url}web/images/sharkphp.png" alt="" style="width:20px;" /></a>
			</div>
			<div class="clear"></div>
		</div><!-- /container -->
	</footer>
	{* END footer *}
{/strip}
<script type="text/javascript">
<!--
$(".chzn-select").chosen();
$(document).ready(function() {
	$("a.fbimage").fancybox();
});
{if isset($FlashMessage) && !empty($FlashMessage)}
$(".breadcrumb").after('<div class="alert alert-info"><button type="button" class="close" data-dismiss="alert">&times;</button>{$FlashMessage}</div>');
{/if}
//-->
</script>
{if $smarty.const.IN_PRODUCTION === false}
<div class="pull-right">
	<a href="#dvlpModal" role="button" class="btn" data-toggle="modal">Infos dev</a>
</div>
{$infosdev}
{/if}
</body>
</html>