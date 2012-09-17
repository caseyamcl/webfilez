<?php

namespace Reqresp;

/**
 * ErrorWrapper Class converts PHP errors into exceptions
 */
class ErrorWrapper
{
    // --------------------------------------------------------------

    /**
     * Optionally statically invoke the ErrorWrapper
     *
     * @param boolean $reportingOff
     * If true, will manaully shutoff onscreen error reporting
     */
    public static function invoke($reportingOff = false)
    {
        $that = new ErrorWrapper();
        return $that->setup();
    }

    // --------------------------------------------------------------

    /**
     * Setup the error handling
     *
     * @param boolean $reportingOff
     * @return \Reqresp\ErrorWrapper
     */
    public function setup($reportingOff = false)
    {
        //Set it up
        if ($reportingOff) {
            ini_set('display_errors', 'Off');
            error_reporting(-1);
        }

        //Set the error handler to this
        set_error_handler(array($this, 'handleError'));

        //Register a shutdown function
        //register_shutdown_function(array($this, 'handleShutdown'));

        return $this;
    }

    // --------------------------------------------------------------

    /**
     * Restore normal error handling upon destruction
     */
    public function __destruct()
    {
        restore_error_handler();
        restore_exception_handler();
    }

    // --------------------------------------------------------------

    /**
     * Callback to handle regular PHP errors
     */
    public function handleError($errno, $errstr, $errfile, $errline, $errcontext = null)
    {
        throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
    }

    // --------------------------------------------------------------

    /**
     * Callback to handle PHP Shutdown errors
     */
    public function handleShutdown()
    {
        $error = error_get_last();

        if ($error) {
            call_user_func(array($this, 'handleError'), $error['type'], $error['message'], $error['file'], $error['line']);
        }
    }
}

/* EOF: ErrorWrapper */