/* global observers */
let nRows = 17;                 // number of rows shown when the page is first loaded
let sortFirstNameDesc = true;   // set the next sorting method on first name to desc
let sortLastNameDesc = false;   // set the next sorting method on last name to asc
let sortLocationDesc = false;   // set the next sorting method on location to asc
let log = 'Nothing to show';
let observers;

/**
 * Function generates the html string to add inside the tables tbody.
 * It finally replaces the t-body inner-html with that string.
 */
function showTable() {
    let tempObservers;          // contains the observers that will be shown
    let tbodyString = '';       // contains the complete html string
    let lastRow;                // contains the last rows html string

    // if the number of row to display is greater or equal to the observers array length
    if (nRows >= observers.length) {
        // select all the observers that will be shown on page
        tempObservers = observers.slice();
        // set the last row to a 'show less' button
        lastRow = "<tr><td onclick='showLess()' class='lastRow' colspan='3'>Load Less</td></tr>";
    } else {
        // select all the observers that will be shown on page
        tempObservers = observers.slice(0, nRows);
        // set the last row to a 'show more' button
        lastRow = "<tr><td onclick='showMore()' class='lastRow' colspan='3'>Load More</td></tr>";
    }

    // generate a row for each observer to be shown on page
    tempObservers.forEach((observer) => {
        tbodyString += `
            <tr>
                <td class='width25'>${observer['first_name']}</td>
                <td class='width25'>${observer['last_name']}</td>
                <td class='width50'>${observer['locations']}</td>
            </tr>
        `;
    });

    tbodyString += lastRow;
    document.getElementById('observers').innerHTML = tbodyString;
}

/**
 * Function changes the sort icon to the last clicked table header.
 * @param {HTMLTableCellElement} headerElement table header that was clicked for sorting
 */
function setSortIcon(headerElement) {
    // remove the sort icon from the page
    document.getElementById('sortIcon').remove();
    // add the icon to the clicked element ('headerElement')
    headerElement.innerHTML += '<i id="sortIcon" class="fa fa-sort" aria-hidden="true"></i>';
}

/**
 * Function sorts table by first name (asc or desc). It also changes the sort
 * icon position if needed and updates the function to be called when clicking
 * on 'headerElement'.
 * @param {HTMLTableCellElement} headerElement html element that performed the call to this function
 */
function sortFirstName(headerElement) {
    sortLastNameDesc = false;   // set the next sorting method on last name to asc
    sortLocationDesc = false;   // set the next sorting method on location to asc

    // sort asc or desc and change the onclick property of 'headerElement'
    if (sortFirstNameDesc) {
        observers.sort((first, second) => first['first_name'] < second['first_name']);
    } else {
        observers.sort((first, second) => first['first_name'] > second['first_name']);
    }

    sortFirstNameDesc = !sortFirstNameDesc;

    setSortIcon(headerElement);
    showTable();
}

/**
 * Function sorts table by last name (asc or desc). It also changes the sort
 * icon position if needed and updates the function to be called when clicking
 * on 'headerElement'.
 * @param {HTMLTableCellElement} headerElement html element that performed the call to this function
 */
function sortLastName(headerElement) {
    sortFirstNameDesc = false;  // set the next sorting method on first name to asc
    sortLocationDesc = false;   // set the next sorting method on location to asc

    // sort asc or desc and change the onclick property of 'headerElement'
    if (sortLastNameDesc) {
        observers.sort((first, second) => first['last_name'] < second['last_name']);
    } else {
        observers.sort((first, second) => first['last_name'] > second['last_name']);
    }

    sortLastNameDesc = !sortLastNameDesc;

    setSortIcon(headerElement);
    showTable();
}

/**
 * Function sorts table by location (asc or desc). It also changes the sort
 * icon position if needed and updates the function to be called when clicking
 * on 'headerElement'.
 * @param {HTMLTableCellElement} headerElement html element that performed the call to this function
 */
function sortLocations(headerElement) {
    sortFirstNameDesc = false;  // set the next sorting method on first name to asc
    sortLastNameDesc = false;   // set the next sorting method on last name to asc

    // sort asc or desc and change the onclick property of 'headerElement'
    if (sortLocationDesc) {
        observers.sort((first, second) => first['locations'] < second['locations']);
    } else {
        observers.sort((first, second) => first['locations'] > second['locations']);
    }

    sortLocationDesc = !sortLocationDesc;

    setSortIcon(headerElement);
    showTable();
}

// increase the rows to show and update the table
function showMore() {
    nRows += observers.length;
    showTable();
}

// decrease the rows to show and update the table
function showLess() {
    nRows = 17;
    showTable();
}

/**
 * Function makes an api call to back-end to get all the observers.
 * Once it gets a valid response, it stores the observers in a local array and
 * shows the observers on page.
 */
function getObservers() {
    const token = $("#token").attr("name");

    $.ajax({
        type: 'GET',
        url: `/index.php?option=com_bramsnetwork&view=observers&task=getobservers&format=json&${token}=1`,
        success: (response) => {
            // convert the primary object into an array of objects
            observers = Object.keys(response.data).map((key) => response.data[key]);
            // sort once by first name and show the table on page
            observers.sort((first, second) => first['first_name'] > second['first_name']);
            showTable();
        },
        error: (response) => {
            // on fail, show an error message
            document.getElementById('error').innerHTML = (
                'API call failed, please read the \'log\' variable in ' +
                'developer console for more information about the problem.'
            );
            // store the server response in the log variable
            log = response;
        },
    });
}

window.onload = getObservers;
