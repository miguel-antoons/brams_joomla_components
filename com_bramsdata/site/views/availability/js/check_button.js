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
            /index.php?
            option=com_bramsdata
            &view=availability
            &task=getAvailability
            &format=json
            &${token}=1
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
