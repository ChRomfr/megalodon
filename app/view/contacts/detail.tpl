{strip}
{* app/view/contacts/detail.tpl *}
<ol class="breadcrumb">
	<li><a href="{$Helper->getLink("index")}" title="Accueil">Accueil</a></li>
	<li><a href="{$Helper->getLink("contacts")}" title="Contacts">Contacts</a></li>
	<li class="active">
		{if !empty($contact.raison_social)}{$contact.raison_social}{else}{$contact.prenom}&nbsp;{$contact.nom}{/if}
	</li>
</ol>

<div class="well">

	<div class="pull-right">
		{if $smarty.session.utilisateur.isAdmin > 0 || isset($smarty.session.acl.contacts_edit)}
		<a href="{$Helper->getLink("contacts/edit/{$contact.contact_id}")}" title="Modifier ce contact"><i class="icon icon-edit"></i></a>
		&nbsp;&nbsp;
		{/if}
		{if $smarty.session.utilisateur.isAdmin > 0 || isset($smarty.session.acl.contacts_delete)}
		<a href="javascript:deleteContact({$contact.contact_id})" title="Supprimer ce contact"><i class="icon icon-trash"></i></a>
		{/if}
	</div>
	
	<h4>{if !empty($contact.raison_social)}{$contact.raison_social}{else}{$contact.prenom}&nbsp;{$contact.nom}{/if}</h4>
	
	<div class="clearfix"></div>
	<table class="table table-condensed">
		
		<tr>
			<td>Adresse :</td>
			<td>
				<div class="pull-left">
					{$contact.adresse1}<br/>
					{if !empty($contact.adresse2)}{$contact.adresse2}<br/>{/if}
					{$contact.code_postal}&nbsp;{$contact.ville}<br/>
					{if !empty($contact.pays)}{$contact.pays}{/if}
				</div>
				<div id="map-contacts" style="width:300px; height:300px;" class="pull-right"></div><div class="clearfix"></div>
			</td>
		</tr>
		
		{* Traitement des telephones *}
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
			<td>{$row.telephone}&nbsp;&nbsp;<a href="javascript:DeletePhone({$row.id});" title="Supprimer"><i class="icon icon-trash"></i></a></td>
		</tr>
		{foreachelse}
		<tr>
			<td colspan="2" class="text-center">
				    <div class="alert alert-info">
					    <button type="button" class="close" data-dismiss="alert">&times;</button>
					    <strong>/!\</strong> Aucun téléphone.
				   </div>
			   	
			</td>
		</tr>
		{/foreach}
		<tr>
			<td colspan="2">
				<div class="text-center">
					<a href="javascript:GetFormAddPhone({$contact.contact_id});" title="" class="btn btn-default">Ajouter un téléphone</a>
				</div>
			</td>
		</tr>
		
		{if !empty($contact.email)}
		<tr>
			<td>Email :</td>
			<td><a href="#modal-email" title="" data-toggle="modal"><button class="btn"><i class="glyphicon glyphicon-envelope"></i></button></a>&nbsp;{$contact.email}</td>
		</tr>
		{/if}
		
		{* Traitement des infos cas entreprise *}
		{if !empty($contact.raison_social)}
		
			{if !empty($contact.siret)}
			<tr>
				<td>SIRET :</td>
				<td>{$contact.siret}</td>
			</tr>
			{/if}
			
			{if !empty($contact.effectif)}
			<tr>
				<td>Effectif :</td>
				<td>{$contact.effectif}</td>
			{/if}
			
		{/if}
		
		{* Traitement pro lie a une entreprise*}
		{if isset($contact.societe)}
		<tr>
			<td>Societe :</td>
			<td><a href="{$Helper->getLink("contacts/detail/{$contact.societe.contact_id}")}" title="Detail {$contact.societe.raison_social}">{$contact.societe.raison_social}</td>
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
				<ul class="unstyled">
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
		
		{if !empty($siege)}
			<tr>
				<td>Siege social :</td>
				<td><a href="{$Helper->getLink("contacts/detail/{$siege.contacts_id}")}" title="">{$siege.raison_social}</a></td>
			</tr>
		{/if}
		
	</table>
	
	{* START TAB *}
	<div class="tab-content">
		
		{* Liste des onglets *}
		<ul id="tabContacts" class="nav nav-tabs">
            {if !empty($contact.raison_social)}<li><a href="#tabContactsOfSociete" data-toggle="tab">Contacts</a></li>{/if}
			{if !empty($contact.raison_social) && $contact.mother == 1}<li><a href="#tabAgences" data-toggle="tab">Agences</a></li>{/if}
            <li {if $smarty.session.utilisateur.historique_contact == 0}class="active"{/if}><a href="#tabSuivi" data-toggle="tab">Suivi</a></li>
			<li><a href="#tabContactsEmailSend" data-toggle="tab">Emails</a></li>
			<li><a href="#tabMailingSend" data-toggle="tab">Mailings</a></li>
			<li><a href="#tabContactsFiles" data-toggle="tab">Fichiers</a></li>
			{if $smarty.session.utilisateur.historique_contact == 1}<li class="active"><a href="#tabContactsLogs" data-toggle="tab">Histoirique</a></li>{/if}
			{if $smarty.session.utilisateur.isAdmin > 0}<li><a href="#tabDoublons" data-toggle="tab">Doublons</a></li>{/if}
        </ul>

        <div id="tabSuivi" class="tab-pane">
        	<div class="pull-right">
        		<a href="#modal-suivi" title="" data-toggle="modal" data-target="#modal-suivi"><i class="icon icon-plus"></i></a>
        	</div>
        	<div class="clearfix"></div>
        	{if count($contact.suivis) == 0}
        		<div class="text-center alert">Ce contact n'a aucun suivi</div>
        	{else}
        	<table class="table">
        		<thead>
        			<tr>
        				<th>Suivi</th>
        				<th>Date</th>
        				<th>Auteur</th>
        				{if $smarty.session.utilisateur.mailing_adm == 1 || $smarty.session.utilisateur.isAdmin > 0}
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
        				{if $smarty.session.utilisateur.mailing_adm == 1 || $smarty.session.utilisateur.isAdmin > 0}
        				<td><a href="javascript:deleteSuivi({$row.id});" title="Supprimer"><i class="icon icon-trash"></i></a></td>
        				{/if}
        			</tr>
        			{/foreach}
        		</tbody>
        	</table>
        	{/if}
        </div>

        <div id="tabMailingSend" class="tab-pane">
        	{if count($contact.mailings) == 0}
        		<div class="text-center alert">Ce contact n'a reçu aucun mailing</div>
        	{else}
			<table class="table table-striped table-condensed">
				<thead>
					<tr>
						<th>#</th>
						<th>Mailing</th>
					</tr>
				</thead>
				<tbody>
				{foreach $contact.mailings as $row}
					<tr>
						<td>{$row.id}</td>
						<td>{$row.libelle}</td>
					</tr>
				{/foreach}
				</tbody>
			</table>
        	{/if}
        </div>
		
		{if $smarty.session.utilisateur.isAdmin > 0}
		<div id="tabDoublons" class="tab-pane">
		{if isset($matchings)}
			{* formulaire de fusion *}			
			<form method="get" action="{$Helper->getLink("contacts/fusion_contact")}" class="form-inline">
				<input type="text" name="c2" required class="input-small" placeholder="ID du doublon"/>&nbsp;
				<button class="btn btn-primary" type="submit">Fusionner</button>
				<input type="hidden" name="c1" value="{$contact.contact_id}" />
			</form>
			
			{* Tableau des matching *}
			<table class="table table-striped table-condensed">
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
						<div class="alert alert-success">Aucun doublon trouvé</div>
					</td>
				</tr>
				{/foreach}
			</table>
		{/if}
		</div>
		{/if}
		
		{if !empty($contact.raison_social) && $contact.mother == 1}
		<div id="tabAgences" class="tab-pane">
			
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
						<td><a href="{$Helper->getLink("contacts/detail/{$row.contacts_id}")}" title="Detail">{$row.contacts_id}</a></td>
						<td>{$row.raison_social}</td>
						<td>{$row.ville}</td>
						<td><a href="javascript:agenceRemove({$row.contacts_id}, {$contact.contact_id})" title=""><i class="icon icon-trash"></i></td>
					</tr>
				{/foreach}
				</tbody>
			</table>
			
			<form method="get" action="{$Helper->getLink("contacts/agence_add")}" class="form-inline" role="form">
				<fieldset>
					<legend>Nouvelle agence :</legend>
					<div class="form-group">
						<label class="sr-only">ID de l'agence</label>
						<input type="text" name="agence_id" required class="form-control" placeholder="ID de l agence"/>
					</div>
					<button class="btn btn-primary" type="submit">Enregistrer</button>
					<input type="hidden" name="mother" value="{$contact.sid}" />
				</fieldset>
			</form>
		</div>
		{/if}
		
		<div id="tabContactsFiles" class="tab-pane">
			{if count($contact.files)}
			<table class="table table-condensed" id="table-contacts-files">
				<thead>
					<tr>
						<th>Fichier</th>
						<th>Utilisateur</th>
						<th>Date d'ajout</th>
						{if $smarty.session.utilisateur.mailing_adm == 1 || $smarty.session.utilisateur.isAdmin > 0}
						<th></th>
						{/if}
					</tr>
				</thead>
				<tbody>
					{foreach $contact.files as $file}
					<tr>
						<td><a href="{$config.url}web/upload/contacts/{$contact.contact_id}/{$file.disk_name}" title="Télécharger le fichier">{$file.name}</a></td>
						<td>{$file.identifiant}</td>
						<td>{$file.date_add}</td>
						{if $smarty.session.utilisateur.mailing_adm == 1 || $smarty.session.utilisateur.isAdmin > 0}
						<td><a href="javascript:deleteFile({$file.id});" title="Supprimer ce fichier"><i class="icon icon-trash"></i></a></td>
						{/if}
					</tr>
					{/foreach}
				</tbody>
			</table>
			{/if}
			<hr/>
			<form method="post" action="{$Helper->getLink("contacts/file_add/{$contact.contact_id}?nohtml")}" target="uploadFrame" enctype="multipart/form-data" onsubmit="return sendUpload()" class="form-inline" role="form">
				<div class="form-group">
					<label class="sr-only">Fichier</label>
					<input type="file" name="file_contact" class="form-control" placeholder="Fichier a envoyé"/>
				</div>
				<button type="submit" class="btn btn-primary">Envoyer</button>
			</form>
			
			<div id="uploadInfos">
				<div id="uploadStatus"></div>
				<iframe id="uploadFrame" name="uploadFrame" style="display:none;"></iframe>
			</div>
		</div>
		
		{if $smarty.session.utilisateur.historique_contact == 1}
		<div id="tabContactsLogs" class="tab-pane active">
			<table class="table table-condensed">
				<thead>
					<tr>
						<th>Date</th>
						<th>Utilisateur</th>
						<th>Log</th>
					</tr>
				</thead>
				<tbody>
				{foreach $contact.logs as $row}
					<tr>
						<td>{$row.date_log}</td>
						<td>{$row.log_user}</td>
						<td>{$row.log}</td>
					</tr>
				{/foreach}
				</tbody>
			</table>
		</div>
		{/if}
		
		<div id="tabContactsEmailSend" class="tab-pane">
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
						<td>{$row.sujet}</td>
						<td>{$row.result}</td>
					</tr>
					{/foreach}
				</tbody>
			</table>
		</div>
				
		{if !empty($contact.raison_social)}
		<div id="tabContactsOfSociete" class="tab-pane">
			<div class="pull-right">
				<a href="{$Helper->getLink("contacts/add?societe={$contact.contact_id}")}" title=""><i class="icon icon-plus"></i></a>
			</div>
			<div class="clearfix"></div>
			<table class="table">
				<thead>
					<tr>
						<th>Contact</th>
						<th>Email</td>
						<th>Service</th>
						<th>Poste</th>
					</tr>
				</thead>
				<tbody>
					{foreach $contact.contacts as $row}
					<tr>
						<td><a href="{$Helper->getLink("contacts/detail/{$row.cid}")}" title="">{$row.prenom}&nbsp;{$row.nom}</a></td>
						<td>{$row.email}</td>
						<td>{$row.service}</td>
						<td>{$row.poste}</td>
					</tr>
					{/foreach}
				</tbody>
			</table>
		</div>{* /tabContactsOfSociete *}
		{/if}
		
	</div>{* /tab-content *}
	{* END TAB *}
	

