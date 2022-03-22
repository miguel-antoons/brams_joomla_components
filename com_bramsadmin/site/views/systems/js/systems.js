/* global systems */
/* global $ */
let sortLocationDesc = true;
let sortNameDesc = false;
let sortStartDesc = false;
let sortEndDesc = false;
// eslint-disable-next-line no-unused-vars
let log = 'Nothing to show';

function stopPropagation() {
    $('.systemRow button').on('click', (e) => {
        e.stopPropagation();
    });
}

function generateTable() {
    let HTMLString = '';

    systems.forEach(
        (system) => {
            HTMLString += `
                <tr
                    class='systemRow'
                    onclick="window.location.href='/index.php?option=com_bramsadmin&view=systemedit&id=${system[0]}';"
                >
                    <td>${system[1]}</td>
                    <td>${system[2]}</td>
                    <td>${system[3]}</td>
                    <td>${system[4]}</td>
                    <td>
                        <button
                            type='button'
                            class='customBtn edit'
                            onclick="window.location.href='/index.php?option=com_bramsadmin&view=systemedit&id=${system[0]}';"
                        >
                            <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                            Edit
                        </button>
                        <button
                            type='button'
                            class='customBtn delete'
                            onclick="deleteSystem(${system[0]})"
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

function deleteSystem(systemId) {
    $.ajax({
        type: 'DELETE',
        url: `/index.php?option=com_bramsadmin&view=systemedit&task=deletesystem&format=json&id=${systemId}`,
        success: (response) => {
            const isDeletedElement = (element) => element[0] === systemId;
            systems.splice(systems.findIndex(isDeletedElement), 1);
            generateTable();
            document.getElementById('message').innerHtml = response.data.message;
        },
        error: (response) => {
            document.getElementById('message').innerHTML = (
                'API call failed, please read the \'log\' variable in ',
                'developper console for more information about the problem.'
            );
            log = response;
        },
    });
}

/**
 * Function changes the sort icon to the last clicked table header.
 * @param {html} headerElement table header that was clicked for sorting
 */
function setSortIcon(headerElement) {
    // remove the sort icon from the page
    document.getElementById('sortIcon').remove();
    // add the icon to the clicked element ('headerElement')
    headerElement.innerHTML += '<i id="sortIcon" class="fa fa-sort" aria-hidden="true"></i>';
}

function sortLocation(headerElement) {
    sortNameDesc = false;
    sortStartDesc = false;
    sortEndDesc = false;

    if (sortLocationDesc) {
        systems.sort((first, second) => first[1] < second[1]);
    } else {
        systems.sort((first, second) => first[1] > second[1]);
    }

    sortLocationDesc = !sortLocationDesc;

    setSortIcon(headerElement);
    generateTable();
}

function sortName(headerElement) {
    sortLocationDesc = false;
    sortStartDesc = false;
    sortEndDesc = false;

    if (sortNameDesc) {
        systems.sort((first, second) => first[2] < second[2]);
    } else {
        systems.sort((first, second) => first[2] > second[2]);
    }

    sortNameDesc = !sortNameDesc;

    setSortIcon(headerElement);
    generateTable();
}

function sortStart(headerElement) {
    sortNameDesc = false;
    sortLocationDesc = false;
    sortEndDesc = false;

    if (sortStartDesc) {
        systems.sort((first, second) => first[3] < second[3]);
    } else {
        systems.sort((first, second) => first[3] > second[3]);
    }

    sortStartDesc = !sortStartDesc;

    setSortIcon(headerElement);
    generateTable();
}

function sortEnd(headerElement) {
    sortNameDesc = false;
    sortStartDesc = false;
    sortLocationDesc = false;

    if (sortEndDesc) {
        systems.sort((first, second) => first[4] < second[4]);
    } else {
        systems.sort((first, second) => first[4] > second[4]);
    }

    sortEndDesc = !sortEndDesc;

    setSortIcon(headerElement);
    generateTable();
}

function onPageLoad() {
    systems.sort((first, second) => first[1] > second[1]);
    generateTable();
}
