<?php
namespace MarcosHoo\PropelEloquent;

use Propel\Generator\Model\Behavior;

class EloquentBehavior extends Behavior
{
    public function objectMethods()
    {
        $mutators = "\n";

        foreach ($this->getTable()->getColumns() as $col) {
            $class = $this->getTableFullClassName();
            $name = $col->getName();
            $phpname = $col->getPhpName();
            $mutators .= "/**
 * Set the value of [{$name}] column.
 *
 * @param string \$v new value
 * @return \$this|\{$class} The current object (for fluent API support)
 */
public function set{$phpname}Temp(\$value)
{
    \$result = \$this->set{$phpname}Attribute(\$value);    
    \$this->attributes['{$name}'] = \$this->{$name};

    return \$result;
}

/**
 * Attribute {$name} accessor.
 *
 * @param mixed \$value
 * @return mixed
 */
public function get{$phpname}Attribute(\$value)
{
    return get{$phpname}();
}

";
        }

        return $mutators .
        '/**
 * Get an attribute from the model.
 *
 * @param  string  $key
 * @return mixed
 */
public function getAttribute($key)
{
    $method = \'get\' . str_replace(\' \', \'\', ucwords(str_replace(\'_\', \' \', $key)));
    try {
        return call_user_func([$this, $method]);
    } catch (\BadMethodCallException $e) {
        return null;
    }
}

/**
 * Set a given attribute on the model.
 *
 * @param  string  $key
 * @param  mixed  $value
 * @return $this
 */
public function setAttribute($key, $value)
{
    $method = \'set\' . str_replace(\' \', \'\', ucwords(str_replace(\'_\', \' \', $key)));
    try {
        return call_user_func([$this, $method], $value);
    } catch (\BadMethodCallException $e) {
        return null;
    }
}

/**
 * Dynamically retrieve attributes on the model.
 *
 * @param  string  $key
 * @return mixed
 */
public function __get($key)
{
    return $this->getAttribute($key);
}

/**
 * Dynamically set attributes on the model.
 *
 * @param  string  $key
 * @param  mixed  $value
 * @return void
 */
public function __set($key, $value)
{
    return $this->setAttribute($key, $value);
}

/**
 * Sync propel and eloquent attributes.
 */
protected function syncAttributes()
{
' . implode("\n", array_map(function ($col) {
            return "    \$this->attributes['{$col->getName()}'] = \$this->{$col->getName()};";
        }, $this->getTable()->getColumns())) . '
}

/**
 * Set the array of model attributes. No checking is done.
 *
 * @param  array  $attributes
 * @param  bool  $sync
 * @return $this
 */
public function setRawAttributes(array $attributes, $sync = false)
{
    $this->hydrateThis($attributes, 0, false, TableMap::TYPE_FIELDNAME);

    if ($sync) {
        $this->syncOriginal();
    }

    return $this;
}

/**
 * Hydrates (populates) the object variables with values from the database resultset.
 *
 * @see hydrateThis()
 */
public function hydrateThis($row, $startcol = 0, $rehydrate = false, $indexType = TableMap::TYPE_NUM)
{
    $result = $this->propelHydrate($row, $startcol, $rehydrate, $indexType);
    
    $this->syncAttributes();
    
    return $result;
}

/**
 * Create a collection of models from plain arrays.
 *
 * @param  array  $items
 * @param  string|null  $connection
 * @return \Illuminate\Database\Eloquent\Collection
 */
public static function hydrateTemp(array $items, $connection = null)
{
    $stdToArray = function ($obj) use (&$stdToArray) {
        $reaged = (array)$obj;
        foreach ($reaged as $key => &$field) {
            if (is_object($field)) {
                $field = $stdToArray($field);
            }
        }
        return $reaged;
    };

    $map = self::TABLE_MAP;
    $class = (new $map)->getOMClass(false);
    $model = new $class;

    foreach ($items as &$item) {
        $attributes = $stdToArray($item);
        $item = clone $model;
        $item->setRawAttributes($attributes, true);
    }

    return $model->newCollection($items);
}

/**
 * Save the model to the database.
 *
 * @param  array  $options
 * @return bool
 */
