'use strict';

$('#gum-new').on('click', function() {
    $('#gum-edit-user-dialog input,textarea,select').each(function () {
        let element = $(this);
        if (element.prop('name') !== 'csrfToken')
        {
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
        }
    });
    $('#gum-user-dialog-graduation-list').html('');
});

$('.btn-gum-edit').on('click', function () {
    let memberId = $(this).data('id');
    $.get('/api/member/get/' + memberId, {}, null, 'json')
        .done(function (data) {

            for (let property in data)
            {
                if (data.hasOwnProperty(property))
                {
                    if (property === 'graduationList')
                    {
                        $('#gum-user-dialog-graduation-list').html(data[property]);
                    }
                    else
                    {
                        let element = $('#gum-edit-user-dialog [name=' + property + ']').first();
                        if (element)
                        {
                            if (element.is(':checkbox'))
                            {
                                element.prop('checked', data[property]);
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
        }
    );

    $('#gum-edit-user-dialog [name=id]').val(memberId);
});

$(document).on('submit', '.myForm', function(e) {

    e.preventDefault();
});

$('#gum-popup-save').on('click keyup', function () {
    $.post({
        url: '/api/member/save',
        data: $('#gum-user-popup').serialize(),
    }).done(function (data) {
        location.reload();
    });

});