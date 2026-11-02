<nav class="sidebar-menu" aria-label="Navigasi peserta">
    <a href="{{ route('dashboard.peserta') }}" class="sidebar-link {{ $activeMenu === 'dashboard' ? 'active' : '' }}" @if($activeMenu === 'dashboard') aria-current="page" @endif>
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
        Dashboard
    </a>
    <a href="{{ route('dashboard.peserta.project.create') }}" class="sidebar-link {{ $activeMenu === 'create' ? 'active' : '' }}" @if($activeMenu === 'create') aria-current="page" @endif>
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
        Ajukan Project Baru
    </a>
    <a href="{{ route('dashboard.peserta.projects.index') }}" class="sidebar-link {{ $activeMenu === 'project' ? 'active' : '' }}" @if($activeMenu === 'project') aria-current="page" @endif>
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
        Project
    </a>
    <a href="{{ route('dashboard.profile') }}" class="sidebar-link {{ $activeMenu === 'profile' ? 'active' : '' }}" @if($activeMenu === 'profile') aria-current="page" @endif>
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="4" stroke-width="2"></circle><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 21a8 8 0 0116 0"></path></svg>
        Profil
    </a>
</nav>
