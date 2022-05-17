/* eslint-disable no-global-assign */
// * cf. ../../_js/list.js
// eslint-disable-next-line no-unused-vars
/* global $, log, elements, sortAsc, sortDesc, stopPropagation, deleteRow, apiFailMessg */
// eslint-disable-next-line no-unused-vars
const sortDescFlags = {
    code: true,
    brand: false,
    model: false,
};

/**
 * Function generates the antenna table from the elements array.
 * It then renders the table on inside the #antennas element.
 */
function generateTable() {
    let HTMLString = '';

    // generate a row for each antenna
    elements.forEach(
        (antenna) => {
            HTMLString += `
                <tr class="tableRow">
                    <td onclick="window.location.href=
                        '/index.php?option=com_bramsadmin&view=antennaEdit&id=${antenna.id}';"
                    >${antenna.code}</td>
                    <td onclick="window.location.href=
                        '/index.php?option=com_bramsadmin&view=antennaEdit&id=${antenna.id}';"
                    >${antenna.brand}</td>
                    <td onclick="window.location.href=
                        '/index.php?option=com_bramsadmin&view=antennaEdit&id=${antenna.id}';"
                    >${antenna.model}</td>
                    <td>
                        <button
                            type='button'
                            class='customBtn edit'
                            onclick="window.location.href=
                                '/index.php'
                                + '?option=com_bramsadmin'
                                + '&view=antennaEdit'
                                + '&id=${antenna.id}';"
                        >
                            <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                            Edit
                        </button>
                        <button
                            type='button'
                            class='customBtn delete'
                            onclick=
                                "deleteAntenna(
                                    ${antenna.id},
                                    '${antenna.brand} ${antenna.model}',
                                    ${antenna.notDeletable}
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

    document.getElementById('antennas').innerHTML = HTMLString;
    // stopPropagation();
}

/**
 * Calls an api to delete the antenna with id equal to 'antennaId' argument.
 * If the antenna was successfully deleted, it updates the html table.
 *
 * @param {number}      antennaId    id of the antenna that has to be deleted
 * @param {string}      antennaName  name of the antenna to be deleted
 * @param {string|null} notDeletable determines if the antenna can be deleted or not
 */
// eslint-disable-next-line no-unused-vars
function deleteAntenna(antennaId, antennaName, notDeletable) {
    if (notDeletable) {
        document.getElementById('delete').style.setProperty('display', 'none', 'important');
        document.getElementById('exitButton').innerHTML = '<i class="fa fa-check-square" aria-hidden="true"></i> Ok';
        document.getElementById('exampleModalLabel').innerHTML = `Unable to delete ${campaignName}`;
        document.getElementById('modal-body').innerHTML = '' +
            'Antenna can\'t be deleted as long as there are systems (radsys_system) '
            + 'referencing this antenna. Please remove the systems referencing this'
            + ' antenna in order to remove the antenna.';
        return;
    }

    setPopup(campaignId, campaignName, 'campaigns');
}

/**
 * Function calls an api to get all the antennas from the back-end. If no error occurs
 * it should receive the id, brand, model and code for each antenna.
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
            &view=antennas
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
