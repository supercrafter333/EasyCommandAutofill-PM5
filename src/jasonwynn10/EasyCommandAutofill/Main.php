<?php
declare(strict_types=1);
namespace jasonwynn10\EasyCommandAutofill;

use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\network\mcpe\protocol\types\CommandData;
use pocketmine\network\mcpe\protocol\types\CommandEnum;
use pocketmine\network\mcpe\protocol\types\CommandParameter;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;

class Main extends PluginBase implements Listener {
	/** @var self $instance */
	private static $instance;
	/** @var CommandData[] $manualOverrides */
	protected $manualOverrides = [];
	/** @var string[] $debugCommands */
	protected $debugCommands = [];

	/**
	 * @return self
	 */
	public static function getInstance() : self {
		return self::$instance;
	}

	public function onLoad() {
		self::$instance = $this;
	}

	public function onEnable() {
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->debugCommands = ["dumpmemory", "gc", "timings", "status"];
	}

	/**
	 * @param string $commandName
	 * @param CommandData $data
	 *
	 * @return self
	 */
	public function addManualOverride(string $commandName, CommandData $data) : self {
		$this->manualOverrides[$commandName] = $data;
		return $this;
	}

	/**
	 * @return CommandData[]
	 */
	public function getManualOverrides() : array {
		return $this->manualOverrides;
	}

	/**
	 * @param string $debugCommands
	 *
	 * @return self
	 */
	public function addDebugCommand(string $debugCommands) : self {
		$this->debugCommands[] = $debugCommands;
		return $this;
	}

	/**
	 * @return string[]
	 */
	public function getDebugCommands() : array {
		return $this->debugCommands;
	}

