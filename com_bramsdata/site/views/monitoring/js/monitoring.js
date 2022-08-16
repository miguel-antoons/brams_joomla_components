let noiseCharts = {};
let calibratorCharts = {};
let stationNames = {};

// ! comments and image name!!
function generateDownloadableImage(key) {
    const calibratorImage = calibratorCharts[key].toBase64Image('image/jpeg', 1);
    const noiseImage = noiseCharts[key].toBase64Image('image/jpeg', 1);

    const tmpLink = document.createElement('a');
    tmpLink.download = (
        stationNames[key]
        + '_' + document.getElementById('startDate').value
        + '_' + document.getElementById('endDate').value
        + '_calibrator.jpg'
    );
    tmpLink.href = calibratorImage;

    document.body.appendChild(tmpLink);
    tmpLink.click();

    tmpLink.download = (
        stationNames[key]
        + '_' + document.getElementById('startDate').value
        + '_' + document.getElementById('endDate').value
        + '_noise.jpg'
    );
    tmpLink.href = noiseImage;
    tmpLink.click();

    document.body.removeChild(tmpLink);
}

/**
 * Function resets the zoom of the charts with the given key.
 * 
 * @param {Number} key  key of the carts in the 'noiseCharts' and
 *                      'calibratorCharts' objects.
 */
function resetZoom(key) {
    calibratorCharts[key].resetZoom();
    noiseCharts[key].resetZoom();
}

/**
 * Function gets all the checked station checkboxes and returns it. If there
 * are none checked, the function will return false.
 *
 * @returns {boolean|*[]}
 */
 function getCheckboxes() {
    const checkedCheckboxes = document.querySelectorAll('input[name=station]:checked');
    // check if there are any checked checkboxes
    if (!checkedCheckboxes.length) {
        $('#myModal').modal();
        return false;
    }
    // store their values only
    const checkboxValues = [];
    checkedCheckboxes.forEach((checkbox) => {
        checkboxValues.push(checkbox.value);
        stationNames[checkbox.value] = checkbox.labels[0].innerText;
    });
    return checkboxValues;
}

/**
 * Function generates 2 charts per station (one for the noise and one for the calibrator psd).
 * It adds all the carts to the page.
 * 
 * @param {Object} data Labels and PSD data for each station, stored in an Object
 */
