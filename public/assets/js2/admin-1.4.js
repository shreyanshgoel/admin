$(document).ready(function() {

    $('#check_email_btn').on('click', function() {

        document.getElementById('loading').style.display = 'block';
        document.getElementById('cross_sign').style.display = 'none';
        document.getElementById('check_sign').style.display = 'none';

        var v = $('#check_email').val();

        Request.post({ action: "ajax/check_email", data: {email: v}}, function(data) {

            $.each(data, function(index, value){

                if(value == 1){

                    document.getElementById('loading').style.display = 'none';
                    document.getElementById('cross_sign').style.display = 'none';
                    document.getElementById('check_sign').style.display = 'block';

                    document.getElementById('details').style.display = 'none';

                    $('.required').attr('required', false);

                }

                if(value == 0){

                    document.getElementById('loading').style.display = 'none';
                    document.getElementById('cross_sign').style.display = 'block';
                    document.getElementById('check_sign').style.display = 'none';

                    document.getElementById('details').style.display = 'block';

                    $('.required').attr('required', true);

                }
            });

        });

    });
});

