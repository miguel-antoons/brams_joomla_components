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
                onclick='window.location.href="/index.php?option=com_bramsadmin&view=antennas"'
            >
                <i class="fa fa-arrow-left" aria-hidden="true"></i>
                Return
            </button>
            <h1 id="title">Create New Antenna</h1>
            <p id='error'>

            </p>
            <div id='inputContainer'>
                <label id="code" class='form-label required' for='antennaCode'>Antenna Code</label>
                <input
                    type="text"
                    class="form-control"
                    id="antennaCode"
                    maxlength="31"
                    pattern=""
                    required
                >

                <label class='form-label' for='antennaBrand'>Brand</label>
                <input
                    type='text'
                    class='form-control'
                    id='antennaBrand'
                    required
                >

                <label id="latitude" for='antennaModel' class="form-label">Model</label>
                <input
                    class='form-control'
                    type='text'
                    id='antennaModel'
                    required
                >

                <label for='antennaComments'>Comments</label>
                <input
                    class='form-control'
                    type='text'
                    id='antennaComments'
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
