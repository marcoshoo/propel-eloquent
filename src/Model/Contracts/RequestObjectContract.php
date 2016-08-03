<?php

namespace MarcosHoo\PropelEloquent\Model\Contracts;

use Illuminate\Http\Request;

/**
 *
 * @author marcos
 *
 */
interface RequestObjectContract extends HydrateFromContract
{
    /**
     *
     * @param Request $request
     */
    public function fromRequest(Request $request);
}