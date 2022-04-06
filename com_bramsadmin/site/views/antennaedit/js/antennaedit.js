// * cf. ../../_js/edit.js
// eslint-disable-next-line no-unused-vars
/* global $, elementId, codes, log, apiFailMessg, newElement, updateElement, getCodes */
const currentView = 'antennaEdit';
const redirectView = 'antennas';

/**
 * Function checks if all the required inputs have values in them. It doesn't
 * check the values itself, just if there is something.
 *
 * @param antennaCode   {string}    antennaCode input value
 * @param oldIsValid    {boolean}   flag that determines if values are valid or not
 * @returns             {(*|string)[]|(boolean|string)[]}
 *                                  Returns an array with 2 values :
 *                                      0: new isValid flag
 *                                      1: error message if an input is empty
 */
function verifyRequired(antennaCode, oldIsValid) {
    // if one of the required inputs are empty
    if (!antennaCode) {
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
                Required inputs are Antenna Code.
            </li>`,
        ];
    }

    return [oldIsValid, ''];
}

/**
 * Function verifies if the entered antenna code doesn't exist already.
 * This is done so that each antenna code is unique.
 *
 * @param antennaCode   {string}    antennaCode input value
 * @param oldIsValid    {boolean}   flag that determines if values are valid or not
 * @returns             {(*|string)[]|(boolean|string)[]}
 *                                  Returns an array with 2 values :
 *                                      0: new isValid flag
 *                                      1: error message if the antenna code already exists
 */
function verifyCode(antennaCode, oldIsValid) {
    const pattern = /^[a-z\d\-_]+$/i;
    // if the antenna code already exists
    if (codes.includes(antennaCode)) {
        document.getElementById('code').innerHTML += ''
            + '<i class="fa fa-exclamation-circle red right" aria-hidden="true"></i>';

        // set the valid flag to false and return an error message
        return [
            false,
            `<li>
                <i class="fa fa-exclamation-circle red" aria-hidden="true"></i>
                Entered antenna code is already taken. Please enter a free antenna code.
            </li>`,
        ];
    }

    // test if any forbidden characters are in the code
    if (!pattern.test(antennaCode)) {
        document.getElementById('code').innerHTML += ''
            + '<i class="fa fa-exclamation-circle red right" aria-hidden="true"></i>';

        // set the valid flag to false and return an error message
        return [
            false,
            `<li>
                <i class="fa fa-exclamation-circle red" aria-hidden="true"></i>
                Entered antenna code contains forbidden characters. Be sure to only use dash, 
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
 * @param antennaCode   {string}    antennaCode input value
 * @returns             {boolean}   true if the inputs are valid, else returns false
 */
function verifyValues(antennaCode) {
    // remove all the icons inside the labels
    const icons = document.querySelectorAll('label .fa');
    let verificationResult;
    icons.forEach((icon) => icon.remove());

    let isValid = true;
    let HTMLError = '<h4>Found Problems</h4><ul>';

    // check if all required inputs are filled
    verificationResult = verifyRequired(antennaCode, isValid);
    [isValid] = verificationResult;
    HTMLError += verificationResult[1];

    // check if antenna code is valid
    verificationResult = verifyCode(antennaCode, isValid);
    [isValid] = verificationResult;

    // display the errors on the page
    document.getElementById('error').innerHTML = `${HTMLError}${verificationResult[1]}</ul>`;

    return isValid;
}

/**
 * Function calls an api to create a new antenna. It sends all the information
 * about the new antenna to back-end. For the api to be called, the input values
 * have to be valid.
 *
 * @param formInputs    {HTMLDivElement.children}    Div element containing the inputs
 */
function newAntenna(formInputs) {
    const antCode = formInputs.antennaCode.value;
    const antBrand = formInputs.antennaBrand.value;
    const antModel = formInputs.antennaModel.value;
    const antComments = formInputs.antennaComments.value;

    // verify if the entered values are valid
    if (verifyValues(antCode)) {
        const data = {
            new_antenna: {
                code: antCode,
                brand: antBrand,
                model: antModel,
                comments: antComments,
            },
        };
        newElement(data, currentView, redirectView);
    }
}

/**
 * Function calls an api to update an antenna. It sends all the information
 * about the updated antenna to back-end. For the api to be called, the input values
 * have to be valid.
 *
 * @param formInputs    {HTMLDivElement.children}    Div element containing the inputs
 */
function updateAntenna(formInputs) {
    const antCode = formInputs.antennaCode.value;
    const antBrand = formInputs.antennaBrand.value;
    const antModel = formInputs.antennaModel.value;
    const antComments = formInputs.antennaComments.value;

    // verify if all the entered values are valid
    if (verifyValues(antCode)) {
        data = {
            modified_antenna: {
                id: elementId,
                code: antCode,
                brand: antBrand,
                model: antModel,
                comments: antComments,
            },
        };
        updateElement(data, currentView, redirectView);
    }
}

// function decides which api to call (update or create)
function formProcess(form) {
    if (elementId) {
        return updateAntenna(form);
    }
    return newAntenna(form);
}

/**
 * Function gets all the information about a specific antenna through
 * an api to the sites back-end. The antenna to get information from
 * is specified in the pages url.
 * It then prepares the page to update/create an antenna.
 */
function getAntennaInfo() {
    // get the id from url
    const paramString = window.location.search.split('?')[1];
    const queryString = new URLSearchParams(paramString);
    elementId = Number(queryString.get('id'));
    // get all the antenna codes
    getCodes(currentView);

    // if an antenna has to be updated
    if (elementId) {
        const token = $('#token').attr('name');

        $.ajax({
            type: 'GET',
            url: `
                index.php?
                option=com_bramsadmin
                &task=getOne
                &view=antennaEdit
                &format=json
                &id=${elementId}
                &${token}=1
            `,
            success: (response) => {
                document.getElementById('antennaCode').value = response.data.code;
                document.getElementById('antennaBrand').value = response.data.brand;
                document.getElementById('antennaModel').value = response.data.model;
                document.getElementById('antennaComments').value = response.data.comments;
                document.getElementById('title').innerHTML = `
                    Update Antenna ${response.data.brand} ${response.data.model}`;
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
window.onload = getAntennaInfo;
