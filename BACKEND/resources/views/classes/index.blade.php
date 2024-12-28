@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold mb-6">Manage Classes and Images</h1>

    <form action="{{ route('classes.store') }}" method="POST" class="mb-6">
        @csrf
        <div class="flex items-center space-x-4">
            <input type="text" name="name" placeholder="Class Name" required
                class="border border-gray-300 rounded-md p-2 flex-1" />
            <button type="submit" class="bg-blue-500 text-white rounded-md px-4 py-2 hover:bg-blue-600">Create Class</button>
        </div>
    </form>

    <div>
        @foreach ($classes as $class)
        <div class="bg-white shadow-md rounded-lg p-4 mb-6">
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold truncate">{{ $class->name }}</h2>
                <form action="{{ route('classes.destroy', $class->id) }}" method="POST" class="ml-4">
                    @csrf
                    {{ method_field('DELETE') }}
                    <button type="submit" class="text-red-500 hover:text-red-700">...</button>
                </form>
            </div>

            <!-- Upload Image Section -->
            <form action="{{ route('images.store', $class->id) }}" method="POST" enctype="multipart/form-data"
                class="mt-4" id="uploadForm{{ $class->id }}">
                @csrf
                <div class="flex items-center space-x-4">
                    <input type="file" accept=".jpg" name="images[]" id="fileInput{{ $class->id }}"
                        class="border border-gray-300 rounded-md p-2 flex-1" multiple />
                    <button type="submit" class="bg-green-500 text-white rounded-md px-4 py-2 hover:bg-green-600">Upload
                        Image</button>
                </div>
            </form>

            <!-- Capture Image Section -->
            <div class="mt-4">
                <button class="bg-yellow-500 text-white rounded-md px-4 py-2 hover:bg-yellow-600 openCameraBtn"
                    data-class-id="{{ $class->id }}">Open Camera</button>
                <div id="cameraContainer{{ $class->id }}" class="mt-4 hidden">
                    <video id="cameraStream{{ $class->id }}" autoplay playsinline class="w-full max-w-md rounded-md"></video>
                    <button class="bg-blue-500 text-white rounded-md px-4 py-2 hover:bg-blue-600 mt-2 captureBtn"
                        data-class-id="{{ $class->id }}">Hold to Capture</button>
                    <canvas id="captureCanvas{{ $class->id }}" class="hidden"></canvas>
                    <div class="mt-4 flex flex-wrap" id="previewContainer{{ $class->id }}"></div>

                    <!-- Save Captured Images -->
                    <button class="bg-red-500 text-white rounded-md px-4 py-2 hover:bg-red-600 mt-4 saveCapturedBtn"
                        data-class-id="{{ $class->id }}">
                        Save Captured Images
                    </button>
                </div>
            </div>

            <!-- Existing Images -->
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

<script>
    document.addEventListener('DOMContentLoaded', () => {
        let videoStreams = {}; // Store individual video streams
        let captureIntervals = {}; // Store intervals for each class capture session
        let capturedImages = {}; // Store captured images for each class

        // Open Camera and Start Stream for each Class
        document.querySelectorAll('.openCameraBtn').forEach(button => {
            button.addEventListener('click', async (event) => {
                const classId = event.target.dataset.classId;
                const cameraContainer = document.getElementById(`cameraContainer${classId}`);
                const cameraStream = document.getElementById(`cameraStream${classId}`);
                const toggleBtn = event.target;

                if (cameraContainer.classList.contains('hidden')) {
                    cameraContainer.classList.remove('hidden');
                    toggleBtn.textContent = 'Close Camera';

                    // Start video stream for the class
                    if (!videoStreams[classId]) {
                        videoStreams[classId] = await navigator.mediaDevices.getUserMedia({
                            video: true
                        });
                        cameraStream.srcObject = videoStreams[classId];
                    }
                } else {
                    cameraContainer.classList.add('hidden');
                    toggleBtn.textContent = 'Open Camera';

                    // Stop video stream for the class
                    const tracks = videoStreams[classId]?.getTracks();
                    if (tracks) {
                        tracks.forEach(track => track.stop());
                        cameraStream.srcObject = null;
                        delete videoStreams[classId]; // Free the video stream
                    }
                }
            });
        });

        // Hold to Capture Images for each Class
        document.querySelectorAll('.captureBtn').forEach(button => {
            button.addEventListener('mousedown', startCapture);
            button.addEventListener('mouseup', stopCapture);
            button.addEventListener('mouseleave', stopCapture);

            function startCapture(event) {
                const classId = event.target.dataset.classId;

                captureIntervals[classId] = setInterval(() => captureImage(classId), 500);
            }

            function stopCapture(event) {
                const classId = event.target.dataset.classId;

                clearInterval(captureIntervals[classId]);
                captureIntervals[classId] = null;
            }

            function captureImage(classId) {
                const cameraStream = document.getElementById(`cameraStream${classId}`);
                const captureCanvas = document.getElementById(`captureCanvas${classId}`);
                const previewContainer = document.getElementById(`previewContainer${classId}`);
                const context = captureCanvas.getContext('2d');

                captureCanvas.width = cameraStream.videoWidth;
                captureCanvas.height = cameraStream.videoHeight;
                context.drawImage(cameraStream, 0, 0, captureCanvas.width, captureCanvas.height);

                // Preview the captured image
                const imgUrl = captureCanvas.toDataURL('image/jpeg');
                const div = document.createElement('div');
                div.className = 'relative w-24 h-24 m-2';
                div.innerHTML = `<img src="${imgUrl}" class="w-full h-full object-cover rounded-md" alt="Captured Image" />`;
                previewContainer.appendChild(div);

                // Save the captured image to array for the respective class
                if (!capturedImages[classId]) capturedImages[classId] = [];
                capturedImages[classId].push(imgUrl);
            }
        });

        // Save Captured Images for the Class
        document.querySelectorAll('.saveCapturedBtn').forEach(button => {
            button.addEventListener('click', (event) => {
                const classId = event.target.dataset.classId;

                if (capturedImages[classId] && capturedImages[classId].length > 0) {
                    const formData = new FormData();
                    capturedImages[classId].forEach((imgData, index) => {
                        const blob = dataURLToBlob(imgData);
                        formData.append('images[]', blob, `capture_${Date.now() + index}.jpg`);
                    });

                    formData.append('_token', '{{ csrf_token() }}');

                    // Send the images for the specific class
                    fetch(`{{ route('images.store', ':class_id') }}`.replace(':class_id', classId), {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(() => {
                            // alert('Captured images saved successfully!');
                            location.reload(); // Reload page
                        })
                        .catch(err => {
                            console.error('Upload failed:', err);
                            // alert('Failed to save images.');
                            location.reload(); // Reload page
                        });
                    } else {
                        alert('No images captured to save.');
                    }
            });
        });

        // Convert data URL to Blob
        function dataURLToBlob(dataUrl) {
            const byteString = atob(dataUrl.split(',')[1]);
            const arrayBuffer = new ArrayBuffer(byteString.length);
            const uInt8Array = new Uint8Array(arrayBuffer);
            for (let i = 0; i < byteString.length; i++) {
                uInt8Array[i] = byteString.charCodeAt(i);
            }
            return new Blob([uInt8Array], {
                type: 'image/jpeg'
            });
        }
    });
</script>
@endsection