{strip}
<h5>{if !empty($contact.raison_social)}{$contact.raison_social}{else}{$contact.prenom}&nbsp;{$contact.nom}{/if}</h5>
{if $contact.ctype == 'societe' && empty($contact.siret)}
<div class="alert alert-warning">Societe sans numéro de SIRET</div>
{/if}

{if $contact.has_tel == 0}
<div class="alert alert-warning">Ce contact n'a pas de numéro de téléphone</div>
{/if}
<table class="table table-condensed table-striped">
	<tr>
		<td>Type :</td>
		<td>{if $contact.ctype == 'societe'}Societe
			{elseif $contact.ctype == 'societe_contact'}Professionel
			{else}Particulier
			{/if}
		</td>
	</tr>
	{if $contact.ctype == 'societe' && !empty($contact.siret)}
	<tr>
		<td>Siret :</td>
		<td>{$contact.siret}</td>
	</tr>
	{/if}
	
	{if !empty($contact.email)}
	<tr>
		<td>Email :</td>
		<td>{$contact.email}</td>
	{/if}

	{foreach $contact.telephones as $row}
	<tr>
		<td>
			{if $row.type == 1}Ligne directe
			{elseif $row.type == 2}Standard
			{elseif $row.type == 3}Domicile
			{elseif $row.type == 4}Mobile
			{elseif $row.type == 5}Fax
			{/if}
		</td>
		<td>{$row.telephone}</td>
	</tr>
	{/foreach}
</table>
{/strip}