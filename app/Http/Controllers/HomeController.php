<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\MentoringField;

class HomeController extends Controller
{
    public function index()
    {
        return $this->renderPage('landing');
    }

    public function dashboard()
    {
        $roleName = auth()->user()->role?->role_name;

        $dashboardViews = [
            'Peserta' => 'dashboard.peserta',
            'Mentor' => 'dashboard.mentor',
            'Pengurus KSM' => 'dashboard.pengurus',
        ];

        abort_unless(isset($dashboardViews[$roleName]), 403, 'Role akun tidak memiliki dashboard.');

        return $this->renderPage($dashboardViews[$roleName], $roleName);
    }

    private function renderPage(string $view, ?string $dashboardRole = null)
    {
        try {
            $mentoringFields = MentoringField::take(4)->get();
            $publishedProjects = Project::with(['mentoringField', 'creator'])
                ->where('status', 'Published')
                ->latest('published_at')
                ->take(3)
                ->get();
        } catch (\Exception $e) {
            // Fallback berupa collection kosong jika migration / database belum diisi
            $mentoringFields = collect();
            $publishedProjects = collect();
        }

        return response()
            ->view($view, compact('mentoringFields', 'publishedProjects', 'dashboardRole'))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, private')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
}