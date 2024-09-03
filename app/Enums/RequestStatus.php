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
    case ASSIGNED = 'assigned';
    case ACCEPTED = 'accepted';
    case REJECTED = 'rejected';
    case ADJUSTED = 'adjusted';
    case SCHEDULED = 'scheduled';
    case COMPLIED = 'complied';
    case EXTENDED = 'extended';
    case VERIFIED = 'verified';
    case DENIED = 'denied';
    case AMMENDED = 'ammended';
    case SURVEYED = 'surveyed';

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
            self::RESOLVED => 'info',
            self::SCHEDULED => 'info',
            self::COMPLIED => 'warning',
            self::EXTENDED => 'success',
            self::VERIFIED => 'success',
            self::DENIED => 'danger',
            self::AMMENDED => 'info',
            self::SURVEYED => 'success',
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
            self::RESOLVED => 'The request has been completed fully and will no longer receive updates',
            self::COMPLIED => 'The user submitted the lacking documents',
            self::EXTENDED => 'The user requires the request to be extended due to further issues',
            self::VERIFIED => 'The user has accepted the completion of the request',
            self::DENIED => 'The user has rejected the completion of the request',
            self::APPROVED => 'The request has been accepted and is being processed',
            self::AMMENDED => 'The request remarks have been updated by the user',
            self::SURVEYED => 'The use has submitted a survey and is completed through',
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
            self::EXTENDED => 'gmdi-cable-tt',
            self::VERIFIED => 'gmdi-verified-user-o',
            self::DENIED => 'gmdi-do-not-disturb-on-total-silence',
            self::AMMENDED => 'gmdi-filter-tilt-shift-o',
            self::SURVEYED => 'gmdi-book-r',
            default => 'gmdi-circle-o',
        };
    }

    public function getLabel(?string $type = null, ?bool $capitalize = true): ?string
    {
        $label = match ($type) {
            'nounForm' => match ($this->value) {
                'approved' => 'approval',
                'declined' => 'declination',
                'completed' => 'completion',
                'cancelled' => 'cancellation',
                'initiated' => 'initiation',
                'suspended' => 'suspension',
                'published' => 'publication',
                'retracted' => 'retraction',
                'assigned' => 'assignment',
                'accepted' => 'acceptance',
                'rejected' => 'rejection',
                'adjusted' => 'adjustment',
                'scheduled' => 'scheduling',
                'extended' => 'extension',
                'verified' => 'verification',
                'denied' => 'denial',
                'ammended' => 'alter',
                'surveyed' => 'surveying',
                default => $this->value,
            },
            'presentTense' => match ($this->value) {
                'approved' => 'approve',
                'cancelled' => 'cancel',
                'declined' => 'decline',
                'scheduled' => 'schedule',
                default => substr($this->value, 0, -2),
            },
            default => $this->value,
        };

        return $capitalize ? ucfirst($label) : $label;
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
            self::RESOLVED,
            self::EXTENDED,
            self::VERIFIED,
            self::DENIED,
            self::SURVEYED,
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
