<?php
declare(strict_types=1);

namespace OCA\NextcloudChat\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCA\NextcloudChat\Service\ElementService;

class ElementController extends Controller {
    private ElementService $elementService;

    public function __construct(
        string $appName,
        IRequest $request,
        ElementService $elementService
    ) {
        parent::__construct($appName, $request);
        $this->elementService = $elementService;
    }

    /**
     * @NoAdminRequired
     */
    public function getConfig(): JSONResponse {
        return new JSONResponse($this->elementService->getElementConfig());
    }

    /**
     * @NoAdminRequired
     */
    public function updateConfig(): JSONResponse {
        try {
            $config = $this->request->getParam('config');
            $this->elementService->updateElementConfig($config);
            return new JSONResponse(['success' => true]);
        } catch (\Exception $e) {
            return new JSONResponse(['error' => $e->getMessage()], 500);
        }
    }
}
