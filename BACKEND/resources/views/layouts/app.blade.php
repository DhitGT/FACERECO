<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>
        @yield('title', 'Dashboard')</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100">
    <nav class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <a class="text-xl font-bold text-gray-800" href="#">Dashboard</a>
                <div class="hidden md:flex space-x-4">
                    <form action="{{ route('dashboard') }}" method="GET" id="dashboard-form" class="inline">
                        @csrf
                        <button type="submit" class="text-gray-600 hover:text-gray-800">Dashboard</button>
                    </form>
                    <form action="{{ route('classes') }}" method="GET" id="classes-form" class="inline">
                        @csrf
                        <button type="submit" class="text-gray-600 hover:text-gray-800">Classes</button>
                    </form>
                    <form action="{{ route('admin.logout') }}" method="POST" id="logout-form" class="inline">
                        @csrf
                        <button type="submit" class="text-gray-600 hover:text-gray-800">Logout</button>
                    </form>

                </div>
                <button class="md:hidden text-gray-600 focus:outline-none" id="navbar-toggle">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16m-7 6h7"></path>
                    </svg>
                </button>
            </div>
        </div>
        <div class="md:hidden" id="navbar-menu">
            <div class="px-4 py-2">
                <form action="{{ route('admin.logout') }}" method="POST" id="logout-form" class="inline">
                    @csrf
                    <button type="submit" class="text-gray-600 hover:text-gray-800">Logout</button>
                </form>

            </div>
        </div>
    </nav>

    <main class="container mx-auto py-6">
        @yield('content')
    </main>

    <footer class="text-center py-4 bg-white shadow mt-6">
        &copy;
        {{ date('Y') }} Image Manager
    </footer>

    <script>
        const toggleButton = document.getElementById('navbar-toggle');
        const navbarMenu = document.getElementById('navbar-menu');

        toggleButton.addEventListener('click', () => {
            navbarMenu.classList.toggle('hidden');
        });
    </script>
</body>

</html>
