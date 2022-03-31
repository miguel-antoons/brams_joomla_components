/* global $ */
let log = 'Nothing to show';        // contains debug information if needed
let observerId = 0;                 // the id of the observer to show (if 0 --> no observer)
let observerCodes = [];             // array with all the taken observer codes

/**
 * Function checks if all the required inputs have values in them. It doesn't
 * check the values itself, just if there is something.
 *
 * @param observerCode          {string}    observerCode input value
 * @param observerFName         {string}    observerFName input value
 * @param observerLName         {string}    observerLName input value
 * @param observerCountry       {string}    observerCountry input value
 * @param observerEmail         {string}    observerEmail input value
 * @param oldIsValid            {boolean}   flag that determines if values are valid or not
 * @returns                     {(*|string)[]|(boolean|string)[]}
 *                                          Returns an array with 2 values :
 *                                              0: new isValid flag
 *                                              1: error message if an input is empty
 */
function verifyRequired(
    observerCode,
    observerFName,
    observerLName,
    observerCountry,
    observerEmail,
    oldIsValid
) {
    // if one of the required inputs are empty
    if (
        !observerCode
        || !observerFName
        || !observerLName
        || !observerCountry
        || !observerEmail
    ) {
        // add an exclamation circle to the required inputs
        const requiredInputs = document.getElementsByClassName('required');
        Array.from(requiredInputs).forEach(
            (input) => input.innerHTML +=
                '<i class="fa fa-exclamation-circle orange right" aria-hidden="true"></i>'
        );

        // add an error text
        return [
            false,
            `<li>
                <i class="fa fa-exclamation-circle orange" aria-hidden="true"></i>
                Please fill all required inputs before submitting the form. 
                 All inputs are required.
            </li>`
        ];
    }

    return [oldIsValid, ''];
}

/**
 * Function verifies if the entered observer code doesn't exist already.
 * This is done so that each observer code is unique.
 *
 * @param observerCode  {string}    observerCode input value
 * @param oldIsValid    {boolean}   flag that determines if values are valid or not
 * @returns             {(*|string)[]|(boolean|string)[]}
 *                                  Returns an array with 2 values :
 *                                      0: new isValid flag
 *                                      1: error message if the observer code already exists
 */
function verifyCode(observerCode, oldIsValid) {
    // if the location code already exists
    if (observerCodes.includes(observerCode)) {
        document.getElementById('code').innerHTML += '' +
            '<i class="fa fa-exclamation-circle red right" aria-hidden="true"></i>';

        // set the valid flag to false and return an error message
        return [
            false,
            `<li>
                <i class="fa fa-exclamation-circle red" aria-hidden="true"></i>
                Entered observer code is already taken. Please enter a free observer code or uncheck 
                the checkbox next to the observer code input.
            </li>`
        ];
    }

    return [oldIsValid, ''];
}

/**
 * Function verifies if the entered email is valid. It verifies if the length is
 * higher than 5 and that the character '@' and '.' are present.
 *
 * @param observerEmail {string}    observerEmail input value
 * @param oldIsValid    {boolean}   flag that determines if values are valid or not
 * @returns             {(*|string)[]|(boolean|string)[]}
 *                                  Returns an array with 2 values :
 *                                      0: new isValid flag
 *                                      1: error message if the email is not valid
 */
function verifyEmail(observerEmail, oldIsValid) {
    // if the location code already exists
    if (
        !observerEmail.includes('@')
        || !observerEmail.includes('.')
        || observerEmail.length < 5
    ) {
        document.getElementById('email').innerHTML += '' +
            '<i class="fa fa-exclamation-circle red right" aria-hidden="true"></i>';

        // set the valid flag to false and return an error message
        return [
            false,
            `<li>
                <i class="fa fa-exclamation-circle red" aria-hidden="true"></i>
                Entered e-mail does not fulfill the minimum requirements for an e-mail.
                An e-mail must at least contain 5 characters, one '@' character and one '.' character.
            </li>`
        ];
    }

    return [oldIsValid, ''];
}

/**
 * Function verifies if the entered values are valid upon api call. If all the
 * values are valid, the function returns true. If not it returns false.
 *
 * @param observerCode          {string}    observerCode input value
 * @param observerFName         {string}    observerFName input value
 * @param observerLName         {string}    observerLName input value
 * @param observerCountry       {string}    observerCountry input value
 * @param observerEmail         {string}    observerEmail input value
 * @returns                     {boolean}   true if the inputs are valid, else returns false
 */
