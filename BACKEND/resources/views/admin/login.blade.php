<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <form method="POST" action="{{ route('admin.login') }}" class="max-w-md w-full bg-white p-8 rounded-lg shadow-md">
        @csrf
        <h2 class="text-2xl font-bold mb-6 text-center">Login</h2>

        <div class="mb-4">
            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}" required
                class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:outline-none focus:ring focus:ring-blue-500" />
        </div>

        <div class="mb-4">
            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
            <input type="password" name="password" id="password" required
                class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:outline-none focus:ring focus:ring-blue-500" />
        </div>

        <button type="submit"
            class="w-full bg-blue-500 text-white rounded-md px-4 py-2 hover:bg-blue-600 transition duration-200">Login</button>

        @if($errors->any())
            <div class="mt-4 text-red-500 text-sm">
                {{ $errors->first() }}
            </div>
        @endif
    </form>
</body>

</html>
