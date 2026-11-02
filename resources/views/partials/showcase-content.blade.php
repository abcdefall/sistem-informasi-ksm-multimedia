@section('content')
    <main class="showcase-page">
        <div class="showcase-container">
            <header class="showcase-heading">
                <div>
                    <span class="showcase-eyebrow">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 5.5A1.5 1.5 0 0 1 5.5 4h13A1.5 1.5 0 0 1 20 5.5v13a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 4 18.5v-13Z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 8h8M8 12h8m-8 4h5"></path>
                        </svg>
                        {{ $showcaseContext['eyebrow'] }}
                    </span>
                    <h1>{{ $showcaseContext['heading'] }}</h1>
                    <p>{{ $showcaseContext['description'] }}</p>
                </div>
                @if($showcaseContext['action_url'])
                    <a href="{{ $showcaseContext['action_url'] }}" class="showcase-role-action">
                        {{ $showcaseContext['action_label'] }}
                        <span aria-hidden="true">&rarr;</span>
                    </a>
                @endif
            </header>

            <form action="{{ route('showcase') }}" method="GET" class="showcase-filters">
                <label class="showcase-search">
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="10.8" cy="10.8" r="6.3" stroke-width="1.8"></circle>
                        <path stroke-linecap="round" stroke-width="1.8" d="m15.5 15.5 4 4"></path>
                    </svg>
                    <input
                        type="search"
                        name="q"
                        value="{{ $filters['q'] ?? '' }}"
                        placeholder="Cari karya, topik, atau nama tim..."
                        aria-label="Cari karya, topik, atau nama tim"
                    >
                </label>
                <button type="submit" class="showcase-search-submit">Cari</button>
                <nav class="showcase-category-filters" aria-label="Filter bidang karya">
                    <a href="{{ route('showcase', array_filter(['q' => $filters['q'] ?? null])) }}"
                        class="showcase-filter-chip {{ empty($filters['field']) ? 'is-active' : '' }}">Semua</a>
                    @foreach($mentoringFields as $field)
                        <a href="{{ route('showcase', array_filter(['q' => $filters['q'] ?? null, 'field' => $field->field_id])) }}"
                            class="showcase-filter-chip {{ ($filters['field'] ?? null) === $field->field_id ? 'is-active' : '' }}">
                            {{ $field->field_name }}
                        </a>
                    @endforeach
                </nav>
            </form>

            @if($projects->isEmpty())
                <div class="showcase-empty">
                    <h2>Karya belum ditemukan</h2>
                    <p>Coba ubah kata kunci atau pilih bidang lainnya.</p>
                    @if(!empty($filters))
                        <a href="{{ route('showcase') }}">Hapus filter</a>
                    @endif
                </div>
            @else
                <section class="showcase-grid" aria-label="Daftar karya yang dipublikasikan">
                    @foreach($projects as $project)
                        @php
                            $projectMembers = $membersByProject->get($project->project_id, collect());
                            $creatorName = $project->creator?->name ?? 'Tim KSM Multimedia';
                        @endphp
                        <article class="showcase-project-card">
                            <div class="showcase-project-image">
                                @if($project->thumbnail_url)
                                    <img src="{{ $project->thumbnail_display_url }}" alt="Pratinjau {{ $project->title }}" loading="lazy">
                                @else
                                    <div class="showcase-image-placeholder">
                                        <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m3 16 5-5 4 4 3-3 6 6M5 20h14a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2Z"></path>
                                            <circle cx="8.5" cy="8.5" r="1.5" stroke-width="1.5"></circle>
                                        </svg>
                                    </div>
                                @endif
                                <span class="showcase-project-badge">{{ $project->mentoringField?->field_name ?? 'Multimedia' }}</span>
                            </div>
                            <div class="showcase-project-content">
                                <div class="showcase-project-meta">
                                    <span>{{ $project->project_id }}</span>
                                    @if($project->period?->period_name)
                                        <span aria-hidden="true">&bull;</span>
                                        <span>{{ $project->period->period_name }}</span>
                                    @endif
                                </div>
                                <h2 title="{{ $project->title }}">
                                    <a href="{{ route('showcase.show', $project->project_id) }}">{{ $project->title }}</a>
                                </h2>
                                <p class="showcase-project-description">
                                    {{ $project->description ?: 'Karya capstone mahasiswa KSM Multimedia.' }}
                                </p>
                                <div class="showcase-project-footer">
                                    <div class="showcase-author-avatar" aria-hidden="true">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($creatorName, 0, 2)) }}</div>
                                    <div class="showcase-author">
                                        <span>{{ $projectMembers->isNotEmpty() ? $projectMembers->take(2)->implode(', ') : $creatorName }}@if($projectMembers->count() > 2) +{{ $projectMembers->count() - 2 }} lainnya @endif</span>
                                        <small>{{ $project->mentoringField?->field_name ?? 'KSM Multimedia' }}</small>
                                    </div>
                                    <a href="{{ route('showcase.show', $project->project_id) }}" class="showcase-project-link" aria-label="Lihat detail {{ $project->title }}">
                                        Detail <span aria-hidden="true">&rarr;</span>
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </section>

                @if($projects->hasPages())
                    <nav class="showcase-pagination" aria-label="Navigasi halaman showcase">
                        {{ $projects->onEachSide(1)->links() }}
                    </nav>
                @endif
            @endif
        </div>
    </main>
@endsection
