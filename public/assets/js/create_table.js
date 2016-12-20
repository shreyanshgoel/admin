$(document).ready(function () {
	$('#table_type').on('change', function() {
        var self = $(this);

        var val = self.val();

        target1 = $('#custom');
        target2 = $('#column1');

        if(val == "Inventory"){

        	target1.css("display","none");
        	target2.removeAttr("required");
        }


        if(val == "Other"){

        	target1.css("display","block");
        	target2.attr("required", "required");
        }
    });
});