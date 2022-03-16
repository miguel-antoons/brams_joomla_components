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
            <h1>Edit System <?php echo $this->locations[0]->name; ?></h1>

            <form onsubmit='formProcess(this)' name='updateSystem'>
                <label for='systemName'>Name</label>
                <input type='text' value='<?php echo $this->system_info[0]->name; ?>' id='systemName'>

                <label for='systemLocation'>Location</label>
                <select name='locations' id='systemLocation'>
                    <?php foreach($this->locations as $location) : ?>
                        <option
                            value='<?php echo $location->id; ?>' 
                            <?php echo $location->selected; ?>
                        >
                            <?php echo $location->name; ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for='systemAntenna'>Antenna</label>
                <input type='number' value='<?php echo $this->system_info[0]->antenna; ?>' id='systemAntenna'>

                <label for='systemStart'>Start</label>
                <input type='datetime-local' value='<?php echo $this->date_to_show ?>' id='systemStart'>

                <label for='systemComments'>Comments</label>
                <input type='text' value='<?php echo $this->system_info[0]->comments; ?>' id='systemComments'>

                <input name='submit' type='submit' id='submit'/>
            </form>
        </div>
    </div>
</div>

<script>
    let currentId = <?php echo $this->id; ?>;
</script>
