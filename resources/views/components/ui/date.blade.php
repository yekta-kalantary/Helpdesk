@props(['value' => null, 'datetime' => false])

{{-- User-facing dates use the existing Y/m/d convention; date-times add 24-hour time. --}}
@if($value)
    @php($formattedValue = $value instanceof \DateTimeInterface ? $value : \Illuminate\Support\Carbon::parse($value))
    {{ $datetime ? $formattedValue->timezone(config('app.timezone'))->format('Y/m/d H:i') : $formattedValue->format('Y/m/d') }}
@endif
