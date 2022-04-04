/* global $ */
// eslint-disable-next-line no-unused-vars
let log = 'Nothing to show';        // contains debug information if needed
let beaconId = 0;                   // the id of the beacon to show (if 0 --> no beacon)
let beaconCodes = [];               // array with all beacon codes

/**
 * Function checks if all the required inputs have values in them. It doesn't
 * check the values itself, just if there is something.
 *
 * @param beaCode          {string}    beaconCode input value
 * @param beaName          {string}    beaconName input value
 * @param beaLatitude      {string}    beaconLatitude input value
 * @param beaLongitude     {string}    beaconLongitude input value
 * @param beaFrequency     {string}    beaconFrequency input value
 * @param beaPower         {string}    beaconPower input value
 * @param beaPolarization  {string}    beaconPolarization input value
 * @param oldIsValid       {boolean}   flag that determines if values are valid or not
 * @returns                {(*|string)[]|(boolean|string)[]}
 *                                     Returns an array with 2 values :
 *                                          0: new isValid flag
 *                                          1: error message if an input is empty
 */
function verifyRequired(
    beaCode,
    beaName,
    beaLatitude,
    beaLongitude,
    beaFrequency,
    beaPower,
    beaPolarization,
    oldIsValid,
) {
    // if one of the required inputs are empty
    if (
        !beaCode
        || !beaName
        || !beaLatitude
        || !beaLongitude
        || !beaPower
        || !beaFrequency
        || !beaPolarization
    ) {
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
                Required inputs are Code, Name, Status, Latitude, Longitude, 
                Frequency, Power and Polarization.
            </li>`,
        ];
    }

    return [oldIsValid, ''];
}

/**
 * Function verifies if the entered beacon code doesn't exist already.
 * This is done so that each beacon code is unique.
 *
 * @param beaCode       {string}    beaconCode input value
 * @param oldIsValid    {boolean}   flag that determines if values are valid or not
 * @returns             {(*|string)[]|(boolean|string)[]}
 *                                  Returns an array with 2 values :
 *                                      0: new isValid flag
 *                                      1: error message if the beacon code already exists
 */
function verifyCode(beaCode, oldIsValid) {
    // if the beacon code already exists
    if (beaconCodes.includes(beaCode)) {
        document.getElementById('code').innerHTML += ''
            + '<i class="fa fa-exclamation-circle red right" aria-hidden="true"></i>';

        // set the valid flag to false and return an error message
        return [
            false,
            `<li>
                <i class="fa fa-exclamation-circle red" aria-hidden="true"></i>
                Entered beacon code is already taken. Please enter a free beacon code or uncheck 
                the checkbox next to the location code input.
            </li>`,
        ];
    }

    return [oldIsValid, ''];
}

/**
 * Function verifies if the entered latitude is within the permitted limits
 * (-90 -> 90). Values outside this range will generate an error message.
 *
 * @param beaLatitude       {string|number}     beaconLatitude input value
 * @param oldIsValid        {boolean}           flag that determines if values are valid or not
 * @returns                 {(*|string)[]|(boolean|string)[]}
 *                                              Returns an array with 2 values :
 *                                                  0: new isValid flag
 *                                                  1: error message if the latitude is outside the permitted range
 */
function verifyLatitude(beaLatitude, oldIsValid) {
    // eslint-disable-next-line no-param-reassign
    beaLatitude = Number(beaLatitude);

    // if latitude is higher than 90 / lower than 90
    if (beaLatitude > 90 || beaLatitude < -90) {
        document.getElementById('latitude').innerHTML += ''
            + '<i class="fa fa-exclamation-circle red right" aria-hidden="true"></i>';
        // add an error message
        return [
            false,
            `<li>
                <i class="fa fa-exclamation-circle red" aria-hidden="true"></i>
                Entered value for latitude is either higher than 90 or lower than -90. Please enter
                a value in between -90 and 90 for latitude.
            </li>`,
        ];
    }

    return [oldIsValid, ''];
}

/**
 * Function verifies if the entered longitude is within the permitted limits
 * (-180 -> 180). Values outside this range will generate an error message.
 *
 * @param beaLongitude      {string|number}     beaconLongitude input value
 * @param oldIsValid        {boolean}           flag that determines if values are valid or not
 * @returns                 {(*|string)[]|(boolean|string)[]}
 *                                              Returns an array with 2 values :
 *                                                  0: new isValid flag
 *                                                  1: error message if the longitude is outside the permitted range
 */
function verifyLongitude(beaLongitude, oldIsValid) {
    // eslint-disable-next-line no-param-reassign
    beaLongitude = Number(beaLongitude);

    // if longitude is higher than 180 / lower than -180
    if (beaLongitude > 180 || beaLongitude < -180) {
        document.getElementById('longitude').innerHTML += ''
            + '<i class="fa fa-exclamation-circle red right" aria-hidden="true"></i>';
        // add an error message
        return [
            false,
            `<li>
                <i class="fa fa-exclamation-circle red" aria-hidden="true"></i>
                Entered value for longitude is either higher than 180 or lower than -180. 
                Please enter a value in between -180 and 180 for latitude.
            </li>`,
        ];
    }

    return [oldIsValid, ''];
}

/**
 * Function verifies if the given inputNumber is higher than 0.
 * If the number is negative, it returns an error message.
 *
 * @param inputNumber   {string|number} number to verify
 * @param elementId     {string}        name of the input label
 * @param oldIsValid    {boolean}       flag that determines if values are valid or not
 * @returns             {(*|string)[]|(boolean|string)[]}
 *                                      Returns an array with 2 values :
 *                                          0: new isValid flag
 *                                          1: error message if the number is negative
 */
function verifyPositiveNumber(inputNumber, elementId, oldIsValid) {
    // eslint-disable-next-line no-param-reassign
    inputNumber = Number(inputNumber);

    // if the entered number is lower than 0
    if (inputNumber < 0) {
        document.getElementById(elementId).innerHTML += ''
            + '<i class="fa fa-exclamation-circle red right" aria-hidden="true"></i>';
        // add an error message
        return [
            false,
            `<li>
                <i class="fa fa-exclamation-circle red" aria-hidden="true"></i>
                Entered value for ${elementId} is cannot be negative. Please enter
                a positive value for ${elementId}.
            </li>`,
        ];
    }

    return [oldIsValid, ''];
}

/**
 * Function verifies if the entered values are valid upon api call. If all the
 * values are valid, the function returns true. If not it returns false.
 *
 * @param beaCode          {string}    beaconCode input value
 * @param beaName          {string}    beaconName input value
 * @param beaLatitude      {string}    beaconStatus input value
 * @param beaLongitude     {string}    beaconLongitude input value
 * @param beaFrequency     {string}    beaconFrequency input value
 * @param beaPower         {string}    beaconPower input value
 * @param beaPolarization  {string}    beaconPolarization input value
 * @returns                {boolean}   true if the inputs are valid, else returns false
 */
function verifyValues(
    beaCode,
    beaName,
    beaLatitude,
    beaLongitude,
    beaFrequency,
    beaPower,
    beaPolarization,
) {
    // remove all the icons inside the labels
    const icons = document.querySelectorAll('label .fa');
    let verificationResult = [];
    icons.forEach((icon) => icon.remove());

    let isValid = true;
    let HTMLError = '<h4>Found Problems</h4><ul>';

    // check if all required inputs are filled
    verificationResult = verifyRequired(
        beaCode,
        beaName,
        beaLatitude,
        beaLongitude,
        beaFrequency,
        beaPower,
        beaPolarization,
        isValid,
    );
    [isValid] = verificationResult;
    HTMLError += verificationResult[1];

    // check if location code is valid
    verificationResult = verifyCode(beaCode, isValid);
    [isValid] = verificationResult;
    HTMLError += verificationResult[1];

    // check if latitude is valid
    verificationResult = verifyLatitude(beaLatitude, isValid);
    [isValid] = verificationResult;
    HTMLError += verificationResult[1];

    // check if longitude is valid
    verificationResult = verifyLongitude(beaLongitude, isValid);
    [isValid] = verificationResult;
    HTMLError += verificationResult[1];

    // check if frequency is higher than 0
    verificationResult = verifyPositiveNumber(beaFrequency, 'frequency', isValid);
    [isValid] = verificationResult;
    HTMLError += verificationResult[1];

    // verify that power is higher than 0
    verificationResult = verifyPositiveNumber(beaPower, 'power', isValid);
    [isValid] = verificationResult;
    // display the errors on the page
    document.getElementById('error').innerHTML = `${HTMLError + verificationResult[1]}</ul>`;

    return isValid;
}

/**
 * Function calls an api to create a new beacon. It sends all the information
 * about the new beacon to back-end. For the api to be called, the input values
 * have to be valid.
 *
 * @param formInputs    {HTMLDivElement.children}    Div element containing the inputs
 */
function newBeacon(formInputs) {
    const beaCode = formInputs.codeWrapper.children.beaconCode.value;
    const beaName = formInputs.beaconName.value;
    const beaLatitude = formInputs.beaconLatitude.value;
    const beaLongitude = formInputs.beaconLongitude.value;
    const beaFrequency = formInputs.beaconFrequency.value;
    const beaPower = formInputs.beaconPower.value;
    const beaPolarization = formInputs.beaconPolarization.value;

    // verify if the entered values are valid
    if (
        verifyValues(
            beaCode,
            beaName,
            beaLatitude,
            beaLongitude,
            beaFrequency,
            beaPower,
            beaPolarization,
        )
    ) {
        const token = $('#token').attr('name');

        $.ajax({
            type: 'POST',
            url: `
                /index.php?
                option=com_bramsadmin
                &task=newBeacon
                &view=beaconEdit
                &format=json
                &${token}=1
            `,
            data: {
                new_beacon: {
                    code: beaCode,
                    name: beaName,
                    latitude: beaLatitude,
                    longitude: beaLongitude,
                    frequency: beaFrequency,
                    power: beaPower,
                    polarization: beaPolarization,
                    // ? uncomment the following line to add a comments field
                    // comments: formInputs.beaconComments.value,
                },
            },
            success: () => {
                // on success return to the location page
                window.location.href = '/index.php?option=com_bramsadmin&view=beacons&message=2';
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
 * Function calls an api to update a beacon. It sends all the information
 * about the updated beacon to back-end. For the api to be called, the input values
 * have to be valid.
 *
 * @param formInputs    {HTMLDivElement.children}    Div element containing the inputs
 */
function updateBeacon(formInputs) {
    const beaCode = formInputs.codeWrapper.children.beaconCode.value;
    const beaName = formInputs.beaconName.value;
    const beaLatitude = formInputs.beaconLatitude.value;
    const beaLongitude = formInputs.beaconLongitude.value;
    const beaFrequency = formInputs.beaconFrequency.value;
    const beaPower = formInputs.beaconPower.value;
    const beaPolarization = formInputs.beaconPolarization.value;

    // verify if all the entered values are valid
    if (
        verifyValues(
            beaCode,
            beaName,
            beaLatitude,
            beaLongitude,
            beaFrequency,
            beaPower,
            beaPolarization,
        )
    ) {
        const token = $('#token').attr('name');

        $.ajax({
            type: 'POST',
            url: `
                /index.php?
                option=com_bramsadmin
                &task=updateBeacon
                &view=beaconEdit
                &format=json
                &${token}=1
            `,
            data: {
                modified_beacon: {
                    id: beaconId,
                    code: beaCode,
                    name: beaName,
                    latitude: beaLatitude,
                    longitude: beaLongitude,
                    frequency: beaFrequency,
                    power: beaPower,
                    polarization: beaPolarization,
                    // ? uncomment the following line to add a comments field
                    // comments: formInputs.beaconComments.value,
                },
            },
            success: () => {
                // on success return to the locations page
                window.location.href = '/index.php?option=com_bramsadmin&view=beacons&message=1';
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
    if (beaconId) {
        return updateBeacon(form);
    }
    return newBeacon(form);
}

/**
 * Function sets the code value base on the selected country and the
 * entered name.
 * * NOTE :
 * *    code should take 2-letter country code and append the first
 * *    4 letters from the name of the location (without spaces). In
 * *    case of conflicts, the last letter of the code is replaced by
 * *    the last letter of the location name. If a conflict remains,
 * *    replace the last letter of the code by a number starting from 2.
 */
function setCode() {
    // if the user wants to choose for himself, return
    if (document.getElementById('codeStatus').checked) {
        return;
    }

    let beaconCode;
    const selectedCountry = document.getElementById('beaconCountry').value;
    // take the first word from the location name
    const beaconName = document.getElementById('beaconName').value.split(' ')[0];

    // take the country code, add the 4 first letters from the location name
    beaconCode = selectedCountry + beaconName.substring(0, 4).toUpperCase();

    // if the code doesn't exist yet, set the code value
    if (!beaconCodes.includes(beaconCode)) {
        document.getElementById('beaconCode').value = beaconCode;
        return;
    }

    // take the country code, add the 3 first letters of the location name and the last letter of the location name
    beaconCode = selectedCountry
        + beaconName.substring(0, 3).toUpperCase()
        + beaconName.substring(beaconName.length - 1).toUpperCase();

    // if the code doesn't exist yet, set the code value
    if (!beaconCodes.includes(beaconCode)) {
        document.getElementById('beaconCode').value = beaconCode;
        return;
    }

    // while the locationCode already exists
    for (let i = 2; beaconCodes.includes(beaconCode); i += 1) {
        // generate a new locationCode from the country code, the 3 first letters
        // from the location name and i
        beaconCode = selectedCountry
            + beaconName.substring(0, 3).toUpperCase()
            + String(i).toUpperCase();
    }

    document.getElementById('beaconCode').value = beaconCode;
}

/**
 * Function activates ore deactivates the readonly attribute of the code input
 * based on the value of a checkbox element received as argument.
 *
 * @param checkbox  {HTMLInputElement}  checkbox element
 */
function setCodeStatus(checkbox) {
    // update the readonly attribute of the code input
    document.getElementById('beaconCode').readOnly = !checkbox.checked;

    // if the checkbox has been unchecked, update the code input value
    if (!checkbox.checked) {
        setCode();
    }
}

/**
 * Function gets all the taken beacon codes via an api call to
 * the sites back-end. This function exists in order to verify if
 * the entered beacon code hasn't been taken yet.
 */
function getBeaconCodes() {
    const token = $('#token').attr('name');

    $.ajax({
        type: 'GET',
        url: `
            /index.php?
            option=com_bramsadmin
            &task=getBeaconCodes
            &view=beaconEdit
            &format=json
            &beaconId=${beaconId}
            &${token}=1
        `,
        success: (response) => {
            // store the beacon codes locally
            beaconCodes = response.data;
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
 * inside the beaconCountry select.
 *
 * @param currentCountry    {string}    country of the current beacon
 */
function getCountries(currentCountry = '') {
    const token = $('#token').attr('name');

    $.ajax({
        type: 'GET',
        url: `
            /index.php?
            option=com_bramsadmin
            &task=getCountries
            &view=beaconEdit
            &format=json
            &currentCountry=${currentCountry}
            &${token}=1
        `,
        success: (response) => {
            let HTMLString = '';

            // add an option element for each location
            response.data.forEach((country) => {
                HTMLString += `
                    <option value=${country.country_code} ${country.selected}>
                        ${country.name}
                    </option>
                `;
            });

            document.getElementById('beaconCountry').innerHTML = HTMLString;
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
 * Function gets all the information about a specific beacon through
 * an api to the sites back-end. The beacon to get information from
 * is specified in the pages url.
 * It then prepares the page to update/create a beacon.
 */
function getBeaconInfo() {
    // get the id from url
    const paramString = window.location.search.split('?')[1];
    const queryString = new URLSearchParams(paramString);
    beaconId = Number(queryString.get('id'));
    // get all the beacon codes
    getBeaconCodes();

    // if a location has to be updated
    if (beaconId) {
        const token = $('#token').attr('name');

        $.ajax({
            type: 'GET',
            url: `
                index.php?
                option=com_bramsadmin
                &task=getBeacon
                &view=beaconEdit
                &format=json
                &id=${beaconId}
                &${token}=1
            `,
            success: (response) => {
                // get all the countries
                getCountries(response.data.code.substring(0, 2));
                const statusCheckbox = document.getElementById('codeStatus');
                statusCheckbox.checked = true;

                // set all the input values of the html inputs
                document.getElementById('beaconCode').value = response.data.code;
                document.getElementById('beaconName').value = response.data.name;
                document.getElementById('beaconLatitude').value = response.data.latitude;
                document.getElementById('beaconLongitude').value = response.data.longitude;
                document.getElementById('beaconFrequency').value = response.data.frequency;
                document.getElementById('beaconPower').value = response.data.power;
                document.getElementById('beaconPolarization').value = response.data.polarization;
                // ? uncomment the following lines to add a comments field
                // document.getElementById('beaconComments').value = (
                //     ( response.data.comments === null) ? '' : response.data.comments
                // );
                document.getElementById('title').innerHTML = `Update ${response.data.name}`;

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
        // get all the countries
        getCountries();
    }
}

// set onload function
window.onload = getBeaconInfo;
