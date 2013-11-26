<!-- view/import/dejaimpoter.tpl -->
<ul class="breadcrumb">
	<li><a href="{$Helper->getLink("index")}">Accueil</a><span class="divider">>></span></li>
	<li>Erreur</li>
</ul>

<div class="well">
	<div class="alert alert-block">
		<h4>Fichier deja importer</h4>
		<p>Ce fichier a deja ete importe le : {$import.date_import} par {$import.user_import}</p>
	</div>
	<br/><br/>
	<a href="{$Helper->getLink("import")}" class="btn btn-inverse">Retour</a>
</div>