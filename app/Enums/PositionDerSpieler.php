<?php
namespace App\Enums;

class PositionDerSpieler extends \App\Lib\BetterEnum{
    const __default = self::Vorne;

    const Vorne = "Vorne";
    const Hinten = "Hinten";
}
