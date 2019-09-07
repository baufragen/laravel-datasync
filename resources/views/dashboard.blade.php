@extends('dataSync::layouts.default')

@section('content')
    <h1>Dashboard</h1>

    {{ $logs->links() }}

    <table class="table table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Connection</th>
                <th>Model</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
                <tr>
                    <td>
                        @if ($log->isSuccessful())
                            <span class="badge badge-pill badge-success">
                                    x
                                </span>
                        @else
                            <span class="badge badge-pill badge-danger">
                                    x
                                </span>
                        @endif
                        {{ $log->id }}
                    </td>
                    <td>
                        {{ $log->connection }}
                    </td>
                    <td>
                        {{ $log->model }} [{{ $log->identifier }}]
                    </td>
                    <td>
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
                <th>Connection</th>
                <th>Model</th>
                <th></th>
            </tr>
        </tfoot>
    </table>

    {{ $logs->links() }}
@endsection