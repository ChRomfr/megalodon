<!-- view/importafpi/index.tpl -->
{strip}
<ul class="breadcrumb">
	<li><a href="{$Helper->getLink("index")}" title="Accueil">Accueil</a></li>
	<li class="active">Import - AFPI</li>
</ul>

<div class="well">
	<form method="post" action="#" enctype="multipart/form-data">
		<fieldset>
			<legend>Importation - AFPI</legend>
			Pré requis : 
				<ul class="">
					<li>Fichier au format CSV</li>
					<li>Sepateur <strong>;</strong></li>
				</ul>
			<br/>
			<div class="form-group">
				<label class="col-sm-2 control-label">Fichier :</label>
				<div class="col-sm-5">
				<input type="file" name="file_import" id="file_import" class="form-control"/>
				</div>
			</div>	
			
		</fieldset>
		<div class="text-center">
			<hr/>
			<input type="hidden" name="token" value="{$smarty.session.token}" />
			<button type="submit" class="btn btn-primary">Envoyer</button>
			&nbsp;&nbsp;
			<a href="{$Helper->getLink("index")}" class="btn btn-danger">Annuler</a>
		</div>
	</form>
</div>
{/strip}