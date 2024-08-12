<?php

namespace App\Livewire;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Jetstream\Features;
use Laravel\Jetstream\Http\Livewire\TeamMemberManager;
use Laravel\Jetstream\TeamInvitation;

class TeamMembers extends TeamMemberManager
{
    public function updateSearch()
    {
        $this->render();
    }

    public function setFormValues($role, $email)
    {
        $this->addTeamMemberForm['role'] = $role;
        $this->addTeamMemberForm['email'] = $email;
    }

    /**
     * Add a new team member to a team.
     *
     * @return void
     */
    public function addTeamMember()
    {
        $userIdInvite = User::where('email', $this->addTeamMemberForm['email'])->first()->id;
        $this->resetErrorBag();
        $rules = [
            'addTeamMemberForm.email' => ['required', 'email'],
            'addTeamMemberForm.role' => ['required', 'not_in:Admin'],
        ];

        $messages = [
            'addTeamMemberForm.email.required' => 'L\'email est requis',
            'addTeamMemberForm.email.email' => 'L\'email doit être une adresse email valide',
            'addTeamMemberForm.role.required' => 'Le role est requis',
            'addTeamMemberForm.role.not_in' => 'Le rôle Admin n\'est pas autorisé.',
        ];

        $this->validate($rules, $messages);

        if (Features::sendsTeamInvitations()) {

            if ($this->team->users->contains($userIdInvite)) {
                $this->addError('addTeamMemberForm', 'Cet utilisateur est déja dans l\'équipe');

                return;
            }

            try {
                $teamId = $this->team->id;
                $newTeamInvitation = new TeamInvitation([

                    'email' => $this->addTeamMemberForm['email'],
                    'role' => $this->addTeamMemberForm['role'],

                ]);
                $newTeamInvitation->team_id = $teamId;
                $newTeamInvitation->user_id = $userIdInvite;
                $newTeamInvitation->user_id_createInvite = auth()->user()->id;
                $newTeamInvitation->save();
            } catch (\Throwable $th) {

                if ($th->getCode() == 23000) {
                    $this->addError('addTeamMemberForm', 'Cet utilisateur est déja dans l\'équipe');
                }
            }
        }

        $this->addTeamMemberForm = [
            'email' => '',
            'role' => null,
        ];

        $this->team = $this->team->fresh();

        $this->dispatch('teamMemberAdded');
    }

    public function setRole($role, $user, $teamId)
    {
        $teamUser = User::find($user)->teams->find($teamId)->membership;
        $teamUser->role = $role;
        $teamUser->save();
        $this->dispatch('saved');
    }

    public function changeLeader($user_id)
    {
        if (Auth::user()->isAdmin() || Auth::user()->isLeader($this->team->id)) {

            $userTeamNewLeader = User::find($user_id)->teams->find($this->team->id)->membership;
            $userTeamNewLeader->role = 2;
            $userTeamNewLeader->save();

            $this->team->user_id = $user_id;
            $this->team->save();

            $this->js('window.location.reload()');

            $this->dispatch('leaderChanged');
        }
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.team-members', [
            'users' => User::where('email', 'LIKE', "%{$this->addTeamMemberForm['email']}%")
                ->where('id', '!=', auth()->user()->id)
                ->where('name', '!=', 'Admin')
                ->get(),
            'nonTeamMembers' => User::join('team_user', 'users.id', '=', 'team_user.user_id')
                ->whereNotIn('users.id', function ($query) {
                    $query->select('user_id')
                        ->from('team_user')
                        ->where('team_id', $this->team->id);
                })
                ->where('team_user.team_id', '!=', $this->team->id)
                ->where('users.id', '!=', auth()->user()->id)
                ->where('users.name', '!=', 'Admin')
                ->get(),
            'roleTest' => Role::where('name', '!=', 'Admin')->get(),
        ])->with('isAdmin', $this->user->isAdmin());
    }
}
