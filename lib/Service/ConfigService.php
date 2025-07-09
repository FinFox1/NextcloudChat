<?php
declare(strict_types=1);

namespace OCA\NextcloudChat\Service;

use OCP\IConfig;

class ConfigService {
    private IConfig $config;
    private string $appName = 'nextcloud_chat';

    public function __construct(IConfig $config) {
        $this->config = $config;
    }

    public function getMatrixServerUrl(): string {
        return $this->config->getAppValue(
            $this->appName,
            'matrix_server_url',
            'https://matrix.example.com'
        );
    }

    public function setMatrixServerUrl(string $url): void {
        $this->config->setAppValue($this->appName, 'matrix_server_url', $url);
    }

    public function getMatrixServerName(): string {
        return $this->config->getAppValue(
            $this->appName,
            'matrix_server_name',
            'matrix.example.com'
        );
    }

    public function setMatrixServerName(string $name): void {
        $this->config->setAppValue($this->appName, 'matrix_server_name', $name);
    }

    public function getElementCallUrl(): string {
        return $this->config->getAppValue(
            $this->appName,
            'element_call_url',
            'https://call.element.io'
        );
    }

    public function setElementCallUrl(string $url): void {
        $this->config->setAppValue($this->appName, 'element_call_url', $url);
    }

    public function getIdentityServerUrl(): string {
        return $this->config->getAppValue(
            $this->appName,
            'identity_server_url',
            'https://vector.im'
        );
    }

    public function setIdentityServerUrl(string $url): void {
        $this->config->setAppValue($this->appName, 'identity_server_url', $url);
    }

    public function getMatrixApplicationToken(): string {
        return $this->config->getAppValue(
            $this->appName,
            'matrix_app_token',
            ''
        );
    }

    public function setMatrixApplicationToken(string $token): void {
        $this->config->setAppValue($this->appName, 'matrix_app_token', $token);
    }

    public function getElementWebUrl(): string {
        return $this->config->getAppValue(
            $this->appName,
            'element_web_url',
            'https://app.element.io'
        );
    }

    public function setElementWebUrl(string $url): void {
        $this->config->setAppValue($this->appName, 'element_web_url', $url);
    }

    public function isMatrixConfigured(): bool {
        return !empty($this->getMatrixServerUrl()) && 
               !empty($this->getMatrixServerName()) &&
               !empty($this->getMatrixApplicationToken());
    }
}
