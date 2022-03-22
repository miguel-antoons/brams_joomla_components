/* global systems */
/* global $ */
let sortLocationDesc = true;
let sortNameDesc = false;
let sortStartDesc = false;
let sortEndDesc = false;

function generateTable() {
    let HTMLString = '';

    systems.forEach(
        (system) => {
            HTMLString += `
                <tr>
                    <td>${system[1]}</td>
                    <td>${system[2]}</td>
                    <td>${system[3]}</td>
                    <td>${system[4]}</td>
                    <td>
                        <button
                            type='button'
                            onclick="window.location.href='/index.php?option=com_bramsadmin&view=systemedit&id=${system[0]}';"
                        >
                            Edit
                        </button>
                        <button
                            type='button'
                            onclick="deleteSystem(${system[0]})"
                        >
                            Delete
                        </button>
                    </td>
                </tr>
            `;
        },
    );

    document.getElementById('systems').innerHTML = HTMLString;
}

function deleteSystem(systemId) {
    $.ajax({
        type: 'DELETE',
        url: `/index.php?option=com_bramsadmin&view=systemedit&task=deletesystem&format=json&id=${systemId}`,
        success: () => {
            window.location.href = '/index.php?option=com_bramsadmin&view=systems';
        },
        error: (response) => {
            console.log('api call failed', '\n', response);
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
