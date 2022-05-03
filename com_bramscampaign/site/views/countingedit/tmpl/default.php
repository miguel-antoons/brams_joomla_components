<?php
/**
 * @author      Antoons Miguel
 * @package     Joomla.Administrator
 * @subpackage  com_bramscampaign
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
?>
<div class="container custom_container container_margin">
    <?php echo '<input id="token" type="hidden" name="' . JSession::getFormToken() . '" value="1" />'; ?>
    <div class="row">
        <div class="col custom_col">
            <button
                type='button'
                class='customBtn return'
                onclick='window.location.href="/index.php?option=com_bramscampaign&view=countings"'
            >
                <i class="fa fa-arrow-left" aria-hidden="true"></i>
                Return
            </button>
        </div>
        <h1 id="title"></h1>
    </div>

    <div class="row">
        <div class="col">
            <button
                type="button"
                name="previous"
                class="customBtn navigation"
                id="previous"
                onclick="goTo(true, undefined)"
            >
                <i class="fa fa-chevron-circle-left" aria-hidden="true"></i>
                Previous
            </button>
        </div>
        <div class="col">
            <label class="form-label required" for="spectrogramNames">Spectrograms</label>
            <select
                name="spectrogram"
                class="form-control"
                id="spectrogramNames"
                onchange="goTo(false, this.value)"
            >
                <!-- different spectrograms of the counting come here -->
            </select>
        </div>
        <div class="col">
            <button
                type="button"
                name="next"
                class="customBtn navigation"
                id="next"
                onclick="goTo(false, undefined)"
            >
                Next
                <i class="fa fa-chevron-circle-right" aria-hidden="true"></i>
            </button>
        </div>
    </div>
</div>

<div id="mc_content">
    <div id="mc_container">
        <canvas id="mc_counting">
            <p>You need a browser with HTML5 support to see this page.</p>
        </canvas>

        <canvas id="mc_background">
            <p>You need a browser with HTML5 support to see this page.</p>
        </canvas>

        <canvas id="mc_canvas">
            <p>You need a browser with HTML5 support to see this page.</p>
        </canvas>
    </div>
</div>
