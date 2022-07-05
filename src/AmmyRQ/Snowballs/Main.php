<?php

namespace AmmyRQ\Snowballs;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\entity\projectile\Snowball as SnowballClass;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\utils\Config;
use pocketmine\event\entity\{
	ProjectileLaunchEvent, EntityDamageEvent
};
use pocketmine\entity\{EntityFactory, EntityDataHelper, Skin};
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\world\World;
use pocketmine\entity\Location;
use pocketmine\player\Player;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\item\ItemIds;

use AmmyRQ\Snowballs\Entity\CustomSnowball;
use AmmyRQ\Snowballs\Commands\SnowballsCmd;

class Main extends PluginBase implements Listener
{

	/** @var Main|null */
	private static ?Main $instance = null;

	/** @var array */
	public array $generalCooldowns = [];

	/**
	 * @return self
	 * @throws \Exception if $instance is null
	 */
	public static function getInstance() : self
	{
		if(self::$instance === null) throw new \Exception("[Snowballs] Instance is null.");

		return self::$instance;
	}

	/**
	 * Returns the language manager.
	 * @return LanguageManager
	 */
	public static function getLanguageManager() : LanguageManager
	{
		return new LanguageManager();
	}

	/**
	 * Checks if the WorldGuard plugin exists and if $player is in a safe zone.
     * @param Player $player
	 * @return bool
	*/
	public static function isWorldGuardInstalled(Player $player) : bool
	{
		$worldGuard = self::getInstance()->getServer()->getPluginManager()->getPlugin("WorldGuard");

		if($worldGuard !== null)
		{
			if($worldGuard->getRegionByPlayer($player) !== "")
			{
				$reg = $worldGuard->getRegionByPlayer($player);

				if($reg->getFlag("pvp") === "false")
                    return true;

				if($reg->getFlag("invincible") === "true")
                    return true;
			}
		}

		return false;
	}

