<?php

declare(strict_types=1);

namespace Max\koth\Tasks;

use Max\koth\Arena;
use Max\koth\KOTH;
use pocketmine\network\mcpe\protocol\types\BossBarColor;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class KothTask extends Task {

	private KOTH $pl;
	private ?Player $king = null;
	private string $kingName = "...";
	private Arena $arena;
	private int $captureTime;

	public function __construct(KOTH $pl, Arena $arena) {
		$this->pl = $pl;
		$this->arena = $arena;
		$this->captureTime = time();
	}

	public function onRun() : void {
		if ($this->king !== null && $this->king->isOnline() && $this->arena->isInside($this->king)) {
			if (time() - $this->captureTime >= $this->pl->CAPTURE_TIME) {
				$this->pl->stopKoth($this->kingName);
				return;
			}
		} else {
			$this->king = null;
			$this->kingName = "...";
			$this->captureTime = time();
			$onlinePlayers = Server::getInstance()->getOnlinePlayers();
			shuffle($onlinePlayers);
			foreach ($onlinePlayers as $player) {
				if ($this->arena->isInside($player)) {
					$this->king = $player;
					$this->kingName = $player->getName();
					break;
				}
			}
		}

		$elapsed = time() - $this->captureTime;
		$timeLeft = max(0, $this->pl->CAPTURE_TIME - $elapsed);
		$minutes = intdiv($timeLeft, 60);
		$seconds = sprintf("%02d", $timeLeft - ($minutes * 60));

		$bossBar = $this->pl->bossBar;
		if ($bossBar !== null) {
			$percent = $this->pl->CAPTURE_TIME > 0 ? $elapsed / $this->pl->CAPTURE_TIME : 0.0;
			$color = $this->king !== null ? BossBarColor::GREEN : BossBarColor::WHITE;
			$title = "§bKOTH: §c" . $this->arena->getName() . " §r§7- §bTime: §c" . $minutes . ":" . $seconds . " §r§7- §bKing: §c" . $this->kingName;
			$bossBar->update($title, $percent, $color);
			foreach (Server::getInstance()->getOnlinePlayers() as $player) {
				$bossBar->addPlayer($player);
			}
		}

		if ($this->pl->SEND_TIPS) {
			foreach (Server::getInstance()->getOnlinePlayers() as $player) {
				$player->sendTip("§bKOTH: §c" . $this->arena->getName() . "§r - §bTime: §c" . $minutes . ":" . $seconds . "\n§bKing: §c" . $this->kingName);
			}
		}
	}
}
