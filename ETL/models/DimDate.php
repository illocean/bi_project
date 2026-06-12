<?php
namespace Models;

use Illuminate\Database\Eloquent\Model;

class DimDate extends Model
{
    protected $connection = 'target';
    protected $table = 'dim_date';
    protected $primaryKey = 'date_key';
    public $incrementing = false;
    public $timestamps = false;
    protected $fillable = ['date_key', 'full_date', 'month_number', 'month_name', 'quarter', 'year'];
}
