<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pengguna - KSM Multimedia</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @vite(['resources/css/landing.css', 'resources/css/dashboard.css'])
</head>
<body class="font-sans antialiased text-gray-800 flex flex-col min-h-screen">
    @include('pengurus.partials.navbar')

    <div class="participant-layout pengurus-layout">
        @include('pengurus.partials.sidebar')

        <main class="participant-content mentor-content pengurus-content">
            <header class="mb-5">
                <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">Kelola Pengguna</h1>
                <p class="mt-1 max-w-3xl text-sm text-slate-500">Kelola akun Peserta, Mentor, dan Pengurus KSM yang terdaftar di website. Kata sandi tidak ditampilkan atau diubah dari halaman ini.</p>
            </header>

            @if (session('status'))
                <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                    @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
                </div>
            @endif

            <section class="mb-5 grid grid-cols-1 gap-3 sm:grid-cols-3" aria-label="Ringkasan pengguna">
                @foreach (['all' => 'Total Akun', 'active' => 'Akun Aktif', 'inactive' => 'Akun Nonaktif'] as $key => $label)
                    <article class="mentor-stat-card">
                        <div>
                            <p class="text-xs font-medium text-slate-500">{{ $label }}</p>
                            <p class="mt-1 text-2xl font-bold text-slate-900">{{ $userCounts[$key] }}</p>
                        </div>
                    </article>
                @endforeach
            </section>

            <form method="GET" action="{{ route('dashboard.pengurus.users') }}" class="mentor-filter-bar mb-4">
                <label class="mentor-search">
                    <svg class="h-4 w-4 shrink-0 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.8" stroke-width="1.8"/><path stroke-linecap="round" stroke-width="1.8" d="m16 16 4 4"/></svg>
                    <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Cari nama, email, atau ID akun" aria-label="Cari pengguna">
                </label>
                <div class="pengurus-filter-form">
                    <label>
                        <span class="sr-only">Filter role</span>
                        <select name="role" class="mentor-status-select">
                            <option value="">Semua Role</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->role_id }}" @selected(($filters['role'] ?? '') === $role->role_id)>{{ $role->role_name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>
                        <span class="sr-only">Filter status akun</span>
                        <select name="status" class="mentor-status-select">
                            <option value="">Semua Status</option>
                            <option value="active" @selected(($filters['status'] ?? '') === 'active')>Aktif</option>
                            <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Nonaktif</option>
                        </select>
                    </label>
                    <button type="submit" class="mentor-filter-button" aria-label="Terapkan filter" title="Terapkan filter">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-width="1.8" d="M4 6h16M7 12h10m-7 6h4"/></svg>
                    </button>
                </div>
            </form>

            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-label="Daftar pengguna">
                <div class="overflow-x-auto">
                    <table class="mentor-review-table w-full min-w-[980px] text-left">
                        <thead>
                            <tr>
                                <th>Pengguna</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Terdaftar</th>
                                <th>Kelola Akun</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($users as $user)
                                <tr>
                                    <td>
                                        <p class="font-bold text-slate-900">{{ $user->name }}</p>
                                        <p class="mt-1 text-xs text-slate-500">{{ $user->email }}</p>
                                        <p class="mt-1 text-[0.65rem] text-slate-400">{{ $user->user_id }}</p>
                                    </td>
                                    <td><span class="mentor-field-badge">{{ $user->role?->role_name }}</span></td>
                                    <td>
                                        <span class="mentor-status-badge {{ $user->is_active ? 'mentor-status-submitted' : 'mentor-status-resubmitted' }}">
                                            {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap text-slate-600">{{ $user->created_at?->format('d M Y') }}</td>
                                    <td>
                                        <form id="update-user-{{ $user->user_id }}" method="POST" action="{{ route('dashboard.pengurus.users.update', $user->user_id) }}" class="grid gap-2">
                                            @csrf
                                            @method('PUT')
                                            <label class="sr-only" for="name-{{ $user->user_id }}">Nama pengguna</label>
                                            <input id="name-{{ $user->user_id }}" name="name" value="{{ $user->name }}" maxlength="255" required class="w-full rounded-md border border-slate-300 px-2 py-1 text-xs">
                                            <label class="sr-only" for="email-{{ $user->user_id }}">Email</label>
                                            <input id="email-{{ $user->user_id }}" type="email" name="email" value="{{ $user->email }}" maxlength="255" required class="w-full rounded-md border border-slate-300 px-2 py-1 text-xs">
                                            <label class="sr-only" for="role-{{ $user->user_id }}">Role</label>
                                            <select id="role-{{ $user->user_id }}" name="role_id" required class="w-full rounded-md border border-slate-300 bg-white px-2 py-1 text-xs" @disabled($user->user_id === auth()->id())>
                                                @foreach ($roles as $role)
                                                    <option value="{{ $role->role_id }}" @selected($user->role_id === $role->role_id)>{{ $role->role_name }}</option>
                                                @endforeach
                                            </select>
                                            @if($user->user_id === auth()->id())
                                                <input type="hidden" name="role_id" value="{{ $user->role_id }}">
                                                <span class="text-[0.65rem] text-slate-500">Role akun sendiri tidak dapat diubah.</span>
                                            @endif
                                        </form>
                                    </td>
                                    <td class="text-right">
                                        <div class="flex flex-col items-end gap-2">
                                            <button type="submit" form="update-user-{{ $user->user_id }}" class="pengurus-approval-button">Simpan</button>
                                            @if($user->user_id !== auth()->id())
                                                <form method="POST" action="{{ route('dashboard.pengurus.users.status', $user->user_id) }}" onsubmit="return confirm('Ubah status akun ini?');">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="rounded-md border border-slate-300 px-2 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                                        {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-[0.65rem] text-slate-400">Akun Anda</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center">
                                        <p class="font-semibold text-slate-700">Pengguna tidak ditemukan.</p>
                                        <p class="mt-1 text-sm text-slate-500">Coba ubah kata kunci atau filter.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            @if($users->hasPages())
                <div class="mt-5">{{ $users->links() }}</div>
            @endif
        </main>
    </div>

    @include('pengurus.partials.footer')
</body>
</html>
