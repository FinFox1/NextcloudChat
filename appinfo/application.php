<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use OCA\NextcloudChat\AppInfo\Application;

$app = new Application();
$app->getContainer()->get('OCP\AppFramework\App');
