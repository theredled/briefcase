<?php
namespace App\Models;

/**
 * Created by PhpStorm.
 * User: Benoît Guchet
 * Date: 11/05/2025
 * Time: 23:54
 */

trait TracksRelationChanges
{
    public function hasRelationChanged(string $relation, array $newIds): bool
    {
        $currentIds = $this->$relation->pluck('id')->sort()->values()->toArray();
        $newIds = collect($newIds)->sort()->values()->toArray();

        return $currentIds !== $newIds;
    }
}
