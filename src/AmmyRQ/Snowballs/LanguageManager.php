<?php

namespace AmmyRQ\Snowballs;

use pocketmine\utils\Config;

class LanguageManager
{
    /**
     * Returns the current language
     * @return string
     */
    private static function getLang() : string
    {
        $file = new Config(Main::getInstance()->getDataFolder() . "config.yml", Config::YAML);

        if($file->exists("currentLanguage"))
            return $file->get("currentLanguage");
        else
            return "eng";
    }
    /**
     * Obtains a translation of a specific ball
     * @param string $type  CustomSnowball type
     * @param string $option
     * @param bool $img
     * @return string
     */
    public function getSnowballTranslation(string $type, string $option, bool $img = false) : string
    {
        $file = new Config(Main::getInstance()->getDataFolder() . "lang" . DIRECTORY_SEPARATOR . self::getLang() . ".yml", Config::YAML);
        $data = $file->getAll();

        if($img)
            return $data[$type]["img"][$option];
        else
            return $data[$type][$option];
	}

    /**
     * Obtains a translation of a general message
     * @param string $key
     * @return string
     */
    public function getTranslation(string $key) : string
    {
        $file = new Config(Main::getInstance()->getDataFolder() . "lang" . DIRECTORY_SEPARATOR . self::getLang() . ".yml", Config::YAML);

        return $file->get($key);
    }

    /**
     * Obtains a translation from UI messages
     * @param string $key
     * @param string $type
     * @return string
     */
    public function getUITranslation(string $key, ?string $type = null)
    {
        $file = new Config(Main::getInstance()->getDataFolder() . "lang" . DIRECTORY_SEPARATOR . self::getLang() . ".yml", Config::YAML);

        $data = $file->getAll();

        if(!is_null($type))
            return $data[$key][$type];
        else
            return $data[$key];
    }

}
