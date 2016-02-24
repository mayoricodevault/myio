<?php namespace App\Http\Controllers;

use App, Input;

class SettingsController extends Controller {

    /**
     * Settings service instance.
     *
     * @var App\Services\Settings;
     */
    private $settings;

    public function __construct()
    {
        $this->middleware('loggedIn');
        $this->middleware('admin');

        $this->settings = App::make('Settings');
    }

    public function getAllSettings()
    {
        return $this->settings->getAll();
    }

    /**
     * Update Settings in the database with given ones.
     *
     * @return int
     */
    public function updateSettings()
    {
        foreach(Input::all() as $name => $value) {
            $this->settings->set($name, $value);
        }

        return response(trans('app.settingsUpdated'));
    }
}
