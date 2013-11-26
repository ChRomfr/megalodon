<!-- START view/entreprise/checkadremail.tpl -->
{strip}
<p>Email invalide : {$email_invalide}</p>
<br/>
<table class="table table-striped table-condensed" id="liste-emails">
	{foreach $emails as $row}
	<tr>
		<td>{$row.email}</td>
		<td><a href="{$Helper->getLink("entreprise/edit/{$row.id}")}" target="_blank" title="Corriger"><i class="icon icon-edit"></i></a></td>
		<td><a href="javascript:deleteEmail('{$row.email}');" title=""><i class="icon icon-trash"></i>
	</tr>
	{/foreach}
</table>
{/strip}
<script type="text/javascript">
<!--
function deleteEmail(email){
	if(confirm('Etes vous sur de vouloir retirer cette adresse e-mail ?')){
		// Requete ajax
		$.get(
	        '{$Helper->getLink("entreprise/removeemailajax/'+email+'")}',{literal}
	        {nohtml:'nohtml'},
	        function(data){
	            $('#result-cleandata').html(data);
	        }
	    );
	    {/literal}
	}
}
//-->
</script>
<!-- END view/entreprise/checkadremail.tpl -->