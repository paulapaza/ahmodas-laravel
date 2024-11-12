<aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
    <div class="p-3">
        <h5>Configuracion de usuario</h5>

        @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
            <div class="text-center mb-3">
                <img class="" src="{{ Auth::user()->profile_photo_url }}"
                    alt="{{ Auth::user()->name }}" width="120px"/>
            </div>

        @endif

        <ul class="text-right mr-5 list-unstyled" style="left: 0px; right: inherit;">
            <li class="my-0 ">
                <a href="{{ route('profile.show') }}" class="">Mi perfil</a>
            </li>
            <li>
                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}" x-data>
                    @csrf
                    <a href="route('logout')"
                                onclick="event.preventDefault();
                                            this.closest('form').submit();">
                            {{ __('Log Out') }}
                    </a>
                </form>
            </li>

        </ul>
    </div>
</aside>