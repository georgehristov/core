<?php

namespace atk4\core;

trait PluginTrait
{
	use CollectionTrait;
	use DynamicMethodTrait;
		
	/**
     * Check this property to see if trait is present in the object.
     *
     * @var bool
     */
    public $_pluginTrait = true;

    /**
     * Contains information about configured plugins.
     *
     * @var array
     */
    protected $plugins = [];
    
    /**
     * Plugins that are added by default
     * Method addDefaultPlugins should be used for performing the operation
     * 
     * @var array
     */
//     protected $defaultPlugins = [];
    
    /**
     * Array of 'alias' => plugin\class hashes
     *
     * @var array
     */
    protected $pluginAlias = [];
    
    /**
     * Property to facilitate passing on plugin options using atk4 seed array
     * 
     * @var array
     */
    public $pluginOptions = [];    
    
    /**
     * List of disabled plugins
     * 
     * @var array
     */
    public $disablePlugins = [];    

    public function addPlugins($plugins)
    {
    	foreach ((array) $plugins as $class) {
    		$this->addPlugin($class);
    	}
    }
    
    /**
     * Set or get plugin
     * 
     * @param string $name
     * @param object|null $object
     * @throws Exception
     * @return Plugin
     */
    public function addPlugin($class, $defaults = [])
    {
    	if (! is_a($class, Plugin::class, true)) {
    		throw new Exception(['Plugin object must be of class ' . Plugin::class]);
    	}
    		
    	if (is_array($class)) {
    		$defaults = array_slice($class, 1);
    			
    		$class = array_pop($class);
    	}
    		
    	$plugin = is_object($class)? $class: new $class;
    	
    	$class = get_class($plugin);
    	
    	$alias = $plugin->alias?: get_class($plugin);
    	
    	// plugin is disabled
    	if (array_intersect([$alias, $class], (array) $this->disablePlugins)) return;
    	
    	$this->pluginAlias[$alias] = $class;
    		
    	$defaults = $defaults?: ($this->pluginOptions[$alias]?? []);
    	
    	// pass on options of default seed from owner to plugin
    	// e.g $grid->menu will pass options to $plugin->seeds['menu'] to be used for menu seed creation
    	$defaults = array_merge(['seeds' => [$alias => $this->{$alias}?? []]], $defaults);
    	
    	$plugin->setDefaults($defaults);

    	return $this->_addIntoCollection($class, $plugin->addTo($this), 'plugins');
    }
    
    public function addDefaultPlugins()
    {
    	foreach ((array) ($this->defaultPlugins?? []) as $pluginClass) {
    		$this->addPlugin($pluginClass);
    	}
    	
    	return $this;
    }
    
    public function getPlugin($aliasOrClass)
    {
    	return $this->_getFromCollection($this->mapPluginClass($aliasOrClass), 'plugins');
    }
    
    public function mapPluginClass($aliasOrClass)
    {
    	return $this->pluginAlias[$aliasOrClass]?? $aliasOrClass;
    }
    
    public function setUrlArgs($args)
    {
    	foreach ((array) $this->plugins as $plugin) {
    		$plugin->setUrlArgs($args);
    	}
    	
    	return $this;
    }
    
    public function getPluginSeed($aliasOrClass, $name = null)
    {
    	if (! $this->hasPlugin($aliasOrClass)) return null;
    	
    	$plugin = $this->getPlugin($aliasOrClass);
    	
    	$name = $name?: $plugin->alias;
    	
    	return $plugin->getSeed($name);
    }
    
    public function removePlugin($aliasOrClass)
    {
    	if ($this->hasPlugin($aliasOrClass)) {
    		$this->getPlugin($aliasOrClass)->deactivate();
    	}
    	
    	return $this->_removeFromCollection($this->mapPluginClass($aliasOrClass), 'plugins');
    }
    
    public function hasPlugin($aliasOrClass)
    {
    	return isset($this->plugins[$this->mapPluginClass($aliasOrClass)]);
    }
}
