<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasLabel;

enum RequestStatus: string implements HasColor, HasDescription, HasLabel
{
    // MAJOR
    case APPROVED = 'approved';
    case DECLINED = 'declined';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case STARTED = 'started';
    case SUSPENDED = 'suspended';
    case PUBLISHED = 'published';
    case RETRACTED = 'retracted';

    // MINOR -------- DO NOT INCLUDE IN FORMS
    case ASSIGNED = 'assigned'; // The request has been assigned to any assignees.
    case ACCEPTED = 'accepted'; // The request has been accepted by the assignee.
    case REJECTED = 'rejected'; // The request has been rejected by the assignee.
    case ADJUSTED = 'adjusted'; // The request difficulty or priority has been adjusted.
    case SCHEDULED = 'scheduled'; // The request has been scheduled for a target specific date and time.

    public function getColor(): ?string
    {
        return match ($this) {
            self::APPROVED => 'green',
            self::DECLINED => 'red',
            self::COMPLETED => 'success',
            self::CANCELLED => 'danger',
            self::STARTED => 'blue',
            self::SUSPENDED => 'warning',
            self::PUBLISHED => 'success',
            self::RETRACTED => 'warning',
            default => 'gray'
        };
    }

    public function getDescription(): ?string
    {
        return match ($this) {
            self::ACCEPTED => 'The request has been accepted.',
            self::DECLINED => 'The request has been declined.',
            self::COMPLETED => 'The request has been completed.',
            self::CANCELLED => 'The request has been cancelled and will not be processed further.',
            self::STARTED => 'The request has been taken up and is in progress.',
            self::SUSPENDED => 'The request has been suspended and is awaiting further action.',
            self::PUBLISHED => 'The request has been published by the user',
            self::RETRACTED => 'The request has been retracted by the requestor and is waiting to be republished.',
            default => null
        };
    }

    public function getLabel($present = false): ?string
    {
        return str($this->value)
            ->when($present, function ($value) {
                return match ($value->toString()) {
                    'cancelled' => 'Cancel',
                    'declined' => 'Decline',
                    'scheduled' => 'Schedule',
                    default => $value->substr(0, -2)->headline(),
                };
            })
            ->headline();
    }

    public function major()
    {
        return in_array($this->value, [
            self::APPROVED,
            self::DECLINED,
            self::COMPLETED,
            self::CANCELLED,
            self::STARTED,
            self::SUSPENDED,
            self::PUBLISHED,
            self::RETRACTED,
        ]);
    }

    public function minor()
    {
        return in_array($this->value, [
            self::ASSIGNED,
            self::ACCEPTED,
            self::REJECTED,
            self::ADJUSTED,
            self::SCHEDULED,
        ]);
    }
}
