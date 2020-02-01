<?php
// by DRAGOVN !!!
namespace hachkingtohach1;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\PlayerActionPacket;
 
class Main extends PluginBase implements Listener {
    
	/**
	 * Permissions:   hacker.hack  // This is permission for hackers.
	**/
	
	public const ARRAY_MAX_SIZE = 100;
	
	public $ticksanti = [];
	
    public $clicksData = [];
	
	public $title = "[FireDog]";
	
	public function onEnable() {
		
		@mkdir($this->getDataFolder());
		$this->saveDefaultConfig();
		$this->getResource("config.yml");
		
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		
		$this->getLogger()->info(TextFormat::GREEN . "is Enable!");
	}

	public function onDisable() {
		
		$this->getLogger()->info(TextFormat::RED . "is Disable!");
	}
	
	public function initPlayerClickDatagetCpsAntiHack(Player $p) : void {
		
        $this->clicksDatagetCpsAntiHack[$p->getLowerCaseName()] = [];
    }
	
	public function addClickgetCpsAntiHack(Player $p) : void {
		
        array_unshift($this->clicksDatagetCpsAntiHack[$p->getLowerCaseName()], microtime(true));
		
        if(count($this->clicksDatagetCpsAntiHack[$p->getLowerCaseName()]) >= self::ARRAY_MAX_SIZE){
			
            array_pop($this->clicksDatagetCpsAntiHack[$p->getLowerCaseName()]);
        }
    }
	
	public function getCpsAntiHack(Player $player, float $deltaTime = 1.0, int $roundPrecision = 1) : float {
        
		if(!isset($this->clicksDatagetCpsAntiHack[$player->getLowerCaseName()]) || empty($this->clicksDatagetCpsAntiHack[$player->getLowerCaseName()])) return 0.0;
		
        $ct = microtime(true);
		
        return round(count(array_filter($this->clicksDatagetCpsAntiHack[$player->getLowerCaseName()], static function(float $t) use ($deltaTime, $ct) : bool{return ($ct - $t) <= $deltaTime;})) / $deltaTime, $roundPrecision);
    
	}
	
    public function removePlayerClickDatagetCpsAntiHack(Player $player) : void {
		
        unset($this->clicksDatagetCpsAntiHack[$player->getLowerCaseName()]);
    
	}
	
    public function playerJoinAntiHack(PlayerJoinEvent $event) : void {
		
        $this->initPlayerClickDatagetCpsAntiHack($event->getPlayer());
    
	}
	
    public function playerQuitAntiHack(PlayerQuitEvent $event) : void {
		
        $this->removePlayerClickDatagetCpsAntiHack($event->getPlayer());
    
	}
	
    public function packetReceivegetCpsAntiHack(DataPacketReceiveEvent $event) : void{
		
        if($event->getPacket()::NETWORK_ID === InventoryTransactionPacket::NETWORK_ID && $event->getPacket()->transactionType === InventoryTransactionPacket::TYPE_USE_ITEM_ON_ENTITY){
            
			$this->addClickgetCpsAntiHack($event->getPlayer());
        }
		elseif($event->getPacket()::NETWORK_ID === PlayerActionPacket::NETWORK_ID && $event->getPacket()->action === PlayerActionPacket::ACTION_START_BREAK){
            
			$this->addClickgetCpsAntiHack($event->getPlayer());
        }
    }
	
	// Have been programmed: 10:08 PM 1/24/2020
	################################AntiCheat#################################
    //------------------------------------------------------------------------	
	public function onDamageAntiCheat(EntityDamageEvent $event) {
        $entity = $event->getEntity();
        if ($event instanceof EntityDamageByEntityEvent and $entity instanceof Player) {
            $damager = $event->getDamager();
			
			if(!$damager instanceof Player) return;
			
			if(!$entity instanceof Player) return;
			
            if($damager instanceof Player) {
				
				for ($i = (int)$this->getConfig()->get("cps_hacking"); $i >= 100; $i++) {
				    if($this->getCpsAntiHack($damager) >= $i) {
					    $entity->sendMessage($this->title . $this->getConfig()->get("message_hacking"));
					    $event->setCancelled(true);
					}
				}
				
				if(!$damager->hasPermission('hacker.hack')) { // Don't change it!
                    if ($damager->distance($entity) > 3.9) {						
                        $event->setCancelled(true);
					}
				}
			}
		}
	}
	//-------------------------------------------------------------------------
	public function PlayerMoveAntiCheat(PlayerMoveEvent $event) {  
        $player = $event->getPlayer();
        $beneath = $event->getPlayer()->getLevel()->getBlock($event->getPlayer()->floor()->subtract(0, 7));
		
		if($beneath->getId() === 0) {
			
	        if(!$player->hasPermission('hacker.hack')){ // Don't change it!
			    if ($player->getGamemode() === Player::SPECTATOR) {
				    return;
				}
				
			    if ($player->getGamemode() === Player::SURVIVAL){
				    if($player->getLevel()->getFolderName() == $this->getServer()->getDefaultLevel()->getFolderName()) {
						if($this->getConfig()->get("permssion_for_server") == "true" ) {
				            if($player->hasPermission($this->getConfig()->get("permssion_for_server"))) {
					            return;
						    }
						}
					}
					$player->setFlying(false);
				    $player->setAllowFlight(false);				    
				    return;
				}
			    if ($player->getGamemode() === Player::CREATIVE) {
			        $player->setGamemode(0);
					$player->setFlying(false);
				    $player->setAllowFlight(false);
					
				}
			    if ($player->getGamemode() === Player::ADVENTURE) {
                    if($player->getLevel()->getFolderName() == $this->getServer()->getDefaultLevel()->getFolderName()) {
						if($this->getConfig()->get("permssion_for_server") == "true" ) {
						    if($player->hasPermission($this->getConfig()->get("permssion_for_server"))) {
					            return;
						    }
						}
					}                    					
					$player->setFlying(false);
				    $player->setAllowFlight(false);
					
				}
			}
		}
	}
	//------------------------------------------------------------------------
    ################################AntiCheat#################################		
}
