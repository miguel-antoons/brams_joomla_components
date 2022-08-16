<?php
/**
 * @author      Antoons Miguel
 * @package     Joomla.Administrator
 * @subpackage  com_bramsdata
 */


// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Controller\BaseController;

try {
	// Get an instance of the controller prefixed by BramsData
	$controller = BaseController::getInstance('BramsData');
} catch (Exception $e) {
	echo '
        Something went wrong. 
        Activate Joomla debug and view log messages for more information.
    ';
	Log::add($e, Log::ERROR, 'error');
	return;
}

try {
	// get the request data input
	$input = JFactory::getApplication()->input;
} catch (Exception $e) {
	echo '
        Something went wrong. 
        Activate Joomla debug and view log messages for more information.
    ';
	Log::add($e, Log::ERROR, 'error');
	return;
}
try {
	// execute the requested task
	$controller->execute($input->getCmd('task'));
} catch (Exception $e) {
	echo '
        Something went wrong. 
        Activate Joomla debug and view log messages for more information.
    ';
	Log::add($e, Log::ERROR, 'error');
	return;
}

// Redirect if set by the controller
$controller->redirect();
