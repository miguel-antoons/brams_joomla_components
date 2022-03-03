<?php
/**
 * @author      Antoons Miguel
 * @package     Joomla.Administrator
 * @subpackage  com_bramsnetwork
 */


// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// Get an instance of the controller prefixed by BramsNetwork
$controller = BaseController::getInstance('BramsNetwork');

// Perform the Request task
$controller->execute(JFactory::getApplication()->input->get('task'));

// Redirect if set by the controller
$controller->redirect();
