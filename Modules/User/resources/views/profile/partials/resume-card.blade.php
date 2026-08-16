@php
    $profile = $user->profile;
    $resumeErrors = $errors->resume;
    $resumeViews = $profile?->resumeViews ?? collect();
    $configuredFeeCents = max(0, (int) config('resume.upload_fee_cents'));
    $resumePaymentPending = $profile?->hasResume() && $profile->resume_fee_cents > 0 && $profile->resume_paid_at === null;
@endphp

<section class="space-y-7">
    <header class="flex flex-col gap-4 border-b border-slate-200/80 pb-6 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="account-section-kicker">Resume bank</p>
            <h2 class="mt-2 text-2xl font-semibold tracking-[-0.03em] text-slate-950">Your resume and visibility</h2>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                Upload a resume privately, then choose whether verified employers with resume-bank access can discover it. You can turn discovery off at any time.
            </p>
        </div>
        @if(session('status') === 'resume-updated')
            <span class="account-inline-badge bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200">Resume saved</span>
        @elseif(session('status') === 'resume-payment-complete')
            <span class="account-inline-badge bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200">Payment confirmed</span>
        @elseif(session('status') === 'resume-deleted')
            <span class="account-inline-badge bg-slate-100 text-slate-700 ring-1 ring-slate-200">Resume removed</span>
        @endif
    </header>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.15fr),minmax(300px,0.85fr)]">
        <div>
            <form method="post" action="{{ route('resume.update') }}" enctype="multipart/form-data" class="space-y-5">
                @csrf
                @method('put')

                <div class="rounded-[24px] border border-slate-200 bg-slate-50/70 p-5">
                    @if($profile?->hasResume())
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm font-semibold text-slate-950">{{ $profile->resume_original_name }}</p>
                                <p class="mt-1 text-xs text-slate-500">
                                    Uploaded {{ $profile->resume_uploaded_at?->format('M j, Y g:i A') }}
                                    @if($profile->resume_size)
                                        · {{ number_format($profile->resume_size / 1024, 0) }} KB
                                    @endif
                                </p>
                            </div>
                            <a href="{{ route('resumes.download', $profile) }}" class="account-secondary-button">Download your copy</a>
                        </div>
                    @else
                        <p class="text-sm font-semibold text-slate-700">No resume uploaded yet.</p>
                    @endif
                </div>

                @if($resumePaymentPending)
                    <div class="rounded-[22px] border border-amber-200 bg-amber-50 px-4 py-3 text-sm leading-6 text-amber-900">Payment is pending. The resume remains private and excluded from employer search until Stripe confirms payment.</div>
                @endif

                <div class="account-field">
                    <label for="resume" class="account-label">{{ $profile?->hasResume() ? 'Replace resume' : 'Upload resume' }}</label>
                    <input id="resume" name="resume" type="file" accept=".pdf,.doc,.docx" class="account-input bg-white">
                    <p class="account-helper">PDF, DOC, or DOCX. Maximum file size: 5 MB.</p>
                    @foreach($resumeErrors->get('resume') as $message)
                        <p class="account-error">{{ $message }}</p>
                    @endforeach
                </div>

                <label class="flex cursor-pointer items-start gap-3 rounded-[22px] border border-slate-200 bg-white p-4">
                    <input type="hidden" name="resume_searchable" value="0">
                    <input type="checkbox" name="resume_searchable" value="1" @checked(old('resume_searchable', $profile?->resume_searchable)) class="mt-1 h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900">
                    <span>
                        <span class="block text-sm font-semibold text-slate-950">Let authorized employers discover my resume</span>
                        <span class="mt-1 block text-sm leading-6 text-slate-500">Only verified employers granted resume-bank access can open or download it. Your resume is never public.</span>
                    </span>
                </label>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-xs leading-5 text-slate-500">
                        @if($configuredFeeCents > 0 && is_null($profile?->resume_paid_at))
                            One-time resume bank submission fee: ${{ number_format($configuredFeeCents / 100, 2) }}. Payment is processed securely by Stripe before employer discovery is activated.
                        @else
                            Resume submission is free during staging. Any future fee will be shown before upload and recorded on your account.
                        @endif
                    </p>
                    <button type="submit" class="account-primary-button">
                        {{ $configuredFeeCents > 0 && is_null($profile?->resume_paid_at) ? 'Continue to secure payment' : 'Save resume settings' }}
                    </button>
                </div>
            </form>

            @if($profile?->hasResume())
                <form method="post" action="{{ route('resume.destroy') }}" class="mt-4" onsubmit="return confirm('Remove your resume and disable employer discovery?')">
                    @csrf
                    @method('delete')
                    <button type="submit" class="text-sm font-semibold text-red-700">Remove resume</button>
                </form>
            @endif
        </div>

        <aside class="rounded-[26px] bg-slate-950 p-5 text-white">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-400">Employer activity</p>
                    <h3 class="mt-2 text-xl font-semibold">Who viewed your resume</h3>
                </div>
                <span class="rounded-full bg-white/10 px-3 py-1 text-xs font-semibold">{{ $resumeViews->count() }} recent</span>
            </div>

            <div class="mt-5 space-y-3">
                @forelse($resumeViews as $resumeView)
                    <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-sm font-semibold">{{ $resumeView->employer?->name ?? 'Authorized employer' }}</p>
                            <span class="rounded-full bg-white/10 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide">
                                {{ $resumeView->action === 'download' ? 'Downloaded' : 'Viewed' }}
                            </span>
                        </div>
                        <p class="mt-1 text-xs text-slate-400">{{ $resumeView->created_at?->diffForHumans() }}</p>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-white/15 px-4 py-6 text-sm leading-6 text-slate-400">
                        No employer activity yet. When an authorized employer opens or downloads your resume, it will appear here.
                    </div>
                @endforelse
            </div>
        </aside>
    </div>
</section>
