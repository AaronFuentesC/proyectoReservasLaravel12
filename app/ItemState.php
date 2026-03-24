<?php

namespace App;

/**
 * Enum con todos los estados en los que puede estar un equipo
 */
enum ItemState : string
{
    case ok = 'ok';
    case maintenance = 'maintenance';
    case not_available = 'not_available';
}
