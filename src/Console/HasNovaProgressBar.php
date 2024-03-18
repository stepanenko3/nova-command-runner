<?php

namespace Stepanenko3\NovaCommandRunner\Console;

trait HasNovaProgressBar
{
    protected function createProgressBar(
        int $max,
    ): ProgressBar {
        return new ProgressBar(
            $this->output,
            $this->signature,
            $max,
        );
    }
}
