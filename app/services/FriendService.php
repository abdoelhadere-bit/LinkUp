<?php
namespace App\services;
use App\Models\FriendRequest;   

class FriendService {

    public function status(int $userId, int $me): string
    {
        $fr = FriendRequest::query()
                ->where(function($q) use ($me, $userId){
                    $q->where('receiver_id', $me)->where('sender_id', $userId);
                })
                ->orWhere(function($q) use ($me, $userId){
                    $q->where('receiver_id', $userId)->where('sender_id', $me);
                })
                ->first();
        
        if($userId === $me) return 'me';
        if(!$fr) return 'none';
        if($fr->status === 'accepted') return 'friends';
        if($fr->status === 'pending') return $fr->sender_id === $me ? 'outgoing_pending' : 'incoming_pending';
        return 'none';
        
    }

    public function send(int $userId, int $me)
    {
        if($userId === $me) return 'me';

        $isExist = FriendRequest::query()
                    ->where(function ($q) use ($me, $userId){
                        $q->where('receiver_id', $me)->where('sender_id', $userId);
                    })
                    ->orWhere(function ($q) use ($me, $userId){
                        $q->where('receiver_id', $userId)->where('sender_id', $me);
                    })
                    ->whereIn('status', ['pending', 'accepted'])
                    ->exists();

        if($isExist) return;

        FriendRequest::create([
            'sender_id'=> $me,
            'receiver_id'=>$userId,
            'status'=>'pending'
        ]);
    }

    public function accept(int $userId, int $me): void
    {
        FriendRequest::query()
                ->where('receiver_id', $me)
                ->where('sender_id', $userId)
                ->where('status', 'pending')
                ->firstOrFail()
                ->update(['status' => 'accepted']);
        
    }

    public function decline(int $userId, int $me): void
    {
        FriendRequest::query()
                ->where('receiver_id', $me)
                ->where('sender_id', $userId)
                ->where('status', 'pending')
                ->firstOrFail()
                ->update(['status' => 'declined']);

    }
}