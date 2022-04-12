/* eslint-disable no-global-assign */
// * cf. ../../_js/edit.js
// eslint-disable-next-line no-unused-vars
/* global $, elementId, codes, log, apiFailMessg, newElement, updateElement, getCodes */
const currentView = 'campaignEdit';
const redirectView = 'campaigns';
let campaignNames = [];           // array with all taken system names

/**
 * Function checks if all the required inputs have values in them. It doesn't
 * check the values itself, just if there is something.
 *
 * @param name          {string}    campaignName input value
 * @param type          {string}    campaignType input value
 * @param station       {string}    campaignStation input value
 * @param start         {string}    campaignStart input value
 * @param end           {string}    campaignEnd input value
 * @param oldIsValid    {boolean}   flag that determines if values are valid or not
 * @returns             {(*|string)[]|(boolean|string)[]}
 *                                  Returns an array with 2 values :
 *                                      0: new isValid flag
 *                                      1: error message if an input is empty
 */
function verifyRequired(
    name,
    type,
    station,
    start,
    end,
    oldIsValid
) {
    // if one of the required inputs are empty
    if (!name || !type || !station || !start || !end) {
        // add an exclamation circle to the required inputs
        const requiredInputs = document.getElementsByClassName('required');
        Array.from(requiredInputs).forEach(
            (input) => {
                input.innerHTML
                    += '<i class="fa fa-exclamation-circle orange right" aria-hidden="true"></i>';
            },
        );

        // add an error text
        return [
            false,
            `<li>
                <i class="fa fa-exclamation-circle orange" aria-hidden="true"></i>
                Please fill all required inputs before submitting the form. 
                Required inputs are Name, Type, Station, Start and End.
            </li>`,
        ];
    }

    return [oldIsValid, ''];
}

/**
 * Function verifies if the entered campaign name doesn't exist already.
 * This is done so that each campaign name is unique.
 *
 * @param name          {string}    campaignName input value
 * @param oldIsValid    {boolean}   flag that determines if values are valid or not
 * @returns             {(*|string)[]|(boolean|string)[]}
 *                                  Returns an array with 2 values :
 *                                      0: new isValid flag
 *                                      1: error message if the campaign name already exists
 */
function verifyName(name, oldIsValid) {
    const pattern = /^[a-z\d\-_]+$/i;
    // if the antenna code already exists
    if (campaignNames.includes(name)) {
        document.getElementById('name').innerHTML += ''
            + '<i class="fa fa-exclamation-circle red right" aria-hidden="true"></i>';

        // set the valid flag to false and return an error message
        return [
            false,
            `<li>
                <i class="fa fa-exclamation-circle red" aria-hidden="true"></i>
                Entered campaign name is already taken. Please enter a free campaign name.
            </li>`,
        ];
    }

    // test if any forbidden characters are in the code
    if (!pattern.test(name)) {
        document.getElementById('name').innerHTML += ''
            + '<i class="fa fa-exclamation-circle red right" aria-hidden="true"></i>';

        // set the valid flag to false and return an error message
        return [
            false,
            `<li>
                <i class="fa fa-exclamation-circle red" aria-hidden="true"></i>
                Entered campaign name contains forbidden characters. Be sure to only use dash, 
                underscore and alphanumeric characters.
            </li>`,
        ];
    }

    return [oldIsValid, ''];
}

/**
 * Function checks if the start date comes before the end date. If not, an error
 * message is returned.
 *
 * @param startDate     {string}    chosen start date by the user
 * @param endDate       {string}    chosen end date by the user
 * @param oldIsValid    {boolean}   old isValid flag
 * @returns             {(*|string)[]|(boolean|string)[]}
 *                                  Returns an array with 2 values :
 *                                      0: new isValid flag
 *                                      1: error message if the start date comes after the end date
 */
