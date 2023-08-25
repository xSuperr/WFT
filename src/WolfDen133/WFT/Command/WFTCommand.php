<?php

namespace WolfDen133\WFT\Command;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;


use pocketmine\command\CommandSender;

use pocketmine\world\Position;

use pocketmine\player\Player;

use WolfDen133\WFT\API\TextManager;
use WolfDen133\WFT\Form\Types\CreationForm;
use WolfDen133\WFT\Form\Types\ListForm;
use WolfDen133\WFT\WFT;

class WFTCommand extends BaseCommand
{

    public function prepare(): void
    {
        $this->setPermission(WFT::getInstance()->getLanguageManager()->getLanguage()->getValue("command.permission"));
        $this->setDescription(WFT::getInstance()->getLanguageManager()->getLanguage()->getValue("command.description"));
        $this->setAliases(WFT::getInstance()->getLanguageManager()->getLanguage()->getValue("command.aliases"));
        $this->registerArgument(0, new RawStringArgument('one', true));
        $this->registerArgument(1, new RawStringArgument('two', true));
        $this->registerArgument(2, new RawStringArgument('three', true));
        $this->setUsage("/wft help");
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!($sender instanceof Player)) {
            $sender->sendMessage(WFT::getInstance()->getLanguageManager()->getLanguage()->getValue("command.sender"));
            return;
        }

        if (!$this->testPermission($sender)) return;

        if (empty($args)) {
            $sender->sendMessage($this->getUsage());
            return;
        }

        if (count($args) == 1) {
            switch ($args['one']) {
                case "reload":
                    WFT::getInstance()->getTextManager()->reloadTexts();
                    $sender->sendMessage("Reloaded texts");
                    break;
                case "list":
                case "see":
                case "all":
                    $sender->sendMessage("\\/=====FLOATING-TEXT LIST=====\\/\n");
                    foreach (WFT::getInstance()->getTextManager()->getTexts() as $text) {

                        $sender->sendMessage("==============================\n" .
                        " Name: " . $text->getName() . "\n" .
                        " Level: " . $text->getPosition()->getWorld()->getDisplayName() . "\n" .
                        " Position: " . $text->getPosition()->getX() . ", " . $text->getPosition()->getY() . ", " . $text->getPosition()->getZ() . "\n" .
                        " Lines: " . "\n(\n    " . implode("\n    ", explode("#", $text->getText())) . "\n)\n" .
                        "==============================\n");

                    }
                    $sender->sendMessage("/\\=====FLOATING-TEXT LIST=====/\\");
                    break;

                case "help":
                case "stuck":
                case "h":
                case "?":

                    $sender->sendMessage(str_replace("{LINE}", "\n", WFT::getInstance()->getLanguageManager()->getLanguage()->getValue("command.help")));
                    break;

                case "remove":
                case "break":
                case "delete":
                case "bye":
                case "d":
                case "r":
                    WFT::getInstance()->getFormManager()->sendFormTo($sender, ListForm::FORM_ID, ListForm::MODE_REMOVE);
                    break;

                case "tp":
                case "teleportto":
                case "tpto":
                case "goto":
                case "teleport":
                WFT::getInstance()->getFormManager()->sendFormTo($sender, ListForm::FORM_ID, ListForm::MODE_TP);

                break;

                case "tphere":
                case "teleporthere":
                case "movehere":
                case "bringhere":
                case "tph":
                case "move":
                WFT::getInstance()->getFormManager()->sendFormTo($sender, ListForm::FORM_ID, ListForm::MODE_TPHERE);

                break;

                case "edit":
                case "e":
                case "change":
                WFT::getInstance()->getFormManager()->sendFormTo($sender, ListForm::FORM_ID, ListForm::MODE_EDIT);

                break;

                case "add":
                case "create":
                case "spawn":
                case "summon":
                case "new":
                case "c":
                case "a":
                WFT::getInstance()->getFormManager()->sendFormTo($sender, CreationForm::FORM_ID);

                break;
                default:
                    $sender->sendMessage($this->getUsage());
                    return;
            }

            return;
        }

        if (count($args) == 2) {

            if (($text = WFT::getInstance()->getTextManager()->getTextById($args['two'])) === null) {
                $sender->sendMessage(WFT::getInstance()->getLanguageManager()->getLanguage()->getMessage("not-found", ["{NAME}" => $args['two']]));
                return;
            }

            switch ($args['one']) {
                case "remove":
                case "break":
                case "delete":
                case "bye":
                case "d":
                case "r":

                    WFT::getInstance()->getTextManager()->removeText($text->getName());
                    $sender->sendMessage(WFT::getInstance()->getLanguageManager()->getLanguage()->getMessage("remove", ["{NAME}" => $text->getName()]));

                    break;
                case "tp":
                case "teleportto":
                case "tpto":
                case "goto":
                case "teleport":

                    WFT::getInstance()->getTextManager()->levelCheck($text->getPosition()->getWorld()->getDisplayName());
                    $sender->teleport($text->getPosition());

                    break;
                case "tphere":
                case "teleporthere":
                case "movehere":
                case "bringhere":
                case "tph":
                case "move":

                    $text->setPosition(new Position($sender->getPosition()->getX(), $sender->getPosition()->getY() + 1.8, $sender->getPosition()->getZ(), $sender->getWorld()));
                    WFT::getInstance()->getTextManager()->getActions()->respawnToAll($text->getName());
                    WFT::getInstance()->getTextManager()->saveText($text);

                    break;
                default:
                    $sender->sendMessage($this->getUsage());
                    return;
            }
            return;
        }

        if (count($args) >= 3) {
            $api = WFT::getInstance()->getTextManager();

            switch ($args['one']) {
                case "add":
                case "create":
                case "spawn":
                case "summon":
                case "new":
                case "c":
                case "a":

                    if (in_array($args['two'], array_keys($api->getTexts()))) {
                        $sender->sendMessage(WFT::getInstance()->getLanguageManager()->getLanguage()->getMessage("exists", ["{NAME}" => $args['two']]));
                        return;
                    }

                    $floatingText = $api->registerText($args['two'], implode(" ", array_splice($args, 2)), new Position($sender->getPosition()->getX(), $sender->getPosition()->getY() + 1.8, $sender->getPosition()->getZ(), $sender->getWorld()));

                    $sender->sendMessage(WFT::getInstance()->getLanguageManager()->getLanguage()->getMessage("add", ["{NAME}" => $floatingText->getName()]));
                    break;
                case "edit":
                case "e":
                case "change":

                    if (($text = WFT::getInstance()->getTextManager()->getTextById($args['two'])) === null) {
                        $sender->sendMessage(WFT::getInstance()->getLanguageManager()->getLanguage()->getMessage("not-found", ["{NAME}" => $args['two']]));
                        return;
                    }

                    $text->setText(implode(" ", array_splice($args, 2)));
                    $api->saveText($text);
                    $api->getActions()->respawnToAll($text->getName());
                    $sender->sendMessage(WFT::getInstance()->getLanguageManager()->getLanguage()->getMessage("update", ["{NAME}" => $args['two']]));
                    break;
                default:
                    $sender->sendMessage($this->getUsage());
                    return;
            }
            return;
        }

        $sender->sendMessage($this->getUsage());
    }
}
