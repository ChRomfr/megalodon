<!-- view/entreprise/fiche.tpl -->
{strip}
<ul class="breadcrumb">
	<li><a href="{$Helper->getLink("index/index")}" title="Accueil">Accueil</a><span class="divider">>></span><li>
	<li><a href="
		{if isset($smarty.server.HTTP_REFERER) && !empty($smarty.server.HTTP_REFERER) && stripos($smarty.server.HTTP_REFERER,'contact') === FALSE}
		{$smarty.server.HTTP_REFERER}
		{else}
		{$Helper->getLink("entreprise")}
		{/if}
		" title="Retour a la liste">Liste</a><span class="divider">>></span></li>
	<li>{$entreprise.raison_social}</li>
</ul>

<div class="well">
	<div class="pull-right">
		<a href="{$Helper->getLink("entreprise/edit/{$entreprise.id}")}" title=""><i class="icon icon-edit"></i></a>
		&nbsp;&nbsp;
		<a href="javascript:deleteEntreprise({$entreprise.id}, '{$entreprise.raison_social}')" title=""><i class="icon icon-trash"></i></a>
	</div>
	<h3>{$entreprise.raison_social}</h3>
	<div class="clearfix"></div>
	<table class="table">
		
		<tr>
			<td>Adresse :</td>
			<td><div class="pull-left">{$entreprise.adresse1}<br/>{$entreprise.adresse2}<br/>{$entreprise.code_postal} {$entreprise.ville}</div><div id="map-ets" style="width:300px; height:300px;" class="pull-right"></div><div class="clearfix"></div></td>
		</tr>
		
		{if !empty($entreprise.telephone)}
		<tr>
			<td>Telephone :</td>
			<td>{$entreprise.telephone}</td>
		</tr>
		{/if}
		
		{if !empty($entrepise.fax)}
		<tr>
			<td>Fax :</td>
			<td>{$entreprise.fax}</td>
		</tr>
		{/if}
		
		{if !empty($entreprise.email)}
		<tr>
			<td>Email :</td>
			<td><a href="#modal-email" title="" data-toggle="modal">{$entreprise.email}</a></td>
		</tr>
		{/if}
		
		<tr>
			<td>Siret :</td>
			<td>{$entreprise.siret} <a href="{$config.lien_societe}{$entreprise.siret}" target="_blank" title="Infos societe"><i class="icon icon-info-sign"></i></a></td>
		</tr>
		
		<tr>
			<td>Effectif :</td>
			<td>{$entreprise.effectif}</td>
		</tr>

		{if !empty($categories)}
		<tr>
			<td>Catégorie :</td>
			<td>
				{foreach $categories as $category}
				<li>{$category.libelle}</li>
				{/foreach}
			</td>
		</tr>
		{/if}

		{if !empty($entreprise.secteur)}
		<tr>
			<td>Secteur :</td>
			<td>{$entreprise.secteur}</td>
		</tr>
		{/if}
		
	</table>
	
	{if count($contacts) == 0}
	<div class="alert alert-info">
		<div class="text-center">
			<strong>Aucun contact directe pour cette entreprise</strong>
			<br/>
			<a href="{$Helper->getLink("contact/add/{$entreprise.id}")}" title="Ajouter un contact" class="btn">Ajouter</a>
		</div>
	</div>
	{else}
		<div class="pull-right">
			<a href="{$Helper->getLink("contact/add/{$entreprise.id}")}" title="Ajouter un contact"><i class="icon-plus"></i></a>
		</div>
		<div class="clearfix"></div>
		<table class="table table-condensed table-striped table-bordered">
			<thead>
				<tr>
					<th>Contact</th>
					<th>Service</th>
					<th>Poste</th>
					<th>Email</th>
					<th>Telephone</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				{foreach $contacts as $row}
				<tr>
					<td>{$row.prenom} {$row.nom}</td>
					<td>{$row.service}</td>
					<td>{$row.poste}</td>
					<td><a href="mailto:{$row.email}">{$row.email}</a></td>
					<td>{$row.telephone}{if !empty($row.portable)}<br/>{$row.portable}{/if}</td>
					<td>
						<a href="{$Helper->getLink("contact/edit/{$row.id}")}" title="Modifier"><i class="icon icon-edit"></i></a>
						&nbsp;&nbsp;
						<a href="javascript:deleteContact({$row.id},'{$row.prenom} {$row.nom}');" title="Supprimer"><i class="icon icon-trash"></i></a>
					</td>
				</tr>
				{/foreach}
			</tbody>
		</table>
	{/if}
	
