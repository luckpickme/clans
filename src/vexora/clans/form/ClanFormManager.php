<?php

namespace vexora\clans\form;

use pocketmine\player\Player;
use awpe\practice\gui\form\SimpleForm; //replace with jojoe77777 simpleform
use awpe\practice\gui\form\CustomForm; //replace with jojoe77777 customform
use pocketmine\utils\TextFormat;
use pocketmine\plugin\PluginBase;
use vexora\clans\types\ClanManager;
use vexora\clans\types\ClanColor;
use vexora\clans\permissions\ClanPermissions;

class ClanFormManager {
    
    public function __construct(
        private PluginBase $plugin,
        private ClanManager $clanManager
    ) {}

    public function sendMainForm(Player $player): void {
        $form = new SimpleForm(function(Player $player, ?int $data = null): void {
            if($data === null) return;
            
            switch($data) {
                case 0: $this->sendCreateClanForm($player); break;
                case 1: $this->sendClanList($player); break;
                case 2: $this->sendMyClanInfo($player); break;
                case 3: $this->sendLeaveClanForm($player); break;
                case 4: $this->sendClanManagementForm($player); break;
            }
        });
        
        $form->setTitle("§l§6Система кланов");
        $form->setContent("Выберите действие:");
        $form->addButton("§aСоздать клан");
        $form->addButton("§9Список кланов");
        $form->addButton("§dМой клан");
        $form->addButton("§cПокинуть клан");
        
        if($this->clanManager->getPlayerClan($player->getName()) !== null) {
            $form->addButton("§6Управление кланом");
        }
        
        $player->sendForm($form);
    }

    private function sendCreateClanForm(Player $player): void {
        if(!$player->hasPermission(ClanPermissions::CREATE)) {
            $player->sendMessage(TextFormat::RED . "У вас нет прав на создание клана!");
            return;
        }
        
        $form = new CustomForm(function(Player $player, ?array $data = null): void {
            if($data === null) return;
            
            $clanName = trim($data[0]);
            $colorIndex = (int)$data[1];
            
            if(empty($clanName)) {
                $player->sendMessage(TextFormat::RED . "Название клана не может быть пустым!");
                return;
            }
            
            $color = ClanColor::from($colorIndex);
            if($this->clanManager->createClan($player, $clanName, $color)) {
                $player->sendMessage(TextFormat::GREEN . "Клан " . $color->getTextFormat() . $clanName . TextFormat::GREEN . " успешно создан!");
            } else {
                $player->sendMessage(TextFormat::RED . "Клан с таким названием уже существует!");
            }
        });
        
        $form->setTitle("§l§6Создание клана");
        $form->addInput("Название клана", "Введите название");
        $form->addDropdown("Цвет клана", array_map(fn(ClanColor $c) => $c->getName(), ClanColor::cases()));
        $player->sendForm($form);
    }

    private function sendClanList(Player $player): void {
        $form = new SimpleForm(function(Player $player, ?int $data = null): void {
            if($data === null) return;
            
            $clans = array_values($this->clanManager->getAllClans());
            if(isset($clans[$data])) {
                $this->sendClanInfo($player, $clans[$data]->getName());
            }
        });
        
        $form->setTitle("§l§6Список кланов");
        $form->setContent("Выберите клан для просмотра информации:");
        
        foreach($this->clanManager->getAllClans() as $clan) {
            $form->addButton($clan->getFormattedName() . "\n§7Участников: " . count($clan->getMembers()));
        }
        
        $player->sendForm($form);
    }

    private function sendClanInfo(Player $player, string $clanName): void {
        $clan = $this->clanManager->getClan($clanName);
        if($clan === null) {
            $player->sendMessage(TextFormat::RED . "Этот клан не существует!");
            return;
        }
        
        $form = new SimpleForm(function(Player $player, $data = null): void {});
        
        $stats = $clan->getStats();
        $content = "§aЛидер: §f" . $clan->getLeader() . "\n";
        $content .= "§aЦвет: §f" . $clan->getColor()->getName() . "\n";
        $content .= "§aУчастников: §f" . count($clan->getMembers()) . "\n";
        $content .= "§aУбийств: §f" . $stats["kills"] . "\n";
        $content .= "§aEXP: §f" . $stats["exp"] . "\n";
        $content .= "§aУровень: §f" . $stats["level"] . "\n\n";
        $content .= "§aУчастники:\n§f" . implode("\n", $clan->getMembers());
        
        $form->setTitle("§l§6Информация о клане: " . $clan->getFormattedName());
        $form->setContent($content);
        $form->addButton("§cНазад");
        $player->sendForm($form);
    }

    private function sendMyClanInfo(Player $player): void {
        $clan = $this->clanManager->getPlayerClan($player->getName());
        if($clan === null) {
            $player->sendMessage(TextFormat::RED . "Вы не состоите в клане!");
            return;
        }
        
        $this->sendClanInfo($player, $clan->getName());
    }

