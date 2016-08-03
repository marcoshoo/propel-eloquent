<?php

namespace MarcosHoo\PropelEloquent\Model;

use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;

/**
 *
 * @author marcos
 *
 */
trait HydrateFromTrait
{
    /**
     *
     * @param string $attribute
     * @param mixed $value
     * @return ActiveRecordInterface
     */
    public function from($attributes, $value = null)
    {
        if (!is_array($attributes)) {
            if (is_object($attributes)) {
                $attributes = get_object_vars($attributes);
            } else {
                $json =json_decode($attributes ,true);
                if (count($json)) {
                    $attributes = $json;
                } else {
                    $attributes = [
                        $attributes => $value
                    ];
                }
            }
        }

        $this->fromArray($attributes);

        $r = new \ReflectionClass($this);

        foreach ($attributes as $name => $value) {

            $attr = \Illuminate\Support\Str::studly($name);
            $add = substr($attr, 0, -1);
            if ($r->hasMethod('set' . $attr)
            && $r->hasProperty('coll' . $attr)
            && $r->hasMethod('add' . $add)
        ) {
            $method = $r->getMethod('add' . $add);

            $class = $method->getParameters();
            $class = $class[0]->getClass()->getName();

            if ($class) {
                $itens = new ObjectCollection();
                foreach ($value as $item) {
                    if (is_array($item)) {
                        $itens->append((new $class)->from($item));
                    }
                }
                $set = 'set' . $attr;
                $this->$set($itens);
            }
        }
    }

    if (!$this->isPrimaryKeyNull()) {
        $this->setNew(false);
    }

    return $this;
}
}