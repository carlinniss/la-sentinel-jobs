@php
    $replacing = $replacing ?? false;
@endphp
<div>
    <span class="mb-1.5 block text-sm font-semibold text-[#17120f]">
        {{ $replacing ? 'Replace your resume' : 'Your resume' }}
        @if($optional)<span class="font-normal text-[#675d52]">(optional)</span>@endif
    </span>
    <label for="resume" class="flex cursor-pointer flex-col items-center justify-center gap-1 rounded-2xl border-2 border-dashed border-[#c9b48c] bg-[#fbf4e6] px-4 py-6 text-center hover:border-[#8b1d22] focus-within:border-[#8b1d22] focus-within:ring-2 focus-within:ring-[#8b1d22]/20">
        <svg class="h-8 w-8 text-[#8b1d22]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 16V4m0 0-4 4m4-4 4 4M5 15v3a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-3"/>
        </svg>
        <span class="text-base font-bold text-[#8b1d22]" x-text="fileName || 'Choose a file'">Choose a file</span>
        <span class="text-sm text-[#675d52]">PDF, Word (DOC or DOCX), up to 5 MB</span>
        <input id="resume" name="resume" type="file" accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" class="sr-only" x-on:change="fileName = $event.target.files.length ? $event.target.files[0].name : ''" @unless($optional || $replacing) required @endunless>
    </label>
    @error('resume')<p class="mt-1.5 text-sm font-medium text-[#b42318]">{{ $message }}</p>@enderror
</div>
