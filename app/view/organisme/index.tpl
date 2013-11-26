{strip}
<ul class="breadcrumb">
	<li><a href="{$Helper->getLink("index")}" title="">Accueil</a><span class="divider">>></span></li>
	<li class="active">Organimes</li>
</ul>

<div class="well">
	<div class="pull-right">
		<a href="{$Helper->getLink("organisme/add")}"><i class="icon icon-add"></i></a>
	</div>
	<h3>Organismes</h3>
	<div class="clearfix"></div>

	<table class="table table-striped">
		<thead>
			<tr>
				<th>#</th>
				<th>Organisme</th>
				<th>Action</th>
			</tr>
		</thead>
		<tbody>
			{foreach $organismes as $row}
			<tr>
				<td>{$row.id}</td>
				<td>{$row.libelle}</td>
				<td>
					<a href="{$Helper->getLink("organisme/edit/{$row.id}")}" title=""><i class="icon-pencil"></i></a>
					&nbsp;&nbsp;
					<a href="javascript:delorganisme({$row.id})" title=""><i class="icon-trash"></i></a>
				</td>
			</tr>
			{/foreach}
		</tbody>
	</table>
</div>
{/strip}
<script type="text/javascript">
<!--
function delorganisme(id){
	if(confirm('Etes vous sur de vouloir supprime cet organisme ?')){
		window.location.href='{$Helper->getLink("organisme/delete/'+id+'")}';
	}
}
//-->
</script>