/* eslint-disable no-global-assign */
// * cf. ../../_js/list.js
// eslint-disable-next-line no-unused-vars
/* global $, log, elements, sortAsc, sortDesc, stopPropagation, deleteRow, apiFailMessg */
// eslint-disable-next-line no-unused-vars
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
                <tr class="tableRow">
                    <td onclick="window.location.href=
                        '/index.php?'
                        + 'option=com_bramsadmin'
                        + '&view=locationEdit'
                        + '&id=${location.id}';"
                    >${location.location_code}</td>
                    <td onclick="window.location.href=
                        '/index.php?'
                        + 'option=com_bramsadmin'
                        + '&view=locationEdit'
                        + '&id=${location.id}';"
                    >${location.name}</td>
                    <td onclick="window.location.href=
                        '/index.php?'
                        + 'option=com_bramsadmin'
                        + '&view=locationEdit'
                        + '&id=${location.id}';"
                    >${location.latitude}</td>
                    <td onclick="window.location.href=
                        '/index.php?'
                        + 'option=com_bramsadmin'
                        + '&view=locationEdit'
                        + '&id=${location.id}';"
                    >${location.longitude}</td>
                    <td onclick="window.location.href=
                        '/index.php?'
                        + 'option=com_bramsadmin'
                        + '&view=locationEdit'
                        + '&id=${location.id}';"
                    >${location.transfer_type}</td>
                    <td onclick="window.location.href=
                        '/index.php?'
                        + 'option=com_bramsadmin'
                        + '&view=locationEdit'
                        + '&id=${location.id}';"
                    >${location.obs_name}</td>
                    <td onclick="window.location.href=
                        '/index.php?'
                        + 'option=com_bramsadmin'
                        + '&view=locationEdit'
                        + '&id=${location.id}';"
                    >${ftpPassword}</td>
                    <td onclick="window.location.href=
                        '/index.php?'
                        + 'option=com_bramsadmin'
                        + '&view=locationEdit'
                        + '&id=${location.id}';"
                    >${tvId}</td>
                    <td onclick="window.location.href=
                        '/index.php?'
                        + 'option=com_bramsadmin'
                        + '&view=locationEdit'
                        + '&id=${location.id}';"
                    >${tvPassword}</td>
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
                                ${location.notDeletable}
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
    // stopPropagation();
}

/**
 * Calls an api to delete the location with id equal to 'locationId' argument.
 * If the location was successfully deleted, it updates the html table.
 *
 * @param {number}      locationId   id of the location that has to be deleted
 * @param {string}      locationName name of the location to be deleted
 * @param {string|null} notDeletable determines if the location can be deleted or not
 */
// eslint-disable-next-line no-unused-vars
function deleteLocation(locationId, locationName, notDeletable) {
    if (notDeletable) {
        document.getElementById('delete').style.setProperty('display', 'none', 'important');
        document.getElementById('exitButton').innerHTML = '<i class="fa fa-check-square" aria-hidden="true"></i> Ok';
        document.getElementById('exampleModalLabel').innerHTML = `Unable to delete ${campaignName}`;
        document.getElementById('modal-body').innerHTML = '' +
            "Location can't be deleted as long as there are systems referencing this location. "
            + 'Please remove the systems referencing this location in order to remove the '
            + 'location.';
        return;
    }

    setPopup(campaignId, campaignName, 'campaigns');
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
