<?php

namespace Tests;

use Derekmd\Dusk\Concerns\TogglesHeadlessMode;
use Derekmd\Dusk\Firefox\SupportsFirefox;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Firefox\FirefoxOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Laravel\Dusk\TestCase as BaseTestCase;

abstract class DuskTestCase extends BaseTestCase
{
    use CreatesApplication, SupportsFirefox, TogglesHeadlessMode;

    /**
     * Prepare for Dusk test execution.
     *
     * @beforeClass
     * @return void
     */
    public static function prepare()
    {
        if (static::runningFirefoxInSail()) {
            return;
        }

        if (env('DUSK_CHROME')) {
            static::startChromeDriver();
        } else {
            static::startFirefoxDriver();
        }
    }

    /**
     * Create the RemoteWebDriver instance.
     *
     * @return \Facebook\WebDriver\Remote\RemoteWebDriver
     */
    protected function driver()
    {
        if (env('DUSK_CHROME')) {
            return $this->chromeDriver();
        }

        return $this->firefoxDriver();
    }

    /**
     * Create the ChromeDriver instance.
     *
     * @return \Facebook\WebDriver\Remote\RemoteWebDriver
     */
    protected function chromeDriver()
    {
        $driver_args = env("DUSK_HEADLESS") ? ['--disable-gpu', '--headless', '--no-sandbox', '--window-size=1920,1080',] : 
                                              ['--disable-gpu', '--no-sandbox', '--window-size=1920,1080',];

        $options = (new ChromeOptions)->addArguments($this->filterHeadlessArguments($driver_args));

        // $options = (new ChromeOptions)->addArguments($this->filterHeadlessArguments([
        //     '--disable-gpu',
        //     // '--headless',
        //     '--window-size=1920,1080',
        // ]));

        return RemoteWebDriver::create(
            $_ENV['DUSK_DRIVER_URL'] ?? 'http://localhost:9515',
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY, $options
            )
        );
    }

    /**
     * Create the Geckodriver instance.
     *
     * @return \Facebook\WebDriver\Remote\RemoteWebDriver
     */
    protected function firefoxDriver()
    {
        $driver_args = env("DUSK_HEADLESS") ? ['--headless', '--no-sandbox', '--window-size=1920,1080',] : 
                                              ['--no-sandbox', '--window-size=1920,1080',];

        $capabilities = DesiredCapabilities::firefox();

        $capabilities->getCapability(FirefoxOptions::CAPABILITY)
            ->addArguments($this->filterHeadlessArguments($driver_args))
            ->setPreference('devtools.console.stdout.content', true);

        // $capabilities->getCapability(FirefoxOptions::CAPABILITY)
        //     ->addArguments($this->filterHeadlessArguments([
        //         '--headless',
        //         '--window-size=1920,1080',
        //     ]))
        //     ->setPreference('devtools.console.stdout.content', true);

        return RemoteWebDriver::create(
            $_ENV['DUSK_DRIVER_URL'] ?? 'http://localhost:4444',
            $capabilities
        );
    }
}
