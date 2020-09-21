@extends('dataSync::layouts.default')

@section('content')
    <h1>Dashboard</h1>

    <div class="row">
        <div class="col-md-8">
            {{ $logs->appends($filter)->links() }}
        </div>
        <div class="col-md-4">
            <form method="get">
                <input type="checkbox" name="filter[success][]" value="successful" @if(in_array('successful', $filter['success'])) checked="checked" @endif /> Successful
                <input type="checkbox" name="filter[success][]" value="failed" @if(in_array('failed', $filter['success'])) checked="checked" @endif /> Failed
                <button class="btn btn-sm btn-outline-primary" type="submit">Filter</button>
            </form>
        </div>
    </div>

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
                        {{ $log->created_at->format('d.m.Y') }}<br />
                        {{ $log->created_at->format('H:i') }}
                    </td>
                    <td>
                        {{ $log->getModelIdentifier() }}<br />
                        <small>{{ $log->getModelClass() }}</small>
                    </td>
                    <td>
                        {{ $log->connection }}
                    </td>
                    <td class="text-right">
                        <a class="btn btn-outline-info" href="{{ route('dataSync.dashboard.details', ['dataSyncLog' => $log]) }}">Details</a>
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