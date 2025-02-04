<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class InventarioEst
 * 
 * @property string|null $idema
 * @property string|null $nombre
 * @property string|null $provincia
 *
 * @package App\Models
 */
class InventarioEst extends Model
{
	protected $table = 'inventario_est';
	public $incrementing = false;
	public $timestamps = false;

	protected $fillable = [
		'idema',
		'nombre',
		'provincia'
	];
}
