<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Memora\Statut\Exceptions\StatutProviderException;
use Memora\Statut\Providers\RobotalpProvider;
use Memora\Statut\Support\MonitorDto;
use Memora\Statut\Support\MonitorStatus;
use Memora\Statut\Support\Overview;

function makeProvider(): RobotalpProvider
{
    return new RobotalpProvider([
        'base_url'     => 'https://api.robotalp.com',
        'api_key'      => 'test',
        'workspace_id' => 999,
    ]);
}

it('maps the overview payload correctly', function () {
    Http::fake([
        'api.robotalp.com/robot/status/999/' => Http::response([
            'status' => true,
            'data'   => [
                'total'            => 3,
                'up'               => 2,
                'down'             => 1,
                'paused'           => 0,
                'active_incidents' => 1,
            ],
        ]),
    ]);

    $o = makeProvider()->getOverview();

    expect($o)->toBeInstanceOf(Overview::class)
        ->and($o->total)->toBe(3)
        ->and($o->up)->toBe(2)
        ->and($o->down)->toBe(1)
        ->and($o->paused)->toBe(0)
        ->and($o->activeIncidents)->toBe(1)
        ->and($o->allOperational())->toBeFalse();
});

it('lists monitors as MonitorDto with correct status mapping', function () {
    Http::fake([
        'api.robotalp.com/robot*' => Http::response([
            'status' => true,
            'data'   => [
                [
                    'id'            => 101,
                    'name'          => 'Web',
                    'address'       => 'https://example.com',
                    'status'        => 1,
                    'availability'  => 1,
                    'robot_type'    => ['id' => 1, 'name' => 'Uptime'],
                    'last_run_time' => 1700000000000,
                ],
                [
                    'id'            => 102,
                    'name'          => 'DB',
                    'address'       => 'db.example.com',
                    'status'        => 2,
                    'availability'  => 0,
                    'robot_type'    => ['id' => 2, 'name' => 'Ping'],
                    'last_run_time' => null,
                ],
            ],
        ]),
    ]);

    $monitors = makeProvider()->listMonitors();

    expect($monitors)->toHaveCount(2)
        ->and($monitors[0])->toBeInstanceOf(MonitorDto::class)
        ->and($monitors[0]->id)->toBe(101)
        ->and($monitors[0]->name)->toBe('Web')
        ->and($monitors[0]->status)->toBe(MonitorStatus::UP)
        ->and($monitors[1]->status)->toBe(MonitorStatus::PAUSED);
});

it('throws StatutProviderException on HTTP 500', function () {
    Http::fake([
        'api.robotalp.com/robot/status/999/' => Http::response(['detail' => 'boom'], 500),
    ]);

    makeProvider()->getOverview();
})->throws(StatutProviderException::class);

it('caches identical calls', function () {
    Http::fake([
        'api.robotalp.com/robot/status/999/' => Http::response([
            'status' => true,
            'data'   => [
                'total'            => 1,
                'up'               => 1,
                'down'             => 0,
                'paused'           => 0,
                'active_incidents' => 0,
            ],
        ]),
    ]);

    $p = makeProvider();
    $p->getOverview();
    $p->getOverview();

    Http::assertSentCount(1);
});

it('uses the ApiKey scheme in the Authorization header', function () {
    Http::fake([
        'api.robotalp.com/robot/status/999/' => Http::response([
            'status' => true,
            'data'   => ['total' => 0, 'up' => 0, 'down' => 0, 'paused' => 0, 'active_incidents' => 0],
        ]),
    ]);

    makeProvider()->getOverview();

    Http::assertSent(function ($request) {
        return $request->hasHeader('Authorization', 'ApiKey test');
    });
});

it('fetches active incidents via the incident endpoint with status_id=0', function () {
    Http::fake([
        'api.robotalp.com/incident*' => Http::response([
            'status' => true,
            'data'   => [
                [
                    'id'          => 'inc-1',
                    'robot'       => ['id' => 101, 'name' => 'Web'],
                    'started_at'  => 1779140556104,
                    'ended_at'    => null,
                    'resolved'    => false,
                    'cause'       => 'timeout',
                ],
            ],
        ]),
    ]);

    $incidents = makeProvider()->getActiveIncidents();

    expect($incidents)->toHaveCount(1)
        ->and($incidents[0]->id)->toBe('inc-1')
        ->and($incidents[0]->monitorId)->toBe(101)
        ->and($incidents[0]->monitorName)->toBe('Web')
        ->and($incidents[0]->resolved)->toBeFalse()
        ->and($incidents[0]->startedAt)->toMatch('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/');

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'incident')
            && str_contains($request->url(), 'workspace_id=999')
            && str_contains($request->url(), 'status_id=0');
    });
});
