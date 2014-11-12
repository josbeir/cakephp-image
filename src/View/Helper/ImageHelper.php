<?php
namespace Image\View\Helper;

use Cake\View\Helper;
use Cake\ORM\Entity;

class ImageHelper extends Helper {

	public $helpers = [ 'Url', 'Html' ];

	protected $_defaultConfig = [
		'path' => null
	];

	public function render($image, array $options = []) {
		if (empty($image)) {
			return null;
		}

		$path = $this->directory($image);

		if (isset($options['preset'])) {
			$path .= $options['preset'] .'_';
		}

		$path .= $image->filename;

		unset($options['preset']);

		return $this->Html->image($path, $options);
	}

/**
 * Return directory where image is located
 * @param  Entity $image [description]
 * @return string        [description]
 */
	protected function directory(Entity $image) {
		return $this->config('path') . DS . $image->model . DS;
	}

}
