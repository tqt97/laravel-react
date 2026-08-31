<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class InertiaErrorTest extends TestCase
{
    use RefreshDatabase;

    public function test_inertia_error_response_uses_the_error_page_and_shared_locale_props(): void
    {
        Route::middleware('web')->post('/testing/not-found', fn () => abort(404));

        $this->withHeaders([
            'X-Inertia' => 'true',
            'X-Requested-With' => 'XMLHttpRequest',
        ])
            ->post('/testing/not-found')
            ->assertNotFound()
            ->assertJsonPath('component', 'errors/error')
            ->assertJsonPath('props.status', 404)
            ->assertJsonStructure([
                'props' => [
                    'locale',
                    'timezone',
                    'translations',
                ],
            ]);
    }
}
