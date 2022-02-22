<?php
/**
 * @author      Antoons Miguel
 * @package     Joomla.Administrator
 * @subpackage  com_bramsdata
 */


// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use \Joomla\CMS\MVC\Controller\BaseController;
use \Joomla\CMS\MVC\View\HtmlView;
use \Joomla\CMS\MVC\Model\BaseDatabaseModel;
use \Joomla\CMS\MVC\Model\ItemModel;
use \Joomla\CMS\MVC\Model\ListModel;

// Get an instance of the controller prefixed by BramsData
$controller = BaseController::getInstance('BramsData');

// Perform the Request task
$input = JFactory::getApplication()->input;
$controller->execute($input->getCmd('task'));

// Redirect if set by the controller
$controller->redirect();
