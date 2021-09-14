<?php

namespace App\Http\Controllers;

use App\Helpers\TranslateHelper;
use Illuminate\Http\Request;

class HomeController extends Controller
{

    /**
     * Show the home page
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }
}
