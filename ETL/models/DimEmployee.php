<?php
namespace Models;

use Illuminate\Database\Eloquent\Model;

class DimEmployee extends Model
{
    protected $connection = 'target';
    protected $table = 'dim_employee';
    protected $primaryKey = 'employee_key';
    public $timestamps = false;
    protected $fillable = ['employeeNumber', 'lastName', 'firstName', 'jobTitle'];
}
