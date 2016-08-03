<?php

namespace MarcosHoo\PropelEloquent\Builder;

use Propel\Generator\Builder\Om\AbstractObjectBuilder;

/**
 *
 * @author marcos
 *
 */
class EloquentBuilder extends AbstractObjectBuilder
{
    /**
     *
     * @var array
     */
    protected $parameters = [];

    /**
     *
     * {@inheritDoc}
     * @see \Propel\Generator\Builder\Om\AbstractOMBuilder::build()
     */
    public function build()
    {
        $parameters = $this->getTable()->getBehavior('eloquent')->getParameters();


        $this->parameters['methods'] = isset($parameters['methods']) ? $parameters['methods'] : '';
        $this->parameters['attributes'] = isset($parameters['atrtributes']) ? $parameters['atrtributes'] : '';

        foreach (['declaredClasses', 'interfaces', 'traits'] as $parameter) {

            if (isset($parameters[$parameter])) {

                foreach (
                    array_filter(
                        explode(',', strtr(
                            str_replace(' as ', '|', $parameters[$parameter]),
                            ["\n" => '', ' ' => '']
                        )),
                        function ($var) {
                            return $var != '';
                        }
                    ) as $i => $className
                ) {

                    $className = trim($className);
                    $info = explode('|', $className);
                    $temp = explode('\\', $info[0]);
                    $alias = isset($info[1]) ? $info[1] : false;
                    $class = $temp[count($temp) - 1];
                    $this->declareClass($info[0], $alias);

                    if ($i === 0) {
                        $this->parameters[$parameter] =
                            $parameter == 'interfaces' || $parameter == 'traits'
                                ? $parameter == 'interfaces'  ? ' implements ' : '    use '
                                : 'use ';
                    } else {
                        $this->parameters[$parameter] .=
                            $parameter == 'interfaces' || $parameter == 'traits'
                                ? ', '
                                : '';
                    }

                    $this->parameters[$parameter] .=
                        $parameter == 'interfaces' || $parameter == 'traits'
                            ? $alias ?: $class
                            : $className . ($alias ? " as {$alias}" : '') . ";\n";
                }

                if (isset($this->parameters[$parameter]) && $parameter == 'traits') {
                    $this->parameters[$parameter] .= ";\n";
                }

            } else {

                $this->parameters[$parameter] = '';

            }

        }

        $this->declareClass($this->getStubObjectBuilder()->getClassName(), 'Propel');
        $this->declareClass('Illuminate\Database\Eloquent\Model');
        $this->declareClass('MarcosHoo\PropelEloquent\Model\Contracts\RequestObjectContract');
        $this->declareClass('MarcosHoo\PropelEloquent\Model\RequestObjectTrait');

        return parent::build();
    }

    /**
     *
     * {@inheritDoc}
     * @see \Propel\Generator\Builder\Om\AbstractOMBuilder::getPackagePath()
     */
    public function getPackagePath()
    {
        return parent::getPackagePath() . '/Eloquent';
    }

    /**
     *
     * {@inheritDoc}
     * @see \Propel\Generator\Builder\Om\AbstractOMBuilder::getNamespace()
     */
    public function getNamespace()
    {
        return parent::getNamespace(). '\Eloquent';
    }

    /**
     * @return string
     */
    public function getUnprefixedClassname()
    {
        return $this->getStubObjectBuilder()->getUnprefixedClassname();
    }

    /**
     *
     * @return string
     */
    public function getTableFullClassName()
    {
        return  parent::getNamespace() . '\\' . $this->getUnprefixedClassname();
    }