public function saveTemp(array $options = [])
{
    if ($this->fireModelEvent(\'saving\') === false) {
        return false;
    }

    $con = null; 

    if (is_array($options) && count($options)) {
        if ($options[0] instanceOf ConnectionInterface) {
            $con = $options[0];
            unset($options[0]);
        } 
    } elseif (isset($options[0]) &&  $options[0] instanceOf ConnectionInterface) {
        $con = $options;
    }

    if ($con === null) {
        $con = Propel::getServiceContainer()->getWriteConnection(' . $this->getTableClassName() . 'TableMap::DATABASE_NAME);
    }

    $affectedRows = $this->saveThis($con);

    $this->finishSave($options);

    return $affectedRows;
}

/**
 * Delete the model from the database.
 *
 * @return bool|null
 * @throws \Exception
 */
public function deleteTemp()
{
    $options = func_get_args();

    $con = null; 

    if (is_array($options) && count($options)) {
        if ($options[0] instanceOf ConnectionInterface) {
            $con = $options[0];
            unset($options[0]);
        } 
    } elseif (isset($options[0]) &&  $options[0] instanceOf ConnectionInterface) {
        $con = $options;
    }

    if ($con === null) {
        $con = Propel::getServiceContainer()->getWriteConnection(' . $this->getTableClassName() . 'TableMap::DATABASE_NAME);
    }

    if (!$this->isNew) {
        if ($this->fireModelEvent(\'deleting\') === false) {
            return false;
        }

        // Here, we\'ll touch the owning models, verifying these timestamps get updated
        // for the models. This will allow any caching to get broken on the parents
        // by the timestamp. Then we will go ahead and delete the model instance.
        $this->touchOwners();

        $this->deleteThis($con);

        $this->exists = false;

        // Once the model has been deleted, we will fire off the deleted event so that
        // the developers may hook into post-delete operations. We will then return
        // a boolean true as the delete is presumably successful on the database.
        $this->fireModelEvent(\'deleted\', false);

        return true;
    } else {
       return false;
    }       
}
';
    }

    public function getTableNameSpace()
    {
        return $this->getTable()->getNamespace();
    }

    public function getTableClassName()
    {
        return $this->getTable()->getPhpName();
    }

    public function getTableFullClassName()
    {
        return $this->getTableNameSpace() . '\\' . $this->getTableClassName();
    }

    protected function fixPackageName(&$script)
    {
        $matches = null;
        $pattern = "/@package +propel.*(\.[A-Za-z_0-9]+)( +)?/";
        $package = str_replace('\\', '.', $this->getTableNameSpace());
        preg_match($pattern, $script, $matches);
        if (isset($matches[1])) {
            $replacement = "@package {$package}{$matches[1]}";
            $script = preg_replace($pattern, $replacement, $script);
        }
    }

    protected function changeClassDeclaration(&$script)
    {
        $matches = null;
        $pattern = '/(\n+\/\*\*.*abstract +class +[A-Za-z_0-9]+ +)(extends +([A-Za-z_0-9]+) +)?(implements +[A-Za-z_0-9]+)(( |\n)+)?{/sm';
        preg_match($pattern, $script, $matches);
        if ($matches[2] == '') {
            $replacement = "use \Illuminate\Database\Eloquent\Model as EloquentModel;\n{$matches[1]}extends EloquentModel {$matches[4]}{$matches[5]}{";
            $script = preg_replace($pattern, $replacement, $script);
        } else {
            $replacement = "use \Illuminate\Database\Eloquent\Model as EloquentModel;\n{$matches[1]}extends EloquentModel {$matches[4]}{$matches[5]}{\n    use {$matches[3]};\n";
            $script = preg_replace($pattern, $replacement, $script);
        }
    }

    protected function changeConstructor(&$script)
    {
        $matches = null;
        $pattern = '/( +\/\*\*\n +\* Initializes.*public +function +__construct\()(\)( +)?(\n+)?( +)?{( +)?(\n+)?)( +)?(\$this->applyDefaultValues\(\);( +)?(\n+))(( +)})/sm';
        preg_match($pattern, $script, $matches);
        if (count($matches)) {
            $replacement = "{$matches[1]}array \$attributes = []{$matches[2]}{$matches[8]}parent::__construct(\$attributes);\n{$matches[8]}{$matches[9]}{$matches[12]}";
            $script = preg_replace($pattern, $replacement, $script);
        } else {
            $pattern = '/( +\/\*\*\n +\* Initializes.*public +function +__construct\()(\)( +)?(\n+)?( +)?{)([ \n]+)?(\n( +)})/sm';
            preg_match($pattern, $script, $matches);
            $replacement = "{$matches[1]}array \$attributes = []{$matches[2]}\n{$matches[8]}    parent::__construct(\$attributes);{$matches[7]}";
            $script = preg_replace($pattern, $replacement, $script);
        }
    }

    protected function renameSetMethods(&$script)
    {
        $matches = null;

        foreach ($this->getTable()->getColumns() as $col) {
            $phpname = $col->getPhpName();
            $pattern = "/function set{$phpname}\(/";
            $replacement = "function set{$phpname}Attribute(";
            $script = preg_replace($pattern, $replacement, $script);
        }

        $pattern = "/function +(set[a-zA-Z0-9_]+)Temp\(/";
        preg_match($pattern, $script, $matches);
        if (isset($matches[1])) {
            $replacement = 'function ${1}(';
            $script = preg_replace($pattern, $replacement, $script);
        }
    }

    protected function renameHydrateMethod(&$script)
    {
        $pattern = "/public +function +hydrate\(/";
        $replacement = "protected function propelHydrate(";
        $script = preg_replace($pattern, $replacement, $script);

        $pattern = '/\$this->hydrate\(/';
        $replacement = '$this->hydrateThis(';
        $script = preg_replace($pattern, $replacement, $script);

        $pattern = '/\$obj->hydrate\(/';
        $replacement = '$obj->hydrateThis(';
        $script = preg_replace($pattern, $replacement, $script);

        $pattern = "/ hydrateTemp\(/";
        $replacement = " hydrate(";
        $script = preg_replace($pattern, $replacement, $script);
    }

    public function renameSaveMethods(&$script)
    {
        $pattern = '/function +save\(/';
        $replacement = 'function saveThis(';
        $script = preg_replace($pattern, $replacement, $script);

        $pattern = '/(\$[A-Za-z_0-9]+)->save\(\$con\)/';
        preg_match($pattern, $script, $matches);
        if (isset($matches[1]) && $matches[1] !== '$this') {
            $replacement = '${1}->save(${1} instanceof EloquentModel ? [$con] : $con)';
            $script = preg_replace($pattern, $replacement, $script);
        }

        $pattern = '/(\$this->([A-Za-z_0-9]+))->save\(\$con\)/';
        preg_match($pattern, $script, $matches);
        if (isset($matches[2])) {
            $replacement = '$this->${2}->save($this->${2} instanceof EloquentModel ? [$con] : $con)';
            $script = preg_replace($pattern, $replacement, $script);
        }

        $pattern = '/\$this->save\((.*)\)/';
        preg_match($pattern, $script, $matches);
        $con = isset($matches[1]) ? $matches[1] : '';
        $replacement = "\$this->save([{$con}])";
        $script = preg_replace($pattern, $replacement, $script);

        $pattern = '/ saveTemp\(/';
        $replacement = ' save(';
        $script = preg_replace($pattern, $replacement, $script);

        $pattern = '/\$this->saveTemp\(/';
        $replacement = '$this->save(';
        $script = preg_replace($pattern, $replacement, $script);
    }

    public function renameDeleteMethods(&$script)
    {
        $pattern = '/public +function +delete\(/';
        $replacement = 'protected function deleteThis(';
        $script = preg_replace($pattern, $replacement, $script);

        $pattern = '/(\$[A-Za-z_0-9]+)->delete\(\$con\)/';
        preg_match($pattern, $script, $matches);
        if (isset($matches[1]) && $matches[1] !== '$this') {
            $replacement = '${1}->delete(${1} instanceof EloquentModel ? [$con] : $con)';
            $script = preg_replace($pattern, $replacement, $script);
        }

        $pattern = '/(\$this->([A-Za-z_0-9]+))->delete\(\$con\)/';
        preg_match($pattern, $script, $matches);
        if (isset($matches[2])) {
            $replacement = '$this->${2}->delete($this->${2} instanceof EloquentModel ? [$con] : $con)';
            $script = preg_replace($pattern, $replacement, $script);
        }

        $pattern = '/\$this->delete\((.*)\)/';
        preg_match($pattern, $script, $matches);
        $con = isset($matches[1]) ? $matches[1] : '';
        $replacement = "\$this->delete([{$con}])";
        $script = preg_replace($pattern, $replacement, $script);

        $pattern = '/ deleteTemp\(/';
        $replacement = ' delete(';
        $script = preg_replace($pattern, $replacement, $script);

        $pattern = '/\$this->deleteTemp\(/';
        $replacement = '$this->delete(';
        $script = preg_replace($pattern, $replacement, $script);
    }

    protected function changePrePostMethods(&$script)
    {
        $pattern = "/if +\(is_callable\('parent::(pre|post)[a-zA-Z0-9_]+'\)\) +{/";
        $replacement = 'if (($p = get_parent_class(self::class)) && (new \ReflectionClass($p))->hasMethod(__FUNCTION__)) {';
        $script = preg_replace($pattern, $replacement, $script);
    }

    protected function changeMagicCallMethod(&$script)
    {
        $pattern = '/throw new BadMethodCallException\(sprintf\(\'Call to undefined method: %s.\', \$name\)\);/';
        $replacement = 'return parent::__call($name, $params);';
        $script = preg_replace($pattern, $replacement, $script);
    }

    public function setTableMapTypeFieldNameAsDefault(&$script)
    {
        $pattern = '/toArray\(TableMap::TYPE_PHPNAME/';
        $replacement = 'toArray(TableMap::TYPE_FIELDNAME';
        $script = preg_replace($pattern, $replacement, $script);

        $pattern = '/\ype += +TableMap::TYPE_PHPNAME/';
        $replacement = 'ype = TableMap::TYPE_FIELDNAME';
        $script = preg_replace($pattern, $replacement, $script);
    }

    public function tableMapFilter(&$script)
    {
        $this->fixPackageName($script);
        $this->renameSaveMethods($script);
        $this->renameHydrateMethod($script);
    }

    public function queryFilter(&$script)
    {
        $this->fixPackageName($script);
        $this->renameSaveMethods($script);
        $this->renameHydrateMethod($script);
    }

    public function objectFilter(&$script)
    {
        $this->fixPackageName($script);
        $this->changeClassDeclaration($script);
        $this->changeConstructor($script);
        $this->renameSetMethods($script);
        $this->renameSaveMethods($script);
        $this->renameDeleteMethods($script);
        $this->renameHydrateMethod($script);
        $this->changePrePostMethods($script);
        $this->changeMagicCallMethod($script);
        $this->setTableMapTypeFieldNameAsDefault($script);
    }
}
