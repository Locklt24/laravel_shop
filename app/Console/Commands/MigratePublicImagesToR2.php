<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

#[Signature('storage:sync-public-images-to-r2 {--dry-run} {--overwrite}')]
#[Description('Copy local public image files to Cloudflare R2')]
class MigratePublicImagesToR2 extends Command
{
    private const IMAGE_EXTENSIONS = ['avif', 'gif', 'jpeg', 'jpg', 'png', 'svg', 'webp'];

    public function handle(): int
    {
        $r2Config = config('filesystems.disks.r2', []);
        $requiredConfiguration = [
            'key' => 'R2_ACCESS_KEY_ID',
            'secret' => 'R2_SECRET_ACCESS_KEY',
            'bucket' => 'R2_BUCKET',
            'endpoint' => 'R2_ENDPOINT',
            'url' => 'R2_URL',
        ];
        $missingConfiguration = [];

        foreach ($requiredConfiguration as $setting => $environmentVariable) {
            if (blank($r2Config[$setting] ?? null)) {
                $missingConfiguration[] = $environmentVariable;
            }
        }

        if ($missingConfiguration !== []) {
            $this->error('Configure these R2 environment variables before syncing: '.implode(', ', $missingConfiguration));

            return self::FAILURE;
        }

        $source = Storage::disk('public');
        $destination = Storage::disk('r2');
        $imagePaths = collect($source->allFiles())
            ->filter(fn (string $path): bool => in_array(
                strtolower(pathinfo($path, PATHINFO_EXTENSION)),
                self::IMAGE_EXTENSIONS,
                true
            ))
            ->values();

        if ($imagePaths->isEmpty()) {
            $this->info('No local public images found.');

            return self::SUCCESS;
        }

        $uploaded = 0;
        $wouldUpload = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($imagePaths as $path) {
            if (! $this->option('overwrite') && $destination->exists($path)) {
                $this->line("Skipped existing image: {$path}");
                $skipped++;

                continue;
            }

            if ($this->option('dry-run')) {
                $this->line("Would upload: {$path}");
                $wouldUpload++;

                continue;
            }

            $stream = $source->readStream($path);

            if (! is_resource($stream)) {
                $this->error("Could not read local image: {$path}");
                $failed++;

                continue;
            }

            try {
                $wasUploaded = $destination->writeStream($path, $stream);
            } finally {
                fclose($stream);
            }

            if (! $wasUploaded) {
                $this->error("Could not upload image to R2: {$path}");
                $failed++;

                continue;
            }

            $this->info("Uploaded: {$path}");
            $uploaded++;
        }

        if ($this->option('dry-run')) {
            $this->line("Dry run: {$wouldUpload} would upload, {$skipped} already exist, {$failed} failed.");
        } else {
            $this->line("Finished: {$uploaded} uploaded, {$skipped} skipped, {$failed} failed.");
        }

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
