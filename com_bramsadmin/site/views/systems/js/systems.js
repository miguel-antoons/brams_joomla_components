/* global systems */
/* global $ */
function onPageLoad() {
    let HTMLString = '';

    systems.forEach(
        (system) => {
            HTMLString += `
                <tr>
                    <td>
                        ${system[2]}
                    </td>
                    <td>
                        ${system[1]}
                    </td>
                    <td>
                        ${system[3]}
                    </td>
                    <td>
                        ${system[4]}
                    </td>
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
