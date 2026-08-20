<?php

declare(strict_types=1);

namespace Max\koth\Commands\SubCommands;

use Max\koth\libs\CortexPE\Commando\args\RawStringArgument;
use Max\koth\libs\CortexPE\Commando\BaseSubCommand;
use Max\koth\KOTH;
use pocketmine\command\CommandSender;

class deleteSubCommand extends BaseSubCommand {

	protected function prepare(): void {
		$this->setPermission("maxkoth.command.koth.delete");
		$this->registerArgument(0, new RawStringArgument("Arena name", false));
	}

	/**
	 * @param array<string, mixed> $args
	 */
	public function onRun(CommandSender $sender, string $aliasUsed, array $args) : void {
		$koth = KOTH::getInstance();
		$arena = $koth->getArena($args["Arena name"]);
		if (!$arena){
			$sender->sendMessage("§7[§bKOTH§7] §cThat arena does not exist.");
			return;
		}
		$koth->data->remove($arena->getName());
		$sender->sendMessage("§7[§bKOTH§7] §aDeleted koth arena");
	}
}