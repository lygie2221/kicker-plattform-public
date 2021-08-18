@extends('layouts.app')
@section('title', 'Begegnung erstellen')
@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card" style="padding:20px">
                    {!! Form::open(['url' => route('Begegnungen.new'), 'id' =>
                        'begegungenForm', 'class' => 'form-horizontal cp-cond-form'])
                        !!}

                    <div class="form-group {{ $errors->has('Modus') ? 'has-error' : ''}}" >
                            {!! Form::label('modus', 'Modus', ['class' => 'control-label']) !!}
                            {!! Form::select('modus', \App\Enums\ModusDerBegegnung::toArray()
                        ) !!}
                            {!! $errors->first('title', '<p class="help-block">:message</p>') !!}
                    </div>

                    <div class="form-group {{ $errors->has('standort') ? 'has-error' : ''}}" >
                        {!! Form::label('standort', 'Standort', ['class' => 'control-label']) !!}
                        {!! Form::select('standort', \App\Standort::getAllForSelect()
                    ) !!}
                        {!! $errors->first('title', '<p class="help-block">:message</p>') !!}
                    </div>

                    <div class="form-group {{ $errors->has('standort') ? 'has-error' : ''}}" >
                        <div style="width:48%; float:left" >

                            {!! Form::label('team1_spieler1', 'Team1 Spieler1', ['class' => 'control-label']) !!}
                            {!! Form::select('team1_spieler1', \App\User::getAllForSelect()
                        ) !!}
                            {!! $errors->first('title', '<p class="help-block">:message</p>') !!}
                        </div>
                        <div style="width:48%; float:left" >
                            {!! Form::label('team1_spieler2', 'Team1 Spieler2', ['class' => 'control-label']) !!}
                            {!! Form::select('team1_spieler1', \App\User::getAllForSelect()
                        ) !!}
                            {!! $errors->first('title', '<p class="help-block">:message</p>') !!}
                        </div>
                    </div>

                    <div class="form-group {{ $errors->has('standort') ? 'has-error' : ''}}" >
                        <div style="width:48%; float:left"
                            {!! Form::label('team2_spieler1', 'Team2 Spieler1', ['class' => 'control-label']) !!}
                            {!! Form::select('team2_spieler1', \App\User::getAllForSelect()
                        ) !!}
                            {!! $errors->first('title', '<p class="help-block">:message</p>') !!}
                        </div>
                        <div style="width:48%; float:left" >
                            {!! Form::label('team2_spieler2', 'Team2 Spieler2', ['class' => 'control-label']) !!}
                            {!! Form::select('team2_spieler2', \App\User::getAllForSelect()
                        ) !!}
                            {!! $errors->first('title', '<p class="help-block">:message</p>') !!}
                        </div>

                </div>




                    <div class="form-group {{ $errors->has('Modus') ? 'has-error' : ''}}" >
                    {!! Form::submit("Speichern") !!}
                    </div>


                    {!! Form::close() !!}
            </div>
    </div>
    </div>
    </div>

@endsection

