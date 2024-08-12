<?php

namespace App\Livewire;

use Livewire\Component;

class Welcome extends Component
{
    public $calendarUrl;

    public function render()
    {
        $this->calendarUrl = auth()->user()->getCalendarUrl();

        return view('livewire.welcome')->layout('layouts.guest');
    }
}
