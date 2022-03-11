const minLatitude = 49.191557;      // minimum latitude possible on the shown belgian map
const maxLatitude = 51.802354;      // maximum latitude possible on the shown belgian map
const minLongitude = 2.158350;      // minimum longitude possible on the shown belgian map
const maxLongitude = 6.883813;      // maximum longitude possible on the shown belgian map
const imageXmin = 0;                // start x point of the shown map
const imageYmin = 0;                // start y point of the shown map
let activeStations = [];            // array contains all active stations
let inactiveStations = [];          // array contains all inactive stations
let beacons = [];                   // array contains all beacons

/**
 * Calculates the x and y coordinates for a specific station on the
 * network map.
 * @param {number} longitude 
 * @param {number} latitude 
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
    const [xPosition, yPosition] = calculateXY(station[3], station[4]);
    let mapOptions = '';    // colors of 1 station on the map

    // if the station has a non null data availability rate on
    // the given date
    if (station[station.length - 2]) {
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
            class="${station[2]}"
            shape='circle'
            onmouseover="showStationInfo('${station[0]}', '${station[1]}', '${station[2]}', ${station[5] / 10})"
            alt='${station[0]}'
            title='${station[0]}'
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
    const [xPosition, yPosition] = calculateXY(beacon[3], beacon[4]);
    // set blue color for beacon
    const mapOptions = {
        fillColor: '0000ff',
        strokeColor: '0000ff',
    };

    // return new area element
    return `
        <area 
            class="${beacon[2]}"
            shape='poly'
            onmouseover="showStationInfo('${beacon[0]}', '${beacon[1]}', '${beacon[2]}', '${beacon[5]}')"
            alt='${beacon[0]}'
            title='${beacon[0]}'
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
    beacons.forEach(
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
 * Entry point to show the stations on the map. The functions verifies
 * wich checkboxes are checked and passes the correct array with the
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
        stationsToShow = allStations;
    } else if (activeCheckbox) {
        stationsToShow = activeStations;
    } else if (inactiveCheckbox) {
        stationsToShow = inactiveStations;
    } else {
        stationsToShow = [];
    }

    // set stations to show and call the render function according to old/new checkboxes
    if (newCheckbox && oldCheckbox) {
        showStations(stationsToShow);
    } else if (newCheckbox) {
        showStations(stationsToShow.filter((station) => station[2] === 'SSH'));
    } else if (oldCheckbox) {
        showStations(stationsToShow.filter((station) => station[2] !== 'SSH'));
    } else {
        showStations([]);
    }
}

/**
 * Function shows station information on the page when a specific station
 * is hovered.
 * @param {string} stationName name of the station
 * @param {string} stationCountry country code of the station (i.e. 'BE')
 * @param {string} stationTransfer station tranfer type (i.e. 'SSH')
 * @param {int} stationRate station file availability rate for a given date
 */
function showStationInfo(stationName, stationCountry, stationTransfer, stationRate) {
    document.getElementById('stationName').innerHTML = stationName;
    document.getElementById('stationCountry').innerHTML = stationCountry;
    document.getElementById('stationTransfer').innerHTML = stationTransfer;
    if (typeof stationRate === 'number') {
        document.getElementById('stationRate').innerHTML = `${stationRate} %`;
    } else {
        document.getElementById('stationRate').innerHTML = stationRate;
    }
}

/**
 * Function is called when the page is loading. It spearates the active
 * and inactive stations in separate arrays and calls shows the stations
 * on screen for the first time.
 */
function onMapLoad() {
    // separate active and inactive stations in 2 arrays
    beacons = allStations.filter((station) => station[station.length - 1]);
    allStations = allStations.filter((station) => !station[station.length - 1]);
    activeStations = allStations.filter((station) => station[station.length - 2] > 0);
    inactiveStations = allStations.filter((station) => station[station.length - 2] === 0);

    // show current selected date on screen
    document.getElementById('selectedDate').innerHTML = document.getElementById('startDate').value;

    // show the station on the shown map
    showStationsEntry();
}
