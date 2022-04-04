/* global $ */
const sortDescFlags = {
    code: true,     // next sort method for the location code table header (true = desc, false = asc)
    name: false,    // next sort method for the name table header (true = desc, false = asc)
    start: false,   // next sort method for the start table header (true = desc, false = asc)
    end: false      // next sort method for the end table header (true = desc, false = asc)
};
// eslint-disable-next-line no-unused-vars
let log = 'Nothing to show';    // variable contains log messages if something was logged
let systems;

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
 * Function generates the system table from the systems array.
 * It then renders the table on inside the #systems element.
 */
function generateTable() {
    let HTMLString = '';

    // generate a row for each system
    systems.forEach(
        (system) => {
            HTMLString += `
                <tr
                    class="tableRow"
                    onclick="window.location.href='/index.php?option=com_bramsadmin&view=systemedit&id=${system.id}';"
                >
                    <td>${system.code}</td>
                    <td>${system.name}</td>
                    <td>${system.start}</td>
                    <td>${system.end}</td>
                    <td>
                        <button
                            type='button'
                            class='customBtn edit'
                            onclick="window.location.href='/index.php?option=com_bramsadmin&view=systemedit&id=${system.id}';"
                        >
                            <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                            Edit
                        </button>
                        <button
                            type='button'
                            class='customBtn delete'
                            onclick="deleteSystem(${system.id}, '${system.name}')"
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
 * @param {number} systemId id of the system that has to be deleted
 * @param {string} locationName name of the systems' location to be deleted
 */
function deleteSystem(systemId, locationName) {
    if (!confirm(`Are you sure you want to delete ${locationName}`)) return;
    const token = $('#token').attr('name');

    $.ajax({
        type: 'DELETE',
        url: `/index.php?option=com_bramsadmin&view=systems&task=deletesystem&format=json&id=${systemId}&${token}=1`,
        success: (response) => {
            // on success, update the html table by removing the system from it
            const isDeletedElement = (element) => Number(element.id) === systemId;
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
        systems.sort((first, second) => sortDesc(first[attribute], second[attribute], noSpace));
    } else {
        // sort asc
        systems.sort((first, second) => sortAsc(first[attribute], second[attribute], noSpace));
    }

    // toggle the sorting method
    sortDescFlags[attribute] = !sortDescFlags[attribute];

    setSortIcon(headerElement);
    generateTable();
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
        url: `/index.php?option=com_bramsadmin&view=systems&task=getsystems&format=json&${token}=1`,
        success: (response) => {
            systems = response.data;
            systems.sort((first, second) => sortAsc(first.code, second.code));
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
