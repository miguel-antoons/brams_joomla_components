/* global $ */
/* global currentId */
/* global locationAntennas */
/* global systemNames */
function verifyValues(antennaValue, locationSelect, locationId, systemName, systemStart) {
    if (!antennaValue || !locationId || !systemName || !systemStart) {
        document.getElementById('error').innerHTML = `
            Please fill all required inputs before submitting the form. 
            Required inputs are Name, Location, Antenna and Start.
        `;

        return false;
    }

    if (systemNames.includes(systemName)) {
        document.getElementById('error').innerHTML = `
            Entered system name is already taken. Please enter a free sytem name.
        `;

        return false;
    }

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

function newSystem(form) {
    const antennaValue = form.systemAntenna.value;
    const locationSelect = form.systemLocation;
    const locationId = locationSelect.value;
    const systemName = form.systemName.value;
    const systemStart = form.systemStart.value;

    if (verifyValues(antennaValue, locationSelect, locationId, systemName, systemStart)) {
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
                window.location.href = '/index.php?option=com_bramsadmin&view=systems';
            },
            error: (response) => {
                console.log('api call failed', '\n', response);
            },
        });
    }
}

function updateSystem(form) {
    const antennaValue = form.systemAntenna.value;
    const locationSelect = form.systemLocation;
    const locationId = locationSelect.value;
    const systemName = form.systemName.value;
    const systemStart = form.systemStart.value;

    if (verifyValues(antennaValue, locationSelect, locationId, systemName, systemStart)) {
        $.ajax({
            type: 'PUT',
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
                window.location.href = '/index.php?option=com_bramsadmin&view=systems';
            },
            error: (response) => {
                console.log('api call failed', '\n', response);
            },
        });
    }
}

function formProcess(form) {
    if (currentId) {
        return updateSystem(form);
    }

    return newSystem(form);
}

function setAntenna() {
    const selectedLocation = document.getElementById('systemLocation').value;
    locationAntennas[String(selectedLocation)].sort();
    document.getElementById('systemAntenna').value = (
        locationAntennas[selectedLocation][locationAntennas[selectedLocation].length - 1]
        + 1
    );
}
