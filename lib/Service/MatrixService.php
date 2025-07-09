<?php
declare(strict_types=1);

namespace OCA\NextcloudChat\Service;

use OCP\ILogger;
use OCP\IUser;
use OCP\IUserSession;

class MatrixService {
    private ConfigService $configService;
    private ILogger $logger;
    private IUserSession $userSession;

    public function __construct(
        ConfigService $configService,
        ILogger $logger,
        IUserSession $userSession
    ) {
        $this->configService = $configService;
        $this->logger = $logger;
        $this->userSession = $userSession;
    }

    public function getMatrixServerUrl(): string {
        return $this->configService->getMatrixServerUrl();
    }

    public function authenticateUser(): string {
        $user = $this->userSession->getUser();
        if (!$user instanceof IUser) {
            throw new \Exception('User not authenticated');
        }

        // Convert Nextcloud user to Matrix user
        $matrixUserId = '@' . $user->getUID() . ':' . $this->configService->getMatrixServerName();
        
        // Here you would implement the actual Matrix authentication
        // This could involve SSO, password authentication, or JWT tokens
        return $this->performMatrixLogin($matrixUserId, $user);
    }

    private function performMatrixLogin(string $matrixUserId, IUser $user): string {
        $serverUrl = $this->getMatrixServerUrl();
        
        // Implementation would depend on your Matrix server authentication method
        // For example, using application service registration or SSO
        
        $loginData = [
            'type' => 'm.login.application_service',
            'user' => $matrixUserId,
            // Additional authentication data
        ];

        $response = $this->makeMatrixRequest('POST', '/_matrix/client/r0/login', $loginData);
        
        if (isset($response['access_token'])) {
            return $response['access_token'];
        }
        
        throw new \Exception('Matrix authentication failed');
    }

    private function makeMatrixRequest(string $method, string $endpoint, array $data = []): array {
        $url = $this->getMatrixServerUrl() . $endpoint;
        
        $options = [
            'http' => [
                'method' => $method,
                'header' => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $this->configService->getMatrixApplicationToken()
                ],
                'content' => json_encode($data)
            ]
        ];

        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        
        if ($result === false) {
            throw new \Exception('Matrix API request failed');
        }
        
        return json_decode($result, true);
    }
}
