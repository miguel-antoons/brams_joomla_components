/* eslint-disable no-global-assign */
// * cf. ../../_js/list.js
// eslint-disable-next-line no-unused-vars
/* global $, log, elements, sortAsc, sortDesc, stopPropagation, deleteRow, apiFailMessg */
// eslint-disable-next-line no-unused-vars
const sortDescFlags = {
    code: true,     // next sort method for the digitizer code table header (true = desc, false = asc)
    brand: false,   // next sort method for the brand table header (true = desc, false = asc)
    model: false,   // next sort method for the model table header (true = desc, false = asc)
};

/**
 * Function generates the digitizer table from the digitizers array.
 * It then renders the table on inside the #digitizers element.
 */
function generateTable() {
    let HTMLString = '';

    // generate a row for each digitizer
    elements.forEach(
        (digitizer) => {
            HTMLString += `
                <tr class="tableRow">
                    <td onclick="window.location.href=
                        '/index.php?'
                        + 'option=com_bramsadmin'
                        + '&view=digitizerEdit'
                        + '&id=${digitizer.id}';"
                    >${digitizer.code}</td>
                    <td onclick="window.location.href=
                        '/index.php?'
                        + 'option=com_bramsadmin'
                        + '&view=digitizerEdit'
                        + '&id=${digitizer.id}';"
                    >${digitizer.brand}</td>
                    <td onclick="window.location.href=
                        '/index.php?'
                        + 'option=com_bramsadmin'
                        + '&view=digitizerEdit'
                        + '&id=${digitizer.id}';"
                    >${digitizer.model}</td>
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
                            data-toggle="modal"
                            data-target="#myModal"
                            onclick="deleteDigitizer(
                                ${digitizer.id},
                                '${digitizer.code}',
                                ${digitizer.notDeletable})"
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
    // stopPropagation();
}

/**
 * Calls an api to delete the digitizer with id equal to 'digitizerId' argument.
 * If the digitizer was successfully deleted, it updates the html table.
 *
 * @param {number}      digitizerId     id of the digitizer that has to be deleted
 * @param {string}      digitizerName   name of the digitizer to be deleted
 * @param {null|string} notDeletable    Indicates if the element can be deleted
 */
// eslint-disable-next-line no-unused-vars
function deleteDigitizer(digitizerId, digitizerName, notDeletable) {
    if (notDeletable) {
        document.getElementById('delete').style.setProperty('display', 'none', 'important');
        document.getElementById('exitButton').innerHTML = '<i class="fa fa-check-square" aria-hidden="true"></i> Ok';
        document.getElementById('exampleModalLabel').innerHTML = `Unable to delete ${digitizerName}`;
        document.getElementById('modal-body').innerHTML = '' +
            'Digitizer can\'t be deleted as long as there are systems (radsys_system) '
            + 'referencing this digitizer. Please remove the systems referencing this'
            + ' digitizer in order to remove the digitizer.';
        return;
    }

    setPopup(digitizerId, digitizerName, 'digitizers');
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

window.onload = getDigitizers;
