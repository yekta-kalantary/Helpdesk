<?php

namespace Modules\Projects\Application\Actions;

use Modules\Projects\Domain\Contracts\ProjectRepository;

class SaveProject
{
    public function __construct(private readonly ProjectRepository $projects) {}

    public function execute(?int $id, array $attributes, array $memberIds): int
    {
        if ($id) {
            $this->projects->update($id, $attributes, $memberIds);

            return $id;
        }

        return $this->projects->create($attributes, $memberIds);
    }
}
