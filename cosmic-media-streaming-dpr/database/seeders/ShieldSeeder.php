<?php

namespace Database\Seeders;

use BezhanSalleh\FilamentShield\Support\Utils;
use Illuminate\Database\Seeder;

class ShieldSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $rolesWithPermissions = '[{"name":"super_admin","guard_name":"web","permissions":["view_display","view_any_display","create_display","update_display","restore_display","restore_any_display","replicate_display","reorder_display","delete_display","delete_any_display","force_delete_display","force_delete_any_display","view_layout","view_any_layout","create_layout","update_layout","restore_layout","restore_any_layout","replicate_layout","reorder_layout","delete_layout","delete_any_layout","force_delete_layout","force_delete_any_layout","view_media","view_any_media","create_media","update_media","restore_media","restore_any_media","replicate_media","reorder_media","delete_media","delete_any_media","force_delete_media","force_delete_any_media","view_media::hls","view_any_media::hls","create_media::hls","update_media::hls","restore_media::hls","restore_any_media::hls","replicate_media::hls","reorder_media::hls","delete_media::hls","delete_any_media::hls","force_delete_media::hls","force_delete_any_media::hls","view_media::html","view_any_media::html","create_media::html","update_media::html","restore_media::html","restore_any_media::html","replicate_media::html","reorder_media::html","delete_media::html","delete_any_media::html","force_delete_media::html","force_delete_any_media::html","view_media::image","view_any_media::image","create_media::image","update_media::image","restore_media::image","restore_any_media::image","replicate_media::image","reorder_media::image","delete_media::image","delete_any_media::image","force_delete_media::image","force_delete_any_media::image","view_media::live::url","view_any_media::live::url","create_media::live::url","update_media::live::url","restore_media::live::url","restore_any_media::live::url","replicate_media::live::url","reorder_media::live::url","delete_media::live::url","delete_any_media::live::url","force_delete_media::live::url","force_delete_any_media::live::url","view_media::qr::code","view_any_media::qr::code","create_media::qr::code","update_media::qr::code","restore_media::qr::code","restore_any_media::qr::code","replicate_media::qr::code","reorder_media::qr::code","delete_media::qr::code","delete_any_media::qr::code","force_delete_media::qr::code","force_delete_any_media::qr::code","view_media::slider","view_any_media::slider","create_media::slider","update_media::slider","restore_media::slider","restore_any_media::slider","replicate_media::slider","reorder_media::slider","delete_media::slider","delete_any_media::slider","force_delete_media::slider","force_delete_any_media::slider","view_media::slider::content","view_any_media::slider::content","create_media::slider::content","update_media::slider::content","restore_media::slider::content","restore_any_media::slider::content","replicate_media::slider::content","reorder_media::slider::content","delete_media::slider::content","delete_any_media::slider::content","force_delete_media::slider::content","force_delete_any_media::slider::content","view_media::video","view_any_media::video","create_media::video","update_media::video","restore_media::video","restore_any_media::video","replicate_media::video","reorder_media::video","delete_media::video","delete_any_media::video","force_delete_media::video","force_delete_any_media::video","view_playlist","view_any_playlist","create_playlist","update_playlist","restore_playlist","restore_any_playlist","replicate_playlist","reorder_playlist","delete_playlist","delete_any_playlist","force_delete_playlist","force_delete_any_playlist","view_running::text","view_any_running::text","create_running::text","update_running::text","restore_running::text","restore_any_running::text","replicate_running::text","reorder_running::text","delete_running::text","delete_any_running::text","force_delete_running::text","force_delete_any_running::text","view_schedule","view_any_schedule","create_schedule","update_schedule","restore_schedule","restore_any_schedule","replicate_schedule","reorder_schedule","delete_schedule","delete_any_schedule","force_delete_schedule","force_delete_any_schedule","view_user","view_any_user","create_user","update_user","restore_user","restore_any_user","replicate_user","reorder_user","delete_user","delete_any_user","force_delete_user","force_delete_any_user"]}]';
        $directPermissions = '[]';

        static::makeRolesWithPermissions($rolesWithPermissions);
        static::makeDirectPermissions($directPermissions);

        $this->command->info('Shield Seeding Completed.');
    }

    protected static function makeRolesWithPermissions(string $rolesWithPermissions): void
    {
        if (!blank($rolePlusPermissions = json_decode($rolesWithPermissions, true))) {

            foreach ($rolePlusPermissions as $rolePlusPermission) {
                $role = Utils::getRoleModel()::firstOrCreate([
                    'name' => $rolePlusPermission['name'],
                    'guard_name' => $rolePlusPermission['guard_name'],
                ]);

                if (!blank($rolePlusPermission['permissions'])) {

                    $permissionModels = collect();

                    collect($rolePlusPermission['permissions'])
                        ->each(function ($permission) use ($permissionModels) {
                            $permissionModels->push(Utils::getPermissionModel()::firstOrCreate([
                                'name' => $permission,
                                'guard_name' => 'web',
                            ]));
                        });
                    $role->syncPermissions($permissionModels);

                }
            }
        }
    }

    public static function makeDirectPermissions(string $directPermissions): void
    {
        if (!blank($permissions = json_decode($directPermissions, true))) {

            foreach ($permissions as $permission) {

                if (Utils::getPermissionModel()::whereName($permission)->doesntExist()) {
                    Utils::getPermissionModel()::create([
                        'name' => $permission['name'],
                        'guard_name' => $permission['guard_name'],
                    ]);
                }
            }
        }
    }
}
