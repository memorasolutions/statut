<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\View;

beforeEach(function () {
    View::addNamespace('statut', [__DIR__.'/../stubs']);
});

it('renders the status page in French', function () {
    Http::fake([
        'api.robotalp.com/workspace/999/robots/status/' => Http::response([
            'status' => true,
            'data'   => [
                'total'            => 1,
                'up'               => 1,
                'down'             => 0,
                'paused'           => 0,
                'active_incidents' => 0,
            ],
        ]),
        'api.robotalp.com/workspace/999/robots*' => Http::response([
            'status' => true,
            'data'   => [],
        ]),
        'api.robotalp.com/workspace/999/incident/active/' => Http::response([
            'status' => true,
            'data'   => [],
        ]),
    ]);

    $response = $this->get('/statut');

    $response->assertStatus(200);
    $response->assertSee('État des services', false);
    $response->assertSee('role="main"', false);
});

it('shows the error banner when the provider fails', function () {
    Http::fake([
        'api.robotalp.com/*' => Http::response(['detail' => 'down'], 503),
    ]);

    $response = $this->get('/statut');

    $response->assertStatus(200);
    $response->assertSee('La plateforme de surveillance est temporairement indisponible.', false);
});
