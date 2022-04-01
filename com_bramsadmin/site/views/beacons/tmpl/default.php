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
            <p id='message' class='<?php echo $this->message['css_class']; ?>'>
                <?php echo $this->message['message']; ?>
            </p>
            <h1>Beacons</h1>
            <p>
                Click on one of the buttons on the right side column to edit or delete the beacon.
            </p>
        </div>
    </div>
    <div class='row'>
        <div class='col custom_col'>
            <button
                type='button'
                class='customBtn new'
                onclick="window.location.href='/index.php?option=com_bramsadmin&view=beaconEdit&id=';"
            >
                <i class="fa fa-plus-square-o" aria-hidden="true"></i>
                New Beacon
            </button>
        </div>
    </div>
    <div class='row'>
        <div class='col'>
            <table class='table'>
                <thead>
                <tr>
                    <th class='headerCol' onclick="sortTable(this, 'name')">
                        Name <i id='sortIcon' class="fa fa-sort" aria-hidden="true"></i>
                    </th>
                    <th class='headerCol' onclick="sortTable(this, 'latitude')">
                        Latitude
                    </th>
                    <th class='headerCol' onclick="sortTable(this, 'longitude')">
                        Longitude
                    </th>
                    <th class='headerCol' onclick="sortTable(this, 'frequency')">
                        Frequency
                    </th>
                    <th class='headerCol' onclick="sortTable(this, 'power')">
                        Power
                    </th>
                    <th class='headerCol actions'>
                        Actions
                    </th>
                </tr>
                </thead>
                <tbody id='beacons'>

                </tbody>
            </table>
        </div>
    </div>
</div>
