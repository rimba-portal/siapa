<x-filament-panels::page.simple>
    <h2 class="text-center text-2xl font-bold mb-6">Secure Login</h2>
    {{ $this->form }}

    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
    <script>
        let modelsLoaded = false;
        const THRESHOLD = {{ config('identity.face_match_threshold', 0.6) }};

        async function loadModels() {
            await faceapi.nets.ssdMobilenetv1.loadFromUri('/models');
            await faceapi.nets.faceLandmark68Net.loadFromUri('/models');
            await faceapi.nets.faceRecognitionNet.loadFromUri('/models');
            modelsLoaded = true;
        }

        window.addEventListener('start-face-scan', async () => {
            if (!modelsLoaded) await loadModels();
            const stored = JSON.parse(document.querySelector('input[name="stored_face"]').value);
            const matcher = new faceapi.FaceMatcher(new faceapi.LabeledFaceDescriptors('user', [new Float32Array(stored)]), THRESHOLD);

            const step = document.querySelector('[data-step-id="face-verification"]');
            step.innerHTML += `<div class="mt-6 text-center"><video id="cam" autoplay muted playsinline width="320" height="240" class="mx-auto border rounded"></video><p id="status" class="mt-4"></p></div>`;

            const cam = document.getElementById('cam');
            const status = document.getElementById('status');
            const faceOk = document.querySelector('input[name="face_ok"]');
            const form = document.querySelector('form');
            cam.srcObject = await navigator.mediaDevices.getUserMedia({video: true});

            const check = setInterval(async () => {
                const d = await faceapi.detectSingleFace(cam).withFaceLandmarks().withFaceDescriptor();
                if (!d) return status.textContent = '❌ No face detected';
                const match = matcher.findBestMatch(d.descriptor);
                status.textContent = `🔍 Match: ${(1 - match.distance).toFixed(2)}`;
                if (match.distance <= THRESHOLD) {
                    status.textContent = '✅ Matched! Logging in...';
                    faceOk.value = '1';
                    clearInterval(check);
                    setTimeout(() => form.submit(), 800);
                }
            }, 1200);
        });
    </script>
</x-filament-panels::page.simple>