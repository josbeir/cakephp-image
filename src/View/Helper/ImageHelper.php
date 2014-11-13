<?php
namespace Image\View\Helper;

use Cake\View\Helper;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;

class ImageHelper extends Helper {

	public $helpers = [ 'Url', 'Html' ];

	public $paths = [ ];

	protected $_defaultConfig = [
		'path' => null
	];

	public function render($image, array $options = []) {
		if (empty($image)) {
			return null;
		}

		$path = $this->basePath($image);

		if (isset($options['preset'])) {
			$path .= $options['preset'] .'_';
		}

		$path .= $image->filename;

		unset($options['preset']);

		return $this->Html->image($path, $options);
	}

/**
 * Return directory where image is located for given entity
 * @param  Entity $image [description]
 * @return string        [description]
 */
	protected function basePath(Entity $image) {

		if (isset($this->paths[$image->model])) {
			return $this->paths[$image->model];
		}

		$basePath = $this->config('path');

		// use the path defined in the model's behavior
		if (empty($path)) {
			$table = TableRegistry::get($image->model);
			$basePath = $table->behaviors()->Image->config('path');
			$basePath = str_replace(WWW_ROOT, '/', $basePath);
		}

		return $this->paths[$image->model] = $basePath . DS . $image->model . DS;
	}

}
