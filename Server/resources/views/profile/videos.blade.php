@php

    use App\Models\Video;
    $sort = request('sort') === 'asc' ? 'desc' : 'asc'; // Get the current sort order
    $sortIcon = request('sort') === 'asc' ? '&#9660;' : '&#9650;'; // Set the sort icon based on the sort order

@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Your Videos
        </h2>
    </x-slot>

    <style>
        .table {
            --table-bg: #FFFFFF;
            --table-text: #000000;
        }

        .table-dark {
            --table-bg: #212529;
            --table-text: #FFFFFF;
        }

        .table tr {
            background-color: var(--table-bg);
            color: var(--table-text);
        }

        .table td {
            padding: 0.75rem;
            vertical-align: top;
            border-top: 1px solid rgba(0, 0, 0, 0.06);
        }
    .btn-primary {
        background-color: #4CAF50;
        color: white;
        padding: 8px 16px;
        border: none;
        cursor: pointer;
        border-radius: 4px;
        font-size: 14px;
        text-decoration: none;

        display:inline-block;
    }

    .btn-danger {
        background-color: #FF5722;
        color: white;
        padding: 8px 16px;
        border: none;
        cursor: pointer;
        border-radius: 4px;
        font-size: 14px;
        text-decoration: none;
        display:inline-block;
        }
        .btn-primary:hover,
    .btn-danger:hover, .btn-third:hover{
        /* Add the following styles for hover state */
        filter: brightness(80%); /* Adjust the brightness as desired */
    }
    </style>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg">
            <div class=" p-4 sm:p-8" style="display: flex; justify-content: space-evenly;">
               
            <table class="table table-dark" style="border-collapse: collapse;">
    <thead>
        <tr>
        <th style="border: 1px solid #fff; padding: 10px;">
                    <a href="{{ route('videos.listVideos', ['sort' => $sort]) }}">
                        File Name {!! request('sort') === 'asc' ? '&#9660;' : '&#9650;' !!}
                    </a>
                </th>
            <th style="border: 1px solid #fff; padding: 10px;">Camera Name</th>
            <th style="border: 1px solid #fff; padding: 10px;">Size</th> 
            <th style="border: 1px solid #fff; padding: 10px;">Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($videos as $video)
            <tr>
                <td style="border: 1px solid #fff; padding: 10px;">{{ $video->name }}</td>
                <td style="border: 1px solid #fff; padding: 10px;">{{ $video->camera_id }}</td>
                <td style="border: 1px solid #fff; padding: 10px;">{{ $video->size }}</td> <!-- New size field -->
                <td style="border: 1px solid #fff; padding: 10px;">
                    <a href="{{ route('videos.download', $video->id) }}" class="btn btn-primary">Download</a>
                    <form action="{{ route('videos.remove', $video->id) }}" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Remove</button>
                    </form>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

                    <form action="{{ route('videos.removeAll') }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-third" style="background-color: #4CAF50; color: white; padding: 10px 20px; border: none; cursor: pointer; border-radius: 4px; font-size: 16px;">Remove All</button>

                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
