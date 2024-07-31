<?php

namespace App\Livewire;

class TeamMembersCheckbox extends Calendar
{
    public $selectedUsers = [];

    public $userOnly;

    public $team;

    public $userTeam;

    public $selectedAll;

    public $selectedTeam;

    public function checkedBox()
    {
        $this->dispatch('aUserHasBeenSelected', $this->selectedUsers, $this->selectedTeam);
    }

    public function allCheckedBox()
    {
        // dd($this->selectedUsers);
        if ($this->selectedAll) {
            $x = 0;
            foreach ($this->userTeam as $user) {
                $this->selectedUsers[$x] = "$user->id";
                $x++;
            }
            $this->selectedTeam = true;

        } else {
            $this->selectedUsers = [];
            $this->selectedTeam = false;
        }
        $this->dispatch('aUserHasBeenSelected', $this->selectedUsers, $this->selectedTeam);

        // dd($this->selectedUsers);
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {

        if (!$this->team==null) {
            $this->userTeam = $this->team->users()->where('role', '!=', 1)->get();
        }
        else {
            $this->userOnly= auth()->user();
        }


        return view('livewire.team-members-checkbox');
    }
}
