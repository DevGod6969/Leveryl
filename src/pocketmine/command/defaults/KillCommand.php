<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

namespace pocketmine\command\defaults;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\TranslationContainer;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class KillCommand extends VanillaCommand {

	/**
	 * KillCommand constructor.
	 *
	 * @param $name
	 */
	public function __construct($name){
		parent::__construct(
			$name,
			"%pocketmine.command.kill.description",
			"%pocketmine.command.kill.usage",
			["suicide"]
		);
		$this->setPermission("pocketmine.command.kill.self;pocketmine.command.kill.other");
		/*  
		fix in Player.php
		/kill    is everybody can use
		
		*/

	}

	/**
	 * @param CommandSender $sender
	 * @param string $currentAlias
	 * @param array $args
	 *
	 * @return bool
	 */
	public function execute(CommandSender $sender, $currentAlias, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		if(count($args) >= 2){
			$sender->sendMessage(new TranslationContainer("commands.generic.usage", [$this->usageMessage]));

			return false;
		}

		if(count($args) === 1){
			if(!$sender->hasPermission("pocketmine.command.kill.other")){
				$sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.generic.permission"));

				return true;
			}

			switch($args[0]){
				case '@r':
					$players = $sender->getServer()->getOnlinePlayers();
					if(count($players) > 0){
						$player = $players[array_rand($players)];
					} else {
						$sender->sendMessage("No players online");
						return true;
					}

					if($player instanceof Player){
						$sender->getServer()->getPluginManager()->callEvent($ev = new EntityDamageEvent($player, EntityDamageEvent::CAUSE_SUICIDE, 1000));

						if($ev->isCancelled()){
							return true;
						}

						$player->setLastDamageCause($ev);
						$player->setHealth(0);

						$sender->sendMessage("Killed " . $player->getName());
					}
					return true;
				case '@e':
					$count = 0;
					if($sender instanceof Player){
						foreach($sender->getLevel()->getEntities() as $entity){
							if($entity instanceof Player){
								if($entity->getGamemode() === Player::ADVENTURE or $entity->getGamemode() === Player::SURVIVAL){
									$sender->getServer()->getPluginManager()->callEvent($ev = new EntityDamageEvent($entity, EntityDamageEvent::CAUSE_SUICIDE, 1000));

									if($ev->isCancelled()){
										return true;
									}

									$entity->setLastDamageCause($ev);
									$entity->setHealth(0);
								}
							} else {
								$entity->close();
							}
							$count++;
						}
					} else {
						foreach($sender->getServer()->getDefaultLevel()->getEntities() as $entity){
							if($entity instanceof Player){
								if($entity->getGamemode() === Player::ADVENTURE or $entity->getGamemode() === Player::SURVIVAL){
									$sender->getServer()->getPluginManager()->callEvent($ev = new EntityDamageEvent($entity, EntityDamageEvent::CAUSE_SUICIDE, 1000));

									if($ev->isCancelled()){
										return true;
									}

									$entity->setLastDamageCause($ev);
									$entity->setHealth(0);
								}
							} else {
								$sender->getServer()->getPluginManager()->callEvent($ev = new EntityDamageEvent($entity, EntityDamageEvent::CAUSE_SUICIDE, 1000));

								if($ev->isCancelled()){
									return true;
								}

								$entity->setLastDamageCause($ev);
								$entity->setHealth(0);
							}
							$count++;
						}
					}
					$sender->sendMessage("Killed " . $count . " Entities");
					return true;
				case '@p':
					$player = $sender;
					if($player instanceof Player){
						$sender->getServer()->getPluginManager()->callEvent($ev = new EntityDamageEvent($player, EntityDamageEvent::CAUSE_SUICIDE, 1000));

						if($ev->isCancelled()){
							return true;
						}

						$player->setLastDamageCause($ev);
						$player->setHealth(0);

						Command::broadcastCommandMessage($sender, new TranslationContainer("commands.kill.successful", [$player->getName()]));
					}else{
						$sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.generic.player.notFound"));
					}
					return true;
				default;
					$player = $sender->getServer()->getPlayer($args[0]);
					if($player instanceof Player){
						$sender->getServer()->getPluginManager()->callEvent($ev = new EntityDamageEvent($player, EntityDamageEvent::CAUSE_SUICIDE, 1000));

						if($ev->isCancelled()){
							return true;
						}

						$player->setLastDamageCause($ev);
						$player->setHealth(0);

						Command::broadcastCommandMessage($sender, new TranslationContainer("commands.kill.successful", [$player->getName()]));
					}else{
						$sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.generic.player.notFound"));
					}
					return true;
			}
		}

		if($sender instanceof Player){
			if(!$sender->hasPermission("pocketmine.command.kill.self")){
				$sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.generic.permission"));

				return true;
			}

			$sender->getServer()->getPluginManager()->callEvent($ev = new EntityDamageEvent($sender, EntityDamageEvent::CAUSE_SUICIDE, 1000));

			if($ev->isCancelled()){
				return true;
			}

			$sender->setLastDamageCause($ev);
			$sender->setHealth(0);
			$sender->sendMessage(new TranslationContainer("commands.kill.successful", [$sender->getName()]));
		}else{
			$sender->sendMessage(new TranslationContainer("commands.generic.usage", [$this->usageMessage]));

			return false;
		}

		return true;
	}
}
