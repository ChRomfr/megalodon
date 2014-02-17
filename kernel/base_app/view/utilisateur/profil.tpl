{strip}
{if isset($config.bootstrap_version) && $config.bootstrap_version == 3}
<ol class="breadcrumb">
	<li><a href="{$Helper->getLink("index")}" title="{$lang.Accueil}">{$lang.Accueil}</a></li>
	<li class="active">{$lang.Profil}</li>
</ol>

<div class="well">
	<h4>{$lang.Profil}</h4>
	<table class="table">
		<tr>
			<td>{$lang.Identifiant}</td>
			<td>{$smarty.session.utilisateur.identifiant}</td>
		</tr>
		<tr>
			<td>{$lang.Email}</td>
			<td>{$smarty.session.utilisateur.email}</td>
		</tr>
	</table>

	<div class="fright">
		<ul class="list-inline">
			{if $config.user_edit_profil == 1}
			<li><a href="{$Helper->getLink("utilisateur/edit")}" title="{$lang.Edition}" class="btn btn-default">{$lang.Edition}</a></li>
			{/if}
			<li><a href="{$Helper->getLink("utilisateur/changePassword")}" title="{$lang.Changement_de_mot_de_passe}" class="btn btn-default">{$lang.Changement_de_mot_de_passe}</a></li>
		</ul>
	</div>
	<div class="clearfix"></div>
</div>
{else}
<ul class="breadcrumb">
	<li><a href="{$Helper->getLink("index")}" title="{$lang.Accueil}">{$lang.Accueil}</a><span class="divider">/</span></li>
	<li>{$lang.Profil}</li>
</ul>

<div class="well">
	<h4>{$lang.Profil}</h4>
	<table class="table">
		<tr>
			<td>{$lang.Identifiant}</td>
			<td>{$smarty.session.utilisateur.identifiant}</td>
		</tr>
		<tr>
			<td>{$lang.Email}</td>
			<td>{$smarty.session.utilisateur.email}</td>
		</tr>
	</table>

	<div class="fright">
		<ul class="inline">
			{if $config.user_edit_profil == 1}
			<li><a href="{$Helper->getLink("utilisateur/edit")}" title="{$lang.Edition}" class="btn">{$lang.Edition}</a></li>
			{/if}
			<li><a href="{$Helper->getLink("utilisateur/changePassword")}" title="{$lang.Changement_de_mot_de_passe}" class="btn">{$lang.Changement_de_mot_de_passe}</a></li>
		</ul>
	</div>
	<div class="clear"></div>
</div>	
{/if}
{/strip}