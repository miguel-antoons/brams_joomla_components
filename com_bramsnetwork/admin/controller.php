<?php
/**
 * @author      Antoons Miguel
 * @package     Joomla.Administrator
 * @subpackage  com_bramsnetwork
 */


 // No direct access to this file
defined('_JEXEC') or die('Restricted access');

use \Joomla\CMS\MVC\Controller\BaseController;

/**
 * General Controller of BramsNetwork component
 *
 * @package     Joomla.Administrator
 * @subpackage  com_bramsnetwork
 * @since       0.0.1
 */
class BramsNetworkController extends BaseController {
	/**
	 * The default view for the display method.
	 *
	 * @var string
	 * @since 12.2
	 */
	protected $default_view = 'map';
}
