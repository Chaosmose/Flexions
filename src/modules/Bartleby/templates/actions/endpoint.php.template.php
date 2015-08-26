<?php

require_once FLEXIONS_MODULES_DIR . '/Bartleby/templates/Requires.php';

/* @var $f Flexed */
/* @var $d ActionRepresentation*/

if (isset ( $f )) {
    $classNameWithoutPrefix=$h->ucFirstRemovePrefixFromString($d->class);
    $callDataClassName=$classNameWithoutPrefix.'CallData';
    $f->fileName = $classNameWithoutPrefix.'.php';
    $f->package = 'php/api/'.$h->majorVersionPathSegmentString().'generated/endpoints/';
}

/* TEMPLATES STARTS HERE -> */?>
<?php echo '<?php'?>
<?php echo GenerativeHelperForPhp::defaultHeader($f,$d); ?>

namespace Bartleby\EndPoints;

require_once BARTLEBY_ROOT_FOLDER . 'mongo/MongoEndPoint.php';
require_once BARTLEBY_ROOT_FOLDER . 'mongo/MongoCallDataRawWrapper.php';

use Bartleby\Mongo\MongoEndPoint;
use bartleby\mongo\MongoCallDataRawWrapper;
use Bartleby\Core\JsonResponse;
use \MongoCollection;

