/* global observers */
let sortDescFlags = {
    first_name: true,           // set the next sorting method on first name to desc
    last_name: false,           // set the next sorting method on last name to asc
    locations: false,           // set the next sorting method on location to asc
};
let nRows = 17;                 // number of rows shown when the page is first loaded
let log = 'Nothing to show';
let observers;

function sortAsc(first, second, noSpace) {
    if (first === null) return 1;
    if (second === null) return -1;
    // eslint-disable-next-line no-param-reassign
    if (noSpace) { first = first.replace(/\s/g, ''); second = second.replace(/\s/g, ''); }
    if (first > second) return 1;
    if (first < second) return -1;
    return 0;
}

function sortDesc(first, second, noSpace) {
    if (first === null) return 1;
    if (second === null) return -1;
    // eslint-disable-next-line no-param-reassign
    if (noSpace) { first = first.replace(/\s/g, ''); second = second.replace(/\s/g, ''); }
    if (first < second) return 1;
    if (first > second) return -1;
    return 0;
}

/**
 * Function generates the html string to add inside the tables tbody.
 * It finally replaces the t-body inner-html with that string.
 */
function generateTable() {
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
 * Function sorts the table by attribute parameter
 * @param {HTMLTableCellElement} headerElement  table header that was clicked for sorting
 * @param {string}               attribute      location attribute to sort on
 * @param {boolean}              noSpace        Whether to remove spaces or not from strings when sorting
 */
function sortTable(headerElement, attribute, noSpace = false) {
    // reset all the sorting methods for all the other table headers
    Object.keys(sortDescFlags).forEach((key) => {
        if (key !== attribute) {
            sortDescFlags[key] = false;
        }
    });

    // if sorting method is set to desc
    if (sortDescFlags[attribute]) {
        // sort the system array desc
        observers.sort((first, second) => sortDesc(first[attribute], second[attribute], noSpace));
    } else {
        // sort asc
        observers.sort((first, second) => sortAsc(first[attribute], second[attribute], noSpace));
    }

    // toggle the sorting method
    sortDescFlags[attribute] = !sortDescFlags[attribute];

    setSortIcon(headerElement);
    generateTable();
}

// increase the rows to show and update the table
function showMore() {
    nRows += observers.length;
    generateTable();
}

// decrease the rows to show and update the table
function showLess() {
    nRows = 17;
    generateTable();
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
            observers.sort((first, second) => sortAsc(first.first_name, second.first_name))
            generateTable();
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
