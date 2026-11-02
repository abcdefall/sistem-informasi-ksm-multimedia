<?php

use Illuminate\Support\Facades\Storage;

it('checks the public media disk and removes its healthcheck file', function () {
    config()->set('filesystems.project_media_disk', 'public');
    Storage::fake('public');

    $this->artisan('media:check')
        ->expectsOutputToContain('Disk: public')
        ->expectsOutputToContain('OK put')
        ->expectsOutputToContain('OK exists')
        ->expectsOutputToContain('OK get')
        ->expectsOutputToContain('OK delete')
        ->assertSuccessful();

    Storage::disk('public')->assertMissing('projects/_healthcheck/test.txt');
});
