<?php
/**
 * Small reusable UI pieces used by both the recycling flow and the admin pages.
 */

/** Coloured stat tile. $tone: green | rose | sun | peach. $sub may contain HTML entities. */
function stat_tile(string $tone, string $iconHtml, $value, string $label, string $sub = '', string $unit = ''): string {
    return '<div class="tile t-' . $tone . '"><div class="ic">' . $iconHtml . '</div>'
         . '<div class="v">' . e($value) . ($unit ? '<small> ' . e($unit) . '</small>' : '') . '</div>'
         . '<div class="l">' . e($label) . '</div>'
         . ($sub !== '' ? '<div class="s">' . $sub . '</div>' : '') . '</div>';
}

/** Small rounded label. $tone: '' (green) | warn | bad | mute | dog | cat */
function pill(string $text, string $tone = ''): string {
    return '<span class="pill ' . e($tone) . '">' . e($text) . '</span>';
}

function food_pill(?string $type): string {
    return $type ? pill(strtoupper($type), $type) : pill('-', 'mute');
}

function tx_status_pill(string $status): string {
    return pill(strtoupper($status), $status === 'completed' ? '' : ($status === 'cancelled' ? 'bad' : 'warn'));
}

/** Notice box with an info icon. */
function notice(string $html, string $style = ''): string {
    return '<div class="notice"' . ($style ? ' style="' . e($style) . '"' : '') . '>' . ic_info() . ' <span>' . $html . '</span></div>';
}

/** Progress bar with a label underneath. */
function progress_bar(int $percent, string $label = ''): string {
    return '<div class="bar"><span style="width:' . max(0, min(100, $percent)) . '%"></span></div>'
         . ($label !== '' ? '<div class="barlab">' . $label . '</div>' : '');
}
