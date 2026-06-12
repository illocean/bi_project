<?php
namespace Models;

use Illuminate\Database\Eloquent\Model;

class SourceOrderDetail extends Model
{
    protected $connection = 'source';
    protected $table = 'orderdetails';
    protected $primaryKey = null;
    public $incrementing = false;
    public $timestamps = false;
    protected $fillable = ['orderNumber', 'productCode', 'quantityOrdered', 'priceEach', 'orderLineNumber'];
}
