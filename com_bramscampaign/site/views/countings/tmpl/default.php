<?php
/**
 * @author      Antoons Miguel
 * @package     Joomla.Administrator
 * @subpackage  com_bramscampaign
 *
 * ! NOTE that this file and its content will probably be deleted in the near future
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
?>

<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Download Spectrograms</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Choose whether to download the original spectrograms or the spectrograms with the highlighted
                meteors.
            </div>
            <div class="modal-footer">
                <button id="downloadAnnotated" type="button" class="customBtn down1" data-dismiss="modal">
                    <i class="fa fa-download" aria-hidden="true"></i> Download Spectrogram
                </button>
                <button id="downloadCsv" type="button" class="customBtn down2" data-dismiss="modal">
                    <i class="fa fa-download" aria-hidden="true"></i> Download CSV
                </button>
            </div>
        </div>
    </div>
</div>

<div id="DOMContainer" class="container custom_container container_margin">
    <?php echo '<input id="token" type="hidden" name="' . JSession::getFormToken() . '" value="1" />'; ?>
    <div class='row'>
        <div class='col custom_col'>
            <p id='message'>

            </p>
            <h1>Countings</h1>
            <p>
                Click one the button in the right column to add meteors to the counting.
            </p>
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
                    <th class='headerCol' onclick="sortTable(this, 'station')">
                        Station
                    </th>
                    <th class='headerCol' onclick="sortTable(this, 'start')">
                        Start
                    </th>
                    <th class='headerCol' onclick="sortTable(this, 'end')">
                        End
                    </th>
                    <th class='headerCol' onclick="sortTable(this, 'hasParticipated')">
                        Actions
                    </th>
                </tr>
                </thead>
                <tbody id='campaigns'>

                </tbody>
            </table>
        </div>
    </div>
</div>