</div>{* /well *}

{* Modal avec formulaire email *}
<div id="modal-email" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
		<h3 id="myModalLabel">Envoie email</h3>
	</div>
	<div class="modal-body">
		<form method="post" action="{$Helper->getLink("entreprise/sendemail/{$entreprise.id}")}" id="form-email-ets" class="form">
			<label>A :</label>
			<span class="uneditable-input">{$entreprise.email}</span>
			<input type="hidden" name="mail[a]" value="{$entreprise.email}" />
			<label>De :</label>
			<select name="mail[de]" required>
				<option value="{$smarty.session.utilisateur.email}">{$smarty.session.utilisateur.email}</option>
			</select>
			<label>Sujet :</label>
			<input type="text" name="mail[sujet]" id="sujet" required />
			<label>Message :</label>
			<textarea name="mail[body]" required id="body-email" cols="40" rows="6"></textarea>
	</div>
	<div class="modal-footer">
		<button class="btn" data-dismiss="modal" aria-hidden="true">Annuler</button>
		<button type="submit" class="btn btn-primary">Envoyer</button>
	</div>
		</form>
</div>
{/strip}

<script type="text/javascript">
<!--
function deleteEntreprise(eid, eraisonsocial){
	if(confirm('Etes vous sur de vouloir supprimer cette entreprise : '+ eraisonsocial +' ?')){
		window.location.href='{$Helper->getLink("entreprise/delete/'+eid+'")}';
	}
}

function deleteContact(cid, clibelle){
	if(confirm('Etes vous sur de vouloir supprimer ce contact : '+ clibelle +' ?')){
		window.location.href='{$Helper->getLink("contact/delete/'+cid+'")}';
	}
}
//-->
</script>

<script src="http://maps.googleapis.com/maps/api/js?sensor=false" type="text/javascript"></script> 
<script type="text/javascript">
var globalCluster;
{literal}
$(function(){
	$('#map-ets').gmap3({
		map:{
			option:{
				center:[46.578498,2.457275],
              	zoom: 4,
              	mapTypeId: google.maps.MapTypeId.TERRAIN
			}
		},
		marker:{
			values:
			{/literal}{$markers}{literal}
			,
			cluster:{
				radius:100,
				0:{
					content: '<div class="cluster cluster-1">CLUSTER_COUNT</div>',
					width:53,
					height:52
				},
				20:{
					content: '<div class="cluster cluster-2">CLUSTER_COUNT</div>',
					width:56,
					height:55
				},
				50:{
					content: '<div class="cluster cluster-3">CLUSTER_COUNT</div>',
					width:66,
					height:65
				},
				events: {
	                click: function(cluster) {
	                  var map = $(this).gmap3("get");
	                  map.setCenter(cluster.main.getPosition());
	                  map.setZoom(map.getZoom() + 1);
	                }
              	}
			},

			events:{
              mouseover: function(marker, event, context){
              	//console.log(context.data);
                $(this).gmap3(
                  {clear:"overlay"},
                  {
                  overlay:{
                    latLng: marker.getPosition(),
                    options:{
                      content:  '<div class="infobulle"><div class="bg"></div><div class="text">'+ context.data.rs +'<br/>'+context.data.adresse+'<br/>'+context.data.code_postal+' '+ context.data.ville +'</div></div>',
                      offset: {
                        x:-46,
                        y:-73
                      }
                    }
                  }
                });
              },
              mouseout: function(){
                $(this).gmap3({clear:"overlay"});
              }
            }
		},
	});
});
{/literal}
$(document).ready(function()	{
   $('#body-email').markItUp(mySettings);
});
</script>