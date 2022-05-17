<?php
/**
 * @author      Antoons Miguel
 * @package     Joomla.Administrator
 * @subpackage  com_bramsadmin
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
?>
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div id="modal-body" class="modal-body">

            </div>
            <div class="modal-footer">
                <button id="delete" type="button" class="customBtn down2" data-dismiss="modal">
                    <i class="fa fa-check-square" aria-hidden="true"></i> Yes
                </button>
                <button id="exitButton" type="button" class="customBtn down1" data-dismiss="modal">
                    <i class="fa fa-times-circle" aria-hidden="true"></i> No
                </button>
            </div>
        </div>
    </div>
</div>

<div class="container custom_container container_margin">
    <?php echo '<input id="token" type="hidden" name="' . JSession::getFormToken() . '" value="1" />'; ?>
    <div class='row'>
        <div class='col custom_col'>
            <p id='message' class='<?php echo $this->message['css_class']; ?>'>
                <?php echo $this->message['message']; ?>
            </p>
            <h1>Antennas</h1>
            <p>
                Click on one of the buttons on the right side column to edit or delete the antenna.
            </p>
        </div>
    </div>
    <div class='row'>
        <div class='col custom_col'>
            <button
                type='button'
                class='customBtn new'
                onclick="window.location.href='/index.php?option=com_bramsadmin&view=antennaEdit&id=';"
            >
                <i class="fa fa-plus-square-o" aria-hidden="true"></i>
                New Antenna
            </button>
        </div>
    </div>
    <div class='row'>
        <div class='col'>
            <table class='table'>
                <thead>
                <tr>
                    <th class='headerCol' onclick="sortTable(this, 'code')">
                        Antenna Code <i id='sortIcon' class="fa fa-sort" aria-hidden="true"></i>
                    </th>
                    <th class='headerCol' onclick="sortTable(this, 'brand')">
                        Brand
                    </th>
                    <th class='headerCol' onclick="sortTable(this, 'model')">
                        Model
                    </th>
                    <th class='headerCol'>
                        Actions
                    </th>
                </tr>
                </thead>
                <tbody id='antennas'>

                </tbody>
            </table>
        </div>
    </div>
</div>
