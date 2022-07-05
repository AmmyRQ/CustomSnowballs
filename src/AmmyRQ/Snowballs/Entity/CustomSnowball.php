<?php

namespace AmmyRQ\Snowballs\Entity;

use pocketmine\player\Player;
use pocketmine\entity\{Human, Entity};
use pocketmine\entity\effect\{
    EffectInstance, VanillaEffects
};
use pocketmine\world\particle\BlockBreakParticle;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\network\mcpe\protocol\AddActorPacket;

use AmmyRQ\Snowballs\Main;
use AmmyRQ\Snowballs\ParticleManager;

class CustomSnowball extends Human
{

	/** @var Player|null */
	private ?Player $owner = null;

	/**
     * Indicates the type of ball being thrown
     * @var string
     */
	private string $type = "none";

	/**
	 * Initialize the entity.
	 * @return void
	 */
	public function init() : void
	{
		$this->setBreathing(false);
		$this->setNameTag("");
        $this->setNameTagVisible(false);
		$this->setScale(3);
	}

	/**
	 * @param int $tickDiff = 1
	 * @return bool
	 */
	public function entityBaseTick(int $tickDiff = 1) : bool
	{
		if ($this->getOwner() instanceof Player && $this->getOwner()->isOnline() && !$this->isCollided)
			ParticleManager::addParticles($this, $this->getType());
		else
		{
			$this->setOwner(null);
            $this->close();
			return false;
		}

		return parent::entityBaseTick($tickDiff);
	}

	/**
     *
	 * @return void
	 */
	public function setType($type) : void
	{
		$this->type = $type;
	}

	/**
	 * @return string
	 */
	public function getType() : string
	{
		return $this->type;
	}

    /**
     * @param Player|null $player
     * @return void
     */
    public function setOwner(?Player $player) : void
    {
        $this->owner = $player;
    }

    /**
     * @return Player|null
     */
    public function getOwner() : ?Player
    {
        return $this->owner;
    }

    /**
     * @param Player $playerHit
     * @throws \Exception
     * @return void
     */
	public function onCollideWithPlayer(Player $playerHit) : void
	{
		if($this->isCollided) return;

		$owner = $this->getOwner();
		$living = $playerHit->getEffects();

		if($playerHit->getUniqueId() === $owner->getUniqueId())
            return;


		if(Main::isWorldGuardInstalled($playerHit))
            return;

		switch($this->getType())
		{
			//Fireball
			case "fireball":
                $damageEv = new EntityDamageByEntityEvent($owner, $playerHit, 1, 0.1);
                $playerHit->attack($damageEv);

                $playerHit->setOnFire(8);

                if($owner->isOnline())
                    Main::playSound($owner, "random.orb");

                Main::playSound($playerHit, "mob.shulker.bullet.hit");
			break;

			//Iceball
			case "iceball":
                $damageEv = new EntityDamageByEntityEvent($owner, $playerHit, 1, 0.1);
                    $playerHit->attack($damageEv);

                $living->add(new EffectInstance(VanillaEffects::SLOWNESS(), 15*20, 1));

                if($owner->isOnline())
                    Main::playSound($owner, "random.orb");

                Main::playSound($playerHit, "block.turtle_egg.crack");
			break;

			//Teleport ball
			case "enderball":
                if(!$owner->isOnline()) break;

                $damageEv = new EntityDamageByEntityEvent($owner, $playerHit, 1, 0.1);
                    $playerHit->attack($damageEv);

                $hittedPosition = $playerHit->getLocation()->asVector3();
                $playerPosition = $owner->getLocation()->asVector3();

                $owner->teleport($hittedPosition);
                    $playerHit->teleport($playerPosition);

                Main::playSound($owner, "mob.shulker.teleport");
                Main::playSound($playerHit, "bottle.dragonbreath");
			break;

			//Poison ball
			case "toxicball":
                $damageEv = new EntityDamageByEntityEvent($owner, $playerHit, 1, 0.1);
                    $playerHit->attack($damageEv);

                $living->add(new EffectInstance(VanillaEffects::BLINDNESS(), 8*20, 1));
                $living->add(new EffectInstance(VanillaEffects::NAUSEA(), 8*20, 2));
                $living->add(new EffectInstance(VanillaEffects::POISON(), 5*20, 1));

                if($owner->isOnline())
                    Main::playSound($owner, "random.orb");

                Main::playSound($playerHit, "mob.blaze.breathe");
			break;

			//Lightning ball
			case "thunderball":
                //Applies damage to a player from source
                $damageEv = new EntityDamageByEntityEvent($owner, $playerHit, 1, 8.0);
                $playerHit->attack($damageEv);

                $light = new AddActorPacket();
                $light->type = "minecraft:lightning_bolt";
                $light->actorUniqueId = Entity::nextRuntimeId();
                $light->actorRuntimeId = 1;
                $light->metadata = [];
                $light->motion = null;
                $light->yaw = $playerHit->getLocation()->getYaw();
                $light->pitch = $playerHit->getLocation()->getPitch();
                $light->position = $playerHit->getLocation()->asVector3();

                Main::getInstance()->getServer()->broadcastPackets($playerHit->getWorld()->getPlayers(), [$light]);

                $block = $playerHit->getWorld()->getBlock($playerHit->getPosition()->floor()->down());
                $particle = new BlockBreakParticle($block);
                $playerHit->getWorld()->addParticle($playerHit->getLocation()->asVector3(), $particle);

                $living->add(new EffectInstance(VanillaEffects::BLINDNESS(), 3*20, 1, true));

                if($owner->isOnline())
                    Main::playSound($owner, "random.orb");

                Main::playSound($playerHit, "ambient.weather.lightning.impact");
			break;
		}

		$this->close();
	}
}