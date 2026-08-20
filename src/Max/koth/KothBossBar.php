<?php

declare(strict_types=1);

namespace Max\koth;

use pocketmine\network\mcpe\protocol\BossEventPacket;
use pocketmine\player\Player;

/**
 * Single boss bar shared by every viewer of the currently running KOTH event.
 *
 * The boss actor ID is keyed per viewer (their own entity ID) rather than a
 * shared fake ID: clients ignore TYPE_SHOW for an actor ID they have no
 * knowledge of, but every client already knows its own player entity.
 */
class KothBossBar {

	/** @var array<int, Player> */
	private array $viewers = [];

	private string $lastTitle = "";
	private float $lastPercent = 0.0;
	private int $lastColor;

	public function __construct(int $defaultColor) {
		$this->lastColor = $defaultColor;
	}

	public function addPlayer(Player $player) : void {
		$id = spl_object_id($player);
		if (isset($this->viewers[$id]) || !$player->isConnected()) {
			return;
		}
		$this->viewers[$id] = $player;
		$player->getNetworkSession()->sendDataPacket(BossEventPacket::show(
			$player->getId(),
			$this->lastTitle,
			$this->lastPercent,
			$this->lastColor
		));
	}

	public function removePlayer(Player $player) : void {
		$id = spl_object_id($player);
		if (!isset($this->viewers[$id])) {
			return;
		}
		unset($this->viewers[$id]);
		if ($player->isConnected()) {
			$player->getNetworkSession()->sendDataPacket(BossEventPacket::hide($player->getId()));
		}
	}

	public function removeAll() : void {
		foreach ($this->viewers as $player) {
			$this->removePlayer($player);
		}
	}

	public function update(string $title, float $percent, int $color) : void {
		$percent = max(0.0, min(1.0, $percent));

		$titleChanged = $title !== $this->lastTitle;
		$percentChanged = abs($percent - $this->lastPercent) > 0.001;
		$colorChanged = $color !== $this->lastColor;
		if (!$titleChanged && !$percentChanged && !$colorChanged) {
			return;
		}

		foreach ($this->viewers as $player) {
			if (!$player->isConnected()) {
				continue;
			}
			$session = $player->getNetworkSession();
			$bossId = $player->getId();
			if ($titleChanged) $session->sendDataPacket(BossEventPacket::title($bossId, $title));
			if ($percentChanged) $session->sendDataPacket(BossEventPacket::healthPercent($bossId, $percent));
			if ($colorChanged) $session->sendDataPacket(BossEventPacket::properties($bossId, $color));
		}

		$this->lastTitle = $title;
		$this->lastPercent = $percent;
		$this->lastColor = $color;
	}
}
