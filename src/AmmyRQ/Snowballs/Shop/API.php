<?php

/***************** ****************
* Maybe this mini API could be useless.
****************** ****************/


namespace AmmyRQ\Snowballs\Shop;

use pocketmine\item\{ItemFactory, ItemIds};
use pocketmine\player\Player;

use AmmyRQ\Snowballs\LanguageManager as LM;
use AmmyRQ\Snowballs\Shop\Shop;
use AmmyRQ\Snowballs\Main;
use onebone\economyapi\EconomyAPI;
use pocketmine\utils\Config;

class API
{

	/**
	 * Tries to buy snowballs by a player.
	 * @param Player $player
	 * @param string $type    Snowball type
	 * @param int amount = 1  Item amount
	 * @return void
	 */
	public static function tryToBuySnowballs(Player $player, string $type, int $amount = 1) : void
	{
        $file = new Config(Main::getInstance()->getDataFolder() . "config.yml");

		$name = Main::getLanguageManager()->getSnowballTranslation($type, "name");
		$effects = Main::getLanguageManager()->getSnowballTranslation($type, "effects");
		$cost = (int)Main::getLanguageManager()->getSnowballTranslation($type, "price") * $amount;

		$fromFactory = new ItemFactory();
		$item = $fromFactory->get(ItemIds::SNOWBALL, 0, $amount);

		if(!$player->getInventory()->canAddItem($item))
		{
			$player->sendMessage(Main::getLanguageManager()->getTranslation("notEnoughSpace-Inventory"));
			return;
		}

		if(EconomyAPI::getInstance()->myMoney($player) < $cost)
		{
			$player->sendMessage(
				str_replace("<cost>", $cost, Main::getLanguageManager()->getTranslation("notEnoughMoney"))
			);

			return;
		}

		$item->setCustomName($name);
		$item->setLore([$effects]);

		$player->getInventory()->addItem($item);
		EconomyAPI::getInstance()->reduceMoney($player, $cost);

		$player->sendMessage(
			str_replace(
				["<amount>", "<cost>", "<type>"],
				[$amount, $cost, $name],
                Main::getLanguageManager()->getTranslation("boughtSuccessfully")
			)
		);
	}

	/**
	 * Opens the shop UI to a player
	 * @param Player $player
	 * @return void
	 */
	public static function openShop(Player $player) : void { Shop::openUI($player); }

}