</div>{* /well *}

<div id="modal-email" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h3 id="myModalLabel">Envoie email</h3>
			</div>

			<div class="modal-body">
				<form method="post" action="{$Helper->getLink("contacts/sendemail/{$contact.contact_id}")}" id="form-email-ets" class="form" role="form">
					<div class="form-group">
						<label>A :</label>
						<span class="uneditable-input">{$contact.email}</span>
						<input type="hidden" name="mail[a]" value="{$contact.email}" class="form-control"/>
					</div>
					
					<div class="form-group">
						<label>De :</label>
						<select name="mail[de]" required class="form-control">
							<option value="{$smarty.session.utilisateur.email}">{$smarty.session.utilisateur.email}</option>
						</select>
					</div>
					
					<div class="control-form">
						<label>Sujet :</label>
						<input type="text" name="mail[sujet]" id="sujet" required class="form-control"/>
					</div>
					<div class="control-form">
						<label>Message :</label>
						<textarea name="mail[body]" required id="body-email" cols="40" rows="6" class="form-control"></textarea>
					</div>
			</div>
			<div class="modal-footer">
				<button class="btn" data-dismiss="modal" aria-hidden="true">Annuler</button>
				<button type="submit" class="btn btn-primary">Envoyer</button>
			</div>
				</form>
		</div><!-- /modal-content -->
	</div><!-- /modal-dialog -->
