<?php namespace App\Services;

use Auth;
use Storage;
use Exception;
use App\Setting;

class Settings {

    /**
     * Array of all settings.
     *
     * @var array
     */
    private $all;

    /**
     * Settings with these keys need to be written into .env file
     * instead database settings table.
     *
     * @var array
     */
    private $envSettings = ['google_id', 'google_secret', 'facebook_id', 'facebook_secret', 'twitter_id', 'twitter_secret', 'mandrill_api_key', 'stripe_secret_key'];

    /**
     * Create a new settings service instance.
     *
     * @param Setting $settingModel
     */
	public function __construct(Setting $settingModel)
    {
        $this->model = $settingModel;

        try {
            $this->all = $this->model->lists('value', 'name');

            $fromEnvFile = [];

            foreach($this->envSettings as $key) {
                $fromEnvFile[$key] = env(strtoupper($key));
            }

            $this->all = array_merge($this->all, $fromEnvFile);

        } catch (Exception $e) {
            $this->all = [];
        }
    }

    /**
     * Get all available settings from database and .env file.
     *
     * @return array
     */
    public function getAll()
    {
        return $this->all;
    }

    /**
     * Get settings that are safe to to show in the source code
     * on the front end.
     *
     * @return array
     */
    public function getAllSafe()
    {
        if (Auth::user() && Auth::user()->isAdmin) {
            return $this->getAll();
        }

        $filtered = [];

        foreach ($this->all as $key => $value) {
            if ( ! in_array($key, $this->envSettings)) {
                $filtered[$key] = $value;
            }
        }

        return $filtered;
    }

    /**
     * Get a setting by key or return default.
     *
     * @param string $key
     * @param string|null $default
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if (isset($this->all[$key])) {
            return $this->all[$key];
        }

        return $default;
    }

    /**
     * Set
     * @param $key
     * @param $value
     *
     * @return Setting|void
     */
    public function set($key, $value)
    {
        If ($this->get('installed') && ! Auth::user()->isAdmin) {
            abort(403);
        }

        if (in_array($key, $this->envSettings)) {
            return $this->writeToEnvFile($key, $value);
        }

        $setting = Setting::where('name', $key)->first();

        if ( ! $setting) {
            $setting = new Setting(['name' => $key]);
        }

        $setting->value = $value;
        $setting->save();

        return $setting;
    }

    /**
     * Write given setting to .env file.
     *
     * @param $key
     * @param $value
     *
     * @return void
     */
    private function writeToEnvFile($key, $value)
    {
        if ( ! $value) $value = 'null';

        $key = strtoupper($key);

        $content = file_get_contents(base_path('.env'));

        $content = preg_replace("/(.*?$key=).*?(.+?)\\n/msi", '${1}'.$value."\n", $content);

        file_put_contents(base_path('.env'), $content);
    }
}
