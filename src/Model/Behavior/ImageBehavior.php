<?php
/**
 * Image, image behavior
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Jasper Smet
 * @link          https://github.com/josbeir/image
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Image\Model\Behavior;

use Cake\ORM\Behavior;
use Cake\ORM\Entity;
use Cake\Event\Event;
use Cake\Filesystem\Folder;
use Cake\Filesystem\File;
use Cake\ORM\TableRegistry;
use Cake\ORM\Query;
use WideImage\WideImage;
use ArrayObject;

class ImageBehavior extends Behavior {

/**
 * [$_imagesTable description]
 * @var [type]
 */
	protected $_imagesTable;

/**
 * [$_defaultConfig description]
 * @var [type]
 */
	public $_defaultConfig = [
		'fields' => [],
		'presets' => [],
		'path' => null,
		'table' => 'images'
	];

/**
 * [initialize description]
 * @param  array  $config [description]
 * @return [type]         [description]
 */
	public function initialize(array $config) {
		$this->_imagesTable = TableRegistry::get($this->config('table'));

		$this->setupAssociations(
			$this->_config['table'],
			$this->_config['fields']
		);
	}

/**
 * [setupAssociations description]
 * @param  [type] $table  [description]
 * @param  [type] $fields [description]
 * @return [type]         [description]
 */
	protected function setupAssociations($table, $fields) {
		$alias = $this->_table->alias();

		foreach ($fields as $field => $type) {
			$assocType = $type == 'many' ? 'hasMany' : 'hasOne';
			$name = $this->fieldName($field);
			$target = TableRegistry::get($name);
			$target->table($table);

			$this->_table->{$assocType}($name, [
				'targetTable' => $target,
				'foreignKey' => 'foreign_key',
				'joinType' => 'LEFT',
				'propertyName' => $this->fieldName($field, false),
				'conditions' => [
					$name . '.model' => $alias,
					$name . '.field' => $field,
				]
			]);
		}	

		$this->_table->hasMany($table, [
			'foreignKey' => 'foreign_key',
			'strategy' => 'subquery',
			'propertyName' => '_images',
			'dependent' => false,
			'conditions' => [
				$table .'.model' => $alias
			]
		]);
	}

/**
 * [beforeFind description]
 * @param  Event  $event   [description]
 * @param  Query  $query   [description]
 * @param  [type] $options [description]
 * @return [type]          [description]
 *
 * ### Options
 * `images` When setting images to false nothing will be added to the query and no image fields will be returned in the resultset and will probably
 * speed up overall performance
 */
	public function beforeFind(Event $event, Query $query, $options = []) {
		if (isset($options['images']) && !$options['images']) {
			return $query;
		}

		$fields = $this->config('fields');
		$alias = $this->_table->alias();
		$contain = $conditions = [];

		foreach ($fields as $field => $type) {
			$field = $this->fieldName($field);
			$contain[$field] = $conditions;
		}

		return $query
			->contain($contain)
			->formatResults(function($results) {
				return $this->_mapResults($results);
			}, $query::PREPEND);
	}

/**
 * [_mapResults description]
 * @param  [type] $results [description]
 * @return [type]          [description]
 */
	protected function _mapResults($results) {
		$fields = $this->config('fields');

		return $results->map(function ($row) use($fields) {
			$hydrated = !is_array($row);

			foreach ($fields as $field => $type) {
				$name = $this->fieldName($field, false);
				$image = isset($row[$name]) ? $row[$name] : null;

				if ($image === null) {
					unset($row[$name]);
					continue;
				}

				$row[$field] = $image;
				unset($row[$name]);
			}

			if ($hydrated) {
				$row->clean();
			}

			return $row;
		});
	}

/**
 * [_upload description]
 * @param  string  $fieldName Name of the field
 * @param  string  $fileName  [description]
 * @param  string  $filePath  [description]
 * @param  boolean $copy      [description]
 * @return \Cake\ORM\Entity             [description]
 */
	protected function _upload($fileName, $filePath, $copy = false) {
		$data = [];

		if (!file_exists($filePath)) {
			//return $data;
		}

		$basePath = $this->basePath();
		$pathinfo = pathinfo($fileName);
		$fileName = md5_file($filePath) .'.'. $pathinfo['extension'];
		$fullPath = $basePath . DS . $fileName;
		$folder = new Folder($basePath, true, 0777);
		$transferFn = $copy ? 'copy' : 'move_uploaded_file';
		$existing = file_exists($fullPath);

		if ($existing || call_user_func_array($transferFn, [ $filePath, $fullPath ])) {
			$file = new File($fullPath);
			$data = [
				'filename' => $fileName,
				'size' => $file->size(),
				'mime' => $file->mime()
			];
		}

		return $data;
	}

/**
 * Generate all presets for given image entity, built so it can be used as an external method
 * @param  [type] $image [description]
 * @return [type]        [description]
 */
	public function generatePresets($image, $force = false) {
		$basePath = $this->basePath($image->model) . DS;
		$imagePath = $basePath . $image->filename;

		foreach($this->config('presets') as $preset => $options) {
			$destination = $basePath . $preset .'_'. $image->filename;

			if (!$force && file_exists($destination)) {
				continue;
			}

			$wImage = WideImage::load($imagePath);
			foreach ($options as $action => $params) {
				if (is_callable($params)) {
					$wImage = $params($wImage, $imagePath);
				} else {
					$wImage = call_user_func_array([ $wImage, $action ], $params);
				}
			}

			$wImage->saveToFile($destination);
		}

		return true;
	}

