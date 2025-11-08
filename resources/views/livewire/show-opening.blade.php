<div class="container mx-auto px-4 py-8">
    <div class="bg-blue-500 text-white text-center py-6">
        <h1 class="text-3xl font-bold">{{ $opening->title }}</h1>
        <p class="text-lg">{{ $opening->department->name }} - {{ $opening->branch->name }}</p>
    </div>
    <div class="grid grid-cols-12 gap-6 mt-8">
        <!-- Job Details -->
        <div class="col-span-8 bg-white p-6 shadow-md rounded-lg">
            <h2 class="text-xl font-semibold mb-4">Job Description</h2>
            <p>{!! nl2br(e($opening->description)) !!}</p>
            <h2 class="text-xl font-semibold mt-6">Responsibilities</h2>
            <ul class="list-disc list-inside">
                @foreach($opening->responsibilities as $responsibility)
                    <li>{{ $responsibility->description }}</li>
                @endforeach
            </ul>
            <h2 class="text-xl font-semibold mt-6">Qualifications</h2>
            <ul class="list-disc list-inside">
                @foreach($opening->qualifications as $qualification)
                    <li>{{ $qualification->description }}</li>
                @endforeach
            </ul>
            <h2 class="text-xl font-semibold mt-6">Skills Required</h2>
            <ul class="list-disc list-inside">
                @foreach($opening->skills as $skill)
                    <li>{{ $skill->name }}</li>
                @endforeach
            </ul>
        </div>
        <!-- Apply Form -->
        <div class="col-span-4 bg-gray-100 p-6 rounded-lg">
            <h2 class="text-xl font-semibold mb-4">Apply Now</h2>
            @if(session()->has('success'))
                <div class="bg-green-500 text-white p-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif
            <form wire:submit.prevent="submitApplication">
                <div class="mb-4">
                    <label class="block font-semibold">Full Name</label>
                    <input type="text" wire:model="name" class="w-full border-gray-300 rounded-lg">
                    @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <div class="mb-4">
                    <label class="block font-semibold">Email</label>
                    <input type="email" wire:model="email" class="w-full border-gray-300 rounded-lg">
                    @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <div class="mb-4">
                    <label class="block font-semibold">Phone</label>
                    <input type="tel" wire:model="phone" class="w-full border-gray-300 rounded-lg">
                    @error('phone') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <div class="mb-4">
                    <label class="block font-semibold">Upload Avatar</label>
                    <input type="file" wire:model="avatar">
                    @error('avatar') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <div class="mb-4">
                    <label class="block font-semibold">Upload CV (PDF)</label>
                    <input type="file" wire:model="cv">
                    @error('cv') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <div class="mb-4">
                    <label class="block font-semibold">Job Status</label>
                    <select wire:model="job_status" class="w-full border-gray-300 rounded-lg">
                        <option value="Employed">Employed</option>
                        <option value="Unemployed">Unemployed</option>
                    </select>
                    @error('job_status') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-lg">
                    Apply Now
                </button>
            </form>
        </div>
    </div>
</div>