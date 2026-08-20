<?php

/***
 * DiscordWebhookAPI - A PocketMine-MP Virion to easily send messages via Discord Webhooks
 * https://github.com/CortexPE/DiscordWebhookAPI
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Written by @CortexPE <https://CortexPE.xyz>
 */

declare(strict_types=1);

namespace Max\koth\libs\CortexPE\DiscordWebhookAPI;

use Max\koth\libs\CortexPE\DiscordWebhookAPI\task\DiscordWebhookSendTask;
use pocketmine\Server;

class Webhook {
	protected string $url;

	public function __construct(string $url) {
		$this->url = $url;
	}

	public function getURL() : string {
		return $this->url;
	}

	public function isValid() : bool {
		return filter_var($this->url, FILTER_VALIDATE_URL) !== false;
	}

	public function send(Message $message) : void {
		Server::getInstance()->getAsyncPool()->submitTask(new DiscordWebhookSendTask($this, $message));
	}
}
