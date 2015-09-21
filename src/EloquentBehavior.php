<?php
namespace MarcosHoo\PropelEloquent;

use Propel\Generator\Model\Behavior;

class EloquentBehavior extends Behavior
{
    protected $parameters = [];

    public function objectMethods()
    {
        return '

/**
 *
 * @var \Illuminate\Database\Eloquent\Model
 */
protected $___model;

/**
 *
 * @var array
 */
protected $___args;

/**
 *
 * @var boolean
 */
protected static $___eloquentClassLoaded = false;

/**
 *
 * @return string
 */
public static function ___getEloquentNamespace()
{
    return head(explode(\'\\\\\', static::class));
}

/**
 *
 * @return string
 */
public static function ___getEloquentBaseClassname()
{
    return class_basename(static::class);
}

/**
 * @return string
 */
public static function ___getEloquentClassname()
{
    return self::___getEloquentNamespace() . \'\\\\\' . self::___getEloquentBaseClassname();
}

/**
 *
 * @return string
 */
public static function ___generateEloquent()
{
    $namespace = static::___getEloquentNamespace();
    $class = static::___getEloquentBaseClassname();

    $tablemapClass = static::TABLE_MAP;
    $tablemap = new $tablemapClass;
    $behaviors = $tablemap->getBehaviors();

    $table = constant($tablemapClass . \'::TABLE_NAME\');
    $primaryKey = array_keys($tablemap->getPrimaryKeys())[0];
    $timestamps = in_array(\'timestampable\', array_keys($behaviors));

    $data = "<?php
namespace ${namespace};

class ${class} extends \\Illuminate\\Database\\Eloquent\\Model
{
    protected \$___model;

    protected \$table = \'${table}\';

    protected \$primaryKey = \'${primaryKey}\';

";

            if (!$timestamps) {
                $data .= "
    protected \\$timestamps = false;


";
            }

            $r = new \ReflectionClass(static::class);

            $attribute = $r->getProperty(\'fillable\');
            $classname = static::class;
            $instance = new $classname;

            if ($attribute && ($attribute->isProtected() ||  $attribute->isPublic())) {
                $data .= "    protected \$fillable = [\\n";
                foreach ($instance->fillable as $index => $field) {
                    $data .= "            \'$field\',\\n";
                }
                $data .= "        ];";
            }

            $thisClass = \'\\\\\'. static::class;

            $data .= "

    public function /* */__construct()
    {
        \\$args = func_get_args();
        if (isset(\$args[1])) {
            if (\\$args[1] instanceof ${thisClass}) {
               \\$this->___model = \\$args[0];
            } else {
               parent::__construct(\\$args[0]);
            }
        } else {
            parent::__construct();
        }
        \\$this->___model = \\$this->___model ?: new ${thisClass}(\\$this);
    }

    public function setRawAttributes(array \\$attributes, \\$sync = false)
    {
        parent::setRawAttributes(\\$attributes, \\$sync);
        \\$this->___model->___fill(\\$attributes);
    }

    public function ___setModel(\\\\${classname} \\$model)
    {
        \\$this->___model = \\$model;
    }

    public function setAttribute(\\$key, \\$value)
    {
        \\$this->___model->\\$key = \\$value;
    }

    public function getAttribute(\\$key)
    {
        return \\$this->___model->\\$key;
    }

    public function save(array \\$options = [])
    {
        \\$saved = parent::save(\\$options);
        if (\\$daved && \\$this->___model) {
            \\$this->___model->setNew(false);
            \\$this->___model->resetModified();
            \\$args = func_get_args();
            \\$this->___model->reload(\'false\', isset(\\$args[1]) ?: null);
        }
    }

    public function delete()
    {
        if (\\$this->___model) {
            \\$args = func_get_args();
            \\$this->___model->delete(isset(\\$args[1]) ?: null);
        }
        \\$this->exists = false;
    }

    public function  /* */__call(\\$name, \\$params)
    {
        if (\\$this->___model && method_exists(\\$this->___model, \\$name)) {
            return call_user_func_array([ \\$this->___model, \\$name ], \\$params);
        } else {
            throw new PropelEloquentException(\"Method \'\\$name\' does not exist.\");
        }
    }
}
";

    return preg_replace(\'/\n    /\', "\n", $data);
}

/**
 *
 * @return string
 */
protected static function ___getEloquentFilename($hash)
{
    return storage_path(\'framework/cache\') . \'/\' . $hash . \'.php\';
}

/**
 *
 * @return string
 * @throws PropelEloquentException
 */
protected static function ___generateEloquentFile()
{
    $data = static::___generateEloquent();
    $hash = md5($data);

    $filename = static::___getEloquentFilename($hash);

    if (!file_exists($filename) || is_writable($filename)) {
        $file = touch($filename);

        if (!$file) {
            throw new PropelEloquentException("Can\'t create file \'$filename\'");
        }

        $loader = require base_path(\'vendor/autoload.php\');
        $loader->addClassMap([ static::___getEloquentClassname() => $filename ]);

        if (!$file) {
            throw new PropelEloquentException("Can\'t create file \'$filename\'");
        }

        file_put_contents($filename, $data);
    }

    return $filename;
}

protected static function ___loadEloquentFile()
{
    if (!self::$___eloquentClassLoaded) {
        $filename = static::___generateEloquentFile();
        require_once $filename;
        self::$___eloquentClassLoaded = true;
    }
}

/**
 *
 * @throws PropelEloquentException
 * @return \Illuminate\Database\Eloquent\Model
 */
protected function ___loadEloquentClass($args = null, $connection = null)
{
    if (!$this->___model) {
        static::___loadEloquentFile();
        if (!class_exists($this->___getEloquentClassname())) {
            throw new PropelEloquentException("Class {$this->___getEloquentClassname()} was not loaded from file \'$filename\'");
        }
        $classname = $this->___getEloquentClassname();
        $this->___model = new $classname($this);
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
 * @throws PropelEloquentException
 * @return \Illuminate\Database\Eloquent\Collection|boolean
 */
public function ___callEloquent($name, $params)
{
    switch ($name) {
        case \'hydrate\':
            if (isset($params[1]) && $params[1] instanceof EloquentCollection) {
                return $this->___loadEloquentClass->hydrate($params[0], $params[1]);
            }
            break;
        case \'save\':
            if (isset($params[0]) && is_array($params[0])) {
                return $this->___model->save($params[0]);
            }
    }
    throw new PropelEloquentException();
}

/**
 *
 * @param string $name
 * @param array $params
 */
public static function __callstatic($name, $params)
{
    static::___loadEloquentFile();

    $classname = self::___getEloquentClassname();
    $instance = new $classname;

    return call_user_func_array([ $instance, $name ], $params);
}

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
    $this->setNew(false);
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
    $method = \'set\' . $key;
    if (method_exists($this, $method)) {
        $r = new \ReflectionProperty($this, $method);
        if ($r && $r->isPublic()) {
            $r->invoke($this, $value);
        } else {
            $this->___loadEloquentClass()->setAttribute($key, $value);
        }
    } else {
        $this->___loadEloquentClass()->setAttribute($key, $value);
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
    $method = \'get\' . $key;
    if (method_exists($this, $method)) {
        $r = new \ReflectionProperty($this, $method);
        if ($r && $r->isPublic()) {
            return $r->invoke($this);
        }
    }
    if (property_exists($this, $key)) {
        throw new PropelEloquentException("Property \'$key\' does not exist.");
    }
    return $this->$key;
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
        \$this->___args = func_get_args();
        if (isset(\$this->___args[0])) {
            if (\$this->___args[0] instanceof \Illuminate\Database\Eloquent\Model) {
                \$this->___fill(\$this->___args[0]->getAttributes());
                \$this->___model = \$this->___args[0];
            } elseif (is_array(\$this->___args)) {
                \$this->___loadEloquentClass(\$this->___args);
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
    }
}