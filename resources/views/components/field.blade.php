@props(['name', 'label', 'type' => 'text', 'value' => null, 'help' => null])

<div class="field">
    <label for="{{ $name }}">{{ $label }}</label>
    @if ($type === 'textarea')
        <textarea id="{{ $name }}" name="{{ $name }}" {{ $attributes->merge(['rows' => 8]) }}>{{ old($name, $value) }}</textarea>
    @elseif ($type === 'file')
        <input id="{{ $name }}" name="{{ $name }}" type="file" {{ $attributes }}>
    @else
        <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ old($name, $value) }}" {{ $attributes }}>
    @endif
    @if ($help)
        <small>{{ $help }}</small>
    @endif
    @error($name)
        <div class="error">{{ $message }}</div>
    @enderror
</div>
