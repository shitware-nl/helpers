<?php

namespace Rsi;

/**
 *  XML helper functions.
 */
class Xml{

  /**
   *  Check wether a name is a valid XML tag name.
   *  @param string $name  Tag name.
   *  @return bool  True if valid.
   */
  public static function validTag($name){
    return !preg_match('/(^[\\.\\-\\d]|^xml|[' . preg_quote('!"#$%&\'()*+,/;<=>?@[\\]^`{|}~','/') . '\\s])/i',$name);
  }
  /**
   *  Covert a DOM node to a (simple) XML node.
   *  @param \DOMNode $node
   *  @param string $class_name  Class name for the XML object (descendant of SimpleXMLElement).
   *  @return SimpleXMLElement
   */
  public static function fromDom($node,$class_name = 'SimpleXMLElement'){
    return simplexml_import_dom((new \DomDocument())->importNode($node,true),$class_name);
  }

}