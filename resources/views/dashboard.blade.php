@extends('dataSync::layouts.default')

@section('content')
    <h1>Dashboard</h1>

    {{ $logs->links() }}

    <table class="table table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th class="text-center">Datum</th>
                <th>Model</th>
                <th>Connection</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
                <tr>
                    <td>
                        @if ($log->isSuccessful())
                            <i class="fa fa-smile text-success"></i>
                        @else
                            <i class="fa fa-angry text-danger"></i>
                        @endif
                        {{ $log->id }}
                    </td>
                    <td class="text-center">
                        {!! $log->created_at->format('d.m.Y<br/>H:i') !!}
                    </td>
                    <td>
                        {{ $log->getModelIdentifier() }}<br />
                        <small>{{ $log->getModelClass() }}</small>
                    </td>
                    <td>
                        {{ $log->connection }}
                    </td>
                    <td class="text-right">
                        <a class="btn btn-outline-info" href="">Details</a>
                    </td>
                </tr>
                @empty
                    <tr>
                        <td colspan="4">Keine aktuellen Logs vorhanden</td>
                    </tr>
                @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th>#</th>
                <th class="text-center">Datum</th>
                <th>Model</th>
                <th>Connection</th>
                <th></th>
            </tr>
        </tfoot>
    </table>

    {{ $logs->links() }}
@endsection