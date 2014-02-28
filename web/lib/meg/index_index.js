var widget_add = 0;
var tpl_current_campaign = '<tr><td><a href="'+base_url+'index.php/campaign/view/{{id}}" title="">{{title}}</td><td>{{date_start}}</td><td>{{date_end}}</td></tr>';
var tpl_agenda = '<tr><td><a href="javascript:get_rdv_detail({{id}})" title="">{{date_rdv}}</td><td>{{participant}}</td></tr>';

if(suser.index_map_contacts == 1){
	var globalCluster;
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
				values:markers,
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
}

/**
 * Widget Calendrier
 * @return {[type]} [description]
 */
$(document).ready(function(){
	if($('#widget-mailing')){
		$('#widget-mailing').html('<h4><i class="fa fa-calendar">&nbsp;&nbsp;</i>Calendrier des mailings</h4><div id="calmailingindex"></div>')

		$.getJSON(
			base_url + 'index.php/mailing/getmailingforcalendar',
			{nohtml:'nohtml'},   
			function(data){
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
					height:200	
				});	
			}
		);
	}
});
	
/**
 * Recupere les campagnes en cours
 * @return {[type]} [description]
 */
$(document).ready(function(){
	if (typeof current_campaigns != 'undefined'){
		// Affichage du widget campaign
		var whtml = '<div class="well col-md-5 col-xs-5 col-sm-5"><h4><i class="fa fa-phone-square"></i>&nbsp;&nbsp;Vos campagnes en cours</h4><hr/><table class="table table-striped table-condensed" id="table-current-campaign"><thead><tr><th>Campagne</th><th>Debut</th><th>Fin</th></tr></thead><tbody></tbody></table></div>';

		$('#end-widget-index').before('<div id="widget-current-campaign"></div><div class="col-md-1 col-xs-1 col-sm-1"></div>');
		$('#widget-current-campaign').html(whtml);

		for( var i in current_campaigns ){      
        	$('#table-current-campaign').append( Mustache.render(tpl_current_campaign, current_campaigns[i]) );
    	}

		widget_add = widget_add + 1;
	}
});

/**
 * Affiche les futurs rendez vous
 * @return {[type]} [description]
 */
$(document).ready(function(){
	if (typeof meets != 'undefined'){
		// Affichage du widget campaign
		var whtml = '<div class="well col-md-5 col-xs-5 col-sm-5"><h4><i class=""></i>&nbsp;&nbsp;Agenda</h4><hr/><table class="table table-striped table-condensed" id="table-agenda"><thead><tr><th>Date</th><th>Participant</th></tr></thead><tbody></tbody></table></div>';

		$('#end-widget-index').before('<div id="widget-agenda"></div><div class="col-md-1 col-xs-1 col-sm-1"></div>');
		$('#widget-agenda').html(whtml);

		for( var i in meets ){      
        	$('#table-agenda').append( Mustache.render(tpl_agenda, meets[i]) );
    	}

		widget_add = widget_add + 1;
	}
})
