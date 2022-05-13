<?php
/**
 * @author      Antoons Miguel
 * @package     Joomla.Administrator
 * @subpackage  com_bramsdata
 */


// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\Input\Input;

require JPATH_ROOT.DIRECTORY_SEPARATOR.'components/com_bramsdata/models/Lib/Archive.php';

/**
 * HTML View class for the BramsData Component
 *
 * @since  0.4.0
 */
class BramsDataViewViewer extends HtmlView {
    /**
     * Function makes sure to get the application input. If it fails, it
     * will return false
     *
     * @return Input|boolean
     * @since 0.2.5
     */
    private function getAppInput() {
        try {
            return Factory::getApplication()->input;
        } catch (Exception $e) {
            // log the exception
            Log::add($e, Log::ERROR, 'error');
            return false;
        }
    }

    public function getImage() {
        $input          = $this->getAppInput();
        $params         = array(
            'task'      => 'showImage',
            'image'     => $input->get('image'),
        );

        if ($fMin = $input->get('fmin', false)) $params['fmin'] = $fMin;
        if ($fMax = $input->get('fmax', false)) $params['fmax'] = $fMax;

        $archive = new Archive();
        $this->document->setMimeEncoding('image/png');

        echo $archive->get($params);
    }

	public function saveImage() {
		$input = $this->getAppInput();
		$params = array(
			'task'  => 'saveImage',
			'image' => $input->get('image'),
		);

		$archive = new Archive();

		$this->document->setMimeEncoding('image/png');
		header('Content-Disposition: attachment; filename=' . $input->get('image') . '.png');

		echo $archive->get($params);
	}

	public function saveWav() {
		$model              = $this->getModel();
		$input              = $this->getAppInput();
		$image              = $input->get('image');
		$split_image_name   = explode('_', $image);

		$file_start = DateTime::createFromFormat('YmdHi', $split_image_name[2].$split_image_name[3]);

		if ($model->getFileStatus($input->get('sysId'), $file_start->format('Y-m-d H:i'))) {
			$this->document->setMimeEncoding('application/octet-stream');
			header('Content-Disposition: attachment; filename=' . $image . '.wav');

			$archive = new Archive;
			$params = array(
				'task'  => 'saveWave',
				'image' => $image,
			);

			echo $archive->get($params);
		}
	}
}
