@extends('layouts.app')

@section('title', $project->title . ' - Showcase KSM Multimedia')

@section('content')
    <main class="showcase-detail-page">
        <div class="showcase-detail-container">
            <nav class="showcase-detail-breadcrumb" aria-label="Breadcrumb">
                <a href="{{ route('showcase') }}">Showcase</a>
                <span aria-hidden="true">&rsaquo;</span>
                <span>{{ $project->mentoringField?->field_name ?? 'Multimedia' }}</span>
                <span aria-hidden="true">&rsaquo;</span>
                <span class="showcase-detail-breadcrumb-current">{{ $project->title }}</span>
            </nav>

            <div class="showcase-detail-grid">
                <article class="showcase-detail-main">
                    <div class="showcase-detail-image">
                        @if ($project->thumbnail_display_url)
                            <img src="{{ $project->thumbnail_display_url }}" alt="Pratinjau {{ $project->title }}">
                        @else
                            <div class="showcase-detail-image-placeholder" role="img" aria-label="Thumbnail project belum tersedia">
                                <span>Thumbnail project belum tersedia</span>
                            </div>
                        @endif
                    </div>

                    <div class="showcase-detail-project-meta">
                        <span class="showcase-detail-project-id">Capstone ID: {{ $project->project_id }}</span>
                        @if ($project->published_at)
                            <span>Dipublikasikan {{ $project->published_at->format('d M Y') }}</span>
                        @endif
                    </div>

                    <section class="showcase-detail-description">
                        <h2>Deskripsi Singkat</h2>
                        <div>{{ $project->description ?: 'Belum ada deskripsi untuk project ini.' }}</div>
                    </section>
                </article>

                <aside class="showcase-detail-sidebar">
                    <div class="showcase-detail-summary">
                        <div class="showcase-detail-badges">
                            <span class="showcase-detail-category">{{ $project->mentoringField?->field_name ?? 'Multimedia' }}</span>
                            <span class="showcase-detail-verified">Project Terpublikasi</span>
                        </div>
                        <h1>{{ $project->title }}</h1>

                        <section class="showcase-detail-info">
                            <h2>Tim Pembuat</h2>
                            @if ($members->isNotEmpty())
                                <ul class="showcase-detail-team">
                                    @foreach ($members as $member)
                                        <li>
                                            <span class="showcase-detail-avatar" aria-hidden="true">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($member->name, 0, 2)) }}</span>
                                            <span>
                                                <strong>{{ $member->name }}</strong>
                                                @if ($member->member_role)
                                                    <small>{{ $member->member_role }}</small>
                                                @endif
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="showcase-detail-info-value">{{ $project->creator?->name ?? 'Tim KSM Multimedia' }}</p>
                            @endif
                        </section>

                        <section class="showcase-detail-info">
                            <h2>Periode Akademik</h2>
                            <p class="showcase-detail-info-value">{{ $project->period?->period_name ?? 'Tidak tersedia' }}</p>
                        </section>

                        @if ($tools->isNotEmpty())
                            <section class="showcase-detail-info">
                                <h2>Tools Digunakan</h2>
                                <ul class="showcase-detail-tools">
                                    @foreach ($tools as $tool)
                                        <li>{{ $tool }}</li>
                                    @endforeach
                                </ul>
                            </section>
                        @endif

                        @if ($projectUrl)
                            <a class="showcase-detail-primary-link" href="{{ $projectUrl }}" target="_blank" rel="noopener noreferrer">
                                Buka Demo / Repository <span aria-hidden="true">&nearr;</span>
                            </a>
                        @endif

                        <p class="showcase-detail-notice">Project ini telah melalui proses kurasi dan penilaian komite sidang KSM Multimedia.</p>
                    </div>
                </aside>
            </div>
        </div>
    </main>
@endsection
