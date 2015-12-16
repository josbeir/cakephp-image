<?php
	use Cake\Routing\Router;


	Router::plugin('Image', ['path' => '/Image'], function ($routes) {
		$routes->connect('/images/*', ['prefix' => 'admin', 'admin' => 'true', 'controller' => 'Images', 'action' => 'images']);
		$routes->connect('/delete/*', ['prefix' => 'admin', 'admin' => 'true', 'controller' => 'Images', 'action' => 'ajax_delete_image']);
		$routes->connect('/test', ['controller' => 'Users', 'action' => 'logout', 'prefix' => 'admin']);  //No ide why it has to be here..
		$routes->fallbacks('InflectedRoute');

	});

	/*Router::plugin('Image', function ($routes) {
		$routes->prefix('/admin', function ($routes) {
			$routes->connect('/:controller/:action/*');
		});
	});*/

	/*Router::prefix('admin', function ($routes) {
		$routes->plugin('Image', function ($routes) {
			$routes->connect('/:controller');
		});
	});*/

	/*Router::plugin('Image', function ($routes) {
		$routes->prefix('admin', function ($routes) {
			$routes->connect('/:controller');
		});
	});*/