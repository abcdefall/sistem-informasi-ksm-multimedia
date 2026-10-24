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
            <span class="sidebar-link" aria-disabled="true" title="Pengelolaan pengguna belum tersedia">
                <svg class="h-5 w-5" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="9" cy="8" r="3" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 20v-1a6 6 0 0112 0v1M16 11a3 3 0 100-6m2 9a5 5 0 013 4v2"/></svg>
                <span>Kelola Pengguna</span>
            </span>
            <span class="sidebar-link" aria-disabled="true" title="Pengaturan belum tersedia">
                <svg class="h-5 w-5" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8.5a3.5 3.5 0 100 7 3.5 3.5 0 000-7z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.4 15a1.7 1.7 0 00.3 1.9l.1.1-1.7 2.9-.2-.1a1.7 1.7 0 00-1.9.3l-.1.1h-3.4l-.1-.2a1.7 1.7 0 00-1.6-1.1 1.7 1.7 0 00-.9.3l-.2.1-2.9-1.7.1-.2a1.7 1.7 0 00-.3-1.9l-.1-.1v-3.4l.2-.1a1.7 1.7 0 001.1-1.6 1.7 1.7 0 00-.3-.9l-.1-.2 1.7-2.9.2.1a1.7 1.7 0 001.9-.3l.1-.1h3.4l.1.2a1.7 1.7 0 001.6 1.1 1.7 1.7 0 00.9-.3l.2-.1 2.9 1.7-.1.2a1.7 1.7 0 00.3 1.9l.1.1z"/></svg>
                <span>Pengaturan</span>
            </span>
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
