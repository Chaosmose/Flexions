<?php

/*

Created by Benoit Pereira da Silva on 20/04/2013.
Copyright (c) 2013  http://www.pereira-da-silva.com

This file is part of Flexions

Flexions is free software: you can redistribute it and/or modify
it under the terms of the GNU LESSER GENERAL PUBLIC LICENSE as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Flexions is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU LESSER GENERAL PUBLIC LICENSE for more details.

You should have received a copy of the GNU LESSER GENERAL PUBLIC LICENSE
along with Flexions  If not, see <http://www.gnu.org/licenses/>

*/

/**
 * @author bpds
 */
class Flexed {
	
	/**
	 * Populated by the template
	 * @var string
	 */
	var $package;
	
	/**
	 * Populated by the template
	 * @var string
	 */
	var $fileName=NULL;
	
	
	/**
	 * Generated source
	 * @var string
	 */
	var $source;

	
	/**
	 * @var string
	 */
	var $packagePath=NULL ;
	

	/**
	 * @var string
	 */
	var $description;
	
	/**
	 * @var array
	 */
	var $metadata=array();

	

	/**
	 * You can mark a flexed to be excluded.
	 * @var boolean
	 */
	var $exclude=false;
	
	
	
	// ######################


	/**
	 * Can be used to prefix a class for example
	 * @var string
	 */
	var $prefix="";

	/**
	 * 
	 * @var string
	 */
	var $projectName="PROJECT NAME";
	
	/**
	 * 
	 * @var string
	 */
	var $company="COMPANY";
	
	/**
	 * 
	 * @var string
	 */
	var $year="2013";
	
	 /**
	  * 
	  * @var string
	  */
	var $author="flexions@pereira-da-silva.com";
	
	
	/**
	 * @var string
	 */
	var $license;
	

	
	
}

?>