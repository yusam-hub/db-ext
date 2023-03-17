<?php

namespace YusamHub\DbExt\Traits;

use YusamHub\DbExt\Exceptions\PdoExtModelException;
use YusamHub\DbExt\Interfaces\PdoExtInterface;
use YusamHub\DbExt\Interfaces\PdoExtModelExceptionInterface;
use YusamHub\DbExt\Interfaces\PdoExtModelInterface;

trait PdoExtModelTrait
{
    /**
     * @var PdoExtInterface|null
     */
    protected ?PdoExtInterface $pdoExt = null;

    /**
     * @var string
     */
    protected string $tableName = '';

    protected array $savedAttributes = [];

    /**
     * @var string|int
     */
    protected $primaryKey = '';

    /**
     * @param PdoExtInterface $pdoExt
     * @return void
     */
    public function setPdoExt(PdoExtInterface $pdoExt): void
    {
        $this->pdoExt = $pdoExt;
    }

    /**
     * @return PdoExtInterface
     */
    public function getPdoExt(): PdoExtInterface
    {
        return $this->pdoExt;
    }

    public static function findModel(PdoExtInterface $pdoExt, $pk)
    {
        $model = new static();
        $newModel = $pdoExt->findModel(get_class($model), $model->tableName, $model->primaryKey, $pk);
        if ($newModel instanceof PdoExtModelInterface) {
            $newModel->setPdoExt($pdoExt);
            $newModel->triggerAfterLoad();
            return $newModel;
        }
        return null;
    }

    public static function findModelByAttributes(PdoExtInterface $pdoExt, array $attributes)
    {
        $model = new static();
        $newModel = $pdoExt->findModelByAttributes(get_class($model), $model->tableName, $attributes);
        if ($newModel instanceof PdoExtModelInterface) {
            $newModel->setPdoExt($pdoExt);
            $newModel->triggerAfterLoad();
            return $newModel;
        }
        return null;
    }

    public static function findModelOrFail(PdoExtInterface $pdoExt, $pk)
    {
        $model = static::findModel($pdoExt, $pk);
        if (is_null($model)) {
            throw new PdoExtModelException([
                (new static())->primaryKey => $pk,
            ], PdoExtModelExceptionInterface::EXCEPTION_MESSAGE_MODEL_NOT_FOUND);
        }
        return $model;
    }

    public static function findModelByAttributesOrFail(PdoExtInterface $pdoExt, array $attributes)
    {
        $model = static::findModelByAttributes($pdoExt, $attributes);
        if (is_null($model)) {
            throw new PdoExtModelException($attributes, PdoExtModelExceptionInterface::EXCEPTION_MESSAGE_MODEL_NOT_FOUND);
        }
        return $model;
    }

    abstract public function getAttributes(): array;

    public function setAttributes(array $attributes): void
    {
        $this->savedAttributes = [];
        foreach($attributes as $attribute => $value) {
            if (property_exists($this, $attribute)) {
                $this->{$attribute} = $value;
            }
        }
    }

    public function save(): bool
    {
        /**
         * INSERT
         */
        if (empty($this->{$this->primaryKey})) {

            $this->triggerBeforeSave(self::TRIGGER_TYPE_SAVE_ON_INSERT);

            $primaryValue = $this->getPdoExt()->insertReturnId(
                $this->tableName,
                $this->getAttributes()
            );

            if (!empty($primaryValue)) {
                $this->{$this->primaryKey} = $primaryValue;
            }

            if ($this->getPdoExt()->affectedRows() === 1) {

                $this->savedAttributes = $this->getAttributes();

                $this->triggerAfterSave(self::TRIGGER_TYPE_SAVE_ON_INSERT,true);

                return true;
            }

            $this->triggerAfterSave(self::TRIGGER_TYPE_SAVE_ON_INSERT,false);

            return false;
        }
        /**
         * UPDATE
         */
        $changedValues = $this->getChangedAttributes();

        if (empty($changedValues)) {
            $this->triggerBeforeSave(self::TRIGGER_TYPE_SAVE_ON_NONE);
            $this->triggerAfterSave(self::TRIGGER_TYPE_SAVE_ON_NONE, true);
            return true;
        }

        $this->triggerBeforeSave(self::TRIGGER_TYPE_SAVE_ON_UPDATE);

        $result = $this->getPdoExt()->update(
            $this->tableName,
            $changedValues,
            [
                $this->primaryKey => $this->{$this->primaryKey}
            ],
            1
        );

        if ($result) {
            $this->savedAttributes = $this->getAttributes();
        }

        $this->triggerAfterSave(self::TRIGGER_TYPE_SAVE_ON_UPDATE, $result);

        return $result;
    }

    public function getChangedAttributes(): array
    {
        $changedValues = array_diff_assoc($this->getAttributes(), $this->savedAttributes);
        if (isset($changedValues[$this->primaryKey])) {
            unset($changedValues[$this->primaryKey]);
        }
        return $changedValues;
    }

    public function isChangedAttributes(): bool
    {
        return !empty($this->getChangedAttributes());
    }

    protected function triggerBeforeSave(int $triggerType): void
    {

    }

    protected function triggerAfterSave(int $triggerType, bool $saveResult): void
    {

    }

    protected function triggerAfterLoad(): void
    {
        $this->savedAttributes = $this->getAttributes();
    }
}