function verifyValues(
    observerCode,
    observerFName,
    observerLName,
    observerCountry,
    observerEmail,
) {
    // remove all the icons inside the labels
    const icons = document.querySelectorAll('label .fa');
    let verificationResult;
    icons.forEach((icon) => icon.remove());

    let isValid = true;
    let HTMLError = '<h4>Found Problems</h4><ul>';

    // check if all required inputs are filled
    verificationResult = verifyRequired(
        observerCode,
        observerFName,
        observerLName,
        observerCountry,
        observerEmail,
        isValid
    );
    isValid = verificationResult[0];
    HTMLError += verificationResult[1];

    // check if location code is valid
    verificationResult = verifyCode(observerCode, isValid);
    isValid = verificationResult[0];
    HTMLError += verificationResult[1];

    // check if latitude is valid
    verificationResult = verifyEmail(observerEmail, isValid);
    isValid = verificationResult[0];
    // display the errors on the page
    document.getElementById('error').innerHTML = HTMLError + verificationResult[1] + '</ul>';

    return isValid;
}

/**
 * Function calls an api to create a new observer. It sends all the information
 * about the new observer to back-end. For the api to be called, the input values
 * have to be valid.
 *
 * @param formInputs    {HTMLDivElement.children}    Div element containing the inputs
 */
