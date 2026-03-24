<?php

namespace App;
/**
 * Enum con todos los tipos que un item puede tener.
 */
enum ItemType: string
{
    case Projector = 'projector';
    case Car = 'car';
    case Laptop = 'laptop';
    case screen = 'screen';
    case keyboard = 'keyboard';
    case mouse = 'mouse';
}
