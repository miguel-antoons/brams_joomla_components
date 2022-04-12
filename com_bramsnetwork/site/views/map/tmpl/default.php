<?php
/**
 * @author      Antoons Miguel
 * @package     Joomla.Administrator
 * @subpackage  com_bramsnetwork
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
?>

<div class='container'>
    <?php echo '<input id="token" type="hidden" name="' . JSession::getFormToken() . '" value="1" />'; ?>
    <div class='row'>
        <div class='col'>
            <p>
                This is the geographical distribution of the stations of the BRAMS network.
                The green spots represent the recently updated receiving stations, the red
                spots represent the outdated stations while the triangle represents the beacon
                at Dourbes. You can hover over the spots to know the names of the stations.
            </p>
            <p>
                Change the date to see how the receiving stations have evolved over time. You can
                also choose to hide the inactive or active station for each date.
            </p>
        </div>
    </div>

    <div class='row'>
        <div class='col'>
            <h2>Network Map</h2>
        </div>
    </div>

    <div id="form">
        <div class='row'>
            <div class='col'>
                <label class='dateLabel' for="startDate">Date </label>
                <input
                    type="date"
                    name="startDate"
                    id="startDate"
                    min="2010-01-01"
                    max='<?php echo $this->today ?>'
                    value='<?php echo $this->today ?>'
                    required
                />
                <div id="buttonContainer">
                    <button
                        type='submit'
                        id='submit'
                        class='customBtn submit'
                        onclick="getStations()"
                    >
                        <i class="fa fa-check-square" aria-hidden="true"></i>
                        Submit
                    </button>
                    <span id="spinner" class="spinner-border text-success"></span>
            </div>
            </div>
            <div class='col'>
                <input
                    type='checkbox'
                    onClick='showStationsEntry()'
                    class='custom_checkbox'
                    name='checkbox[]'
                    value='active'
                    id='showActive'
                    checked
                />
                <label class='checkbox_label' for='showActive'>
                    Show Active
                </label>
                <br>
                <input
                    type='checkbox'
                    onClick='showStationsEntry()'
                    class='custom_checkbox'
                    name='checkbox[]'
                    value='inactive'
                    id='showInactive'
                />
                <label class='checkbox_label' for='showInactive'>
                    Show Inactive
                </label>
            </div>
            <div class='col'>
                <input
                    type='checkbox'
                    onClick='showStationsEntry()'
                    class='custom_checkbox'
                    name='checkbox[]'
                    value='new'
                    id='showNew'
                    checked
                />
                <label class='checkbox_label' for='showNew'>
                    Show New
                </label>
                <br>
                <input
                    type='checkbox'
                    onClick='showStationsEntry()'
                    class='custom_checkbox'
                    name='checkbox[]'
                    value='old'
                    id='showOld'
                    checked
                />
                <label class='checkbox_label' for='showOld'>
                    Show Old
                </label>
            </div>
        </div>
    </div>

    <div class='row'>
        <div class='col-8'>
            <img
                src='/ProjectDir/img/belgian_map.gif'
                id='belgian_map'
                alt='Belgian map with receiving stations'
                usemap='#station_map'
                class='map'
                width='593'
                height='516'
            />
            <map name='station_map' id='station_map'>
                <!-- station points on map comes here after page load -->
            </map>
        </div>
        <div class='col-md-auto'>
            <h4>Station Info</h4>
            <p>
                Station Name<br>
                <span class='stationInfo' id='stationName'></span><br>
                Station Country Code<br>
                <span class='stationInfo' id='stationCountry'></span><br>
                File Availability on <span id='selectedDate'></span><br>
                <span class='stationInfo' id='stationRate'></span><br>
            </p>
        </div>
    </div>
</div>
