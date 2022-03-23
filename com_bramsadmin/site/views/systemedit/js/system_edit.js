/* global $ */
/* global currentId */
/* global locationAntennas */
/* global systemNames */
/* global defLocationAntenna */
let log = 'Nothing to show';

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
function verifyValues(antennaValue, locationSelect, locationId, systemName, systemStart) {
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
            Entered system name is already taken. Please enter a free sytem name.
        `;

        return false;
    }

    // if the location_id - antenna number combo is already taken
    if (locationAntennas[locationId].includes(Number(antennaValue))) {
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
        // call the create api with the input values
        $.ajax({
            type: 'POST',
            url: '/index.php?option=com_bramsadmin&view=systemedit&task=newsystem&format=json',
            data: {
                newSystemInfo: {
                    name: form.systemName.value,
                    location: locationId,
                    antenna: antennaValue,
                    start: form.systemStart.value,
                    comments: form.systemComments.value,
                },
            },
            success: () => {
                // on success, return to the system page
                window.location.href = '/index.php?option=com_bramsadmin&view=systems&message=2';
            },
            error: (response) => {
                // on fail, show an error message
                document.getElementById('message').innerHTML = (
                    'API call failed, please read the \'log\' variable in ' +
                    'developer console for more information about the problem.'
                );
                // store the server response in the log variable
                log = response;
            },
        });
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
        // call the update api with the input values
        $.ajax({
            type: 'POST',
            url: '/index.php?option=com_bramsadmin&view=systemedit&task=updatesystem&format=json',
            data: {
                systemInfo: {
                    id: currentId,
                    name: form.systemName.value,
                    location: locationId,
                    antenna: antennaValue,
                    start: form.systemStart.value,
                    comments: form.systemComments.value,
                },
            },
            success: () => {
                // on success, return to the system page
                window.location.href = '/index.php?option=com_bramsadmin&view=systems&message=1';
            },
            error: (response) => {
                // on fail, show an error message
                document.getElementById('message').innerHTML = (
                    'API call failed, please read the \'log\' variable in ' +
                    'developer console for more information about the problem.'
                );
                // store the server response in the log variable
                log = response;
            },
        });
    }
}

// function decides which api to call (update or create)
function formProcess(form) {
    if (currentId) {
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
        locationAntennas[String(selectedLocation)].sort();
        document.getElementById('systemAntenna').value = (
            locationAntennas[selectedLocation][locationAntennas[selectedLocation].length - 1]
            + 1
        );
    }
}
