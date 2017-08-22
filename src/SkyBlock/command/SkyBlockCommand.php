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
        parent::__construct("skyblock", "Main SkyBlock command", "§a/skyblock (§eor /sb or /is or /island", ["sb", "is", "island"]);
    }
    public function sendMessage(Player $sender, $message) {
        $sender->sendMessage(TextFormat::AQUA . TextFormat::BOLD . "[" . TextFormat::GREEN . "§6Void§bSkyBlock§cPE" . TextFormat::AQUA . "] " . TextFormat::RESET . TextFormat::DARK_GREEN . $message);
    }
    public function execute(CommandSender $sender,string $commandLabel, array $args) {
        if($sender instanceof Player) {
            if(isset($args[0])) {
                switch($args[0]) {
                    case "go":
		    case "join":
		    case "home":
			if ($sender->hasPermission('sbpe.cmd.go') or $sender->hasPermission('sbpe')) {
                        $config = $this->plugin->getSkyBlockManager()->getPlayerConfig($sender);
                        if(empty($config->get("island"))) {
                            $this->sendMessage($sender, "§4[Error] §cYou Don't have an island! §6Do /is create to make one!");
			}
                        else {
                            $island = $this->plugin->getIslandManager()->getOnlineIsland($config->get("island"));
                            if($island instanceof Island) {
                                $island->addPlayer($sender);
                                $sender->teleport(new Position(15, 7, 10, $this->plugin->getServer()->getLevelByName($island->getIdentifier())));
                                $this->sendMessage($sender, "§aYou were teleported to your island home succesfully");
                            }
                            else {
                                $this->sendMessage($sender, "§4[Error] §cYou Don't have an island! §6Do /is create to make one!");
                          	  }
                      	     }
			}
                        break;
                    case "create":
		    case "make":
		    case "sethome":
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
                                        $this->sendMessage($sender, "§aYou successfully created a {$skyBlockManager->getGeneratorIslandName($args[1])} island and sethome to it!");
                                    }
                                    else {
                                        $this->sendMessage($sender, "§4[Error] §cThat isn't a valid SkyBlock generator!");
                                    }
                                }
                                else {
                                    $this->plugin->getSkyBlockManager()->generateIsland($sender, "basic");
                                    $this->sendMessage($sender, "§aYou successfully created a island! §7Please use: §e/is go §6to teleport to your new island.");
                                }
                            }
                        }
                        else {
                            $this->sendMessage($sender, "§4[Error] §cYou already got a skyblock island! §6Do /is go, to go to it.");
                       	 }
			}
                        break;
		    case "tphome":
				if ($sender->hasPermission('sbpe.cmd.tphome') or $sender->hasPermission('sbpe')) {
                        $config = $this->plugin->getSkyBlockManager()->getPlayerConfig($sender);
                        if(empty($config->get("island"))) {
                            $this->sendMessage($sender, "§4[Error] §cYou Don't have an island! §6Do /is create to make one!");
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
                                $this->sendMessage($sender, "§4[Error] §cYou Don't have an island! §6Do /is create to make one!");
                            }
                        }
				}
                        break;
		    case "addhome":
		    case "makehome":
				if ($sender->hasPermission('sbpe.cmd.sethome') or $sender->hasPermission('sbpe')) {
                        $config = $this->plugin->getSkyBlockManager()->getPlayerConfig($sender);
                        if(empty($config->get("island"))) {
                            $this->sendMessage($sender, "§4[Error] §cYou Don't have an island! §6Do /is create to make one!");
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
                                $this->sendMessage($sender, "§4[Error] §cYou Don't have an island! §6Do /is create to make one!");
                            }
                        }
				}
                        break;
                    case "kick":
                    case "expel":
				if ($sender->hasPermission('sbpe.cmd.kick') or $sender->hasPermission('sbpe')) {
                        $config = $this->plugin->getSkyBlockManager()->getPlayerConfig($sender);
                        if(empty($config->get("island"))) {
                            $this->sendMessage($sender, "§4[Error] §cYou Don't have an island! §6Do /is create to make one!");
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
                                            $this->sendMessage($sender, "§4[Error] §cThat isn't a valid player!");
                                        }
                                    }
                                    else {
                                        $this->sendMessage($sender, "§7Please use: /is kick §c<§7name§c> §7or /is expel §c<§7player>§c §6to kick a user from your island!");
                                    }
                                }
                                else {
                                    $this->sendMessage($sender, "§4[Error] §cYou must be the island owner to expel/kick anyone!");
                                }
                            }
                            else {
                                $this->sendMessage($sender, "§4[Error] §cYou Don't have an island! §6Do /is create to make one!");
                            }
                        }
				}
                        break;
                    case "lock":
		    case "togglelock":
				if ($sender->hasPermission('sbpe.cmd.lock') or $sender->hasPermission('sbpe')) {
                        $config = $this->plugin->getSkyBlockManager()->getPlayerConfig($sender);
                        if(empty($config->get("island"))) {
                            $this->sendMessage($sender, "§4[Error] §cYou Don't have an island! §6Do /is create to make one!");
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
                                $this->sendMessage($sender, "§4[Error] §cYou don't have a island! §6Do /is create to make one!");
                            }
                        }
				}
                        break;
                    case "invite":
		    case "ask":
				if ($sender->hasPermission('sbpe.cmd.invite') or $sender->hasPermission('sbpe')) {
                        $config = $this->plugin->getSkyBlockManager()->getPlayerConfig($sender);
                        if(empty($config->get("island"))) {
                            $this->sendMessage($sender, "§4[Error] §cYou Don't have an island! §6Do /is create to make one!");
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
                                                $this->sendMessage($player, "{$sender->getName()} §7invited you to his island! §7Do §e/is accept §7to accept their invite, or §e/is deny §7to deny their request! §6Their username is §e{$sender->getName()}");
                                            }
                                            else {
                                                $this->sendMessage($sender, "§4[Error] §cThis player is already in someone elses island! §6They have to leave their island in order to join yours!");
                                            }
                                        }
                                        else {
                                            $this->sendMessage($sender, "§4[Error] §c{$args[1]} isn't a valid player!");
                                        }
                                    }
                                    else {
                                        $this->sendMessage($sender, "§7Please use: §7/is invite §c<§7player§c> §7or /is ask §c<§7player§c> §6to invite them to your island!");
                                    }
                                }
                                else {
                                    $this->sendMessage($sender, "§4[Error] §cYou must be the island owner to do this!");
                                }
                            }
                            else {
                                $this->sendMessage($sender, "§4[Error] §cYou Don't have an island! §6Do /is create to make one!");
                            }
                        }
				}
                        break;
                    case "accept":
		    case "yes":
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
                                        $this->sendMessage($sender, "§4[Error] §cYou don't have an invitation from {$player->getName()}!");
                                    }
                                }
                                else {
                                    $this->sendMessage($sender, "§4[Error] §c{$args[1]} is not a valid player");
                                }
                            }
                            else {
                                $this->sendMessage($sender, "§4[Error] §cYou are already in a island! §6Leave it before joining another island!");
                            }
                        }
                        else {
                            $this->sendMessage($sender, "§7Please use: /is accept §c<§7player§c> §7or /is yes §c<§7player§c> §6to join their island!");
                        }
				}
                        break;
                    case "deny":
                    case "reject":
		    case "no":
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
                                            $this->sendMessage($sender, "§4[Error] §cYou don't have an invitation from {$player->getName()}!");
                                        }
                                    }
                                    else {
                                        $this->sendMessage($sender, "§4[Error] §cYou don't have an invitation from {$player->getName()}!");
                                    }
                                }
                                else {
                                    $this->sendMessage($sender, "§4[Error] §c{$args[1]} is not a valid player");
                                }
                            }
                            else {
                                $this->sendMessage($sender, "§4[Error] §cYou cannot reject a user if you're already in their island!");
                            }
                        }
                        else {
                            $this->sendMessage($sender, "§7Please use: /is deny §c<§7player§c> §7or /is reject §c<§7player§c> §7or /is no §c<§7player§c> §6to deny their request!");
                        }
				}
                        break;
                    case "members":
		    case "listmembers":
		    case "ourmembers":
				if ($sender->hasPermission('sbpe.cmd.members') or $sender->hasPermission('sbpe')) {
                        $config = $this->plugin->getSkyBlockManager()->getPlayerConfig($sender);
                        if(empty($config->get("island"))) {
                            $this->sendMessage($sender, "§4[Error] §cYou must be in a island to use this command!");
                        }
                        else {
                            $island = $this->plugin->getIslandManager()->getOnlineIsland($config->get("island"));
                            if($island instanceof Island) {
                                $this->sendMessage($sender, "____| {$island->getOwnerName()}'s §aMembers in your Island! |____");
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
		    case "disband":
		    case "del":
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
                                    $this->sendMessage($sender, "§4[Error] §cYou must be the owner to delete the island!");
                                }
                            }
                            else {
                                $this->sendMessage($sender, "§4[Error] §cYou must be in a island to delete it!");
                            }
                        }
				}
                        break;
                    case "makeleader":
		    case "newleader":
		    case "transferleader":
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
                                                $this->sendMessage($player, "§aYou are now the Ownership of your island, by §2{$sender->getName()}!");
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
                                        $this->sendMessage($sender, "§7Please use: /is makeleader §c<§7player§c> §7or /is newleader §c<§7player§c> §6to transfer ownership!");
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
		    case "quit":
				if ($sender->hasPermission('sbpe.cmd.leave') or $sender->hasPermission('sbpe')) {
                        $config = $this->plugin->getSkyBlockManager()->getPlayerConfig($sender);
                        if(empty($config->get("island"))) {
                            $this->sendMessage($sender, "§4[Error] §cYou must be in a island to leave it!");
                        }
                        else {
                            $island = $this->plugin->getIslandManager()->getOnlineIsland($config->get("island"));
                            if($island instanceof Island) {
                                if($island->getOwnerName() == strtolower($sender->getName())) {
                                    $this->sendMessage($sender, "§4[Error] §cYou cannot leave a island if you're the owner! §6Maybe you can try using /is disband to delete your island!");
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
		    case "takeaway":
				if ($sender->hasPermission('sbpe.cmd.remove') or $sender->hasPermission('sbpe')) {
                        $config = $this->plugin->getSkyBlockManager()->getPlayerConfig($sender);
                        if(empty($config->get("island"))) {
                            $this->sendMessage($sender, "§4[Error] §cYou must be in a island to remove a user!");
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
                                        $this->sendMessage($sender, "§7Please use: /is remove §c<§7player§c> §7or /is takeaway §c<§7player§c> §6to remove a user from your island!");
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
		    case "tpto":
				if ($sender->hasPermission('sbpe.command.tp') or $sender->hasPermission('sbpe')) {
                        if(isset($args[1])) {
                            $island = $this->plugin->getIslandManager()->getIslandByOwner($args[1]);
                            if($island instanceof Island) {
                                if($island->isLocked()) {
                                    $this->sendMessage($sender, "§4[Error] §cThis island is locked, you cannot join it! §6If you're the island owner, do /is togglelock to unlock it again.");
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
                            $this->sendMessage($sender, "§7Please use: /is tp §c<§7owner_name§c> §7or /is tpto §c<§7owner_name§c> §6to teleport to another user's island.");
                        }
				}
                        break;
                    case "reset":
		    case "clear":
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
                                        $this->sendMessage($sender, "§6You'll be able to reset your island again in §d{$minutes} §5minutes");
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
                                        $this->sendMessage($sender, "§aYou successfully reset the island to how it was originally! §6Want to make sure it's done properly? Do /is go to see!");
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
			case "changelog":
			case "clog":
				if ($sender->hasPermission('sbpe.cmd.changelog') or $sender->hasPermission('sbpe')) {
				$this->sendmessage($sender, "§aSkyblock §2v1.0.5 Changelog: §bAdded new aliases: /is addhelper, and /is addmember to /is invite. Changed command usages, bug fixes!");
				}
				break;
			case "version":
			case "ver":	
				if ($sender->hasPermission('sbpe.cmd.ver') or $sender->hasPermission('sbpe')) {
				$this->sendMessage($sender, "§aSkyblock plugin by Zeao. This is the Public release of v1.0.0. Enjoy. Version: §2v1.0.5 §cWant to see what's changed in this update? Do /is changelog!");
				}
				break;
                    case "help":
		    case "?":
				if ($sender->hasPermission('sbpe.cmd.help') or $sender->hasPermission('sbpe')) {
                        $commands = [
                            "§ehelp" => "§7Show skyblock command help page. §7(Aliases: §e/is ?§7)",
                            "§ecreate" => "§7Create a new island §7(Aliases: §e/is make, /is sethome§7)",
                            "§ego" => "§7Teleport you to your island. §7(Aliases: §e/is join, §e/is home§7)",
                            "§ekick" => "§7Kick someone from your island. (Aliases §e/is expel§7)",
                            "§elock" => "§7Lock/unlock your island, then nobody/everybody will be able to join. §7Aliases: §e/is togglelock§7)",
                            "§emembers" => "§7Show all members of your island. §7(Aliases: §e/is ourmembers or /is listmembers§7)",
                            "§etp" => "§7Teleport you to a island that isn't yours. §7(Aliases: §e/is tpto§7)",
                            "§einvite" => "§7Invite a player to be member of your island. §7(Aliases: §e/is ask§7)",
                            "§eaccept <player>" => "§7accept a player's island invitation. §7(Aliases: §e/is yes§7)",
			    "§edeny <player>" => "§7Deny a player's island invitation. §7(Aliases: §e/is no or /is reject§7)",
                            "§eleave" => "§7Leave your island. §7(Aliases: §e/is quit§7)",
                            "§edelete" => "§7Delete your island. §7(Aliases: §e/is disband, /is del§7)",
			    "§eremove" => "§7Remove a player from your island. §7(Aliases: §e/is takeaway§7)",
                            "§emakeleader" => "§7Transfer island ownership. §7(Aliases: §e/is newleader, /is transferleader§7)",
			    "§ereset" => "§7Reset's your island. §7(Aliases: §e/is clear§7)",
			    "§echangelog" => "§7Checks our latest change log. §7(Aliases: §e/is clog§7)",
			    "§5todolist" => "§7Checks the to-do list on this plugin. §c(COMING SOON)",
			    "§5aliases" => "§7Checks the command aliases for an easier view. §c(COMING SOON)",
			    "§5faq" => "§7The most asked questions regarding this plugin. §c(COMING SOON)",
			    "§5info" => "§7Checks plugin info, such as: where this plugin's being tested, etc. §c(COMING SOON)",
			    "§5howtoplay" => "§7Shows you how to play Skyblock. §c(COMING SOON)",
			    
							"§eversion" => "§7Gets Skyblock version. §7(Aliases: §e/is ver§7)"
                        ];
						$sender->sendMessage(TextFormat::DARK_GREEN . "§2§l>>>>>>" . TextFormat::BOLD . TextFormat::AQUA . " [" . TextFormat::GREEN . "§6Void§bSkyBlock§cPE §dHelp Page" . TextFormat::AQUA . "] " . TextFormat::RESET . TextFormat::DARK_GREEN . "§2§l<<<<<<"); 
                        foreach($commands as $command => $description) {
			
                            $sender->sendMessage(TextFormat::AQUA . "§e/" . TextFormat::GREEN . "§eis {$command}: " . TextFormat::RESET . TextFormat::DARK_GREEN . $description);
                        }
                        break;
				}
                    default:
                        $this->sendMessage($sender, "§cUnknown command. §7Please Use: §e/is help §6for a list of Skyblock commands!");
					
                        break;
                }
            }
            else {
                $this->sendMessage($sender, "§7Please use: §e/is help §6for a list of Skyblock commands!");
            }
        }
        else {
            $sender->sendMessage("Please run this command in game, not on console!");
        }
    }
}
