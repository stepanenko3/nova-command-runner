<?php

namespace Stepanenko3\NovaCommandRunner\Traits;

trait RunsArtisan
{
    /**
     * The name of the user running an Artisan command.
     *
     * @return null|string
     */
    public function getArtisanRunByName(): ?string
    {
        return $this->name;
    }
}
