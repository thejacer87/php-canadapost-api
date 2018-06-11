<?php

namespace CanadaPost;

use DOMDocument;
use DOMNode;

interface NodeInterface
{
    /**
     * @param null|DOMDocument $document
     *
     * @return DOMNode
     */
    public function toNode(DOMDocument $document = null);
}
