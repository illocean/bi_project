<?php
namespace Models;

use Illuminate\Database\Eloquent\Model;

class DimOrder extends Model
{
    protected $connection = 'target';
    protected $table = 'dim_order';
    protected $primaryKey = 'order_key';
    public $timestamps = false;
    protected $fillable = ['orderNumber', 'orderLineNumber', 'status'];
}
