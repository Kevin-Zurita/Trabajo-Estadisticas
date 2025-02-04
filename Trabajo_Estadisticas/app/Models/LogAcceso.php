<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class LogAcceso
 * 
 * @property int $id
 * @property int|null $id_usuario
 * @property Carbon|null $fecha_reg
 * @property string|null $descripcion
 * 
 * @property Usuario|null $usuario
 *
 * @package App\Models
 */
class LogAcceso extends Model
{
	protected $table = 'log_acceso';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'id' => 'int',
		'id_usuario' => 'int',
		'fecha_reg' => 'datetime'
	];

	protected $fillable = [
		'id_usuario',
		'fecha_reg',
		'descripcion'
	];

	public function usuario()
	{
		return $this->belongsTo(Usuario::class, 'id_usuario');
	}
}
