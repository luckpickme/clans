<?php

namespace vexora\clans\types;

use pocketmine\utils\TextFormat;

enum ClanColor: int {
    case RED = 0;
    case GREEN = 1;
    case BLUE = 2;
    case ORANGE = 3;
    case GOLD = 4;
    case BLACK = 5;
    case WHITE = 6;

    public function getTextFormat(): string {
        return match($this) {
            self::RED => TextFormat::RED,
            self::GREEN => TextFormat::GREEN,
            self::BLUE => TextFormat::BLUE,
            self::ORANGE => TextFormat::GOLD,
            self::GOLD => TextFormat::YELLOW,
            self::BLACK => TextFormat::BLACK,
            self::WHITE => TextFormat::WHITE
        };
    }

    public function getName(): string {
        return match($this) {
            self::RED => "Красный",
            self::GREEN => "Зеленый",
            self::BLUE => "Синий",
            self::ORANGE => "Оранжевый",
            self::GOLD => "Золотой",
            self::BLACK => "Черный",
            self::WHITE => "Белый"
        };
    }
    
    public static function tryFromValue(int $value): ?self {
        return match($value) {
            0 => self::RED,
            1 => self::GREEN,
            2 => self::BLUE,
            3 => self::ORANGE,
            4 => self::GOLD,
            5 => self::BLACK,
            6 => self::WHITE,
            default => null
        };
    }
}