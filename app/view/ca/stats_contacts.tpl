{* STATS CA SUR ANNEE APPEL DANS LE TAB CA DE LA FICHE CONTACTS *}
{strip}
<hr/>
<h4>Statistiques:</h4>
<table class="table table-striped">
	<thead>
		<tr>
			<th>Année</th>
			<th>CA réalisé</th>
			<th>CA prévision</th>
			<th>Evolution</th>
		</tr>
	</thead>
	<tbody>
		{foreach $stats as $row}
		<tr>
			<td>{$row.year}</td>
			<td>{$row.ca_realise}</td>
			<td>{$row.ca_prevision}</td>
			<td>
				{if $row.ca_evolution > 100}<div class="text-success"><i class="fa fa-sort-desc"></i>&nbsp;
				{elseif $row.ca_evolution == 0}<div class="text-primary">=&nbsp;
				{elseif $row.ca_evolution < 0}<div class="text-danger"><i class="fa fa-sort-asc"></i>&nbsp;
				{/if}{$row.ca_evolution} %
				</div>
			</td>
		</tr>
		{/foreach}
	</tbody>
</table>
{/strip}