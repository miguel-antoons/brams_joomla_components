/* eslint-disable no-global-assign */
// * cf. ../../_js/list.js
// eslint-disable-next-line no-unused-vars
/* global $, log, elements, sortAsc, sortDesc, stopPropagation, deleteRow, apiFailMessg */
// eslint-disable-next-line no-unused-vars
const sortDescFlags = {
    code: true,     // next sort method for the location code table header (true = desc, false = asc)
    name: false,    // next sort method for the name table header (true = desc, false = asc)
    start: false,   // next sort method for the start table header (true = desc, false = asc)
    end: false,     // next sort method for the end table header (true = desc, false = asc)
};

/**
 * Function generates the system table from the systems array.
 * It then renders the table on inside the #systems element.
 */
function generateTable() {
    let HTMLString = '';

    // generate a row for each system
    elements.forEach(
        (system) => {
            HTMLString += `
                <tr
                    class="tableRow"
                    onclick="window.location.href=
                        '/index.php?'
                        + 'option=com_bramsadmin'
                        + '&view=systemEdit'
                        + '&id=${system.id}';"
                >
                    <td>${system.code}</td>
                    <td>${system.name}</td>
                    <td>${system.start}</td>
                    <td>${system.end}</td>
                    <td>
                        <button
                            type='button'
                            class='customBtn edit'
                            onclick="window.location.href=
                                '/index.php?'
                                + 'option=com_bramsadmin'
                                + '&view=systemEdit'
                                + '&id=${system.id}';"
                        >
                            <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                            Edit
                        </button>
                        <button
                            type='button'
                            class='customBtn delete'
                            onclick="deleteSystem(
                                ${system.id},
                                '${system.name}',
                                ${system.notDeletable})"
                        >
                            <i class="fa fa-trash" aria-hidden="true"></i>
                            Delete
                        </button>
                    </td>
                </tr>
            `;
        },
    );

    document.getElementById('systems').innerHTML = HTMLString;
    stopPropagation();
}

/**
 * Calls an api to delete the system with id equal to 'systemId' argument.
 * If the system was successfully deleted, it updates the html table.
 *
 * @param {number}      systemId     id of the system that has to be deleted
 * @param {string}      locationName name of the systems' location to be deleted
 * @param {string|null} notDeletable determines if the system can be deleted or not
 */
// eslint-disable-next-line no-unused-vars
function deleteSystem(systemId, locationName, notDeletable) {
    if (notDeletable) {
        // eslint-disable-next-line no-alert
        alert(
            'System can\'t be deleted as long as there are files '
            + 'referencing this system.\nPlease remove the files referencing this'
            + ' system in order to remove the system.',
        );
        return;
    }

    deleteRow(systemId, locationName, 'systems');
}

/**
 * Function calls an api to get all the systems from the back-end. If no error occurs
 * it should receive the id, name, location code, start and end for each system.
 */
function getSystems() {
    // get the token
    const token = $('#token').attr('name');

    $.ajax({
        type: 'GET',
        url: `
            /index.php?
            option=com_bramsadmin
            &task=getAll
            &view=systems
            &format=json
            &${token}=1
        `,
        success: (response) => {
            elements = response.data;
            elements.sort((first, second) => sortAsc(first.code, second.code));
            generateTable();
        },
        error: (response) => {
            // on fail, show an error message
            document.getElementById('message').innerHTML = (
                'API call failed, please read the \'log\' variable in '
                + 'developer console for more information about the problem.'
            );
            // store the server response in the log variable
            log = response;
        },
    });
}

window.onload = getSystems;