    /**
     *
     * {@inheritDoc}
     * @see \Propel\Generator\Builder\Om\AbstractOMBuilder::addClassOpen()
     */
    protected function addClassOpen(&$script)
    {
        $script .= "
/**
 * Eloquent model class for {$this->getUnprefixedClassname()} Propel model.
 *
 * @package {$this->getPackage()}
 */
class " . $this->getUnprefixedClassname() . " extends Model
{
";
    }

    /**
     *
     * @param string $s
     * @return mixed
     */
    public function formatParameter($s) {
        return str_replace('{:nlw:}', '\n', str_replace('\n', "\n", str_replace('\\\\n', '{:nlw:}', $s)));
    }

    /**
     *
     * {@inheritDoc}
     * @see \Propel\Generator\Builder\Om\AbstractOMBuilder::addClassBody()
     */
    protected function addClassBody(&$script)
    {
        $namespace = parent::getNamespace();
        $class = $this->getUnprefixedClassname();

        $behaviors = [];
        foreach ($this->getTable()->getBehaviors() as $behavior) {
            array_push($behaviors, $behavior->getName());
        }

        $table = $this->getTable()->getName();
        $primaryKey = $this->getTable()->getPrimaryKey()[0]->getName();
        $timestamps = in_array('timestampable', array_keys($behaviors));

        $traits = $this->parameters['traits'];
        $attributes = $this->formatParameter($this->parameters['attributes']);
        $methods = $this->formatParameter($this->parameters['methods']);

        $script .= "{$traits}{$attributes}
    /**
     * @var \\{$this->getTableFullClassName()}
     */
    protected \$___model;

    /**
     * @var string
     */
    protected \$table = '{$table}';

    /**
     * @var string
     */
    protected \$primaryKey = '{$primaryKey}';
";

        if (!$timestamps) {
            $script .= "

    /**
     * @var boolean
     */
    protected \$timestamps = false;
";
        }

        $script .= "
    /*
     * @var boolean
     */
    public \$___alreadyInSet = false;

    /**
     *
     */
    public function __construct()
    {
        \$args = func_get_args();
        if (isset(\$args[0]) && \$args[0] instanceof \\{$this->getTableFullClassName()}) {
            \$this->___model = \$args[0];
            \$args = null;
        } else {
            \$this->___model = \$this->___model ?: new \\{$this->getTableFullClassName()}(\$this);
        }
        \$this->___init();
        if (isset(\$args[0])) {
            parent::__construct(\$args[0]);
        } else {
            parent::__construct();
        }
    }

    /**
     *
     */
    protected function ___init()
    {
        \$r1 = new \ReflectionClass(\$this);
        \$r1 = \$r1->getParentClass();
        \$r2 = new \ReflectionClass(\$this->___model);
        foreach (\$r1->getProperties() as \$p) {
            \$n = \$p->getName();
            if (\$r2->hasProperty(\$n)) {
                \$this->\$n = \$this->___model->___getEloquentProperty(\$n);
            }
        }
    }

    /**
     *
     */
    public function setRawAttributes(array \$attributes, \$sync = false)
    {
        parent::setRawAttributes(\$attributes, \$sync);
        \$this->___model->___fill(\$attributes, true, true);
    }

    /**
     *
     */
    public function ___getModel()
    {
        return \$this->___model;
    }

    /**
     *
     */
    public function ___setModel(\\{$namespace}\\{$class} \$model)
    {
        \$this->___model = \$model;
    }

    /**
     *
     */
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

    /**
     *
     */
    public function setAttribute(\$key, \$value)
    {
        if (!\$this->___alreadyInSet) {
            \$this->___alreadyInSet = true;
            parent::setAttribute(\$key, \$value);
            \$this->___model->\$key = \$value;
            \$this->___alreadyInSet = false;
        }
        return \$this;
    }

    /**
     *
     */
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

    /**
     *
     */
    public function delete()
    {
        if (\$this->___model) {
            \$args = func_get_args();
            \$this->___model->delete(isset(\$args[1]) ? \$args[1] : null);
        }
        \$this->exists = false;
    }

    /**
     *
     */
    public function  __call(\$name, \$params)
    {
        if (\$this->___model && method_exists(\$this->___model, \$name)) {
            return call_user_func_array([ \$this->___model, \$name ], \$params);
        } else {
            return parent::__call(\$name, \$params);
        }
    }

    /**
     *
     */
    public static function all(\$columns = ['*']) {
        return self::___static(parent::all(\$columns));
    }

    /**
     *
     */
    public static function __callStatic(\$method, \$parameters)
    {
        \$instance = new static;

        return static::___static(call_user_func_array([\$instance, \$method], \$parameters));
    }

    /**
     *
     */
    protected static function ___static(\$res)
    {
        \$test = (\$res instanceOf EloquentModel) ? \$res : ((\$res instanceOf EloquentCollection) ? \$res[0] : null);
        if (\$test && method_exists(\$test, '___getModel')) {
            \$cl = true;
            if (\$res instanceOf EloquentModel) {
                \$res = [\$res];
                \$cl = false;
            }
            if (\$cl) {
                \$pcl = new \\Propel\\Runtime\\Collection\\ObjectCollection;
            }
            foreach (\$res as \$m) {
                \$m = \$m->___getModel();
                if (\$cl) {
                    \$pcl->append(\$m);
                } else {
                    return \$m;
                }
            }
            return \$pcl;
        } else {
            return \$res;
        }
    }
{$methods}
";
        $script .= "";
    }

    /**
     *
     * {@inheritDoc}
     * @see \Propel\Generator\Builder\Om\AbstractOMBuilder::addClassClose()
     */
    protected function addClassClose(&$script)
    {
        $script .= "
}
";
    }
}
