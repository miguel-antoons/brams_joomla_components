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
    <p id="error"></p>
    <div id="mc_header">
        <div id="mc_left_icons">
            <a class="fancybox" id="mc_help_icon" href="#mc_help"><img src="/ProjectDir/img/help_icon.png" alt="" title="Show help."/></a>
            <div id="mc_help" style="display: none;">
                <p>How to count meteors:</p>
                <ul>
                    <li style="font-weight: bold;">Make sure the zoom level of your browser is 100&#37;.</li>
                    <li>Click and drag to draw a rectangle around a meteor. Try to draw the smallest possible rectangle.</li>
                    <li>Double-click inside one rectangle to remove it.</li>
                    <li>Press s or 1 to select/deselect short meteors.</li>
                    <li>Press l or 2 to select/deselect long meteors.</li>
                </ul>
                <p>Note: the minimum resolution to display this page is 1024x768.</p>
            </div>
        </div>
        <div id="mc_right_icons">
            <button type="button" onclick="selectMeteorType('S')">
                <img
                    id="mc_short"
                    src="/ProjectDir/img/short_icon.png"
                    alt="S"
                    title="Press s or 1 to select/deselect short meteors."
                />
            </button>
            <button type="button" onclick="selectMeteorType('L')">
                <img
                    id="mc_long"
                    src="/ProjectDir/img/long_icon.png"
                    alt="L"
                    title="Press l or 2 to select/deselect long meteors."
                />
            </button>
        </div>
        <div id="mc_title">

        </div>
    </div>

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
