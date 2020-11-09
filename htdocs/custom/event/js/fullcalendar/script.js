
document.addEventListener('DOMContentLoaded', function() {
  var calendarEl = document.getElementById('events_calendar');

	// var data = document.getElementById("data_events_calendar");
    var data = $("div#data_events_calendar").text();
    var data = JSON.parse( data.toString() );
    // console.log(data);
	
	//colors
    var color_grp = $("div#ID_EVENT_GRP_CALENDAR_COLOR_BG").text();
    var color_sgr = $("div#ID_EVENT_SNS_GRP_CALENDAR_COLOR_BG").text();
    var color_txt = $("div#ID_EVENT_CALENDAR_COLOR_TXT").text();
	
    
    var _events = [];
    var count = 0;
	
    $.each($(data),function(key,value){
		
		if (value.igroupes.length == 0) {
			
				if(value.date_event){
					_events[count] = { 
					  id: value.id,
					  title: value.label, 
					  description: value.label, 
					  start: value.date_event+'T'+value.time_start, 
					  end: value.date_event+'T'+value.time_end,
					  fk_statut: value.fk_statut,
					  fk_event: value.fk_event,
					  event: value.event,
					  entity: value.entity,
					  ref: value.ref,
					  time_start: value.time_start,
					  time_end: value.time_end,
					  total_ht: value.total_ht,
					  total_ttc: value.total_ttc,
					  color: color_sgr,   // background event
					  event_registration_0: value.event_registration_0,
					  event_registration_1: value.event_registration_1,
					  event_registration_8: value.event_registration_8,
					  event_registration_4: value.event_registration_4,
					  event_registration_5: value.event_registration_5,
					  textColor: color_txt,
					  // constraint: 'businessHours',
					  // overlap: false,
					  // rendering: 'background',
					  // groupId: value.fk_event,
					};
					count++;
					
				} // end IF
			
		} else {
			
			$.each($(value.igroupes),function(keyGroup,valueGroup){
				
				if(valueGroup.heured){
					_events[count] = { 
					  id: value.id,
					  title: value.label, 
					  description: value.label, 
					  start: (valueGroup.heured).replace(' ','T'), 
					  end: (valueGroup.heuref).replace(' ','T'),
					  fk_statut: value.fk_statut,
					  fk_event: value.fk_event,
					  event: value.event,
					  entity: value.entity,
					  ref: value.ref,
					  time_start: (valueGroup.heured).slice(11, 19),
					  time_end: (valueGroup.heuref).slice(11, 19),
					  total_ht: value.total_ht,
					  total_ttc: value.total_ttc,
					  color: color_grp,   // background event
					  event_registration_0: value.event_registration_0,
					  event_registration_1: value.event_registration_1,
					  event_registration_8: value.event_registration_8,
					  event_registration_4: value.event_registration_4,
					  event_registration_5: value.event_registration_5,
					  textColor: color_txt,
					  // constraint: 'businessHours',
					  // overlap: false,
					  // rendering: 'background',
					  // groupId: value.fk_event,
					};
					count++;
					
				} // end IF
			}); // $.each
			
		}
		
    });
    // console.log(_events);
	
	
  var calendar = new FullCalendar.Calendar(calendarEl, {
	
	plugins: [ 'interaction', 'dayGrid', 'timeGrid', 'list' ],
	header: {
		left: 'prev,next today',
		center: 'title',
		right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
	},
	// defaultDate: '2020-02-12',
	navLinks: true, // can click day/week names to navigate views
	// businessHours: true, // display business hours
	// editable: true,
	locale: 'fr',
    events: _events,
	selectable: true,
    select: function(info) {
      // alert('selected ' + info.startStr + ' to ' + info.endStr);
	  var date = new Date(info.startStr);
	  var date_str = ('0'+date.getDate()).substr(-2,2)+'/'+('0'+(date.getMonth()+1)).substr(-2,2)+'/'+date.getFullYear();
	  $("form#form_event_popup #date_event").val(date_str);
	  // <!-- Link to open the modal -->
	  $("#create_event_bydate").modal();
    },
    // defaultView: 'dayGridMonth',
	/* 
	navLinks: true,
	navLinkDayClick: function(date, jsEvent) {
		console.log('day', date.toISOString());
		console.log('coords', jsEvent.pageX, jsEvent.pageY);
	},
    dateClick: function(info) {
		console.log(info);
	}, */
	
    eventRender: function(info) {
	  var itemplate = '<div class="tooltip-calendar"><form method="POST"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div><div class="tooltip-desc">';
      itemplate += '<p>- Événement : '+ info.event.extendedProps.event +'</p>';
      itemplate += '<p>- Début : '+ info.event.extendedProps.time_start +'</p>';
      itemplate += '<p>- Fin : '+ info.event.extendedProps.time_end +'</p>';
      itemplate += '<p>- Prix HT : '+ Number(info.event.extendedProps.total_ht).toFixed(2) +'</p>';
      itemplate += '<p>- Prix TTC : '+ Number(info.event.extendedProps.total_ttc).toFixed(2) +'</p>';
      itemplate += '<p class="btn-events-calendar" style="text-align: center; margin: 8px 0;"> <img src="/theme/eldy/img/statut0.png" alt="" title="Brouillon" class="inline-block" style="margin: 5px 0 0 5px;"> '+ info.event.extendedProps.event_registration_0 +' <img src="/theme/eldy/img/statut3.png" alt="" title="Attente de confirmation" class="inline-block" style="margin: 5px 0 0 5px;"> '+ info.event.extendedProps.event_registration_1 +' <img src="/theme/eldy/img/statut1.png" alt="" title="Liste d\'attente" class="inline-block" style="margin: 5px 0 0 5px;"> '+ info.event.extendedProps.event_registration_8 +' <br> <img src="/theme/eldy/img/statut4.png" alt="" title="Confirmée" class="inline-block" style="margin: 5px 0 0 5px;"> '+ info.event.extendedProps.event_registration_4 +' <img src="/theme/eldy/img/statut8.png" alt="" title="Refusée" class="inline-block" style="margin: 5px 0 0 5px;"> '+ info.event.extendedProps.event_registration_5 +' </p>';
	  
	  itemplate += '<a href="/custom/event/card.php?id='+ Number(info.event.extendedProps.fk_event) +'" class="btn-events-calendar" target="_blank">Événement</a>';
	  itemplate += '<a href="/custom/event/day/card.php?id='+ Number(info.event.id) +'" class="btn-events-calendar" target="_blank">Journée</a>';
      itemplate += '</div></form></div>';
        
      var tooltip = new Tooltip(info.el, {
        title: info.event.extendedProps.description,
		template: itemplate,
        placement: 'top',
        trigger: 'hover',
        container: 'body'
      });
    }
	
  });

  calendar.render();
});


$(document).ready(function () {
	$("form#form_event_popup #eventid").select2({ width: '70%' }); 
	// $('#form#form_event_popup #eventid').on("select2-selecting", function(e) { 
	$('form#form_event_popup #eventid').on("change", function() { 
		// console.log($(this).val());
		$('form#form_event_popup').attr("action", "/custom/event/day/card.php?action=create&eventid="+ $(this).val());
	});
});