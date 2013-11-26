{* app/view/contact/index.tpl *}
{strip}
<ul class="breadcrumb">
	<li><a href="{$Helper->getLink("index")}" title="Accueil">Accueil</a><span class="divider">&gt;&gt;</span></li>
	<li class="active">Contacts</li>
</ul>

<div class="well">

	<div class="pull-right">
		<a href="{$Helper->getLink("contacts/add")}" title="Nouveau contact"><i class="icon icon-plus"></i></a>
	</div>
	<div class="clearfix"></div>

	<form method="post" action="{$Helper->getLink("contacts/massdelete")}" onsubmit="return confirSendForm();">

	<table class="table table-condensed table-striped">
		<thead>
			<tr>
				<th>Contact</th>
				<th>Ville</th>
				<th>Email</th>
				<th>Type</th>
				{if $smarty.session.utilisateur.isAdmin > 0}
				<th></th>
				{/if}
			</tr>
		</thead>
		<tbody>
			{foreach $contacts as $row}
			<tr>
				<td><a href="{$Helper->getLink("contacts/detail/{$row.id}")}" title="Detail">{$row.nom} {if !empty($row.prenom)}{$row.prenom}{/if}</a></td>
				<td>{$row.ville}</td>
				<td>{$row.email}</td>
				<td>
					{if $row.ctype == 'societe'}Societe
					{elseif $row.ctype == 'societe_contact'}Pro
					{else}Par
					{/if}
				</td>
				{if $smarty.session.utilisateur.isAdmin > 0}
				<td>
					<a href="javascript:deleteContact({$row.id});" title="Supprimer"><i class="icon icon-trash"></i></a>
					&nbsp;&nbsp;
					<input type="checkbox" name="contacts[][{$row.id}]" />
				</td>
				{/if}
			</tr>
			{/foreach}
		</tbody>
	</table>

	{if isset($Pagination)}
	<div class="pagination">
		{$Pagination->render()}
	</div>
	{/if}

	<div class="pull-right">
		<a href="{$Helper->getLink("contacts/csv")}?{$smarty.server.QUERY_STRING}&amp;csv" title="CSV" target="_blan" class="btn">CSV</a>
		&nbsp;&nbsp;
		<button type="submit" class="btn btn-warning"><i class="icon icon-trash"></i>Supprimer</button>
	</div>

	<div class="clearfix"></div>
	</form>
</div>{* /well *}
{/strip}
<script type="text/javascript">
<!--
function deleteContact(id){
	if(confirm('Etes vous de vouloir supprimer ce contact ?')){
		window.location.href = '{$Helper->getLink("contacts/delete/'+id+'")}';
	}
}

function confirSendForm(){
	if(confirm('Etes vous sur de vouloir supprimer ces contacts ?')){
		return true;
	}

	return false;
}
//-->
</script>