function verifyDates(startDate, endDate, oldIsValid) {
    if (Date.parse(startDate) >= Date.parse(endDate)) {
        document.getElementById('start').innerHTML += ''
            + '<i class="fa fa-exclamation-circle red right" aria-hidden="true"></i>';
        document.getElementById('end').innerHTML += ''
            + '<i class="fa fa-exclamation-circle red right" aria-hidden="true"></i>';

        // set the valid flag to false and return an error message
        return [
            false,
            `<li>
                <i class="fa fa-exclamation-circle red" aria-hidden="true"></i>
                Entered start date is bigger or equal to entered end date. 
                Either enter a start date that comes before the end date or an end date 
                that comes after the start date. 
            </li>`,
        ];
    }

    return [oldIsValid, ''];
}

/**
 * Function verifies that entered value is between 0 and
 * 99999999999. If one of these conditions is not fulfilled, the
 * function returns an error message.
 * If the verifyFloat value is set to false, it will also verify
 * that the entered value is an int.
 *
 * @param stringIntValue    {string}    string number coming from a html input
 * @param valueName         {string}    label of the given number (used for showing error messages)
 * @param verifyFloat       {boolean}   indicates that the entered value may be a float
 * @param oldIsValid        {boolean}   old isValid flag
 * @returns             {(*|string)[]|(boolean|string)[]}
 *                                  Returns an array with 2 values :
 *                                      0: new isValid flag
 *                                      1: error message if one of the conditions is not fulfilled
 */
function verifyNumber(stringIntValue, valueName, verifyFloat, oldIsValid) {
    const intValue = Number(stringIntValue);

    // if the entered value is out of range
    if (intValue > 99999999999 || intValue < 0) {
        document.getElementById(valueName).innerHTML += ''
            + '<i class="fa fa-exclamation-circle red right" aria-hidden="true"></i>';

        // set the valid flag to false and return an error message
        return [
            false,
            `<li>
                <i class="fa fa-exclamation-circle red" aria-hidden="true"></i>
                Invalid ${valueName}. 
                ${valueName} must be a positive number between 0 and 99999999999.
            </li>`,
        ];
    }

    // if the entered value is actually a float number
    if (intValue % 1 && !verifyFloat) {
        document.getElementById(valueName).innerHTML += ''
            + '<i class="fa fa-exclamation-circle red right" aria-hidden="true"></i>';
        // set the valid flag to false and return an error message
        return [
            false,
            `<li>
                <i class="fa fa-exclamation-circle red" aria-hidden="true"></i>
                Invalid ${valueName}. 
                ${valueName} must be an int and not a float.
            </li>`,
        ];
    }

    return [oldIsValid, ''];
}

/**
 * Function verifies if the entered values are valid upon api call. If all the
 * values are valid, the function returns true. If not it returns false.
 *
 * @param name      {string}
 * @param type      {string}
 * @param station   {string}
 * @param start     {string}
 * @param end       {string}
 * @param fft       {string}
 * @param overlap   {string}
 * @param colorMin  {string}
 * @param colorMax  {string}
 *
 * @returns         {boolean}   true if the inputs are valid, else returns false
 */
function verifyValues(
    name,
    type,
    station,
    start,
    end,
    fft,
    overlap,
    colorMin,
    colorMax,
) {
    // remove all the icons inside the labels
    const icons = document.querySelectorAll('label .fa');
    let verificationResult;
    icons.forEach((icon) => icon.remove());

    let isValid = true;
    let HTMLError = '<h4>Found Problems</h4><ul>';

    // check if all required inputs are filled
    verificationResult = verifyRequired(
        name,
        type,
        station,
        start,
        end,
        fft,
        overlap,
        colorMin,
        colorMax,
        isValid,
    );
    [isValid] = verificationResult;
    HTMLError += verificationResult[1];

    // check if campaign name is valid
    verificationResult = verifyName(name, isValid);
    [isValid] = verificationResult;
    HTMLError += verificationResult[1];

    // check if dates are correct
    verificationResult = verifyDates(start, end, isValid);
    [isValid] = verificationResult;
    HTMLError += verificationResult[1];

    // check if fft is correct
    verificationResult = verifyNumber(fft, 'FFT', false, isValid);
    [isValid] = verificationResult;
    HTMLError += verificationResult[1];

    // check if overlap is correct
    verificationResult = verifyNumber(overlap, 'Overlap', false, isValid);
    [isValid] = verificationResult;
    HTMLError += verificationResult[1];

    // check if colorMin is correct
    verificationResult = verifyNumber(colorMin, 'Color Min', true, isValid);
    [isValid] = verificationResult;
    HTMLError += verificationResult[1];

    // check if colorMax is valid
    verificationResult = verifyNumber(colorMax, 'Color Max', true, isValid);
    [isValid] = verificationResult;

    // display the errors on the page
    document.getElementById('error').innerHTML = `${HTMLError}${verificationResult[1]}</ul>`;

    return isValid;
}

