/* eslint-disable no-global-assign */
// * cf. ../../_js/list.js
// eslint-disable-next-line no-unused-vars
/* global $, log, elements, sortAsc, sortDesc, stopPropagation, deleteRow, apiFailMessg */
// eslint-disable-next-line no-unused-vars
const sortDescFlags = {
    code: true,
    name: false,
    version: false,
};

/**
 * Function generates the software table from the elements array.
 * It then renders the table on inside the #antennas element.
 */
function generateTable() {
    let HTMLString = '';

    // generate a row for each software
    elements.forEach(
        (software) => {
            HTMLString += `
                <tr
                    class="tableRow"
                    onclick="window.location.href=
                        '/index.php?option=com_bramsadmin&view=softwareEdit&id=${software.id}';"
                >
                    <td>${software.code}</td>
                    <td>${software.name}</td>
                    <td>${software.version}</td>
                    <td>
                        <button
                            type='button'
                            class='customBtn edit'
                            onclick="window.location.href=
                                '/index.php'
                                + '?option=com_bramsadmin'
                                + '&view=softwareEdit'
                                + '&id=${software.id}';"
                        >
                            <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                            Edit
                        </button>
                        <button
                            type='button'
                            class='customBtn delete'
                            onclick=
                                "deleteSoftware(
                                    ${software.id},
                                    '${software.code}',
                                    ${software.notDeletable}
                                )"
                        >
                            <i class="fa fa-trash" aria-hidden="true"></i>
                            Delete
                        </button>
                    </td>
                </tr>
            `;
        },
    );

    document.getElementById('softwares').innerHTML = HTMLString;
    stopPropagation();
}

/**
 * Calls an api to delete the software with id equal to 'softwareId' argument.
 * If the software was successfully deleted, it updates the html table.
 *
 * @param {number}      softwareId   id of the software that has to be deleted
 * @param {string}      softwareName name of the software to be deleted
 * @param {boolean}     notDeletable determines if the software can be deleted or not
 */
// eslint-disable-next-line no-unused-vars
function deleteSoftware(softwareId, softwareName, notDeletable) {
    if (notDeletable) {
        // eslint-disable-next-line no-alert
        alert(
            'Software can\'t be deleted as long as there are systems (radsys_system) '
            + 'referencing this software.\nPlease remove the systems referencing this'
            + ' software in order to remove the software.',
        );
        return;
    }

    deleteRow(softwareId, softwareName, 'softwares');
}

/**
 * Function calls an api to get all the software from the back-end. If no error occurs
 * it should receive the id, name, version and code for each single software.
 */
function getAll() {
    // get the token
    const token = $('#token').attr('name');

    $.ajax({
        type: 'GET',
        url: `
            /index.php?
            option=com_bramsadmin
            &task=getAll
            &view=softwares
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
            document.getElementById('message').innerHTML = apiFailMessg;
            // store the server response in the log variable
            log = response;
        },
    });
}

window.onload = getAll;
