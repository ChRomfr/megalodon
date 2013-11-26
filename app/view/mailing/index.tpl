{strip}
<ol class="breadcrumb">
	<li><a href="{$Helper->getLink("index")}" title="Accueil">Accueil</a></li>
	<li class="active">Mailings</li>
</ol>

<div class="well">
	<table class="table table-striped">
		<thead>
			<tr>
				<th>#</th>
				<th>Mailing</th>
				<th>Status</th>
				<th>Demandeur</th>
				<th>Date de demande</th>
				<th>Date d'envoie</th>
			</tr>
		</thead>
		<tbody>
		{foreach $mailings as $mailing}
			<tr>
				<td>{$mailing.id}</td>
				<td><a href="{$Helper->getLink("mailing/fiche/{$mailing.id}")}" title="Detail">{$mailing.libelle}</a></td>
				<td>
					{if $mailing.valid == 0}<span class="label label-info">A valider</span>
					{elseif $mailing.valid == 1}<span class="label label-success">Accepter</span>
					{elseif $mailing.valid == 2}<span class="label label-warning">Refuser</span>
					{/if}
				</td>
				<td>{$mailing.demandeur}</td>
				<td>{$mailing.date_wish}</td>
				<td>{$mailing.date_send}</td>
			</tr>
		{/foreach}
		</tbody>
	</table>
</div>{* /well *}
{/strip}