<?php
/**
 * Availability Module Entry Point
 * 
 * @author      Antoons Miguel
 */

// No direct access
defined('_JEXEC') or die;
// Include the syndicate functions only once
require_once dirname(__FILE__) . '/helper.php';

$stations = modAvailabilityHelper::getStations($params);
require JModuleHelper::getLayoutPath('mod_helloworld');
