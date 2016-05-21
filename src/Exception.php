<?php // vim:ts=4:sw=4:et:fdm=marker

namespace atk4\core;

/**
 * All exceptions generated by Agile Toolkti will use this class
 *
 * @license MIT
 * @copyright Agile Toolkit (c) http://agiletoolkit.org/
 */
class Exception extends \Exception
{
    /**
     * Most exceptions would be a cause by some other exception, DSQL
     * will encapsulate them and allow you to access them anyway.
     */
    private $params = [];

    public function __construct(
        $message = "",
        $code = 0,
        \Throwable $previous = null
    ) {
        if (is_array($message)) {
            // message contain additional parameters
            $this->params = $message;
            $message = array_shift($this->params);
        }

        parent::__construct($message, $code, $previous);
    }

    /**
     * Follow the getter-style of PHP Exception
     */
    public function getParams()
    {
        return $this->params;
    }
}