'use strict';

$('#gum-new').on('click', function() {
    $('#gum-edit-user-dialog input,textarea,select').each(function () {
        let element = $(this);
        if (element.is(':checkbox'))
        {
            element.prop('checked', false);
        }
        else if (element.is('select'))
        {
            element.find('option').prop('selected', false);
        }
        else
        {
            element.val('');
        }
    });
});

$('.btn-gum-edit').on('click', function () {
    let memberId = $(this).data('id');
    $.get('/api/member/get/' + memberId, {}, null, 'json')
        .done(function (data) {

            for (let property in data)
            {
                if (data.hasOwnProperty(property))
                {
                    let element = $('#gum-edit-user-dialog [name=' + property + ']').first();
                    if (element)
                    {
                        if (element.is(':checkbox'))
                        {
                            element.prop('checked', parseInt(data[property]) !== 0);
                        }
                        else if (element.is('select'))
                        {
                            $('#gum-edit-user-dialog [name=' + property + '] option').prop('selected', false);
                            $('#gum-edit-user-dialog [name=' + property + '] option[value=' + data[property] + ']').prop('selected', true);
                        }
                        else
                        {
                            element.val(data[property])
                        }
                    }
                }
            }
        }
    );

    $('#gum-edit-user-dialog [name=id]').val(memberId);
});