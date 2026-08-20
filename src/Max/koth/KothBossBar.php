<?php

declare(strict_types=1);

namespace Max\koth;

use pocketmine\network\mcpe\protocol\BossEventPacket;
use pocketmine\player\Player;

/**
 * Single boss bar shared by every viewer of the currently running KOTH event.
 */
class KothBossBar {

	private const BOSS_ACTOR_UNIQUE_ID = -1;

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
			self::BOSS_ACTOR_UNIQUE_ID,
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
			$player->getNetworkSession()->sendDataPacket(BossEventPacket::hide(self::BOSS_ACTOR_UNIQUE_ID));
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

		$titlePacket = $titleChanged ? BossEventPacket::title(self::BOSS_ACTOR_UNIQUE_ID, $title) : null;
		$healthPacket = $percentChanged ? BossEventPacket::healthPercent(self::BOSS_ACTOR_UNIQUE_ID, $percent) : null;
		$propertiesPacket = $colorChanged ? BossEventPacket::properties(self::BOSS_ACTOR_UNIQUE_ID, $color) : null;

		foreach ($this->viewers as $player) {
			if (!$player->isConnected()) {
				continue;
			}
			$session = $player->getNetworkSession();
			if ($titlePacket !== null) $session->sendDataPacket($titlePacket);
			if ($healthPacket !== null) $session->sendDataPacket($healthPacket);
			if ($propertiesPacket !== null) $session->sendDataPacket($propertiesPacket);
		}

		$this->lastTitle = $title;
		$this->lastPercent = $percent;
		$this->lastColor = $color;
	}
}
