<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\User;
use Carbon\Carbon;
//storage
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class MessageController extends Controller
{
    public function messages(Request $request)
    {
        $url = env('GOOGLE_CLOUD_STORAGE_BUCKET_URI');
        $api_key_openai = env('OPENAI_API_KEY');
        $dt = new Carbon();
        $dt->TimeZone('Asia/Jakarta');
        $time_now = $dt->toDateTimeString();
        // get day month year from time stamp
        $day_now = date('d', strtotime($time_now));
        $month_now = date('m', strtotime($time_now));
        $year_now = date('Y', strtotime($time_now));

        try {
            $request->validate([
                'room_chat_id' => 'required|integer',
                'receiver_id' => 'required|integer',
                'message' => 'nullable|string',
                'file' => 'nullable|file|max:1024',
            ]);

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $fileName = $file->getClientOriginalName();
                $filePath = $file->storeAs('audio', $fileName);
                $fileUrl = $url . '/' . $filePath;

                $response = Http::post('https://machine-learning-anna-app-api-nutvn2wcpq-ew.a.run.app', [
                    'filename' => $fileName,
                ]);

                if ($response->ok()) {
                    $json = $response->json();
                    $message_from_bot = $json['transkrip'];
                    $response = Http::withToken($api_key_openai, 'Bearer')->post('https://api.openai.com/v1/chat/completions', [
                        'model' => 'gpt-3.5-turbo',
                        'messages' => [
                            [
                                'role' => 'user',
                                'content' => $message_from_bot,
                            ],
                        ],
                        'temperature' => 0.7,
                    ]);

                    if ($response->ok()) {
                        $json = $response->json();
                        $message_from_bot = $json['choices'][0]['message']['content'];
                    } else {
                        $message_from_bot = 'Hi, I am a ANNA BOT. I will reply you soon. (audio no support yet)';
                    }
                } else {
                    $message_from_bot = 'this audio is not support yet';
                }
            } else if ($request->has('message')) {
                $response = Http::withToken($api_key_openai, 'Bearer')->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-3.5-turbo',
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $request->message,
                        ],
                    ],
                    'temperature' => 0.7,
                ]);

                if ($response->ok()) {
                    $json = $response->json();
                    $message_from_bot = $json['choices'][0]['message']['content'];
                } else {
                    $message_from_bot = 'Hi, I am a ANNA BOT. I will reply you soon. (audio no support yet)';
                }
            } else {

                $message_from_bot = 'Hi, I am a ANNA BOT. I will reply you soon. (audio no support yet)';
            }


            $message = Message::create([
                'room_chat_id' => $request->room_chat_id,
                'sender_id' => auth()->user()->id, // auth()->user()->id
                'receiver_id' => $request->receiver_id,
                'message' => $request->message,
                'file' => $filePath ?? null,
                'created_at' => $time_now,
            ]);


            $messagefrombot = Message::create([
                'room_chat_id' => $request->room_chat_id,
                'sender_id' => $request->receiver_id,
                'receiver_id' => auth()->user()->id, // auth()->user()->id
                'message' => $message_from_bot,
                'file' => null,
                'created_at' => $time_now,
            ]);

            $data_from_bot = Message::where('receiver_id',  auth()->user()->id)->where('room_chat_id', $request->room_chat_id)->latest()->first();

            return response()->json([
                'error' => false,
                'message' => 'Message sent successfully',
                'data' => $data_from_bot,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Failed to send message',
                'error_alert' => $e->getMessage(),
            ], 500);
        }
    }
}
