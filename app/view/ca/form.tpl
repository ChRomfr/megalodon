{* FORMULAIRE PERMETTANT AJOUTER/MODIFIER UN CA LIE A UN CONTACT *}
{strip}
<form method="post" id="form-contacts-ca" class="form-horizontal" {if !isset($ca)}action="{$Helper->getLink("ca/add/{$contact_id}")}"{else}action="{$Helper->getLink("ca/edit/{$contact_id}?ca_id={$ca.id}")}"{/if}>
	<div class="form-group">
		<label for="montant" class="col-sm-3">Montant :</label>
		<div class="col-sm-6">
			<input type="text" name="ca[montant]" required id="montant" class="form-control" placeholder="1000" {if isset($ca)}value="{$ca.montant}"{/if}/>
		</div>
	</div>
	<div class="form-group">
		<label for="ca-statut" class="col-sm-3">Statut :</label>
		<div class="col-sm-6">
			<select name="ca[statut]" required id="ca-statut" class="form-control">
				<option></option>
				<option value="1" {if isset($ca) && $ca.statut == 1}selected="selected"{/if}>Prévision</option>
				<option value="2" {if isset($ca) && $ca.statut == 2}selected="selected"{/if}>Réalisé</option>
				<option value="3" {if isset($ca) && $ca.statut == 3}selected="selected"{/if}>Annuler</option>
			</select>
		</div>
	</div>
	<div class="form-group">
		<label for="date-ca" class="col-sm-3">Date :</label>
		<div class="col-sm-6">
			<input type="text" name="ca[date_ca]" required id="date-ca" class="form-control" placeholder="{date('Y-m-d')}" data-format="YYYY-MM-DD" {if isset($ca)}value="{$ca.date_ca}"{/if}/>
		</div>
	</div>
	<hr/>
	<div class="text-center">
		{if isset($ca)}
		<input type="hidden" name="ca[id]" value="{$ca.id}" required />
		<input type="hidden" name="ca[user_id]" value="{$ca.user_id}" required />
		<input type="hidden" name="ca[date_add]" value="{$ca.date_add}" required />
		{/if}
		<input type="hidden" name="ca[contact_id]" value="{$contact_id}" required />
		<button type="submit" class="btn btn-primary">Enregistrer</button>
	</div>
</form>
{/strip}
<script type="text/javascript">
$(document).ready(function(){
    $('#date-ca').datetimepicker({
        icons: {
            time: "fa fa-clock-o",
            date: "fa fa-calendar",
            up: "fa fa-arrow-up",
            down: "fa fa-arrow-down"
        }
    });
})
</script>