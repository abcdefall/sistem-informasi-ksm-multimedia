<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\MentoringField;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        try {
            // Ambil data dari database
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

        return view('landing', compact('mentoringFields', 'publishedProjects'));
    }
}