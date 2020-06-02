<?php

declare(strict_types=1);

/**
 * Contao Utilities for Contao Open Source CMS
 * Copyright (c) 2019-2020 Web ex Machina
 *
 * @category ContaoBundle
 * @package  Web-Ex-Machina/contao-utils
 * @author   Web ex Machina <contact@webexmachina.fr>
 * @link     https://github.com/Web-Ex-Machina/contao-utils/
 */

namespace WEM\UtilsBundle\Backend;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * Back end module "smartgear".
 *
 * @author Web ex Machina <https://www.webexmachina.fr>
 */
class Command
{
    /**
     * Execute a command through PHP.
     *
     * @param string $strCmd [Check https://docs.contao.org/dev/reference/commands/ for available commands]
     *
     * @todo Allow the call of other files than contao-console
     * @todo Allow the call of arguments to add (they could be added with the command but maybe it would be more appropriate to have them separated)
     * @todo Handle the system environment
     */
    public static function exec($strCmd): void
    {
        // Finally, clean the Contao cache
        $strConsolePath = \System::getContainer()->getParameter('kernel.project_dir').' /vendor/bin/contao-console';
        $cmd = sprintf(
            'php %s %s --env=prod',
            $strConsolePath,
            $strCmd
        );

        $process = method_exists(Process::class, 'fromShellCommandline') ? Process::fromShellCommandline(
            $cmd
        ) : new Process($cmd);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }
}
