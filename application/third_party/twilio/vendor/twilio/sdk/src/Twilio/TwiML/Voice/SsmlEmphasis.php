<?php

/**
 * This code was generated by
 * \ / _    _  _|   _  _
 * | (_)\/(_)(_|\/| |(/_  v1.0.0
 * /       /
 */

namespace Twilio\TwiML\Voice;

use Twilio\TwiML\TwiML;

class SsmlEmphasis extends TwiML {
    /**
     * SsmlEmphasis constructor.
     *
     * @param string $words Words to emphasize
     * @param array $attributes Optional attributes
     */
    public function __construct($words, $attributes = []) {
        parent::__construct('emphasis', $words, $attributes);
    }

    /**
     * Add Level attribute.
     *
     * @param string $level Specify the degree of emphasis
     */
    public function setLevel($level): self {
        return $this->setAttribute('level', $level);
    }
}