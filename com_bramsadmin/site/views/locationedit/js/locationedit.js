/* global $ */
let log = 'Nothing to show';        // contains debug information if needed
let locationId = 0;                 // the id of the location to show (if 0 --> no location)
let locationCodes = {};             // object with all location codes grouped by location

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
            &view=locationedit
            &format=json
            &task=getlocationcodes
            &locationid=${locationId}
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
                // get all the location codes
                getLocations();
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
        // get all the location codes
        getLocations();
        // get all the countries
        getCountries();
        // get all the observers
        getObservers();
    }
}

// set onload function
window.onload = getLocationInfo;
