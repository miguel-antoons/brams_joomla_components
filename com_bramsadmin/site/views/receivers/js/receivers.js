/* eslint-disable no-global-assign */
// * cf. ../../_js/list.js
// eslint-disable-next-line no-unused-vars
/* global $, log, elements, sortAsc, sortDesc, stopPropagation, deleteRow, apiFailMessg */
// eslint-disable-next-line no-unused-vars
const sortDescFlags = {
    code: true,
    brand: false,
    model: false,
};

/**
 * Function generates the receiver table from the receivers array.
 * It then renders the table on inside the #receivers element.
 */
function generateTable() {
    let HTMLString = '';

    // generate a row for each receiver
    elements.forEach(
        (receiver) => {
            HTMLString += `
                <tr class="tableRow">
                    <td onclick="window.location.href=
                        '/index.php?option=com_bramsadmin&view=receiverEdit&id=${receiver.id}';"
                    >${receiver.code}</td>
                    <td onclick="window.location.href=
                        '/index.php?option=com_bramsadmin&view=receiverEdit&id=${receiver.id}';"
                    >${receiver.brand}</td>
                    <td onclick="window.location.href=
                        '/index.php?option=com_bramsadmin&view=receiverEdit&id=${receiver.id}';"
                    >${receiver.model}</td>
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
                            data-toggle="modal"
                            data-target="#myModal"
                            onclick=
                                "deleteReceiver(
                                    ${receiver.id},
                                    '${receiver.code}',
                                    ${receiver.notDeletable}
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
    // stopPropagation();
}

/**
 * Calls an api to delete the receiver with id equal to 'receiverId' argument.
 * If the antenna was successfully deleted, it updates the html table.
 *
 * @param {number}      receiverId   id of the receiver that has to be deleted
 * @param {string}      receiverName name of the receiver to be deleted
 * @param {boolean}     notDeletable determines if the receiver can be deleted or not
 */
// eslint-disable-next-line no-unused-vars
function deleteReceiver(receiverId, receiverName, notDeletable) {
    if (notDeletable) {
        document.getElementById('delete').style.setProperty('display', 'none', 'important');
        document.getElementById('exitButton').innerHTML = '<i class="fa fa-check-square" aria-hidden="true"></i> Ok';
        document.getElementById('exampleModalLabel').innerHTML = `Unable to delete ${receiverName}`;
        document.getElementById('modal-body').innerHTML = '' +
            'Receiver can\'t be deleted as long as there are systems (radsys_system) '
            + 'referencing this receiver.\nPlease remove the systems referencing this'
            + ' receiver in order to remove the receiver.';
        return;
    }

    setPopup(receiverId, receiverName, 'receivers');
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
            elements = response.data;
            elements.sort((first, second) => sortAsc(first.code, second.code));
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
