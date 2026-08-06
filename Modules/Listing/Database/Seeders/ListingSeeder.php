<?php

declare(strict_types=1);

namespace Modules\Listing\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Modules\Category\Models\Category;
use Modules\Listing\Models\Listing;
use Modules\Listing\Support\SampleListingImageCatalog;
use Modules\User\App\Models\User;
use Modules\User\App\Support\DemoUserCatalog;

class ListingSeeder extends Seeder
{
    private const JOB_TITLES = [
        'clinical' => ['Community Clinic Intake Coordinator', 'Medical Assistant - Family Practice', 'Patient Benefits Navigator'],
        'caregiving' => ['Home Care Aide - South LA', 'Senior Companion Program Lead', 'Residential Support Specialist'],
        'operations' => ['Clinic Operations Scheduler', 'Healthcare Office Coordinator', 'Mobile Outreach Dispatcher'],
        'teaching' => ['After-School Literacy Instructor', 'STEM Teaching Fellow', 'Substitute Teacher Pool'],
        'youth-programs' => ['Youth Mentor Coordinator', 'College Access Coach', 'Summer Program Site Lead'],
        'administration' => ['School Office Assistant', 'Enrollment Services Associate', 'Program Data Clerk'],
        'construction' => ['Construction Apprentice', 'Site Safety Assistant', 'Facilities Maintenance Technician'],
        'electrical' => ['Electrical Trainee', 'Solar Installation Helper', 'Low Voltage Technician'],
        'transportation' => ['Transit Ambassador', 'Class B Shuttle Driver', 'Logistics Route Coordinator'],
        'editorial' => ['Community News Reporter', 'Newsletter Editor', 'Culture Desk Contributor'],
        'production' => ['Video Production Assistant', 'Podcast Studio Operator', 'Event Livestream Technician'],
        'marketing' => ['Digital Campaign Coordinator', 'Social Media Producer', 'Brand Partnerships Associate'],
        'city-services' => ['Neighborhood Services Representative', 'Permit Center Assistant', 'Community Desk Specialist'],
        'public-safety' => ['Emergency Preparedness Outreach Aide', 'Traffic Safety Educator', 'Public Safety Records Clerk'],
        'community-outreach' => ['Small Business Outreach Coordinator', 'Housing Resource Navigator', 'Civic Engagement Organizer'],
        'case-management' => ['Family Services Case Manager', 'Reentry Resource Specialist', 'Tenant Support Advocate'],
        'development' => ['Grants Associate', 'Donor Relations Coordinator', 'Corporate Giving Assistant'],
        'program-coordination' => ['Food Access Program Coordinator', 'Workforce Program Assistant', 'Volunteer Services Lead'],
        'food-service' => ['Line Cook - Cultural Center Cafe', 'Catering Prep Lead', 'Barista Trainer'],
        'events' => ['Event Operations Coordinator', 'Venue Setup Crew Lead', 'Guest Check-In Supervisor'],
        'guest-services' => ['Museum Guest Services Associate', 'Hotel Front Desk Agent', 'Community Event Host'],
        'it-support' => ['Help Desk Technician', 'Field IT Support Specialist', 'Device Deployment Coordinator'],
        'data' => ['Workforce Data Analyst', 'CRM Data Coordinator', 'Research Assistant'],
        'product' => ['Junior Product Coordinator', 'No-Code Automation Specialist', 'Community Platform Associate'],
    ];

    private const EMPLOYERS = [
        'LA Sentinel Talent Desk',
        'Kedren Community Health',
        'Destination Crenshaw',
        'South LA Transit Partners',
        'Watts Community Action Network',
        'Inner City Arts Workforce',
        'Baldwin Hills Hospitality Group',
        'Vermont Slauson Economic Development',
        'BMO Community Banking Careers',
        'LA County Workforce Desk',
        'South LA College & Career Bridge',
        'Crenshaw Chamber Employer Network',
    ];

    private const LA_AREAS = [
        'Leimert Park',
        'Crenshaw',
        'View Park',
        'Baldwin Hills',
        'Watts',
        'Inglewood',
        'West Adams',
        'Downtown Los Angeles',
        'Exposition Park',
        'Hyde Park',
    ];

    public function run(): void
    {
        $users = $this->resolveSeederUsers();
        $categories = $this->resolveSeedableCategories();

        if ($users->isEmpty() || $categories->isEmpty()) {
            return;
        }

        $plannedSlugs = [];
        $assignedImageIndex = 0;

        foreach ($categories as $category) {
            foreach ($users as $user) {
                $listingData = $this->buildListingData(
                    $category,
                    $assignedImageIndex,
                    $user,
                    SampleListingImageCatalog::pathFor($category, $assignedImageIndex)
                );
                $listing = $this->upsertListing($listingData, $category, $user);
                $plannedSlugs[] = $listing->slug;
                $this->syncListingImage($listing, $listingData['image_path']);
                $assignedImageIndex++;
            }
        }

        Listing::query()
            ->whereIn('user_id', $users->pluck('id'))
            ->where('slug', 'like', 'demo-%')
            ->whereNotIn('slug', $plannedSlugs)
            ->get()
            ->each(function (Listing $listing): void {
                $listing->clearMediaCollection('listing-images');
                $listing->delete();
            });
    }

