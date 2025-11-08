<?php

namespace App\Livewire;

use App\Models\Applicant;
use App\Models\Opening;
use Filament\Notifications\Notification;
use Livewire\Component;
use Livewire\WithFileUploads;

class ShowOpening extends Component
{
    use WithFileUploads;

    public Opening $opening;

    public $name;

    public $email;

    public $phone;

    public $avatar;

    public $cv;

    public $job_status = 'Unemployed';

    public function mount($id)
    {
        $this->opening = Opening::with(['department', 'designation', 'branch', 'skills', 'qualifications', 'responsibilities'])
            ->findOrFail($id);
    }

    public function submitApplication()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'avatar' => 'required|image|max:1024',
            'cv' => 'required|mimes:pdf|max:2048',
            'job_status' => 'required|in:Employed,Unemployed',
        ]);
        $exists = Applicant::where('email', $this->email)
            ->where('opening_id', $this->opening->id)
            ->exists();
        if ($exists) {
            Notification::make()
                ->title('You have already applied')
                ->body('Your application is already under review. Please be patient.')
                ->info()
                ->send();

            return;
        }
        $avatarPath = $this->avatar->store('avatars', 'public');
        $cvPath = $this->cv->store('cvs', 'public');
        Applicant::create([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'cv' => $cvPath,
            'avatar' => $avatarPath,
            'job_status' => $this->job_status,
            'opening_id' => $this->opening->id,
        ]);
        Notification::make()
            ->title('Application Submitted')
            ->body('Thank you for applying! Our team will review your application.')
            ->success()
            ->send();
        session()->flash('success', 'Application submitted successfully.');
        $this->reset(['name', 'email', 'phone', 'avatar', 'cv', 'job_status']);
    }

    public function render()
    {
        return view('livewire.show-opening')->title($this->opening->title);
    }
}
