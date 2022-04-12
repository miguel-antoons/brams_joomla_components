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
                onclick='window.location.href="/index.php?option=com_bramsadmin&view=systems"'
            >
                <i class="fa fa-arrow-left" aria-hidden="true"></i>
                Return
            </button>
            <h1 id="title">Create New System</h1>
            <p id='error'>

            </p>
            <div id='inputContainer'>
                <label class='form-label required' id="name" for='systemName'>Name</label>
                <input
                    type='text'
                    class='form-control'
                    id='systemName'
                    maxlength="31"
                    required
                >

                <label class='form-label required' id="location" for='systemLocation'>Location</label>
                <select
                    name='locations'
                    class='form-control'
                    id='systemLocation'
                    onChange='setAntenna()'
                    required
                >
                    <!-- location options will come here -->
                </select>

                <label class='form-label required' id="antenna" for='systemAntenna'>Antenna</label>
                <input
                    class='form-control'
                    type='number'
                    value='0'
                    min='0'
                    max="999999"
                    id='systemAntenna'
                    required
                >

                <label class='form-label required' for='systemStart'>Start</label>
                <input
                    class='form-control'
                    type='datetime-local'
                    id='systemStart'
                    required
                >

                <label for='systemComments'>Comments</label>
                <input
                    class='form-control'
                    type='text'
                    id='systemComments'
                    maxlength='255'
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
