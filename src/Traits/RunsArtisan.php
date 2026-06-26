<?php

namespace Stepanenko3\NovaCommandRunner\Traits;

trait RunsArtisan
{
    /**
     * The name of the user running an Artisan command.
     */
    public function getArtisanRunByName(): ?string
    {
        return $this->name;
    }
}
