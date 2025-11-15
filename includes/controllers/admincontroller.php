<?php
namespace Includes\Controllers;

use Includes\Routing\Request;
use Includes\Auth\Auth;
use Core\Response;
use Includes\Middleware\AuthenticateMiddleware;

/**
 * Base Admin Controller
 * Provides common functionality for all admin controllers
 */
class AdminController extends Controller
{
    protected $authMiddleware;
    protected $adminViewsPath = 'admin/';
    protected $adminAssetsPath = '/assets/admin/';

    public function __construct(Request $request, Auth $auth)
    {
        parent::__construct($request, $auth);
        $this->authMiddleware = new AuthenticateMiddleware($auth);
        
        // Verify admin access on all admin routes
        $this->verifyAdminAccess();
    }

    /**
     * Verify user has admin privileges
     */
    protected function verifyAdminAccess()
    {
        if (!$this->auth->user() || !$this->auth->user()->isAdmin()) {
            return Response::redirect('/login')
                ->withError('Admin access required');
        }
    }

    /**
     * Render admin view with common admin layout
     */
    protected function adminView($view, $data = [])
    {
        $data = array_merge($data, [
            'adminAssetsPath' => $this->adminAssetsPath,
            'currentUser' => $this->auth->user(),
            'csrfToken' => $this->auth->getCsrfToken()
        ]);

        return $this->view($this->adminViewsPath . $view, $data);
    }

    /**
     * Admin dashboard
     */
    public function dashboard()
    {
        return $this->authMiddleware->handle($this->request, function() {
            return $this->adminView('dashboard', [
                'title' => 'Admin Dashboard',
                'stats' => $this->getDashboardStats()
            ]);
        });
    }

    /**
     * Get dashboard statistics
     */
    protected function getDashboardStats()
    {
        return [
            'totalUsers' => User::count(),
            'totalContent' => Content::count(),
            'recentActivity' => ActivityLog::recent(5)
        ];
    }
}
