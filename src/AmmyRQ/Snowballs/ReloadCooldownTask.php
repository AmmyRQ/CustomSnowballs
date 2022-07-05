<?php

namespace AmmyRQ\Snowballs;

use pocketmine\scheduler\Task;
use pocketmine\player\Player;

use AmmyRQ\Snowballs\Main;

class ReloadCooldownTask extends Task
{

	/** @var Player */
	private $player;

	public function __construct(Player $player)
	{
		$this->player = $player;

		$this->grayBars = 10;
		$this->greenBars = 1;

		Main::getInstance()->getScheduler()->scheduleRepeatingTask($this, 10);
	}

	/**
	 * @param int | null $currentTick
	 * @return void
	 */
	public function onRun() : void
	{
		$player = $this->player;

		if(!$player->isOnline())
		{
			unset(Main::getInstance()->generalCooldowns[array_search($player->getName(), Main::getInstance()->generalCooldowns)]);
			$this->getHandler()->cancel();
			return;
		}

		if($this->grayBars === 0)
		{
			$player->sendPopup(Main::getLanguageManager()->getTranslation("ballReloaded"));

			unset(Main::getInstance()->generalCooldowns[array_search($player->getName(), Main::getInstance()->generalCooldowns)]);
			$this->getHandler()->cancel();	
			return;
		}

		$message = str_repeat("§a|", $this->greenBars) . str_repeat("§7|", $this->grayBars);

		$player->sendPopup(Main::getLanguageManager()->getTranslation("reloadingBall") . "\n" . $message);

		--$this->grayBars;
        ++$this->greenBars;
	}
}