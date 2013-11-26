<ul class="breadcrumb">
	<li><a href="{$Helper->getLink("index")}" title="Accueil">Accueil</a><span class="divider">&gt;&gt;</span></li>
	<li><a href="{$Helper->getLink("mailing")}" title="Mailing">Mailing</a><span class="divider">&gt;&gt;</span></li>
	<li>Calendrier</li>
</ul>

<div class="well">
	<div id="calmailing" style="width:80%;"></div>
</div>

<script type="text/javascript">
<!--
$.getJSON(
	'{$Helper->getLink("mailing/getmailingforcalendar")}', {literal}
	{nohtml:'nohtml'},{/literal}   
	function(data){ 
		
		$(document).ready(function() {
	
			$('#calmailing').fullCalendar({
				header: {
					left: 'prev,next today',
					center: 'title',
					right: 'month,basicWeek,basicDay'
				},
				firstDay:1,
				editable: false,
				events:data,		
			});	
		}); 		
	}
);
//-->
</script>