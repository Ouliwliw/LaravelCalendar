<?php

namespace App\Livewire;

use App\Http\Services\LaravelSabreCalendarHome;
use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Jetstream\Http\Livewire\CreateTeamForm;

class CreateTeamController extends CreateTeamForm
{
    public $email = null;

    public string $updateMailTeamOwner;

    public $updateUserTeamOwner = null;

    public $name;

    public $laravelSabreCalendarHome;

    public function updateSearchAndUser($email)
    {
        $this->updateSearch($email);
        $this->updateUserTeamOwnerFunc($email);
    }

    public function updateSearch($email)
    {
        $this->email = $email;

        return $this->render();
    }

    public function updateUserTeamOwnerFunc($mail)
    {
        $user = User::where('email', $mail)->first();
        $this->updateUserTeamOwner = $user;
    }

    public function setFormValues($email)
    {
        $this->email = $email;
    }

    public function setLaravelSabreCalendarHome()
    {
        $this->laravelSabreCalendarHome = new LaravelSabreCalendarHome();
    }

    public function mount()
    {

        $user = auth()->user();
        if (! $user->hasRole('Admin')) {
            redirect()->route('dashboard');
        }

    }

    public function create(Request $request)
    {
        if (auth()->user()->hasRole('Admin')) {
            $this->validate([
                'name' => ['required', 'string', 'max:255', 'unique:teams,name'],
                'email' => ['required', 'email', 'exists:users,email'],
            ]);

            $nameTeam = $this->name;
            $user = User::where('email', $this->email)->first();
            // dd($nameTeam,$userMail);
            $user->createTeamPrincipal($nameTeam);

            // return redirect()->route('dashboard')->with('alert', "L'équipe a été créée avec succès !");
            return redirect()->route('dashboard');
        }

        // return redirect()->route('dashboard')->with('status', "L'équipe ne peut-être créée avec cet utilisateur !");
        return redirect()->route('dashboard');
    }

    public function render()
    {
        if (auth()->user()->hasRole('Admin')) {
            return view('livewire.create-team-controller', [
                'users' => User::where('email', 'LIKE', "%{$this->email}%")->where('id', '!=', auth()->user()->id)->where(
                    'name',
                    '!=',
                    'Admin'
                )->get(),
            ]);
        } else {
            return view('livewire.dashboard');
        }
    }
}
