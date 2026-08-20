<?php

declare(strict_types=1);

namespace Max\koth\Commands\SubCommands;

use Max\koth\libs\CortexPE\Commando\args\RawStringArgument;
use Max\koth\libs\CortexPE\Commando\BaseSubCommand;
use Max\koth\KOTH;
use pocketmine\command\CommandSender;

class stopSubCommand extends BaseSubCommand {

	protected function prepare(): void {
		$this->setPermission("maxkoth.command.koth.stop");
		$this->registerArgument(0, new RawStringArgument("Arena name", true));
	}

	/**
	 * @param array<string, mixed> $args
	 */
	public function onRun(CommandSender $sender, string $aliasUsed, array $args) : void {
		$sender->sendMessage(KOTH::getInstance()->stopKoth());
	}
}