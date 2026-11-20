<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SyncPublicImagesToR2CommandTest extends TestCase
{
    public function test_copies_public_images_to_r2_and_ignores_non_image_files(): void
    {
        $this->configureR2();
        Storage::fake('public');
        Storage::fake('r2');
        Storage::disk('public')->put('products/old.svg', '<svg>old</svg>');
        Storage::disk('public')->put('products/readme.txt', 'not an image');

        $this->artisan('storage:sync-public-images-to-r2')
            ->assertSuccessful();

        Storage::disk('r2')->assertExists('products/old.svg');
        $this->assertSame('<svg>old</svg>', Storage::disk('r2')->get('products/old.svg'));
        Storage::disk('r2')->assertMissing('products/readme.txt');
        Storage::disk('public')->assertExists('products/old.svg');
    }

    public function test_dry_run_does_not_copy_local_images(): void
    {
        $this->configureR2();
        Storage::fake('public');
        Storage::fake('r2');
        Storage::disk('public')->put('products/old.webp', 'old image');

        $this->artisan('storage:sync-public-images-to-r2', ['--dry-run' => true])
            ->assertSuccessful();

        Storage::disk('r2')->assertMissing('products/old.webp');
        Storage::disk('public')->assertExists('products/old.webp');
    }

    public function test_does_not_overwrite_an_existing_r2_image_by_default(): void
    {
        $this->configureR2();
        Storage::fake('public');
        Storage::fake('r2');
        Storage::disk('public')->put('products/old.webp', 'local image');
        Storage::disk('r2')->put('products/old.webp', 'existing cloud image');

        $this->artisan('storage:sync-public-images-to-r2')
            ->assertSuccessful();

        $this->assertSame('existing cloud image', Storage::disk('r2')->get('products/old.webp'));
    }

    private function configureR2(): void
    {
        config([
            'filesystems.disks.r2.key' => 'test-key',
            'filesystems.disks.r2.secret' => 'test-secret',
            'filesystems.disks.r2.bucket' => 'test-bucket',
            'filesystems.disks.r2.endpoint' => 'https://example.test',
            'filesystems.disks.r2.url' => 'https://images.example.test',
        ]);
    }
}
