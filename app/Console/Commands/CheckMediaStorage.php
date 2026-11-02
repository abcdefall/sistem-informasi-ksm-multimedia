<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

#[Signature('media:check')]
#[Description('Check project media storage access.')]
class CheckMediaStorage extends Command
{
    public function handle(): int
    {
        $diskName = config('filesystems.project_media_disk', 'public');
        $diskConfig = config("filesystems.disks.{$diskName}", []);
        $diskConfig = is_array($diskConfig) ? $diskConfig : [];
        $path = 'projects/_healthcheck/test.txt';
        $contents = 'project media storage check';

        $this->line("Disk: {$diskName}");
        $this->line('Bucket: '.(filled($diskConfig['bucket'] ?? null) ? 'terisi' : 'kosong'));
        $this->line('Endpoint: '.(filled($diskConfig['endpoint'] ?? null) ? 'terisi' : 'kosong'));
        $this->line('Region: '.(filled($diskConfig['region'] ?? null) ? 'terisi' : 'kosong'));

        $successful = $this->runStep('put', function () use ($diskName, $path, $contents): void {
            if (! Storage::disk($diskName)->put($path, $contents)) {
                throw new RuntimeException('Storage put returned false.');
            }
        });

        $successful = $this->runStep('exists', function () use ($diskName, $path): void {
            if (! Storage::disk($diskName)->exists($path)) {
                throw new RuntimeException('The healthcheck file does not exist.');
            }
        }) && $successful;

        $successful = $this->runStep('get', function () use ($diskName, $path, $contents): void {
            if (Storage::disk($diskName)->get($path) !== $contents) {
                throw new RuntimeException('The healthcheck file contents do not match.');
            }
        }) && $successful;

        $successful = $this->runStep('delete', function () use ($diskName, $path): void {
            if (! Storage::disk($diskName)->delete($path)) {
                throw new RuntimeException('Storage delete returned false.');
            }
        }) && $successful;

        return $successful ? self::SUCCESS : self::FAILURE;
    }

    private function runStep(string $name, callable $operation): bool
    {
        try {
            $operation();
            $this->info("OK {$name}");

            return true;
        } catch (Throwable $exception) {
            $this->error("GAGAL {$name}: {$exception->getMessage()}");

            return false;
        }
    }
}