/**
 * Function calls api to create a new campaign.
 *
 * @param {HTMLDivElement} form div element that contains all the inputs
 */
function newCampaign(form) {
    // * uncomment below line if you want a spinner
    // document.getElementById('spinner').style.display = 'inline';

    // get all the values
    const camName = form.campaignName.value;
    const camType = form.campaignType.value;
    const camStation = form.campaignStation.value;
    const camStart = form.campaignStart.value;
    const camEnd = form.campaignEnd.value;
    const camFFT = form.campaignFFT.value;
    const camOverlap = form.campaignOverlap.value;
    const camColorMin = form.campaignColorMin.value;
    const camColorMax = form.campaignColorMax.value;

    // if the inputs are valid
    if (verifyValues(
        camName,
        camType,
        camStation,
        camStart,
        camEnd,
        camFFT,
        camOverlap,
        camColorMin,
        camColorMax
    )) {
        const data = {
            newCampaignInfo: {
                name: camName,
                type: camType,
                system: camStation,
                start: camStart,
                end: camEnd,
                fft: camFFT,
                overlap: camOverlap,
                colorMin: camColorMin,
                colorMax: camColorMax,
                comments: form.campaignComments.value,
            },
        };
        newElement(data, currentView, redirectView);
    } else {
        document.getElementById('spinner').style.display = 'none';
    }
}

/**
 * Function calls api to update a system
 *
 * @param {HTMLDivElement} form div element that contains all the inputs
 */
function updateCampaign(form) {
    // * uncomment below line if you want a spinner
    // document.getElementById('spinner').style.display = 'inline';
    // get all the values
    const camName = form.campaignName.value;
    const camType = form.campaignType.value;
    const camStation = form.campaignStation.value;
    const camStart = form.campaignStart.value;
    const camEnd = form.campaignEnd.value;
    const camFFT = form.campaignFFT.value;
    const camOverlap = form.campaignOverlap.value;
    const camColorMin = form.campaignColorMin.value;
    const camColorMax = form.campaignColorMax.value;

    // if the inputs are valid
    if (verifyValues(
        camName,
        camType,
        camStation,
        camStart,
        camEnd,
        camFFT,
        camOverlap,
        camColorMin,
        camColorMax
    )) {
        const data = {
            campaignInfo: {
                id: elementId,
                name: camName,
                type: camType,
                system: camStation,
                start: camStart,
                end: camEnd,
                fft: camFFT,
                overlap: camOverlap,
                colorMin: camColorMin,
                colorMax: camColorMax,
                comments: form.campaignComments.value,
            },
        };
        updateElement(data, currentView, redirectView);
        return;
    } else {
        document.getElementById('spinner').style.display = 'none';
    }
}

// function decides which api to call (update or create)
// eslint-disable-next-line no-unused-vars
function formProcess(form) {
    if (elementId) {
        return updateCampaign(form);
    }
    return newCampaign(form);
}

/**
 * Function call an api to get all the systems name. This function
 * has been written in order to fill the form's station select
 * with the different available systems.
 *
 * @param id {number}   id of the system that has to be selected by default
 */
