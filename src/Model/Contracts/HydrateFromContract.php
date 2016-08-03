<?php

namespace MarcosHoo\PropelEloquent\Model\Contracts;

/**
 *
 * @author marcos
 *
 */
interface HydrateFromContract
{
    /**
     *
     * @param unknown $attributes
     * @param unknown $value
     */
    public function from($attributes, $value = null);
}