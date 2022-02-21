<?php
/**
 * @author      Antoons Miguel
 * @package     Joomla.Administrator
 * @subpackage  com_availability
 */


// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * Availability Model
 *
 * @since  0.0.1
 */
class AvailabilityModelAvailability extends JModelItem
{
	/**
	 * @var string message
	 */
	protected $message;

	/**
	 * Get the message
     *
	 * @return  string  The message to be displayed to the user
	 */
	public function getMsg()
	{
		if (!isset($this->message))
		{
			$this->message = 'Hello World!';
		}

		return $this->message;
	}
}
