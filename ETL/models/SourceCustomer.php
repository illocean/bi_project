<?php
namespace Models;

use Illuminate\Database\Eloquent\Model;

class SourceCustomer extends Model
{
    protected $connection = 'source';
    protected $table = 'customers';
    protected $primaryKey = 'customerNumber';
    public $incrementing = false;
    public $timestamps = false;
    protected $fillable = ['customerNumber', 'customerName', 'city', 'state', 'country'];
}
