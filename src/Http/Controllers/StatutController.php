<?php

declare(strict_types=1);

namespace Memora\Statut\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use Memora\Statut\Contracts\MonitoringProvider;
use Memora\Statut\Exceptions\StatutProviderException;

final class StatutController extends Controller
{
    public function __construct(
        private readonly MonitoringProvider $provider,
    ) {
    }

    public function __invoke(): View
    {
        $hidden = (array) config('statut.monitors.hidden', []);

        try {
            $overview  = $this->provider->getOverview();
            $monitors  = collect($this->provider->listMonitors())
                ->reject(fn ($monitor) => in_array((string) $monitor->id, array_map('strval', $hidden), true))
                ->values()
                ->all();
            $incidents = $this->provider->getActiveIncidents();
        } catch (StatutProviderException $e) {
            report($e);
            $overview  = null;
            $monitors  = [];
            $incidents = [];
        }

        $layout = config('statut.layout', 'layouts.app');
        if (! is_string($layout) || ! preg_match('/^[A-Za-z0-9._-]+$/', $layout)) {
            $layout = 'layouts.app';
        }

        $section = config('statut.section', 'content');
        if (! is_string($section) || ! preg_match('/^[a-z0-9_-]+$/', $section)) {
            $section = 'content';
        }

        return view('statut::index', compact('overview', 'monitors', 'incidents'))
            ->with([
                'layout'   => $layout,
                'section'  => $section,
                'brand'    => config('statut.brand', []),
                'hasError' => $overview === null,
            ]);
    }
}
