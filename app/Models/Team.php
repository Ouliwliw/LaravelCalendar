<?php

namespace App\Models;

use App\Http\Services\LaravelSabreCalendarHome;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Jetstream\Events\TeamCreated;
use Laravel\Jetstream\Events\TeamDeleted;
use Laravel\Jetstream\Events\TeamUpdated;
use Laravel\Jetstream\Team as JetstreamTeam;

class Team extends JetstreamTeam
{
    use HasFactory;

    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'personal_team',
    ];

    /**
     * The event map for the model.
     *
     * @var array<string, class-string>
     */
    protected $dispatchesEvents = [
        'created' => TeamCreated::class,
        'updated' => TeamUpdated::class,
        'deleted' => TeamDeleted::class,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'personal_team' => 'boolean',
        ];
    }

    public function teamUser()
    {
        return $this->hasMany(TeamUser::class);
    }

    public function principal()
    {
        return $this->hasOne(Principal::class, 'id', 'principal_id');
    }

    public function createPrincipal($name)
    {
        $principal = new Principal();
        $hashDossier = $this->hashName($name);
        $principal->uri = 'principals/'.$hashDossier;
        $principal->displayname = $this->name;
        $principal->save();

        $this->principal_id = $principal->id;

        $this->createCalendar();

        $this->save();

        return $principal;
    }

    public function hashName($name)
    {
        return md5(hash('sha256', ('team'.$name)));
    }

    public function createTeam($name, $user_id)
    {
        // $team = new Team();
        $this->name = $name;
        $this->user_id = $user_id;
        $this->personal_team = true;
        $this->createPrincipal($name);
    }

    public function createCalendar()
    {
        $laravelCalendarHome = new LaravelSabreCalendarHome();
        $laravelCalendarHome->createCalendarTeamOrUser('CalendarTeam', $this->name, $this);
    }

    public function changeUserPersonalTeam($user_id)
    {
        $this->user_id = $user_id;
        $this->save();
    }

    public function getCalendarUrl()
    {
        $laravelSabreRoot = config('app.laravelSabreRoot');
        $appRoot = config('app.appRoot');

        $calendar = Calendarinstances::where('displayname', $this->name)->first();

        return $appRoot.'/'.$laravelSabreRoot.'/calendars/'.$this->hashName($this->name).'/'.$calendar->uri;
    }

    public function getEvents()
    {
        $calendar = Calendarinstances::where('displayname', $this->name)->first();

        return $calendarobject = Calendarobject::where('calendarid', $calendar->calendarid)->get();
    }
}
