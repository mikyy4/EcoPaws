<?php
/**
 * Inline SVG icons. Every function takes an optional CSS class.
 */

/** Stroke-style 24x24 icon. */
function svg_stroke(string $body, string $cls = '', float $width = 3, bool $round = true): string {
    $join = $round ? ' stroke-linejoin="round"' : '';
    return '<svg class="' . $cls . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="' . $width
         . '" stroke-linecap="round"' . $join . ' aria-hidden="true">' . $body . '</svg>';
}

// ---------- Brand & UI icons ----------
function ic_paw($cls = '')    { return '<svg class="' . $cls . '" viewBox="0 0 64 64" fill="currentColor" aria-hidden="true"><ellipse cx="32" cy="42" rx="13" ry="11"/><ellipse cx="14" cy="28" rx="6" ry="8" transform="rotate(-20 14 28)"/><ellipse cx="25" cy="17" rx="6" ry="8.5"/><ellipse cx="39" cy="17" rx="6" ry="8.5"/><ellipse cx="50" cy="28" rx="6" ry="8" transform="rotate(20 50 28)"/></svg>'; }
function ic_bottle($cls = '') { return '<svg class="' . $cls . '" viewBox="0 0 48 64" fill="currentColor" aria-hidden="true"><rect x="18" y="2" width="12" height="7" rx="2"/><path d="M19 9h10v6c0 3 9 6 9 14v28a5 5 0 0 1-5 5H15a5 5 0 0 1-5-5V29c0-8 9-11 9-14z"/><rect x="12" y="32" width="24" height="14" fill="#fff" opacity=".4"/></svg>'; }
function ic_leaf($cls = '')   { return '<svg class="' . $cls . '" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20 3C10 3 4 8 4 15c0 1.6.4 3 1 4.2C7 15 10 12 14 10c-3 3-5.2 6-6.2 10.2.7.2 1.5.3 2.2.3 7 0 10-6 10-17.5z"/></svg>'; }

function ic_check($cls = '')  { return svg_stroke('<path d="M4 12.5l5 5L20 6.5"/>', $cls, 3.4); }
function ic_x($cls = '')      { return svg_stroke('<path d="M6 6l12 12M18 6L6 18"/>', $cls, 3.4, false); }
function ic_info($cls = '')   { return svg_stroke('<circle cx="12" cy="12" r="9.5"/><path d="M12 11v6M12 7.5v.1"/>', $cls, 2.6, false); }
function ic_arrow($cls = '')  { return svg_stroke('<path d="M5 12h14M13 6l6 6-6 6"/>', $cls); }
function ic_back($cls = '')   { return svg_stroke('<path d="M19 12H5M11 6l-6 6 6 6"/>', $cls); }
function ic_scan($cls = '')   { return svg_stroke('<path d="M4 8V5a1 1 0 0 1 1-1h3M16 4h3a1 1 0 0 1 1 1v3M20 16v3a1 1 0 0 1-1 1h-3M8 20H5a1 1 0 0 1-1-1v-3M4 12h16"/>', $cls, 2.6); }
function ic_lock($cls = '')   { return svg_stroke('<rect x="5" y="11" width="14" height="9" rx="2.5"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/>', $cls, 2.6); }

// ---------- Reward icons ----------
/** Bowl of kibble = "food credit" */
function ic_bowl($cls = '') { return '<svg class="' . $cls . '" viewBox="0 0 64 64" aria-hidden="true"><g fill="#B9794A"><circle cx="20" cy="26" r="6"/><circle cx="32" cy="21" r="6.5" fill="#CF9157"/><circle cx="44" cy="26" r="6"/><circle cx="26" cy="31" r="5.5" fill="#CF9157"/><circle cx="38" cy="31" r="5.5"/></g><path d="M5 34h54c0 15-11 24-27 24S5 49 5 34z" fill="currentColor"/><path d="M12 42c3 6 9 10 20 10" stroke="#fff" stroke-opacity=".45" stroke-width="3" fill="none" stroke-linecap="round"/></svg>'; }

/** Dental chew stick (bone shaped) */
function ic_stick($cls = '') { return '<svg class="' . $cls . '" viewBox="0 0 64 64" aria-hidden="true"><g transform="rotate(-35 32 32)" fill="currentColor"><rect x="14" y="25" width="36" height="14" rx="5"/><circle cx="13" cy="24" r="7"/><circle cx="13" cy="40" r="7"/><circle cx="51" cy="24" r="7"/><circle cx="51" cy="40" r="7"/><g stroke="#fff" stroke-opacity=".55" stroke-width="2.4" stroke-linecap="round"><path d="M24 28v8M32 28v8M40 28v8"/></g></g></svg>'; }

