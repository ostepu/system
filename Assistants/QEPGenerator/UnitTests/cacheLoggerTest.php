<?php

include_once dirname(__FILE__) . '/../cacheLogger.php';

/**
 * Generated by PHPUnit_SkeletonGenerator on 2017-12-18 at 22:41:58.
 */
class cacheLoggerTest extends PHPUnit_Framework_TestCase {
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        cacheLogger::disableLog();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
        cacheLogger::disableLog();        
    }

    /**
     * @covers cacheLogger::enableLog
     */
    public function testEnableLog() {
        cacheLogger::enableLog();
    }

    /**
     * @covers cacheLogger::disableLog
     * @todo   Implement testDisableLog().
     */
    public function testDisableLog() {
        cacheLogger::disableLog();
    }

    /**
     * @covers cacheLogger::Log
     */
    public function testLog() {
        cacheLogger::disableLog();
        cacheLogger::Log('unitTestDisabled');
        cacheLogger::enableLog();
        cacheLogger::Log('unitTestEnabled');
        cacheLogger::Log('unitTestEnabled', 'unitTestName');
        cacheLogger::Log('unitTestEnabled', 'unitTestName', LogLevel::ERROR);
    }

    /**
     * @covers cacheLogger::LogError
     */
    public function testLogError() {
        cacheLogger::LogError('unitTestErrorText');
        cacheLogger::LogError('unitTestErrorText', 'unitTestErrorName');
    }

}