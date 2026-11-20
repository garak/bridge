<?php

namespace Garak\Bridge;

use Garak\Card\CardsTrick;

/**
 * Bridge rules match the default CardsTrick ones:
 * - if at least one trump card was played, the highest trump wins;
 * - otherwise, the highest card of the led suit wins; off-suit discards never win.
 */
final class Trick extends CardsTrick
{
}
