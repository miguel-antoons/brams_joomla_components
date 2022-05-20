let charts = [];

function generateChart(data) {
    const chartContainer = document.getElementById('chartContainer');
    console.log(data);

    Object.keys(data['data']).forEach(
        (key) => {
            console.log(key);
            const noiseId = `noise${key}`;
            const calibratorId = `calibrator${key}`;

            chartContainer.innerHTML += `
                <div class="row outer">
                    <div id="${key}" class="col scrollable">
                        <canvas id="${noiseId}" width="400" height="400"></canvas>
                        <canvas id="${calibratorId}" width="400" height="400"></canvas>
                    </div>
                </div>
            `;

            const calibratorChart = document.getElementById(calibratorId).getContext('2d');
            const noiseChart = document.getElementById(noiseId).getContext('2d');

            charts.push(new Chart(noiseChart, {
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
                    fill: false,
                    interaction: {
                        intersect: false
                    },
                    radius: 0,
                },
            }));

            charts.push(new Chart(calibratorChart, {
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
                    fill: false,
                    interaction: {
                        intersect: false
                    },
                    radius: 0,
                },
            }));
        }
    )
    document.getElementById('spinner').style.display = 'none';
}

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
