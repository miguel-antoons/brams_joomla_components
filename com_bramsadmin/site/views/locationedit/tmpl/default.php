<?php
/**
 * @author      Antoons Miguel
 * @package     Joomla.Administrator
 * @subpackage  com_bramsadmin
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
?>

<div class="container custom_container container_margin">
    <?php echo '<input id="token" type="hidden" name="' . JSession::getFormToken() . '" value="1" />'; ?>
    <div class='row'>
        <div class='col custom_col'>
            <button
                type='button'
                class='customBtn return'
                onclick='window.location.href="/index.php?option=com_bramsadmin&view=locations"'
            >
                <i class="fa fa-arrow-left" aria-hidden="true"></i>
                Return
            </button>
            <h1 id="title">Create New Location</h1>
            <p id='error'>

            </p>
            <div id='inputContainer'>
                <label class='form-label' for='locationCode'>Code</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text" id="validationTooltipUsernamePrepend">
                            <label id="codeStatusL">
                                <input id="codeStatus" type="checkbox" onchange="setCodeStatus(this)">
                            </label>
                        </span>
                    </div>
                    <input type="text" class="form-control" id="locationCode" readonly required>
                </div>

                <label class='form-label' for='locationName'>Name</label>
                <input
                        type='text'
                        class='form-control'
                        id='locationName'
                        oninput="setCode()"
                        required
                >

                <label for='locationStatus'>Status</label>
                <select
                        name='locations'
                        class='form-control'
                        id='locationStatus'
                >
                    <option value="A" selected>Active</option>
                    <option value="D">Inactive</option>
                </select>

                <label for='locationCountry'>Country</label>
                <select
                    name='locations'
                    class='form-control'
                    id='locationCountry'
                    onchange='setCode()'
                >
                    <!-- country options will all come here -->
                </select>

                <label for='locationLatitude'>Latitude</label>
                <input
                    class='form-control'
                    type='number'
                    value='0'
                    min='-180'
                    max="180"
                    id='locationLatitude'
                    required
                >

                <label for='locationLongitude'>Longitude</label>
                <input
                    class='form-control'
                    type='number'
                    value='0'
                    min='-180'
                    max="180"
                    id='locationLongitude'
                    required
                >

                <label for='locationTransferType'>Transfer Type</label>
                <!-- ! change function below -->
                <select
                        name='locations'
                        class='form-control'
                        id='locationTransferType'
                >
                    <option value="SSH" selected>SSH</option>
                    <option value="USB">USB</option>
                    <option value="FTP">FTP</option>
                </select>

                <label for='locationObserver'>Observer</label>
                <!-- ! change function below -->
                <select
                        name='locations'
                        class='form-control'
                        id='locationObserver'
                >
                    <!-- observer options will all come here -->
                </select>

                <label for='locationComments'>Comments</label>
                <input
                    class='form-control'
                    type='text'
                    id='locationComments'
                >

                <label for='locationFTPPass'>FTP Password</label>
                <input
                        class='form-control'
                        type='text'
                        id='locationFTPPass'
                >

                <label for='locationTVId'>Teamviewer ID</label>
                <input
                        class='form-control'
                        type='text'
                        id='locationTVId'
                >

                <label for='locationTVPass'>Teamviewer Password</label>
                <input
                        class='form-control'
                        type='text'
                        id='locationTVPass'
                >

                <button
                    name='submit'
                    class='customBtn save'
                    id='submit'
                    onclick="formProcess(document.getElementById('inputContainer').children)"
                >
                    <i class="fa fa-floppy-o" aria-hidden="true"></i>
                    Save
                </button>
            </div>
        </div>
    </div>
</div>
