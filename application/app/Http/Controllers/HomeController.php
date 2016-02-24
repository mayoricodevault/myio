<?php namespace App\Http\Controllers;

use Carbon\Carbon;
use Auth, Lang, App;

class HomeController extends Controller {

	public function index()
	{
        return view('main')->with('user', Auth::user())
                           ->with('baseUrl', url())
                           ->with('translations', json_encode(Lang::get('app')))
                           ->with('settings', App::make('Settings'))
                           ->with('isDemo', IS_DEMO);
    }
}
