<?php

namespace WolfDen133\WFT\Texts;

use pocketmine\block\VanillaBlocks;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;;
use pocketmine\network\mcpe\protocol\SetActorDataPacket;
use pocketmine\network\mcpe\protocol\types\AbilitiesData;
use pocketmine\network\mcpe\protocol\types\AbilitiesLayer;
use pocketmine\network\mcpe\protocol\types\command\CommandPermissions;
use pocketmine\network\mcpe\protocol\types\DeviceOS;
use pocketmine\network\mcpe\protocol\types\entity\ByteMetadataProperty;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\network\mcpe\protocol\types\entity\FloatMetadataProperty;
use pocketmine\network\mcpe\protocol\types\entity\IntMetadataProperty;
use pocketmine\network\mcpe\protocol\types\entity\LongMetadataProperty;
use pocketmine\network\mcpe\protocol\types\entity\PropertySyncData;
use pocketmine\network\mcpe\protocol\types\entity\StringMetadataProperty;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStack;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\network\mcpe\protocol\UpdateAbilitiesPacket;
use Ramsey\Uuid\Uuid as UUID;
use pocketmine\entity\Skin;
use pocketmine\world\Position;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\PlayerListPacket;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use pocketmine\network\mcpe\protocol\types\PlayerListEntry;
use WolfDen133\WFT\Utils\Utils;
use pocketmine\player\Player;

class SubText
{
    private string $text;
    private Position $position;

    private string $uuid;
    private int $runtime;

    public function __construct(string $text, Position $position, string $uuid, int $runtimeID)
    {
        $this->text = $text;
        $this->position = $position;
        $this->runtime = $runtimeID;
        $this->uuid = $uuid;
    }

    public function setText (string $text) : void
    {
        $this->text = $text;
    }

    public function updateTextTo (Player $player) : void
    {
        $pk = SetActorDataPacket::create($this->runtime,
            [ EntityMetadataProperties::NAMETAG => new StringMetadataProperty(Utils::getFormattedText($this->text, $player)) ],
            new PropertySyncData([], []),
            0
        );

        $player->getNetworkSession()->sendDataPacket($pk);
    }

    public function spawnTo (Player $player) : void
    {
        $actorFlags = (
            1 << EntityMetadataFlags::NO_AI
        );

        $pk = AddActorPacket::create(
            $this->runtime,
            $this->runtime,
            EntityIds::FALLING_BLOCK,
            $this->position->asVector3(),
            null,
            0,
            0,
            0,
            0,
            [],
            [
                EntityMetadataProperties::FLAGS => new LongMetadataProperty($actorFlags),
                EntityMetadataProperties::SCALE => new FloatMetadataProperty(0.1),
                EntityMetadataProperties::BOUNDING_BOX_WIDTH => new FloatMetadataProperty(0.0),
                EntityMetadataProperties::BOUNDING_BOX_HEIGHT => new FloatMetadataProperty(0.0),
                EntityMetadataProperties::NAMETAG => new StringMetadataProperty(Utils::getFormattedText($this->text, $player)),
                EntityMetadataProperties::VARIANT => new IntMetadataProperty(TypeConverter::getInstance()->getBlockTranslator()->internalIdToNetworkId(VanillaBlocks::AIR()->getStateId())),
                EntityMetadataProperties::ALWAYS_SHOW_NAMETAG => new ByteMetadataProperty(1),
            ],
            new PropertySyncData([], []),
            []
        );

        $player->getNetworkSession()->sendDataPacket($pk);

    }

    public function closeTo (Player $player) : void
    {
        $pk = RemoveActorPacket::create($this->runtime);

        $player->getNetworkSession()->sendDataPacket($pk);
    }
}
