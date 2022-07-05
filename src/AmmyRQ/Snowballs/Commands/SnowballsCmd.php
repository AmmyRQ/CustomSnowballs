<?php

namespace AmmyRQ\Snowballs\Commands;

use pocketmine\command\{Command, CommandSender};
use pocketmine\utils\Config;
use pocketmine\player\Player;

use AmmyRQ\Snowballs\Main;
use AmmyRQ\Snowballs\Shop\API;

class SnowballsCmd extends Command
{

	/** @var Main|null */
	private ?Main $plugin = null;

	public function __construct(Main $plugin)
	{
		$this->plugin = $plugin;
		parent::__construct("snowballs", "CustomSnowballs shop", "/snowballs", ["cs"]);
	}

	/**
	 * @param CommandSender $player
	 * @param string $commandLabel
	 * @param array $args
	 * @return void
	 */
	public function execute(CommandSender $player, string $commandLabel, array $args) : void
	{
		if(!$player instanceof Player)
            return;

        $file = new Config($this->plugin->getDataFolder() . "config.yml");

		if(!in_array($player->getWorld()->getDisplayName(), $file->get("allowedWorlds")))
		{
			Main::getLanguageManager()->getTranslation("worldNotAllowed");
			return;
		}

		API::openShop($player);
	}
}