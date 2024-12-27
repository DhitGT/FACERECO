@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold mb-6">Manage Classes and Images</h1>

    <form action="{{ route('classes.store') }}" method="POST" class="mb-6">
        @csrf
        <div class="flex items-center space-x-4">
            <input type="text" name="name" placeholder="Class Name" required
                class="border border-gray-300 rounded-md p-2 flex-1" />
            <button type="submit" class="bg-blue-500 text-white rounded-md px-4 py-2 hover:bg-blue-600">Create
                Class</button>
        </div>
    </form>

    <div>
        @foreach ($classes as $class)
            <div class="bg-white shadow-md rounded-lg p-4 mb-6">
                <div class="flex justify-between items-center">
                    <h2 class="text-xl font-semibold truncate">
                        {{ $class->name }}
                    </h2>
                    <form action="{{ route('classes.destroy', $class->id) }}" method="POST" class="ml-4">
                        @csrf
                        {{ method_field('DELETE') }}
                        <button type="submit" class="text-red-500 hover:text-red-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </form>
                </div>

                <form action="{{ route('images.store', $class->id) }}" method="POST" enctype="multipart/form-data"
                    class="mt-4">
                    @csrf
                    <div class="flex items-center space-x-4">
                        <input type="file" accept=".jpg" name="images[]" id="images"
                            class="border border-gray-300 rounded-md p-2 flex-1" multiple required />
                        <button type="submit" class="bg-green-500 text-white rounded-md px-4 py-2 hover:bg-green-600">Upload
                            Image</button>
                    </div>
                </form>

                <div class="mt-4 flex flex-wrap">
                    @foreach ($class->images as $image)
                        <div class="relative w-24 h-24 m-2">
                            <img src="{{ url("images/{$class->name}/{$loop->iteration}.jpg") }}"
                                class="w-full h-full object-cover rounded-md" alt="Image {{ $loop->iteration }}">
                            <form action="{{ route('images.destroy', [$class->id, $image->id]) }}" method="POST"
                                class="absolute top-0 right-0">
                                @csrf
                                {{ method_field('DELETE') }}
                                <button type="submit" class="text-red-500 hover:text-red-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
