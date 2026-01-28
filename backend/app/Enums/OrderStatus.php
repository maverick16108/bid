<?php

namespace App\Enums;

enum OrderStatus: string
{
    case NEW = 'new';
    case SENT_TO_1C = 'sent_to_1c';
    case PROCESSING = 'processing';
    case CONFIRMED = 'confirmed';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match($this) {
            self::NEW => 'Новая',
            self::SENT_TO_1C => 'Отправлена в 1С',
            self::PROCESSING => 'В обработке',
            self::CONFIRMED => 'Подтверждена',
            self::COMPLETED => 'Выполнена',
            self::CANCELLED => 'Отменена',
            self::REJECTED => 'Отклонена',
        };
    }
}
