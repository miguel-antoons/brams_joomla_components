<?php
$this->Html->css('/js/fancybox/jquery.fancybox.css', array('inline' => false, 'media' => 'screen'));
$this->Html->css('manual_counting.css', array('inline' => false, 'media' => 'screen'));

$this->Html->script('manual_counting.js', array('inline' => false));
$this->Html->script('/js/jquery-1.10.2.min.js', array('inline' => false));
$this->Html->script('/js/fancybox/jquery.fancybox.pack.js', array('inline' => false));

function createForm($view, $action, $onSubmit = null) {
    $options['url'] = array('controller' => 'manual_countings', 'action' => $action);
    if ($onSubmit !== null) {
        $options['onsubmit'] = $onSubmit;
    }
    $str = $view->Form->create('ManualCounting', $options);
    $str .= $view->Form->input('id');
    $str .= $view->Form->submit('', array('name' => 'submit', 'id' => 'mc_'.$action, 'div' => false, 'class' => 'mc_'.$action));
    $str .= $view->Form->end();
    return $str;
}

function createCanvas($spectrogram, $id) {
    $width = $spectrogram['width'];
    $height = $spectrogram['height'];
    $str  = '<canvas id="'.$id.'" width="'.$width.'" height="'.$height.'">';
    $str .= '<p>You need a browser with HTML5 support to see this page.</p>';
    $str .= '</canvas>';
    return $str;
}

?>

    <div id="mc_content" style="width: <?php echo $spectrogram['width']+104; ?>px;">
    <div id="mc_header">
        <div id="mc_left_icons">
            <a class="fancybox" id="mc_help_icon" href="#mc_help"><img src="/img/help_icon.png" alt="" title="Show help."/></a>
            <div id="mc_help" style="display: none;">
                <p>How to count meteors:</p>
                <ul>
                    <li style="font-weight: bold;">Make sure the zoom level of your browser is 100&#37;.</li>
                    <li>Click and drag to draw a rectangle around a meteor. Try to draw the smallest possible rectangle.</li>
                    <li>Double-click inside one rectangle to remove it.</li>
                    <li>Press s or 1 to select/deselect short meteors.</li>
                    <li>Press l or 2 to select/deselect long meteors.</li>
                </ul>
                <p>Note: the minimum resolution to display this page is 1024x768.</p>
            </div>
        </div>
        <div id="mc_right_icons">
            <a href="#S" onclick="selectMeteorType('S')"><img id="mc_short" src="/img/short_icon.png" alt="S"
                title="Press s or 1 to select/deselect short meteors."/></a>
            <a href="#L" onclick="selectMeteorType('L')"><img id="mc_long" src="/img/long_icon.png" alt="L"
                title="Press l or 2 to select/deselect long meteors."/></a>
            <a href="/manual_countings/"><img src="/img/white_cross_icon.png" alt="X" title="Close the window."/></a>
        </div>
        <div id="mc_title">
        <?php echo $this->Form->create('ManualCounting',
              array('url' => array('controller' => 'manual_countings', 'action' => 'select')));
              echo $this->Form->input('id');
              echo $this->Form->select('data_file', $dataFiles, array('empty' => false,
                                                                      'value' => $this->data['DataFile']['id'],
                                                                      'onchange' => 'this.form.submit()'));
              echo ' ('.($this->data['ManualCounting']['progress']+1).'/'.$this->data['ManualCountingCampaign']['file_count'].')';
              echo $this->Form->end(); ?>
        </div>
    </div>

    <div id="mc_container">
        <?php echo createForm($this, 'previous', 'return submitMeteors(this);');
              /* Create static canvas solves most of the bugs with excanvas... */
              echo createCanvas($spectrogram, 'mc_counting');
              echo createCanvas($spectrogram, 'mc_background');
              echo createCanvas($spectrogram, 'mc_canvas');
              echo createForm($this, 'next', 'return submitMeteors(this);'); ?>
    </div>
</div>

<script type="text/javascript">
    var mc_image_src = '<?php echo $spectrogram['url']; ?>';
    var mc_meteors = new Array();
    var mc_meteor_type = '<?php echo $meteorType; ?>';
<?php foreach ($meteors as $meteor) {
    $top = $meteor['ManualCountingMeteor']['top'];
    $left = $meteor['ManualCountingMeteor']['left'];
    $bottom = $meteor['ManualCountingMeteor']['bottom'];
    $right = $meteor['ManualCountingMeteor']['right'];
    $type = $meteor['ManualCountingMeteor']['type'];
    echo "    mc_meteors.push(new Meteor($left, $top, $right, $bottom, '$type'));\n";
} ?>

    $(document).ready(function() {
        $(".fancybox").fancybox();
        <?php if ($displayHelp) echo '$("#mc_help_icon").fancybox().click();'; ?>
        });
</script>

<!-- Not in head for now. See issue  -->
<script type="text/javascript" src="/js/detect-zoom.js"></script>
<script>
    var zoom = detectZoom.zoom();
    if (zoom && zoom != 1) {
        zoom = Math.round(100 * zoom);
        alert('The current zoom level of your browser is ' + zoom + '%. Please reset your zoom to 100%.');
    }
</script>