<table id="{{ $DT->ID }}" class="table row-border" style="width: 100%;">
    <thead>
    <tr>
        @foreach ($DT->Columns as $c)
            <th>
                <div class="table-header" @if($c->DisabledOrdering)data-orderable="false"@endif>
                    <span class="column-title">{{ $c->Name }}</span>
                </div>
            </th>
        @endforeach
    </tr>
    </thead>

    <tfoot>
    @foreach ($DT->Columns as $c)
        @if($loop->first)
            <td style="padding-left: 24px;"><b>Total:</b></td>
        @else
            <td style="font-weight: 700;"></td>
        @endif
    @endforeach
    </tfoot>
</table>
