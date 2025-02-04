<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Stat
 * 
 * @property int $id
 * @property int|null $id_estacion
 * @property string|null $idema
 * @property Carbon|null $fecha
 * @property float|null $vv
 * @property float|null $ta
 * @property float|null $hr
 * @property float|null $prec
 * 
 * @property EstacionBd|null $estacion_bd
 *
 * @package App\Models
 */
class Stat extends Model
{
	protected $table = 'stats';
	public $timestamps = false;

	protected $casts = [
		'id_estacion' => 'int',
		'fecha' => 'datetime',
		'vv' => 'float',
		'ta' => 'float',
		'hr' => 'float',
		'prec' => 'float'
	];

	protected $fillable = [
		'id_estacion',
		'idema',
		'fecha',
		'vv',
		'ta',
		'hr',
		'prec'
	];

	public function estacion_bd()
	{
		return $this->belongsTo(EstacionBd::class, 'id_estacion');
	}
}
