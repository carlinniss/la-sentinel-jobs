<label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-[#e6d8bd] bg-white p-4">
    <input type="hidden" name="resume_searchable" value="0">
    <input type="checkbox" name="resume_searchable" value="1" @checked($checked) class="mt-0.5 h-5 w-5 shrink-0 rounded border-[#b9a98c] text-[#8b1d22] focus:ring-[#8b1d22]">
    <span>
        <span class="block text-base font-semibold text-[#17120f]">Let verified employers find my resume</span>
        <span class="mt-0.5 block text-sm leading-6 text-[#675d52]">Leave this off to keep it private. You can turn it on or off anytime.</span>
    </span>
</label>
