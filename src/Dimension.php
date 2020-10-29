<?php

namespace CanadaPost;

/**
 * Dimensions contains storage locations for width, height, length.
 *
 * @package CanadaPost
 */
class Dimension
{
  /**
   * @var int
   * Longest dimension stored in mm.
   */
  protected $length;

  /**
   * @var int
   * Second longest dimension stored in mm.
   */
  protected $width;

  /**
   * @var int
   * Shortest dimension stored in mm.
   */
  protected $height;

  /**
   * Dimension constructor.
   * @param int $length
   * @param int $width
   * @param int $height
   */
  public function __construct($length, $width, $height) {
    $this->length = $length;
    $this->width = $width;
    $this->height = $height;
  }

  /**
   * @return int
   * Length returned in cm.
   */
  public function getLength() {
    return (float)$this->length / 10;
  }

  /**
   * @param int $length
   * Length provided in mm.
   */
  public function setLength($length) {
    $this->length = $length;
  }

  /**
   * @return float
   * Width returned in cm.
   */
  public function getWidth() {
    return (float)$this->width / 10;
  }

  /**
   * @param int $width
   * Width provided in mm.
   */
  public function setWidth($width) {
    $this->width = $width;
  }

  /**
   * @return int
   * Height returned in cm.
   */
  public function getHeight() {
    return (float)$this->height / 10;
  }

  /**
   * @param int $height
   * Height provided in mm.
   */
  public function setHeight($height) {
    $this->height = $height;
  }
}
