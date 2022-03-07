const minLatitude = 49.191557;
const maxLatitude = 51.802354;
const minLongitude = 2.158350;
const maxLongitude = 6.883813;
const imageXmin = 0;
const imageXmax = 593;
const imageYmin = 0;
const imageYmax = 516;
let activeStations = [];
let inactiveStations = [];

function showStations(stationsToShow) {
    let areaString = '';
    let xPosition;
    let yPosition;
    let mapOptions;

    stationsToShow.forEach(
        (station) => {
            xPosition = Math.round(
                imageXmin
                + ((station[3] - minLongitude)
                / (maxLongitude - minLongitude))
                * (imageXmax - imageYmin),
            );
            yPosition = Math.round(
                imageYmin
                + ((station[4] - maxLatitude)
                / (minLatitude - maxLatitude))
                * (imageYmax - imageYmin),
            );

            if (station[station.length - 1]) {
                mapOptions = {
                    fillColor: '00ff00',
                    strokeColor: '00ff00',
                };
            } else {
                mapOptions = {
                    fillColor: 'ff0000',
                    strokeColor: 'ff0000',
                };
            }

            areaString += `
                <area 
                    class="${station[2]}"
                    shape='circle'
                    onmouseover='showStationInfo('${station[0]}', '${station[1]}', '${station[2]}', ${station[5] / 10})'
                    alt='${station[0]}'
                    title='${station[0]}'
                    coords='${xPosition},${yPosition},4'
                    data-maphilight=${JSON.stringify(mapOptions)}
                />
            `;
        },
    );

    document.getElementById('station_map').innerHTML = areaString;
    $('.map').maphilight();
}

function showStationsEntry() {
    const activeCheckbox = document.getElementById('showActive').checked;
    const inactiveCheckbox = document.getElementById('showInactive').checked;
    const newCheckbox = document.getElementById('showNew').checked;
    const oldCheckbox = document.getElementById('showOld').checked;
    let stationsToShow;

    if (activeCheckbox && inactiveCheckbox) {
        stationsToShow = allStations;
    } else if (activeCheckbox) {
        stationsToShow = activeStations;
    } else if (inactiveCheckbox) {
        stationsToShow = inactiveStations;
    } else {
        stationsToShow = [];
    }

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

function showStationInfo(stationName, stationCountry, stationTransfer, stationRate) {
    document.getElementById('stationName').innerHTML = stationName;
    document.getElementById('stationCountry').innerHTML = stationCountry;
    document.getElementById('stationTransfer').innerHTML = stationTransfer;
    document.getElementById('stationRate').innerHTML = `${stationRate} %`;
}

function onMapLoad() {
    activeStations = allStations.filter((station) => station[station.length - 1] > 0);
    inactiveStations = allStations.filter((station) => station[station.length - 1] === 0);
    document.getElementById('selectedDate').innerHTML = document.getElementById('startDate').value;

    showStationsEntry();
}
