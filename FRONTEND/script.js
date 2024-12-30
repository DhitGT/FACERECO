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
async function getAbsen() {
    // Fetch class names dynamically from the API
    const response = await fetch('http://127.0.0.1:8000/api/absen');
    if (!response.ok) {
        console.error('Failed to fetch class names');
        return [];
    }
    return await response.json();
}
async function getAbsenExit() {
    // Fetch class names dynamically from the API
    const response = await fetch('http://127.0.0.1:8000/api/absenExit');
    if (!response.ok) {
        console.error('Failed to fetch class names');
        return [];
    }
    return await response.json();
}

const speakText = (text2) => {
    const text = text2

    // Check if the browser supports SpeechSynthesis
    if ('speechSynthesis' in window) {
        const msg = new SpeechSynthesisUtterance();
        msg.text = text;
        msg.lang = 'id-ID'; // Indonesian language code

        // Speak the text
        window.speechSynthesis.speak(msg);
    } else {
        alert("Text-to-Speech is not supported in this browser.");
    }
};

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
    document.getElementById('container-vidio').appendChild(canvas);

    console.log("canvas")


    const displaySize = { width: video.width, height: video.height };
    faceapi.matchDimensions(canvas, displaySize);

    let lastLabel = null;
    let lastLabelTime = null;
    let detectionInterval = null;
    let attendancePosted = false; // Flag to ensure postAttendance is executed only once
    async function startDetection() {
        attendancePosted = false;
        let listUdahAbsen = await getAbsen();
        let listUdahAbsenExit = await getAbsenExit();
        detectionInterval = setInterval(async () => {
            const detections = await faceapi
                .detectAllFaces(video)
                .withFaceLandmarks()
                .withFaceDescriptors();

            if (detections && detections.length > 0) {
                const resizedDetections = faceapi.resizeResults(detections, displaySize);

                canvas.getContext("2d").clearRect(0, 0, canvas.width, canvas.height);

                const results = resizedDetections.map((d) => {
                    return faceMatcher.findBestMatch(d.descriptor);
                });

                console.log("RESULT : ", results);

                results.forEach(async (result, i) => {
                    const box = resizedDetections[i].detection.box;
                    const drawBox = new faceapi.draw.DrawBox(box, {
                        label: result.toString(), // Convert to string for drawing
                    });
                    drawBox.draw(canvas);

                    const label = result.label;

                    // Check if the label is not "unknown"
                    if (label !== "unknown") {
                        const now = Date.now();

                        // Check if the label matches the last label and has remained the same for 1 second
                        const now2 = new Date();
                        const currentHour = now2.getHours();

                        console.log("JAM 2 : ", currentHour)
                        if(currentHour < 12){
                            if (label === lastLabel && !listUdahAbsen.some((absen) => absen.name === label)) {
                                console.log("masuk mas")
                                if (lastLabelTime && now - lastLabelTime >= 1000 && !attendancePosted) {
                                    console.log("masuk mas 2")
                                    console.log(`Stopping detection, label "${label}" remained the same for 1 second.`);
                                    clearInterval(detectionInterval); // Stop the detection
    
                                    const currentTime = getCurrentTime();
                                    showPopup(
                                        `Selamat datang, ${label}. Anda absen di jam ${currentTime}`
                                    );
                                    speakText(`Selamat datang, ${label}`)
                                    postAttendance(label); // Call only once
                                    attendancePosted = true; // Set the flag
    
                                    await new Promise((resolve) => setTimeout(resolve, 4000));
                                    // Reset and restart detection
                                    lastLabel = null;
                                    lastLabelTime = null;
                                    startDetection(); // Restart the loop
                                }
                            } else {
                                lastLabel = label; // Update the label
                                lastLabelTime = now; // Update the time
                            }
                        }else{
                            if (label === lastLabel && !listUdahAbsenExit.some((absen) => absen.name === label)) {
                                console.log("keluar mas")
                                if (lastLabelTime && now - lastLabelTime >= 1000 && !attendancePosted) {
                                    console.log("keluar mas 2")
                                    console.log(`Stopping detection, label "${label}" remained the same for 1 second.`);
                                    clearInterval(detectionInterval); // Stop the detection
    
                                    const currentTime = getCurrentTime();
                                    showPopup(
                                        `Hati Hati, ${label}. Anda absen keluar di jam ${currentTime}`
                                    );
                                    speakText(`Hati Hati, ${label}`)
                                    postAttendance(label); // Call only once
                                    attendancePosted = true; // Set the flag
    
                                    await new Promise((resolve) => setTimeout(resolve, 4000));
                                    // Reset and restart detection
                                    lastLabel = null;
                                    lastLabelTime = null;
                                    startDetection(); // Restart the loop
                                }
                            } else {
                                lastLabel = label; // Update the label
                                lastLabelTime = now; // Update the time
                            }
                        }

                       

                    } else {
                        // Reset the tracking if the label is "unknown"
                        lastLabel = null;
                        lastLabelTime = null;
                        attendancePosted = false; // Reset the flag
                    }
                });
            }
        }, 100);
    }
    startDetection()

});
