const minLatitude = 49.191557;
const maxLatitude = 51.802354;
const minLongitude = 2.158350;
const maxLongitude = 6.883813;
const imageXmin = 0;
const imageXmax = 593;
const imageYmin = 0;
const imageYmax = 516;

function onMapLoad(allStations) {
    let areaString = '';
    let xPosition;
    let yPosition;

    allStations.forEach(
        (station) => {
            let statusOptions = '';
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

            if (station[-1]) {
                statusOptions = '{"fillColor": "00FF00", "fillOpacity": 1, "strokeColor": "00FF00"}';
            } else {
                statusOptions = '{"fillColor": "FF0000", "fillOpacity": 1, "strokeColor": "FF0000"}';
            }

            areaString += `
                <area 
                    class='network_area ${station[2]}'
                    shape='circle'
                    alt='${station[0]}'
                    title='${station[0]}'
                    coords='${xPosition},${yPosition},4'
                    data-maphilight="${statusOptions}"
                />
            `;
        },
    );

    document.getElementById('station_map').innerHTML = areaString;
}
