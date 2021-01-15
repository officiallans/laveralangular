@extends('emails.base')

@section('header')
    <h1>{{$subject}}</h1>
@endsection

@section('content')
    {{$user['name']}} оновив подію {{$type}}
    {{date('d.m.Y', strtotime( $workflow->getOriginal('start_at') ))}}

    @if ($workflow->getOriginal('end_at') !== null)
        - {{date('d.m.Y', strtotime( $workflow->getOriginal('end_at') ))}}
    @endif
    @if (in_array($workflow->type, ['working_off', 'time_off']))
        {{$workflow->getOriginal('duration')/60}} год.
    @endif

    @if ($workflowAuthor->id !== $user->id)
        <br>
        для {{$workflowAuthor['name']}}
    @endif
    : <br>
    на {{date('d.m.Y', strtotime( $workflow->start_at ))}}
    @if ($workflow->end_at !== null)
        - {{date('d.m.Y', strtotime( $workflow->end_at ))}}
    @endif
    {{$workflow->smartDuration}}
    <br>
    Баланс: {{$balance}}<br>
    {{$workflow['comment']}}
@endsection