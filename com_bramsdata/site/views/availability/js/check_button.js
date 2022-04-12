let log = 'Nothing to show';    // contains debug information if needed
// graph options
const options = {
    id_div_container: "visavail_container",
    id_div_graph: "visavail_graph",
    responsive: {
        enabled: true,
    },
    custom_categories: true,
    onClickBlock: zoomGraph,
};
let chart = false;

// verify if the 'check all' checkbox has to be checked
function changeAllStatus() {
    const checkAllBox = document.getElementById('checkAll');
    const systemCheckBoxes = document.getElementsByClassName('custom_checkbox');
    let allChecked = true;

    // iterate over all checkboxes to check if all are checked or not
    for (let i = 0; i < systemCheckBoxes.length && allChecked; i += 1) {
        if (!systemCheckBoxes[i].checked) {
            allChecked = false;
        }
    }

    // set the correct value
    checkAllBox.checked = allChecked;
}

// Check all Raspberry PI boxes
function checkRPIBoxes(source) {
    // get all Raspberry PI boxes and all active boxes
    const RPICheckBoxes = [].slice.call(document.getElementsByClassName('SSH'));
    const activeSystems = [].slice.call(document.getElementsByClassName('A'));

    // check/uncheck all active Raspberry boxes
    for (let i = 0; i < RPICheckBoxes.length; i += 1) {
        if (activeSystems.includes(RPICheckBoxes[i])) {
            RPICheckBoxes[i].checked = source.checked;
        }
    }

    changeAllStatus();
}

// check/uncheck all older station checkboxes
function checkFTPBoxes(source) {
    // get FTP, USB and active stations
    const FTPCheckBoxes = [].slice.call(document.getElementsByClassName('FTP'));
    const USBCheckBoxes = [].slice.call(document.getElementsByClassName('USB'));
    const oldSystems = FTPCheckBoxes.concat(USBCheckBoxes);
    const activeSystems = [].slice.call(document.getElementsByClassName('A'));

    // check/uncheck all older station checkboxes
    for (let i = 0; i < oldSystems.length; i += 1) {
        if (activeSystems.includes(oldSystems[i])) {
            oldSystems[i].checked = source.checked;
        }
    }

    changeAllStatus();
}

// check/uncheck all checkboxes
function checkAllBoxes(source) {
    const allCheckboxes = document.querySelectorAll('input[type=\'checkbox\']');
    for (let i = 0; i < allCheckboxes.length; i += 1) {
        allCheckboxes[i].checked = source.checked;
    }
}

// verify if the 'check old' checkbox has to be checked
function changeRPIStatus() {
    const checkRPIBox = document.getElementById('checkRPI');
    const RPICheckBoxes = [].slice.call(document.getElementsByClassName('SSH'));
    const activeSystems = [].slice.call(document.getElementsByClassName('A'));
    let allRPIChecked = true;

    // iterate over all the RPI checkboxes and verify if they are checked
    for (let i = 0; i < RPICheckBoxes.length && allRPIChecked; i += 1) {
        if (!RPICheckBoxes[i].checked && activeSystems.includes(RPICheckBoxes[i])) {
            allRPIChecked = false;
        }
    }

    checkRPIBox.checked = allRPIChecked;
}

// verify if the 'check new' checkbox has to be checked
function changeOldStatus() {
    const checkOldBox = document.getElementById('checkFTP');
    const FTPCheckBoxes = [].slice.call(document.getElementsByClassName('FTP'));
    const USBCheckBoxes = [].slice.call(document.getElementsByClassName('USB'));
    const activeSystems = [].slice.call(document.getElementsByClassName('A'));
    const oldSystems = FTPCheckBoxes.concat(USBCheckBoxes);
    let allOldChecked = true;

    // iterate over all new checkboxes
    for (let i = 0; i < oldSystems.length && allOldChecked; i += 1) {
        if (!oldSystems[i].checked && activeSystems.includes(oldSystems[i])) {
            allOldChecked = false;
        }
    }

    // set correct value
    checkOldBox.checked = allOldChecked;
}

// checkbox entrypoint function
function changeCheckBox() {
    changeRPIStatus();
    changeOldStatus();
    changeAllStatus();
}

// convert datetime object to string yyyymmdd date
function yyyymmdd(date) {
    const mm = date.getMonth() + 1;
    const dd = date.getDate();

    return [
        date.getFullYear(),
        (mm > 9 ? '' : '0') + mm,
        (dd > 9 ? '' : '0') + dd,
    ].join('-');
}

// zoom in on the graph onclick
// basically, resubmit the form with the start and end date from the clicked item
function zoomGraph(d, i) {
    const startDateElement = document.getElementById('startDate');
    const endDateElement = document.getElementById('endDate');
    let diffDays = Math.abs(Date.parse(endDateElement.value) - Date.parse(startDateElement.value));
    diffDays = Math.ceil(diffDays / (1000 * 60 * 60 * 24));

    // change start and end date and resubmit the form
    if (diffDays > 14) {
        startDateElement.value = yyyymmdd(d[0]);
        endDateElement.value = yyyymmdd(d[2]);
        document.getElementById('submit').click();
    }
}

/**
 * Verify if the inputted dates are correct. If they aren't
 * set default values instead.
 */
function verifyDates() {
    let startDate = document.getElementById('startDate');
    let endDate = document.getElementById('endDate');

    // if the end date is smaller than the start date
    if (endDate.value <= startDate.value) {
        // set end date to today's date
        const today = new Date();
        endDate.value = yyyymmdd(today);

        // set start date to yesterday's date
        let yesterday = new Date(today);
        yesterday.setDate(yesterday.getDate() - 1);
        startDate.value = yyyymmdd(yesterday);
    }
}

/**
 * Function gets all the checked station checkboxes and returns it. If there
 * are none checked, the function will return false.
 *
 * @returns {boolean|*[]}
 */
function getSelectedCheckboxes() {
    const checkedCheckboxes = document.querySelectorAll('input[name=station]:checked');
    // check if there are any checked checkboxes
    if (!checkedCheckboxes.length) {
        alert("You must check at least one checkbox.");
        return false;
    }
    // store their values only
    let checkboxValues = [];
    checkedCheckboxes.forEach((checkbox) => {
        checkboxValues.push(checkbox.value);
    })
    return checkboxValues;
}

/**
 * Function gets all the availability info via an api to the
 * sites back-end. It then (re)creates the availability graph.
 */
function getAvailability() {
    document.getElementById('spinner').style.display = 'inline';
    // check which checkboxes are checked
    const checkboxValues = getSelectedCheckboxes();
    if (!checkboxValues) {
        document.getElementById('spinner').style.display = 'none';
        return;
    }
    const token = $('#token').attr('name');
    // verify the inputted dates
    verifyDates();

    // request availability date from back-end
    $.ajax({
        type: 'GET',
        url: `
            /index.php?option=com_bramsdata&view=availability&task=getAvailability&format=json&${token}=1
        `,
        data: {
            ids: checkboxValues,
            start: document.getElementById('startDate').value,
            end: document.getElementById('endDate').value,
        },
        success: (response) => {
            if (chart) {
                chart.updateGraph(options, response.data);
            } else {
                chart = visavail.generate(options, response.data);
            }
            document.getElementById('spinner').style.display = 'none';
        },
        error: (response) => {
            // on fail, show an error message
            document.getElementById('error').innerHTML = (
                'API call failed, please read the \'log\' variable in ' +
                'developer console for more information about the problem.'
            );
            // store the server response in the log variable
            log = response;
            document.getElementById('spinner').style.display = 'none';
        },
    });
}
