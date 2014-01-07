{strip}
<ol class="breadcrumb">
	<li><a href="{$Helper->getLink("index")}" title="Accueil"><i class="fa fa-home"></i>&nbsp;&nbsp;Accueil</a></li>
	<li><a href="{$Helper->getLink("mailing")}" title="Mailing"><i class="fa fa-envelope"></i>&nbsp;&nbsp;Mailing</a></li>
	<li class="active">#{$mailing->id} - {$mailing->libelle}</li>
</ol>

<div class="well">

	
	<div class="pull-right">
		{if $smarty.session.utilisateur.isAdmin > 0 || isset($smarty.session.acl.mailing_valid)}
		<a href="{$Helper->getLink("mailing/invalid_emails/{$mailing->id}")}" title="Email invalide"><span class="fa-stack fl-lg"><i class="fa fa-envelope fa-stack-1x"></i><i class="fa fa-ban fa-stack-2x text-danger"></i></span></a>
		&nbsp;&nbsp;&nbsp;
		<a href="{$Helper->getLink("mailing/import_stats/{$mailing->id}")}" title="Importer les stats"><i class="fa fa-signal fa-lg"></i></a>
		&nbsp;&nbsp;&nbsp;
		{/if}
		{if $smarty.session.utilisateur.isAdmin > 0 || isset($smarty.session.acl.mailing_adm) || isset($smarty.session.acl.mailing_valid)}
		<a href="{$Helper->getLink("mailing/edit/{$mailing->id}")}" title="Modifier"><i class="fa fa-edit fa-lg"></i></a>
		&nbsp;&nbsp;&nbsp;
		{/if}
		{if $smarty.session.utilisateur.isAdmin > 0 || isset($smarty.session.acl.mailing_adm)}
		<a href="javascript:deletemailing({$mailing->id})" title="Supprimer"><i class="fa fa-trash-o fa-lg"></i></a>
		{/if}
	</div>
	

	<h3>{$mailing->libelle}</h3>
	<div class="clearfix"></div>
	<table class="table table-striped table-condensed">
		<tr>
			<td>Description :</td>
			<td>{$mailing->description|nl2br}</td>
		</tr>
		<tr>
			<td>Date d'envoie souhaitée :</td>
			<td>{$mailing->date_wish}</td>
		</tr>
		<tr>
			<td>Status :</td>
			<td>
				{if !empty($mailing->date_send)}
				Envoyé le {$mailing->date_send} à {$nb_destinataires} destinataires
				{else}
				
					{if $mailing->valid == 0}
						En attente
						{if $smarty.session.utilisateur.isAdmin > 0 || isset($smarty.session.acl.mailing_valid)}
							&nbsp;
							<a href="javascript:valid({$mailing->id})" title="Valider"><i class="glyphicon glyphicon-ok"></i></a>
							&nbsp;&nbsp;
							<a href="javascript:unvalid({$mailing->id})" title="Refuser"><i class="glyphicon glyphicon-remove"></i></a>
						{/if}
					{elseif $mailing->valid == 1}Accepter
					{elseif $mailing->valid == 2}Refuser<p class="muted">{$mailing->refus}</p>
					{else}error status
					{/if}
				{/if}
			</td>
		</tr>

		{if !empty($mailing->type)}
		<tr>
			<td>Type :</td>
			<td><strong>{$mailing->type}</strong></td>
		</tr>
		{/if}

		{if !empty($mailing->number)}
		<tr>
			<td>Numero :</td>
			<td>{$mailing->number}</td>
		</tr>
		{/if}

		{if !empty($mailing->caller)}
		<tr>
			<td>Demandeur :</td>
			<td>{$mailing->caller}</td>
		</tr>
		{/if}
		
	</table>

	{if is_array($mailing->stats)}
	<hr/>
	<h4><i class="fa fa-signal"></i>&nbsp;Statistiques</h4>
	<table class="table table-condensed table-striped">

		<tr>
			<td>Nombre de destinataire réel :</td>
			<td>{$mailing->stats.file_contact}</td>
		</tr>

		<tr>
			<td>Nombre de destinatire en base :</td>
			<td>{$mailing->stats.db_contact}</td>
		</tr>

		<tr>
			<td>Ouverture :</td>
			<td>{$mailing->stats.open} {round(($mailing->stats.open*100)/$mailing->stats.file_contact)}%</td>
		</tr>
	</table>
	{/if}

	<hr/>
	<h4><i class="fa fa-filter"></i>&nbsp;Cibles du mailing</h4>
	<table class="table table-striped table-condensed">
		<tr>
			<td>Cible :</td>
			<td>
				<ul>
					{if isset($mailing->cible['societes']) && $mailing->cible['societes'] == 1}<li>Societe</li>{/if}
					{if isset($mailing->cible['societe_contact']) && $mailing->cible['societe_contact'] == 1}<li>Contact societe</li>{/if}
					{if isset($mailing->cible['particulier']) && $mailing->cible['particulier'] == 1}<li>Particulier</li>{/if}
				</ul>
				
			</td>
		</tr>		
	
		{if isset($mailing->cible['departement']) && !empty($mailing->cible['departement'])}
		<tr>
			<td>Departements :</td>
			<td>{foreach $mailing->cible['departement'] as $k => $v}<li>{$v}</li>{/foreach}</td>
		</tr>
		{/if}
		
		{if isset($mailing->cible['categorie']) && !empty($mailing->cible['categorie'])}
		<tr>
			<td>Categories :</td>
			<td>
				<ul>
					{foreach $mailing->cible['categorie'] as $k => $v}
					<li>
						{foreach $global_categories as $row}
							{if $row.id == $v}{$row.libelle}{/if}
						{/foreach}
					</li>
					{/foreach}
				</ul>
			</td>
		</tr>
		{/if}	
		
		{if isset($mailing->cible['effectif_mini']) && !empty($mailing->cible['effectif_max'])}
		<tr>
			<td>Effectif :</td>
			<td>de {$mailing->cible['effectif_mini']} à {$mailing->cible['effectif_max']}</td>
		</tr>
		{/if}
		
		{if isset($mailing->cible['poste']) && !empty($mailing->cible['poste'])}
		<tr>
			<td>Poste :</td>
			<td>
				{foreach $global_postes as $row}
					{if $row.id == $mailing->cible['poste']}
						{$row.libelle}
					{/if}
				{/foreach}
			</td>
		</tr>
		{/if}
		
		{if isset($mailing->cible['service']) && !empty($mailing->cible['service'])}
		<tr>
			<td>Service :</td>
			<td>
				{foreach $global_services as $row}
					{if $row.id == $mailing->cible['service']}
						{$row.libelle}
					{/if}
				{/foreach}
			</td>
		</tr>
		{/if}
		
		{if isset($mailing->cible['is_client'])}
		<tr>
			<td>Client :</td>
			<td><i class="glyphicon glyphicon-ok"></i></td>
		</tr>
		{/if}
		
	</table>
	
	{if empty($mailing->date_send)}
	<div id="nb-contacts-for-mailing"></div>
	{/if}
	
	{if empty($mailing->date_send)}
		{if $smarty.session.utilisateur.isAdmin > 0 || isset($smarty.session.acl.mailing_valid) || isset($smarty.session.acl.contacts_export_csv)}
		<a href="{$link_csv}" title="Export des contacts" class="btn btn-success"><i class="glyphicon glyphicon-export"></i>&nbsp;Export cible CSV</a>
		{/if}
		&nbsp;&nbsp;
		<a href="{$link_view}" title="Export des contacts" class="btn btn-info" target="_blank"><i class="fa fa-eye"></i>&nbsp;Voir cible</a>
	{/if}
	
	{if $smarty.session.utilisateur.isAdmin > 0 || isset($smarty.session.acl.mailing_valid)}
	<div class="pull-right">
		{if $mailing->send == 0 && $mailing->valid == 1}
		<a href="{$Helper->getLink("mailing/marksend/{$mailing->id}")}" title="Marquer le mailing comme envoye" class="btn btn-default">Marquer comme envoyé</a>
		&nbsp;&nbsp;
		{/if}
	</div>
	<div class="clearfix"></div>
	{/if}
	
</div>{* /well *}
{/strip}
<script type="text/javascript">
<!--
{if $smarty.session.utilisateur.isAdmin > 0 || isset($smarty.session.acl.mailing_valid)}
function valid(id){
	if(confirm('Etes vous certain de voir valider ce maling ?')){
		window.location.href = '{$Helper->getLink("mailing/valided/'+id+'")}';
	}
}

function unvalid(id){
	var raison = prompt("Motif refus :", "motif refus");
	window.location.href = '{$Helper->getLink("mailing/refused/'+id+'?raison='+encodeURIComponent(raison)+'")}';
}

function deletemailing(id){
	if(confirm('Etes vous sur de vouloir supprimer cette demande ?')){
		window.location.href = '{$Helper->getLink("mailing/delete/'+id+'")}';
	}
}
{/if}

{if !empty($mailing->date_send)}
jQuery(document).ready(function(){
	$.get(
        '{$Helper->getLink("mailing/ajax_get_nbcontacts/{$mailing.id}")}',{literal}
        {nohtml:'nohtml'},{/literal}
        function(data){
           $("#nb-contacts-for-mailing").html('<div class="text-center">Nombre de contacts : ' + data + '</div>');
        }       
    );
});
{/if}
//-->
</script>