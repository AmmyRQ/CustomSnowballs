<?php

namespace AmmyRQ\Snowballs;

use pocketmine\world\particle\{
	FlameParticle, LavaParticle, SmokeParticle, EntityFlameParticle,
	BlockBreakParticle, PortalParticle, LavaDripParticle,
	RedstoneParticle, BubbleParticle, EnchantmentTableParticle
};
use pocketmine\entity\Entity;
use pocketmine\block\{
    BlockFactory, BlockLegacyIds
};

class ParticleManager
{

	/**
	 * Adds particles to an entity.
	 * @param Entity $entity
	 * @param string $type 		Snowball type.
	 * @return void
	 */
	public static function addParticles(Entity $entity, string $type) : void
	{
		$level = $entity->getWorld();
		$vector3 = $entity->getLocation()->asVector3();
		$factory = new BlockFactory();

		switch($type)
		{
			case "fireball":
                $level->addParticle($vector3, new FlameParticle());
                $level->addParticle($vector3, new LavaParticle());
                $level->addParticle($vector3, new SmokeParticle());
                $level->addParticle($vector3, new EntityFlameParticle());
			break;

			case "iceball":
			    $level->addParticle($vector3, new BlockBreakParticle($factory->get(BlockLegacyIds::ICE, 0)));
			break;

			case "enderball":
                $level->addParticle($vector3, new LavaDripParticle());
                $level->addParticle($vector3, new PortalParticle());
                $level->addParticle($vector3, new BlockBreakParticle($factory->get(BlockLegacyIds::PORTAL, 0)));
			break;

			case "toxicball":
                $level->addParticle($vector3, new RedstoneParticle());
                $level->addParticle($vector3, new BubbleParticle());
                $level->addParticle($vector3, new BlockBreakParticle($factory->get(BlockLegacyIds::COAL_BLOCK, 0)));
			break;

			case "thunderball":
                $level->addParticle($vector3, new EnchantmentTableParticle());
                $level->addParticle($vector3, new BlockBreakParticle($factory->get(BlockLegacyIds::BEACON, 0)));
			break;
		}
	}
}
