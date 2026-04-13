<?php

declare(strict_types=1);

namespace Sixtec\WBApi\DTOs;

/**
 * @author Mário Lucas
 * @since  2026-04-12
 */
enum MediaType: string
{
    case Image    = 'image';
    case Audio    = 'audio';
    case Video    = 'video';
    case Document = 'document';
    case Sticker  = 'sticker';
}
