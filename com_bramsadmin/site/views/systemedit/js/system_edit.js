// * cf. ../../_js/edit.js
// eslint-disable-next-line no-unused-vars
/* global $, elementId, codes, log, apiFailMessg, newElement, updateElement, getCodes */
const currentView = 'systemEdit';
const redirectView = 'systems';
const systemNames = [];             // array with all taken system names
const defLocationAntenna = {};      // object with current location and antenna combo
let locationAntennas = {};          // object with all antennas grouped by location

/**
 * Function verifies if all the required inputs for the system are available
 * and if they contain valid values.
 *
 * @param {number} antennaValue value of the antenna input
 * @param {HTMLSelectElement} locationSelect location select element
 * @param {number} locationId selected location id
 * @param {string} systemName
 * @param {string} systemStart
 * @returns {boolean} true if everything is okay, false if an error was detected
 */
function verifyValues(
    antennaValue,
    locationSelect,
    locationId,
    systemName,
    systemStart,
) {
    // one of the input values is empty
    if (!antennaValue || !locationId || !systemName || !systemStart) {
        document.getElementById('error').innerHTML = `
            Please fill all required inputs before submitting the form. 
            Required inputs are Name, Location, Antenna and Start.
        `;

        return false;
    }

    // if the system name is already taken
    if (systemNames.includes(systemName)) {
        document.getElementById('error').innerHTML = `
            Entered system name is already taken. Please enter a free system name.
        `;

        return false;
    }

    // if the location_id - antenna number combo is already taken
    if (locationAntennas[locationId].antennas.includes(Number(antennaValue))) {
        document.getElementById('error').innerHTML = `
            Antenna - location combo ${antennaValue} - ${locationId} (
            ${locationSelect.options[locationSelect.selectedIndex].label}) 
            already exists. Either set a different antenna value (recommended is 
            ${locationAntennas[locationId][locationAntennas[locationId].length - 1] + 1}) 
            or change system location.
        `;

        return false;
    }

    return true;
}

/**
 * Function calls api to create a new system.
 *
 * @param {HTMLDivElement} form div element that contains all the inputs
 */
function newSystem(form) {
    // get all the values
    const antennaValue = form.systemAntenna.value;
    const locationSelect = form.systemLocation;
    const locationId = locationSelect.value;
    const systemName = form.systemName.value;
    const systemStart = form.systemStart.value;

    // if the inputs are valid
    if (verifyValues(antennaValue, locationSelect, locationId, systemName, systemStart)) {
        const data = {
            newSystemInfo: {
                name: systemName,
                location: locationId,
                antenna: antennaValue,
                start: systemStart,
                comments: form.systemComments.value,
            },
        };
        newElement(data, currentView, redirectView);
    }
}

/**
 * Function calls api to update a system
 *
 * @param {HTMLDivElement} form div element that contains all the inputs
 */
function updateSystem(form) {
    // get all the values
    const antennaValue = form.systemAntenna.value;
    const locationSelect = form.systemLocation;
    const locationId = locationSelect.value;
    const systemName = form.systemName.value;
    const systemStart = form.systemStart.value;

    // if the inputs are valid
    if (verifyValues(antennaValue, locationSelect, locationId, systemName, systemStart)) {
        const data = {
            systemInfo: {
                id: elementId,
                name: form.systemName.value,
                location: locationId,
                antenna: antennaValue,
                start: form.systemStart.value,
                comments: form.systemComments.value,
            },
        };
        updateElement(data, currentView, redirectView);
    }
}

// function decides which api to call (update or create)
function formProcess(form) {
    if (elementId) {
        return updateSystem(form);
    }
    return newSystem(form);
}

/**
 * Function sets the default antenna value. That is the next antenna value available
 * for a given location. THis function is called each time the user changes the location.
 */
