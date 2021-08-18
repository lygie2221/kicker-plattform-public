<?php

namespace App\Http\Controllers;

use App\Begegnung;
use App\CryptoPanelFakeTransactions;
use App\CryptoPanelTransactions;
use App\CryptoPanelUser;
use App\Enums\Coins;
use App\Enums\Endpoints;
use App\Enums\NotificationType;
use App\Enums\Status;
use App\Helper\StaticHelper;
use App\MarketingFunnel;
use App\MarketingFunnelTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Mail;

class BegegnungenController extends Controller
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
    public function index(Request $request, $id = null)
    {


    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create() {
        return view('begegnungen.create',
            [
            ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(Request $request) {

        $this->validate($request, [
            'modus' => 'required|max:255',
            'standort' => 'required|int',

        ]);

        $requestData = $request->all();

        $begegnung = new Begegnung();

        $begegnung->modus=$requestData["modus"];
        $begegnung->standort_id=$requestData["standort"];

        $begegnung->save();

        return redirect(route('home'));
    }



    /**
     * Display the specified resource.
     *
     * @param  int  $id
     *
     * @return \Illuminate\View\View
     */
    public function show($id) {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     *
     * @return \Illuminate\View\View
     */
    public function edit($id,$userid=false,$testmail=null)
    {
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param  int  $id
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(Request $request, $id)
    {
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function destroy($id)
    {
        Begegnung::destroy($id);

        return redirect(route('home'));
    }
}
