<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class EstacionBd
 * 
 * @property int $id
 * @property int|null $estado
 * 
 * @property Collection|Dato[] $datos
 * @property Collection|Stat[] $stats
 *
 * @package App\Models
 */
class EstacionBd extends Model
{
	protected $table = 'estacion_bd';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'id' => 'int',
		'estado' => 'int'
	];

	protected $fillable = [
		'estado'
	];

	public function datos()
	{
		return $this->hasMany(Dato::class, 'id_estacion');
	}

	public function stats()
	{
		return $this->hasMany(Stat::class, 'id_estacion');
	}
}
