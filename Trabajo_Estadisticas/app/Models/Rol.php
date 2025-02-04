<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Rol
 * 
 * @property int $id
 * @property string|null $Descripcion
 * 
 * @property Collection|Usuario[] $usuarios
 *
 * @package App\Models
 */
class Rol extends Model
{
	protected $table = 'rol';
	public $timestamps = false;

	protected $fillable = [
		'Descripcion'
	];

	public function usuarios()
	{
		return $this->belongsToMany(Usuario::class, 'usuario_rol', 'id_rol', 'id_usuario')
					->withPivot('id', 'fecha_creacion');
	}
}
