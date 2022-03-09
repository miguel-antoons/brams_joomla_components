/* global observers */
let nRows = 17;
const step = 17;

function showTable() {
    let tempObservers = [];
    let tbodyString = '';
    let lastRow = '';

    if (nRows >= observers.length) {
        nRows = observers.length;
        tempObservers = observers.slice();
        lastRow = "<tr><td onClick='showLess()' class='lastRow' colspan='3'>Load Less</td></tr>";
    } else {
        tempObservers = observers.slice(0, nRows);
        lastRow = "<tr><td onClick='showMore()' class='lastRow' colspan='3'>Load More</td></tr>";
    }

    tempObservers.forEach((observer) => {
        tbodyString += `
            <tr>
                <td>${observer[0]}</td>
                <td>${observer[1]}</td>
                <td>${observer[2]}</td>
            </tr>
        `;
    });

    tbodyString += lastRow;
    document.getElementById('observers').innerHTML = tbodyString;
}

function onPageLoad() {
    observers.sort((first, second) => first[0] > second[0]);

    showTable();
}

function sortFirstName(headerElement, desc) {
    const sortLastNameEl = document.getElementById('sortLastName');
    const sortLocationsEl = document.getElementById('sortLocations');
    sortLastNameEl.onclick = function sort() { sortLastName(sortLastNameEl, 0); };
    sortLocationsEl.onclick = function sort() { sortLocations(sortLocationsEl, 0); };

    if (desc) {
        headerElement.onclick = function sort() { sortFirstName(headerElement, 0); };
        observers.sort((first, second) => first[0] < second[0]);
    } else {
        headerElement.onclick = function sort() { sortFirstName(headerElement, 1); };
        observers.sort((first, second) => first[0] > second[0]);
    }

    document.getElementById('sortIcon').remove();
    headerElement.innerHTML += '<i id="sortIcon" class="fa fa-sort" aria-hidden="true"></i>';

    showTable();
}

function sortLastName(headerElement, desc) {
    const sortFirstNameEl = document.getElementById('sortFirstName');
    const sortLocationsEl = document.getElementById('sortLocations');
    sortFirstNameEl.onclick = function sort() { sortFirstName(sortFirstNameEl, 0); };
    sortLocationsEl.onclick = function sort() { sortLocations(sortLocationsEl, 0); };

    if (desc) {
        headerElement.onclick = function sort() { sortLastName(headerElement, 0); };
        observers.sort((first, second) => first[1] < second[1]);
    } else {
        headerElement.onclick = function sort() { sortLastName(headerElement, 1); };
        observers.sort((first, second) => first[1] > second[1]);
    }

    document.getElementById('sortIcon').remove();
    headerElement.innerHTML += '<i id="sortIcon" class="fa fa-sort" aria-hidden="true"></i>';

    showTable();
}

function sortLocations(headerElement, desc) {
    const sortLastNameEl = document.getElementById('sortLastName');
    const sortFirstNameEl = document.getElementById('sortFirstName');
    sortLastNameEl.onclick = function sort() { sortLastName(sortLastNameEl, 0); };
    sortFirstNameEl.onclick = function sort() { sortFirstName(sortFirstNameEl, 0); };

    if (desc) {
        headerElement.onclick = function sort() { sortLocations(headerElement, 0); };
        observers.sort((first, second) => first[2] < second[2]);
    } else {
        headerElement.onclick = function sort() { sortLocations(headerElement, 1); };
        observers.sort((first, second) => first[2] > second[2]);
    }

    document.getElementById('sortIcon').remove();
    headerElement.innerHTML += '<i id="sortIcon" class="fa fa-sort" aria-hidden="true"></i>';

    showTable();
}

function showMore() {
    nRows += step;
    showTable();
}

function showLess() {
    nRows -= step;
    showTable();
}
