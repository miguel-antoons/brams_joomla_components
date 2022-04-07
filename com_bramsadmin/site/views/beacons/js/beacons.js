/* eslint-disable no-global-assign */
// * cf. ../../_js/list.js
// eslint-disable-next-line no-unused-vars
/* global $, log, elements, sortAsc, sortDesc, stopPropagation, deleteRow, apiFailMessg */
// eslint-disable-next-line no-unused-vars
const sortDescFlags = {
    name: true,         // next sort method for the beacon name table header (true = desc, false = asc)
    latitude: false,    // next sort method for the latitude table header (true = desc, false = asc)
    longitude: false,   // next sort method for the longitude table header (true = desc, false = asc)
    frequency: false,   // next sort method for the frequency table header (true = desc, false = asc)
    power: false,       // next sort method for the power table header (true = desc, false = asc)
};

/**
 * Function generates the beacon table from the beacons array.
 * It then renders the table on inside the #beacons element.
 */
function generateTable() {
    let HTMLString = '';

    // generate a row for each system
    elements.forEach(
        (beacon) => {
            HTMLString += `
                <tr
                    class="tableRow"
                    onclick="window.location.href=
                        '/index.php?'
                        + 'option=com_bramsadmin'
                        + '&view=beaconEdit'
                        + '&id=${beacon.id}';"
                >
                    <td>${beacon.name}</td>
                    <td>${beacon.latitude}</td>
                    <td>${beacon.longitude}</td>
                    <td>${beacon.frequency}</td>
                    <td>${beacon.power}</td>
                    <td class="actions">
                        <button
                            type='button'
                            class='customBtn edit'
                            onclick="window.location.href=
                                '/index.php?'
                                + 'option=com_bramsadmin'
                                + '&view=beaconEdit'
                                + '&id=${beacon.id}';"
                        >
                            <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                            Edit
                        </button>
                        <button
                            type='button'
                            class='customBtn delete'
                            onclick=
                                "deleteBeacon(
                                    ${beacon.id},
                                    '${beacon.name}',
                                    ${beacon.not_deletable}
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

    document.getElementById('beacons').innerHTML = HTMLString;
    stopPropagation();
}

/**
 * Calls an api to delete the beacon with id equal to 'beaconId' argument.
 * If the beacon was successfully deleted, it updates the html table.
 *
 * @param {number}      beaconId     id of the beacon that has to be deleted
 * @param {string}      beaconName   name of the beacon to be deleted
 * @param {string|null} notDeletable determines if the beacon can be deleted or not
 */
// eslint-disable-next-line no-unused-vars
function deleteBeacon(beaconId, beaconName, notDeletable) {
    if (notDeletable !== null) {
        // eslint-disable-next-line no-alert
        alert(
            "Beacon can't be deleted as long as there are files referencing this beacon.\n"
            + 'Please remove the files referencing this beacon in order to remove the beacon.',
        );
        return;
    }

    deleteRow(beaconId, beaconName, 'beacons');
}

/**
 * Function calls an api to get all the beacons from the back-end. If no error occurs
 * it should receive the name, beacon latitude, beacon longitude, beacon frequency and
 * beacon power for each beacon.
 */
function getBeacons() {
    // get the token
    const token = $('#token').attr('name');

    $.ajax({
        type: 'GET',
        url: `
            /index.php?
            option=com_bramsadmin
            &task=getAll
            &view=beacons
            &format=json
            &${token}=1
        `,
        success: (response) => {
            elements = response.data;
            elements.sort((first, second) => sortAsc(first.name, second.name));
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

window.onload = getBeacons;
