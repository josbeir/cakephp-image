<?php
namespace Image\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ImagesFixture
 *
 */
class ImagesFixture extends TestFixture
{

    /**
     * Fields
     *
     * @var array
     */
    // @codingStandardsIgnoreStart
    public $fields = [
        'id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'autoIncrement' => true, 'precision' => null],
        'field_index' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'model' => ['type' => 'string', 'length' => 255, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'fixed' => null],
        'field' => ['type' => 'string', 'length' => 255, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'fixed' => null],
        'foreign_key' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'filename' => ['type' => 'string', 'length' => 255, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'fixed' => null],
        'size' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'mime' => ['type' => 'string', 'length' => 255, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'fixed' => null],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
        ],
        '_options' => [
            'engine' => 'InnoDB',
            'collation' => 'utf8_general_ci'
        ],
    ];
    // @codingStandardsIgnoreEnd

    /**
     * Records
     *
     * @var array
     */
    public $records = [
        [
            'id' => 1,
            'field_index' => 1,
            'model' => 'Articles',
            'field' => 'image',
            'foreign_key' => 1,
            'filename' => 'test1.jpg',
            'size' => 100,
            'mime' => 'image/jpg'
        ],
        [
            'id' => 2,
            'field_index' => 0,
            'model' => 'Articles',
            'field' => 'images',
            'foreign_key' => 1,
            'filename' => 'test2.jpg',
            'size' => 100,
            'mime' => 'image/jpg'
        ],
        [
            'id' => 3,
            'field_index' => 1,
            'model' => 'Articles',
            'field' => 'images',
            'foreign_key' => 1,
            'filename' => 'test3.jpg',
            'size' => 100,
            'mime' => 'image/jpg'
        ],
        [
            'id' => 4,
            'field_index' => 2,
            'model' => 'Articles',
            'field' => 'images',
            'foreign_key' => 1,
            'filename' => 'test4.jpg',
            'size' => 100,
            'mime' => 'image/jpg'
        ]
    ];
}
