<?php
namespace Image\Test\TestCase\Model\Behavior;

use Cake\Collection\Collection;
use Cake\Core\Plugin;
use Cake\ORM\Behavior\TranslateBehavior;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Image\Model\Behavior\ImageBehavior;

/**
 * Image\Model\Behavior\ImageBehavior Test Case
 */
class ImageBehaviorTest extends TestCase
{

    public $fixtures = [
        'core.articles',
        'plugin.image.images'
    ];

    public function tearDown()
    {
        parent::tearDown();
        TableRegistry::clear();
    }

    public function testFindImageOne()
    {
        $table = TableRegistry::get('Articles');
        $table->addBehavior('Image.Image', [
            'fields' => [
                'image' => 'one'
            ]
        ]);

        $result = $table->find()
            ->first()
            ->toArray();

        $expected = [
            'id' => 1,
            'author_id' => 1,
            'title' => 'First Article',
            'body' => 'First Article Body',
            'published' => 'Y',
            'image' => [
                'id' => 1,
                'field_index' => 1,
                'model' => 'Articles',
                'field' => 'image',
                'foreign_key' => 1,
                'filename' => 'test1.jpg',
                'size' => 100,
                'mime' => 'image/jpg'
            ]
        ];

        $this->assertSame($expected, $result);
    }

    public function testFindImageMany()
    {
        $table = TableRegistry::get('Articles');
        $table->addBehavior('Image.Image', [
            'fields' => [
                'images' => 'many'
            ]
        ]);

        $result = $table->find()
            ->hydrate(false)
            ->first();

        $expected = [
            'id' => 1,
            'author_id' => 1,
            'title' => 'First Article',
            'body' => 'First Article Body',
            'published' => 'Y',
            'images' => [
                0 => [
                    'id' => 2,
                    'field_index' => 0,
                    'model' => 'Articles',
                    'field' => 'images',
                    'foreign_key' => 1,
                    'filename' => 'test2.jpg',
                    'size' => 100,
                    'mime' => 'image/jpg'
                ],
                1 => [
                    'id' => 3,
                    'field_index' => 1,
                    'model' => 'Articles',
                    'field' => 'images',
                    'foreign_key' => 1,
                    'filename' => 'test3.jpg',
                    'size' => 100,
                    'mime' => 'image/jpg'
                ],
                2 => [
                    'id' => 4,
                    'field_index' => 2,
                    'model' => 'Articles',
                    'field' => 'images',
                    'foreign_key' => 1,
                    'filename' => 'test4.jpg',
                    'size' => 100,
                    'mime' => 'image/jpg'
                ]
            ]
        ];

        $this->assertSame($expected, $result);
    }

    /**
     * Emulate upload structure and save uploaded file
     * Because its not a real upload the upload behavior will use copy instead of move_uploaded_file accordingly
     * (This should be tested better in the future tho)
     */
    public function testFormUploadSingleFile()
    {
        $table = TableRegistry::get('Articles');
        $table->addBehavior('Image.Image', [
            'path' => TMP . 'tests' . DS . 'image',
            'fields' => [
                'image' => 'one'
            ]
        ]);

        $item = $table->find()->first();
        $item->set('image', [
            'name' => 'test.jpg',
            'tmp_name' => Plugin::path('Image') . 'tests' . DS . 'assets' . DS . 'shoes.png'
        ]);

        $table->save($item);
        $item = $table->find()->first();
        $image = $item->get('image')->toArray();

        $expected = [
            'id' => 5,
            'field_index' => 0,
            'model' => 'Articles',
            'field' => 'image',
            'foreign_key' => 1,
            'filename' => 'df047416c612a9f13a3565ea6f0c38f6.jpg',
            'size' => 132137,
            'mime' => 'image/png'
        ];

        $this->assertEquals(1, $item->get('id'));
        $this->assertSame($expected, $image);
    }

    /**
     * Emulate upload structure and save multiple uploaded files
     * Because its not a real upload the upload behavior will use copy instead of move_uploaded_file accordingly
     * (This should be tested better in the future tho)
     */
    public function testFormUploadMultipleFiles()
    {
        $table = TableRegistry::get('Articles');
        $table->addBehavior('Image.Image', [
            'path' => TMP . 'tests' . DS . 'image',
            'fields' => [
                'images' => 'many'
            ]
        ]);

        $item = $table->find()->first();
        $item->set('images', [
            0 => [
                'name' => 'test.jpg',
                'tmp_name' => Plugin::path('Image') . 'tests' . DS . 'assets' . DS . 'shoes.png'
            ],
            1 => [
                'name' => 'simpsons.jpg',
                'tmp_name' => Plugin::path('Image') . 'tests' . DS . 'assets' . DS . 'simpsons.png'
            ],
            2 => [
                'name' => 'gucci.jpg',
                'tmp_name' => Plugin::path('Image') . 'tests' . DS . 'assets' . DS . 'gucci.png'
            ]
        ]);

        $table->save($item);
        $item = $table->find()
            ->hydrate(false)
            ->first();

        $expected = [
            'id' => 1,
            'author_id' => 1,
            'title' => 'First Article',
            'body' => 'First Article Body',
            'published' => 'Y',
            'images' => [
                0 => [
                    'id' => 2,
                    'field_index' => 0,
                    'model' => 'Articles',
                    'field' => 'images',
                    'foreign_key' => 1,
                    'filename' => 'df047416c612a9f13a3565ea6f0c38f6.jpg',
                    'size' => 132137,
                    'mime' => 'image/png'
                ],
                1 => [
                    'id' => 3,
                    'field_index' => 1,
                    'model' => 'Articles',
                    'field' => 'images',
                    'foreign_key' => 1,
                    'filename' => 'fba1943f40ec92eddd81e0688a255a43.jpg',
                    'size' => 33628,
                    'mime' => 'image/png'
                ],
                2 => [
                    'id' => 4,
                    'field_index' => 2,
                    'model' => 'Articles',
                    'field' => 'images',
                    'foreign_key' => 1,
                    'filename' => '0409095d5904edde1065f313018d7518.jpg',
                    'size' => 119448,
                    'mime' => 'image/png'
                ]
            ]
        ];

        $this->assertSame($expected, $item);
    }
}
