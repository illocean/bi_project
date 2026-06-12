<?php
namespace Models;

use Illuminate\Database\Eloquent\Model;

class SourceOffice extends Model
{
    protected $connection = 'source';
    protected $table = 'offices';
    protected $primaryKey = 'officeCode';
    public $incrementing = false;
    public $timestamps = false;
    protected $fillable = ['officeCode', 'city', 'state', 'country', 'territory'];
}
