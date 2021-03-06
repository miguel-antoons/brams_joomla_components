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
                <label id="code" class='form-label required' for='locationCode'>Code</label>
                <div id="codeWrapper" class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text" id="validationTooltipUsernamePrepend">
                            <label id="codeStatusL">
                                <input id="codeStatus" type="checkbox" onchange="setCodeStatus(this)">
                            </label>
                        </span>
                    </div>
                    <input
                        type="text"
                        class="form-control"
                        id="locationCode"
                        maxlength="31"
                        readonly
                        required
                    >
                </div>

                <label class='form-label required' for='locationName'>Name</label>
                <input
                        type='text'
                        class='form-control'
                        id='locationName'
                        oninput="setCode()"
                        maxlength="31"
                        required
                >

                <label for='locationStatus' class="required">Status</label>
                <select
                    name='locations'
                    class='form-control'
                    id='locationStatus'
                    required
                >
                    <option value="A" selected>Active</option>
                    <option value="D">Inactive</option>
                </select>

                <label for='locationCountry' class="required">Country</label>
                <select
                    name='locations'
                    class='form-control'
                    id='locationCountry'
                    onchange='setCode()'
                    required
                >
                    <!-- country options will all come here -->
                </select>

                <label id="latitude" for='locationLatitude' class="required">Latitude</label>
                <input
                    class='form-control'
                    type='number'
                    value='0'
                    id='locationLatitude'
                    required
                >

                <label id="longitude" for='locationLongitude' class="required">Longitude</label>
                <input
                    class='form-control'
                    type="number"
                    value='0'
                    id='locationLongitude'
                    name="locationLongitude"
                    required
                />

                <label for='locationTransferType'  class="required">Transfer Type</label>
                <select
                    name='locations'
                    class='form-control'
                    id='locationTransferType'
                    required
                >
                    <option value="SSH" selected>SSH</option>
                    <option value="USB">USB</option>
                    <option value="FTP">FTP</option>
                </select>

                <label for='locationObserver'  class="required">Observer</label>
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
                    maxlength="255"
                >

                <label for='locationFTPPass'>FTP Password</label>
                <input
                    class='form-control'
                    type='text'
                    id='locationFTPPass'
                    maxlength="20"
                >

                <label for='locationTVId'>Teamviewer ID</label>
                <input
                    class='form-control'
                    type='text'
                    id='locationTVId'
                    maxlength="20"
                >

                <label for='locationTVPass'>Teamviewer Password</label>
                <input
                    class='form-control'
                    type='text'
                    id='locationTVPass'
                    maxlength="20"
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