function setAntenna() {
    // get the value of the location select element
    const selectedLocation = document.getElementById('systemLocation').value;

    // if the location selected has not changed and the system already exists
    if (
        Number(selectedLocation) === defLocationAntenna.location
        && defLocationAntenna.antenna !== -1
    ) {
        // set the antenna value to the original value
        document.getElementById('systemAntenna').value = defLocationAntenna.antenna;
    } else {
        // take the next available antenna for the selected location
        locationAntennas[selectedLocation].antennas.sort();
        document.getElementById('systemAntenna').value = (
            Number(locationAntennas[selectedLocation]
                .antennas[locationAntennas[selectedLocation].antennas.length - 1])
            + 1
        );
    }
}

/**
 * Functions call an api to get all the antennas grouped by locations. If
 * a valid response is returned, it fills the location select with all the
 * available locations.
 */
function getLocations() {
    const token = $('#token').attr('name');

    $.ajax({
        type: 'GET',
        url: `
            /index.php?
            option=com_bramsadmin
            &task=getLocationAntennas
            &view=${currentView}
            &format=json
            &locationid=${defLocationAntenna.location}
            &antenna=${defLocationAntenna.antenna}
            &${token}=1
        `,
        success: (response) => {
            let HTMLString = '';
            locationAntennas = response.data;

            Object.keys(locationAntennas).forEach((key) => {
                HTMLString += `
                    <option value=${locationAntennas[key].id} ${locationAntennas[key].selected}>
                        ${locationAntennas[key].name}
                    </option>
                `;
            });

            document.getElementById('systemLocation').innerHTML = HTMLString;

            // set correct antenna value
            setAntenna();
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
 * Function call api to get all system names. However, if 'id' is a non-zero
 * positive number, the name of the current system will not be part of the
 * returned array.
 *
 * @param {number} id id of the current system if there is one, else -1
 */
function getSystemNames(id) {
    const token = $('#token').attr('name');

    $.ajax({
        type: 'GET',
        url: `
            /index.php?
            option=com_bramsadmin
            &task=getAllSimple
            &view=${currentView}
            &format=json
            &id=${id}
            &${token}=1
        `,
        success: (response) => {
            // store the response in an array
            response.data.forEach(
                (object) => systemNames.push(object.name),
            );
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
 * Function checks if the form is to create or update a system. If its purpose is
 * for updating a system (a non-zero number was found in the urls id attribute) it will
 * request the system info to back-end and set all the inputs accordingly.
 * If the forms' purpose is creating a system, it will just set default values.
 */
function getSystemInfo() {
    // get the id from url
    const paramString = window.location.search.split('?')[1];
    const queryString = new URLSearchParams(paramString);
    elementId = Number(queryString.get('id'));

    if (elementId) {
        const token = $('#token').attr('name');

        $.ajax({
            type: 'GET',
            url: `
                /index.php?
                option=com_bramsadmin
                &task=getOne
                &view=${currentView}
                &format=json
                &id=${elementId}
                &${token}=1
            `,
            success: (response) => {
                const inputContainer = document.getElementById('inputContainer').children;

                // set all the input values
                inputContainer.systemName.value = response.data.name;
                defLocationAntenna.location = Number(response.data.location_id);
                inputContainer.systemAntenna.value = response.data.antenna;
                defLocationAntenna.antenna = Number(response.data.antenna);
                inputContainer.systemStart.value = response.data.start.replace(/ /g, 'T');
                inputContainer.systemComments.value = response.data.comments;
                document.getElementById('title').innerHTML = `Update System ${response.data.name}`;

                // get locations and other system names
                getLocations();
                getSystemNames(elementId);
            },
            error: (response) => {
                // on fail, show an error message
                document.getElementById('error').innerHTML = apiFailMessg;
                // store the server response in the log variable
                log = response;
            },
        });
    } else {
        // set default values to inputs if needed
        defLocationAntenna.location = -1;
        defLocationAntenna.antenna = -1;
        const currentDate = new Date();
        document.getElementById('systemStart').value = currentDate.toISOString().substring(0, 16);

        // get all locations and antennas
        getLocations();
        // get all system names
        getSystemNames(-1);
    }
}

// set onload function
window.onload = getSystemInfo;