class  <?php echo $callDataClassName; ?> extends MongoCallDataRawWrapper {
<?php
$name=null;
$parameterIsAcollection=false;
while ($d->iterateOnParameters() === true) {
    $parameter = $d->getParameter();
    $name=$parameter->name;
    $typeOfProp=$parameter->type;
    $o=FlexionsTypes::OBJECT;
    $c=FlexionsTypes::COLLECTION;
    $parameterIsAcollection=($typeOfProp===$c);
    if (($typeOfProp===$o)||($typeOfProp===$c)) {
        $typeOfProp = $h->ucFirstRemovePrefixFromString($parameter->instanceOf);
        if($typeOfProp==$c){
            $typeOfProp=' array of '.$typeOfProp;
        }
    }

    if($parameter->type==FlexionsTypes::ENUM){
        $enumTypeName=ucfirst($name);
        $typeOfProp=$parameter->instanceOf.' '.$typeOfProp;
        echoIndentCR('// Enumeration of possibles values of '.$name, 1);
        foreach ($parameter->enumerations as $element) {
            if($parameter->instanceOf==FlexionsTypes::STRING){
                echoIndentCR('const ' .$enumTypeName.'_'.ucfirst($element).' = "'.$element.'";' ,1);
            }else{
                echoIndentCR('const ' .$enumTypeName.'_'.ucfirst($element).' = '.$element.';', 1);
            }
        }
    }
    if(isset($parameter->description) && strlen($parameter->description)>1){
        echoIndentCR('/* '.$parameter->description.' */',1);
    }

    echoIndentCR('const '.$name.'=\''.$name.'\';',1);
}
?>
}

 class  <?php echo $classNameWithoutPrefix; ?> extends MongoEndPoint {
<?php


// We use the last and unique parameter for CRUD endpoints (ids based)
// If there is no parameters it means it is a generic Get endpoint based on request.

$lastParameterName=isset($name)?$name:'NO_PARAMETERS';
$isGenericGETEndpoint=(($d->httpMethod=='GET')&& ($lastParameterName=='NO_PARAMETERS'));
$parameterIsNotAcollection=(!$parameterIsAcollection);
if ($isGenericGETEndpoint==true){
   $parameterIsNotAcollection=false;
}


if($d->httpMethod=='POST') {
    echo('
    function call('.$callDataClassName.' $parameters) {
        $db=$this->getDb();
        /* @var \MongoCollection */
        $collection = $db->'.$d->collectionName.';
        // Default write policy
        $options = array (
            "w" => 1,
            "j" => true
        );
        '.
        (
        ($parameterIsNotAcollection)?
            '$obj=$parameters->getValueForKey('.$callDataClassName.'::'.$lastParameterName.');'
            :
            '$obj=$parameters->getValueForKey('.$callDataClassName.'::'.$lastParameterName.');'
        )
        .'
        if(!isset($obj)){
          return new JsonResponse(\'Void submission\',400);
        }
        try {
            '.(($parameterIsNotAcollection)?
            '$r = $collection->insert ( $obj,$options );'
            :
            '$r = $collection->batchInsert( $obj,$options );'
        ).'
             if ($r[\'ok\']==1) {
                return new JsonResponse(NULL,200);
            } else {
                return new JsonResponse($r,412);
            }
        } catch ( \Exception $e ) {
            return new JsonResponse( array (\'code\'=>$e->getCode(),
                                            \'message\'=>$e->getMessage(),
                                            \'file\'=>$e->getFile(),
                                            \'line\'=>$e->getLine(),
                                            \'trace\'=>$e->getTraceAsString()
                                            ),
                                            417
                                    );
        }
        return new JsonResponse(NULL, 200);
     }'
    );
}elseif ($d->httpMethod=='GET'){

    echo('
     function call('.$callDataClassName.' $parameters) {
        $db=$this->getDb();
        /* @var \MongoCollection */
        $collection = $db->'.$d->collectionName.';
        // result fields
        $f=$parameters->getValueForKey(MongoCallDataRawWrapper::result_fields);
        '.
    (

             ($parameterIsNotAcollection)?

        '$q = array (\'_id\' =>$parameters->getValueForKey('.$callDataClassName.'::'.$lastParameterName.'));'

            :
        ''.
    (

            ($isGenericGETEndpoint)?
        '//we use the parametric query
        $q=$parameters->getValueForKey(MongoCallDataRawWrapper::query);
        if (isset($q)&& count($q)>0){
        }else{
            return new JsonResponse(\'Query is void\',412);
        }'
            :

        '$ids=$parameters->getValueForKey('.$callDataClassName.'::'.$lastParameterName.');
        if(isset ($ids) && count($ids)){
            $q = array( \'_id\'=>array( \'$in\' => $ids ));
        }else{
            return new JsonResponse(NULL,204);
        }'
    )

)
        .'
        try {'.
    (

        ($parameterIsNotAcollection)?'

           if(isset($f)){
                $r = $collection->findOne( $q , $f );
           }else{
                $r = $collection->findOne($q);
           }
            if (isset($r)) {
                return new JsonResponse($r,200);
            } else {
                return new JsonResponse(NULL,404);
            }'

            :

            '
           $r=array();
           if(isset($f)){
                $cursor = $collection->find( $q , $f );
           }else{
                $cursor = $collection->find($q);
           }
           // Sort ?
           $s=$parameters->getValueForKey(MongoCallDataRawWrapper::sort);
           if (isset($s) && count($s)>0){
              $cursor=$cursor->sort($s);
           }
           if ($cursor->count ( TRUE ) > 0) {
			foreach ( $cursor as $obj ) {
				$r [] = $obj;
			}
		   }

            if (count($r)>0 ) {
                return new JsonResponse($r,200);
            } else {
                return new JsonResponse(NULL,404);
            }'
    )
            .'
       } catch ( \Exception $e ) {
            return new JsonResponse( array (\'code\'=>$e->getCode(),
                                            \'message\'=>$e->getMessage(),
                                            \'file\'=>$e->getFile(),
                                            \'line\'=>$e->getLine(),
                                            \'trace\'=>$e->getTraceAsString()
                                            ),
                                            417
                                    );
        }
        return new JsonResponse(NULL, 200);
     }');
}elseif ($d->httpMethod=='PUT'){
    echo('
    function call('.$callDataClassName.' $parameters) {
        $db=$this->getDb();
        /* @var \MongoCollection */
        $collection = $db->'.$d->collectionName.';
        // Default write policy
        $options = array (
            "w" => 1,
            "j" => true
        );
        '.
        (
        ($parameterIsNotAcollection)?
        '$obj=$parameters->getValueForKey('.$callDataClassName.'::'.$lastParameterName.');
        if(!isset($obj)){
          return new JsonResponse(\'Invalid void object\',406);
        }
        $q = array (\'_id\' =>$obj[\'_id\']);'
            :
            '$arrayOfObject=$parameters->getValueForKey('.$callDataClassName.'::'.$lastParameterName.');
        if(!isset($arrayOfObject) || (is_array($arrayOfObject) && count($arrayOfObject)<1) ){
            return new JsonResponse(\'Invalid void array\',406);
        }'
        )
        .'
        try {
            '.(($parameterIsNotAcollection)?
            '$r = $collection->update ($q, $obj,$options );
            if ($r[\'ok\']==1) {
              if(array_key_exists(\'updatedExisting\',$r)){
                    $existed=$r[\'updatedExisting\'];
                    if($existed==true){
                        return new JsonResponse(NULL,200);
                    }else{
                        return new JsonResponse(NULL,404);
                    }
                }
                return new JsonResponse(NULL,200);
            } else {
                return new JsonResponse($r,412);
            }'
            :
            'foreach ($arrayOfObject as $obj){
                $q = array (\'_id\' => $obj[\'_id\']);
                $r = $collection->update( $q, $obj,$options);
                if ($r[\'ok\']==1) {
                    if (array_key_exists(\'updatedExisting\', $r)) {
                        $existed = $r[\'updatedExisting\'];
                        if ($existed == false) {
                            return new JsonResponse($q,404);
                        }
                    }
                }else{
                    return new JsonResponse($q,412);
                }
             }
            return new JsonResponse(NULL,200);'
        ).'

        } catch ( \Exception $e ) {
            return new JsonResponse( array (\'code\'=>$e->getCode(),
                                            \'message\'=>$e->getMessage(),
                                            \'file\'=>$e->getFile(),
                                            \'line\'=>$e->getLine(),
                                            \'trace\'=>$e->getTraceAsString()
                                            ),
                                            417
                                    );
        }
        return new JsonResponse(NULL, 200);
     }'
    );
}elseif ($d->httpMethod=='DELETE'){
    // DELETE
    echo('
    function call('.$callDataClassName.' $parameters) {
        $db=$this->getDb();
        /* @var \MongoCollection */
        $collection = $db->'.$d->collectionName.';
        // Default write policy
        $options = array (
            "w" => 1,
            "j" => true
        );
        '.
    (
        ($parameterIsNotAcollection)?

            '$q = array (\'_id\' =>$parameters->getValueForKey('.$callDataClassName.'::'.$lastParameterName.'));'

            :

            '$ids=$parameters->getValueForKey('.$callDataClassName.'::'.$lastParameterName.');
        if(isset ($ids) && count($ids)>0){
            $q = array( \'_id\' =>array( \'$in\' => $ids ));
        }else{
            return new JsonResponse(NULL,204);
        }'

    )
        .'
        try {
            $r = $collection->remove ( $q,$options );
             if ($r[\'ok\']==1) {
                 if($r[\'n\']>=1){
                     return new JsonResponse(NULL,200);
                 }else{
                     return new JsonResponse(NULL,404);
                 }
            } else {
                return new JsonResponse($r,412);
            }
        } catch ( \Exception $e ) {
            return new JsonResponse( array (\'code\'=>$e->getCode(),
                                            \'message\'=>$e->getMessage(),
                                            \'file\'=>$e->getFile(),
                                            \'line\'=>$e->getLine(),
                                            \'trace\'=>$e->getTraceAsString()
                                            ),
                                            417
                                    );
        }
        return new JsonResponse(NULL, 200);
     }'
    );
}else{
    echo('// STRANGE METHOD '.$d->httpMethod);
}
?>

 }

<?php echo '?>'?><?php /*<- END OF TEMPLATE */?>