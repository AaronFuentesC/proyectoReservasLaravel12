<?php

namespace App;

/**
 * Enum con todos los estados en los que puede estar una reserva
 */
enum BookingState: string
{
    case draft = 'draft';
    case pending = 'pending';
    case approved = 'approved';
    case rejected = 'rejected';
    case cancelled = 'cancelled';

    case finished = 'finished';
}