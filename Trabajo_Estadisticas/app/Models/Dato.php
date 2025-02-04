<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Dato
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
class Dato extends Model
{
	protected $table = 'datos';
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
