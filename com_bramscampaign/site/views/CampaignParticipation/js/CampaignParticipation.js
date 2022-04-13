/* eslint-disable no-global-assign */
// * cf. ../../_js/list.js
// eslint-disable-next-line no-unused-vars
/* global $, log, elements, sortAsc, sortDesc, stopPropagation, deleteRow, apiFailMessg */
// eslint-disable-next-line no-unused-vars
const sortDescFlags = {
    name: true,     // next sort method for the name table header (true = desc, false = asc)
    station: false, // next sort method for the station table header (true = desc, false = asc)
    start: false,   // next sort method for the start table header (true = desc, false = asc)
    end: false,     // next sort method for the end table header (true = desc, false = asc)
    type: false,    // next sort method for the type table header (true = desc, false = asc)
};

/**
 * Function generates the campaign table from the elements array.
 * It then renders the table on inside the #campaigns element.
 */
function generateTable() {
    let HTMLString = '';

    // generate a row for each campaign
    elements.forEach(
        (campaign) => {
            HTMLString += `
                <tr class="tableRow">
                    <td>${campaign.name}</td>
                    <td>${campaign.station}</td>
                    <td>${campaign.start}</td>
                    <td>${campaign.end}</td>
                    <td>
                        <button
                            type='button'
                            class='customBtn add'
                            onclick="console.log('hello')"
                        >
                            <i class="fa fa-trash" aria-hidden="true"></i>
                            Add Campaign
                        </button>
                    </td>
                </tr>
            `;
        },
    );

    document.getElementById('campaigns').innerHTML = HTMLString;
    stopPropagation();
}

/**
 * Function calls an api to get all the campaigns from the back-end. If no error occurs
 * it should receive every campaign and its information.
 */
function getCampaigns() {
    // get the token
    const token = $('#token').attr('name');

    $.ajax({
        type: 'GET',
        url: `
            /index.php?
            option=com_bramscampaign
            &task=getAll
            &view=campaignParticipation
            &model=campaigns
            &format=json
            &${token}=1
        `,
        success: (response) => {
            elements = response.data;
            elements.sort((first, second) => sortAsc(first.name, second.name));
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

window.onload = getCampaigns;
