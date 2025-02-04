<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class UsuarioRol
 * 
 * @property int $id
 * @property int|null $id_usuario
 * @property int|null $id_rol
 * @property Carbon $fecha_creacion
 * 
 * @property Usuario|null $usuario
 * @property Rol|null $rol
 *
 * @package App\Models
 */
class UsuarioRol extends Model
{
	protected $table = 'usuario_rol';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'id' => 'int',
		'id_usuario' => 'int',
		'id_rol' => 'int',
		'fecha_creacion' => 'datetime'
	];

	protected $fillable = [
		'id_usuario',
		'id_rol',
		'fecha_creacion'
	];

	public function usuario()
	{
		return $this->belongsTo(Usuario::class, 'id_usuario');
	}

	public function rol()
	{
		return $this->belongsTo(Rol::class, 'id_rol');
	}
}
