function checkRPIBoxes(source) {
    let RPICheckBoxes = [].slice.call(document.getElementsByClassName('SSH'));
    let activeSystems = [].slice.call(document.getElementsByClassName('A'));

    for (let i = 0 ; i < RPICheckBoxes.length ; i++) {
        if (activeSystems.includes(RPICheckBoxes[i])) {
            RPICheckBoxes[i].checked = source.checked;
        }
    }
}

function checkFTPBoxes(source) {
    let FTPCheckBoxes = [].slice.call(document.getElementsByClassName('FTP'));
    let USBCheckBoxes = [].slice.call(document.getElementsByClassName('USB'));
    let oldSystems = FTPCheckBoxes.concat(USBCheckBoxes);
    let activeSystems = [].slice.call(document.getElementsByClassName('A'));
    console.log(FTPCheckBoxes);

    for (let i = 0 ; i < oldSystems.length ; i++) {
        if (activeSystems.includes(oldSystems[i])) {
            oldSystems[i].checked = source.checked;
        }
    }
}

function checkAllBoxes(source) {
    let allCheckboxes = document.querySelectorAll('input[type=\'checkbox\']');
    for (let i = 0 ; i < allCheckboxes.length ; i++) {
        allCheckboxes[i].checked = source.checked;
    }
}

function changeCheckBox() {
    changeAllStatus();
    changeRPIStatus();
    changeOldStatus();
}

function changeAllStatus() {
    let checkAllBox = document.getElementById('checkAll');
    let systemCheckBoxes = document.getElementsByClassName('custom_checkbox');
    let allChecked = true;

    for (let i = 0 ; i < systemCheckBoxes.length && allChecked ; i++) {
            if (!systemCheckBoxes[i].checked) {
                allChecked = false;
            }
    }

    if (allChecked) {
        checkAllBox.checked = true;
    }
    else {
        checkAllBox.checked = false;
    }
}

function changeRPIStatus() {
    let checkRPIBox = document.getElementById('checkRPI');
    let RPICheckBoxes = [].slice.call(document.getElementsByClassName('SSH'));
    let activeSystems = [].slice.call(document.getElementsByClassName('A'));
    let allRPIChecked = true;

    for (let i = 0 ; i < RPICheckBoxes.length && allRPIChecked ; i++) {
        if (!RPICheckBoxes[i].checked && activeSystems.includes(RPICheckBoxes[i])) {
            allRPIChecked = false;
        }
    }

    if (allRPIChecked) {
        checkRPIBox.checked = true;
    }
    else {
        checkRPIBox.checked = false;
    }
}

function changeOldStatus() {
    let checkOldBox = document.getElementById('checkFTP');
    let FTPCheckBoxes = [].slice.call(document.getElementsByClassName('FTP'));
    let USBCheckBoxes = [].slice.call(document.getElementsByClassName('USB'));
    let activeSystems = [].slice.call(document.getElementsByClassName('A'));
    let oldSystems = FTPCheckBoxes.concat(USBCheckBoxes);
    let allOldChecked = true;

    for (let i = 0 ; i < oldSystems.length && allOldChecked ; i++) {
        if (!oldSystems[i].checked && activeSystems.includes(oldSystems[i])) {
            allOldChecked = false;
        }
    }

    if (allOldChecked) {
        checkOldBox.checked = true;
    }
    else {
        checkOldBox.checked = false;
    }
}
