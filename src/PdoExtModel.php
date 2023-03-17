<?php

namespace YusamHub\DbExt;

use YusamHub\DbExt\Interfaces\PdoExtInterface;
use YusamHub\DbExt\Interfaces\PdoExtModelInterface;
use YusamHub\DbExt\Traits\PdoExtModelTrait;

/**
 * @method static PdoExtModelInterface|null findModel(PdoExtInterface $pdoExt, $pk)
 * @method static PdoExtModelInterface findModelOrFail(PdoExtInterface $pdoExt, $pk)
 * @method static PdoExtModelInterface|null findModelByAttributes(PdoExtInterface $pdoExt, array $attributes)
 * @method static PdoExtModelInterface findModelByAttributesOrFail(PdoExtInterface $pdoExt, array $attributes)
 */
abstract class PdoExtModel implements PdoExtModelInterface
{
    use PdoExtModelTrait;
}