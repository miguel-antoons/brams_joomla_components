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
            <h1>Observers</h1>
            <p>
                Click on one of the buttons on the right side column to edit or delete the Observer.
            </p>
        </div>
    </div>
    <div class='row'>
        <div class='col custom_col'>
            <button
                type='button'
                class='customBtn new'
                onclick="window.location.href='/index.php?option=com_bramsadmin&view=observerEdit&id=';"
            >
                <i class="fa fa-plus-square-o" aria-hidden="true"></i>
                New Observer
            </button>
        </div>
    </div>
    <div class='row'>
        <div class='col'>
            <table class='table'>
                <thead>
                <tr>
                    <th class='headerCol' onclick="sortTable(this, 'code')">
                        Observer Code <i id='sortIcon' class="fa fa-sort" aria-hidden="true"></i>
                    </th>
                    <th class='headerCol' onclick="sortTable(this, 'first_name')">
                        First Name
                    </th>
                    <th class='headerCol' onclick="sortTable(this, 'last_name')">
                        Last Name
                    </th>
                    <th class='headerCol' onclick="sortTable(this, 'email')">
                        E-mail
                    </th>
                    <th class='headerCol'>
                        Actions
                    </th>
                </tr>
                </thead>
                <tbody id='observers'>

                </tbody>
            </table>
        </div>
    </div>
</div>
