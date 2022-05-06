function dateString(d) {
    const pad = (n) => { return n < 10 ? `0${n}` : n; }
    return `${d.getFullYear()}-${
        pad(d.getMonth() + 1)}-${
        pad(d.getDate())}T${
        pad(d.getHours())}:${
        pad(d.getMinutes())}`;
}


function loadSpectrogramsTable(stationId, fParams, startDate, endDate) {
    const token = $('#token').attr('name');
    let year;
    let month;
    let day;
    let hour;
    let minute;
    let imageName;
    let HTMLString = '<div class="row"><div class="col">'
    for (
        const loopDate = new Date(startDate);
        loopDate < endDate;
        loopDate.setTime(loopDate.getTime() + (5 * 60 * 1000))
    ) {
        year = loopDate.getFullYear();
        month = `0${loopDate.getMonth()}`.slice(-2);
        day = `0${loopDate.getDate()}`.slice(-2);
        hour = `0${loopDate.getHours()}`.slice(-2);
        minute = `0${loopDate.getMinutes()}`.slice(-2);
        imageName = `RAD_BEDOUR_${year}${month}${day}_${hour}${minute}_${stationId}`;
        HTMLString += `
            <img
                class="spectrogram"
                src="
                    /index.php?
                    option=com_bramsdata
                    &task=getImage
                    &view=viewer
                    &format=raw
                    &image=${imageName}
                    ${fParams}
                    &${token}=1
                "
                alt="${imageName}"
                onerror="this.onerror=null;this.src='/ProjectDir/img/brams_viewer/no_data.jpg';"
            >
        `;
    }
    HTMLString += '</div></div>';

    document.getElementById('spectrogramContainer').innerHTML += HTMLString;
}


function getSpectrograms(stationId, fMin, fMax, startDate, endDate) {
    const token = $('#token').attr('name');
    let intFMin;
    let intFMax;
    let fParams = '';
    if (fMin !== "" && fMax !== "") {
        intFMin = Number(fMin);
        intFMax = Number(fMax);
        if ((intFMax - intFMax) < 10) {
            intFMax = intFMin + 10;
        }
        fParams = `&fmin=${intFMin}&fmax=${intFMax}`;
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
            &begin=${dateString(startDate)}
            &end=${dateString(endDate)}
            ${fParams}
            &${token}=1
        `,
        success: (data) => {
            const station = data.data[0];
            loadSpectrogramsTable(station, fParams, startDate, endDate);
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
}

function showSpectrograms() {
    const fMin = document.getElementById('fMin').value;
    const fMax = document.getElementById('fMax').value;
    const startDate = new Date(Date.parse(document.getElementById('startDate').value));
    // set minutes to be a multiple of 5
    startDate.setMinutes(startDate.getMinutes() - (startDate.getMinutes() % 5))
    const endDate = new Date(startDate);
    endDate.setMinutes(startDate.getMinutes() + 65);

    const selectedStations = getSelectedCheckboxes();
    document.getElementById('spectrogramContainer').innerHTML = '';
    selectedStations.forEach(
        (station) => {
            getSpectrograms(station, fMin, fMax, startDate, endDate);
        }
    );
}
