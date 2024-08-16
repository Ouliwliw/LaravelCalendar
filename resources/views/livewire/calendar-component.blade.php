<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 border-box">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg max-h-90% border-box">
            <div class="p-6 pt-0 lg:p-8 border-b border-box">
                <div class="max-w-7xl mx-auto border-box">
                    <div class="p-6 pt-0 lg:p-8 text-center">
                        <p class="text-lg leading-6 text-gray-500">
                            Mon calendrier : {{ $this->calendarUrlUserConnected }}
                        </p>
                        <p class="text-sm italic text-red-800">
                            *à copier dans votre calendrier personnel
                        </p>

                        {{-- @foreach ($this->calendarUrls as $key => $calendar)
                            @if ($key == 'team')
                                <p class="text-lg leading-6 text-gray-500"> {{ $team->name }} : {{ $calendar }}
                                </p>
                            @else
                                <p class="text-lg leading-6 text-gray-500"> {{ $this->namesUsers[$key] }} :
                                    {{ $calendar }} </p>
                            @endif
                        @endforeach --}}
                        <p class="text-sm italic text-red-500">
                            Les modification prises en compte par votre
                            calendrier personnels sont uniquement la date, les modifications de titres, ou autres
                            elements ne seront pas pris en compte dans <strong>HubSpot</strong>.
                        </p>

                    </div>
                    <div class="border-box">
                        <div id="calendar-container" wire:ignore class="border-box">
                            <div id="calendar" class="border-box">
                            </div>
                        </div>
                    </div>

                    <script src="https://cdn.jsdelivr.net/npm/fullcalendar/index.global.min.js"></script>
                    <script>
                        let calendar;
                        let timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;

                        document.addEventListener("livewire:initialized", function() {
                            let calendarEl = document.querySelector("#calendar");

                            calendar = new FullCalendar.Calendar(calendarEl, {
                                editable: true,
                                selectable: true,
                                droppable: true,
                                selectMirror: true,
                                aspectRatio: 1.5,
                                locale: "fr",
                                weekNumberCalculation: "ISO",
                                initialView: "timeGridWeek",
                                headerToolbar: {
                                    left: "prevYear,prev,next,nextYear today",
                                    center: "title",
                                    right: "dayGridMonth,timeGridWeek,timeGridDay,listWeek",
                                },
                                views: {
                                    dayGridMonth: {
                                        fixedWeekCount: false,
                                        showNonCurrentDates: true,
                                    },
                                },

                                eventMouseEnter: function(info) {
                                    let event = info.event;

                                    var tooltip = window.tippy(info.el, {
                                        interactive: true,
                                        appendTo: () => document.body,
                                        placement: 'auto',
                                        delay: 300,
                                        theme: 'material',
                                        allowHTML: true,
                                        content: 'TITRE : ' + event.title +
                                            '<br>' +
                                            'DESCRIPTION : ' + event.extendedProps.description +
                                            '<br>' +
                                            'TYPE : ' + event.extendedProps.category +
                                            '<br>' +
                                            'DEBUT : ' + event.start +
                                            '<br>' +
                                            'FIN : ' + event.end +
                                            '<br>',
                                    });
                                },

                                eventResize: function(info) {
                                    @this.updateEvent(info.event.id, info.event.startStr, info.event.endStr);
                                },

                                eventDrop: function(info) {
                                    @this.updateEvent(info.event.id, info.event.startStr, info.event.endStr, info.event
                                        .allDay);
                                },

                                select: function(info) {
                                    openNewModal(info);
                                },
                                eventClick: function(info) {
                                    openModal(info);
                                },
                            });

                            calendar.render();

                            Livewire.on("eventsHaveBeenFetched", () => {

                                calendar.removeAllEventSources();

                                calendar.addEventSource(fetchJSONEvents());

                                console.log("Events have been fetched");
                            });

                            function openModal(info) {
                                Livewire.dispatch("openModal", {
                                    component: "event-modal",
                                    arguments: {
                                        events: info.event.id,
                                        timezone: timezone,
                                        start: info.event.startStr,
                                        end: info.event.endStr,
                                    },
                                });
                            }

                            function openNewModal(info) {
                                Livewire.dispatch("openModal", {
                                    component: "event-modal",
                                    arguments: {
                                        timezone: timezone,
                                        start: info.startStr,
                                        end: info.endStr,
                                    },
                                });
                            }

                            setInterval(function() {
                                calendar.refetchEvents();
                            }, 30 * 1000);

                            function fetchJSONEvents() {
                                return @this.events;
                            }
                        });
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>

