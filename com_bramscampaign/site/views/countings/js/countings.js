/* eslint-disable no-global-assign */
// * cf. ../../_js/list.js
// eslint-disable-next-line no-unused-vars
/* global $, log, elements, sortAsc, sortDesc, stopPropagation, deleteRow, apiFailMessg */
// eslint-disable-next-line no-unused-vars
const sortDescFlags = {
    name: true,             // next sort method for the name table header (true = desc, false = asc)
    station: false,         // next sort method for the station table header (true = desc, false = asc)
    start: false,           // next sort method for the start table header (true = desc, false = asc)
    end: false,             // next sort method for the end table header (true = desc, false = asc)
    type: false,            // next sort method for the type table header (true = desc, false = asc)
    hasParticipated: true,  // next sort method for the actions' table header (true = desc, false = asc)
};

function createCounting(id, start, systemId) {
    // get the token
    const token = $('#token').attr('name');

    $.ajax({
        type: 'POST',
        url: `
            /index.php?
            option=com_bramscampaign
            &task=create
            &view=countings
            &format=json
            &${token}=1
        `,
        data: {
            counting_info: {
                campaign_id: id,
                start_date: start,
                system_id: systemId,
            },
        },
        success: (response) => {
            console.log(response);
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

function editCounting(camId) {
    window.location.href = `
        /index.php?
        option=com_bramscampaign
        &view=countingEdit
        &id=${camId}
    `;
}

function countingAction(camId, camStart, camSysId, camHasParticipated) {
    if (camHasParticipated) {
        return editCounting(camId);
    }
    return createCounting(camId, camStart, camSysId);
}

function downloadSpectrogram(camId, annotatedSpectrograms = false) {
    // get the token
    const token = $('#token').attr('name');
    let task;
    if (annotatedSpectrograms) task = 'getCSV';
    else task = 'getSpectrograms';

    location.href = `
        /index.php?
        option=com_bramscampaign
        &task=${task}
        &view=countings
        &model=spectrogram,campaigns
        &format=zip
        &id=${camId}
        &annotated=1
        &${token}=1
    `;

    // $.ajax({
    //     type: 'GET',
    //     url: `
    //         /index.php?
    //         option=com_bramscampaign
    //         &task=${task}
    //         &view=countings
    //         &format=zip
    //         &${token}=1
    //     `,
    //     success: (response, text, xhr) => {
    //         console.log('hello');
    //         // console.log(response, text, xhr);
    //     },
    //     error: (response) => {
    //         // on fail, show an error message
    //         document.getElementById('message').innerHTML = (
    //             'API call failed, please read the \'log\' variable in '
    //             + 'developer console for more information about the problem.'
    //         );
    //         // store the server response in the log variable
    //         log = response;
    //     },
    // });
}

function setPopupTitle(camName, camId) {
    document.getElementById('exampleModalLabel').innerHTML = `
        Download Spectrograms from ${camName} counting`;

    document.getElementById('downloadOriginal').onclick = () => downloadSpectrogram(camId);
}

/**
 * Function generates the campaign table from the elements array.
 * It then renders the table on inside the #campaigns element.
 */
function generateTable() {
    let HTMLString = '';

    // generate a row for each campaign
    elements.forEach(
        (campaign) => {
            let buttonText;
            let addClass;

            if (campaign.hasParticipated) {
                buttonText = '<i class="fa fa-pencil-square" aria-hidden="true"></i> Edit';
                addClass = 'edit';

            } else {
                buttonText = '<i class="fa fa-plus-square" aria-hidden="true"></i> Add';
                addClass = 'add';
            }

            HTMLString += `
                <tr class="tableRow">
                    <td>${campaign.name}</td>
                    <td>${campaign.station}</td>
                    <td>${campaign.start.slice(0, -3)}</td>
                    <td>${campaign.end.slice(0, -3)}</td>
                    <td>
                        <button
                            type='button'
                            class='customBtn ${addClass}'
                            onclick="countingAction(
                                ${campaign.id},
                                '${campaign.start}',
                                ${campaign.sysId},
                                ${campaign.hasParticipated}
                            )"
                        >
                            ${buttonText}
                        </button>
                        <button
                            type="button"
                            class="customBtn down"
                            data-toggle="modal"
                            data-target="#myModal"
                            onclick="setPopupTitle('${campaign.name}', ${campaign.id})"
                        >
                            <i class="fa fa-download" aria-hidden="true"></i>
                        </button>
                    </td>
                </tr>
            `;
        },
    );

    document.getElementById('campaigns').innerHTML = HTMLString;
    // stopPropagation();
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
            &view=countings
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