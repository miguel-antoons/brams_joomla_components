function dateString(d) {
    const pad = (n) => { return n < 10 ? `0${n}` : n; }
    return `${d.getFullYear()}-${
        pad(d.getMonth() + 1)}-${
        pad(d.getDate())}T${
        pad(d.getHours())}:${
        pad(d.getMinutes())}`;
}


function getSpectrograms(stationId, fMin, fMax, startDate, endDate) {
    const token = $('#token').attr('name');
    if (fMin !== '' && fMax !== '') {
        const intFMin = Number(fMin);
        let intFMax = Number(fMax);
        if ((intFMax - intFMax) < 10) {
            intFMax = intFMin + 10;
        }
    }

    $.ajax({
        type: 'GET',
        url: `
            /index.php?
            option=com_bramsdata
            &task=makeImages
            &view=viewer
            &format=json
            &station=${stationId}
            &start=${dateString(startDate)}
            &end=${dateString(endDate)}
            &fmin=${intFMin}
            &fmax=${intFMax}
            &${token}=1
        `,
        success: (data) => {
            console.log(data);
        },
        error: (response) => {
            // on fail, show an error message
            document.getElementById('error').innerHTML = (
                'API call failed, please read the \'log\' variable in '
                + 'developer console for more information about the problem.'
            );
            // store the server response in the log variable
            log = response;
        },
    });
    return ''
}

function showSpectrograms() {
    let HTMLString = '';
    const fMin = document.getElementById('fMin').value;
    const fMax = document.getElementById('fMax').value;
    const startDate = Date.parse(document.getElementById('startDate').value);
    // set minutes to be a multiple of 5
    startDate.setMinutes(startDate.getMinutes() - (startDate.getMinutes() % 5))
    const endDate = new Date(startDate);
    endDate.setMinutes(startDate.getMinutes() + 65);

    const selectedStations = getSelectedCheckboxes();

    selectedStations.forEach(
        (station) => {
            HTMLString += '<div class="row"><div class="col">'
            for (
                const loopDate = new Date(startDate);
                loopDate < endDate;
                loopDate.setTime(loopDate.getTime() + (5 * 60 * 1000))
            ) {
                HTMLString += getSpectrograms();
            }
            console.log(station);
        }
    );

    console.log(startDate);
}
