{strip}
<ol class="breadcrumb">
	<li><a href="{$Helper->getLink("index")}" title="Accueil"><i class="fa fa-home"></i>&nbsp;&nbsp;Accueil</a></li>
	<li><a href="{$Helper->getLink("mailing")}" title="Mailing"><i class="fa fa-envelope"></i>&nbsp;&nbsp;Mailing</a></li>	
	<li class="active">
		{if isset($smarty.session.utilisateur.mailing_adm) && $smarty.session.utilisateur.mailing_adm == 1}
			Nouveau mailing
		{else}
			Demande
		{/if}
	</li>
</ol>

<div class="well">
{$form}
</div>

