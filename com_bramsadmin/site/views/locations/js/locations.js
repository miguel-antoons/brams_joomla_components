/* global $ */
const sortDescFlags = {
    location_code: true,    // next sort method for the location table header (true = desc, false = asc)
    name: false,            // next sort method for the name table header (true = desc, false = asc)
    ftp_password: false,    // next sort method for the ftp password table header (true = desc, false = asc)
    latitude: false,        // next sort method for the latitude table header (true = desc, false = asc)
    longitude: false,       // next sort method for the longitude table header (true = desc, false = asc)
    obs_name: false,        // next sort method for the observer name table header (true = desc, false = asc)
    transfer_type: false,   // next sort method for the transfer type table header (true = desc, false = asc)
    tv_id: false,           // next sort method for the teamviewer id table header (true = desc, false = asc)
    tv_password: false,     // next sort method for the teamviewer password table header (true = desc, false = asc)
};
// eslint-disable-next-line no-unused-vars
let log = 'Nothing to show';    // variable contains log messages if something was logged
let locations;

// function stop the onclick property from .systemRow classes
// from firing when clicking on a button inside a .systemRow class
function stopPropagation() {
    $('.tableRow button').on('click', (e) => {
        e.stopPropagation();
    });
}

function sortAsc(first, second, noSpace) {
    if (first === null) return 1;
    if (second === null) return -1;
    // eslint-disable-next-line no-param-reassign
    if (noSpace) { first = first.replace(/\s/g, ''); second = second.replace(/\s/g, ''); }
    if (first > second) return 1;
    if (first < second) return -1;
    return 0;
}

function sortDesc(first, second, noSpace) {
    if (first === null) return 1;
    if (second === null) return -1;
    // eslint-disable-next-line no-param-reassign
    if (noSpace) { first = first.replace(/\s/g, ''); second = second.replace(/\s/g, ''); }
    if (first < second) return 1;
    if (first > second) return -1;
    return 0;
}

/**
 * Calls an api to delete the location with id equal to 'locationId' argument.
 * If the location was successfully deleted, it updates the html table.
 *
 * @param {number} locationId id of the location that has to be deleted
 * @param {string} locationName name of the systems' location to be deleted
 */
function deleteLocation(locationId, locationName) {
    if (!confirm(`Are you sure you want to delete ${locationName}`)) return;
    const token = $('#token').attr('name');

    $.ajax({
        type: 'DELETE',
        url: `/index.php?option=com_bramsadmin&view=locations&task=deletesystem&format=json&id=${systemId}&${token}=1`,
        success: (response) => {
            // on success, update the html table by removing the system from it
            const isDeletedElement = (element) => Number(element.id) === locationId;
            systems.splice(systems.findIndex(isDeletedElement), 1);
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
 * Function generates the system table from the systems array.
 * It then renders the table on inside the #systems element.
 */
function generateTable() {
    let HTMLString = '';

    // generate a row for each system
    locations.forEach(
        (location) => {
            let ftpPassword = '';
            let tvId = '';
            let tvPassword = '';

            if (
                location.ftp_password !== null
            ) ftpPassword = location.ftp_password;
            if (location.tv_id !== null) tvId = location.tv_id;
            if (
                location.tv_password !== null
            ) tvPassword = location.tv_password;

            HTMLString += `
                <tr
                    class="tableRow"
                    onclick="window.location.href='/index.php?option=com_bramsadmin&view=systemedit&id=${location.id}';"
                >
                    <td>${location.location_code}</td>
                    <td>${location.name}</td>
                    <td>${location.latitude}</td>
                    <td>${location.longitude}</td>
                    <td>${location.transfer_type}</td>
                    <td>${location.obs_name}</td>
                    <td>${ftpPassword}</td>
                    <td>${tvId}</td>
                    <td>${tvPassword}</td>
                    <td>
                        <button
                            type='button'
                            class='customBtn edit'
                            onclick="window.location.href='/index.php?option=com_bramsadmin&view=locationedit&id=${location.id}';"
                        >
                            <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                        </button>
                        <button
                            type='button'
                            class='customBtn delete'
                            onclick="deleteLocation(${location.id}, '${location.location_code}')"
                        >
                            <i class="fa fa-trash" aria-hidden="true"></i>
                        </button>
                    </td>
                </tr>
            `;
        },
    );

    document.getElementById('locations').innerHTML = HTMLString;
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
        locations.sort((first, second) => sortDesc(first[attribute], second[attribute], noSpace));
    } else {
        // sort asc
        locations.sort((first, second) => sortAsc(first[attribute], second[attribute], noSpace));
    }

    // toggle the sorting method
    sortDescFlags[attribute] = !sortDescFlags[attribute];

    setSortIcon(headerElement);
    generateTable();
}

/**
 * Function calls an api to get all the locations from the back-end. If no error occurs
 * it should receive every location and its information.
 */
function getLocations() {
    // get the token
    const token = $('#token').attr('name');

    $.ajax({
        type: 'GET',
        url: `/index.php?option=com_bramsadmin&view=locations&task=getlocations&format=json&${token}=1`,
        success: (response) => {
            locations = response.data;
            locations.sort((first, second) => sortAsc(first.location_code, second.location_code));
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

window.onload = getLocations;