    private function resolveSeederUsers(): Collection
    {
        return User::query()
            ->whereIn('email', DemoUserCatalog::emails())
            ->orderBy('email')
            ->get()
            ->values();
    }

    private function resolveSeedableCategories(): Collection
    {
        $leafCategories = Category::query()
            ->where('is_active', true)
            ->whereDoesntHave('children')
            ->with('parent:id,name,slug')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        if ($leafCategories->isNotEmpty()) {
            return $leafCategories->values();
        }

        return Category::query()
            ->where('is_active', true)
            ->with('parent:id,name,slug')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->values();
    }

    private function buildListingData(
        Category $category,
        int $index,
        User $user,
        ?string $imagePath
    ): array {
        $location = $this->resolveLocation($index);
        $title = $this->buildTitle($category, $index);
        $slug = 'demo-'.Str::slug($user->email).'-'.$category->slug;

        return [
            'slug' => $slug,
            'title' => $title,
            'description' => $this->buildDescription($title, $location['city'], $index),
            'price' => $this->priceForIndex($index),
            'city' => $location['city'],
            'country' => $location['country'],
            'contact_phone' => DemoUserCatalog::phoneFor($user->email),
            'is_featured' => $index % 7 === 0,
            'expires_at' => now()->addDays(28 + ($index % 14)),
            'created_at' => now()->subHours(6 + $index),
            'image_path' => $imagePath,
        ];
    }

    private function resolveLocation(int $index): array
    {
        return [
            'country' => 'United States',
            'city' => self::LA_AREAS[$index % count(self::LA_AREAS)],
        ];
    }

    private function buildTitle(Category $category, int $index): string
    {
        $fragment = $this->jobTypeSlug($category);
        $titles = self::JOB_TITLES[$fragment] ?? [
            'Community Hiring Opportunity',
            'Local Workforce Role',
            'LA Sentinel Featured Job',
        ];

        return $titles[$index % count($titles)];
    }

    private function jobTypeSlug(Category $category): string
    {
        $categorySlug = (string) $category->slug;
        $parentSlug = (string) ($category->parent?->slug ?? '');

        if ($parentSlug !== '' && str_starts_with($categorySlug, $parentSlug.'-')) {
            return Str::after($categorySlug, $parentSlug.'-');
        }

        return Str::after($categorySlug, 'jobs-');
    }

    private function buildDescription(string $title, string $city, int $index): string
    {
        $employer = self::EMPLOYERS[$index % count(self::EMPLOYERS)];
        $schedule = ['full-time', 'part-time', 'hybrid', 'weekend-friendly', 'contract-to-hire'][$index % 5];
        $benefit = ['paid training', 'health benefits', 'transit stipend', 'career coaching', 'bilingual candidates encouraged'][$index % 5];
        $candidateFlow = [
            'resume review within two business days',
            'same-week interview slots',
            'community hiring priority',
            'entry-level pathway support',
            'clear advancement milestones',
        ][$index % 5];

        return sprintf(
            '%s is hiring for %s in %s. This %s demo posting highlights %s, %s, clear next steps, and a fast message flow so LA Sentinel can show employers how job seekers discover, save, and respond to local opportunities.',
            $employer,
            $title,
            $city,
            $schedule,
            $benefit,
            $candidateFlow
        );
    }

    private function priceForIndex(int $index): int
    {
        $basePrices = [
            22,
            28,
            34,
            42000,
            52000,
            68000,
            75000,
            92000,
        ];

        $base = $basePrices[$index % count($basePrices)];
        $step = (int) floor($index / count($basePrices)) * 2;

        return $base + $step;
    }

    private function upsertListing(array $data, Category $category, User $user): Listing
    {
        $listing = Listing::updateOrCreate(
            ['slug' => $data['slug']],
            [
                'title' => $data['title'],
                'description' => $data['description'],
                'price' => $data['price'],
                'currency' => 'USD',
                'city' => $data['city'],
                'country' => $data['country'],
                'category_id' => $category->id,
                'contact_email' => $user->email,
                'contact_phone' => $data['contact_phone'],
                'expires_at' => $data['expires_at'],
            ]
        );

        $listing->applyAdminFormData([
            'slug' => $data['slug'],
            'user_id' => $user->id,
            'status' => 'active',
            'is_featured' => $data['is_featured'],
        ]);
        $listing->save();

        $listing->forceFill([
            'created_at' => $data['created_at'],
            'updated_at' => $data['created_at'],
        ])->saveQuietly();

        return $listing;
    }

    private function syncListingImage(Listing $listing, ?string $imageAbsolutePath): void
    {
        if (! is_string($imageAbsolutePath) || ! is_file($imageAbsolutePath)) {
            return;
        }

        $listing->replacePublicImage(
            $imageAbsolutePath,
            SampleListingImageCatalog::fileNameFor($imageAbsolutePath, $listing->slug)
        );
    }
}
