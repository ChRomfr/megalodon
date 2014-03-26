<!DOCTYPE html>
<html lang="fr">
<head>
<title>Meg - Mailing Export Gl0b@L</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta http-equiv="x-ua-compatible" content="IE=10">
<link rel="icon" type="image/png" href="{$config.url}themes/sharkphp/images/sharkphp.png" />
<!--[if IE]><link rel="shortcut icon" type="image/x-icon" href="{$config.url}themes/sharkphp/images/sharkphp.ico" /><![endif]-->
<link rel="stylesheet" href="{$config.url}themes/font-awesome/css/font-awesome.css" type="text/css" media="screen" />
<link rel="stylesheet" href="{$config.url}themes/bootstrap3/css/bootstrap.css" type="text/css" media="screen" />
<link rel="stylesheet" href="{$config.url}themes/afpi2/css/afpi2.css" type="text/css" media="screen" />
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

<!--[if lt IE 9]><script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
<script type="text/javascript">
var base_url = "{$config.url}";
var suser = {$smarty.session.utilisateur|json_encode};
</script>
</head>
<body>
{strip}
	<div class="navbar navbar-fixed-top navbar-inverse" role="navigation">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="#">
          	<img alt="M&euros;H" src="http://www.sharkphp.com/web/images/sharkphp_white.png" style="height:20px;">
          </a>
        </div>
        <div class="navbar-collapse collapse">
		  <form class="navbar-form navbar-left" role="search" method="get" action="{$Helper->getLink("contacts/index")}">
			  <div class="form-group">
			    <input type="text" name="filtre[query]" value="{if isset($smarty.get.filtre.query) && !empty($smarty.get.filtre.query)}{$smarty.get.filtre.query}{/if}" class="form-control" placeholder="Search" id="search-top-layout">
			  </div>
			  <input type="hidden" name="filtre[societe_contact]" value="1" />
			  <input type="hidden" name="filtre[societes]" value="1" />
			  <input type="hidden" name="filtre[particulier]" value="1" />
			  <button type="submit" class="btn btn-default"><i class="fa fa-search"></i></button>
			</form>

          <ul class="nav navbar-nav navbar-right">
			{if $smarty.session.utilisateur.id != 'Visiteur'}
			
			<li class="dropdown">		
				<a id="notification-icon" class="notifications notification-icon dropdown-toggle" data-toggle="dropdown" href="#">				    	
					<i class="glyphicon glyphicon-globe"></i>
					<span class="notification-counter" id="notification-counter" style="display: none;">0</span>
				</a>

			    <ul id="notification-items" class="dropdown-menu" aria-labelledby="notification-icon">
					{if isset($clear_notification)}
					<li class="notification-button">
						Clear notification
					</li>
					<li class="divider"></li>
					{/if}
			        
			        <li id="notification-spinner"><img src="{$config.url}web/lib/notifications/img/loading.gif" alt="Loading" /></li>
			        
			        {if isset($all_notification)}
			            <li class="divider"></li>
			            <li class="notification-button">
			                All notifications
			            </li>
			        {/if}			        
			    </ul>			
			</li>

			<li class="dropdown">
				<a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="glyphicon glyphicon-user"></i> {if !empty($smarty.session.utilisateur.prenom)}&nbsp;<strong>{$smarty.session.utilisateur.prenom}</strong>{/if} <b class="caret"></b></a>
				<ul class="dropdown-menu">
					<li><a href="{$Helper->getLink("utilisateur")}" title="Mon compte"><i class="glyphicon glyphicon-user"></i> Profil</a></li>
					{if $modules.rdv.actif == 1}
					<li class="divider"></li>
					<li><a href="{$config.url}index.php/rdv/my" title="Mes rendez vous"><i class="fa fa-clock-o"></i>&nbsp;Rendez-vous</a></li>
					<li><a href="{$config.url}index.php/rdv/mycalendar" title="Mon calendrier"><i class="fa fa-calendar"></i>&nbsp;Mon calenderier</a></li>
					{/if}
					<li class="divider"></li>
					<li><a href="{$Helper->getLink("tasks/mytasks")}" title="Mes tâches"><i class="fa fa-tasks"></i> Tâches</a></li>
					<li><a href="{$Helper->getLink("notifications/viewall")}" title="Notifications"><i class="fa fa-globe"></i> Notifications</a></li>
					<li class="divider"></li>
					<li><a href="{$Helper->getLink("connexion/logout")}" title="Deconnexion"><i class="glyphicon glyphicon-off"></i> Deconnexion</a></li>
				</ul>
			</li>
			
			{/if}
          </ul>
          
        </div>{* /.nav-collapse *}
      </div>
    </div>

	{* START conteneur central *}
	<div class="container-fluid">
		<div class="row">
			<div class="col-sm-3 col-md-3 sidebar">{$nav_menu_left}</div>
			<div class="col-sm-9 col-sm-offset-3 col-md-9 col-md-offset-3 main" id="content-central">{$content}</div>
		</div>{* /row-fluid *}
	</div>{* /container-fluid *}
	{* END conteneur central *}
	
	{* START footer *}
	<footer class="footer_site">
		<div class="container">
			<div class="row">
				<div class="col-md-8 col-xs-8"></div>
				<div class="col-md-4 col-xs-4"></div>
			</div>
		</div>

		<div class="container">
			<div class="row">
				<div class="col-md-8 col-xs-8"></div>
				<div class="col-md-4 col-xs-4"></div>
			</div>			
		</div>
		<hr/>
		<div class="container">
			<div class="pull-left"></div>
			<div class="pull-right">
			Powered <a href="http://www.sharkphp.com" title="PHP CMS/FRAMEWORK">Sharkphp<img src="{$config.url}web/images/sharkphp.png" alt="" style="width:20px;" /></a>
			</div>
			<div class="clearfix"></div>	
		</div>	
	</footer>
	{* END footer *}

{$modal_global}
{$modal_search}

{* APPEL JS EN FOOTER *}
{if isset($FlashMessage) && !empty($FlashMessage)}<script type="text/javascript">var flash_message = '{$FlashMessage}'</script>{/if}
{if isset($pnotify) && !empty($pnotify)}<script type="text/javascript">var notify =  {$pnotify|json_encode}</script>{/if}
{foreach registry::$js_lib_footer as $k => $v}
<script type="text/javascript" src="{$config.url}web/lib/{$v}"></script>
{/foreach}
<script type="text/javascript" src="{$config.url}themes/bootstrap3/js/bootstrap.min.js"></script>
{if $smarty.const.IN_PRODUCTION == false}<script type="text/javascript" src="{$config.url}web/lib/meg/app.js"></script>{else}<script type="text/javascript" src="{$config.url}web/lib/meg/app.min.js"></script>{/if}

{if $smarty.const.IN_PRODUCTION === false}
<div class="container-fluid">
	<div class="row">
		<div class="col-sm-9 col-sm-offset-3 col-md-9 col-md-offset-3 main" id="content-central">
			<div class="pull-right">
				<a href="#dvlpModal" role="button" class="btn btn-primary" data-toggle="modal">Infos dev</a>
			</div>
			<div class="clearfix"></div>
			<hr/>
			<div style="size:9px; margin:auto; width:1000px;">
				<div>
				Page generee en : {$dvlp_tps_generation} sec | 
				Requete SQL : {$dvlp_nb_queries}| 
				Utilisation memoire : {$dvlp_memory} mo
				</div>
			</div>
			{$infosdev}
		</div>
	</div>
</div>
{/if}
</body>
</html>
{/strip}
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
@email : roumain18@gmail.com
@url : http://www.sharkphp.com
//-->