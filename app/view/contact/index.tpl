{strip}
<ul class="breadcrumb">
	<li><a href="{$Helper->getLink("index")}" title="Accueil">Accueil</a><span class="divider">>></span></li>
	<li>Contacts</li>
</ul>
<div class="well">
	<h3>Contact</h3>
	<table class="table table-striped table-condensed">
		<thead>
			<tr>
				<th>Contact</th>
				<th>Entreprise</th>
				<th>Ville</th>
				<th>Email</th>
			</tr>
		</thead>
		<tbody>
			{foreach $contacts as $row}
			<tr>
				<td>{$row.prenom} {$row.nom}</td>
				<td><a href="{$Helper->getLink("entreprise/fiche/{$row.entreprise_id}")}" title="Detail">{$row.raison_social}</a></td>
				<td>{$row.ville}</td>
				<td>{$row.email}</td>
			</tr>
			{/foreach}
		</tbody>
	</table>

	<div class="pull-right">
		csv&nbsp;:&nbsp;
		<a href="{$Helper->getLink("contact/index")}?{$smarty.server.QUERY_STRING}&amp;csv" title="CSV"><small>Contacts</small></a>
		&nbsp;&nbsp;
		<a href="{$Helper->getLink("contact/index")}?{$smarty.server.QUERY_STRING}&amp;csv&amp;ets" title=""><small>Contacts + Entreprises</small></a>
	</div>

	{if isset($Pagination)}
	<ul class="pagination">
		{$Pagination->render()}
	</ul>
	{/if}

	<div class="clearfix"></div>
</div>
{/strip}