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

    <form action='' method='post' name='networkMapForm'>
        <div class='row'>
            <div class='col'>
                <label class='dateLabel for='startDate'>Date </label>
                <input
                    type='date'
                    name='startDate'
                    id='startDate'
                    min='2010-01-01'
                    max='<?php echo $this->today ?>'
                    value='<?php echo $this->today ?>'
                    required
                />
                <input name='submit' type='submit' id='submit' class='custom_btn'/>
            </div>
            <div class='col'>
                <input 
                    type='checkbox' 
                    onClick='showStationsEntry()' 
                    class='custom_checkbox'
                    name='checkbox[]'
                    value='<?php echo $this->active_checkbox_value ?>'
                    id='showActive'
                    <?php echo $this->checkbox[$this->active_checkbox_value] ?>
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
                    value='<?php echo $this->inactive_checkbox_value ?>'
                    id='showInactive'
                    <?php echo $this->checkbox[$this->inactive_checkbox_value] ?>
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
                    value='<?php echo $this->new_checkbox_value ?>'
                    id='showNew'
                    <?php echo $this->checkbox[$this->new_checkbox_value] ?>
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
                    value='<?php echo $this->old_checkbox_value ?>'
                    id='showOld'
                    <?php echo $this->checkbox[$this->old_checkbox_value] ?>
                />
                <label class='checkbox_label' for='showOld'>
                    Show Old
                </label>
            </div>
        </div>
    </form>

    <div class='row'>
        <div class='col'>
            <img 
                src='/ProjectDir/img/belgian_map.gif'
                id='belgian_map'
                alt='Belgian map with receiving stations' 
                usemap='#station_map'
                class='map'
                height='516'
                width='593'
            />
            <map name='station_map' id='station_map'>

            </map>
        </div>
        <div class='col'>
            <h4>Station Info</h4>
            <p>
                Station Name : <span class='stationInfo' id='stationName'></span><br>
                Station Country Code : <span class='stationInfo' id='stationCountry'></span><br>
                Station Transfer Type : <span class='stationInfo' id='stationTransfer'></span><br>
                File Availability on <span id='selectedDate'></span> : <span class='stationInfo' id='stationRate'></span><br>
            </p>
        </div>
    </div>
</div>
<script>
    // function to dynamically set the stations on the image can 
    // be called either here or ont the image onload property

    let allStations = [
        <?php foreach ($this->active_stations as $active) : ?>
            [
                "<?php echo $active->name; ?>",
                "<?php echo $active->country_code; ?>",
                "<?php echo $active->transfer_type; ?>",
                <?php echo $active->longitude; ?>,
                <?php echo $active->latitude; ?>,
                <?php echo $active->rate; ?>,
                false
            ],
        <?php endforeach; ?>
        <?php foreach ($this->inactive_stations as $inactive) : ?>
            [
                "<?php echo $inactive->name; ?>",
                "<?php echo $inactive->country_code; ?>",
                "<?php echo $inactive->transfer_type; ?>",
                <?php echo $inactive->longitude; ?>,
                <?php echo $inactive->latitude; ?>,
                <?php echo $inactive->rate; ?>,
                false
            ],
        <?php endforeach; ?>
        /*<?php /*foreach ($this->beacons as $beacon) : ?>
            [
                "<?php echo $beacon->name; ?>",
                "<?php echo $beacon->country_code; ?>",
                "<?php echo $beacon->transfer_type; ?>",
                <?php echo $beacon->longitude; ?>,
                <?php echo $beacon->latitude; ?>,
                <?php echo $beacon->rate; ?>,
                true
            ],
        <?php endforeach; */ ?>
    ]*/

    onMapLoad();
</script>
