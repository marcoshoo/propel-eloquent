<?php

namespace MarcosHoo\PropelEloquent\Model;

use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use Illuminate\Http\Request;

/**
 *
 * @author marcos
 *
 */
trait RequestObjectTrait
{
    use HydrateFromTrait;

    /**
     *
     * @param string $attribute
     * @param mixed $value
     * @return ActiveRecordInterface
     */
    public function fromRequest(Request $request)
    {
        $this->from($request->all());
    }
}
