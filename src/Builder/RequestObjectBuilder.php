<?php

namespace MarcosHoo\PropelEloquent\Builder;

use Propel\Generator\Builder\Om\AbstractObjectBuilder;

/**
 *
 * @author marcos
 *
 */
class RequestObjectBuilder extends AbstractObjectBuilder
{
    /**
     *
     * {@inheritDoc}
     * @see \Propel\Generator\Builder\Om\AbstractOMBuilder::build()
     */
    public function build()
    {
        $this->declareClass($this->getStubObjectBuilder()->getClassName());
        return parent::build();
    }

    /**
     *
     * {@inheritDoc}
     * @see \Propel\Generator\Builder\Om\AbstractOMBuilder::getPackagePath()
     */
    public function getPackagePath()
    {
        return parent::getPackagePath() . '/Request';
    }

    /**
     *
     * {@inheritDoc}
     * @see \Propel\Generator\Builder\Om\AbstractOMBuilder::getNamespace()
     */
    public function getNamespace()
    {
       return parent::getNamespace(). '\Request';
    }

    /**
     * @return string
     */
    public function getParentClassName()
    {
        return $this->getStubObjectBuilder()->getUnprefixedClassname();
    }

    /**
     *
     * @return string
     */
	public function getUnprefixedClassname()
	{
		return $this->getParentClassName() . 'RequestObject';
	}

	/**
	 *
	 * {@inheritDoc}
	 * @see \Propel\Generator\Builder\Om\AbstractOMBuilder::addClassOpen()
	 */
	protected function addClassOpen(&$script)
	{
		$script .= "

use MarcosHoo\PropelEloquent\Model\Contracts\RequestObjectContract;
use MarcosHoo\PropelEloquent\Model\RequestObjectTrait;

/**
 *
 */
class " . $this->getUnprefixedClassname() . " extends " . $this->getParentClassName() . " implements RequestObjectContract
{
    use RequestObjectTrait;
";
	}

	/**
	 *
	 * {@inheritDoc}
	 * @see \Propel\Generator\Builder\Om\AbstractOMBuilder::addClassBody()
	 */
	protected function addClassBody(&$script)
	{
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
