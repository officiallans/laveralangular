@extends('emails.base')

@section('header')
    <h1>{{$subject}}</h1>
@endsection

@section('content')
    {{$user['name']}} створив подію {{$type}}
    @if ($workflowAuthor->id !== $user->id)
        <br>
        для {{$workflowAuthor['name']}}
    @endif
    {{date('d.m.Y', strtotime( $workflow->start_at ))}}
    @if ($workflow->end_at !== null)
        - {{date('d.m.Y', strtotime( $workflow->end_at ))}}
    @endif
    {{$workflow->smartDuration}}
    <br>
    Баланс: {{$balance}}<br>
    {{$workflow['comment']}}
@endsection