// ---------- Animal illustrations ----------
function ic_dog($cls = '') { return '<svg class="' . $cls . '" viewBox="0 0 120 120" aria-hidden="true">
<path d="M26 30c-12 2-18 24-11 42 3 8 12 7 15-2 3-9 3-26 1-36z" fill="#B58A62"/><path d="M94 30c12 2 18 24 11 42-3 8-12 7-15-2-3-9-3-26-1-36z" fill="#B58A62"/>
<ellipse cx="60" cy="62" rx="35" ry="37" fill="#EBC9A0"/><ellipse cx="60" cy="82" rx="20" ry="15" fill="#FFF3E2"/>
<path d="M60 26c-9 4-12 14-9 24 5 3 13 3 18 0 3-10 0-20-9-24z" fill="#D9A97A" opacity=".55"/>
<circle cx="46" cy="60" r="5.5" fill="#2E2A26"/><circle cx="74" cy="60" r="5.5" fill="#2E2A26"/><circle cx="47.8" cy="58" r="1.8" fill="#fff"/><circle cx="75.8" cy="58" r="1.8" fill="#fff"/>
<ellipse cx="60" cy="73" rx="8" ry="5.5" fill="#2E2A26"/><path d="M60 78v6M60 84c-4 5-10 4-12 0M60 84c4 5 10 4 12 0" stroke="#2E2A26" stroke-width="3" fill="none" stroke-linecap="round"/>
<ellipse cx="38" cy="75" rx="6" ry="4" fill="#F4A98A" opacity=".55"/><ellipse cx="82" cy="75" rx="6" ry="4" fill="#F4A98A" opacity=".55"/></svg>'; }

function ic_cat($cls = '') { return '<svg class="' . $cls . '" viewBox="0 0 120 120" aria-hidden="true">
<path d="M20 22l14 36 24-20z" fill="#9C9AA6"/><path d="M100 22L86 58 62 38z" fill="#9C9AA6"/><path d="M27 34l7 17 11-9z" fill="#F2B6AE"/><path d="M93 34l-7 17-11-9z" fill="#F2B6AE"/>
<ellipse cx="60" cy="66" rx="40" ry="35" fill="#C3C1CC"/><path d="M60 32v14M50 34l3 11M70 34l-3 11" stroke="#9C9AA6" stroke-width="4" stroke-linecap="round"/>
<ellipse cx="45" cy="63" rx="7" ry="8" fill="#F3E4A4"/><ellipse cx="75" cy="63" rx="7" ry="8" fill="#F3E4A4"/><ellipse cx="45" cy="63" rx="2.6" ry="7" fill="#2E2A26"/><ellipse cx="75" cy="63" rx="2.6" ry="7" fill="#2E2A26"/>
<path d="M55 76h10l-5 6z" fill="#E98C85"/><path d="M60 82c-3 5-9 5-12 1M60 82c3 5 9 5 12 1" stroke="#5A5663" stroke-width="2.8" fill="none" stroke-linecap="round"/>
<g stroke="#fff" stroke-width="2.4" stroke-linecap="round" opacity=".9"><path d="M30 76l-17-4M30 83l-17 3M90 76l17-4M90 83l17 3"/></g></svg>'; }

/** Dog or cat icon from a 'dog' | 'cat' value. */
function ic_pet(string $type, string $cls = ''): string { return $type === 'cat' ? ic_cat($cls) : ic_dog($cls); }

// ---------- Admin sidebar / page-header icons (small stroke set) ----------
function ai(string $name, string $cls = ''): string {
    static $paths = [
        'dash'   => '<rect x="3" y="3" width="7.5" height="9" rx="2"/><rect x="13.5" y="3" width="7.5" height="5.5" rx="2"/><rect x="13.5" y="12" width="7.5" height="9" rx="2"/><rect x="3" y="15.5" width="7.5" height="5.5" rx="2"/>',
        'bottle' => '<path d="M10 2h4v4l2.5 3.5V20a2 2 0 0 1-2 2h-5a2 2 0 0 1-2-2V9.5L10 6z"/><path d="M7.5 13h9"/>',
        'gift'   => '<rect x="3" y="8" width="18" height="4" rx="1.5"/><path d="M12 8v13M5 12v8a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-8"/><path d="M12 8c-3 0-4.5-1-4.5-2.5S9 3 10.5 4 12 8 12 8zm0 0c3 0 4.5-1 4.5-2.5S15 3 13.5 4 12 8 12 8z"/>',
        'bowl'   => '<path d="M3 11h18c0 5.5-4 9-9 9s-9-3.5-9-9z"/><path d="M8 7c0-1.2 1-2 2-2M13 6c0-1.2 1-2 2-2"/>',
        'box'    => '<path d="M21 8l-9-5-9 5v8l9 5 9-5z"/><path d="M3 8l9 5 9-5M12 13v8"/>',
        'report' => '<path d="M6 3h9l4 4v14H6z"/><path d="M14 3v5h5M9 13h7M9 17h7M9 9h2"/>',
        'user'   => '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4.4 3.6-7 8-7s8 2.6 8 7"/>',
        'logout' => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>',
        'alert'  => '<path d="M12 3l10 18H2z"/><path d="M12 10v5M12 18v.1"/>',
        'lock'   => '<rect x="4" y="11" width="16" height="10" rx="2.5"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/>',
        'list'   => '<path d="M8 6h13M8 12h13M8 18h13M3 6h.1M3 12h.1M3 18h.1"/>',
        'cal'    => '<rect x="3" y="5" width="18" height="16" rx="3"/><path d="M8 3v4M16 3v4M3 10h18"/>',
        'menu'   => '<path d="M4 7h16M4 12h16M4 17h16"/>',
        'close'  => '<path d="M6 6l12 12M18 6L6 18"/>',
        'home'   => '<path d="M3 11l9-8 9 8"/><path d="M5 10v10h5v-6h4v6h5V10"/>',
        'external' => '<path d="M14 4h6v6M20 4l-9 9M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5"/>',
    ];
    return svg_stroke($paths[$name] ?? '', $cls, 2.2);
}
