<?php

namespace App\Observers;

use App\Models\Activity;
use Illuminate\Support\Facades\Storage;

class ActivityObserver
{
    public function updating(Activity $activity): void
    {
        if ($activity->isDirty('photo') && $activity->getOriginal('photo')) {
            Storage::disk('activities')->delete($activity->getOriginal('photo'));
            Storage::disk('activities')->delete('thumbs/' . $activity->getOriginal('photo'));
        }
    }
}
