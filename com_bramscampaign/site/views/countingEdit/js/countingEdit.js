/* eslint-disable no-global-assign */
// * cf. ../../_js/edit.js
// eslint-disable-next-line no-unused-vars
/* global $, elementId, codes, log, apiFailMessg, newElement, updateElement, getCodes */
const currentView = 'countingEdit';
const redirectView = 'countings';

function getSpectrograms() {
    const token = $('#token').attr('name');

    $.ajax({
        type: 'GET',
        url: `
            /index.php?
            option=com_bramscampaign
            &task=getSpectrograms
            &model=spectrogram,campaigns
            &view=${currentView}
            &format=json
            &id=${elementId}
            &${token}=1
        `,
        success: (response) => {
            console.log(response);
        },
        error: (response) => {
            // on fail, show an error message
            document.getElementById('error').innerHTML = (
                'API call failed, please read the \'log\' variable in '
                + 'developer console for more information about the problem.'
            );
            // store the server response in the log variable
            log = response;
        },
    });
}

function onLoad() {
    // get the id from url
    const paramString = window.location.search.split('?')[1];
    const queryString = new URLSearchParams(paramString);
    elementId = Number(queryString.get('id'));

    if (elementId) {
        getSpectrograms();
    }
}

window.onload = onLoad;
