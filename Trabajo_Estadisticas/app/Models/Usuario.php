<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Usuario
 * 
 * @property int $id
 * @property string|null $nombre
 * @property string|null $passwd
 * @property string|null $email
 * 
 * @property Collection|LogAcceso[] $log_accesos
 * @property Collection|Rol[] $rols
 *
 * @package App\Models
 */
class Usuario extends Model
{
	protected $table = 'usuario';
	public $timestamps = false;

	protected $fillable = [
		'nombre',
		'passwd',
		'email'
	];

	public function log_accesos()
	{
		return $this->hasMany(LogAcceso::class, 'id_usuario');
	}

	public function rols()
	{
		return $this->belongsToMany(Rol::class, 'usuario_rol', 'id_usuario', 'id_rol')
					->withPivot('id', 'fecha_creacion');
	}
}
