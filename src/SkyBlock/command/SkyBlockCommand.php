<?php
namespace SkyBlock\command;
use SkyBlock\Utils;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use SkyBlock\invitation\Invitation;
use SkyBlock\island\Island;
use SkyBlock\Main;
use SkyBlock\reset\Reset;
class SkyBlockCommand extends Command {
    /** @var Main */
    private $plugin;
    /**
     * SkyBlockCommand constructor.
     *
     * @param Main $plugin
     */
    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
        parent::__construct("skyblock", "Main SkyBlock command", "§cUsage: /skyblock", ["sb", "is", "island"]);
    }
    public function sendMessage(Player $sender, $message) {
        $sender->sendMessage(TextFormat::AQUA . TextFormat::BOLD . "[" . TextFormat::GREEN . "SkyBlock" . TextFormat::AQUA . "] " . TextFormat::RESET . TextFormat::DARK_GREEN . $message);
    }
    public function execute(CommandSender $sender, $commandLabel, array $args) {
        if($sender instanceof Player) {
            if(isset($args[0])) {
                switch($args[0]) {
                    case "go":
			if ($sender->hasPermission('sbpe.cmd.go') or $sender->hasPermission('sbpe')) {
                        $config = $this->plugin->getSkyBlockManager()->getPlayerConfig($sender);
                        if(empty($config->get("island"))) {
                            $this->sendMessage($sender, "§4[Error] §cYou Don't have an island!");
			}
                        else {
                            $island = $this->plugin->getIslandManager()->getOnlineIsland($config->get("island"));
                            if($island instanceof Island) {
                                $island->addPlayer($sender);
                                $sender->teleport(new Position(15, 7, 10, $this->plugin->getServer()->getLevelByName($island->getIdentifier())));
                                $this->sendMessage($sender, "§aYou were teleported to your island home succesfully");
                            }
                            else {
                                $this->sendMessage($sender, "§4[Error] §cYou Don't have an island!!");
                          	  }
                      	     }
			}
                        break;
                    case "create":
			if ($sender->hasPermission('sbpe.cmd.create') or $sender->hasPermission('sbpe')) {
                        $config = $this->plugin->getSkyBlockManager()->getPlayerConfig($sender);
                        if(empty($config->get("island"))) {
                            $reset = $this->plugin->getResetHandler()->getResetTimer($sender);
                            if($reset instanceof Reset) {
                                $minutes = Utils::printSeconds($reset->getTime());
                                $this->sendMessage($sender, "§5You'll be able to create a new island in §4{$minutes} §cminutes");
                            }
                            else {
                                $skyBlockManager = $this->plugin->getSkyBlockGeneratorManager();
                                if(isset($args[1])) {
                                    if($skyBlockManager->isGenerator($args[1])) {
                                        $this->plugin->getSkyBlockManager()->generateIsland($sender, $args[1]);
                                        $this->sendMessage($sender, "§aYou successfully created a {$skyBlockManager->getGeneratorIslandName($args[1])} island!");
                                    }
                                    else {
                                        $this->sendMessage($sender, "§4[Error] §cThat isn't a valid SkyBlock generator!");
                                    }
                                }
                                else {
                                    $this->plugin->getSkyBlockManager()->generateIsland($sender, "basic");
                                    $this->sendMessage($sender, "§aYou successfully created a island! §bDo §5/is go §ato go on your island!");
                                }
                            }
                        }
                        else {
                            $this->sendMessage($sender, "§4[Error] §cYou already got a skyblock island!");
                       	 }
			}
                        break;
                    case "home":
				if ($sender->hasPermission('sbpe.cmd.home') or $sender->hasPermission('sbpe')) {
                        $config = $this->plugin->getSkyBlockManager()->getPlayerConfig($sender);
                        if(empty($config->get("island"))) {
                            $this->sendMessage($sender, "§4[Error] §cYou Don't have an island");
                        }
                        else {
                            $island = $this->plugin->getIslandManager()->getOnlineIsland($config->get("island"));
                            if($island instanceof Island) {
                                $home = $island->getHomePosition();
                                if($home instanceof Position) {
                                    $sender->teleport($home);
                                    $this->sendMessage($sender, "§aYou have been teleported to your island home succesfully");
                                }
                                else {
                                    $this->sendMessage($sender, "§4[Error] §cYour island hasn't got a home position set!");
                                }
                            }
                            else {
                                $this->sendMessage($sender, "§4[Error] §cYou Don't have an island!");
                            }
                        }
				}
                        break;
                    case "sethome":
				if ($sender->hasPermission('sbpe.cmd.sethome') or $sender->hasPermission('sbpe')) {
                        $config = $this->plugin->getSkyBlockManager()->getPlayerConfig($sender);
                        if(empty($config->get("island"))) {
                            $this->sendMessage($sender, "§4[Error] §cYou Don't have an island");
                        }
                        else {
                            $island = $this->plugin->getIslandManager()->getOnlineIsland($config->get("island"));
                            if($island instanceof Island) {
                                if($island->getOwnerName() == strtolower($sender->getName())) {
                                    if($sender->getLevel()->getName() == $config->get("island")) {
                                        $island->setHomePosition($sender->getPosition());
                                        $this->sendMessage($sender, "§aYou have set your island home successfully!");
                                    }
                                    else {
                                        $this->sendMessage($sender, "§4[Error] §cYou must be in your island to set home!");
                                    }
                                }
                                else {
                                    $this->sendMessage($sender, "§4[Error] §cYou must be the island leader to do this!");
                                }
                            }
                            else {
                                $this->sendMessage($sender, "§4[Error] §cYou Don't have an island");
                            }
                        }
				}
                        break;
                    case "kick":
                    case "expel":
				if ($sender->hasPermission('sbpe.cmd.kick') or $sender->hasPermission('sbpe')) {
                        $config = $this->plugin->getSkyBlockManager()->getPlayerConfig($sender);
                        if(empty($config->get("island"))) {
                            $this->sendMessage($sender, "§4[Error] §cYou Don't have an island");
                        }
                        else {
                            $island = $this->plugin->getIslandManager()->getOnlineIsland($config->get("island"));
                            if($island instanceof Island) {
                                if($island->getOwnerName() == strtolower($sender->getName())) {
                                    if(isset($args[1])) {
                                        $player = $this->plugin->getServer()->getPlayer($args[1]);
                                        if($player instanceof Player and $player->isOnline()) {
                                            if($player->getLevel()->getName() == $island->getIdentifier()) {
                                                $player->teleport($this->plugin->getServer()->getDefaultLevel()->getSafeSpawn());
                                                $this->sendMessage($sender, "{$player->getName()} §chas been kicked from your island!");
                                            }
                                            else {
                                                $this->sendMessage($sender, "§4[Error] §cThe player isn't in your island!");
                                            }
                                        }
                                        else {
                                            $this->sendMessage($sender, "§4[Error] §cThat isn't a valid player");
                                        }
                                    }
                                    else {
                                        $this->sendMessage($sender, "§cUsage: /is kick <player>");
                                    }
                                }
                                else {
                                    $this->sendMessage($sender, "§4[Error] §cYou must be the island owner to expel/kick anyone");
                                }
                            }
                            else {
                                $this->sendMessage($sender, "§4[Error] §cYou Don't have an island");
                            }
                        }
				}
                        break;
                    case "lock":
				if ($sender->hasPermission('sbpe.cmd.lock') or $sender->hasPermission('sbpe')) {
                        $config = $this->plugin->getSkyBlockManager()->getPlayerConfig($sender);
                        if(empty($config->get("island"))) {
                            $this->sendMessage($sender, "§4[Error] §cYou Don't have an island");
                        }
                        else {
                            $island = $this->plugin->getIslandManager()->getOnlineIsland($config->get("island"));
                            if($island instanceof Island) {
                                if($island->getOwnerName() == strtolower($sender->getName())) {
                                    $island->setLocked(!$island->isLocked());
                                    $locked = ($island->isLocked()) ? "locked" : "unlocked";
                                    $this->sendMessage($sender, "§aYour island has been {$locked}!");
                                }
                                else {
                                    $this->sendMessage($sender, "§4[Error] §cYou must be the island owner to do this!");
                                }
                            }
                            else {
                                $this->sendMessage($sender, "§4[Error] §cYou haven't got a island!");
                            }
                        }
				}
                        break;
                    case "invite":
				if ($sender->hasPermission('sbpe.cmd.invite') or $sender->hasPermission('sbpe')) {
                        $config = $this->plugin->getSkyBlockManager()->getPlayerConfig($sender);
                        if(empty($config->get("island"))) {
                            $this->sendMessage($sender, "§4[Error] §cYou Don't have an island");
                        }
                        else {
                            $island = $this->plugin->getIslandManager()->getOnlineIsland($config->get("island"));
                            if($island instanceof Island) {
                                if($island->getOwnerName() == strtolower($sender->getName())) {
                                    if(isset($args[1])) {
                                        $player = $this->plugin->getServer()->getPlayer($args[1]);
                                        if($player instanceof Player and $player->isOnline()) {
                                            $config = $this->plugin->getSkyBlockManager()->getPlayerConfig($player);
                                            if(empty($config->get("island"))) {
                                                $this->plugin->getInvitationHandler()->addInvitation($sender, $player, $island);
                                                $this->sendMessage($sender, "§aYou sent a invitation to §2{$player->getName()} §asuccesfully!");
                                                $this->sendMessage($player, "{$sender->getName()} §ainvited you to his island! §2Do /is accept §ato accept their invite, or §2/is deny §ato deny their request. {$sender->getName()}");
                                            }
                                            else {
                                                $this->sendMessage($sender, "§4[Error] §cThis player is already in a island!");
                                            }
                                        }
                                        else {
                                            $this->sendMessage($sender, "§4[Error] §c{$args[1]} isn't a valid player!");
                                        }
                                    }
                                    else {
                                        $this->sendMessage($sender, "§cUsage: /is invite <player>");
                                    }
                                }
                                else {
                                    $this->sendMessage($sender, "§4[Error] §cYou must be the island owner to do this!");
                                }
                            }
                            else {
                                $this->sendMessage($sender, "§4[Error] §cYou Don't have an island");
                            }
                        }
				}
                        break;
                    case "accept":
				if ($sender->hasPermission('sbpe.cmd.invite.accept') or $sender->hasPermission('sbpe')) {
                        if(isset($args[1])) {
                            $config = $this->plugin->getSkyBlockManager()->getPlayerConfig($sender);
                            if(empty($config->get("island"))) {
                                $player = $this->plugin->getServer()->getPlayer($args[1]);
                                if($player instanceof Player and $player->isOnline()) {
                                    $invitation = $this->plugin->getInvitationHandler()->getInvitation($player);
                                    if($invitation instanceof Invitation) {
                                        if($invitation->getSender() == $player) {
                                            $invitation->accept();
                                        }
                                        else {
                                            $this->sendMessage($sender, "§4[Error] §cYou don't have an invitation from {$player->getName()}!");
                                        }
                                    }
                                    else {
                                        $this->sendMessage($sender, "§4[Error] §cYou don't have an invitation from {$player->getName()}");
                                    }
                                }
                                else {
                                    $this->sendMessage($sender, "§4[Error] §c{$args[1]} is not a valid player");
                                }
                            }
                            else {
                                $this->sendMessage($sender, "§4[Error] §cYou cannot be in a island if you want join another island!");
                            }
                        }
                        else {
                            $this->sendMessage($sender, "§cUsage: /is accept <player>");
                        }
				}
                        break;
                    case "deny":
                    case "reject":
				if ($sender->hasPermission('sbpe.cmd.invite.deny') or $sender->hasPermission('sbpe')) {
                        if(isset($args[1])) {
                            $config = $this->plugin->getSkyBlockManager()->getPlayerConfig($sender);
                            if(empty($config->get("island"))) {
                                $player = $this->plugin->getServer()->getPlayer($args[1]);
                                if($player instanceof Player and $player->isOnline()) {
                                    $invitation = $this->plugin->getInvitationHandler()->getInvitation($player);
                                    if($invitation instanceof Invitation) {
                                        if($invitation->getSender() == $player) {
                                            $invitation->deny();
                                        }
                                        else {
                                            $this->sendMessage($sender, "§4[Error] §cYou haven't got a invitation from {$player->getName()}!");
                                        }
                                    }
                                    else {
                                        $this->sendMessage($sender, "§4[Error] §cYou haven't got a invitation from {$player->getName()}");
                                    }
                                }
                                else {
                                    $this->sendMessage($sender, "§4[Error] §c{$args[1]} is not a valid player");
                                }
                            }
                            else {
                                $this->sendMessage($sender, "§4[Error] §cYou cannot be in a island if you want reject another island!");
                            }
                        }
                        else {
                            $this->sendMessage($sender, "§cUsage: /is deny <player>");
                        }
				}
                        break;
                    case "members":
				if ($sender->hasPermission('sbpe.cmd.members') or $sender->hasPermission('sbpe')) {
                        $config = $this->plugin->getSkyBlockManager()->getPlayerConfig($sender);
                        if(empty($config->get("island"))) {
                            $this->sendMessage($sender, "§4[Error] §cYou must be in a island to use this command!");
                        }
                        else {
                            $island = $this->plugin->getIslandManager()->getOnlineIsland($config->get("island"));
                            if($island instanceof Island) {
                                $this->sendMessage($sender, "____| {$island->getOwnerName()}'s §aIsland Members |____");
                                $i = 1;
                                foreach($island->getAllMembers() as $member) {
                                    $this->sendMessage($sender, "{$i}. {$member}");
                                    $i++;
                                }
                            }
                            else {
                                $this->sendMessage($sender, "§4[Error] §cYou must be in a island to use this command!");
                            }
                        }
				}
                        break;
                    case "delete":
				if ($sender->hasPermission('sbpe.cmd.delete') or $sender->hasPermission('sbpe')) {
                        $config = $this->plugin->getSkyBlockManager()->getPlayerConfig($sender);
                        if(empty($config->get("island"))) {
                            $this->sendMessage($sender, "§4[Error] §cYou must be in a island to delete it!");
                        }
                        else {
                            $island = $this->plugin->getIslandManager()->getOnlineIsland($config->get("island"));
                            if($island instanceof Island) {
                                if($island->getOwnerName() == strtolower($sender->getName())) {
                                    foreach($island->getAllMembers() as $member) {
                                        $memberConfig = new Config($this->plugin->getDataFolder() . "users" . DIRECTORY_SEPARATOR . $member . ".json", Config::JSON);
                                        $memberConfig->set("island", "");
                                        $memberConfig->save();
                                    }
                                    $this->plugin->getIslandManager()->removeIsland($island);
                                    $this->plugin->getResetHandler()->addResetTimer($sender);
                                    $this->sendMessage($sender, "§aYou successfully deleted the island!");
                                }
                                else {
                                    $this->sendMessage($sender, "§4[Error] §cYou must be the owner to disband the island!");
                                }
                            }
                            else {
                                $this->sendMessage($sender, "§4[Error] §cYou must be in a island to disband it!");
                            }
                        }
				}
                        break;
                    case "makeleader":
		    case "newleader":
				if ($sender->hasPermission('sbpe.cmd.makeleader') or $sender->hasPermission('sbpe')) {
                        $config = $this->plugin->getSkyBlockManager()->getPlayerConfig($sender);
                        if(empty($config->get("island"))) {
                            $this->sendMessage($sender, "§4[Error] §cYou must be in a island to set a new leader!");
                        }
                        else {
                            $island = $this->plugin->getIslandManager()->getOnlineIsland($config->get("island"));
                            if($island instanceof Island) {
                                if($island->getOwnerName() == strtolower($sender->getName())) {
                                    if(isset($args[1])) {
                                        $player = $this->plugin->getServer()->getPlayer($args[1]);
                                        if($player instanceof Player and $player->isOnline()) {
                                            $playerConfig = $this->plugin->getSkyBlockManager()->getPlayerConfig($player);
                                            $playerIsland = $this->plugin->getIslandManager()->getOnlineIsland($playerConfig->get("island"));
                                            if($island == $playerIsland) {
                                                $island->setOwnerName($player);
                                                $island->addPlayer($player);
                                                $this->sendMessage($sender, "§aYou have set the ownership to §2{$player->getName()} §asuccesfully!");
                                                $this->sendMessage($player, "§aYou are now the Ownership of your island, by §2{$sender->getName()}");
                                            }
                                            else {
                                                $this->sendMessage($sender, "§4[Error] §cThe player should be on your island!");
                                            }
                                        }
                                        else {
                                            $this->sendMessage($sender, "§4[Error] §c{$args[1]} isn't a valid player!");
                                        }
                                    }
                                    else {
                                        $this->sendMessage($sender, "§cUsage: /is makeleader <player>");
                                    }
                                }
                                else {
                                    $this->sendMessage($sender, "§4[Error] §cYou must be the island leader to do this!");
                                }
                            }
                            else {
                                $this->sendMessage($sender, "§4[Error] §cYou must be in a island to set a new leader!");
                            }
                        }
				}
                        break;
                    case "leave":
				if ($sender->hasPermission('sbpe.cmd.leave') or $sender->hasPermission('sbpe')) {
                        $config = $this->plugin->getSkyBlockManager()->getPlayerConfig($sender);
                        if(empty($config->get("island"))) {
                            $this->sendMessage($sender, "§4[Error] §cYou must be in a island to leave it!");
                        }
                        else {
                            $island = $this->plugin->getIslandManager()->getOnlineIsland($config->get("island"));
                            if($island instanceof Island) {
                                if($island->getOwnerName() == strtolower($sender->getName())) {
                                    $this->sendMessage($sender, "§4[Error] §cYou cannot leave a island if you're the owner! Maybe you can try using /is disband");
                                }
                                else {
                                    $this->plugin->getChatHandler()->removePlayerFromChat($sender);
                                    $config->set("island", "");
                                    $config->save();
                                    $island->removeMember(strtolower($sender->getName()));
                                    $this->sendMessage($sender, "§aYou left the island succesfully!");
                                }
                            }
                            else {
                                $this->sendMessage($sender, "§4[Error] §cYou must be in a island to leave it!");
                            }
                        }
				}
                        break;
                    case "remove":
				if ($sender->hasPermission('sbpe.cmd.remove') or $sender->hasPermission('sbpe')) {
                        $config = $this->plugin->getSkyBlockManager()->getPlayerConfig($sender);
                        if(empty($config->get("island"))) {
                            $this->sendMessage($sender, "§4[Error] §cYou must be in a island to leave it!");
                        }
                        else {
                            $island = $this->plugin->getIslandManager()->getOnlineIsland($config->get("island"));
                            if($island instanceof Island) {
                                if($island->getOwnerName() == strtolower($sender->getName())) {
                                    if(isset($args[1])) {
                                        if(in_array(strtolower($args[1]), $island->getMembers())) {
                                            $island->removeMember(strtolower($args[1]));
                                            $player = $this->plugin->getServer()->getPlayerExact($args[1]);
                                            if($player instanceof Player and $player->isOnline()) {
                                                $this->plugin->getChatHandler()->removePlayerFromChat($player);
                                            }
                                            $this->sendMessage($sender, "§2{$args[1]} §awas removed from your team/island succesfully!");
                                        }
                                        else {
                                            $this->sendMessage($sender, "§4[Error] §c{$args[1]} isn't a player of your island!");
                                        }
                                    }
                                    else {
                                        $this->sendMessage($sender, "§cUsage: /is remove <player>");
                                    }
                                }
                                else {
                                    $this->sendMessage($sender, "§4[Error] §cYou must be the island owner to do this!");
                                }
                            }
                            else {
                                $this->sendMessage($sender, "§4[Error] §cYou must be in a island to leave it!");
                            }
                        }
				}
                        break;					
						case "tp":
				if ($sender->hasPermission('sbpe.cmd.tp') or $sender->hasPermission('sbpe')) {
                        if(isset($args[1])) {
                            $island = $this->plugin->getIslandManager()->getIslandByOwner($args[1]);
                            if($island instanceof Island) {
                                if($island->isLocked()) {
                                    $this->sendMessage($sender, "§4[Error] §cThis island is locked, you cannot join it!");
                                }
                                else {
                                    $sender->teleport(new Position(15, 7, 10, $this->plugin->getServer()->getLevelByName($island->getIdentifier())));
                                    $this->sendMessage($sender, "§aYou joined the island successfully");
                                }
                            }
                            else {
                                $this->sendMessage($sender, "§4[Error] §cAt least one island member must be active if you want to see the island!");
                            }
                        }
                        else {
                            $this->sendMessage($sender, "§cUsage: /is tp <owner_name>");
                        }
				}
                        break;
                    case "reset":
				if ($sender->hasPermission('sbpe.cmd.reset') or $sender->hasPermission('sbpe')) {
                        $config = $this->plugin->getSkyBlockManager()->getPlayerConfig($sender);
                        if(empty($config->get("island"))) {
                            $this->sendMessage($sender, "§4[Error] §cYou must be in a island to reset it!");
                        }
                        else {
                            $island = $this->plugin->getIslandManager()->getOnlineIsland($config->get("island"));
                            if($island instanceof Island) {
                                if($island->getOwnerName() == strtolower($sender->getName())) {
                                    $reset = $this->plugin->getResetHandler()->getResetTimer($sender);
                                    if($reset instanceof Reset) {
                                        $minutes = Utils::printSeconds($reset->getTime());
                                        $this->sendMessage($sender, "§5You'll be able to reset your island again in §d{$minutes} §5minutes");
                                    }
                                    else {
                                        foreach($island->getAllMembers() as $member) {
                                            $memberConfig = new Config($this->plugin->getDataFolder() . "users" . DIRECTORY_SEPARATOR . $member . ".json", Config::JSON);
                                            $memberConfig->set("island", "");
                                            $memberConfig->save();
                                        }
                                        $generator = $island->getGenerator();
                                        $this->plugin->getIslandManager()->removeIsland($island);
                                        $this->plugin->getResetHandler()->addResetTimer($sender);
                                        $this->plugin->getSkyBlockManager()->generateIsland($sender, $generator);
                                        $this->sendMessage($sender, "§aYou successfully reset the island!");
                                    }
                                }
                                else {
                                    $this->sendMessage($sender, "§4[Error] §cYou must be the owner to reset the island!");
                                }
                            }
                            else {
                                $this->sendMessage($sender, "§4[Error] §cYou must be in a island to reset it!");
                            }
                        }
				}
                        break;
			case "version":
			case "ver":	
				if ($sender->hasPermission('sbpe.cmd.ver') or $sender->hasPermission('sbpe')) {
				$this->sendMessage($sender, "§caTest plugin by Zeao. This branch is a testing branch. where bugs will be expected and major issues may occur. Version: §2v1.0.0-BETA-1");
				}
				break;
                    case "help":
				if ($sender->hasPermission('sbpe.cmd.home') or $sender->hasPermission('sbpe')) {
                        $commands = [
                            "§ehelp" => "§7Show skyblock command info",
                            "§ecreate" => "§7Create a new island",
                            "§ego" => "§7Teleport you to your island",
                            "§ekick" => "§7Kick someone from your island",
                            "§elock" => "75Lock/unlock your island, then nobody/everybody will be able to join",
                            "§esethome" => "§7Set your island home",
                            "§ehome" => "§7Teleport you to your island home",
                            "§emembers" => "§7Show all members of your island",
                            "§etp <ownerName>" => "§7Teleport you to a island that isn't yours",
                            "§einvite" => "§7Invite a player to be member of your island",
                            "§eaccept/deny <player>" => "§7accept/deny's an invitation",
                            "§eleave" => "§7Leave your island",
                            "§edelete" => "§7Delete your island",
			    "§eremove" => "§7Remove a player from your island",
                            "§emakeleader" => "§7Transfer island ownership",
			    "edreset" => "§7Reset's your island.",
			    
							"§eversion" => "§7Gets Skyblock version."
                        ];
						$sender->sendMessage(TextFormat::DARK_GREEN . "-----------" . TextFormat::BOLD . TextFormat::AQUA . " [" . TextFormat::GREEN . "SkyBlockPE Help" . TextFormat::AQUA . "] " . TextFormat::RESET . TextFormat::DARK_GREEN . "-----------"); 
                        foreach($commands as $command => $description) {
			
                            $sender->sendMessage(TextFormat::AQUA . "/" . TextFormat::GREEN . "is {$command}: " . TextFormat::RESET . TextFormat::DARK_GREEN . $description);
                        }
                        break;
				}
                    default:
                        $this->sendMessage($sender, "§cUnknown command. §dUse §5/is help §dfor a list of skyblock commands.");
					
                        break;
                }
            }
            else {
                $this->sendMessage($sender, "§7Use §e/is help §7for a list of §eskyblock commands.");
            }
        }
        else {
            $sender->sendMessage("Please run this command in game, not on console.");
        }
    }
}
