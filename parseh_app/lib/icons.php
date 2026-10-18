<?php
declare(strict_types=1);

function icon(string $name, int $size = 24, string $color = 'currentColor'): string
{
    $paths = [
        'pregnancy'          => '<circle cx="12" cy="8" r="3"/><path d="M7 21c0-5 2-9 5-9s5 3 5 6c0 1.5-1 3-3 3"/>',
        'breast'             => '<circle cx="12" cy="12" r="8"/><circle cx="12" cy="12" r="3"/>',
        'pediatric'          => '<circle cx="12" cy="12" r="8"/><path d="M9 10h.01M15 10h.01M9 15c1.5 1.3 4.5 1.3 6 0"/>',
        'doppler'            => '<path d="M3 12h4l2-5 3 10 2-6 1.5 3H21"/>',
        'general-ultrasound' => '<path d="M7 4h5v6a2.5 2.5 0 0 1-5 0z"/><path d="M14 9c2.5 0 5 2 6 5M14 12c1.5 0 3 1 3.5 2.5M9.5 12.5v4a3.5 3.5 0 0 0 7 0"/>',
        'radiology'          => '<rect x="4" y="3" width="16" height="18" rx="2"/><path d="M12 6v12M9 8h6M8.5 11h7M9 14h6M10 17h4"/>',
        'clock'              => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'phone'              => '<path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2"/>',
        'pin'                => '<path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/>',
        'logo'               => '',
    ];
    if ($name === 'logo') {
        return '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 44 44" fill="none" aria-hidden="true"><rect x="2" y="2" width="40" height="40" rx="10" fill="#0B2E33"/><path d="M11 14h22M13 14v17M22 14v17M31 14v17M10 31h24M12 10.5h20" stroke="#C8A97E" stroke-width="2.4" stroke-linecap="round"/></svg>';
    }
    $p = $paths[$name] ?? $paths['radiology'];
    return '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="' . $color . '" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $p . '</svg>';
}

/** متن ساده با خطوط خالی → پاراگراف‌ها (بدون اجازه HTML) */
function paragraphs(?string $text): string
{
    $text = str_replace(["\r\n", '\n'], ["\n", "\n"], (string) $text);
    $out = '';
    foreach (preg_split("/\n\s*\n/", trim($text)) as $p) {
        if (trim($p) !== '') {
            $out .= '<p>' . nl2br(e(trim($p))) . '</p>';
        }
    }
    return $out;
}
