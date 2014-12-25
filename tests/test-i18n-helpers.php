<?php

use Fabschurt\WpTweaks\Tests\WpTestCase;
use Symfony\Component\Process\Process;

class AssetsHelpersTest extends WpTestCase
{
    protected function installPolylang()
    {
        $process = new Process('./vendor/bin/wp plugin install polylang --activate');
        $process->mustRun();
    }

    protected function removePolylang()
    {
        $process = new Process('./vendor/bin/wp plugin uninstall polylang');
        $process->mustRun();
    }
}
