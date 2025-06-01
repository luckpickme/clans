<?php

namespace vexora\clans;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use vexora\clans\types\ClanManager;
use vexora\clans\form\ClanFormManager;
use vexora\clans\event\ClanListener;
use vexora\clans\permissions\ClanPermissions;

class Main extends PluginBase {
    private ClanManager $clanManager;
    private ClanFormManager $formManager;

    public function onEnable(): void {
        
        $this->clanManager = new ClanManager($this);
        $this->formManager = new ClanFormManager($this, $this->clanManager);
        
        $this->getServer()->getPluginManager()->registerEvents(new ClanListener($this, $this->clanManager), $this);
    }

    public function onDisable(): void {
        $this->clanManager->saveData();
    }

    /**
     * Обработчик команды /clans
     */
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        if($command->getName() === "clans") {
            if(!$sender instanceof Player) {
                $sender->sendMessage(TextFormat::RED . "Эта команда только для игроков!");
                return true;
            }
            
            if(!$sender->hasPermission(ClanPermissions::USE)) {
                $sender->sendMessage(TextFormat::RED . "У вас нет прав на использование этой команды!");
                return true;
            }
            
            if(isset($args[0])) {
                switch(strtolower($args[0])) {
                    case "accept":
                        if(isset($args[1])) {
                            if($this->clanManager->acceptInvite($sender, $args[1])) {
                                $sender->sendMessage(TextFormat::GREEN . "Вы вступили в клан!");
                            } else {
                                $sender->sendMessage(TextFormat::RED . "Не удалось принять приглашение!");
                            }
                        } else {
                            $sender->sendMessage(TextFormat::RED . "Использование: /clans accept <название клана>");
                        }
                        return true;
                    case "manage":
                        $this->formManager->sendClanManagementForm($sender);
                        return true;
                }
            }
            
            $this->formManager->sendMainForm($sender);
            return true;
        }
        return false;
    }
}