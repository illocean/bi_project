<?php
namespace Models;

use Illuminate\Database\Eloquent\Model;

class DimOffice extends Model
{
    protected $connection = 'target';
    protected $table = 'dim_office';
    protected $primaryKey = 'office_key';
    public $timestamps = false;
    protected $fillable = ['officeCode', 'city', 'state', 'country', 'territory'];
}
