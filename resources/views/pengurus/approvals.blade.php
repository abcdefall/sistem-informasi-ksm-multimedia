<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approval Publikasi - KSM Multimedia</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @vite(['resources/css/landing.css', 'resources/css/dashboard.css'])
</head>
<body class="font-sans antialiased text-gray-800 flex flex-col min-h-screen">
    @include('pengurus.partials.navbar')

    <div class="participant-layout pengurus-layout">
        @include('pengurus.partials.sidebar')

        <main class="participant-content mentor-content pengurus-content">
            <header class="mb-5 flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
                <div>
                    <a href="{{ route('dashboard.pengurus') }}" class="text-xs font-semibold text-ksm hover:underline">← Dashboard Monitoring</a>
                    <h1 class="mt-2 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">Approval Publikasi</h1>
                    <p class="mt-1 max-w-2xl text-sm text-slate-500">Tinjau project yang telah direkomendasikan mentor sebelum disetujui dan diterbitkan ke showcase.</p>
                </div>
                <span class="rounded-full border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-800">{{ $pendingApprovalCount }} menunggu persetujuan</span>
            </header>

            @if (session('status'))
                <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                    @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
                </div>
            @endif

            <form method="GET" action="{{ route('dashboard.pengurus.approvals') }}" class="mentor-filter-bar mb-4">
                <label class="mentor-search">
                    <svg class="h-4 w-4 shrink-0 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.8" stroke-width="1.8"/><path stroke-linecap="round" stroke-width="1.8" d="m16 16 4 4"/></svg>
                    <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Cari judul, ID project, atau peserta" aria-label="Cari project">
                </label>
                <div class="pengurus-filter-form">
                    <label>
                        <span class="sr-only">Filter bidang</span>
                        <select name="field" class="mentor-status-select">
                            <option value="">Semua Bidang</option>
                            @foreach ($fields as $field)
                                <option value="{{ $field->field_id }}" @selected(($filters['field'] ?? '') === $field->field_id)>{{ $field->field_name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <button type="submit" class="mentor-filter-button" aria-label="Terapkan filter" title="Terapkan filter">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-width="1.8" d="M4 6h16M7 12h10m-7 6h4"/></svg>
                    </button>
                </div>
            </form>

            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="mentor-review-table pengurus-approval-table w-full min-w-[820px] text-left">
                        <thead>
                            <tr>
                                <th>Judul &amp; Identitas Capstone</th>
                                <th>Bidang</th>
                                <th>Tim Pengusul</th>
                                <th>Rekomendasi</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($projects as $project)
                                @php($members = $membersByProject->get($project->project_id, collect()))
                                <tr>
                                    <td>
                                        <p class="font-bold text-slate-900">{{ $project->title }}</p>
                                        <p class="mt-1 text-xs text-slate-500">ID: {{ $project->project_id }}</p>
                                    </td>
                                    <td><span class="mentor-field-badge">{{ $project->mentoringField?->field_name ?? 'Belum ditentukan' }}</span></td>
                                    <td>
                                        <p class="text-xs font-semibold text-slate-700">{{ $members->pluck('name')->join(', ') ?: $project->creator?->name }}</p>
                                        <p class="mt-1 text-[0.65rem] text-slate-500">Pengaju: {{ $project->creator?->name }}</p>
                                    </td>
                                    <td><span class="mentor-status-badge pengurus-status-approved">Lolos evaluasi mentor</span></td>
                                    <td class="text-right">
                                        <a href="{{ route('dashboard.pengurus.approval.show', $project->project_id) }}" class="pengurus-approval-button">Review &amp; Approval <span aria-hidden="true">→</span></a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-6 py-12 text-center text-sm text-slate-500">Tidak ada project yang menunggu persetujuan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="flex flex-col justify-between gap-3 border-t border-slate-100 px-4 py-3 text-xs text-slate-500 sm:flex-row sm:items-center">
                    <span>Menampilkan {{ $projects->firstItem() ?? 0 }}–{{ $projects->lastItem() ?? 0 }} dari {{ $projects->total() }} project</span>
                    {{ $projects->links() }}
                </div>
            </section>
        </main>
    </div>

    @include('pengurus.partials.footer')
</body>
</html>
