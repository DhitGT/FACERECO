const video = document.getElementById("video");

// Load models
Promise.all([
    faceapi.nets.ssdMobilenetv1.loadFromUri("/models"),
    faceapi.nets.faceRecognitionNet.loadFromUri("/models"),
    faceapi.nets.faceLandmark68Net.loadFromUri("/models"),
]).then(startWebcam);

function startWebcam() {
    navigator.mediaDevices
        .getUserMedia({
            video: true,
            audio: false,
        })
        .then((stream) => {
            video.srcObject = stream;
        })
        .catch((error) => {
            console.error(error);
        });
}

async function getClassNames() {
    // Fetch class names dynamically from the API
    const response = await fetch('http://127.0.0.1:8000/api/classes');
    if (!response.ok) {
        console.error('Failed to fetch class names');
        return [];
    }
    return await response.json();
}

async function getLabeledFaceDescriptions() {
    const labels = await getClassNames(); // Get class names from the API
    return Promise.all(
        labels.map(async (label) => {
            const descriptions = [];
            for (let i = 1; i <= label.image_count; i++) {  // Assuming 2 images per class
                try {
                    const img = await faceapi.fetchImage(`http://127.0.0.1:8000/images/${label.class_name}/${i}.jpg`);
                    const detections = await faceapi
                        .detectSingleFace(img)
                        .withFaceLandmarks()
                        .withFaceDescriptor();

                    // Ensure detections are valid before accessing the descriptor
                    if (detections) {
                        descriptions.push(detections.descriptor);
                    } else {
                        console.warn(`No face detected in image for label: ${label.class_name}`);
                    }
                } catch (error) {
                    console.error(`Failed to fetch or process image for label: ${label.class_name}, image ${i}`, error);
                }
            }
            return new faceapi.LabeledFaceDescriptors(label.class_name, descriptions);
        })
    );
}


video.addEventListener("play", async () => {
    const labeledFaceDescriptors = await getLabeledFaceDescriptions();
    const faceMatcher = new faceapi.FaceMatcher(labeledFaceDescriptors);

    const canvas = faceapi.createCanvasFromMedia(video);
    document.body.append(canvas);

    const displaySize = { width: video.width, height: video.height };
    faceapi.matchDimensions(canvas, displaySize);

    setInterval(async () => {
        const detections = await faceapi
            .detectAllFaces(video)
            .withFaceLandmarks()
            .withFaceDescriptors();

        // Ensure detections are valid before processing
        if (detections && detections.length > 0) {
            const resizedDetections = faceapi.resizeResults(detections, displaySize);

            canvas.getContext("2d").clearRect(0, 0, canvas.width, canvas.height);

            const results = resizedDetections.map((d) => {
                return faceMatcher.findBestMatch(d.descriptor);
            });

            console.log("RESULT : ", results)

            results.forEach((result, i) => {
                const box = resizedDetections[i].detection.box;
                const drawBox = new faceapi.draw.DrawBox(box, {
                    label: result,
                });
                drawBox.draw(canvas);
            });
        }
    }, 100);
});
