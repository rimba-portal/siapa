<?php

declare(strict_types=1);

namespace Rimba\Who\Components;

use Closure;
use Filament\Forms\Components\BaseFileUpload;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SnapPicture extends BaseFileUpload
{
    protected string $view = 'bites::components.snap-picture';

    protected string $disk = 'user_photo';

    protected ?string $directory = null;

    protected string $visibility = 'public';

    protected ?string $targetField = null;

    protected bool $shouldDeleteTemporaryFile = true;

    protected bool $showCameraSelector = true;

    protected int $imageQuality = 90;

    protected string $aspect = '16:9';

    protected bool $useModal = true;

    protected bool $shouldDeleteOnEdit = true;

    protected ?int $captureMaxWidth = null;

    protected ?int $captureMaxHeight = null;

    protected bool $autoStart = false;

    protected bool $multipleMode = false;

    protected array $shotSequence = [];

    protected bool $requireAllShots = true;

    public function disk(string $disk): static
    {
        $this->disk = $disk;

        return $this;
    }

    public function directory(?string $directory): static
    {
        $this->directory = $directory;

        return $this;
    }

    public function visibility(string $visibility): static
    {
        $this->visibility = $visibility;

        return $this;
    }

    public function targetField(string $fieldName): static
    {
        $this->targetField = $fieldName;

        return $this;
    }

    public function shouldDeleteTemporaryFile(bool $condition = true): static
    {
        $this->shouldDeleteTemporaryFile = $condition;

        return $this;
    }

    public function showCameraSelector(bool $show = true): static
    {
        $this->showCameraSelector = $show;

        return $this;
    }

    public function imageQuality(int $quality): static
    {
        $this->imageQuality = max(1, min(100, $quality));

        return $this;
    }

    public function aspect(string $ratio): static
    {
        $this->aspect = $ratio;

        return $this;
    }

    public function useModal(bool $useModal = true): static
    {
        $this->useModal = $useModal;

        return $this;
    }

    public function captureMaxWidth(?int $width): static
    {
        $this->captureMaxWidth = $width;

        return $this;
    }

    public function captureMaxHeight(?int $height): static
    {
        $this->captureMaxHeight = $height;

        return $this;
    }

    public function captureMaxDimensions(?int $width, ?int $height): static
    {
        $this->captureMaxWidth = $width;
        $this->captureMaxHeight = $height;

        return $this;
    }

    public function autoStart(bool $autoStart = true): static
    {
        $this->autoStart = $autoStart;

        return $this;
    }

    public function getDisk(): string
    {
        return $this->disk;
    }

    public function getDirectory(): ?string
    {
        return $this->directory;
    }

    public function getVisibility(): string
    {
        return $this->visibility;
    }

    public function getTargetField(): ?string
    {
        return $this->targetField ?? $this->getName();
    }

    public function getShouldDeleteTemporaryFile(): bool
    {
        return $this->shouldDeleteTemporaryFile;
    }

    public function getShowCameraSelector(): bool
    {
        return $this->showCameraSelector;
    }

    public function getImageQuality(): int
    {
        return $this->imageQuality;
    }

    public function getAspect(): string
    {
        return $this->aspect;
    }

    public function getUseModal(): bool
    {
        return $this->useModal;
    }

    public function getCaptureMaxWidth(): ?int
    {
        return $this->captureMaxWidth;
    }

    public function getCaptureMaxHeight(): ?int
    {
        return $this->captureMaxHeight;
    }

    public function getAutoStart(): bool
    {
        return $this->autoStart;
    }

    public function shouldDeleteOnEdit(bool $condition = true): static
    {
        $this->shouldDeleteOnEdit = $condition;

        return $this;
    }

    public function getShouldDeleteOnEdit(): bool
    {
        return $this->shouldDeleteOnEdit;
    }

    public function multiple(array $shots = []): static
    {
        $this->multipleMode = true;
        $this->shotSequence = $shots;

        return $this;
    }

    public function shots(array $sequence): static
    {
        $this->shotSequence = $sequence;
        $this->multipleMode = $sequence !== [];

        return $this;
    }

    public function requireAllShots(bool $required = true): static
    {
        $this->requireAllShots = $required;

        return $this;
    }

    public function getMultipleMode(): bool
    {
        return $this->multipleMode;
    }

    public function getShotSequence(): array
    {
        return $this->shotSequence;
    }

    public function getRequireAllShots(): bool
    {
        return $this->requireAllShots;
    }

    public function saveBase64Image(string $base64Data): ?string
    {
        if (empty($base64Data)) {
            return null;
        }

        $base64Data = preg_replace('#^data:image/\w+;base64,#i', '', $base64Data);
        $filename = Str::uuid().'.jpg';
        $path = $this->directory ? $this->directory.'/'.$filename : $filename;
        $imageData = base64_decode($base64Data);

        if (! $imageData) {
            return null;
        }

        Storage::disk($this->disk)->put($path, $imageData, $this->visibility);

        return $path;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->rule(function (self $component): Closure {
            return function (string $attribute, $value, Closure $fail) use ($component): void {

                if (! $component->getMultipleMode()) {
                    return;
                }

                if (! $component->getRequireAllShots()) {
                    return;
                }

                $sequence = $component->getShotSequence() ?? [];
                if (! is_array($value)) {
                    $fail('Please capture all required photos.');

                    return;
                }

                $missing = [];
                foreach ($sequence as $shot) {
                    $key = $shot['key'] ?? null;
                    if (! $key) {
                        continue;
                    }

                    $v = $value[$key] ?? null;
                    if ($v === null || $v === '') {
                        $missing[] = $shot['label'] ?? $key;
                    }
                }

                if ($missing !== []) {
                    $fail('Missing required photos: '.implode(', ', $missing).'.');
                }
            };
        });

        $this->afterStateHydrated(function ($get, $set, $state): void {
            // displaying existing record
        });
        $component = $this;
        $this->dehydrateStateUsing(function ($state, $get, $set) use ($component): mixed {
            try {
                // If there is no state data to process, return it directly
                if (empty($state)) {
                    return $state;
                }

                $multipleMode = $component->getMultipleMode();
                $disk = $component->getDisk();
                $directory = $component->getDirectory();
                $visibility = $component->getVisibility();

                // Define the localized execution helper to convert and save images
                $saveBase64 = function (string $base64Data) use ($component): ?string {
                    if (empty($base64Data)) {
                        return null;
                    }

                    // If it is not a base64 string (already a storage path), keep it
                    if (! str_starts_with($base64Data, 'data:image/')) {
                        return $base64Data;
                    }

                    // Process and store base64 string
                    return $component->saveBase64Image($base64Data);
                };

                // PATH 1: Handle Multi-Shot Sequence (Array state payload)
                if ($multipleMode) {
                    if (! is_array($state)) {
                        return [];
                    }

                    $processedState = [];
                    foreach ($state as $key => $base64Value) {
                        $processedState[$key] = $base64Value ? $saveBase64($base64Value) : null;
                    }

                    return $processedState;
                }

                // PATH 2: Handle Single Shot Mode (String state payload)
                if (is_string($state)) {
                    return $saveBase64($state);
                }

                // Fallback return if structure is unexpected
                return $state;

            } catch (\Exception $exception) {
                // Fallback to original state if storage fails
                return $state;
            }
        });

    }
}