function newObserver(formInputs) {
    const obsCode = formInputs.codeWrapper.children.observerCode.value;
    const obsFName = formInputs.observerFName.value;
    const obsLName = formInputs.observerLName.value;
    const obsCountry = formInputs.observerCountry.value;
    const obsEmail = formInputs.observerEmail.value;

    // verify if the entered values are valid
    if (
        verifyValues(
            obsCode,
            obsFName,
            obsLName,
            obsCountry,
            obsEmail
        )
    ) {
        const token = $('#token').attr('name');

        $.ajax({
            type: 'POST',
            url: `
                /index.php?
                option=com_bramsadmin
                &view=observerEdit
                &format=json
                &task=newObserver
                &${token}=1
            `,
            data: {
                new_observer: {
                    code: obsCode,
                    first_name: obsFName,
                    last_name: obsLName,
                    country: obsCountry,
                    email: obsEmail,
                },
            },
            success: () => {
                // on success return to the location page
                window.location.href = '/index.php?option=com_bramsadmin&view=observers&message=2'
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
}

/**
 * Function calls an api to update an observer. It sends all the information
 * about the updated observer to back-end. For the api to be called, the input values
 * have to be valid.
 *
 * @param formInputs    {HTMLDivElement.children}    Div element containing the inputs
 */
function updateObserver(formInputs) {
    const obsCode = formInputs.codeWrapper.children.observerCode.value;
    const obsFName = formInputs.observerFName.value;
    const obsLName = formInputs.observerLName.value;
    const obsCountry = formInputs.observerCountry.value;
    const obsEmail = formInputs.observerEmail.value;

    // verify if all the entered values are valid
    if (
        verifyValues(
            obsCode,
            obsFName,
            obsLName,
            obsCountry,
            obsEmail
        )
    ) {
        const token = $('#token').attr('name');

        $.ajax({
            type: 'POST',
            url: `
                /index.php?
                option=com_bramsadmin
                &view=observerEdit
                &format=json
                &task=updateObserver
                &${token}=1
            `,
            data: {
                modified_observer: {
                    id: observerId,
                    code: obsCode,
                    first_name: obsFName,
                    last_name: obsLName,
                    country: obsCountry,
                    email: obsEmail,
                },
            },
            success: () => {
                // on success return to the locations page
                window.location.href = '/index.php?option=com_bramsadmin&view=observers&message=1';
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
}

// function decides which api to call (update or create)
function formProcess(form) {
    if (observerId) {
        return updateObserver(form);
    }
    return newObserver(form);
}

/**
 * Function sets the code value based on the first and the last name
 * of the observer.
 * * NOTE :
 * *    code should take 3 first letters of the last name and append the first
 * *    2 letters from the first name of the observer (without spaces). In
 * *    case of conflicts, the 3rd letter of the code is replaced by
 * *    the last letter of the observer's last name. If a conflict remains,
 * *    replace the last letter of the code by a number starting from 2.
 */
function setCode() {
    // if the user wants to choose for himself, return
    if (document.getElementById('codeStatus').checked) {
        return;
    }

    let observerCode;
    const firstName = document.getElementById('observerFName').value;
    // take the first word from the last name
    const lastName = document.getElementById('observerLName').value.split(' ')[0];

    // take the first 3 letters of the last name and add the 2 first letters of the first name to it
    observerCode = lastName.substring(0, 3).toUpperCase() + firstName.substring(0, 2).toUpperCase();

    // if the code doesn't exist yet, set the code value
    if (!observerCodes.includes(observerCode)) {
        document.getElementById('observerCode').value = observerCode;
        return;
    }

    // take the country code, add the 3 first letters of the location name and the last letter of the location name
    observerCode = lastName.substring(0, 2).toUpperCase()
        + lastName.substring(lastName.length - 1).toUpperCase()
        + firstName.substring(0, 2).toUpperCase();

    // if the code doesn't exist yet, set the code value
    if (!observerCodes.includes(observerCode)) {
        document.getElementById('observerCode').value = observerCode;
        return;
    }

    // while the locationCode already exists
    for (let i = 2; observerCodes.includes(observerCode); i++) {
        // generate a new locationCode from the country code, the 3 first letters
        // from the location name and i
        observerCode = lastName.substring(0, 3).toUpperCase()
            + firstName.substring(0, 1).toUpperCase()
            + String(i).toUpperCase();
    }

    document.getElementById('observerCode').value = observerCode;
}

/**
 * Function activates ore deactivates the readonly attribute of the code input
 * based on the value of a checkbox element received as argument.
 *
 * @param checkbox  {HTMLInputElement}  checkbox element
 */
function setCodeStatus(checkbox) {
    // update the readonly attribute of the code input
    document.getElementById('observerCode').readOnly = !checkbox.checked;

    // if the checkbox has been unchecked, update the code input value
    if (!checkbox.checked) {
        setCode();
    }
}

/**
 * Function gets all the taken observer codes via an api call to
 * the sites back-end. This function exists in order to verify if
 * the entered observer code hasn't been taken yet.
 */
function getObsCodes() {
    const token = $('#token').attr('name');

    $.ajax({
        type: 'GET',
        url: `
            /index.php?
            option=com_bramsadmin
            &view=observerEdit
            &format=json
            &task=getObserverCodes
            &observerId=${observerId}
            &${token}=1
        `,
        success: (response) => {
            // store the location codes locally
            observerCodes = response.data;
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

/**
 * Function gets all the countries (country code, country name) through
 * an api to the sites back-end. It then shows all these countries
 * inside the observerCountry select.
 *
 * @param currentCountry    {string}    country of the current observer
 */
function getCountries(currentCountry = '') {
    const token = $('#token').attr('name');

    $.ajax({
        type: 'GET',
        url: `
            /index.php?
            option=com_bramsadmin
            &view=observerEdit
            &format=json
            &task=getCountries
            &currentCountry=${currentCountry}
            &${token}=1
        `,
        success: (response) => {
            let HTMLString = '';

            // add an option element for each location
            response.data.forEach((country) => {
                HTMLString += `
                    <option value=${country.country_code} ${country.selected}>${country.name}</option>
                `;
            })

            document.getElementById('observerCountry').innerHTML = HTMLString;
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

/**
 * Function gets all the information about a specific observer through
 * an api to the sites back-end. The observer to get information from
 * is specified in the pages url.
 * It then prepares the page to update/create an observer.
 */
function getObserver() {
    // get the id from url
    const paramString = window.location.search.split('?')[1];
    const queryString = new URLSearchParams(paramString);
    observerId = Number(queryString.get('id'));
    // get all the observer codes
    getObsCodes();

    // if an observer has to be updated
    if (observerId) {
        const token = $('#token').attr('name');

        $.ajax({
            type: 'GET',
            url: `
                index.php?
                option=com_bramsadmin
                &task=getObserver
                &view=observerEdit
                &format=json
                &id=${observerId}
                &${token}=1
            `,
            success: (response) => {
                console.log(response);
                // get all the countries
                getCountries(response.data.country_code);
                const statusCheckbox = document.getElementById('codeStatus');
                statusCheckbox.checked = true;
                document.getElementById('observerFName').value = response.data.first_name;
                document.getElementById('observerLName').value = response.data.last_name;
                document.getElementById('observerCode').value = response.data.observer_code;
                document.getElementById('observerEmail').value = response.data.email;

                setCodeStatus(statusCheckbox);
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
    } else {
        // get all countries
        getCountries()
    }
}

// set onload function
window.onload = getObserver;
