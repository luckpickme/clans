<?php

namespace vexora\clans\types;

use pocketmine\utils\TextFormat;

class Clan {
    public function __construct(
        private string $name,
        private int $color,
        private string $leader,
        private array $members,
        private array $stats = ["kills" => 0, "exp" => 0, "level" => 1]
    ) {}

    public function getName(): string {
        return $this->name;
    }

    public function getColor(): ClanColor {
        return ClanColor::from($this->color);
    }

    public function getLeader(): string {
        return $this->leader;
    }

    public function getMembers(): array {
        return $this->members;
    }

    public function getStats(): array {
        return $this->stats;
    }

    public function addMember(string $playerName): void {
        if(!in_array($playerName, $this->members)) {
            $this->members[] = $playerName;
        }
    }

    public function removeMember(string $playerName): void {
        $this->members = array_diff($this->members, [$playerName]);
    }

    public function addKill(int $exp = 3): void {
        $this->stats["kills"]++;
        $this->stats["exp"] += $exp;
        $this->stats["level"] = floor($this->stats["exp"] / 100) + 1;
    }

    public function getFormattedName(): string {
        return $this->getColor()->getTextFormat() . $this->name;
    }
}