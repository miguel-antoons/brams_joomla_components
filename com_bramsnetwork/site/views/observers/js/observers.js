/* global observers */
let nRows = 17;     // number of rows shown when the page is first loaded
const step = 17;    // number of rows to add when clicking on the 'show more' button

/**
 * Function generates the html string to add inside the tables tbody.
 * It finally replaces the tbodys innerhtml with that string.
 */
function showTable() {
    let tempObservers = [];     // contains the observers that will be shown
    let tbodyString = '';       // contains the complete html string
    let lastRow = '';           // contains the last rows html string

    // if the number of row to display is greater or equal to the observers array length
    if (nRows >= observers.length) {
        // update the nRows value to the observers array length
        nRows = observers.length;
        // select all the observers that will be shown on page
        tempObservers = observers.slice();
        // set the last row to a 'show less' button
        lastRow = "<tr><td onClick='showLess()' class='lastRow' colspan='3'>Load Less</td></tr>";
    } else {
        // select all the observers that will be shown on page
        tempObservers = observers.slice(0, nRows);
        // set the last row to a 'show more' button
        lastRow = "<tr><td onClick='showMore()' class='lastRow' colspan='3'>Load More</td></tr>";
    }

    // generate a row for each observer to be shown on page
    tempObservers.forEach((observer) => {
        tbodyString += `
            <tr class='d-flex'>
                <td class='col-3'>${observer[0]}</td>
                <td class='col-3'>${observer[1]}</td>
                <td class='col-6'>${observer[2]}</td>
            </tr>
        `;
    });

    tbodyString += lastRow;
    document.getElementById('observers').innerHTML = tbodyString;
}

/**
 * Function fires 1 time during page loading. It sorts the array 1 time
 * and call the 'showTable' function that will generate the tables tbody.
 */
function onPageLoad() {
    observers.sort((first, second) => first[0] > second[0]);

    showTable();
}

/**
 * Function sorts table by first name (asc or desc). It also changes the sort
 * icon position if needed and updates the function to be called when clicking
 * on 'headerElement'.
 * @param {html} headerElement html element that performed the call to this function
 * @param {boolean} desc indicates if the table must be sorted desc (1) or asc (0)
 */
function sortFirstName(headerElement, desc) {
    const sortLastNameEl = document.getElementById('sortLastName');
    const sortLocationsEl = document.getElementById('sortLocations');

    // set other headerElements onclick to default value
    sortLastNameEl.onclick = function sort() { sortLastName(sortLastNameEl, 0); };
    sortLocationsEl.onclick = function sort() { sortLocations(sortLocationsEl, 0); };

    // sort asc or desc and change the onclick prperty of 'headerElement'
    if (desc) {
        headerElement.onclick = function sort() { sortFirstName(headerElement, 0); };
        observers.sort((first, second) => first[0] < second[0]);
    } else {
        headerElement.onclick = function sort() { sortFirstName(headerElement, 1); };
        observers.sort((first, second) => first[0] > second[0]);
    }

    // remove the sort icon from the page
    document.getElementById('sortIcon').remove();
    // add the icon to the clicked element ('headerElement')
    headerElement.innerHTML += '<i id="sortIcon" class="fa fa-sort" aria-hidden="true"></i>';

    showTable();
}

/**
 * Function sorts table by last name (asc or desc). It also changes the sort
 * icon position if needed and updates the function to be called when clicking
 * on 'headerElement'.
 * @param {html} headerElement html element that performed the call to this function
 * @param {boolean} desc indicates if the table must be sorted desc (1) or asc (0)
 */
function sortLastName(headerElement, desc) {
    const sortFirstNameEl = document.getElementById('sortFirstName');
    const sortLocationsEl = document.getElementById('sortLocations');

    // set other headerElements onclick to default value
    sortFirstNameEl.onclick = function sort() { sortFirstName(sortFirstNameEl, 0); };
    sortLocationsEl.onclick = function sort() { sortLocations(sortLocationsEl, 0); };

    // sort asc or desc and change the onclick prperty of 'headerElement'
    if (desc) {
        headerElement.onclick = function sort() { sortLastName(headerElement, 0); };
        observers.sort((first, second) => first[1] < second[1]);
    } else {
        headerElement.onclick = function sort() { sortLastName(headerElement, 1); };
        observers.sort((first, second) => first[1] > second[1]);
    }

    // remove the sort icon from the page
    document.getElementById('sortIcon').remove();
    // add the icon to the clicked element ('headerElement')
    headerElement.innerHTML += '<i id="sortIcon" class="fa fa-sort" aria-hidden="true"></i>';

    showTable();
}

/**
 * Function sorts table by location (asc or desc). It also changes the sort
 * icon position if needed and updates the function to be called when clicking
 * on 'headerElement'.
 * @param {html} headerElement html element that performed the call to this function
 * @param {boolean} desc indicates if the table must be sorted desc (1) or asc (0)
 */
function sortLocations(headerElement, desc) {
    const sortLastNameEl = document.getElementById('sortLastName');
    const sortFirstNameEl = document.getElementById('sortFirstName');

    // set other headerElements onclick to default value
    sortLastNameEl.onclick = function sort() { sortLastName(sortLastNameEl, 0); };
    sortFirstNameEl.onclick = function sort() { sortFirstName(sortFirstNameEl, 0); };

    // sort asc or desc and change the onclick prperty of 'headerElement'
    if (desc) {
        headerElement.onclick = function sort() { sortLocations(headerElement, 0); };
        observers.sort((first, second) => first[2] < second[2]);
    } else {
        headerElement.onclick = function sort() { sortLocations(headerElement, 1); };
        observers.sort((first, second) => first[2] > second[2]);
    }

    // remove the sort icon from the page
    document.getElementById('sortIcon').remove();
    // add the icon to the clicked element ('headerElement')
    headerElement.innerHTML += '<i id="sortIcon" class="fa fa-sort" aria-hidden="true"></i>';

    showTable();
}

// increase the rows to show and update the table
function showMore() {
    nRows += step;
    showTable();
}

// decrease the rows to show and update the table
function showLess() {
    nRows -= step;
    showTable();
}