</div>

{* MODAL AJOUT SUIVI *}
<div id="modal-suivi" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
					<h3 id="myModalLabel">Nouveau suivi</h3>
				</div>
				<div class="modal-body">
					<form method="post" action="{$Helper->getLink("contacts/suiviadd/{$contact.contact_id}")}" id="form-suivi-add" class="form">
						<label>Suivi :</label>
						<textarea name="suivi[suivi]" id="suivi" class="input-xxlarge" required></textarea>
				</div>
				<div class="modal-footer">
					<button class="btn" data-dismiss="modal" aria-hidden="true">Annuler</button>
					<button type="submit" class="btn btn-primary">Envoyer</button>
				</div>
					</form>
		</div><!-- /modal-content -->
	</div><!-- /modal-dialog -->
</div>

{* MODAL GENERIQUE *}
<div id="modal-fiche-contacts"	class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h3 id="modal-fiche-contact-label"></h3>
			</div>
			<div class="modal-body" id="modal-fiche-contacts-body">
				
			</div>
			<div class="modal-footer">
				<button class="btn" data-dismiss="modal" aria-hidden="true">Fermer</button>
			</div>
		</div><!-- /modal-content -->
	</div><!-- /modal-dialog -->
</div>
{/strip}

<script src="http://maps.googleapis.com/maps/api/js?sensor=false" type="text/javascript"></script> 
<script type="text/javascript">
<!--
function deleteContact(id){
	if(confirm('Etes vous sur de voiloir supprimer ?')){
		window.location.href = '{$Helper->getLink("contacts/delete/'+id+'")}';
	}
}

