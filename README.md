CakePHP 3.0 Image upload behavior
=====
Image behavior that works much like Cake's built in Translate Behavior by adding fields with image data to every entity the table returns.

* Uploads can be either $_FILE based or just a string containing path. 'copy' or 'move_uploaded_file' is used accordingly.
* Validating should be done by cake's nice validation options and is thereforce not included in the behavior itself.
* Image presets are generated using [WideImage](https://github.com/smottt/WideImage). See the sourceforge [documentation](http://wideimage.sourceforge.net/) page.

### Notes
The behavior is very much a work in progress and should not be considered stable in any way.

### Configuration parameters
* **fields**: Fields used for images, should be the name of the field as key and the type as value (many, one)
* **presets**: Array of presets containing a list of WideImage methods and their parameters
* **path**: The base path where the uploaded images should be stored
* **table**: The table name of for storing the image data (see Config/Schema/images.sql)

### Usage

Install using composer

```javascript
"require": {
	"josbeir/Image": "dev-master"
}
```

And run `php composer.phar update`

Enable the plugin by adding it to bootstrap.php
```php
Plugin::load('Image');
```

Enable the image behavior by adding it to the Table's initialize hook

```php
	public function initialize(array $config) {
		$this->addBehavior('Image.Image', [
			'path' => Configure::read('Asset.path'),
			'presets' => [
				'overview' => [
					'resize' => [ 200, 200, 'outside', 'fill' ],
					'crop' => [ 'center', 'center', 200, 200 ]
				]
			],
			'fields' => [
				'images' => 'many',
				'main' => 'one'
			],
		]);
	}
```

### Image presets
Image manipulation is handled by [WideImage](https://github.com/smottt/WideImage) and configuring presets is pretty straightforward.
In the example below the preset 'overview' is generated by looping trough various WideImage helper functions

```php
$this->addBehavior('Image.Image', [
	'path' => WWW_ROOT . 'myimages',
	'presets' => [
		'overview' => [
			'resize' => [ 200, 200, 'outside', 'fill' ], // $image->resize(200, 200, 'outside', 'fill');
			'crop' => [ 'center', 'center', 200, 200 ] // $image->crop('center', 'center', 200, 200);
			'canvas' => function($image) { // you can use callback functions for more advanced stuff
				$white = $image->allocateColor(255,255,255);
				return $image->resizeCanvas(200, 200, 'center', 'center', $white);
			},
		]
	],
	'fields' => [
		'image' => 'one'
	],
]);
```

### Helper
I've included a basic helper to render the images in your templates.

```php
$this->Image->render($entity->field); // Original image
$this->Image->render($entity->field, [ 'preset' => 'presetName' ]); // Preset
$this->Image->render($entity->field, [ 'preset' => 'presetName', 'alt' => 'Cool image' ]); // Preset + image attributes
$this->Image->url($entity->field, [ 'preset' => 'presetName' ]); // Returns the image path
```

### Shell
Simple shell to re-generate all presets for given model

```cli
bin/cake image
