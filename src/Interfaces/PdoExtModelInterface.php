<?php

namespace YusamHub\DbExt\Interfaces;

interface PdoExtModelInterface
{
    const TRIGGER_TYPE_SAVE_ON_NONE = 0;
    const TRIGGER_TYPE_SAVE_ON_INSERT = 1;
    const TRIGGER_TYPE_SAVE_ON_UPDATE = 2;

    function setPdoExt(PdoExtInterface $pdoExt): void;
    function getPdoExt(): PdoExtInterface;

    static function findModel(PdoExtInterface $pdoExt, $pk);

    static function findModelByAttributes(PdoExtInterface $pdoExt, array $attributes);

    static function findModelOrFail(PdoExtInterface $pdoExt, $pk);

    static function findModelByAttributesOrFail(PdoExtInterface $pdoExt, array $attributes);

    function getAttributes(): array;

    function setAttributes(array $attributes): void;

    function save(): bool;

    function getChangedAttributes(): array;

    function isChangedAttributes(): bool;
}