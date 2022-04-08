<?php
/**
 * @author      Antoons Miguel
 * @package     Joomla.Administrator
 * @subpackage  com_bramscampaign
 */


// No direct access to this file
use Joomla\CMS\MVC\Controller\BaseController;

defined('_JEXEC') or die('Restricted access');

// Get an instance of the controller prefixed by BramsCampaign
$controller = BaseController::getInstance('BramsCampaign');

// Perform the Request task
$controller->execute(JFactory::getApplication()->input->get('task'));

// Redirect if set by the controller
$controller->redirect();
