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
            <h1>Hello World !</h1>

            <form onsubmit='updateSystem()' method='put' name='updateSystem'>
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

                <label for='systemAntenna'>Name</label>
                <input type='number' value='<?php echo $this->system_info[0]->antenna; ?>' id='systemAntenna'>

                <label for='systemStart'>Start</label>
                <input type='datetime-local' value='<?php echo $this->now ?>' id='systemStart'>

                <label for='systemComments'>Name</label>
                <input type='text' value='<?php echo $this->system_info[0]->comments; ?>' id='systemComments'>

                <input name='submit' type='submit' id='submit'/>
            </form>
            <p><?php echo $this->id ?></p>
        </div>
    </div>
</div>

<script>
    
</script>
