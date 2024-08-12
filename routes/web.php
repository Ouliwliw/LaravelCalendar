<?php

use App\Http\Controllers\DAVController;
use App\Http\Middleware\AccesSabreJustAdmin;
use App\Livewire\Calendar;
use App\Livewire\CreateTeamController;
use App\Livewire\CreateUserController;
use App\Livewire\EventComponent;
use App\Livewire\TeamSettingsController;
use App\Livewire\Welcome;
use GuzzleHttp\Client;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Spatie\IcalendarGenerator\Components\Calendar as ComponentsCalendar;
use Spatie\IcalendarGenerator\Components\Event;

Route::get('/dd', function () {
    $fetch = new EventComponent();
    dd($fetch->refetchEvents());
});

Route::get('/dd2', function () {

    $client = new Client();
    $url = 'http://localhost/dav/calendars/6fa5bf2dd665bfd42687/lecalendrierdeAdmin/LaraconOnline.ics';

    $test = Event::create()
        ->name('Laracon Online')
        ->description('Experience Laracon all around the world')
        ->uniqueIdentifier('A unique identifier can be set here')
        ->createdAt(new DateTime('6 march 2024'))
        ->startsAt(new DateTime('6 march 2024 15:00'))
        ->endsAt(new DateTime('6 march 2024 16:00'));

    $cal = ComponentsCalendar::create()->event($test)->get();

    $response = $client->request('PUT', $url, [
        'body' => $cal,
        'headers' => [
            'Content-Type' => 'text/calendar; charset=UTF-8',
            'If-None-Match' => '*',
        ],
    ]);
});

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    Route::get('/', Welcome::class)->name('welcome');
    Route::get('refetch-events/', "App\Livewire\EventComponent@refetchEvents")->name('refetch-events');

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/calendar', Calendar::class)->name('calendar');

    Route::get('/teams/create', CreateTeamController::class)->name('teams_create');
    Route::get('/teams/{team}', TeamSettingsController::class)->name('teams_settings');
    Route::get('/user/create', CreateUserController::class)->name('user_create');

    Route::get('/current-team', function () {
        return redirect(url()->previous());
    })->name('current_team');
});

$verbs = [
    'GET',
    'HEAD',
    'POST',
    'PUT',
    'PATCH',
    'DELETE',
    'PROPFIND',
    'PROPPATCH',
    'MKCOL',
    'COPY',
    'MOVE',
    'LOCK',
    'UNLOCK',
    'OPTIONS',
    'REPORT',
];
Router::$verbs = array_merge(Router::$verbs, $verbs);
$urlName = config('app.laravelSabreRoot');

Route::any('/'.$urlName.'/'.'calendars'.'/'.'{path}', [DAVController::class, 'init'])
    ->name('sabre.dav.calendars.user')
    ->where('path', '(.)*')->withoutMiddleware(AccesSabreJustAdmin::class);

Route::any('/'.$urlName.'{path?}', [DAVController::class, 'init'])
    ->name('sabre.dav')
    ->where('path', '(.)*')->middleware(AccesSabreJustAdmin::class);
