<main class="mentoring-page">
    <section class="mentoring-hero">
        <div class="mentoring-container mentoring-hero-grid">
            <div class="mentoring-intro">
                <span class="mentoring-eyebrow"><span aria-hidden="true"></span>{{ $mentoringContext['eyebrow'] }}</span>
                <h1>{{ $mentoringContext['heading'] }}</h1>
                <p>{{ $mentoringContext['description'] }}</p>
                <a href="#program" class="mentoring-primary-link">Jelajahi program <span aria-hidden="true">&rarr;</span></a>
            </div>
            <div class="mentoring-highlights" aria-label="Keunggulan program mentoring">
                <article class="mentoring-highlight">
                    <span class="mentoring-highlight-icon" aria-hidden="true">01</span>
                    <div><h2>Kurikulum terarah</h2><p>Materi disusun mengikuti praktik dan kebutuhan tiap bidang.</p></div>
                </article>
                <article class="mentoring-highlight">
                    <span class="mentoring-highlight-icon mentoring-highlight-icon-warm" aria-hidden="true">02</span>
                    <div><h2>Bimbingan mentor</h2><p>Proses belajar didukung arahan dan evaluasi berkala.</p></div>
                </article>
                <article class="mentoring-highlight">
                    <span class="mentoring-highlight-icon mentoring-highlight-icon-blue" aria-hidden="true">03</span>
                    <div><h2>Portofolio capstone</h2><p>Hasil belajar diarahkan menjadi karya yang dapat dipresentasikan.</p></div>
                </article>
            </div>
        </div>
    </section>

    <section id="program" class="mentoring-programs">
        <div class="mentoring-container">
            <div class="mentoring-section-heading">
                <div>
                    <span class="mentoring-section-label">Kurikulum pilihan</span>
                    <h2>Jalur mentoring unggulan</h2>
                </div>
                <p>Temukan bidang yang paling sesuai dengan minat, lalu kembangkan keterampilan melalui proyek dan pendampingan.</p>
            </div>

            @php
                $programDetails = [
                    'Web Dev' => [
                        'title' => 'Web Development',
                        'category' => 'Rekayasa perangkat lunak',
                        'description' => 'Bangun aplikasi web modern dari perancangan antarmuka hingga implementasi backend dan integrasi layanan.',
                        'skills' => ['React', 'Vue', 'Node.js', 'REST API'],
                    ],
                    'Game Dev' => [
                        'title' => 'Game Development',
                        'category' => 'Sains interaktif',
                        'description' => 'Rancang pengalaman game interaktif melalui gameplay, pemrograman, level design, dan aset 3D.',
                        'skills' => ['Unity', 'C#', 'Unreal Engine', '3D'],
                    ],
                    'UI/UX' => [
                        'title' => 'UI/UX Design',
                        'category' => 'Interaksi manusia & komputer',
                        'description' => 'Pelajari riset pengguna, perancangan alur, prototyping, dan evaluasi usability untuk solusi digital.',
                        'skills' => ['Figma', 'Riset pengguna', 'Design system', 'Usability'],
                    ],
                    'Graphic Design' => [
                        'title' => 'Graphic Design',
                        'category' => 'Komunikasi visual & seni',
                        'description' => 'Kembangkan identitas visual, tipografi, ilustrasi, dan materi komunikasi untuk kebutuhan digital.',
                        'skills' => ['Illustrator', 'Photoshop', 'Tipografi', 'Branding'],
                    ],
                ];
            @endphp

            @if($mentoringFields->isEmpty())
                <div class="mentoring-empty">
                    <h3>Program mentoring sedang disiapkan</h3>
                    <p>Informasi jalur kurikulum akan ditampilkan di sini setelah tersedia.</p>
                </div>
            @else
                <div class="mentoring-card-grid">
                    @foreach($mentoringFields as $index => $field)
                        @php
                            $program = $programDetails[$field->field_name] ?? [
                                'title' => $field->field_name,
                                'category' => 'Bidang mentoring KSM Multimedia',
                                'description' => $field->description ?: 'Pelajari keterampilan dan praktik terbaik di bidang ' . $field->field_name . '.',
                                'skills' => ['Kurikulum terstruktur', 'Proyek capstone'],
                            ];
                        @endphp
                        <article class="mentoring-card">
                            <div class="mentoring-card-top">
                                <span class="mentoring-number">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>
                                <span class="mentoring-category">{{ $program['category'] }}</span>
                            </div>
                            <h3>{{ $program['title'] }}</h3>
                            <p>{{ $program['description'] }}</p>
                            <ul class="mentoring-tags" aria-label="Materi {{ $program['title'] }}">
                                @foreach($program['skills'] as $skill)
                                    <li>{{ $skill }}</li>
                                @endforeach
                            </ul>
                            <a href="{{ $mentoringContext['action_url'] }}" class="mentoring-card-link">
                                {{ $mentoringContext['action_label'] }} <span aria-hidden="true">&rarr;</span>
                            </a>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <section class="mentoring-cta-section">
        <div class="mentoring-container">
            <div class="mentoring-cta">
                <div>
                    <span class="mentoring-section-label">Langkah berikutnya</span>
                    <h2>Siap mengembangkan potensi multimedia Anda?</h2>
                    <p>Mulai perjalanan belajar dan bangun portofolio capstone bersama komunitas KSM Multimedia.</p>
                </div>
                <a href="{{ $mentoringContext['action_url'] }}" class="mentoring-cta-button">
                    {{ $mentoringContext['action_label'] }} <span aria-hidden="true">&rarr;</span>
                </a>
            </div>
        </div>
    </section>
</main>
