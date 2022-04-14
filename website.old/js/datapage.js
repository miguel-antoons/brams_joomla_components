const viewportwidth = screen.availWidth;
const viewportheight = screen.availHeight;
let viewHeight = 0;
let imgHeight = 0;
let allStations;
let timeoutValue;
let fmax = 0;
let fmin = 0;
const timeFrames = 0;
$(document).ready(() => {
    $('img.bramsImage').load(() => {
    });
    startDate = getStartDate();
    endDate = getEndDate();
    $('.tDate').datepicker({
        dateFormat: 'yy-mm-dd', changeYear: true, changeMonth: true, minDate: startDate, maxDate: endDate,
    });
    $('#tDate').datepicker({
        dateFormat: 'yy-mm-dd', changeYear: true, changeMonth: true, minDate: startDate, maxDate: endDate,
    });
    $('.bramsSelector').each(function (index) {
        stationName = ($(this).attr('station_name'));
    });
    allStations = $('.cBox:checked').map(function () { return $(this).val(); }).get();
    if (allStations.length == 1) {
        $('.frequency').attr('disabled', false);
        $('.frequencylabel').removeClass('disabled');
    } else {
        $('.frequency').val('');
        $('.frequency').attr('disabled', 'disabled');
        $('.frequencylabel').addClass('disabled');
    }
    viewHeight = $('div#canvasBottomPosition').offset().top - $('div#canvasTopPosition').offset().top;
    $('div#imageArea').height(viewHeight);
    $('.titleSpacer').height(viewHeight / $('.bramsSelector').length);
});

function dateString(d) {
    function pad(n) { return n < 10 ? `0${n}` : n; }
    return `${d.getFullYear()}-${
        pad(d.getMonth() + 1)}-${
        pad(d.getDate())}T${
        pad(d.getHours())}:${
        pad(d.getMinutes())}`;
}

function getStartDate() {
    let startDate = new Date(0);
    const boxes = $('.cBox:checked');
    boxes.each(function () {
        thisDate = new Date($(this).attr('start'));
        if (thisDate > startDate) {
            startDate = thisDate;
        }
    });
    return (startDate);
}
function getEndDate() {
    let endDate = new Date();
    const boxes = $('.cBox:checked');
    boxes.each(function () {
        thisDate = new Date($(this).attr('end'));
        if (thisDate < endDate) {
            endDate = thisDate;
        }
    });
    return (endDate);
}

function prepareImageArea() {
    dateParts = $('#tDate').val().split('-');
    timeParts = $('#tHours option:selected').text().split(':');
    startDate = new Date(dateParts[0], dateParts[1] - 1, dateParts[2], timeParts[0], timeParts[1]);
    endDate = new Date(startDate);
    endDate.setMinutes(startDate.getMinutes() + 65);
    fmin = $('#fmin').val();
    fmax = $('#fmax').val();
    imgHeight = Math.round((viewHeight - 20) / allStations.length);
    const boxes = $('.cBox.highlight');
    $('#imageCanvas').empty();
    $('stationArea').empty();
    if (boxes.length != $('#imageCanvas').children().length - 1) {
        boxes.each(function () {
            $('#imageCanvas').append(`<div id=${$(this).val()} class=stationArea >${$(this).val()}</div>`);
        });
    }
    $('.stationArea').each(function () {
        $(this).on = ('getImg', requestImage($(this), startDate, endDate));
    });
    $('.stationArea').empty();
    loopDate = new Date(startDate);
    for (loopTime = loopDate.getTime(); loopTime < endDate.getTime(); loopTime += (1000 * 5 * 60)) {
        loopDate.setTime(loopTime);
        month = `0${loopDate.getMonth() + 1}`;
        day = `0${loopDate.getDate()}`;
        hour = `0${loopDate.getHours()}`;
        minute = `0${loopDate.getMinutes()}`;
        boxes.each(function () {
            idName = `#${$(this).val()}`;
            $(idName).append('<div class=tImg id=' + `RAD_BEDOUR_${loopDate.getFullYear()}${month.slice(-2)}${day.slice(-2)}_${hour.slice(-2)}${minute.slice(-2)}_${$(this).val()}><img class=waitImg src=/img/brams_viewer/loading.gif alert(thisUrl);if></div>`);
        });
    }
    $('.tImg').each(function () {
        $(this).height(imgHeight);
        $(this).children().height(imgHeight);
    });
    newWidth = `${(((imgHeight * 1024) / 755) * 13) + 100}px`;
    $('.stationArea').css('width', newWidth);
    $('.stationArea').trigger('getImg');
}