/**
 * Implementation of the beforesave event, handles uploading / saving and overwriting of image records
 * @param  \Cake\Event\Event       $event   [description]
 * @param  \Cake\ORM\Entity      $entity  [description]
 * @param  ArrayObject $options [description]
 * @return [type]               [description]
 */

	public function beforeSave(Event $event, Entity $entity, ArrayObject $options) {
		$fields = $this->config('fields');
		$alias = $this->_table->alias();
		
		$newOptions = [$this->_imagesTable->alias() => ['validate' => false]];
		$options['associated'] = $newOptions + $options['associated'];
		$entities = [];

		foreach ($fields as $fieldName => $fieldType) {
			$uploadedImages = [];
			$field = $entity->{$fieldName};
			$field = $fieldType == 'one' ? [ $field ] : $field;

			foreach ($field as $image) {
				if (!empty($image['tmp_name'])) { // server based file uploads
					$uploadeImage = $this->_upload($image['name'], $image['tmp_name'], false);
				} elseif (is_string($image)) { // any other 'path' based uploads
					$uploadeImage = $this->_upload($image, $image, true);
				}

				if (!empty($uploadeImage)) {
					$uploadedImages[] = $uploadeImage + [ 'model' => $alias, 'field' => $fieldName ];
				}
			}

			if (!empty($uploadedImages)) {
				if (!$entity->isNew()) {
					$preexisting = $this->_imagesTable->find()
						->where(['model' => $alias, 'field' => $fieldName, 'foreign_key' => $entity->id ])
						->bufferResults(false);

					foreach ($preexisting as $index => $image) {
						if (isset($uploadedImages[$index])) {
							$entities[$index] = $this->_imagesTable->patchEntity($image, $uploadedImages[$index]);
						} else if ($fieldType == 'one') {
							$this->_imagesTable->delete($image);
						}
					}
				}

				$new = array_diff_key($uploadedImages, $entities);
				foreach ($new as $image) {
					$entities[] = $this->_imagesTable->newEntity($image);
				}
			}

			$entity->dirty($fieldName, false);
		}

		$entity->set('_images', $entities);
	}

/**
 * [afterSave description]
 * @param  Event       $event   [description]
 * @param  Entity      $entity  [description]
 * @param  ArrayObject $options [description]
 * @return [type]               [description]
 */
	public function afterSave(Event $event, Entity $entity, ArrayObject $options) {
		if (!empty($entity->_images)) {
			foreach ($entity->_images as $imageEntity) {
				$this->generatePresets($imageEntity);
			}

			$entity->unsetProperty('_images');
		}
	}

/**
 * [afterDelete description]
 * @param  Event       $event   [description]
 * @param  Entity      $entity  [description]
 * @param  ArrayObject $options [description]
 * @return [type]               [description]
 */
	public function afterDelete(Event $event, Entity $entity, ArrayObject $options) {
		$fields = $this->config('fields');

		foreach ($fields as $fieldName => $fieldType) {
			if (isset($entity->{$fieldName})) {
				$images = $entity->{$fieldName};
				if (!is_array($entity->{$fieldName})) {
					$images = [ $entity->{$fieldName} ];
				}

				foreach ($images as $imageEntity) {
					$this->deleteImageEntity($imageEntity);
				}
			}
		}
	}

/**
 * Safely remove the image entity and all its presets
 * The physical image files are only removed after making sure that the same file is not used in other records
 * @param  \Cake\ORM\Entity $imageEntity [description]
 * @return bool
 */
	public function deleteImageEntity($imageEntity) {
		$shared = $this->_imagesTable->find()
			->where([
				'foreign_key !=' => $imageEntity->foreign_key,
				'model' => $imageEntity->model,
				'filename' => $imageEntity->filename
			]);

		if (!$shared->count()) {
			$basePath = $this->basePath();

			(new File($basePath . DS . $imageEntity->filename))->delete();

			foreach($this->config('presets') as $preset => $options) {
				(new File($basePath . DS . $preset .'_'. $imageEntity->filename))->delete();
			}
		}

		return $this->_imagesTable->delete($imageEntity);
	}

/**
 * Return the correct fieldname used in relations and other parts
 * @param  string  $field   fieldname
 * @param  bool $includeAlias wheter to include the alias
 * @return string
 */
	protected function fieldName($field, $includeAlias = true) {
		$alias = $this->_table->alias();
		$name = $field . '_image';

		if ($includeAlias) {
			$name = $alias . '_' . $name;
		}

		return $name;
	}

/**
 * Return basepath for current model or overridable by the `alias` parameter
 * @param string $alias Optional parameter to override the alias returned in the basePath
 * @return string
 */
	public function basePath($alias = null) {
		if (!$alias) {
			$alias = $this->_table->alias();
		}
		return $this->config('path') . DS . $this->_table->alias();
	}

/**
 * Return Images table object attached to current table
 * @return Cake\ORM\Table Images table object
 */
	public function imagesTable() {
		return $this->_imagesTable;
	}
}
