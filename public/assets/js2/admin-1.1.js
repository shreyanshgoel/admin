$(document).ready(function() {

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

    $('.create_dept').on('click', function() {

       $('#dept_label').html('Create New Department');
        $('#dept_name').removeAttr('value');
        $('#dept_head').selectpicker('val', 'deselectAll');
        $('#dept_desc').html('');
        
        $('#dept_button').attr('value', 'create_dept');
        $('#dept_button').html('Create');
        $('#create_dept').modal('toggle');
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

function edit_department(id) {
    Request.post({ action: "ajax/edit_department", data: { department_id: id } }, function(data) {

        $('#dept_label').html('Edit Department');
        $('#dept_name').attr('value', data.dept._name);
        $('#dept_edit').attr('value', data.dept.__id);
        $('#dept_head').selectpicker('val', data.dept._head_id);
        $('#dept_desc').html(data.dept._description);
        
        $('#dept_button').attr('value', 'edit_dept');
        $('#dept_button').html('Edit');
    });

    $('#create_dept').modal('toggle');
}