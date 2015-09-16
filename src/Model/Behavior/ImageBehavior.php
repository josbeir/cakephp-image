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

use ArrayObject;
use Cake\Event\Event;
use Cake\Filesystem\File;
use Cake\Filesystem\Folder;
use Cake\ORM\Behavior;
use Cake\ORM\Entity;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;
use Intervention\Image\ImageManager;

class ImageBehavior extends Behavior
{

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
        'quality' => 75,
        'path' => null,
        'table' => 'images',
        'manager' => [
            'driver' => 'imagick'
        ]
    ];

    protected $_mimeTypes = [
        'image/jpg',
        'image/jpeg',
        'image/gif',
        'image/png'
    ];

    /**
     * [initialize description]
     * @param  array  $config [description]
     * @return void
     */
    public function initialize(array $config)
    {
        $this->_imagesTable = TableRegistry::get($this->config('table'));

        $this->_setupAssociations(
            $this->_config['table'],
            $this->_config['fields']
        );
    }

    /**
     * [setupAssociations description]
     * @param  [type] $table  [description]
     * @"param  ["type] $fields [description]
     * @return void
     */
    protected function _setupAssociations($table, $fields)
    {
        $alias = $this->_table->registryAlias();

        foreach ($fields as $field => $type) {
            $assocType = $type == 'many' ? 'hasMany' : 'hasOne';
            $name = $this->_fieldName($field);

            if (!TableRegistry::exists($name)) {
                $fieldTable = TableRegistry::get($name, [
                    'className' => $table,
                    'alias' => $name,
                    'table' => $this->_imagesTable->table()
                ]);
            } else {
                $fieldTable = TableRegistry::get($name);
            }

            $this->_table->{$assocType}($name, [
                'targetTable' => $fieldTable,
                'foreignKey' => 'foreign_key',
                'joinType' => 'LEFT',
                'propertyName' => $this->_fieldName($field, false),
                'order' => [
                    $name . '.field_index' => 'ASC'
                ],
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
                $table . '.model' => $alias
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
    public function beforeFind(Event $event, Query $query, $options = [])
    {
        if (isset($options['images']) && !$options['images']) {
            return $query;
        }

        $fields = $this->config('fields');
        $contain = $conditions = [];

        foreach ($fields as $field => $type) {
            $field = $this->_fieldName($field);
            $contain[$field] = $conditions;
        }

        $mapper = function ($row, $key, $mapReduce) use ($fields) {
            foreach ($fields as $field => $type) {
                $name = $this->_fieldName($field, false);
                $image = isset($row[$name]) ? $row[$name] : null;

                // make sure we set the correct registry alias for the entity so
                // we can access the entity's repository from the ImageHelper
                if (!empty($image)) {
                    if (is_array($image)) {
                        foreach ($image as &$imageEntity) {
                            $this->_setEntitySource($imageEntity);
                        }
                    } else {
                        $this->_setEntitySource($image);
                    }
                }

                if ($image === null) {
                    unset($row[$name]);
                    continue;
                }

                $row[$field] = $image;
                unset($row[$name]);
            }

            if ($row instanceof Entity) {
                $row->clean();
            }

            $mapReduce->emitIntermediate($row, $key);
        };

        $reducer = function ($items, $key, $mapReduce) {
            if (isset($items[0])) {
                $mapReduce->emit($items[0], $key);
            }
        };

        return $query
            ->contain($contain)
            ->mapReduce($mapper, $reducer);
    }

    /**
     * [setEntitySource description]
     * @param [type] $entity [description]
     */
    protected function _setEntitySource(&$entity)
    {
        if ($entity instanceof Entity) {
            $entity->source($this->_table->registryAlias());
        }

        return $entity;
    }

    /**
     * [_upload description]
     * @param  string  $fileName [description]
     * @param  string  $filePath [description]
     * @param  bool $copy     [description]
     * @return array            [description]
     */
    protected function _upload($fileName, $filePath, $copy = false)
    {
        $data = [];

        if (!file_exists($filePath) || !$this->_isImage($filePath)) {
            return $data;
        }

        $basePath = $this->basePath();
        $pathinfo = pathinfo($fileName);
        $fileName = md5_file($filePath) . '.' . $pathinfo['extension'];
        $fullPath = $basePath . DS . $fileName;
        $folder = new Folder($basePath, true, 0777);
        $transferFn = $copy || !is_uploaded_file($filePath) ? 'copy' : 'move_uploaded_file';
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
     * Check if given path is an image
     * @param  string  $path path of the image
     * @return boolean       true on success
     */
    protected function _isImage($path)
    {
        $file = new File($path);
        $mime = $file->mime();

        return in_array($mime, $this->_mimeTypes);
    }

    /**
     * Generate all presets for given image entity, built so it can be used as an external method
     * @param  \Cake\ORM\Entity  $image [description]
     * @param  bool $force [description]
     * @return bool         [description]
     */
    public function generatePresets($image, $force = false)
    {
        $manager = new ImageManager($this->config('manager'));
        $basePath = $this->basePath($image->model) . DS;
        $imagePath = $basePath . $image->filename;

        if (!is_file($imagePath)) {
            return false;
        }

        foreach ($this->config('presets') as $preset => $options) {
            $destination = $basePath . $preset . '_' . $image->filename;

            if (!$force && file_exists($destination)) {
                continue;
            }

            $intImage = $manager->make($imagePath);
            foreach ($options as $action => $params) {
                if (is_callable($params)) {
                    $intImage = $params($intImage, $imagePath);
                } else {
                    $intImage = call_user_func_array([ $intImage, $action ], $params);
                }
            }

            $intImage->save($destination, $this->config('quality'));
        }

        return true;
    }

    /**
     * Implementation of the beforesave event, handles uploading / saving and overwriting of image records
     * @param  \Cake\Event\Event       $event   [description]
     * @param  \Cake\ORM\Entity      $entity  [description]
     * @param  ArrayObject $options [description]
     * @return void
     */
    public function beforeSave(Event $event, Entity $entity, ArrayObject $options)
    {
        $fields = $this->config('fields');
        $alias = $this->_table->registryAlias();

        $newOptions = [$this->_imagesTable->alias() => ['validate' => false]];
        $options['associated'] = $newOptions + $options['associated'];
        $entities = [];

        foreach ($fields as $_fieldName => $fieldType) {
            $uploadedImages = [];
            $field = $entity->{$_fieldName};
            $field = $fieldType == 'one' ? [ $field ] : $field;

            if (!$field) {
                continue;
            }

            foreach ($field as $index => $image) {
                $uploadeImage = null;

                if (!empty($image['tmp_name'])) { // server based file uploads
                    $uploadeImage = $this->_upload($image['name'], $image['tmp_name'], false);
                } elseif (is_string($image)) { // any other 'path' based uploads
                    $uploadeImage = $this->_upload($image, $image, true);
                }

                if (!empty($uploadeImage)) {
                    $uploadedImages[$index] = $uploadeImage + [
                        'field_index' => $index,
                        'model' => $alias,
                        'field' => $_fieldName
                    ];
                }
            }

            if (!empty($uploadedImages)) {
                if (!$entity->isNew()) {
                    $imagesTableAlias = $this->_imagesTable->alias();
                    $preexisting = $this->_imagesTable->find()
                        ->where([
                            'model' => $alias,
                            'field' => $_fieldName,
                            'foreign_key' => $entity->{$this->_table->primaryKey()}
                        ])
                        ->order(['field_index' => 'ASC' ]);

                    foreach ($preexisting as $image) {
                        if (isset($uploadedImages[$image->field_index])) {
                            $entities[$image->field_index] = $this->_imagesTable->patchEntity($image, $uploadedImages[$image->field_index]);
                        } elseif ($fieldType == 'one') {
                            $this->_imagesTable->delete($image);
                        }
                    }
                }

                $new = array_diff_key($uploadedImages, $entities);
                foreach ($new as $image) {
                    $entities[] = $this->_imagesTable->newEntity($image);
                }
            }

            $entity->dirty($_fieldName, false);
        }

        $entity->set('_images', $entities);
    }

    /**
     * [afterSave description]
     * @param  Event       $event   [description]
     * @param  Entity      $entity  [description]
     * @param  ArrayObject $options [description]
     * @return void
     */
    public function afterSave(Event $event, Entity $entity, ArrayObject $options)
    {
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
     * @return void
     */
    public function afterDelete(Event $event, Entity $entity, ArrayObject $options)
    {
        $fields = $this->config('fields');

        foreach ($fields as $_fieldName => $fieldType) {
            if (isset($entity->{$_fieldName})) {
                $images = $entity->{$_fieldName};
                if (!is_array($entity->{$_fieldName})) {
                    $images = [ $entity->{$_fieldName} ];
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
    public function deleteImageEntity($imageEntity)
    {
        $shared = $this->_imagesTable->find()
            ->where([
                'id !=' => $imageEntity->id,
                'field_index !=' => $imageEntity->field_index,
                'model' => $imageEntity->model,
                'filename' => $imageEntity->filename
            ]);

        if (!$shared->count()) {
            $basePath = $this->basePath();

            (new File($basePath . DS . $imageEntity->filename))->delete();

            foreach ($this->config('presets') as $preset => $options) {
                (new File($basePath . DS . $preset . '_' . $imageEntity->filename))->delete();
            }
        }

        return $this->_imagesTable->delete($imageEntity);
    }

    /**
     * Delete an image by id
     * @param  int $imageId Image entity id
     * @return bool
     */
    public function deleteImage($imageId)
    {
        $image = $this->_imagesTable->get($imageId);

        return $this->deleteImageEntity($image);
    }

    /**
     * Return the correct _fieldName used in relations and other parts
     * @param  string  $field   _fieldName
     * @param  bool $includeAlias wheter to include the alias
     * @return string
     */
    protected function _fieldName($field, $includeAlias = true)
    {
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
    public function basePath($alias = null)
    {
        if (!$alias) {
            $alias = $this->_table->alias();
        }
        return $this->config('path') . DS . $this->_table->alias();
    }

    /**
     * Return Images table object attached to current table
     * @return Cake\ORM\Table Images table object
     */
    public function imagesTable()
    {
        return $this->_imagesTable;
    }
}
