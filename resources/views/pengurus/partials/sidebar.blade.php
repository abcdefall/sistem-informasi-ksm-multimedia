<aside class="participant-sidebar pengurus-sidebar">
    <div class="participant-sidebar-shell pengurus-sidebar-shell">
        <nav class="sidebar-menu" aria-label="Navigasi pengurus">
            <a href="{{ route('dashboard.pengurus') }}" class="sidebar-link {{ $activeMenu === 'dashboard' ? 'active' : '' }}" @if ($activeMenu === 'dashboard') aria-current="page" @endif>
                <svg class="h-5 w-5" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="3" width="7" height="7" rx="1" stroke-width="2"/><rect x="14" y="3" width="7" height="7" rx="1" stroke-width="2"/><rect x="3" y="14" width="7" height="7" rx="1" stroke-width="2"/><rect x="14" y="14" width="7" height="7" rx="1" stroke-width="2"/></svg>
                <span>Dashboard &amp; Monitoring</span>
            </a>
            <a href="{{ route('dashboard.pengurus.approvals') }}" class="sidebar-link {{ $activeMenu === 'approvals' ? 'active' : '' }}" @if ($activeMenu === 'approvals') aria-current="page" @endif>
                <svg class="h-5 w-5" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 11l2 2 4-4m5-5v6a8 8 0 11-4-6.9"/></svg>
                <span>Approval Publikasi</span>
                @if ($pendingApprovalCount > 0)
                    <span class="mentor-menu-count">{{ $pendingApprovalCount }}</span>
                @endif
            </a>
            <a href="{{ route('dashboard.pengurus.audit') }}" class="sidebar-link {{ $activeMenu === 'audit' ? 'active' : '' }}" @if ($activeMenu === 'audit') aria-current="page" @endif>
                <svg class="h-5 w-5" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>
                <span>Log Audit Trail</span>
            </a>
            <a href="{{ route('dashboard.pengurus.users') }}" class="sidebar-link {{ $activeMenu === 'users' ? 'active' : '' }}" @if ($activeMenu === 'users') aria-current="page" @endif>
                <svg class="h-5 w-5" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="9" cy="8" r="3" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 20v-1a6 6 0 0112 0v1M16 11a3 3 0 100-6m2 9a5 5 0 013 4v2"/></svg>
                <span>Kelola Pengguna</span>
            </a>
            <a href="{{ route('dashboard.pengurus.settings') }}" class="sidebar-link {{ $activeMenu === 'settings' ? 'active' : '' }}" @if ($activeMenu === 'settings') aria-current="page" @endif>
                <svg class="h-5 w-5" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10 3.5h4l.55 2.35a6.5 6.5 0 011.5.87l2.28-.78 2 3.46-1.73 1.72a6.5 6.5 0 010 1.76l1.73 1.72-2 3.46-2.28-.78a6.5 6.5 0 01-1.5.87L14 20.5h-4l-.55-2.35a6.5 6.5 0 01-1.5-.87l-2.28.78-2-3.46 1.73-1.72a6.5 6.5 0 010-1.76L3.67 9.4l2-3.46 2.28.78a6.5 6.5 0 011.5-.87L10 3.5z"/>
                    <circle cx="12" cy="12" r="3" stroke-width="1.8"/>
                </svg>
                <span>Pengaturan</span>
            </a>
        </nav>

        @if ($activeMenu === 'audit')
            <section class="pengurus-system-card" aria-label="Status integritas audit">
                <p class="flex items-center gap-2 text-xs font-bold text-ksm">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3l8 4v5c0 5-3.5 8-8 9-4.5-1-8-4-8-9V7l8-4z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/></svg>
                    Immutable Integrity
                </p>
                <p class="mt-2 text-[0.65rem] leading-relaxed text-slate-500">Semua perubahan tercatat dan hanya dapat ditambah sebagai log audit.</p>
            </section>
        @else
            <section class="pengurus-system-card mt-auto" aria-label="Informasi dashboard">
                <p class="text-xs font-bold text-slate-700">Periode Akademik</p>
                <p class="mt-1 text-xs text-slate-500">{{ ($activePeriod ?? null) ?: 'Periode aktif KSM' }}</p>
                <div class="mt-3 flex items-center justify-between border-t border-slate-200 pt-3 text-[0.65rem]">
                    <span class="text-slate-500">Persetujuan tertunda</span>
                    <strong class="text-ksm">{{ $pendingApprovalCount }} project</strong>
                </div>
            </section>
        @endif
    </div>
</aside>
