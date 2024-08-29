<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum RequestStatus: string implements HasColor, HasDescription, HasIcon, HasLabel
{
    case APPROVED = 'approved';
    case DECLINED = 'declined';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case STARTED = 'started';
    case PUBLISHED = 'published';
    case RETRACTED = 'retracted';
    case RESOLVED = 'resolved';
    case SUSPENDED = 'suspended';
    case COMPLIED = 'complied';
    case ASSIGNED = 'assigned';
    case ACCEPTED = 'accepted';
    case REJECTED = 'rejected';
    case ADJUSTED = 'adjusted';
    case SCHEDULED = 'scheduled';
    case COMPLIED = 'complied';

    public function getColor(): ?string
    {
        return match ($this) {
            self::APPROVED => 'success',
            self::DECLINED => 'danger',
            self::COMPLETED => 'success',
            self::CANCELLED => 'danger',
            self::STARTED => 'info',
            self::SUSPENDED => 'warning',
            self::PUBLISHED => 'success',
            self::RETRACTED => 'warning',
            self::ACCEPTED => 'success',
            self::REJECTED => 'danger',
            self::ASSIGNED,
            self::ADJUSTED,
            self::RESOLVED,
            self::SCHEDULED => 'info',
            self::COMPLIED => 'warning',
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

    public function getIcon(): ?string
    {
        return match ($this) {
            self::APPROVED => 'gmdi-verified-o',
            self::DECLINED => 'gmdi-block-o',
            self::COMPLETED => 'gmdi-task-alt-o',
            self::RESOLVED => 'gmdi-approval-tt',
            self::CANCELLED => 'gmdi-disabled-by-default-o',
            self::STARTED => 'gmdi-alarm-o',
            self::SUSPENDED => 'gmdi-front-hand-o',
            self::PUBLISHED => 'gmdi-published-with-changes-o',
            self::RETRACTED => 'gmdi-settings-backup-restore-o',
            self::ASSIGNED => 'gmdi-group-add-o',
            self::ACCEPTED => 'gmdi-how-to-reg-o',
            self::REJECTED => 'gmdi-person-off-o',
            self::ADJUSTED => 'gmdi-scale-o',
            self::SCHEDULED => 'gmdi-event-o',
            self::COMPLIED => 'gmdi-task-r',
            default => 'gmdi-circle-o',
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
                    default => $value->substr(0, -2),
                };
            })
            ->headline();
    }

    public function major()
    {
        return in_array($this, [
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
        return in_array($this, [
            self::ASSIGNED,
            self::ACCEPTED,
            self::REJECTED,
            self::ADJUSTED,
            self::SCHEDULED,
            self::COMPLIED,
        ]);
    }
}
