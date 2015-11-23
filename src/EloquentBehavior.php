<?php
namespace MarcosHoo\PropelEloquent;

use Propel\Generator\Model\Behavior;

class EloquentBehavior extends Behavior
{
    protected $parameters = [];

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

    public function getEloquentFullClassName()
    {
        return head(explode('\\',$this->getTableNameSpace())) . '\\' . $this->getTableClassName();
    }

    public function objectMethods()
    {
        return '
/**
 *
 * @var \Illuminate\Database\Eloquent\Model
 */
protected $___model;

/*
 * @var boolean
 */
public $___alreadyInSet = false;

/**
 *
 */
public function ___getEloquentProperty($property, \\' . $this->getTableFullClassName() . ' $model)
{
    $m = $this->___loadEloquentClass();
    if ($model !== $m) {
        $r1 = new \ReflectionClass($m);
        $r1 = $r1->getParentClass();
        $r2 = new \ReflectionClass($this);
        if ($r1->hasProperty($property) && $r2->hasProperty($property)) {
            return $this->$property;
        }
    }
    throw new \Exception("Property does not exist.");
}

/**
 *
 * @throws \Exception
 * @return \Illuminate\Database\Eloquent\Model
 */
protected function ___loadEloquentClass($args = null, $connection = null)
{
    if (!$this->___model) {
        $this->___model = new \\' . $this->getEloquentFullClassName() . '($this);
        if (is_array($args)) {
            $this->___model->fill($args, $connection);
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
    }
    throw new \Exception();
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
 */
public function ___fill(array $attributes = [])
{
    $totallyGuarded = $this->___model && $this->___model->totallyGuarded();
    foreach ($attributes as $key => $value) {
        if ($totallyGuarded) {
            throw new \Illuminate\Database\EloquentException\MassAssignmentException($key);
        }
        $method = \'set\' . $key;
        if (method_exists($this, $method)) {
            $this->$method($value);
        } else {
            $this->$key = $value;
        }
    }
    $this->resetModified();
    $this->setDeleted(false);
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
        $this->$key = $value;
        $method = \'set\' . $key;
        $eloquent = $this->___model;
        if (method_exists($this, $method)) {
            $r = new \ReflectionMethod($this, $method);
            if ($r->isPublic()) {
                $r->invoke($this, $value);
            }
        }
        $eloquent->$key = $this->$key;
        $this->___alreadyInSet = false;
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
    if (!isset($this->$key)) {
        return $this->___model->getAttribute($key);
    } else {
        $r = new \ReflectionClass($this);
        if ($r->hasProperty($key)) {
            $r = $r->getProperty($key);
            if ($r->isPublic()) {
                return $this->$key;
            }
        }
    }
    throw new \Exception(\'Property is protected or private.\');
}
';
    }

    public function objectFilter(&$script)
    {
        $matches = null;
        $pattern = '/( +)throw new BadMethodCallException\(.*\);( +)?\n?( +)?/';
        preg_match($pattern, $script, $matches);
        $replacement =
            $matches[1] . "try{\n" . $matches[1] . "    call_user_func_array([ \$this, '___callEloquent' ], func_get_args());\n" .
            $matches[1] . "} catch(\Exception \$e) {\n    " . $matches[0] . "    }\n". $matches[3];

        $script = preg_replace($pattern, $replacement, $script);

        $constructor = <<<EOD
        \$args = func_get_args();
        if (isset(\$args[0])) {
            if (\$args[0] instanceof \Illuminate\Database\Eloquent\Model) {
                \$this->___fill(\$args[0]->getAttributes());
                \$this->___model = \$args[0];
            } elseif (is_array(\$args)) {
                \$this->___loadEloquentClass(\$args);
            }
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

        $namespace = head(explode('\\', $this->getTableNameSpace()));
        $class = $this->getTableClassName();

        $behaviors = [];
        foreach ($this->getTable()->getBehaviors() as $behavior) {
            array_push($behaviors, $behavior->getName());
        }

        $table = $this->getTable()->getName();
        $primaryKey = $this->getTable()->getPrimaryKey()[0]->getName();
        $timestamps = in_array('timestampable', array_keys($behaviors));

        function fmt($s) {
            return str_replace('{:nlw:}', '\n', str_replace('\n', "\n", str_replace('\\\\n', '{:nlw:}', $s)));
        }

        $uses =
            isset($this->parameters['uses'])
                ? "\nuse " . implode(";\nuse ",explode(',',$this->getParameter('uses'))) . ";\n" : '';

        $interfaces =
            isset($this->parameters['interfaces'])
                ? 'implements ' . fmt($this->getParameter('interfaces'))
                : '';

        $traits =
            isset($this->parameters['traits'])
                ? 'use ' . fmt($this->getParameter('traits')) . ";\n\n    "
                : '';

        $properties =
            isset($this->parameters['properties'])
                ? ''. fmt($this->getParameter('properties')) . "\n\n    "
                : '';

        $methods =
            isset($this->parameters['methods'])
                ? "\n    " . fmt($this->getParameter('methods')) . "\n"
                : '';

        $script .= "
namespace {$namespace};
{$uses}
class {$class} extends \\Illuminate\\Database\\Eloquent\\Model {$interfaces}
{
    {$traits}{$properties}protected \$___model;

    protected \$table = '{$table}';

    protected \$primaryKey = '{$primaryKey}';
";

        if (!$timestamps) {
            $script .= "
    protected \$timestamps = false;

";
        }

        $script .= "

    /*
     * @var boolean
     */
    public \$___alreadyInSet = false;

    public function __construct()
    {
        \$args = func_get_args();
        if (isset(\$args[0]) && \$args[0] instanceof {$this->getTableFullClassName()}) {
            \$args = null;
            \$this->___model = \$args[0];
        } else {
            \$this->___model = \$this->___model ?: new \Quotem\Models\User(\$this);
        }
        \$this->___init();
        if (isset(\$args[0])) {
            parent::__construct(\$args[0]);
        } else {
            parent::__construct();
        }
    }

    protected function ___init()
    {
        \$r1 = new \ReflectionClass(\$this);
        \$r1 = \$r1->getParentClass();
        \$r2 = new \ReflectionClass(\$this->___model);
        foreach (\$r1->getProperties() as \$p) {
            \$n = \$p->getName();
            if (\$r2->hasProperty(\$n)) {
                \$this->\$n = \$this->___model->___getEloquentProperty(\$n, \$this->___model);
            }
        }
    }

    public function __getPropel()
    {
        return \$this->___model;
    }

    public function setRawAttributes(array \$attributes, \$sync = false)
    {
        parent::setRawAttributes(\$attributes, \$sync);
        \$this->___model->___fill(\$attributes);
    }

    public function ___setModel(\\{$namespace}\\{$class} \$model)
    {
        \$this->___model = \$model;
    }

    public function _set(\$key, \$value)
    {
        if (!\$this->___alreadyInSet) {
            \$this->___alreadyInSet = true;
            if (!\$this->___model->___alreadyInSet) {
                \$this->___model->\$key = \$value;
            } else {
                parent::setAttribute(\$key, \$value);
            }
            \$this->___alreadyInSet = false;
        } else {
            parent::setAttribute(\$key, \$value);
        }
    }

    public function save(array \$options = [])
    {
        \$saved = parent::save(\$options);
        if (\$saved && \$this->___model) {
            \$pk = \$this->primaryKey;
            \$this->___alreadyInSet = true;
            \$this->___model->\$pk = \$this->\$pk;
            \$this->___alreadyInSet = false;
            \$this->___model->setNew(false);
            \$this->___model->resetModified();
            \$args = func_get_args();
            \$this->___model->reload('false', isset(\$args[1]) ? \$args[1] : null);
        }
    }

    public function delete()
    {
        if (\$this->___model) {
            \$args = func_get_args();
            \$this->___model->delete(isset(\$args[1]) ? \$args[1] : null);
        }
        \$this->exists = false;
    }

    public function  __call(\$name, \$params)
    {
        if (\$this->___model && method_exists(\$this->___model, \$name)) {
            return call_user_func_array([ \$this->___model, \$name ], \$params);
        } else {
            throw new \Exception(\"Method '\$name' does not exist.\");
        }
    }
{$methods}}
";

    }
}