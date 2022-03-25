/* global allStations */
const minLatitude = 49.191557;      // minimum latitude possible on the shown belgian map
const maxLatitude = 51.802354;      // maximum latitude possible on the shown belgian map
const minLongitude = 2.158350;      // minimum longitude possible on the shown belgian map
const maxLongitude = 6.883813;      // maximum longitude possible on the shown belgian map
const imageXmin = 0;                // start x point of the shown map
const imageYmin = 0;                // start y point of the shown map
let allStations = [];               // array contains all stations

/**
 * Calculates the x and y coordinates for a specific station on the
 * network map.
 * @param {number} longitude longitude of the station
 * @param {number} latitude latitude of the station
 * @returns {array} x & y coordinates of the station on the map image
 */
function calculateXY(longitude, latitude) {
    const imageXmax = 593;  // end x point of the shown map
    const imageYmax = 516;  // end y point of the shown map

    // calculate the x position of the station
    const xPosition = Math.round(
        imageXmin
        + ((longitude - minLongitude)
        / (maxLongitude - minLongitude))
        * (imageXmax - imageYmin),
    );
    // calculate the y position of the station
    const yPosition = Math.round(
        imageYmin
        + ((latitude - maxLatitude)
        / (minLatitude - maxLatitude))
        * (imageYmax - imageYmin),
    );

    return [xPosition, yPosition];
}

/**
 * Generates and 'area' element from station information.
 * @param {array} station information of the station
 * @returns {string} area tag with station info
 */
function addStationString(station) {
    const [xPosition, yPosition] = calculateXY(station['longitude'], station['latitude']);
    let mapOptions;    // colors of 1 station on the map

    // if the station has a non-null data availability rate on
    // the given date
    if (Number(station['rate'])) {
        // set the station color to green
        mapOptions = {
            fillColor: '00ff00',
            strokeColor: '00ff00',
        };
    } else {
        // set the station color to red
        mapOptions = {
            fillColor: 'ff0000',
            strokeColor: 'ff0000',
        };
    }

    // return new area element
    return `
        <area 
            class="${station['transfer_type']}"
            shape='circle'
            onmouseover="showStationInfo('${station['name']}', '${station['country_code']}', ${station['rate'] / 10})"
            alt='${station['name']}'
            title='${station['name']}'
            coords='${xPosition},${yPosition},4'
            data-maphilight=${JSON.stringify(mapOptions)}
        />
    `;
}

/**
 * Generates an area element from the beacon information.
 * @param {array} beacon beacon to show
 * @returns {string} area tag of the beacon
 */
function addBeaconString(beacon) {
    const [xPosition, yPosition] = calculateXY(beacon['longitude'], beacon['latitude']);
    // set blue color for beacon
    const mapOptions = {
        fillColor: '0000ff',
        strokeColor: '0000ff',
    };

    // return new area element
    return `
        <area 
            class="${beacon['transfer_type']}"
            shape='poly'
            onmouseover="showStationInfo('${beacon['name']}', '${beacon['country_code']}', '${beacon['rate']}')"
            alt='${beacon['name']}'
            title='${beacon['name']}'
            coords='${xPosition},${yPosition - 5},${xPosition - 4},${yPosition + 4},${xPosition + 4},${yPosition + 4}'
            data-maphilight=${JSON.stringify(mapOptions)}
        />
    `;
}

/**
 * Function calculates the position of each station on the map
 * and generates the area tags for showing all the stations on
 * the map.
 * Finally, it replaces the inner html of the #station_map
 * element.
 * @param {array} stationsToShow Stations the user wants to see
 */
function showStations(stationsToShow) {
    let areaString = '';    // final innerHTML of the map tag

    // generate a 'area' element for each station
    stationsToShow.forEach(
        (station) => {
            areaString += addStationString(station);
        },
    );

    // generate an 'area' element for each beacon
    allStations['beacon'].forEach(
        (beacon) => {
            areaString += addBeaconString(beacon);
        },
    );

    // set the map inner html to newly generated area tags
    document.getElementById('station_map').innerHTML = areaString;
    // update the image
    $('.map').maphilight();
}

/**
 * Entry point to show the stations on the map. The function verifies
 * which checkboxes are checked and passes the correct array with the
 * stations to show to the function that will update the page.
 */
function showStationsEntry() {
    // get booleans for each checkbox
    const activeCheckbox = document.getElementById('showActive').checked;
    const inactiveCheckbox = document.getElementById('showInactive').checked;
    const newCheckbox = document.getElementById('showNew').checked;
    const oldCheckbox = document.getElementById('showOld').checked;
    let stationsToShow;     // array will contain the stations that have to be shown

    // set stations to show according to the active/inactive checkboxes
    if (activeCheckbox && inactiveCheckbox) {
        stationsToShow = allStations['active'].concat(allStations['inactive']);
    } else if (activeCheckbox) {
        stationsToShow = allStations['active'];
    } else if (inactiveCheckbox) {
        stationsToShow = allStations['inactive'];
    } else {
        stationsToShow = [];
    }

    // set stations to show and call the render function according to old/new checkboxes
    if (newCheckbox && oldCheckbox) {
        showStations(stationsToShow);
    } else if (newCheckbox) {
        showStations(stationsToShow.filter((station) => station['transfer_type'] === 'SSH'));
    } else if (oldCheckbox) {
        showStations(stationsToShow.filter((station) => station['transfer_type'] !== 'SSH'));
    } else {
        showStations([]);
    }
}

/**
 * Function shows station information on the page when a specific station
 * is hovered.
 * @param {string} stationName name of the station
 * @param {string} stationCountry country code of the station (i.e. 'BE')
 * @param {string} stationRate station file availability rate for a given date
 */
function showStationInfo(stationName, stationCountry, stationRate) {
    document.getElementById('stationName').innerHTML = stationName;
    document.getElementById('stationCountry').innerHTML = stationCountry;
    if (typeof stationRate === 'number') {
        document.getElementById('stationRate').innerHTML = `${stationRate} %`;
    } else {
        document.getElementById('stationRate').innerHTML = stationRate;
    }
}

/**
 * Function call an api to get all the stations' information from backend. If no problems
 * occur, it will receive an object with 3 arrays: one with active stations, one with beacons
 * and one with inactive stations.
 */
function getStations() {
    // get the token
    const token = $('#token').attr('name');
    const date = document.getElementById('startDate').value;

    $.ajax({
        type: 'GET',
        url: `/index.php?option=com_bramsnetwork&view=map&task=getstations&format=json&${token}=1&date=${date}`,
        success: (response) => {
            allStations = response.data;
            document.getElementById('selectedDate').innerHTML = date;
            showStationsEntry();
        },
        error: (response) => {
            // on fail, show an error message
            document.getElementById('error').innerHTML = (
                'API call failed, please read the \'log\' variable in ' +
                'developer console for more information about the problem.'
            );
            // store the server response in the log variable
            log = response;
        },
    });
}

window.onload = getStations;
