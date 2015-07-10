<?php

require_once FLEXIONS_ROOT_DIR . 'flexions/helpers/representations/flexions/FlexionsRepresentationsIncludes.php';
require_once FLEXIONS_ROOT_DIR.'modules/XcDataModelXMLImporter/XcdatamodelXMLToFlexionsRepresentation.class.php';

// we load the shared variables
include  FLEXIONS_SOURCE_DIR.'/variables-for-MPS.php';


$transformer=new XCDDataXMLToFlexionsRepresentation();
// $r is a ProjectRepresentation
$r=$transformer->projectRepresentationFromXcodeModel($descriptorFilePath,$prefix);

// We pass some dummy data to the loop
$r->actions=array(1,2);

$p=array(
    DefaultLoops::ENTITIES=>$r->entities,
    DefaultLoops::ACTIONS=>$r->actions,
    "infos"=>"SOME INFOS"
);

// we instanciate the Hypotypose singleton
$h = Hypotypose::instance();
$h->classPrefix=$r->classPrefix;

/// Associate the entities to the loop name
if(! $h->setLoopDescriptor($r->entities,DefaultLoops::ENTITIES)){
	throw new Exception('Error when setting the loop descriptor '.DefaultLoops::ENTITIES);
}

/// Associate the actions to the loop name
if(! $h->setLoopDescriptor($r->actions,DefaultLoops::ACTIONS)){
	throw new Exception('Error when setting the loop descriptor '.DefaultLoops::ACTIONS);
}

/// Associate the project data to be  the loop name
if(! $h->setLoopDescriptor($p ,DefaultLoops::PROJECT)){
	throw new Exception('Error when setting the loop descriptor '.DefaultLoops::PROJECT);
}