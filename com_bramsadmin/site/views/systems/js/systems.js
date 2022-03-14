/* global systems */
function onPageLoad() {
    let HTMLString = '';

    systems.foreach(
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
                            onclick="window.lcation='index.php?option=com_bramsadmin&view=system_edit&id=${system[0]}"
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
