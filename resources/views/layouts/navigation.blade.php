<nav class="bg-white shadow">
    <div class="container mx-auto px-6 py-3">
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('forms.index') }}" class="text-xl font-bold text-gray-800">{{ config('app.name', 'Form Builder') }}</a>
            </div>
            <div>
                @auth
                <a href="{{ route('forms.index') }}" class="text-gray-800 mx-2">My Forms</a>
                <a href="{{ route('logout') }}" class="text-gray-800 mx-2"
                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    Logout
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                    @csrf
                </form>
                @else
                <a href="{{ route('login') }}" class="text-gray-800 mx-2">Login</a>
                <a href="{{ route('register') }}" class="text-gray-800 mx-2">Register</a>
                @endauth
            </div>
        </div>
    </div>
</nav>
