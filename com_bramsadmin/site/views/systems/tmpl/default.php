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
    <div class='row'>
        <div class='col custom_col'>
            <h1>Systems</h1>
            <p>
                Click on one of the buttons in the right side column to edit or delete the system.
            </p>
        </div>
    </div>
    <div class='row'>
        <div class='col custom_col'>
            <button
                type='button'
                onclick="window.location.href='index.php?option=com_bramsadmin&view=systemedit&id=';"
            >
                New System
            </button>
        </div>
    </div>
    <div class='row'>
        <div class='col'>
            <table class='table'>
                <thead>
                    <tr>
                        <th class='headerCol' onClick='sortLocation(this)' width="20%">
                            Location Code <i id='sortIcon' class="fa fa-sort" aria-hidden="true"></i>
                        </th>
                        <th class='headerCol' onClick='sortName(this)' width="20%">
                            System Name 
                        </th>
                        <th class='headerCol' onClick='sortStart(this)' width="20%">
                            Start 
                        </th>
                        <th class='headerCol' onClick='sortEnd(this)' width="20%">
                            End 
                        </th>
                        <th class='headerCol' width="20%">
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

<script>
    let systems = [
        <?php foreach($this->systems as $system) : ?>
            [
                <?php echo $system->id; ?>,
                '<?php echo $system->code; ?>',
                '<?php echo $system->name; ?>',
                '<?php echo $system->start; ?>',
                '<?php echo $system->end; ?>'
            ],
        <?php endforeach; ?>
    ];

    onPageLoad();
</script>
