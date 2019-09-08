@extends('dataSync::layouts.default')

@section('content')
    <div class="jumbotron">
        <h1 class="display-4">Log</h1>
        <p class="lead">{{ $log->created_at->format('d.m.Y H:i') }} <small>on</small> {{ $log->connection }}</p>
    </div>

    <div id="jsonData" data-json-data="{{ $log->payload }}">
    </div>
@endsection