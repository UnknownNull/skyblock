<?php
/**
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

declare(strict_types=1);

namespace GiantQuartz\SkyBlock\command\presets;


use ReflectionException;
use GiantQuartz\SkyBlock\command\IslandCommand;
use GiantQuartz\SkyBlock\command\IslandCommandMap;
use GiantQuartz\SkyBlock\session\Session;
use GiantQuartz\SkyBlock\SkyBlock;
use GiantQuartz\SkyBlock\utils\message\MessageContainer;

class KickCommand extends IslandCommand {

    /** @var SkyBlock */
    private $plugin;

    public function __construct(IslandCommandMap $map) {
        $this->plugin = $map->getPlugin();
    }

    public function getName(): string {
        return "kick";
    }

    public function getUsageMessageContainer(): MessageContainer {
        return new MessageContainer("KICK_USAGE");
    }

    public function getDescriptionMessageContainer(): MessageContainer {
        return new MessageContainer("KICK_DESCRIPTION");
    }

    /**
     * @throws ReflectionException
     */
    public function onCommand(Session $session, array $args): void {
        if($this->checkOfficer($session)) {
            return;
        } elseif(!isset($args[0])) {
            $session->sendTranslatedMessage(new MessageContainer("KICK_USAGE"));
            return;
        }
        $server = $this->plugin->getServer();
        $player = $server->getPlayer($args[0]);
        if($player == null) {
            $session->sendTranslatedMessage(new MessageContainer("NOT_ONLINE_PLAYER", [
                "name" => $args[0]
            ]));
            return;
        }
        $playerSession = $this->plugin->getSessionManager()->getSession($player);
        if($this->checkClone($session, $playerSession)) {
            return;
        } elseif($playerSession->getIsland() === $session->getIsland()) {
            $session->sendTranslatedMessage(new MessageContainer("CANNOT_KICK_A_MEMBER"));
        } elseif(in_array($player, $session->getIsland()->getPlayersOnline())) {
            $player->teleport($server->getDefaultLevel()->getSpawnLocation());
            $playerSession->sendTranslatedMessage(new MessageContainer("KICKED_FROM_THE_ISLAND"));
            $session->sendTranslatedMessage(new MessageContainer("YOU_KICKED_A_PLAYER", [
                "name" => $playerSession->getName()
            ]));
        } else {
            $session->sendTranslatedMessage(new MessageContainer("NOT_A_VISITOR", [
                "name" => $playerSession->getName()
            ]));
        }
    }

}