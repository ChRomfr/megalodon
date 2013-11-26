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
		<select name="contact[per][societe_id]" id="contact-per-societe" class="chozen form-control">
			<option value=""></option>
			{foreach $entreprises as $row}
			<option value="{$row.id}" {if isset($smarty.get.societe) && !empty($smarty.get.societe) && $smarty.get.societe == $row.contact_id}selected="selected"{/if}>{$row.raison_social}</option>
			{/foreach}
		</select>
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
{literal}
<script type="text/javascript">
<!--
$(".chozen").chosen();
//-->
</script>
{/literal}
{/if}
{/strip}