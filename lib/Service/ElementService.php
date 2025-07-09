<?php
declare(strict_types=1);

namespace OCA\NextcloudChat\Service;

use OCP\ILogger;

class ElementService {
    private ConfigService $configService;
    private ILogger $logger;

    public function __construct(
        ConfigService $configService,
        ILogger $logger
    ) {
        $this->configService = $configService;
        $this->logger = $logger;
    }

    public function getElementConfig(): array {
        $baseUrl = $this->configService->getMatrixServerUrl();
        
        return [
            'default_server_config' => [
                'm.homeserver' => [
                    'base_url' => $baseUrl,
                    'server_name' => $this->configService->getMatrixServerName()
                ],
                'm.identity_server' => [
                    'base_url' => $this->configService->getIdentityServerUrl()
                ]
            ],
            'disable_custom_urls' => false,
            'disable_guests' => true,
            'disable_login_language_selector' => false,
            'disable_3pid_login' => false,
            'brand' => 'Nextcloud Chat',
            'integrations_ui_url' => '',
            'integrations_rest_url' => '',
            'integrations_widgets_urls' => [],
            'bug_report_endpoint_url' => '',
            'default_country_code' => 'US',
            'show_labs_settings' => true,
            'features' => [
                'feature_pinning' => 'labs',
                'feature_custom_status' => 'enable',
                'feature_custom_tags' => 'enable',
                'feature_state_counters' => 'enable'
            ],
            'default_federate' => true,
            'default_theme' => 'auto',
            'room_directory' => [
                'servers' => [$this->configService->getMatrixServerName()]
            ],
            'enable_presence_by_hs_url' => [
                $baseUrl => true
            ],
            'setting_defaults' => [
                'breadcrumbs' => true
            ],
            'jitsi' => [
                'preferred_domain' => 'meet.element.io'
            ],
            'element_call' => [
                'url' => $this->configService->getElementCallUrl(),
                'use_exclusively' => false,
                'participant_limit' => 8,
                'brand' => 'Element Call'
            ],
            'map_style_url' => 'https://api.maptiler.com/maps/streets/style.json?key=YOUR_API_KEY'
        ];
    }
}
