<?php

namespace App\Livewire;

use App\Models\Events;
use Livewire\Attributes\On;
use Livewire\Component;

class Calendar extends Component
{
    public $user;

    public $team;

    public $teamMembers;

    public $selectedUsers = [];

    public $events;

    public $allUrlIcsEvents = [];

    public $calendarUrls = [];

    public function __construct()
    {
        $this->user = auth()->user();
        $this->team = $this->user->currentTeam;
    }

    ////////////////////////////////////////////////////////////////////////////////////////
    /// Events ///
    ////////////////////////////////////////////////////////////////////////////////////////

    public function create($data)
    {
        $event = new Events($data);
        $event->save();

        return $event;
    }

    public function update($id, $data)
    {

        $event = Events::find($id);
        $event->fill($data);
        $event->save();

        return $event;
    }

    #[On('aUserHasBeenSelected')]
    public function refetchEvents($selectedUsers)
    {
        if (count($selectedUsers) > 1) {

            if (! $this->user->isAdminOrModerateur($this->team)) {

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

            if ($events) {

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
        }

        $this->events = json_encode($allUsersEvents);
        
        $this->events = json_decode($this->events);
        
        return $this->dispatch('eventsHaveBeenFetched');
    }

    ////////////////////////////////////////////////////////////////////////////////////////
    /// Render ///
    ////////////////////////////////////////////////////////////////////////////////////////

    public function render()
    {
        return view('livewire.calendar')->with([
            'team' => $this->team,
        ]);
    }
}
