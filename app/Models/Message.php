<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $table = 'messages';

    protected $fillable = [
        'room_chat_id',
        'sender_id',
        'receiver_id',
        'file',
        'message',
        'created_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function roomChat()
    {
        return $this->belongsTo(RoomChat::class);
    }
}
