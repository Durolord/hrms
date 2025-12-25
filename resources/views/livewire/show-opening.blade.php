<div class="min-h-screen bg-stone-950 text-stone-100">
    <div class="relative border-b border-stone-800">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_30%_20%,rgba(120,113,108,0.35),transparent_45%),radial-gradient(circle_at_70%_10%,rgba(87,83,78,0.4),transparent_35%)]"></div>
        <div class="container mx-auto relative px-4 py-14">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-2xl">
                    <p class="uppercase tracking-[0.3em] text-xs text-stone-400">Open Role</p>
                    <h1 class="mt-3 text-4xl font-semibold tracking-tight md:text-5xl">{{ $opening->title }}</h1>
                    <p class="mt-4 text-lg text-stone-300">{{ $opening->department->name }} - {{ $opening->branch->name }}</p>
                </div>
                <div class="flex flex-wrap gap-3 text-sm">
                    <span class="rounded-full border border-stone-700 bg-stone-900/70 px-4 py-2 text-stone-200">Full Time</span>
                    <span class="rounded-full border border-stone-700 bg-stone-900/70 px-4 py-2 text-stone-200">On-site</span>
                    <span class="rounded-full border border-stone-700 bg-stone-900/70 px-4 py-2 text-stone-200">Immediate</span>
                </div>
            </div>
        </div>
    </div>
    <div class="container mx-auto px-4 py-12">
        <div class="grid gap-8 lg:grid-cols-12">
            <!-- Job Details -->
            <div class="lg:col-span-7 xl:col-span-8">
                <div class="rounded-2xl border border-stone-800 bg-stone-900/60 p-8 shadow-[0_25px_80px_-40px_rgba(0,0,0,0.8)]">
                    <h2 class="text-xl font-semibold text-stone-100">Job Description</h2>
                    <p class="mt-4 leading-relaxed text-stone-300">{!! nl2br(e($opening->description)) !!}</p>
                    <div class="mt-8 grid gap-8 md:grid-cols-2">
                        <div>
                            <h3 class="text-sm font-semibold uppercase tracking-[0.2em] text-stone-400">Responsibilities</h3>
                            <ul class="mt-4 space-y-3 text-sm text-stone-300">
                                @foreach($opening->responsibilities as $responsibility)
                                    <li class="flex gap-3">
                                        <span class="mt-1 h-2 w-2 rounded-full bg-stone-500"></span>
                                        <span>{{ $responsibility->description }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold uppercase tracking-[0.2em] text-stone-400">Qualifications</h3>
                            <ul class="mt-4 space-y-3 text-sm text-stone-300">
                                @foreach($opening->qualifications as $qualification)
                                    <li class="flex gap-3">
                                        <span class="mt-1 h-2 w-2 rounded-full bg-stone-500"></span>
                                        <span>{{ $qualification->description }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    <div class="mt-10">
                        <h3 class="text-sm font-semibold uppercase tracking-[0.2em] text-stone-400">Skills Required</h3>
                        <div class="mt-4 flex flex-wrap gap-3">
                            @foreach($opening->skills as $skill)
                                <span class="rounded-full border border-stone-700 bg-stone-900/70 px-4 py-2 text-sm text-stone-200">{{ $skill->name }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <!-- Apply Form -->
            <div class="lg:col-span-5 xl:col-span-4">
                <div class="rounded-2xl border border-stone-800 bg-stone-900/70 p-8">
                    <div class="mb-6 flex items-center justify-between">
                        <h2 class="text-xl font-semibold text-stone-100">Apply Now</h2>
                        <span class="rounded-full border border-stone-700 bg-stone-950 px-3 py-1 text-xs uppercase tracking-[0.3em] text-stone-400">Form</span>
                    </div>
                    @if(session()->has('success'))
                        <div class="mb-6 rounded-lg border border-emerald-600/40 bg-emerald-950/40 px-4 py-3 text-sm text-emerald-200">
                            {{ session('success') }}
                        </div>
                    @endif
                    <form wire:submit.prevent="submitApplication" class="space-y-5">
                        <div>
                            <label class="block text-sm font-semibold text-stone-200">Full Name</label>
                            <input type="text" wire:model="name" class="mt-2 w-full rounded-xl border border-stone-700 bg-stone-950/60 px-4 py-3 text-sm text-stone-100 placeholder-stone-500 focus:border-stone-400 focus:outline-none focus:ring-1 focus:ring-stone-500">
                            @error('name') <span class="mt-2 block text-xs text-rose-400">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-stone-200">Email</label>
                            <input type="email" wire:model="email" class="mt-2 w-full rounded-xl border border-stone-700 bg-stone-950/60 px-4 py-3 text-sm text-stone-100 placeholder-stone-500 focus:border-stone-400 focus:outline-none focus:ring-1 focus:ring-stone-500">
                            @error('email') <span class="mt-2 block text-xs text-rose-400">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-stone-200">Phone</label>
                            <input type="tel" wire:model="phone" class="mt-2 w-full rounded-xl border border-stone-700 bg-stone-950/60 px-4 py-3 text-sm text-stone-100 placeholder-stone-500 focus:border-stone-400 focus:outline-none focus:ring-1 focus:ring-stone-500">
                            @error('phone') <span class="mt-2 block text-xs text-rose-400">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-stone-200">Upload Avatar</label>
                            <input type="file" wire:model="avatar" class="mt-2 w-full rounded-xl border border-dashed border-stone-700 bg-stone-950/60 px-4 py-3 text-sm text-stone-300 file:mr-4 file:rounded-full file:border-0 file:bg-stone-800 file:px-4 file:py-2 file:text-xs file:uppercase file:tracking-[0.2em] file:text-stone-200 hover:file:bg-stone-700">
                            @error('avatar') <span class="mt-2 block text-xs text-rose-400">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-stone-200">Upload CV (PDF)</label>
                            <input type="file" wire:model="cv" class="mt-2 w-full rounded-xl border border-dashed border-stone-700 bg-stone-950/60 px-4 py-3 text-sm text-stone-300 file:mr-4 file:rounded-full file:border-0 file:bg-stone-800 file:px-4 file:py-2 file:text-xs file:uppercase file:tracking-[0.2em] file:text-stone-200 hover:file:bg-stone-700">
                            @error('cv') <span class="mt-2 block text-xs text-rose-400">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-stone-200">Job Status</label>
                            <select wire:model="job_status" class="mt-2 w-full rounded-xl border border-stone-700 bg-stone-950/60 px-4 py-3 text-sm text-stone-100 focus:border-stone-400 focus:outline-none focus:ring-1 focus:ring-stone-500">
                                <option value="Employed">Employed</option>
                                <option value="Unemployed">Unemployed</option>
                            </select>
                            @error('job_status') <span class="mt-2 block text-xs text-rose-400">{{ $message }}</span> @enderror
                        </div>
                        <button type="submit" class="w-full rounded-xl border border-stone-700 bg-stone-100 px-4 py-3 text-sm font-semibold uppercase tracking-[0.2em] text-stone-900 transition hover:bg-white">
                            Apply Now
                        </button>
                    </form>
                </div>
                <div class="mt-6 rounded-2xl border border-stone-800 bg-stone-900/50 px-6 py-5 text-sm text-stone-300">
                    <p class="text-xs uppercase tracking-[0.3em] text-stone-400">Process</p>
                    <p class="mt-2">We review within 3 business days. Shortlisted candidates get a call within 24 hours.</p>
                </div>
            </div>
        </div>
    </div>
</div>
