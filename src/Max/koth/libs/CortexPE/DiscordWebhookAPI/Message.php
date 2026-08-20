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

class Message implements \JsonSerializable {
	/** @var array<string, mixed> */
	protected array $data = [];

	public function setContent(string $content) : void {
		$this->data["content"] = $content;
	}

	public function getContent() : ?string {
		return $this->data["content"] ?? null;
	}

	public function getUsername() : ?string {
		return $this->data["username"] ?? null;
	}

	public function setUsername(string $username) : void {
		$this->data["username"] = $username;
	}

	public function getAvatarURL() : ?string {
		return $this->data["avatar_url"] ?? null;
	}

	public function setAvatarURL(string $avatarURL) : void {
		$this->data["avatar_url"] = $avatarURL;
	}

	public function addEmbed(Embed $embed) : void {
		if (!empty($arr = $embed->asArray())) {
			$this->data["embeds"][] = $arr;
		}
	}

	public function setTextToSpeech(bool $ttsEnabled) : void {
		$this->data["tts"] = $ttsEnabled;
	}

	public function jsonSerialize() : mixed {
		return $this->data;
	}
}
