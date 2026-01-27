<?php
/**
 * Client Activity Controller
 * Handles admin interface for activity tracking
 */

require_once __DIR__ . '/../../models/clientactivity.php';
require_once __DIR__ . '/../../includes/viewrenderer.php';

class ActivityController {
    protected $activityModel;
    protected $view;

    public function __construct() {
        $this->activityModel = new ClientActivity();
        $this->view = new ViewRenderer();
    }

    public function index() {
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;
        
        $activities = $this->activityModel->getActivities(
            null, // clientId
            50,   // limit
            $startDate,
            $endDate
        );
        
        $this->view->render('admin/activity/index', [
            'activities' => $activities,
            'title' => 'Client Activities',
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
    }

    public function client($clientId) {
        $activities = $this->activityModel->getActivities($clientId);
        $this->view->render('admin/activity/client', [
            'activities' => $activities,
            'clientId' => $clientId,
            'title' => 'Client Activity Log'
        ]);
    }

    public function dashboardWidget() {
        $activities = $this->activityModel->getRecentActivities(5);
        return $this->view->renderPartial('admin/activity/widget', [
            'activities' => $activities
        ]);
    }
}
