/* global $ */
/* global currentId */
function newSystem(form) {
    $.ajax({
        type: 'POST',
        url: '/index.php?option=com_bramsadmin&view=systemedit&task=newsystem&format=json',
        data: {
            newSystemInfo: {
                name: form.systemName.value,
                location: form.systemLocation.value,
                antenna: form.systemAntenna.value,
                start: form.systemStart.value,
                comments: form.systemComments.value,
            },
        },
        success() {
            window.location.href = '/index.php?option=com_bramsadmin&view=systems';
        },
        error() {
            console.log('api call failed');
        },
    });

    return false;
}

function updateSystem(form) {
    const formT = form + 1;
    return formT;
}

function formProcess(form) {
    if (currentId) {
        return updateSystem(form);
    }

    return newSystem(form);
}
