<?php
namespace Core;

require_once __DIR__ . '/controller.php';

class PageBuilderController extends Controller {
    protected $contentModel;
    protected $versionModel;

    public function __construct() {
        parent::__construct();
        require_once __DIR__ . '/../../models/ContentModel.php';
        require_once __DIR__ . '/../../models/ContentVersionModel.php';
        $this->contentModel = new \ContentModel();
        $this->versionModel = new \ContentVersionModel();
    }

    /**
     * Create a new page
     */
    public function create() {
        $data = $this->request->getParsedBody();
        
        try {
            $pageId = $this->contentModel->create($data);
            $versionId = $this->versionModel->createInitialVersion($pageId, $data);
            
            return $this->response->json([
                'success' => true,
                'page_id' => $pageId,
                'version_id' => $versionId
            ]);
        } catch (\Exception $e) {
            return $this->response->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get page content
     */
    public function read($id) {
        try {
            $content = $this->contentModel->getById($id);
            $versions = $this->versionModel->getByPageId($id);
            
            return $this->response->json([
                'success' => true,
                'content' => $content,
                'versions' => $versions
            ]);
        } catch (\Exception $e) {
            return $this->response->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update page content
     */
    public function update($id) {
        $data = $this->request->getParsedBody();
        
        try {
            $this->contentModel->update($id, $data);
            $versionId = $this->versionModel->createNewVersion($id, $data);
            
            return $this->response->json([
                'success' => true,
                'version_id' => $versionId
            ]);
        } catch (\Exception $e) {
            return $this->response->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete page
     */
    public function delete($id) {
        try {
            $this->contentModel->delete($id);
            $this->versionModel->deleteByPageId($id);
            
            return $this->response->json(['success' => true]);
        } catch (\Exception $e) {
            return $this->response->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
