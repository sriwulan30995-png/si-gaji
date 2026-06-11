<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    use HasFactory;

    protected static function booted()
    {
        static::creating(function ($permission) {
            $baseName = $permission->name;

            // Jika tidak mengandung action keyword, jangan simpan permission utama
            if (!preg_match('/\b(View|Create|Update|Delete|Restore|Force Delete|View Any)\b/i', $baseName)) {
                // Simpan semua turunan, lalu batalkan simpan aslinya
                $actions = ['View Any', 'View', 'Create', 'Update', 'Delete', 'Restore', 'Force Delete'];

                foreach ($actions as $action) {
                    $perm = "{$action} {$baseName}";

                    static::firstOrCreate([
                        'name' => $perm,
                        'guard_name' => 'web',
                    ]);
                }

                return false; // <<=== Batalkan insert ke DB untuk "HargaSewa" (tanpa action)
            }

            return true; // lanjut simpan kalau ada action-nya
        });
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_has_permissions', 'permission_id', 'role_id');
    }
}
