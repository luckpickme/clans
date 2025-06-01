<?php

namespace vexora\clans\types;

use pocketmine\player\Player;
use pocketmine\utils\Config;
use pocketmine\plugin\PluginBase;

class ClanManager {
    private array $clans = [];
    private array $playerClans = [];
    private Config $config;

    public function __construct(private PluginBase $plugin) {
        $this->config = new Config($plugin->getDataFolder() . "clans.yml", Config::YAML, [
            'clans' => [],
            'playerClans' => []
        ]);
        $this->loadData();
    }
    
    /**
     * Загрузка данных из файла
     */
    private function loadData(): void {
        $data = $this->config->getAll();
        $this->playerClans = $data['playerClans'] ?? [];
        
        $this->clans = [];
        foreach ($data['clans'] ?? [] as $name => $clanData) {
            if (is_array($clanData)) {
                try {
                    $this->clans[$name] = new Clan(
                        $name,
                        $clanData['color'] ?? 0,
                        $clanData['leader'] ?? '',
                        $clanData['members'] ?? [],
                        $clanData['stats'] ?? ['kills' => 0, 'exp' => 0, 'level' => 1]
                    );
                } catch (\Throwable $e) {
                    $this->plugin->getLogger()->error("Ошибка загрузки клана {$name}: " . $e->getMessage());
                }
            }
        }
    }
    
    /**
     * Сохранение данных в файл
     */
    public function saveData(): void {
        $clansData = [];
        foreach ($this->clans as $name => $clan) {
            $clansData[$name] = [
                'color' => $clan->getColor()->value,
                'leader' => $clan->getLeader(),
                'members' => $clan->getMembers(),
                'stats' => $clan->getStats()
            ];
        }
        
        $this->config->setAll([
            'clans' => $clansData,
            'playerClans' => $this->playerClans
        ]);
        $this->config->save();
    }
    
    /**
     * Создание нового клана
     */
    public function createClan(Player $leader, string $name, ClanColor $color): bool {
        if (isset($this->clans[$name])) {
            return false;
        }
        
        $this->clans[$name] = new Clan(
            $name,
            $color->value,
            $leader->getName(),
            [$leader->getName()],
            ['kills' => 0, 'exp' => 0, 'level' => 1]
        );
        
        $this->playerClans[$leader->getName()] = $name;
        $this->saveData();
        return true;
    }
    
    /**
     * Приглашение игрока в клан
     */
    public function invitePlayer(Player $inviter, Player $target): bool {
        $clan = $this->getPlayerClan($inviter->getName());
        if ($clan === null || $clan->getLeader() !== $inviter->getName()) {
            return false;
        }
        
        if ($this->getPlayerClan($target->getName()) !== null) {
            return false;
        }
        
        $target->sendMessage("§aВы получили приглашение в клан " . $clan->getFormattedName() . " от " . $inviter->getName());
        $target->sendMessage("§aЧтобы принять, введите §f/clans accept " . $clan->getName());
        return true;
    }
    
    /**
     * Принятие приглашения в клан
     */
    public function acceptInvite(Player $player, string $clanName): bool {
        if ($this->getPlayerClan($player->getName()) !== null) {
            return false;
        }
        
        $clan = $this->getClan($clanName);
        if ($clan === null) {
            return false;
        }
        
        $clan->addMember($player->getName());
        $this->playerClans[$player->getName()] = $clanName;
        $this->saveData();
        return true;
    }
    
    /**
     * Удаление клана
     */
    public function disbandClan(Player $leader): bool {
        $clan = $this->getPlayerClan($leader->getName());
        if ($clan === null || $clan->getLeader() !== $leader->getName()) {
            return false;
        }
        
        foreach ($clan->getMembers() as $member) {
            unset($this->playerClans[$member]);
        }
        
        unset($this->clans[$clan->getName()]);
        $this->saveData();
        return true;
    }
    
    /**
     * Получение клана по имени
     */
    public function getClan(string $name): ?Clan {
        return $this->clans[$name] ?? null;
    }
    
    /**
     * Получение клана игрока
     */
    public function getPlayerClan(string $playerName): ?Clan {
        $clanName = $this->playerClans[$playerName] ?? null;
        return $clanName ? $this->getClan($clanName) : null;
    }
    
    /**
     * Получение списка всех кланов
     */
    public function getAllClans(): array {
        return $this->clans;
    }
    
    /**
     * Выход из клана
     */
    public function leaveClan(Player $player): bool {
        $clan = $this->getPlayerClan($player->getName());
        if ($clan === null || $clan->getLeader() === $player->getName()) {
            return false;
        }
        
        $clan->removeMember($player->getName());
        unset($this->playerClans[$player->getName()]);
        $this->saveData();
        return true;
    }
}