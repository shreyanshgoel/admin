$('#calendar').fullCalendar({
    events: "http://clutchstart.tk/ajax/calendar_events",

    droppable: true,

    editable: true,

    drop: function( date, jsEvent, ui, resourceI) { 

        var obj = JSON.parse(this.getAttribute("data-event"));

        var d = new Date(date),
        month = '' + (d.getMonth() + 1),
        day = '' + d.getDate(),
        year = d.getFullYear();

        if (month.length < 2) month = '0' + month;
        if (day.length < 2) day = '0' + day;

        var date2 =  [year, month, day].join('-');        

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
    
    eventDrop: function(event, delta, revertFunc) {

        alert(event.id);

    }

});