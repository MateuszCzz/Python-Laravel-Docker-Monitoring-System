<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\VideoStoreRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class VideoController extends Controller
{
    public function uploadVideo(VideoStoreRequest $request)
{
    // Check credentials
    $user = User::where('name', $request->username)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json(['error' => 'Invalid credentials'], 401);
    }

    // Store video
    $video = new Video();

    $videoFile = $request->file('video');
    $originalFilename = $videoFile->getClientOriginalName();
    $extension = $videoFile->getClientOriginalExtension();
    $randomFilename = Str::random(40) . '.' . $extension;

    $path = Storage::disk('videos')->putFileAs('', $videoFile, $randomFilename);

    $video->path = $path;
    $video->name = $originalFilename;
    $video->camera_id = $request->camera_id;
    $video->user_id = $user->id;
    $video->save();

    return response('Video uploaded successfully.', 200);
}

function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];

    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);

    $bytes /= pow(1024, $pow);

    if ($pow === 0 || $bytes >= 10) {
        return number_format($bytes, 0) . ' ' . $units[$pow];
    } else {
        return number_format($bytes, $precision) . ' ' . $units[$pow];
    }
}



public function listVideos()
{
    $user = auth()->user();
    $sort = request('sort') === 'asc' ? 'asc' : 'desc';
    $videos = Video::where('user_id', $user->id)
                    ->orderBy('name', $sort)
                    ->get();
    foreach ($videos as $video) {
        $video->size = Storage::disk('videos')->size($video->path); 
        $video->size = VideoController::formatBytes($video->size);
    }
    return view('profile.videos', compact('videos'));
}


    public function download(Video $video)
    {
        $path = storage_path('app/videos/' . $video->path);

        return response()->download($path, $video->name);
    }

    public function remove($id)
    {
        $video = Video::find($id);    
        if (Storage::disk('videos')->exists($video->path)) {
            Storage::disk('videos')->delete($video->path);
        }
        Video::destroy($id);
        return redirect()->route('videos.listVideos');
    }
    
    public function removeAll()
    {
        $user = auth()->user();
        $videos = Video::where('user_id', $user->id)->get();


        foreach ($videos as $video) {
            if (Storage::disk('videos')->exists($video->path)) {
                Storage::disk('videos')->delete($video->path);
            }
            Video::destroy($video->id);
        }
        return redirect()->route('videos.listVideos');

    }
}