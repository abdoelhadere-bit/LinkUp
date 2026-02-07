<?php

namespace App\Http\Controllers;

use App\Models\FriendRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FriendRequestController extends Controller
{
    // Page : rechercher + voir demandes reçues
    public function index(Request $request)
    {
        $q = $request->query('q');

        $users = collect();

        if($q){
            $users = User::query()
                ->where('id', '!=', Auth::id())
                ->where(function ($query) use ($q){
                    $query->where('name', 'ilike', "%{$q}%")
                          ->orWhere('email', 'ilike', "%{$q}%")  
                          ->orWhere('username', 'ilike', "%{$q}%");
                })
                ->limit(20)->get();
        }

        $receivedPending = Auth::user()
            ->receivedRequests()
            ->where('status', 'pending')
            ->with('sender') // nécessite relation sender() dans FriendRequest
            ->latest()
            ->get();

        $sentPending = Auth::user()
            ->sentRequests()
            ->where('status', 'pending')
            ->with('receiver') // nécessite relation receiver() dans FriendRequest
            ->latest()
            ->get();

        return view('friends.index', compact('q', 'users', 'receivedPending', 'sentPending'));
    }

    // Envoyer demande
    public function send(Request $request)
    {
        $data = $request->validate([
            'receiver_id' => ['required', 'exists:users,id'],
        ]);

        $senderId = Auth::id();
        $receiverId = (int) $data['receiver_id'];

        // 1) Interdire s'envoyer à soi-même
        if ($senderId === $receiverId) {
            return back()->withErrors(['receiver_id' => "Tu ne peux pas t'envoyer une demande à toi-même."]);
        }

        // 2) Interdire doublons (dans les 2 sens) si pending/accepted
        $alreadyExists = FriendRequest::query()
            ->whereIn('status', ['pending', 'accepted'])
            ->where(function ($q) use ($senderId, $receiverId) {
                $q->where(function ($x) use ($senderId, $receiverId) {
                    $x->where('sender_id', $senderId)->where('receiver_id', $receiverId);
                })->orWhere(function ($x) use ($senderId, $receiverId) {
                    $x->where('sender_id', $receiverId)->where('receiver_id', $senderId);
                });
            })
            ->exists();

        if ($alreadyExists) {
            return back()->withErrors(['receiver_id' => 'Une demande existe déjà (ou vous êtes déjà amis).']);
        }

        FriendRequest::create([
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Demande envoyée ');
    }

    // Accepter
    public function accept(FriendRequest $friendRequest)
    {
        if ($friendRequest->receiver_id !== Auth::id()) {
            abort(403);
        }

        if ($friendRequest->status !== 'pending') {
            return back()->withErrors(['status' => 'Cette demande n’est plus en attente.']);
        }

        $friendRequest->update(['status' => 'accepted']);

        return back()->with('success', 'Demande acceptée ');
    }

    // Refuser
    public function decline(FriendRequest $friendRequest)
    {
        if ($friendRequest->receiver_id !== Auth::id()) {
            abort(403);
        }

        if ($friendRequest->status !== 'pending') {
            return back()->withErrors(['status' => 'Cette demande n’est plus en attente.']);
        }

        $friendRequest->update(['status' => 'declined']); // ou $friendRequest->delete();

        return back()->with('success', 'Demande refusée ');
    }

}
