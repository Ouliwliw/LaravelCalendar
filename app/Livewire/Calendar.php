<?php

namespace App\Livewire;

use App\Models\Events;
use Livewire\Component;

class Calendar extends Component
{
    public $user;

    public $team;

    public $teamMembers;

    public $selectedUsers = [];

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

    public function refetchEvents($selectedUsers = null)
    {
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

        return json_encode($allUsersEvents);
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
