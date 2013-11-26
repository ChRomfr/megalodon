<table class="table table-condensed table-striped">
	<thead>
		<tr>
			<th>Utilisateur</th>
			<th>Cumul</th>
			<th>Hits</th>
		</tr>
	</thead>
	<tbody>
	{foreach $stats as $row}
	<tr>
		<td><a href="javascript:detailuserbyip('{$row.ip}')">{$row.ip}</a></td>
		<td>{$row.cumul_format}</td>
		<td>{$row.hits}</td>
	</tr>
	{/foreach}
	</tbody>
</table>