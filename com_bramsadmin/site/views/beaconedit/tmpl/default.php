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
                onclick='window.location.href="/index.php?option=com_bramsadmin&view=beacons"'
            >
                <i class="fa fa-arrow-left" aria-hidden="true"></i>
                Return
            </button>
            <h1 id="title">Create New Beacon</h1>
            <p id='error'>

            </p>
            <div id='inputContainer'>
                <label id="code" class='form-label required' for='beaconCode'>Code</label>
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
                        id="beaconCode"
                        maxlength="31"
                        readonly
                        required
                    >
                </div>

                <label class='form-label required' for='beaconName'>Name</label>
                <input
                    type='text'
                    class='form-control'
                    id='beaconName'
                    oninput="setCode()"
                    maxlength="31"
                    required
                >

                <label for='beaconCountry' class="required">Country</label>
                <select
                    name='beacons'
                    class='form-control'
                    id='beaconCountry'
                    onchange='setCode()'
                    required
                >
                    <!-- country options will all come here -->
                </select>

                <label id="latitude" for='beaconLatitude' class="required">Latitude</label>
                <input
                    class='form-control'
                    type='number'
                    value='0'
                    id='beaconLatitude'
                    required
                >

                <label id="longitude" for='beaconLongitude' class="required">Longitude</label>
                <input
                    class='form-control'
                    type="number"
                    value='0'
                    id='beaconLongitude'
                    name="beaconLongitude"
                    required
                />

                <label id="frequency" for='beaconFrequency' class="required">Frequency (MHz)</label>
                <input
                    class='form-control'
                    type='number'
                    id='beaconFrequency'
                    max="9999"
                    value="0"
                    min="0"
                    required
                >

                <label id="power" for='beaconPower' class="required">Power (W)</label>
                <input
                    class='form-control'
                    type='number'
                    id='beaconPower'
                    max="999999"
                    value="0"
                    min="0"
                    required
                >

                <label for='beaconPolarization' class="required">Polarization</label>
                <input
                        class='form-control'
                        type='text'
                        id='beaconPolarization'
                        maxlength="15"
                        required
                >

                <!-- ? uncomment the following lines to add a comments field -->
<!--                <label for='beaconComments'>Comments</label>-->
<!--                <input-->
<!--                    class='form-control'-->
<!--                    type='text'-->
<!--                    id='beaconComments'-->
<!--                >-->

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
