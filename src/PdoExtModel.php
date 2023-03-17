<?php

namespace YusamHub\DbExt;


use YusamHub\DbExt\Interfaces\PdoExtKernelInterface;
use YusamHub\DbExt\Interfaces\PdoExtModelInterface;
use YusamHub\DbExt\Traits\PdoExtModelTrait;

/**
 * @method static PdoExtModelInterface|null findModel(PdoExtKernelInterface $pdoExt, $pk)
 * @method static PdoExtModelInterface findModelOrFail(PdoExtKernelInterface $pdoExt, $pk)
 * @method static PdoExtModelInterface|null findModelByAttributes(PdoExtKernelInterface $pdoExt, array $attributes)
 * @method static PdoExtModelInterface findModelByAttributesOrFail(PdoExtKernelInterface $pdoExt, array $attributes)
 */
abstract class PdoExtModel implements PdoExtModelInterface
{
    use PdoExtModelTrait;
}