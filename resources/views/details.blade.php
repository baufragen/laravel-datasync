@extends('dataSync::layouts.default')

@section('content')
    <div class="jumbotron">
        <h1 class="display-4">Log</h1>
        <p class="lead">{{ $log->created_at->format('d.m.Y H:i') }} <small>on</small> {{ $log->connection }}</p>
    </div>

    @if($log->isSuccessful())
        <div id="jsonData" data-json-data="{{ $log->payload }}">
        </div>
    @else
        <div class="row">
            <h2>{{ $log->getResponseValue('message') }}</h2>
            <span>in <strong>{{ $log->getResponseValue('file') }}</strong> Zeile <strong>{{ $log->getResponseValue('line') }}</strong></span>
        </div>

        <div id="jsonData" data-json-data='@json($log->getResponseValue('trace'))'>
        </div>
    @endif
@endsection