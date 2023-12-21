<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RoomChat;
use Carbon\Carbon;

class RoomChatController extends Controller
{
    public function createRoom(Request $request)
    {
        $dt = new Carbon();
        $dt->TimeZone('Asia/Jakarta');
        $time_now = $dt->toDateTimeString();
        // get day month year from time stamp
        $day_now = date('d', strtotime($time_now));
        $month_now = date('m', strtotime($time_now));
        $year_now = date('Y', strtotime($time_now));

        try {
            $request->validate([
                'title' => 'required|string',
            ]);

            $room = RoomChat::create([
                'title' => $request->title,
                'user_id' => auth()->user()->id,
                'created_at' => $time_now,
            ]);

            return response()->json([
                'error' => false, // 'error' => 'false
                'status' => 'success',
                'message' => 'Room created successfully',
                'data' => $room,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true, // 'error' => 'true
                'status' => 'error',
                'message' => 'Failed to create room',
                'data' => $e->getMessage(),
            ], 400);
        }
    }

    public function rooms(Request $request)
    {
        try {
            $rooms = RoomChat::where('user_id', auth()->user()->id)->get();

            return response()->json([
                'error' => false, // 'error' => 'false
                'status' => 'success',
                'message' => 'All Rooms fetched successfully',
                'data' => $rooms,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true, // 'error' => 'true
                'status' => 'error',
                'message' => 'Failed to fetch rooms',
                'data' => $e->getMessage(),
            ], 400);
        }
    }

    public function room(Request $request, $id)
    {
        try {
            $room = RoomChat::where('id', $id)->with('messages')->first();

            return response()->json([
                'error' => false, // 'error' => 'false
                'status' => 'success',
                'message' => 'Room fetched successfully',
                'data' => $room,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true, // 'error' => 'true
                'status' => 'error',
                'message' => 'Failed to fetch room',
                'data' => $e->getMessage(),
            ], 400);
        }
    }

    public function updateRoom(Request $request, $id)
    {
        try {
            $request->validate([
                'title' => 'required|string',
            ]);

            $room = RoomChat::where('id', $id)->update([
                'title' => $request->title,
            ]);

            return response()->json([
                'error' => false, // 'error' => 'false
                'status' => 'success',
                'message' => 'Title Room updated successfully',
                'data' => $room,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true, // 'error' => 'true
                'status' => 'error',
                'message' => 'Failed to update room',
                'data' => $e->getMessage(),
            ], 400);
        }
    }

    public function deleteRoom(Request $request, $id)
    {
        try {
            $room = RoomChat::where('id', $id)->delete();

            return response()->json([
                'error' => false, // 'error' => 'false
                'status' => 'success',
                'message' => 'Room deleted successfully',
                'data' => $room,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true, // 'error' => 'true
                'status' => 'error',
                'message' => 'Failed to delete room',
                'data' => $e->getMessage(),
            ], 400);
        }
    }
}
