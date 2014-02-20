{strip}
<ol class="breadcrumb">
	<li><a href="{$Helper->getLink("index")}" title=""><i class="fa fa-home"></i>&nbsp;&nbsp;Accueil</a></li>
    <li><a href="{$Helper->getLink("adm")}" title="Administration"><i class="fa fa-dashboard"></i>&nbsp;&nbsp;Administration</a></li>
	<li class="active">Organimes</li>
</ol>

<div class="well">
	<div class="pull-right">
		<a href="{$Helper->getLink("organisme/add")}"><i class="fa fa-plus fa-lg"></i></a>
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
					<a href="{$Helper->getLink("organisme/edit/{$row.id}")}" title=""><i class="fa fa-edit"></i></a>
					&nbsp;&nbsp;
					<a href="javascript:delorganisme({$row.id})" title=""><i class="fa fa-trash-o"></i></a>
				</td>
			</tr>
			{/foreach}
		</tbody>
	</table>
</div>
{/strip}
<script type="text/javascript">
function delorganisme(id){
	if(confirm('Etes vous sur de vouloir supprime cet organisme ?')){
		window.location.href= base_url + 'index.php/organisme/delete/'+id;
	}
}
</script>