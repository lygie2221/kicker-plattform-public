<?php
namespace App\Enums;

class ModusDerBegegnung extends \App\Lib\BetterEnum{
    const __default = self::OneMatch;

    const OneMatch = "One Match";
    const BestOf3 = "Best Of 3";
    const BestOf5 = "Best Of 5";

}
