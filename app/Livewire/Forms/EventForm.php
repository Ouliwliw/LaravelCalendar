<?php

namespace App\Livewire\Forms;

use App\Models\Events;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Livewire\Form;

class EventForm extends Form
{
    public ?Events $events;

    public string $timezone;

    public int $user_id;

    public string $start;

    public string $end;

    public string $title;

    public ?string $description = null;

    public string $category = 'RDV';

    public string $visibility = 'public';

    public $visibilityForm;

    public $statusForm;

    public $categoriesForm;

    public int $status = 0;

    public bool $is_all_day = false;

    public string $backgroundColor;

    public string $borderColor;

    /**
     * Définit les règles de validation pour le formulaire.
     */
    protected function rules(): array
    {
        $this->visibilityForm = include base_path('app/Tableaux/Visibility.php');
        $this->statusForm = array_keys(include base_path('app/Tableaux/Status.php'));
        $this->categoriesForm = include base_path('app/Tableaux/Categories.php');

        return [
            'user_id' => 'required|integer',
            'title' => 'required|string|max:255',
            'start' => 'required|date',
            'end' => 'required|date|after:start',
            'description' => 'nullable|string|max:1000',
            'status' => ['required', 'integer', Rule::in($this->statusForm)],
            'is_all_day' => 'required|boolean',
            'visibility' => ['required', 'string', Rule::in($this->visibilityForm)],
            'category' => ['required', 'string', Rule::in($this->categoriesForm)],
            'backgroundColor' => 'required|string',
            'borderColor' => 'required|string',
        ];
    }

    public function setEvent(Events $events, $timezone): void
    {
        $this->events = $events;
        $this->user_id = $events->user_id;
        $this->title = $events->title;
        $this->description = $events->description;
        $this->start = $events->start;
        $this->end = $events->end;
        $this->status = $events->status;

        // @phpstan-ignore-next-line
        $this->is_all_day = $events->is_all_day;
        $this->visibility = $events->visibility;
        $this->category = $events->category;
        $this->backgroundColor = $events->backgroundColor;
        $this->borderColor = $events->borderColor;
    }

    public function store($timezone): void
    {
        $user = auth()->user();
        $parsedStartWithTimeZone = Carbon::parse($this->start, $timezone);
        $parsedEndWithTimeZone = Carbon::parse($this->end, $timezone);

        $parsedStartIfFullDay = Carbon::parse($this->start);
        $parsedEndIfFullDay = Carbon::parse($this->end);

        $this->user_id = $user->id;
        $this->backgroundColor = $user->color;
        $this->borderColor = $user->color. 80;
        $this->timezone = $timezone;

        if ($this->is_all_day) {
            $this->start = $parsedStartIfFullDay->startOfDay();
            $this->end = $parsedEndIfFullDay->endOfDay();
        } else {
            $this->start = $parsedStartWithTimeZone->setTimezone('UTC')->toIso8601String();
            $this->end = $parsedEndWithTimeZone->setTimezone('UTC')->toIso8601String();
        }

        $validatedData = $this->validate();

        Events::create($validatedData);
    }

    public function update($timezone)
    {
        $validatedData = $this->validate();

        if ($this->is_all_day) {

            $validatedData['start'] = Carbon::parse($validatedData['start'])->startOfDay();
            $validatedData['end'] = Carbon::parse($validatedData['end'])->endOfDay();
        } else {

            $validatedData['start'] = Carbon::parse($validatedData['start'], $timezone)->setTimezone('UTC')->toIso8601String();
            $validatedData['end'] = Carbon::parse($validatedData['end'], $timezone)->setTimezone('UTC')->toIso8601String();
        }

        $this->events->update($validatedData);
    }
}
