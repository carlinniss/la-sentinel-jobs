<?php

declare(strict_types=1);

namespace Modules\Conversation\App\Support;

class QuickMessageCatalog
{
    public static function all(): array
    {
        return [
            'Hi',
            'Is this job still open?',
            'Can you share the schedule?',
            'I am interested in applying.',
            'Thanks',
        ];
    }
}
