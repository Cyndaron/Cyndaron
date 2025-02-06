'use strict';

function clearPopUpFields()
{
    $('.remove-member-graduation').off();

    $('#gum-edit-user-dialog input,textarea,select').each(function ()
    {
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

$('#gum-new').on('click', function ()
{
    clearPopUpFields();
});

const gumEditHandler = function ()
{
    clearPopUpFields();

    let memberId = $(this).data('id');
    $.get('/api/member/get/' + memberId, {}, null, 'json')
        .done(function (data)
            {
                for (let property in data)
                {
                    if (data.hasOwnProperty(property))
                    {
                        if (property === 'graduationList')
                        {
                            $('#gum-user-dialog-graduation-list').html(data[property]);
                            $('.remove-member-graduation').on('click', function ()
                            {
                                let id = $(this).data('id');
                                $.post({
                                    url: '/api/member/removeGraduation/' + id,
                                    data: {'csrfToken': $('[name=csrfTokenRemoveGraduation]').first().val()},
                                }).done(function ()
                                {
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
}

const gumDuplicateHandler = function ()
{
    clearPopUpFields();

    let memberId = $(this).data('id');
    $.get('/api/member/get/' + memberId, {}, null, 'json')
        .done(function (data)
            {
                for (let property in data)
                {
                    if (data.hasOwnProperty(property))
                    {
                        if (property === 'username')
                        {
                            continue;
                        }

                        if (property === 'graduationList')
                        {
                            $('#gum-user-dialog-graduation-list').html(data[property]);
                            $('.remove-member-graduation').on('click', function ()
                            {
                                let id = $(this).data('id');
                                $('#member-graduation-' + id).remove();
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
}

const gumDeleteHandler = function()
{
    const totalCountElement = document.getElementById('gum-num-members')
    totalCountElement.innerText = (parseInt(totalCountElement.innerText) - 1).toString();
}

$(document).on('submit', '.myForm', function (e)
{
    e.preventDefault();
});

$('#gum-popup-save').on('click keyup', function ()
{
    setWaitingPointer();
    $.post({
        url: '/api/member/save',
        data: $('#gum-user-popup').serialize(),
    }).done(function (record)
    {
        new bootstrap.Modal('#gum-edit-user-dialog').hide();

        const gumTableBody = document.getElementById('gum-table-body');
        const startOfNextQuarter = gumTableBody.attributes['data-next-quarter-start'].value;
        const csrfTokenMemberDelete = gumTableBody.attributes['data-csrf-token-member-delete'].value;
        const existingElement = document.getElementById('pm-row-member-' + record.id);
        if (existingElement)
        {
            existingElement.remove();
        }
        else
        {
            const totalCountElement = document.getElementById('gum-num-members');
            totalCountElement.innerText = (parseInt(totalCountElement.innerText) + 1).toString();
        }

        addMemberToGrid(gumTableBody, startOfNextQuarter, csrfTokenMemberDelete, record);
        sortGrid();

        setNormalPointer();
    });

});

$('#gum-filter-iban').on('change', function ()
{
    let value = parseInt($("#gum-filter-iban option:selected").val());
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
    let value = $("#gum-filter-gender option:selected").val();
    $('.geelhoed-member-entry').show();

    if (value !== '')
    {
        $('.geelhoed-member-entry:not([data-gender="' + value + '"])').hide();
    }
});

$('#gum-filter-temporaryStop').on('change', function ()
{
    let value = parseInt($("#gum-filter-temporaryStop option:selected").val());
    $('.geelhoed-member-entry').show();

    if (value !== -1)
    {
        $('.geelhoed-member-entry:not([data-temporaryStop="' + value + '"])').hide();
    }
});

$('#gum-filter-paymentProblem').on('change', function ()
{
    let value = parseInt($("#gum-filter-paymentProblem option:selected").val());
    $('.geelhoed-member-entry').show();

    if (value !== -1)
    {
        $('.geelhoed-member-entry:not([data-paymentProblem="' + value + '"])').hide();
    }
});

$('#gum-filter-isContestant').on('change', function ()
{
    let value = parseInt($("#gum-filter-isContestant option:selected").val());
    $('.geelhoed-member-entry').show();

    if (value !== -1)
    {
        $('.geelhoed-member-entry:not([data-isContestant="' + value + '"])').hide();
    }
});

$('#gum-filter-sport').on('change', function ()
{
    let value = parseInt($("#gum-filter-sport option:selected").val());
    $('.geelhoed-member-entry').show();

    if (value !== -1)
    {
        $('.geelhoed-member-entry:not([data-sport-' + value + '="1"])').hide();
    }
});

$('#gum-filter-location').on('change', function ()
{
    let value = parseInt($("#gum-filter-location option:selected").val());
    $('.geelhoed-member-entry').show();

    if (value !== -1)
    {
        $('.geelhoed-member-entry:not([data-location-' + value + '="1"])').hide();
    }
});

$('#gum-filter-graduation').on('change', function ()
{
    let value = parseInt($("#gum-filter-graduation option:selected").val());
    $('.geelhoed-member-entry').show();

    if (value !== -1)
    {
        $('.geelhoed-member-entry:not([data-graduation-' + value + '="1"])').hide();
    }
});

$('#gum-filter-paymentMethod').on('change', function ()
{
    let value = $("#gum-filter-paymentMethod option:selected").val();
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

function addMemberToGrid(gumTableBody, startOfNextQuarter, csrfTokenMemberDelete, record)
{
    const tr = document.createElement('tr');
    tr.setAttribute('id', 'pm-row-member-' + record.id);
    tr.setAttribute('class', 'geelhoed-member-entry');
    tr.setAttribute('data-name', record.name);
    tr.setAttribute('data-iban', record.iban);
    tr.setAttribute('data-gender', record.gender);
    tr.setAttribute('data-temporaryStop', record.temporaryStop);
    tr.setAttribute('data-paymentMethod', record.paymentMethod);
    tr.setAttribute('data-paymentProblem', record.paymentProblem);
    tr.setAttribute('data-isContestant', record.isContestant);
    tr.setAttribute('data-dateOfBirth', record.dateOfBirth);
    for (const sportId of record.sports)
    {
        tr.setAttribute('data-sport-' + sportId, '1');
    }
    for (const graduationId of record.graduations)
    {
        tr.setAttribute('data-graduation-' + graduationId, '1');
    }
    for (const locationId of record.locations)
    {
        tr.setAttribute('data-location-' + locationId, '1');
    }
    gumTableBody.appendChild(tr);

    const idColumn = document.createElement('td');
    idColumn.innerHTML = record.id;
    tr.appendChild(idColumn);

    const addressColumn = document.createElement('td');
    addressColumn.innerHTML = record.name + '<br>' + record.streetAddress + '<br>' + record.postcodeAndCity;
    tr.appendChild(addressColumn);

    const contactDataColumn = document.createElement('td');
    let contactDataHTML = '';
    if (record.email !== '')
    {
        contactDataHTML = 'E-mail: <a href="mailto:' + record.email + '">' + record.email + '</a>';
    }

    for (let i = 0; i < record.phoneNumbers.length; i++)
    {
        contactDataHTML += '<br>' + 'Tel.nr. ' + (i + 1) + ': ' + record.phoneNumbers[i];
    }
    contactDataColumn.innerHTML = contactDataHTML;
    tr.appendChild(contactDataColumn);

    const hoursColumn = document.createElement('td');
    let hoursData = '<ul>';
    for (const hour of record.hours)
    {
        hoursData += '<li>' + hour + '</li>';
    }
    hoursData += '</ul>';
    hoursColumn.innerHTML = hoursData;
    tr.appendChild(hoursColumn);

    const paymentInformationColumn = document.createElement('td');
    let paymentInformation =
        record.iban +
        '<br>' +
        '<abbr title="Voor kwartaal dat begint op ' + startOfNextQuarter + '">Kw.bedrag: </abbr> ' +
        record.quarterlyFee;
    paymentInformationColumn.innerHTML = paymentInformation;
    tr.appendChild(paymentInformationColumn);

    const statusColumn = document.createElement('td');
    let statusInfo = '';
    if (record.isContestant)
        statusInfo += '<abbr title="Wedstrijdjudoka">W</abbr><br>';
    if (record.canLogin)
        statusInfo += '<abbr title="Kan inloggen">I</abbr><br>';
    if (record.isSenior)
        statusInfo += '<abbr title="Is senior">S</abbr>';
    statusColumn.innerHTML = statusInfo;
    tr.appendChild(statusColumn);

    const editButton = document.createElement('button');
    editButton.setAttribute('type', 'button');
    editButton.setAttribute('class', 'btn btn-outline-cyndaron btn-sm btn-gum-edit');
    editButton.setAttribute('data-bs-toggle', 'modal');
    editButton.setAttribute('data-bs-target', '#gum-edit-user-dialog');
    editButton.setAttribute('data-id', record.id);
    editButton.setAttribute('title', 'Bewerk dit lid');
    editButton.innerHTML = '<span class="glyphicon glyphicon-pencil"></span>';
    editButton.addEventListener('click', gumEditHandler);

    const duplicateButton = document.createElement('button');
    duplicateButton.setAttribute('type', 'button');
    duplicateButton.setAttribute('class', 'btn btn-outline-cyndaron btn-sm btn-gum-duplicate');
    duplicateButton.setAttribute('data-bs-toggle', 'modal');
    duplicateButton.setAttribute('data-bs-target', '#gum-edit-user-dialog');
    duplicateButton.setAttribute('data-id', record.id);
    duplicateButton.setAttribute('title', 'Dupliceer dit lid');
    duplicateButton.innerHTML = '<span class="glyphicon glyphicon-copy"></span>';
    duplicateButton.addEventListener('click', gumDuplicateHandler);

    const deleteButton = document.createElement('button');
    deleteButton.setAttribute('type', 'button');
    deleteButton.setAttribute('class', 'btn btn-danger btn-sm pm-delete');
    deleteButton.setAttribute('data-type', 'member');
    deleteButton.setAttribute('data-id', record.id);
    deleteButton.setAttribute('data-csrf-token', csrfTokenMemberDelete);
    deleteButton.setAttribute('title', 'Verwijder dit lid');
    deleteButton.innerHTML = '<span class="glyphicon glyphicon-trash"></span>';
    deleteButton.addEventListener('click', pmDeleteFunction);

    const actionsColumn = document.createElement('td');
    const actionsGroup = document.createElement('div');
    actionsGroup.setAttribute('class', 'btn-group');
    actionsGroup.setAttribute('role', 'group');
    actionsGroup.appendChild(editButton);
    actionsGroup.appendChild(duplicateButton);
    actionsGroup.appendChild(deleteButton);
    actionsColumn.appendChild(actionsGroup)
    tr.appendChild(actionsColumn);
}

function loadMembers()
{
    const gumTableBody = document.getElementById('gum-table-body');
    const startOfNextQuarter = gumTableBody.attributes['data-next-quarter-start'].value;
    const csrfTokenMemberDelete = gumTableBody.attributes['data-csrf-token-member-delete'].value;
    gumTableBody.innerHTML = '';

    setWaitingPointer();
    fetch('/api/member/getGrid')
        .then((response) => response.json()
            .then((decodedResponse) =>
            {
                let numMembers = 0;
                for (const record of decodedResponse)
                {
                    addMemberToGrid(gumTableBody, startOfNextQuarter, csrfTokenMemberDelete, record);
                    numMembers++;
                }
                //sortGrid();
                document.getElementById('gum-num-members').innerText = numMembers.toString();
                setNormalPointer();
            })
        );
}

document.addEventListener("DOMContentLoaded", function (event)
{
    loadMembers();
    document.getElementById('confirm-dangerous-yes').addEventListener('click', gumDeleteHandler);
});

function setWaitingPointer()
{
    document.body.style.cursor = 'wait';
}

function setNormalPointer()
{
    document.body.style.cursor = 'default';
}

function sortGrid()
{
    const tableBody = document.getElementById("gum-table-body");
    let isSorting = true;
    while (isSorting)
    {
        isSorting = false;
        let rows = tableBody.rows;
        let shouldSwitch = false;
        let i = 0;
        for (i = 0; i < (rows.length - 1); i++)
        {
            shouldSwitch = false;
            let x = rows[i].attributes['data-name'].value.toLowerCase();
            let y = rows[i + 1].attributes['data-name'].value.toLowerCase();
            if (x > y)
            {
                shouldSwitch = true;
                break;
            }
        }
        if (shouldSwitch)
        {
            rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
            isSorting = true;
        }
    }
}
