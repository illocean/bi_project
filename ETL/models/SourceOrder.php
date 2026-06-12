<?php
namespace Models;

use Illuminate\Database\Eloquent\Model;

class SourceOrder extends Model
{
    protected $connection = 'source';
    protected $table = 'orders';
    protected $primaryKey = 'orderNumber';
    public $incrementing = false;
    public $timestamps = false;
    protected $fillable = ['orderNumber', 'orderDate', 'status', 'customerNumber'];
}
