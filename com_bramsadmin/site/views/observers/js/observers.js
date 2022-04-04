/* global $ */
const sortDescFlags = {
    code: true,         // next sort method for the observer code table header (true = desc, false = asc)
    firstName: false,   // next sort method for the observer first name table header (true = desc, false = asc)
    lastName: false,    // next sort method for the observer last name table header (true = desc, false = asc)
    eMail: false,       // next sort method for the observer e-mail table header (true = desc, false = asc)
};
// eslint-disable-next-line no-unused-vars
let log = 'Nothing to show';    // variable contains log messages if something was logged
let observers;

// function stop the onclick property from .tableRow classes
// from firing when clicking on a button inside a .tableRow class
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
    observers.forEach(
        (observer) => {
            HTMLString += `
                <tr
                    class="tableRow"
                    onclick="window.location.href=
                        '/index.php?'
                        + 'option=com_bramsadmin'
                        + '&view=observerEdit'
                        + '&id=${observer.id}';"
                >
                    <td>${observer.code}</td>
                    <td>${observer.first_name}</td>
                    <td>${observer.last_name}</td>
                    <td>${observer.email}</td>
                    <td>
                        <button
                            type='button'
                            class='customBtn edit'
                            onclick="window.location.href=
                                '/index.php?'
                                + 'option=com_bramsadmin'
                                + '&view=observerEdit'
                                + '&id=${observer.id}';"
                        >
                            <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                            Edit
                        </button>
                        <button
                            type='button'
                            class='customBtn delete'
                            onclick="deleteObserver(
                                ${observer.id},
                                '${observer.first_name} ${observer.last_name}',
                                ${observer.not_deletable}
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

    document.getElementById('observers').innerHTML = HTMLString;
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
        observers.sort((first, second) => sortDesc(first[attribute], second[attribute], noSpace));
    } else {
        // sort asc
        observers.sort((first, second) => sortAsc(first[attribute], second[attribute], noSpace));
    }

    // toggle the sorting method
    sortDescFlags[attribute] = !sortDescFlags[attribute];

    setSortIcon(headerElement);
    generateTable();
}

/**
 * Calls an api to delete the observer with id equal to 'systemId' argument.
 * If the system was successfully deleted, it updates the html table.
 *
 * @param {number}      observerId   id of the observer that has to be deleted
 * @param {string}      observerName name of the observer's location to be deleted
 * @param {string|null} notDeletable determines if the observer can be deleted or not
 */
function deleteObserver(observerId, observerName, notDeletable) {
    if (notDeletable !== null) {
        alert(
            "Observer can't be deleted as long as there are locations referencing this observer.\n"
            + 'Please remove the locations referencing this observer in order to remove the'
            + ' observer.',
        );
        return;
    }

    if (!confirm(`Are you sure you want to delete ${observerName}`)) return;
    const token = $('#token').attr('name');

    $.ajax({
        type: 'DELETE',
        url: `
            /index.php?
            option=com_bramsadmin
            &task=delete
            &view=observers
            &format=json
            &id=${observerId}
            &${token}=1
        `,
        success: (response) => {
            // on success, update the html table by removing the system from it
            const isDeletedElement = (element) => Number(element.id) === observerId;
            observers.splice(observers.findIndex(isDeletedElement), 1);
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
 * Function calls an api to get all the observers from the back-end. If no error occurs
 * it should receive the id, observer code, first name, last name and e-mail for each
 * observer.
 */
function getObservers() {
    // get the token
    const token = $('#token').attr('name');

    $.ajax({
        type: 'GET',
        url: `
            /index.php?
            option=com_bramsadmin
            &task=getAll
            &view=observers
            &format=json
            &${token}=1
        `,
        success: (response) => {
            observers = response.data;
            observers.sort((first, second) => sortAsc(first.code, second.code));
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

window.onload = getObservers;
