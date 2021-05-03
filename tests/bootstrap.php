<?php
/**
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

error_reporting(E_ALL & ~E_USER_DEPRECATED);

require dirname(__DIR__) . '/vendor/autoload.php';

define('CAKE', dirname(__DIR__) . '/vendor/cakephp/cakephp/src/');

require CAKE . 'basics.php';

define('APP', __DIR__);
define('TMP', sys_get_temp_dir() . DS);
define('LOGS', TMP . 'logs' . DS);

use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Datasource\ConnectionManager;

Configure::write('debug', true);
Configure::write('App', [
    'namespace' => 'App',
    'paths' => [
        'plugins' => [APP . DS . 'testapp' . DS . 'Plugin' . DS],
    ],
]);

Cache::setConfig([
    '_cake_core_' => [
        'engine' => 'File',
        'prefix' => 'cake_core_',
        'serialize' => true,
        'path' => '/tmp',
    ],
    '_cake_model_' => [
        'engine' => 'File',
        'prefix' => 'cake_model_',
        'serialize' => true,
        'path' => '/tmp',
    ],
]);

if (!getenv('db_dsn')) {
    putenv('db_dsn=sqlite:///:memory:');
}

if (!getenv('DB')) {
    putenv('DB=sqlite');
}

ConnectionManager::setConfig('test', ['url' => getenv('db_dsn')]);

Plugin::load('Image', [
    'path' => dirname(dirname(__FILE__)) . DS,
]);
