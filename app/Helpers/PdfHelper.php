<?php

namespace App\Helpers;

use App\Models\Setting;

/**
 * Shared letterhead data for every "khata"-style PDF report (logo, company
 * name) - one place so every report's PDF looks consistent and picks up a
 * logo change from Settings > General automatically.
 */
class PdfHelper
{
    public static function companyName(): string
    {
        return Setting::get('app_name') ?: config('app.name', 'WSERP');
    }

    /**
     * Base64 data URI for the configured logo, or null if none is set or the
     * file is missing. dompdf embeds inline data URIs reliably regardless of
     * the isRemoteEnabled config, unlike fetching the logo by URL.
     */
    public static function companyLogoDataUri(): ?string
    {
        $logo = Setting::get('logo');
        if (!$logo) {
            return null;
        }

        $path = public_path($logo);
        if (!file_exists($path)) {
            return null;
        }

        $mime = match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            default => 'image/png',
        };

        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
    }
}
