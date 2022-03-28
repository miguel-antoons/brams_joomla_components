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
            <h1>Systems</h1>
            <p>
                Click on one of the buttons on the right side column to edit or delete the system.
            </p>
        </div>
    </div>
    <div class='row'>
        <div class='col custom_col'>
            <button
                type='button'
                class='customBtn new'
                onclick="window.location.href='/index.php?option=com_bramsadmin&view=systemedit&id=';"
            >
                <i class="fa fa-plus-square-o" aria-hidden="true"></i>
                New System
            </button>
        </div>
    </div>
    <div class='row'>
        <div class='col'>
            <table class='table'>
                <thead>
                    <tr>
                        <th class='headerCol' onclick="sortTable(this, 'code')">
                            Location Code <i id='sortIcon' class="fa fa-sort" aria-hidden="true"></i>
                        </th>
                        <th class='headerCol' onclick="sortTable(this, 'name')">
                            System Name
                        </th>
                        <th class='headerCol' onclick="sortTable(this, 'start')">
                            Start
                        </th>
                        <th class='headerCol' onclick="sortTable(this, 'end')">
                            End
                        </th>
                        <th class='headerCol'>
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody id='systems'>

                </tbody>
            </table>
        </div>
    </div>
</div>
