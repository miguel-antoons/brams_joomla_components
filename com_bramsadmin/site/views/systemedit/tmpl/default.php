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
            <button type='button' class='customBtn return' onclick='history.back()'>
                <i class="fa fa-arrow-left" aria-hidden="true"></i>
                Return
            </button>
            <h1><?php echo $this->title; ?></h1>
            <p id='error'>

            </p>
            <div id='inputContainer'>
                <label class='form-label' for='systemName'>Name</label>
                <input
                    type='text'
                    class='form-control'
                    value='<?php echo $this->system_info[0]->name; ?>'
                    id='systemName'
                    required
                >

                <label for='systemLocation'>Location</label>
                <select name='locations' class='form-control' id='systemLocation' onChange='setAntenna()'>
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
                <input
                    class='form-control'
                    type='number'
                    value='<?php echo $this->antenna; ?>'
                    min='0'
                    id='systemAntenna'
                >

                <label for='systemStart'>Start</label>
                <input
                    class='form-control'
                    type='datetime-local'
                    value='<?php echo $this->date_to_show ?>'
                    id='systemStart'
                    required
                >

                <label for='systemComments'>Comments</label>
                <input
                    class='form-control'
                    type='text'
                    value='<?php echo $this->system_info[0]->comments; ?>'
                    id='systemComments'
                >

                <button
                    name='submit'
                    class='customBtn save'
                    id='submit'
                    onclick="formProcess(document.getElementById('inputContainer').children)"
                >
                    <i class="fa fa-floppy-o" aria-hidden="true"></i>
                    Submit
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    let currentId = false;
    const locationAntennas = {
        <?php foreach ($this->locations as $location) : ?>
            <?php echo $location->id; ?>: [
                <?php foreach ($location->antennas as $antenna) : ?>
                    <?php echo $antenna; ?>,
                <?php endforeach; ?>
            ],
        <?php endforeach; ?>
    };

    const systemNames = [
        <?php foreach ($this->system_names as $system_name) : ?>
            '<?php echo $system_name->name; ?>',
        <?php endforeach; ?>
    ];

    const defLocationAntenna = {
        location: <?php echo $this->location_id; ?>,
        antenna: <?php echo $this->antenna; ?>,
    };

    if (<?php echo $this->id; ?>) {
        currentId = <?php echo $this->id; ?>;
    }

    setAntenna();
</script>