    private function sendLeaveClanForm(Player $player): void {
        $clan = $this->clanManager->getPlayerClan($player->getName());
        if($clan === null) {
            $player->sendMessage(TextFormat::RED . "Вы не состоите в клане!");
            return;
        }
        
        $form = new SimpleForm(function(Player $player, ?int $data = null) use ($clan): void {
            if($data === null) return;
            
            if($data === 0) {
                if($this->clanManager->leaveClan($player)) {
                    $player->sendMessage(TextFormat::GREEN . "Вы покинули клан " . $clan->getFormattedName());
                } else {
                    $player->sendMessage(TextFormat::RED . "Вы не можете покинуть клан, так как вы его лидер!");
                }
            }
        });
        
        $form->setTitle("§l§6Покинуть клан");
        $form->setContent("Вы уверены, что хотите покинуть клан " . $clan->getFormattedName() . "?");
        $form->addButton("§aДа, покинуть");
        $form->addButton("§cНет, остаться");
        $player->sendForm($form);
    }

    private function sendClanManagementForm(Player $player): void {
        $clan = $this->clanManager->getPlayerClan($player->getName());
        if ($clan === null) {
            $player->sendMessage(TextFormat::RED . "Вы не состоите в клане!");
            return;
        }
        
        $isLeader = $clan->getLeader() === $player->getName();
        
        $form = new SimpleForm(function(Player $player, ?int $data = null) use ($isLeader): void {
            if($data === null) return;
            
            switch($data) {
                case 0: $this->sendInvitePlayerForm($player); break;
                case 1: 
                    if ($isLeader) $this->sendDisbandClanForm($player); 
                    break;
                case 2: $this->sendKickPlayerForm($player); break;
            }
        });
        
        $form->setTitle("§l§6Управление кланом");
        $form->setContent("Выберите действие:");
        
        if ($isLeader) {
            $form->addButton("§aПригласить игрока");
            $form->addButton("§cУдалить клан");
            $form->addButton("§4Исключить игрока");
        } else {
            $form->addButton("§aПригласить игрока");
            $form->setContent("§cТолько лидер может управлять кланом");
        }
        
        $player->sendForm($form);
    }

    private function sendInvitePlayerForm(Player $player): void {
        $onlinePlayers = array_filter(
            $this->plugin->getServer()->getOnlinePlayers(),
            fn(Player $p) => $p->getName() !== $player->getName()
        );
        
        $form = new SimpleForm(function(Player $player, ?int $data = null) use ($onlinePlayers): void {
            if($data === null) return;
            
            $players = array_values($onlinePlayers);
            if(isset($players[$data])) {
                $target = $players[$data];
                if ($this->clanManager->invitePlayer($player, $target)) {
                    $player->sendMessage(TextFormat::GREEN . "Приглашение отправлено игроку " . $target->getName());
                } else {
                    $player->sendMessage(TextFormat::RED . "Не удалось отправить приглашение!");
                }
            }
        });
        
        $form->setTitle("§l§6Приглашение в клан");
        $form->setContent("Выберите игрока для приглашения:");
        
        foreach ($onlinePlayers as $p) {
            $form->addButton($p->getName());
        }
        
        $player->sendForm($form);
    }

    private function sendDisbandClanForm(Player $player): void {
        $clan = $this->clanManager->getPlayerClan($player->getName());
        if ($clan === null) return;
        
        $form = new SimpleForm(function(Player $player, ?int $data = null) use ($clan): void {
            if($data === null) return;
            
            if($data === 0) {
                if ($this->clanManager->disbandClan($player)) {
                    $player->sendMessage(TextFormat::GREEN . "Клан " . $clan->getFormattedName() . " был удален!");
                } else {
                    $player->sendMessage(TextFormat::RED . "Не удалось удалить клан!");
                }
            }
        });
        
        $form->setTitle("§l§6Удаление клана");
        $form->setContent("Вы уверены, что хотите удалить клан " . $clan->getFormattedName() . "? Это действие нельзя отменить!");
        $form->addButton("§aДа, удалить");
        $form->addButton("§cНет, отмена");
        $player->sendForm($form);
    }

    private function sendKickPlayerForm(Player $player): void {
        $clan = $this->clanManager->getPlayerClan($player->getName());
        if ($clan === null || $clan->getLeader() !== $player->getName()) return;
        
        $members = array_diff($clan->getMembers(), [$player->getName()]);
        
        $form = new SimpleForm(function(Player $player, ?int $data = null) use ($clan, $members): void {
            if($data === null) return;
            
            $members = array_values($members);
            if(isset($members[$data])) {
                $member = $members[$data];
                $clan->removeMember($member);
                unset($this->clanManager->playerClans[$member]);
                $this->clanManager->saveData();
                
                $target = $this->plugin->getServer()->getPlayerExact($member);
                if ($target !== null) {
                    $target->sendMessage(TextFormat::RED . "Вы были исключены из клана " . $clan->getFormattedName());
                }
                
                $player->sendMessage(TextFormat::GREEN . "Игрок " . $member . " был исключен из клана");
            }
        });
        
        $form->setTitle("§l§6Исключение игрока");
        $form->setContent("Выберите игрока для исключения:");
        
        foreach ($members as $member) {
            $form->addButton($member);
        }
        
        $player->sendForm($form);
    }
}