$('#calendar').fullCalendar({
    events: "http://clutchstart.tk/ajax/calendar_events",

    droppable: true,

    editable: true,

    drop: function( date, jsEvent, ui, resourceI) { 
      
        var obj = JSON.parse(this.getAttribute("data-event"));     
        date2 = date.format();

        Request.post({ action: "ajax/calendar_save/", data: {date: date2, title: obj.title, color: obj.color} }, function(data) {
        });
    },

    eventClick: function(event, jsEvent, view) {
       var title = prompt('Event Title:', event.title, { buttons: { Ok: true, Cancel: false} });
       if (title){
           
           event.title = title;
           
           $.ajax({
             url: 'process.php',
             data: 'type=changetitle&title='+title+'&eventid='+event.id,
             type: 'POST',
             dataType: 'json',
             success: function(response){
               if(response.status == 'success')
               $('#calendar').fullCalendar('updateEvent',event);
             },
             error: function(e){
               alert('Error processing your request: '+e.responseText);
             }
           });
       }
    },
    
    eventDrop: function(event, jsEvent, ui, view) {
      var date = new Date(event._start._d);
      date = date.toISOString().slice(0,10);

      Request.post({ action: "ajax/calendar_date_change/", data: {date: date, id: event.id} }, function(data) {
      });
    }

});