<?php

namespace AmmyRQ\Snowballs;

use AmmyRQ\Snowballs\Main;

class FileManager 
{
	private const SKINS = ["fireball", "iceball", "thunderball", "toxicball", "enderball"];
    private const LANGS = ["spa", "eng", "it"];

	/**
	 * Starts verification and files creation
	 * @return void
	 */
	public static function init() : void 
	{
		$dir = Main::getInstance()->getDataFolder();

		if(!is_dir($dir))
		{
			@mkdir($dir);
			Main::getInstance()->getServer()->getLogger()->debug("[CustomSnowballs] Resources directory created successfully.");
		}

		if(!is_file($dir . "customSnowball.json"))
		{
			Main::getInstance()->saveResource("customSnowball.json");
			Main::getInstance()->getServer()->getLogger()->debug("[CustomSnowballs] Geometry model loaded successfully.");
		}

        if(!is_file($dir . "config.yml"))
        {
            Main::getInstance()->saveResource("config.yml");
            Main::getInstance()->getServer()->getLogger()->debug("[CustomSnowballs] Config loaded successfully.");
        }

		foreach(self::SKINS as $files)
		{
			if(!is_file($dir . "skins" . DIRECTORY_SEPARATOR . $files . ".png"))
			{
				Main::getInstance()->saveResource("skins" . DIRECTORY_SEPARATOR . $files . ".png");
				Main::getInstance()->getServer()->getLogger()->debug("[CustomSnowballs] File " . $files . ".png loaded successfully.");
			}
		}

        foreach(self::LANGS as $langs)
        {
            if(!is_file($dir . "lang" . DIRECTORY_SEPARATOR . $langs . ".yml"))
            {
                Main::getInstance()->saveResource("lang" . DIRECTORY_SEPARATOR . $langs . ".yml");
                Main::getInstance()->getServer()->getLogger()->debug("[CustomSnowballs] File " . $files . ".yml loaded successfully.");
            }
        }

		Main::getInstance()->getServer()->getLogger()->debug("[CustomSnowballs] All files have been verificated successfully.");
	}
}