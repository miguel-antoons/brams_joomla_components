/* eslint-disable no-global-assign */
// * cf. ../../_js/list.js
// eslint-disable-next-line no-unused-vars
/* global $, log, elements, sortAsc, sortDesc, stopPropagation, deleteRow, apiFailMessg */
// eslint-disable-next-line no-unused-vars
const sortDescFlags = {
    code: true,         // next sort method for the observer code table header (true = desc, false = asc)
    firstName: false,   // next sort method for the observer first name table header (true = desc, false = asc)
    lastName: false,    // next sort method for the observer last name table header (true = desc, false = asc)
    eMail: false,       // next sort method for the observer e-mail table header (true = desc, false = asc)
};

/**
 * Function generates the observer table from the elements array.
 * It then renders the table on inside the #observers element.
 */
function generateTable() {
    let HTMLString = '';

    // generate a row for each observer
    elements.forEach(
        (observer) => {
            HTMLString += `
                <tr class="tableRow">
                    <td onclick="window.location.href=
                        '/index.php?'
                        + 'option=com_bramsadmin'
                        + '&view=observerEdit'
                        + '&id=${observer.id}';"
                    >${observer.code}</td>
                    <td onclick="window.location.href=
                        '/index.php?'
                        + 'option=com_bramsadmin'
                        + '&view=observerEdit'
                        + '&id=${observer.id}';"
                    >${observer.first_name}</td>
                    <td onclick="window.location.href=
                        '/index.php?'
                        + 'option=com_bramsadmin'
                        + '&view=observerEdit'
                        + '&id=${observer.id}';"
                    >${observer.last_name}</td>
                    <td onclick="window.location.href=
                        '/index.php?'
                        + 'option=com_bramsadmin'
                        + '&view=observerEdit'
                        + '&id=${observer.id}';"
                    >${observer.email}</td>
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
                            data-toggle="modal"
                            data-target="#myModal"
                            onclick="deleteObserver(
                                ${observer.id},
                                '${observer.first_name} ${observer.last_name}',
                                ${observer.notDeletable}
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
    // stopPropagation();
}

/**
 * Calls an api to delete the observer with id equal to 'systemId' argument.
 * If the system was successfully deleted, it updates the html table.
 *
 * @param {number}      observerId   id of the observer that has to be deleted
 * @param {string}      observerName name of the observer's location to be deleted
 * @param {string|null} notDeletable determines if the observer can be deleted or not
 */
// eslint-disable-next-line no-unused-vars
function deleteObserver(observerId, observerName, notDeletable) {
    if (notDeletable) {
        document.getElementById('delete').style.setProperty('display', 'none', 'important');
        document.getElementById('exitButton').innerHTML = '<i class="fa fa-check-square" aria-hidden="true"></i> Ok';
        document.getElementById('exampleModalLabel').innerHTML = `Unable to delete ${observerName}`;
        document.getElementById('modal-body').innerHTML = '' +
            "Observer can't be deleted as long as there are locations referencing this observer. "
            + 'Please remove the locations referencing this observer in order to remove the'
            + ' observer.';
        return;
    }

    setPopup(observerId, observerName, 'observers');
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
            elements = response.data;
            elements.sort((first, second) => sortAsc(first.code, second.code));
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

window.onload = getObservers;
