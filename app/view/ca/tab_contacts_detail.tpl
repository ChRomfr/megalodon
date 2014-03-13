{* TAB CA FICHE CONTACTS *}
{strip}
<div id="tabCA" class="tab-pane">
	<div class="pull-right">
		<a href="javascript:get_form_ca({$contact.contact_id})" title=""><i class="fa fa-plus fa-lg"></i></a>
	</div>
	<div class="clearfix"></div>
	<table class="table table-striped table-condensed" id="table-contacts-ca">
		<thead>
			<tr>
				<th>Auteur</th>
				<th>Status</th>
				<th>Montant</th>
				<th>Date</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			{foreach $cas as $row}
			<tr>
				<td>{if $smarty.session.utilisateur.id == $row.user_id}{$smarty.session.utilisateur.identifiant}{else}<i class="fa fa-user"></i>&nbsp;#{$row.user_id}{/if}</td>
				<td>{if $row.statut == 1}<span class="text-primary">Prévision</span>{elseif $row.statut == 2}<span class="text-success">Realise</span>{elseif $row.statut == 3}<span class="text-danger">Annulé</span>{/if}</td>
				<td>{if $row.statut == 3}<strike>{/if}{$row.montant}{if $row.statut == 3}</strike>{/if}</td>
				<td>{$row.date_ca}</td>
				<td>
					{if $smarty.session.utilisateur.id == $row.user_id}
					<a href="javascript:get_form_edit_ca({$contact.contact_id}, {$row.id});" title="Modifier"><i class="fa fa-edit"></i></a>
					{/if}
				</td>
			</tr>
			{/foreach}
		</tbody>
	</table>
	<div id="stats-ca"></div>
</div>
{/strip}
<script type="text/javascript">
var cid = {$contact.contact_id};
$(document).on('click', "#atabca", function () {
	$.get(
		base_url + 'index.php/ca/stats_contacts/'+cid,{},
	    function(data){
	        $("#stats-ca").html(data);
	    }
    );
})
</script>