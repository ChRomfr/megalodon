{strip}
<ul class="breadcrumb">
	<li><a href="{$Helper->getLink("index")}" title="Accueil">Accueil</a><span class="divider">>></span></li>
	<li>Entreprises</li>
</ul>
<div class="well">
	<h3>Entreprises</h3>
	<table class="table table-striped table-condensed table-bordered">
		<thead>
			<tr>
				<th>Entreprise</th>
				{if isset($smarty.get.filtre.effectif_mini) && !empty($smarty.get.filtre.effectif_mini)}
				<th>Effectif</th>
				{/if}
				<th>Ville</th>
				<th>Email</th>
		</thead>
		<tbody>
			{foreach $Ets as $row}
			<tr>
				<td><a href="{$Helper->getLink("entreprise/fiche/{$row.id}")}" title="Detail">{$row.raison_social}</a></td>
				{if isset($smarty.get.filtre.effectif_mini) && !empty($smarty.get.filtre.effectif_mini)}
				<td>{$row.effectif}</td>
				{/if}
				<td>{$row.ville}</td>
				<td>{$row.email}</td>
			</tr>
			{/foreach}
		</tbody>
	</table>

	<div class="pull-right">
		<a href="{$Helper->getLink("entreprise/index")}?{$smarty.server.QUERY_STRING}&amp;csv" title="CSV">CSV</a>
	</div>
	{if isset($Pagination)}
	<div class="pagination">
		{$Pagination->render()}
	</div>
	{/if}
	<div class="clearfix"></div>
</div>
{/strip}