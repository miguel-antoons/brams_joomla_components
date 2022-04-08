/* eslint-disable no-unused-vars */
/**
 * @author Antoons Miguel
 *
 * * This file contains function and variable that (almost) each list/table
 * * needs (delete api, sorting methods, ...).
 *
 * * Note that when a variable/comment is referring to 'element' this
 * * means the instance of a broader category. That category can be
 * * (among others) antennas, systems, locations, ...
 * * So, following the above examples, an element can be an antenna,
 * * a system, a location, ...
 * * The specific category at a precise moment depends on the view
 * * that loads this file.
 * * The available categories depend on the views present in the BRAMS
 * * administration component.
 */

/* global $,  sortDescFlags, generateTable */
// eslint-disable-next-line no-unused-vars
let log = 'Nothing to show';
let elements;
const apiFailMessg = (
    'API call failed, please read the \'log\' variable in '
    + 'developer console for more information about the problem.'
);

// function stops the onclick property from .tableRow classes
// from firing when clicking on a button inside a .systemRow class
function stopPropagation() {
    $('.tableRow button').on('click', (e) => {
        e.stopPropagation();
    });
}

function sortAsc(first, second, noSpace = false) {
    if (first === null) return 1;
    if (second === null) return -1;
    // eslint-disable-next-line no-param-reassign
    if (noSpace) { first = first.replace(/\s/g, ''); second = second.replace(/\s/g, ''); }
    if (first > second) return 1;
    if (first < second) return -1;
    return 0;
}

function sortDesc(first, second, noSpace = false) {
    if (first === null) return 1;
    if (second === null) return -1;
    // eslint-disable-next-line no-param-reassign
    if (noSpace) { first = first.replace(/\s/g, ''); second = second.replace(/\s/g, ''); }
    if (first < second) return 1;
    if (first > second) return -1;
    return 0;
}

/**
 * Function deletes an element that has an id equal to 'elementId'.
 *
 * @param {number} elementId id of the element to delete
 * @param {string} displayName name to refer to in messages (i.e. a pop-up)
 * @param {string} viewName name of the specific delete api to call
 * @returns void
 */
function deleteRow(elementId, displayName, viewName) {
    // eslint-disable-next-line no-alert, no-restricted-globals
    if (!confirm(`Are you sure you want to delete ${displayName}`)) return;
    const token = $('#token').attr('name');

    $.ajax({
        type: 'DELETE',
        url: `
            /index.php?
            option=com_bramscampaign
            &view=${viewName}
            &task=delete
            &format=json
            &id=${elementId}
            &${token}=1
        `,
        success: (response) => {
            // on success, update the html table by removing the system from it
            const isDeletedElement = (element) => Number(element.id) === elementId;
            elements.splice(elements.findIndex(isDeletedElement), 1);
            generateTable();
            document.getElementById('message').innerHTML = response.data.message;
        },
        error: (response) => {
            // on fail, show an error message
            document.getElementById('message').innerHTML = apiFailMessg;
            // store the server response in the log variable
            log = response;
        },
    });
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
 * @param {string}               attribute      column to sort on
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
        // sort the antennas array desc
        elements.sort((first, second) => sortDesc(first[attribute], second[attribute], noSpace));
    } else {
        // sort asc
        elements.sort((first, second) => sortAsc(first[attribute], second[attribute], noSpace));
    }

    // toggle the sorting method
    sortDescFlags[attribute] = !sortDescFlags[attribute];

    setSortIcon(headerElement);
    generateTable();
}
