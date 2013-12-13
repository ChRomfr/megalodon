{strip}
<ol class="breadcrumb">
	<li><a href="{$Helper->getLink("index")}" title="Accueil"><i class="fa fa-home"></i>&nbsp;&nbsp;Accueil</a></li>
	<li><a href="{$Helper->getLink("mailing")}" title="Mailing"><i class="fa fa-envelope"></i>&nbsp;&nbsp;Mailing</a></li>
	<li class="active"><i class="fa fa-calendar"></i>&nbsp;&nbsp;Calendrier</li>
</ol>

<div class="well">
	<div id="calmailing" style="width:80%;"></div>
</div>
{/strip}
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