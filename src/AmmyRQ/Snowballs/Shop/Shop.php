<?php

namespace AmmyRQ\Snowballs\Shop;

use pocketmine\player\Player;
use pocketmine\utils\Config;

use jojoe77777\FormAPI\{SimpleForm, CustomForm, ModalForm};
use AmmyRQ\Snowballs\Main;
use AmmyRQ\Snowballs\Shop\API;

class Shop
{

	/**
	 * Opens the shop
	 * @param Player $player
	 * @return void
	 */
	public static function openUI(Player $player) : void
	{
        $file = new Config(Main::getInstance()->getDataFolder() . "config.yml");

		$form = new SimpleForm(
			function( Player $player, ?int $data = null)
			{
				if(isset($data))
				{
					if($data === 0)
                        return;

					self::openAmountSelector($player, $data - 1);
				}
			}
		);

		$form->setTitle(Main::getLanguageManager()->getUITranslation("menu", "title"));
		$form->setContent(Main::getLanguageManager()->getUITranslation("menu", "content"));
		$form->addButton(Main::getLanguageManager()->getUITranslation("exit"));

		for ($i=0; $i < count($file->get("availableCustomSnowballs")); $i++)
			$form->addButton(
                Main::getLanguageManager()->getSnowballTranslation($file->get("availableCustomSnowballs")[$i], "name"),
                Main::getLanguageManager()->getSnowballTranslation($file->get("availableCustomSnowballs")[$i], "type", true),
                Main::getLanguageManager()->getSnowballTranslation($file->get("availableCustomSnowballs")[$i], "url", true),
            );

		$player->sendForm($form);
	}

	/**
	 * Checks the snowball's ID and continues to the purchase
	 * @param Player $player
	 * @param int $id
	 * @return void
	 */
	public static function openAmountSelector(Player $player, int $id) : void
	{
        $file = new Config(Main::getInstance()->getDataFolder() . "config.yml");
        $data = $file->get("availableCustomSnowballs")[$id];

        $name = Main::getLanguageManager()->getSnowballTranslation($data, "name");
        $description = Main::getLanguageManager()->getSnowballTranslation($data, "description");
        $effects = Main::getLanguageManager()->getSnowballTranslation($data, "effects");
        $price = Main::getLanguageManager()->getSnowballTranslation($data, "price");


		$form = new CustomForm(
			function( Player $player, ?array $data = null) use($name, $effects, $price, $id, $description)
			{
				if(isset($data[0]))
				{
                    if(!is_numeric($data[0]))
                        $data[0] = 0;

					if($data[0] < 1)
					{
						$player->sendMessage(Main::getLanguageManager()->getTranslation("canNotBeLessThan1"));
						return;
					}

					$totalPrice = (int)$data[0]*$price;

					self::confirmation($player, $name, $description, $data[0], $id, $totalPrice);
				}
			}
		);

		$form->setTitle(str_replace("{name}", $name, Main::getLanguageManager()->getUITranslation("setPurchase", "title")));
		$form->addInput(str_replace(
			["{description}", "{effects}", "{price}"],
			[$description, $effects, $price],
            Main::getLanguageManager()->getUITranslation("setPurchase", "content")
		));
		$player->sendForm($form);
	}

	/**
	 * Creates a purchase cofirmation.
	 * @param Player $player
	 * @param string $name
	 * @param string $description
	 * @param int $amount
	 * @param int $id
	 * @param int $totalPrice
	 * @return void
	 */
	public static function confirmation(Player $player, string $name, string $description, int $amount, int $id, int $totalPrice) : void
	{
        $file = new Config(Main::getInstance()->getDataFolder() . "config.yml");
        $type = $file->get("availableCustomSnowballs")[$id];

		$form = new ModalForm(
			function( Player $player, ?bool $data = null) use($type, $amount)
			{
				if(isset($data))
				{
					switch($data)
					{
						case true:
						    API::tryToBuySnowballs($player, $type, $amount);
						break;

						case false:
						    self::openUI($player);
						break;
					}
				}
			}
		);

		$form->setTitle(Main::getLanguageManager()->getUITranslation("confirmation", "title"));
		$form->setContent(str_replace(
			["{name}", "{description}", "{amount}", "{cost}"],
			[$name, $description, $amount, "$".$totalPrice],
            Main::getLanguageManager()->getUITranslation("confirmation", "content")
		));
		$form->setButton1(Main::getLanguageManager()->getTranslation("confirmation-true"));
		$form->setButton2(Main::getLanguageManager()->getTranslation("confirmation-false"));

		$player->sendForm($form);
	}
}
