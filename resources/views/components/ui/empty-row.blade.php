@props(['colspan' => 1])

<tr>
    <td colspan="{{ $colspan }}" class="px-4 py-10 text-center text-body-sm text-text-muted">
        {{ $slot->isEmpty() ? __('app.no_records') : $slot }}
    </td>
</tr>
