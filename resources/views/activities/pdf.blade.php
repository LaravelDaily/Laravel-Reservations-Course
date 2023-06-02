<h1>{{ $data->name }}</h1>

<h4>Start time {{ $data->start_time }}</h4>

<table>
    <thead>
        <tr>
            <th></th>
            <th>Name</th>
            <th>Email</th>
            <th>Registration Time</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data->participants as $participant)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $participant->name }}</td>
                <td>{{ $participant->email }}</td>
                <td>{{ $participant->pivot->created_at }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
