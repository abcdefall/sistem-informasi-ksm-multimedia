<nav class="sidebar-menu" aria-label="Navigasi mentor">
    <a href="{{ route('dashboard.mentor') }}" class="sidebar-link {{ $activeMenu === 'queue' ? 'active' : '' }}" @if($activeMenu === 'queue') aria-current="page" @endif>
        <svg class="w-5 h-5" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 4h14a2 2 0 012 2v14l-4-2-5 2-5-2-4 2V6a2 2 0 012-2z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 9h6M9 13h6"/></svg>
        <span>Antrian Review</span>
        @if ($waitingReviewCount + $revisionIncomingCount > 0)
            <span class="mentor-menu-count">{{ $waitingReviewCount + $revisionIncomingCount }}</span>
        @endif
    </a>
    <a href="{{ route('dashboard.mentor.evaluations') }}" class="sidebar-link {{ $activeMenu === 'evaluations' ? 'active' : '' }}" @if($activeMenu === 'evaluations') aria-current="page" @endif>
        <svg class="w-5 h-5" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>
        <span>Riwayat Evaluasi</span>
    </a>
    <a href="{{ route('dashboard.mentor.schedules') }}" class="sidebar-link {{ $activeMenu === 'schedules' ? 'active' : '' }}" @if($activeMenu === 'schedules') aria-current="page" @endif>
        <svg class="w-5 h-5" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3.5" y="5" width="17" height="16" rx="2" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7.5 3v4M16.5 3v4M4 10h16"/></svg>
        <span>Jadwal Mentoring</span>
    </a>
    <a href="{{ route('dashboard.profile') }}" class="sidebar-link {{ $activeMenu === 'profile' ? 'active' : '' }}" @if($activeMenu === 'profile') aria-current="page" @endif>
        <svg class="w-5 h-5" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="8" r="4" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 21a8 8 0 0116 0"/></svg>
        <span>Profil</span>
    </a>
</nav>
