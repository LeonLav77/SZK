@props(['name', 'label', 'checked' => false, 'help' => null])

<div class="field">
    <label class="check">
        <input type="hidden" name="{{ $name }}" value="0">
        <input type="checkbox" name="{{ $name }}" value="1" @checked(old($name, $checked))>
        {{ $label }}
    </label>
    @if ($help)
        <small>{{ $help }}</small>
    @endif
</div>
