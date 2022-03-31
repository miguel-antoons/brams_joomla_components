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
                onclick='window.location.href="/index.php?option=com_bramsadmin&view=observers"'
            >
                <i class="fa fa-arrow-left" aria-hidden="true"></i>
                Return
            </button>
            <h1 id="title">Create New Observer</h1>
            <p id='error'>

            </p>
            <div id='inputContainer'>
                <label id="code" class='form-label required' for='observerCode'>Observer Code</label>
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
                        id="observerCode"
                        maxlength="31"
                        readonly
                        required
                    >
                </div>

                <label class='form-label required' for='observerFName'>First Name</label>
                <input
                    type='text'
                    class='form-control'
                    id='observerFName'
                    maxlength="31"
                    oninput="setCode()"
                    required
                >

                <label class='form-label required' for='observerLName'>Last Name</label>
                <input
                    type='text'
                    class='form-control'
                    id='observerLName'
                    maxlength="31"
                    oninput="setCode()"
                    required
                >

                <label for='observerCountry' class="required">Country</label>
                <select
                    name='observers'
                    class='form-control'
                    id='observerCountry'
                    required
                >
                    <!-- country options will all come here -->
                </select>

                <label id="email" class='form-label required' for='observerEmail'>Email</label>
                <input
                        type='email'
                        class='form-control'
                        id='observerEmail'
                        maxlength="63"
                        required
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
