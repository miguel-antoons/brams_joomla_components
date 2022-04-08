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
                <tr
                    class="tableRow"
                    onclick="window.location.href=
                        '/index.php?'
                        + 'option=com_bramscampaign'
                        + '&view=camapignEdit'
                        + '&id=${campaign.id}';"
                >
                    <td>${campaign.name}</td>
                    <td>${campaign.type}</td>
                    <td>${campaign.station}</td>
                    <td>${campaign.start}</td>
                    <td>${campaign.end}</td>
                    <td>
                        <button
                            type='button'
                            class='customBtn edit'
                            onclick="window.location.href=
                                '/index.php?'
                                + 'option=com_bramscampaign'
                                + '&view=campaignEdit'
                                + '&id=${campaign.id}';"
                        >
                            <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                        </button>
                        <button
                            type='button'
                            class='customBtn delete'
                            onclick="deleteCampaign(
                                ${campaign.id},
                                '${campaign.name}',
                                ${campaign.notDeletable}
                            )"
                        >
                            <i class="fa fa-trash" aria-hidden="true"></i>
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
 * Calls an api to delete the campaign with id equal to 'campaignId' argument.
 * If the campaign was successfully deleted, it updates the html table.
 *
 * @param {number}      campaignId   id of the campaign that has to be deleted
 * @param {string}      campaignName name of the campaign to be deleted
 * @param {string|null} notDeletable determines if the campaign can be deleted or not
 */
// eslint-disable-next-line no-unused-vars
function deleteCampaign(campaignId, campaignName, notDeletable) {
    if (notDeletable !== null) {
        // eslint-disable-next-line no-alert
        alert(
            "Campaign can't be deleted as long as there are campaign countings referencing this campaign.\n"
            + 'Please remove the countings referencing this campaign in order to remove the '
            + 'campaign.',
        );
        return;
    }

    deleteRow(campaignId, campaignName, 'campaigns');
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
            &view=campaigns
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
