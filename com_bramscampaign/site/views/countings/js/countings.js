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
            if (response.continue) {
                const button = document.getElementById(id);
                button.classList.add('edit');
                button.classList.remove('add');
                button.innerHTML = '<i class="fa fa-pencil-square" aria-hidden="true"></i> Edit';
            }
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

function downloadSpectrogram(camId, camName, annotatedSpectrograms = false) {
    document.getElementById(`spinner${camId}`).style.display = 'inline-block';
    // get the token
    const token = $('#token').attr('name');

    if (annotatedSpectrograms) {
        location.href = `
            /index.php?
            option=com_bramscampaign
            &task=getSpectrograms
            &view=countings
            &model=spectrogram,campaigns
            &format=zip
            &id=${camId}
            &annotated=1
            &name=${camName}
            &${token}=1
        `;
    } else getCSV(camId, camName);

    setTimeout(`getDownloadStatus('${camId}')`, 500);
}

function generateCSV(csvData, camName) {
    const csvContent = `data:text/csv;charset=utf-8,${
        csvData.map((e) => e.join(',')).join('\n')}`;

    const encodedUri = encodeURI(csvContent);
    const link = document.createElement('a');
    link.setAttribute('href', encodedUri);
    link.setAttribute('download', `${camName}.csv`);
    document.body.appendChild(link); // Required for FF

    link.click();
}

function getCSV(camId, camName) {
    // get the token
    const token = $('#token').attr('name');

    $.ajax({
        type: 'GET',
        url: `
            /index.php?
            option=com_bramscampaign
            &task=getCSV
            &view=countings
            &model=spectrogram,campaigns
            &format=json
            &id=${camId}
            &${token}=1
        `,
        success: (response) => {
            generateCSV(response.data.csv_data, camName);
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

function setPopupTitle(camName, camId) {
    document.getElementById('exampleModalLabel').innerHTML = `
        Download files from ${camName} counting`;

    document.getElementById('downloadAnnotated').onclick = () => {
        downloadSpectrogram(camId, camName, true);
    };
    document.getElementById('downloadCsv').onclick = () => {
        downloadSpectrogram(camId, camName, true);
    };
}

function stopSpinners() {
    const spinners = document.getElementsByClassName('spinner');
    spinners.forEach((spinner) => {
        spinner.style.display = 'none';
    });
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
            campaign.hasParticipated = Number(campaign.hasParticipated);
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
                    <td onclick="
                        countingAction(
                            ${campaign.id},
                            '${campaign.start}',
                            ${campaign.sysId},
                            ${campaign.hasParticipated}
                        )"
                     >${campaign.name}</td>
                    <td onclick="
                        countingAction(
                            ${campaign.id},
                            '${campaign.start}',
                            ${campaign.sysId},
                            ${campaign.hasParticipated}
                        )"
                     >${campaign.station}</td>
                    <td onclick="
                        countingAction(
                            ${campaign.id},
                            '${campaign.start}',
                            ${campaign.sysId},
                            ${campaign.hasParticipated}
                        )"
                     >${campaign.start.slice(0, -3)}</td>
                    <td onclick="
                        countingAction(
                            ${campaign.id},
                            '${campaign.start}',
                            ${campaign.sysId},
                            ${campaign.hasParticipated}
                        )"
                     >${campaign.end.slice(0, -3)}</td>
                    <td>
                        <button
                            id="${campaign.id}"
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
                        <span
                            id="spinner${campaign.id}"
                            class="spinner-border text-success spinner">
                        </span>
                    </td>
                </tr>
            `;
        },
    );

    document.getElementById('campaigns').innerHTML = HTMLString;
    stopSpinners();
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

function getDownloadStatus(camId) {
    // get the token
    const token = $('#token').attr('name');

    $.ajax({
        url: `
            /index.php?
            option=com_bramscampaign
            &task=getDownloadStatus
            &view=countings
            &format=json
            &${token}=1
        `,
        type: 'GET',
        dataType: 'json',
        success: (response) => {
            if (response.status === 'pending') {
                setTimeout('getDownloadStatus(camId)', 500);
            } else {
                document.getElementById(`spinner${camId}`).style.display = 'none';
            }
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
