<?php

namespace YusamHub\DbExt;


use YusamHub\DbExt\Interfaces\PdoExtKernelInterface;
use YusamHub\DbExt\Interfaces\PdoExtModelInterface;
use YusamHub\DbExt\Traits\GetSetPdoExtKernelTrait;
use YusamHub\DbExt\Traits\PdoExtModelTrait;

/**
 * @method static PdoExtModelInterface|null findModel(PdoExtKernelInterface $pdoExtKernel, $pk)
 * @method static PdoExtModelInterface findModelOrFail(PdoExtKernelInterface $pdoExtKernel, $pk)
 * @method static PdoExtModelInterface|null findModelByAttributes(PdoExtKernelInterface $pdoExtKernel, array $attributes)
 * @method static PdoExtModelInterface findModelByAttributesOrFail(PdoExtKernelInterface $pdoExtKernel, array $attributes)
 */
abstract class PdoExtModel implements PdoExtModelInterface
{
    use PdoExtModelTrait;
    use GetSetPdoExtKernelTrait;
}