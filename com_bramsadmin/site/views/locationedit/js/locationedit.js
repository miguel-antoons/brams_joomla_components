/* global $ */
let log = 'Nothing to show';        // contains debug information if needed
let locationId = 0;                 // the id of the location to show (if 0 --> no location)
let locationCodes = {};             // object with all location codes grouped by location

/**
 * Function checks if all the required inputs have values in them. It doesn't
 * check the values itself, just if there is something.
 *
 * @param locationCode          {string}    locationCode input value
 * @param locationName          {string}    locationName input value
 * @param locationStatus        {string}    locationStatus input value
 * @param locationCountry       {string}    locationCountry input value
 * @param locationLatitude      {string}    locationLatitude input value
 * @param locationLongitude     {string}    locationLongitude input value
 * @param locationTransferT     {string}    locationTransferType input value
 * @param locationObserver      {string}    locationObserver input value
 * @param oldIsValid            {boolean}   flag that determines if values are valid or not
 * @returns                     {(*|string)[]|(boolean|string)[]}
 *                                          Returns an array with 2 values :
 *                                              0: new isValid flag
 *                                              1: error message if an input is empty
 */
function verifyRequired(
    locationCode,
    locationName,
    locationStatus,
    locationCountry,
    locationLatitude,
    locationLongitude,
    locationTransferT,
    locationObserver,
    oldIsValid
) {
    // if one of the required inputs are empty
    if (
        !locationCode
        || !locationName
        || !locationStatus
        || !locationCountry
        || !locationLongitude
        || !locationLatitude
        || !locationTransferT
        || !Number(locationObserver)
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
                Required inputs are Code, Name, Status, Country, Latitude, Longitude, Transfer Type, and Observer.
            </li>`
        ];
    }

    return [oldIsValid, ''];
}

/**
 * Function verifies if the entered location code doesn't exist already.
 * This is done so that each location code is unique.
 *
 * @param locationCode  {string}    locationCode input value
 * @param oldIsValid    {boolean}   flag that determines if values are valid or not
 * @returns             {(*|string)[]|(boolean|string)[]}
 *                                  Returns an array with 2 values :
 *                                      0: new isValid flag
 *                                      1: error message if the location code already exists
 */
function verifyCode(locationCode, oldIsValid) {
    // if the location code already exists
    if (locationCodes.includes(locationCode)) {
        document.getElementById('code').innerHTML += '' +
            '<i class="fa fa-exclamation-circle red right" aria-hidden="true"></i>';

        // set the valid flag to false and return an error message
        return [
            false,
            `<li>
                <i class="fa fa-exclamation-circle red" aria-hidden="true"></i>
                Entered location code is already taken. Please enter a free location code or uncheck 
                the checkbox next to the location code input.
            </li>`
        ];
    }

    return [oldIsValid, ''];
}

/**
 * Function verifies if the entered latitude is within the permitted limits
 * (-90 -> 90). Values outside this range will generate an error message.
 *
 * @param locationLatitude  {number}    locationLatitude input value
 * @param oldIsValid        {boolean}   flag that determines if values are valid or not
 * @returns                 {(*|string)[]|(boolean|string)[]}
 *                                      Returns an array with 2 values :
 *                                          0: new isValid flag
 *                                          1: error message if the latitude is outside the permitted range
 */
function verifyLatitude(locationLatitude, oldIsValid) {
    locationLatitude = Number(locationLatitude);

    // if latitude is higher than 90 / lower than 90
    if (locationLatitude > 90 || locationLatitude < -90) {
        document.getElementById('latitude').innerHTML += '' +
            '<i class="fa fa-exclamation-circle red right" aria-hidden="true"></i>';
        // add an error message
        return [
            false,
            `<li>
                <i class="fa fa-exclamation-circle red" aria-hidden="true"></i>
                Entered value for latitude is either higher than 90 or lower than -90. Please enter
                a value in between -90 and 90 for latitude.
            </li>`
        ];
    }

    return [oldIsValid, ''];
}

/**
 * Function verifies if the entered longitude is within the permitted limits
 * (-180 -> 180). Values outside this range will generate an error message.
 *
 * @param locationLongitude {number}    locationLongitude input value
 * @param oldIsValid        {boolean}   flag that determines if values are valid or not
 * @returns                 {(*|string)[]|(boolean|string)[]}
 *                                      Returns an array with 2 values :
 *                                          0: new isValid flag
 *                                          1: error message if the longitude is outside the permitted range
 */
function verifyLongitude(locationLongitude, oldIsValid) {
    locationLongitude = Number(locationLongitude);

    // if longitude is higher than 180 / lower than -180
    if (locationLongitude > 180 || locationLongitude < -180) {
        document.getElementById('longitude').innerHTML += '' +
            '<i class="fa fa-exclamation-circle red right" aria-hidden="true"></i>';
        // add an error message
        return [
            false,
            `<li>
                <i class="fa fa-exclamation-circle red" aria-hidden="true"></i>
                Entered value for longitude is either higher than 180 or lower than -180. Please enter
                a value in between -90 and 90 for latitude.
            </li>`
        ];
    }

    return [oldIsValid, ''];
}

/**
 * Function verifies if the enetred values are valid upon api call. If all the
 * values are valid, the function returns true. If not it returns false.
 *
 * @param locationCode          {string}    locationCode input value
 * @param locationName          {string}    locationName input value
 * @param locationStatus        {string}    locationStatus input value
 * @param locationCountry       {string}    locationCountry input value
 * @param locationLatitude      {string}    locationLatitude input value
 * @param locationLongitude     {string}    locationLongitude input value
 * @param locationTransferT     {string}    locationTransferType input value
 * @param locationObserver      {string}    locationObserver input value
 * @returns                     {boolean}   true if the inputs are valid, else returns false
 */
function verifyValues(
    locationCode,
    locationName,
    locationStatus,
    locationCountry,
    locationLatitude,
    locationLongitude,
    locationTransferT,
    locationObserver
) {
    // remove all the icons inside the labels
    const icons = document.querySelectorAll('label .fa');
    let verificationResult;
    icons.forEach((icon) => icon.remove());

    let isValid = true;
    let HTMLError = '<h4>Found Problems</h4><ul>';

    // check if all required inputs are filled
    verificationResult = verifyRequired(
        locationCode,
        locationName,
        locationStatus,
        locationCountry,
        locationLatitude,
        locationLongitude,
        locationTransferT,
        locationObserver,
        isValid
    );
    isValid = verificationResult[0];
    HTMLError += verificationResult[1];

    // check if location code is valid
    verificationResult = verifyCode(locationCode, isValid);
    isValid = verificationResult[0];
    HTMLError += verificationResult[1];

    // check if latitude is valid
    verificationResult = verifyLatitude(locationLatitude, isValid);
    isValid = verificationResult[0];
    HTMLError += verificationResult[1];

    // check if longitude is valid
    verificationResult = verifyLongitude(locationLongitude, isValid);
    isValid = verificationResult[0];
    // display the errors on the page
    document.getElementById('error').innerHTML = HTMLError + verificationResult[1] + '</ul>';

    return isValid;
}

/**
 * Function calls an api to create a new location. It sends all the information
 * about the new location to back-end. For the api to be called, the input values
 * have to be valid.
 *
 * @param formInputs    {HTMLDivElement.children}    Div element containing the inputs
 */
function newLocation(formInputs) {
    const locCode = formInputs.codeWrapper.children.locationCode.value;
    const locName = formInputs.locationName.value;
    const locStatus = formInputs.locationStatus.value;
    const locCountry = formInputs.locationCountry.value;
    const locLatitude = formInputs.locationLatitude.value;
    const locLongitude = formInputs.locationLongitude.value;
    const locTransferT = formInputs.locationTransferType.value;
    const locObserver = formInputs.locationObserver.value;

    // verify if the entered values are valid
    if (
        verifyValues(
            locCode,
            locName,
            locStatus,
            locCountry,
            locLatitude,
            locLongitude,
            locTransferT,
            locObserver
        )
    ) {
        const token = $('#token').attr('name');

        $.ajax({
            type: 'POST',
            url: `
                /index.php?
                option=com_bramsadmin
                &view=locationEdit
                &format=json
                &task=newLocation
                &${token}=1
            `,
            data: {
                new_location: {
                    code: locCode,
                    name: locName,
                    status: locStatus,
                    country: locCountry,
                    latitude: locLatitude,
                    longitude: locLongitude,
                    transfer_type: locTransferT,
                    observer_id: locObserver,
                    comments: formInputs.locationComments.value,
                    ftp_pass: formInputs.locationFTPPass.value,
                    tv_id: formInputs.locationTVId.value,
                    tv_pass: formInputs.locationTVPass.value,
                },
            },
            success: () => {
                // on success return to the location page
                window.location.href = '/index.php?option=com_bramsadmin&view=locations&message=2'
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
 * Function calls an api to update a location. It sends all the information
 * about the updated location to back-end. For the api to be called, the input values
 * have to be valid.
 *
 * @param formInputs    {HTMLDivElement.children}    Div element containing the inputs
 */
function updateLocation(formInputs) {
    const token = $('#token').attr('name');
    const locCode = formInputs.codeWrapper.children.locationCode.value;
    const locName = formInputs.locationName.value;
    const locStatus = formInputs.locationStatus.value;
    const locCountry = formInputs.locationCountry.value;
    const locLatitude = formInputs.locationLatitude.value;
    const locLongitude = formInputs.locationLongitude.value;
    const locTransferT = formInputs.locationTransferType.value;
    const locObserver = formInputs.locationObserver.value;

    // verify if all the entered values are valid
    if (
        verifyValues(
            locCode,
            locName,
            locStatus,
            locCountry,
            locLatitude,
            locLongitude,
            locTransferT,
            locObserver
        )
    ) {
        $.ajax({
            type: 'POST',
            url: `
                /index.php?
                option=com_bramsadmin
                &view=locationEdit
                &format=json
                &task=updateLocation
                &${token}=1
            `,
            data: {
                modified_location: {
                    id: locationId,
                    code: locCode,
                    name: locName,
                    status: locStatus,
                    country: locCountry,
                    latitude: locLatitude,
                    longitude: locLongitude,
                    transfer_type: locTransferT,
                    observer_id: locObserver,
                    comments: formInputs.locationComments.value,
                    ftp_pass: formInputs.locationFTPPass.value,
                    tv_id: formInputs.locationTVId.value,
                    tv_pass: formInputs.locationTVPass.value,
                },
            },
            success: () => {
                // on success return to the locations page
                window.location.href = '/index.php?option=com_bramsadmin&view=locations&message=1';
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
    if (locationId) {
        return updateLocation(form);
    }
    return newLocation(form);
}

/**
 * Function activates ore deactivates the readonly attribute of the code input
 * based on the value of a checkbox element received as argument.
 *
 * @param checkbox  {HTMLInputElement}  checkbox element
 */
function setCodeStatus(checkbox) {
    // update the readonly attribute of the code input
    document.getElementById('locationCode').readOnly = !checkbox.checked;

    // if the checkbox has been unchecked, update the code input value
    if (!checkbox.checked) {
        setCode();
    }
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

    let locationCode;
    let selectedCountry = document.getElementById('locationCountry').value;
    // take the first word from the location name
    let locationName = document.getElementById('locationName').value.split(' ')[0];

    // take the country code, add the 4 first letters from the location name
    locationCode = selectedCountry + locationName.substring(0, 4).toUpperCase();

    // if the code doesn't exist yet, set the code value
    if (!locationCodes.includes(locationCode)) {
        document.getElementById('locationCode').value = locationCode;
        return;
    }

    // take the country code, add the 3 first letters of the location name and the last letter of the location name
    locationCode = selectedCountry
        + locationName.substring(0, 3).toUpperCase()
        + locationName.substring(locationName.length - 1).toUpperCase();

    // if the code doesn't exist yet, set the code value
    if (!locationCodes.includes(locationCode)) {
        document.getElementById('locationCode').value = locationCode;
        return;
    }

    // while the locationCode already exists
    for (let i = 2; locationCodes.includes(locationCode); i++) {
        // generate a new locationCode from the country code, the 3 first letters
        // from the location name and i
        locationCode = selectedCountry
            + locationName.substring(0, 3).toUpperCase()
            + String(i).toUpperCase();
    }

    document.getElementById('locationCode').value = locationCode;
}

/**
 * Function gets all the taken location codes via an api call to
 * the sites back-end. This function exists in order to verify if
 * the entered location code hasn't been taken yet.
 */
function getLocations() {
    const token = $('#token').attr('name');

    $.ajax({
        type: 'GET',
        url: `
            /index.php?
            option=com_bramsadmin
            &view=locationEdit
            &format=json
            &task=getLocationCodes
            &locationId=${locationId}
            &${token}=1
        `,
        success: (response) => {
            // store the location codes locally
            locationCodes = response.data;
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
 * inside the locationCountry select.
 *
 * @param currentCountry    {string}    country of the current location
 */
function getCountries(currentCountry = '') {
    const token = $('#token').attr('name');

    $.ajax({
        type: 'GET',
        url: `
            /index.php?
            option=com_bramsadmin
            &view=locationEdit
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

            document.getElementById('locationCountry').innerHTML = HTMLString;
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
 * Function gets all the observers through an api to the sites back-end.
 * It then shows all the observers inside the locationObserver select on
 * the page.
 *
 * @param currentObserver   {number}    id of the observer of the current location
 */
function getObservers(currentObserver = 0) {
    const token = $('#token').attr('name');

    $.ajax({
        type: 'GET',
        url: `
            /index.php?
            option=com_bramsadmin
            &view=locationEdit
            &format=json
            &task=getObservers
            &currentObserver=${currentObserver}
            &${token}=1
        `,
        success: (response) => {
            let HTMLString = '';

            // add an option element for each observer
            response.data.forEach((observer) => {
                HTMLString += `
                    <option value=${observer.id} ${observer.selected}>${observer.name}</option>
                `;
            });

            document.getElementById('locationObserver').innerHTML = HTMLString;
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
 * Function gets all the information about a specific location through
 * an api to the sites back-end. The location to get information from
 * is specified in the pages url.
 * It then prepares the page to update/create a location.
 */
function getLocationInfo() {
    // get the id from url
    const paramString = window.location.search.split('?')[1];
    const queryString = new URLSearchParams(paramString);
    locationId = Number(queryString.get('id'));
    // get all the location codes
    getLocations();

    // if a location has to be updated
    if (locationId) {
        const token = $('#token').attr('name');

        $.ajax({
            type: 'GET',
            url: `
                index.php?
                option=com_bramsadmin
                &view=locationEdit
                &format=json
                &task=getLocation
                &id=${locationId}
                &${token}=1
            `,
            success: (response) => {
                // get all the countries
                getCountries(response.data.country_code);
                // get all the observers
                getObservers(response.data.observer_id);
                const statusCheckbox = document.getElementById('codeStatus');

                // set all the input values of the html inputs
                document.getElementById('locationCode').value = response.data.location_code;
                statusCheckbox.checked = true;
                document.getElementById('locationName').value = response.data.name;
                document.getElementById('locationStatus').value = response.data.status;
                document.getElementById('locationLatitude').value = response.data.latitude;
                document.getElementById('locationLongitude').value = response.data.longitude;
                document.getElementById('locationTransferType').value = response.data.transfer_type;
                document.getElementById('locationComments').value = (
                    ( response.data.comments === null) ? '' : response.data.comments
                );
                document.getElementById('locationFTPPass').value = response.data.ftp_password;
                document.getElementById('locationTVId').value = response.data.tv_id;
                document.getElementById('locationTVPass').value = response.data.tv_password;
                document.getElementById('title').innerHTML = `Update location ${response.data.name}`;

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
        // get all the observers
        getObservers();
    }
}

// set onload function
window.onload = getLocationInfo;
