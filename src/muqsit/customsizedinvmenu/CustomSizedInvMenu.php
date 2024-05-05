<?php

declare(strict_types=1);

namespace muqsit\customsizedinvmenu;

use muqsit\invmenu\InvMenu;
use muqsit\invmenu\InvMenuHandler;
use muqsit\customsizedinvmenu\type\CustomSizedInvMenuType;
use muqsit\customsizedinvmenu\manager\ResourcePackManager as RSP;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\cache\StaticPacketCache;
use pocketmine\plugin\PluginBase;

use RuntimeException;
use function array_rand;
use function assert;
use function is_numeric;

final class CustomSizedInvMenu extends PluginBase{

	private const TYPE_DYNAMIC_PREFIX = "muqsit:customsizedinvmenu_";

	public static function create(int $size) : InvMenu{
		static $ids_by_size = [];
		if(!isset($ids_by_size[$size])){
			$id = self::TYPE_DYNAMIC_PREFIX . $size;
			InvMenuHandler::getTypeRegistry()->register($id, CustomSizedInvMenuType::ofSize($size));
			$ids_by_size[$size] = $id;
		}
		return InvMenu::create($ids_by_size[$size]);
	}

	protected function onEnable() : void{
    $this->saveDefaultConfig();
		RSP::register($this);

		if(!InvMenuHandler::isRegistered()){
			InvMenuHandler::register($this);
		}

		$packet = StaticPacketCache::getInstance()->getAvailableActorIdentifiers();
		$tag = $packet->identifiers->getRoot();
		assert($tag instanceof CompoundTag);
		$id_list = $tag->getListTag("idlist");
		assert($id_list !== null);
		$id_list->push(CompoundTag::create()
			->setString("bid", "")
			->setByte("hasspawnegg", 0)
			->setString("id", CustomSizedInvMenuType::ACTOR_NETWORK_ID)
			->setByte("summonable", 0)
		);
	}
	
	protected function onDisable() : void{
	  RSP::unRegister($this);
	}
}
