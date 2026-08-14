<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Old-store URL -> new WSRetail storefront page. Captured automatically
 * during a CMS import (see IntegrationImportService::recordRedirect()) so a
 * store that already has SEO ranking on its old product URLs doesn't lose
 * it the moment the old site is replaced. Looked up publicly via
 * /api/v1/customer/redirect (RedirectController) and browsable/exportable
 * from Settings > Integrations > SEO Redirects.
 */
class UrlRedirect extends Model
{
    protected $fillable = ['product_id', 'old_path', 'new_path', 'source'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
