<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div
        x-data="{
            stream: null,
            photoUrl: null,
            
            init() {
                // Initialize state from existing value if any
                this.photoUrl = @js($getState());
            },

            startCamera() {
                navigator.mediaDevices.getUserMedia({ video: true, audio: false })
                    .then(stream => {
                        this.stream = stream;
                        this.$refs.video.srcObject = stream;
                    })
                    .catch(err => {
                        console.error('Webcam not found or permission denied:', err);
                        alert('Unable to access webcam.');
                    });
            },

            takePhoto() {
                const video = this.$refs.video;
                const canvas = document.createElement('canvas');
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
                
                // Convert frame to base64
                this.photoUrl = canvas.toDataURL('image/jpeg');
                
                // Update Filament's wire:model / state
                $wire.set('{{ $getStatePath() }}', this.photoUrl);
                
                this.stopCamera();
            },

            stopCamera() {
                if (this.stream) {
                    this.stream.getTracks().forEach(track => track.stop());
                    this.stream = null;
                }
            }
        }"
        @class(['space-y-4'])
    >
        <!-- Camera Live View -->
        <div @class(['relative', 'flex', 'justify-center', 'overflow-hidden', 'rounded-lg', 'border', 'border-gray-300', 'bg-gray-50', 'p-2', 'dark:border-gray-600', 'dark:bg-gray-800'])>
            <video
                x-ref="video"
                autoplay
                playsinline
                @class(['w-full', 'max-w-md', 'rounded-lg'])
                x-show="stream"
            ></video>

            <!-- Snapshot Preview -->
            <img
                :src="photoUrl"
                x-show="photoUrl && ! stream"
                @class(['w-full', 'max-w-md', 'rounded-lg'])
                alt="Captured Photo"
            />

            <div x-show="! stream && ! photoUrl" @class(['py-16', 'text-center', 'text-gray-400', 'dark:text-gray-500'])>
                Webcam is turned off
            </div>
        </div>

        <!-- Controls -->
        <div @class(['flex', 'gap-2'])>
            <x-filament::button type="button" x-show="! stream" x-on:click="startCamera()">
                Start Camera
            </x-filament::button>

            <x-filament::button type="button" color="success" x-show="stream" x-on:click="takePhoto()">
                Capture Photo
            </x-filament::button>

            <x-filament::button
                type="button"
                color="danger"
                x-show="photoUrl || stream"
                x-on:click=" stopCamera(); photoUrl = null; $wire.set('{{ $getStatePath() }}', null); "
            >
                Clear
            </x-filament::button>
        </div>
    </div>
</x-dynamic-component>
