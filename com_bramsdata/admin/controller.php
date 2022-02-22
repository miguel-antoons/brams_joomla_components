<?php
/**
 * @author      Antoons Miguel
 * @package     Joomla.Administrator
 * @subpackage  com_bramsdata
 */


 // No direct access to this file
defined('_JEXEC') or die('Restricted access');

use \Joomla\CMS\MVC\Controller\BaseController;

/**
 * General Controller of BramsData component
 *
 * @package     Joomla.Administrator
 * @subpackage  com_bramsdata
 * @since       0.0.5
 */
class BramsDataController extends BaseController {
	/**
	 * The default view for the display method.
	 *
	 * @var string
	 * @since 12.2
	 */
	protected $default_view = 'availability';
}
