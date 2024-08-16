<?php

namespace App\Livewire;

use App\Models\Events;
use Carbon\Carbon;
use Livewire\Attributes\On;

class CalendarComponent extends Calendar
{
    public $calendarUrlUserConnected = '';

    public $events;

    public $timezone;

    public function status($value)
    {
        $status = require 'app/Tableaux/Status.php';

        return $status[$value];
    }

    public function updateEvent($eventID, $start, $end, $isAllDay = false)
    {
        $event = Events::find($eventID);

        if ($isAllDay) {
            $start = Carbon::parse($start)->startOfDay();
            $end = Carbon::parse($start)->endOfDay();
        }
        $event->update([
            'start' => Carbon::parse($start)->setTimezone('UTC')->toIso8601String(),
            'end' => Carbon::parse($end)->setTimezone('UTC')->toIso8601String(),
            'is_all_day' => $isAllDay,
        ]);
    }

    public function setTimeZone($timezone)
    {
        $this->timezone = $timezone;
    }

    // Functions de conditions d'affichage

    public function isEventPrivate($event)
    {
        if ($event->visibility == 'private') {
            $event->title = 'Privé';
            $event->description = 'Cet événement est privé';
            $event->category = 'Privé';
        }
    }

    public function isEventConfidential($event)
    {
        if ($event->visibility == 'confidential') {
            $event->title = 'Confidentiel';
            $event->description = "Vous n'avez pas les droits pour voir cet événement";
        }
    }

    #[On('aUserHasBeenSelected')]
    public function refetchEvents($selectedUsers)
    {
        if (count($selectedUsers) > 1) {

            if (! $this->user->isAdminOrModerator($this->team)) {

                return abort(403, "Vous n'êtes qu'un utilisateur, vous ne pouvez pas faire ça");
            }
        }

        if (! $selectedUsers) {

            $selectedUsers = [0];
        }

        $allUsersEvents = [];

        foreach ($selectedUsers as $selectedUser) {

            $eventQuery = Events::query();
            $eventQuery->where('user_id', $selectedUser);
            $events = $eventQuery->get();
            $existingsEventsIDs = [];

            foreach ($events as $event) {

                if (! (int) $event['is_all_day']) {
                    $event['allDay'] = false;
                    $event['start'] = $event['start'];
                    $event['end'] = $event['end'];
                    $event['endDay'] = $event['end'];
                    $event['startDay'] = $event['start'];
                } else {
                    $event['allDay'] = true;
                    $event['endDay'] = $event['end'];
                    $event['end'] = $event['end'];
                    $event['startDay'] = $event['start'];
                }
                array_push($allUsersEvents, $event);
                array_push($existingsEventsIDs, $event->event_id);
            }
        }

        $this->events = json_encode($allUsersEvents);

        $this->events = json_decode($this->events);

        return $this->dispatch('eventsHaveBeenFetched');
    }

    public function render()
    {
        $this->calendarUrlUserConnected = $this->user->getCalendarUrl();

        return view('livewire.calendar-component');
    }
}
