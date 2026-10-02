<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Modules\User\App\Models\Profile;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('jobs:signup-report {source? : Only report one source, e.g. tos2026}', function (?string $source = null) {
    $rows = Profile::query()
        ->whereNotNull('signup_source')
        ->when($source, fn ($query) => $query->where('signup_source', Profile::normalizeSource($source)))
        ->selectRaw('signup_source')
        ->selectRaw('count(*) as signups')
        ->selectRaw('sum(case when resume_path is not null then 1 else 0 end) as with_resume')
        ->selectRaw('sum(case when resume_searchable = 1 and resume_path is not null then 1 else 0 end) as discoverable')
        ->groupBy('signup_source')
        ->orderByDesc('signups')
        ->get();

    $this->table(
        ['Source', 'Sign-ups', 'With resume', 'Discoverable'],
        $rows->map(fn ($row) => [$row->signup_source, $row->signups, $row->with_resume, $row->discoverable])->all(),
    );
})->purpose('Count job-seeker sign-ups and resumes by campaign source');

if (config('demo.enabled')) {
    Schedule::command('demo:cleanup')->hourly();
}
