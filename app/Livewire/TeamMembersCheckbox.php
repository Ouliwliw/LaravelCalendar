<?php

namespace App\Livewire;

class TeamMembersCheckbox extends Calendar
{
    public $userOnly;

    public $allTeamMembersSelected;

    public function checkedBox()
    {
        $this->dispatch('aUserHasBeenSelected', $this->selectedUsers);
    }

    public function allCheckedBox()
    {
        if ($this->allTeamMembersSelected) {

            $x = 0;

            foreach ($this->teamMembers as $user) {

                $this->selectedUsers[$x] = "$user->id";
                $x++;
            }
            
        } else {

            $this->selectedUsers = [];
        }

        $this->dispatch('aUserHasBeenSelected', $this->selectedUsers);
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        if ($this->team != null) {

            $this->teamMembers = $this->team->users()->where('role', '!=', 1)->get();

        } else {

            $this->userOnly = auth()->user();
        }

        return view('livewire.team-members-checkbox');
    }
}
