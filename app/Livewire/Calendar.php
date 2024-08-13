<?php

namespace App\Livewire;

use Livewire\Component;

class Calendar extends Component
{
    public $user;

    public $team;

    public $teamMembers;

    public $selectedUsers = [];

    public $allUrlIcsEvents = [];

    public $calendarUrls = [];

    public function __construct()
    {
        $this->user = auth()->user();
        $this->team = $this->user->currentTeam;
    }

    public function render()
    {
        return view('livewire.calendar')->with([
            'team' => $this->team,
        ]);
    }
}
