<?php

namespace YusamHub\DbExt\Interfaces;

interface PdoExtModelInterface
{
    const TRIGGER_TYPE_SAVE_ON_NONE = 0;
    const TRIGGER_TYPE_SAVE_ON_INSERT = 1;
    const TRIGGER_TYPE_SAVE_ON_UPDATE = 2;

    function setPdoExtKernel(PdoExtKernelInterface $pdoExt): void;
    function getPdoExtKernel(): PdoExtKernelInterface;

    static function findModel(PdoExtKernelInterface $pdoExt, $pk);

    static function findModelByAttributes(PdoExtKernelInterface $pdoExt, array $attributes);

    static function findModelOrFail(PdoExtKernelInterface $pdoExt, $pk);

    static function findModelByAttributesOrFail(PdoExtKernelInterface $pdoExt, array $attributes);

    function getAttributes(): array;

    function setAttributes(array $attributes): void;

    function save(): bool;

    function getChangedAttributes(): array;

    function isChangedAttributes(): bool;
}