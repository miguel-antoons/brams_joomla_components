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

<form action='' method='post'>
    <?php foreach ($this->stations as $station) : ?>
        <input 
            type='checkbox' 
            onClick='changeCheckBox()' 
            class='custom_checkbox <?php echo $station->transfer_type ?> <?php echo $station->status ?>'
            name='station[]'
            value='<?php echo $station->id ?>'
            id='station<?php echo $station->id ?>'
            <?php echo $station->checked ?>
        />
        <label for='station<?php echo $station->id ?>'><?php echo $station->name ?></label>
    <?php endforeach; ?>

    <label for='startDate'>From </label>
    <input type='date' name='startDate' min='2011-01-01' max='<?php echo $this->today ?>' value='<?php echo $this->start_date ?>' required/>

    <label for='endDate'>To </label>
    <input type='date' name='endDate' min='2011-01-01' max='<?php echo $this->today ?>' value='<?php echo $this->end_date ?>' required/>

    <input name='submit' type='submit' />
</form>
<!-- debug paragraph, please remove or comment the below html tags once the product is finished -->
<p>
    <?php //print_r($this->availability) ?>
</p>
<div style="overflow: hidden;" class="visavail" id="visavail_container">
    <p id="visavail_graph">
        <!-- Visavail.js chart will be placed here -->
    </p>
</div>
<script>
    let dataset = [
        <?php foreach ($this->selected_stations as $station) : ?>
            {
                "measure": "<?php echo $station ?>",
                "interval_s": <?php echo $this->interval ?>,
                "categories": {
                    "0%": {class: "rect_red", tooltip_html: '<i class="fas fa-fw fa-exclamation-circle tooltip_red"></i>' },
                    "100%": {class: "rect_green", tooltip_html: '<i class="fas fa-fw fa-exclamation-circle tooltip_green"></i>' },
                    "0.1 - 20%": {class: "rect_red1", tooltip_html: '<i class="fas fa-fw fa-exclamation-circle tooltip_red1"></i>' },
                    "20.1 - 40%": {class: "rect_red2", tooltip_html: '<i class="fas fa-fw fa-exclamation-circle tooltip_red2"></i>' },
                    "40.1 - 60%": {class: "rect_yellow", tooltip_html: '<i class="fas fa-fw fa-exclamation-circle tooltip_yellow"></i>' },
                    "60.1 - 80%": {class: "rect_green2", tooltip_html: '<i class="fas fa-fw fa-exclamation-circle tooltip_green2"></i>' },
                    "80.1 - 99.9%": {class: "rect_green1", tooltip_html: '<i class="fas fa-fw fa-exclamation-circle tooltip_green1"></i>' },
                },
                "data": [
                    <?php for ($index = 0 ; $index < count($this->availability[$station]) - 1 ; $index++) : ?>
                        [
                            "<?php echo $this->availability[$station][$index]->start ?>", 
                            "<?php echo $this->availability[$station][$index]->available ?>", 
                            "<?php 
                                $end_time = new DateTime($this->availability[$station][$index + 1]->start);
                                echo $end_time->format('Y-m-d H:i:s');
                            ?>"
                        ],
                    <?php endfor; ?>
                ]
            },
        <?php endforeach; ?>
    ];

    let customized_categories = false;

    if (<?php echo $this->custom_categories ?>) {
        customized_categories = true;
    }

    let options = {
        id_div_container: "visavail_container",
        id_div_graph: "visavail_graph",
        responsive: {
            enabled: true,
        },
        custom_categories: customized_categories
    };

    let chart = visavail.generate(options, dataset);
    //console.log(dataset);
</script>
