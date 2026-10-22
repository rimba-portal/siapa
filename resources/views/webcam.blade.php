<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div
        x-data="{
            stream: null,
            photoUrl: @js($getPhotoUrl()),

            startCamera() {
                navigator.mediaDevices
                    .getUserMedia({
                        video: true,
                        audio: false,
                    })
                    .then((stream) => {
                        this.stream = stream;
                        this.$refs.video.srcObject = stream;
                    })
                    .catch((error) => {
                        console.error(
                            'Unable to access webcam:',
                            error
                        );

                        alert('Unable to access webcam.');
                    });
            },

            takePhoto() {
                const video = this.$refs.video;
                const canvas = document.createElement('canvas');

                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;

                canvas
                    .getContext('2d')
                    .drawImage(
                        video,
                        0,
                        0,
                        canvas.width,
                        canvas.height
                    );

                const photo = canvas.toDataURL(
                    'image/jpeg',
                    0.9
                );

                this.photoUrl = photo;

                $wire.set(
                    '{{ $getStatePath() }}',
                    photo
                );

                this.stopCamera();
            },

            stopCamera() {
                if (! this.stream) {
                    return;
                }

                this.stream
                    .getTracks()
                    .forEach((track) => track.stop());

                this.stream = null;
            },

            clearPhoto() {
                this.stopCamera();

                this.photoUrl = null;

                $wire.set(
                    '{{ $getStatePath() }}',
                    null
                );
            },
        }"
        class="space-y-4"
    >
        <div class="relative flex justify-center overflow-hidden rounded-lg border border-gray-300 bg-gray-50 p-2 dark:border-gray-600 dark:bg-gray-800">
            <video x-ref="video" autoplay muted playsinline x-show="stream" class="w-full max-w-md rounded-lg"></video>

            <img
                x-show="photoUrl && ! stream"
                x-bind:src="photoUrl"
                class="w-full max-w-md rounded-lg object-cover"
                alt="Profile Photo"
            />

            <div x-show="! stream && ! photoUrl" class="py-16 text-center text-gray-400 dark:text-gray-500">
                No profile photo captured
            </div>
        </div>

        <div class="flex gap-2">
            <x-filament::button type="button" x-show="! stream" x-on:click="startCamera()">
                <span x-text="photoUrl ? 'Retake Photo' : 'Start Camera'"></span>
            </x-filament::button>

            <x-filament::button type="button" color="success" x-show="stream" x-on:click="takePhoto()">
                Capture Photo
            </x-filament::button>

            <x-filament::button type="button" color="danger" x-show="photoUrl || stream" x-on:click="clearPhoto()">
                Clear
            </x-filament::button>
        </div>
    </div>
</x-dynamic-component>
