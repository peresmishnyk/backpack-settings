<?php


namespace Peresmishnyk\BackpackSettings;


use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Peresmishnyk\BackpackSettings\Models\SettingsModel;
use function PHPUnit\Framework\isNull;

class Settings
{
    protected $config_key;
    protected $cache;
    protected $key;
    protected $settings = null;

    public function __construct($config_key)
    {
        $this->config_key = $config_key;
        $this->cache = Cache::store(Arr::get(config($config_key), 'cache.store') ?? config('cache.default'));
        $this->key = Arr::get(config($config_key), 'cache.key');
    }

    public function config($key = '')
    {
        return config($key == '' ? $this->config_key : $this->config_key . '.' . $key);
    }

    public function get($key, $default = null)
    {
        return Arr::get($this->get_settings(), $key, $default);
    }

    private function get_settings()
    {
        if (isNull($this->settings)) {
            if (!$this->cache->has($this->key)) {
                $data = $this->get_settings_from_db();
                $this->cache->put($this->key, $data);
                $this->settings = $data;
            } else {
                $this->settings = $this->cache->get($this->key);
            }
            config(['settings' => $this->settings]);
        }
        return $this->settings;
    }

    private function get_settings_from_db()
    {
        return SettingsModel::all()->keyBy('key')->map(
            function ($el) {
                $el->mergeCasts($el->extras_casts)->withFakes();
                $attr_names = collect(
                    array_diff(
                        array_keys($el->getAttributes()),
                        ['key', 'extras', 'extras_casts', 'updated_at', 'created_at']
                    )
                )->flip();
                return $attr_names->map(
                    function ($attr, $attr_name) use ($el) {
                        return $el->getAttribute($attr_name);
                    });
            });
    }

    public function refresh()
    {
        $this->settings = null;
        $this->cache->forget($this->key);
    }
}
