@extends('dataSync::layouts.default')

@section('content')
    <h1>Log <small>{{ $dataSyncLog->created_at->format('d.m.Y H:i') }}</small></h1>

@endsection