<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\Submission;
use App\Models\User;
use App\Services\DashboardStatisticsService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;

/**
 * DashboardController handles the display of user-specific and admin dashboard views.
 */
class DashboardController extends Controller
{
    /**
     * @var DashboardStatisticsService
     */
    private DashboardStatisticsService $statisticsService;

    /**
     * Create a new controller instance.
     *
     * @param DashboardStatisticsService $statisticsService
     */
    public function __construct(DashboardStatisticsService $statisticsService)
    {
        $this->statisticsService = $statisticsService;
    }

    /**
     * Display the dashboard view with user-specific and admin statistics.
     *
     * @return View|Factory|Application
     */
    public function index(): View|Factory|Application
    {
        $formStats = $this->getUserFormStatistics();
        $adminStats = $this->getAdminStatistics();

        return view('dashboard', compact('formStats', 'adminStats'));
    }

    /**
     * Get statistics for forms owned by or assigned to the current user.
     *
     * @return Collection
     */
    private function getUserFormStatistics(): Collection
    {
        $forms = $this->getUserForms();

        return $forms->map(function (Form $form) {
            return [
                'form' => $form,
                'draft_count' => $this->statisticsService->getFormDraftCount($form),
                'submitted_count' => $this->statisticsService->getFormSubmittedCount($form),
            ];
        });
    }

    /**
     * Get forms owned by or assigned to the current user.
     *
     * @return Collection
     */
    private function getUserForms(): Collection
    {
        $createdForms = Auth::user()->forms()->latest();
        $assignedForms = Form::whereHas('appointedUsers', function($query) {
            $query->where('user_id', Auth::id());
        })->latest();

        return $createdForms->union($assignedForms)->get();
    }

    /**
     * Get admin statistics if the current user is an admin.
     *
     * @return array|null
     */
    private function getAdminStatistics(): ?array
    {
        if (!Auth::user()->isAdmin()) {
            return null;
        }

        return [
            'users' => $this->statisticsService->getUserStatistics(),
            'forms' => $this->statisticsService->getFormStatistics(),
            'submissions' => $this->statisticsService->getSubmissionStatistics(),
        ];
    }
} 