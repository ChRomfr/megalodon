{* Tab contenant la liste des fichiers lié a un contacts *}
{strip}
<div id="tabContactsFiles" class="tab-pane">
	<br/>
	<div class="pull-right">
		<a href="javascript:get_form_add_file({$contact.id});" title="" class="btn btn-default"><i class="fa fa-lg fa-cloud-upload"></i></a>
	</div>
	<div class="clearfix"></div>

	<table class="table table-condensed table-striped" id="table-contacts-files">
		<thead>
			<tr>
				<th>Fichier</th>
				<th>Utilisateur</th>
				<th>Date d'ajout</th>
				{if $smarty.session.utilisateur.isAdmin > 0}
				<th></th>
				{/if}
			</tr>
		</thead>
		<tbody>
		</tbody>
	</table>			
</div>
{/strip}