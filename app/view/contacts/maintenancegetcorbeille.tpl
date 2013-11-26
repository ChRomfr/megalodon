{* app/view/contacts/maintenancegetcorbeille.tpl *}
{strip}
<hr/>
<h4>Elements à la corbeille</h4>
<table class="table table-condensed table-striped">
	<thead>
		<tr>
			<th>#</th>
			<th>Contact</th>
			<th></th>
		</tr>
	</thead>
	<tbody>
		{foreach $contacts as $row}
		<tr>
			<td>{$row.id}</td>
			<td>{if !empty($row.raison_social)}{$row.raison_social}{else}{$row.prenom}&nbsp;{$row.nom}{/if}</td>
			<td><a href="javascript:restorecontact({$row.id});" title="Restaurer" class="btn">Restaurer</a></td>
		</tr>
		{/foreach}
	</tbody>
</table>
{/strip}
<script type="text/javascript">
<!--
function restorecontact(cid){
	if(confirm('Etes vous sur de vouloir restaurer ce contact ?')){
		$("#maintenance-ctcs-corbeille").html('Traitement ...');
		$.get(
	        '{$Helper->getLink("contacts/ajaxrestore/'+cid+'")}',{literal}
	        {nohtml:'nohtml'},{/literal}
	        function(data){
	            $("#maintenace-ctcs-corbeille").html(data);
	        }
	    );
	}
}
//-->
</script>