function requestImage(obj, startDate, endDate) {
    urlParams = '';
    if (fmax != '' && fmin != '') {
        if (fmax - fmin < 10) {
            fmax = fmin + 10;
        }

        console.log(fmax);
        console.log(fmin);

        urlParams = `&fmax=${fmax}&fmin=${fmin}`;
    }

    const thisUrl = `/data/make_images?begin=${dateString(startDate)}&end=${dateString(endDate)}&station=${obj.attr('id')}`;
    const request = $.ajax({
        url: thisUrl + urlParams,
    });
    request.done((data) => {
        const newWidth = 0;
        $(`#${data}`).children().each(function () {
            $(this).html(`<img class=spectrogram src=/data/show_image?image=${$(this).attr('id')}${urlParams} onError="this.src='/img/brams_viewer/no_data.jpg';" onClick='showLightBox(this);' >`);
        });
    });

    request.fail((jqXHR, textStatus) => {
    });
}
const scrollDiv = function (dir, px) {
    const canvas = $('#imageCanvas');
    if (dir == 'l') {
        canvas.scrollLeft -= px;
    } else if (dir == 'r') {
        canvas.scrollLeft += px;
    }
};

function showLightBox(img) {
    if (img.src.indexOf('no_data') != -1) {
        return false;
    }
    const lb = $('#lightBox');
    const lbimg = $('#lightBoxImage');
    lbimg.attr('src', img.src);
    lb.toggleClass('showBox');
    lb.toggleClass('hideBox');
    const url = (img.src);
    waveUrl = url.replace('show_image', 'save_wave');
    imgUrl = url.replace('show_image', 'save_image');
    $('#wavLink').attr('href', waveUrl);
    $('#imgLink').attr('href', imgUrl);
}
function prepareSlideshow() {
    $('#slideshow').removeAttr('onclick').attr('onclick', 'stopSlideshow()');
    $('#slideshowImg').attr('src', '/img/brams_viewer/stopbutton.png');
    $('#slideshowImg').attr('title', 'stop slideshow');
    startSlideshow();
}
function startSlideshow() {
    browseImage('nextTime');
    timeoutValue = setTimeout(startSlideshow, 2000);
}
function stopSlideshow() {
    browseImage('nextTime');
    clearTimeout(timeoutValue);
    $('#slideshow').removeAttr('onclick').attr('onclick', 'prepareSlideshow()');
    $('#slideshowImg').attr('title', 'start slideshow');
    $('#slideshowImg').attr('src', '/img/brams_viewer/playbutton.png');
}
function browseImage(myRequest) {
    lbimg = $('#lightBoxImage');
    index = lbimg.attr('src').indexOf('=');
    thisTimeFrame = lbimg.attr('src').slice(index + 1);
    index = thisTimeFrame.indexOf('&');
    if (index != -1) { thisTimeFrame = thisTimeFrame.slice(0, index); }
    const timeFrames = [];
    const myStation = $(`#${thisTimeFrame}`).parent();
    myStation.children().each(function () { timeFrames.push($(this).attr('id')); });
    allStations = $('.cBox:checked').map(function () { return $(this).val(); }).get();

    index = 0;
    allFiles = [];
    $('.cBox:checked').each(function () {
        const stationName = $(this).val();
        const fileNames = [];
        $(`#${stationName}`).children().each(function () {
            if ($(this).children('img:first').attr('src') == '/img/brams_viewer/no_data.jpg') {
            } else {
                fileNames.push($(this).attr('id'));
            }
        });
        allFiles[index] = fileNames;
        index++;
    });

    stationLength = allStations.length - 1;
    newStation = myStation.attr('id');
    stationIndex = jQuery.inArray(myStation.attr('id'), allStations);
    timeLength = allFiles[stationIndex].length - 1;
    timeIndex = jQuery.inArray(thisTimeFrame, timeFrames);
    newTimeIndex = timeIndex;
    newStationIndex = stationIndex;
    if (myRequest == 'nextTime') {
        newTimeIndex = timeIndex == timeLength ? 0 : timeIndex + 1;
    }
    if (myRequest == 'prevTime') {
        newTimeIndex = timeIndex == 0 ? timeLength : timeIndex - 1;
    }
    if (myRequest == 'nextStation') {
        newStationIndex = stationIndex == stationLength ? 0 : stationIndex + 1;
    }
    if (myRequest == 'prevStation') {
        newStationIndex = stationIndex == 0 ? stationLength : stationIndex - 1;
    }
    modUrl = allFiles[newStationIndex][newTimeIndex];
    newUrl = ($(`#${modUrl}> img`).attr('src'));
    lbimg.attr('src', newUrl);
    waveUrl = newUrl.replace('show_image', 'save_wave');
    imgUrl = newUrl.replace('show_image', 'save_image');
    $('#wavLink').attr('href', waveUrl);
    $('#imgLink').attr('href', imgUrl);
}
function hideLightBox() {
    stopSlideshow();
    const lb = $('#lightBox');
    lb.toggleClass('showBox');
    lb.toggleClass('hideBox');
}
