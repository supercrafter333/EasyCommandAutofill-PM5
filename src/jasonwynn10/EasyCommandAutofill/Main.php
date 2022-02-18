<?php
declare(strict_types=1);
namespace jasonwynn10\EasyCommandAutofill;

use pocketmine\command\Command;
use pocketmine\event\EventPriority;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\lang\Translatable;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\network\mcpe\protocol\types\command\CommandEnum;
use pocketmine\network\mcpe\protocol\types\command\CommandEnumConstraint;
use pocketmine\network\mcpe\protocol\types\command\CommandParameter;
use pocketmine\network\mcpe\protocol\UpdateSoftEnumPacket;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase{
	/** @var CommandData[] $manualOverrides */
	protected $manualOverrides = [];
	/** @var string[] $debugCommands */
	protected $debugCommands = [];
	/** @var CommandEnum[] $hardcodedEnums */
	protected $hardcodedEnums = [];
	/** @var CommandEnum[] $softEnums */
	protected $softEnums = [];
	/** @var CommandEnumConstraint[] $enumConstraints */
	protected $enumConstraints = [];

	public function onEnable() : void {
		$this->getServer()->getPluginManager()->registerEvent(DataPacketSendEvent::class, \Closure::fromCallable([$this, "onDataPacketSend"]), EventPriority::HIGHEST, $this, false);

		if($this->getConfig()->get("Highlight-Debug", true))
			$this->debugCommands = ["dumpmemory", "gc", "timings", "status"];
		$map = $this->getServer()->getCommandMap();
		$language = $this->getServer()->getLanguage();

		$commandName = 'pocketmine:ban';
		$command = $map->getCommand($commandName);
		$name = $command->getName();
		$aliases = $command->getAliases();
		$description = $command->getDescription();
		$description = $description instanceof Translatable ? $language->translate($description) : $description;
		$this->addManualOverride($commandName, $this->generateGenericCommandData($name, $aliases, $description, '/ban <player: target> [reason: string]'));

		$commandName = 'pocketmine:ban-ip';
		$command = $map->getCommand($commandName);
		$name = $command->getName();
		$aliases = $command->getAliases();
		$description = $command->getDescription();
		$description = $description instanceof Translatable ? $language->translate($description) : $description;
		$this->addManualOverride($commandName, $this->generateGenericCommandData($name, $aliases, $description, '/ban-ip <player: target> [reason: string] OR /ban-ip <address: string> [reason: string]'));

		$commandName = 'pocketmine:banlist';
		$command = $map->getCommand($commandName);
		$name = $command->getName();
		$aliases = $command->getAliases();
		$description = $command->getDescription();
		$description = $description instanceof Translatable ? $language->translate($description) : $description;
		$this->addManualOverride($commandName, $this->generateGenericCommandData($name, $aliases, $description, '/ban <player: target> [reason: string]'));

		$commandName = 'pocketmine:clear';
		$command = $map->getCommand($commandName);
		$name = $command->getName();
		$aliases = $command->getAliases();
		$description = $command->getDescription();
		$description = $description instanceof Translatable ? $language->translate($description) : $description;
		$this->addManualOverride($commandName, $this->generateGenericCommandData($name, $aliases, $description, '/clear [player: target]'));

		$commandName = 'pocketmine:defaultgamemode';
		$command = $map->getCommand($commandName);
		$name = $command->getName();
		$aliases = $command->getAliases();
		$description = $command->getDescription();
		$description = $description instanceof Translatable ? $language->translate($description) : $description;
		$this->addManualOverride($commandName, $this->generateGenericCommandData($name, $aliases, $description, '/defaultgamemode <survival|creative|adventure|spectator>'));

		$commandName = 'pocketmine:deop';
		$command = $map->getCommand($commandName);
		$name = $command->getName();
		$aliases = $command->getAliases();
		$description = $command->getDescription();
		$description = $description instanceof Translatable ? $language->translate($description) : $description;
		$this->addManualOverride($commandName, $this->generateGenericCommandData($name, $aliases, $description, '/deop <player: target>'));

		$commandName = 'pocketmine:difficulty';
		$command = $map->getCommand($commandName);
		$name = $command->getName();
		$aliases = $command->getAliases();
		$description = $command->getDescription();
		$description = $description instanceof Translatable ? $language->translate($description) : $description;
		$this->addManualOverride($commandName, $this->generateGenericCommandData($name, $aliases, $description, '/difficulty <peaceful|easy|normal|hard>'));

		$commandName = 'pocketmine:dumpmemory';
		$command = $map->getCommand($commandName);
		$name = $command->getName();
		$aliases = $command->getAliases();
		$description = $command->getDescription();
		$description = $description instanceof Translatable ? $language->translate($description) : $description;
		$this->addManualOverride($commandName, $this->generateGenericCommandData($name, $aliases, $description, '/dumpmemory'));

		$commandName = 'pocketmine:effect';
		$command = $map->getCommand($commandName);
		$name = $command->getName();
		$aliases = $command->getAliases();
		$description = $command->getDescription();
		$description = $description instanceof Translatable ? $language->translate($description) : $description;
		$this->addManualOverride($commandName, $this->generateGenericCommandData($name, $aliases, $description, '/effect <player: target> <effect: string> [duration: int] [amplifier: int] [hideParticles: bool]'));

		$commandName = 'pocketmine:enchant';
		$command = $map->getCommand($commandName);
		$name = $command->getName();
		$aliases = $command->getAliases();
		$description = $command->getDescription();
		$description = $description instanceof Translatable ? $language->translate($description) : $description;
		$this->addManualOverride($commandName, $this->generateGenericCommandData($name, $aliases, $description, '/enchant <player: target> <enchantment: string> [level: int]'));

		$commandName = 'pocketmine:gamemode';
		$command = $map->getCommand($commandName);
		$name = $command->getName();
		$aliases = $command->getAliases();
		$description = $command->getDescription();
		$description = $description instanceof Translatable ? $language->translate($description) : $description;
		$this->addManualOverride($commandName, $this->generateGenericCommandData($name, $aliases, $description, '/gamemode <survival|creative|adventure|spectator> [player: target]'));

		$commandName = 'pocketmine:gc';
		$command = $map->getCommand($commandName);
		$name = $command->getName();
		$aliases = $command->getAliases();
		$description = $command->getDescription();
		$description = $description instanceof Translatable ? $language->translate($description) : $description;
		$this->addManualOverride($commandName, $this->generateGenericCommandData($name, $aliases, $description, '/gc'));

		$commandName = 'pocketmine:give';
		$command = $map->getCommand($commandName);
		$name = $command->getName();
		$aliases = $command->getAliases();
		$description = $command->getDescription();
		$description = $description instanceof Translatable ? $language->translate($description) : $description;
		$this->addManualOverride($commandName, $this->generateGenericCommandData($name, $aliases, $description, '/give <player: target> <item: string> [amount: int] [data: json]'));

		$commandName = 'pocketmine:kick';
		$command = $map->getCommand($commandName);
		$name = $command->getName();
		$aliases = $command->getAliases();
		$description = $command->getDescription();
		$description = $description instanceof Translatable ? $language->translate($description) : $description;
		$this->addManualOverride($commandName, $this->generateGenericCommandData($name, $aliases, $description, '/kick <player: target> [reason: string]'));

		$commandName = 'pocketmine:kill';
		$command = $map->getCommand($commandName);
		$name = $command->getName();
		$aliases = $command->getAliases();
		$description = $command->getDescription();
		$description = $description instanceof Translatable ? $language->translate($description) : $description;
		$this->addManualOverride($commandName, $this->generateGenericCommandData($name, $aliases, $description, '/kill <player: target>'));

		$commandName = 'pocketmine:list';
		$command = $map->getCommand($commandName);
		$name = $command->getName();
		$aliases = $command->getAliases();
		$description = $command->getDescription();
		$description = $description instanceof Translatable ? $language->translate($description) : $description;
		$this->addManualOverride($commandName, $this->generateGenericCommandData($name, $aliases, $description, '/list'));

		$commandName = 'pocketmine:me';
		$command = $map->getCommand($commandName);
		$name = $command->getName();
		$aliases = $command->getAliases();
		$description = $command->getDescription();
		$description = $description instanceof Translatable ? $language->translate($description) : $description;
		$this->addManualOverride($commandName, $this->generateGenericCommandData($name, $aliases, $description, '/me <message: string>'));

		$commandName = 'pocketmine:op';
		$command = $map->getCommand($commandName);
		$name = $command->getName();
		$aliases = $command->getAliases();
		$description = $command->getDescription();
		$description = $description instanceof Translatable ? $language->translate($description) : $description;
		$this->addManualOverride($commandName, $this->generateGenericCommandData($name, $aliases, $description, '/op <player: target>'));

		$commandName = 'pocketmine:pardon';
		$command = $map->getCommand($commandName);
		$name = $command->getName();
		$aliases = $command->getAliases();
		$description = $command->getDescription();
		$description = $description instanceof Translatable ? $language->translate($description) : $description;
		$this->addManualOverride($commandName, $this->generateGenericCommandData($name, $aliases, $description, '/pardon <player: target>'));

		$commandName = 'pocketmine:pardon-ip';
		$command = $map->getCommand($commandName);
		$name = $command->getName();
		$aliases = $command->getAliases();
		$description = $command->getDescription();
		$description = $description instanceof Translatable ? $language->translate($description) : $description;
		$this->addManualOverride($commandName, $this->generateGenericCommandData($name, $aliases, $description, '/pardon-ip <player: target> OR /pardon-ip <address: string>'));

		$commandName = 'pocketmine:particle';
		$command = $map->getCommand($commandName);
		$name = $command->getName();
		$aliases = $command->getAliases();
		$description = $command->getDescription();
		$description = $description instanceof Translatable ? $language->translate($description) : $description;
		$this->addManualOverride($commandName, $this->generateGenericCommandData($name, $aliases, $description, '/particle <player: target> <particle: string> <position: x y z> <relative: x y z> [count: int] [data: int]'));

		$commandName = 'pocketmine:plugins';
		$command = $map->getCommand($commandName);
		$name = $command->getName();
		$aliases = $command->getAliases();
		$description = $command->getDescription();
		$description = $description instanceof Translatable ? $language->translate($description) : $description;
		$this->addManualOverride($commandName, $this->generateGenericCommandData($name, $aliases, $description, '/plugins'));

		$commandName = 'pocketmine:save-all';
		$command = $map->getCommand($commandName);
		$name = $command->getName();
		$aliases = $command->getAliases();
		$description = $command->getDescription();
		$description = $description instanceof Translatable ? $language->translate($description) : $description;
		$this->addManualOverride($commandName, $this->generateGenericCommandData($name, $aliases, $description, '/save-all'));

		$commandName = 'pocketmine:save-off';
		$command = $map->getCommand($commandName);
		$name = $command->getName();
		$aliases = $command->getAliases();
		$description = $command->getDescription();
		$description = $description instanceof Translatable ? $language->translate($description) : $description;
		$this->addManualOverride($commandName, $this->generateGenericCommandData($name, $aliases, $description, '/save-off'));

		$commandName = 'pocketmine:save-on';
		$command = $map->getCommand($commandName);
		$name = $command->getName();
		$aliases = $command->getAliases();
		$description = $command->getDescription();
		$description = $description instanceof Translatable ? $language->translate($description) : $description;
		$this->addManualOverride($commandName, $this->generateGenericCommandData($name, $aliases, $description, '/save-on'));

		$commandName = 'pocketmine:say';
		$command = $map->getCommand($commandName);
		$name = $command->getName();
		$aliases = $command->getAliases();
		$description = $command->getDescription();
		$description = $description instanceof Translatable ? $language->translate($description) : $description;
		$this->addManualOverride($commandName, $this->generateGenericCommandData($name, $aliases, $description, '/say <message: string>'));

		$commandName = 'pocketmine:seed';
		$command = $map->getCommand($commandName);
		$name = $command->getName();
		$aliases = $command->getAliases();
		$description = $command->getDescription();
		$description = $description instanceof Translatable ? $language->translate($description) : $description;
		$this->addManualOverride($commandName, $this->generateGenericCommandData($name, $aliases, $description, '/seed'));

		$commandName = 'pocketmine:setworldspawn';
		$command = $map->getCommand($commandName);
		$name = $command->getName();
		$aliases = $command->getAliases();
		$description = $command->getDescription();
		$description = $description instanceof Translatable ? $language->translate($description) : $description;
		$this->addManualOverride($commandName, $this->generateGenericCommandData($name, $aliases, $description, '/setworldspawn [position: x y z]'));

		$commandName = 'pocketmine:spawnpoint';
		$command = $map->getCommand($commandName);
		$name = $command->getName();
		$aliases = $command->getAliases();
		$description = $command->getDescription();
		$description = $description instanceof Translatable ? $language->translate($description) : $description;
		$this->addManualOverride($commandName, $this->generateGenericCommandData($name, $aliases, $description, '/spawnpoint [player: target] [position: x y z]'));

		$commandName = 'pocketmine:status';
		$command = $map->getCommand($commandName);
		$name = $command->getName();
		$aliases = $command->getAliases();
		$description = $command->getDescription();
		$description = $description instanceof Translatable ? $language->translate($description) : $description;
		$this->addManualOverride($commandName, $this->generateGenericCommandData($name, $aliases, $description, '/status'));

		$commandName = 'pocketmine:stop';
		$command = $map->getCommand($commandName);
		$name = $command->getName();
		$aliases = $command->getAliases();
		$description = $command->getDescription();
		$description = $description instanceof Translatable ? $language->translate($description) : $description;
		$this->addManualOverride($commandName, $this->generateGenericCommandData($name, $aliases, $description, '/stop'));

		$commandName = 'pocketmine:tell';
		$command = $map->getCommand($commandName);
		$name = $command->getName();
		$aliases = $command->getAliases();
		$description = $command->getDescription();
		$description = $description instanceof Translatable ? $language->translate($description) : $description;
		$this->addManualOverride($commandName, $this->generateGenericCommandData($name, $aliases, $description, '/tell <player: target> <message: string>'));

		$commandName = 'pocketmine:time';
		$command = $map->getCommand($commandName);
		$name = $command->getName();
		$aliases = $command->getAliases();
		$description = $command->getDescription();
		$description = $description instanceof Translatable ? $language->translate($description) : $description;
		$this->addManualOverride($commandName, $this->generateGenericCommandData($name, $aliases, $description, '/time <set|add|query> <time: int>'));

		$commandName = 'pocketmine:timings';
		$command = $map->getCommand($commandName);
		$name = $command->getName();
		$aliases = $command->getAliases();
		$description = $command->getDescription();
		$description = $description instanceof Translatable ? $language->translate($description) : $description;
		$this->addManualOverride($commandName, $this->generateGenericCommandData($name, $aliases, $description, '/timings <on|off|paste|reset|report>'));

		$commandName = 'pocketmine:title';
		$command = $map->getCommand($commandName);
		$name = $command->getName();
		$aliases = $command->getAliases();
		$description = $command->getDescription();
		$description = $description instanceof Translatable ? $language->translate($description) : $description;
		$this->addManualOverride($commandName, $this->generateGenericCommandData($name, $aliases, $description, '/title <player: target> <title: string> [subtitle: string] [time: int]'));

		$commandName = 'pocketmine:tp';
		$command = $map->getCommand($commandName);
		$name = $command->getName();
		$aliases = $command->getAliases();
		$description = $command->getDescription();
		$description = $description instanceof Translatable ? $language->translate($description) : $description;
		$this->addManualOverride($commandName, $this->generateGenericCommandData($name, $aliases, $description, '/tp <player: target> [position: x y z]'));

		$commandName = 'pocketmine:transferserver';
		$command = $map->getCommand($commandName);
		$name = $command->getName();
		$aliases = $command->getAliases();
		$description = $command->getDescription();
		$description = $description instanceof Translatable ? $language->translate($description) : $description;
		$this->addManualOverride($commandName, $this->generateGenericCommandData($name, $aliases, $description, '/transferserver <address: string> [port: int]'));

		$commandName = 'pocketmine:version';
		$command = $map->getCommand($commandName);
		$name = $command->getName();
		$aliases = $command->getAliases();
		$description = $command->getDescription();
		$description = $description instanceof Translatable ? $language->translate($description) : $description;
		$this->addManualOverride($commandName, $this->generateGenericCommandData($name, $aliases, $description, '/version [plugin: string]'));

		$commandName = 'pocketmine:whitelist';
		$command = $map->getCommand($commandName);
		$name = $command->getName();
		$aliases = $command->getAliases();
		$description = $command->getDescription();
		$description = $description instanceof Translatable ? $language->translate($description) : $description;
		$this->addManualOverride($commandName, $this->generateGenericCommandData($name, $aliases, $description, '/whitelist <add|remove|on|off|list|reload> [player: target]'));
	}

	public function onDataPacketSend(DataPacketSendEvent $event) : void {
		$packets = $event->getPackets();
		foreach($packets as $pk) {
			if(!$pk instanceof AvailableCommandsPacket)
				return;
			foreach($event->getTargets() as $networkSession) {
				$player = $networkSession->getPlayer();
				$pk->commandData = [];
				foreach($this->getServer()->getCommandMap()->getCommands() as $command) {
					if(isset($pk->commandData[$command->getName()]) or $command->getName() === "help" or !$command->testPermissionSilent($player))
						continue;

					$pk->commandData[$command->getName()] = $this->generatePlayerSpecificCommandData($command, $networkSession->getPlayer());
				}
			}
			$pk->softEnums = $this->getSoftEnums();
			$pk->hardcodedEnums = $this->getHardcodedEnums();
			$pk->enumConstraints = $this->getEnumConstraints();
		}
	}

	public function generatePlayerSpecificCommandData(Command $command, Player $player) : CommandData {
		$language = $player->getLanguage();

		$name = $command->getName();
		$aliases = $command->getAliases();
		$description = $command->getDescription();
		$description = $description instanceof Translatable ? $language->translate($description) : $description;
		$usage = $command->getUsage();
		$usage = $usage instanceof Translatable ? $language->translate($usage) : $usage;
		$hasPermission = $command->testPermissionSilent($player);

		$filteredData = array_filter(
			$this->getManualOverrides(),
			fn(CommandData $data) => $name === $data->name
		);
		foreach($filteredData as $data) {
			$data->description = $description;
			$data->permission = (int)!$hasPermission;
			if(!$data->aliases instanceof CommandEnum) {
				$data->aliases = $this->generateAliasEnum($name, $aliases);
			}
			//$player->sendMessage($name.' is a manual override');
			return $data; // yes I know this in a loop, ill deal with this logic later
		}

		return $this->generateGenericCommandData($name, $aliases, $description, $usage, $hasPermission);
	}

	public function generateGenericCommandData(string $name, array $aliases, string $description, string $usage, bool $hasPermission = false) : CommandData {
		$hasPermission = (int)!$hasPermission;

		if($usage === '' or $usage[0] === '%') {
			//$player->sendMessage($name.' is a generated default');
			$data = $this->generatePocketMineDefaultCommandData($name, $aliases, $description);
			$data->permission = $hasPermission;
			return $data;
		}

		$usages = explode(" OR ", $usage); // split command trees
		$overloads = [];
		$enumCount = 0;
		for($tree = 0; $tree < count($usages); ++$tree) {
			$usage = $usages[$tree];
			$overloads[$tree] = [];
			$commandString = explode(" ", $usage)[0];
			preg_match_all('/(\s?[<\[]?\s*)([a-zA-Z0-9|\/]+)\s*:?\s*(string|int|x y z|float|mixed|target|message|text|json|command|boolean|bool|player)?\s*[>\]]?\s?/iu', $usage, $matches, PREG_PATTERN_ORDER, strlen($commandString));
			$argumentCount = count($matches[0])-1;
			for($argNumber = 0; $argumentCount >= 0 and $argNumber <= $argumentCount; ++$argNumber){
				if(!isset($matches[1][$argNumber]) or $matches[1][$argNumber] === " "){
					$this->addHardcodedEnum($enum = new CommandEnum(strtolower($matches[2][$argNumber]), [strtolower($matches[2][$argNumber])]), false);
					$overloads[$tree][$argNumber] = CommandParameter::enum(strtolower($matches[2][$argNumber]), $enum, CommandParameter::FLAG_FORCE_COLLAPSE_ENUM, false);
					continue;
				}
				$optional = str_contains($matches[1][$argNumber], '[');
				$paramName = strtolower($matches[2][$argNumber]);
				if(!str_contains($paramName, "|") and !str_contains($paramName, "/")){
					if(!isset($matches[3][$argNumber]) and $this->getConfig()->get("Parse-with-Parameter-Names", true) === true){
						if(str_contains($paramName, "player") or str_contains($paramName, "target")){
							$paramType = AvailableCommandsPacket::ARG_TYPE_TARGET;
						}elseif(str_contains($paramName, "count")){
							$paramType = AvailableCommandsPacket::ARG_TYPE_INT;
						}elseif(str_contains($paramName, "block")){
							$paramType = AvailableCommandsPacket::ARG_TYPE_INT; // TODO: change to block names enum
						}else{
							$paramType = AvailableCommandsPacket::ARG_TYPE_RAWTEXT;
						}
					}else{
						$paramType = match (strtolower($matches[3][$argNumber])) {
							"string" => AvailableCommandsPacket::ARG_TYPE_STRING,
							"int" => AvailableCommandsPacket::ARG_TYPE_INT,
							"x y z" => AvailableCommandsPacket::ARG_TYPE_POSITION,
							"float" => AvailableCommandsPacket::ARG_TYPE_FLOAT,
							"player", "target" => AvailableCommandsPacket::ARG_TYPE_TARGET,
							"message" => AvailableCommandsPacket::ARG_TYPE_MESSAGE,
							"json" => AvailableCommandsPacket::ARG_TYPE_JSON,
							"command" => AvailableCommandsPacket::ARG_TYPE_COMMAND,
							"boolean", "bool", "mixed" => AvailableCommandsPacket::ARG_TYPE_VALUE,
							default => AvailableCommandsPacket::ARG_TYPE_RAWTEXT,
						};
					}
					$overloads[$tree][$argNumber] = CommandParameter::standard($paramName, $paramType, 0, $optional);
				}elseif(str_contains($paramName, "|")){
					++$enumCount;
					$enumValues = explode("|", $paramName);
					$this->addSoftEnum($enum = new CommandEnum($name . " Enum#" . $enumCount, $enumValues), false);
					$overloads[$tree][$argNumber] = CommandParameter::enum($paramName, $enum, CommandParameter::FLAG_FORCE_COLLAPSE_ENUM, $optional);
				}elseif(str_contains($paramName, "/")){
					++$enumCount;
					$enumValues = explode("/", $paramName);
					$this->addSoftEnum($enum = new CommandEnum($name . " Enum#" . $enumCount, $enumValues), false);
					$overloads[$tree][$argNumber] = CommandParameter::enum($paramName, $enum, CommandParameter::FLAG_FORCE_COLLAPSE_ENUM, $optional);
				}
			}
		}
		//$player->sendMessage($name.' is a fully generated command');
		return new CommandData(strtolower($name), $description, (int) in_array($name, $this->getDebugCommands()), $hasPermission, $this->generateAliasEnum($name, $aliases), $overloads);
	}

	private function generateAliasEnum(string $name, array $aliases) : ?CommandEnum {
		if(count($aliases) > 0){
			if(!in_array($name, $aliases, true)){
				//work around a client bug which makes the original name not show when aliases are used
				$aliases[] = $name;
			}
			return new CommandEnum(ucfirst($name) . "Aliases", $aliases);
		}
		return null;
	}

	private function generatePocketMineDefaultCommandData(string $name, array $aliases, string $description) : CommandData {
		return new CommandData(
			strtolower($name),
			$description,
			0,
			1,
			$this->generateAliasEnum($name, $aliases),
			[
				[
					CommandParameter::standard("args", AvailableCommandsPacket::ARG_TYPE_RAWTEXT, 0, true)
				]
			]
		);
	}

	public function addManualOverride(string $commandName, CommandData $data, bool $sendPacket = true) : self {
		$this->manualOverrides[$commandName] = $data;
		if(!$sendPacket)
			return $this;
		foreach($this->getServer()->getOnlinePlayers() as $player) {
			$player->getNetworkSession()->sendDataPacket(new AvailableCommandsPacket());
		}
		return $this;
	}

	/**
	 * @return CommandData[]
	 */
	public function getManualOverrides() : array {
		return $this->manualOverrides;
	}

	public function addDebugCommand(string $commandName, bool $sendPacket = true) : self {
		$this->debugCommands[] = $commandName;
		if(!$sendPacket)
			return $this;
		foreach($this->getServer()->getOnlinePlayers() as $player) {
			$player->getNetworkSession()->sendDataPacket(new AvailableCommandsPacket());
		}
		return $this;
	}

	/**
	 * @return string[]
	 */
	public function getDebugCommands() : array {
		return $this->debugCommands;
	}

	public function addHardcodedEnum(CommandEnum $enum, bool $sendPacket = true) : self {
		foreach($this->softEnums as $softEnum)
			if($enum->getName() === $softEnum->getName())
				throw new \InvalidArgumentException("Hardcoded enum is already in soft enum list.");
		$this->hardcodedEnums[$enum->getName()] = $enum;
		if(!$sendPacket)
			return $this;
		foreach($this->getServer()->getOnlinePlayers() as $player) {
			$player->getNetworkSession()->sendDataPacket(new AvailableCommandsPacket());
		}
		return $this;
	}

	/**
	 * @return CommandEnum[]
	 */
	public function getHardcodedEnums() : array {
		return $this->hardcodedEnums;
	}

	public function addSoftEnum(CommandEnum $enum, bool $sendPacket = true) : self {
		foreach($this->hardcodedEnums as $hardcodedEnum)
			if($enum->getName() === $hardcodedEnum->getName())
				throw new \InvalidArgumentException("Soft enum is already in hardcoded enum list.");
		$this->softEnums[$enum->getName()] = $enum;
		if(!$sendPacket)
			return $this;
		$pk = new UpdateSoftEnumPacket();
		$pk->enumName = $enum->getName();
		$pk->values = $enum->getValues();
		$pk->type = UpdateSoftEnumPacket::TYPE_ADD;
		foreach($this->getServer()->getOnlinePlayers() as $player) {
			$player->getNetworkSession()->sendDataPacket($pk);
		}
		return $this;
	}

	/**
	 * @return CommandEnum[]
	 */
	public function getSoftEnums() : array {
		return $this->softEnums;
	}

	public function updateSoftEnum(CommandEnum $enum, bool $sendPacket = true) : self {
		foreach($this->hardcodedEnums as $hardcodedEnum)
			if($enum->getName() === $hardcodedEnum->getName())
				throw new \InvalidArgumentException("Soft enum is already in hardcoded enum list.");
		$this->softEnums[$enum->getName()] = $enum;
		if(!$sendPacket)
			return $this;
		$pk = new UpdateSoftEnumPacket();
		$pk->enumName = $enum->getName();
		$pk->values = $enum->getValues();
		$pk->type = UpdateSoftEnumPacket::TYPE_SET;
		foreach($this->getServer()->getOnlinePlayers() as $player) {
			$player->getNetworkSession()->sendDataPacket($pk);
		}
		return $this;
	}

	public function removeSoftEnum(CommandEnum $enum, bool $sendPacket = true) : self {
		unset($this->softEnums[$enum->getName()]);
		if(!$sendPacket)
			return $this;
		$pk = new UpdateSoftEnumPacket();
		$pk->enumName = $enum->getName();
		$pk->values = $enum->getValues();
		$pk->type = UpdateSoftEnumPacket::TYPE_REMOVE;
		foreach($this->getServer()->getOnlinePlayers() as $player) {
			$player->getNetworkSession()->sendDataPacket($pk);
		}
		return $this;
	}

	public function addEnumConstraint(CommandEnumConstraint $enumConstraint) : self {
		foreach($this->hardcodedEnums as $hardcodedEnum)
			if($enumConstraint->getEnum()->getName() === $hardcodedEnum->getName()) {
				$this->enumConstraints[] = $enumConstraint;
				foreach($this->getServer()->getOnlinePlayers() as $player) {
					$player->getNetworkSession()->sendDataPacket(new AvailableCommandsPacket());
				}
				return $this;
			}
		foreach($this->softEnums as $softEnum)
			if($enumConstraint->getEnum()->getName() === $softEnum->getName()) {
				$this->enumConstraints[] = $enumConstraint;
				foreach($this->getServer()->getOnlinePlayers() as $player) {
					$player->getNetworkSession()->sendDataPacket(new AvailableCommandsPacket());
				}
				return $this;
			}
		throw new \InvalidArgumentException("Enum name does not exist in any Enum list");
	}

	/**
	 * @return CommandEnumConstraint[]
	 */
	public function getEnumConstraints() : array {
		return $this->enumConstraints;
	}
}