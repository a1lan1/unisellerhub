<?php

declare(strict_types=1);

namespace App\Modules\Shared\Domain\Enums;

enum CacheKeyEnum: string
{
    case USER_PERMISSIONS = 'user_permissions_%d';
    case USER_ROLES = 'user_roles_%d';
    case REVIEWS_USER = 'reviews_user_%d_%s_page_%d';
    case SELLERS = 'sellers';
    case SELLERS_WITH_PRODUCTS = 'sellers_with_products_%d';
    case LOCATIONS_FOR_USER = 'locations_for_user_%d';
    case LOCATIONS_WITH_STATS = 'locations_with_stats_%d';
    case RESPONSE_TEMPLATES_FOR_USER = 'response_templates_for_user_%d';
    case GEO_METRICS_USER = 'geo_metrics_user_%d_loc_';
}
