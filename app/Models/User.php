<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Exception;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Jetstream\HasProfilePhoto;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use App\Http\Services\LaravelSabreCalendarHome;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto {
        HasProfilePhoto::profilePhotoUrl as getPhotoUrl;
    }
    use HasRoles;
    use HasTeams;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the URL to the user's profile photo.
     */
    public function profilePhotoUrl(): Attribute
    {
        return filter_var($this->profile_photo_path, FILTER_VALIDATE_URL)
            ? Attribute::get(fn () => $this->profile_photo_path)
            : $this->getPhotoUrl();
    }

   

    public function teamInvitation(): HasMany
    {
        return $this->hasMany(TeamInvitation::class);
    }


    

    public function canDoAction($roleName, $teamId): bool
    {
        $roleId = Role::where('name', $roleName)->first()->id;
        $roleAdminId = Role::where('name', 'Admin')->first()->id;


        // Vérifiez si l'utilisateur a le rôle dans l'équipe spécifiée
        $teamRole = $this->teams()->where('teams.id', $teamId)->first()->membership->role;

        if ($teamRole === $roleAdminId) {
            return true;
        }


        return $teamRole === $roleId;
    }
    public function isAdmin(): bool
    {
        return $this->hasRole('Admin');
    }

    
   
    public function hashUserName()
    {
        return md5(hash('sha256', $this->username));
    }

    public function principal()
    {
        return $this->hasOne(Principal::class, 'id', 'principal_id');
    }

    public function createPrincipal()
    {
        //Partie Crééer Principal
        $principal = new Principal();
        $hashDossier = $this->hashUserName();
        $principal->uri = 'principals/' . $hashDossier;
        $principal->email = $this->email;
        $principal->displayname = $this->username;
        $principal->save();
        

        //Partie Ajout de l'id du principal dans la table User
        $this->principal_id = $principal->id;
        $this->save();
        
        //Partie Créer le calendrier 
        $this->createCalendar();

        return $principal;
    }
    
    

    static function createUser($name, $username, $email){

        //Creation User 
        $user = new User();
        $user->name = $name;
        $user->username = $username;
        $user->email = $email;
        $user->password = Hash::make(config('app.password')) ;
        $user->save();
        //Creation Principal
        $principal = $user->createPrincipal();

    }




    public function assignTeam($teamId, $roleName)
    {
        if ($roleName == 'Admin'){
            throw new Exception('Admin can not be assigned to a team');
        }
        else if ($roleName == 'Moderateur'){
            $this->assignRoleAndTeam('Moderateur',$teamId);
        }
        else if ($roleName == 'Utilisateur'){
            $this->assignRoleAndTeam('Utilisateur',$teamId);
        }
        else {
            throw new Exception('Role not found');
        }
    }
    public function assignRoleAndTeam($roleName, $teamId)
    {
        $team=Team::where('id',$teamId)->first();
        $role = Role::where('name', $roleName)->first();

        $team->users()->attach($this->id, ['role' => $role->id, 'model_type' => 'App\Models\User']);

        $this->current_team_id = $teamId;
        $this->save();
    }

    public function createCalendar(){
        $laravelCalendarHome = new LaravelSabreCalendarHome();
        $laravelCalendarHome->createCalendarTeamOrUser('CalendarUser', $this->username, $this);   
    }

    public function assignJustRole($roleName)
    {
        $role=Role::where('name',$roleName)->first();
        $pivotTable= new TeamUser();
        $pivotTable->role=$role->id;
        $pivotTable->model_type='App\Models\User';
        $pivotTable->user_id=$this->id;
        $pivotTable->save();    
    }
    public function createTeamPrincipal($name)
    {
        $team = new Team();
        $team->createTeam($name, $this->id);
        

        // assigner le role 
        $this->assignRoleAndTeam('Moderateur', $team->id);
        $adminId=TeamUser::where('role',Role::where('name','Admin')->first()->id)->first()->user_id;
        $admin=User::where('id',$adminId)->first();
        $admin->assignRoleAndTeam('Admin',$team->id);

    }
    public function joinTeam($nameRole,$teamId)
    {
        $team=Team::where('id',$teamId)->first();
        $this->assignRoleAndTeam($nameRole, $team->id);
    }

    
    
    
}
