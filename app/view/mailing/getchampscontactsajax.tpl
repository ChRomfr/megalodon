{strip}
{* START poste *}
<div class="control-group">
	<label class="control-label">Poste :</label>		
	<div class="controls">
		<select name="mailing[cible][poste]">
			<option value=""></option>
			{foreach $global_postes as $row}
			<option value="{$row.id}" {if isset($smarty.get.filtre.poste) && $smarty.get.filtre.poste == $row.id}selected="selected"{/if}>{$row.libelle}</option>
			{/foreach}
		</select>
	</div>
</div>
{* END poste *}

{* START service *}
<div class="control-group">
	<label class="control-label">Service :</label>			
	<div class="controls">
		<select name="mailing[cible][service]">
			<option value=""></option>
			{foreach $global_services as $row}
			<option value="{$row.id}" {if isset($smarty.get.filtre.service) && $smarty.get.filtre.service == $row.id}selected="selected"{/if}>{$row.libelle}</option>
			{/foreach}
		</select>
	</div>
</div>
{* END service *}
{/strip}
'