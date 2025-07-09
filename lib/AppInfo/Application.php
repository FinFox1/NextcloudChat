<?php
declare(strict_types=1);

namespace OCA\NextcloudChat\AppInfo;

use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;

class Application extends App implements IBootstrap {
    public const APP_ID = 'nextcloud_chat';

    public function __construct() {
        parent::__construct(self::APP_ID);
    }

    public function register(IRegistrationContext $context): void {
        // Register services
        $context->registerService('ElementService', function($c) {
            return new \OCA\NextcloudChat\Service\ElementService(
                $c->query('ConfigService'),
                $c->query(\OCP\ILogger::class)
            );
        });

        $context->registerService('MatrixService', function($c) {
            return new \OCA\NextcloudChat\Service\MatrixService(
                $c->query('ConfigService'),
                $c->query(\OCP\ILogger::class)
            );
        });

        $context->registerService('ConfigService', function($c) {
            return new \OCA\NextcloudChat\Service\ConfigService(
                $c->query(\OCP\IConfig::class)
            );
        });
    }

    public function boot(IBootContext $context): void {
        // Boot process
    }
}
