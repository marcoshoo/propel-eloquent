<?php
namespace MarcosHoo\PropelEloquent;

use Propel\Generator\Model\Behavior;
use MarcosHoo\PropelEloquent\Builder\EloquentBuilder;
use MarcosHoo\PropelEloquent\Builder\RequestObjectBuilder;

class EloquentBehavior extends Behavior
{
    /**
     *
     * @var array
     */
    protected $additionalBuilders = [
        EloquentBuilder::class,
        RequestObjectBuilder::class,
    ];

    /**
     *
     * @return string
     */
    public function getTableNameSpace()
    {
        return $this->getTable()->getNamespace();
    }

    /**
     *
     * @return string
     */
    public function getTableClassName()
    {
        return $this->getTable()->getPhpName();
    }

    /**
     *
     * @return string
     */
    public function getTableFullClassName()
    {
        return $this->getTableNameSpace() . '\\' . $this->getTableClassName();
    }

    /**
     *
     * @return string
     */
    public function getEloquentFullClassName()
    {
        return $this->getTableNameSpace() . '\\Eloquent\\' . $this->getTableClassName();
    }

    /**
     *
     * @return string
     */
    public function objectAttributes()
    {
        return '
/**
 *
 * @var \\' . $this->getEloquentFullClassName() . '
 */
protected $___model;

/**
 *
 * @var boolean
 */
public $___alreadyInSet = false;
';
    }

