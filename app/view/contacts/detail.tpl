{* app/view/contacts/detail.tpl *}
{strip}
<ol class="breadcrumb">
	<li><a href="{$Helper->getLink("index")}" title="Accueil"><i class="fa fa-home"></i>&nbsp;&nbsp;Accueil</a></li>
	<li><a href="{$Helper->getLink("contacts")}" title="Contacts"><i class="fa fa-users"></i>&nbsp;&nbsp;Contacts</a></li>
	<li class="active">{$contact.nom} {if !empty($contact.prenom)}{$contact.prenom}{/if}</li>
</ol>

<div class="well">

	{if $contact.actif == 0}
	<div class="bs-callout bs-callout-warning">
		<p class="text-center"><strong>Contact désactivé</strong></p>
	</div>
	{/if}
	
	{if $contact.pasdecontact == 1}
	<div class="bs-callout bs-callout-danger">
		<p class="text-center"><strong>Ce contact ne souhaite pas être démarché depuis le {$contact.date_pasdecontact}</strong></p>
	</div>
	{/if}

	<div class="pull-right">
		<a href="javascript:task_get_form('contacts', {$contact.id});" title="Ajouter une tâche"><i class="fa fa-lg fa-tasks"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;
		<a href="javascript:get_form_add_file({$contact.id});" title="{$lang.Ajouter_un_document}"><i class="fa fa-lg fa-cloud-upload"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;
		{if !empty($contact.email)}
		<a href="javascript:get_form_send_email({$contact.id})" title="Envoyer un email"><i class="fa fa-envelope fa-lg"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;
		{/if}
		<a href="javascript:GetFormAddPhone({$contact.id})" title="Ajouter un telephone"><i class="fa fa-phone fa-lg"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;
		{if $smarty.session.utilisateur.isAdmin > 0 || isset($smarty.session.acl.contacts_edit)}
		<a href="{$Helper->getLink("contacts/edit/{$contact.id}")}" title="Modifier ce contact"><i class="fa fa-edit fa-lg"></i></a>
		&nbsp;&nbsp;&nbsp;&nbsp;
		{/if}
		{if $smarty.session.utilisateur.isAdmin > 0 || isset($smarty.session.acl.contacts_delete)}
		<a href="javascript:deleteContact({$contact.id})" title="Supprimer ce contact"><i class="fa fa-trash-o fa-lg"></i></a>
		{/if}
	</div>
	
	<h4>{if !empty($contact.raison_social)}{$contact.raison_social}{else}{$contact.prenom}&nbsp;{$contact.nom}{/if}</h4>
	
	<div class="clearfix"></div>

	<table class="table table-condensed table-striped">
		
		<tr>
			<td>Adresse :</td>
			<td>
				<div class="pull-left">
					{$contact.adress}<br/>
					{if !empty($contact.adresse2)}{$contact.adresse2}<br/>{/if}
					{$contact.zip_code}&nbsp;{$contact.city}<br/>
					{if !empty($contact.pays)}{$contact.pays}{/if}
				</div>
				{if !empty($contact.lat) && !empty($contact.lng)}
				<div id="map-contacts" style="width:300px; height:300px;" class="pull-right"></div><div class="clearfix"></div>
				{/if}
			</td>
		</tr>
		
		{foreach $contact.telephones as $row}
		<tr>
			<td>
				{if $row.type == 1}Ligne directe
				{elseif $row.type == 2}Standard
				{elseif $row.type == 3}Domicile
				{elseif $row.type == 4}Mobile
				{elseif $row.type == 5}Fax
				{/if}
			</td>
			<td>{$row.telephone}&nbsp;&nbsp;<a href="javascript:DeletePhone({$row.id});" title="Supprimer"><i class="fa fa-trash-o"></i></a></td>
		</tr>
		{/foreach}
		
		{if !empty($contact.email)}
		<tr>
			<td>Email :</td>
			<td><a href="javascript:get_form_send_email({$contact.id})" title="Envoyer un email"><button class="btn"><i class="glyphicon glyphicon-envelope"></i></button></a>&nbsp;{$contact.email}</td>
		</tr>
		{/if}
		
		{* Traitement des infos cas entreprise *}
		{if !empty($contact.raison_social)}
		
			{if !empty($contact.siret)}
			<tr>
				<td>SIRET :</td>
				<td><a href="http://www.bilansgratuits.fr/entreprise/fiche/{$contact.siret}.htm" title="Voir fiche sur bilan gratuit" target="_blank">{$contact.siret}</a></td>
			</tr>
			{/if}
			
			{if !empty($contact.effectif)}
			<tr>
				<td>Effectif :</td>
				<td>{$contact.effectif}</td>
			{/if}
			
		{/if}
		
		{* Traitement pro lie a une entreprise*}
		{if !empty($contact.parent_id) && $contact.type == 2}
		<tr>
			<td>Societe :</td>
			<td><a href="{$Helper->getLink("contacts/detail/{$contact.parent_id}")}" title="Detail {$contact.parent_nom}">{$contact.parent_nom}</td>
		</tr>
		{/if}
		
		{if !empty($contact.poste_id)}
		<tr>
			<td>Poste :</td>
			<td>{$contact.poste}</td>
		</tr>
		{/if}
		
		{if !empty($contact.service_id)}
		<tr>
			<td>Service :</td>
			<td>{$contact.service}</td>
		</tr>
		{/if}
		
		{* Traitement des categories *}
		{if count($contact.categories) > 0}
		<tr>
			<td>Catégories :</td>
			<td>
				<ul class="list-unstyled">
					{foreach $contact.categories as $row}
					<li>{$row.libelle}</li>
					{/foreach}
				</ul>
			</td>
		{/if}
		
		{if !empty($contact.code_interne)}
		<tr>
			<td>Code interne :</td>
			<td><strong>{$contact.code_interne}</strong></td>
		</tr>
		{/if}
		
		{if !empty($contact.client)}
		<tr>
			<td>Client :</td>
			<td><span class="label label-success">Oui</span></td>
		</tr>
		{/if}
		
		{if !empty($contact.parent_id) && $contact.type == 1}
			<tr>
				<td>Siege social :</td>
				<td><a href="{$Helper->getLink("contacts/detail/{$contact.parent_id}")}" title="">{$contact.parent_nom}</a></td>
			</tr>
		{/if}

		{if !empty($contact.users)}
		<tr>
			<td>Lié à :</td>
			<td>
				{foreach $contact.users as $k => $v}
				<span class="badge">{$v}</span>
				{/foreach}
			</td>
		</tr>	
		{/if}
		
		{if !empty($contact.groups)}
		<tr>
			<td>Lié au groupe :</td>
			<td>
				{foreach $contact.groups as $k => $v}
				<span class="badge">{$v}</span>
				{/foreach}
			</td>
		</tr>	
		{/if}
		
	</table>
	
	<hr/>
	
	<div class="tab-content">
		
		<ul id="tabContacts" class="nav nav-tabs">
            {if $contact.type == 1}<li><a href="#tabContactsOfSociete" data-toggle="tab" id="atabcontactofsociete">Contacts</a></li>{/if}
			{if $contact.type == 1 && $contact.mother == 1}<li><a href="#tabAgences" data-toggle="tab">Agences</a></li>{/if}
            <li {if $smarty.session.utilisateur.historique_contact == 0}class="active"{/if}><a href="#tabSuivi" data-toggle="tab">Suivi</a></li>
            <li><a href="#tab-rdv" data-toggle="tab" id="atabmeeting">Rendez vous</a></li>
			<li><a href="#tabContactsEmailSend" data-toggle="tab">Emails</a></li>
			<li><a href="#tabMailingSend" data-toggle="tab" id="atabmailing">Mailings</a></li>
			<li><a href="#tabContactsFiles" data-toggle="tab" id="atabfiles">Fichiers</a></li>
			{if isset($modules['ca']) && $modules['ca']['actif'] == 1}<li><a id="atabca" href="#tabCA" data-toggle="tab">CA</a></li>{/if}
			{if $smarty.session.utilisateur.historique_contact == 1}<li class="active"><a href="#tabContactsLogs" data-toggle="tab">Historique</a></li>{/if}
			{if $smarty.session.utilisateur.isAdmin > 0}<li><a href="#tabDoublons" data-toggle="tab">Doublons</a></li>{/if}
        </ul>

        <!-- START tab-suivis -->
        <div id="tabSuivi" class="tab-pane {if $smarty.session.utilisateur.historique_contact == 0}active{/if}">
        	<br/>
        	<div class="pull-right">
        		<a href="javascript:get_form_suivi({$contact.id});" title="" class="btn btn-default"><i class="fa fa-plus fa-lg"></i></a> 
        	</div>
        	<div class="clearfix"></div>
        	{if count($contact.suivis) == 0}
        		<br/>
        		<div class="text-center alert alert-warning">Ce contact n'a aucun suivi</div>
        	{else}
        	<br/>
        	<table class="table table-striped table-condensed">
        		<thead>
        			<tr>
        				<th>Suivi</th>
        				<th>Date</th>
        				<th>Auteur</th>
        				<th>Source</th>
        				{if $smarty.session.utilisateur.isAdmin > 0}
        				<th>&nbsp;</th>
        				{/if}
        			</tr>
        		</thead>
        		<tbody>
        			{foreach $contact.suivis as $row}
        			<tr>
        				<td>{$row.suivi|nl2br}</td>
        				<td>{$row.date_suivi}</td>
        				<td>{$row.identifiant}</td>
        				<td>{$row.source}</td>
        				{if $smarty.session.utilisateur.isAdmin > 0}
        				<td><a href="javascript:deleteSuivi({$row.id});" title="Supprimer"><i class="fa fa-trash-o"></i></a></td>
        				{/if}
        			</tr>
        			{/foreach}
        		</tbody>
        	</table>
        	{/if}
        </div>
        <!-- END tab-suivis -->

        <div id="tab-rdv" class="tab-pane">
        	<br/>
        	<div class="pull-right">
        		<a href="javascript:get_rdv_contacts({$contact.id});" title="" class="btn btn-default"><i class="fa fa-plus fa-lg"></i></a>
        	</div>
        	<div class="clearfix"></div>
        	<br/>
			<table class="table table-striped table-condensed" id="table-contacts-rdv">
				<thead><tr><th>#</th><th>Date</th><th>Collorateur</th><th>Statut</th></tr></thead>
				<tbody></tbody>
			</table>
        </div>

        <!-- START tab-mailing envoye -->
        <div id="tabMailingSend" class="tab-pane">
        	<br/>
			<table class="table table-striped table-condensed" id="table-contacts-mailings">
				<thead><tr><th>#</th><th>Mailing</th><th>Email</th><th>Ouvert</th></tr></thead>
				<tbody></tbody>
			</table>
        </div>
        <!-- END tab-mailing envoye -->
		
		<!-- START tab Doublons -->
		{if $smarty.session.utilisateur.isAdmin > 0}
		<div id="tabDoublons" class="tab-pane">
		{if isset($matchings)}

			{if count($matchings) > 0}			
			<form method="get" action="{$Helper->getLink("contacts/fusion_contact")}" class="form-inline" role="form">
				<div class="form-group">
					<label class="sr-only">ID du doublon</label>
					<input type="text" name="c2" required class="form-control" placeholder="ID du doublon"/>
				</div>
				<button class="btn btn-primary" type="submit">Fusionner</button>
				<input type="hidden" name="c1" value="{$contact.id}" />				
			</form>
			{/if}

			<table class="table table-striped table-condensed" id="table-matchings">
				<thead>
					<tr>
						<th>ID</th>
						<th>Raison social</th>
					</tr>
				</thead>
				{foreach $matchings as $row}
				<tr>
					<td>{$row.contact_id}</td>
					<td><a href="{$Helper->getLink("contacts/detail/{$row.contact_id}")}" title="Voir la fiche" target="_blank">{$row.raison_social}</a></td>
				</tr>
				{foreachelse}
				<tr>
					<td colspan="2">
						<div class="text-center alert alert-success">Aucun doublon trouvé</div>
					</td>
				</tr>
				{/foreach}
			</table>
		{/if}
		</div>
		{/if}
		<!-- END tab Doublons -->

		{if $contact.type == 1 && $contact.mother == 1}
		<div id="tabAgences" class="tab-pane">
			<br/>
			<div class="pull-right">
				<a href="javascript:get_form_add_agence({$contact.id})" title=""><i class="fa fa-plus fa-lg"></i></a>
			</div>
			<div class="clearix"></div>
			<table class="table table-striped table-consended">
				<thead>
					<tr>
						<th>#</th>
						<th>Raison social</th>
						<th>Ville</th>
						<th></td>
					</tr>
				</thead>
				<tbody>
				{foreach $agences as $row}
					<tr>
						<td><a href="{$Helper->getLink("contacts/detail/{$row.id}")}" title="Detail">#{$row.id}</a></td>
						<td>{$row.nom}</td>
						<td>{$row.city}</td>
						<td><a href="javascript:agenceRemove({$row.id}, {$contact.id})" title=""><i class="fa fa-trash-o"></i></td>
					</tr>
				{/foreach}
				</tbody>
			</table>		
		</div>
		{/if}
		
		{$tab_files}

		{if $smarty.session.utilisateur.historique_contact == 1}
		<div id="tabContactsLogs" class="tab-pane active">
			<table class="table table-condensed" id="table-contacts-logs">
				<thead>
					<tr>
						<th>Date</th>
						<th>Utilisateur</th>
						<th>Log</th>
					</tr>
				</thead>
				<tbody></tbody>
			</table>
		</div>
		{/if}
		
		<div id="tabContactsEmailSend" class="tab-pane">
			<br/>
			<table class="table table-condensed">
				<thead>
					<tr>
						<th>Date</th>
						<th>Utilisateur</th>
						<th>De</th>
						<th>Sujet</th>
						<th>Resultat</th>
					</tr>
				</thead>
				<tbody>
					{foreach $contact.emails as $row}
					<tr>
						<td>{$row.date_send}</td>
						<td>{$row.email_user}</td>
						<td>{$row.de}</td>
						<td><a href="javascript:get_form_send_email({$row.id});" title="">{$row.sujet}</a></td>
						<td>{$row.result}</td>
					</tr>
					{/foreach}
				</tbody>
			</table>
		</div>
				
		{if $contact.type == 1}
		<div id="tabContactsOfSociete" class="tab-pane">
			<div class="pull-right">
				<a href="{$Helper->getLink("contacts/add?societe={$contact.id}")}" title="Ajouter un contact" class="btn btn-default"><i class="fa fa-plus fa-lg"></i></a>
			</div>
			<div class="clearfix"></div>
			<table class="table" id="table-contacts-societe">
				<thead>
					<tr>
						<th>Contact</th>
						<th>Email</td>
						<th>Service</th>
						<th>Poste</th>
					</tr>
				</thead>
				<tbody></tbody>
			</table>
		</div>
		{/if}

		{* Contenu tab CA *}
		{if isset($modules['ca']) && $modules['ca']['actif'] == 1}{$tab_ca}{/if}

	</div>

</div>{* /.well *}

{* MODAL GENERIQUE *}
<div id="modal-fiche-contacts"	class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h3 id="modal-fiche-contact-label"></h3>
			</div>
			<div class="modal-body" id="modal-fiche-contacts-body"></div>
			<div class="modal-footer">
				<button class="btn" data-dismiss="modal" aria-hidden="true">Fermer</button>
			</div>
		</div><!-- /modal-content -->
	</div><!-- /modal-dialog -->
</div>
{/strip}

<script src="http://maps.googleapis.com/maps/api/js?sensor=false" type="text/javascript"></script> 
<script type="text/javascript">var contact = {$contact|json_encode};</script>