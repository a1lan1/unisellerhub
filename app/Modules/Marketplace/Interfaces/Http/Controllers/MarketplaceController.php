<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Interfaces\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Marketplace\Application\Services\MarketplaceConnectionService;
use App\Modules\Marketplace\Domain\Models\MarketplaceConnection;
use App\Modules\Marketplace\Interfaces\Http\Requests\BaseMarketplaceConnectionRequest;
use Inertia\Inertia;
use Inertia\Response;

class MarketplaceController extends Controller
{
    public function __construct(private readonly MarketplaceConnectionService $service) {}

    /**
     * Display the specific marketplace dashboard.
     */
    public function show(BaseMarketplaceConnectionRequest $request, MarketplaceConnection $marketplaceConnection): Response
    {
        $stats = $this->service->getMarketplaceConnectionStats($marketplaceConnection);

        return Inertia::render('marketplaces/Show', [
            'connection' => $marketplaceConnection,
            'stats' => [
                'total_products' => $stats->totalProducts,
                'total_orders' => $stats->totalOrders,
                'recent_activity' => $stats->recentActivity,
            ],
        ]);
    }

    /**
     * Display marketplace sync logs.
     */
    public function logs(BaseMarketplaceConnectionRequest $request, MarketplaceConnection $marketplaceConnection): Response
    {
        $logs = $this->service->getMarketplaceConnectionLogs($marketplaceConnection);

        return Inertia::render('marketplaces/Logs', [
            'connection' => $marketplaceConnection,
            'logs' => $logs,
        ]);
    }

    /**
     * Display Avito Messenger (Mock).
     */
    public function messenger(BaseMarketplaceConnectionRequest $request, MarketplaceConnection $marketplaceConnection): Response
    {
        return Inertia::render('marketplaces/AvitoMessenger', [
            'connection' => $marketplaceConnection,
        ]);
    }
}
