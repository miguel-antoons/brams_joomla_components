const minLatitude = 49.191557;
const maxLatitude = 51.802354;
const minLongitude = 2.158350;
const maxLongitude = 6.883813;
const imageXmin = 0;
const imageXmax = 593;
const imageYmin = 0;
const imageYmax = 516;

function onMapLoad(allStations) {
    const stationMap = document.getElementById('station_map');
    let areaString = '';
    let xPosition;
    let yPosition;

    allStations.forEach(
        (station) => {
            let statusClass = '';
            xPosition = Math.round(
                imageXmin
                + ((station[3] - minLongitude)
                / (maxLongitude - minLongitude))
                * (imageXmax - imageYmin),
            );
            yPosition = Math.round(
                imageYmin
                + ((station[4] - minLatitude)
                / (maxLatitude - minLatitude))
                * (imageYmax - imageYmin),
            );

            if (station[-1]) {
                statusClass = 'active';
            } else {
                statusClass = 'inactive';
            }

            areaString += `
                <area 
                    class='${station[2]} ${statusClass}'
                    shape='circle'
                    alt='${station[0]}'
                    title='${station[0]}'
                    coords='${xPosition},${yPosition},4'
                />
            `;
        },
    );

    stationMap.innerHTML = areaString;
}