function sendUpload(){    
	$("#uploadStatus").html('<div class="alert alert-info"><img src="{$config.url}web/images/lightbox-ico-loading.gif" alt="" /><br/>Enregistrement en cour ...</div></div>');
	return true;
}

function endUpload(result){
	if( result == 'echec'){
		$("#uploadStatus").html('<div class="alert alert-error">Une erreur est survenu pendant le traitement</div>');
		$("#FormBienEdit").css('display','block');
	}else{
		alert('Fichier enregistré');
		//$("#uploadStatus").html('<div class="alert alert-success">Bien enregistre</div>');
		ReloadFiles({$contact.contact_id});
	    $('#uploadStatus').html('');
	}
}

{if $smarty.session.utilisateur.mailing_adm == 1 || $smarty.session.utilisateur.isAdmin > 0}
function deleteSuivi(sid){
	if(confirm('Etes vous sur de vouloir supprimer ce suivi ?')){
		window.location.href = '{$Helper->getLink("contacts/suividelete/'+sid+'")}';
	}
}

function deleteFile(fid){
	if(confirm('Etes vous sur de vouloir supprimer ce fichier ?')){
		window.location.href = '{$Helper->getLink("contacts/file_delete/'+fid+'")}';
	}
}
{/if}

function GetFormAddPhone(cid){
	$.get(
        '{$Helper->getLink("contacts/AjaxAddPhone/'+cid+'")}',{literal}
        {nohtml:'nohtml'},{/literal}
        function(data){
            $("#modal-fiche-contacts-body").html(data);
        }
        
    );

    $('#modal-fiche-contact-label').html('Nouveau telephone');
    $('#modal-fiche-contacts').modal('show');
}

function DeletePhone(pid){
	if(confirm('Etes vous sur de vouloir supprimer ce téléphone ?')){
		window.location.href = '{$Helper->getLink("contacts/phone_delete/'+pid+'")}';
	}
}

function ReloadFiles(cid){
	// Clean table
	$('#table-contacts-files').find("tr:gt(0)").remove();
	// Ajax query
	$.get(
        '{$Helper->getLink("contacts/get_files/'+cid+'")}',{literal}
        {nohtml:'nohtml',json: 'json'},
        function(data){
            var tpl = '<tr><td><a href="{{url_download}}" title="" target="_blank">{{name}}</td><td>{{identifiant}}</td><td>{{date_add}}</td></tr>';
        	for( var i in data ){      
            	$('#table-contacts-files').append( Mustache.render(tpl, data[i]) );
        	}
        },'json'
        {/literal}
    );
}
{if !empty($contact.raison_social) && $contact.mother == 1}
function agenceRemove(aid, mother){
	if(confirm('Etes vous sur de vouloir retirer cette agence ?')){
		window.location.href = '{$Helper->getLink("contacts/agence_remove?agence_id='+aid+'&mother='+mother+'")}'
	}
}
{/if}

// Gmap3
jQuery(document).ready(function(){
	$("#map-contacts").gmap3({
    	marker:{
      	//address: "Haltern am See, Weseler Str. 151"
      	latLng:[{$contact.lat}, {$contact.lng}]
    	},
    	map:{
      		options:{
        		zoom: 14
  			}
		}
  	});
});


//-->
</script>