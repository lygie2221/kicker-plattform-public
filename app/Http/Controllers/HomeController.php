<?php

namespace App\Http\Controllers;

use App\DataTables\Tables\Begegnungen;
use Illuminate\Http\Request;

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
    public function index(Begegnungen $begenungen)
    {
        return view(
            'home',
            [],
            [
                'DT' => $begenungen,
                'dtViewJSConfig' => $begenungen->dtViewJSConfig()
            ]
        );

    }

    public function redirect()
    {
        return redirect(route('home'));

    }
}
