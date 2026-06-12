<?php
namespace Models;

use Illuminate\Database\Eloquent\Model;

class DimProduct extends Model
{
    protected $connection = 'target';
    protected $table = 'dim_product';
    protected $primaryKey = 'product_key';
    public $timestamps = false;
    protected $fillable = ['productCode', 'productName', 'productLine', 'productScale', 'productVendor'];
}
