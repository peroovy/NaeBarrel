<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


/**
 * App\Models\Client
 *
 * @property int $id
 * @property string $login
 * @property string $password
 * @property string|null $email
 * @property int $permission
 * @property int $balance
 * @method static \Illuminate\Database\Eloquent\Builder|Client newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Client newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Client query()
 * @method static \Illuminate\Database\Eloquent\Builder|Client whereBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Client whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Client whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Client whereLogin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Client wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Client wherePermission($value)
 * @mixin \Eloquent
 */
class Client extends Model
{
    use HasFactory;

    protected $table = 'clients';

    public function permission(): Permission
    {
        return $this->hasOne(Permission::class, 'id', 'permission')
            ->getResults();
    }
}
