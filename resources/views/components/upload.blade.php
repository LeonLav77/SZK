{{--
    Upload drop zone: click or drag a file in. Shows the current file, an instant preview of a
    newly picked image, checks the size before the form is sent, and can remove the current
    file (sent as remove_{name}=1).
--}}
@props([
    'name',
    'label',
    'current' => null,      // stored path on the public disk
    'kind' => 'image',      // image | pdf
    'maxMb' => 20,          // keep in sync with the max:20480 rules in the controllers
    'help' => null,
    'previewClass' => '',
    'required' => false,    // marks the label and hides "Ukloni"; the server enforces it
])

@php
    $currentUrl = $current ? Storage::disk('public')->url($current) : null;
    $isImage = $kind === 'image';
    $accept = $isImage ? 'image/jpeg,image/png,image/webp,image/gif' : 'application/pdf';
    $types = $isImage ? 'JPG, PNG ili WebP' : 'PDF';
@endphp

<div class="field upload" x-data="{
        currentUrl: @js($currentUrl),
        url: @js($currentUrl),
        picked: null,
        removed: false,
        over: false,
        error: '',
        choose() { this.$refs.input.click() },
        drop(e) {
            this.over = false;
            if (! e.dataTransfer.files.length) return;
            this.$refs.input.files = e.dataTransfer.files;
            this.pick();
        },
        pick() {
            const file = this.$refs.input.files[0];
            this.error = '';
            if (! file) return;
            if (file.size > {{ $maxMb }} * 1048576) {
                this.error = 'Datoteka je prevelika (' + (file.size / 1048576).toFixed(1) + ' MB). Najviše {{ $maxMb }} MB.';
                this.$refs.input.value = '';
                return;
            }
            this.picked = { name: file.name, size: (file.size / 1048576).toFixed(1) + ' MB' };
            this.removed = false;
            if (@js($isImage)) this.url = URL.createObjectURL(file);
        },
        clear() {
            this.$refs.input.value = '';
            this.error = '';
            if (this.picked) { this.picked = null; this.url = this.currentUrl; return; }
            this.removed = true;
        },
        get hasFile() { return !! this.picked || (!! this.currentUrl && ! this.removed) },
    }">
    <label for="{{ $name }}" @class(['req' => $required])>{{ $label }}</label>

    @unless ($required)
        <input type="hidden" name="remove_{{ $name }}" :value="removed ? 1 : 0">
    @endunless
    <input class="sr-only" id="{{ $name }}" name="{{ $name }}" type="file" accept="{{ $accept }}"
           x-ref="input" @change="pick()">

    <div class="dropzone" :class="{ over, filled: hasFile }" role="button" tabindex="0"
         @click="choose()" @keydown.enter.prevent="choose()" @keydown.space.prevent="choose()"
         @dragover.prevent="over = true" @dragleave.prevent="over = false" @drop.prevent="drop($event)">

        @if ($isImage)
            <img class="dropzone-thumb {{ $previewClass }}" :src="url" alt="" x-show="hasFile && url">
        @else
            <div class="dropzone-icon pdf" x-show="hasFile">PDF</div>
        @endif
        <div class="dropzone-icon" x-show="! hasFile" aria-hidden="true">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 16V4M6 10l6-6 6 6"/><path d="M4 16v3a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-3"/></svg>
        </div>

        <div class="dropzone-text">
            <template x-if="picked">
                <div>
                    <strong x-text="picked.name"></strong>
                    <span class="muted" x-text="picked.size + ' · sprema se kad klikneš Spremi'"></span>
                </div>
            </template>
            <template x-if="! picked && hasFile">
                <div>
                    <strong>{{ $isImage ? 'Trenutna slika' : 'Trenutni PDF' }}</strong>
                    <span class="muted">Klikni ili povuci novu datoteku da je zamijeniš</span>
                </div>
            </template>
            <template x-if="! hasFile">
                <div>
                    <strong>Klikni za odabir ili povuci datoteku ovdje</strong>
                    <span class="muted">{{ $types }} · najviše {{ $maxMb }} MB</span>
                </div>
            </template>
        </div>
    </div>

    <div class="upload-actions">
        @if ($currentUrl && ! $isImage)
            <a href="{{ $currentUrl }}" target="_blank" x-show="! picked && ! removed">Otvori trenutni PDF</a>
        @endif
        <button type="button" class="link danger" x-show="picked || (hasFile && ! @js($required))" @click="clear()"
                x-text="picked ? 'Poništi odabir' : 'Ukloni'"></button>
        <button type="button" class="link" x-show="removed && ! picked" @click="removed = false">Vrati</button>
        <span class="muted" x-show="removed && ! picked">Bit će uklonjeno kad klikneš Spremi.</span>
    </div>

    @if ($help)
        <small>{{ $help }}</small>
    @endif
    <div class="error" x-show="error" x-text="error"></div>
    @error($name)
        <div class="error">{{ $message }}</div>
    @enderror
</div>
