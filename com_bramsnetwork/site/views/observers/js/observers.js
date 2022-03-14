/* global observers */
let nRows = 17;                 // number of rows shown when the page is first loaded
let sortFirstNameDesc = true;   // set the next sorting method on first name to desc
let sortLastNameDesc = false;   // set the next sorting method on last name to asc
let sortLocationDesc = false;   // set the next sorting method on location to asc
const step = 17;                // number of rows to add when clicking on the 'show more' button

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
            <tr>
                <td width="25%">${observer[0]}</td>
                <td width="25%">${observer[1]}</td>
                <td width="50%">${observer[2]}</td>
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
 */
function sortFirstName(headerElement) {
    sortLastNameDesc = false;   // set the next sorting method on last name to asc
    sortLocationDesc = false;   // set the next sorting method on location to asc

    // sort asc or desc and change the onclick prperty of 'headerElement'
    if (sortFirstNameDesc) {
        observers.sort((first, second) => first[0] < second[0]);
    } else {
        observers.sort((first, second) => first[0] > second[0]);
    }

    sortFirstNameDesc = !sortFirstNameDesc;

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
 */
function sortLastName(headerElement) {
    sortFirstNameDesc = false;  // set the next sorting method on first name to asc
    sortLocationDesc = false;   // set the next sorting method on location to asc

    // sort asc or desc and change the onclick prperty of 'headerElement'
    if (sortLastNameDesc) {
        observers.sort((first, second) => first[1] < second[1]);
    } else {
        observers.sort((first, second) => first[1] > second[1]);
    }

    sortLastNameDesc = !sortLastNameDesc;

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
 */
function sortLocations(headerElement) {
    sortFirstNameDesc = false;  // set the next sorting method on first name to asc
    sortLastNameDesc = false;   // set the next sorting method on last name to asc

    // sort asc or desc and change the onclick prperty of 'headerElement'
    if (sortLocationDesc) {
        observers.sort((first, second) => first[2] < second[2]);
    } else {
        observers.sort((first, second) => first[2] > second[2]);
    }

    sortLocationDesc = !sortLocationDesc;

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
