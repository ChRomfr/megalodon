<!-- START adm/contacts_postes -->
{strip}
<ol class="breadcrumb">
	<li><a href="{$Helper->getLink("index")}" title="Accueil"><i class="fa fa-home"></i>&nbsp;&nbsp;Accueil</a></li>
	<li><a href="{$Helper->getLink("adm")}" title="Administration"><i class="fa fa-dashboard"></i>&nbsp;&nbsp;Administration</a></li>
	<li><a href="{$Helper->getLink("adm/contacts")}" title="Contacts"><i class="fa fa-users"></i>&nbsp;&nbsp;Contacts</a></li>
	<li class="active"><i class="fa fa-suitcase"></i>&nbsp;&nbsp;Postes</li>
</ol>

<div class="well">
	<div class="pull-right">
		<a href="javascript:get_form_new_poste();" title="Nouveau poste"><i class="fa fa-plus fa-lg"></i></a>
	</div>
	<div class="clearfix"></div>
	<h4><i class="fa fa-suitcase fa-x3"></i>&nbsp;Postes</h4>
	<br/>
	<table class="table table-striped">
		<thead>
			<tr>
				<th>#</th>
				<th>Libelle</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			{foreach $postes as $poste}
			<tr>
				<td>{$poste.id}</td>
				<td>{$poste.libelle}</td>
				<td>
					<a href="javascript:get_form_edit_poste({$poste.id})" title="Modifier"><i class="fa fa-edit"></i><a>
					&nbsp;&nbsp;
					<a href="javascript:delete_poste({$poste.id});" title="Supprimer"><i class="fa fa-trash-o"></i></a>
				</td>
			</tr>
			{/foreach}
		</tbody>
	</table>
</div>
{/strip}
<script type="text/javascript">
<!--
function delete_poste(pid){
	if(confirm('Etes vous sur de vouloir supprime ce poste ?')){
		window.location.href = '{$Helper->getLink("adm/contacts_postes_delete/'+pid+'")}';
	}
}

function get_form_new_poste(){
	$.get(
        '{$Helper->getLink("adm/contacts_postes_load_form")}',{literal}
        {nohtml:'nohtml'},{/literal}
        function(data){
            $("#modal-global-body").html(data);
        }        
    );
    $('#modal-global-label').html('<i class="fa fa-suitcase"></i>&nbsp;&nbsp;Nouveau poste');
    $('#modal-global').modal('show');
}

function get_form_edit_poste(pid){
	$.get(
        '{$Helper->getLink("adm/contacts_postes_load_form/'+pid+'")}',{literal}
        {nohtml:'nohtml'},{/literal}
        function(data){
            $("#modal-global-body").html(data);
        }        
    );
    $('#modal-global-label').html('<i class="fa fa-suitcase"></i>&nbsp;&nbsp;Modification poste');
    $('#modal-global').modal('show');
}
//-->
</script>
<!-- END adm/contacts_postes -->