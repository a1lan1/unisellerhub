<?php

declare(strict_types=1);

use App\Modules\User\Domain\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

use function Pest\Laravel\get;

beforeEach(function (): void {
    Storage::fake('reports');
    Storage::fake('public');
    $this->user = User::factory()->withBaseRoles()->create();
    $this->actingAs($this->user);
});

it('can download an exported file with a valid signed URL from reports disk', function (): void {
    $filePath = 'test-report.xlsx';
    Storage::disk('reports')->put($filePath, 'Test file content');

    $signedUrl = URL::temporarySignedRoute(
        'exports.download',
        now()->addMinutes(5),
        ['path' => $filePath]
    );

    get($signedUrl)
        ->assertOk()
        ->assertDownload($filePath);
});

it('can download an exported file with a valid signed URL from public disk', function (): void {
    $filePath = 'public-test-file.pdf';
    Storage::disk('public')->put($filePath, 'Public file content');

    $signedUrl = URL::temporarySignedRoute(
        'exports.download',
        now()->addMinutes(5),
        ['path' => $filePath]
    );

    get($signedUrl)
        ->assertOk()
        ->assertDownload($filePath);
});

it('cannot download an exported file with an invalid signed URL', function (): void {
    $filePath = 'test-report.xlsx';
    Storage::disk('reports')->put($filePath, 'Test file content');

    $signedUrl = URL::temporarySignedRoute(
        'exports.download',
        now()->addMinutes(5),
        ['path' => $filePath]
    );

    // Tamper with the URL to make it invalid
    $invalidSignedUrl = $signedUrl.'&invalid=true';

    get($invalidSignedUrl)
        ->assertForbidden(); // Laravel's signed route middleware returns 403 Forbidden for invalid signatures
});

it('cannot download an exported file with an expired signed URL', function (): void {
    $filePath = 'test-report.xlsx';
    Storage::disk('reports')->put($filePath, 'Test file content');

    $signedUrl = URL::temporarySignedRoute(
        'exports.download',
        now()->subMinutes(5), // Expired URL
        ['path' => $filePath]
    );

    get($signedUrl)
        ->assertForbidden();
});

it('returns 404 for a non-existent file', function (): void {
    $filePath = 'non-existent-file.xlsx';

    $signedUrl = URL::temporarySignedRoute(
        'exports.download',
        now()->addMinutes(5),
        ['path' => $filePath]
    );

    get($signedUrl)
        ->assertNotFound();
});
