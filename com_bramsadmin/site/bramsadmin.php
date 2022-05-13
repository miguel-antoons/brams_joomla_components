<?php
/**
 * @author      Antoons Miguel
 * @package     Joomla.Administrator
 * @subpackage  com_bramsadmin
 */


// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;

try {
	$app = Factory::getApplication();
} catch (Exception $e) {
	echo '
        Something went wrong. 
        Activate Joomla debug and view log messages for more information.
    ';
	Log::add($e, Log::ERROR, 'error');
	return;
}

$user = $app->getIdentity();
if ($user === null || $user->id <= 0) {
	echo 'Unauthorized access';
	Log::add('An unauthorized access has been performed.', Log::NOTICE, 'error');
	return;
}


try {
    // Get an instance of the controller prefixed by BramsAdmin
    $controller = BaseController::getInstance('BramsAdmin');
} catch (Exception $e) {
    echo '
        Something went wrong. 
        Activate Joomla debug and view log messages for more information.
    ';
    Log::add($e, Log::ERROR, 'error');
    return;
}

try {
    // get the application input from the request
    $input = $app->input;
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
