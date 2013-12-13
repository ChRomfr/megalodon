<!DOCTYPE html>
<html>
<head>
<title>Meg - Mailing Export Gl0b@L</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta http-equiv="x-ua-compatible" content="IE=10">
<link rel="icon" type="image/png" href="{$config.url}themes/sharkphp/images/sharkphp.png" />
<!--[if IE]><link rel="shortcut icon" type="image/x-icon" href="{$config.url}themes/sharkphp/images/sharkphp.ico" /><![endif]-->
<link rel="stylesheet" href="{$config.url}themes/font-awesome/css/font-awesome.css" type="text/css" media="screen" />
<link rel="stylesheet" href="{$config.url}themes/bootstrap3/css/bootstrap.css" type="text/css" media="screen" />
<!--
<link rel="stylesheet" href="{$config.url}themes/dashboard/css/opa-icons.css" type="text/css" media="screen" />
<link rel="stylesheet" href="{$config.url}themes/dashboard/css/charisma-app.css" type="text/css" media="screen" />
<link rel="stylesheet" href="{$config.url}themes/dashboard/css/uniform.default.css" type="text/css" media="screen" />
<link rel="stylesheet" href="{$config.url}themes/dashboard/dashboard.css" type="text/css" media="screen" />
//-->
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
<script type="text/javascript" src="{$config.url}themes/bootstrap/js/bootstrap.min.js"></script>
<!--[if lt IE 9]>
<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
</head>
<!-- <body data-spy="scroll" data-target=".navbar"> -->
<body>
{strip}
	{* Bar de navigation *}
	<div class="navbar navbar-default navbar-fixed-top navbar-inverse" role="navigation">
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
		<!--
          <ul class="nav navbar-nav">
            <li class="active"><a href="#">Home</a></li>
            <li><a href="#about">About</a></li>
            <li><a href="#contact">Contact</a></li>
            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown">Dropdown <b class="caret"></b></a>
              <ul class="dropdown-menu">
                <li><a href="#">Action</a></li>
                <li><a href="#">Another action</a></li>
                <li><a href="#">Something else here</a></li>
                <li class="divider"></li>
                <li class="dropdown-header">Nav header</li>
                <li><a href="#">Separated link</a></li>
                <li><a href="#">One more separated link</a></li>
              </ul>
            </li>
          </ul>
		  -->
		  <form class="navbar-form navbar-left" role="search" method="get" action="{$Helper->getLink("contacts/index")}">
			  <div class="form-group">
			    <input type="text" name="filtre[query]" value="{if isset($smarty.get.filtre.query) && !empty($smarty.get.filtre.query)}{$smarty.get.filtre.query}{/if}" class="form-control" placeholder="Search">
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
				<a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="glyphicon glyphicon-user"></i> <b class="caret"></b></a>
				<ul class="dropdown-menu">
					<li><a href="{$Helper->getLink("utilisateur")}" title="Mon compte"><i class="glyphicon glyphicon-user"></i> Profil</a></li>
					<li class="divider"></li>
					<li><a href="{$Helper->getLink("tasks/mytasks")}" title="Mes tâches"><i class="fa fa-tasks"></i> Tâches</a></li>
					<li><a href="{$Helper->getLink("notifications/viewall")}" title="Notifications"><i class="fa fa-globe"></i> Notifications</a></li>
					<li class="divider"></li>
					<li><a href="{$Helper->getLink("connexion/logout")}" title="Deconnexion"><i class="glyphicon glyphicon-off"></i> Deconnexion</a></li>
				</ul>
			</li>
			
			{/if}
          </ul>
          
        </div><!--/.nav-collapse -->
      </div>
    </div>

	
	{* START header *}
	<div id="header" style="padding-top:50px;"></div>
	{* END header *}

	{* START conteneur central *}
	<div class="container-fluid">
		<div class="row-fluid">
			<div class="col-md-3 main-menu-span" style="padding-top:20px;">
				{$nav_menu_left}
			</div>{* /span3 *}
			<div class="col-md-9">
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
			<div class="pull-left">
				
			</div>
			<div class="pull-right">
				Réaliser avec <a href="http://www.sharkphp.com" title="Another CMS/FRAMEWORK">Sharkphp <img src="{$config.url}web/images/sharkphp.png" alt="" style="width:20px;" /></a>
			</div>
			<div class="clearfix"></div>
		</div><!-- /container -->
	</footer>
	{* END footer *}

{$modal_global}
{/strip}
<script type="text/javascript">
<!--
$(".chzn-select").chosen();
$(".chozen").chosen();

{if isset($FlashMessage) && !empty($FlashMessage)}
$(".breadcrumb").after('<div class="alert alert-info"><button type="button" class="close" data-dismiss="alert">&times;</button>{$FlashMessage}</div>');
{/if}

{if $smarty.session.utilisateur.id != 'Visiteur'}
var interval = 10000;
var pagetitle = "";
var userid = "{$smarty.session.utilisateur.id}";
var count_url = "{$Helper->getLink("notifications/getcount")}";
var list_url = "{$Helper->getLink("notifications/getlist")}";
$(document).ready(function(){
    startNotifications();
});

$(document).ready(function() {
	$.get(
        '{$Helper->getLink("ajax/my_tasks")}',{literal}
        {
        	nohtml:'nohtml',        	
        },{/literal}
        function(data){
            $('#nav-menu-my-task').html(data);
        }
    );
});
{/if}

{if isset($pnotify)}
$(function(){
	$.pnotify({
	    title: '{$pnotify.title}',
	    text: '{$pnotify.message}',
	    type: '{$pnotify.type}',
	    opacity: .{$pnotify.oppacity}
	});
});
{/if}

//-->
</script>

{if $smarty.const.IN_PRODUCTION === false}
	<div class="pull-right">
		<a href="#dvlpModal" role="button" class="btn btn-primary" data-toggle="modal">Infos dev</a>
	</div>
	<hr/>
	<div style="size:9px; margin:auto; width:1000px;">
		<div>
		Page generee en : {$dvlp_tps_generation} sec | 
		Requete SQL : {$dvlp_nb_queries}| 
		Utilisation memoire : {$dvlp_memory} mo
		</div>
	</div>
	{$infosdev}
{/if}
</body>
</html>
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