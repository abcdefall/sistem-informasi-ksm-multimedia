@if ($mentor)
    <section class="mentor-card" aria-label="Mentor pembimbing">
        <span class="mentor-card-icon" aria-hidden="true">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6a3 3 0 00-3 3v1a3 3 0 006 0V9a3 3 0 00-3-3zm-6 6v-2a6 6 0 1112 0v2m-2 6h2a2 2 0 002-2v-4a2 2 0 00-2-2h-1m-10 8H7a2 2 0 01-2-2v-4a2 2 0 012-2h1m1 7h1m6 0h1"></path></svg>
        </span>
        <div class="min-w-0">
            <p class="mentor-card-label">Mentor Pembimbing</p>
            <p class="mentor-card-name">{{ $mentor->name }}</p>
            <a href="mailto:{{ $mentor->email }}" class="mentor-card-email">{{ $mentor->email }}</a>
        </div>
    </section>
@endif
