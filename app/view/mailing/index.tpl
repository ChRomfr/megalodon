{strip}
<ol class="breadcrumb">
	<li><a href="{$Helper->getLink("index")}" title="Accueil"><i class="fa fa-home"></i>&nbsp;&nbsp;Accueil</a></li>
	<li class="active"><i class="fa fa-envelope"></i>&nbsp;&nbsp;Mailings</li>
</ol>

<div class="well">
	{if $smarty.session.utilisateur.isAdmin == 1 || isset($smarty.session.acl.mailing_demand)}
	<div class="pull-right">
		<a href="{$Helper->getLink("mailing/add")}" title="Nouveau"><i class="fa fa-plus fa-lg"></i></a>
	</div>
	{/if}
	<div class="clearfix"></div>
	<table class="table table-striped table-condensed">
		<thead>
			<tr>
				<th>#</th>
				<th>Mailing</th>
				<th>Statut</th>
				<th>Type</th>
				<th>Action</th>
				<th>Demandeur</th>
				<th>Date de demande</th>
				<th>Date d'envoie</th>
				<th>Stats</td>
			</tr>
		</thead>
		<tbody>
		{foreach $mailings as $mailing}
			<tr>
				<td>{$mailing.id}</td>
				<td><a href="{$Helper->getLink("mailing/fiche/{$mailing.id}")}" title="Detail">{$mailing.libelle}</a></td>
				<td>
					{if !empty($mailing.date_send)}<span class="label label-success">Envoyé</span>
					{elseif $mailing.valid == 0}<span class="label label-default">A valider</span>
					{elseif $mailing.valid == 1}<span class="label label-info">Accepter</span>
					{elseif $mailing.valid == 2}<span class="label label-warning">Refuser</span>
					{/if}
				</td>
				<td>{$mailing.type}</td>
				<td>{$mailing.action}</td>
				<td>{$mailing.demandeur}</td>
				<td>{$mailing.date_wish}</td>
				<td>{$mailing.date_send}</td>
				<td>
					{if !empty($mailing.stats)}<span class="label label-success">Oui</span>{else}<span class="label label-warning">Non</span>{/if}
				</td>
			</tr>
		{/foreach}
		</tbody>
	</table>
	{if isset($Pagination)}
	<div class="pull-left">{$Pagination->render()}</div>
	{/if}
	<div class="clearfix"></div>
</div>
{/strip}
<script type="text/javascript">
function mailing_add(){
	$.get(
        '{$Helper->getLink("mailing/get_form")}',{literal}
        {nohtml:'nohtml'},{/literal}
        function(data){
            $("#modal-global-body").html('<div class="well">'+data+'</div>');
        }        
    );
    $('#modal-global-label').html('');
    $('#modal-global').modal('show');
}
</script>