function generateChart(data) {
    const chartContainer = document.getElementById('chartContainer');
    console.log(data);

    // for each station
    Object.keys(data['data']).forEach(
        (key) => {
            // create a canvas
            var noiseCanvas = document.createElement('canvas');
            var calibratorCanvas = document.createElement('canvas');
            var noiseId = `noise${key}`;
            var calibratorId = `calibrator${key}`;

            noiseCanvas.id = noiseId;
            calibratorCanvas.id = calibratorId;

            // create a new scrollable div
            chartContainer.innerHTML += `
                <h3>${stationNames[key]}</h3>
                <button 
                    type='button'
                    class='left btn btn-dark'
                    onClick='resetZoom(${key})'
                >
                    <i class="fa fa-undo-alt"></i>
                    Reset Zoom
                </button>
                <button
                    type='button'
                    class='right btn btn-dark'
                    onClick='generateDownloadableImage(${key})'
                >
                    <i class="fa fa-cloud-download-alt"></i>
                    Images
                </button>
                <div class="row outer">
                    <div id="container${key}" class="col scrollable">

                    </div>
                </div>
            `;

            document.getElementById(`container${key}`).appendChild(noiseCanvas);
            document.getElementById(`container${key}`).appendChild(calibratorCanvas); 
        }
    );

    // same loop, is needed --> otherwise a chartJs will 
    // only show the 2 last generated charts
    Object.keys(data['data']).forEach(
        (key) => {
            // document.getElementById('chartTable').appendChild(noiseCanvas);
            // document.getElementById('chartTable').appendChild(calibratorCanvas);
            // document.getElementById('chartTable').innerHTML += '<br>';
            const noiseId = `noise${key}`;
            const calibratorId = `calibrator${key}`;

            let noiseCtx = document.getElementById(noiseId).getContext('2d');
            let calibratorCtx = document.getElementById(calibratorId).getContext('2d');

            // add new chart for noise
            noiseCharts[key] = new Chart(noiseCtx, {
                type: 'line',
                data: {
                    labels: data['labels'],
                    datasets: [{
                        label: 'Noise PSD',
                        data: data['data'][key]['noise'],
                        borderColor: 'rgb(75, 192, 192)',
                        spanGaps: true
                    }]
                },
                options: {
                    title: {
                        display: true,
                        text: key
                    },
                    tooltips: {
                        mode: 'index',
                        intersect: false
                    },
                    responsive: true,
                    plugins: {
                        zoom: {
                            pan: {
                                enabled: true,
                                treshold: 0,
                            },
                            zoom: {
                                wheel: {
                                    enabled: true,
                                    modifierKey: 'ctrl',
                                },
                                pinch: {
                                    enabled: true,
                                },
                                mode: 'xy',
                            },
                        },
                    },
                }
            });
            window[noiseId] = noiseCharts[key];

            // add new chart for calibrator
            calibratorCharts[key] = new Chart(calibratorCtx, {
                type: 'line',
                data: {
                    labels: data['labels'],
                    datasets: [{
                        label: 'Calibrator PSD',
                        data: data['data'][key]['calibrator'],
                        borderColor: 'rgb(192, 192, 75)',
                        spanGaps: true
                    }]
                },
                options: {
                    title: {
                        display: true,
                        text: key
                    },
                    tooltips: {
                        mode: 'index',
                        intersect: false
                    },
                    responsive: true,
                    plugins: {
                        zoom: {
                            pan: {
                                enabled: true,
                                treshold: 0,
                            },
                            zoom: {
                                wheel: {
                                    enabled: true,
                                    modifierKey: 'ctrl',
                                },
                                pinch: {
                                    enabled: true,
                                },
                                mode: 'xy',
                            },
                        },
                    },
                }
            });
            window[calibratorId] = calibratorCharts[key];
        }
    )
    document.getElementById('spinner').style.display = 'none';
}

/**
 * Function calls an API to get all the psd values.
 * Once it gets the data, it calls a function to create the charts.
 * 
 * @param {String} startDate string start date
 * @param {String} endDate string end date
 * @param {Number} interval interval value
 * @param {Array} checkboxValues array with all the checkbox values (system ids)
 */
function getChartData(startDate, endDate, /* interval, */ checkboxValues) {
    const token = $('#token').attr('name');

    $.ajax({
        type: 'GET',
        url: `
            /index.php?
            option=com_bramsdata
            &view=monitoring
            &task=getPSD
            &format=json
            &start=${startDate}
            &end=${endDate}
            &ids=${checkboxValues}
            &${token}=1
        `,
        success: (response) => {
            document.getElementById('chartContainer').innerHTML = '';
            generateChart(response.data);
        },
        error: (response) => {
            // on fail, show an error message
            document.getElementById('error').innerHTML = (
                'API call failed, please read the \'log\' variable in '
                + 'developer console for more information about the problem.'
            );
            document.getElementById('spinner').style.display = 'none';
            // store the server response in the log variable
            log = response;
        },
    });
}

function verifyInterval() {
    const intervalField = document.getElementById('interval');
    const intervalValue = Number(intervalField.value);

    if (intervalValue > 40320) intervalField.value = '40320';
    else if (intervalValue < 5) intervalField.value = '5';

    return intervalField.value;
}

/**
 * Function is the entrypoint to generate the psd charts.
 * It verifies all the input value and calls the api to get the psd.
 * 
 * @returns void
 */
function chartStart() {
    document.getElementById('spinner').style.display = 'inline';
    // get all the selected stations
    const checkboxValues = getCheckboxes();
    if (!checkboxValues) {
        document.getElementById('spinner').style.display = 'none';
        return;
    }
    // verify the specified dates
    verifyDates()
    // const interval = verifyInterval();

    getChartData(
        document.getElementById('startDate').value,
        document.getElementById('endDate').value,
        // interval,
        checkboxValues
    );
}
