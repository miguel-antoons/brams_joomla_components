function checkRPIBoxes(source) {
  const RPICheckBoxes = [].slice.call(document.getElementsByClassName('SSH'));
  const activeSystems = [].slice.call(document.getElementsByClassName('A'));

  for (let i = 0; i < RPICheckBoxes.length; i += 1) {
    if (activeSystems.includes(RPICheckBoxes[i])) {
      RPICheckBoxes[i].checked = source.checked;
    }
  }
}

function checkFTPBoxes(source) {
  const FTPCheckBoxes = [].slice.call(document.getElementsByClassName('FTP'));
  const USBCheckBoxes = [].slice.call(document.getElementsByClassName('USB'));
  const oldSystems = FTPCheckBoxes.concat(USBCheckBoxes);
  const activeSystems = [].slice.call(document.getElementsByClassName('A'));

  for (let i = 0; i < oldSystems.length; i += 1) {
    if (activeSystems.includes(oldSystems[i])) {
      oldSystems[i].checked = source.checked;
    }
  }
}

function checkAllBoxes(source) {
  const allCheckboxes = document.querySelectorAll('input[type=\'checkbox\']');
  for (let i = 0; i < allCheckboxes.length; i += 1) {
    allCheckboxes[i].checked = source.checked;
  }
}

function changeAllStatus() {
  const checkAllBox = document.getElementById('checkAll');
  const systemCheckBoxes = document.getElementsByClassName('custom_checkbox');
  let allChecked = true;

  for (let i = 0; i < systemCheckBoxes.length && allChecked; i += 1) {
    if (!systemCheckBoxes[i].checked) {
      allChecked = false;
    }
  }

  if (allChecked) {
    checkAllBox.checked = true;
  } else {
    checkAllBox.checked = false;
  }
}

function changeRPIStatus() {
  const checkRPIBox = document.getElementById('checkRPI');
  const RPICheckBoxes = [].slice.call(document.getElementsByClassName('SSH'));
  const activeSystems = [].slice.call(document.getElementsByClassName('A'));
  let allRPIChecked = true;

  for (let i = 0; i < RPICheckBoxes.length && allRPIChecked; i += 1) {
    if (!RPICheckBoxes[i].checked && activeSystems.includes(RPICheckBoxes[i])) {
      allRPIChecked = false;
    }
  }

  if (allRPIChecked) {
    checkRPIBox.checked = true;
  } else {
    checkRPIBox.checked = false;
  }
}

function changeOldStatus() {
  const checkOldBox = document.getElementById('checkFTP');
  const FTPCheckBoxes = [].slice.call(document.getElementsByClassName('FTP'));
  const USBCheckBoxes = [].slice.call(document.getElementsByClassName('USB'));
  const activeSystems = [].slice.call(document.getElementsByClassName('A'));
  const oldSystems = FTPCheckBoxes.concat(USBCheckBoxes);
  let allOldChecked = true;

  for (let i = 0; i < oldSystems.length && allOldChecked; i += 1) {
    if (!oldSystems[i].checked && activeSystems.includes(oldSystems[i])) {
      allOldChecked = false;
    }
  }

  if (allOldChecked) {
    checkOldBox.checked = true;
  } else {
    checkOldBox.checked = false;
  }
}

function changeCheckBox() {
  changeAllStatus();
  changeRPIStatus();
  changeOldStatus();
}
