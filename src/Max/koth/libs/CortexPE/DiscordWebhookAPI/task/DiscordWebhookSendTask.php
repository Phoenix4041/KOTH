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
 *
 * Ported to PM5 (AsyncTask::onCompletion() no longer takes a Server parameter,
 * and only thread-safe scalars may cross into the worker thread).
 */

declare(strict_types=1);

namespace Max\koth\libs\CortexPE\DiscordWebhookAPI\task;

use Max\koth\libs\CortexPE\DiscordWebhookAPI\Message;
use Max\koth\libs\CortexPE\DiscordWebhookAPI\Webhook;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class DiscordWebhookSendTask extends AsyncTask {
	protected string $url;
	protected string $payload;

	public function __construct(Webhook $webhook, Message $message) {
		$this->url = $webhook->getURL();
		$this->payload = json_encode($message, JSON_THROW_ON_ERROR);
	}

	public function onRun() : void {
		$ch = curl_init($this->url);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $this->payload);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);

		$body = curl_exec($ch);
		$code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
		if ($body === false) {
			$body = curl_error($ch);
			$code = 0;
		}
		curl_close($ch);

		$this->setResult([(string) $body, $code]);
	}

	public function onCompletion() : void {
		/** @var array{0: string, 1: int} $response */
		$response = $this->getResult();
		if (!in_array($response[1], [200, 204], true)) {
			Server::getInstance()->getLogger()->error("[DiscordWebhookAPI] Got error ({$response[1]}): " . $response[0]);
		}
	}
}
