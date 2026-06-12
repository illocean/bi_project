<?php
namespace Models;

use Illuminate\Database\Eloquent\Model;

class SourceProduct extends Model
{
    protected $connection = 'source';
    protected $table = 'products';
    protected $primaryKey = 'productCode';
    public $incrementing = false;
    public $timestamps = false;
    protected $fillable = ['productCode', 'productName', 'productLine', 'productScale', 'productVendor'];
}
