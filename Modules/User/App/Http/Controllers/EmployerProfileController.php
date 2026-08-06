<?php

declare(strict_types=1);

namespace Modules\User\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Listing\Models\Listing;
use Modules\User\App\Models\User;

class EmployerProfileController extends Controller
{
    public function show(User $employer)
    {
        $employer->loadMissing('profile');

        $listings = Listing::query()
            ->active()
            ->ownedByUser($employer->getKey())
            ->with('category:id,name,parent_id,slug')
            ->orderByDesc('is_featured')
            ->orderByDesc('created_at')
            ->paginate(9);

        return view('user::employers.show', compact('employer', 'listings'));
    }
}
