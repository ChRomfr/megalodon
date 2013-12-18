<!-- START adm/contacts_services -->
{strip}
<ol class="breadcrumb">
	<li><a href="{$Helper->getLink("index")}" title="Accueil"><i class="fa fa-home"></i>&nbsp;&nbsp;Accueil</a></li>
	<li><a href="{$Helper->getLink("adm")}" title="Administration"><i class="fa fa-dashboard"></i>&nbsp;&nbsp;Administration</a></li>
	<li><a href="{$Helper->getLink("adm/contacts")}" title="Contacts"><i class="fa fa-users"></i>&nbsp;&nbsp;Contacts</a></li>
	<li class="active"><i class="fa fa-flag"></i>&nbsp;&nbsp;Services</li>
</ol>

<div class="well">
	<div class="pull-right">
		<a href="javascript:get_form_new_service();" title="Nouveau service"><i class="fa fa-plus fa-lg"></i></a>
	</div>
	<div class="clearfix"></div>
	<h4><i class="fa fa-flag fa-x3"></i>&nbsp;Services</h4>
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
			{foreach $services as $service}
			<tr>
				<td>{$service.id}</td>
				<td>{$service.libelle}</td>
				<td>
					<a href="javascript:get_form_edit_service({$service.id})" title="Modifier"><i class="fa fa-edit"></i><a>
					&nbsp;&nbsp;
					<a href="javascript:delete_service({$service.id});" title="Supprimer"><i class="fa fa-trash-o"></i></a>
				</td>
			</tr>
			{/foreach}
		</tbody>
	</table>
</div>
{/strip}
<script type="text/javascript">
<!--
function delete_service(sid){
	if(confirm('Etes vous sur de vouloir supprime ce service ?')){
		window.location.href = '{$Helper->getLink("adm/contacts_services_delete/'+sid+'")}';
	}
}

function get_form_new_service(){
	$.get(
        '{$Helper->getLink("adm/contacts_services_load_form")}',{literal}
        {nohtml:'nohtml'},{/literal}
        function(data){
            $("#modal-global-body").html(data);
        }        
    );
    $('#modal-global-label').html('<i class="fa fa-flag"></i>&nbsp;&nbsp;Nouveau service');
    $('#modal-global').modal('show');
}

function get_form_edit_service(pid){
	$.get(
        '{$Helper->getLink("adm/contacts_services_load_form/'+pid+'")}',{literal}
        {nohtml:'nohtml'},{/literal}
        function(data){
            $("#modal-global-body").html(data);
        }        
    );
    $('#modal-global-label').html('<i class="fa fa-flag"></i>&nbsp;&nbsp;Modification service');
    $('#modal-global').modal('show');
}
//-->
</script>
<!-- END adm/contacts_services -->