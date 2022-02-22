<?php
/**
 * @author      Antoons Miguel
 * @package     Joomla.Administrator
 * @subpackage  com_bramsdata
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
?>

<input type='checkbox' onClick='checkAllBoxes(this)' id='checkAll' name='checkAll' />
<label for='checkAll'>Check All</label>

<input type='checkbox' onClick='checkRPIBoxes(this)' id='checkRPI' name='checkRPI' />
<label for='checkRPI'>Check New</label>

<input type='checkbox' onClick='checkFTPBoxes(this)' id='checkFTP' name='checkFTP' />
<label for='checkFTP'>Check Old</label>

<form action='' method='get'>
    <?php foreach ($this->stations as $station) : ?>
        <input 
            type='checkbox' 
            onClick='changeCheckBox()' 
            class='custom_checkbox <?php $station->transfer_type ?> <?php $station->status ?>'
            name='station[]'
            value='<?php $station->id ?>'
            <?php $station->checked ?>
        />
        <label for='station<?php $station->id ?>'><?php $station->name ?></label>
    <?php endforeach; ?>

    <label for='startDate'>From </label>
    <input type='date' name='startDate' min='2011-01-01' max='<?php $this->today ?>' value='<?php $this->start_date ?>' required/>

    <label for='endDate'>To </label>
    <input type='date' name='endDate' min='2011-01-01' max='<?php $this->today ?>' value='<?php $this->end_date ?>' required/>

    <input name='submit' type='submit' />
</form>
