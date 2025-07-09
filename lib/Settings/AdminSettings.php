<?php
declare(strict_types=1);

namespace OCA\NextcloudChat\Settings;

use OCA\NextcloudChat\Service\ConfigService;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IInitialStateService;
use OCP\Settings\ISettings;

class AdminSettings implements ISettings {
    private ConfigService $configService;
    private IInitialStateService $initialStateService;

    public function __construct(
        ConfigService $configService,
        IInitialStateService $initialStateService
    ) {
        $this->configService = $configService;
        $this->initialStateService = $initialStateService;
    }

    public function getForm(): TemplateResponse {
        $this->initialStateService->provideInitialState('nextcloud_chat', 'admin-config', [
            'matrix_server_url' => $this->configService->getMatrixServerUrl(),
            'matrix_server_name' => $this->configService->getMatrixServerName(),
            'element_call_url' => $this->configService->getElementCallUrl(),
            'identity_server_url' => $this->configService->getIdentityServerUrl(),
            'element_web_url' => $this->configService->getElementWebUrl(),
        ]);

        return new TemplateResponse('nextcloud_chat', 'admin-settings');
    }

    public function getSection(): string {
        return 'nextcloud_chat';
    }

    public function getPriority(): int {
        return 0;
    }
}
