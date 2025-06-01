<?php

namespace vexora\clans\permissions;

use pocketmine\plugin\PluginBase;

final class ClanPermissions {

    public const USE = "clansystem.use";
    public const CREATE = "clansystem.create";
    public const INVITE = "clansystem.invite";
    public const KICK = "clansystem.kick";
    
    public static function registerAll(PluginBase $plugin): void {
        $permissions = [
            self::USE => [
                "description" => "Позволяет использовать команду /clans",
                "default" => true
            ],
            self::CREATE => [
                "description" => "Позволяет создавать кланы",
                "default" => true
            ],
            self::INVITE => [
                "description" => "Позволяет приглашать игроков в клан",
                "default" => "op"
            ],
            self::KICK => [
                "description" => "Позволяет исключать игроков из клана",
                "default" => "op"
            ]
        ];
        
        foreach($permissions as $name => $data) {
            $plugin->getServer()->getPluginManager()->addPermission(new \pocketmine\permission\Permission($name, $data["description"], $data["default"]));
        }
    }
}