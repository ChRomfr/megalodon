{strip}
<ul class="breadcrumb">
	<li><a href="{$Helper->getLink("index")}" title="Accueil">Accueil</a><span class="divider">>></span></li>
	<li>Mailing<span class="divider">>></span></li>
	<li>Type</li>
</ul>

<div class="well">
	<div class="pull-right">
		<a href="{$Helper->getLink("mailing_type/add")}" title=""><i class="icon icon-add"></i></a>
	</div>
	<h4>Type de mailing</h4>
	<div class="clearfix"></div>
	<table class="table table-striped">
		<thead>
			<tr>
				<th>Type</th>
				<th>Action</th>
			</tr>
		</thead>
		<tbody>
			{foreach $types as $row}
			<tr>
				<td>{$row.libelle}</td>
				<td>
					<!-- edit -->
					<a href="{$Helper->getLink("mailing_type/edit/{$row.id}")}" title="Edition"><i class="icon-pencil"></i></a>
					&nbsp;&nbsp;&nbsp;
					<!-- delete -->
					<a href="javascript:deleteType({$row.id}, '{$row.libelle}');" title="Supprimer"><i class="icon-trash"></i></a>
				</td>
			</tr>
			{/foreach}
		</tbody>
	</table>
</div><!-- /well -->
{/strip}
<script type="text/javascript">
<!--
function deleteType(cid, clibelle){
	if(confirm('Etes vous de vouloir supprimer ce type : '+clibelle+'?')){
		window.location.href='{$Helper->getLink("mailing_type/delete/'+cid+'")}';
	}
}
//-->
</script>