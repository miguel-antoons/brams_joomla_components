<?php
/**
 * @author      Antoons Miguel
 * @package     Joomla.Administrator
 * @subpackage  com_bramsdata
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
?>
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Note</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Make sure to select at least one station before continuing.
            </div>
            <div class="modal-footer">
                <button id="downloadCsv" type="button" class="customBtn down2" data-dismiss="modal">
                    <i class="fa fa-check-square" aria-hidden="true"></i> Ok
                </button>
            </div>
        </div>
    </div>
</div>

<p id="error">

</p>
<div class="container custom_container container_margin menu">
    <?php echo '<input id="token" type="hidden" name="' . JSession::getFormToken() . '" value="1" />'; ?>
    <div class='row'>
        <div class='col custom_col'>
            <input type='checkbox' onclick="checkAllBoxes(this)" id='checkAll' name='checkAll' />
            <label class='master_checkbox' for='checkAll'>Check All</label>
        </div>
        <div class='col custom_col'>
            <input type='checkbox' onclick='checkRPIBoxes(this)' id='checkRPI' name='checkRPI' />
            <label class='master_checkbox' for='checkRPI'>RSP 2</label>
        </div>
        <div class='col custom_col'>
            <input type='checkbox' onclick='checkFTPBoxes(this)' id='checkFTP' name='checkFTP' />
            <label class='master_checkbox' for='checkFTP'>ICOM</label>
        </div>
        <div class='col custom_col'></div>
        <div class='col custom_col'></div>
    </div>
</div>

<div id="form">
    <div class="container custom_container menu">
        <div class='row'>
            <div class='col custom_col'>
                <?php $index = 0 ?>
                <?php foreach ($this->stations as $station) : ?>
                    <?php
                    if(!($index % $this->column_length) && $index) { echo "</div><div class='col custom_col'>"; }
                    $index++;
                    ?>
                    <input
                        type='checkbox'
                        onclick='changeCheckBox()'
                        class='custom_checkbox <?php echo $station->transfer_type ?> <?php echo $station->status ?>'
                        name='station'
                        value='<?php echo $station->location_code . '_' . $station->alias ?>'
                        id='station<?php echo $station->id ?>'
                        <?php echo $station->checked ?>
                    />
                    <label class='checkbox_label' for='station<?php echo $station->id ?>'>
                        <?php echo $station->name ?>
                    </label>
                    <br>
                <?php endforeach; ?>
            </div>
        </div>
        <div class='row customRow'>
            <div class="col-5 custom_col">
                <div class="float-left w-50">
                    <label for="fMin" class="form-label">fmin</label>
                    <input
                        type='number'
                        name='fMin'
                        id='fMin'
                        class="finput form-control"
                        min='0'
                        max='2756'
                    />
                </div>
                <div class="float-right w-50">
                    <label for="fMax" class="form-label">fmax</label>
                    <input
                        type='number'
                        name='fMax'
                        id='fMax'
                        class="finput form-control"
                        min='0'
                        max='2756'
                    />
                </div>
            </div>
            <div class='col-5 custom_col'>
                <label for='startDate'>Date & Time</label>
                <input
                    type='datetime-local'
                    name='startDate'
                    id='startDate'
                    class="form-control"
                    min='2011-01-01T00:00'
                    max='<?php echo $this->today ?>'
                    required
                />
            </div>
            <div class="col-2 custom_col">
                <div id="buttonContainer">
                    <button
                        name='submit'
                        class='customBtn save'
                        id='submit'
                        onclick="showSpectrograms()"
                    >
                        <i class="fa fa-check-square" aria-hidden="true"></i>
                        Submit
                    </button>
                    <span id="spinner" class="spinner-border text-success"></span>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="spectrogramContainer" class="container">

</div>
