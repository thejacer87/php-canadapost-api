<?php

namespace CanadaPost\Entity;

use DOMDocument;
use DOMElement;
use CanadaPost\NodeInterface;

class Service implements NodeInterface
{
  // Valid domestic values
  const S_EXPEDITED_PARCEL = 'DOM.EP';
  const S_REGULAR_PARCEL = 'DOM.RP';
  const S_PRIORITY = 'DOM.PC';
  const S_XPRESSPOST = 'DOM.XP';

  private static $serviceNames = [
    'DOM.EP' => 'Expedited Parcel',
    'DOM.RP' => 'Regular Parcel',
    'DOM.PC' => 'Priority',
    'DOM.XP' => 'Xpresspost',
  ];

  /**
   * @var string
   */
  private $code = self::S_REGULAR_PARCEL;

  /**
   * @var string
   */
  private $description;

  /**
   * @param null|object $attributes
   */
  public function __construct($attributes = null)
  {
    if (null !== $attributes) {
      if (isset($attributes->Code)) {
        $this->setCode($attributes->Code);
      }
      if (isset($attributes->Description)) {
        $this->setDescription($attributes->Description);
      }
    }
  }

  /**
   * @return array
   */
  public static function getServices()
  {
    return self::$serviceNames;
  }

  /**
   * @param null|DOMDocument $document
   *
   * @return DOMElement
   */
  public function toNode(DOMDocument $document = null)
  {
    if (null === $document) {
      $document = new DOMDocument();
    }

    $node = $document->createElement('Service');
    $node->appendChild($document->createElement('Code', $this->getCode()));
    $node->appendChild($document->createElement('Description', $this->getDescription()));

    return $node;
  }

  /**
   * @return string
   */
  public function getName()
  {
    return self::$serviceNames[$this->getCode()];
  }

  /**
   * @return string
   */
  public function getCode()
  {
    return $this->code;
  }

  /**
   * @param string $code
   *
   * @return $this
   */
  public function setCode($code)
  {
    $this->code = $code;

    return $this;
  }

  /**
   * @return string
   */
  public function getDescription()
  {
    return $this->description;
  }

  /**
   * @param string $description
   *
   * @return $this
   */
  public function setDescription($description)
  {
    $this->description = $description;

    return $this;
  }
}