    /**
     *
     * @return string
     */
    public function objectMethods()
    {
        return '
/**
 *
 * @return array
 */
public function jsonSerialize()
{
    return $this->toArray(TableMap::TYPE_FIELDNAME);
}

/**
 *
 * @return \\' . $this->getEloquentFullClassName() . '
 */
public function getEloquentModel()
{
    return $this->___model;
}

/**
 *
 */
public function ___getEloquentProperty($property)
{
    $r1 = new \ReflectionClass(\'\\' . $this->getEloquentFullClassName() . '\');
    $r1 = $r1->getParentClass();
    $r2 = new \ReflectionClass($this);
    if ($r1->hasProperty($property) && $r2->hasProperty($property)) {
        return $this->$property;
    }
}

/**
 *
 * @throws \Exception
 * @return \\' . $this->getEloquentFullClassName() . '
 */
protected function ___loadEloquentClass($args = null)
{
    if (!$this->___model) {
        $this->___model = new \\' . $this->getEloquentFullClassName() . '($this);
        if (is_array($args)) {
            $this->___model->fill($args);
        }
    }
    return $this->___model;
}

/**
 *
 * @param string \$name
 * @param array \$params
 * @throws \Exception
 * @return \Illuminate\Database\Eloquent\Collection|boolean
 */
public function ___callEloquent($name, $params)
{
    switch ($name) {
        case \'hydrate\':
            if (isset($params[1]) && $params[1] instanceof EloquentCollection) {
                return $this->___loadEloquentClass()->hydrate($params[0], $params[1]);
            }
            break;
        case \'save\':
            if (isset($params[0]) && is_array($params[0])) {
                return $this->___model->save($params[0]);
            }
            break;
        default:
            $eloquent = $this->___loadEloquentClass();
            if ($eloquent) {
                return call_user_func_array([ $eloquent, $name ], $params);
            }
    }
    throw new \Exception(\'Invalid method\');
}

/**
 *
 * @param string $name
 * @param array $params
 */
public static function __callstatic($name, $params)
{
    return call_user_func_array("' . $this->getEloquentFullClassName() . '::$name" , $params);
}

/**
 *
 * @param array $attributtes
 * @param boolean $resetModified
 * @param boolean $force
*/
public function ___fill(array $attributes = [], $resetModified = false, $force = false)
{
    $totallyGuarded = !$force && $this->___model && $this->___model->totallyGuarded();
    foreach ($attributes as $key => $value) {
        if ($totallyGuarded) {
            throw new \Illuminate\Database\Eloquent\MassAssignmentException($key);
        }
        $method = \'set\' . $key;
        if (method_exists($this, $method)) {
            $this->$method($value);
        } else {
            $this->$key = $value;
        }
    }
    if ($resetModified) {
        $this->resetModified();
        $this->setDeleted(false);
    }
}

/**
 * Fill the model with an array of attributes. Force mass assignment.
 *
 * @param  array  $attributes
 * @param boolean $resetModified
 * @return $this
 */
public function ___forceFill(array $attributes, $resetModified = false)
{
    $this->___fill($attributes, $resetModified, true);
}

/**
 * Dynamically set attributes on the model.
 *
 * @param  string  $key
 * @param  mixed   $value
 * @return void
 */
public function __set($key, $value)
{
    if (!$this->___alreadyInSet) {
        $this->___alreadyInSet = true;
        $name = ' . $this->getTable()->getPhpName() . 'TableMap::getTableMap()->getColumn($key)->getPhpName();
        $method = \'set\' . $name;
        $eloquent = $this->___model;
        if (method_exists($this, $method)) {
            $r = new \ReflectionMethod($this, $method);
            if ($r->isPublic()) {
                $r->invoke($this, $value);
            }
        }
        $eloquent->setAttribute($key, $value);
        $this->___alreadyInSet = false;
    }
}

/**
 * Dynamically retrieve attributes on the model.
 *
 * @param  string  $key
 * @throws BadMethodCallException
 * @return mixed
 */
protected function ___getAttribute($key)
{
    $str = new \Illuminate\Support\Str;
    $rx = new \ReflectionClass($this);
    $skey = $str->studly($key);

    $property_name = \'coll\' . $skey;
    $property = $rx->hasProperty($property_name) ? $rx->getProperty($property_name) : null;
    $method_name = \'get\' . $skey;
    $method = $rx->hasMethod($method_name) ? $rx->getMethod($method_name) : null;
    if ($property && $method) {
        return $method->invoke($this);
    }

    $property_name = \'a\' . $skey;
    $property = $rx->hasProperty($property_name) ? $rx->getProperty($property_name) : null;
    $method_name = \'___getColumn\' . $skey;
    $method = $rx->hasMethod($method_name) ? $rx->getMethod($method_name) : null;
    if ($property && $method) {
        return $method->invoke($this);
    }

    $info = explode(\'_\', $key);
    if (count($info) == 2) {
        $skey = $str->studly($info[1]) . \'RelatedBy\' . $str->studly($info[0]);
        $property_name2 = \'a\' . $skey;
        $property_name1 = \'coll\' . $skey;
        $property = $rx->hasProperty($property_name1) ? $rx->getProperty($property_name1) : ($rx->hasProperty($property_name2) ? $rx->getProperty($property_name2) : null);
        $method_name = \'get\' . $skey;
        $method = $rx->hasMethod($method_name) ? $rx->getMethod($method_name) : null;
        if ($property && $method) {
            return $method->invoke($this);
        }
    }

    throw new BadMethodCallException(\'Invalid attribute.\');
}

/**
 * Dynamically retrieve attributes on the model.
 *
 * @param  string  $key
 * @return mixed
 */
public function __get($key)
{
    try {
        return $this->___getAttribute($key);
    } catch (BadMethodCallException $e) {
        return $this->___model->getAttribute($key);
    }
}
';
    }

    /**
     *
     * @return string
     */
    public function objectCall()
    {
        return <<<EOD
    \$columnName = substr(\$name, 3);

    if (0 === strpos(\$name, 'set') && method_exists(\$this, 'set' . \$columnName)) {
        return call_user_func_array([\$this, 'set' . \$columnName], \$params);
    }

    try{
        return call_user_func_array([ \$this, '___callEloquent' ], func_get_args());
    } catch(\Exception \$e) {
        try {
            return \$this->___getAttribute(\$name);
        } catch (BadMethodCallException \$e) {}
    }

EOD;

    }

    /**
     *
     * @param string $script
     */
    public function objectFilter(&$script)
    {
        $matches = null;
        $pattern = '/(@package)( )+propel\.generator\.(\.)?(.*)/';
        preg_match($pattern, $script, $matches);

        $script = preg_replace($pattern, $matches[1] . $matches[2] . $matches[4], $script);

        $matches = null;
        $pattern = "/(\n\/\*(.*)abstract class " . $this->getTableClassName() .  "( extends \w+)?)( implements (\w|,)+)?((.+)?\n\{)/sm";
        preg_match($pattern, $script, $matches);

        $replacement =
            "use \JsonSerializable;\n"
            . "use MarcosHoo\PropelEloquent\Model\Contracts\HydrateFromContract;\n"
            . "use MarcosHoo\PropelEloquent\Model\HydrateFromTrait;\n\n"
            . $matches[1] . $matches[4] . ($matches[4] ? ', ' : ' implements ')
            . 'HydrateFromContract, JsonSerializable' . $matches[6] . "\n    use HydrateFromTrait;\n";

        $script = preg_replace($pattern, $replacement, $script);

        foreach ($this->getTable()->getColumns() as $col) {

            $col = $col->getName();

            $script = preg_replace("/protected( +)\\\${$col};/", "protected \$column_{$col};", $script);

            $name = $this->getTable()->getColumn($col)->getPhpName();

            foreach ([' ', '( +)?\)', '( +)?=', '( +)?;', '( +)?,', '->'] as $search) {

                $matches = null;
                $pattern = "/\\\$this->({$col})(${search})/";
                preg_match($pattern, $script, $matches);

                if (count($matches) > 2) {
                    $replacement = '$this->column_' . $matches[1] . $matches [2];
                    $script = preg_replace($pattern, $replacement, $script);
                }

            }

            $matches = null; //{$name}
            $pattern = "/(public function( +)?get{$name}\(((\\$\w+) = (.*))?\))/";
            preg_match($pattern, $script, $matches);

            $params = isset($matches[3]) ? $matches[3] : '';
            $var = isset($matches[4]) ? $matches[4] : '';

            $replacement = <<<EOD
public function get{$name}({$params})
    {
        return \$this->___getColumn{$name}({$var});
    }

    /**
     * Get the [{$col}] column value.
     *
     * @return boolean
     */
    protected function ___getColumn{$name}({$params})
EOD;

            $script = preg_replace($pattern,$replacement, $script);

            $matches = null;
            $pattern = "/(public function( +)?set{$name}\(\\\$v\)( +)?\n?( +)?{)(( +)?(.*)\/\/( +)set{$name})/sm";
            preg_match($pattern, $script, $matches);

            if (count($matches) > 8) {

                $replacement = <<<EOD
public function set{$name}(\$v)
    {
        return \$this->___setColumn{$name}(\$v);
    }

    /**
     * Set the value of [{$col}] column.
     *
     * @param string \$v new value
     * @return \$this|{$this->getTableFullClassName()} The current object (for fluent API support)
     */
    protected function ___setColumn{$name}(\$v)
    {
        \$this->___alreadyInSet = true;
        \$this->___model->{$col} = \$v;
        \$this->___alreadyInSet = false;
{$matches[5]}
EOD;

                $script = preg_replace($pattern, $replacement, $script);
            }

        }

        $matches = null;
        $pattern = '/(( +)?(\$this->ensureConsistency\(\);)\n?( +)?}( +)?\n)/';
        preg_match($pattern, $script, $matches);

        $forceFill = <<<EOD
\$values = \$this->toArray(TableMap::TYPE_FIELDNAME);
            \$columns = [];
            \$r = new \ReflectionClass(self::class);

            foreach (\$values as \$name => \$value) {
                if (\$r->hasProperty('column_' . \$name)) {
                    \$columns[\$name] = \$value;
                }
            }

            \$this->___model->forceFill(\$columns);

EOD;
        $replacement = $matches[0] . "\n" . $matches[4] . $forceFill;

        $script = preg_replace($pattern, $replacement, $script);

        $constructor = <<<EOD
        \$args = func_get_args();
        if (isset(\$args[0])) {
            if (\$args[0] instanceof \\{$this->getEloquentFullClassName()}) {
                \$this->___fill(\$args[0]->getAttributes(), true);
                \$this->___model = \$args[0];
            } elseif (is_array(\$args[0])) {
                \$this->___loadEloquentClass(\$args[0]);
            }
        } else {
           \$this->___loadEloquentClass();
        }
EOD;

        $matches = null;
        $pattern = '/( +)(public function __construct\(.*\)(( +)?\n?( +?){))/';
        preg_match($pattern, $script, $matches);

        if (!isset($matches[0])) {
            $pattern = '/\n\}\n\z/';
            preg_match($pattern, $script, $matches);
            $replacement = "\n    public function __construct()\n    {\n" . $constructor . "\n    }" . $matches[0];
        } else {
            $replacement =
                $matches[0] . $matches[1] . "\n" . $constructor;
        }
        $script = preg_replace($pattern, $replacement, $script);

    }
}
