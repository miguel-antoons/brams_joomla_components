let nRows = 17;
const step = 17;

function showTable() {
    let tempObservers = [];
    let tbodyString = '';
    let lastRow = '';

    if (nRows >= observers.length) {
        nRows = observers.length;
        tempObservers = observers.slice();
        lastRow = "<tr><td onClick='showMore()' class='lastRow' colspan='3'>Load More</td></tr>";
    } else {
        tempObservers = observers.slice(0, nRows);
        lastRow = "<tr><td onClick='showLess()' class='lastRow' colspan='3'>Load Less</td></tr>";
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
    if (desc) {
        headerElement.onclick = function sort() { sortFirstName(headerElement, 0); };
        observers.sort((first, second) => first[0] > second[0]);
    } else {
        headerElement.onclick = function sort() { sortFirstName(headerElement, 1); };
        observers.sort((first, second) => first[0] < second[0]);
    }

    document.getElementById('sortIcon').remove();
    headerElement.innerHTML += '<i id="sortIcon" class="fa-solid fa-sort"></i>';

    showTable();
}

function sortLastName(headerElement, desc) {
    if (desc) {
        headerElement.onclick = function sort() { sortLastName(headerElement, 0); };
        observers.sort((first, second) => first[1] > second[1]);
    } else {
        headerElement.onclick = function sort() { sortLastName(headerElement, 1); };
        observers.sort((first, second) => first[1] < second[1]);
    }

    document.getElementById('sortIcon').remove();
    headerElement.innerHTML += '<i id="sortIcon" class="fa-solid fa-sort"></i>';

    showTable();
}

function sortLocations(headerElement, desc) {
    if (desc) {
        headerElement.onclick = function sort() { sortLocations(headerElement, 0); };
        observers.sort((first, second) => first[3] > second[3]);
    } else {
        headerElement.onclick = function sort() { sortLocations(headerElement, 1); };
        observers.sort((first, second) => first[3] < second[3]);
    }

    document.getElementById('sortIcon').remove();
    headerElement.innerHTML += '<i id="sortIcon" class="fa-solid fa-sort"></i>';

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
