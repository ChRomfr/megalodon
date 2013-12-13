{strip}
<ol class="breadcrumb">
	<li class="active">Accueil</li>
</ol>

<div class="col-md-5 well">
	<h4><i class="fa fa-signal"></i>&nbsp;&nbsp;Statistiques</h4>
	<table class="table table-striped table-condensed">
		<tr>
			<td>Contacts :</td>
			<td>{$stats.nb_ctcs}</td>
		</tr>
		<tr>
			<td>Societes :</td>
			<td>{$stats.nb_sct}</td>
		</tr>
		<tr>
			<td>Personne :</td>
			<td>{$stats.nb_per}</td>
		</tr>
		<tr>
			<td>Email :</td>
			<td>{$stats.nb_email}</td>
		</tr>
	</table>
</div>
<div class="col-md-1"></div>
<div class="col-md-6 well">
	<h4><i class="fa fa-calendar">&nbsp;&nbsp;</i>Calendrier des mailings</h4>
	{if $smarty.session.utilisateur.mailing_adm == 1}
	<div id="calmailingindex"style="width:95%;"></div>
	<div id="calmailingindex-progress">
		<div class="progress">
			<div class="progress-bar progress-striped active" style="width: 0%;"></div>
	    </div>
	</div>
	{/if}
</div>
<div class="clearfix"></div>

{if $smarty.session.utilisateur.index_map_contacts == 1}
<div class="col-md-12 well">
	<h4><i class="fa fa-globe"></i>&nbsp;&nbsp;Cartes des contacts</h4>
	<div id="map-city" class="gmap3" style="height:500px;"></div>
</div>
{/if}
{/strip}
{if $smarty.session.utilisateur.index_map_contacts == 1}
<script src="http://maps.googleapis.com/maps/api/js?sensor=false" type="text/javascript"></script>
{/if}
<script type="text/javascript">
jQuery(document).ready(function(){
	var progress = setInterval(function() {
	      var $bar = $('.progress-bar');
	      
	      if ($bar.width()=='100%') {
	          //clearInterval(progress);
	          $bar.width('100%');
	          //$('.progress').removeClass('active');
	      } else {
	          $bar.width($bar.width()+40);
	      }
	      //$bar.text($bar.width()/4 + "%");
	  }, 100);
});

{if $smarty.session.utilisateur.index_map_contacts == 1}
var globalCluster;
{literal}
$(function(){
	$('#map-city').gmap3({
		map:{
			option:{
				center:[46.578498,2.457275],
              	zoom: 4,
              	mapTypeId: google.maps.MapTypeId.TERRAIN
			}
		},
		marker:{
			values:
			{/literal}{$Markers}{literal}
			,
			cluster:{
				radius:100,
				0:{
					content: '<div class="cluster cluster-1">CLUSTER_COUNT</div>',
					width:53,
					height:52
				},
				20:{
					content: '<div class="cluster cluster-2">CLUSTER_COUNT</div>',
					width:56,
					height:55
				},
				50:{
					content: '<div class="cluster cluster-3">CLUSTER_COUNT</div>',
					width:66,
					height:65
				},
				events: {
	                click: function(cluster) {
	                  var map = $(this).gmap3("get");
	                  map.setCenter(cluster.main.getPosition());
	                  map.setZoom(map.getZoom() + 1);
	                }
              	}
			},

			events:{
              mouseover: function(marker, event, context){
              	//console.log(context.data);
                $(this).gmap3(
                  {clear:"overlay"},
                  {
                  overlay:{
                    latLng: marker.getPosition(),
                    options:{
                      content:  '<div class="infobulle"><div class="bg"></div><div class="text">'+ context.data.rs +'<br/>'+context.data.adresse+'<br/>'+context.data.code_postal+' '+ context.data.ville +'</div></div>',
                      offset: {
                        x:-46,
                        y:-73
                      }
                    }
                  }
                });
              },
              mouseout: function(){
                $(this).gmap3({clear:"overlay"});
              }
            }
		},
	});
});
{/literal}
{/if}

$.getJSON(
	'{$Helper->getLink("mailing/getmailingforcalendar")}', {literal}
	{nohtml:'nohtml'},{/literal}   
	function(data){		
		$(document).ready(function() {
	
			$('#calmailingindex').fullCalendar({
				header: {
					left: '',
					center: '',
					right: ''
				},
				defaultView: 'basicWeek',
				firstDay:1,
				editable: false,
				events:data,		
			});	
		}); 
		$('#calmailingindex-progress').css('display','none');		
	}
);
</script>