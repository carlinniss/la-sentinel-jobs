<?php

declare(strict_types=1);

namespace Modules\Site\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Category\Models\Category;
use Modules\Listing\Models\Listing;
use Modules\User\App\Models\User;

class HomeController extends Controller
{
    public function index()
    {
        $categories = Category::homeParentCategories();
        $featuredListings = Listing::homeFeatured();
        $recentListings = Listing::homeRecent();
        $listingCount = Listing::activeCount();
        $categoryCount = Category::activeCount();
        $userCount = User::totalCount();
        $featuredEmployers = User::query()
            ->with('profile')
            ->withCount([
                'listings as active_listings_count' => fn ($query) => $query->where('status', 'active'),
            ])
            ->featuredEmployers()
            ->orderByRaw("case when email = 'k@k.com' or name like '%St. John%' or name like '%St John%' then 0 else 1 end")
            ->orderByDesc('active_listings_count')
            ->limit(3)
            ->get();
        $favoriteListingIds = auth()->check()
            ? auth()->user()->favoriteListingIds()
            : [];

        return view('site::home', compact(
            'categories',
            'featuredListings',
            'recentListings',
            'listingCount',
            'categoryCount',
            'userCount',
            'featuredEmployers',
            'favoriteListingIds',
        ));
    }
}
