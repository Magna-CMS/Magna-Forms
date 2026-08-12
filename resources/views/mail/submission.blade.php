<p>A new submission arrived for <strong>{{ $formName }}</strong>.</p>

<table cellpadding="6" cellspacing="0" border="0">
    @foreach($values as $handle => $value)
        <tr>
            <td style="color:#64748b">{{ $handle }}</td>
            <td>{{ $value }}</td>
        </tr>
    @endforeach
</table>
