<?php

namespace YusamHub\DbExt\Interfaces;

interface PdoExtModelExceptionInterface
{
    const EXCEPTION_MESSAGE_MODEL_NOT_FOUND = "Model not found";
    function getData(): array;
}