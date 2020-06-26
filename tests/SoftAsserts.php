<?php
/**
 * Created by PhpStorm.
 * User: andrewcraver
 * Date: 4/20/16
 * Time: 1:39 PM
 */

namespace softAssert;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\AssertionFailedError;

/**
 * Class SoftAssert
 * @package softAssert
 */
class SoftAssert
{
    /**
     * soft assert error array property.
     * @var array
     */
    private $assertion_failed_errors = array();

    /**
     * can be used for any assertion, takes variable args depending on assertion being called.
     * example call: $this->softAssert('assertEquals', 1.1, 1.2, 'custom message', 0.1).
     * @author acraver
     * @param $assertion
     * @param array ...$args
     * @throws \Exception
     */
    public function assert($assertion, ...$args)
    {
        if (method_exists(Assert::class, $assertion)) {
            try {
                Assert::$assertion(...$args);
            } catch (AssertionFailedError $e) {
                $this->formatPushSoftAssertError($e);
            }
        } else {
            throw new \Exception("$assertion is not a valid assertion type!");
        }
    }

    /**
     * formats the error by taking message and stack trace and pushing to error array.
     * @author acraver
     * @param AssertionFailedError $e
     */
    private function formatPushSoftAssertError(AssertionFailedError $e)
    {
        $message = rtrim($e->getMessage(), "\n");
        $trace = $e->getTraceAsString();
        $start = strpos($trace, __FILE__);
        $start = strpos($trace, ' /', $start);
        $end = strpos($trace, ':', $start);
        $trace = substr($trace, $start, $end - $start);
        $this->assertion_failed_errors[] = $message . "\n" . $trace;
    }

    /**
     * throws exception with all failures if they exist.
     * call this function at end of test.
     * @author acraver
     */
    public function assertAll()
    {
        if (!empty($this->assertion_failed_errors)) {
            $i = 1;
            $errorsString = "The following asserts failed:\n\n";
            foreach ($this->assertion_failed_errors as $err) {
                $errorsString .= "$i) $err\n\n";
                $i++;
            }
            throw new AssertionFailedError("Test FAILED\n\n$errorsString");
        }
    }
}