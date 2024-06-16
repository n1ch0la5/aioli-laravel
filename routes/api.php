<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Jobs\SendMessage;
use App\Models\Message;
// use Illuminate\Http\JsonResponse;

Route::post('/message', function(Request $request){
    $message = Message::create([
        'user_id' => auth()->id(),
        'text' => $request->get('message'),
    ]);
    SendMessage::dispatch($message);

    return response()->json([
        'success' => true,
        'message' => "Message created and job dispatched.",
    ]);
})->middleware('auth:sanctum');

Route::get('/message', function(){
    return Message::all();
})->middleware('auth:sanctum');

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/sanctum/token', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
        'device_name' => 'required',
    ]);
 
    $user = User::where('email', $request->email)->first();
 
    if (! $user || ! Hash::check($request->password, $user->password)) {
        throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
        ]);
    }
 
    return $user->createToken($request->device_name)->plainTextToken;
});

Route::post('/sanctum/logout', function(Request $request){
    $request->user()->tokens()->delete();
    // $request->session()->invalidate();
    // $request->session()->regenerateToken();

    return response()->json(['message' => 'Logged out']);
})->middleware('auth:sanctum');
