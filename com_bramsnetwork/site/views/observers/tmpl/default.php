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
    <?php echo '<input id="token" type="hidden" name="' . JSession::getFormToken() . '" value="1" />'; ?>
    <div class='row'>
        <div class='col'>
            <h1>Observers</h1>
            <p>
                Below is the list of people participating in the BRAMS network.
            </p>
        </div>
    </div>
    <div class='row'>
        <div class='col'>
            <table class='table'>
                <thead>
                    <tr>
                        <th class='headerCol width25' id='sortFirstName' onclick='sortFirstName(this)'>
                            First name <i id='sortIcon' class="fa fa-sort" aria-hidden="true"></i>
                        </th>
                        <th class='headerCol width25' id='sortLastName' onclick='sortLastName(this)'>
                            Last name </th>
                        <th class='headerCol width50' id='sortLocations' onclick='sortLocations(this)'>
                            Stations </th>
                    </tr>
                </thead>
                <tbody id='observers'>
                    <!-- observer information comes here after page load -->
                </tbody>
            </table>
        </div>
    </div>
</div>
