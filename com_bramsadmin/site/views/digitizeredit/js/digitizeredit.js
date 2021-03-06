/* eslint-disable no-global-assign */
// * cf. ../../_js/edit.js
// eslint-disable-next-line no-unused-vars
/* global $, elementId, codes, log, apiFailMessg, newElement, updateElement, getCodes */
const currentView = 'digitizerEdit';
const redirectView = 'digitizers';

/**
 * Function checks if all the required inputs have values in them. It doesn't
 * check the values itself, just if there is something.
 *
 * @param digitizerCode {string}    digitizerCode input value
 * @param oldIsValid    {boolean}   flag that determines if values are valid or not
 * @returns             {(*|string)[]|(boolean|string)[]}
 *                                  Returns an array with 2 values :
 *                                      0: new isValid flag
 *                                      1: error message if an input is empty
 */
function verifyRequired(digitizerCode, oldIsValid) {
    // if one of the required inputs are empty
    if (!digitizerCode) {
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
                Required inputs are Digitizer Code.
            </li>`,
        ];
    }

    return [oldIsValid, ''];
}

/**
 * Function verifies if the entered digitizer code doesn't exist already.
 * This is done so that each digitizer code is unique.
 *
 * @param digitizerCode {string}    digitizerCode input value
 * @param oldIsValid    {boolean}   flag that determines if values are valid or not
 * @returns             {(*|string)[]|(boolean|string)[]}
 *                                  Returns an array with 2 values :
 *                                      0: new isValid flag
 *                                      1: error message if the digitizer code already exists
 */
function verifyCode(digitizerCode, oldIsValid) {
    const pattern = /^[a-z\d\-_]+$/i;
    // if the location code already exists
    if (codes.includes(digitizerCode)) {
        document.getElementById('code').innerHTML += ''
            + '<i class="fa fa-exclamation-circle red right" aria-hidden="true"></i>';

        // set the valid flag to false and return an error message
        return [
            false,
            `<li>
                <i class="fa fa-exclamation-circle red" aria-hidden="true"></i>
                Entered digitizer code is already taken. Please enter a free digitizer code.
            </li>`,
        ];
    }

    // test if any forbidden characters are in the code
    if (!pattern.test(digitizerCode)) {
        document.getElementById('code').innerHTML += ''
            + '<i class="fa fa-exclamation-circle red right" aria-hidden="true"></i>';

        // set the valid flag to false and return an error message
        return [
            false,
            `<li>
                <i class="fa fa-exclamation-circle red" aria-hidden="true"></i>
                Entered digitizer code contains forbidden characters. Be sure to only use dash, 
                underscore and alphanumeric characters.
            </li>`,
        ];
    }

    return [oldIsValid, ''];
}

/**
 * Function verifies if the entered values are valid upon api call. If all the
 * values are valid, the function returns true. If not it returns false.
 *
 * @param digitizerCode {string}    digitizerCode input value
 * @returns             {boolean}   true if the inputs are valid, else returns false
 */
function verifyValues(digitizerCode) {
    // remove all the icons inside the labels
    const icons = document.querySelectorAll('label .fa');
    let verificationResult;
    icons.forEach((icon) => icon.remove());

    let isValid = true;
    let HTMLError = '<h4>Found Problems</h4><ul>';

    // check if all required inputs are filled
    verificationResult = verifyRequired(digitizerCode, isValid);
    [isValid] = verificationResult;
    HTMLError += verificationResult[1];

    // check if digitizer code is valid
    verificationResult = verifyCode(digitizerCode, isValid);
    [isValid] = verificationResult;

    // display the errors on the page
    document.getElementById('error').innerHTML = `${HTMLError}${verificationResult[1]}</ul>`;

    return isValid;
}

/**
 * Function calls an api to create a new digitizer. It sends all the information
 * about the new digitizer to back-end. For the api to be called, the input values
 * have to be valid.
 *
 * @param formInputs    {HTMLDivElement.children}    Div element containing the inputs
 */
function newDigitizer(formInputs) {
    const digCode = formInputs.digitizerCode.value;
    const digBrand = formInputs.digitizerBrand.value;
    const digModel = formInputs.digitizerModel.value;
    const digComments = formInputs.digitizerComments.value;

    // verify if the entered values are valid
    if (verifyValues(digCode)) {
        const data = {
            new_digitizer: {
                code: digCode,
                brand: digBrand,
                model: digModel,
                comments: digComments,
            },
        };
        newElement(data, currentView, redirectView);
    }
}

/**
 * Function calls an api to update a digitizer. It sends all the information
 * about the updated digitizer to back-end. For the api to be called, the input values
 * have to be valid.
 *
 * @param formInputs    {HTMLDivElement.children}    Div element containing the inputs
 */
function updateDigitizer(formInputs) {
    const digCode = formInputs.digitizerCode.value;
    const digBrand = formInputs.digitizerBrand.value;
    const digModel = formInputs.digitizerModel.value;
    const digComments = formInputs.digitizerComments.value;

    // verify if all the entered values are valid
    if (verifyValues(digCode)) {
        const data = {
            modified_digitizer: {
                id: elementId,
                code: digCode,
                brand: digBrand,
                model: digModel,
                comments: digComments,
            },
        };
        updateElement(data, currentView, redirectView);
    }
}

// function decides which api to call (update or create)
// eslint-disable-next-line no-unused-vars
function formProcess(form) {
    if (elementId) {
        return updateDigitizer(form);
    }
    return newDigitizer(form);
}

/**
 * Function gets all the information about a specific digitizer through
 * an api to the sites back-end. The digitizer to get information from
 * is specified in the pages url.
 * It then prepares the page to update/create a digitizer.
 */
function getDigitizerInfo() {
    // get the id from url
    const paramString = window.location.search.split('?')[1];
    const queryString = new URLSearchParams(paramString);
    elementId = Number(queryString.get('id'));
    // get all the location codes
    getCodes(currentView);

    // if a digitizer has to be updated
    if (elementId) {
        const token = $('#token').attr('name');

        $.ajax({
            type: 'GET',
            url: `
                index.php?
                option=com_bramsadmin
                &task=getOne
                &view=digitizerEdit
                &format=json
                &id=${elementId}
                &${token}=1
            `,
            success: (response) => {
                document.getElementById('digitizerCode').value = response.data.code;
                document.getElementById('digitizerBrand').value = response.data.brand;
                document.getElementById('digitizerModel').value = response.data.model;
                document.getElementById('digitizerComments').value = response.data.comments;
                document.getElementById('title').innerHTML = `
                    Update Digitizer ${response.data.code}`;
            },
            error: (response) => {
                // on fail, show an error message
                document.getElementById('error').innerHTML = apiFailMessg;
                // store the server response in the log variable
                log = response;
            },
        });
    }
}

// set onload function
window.onload = getDigitizerInfo;
