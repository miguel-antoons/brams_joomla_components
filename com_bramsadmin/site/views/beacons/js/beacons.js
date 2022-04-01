/* global $ */
let sortDescFlags = {
    name: true,         // next sort method for the beacon name table header (true = desc, false = asc)
    latitude: false,    // next sort method for the latitude table header (true = desc, false = asc)
    longitude: false,   // next sort method for the longitude table header (true = desc, false = asc)
    frequency: false,   // next sort method for the frequency table header (true = desc, false = asc)
    power: false,       // next sort method for the power table header (true = desc, false = asc)
};
// eslint-disable-next-line no-unused-vars
let log = 'Nothing to show';    // variable contains log messages if something was logged
let beacons;

// function stop the onclick property from .systemRow classes
// from firing when clicking on a button inside a .systemRow class
function stopPropagation() {
    $('.tableRow button').on('click', (e) => {
        e.stopPropagation();
    });
}

function sortAsc(first, second) {
    if (first > second) return 1;
    if (first < second) return -1;
    return 0;
}

function sortDesc(first, second) {
    if (first < second) return 1;
    if (first > second) return -1;
    return 0;
}

/**
 * Calls an api to delete the beacon with id equal to 'beaconId' argument.
 * If the beacon was successfully deleted, it updates the html table.
 *
 * @param {number}      beaconId     id of the beacon that has to be deleted
 * @param {string}      beaconName   name of the beacon to be deleted
 * @param {string|null} notDeletable determines if the beacon can be deleted or not
 */
function deleteBeacon(beaconId, beaconName, notDeletable) {
    if (notDeletable !== null) {
        alert(
            "Beacon can't be deleted as long as there are files referencing this beacon.\n" +
            "Please remove the files referencing this beacon in order to remove the beacon."
        );
        return;
    }

    if (!confirm(`Are you sure you want to delete ${beaconName}`)) return;
    const token = $('#token').attr('name');

    $.ajax({
        type: 'DELETE',
        url: `
            /index.php?
            option=com_bramsadmin
            &task=deleteBeacon
            &view=beacons
            &format=json
            &id=${beaconId}
            &${token}=1
        `,
        success: (response) => {
            // on success, update the html table by removing the system from it
            const isDeletedElement = (element) => Number(element.id) === beaconId;
            beacons.splice(beacons.findIndex(isDeletedElement), 1);
            generateTable();
            document.getElementById('message').innerHTML = response.data.message;
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

/**
 * Function generates the beacon table from the beacons array.
 * It then renders the table on inside the #beacons element.
 */
function generateTable() {
    let HTMLString = '';

    // generate a row for each system
    beacons.forEach(
        (beacon) => {
            HTMLString += `
                <tr
                    class="tableRow"
                    onclick="window.location.href='/index.php?option=com_bramsadmin&view=beaconEdit&id=${beacon.id}';"
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
                            onclick="window.location.href='/index.php?option=com_bramsadmin&view=beaconEdit&id=${beacon.id}';"
                        >
                            <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                            Edit
                        </button>
                        <button
                            type='button'
                            class='customBtn delete'
                            onclick="deleteBeacon(${beacon.id}, '${beacon.name}', ${beacon.not_deletable})"
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
 * Function changes the sort icon to the last clicked table header.
 * @param {HTMLTableCellElement} headerElement table header that was clicked for sorting
 */
function setSortIcon(headerElement) {
    // remove the sort icon from the page
    document.getElementById('sortIcon').remove();
    // add the icon to the clicked element ('headerElement')
    headerElement.innerHTML += '<i id="sortIcon" class="fa fa-sort" aria-hidden="true"></i>';
}

/**
 * Function sorts the table by attribute parameter
 * @param {HTMLTableCellElement} headerElement  table header that was clicked for sorting
 * @param {string}               attribute      location attribute to sort on
 * @param {boolean}              noSpace        Whether to remove spaces or not from strings when sorting
 */
function sortTable(headerElement, attribute, noSpace = false) {
    // reset all the sorting methods for all the other table headers
    Object.keys(sortDescFlags).forEach((key) => {
        if (key !== attribute) {
            sortDescFlags[key] = false;
        }
    });

    // if sorting method is set to desc
    if (sortDescFlags[attribute]) {
        // sort the system array desc
        beacons.sort((first, second) => sortDesc(first[attribute], second[attribute], noSpace));
    } else {
        // sort asc
        beacons.sort((first, second) => sortAsc(first[attribute], second[attribute], noSpace));
    }

    // toggle the sorting method
    sortDescFlags[attribute] = !sortDescFlags[attribute];

    setSortIcon(headerElement);
    generateTable();
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
            &task=getBeacons
            &view=beacons
            &format=json
            &${token}=1
        `,
        success: (response) => {
            beacons = response.data;
            beacons.sort((first, second) => sortAsc(first.name, second.name));
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

window.onload = getBeacons;
