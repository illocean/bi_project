<?php
namespace Models;

use Illuminate\Database\Eloquent\Model;

class DimCustomer extends Model
{
    protected $connection = 'target';
    protected $table = 'dim_customer';
    protected $primaryKey = 'customer_key';
    public $timestamps = false;
    protected $fillable = ['customerNumber', 'customerName', 'city', 'state', 'country'];
}
