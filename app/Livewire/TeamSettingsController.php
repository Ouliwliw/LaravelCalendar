<?php

namespace App\Livewire;

use Livewire\Component;

class TeamSettingsController extends Component
{
    public $isAdmin;

    public $isModerator;

    public $team;

    public function render()
    {
        $this->team = auth()->user()->currentTeam;

        $this->isAdmin = auth()->user()->isAdmin();
        // @phpstan-ignore-next-line
        $this->isModerator = auth()->user()->canDoAction('Moderateur', $this->team->id);

        return view('livewire.team-settings-controller')->with([
            'team' => $this->team,
            'isAdmin' => $this->isAdmin,
            'isModerator' => $this->isModerator,
        ]);
    }
}
