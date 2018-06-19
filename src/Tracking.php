<?php

namespace CanadaPost;

class Tracking extends ClientBase
{
    public function getSummary($trackingPin)
    {
        $response = $this->get(
            "vis/track/pin/{$trackingPin}/summary",
            ['Accept' => 'application/vnd.cpc.track+xml']
        );
        return $response;
    }
}
