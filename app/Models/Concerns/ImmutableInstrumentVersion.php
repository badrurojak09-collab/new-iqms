<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use LogicException;

trait ImmutableInstrumentVersion
{
    public static function bootImmutableInstrumentVersion(): void
    {
        static::saving(function (self $model): void {
            if ($model->exists && ($model->getOriginal('status') === 'published'
                || $model->getOriginal('status') === 'retired'
                || $model->getOriginal('published_at') !== null)) {
                throw new LogicException('Instrument version yang published tidak dapat diubah. Buat version baru.');
            }
        });

        static::deleting(function (self $model): void {
            if ($model->isImmutable()) {
                throw new LogicException('Instrument version yang published tidak dapat dihapus.');
            }
        });
    }

    public function isImmutable(): bool
    {
        return $this->status === 'published'
            || $this->status === 'retired'
            || $this->published_at !== null;
    }
}
