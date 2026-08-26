@php
    $isDisabled = $field->isDisabled();
    $isMultiple = $field->getMultipleMode();
    $shotSequence = $field->getShotSequence();
    $requireAllShots = $field->getRequireAllShots();
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div
        wire:key="{{ $getStatePath() }}-{{ $isDisabled ? 'disabled' : 'enabled' }}"
        x-data="{
        photoData: $wire.entangle('{{ $getStatePath() }}'),
        photoSelected: false,
        webcamActive: false,
        webcamError: null,
        cameraStream: null,
        availableCameras: [],
        selectedCameraId: null,
        modalOpen: false,
        showingPreview: false,
        aspectRatio: '{{ $getAspect() }}',
        imageQuality: {{ $getImageQuality() }},
        maxWidth: {{ $getCaptureMaxWidth() ?? 'null' }},
        maxHeight: {{ $getCaptureMaxHeight() ?? 'null' }},
        autoStart: {{ $getAutoStart() ? 'true' : 'false' }},
        mirroredView: false,
        isBackCamera: false,
        isDisabled: {{ $isDisabled ? 'true' : 'false' }},
        isMobile: /iPhone|iPad|iPod|Android/i.test(navigator.userAgent),
        currentFacingMode: 'environment',
        componentId: '{{ $getId() }}',
        initialized: false,
        isHovering: false,
    
        isMultiple: {{ $isMultiple ? 'true' : 'false' }},
        shotSequence: @js($shotSequence),
        requireAllShots: {{ $requireAllShots ? 'true' : 'false' }},
        currentShotIndex: 0,
        capturedShots: {},
        retakeOnlyKey: null,
        retakeSingle: false,
        retakeKey: null,
        modalSnapshot: null,

        init() {
            this.initMultipleMode();
            this.checkVisibilityAndAutoStart();
        },

        initMultipleMode() {
            if (! this.isMultiple) return;
    
            if (this.photoData && typeof this.photoData === 'object' && ! Array.isArray(this.photoData)) {
                this.capturedShots = { ...this.photoData };
            }
    
            this.ensureShotKeys();
            this.syncPhotoDataFromShots();
        },
    
        get currentShot() {
            return this.shotSequence?.[this.currentShotIndex] || null;
        },
    
        get completedShotsCount() {
            return Object.keys(this.capturedShots).filter(k => this.capturedShots[k]).length;
        },
    
        get allShotsCompleted() {
            if (! this.requireAllShots) return true;
            return (this.shotSequence || []).every(shot => this.capturedShots?.[shot.key]);
        },
    
        get hasAnyCapturedShot() {
            return Object.values(this.capturedShots || {}).some(v => !!v);
        },
    
        syncPhotoDataFromShots() {
            if (! this.isMultiple) return;
            if (! this.hasAnyCapturedShot) {
                this.photoData = null;
                return;
            }
            this.photoData = { ...this.capturedShots };
        },
    
        ensureShotKeys() {
            (this.shotSequence || []).forEach(shot => {
                if (shot?.key && typeof this.capturedShots[shot.key] === 'undefined') {
                    this.capturedShots[shot.key] = null;
                }
            });
        },
    
        getImageUrl(path) {
            if (! path) return null;
    
            if (typeof path !== 'string') return null;
    
            if (path.startsWith('data:image/')) return path;
            if (path.startsWith('http://') || path.startsWith('https://')) return path;
    
            const cleanPath = path.replace(/^\/+/, '');
            return '/storage/' + cleanPath;
        },
    
        async getCameras() {
            try {
                const devices = await navigator.mediaDevices.enumerateDevices();
                this.availableCameras = devices.filter(device => device.kind === 'videoinput');
    
                if (this.availableCameras.length > 0 && ! this.selectedCameraId) {
                    this.selectedCameraId = this.availableCameras[0].deviceId;
                    this.detectCameraType(this.availableCameras[0]);
                }
    
                return this.availableCameras;
            } catch (error) {
                console.error('Error getting camera devices:', error);
                this.webcamError = '{{ __('filament-take-picture-field::take-picture-field.error_unable_to_detect_cameras') }}';
                return [];
            }
        },
    
        detectCameraType(camera) {
            if (! camera) return;
    
            const label = (camera.label || '').toLowerCase();
    
            this.isBackCamera = label.includes('back') ||
                label.includes('rear') ||
                label.includes('environment') ||
                label.includes('0, facing back');
    
            this.mirroredView = ! this.isBackCamera;
        },
    
        async startCamera() {
            if (this.isDisabled) return;

            if (this.webcamActive) return;

            this.stopCamera();

            await new Promise(r => setTimeout(r, 120));

            this.initMultipleMode();

            this.webcamActive = true;
            this.webcamError = null;

            await this.$nextTick();

            let aspectWidth = 16;
            let aspectHeight = 9;

            if (this.aspectRatio) {
                const parts = this.aspectRatio.split(':');
                if (parts.length === 2) {
                    aspectWidth = parseInt(parts[0]);
                    aspectHeight = parseInt(parts[1]);
                }
            }

            const constraints = {
                video: {
                    facingMode: this.isMobile ? this.currentFacingMode : 'user',
                    width: { ideal: aspectWidth * 120 },
                    height: { ideal: aspectHeight * 120 },
                },
                audio: false
            };

            if (this.selectedCameraId) {
                constraints.video.deviceId = { exact: this.selectedCameraId };
            }

            try {
                const stream = await navigator.mediaDevices.getUserMedia(constraints);
                this.cameraStream = stream;

                await this.$nextTick();
                if (this.$refs.video) this.$refs.video.srcObject = stream;

                if ({{ $getShowCameraSelector() ? 'true' : 'false' }}) {
                    await this.getCameras();
                }

                const videoTrack = stream.getVideoTracks()[0];
                if (videoTrack) {
                    const settings = videoTrack.getSettings();
                    if (settings.facingMode) {
                        this.isBackCamera = settings.facingMode === 'environment';
                        this.mirroredView = ! this.isBackCamera;
                    }
                }

                if ({{ $getUseModal() ? 'true' : 'false' }} && ! this.modalOpen) {
                    this.openModal();
                }
            } catch (error) {
                console.error('Error starting camera:', error);
            }
        }
    }"
    >
