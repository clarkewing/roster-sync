<?php

namespace App\DuskDrivers;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use NunoMaduro\LaravelConsoleDusk\Drivers\Chrome as ConsoleDuskChrome;

class Chrome extends ConsoleDuskChrome
{
    // Overwrite Driver creation to log ALL console message levels.
    public function getDriver()
    {
        $options = (new ChromeOptions())
            ->addArguments(
                array_filter(array_merge(
                    config('laravel-console-dusk.driver.chrome.options', []),
                    [$this->runHeadless()]
                ))
            )
            ->setExperimentalOption('mobileEmulation', [
                'userAgent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36',
            ]);

        $driver = RemoteWebDriver::create(
            $_ENV['DUSK_DRIVER_URL'] ?? 'http://localhost:9515',
            DesiredCapabilities::chrome()
                ->setCapability(
                    ChromeOptions::CAPABILITY,
                    $options,
                )
                ->setCapability('goog:loggingPrefs', ['browser' => 'ALL'])
        );

        $driver->executeCustomCommand(
            '/session/:sessionId/goog/cdp/execute',
            'POST',
            [
                'cmd' => 'Browser.setDownloadBehavior',
                'params' => ['behavior' => 'allow', 'downloadPath' => config('laravel-console-dusk.paths.downloads')],
            ],
        );

        return $driver;
    }
}
