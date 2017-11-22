<?php
/**
 * Created by PhpStorm.
 * User: fizda
 * Date: 06/06/2017
 * Time: 17:06
 */

use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\ORM\Tools\Console\ConsoleRunner;

require __DIR__ . '/../vendor/autoload.php';

$settings = [
    'meta' => [
        'entity_path' => [
            'src/CNC/Entity'
        ],
        'auto_generate_proxies' => false,
        'proxy_dir' => __DIR__ . '/../cache/proxies',
        'cache' => new \Doctrine\Common\Cache\ArrayCache(),
    ],
    'connection' => [
        'driver' => 'pdo_mysql',
        'host' => 'localhost',
        'dbname' => 'cncappsdev',
        'user' => 'root',
        'password' => 'CnC1989',
        'charset' => 'utf8',
        'driverOptions' => array(
            1002 => 'SET NAMES utf8'
        )
    ]
];

$config = \Doctrine\ORM\Tools\Setup::createAnnotationMetadataConfiguration(
    $settings['meta']['entity_path'],
    $settings['meta']['auto_generate_proxies'],
    $settings['meta']['proxy_dir'],
    $settings['meta']['cache'],
    false
);
$em = \Doctrine\ORM\EntityManager::create($settings['connection'], $config);

/** @var $em \Doctrine\ORM\EntityManager */
$platform = $em->getConnection()->getDatabasePlatform();
$platform->registerDoctrineTypeMapping('enum', 'string');

AnnotationRegistry::registerLoader('class_exists');
return ConsoleRunner::createHelperSet($em);