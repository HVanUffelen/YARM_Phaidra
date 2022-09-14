<div class="col-12 col-xl-3 file-r4phaidra form-inline mt-2">
    <div class="custom-control custom-checkbox">
        @if ($file['license_id'] == 1)
            {!! Form::checkbox('r4phaidra[' . ($loop->iteration-1) . ']', true, (bool)$file['r4phaidra'], ['id'=>'r4p_' .$loop->iteration, 'class' => 'custom-control-input r4phaidra_cbx', 'disabled' => true]) !!}
        @else
            {!! Form::checkbox('r4phaidra[' . ($loop->iteration-1) . ']', true, (bool)$file['r4phaidra'], ['id'=>'r4p_' .$loop->iteration, 'class' => 'custom-control-input r4phaidra_cbx']) !!}
        @endif
        {!! Form::label('r4p_' .$loop->iteration, 'R4 Phaedra',['class' => 'custom-control-label font-weight-bold']); !!}
    </div>
</div>
