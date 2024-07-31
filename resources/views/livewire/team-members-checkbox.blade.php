<div>

    <div class="mt-10 sm:mt-0">
        <x-calendar-team-action-section>
            <x-slot name="title">
                {{ __("Membre de l'équipe") }}
            </x-slot>

            <x-slot name="description">

            </x-slot>

            <!-- Team Member List -->
            <x-slot name="content">
                <div class="space-y-6">
                    @if ($team !== null)

                        <div class="flex items-center justify-between">

                            <div class="flex items-center">
                                @if ($this->user->isAdminOrModerateur($team))
                                    <div class="ms-4">
                                        Tous
                                    </div>
                                @else
                                    <div class="ms-4">
                                        Afficher les events
                                    </div>
                                @endif

                            </div>
                            <div class="flex items">
                                @if ($this->user->isAdminOrModerateur($team))
                                    <input type="checkbox" wire:model="allTeamMembersSelected"
                                        wire:click="allCheckedBox()"
                                        class="form-checkbox h-5 w-5 transition duration-100 ease-in-out"
                                        style="color: black " />
                                @else
                                    <input type="checkbox" wire:model="selectedUsers" value="{{ $this->user->id }}"
                                        wire:click="checkedBox()" class="form-checkbox h-5 w-5"
                                        style="color: {{ $this->user->color }}" />
                                @endif

                                {{-- <label for="">{{ $user->id }}</label> --}}

                            </div>

                        </div>

                        @foreach ($this->teamMembers->sortBy('name') as $user)
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <img class="w-8 h-8 rounded-full object-cover" src="{{ $user->profile_photo_url }}"
                                        alt="{{ $user->name }}">
                                    <div class="ms-4  {{ $this->user->id === $user->id ? 'ms-4 font-bold' : '' }}">
                                        {{ $user->name }} {{ $this->user->id === $user->id ? '( moi )' : '' }}
                                    </div>

                                </div>
                                <div class="flex items ms-2">
                                    {{-- <label for="">{{ $user->id }}</label> --}}
                                    @isAdminOrModerateur($team)
                                    <input type="checkbox" wire:model="selectedUsers" value="{{ $user->id }}"
                                        wire:click="checkedBox()" class="form-checkbox h-5 w-5"
                                        style="color: {{ $user->color }}" />
                                    @endisAdminOrModerateur()
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <img class="w-8 h-8 rounded-full object-cover"
                                    src="{{ $this->userOnly->profile_photo_url }}" alt="{{ $this->userOnly->name }}">
                                <div class="ms-4   'ms-4 font-bold'  }}">
                                    {{ $this->userOnly->name }}
                                    {{ $this->userOnly->id === $this->userOnly->id ? '( moi, userOnly )' : '' }}
                                </div>

                            </div>
                            <div class="flex items ms-2">
                                {{-- <label for="">{{ $user->id }}</label> --}}
                                <input type="checkbox" wire:model="selectedUsers" value="{{ $this->user->id }}"
                                    wire:click="checkedBox()" class="form-checkbox h-5 w-5"
                                    style="color: {{ $this->user->color }}" />
                            </div>
                        </div>
                    @endif

                </div>
            </x-slot>
        </x-calendar-team-action-section>
    </div>

</div>

