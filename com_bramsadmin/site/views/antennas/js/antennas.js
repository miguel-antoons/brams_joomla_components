/* global $ */
let sortDescFlags = {
    code: true,
    brand: false,
    model: false,
};
// eslint-disable-next-line no-unused-vars
let log = 'Nothing to show';    // variable contains log messages if something was logged
let antennas;

// function stops the onclick property from .systemRow classes
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
 * Function generates the antenna table from the antennas array.
 * It then renders the table on inside the #antennas element.
 */
function generateTable() {
    let HTMLString = '';

    // generate a row for each system
    antennas.forEach(
        (antennas) => {
            HTMLString += `
                <tr
                    class="tableRow"
                    onclick="window.location.href='/index.php?option=com_bramsadmin&view=antennaEdit&id=${antennas.id}';"
                >
                    <td>${antennas.code}</td>
                    <td>${antennas.brand}</td>
                    <td>${antennas.model}</td>
                    <td>
                        <button
                            type='button'
                            class='customBtn edit'
                            onclick="window.location.href='/index.php?option=com_bramsadmin&view=antennaEdit&id=${antennas.id}';"
                        >
                            <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                            Edit
                        </button>
                        <button
                            type='button'
                            class='customBtn delete'
                            onclick="deleteAntenna(${antennas.id}, '${antennas.brand} ${antennas.model}', ${antennas.not_deletable})"
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
    stopPropagation();
}

/**
 * Calls an api to delete the antenna with id equal to 'antennaId' argument.
 * If the antenna was successfully deleted, it updates the html table.
 *
 * @param {number}      antennaId    id of the antenna that has to be deleted
 * @param {string}      antennaName  name of the antenna to be deleted
 * @param {string|null} notDeletable determines if the location can be deleted or not
 */
function deleteAntenna(antennaId, antennaName, notDeletable) {
    if (notDeletable !== null) {
        alert(
            "Antenna can't be deleted as long as there are systems (radsys_system) referencing this antenna.\n" +
            "Please remove the systems referencing this antenna in order to remove the antenna."
        );
        return;
    }

    if (!confirm(`Are you sure you want to delete ${antennaName}`)) return;
    const token = $('#token').attr('name');

    $.ajax({
        type: 'DELETE',
        url: `
            /index.php?
            option=com_bramsadmin
            &view=antennas
            &task=deleteAntenna
            &format=json
            &id=${antennaId}
            &${token}=1
        `,
        success: (response) => {
            // on success, update the html table by removing the system from it
            const isDeletedElement = (element) => Number(element.id) === antennaId;
            antennas.splice(antennas.findIndex(isDeletedElement), 1);
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
 * @param {string}               attribute      antenna attribute to sort on
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
        antennas.sort((first, second) => sortDesc(first[attribute], second[attribute], noSpace));
    } else {
        // sort asc
        antennas.sort((first, second) => sortAsc(first[attribute], second[attribute], noSpace));
    }

    // toggle the sorting method
    sortDescFlags[attribute] = !sortDescFlags[attribute];

    setSortIcon(headerElement);
    generateTable();
}

/**
 * Function calls an api to get all the antennas from the back-end. If no error occurs
 * it should receive the id, brand, model and code for each antenna.
 */
function getAntennas() {
    // get the token
    const token = $('#token').attr('name');

    $.ajax({
        type: 'GET',
        url: `
            /index.php?
            option=com_bramsadmin
            &task=getAntennas
            &view=antennas
            &format=json
            &${token}=1
        `,
        success: (response) => {
            antennas = response.data;
            antennas.sort((first, second) => sortAsc(first.code, second.code));
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

window.onload = getAntennas;
