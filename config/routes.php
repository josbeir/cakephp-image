<?php
	use Cake\Routing\Router;


	Router::plugin('Image', ['path' => '/Image'], function ($routes) {
		$routes->connect('/:controller/:action/*');
	});

	/*Router::plugin('Image', function ($routes) {
		$routes->prefix('/admin', function ($routes) {
			$routes->connect('/:controller/:action/*');
		});
	});*/

	/*Router::plugin('Image', function ($routes) {
		$routes->prefix('admin', function ($routes) {
			$routes->connect('/:controller');
		});
	});*/