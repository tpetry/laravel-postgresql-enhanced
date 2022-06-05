<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Eloquent\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * The implementations of these functions have been taken from the Laravel core and
 * have been changed in the most minimal way to support the returning clause.
 */
trait RefreshDataOnSave
{
    /**
     * Perform a model insert operation.
     */
    protected function performInsert(Builder $query): bool
    {
        if (false === $this->fireModelEvent('creating')) {
            return false;
        }

        // First we'll need to create a fresh query instance and touch the creation and
        // update timestamps on this model, which are maintained by us for developer
        // convenience. After, we will just continue saving these model instances.
        if ($this->usesTimestamps()) {
            $this->updateTimestamps();
        }

        // We're executing a standard laravel insert into the database with the difference that
        // a returning statement is executed returning all the saved and generated values. These
        // values are saved to the model which will later on in save() be synced to original.
        $returning = $query->toBase()->insertReturning($this->getAttributes());
        $this->setRawAttributes((array) $returning->first());

        // We will go ahead and set the exists property to true, so that it is set when
        // the created event is fired, just in case the developer tries to update it
        // during the event. This will allow them to do so and run an update here.
        $this->exists = true;

        $this->wasRecentlyCreated = true;

        $this->fireModelEvent('created', false);

        return true;
    }

    /**
     * Perform a model update operation.
     */
    protected function performUpdate(Builder $query): bool
    {
        // If the updating event returns false, we will cancel the update operation so
        // developers can hook Validation systems into their models and cancel this
        // operation if the model does not pass validation. Otherwise, we update.
        if (false === $this->fireModelEvent('updating')) {
            return false;
        }

        // First we need to create a fresh query instance and touch the creation and
        // update timestamp on the model which are maintained by us for developer
        // convenience. Then we will just continue saving the model instances.
        if ($this->usesTimestamps()) {
            $this->updateTimestamps();
        }

        // Once we have run the update operation, we will fire the "updated" event for
        // this model instance. This will allow developers to hook into these after
        // models are updated, giving them a chance to do any special processing.
        $dirty = $this->getDirty();

        if (\count($dirty) > 0) {
            // We're executing a standard laravel update to the database with the difference that
            // a returning statement is executed returning all the updated and generated values. These
            // values are saved to the model which will later on in save() be synced to original.
            $returning = $this->setKeysForSaveQuery($query)->toBase()->updateReturning($dirty);
            $this->setRawAttributes((array) $returning->first());

            $this->syncChanges();

            $this->fireModelEvent('updated', false);
        }

        return true;
    }

    /**
     * Perform the actual delete query on this model instance.
     *
     * @return void
     */
    protected function performDeleteOnModel()
    {
        $returning = $this->setKeysForSaveQuery($this->newModelQuery())->deleteReturning();

        if ($returning) {
            $this->setRawAttributes((array) $returning->first());
        } else {
            $this->exists = false;
        }
    }
}
