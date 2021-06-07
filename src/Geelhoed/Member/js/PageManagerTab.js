'use strict';

function clearPopUpFields()
{
    $('.remove-member-graduation').off();

    $('#gum-edit-user-dialog input,textarea,select').each(function () {
        let element = $(this);
        let elementName = element.prop('name');
        if (!elementName.startsWith('csrfToken'))
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
}

$('#gum-new').on('click', function()
{
    clearPopUpFields();
});

$('.btn-gum-edit').on('click', function ()
{
    clearPopUpFields();

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
                        $('.remove-member-graduation').on('click', function() {
                            let id = $(this).data('id');
                            $.post({
                                url: '/api/member/removeGraduation/' + id,
                                data: { 'csrfToken': $('[name=csrfTokenRemoveGraduation]').first().val()},
                            }).done(function() {
                                $('#member-graduation-' + id).remove();
                            });
                        });
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
                                if (data[property])
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

$(document).on('submit', '.myForm', function(e)
{
    e.preventDefault();
});

$('#gum-popup-save').on('click keyup', function ()
{
    $.post({
        url: '/api/member/save',
        data: $('#gum-user-popup').serialize(),
    }).done(function (data) {
        location.reload();
    });

});

$('#gum-filter-iban').on('change', function ()
{
    let value = parseInt($( "#gum-filter-iban option:selected" ).val());
    $('.geelhoed-member-entry').show();

    if (value === 1)
    {
        $('.geelhoed-member-entry[data-iban=""]').hide();
    }
    else if (value === 2)
    {
        $('.geelhoed-member-entry:not([data-iban=""])').hide();
    }
});

$('#gum-filter-gender').on('change', function ()
{
    let value = $( "#gum-filter-gender option:selected" ).val();
    $('.geelhoed-member-entry').show();

    if (value !== '')
    {
        $('.geelhoed-member-entry:not([data-gender="' + value + '"])').hide();
    }
});

$('#gum-filter-temporaryStop').on('change', function ()
{
    let value = parseInt($( "#gum-filter-temporaryStop option:selected" ).val());
    $('.geelhoed-member-entry').show();

    if (value !== -1)
    {
        $('.geelhoed-member-entry:not([data-temporaryStop="' + value + '"])').hide();
    }
});

$('#gum-filter-paymentProblem').on('change', function ()
{
    let value = parseInt($( "#gum-filter-paymentProblem option:selected" ).val());
    $('.geelhoed-member-entry').show();

    if (value !== -1)
    {
        $('.geelhoed-member-entry:not([data-paymentProblem="' + value + '"])').hide();
    }
});

$('#gum-filter-isContestant').on('change', function ()
{
    let value = parseInt($( "#gum-filter-isContestant option:selected" ).val());
    $('.geelhoed-member-entry').show();

    if (value !== -1)
    {
        $('.geelhoed-member-entry:not([data-isContestant="' + value + '"])').hide();
    }
});

$('#gum-filter-sport').on('change', function ()
{
    let value = parseInt($( "#gum-filter-sport option:selected" ).val());
    $('.geelhoed-member-entry').show();

    if (value !== -1)
    {
        $('.geelhoed-member-entry:not([data-sport-' + value + '="1"])').hide();
    }
});

$('#gum-filter-graduation').on('change', function ()
{
    let value = parseInt($( "#gum-filter-graduation option:selected" ).val());
    $('.geelhoed-member-entry').show();

    if (value !== -1)
    {
        $('.geelhoed-member-entry:not([data-graduation-' + value + '="1"])').hide();
    }
});

$('#gum-filter-paymentMethod').on('change', function ()
{
    let value = $( "#gum-filter-paymentMethod option:selected" ).val();
    $('.geelhoed-member-entry').show();

    if (value !== '')
    {
        $('.geelhoed-member-entry:not([data-paymentMethod="' + value + '"])').hide();
    }
});

$('.gum-filter-dateOfBirth').on('change', function ()
{
    $('.geelhoed-member-entry').show();

    let startVal = $("#gum-filter-dateOfBirth-start").val();
    let endVal = $("#gum-filter-dateOfBirth-end").val();

    if (startVal === '' && endVal === '')
    {
        return;
    }

    let startDate = null;
    if (startVal)
    {
        startDate = new Date(startVal);
    }
    let endDate = null;
    if (endVal)
    {
        endDate = new Date(endVal);
    }

    $('.geelhoed-member-entry').each(function ()
    {
        let dateOfBirthVal = $(this).data('dateofbirth');
        if (!dateOfBirthVal)
        {
            $(this).hide();
        }
        else
        {
            let dateOfBirth = new Date(dateOfBirthVal);
            if (startDate && startDate > dateOfBirth)
            {
                $(this).hide();
            }
            if (endDate && endDate < dateOfBirth)
            {
                $(this).hide();
            }
        }
    });
});
