<?php
/**
 * @author      Antoons Miguel
 * @package     Joomla.Administrator
 * @subpackage  com_bramsdata
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
?>

<div class="container custom_container container_margin">
    <div class='row'>
        <div class='col custom_col'>
            <input type='checkbox' onClick='checkAllBoxes(this)' id='checkAll' name='checkAll' />
            <label class='master_checkbox' for='checkAll'>Check All</label>
        </div>
        <div class='col custom_col'>
            <input type='checkbox' onClick='checkRPIBoxes(this)' id='checkRPI' name='checkRPI' />
            <label class='master_checkbox' for='checkRPI'>Check New</label>
        </div>
        <div class='col custom_col'>
            <input type='checkbox' onClick='checkFTPBoxes(this)' id='checkFTP' name='checkFTP' />
            <label class='master_checkbox' for='checkFTP'>Check Old</label>
        </div>
        <div class='col custom_col'></div>
        <div class='col custom_col'></div>
    </div>
</div>

<form action='' method='post' name='availabilityForm'>
    <div class="container custom_container">
        <div class='row'>
            <?php echo "<div class='col custom_col'>"; ?>
            <?php $index = 0 ?>
            <?php foreach ($this->stations as $station) : ?>
                <?php 
                    if(!($index % $this->column_length) && $index) { echo "</div><div class='col custom_col'>"; }
                    $index++;
                ?>
                <input 
                    type='checkbox' 
                    onClick='changeCheckBox()' 
                    class='custom_checkbox <?php echo $station->transfer_type ?> <?php echo $station->status ?>'
                    name='station[]'
                    value='<?php echo $station->id ?>'
                    id='station<?php echo $station->id ?>'
                    <?php echo $station->checked ?>
                />
                <label class='checkbox_label' for='station<?php echo $station->id ?>'><?php echo $station->name ?></label>
                <br>
            <?php endforeach; ?>
            <?php echo "</div>" ?>
        </div>
        <div class='row'>
            <div class='col custom_col'>
                <label for='startDate'>From </label>
                <input type='date' name='startDate' id='startDate' min='2011-01-01' max='<?php echo $this->today ?>' value='<?php echo $this->start_date ?>' required/>
            </div>
            <div class='col custom_col'>
                <label for='endDate'>To </label>
                <input type='date' name='endDate' id='endDate' min='2011-01-01' max='<?php echo $this->today ?>' value='<?php echo $this->end_date ?>' required/>
            </div>
        </div>
        <input name='submit' type='submit' id='submit' class='custom_btn'/>
    </div>
</form>
<div class="container custom_container">
    <div class="row">
        <div class='col legend'><span class="a"></span>  100%</div>
        <div class='col legend'><span class="b"></span>  80.1 - 99.9%</div>
        <div class='col legend'><span class="c"></span>  60.1 - 80%</div>
        <div class='col legend'><span class="d"></span>  40.1 - 60%</div>
        <div class='col legend'><span class="e"></span>  20.1 - 40%</div>
        <div class='col legend'><span class="f"></span>  0.1 - 20%</div>
        <div class='col legend'><span class="g"></span>  0%</div>
    </div>
    <div class='row'>
        <div class='col'>
            <div style="overflow: hidden;" class="visavail" id="visavail_container">
                <p id="visavail_graph">
                    <!-- Visavail.js chart will be placed here -->
                </p>
            </div>
        </div>
    </div>
</div>
<script>
    // check that at least one checkbox is checked on submit
    $(document).ready(function () {
        $('#submit').click(function() {
            checked = $("input[type=checkbox]:checked").length;

            if(!checked) {
            alert("You must check at least one checkbox.");
            return false;
            }

        });
    });

    // check needed checkboxes on page reload
    changeCheckBox();

    // data to enter into the graph
    let dataset = [
        <?php foreach ($this->selected_stations as $station) : ?>
            {
                "measure": "<?php echo $this->stations[array_search($station, array_column($this->stations, 'id'))]->name ?>",
                "interval_s": <?php echo $this->interval ?>,
                "categories": {
                    "0%": {class: "rect_has_no_data", tooltip_html: '<i class="fas fa-fw fa-exclamation-circle tooltip_has_no_data">0%</i><br>' },
                    "100%": {class: "rect_has_data", tooltip_html: '<i class="fas fa-fw fa-check tooltip_has_data">100%</i><br>' },
                    "0.1 - 20%": {class: "rect_red1", tooltip_html: '<i class="fas fa-fw tooltip_red1">0.1 - 20%</i><br>' },
                    "20.1 - 40%": {class: "rect_red2", tooltip_html: '<i class="fas fa-fw tooltip_red2">20.1 - 40%</i><br>' },
                    "40.1 - 60%": {class: "rect_blue", tooltip_html: '<i class="fas fa-fw tooltip_blue">40.1 - 60%</i><br>' },
                    "60.1 - 80%": {class: "rect_green2", tooltip_html: '<i class="fas fa-fw tooltip_green2">60.1 - 80%</i><br>' },
                    "80.1 - 99.9%": {class: "rect_green1", tooltip_html: '<i class="fas fa-fw tooltip_green1">80.1 - 99.9%</i><br>' },
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

    // graph options
    let options = {
        id_div_container: "visavail_container",
        id_div_graph: "visavail_graph",
        responsive: {
            enabled: true,
        },
        custom_categories: true,
        onClickBlock: zoomGraph,
    };

    let chart = visavail.generate(options, dataset);
</script>