	/**
	 * @return void
	 */
	public function onEnable() : void
	{
		self::$instance = $this;

		//Checks and creates the resource files
		FileManager::init();

		//Creates a custom snowball class
		$factory = new EntityFactory();
		$factory->register(
			CustomSnowball::class, function(World $world, CompoundTag $tag) : CustomSnowball
			{
				return new CustomSnowball(EntityDataHelper::parseLocation($tag, $world), CustomSnowball::parseSkinNBT($tag), $tag);
			}, ['Human']
		);

		$this->getServer()->getLogger()->debug("[CustomSnowballs] Entity registered successfully.");

		$this->getServer()->getCommandMap()->register("snowballs", new SnowballsCmd($this));
		$this->getServer()->getLogger()->debug("[CustomSnowballs] Command registered successfully.");

		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	/**
	 * @param PlayerInteractEvent $event
	 * @return void
	 */
	public function onInteract(PlayerInteractEvent $event) : void
	{
		$player = $event->getPlayer();
		$item = $event->getItem();

		if($item->getId() === ItemIds::SNOWBALL)
		{
			if(self::isWorldGuardInstalled($player))
                return;
			
			switch($item->getCustomName())
			{
                case self::getLanguageManager()->getSnowballTranslation("fireball", "name"):
                case self::getLanguageManager()->getSnowballTranslation("iceball", "name"):
                case self::getLanguageManager()->getSnowballTranslation("enderball", "name"):
                case self::getLanguageManager()->getSnowballTranslation("toxicball", "name"):
                case self::getLanguageManager()->getSnowballTranslation("thunderball", "name"):
                    $file = new Config($this->getDataFolder() . "config.yml");

                    if(!in_array($player->getWorld()->getDisplayName(), $file->get("allowedWorlds")))
                    {
                        self::getLanguageManager()->getTranslation("worldNotAllowed");
                        $event->cancel();
                        return;
                    }

                    if(in_array($player->getName(), $this->generalCooldowns))
                        $event->cancel();
				break;
			}
		}
	}

	/**
	 * @param Player $player
	 * @return void
	 */
	public static function shootSnowball(Player $player) : void
	{
		$item = $player->getInventory()->getItemInHand();
		if($item->getId() === ItemIds::SNOWBALL)
		{
			switch($item->getName())
			{
				case self::getLanguageManager()->getSnowballTranslation("fireball", "name"):
                    $type = "fireball";
                    self::playSound($player, "mob.blaze.shoot");
				break;

                case self::getLanguageManager()->getSnowballTranslation("iceball", "name"):
                    $type = "iceball";
                    self::playSound($player, "mob.snowgolem.death");
				break;

                case self::getLanguageManager()->getSnowballTranslation("enderball", "name"):
                    $type = "enderball";
                    self::playSound($player, "mob.enderdragon.hit");
				break;

				case self::getLanguageManager()->getSnowballTranslation("toxicball", "name"):
                    $type = "toxicball";
                    self::playSound($player, "mob.witch.ambient");
				break;

				case self::getLanguageManager()->getSnowballTranslation("thunderball", "name"):
                    $type = "thunderball";
                    self::playSound($player, "ambient.weather.thunder");
				break;

				default:
                    $type = "none";
				break;
			}
		}

		if($type === "none")
            return;

		$location = $player->getLocation();
		
		$projectile = new CustomSnowball(
			Location::fromObject($player->getEyePos(), $player->getWorld(), $location->yaw, $location->pitch), 
			new Skin(
				$type, self::getInstance()->createSkin($type), '',
				"geometry.customSnowball", file_get_contents(self::getInstance()->getDataFolder() . "customSnowball.json")
			)
		);

		$projectile->init();
		$projectile->setMotion($player->getDirectionVector()->multiply(3));

		$projectile->plugin = self::getInstance();
		$projectile->setOwner($player);
		$projectile->setType($type);

		$item->setCount($item->getCount() - 1);
		$player->getInventory()->setItemInHand($item);

		$projectile->spawnToAll();
	}

	/**
	 * @param ProjectileLaunchEvent $event
	 * @return void
	 */
	public function onLaunch(ProjectileLaunchEvent $event) : void
	{
		$entity = $event->getEntity();
		$player = $entity->getOwningEntity();

		if(!$player instanceof Player)
            return;

		if($entity instanceof SnowballClass)
		{
			//Prevents to add cooldown to a player who threw a normal snowball
			if(!$player->getInventory()->getItemInHand()->hasCustomName())
                return;

			if(!in_array($player->getName(), $this->generalCooldowns))
			{
				$this->generalCooldowns[] = $player->getName();
				new ReloadCooldownTask($player);
			}
			else
			{
				$event->cancel();
				return;
			}

            $file = new Config($this->getDataFolder() . "config.yml");

            if(!in_array($player->getWorld()->getDisplayName(), $file->get("allowedWorlds")))
            {
                self::getLanguageManager()->getTranslation("worldNotAllowed");
                $event->cancel();
                return;
            }

			$event->cancel();

			self::shootSnowball($player);
		}
	}

	/**
	 * Handles the snowball damage.
	 * @param EntityDamageEvent $event
	 * @priority NORMAL
	 * @return void
	 */
	public function onDamage(EntityDamageEvent $event) : void
	{
		if($event->getEntity() instanceof CustomSnowball)
		{
			switch($event->getCause())
			{
				case EntityDamageEvent::CAUSE_VOID:
				case EntityDamageEvent::CAUSE_SUFFOCATION:
                    $event->cancel();
                    $event->getEntity()->close();
				break;
				case EntityDamageEvent::CAUSE_LAVA:
                    $event->cancel();
                    $event->getEntity()->flagForDespawn();
                break;
			}

			$event->cancel();
		}
	}

	/**
	 * Sends a certain sound to a player (from game resources).
	 * @param Player $player
	 * @param string $soundName		You can find all sounds here: https://minecraft.fandom.com/wiki/Sounds.json/Bedrock_Edition_values
	 * @param float $volume			Default: 1.0
	 * @param float $pitch 			Default: 1.0
	*/
	public static function playSound(Player $player, string $soundName, float $volume = 1.0, float $pitch = 1.0)
	{
		$pk = new PlaySoundPacket();
		$pk->soundName = $soundName;
		$pk->x = (int)$player->getLocation()->asVector3()->getX();
		$pk->y = (int)$player->getLocation()->asVector3()->getY();
		$pk->z = (int)$player->getLocation()->asVector3()->getZ();
		$pk->volume = $volume;
		$pk->pitch = $pitch;
		$player->getNetworkSession()->sendDataPacket($pk);
	}

    /**
     * Converts an image to data
     * @param string $type Snowball type
     * @return string
     * @throws \Exception
     */
	public static function createSkin(string $type) : string
	{
		$path = self::getInstance()->getDataFolder() . "skins" . DIRECTORY_SEPARATOR . $type . ".png";

		$img = @imagecreatefrompng($path);
		$bytes = '';
		for ($y = 0; $y < @imagesy($img); $y++) {
			for ($x = 0; $x < @imagesx($img); $x++) {
				$rgba = @imagecolorat($img, $x, $y);
				$a = ((~((int)($rgba >> 24))) << 1) & 0xff;
				$r = ($rgba >> 16) & 0xff;
				$g = ($rgba >> 8) & 0xff;
				$b = $rgba & 0xff;
				$bytes .= chr($r) . chr($g) . chr($b) . chr($a);
			}
		}
		@imagedestroy($img);
		return $bytes;
	}

}
