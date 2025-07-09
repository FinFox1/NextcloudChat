<?php
declare(strict_types=1);

namespace OCA\NextcloudChat\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCA\NextcloudChat\Service\MatrixService;
use OCA\NextcloudChat\Service\ElementService;
use OCA\NextcloudChat\Service\ConfigService;

class ApiController extends Controller {
    private MatrixService $matrixService;
    private ElementService $elementService;
    private ConfigService $configService;

    public function __construct(
        string $appName,
        IRequest $request,
        MatrixService $matrixService,
        ElementService $elementService,
        ConfigService $configService
    ) {
        parent::__construct($appName, $request);
        $this->matrixService = $matrixService;
        $this->elementService = $elementService;
        $this->configService = $configService;
    }

    /**
     * @NoAdminRequired
     */
    public function config(): JSONResponse {
        return new JSONResponse([
            'element_config' => $this->elementService->getElementConfig(),
            'matrix_server' => $this->matrixService->getMatrixServerUrl(),
            'features' => [
                'element_call' => true,
                'federation' => true,
                'e2e_encryption' => true
            ]
        ]);
    }

    /**
     * @NoAdminRequired
     */
    public function getRooms(): JSONResponse {
        try {
            $rooms = $this->matrixService->getUserRooms();
            return new JSONResponse(['rooms' => $rooms]);
        } catch (\Exception $e) {
            return new JSONResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @NoAdminRequired
     */
    public function createRoom(): JSONResponse {
        try {
            $name = $this->request->getParam('name');
            $topic = $this->request->getParam('topic', '');
            $isPublic = $this->request->getParam('public', false);
            
            $roomId = $this->matrixService->createRoom($name, $topic, $isPublic);
            return new JSONResponse(['room_id' => $roomId]);
        } catch (\Exception $e) {
            return new JSONResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @NoAdminRequired
     */
    public function joinRoom(string $roomId): JSONResponse {
        try {
            $this->matrixService->joinRoom($roomId);
            return new JSONResponse(['success' => true]);
        } catch (\Exception $e) {
            return new JSONResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @NoAdminRequired
     */
    public function leaveRoom(string $roomId): JSONResponse {
        try {
            $this->matrixService->leaveRoom($roomId);
            return new JSONResponse(['success' => true]);
        } catch (\Exception $e) {
            return new JSONResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @NoAdminRequired
     */
    public function startCall(string $roomId): JSONResponse {
        try {
            $callUrl = $this->elementService->startCall($roomId);
            return new JSONResponse(['call_url' => $callUrl]);
        } catch (\Exception $e) {
            return new JSONResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @NoAdminRequired
     */
    public function endCall(string $roomId): JSONResponse {
        try {
            $this->elementService->endCall($roomId);
            return new JSONResponse(['success' => true]);
        } catch (\Exception $e) {
            return new JSONResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @NoAdminRequired
     */
    public function getUsers(): JSONResponse {
        try {
            $users = $this->matrixService->getKnownUsers();
            return new JSONResponse(['users' => $users]);
        } catch (\Exception $e) {
            return new JSONResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @NoAdminRequired
     */
    public function inviteUser(string $roomId): JSONResponse {
        try {
            $userId = $this->request->getParam('user_id');
            $this->matrixService->inviteUserToRoom($roomId, $userId);
            return new JSONResponse(['success' => true]);
        } catch (\Exception $e) {
            return new JSONResponse(['error' => $e->getMessage()], 500);
        }
    }
}
