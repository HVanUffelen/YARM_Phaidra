@if ($data['books'] != '' or $data['tei'] != '' or $data['otherObjects'] != '')
    <li>@lang('Long term repository (Vienna University Library)')</li>
    <ul class="result2">
        @endif
        @if ($data['books'] != '')
            <li>@lang('Books')</li>
            <ul class="result3">
                {!!$data['books']!!}
            </ul>
        @endif
        @if ($data['tei'] != '')
            <li>@lang('Documents in/from TEI')</li>
            <ul class="result3">
                {!! $data['tei']!!}
            </ul>
        @endif
        @if ($data['otherObjects'] != '')
            <li>@lang('Other digital objects')</li>
            <ul class="result3">
                {!!$data['otherObjects']!!}
            </ul>
        @endif
        @if ($data['books'] != '' or $data['tei'] != '' or $data['otherObjects'] != '')
    </ul>
@endif
