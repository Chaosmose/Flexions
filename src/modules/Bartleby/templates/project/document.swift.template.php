<?php

include  FLEXIONS_MODULES_DIR . '/Bartleby/templates/localVariablesBindings.php';
require_once FLEXIONS_MODULES_DIR . '/Bartleby/templates/Requires.php';
require_once FLEXIONS_MODULES_DIR . '/Languages/FlexionsSwiftLang.php';
require_once FLEXIONS_MODULES_DIR . '/Bartleby/templates/project/SwiftDocumentConfigurator.php';

/*
 * This template is an advanced template that must be configured
 * to be used multiple time within the same project
 * You must declare $configurator a SwitfDocumentConfigurator instance its before invocation.
 */

/* @var $f Flexed */
/* @var $d ProjectRepresentation */
/* @var $project ProjectRepresentation */
/* @var $action ActionRepresentation*/
/* @var $entity EntityRepresentation */
/* @var $configurator SwiftDocumentConfigurator */

if (isset ( $f ) && isset($configurator)) {
    // We determine the file name.
    $f->fileName = $configurator->filename;
    // And its package.
    $f->package = 'xOS/';
}else{
    return 'THIS TEMPLATES REQUIRES A SwitfDocumentConfigurator IN $configurator';
}

if (!isset($isIncludeInBartlebysCommons)) {
    $isIncludeInBartlebysCommons=false;
}

$project=$d;// It is a project template

//Collection controllers are related to actions.

// Include block
include  dirname(__DIR__).'/blocks/BarltebysIncludesBlock.swift.php';

/* TEMPLATES STARTS HERE -> */?>
//
//  <?php echo($configurator->filename.cr()) ?>
//
//  The is the central piece of the Document oriented architecture.
//  We provide a universal implementation with conditionnal compilation
//
//  The document stores references to Bartleby's style ManagedCollections.
//  This allow to use intensively bindings and distributed data automation.
//  With the mediation of standard Bindings approach with NSArrayControler
//
//  We prefer to centralize the complexity of data handling in the document.
//  Thats why for example we implement projectBindingsArrayController.didSet with an CGD dispatching
//  We could have set the binding programmatically in the WindowController
//  But we consider for clarity that the Storyboarded Bindings Settings should be as exhaustive as possible.
//  And the potential complexity masked.
//
//  Generated by flexions
//

import Foundation

#if os(OSX)
import AppKit
#else
import UIKit
#endif

import Foundation
#if !USE_EMBEDDED_MODULES
<?php echo $includeBlock ?>
#endif

@objc(<?php echo($configurator->getClassName())?>) open class <?php echo($configurator->getClassName())?> : <?php
    if ($isIncludeInBartlebysCommons==true){
        echo('BXDocument,BoxDelegate');
    }else{
        echo('BartlebyDocument');
    }
?> {

<?php
if ($isIncludeInBartlebysCommons){
    include __DIR__ . '/document.blocks/bartlebyDocument.base.swift.php';
}else{
}
    include __DIR__ . '/document.blocks/documentCollectionInitializer.swift.php';


?>
    // MARK: - Collection Controllers

    // The initial instances are proxies
    // On document deserialization the collection are populated.

<?php echo($collectionInitializationBlock); ?>

    // MARK: - Schemas

    /**

    In this func you should :

    #1  Define the Schema
    #2  Register the collections (by calling registerCollections())

    */
<?php

if (!$isIncludeInBartlebysCommons){
    echoIndent("override open func configureSchema(){",1);
    echoIndent('// #1  Defines the Schema',2);
    echoIndent('super.configureSchema()',2);
} else{
    echoIndent('open func configureSchema(){',1);
}


foreach ($project->entities as $entity) {
    if ($configurator->managedCollectionShouldBeSupportedForEntity($project,$entity) && !$entity->isUnManagedModel()){
        $pluralizedEntity=Pluralization::pluralize($entity->name);
        $arrayControllerClassName=ucfirst($pluralizedEntity).'ArrayController';
        $arrayControllerVariableName=lcfirst($pluralizedEntity).'ArrayController';
        $entityDefinition=lcfirst($entity->name).'Definition';
        echoIndent('

        let '.$entityDefinition.' = CollectionMetadatum()
        '.$entityDefinition.'.proxy = self.'.lcfirst($pluralizedEntity).'
        '.$entityDefinition.'.collectionName = '.$entity->name.'.collectionName
        '.$entityDefinition.'.storage = CollectionMetadatum.Storage.monolithicFileStorage
        '.$entityDefinition.'.persistsDistantly = '. (($entity->isDistantPersistencyOfCollectionAllowed())? 'true':'false').'
        '.$entityDefinition.'.inMemory = '. (($entity->shouldPersistsLocallyOnlyInMemory())? 'true':'false').'
        ',0);
    }
}
?>

        // Proceed to configuration
        do{

<?php
foreach ($project->entities as $entity) {
    if ($configurator->managedCollectionShouldBeSupportedForEntity($project,$entity) && !$entity->isUnManagedModel()){
        $pluralizedEntity=Pluralization::pluralize($entity->name);
        $arrayControllerClassName=ucfirst($pluralizedEntity).'ArrayController';
        $arrayControllerVariableName=lcfirst($pluralizedEntity).'ArrayController';
        $entityDefinition=lcfirst($entity->name).'Definition';
        echoIndent('try self.metadata.configureSchema('.$entityDefinition.')',3);
    }
}
?>

        }catch DocumentError.duplicatedCollectionName(let collectionName){
            self.log("Multiple Attempt to add the Collection named \(collectionName)",file:#file,function:#function,line:#line,category: Default.LOG_WARNING)
        }catch {
            self.log("\(error)",file:#file,function:#function,line:#line,category: Default.LOG_WARNING)
        }

        // #2 Registers the collections
        do{
            try self.registerCollections()
        }catch{
        }
    }
    <?php

    if ($isIncludeInBartlebysCommons){
        include __DIR__ . '/document.blocks/bartlebyDocument.newEntitiesFactory.block.php';
    }

?>

}
