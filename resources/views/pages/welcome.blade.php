<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join Our Team</title>
    @vite('resources/css/app.css') <!-- Include your CSS -->
    @livewireStyles <!-- Include Livewire styles -->
</head>
<body class="bg-gray-100">
    <div class="max-w-7xl mx-auto py-10 px-4">
        <div class="text-center">
            <h1 class="text-4xl font-bold">Join Our Team</h1>
            <p class="mt-2 text-gray-600">We are hiring! Browse our open positions and apply today.</p>
        </div>
        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Job Openings -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-2xl font-semibold mb-4">Current Job Openings</h2>
                <ul class="space-y-3">
                    @foreach(App\Models\Opening::all() as $opening)
                        <li class="border p-3 rounded-md shadow-sm">
                            <strong>{{ $opening->title }}</strong><br>
                            <span class="text-gray-500">{{ $opening->description }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
            <!-- Application Form -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-2xl font-semibold mb-4">Apply Now</h2>
                <form wire:submit.prevent="submit">
                    {{ $this->form }}
                    <x-filament::button type="submit" class="mt-4 w-full">Submit Application</x-filament::button>
                </form>
            </div>
        </div>
    </div>
    @livewireScripts <!-- Include Livewire scripts -->
</body>
</html>