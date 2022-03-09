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
                        <th onClick='sortFirstName(this, 1)' scope='col'>First name <i id="sortIcon" class="fa-solid fa-sort"></i></th>
                        <th onClick='sortLastName(this, 0)' scope='col'>Last name </th>
                        <th onClick='sortLocations(this, 0)' scope='col'>Stations </th>
                    </tr>
                </thead>
                <tbody id='observers'>
                    <!-- observer information comes here after page load -->
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
    let observers = [
        <?php foreach($this->observer_info as $observer) : ?>
            [
                '<?php echo $observer->first_name ?>',
                '<?php echo $observer->last_name ?>',
                '<?php echo $observer->locations ?>',
            ],
        <?php endforeach; ?>
    ];
</script>
