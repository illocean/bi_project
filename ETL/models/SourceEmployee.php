<?php
namespace Models;

use Illuminate\Database\Eloquent\Model;

class SourceEmployee extends Model
{
    protected $connection = 'source';
    protected $table = 'employees';
    protected $primaryKey = 'employeeNumber';
    public $incrementing = false;
    public $timestamps = false;
    protected $fillable = ['employeeNumber', 'lastName', 'firstName', 'jobTitle'];
}