function getSystems(id = -1) {
    const token = $('#token').attr('name');

    $.ajax({
        type: 'GET',
        url: `
            /index.php?
            option=com_bramscampaign
            &task=getSystems
            &view=${currentView}
            &format=json
            &id=${id}
            &${token}=1
        `,
        success: (response) => {
            let HTMLString = '';

            response.data.forEach((system) => {
                HTMLString += `
                    <option value=${system.id} ${system.selected}>
                        ${system.name}
                    </option>
                `;
            });

            document.getElementById('campaignStation').innerHTML = HTMLString;
        },
        error: (response) => {
            // on fail, show an error message
            document.getElementById('error').innerHTML = apiFailMessg;
            // store the server response in the log variable
            log = response;
        },
    });
}

/**
 * Function call api to get all campaign names. However, if 'id' is a non-zero
 * positive number, the name of the current campaign will not be part of the
 * returned array.
 *
 * @param {number} id id of the current campaign if there is one, else -1
 */
function getCampaignNames(id = -1) {
    const token = $('#token').attr('name');

    $.ajax({
        type: 'GET',
        url: `
            /index.php?
            option=com_bramscampaign
            &task=getAllSimple
            &view=${currentView}
            &format=json
            &id=${id}
            &${token}=1
        `,
        success: (response) => {
            campaignNames = response.data;
        },
        error: (response) => {
            // on fail, show an error message
            document.getElementById('error').innerHTML = apiFailMessg;
            // store the server response in the log variable
            log = response;
        },
    });
}

function getTypes(id = -1) {
    const token = $('#token').attr('name');

    $.ajax({
        type: 'GET',
        url: `
            /index.php?
            option=com_bramscampaign
            &task=getTypes
            &view=${currentView}
            &format=json
            &id=${id}
            &${token}=1
        `,
        success: (response) => {
            let HTMLString;
            // create html option strings for each type
            response.data.forEach(
                (type) => {
                    HTMLString += `
                        <option value='${type.id}' ${type.selected}>
                            ${type.name}
                        </option>
                    `;
                }
            );

            document.getElementById('campaignType').innerHTML = HTMLString;
        },
        error: (response) => {
            // on fail, show an error message
            document.getElementById('error').innerHTML = apiFailMessg;
            // store the server response in the log variable
            log = response;
        },
    });
}

/**
 * Function checks if the form is to create or update a campaign. If its purpose is
 * for updating a campaign (a non-zero number was found in the urls id attribute) it will
 * request the campaign info to back-end and set all the inputs accordingly.
 * If the forms' purpose is creating a system, it will just set default values.
 */
function getCampaignInfo() {
    // get the id from url
    const paramString = window.location.search.split('?')[1];
    const queryString = new URLSearchParams(paramString);
    elementId = Number(queryString.get('id'));

    if (elementId) {
        getCampaignNames(elementId);
        const token = $('#token').attr('name');

        $.ajax({
            type: 'GET',
            url: `
                /index.php?
                option=com_bramscampaign
                &task=getOne
                &view=${currentView}
                &format=json
                &id=${elementId}
                &${token}=1
            `,
            success: (response) => {
                const inputContainer = document.getElementById('inputContainer').children;
                getTypes(response.data.type);
                getSystems(response.data.system);

                // set all the input values
                inputContainer.campaignName.value = response.data.name;
                inputContainer.campaignStart.value = response.data.start.replace(/ /g, 'T');
                inputContainer.campaignEnd.value = response.data.end.replace(/ /g, 'T');
                inputContainer.campaignFFT.value = response.data.fft;
                inputContainer.campaignOverlap.value = response.data.overlap;
                inputContainer.campaignColorMin.value = response.data.colorMin;
                inputContainer.campaignColorMax.value = response.data.colorMax;
                inputContainer.campaignComments.value = response.data.comments;
                document.getElementById('title').innerHTML = `Update Campaign ${response.data.name}`;
            },
            error: (response) => {
                // on fail, show an error message
                document.getElementById('error').innerHTML = apiFailMessg;
                // store the server response in the log variable
                log = response;
            },
        });
    } else {
        // get all campaign types
        getTypes();
        // get all systems
        getSystems();
        // get all campaign names
        getCampaignNames(-1);
    }
}

// set onload function
window.onload = getCampaignInfo;
