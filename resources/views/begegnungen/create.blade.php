@extends('layouts.app')
@section('title', 'Funnel erstellen')
@section('content')
    {!! Form::open(['url' => route('Begegnungen.new'), 'id' =>
        'begegungenForm', 'class' => 'form-horizontal cp-cond-form', 'files' => true])
        !!}

    <div class="form-group {{ $errors->has('Modus') ? 'has-error' : ''}}" style="float:left"; width:48%">
            {!! Form::label('modus', 'modus', ['class' => 'control-label']) !!}
            {!! Form::select('modus', \App\Enums\ModusDerBegegnung::toArray()
        ) !!}
            {!! $errors->first('title', '<p class="help-block">:message</p>') !!}
    </div>

    <div class="form-group {{ $errors->has('Modus') ? 'has-error' : ''}}" style="float:left"; width:48%">
        {!! Form::label('standort', 'modus', ['class' => 'control-label']) !!}
        {!! Form::select('standort', \App\Standort::getAllForSelect()
    ) !!}
        {!! $errors->first('title', '<p class="help-block">:message</p>') !!}
    </div>

    <div class="form-group {{ $errors->has('Modus') ? 'has-error' : ''}}" style="float:left"; width:48%">
    {!! Form::submit("Speichern") !!}
    </div>


    {!! Form::close() !!}

@endsection

