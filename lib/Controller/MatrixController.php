<?php
declare(strict_types=1);

namespace OCA\NextcloudChat\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCA\NextcloudChat\Service\MatrixService;
use OCA\NextcloudChat\Service\ElementService;

class MatrixController extends Controller {
    private MatrixService $matrixService;
    private ElementService $elementService;

    public function __construct(
        string $appName,
        IRequest $request,
        MatrixService $matrixService,
        ElementService $elementService
    ) {
        parent::__construct($appName, $request);
        $this->matrixService = $matrixService;
        $this->elementService = $elementService;
    }

    /**
     * @NoAdminRequired
     */
    public function getConfig(): JSONResponse {
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
    public function authenticate(): JSONResponse {
        try {
            $accessToken = $this->matrixService->authenticateUser();
            return new JSONResponse(['access_token' => $accessToken]);
        } catch (\Exception $e) {
            return new JSONResponse(['error' => $e->getMessage()], 500);
        }
    }
}
