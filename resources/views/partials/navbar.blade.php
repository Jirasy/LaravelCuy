<div class="flex justify-between items-center bg-white border border-gray-200 rounded-xl px-4 py-3 mb-6 text-sm">
    <div>
        Halo, <strong>{{ auth()->user()->name }}</strong>
        <span class="ml-2 px-2 py-0.5 text-xs rounded-full
            {{ auth()->user()->isAdmin() ? 'bg-purple-100 text-purple-800' : (auth()->user()->isPembina() ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">
            {{ ucfirst(auth()->user()->role) }}
        </span>
    </div>
    <div class="flex items-center gap-4">
        @if(auth()->user()->isAdmin())
            <a href="{{ route('users.index') }}" class="text-gray-700 hover:underline">Kelola User</a>
        @endif
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="text-red-600 hover:underline font-semibold">Keluar</button>
        </form>
    </div>
</div>