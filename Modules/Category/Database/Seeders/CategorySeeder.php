<?php

declare(strict_types=1);

namespace Modules\Category\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Category\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Healthcare', 'slug' => 'jobs-healthcare', 'icon' => null, 'children' => ['Clinical', 'Caregiving', 'Operations']],
            ['name' => 'Education', 'slug' => 'jobs-education', 'icon' => null, 'children' => ['Teaching', 'Youth Programs', 'Administration']],
            ['name' => 'Skilled Trades', 'slug' => 'jobs-skilled-trades', 'icon' => null, 'children' => ['Construction', 'Electrical', 'Transportation']],
            ['name' => 'Media & Creative', 'slug' => 'jobs-media-creative', 'icon' => null, 'children' => ['Editorial', 'Production', 'Marketing']],
            ['name' => 'Public Sector', 'slug' => 'jobs-public-sector', 'icon' => null, 'children' => ['City Services', 'Public Safety', 'Community Outreach']],
            ['name' => 'Nonprofit', 'slug' => 'jobs-nonprofit', 'icon' => null, 'children' => ['Case Management', 'Development', 'Program Coordination']],
            ['name' => 'Hospitality', 'slug' => 'jobs-hospitality', 'icon' => null, 'children' => ['Food Service', 'Events', 'Guest Services']],
            ['name' => 'Technology', 'slug' => 'jobs-technology', 'icon' => null, 'children' => ['IT Support', 'Data', 'Product']],
        ];

        foreach ($categories as $index => $data) {
            $parent = Category::withTrashed()->updateOrCreate(
                ['slug' => $data['slug']],
                ['name' => $data['name'], 'slug' => $data['slug'], 'icon' => $data['icon'], 'level' => 0, 'sort_order' => $index, 'is_active' => true]
            );

            if ($parent->trashed()) {
                $parent->restore();
            }

            foreach ($data['children'] as $i => $childName) {
                $childSlug = $data['slug'].'-'.Str::slug($childName);
                $child = Category::withTrashed()->updateOrCreate(
                    ['slug' => $childSlug],
                    ['name' => $childName, 'slug' => $childSlug, 'parent_id' => $parent->id, 'level' => 1, 'sort_order' => $i, 'is_active' => true]
                );

                if ($child->trashed()) {
                    $child->restore();
                }
            }
        }

        Category::query()
            ->whereIn('slug', $this->legacyDemoSlugs())
            ->update(['is_active' => false]);
    }

    private function legacyDemoSlugs(): array
    {
        $legacyCategories = [
            'electronics' => ['Phones', 'Computers', 'Tablets', 'TVs'],
            'vehicles' => ['Cars', 'Motorcycles', 'Trucks', 'Boats'],
            'real-estate' => ['For Sale', 'For Rent', 'Commercial'],
            'fashion' => ['Men', 'Women', 'Kids', 'Shoes'],
            'home-garden' => ['Furniture', 'Garden', 'Appliances'],
            'sports' => ['Outdoor', 'Fitness', 'Team Sports'],
            'jobs' => ['Full Time', 'Part Time', 'Freelance'],
            'services' => ['Cleaning', 'Repair', 'Education'],
        ];

        return collect($legacyCategories)
            ->flatMap(function (array $children, string $slug): array {
                return array_merge(
                    [$slug],
                    collect($children)
                        ->map(fn (string $child): string => $slug.'-'.Str::slug($child))
                        ->all()
                );
            })
            ->values()
            ->all();
    }
}
