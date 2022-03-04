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
            <img 
                src='/ProjectDir/img/belgian_map.gif'
                id='belgian_map'
                alt='Belgian map with receiving stations' 
            />
            <map name='station_map' id='station_map'>

            </map>
        </div>
    </div>
</div>
<form action='' method='post' name='networkMapForm'>
    <!-- TODO : add date filed and 2 checkboxes (select active, select inactive) -->
</form>
<script>
    // function to dynamically set the stations on the image can 
    // be called either here or ont the image onload property

    let all_stations = [
        <?php foreach ($this->active_stations as $active) : ?>
            [
                "<?php echo $active->name; ?>",
                "<?php echo $active->country_code; ?>",
                "<?php echo $active->transfer_type; ?>",
                <?php echo $active->longitude; ?>,
                <?php echo $active->latitude; ?>,
                <?php echo $active->rate; ?>
            ],
        <?php endforeach; ?>
        <?php foreach ($this->inactive_stations as $inactive) : ?>
            [
                "<?php echo $inactive->name; ?>",
                "<?php echo $inactive->country_code; ?>",
                "<?php echo $inactive->transfer_type; ?>",
                <?php echo $inactive->longitude; ?>,
                <?php echo $inactive->latitude; ?>,
                <?php echo $inactive->rate; ?>
            ],
        <?php endforeach; ?>

    ]

    onMapLoad(all_stations);
</script>
