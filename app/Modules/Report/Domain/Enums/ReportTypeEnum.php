<?php

declare(strict_types=1);

namespace App\Modules\Report\Domain\Enums;

enum ReportTypeEnum: string
{
    case SALES_STATS = 'sales_stats';
    case ABC_ANALYSIS = 'abc_analysis';
    case PROFITABILITY = 'profitability';
    case PRICE_ANALYSIS = 'price_analysis_report';
    case PRODUCT_REVENUE_ANALYSIS = 'product_revenue_analysis';
    case PRODUCT_PROFITABILITY_ANALYSIS = 'product_profitability_analysis';
}
