<?php

namespace atk4\core;

 use atk4\ui\View;
	
 abstract class Plugin
{
 	use DIContainerTrait;
 	
 	/**
 	 * Define possible target classes that can use the plugin
 	 * 
 	 * @var string|array
 	 */
 	protected $target = [];
 	
 	/**
 	 * The object that uses the plugin
 	 * 
 	 * @var object
 	 */
 	protected $owner;
 	
 	/**
 	 * List of plugin classes required as dependencies of this plugin
 	 * 
 	 * @var array
 	 */
 	protected $require = [];
 	
 	/**
 	 * List of plugin methods that will be injected in the owner
 	 * 
 	 * @var array
 	 */
 	protected $pluginMethods = [];
 	
 	protected $seeds = [];
 	
 	public function addTo($owner) : self
 	{
 		$this->owner = $owner;
 		
 		$this->checkPluginDependencies();
 		
 		$this->addPluginMethods(); 		
 		
 		$this->activate();
 		
 		return $this;
 	}
 	
 	/**
 	 * Method that contains the functionality of the plugin
 	 */
 	protected function activate() {}
 	
	public function deactivate() : self
	{
		// remove any seeds injected in the owner
		foreach ((array) $this->seeds as $name => $seed) {
			if ($seed instanceof View) {
				$seed->destroy();
			}
			else {
				$this->seeds[$name] = false;
			}
		}
		
		return $this;
	}
    
	public function getSeed($name) 
	{
		return $this->seeds[$name]?? null;
	}
    
	public function setUrlArgs($args) {}
	
	protected function addPluginMethods() : self
	{
		foreach ((array) $this->pluginMethods as $methodName) {
			if (! is_callable($callback = [$this, $methodName])) continue;
			
			// register passively, skip if method exists
			if ($this->owner->hasMethod($methodName)) continue;
			
			$this->owner->addMethod($methodName, $callback);
		}
		
		return $this;
	}
	
	protected function checkPluginDependencies() : self
	{
		// owner must use the PluginTrait
		if (empty($this->owner->_pluginTrait)) {
			throw new Exception([get_class($this->owner) . ' must use PluginTrait to activate plugins']);
		}
		
		// if there is a list of target classes then attempted owner should be in it
		foreach ((array) $this->target as $targetClass) {
			if (is_a($this->owner, $targetClass)) continue;
			
			throw new Exception([get_class($this->owner) . ' is not in the list of target components to use ' . get_class($this)]);
		}
		
		// dependencies on other plugins must be satisfied (they should have been added to the attempted owner)
		foreach ((array) $this->require as $pluginClass) {
			if ($this->owner->hasPlugin($pluginClass)) continue;
			
			throw new Exception([get_class($this) . " plugin depends on $pluginClass to activate in "  . get_class($this->owner)]);
		}
		
		return $this;
	}
	
	protected function setMissingProperty($key, $value)
	{
		// lazy implementation of DIContainer - skip missing properties
		return;
	}
}
