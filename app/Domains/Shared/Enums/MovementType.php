<?php

namespace App\Domains\Shared\Enums;

enum MovementType: string
{
    case Purchase = 'purchase';
    case Sale = 'sale';
    case TransferOut = 'transfer_out';
    case TransferIn = 'transfer_in';
    case ManualCorrection = 'manual_correction';
    case Damage = 'damage';
    case Reservation = 'reservation';
    case ReservationRelease = 'reservation_release';
    case Return = 'return';
    case Initial = 'initial';
}
