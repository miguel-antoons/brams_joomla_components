let stations = [];
let gallery;

/**
 * Function converts a datetime object to a string date and time.
 * 
 * @param {DateTime} d datetime to convert to string
 * @returns the string representation of the datetime argument
 */
function dateString(d) {
    const pad = (n) => { return n < 10 ? `0${n}` : n; }
    return `${d.getFullYear()}-${
        pad(d.getMonth() + 1)}-${
        pad(d.getDate())}T${
        pad(d.getHours())}:${
        pad(d.getMinutes())}`;
}

/**
 * Function initializes a new viewer.
 * The viewer then show the images in fullscreen mode.
 * 
 * @param {HTMLElement} parentElement Container containing the images to show
 * @param {Number} index index of the clicked image within the container
 */
function createGallery(parentElement, index) {
    // if there is an existing viewer
    if (gallery !== undefined) {
        // destroy it
        gallery.destroy();
    }

    // create the new viewer
    gallery = new Viewer(parentElement, {
        toolbar: {
            zoomIn: {
                show: true,
                size: 'large',
            },
            zoomOut: {
                show: true,
                size: 'large',
            },
            oneToOne: {
                show: true,
                size: 'large',
            },
            prev: {
                show: true,
                size: 'large',
            },
            next: {
                show: true,
                size: 'large',
            },
            // add the png download button
            png: {
                show: true,
                size: 'large',
                click: () => {
                    const a = document.createElement('a');

                    a.href = gallery.image.src.replace('getImage', 'saveImage');
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                },
            },
            // add the wav download button
            wav: {
                show: true,
                size: 'large',
                click: () => {
                    const a = document.createElement('a');

                    a.href = `
                        ${gallery.image.src.replace('getImage', 'saveWav')}
                        &sysId=${parentElement.id}
                    `;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                },
            },
            // add the button to go to the previous row of images
            up: {
                show: true,
                size: 'large',
                click: () => {
                    const newIndex = stations.findIndex((element) => element === parentElement.id) - 1;

                    if (newIndex >= 0 && newIndex < stations.length) {
                        gallery.hide();
                        createGallery(document.getElementById(stations[newIndex]), index);
                    }
                },
            },
            // add the button to go to the next row of images
            down: {
                show: true,
                size: 'large',
                click: () => {
                    const newIndex = stations.findIndex((element) => element === parentElement.id) + 1;

                    if (newIndex >= 0 && newIndex < stations.length) {
                        gallery.hide();
                        createGallery(document.getElementById(stations[newIndex]), index);
                    }
                },
            },
        },
    });

    // show the gallery with the clicked image
    gallery.view(index);
}

/**
 * Function adds the spectrogram images to the page of a specific station within a certain interval.
 * 
 * @param {String} stationId string station id
 * @param {String} fParams frequency arguments (fmin & fmax)
 * @param {Datetime} startDate start date
 * @param {DateTime} endDate end date
 * @param {string} imageOnload image onload attribute
 */
function loadSpectrogramsRow(stationId, fParams, startDate, endDate, imageOnload) {
    const token = $('#token').attr('name');
    let year;
    let month;
    let day;
    let hour;
    let minute;
    let imageName;
    // add scrollable div
    let HTMLString = `<div class="row outer"><div id="${stationId}" class="col scrollable">`;
    let index = 0;

    // foreach 5 minutes
    for (
        const loopDate = new Date(startDate);
        loopDate < endDate;
        loopDate.setTime(loopDate.getTime() + (5 * 60 * 1000))
    ) {
        // generate a new image
        const newImage = document.createElement('IMG');
        newImage.setAttribute('class', 'spectrogram');

        year = loopDate.getFullYear();
        month = `0${loopDate.getMonth() + 1}`.slice(-2);
        day = `0${loopDate.getDate()}`.slice(-2);
        hour = `0${loopDate.getHours()}`.slice(-2);
        minute = `0${loopDate.getMinutes()}`.slice(-2);
        // generate the supposed image name based on the date and the station id
        imageName = `RAD_BEDOUR_${year}${month}${day}_${hour}${minute}_${stationId}`;

        const imageUrl = `
            /index.php?
            option=com_bramsdata
            &task=getImage
            &view=viewer
            &format=png
            &image=${imageName}
            ${fParams}
            &${token}=1
        `;
        // add the image to the HTML string
        HTMLString += `
            <img
                src="${imageUrl}"
                alt="${imageName}"
                class="spectrogram ${stationId}"
                onerror="this.onerror=null;this.src='/ProjectDir/img/brams_viewer/no_data.jpg';"
                onclick="createGallery(this.parentElement, ${index})"
                onload="${imageOnload}"
            >
        `;
        index += 1;
    }
    HTMLString += '</div></div>';
    // add the images to the page
    document.getElementById('spectrogramContainer').innerHTML += HTMLString;
    stations.push(stationId);
}

/**
 * Function call the function to create the spectrograms for a specific station
 * during a given interval (startDate -> endDate).
 * It then adds all the images to the page through the loadSpectrogramsRow function.
 *
 * @param {String} stationId string station id
 * @param {string} fMin fmin input value
 * @param {string} fMax fmax input value
 * @param {DateTime} startDate start date
 * @param {DateTime} endDate end date
 * @param {String} imageOnload Image onload attribute
 */
function getSpectrograms(stationId, fMin, fMax, startDate, endDate, imageOnload) {
    const token = $('#token').attr('name');
    let intFMin;
    let intFMax;
    let fParams = '';

    // verify the values of fmin and fmax
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
            loadSpectrogramsRow(station, fParams, startDate, endDate, imageOnload);
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

/**
 * Function is the entrypoint to show the spectrograms.
 * It verifies the dates (sets default dates if needed), gets all the
 * selected stations and the fmin and fmax values.
 * Finally, it calls the API to make the images for each selected station.
 * 
 * @returns void
 */
function showSpectrograms() {
    const startDate = new Date(Date.parse(document.getElementById('startDate').value));
    if (isNaN(startDate)) {
        return;
    }

    // show the loading spinner
    document.getElementById('spinner').style.display = 'inline';

    const fMin = document.getElementById('fMin').value;
    const fMax = document.getElementById('fMax').value;

    // set minutes to be a multiple of 5
    startDate.setMinutes(startDate.getMinutes() - (startDate.getMinutes() % 5))

    // set end date to be 65 minutes higher than the start date
    const endDate = new Date(startDate);
    endDate.setMinutes(startDate.getMinutes() + 65);

    const selectedStations = getSelectedCheckboxes();

    // if no stations were selected
    if (selectedStations.length === 0) {
        document.getElementById('spinner').style.display = 'none';
    }
    document.getElementById('spectrogramContainer').innerHTML = '';
    stations = [];

    // foreach station, call the api to make the images
    selectedStations.forEach(
        (station, index) => {
            let imageOnload = '';

            // if this is the last selected station, add an image onload function
            if (index === selectedStations.length - 1) imageOnload = "document.getElementById('spinner').style.display = 'none';";
            getSpectrograms(station, fMin, fMax, startDate, endDate, imageOnload);
        }
    );
}
