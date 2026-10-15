<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Modules\User\App\Models\Profile;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('jobs:signup-report {source? : Only report one source, e.g. tos2026, or "none" for sign-ups with no code}', function (?string $source = null) {
    $rows = Profile::query()
        ->when($source === 'none', fn ($query) => $query->whereNull('signup_source'))
        ->when($source !== null && $source !== 'none', fn ($query) => $query->where('signup_source', Profile::normalizeSource($source)))
        ->selectRaw('signup_source')
        ->selectRaw('count(*) as signups')
        ->selectRaw('sum(case when resume_path is not null then 1 else 0 end) as with_resume')
        ->selectRaw('sum(case when resume_searchable = 1 and resume_path is not null then 1 else 0 end) as discoverable')
        ->selectRaw('sum(case when newsletter_opt_in_at is not null then 1 else 0 end) as newsletter')
        ->groupBy('signup_source')
        ->orderByDesc('signups')
        ->get();

    $table = $rows->map(fn ($row) => [
        $row->signup_source ?? '(no code)',
        (int) $row->signups,
        (int) $row->with_resume,
        (int) $row->discoverable,
        (int) $row->newsletter,
    ]);

    if ($table->count() > 1) {
        $table->push(['TOTAL', $table->sum(1), $table->sum(2), $table->sum(3), $table->sum(4)]);
    }

    $this->table(['Source', 'Sign-ups', 'With resume', 'Discoverable', 'Newsletter'], $table->all());
})->purpose('Count job-seeker sign-ups and resumes by campaign source, including sign-ups with no code');

Artisan::command('jobs:newsletter-export', function () {
    $out = fopen('php://stdout', 'w');
    fputcsv($out, ['email', 'first_name', 'last_name', 'opted_in_at', 'source']);

    Profile::query()
        ->whereNotNull('newsletter_opt_in_at')
        ->with('user:id,name,email')
        ->orderBy('newsletter_opt_in_at')
        ->each(function (Profile $profile) use ($out): void {
            if (! $profile->user) {
                return;
            }

            [$first, $last] = array_pad(explode(' ', (string) $profile->user->name, 2), 2, '');
            fputcsv($out, [
                $profile->user->email,
                $first,
                $last,
                $profile->newsletter_opt_in_at?->toDateTimeString(),
                $profile->signup_source,
            ]);
        });

    fclose($out);
})->purpose('Print newsletter opt-ins as CSV for importing into the mailing list');

if (config('demo.enabled')) {
    Schedule::command('demo:cleanup')->hourly();
}
