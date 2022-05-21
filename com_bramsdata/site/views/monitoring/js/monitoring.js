let charts = {};
let contexts = {};

/**
 * ! Function is currently not working as it should
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
                <div class="row outer">
                    <div id="container${key}" class="col scrollable">
                        
                    </div>
                </div>
            `;

            document.getElementById(`container${key}`).appendChild(noiseCanvas);
            document.getElementById(`container${key}`).appendChild(calibratorCanvas);

            let noiseCtx = document.getElementById(noiseId).getContext('2d');
            let calibratorCtx = document.getElementById(calibratorId).getContext('2d');

            // add new chart for noise
            window[noiseId] = new Chart(noiseCtx, {
                type: 'line',
                data: {
                    labels: data['labels'],
                    datasets: [{
                        label: 'test',
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
                    scales: {
                        xAxes: [{
                            stacked: true,
                        }],
                        yAxes: [{
                            stacked: true
                        }]
                    }
                }
            });

            // add new chart for calibrator
            window[calibratorId] = new Chart(calibratorCtx, {
                type: 'line',
                data: {
                    labels: data['labels'],
                    datasets: [{
                        label: 'test',
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
                    scales: {
                        xAxes: [{
                            stacked: true,
                        }],
                        yAxes: [{
                            stacked: true
                        }]
                    }
                }
            });
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
function getChartData(startDate, endDate, interval, checkboxValues) {
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
    const checkboxValues = getSelectedCheckboxes();
    if (!checkboxValues) {
        document.getElementById('spinner').style.display = 'none';
        return;
    }
    // verify the specified dates
    verifyDates()
    const interval = verifyInterval();

    getChartData(
        document.getElementById('startDate').value,
        document.getElementById('endDate').value,
        interval,
        checkboxValues
    );
}
