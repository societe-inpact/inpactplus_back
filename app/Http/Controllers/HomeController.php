<?php

namespace App\Http\Controllers;

use App\Models\Companies\CompanyFolder;
use App\Models\Misc\User;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $users = User::with('employee')->get();
        $folders = CompanyFolder::all();
        return view('home', compact('users', 'folders'));
    }
}
