<?php
/**
 * @author      Antoons Miguel
 * @package     Joomla.Administrator
 * @subpackage  com_bramscampaign
 */


 // No direct access to this file
defined('_JEXEC') or die('Restricted access');

use \Joomla\CMS\MVC\Controller\BaseController;

/**
 * General Controller of BramsCampaign component
 *
 * @package     Joomla.Administrator
 * @subpackage  com_bramscampaign
 * @since       0.0.1
 */
class BramsCampaignController extends BaseController {
	/**
	 * The default view for the display method.
	 *
	 * @var string
	 * @since 0.0.1
	 */
	protected $default_view = 'countings';
}
