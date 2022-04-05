/* global $ */
const sortDescFlags = {
    code: true,     // next sort method for the digitizer code table header (true = desc, false = asc)
    brand: false,   // next sort method for the brand table header (true = desc, false = asc)
    model: false,   // next sort method for the model table header (true = desc, false = asc)
};
// eslint-disable-next-line no-unused-vars
let log = 'Nothing to show';    // variable contains log messages if something was logged
let digitizers;

// function stops the onclick property from .tableRow classes
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
 * Function generates the digitizer table from the digitizers array.
 * It then renders the table on inside the #digitizers element.
 */
function generateTable() {
    let HTMLString = '';

    // generate a row for each digitizer
    digitizers.forEach(
        (digitizer) => {
            HTMLString += `
                <tr
                    class="tableRow"
                    onclick="window.location.href=
                        '/index.php?'
                        + 'option=com_bramsadmin'
                        + '&view=digitizerEdit'
                        + '&id=${digitizer.id}';"
                >
                    <td>${digitizer.code}</td>
                    <td>${digitizer.brand}</td>
                    <td>${digitizer.model}</td>
                    <td>
                        <button
                            type='button'
                            class='customBtn edit'
                            onclick="window.location.href=
                                '/index.php?'
                                + 'option=com_bramsadmin'
                                + '&view=digitizerEdit'
                                + '&id=${digitizer.id}';"
                        >
                            <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                            Edit
                        </button>
                        <button
                            type='button'
                            class='customBtn delete'
                            onclick="deleteDigitizer(
                                ${digitizer.id},
                                '${digitizer.code}',
                                ${digitizer.not_deletable})"
                        >
                            <i class="fa fa-trash" aria-hidden="true"></i>
                            Delete
                        </button>
                    </td>
                </tr>
            `;
        },
    );

    document.getElementById('digitizers').innerHTML = HTMLString;
    stopPropagation();
}

/**
 * Calls an api to delete the digitizer with id equal to 'digitizerId' argument.
 * If the digitizer was successfully deleted, it updates the html table.
 *
 * @param {number}      digitizerId     id of the digitizer that has to be deleted
 * @param {string}      digitizerName   name of the digitizer to be deleted
 * @param {null|string} notDeletable    Indicates if the element can be deleted
 */
function deleteDigitizer(digitizerId, digitizerName, notDeletable) {
    if (notDeletable !== null) {
        alert(
            'Digitizer can\'t be deleted as long as there are systems (radsys_system) '
            + 'referencing this digitizer.\nPlease remove the systems referencing this'
            + ' digitizer in order to remove the digitizer.',
        );
        return;
    }

    // eslint-disable-next-line no-alert
    if (!confirm(`Are you sure you want to delete ${digitizerName}`)) return;
    const token = $('#token').attr('name');

    $.ajax({
        type: 'DELETE',
        url: `
            /index.php?
            option=com_bramsadmin
            &task=delete
            &view=digitizers
            &format=json
            &id=${digitizerId}
            &${token}=1
        `,
        success: (response) => {
            // on success, update the html table by removing the digitizer from it
            const isDeletedElement = (element) => Number(element.id) === digitizerId;
            digitizers.splice(digitizers.findIndex(isDeletedElement), 1);
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
 * @param {string}               attribute      digitizer attribute to sort on
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
        digitizers.sort((first, second) => sortDesc(first[attribute], second[attribute], noSpace));
    } else {
        // sort asc
        digitizers.sort((first, second) => sortAsc(first[attribute], second[attribute], noSpace));
    }

    // toggle the sorting method
    sortDescFlags[attribute] = !sortDescFlags[attribute];

    setSortIcon(headerElement);
    generateTable();
}

/**
 * Function calls an api to get all the digitizers from the back-end. If no error occurs
 * it should receive the id, brand, digitizer code and model for each digitizer.
 */
function getDigitizers() {
    // get the token
    const token = $('#token').attr('name');

    $.ajax({
        type: 'GET',
        url: `
            /index.php?
            option=com_bramsadmin
            &task=getAll
            &view=digitizers
            &format=json
            &${token}=1
        `,
        success: (response) => {
            digitizers = response.data;
            digitizers.sort((first, second) => sortAsc(first.code, second.code));
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

window.onload = getDigitizers;
