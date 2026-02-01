<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->query('q');
        $users = collect();
        if($q){
            $users = User::query()
                    ->where('id', '!=', Auth::id())
                    ->where(function ($query) use ($q){
                        $query->where('username', 'ilike', "%{$q}%");
                    })->get();
        }

        $sentPending = Auth::user()
                        ->sentRequests()
                        ->where('status', 'pending')
                        ->with('receiver')
                        ->latest()->get();

        $receivedPending = Auth::user()
                            ->receivedRequests()
                            ->where('status', 'pending')
                            ->with('sender')
                            ->latest()->get();

        return view('dashboard', compact('q', 'users', 'sentPending', 'receivedPending'));
                        
    }
}
