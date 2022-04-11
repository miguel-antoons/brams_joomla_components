/* eslint-disable no-unused-vars */
/**
 * @author Antoons Miguel
 *
 * * This file contains function and variable that (almost) each form
 * * needs (update api, create api, ...).
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

/* global $ */
// eslint-disable-next-line no-unused-vars
let log = 'Nothing to show';        // contains debug information if needed
let elementId;                      // contains the id of the edited element
// eslint-disable-next-line no-unused-vars
let codes = [];                     // contains all the codes that are not available anymore
const apiFailMessg = (
    'API call failed, please read the \'log\' variable in '
    + 'developer console for more information about the problem.'
);

/**
 * Function creates a new element.
 *
 * @param {object} data         attribute values of the new element
 * @param {string} apiView      name of the api (this param decides what element will be created)
 * @param {string} redirectView view to redirect to after the element is created
 */
function newElement(data, apiView, redirectView) {
    const token = $('#token').attr('name');

    $.ajax({
        type: 'POST',
        url: `
            /index.php?
            option=com_bramscampaign
            &task=create
            &view=${apiView}
            &format=json
            &${token}=1
        `,
        data,
        success: () => {
            // on success return to the antennas page
            window.location.href = `
                /index.php?option=option=com_bramscampaign&view=${redirectView}&message=2`;
        },
        error: (response) => {
            // on fail, show an error message
            document.getElementById('error').innerHTML = apiFailMessg;
            // store the server response in the log variable
            log = response;
        },
    });
}

/**
 * Function updates an element with the given values.
 *
 * @param {object} data         updated data of the element
 * @param {string} apiView      name of the api (this param decides what element will be updated)
 * @param {string} redirectView view to redirect to after the element has been updated
 */
function updateElement(data, apiView, redirectView) {
    const token = $('#token').attr('name');

    $.ajax({
        type: 'POST',
        url: `
            /index.php?
            option=com_bramscampaign
            &task=update
            &view=${apiView}
            &format=json
            &${token}=1
        `,
        data,
        success: () => {
            // on success return to the antennas page
            window.location.href = `
                /index.php?option=com_bramscampaign&view=${redirectView}&message=1`;
        },
        error: (response) => {
            // on fail, show an error message
            document.getElementById('error').innerHTML = apiFailMessg;
            // store the server response in the log variable
            log = response;
        },
    });
}

/**
 * Function gets all the taken codes from the elements via an api call to
 * the sites back-end. This function exists in order to verify if
 * the entered code hasn't been taken yet.
 */
function getCodes(apiView) {
    const token = $('#token').attr('name');

    $.ajax({
        type: 'GET',
        url: `
            /index.php?
            option=com_bramscampaign
            &task=getCodes
            &view=${apiView}
            &format=json
            &id=${elementId}
            &${token}=1
        `,
        success: (response) => {
            // store the codes locally
            codes = response.data;
        },
        error: (response) => {
            // on fail, show an error message
            document.getElementById('error').innerHTML = apiFailMessg;
            // store the server response in the log variable
            log = response;
        },
    });
}
