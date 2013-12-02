{strip}
<div class="form-group">
	<label class="control-label col-sm-2">Nom :</label>
	<div class="col-sm-5">
		<input type="text" name="contact[per][nom]" id="contact-par-nom" class="form-control"/>
	</div>
</div>

<div class="form-group">
	<label class="control-label col-sm-2">Prenom :</label>
	<div class="col-sm-5">
		<input type="text" name="contact[per][prenom]" id="contact-par-prenom" class="form-control"/>
	</div>
</div>

{if $smarty.get.typesocio == 1}
<div class="form-group">
	<label class="control-label col-sm-2">Societe :</label>
	<div class="col-sm-5">
		<input type="text" id="search_societe" class="form-control" {if isset($smarty.get.societe)}value="{$ets.raison_social}"{/if}/>
		<input type="hidden" name="contact[per][societe_id]" id="societe-id" {if isset($smarty.get.societe)}value="{$smarty.get.societe}"{/if}/>
	</div>
</div>

<div class="form-group">
	<label class="control-label col-sm-2">Poste :</label>
	<div class="col-sm-5">
		<select name="contact[per][poste_id]" id="contact-per-poste" class="form-control chozen">
			<option value=""></option>
			{foreach $global_postes as $row}
			<option value="{$row.id}">{$row.libelle}</option>
			{/foreach}
		</select>
	</div>
</div>

<div class="form-group">
	<label class="control-label col-sm-2">Service :</label>
	<div class="col-sm-5">
		<select name="contact[per][service_id]" id="contact-per-service" class="form-control chozen">
			<option value=""></option>
			{foreach $global_services as $row}
			<option value="{$row.id}">{$row.libelle}</option>
			{/foreach}
		</select>
	</div>
</div>
{/strip}
<script type="text/javascript">
<!--
$(".chozen").chosen();

jQuery(document).ready(function(){
	// binds form submission and fields to the validation engine
	jQuery("#formNewRepas").validationEngine();
	
	$('#search_societe').autocomplete({
		source : '{$Helper->getLink("contacts/ajax_search_societe?nohtml=nohtml")}',
		minLength: 2,
		dataType: "json",
		selectFirst: true,
		delay: 0,
		select: function(e, ui){
			var selectObj = ui.item;
			$(this).val(selectObj.label)
			$("#societe-id").val( ui.item.value );
			return false;
		}
	});	
});
//-->
</script>
{/if}
