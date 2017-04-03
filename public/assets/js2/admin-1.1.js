$(document).ready(function() {

    $('#check_email_btn').on('click', function() {

        document.getElementById('loading').style.display = 'block';
        document.getElementById('cross_sign').style.display = 'none';
        document.getElementById('check_sign').style.display = 'none';

        var v = $('#check_email').val();

        Request.post({ action: "ajax/check_email", data: { email: v } }, function(data) {

            $.each(data, function(index, value) {

                if (value == 1) {

                    document.getElementById('loading').style.display = 'none';
                    document.getElementById('cross_sign').style.display = 'none';
                    document.getElementById('check_sign').style.display = 'block';

                    document.getElementById('details').style.display = 'none';

                    $('.required').attr('required', false);

                }

                if (value == 0) {

                    document.getElementById('loading').style.display = 'none';
                    document.getElementById('cross_sign').style.display = 'block';
                    document.getElementById('check_sign').style.display = 'none';

                    document.getElementById('details').style.display = 'block';

                    $('.required').attr('required', true);

                }
            });

        });

    });

    $('.create_project').on('click', function() {

        $('#project_label').html('Create Project');
        $('#p_team').selectpicker('val', 'deselectAll');
        $('#p_head').selectpicker('val', 'deselectAll');
        $('#p_name').removeAttr('value');
        $('#p_details').html('');
        $('#p_duedate').removeAttr('value');
        $('#p_type').selectpicker('val', 'deselectAll');

        $('#p_button').attr('value', 'create_project');
        $('#p_button').html('Create');
        $('#create_project').modal('toggle');

    });

});



function edit_project(id) {
    Request.post({ action: "ajax/edit_project", data: { project_id: id } }, function(data) {

        $('#project_label').html('Edit Project');
        $('#edit_project_id').attr('value', data.project.__id);
        $('#p_team').selectpicker('val', data.project._team);
        $('#p_head').selectpicker('val', data.project._team);
        $('#p_name').attr('value', data.project._name);
        $('#p_details').html(data.project._details);
        $('#p_duedate').attr('value', data.project._due_date.date.substring(0,10));
        $('#p_type').selectpicker('val', data.project._status[0].substring(0,1).toUpperCase()+data.project._status[0].substring(1));

        $('#p_button').attr('value', 'edit_project');
        $('#p_button').html('Edit');
    });

    $('#create_project').modal('toggle');
}
