<?php

namespace App\Livewire;

use App\Livewire\Forms\EventForm;
use App\Models\Events;
use Carbon\Carbon;
use LivewireUI\Modal\ModalComponent;

class EventModal extends ModalComponent
{
    public ?Events $events = null;

    public string $timezone;

    public $visibilityOptions;

    public $categoryOptions;

    public EventForm $form;

    public function mount(?Events $events, $timezone, $start, $end)
    {
        $this->visibilityOptions = include base_path('app/Tableaux/Visibility.php');
        $this->categoryOptions = include base_path('app/Tableaux/Categories.php');
        if ($events && $events->exists) {
            $this->form->setEvent($events, $timezone);
        }
        $this->form->start = Carbon::parse($start)->format('Y-m-d\TH:i');
        $this->form->end = Carbon::parse($end)->format('Y-m-d\TH:i');
    }

    public function save()
    {
        $this->form->store($this->timezone);
        $this->closeModal();
        $arrayUserID = [$this->form->user_id];
    }

    public function update()
    {
        $this->form->update($this->timezone);
        $this->closeModal();
        $arrayUserID = [$this->form->user_id];
    }

    public function render()
    {
        return view('livewire.event-modal');
    }
}
