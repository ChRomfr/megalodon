<!-- view/categorie/index.tpl -->
{strip}
<ol class="breadcrumb">
	<li><a href="{$Helper->getLink("index")}" title="Accueil"><i class="fa fa-home"></i>&nbsp;Accueil</a></li>
	<li><a href="{$Helper->getLink("adm")}" title="Administration"<i class="fa fa-dashboard"></i>&nbsp;Administration</a></li>
	<li><a href="{$Helper->getLink("adm/contacts")}" title="{$lang.Contacts}"><i class="fa fa-group"></i>&nbsp;{$lang.Contacts}</a></li>
	<li class="active">Catégorie</li>
</ol>

<div class="well">
	<div class="pull-right">
		<a href="{$Helper->getLink("categorie/add")}" title=""><i class="fa fa-plus fa-lg"></i></a>
	</div>
	<h4>Catégories</h4>
	<div class="clearfix"></div>
	<table class="table table-striped">
		<thead>
			<tr>
				<th>Categorie</th>
				<th>Action</th>
			</tr>
		</thead>
		<tbody>
			{foreach $categories as $row}
			<tr>
				<td>{$row.libelle}</td>
				<td>
					<!-- move -->
					<a href="{$Helper->getLink("categorie/move/{$row.id}")}" title="Deplacer les elements de la categorie vers une autre"><i class="fa fa-share"></i></a>
					&nbsp;&nbsp;&nbsp;
					<!-- edit -->
					<a href="{$Helper->getLink("categorie/edit/{$row.id}")}" title="Edition"><i class="glyphicon glyphicon-edit"></i></a>
					&nbsp;&nbsp;&nbsp;
					<!-- delete -->
					<a href="javascript:deleteCategorie({$row.id}, '{$row.libelle}')" title="Supprimer"><i class="glyphicon glyphicon-trash"></i></a>
				</td>
			</tr>
			{/foreach}
		</tbody>
	</table>
</div><!-- /well -->
{/strip}
<script type="text/javascript">
<!--
function deleteCategorie(cid, clibelle){
	if(confirm('Etes vous de vouloir supprimer la categorie : '+clibelle+' ?')){
		window.location.href='{$Helper->getLink("categorie/delete/'+cid+'")}';
	}
}
//-->
</script>