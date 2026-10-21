<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\Category\Models\Category;
use Modules\Listing\Models\Listing;
use Modules\User\App\Models\Profile;
use Modules\User\App\Models\User;

class LaunchStJohnsJobs extends Command
{
    protected $signature = 'jobs:launch-st-johns
        {--force : Apply the changes. Without it, only show what would happen}
        {--contact=jfulton@sjch.org : Email that owns the St. John\'s employer account}';

    protected $description = 'Remove demo users and demo jobs, then post the St. John\'s Community Health founding-sponsor jobs';

    private const DEMO_EMAILS = [
        'a@a.com', 'b@b.com', 'c@c.com', 'd@d.com', 'e@e.com', 'f@f.com',
        'g@g.com', 'h@h.com', 'i@i.com', 'j@j.com', 'k@k.com',
    ];

    private const EMPLOYER_NAME = "St. John's Community Health";

    private const EMPLOYER_WEBSITE = 'https://www.sjch.org';

    private const EMPLOYER_BIO = "St. John's Community Health is the founding sponsor of LA Sentinel Jobs. Since 1964, St. John's has provided accessible, community-centered care to underserved communities across Los Angeles, San Bernardino, and Riverside Counties through medical, dental, behavioral health, and social-support services. Its mission is to eliminate health disparities and foster community well-being by providing and promoting the highest quality care.";

    private const LISTING_LIFETIME_DAYS = 60;

    public function handle(): int
    {
        $apply = (bool) $this->option('force');
        $contact = strtolower(trim((string) $this->option('contact')));

        $this->line($apply ? '<fg=yellow>Applying changes.</>' : '<fg=cyan>Dry run. Nothing will change. Re-run with --force to apply.</>');

        $demoUsers = User::withTrashed()->whereIn('email', self::DEMO_EMAILS)->get();
        $realAdmins = User::query()
            ->whereNotIn('email', self::DEMO_EMAILS)
            ->whereHas('roles', fn ($query) => $query->where('name', 'admin'))
            ->pluck('email');
        $keptDemoAdmins = $realAdmins->isEmpty()
            ? $demoUsers->filter(fn (User $user): bool => $user->hasRole('admin'))
            : collect();
        $usersToDelete = $demoUsers->reject(fn (User $user): bool => $keptDemoAdmins->contains('id', $user->id));

        $demoListings = Listing::withTrashed()
            ->where(fn ($query) => $query->whereIn('user_id', $demoUsers->pluck('id'))->orWhereNull('user_id'))
            ->get();
        $otherListings = Listing::withTrashed()
            ->whereNotNull('user_id')
            ->whereNotIn('user_id', $demoUsers->pluck('id'))
            ->where('slug', 'not like', 'st-johns-%')
            ->get();

        $this->newLine();
        $this->info('Demo cleanup');
        $this->line(sprintf('  Demo listings to delete (demo-owned or with no owner): %d', $demoListings->count()));
        $this->line(sprintf('  Demo accounts to delete: %d (%s)', $usersToDelete->count(), $usersToDelete->pluck('email')->join(', ') ?: 'none'));
        $this->line(sprintf('  Real admin accounts: %s', $realAdmins->join(', ') ?: 'NONE'));

        if ($keptDemoAdmins->isNotEmpty()) {
            $this->warn(sprintf('  Keeping demo admin %s because it is the only admin. Change its email and password in /admin, then re-run.', $keptDemoAdmins->pluck('email')->join(', ')));
        }

        if ($realAdmins->isEmpty()) {
            $this->warn('  No admin can log in to /admin. Create one with: php artisan jobs:make-admin you@example.com');
        }

        if ($otherListings->isNotEmpty()) {
            $this->warn(sprintf('  Leaving %d non-demo listing(s) untouched:', $otherListings->count()));
            $otherListings->each(fn (Listing $listing) => $this->line(sprintf('    #%d %s (user %s)', $listing->id, $listing->title, $listing->user_id ?? 'none')));
        }

        $jobs = $this->jobs($contact);

        $this->newLine();
        $this->info(sprintf("St. John's jobs to post as %s (featured, %d days)", $contact, self::LISTING_LIFETIME_DAYS));
        foreach ($jobs as $job) {
            $this->line(sprintf('  - %s  [%s]  image: %s', $job['title'], $job['category'], is_file($job['image']) ? 'ok' : 'MISSING'));
        }

        foreach ($jobs as $job) {
            if (! Category::query()->where('slug', $job['category'])->exists()) {
                $this->error(sprintf('Category %s does not exist. Nothing was changed.', $job['category']));

                return self::FAILURE;
            }
        }

        if (! $apply) {
            return self::SUCCESS;
        }

        DB::transaction(function () use ($demoListings, $usersToDelete): void {
            $demoListings->each(function (Listing $listing): void {
                $listing->clearMediaCollection('listing-images');
                $listing->forceDelete();
            });

            $usersToDelete->each(function (User $user): void {
                Profile::withTrashed()->where('user_id', $user->id)->forceDelete();
                $user->syncRoles([]);
                $user->forceDelete();
            });
        });

        $employer = User::withTrashed()->firstOrNew(['email' => $contact]);
        if (! $employer->exists) {
            $employer->password = Hash::make(Str::random(48));
        }
        $employer->name = self::EMPLOYER_NAME;
        $employer->save();
        if ($employer->trashed()) {
            $employer->restore();
        }

        $profile = Profile::withTrashed()->firstOrNew(['user_id' => $employer->id]);
        $profile->fill([
            'city' => 'Los Angeles',
            'country' => 'United States',
            'website' => self::EMPLOYER_WEBSITE,
            'bio' => self::EMPLOYER_BIO,
            'is_verified' => true,
            'resume_access_enabled' => true,
        ])->save();
        if ($profile->trashed()) {
            $profile->restore();
        }

        foreach ($jobs as $job) {
            $category = Category::query()->where('slug', $job['category'])->firstOrFail();

            $listing = Listing::withTrashed()->firstOrNew(['slug' => $job['slug']]);
            $listing->applyAdminFormData([
                'title' => $job['title'],
                'description' => $job['description'],
                'price' => null,
                'currency' => 'USD',
                'category_id' => $category->id,
                'contact_email' => $contact,
                'contact_phone' => null,
                'expires_at' => now()->addDays(self::LISTING_LIFETIME_DAYS),
                'country' => 'United States',
                'city' => $job['city'],
                'slug' => $job['slug'],
                'user_id' => $employer->id,
                'is_featured' => true,
                'status' => 'active',
            ]);
            $listing->save();
            if ($listing->trashed()) {
                $listing->restore();
            }

            if (is_file($job['image'])) {
                $listing->replacePublicImage($job['image'], basename($job['image']));
            }

            $this->line(sprintf('  Posted #%d %s', $listing->id, $listing->title));
        }

        $this->newLine();
        $this->info('Done. Set JOBS_BOARD_ENABLED=true and restart the app container to open the jobs board.');

        return self::SUCCESS;
    }

    private function jobs(string $contact): array
    {
        $images = public_path('images/jobs/st-johns');
        $apply = "HOW TO APPLY\nEmail your resume to St. John's Community Health recruiting at {$contact} and mention LA Sentinel Jobs. Learn more about St. John's at sjch.org.";
        $about = "ABOUT ST. JOHN'S COMMUNITY HEALTH\nSt. John's is the founding sponsor of LA Sentinel Jobs. Its mission is to eliminate health disparities and foster community well-being by providing and promoting the highest quality care in Los Angeles, San Bernardino, and Riverside Counties.";
        $eeo = "St. John's Community Health is an Equal Opportunity Employer.";

        return [
            [
                'slug' => 'st-johns-director-of-senior-services',
                'title' => 'Director of Senior Services',
                'category' => 'jobs-healthcare-operations',
                'city' => 'Los Angeles',
                'image' => $images.'/director-of-senior-services.jpg',
                'description' => implode("\n\n", [
                    "Full-time | Exempt | Population Health / Community Programs\nMulti-site: St. John's facilities and community partner locations",
                    "St. John's Community Health is hiring a Director of Senior Services to lead the design, launch, and ongoing operation of its new community-based senior program. This is a building role: you will create the program's service model, procedures, partnership network, and measurement framework from the ground up, supervise the coordination and outreach team, and serve as St. John's primary representative to community and faith-based partners serving older adults.",
                    "WHAT YOU WILL DO\n• Design the program model: services, eligibility, site selection, session cadence, and staffing\n• Write policies and workflows for intake, service delivery, documentation, and escalation of clinical or social concerns\n• Define outcomes and metrics, and roll out the program in phases with a pilot and expansion criteria\n• Build and formalize partnerships with community and faith-based organizations, and represent St. John's in senior services networks\n• Coordinate with clinical leadership on health screenings, preventive checkups, and immunization events at community sites\n• Recruit, hire, supervise, and develop program staff and volunteers\n• Manage the program budget with Finance, pursue funding with Grants and Development, and report to executive leadership",
                    "REQUIRED\n• Bachelor's degree in Health Care Administration, Public Health, Nursing, Social Work, Gerontology, Business Administration, or a related field\n• 5+ years of progressively responsible management experience in health care, human services, or community program operations\n• Experience building a program or service from concept through launch\n• A record of developing and sustaining community partnerships\n• Budget development and management experience",
                    "PREFERRED\n• Master's degree (MPH, MHA, MSW, MBA, or Gerontology)\n• Experience in a Federally Qualified Health Center or other safety-net setting\n• Adult day health, senior center, or managed long-term services experience\n• Established relationships with organizations serving older adults in the St. John's service area\n• Grant writing and grant management experience",
                    $about,
                    $apply,
                    $eeo,
                ]),
            ],
            [
                'slug' => 'st-johns-geriatrician',
                'title' => 'Geriatrician',
                'category' => 'jobs-healthcare-clinical',
                'city' => 'Los Angeles',
                'image' => $images.'/geriatrician.jpg',
                'description' => implode("\n\n", [
                    "Full-time | Exempt | Medical Department | Reports to the Chief Medical Officer\nClinics across Los Angeles, San Bernardino, and Riverside Counties. No hospital call or rounds.",
                    "St. John's Community Health is hiring a Geriatrician to provide comprehensive, patient-centered medical care to older adults, including preventive care, chronic disease management, medication management, functional assessments, cognitive screening, advance care planning, and care coordination across primary care, behavioral health, specialty care, and community resources. You will deliver culturally competent care to diverse, underserved, and uninsured communities. The role includes care within faith-based congregational settings, home visits, and occasional evening and Saturday shifts.",
                    "WHAT YOU WILL DO\n• Conduct comprehensive geriatric assessments of physical, cognitive, emotional, social, and functional status\n• Diagnose and manage acute and chronic conditions, memory disorders, dementia, depression, and frailty\n• Reconcile medications and manage polypharmacy risks\n• Address fall risk, mobility, nutrition, and other geriatric-specific concerns\n• Lead advance care planning and goals-of-care conversations\n• Coordinate care with specialists, behavioral health, pharmacists, social workers, case managers, and caregivers\n• Supervise up to 4 advanced practice practitioners and help train residents and students",
                    "REQUIRED\n• MD or DO, with completion of an accredited Internal Medicine or Family Medicine residency\n• Current California Medical License, DEA Certificate, and BLS Certification\n• Board Certified or Board Eligible in Family Medicine or Internal Medicine\n• Experience caring for adult and elderly patients, and with electronic health records\n• Reliable transportation and the ability to travel throughout the three-county service area",
                    "PREFERRED\n• Fellowship training in Geriatric Medicine, and board certification or eligibility in Geriatric Medicine\n• HIV Certification\n• Experience serving underserved, vulnerable, and aging populations",
                    $about,
                    $apply,
                    $eeo,
                ]),
            ],
            [
                'slug' => 'st-johns-senior-services-program-coordinator',
                'title' => 'Senior Services Program Coordinator',
                'category' => 'jobs-healthcare-operations',
                'city' => 'Los Angeles',
                'image' => $images.'/senior-services-program-coordinator.jpg',
                'description' => implode("\n\n", [
                    "Full-time | Non-Exempt | Population Health / Community Programs | Reports to the Director of Senior Services\nMulti-site: St. John's facilities and community partner locations",
                    "St. John's Community Health is hiring a Senior Services Program Coordinator to run the day-to-day operations of its senior program. You will turn the program plan into scheduled, staffed, supplied, and documented sessions, and be the consistent point of contact for participants, partner sites, and clinical teams on the day of service. This role suits a highly organized person who enjoys warm, direct contact with older adults.",
                    "WHAT YOU WILL DO\n• Build and maintain the program calendar across sites with partner organizations\n• Confirm site logistics, and manage supplies, materials, and equipment\n• Lead sessions on site: greet participants, manage flow, and solve problems in real time\n• Coordinate health screening and checkup events with St. John's clinical staff\n• Manage intake, registration, rosters, attendance, and program data in line with HIPAA\n• Schedule guest presenters and volunteers, and collect participant feedback",
                    "REQUIRED\n• Associate's degree (Bachelor's in health, human services, or a related field preferred)\n• 2+ years of program coordination, administrative, or community program experience\n• Strong organization across multiple sites, schedules, and deadlines\n• Proficiency with Microsoft Office\n• Ability to lift and transport materials up to 25 pounds",
                    "PREFERRED\n• Experience in a health center, senior center, adult day program, or community-based social services\n• Volunteer coordination experience\n• Familiarity with electronic health records or participant tracking databases",
                    $about,
                    $apply,
                    $eeo,
                ]),
            ],
            [
                'slug' => 'st-johns-community-outreach-specialist-senior-services',
                'title' => 'Community Outreach Specialist, Senior Services',
                'category' => 'jobs-healthcare-operations',
                'city' => 'Los Angeles',
                'image' => $images.'/community-outreach-specialist.jpg',
                'description' => implode("\n\n", [
                    "Full-time | Non-Exempt | Population Health / Community Programs | Reports to the Director of Senior Services\nPrimarily field-based within the St. John's service area, including evenings and weekends",
                    "St. John's Community Health is hiring a Community Outreach Specialist to be the front door of its senior services program. Working mostly in the field, you will build relationships with community and faith-based organizations, meet older adults where they already gather, and guide interested seniors and their families through the steps to take part. Success comes from consistent presence, cultural fluency, and earning trust over time.",
                    "WHAT YOU WILL DO\n• Build relationships with churches, senior ministries, community centers, and local groups serving older adults\n• Show up consistently at community gatherings, services, health fairs, and events\n• Give one-on-one and group presentations on program services and eligibility\n• Help seniors with intake and registration, and follow up to turn interest into attendance\n• Remove barriers such as transportation, scheduling, language access, and documentation\n• Connect participants to other St. John's services and track outreach and enrollment results",
                    "REQUIRED\n• High school diploma or equivalent (Associate's or Bachelor's in health or human services preferred)\n• 2+ years of community outreach, community health worker, promotora, patient navigation, or similar experience\n• Comfort with public speaking and group presentations\n• Ability to work substantially in the field, including regular evening and weekend hours",
                    "PREFERRED\n• Community Health Worker certification\n• Existing relationships with community or faith-based organizations in the St. John's service area\n• Enrollment, eligibility, or benefits navigation experience\n• Experience in a Federally Qualified Health Center or safety-net setting",
                    $about,
                    $apply,
                    $eeo,
                ]),
            ],
        ];
    }
}
