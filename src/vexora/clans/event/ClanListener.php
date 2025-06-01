<?php

namespace vexora\clans\event;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use vexora\clans\types\ClanManager;

class ClanListener implements Listener {
    public function __construct(
        private PluginBase $plugin,
        private ClanManager $clanManager
    ) {}
    
    /**
     * Обработчик входа игрока - показывает сообщение о кланах
     */
    public function onJoin(PlayerJoinEvent $event): void {
        $player = $event->getPlayer();
        if($this->clanManager->getPlayerClan($player->getName()) === null) {
            $player->sendMessage("§eИспользуйте §a/clans §eчтобы создать или вступить в клан!");
        }
    }
    
    /**
     * Обработчик убийства - начисляет EXP и убийства клану убийцы
     */
    public function onKill(EntityDamageByEntityEvent $event): void {
        $victim = $event->getEntity();
        $damager = $event->getDamager();
        
        if($victim instanceof Player && $damager instanceof Player) {
            if($victim->getHealth() - $event->getFinalDamage() <= 0) {
                $clan = $this->clanManager->getPlayerClan($damager->getName());
                if ($clan !== null) {
                    $clan->addKill();
                    $this->clanManager->saveData();
                }
            }
        }
    }
}