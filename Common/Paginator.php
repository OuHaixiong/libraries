<?php

class Common_Paginator extends Zend_Paginator{
	
	public $urlvar =array();
	public $debug;
	public $mordedebug =FALSE;
	public $select;
	public function getUrl($page,$params=array()){

		$p=$this->urlvar;
		$p['page']=$page;
		$p=array_merge($p,$params);
		return $p;
	}
	public function debug(){
		if($this->debug)
		{ //return;
			if ($this->debug===2){{
				foreach ($this->profiler->getQueryProfiles() as $query)
				P_Putils_Common :: pr ( $query->getQuery()->__toString(),"");
			}
			}else{
			
			if(is_array($this->select))
			{
				var_dump($this->select);
			}  
			else
			{
				//echo $this->select; pr
				Putils_Common :: pr ( $this->select->__toString() , "<b style = 'color:blue' >jim</b>:注意查看sql!"  );
			}
			}//exit;
		}
	
	
	}
	public static function factory($data, $adapter = self::INTERNAL_ADAPTER,array $prefixPaths = null)
	{
		if ($data instanceof Zend_Paginator_AdapterAggregate) {
			return new self($data->getPaginatorAdapter());
		} else {
			if ($adapter == self::INTERNAL_ADAPTER) {
				if (is_array($data)) {
					$adapter = 'Array';
				} else if ($data instanceof Zend_Db_Table_Select) {
					$adapter = 'DbTableSelect';
				} else if ($data instanceof Zend_Db_Select) {
					$adapter = 'DbSelect';
				} else if ($data instanceof Iterator) {
					$adapter = 'Iterator';
				} else if (is_integer($data)) {
					$adapter = 'Null';
				} else {
					$type = (is_object($data)) ? get_class($data) : gettype($data);
	
					/**
					 * @see Zend_Paginator_Exception
					 */
					require_once 'Zend/Paginator/Exception.php';
	
					throw new Zend_Paginator_Exception('No adapter for type ' . $type);
				}
			}
	
			$pluginLoader = self::getAdapterLoader();
	
			if (null !== $prefixPaths) {
				foreach ($prefixPaths as $prefix => $path) {
					$pluginLoader->addPrefixPath($prefix, $path);
				}
			}
	
			$adapterClassName = $pluginLoader->load($adapter);
	
			return new self(new $adapterClassName($data));
		}
	}
	
}