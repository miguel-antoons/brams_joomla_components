/* global systems */
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
                    </td>
                </tr>
            `;
        },
    );

    document.getElementById('systems').innerHTML = HTMLString;
}
