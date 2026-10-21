<?php

declare(strict_types=1);

namespace Modules\Listing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ListingApplyClick extends Model
{
    use SoftDeletes;

    protected $fillable = ['listing_id', 'user_id', 'source'];

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }
}
