/* global $ */
const sortDescFlags = {
    code: true,
    brand: false,
    model: false,
};
// eslint-disable-next-line no-unused-vars
let log = 'Nothing to show';    // variable contains log messages if something was logged
let receivers;

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
 * Function generates the receiver table from the receivers array.
 * It then renders the table on inside the #receivers element.
 */
function generateTable() {
    let HTMLString = '';

    // generate a row for each receiver
    receivers.forEach(
        (receiver) => {
            HTMLString += `
                <tr
                    class="tableRow"
                    onclick="window.location.href=
                        '/index.php?option=com_bramsadmin&view=receiverEdit&id=${receiver.id}';"
                >
                    <td>${receiver.code}</td>
                    <td>${receiver.brand}</td>
                    <td>${receiver.model}</td>
                    <td>
                        <button
                            type='button'
                            class='customBtn edit'
                            onclick="window.location.href=
                                '/index.php'
                                + '?option=com_bramsadmin'
                                + '&view=receiverEdit'
                                + '&id=${receiver.id}';"
                        >
                            <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                            Edit
                        </button>
                        <button
                            type='button'
                            class='customBtn delete'
                            onclick=
                                "deleteReceiver(
                                    ${receiver.id},
                                    '${receiver.code}',
                                    ${receiver.not_deletable}
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

    document.getElementById('receivers').innerHTML = HTMLString;
    stopPropagation();
}

/**
 * Calls an api to delete the receiver with id equal to 'receiverId' argument.
 * If the antenna was successfully deleted, it updates the html table.
 *
 * @param {number}      receiverId   id of the receiver that has to be deleted
 * @param {string}      receiverName name of the receiver to be deleted
 * @param {string|null} notDeletable determines if the receiver can be deleted or not
 */
function deleteReceiver(receiverId, receiverName, notDeletable) {
    if (notDeletable !== null) {
        alert(
            'Receiver can\'t be deleted as long as there are systems (radsys_system) '
            + 'referencing this receiver.\nPlease remove the systems referencing this'
            + ' receiver in order to remove the receiver.',
        );
        return;
    }

    if (!confirm(`Are you sure you want to delete ${receiverName}`)) return;
    const token = $('#token').attr('name');

    $.ajax({
        type: 'DELETE',
        url: `
            /index.php?
            option=com_bramsadmin
            &view=receivers
            &task=delete
            &format=json
            &id=${receiverId}
            &${token}=1
        `,
        success: (response) => {
            // on success, update the html table by removing the system from it
            const isDeletedElement = (element) => Number(element.id) === receiverId;
            receivers.splice(receivers.findIndex(isDeletedElement), 1);
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
 * @param {string}               attribute      column to sort on
 * @param {boolean}              noSpace        whether to remove spaces or not from strings when sorting
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
        // sort the receivers array desc
        receivers.sort((first, second) => sortDesc(first[attribute], second[attribute], noSpace));
    } else {
        // sort asc
        receivers.sort((first, second) => sortAsc(first[attribute], second[attribute], noSpace));
    }

    // toggle the sorting method
    sortDescFlags[attribute] = !sortDescFlags[attribute];

    setSortIcon(headerElement);
    generateTable();
}

/**
 * Function calls an api to get all the receivers from the back-end. If no error occurs
 * it should receive the id, brand, model and code for each receiver.
 */
function getReceivers() {
    // get the token
    const token = $('#token').attr('name');

    $.ajax({
        type: 'GET',
        url: `
            /index.php?
            option=com_bramsadmin
            &task=getAll
            &view=receivers
            &format=json
            &${token}=1
        `,
        success: (response) => {
            receivers = response.data;
            receivers.sort((first, second) => sortAsc(first.code, second.code));
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

window.onload = getReceivers;