	/**
	 * @param DataPacketSendEvent $event
	 */
	public function onDataPacketSend(DataPacketSendEvent $event) {
		$pk = $event->getPacket();
		if($pk instanceof AvailableCommandsPacket) {
			foreach($this->getServer()->getCommandMap()->getCommands() as $name => $command) {
				if(isset($pk->commandData[$command->getName()]) or $command->getName() === "help")
					continue;
				if(in_array($command->getName(), array_keys(Main::getInstance()->getManualOverrides()))) {
					$pk->commandData[$command->getName()] = Main::getInstance()->getManualOverrides()[$name];
					continue;
				}
				$usage = $this->getServer()->getLanguage()->translateString($command->getUsage());
				if(empty($usage) or $usage[0] === '%') {
					$data = new CommandData();
					//TODO: commands containing uppercase letters in the name crash 1.9.0 client
					$data->commandName = strtolower($command->getName());
					$data->commandDescription = $this->getServer()->getLanguage()->translateString($command->getDescription());
					$data->flags = (int)in_array($command->getName(), Main::getInstance()->getDebugCommands());
					$data->permission = 0;

					$parameter = new CommandParameter();
					$parameter->paramName = "args";
					$parameter->paramType = AvailableCommandsPacket::ARG_FLAG_VALID | AvailableCommandsPacket::ARG_TYPE_RAWTEXT;
					$parameter->isOptional = true;
					$data->overloads[0][0] = $parameter;

					$aliases = $command->getAliases();
					if(count($aliases) > 0){
						if(!in_array($data->commandName, $aliases, true)) {
							//work around a client bug which makes the original name not show when aliases are used
							$aliases[] = $data->commandName;
						}
						$data->aliases = new CommandEnum();
						$data->aliases->enumName = ucfirst($command->getName()) . "Aliases";
						$data->aliases->enumValues = $aliases;
					}
					$pk->commandData[$command->getName()] = $data;
					continue;
				}
				$usages = explode(" OR ", $usage); // split command trees
				$enumCount = 0;
				for($tree = 0; $tree < count($usages); ++$tree) {
					$usage = $usages[$tree];
					var_dump($usage);
					$commandString = explode(" ", $usage)[0];
					preg_match_all('/(\s*[<\[]\s*)((?:[a-zA-Z0-9]+)((\s*:?\s*)(string|int|x y z|float|mixed|target|message|text|json|command|boolean))|([a-zA-Z0-9]+(\|[a-zA-Z0-9]+)?)+)(\s*[>\]]\s*)/ius', $usage, $matches, PREG_PATTERN_ORDER, strlen($commandString));
					$argumentCount = count($matches[0])-1;
					if($argumentCount < 0 and $command->testPermissionSilent($event->getPlayer())) {
						$data = new CommandData();
						//TODO: commands containing uppercase letters in the name crash 1.9.0 client
						$data->commandName = strtolower($command->getName());
						$data->commandDescription = $this->getServer()->getLanguage()->translateString($command->getDescription());
						$data->flags = (int)in_array($command->getName(), Main::getInstance()->getDebugCommands());
						$data->permission = 0;

						$aliases = $command->getAliases();
						if(count($aliases) > 0){
							if(!in_array($data->commandName, $aliases, true)){
								//work around a client bug which makes the original name not show when aliases are used
								$aliases[] = $data->commandName;
							}
							$data->aliases = new CommandEnum();
							$data->aliases->enumName = ucfirst($command->getName()) . "Aliases";
							$data->aliases->enumValues = $aliases;
						}
						$pk->commandData[$command->getName()] = $data;
						continue;
					}
					$data = new CommandData();
					//TODO: commands containing uppercase letters in the name crash 1.9.0 client
					$data->commandName = strtolower($command->getName());
					$data->commandDescription = Server::getInstance()->getLanguage()->translateString($command->getDescription());
					$data->flags = (int)in_array($command->getName(), Main::getInstance()->getDebugCommands()); // make command autofill blue if debug
					$data->permission = (int)$command->testPermissionSilent($event->getPlayer()); // hide commands players do not have permission to use
					for($argNumber = 0; $argNumber <= $argumentCount; ++$argNumber) {
						if(!empty($matches[1][$argNumber])) {
							$optional = $matches[1][$argNumber] === '[';
						}else{
							$optional = false;
						}
						$paramName = strtolower($matches[2][$argNumber]);
						if(strpos($paramName, "|") === false) {
							if(Main::getInstance()->getConfig()->get("Parse-with-Parameter-Names", true)) {
								if(strpos($paramName, "player") !== false or strpos($paramName, "target") !== false) {
									$paramType = AvailableCommandsPacket::ARG_FLAG_VALID | AvailableCommandsPacket::ARG_TYPE_TARGET;
								}elseif(strpos($paramName, "count") !== false) {
									$paramType = AvailableCommandsPacket::ARG_FLAG_VALID | AvailableCommandsPacket::ARG_TYPE_INT;
								}
							}
							if(!isset($paramType)){
								$fieldType = strtolower($matches[4][$argNumber]);
								switch($fieldType) {
									case "string":
										$paramType = AvailableCommandsPacket::ARG_FLAG_VALID | AvailableCommandsPacket::ARG_TYPE_STRING;
									break;
									case "int":
										$paramType = AvailableCommandsPacket::ARG_FLAG_VALID | AvailableCommandsPacket::ARG_TYPE_INT;
									break;
									case "x y z":
										$paramType = AvailableCommandsPacket::ARG_FLAG_VALID | AvailableCommandsPacket::ARG_TYPE_POSITION;
									break;
									case "float":
										$paramType = AvailableCommandsPacket::ARG_FLAG_VALID | AvailableCommandsPacket::ARG_TYPE_FLOAT;
									break;
									case "target":
										$paramType = AvailableCommandsPacket::ARG_FLAG_VALID | AvailableCommandsPacket::ARG_TYPE_TARGET;
									break;
									case "message":
										$paramType = AvailableCommandsPacket::ARG_FLAG_VALID | AvailableCommandsPacket::ARG_TYPE_MESSAGE;
									break;
									case "json":
										$paramType = AvailableCommandsPacket::ARG_FLAG_VALID | AvailableCommandsPacket::ARG_TYPE_JSON;
									break;
									case "command":
										$paramType = AvailableCommandsPacket::ARG_FLAG_VALID | AvailableCommandsPacket::ARG_TYPE_COMMAND;
									break;
									case "boolean":
									case "mixed":
										$paramType = AvailableCommandsPacket::ARG_FLAG_VALID | AvailableCommandsPacket::ARG_TYPE_VALUE;
									break;
									default:
									case "text":
										$paramType = AvailableCommandsPacket::ARG_FLAG_VALID | AvailableCommandsPacket::ARG_TYPE_RAWTEXT;
									break;
								}
							}
							$parameter = new CommandParameter();
							$parameter->paramName = $paramName;
							$parameter->paramType = $paramType;
							$parameter->isOptional = $optional;
							$data->overloads[$tree][$argNumber] = $parameter;
						}else{
							++$enumCount;
							$enumValues = explode("|", $paramName);
							$parameter = new CommandParameter();
							$parameter->paramName = $paramName;
							$parameter->paramType = AvailableCommandsPacket::ARG_FLAG_ENUM | AvailableCommandsPacket::ARG_FLAG_VALID | $enumCount;
							$enum = new CommandEnum();
							$enum->enumName = $data->commandName." Enum#".$enumCount; // TODO: change to readable name in case parameter flag is 0
							$enum->enumValues = $enumValues;
							$parameter->enum = $enum;
							$parameter->flags = 1; // TODO
							$parameter->isOptional = $optional;
							$data->overloads[$tree][$argNumber] = $parameter;

							//$pk->hardcodedEnums[] = $enum; // TODO
							//$pk->softEnums[] = $enum; // TODO
							//$pk->enumConstraints[] = new \pocketmine\network\mcpe\protocol\types\CommandEnumConstraint($enum, 0, []); // TODO
						}
					}
					$aliases = $command->getAliases();
					if(count($aliases) > 0){
						if(!in_array($data->commandName, $aliases, true)){
							//work around a client bug which makes the original name not show when aliases are used
							$aliases[] = $data->commandName;
						}
						$data->aliases = new CommandEnum();
						$data->aliases->enumName = ucfirst($command->getName()) . "Aliases";
						$data->aliases->enumValues = $aliases;
					}
					$pk->commandData[$command->getName()] = $data;
				}
			}
		}
	}
}