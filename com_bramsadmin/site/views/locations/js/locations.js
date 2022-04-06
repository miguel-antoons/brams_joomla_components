// eslint-disable-next-line no-unused-vars
/* global $, log, elements, sortAsc, sortDesc, stopPropagation, deleteRow, apiFailMessg */
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

/**
 * Function generates the location table from the elements array.
 * It then renders the table on inside the #locations element.
 */
function generateTable() {
    let HTMLString = '';

    // generate a row for each location
    elements.forEach(
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
                    onclick="window.location.href=
                        '/index.php?'
                        + 'option=com_bramsadmin'
                        + '&view=locationEdit'
                        + '&id=${location.id}';"
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
                            onclick="window.location.href=
                                '/index.php?'
                                + 'option=com_bramsadmin'
                                + '&view=locationEdit'
                                + '&id=${location.id}';"
                        >
                            <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                        </button>
                        <button
                            type='button'
                            class='customBtn delete'
                            onclick="deleteLocation(
                                ${location.id},
                                '${location.location_code}',
                                ${location.not_deletable}
                            )"
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
 * Calls an api to delete the location with id equal to 'locationId' argument.
 * If the location was successfully deleted, it updates the html table.
 *
 * @param {number}      locationId   id of the location that has to be deleted
 * @param {string}      locationName name of the location to be deleted
 * @param {string|null} notDeletable determines if the location can be deleted or not
 */
function deleteLocation(locationId, locationName, notDeletable) {
    if (notDeletable !== null) {
        alert(
            "Location can't be deleted as long as there are systems referencing this location.\n"
            + 'Please remove the systems referencing this location in order to remove the '
            + 'location.',
        );
        return;
    }

    deleteRow(locationId, locationName, 'locations');
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
        url: `
            /index.php?
            option=com_bramsadmin
            &task=getAll
            &view=locations
            &format=json
            &${token}=1
        `,
        success: (response) => {
            elements = response.data;
            elements.sort((first, second) => sortAsc(first.location_code, second.location_code));
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

window.onload = getLocations;
