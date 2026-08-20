<?php

declare(strict_types=1);

namespace Max\koth;

use pocketmine\player\Player;
use pocketmine\Server;

class Arena{

	private string $name;
	/** @var array<string, mixed> */
	private array $data;

	public function __construct(string $arenaName){
		$this->name = $arenaName;

		$data = KOTH::$instance->data->get($arenaName, null);
		if (is_null($data)) {
			$defaultWorld = Server::getInstance()->getWorldManager()->getDefaultWorld();
			$this->data = [
				"spawn" => null,
				"arenaMin" => [
					"x" => 0,
					"y" => 0,
					"z" => 0,
				],
				"arenaMax" => [
					"x" => 0,
					"y" => 0,
					"z" => 0,
				],
				"world" => $defaultWorld !== null ? $defaultWorld->getFolderName() : "world",
				"coords" => "0, 0, 0"
			];
			$this->save();
		} else {
			$this->data = $data;
		}
	}

	public function getName() : string {
		return $this->name;
	}

	/**
	 * @return array{x: float, y: float, z: float}|null
	 */
	public function getSpawn() : ?array {
		return $this->data["spawn"];
	}

	public function getCoords() : string {
		return $this->data["coords"];
	}

	public function getWorld() : string {
		return $this->data["world"];
	}

	/**
	 * @return array{x: float, y: float, z: float}
	 */
	public function getMin() : array {
		return $this->data["arenaMin"];
	}

	/**
	 * @return array{x: float, y: float, z: float}
	 */
	public function getMax() : array {
		return $this->data["arenaMax"];
	}



	public function setCoords(string $coords) : void {
		$this->data["coords"] = $coords;
		$this->save();
	}

	/**
	 * @param array{x: float, y: float, z: float}|null $spawn
	 */
	public function setSpawn(?array $spawn) : void {
		$this->data["spawn"] = $spawn;
		$this->save();
	}

	public function setWorld(string $worldName) : void {
		$this->data["world"] = $worldName;
		$this->save();
	}

	/**
	 * @param array{x: float, y: float, z: float} $arenaMin
	 */
	public function setMin(array $arenaMin) : void {
		$this->data["arenaMin"] = $arenaMin;
		$this->save();
	}

	/**
	 * @param array{x: float, y: float, z: float} $arenaMax
	 */
	public function setMax(array $arenaMax) : void {
		$this->data["arenaMax"] = $arenaMax;
		$this->save();
	}



	public function save() : void {
		$data = KOTH::$instance->data;
		$data->set($this->name, $this->data);
		$data->save();
	}

	public function isInside(Player $player) : bool {
		$min = $this->getMin();
		$max = $this->getMax();
		if ($player->getWorld()->getFolderName() == $this->getWorld() AND
			$player->isOnline() AND
			$player->getPosition()->getX() >= $min["x"] AND $player->getPosition()->getX() <= $max["x"] AND
			$player->getPosition()->getY() >= $min["y"] AND $player->getPosition()->getY() <= $max["y"] AND
			$player->getPosition()->getZ() >= $min["z"] AND $player->getPosition()->getZ() <= $max["z"]) {
			return True;
		}
		return False;
	}
}